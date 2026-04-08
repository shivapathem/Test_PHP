<form id="facility-delete-form" style="margin-bottom: 15px;">
    <table class="delete-popup-table">
        <tbody>
            <tr>
                <th colspan="2" >Delete Facility</th>
            </tr>

        <tr>
            <td class="validation-error-text" >Are you sure you want to Delete this Facility?</td>
        </tr>
        @csrf
        <tr>
            <td class="delete-popup-button-group">
                <button type="button" class="delete-popup" id="approve-delete-facility-form" style="margin-bottom: 5px;" data-facility-id="{{$facility->FC_FacilityID}}">Yes</button>
                <button type="button" class="delete-popup" id="cancel-delete-facility-form" style="margin-bottom: 5px;">No</button>
                <button type="button" class="delete-popup" id="ignore-delete-facility-form" style="margin-bottom: 5px;">OK</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>