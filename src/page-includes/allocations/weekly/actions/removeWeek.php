<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/PublishWeekService.php';
$request = Request::createFromGlobals();
?>

<form name="removeweekallocations">

    <table class="tablesmalltidy" width="100%">
        <tr height="30px">
            <th colspan="2"><b>Delete Week<b></th>
        </tr>

        <tr>
            <td colspan="2">Please select week</td>
        </tr>

        <tr>
            <td colspan="2" >
                <input id="weeknumber"  name="removeweeknumber" type="text" size="20" value=<?php echo $request->get('weeknumber');?>>
            </td>
        </tr>


        <input type="hidden" name="scheduledteamid" value="<?php echo $request->get('teamId');?>">
        <input type="hidden" name="action" value="saveremoveweek">
        <input type="hidden" name="userId" value="<?php echo isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];; ?>">
        <tr>
            <td align="right">
                <input type="button" name="removeweekApprove" id="removeweekApprove" value="OK">
                <input type="button" value="Cancel" onclick="$('#facebox .close').click()">
            </td>
        </tr>
    </table>


</form>

<script>
	$(document).ready(function(){
        $("#weeknumber").select();

        $('#facebox .close')
            .click($.facebox.close)
            .empty()
            .append('<img src="'
            + $.facebox.settings.closeImage
            + '" class="close_image" title="close">')
	});


</script>