<?php

namespace App\Rules;

use App\Models\Facility\Facility;
use App\Models\Facility\FacilityAvailability;
use App\Models\Facility\FacilityMarkUnavailable;
use App\Models\FacilityBooking\FacilityBooking;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RuleFacilityUnavailabilityConflict implements ValidationRule
{
    protected ?Facility $facility;

    /**
     * @param \App\Models\Facility\Facility|null $facility
     */
    public function __construct(?Facility $facility)
    {
        $this->facility = $facility;
    }


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Guard: missing facility → pass.
        if (!$this->facility || empty($this->facility->FC_FacilityID)) {
            return;
        }

        // Build preview facility exactly like the form would update it.
        [$slots, $windowStart, $windowEnd] = $this->buildPreviewSlotsFromModel($this->facility);

        // No unavailable slots within the primary window → pass.
        if (empty($slots)) {
            return;
        }

        // Tighten the scan window to min..max of produced slots (perf).
        $min = $slots[0]['from']->copy();
        $max = $slots[0]['to']->copy();
        foreach ($slots as $s) {
            if ($s['from']->lt($min)) {
                $min = $s['from']->copy();
            }
            if ($s['to']->gt($max)) {
                $max = $s['to']->copy();
            }
        }
        $windowStart = $min;
        $windowEnd   = $max;

        // Fetch overlapping bookings (exclude Cancelled/Declined).
        $candidates = FacilityBooking::query()
            ->where('FB_FacilityID', $this->facility->FC_FacilityID)
            ->whereNotIn('FB_BookingStatus', [
                FacilityBooking::BOOKING_STATUS_CANCELLED,
                FacilityBooking::BOOKING_STATUS_DECLINED,
            ])
            ->with(['createdBy:UD_UserID,UD_DisplayName,UD_InternalEmail'])
            ->where(function ($q) use ($windowStart, $windowEnd) {
                $q->where('FB_BookingStartDateTime', '<', $windowEnd->format('Y-m-d H:i:s'))
                  ->where('FB_BookingEndDateTime', '>', $windowStart->format('Y-m-d H:i:s'));
            })
            ->get(['FB_FacilityBookingID', 'FB_BookingStartDateTime', 'FB_BookingEndDateTime','FB_CreatedBy']);

        // Inclusive datetime overlap check.
        $conflictIds = [];
        foreach ($candidates as $b) {
            $bStart = Carbon::parse($b->FB_BookingStartDateTime);
            $bEnd   = Carbon::parse($b->FB_BookingEndDateTime);

            foreach ($slots as $slot) {
                $sFrom = $slot['from'];
                $sTo   = $slot['to'];
                if ($bStart < $sTo && $bEnd > $sFrom) {
                    $isSelfBooked = ($this->facility->FC_DefaultBookingType === Facility::DEFAULT_BOOKING_SELF_BOOKED);
                    $creator      = $b->createdBy;
                    $isCreatorAdminForFacility = $creator ? (bool) $creator->haveAccessToFacility($this->facility) : false;

                    if ($isSelfBooked || !$isCreatorAdminForFacility) {
                        $conflictIds[] = (int) $b->FB_FacilityBookingID;
                        break;
                    }
                }
            }
        }

        $conflictIds = array_values(array_unique($conflictIds));

        $req = request();

        // Confirmed: merge IDs/window and pass.
        if (!empty($conflictIds) && $req->boolean('confirm_mark_unavailable')) {
            $req->merge([
                '_conflict_ids'    => $conflictIds,
                '_conflict_window' => [
                    'start' => $windowStart->format('Y-m-d H:i:s'),
                    'end'   => $windowEnd->format('Y-m-d H:i:s'),
                ],
            ]);
            return;
        }

        // Not confirmed and conflicts exist → fail with recognizable marker.
        if (!empty($conflictIds)) {
            $fail('FACILITY_UNAVAILABILITY_CONFLICT');
            return;
        }

        // No conflicts → pass.
    }


    protected function buildPreviewSlotsFromModel(Facility $facility): array
    {
        $req = request();

        // Build preview facility from current + posted fields.
        $preview = $facility->replicate();

        // FC_ActiveFrom from facilityFormData.active_from_frm (d/m/Y -> startOfDay).
        $activeFromFrm = (string) ($req->input('facilityFormData.active_from_frm') ?? '');
        if (!empty($activeFromFrm)) {
            $preview->FC_ActiveFrom = Carbon::createFromFormat('d/m/Y', $activeFromFrm)->startOfDay();
        } else {
            $preview->FC_ActiveFrom = $facility->FC_ActiveFrom;
        }

        // Availability relation: exact keys as repository.
        $faForm = (array) $req->input('facilityAvailability', []);
        $preview->setRelation(
            'facilityAvailability',
            $this->buildPreviewAvailabilityFromPosted($faForm, $facility->facilityAvailability)
        );

        // Mark-as-Unavailable relation: only if BOTH start & end are posted.
        $uaForm     = (array) $req->input('facilityMarkAsUnavailable', []);
        $uaHasRange = $this->hasUaRangeInForm($uaForm);

        $preview->setRelation(
            'facilityMarkAsUnavailable',
            $uaHasRange
                ? $this->buildPreviewMarkUnavailableFromPosted($uaForm, $facility->facilityMarkAsUnavailable)
                : $facility->facilityMarkAsUnavailable
        );

        // PRIMARY WINDOW: today → last future booking end (or today end-of-day if none).
        $windowStart = Carbon::now()->startOfDay();
        // dd($windowStart);
        $lastEnd     = $this->getFacilityLastBookingEnd($facility, true);
        $windowEnd   = $lastEnd ? $lastEnd->copy() : Carbon::now()->endOfDay();

        // Build slots solely from model API (single source of truth).
        $rawSlots = $preview->getUnavailableDateTime($windowStart, $windowEnd, true);
        $slots    = $this->normalizeSlotsToCarbon($rawSlots);

        return [$slots, $windowStart, $windowEnd];
    }


    protected function buildPreviewAvailabilityFromPosted(array $faForm, ?FacilityAvailability $current): FacilityAvailability
    {
        $fa = $current ? $current->replicate() : new FacilityAvailability();

        // Day order mirrors repository: Saturday..Friday
        $days = [
            'saturday' => 'Saturday',
            'sunday'   => 'Sunday',
            'monday'   => 'Monday',
            'tuesday'  => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday'   => 'Friday',
        ];

        $unavailArray = [];
        if (isset($faForm['facility_unavailability_frm'])) {
            $raw = $faForm['facility_unavailability_frm'];
            $unavailArray = is_array($raw) ? $raw : [$raw];
            $unavailArray = array_map('strtolower', $unavailArray);
        }

        foreach ($days as $lower => $proper) {
            $fromKey = "facility_availability_{$lower}_from_frm";
            $toKey   = "facility_availability_{$lower}_to_frm";

            $isUnavailableDay = in_array($lower, $unavailArray, true);
            $availabilityFlag = $isUnavailableDay ? 0 : 1;

            $fa->setAttribute("FCA_FacilityTimeAvailability_{$proper}", $availabilityFlag);
            $fa->setAttribute(
                "FCA_FacilityTimeFrom_{$proper}",
                $faForm[$fromKey] ?? $current?->getAttribute("FCA_FacilityTimeFrom_{$proper}") ?? '00:00'
            );
            $fa->setAttribute(
                "FCA_FacilityTimeTo_{$proper}",
                $faForm[$toKey] ?? $current?->getAttribute("FCA_FacilityTimeTo_{$proper}") ?? '00:00'
            );
        }

        return $fa;
    }


    protected function buildPreviewMarkUnavailableFromPosted(array $uaForm, ?FacilityMarkUnavailable $current): FacilityMarkUnavailable
    {
        $ua = $current ? $current->replicate() : new FacilityMarkUnavailable();

        $ua->FMU_FacilityMarkUnavailableStartDate = Carbon::createFromFormat('d/m/Y', $uaForm['mark_as_unavailable_start_date_frm'])->startOfDay();
        $ua->FMU_FacilityMarkUnavailableEndDate   = Carbon::createFromFormat('d/m/Y', $uaForm['mark_as_unavailable_end_date_frm'])->endOfDay();

        // Day order mirrors repository: Saturday..Friday
        $days = [
            'saturday' => 'Saturday',
            'sunday'   => 'Sunday',
            'monday'   => 'Monday',
            'tuesday'  => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday'   => 'Friday',
        ];

        foreach ($days as $lower => $proper) {
            $fromKey = "facility_mark_as_unavailable_{$lower}_from_frm";
            $toKey   = "facility_mark_as_unavailable_{$lower}_to_frm";
            $chkKey  = "facility_mark_as_unavailable_{$lower}_is_checked_frm";

            $ua->setAttribute(
                "FMU_FacilityMarkUnavailableTimeFrom_{$proper}",
                $uaForm[$fromKey] ?? $current?->getAttribute("FMU_FacilityMarkUnavailableTimeFrom_{$proper}") ?? '00:00'
            );
            $ua->setAttribute(
                "FMU_FacilityMarkUnavailableTimeTo_{$proper}",
                $uaForm[$toKey] ?? $current?->getAttribute("FMU_FacilityMarkUnavailableTimeTo_{$proper}") ?? '00:00'
            );

            $ua->setAttribute(
                "FMU_FacilityMarkUnavailableIsChecked_{$proper}",
                (isset($uaForm[$chkKey]) && $uaForm[$chkKey] === 'on') ? 1 : 0
            );
        }

        return $ua;
    }

    /**
     * Whether mark-as-unavailable range is present (both dates posted).
     */
    protected function hasUaRangeInForm(array $uaForm): bool
    {
        return !empty($uaForm['mark_as_unavailable_start_date_frm'])
            && !empty($uaForm['mark_as_unavailable_end_date_frm']);
    }


    protected function normalizeSlotsToCarbon(array $raw): array
    {
        $out = [];
        foreach ($raw as $slot) {
            if (empty($slot['from']) || empty($slot['to'])) {
                continue;
            }

            $from = Carbon::parse($slot['from']);
            $to   = Carbon::parse($slot['to']);

            if ($from->gt($to)) {
                [$from, $to] = [$to, $from];
            }

            $out[] = [
                'from'     => $from,
                'to'       => $to,
                'archived' => (int) ($slot['archived'] ?? 0),
                'active'   => (int) ($slot['active'] ?? $slot['facilityActive'] ?? 1),
            ];
        }

        usort($out, fn($a, $b) => $a['from'] <=> $b['from']);
        return $out;
    }


    protected function getFacilityLastBookingEnd(Facility $facility, bool $futureOnly = true): ?Carbon
    {
        $query = FacilityBooking::query()->where('FB_FacilityID', $facility->FC_FacilityID);
        if ($futureOnly) {
            $query->whereDate('FB_BookingEndDateTime', '>=', Carbon::now());
        }
        $maxEndRaw = $query->max('FB_BookingEndDateTime'); // string|null
        return $maxEndRaw ? Carbon::parse($maxEndRaw) : null;
    }
}
