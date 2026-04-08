<?php
date_default_timezone_set('UTC');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';


$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$allocationId = $_POST['allocationid'] ?? 0;
$requestPageName=$_POST['pagename'] ?? '';
$pdo = OpenDBLinkA7();

$dutyname =  $_POST['dutyname'] ?? '';
$week = $_POST['weekno'] ?? '';
$day =  $_POST['dayno'] ?? '';
$intTeamID = $_POST['intteamid'] ?? 0;
$curdate = $_POST['Dutydate'] ?? '';
$arrStaffOptions = [];
if (!empty($strUser) && $UserID!=0) {
  $arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);
}

/* Set shiftleader flag for rotas --START */
$isShiftleader = 1;

if (isset($arrStaffOptions[$intTeamID])) {
  $options = $arrStaffOptions[$intTeamID];
  $isShiftleaderVal = 2;
	if($options['isShiftLeader'] == 1)
	{
		$isShiftleaderVal = 0;
	}
}
/* Set shiftleader flag for rotas --END */

// Get the staff belong to this team and for this day
$arrAllocations=[];
if ((!empty($week)) && ($day!='') && ($intTeamID !=0) && (!empty($curdate))) {
  $arrAllocations = GetStaffDetailsByDayAndTeam($week, $day, $intTeamID, $curdate, $isShiftleader);
}
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
  if (!empty($arrAllocations)) {
    foreach ($arrAllocations as $arrAlloc) {
      $schedulingPersonId = $schedulingPersonId ?? null;
      if (isset($arrAlloc['SchedulingPersonID']) && ($arrAlloc['SchedulingPersonID'] != $schedulingPersonId)) {
        echo '<option value="'.$arrAlloc['SchedulingPersonID'].'" data-alloc-duty="'.$arrAlloc['AllocationsDutyID'].'">'.$arrAlloc['FullName'].' ( '.$arrAlloc['DutyName'].' )'.'</option>';
      }
    }
  }
  echo '</select>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>&nbsp;</td>';
  echo '<td><br><input type="submit" value="Assign" name="update"><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="oldAllocationId" id="oldAllocationId" value="'.$allocationId.'">';
  echo '<input type="hidden" id="swapAllocationId" name="swapAllocationId" value="">';
  echo '<input type="hidden" id="intTeamID" name="intTeamID" value="'.$intTeamID.'">';
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
;

function submitForm() {
  $('input[type="submit"]').prop('disabled', true);
  var ExistingAllocationId = $("#scheduledPerson option:selected").attr('data-alloc-duty');
  $("#swapAllocationId").val(ExistingAllocationId);
  <?php if ($requestPageName=='production') { ?>
  $.ajax({
    type:'POST',
    url: 'page-includes/allocations/edits/mark-duty-cover.php',
    data:$('#form').serialize(),
    success: function(data) {
		data = JSON.parse(data);
      if(data.errorMessage != null && data.spStatus != 1) {
        customAlert(data.errorMessage);
      } else{
        $.facebox.close();
        ShowAllocationsDuties('<?php echo $intTeamID; ?>');
      }
    }
  });
  return false;
  <?php } ?>
}
function cancel() {
  $.facebox.close();
}
</script>
<?php
echo '</div> ';
?>