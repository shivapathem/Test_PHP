<div id="facility-mark-as-unavailable-modal" style="overflow: visible !important; position: relative;">
    @php
    $facilityMarkAsUnavailable = isset($editFacility) ? $editFacility->facilityMarkAsUnavailable : null;
    $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    $intervals = \Carbon\CarbonPeriod::create(
    \Carbon\Carbon::createFromTime(0, 0, 0),
    '15 minutes',
    \Carbon\Carbon::createFromTime(23, 45, 0)
    );
    @endphp

    <script src="/mvc-app/public{{ mix('js/facility/form/facilityMarkAsUnavailable.js') }}" defer></script>
    <link rel="stylesheet" href="/mvc-app/public/{{mix('css/facilityMarkAsUnavailable/facilityMarkAsUnavailable.css')}}" />
    <form id="facility-mark-as-unavailable-form" autocomplete="off" style="overflow: visible;">

        <div class="facility-form fmu-table">
            <div class="fmu-row fmu-row--dates">
                <div class="fmu-cell fmu-cell--header">
                    <label class="fmu-label" for="mark_as_unavailable_start_date_frm">Start Date</label>
                </div>
                <div class="fmu-cell">
                    <input type="text" id="mark_as_unavailable_start_date_frm" name="mark_as_unavailable_start_date_frm" class="facility-form-input"
                        value="{{ $facilityMarkAsUnavailable ? $facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableStartDate->format('d/m/Y') : '' }}">
                </div>
                <div class="fmu-cell fmu-cell--header">
                    <label class="fmu-label" for="mark_as_unavailable_end_date_frm">End Date</label>
                </div>
                <div class="fmu-cell">
                    <input type="text" id="mark_as_unavailable_end_date_frm" name="mark_as_unavailable_end_date_frm" class="facility-form-input"
                        value="{{ $facilityMarkAsUnavailable ? $facilityMarkAsUnavailable->FMU_FacilityMarkUnavailableEndDate->format('d/m/Y') : '' }}">
                </div>
            </div>
        </div>

        <div class="facility-form fmu-table" style="overflow: visible;">
            <div class="fmu-row fmu-row--schedule">
                <div class="fmu-cell fmu-cell--header">Day</div>
                <div class="fmu-cell fmu-cell--header">From</div>
                <div class="fmu-cell fmu-cell--header">To</div>
                <div class="fmu-cell fmu-cell--header fmu-cell--center">Enable</div>
            </div>

            @foreach($daysOfWeek as $day)
            @php
            $dayUcfirst = ucfirst($day);
            $keyFrom = 'FMU_FacilityMarkUnavailableTimeFrom_' . $dayUcfirst;
            $keyTo = 'FMU_FacilityMarkUnavailableTimeTo_' . $dayUcfirst;
            $isCheckedKey = 'FMU_FacilityMarkUnavailableIsChecked_' . $dayUcfirst;
            @endphp

            <div class="fmu-row fmu-row--schedule" style="overflow: visible;">
                <div class="fmu-cell">{{ mb_ucfirst($day) }}</div>

                {{-- Time From --}}
                <div class="fmu-cell" style="overflow: visible;">
                    <select disabled id="facility_mark_as_unavailable_{{$day}}_from_frm"
                        name="facility_mark_as_unavailable_{{$day}}_from_frm"
                        class="facility-form-input facility-avaliablility-chosen"
                        style="position: relative; z-index: 9999;">
                        @foreach($intervals as $interval)
                        @php $intervalFormatted = $interval->format('H:i'); @endphp
                        <option value="{{ $intervalFormatted }}"
                            @if($facilityMarkAsUnavailable && substr($facilityMarkAsUnavailable->$keyFrom, 0, 5) == $intervalFormatted) selected @endif>
                            {{ $intervalFormatted }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Time To --}}
                <div class="fmu-cell" style="overflow: visible;">
                    <select disabled id="facility_mark_as_unavailable_{{$day}}_to_frm"
                        name="facility_mark_as_unavailable_{{$day}}_to_frm"
                        class="facility-form-input facility-avaliablility-chosen"
                        style="position: relative; z-index: 9999;">
                        @foreach($intervals as $interval)
                        @php $intervalFormatted = $interval->format('H:i'); @endphp
                        <option value="{{ $intervalFormatted }}"
                            @if($facilityMarkAsUnavailable && substr($facilityMarkAsUnavailable->$keyTo, 0, 5) == $intervalFormatted) selected @endif>
                            {{ $intervalFormatted }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Checkbox --}}
                <div class="fmu-cell fmu-cell--center">
                    <input type="checkbox"
                        id="facility_mark_as_unavailable_{{$day}}_active_frm"
                        class="facility-mark-as-unavailable-active-chk"
                        name="facility_mark_as_unavailable_{{$day}}_is_checked_frm"
                        @if(isset($facilityMarkAsUnavailable) && isset($facilityMarkAsUnavailable->$isCheckedKey) && $facilityMarkAsUnavailable->$isCheckedKey == 1) checked @endif>
                </div>
            </div>
            @endforeach
        </div>

    </form>


    <div style="text-align: right; margin-top: 5px;">
        <button type="button" style="font-size:12px" id="facility-mark-as-unavailable-save-btn"
            class="ui-button ui-widget ui-corner-all">Done</button>
    </div>
</div>