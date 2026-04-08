<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$id = $request->get('id');
$teamId = $request->get('teamId');
$weekNumber = $request->get('weekNum');
$dataEdit = $request->get('dataEdit');
$dutyDate = $request->get('dutyDate');
$allocationsDutyId = $request->get('allocationsDutyId');
$allocationsSpId = $request->get('allocationsSpId');
$allocationsSchPer = $request->get('allocationsSchPer');
$DutyName = $request->get('DutyName') ? $request->get('DutyName') : '';
$DutyName = ($DutyName != null) ? $DutyName : '';
$oldDutyComments = $request->get('oldDutyComments') ? $request->get('oldDutyComments') : '';
$oldPersonComments = $request->get('oldPersonComments') ? $request->get('oldPersonComments') : '';
?>

<form name="allocationComments" id="allocationComments">
	<table class="tablesmalltidy" width="100%">
		<tr height="30px">
			<th colspan="2"><strong>&nbsp; Duty Comments on <?php echo date("d/m/Y", strtotime($request->get('dutyDate'))) ?> for '<?php echo htmlspecialchars($DutyName); ?>'</strong></th>
		</tr>
		<tr height="30px">
			<td class="padding-5"><strong>Duty Comments</strong></td>
			<td>
				<textarea name="DutyComments" id="DutyComments" cols="45" rows="5" maxlength="500" placeholder="Please enter Duty Comments" <?php if($dataEdit == 0){?> disabled="disabled" <?php }?>></textarea>
				Characters Remaining : <span id="remainDutyComments">500</span>
			</td>
		</tr>
		<tr><td colspan="2" style="height: 8px; border: none;"></td></tr>
		<tr height="30px">
			<th colspan="2"><strong>&nbsp; Person Comments on <?php echo date("d/m/Y", strtotime($request->get('dutyDate'))) ?> for <?php echo htmlspecialchars($request->get('scheduledPersonName'));?> </strong></th>
		</tr>
		<tr height="30px">
			<td class="padding-5"><strong>Person Comments</strong></td>
			<td>
				<textarea name="PersonComments" id="PersonComments" cols="45" rows="5" maxlength="500" placeholder="Please enter Person Comments" <?php if($dataEdit == 0){?> disabled="disabled" <?php }?>></textarea>
				Characters Remaining : <span id="remainPersonComments">500</span>
			</td>
		</tr>
		<tr>
			<th colspan="2" style="text-align: center;padding: 7px;">
				 <?php if($dataEdit == 0){?>
				 	<input type="button" value="Cancel" onclick="closeCommentPopup();">
				 <?php } else {?>
				 	<input type="button" value="Update" onclick="updateComments(<?php echo $id;?>,<?php echo $teamId;?>,<?php echo $weekNumber;?>,<?php echo $allocationsDutyId;?>,<?php echo $allocationsSpId;?>,'<?php echo $dutyDate;?>','<?php echo $allocationsSchPer;?>')">
				 	<input type="hidden" value="<?php echo htmlspecialchars($oldDutyComments); ?>" name="oldDutyComments">
				 	<input type="hidden" value="<?php echo htmlspecialchars($oldPersonComments); ?>" name="oldPersonComments">
				 <?php }?>
			</th>
		</tr>
	</table>
</form>

<script type="text/javascript">
function closeCommentPopup(){
	$('#facebox .close').click();
}

var dutymaxchars = 500;
$('#DutyComments').keyup(function () {
    var tlengthduty = $(this).val().length;
    $(this).val($(this).val().substring(0, dutymaxchars));
    var tlengthduty = $(this).val().length;
    remaindutycomments = dutymaxchars - parseInt(tlengthduty);
    $('#remainDutyComments').text(remaindutycomments);
});

var personmaxchars = 500;
$('#PersonComments').keyup(function () {
    var tlengthperson = $(this).val().length;
    $(this).val($(this).val().substring(0, personmaxchars));
    var tlengthperson = $(this).val().length;
    remainpersoncomments = personmaxchars - parseInt(tlengthperson);
    $('#remainPersonComments').text(remainpersoncomments);
});
</script>