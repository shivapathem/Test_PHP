<form id="customer-delete-form">
    <table class="delete-popup-table">
        <tbody>
            <tr>
                <th colspan="2">Delete Customer</th>
            </tr>
        
        <tr>
            <td class="validation-error-text" >Are you sure you want to Delete this Customer?</td>
        </tr>
        @csrf
        <tr>
            <td style="text-align: right;padding:4px;">
                <button type="button" class="delete-popup" id="approve-delete-customer-form" data-customer-id="{{$customer->EC_ExternalCustomerID}}">Yes</button>
                <button type="button" class="delete-popup" id="cancel-delete-customer-form">No</button>
                <button type="button" class="delete-popup" id="ignore-delete-customer-form">Ok</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>