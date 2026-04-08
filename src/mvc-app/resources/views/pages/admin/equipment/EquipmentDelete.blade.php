<form id="equipment-delete-form" style="margin-bottom: 15px;">
    <table class="delete-popup-table">
        <tbody>
            <tr>
                <th colspan="2">Delete Equipment</th>
            </tr>
        
        <tr>
            <td class="validation-error-text" >Are you sure you want to Delete this Equipment?</td>
        </tr>
        @csrf
        <tr>
            <td class="delete-popup-button-group">
                <button type="button" class="delete-popup" id="approve-delete-equipment-form" data-equipment-id="{{$equipment->EQ_EquipmentID}}">Yes</button>
                <button type="button" class="delete-popup" id="cancel-delete-equipment-form">No</button>
                <button type="button" class="delete-popup" id="ignore-delete-equipment-form">Ok</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>