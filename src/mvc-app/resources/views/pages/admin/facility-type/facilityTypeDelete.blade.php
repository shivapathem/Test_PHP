<form id="faciltiytype-delete-form" style="margin-bottom: 15px;">
    <table class="delete-popup-table">
        <tbody>
            <tr>
                <th colspan="2">Delete Facility Type</th>
            </tr>
        
        <tr>
            <td class="validation-error-text" >Are you sure you want to Delete this Facility Type?</td>
        </tr>
        @csrf
        <tr>
            <td class="delete-popup-button-group">
                <button type="button" class="delete-popup" id="approve-delete-facilitytype-form" data-facilitytype-id="{{$facility_type->FT_FacilityTypeID}}">Yes</button>
                <button type="button" class="delete-popup" id="cancel-delete-facilitytype-form">No</button>
                <button type="button" class="delete-popup" id="ignore-delete-facilitytype-form">Ok</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>