<?php
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctions.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../page-includes/users/process/classUserSetup.php';
include_once '../../../function-includes/common/classCommonDBFunctions.php';
use App\Models\User\RefRole;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$setupObj = new classUserSetup();
$commonObj = new classCommonDBFunctions();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intSysAdmin =  isset($_SESSION['user']['SysAdmin']) ? $_SESSION['user']['SysAdmin'] :0 ;
$arrUserLeaveSetting=   json_decode($commonObj->userLeaveRequestByNetLogin($strUser, 0),true);

$teamId = $_POST['teamId'];
$startWeek = $_POST['start_week'];
$endWeek = $_POST['end_week'];
$isShiftleader = $_POST['is_shiftleader'];
$schedulingPersonId = $_POST['scheduledPersonId'];

$schedulingPersonDetail = GetScheduledPersonDetailsById($schedulingPersonId);

$startDate = DateTime::createFromFormat('d/m/Y', $_POST['start_date'])->format('Y-m-d');
$endDate = !empty($_POST['end_date']) ? (DateTime::createFromFormat('d/m/Y', $_POST['end_date']))->format('Y-m-d') : '';


$isScheduler = $userRoleTrait->checkSchedulingTeamRoleExists($teamId, RefRole::SCHEDULER);
$isTeamAdmin = $userRoleTrait->checkSchedulingTeamRoleExists($teamId, RefRole::SCHEDULING_TEAM_ADMIN);
$isTeamLeader = $userRoleTrait->checkSchedulingTeamRoleExists($teamId, RefRole::TEAM_LEADER);
//If system admin or area admin the person have STA permission
if($isTeamAdmin == 0) {
  $isTeamAdmin = $userRoleTrait->checkEditWeeeklyAdminRole($teamId);
}
if (($isScheduler != 1) && ($isTeamAdmin != 1) && ($isTeamLeader != 1)) {
  echo "Access denied";
  die;
}

$schedulingPersonData = GetScheduledPersonTeamDetailsNew($schedulingPersonId);
$spNetLogin = $schedulingPersonData[$teamId]['Login'];
$arrLeave = ReadLeaveForRota($spNetLogin, $startDate, $endDate);

$startWeek = bbcweeknumber($startDate);
$endWeek =  bbcweeknumber($endDate);

$arrTeamDefaults = GetTeamDefaults($schedulingPersonId,$teamId);

$pdo = OpenDBLinkA7();
$strQueryLastCreatedWeek = "select max(AL_WeekNumber) as MaxWeekNumber from Allocations where AL_SchedulingTeamID = :teamId";
$stmt = $pdo->prepare($strQueryLastCreatedWeek);
$stmt->bindParam(':teamId', $teamId, PDO::PARAM_INT);
$stmt->execute();
$lastCreatedWeek = $stmt->fetch(PDO::FETCH_ASSOC)['MaxWeekNumber'];

if(empty($endDate)) {
    $endWeek = $lastCreatedWeek;
}

$strQuery = "exec [dbo].[usp_fetch_monthlyAllocationAndRota] :startWeek, :endWeek, :schedulingPersonId, :isShiftleader, :strUser, 2";
$stmt = $pdo->prepare($strQuery);
$stmt->bindParam(':startWeek', $startWeek, PDO::PARAM_INT);
$stmt->bindParam(':endWeek', $endWeek, PDO::PARAM_INT);
$stmt->bindParam(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
$stmt->bindParam(':isShiftleader', $isShiftleader, PDO::PARAM_INT);
$stmt->bindParam(':strUser', $strUser, PDO::PARAM_STR);
$stmt->execute();
$arrAllocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$canNotDelete = [];
$deleteableDutyCounter = 0;
$dutyNeedCovering = [];
$sicknessDetail = [];

foreach($arrAllocations as $duty) {
    $schedulingTeamId = $duty['SchedulingTeamId'];
    if(empty($schedulingTeamId) || empty($duty['DutyName']) || in_array(strtolower($duty['DutyName']), ['leave', 'u', 'off leave', 'absent']) || $duty['display_priority'] != 1) {
        continue;
    }

    $teamDeleteRestricted = $duty['IsRestrictDeleteDuty'];

    $canDelete = true;
    if ((strtotime($duty['DutyDate']) >= strtotime($startDate)) && (strtotime($duty['DutyDate']) <= strtotime($endDate) || empty($endDate))) {
        if(($isScheduler != 1) && ($isTeamAdmin != 1) && ($isTeamLeader != 1)) {
            $duty['reason'][] = $duty['IsHomeTeam'] == 1 ? 'No Access to the team' : 'Duty from Additional Team';
            $canNotDelete[$duty['ID']] = $duty;
            $canDelete = false;
        }

        if($duty['IsChargingAdded'] == 1) {
            $duty['reason'][] = 'Duty with Charging attached';
            $canNotDelete[$duty['ID']] = $duty;
            $canDelete = false;
        }

        if($duty['MarkedOvertime'] == 1) {
            $duty['reason'][] = 'Duty with Overtime attached';
            $canNotDelete[$duty['ID']] = $duty;
            $canDelete = false;
        }

        if(!empty($duty['LeaveStartTime']) || (isset($arrLeave[$duty['DutyDate']]) && reset($arrLeave[$duty['DutyDate']])['Approved'] == 1) ) {
            $duty['reason'][] = 'Duty with PDL attached';
            $canNotDelete[$duty['ID']] = $duty;
            $canDelete = false;
        }

        if($teamDeleteRestricted == 1 && isset($duty['DutyName'][1]) && !in_array($duty['DutyName'][1], ['7', '8', '9']) && ($duty['IsNeedCovering'] == 1 || strlen((string)$duty['IsNeedCovering']) == 0) && (!empty($duty['StartTime']) || !empty($duty['EndTime'])) && $canDelete == true) {
           $dutyNeedCovering[] = $duty;
           $canDelete = false;
        }

        if(in_array(strtolower($duty['DutyName']), ['u-sick', 'sick']) && $canDelete == true) {
            $sicknessDetail[] = $duty;
            $canDelete = false;
        }

        if($canDelete) {
            $deleteableDutyCounter++;
        }
    }
}

?>
<style>
#facebox .content {
    width: auto !important;
}
#facebox .close {
    display: none !important;
}
</style>


<div style="padding: 4px; font-size: 12px; margin-bottom:12px;" class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix">
    <span class="ui-dialog-title">
        <?php echo 'Delete Duties for ' . $schedulingPersonDetail['FullName'] . ' from ' . date('d/m/Y', strtotime($startDate)) . ' to ' . date('d/m/Y', strtotime($endDate)) ; ?>
    </span>
</div>
<?php if(count($canNotDelete) > 0) { ?>
<table class="redtable bluetable" style="width: 100%; margin-top: 0px;">
    <thead>
        <tr role="row">
            <th colspan="6" style="text-align:center;border-right: 1px solid #cdcdcd;">Duties which cannot be Deleted</th>
        </tr>
        <tr role="row">
            <th style="text-align:center;">Duty Name</th>
            <th style="text-align:center;">Duty Date</th>
            <th style="text-align:center;">Start Time</th>
            <th style="text-align:center;">End Time</th>
            <th style="text-align:center;">Scheduling Team</th>
            <th style="text-align:center;border-right: 1px solid #cdcdcd;">Reason</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach($canNotDelete as $key => $cnua) {
            $starttime = '--:--';
            $endtime = '--:--';
            if (!empty($cnua['StartTime']) || !empty($cnua['EndTime'])) {
                $intstartHour = intval($cnua["StartTime"] / 3600);
                $intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
                $intstartMinute = intval(($cnua["StartTime"] % 3600) / 60);
                $intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
                $starttime = $cnua["StartTime"];
                $starttime = $intstartHour . ":" . $intstartMinute;
                if ($cnua["EndTime"] > 86400) {
                    $cnua['EndTime'] = $cnua['EndTime'] - 86400;
                }
                $intendHour = intval($cnua['EndTime'] / 3600);
                $intendHour = strlen(trim($intendHour)) == 1 ? "0" . $intendHour : $intendHour;
                $intendMinute = intval(($cnua['EndTime'] % 3600) / 60);
                $intendMinute = strlen(trim($intendMinute)) == 1 ? "0" . $intendMinute : $intendMinute;
                $endtime = $intendHour . ":" . $intendMinute;
            }
            $canNotDelete[$key]['formatted_start_time'] = $starttime;
            $canNotDelete[$key]['formatted_end_time'] = $endtime;
        ?>
        <tr role="row" class="odd">
            <td>
                <?php echo ($cnua['DutyName'] ?? ''); ?>
            </td>
            <td>
                <?php echo ((isset($cnua['DutyDate']) && !empty($cnua['DutyDate'])) ? date('d/m/Y', strtotime($cnua['DutyDate'])) : '-'); ?>
            </td>
            <td>
                <?php echo ($starttime ?? '-'); ?>
            </td>
            <td>
                <?php echo ($endtime ?? '-'); ?>
            </td>
            <td>
                <?php echo ($cnua['schedulingTeamName'] ?? '-'); ?>
            </td>
            <td>
                <?php echo implode('<br>', $cnua['reason'] ?? []); ?>
            </td>
		</tr>
        <?php }  ?>
    </tbody>
</table>
<?php } ?>
<input type="hidden" id="duties-cant-deleted" value="<?php echo htmlentities(json_encode($canNotDelete));  ?>">
<input type="hidden" id="duty-need-covering" value="<?php echo htmlentities(json_encode($dutyNeedCovering));  ?>">
<input type="hidden" id="sickness-details" value="<?php echo htmlentities(json_encode($sicknessDetail));  ?>">
<hr>
<div>
    <p style="font-size: 8pt;font-weight: bold;text-align: center;"> Are you sure you want to delete the selected duties? <br> Total duties to be Deleted : <?php echo $deleteableDutyCounter; ?>
    <?php
        if(count($dutyNeedCovering) > 0) {
          echo  '<br> Total duties to be Unassigned : ' .  count($dutyNeedCovering);
        }
        if(count($sicknessDetail) > 0) {
            echo  '<br> Total sickness to be Deleted : ' .  count($sicknessDetail);
        }
    ?>
    </p>
    <div class="ui-dialog-buttonset" style="float: right;">
        <?php if(count($dutyNeedCovering) > 0 || $deleteableDutyCounter > 0 || count($sicknessDetail) > 0) { ?>
        <button type="button" id="submit-delete-duty-yearly" class="ui-button ui-corner-all ui-widget" style="margin-right: 9px;">Confirm</button>
        <?php } ?>
        <button type="button" class="ui-button ui-corner-all ui-widget" id="delete-duty-close">Cancel</button>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#delete-duty-close').click($.facebox.close);
    $('#submit-delete-duty-yearly').click(function() {
        submitDeleteDuties();
    });
    $('#delete-duty-close').click(function() {
        resetMaxDate();
        saveFilters();
        $('.context-menu-date-range').removeClass('context-menu-date-range');
    });
});

function submitDeleteDuties() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/edit-yearly/edit-yearly-delete-action.php',
        data: {
          'start_week' : <?php echo $startWeek; ?>,
          'end_week' : <?php echo $endWeek; ?>,
          'is_shiftleader' : <?php echo $isShiftleader; ?>,
          'teamId' : <?php echo $teamId; ?>,
          'scheduledPersonId': <?php echo $schedulingPersonId; ?>,
          'start_date': '<?php echo $startDate; ?>',
          'end_date': '<?php echo $endDate; ?>',
          'can_not_delete': $('#duties-cant-deleted').val(),
          'deleteableDutyCounter': <?php echo $deleteableDutyCounter; ?>,
          'duty_need_covering': $('#duty-need-covering').val(),
          'sickness_details':  $('#sickness-details').val()
        },
        success: function (data) {
            var teamId = <?php echo $teamId; ?>;
            $('#delete-duty-close').trigger('click');
            ShowSchedulingTeamYearAllocations(<?php echo $teamId; ?>);
        },
        error:function (data) {
        }
  });
}
</script>