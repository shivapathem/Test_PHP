<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
date_default_timezone_set('UTC');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$pdo = OpenDBLinkA7();
$allocationId = $_POST['allocationId'];
$isEdited = $_POST['isEdited'];
$parentId = $_POST['parentId'];
$isShiftleader = $_POST['isShiftleader'] ?? 0;
$intTeamID= $_POST['teamId'];
$week = $_POST['WeekNumber']?? 0;
$day = $_POST['iDay']?? 0;
if(isset($_POST['currentDate'])) {
    $curdate = $_POST['currentDate'];
} elseif (isset($_SESSION['allocattionsdate'])) {
    $curdate = $_SESSION['allocattionsdate'];
} else {
    $curdate = date("Y-m-d");
}

$arrStaffOptions = [];
if (!empty($strUser) && $UserID!=0) {
  $arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);
}
if (isset($arrStaffOptions[$intTeamID])) {
  $options = $arrStaffOptions[$intTeamID];
  $isShiftleaderVal = 2;
	if($options['isShiftLeader'] == 1)
	{
		$isShiftleaderVal = 0;
	}
}

$updatedAllocationId = ($isEdited != 1) ? $allocationId : $parentId;
if (isset($_POST['process']) &&($_POST['process']=='Assign')) {
  SwapDuty($_POST);
}
else {
  // Get the staff belong to this team and for this day
  $arrAllocations = GetStaffDetailsByDayAndTeam($week, $day, $intTeamID, $curdate, $isShiftleaderVal);
  echo '<div id="assignpage">';
  echo '<form onsubmit="return submitForm();" id="form">';
  echo '<table class="redtable" width="600px">';
  echo '<tr>';
  echo '<th colspan="2"><br>You can assign to a person who works in the Team associated with this allocation<br><br></th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>Available staff</td>';
  echo '<td>';
  echo '<select class="chosen-select" id="scheduledPerson" name="scheduledPerson">';
  foreach ($arrAllocations as $arrAlloc) {
    if(isset($arrAlloc['isEdited'])){
      $edited = $arrAlloc['isEdited'];
    }else{
      $edited = 0;
    }
    echo '<option value="'.$arrAlloc['SchedulingPersonID'].'" data-alloc-sp="' . $arrAlloc['AllocationsSPID'] . '" data-alloc-duty="'.$arrAlloc['AllocationsDutyID'].'" data-isEdited="'.$edited.'">'.$arrAlloc['FullName'].' ('. $arrAlloc['DutyName'].')'.'</option>';
  }
  echo '</select>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>&nbsp;</td>';
  echo '<td><br><input type="submit" value="Assign" id="update"  name="update"><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" id="allocationId" name="allocationId" value="'.$allocationId.'">';
  echo '<input type="hidden" id="isEdited" name="isEdited" value="'.$isEdited.'">';
  echo '<input type="hidden" id="parentId" name="parentId" value="'.$parentId.'">';
  echo '<input type="hidden" id="newAllocationId" name="newAllocationId" value="">';
  echo '<input type="hidden" id="newIsEdited" name="newIsEdited" value="">';
  echo '<input type="hidden" id="newAllocationSPID" name="newAllocationSPID" value="">';
  echo '<input type="hidden" id="isAssign" name="isAssign" value="1">';
  echo '<input type="hidden" id="isShiftLeader" name="isShiftLeader" value="'.$isShiftleader.'">';
  echo '<input type="hidden" id="teamId" name="teamId" value="'.$intTeamID.'">';
  echo '<input type="hidden" id="weekNumber" name="weekNumber" value="'.$week.'">';
  echo '<input type="hidden" id="process" name="process" value="Assign">';
  echo '</div>';
  echo '</form>';

?>
<script type="text/javascript">
$(document).ready(function() {
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "450px"
  });
});

function submitForm() {
  $('input[type="submit"]').prop('disabled', true);
  var newAllocationId = $("#scheduledPerson option:selected").attr('data-alloc-duty');
  var newIsEdited = $("#scheduledPerson option:selected").attr('data-isEdited');
  $("#newAllocationId").val(newAllocationId);
  $("#newIsEdited").val(newIsEdited);
  $("#newAllocationSPID").val($("#scheduledPerson option:selected").attr('data-alloc-sp'));
  $.ajax({
    type:'POST',
    url: 'page-includes/allocations/edits/assignduty.php',
    data:$('#form').serialize(),
    success: function(data) {
		data = JSON.parse(data);
	    if(data.SPMessage != null && data.SPExecStatus != 0) {
			  customAlert(data.SPMessage);
	    } else{
			  ShowDailyAllocations(<?php echo $intTeamID; ?>, '<?php echo $curdate ?>');
			  $.facebox.close();
		}
    }
  });
  return false;
}
function cancel() {
  $.facebox.close();
}
</script>
<?php
  echo '</div> ';
}
?>