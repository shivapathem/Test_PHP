<form id="create-facility-booker-note-form" autocomplete="off">
    <table class="facility-booking-form">
        <tbody>
            <tr>
                <td style="width:20%">Note<span class="required-asterisk">*</span></td>
                <td colspan="3">
                    <textarea rows="1" style="width:98%" id="booker_note_frm" name="booker_note_frm" required aria-required="true">{{isset($facilityBookerNote) ? $facilityBookerNote->FBN_Note : ''}}</textarea>
                </td>
            </tr>
             <tr>
                <td>Date<span class="required-asterisk">*</span></td>
                <td colspan="3">
                    <input value="{{isset($facilityBookerNote) ? $facilityBookerNote->FBN_StartDateTime->format('d/m/Y') : $clickPositionDatetime->format('d/m/Y')}}" type="text" class="facility-booking-form-input" id="booker_note_date" name="booker_note_date" value="" style="width: 15%;" required aria-required="true">
                </td>
             </tr>
            <tr>
                <td>Start Time<span class="required-asterisk">*</span></td>
                <td>
                    @php($existingStartTime = isset($facilityBookerNote) ? $facilityBookerNote->FBN_StartDateTime->format('H:i') : '')
                    @if(empty($existingStartTime))
                    @php($existingStartTime = $clickPositionDatetime->format('H:i'))
                    @endif
                    <select id="booker_note_start_time" name="booker_note_start_time" data-placeholder="Select Start Time" required aria-required="true">
                        <option></option>
                        @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                        @php($interval = $interval->format('H:i'))
                        <option value="{{$interval}}"
                        @if($existingStartTime == $interval) selected @endif
                        >{{$interval}}</option>
                        @endforeach
                    </select>
                </td>
                <td>End Time<span class="required-asterisk">*</span></td>
                <td>
                    @php($existingEndTime = isset($facilityBookerNote) ? $facilityBookerNote->FBN_EndDateTime->format('H:i') : '')
                    @if(empty($existingEndTime))
                    @php($existingEndTime = $clickPositionDatetime->clone()->addMinutes(15)->format('H:i'))
                    @endif
                    <select id="booker_note_end_time" name="booker_note_end_time" data-placeholder="Select End Time" required aria-required="true">
                        <option></option>
                        @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                        @php($interval = $interval->format('H:i'))
                        <option value="{{$interval}}"
                        @if($existingEndTime == $interval) selected @endif
                        >{{$interval}}</option>
                        @endforeach
                    </select>
                </td>
            </tr>
            <tr>
                <td>Recurring Note<span class="required-asterisk">*</span></td>
                <td colspan="3">
                    <input type="radio" id="recurring_booker_note_yes" name="recurring_booker_note_enable" value="yes" required aria-required="true"
                    @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->is_recurring) checked @endif
                    >
                    <label for="recurring_booker_note_yes">Yes</label>
                    <input type="radio" id="recurring_booker_note_no" name="recurring_booker_note_enable" value="no"
                    @if(isset($facilityBookerNoteRecurrence) && !$facilityBookerNoteRecurrence->is_recurring) checked @endif
                    >
                    <label for="recurring_booker_note_no">No</label>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="facility-booking-form" style="margin-top:8px">
        <tbody id="facility-booker-note-recurring-details-table-body"
        @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->is_recurring)
        @else
        style="display: none"
        @endif
        >
            <tr>
                <td>Start Date<span class="required-asterisk">*</span></td>
                <td>
                    <input type="text" class="facility-booking-form-input" id="booker_note_recurring_start_date" name="booker_note_recurring_start_date"
                    @isset($facilityBookerNoteRecurrence) value="{{$facilityBookerNoteRecurrence->FBNR_SeriesStartDate->format('d/m/Y')}}" @endisset
                    >
                </td>
                <td>End Date<span class="required-asterisk">*</span></td>
                <td>
                    <input type="text" class="facility-booking-form-input" id="booker_note_recurring_end_date" name="booker_note_recurring_end_date"
                    @isset($facilityBookerNoteRecurrence) value="{{$facilityBookerNoteRecurrence->FBNR_SeriesEndDate->format('d/m/Y')}}" @endisset
                    >
                </td>
            </tr>
            <tr>
                <td>Start Time</td>
                <td id="booker-note-recurring-start-time">
                    @isset($facilityBookerNoteRecurrence)
                    {{substr($facilityBookerNoteRecurrence->FBNR_StartTime, 0,5)}}
                    @else
                    00:00
                    @endisset
                </td>
                <td>End Time</td>
                <td id="booker-note-recurring-end-time">
                    @isset($facilityBookerNoteRecurrence)
                    {{substr($facilityBookerNoteRecurrence->FBNR_EndTime, 0,5)}}
                    @else
                    00:00
                    @endisset
                </td>
            </tr>
            @php($recurrenceType = (isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->is_recurring) ? $facilityBookerNoteRecurrence->FBNR_RecurrenceType : '')
            <tr>
                <td colspan="4">
                    <input type="radio" id="booker_note_recurring_daily" name="booker_note_recurring_recurrence_type" value="{{App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY}}"
                    @if($recurrenceType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY)
                    checked
                    @endif
                    >
                    <label for="booker_note_recurring_daily">Daily</label>
                    <input type="radio" id="booker_note_recurring_weekly" name="booker_note_recurring_recurrence_type" value="{{App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY}}"
                    @if($recurrenceType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY)
                    checked
                    @endif
                    >
                    <label for="booker_note_recurring_weekly">Weekly</label>
                </td>
            </tr>
            <tr>
                <td colspan="4" id="booker-note-daily-recurrence-setting-container"
                @if($recurrenceType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY)
                @else
                style="display: none"
                @endif
                >
                    <div id="recurring-booker-note-recurrence-daily-days-container">
                        Recur every<span class="required-asterisk">*</span> <input style="width: 42px;" min='1' type="number" name="recurring_booker_note_recurrence_daily_days"
                        @if($recurrenceType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_DAILY)
                        value="{{$facilityBookerNoteRecurrence->FBNR_RecurrenceDayInterval}}"
                        @endif
                        > day(s)
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="4" id="booker-note-weekly-recurrence-setting-container"
                @if($recurrenceType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY)
                @else
                style="display: none"
                @endif
                >
                    Recur every<span class="required-asterisk">*</span> <input style="width: 42px;" min='1' type="number" name="recurring_booker_note_recurrence_weekly_weeks"
                    @if($recurrenceType == App\Models\FacilityBooking\FacilityBookingRecurrence::RECUR_TYPE_WEEKLY)
                    value="{{$facilityBookerNoteRecurrence->FBNR_RecurrenceWeekInterval}}"
                    @endif
                    > weeks(s) on : <br>
                    <input type="checkbox" id="recurring_booker_note_weekly_saturday" name="recurring_booker_note_weekly_days[]" value="saturday"
                    @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->FBNR_RecurrenceWeek_Saturday == 1)
                    checked
                    @endif
                    >
                    <label for="recurring_booker_note_weekly_saturday">Saturday</label>
                    <input type="checkbox" id="recurring_booker_note_weekly_sunday" name="recurring_booker_note_weekly_days[]" value="sunday"
                    @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->FBNR_RecurrenceWeek_Sunday == 1)
                    checked
                    @endif
                    >
                    <label for="recurring_booker_note_weekly_sunday">Sunday</label>
                    <input type="checkbox" id="recurring_booker_note_weekly_monday" name="recurring_booker_note_weekly_days[]" value="monday"
                    @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->FBNR_RecurrenceWeek_Monday == 1)
                    checked
                    @endif
                    >
                    <label for="recurring_booker_note_weekly_monday">Monday</label>
                    <input type="checkbox" id="recurring_booker_note_weekly_tuesday" name="recurring_booker_note_weekly_days[]" value="tuesday"
                    @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->FBNR_RecurrenceWeek_Tuesday == 1)
                    checked
                    @endif
                    >
                    <label for="recurring_booker_note_weekly_tuesday">Tuesday</label>
                    <input type="checkbox" id="recurring_booker_note_weekly_wednesday" name="recurring_booker_note_weekly_days[]" value="wednesday"
                    @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->FBNR_RecurrenceWeek_Wednesday == 1)
                    checked
                    @endif
                    >
                    <label for="recurring_booker_note_weekly_wednesday">Wednesday</label>
                    <input type="checkbox" id="recurring_booker_note_weekly_thursday" name="recurring_booker_note_weekly_days[]" value="thursday"
                    @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->FBNR_RecurrenceWeek_Thursday == 1)
                    checked
                    @endif
                    >
                    <label for="recurring_booker_note_weekly_thursday">Thursday</label>
                    <input type="checkbox" id="recurring_booker_note_weekly_friday" name="recurring_booker_note_weekly_days[]" value="friday"
                    @if(isset($facilityBookerNoteRecurrence) && $facilityBookerNoteRecurrence->FBNR_RecurrenceWeek_Friday == 1)
                    checked
                    @endif
                    >
                    <label for="recurring_booker_note_weekly_friday">Friday</label>
                </td>
            </tr>
        </tbody>
    </table>
    @if(isset($facilityBookerNote))
    <table id="facility-booker-note-update-instances-detail" class="facility-booking-form" style="margin-top:8px; {{ !$facilityBookerNoteRecurrence->is_recurring ? 'display:none;' : '' }}">
        <tr><td>
            <div>
                <span>Would you like this change to be applied to:</span>
                <input type="radio" id="booker_note_save_recurrence_type_only_one" name="booker_note_save_recurrence_type" value="current" @checked(!$facilityBookerNoteRecurrence->is_recurring)>
                <label for="booker_note_save_recurrence_type_only_one">This instance only</label>
                <input type="radio" id="booker_note_save_recurrence_type_date_range" name="booker_note_save_recurrence_type" value="date_range">
                <label for="booker_note_save_recurrence_type_date_range">A range of instances</label>
                <input type="radio" id="booker_note_save_recurrence_type_all_series" name="booker_note_save_recurrence_type" value="all_series">
                <label for="booker_note_save_recurrence_type_all_series"> All instances in the series</label>
            </div>
            <div style="display: none" id="booker-note-update-recurrence-date-range" data-recurrence-start-date="{{$facilityBookerNote->facilityBookerNoteRecurrence->FBNR_SeriesStartDate->format('Y-m-d')}}" data-recurrence-end-date="{{$facilityBookerNote->facilityBookerNoteRecurrence->FBNR_SeriesEndDate->format('Y-m-d')}}">
                <br>
                <table class="facility-booking-form">
                    <tr>
                        <td>Start Date</td>
                        <td>
                            <input type="text" class="facility-booking-form-input" id="booker_note_save_recurrence_type_start_date_range" name="booker_note_save_recurrence_type_start_date_range" value="">
                        </td>
                        <td>End Date</td>
                        <td>
                            <input type="text" class="facility-booking-form-input" id="booker_note_save_recurrence_type_end_date_range" name="booker_note_save_recurrence_type_end_date_range" value="">
                        </td>
                    </tr>
                </table>
            </div>
        </td></tr>
    </table>
    @endif
    @csrf
</form>
<div id="overlap-validate-booker-note">
</div>
<div style="text-align: right">
    <button type="button" style="font-size:12px" id="booker-note-recurring-save-btn" class="ui-button ui-widget ui-corner-all">Save</button>
    <button type="button" style="font-size:12px" id="booker-note-recurring-cancel-btn" class="ui-button ui-widget ui-corner-all">Cancel</button>
</div>
<input type="hidden" id="booker-note-facility-id" value="{{$facility->FC_FacilityID}}" />
<input type="hidden" id="facility-active-from" value="{{$facility->FC_ActiveFrom}}">
@isset($facilityBookerNote)
<input type="hidden" id="booker-note-id-edit" value="{{$facilityBookerNote->FBN_FacilityBookerNoteID}}" />
@endisset