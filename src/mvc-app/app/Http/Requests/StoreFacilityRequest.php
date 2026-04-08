<?php

namespace App\Http\Requests;

use App\Models\Facility\Equipment;
use App\Models\Facility\Facility;
use App\Models\Facility\FacilityLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityRequest extends FormRequest
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
        $edit = $this->route('facility') != null ? $this->route('facility')->FC_FacilityID : 0;
        return [
            "facilityFormData" => "required|array",
            "facilityFormData.facility_frm" => [
                "required",
                "max:50",
                Rule::unique('Facilities', 'FC_FacilityName')->whereNull('deleted_at')->ignore($edit, 'FC_FacilityID')
            ],
            "facilityFormData.active_from_frm" => "required|date_format:d/m/Y",
            "facilityFormData.area_owner_frm" => "required",
            "facilityFormData.provider_type_frm" => "required",
            "facilityFormData.provider_name_frm" => "required",
            "facilityFormData.internal_location_frm" => 'required_if:facilityFormData.provider_type_frm,' . FacilityLocation::PROVIDER_TYPE_INTERNAL,
            "facilityFormData.location_notes_frm" => "max:255",
            "facilityFormData.facility_capacity_frm" => "required|numeric|min:0|max:9999",
            "facilityFormData.facility_accessible_frm" => "required",
            "facilityFormData.accessibility_notes_frm" => "required_if:facilityFormData.facility_accessible_frm,limited|max:255",
            "facilityFormData.facility_default_booking_frm" => "required",
            "facilityFormData.facility_make_bookings_private_frm" => "required",
            "facilityFormData.allow_booking_frm" => 'required_if:facilityFormData.facility_default_booking_frm,' . Facility::DEFAULT_BOOKING_MANAGED,
            "facilityFormData.on_off_booking_frm" => 'required_if:facilityFormData.facility_default_booking_frm,' . Facility::DEFAULT_BOOKING_SELF_BOOKED,
            "facilityFormData.allow_self_booking_from_frm" => "required_if:facilityFormData.facility_default_booking_frm," . Facility::DEFAULT_BOOKING_SELF_BOOKED . "|numeric|min:0|max:999",
            "facilityFormData.allow_self_booking_to_frm" => "required_if:facilityFormData.facility_default_booking_frm," . Facility::DEFAULT_BOOKING_SELF_BOOKED . "|numeric|max:999|gt:facilityFormData.allow_self_booking_from_frm",
            "facilityFormData.pop_up_notes_frm" => "max:255",
            "facilityFormData.facility_notes_frm" => "max:255",
            "facilityFormData.facility_contact" => [
                'required',
                function ($attribute, $value, $fail) {
                    $emails = array_filter(array_map('trim', explode(',', $value)));
                    if (count($emails) !== count(array_unique($emails))) {
                        $fail("The Facility Contact contains duplicate emails.");
                    }
                    foreach ($emails as $email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            return $fail("The {$attribute} contains an invalid email: {$email}");
                        }
                        if (!checkdnsrr(substr(strrchr($email, "@"), 1), 'MX')) {
                            $fail("The {$attribute} contains an email with invalid domain: {$email}");
                        }
                    }
                },
            ],
            //Location external UK
            "facilityExternalUkLocationForm.location_external_uk_building_number" => "required_if:facilityFormData.provider_type_frm," . FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK,
            "facilityExternalUkLocationForm.location_external_uk_street" => "required_if:facilityFormData.provider_type_frm," . FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK,
            "facilityExternalUkLocationForm.location_external_uk_city" => "required_if:facilityFormData.provider_type_frm," . FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK,
            "facilityExternalUkLocationForm.location_external_uk_county" => "required_if:facilityFormData.provider_type_frm," . FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK,
            "facilityExternalUkLocationForm.location_external_uk_post_code" => "required_if:facilityFormData.provider_type_frm," . FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK,
            "facilityExternalUkLocationForm.location_external_uk_country" => "required_if:facilityFormData.provider_type_frm," . FacilityLocation::PROVIDER_TYPE_EXTERNAL_UK,

            //Location external International
            "facilityExternalUkInternationalForm.location_external_international_address" => "required_if:facilityFormData.provider_type_frm," . FacilityLocation::PROVIDER_TYPE_EXTERNAL_INTERNATIONAL,

            //Facility Availability
            "facilityAvailability.facility_availability_saturday_from_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_saturday_to_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_sunday_from_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_sunday_to_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_monday_from_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_monday_to_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_tuesday_from_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_tuesday_to_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_wednesday_from_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_wednesday_to_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_thursday_from_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_thursday_to_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_friday_from_frm" => "required|date_format:H:i",
            "facilityAvailability.facility_availability_friday_to_frm" => "required|date_format:H:i",

            //Mark Unavailable
            "facilityMarkAsUnavailable.mark_as_unavailable_start_date_frm" => [
                'required_with:facilityMarkAsUnavailable.mark_as_unavailable_end_date_frm',
                'date_format:d/m/Y',
                'nullable',
                'before_or_equal:facilityMarkAsUnavailable.mark_as_unavailable_end_date_frm'
            ],
            "facilityMarkAsUnavailable.mark_as_unavailable_end_date_frm" => [
                'date_format:d/m/Y',
                'nullable',
                'after_or_equal:facilityMarkAsUnavailable.mark_as_unavailable_start_date_frm'
            ],

            //Facility Type
            "facilityTypeForm.facility_type_frm" => "required",
            "facilityTypeForm.facility_sub_type_frm" => "required|array",
            "facilityTypeForm.facility_sub_type_primary_frm" => "required",

            //Technical setup
            "facilityTechnicalSetupForm" => "required:array",
            "facilityTechnicalSetupForm.equipment" => "required:array",
            "facilityTechnicalSetupForm.service_list_select_to" => "required:array",
            "facilityTechnicalSetupForm.equipment.*.type" => "required",
            "facilityTechnicalSetupForm.equipment.*.quantity" => [
                "required_if:facilityTechnicalSetupForm.equipment.*.type," . Equipment::EQUIPMENT_TYPE_ET,
                "numeric",
                "min:1",
                "max:9999"
            ],

            //If front end validation failed
            "facilityFormValidation" => [
                'required',
                'boolean',
                function ($attribute, $value, $fail) {
                    if ($value != 1) {
                        $fail('Front end validation Failed.');
                    }
                }
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
            "facilityFormData.facility_frm" => "Facility",
            "facilityFormData.active_from_frm" => "Active From",
            "facilityFormData.area_owner_frm" => "Area Owner",
            "facilityFormData.provider_type_frm" => "Provider Type",
            "facilityFormData.provider_name_frm" => "Provider Name",
            "facilityFormData.internal_location_frm" => 'Location',
            "facilityFormData.location_notes_frm" => "Location Notes",
            "facilityFormData.facility_capacity_frm" => "Facility Capacity",
            "facilityFormData.facility_accessible_frm" => "Accessible",
            "facilityFormData.accessibility_notes_frm" => "Accessibility Notes",
            "facilityFormData.facility_default_booking_frm" => "Default Booking Type",
            "facilityFormData.facility_make_bookings_private_frm" => "Make All Bookings Private",
            "facilityFormData.allow_booking_frm" => 'Allow Booking Request',
            "facilityFormData.on_off_booking_frm" => 'One-Off Bookings Only',
            "facilityFormData.allow_self_booking_from_frm" => "Allow Self Booking from",
            "facilityFormData.allow_self_booking_to_frm" => "Allow Self Booking To",
            "facilityFormData.pop_up_notes_frm" => "Pop-up Notes",
            "facilityFormData.facility_notes_frm" => "Facility Notes",
            "facilityLinkFacilityForm.facility_link_chose" => "Link Facilities",
            "facilityFormData.facility_contact" => "Facility Contact Email",
            "facilityMarkAsUnavailable.mark_as_unavailable_start_date_frm" => "Mark as Unavailable Start Date",
            "facilityMarkAsUnavailable.mark_as_unavailable_end_date_frm" => "Mark as Unavailable End Date"
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //External Uk location
            "facilityExternalUkLocationForm.location_external_uk_building_number.required_if" => "The Building Number/Name field is required.",
            "facilityExternalUkLocationForm.location_external_uk_street.required_if" => "The Street field is required.",
            "facilityExternalUkLocationForm.location_external_uk_city.required_if" => "The City field is required.",
            "facilityExternalUkLocationForm.location_external_uk_county.required_if" => "The County field is required.",
            "facilityExternalUkLocationForm.location_external_uk_post_code.required_if" => "The Postcode field is required.",
            "facilityExternalUkLocationForm.location_external_uk_country.required_if" => "The Country field is required.",

            //International location
            "facilityExternalUkInternationalForm.location_external_international_address.required_if" => "The Complete Address field is required.",

            //Facility type
            "facilityTypeForm.facility_type_frm.required" => "Facility Type",
            "facilityTypeForm.facility_sub_type_frm.required" => "Facility Sub Type",
            "facilityTypeForm.facility_sub_type_primary_frm.required" => "Primary Sub Type",

            //Allow self booking
            "facilityFormData.allow_self_booking_from_frm.min" => "Self Booking From value should be minimum of 0 and maximum of 999",
            "facilityFormData.allow_self_booking_from_frm.max" => "Self Booking From value should be minimum of 0 and maximum of 999",
            "facilityFormData.allow_self_booking_to_frm.min" => "Self Booking To value should be minimum of 0 and maximum of 999",
            "facilityFormData.allow_self_booking_to_frm.max" => "Self Booking To value should be minimum of 0 and maximum of 999",
        ];
    }
}
