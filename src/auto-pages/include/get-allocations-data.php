<?php
session_start();
include_once '../../function-includes/genericfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = GetUserIdbyNetlogin($strUser);
if (isset($_REQUEST['teamId'])) {
  	$intTeamID = $_REQUEST['teamId'];
} else {
  	$intTeamID = GetDefaultSchedulingTeamIdByLogin($UserID);
}

$strCurrentDate = date("Y-m-d");
$strYesterday = date("Y-m-d", strtotime("-1 day", (strtotime($strCurrentDate))));
$strTomorrow = date("Y-m-d", strtotime("+1 day", (strtotime($strCurrentDate))));

$arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);
if (isset($arrStaffOptions[$intTeamID]) && ($arrStaffOptions[$intTeamID]['isScheduler'] == 1 || $arrStaffOptions[$intTeamID]['isTeamAdmin'] == 1)) {
    $rolepermission = 1;
} elseif (isset($arrStaffOptions[$intTeamID]) && ($arrStaffOptions[$intTeamID]['isShiftLeader'] == 1 && $lockUnlock==1)) {
    $rolepermission = 1;
} else {
  	$rolepermission=0;
}

$getWeekandDayArr = GetAllocationWeekandDay($strCurrentDate);
$intWeek = $getWeekandDayArr['ixYearWeek'];
$intDay = $getWeekandDayArr['ixDayInWeek'];

$returnData['status'] = true;
$returnData['intWeek'] = $intWeek;
$returnData['intDay'] = $intDay;
$returnData['rolepermission'] = $rolepermission;

echo json_encode($returnData);