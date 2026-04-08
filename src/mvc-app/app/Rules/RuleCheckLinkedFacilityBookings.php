<?php

namespace App\Rules;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class RuleCheckLinkedFacilityBookings implements ValidationRule
{
    /**
     * Facility facility
     */
    private $facility;

    /**
     * Facility form data
     */
    private $formData;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Facility $facility, array $formData)
    {
        $this->facility = $facility;
        $this->formData = $formData;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $newLinkedFacilities = $this->formData['facilityLinkFacilityForm']['facility_link_chose'] ?? [0];
        $removedLinkedFacilities = $this->facility->linkedFacilities->whereNotIn('FC_FacilityID', $newLinkedFacilities);
        $failed = false;
        //Check existing linked bookings which will be removed
        $validationErrors = ['toBeRemoved' => [], 'conflicts' => []];
        if ($removedLinkedFacilities->count() > 0 && $this->formData['facility_booking_linked_decline_reason'] == 0) {
            $linkedFacilityQuery = $this->facility->facilityBookings()->linkedBooking()
                ->whereNotIn('linkedBookings.FB_BookingStatus', [FacilityBooking::BOOKING_STATUS_DECLINED, FacilityBooking::BOOKING_STATUS_CANCELLED])
                ->where(function ($query2) {
                    $query2->whereDate('FacilityBookings.FB_BookingStartDateTime', '>=', Carbon::now())
                        ->orWhereDate('FacilityBookings.FB_BookingEndDateTime', '>=', Carbon::now());
                });

            $linkedFacilityQuery2 = $linkedFacilityQuery->where('linkedBookings.FB_FacilityID', $removedLinkedFacilities->pluck('FC_FacilityID')->toArray());
            if ($linkedFacilityQuery2->count() > 0) {
                $failed = true;
                $validationErrors['toBeRemoved'] = $linkedFacilityQuery2->select('linkedFctly.FC_FacilityName')->distinct()->get()->pluck('FC_FacilityName')->toArray();
            }
        }

        //Validate Other booking conflicts Bookings will be conflicted
        $mandatoryLinkedFacilities = [];
        foreach ($this->formData['facilityLinkFacilityForm']['facility_link_chose'] ?? [] as $facilityLink) {
            if (in_array($facilityLink, $this->formData['facilityLinkFacilityForm']['facility_link_mandatory'] ?? [])) {
                $mandatoryLinkedFacilities[] = $facilityLink;
            }
        }
        if (!empty($mandatoryLinkedFacilities)) {
            $facilityBookedTimes = FacilityBooking::whereNotIn('FacilityBookings.FB_BookingStatus', [FacilityBooking::BOOKING_STATUS_DECLINED, FacilityBooking::BOOKING_STATUS_CANCELLED])
                ->where(function ($query2) {
                    $query2->whereDate('FacilityBookings.FB_BookingStartDateTime', '>=', Carbon::now())
                        ->orWhereDate('FacilityBookings.FB_BookingEndDateTime', '>=', Carbon::now());
                })
                ->join('Facilities', 'FC_FacilityID', 'FacilityBookings.FB_FacilityID')
                ->join('FacilityBookings as ParentFacility', function ($join) {
                    $join->on('FacilityBookings.FB_FacilityBookingID', '>', DB::raw('ParentFacility.FB_FacilityBookingID'))
                        ->where('FacilityBookings.FB_FacilityBookingID', '!=', DB::raw('ParentFacility.FB_FacilityBookingID'))
                        ->where('FacilityBookings.FB_FacilityBookingRecurrenceID', '!=', DB::raw('ParentFacility.FB_FacilityBookingRecurrenceID'))
                        ->whereNotIn('ParentFacility.FB_BookingStatus', [FacilityBooking::BOOKING_STATUS_DECLINED, FacilityBooking::BOOKING_STATUS_CANCELLED])
                        ->where('ParentFacility.FB_FacilityID', $this->facility->FC_FacilityID)
                        ->whereNull('ParentFacility.deleted_at')
                        ->where('FacilityBookings.FB_BookingStartDateTime', '<', DB::raw('ParentFacility.FB_BookingEndDateTime'))
                        ->Where('FacilityBookings.FB_BookingEndDateTime', '>', DB::raw('ParentFacility.FB_BookingStartDateTime'));
                });
            //First check if there are overlaps in current facility
            $currentFacilityOverlap = $facilityBookedTimes->clone()->where('FacilityBookings.FB_FacilityID', $this->facility->FC_FacilityID)->select(DB::raw('CAST(FacilityBookings.FB_BookingStartDateTime AS DATE) AS FB_BookingStartDateTime'), 'Facilities.FC_FacilityName')->distinct()->get();
            foreach ($currentFacilityOverlap as $queryData) {
                $validationErrors['conflicts'][] = $queryData->toArray();
                $failed = true;
            }
            //Then Chek other facilities
            $otherFacilityOverlap = $facilityBookedTimes->clone()->whereIn('FacilityBookings.FB_FacilityID', $mandatoryLinkedFacilities)->select(DB::raw('CAST(FacilityBookings.FB_BookingStartDateTime AS DATE) AS FB_BookingStartDateTime'), 'Facilities.FC_FacilityName')->distinct()->get();
            foreach ($otherFacilityOverlap as $queryData) {
                $validationErrors['conflicts'][] = $queryData->toArray();
                $failed = true;
            }
        }

        if ($failed) {
            $fail(json_encode($validationErrors));
        }
    }
}
