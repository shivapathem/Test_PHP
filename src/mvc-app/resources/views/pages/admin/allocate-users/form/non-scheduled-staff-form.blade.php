<form id="newstaffteamuser">
    <table class="redtable" width="520px" id="js_usersAD" style="margin: 6px 0px 0px;">
        <tbody>
            <tr>
                <th colspan="2" align="center" style="padding-bottom: 10px; padding-top: 15px; border-right: solid 1px #666;">Add New Non Scheduled Staff Member<br></th>
            </tr>
            <tr id="bugtd">
                <td colspan="2" class="messageerror error" align="center"></td>
            </tr>
            <tr>
                <td style="padding: 5px;width: 170px;">Network Login</td>
                <td style="padding: 3px;">
                    <input type="text" style="width:342px" id="js_netLogin" name="netLogin" size="35" value="">
                </td>
            </tr>
        </tbody>
    </table>
    <p style="float: right; margin-top: 5px; margin-bottom: 0px;">
        <button type="button" class="allocate-user-add-form-button" id="js_nonschedteamstaff" aria-label="Add new non scheduled staff member">Add User</button>
    </p>
    <input type="hidden" name="js_checkalloacteuser" value="checkalloacate">
    <input type="hidden" name="teamid" value="{{ $teamId }}">
</form>
