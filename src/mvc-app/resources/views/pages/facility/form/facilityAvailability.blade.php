<link rel="stylesheet" href="/mvc-app/public/{{mix('css/facilityAvailability/facilityAvailability.css')}}" />
<div id="facility-availability-modal" style="overflow: visible !important; position: relative;">
    @php($facilityAvailability = isset($editFacility) ? $editFacility->facilityAvailability : null)
    <form id="facility-availability-form" style="overflow: visible;">
        <div class="plain-table facility-form" style="overflow: visible;">
            <div class="plain-table__row plain-table__row--header">
                <div class="plain-table__cell">Day</div>
                <div class="plain-table__cell">From</div>
                <div class="plain-table__cell">To</div>
                <div class="plain-table__cell">Unavailable</div>
            </div>

            @foreach(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day)
            <div class="plain-table__row {{ $loop->last ? 'plain-table__row--last' : '' }}" style="overflow: visible;">
                <div class="plain-table__cell">
                    {{mb_ucfirst($day)}}
                </div>
                <div class="plain-table__cell" style="overflow: visible;">
                    <select style="position: relative; z-index: 9999;" id="facility_availability_{{$day}}_from_frm" name="facility_availability_{{$day}}_from_frm" class="facility-form-input facility-avaliablility-chosen">
                        @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                        @php($interval = $interval->format('H:i'))
                        <option value="{{$interval}}"
                            @if(isset($facilityAvailability) && substr($facilityAvailability['FCA_FacilityTimeFrom_' . ucfirst($day)], 0, 5)==$interval) selected @endif>{{$interval}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="plain-table__cell">
                    <select id="facility_availability_{{$day}}_to_frm" name="facility_availability_{{$day}}_to_frm" class="facility-form-input facility-avaliablility-chosen">
                        @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::createFromTime(0, 0, 0), '15 minutes', \Carbon\Carbon::createFromTime(23, 45, 0)) as $interval)
                        @php($interval = $interval->format('H:i'))
                        <option value="{{$interval}}"
                            @if(isset($facilityAvailability) && substr($facilityAvailability['FCA_FacilityTimeTo_' . ucfirst($day)], 0, 5)==$interval) selected @endif>{{$interval}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="plain-table__cell plain-table__cell--center" style="text-align:center">
                    <input id="facility_availability_{{$day}}_unavailability_frm" type="checkbox" name="facility_unavailability_frm[]" value="{{$day}}"
                        @if(isset($facilityAvailability) && $facilityAvailability['FCA_FacilityTimeAvailability_' . ucfirst($day)]==0) checked @endif>
                </div>
            </div>
            @endforeach
        </div>
    </form>
    <div style="text-align: right; margin-top: 5px;">
        <button type="button" style="font-size:12px" id="facility-availability-save-btn" class="ui-button ui-widget ui-corner-all">Done</button>
    </div>
</div>