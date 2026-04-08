<form id="create-facility-form" autocomplete="off">
    <table class="facility-form" >
        <tbody>
            <tr>
                <td style="width: 18%;">Facility</td>
                <td>
                   {{$editFacility->FC_FacilityName}}
                    
                </td>
                <td style="width: 18%;">Active From</td>
                <td>
                     @php($activeFromDate = isset($editFacility) ? $editFacility->FC_ActiveFrom : now())
                    {{$activeFromDate->format('d/m/Y')}}
                </td>
            </tr>

            <tr>
                <td>Area Owner</td>
                <td>
                    @php($exitingAreaId = isset($editFacility) ? $editFacility->FC_AreaOwnerID : '')
                    {{$areaList->pluck('DivisionName','DivisionID')->get($exitingAreaId)}}
                </td>
                <td>Restrict Bookers</td>
                <td>
                    @if($editFacility->FC_DefaultBookingType == App\Models\Facility\Facility::DEFAULT_BOOKING_SELF_BOOKED)
                        All users are Requestors
                    @else
                    {{
                    $editFacility->facilityRestrictBookers->count() == 0 
                    ?
                    'All teams in this area are Bookers'
                    :
                    $editFacility->facilityRestrictBookers->pluck('schedulingTeamName')->implode(', ')
                    }}
                    @endif
                </td>
            </tr>

            <tr>
                <td>Provider Type</td>
                <td> {{$providerTypeList[$editFacility->FC_ProviderType]}}</td>
                <td>Provider Name</td>
                <td>{{$editFacility->FC_ProviderName}}</td>
            </tr>
            <tr id="location-internal" @if(isset($editFacility) && $editFacility->FC_ProviderType == 'internal' ) style="" @else style="display: none"  @endif>
                <td>Location</td>
                <td>
                    @php($existingLocationId = isset($editFacility) && $editFacility->FC_ProviderType == 'internal' ? $editFacility->internalLocation->LN_LocationID : '')
                    {{$internalLocationList->pluck('LN_Location','LN_LocationID')->get($existingLocationId)}}
                </td>
                    <td>Facility ID</td>
                    <td>{{ $editFacility->FC_FacilityID }}</td>
            </tr>
            <tr id="location-external-uk" @if(isset($editFacility) && $editFacility->FC_ProviderType == 'external_uk' ) style="" @else style="display: none"  @endif>
                <td>Location</td>
                <td colspan="3">
                    <a href="javascript:void(0);" id="location_external_uk_open_frm" @if($viewOnly || $bookingsExist) style="display: none" @endif> Update</a>
                </td>
            </tr>
            <tr id="location-external-international" @if(isset($editFacility) && $editFacility->FC_ProviderType == 'external_international' ) style="" @else style="display: none"  @endif>
                <td>Location</td>
                <td colspan="3">
                    <a href="javascript:void(0);" id="location_external_international_open_frm" @if($viewOnly || $bookingsExist) style="display: none" @endif> Update</a>
                </td>
            </tr>
            <tr>
                <td>Location Notes</td>
                <td colspan="3">{{$editFacility->FC_LocationNote}}</td>
            </tr>
            <tr>
                <td>Facility Availability</td>
                <td>
                     <a href="javascript:void(0);" id="facility_availability_open_frm" @if($viewOnly) style="display: none" @endif> Update</a>
                </td>
                <td>Facility Type</td>
                <td id="facility-type-detail-container">
                     <a href="javascript:void(0);" id="facility_type_open_frm" @if($viewOnly) style="display: none" @endif> Update</a>
                </td>
            </tr>
            <tr>
                <td>Primary Facility</td>
                <td>{{ $editFacility->primary_facility ? 'Yes' : 'No' }} </td>
                <td>Technical Setup</td>
                <td id="technical-setup-detail-container">
                     <a href="javascript:void(0);" id="technical_setup_open_frm" @if($viewOnly) style="display: none" @endif> Update</a>
                </td>
            </tr>
            <tr id="linked-facility-row" @if(isset($editFacility) && $editFacility->primary_facility == 1) style="" @else style="display: none" @endif>
                <td>Linked Facility</td>
                <td>
                     <a href="javascript:void(0);" id="linked_facility_open_frm" @if($viewOnly) style="display: none" @endif> Update</a>
                </td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td>Facility Capacity</td>
                <td> {{$editFacility->FC_FacilityCapacity}} </td>
                <td>Accessible?</td>
                <td> {{$accessibleList[$editFacility->FC_Accessible]}}</td>
            </tr>
            <tr>
                <td>Accessibility Notes</td>
                <td colspan="3">{{$editFacility->FC_AccessibilityNote}}</td>
            </tr>
            <tr>
                <td>Default Booking Type</td>
                <td> {{$defaultBookingList[$editFacility->FC_DefaultBookingType]}} </td>
                <td>Make All Bookings Private</td>
                <td> {{$bookingPrivateList[$editFacility->FC_MakeAllBookingsPrivate]}}</td>
            </tr>
            <tr>
                @isset($editFacility)
                <td>Change Booking Type</td>
                <td>
                    <a href="javascript:void(0);" id="change_booking_type_open_frm" @if($viewOnly) style="display:none" @endif>Update</a>
                </td>
                @endisset
                <td>Mark as Unavailable</td>
                <td>
                    <a href="javascript:void(0);" id="mark_as_unavailable_open_frm" @if($viewOnly) style="display:none" @endif>Update</a>
                </td>
                @if(!isset($editFacility))
                <td colspan="2">
                </td>
                @endif
            </tr>

            <tr>
                <td>Allow Booking Request</td>
                <td>{{$editFacility->FC_AllowBookingRequest ? 'Yes':'No'}}</td>
                <td>One-Off Bookings Only</td>
                <td> {{$editFacility->FC_OneOffBookingsOnly ? 'Yes':'No'}}</td>

            </tr>
            <tr @if($editFacility->FC_DefaultBookingType === 'managed') style="display: none" @endif>
                <td>Allow Self Booking From</td>
                <td>{{$editFacility->FC_AllowSelfBookingFrom}}</td>
                <td>Allow Self Booking To</td>
                <td>{{$editFacility->FC_AllowSelfBookingTo}}</td>
            </tr>
            <tr>
                <td>Please Note</td>
                <td colspan="3"> {{$editFacility->FC_PopupNote}}</td>
            </tr>
            <tr>
                <td>Facility Notes</td>
                <td colspan="3">{{$editFacility->FC_FacilityNote}}</td>
            </tr>
            <tr>
                <td>Facility Contact</td>
                <td>{!! str_replace(',', ',<br>', $editFacility->FC_Contact) !!} </td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
    @csrf
</form>
<br>
<input type="hidden" id="facility-form-view-only" value="{{$viewOnly}}">
<input type="hidden" id="booking_status_declined_or_confirm" name="booking_status_declined_or_confirmed" value="">
@if($viewOnly) 
<button style="font-size:12px;float:right;"  class="ui-button ui-widget ui-corner-all" onclick="$(this).closest('.ui-dialog-content').dialog('close')">Close</button>
 @endif
@if(!$viewOnly)
<div style="text-align: right">
    @if(isset($editFacility))
    <button style="font-size:12px; background-color: #d0d0d0;" id="submit-update-facility-form-button" class="ui-button ui-widget ui-corner-all" data-facility-id="{{$editFacility->FC_FacilityID}}">Update Facility</button>
    @else
    <button style="font-size:12px; background-color: #d0d0d0;" id="submit-create-facility-form-button" class="ui-button ui-widget ui-corner-all">Create Facility</button>
    @endif
</div>
@endif
@include('pages.facility.form.locationExternalUK')
@include('pages.facility.form.locationExternalInternational')
@include('pages.facility.form.facilityLink')
@include('pages.facility.form.facilityAvailability')
@include('pages.facility.form.facilityType')
@include('pages.facility.form.facilityTechnicalSetup')
@include('pages.facility.form.facilityMarkAsUnavailable')
@isset($editFacility)
@include('pages.facility.form.facilityChangeBookingType')
@include('pages.facility.form.booking-type-confirm-modal')
@include('pages.facility.form.privacy-update-notify-model')
@endisset