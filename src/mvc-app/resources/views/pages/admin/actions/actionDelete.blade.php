<form id="action-delete-form" style="margin-bottom: 15px;">
    <table class="delete-popup-table">
        <tbody>
            <tr>
                <th colspan="2">Delete Action</th>
            </tr>

        <tr>
            <td class="validation-error-text">
                Are you sure you want to Delete this Action?
                @if($futureBookingsCount > 0)
                    <br><br>
                    <strong>Warning:</strong> This Action is attached to {{ $futureBookingsCount }} future booking(s) and will be removed from them.
                @endif
            </td>
        </tr>
        @csrf
        <tr>
            <td style="text-align: center">
                <button type="button" class="delete-popup" id="cancel-delete-action-form" aria-label="Cancel deletion action">No</button>
                <button type="button" class="delete-popup" id="approve-delete-action-form" data-action-id="{{$action->action_id}}" aria-label="Approve deletion action">Yes</button>
                <button type="button" class="delete-popup" id="ignore-delete-action-form" aria-label="Ignore and continue">Ok</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>