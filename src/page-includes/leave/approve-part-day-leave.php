<style type="text/css">
.ui-timepicker-wrapper{ z-index: 999999 !important;}
</style>
<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../../../vendor/autoload.php";
require_once __DIR__ . '/../../page-includes/allocations/weekly/service/AllocationService.php';
include_once __DIR__ ."/../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$service = new AllocationService();

$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = isset($_SESSION['user']['AreaID']) ? $_SESSION['user']['AreaID'] : 0;
$currentuserid = ($_COOKIE['editWeeklyUserId']) ?? $_SESSION['user']['UserID'];
$username = isset($_COOKIE['editWeeklyUserFullName']) ? $_COOKIE['editWeeklyUserFullName'] : $_SESSION['user']['FullName'];
$dutyStartTime = displayTime($service->seconds2hours($request->get('dutyStartTime')));
$dutyEndTime = displayTime($service->seconds2hours($request->get('dutyEndTime')));
$dutyDuration = displayTime(number_format((float) ($request->get('dutyDuration') / 3600), 2, '.', ''), '.');
$pdlStartTime = ($request->get('pdlStartTime') == 0) ? '00:00' : displayTime($service->seconds2hours($request->get('pdlStartTime')));
$pdlEndTime = ($request->get('pdlEndTime') == 0) ? '00:00' : displayTime($service->seconds2hours($request->get('pdlEndTime')));
$calPdlDuration = $request->get('pdlEndTime');

$_SESSION['attributeid'] = $_POST['dutyId'];
$_SESSION['start_time'] = $_POST['pdlStartTime'];
$_SESSION['end_time'] = $_POST['pdlEndTime'];

if ($request->get('pdlStartTime') > $request->get('pdlEndTime')) {
    $calPdlDuration = (86400 + $request->get('pdlEndTime'));
}

$pdlDuration = displayTime(number_format((float) (($calPdlDuration - $request->get('pdlStartTime')) / 3600), 2, '.', ''), '.');

$tdPDLDuration = '';
if (($request->get('pdlStartTime') != 0) || ($request->get('pdlEndTime') != 0) || ($request->get('pdlTempData') != '')) {
    if ($request->get('pdlTempData') != '') {
        foreach ($request->get('pdlTempData') as $key => $value) {
            if ($value['leaveId'] == $request->get('leaveId')) {
                $pdlStartTime = displayTime($service->seconds2hours($value['pdlStartTime']));
                $pdlEndTime = displayTime($service->seconds2hours($value['pdlEndTime']));
                $calPdlDuration = $value['pdlEndTime'];
                if ($value['pdlStartTime'] > $value['pdlEndTime']) {
                    $calPdlDuration = (86400 + $value['pdlEndTime']);
                }
                $tdPDLDuration = displayTime(number_format((float) (($calPdlDuration - $value['pdlStartTime']) / 3600), 2, '.', ''), '.') . ' hrs';
                break;
            }
        }
    } else {
        $tdPDLDuration = $pdlDuration . ' hrs';
    }
}

$request->request->set('id', $request->get('dutyId'));
$request->request->set('getType', 'PDL');
$dataAllocatedJobs = $service->getDutyAllocatedJobs($request);

$dutyJobsStr = '';
if (!empty($dataAllocatedJobs)) {
    $dutyJobs = [];
    foreach ($dataAllocatedJobs as $k => $v) {
        $dutyJobs[$v['JobName']]['startTime'] = $v['StartTime'];
        $dutyJobs[$v['JobName']]['endTime'] = $v['EndTime'];
    }
    if (!empty($dutyJobs)) {
        $dutyJobsStr = json_encode($dutyJobs);
    }
}

function displayTime($time = '', $seperator = ':')
{
    $explTime = explode($seperator, $time);

    $dtTimeHr = $explTime[0];
    if (($dtTimeHr <= 9) && ($seperator == ':')) {
        $dtTimeHr = '0' . $dtTimeHr;
    }
    $dtTimeSec = $explTime[1];
    if (($dtTimeSec <= 9) && (substr_count($dtTimeSec, 0) == 1) && ($seperator == ':')) {
        $dtTimeSec = '0' . $dtTimeSec;
    }

    if (($seperator == '.') && ($dtTimeSec == '50')) {
        $dtTimeSec = '5';
    }
    return $dtTimeHr . $seperator . $dtTimeSec;
}
function PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype){
	$pdo = OpenDBLinkA7();
        try{
        $strQuery = "exec [dbo].[usp_mod_AllocationHistory] ?,?,?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $attributeid, PDO::PARAM_INT);
        $stmt->bindParam(2, $historytype, PDO::PARAM_INT);
        $stmt->bindParam(3, $userid, PDO::PARAM_INT);
        $stmt->bindParam(4, $message, PDO::PARAM_STR);
        $stmt->bindParam(5, $status, PDO::PARAM_INT);
		$stmt->bindParam(6, $historysubtype, PDO::PARAM_STR);
        $stmt->execute();
      } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        echo $e->getMessage();
      }
  }
?>
<div>
<div style="width: 1%; float:left; color: white;">.</div>
<div style="width: 99%; float:right; text-align: center; font-size: 11px; color: #FF0000; display: none;" id="errorDiv"></div>
</div>
<div style="font-size:10px; color: #FF0000; text-align:center; width:100%; display:none;" id="errDiv"></div>
<form id="editDutyForm" onsubmit="return false;">
    <input type="hidden" name="dutyJobs" id="dutyJobs" value='<?php echo $dutyJobsStr; ?>'>
    <input type="hidden" name="validationPassVal" id="validationPassVal">
    <input type="hidden" name="leaveId" id="leaveId" value='<?php echo $request->get('leaveId'); ?>'>
    <?php if (($pdlStartTime == '00:00') && ($pdlEndTime == '00:00')) {?>
    <input type="hidden" name="adminApplyPDL" id="adminApplyPDL" value="1">
    <?php } else {?>
    <input type="hidden" name="adminApplyPDL" id="adminApplyPDL" value="0">
    <?php }?>
    <table id="dutynewedittabledetail" class="redtable smalltable bluetable" width="100%">
        <tbody>
            <tr>
                <td style="width:35%;">
                    Duty Details
                </td>
                <td style="width:65%;">
                    <?php echo $request->get('dutyName') . ' | ' . $dutyStartTime . ' - ' . $dutyEndTime . ' | ' . $dutyDuration . ' hrs'; ?>
                </td>
            </tr>
            <tr>
                <td style="width:35%;">
                    Part Day Leave Start Time
                </td>
                <td style="width:65%;">
                    <input id="starttimemasterduty" name="StartTime" type="text" value="<?php echo $pdlStartTime ?>" class="time ui-timepicker-input" size="10" autocomplete="off"  maxlength="5" onchange="calculatePDLDuration('.');">
                </td>
            </tr>
            <tr>
                <td style="width:35%;">
                    Part Day Leave End Time
                </td>
                <td style="width:65%;">
                    <input id="endtimemasterduty" name="EndTime" type="text" value="<?php echo $pdlEndTime ?>" class="time ui-timepicker-input" size="10" autocomplete="off" maxlength="5" onchange="calculatePDLDuration('.');">
                </td>
            </tr>
            <tr>
                <td style="width:35%;">
                    Part Day Leave Duration
                </td>
                <td style="width:65%;" id="pdlDurationTd">
                    <?php echo $tdPDLDuration; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
</form>
<script type="text/javascript">
$( document ).ready(function() {
    $(function() {
        $('#starttimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });

        $('#endtimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });
    });
});
</script>