@php($existingActions = $facilityBooking->actions ?? collect([]))
<div id="facility-booking-action-modal">
    <form id="facility-booking-action-form">
        <table class="facility-booking-form">
            <tbody>
                <tr>
                    <td>
                        Select Action
                    </td>
                    <td>
                        <select class="facility-booking-action-form-input" data-placeholder="Select Action" id="facility_booking_action_chose" name="facility_booking_action_chose[]" multiple>
                            <option></option>
                            @foreach($actions as $action)
                                <option value="{{$action->action_id}}"
                                @if($existingActions->where('action_id', $action->action_id)->count() > 0)
                                selected
                                @endif
                                >{{$action->action_name}}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="facility-booking-form" id="facility-booking-action-details-table">
            <thead>
                <tr>
                    <th>
                        Action Name
                    </th>
                    <th>
                        Start Time
                    </th>
                    <th>
                        End Time
                    </th>
                </tr>
            </thead>
            <tbody id="facility-booking-action-details-table-body">
                {{-- This html is also generated in js  --}}
                @foreach($existingActions as $action)
                <tr class="table-edit-action-row" data-id="{{$action->action_id}}">
                    <td>
                        {{$action->action_name}}
                    </td>
                    <td>
                        <select class="action-start-time" data-id="{{$action->action_id}}">
                            @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                            @php($interval = $interval->format('H:i'))
                            <option value="{{$interval}}"
                            @if(substr($action->getOriginal('pivot_FBA_ActionStartTime'), 0 , 5) == $interval) selected @endif
                            >{{$interval}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="action-end-time" data-id="{{$action->action_id}}">
                            @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                            @php($interval = $interval->format('H:i'))
                            <option value="{{$interval}}"
                            @if(substr($action->getOriginal('pivot_FBA_ActionEndTime'), 0 , 5) == $interval) selected @endif
                            >{{$interval}}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <div style="text-align: right">
            <button type="button" style="font-size:12px" id="facility-booking-action-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
        </div>
    </form>
</div>