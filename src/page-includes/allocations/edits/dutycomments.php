<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
require_once __DIR__ . '/../weekly/service/AllocationService.php';

use Symfony\Component\HttpFoundation\Request;

$pdo = OpenDBLinkA7();
// Fetch duty comments
$allocationId = $_REQUEST['dutyId'];
$isEdited = $_REQUEST['isEdited'];
$parentAllocId = $_REQUEST['parentId'];
$intTeamId = isset($_REQUEST['teamId']) ? $_REQUEST['teamId'] : null;
$query = "SELECT ad.AD_DutyName as DutyName, ad.AD_Comments as DutyComments, asp.ASP_Comments as PersonComments, asp.ASP_AllocationsSPID as AllocationsSpId, al.AL_SchedulingTeamID as SchedulingTeamId,
          asp.ASP_SchedulingPersonID as schedulingPersonID
          FROM  AllocationsDuties ad (Nolock)
          INNER JOIN AllocationsScheduledPersons as asp on asp.ASP_AllocationsDutyID = ad.AD_AllocationsDutyID
          INNER JOIN Allocations as al on al.AL_AllocationsID = ad.AD_AllocationsID
          WHERE ad.AD_AllocationsDutyID = :updatedAllocationId";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':updatedAllocationId', $allocationId, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$schedulingPersonId = isset($_REQUEST['schedulingPersonId']) ? $_REQUEST['schedulingPersonId'] : null;
if ($schedulingPersonId !== null) {
    $personDetails = GetScheduledPersonDetailsById($schedulingPersonId);
} else {
    $personDetails = null;
}
$dutyname = $row['DutyName'];
$dutycomments = $row['DutyComments'];
$personcomments = $row['PersonComments'];
$allocationsSpId = $row['AllocationsSpId'];
$iniDailyDutyComments = strlen($dutycomments ?? '');
$remainingDutyComments = (500 - $iniDailyDutyComments);
$iniDailyPersonComments = strlen($personcomments ?? '');
$remainingPersonComments = (500 - $iniDailyPersonComments);

if (!isset($_REQUEST['submit'])) {
  echo '<form id="dutycomments">';
  echo '<table class="tablesmalltidy dutyCommentTable" width="100%">';
  echo '<tr height="30px">';
  echo '<th colspan="2"><strong>&nbsp; Duty Comments on ' . date("d/m/Y", strtotime($_REQUEST['dutyDate'])) . ' for \'' . htmlspecialchars($_REQUEST['dutyName']) . '\'</strong></th>';
  echo '</tr>';
  echo '<tr height="30px">';
  echo '<td class="padding-5"><strong>Duty Comments</strong></td>';
  echo '<td>';
  echo '<textarea name="dutycomments" id="DailyDutyComments" cols="45" rows="5" maxlength="500" placeholder="Please enter Duty Comments">'.$dutycomments.'</textarea><br>';
  echo 'Characters Remaining : <span id="remainDutyComments">'.$remainingDutyComments.'</span>';
  echo '</td>';
  echo '</tr>';
  echo '<tr><td colspan="2" style="height: 8px; border: none;"></td></tr>';
  echo '<tr height="30px">';
  echo '<th colspan="2"><strong>&nbsp; Person Comments on ' . date("d/m/Y", strtotime($_REQUEST['dutyDate'])) . ' for ' . htmlspecialchars($personDetails['FullName']) . '</strong></th>';
  echo '</tr>';
  echo '<tr height="30px">';
  echo '<td class="padding-5"><strong>Person Comments</strong></td>';
  echo '<td>';
  echo '<textarea name="personcomments" id="DailyPersonComments" cols="45" rows="5" maxlength="500" placeholder="Please enter Person Comments">'.$personcomments.'</textarea><br>';
  echo 'Characters Remaining : <span id="remainPersonComments">'.$remainingPersonComments.'</span>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<th colspan="2" style="text-align: center;padding: 7px;"><input name="submit" type="submit" value="Update" /></th>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="dutyId" value="'.$allocationId.'">';
  echo '<input type="hidden" name="allocationsDutyId" value="'.$allocationId.'">';
  echo '<input type="hidden" name="allocationsSpId" value="'.$allocationsSpId.'">';
  echo '<input type="hidden" name="isEdited" value="'.$isEdited.'">';
  echo '<input type="hidden" name="parentId" value="'.$parentAllocId.'">';
  echo '<input type="hidden" name="team" value="'.$intTeamId.'">';
  echo '<input type="hidden" name="isShiftLeader" value="'.$_POST['isShiftLeader'].'">';
  echo '<input type="hidden" name="schedulingPersonId" value="'. $schedulingPersonId .'">';
  echo '<input type="hidden" name="dutyDate" value="'. $_REQUEST['dutyDate'] .'">';
  echo '</form>';
?>

<script type="text/javascript">
$('document').ready(function(){
  $('#dutycomments').validate({
      submitHandler: function(form) {
      $.ajax({
        type:'POST',
        url: 'page-includes/allocations/edits/dutycomments.php',
        data:$('#dutycomments').serialize(),
        success: function(data) {
          $.facebox.close();
          ShowDailyAllocations (<?php echo $intTeamId?>, $('#strCurrentDate').val());
        }
      });
    }
  })
});

function cancel() {
  $.facebox.close();
}

var dutymaxchars = 500;
$('#DailyDutyComments').keyup(function () {
    var tlengthduty = $(this).val().length;
    $(this).val($(this).val().substring(0, dutymaxchars));
    var tlengthduty = $(this).val().length;
    remaindutycomments = dutymaxchars - parseInt(tlengthduty);
    $('#remainDutyComments').text(remaindutycomments);
});

var personmaxchars = 500;
$('#DailyPersonComments').keyup(function () {
    var tlengthperson = $(this).val().length;
    $(this).val($(this).val().substring(0, personmaxchars));
    var tlengthperson = $(this).val().length;
    remainpersoncomments = personmaxchars - parseInt(tlengthperson);
    $('#remainPersonComments').text(remainpersoncomments);
});
</script>
<?php

}
else {
  $request = Request::createFromGlobals();
  $service = new AllocationService();

  $updatedAllocationDutyId = escapeSingleQuotes($_POST['dutyId']);
  $dutycomments = escapeSingleQuotes($_POST['dutycomments']);
  $personcomments = escapeSingleQuotes($_POST['personcomments']);
  $isShiftLeader = (isset($_POST['isShiftLeader']) && !empty($_POST['isShiftLeader'])) ? $_POST['isShiftLeader'] : 0;
  //setting request data
  $request->request->set('id', $_POST['parentId']);
  $request->request->set('DutyComments', $dutycomments);
  $request->request->set('PersonComments', $personcomments);
  $request->request->set('isShiftLeader', $isShiftLeader);
  $request->request->set('allocationsDutyId', $updatedAllocationDutyId);
  $request->request->set('allocationsSpId', $_POST['allocationsSpId']);
  $request->request->set('allocationsSchPer', $_POST['schedulingPersonId']);
  $request->request->set('dutyDate', $_POST['dutyDate']);
	if((!empty($request->get('DutyComments')) || ($request->get('DutyComments') != $row['DutyComments'])) && (!empty($request->get('PersonComments')) || ($request->get('PersonComments') != $row['PersonComments'])))
	{
		$request->request->set('pCommentType', 0);
	}elseif(!empty($request->get('DutyComments')) || ($request->get('DutyComments') != $row['DutyComments']) )
	{
		$request->request->set('pCommentType', 1);
	}elseif(!empty($request->get('PersonComments')) || ($request->get('PersonComments') != $row['PersonComments']))
	{
		$request->request->set('pCommentType', 2);
	}
  $add = $service->addCommentsToAllocations($request);
  if ($add) {
    $response['success'] = true;
  } else {
    $response['success'] = false;
  }
  echo json_encode($response);
}