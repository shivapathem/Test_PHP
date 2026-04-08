<form id="create-facility-booking-form" autocomplete="off">

    <link rel="stylesheet" href="/mvc-app/public/{{mix('css/facility-booking/facility-booking-form.css')}}" />
    @php
        $viewOnly = $viewOnly ?? false;
        $isLocked = !isset($facilityBooking) || (isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking !== null);
        $isDisabled = $viewOnly || isset($copyBookingDate) || isset($moveBookingDate);

        $facilityBookingsPrivate = $facility->FC_MakeAllBookingsPrivate ?? App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_NO;
        $bookingFBPrivate  = isset($facilityBooking) ? $facilityBooking->FB_Private : null;
        // check priority
        $priority = [
            App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_NO => 0,
            App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_YES => 2,
            App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_SUMMARY => 1,];
        if ($bookingFBPrivate !== null) {
            $selectedPrivateType = $priority[$bookingFBPrivate] > $priority[$facilityBookingsPrivate] ? $bookingFBPrivate : $facilityBookingsPrivate;
        } else {
            $selectedPrivateType = $facilityBookingsPrivate;
        }

        $disableDropdown = $viewOnly || ($selectedPrivateType === App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_YES && $facilityBookingsPrivate === App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_YES);
        $disableNo = (($selectedPrivateType === App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_SUMMARY && $facilityBookingsPrivate === App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_SUMMARY) || ($selectedPrivateType === App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_YES && $facilityBookingsPrivate === App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_SUMMARY));

    @endphp

    @if(isset($facilityBooking) && !isset($copyBookingDate))
        @php($bookingType = $facilityBooking->facilityBookingRecurrence->FBR_BookingType)
    @elseif(isset($createFacilityRecord) && $createFacilityRecord == 1)
        @php($bookingType = App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_RECORD)
    @else
        @php($bookingType = App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_REQUEST)
    @endif
    <div class="facility-booking-form fb-table">
            <div class="fb-row">
                <div id="facility-booking-label-facility" class="fb-cell">Facility</div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-facility">
                    @if($isLocked)
                        <span id="facility-booking-facility-name">{{$facility->FC_FacilityName}}</span>
                        <input type="hidden" name="facility_id" id="facility-chosen" value="{{$facility->FC_FacilityID}}">
                    @else
                    <select name="facility_id" id="facility-chosen" aria-labelledby="facility-booking-label-facility" {{ $isDisabled ? 'disabled' : '' }}>
                         @foreach(App\Models\Facility\Facility::all() as $facilityList)
                        <option value="{{$facilityList->FC_FacilityID}}"
                            @if($facilityList->FC_FacilityID == $facility->FC_FacilityID)
                            selected
                            @endif
                        >
                            {{$facilityList->FC_FacilityName}}
                        </option>
                        @endforeach
                    </select>
                    @if($isDisabled)
                        <input type="hidden" name="facility_id" value="{{$facility->FC_FacilityID}}">
                    @endif
                    @endif
                </div>
                <div id="facility-booking-label-facility-sub-type" class="fb-cell">Facility Sub Type</div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-facility-sub-type">
                    @if($facility->facilitySubTypes->count() == 1)
                        {{$facility->facilitySubTypes->first()->FST_FacilitySubType}}
                        <input type="hidden" name="facility_booking_facility_sub_type_form" value="{{$facility->facilitySubTypes->first()->FST_FacilitySubTypeID}}">
                    @else
                        <select id="facility_booking_facility_sub_type_form" name="facility_booking_facility_sub_type_form" aria-labelledby="facility-booking-label-facility-sub-type" data-placeholder="Select Facility Sub Type" @if($viewOnly) disabled @endif>
                            <option></option>
                            @foreach($facility->facilitySubTypes as $facilitySubType)
                            <option value="{{$facilitySubType->FST_FacilitySubTypeID}}"
                            @if((!isset($facilityBooking->FB_FacilitySubTypeID) || isset($moveBookingDate)) && $facilitySubType->getOriginal('pivot_FCST_PrimarySubType') == 1)
                                selected
                            @endif
                            @if(isset($facilityBooking) && !isset($moveBookingDate) && $facilityBooking->FB_FacilitySubTypeID == $facilitySubType->FST_FacilitySubTypeID)
                                selected
                            @endisset
                            >{{$facilitySubType->FST_FacilitySubType}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
            <div class="fb-row">
                <div class="fb-cell">Booking Type</div>
                <div class="fb-cell">
                    {{$facility->default_booking_type}}
                </div>
                @if(isset($facilityBooking) && !isset($copyBookingDate))
                        <div class="fb-cell">
                            @if(isset($bookingType) && $bookingType == App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_REQUEST)
                                Booking Request ID
                            @elseif(isset($bookingType) && $bookingType == App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_RECORD)
                                Booking Record ID
                            @else
                                Booking ID
                            @endif
                        </div>
                        <div class="fb-cell">{{ $facilityBooking->FB_FacilityBookingID }}</div>
                    @else
                    <div class="fb-cell" data-colspan="2"></div>
                @endif
            </div>
            <div class="fb-row">
                <div class="fb-cell">Facility Type</div>
                <div class="fb-cell">
                    {{$facility->facilityType->FT_FacilityType}}
                </div>
                <div class="fb-cell">Location/Address</div>
                <div class="fb-cell">
                    {{$facility->current_location}}
                </div>
            </div>
            <div class="fb-row">
                <div class="fb-cell">Facility Contact Email</div>
                <div class="fb-cell">
                    {{-- To show multiple Facility Contact email --}}
                    {!! str_replace(',', ',<br>', $facility->FC_Contact) !!}
                </div>
                <div class="fb-cell">Technical Setup</div>
                <div class="fb-cell">
                    @include('pages.facility-booking.form.facility-booking-technical-setup')
                </div>
            </div>
            <div class="fb-row">
                @if(isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null)
                    <div class="fb-cell">Primary Booking Facility</div>
                    <div data-colspan="3" class="fb-cell">
                        {{$facilityBooking->linkedToFacilityBooking->facility->FC_FacilityName}}
                    </div>
                @else
                    <div class="fb-cell">Linked Facilities</div>
                    <div data-colspan="3" class="fb-cell">
                        <a href="javascript:void(0);" id="facility_booking_facility_link_open_frm" @if($viewOnly) style="display:none" @endif> Edit</a>
                    </div>
                @endif
            </div>
    </div>
    <div class="facility-booking-form fb-table" style="margin-top: 10px">
            <div class="fb-row">
                <div id="facility-booking-label-booking-title" class="fb-cell">Booking Title<span class="required-asterisk">*</span></div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-booking-title">
                    <input type="text" class="facility-booking-form-input" id="facility_request_booking_title" name="facility_request_booking_title" aria-labelledby="facility-booking-label-booking-title" maxlength="50" value="{{$facilityBooking->FB_BookingTitle ?? ''}}" @if($viewOnly) disabled @endif required aria-required="true">
                </div>
                <div class="fb-cell">Booking Status</div>
                <div class="fb-cell">
                    {{ $facilityBooking->formatted_booking_status ?? 'New'}}
                </div>
            </div>
            <div class="fb-row">
                <div id="facility-booking-label-date" class="fb-cell">Date<span class="required-asterisk">*</span></div>
                <div id="booking-date-container" class="fb-cell" aria-labelledby="facility-booking-label-date">
                    @php($bookingData = (isset($facilityBooking) ? $facilityBooking->FB_BookingStartDateTime->format('d/m/Y') : ''))
                    @if(isset($dateSelected) && !empty($dateSelected))
                    @php($bookingData = \Carbon\Carbon::parse($dateSelected)->format('d/m/Y'))
                    @endif
                    @isset($copyBookingDate)
                    @php($bookingData = \Carbon\Carbon::parse($copyBookingDate)->format('d/m/Y'))
                    @endisset
                    @isset($moveBookingDate)
                    @php($bookingData = \Carbon\Carbon::parse($moveBookingDate)->format('d/m/Y'))
                    @endisset
                    <input type="text" class="facility-booking-form-input" id="booking_date" name="booking_date" aria-labelledby="facility-booking-label-date"
                    @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true)
                    disabled
                    @endif
                    value="{{$bookingData}}" required aria-required="true">
                </div>
                <div id="facility-booking-label-private" class="fb-cell">Private?</div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-private">
                    <select class="facility-booking-form-input" id="private_booking" name="private_booking" aria-labelledby="facility-booking-label-private" @if($viewOnly || $disableDropdown) disabled @endif>
                        @foreach(App\Models\FacilityBooking\FacilityBookingRecurrence::privateBookingTypes() as $key => $privateType)
                            <option value="{{ $key }}"
                            @if($selectedPrivateType === $key) selected @endif
                            @if($key === App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_NO && $disableNo) disabled @endif
                            >
                                {{ $privateType }}
                            </option>
                         @endforeach
                    </select>
                </div>
            </div>
            <div class="fb-row">
                <div id="facility-booking-label-start-time" class="fb-cell">Start Time</div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-start-time">
                    <select id="booking_start_time" name="booking_start_time" aria-labelledby="facility-booking-label-start-time" data-placeholder="Select Start Time"
                    @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true)
                    disabled
                    @endif
                    >
                        <option></option>
                        @php($existingStartTime = isset($facilityBooking) ? $facilityBooking->FB_BookingStartDateTime->format('H:i') : '')
                        @if(empty($existingStartTime) && isset($clickPositionDateTime))
                        @php($existingStartTime = $clickPositionDateTime->format('H:i'))
                        @endif
                        @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                        @php($interval = $interval->format('H:i'))
                        <option value="{{$interval}}"
                        @if($existingStartTime == $interval) selected @endif
                        >{{$interval}}</option>
                        @endforeach
                    </select>
                </div>
                <div id="facility-booking-label-end-time" class="fb-cell">End Time</div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-end-time">
                    <select id="booking_end_time" name="booking_end_time" aria-labelledby="facility-booking-label-end-time" data-placeholder="Select End Time"
                    @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly)
                    disabled
                    @endif
                    >
                        <option></option>
                        @php($existingEndTime = isset($facilityBooking) ? $facilityBooking->FB_BookingEndDateTime->format('H:i') : '')
                        @if(empty($existingEndTime) && isset($clickPositionDateTime))
                        @php($existingEndTime = $clickPositionDateTime->clone()->addMinutes(15)->format('H:i'))
                        @endif
                        @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                        @php($interval = $interval->format('H:i'))
                        <option value="{{$interval}}"
                        @if($existingEndTime == $interval) selected @endif
                        >{{$interval}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="fb-row">
                @php($recurringEnable = 1)
                @if(($facility->FC_DefaultBookingType == App\Models\Facility\Facility::DEFAULT_BOOKING_SELF_BOOKED && $facility->FC_OneOffBookingsOnly == 1) || $viewOnly)
                    @php($recurringEnable = 0)
                @endif
                @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_BookingType == \App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_RECORD || $viewOnly)
                    @php($recurringEnable = 1)
                @endif
                <div id="facility-booking-label-recurring" class="fb-cell">Recurring Booking</div>
                <div id="recurring-booking-detail-container" class="fb-cell" aria-labelledby="facility-booking-label-recurring">
                    <input type="radio" id="recurring_booking_yes" name="recurring_booking_enable" value="yes" aria-labelledby="facility-booking-label-recurring"
                        @if($recurringEnable == 0 || $viewOnly == true || (isset($facilityBooking->linkedToFacilityBooking) && $facilityBooking->linkedToFacilityBooking != null))
                            disabled
                        @endif
                        @if(isset($facilityBookingRecurrence) && !$facilityBookingRecurrence->facilityRecurrenceStartDateEditable() && !isset($copyBookingDate)) disabled @endif
                        @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->isRecurring)
                            checked
                        @endif
                    >
                    <label for="recurring_booking_yes">Yes</label>
                    <input type="radio" id="recurring_booking_no" name="recurring_booking_enable" value="no" aria-labelledby="facility-booking-label-recurring"
                        @if($recurringEnable == 0 || $viewOnly == true || (isset($facilityBooking->linkedToFacilityBooking) && $facilityBooking->linkedToFacilityBooking != null))
                            disabled
                        @endif
                        @if(isset($facilityBookingRecurrence) && !$facilityBookingRecurrence->facilityRecurrenceStartDateEditable() && !isset($copyBookingDate)) disabled @endif
                        @if(!isset($facilityBooking) || (isset($facilityBookingRecurrence) && !$facilityBookingRecurrence->isRecurring))
                            checked
                        @endif
                    >
                    <label for="recurring_booking_no">No</label>
                </div>
                <div data-colspan="2" id="recurring-setting-detail-container" class="fb-cell"></div>
            </div>
    </div>
    <div class="facility-booking-form fb-table" style="margin-top: 10px">
            <div class="fb-row">
                <div id="facility-booking-label-customer-type" class="fb-cell">Customer Type</div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-customer-type">
                    <select class="facility-booking-form-input" id="customer_type" name="customer_type" aria-labelledby="facility-booking-label-customer-type" @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true) disabled @endif>
                        @foreach(App\Models\FacilityBooking\FacilityBookingRecurrence::customerTypes() as $key => $customerType)
                            <option value="{{$key}}"
                            @if(isset($facilityBooking) && $facilityBooking->FB_CustomerType == $key)
                            selected
                            @endif
                            >{{$customerType}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-company-name">
                    <div id="facility-booking-label-company-name" class="external-customer-container"  @if(isset($facilityBooking) && $facilityBooking->FB_CustomerType == App\Models\FacilityBooking\FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL) style="display:block" @else style="display:none" @endif>
                        Company Name<span class="required-asterisk external-required">*</span>
                    </div>
                </div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-company-name">
                    <div class="external-customer-container facility-booking-form-input" @if(isset($facilityBooking) && $facilityBooking->FB_CustomerType == App\Models\FacilityBooking\FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL) style="display:block" @else style="display:none" @endif>
                        <select id="external_customer_company" name="external_customer_company" aria-labelledby="facility-booking-label-company-name" data-placeholder="Select Company Name" @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true) disabled @endif>
                            <option></option>
                            @foreach($externalCustomers as $externalCustomer)
                            <option
                            @if(isset($facilityBooking) && $facilityBooking->FB_ExternalCustomerID == $externalCustomer->EC_ExternalCustomerID) selected @endif
                            data-external-customer='@json($externalCustomer->only(['EC_ContactName', 'EC_ContactNumber', 'EC_ContactEmail']))' value="{{$externalCustomer->EC_ExternalCustomerID}}">{{$externalCustomer->EC_CompanyName}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="fb-row">
                <div id="facility-booking-label-contact-name" class="fb-cell">Contact Name<span class="required-asterisk external-required">*</span></div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-contact-name">
                    <input placeholder="Use if Customer is different from Requestor" class="facility-booking-form-input" type="text" name="customer_contact_name" id="customer_contact_name" aria-labelledby="facility-booking-label-contact-name" value="{{$facilityBooking->FB_ContactName ?? auth()->user()->UD_DisplayName}}" @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true) disabled @endif
                    @include('includes.input-text-name-regex')
                    >
                </div>
                <div id="facility-booking-label-contact-telephone" class="fb-cell">Contact Telephone<span class="required-asterisk external-required">*</span></div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-contact-telephone">
                    <input placeholder="Use if Customer is different from Requestor" class="facility-booking-form-input" type="text" name="customer_contact_telephone" id="customer_contact_telephone" aria-labelledby="facility-booking-label-contact-telephone" value="{{$facilityBooking->FB_ContactTelephone ?? auth()->user()->UD_OfficePhone}}" @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true) disabled @endif
                    @include('includes.input-telephone-number-regex')
                    >
                </div>
            </div>
            <div class="fb-row">
                <div id="facility-booking-label-contact-email" class="fb-cell">Contact Email<span class="required-asterisk">*</span></div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-contact-email">
                    <input placeholder="Use if Customer is different from Requestor" class="facility-booking-form-input" type="text" name="customer_contact_email" id="customer_contact_email" aria-labelledby="facility-booking-label-contact-email" value="{{$facilityBooking->FB_ContactEmail ?? auth()->user()->UD_InternalEmail}}" @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true) disabled @endif required aria-required="true">
                </div>
                <div class="fb-cell">
                    <span id="facility-booking-label-requestor-name">Requestor Name<span class="required-asterisk">*</span></span>
                </div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-requestor-name">
                    <input type="text" id="requestor_name" class="facility-booking-form-input" name="requestor_name" aria-labelledby="facility-booking-label-requestor-name" value="{{$facilityBooking->FB_RequestorName ?? auth()->user()->UD_DisplayName}}" @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true) disabled @endif required aria-required="true"
                    @include('includes.input-text-name-regex')
                    >
                </div>
            </div>
            <div class="fb-row">
                <div id="facility-booking-label-requestor-detail" class="fb-cell">Requestor Details<span class="required-asterisk">*</span></div>
                <div class="fb-cell" aria-labelledby="facility-booking-label-requestor-detail">
                    <input value="{{$facilityBooking->FB_RequestorDetail ?? auth()->user()->UD_InternalEmail}}" class="facility-booking-form-input" type="text" name="requestor_detail" id="requestor_detail" aria-labelledby="facility-booking-label-requestor-detail" @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true) disabled @endif required aria-required="true">
                </div>
                <div data-colspan="2" class="fb-cell">
                </div>
            </div>
    </div>
    <div class="facility-booking-form fb-table" style="margin-top: 10px">
            <div
            @if($bookingType == App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_RECORD)
            style="display: none"
            @endif
             class="fb-row">
                <div id="facility-booking-label-requestor-notes" class="fb-cell">Requestor Notes</div>
                <div data-colspan="3" class="fb-cell" aria-labelledby="facility-booking-label-requestor-notes">
                    <textarea id="requestor_notes" name="requestor_notes" rows="1" class="facility-booking-form-input" aria-labelledby="facility-booking-label-requestor-notes"
                        @if($viewOnly) readonly style="background:#ededed;" @endif>{{($facilityBooking->FB_RequestorNote ?? '')}}</textarea>
                </div>
            </div>
            <div
            @if($bookingType == App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_REQUEST)
            style="display: none"
            @endif
             class="fb-row">
                <div id="facility-booking-label-scheduler-notes" class="fb-cell">Scheduler Notes</div>
                <div data-colspan="3" class="fb-cell" aria-labelledby="facility-booking-label-scheduler-notes">
                    <textarea id="scheduler_notes" name="scheduler_notes" rows="1" class="facility-booking-form-input" aria-labelledby="facility-booking-label-scheduler-notes"
                        @if($viewOnly) readonly style="background:#ededed;" @endif>{{ isset($copyBookingDate) ? '' : ($facilityBooking->FB_SchedulerNote ?? '') }}</textarea>
                </div>
            </div>
            <div class="fb-row">
                <div class="fb-cell">Location Notes</div>
                <div data-colspan="3" class="fb-cell">
                    {{$facility->FC_LocationNote}}
                </div>
            </div>
            <div class="fb-row">
                <div class="fb-cell">Facility Notes</div>
                <div data-colspan="3" class="fb-cell">
                    {{$facility->FC_FacilityNote}}
                </div>
            </div>
            <div class="fb-row">
                <div class="fb-cell">Accessibility Notes</div>
                <div data-colspan="3" class="fb-cell">
                    {{$facility->FC_AccessibilityNote}}
                </div>
            </div>
            <div class="fb-row">
                <div class="fb-cell">Notes</div>
                <div data-colspan="3" class="fb-cell">
                    {{$facility->FC_PopupNote}}
                </div>
            </div>
    </div>
    <div class="facility-booking-form fb-table" style="margin-top: 10px">
            <div class="fb-row">
                <div class="fb-cell">Action</div>
                <div id="actions-detail" class="fb-cell">
                </div>
                <div @if($viewOnly) style="display: none" @endif class="fb-cell">Edit Action</div>
                <div id="action-edit-container" @if($viewOnly) style="display: none" @endif class="fb-cell"><a href="javascript:void(0);" id="edit-action-open-frm"> Edit </a></div>
            </div>
    </div>
    @csrf
    @if((isset($createFacilityRecord) && $createFacilityRecord) || isset($facilityBooking))
    <input type="hidden" id="facility-booking-current-status-hidden" name="booking_status" value='{{$facilityBooking->FB_BookingStatus ?? ''}}'>
    @endif
    @isset($moveBookingDate)
    <input type="hidden" id="moving_action" name ="moving_action" value="1">
    @endisset
    <input type="hidden" id="copy_booking_id" name ="copy_booking_id" value="@if(isset($copyBookingDate)){{($facilityBooking->FB_FacilityBookingID ?? '')}}@endif" />
    <input type="hidden" id="moved_booking" name ="moved_booking" value="@if(isset($facilityBooking) && !isset($copyBookingDate) && $facility->FB_FacilityID != $facilityBookingRecurrence->FBR_FacilityID){{1}}@else{{0}}@endif" />
    <input type="hidden" id="facility_booking_data_type" name="facility_booking_data_type" value="{{$bookingType}}" >
</form>
<input type="hidden" id="facility-area-owner-booking-frm" value={{$facility->FC_AreaOwnerID}}>
<input type="hidden" id="facility-restricted-bookers-booking-frm" value={{$facility->facilityRestrictBookers->pluck('schedulingTeamId')->implode(',')}}>
<input type="hidden" id="facility-active-from" value="{{$facility->FC_ActiveFrom}}">
<input type="hidden" id="facility-archive-from" value="{{(!empty($facility->FC_ArchivedDate) ? $facility->FC_ArchivedDate->format('Y-m-d') : '')}}">
<input type="hidden" id="facility-availability" value='@json($facility->facilityAvailability)'>
<input type="hidden" id="facility-unavailability" value='@json($facility->facilityMarkAsUnavailable)'>
<input type="hidden" id="facility-id" value='{{$facility->FC_FacilityID}}'>
<input type="hidden" id="facility-default-booking-type" value='{{$facility->FC_DefaultBookingType}}'>
<input type="hidden" id="facility-allow-self-booking-from" value='{{$facility->FC_AllowSelfBookingFrom}}'>
<input type="hidden" id="facility-allow-self-booking-to" value='{{$facility->FC_AllowSelfBookingTo}}'>
@if(isset($facilityBooking) && !isset($copyBookingDate)) {{-- Required only while editing --}}
<input type="hidden" id="facility-booking-recurrence-id" value={{$facilityBooking->FB_FacilityBookingRecurrenceID}}>
<input type="hidden" id="facility-booking-id-hidden" value='{{$facilityBooking->FB_FacilityBookingID}}'>
<input type="hidden" id="facility-booking-is-linked-booking-hidden" value="{{($facilityBooking->linkedToFacilityBooking != null ? 1 : 0)}}" >
<input type="hidden" id="facility-booking-current-facility-id-hidden" value='{{$facilityBooking->FB_FacilityID}}'>
<input type="hidden" id="facility-booking-recurrence-booking-total-bookings-hidden" value='{{$facilityBooking->facilityBookingRecurrence->availableFacilityBookings(true, false)->count()}}'>
<input type="hidden" id="facility-booking-edit-existing-date-hidden" value='{{$facilityBooking->FB_BookingStartDateTime->format('d/m/Y')}}'>
@endif
@if(isset($availableRecurrenceFacilityBookingCount))
<input type="hidden" id="facility-booking-recurrence-booking-available-count" value="{{$availableRecurrenceFacilityBookingCount}}">
@endif
@if(isset($moveBookingDate))
<input type="hidden" id="move-facility-booking" value="1">
@endif
@if(isset($createFacilityRecord) && $createFacilityRecord)
<input type="hidden" id="facility-create-booking-record" value="1">
@endif
<div id="overlap-validation">
</div>
<div id="facility-availability-validation">
</div>
<div id="recurrence-overlap-validation">
</div>
<br>
<div style="text-align:right">
    @if(!$viewOnly)
        @if(
        (isset($createFacilityRecord) && $createFacilityRecord) ||
        (
            (isset($facilityBooking) &&
            $facilityBooking->linkedToFacilityBooking == null) &&
            $adminAccess == true &&
            ($facility->FC_DefaultBookingType == App\Models\Facility\Facility::DEFAULT_BOOKING_MANAGED || (isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_BookingType == \App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_RECORD))
        )
        )
            @php($showButtons = $facility->FC_DefaultBookingType == App\Models\Facility\Facility::DEFAULT_BOOKING_MANAGED)
            @if($showButtons)
            <button data-status="{{\App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_PENDING}}" style="font-size:12px" id="submit-pending-facility-booking-form-button" class="ui-button ui-widget ui-corner-all">Save as Pending</button>
            @endif
            <button data-status="{{\App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_CONFIRMED}}" style="font-size:12px" id="submit-confirm-facility-booking-form-button" class="ui-button ui-widget ui-corner-all">Save as Confirmed</button>
            @if($showButtons && isset($facilityBooking) && !isset($copyBookingDate) && !isset($moveBookingDate))
            <button data-status="{{\App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_DECLINED}}" style="font-size:12px" id="submit-decline-facility-booking-form-button" class="ui-button ui-widget ui-corner-all">Save as Declined</button>
            @endif
        @else
            <button style="font-size:12px" id="submit-facility-booking-form-button" class="ui-button ui-widget ui-corner-all">@if(isset($facilityBooking) && !isset($copyBookingDate)) Save Changes @else Submit Request  @endif</button>
        @endif
    @endif
        <button style="font-size:12px" id="submit-cancel-facility-booking-form-button" class="ui-button ui-widget ui-corner-all">Close</button>
</div>
@include('pages.facility-booking.form.facility-booking-facility-link')
@include('pages.facility-booking.form.facility-booking-recurring')
@include('pages.facility-booking.form.facility-booking-action')
@include('pages.facility-booking.form.facility-booking-popup-note')
@include('pages.facility-booking.form.facility-linked-unavailability')
@include('pages.facility-booking.form.recurring-booking-warning')
@include('pages.facility-booking.form.facility-booking-managed-facility-booking-conflict')
