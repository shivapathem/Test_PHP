<?php

namespace App\Http\Requests;

use App\Rules\RuleCheckLinkedFacilityBookings;
use App\Rules\RuleFacilityActiveFrom;
use App\Rules\RuleFacilityUnavailabilityConflict;
use App\Models\Facility\Facility;
use App\Models\Facility\FacilityAvailability;
use App\Models\Facility\FacilityMarkUnavailable;
use App\Models\FacilityBooking\FacilityBooking;
use Carbon\Carbon;

class UpdateFacilityRequest extends StoreFacilityRequest
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
        $facility = $this->route('facility');
        $rules["facilityFormData.active_from_frm"] = [
            "required",
            "date_format:d/m/Y",
            new RuleFacilityActiveFrom($this->route('facility'))
        ];
        $rules['facilityChangeBookingType.change_booking_type_start_date_frm'] = [
            'required_with:facilityChangeBookingType.change_booking_type_end_date_frm',
            'date_format:d/m/Y',
            'nullable',
            'before_or_equal:facilityChangeBookingType.change_booking_type_end_date_frm'
        ];
        $rules['facilityChangeBookingType.change_booking_type_end_date_frm'] = [
            'required_with:facilityChangeBookingType.change_booking_type_start_date_frm',
            'date_format:d/m/Y',
            'nullable',
            'after_or_equal:facilityChangeBookingType.change_booking_type_start_date_frm'
        ];

        $rules['validate_linked_facility'] = [
            'required',
            new RuleCheckLinkedFacilityBookings($this->route('facility'), $this->all())
        ];

        $rules['confirm_mark_unavailable'] = ['nullable', 'boolean'];

        $confirm = (bool) $this->input('confirm_mark_unavailable', false);
        $rules['facilityMarkAsUnavailable'] = [
            'bail',
            'array',
            new RuleFacilityUnavailabilityConflict($facility, $confirm)
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
        $attributes['facilityChangeBookingType.change_booking_type_start_date_frm'] = 'Change Booking Type Start Date';
        $attributes['facilityChangeBookingType.change_booking_type_end_date_frm'] = 'Change Booking Type End Date';
        return $attributes;
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = parent::messages();
        return $messages;
    }
}
