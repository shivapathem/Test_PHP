<?php
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
$dutiesCantBeUnassigned = json_decode($_POST['can_not_unassign'], true);
$doesntNeedCovering = json_decode($_POST['doesnt_need_covering'], true);
$sicknessDetails = json_decode($_POST['sickness_details'], true);
$loggedInUser = $commonObj->GetStaffDetailsUserDetailsByLogin($strUser);

$teamId = $_POST['teamId'];
$startWeek = $_POST['start_week'];
$endWeek = $_POST['end_week'];
$isShiftleader = $_POST['is_shiftleader'];
$schedulingPersonId = $_POST['scheduledPersonId'];
$schedulingPersonDetail = GetScheduledPersonDetailsById($schedulingPersonId);


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

$emailList =  getEditYearlyMailList($teamId);

$teamBasedStartEndDates = getStartEndDateBasedOnHomeTeam($startDate, $endDate, $schedulingPersonId);

$pdo = OpenDBLinkA7();
foreach($teamBasedStartEndDates as $teamBasedStartEndDate) {
    $strQuery = "exec [dbo].[usp_EditYearly_Edits] 'UNASSIGN', :strUser, :startDate, :endDate, :teamId, :schedulingPersonId";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':strUser', $strUser, PDO::PARAM_STR);
    $stmt->bindParam(':startDate', $teamBasedStartEndDate['startDate'], PDO::PARAM_STR);
    $stmt->bindParam(':endDate', $teamBasedStartEndDate['endDate'], PDO::PARAM_STR);
    $stmt->bindParam(':teamId', $teamBasedStartEndDate['teamId'], PDO::PARAM_INT);
    $stmt->bindParam(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
    $stmt->execute();
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

$mail->Subject = getenv('EMAIL_SUFFIX') . ' Duties unassigned for ' . $schedulingPersonDetail['FullName'] . ' from ' . date('d/m/Y', strtotime($startDate)) . ' to ' . date('d/m/Y', strtotime($endDate));

$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
$mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');

$html='<style>'.file_get_contents(getenv('EMAIL_CSS')).'</style>
    <body>
    <div style="background-color: #4B72BF"><img src="cid:pobanner" /></div><br>';
$html.= '
<div style="padding:12px">
     <p>Hi,<br><br>
     Total of ' . $_POST['unassignableDutyCounter'] . ' duties has been unassigned
     for ' . $schedulingPersonDetail['FullName'] . ' from ' . date('d/m/Y', strtotime($startDate)) . ' to ' . date('d/m/Y', strtotime($endDate)) . ' by ' . $loggedInUser['userDisplayName'] . '.<p>';

    if(count($dutiesCantBeUnassigned) > 0) {

        $html .= '<div><div style="display: grid; place-items: center;margin-top:8px;">
        <table class="tablesmalltidy text-style">
        <thead>
            <tr role="row">
                <th class="headercell" colspan="6" style="text-align:center;">Duties not Unassigned</th>
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
        foreach($dutiesCantBeUnassigned as $key => $cnua) {

            $html.= '<tr role="row" class="odd">
                <td>
                    ' . ($cnua['DutyName'] ?? '') . '
                </td>
                <td>
                    ' . ((isset($cnua['DutyDate']) && !empty($cnua['DutyDate'])) ? date('d/m/Y', strtotime($cnua['DutyDate'])) : '-') . '
                </td>
                <td>
                    ' . ($cnua['formatted_start_time'] ?? '-') . '
                </td>
                <td>
                    ' . ($cnua['formatted_end_time'] ?? '-') . '
                </td>
                <td>
                    ' . ($cnua['schedulingTeamName'] ?? '-') . '
                </td>
                <td>
                    ' . implode('<br>', $cnua['reason'] ?? []) . '
                </td>
            </tr>';
        }
        $html.='</tbody></table></div></div>';
    }

    if(count($doesntNeedCovering) > 0) {

        $html .= '<div><div style="display: grid; place-items: center;margin-top:8px;">
        <table class="tablesmalltidy text-style">
        <thead>
            <tr role="row">
                <th colspan="6" class="headercell" style="text-align:center;">Unassigned duties which do not need covering</th>
            </tr>
            <tr role="row">
                <th class="headercell" >Duty Name</th>
                <th class="headercell" >Duty Date</th>
                <th class="headercell" >Start Time</th>
                <th class="headercell" >End Time</th>
                <th class="headercell" >Scheduling Team</th>
            </tr>
        </thead>
        <tbody>';
        foreach($doesntNeedCovering as $key => $dnc) {

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
                    ' . ($dnc['schedulingTeamName'] ?? '-') . '
                </td>
            </tr>';
        }
        $html.='</tbody></table></div></div>';
    }

    if(count($sicknessDetails) > 0) {
        $html .= '<div><div style="display: grid; place-items: center;margin-top:8px;">
        <table class="tablesmalltidy text-style">
        <thead>
            <tr role="row">
                <th colspan="6" class="headercell" style="text-align:center;">Sickness detail</th>
            </tr>
            <tr role="row">
                <th class="headercell" >Sickness</th>
                <th class="headercell" >Date</th>
                <th class="headercell" >Scheduling Team</th>
            </tr>
        </thead>
        <tbody>';
        foreach($sicknessDetails as $key => $sick) {

            $html.= '<tr role="row" class="odd">
                <td>
                    ' . ($sick['DutyName'] ?? '') . '
                </td>
                <td>
                    ' . ( (isset($sick['DutyDate']) && !empty($sick['DutyDate'])) ? date('d/m/Y', strtotime($sick['DutyDate'])) : '-' ) . '
                </td>
                <td>
                    ' . ($sick['schedulingTeamName'] ?? '-') . '
                </td>
            </tr>';
        }
        $html.='</tbody></table></div></div>';
    }
$html .= '<br><br><p align="left"> Thanks </p><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
$html.='</div></body></html>';
$mail->msgHTML($html);
$mail->send();
?>