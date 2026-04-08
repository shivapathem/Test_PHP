<div id="booking-type-confirm-modal" style="display:none; font-size: 12px">
    <p>There are existing Future Bookings on this Facility,<br>you will have to save them as -</p>

    <label>
        <input type="radio" name="booking_type_confirm" style="font-size: 12px;" value="Confirmed">
        Confirmed
    </label><br>

    <label>
        <input type="radio" style="font-size: 12px;" name="booking_type_confirm" value="Declined">
        Declined
    </label>

    <div id="booking-type-error-model" style="display:none; color:#d9534f; margin-top:10px; font-size: 12px;">
       Please select one option before proceeding.
    </div>

    <div style="text-align:right; margin-top:15px;">
        <button type="button" id="booking-type-confirm-btn"
                class="ui-button ui-widget ui-corner-all">
            Done
        </button>
    </div>
</div>