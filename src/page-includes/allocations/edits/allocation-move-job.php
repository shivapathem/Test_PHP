<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/helpers.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../function-includes/AllocationsFunctionsday.php';

if ($_REQUEST['process']=='getDutiesList') {
    $teamId = $_REQUEST["teamId"];
    $currentdate = date('Y-m-d', strtotime($_REQUEST['currentdate']));
    $getWeekandDayArr = GetAllocationWeekandDay($currentdate);
    $week = intval($getWeekandDayArr['ixYearWeek']);
    $day = intval($getWeekandDayArr['ixDayInWeek']);
    $strDayName = $getWeekandDayArr['sDayName'];
    echo GetDutyByDayAndTeam($week, $day, $teamId, $currentdate,$strDayName);
}

if (($_REQUEST['process']=='MoveJob')) {
  $scheduledPersonId = $_REQUEST["scheduledPerson"];
  $JobId = $_REQUEST["JobId"];
  $teamId = $_REQUEST["teamId"];
  $role = $_REQUEST["role"];
  $destinationDate = date('Y-m-d', strtotime($_REQUEST['destinationDate']));
  $DutyId = $_REQUEST["DutyId"] ?? '';
  $response=MoveJobToOtherDuty($scheduledPersonId,$JobId,$teamId,$destinationDate,$DutyId,$role);
  $response = json_decode($response,true);
  $finalArray = array("intstatus"=>$response['SPExecStatus'],"strstatus"=>$response['SPMessage']);
  echo json_encode($finalArray);
  die();
}

if (($_REQUEST['process']=='CopyJobTo')) {
  $scheduledPersonId = $_REQUEST["scheduledPerson"];
  $JobId = $_REQUEST["JobId"];
  $teamId = $_REQUEST["selteamId"];
  $startDateCopyJobInput = date('Y-m-d', strtotime($_REQUEST['startDateCopyJobInput']));
  $endDateCopyJobInput = date('Y-m-d', strtotime($_REQUEST['endDateCopyJobInput']));
  $role = checkroleforpublish($teamId,$startDateCopyJobInput);
  $response=CopyJobToOtherDuty($scheduledPersonId,$JobId,$teamId,$startDateCopyJobInput,$endDateCopyJobInput,$role);
  $response = json_decode($response,true);
  $finalArray = array("intstatus"=>$response['SPExecStatus'],"strstatus"=>$response['SPMessage']);
  echo json_encode($finalArray);
  die();
}

function Checkroleforpublish($intTeamID,$strCurrentDate){

	$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
	$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

	$defaultLockSetting = getDefaultLocksettingByTeamONiDay($intTeamID, $strCurrentDate);
	if (isset($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus']) && $defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus'] == 1) {
		$lockUnlock = 1;
	} else {
		$lockUnlock = 0;
	}

	$arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);
	if ($arrStaffOptions[$intTeamID]['isAdmin'] == 1 || $arrStaffOptions[$intTeamID]['isScheduler'] == 1 || $arrStaffOptions[$intTeamID]['isTeamAdmin'] == 1) {
		$role = 2;
	} elseif ($arrStaffOptions[$intTeamID]['isShiftLeader'] == 1 && $lockUnlock == 1) {
		$role = 1;
	} else {
		$role = 0;
	}
	return 	$role;
}