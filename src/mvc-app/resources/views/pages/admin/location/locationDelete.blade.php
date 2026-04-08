<form id="location-delete-form" style="margin-bottom: 15px;">
    <table class="delete-popup-table">
        <tbody>
            <tr>
                <th colspan="2">Delete Location</th>
            </tr>
        
        <tr>
            <td class="validation-error-text" >Are you sure you want to Delete this Location?</td>
        </tr>
        @csrf
        <tr>
            <td class="delete-popup-button-group">
                <button type="button" class="delete-popup" id="approve-delete-location-form" data-location-id="{{$location->LN_LocationID}}">Yes</button>
                <button type="button" class="delete-popup" id="cancel-delete-location-form">No</button>
                <button type="button" class="delete-popup" id="ignore-delete-location-form">Ok</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>