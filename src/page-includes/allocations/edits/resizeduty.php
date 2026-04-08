<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
date_default_timezone_set('Europe/London');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../function-includes/DBHelper.php';

$dutyid = $_POST['dutyid'];
$OrigLeft =  $_POST['OrigLeft'];
$OrigWidth = $_POST['OrigWidth'];
$NewLeft = $_POST['NewLeft'];
$NewWidth = $_POST['NewWidth'];
$isShiftleader = $_POST['isShiftleader'];
$time = time();
$row = GetAllocationsDetailsByAllocationDutyId($dutyid);

$allocationId = $row['AllocationID'] ?? null;
$DutyName = $row['DutyName'] ?? null;
$Duration = $row['Duration'] ?? null;
$WeekNumber = $row['WeekNumber'] ?? null;
$iDay = $row['iDay'] ?? null;
$StartTime = intval($row['StartTime'] ?? 0);
$EndTime = intval($row['EndTime'] ?? 0);

$SchedulingTeamId = $row['SchedulingTeamId'] ?? 0;
$SchedulingPersonID = $row['SchedulingPersonID']  ?? null;
$DutyDate = $row['DutyDate']  ?? null;
$StartDate = null;
$EndDate =  null;
$dutyProgramId = $row['DutyProgramID1']  ?? null;
$dutyProgramId2 = $row['DutyProgramID2'] ?? null;
$dutyProgramId3 = $row['DutyProgramID3'] ?? null;
$dutyProgramId4 = $row['DutyProgramID4'] ?? null;
$dutyProgramId5 = $row['DutyProgramID5'] ?? null;
$dutyProgramId6 = $row['DutyProgramID6'] ?? null;
$dutyBreakTime = $row['PlannedDutyBreakTime'] ?? null;
$dutyColorId = $row['DutyColourID'] ?? null;
$isNeedCovering = $row['IsNeedCovering'] ?? null;
$isOverrideOver12 = $row['IsOverrideOver12'] ?? null;
$dutyComments = $row['Comments'] ?? null;
$OrigStart = intval($row['StartTime']?? 0);
$OrigEnd = intval($row['EndTime'] ?? 0);
if ($OrigStart >= $OrigEnd){
  $OrigEnd =$OrigEnd + 86400;
}
$OrigDur = abs($OrigEnd - $OrigStart);
if ($OrigEnd==$OrigStart){
  $OrigDur=86400;
}
// How many seconds for the width
$SecsPerPixel = $OrigDur / $OrigWidth ;
$NewDur = $SecsPerPixel * $NewWidth;

if ($OrigLeft == $NewLeft) {
  $NewStart = $OrigStart;
  $NewEnd = RoundFifteenMins($OrigStart + $NewDur);
  $dblStart = $NewStart;
  $dblEnd = $NewEnd;
} else {
  if ($NewLeft<0){
    $OrigEnd=$OrigEnd+86400;
  }
  $NewStart = RoundFifteenMins($OrigEnd - $NewDur);
  $NewEnd = $OrigEnd;
  $dblStart = $NewStart;
  $dblEnd = $NewEnd;
  $Duration =$NewDur;
}
  $countendtime = $dblEnd;
  if($dblEnd >86400) {
	  $dblEnd=$dblEnd-86400;
  }
  $TaskType = 'updateDuty';

  $jobsrow = AllocationJobsCount($dutyid,$countendtime,$allocationId);

  if (($jobsrow['CountJobs']?? 0) != 0) {
	$finalArray = array("strstatus"=>2,"strsmsg"=>'You cannot resize a duty to times less than the Jobs within it. Please remove the Jobs first!.');
  } else {
    $aftermidnight=0;
	if ($NewLeft<0){
		 $aftermidnight=1;
	}
    if ($NewDur>86400){
		$finalArray = array("strstatus"=>1,"strsmsg"=>'You cannot extend duty greater than 24 Hours!.');
	} else if ($NewStart>=86400){
		$finalArray = array("strstatus"=>1,"strsmsg"=>'You cannot resize duty after 24 Hours!.');
	} else {
    $response = EditAllocatedDuty($allocationId,$dutyid,$DutyName, $dblStart, $dblEnd, $SchedulingTeamId, $TaskType,$WeekNumber,$iDay,$dutyBreakTime,$dutyColorId,$dutyProgramId,$StartDate,$EndDate,$DutyDate,$SchedulingPersonID,$aftermidnight,$isShiftleader,$Duration,$dutyProgramId2,$dutyProgramId3,$dutyProgramId4,$dutyProgramId5,$dutyProgramId6,$isNeedCovering,$isOverrideOver12,$dutyComments);
		$response = json_decode($response,true);
		$finalArray = array("strstatus"=>$response['intstatus'],"strsmsg"=>$response['strstatus']);
	}
  }
   echo json_encode($finalArray);
   die();