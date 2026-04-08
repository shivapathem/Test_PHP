<?php
header('Content-Type: application/json');
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctions.php';
include_once '../../../function-includes/common/classCommonDBFunctions.php';
use App\Models\User\RefRole;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$commonObj = new classCommonDBFunctions();
$setupObj = new classUserSetup();
$loggedInUser = $commonObj->GetStaffDetailsUserDetailsByLogin($strUser);

$teamId = $_POST['teamId'];
$startWeek = $_POST['start_week'];
$endWeek = $_POST['end_week'];
$isShiftleader = $_POST['is_shiftleader'];
$schedulingPersonId = $_POST['scheduledPersonId'];
$schedulingPersonDetail = GetScheduledPersonDetailsById($schedulingPersonId);
$arrTeamDefaults = GetTeamDefaults(0, $teamId);

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

$startDate =  $_POST['start_date'];
$endDate = $_POST['end_date'];

$startWeek = bbcweeknumber($startDate);
$endWeek =  bbcweeknumber($endDate);

$strQuery = "exec [dbo].[usp_fetch_monthlyAllocationAndRota] :startWeek, :endWeek, :schedulingPersonId, :isShiftleader, :strUser, 2";
$stmt = $pdo->prepare($strQuery);
$stmt->bindParam(':startWeek', $startWeek, PDO::PARAM_INT);
$stmt->bindParam(':endWeek', $endWeek, PDO::PARAM_INT);
$stmt->bindParam(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
$stmt->bindParam(':isShiftleader', $isShiftleader, PDO::PARAM_INT);
$stmt->bindParam(':strUser', $strUser, PDO::PARAM_STR);
$stmt->execute();
$arrAllocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$emailList =  getEditYearlyMailList($teamId);

$unallocatedDuty = [];
$dutyNotReplaced = [];
$responseData = [];

foreach($arrAllocations as $duty) {
    $schedulingTeamId = $duty['SchedulingTeamId'];
    if($duty['IsHomeTeam'] != 1 || empty($schedulingTeamId) || empty($duty['DutyName']) || in_array(strtolower($duty['DutyName']), ['leave', 'u', 'off leave', 'absent']) || $duty['display_priority'] != 1) {
        continue;
    }
    if ((strtotime($duty['DutyDate']) >= strtotime($startDate)) && (strtotime($duty['DutyDate']) <= strtotime($endDate) || empty($endDate))) {
        if(($isScheduler != 1) && ($isTeamAdmin != 1) && ($isTeamLeader != 1)) {
            dutyTimeFormat($duty);
            $duty['reasons'][] = 'No Access to the team';
            $duty['SchedulingTeamName'] = $arrTeamDefaults[$schedulingTeamId]['Description'];
            $dutyNotReplaced[$duty['ID']] = $duty;
        }
    }
}

$teamBasedStartEndDates = getStartEndDateBasedOnHomeTeam($startDate, $endDate, $schedulingPersonId);
if(empty($teamBasedStartEndDates)) {
    $responseData[] = ['status' => (int) 0, 'message' => 'No team associated with the user during this time period.'];
}
foreach($teamBasedStartEndDates as $teamBasedStartEndDate) {
    $pdo = OpenDBLinkA7();
    $strQuery = "exec [dbo].[usp_EditYearly_Edits] 'APPLYROTA', :strUser, :startDate, :endDate, :teamId, :schedulingPersonId";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':strUser', $strUser, PDO::PARAM_STR);
    $stmt->bindParam(':startDate', $teamBasedStartEndDate['startDate'], PDO::PARAM_STR);
    $stmt->bindParam(':endDate', $teamBasedStartEndDate['endDate'], PDO::PARAM_STR);
    $stmt->bindParam(':teamId', $teamBasedStartEndDate['teamId'], PDO::PARAM_INT);
    $stmt->bindParam(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
    $stmt->execute();
    $queryData = [];
    do {
        $resultData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($resultData) > 0 ) {
            $queryData[] = $resultData;
        }
    } while ($stmt->nextRowset());

    /*
    The response data will contain the query execution details, which will be used on the front end.
    An array index with a status of 0 indicates failure, while 1 indicates success.
    The message at each array index holds the stored procedure response for failed executions.
    */
    $responseData[] = ['status' => (int) $queryData[0][0]['SPExecStatus'], 'message' =>
    (int) $queryData[0][0]['SPExecStatus'] != 0 ?
    $queryData[0][0]['SPMessage'] . ', Applying Rota from ' . date("d/m/Y", strtotime($teamBasedStartEndDate['startDate'])) . ' to ' . date("d/m/Y", strtotime($teamBasedStartEndDate['endDate'])) . ' in Team ' . $arrTeamDefaults[$teamBasedStartEndDate['teamId']]['Description'] . '.'
    :
    'Applied Rota from ' . date("d/m/Y", strtotime($teamBasedStartEndDate['startDate'])) . ' to ' . date("d/m/Y", strtotime($teamBasedStartEndDate['endDate'])) . ' in Team ' . $arrTeamDefaults[$teamBasedStartEndDate['teamId']]['Description'] . ' successfully.'
    ];

    foreach($queryData[1] ?? [] as $duty) {
        dutyTimeFormat($duty);
        $reasons = [];
        if(empty($duty['DutyName'])) {
            continue;
        }
        if(!empty($duty['AdditionalTeamID'])) {
            $reasons[] = 'Duty from Additional Team';
            $duty['SchedulingTeamName'] = $duty['AdditionalTeamName'];
        }

        if(empty($duty['SchedulingPersonID'])) {
            $unallocatedDuty[] = $duty;
            continue;
        }

        if(in_array(strtolower($duty['DutyName']), [strtolower('LEAVE'), strtolower('OFF LEAVE')])) {
            $reasons[] = ucfirst($duty['DutyName']);
        }

        if($duty['ChargingStatus'] > 0) {
            $reasons[] = 'Duty with Charging attached';
        }

        if($duty['MarkedOvertime'] == 1) {
            $reasons[] = 'Duty with Overtime attached';
        }

        if(!empty($duty['LeaveStartTime']) || !empty($duty['LeaveEndTime'])) {
            $reasons[] = 'Duty with PDL attached';
        }
        $duty['reasons'] = $reasons;
        $dutyNotReplaced[] = $duty;
    }
}

usort($dutyNotReplaced, function ($a, $b) {
    return strtotime($a['DutyDate']) - strtotime($b['DutyDate']);
});

echo json_encode($responseData);

$successResponse = false;
foreach($responseData as $resData) {
    if($resData['status'] == 0) {
        $successResponse = true;
    }
}

if(!$successResponse) {
    die;
}

$mail = new PHPMailer\PHPMailer\PHPMailer();

$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = getenv('SMTP_HOST');
$mail->Port = 25;

$mail->setFrom('noreply@bbc.co.uk', 'Allocate');

foreach($emailList as $mailId) {
    $mail->addAddress($mailId['InternalEmail']);
}

$mail->Subject = getenv('EMAIL_SUFFIX').' Rota applied for ' . $schedulingPersonDetail['FullName'] . ' from ' . date('d/m/Y', strtotime($startDate)) . ' to ' . date('d/m/Y', strtotime($endDate));

$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

$html='<style>'.file_get_contents(getenv('EMAIL_CSS')).'</style>
    <body>
    <div style="background-color: #4B72BF"><img src="cid:pobanner" /></div><br>';
$html.= '
<div style="padding:12px">
     Hi,<br><br>
     Rota have been applied for ' . $schedulingPersonDetail['FullName'] . ' from ' . date('d/m/Y', strtotime($startDate)) . ' to ' . date('d/m/Y', strtotime($endDate)) . ' by ' . $loggedInUser['userDisplayName'] . '.';

    if(count($dutyNotReplaced) > 0) {

        $html .= '<div><div style="display: grid; place-items: center;margin-top:8px;">
        <table class="tablesmalltidy text-style">
        <thead>
            <tr role="row">
                <th colspan="6" class="headercell" style="text-align:center">Duties not replaced by Rota</th>
            </tr>
            <tr role="row">
                <th class="headercell" >Duty Name</th>
                <th class="headercell" >Duty Date</th>
                <th class="headercell" >Start Time</th>
                <th class="headercell" >End Time</th>
                <th class="headercell" >Scheduling Team</th>
                <th class="headercell" >Reason</th>
            </tr>
        </thead>
        <tbody>';
        foreach($dutyNotReplaced as $key => $dnr) {

            $html.= '<tr role="row" class="odd">
                <td>
                    ' . ($dnr['DutyName'] ?? '') . '
                </td>
                <td>
                    ' . ((isset($dnr['DutyDate']) && !empty($dnr['DutyDate'])) ? date('d/m/Y', strtotime($dnr['DutyDate'])) : '-') . '
                </td>
                <td>
                    ' . ($dnr['formatted_start_time'] ?? '-') . '
                </td>
                <td>
                    ' . ($dnr['formatted_end_time'] ?? '-') . '
                </td>
                <td>
                    ' . ($dnr['SchedulingTeamName'] ?? '-') . '
                </td>
                <td>
                    ' . implode('<br>', $dnr['reasons'] ?? []) . '
                </td>
            </tr>';
        }
        $html.='</tbody></table></div></div>';
    }

    if(count($unallocatedDuty) > 0) {

        $html .= '<div><div style="display: grid; place-items: center;margin-top:8px;">
        <table class="tablesmalltidy text-style">
        <thead>
            <tr role="row">
                <th class="headercell" colspan="6"  style="text-align:center">Duties added to Unallocated Section</th>
            </tr>
            <tr role="row">
                <th class="headercell">Duty Name</th>
                <th class="headercell" >Duty Date</th>
                <th class="headercell" >Start Time</th>
                <th class="headercell" >End Time</th>
                <th class="headercell" >Scheduling Team</th>
            </tr>
        </thead>
        <tbody>';
        foreach($unallocatedDuty as $key => $dnc) {

            $html.= '<tr role="row" class="odd">
                <td>
                    ' . ($dnc['DutyName'] ?? '') . '
                </td>
                <td>
                    ' . ( (isset($dnc['DutyDate']) && !empty($dnc['DutyDate'])) ? date('d/m/Y', strtotime($dnc['DutyDate'])) : '-' ) . '
                </td>
                <td>
                    ' . ($dnc['formatted_start_time'] ?? '-') . '
                </td>
                <td>
                    ' . ($dnc['formatted_end_time'] ?? '-') . '
                </td>
                <td>
                    ' . ($dnc['SchedulingTeamName'] ?? '-') . '
                </td>
            </tr>';
        }
        $html.='</tbody></table></div></div>';
    }
$html .= '<br><br><p align="left"> Thanks </p><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
$html.='</div></body></html>';
$mail->msgHTML($html);
$mail->send();

function dutyTimeFormat(&$duty) {
    $starttime = '--:--';
    $endtime = '--:--';
    if (!empty($duty['StartTime']) || !empty($duty['EndTime'])) {
        $intstartHour = intval($duty["StartTime"] / 3600);
        $intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
        $intstartMinute = intval(($duty["StartTime"] % 3600) / 60);
        $intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
        $starttime = $duty["StartTime"];
        $starttime = $intstartHour . ":" . $intstartMinute;
        if ($duty["EndTime"] > 86400) {
            $duty['EndTime'] = $duty['EndTime'] - 86400;
        }
        $intendHour = intval($duty['EndTime'] / 3600);
        $intendHour = strlen(trim($intendHour)) == 1 ? "0" . $intendHour : $intendHour;
        $intendMinute = intval(($duty['EndTime'] % 3600) / 60);
        $intendMinute = strlen(trim($intendMinute)) == 1 ? "0" . $intendMinute : $intendMinute;
        $endtime = $intendHour . ":" . $intendMinute;
    }
    $duty['formatted_start_time'] = $starttime;
    $duty['formatted_end_time'] = $endtime;
}
?>