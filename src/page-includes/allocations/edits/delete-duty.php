<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../../function-includes/helpers.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/delete-unallocated-duty-process.php';

$strUser =$_REQUEST['userName'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$dutyid = $_REQUEST['dutyid'];
$team_id = $_REQUEST['teamId'];
$weeknum = $_REQUEST['weeknum'];
$dutyName = $_REQUEST['dutyName'];
$dayName = $_REQUEST['dayName'];

$intCountJobs = 0;
$arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);
$jobinfo = getJobsCountUnderAllocationDuty($dutyid,$team_id);
$editjobinfo = getJobsCountUnderAllocationEditDuty($dutyid);
$editjobscount = $editjobinfo['jobcount'] ?? 0;
$jobCount = $jobinfo['jobcount'] ?? 0;
$intCountJobs = ($editjobscount + $jobCount);
  

/* Set shiftleader flag for rotas --START */
$isShiftleader = 0;
if (($arrStaffOptions[$team_id]['isShiftLeader'] == 1) || ($arrStaffOptions[$team_id]['isSchedulingTeamViewer'] == 1)) {
  $isShiftleader = 1;
}

if (($arrStaffOptions[$team_id]['isScheduler'] == 1) || ($arrStaffOptions[$team_id]['isTeamAdmin'] == 1)) {
  $isShiftleader = 0;
}
/* Set shiftleader flag for rotas --END */

 if (($arrStaffOptions[$team_id]['isScheduler'] == 1) || ($arrStaffOptions[$team_id]['isTeamAdmin'] == 1) ||  $intCountJobs == 0) {
 ?>
 
<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 400px; z-index: 101;" >
  <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
    <span id="ui-id-5" class="ui-dialog-title">Please Confirm</span>
  </div>
 
  <div style="width: auto; height: auto;" class="ui-dialog-content ui-widget-content">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
  Do you really wish to delete Unallocated Duty '<?php echo $dutyName; ?>' 
  <?php if ($intCountJobs>0){?>
  and any jobs attached to the duty 
  <?php } ?>
  from <?php echo $dayName; ?>?</p>
    <form name="cfrm" id="cfrm" method="post">
      <input type='hidden' value='<?php echo $team_id;?>' id="teamid" name="teamid" readonly>
      <input type='hidden' value='<?php echo $dutyid;?>' id="dutyid" name="dutyid" readonly>
      <input type='hidden' value='<?php echo $weeknum;?>' id="weeknum" name="weeknum" readonly>
      <input type='hidden' value='<?php echo $isShiftleader;?>' id="isShiftleader" name="isShiftleader" readonly>
      <input type='hidden' value='1' id="action" name="action">
    </form>
  </div>
 <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
  <div class="ui-dialog-buttonset">
    <input type="button" value="Yes" onclick="confirm()">
    <input type="button" value="No" onclick="cancel()">
 </div>
 </div>
 </div>

<script src="js/allocations/daily/deletduty.js?v=<?php echo time(); ?>"></script>
 <?php
 }
   else {   
 ?>
 <div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 400px; z-index: 101;" >
 <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
 <span id="ui-id-5" class="ui-dialog-title">Please Confirm</span>
 </div>
 
 <div style="width: auto; min-height: 0px; max-height: none; height: 58px;" class="ui-dialog-content ui-widget-content">
 
 <?php if (($arrStaffOptions[$team_id]['isShiftLeader'] == 1) && $intCountJobs > 0) { ?>
 <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can only Delete Duties that do not contain any jobs!</p>
 <?php } else { ?>
 <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete this duty? <br> Any associated Jobs will also be deleted</p>
 <?php } ?>
 
 </div>
 <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
 <div class="ui-dialog-buttonset">
 <input type="button" value="OK" onclick="cancel()">
 </div>
 </div>
 </div>
 
 <script>
 
 function cancel() {
     $.facebox.close();
 }
 </script>
 
 
 <?php
 
   }
 
 
 ?>
 


