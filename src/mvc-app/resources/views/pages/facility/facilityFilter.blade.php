<div class="filter-wrapper diplay-flex">
    <form id="facility-filter-form">
        <div class="dropdown-wrapper">
            <div class="dropdown-trigger">
                <div id="facility-filter-trigger-dropdown" tabindex="0" role="button" aria-label="Filters" aria-expanded="false" aria-controls="facility-filters-panel" style="width: 100%">
                    <i id="facility-filter-trigger-dropdown-icon" class="fa-solid fa-caret-right"></i>
                    <span>Filters</span>
                </div>
                <span class="clearFilterIcon" style="display: none" tabindex="0"><i class="fa fa-times-circle" role="button" aria-label="clear filter" title="clear filter"></i></span>
            </div>
            <div id="facility-filters-panel" hidden role="region" aria-label="Facility catalogue filters" aria-hidden="true">
                <div class="dropdown-content" id="facility-filter-content" style="display: none;"role="region" aria-label="Facility catalogue filters" aria-hidden="true">
                    <div class="filter-container">
                        <div class="filter-row">
                            <label class="content-label" for="facility_filter_privacy_type" id="lbl-facility_filter_privacy_type">Filter Type</label>
                            <div style="width: 80%; padding: 2px 2px;">
                                <select id="facility_filter_privacy_type" name="facility_filter_privacy_type" class="filter-select w-100-p" aria-label="Filter Type">
                                    <option value="">Select Filter</option>
                                    @foreach(App\Models\Filter\Filter::filterPrivacyType() as $key => $privacy)
                                    <option value="{{$key}}">{{$privacy}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="filter-row">
                            <label class="content-label" for="facility_filter_saved_filter_list" id="lbl-facility_filter_saved_filter_list">Preset Filters (Public)</label>
                            <div style="width: 80%; padding: 2px 2px;">
                                <div class="d-flex filter-btn-wrapper">
                                    <button id="facility-filter-go-btn" type="button" style="font-size: 10px !important;">
                                        GO
                                    </button>
                                    <select id="facility_filter_saved_filter_list" class="filter-select w-100-p" aria-label="Preset Filters (Public)">
                                        <option value="">Select Filter</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label">
                                <button type="button" class="f-16" id="facility-filter-details-drop-down" aria-expanded="false" aria-controls="facility-filter-details" aria-label="Additional filter options" style="background: none; border: 0; padding: 0;">
                                    <i id="facility-filter-details-drop-down-icon"
                                        class="fa-solid fa-angle-right"
                                        aria-hidden="true"></i>
                                </button>
                            </div>
                            <div style="width: 80%; padding: 2px 2px;height: 12px;"></div>
                        </div>
                    </div>

                    <div id="facility-filter-details" class="w-100-p" style="display: none;" aria-hidden="true">
                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="facility-filter-facility-area-filter-data" style="margin:0;" id="lbl-facility-filter-facility-area-filter-data">Facility Area</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" name="filter_facility_area_condition" id="facility-filter-facility-area-filter-condition" aria-label="Facility Area">
                                        <option value="*">CONTAINS</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_area_data[]" data-placeholder="Select Facility Area" id="facility-filter-facility-area-filter-data" multiple aria-label="Facility Area">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="filter_facility_name_data" style="margin:0;" id="lbl-filter_facility_name_data">Facility Name</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80 f-10" id="filter_facility_name_condition" name="filter_facility_name_condition" aria-label="Facility Name">
                                        <option value="*">CONTAINS</option>
                                        <option value=";">OR</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <input type="text" class="filter-text" id="filter_facility_name_data" name="filter_facility_name_data" value="" placeholder="Facility Name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="facility-filter-provider-type-filter-data" style="margin:0;" id="lbl-facility-filter-provider-type-filter-data">Provider Type</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" name="filter_facility_provider_type_condition" id="facility-filter-provider-type-filter-condition" aria-label="Provider Type">
                                        <option value="*">CONTAINS</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_provider_type_data[]" data-placeholder="Select Provider Type" id="facility-filter-provider-type-filter-data" multiple aria-label="Provider Type">
                                            @foreach(App\Models\Facility\FacilityLocation::providerTypeList() as $key => $providerType)
                                            <option value="{{$providerType}}">{{$providerType}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="filter_facility_provider_name_data" style="margin:0;" id="lbl-filter_facility_provider_name_data">Provider Name</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" id="filter_facility_provider_name_condition" name="filter_facility_provider_name_condition" aria-label="Provider Name">
                                        <option value="*">CONTAINS</option>
                                        <option value=";">OR</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <input type="text" class="filter-text" id="filter_facility_provider_name_data" name="filter_facility_provider_name_data" value="" placeholder="Provider Name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="filter_facility_status_data" style="margin:0;" id="lbl-filter_facility_status_data">Status</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" id="filter_facility_status_condition" name="filter_facility_status_condition" aria-label="Status">
                                        <option value="*">CONTAINS</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_status_data[]" data-placeholder="Select Status" id="filter_facility_status_data" multiple aria-label="Status">
                                            @foreach(App\Models\Facility\Facility::getStatus() as $key => $getStatus)
                                            <option value="{{$getStatus}}">{{$getStatus}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="facility-filter-default-booking-type-filter-data" style="margin:0;" id="lbl-facility-filter-default-booking-type-filter-data">Default Booking Type</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" name="filter_facility_default_booking_type_condition" id="facility-filter-default-booking-type-filter-condition" aria-label="Default Booking Type">
                                        <option value="*">CONTAINS</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_default_booking_type_data[]" data-placeholder="Select Default Booking Type" id="facility-filter-default-booking-type-filter-data" multiple aria-label="Default Booking Type">
                                            @foreach(App\Models\Facility\Facility::defaultBookingList() as $key => $defaultBookingType)
                                            <option value="{{$defaultBookingType}}">{{$defaultBookingType}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="facility-filter-facility-type-filter-data" style="margin:0;" id="lbl-facility-filter-facility-type-filter-data">Facility Type</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" name="filter_facility_type_condition" id="facility-filter-facility-type-filter-condition" aria-label="Facility Type">
                                        <option value="*">CONTAINS</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_type_data[]" data-placeholder="Select Facility Type" id="facility-filter-facility-type-filter-data" multiple aria-label="Facility Type">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="facility-filter-facility-sub-type-filter-data" style="margin:0;" id="lbl-facility-filter-facility-sub-type-filter-data">Facility Sub-Type</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" name="filter_facility_sub_type_condition" id="facility-filter-facility-sub-type-filter-condition" aria-label="Facility Sub-Type">
                                        <option value="*">CONTAINS</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_sub_type_data[]" data-placeholder="Select Facility Sub Type" id="facility-filter-facility-sub-type-filter-data" multiple aria-label="Facility Sub-Type">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="filter_facility_location_data" style="margin:0;" id="lbl-filter_facility_location_data">Location</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" id="filter_facility_location_condition" name="filter_facility_location_condition" aria-label="Location">
                                        <option value="*">CONTAINS</option>
                                        <option value=";">OR</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <input type="text" class="filter-text" id="filter_facility_location_data" name="filter_facility_location_data" value="" placeholder="Location">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2" style="width: 45%;">
                                <div class="d-flex justify-content-between">
                                    <label for="filter_facility_capacity_data" style="margin:0;" id="lbl-filter_facility_capacity_data">Facility Capacity</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" style="width: 41%;" id="filter_facility_capacity_condition" name="filter_facility_capacity_condition" aria-label="Facility Capacity">
                                        <option value=">">GREATER THAN</option>
                                        <option value="<">LESS THAN</option>
                                        <option value=">=">GREATER THAN OR EQUAL TO</option>
                                        <option value="<=">LESS THAN OR EQUAL TO</option>
                                        <option value="=">EQUAL TO</option>
                                        <option value="!=">NOT EQUAL TO</option>
                                    </select>
                                    <div style="width:139px;">
                                        <input type="number" min=1 class="filter-text" id="filter_facility_capacity_data" name="filter_facility_capacity_data" value="" placeholder="Facility Capacity">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="filter_facility_accessible_data" style="margin:0;" id="lbl-filter_facility_accessible_data">Accessible</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" id="filter_facility_accessible_condition" name="filter_facility_accessible_condition" aria-label="Accessible">
                                        <option value="*">CONTAINS</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_accessible_data[]" data-placeholder="Select Status" id="filter_facility_accessible_data" multiple aria-label="Accessible">
                                            @foreach(App\Models\Facility\Facility::accessibleList() as $key => $accessible)
                                            <option value="{{$accessible}}">{{$accessible}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="facility-filter-facility-service-filter-data" style="margin:0;" id="lbl-facility-filter-facility-service-filter-data">Service</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" name="filter_facility_service_condition" id="facility-filter-facility-service-filter-condition" aria-label="Service">
                                        <option value="*">CONTAINS</option>
                                        <option value="__AND__">AND</option>
                                        <option value=";">OR</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_service_data[]" data-placeholder="Select Service" id="facility-filter-facility-service-filter-data" multiple aria-label="Service">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <div class="content-label-t2">
                                <div class="d-flex justify-content-between">
                                    <label for="facility-filter-facility-equipment-filter-data" style="margin:0;" id="lbl-facility-filter-facility-equipment-filter-data">Equipment</label>
                                    <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="filter-content">
                                <div class="d-flex justify-content-between gap-10">
                                    
                                    <select class="filter-select w-80" name="filter_facility_equipment_condition" id="facility-filter-facility-equipment-filter-condition" aria-label="Equipment">
                                        <option value="*">CONTAINS</option>
                                        <option value="__AND__">AND</option>
                                        <option value=";">OR</option>
                                        <option value="!">NOT CONTAINS</option>
                                    </select>
                                    <div style="width:139px;">
                                        <select class="filter-select w-80" name="filter_facility_equipment_data[]" data-placeholder="Select Equipment" id="facility-filter-facility-equipment-filter-data" multiple aria-label="Equipment">
                                            <option></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-row">
                            <label class="content-label-t2" style="width: 34%;" for="facility_filter_new_name" id="lbl-facility_filter_new_name">New Filter Name</label>
                            <div class="filter-content" style="width: 65%;">
                                <input type="text" name="facility_filter_new_name" id="facility_filter_new_name" class="filter-text" value="">
                            </div>
                        </div>
                    </div>

                    <div class="filter-button-wrapper d-flex justify-content-between mt-10" style="display: flex;">
                        <div class="radio-input-wrap status-filter-wrap">
                            <input type="radio" class="radio-input" id="filter-option-and" value="and" name="filter_global_condition">
                            <label for="filter-option-and" class="radio-label f-10">AND</label>
                            <input type="radio" class="radio-input ml-20 f-10" id="filter-option-or" value="or" name="filter_global_condition" checked>
                            <label for="filter-option-or" class="radio-label">OR</label>
                        </div>
                        <div class="d-flex filter-btn-wrapper">
                            @if(auth()->user()->isFacilityAdministrator)
                            <button type="button" id="facility-filter-public-save-btn">Save Public</button>
                            @endif
                            <button type="button" id="facility-filter-private-save-btn">Save Private</button>
                            <button type="button" id="facility-filter-delete-btn">Delete</button>
                            <button type="button" id="facility-filter-apply-btn">Apply</button>
                            <button type="button" id="facility-filter-cancel-btn">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @csrf
    </form>
    <div class="diplay-flex">
        <input type="checkbox" class="checkbox-input" id="include-archived-facilities" value="Include archived facilities">
        <label style="padding-top: 6px;" for="include-archived-facilities" class="radio-label">Include Archived Facilities</label>
        <div style="padding-top: 6px;" class="separator"> &nbsp; | &nbsp;</div>
        <input type="checkbox" class="checkbox-input" id="show-all-facilities" value="Show All Facilities">
        <label style="padding-top: 6px;" for="show-all-facilities" class="radio-label">Show All Facilities</label>
    </div>
</div>