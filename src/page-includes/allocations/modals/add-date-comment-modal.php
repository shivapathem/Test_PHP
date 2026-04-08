<?php

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once '../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../weekly/service/AllocationRepository.php';
$request = Request::createFromGlobals();
$date = $request->get('date');
$teamId = $request->get('teamId');
$repository = new AllocationRepository();
$existingData = $repository->getDateComment($request->get('teamId'), [$request->get('date')]);
$existingData = $existingData[0] ?? [];
?>

<form name="dateComment" id="dateComment">
	<table class="tablesmalltidy" width="100%">
		<tr height="30px">
			<th colspan="2"><strong>Date Comment for <?php echo Carbon::parse($date)->format('d/m/Y') ?></strong></th>
		</tr>
		<tr>
			<td colspan="2"><hr></td>
		</tr>
		<tr height="30px">
			<td class="padding-5"><strong>Date Comment</strong></td>
			<td>
				<textarea name="date_comment" id="date-Comment-txt" cols="45" rows="5" maxlength="500" placeholder="Please enter date Comment"><?php echo !empty($existingData) ? $existingData['Comment'] : '' ?></textarea>
				Characters Remaining : <span id="remainDateComment">500</span>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="button" value="Update" onclick="updateDateComment(<?php echo $existingData['ID'] ?? 0; ?>, '<?php echo $date; ?>', <?php echo $teamId; ?>)">
				<input type="button" value="Cancel" onclick="closeCommentPopup();">
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">
function closeCommentPopup(){
	$('#facebox .close').click();
}

$(function() {
	var datecommentmaxchars = 500;
	updateCommentMaxChars($('#date-Comment-txt'));
	$('#date-Comment-txt').keyup(function () {
		updateCommentMaxChars($(this));
	});
	function updateCommentMaxChars($textObj) {
		let tlengthduty = $textObj.val().length;
		remaindateComment = datecommentmaxchars - parseInt(tlengthduty);
		$('#remainDateComment').text(remaindateComment);
	}
});
</script>