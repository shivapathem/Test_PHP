<?php

namespace App\Trait;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\FacilityBooking\FacilityBookingRecurrence;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait FacilityUnAvailabilityBookingFormDataTrait
{
    public function getFacilityBookingUnavailabilityDate(Facility $facility, array $formData, ?FacilityBooking $facilityBooking = null): array
    {
        if (empty($formData['facilityBookingMainData']['booking_date']) || empty($formData['facilityBookingMainData']['booking_start_time']) || empty($formData['facilityBookingMainData']['booking_end_time'])) {
            return [];
        }
        $startTimeString = $formData['facilityBookingMainData']['booking_start_time'];
        $endTimeString = $formData['facilityBookingMainData']['booking_end_time'];

        $date = Carbon::createFromFormat('d/m/Y', $formData['facilityBookingMainData']['booking_date']);
        $startDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $startTimeString);

        //End Date Time
        $bookingEndDate = clone $date;
        $endDateTime = Carbon::parse($bookingEndDate->format('Y-m-d')  . ' ' . $endTimeString);

        //Recurrence validation
        $availableDates = [$date->format('Y-m-d')];

        if ($facilityBooking != null && isset($formData['facilityUpdateInstanceDetail']['save_recurrence_type'])) {
            if ($formData['facilityUpdateInstanceDetail']['save_recurrence_type'] == 'date_range') {
                if (empty($formData['facilityUpdateInstanceDetail']['save_recurrence_type_start_date_range']) || empty($formData['facilityUpdateInstanceDetail']['save_recurrence_type_end_date_range'])) {
                    return [];
                }
                $startDateTime = Carbon::createFromFormat('d/m/Y H:i', $formData['facilityUpdateInstanceDetail']['save_recurrence_type_start_date_range'] . ' ' . $startTimeString);
                $endDateTime = Carbon::createFromFormat('d/m/Y H:i', $formData['facilityUpdateInstanceDetail']['save_recurrence_type_end_date_range'] . ' ' . $endTimeString);
            }
            if ($formData['facilityUpdateInstanceDetail']['save_recurrence_type'] == 'all_series') {
                $startDateTime = Carbon::parse($facilityBooking->facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d') . ' ' . $startTimeString);
                $endDateTime = Carbon::parse($facilityBooking->facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d') . ' ' . $endTimeString);
            }

            //Check change in recurrecence date
            $recurrenceStartTimeFrm = Carbon::createFromFormat('d/m/Y', $formData['facilityBookingRecurring']['recurring_start_date']);
            $recurrenceEndTimeFrm = Carbon::createFromFormat('d/m/Y', $formData['facilityBookingRecurring']['recurring_end_date']);
            if (
                ($facilityBooking->facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d') != $recurrenceStartTimeFrm->format('Y-m-d')) ||
                ($facilityBooking->facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d') != $recurrenceEndTimeFrm->format('Y-m-d'))
            ) {
                $startDateTime = Carbon::parse($recurrenceStartTimeFrm->format('Y-m-d') . ' ' . $startTimeString);
                $endDateTime = Carbon::parse($recurrenceEndTimeFrm->format('Y-m-d') . ' ' . $endTimeString);
            }
            $startDateTime = $startDateTime->lt(Carbon::now()) ? Carbon::parse(Carbon::now()->format('Y-m-d') . ' ' . $startTimeString) : $startDateTime;
        }

        if (isset($formData['facilityBookingMainData']['recurring_booking_enable']) && $formData['facilityBookingMainData']['recurring_booking_enable'] == 'yes') {
            if (empty($formData['facilityBookingRecurring']['recurring_start_date']) || empty($formData['facilityBookingRecurring']['recurring_end_date']) || empty($formData['facilityBookingRecurring']['recurring_booking_recurrence_type'])) {
                return [];
            }
            $facilityBookingRecurrence = new FacilityBookingRecurrence();
            $facilityBookingRecurrence->facility = $facility;
            $facilityBookingRecurrence->FBR_SeriesStartDate =  Carbon::createFromFormat('d/m/Y', $formData['facilityBookingRecurring']['recurring_start_date']);
            $facilityBookingRecurrence->FBR_SeriesEndDate =  Carbon::createFromFormat('d/m/Y', $formData['facilityBookingRecurring']['recurring_end_date']);
            $facilityBookingRecurrence->FBR_RecurrenceType = $formData['facilityBookingRecurring']['recurring_booking_recurrence_type'];
            $facilityBookingRecurrence->FBR_RecurrenceDayInterval = $formData['facilityBookingRecurring']['recurring_booking_recurrence_daily_days'];
            $facilityBookingRecurrence->FBR_RecurrenceWeekInterval = $formData['facilityBookingRecurring']['recurring_booking_recurrence_weekly_weeks'];
            //Daily recurrence overlap validation
            if ($formData['facilityBookingRecurring']['recurring_booking_recurrence_type'] == FacilityBookingRecurrence::RECUR_TYPE_DAILY) {
                if (empty($formData['facilityBookingRecurring']['recurring_booking_recurrence_daily_days'])) {
                    return [];
                }
            }

            //Weeekly recurrence overlap validation
            if ($formData['facilityBookingRecurring']['recurring_booking_recurrence_type'] == FacilityBookingRecurrence::RECUR_TYPE_WEEKLY) {
                if (empty($formData['facilityBookingRecurring']['recurring_booking_recurrence_weekly_weeks']) || !isset($formData['facilityBookingRecurring']['recurring_booking_weekly_days'])) {
                    return [];
                }
                $days = ['Sunday', 'Saturday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                foreach ($days as $dayLoop) { //Reset
                    $facilityBookingRecurrence->{'FBR_RecurrenceWeek_' . $dayLoop} = 0;
                }
                foreach ($formData['facilityBookingRecurring']['recurring_booking_weekly_days'] as $day) {
                    $facilityBookingRecurrence->{'FBR_RecurrenceWeek_' . ucfirst($day)} = 1;
                }
            }

            foreach ($facilityBookingRecurrence->getAvailableDates(Auth::user()) as $carbonDate) {
                $availableDates[] = $carbonDate->format('Y-m-d'); // Or any desired format
            }

            $differences = [];
            if ($facilityBooking != null) {
                $only = $facilityBookingRecurrence->checkRecurrenceDifferenceArray();
                $differences = array_diff_assoc($facilityBookingRecurrence->only($only), $facilityBooking->facilityBookingRecurrence->only($only));
            }
            if (!empty($differences) || $facilityBooking == null) {
                $startDateTime = Carbon::parse($facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d') . ' ' . $startTimeString);
                $endDateTime = Carbon::parse($facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d') . ' ' . $endTimeString);
            }
        }

        $facilityIds = $formData['facilityBookingFacilityLink'] ?? [];
        $facilityIds[] = $facility->FC_FacilityID;
        //Get Unavailability timeline of each facility
        $facilityUnavailabilityDetails = [];
        $facilitiesLoop = Facility::whereIn('FC_FacilityID', $facilityIds)->get();

        foreach ($facilitiesLoop as $facilityLoop) {
            $unavailabilityFacilityDetail = [];
            foreach (
                $facilityLoop->getUnavailableDateTime(
                    $startDateTime->clone()->startOfDay(),
                    $endDateTime->clone()->startOfDay(),
                    true
                ) as $unAvailableDetail
            ) {
                $unAvailableFromDateTimeLoop = Carbon::parse($unAvailableDetail['from']);
                $unAvailableToDateTimeLoop = Carbon::parse($unAvailableDetail['to']);
                $bookingStartDateTime = Carbon::parse($unAvailableFromDateTimeLoop->format('Y-m-d') . ' ' . $startDateTime->format('H:i'));
                $bookingEndDateTime = Carbon::parse($unAvailableFromDateTimeLoop->format('Y-m-d') . ' ' . $endDateTime->format('H:i'));
                if ($bookingEndDateTime->lt($bookingStartDateTime)) {
                    $bookingEndDateTime->addDays(1);
                }
                if (
                    !in_array($unAvailableFromDateTimeLoop->format('Y-m-d'), $availableDates)
                ) {
                    continue;
                }

                //Check based on facility active, availability, unavailability and achived
                if (
                    $bookingStartDateTime->lt($unAvailableToDateTimeLoop)
                    &&
                    $bookingEndDateTime->gt($unAvailableFromDateTimeLoop)
                ) {
                    $unavailabilityFacilityDetail[] = [
                        'from' => $unAvailableFromDateTimeLoop->format('Y-m-d H:i'),
                        'to' => $unAvailableToDateTimeLoop->format('Y-m-d H:i'),
                        'archived' => $unAvailableDetail['archived'],
                        'active' => $unAvailableDetail['facilityActive']
                    ];
                }
            }

            if (!empty($unavailabilityFacilityDetail)) {
                $facilityUnavailabilityDetails[] = [
                    'facilityName' => $facilityLoop->FC_FacilityName,
                    'facilityActiveFrom' => $facilityLoop->FC_ActiveFrom->format("Y-m-d"),
                    'facilityId' => $facilityLoop->FC_FacilityID,
                    'unavailability' => $unavailabilityFacilityDetail
                ];
            }
        }
        return $facilityUnavailabilityDetails;
    }
}
