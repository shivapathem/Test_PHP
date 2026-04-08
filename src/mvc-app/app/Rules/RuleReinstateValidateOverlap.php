<?php

namespace App\Rules;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuleReinstateValidateOverlap implements ValidationRule
{
    /**
     * FacilityBooking
     */
    private $facilityBooking;

    /**
     * Form data
     */
    private $formData;

    /**
     * Create a new rule instance.
     *
     * @param FacilityBooking $facilityBooking
     * @return void
     */
    public function __construct(FacilityBooking $facilityBooking, array $formData)
    {
        $this->facilityBooking = $facilityBooking;
        $this->formData = $formData;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $confirmedBookingsFacility = [];

        $facilityBookings = collect([]);
        $initQuery = $this->facilityBooking->facilityBookingRecurrence->availableFacilityBookings()->with(['facility', 'linkedFacilityBookings.facility', 'linkedToFacilityBooking.facility'])
            ->where('FB_BookingStatus', \App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_CANCELLED);
        switch ($this->formData['reinstate_booking']) {
            case 'some_booking':
                $facilityBookings = $initQuery->whereIn('FB_FacilityBookingID', $this->formData['some_booking_list'])->get();
                break;
            case 'recurrence_booking':
                $facilityBookings = $initQuery->whereDate('FB_BookingStartDateTime', '>=', Carbon::createFromFormat('d/m/Y', $this->formData['reinstate_booking_from_date']))
                    ->whereDate('FB_BookingStartDateTime', '<=', Carbon::createFromFormat('d/m/Y', $this->formData['reinstate_booking_to_date']))->get();
                break;
            case 'entire_booking':
                $facilityBookings = $initQuery->get();
                break;
        }
        foreach ($facilityBookings as $facilityBooking) {
            $queryChecker = $this->queryBuilder($facilityBooking);
            if ($facilityBooking->facility->FC_DefaultBookingType == Facility::DEFAULT_BOOKING_SELF_BOOKED && $queryChecker->count() > 0) {
                $confirmedBookingsFacility = [$this->genOverlapString($facilityBooking)];
            }
            //Validate Reinstance linked booking
            foreach ($facilityBooking->linkedFacilityBookings as $linkedBookings) {
                $queryChecker = $this->queryBuilder($linkedBookings);
                if ($linkedBookings->facility->FC_DefaultBookingType == Facility::DEFAULT_BOOKING_SELF_BOOKED && $queryChecker->count() > 0) {
                    $confirmedBookingsFacility[] = $this->genOverlapString($linkedBookings);
                }
            }
            //Validate Reinstate parent booking
            $parentBooking = $facilityBooking->linkedToFacilityBooking;
            if ($parentBooking != null && $parentBooking->FB_BookingStatus == FacilityBooking::BOOKING_STATUS_CANCELLED) {
                $queryChecker = $this->queryBuilder($parentBooking);
                if ($parentBooking->facility->FC_DefaultBookingType == Facility::DEFAULT_BOOKING_SELF_BOOKED && $queryChecker->count() > 0) {
                    $confirmedBookingsFacility[] = $this->genOverlapString($parentBooking);
                }
            }
        }
        if (!empty($confirmedBookingsFacility)) {
            $fail(json_encode($confirmedBookingsFacility));
        }
    }
    /**
     * Query builder
     */
    private function queryBuilder(FacilityBooking $facilityBooking)
    {
        $bookingStart = $facilityBooking->FB_BookingStartDateTime;
        $bookingEnd = $facilityBooking->FB_BookingEndDateTime;
        return $facilityBooking->facility->facilityBookings()->where('FB_BookingStatus', FacilityBooking::BOOKING_STATUS_CONFIRMED)
            ->where('FB_BookingStatus', FacilityBooking::BOOKING_STATUS_CONFIRMED)
            ->where('FB_BookingStartDateTime', '<', $bookingEnd)
            ->where('FB_BookingEndDateTime', '>', $bookingStart);
    }

    /**
     * Overlap string
     */
    private function genOverlapString(FacilityBooking $facilityBooking): string
    {
        return $facilityBooking->facility->FC_FacilityName . ' - ' . $facilityBooking->FB_BookingStartDateTime->format('d/m/Y');
    }
}
