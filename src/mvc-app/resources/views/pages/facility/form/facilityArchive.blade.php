<form id="facility-archive-form" style="margin-bottom: 15px; width: 390px;">
    <input type="hidden" id="facility_active_from_date" value="{{ $facility->FC_ActiveFrom ? $facility->FC_ActiveFrom->format('d/m/Y') : '' }}">
    <table class="delete-popup-table" id ="archive_from_confirm">
        <tbody>
            <tr>
                <th colspan="2" id="archive-header-td-text">Archive Facility</th>
            </tr>

        <tr>
            <td id="archive-td-text" style="padding-left: 5px;">Are you sure you want to Archive this Facility?</td>
        </tr>

        @csrf
        <tr>
            <td class="delete-popup-button-group">
                <button type="button" class="delete-popup" id="approve-archive-facility-form" style="margin-bottom: 5px;" data-archive-id="{{$facility->FC_FacilityID}}">Yes</button>
                <button type="button" class="delete-popup" id="cancel-archive-facility-form" style="margin-bottom: 5px;">No</button>
                <button type="button" class="delete-popup" id="ignore-archive-facility-form" style="margin-bottom: 5px;">OK</button>
            </td>
        </tr>
        </tbody>
    </table>


    <table class="delete-popup-table" id ="archive_from_date" style="display: none;">
        <tbody>
            <tr>
                <th colspan="2">Archive Facility</th>
            </tr>


        <tr>
            <td style="font-size: 14px; padding-left: 5px; width: 100px; vertical-align: baseline;">Archive From</td>
            <td> <input type="text" class="facility-form-input" id="archive_from" name="archive_from" min="{{ date('Y-m-d') }}" onkeydown="return false"></td>
        </tr>
        @csrf
        <tr>
            <td class="delete-popup-button-group" colspan="2">
                <button type="button" id="archive-facility-form" data-archive-id="{{$facility->FC_FacilityID}}" style="margin-bottom: 5px;">Archive</button>

                <button type="button" id="ignore-archive-facility" style="display: none; margin-bottom: 5px;">OK</button>

            </td>
        </tr>
        </tbody>
    </table>

    <table class="delete-popup-table" id ="active_from_date" style="display: none;">
        <tbody>
            <tr>
                <th colspan="2">Activate Facility</th>
            </tr>


        <tr>
            <td style="padding-left: 5px;"> Activate From  </td>
            <td> <input type="text" class="facility-form-input" id="active_from" name="active_from" >
                </td>
        </tr>
        @csrf
        <tr>
            <td class="delete-popup-button-group" colspan="2">
                <button type="button" id="active-facility-form" data-archive-id="{{$facility->FC_FacilityID}}" style="margin-bottom: 5px;">Activate</button>

            </td>
        </tr>
        </tbody>
    </table>
</form>