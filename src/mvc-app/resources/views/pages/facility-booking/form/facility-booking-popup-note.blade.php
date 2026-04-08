<div id="facility-booking-popup-note-modal" style="font-size: 12px;padding: 18px;">
    <div id="facility-booking-popup-container">
        <p style="margin: unset"><b>Note :</b></p>
        <p style="margin: unset">{{$facility->FC_PopupNote}}</p>
        @if(isset($facilityBooking) && $facilityBooking->linkedToFacilityBooking == null && !isset($copyBookingDate))
        <form id="facility-booking-update-instances-detail-form">
            <hr>
            <div id="facility-booking-declined-message-container" style="display: none">
                <label style="position: relative;top: -5px;">Decline Reason</label>
                <textarea id="declined_reason" name="declined_reason" rows="1" style="width:85%">{{$facilityBooking->FB_BookingDeclineReason ?? ''}}</textarea>
            </div>
            <div id="facility-booking-popup-modal-instances-container">
                @if(isset($moveBookingDate))
                <span>Move Booking:</span>
                @else
                <span>Would you like this change to be applied to:</span>
                @endif
                <input type="radio" id="save_recurrence_type_only_one" name="save_recurrence_type" value="current">
                <label for="save_recurrence_type_only_one">This instance only</label>
                <input type="radio" id="save_recurrence_type_date_range" name="save_recurrence_type" value="date_range">
                <label for="save_recurrence_type_date_range">A range of instances</label>
                <input type="radio" id="save_recurrence_type_all_series" name="save_recurrence_type" value="all_series">
                <label for="save_recurrence_type_all_series"> All instances in the series</label>
            </div>
            <div style="display: none" id="update-recurrence-date-range" data-recurrence-start-date="{{$facilityBooking->facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d')}}" data-recurrence-end-date="{{$facilityBooking->facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d')}}">
                <br>
                <table class="facility-booking-form">
                    <tr>
                        <td>Start Date</td>
                        <td>
                            <input type="text" class="facility-booking-form-input" id="save_recurrence_type_start_date_range" name="save_recurrence_type_start_date_range" value="">
                        </td>
                        <td>End Date</td>
                        <td>
                            <input type="text" class="facility-booking-form-input" id="save_recurrence_type_end_date_range" name="save_recurrence_type_end_date_range" value="">
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        @endif
        <br>
        <br>
        <div style="text-align: right">
            <button type="button" style="font-size:12px" id="facility-booking-request-save-btn" class="ui-button ui-widget ui-corner-all">Submit</button>
            <button type="button" style="font-size:12px" id="facility-booking-request-cancel-btn" class="ui-button ui-widget ui-corner-all">Cancel</button>
        </div>
    </div>

    <div id="popup-submit-spinner" style="display: none">
        <div id="spinner-container">
            <div class="loader-spinner" style="margin-left: 37%;"></div>
            <span>Processing request</span>
        </div>
    </div>
</div>