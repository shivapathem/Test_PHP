<?php
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctions.php';
include_once '../../../function-includes/rota_functions.php';
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

$startDateObj = DateTime::createFromFormat('d/m/Y', $_POST['start_date']);
$startDate = $startDateObj ? $startDateObj->format('Y-m-d') : '';
$endDateObj = !empty($_POST['end_date']) ? DateTime::createFromFormat('d/m/Y', $_POST['end_date']) : null;
$endDate = $endDateObj ? $endDateObj->format('Y-m-d') : '';

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
?>
<style>
#facebox .content {
    width: auto !important;
}
#facebox .close {
    display: none !important;
}
</style>


<div style="padding: 4px; font-size: 12px;" class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix">
    <span class="ui-dialog-title">
        <?php echo 'Apply Rota for ' . $schedulingPersonDetail['FullName'] . ' from ' . date('d/m/Y', strtotime($startDate)) . ' to ' . date('d/m/Y', strtotime($endDate)) ; ?>
    </span>
</div>
<div>
    <p id="apply-rota-message"  style="font-size: 8pt;font-weight: bold;text-align: center; max-width:450px;">Are you sure you want to apply the rota pattern to this individual? </p>
    <div class="ui-dialog-buttonset" style="float: right;">
        <button type="button" id="submit-apply-rota-yearly" class="ui-button ui-corner-all ui-widget" style="margin-right: 9px;">Confirm</button>
        <button type="button" class="ui-button ui-corner-all ui-widget" id="apply-rota-close">Cancel</button>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#apply-rota-close').click($.facebox.close);
    $('#submit-apply-rota-yearly').click(function() {
        submitApplyRota();
    });
    $('#apply-rota-close').click(function() {
        resetMaxDate();
        saveFilters();
        $('.context-menu-date-range').removeClass('context-menu-date-range');
        ShowSchedulingTeamYearAllocations(<?php echo $teamId; ?>);
    });
});

function submitApplyRota() {
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/edit-yearly/edit-yearly-apply-rota-action.php',
        data: {
          'start_week' : <?php echo $startWeek; ?>,
          'end_week' : <?php echo $endWeek; ?>,
          'is_shiftleader' : <?php echo $isShiftleader; ?>,
          'teamId' : <?php echo $teamId; ?>,
          'scheduledPersonId': <?php echo $schedulingPersonId; ?>,
          'start_date': '<?php echo $startDate; ?>',
          'end_date': '<?php echo $endDate; ?>'
        },
        success: function (data) {
            let messages = []
            let $table = $('<table class="redtable smalltable bluetable"></table>');
            let noErrors = true;
            data.forEach(function(item) {
                if(item['status'] > 0) {
                    noErrors = false;
                }
                let $td = $('<tr></tr>');
                $table.append($td.append($('<td></td>').text(item['message'])));
            });
            if(!noErrors) {
                $('#apply-rota-message').html($table);
                $('#submit-apply-rota-yearly').hide();
                $('#apply-rota-close').text('Ok');
            } else {
                $('#apply-rota-close').trigger('click');
                ShowSchedulingTeamYearAllocations(<?php echo $teamId; ?>);
            }
        },
        error:function (data) {
        }
  });
}
</script>