<div id="facility-booking-recurring-modal">
    <form id="facility-booking-recurring-form">
        <table class="facility-booking-form">
            <tbody id="facility-booking-recurring-details-table-body">
                    <tr>
                        <td>Start Date<span class="required-asterisk">*</span></td>
                        <td>
                            <input type="text" class="facility-booking-form-input" id="recurring_start_date" name="recurring_start_date"
                            @isset($facilityBookingRecurrence)
                            value="{{$facilityBookingRecurrence->FBR_SeriesStartDate->format('d/m/Y')}}"
                            data-existing-date="{{$facilityBookingRecurrence->FBR_SeriesStartDate->format('Y-m-d')}}"
                            @if(!$facilityBookingRecurrence->facilityRecurrenceStartDateEditable() && !isset($copyBookingDate)) readonly @endif
                            @endisset
                            >
                        </td>
                        <td>End Date<span class="required-asterisk">*</span></td>
                        <td>
                            <input type="text" class="facility-booking-form-input" id="recurring_end_date" name="recurring_end_date" @isset($facilityBookingRecurrence)value="{{$facilityBookingRecurrence->FBR_SeriesEndDate->format('d/m/Y')}}" data-existing-date="{{$facilityBookingRecurrence->FBR_SeriesEndDate->format('Y-m-d')}}" @endisset>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4"><b>Appointment Time</b></td>
                    </tr>
                    <tr>
                        <td>Start Time</td>
                        <td id="recurring-start-time">
                            @isset($facilityBookingRecurrence)
                            {{substr($facilityBookingRecurrence->FBR_StartTime, 0, 5)}}
                            @else
                            00:00
                            @endisset
                        </td>
                        <td>End Time</td>
                        <td id="recurring-end-time">
                            @isset($facilityBookingRecurrence)
                            {{substr($facilityBookingRecurrence->FBR_EndTime, 0, 5)}}
                            @else
                            00:00
                            @endisset
                        </td>
                    </tr>
                    @php($recurringType = (isset($facilityBookingRecurrence) && $facilityBookingRecurrence->is_recurring) ? $facilityBookingRecurrence->FBR_RecurrenceType : '' )
                    <tr>
                        <td colspan="4">
                            <input type="radio" id="recurring_booking_daily" name="recurring_booking_recurrence_type" value="{{App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY}}"
                            @if($recurringType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY)
                            checked
                            @endif
                            >
                            <label for="recurring_booking_daily">Daily</label>
                            <input type="radio" id="recurring_booking_weekly" name="recurring_booking_recurrence_type" value="{{App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY}}"
                            @if($recurringType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY)
                            checked
                            @endif
                            >
                            <label for="recurring_booking_weekly">Weekly</label>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" id="daily-recurrence-setting-container"
                        @if($recurringType != App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY)
                        style="display: none"
                        @endif
                        >
                            <div id="recurring-booking-recurrence-daily-days">
                                Recur every<span class="required-asterisk">*</span> <input style="width: 42px;" min='1' type="number" id="recurring_booking_recurrence_daily_days" name="recurring_booking_recurrence_daily_days"
                                @if($recurringType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY)
                                value="{{$facilityBookingRecurrence->FBR_RecurrenceDayInterval}}"
                                @endif
                                > day(s)
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" id="weekly-recurrence-setting-container"
                        @if($recurringType != App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY)
                        style="display: none"
                        @endif
                        >
                            <div id="recurring-booking-recurrence-weekly-days">
                                Recur every<span class="required-asterisk">*</span> <input style="width: 42px;" min='1' type="number" id="recurring_booking_recurrence_weekly_weeks" name="recurring_booking_recurrence_weekly_weeks"
                                @if($recurringType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY)
                                value="{{$facilityBookingRecurrence->FBR_RecurrenceWeekInterval}}"
                                @endif
                                > weeks(s) on : <br>
                                <input type="checkbox" id="recurring_booking_weekly_saturday" name="recurring_booking_weekly_days[]" value="saturday"
                                @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_RecurrenceWeek_Saturday == 1)
                                checked
                                @endif
                                >
                                <label for="recurring_booking_weekly_saturday">Saturday</label>
                                <input type="checkbox" id="recurring_booking_weekly_sunday" name="recurring_booking_weekly_days[]" value="sunday"
                                @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_RecurrenceWeek_Sunday == 1)
                                checked
                                @endif
                                >
                                <label for="recurring_booking_weekly_sunday">Sunday</label>
                                <input type="checkbox" id="recurring_booking_weekly_monday" name="recurring_booking_weekly_days[]" value="monday"
                                @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_RecurrenceWeek_Monday == 1)
                                checked
                                @endif
                                >
                                <label for="recurring_booking_weekly_monday">Monday</label>
                                <input type="checkbox" id="recurring_booking_weekly_tuesday" name="recurring_booking_weekly_days[]" value="tuesday"
                                @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_RecurrenceWeek_Tuesday == 1)
                                checked
                                @endif
                                >
                                <label for="recurring_booking_weekly_tuesday">Tuesday</label>
                                <input type="checkbox" id="recurring_booking_weekly_wednesday" name="recurring_booking_weekly_days[]" value="wednesday"
                                @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_RecurrenceWeek_Wednesday == 1)
                                checked
                                @endif
                                >
                                <label for="recurring_booking_weekly_wednesday">Wednesday</label>
                                <input type="checkbox" id="recurring_booking_weekly_thursday" name="recurring_booking_weekly_days[]" value="thursday"
                                @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_RecurrenceWeek_Thursday == 1)
                                checked
                                @endif
                                >
                                <label for="recurring_booking_weekly_thursday">Thursday</label>
                                <input type="checkbox" id="recurring_booking_weekly_friday" name="recurring_booking_weekly_days[]" value="friday"
                                @if(isset($facilityBookingRecurrence) && $facilityBookingRecurrence->FBR_RecurrenceWeek_Friday == 1)
                                checked
                                @endif
                                >
                                <label for="recurring_booking_weekly_friday">Friday</label>
                            </div>
                        </td>
                    </tr>
            </tbody>
        </table>
        <br>
        <div style="text-align: right">
            <button type="button" style="font-size:12px" id="facility-booking-recurring-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
        </div>
    </form>
</div>