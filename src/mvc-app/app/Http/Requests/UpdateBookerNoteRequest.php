<?php

namespace App\Http\Requests;

class UpdateBookerNoteRequest extends StoreBookerNoteRequest
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
        $rules = parent::rules();
        $rule['facilityBookerNoteData.booker_note_recurring_start_date'] = [
            "required_if:facilityBookerNoteData.recurring_booker_note_enable,yes",
            "nullable",
            "date_format:d/m/Y"
        ];
        $rule['facilityBookerNoteData.booker_note_recurring_end_date'] = [
            "required_if:facilityBookerNoteData.recurring_booker_note_enable,yes",
            "nullable",
            "date_format:d/m/Y"
        ];
        $rules['facilityBookerNoteData.booker_note_save_recurrence_type'] = [
            'required'
        ];
        $rules['facilityBookerNoteData.booker_note_save_recurrence_type_start_date_range'] = [
            'required_if:facilityBookerNoteData.booker_note_save_recurrence_type,date_range'
        ];
        $rules['facilityBookerNoteData.booker_note_save_recurrence_type_end_date_range'] = [
            'required_if:facilityBookerNoteData.booker_note_save_recurrence_type,date_range'
        ];
        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes['facilityBookerNoteData.booker_note_save_recurrence_type'] = 'Change instances';
        $attributes['facilityBookerNoteData.booker_note_save_recurrence_type_start_date_range'] = 'Start Date';
        $attributes['facilityBookerNoteData.booker_note_save_recurrence_type_end_date_range'] = 'End Date';
        return $attributes;
    }
}
