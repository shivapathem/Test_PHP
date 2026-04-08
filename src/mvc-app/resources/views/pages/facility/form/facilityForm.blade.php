<form id="create-facility-form" autocomplete="off">
    <link rel="stylesheet" href="/mvc-app/public/{{mix('css/facilityForm/facilityForm.css')}}" />
    <div class="ff-grid facility-form">
        <div class="ff-body">
            <div class="ff-row">
                <div class="ff-cell" style="width: 18%;">Facility<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <input type="text" class="facility-form-input" id="facility_frm" name="facility_frm"
                        value="@isset($editFacility) {{$editFacility->FC_FacilityName}} @endisset"
                        @if($viewOnly) disabled @endif
                        required aria-required="true">
                </div>
                <div class="ff-cell" style="width: 18%;">Active From<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    @php($activeFromDate = isset($editFacility) ? $editFacility->FC_ActiveFrom : now())
                    <input type="text" class="facility-form-input" id="active_from_frm" name="active_from_frm"
                        data-date-formated="{{$activeFromDate->format('Y-m-d')}}"
                        value="{{$activeFromDate->format('d/m/Y')}}" @if($viewOnly) disabled @endif onkeydown="javascript: return event.keyCode == 9 ? true : false">
                </div>
            </div>

            <div class="ff-row">
                <div class="ff-cell">Area Owner<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    @if($areaList->count() <= 1 || (isset($editFacility) && $editFacility->facilityBookings()->count() > 0))
                        @php($selectedArea = isset($editFacility) ? $editFacility->facilityAreaOwner : $areaList->first())
                        {{$selectedArea->DivisionName}}
                        <input type="hidden" name="area_owner_frm" id="area_owner_frm" value="{{$selectedArea->DivisionID}}">
                        @else
                        <select class="facility-form-input" data-placeholder="Select Area" id="area_owner_frm" name="area_owner_frm" @if($viewOnly) disabled @endif>
                            <option></option>
                            @php($exitingAreaId = isset($editFacility) ? $editFacility->FC_AreaOwnerID : '')
                            @foreach ($areaList as $area)
                            <option value="{{$area->DivisionID}}" @if($exitingAreaId==$area->DivisionID) selected @endif>{{$area->DivisionName}}</option>
                            @endforeach
                        </select>
                        @endif
                </div>
                <div class="ff-cell">Restrict Bookers</div>
                <div class="ff-cell">
                    <div id="restrict-bookers-text-container">
                        All users are Requestors
                    </div>
                    <div id="restrict-bookers-select-container">
                        <select class="facility-form-input" data-placeholder="All teams in this area are Bookers" multiple="true" id="restrict_bookers_frm" name="restrict_bookers_frm[]" @if($viewOnly) disabled @endif>
                            @isset($editFacility)
                            @php($restrictedBookers = $editFacility->facilityRestrictBookers->pluck('schedulingTeamId')->toArray())
                            @foreach($editFacility->facilityAreaOwner->schedulingTeam as $schedulingTeam)
                            <option value="{{$schedulingTeam->schedulingTeamId}}"
                                @if(in_array($schedulingTeam->schedulingTeamId, $restrictedBookers)) selected @endif
                                >{{$schedulingTeam->schedulingTeamName}}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>
                </div>
            </div>

            <div class="ff-row">
                <div class="ff-cell">Provider Type<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <select @if($viewOnly || $bookingsExist) disabled @endif class="facility-form-input" data-placeholder="Select Provider Type" id="provider_type_frm" name="provider_type_frm" required aria-required="true">
                        <option></option>
                        @foreach ($providerTypeList as $key => $providerType)
                        <option value="{{$key}}"
                            @if(isset($editFacility) && $editFacility->FC_ProviderType == $key) selected @endif
                            >{{$providerType}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ff-cell">Provider Name<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <input @if($viewOnly || $bookingsExist) disabled @endif class="facility-form-input" type="text" id="provider_name_frm" name="provider_name_frm" value="@isset($editFacility){{$editFacility->FC_ProviderName}}@endisset" required aria-required="true"
                        @if(isset($editFacility) && $editFacility->FC_ProviderType == 'internal' ) disabled @endif>
                </div>
            </div>
            <div class="ff-row" id="location-internal" @if(isset($editFacility) && $editFacility->FC_ProviderType == 'internal' ) style="" @else style="display: none" @endif>
                <div class="ff-cell">Location<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <select @if($viewOnly || $bookingsExist) disabled @endif class="facility-form-input" data-placeholder="Select Location" id="internal_location_frm" name="internal_location_frm">
                        <option></option>
                        @php($existingLocationId = isset($editFacility) && $editFacility->FC_ProviderType == 'internal' ? $editFacility->internalLocation->LN_LocationID : '')
                        @foreach ($internalLocationList as $internalLocation)
                        <option value="{{$internalLocation->LN_LocationID}}" @if($existingLocationId==$internalLocation->LN_LocationID) selected @endif>{{$internalLocation->LN_Location}}</option>
                        @endforeach
                    </select>
                </div>
                @if(isset($editFacility))
                <div class="ff-cell">Facility ID</div>
                <div class="ff-cell">{{ $editFacility->FC_FacilityID }}</div>
                @else
                <div class="ff-cell ff-cell--span-2" style="grid-column: span 2"></div>
                @endif
            </div>
            <div class="ff-row" id="location-external-uk" @if(isset($editFacility) && $editFacility->FC_ProviderType == 'external_uk' ) style="" @else style="display: none" @endif>
                <div class="ff-cell">Location<span class="required-asterisk">*</span></div>
                <div class="ff-cell ff-cell--span-3" style="grid-column: span 3">
                    <a href="javascript:void(0);" id="location_external_uk_open_frm" @if($viewOnly || $bookingsExist) style="display: none" @endif> Update</a>
                </div>
            </div>
            <div class="ff-row" id="location-external-international" @if(isset($editFacility) && $editFacility->FC_ProviderType == 'external_international' ) style="" @else style="display: none" @endif>
                <div class="ff-cell">Location<span class="required-asterisk">*</span></div>
                <div class="ff-cell ff-cell--span-3" style="grid-column: span 3">
                    <a href="javascript:void(0);" id="location_external_international_open_frm" @if($viewOnly || $bookingsExist) style="display: none" @endif> Update</a>
                </div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Location Notes</div>
                <div class="ff-cell ff-cell--span-3" style="grid-column: span 3">
                    <textarea rows="1" class="facility-form-input" id="location_notes_frm" name="location_notes_frm" style="width:98%">@isset($editFacility){{$editFacility->FC_LocationNote}}@endisset</textarea>
                </div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Facility Availability</div>
                <div class="ff-cell">
                    <a href="javascript:void(0);" id="facility_availability_open_frm" @if($viewOnly) style="display: none" @endif> Update</a>
                </div>
                <div class="ff-cell">Facility Type<span class="required-asterisk">*</span></div>
                <div class="ff-cell" id="facility-type-detail-container">
                    <a href="javascript:void(0);" id="facility_type_open_frm" @if($viewOnly) style="display: none" @endif> Update</a>
                    <input type="hidden" id="facility_type_completed_frm" name="facility_type_completed_frm" value="" required aria-required="true">
                </div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Primary Facility<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <input @if($viewOnly) disabled @endif type="radio" id="primary_facility_yes_frm" name="primary_facility_frm" value="yes" @if(isset($editFacility) && $editFacility->primary_facility == 1) checked @endif>
                    <label for="primary_facility_yes_frm">Yes</label>
                    <input @if($viewOnly) disabled @endif type="radio" id="primary_facility_no_frm" name="primary_facility_frm" value="no" @if(!isset($editFacility) || $editFacility->primary_facility == 0) checked @endif>
                    <label for="primary_facility_no_frm">No</label>
                </div>
                <div class="ff-cell">Technical Setup<span class="required-asterisk">*</span></div>
                <div class="ff-cell" id="technical-setup-detail-container">
                    <a href="javascript:void(0);" id="technical_setup_open_frm" @if($viewOnly) style="display: none" @endif> Update</a>
                    <input type="hidden" id="technical_setup_completed_frm" name="technical_setup_completed_frm" value="" required aria-required="true">
                </div>
            </div>
            <div class="ff-row" id="linked-facility-row" @if(isset($editFacility) && $editFacility->primary_facility == 1) style="" @else style="display: none" @endif>
                <div class="ff-cell">Linked Facility</div>
                <div class="ff-cell">
                    <a href="javascript:void(0);" id="linked_facility_open_frm" @if($viewOnly) style="display: none" @endif> Update</a>
                </div>
                <div class="ff-cell ff-cell--span-2" style="grid-column: span 2"></div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Facility Capacity<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <input @if($viewOnly) disabled @endif min=0 max="9999" type="number" class="facility-form-input" id="facility_capacity_frm" name="facility_capacity_frm" value="@isset($editFacility){{$editFacility->FC_FacilityCapacity}}@endisset" required aria-required="true"
                        @include('includes.input-number-regex')>
                </div>
                <div class="ff-cell">Accessible?<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <select @if($viewOnly) disabled @endif class="facility-form-input" data-placeholder="Select Accessible" id="facility_accessible_frm" name="facility_accessible_frm" required aria-required="true">
                        <option></option>
                        @foreach($accessibleList as $key => $accessibleData)
                        <option value="{{$key}}"
                            @if(isset($editFacility) && $editFacility->FC_Accessible == $key) selected @endif
                            >{{$accessibleData}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Accessibility Notes</div>
                <div class="ff-cell ff-cell--span-3" style="grid-column: span 3">
                    <textarea @if($viewOnly) disabled @endif rows="1" class="facility-form-input" id="accessibility_notes_frm" name="accessibility_notes_frm" style="width:98%">@isset($editFacility){{$editFacility->FC_AccessibilityNote}}@endisset</textarea>
                </div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Default Booking Type<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <select @if($viewOnly) disabled @endif class="facility-form-input" data-placeholder="Select Default Booking Type" id="facility_default_booking_frm" name="facility_default_booking_frm" required aria-required="true">
                        <option></option>
                        @foreach($defaultBookingList as $key => $defaultBooking)
                        <option value="{{$key}}"
                            @if(isset($editFacility) && $editFacility->FC_DefaultBookingType == $key) selected @endif
                            >{{$defaultBooking}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ff-cell">Make All Bookings Private<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <select @if($viewOnly) disabled @endif class="facility-form-input" data-placeholder="Select Make All Bookings Private" id="facility_make_bookings_private_frm" name="facility_make_bookings_private_frm">
                        @foreach($bookingPrivateList as $key => $bookingPrivate)
                        <option value="{{$key}}"
                            @if(isset($editFacility) && $editFacility->FC_MakeAllBookingsPrivate == $key) selected @endif
                            >{{$bookingPrivate}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="ff-row">
                {{-- @isset($editFacility)
                <div class="ff-cell">Change Booking Type</div>
                <div class="ff-cell">
                    <a href="javascript:void(0);" id="change_booking_type_open_frm" @if($viewOnly) style="display:none" @endif>Update</a>
                </div>
                @endisset
                --}}
                <!-- The label for this field has been changed. Previously, it was displayed as "Mark as Unavailable". -->
                <div class="ff-cell">Unavailable Period</div>
                <div class="ff-cell">
                    <a href="javascript:void(0);" id="mark_as_unavailable_open_frm" @if($viewOnly) style="display:none" @endif>Update</a>
                </div>

                <div class="ff-cell ff-cell--span-2" style="grid-column: span 2">
                </div>

            </div>

            <div class="ff-row">
                <div class="ff-cell">Allow Booking Request<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <input @if($viewOnly) disabled @endif type="radio" id="allow_booking_yes_frm" name="allow_booking_frm" value="yes" @if(isset($editFacility) && $editFacility->FC_AllowBookingRequest == 1) checked @endif>
                    <label for="allow_booking_yes_frm">Yes</label>
                    <input @if($viewOnly) disabled @endif type="radio" id="allow_booking_no_frm" name="allow_booking_frm" value="no" @if(isset($editFacility) && $editFacility->FC_AllowBookingRequest == 0) checked @endif>
                    <label id="allow_booking_no_frm_label" for="allow_booking_no_frm">No</label>
                </div>
                <div class="ff-cell">One-Off Bookings Only<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <input @if($viewOnly) disabled @endif type="radio" id="on_off_booking_yes_frm" name="on_off_booking_frm" value="yes" @if(isset($editFacility) && $editFacility->FC_OneOffBookingsOnly == 1) checked @endif>
                    <label for="on_off_booking_yes_frm">Yes</label>
                    <input @if($viewOnly) disabled @endif type="radio" id="on_off_booking_no_frm" name="on_off_booking_frm" value="no" @if(isset($editFacility) && $editFacility->FC_OneOffBookingsOnly == 0) checked @endif>
                    <label id="on_off_booking_no_frm_label" for="on_off_booking_no_frm">No</label>
                </div>

            </div>
            <div class="ff-row">
                <div class="ff-cell">Allow Self Booking From</div>
                <div class="ff-cell">
                    @if($viewOnly)
                    {{$editFacility->FC_AllowSelfBookingFrom}}
                    @else
                    <input type="number" class="facility-form-input" min="0" max="999" id="allow_self_booking_from_frm" name="allow_self_booking_from_frm" value="@isset($editFacility){{$editFacility->FC_AllowSelfBookingFrom}}@else{{0}}@endisset"
                        @include('includes.input-number-regex')>
                    @endif
                </div>
                <div class="ff-cell">Allow Self Booking To</div>
                <div class="ff-cell">
                    @if($viewOnly)
                    {{$editFacility->FC_AllowSelfBookingTo}}
                    @else
                    <input type="number" class="facility-form-input" min="0" max="999" id="allow_self_booking_to_frm" name="allow_self_booking_to_frm" value="@isset($editFacility){{$editFacility->FC_AllowSelfBookingTo}}@else{{365}}@endisset"
                        @include('includes.input-number-regex')>
                    @endif
                </div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Please Note</div>
                <div class="ff-cell ff-cell--span-3" style="grid-column: span 3">
                    <textarea @if($viewOnly) disabled @endif rows="1" class="facility-form-input" id="pop_up_notes_frm" name="pop_up_notes_frm" style="width:98%">@isset($editFacility){{$editFacility->FC_PopupNote}}@endisset</textarea>
                </div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Facility Notes</div>
                <div class="ff-cell ff-cell--span-3" style="grid-column: span 3">
                    <textarea @if($viewOnly) disabled @endif rows="1" class="facility-form-input" id="facility_notes_frm" name="facility_notes_frm" style="width:98%">@isset($editFacility){{$editFacility->FC_FacilityNote}}@endisset</textarea>
                </div>
            </div>
            <div class="ff-row">
                <div class="ff-cell">Facility Contact Email<span class="required-asterisk">*</span></div>
                <div class="ff-cell">
                    <input @if($viewOnly) disabled @endif class="facility-form-input" type="text" id="facility_contact" name="facility_contact" placeholder="email1@example.com, email2@example.com" value="@isset($editFacility){{$editFacility->FC_Contact}}@endisset" required aria-required="true">
                </div>
                <div class="ff-cell ff-cell--span-2" style="grid-column: span 2"></div>
            </div>
        </div>
    </div>
    @csrf
</form>
<br>
<input type="hidden" id="facility-form-view-only" value="{{$viewOnly}}">
<input type="hidden" id="booking_status_declined_or_confirm" name="booking_status_declined_or_confirmed" value="">
@if($viewOnly)
<button style="font-size:12px;float:right;" class="ui-button ui-widget ui-corner-all" onclick="$(this).closest('.ui-dialog-content').dialog('close')">Close</button>
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
@include('pages.facility.form.facility-linked-facility-validation-modal')
@endisset