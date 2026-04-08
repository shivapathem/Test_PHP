<?php

namespace App\Rules;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBookerNote;
use App\Models\FacilityBooking\FacilityBookerNoteRecurrence;
use App\Models\FacilityBooking\FacilityBookingRecurrence;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RuleCheckBookerNoteOverlap implements Rule
{
    /**
     * Facility $facility
     */
    private $facility;

    /**
     * array $facilityBookerNoteData
     */
    private $facilityBookerNoteData;

    /**
     * FacilityBookerNote $facilityBookerNote
     */
    private $facilityBookerNote;

    /**
     * QueryBuilder $facilityBookerNoteQuery
     */
    private $facilityBookerNoteQuery;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Facility $facility, array $facilityBookerNoteData, ?FacilityBookerNote $facilityBookerNote = null)
    {
        $this->facility = $facility;
        $this->facilityBookerNoteData = $facilityBookerNoteData['facilityBookerNoteData'];
        $this->facilityBookerNote = $facilityBookerNote;
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
        if (empty($this->facilityBookerNoteData['booker_note_date']) || empty($this->facilityBookerNoteData['booker_note_start_time']) || empty($this->facilityBookerNoteData['booker_note_end_time'])) {
            return true;
        }
        $date = Carbon::createFromFormat('d/m/Y', $this->facilityBookerNoteData['booker_note_date']);
        $startDateTime = Carbon::parse($date->format('Y-m-d') . ' ' . $this->facilityBookerNoteData['booker_note_start_time']);

        //End Date Time
        $startTime = Carbon::createFromFormat('H:i', $this->facilityBookerNoteData['booker_note_start_time']);
        $endTime = Carbon::createFromFormat('H:i', $this->facilityBookerNoteData['booker_note_end_time']);
        $bookingEndDate = clone $date;
        $endDateTime = Carbon::parse($bookingEndDate->format('Y-m-d')  . ' ' . $this->facilityBookerNoteData['booker_note_end_time']);


        //Recurrence validation
        $availableDates = [];

        //While Edit
        if ($this->facilityBookerNote != null && isset($this->facilityBookerNoteData['booker_note_save_recurrence_type'])) {
            if ($this->facilityBookerNoteData['booker_note_save_recurrence_type'] == 'date_range') {
                if (empty($this->facilityBookerNoteData['booker_note_save_recurrence_type_start_date_range']) || empty($this->facilityBookerNoteData['booker_note_save_recurrence_type_end_date_range'])) {
                    return true;
                }
                $startDateTime = Carbon::createFromFormat('d/m/Y H:i', $this->facilityBookerNoteData['booker_note_save_recurrence_type_start_date_range'] . ' ' . $this->facilityBookerNoteData['booker_note_start_time']);
                $endDateTime = Carbon::createFromFormat('d/m/Y H:i', $this->facilityBookerNoteData['booker_note_save_recurrence_type_end_date_range'] . ' ' . $this->facilityBookerNoteData['booker_note_end_time']);
            }
            if ($this->facilityBookerNoteData['booker_note_save_recurrence_type'] == 'all_series') {
                $startDateTime = Carbon::parse($this->facilityBookerNote->facilityBookerNoteRecurrence->FBNR_SeriesStartDate->format('Y-m-d') . ' ' . $this->facilityBookerNoteData['booker_note_start_time']);
                $endDateTime = Carbon::parse($this->facilityBookerNote->facilityBookerNoteRecurrence->FBNR_SeriesEndDate->format('Y-m-d') . ' ' . $this->facilityBookerNoteData['booker_note_end_time']);
            }
        }

        if ($this->facilityBookerNote == null && isset($this->facilityBookerNoteData['recurring_booker_note_enable']) && $this->facilityBookerNoteData['recurring_booker_note_enable'] == 'yes') {
            if (empty($this->facilityBookerNoteData['booker_note_recurring_start_date']) || empty($this->facilityBookerNoteData['booker_note_recurring_end_date']) || empty($this->facilityBookerNoteData['booker_note_recurring_recurrence_type'])) {
                return true;
            }
            $facilityBookerNoteRecurrence = new FacilityBookerNoteRecurrence();
            $facilityBookerNoteRecurrence->FBNR_SeriesStartDate =  Carbon::createFromFormat('d/m/Y', $this->facilityBookerNoteData['booker_note_recurring_start_date']);
            $facilityBookerNoteRecurrence->FBNR_SeriesEndDate =  Carbon::createFromFormat('d/m/Y', $this->facilityBookerNoteData['booker_note_recurring_end_date']);
            $facilityBookerNoteRecurrence->FBNR_RecurrenceType = $this->facilityBookerNoteData['booker_note_recurring_recurrence_type'];
            $facilityBookerNoteRecurrence->FBNR_RecurrenceDayInterval = $this->facilityBookerNoteData['recurring_booker_note_recurrence_daily_days'];
            $facilityBookerNoteRecurrence->FBNR_RecurrenceWeekInterval = $this->facilityBookerNoteData['recurring_booker_note_recurrence_weekly_weeks'];
            //Daily recurrence overlap validation
            if ($this->facilityBookerNoteData['booker_note_recurring_recurrence_type'] == FacilityBookingRecurrence::RECUR_TYPE_DAILY) {
                if (empty($this->facilityBookerNoteData['recurring_booker_note_recurrence_daily_days'])) {
                    return true;
                }
            }

            //Weeekly recurrence overlap validation
            if ($this->facilityBookerNoteData['booker_note_recurring_recurrence_type'] == FacilityBookingRecurrence::RECUR_TYPE_WEEKLY) {
                if (empty($this->facilityBookerNoteData['recurring_booker_note_recurrence_weekly_weeks']) || !isset($this->facilityBookerNoteData['recurring_booker_note_weekly_days'])) {
                    return true;
                }
                $days = ['Sunday', 'Saturday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                foreach ($days as $dayLoop) { //Reset
                    $facilityBookerNoteRecurrence->{'FBNR_RecurrenceWeek_' . $dayLoop} = 0;
                }
                foreach ($this->facilityBookerNoteData['recurring_booker_note_weekly_days'] as $day) {
                    $facilityBookerNoteRecurrence->{'FBNR_RecurrenceWeek_' . ucfirst($day)} = 1;
                }
            }

            foreach ($facilityBookerNoteRecurrence->getAvailableDates() as $carbonDate) {
                $availableDates[] = $carbonDate->format('Y-m-d'); // Or any desired format
            }

            $differences = [];
            if ($this->facilityBookerNote != null) {
                $only = $facilityBookerNoteRecurrence->checkRecurrenceDifferenceArray();
                $differences = array_diff_assoc($facilityBookerNoteRecurrence->only($only), $this->facilityBookerNote->facilityBookerNoteRecurrence->only($only));
            }
            if (!empty($differences) || $this->facilityBookerNote == null) {
                $startDateTime = Carbon::parse($facilityBookerNoteRecurrence->FBNR_SeriesStartDate->format('Y-m-d') . ' ' . $this->facilityBookerNoteData['booker_note_start_time']);
                $endDateTime = Carbon::parse($facilityBookerNoteRecurrence->FBNR_SeriesEndDate->format('Y-m-d') . ' ' . $this->facilityBookerNoteData['booker_note_end_time']);
            }
        }

        //Generate query for validation
        $this->facilityBookerNoteQuery = FacilityBookerNote::whereBetween('FBN_StartDateTime', [
            $startDateTime->copy()->startOfDay(),
            $endDateTime->copy()->endOfDay()
        ])->where(function ($query) use ($startTime, $endTime) {

            if ($endTime->lte($startTime)) {
                $start = $startTime->format('H:i:s');
                $end = $endTime->format('H:i:s');
                // Time window spans midnight
                $query->where(function ($q) use ($start, $end) {
                    $q->whereRaw("CAST(FBN_StartDateTime AS TIME) >= ?", [$start])
                        ->orWhereRaw("CAST(FBN_StartDateTime AS TIME) <= ?", [$end]);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->whereRaw("CAST(FBN_EndDateTime AS TIME) >= ?", [$start])
                        ->orWhereRaw("CAST(FBN_EndDateTime AS TIME) <= ?", [$end]);
                });
            } else {
                $start = $startTime->copy()->addMinutes(15)->format('H:i:s');
                $end = $endTime->copy()->subMinutes(15)->format('H:i:s');
                // Time window within same day
                $query->where(function ($q) use ($start, $end) {
                    $q->whereRaw("CAST(FBN_StartDateTime AS TIME) BETWEEN ? AND ?", [$start, $end])
                        ->orWhereRaw("CAST(FBN_EndDateTime AS TIME) BETWEEN ? AND ?", [$start, $end])
                        ->orwhere(function ($qq) use ($start, $end) {
                            $qq->whereRaw("CAST(FBN_StartDateTime AS TIME) < ?", [$start])
                                ->whereRaw("CAST(FBN_EndDateTime AS TIME) > ?", [$end]);
                        });
                });
            }
        })->where('FBN_FacilityID', $this->facility->FC_FacilityID);
        if (!empty($availableDates)) {
            $this->facilityBookerNoteQuery->whereIn(DB::raw('CAST(FBN_StartDateTime AS DATE)'), $availableDates);
        }
        if ($this->facilityBookerNote != null) {
            $this->facilityBookerNoteQuery->where('FBN_FacilityBookerNoteRecurrenceID', '!=', $this->facilityBookerNote->facilityBookerNoteRecurrence->FBNR_FacilityBookerNoteRecurrenceID);
        }
        return ((clone $this->facilityBookerNoteQuery)->count() > 0 ? false : true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $facilityBookerNoteOverlaps = (clone $this->facilityBookerNoteQuery)->limit(20)->get();
        return $facilityBookerNoteOverlaps->implode('date_only_start_date_time', ', ');
    }
}
