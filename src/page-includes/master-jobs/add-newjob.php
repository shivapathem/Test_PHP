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
$intStaffID = $_SESSION['user']['StaffID'] ? $_SESSION['user']['StaffID'] : '';
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'] ? $_SESSION['user']['AreaID'] : '';
$intType = 0;
$ddlRotaTeams = empty($_REQUEST["ddlRotaTeams"]) ? 0 : $_REQUEST["ddlRotaTeams"];
$rsAreaTeams = GetUserAreaTeamsList($intUserID);
$TeamOptions = TeamListDropDown($rsAreaTeams, $ddlRotaTeams);
?>
<div class="popup">
    <div class="content">
        <?php echo '<div style="width: 600px">'; ?>
        <form id="newjob" method="post">
            <table id="jobnewedittable" class="smalltable bluetable" width="100%" role="presentation">
                <thead>
                <tr>
                    <th colspan="5">New Master Job</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="lightblue">Job Name<span class="required">*</span></td>
                    <td colspan="3"><input id="Job" name="Job" type="text" maxlength="50" size="50" value=""></td>
                </tr>
                <tr>
                    <td>Start Time<span class="required">*</span></td>
                    <td><input id="StartTime" name="StartTime" type="text" maxlength="5"
                               class=" ui-timepicker-input" size="10" value="00:00" onChange="jobsTimeSanitization(StartTime.value, 'StartTime');" autocomplete="off"></td>
                    <td>End Time<span class="required">*</span></td>
                    <td><input id="EndTime" name="EndTime" type="text" maxlength="5"
                               class="ui-timepicker-input" size="10" value="00:00" onChange="jobsTimeSanitization(EndTime.value, 'EndTime');" autocomplete="off"></td>
                </tr>
                <tr>
                    <td>Scheduling Team<span class="required">*</span></td>
                    <td colspan="3">
                        <?php echo '   <select class="chosen-select bg-white chosen-selectMaxWidth" name="scheduling_team" id="ddldutyteam">';
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
                <tr hidden>
                    <td class="lightblue">Default Colour</td>
                    <td><input id="jobdefaultcolor" name="defaultcolor" type="color" size="10" value=""></td>
                </tr>
                <tr>
                    <td>Label</td>
                    <td colspan="3">
                        <select name="programme_id" id="JobType">
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
                        <input name="submit" type="submit" value="Create Job"></td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
<script type="text/javascript" src="../../js/add-newjob.js?v=<?php echo time(); ?>"></script>

