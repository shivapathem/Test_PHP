<?php

namespace App\Http\Requests;

use App\Models\Facility\Facility;
use App\Models\FacilityBooking\FacilityBookingRecurrence;
use App\Rules\RuleCheckBookingOverlap;
use App\Rules\RuleFacilityAvailability;
use App\Rules\RuleRecurringDate;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFacilityBookingRequest extends FormRequest
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
        $facility = $this->route('facility') ?? $this->route('facilityBooking')->facility;
        $date = $this->input('facilityBookingMainData.booking_date');
        $parsedDate = $date ? Carbon::createFromFormat('d/m/Y', $date) : Carbon::now();
        return [
            'facilityBookingMainData' => 'required|array',
            'facilityBookingMainData.facility_booking_data_type' => 'required',
            'facilityBookingMainData.facility_booking_facility_sub_type_form' => 'required',
            'facilityBookingMainData.facility_request_booking_title' => 'required|max:50',
            'facilityBookingMainData.booking_date' => [
                "required",
                "date_format:d/m/Y",
                "after_or_equal:" . ($facility->FC_ActiveFrom->gte(Carbon::now()) ? $facility->FC_ActiveFrom->format('Y-m-d') : Carbon::now()->format('Y-m-d')),
            ],
            'facilityBookingMainData.private_booking' => 'required|in:' . implode(',', array_keys(FacilityBookingRecurrence::privateBookingTypes())),
            'facilityBookingMainData.booking_start_time' => [
                'required',
                'date_format:H:i',
                new RuleRecurringDate(
                    //Carbon::createFromFormat('d/m/Y', $this->input('facilityBookingMainData.booking_date')),
                    $parsedDate,
                    $this->input('facilityBookingRecurring.recurring_booking_recurrence_type') ?? '',
                    $this->input('facilityBookingRecurring.recurring_booking_weekly_days', []),
                    'Start time must be in the future based on the first recurring date.',
                    'The start time must be a future time if the booking date is today.'
                )
            ],
            'facilityBookingMainData.booking_end_time' => [
                'required',
                'date_format:H:i',
                new RuleRecurringDate(
                    //Carbon::createFromFormat('d/m/Y', $this->input('facilityBookingMainData.booking_date')),
                    $parsedDate,
                    $this->input('facilityBookingRecurring.recurring_booking_recurrence_type') ?? '',
                    $this->input('facilityBookingRecurring.recurring_booking_weekly_days', []),
                    'The end time must be in the future based on the next recurring day.',
                    'The end Time must be a future time if the booking date is today.',
                    Carbon::createFromFormat('H:i', $this->input('facilityBookingMainData.booking_start_time'))
                )
            ],
            'facilityBookingMainData.recurring_booking_enable' => 'required',
            'facilityBookingMainData.customer_type' => 'required|in:' . implode(',', array_keys(FacilityBookingRecurrence::customerTypes())),
            'facilityBookingMainData.external_customer_company' => 'required_if:facilityBookingMainData.customer_type,' . FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL,
            'facilityBookingMainData.customer_contact_name' => 'required_if:facilityBookingMainData.customer_type,' . FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL . '|max:50',
            'facilityBookingMainData.customer_contact_telephone' => 'required_if:facilityBookingMainData.customer_type,' . FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL . '|nullable|numeric|regex:/^([0-9\s\-\+\(\)]{10,15})$/|min:10',

            'facilityBookingMainData.customer_contact_email' => 'required_if:facilityBookingMainData.customer_type,' . FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL . '|max:50|regex:/^(?!.*\.\.)([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/',
            'facilityBookingMainData.requestor_name' => 'required|max:50',
            'facilityBookingMainData.requestor_detail' => 'required|max:50|regex:/^(?!.*\.\.)([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/',
            'facilityBookingMainData.requestor_notes' => 'nullable|string|max:255',
            'facilityBookingMainData.scheduler_notes' => 'nullable|string|max:255',

            //Recurring
            'facilityBookingRecurring' =>  "required_if:facilityBookingMainData.recurring_booking_enable,yes|array",
            'facilityBookingRecurring.recurring_start_date' => [
                "required_if:facilityBookingMainData.recurring_booking_enable,yes",
                "nullable",
                "date_format:d/m/Y",
                "after_or_equal:" . ($facility->FC_ActiveFrom->gte(Carbon::now()) ? $facility->FC_ActiveFrom->format('Y-m-d') : Carbon::now()->format('Y-m-d')),
            ],
            'facilityBookingRecurring.recurring_end_date' => [
                "required_if:facilityBookingMainData.recurring_booking_enable,yes",
                "nullable",
                "date_format:d/m/Y",
                "after_or_equal:" . ($facility->FC_ActiveFrom->gte(Carbon::now()) ? $facility->FC_ActiveFrom->format('Y-m-d') : Carbon::now()->format('Y-m-d')),
            ],
            'facilityBookingRecurring.recurring_booking_recurrence_type' => "required_if:facilityBookingMainData.recurring_booking_enable,yes",
            'facilityBookingRecurring.recurring_booking_recurrence_daily_days' => "required_if:facilityBookingRecurring.recurring_booking_recurrence_type,daily,facilityBookingMainData.recurring_booking_enable,yes",
            'facilityBookingRecurring.recurring_booking_recurrence_weekly_weeks' => "required_if:facilityBookingRecurring.recurring_booking_recurrence_type,weekly,facilityBookingMainData.recurring_booking_enable,yes",
            'facilityBookingRecurring.recurring_booking_weekly_days' => "required_if:facilityBookingRecurring.recurring_booking_recurrence_type,weekly,facilityBookingMainData.recurring_booking_enable,yes|array",
            'facilityBookingActions' => 'nullable|array',
            'facilityBookingActions.*.actionStartTime' => 'nullable|date_format:H:i',
            'facilityBookingActions.*.actionEndTime' => 'nullable|date_format:H:i',

            //Validate bookings overlap
            'overlap_validate' => [
                'required',
                new RuleCheckBookingOverlap($facility, $this->all(), $this->route('facilityBooking') ?? null)
            ],

            //Validate Conflicts for managed bookings
            'managed_facility_booking_conflicts' => [
                'required',
                Rule::excludeIf(function () use ($facility) {
                    return $this->managed_facility_booking_conflicts == 1 || $facility->FC_DefaultBookingType == Facility::DEFAULT_BOOKING_SELF_BOOKED;
                }),
                new RuleCheckBookingOverlap($facility, $this->all(), $this->route('facilityBooking') ?? null, RuleCheckBookingOverlap::OVERLAP_ALL)
            ],

            //Validate bookings day availability
            'confirmAddUnavailableLinked' => [
                'required',
                'boolean'
            ],
            'facility_availability_validation' => [
                'required',
                new RuleFacilityAvailability($facility, $this->all(), $this->route('facilityBooking') ?? null)
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
            'facilityBookingMainData.facility_booking_facility_sub_type_form' => 'Facility Sub Type',
            'facilityBookingMainData.facility_request_booking_title' => 'Booking Title',
            'facilityBookingMainData.booking_date' => 'Date',
            'facilityBookingMainData.private_booking' => 'Private',
            'facilityBookingMainData.booking_start_time' => 'Start Time',
            'facilityBookingMainData.booking_end_time' => 'End Time',
            'facilityBookingMainData.recurring_booking_enable' => 'Recurring Booking',
            'facilityBookingMainData.customer_type' => 'Customer Type',
            'facilityBookingMainData.external_customer_company' => 'External Customer',
            'facilityBookingMainData.customer_contact_name' => 'Contact Name',
            'facilityBookingMainData.customer_contact_telephone' => 'Contact Telephone',
            'facilityBookingMainData.customer_contact_email' => 'Contact Email',
            'facilityBookingMainData.requestor_name' => 'Requestor Name',
            'facilityBookingMainData.requestor_detail' => 'Requestor Details',
            'facilityBookingMainData.requestor_notes' => 'Requestor Notes',
            'facilityBookingMainData.scheduler_notes' => 'Scheduler Notes',

            //Facility link
            'facilityBookingFacilityLink' => "Linked Facilities	",

            //Recurring
            'facilityBookingRecurring.recurring_start_date' => 'Start Date',
            'facilityBookingRecurring.recurring_end_date' => 'End Date',
            'facilityBookingRecurring.recurring_booking_recurrence_type' => "Recurrence Type",
            'facilityBookingRecurring.recurring_booking_recurrence_daily_days' => "Recurrence Day's",
            'facilityBookingRecurring.recurring_booking_recurrence_weekly_weeks' => "Recurrence Week's",
            'facilityBookingRecurring.recurring_booking_weekly_days' => "Week days",

            //Actions
            'facilityBookingActions.*.actionStartTime' => 'Start Time',
            'facilityBookingActions.*.actionEndTime' => 'End Time'
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
            "facilityBookingMainData.requestor_notes.required_if" => "The Requestor Notes is required.",
            "facilityBookingMainData.scheduler_notes.required_if" => "The Scheduler Notes is required.",
            "facilityBookingMainData.booking_date.required" => "The Booking Date is required."
        ];
    }
}
