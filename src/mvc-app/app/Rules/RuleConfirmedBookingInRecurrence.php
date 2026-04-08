<?php

namespace App\Rules;

use App\Models\FacilityBooking\FacilityBooking;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RuleConfirmedBookingInRecurrence implements ValidationRule
{
    /**
     * FacilityBooking
     */
    private $facilityBooking;

    /**
     * Facility booking form data
     */
    private $formData;

    /**
     * Constructor
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
        if ($this->formData['facilityBookingMainData']['booking_status'] == FacilityBooking::BOOKING_STATUS_CONFIRMED  || empty($this->formData['facilityBookingMainData']['booking_date']) || empty($this->formData['facilityBookingMainData']['booking_start_time']) || empty($this->formData['facilityBookingMainData']['booking_end_time'])) {
            return;
        }
        $startTimeString = $this->formData['facilityBookingMainData']['booking_start_time'];
        $endTimeString = $this->formData['facilityBookingMainData']['booking_end_time'];

        $date = Carbon::createFromFormat('d/m/Y', $this->formData['facilityBookingMainData']['booking_date']);
        $startDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $startTimeString);

        //End Date Time
        $bookingEndDate = clone $date;
        $endDateTime = Carbon::parse($bookingEndDate->format('Y-m-d')  . ' ' . $endTimeString);

        //Edit validations
        if (isset($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type'])) {
            if ($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type'] == 'date_range') {
                if (empty($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type_start_date_range']) || empty($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type_end_date_range'])) {
                    return;
                }
                $startDateTime = Carbon::createFromFormat('d/m/Y H:i', $this->formData['facilityUpdateInstanceDetail']['save_recurrence_type_start_date_range'] . ' ' . $startTimeString);
                $endDateTime = Carbon::createFromFormat('d/m/Y H:i', $this->formData['facilityUpdateInstanceDetail']['save_recurrence_type_end_date_range'] . ' ' . $endTimeString);
            }
            if ($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type'] == 'all_series') {
                $startDateTime = Carbon::parse($this->facilityBooking->facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d') . ' ' . $startTimeString);
                $endDateTime = Carbon::parse($this->facilityBooking->facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d') . ' ' . $endTimeString);
            }
        }

        //Find all confirmed bookings
        $alreadyConfirmedBookings = $this->facilityBooking->facilityBookingRecurrence->facilityBookings()->whereBetween(
            'FB_BookingStartDateTime',
            [
                $startDateTime->startOfDay(),
                $endDateTime->endOfDay()
            ]
        )->where('FB_FacilityID', $this->facilityBooking->FB_FacilityID)
            ->where('FB_FacilityBookingID', '!=', $this->facilityBooking->FB_FacilityBookingID)
            ->where('FB_BookingStatus', FacilityBooking::BOOKING_STATUS_CONFIRMED)->get();
        if ($alreadyConfirmedBookings->count() > 0) {
            $fail(__('There are Confirmed Bookings in the Recurrence on :date, so status cannot be changed.', [
                'date' => $alreadyConfirmedBookings->pluck('FB_BookingStartDateTime')->map(function ($date) {
                    return $date->format('d/m/Y');
                })->implode(', ')
            ]));
        }
    }
}
