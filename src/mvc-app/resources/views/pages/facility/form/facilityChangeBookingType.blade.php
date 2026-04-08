<div id="facility-change-booking-type-modal">
    @php($facilityChangeBookingType = isset($editFacility) ? $editFacility->facilityChangeBookingType : null)
    <form id="facility-change-booking-type-form" autocomplete="off">
        <table class="facility-form">
            <tbody>
                <tr style="text-align: center">
                    <td>
                        Start Date
                    </td>
                    <td>
                        <input value="{{isset($facilityChangeBookingType) ? $facilityChangeBookingType->FCBT_FacilityChangeBookingTypeStartDate->format('d/m/Y') : ''}}" type="text" id="change_booking_type_start_date_frm" name="change_booking_type_start_date_frm" class="facility-form-input">
                    </td>
                    <td>
                        End Date
                    </td>
                    <td>
                        <input value="{{isset($facilityChangeBookingType) ? $facilityChangeBookingType->FCBT_FacilityChangeBookingTypeEndDate->format('d/m/Y') : ''}}" type="text" id="change_booking_type_end_date_frm" name="change_booking_type_end_date_frm"  class="facility-form-input">
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="facility-form">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>From</th>
                    <th>To</th>
                </tr>
            </thead>
            <tbody>
            @foreach(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day)
                <tr>
                    <td>
                        {{mb_ucfirst($day)}}
                    </td>
                    <td>
                        <select id="facility_change_booking_type_{{$day}}_from_frm" name="facility_change_booking_type_{{$day}}_from_frm" class="facility-form-input">
                            @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                                @php($interval = $interval->format('H:i'))
                                <option value="{{$interval}}"
                                @if(isset($facilityChangeBookingType) && substr($facilityChangeBookingType['FCBT_FacilityChangeBookingTypeTimeFrom_' . ucfirst($day)], 0, 5) == $interval) selected @endif
                                >{{$interval}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select id="facility_change_booking_type_{{$day}}_to_frm" name="facility_change_booking_type_{{$day}}_to_frm" class="facility-form-input">
                            @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                                 @php($interval = $interval->format('H:i'))
                                <option value="{{$interval}}"
                                 @if(isset($facilityChangeBookingType) && substr($facilityChangeBookingType['FCBT_FacilityChangeBookingTypeTimeTo_' . ucfirst($day)], 0, 5) == $interval) selected @endif
                                >{{$interval}}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </form>
    <br>
    <div style="text-align: right">
        <button type="button" style="font-size:12px" id="facility-change-booking-type-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
    </div>
</div>