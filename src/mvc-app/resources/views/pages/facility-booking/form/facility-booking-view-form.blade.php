<form id="create-facility-booking-form" autocomplete="off">
    <table class="facility-booking-form">
        <tbody>
            <tr>
                <td style="width: 20%;">Facility</td>
                <td><span id="facility-booking-facility-name">{{$facility->FC_FacilityName}}</span></td>
                <td style="width: 20%;">Facility Sub Type</td>
                <td>
                   {{$facility->facilitySubTypes->pluck('FST_FacilitySubType','FST_FacilitySubTypeID')->get($facilityBooking->FB_FacilitySubTypeID)}}
                </td>
            </tr>
            <tr>
                <td style="width: 20%;">Booking Type</td>
                <td>
                    {{$facility->default_booking_type}}
                </td>
                    <td style="width: 20%;">Booking ID</td>
                    <td>{{ $facilityBooking->FB_FacilityBookingID }}</td>
            </tr>
            <tr>
                <td>Facility Type</td>
                <td>
                    {{$facility->facilityType->FT_FacilityType}}
                </td>
                <td>Location/Address</td>
                <td>
                    {{$facility->current_location}}
                </td>
            </tr>
            <tr>
                <td>Facility Contact</td>
                <td>
                   {!! str_replace(',', ',<br>', $facility->FC_Contact) !!}
                </td>
                <td>Technical Setup</td>
                <td>
                    @include('pages.facility-booking.form.facility-booking-technical-setup')
                </td>
            </tr>
            <tr>
                @if(isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null)
                    <td>Primary Booking Facility</td>
                    <td colspan="3">
                        {{$facilityBooking->linkedToFacilityBooking->facility->FC_FacilityName}}
                    </td>
                @else
                    <td>Linked Facilities</td>
                    <td colspan="3">
                        <a href="javascript:void(0);" id="facility_booking_facility_link_open_frm" @if($viewOnly) style="display:none" @endif> Edit</a> 
                    </td>
                @endif
            </tr>
        </tbody>
    </table>
    <table class="facility-booking-form" style="margin-top: 10px">
        <tbody>
            <tr>
                <td style="width: 20%;">Booking Title</td>
                <td>
                   {{$facilityBooking->FB_BookingTitle}} 
                </td>
                <td style="width: 20%;">Booking Status</td>
                <td>
                    {{ $facilityBooking->formatted_booking_status }}
                </td>
            </tr>
            <tr>
                <td>Date</td>
                <td id="booking-date-container">
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
                    <input type="text" class="facility-booking-form-input" id="booking_date" name="booking_date"
                    @if((isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking != null) || $viewOnly == true)
                    style="display:none"
                    @endif
                    value="{{$bookingData}}">
                    <!-- show in view only -->
                        {{$bookingData}}
                </td>
                <td>Private?</td>
                <td>
                   @php ($privatedata = App\Models\FacilityBooking\FacilityBookingRecurrence::privateBookingTypes())
                    {{$privatedata[$facilityBooking->FB_Private]}}
                </td>
            </tr>
            <tr>
                <td>Start Time</td>
                <td>
                        @php($existingStartTime = isset($facilityBooking) ? $facilityBooking->FB_BookingStartDateTime->format('H:i') : '')
                      
                     @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                        @php($interval = $interval->format('H:i'))
                        @if($existingStartTime == $interval) {{$interval}} @endif
                        @endforeach    
                    
                </td>
                <td>End Time</td>
                <td>
                    @php($existingEndTime = isset($facilityBooking) ? $facilityBooking->FB_BookingEndDateTime->format('H:i') : '')
                    @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                        @php($interval = $interval->format('H:i'))
                        @if($existingEndTime == $interval) {{$interval}} @endif
                    @endforeach    
                   
                </td>
            </tr>
            <tr>
                <td>Recurring Booking</td>
                <td id="recurring-booking-detail-container">{{ $facilityBookingRecurrence->isRecurring ? 'Yes' : 'No' }}</td>
                <td colspan="2" id="recurring-setting-detail-container">
                    @if($facilityBookingRecurrence->isRecurring)
                        <span>Recurring Setting @if(isset($facilityBookingRecurrence)) Series ID: {{ $facilityBookingRecurrence->FBR_FacilityBookingRecurrenceID }} @endif</span>
                        <table id="recurring-setting-detail-table" style="font-size: 10px;border-collapse: inherit;width: 100%; margin-top: 4px;">
                            <tbody>
                                <tr>
                                    <td>Start Date : {{ $facilityBookingRecurrence->FBR_SeriesStartDate->format('d/m/Y') }}</td>
                                    <td>End Date : {{ $facilityBookingRecurrence->FBR_SeriesEndDate->format('d/m/Y') }}</td>
                                    <td>Start Time : <span id="recurring-setting-detail-start-datetime">{{ substr($facilityBookingRecurrence->FBR_StartTime, 0, 5) }}</span></td>
                                    <td>End Time : <span id="recurring-setting-detail-end-datetime">{{ substr($facilityBookingRecurrence->FBR_EndTime, 0, 5) }}</span></td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        @if($facilityBookingRecurrence->FBR_RecurrenceType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY)
                                            Recur every {{ $facilityBookingRecurrence->FBR_RecurrenceDayInterval }} day(s)
                                        @elseif($facilityBookingRecurrence->FBR_RecurrenceType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY)
                                            Recur every {{ $facilityBookingRecurrence->FBR_RecurrenceWeekInterval }} week(s) on 
                                            @php($weekDays = [])
                                            @if($facilityBookingRecurrence->FBR_RecurrenceWeek_Saturday) @php($weekDays[] = 'Saturday') @endif
                                            @if($facilityBookingRecurrence->FBR_RecurrenceWeek_Sunday) @php($weekDays[] = 'Sunday') @endif
                                            @if($facilityBookingRecurrence->FBR_RecurrenceWeek_Monday) @php($weekDays[] = 'Monday') @endif
                                            @if($facilityBookingRecurrence->FBR_RecurrenceWeek_Tuesday) @php($weekDays[] = 'Tuesday') @endif
                                            @if($facilityBookingRecurrence->FBR_RecurrenceWeek_Wednesday) @php($weekDays[] = 'Wednesday') @endif
                                            @if($facilityBookingRecurrence->FBR_RecurrenceWeek_Thursday) @php($weekDays[] = 'Thursday') @endif
                                            @if($facilityBookingRecurrence->FBR_RecurrenceWeek_Friday) @php($weekDays[] = 'Friday') @endif
                                            {{ implode(', ', $weekDays) }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
    <table class="facility-booking-form" style="margin-top: 10px">
        <tbody>
            <tr>
                <td style="width: 20%;">Customer Type</td>
                <td> @php($customerTypeData = App\Models\FacilityBooking\FacilityBookingRecurrence::customerTypes()) 
                    {{$customerTypeData[$facilityBooking->FB_CustomerType]}}</td>
                <td style="width: 20%;">
                    <div class="external-customer-container"  @if(isset($facilityBooking) && $facilityBooking->FB_CustomerType == App\Models\FacilityBooking\FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL) style="display:block" @else style="display:none" @endif>
                        Company Names
                    </div>
                </td>
                <td>
                    <div class="external-customer-container" @if(isset($facilityBooking) && $facilityBooking->FB_CustomerType == App\Models\FacilityBooking\FacilityBookingRecurrence::CUSTOMER_TYPE_EXTERNAL) style="display:block" @else style="display:none" @endif>
                        {{$externalCustomers->pluck('EC_CompanyName','EC_ExternalCustomerID')->get($facilityBooking->FB_ExternalCustomerID)}}
                    </div>
                </td>
            </tr>
            <tr>
                <td>Contact Name</td>
                <td> {{$facilityBooking->FB_ContactName ?? auth()->user()->UD_DisplayName}}</td>
                <td style="width: 20%;">Contact Telephone</td>
                <td>{{$facilityBooking->FB_ContactTelephone ?? auth()->user()->UD_OfficePhone}} </td>
            </tr>
            <tr>
                <td>Contact Email</td>
                <td>{{$facilityBooking->FB_ContactEmail ?? auth()->user()->UD_InternalEmail}} </td>
                <td>
                    Requestor Name
                </td>
                <td>{{$facilityBooking->FB_RequestorName ?? auth()->user()->UD_DisplayName}}</td>
            </tr>
            <tr>
                <td>Requestor Details</td>
                <td> {{$facilityBooking->FB_RequestorDetail ?? auth()->user()->UD_InternalEmail}} </td>
                <td colspan="2">
                </td>
            </tr>
        </tbody>
    </table>
    @if(isset($facilityBooking) && !isset($copyBookingDate))
        @php($bookingType = $facilityBooking->facilityBookingRecurrence->FBR_BookingType)
    @elseif(isset($createFacilityRecord) && $createFacilityRecord == 1)
        @php($bookingType = App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_RECORD)
    @else
        @php($bookingType = App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_REQUEST)
    @endif
    <table class="facility-booking-form" style="margin-top: 10px">
        <tbody>
            <tr
            @if($bookingType == App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_RECORD)
            style="display: none"
            @endif
            >
                <td style="width: 20%;">Requestor Notes</td>
                <td colspan="3">  {{$facilityBooking->FB_RequestorNote}} </td>
            </tr>
            <tr
            @if($bookingType == App\Models\FacilityBooking\FacilityBookingRecurrence::BOOKING_TYPE_REQUEST)
            style="display: none"
            @endif
            >
                <td style="width: 20%;">Scheduler Notes</td>
                <td colspan="3">{{$facilityBooking->FB_SchedulerNote}} </td>
            </tr>
            <tr>
                <td>Location Notes</td>
                <td colspan="3">
                    {{$facility->FC_LocationNote}}
                </td>
            </tr>
            <tr>
                <td>Facility Notes</td>
                <td colspan="3">
                    {{$facility->FC_FacilityNote}}
                </td>
            </tr>
            <tr>
                <td>Accessibility Notes</td>
                <td colspan="3">
                    {{$facility->FC_AccessibilityNote}}
                </td>
            </tr>
            <tr>
                <td>Notes</td>
                <td colspan="3">
                    {{$facility->FC_PopupNote}}
                </td>
            </tr>
        </tbody>
    </table>
    @php($viewActionAccess = $adminAccess || $facilityBooking->FB_CreatedBy == auth()->user()->UD_UserID  || !in_array($facilityBooking->FB_Private, [App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_YES, App\Models\FacilityBooking\FacilityBookingRecurrence::PRIVATE_BOOKING_SUMMARY]))
    @if($viewActionAccess)
    <table class="facility-booking-form" style="margin-top: 10px">
        <tbody>
            <tr>
                <td style="width: 20%;">Action</td>
                <td id="actions-detail">
                </td>
                <td @if($viewOnly) style="display: none" @endif>Edit Action</td>
                <td id="action-edit-container" @if($viewOnly) style="display: none" @endif><a href="javascript:void(0);" id="edit-action-open-frm"> Edit </a></td>
            </tr>
        </tbody>
    </table>
    @endif
    @if((isset($createFacilityRecord) && $createFacilityRecord) || isset($facilityBooking))
    <input type="hidden" id="facility-booking-current-status-hidden" name="booking_status" value='{{$facilityBooking->FB_BookingStatus ?? ''}}'>
    @endif
    @isset($moveBookingDate)
    <input type="hidden" id="moving_action" name ="moving_action" value="1"> 
    @endisset
    <input type="hidden" id="copy_booking_id" name ="copy_booking_id" value="@if(isset($copyBookingDate)){{($facilityBooking->FB_FacilityBookingID ?? '')}}@endif" />
    <input type="hidden" id="moved_booking" name ="moved_booking" value="@if(isset($facilityBooking) && $facility->FB_FacilityID != $facilityBookingRecurrence->FBR_FacilityID){{1}}@else{{0}}@endif" />
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
    <button style="font-size:12px" id="submit-cancel-facility-booking-form-button" class="ui-button ui-widget ui-corner-all">Close</button>
</div>
@include('pages.facility-booking.form.facility-booking-facility-link')
@include('pages.facility-booking.form.facility-booking-recurring')
@if($viewActionAccess)
@include('pages.facility-booking.form.facility-booking-action')
@endif
@include('pages.facility-booking.form.facility-booking-popup-note')
@include('pages.facility-booking.form.facility-linked-unavailability')