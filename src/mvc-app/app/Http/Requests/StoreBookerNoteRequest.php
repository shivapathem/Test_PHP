<?php

namespace App\Http\Requests;

use App\Models\FacilityBooking\FacilityBookingRecurrence;
use App\Rules\RuleCheckBookerNoteOverlap;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookerNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $facility = $this->route('facility') ?? $this->route('facilityBookerNote')->facility;
        return [
            'facilityBookerNoteData.booker_note_frm' => 'required|max:255',
            'facilityBookerNoteData.booker_note_date' => [
                "required",
                "date_format:d/m/Y",
                "after_or_equal:" . ($facility->FC_ActiveFrom->gte(Carbon::now()) ? $facility->FC_ActiveFrom->format('Y-m-d') : $facility->FC_ActiveFrom->format('Y-m-d'))
            ],
            'facilityBookerNoteData.booker_note_start_time' => ['required', 'date_format:H:i'],
            'facilityBookerNoteData.booker_note_end_time' => ['required', 'date_format:H:i'],
            'facilityBookerNoteData.recurring_booker_note_enable' => 'required',

            //Recurring
            'facilityBookerNoteData.booker_note_recurring_start_date' => [
                "exclude_if:facilityBookerNoteData.recurring_booker_note_enable,no",
                "required_if:facilityBookerNoteData.recurring_booker_note_enable,yes",
                "nullable",
                "date_format:d/m/Y",
                "after_or_equal:" . ($facility->FC_ActiveFrom->gte(Carbon::now()) ? $facility->FC_ActiveFrom->format('Y-m-d') : Carbon::now()->format('Y-m-d')),
            ],
            'facilityBookerNoteData.booker_note_recurring_end_date' => [
                "exclude_if:facilityBookerNoteData.recurring_booker_note_enable,no",
                "required_if:facilityBookerNoteData.recurring_booker_note_enable,yes",
                "nullable",
                "date_format:d/m/Y",
                "after_or_equal:" . ($facility->FC_ActiveFrom->gte(Carbon::now()) ? $facility->FC_ActiveFrom->format('Y-m-d') : Carbon::now()->format('Y-m-d')),
            ],
            'facilityBookerNoteData.booker_note_recurring_recurrence_type' => "required_if:facilityBookerNoteData.recurring_booker_note_enable,yes",
            'facilityBookerNoteData.recurring_booker_note_recurrence_daily_days' => "required_if:facilityBookerNoteData.booker_note_recurring_recurrence_type," . FacilityBookingRecurrence::RECUR_TYPE_DAILY . ",facilityBookerNoteData.recurring_booker_note_enable,yes",
            'facilityBookerNoteData.recurring_booker_note_recurrence_weekly_weeks' => "required_if:facilityBookerNoteData.booker_note_recurring_recurrence_type," . FacilityBookingRecurrence::RECUR_TYPE_WEEKLY . ",facilityBookerNoteData.recurring_booker_note_enable,yes",
            'facilityBookerNoteData.recurring_booker_note_weekly_days' => "required_if:facilityBookerNoteData.booker_note_recurring_recurrence_type," . FacilityBookingRecurrence::RECUR_TYPE_WEEKLY . ",facilityBookerNoteData.recurring_booker_note_enable,yes|array",
            'overlap_validate_booker_note' => [
                'required',
                new RuleCheckBookerNoteOverlap($facility, $this->all(), $this->route('facilityBookerNote') ?? null)
            ]
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'facilityBookerNoteData.booker_note_frm' => 'Note',
            'facilityBookerNoteData.booker_note_date' => 'Date',
            'facilityBookerNoteData.booker_note_start_time' => 'Start Time',
            'facilityBookerNoteData.booker_note_end_time' => 'End Time',
            'facilityBookerNoteData.recurring_booker_note_enable' => 'Recurring Note',

            //Recurring
            'facilityBookerNoteData.booker_note_recurring_start_date' => 'Start Date',
            'facilityBookerNoteData.booker_note_recurring_end_date' => 'End Date',
            'facilityBookerNoteData.booker_note_recurring_recurrence_type' => "Recurrence Type",
            'facilityBookerNoteData.recurring_booker_note_recurrence_daily_days' => "Recurrence Day's",
            'facilityBookerNoteData.recurring_booker_note_recurrence_weekly_weeks' => "Recurrence Week's",
            'facilityBookerNoteData.recurring_booker_note_weekly_days' => "Week days",
        ];
    }
}
