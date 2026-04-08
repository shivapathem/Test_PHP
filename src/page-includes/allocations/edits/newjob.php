<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/master-jobs-functions.php';

$prog = getAllProgrammes();
$allProg = json_decode($prog);
$intStaffID = $_SESSION['user']['StaffID'];
if(isset($_SESSION['user']['UserID']) && $_SESSION['user']['UserID'] != ''){
    $intUserID =  $_SESSION['user']['UserID'];
}else{
    $intUserID = $_COOKIE['editWeeklyUserId'];
}
$intType = 0;
$TeamId = empty($_POST["teamId"]) ? 0 : $_POST["teamId"];
$rsAreaTeams = GetUserAreaTeamsList($intUserID);
$TeamOptions = TeamListDropDown($rsAreaTeams, $TeamId);
$allocation = $_POST['allocation'] ?? 0;
$jobId = $_POST['jobId'] ?? 0;
$WeekNumber = $_POST['WeekNumber'];
$iDay = $_POST['iDay'];
$role = empty($_POST["role"]) ? 0 : $_REQUEST["role"];
$dutystart = $_POST['dutystart']?? 0;
$duty_end = $_POST['duty_end']?? 0;
$dutyduration = $_POST['dutyduration']?? 0;

if(isset($_REQUEST['scheduledpersonid'])){
    $scheduledpersonid = $_REQUEST['scheduledpersonid'] ??'';
}else{
    $scheduledpersonid = NULL;
}
if(isset($_REQUEST['unallocated_job'])){
    $unallocated = $_REQUEST['unallocated_job'] ??'';
}else{
    $unallocated = 0;
}
if (isset($_REQUEST['aftermidnight'])) {
    $aftermidnight = 1;
  } else {
    $aftermidnight = 0;
  }
  if(isset($_REQUEST['unallocated_job'])){
    $unallocated = $_REQUEST['unallocated_job'] ??'';
}else{
    $unallocated = 0;
}
?>
<script src="js/allocations/daily/newjob.js?v=<?php echo time(); ?>"></script>
<div class="popup">
    <div class="content">
        <?php echo '<div style="width: 600px">'; ?>
        <form id="newjob" method="post">
            <table id="jobnewedittable" class="smalltable bluetable" style="width:100%;">
                <thead>
				<tr id ="bugtd">
                <td colspan="5" class ="messageerror error" align="center" ></td>
                </tr>
                <tr>
                    <th colspan="5" id="th1">New Job</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="lightblue">Job Name<span class="required">*</span></td>
                    <td colspan="3"><input id="Job" name="Job" type="text" size="50" value=""></td>
                </tr>
                <tr>
                    <td>Start Time<span class="required">*</span></td>
                    <td><input onchange="setStartTime();" id="StartTime" name="StartTime" type="text"
                               class=" ui-timepicker-input" size="10" value="0" autocomplete="off"></td>
                    <td>End Time<span class="required">*</span></td>
                    <td><input onchange="setEndTime();" id="EndTime" name="EndTime" type="text"
                               class="time ui-timepicker-input" size="10" value="0" autocomplete="off"></td>
                </tr>
                <tr>
                    <td>Scheduling Team</td>
                    <td colspan="3">
                        <?php echo '   <select class="chosen-select bg-white chosen-selectMaxWidth" name="scheduling_team" id="selteam">';
                        echo '' . $TeamOptions . ''; ?></td>
                </tr>
                <tr>
                    <td>Info</td>
                    <td colspan="3">
                        <textarea rows="2" maxlength="500" name="info" cols="35"></textarea></td>
                </tr>
                <tr>
                    <td class="lightblue">Background Colour</td>
                    <td><input id="jobbackcolor" name="backcolor" type="text" size="10" value=""></td>
                    <td class="lightblue">Font Colour</td>
                    <td><input id="jobforecolor" name="forecolor" type="text" size="10" value=""></td>

                </tr>
                <tr>
                    <td>Label</td>
                    <td colspan="3">
                        <select name="programme_id" id="JobType">
                            <option value="0">Select Programme</option>
                            <?php foreach ($allProg as $key => $value) { ?>
                                <option value="<?php echo $value->ID; ?>"><?php echo $value->Programme; ?></option>
                            <?php } ?>
                        </select></td>
                </tr>
                <tr>
                    <td>Contact</td>
                    <td colspan="3">
                        <input id="contact" maxlength="50" name="contact" type="text" size="50" value=""
                               class="ui-autocomplete-input" autocomplete="off"></td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td colspan="3">
                        <input id="location" maxlength="50" name="location" type="text" size="50" value=""
                               class="ui-autocomplete-input" autocomplete="off"></td>
                </tr>
                <?php
                if($allocation>0) { ?>
					<tr>
						<td>Create as unallocated</td>
						<td colspan="3">
						<?php if($allocation<=0){$unallocated=1;}else{$unallocated=0;}?>
						<input type="checkbox" name="unallocated" id="unallocated" value="1" <?php if($unallocated){ echo 'checked';} ?>></td>
					</tr>
                <?php } else {  ?>
					<tr>
						<td>Starts after midnight</td>
						<td colspan="3"><input type="checkbox" name="aftermidnight"  id="aftermidnight" value="1" 
						<?php  if ($aftermidnight == 1) {   echo ' checked'; }  ?> >
						    <input type="hidden" name="unallocated" id="unallocated" value="<?php echo $unallocated; ?>">
						</td>
					</tr>
				<?php } ?>
                     <input type="hidden" name="allocationID" id="allocationID" value="<?php echo $allocation;?>">
                     <input type="hidden" name="jobId" id="jobId" value="<?php echo $jobId;?>">
                     <input type="hidden" name="WeekNumber" id="WeekNumber" value="<?php echo $WeekNumber;?>">
                     <input type="hidden" name="iDay" id="iDay" value="<?php echo $iDay;?>">
                     <input type="hidden" name="isEdited" id="isEdited" value="<?php  if(isset($isEdited)){ echo $isEdited;}else{echo 0;} ?>">
                     <input type="hidden" name="scheduledpersonid" id="scheduledpersonid" value="<?php echo $scheduledpersonid;?>">
                     <input type="hidden" name="role" id="role" value="<?php echo $role; ?>">
					  <input type="hidden" name="dutystart" id="dutystart" value="<?php echo $dutystart; ?>">
					 <input type="hidden" name="duty_end" id="duty_end" value="<?php echo $duty_end; ?>">
					 <input type="hidden" name="dutyduration" id="dutyduration" value="<?php echo $dutyduration; ?>">
                     <input type="hidden" name="dutyDate" id="dutyDate" value="<?php echo $_POST['dutyDate']; ?>">
                     <input type="hidden" name="dutyId" id="dutyId" value="<?php echo $_POST['dutyId'] ?? ''; ?>">
                     <tr>
                    <td> <td>
                    <td colspan="3">
                        <input name="submit" type="submit" value="Create Job"></td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>




