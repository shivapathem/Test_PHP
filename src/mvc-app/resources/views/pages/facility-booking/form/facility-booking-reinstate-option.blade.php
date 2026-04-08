<div id="facility-booking-reinstate">
    <form id="facility-booking-reinstate-form" data-facility-id={{$facilityBooking->FB_FacilityBookingID}}>
        <table class="facility-booking-reinstate-form-table" @if($availableRecurrenceFacilityBookingCount == 1) style="display: none;" @endif>
            <tbody id="facility-booking-recurring-details-table-body">
                <tr><td>
                    <input  @if($availableRecurrenceFacilityBookingCount == 1) checked @endif type="radio" id="reinstate_entire_booking" name="reinstate_booking" value="entire_booking">
                    <label for="reinstate_entire_booking">Reinstate - Entire Booking</label>
                </td></tr>
                <tr>
                    <td>
                    <input type="radio" id="reinstate_some_booking" name="reinstate_booking" value="some_booking">
                    <label for="reinstate_some_booking">Reinstate - Some Recurrences</label>                    
                    <div id="reinstate_some_recur" style="display:none;">
                        @foreach($facilityBooking->facilityBookingRecurrence->availableFacilityBookings()->with(['facility', 'linkedToFacilityBooking'])->where('FB_BookingStatus', \App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_CANCELLED)->get() as $facilityBookingM)
                        @if($facilityBookingM->linkedToFacilityBooking != null && $facilityBookingM->linkedToFacilityBooking->FB_BookingStatus == \App\Models\FacilityBooking\FacilityBooking::BOOKING_STATUS_CANCELLED)
                        @continue
                        @endif
                        <input type="checkbox" name="some_booking_list[]" id="reinstate_some_{{$facilityBookingM->FB_FacilityBookingID}}" value="{{$facilityBookingM->FB_FacilityBookingID}}">
                        <label for="reinstate_some_{{$facilityBookingM->FB_FacilityBookingID}}">{{$facilityBookingM->FB_BookingTitle}} - {{$facilityBookingM->facility->FC_FacilityName}} ({{$facilityBookingM->FB_BookingStartDateTime->format('d/m/Y H:i')}} - {{$facilityBookingM->FB_BookingEndDateTime->format('d/m/Y H:i')}})</label>
                        <br>
                        @endforeach
                    </div>
                    </td>                    
                </tr>
                <tr>
                    <td>
                    <input type="radio" id="reinstate_range_booking" name="reinstate_booking" value="recurrence_booking">
                    <label for="reinstate_range_booking">Reinstate - Range of Recurrences</label>
                    <div id="reinstate_range_recur" style="display:none;">
                        <div style="display: inline-flex;"> 
                            <div>
                                From Date
                                <input type="text" class="facility-booking-form-input" id="reinstate_booking_from_date" data-recurrence-start-date="{{$facilityBooking->facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d')}}" data-recurrence-end-date="{{$facilityBooking->facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d')}}" name="reinstate_booking_from_date">
                            </div>
                            <div> 
                                To Date
                                <input type="text" class="facility-booking-form-input" id="reinstate_booking_to_date" data-recurrence-start-date="{{$facilityBooking->facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d')}}" data-recurrence-end-date="{{$facilityBooking->facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d')}}" name="reinstate_booking_to_date">
                            <div>
                        </div>
                    </div>
                    </td>
                </tr>                
            </tbody>
        </table>
         @if($availableRecurrenceFacilityBookingCount == 1)
         Are you sure you want to Reinstate this Booking/Booking Request?
         @endif         
        <div id="reinstate_error_text" style="font-color:red;">
        </div>
        <br>
        <div style="text-align: center">
            <button type="button" id="reinstate-booking">Reinstate Bookings</button>
        </div>
    </form>
    <div id="reinstate-submit-spinner" style="display: none;">
        <div id="reinstate-spinner-container" style="text-align: center;">
            <div class="loader-spinner" style="margin-left: 45%;"></div>
            <span>Processing request</span>
        </div>
    </div>
</div>