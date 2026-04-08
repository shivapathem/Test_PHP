<div id="facility-booking-cancellation">
    <form id="facility-booking-cancellation-form">
        <table class="facility-booking-cancel-form">
            <tbody id="facility-booking-recurring-details-table-body">
                <tr><td>
                    <input type="radio" id="cancel_entire_booking" name="cancel_booking" value="entire_booking">
                    <label for="cancel_entire_booking">Cancel - Entire Booking</label>
                </td></tr>
                <tr><td>
                    <input type="radio" id="cancel_some_booking" name="cancel_booking" value="some_booking">
                    <label for="cancel_some_booking">Cancel - Some Recurrences</label>
                    <tr id="cancel_some_recur">
                    </tr>
                </td></tr>
                <tr><td>
                    <input type="radio" id="cancel_range_booking" name="cancel_booking" value="recurrence_booking">
                    <label for="cancel_range_booking">Cancel - Range of Recurrences</label>
                    <tr id="cancel_range_recur" style="display:none;"> <td>From Date
                    <input type="text" class="facility-booking-form-input" id="cancel_booking_from_date" name="cancel_booking_from_date">
                    </td><td> To Date
                    <input type="text" class="facility-booking-form-input" id="cancel_booking_to_date" name="cancel_booking_to_date">
                    <td></tr>
                </td></tr>
                <tr><td>
                    <input type="radio" id="cancel_nonmandatory_linked_booking" name="cancel_booking" value="nonmandatory_booking">
                    <label for="cancel_nonmandatory_linked_booking">Cancel - Non mandatory Linked Bookings</label>
                </td></tr>
                <tr><td id="error_text" style="font-color:red;">
                </td></tr>
                <tr>
                    <td style="text-align: center">
                        <button type="button" id="cancel-booking">Cancel Bookings</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>