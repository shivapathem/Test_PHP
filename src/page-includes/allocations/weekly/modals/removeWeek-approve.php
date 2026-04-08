<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/PublishWeekService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";
$request = Request::createFromGlobals();

list($week, $year) = explode("/", $request->get('weeknumber'));
$week = str_pad($week, 2, '0', STR_PAD_LEFT);
$request->request->set('weeknumber', $week.'/'.$year);
$request->request->set('removeweeknumber', $week.$year);
?>

<form id="removeweekallocations">

    <table class="tablesmalltidy" width="100%">
        <tr height="30px">
            <th colspan="2"><b>Delete Week<b></th>
        </tr>

        <tr id ="bugtd">
            <td colspan="2" class="messageerror error">Are you sure you want to delete this week and all its allocations?</td>
        </tr>

        <input type="hidden" name="scheduledteamid" value="<?php echo $request->get('teamId');?>">
        <input type="hidden" name="weeknumber" value="<?php echo $request->get('weeknumber');?>">
        <input type="hidden" name="removeweeknumber" value="<?php echo $request->get('removeweeknumber');?>">
        <input type="hidden" name="action" value="saveremoveweek">
        <tr>
            <td align="right">
                <input type="button" name="js_saveRemoveweek" id="js_saveRemoveweek" value="Ok">
                <input type="button" value="Cancel" onclick="$('#facebox .close').click()">

            </td>
        </tr>
    </table>


</form>

<script>
	$(document).ready(function(){
        $('#facebox .close')
            .click($.facebox.close)
            .empty()
            .append('<img src="'
            + $.facebox.settings.closeImage
            + '" class="close_image" title="close">')
	});


</script>