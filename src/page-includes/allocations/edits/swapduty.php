<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
date_default_timezone_set('UTC');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';

$allocationId = $_REQUEST['allocationId'];
$isEdited = $_REQUEST['isEdited'];
$parentId = $_REQUEST['parentId'];
if(isset($_REQUEST['currentDate'])) {
    $strCurrentDate = $_REQUEST['currentDate'];
} elseif (isset($_SESSION['allocattionsdate'])) {
    $strCurrentDate = $_SESSION['allocattionsdate'];
} else {
    $strCurrentDate = date("Y-m-d");
}


$updatedAllocationId = ($isEdited != 1) ? $allocationId : $parentId;

if (!isset($_REQUEST['scheduledPerson'])) {
  echo '<div id="page">';
  // Before the form is submitted
  // Get the allocation details
  $arr = GetScheduledPersonDetailsById($_REQUEST['scheduledPersonId']);
  $fullname = $arr['FullName'] ?? '';
  $dutyname = $_REQUEST['dutyName'] ?? '';
  $week = $_REQUEST['weekNumber'] ?? null;
  $day = $_REQUEST['iday'] ?? null;
  $intTeamID = $_REQUEST['teamId'] ?? null;
  $schedulingPersonId = $_REQUEST['scheduledPersonId'] ?? null;
  $curdate = $strCurrentDate ?? null;
  $isShiftleader = $_REQUEST['isShiftleader'] ?? 0;

  // Get the staff belong to this team and for this day
  $arrAllocations = GetStaffDetailsByDayAndTeam($week, $day, $intTeamID, $curdate);
  echo '<form onsubmit="return submitForm();" id="form">';
  echo '<table class="redtable" width="600px">';
  echo '<tr>';
  echo '<th colspan="2"><br>';
  echo 'You are swapping \''.$dutyname.'\' assigned to '.$fullname.'.';
  echo '<br><br></th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>Available staff</td>';
  echo '<td>';
  echo '<select class="chosen-select" id="scheduledPerson" name="scheduledPerson">';
  foreach ($arrAllocations as $arrAlloc) {
    if ($arrAlloc['SchedulingPersonID'] != $schedulingPersonId) {
      if(isset($arrAlloc['isEdited'])){
        $edited = $arrAlloc['isEdited'];
      }else{
        $edited = 0;
      }
      echo '<option value="'.$arrAlloc['SchedulingPersonID'].'" data-alloc-sp="' . $arrAlloc['AllocationsSPID'] . '" data-alloc-duty="'.$arrAlloc['AllocationsDutyID'].'" data-isEdited="'.$edited.'">'.$arrAlloc['FullName'].' ('. $arrAlloc['DutyName'].')'.'</option>';
    }
  }
  echo '</select>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>&nbsp;</td>';
  echo '<td class="lightcell smalltext"><input type="submit" value="Swap" name="update">&nbsp;&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" id="swapAllocationId" name="allocationId" value="'.$allocationId.'">';
  echo '<input type="hidden" name="isEdited" value="'.$isEdited.'">';
  echo '<input type="hidden" name="parentId" value="'.$parentId.'">';
  echo '<input type="hidden" name="teamId" value="'.$intTeamID.'">';
  echo '<input type="hidden" id="newAllocationId" name="newAllocationId" value="">';
  echo '<input type="hidden" id="newAllocationSPID" name="newAllocationSPID" value="">';
  echo '<input type="hidden" id="newIsEdited" name="newIsEdited" value="">';
  echo '<input type="hidden" id="isAssign" name="isAssign" value="0">';
  echo '<input type="hidden" id="isShiftLeader" name="isShiftLeader" value="'.$isShiftleader.'">';
  echo '<input type="hidden" id="weekNumber" name="weekNumber" value="'.$week.'">';
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
  if($('#swapAllocationId').val() == 0) {
    customAlert('Cannot assign the duty to this Scheduled Person through this process. Use Assign Option.');
    return false;
  }
  $('input[type="submit"]').prop('disabled', true);
  var newAllocationId = $("#scheduledPerson option:selected").attr('data-alloc-duty');
  var newIsEdited = $("#scheduledPerson option:selected").attr('data-isEdited');
  var newAllocationSPID = $("#scheduledPerson option:selected").attr('data-alloc-sp');

  $("#newAllocationId").val(newAllocationId);
  $("#newIsEdited").val(newIsEdited);
  $("#newAllocationSPID").val(newAllocationSPID);
  $.ajax({
    type:'POST',
    url: 'page-includes/allocations/edits/swapduty.php',
    data: $('#form').serialize(),
    success: function(data) {
		data = JSON.parse(data);
	    if(data.SPExecStatus != 0) {
			  customAlert(data.SPMessage);
      } else{
	      ShowDailyAllocations('<?php echo $intTeamID; ?>', '<?php echo $strCurrentDate ?>');
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
}
else {
  // Form being submitted
  $swap = SwapDuty($_POST);
  return $swap;
}

?>