<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
use Symfony\Component\HttpFoundation\Request;

include_once '../../../function-includes/Helpers.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/master-jobs-functions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../page-includes/allocations/weekly/service/AllocationRepository.php';

$repository = new AllocationRepository();
$JobID = $_POST['jobId'] ?? 0;
$role = empty($_POST["role"]) ? 0 : $_POST["role"];
$prog = getAllProgrammes();
$allProg = json_decode($prog);
$intStaffID = $_SESSION['user']['StaffID'];

if(isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID'])){
    $intUserID =  $_SESSION['user']['UserID'];
}else{
    $intUserID = $_COOKIE['editWeeklyUserId'];
}

$intType = 0;
$TeamId = $_POST['teamId'] ?? 0;
if(isset($_POST['unallocated_job'])){
    $unallocated = $_POST['unallocated_job'] ??'';
}else{
    $unallocated = 0;
}
$jobData = fetchEditedUnAllocationJob($JobID,$TeamId);
$rsAreaTeams = GetUserAreaTeamsList($intUserID);
$TeamOptions = TeamListDropDown($rsAreaTeams, $TeamId);
$WeekNumber = $jobData["WeekNumber"];
$iDay = $jobData['iDay'];
$dutystart = $_POST['dutystart'] ?? 0;
$duty_end = $_POST['duty_end'] ?? 0;
$dutyduration = $_POST['dutyduration'] ?? 0;
$AllocationID = $jobData['AllocationID'];
$SchedulingPersonID = $jobData['SchedulingPersonID']?? 0;
$starttime = $jobData['StartTime'];
if($jobData['EndTime'] > 86400) {
    $jobData['EndTime'] = $jobData['EndTime']-86400;
}
$endtime = $jobData['EndTime'];
$intstartHour = intval($starttime / 3600);
$intstartHour = strlen(trim($intstartHour))== 1 ? "0".$intstartHour : $intstartHour;
$intstartMinute = intval(($starttime % 3600) / 60);
$intstartMinute = strlen(trim($intstartMinute))== 1 ? "0".$intstartMinute : $intstartMinute;
$intstartTime   =   $intstartHour . ':' . $intstartMinute;

$intendHour = intval($endtime / 3600);
$intendHour = strlen(trim($intendHour))== 1 ? "0".$intendHour : $intendHour;
$intendMinute = intval(($endtime % 3600) / 60);
$intendMinute = strlen(trim($intendMinute))== 1 ?  "0".$intendMinute : $intendMinute;
$intendTime   =   $intendHour . ':' . $intendMinute;
$aftermidnight = $_REQUEST['midnightJob'] ?? 0;

if ($jobData['JobBackColour'] == "") {
    $backgroundcolor = "#fff";
} else {
    $backgroundcolor = $jobData['JobBackColour'];
}

if ($jobData['JobFontColour'] == "") {
$fontcolor = "#000";
} else {
$fontcolor = $jobData['JobFontColour'];
}
?>
<script src="js/allocations/daily/editJob.js?v=<?php echo time(); ?>"></script>
<div class="popup">
    <div class="content">
    <?php
        echo '<div style="width: 756px">';
    ?>
        <form id="editjob" method="post">
            <table id="jobnewedittable" class="smalltable bluetable">
                <thead>
				<tr id ="bugtd">
                <td colspan="5" class ="messageerror error" align="center" ></td>
                </tr>
                <tr>
                    <th colspan="5" id="th1">Edit Job </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="lightblue">Job Name<span class="required">*</span></td>
                    <td colspan="3"><input id="Job" name="Job" type="text" size="50" value="<?php echo $jobData['JobName'];?>"></td>
                </tr>
                <tr>
                    <td>Start Time<span class="required">*</span></td>
                    <td><input onchange="setStartTime();" id="StartTime" name="StartTime" type="text"
                               class=" ui-timepicker-input" size="10" value="<?php echo $intstartTime;?>" autocomplete="off"></td>
                    <td>End Time<span class="required">*</span></td>
                    <td><input onchange="setEndTime();" id="EndTime" name="EndTime" type="text"
                               class="time ui-timepicker-input" size="10" value="<?php echo $intendTime;?>" autocomplete="off">
                    </td>
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
                        <textarea rows="2" maxlength="500" name="info" cols="35"><?php echo $jobData['Job_Info'];?></textarea></td>
                </tr>

                <tr>
                    <td class="lightblue">Background Colour
                    </td>

                    <td>
                        <input id="jobbackcolor" name="backcolor" type="text" size="10" value="<?php echo $backgroundcolor;?>">

                    </td>

                    <td class="lightblue">Font Colour </td>
                    <td>
                    <input id="jobforecolor" name="forecolor" type="text" size="10" value="<?php echo $fontcolor;?>">


                </td>

                </tr>
                <tr>
                    <td>Programme</td>
                    <td colspan="3">
                        <select name="programme_id" id="JobType">
                            <option value="0">Select Programme</option>
                            <?php foreach ($allProg as $key => $value) {

                              ?>
                                <option value="<?php echo $value->ID; ?>"  <?php
                                if($jobData['ProgrammeId']!=Null || $jobData['ProgrammeId']!=0) {
                                if($jobData['ProgrammeId']== $value->ID) {
                                  echo 'selected';
                                }
                                 }
                             ?>
                              ><?php echo $value->Programme; ?></option>
                            <?php } ?>
                        </select></td>
                </tr>
                <tr>
                    <td>Contact</td>
                    <td>
                        <input id="contact" maxlength="50" name="contact" type="text" size="35" value="<?php echo $jobData['Contact'];?>"
                               class="ui-autocomplete-input" autocomplete="off"></td>
                    <td>Location</td>
                    <td>
                        <input id="location" maxlength="50" name="location" type="text" size="35" value="<?php echo $jobData['Location'];?>"
                               class="ui-autocomplete-input" autocomplete="off"></td>
                </tr>
                <?php if(isset($_POST['dutyid']) && !empty($_POST['dutyid'])) {
                    $request = Request::createFromGlobals();
                    $request->request->set('allocationsDutyId', $_POST['dutyid']);
                    $request->request->set('allocationsSpId', $_POST['allocationSpid']);
                    $request->request->set('id', $_POST['dutyParentid']);
                    $comments = $repository->getAllocationComments($request);
                ?>
                <tr>
                    <td class="padding-5">Duty Comments</td>
                    <td>
                        <textarea name="DutyComments" id="DutyCommentsEditDuty" cols="35" rows="5" maxlength="500" placeholder="Please enter Duty Comments" ><?php echo htmlspecialchars($comments['DutyComments'] ?? ''); ?></textarea>
                        Characters Remaining : <span id="remainDutyCommentsEditDuty">500</span>
                    </td>
                    <td class="padding-5">Person Comments</td>
                    <td>
                        <textarea <?php echo empty($_POST['scheduledPersonId']) ? 'disabled' : ''; ?> name="PersonComments" id="PersonCommentsEditDuty" cols="35" rows="5" maxlength="500" <?php if(!empty($_POST['scheduledPersonId'])) { ?> placeholder="Please enter Person Comments" <?php } ?>><?php echo htmlspecialchars($comments['PersonComments'] ?? ''); ?></textarea>
                        <?php if(!empty($_POST['scheduledPersonId'])) { ?> Characters Remaining : <span id="remainPersonCommentsEditDuty">500</span> <?php } ?>
                    </td>
                </tr>
                <?php } ?>
                <?php if($unallocated==1) { ?>
                <tr>
						<td>Starts after midnight</td>
						<td colspan="3"><input type="checkbox" name="aftermidnight"  id="aftermidnight" value="1"
						<?php  if ($aftermidnight == 1) {   echo ' checked'; }  ?> >
						</td>
				</tr>
                <?php } ?>	
                     <input type="hidden" name="JobID" id="JobID" value="<?php echo $JobID;?>">
                     <input type="hidden" name="WeekNumber" id="WeekNumber" value="<?php echo $WeekNumber;?>">
                     <input type="hidden" name="iDay" id="iDay" value="<?php echo $iDay;?>">
                     <input type="hidden" name="allocationID" id="allocationID" value="<?php echo $AllocationID;?>">
                     <input type="hidden" name="scheduledpersonid" id="scheduledpersonid" value="<?php echo $SchedulingPersonID;?>">
                     <input type="hidden" name="dutyId" id="dutyIdJob" value="<?php echo $_POST['dutyid'] ?? 0;?>">
                     <input type="hidden" name="role" id="role" value="<?php echo $role; ?>">
					 <input type="hidden" name="dutystart" id="dutystart" value="<?php echo $dutystart; ?>">
					 <input type="hidden" name="duty_end" id="duty_end" value="<?php echo $duty_end; ?>">
					 <input type="hidden" name="dutyduration" id="dutyduration" value="<?php echo $dutyduration; ?>">
					 <input type="hidden" name="unallocated" id="unallocated" value="<?php echo $unallocated; ?>">
                     <input type="hidden" name="dutyDate" id="dutyDate" value="<?php echo $jobData['DutyDate'] ; ?>">
                     <input type="hidden" value="<?php echo htmlspecialchars($comments['DutyComments'] ?? ''); ?>" id="oldDutyComments" name="oldDutyComments">
                     <input type="hidden" value="<?php echo htmlspecialchars($comments['PersonComments'] ?? ''); ?>" id="oldPersonComments" name="oldPersonComments">
                     <tr>
                    <td> <td>
                    <td colspan="3">
                        <input name="submit" type="submit" value="Update Job"></td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
</div>
<script>

    var maxcharsComments = 500;
    $(function() {
        $('#DutyCommentsEditDuty').keyup(function () {
            commentsTextCount('remainDutyCommentsEditDuty', $(this));
        });
        $('#PersonCommentsEditDuty').keyup(function () {
            commentsTextCount('remainPersonCommentsEditDuty', $(this));
        });
        commentsTextCount('remainDutyCommentsEditDuty',  $('#DutyCommentsEditDuty'));
        commentsTextCount('remainPersonCommentsEditDuty', $('#PersonCommentsEditDuty'));
    });

    function commentsTextCount(textId, $textArea) {
        if($($textArea).length == 0) {
            return;
        }
        var tlength = $($textArea).val().length;
        $($textArea).val($($textArea).val().substring(0, maxcharsComments));
        var tlength = $($textArea).val().length;
        remaincomments = maxcharsComments - parseInt(tlength);
        $('#' + textId).text(remaincomments);
    }
</script>