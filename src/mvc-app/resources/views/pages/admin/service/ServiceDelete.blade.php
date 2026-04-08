<form id="service-delete-form" style="margin-bottom: 15px;">
    <table class="delete-popup-table">
        <tbody>
            <tr>
                <th colspan="2">Delete Services</th>
            </tr>
        
        <tr>
            <td class="validation-error-text" >Are you sure you want to Delete this Service?</td>
        </tr>
        @csrf
        <tr>
            <td class="delete-popup-button-group">
                <button type="button" class="delete-popup" id="approve-delete-service-form" data-service-id="{{$service->SR_ServiceID}}">Yes</button>
                <button type="button" class="delete-popup" id="cancel-delete-service-form">No</button>
                <button type="button" class="delete-popup" id="ignore-delete-service-form">Ok</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>