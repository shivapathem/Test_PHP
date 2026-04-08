<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;
require_once __DIR__ . '/../service/AllocationService.php';
include_once '../../../../function-includes/masterduty_functions.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
$row = $service->getAdhocDutyByID($request->get("ID"));
$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$History = 'Modified at '.date("H:i").' On '.date("d/m/Y").' by '.$_SESSION['user']['FullName'].'.  Ad Hoc Duty : '.$row['DutyName'];
$historyType = 11;

/* Breaktime calculation */
$BreakTime = is_null($row['BreakTime']) ? 0 : $row['BreakTime'];
$intBreakTimeHour = intval((int)$BreakTime / 3600);
$intBreakTimeMinute = intval(((int)$BreakTime % 3600) / 60);
$breakTimehourOptions = PopulateHoursDropDown($intBreakTimeHour);
$breakTimeminuteOptions = PopulateMinutesDropDown_limited($intBreakTimeMinute);

$intstartHour = intval($row['StartTime'] / 3600);
$intstartHour = strlen(trim($intstartHour))== 1 ? "0".$intstartHour : $intstartHour;
$intstartMinute = intval(($row['StartTime'] % 3600) / 60);
$intstartMinute = strlen(trim($intstartMinute))== 1 ? "0".$intstartMinute : $intstartMinute;
$intstartTime = $intstartHour . ':' . $intstartMinute;

if(($row['StartTime'] == 0) && ($row['EndTime'] == 0)) {
    $row['EndTime'] = 0;
}
$intendHour = intval($row['EndTime'] / 3600);
$intendHour = strlen(trim($intendHour))== 1 ? "0".$intendHour : $intendHour;
$intendMinute = intval(($row['EndTime'] % 3600) / 60);
$intendMinute = strlen(trim($intendMinute))== 1 ?  "0".$intendMinute : $intendMinute;
$intendTime = $intendHour . ':' . $intendMinute;
$isNeedCovering = $row['IsNeedCovering'] ?? 1;
$rsColours = GetDutyColourList($row['TeamID']);
$ColourOptions = PopulateDutyColoursDropDown($rsColours, $row['DutyColourID'], $row['DutyColourID']);
$commentArr = explode('__COMMENT_SEPARETOR__', $row['DutyComment']);
?>
<script src="../../../../../js/editAdhocDuty.js?v=<?php echo time(); ?>"></script>
<script src="../../../../../js/spectrum.js?v=<?php echo time(); ?>"></script>
<!-- show breaktime error message-->
<div id="breaktimeError" title="Message"></div>
<div id="errorDivAhdoc" class="hiddenErrorDiv"></div>

<form id="editAdhocDutyForm">

<table id="adhocdutynewedittabledetail" class="redtable smalltable bluetable" width="100%">
        <tbody>
            <tr>
                <td class="lightblue">Duty Name</td>
                <td colspan="3">
                    <input type="text" id="DutyName" name="DutyName" onkeyup="dutyNameValidation('#DutyName','Duty Name',event);" value="<?php echo $row['DutyName']; ?>" style="width: 40%;">
                </td>
            </tr>
            <tr>
                <td class="lightblue" width="15%">Start Time<span class="required">*</span></td>
                <td width="35%">
                    <input id="StartTime" name="StartTime" type="text" class="ui-timepicker-input adhoc-timepicker" size="10" value="<?php  echo $intstartTime; ?>" maxlength="5" autocomplete="off" onfocusout="calculateDurationAh()">
                    </td>
                <td class="lightblue" width="15%">End Time<span class="required">*</span></td>
                <td width="35%">
                    <input id="EndTime" name="EndTime" type="text" class="ui-timepicker-input adhoc-timepicker" size="10" value="<?php  echo $intendTime; ?>" maxlength="5" autocomplete="off" onfocusout="calculateDurationAh()">
                    </td>
                </tr>
                <tr>
                    <td class="lightblue">Duty Colour</td>
                    <td colspan="3">
                        <select id="ahdutycolour" name="ahdutycolour" class="chosen-select bg-white chosen-selectMaxWidth">
                            <?php echo $ColourOptions; ?>
                        </select>
                    </td>
                </tr>
                <tr>
					<td class="lightblue">Break Duration</td>
					<td colspan="3">
					<?php echo '<select class="chosen-select-breaktime" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 30%;">';
					echo ''.$breakTimehourOptions.'';
					echo '   </select> : ';
					echo '   <select class="chosen-select-breaktime" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 30%;">';
					echo ''.$breakTimeminuteOptions.'';
					echo '   </select></br>';   ?>
					</td>
				</tr>
				<tr><td>Person Comments</td><td colspan="3">
					<textarea rows="2" maxlength="500" name="Comment" id = "commentBox"cols="35"><?php echo isset($commentArr[0]) ? $commentArr[0] : ''; ?></textarea></td>
				</tr>
                <tr>
					<td>Doesn't Need Covering</td>
                    <td td colspan="3">
                        <input id="isNeedCovering" name="isNeedCovering" type="checkbox" <?php echo $isNeedCovering == 0 ? 'checked' : ''; ?>>
                    </td>
				</tr>
                <input type="hidden" name="StartDate" value="<?php echo (new Carbon($row['StartDate']))->format('Y-m-d H:i:s'); ?>">
                <input type="hidden" name="EndDate" value="<?php echo (new Carbon($row['EndDate']))->format('Y-m-d H:i:s'); ?>">
                <input type="hidden" id="adhocId" name="AdhocID" value="<?php echo $row['MasterDutyID']; ?>">
                <input type="hidden" id="SchedulingTeamID" name="SchedulingTeamID" value="<?php echo $row['TeamID']; ?>">
                <input type="hidden" name="StaffID" value="<?php echo $row['ScheduledPersonID']; ?>">
                <input type="hidden" name="userId" value="<?php echo $userID; ?>">
                <input type="hidden" name="message" value="<?php echo $History; ?>">
                <input type="hidden" name="historyType" value="<?php echo $historyType; ?>">
                <input type="hidden" name="attributeId" value="<?php echo $row['MasterDutyID']; ?>">
                <input type="hidden" name="dutyDate" value="<?php echo $request->get("dutyDate"); ?>">
                <input type="hidden" id="dutyColourID" name="dutyColourID" value="<?php echo $row['DutyColourID']; ?>">
                <input type="hidden" id="dutyColourID" name="DutyComment" value="<?php echo isset($commentArr[1]) ? $commentArr[1] : ''; ?>">
                <input type="hidden" name="action" value="edit">
        </tbody>
    </table>
    <!-- Allow form submission with keyboard without duplicating the dialog button -->
    <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
</form>