<div class="filter-wrapper diplay-flex">
    <div class="dropdown-wrapper">
      <div class="dropdown-trigger" >
        <button type="button" id="facility-booking-filter-trigger-dropdown" aria-expanded="false" aria-controls="facility-booking-filter-content" class="dropdown-trigger-button" style="width: 100%; background: none; border: 0; padding: 0; display: inline-flex; align-items: center; gap: 6px;">
          <i id="facility-booking-filter-trigger-dropdown-icon" class="fa-solid fa-caret-right" aria-hidden="true"></i>
          <span>Filters</span>
        </button>
        <span class="clearFilterIcon" style="display: none"><i class="fa fa-times-circle" aria-hidden="true"></i></span>
      </div>
      <div class="dropdown-content" id="facility-booking-filter-content" style="display: none;"role="region" aria-label="Facility booking filters" aria-hidden="true">
        <table class="w-100-p">
          <tbody>
            <tr>
              <td>
                <label for="include-cancelled-booking" class="radio-label">Show Cancelled Booking</label>
                <input type="checkbox" class="checkbox-input" id="include-cancelled-booking" value="Include Cancelled Booking">
              </td>
            </tr>
            <tr>
              <td>
                <label class="radio-label">Show only </label>
                <input type="checkbox" class="checkbox-input" name="show_only_booking_status[]" id="show-only-new-booking" value="{{App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_NEW}}">
                <label for="show-only-new-booking" class="radio-label">New</label>
                <input type="checkbox" class="checkbox-input" name="show_only_booking_status[]" id="show-only-pending-booking" value="{{App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_PENDING}}">
                <label for="show-only-pending-booking" class="radio-label">Pending</label>
                <input type="checkbox" class="checkbox-input" name="show_only_booking_status[]" id="show-only-confirmed-booking" value="{{App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_CONFIRMED}}">
                <label for="show-only-confirmed-booking" class="radio-label">Confirmed</label>
                <input type="checkbox" class="checkbox-input" name="show_only_booking_status[]" id="show-only-declined-booking" value="{{App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_DECLINED}}">
                <label for="show-only-declined-booking" class="radio-label">Declined</label>
              </td>
            </tr>
          </tbody>
        </table>
        <hr/>
        <form id="facility-booking-filter-form" autocomplete="off">
        <table class="w-100-p">
          <tbody>
            <tr>
              <td class="content-label"><label for="facility_booking_filter_privacy_type" class="radio-label" style="margin:0;">Filter Type</label></td>
              <td style="width: 280px !important;">
                <select id="facility_booking_filter_privacy_type" name="facility_booking_filter_privacy_type" class="filter-select w-100-p">
                  <option value="">Select Filter</option>
                  @foreach(App\Models\Filter\Filter::filterPrivacyType() as $key => $privacy)
                  <option value="{{$key}}">{{$privacy}}</option>
                  @endforeach
                </select>
              </td>
            </tr>
            <tr>
              <td class="content-label"><label for="facility_booking_filter_saved_filter_list" class="radio-label" style="margin:0;">Preset Filters (Public)</label></td>
              <td style="width: 280px !important;">
                <div class="d-flex filter-btn-wrapper">
                  <button id="facility-booking-filter-go-btn"  type="button" style="font-size: 10px !important;">
                    GO
                  </button>
                  <select id="facility_booking_filter_saved_filter_list" class="filter-select w-100-p">
                    <option value="">Select Filter</option>
                  </select>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <button type="button" class="f-16" id="facility-booking-filter-details-drop-down" aria-expanded="false" aria-controls="facility-booking-filter-details" aria-label="Additional filter options" style="background: none; border: 0; padding: 0;">
                  <i id="facility-booking-filter-details-drop-down-icon" class="fa-solid fa-angle-right" aria-hidden="true"></i>
                </button>
              </td>
              <td></td>
            </tr>
          </tbody>
        </table>
        <table class="w-100-p" id="facility-booking-filter-details" style="display: none;"aria-hidden="true">
          <tbody>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label for="filter_facility_booking_facility_name_data" class="radio-label" style="margin:0;">Facility Name</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="filter_facility_booking_facility_name_condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Facility Name</label>
                  <select class="filter-select w-80 f-10" id="filter_facility_booking_facility_name_condition" name="filter_facility_booking_facility_name_condition">
                    <option value="*">CONTAINS</option>
                    <option value=";">OR</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div class="facility_booking_align">
                    <input style="width: 130px;" type="text" class="filter-text w-100-p" id="filter_facility_booking_facility_name_data" name="filter_facility_booking_facility_name_data" value="" placeholder="Facility Name">
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label for="filter_facility_booking_provider_name_data" class="radio-label" style="margin:0;">Provider Name</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="filter_facility_booking_provider_name_condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Provider Name</label>
                  <select class="filter-select w-80" id="filter_facility_booking_provider_name_condition" name="filter_facility_booking_provider_name_condition">
                    <option value="*">CONTAINS</option>
                    <option value=";">OR</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div class="facility_booking_align">
                    <input style="width: 130px;" type="text" class="filter-text w-100-p" id="filter_facility_booking_provider_name_data" name="filter_facility_booking_provider_name_data" value="" placeholder="Provider Name">
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label id="lbl-filter_facility_booking_area_data" for="filter_facility_booking_area_data" class="radio-label" style="margin:0;">Area</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="filter_facility_booking_area_condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Area</label>
                  <select class="filter-select w-80" name="filter_facility_booking_area_condition" id="filter_facility_booking_area_condition">
                    <option value="*">CONTAINS</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div style="width:139px;">
                    <select class="filter-select w-80" name="filter_facility_booking_area_data[]" data-placeholder="Select Area" id="filter_facility_booking_area_data" multiple>
                      @foreach(App\Models\Scheduling\Division::whereHas('facility')->get() as $area)
                      <option value="{{$area->DivisionID}}">{{$area->DivisionName}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label for="filter_facility_booking_booking_title_data" class="radio-label" style="margin:0;">Booking Title</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="filter_facility_booking_booking_title_condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Booking Title</label>
                  <select class="filter-select w-80" name="filter_facility_booking_booking_title_condition" id="filter_facility_booking_booking_title_condition">
                    <option value="*">CONTAINS</option>
                    <option value=";">OR</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div class="facility_booking_align">
                    <input style="width: 130px;" type="text" class="filter-text w-100-p" name="filter_facility_booking_booking_title_data" id="filter_facility_booking_booking_title_data" value="" placeholder="Booking Title">
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label id="lbl-facility-booking-filter-default-booking-type-filter-data" for="facility-booking-filter-default-booking-type-filter-data" class="radio-label" style="margin:0;">Default Booking Type</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="facility-booking-filter-default-booking-type-filter-condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Default Booking Type</label>
                  <select class="filter-select w-80" name="filter_facility_booking_default_booking_type_condition" id="facility-booking-filter-default-booking-type-filter-condition">
                    <option value="*">CONTAINS</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div style="width:139px;">
                    <select class="filter-select w-80" name="filter_facility_booking_default_booking_type_data[]" data-placeholder="Select Default Booking Type" id="facility-booking-filter-default-booking-type-filter-data" multiple>
                      @foreach(App\Models\Facility\Facility::defaultBookingList() as $key => $defaultBookingType)
                      <option value="{{$key}}">{{$defaultBookingType}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label id="lbl-facility-booking-filter-accessible-type-filter-data" for="facility-booking-filter-accessible-type-filter-data" class="radio-label" style="margin:0;">Accessible Type</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="facility-booking-filter-accessible-type-filter-condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Accessible Type</label>
                  <select class="filter-select w-80" name="filter_facility_booking_accessible_type_condition" id="facility-booking-filter-accessible-type-filter-condition">
                    <option value="*">CONTAINS</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div style="width:139px;">
                    <select class="filter-select w-80" name="filter_facility_booking_accessible_type_data[]" data-placeholder="Select Accessible Type" id="facility-booking-filter-accessible-type-filter-data" multiple>
                      @foreach(App\Models\Facility\Facility::accessibleList() as $key => $defaultBookingType)
                      <option value="{{$key}}">{{$defaultBookingType}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label id="lbl-facility-booking-filter-facility-booking-type-filter-data" for="facility-booking-filter-facility-booking-type-filter-data" class="radio-label" style="margin:0;">Facility Type</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="facility-booking-filter-facility-booking-type-filter-condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Facility Type</label>
                  <select class="filter-select w-80" name="filter_facility_booking_type_condition" id="facility-booking-filter-facility-booking-type-filter-condition">
                    <option value="*">CONTAINS</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div style="width:139px;">
                    <select class="filter-select w-80" name="filter_facility_booking_type_data[]" data-placeholder="Select Facility Type" id="facility-booking-filter-facility-booking-type-filter-data" multiple>
                      <option></option>
                    </select>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label id="lbl-facility-booking-filter-facility-booking-sub-type-filter-data" for="facility-booking-filter-facility-booking-sub-type-filter-data" class="radio-label" style="margin:0;">Facility Sub-Type</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="facility-booking-filter-facility-booking-sub-type-filter-condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Facility Sub-Type</label>
                  <select class="filter-select w-80" name="filter_facility_booking_sub_type_condition" id="facility-booking-filter-facility-booking-sub-type-filter-condition">
                    <option value="*">CONTAINS</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div style="width:139px;">
                    <select class="filter-select w-80" name="filter_facility_booking_sub_type_data[]" data-placeholder="Select Facility Sub Type" id="facility-booking-filter-facility-booking-sub-type-filter-data" multiple>
                      <option></option>
                    </select>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label for="filter_facility_booking_location_data" class="radio-label" style="margin:0;">Location</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="filter_facility_booking_location_condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Location</label>
                  <select class="filter-select w-80" id="filter_facility_booking_location_condition" name="filter_facility_booking_location_condition">
                    <option value="*">CONTAINS</option>
                    <option value=";">OR</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div class="facility_booking_align">
                    <input style="width: 130px;" type="text" class="filter-text w-100-p" id="filter_facility_booking_location_data" name="filter_facility_booking_location_data" value="" placeholder="Location">
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label id="lbl-facility-booking-filter-facility-booking-service-filter-data" for="facility-booking-filter-facility-booking-service-filter-data" class="radio-label" style="margin:0;">Service</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="facility-booking-filter-facility-booking-service-filter-condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Service</label>
                  <select class="filter-select w-80" name="filter_facility_booking_service_condition" id="facility-booking-filter-facility-booking-service-filter-condition">
                    <option value="*">CONTAINS</option>
                    <option value="__AND__">AND</option>
                    <option value=";">OR</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div style="width:139px;">
                    <select class="filter-select w-80" name="filter_facility_booking_service_data[]" data-placeholder="Select Service" id="facility-booking-filter-facility-booking-service-filter-data" multiple>
                      <option></option>
                    </select>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label">
                <div class="d-flex justify-content-between">
                  <label id="lbl-facility-booking-filter-facility-booking-equipment-filter-data" for="facility-booking-filter-facility-booking-equipment-filter-data" class="radio-label" style="margin:0;">Equipment</label>
                  <i class="fa fa-info-circle filter-info-icon" aria-hidden="true"></i>
                </div>
              </td>
              <td style="width: 250px !important;">
                <div class="d-flex justify-content-between gap-10">
                  <label for="facility-booking-filter-facility-booking-equipment-filter-condition" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">Equipment</label>
                  <select class="filter-select w-80" name="filter_facility_booking_equipment_condition" id="facility-booking-filter-facility-booking-equipment-filter-condition">
                    <option value="*">CONTAINS</option>
                    <option value="__AND__">AND</option>
                    <option value=";">OR</option>
                    <option value="!">NOT CONTAINS</option>
                  </select>
                  <div style="width:139px;">
                    <select class="filter-select w-80" name="filter_facility_booking_equipment_data[]" data-placeholder="Select Equipment" id="facility-booking-filter-facility-booking-equipment-filter-data" multiple>
                      <option></option>
                    </select>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="content-label"><label for="facility_booking_filter_new_name" class="radio-label" style="margin:0;">New Filter Name</label></td>
              <td>
                <input type="text" name="facility_booking_filter_new_name" id="facility_booking_filter_new_name" class="filter-text" value="">
              </td>
            </tr>
          </tbody>
        </table>
        <div class="filter-button-wrapper d-flex justify-content-between mt-10" style="display: flex;">
          <div class="radio-input-wrap status-filter-wrap">
            <input type="radio" class="radio-input" id="filter-option-and" value="and" name="filter_global_condition">
            <label for="filter-option-and" class="radio-label f-10">AND</label>
            <input type="radio" class="radio-input ml-20 f-10" id="filter-option-or" value="or" name="filter_global_condition" checked>
            <label for="filter-option-or" class="radio-label">OR</label>
          </div>
          <div class="d-flex filter-btn-wrapper">
            @if(auth()->user()->isFacilityAdministrator)
                <button type="button" id="facility-booking-filter-public-save-btn">Save Public</button>
            @endif
            <button type="button" id="facility-booking-filter-private-save-btn">Save Private</button>
            <button type="button" id="facility-booking-filter-delete-btn">Delete</button>
            <button type="button" id="facility-booking-filter-apply-btn">Apply</button>
            <button type="button" id="facility-booking-filter-cancel-btn">Clear</button>
          </div>
        </div>
      </div>
    </div>
    @csrf
  </form>
</div>