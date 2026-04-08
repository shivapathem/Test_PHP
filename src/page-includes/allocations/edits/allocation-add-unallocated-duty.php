<?php
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../page-includes/allocations/weekly/service/AllocationRepository.php';

$repository = new AllocationRepository();
if (!empty($_REQUEST['submit'])) {
	if (isset($_REQUEST["StartTime"])){
		$strStartTime = seconds_from_time($_REQUEST["StartTime"]);
	} else {
		$strStartTime = seconds_from_time('00');
	}
	if (isset($_REQUEST["EndTime"])){
		$strEndTime = seconds_from_time($_REQUEST["EndTime"]);
	} else {
		$strEndTime = seconds_from_time('00');
	}

    $dutyInfor['DutyID'] = 0;
    $dutyInfor['dutyName'] =  str_replace("'","''",$_REQUEST["eDutyname"]);
    $dutyInfor['team_id'] = $_REQUEST["scheduling_team"]??0;
    $dutyInfor['intStartTime'] = $strStartTime;
    $dutyInfor['intEndTime'] = $strEndTime;
	if ((isset($_REQUEST["breakTimeHour"])) && (isset($_REQUEST["breakTimeMinute"]))){
		$BreakTime = $_REQUEST["breakTimeHour"].':'.$_REQUEST["breakTimeMinute"];
	} else {
		$BreakTime =  '0';
	}
	$breakTimeSec = seconds_from_time($BreakTime);
    $dutyInfor['breakTime'] = $breakTimeSec;
    $durationSec = seconds_from_time($_REQUEST["dutyduration"]);
    $dutyInfor['dutyduration'] = $durationSec + $breakTimeSec;
    $dutyInfor['TaskType'] = 'addDuty';
    $dutyInfor['unallocated'] = 1;
    $dutyInfor['allocationEditID'] = 0;
    $dutyInfor['WeekNumber'] = $_REQUEST["WeekNumber"];
    $dutyInfor['iDay'] = $_REQUEST["iDay"];
    $dutyInfor['colorID'] = $_REQUEST['ddldutycolour'] ?? 0;
    $dutyInfor['labelId1'] = $_REQUEST['labelIds'][0] ?? 0;
    $dutyInfor['labelId2'] = $_REQUEST['labelIds'][1] ?? 0;
    $dutyInfor['labelId3'] = $_REQUEST['labelIds'][2] ?? 0;
    $dutyInfor['labelId4'] = $_REQUEST['labelIds'][3] ?? 0;
    $dutyInfor['labelId5'] = $_REQUEST['labelIds'][4] ?? 0;
    $dutyInfor['labelId6'] = $_REQUEST['labelIds'][5] ?? 0;
    $dutyInfor['rq_Date'] = $_REQUEST['requested_date'];
    $dutyInfor['role'] = $_REQUEST['role'];
    $dutyInfor['duty_parent_id'] = $_REQUEST['duty_parent_id'];
    $dutyInfor['isEdited'] = 0;
    $dutyInfor['AllocationID']=0;
    $dutyInfor['isNeedCovering'] = isset($_REQUEST['isNeedCovering']) && $_REQUEST['isNeedCovering'] == 'on' ? 0 : 1;
    $dutyInfor['DutyComments'] = $_REQUEST['DutyComments'] ?? '';
    $dutyInfor['isOverrideOver12'] = isset($_REQUEST['isOverrideOver12']) && $_REQUEST['isOverrideOver12'] == 'on' ? 0 : 1;
    $scheduledPersonId = $_REQUEST['scheduledPersonId'] ?? 0;
    echo EditAllocatedDuty($dutyInfor['duty_parent_id'], 0, $dutyInfor['dutyName'], $dutyInfor['intStartTime'], $dutyInfor['intEndTime'], $dutyInfor['team_id'], 0, $dutyInfor['WeekNumber'], $dutyInfor['iDay'], $dutyInfor['breakTime'], $dutyInfor['colorID'],
    $dutyInfor['labelId1'], $dutyInfor['rq_Date'], $dutyInfor['rq_Date'], $dutyInfor['rq_Date'], $scheduledPersonId, 0, $dutyInfor['role'], $dutyInfor['dutyduration'],  $dutyInfor['labelId2'],  $dutyInfor['labelId3'],  $dutyInfor['labelId4'],  $dutyInfor['labelId5'],  $dutyInfor['labelId6'],
    $dutyInfor['isNeedCovering'], $dutyInfor['isOverrideOver12'], $dutyInfor['DutyComments'], $dutyInfor['team_id']);
    $existingComment = '';
    if($scheduledPersonId > 0 && !empty($_REQUEST['PersonComments'])) {
		$repository->setComment($dutyInfor['duty_parent_id'], $dutyInfor['rq_Date'], $scheduledPersonId, $dutyInfor['role'], $_REQUEST['PersonComments'], 2);
	}
}
