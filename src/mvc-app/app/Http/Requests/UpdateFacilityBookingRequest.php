<?php

namespace App\Http\Requests;

use App\Rules\RuleRecurrenceOverlap;

class UpdateFacilityBookingRequest extends StoreFacilityBookingRequest
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
        $rules['facilityBookingMainData.booking_status'] = ['required'];
        $rules['facilityBookingRecurring.recurring_start_date'] = [
            "required_if:facilityBookingMainData.recurring_booking_enable,yes",
            "nullable",
            "date_format:d/m/Y"
        ];
        $rules['facilityBookingMainData.booking_start_time'] = [
            'required',
            'date_format:H:i'
        ];
        $rules['facilityBookingMainData.booking_end_time'] = [
            'required',
            'date_format:H:i'
        ];
        $rules['facilityBookingRecurring.recurring_end_date'] = [
            "required_if:facilityBookingMainData.recurring_booking_enable,yes",
            "nullable",
            "date_format:d/m/Y"
        ];
        if (isset($this->facilityBookingMainData['is_linked_booking'])) {
            unset(
                $rules['facilityBookingRecurring'],
                $rules['facilityBookingRecurring.recurring_start_date'],
                $rules['facilityBookingRecurring.recurring_end_date'],
                $rules['facilityBookingRecurring.recurring_booking_recurrence_type'],
                $rules['facilityBookingRecurring.recurring_booking_recurrence_daily_days'],
                $rules['facilityBookingRecurring.recurring_booking_recurrence_weekly_weeks'],
                $rules['facilityBookingRecurring.recurring_booking_weekly_days'],
                $rules['facilityBookingMainData.recurring_booking_enable']
            );
        }
        $rules['facilityUpdateInstanceDetail.save_recurrence_type'] = [
            'required'
        ];
        $rules['facilityUpdateInstanceDetail.save_recurrence_type_start_date_range'] = [
            'required_if:facilityUpdateInstanceDetail.save_recurrence_type,date_range',
            'nullable',
            'date_format:d/m/Y'
        ];
        $rules['facilityUpdateInstanceDetail.save_recurrence_type_end_date_range'] = [
            'required_if:facilityUpdateInstanceDetail.save_recurrence_type,date_range',
            'nullable',
            'date_format:d/m/Y'
        ];
        $rules['facilityUpdateInstanceDetail.declined_reason'] = [
            'required_if:facilityBookingMainData.booking_status,declined'
        ];
        if ($this->input('admin_page') == 1) {
            $rules['facilityBookingMainData.booking_date'] = [
                'required',
                'date_format:d/m/Y',
            ];
            if (!isset($this->facilityBooking)) {
                $rules['facilityBookingMainData.booking_date'][] =
                    'before_or_equal:' . ($this->facility->FC_ActiveFrom->gte(\Carbon\Carbon::now()) ? $this->facility->FC_ActiveFrom : \Carbon\Carbon::now())->format('Y-m-d');
            }
        }
        $rules['recurrence_overlap_check'] = [
            'required',
            new RuleRecurrenceOverlap($this->route('facilityBooking'), $this->all())
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
        $attributes['facilityBookingMainData.booking_status'] = 'Booking Status';
        $attributes['facilityUpdateInstanceDetail.save_recurrence_type'] = 'Change instances';
        $attributes['facilityUpdateInstanceDetail.save_recurrence_type_start_date_range'] = 'Start Date';
        $attributes['facilityUpdateInstanceDetail.save_recurrence_type_end_date_range'] = 'End Date';
        $attributes['facilityUpdateInstanceDetail.declined_reason'] = 'Declined Reason';
        return $attributes;
    }
}
