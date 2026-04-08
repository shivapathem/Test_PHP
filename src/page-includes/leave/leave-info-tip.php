<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';

$commonDbobj = new classCommonDBFunctions();
$service = new AllocationService();

$id = $_REQUEST['id'];
$sessUserId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$leaveApplicationDetail = getLeaveApplicationDetails($id);
$arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLogin($sessUserId, 0),true);

$leaveCommentflag = 0;
if((strtolower($sessUserId ?? '') == strtolower($leaveApplicationDetail['Login'] ?? '')) || ($arrUserSettings['HasLeaveAdmin'] == 1)){
    $leaveCommentflag = 1;
}

$strUserName =  $leaveApplicationDetail['FullName'];
$bodyStr='';
$strDate = date('jS F Y', strtotime($leaveApplicationDetail['dDate'] ?? ''));
$strOfficeComments = $leaveApplicationDetail['OfficeComments'];
$strUserComments = $leaveApplicationDetail['Comments'];

$dateTime = $leaveApplicationDetail['Created'] ?? '';
$tz_from = 'UTC'; 
$newDateTime = new DateTime($dateTime, new DateTimeZone($tz_from)); 
$newDateTime->setTimezone(new DateTimeZone('Europe/London')); 
$dateTimeUK = $newDateTime->format('jS F Y \a\t H:i');

$bodyStr .='<table class="tablesmalltidy leave-tooltip-table" width="400px">';
$bodyStr .='<tr height="25px"class="leave-tooltip-table-background">';
$bodyStr .='<th colspan="2">Leave for '.$strUserName.' on '.$strDate.'</th>';
$bodyStr .='</tr>';

$bodyStr .='<tr>';
$bodyStr .='<td valign="top" colspan="2">';
$bodyStr .= 'The Leave group is \''.$leaveApplicationDetail['GroupDesc'].'\' and the type is \''.$leaveApplicationDetail['TypeDesc'].'\'.';
$bodyStr .= '</td>';
$bodyStr .='</tr>';

$bodyStr .='<tr>';
$bodyStr .='<td valign="top" colspan="2">This Leave was requested on '.$dateTimeUK.'.</td>';
$bodyStr .='</tr>';

$bodyStr .='<tr>';
$bodyStr .='<td valign="top" colspan="2">';
if ($leaveApplicationDetail['Approved'] == 1) {
    $bodyStr .= 'This Request has been approved.';
}
else {
    $bodyStr .= 'This Request has not yet been approved.';
}
$bodyStr .= '</td>';
$bodyStr .='</tr>';

if ($leaveApplicationDetail['unlikely'] == 1) {
    $bodyStr .='<tr>';
    $bodyStr .='<td valign="top" colspan="2">';
    $bodyStr .= 'This Request has been marked as Unlikely.';
    $bodyStr .= '</td>';
    $bodyStr .='</tr>';
}
if ($leaveCommentflag == 1) {
    if ($strUserComments != '') {
        $bodyStr .= '<tr>';
        $bodyStr .= '<td valign="top">User Comments</td>';
        $bodyStr .= '<td valign="top">' . $strUserComments . '</td>';
        $bodyStr .= '</tr>';
    }
    if ($strOfficeComments != '') {
        $bodyStr .= '<tr>';
        $bodyStr .= '<td valign="top" width="75px">Office Comments</td>';
        $bodyStr .= '<td valign="top" width="75px">' . trim(substr($strOfficeComments, 0, 90)) . '</td>';
        $bodyStr .= '</tr>';
    }
}
if ($leaveApplicationDetail['LeaveStartTime'] != '' || $leaveApplicationDetail['LeaveEndTime'] != '') {
    $pdlStartTime = (int) $leaveApplicationDetail['LeaveStartTime'];
    $pdlEndTime = (int) $leaveApplicationDetail['LeaveEndTime'];
    if($pdlStartTime > $pdlEndTime){
        $pdlEndTime = (int) (86400+$pdlEndTime);
    }
    $bodyStr .='<tr>';
    $bodyStr .='<td valign="top" width="75px" colspan="2" style="color:#109146; font-weight:bold; font-size:7pt;">PDL: '.$service->convertSecondsIntoTime($pdlStartTime,':','No').'-'.$service->convertSecondsIntoTime($pdlEndTime,':','No').' | '.$service->convertSecondsIntoTime(($pdlEndTime - $pdlStartTime),'.','Yes').'</td>';
    $bodyStr .='</tr>';
}
$bodyStr .='</table>';
echo $bodyStr;
 
?>