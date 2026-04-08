<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
include_once __DIR__. '/../../function-includes/user-scheduling-team-list.php';

$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'];
$service = new AllocationService();
$intType = 0;
$ddlRotaTeams = empty($_REQUEST["ddlRotaTeams"]) ? 0 :$_REQUEST["ddlRotaTeams"];

if (isset($_SESSION['allocattionsdate'])) {
	$strCurrentDate = $_SESSION['allocattionsdate'];
} else {
	$strCurrentDate = date("Y-m-d");
}

/* Breaktime calculation */
$BreakTime = 4500;
$intBreakTimeHour = intval((int)$BreakTime / 3600);
$intBreakTimeMinute = intval(((int)$BreakTime % 3600) / 60);
$breakTimehourOptions = PopulateHoursDropDown($intBreakTimeHour);
$breakTimeminuteOptions = PopulateMinutesDropDown_limited($intBreakTimeMinute);

$starttime = $endtime = 0;
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
?>

<script src="../../../js/adhoc.js?v=<?php echo time(); ?>"></script>
<div class="content">
<h1 class="sr-only">Add New Ad Hoc Duty	</h1>
<?php echo '<div style="width: 600px; margin: auto; background-color: #d0d0d038; position: relative; top: 25px;">'; ?>

	<form id="newjob" method="post" >
		<table id="jobnewedittable" class="smalltable bluetable" width="100%" >
			<thead>
				<tr><th colspan="3">New Ad Hoc Duty</th>
				<th class="noBorder"></th></tr>
			</thead>
			<tbody>
				<tr>
					<td>Scheduling Team<span class="required" <?php if (isset($hidestars)) { echo $hidestars;} ?>>*</span></td>
					<td colspan="3">
						<select class="chosen-select bg-white chosen-selectMaxWidth" name="scheduling_team" id="ddldutyteam" onchange="populateStaffName()">';
						<option value=''>Select The Team</option>
						<?php
						$teamOptions = getSchedulingTeamList(0, 'allocation-policy', 'viewEditWeekly');
						if(!empty($teamOptions())) { 
							echo $teamOptions;
						} else {?>
						<option value="0">No Team Assigned</option>
						<?php }?>
					</td>
					
				</tr>
				<tr>
					<td>Start Date<span class="required" <?php if (isset($hidestars)) { echo $hidestars;} ?>>*</span></td>
					<td> <input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="addteamstartdate" name=	"addteamstartdate" required="required" <?php if(isset($disabledcontrol)) { echo  $disabledcontrol;} ?> onchange="setStartDateDayAndWeek(this.value)">
					</td>
					<td>Day  <div id="startDateDay" style="border:1px solid black;" ></div></td>
					<td>Week <div id="startDateWeek" style="border:1px solid black;"></div></td>
				</tr>	 
				<tr>	
					<td>End Date<span class="required" <?php if (isset($hidestars)) { echo  $hidestars; } ?>>*</span></td>
					<td><input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="addteamenddate" name="addteamenddate" required="required" maxlength="20" <?php if(isset($disabledcontrol)) { echo  $disabledcontrol;} ?> onchange="setEndDateDayAndWeek(this.value)"></td>
					<td>Day<div id="endDateDay" style="border:1px solid black;"></div></td>
					<td>Week<div id="endDateWeek" style="border:1px solid black;"></div></td>
				</tr>
				<tr>
					<td>Start Time<span class="required" <?php if (isset($hidestars)) { echo  $hidestars; } ?>>*</span></td><td><input id="StartTime" name="StartTime" type="text" class="ui-timepicker-input" size="10" value="<?php echo $intstartTime; ?>" autocomplete="off" maxlength="5" onChange="if(StartTime.value == 0){setTimeout(function(){StartTime.value ='00:00'; StartTime.blur(); }, 1000);}"></td>
					<td>End Time<span class="required" <?php if (isset($hidestars)) { echo  $hidestars; } ?>>*</span></td><td><input  id="EndTime" name="EndTime" type="text" class="ui-timepicker-input" size="10" value="<?php echo $intendTime; ?>" autocomplete="off" maxlength="5"></td>
				</tr>
				<tr><td>Staff Name<span class="required" <?php if (isset($hidestars)) { echo  $hidestars; } ?>>*</span></td>
				<td colspan="3">
					<select id="staffOption" class="chosen-select bg-white chosen-selectMaxWidth">
						<option selected value="0">Select Staff</option>
					</select>
					</td>
				</tr>
				
				<tr>
					<td>Duty Name<span class="required" <?php if (isset($hidestars)) { echo  $hidestars; } ?>>*</span></td><td colspan="3"><input type="text" id="dutyname" onkeyup="dutyNameValidation('#dutyname','Duty Name',event);" name="dutyname" size="50" maxlength="50" required value=""></td>
				</tr>

				<tr>
					<td>Duty Colour</td>
					<td>
						<select id="ahdutycolour" name="ahdutycolour" class="chosen-select bg-white chosen-selectMaxWidth">
							<option selected value="0">Select The Colour</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Break Duration</td>
					<td>
					<?php echo '<select class="chosen-select-breaktime" name="breakTimeHour" id="breakTimeHour" style="background-color: white; width: 30%;">';
					echo ''.$breakTimehourOptions.'';
					echo '   </select> : ';
					echo '   <select class="chosen-select-breaktime" name="breakTimeMinute" id="breakTimeMinute" style="background-color: white; width: 30%;">';
					echo ''.$breakTimeminuteOptions.'';
					echo '   </select></br>';   ?>  
					</td>
				</tr>
				<tr>
					<td>Person Comments</td><td colspan="3">
						<textarea rows="2" maxlength="500" name="info" id = "commentBox"cols="35"></textarea>
					</td>
				</tr>
				<tr>
					<td>Doesn't Need Covering</td>
                    <td>
                        <input id="isNeedCovering" name="isNeedCovering" type="checkbox">
                    </td>
				</tr>
					
				<tr>
					<td></td>
					<td colspan="3">
						<input name="submit" type="submit" value="Add Ad Hoc Duty">
						<input name="cancel" type="button" value="Cancel" class="closeAdhoc">
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
<script type="text/javascript">
<?php if(empty($teamOptions)) { ?>
    customAlertByModel('Your session has been interrupted. Please reload the week.');
<?php }?>
</script>

