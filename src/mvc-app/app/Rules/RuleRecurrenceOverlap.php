<?php

namespace App\Rules;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RuleRecurrenceOverlap implements ValidationRule
{
    /**
     * Facility
     */
    private $facility;

    /**
     * FacilityBooking
     */
    private $facilityBooking;

    /**
     * Facility booking form data
     */
    private $formData;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(FacilityBooking $facilityBooking, array $formData = [])
    {
        $this->formData = $formData;
        $this->facilityBooking = $facilityBooking;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (
            !isset($this->formData['facilityBookingMainData']['booking_date'])
            || (isset($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type'])
                && $this->formData['facilityUpdateInstanceDetail']['save_recurrence_type'] != 'current')
        ) {
            return;
        }
        $facility = Facility::find($this->formData['facilityBookingMainData']['facility_id']);
        $bookingDate = Carbon::createFromFormat('d/m/Y', $this->formData['facilityBookingMainData']['booking_date']);
        if (
            $this->facilityBooking->facilityBookingRecurrence->facilityBookings()->whereDate('FB_BookingStartDateTime', $bookingDate)
            ->where('FB_FacilityBookingID', '!=', $this->facilityBooking->FB_FacilityBookingID)
            ->where('FB_FacilityID', $facility->FC_FacilityID)
            ->count() > 0
        ) {
            $fail(__("Same Recurrence of a Booking Series on the same Day :date", ['date' => $bookingDate->format('d/m/Y')]));
        }
    }
}
