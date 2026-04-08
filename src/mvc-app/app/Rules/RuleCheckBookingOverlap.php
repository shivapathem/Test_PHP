<?php

namespace App\Rules;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBooking;
use App\Models\FacilityBooking\FacilityBookingRecurrence;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RuleCheckBookingOverlap implements Rule
{
    /**
     * Validation type constant overlap exclude managed booking
     */
    const OVERLAP_EXCLUDE_MANAGED = 'overlap_exclude_managed';

    /**
     * Validation type constant overlap validation
     */
    const OVERLAP_ALL = 'overlap_all';

    /**
     * Validation type
     */
    private $validationType;

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
     * Message validation
     */
    private $messageData = [];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Facility $facility, array $formData = [], ?FacilityBooking $facilityBooking = null, string $validationType = RuleCheckBookingOverlap::OVERLAP_EXCLUDE_MANAGED)
    {
        $this->facility = $facility;
        $this->formData = $formData;
        $this->facilityBooking = $facilityBooking;
        $this->validationType = $validationType;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($this->formData['facilityBookingMainData']['booking_date']) || empty($this->formData['facilityBookingMainData']['booking_start_time']) || empty($this->formData['facilityBookingMainData']['booking_end_time'])) {
            return true;
        }
        $startTimeString = $this->formData['facilityBookingMainData']['booking_start_time'];
        $endTimeString = $this->formData['facilityBookingMainData']['booking_end_time'];

        $date = Carbon::createFromFormat('d/m/Y', $this->formData['facilityBookingMainData']['booking_date']);
        $startDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $startTimeString);

        //End Date Time
        $startTime = Carbon::createFromFormat('H:i', $startTimeString);
        $endTime = Carbon::createFromFormat('H:i', $endTimeString);
        $bookingEndDate = clone $date;
        $endDateTime = Carbon::parse($bookingEndDate->format('Y-m-d')  . ' ' . $endTimeString);

        //Recurrence validation
        $availableDates = [];
        if ($this->facilityBooking != null && isset($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type']) && isset($this->formData['facilityBookingMainData']['recurring_booking_enable']) && $this->formData['facilityBookingMainData']['recurring_booking_enable'] == 'yes') {
            if ($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type'] == 'date_range') {
                if (empty($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type_start_date_range']) || empty($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type_end_date_range'])) {
                    return true;
                }
                $startDateTime = Carbon::createFromFormat('d/m/Y H:i', $this->formData['facilityUpdateInstanceDetail']['save_recurrence_type_start_date_range'] . ' ' . $startTimeString);
                $endDateTime = Carbon::createFromFormat('d/m/Y H:i', $this->formData['facilityUpdateInstanceDetail']['save_recurrence_type_end_date_range'] . ' ' . $endTimeString);
            }
            if ($this->formData['facilityUpdateInstanceDetail']['save_recurrence_type'] == 'all_series') {
                $startDateTime = Carbon::parse($this->facilityBooking->facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d') . ' ' . $startTimeString);
                $endDateTime = Carbon::parse($this->facilityBooking->facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d') . ' ' . $endTimeString);
            }

            $startDateTime = $startDateTime->lte(Carbon::now()) ? Carbon::createFromFormat('Y-m-d H:i', Carbon::now()->format('Y-m-d') . ' ' . $startTimeString) : $startDateTime;
        }
        if (isset($this->formData['facilityBookingMainData']['recurring_booking_enable']) && $this->formData['facilityBookingMainData']['recurring_booking_enable'] == 'yes') {
            if (empty($this->formData['facilityBookingRecurring']['recurring_start_date']) || empty($this->formData['facilityBookingRecurring']['recurring_end_date']) || empty($this->formData['facilityBookingRecurring']['recurring_booking_recurrence_type'])) {
                return true;
            }
            $facilityBookingRecurrence = new FacilityBookingRecurrence();
            $facilityBookingRecurrence->facility = $this->facility;
            $facilityBookingRecurrence->FBR_SeriesStartDate =  Carbon::createFromFormat('d/m/Y', $this->formData['facilityBookingRecurring']['recurring_start_date']);
            $facilityBookingRecurrence->FBR_SeriesEndDate =  Carbon::createFromFormat('d/m/Y', $this->formData['facilityBookingRecurring']['recurring_end_date']);
            $facilityBookingRecurrence->FBR_RecurrenceType = $this->formData['facilityBookingRecurring']['recurring_booking_recurrence_type'];
            $facilityBookingRecurrence->FBR_RecurrenceDayInterval = $this->formData['facilityBookingRecurring']['recurring_booking_recurrence_daily_days'];
            $facilityBookingRecurrence->FBR_RecurrenceWeekInterval = $this->formData['facilityBookingRecurring']['recurring_booking_recurrence_weekly_weeks'];
            //Daily recurrence overlap validation
            if ($this->formData['facilityBookingRecurring']['recurring_booking_recurrence_type'] == FacilityBookingRecurrence::RECUR_TYPE_DAILY) {
                if (empty($this->formData['facilityBookingRecurring']['recurring_booking_recurrence_daily_days'])) {
                    return true;
                }
            }

            //Weekly recurrence overlap validation
            if ($this->formData['facilityBookingRecurring']['recurring_booking_recurrence_type'] == FacilityBookingRecurrence::RECUR_TYPE_WEEKLY) {
                if (empty($this->formData['facilityBookingRecurring']['recurring_booking_recurrence_weekly_weeks']) || !isset($this->formData['facilityBookingRecurring']['recurring_booking_weekly_days'])) {
                    return true;
                }
                $days = ['Sunday', 'Saturday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                foreach ($days as $dayLoop) { //Reset
                    $facilityBookingRecurrence->{'FBR_RecurrenceWeek_' . $dayLoop} = 0;
                }
                foreach ($this->formData['facilityBookingRecurring']['recurring_booking_weekly_days'] as $day) {
                    $facilityBookingRecurrence->{'FBR_RecurrenceWeek_' . ucfirst($day)} = 1;
                }
            }

            foreach ($facilityBookingRecurrence->getAvailableDates(Auth::user()) as $carbonDate) {
                $availableDates[] = $carbonDate->format('Y-m-d'); // Or any desired format
            }

            $differences = [];
            if ($this->facilityBooking != null) {
                $only = $facilityBookingRecurrence->checkRecurrenceDifferenceArray();
                $differences = array_diff_assoc($facilityBookingRecurrence->only($only), $this->facilityBooking->facilityBookingRecurrence->only($only));
            }
            if (!empty($differences) || $this->facilityBooking == null) {
                $startDateTime = Carbon::parse($facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d') . ' ' . $startTimeString);
                $endDateTime = Carbon::parse($facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d') . ' ' . $endTimeString);
            }
        }

        //Generate query for validation
        $facilityIds = $this->formData['facilityBookingFacilityLink'] ?? [];
        $facilityIds[] = $this->facility->FC_FacilityID;
        $bookingStatus = isset($this->formData['facilityBookingMainData']['booking_status']) ? $this->formData['facilityBookingMainData']['booking_status'] : '';
        $bookingRequestType = $this->formData['facilityBookingMainData']['facility_booking_data_type'];

        // 1) Build the [dayStart, dayEnd] windows once:
        $windows = [];
        $currentDay = $startDateTime->copy()->startOfDay();
        $lastDay    = $endDateTime->copy()->startOfDay();

        while ($currentDay->lte($lastDay)) {
            $dayStart = $currentDay->copy()->setTimeFrom($startTime);
            $dayEnd   = $currentDay->copy()->setTimeFrom($endTime);
            if ($endTime->lte($startTime)) {
                $dayEnd->addDay(); // overnight
            }
            $windows[] = [$dayStart, $dayEnd];
            $currentDay->addDay();
        }

        // 2) Query in chunks and merge:
        $chunkSize = 200; // keep bindings safely below DB/driver limits
        $totalRecords = 0;
        foreach (collect($windows)->chunk($chunkSize) as $chunk) {
            $bookingsQuery = FacilityBooking::query()->where(function ($outer) use ($chunk) {
                foreach ($chunk as [$ds, $de]) {
                    $outer->orWhere(function ($q) use ($ds, $de) {
                        $q->where('FB_BookingStartDateTime', '<', $de)
                            ->where('FB_BookingEndDateTime', '>', $ds);
                    });
                }
            });
            $bookingsQuery->join('Facilities', 'Facilities.FC_FacilityID', '=', 'FacilityBookings.FB_FacilityID')
                ->whereNotIn('FB_BookingStatus', [FacilityBooking::BOOKING_STATUS_CANCELLED, FacilityBooking::BOOKING_STATUS_DECLINED])
                ->whereIn('FB_FacilityID', $facilityIds);
            if ($this->validationType == RuleCheckBookingOverlap::OVERLAP_EXCLUDE_MANAGED) {
                $bookingsQuery->where(function ($query) use ($bookingStatus, $bookingRequestType) {
                    $query->where('FC_DefaultBookingType', Facility::DEFAULT_BOOKING_SELF_BOOKED);
                    if ($bookingStatus == FacilityBooking::BOOKING_STATUS_CONFIRMED || $bookingRequestType == FacilityBookingRecurrence::BOOKING_TYPE_REQUEST) { // For managed facility confirm cant overlap
                        $query->orWhere('FB_BookingStatus', FacilityBooking::BOOKING_STATUS_CONFIRMED);
                    }
                });
            }
            if ($this->facilityBooking != null) {
                $bookingsQuery->where('FB_FacilityBookingRecurrenceID', '!=', $this->facilityBooking->FB_FacilityBookingRecurrenceID);
            }
            if (!empty($availableDates)) {
                $bookingsQuery->whereIn(DB::raw('CAST(FB_BookingStartDateTime AS DATE)'), $availableDates);
            }
            $facilityBookingOverlaps = (clone $bookingsQuery)->selectRaw('FacilityBookings.*, FC_FacilityID, FC_FacilityName')->get();
            foreach ($facilityBookingOverlaps as $facilityBookingOverlap) {
                $this->messageData[$facilityBookingOverlap->FC_FacilityID][] = ['facilityName' => $facilityBookingOverlap->FC_FacilityName, 'conflict_date' => $facilityBookingOverlap->date_only_start_date_time];
            }
            $totalRecords = $totalRecords + count($this->messageData);
        }
        return $totalRecords > 0 ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return json_encode($this->messageData);
    }
}
