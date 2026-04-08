<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';

  $jobid = $_POST['jobid'];
  $OrigLeft =  $_POST['OrigLeft'];
  $OrigWidth = $_POST['OrigWidth'];
  $NewLeft = $_POST['NewLeft'];
  $NewWidth = $_POST['NewWidth'];
  $dutyid = $_POST['dutyid']?? 0;
  if (isset($_POST['ForceUpdate'])) {
    $ForceUpdate =$_POST['ForceUpdate'];
  }
  else {
    $ForceUpdate = 0;
  }
  $role = $_POST['role'];
  $pdo = OpenDBLinkA7();
  // Edited job

  $intDutyID = $dutyid;
  $row = fetchEditedUnAllocationJob($jobid);

  $midnightflag = 0;
  $OrigStart = intval($row['StartTime']);
  $OrigEnd = intval($row['EndTime']);
  $DutyStartTime = intval($row['DutyStartTime']);
  $DutyEndTime = intval($row['DutyEndTime']);
  $OrigDur = $OrigEnd - $OrigStart;

  if ($OrigStart>$OrigEnd){
	  $OrigDur = ($OrigEnd+86400) - $OrigStart;
  }

  // How many seconds for the width
  $SecsPerPixel = $OrigDur/ $OrigWidth ;
  $NewDur = $SecsPerPixel * $NewWidth;

  if ($OrigLeft == $NewLeft) {
	$NewStart = $OrigStart;
	$NewEnd = RoundFifteenMins($OrigStart + $NewDur);
} else {
	if ($OrigStart>$OrigEnd){
	  $OrigEnd = $OrigEnd+86400;
	  $midnightflag = 1;
	}
    $NewEnd = $OrigEnd;
 	$NewStart = RoundFifteenMins($OrigEnd - $NewDur);
	if (($OrigEnd - $NewDur)<0){
		$NewStart = RoundFifteenMins(($OrigEnd+86400) - $NewDur);
	}
}

if ($NewEnd>86400){
	$NewEnd=$NewEnd-86400;
	$midnightflag = 1;
}
if ($NewStart>$NewEnd){
	$midnightflag = 1;
}

 if (($NewEnd>$NewStart) && ($DutyStartTime>$DutyEndTime) && (($NewStart<$DutyStartTime) && ($NewEnd<$DutyEndTime)))     {
	$midnightflag = 1;
 }

 if (($DutyStartTime>$DutyEndTime)&& ($NewEnd<$DutyStartTime) )  {
	$midnightflag = 1;
 }
 if ($NewStart>86400){
	$NewStart=$NewStart-86400;
}
  $WeekNumber = $row["WeekNumber"];
  $iday = $row["iDay"];

  $dblStart = $NewStart;
  $dblEnd =  $NewEnd;
  $jobInfor['JobID']  = $row["AllocateJobID"] ?? 0;
  $jobInfor['dutyDate'] = $row['DutyDate'];
  $jobInfor['dutyId'] = !empty($row['DutyStartTime']) ? $row['DutyId'] : 0;
  $jobInfor['Job'] = trim($row["JobName"]);
  $jobInfor['intStartTime'] = $dblStart;
  $jobInfor['intEndTime'] = $dblEnd;
  $jobInfor['info'] = trim($row["Job_Info"] ?? '');
  $jobInfor['contact'] = trim($row["Contact"] ?? '');
  $jobInfor['location'] = trim($row["Location"] ?? '');
  $jobInfor['backcolor'] = trim($row["JobBackColour"]);
  $jobInfor['forecolor'] = trim($row["JobFontColour"]);
  $jobInfor['programme_id'] = trim($row["ProgrammeId"] ?? '');
  $jobInfor['team_id']  = trim($row["schedulingTeamId"]);
  $jobInfor['TaskType'] = 'updatejob';
  $jobInfor['aftermidnight'] = $midnightflag;
  $jobInfor['unallocated'] = 0;
  $jobInfor['JobEditID'] = $row["IsEditedJobAttention"]??0;
  $jobInfor['allocationID'] = $row["AllocationID"];
  $jobInfor['WeekNumber'] = $WeekNumber;
  $jobInfor['iDay'] = $iday;
  $jobInfor['role'] = $role ?? 0;
  $jobInfor['scheduledpersonid'] = $_REQUEST["scheduledpersonid"] ?? '';
  if (!empty($intDutyID)){
	  $willfit =  checkJobOverlap($intDutyID, $dblStart, $dblEnd, $midnightflag, $jobid);
	  if ($willfit == 0) {
      // Do we need to extend the duty?
      $DutyExtend = DutyExtend($jobid, $dblStart, $dblEnd, $row['DutyId']);

      if ($DutyExtend == 1 && $ForceUpdate == 0) {
          $willfit = 2;
      } else {
          $result = AddEditAllocationJob($jobInfor);
          $result=json_decode($result,true);
          $willfit = 0;
      }
	  }
  } else {
	      $result = AddEditAllocationJob($jobInfor);
        $result=json_decode($result,true);
	      $willfit = 0;
  }
echo $willfit;