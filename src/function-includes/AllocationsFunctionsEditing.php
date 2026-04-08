<?php

use Carbon\Carbon;

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
date_default_timezone_set('Europe/London');
include_once 'DBHelper.php';
include_once 'helpers.php';
include_once 'DB_Functions.php';
include_once 'genericfunctions.php';
include_once __DIR__ . '/../page-includes/allocations/weekly/service/TimeDimensionService.php';
include_once __DIR__ . '/masterduty_filter_functions.php';

/**
 * Description : This Old Function Called For Edit Duty
 * @param intEditedID int for Edited Roe Id Infor
 * @param DutyName string for duty Name
 * @param StartTime int for Duty Start Time
 * @param EndTime int for Duty End Time
 * @param BackColour int for Duty BackColour
 * @param FontColour int for Duty FontColour
 *
 */


function EditDuty ($intEditedID, $DutyName, $StartTime, $EndTime, $BackColour, $FontColour) {
  $time = time();
  $strQuery = "SELECT DutyName, StartTime, EndTime, BackColour, FontColour, DepartmentID
               FROM Allocations_webedit
               WHERE  (id = $intEditedID)";

    $db = OpenDatabase();
    $oldduty = sqlsrv_query($db, $strQuery);
    $row = sqlsrv_fetch_array($oldduty);
    $oldDutyName = $row['DutyName'];
    $oldstarttime = $row['StartTime'];
    $oldendtime = $row['EndTime'];
    $intDepartmentID = $row['DepartmentID'];
    if (is_numeric($row["BackColour"])) {
      $OldBackColour = intotohex($row["BackColour"]);
    }
    else {
      $OldBackColour = $row["BackColour"];
    }
    if (is_numeric($row["FontColour"])) {
      $OldFontColour = intotohex($row["FontColour"]);
    }
    else {
      $OldFontColour = $row["FontColour"];
    }

    $dutyhistory = '';
    if ($oldDutyName != $DutyName) {
      $dutyhistory.= "The dutyname was changed from $oldDutyName to $DutyName<br>";
    }
    if (date("H:i", doubletoseconds($oldstarttime)) != date("H:i", doubletoseconds($StartTime)) || date("H:i", doubletoseconds($oldendtime)) != date("H:i", doubletoseconds($EndTime))) {
      $dutyhistory.= "Times were changed from ".date("H:i", doubletoseconds($oldstarttime))."-".date("H:i", doubletoseconds($oldendtime))." to ".date("H:i", doubletoseconds($StartTime))."-".date("H:i", doubletoseconds($EndTime));
    }

    if ($OldBackColour != $BackColour || $OldFontColour != $FontColour) {
      $dutyhistory.= "Colours changed from <span style='background-color:$OldBackColour'><font color='$OldFontColour'>&nbsp;Duty&nbsp;</font></span> to <span style='background-color: $BackColour'><font color='$FontColour'>&nbsp;Duty&nbsp;</font></span><br>";
    }
    if ($dutyhistory != '') {
      $dutyhistory.= ' by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i").'<hr>';

      $dutyhistory = escapeSingleQuotes($dutyhistory);

      $DutyName = substr($DutyName, 0, 50);

      $strQuery = "UPDATE  Allocations_webedit
                   SET
                   DutyName = '$DutyName',
                   StartTime = $StartTime,
                   EndTime = $EndTime,
                   BackColour = '$BackColour',
                   FontColour = '$FontColour',
                   Edited = 1,
                   LastUpdate = $time,
                   History = CONCAT(ISNULL(History,''), '$dutyhistory')
                   WHERE (ID = $intEditedID)";

      sqlsrv_query($db, $strQuery);
    }
    return($intDepartmentID);
}


function GetEditedAllocationIDfromJobID($jobid) {
    $db = OpenDatabase();
  $strQuery = "SELECT           Allocations_webedit.ID AS AllocationID
               FROM             Jobs_webedit
               LEFT OUTER JOIN  Allocations_webedit
               ON               Jobs_webedit.AllocationID = Allocations_webedit.AllocationID
               AND              Jobs_webedit.DepartmentID  = Allocations_webedit.DepartmentID
               WHERE            (Jobs_webedit.ID = $jobid)";

  $rsNewRec = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsNewRec);
  if (is_null($row['AllocationID'])) {
    return (0);
  }
  else {
    return($row['AllocationID']);
  }
}

function DutyExtend ($jobid, $dblStart, $dblEnd, $dutyId) {
  // Get the duty and its details
  $row = GetAllocationsDetailsByAllocationDutyId($dutyId);
  $jobData = fetchEditedUnAllocationJob($jobid);
	$DutyExtend = 0;
	if (!is_null($row['AllocationsDutyID'])) {
		$dblDutyStart = intval($row['StartTime']);
		$dblDutyEnd = intval($row['EndTime']);
		$jobmidnight = $row['DutyDate'] != Carbon::parse($jobData['JobStartTimeLocal'])->format('Y-m-d') ? 1 : 0;

		if ($dblStart<0){
			$dblStart=86400+$dblStart;
		}
		if ($jobmidnight==1){
			if (($dblStart<$dblEnd) && ($dblStart<$dblDutyStart)){
				$dblEnd=86400+$dblEnd;
				$dblStart=86400+$dblStart;
			} else {
				if ($dblStart>$dblEnd){
					$dblEnd=86400+$dblEnd;
				}
			}
		} else {
			if ($dblStart>$dblEnd){
				$dblEnd=86400+$dblEnd;
			}
		}
		if ($dblDutyStart>$dblDutyEnd ){
			$dblDutyEnd=86400+$dblDutyEnd;
		}

		if ($dblStart<$dblDutyStart){
			$DutyExtend = 1;
		}
		if ($dblEnd>$dblDutyEnd){
			$DutyExtend = 1;
		}
	}
  return($DutyExtend);
}

function UpdateEditedJob($intEditedJobID, $strEditJobName, $dblEditJobStart, $dblEditJobEnd, $backcolour, $fontcolour, $strEditJobProgramme,$role) {
  $time = time();
   $userid = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  if($role==1){
    $isEdited=1;
  }else{
    $isEdited=0;
  }

  $pdo = OpenDBLinkA7();
  // Get the duty and its details

      $strQuery = "Select A.aftermidnight,AJ.aftermidnight as jobmidnight,AJ.JobName,AJ.StartTime as JobStartTime,AJ.EndTime as JobEndTime,
      A.SchedulingTeamId,AJ.Programme,AJ.JobBackColour,AJ.JobFontColour,A.ID as AllocationID,A.StartTime,A.EndTime,A.WeekNumber
      from Allocations A INNER JOIN Allocations_jobs AJ
      ON A.ID = AJ.AllocationID and AJ.ID=$intEditedJobID";

  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $strOrigJobName = $row['JobName'];
  $dblOrigJobStart = $row['JobStartTime'];
  $dblOrigJobEnd = $row['JobEndTime'];
  $intTeamID = $row['SchedulingTeamId'];
  $origProg = $row['Programme'];
  $WeekNumber = $row['WeekNumber'];
  $intDutyID = $row['AllocationID'];

  if (is_numeric($row["JobBackColour"])) {
    $strOrigBackColour = intotohex($row["JobBackColour"]);
  }
  else {
    $strOrigBackColour = $row["JobBackColour"];
  }
  if (is_numeric($row["JobFontColour"])) {
    $strOrigFontColour = intotohex($row["JobFontColour"]);
  }
  else {
    $strOrigFontColour = $row["JobFontColour"];
  }


  if (!is_null($row['AllocationID'])) {
    $AllocationID = $row['AllocationID'];
    $dblDutyStart = intval($row['StartTime']);
    $dblDutyEnd = intval($row['EndTime']);
    $dblNewDutyStart = intval($row['StartTime']);
    $dblNewDutyEnd = intval($row['EndTime']);
	$aftermidnight = $row['aftermidnight'];
	$jobaftermidnight = $row['jobmidnight'];

	$changedutyflag=0;
	if ($dblEditJobStart<0){
		$dblEditJobStart=86400+$dblEditJobStart;
	}
	if ($dblDutyEnd>$dblDutyStart){
	    if ($dblEditJobStart<$dblDutyStart){
			$dblNewDutyStart = $dblEditJobStart;
			$changedutyflag=1;
		}
	    if ($dblEditJobEnd>$dblDutyEnd){
			$dblNewDutyEnd = $dblEditJobEnd;
			$changedutyflag=1;
		}
	} else {
		$dblDutyEnd=$dblDutyEnd+86400;
		if ($jobaftermidnight==1){
			if ($dblEditJobStart>$dblEditJobEnd){
				if ($dblEditJobStart<$dblDutyStart){
					$dblNewDutyStart = $dblEditJobStart;
					$changedutyflag=1;
				}
				if ($dblEditJobEnd>$dblDutyEnd){
					$dblNewDutyEnd = $dblEditJobEnd;
					$changedutyflag=1;
				}
			}	else {
				if (($dblEditJobEnd+86400)>$dblDutyEnd){
					$dblNewDutyEnd = $dblEditJobEnd;
					$changedutyflag=1;
				}
			}
		} else {
			if ($dblEditJobStart<$dblDutyStart){
					$dblNewDutyStart = $dblEditJobStart;
					$changedutyflag=1;
			}
			if ($dblEditJobEnd>$dblDutyEnd){
				$dblNewDutyEnd = $dblEditJobEnd;
				$changedutyflag=1;
			}
		}
	}
	if ($dblNewDutyEnd>86400){
		$dblNewDutyEnd=$dblNewDutyEnd-86400;
		$jobaftermidnight=1;
	}
	if($changedutyflag==1){
    $current_User = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
	  $dutyhistory = "Job $strOrigJobName was edited by ".$current_User." on ".gmdate("d/m/Y")." at ".gmdate("H:i");
      $dutyhistory.= " <br>Times automatically changed from ".gmdate("H:i", intval($dblDutyStart))."-".gmdate("H:i", intval($dblDutyEnd))." to ".gmdate("H:i", intval($dblNewDutyStart))."-".gmdate("H:i", intval($dblNewDutyEnd));
      $dutyhistory.= "<hr>";
      $dutyhistory = escapeSingleQuotes($dutyhistory);
      $strQuery = "UPDATE Allocations SET StartTime = $dblNewDutyStart,EndTime = $dblNewDutyEnd,isEdited = $isEdited WHERE (id = $AllocationID)";
	  $stmt = $pdo->prepare($strQuery);
      $stmt->execute();
	   $status = 1;
		$historyType = 8;
      if($role==1){
        saveDataAllocationsPublish($WeekNumber,$intTeamID,$AllocationID);
      }

	   saveHistory($intDutyID,$historyType,$userid,$dutyhistory,$status);
    }

  }

  $jobhistory = '';

  if ($strOrigJobName != $strEditJobName) {
    $jobhistory.= "Name changed from '".$strOrigJobName."' to '".$strEditJobName."'<br>";
  }
  if (intval($dblOrigJobStart) != intval($dblEditJobStart) || intval($dblOrigJobEnd) != intval($dblEditJobEnd)) {
    $jobhistory.= "Times changed from  ".gmdate("H:i", intval($dblOrigJobStart))."-".gmdate("H:i", intval($dblOrigJobEnd))." to ".gmdate("H:i", intval($dblEditJobStart))."-".gmdate("H:i", intval($dblEditJobEnd))."<br>";
  }
  if ($strOrigBackColour != $backcolour || $strOrigFontColour != $fontcolour) {
    $jobhistory.= "Colours changed from <span style='background-color:$strOrigBackColour'><font color='$strOrigFontColour'>&nbsp;Job&nbsp;</font></span> to <span style='background-color: $backcolour'><font color='$fontcolour'>&nbsp;Job&nbsp;</font></span><br>";
  }
  if ($origProg != $strEditJobProgramme) {
      $jobhistory.= "Job Label changed from $origProg to $strEditJobProgramme<br>";
  }

  if (isset($jobhistory) && $jobhistory != '') {
    $current_User = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
    $jobhistory = "Job edited by ".$current_User." on ".date("d/m/Y")." at ".date("H:i")."<br>".$jobhistory."<hr>";
    $jobhistory = escapeSingleQuotes($jobhistory);

    $strEditJobName = escapeSingleQuotes($strEditJobName);
    $strEditJobProgramme = escapeSingleQuotes($strEditJobProgramme);

      try{
            $strQuery = "UPDATE Allocations_jobs
            SET
            JobName = N'$strEditJobName',
            StartTime = $dblEditJobStart,
            EndTime = $dblEditJobEnd,
            JobBackColour = '$backcolour',
            JobFontColour = '$fontcolour',
            Programme = '$strEditJobProgramme',
			aftermidnight = '$jobaftermidnight'
            WHERE (ID = $intEditedJobID)";

            $stmt = $pdo->prepare($strQuery);
            $stmt->execute();
          }catch(Exception $e){
            logger()->critical('DB Error', (array) $e);
          }

        if($role==1){
            $query = "UPDATE Allocations SET isEdited = $isEdited WHERE (id = $AllocationID)";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            saveDataAllocationsPublish($WeekNumber,$intTeamID,$AllocationID);
          }


    $historyType = 9;
    $status = 1;
    $pdo = OpenDBLinkA7();

    try {
      $sql = "exec [dbo].[usp_mod_AllocationHistory] ?,?,?,?,?";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(1, $intEditedJobID, PDO::PARAM_INT);
      $stmt->bindParam(2, $historyType, PDO::PARAM_INT);
      $stmt->bindParam(3, $userid, PDO::PARAM_INT);
      $stmt->bindParam(4, $jobhistory, PDO::PARAM_STR);
      $stmt->bindParam(5, $status, PDO::PARAM_INT);
      $stmt->execute();
    }catch(Exception $e){
          logger()->critical('DB Error', (array) $e);
    }

 }
  return($intTeamID);
}

/**
* This function is used to get duty details in daily allocations.
*
* @param $intDutyID is dutyid
*
* @return $row Returns the array.
*/

function GetDutyDetails($intDutyID){

  $strQuery = "select asp.ASP_SchedulingPersonID as SchedulingPersonID, AD_AllocationsDutyID AllocationsDutyID,	AD_AllocationsID AllocationsID,	AD_DutyName DutyName,	AD_Duration Duration,	AD_iDay AD_iDay, AD_StartTimeSec StartTime,	AD_EndTimeSec EndTime,	AD_DutyBreakTime AD_DutyBreak,	AD_DutyDate DutyDate,	AD_DutyStartTimeUTC StartDate,	AD_DutyEndTimeUTC EndDate,	AD_MasterDutyID MasterDutyID,	AD_DutyType DutyType,	AD_DutyStatus DutyStatus,	AD_DutyColourID DutyColourID,	AD_Comments Comments,	AD_isAttention isAttention,	AD_isRequest isRequest,	AD_DutyProgramID1 DutyProgramID1,	AD_DutyProgramID2 DutyProgramID2,	AD_DutyProgramID3 DutyProgramID3,	AD_DutyProgramID4 DutyProgramID4,	AD_DutyProgramID5 DutyProgramID5,	AD_DutyProgramID6 DutyProgramID6,	AD_PlannedDuration PlannedDuration,	AD_PlannedDutyBreakTime PlannedDutyBreakTime, AD_IsNeedCovering IsNeedCovering,	AD_IsOverrideOver12 IsOverrideOver12,	AD_IsDutyEdited IsDutyEdited,	AD_CreatedBy CreatedBy,	AD_CreatedDate CreatedDate,	AD_UpdatedBy UpdatedBy,	AD_UpdatedDate UpdatedDate,	AD_IsEditedDutyAttention IsEditedDutyAttention, ASP_AllocationsSPID as AllocationsSPID
  from AllocationsDuties
  LEFT JOIN AllocationsScheduledPersons asp ON asp.ASP_AllocationsDutyID = AllocationsDuties.AD_AllocationsDutyID
  where AD_AllocationsDutyID = :dutyId";

  $pdo = OpenDBLinkA7();
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':dutyId', $intDutyID, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

 return $row;
}

function altDragCopyJob($intJobID, $intDutyID, $dblJobStart, $dblJobEnd,$role){
  $current_User = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
  $current_User_Id = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

  try {
    $pdo = OpenDBLinkA7();
    $sql = "exec usp_DragCopyJobTo ?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $intJobID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDutyID, PDO::PARAM_INT);
    $stmt->bindParam(3, $current_User, PDO::PARAM_STR);
    $stmt->bindParam(4, $current_User_Id, PDO::PARAM_INT);
    $stmt->bindParam(5, $role, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return json_encode($result);
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
  return false;
}

function saveHistory($intEditedJobID,$historyType,$userid,$jobhistory,$status){

  $pdo = OpenDBLinkA7();

    try {
          $sql = "exec [dbo].[usp_mod_AllocationHistory] ?,?,?,?,?";
          $stmt = $pdo->prepare($sql);
          $stmt->bindParam(1, $intEditedJobID, PDO::PARAM_INT);
          $stmt->bindParam(2, $historyType, PDO::PARAM_INT);
          $stmt->bindParam(3, $userid, PDO::PARAM_INT);
          $stmt->bindParam(4, $jobhistory, PDO::PARAM_STR);
          $stmt->bindParam(5, $status, PDO::PARAM_INT);
          $stmt->execute();
    }catch(Exception $e){
        logger()->critical('DB Error', (array) $e);
    }

}

function saveDataAllocationsPublish($WeekNumber,$teamId,$allocationId){

  $pdo = OpenDBLinkA7();

    try {
    $sql = "exec [dbo].[usp_mod_PublishIndividulAllocations] ?,?,?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $WeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(2, $teamId, PDO::PARAM_INT);
    $stmt->bindParam(3, $allocationId, PDO::PARAM_INT);
    $stmt->execute();
    }catch(Exception $e){
        logger()->critical('DB Error', (array) $e);
    }
}

/**
* This function is used to get schedulingPersonId from Allocations_edit table.
*
* @param $allocationEditID This param is AllocationId in Allocation_edit table
*
* @return $result Returns the array
*/
function  getweekDetailsAllocationEdit($allocationEditID,$isEdited){

  $pdo = OpenDBLinkA7();
  $sql = "exec [dbo].[usp_getweekDetailsFromAllocation] ?,?";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1, $allocationEditID, PDO::PARAM_INT);
  $stmt->bindParam(2, $isEdited, PDO::PARAM_INT);

  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result;
}



/**
* Description : Add /Edit /Copy  unallocated Job in daily Allocation
*
* @param $masterJobID int This param contains the JobID information
* @param $Job string This param contains the JobName information
* @param $intStartTime int This param contains the JobStartTime information
* @param $intEndTime int This param contains the JobEndTime information
* @param $team_id int This param contains the team_id information
* @param $TaskType string This param contains the TaskType (Add/EDIT/COpy) information
* @param $allocationEditID int This param contains the allocationEditID (if Edited then Required)     information
* @param $WeekNumber int This param contains the WeekNumber information
* @param $iDay int This param contains the iDay information
* @param $unallocated int This param contains the unallocated type information
* @param $programme_id int This param contains the Program information
* @param $location int This param contains the Location information
* @param $contact date This param contains the contact No information

*
* @return $resultJson Returns the json.
*/

function  AddEditAllocationJob(array $jobInfor){

  $AllocationDutyID = $jobInfor['dutyId'];
  $SchedulingPersonID = $jobInfor['scheduledpersonid'];
  try {
    $pdo = OpenDBLinkA7();
    $jobRole = ($jobInfor['role'] == 1) ? 1 : 0;
    $current_User_NetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $current_User_Id = GetUserIdbyNetlogin($current_User_NetLogin);
    $sql = "exec [dbo].[usp_mod_Allocation_Jobs] @JobID = :jobId,
    @AllocationsDutyID = :allocationDutyID, @JobName = :jobName, @StartTime = :startTime, @EndTime = :endTime, @DutyDate = :dutyDate,
    @Info = :info, @JobBackColour = :jobBackColour, @JobFontColour = :jobFontColour, @TeamId = :teamId,
    @Contact	= :contact, @Location	= :location, @ProgrammeId = :programmeId , @AfterMidnight = :aftermidnight, @SchedulingPersonID = :schedulingPersonID,
    @Role = :role, @UserID = :current_User_Id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':jobId', $jobInfor['JobID'], PDO::PARAM_INT);
    $stmt->bindParam(':allocationDutyID', $AllocationDutyID, PDO::PARAM_INT);
    $stmt->bindParam(':jobName', $jobInfor['Job'], PDO::PARAM_STR);
    $stmt->bindParam(':startTime', $jobInfor['intStartTime'], PDO::PARAM_INT);
    $stmt->bindParam(':endTime', $jobInfor['intEndTime'], PDO::PARAM_INT);
    $stmt->bindParam(':dutyDate', $jobInfor['dutyDate'], PDO::PARAM_STR);
    $stmt->bindParam(':info', $jobInfor['info'], PDO::PARAM_STR);
    $stmt->bindParam(':jobBackColour', $jobInfor['backcolor'], PDO::PARAM_STR);
    $stmt->bindParam(':jobFontColour', $jobInfor['forecolor'], PDO::PARAM_STR);
    $stmt->bindParam(':teamId', $jobInfor['team_id'], PDO::PARAM_INT);
    $stmt->bindParam(':contact', $jobInfor['contact'], PDO::PARAM_STR);
    $stmt->bindParam(':location', $jobInfor['location'], PDO::PARAM_STR);
    $stmt->bindParam(':programmeId', $jobInfor['programme_id'], PDO::PARAM_INT);
    $stmt->bindParam(':aftermidnight', $jobInfor['aftermidnight'], PDO::PARAM_INT);
    $stmt->bindParam(':schedulingPersonID', $SchedulingPersonID, PDO::PARAM_INT);
    $stmt->bindParam(':role', $jobRole, PDO::PARAM_INT);
    $stmt->bindParam(':current_User_Id', $current_User_Id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return json_encode($result);
} catch (PDOException $e) {
  logger()->critical('Add Job Error', (array) $e);
}
return false;
}

function  AddNewJob ($intAllocationID, $date, $title, $dblstarttime, $dblendtime, $backcolour, $fontcolour, $intDepartmentID, $strJobProgramme) {
  $week = bbcweeknumber($date);
  $day = getdayofweek($date);
  $time = time();

  $title = escapeSingleQuotes($title);
  $current_User = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
  $jobhistory = "New job created by ".$current_User." on ".date("d/m/Y")." at ".date("H:i")."<br>";
  $jobhistory.= "The job was called ".$title." and the times were ".gmdate("H:i", doubletoseconds($dblstarttime))."-".gmdate("H:i", doubletoseconds($dblendtime))."<br>";
  $jobhistory.= "The job was created as unassigned<hr>";

  $jobhistory = escapeSingleQuotes($jobhistory);

  $strQuery = "INSERT
            INTO Jobs_webedit(StaffNumber, AllocateJobID, AllocationID, WeekNumber, iDay, JobName, StartTime, EndTime, JobBackColour, JobFontColour, History, DepartmentID, canbedeleted, Programme, LastUpdate)
            VALUES (
                '0',
                $time,
                $intAllocationID,
                $week,
                $day,
                N'$title',
                $dblstarttime,
                $dblendtime,
                '$backcolour',
                '$fontcolour',
                '$jobhistory',
                $intDepartmentID,
                1,
                '$strJobProgramme',
                $time)";

        $db = OpenDatabase();
        $results = sqlsrv_query($db, $strQuery);
        $sqlQuery = "SELECT SCOPE_IDENTITY() as computed";
        $rs = sqlsrv_query($db, $sqlQuery);
        $result = sqlsrv_fetch_array($rs, SQLSRV_FETCH_ASSOC);
        return ($result['computed']);

}


function CopyEditedJob($intJobID) {

	$pdo = OpenDBLinkA7();
	try {
		$oldAllocationId = $intJobID;
    $sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
		$query = "exec [dbo].[usp_Edit_Job] 'COPY','".$sessUserNetLogin."',".$oldAllocationId;
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		$stmt->fetch(PDO::FETCH_ASSOC);
	} catch(Exception $e) {
		logger()->critical('DB Error', (array) $e);
	}
}

/**
  * Swap and assign the duties to staff belong to this team and for this day
  * @param  $request Request
  * @return array
  */
function SwapDuty($request) {
  $pdo = OpenDBLinkA7();
  try {

    $oldAllocationDutyId = $request['allocationId'];
    $oldIsEdited = $request['isEdited'];
    $newScheduledPersonId = $request['scheduledPerson'];
    $newAllocationDutyId = $request['newAllocationId'];
    $newAllocationSPID = $request['newAllocationSPID'];
    $newIsEdited = $request['newIsEdited'];
	  $isShiftLeader= "@IsShiftleader = ".$request['isShiftLeader'];
	  $weekNumber= $request['weekNumber'];
	  $teamId= $request['teamId'];
    $userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    if($request['isAssign'] == 1) {
		$edittype='ASSIGN';
    } else {
 	    $edittype='SWAP';
    }
    $sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($sessUserNetLogin) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    //get new allocation details

    $oldAllocationData = GetAllocationsDetailsByAllocationDutyId($oldAllocationDutyId);
    $oldAllocationSPId = $oldAllocationData['AllocationsSPID'] ?? $oldAllocationData['AllocationsDutyID'];
    $allocationID = $oldAllocationData['AllocationID'];
    if (in_array($edittype, ['ASSIGN', 'SWAP'])){
	    $query = "exec usp_Edit_Allocations '".$edittype."','".$sessUserNetLogin."',@pAllocationsID=" . $allocationID . ", @Fromid=" . $oldAllocationSPId . ", @ToId=" . $newAllocationSPID . ", @pSchedulingpersonid=" . $newScheduledPersonId ;
    } else {
        $wkno= "@WeekNumber=".$weekNumber;
        $fromid="@FromID=".$oldAllocationSPId;
        $steamid="@teamId=".$teamId;
        $scheduledpersonid="@pSchedulingPersonID=".$newScheduledPersonId;
        $query = "exec [dbo].[usp_Edit_Allocations] 'ASSGNTOADDPERSON','".$sessUserNetLogin."',".$wkno.",".$fromid.",".$steamid.",".$scheduledpersonid;
    }
		$query1 = "
    SELECT     chargingdutydate,
           UD_DisplayFirstName  displayfirstname,
           UD_DisplayLastName displaylastname,
           AD_DutyName dutyname,
           establishcode,
           establishcodedescription,
           isactual,
           activitycodename,
           AC.description,
           unitprice,
           chargewbscodename,
           establishcode,
           establishcodedescription,
           comments,
           contact,
           telephone,
           quantity
    FROM       chargingdutymapping_link cdml
    INNER JOIN establishcode EC ON         EC.establishcodeid = cdml.estabcodeid
    INNER JOIN activitycode AC ON         AC.activitycodeid = cdml.activitycodeid
    INNER JOIN chargewbscode CWC ON         CWC.chargewbscodeid = cdml.chargecodeid
    INNER JOIN AllocationsScheduledPersons AL ON         al.ASP_AllocationsSPID = cdml.allocationid
    INNER JOIN AllocationsDuties AD on AD.AD_AllocationsDutyID = ASP_AllocationsDutyID
    INNER JOIN UserDetails SP ON         SP.UD_UserID = cdml.personid
    WHERE      cdml.allocationid IN ($newAllocationDutyId, $oldAllocationDutyId)";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->execute();
    $chargingResult = $stmt1->fetchAll(PDO::FETCH_ASSOC);
	  $stmt = $pdo->prepare($query);
	  $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
	  if(($result['SPExecStatus'] == 0) && (empty($result['errorMessage'])) && (count($chargingResult) > 0) && ($request['isShiftLeader']==1))
	  {
			$chargingDate = date('d/m/Y', strtotime($chargingResult[0]['ChargingDutyDate']));
			$query2 = "select Email, schedulingTeamName from schedulingTeams where schedulingTeamId = $teamId";
			$stmt2 = $pdo->prepare($query2);
			$stmt2->execute();
			$emailResult = $stmt2->fetch(PDO::FETCH_ASSOC);
			$mail = new PHPMailer\PHPMailer\PHPMailer();
			$mail->isSMTP();
			$mail->SMTPDebug = 0;
			$mail->Host = getenv('SMTP_HOST');
			$mail->Port = 25;
			$mail->setFrom('noreply@bbc.co.uk', 'Allocate');
			$mailAdd = $emailResult['Email'];
			$schedulingTeamName = $emailResult['schedulingTeamName'];
			$arrMailAdd = explode(";", $mailAdd);
			for($intCount = 0; $intCount < count($arrMailAdd); $intCount++)
			{
			  $mail->addAddress($arrMailAdd[$intCount ]);
			}
			$mail->Subject = "Charging on $chargingDate for $schedulingTeamName has been deleted because a Shiftleader swapped or unassigned a duty";
			require_once __DIR__ . '/../page-includes/allocations/edits/ChargingEmail.php';
			$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
            $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
            $htmlStr = '<style>'.file_get_contents(getenv('EMAIL_CSS')).'</style>
            <body><img alt="Banner" src="cid:pobanner" /><br><br>'.$html;
			$mail->msgHTML($htmlStr);
			$mail->send();
	  }
	  if ( $request['isShiftLeader']==1){
      /* publish  old allocation by shiftleadeer */
      $oldDutyDetails = GetAllocationsDetailsByAllocationDutyId($oldAllocationDutyId);
      $query = "exec [dbo].[usp_mod_PublishIndividulAllocations] ?,?,?";
      $stmt = $pdo->prepare($query);
      $stmt->bindValue(1, $allocationID, PDO::PARAM_INT);
      $stmt->bindValue(2, $oldAllocationDutyId, PDO::PARAM_INT);
      $stmt->bindValue(3, $oldDutyDetails['AllocationsSPID'] ?? 0, PDO::PARAM_INT);
      $stmt->execute();

      /* publish  new allocation by shiftleadeer */
      $newDutyDetails = GetAllocationsDetailsByAllocationDutyId($newAllocationDutyId);
      $query = "exec [dbo].[usp_mod_PublishIndividulAllocations] ?,?,?";
      $stmt = $pdo->prepare($query);
      $stmt->bindValue(1, $allocationID, PDO::PARAM_INT);
      $stmt->bindValue(2, $newAllocationDutyId, PDO::PARAM_INT);
      $stmt->bindValue(3, $newDutyDetails['AllocationsSPID'] ?? 0, PDO::PARAM_INT);
      $stmt->execute();
	  }
    echo $result=json_encode($result);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
}

function candropjob ($intDutyID, $intJobID, $intDropPos, $midnightJob = 0) {

  $pdo = OpenDBLinkA7();
  // Get the job to move...
  // Get the edited jobs start and end time
  $jobrow = fetchEditedUnAllocationJob($intJobID);

  $jobstarttime  = intval($jobrow['StartTime']);
  $jobendtime  = intval($jobrow['EndTime']);
  $aftermidnight = $midnightJob;
  $JobDuration = $jobendtime - $jobstarttime;

  /*
  * =====This function is used in ctrl+ drag======
  */
  if ($intDropPos != -1) {
    $result = GetTimeInDutyFromOffset($intDutyID, $intDropPos);
    $dutyStartTime = $result['dutyStartTime'];
    $dutyEndTime = $result['dutyEndTime'];
    $jobstarttime = $result['StartTime'];
    if ($dutyEndTime < $dutyStartTime){
      if (($jobstarttime >= 86400 || $jobstarttime <= $dutyEndTime-86400) ||  ($jobendtime >= 86400 || $jobendtime <= $dutyEndTime-86400) ){
        $aftermidnight=1;
      }
    }
    if($result['StartTime']>86400){
      $jobstarttime = $result['StartTime']-86400;
    }
    $jobendtime = $jobstarttime + $JobDuration;
    if($jobendtime>86400){
      $jobendtime = $jobendtime-86400;
    }
  }

	$pdo = OpenDBLinkA7();

	if (!is_numeric($intDutyID)) {
		$intDutyID = 0;
	}

  $sql = "SELECT dbo.ufn_check_JobOverLapNewJob(".$intDutyID.",'".$jobstarttime."','".$jobendtime."'," . $aftermidnight . ", '" . $intJobID . "') AS JobCount";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $jobrow = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($jobrow['JobCount'] == 0) {
    $retvalue = 0;
  } else {
    $retvalue = 1;
  }

	$arrResult['Success'] = $retvalue;
	$arrResult['StartTime'] = $jobstarttime;
	$arrResult['EndTime'] = $jobendtime;
  $arrResult['aftermidnight'] = $aftermidnight;
	return($arrResult);
}

function candropshiftjob ($intDutyID, $intJobID, $intDropPos) {

  $pdo = OpenDBLinkA7();
  // Get the job to move...
  // Get the edited jobs start and end time

  $query = "SELECT StartTime, EndTime FROM  Allocations_jobs WHERE (ID = $intJobID)";

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $jobrow = $stmt->fetch(PDO::FETCH_ASSOC);

  $jobstarttime  = intval($jobrow['StartTime']);
  $jobendtime  = intval($jobrow['EndTime']);
  $JobDuration = $jobendtime - $jobstarttime;

  $query = "SELECT COUNT(*) AS CountJobs
  FROM  Allocations_jobs
  INNER JOIN Allocations ON Allocations_jobs.AllocationID = Allocations.ID
  WHERE (Allocations.ID = $intDutyID)
  AND (Allocations_jobs.StartTime < $jobendtime)
  AND (Allocations_jobs.EndTime > $jobstarttime)
  AND Allocations_jobs.ID <> $intJobID
  and (Allocations_jobs.IsActive = 1 or Allocations_jobs.IsActive is null)";

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $jobrow = $stmt->fetch(PDO::FETCH_ASSOC);
  $arrResult['Success'] = $jobrow['CountJobs'];
  $arrResult['StartTime'] = $jobstarttime;
  $arrResult['EndTime'] = $jobendtime;

  return($arrResult);
}

/**
 * This function is used to get Job Row Information using Job ID from Allocation Job tables
 *
 * @param $jobid This param contains the Job ID information
 *
 * @return Integer Return [] information of Data.
 */

function fetchEditedUnAllocationJob($jobId,$TeamId = 0)
{
  try {
        $pdo = OpenDBLinkA7();
        $sql ="
        SELECT AD.AD_AllocationsID as AllocationID, AD.AD_AllocationsDutyID as DutyId, AD.AD_DutyDate as DutyDate, AJ.AJ_AllocateJobID as AllocateJobID, AL.AL_WeekNumber as WeekNumber, AD.AD_iDay as iDay, AJ.AJ_Contact as Contact, AJ.AJ_Location as Location, AJ.AJ_JobName as JobName,
        AJ.AJ_JobStartTimeSec as StartTime, AJ.AJ_JobEndTimeSec as EndTime, AJ.AJ_JobStartTimeLocal as JobStartTimeLocal,AJ_JobBGColour as JobBackColour, AJ_JobFontColour as JobFontColour, AJ.AJ_AllocateJobID as ID, AL.AL_SchedulingTeamID as schedulingTeamId,
        ALS.ASP_SchedulingPersonID as SchedulingPersonID, AJ.AJ_ProgrammeID as ProgrammeId, AJ.AJ_JobInfo as Job_Info, AJ.AJ_IsEditedJobAttention as IsEditedJobAttention, AD_StartTimeSec as DutyStartTime, AD_EndTimeSec as DutyEndTime
		    FROM  AllocationsJobs AJ (NOLOCK)
			  INNER JOIN AllocationsDuties AD (NOLOCK) on AJ.AJ_AllocationsDutyID = AD.AD_AllocationsDutyID
			  INNER JOIN Allocations AL (NOLOCK) on AL.AL_AllocationsID = AD.AD_AllocationsID
			  LEFT JOIN AllocationsScheduledPersons ALS (NOLOCK) on ALS.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID
			  WHERE AJ.AJ_AllocateJobID = :allocationId;
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':allocationId', $jobId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
   }
  return false;

}

function AllocationJobsCount($dutyId,$EndTime,$AllocationId)
{
  try {
        $pdo = OpenDBLinkA7();
        $sql ="exec [dbo].[usp_get_AllocationJobsCount] ?,?,?";
	    $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $dutyId, PDO::PARAM_INT);
        $stmt->bindParam(2, $EndTime, PDO::PARAM_INT);
        $stmt->bindParam(3, $AllocationId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
   }
  return false;

}


/**
 * Description : This Function Workes For delete unallocated Jobs
 * @param $jobId int for JOB ID
 * @param $isEdited check editedStatus
 * return json as result
 */

function  deleteUnallocatedJob($JobId,$isEdited){

  try {
    $pdo = OpenDBLinkA7();
    $current_UserNAme = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
    $current_User = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $current_User = GetUserIdbyNetlogin($current_User);
    $status = 1;
    $strstatus = 'success';
    $sql = "exec [dbo].[usp_delete_Unallocated_Job] ?,?,?,?,?";
    $status = 1;
    $strstatus = 'success';

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $JobId, PDO::PARAM_INT);
    $stmt->bindParam(2, $current_User, PDO::PARAM_INT);
    $stmt->bindParam(3, $current_UserNAme, PDO::PARAM_STR);
    $stmt->bindParam(4, $status, PDO::PARAM_INT);
    $stmt->bindParam(5, $strstatus, PDO::PARAM_STR);

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $resultJson = json_encode($result);
    return $resultJson;

} catch (PDOException $e) {
  logger()->critical('db error', (array) $e);
}
return false;
}

/**
* Description : This function is used to edit duty in daily allocations.

* @param $DutyID int This param contains the DutyID information
* @param $dutyName string This param contains the DutyName information
* @param $intStartTime int This param contains the DutyStartTime information
* @param $intStartTime int This param contains the DutyStartTime information
* @param $team_id int This param contains the team_id information
* @param $TaskType string This param contains the TaskType (Add/EDIT) information
* @param $allocationEditID int This param contains the allocationEditID (if Edited then Required)     information
* @param $WeekNumber int This param contains the WeekNumber information
* @param $iDay int This param contains the iDay information
* @param $breakTimeSec int This param contains the breakTime information
* @param $colorID int This param contains the colorID information
* @param $labelId1 int This param contains the Program labelId1 information
* @param $StartDate datetime This param contains the Duty StartDate information
* @param $EndDate datetime This param contains the Duty EndDate information
* @param $DutyDate datetime This param contains the  DutyDate information
* @param $SchedulingPersonID int This param contains the  SchedulingPersonID information
* @param $labelId2 int This param contains the Program labelId2 information
* @param $labelId3 int This param contains the Program labelId3 information
* @param $labelId4 int This param contains the Program labelId4 information
* @param $labelId5 int This param contains the Program labelId5 information
* @param $labelId6 int This param contains the Program labelId6 information
* @param $isNeedCovering int This param contains if the duty needs covering
* @param $isOverrideOver12 int This param contain if the duty can override over 12
*
* @return $resultJson Returns the json.
*/

function EditAllocatedDuty($allocationsID,$DutyID,$dutyName, $intStartTime, $intEndTime, $team_id, $TaskType,$WeekNumber,$iDay,$breakTimeSec,$colorID,$labelId1,$StartDate,$EndDate,$DutyDate,$SchedulingPersonID,$aftermidnight,$role,$duration,$labelId2,$labelId3,$labelId4,$labelId5,$labelId6,$isNeedCovering,$isOverrideOver12,$dutyComments, $teamId=0,$allocatespid = 0){

  try {
    $pdo = OpenDBLinkA7();

    $current_User = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

    $sql = "exec [dbo].[usp_EditDuty]
    @AllocationsID = :allocationsID,
    @DutyName = :dutyName,
    @StartTime	= :startTime,
    @EndTime = :endTime,
    @BreakTime = :breakTime,
    @Duration	=	:duration,
    @DutyColour	= :dutyColour,
    @DutyLable = :dutyLable,
    @DutyLable2	= :dutyLable2,
    @DutyLable3 = :dutyLable3,
    @DutyLable4	= :dutyLable4,
    @DutyLable5	= :dutyLable5,
    @DutyLable6	= :dutyLable6,
    @pNetLogin	= :currentUser,
    @IsShiftleader = :isShiftleader,
    @IsNeedCovering	= :isNeedCovering,
    @IsOverrideOver12	= :isOverrideOver12,
    @AllocationsDutyID	=	:allocationsDutyID,
    @AllocationsSPID	= :allocationsSPID,
    @DutyDate		=		:dutyDate,
    @pSchedulingPersonID	=	:schedulingPersonID,
    @pDutyComments = :dutyComments,
    @SchedulingTeamID = :schTeamId";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':allocationsID', $allocationsID, PDO::PARAM_INT);
    $stmt->bindValue(':dutyName', $dutyName, PDO::PARAM_STR);
    $stmt->bindValue(':startTime', $intStartTime, PDO::PARAM_INT);
    $stmt->bindValue(':endTime', $intEndTime, PDO::PARAM_INT);
    $stmt->bindValue(':breakTime', $breakTimeSec, PDO::PARAM_INT);
    $stmt->bindValue(':duration', $duration, PDO::PARAM_INT);
    $stmt->bindValue(':dutyColour', $colorID, PDO::PARAM_INT);
    $stmt->bindValue(':dutyLable', $labelId1, PDO::PARAM_INT);
    $stmt->bindValue(':dutyLable2', $labelId2, PDO::PARAM_INT);
    $stmt->bindValue(':dutyLable3', $labelId3, PDO::PARAM_INT);
    $stmt->bindValue(':dutyLable4', $labelId4, PDO::PARAM_INT);
    $stmt->bindValue(':dutyLable5', $labelId5, PDO::PARAM_INT);
    $stmt->bindValue(':dutyLable6', $labelId6, PDO::PARAM_INT);
    $stmt->bindValue(':currentUser', $current_User, PDO::PARAM_STR);
    $stmt->bindValue(':isShiftleader', $role, PDO::PARAM_INT);
    $stmt->bindValue(':isNeedCovering', $isNeedCovering, PDO::PARAM_INT);
    $stmt->bindValue(':isOverrideOver12', $isOverrideOver12, PDO::PARAM_INT);
    $stmt->bindValue(':allocationsDutyID', $DutyID, PDO::PARAM_INT);
    $stmt->bindValue(':allocationsSPID', $allocatespid, PDO::PARAM_INT);
    $stmt->bindValue(':dutyDate', $DutyDate, PDO::PARAM_STR);
    $stmt->bindValue(':schedulingPersonID', $SchedulingPersonID, PDO::PARAM_INT);
    $stmt->bindValue(':dutyComments', $dutyComments, PDO::PARAM_STR);
    $stmt->bindValue(':schTeamId', $teamId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $returnData = [];
    $returnData['intstatus'] = 0;
    $returnData['strstatus'] = $result['SPMessage'] ?? '';
    if(!empty($result) && isset($result['SPExecStatus']) && $result['SPExecStatus'] == 0){
          $returnData['intstatus'] = 1;
          $returnData['strstatus'] = 'Duty updated successfully.';
    }
    return json_encode($returnData);
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
}

  /**
   * Description : Get Duty Person information (It will be use at email time.)
   * @param dutyId int,
   * @param TeamId int
   * return array
   */
  function getAssignUserInformation($dutyId)
  {
    try {
      $pdo = OpenDBLinkA7();
      $sql = "exec [dbo].[usp_get_AssignUserbyTeamIdAndAllocationId] ?";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(1, $dutyId, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetchALL(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      logger()->critical('DB Error', (array) $e);
    }
    return false;
  }

  function MoveJobToOtherDuty($scheduledPersonId,$JobId,$teamId,$destinationDate,$DutyId,$role)
  {
    $current_User = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $current_User_Id = GetUserIdbyNetlogin($current_User);
    if ($DutyId==''){
      $DutyId='NULL';
    }
    if ($scheduledPersonId==''){
      $scheduledPersonId='NULL';
    }
    try {
      $pdo = OpenDBLinkA7();
      $sql = "exec [dbo].[usp_MoveJobToOtherDuty] ".$scheduledPersonId.",'".$destinationDate."',".$teamId.",".$JobId.",".$DutyId.",".$current_User_Id.",".$role;      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
     return json_encode($result);
    } catch (PDOException $e) {
      logger()->critical('DB Error', (array) $e);
    }
    return false;
  }

  function CopyJobToOtherDuty($scheduledPersonId,$JobId,$teamId,$startDate,$endDate,$role)
  {
    $current_User = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
     $current_User = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $current_User_Id = GetUserIdbyNetlogin($current_User);
	if ($scheduledPersonId==''){
		$scheduledPersonId='NULL';
	}
    try {
      $pdo = OpenDBLinkA7();
      $sql = "exec [dbo].[usp_CopyJobToOtherDuty] :scheduledPersonId, :startDate, :endDate, :teamId, :JobId, :current_User_Id, :role";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':scheduledPersonId', $scheduledPersonId, PDO::PARAM_INT);
      $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
      $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
      $stmt->bindParam(':teamId', $teamId, PDO::PARAM_INT);
      $stmt->bindParam(':JobId', $JobId, PDO::PARAM_INT);
      $stmt->bindParam(':current_User_Id', $current_User_Id, PDO::PARAM_INT);
      $stmt->bindParam(':role', $role, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
     return json_encode($result);
    } catch (PDOException $e) {
      logger()->critical('DB Error', (array) $e);
    }
    return false;
  }

  /**
   * Description :This Function makes Duty Mark as Absent|leave|Sick from Edit Daily
   * @param dutyId int,
   * @param teamId int,
   * @param isEdited int
   * @param strNewDuty str (Type could be Absent|leave|Sick )
   * @param markdutyColorId int color id
   * return Json Response
   */

   function makeDutyAbsentSickLeave($allocationId,$teamId,$weekno,$isShiftleader,$editype,$durationhr='',$wstartdate='',$wenddate='',$date='',$mastMiscFilterId = 0, $allocDutyName = '', $sicknessHistory = '', $allocationsSpId = 0, $isChargingPresent = 0, $schedulingPersonId = 0, $dutyDate = '', $allocationsDutyId = 0)
   {
        try{
            $pdo = OpenDBLinkA7();
            $status = 1;
            $strsmsg = 'success';
            $sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
			/*$query1 = "select ChargingDutyDate, DisplayFirstname, DisplayLastname, DutyName, EstablishCode, EstablishCodeDescription, IsActual, ActivityCodeName, AC.Description, UnitPrice, ChargeWbsCodeName, EstablishCode, EstablishCodeDescription, Comments, Contact, Telephone, Quantity from ChargingDutyMapping_Link cdml INNER JOIN EstablishCode EC ON EC.EstablishCodeId = cdml.EstabCodeId INNER JOIN ActivityCode AC ON AC.ActivityCodeId = cdml.ActivityCodeId INNER JOIN ChargeWbsCode CWC ON CWC.ChargeWbsCodeId = cdml.ChargeCodeId INNER JOIN Allocations AL ON AL.ID = cdml.AllocationId INNER JOIN ScheduledPeople SP ON SP.ScheduledPersonID = cdml.PersonId where cdml.AllocationId = $allocationId";
			$stmt1 = $pdo->prepare($query1);
			$stmt1->execute();
			$chargingResult = $stmt1->fetchAll(PDO::FETCH_ASSOC);
			*/
            $isShiftleaderParam= "@pIsShiftleader = ".$isShiftleader;
            if($editype =='MARKSICK'){
                $durationParam= "@pDuration = ".$durationhr;
                $sql = "exec [dbo].[usp_Edit_Allocations] @EditType = '".$editype."', @pNetLogin = '".$sessUserNetLogin."', @pAllocationsID = ".$allocationId.", ".$isShiftleaderParam.", ".$durationParam. ", @psicknessHistory = '$sicknessHistory', @FromID = $allocationsSpId, @pSchedulingPersonID = $schedulingPersonId, @pDutyDate = '".$dutyDate."'";
            } else {
                $sql = "exec [dbo].[usp_Edit_Allocations] @EditType = '".$editype."', @pNetLogin = '".$sessUserNetLogin."', @pAllocationsID = ".$allocationId.", ".$isShiftleaderParam. ", @psicknessHistory = '$sicknessHistory', @FromID = $allocationsSpId, @pSchedulingPersonID = $schedulingPersonId, @pDutyDate = '".$dutyDate."'";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if($result['SPExecStatus'] > 1) {
                $newAllocationId = $result['SPExecStatus'];
            } else {
                $newAllocationId = $allocationId;
            }
        		if(($result['SPExecStatus']) && (empty($result['SPMessage']) || $result['SPMessage'] == 'success') && (($isChargingPresent > 0) && ($isShiftleader == 1)))
        		{
        			$chargingDate = date('d/m/Y', strtotime($dutyDate));
        			$query2 = "select Email, schedulingTeamName from schedulingTeams where schedulingTeamId = $teamId";
        			$stmt2 = $pdo->prepare($query2);
        			$stmt2->execute();
        			$emailResult = $stmt2->fetch(PDO::FETCH_ASSOC);
        			$mail = new PHPMailer\PHPMailer\PHPMailer();
        			$mail->isSMTP();
        			$mail->SMTPDebug = 0;
        			$mail->Host = getenv('SMTP_HOST');
        			$mail->Port = 25;
        			$mail->setFrom('noreply@bbc.co.uk', 'Allocate');
        			$mailAdd = $emailResult['Email'];
        			$schedulingTeamName = $emailResult['schedulingTeamName'];
        			$arrMailAdd = explode(";", $mailAdd);
        			for($intCount = 0; $intCount < count($arrMailAdd); $intCount++)
        			{
        			  $mail->addAddress($arrMailAdd[$intCount ]);
        			}
        			$mail->Subject = "Charging on $chargingDate for $schedulingTeamName has been deleted because a Shiftleader swapped or unassigned a duty";
        			require_once __DIR__ . '/../page-includes/allocations/edits/ChargingEmail.php';
        			$mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
                    $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
                    $htmlStr = '<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?? '').'</style>
                    <body><img alt="Banner" src="cid:pobanner" /><br><br>'.$html;
        			$mail->msgHTML($htmlStr);
        			$mail->send();
        		}
            if ($isShiftleader == 1 && $result['SPExecStatus'] == 0) {
                if(($allocationsDutyId == 0) && ($allocationsSpId == 0)){
                    $queryForPublish = "SELECT ASP.ASP_AllocationsDutyID, ASP.ASP_AllocationsSPID FROM AllocationsScheduledPersons ASP WHERE ASP.ASP_SchedulingPersonID = ".$schedulingPersonId." AND ASP.ASP_DutyDate = '".$dutyDate."'";
                    $stmtForPublish = $pdo->prepare($queryForPublish);
                    $stmtForPublish->execute();
                    $resultForPublish = $stmtForPublish->fetch(PDO::FETCH_ASSOC);
                    if(!empty($resultForPublish)){
                      $allocationsDutyId = $resultForPublish['ASP_AllocationsDutyID'] ?? 0;
                      $allocationsSpId = $resultForPublish['ASP_AllocationsSPID'] ?? 0;
                    }
                }
                $sql2 = "EXEC [dbo].[usp_mod_PublishIndividulAllocations] ?, ?, ?";
                $stmt = $pdo->prepare($sql2);
                $stmt->bindParam(1, $allocationId, PDO::PARAM_INT);
                $stmt->bindParam(2, $allocationsDutyId, PDO::PARAM_INT);
                $stmt->bindParam(3, $allocationsSpId, PDO::PARAM_INT);
                $stmt->execute();
            }
	        if ($result['SPExecStatus'] > 0){
			     $finalArray = array("strstatus"=>0,"strsmsg"=>$result['SPMessage']);
		    } else {
          $dutyExists = 'No';
          if($mastMiscFilterId != ''){
              $rsAssignedDutiesJson  = GetAssignedDutiesToFilter($mastMiscFilterId,1);
              $rsAssignedDutiesJson = json_decode($rsAssignedDutiesJson,true);
              if(!empty($rsAssignedDutiesJson)){
                  foreach($rsAssignedDutiesJson as $rsKey => $rsVal){
                      if(strtolower($rsVal['DutyName']) == strtolower($allocDutyName)){
                          $dutyExists = 'Yes';
                      }
                  }
              }
          }
          if (substr($allocDutyName, 0, 1)=="-"){
            $dutyExists = 'Yes';
          }
          if (substr($allocDutyName, 0, 2)=="--"){
            $dutyExists = 'Yes';
          }
          $finalArray = array("strstatus"=>1,"strsmsg"=>"Sick Mark Successfully","newunallocid"=>$allocationsDutyId,'dutyExists'=>$dutyExists, 'dataId' => $newAllocationId);
		    }
	        return json_encode($finalArray);
        } catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }