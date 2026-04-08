<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/genericfunctions.php';

include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';
$prog = getAllProgrammes();
$allProg = json_decode($prog);

$duty = getMasterDutiesByID($_REQUEST['dutyID']);
$mduty = json_decode($duty);
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'];
$intType = 0;
$intTeamID = $mduty->TeamID;
$rsAreaTeams = GetUserAreaTeamsList($intUserID);
$TeamOptions = TeamListDropDown($rsAreaTeams, $intTeamID);
?>
<div class="popup">
    <div class="content">
        <?php echo '<div style="width: 600px">'; ?>
        <form id="newjob" method="post">
            <input type="hidden" id="duty_id" name="master_duty_id" value="<?php echo $_REQUEST['dutyID']; ?>">
            <input type="hidden" id="duty_starttime" name="duty_starttime" value="<?php echo $mduty->StartTime; ?>">
            <input type="hidden" id="duty_endtime" name="duty_endtime" value="<?php echo $mduty->EndTime; ?>">
            <table id="jobnewedittable" class="smalltable bluetable" role="presentation">
                <thead>
                <tr>
                    <th colspan="5">Add New Job...</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="lightblue">Job Name</td>
                    <td colspan="3"><input id="Job" name="Job" type="text" size="50" value="" auto-focus="0"></td>
                </tr>
                <tr>
                    <td>Start Time</td>
                    <td><input id="StartTime" name="StartTime" type="text" maxlength="5"
                               class="ui-timepicker-input" size="10" value="00:00" onChange="jobsTimeSanitization(StartTime.value, 'StartTime');" autocomplete="off"></td>
                    <td>End Time</td>
                    <td><input id="EndTime" name="EndTime" type="text" maxlength="5"
                               class="ui-timepicker-input" size="10" value="00:00" onChange="jobsTimeSanitization(EndTime.value, 'EndTime');" autocomplete="off"></td>
                </tr>
                <tr>
                    <td>Scheduling Team</td>
                    <td colspan="3">
                        <?php echo '   <select disabled class="chosen-select chosen-selectMaxWidth" name="scheduling_team_show" style="background-color:white; width: 100%;">';
                            echo  $TeamOptions; 
                            echo '</select>';
                        ?>
                    </td>
                <input type="hidden" id="schedule_team" value="<?php echo $intTeamID;?>" name="scheduling_team">
                </tr>
                <tr>
                    <td>Info</td>
                    <td colspan="3">
                        <textarea rows="2" maxlength="500" id="info" name="info" cols="35"></textarea></td>
                </tr>
                <tr>
                    <td class="lightblue">Background Colour</td>
                    <td><input id="backcolor" name="backcolor" type="text" size="10" value="#fff"></td>
                    <td class="lightblue">Font Colour</td>
                    <td><input id="forecolor" name="forecolor" type="text" size="10" value="#000"></td>

                </tr>
                <tr hidden>
                    <td class="lightblue">Default Colour</td>
                    <td><input id="defaultcolor" name="defaultcolor" type="text" size="10" value="#000"></td>
                </tr>
                <tr>
                    <td>Label</td>
                    <td colspan="3">
                        <select name="programme_id" id="programme_id">
                        <option value="0">Select Label</option>
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

                <tr>
                    <td></td>
                    <td colspan="3">
                        <input name="submit" type="submit" value="Submit"></td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript" src="../../js/add-job-from-duty.js?v=<?php echo time(); ?>"></script>
