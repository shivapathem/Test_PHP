<?php
require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/PublishWeekService.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$request = Request::createFromGlobals();
$weekNumberInputArray=$weekNumberInputVal='';
$weekNumber = $request->get('getWeekNumber');
$weekNumberInputArray = explode("/",$request->get('weekNumber', $weekNumber));
$weekNumberInputVal =$weekNumberInputArray[1].$weekNumberInputArray[0];

$request = Request::createFromGlobals();
$request->request->set('userId', $userId);
$request->request->set('startWeek', $weekNumberInputVal);

$publishWeekService = new PublishWeekService($request);
$publishedRecord = $publishWeekService->getPubslishRecord();
$updateDate = new Carbon(isset($publishedRecord['UpdatedDate'])?$publishedRecord['UpdatedDate']:'');

$week = substr($weekNumber, 4, 2);
$year = substr($weekNumber, 0, 4);
$weekNum = sprintf('%s/%s', $week, $year);
?>

<form name="publishWeek" id="publishWeekForm">

<table class="tablesmalltidy" width="100%">
	<tr height="30px">
		<th colspan="2">
			<b>Publish Week<b>
			<a href="javascript:void(0)" onclick="showPublishHistory()" style="position: absolute;right: 28px;"> <i class="fa fa-hourglass-3" id="circle-clr"></i></a>
		</th>
	</tr>
	<tr>
		<td colspan="2">You are about to publish/delete week <?php echo $request->get('weekNumber', $weekNumber); ?></td>
	</tr>
	<tr>
		<td colspan="2">Please choose an option</td>
	</tr>
	<?php if (isset($publishedRecord['Status']) && $publishedRecord['Status'] == 1) : ?> 
        <tr>
            <td colspan="2">This week was last published on <?php echo $updateDate->format('jS F Y H:i'); ?> by <?php echo $publishedRecord['DisplayName']; ?></td>
        </tr>

    <?php else: ?>
        <tr>
            <td colspan="2">This week has not been published yet.</td>
        </tr>
    <?php endif; ?>

    <tr>
		<td colspan="2"><hr></td>
	</tr>

    <tr>
		<td>Send All</td>
		<td><input type="radio" name="send" id="allPublishRecord" value="1" checked></td>
	</tr>
    <tr>
		<td>Delete Published Allocation</td>
		<td><input type="radio" name="send" id="delPublishRecord" value="0"></td>
	</tr>
    <tr>
		<td>Include Future Ad Hoc Duties</td>
		<td><input type="checkbox" name="adhocDuties" id="adhocDuties"></td>
	</tr>
    <!--tr>
		<td>Enable Full Publish (Uncheck for Incremental)</td>
		<td><input type="checkbox" name="isIncrementalPublish" id="isIncrementalPublish"></td>
	</tr-->


    <input type="hidden" name="schedulingTeamId" value="<?php echo $request->get('teamId'); ?>">
    <input type="hidden" name="userId" value="<?php echo $request->get('userId'); ?>">
    <input type="hidden" name="weekNumber" value="<?php echo $weekNumberInputVal; ?>">
	<tr>
		<td align="left"></td>
		<td align="right">
			<input type="button" value="OK" id="publishWeek">
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
		+ '" class="close_image" title="close">');

		$( "#adhocDuties" ).prop( "checked", true );
	});

	$('#delPublishRecord').on('click', function (e) {
        $('#adhocDuties').attr('disabled',true);
        //$('#isIncrementalPublish').attr('disabled',true);
    });

    $('#allPublishRecord').on('click', function (e) {
        $('#adhocDuties').attr('disabled',false);
        //$('#isIncrementalPublish').attr('disabled',false);
    });
function showPublishHistory()
{
	let teamId = $('#searchTeamId').val();
	let weekNo = $('#weekNumber').val();
	let weekNoArr = weekNo.split("/");
	let weekNoFinal = weekNoArr[1] + weekNoArr[0];
	$.ajax({
		type: 'POST',
		url: 'function-includes/common/common.php',
		data: {
            action: 'showPublishHistory',
			teamId: teamId,
			weekNo: weekNoFinal
		},
		success: function (data) {
			let dialog = $(data).dialog({
				width: 500,
				  height: 400,
				  overflow: 'hidden',
				buttons: {
					"Close": function (){
						dialog.dialog('close');
                        $("#CustomDivForModal").css("display", "none");
                        $(".ui-dialog-content").dialog("close");
					}
				}
			})
		}
	});
}
</script>