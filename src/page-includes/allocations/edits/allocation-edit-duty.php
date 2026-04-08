<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
use Symfony\Component\HttpFoundation\Request;

include_once '../../../function-includes/init.php';
include_once '../../../function-includes/helpers.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../page-includes/allocations/weekly/service/AllocationRepository.php';

$request = Request::createFromGlobals();
$repository = new AllocationRepository();
if (!empty($_REQUEST['submit'])) {
	if (isset($_REQUEST["StartTime"])){
		$intStartTime = seconds_from_time($_REQUEST["StartTime"]);
	} else {
		$intStartTime = seconds_from_time('00');
	}
	if (isset($_REQUEST["EndTime"])){
		$intEndTime = seconds_from_time($_REQUEST["EndTime"]);
	} else {
		$intEndTime = seconds_from_time('00');
	}
    $dutyName = str_replace("'","''",$_REQUEST["eDutyname"]);
	if ((isset($_REQUEST["breakTimeHour"])) && (isset($_REQUEST["breakTimeMinute"]))){
		$BreakTime = $_REQUEST["breakTimeHour"].':'.$_REQUEST["breakTimeMinute"];
	} else {
		$BreakTime =  '0';
	}
	$breakTimeSec = seconds_from_time($BreakTime);
	$duration = seconds_from_time($_REQUEST["dutyduration"]);
	
	$durationNew = $duration + $breakTimeSec;

	$colorID = isset($_REQUEST['ddldutycolour'])?$_REQUEST['ddldutycolour']: 0;
    $labelId1 = $_REQUEST['labelIds'][0] ?? 0;
	$labelId2 = $_REQUEST['labelIds'][1] ?? 0;
	$labelId3 = $_REQUEST['labelIds'][2] ?? 0;
	$labelId4 = $_REQUEST['labelIds'][3] ?? 0;
	$labelId5 = $_REQUEST['labelIds'][4] ?? 0;
	$labelId6 = $_REQUEST['labelIds'][5] ?? 0;

	$allocationsID = $_REQUEST["allocationsID"] ?? 0;
    $team_id = $_REQUEST["scheduling_team"];
    $role = $_REQUEST['role']??0;
    $parentId = $_REQUEST["parentId"];
    $DutyID = $_REQUEST["dutyId"];
    $WeekNumber = $_REQUEST["WeekNumber"];
    $iDay = $_REQUEST["iDay"];
    $SchedulingPersonID = $_REQUEST['SchedulingPersonID'];
    $aftermidnight = 0;
    $allocationsSPID = $_REQUEST["allocationsSPID"] ?? 0;
	$isNeedCovering = isset($_REQUEST['isNeedCovering']) && $_REQUEST['isNeedCovering'] == 'on' ? 0 : 1;
	$isOverrideOver12 = isset($_REQUEST['isOverrideOver12']) && $_REQUEST['isOverrideOver12'] == 'on' ? 0 : 1;
	$dutyComments = !empty($_REQUEST['DutyComments']) ? $_REQUEST['DutyComments'] : null;
	if (($intStartTime==0) && ($intEndTime==0) && (($duration+$breakTimeSec)==86400)){
			$intEndTime=86400;
	}

	$DutyDate = $_REQUEST["currDate"];
    if($DutyID == 0){
		$TaskType = 'addDutyfromEdit';
        $StartDate = $_REQUEST["currDate"].' '.$_REQUEST['StartTime'].':'.'00';
        $EndDate = $_REQUEST["currDate"].' '.$_REQUEST['EndTime'].':'.'00';
		echo EditAllocatedDuty($allocationsID,$DutyID,$dutyName, $intStartTime, $intEndTime, $team_id, $TaskType,$WeekNumber,$iDay,$breakTimeSec,$colorID,$labelId1,$StartDate,$EndDate,$DutyDate,$SchedulingPersonID,$aftermidnight,$role,$durationNew,$labelId2,$labelId3,$labelId4,$labelId5,$labelId6,$isNeedCovering,$isOverrideOver12,$dutyComments);
    }else{
        $TaskType = 'updateDuty';
        $StartDate = $_REQUEST['StartDate'];
        $EndDate = $_REQUEST['EndDate'];
		if(!empty($SchedulingPersonID)){
			echo EditAllocatedDuty($allocationsID,$DutyID,$dutyName, $intStartTime, $intEndTime, $team_id, $TaskType,$WeekNumber,$iDay,$breakTimeSec,$colorID,$labelId1,$StartDate,$EndDate,$DutyDate,$SchedulingPersonID,$aftermidnight,$role,$durationNew,$labelId2,$labelId3,$labelId4,$labelId5,$labelId6,$isNeedCovering,$isOverrideOver12,$dutyComments,$team_id,$allocationsSPID);
		}else{
			echo EditAllocatedDuty($allocationsID,$DutyID,$dutyName, $intStartTime, $intEndTime, $team_id, $TaskType,$WeekNumber,$iDay,$breakTimeSec,$colorID,$labelId1,$StartDate,$EndDate,$DutyDate,null,$aftermidnight,$role,$durationNew,$labelId2,$labelId3,$labelId4,$labelId5,$labelId6,$isNeedCovering,$isOverrideOver12,$dutyComments);
		}
    }

	$existingComment = '';
	$personComments = $_REQUEST['PersonComments'] ?? '';
	$existingComment = $_REQUEST['oldPersonComments'] ?? '';
	if($SchedulingPersonID > 0 && $existingComment != $personComments) {
		$repository->setComment($allocationsID, $DutyDate, $SchedulingPersonID, $role, $personComments, 2);
	}
}


