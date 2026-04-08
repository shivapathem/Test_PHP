<?php
// Check if a session is not already started

use Carbon\Carbon;

if (session_status() === PHP_SESSION_NONE ) {
    session_start();
}
include_once 'DBHelper.php';
include_once 'helpers.php';
include_once __DIR__ . '/common/classCommonDBFunctions.php';
include_once __DIR__ . '/../page-includes/users/process/classUserSetup.php';

// ####################################################### A few variables
$intFixedDays = 14;
$intStaffCostPerHour = 25;
$intFreelanceCostPerHour = 25;
$intNightStartMins = 1140;

// ####################################################### New Code
$intViewYears = 4;
$intAdminViewYears = 7;
$arrMapping = [];
$arrRotaHide =  [
  0 => 'Show',
  1 => 'Hide',
  2 => 'Leave'
];

$arrColours[0]['back'] = 'eeeeee';
$arrColours[0]['fore'] = '000000';
$arrColours[1]['back'] = '009000';
$arrColours[1]['fore'] = 'ffffff';
$arrColours[2]['back'] = 'B06C02';
$arrColours[2]['fore'] = 'ffffff';

function GetCurrentFilterData ($strLogin, $schedulingTeamId) {
  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT     CurrentFilter,CurrentDailyFilter
               FROM       Staff_Web_Config_Departments_Link
               where  schedulingTeamId = :schedulingTeamId  and  (Login = N :netLogin)";

  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':schedulingTeamId', $schedulingTeamId, PDO::PARAM_INT);
  $stmt->bindParam(':netLogin', $strLogin, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  return $row;

}

/**
  * Get the BBC Email from staffDetails table
  * @param  $strLogin contains netlogin
  * @return array BBC Email
  */
function GetEmailFromLogin ($strLogin) {

    $pdo = OpenDBLinkA7();

    $sql = "SELECT UD_InternalEmail FROM UserDetails WHERE UD_NetLogin = ?";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1,$strLogin, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($row) && (isset($row['UD_InternalEmail']) && !empty(trim($row['UD_InternalEmail'])))) {
          $strEmail = $row['UD_InternalEmail'];
        } else {
          $arrUser = bbc_GetFromLDAPFull($strLogin);
          $strEmail = $arrUser["email"] ?? '';
          }
      return ($strEmail);
    }
    catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
  }
}

function GetDepartmentNameFromID ($intID) {
  $pdo = OpenDBLinkA7();
  $strQuery = "select schedulingTeamName from schedulingTeams where schedulingTeamId = :intTeamID";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':intTeamID', $intID, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return ($result['schedulingTeamName']);

}

function GetTeamNameFromID ($intTeamID) {

  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT schedulingTeamName
               FROM  schedulingTeams
               WHERE (schedulingTeamId = :intTeamID)";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':intTeamID', $intTeamID, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
   if(isset($result['schedulingTeamName'])) {
    return $result['schedulingTeamName'];
   } else {
     return '';
   }
}

function GetDefaultTeamByLogin ($strLogin) {

  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT       DepartmentID
                FROM         Staff_Web_Config_Departments_Link
                WHERE        (Login = N'$strLogin') AND (isDefault = 1)";

  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if(is_null($result['DepartmentID'])) {
    return (0);
  }
  else {
    return ($result['DepartmentID']);
  }
}


/**
  * Get the weeknumber and Day from date in daily Allocation
  * @param  $date date passed from daily allocation
  * @return array
  */
function GetAllocationWeekandDay ($date) {

  $pdo = OpenDBLinkA7();

  try {
        $strQuery = "SELECT ixYearWeek, ixDayInWeek, ixYear, ixWeekInYear, sDayName FROM TimeDimension WHERE dDateTime=cast('".$date."' as date)";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $date, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;

  } catch(Exception $e) {
       logger()->critical('DB Error', (array) $e);
  }
}

function GetDefaultSchedulingTeamIdByLogin ($userId) {

  $pdo = OpenDBLinkA7();

  $strQuery = " SELECT ScheduledPersonTeam_LINK.TeamID
	FROM UserDetails (nolock)
	INNER JOIN ScheduledPersonTeam_LINK (nolock) on UserDetails.UD_UserID = ScheduledPersonTeam_LINK.ScheduledPersonID
	WHERE UserDetails.UD_UserID = ? and ScheduledPersonTeam_LINK.isDefault = 1";

  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $userId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if(isset($result['TeamID'])) {
    return $result['TeamID'];
  } else {
    return 0;
  }
}

function GetStaffDetailsByLogon($strLogin) {

    $pdo = OpenDBLinkA7();

    $sql = "exec [dbo].[usp_get_StaffScheduledPersonByUserLogon] ?";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1,$strLogin, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }catch (\Exception $e){
        echo $e->getMessage();
    }
}

/**
* This function is used to get the scheduledPerson details from SchedulePeople table.
*
* @param $UserId This param contains the UserId.
*
* @return array Returns the scheduledPersonId.
*/
function GetScheduledPersonIdbyUserId($userID) {

  $pdo = OpenDBLinkA7();

    $strQuery = "select UD_UserID ScheduledPersonID, UD_DisplayName DisplayName from UserDetails where UD_UserID = '".$userID."'";
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

   return $result['ScheduledPersonID'] ?? 0;
}

function GetUserIdbyNetlogin($login) {

  $pdo = OpenDBLinkA7();

  $strQuery = "select UD_UserID as UserID from UserDetails where UD_NetLogin ='$login'";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

   return $result['UserID'] ?? 0;
}

function GetNameAndEFTFromStaffNumber ($strScheduledPersonId, $intTeamID) {
  $pdo = OpenDBLinkA7();
  $arrEFT =false;
  $strQuery = "exec usp_GetNameAndEFTFromStaffNumber ?, ?";
  $stmt = $pdo->prepare($strQuery);
    $stmt->bindValue(1, $strScheduledPersonId, PDO::PARAM_INT);
    $stmt->bindValue(2, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(count((array)$result)>0){
      $arrEFT = array();
    }
    $arrEFT['FullName'] = $result['Fullname'] ?? '';
    $arrEFT['EFT'] = $result['EFT'] ?? '';
    return ($arrEFT);

}

/**
* This function is used to get the email address based on Team ID.
*
* @param $intTeamID This param contains the Team ID.
*
* @return array Returns the email address from the DB as per the scheduling teams ID
*/
function GetTeamEmail($intTeamID) {
  try {
    $pdo= OpenDBLinkA7();
    $strQuery = "SELECT Email FROM schedulingTeams WHERE schedulingTeamId=?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1,$intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($result['Email'])) {
      return ($result['Email']);
    } else {
      return null;
    }
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
  return false;
}
function GetAllTeams()
{
  $strQuery = "SELECT schedulingTeamId ID, schedulingTeamName FullName FROM schedulingTeams WHERE isActive = 1 ORDER BY FullName";
  $pdo= OpenDBLinkA7();
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($result as $row) {
    $arrDepartments[$row["ID"]]  = $row["FullName"];
  }
  if (isset($arrDepartments)) {
    return ($arrDepartments);
  }
}

function GetBreaksTypes() {

  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT BreaksTableTypes.ID, BreaksTableTypes.Description, schedulingTeams.schedulingTeamName
                FROM            BreaksTableTypes
                LEFT OUTER JOIN schedulingTeams ON schedulingTeams.schedulingTeamId = BreaksTableTypes.schedulingTeamId
                ORDER BY        BreaksTableTypes.Description";


  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $arrBreaks = [];

  foreach($result as $row) {
    $arrBreaks[$row["ID"]]["Description"] = $row["Description"];
    $arrBreaks[$row["ID"]]["TeamName"] = $row["schedulingTeamName"];
  }

  return $arrBreaks;
}

/*
This function is  replacement of GetStaffOtionsByDepartment()
*/
function GetStaffOtionsByTeam($strUser, $intTeamID = 0, $userID = 0) {

  $pdo = OpenDBLinkA7();

  $sql = "exec [dbo].[usp_get_MySetUp] 0,?";
  $stmt = $pdo->prepare($sql);
  // The parameters
  $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
  $stmt->execute();
  $resultUserSetup = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $arrStaff = [];
  foreach ($resultUserSetup as $row) {
    $arrStaff[$row["schedulingTeamId"]]["TeamName"] = $row["schedulingTeamName"];
    $arrStaff[$row["schedulingTeamId"]]["StaffNumber"] = $row["StaffNumber"];

    if ((isset($row["System Admin"]) && $row["System Admin"]>0)) {
      $SysAdmin = 1;
    } else {
      $SysAdmin = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["isAdmin"] = $SysAdmin;

    if ((isset($row["Scheduling Team Admin"]) && $row["Scheduling Team Admin"]>0)) {
      $TeamSysAdmin = 1;
    }else {
      $TeamSysAdmin = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["isTeamAdmin"] = $TeamSysAdmin;

    $arrStaff[$row["schedulingTeamId"]]["isDefault"] = $row["isDefault"];
    if ((isset($row["Shift Leader"]) && $row["Shift Leader"]>0)) {
      $ShiftLeader = 1;
    } else {
      $ShiftLeader = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["isShiftLeader"] = $ShiftLeader;
    if ((isset($row["Scheduler"]) && $row["Scheduler"]>0))
    {
      $Scheduler = 1;
    } else {
      $Scheduler = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["isScheduler"] = $Scheduler;

    if ((isset($row["Scheduling Team Viewer"]) && $row["Scheduling Team Viewer"] > 0)) {
      $SchedulingTeamViewer = 1;
    } else {
      $SchedulingTeamViewer = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["isSchedulingTeamViewer"] = $SchedulingTeamViewer;

	if ((isset($row["Team Leader"]) && $row["Team Leader"] > 0)) {
      $SchedulingTeamLeader= 1;
    } else {
      $SchedulingTeamLeader = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["isTeamLeader"] = $SchedulingTeamLeader;

    if ((isset($row["Manager"]) && $row["Manager"]>0)) {
      $Manager = 1;
    } else { $Manager = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["isManager"] = $Manager;
    $arrStaff[$row["schedulingTeamId"]]["CurrentFilter"] = "";
    $arrStaff[$row["schedulingTeamId"]]["CurrentDailyFilter"] = "";
    $arrStaff[$row["schedulingTeamId"]]["HourWidth"] = "75";
    $arrStaff[$row["schedulingTeamId"]]["WeeklyFilterOption"] = "";
    $arrStaff[$row["schedulingTeamId"]]["HideRota"] = $row["rota"];
    $arrStaff[$row["schedulingTeamId"]]["ProdDayCount"] = "";
    $arrStaff[$row["schedulingTeamId"]]["ProdStartDay"] = "";
    $arrStaff[$row["schedulingTeamId"]]["RestictView"] = "";
    $arrStaff[$row["schedulingTeamId"]]["FLMaskAfter"] = $row["maskAfter"];
    $arrStaff[$row["schedulingTeamId"]]["ScheduledPersonID"] = $row["ScheduledPersonID"];
    if ((isset($row["scheduledType"]) && $row["scheduledType"] == 1)) { $Scheduled_Person = 1;
    } else { $Scheduled_Person = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["isScheduledPerson"] = $Scheduled_Person;

    if ((isset($row["showProductionView"]) && $row["showProductionView"]>0)) { $Is_Production_view = 1;
    } else { $Is_Production_view = 0;
    }
    $arrStaff[$row["schedulingTeamId"]]["HasDutiesView"] = $Is_Production_view;
    $arrStaff[$row["schedulingTeamId"]]["HasJobsInWeeklyView"] = $row["ShowJobsInWeeklyView"];
    $arrStaff[$row["schedulingTeamId"]]["Basic Reports"] = $row["Basic Reports"];
    $arrStaff[$row["schedulingTeamId"]]["Advanced Reports"] = $row["Advanced Reports"];
  }

  return $arrStaff;
}

function GetWeeksFromDate ($strStartDate, $strEndDate) {
      $strLoopDate = $strStartDate;
      while (strtotime((string) $strLoopDate) <= strtotime((string) $strEndDate)) {
        $arrDateWeeks[$strLoopDate] = bbcweeknumber($strLoopDate);
	      $strLoopDate = date ("Y-m-d", strtotime("+1 day", strtotime((string) $strLoopDate)));
      }
    return($arrDateWeeks);
}

Function ShowRotaAsWell($date, $intConfirmedDays)    {
  $intConfirmedUnixDate = strtotime("+$intConfirmedDays Days");
  if (strtotime((string) $date) >= $intConfirmedUnixDate) {
    $intShowRotasAsWell = 1;
  }
  else {
    $intShowRotasAsWell = 0;
  }
  return ($intShowRotasAsWell);
}

function SetRotaDutyClass($isworking)    {
  if ($isworking == 0 ) {
    $cellclass = "NotFixedOFF";
  }
  else {
     $cellclass = "NotFixedON ";
  }
  return ($cellclass);
}

Function SetDutyClass($date, $intMaskDays, $intMaskType, $intIsWorking)    {
  $intConfirmedUnixDate = strtotime("+$intMaskDays Days");
  if (strtotime((string) $date) >= $intConfirmedUnixDate) {
    if ($intMaskType == 1) {
      $cellclass = "DutyCellNotWorking";
    }
    else {
      if ($intIsWorking == 0 ) {
        $cellclass = "DutyCellNotWorking";
      }
      else {
        $cellclass = "DutyCellWorking";
      }
    }
  }
  else {
    if ($intIsWorking == 0 ) {
      $cellclass = "DutyCellNotWorking";
    }
    else {
      $cellclass = "DutyCellWorking";
    }
  }
  return ($cellclass);
}

function validateDate($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') == $date;
}

function spindate($thisdate) {
  // Returns a date in the format d/m/y
    if(!is_object($thisdate))
    {
      $thisdate = new DateTime($thisdate);
    }
    $thisdate = $thisdate->format('d/m/y');
    return $thisdate;
}

function SpinWeekOrMonth($value) {
  $year = substr((string) $value, 0, 4);
  $month = substr((string) $value, 4, 2);
  return $month.'/'.$year;
}

/**
* This function is used to get the Scheduled Person Team details of a paticular scheduled person.
*
* @param $scheduledPersonId This param contains the ScheduledPersonId.
*
* @return $arrName array Returns the Details of Scheduled Person.
*/
function GetScheduledPersonTeamDetailsNew($scheduledPersonId){

  $pdo = OpenDBLinkA7();

    $strQuery = "select distinct UD_UserID ScheduledPersonID,
    UD_DisplayName DisplayName,
		 UD_UserID  UserID,
		 0 StaffDetailsID,
		 UD_InternalEmail	 InternalEmail,
		 UD_AdminNotes		  AdminNotes,
		 UD_FWANotes		  FWANotes,
		 sptl.ScheduledPersonID,
		 sptl.TeamID,
		 team.schedulingTeamName as TeamName,
		 sptl.IsHomeTeam,
		 sptl.SortCode,
		 sptl.CreatedBy,
		 convert(varchar(30), sptl.CreatedDate,105) as CreatedDate,
		 convert(varchar(30), sptl.StartDate,105) as StartDate,
		 convert(varchar(30), sptl.EndDate,105) as EndDate,
		 sptl.LastUpdatedBy,
		 convert(datetime,sptl.LastUpdatedDate, 103) as LastUpdatedDate,
		 sptl.BackgroundColour,
		 sptl.fontcolour,
		 sptl.IsActive,
		 sptl.isDefault,
		 sptl.IsAvailable,
		 UD_DisplayFirstName Forename,
		 UD_DisplayFirstName PreferredForename,
		 UD_NetLogin NetLogin,
		 UD_DisplayLastName Surname,
		 NULL AS JobTitle,
		 NULL AS Title,
		 UD_StaffNumber  StaffNumber,
		 0 AveDayLen,
		 0 ShiftBreak,
		 sp.UD_DisplayFirstName  DisplayFirstName,
		 sp.UD_DisplayLastName  DisplayLastName,
		 sptl.IsDefaultBGColour
     from UserDetails (NOLOCK) as sp
    INNER JOIN [dbo].[ScheduledPersonTeam_LINK] (NOLOCK) as sptl on sp.UD_UserID = sptl.ScheduledPersonID
    INNER JOIN schedulingTeams (NOLOCK) as team on team.schedulingTeamId = sptl.TeamID and team.isActive=1
	  LEFT JOIN UserConfigs scp (nolock) ON  UD_UserID = UC_UserID
	  AND CAST(GETDATE() AS Date) BETWEEN scp.UC_StartDate AND ISNULL(scp.UC_EndDate, CAST(GETDATE() AS Date))
    where sp.UD_UserID = :intschedulepersonid
	  AND cast(getdate() as date) between sptl.StartDate and CAST(isnull(sptl.EndDate,GETDATE()) AS DATE )
	  and sptl.scheduledType = 1
    order by IsHomeTeam DESC";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intschedulepersonid', $scheduledPersonId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $arrName = [];
    foreach ($result as $row) {
        $arrName[$row['TeamID']]['FullName'] = $row['DisplayName'];
        $arrName[$row['TeamID']]['Login'] = $row['NetLogin'];
        $arrName[$row['TeamID']]['StaffNumber'] = $row['StaffNumber'];
        $arrName[$row['TeamID']]['isDefault'] = $row['isDefault'];
        $arrName[$row['TeamID']]['IsHomeTeam'] = $row['IsHomeTeam'];
        $arrName[$row['TeamID']]['Email'] = $row['InternalEmail'];
		    $arrName[$row['TeamID']]['ScheduledPersonID'] = $row['ScheduledPersonID'];
        $arrName[$row['TeamID']]['TeamName'] = $row['TeamName'];

        $arrName[$row['TeamID']]['StartDate'] = $row['StartDate'];
      //$arrName[$row['TeamID']]['EndDate']   = $row['EndDate'];

        if ($row['isDefault'] == 1) {
          $arrName['defaultSchedulingTeamId'] = $row['TeamID'];
        }
        if ($row['IsHomeTeam'] == 1) {
          $arrName['homeTeamId'] = $row['TeamID'];
        }
    }
    return ($arrName);
}

function GetScheduledPersonTeamDetails($scheduledPersonId){

  $pdo = OpenDBLinkA7();

    $strQuery = "exec [dbo].[usp_get_Schedulepersonteamsdetails] ?";

    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $scheduledPersonId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $arrName = [];
    foreach ($result as $row) {
        $arrName[$row['TeamID']]['FullName'] = $row['DisplayName'];
        $arrName[$row['TeamID']]['Login'] = $row['NetLogin'];
        $arrName[$row['TeamID']]['StaffNumber'] = $row['StaffNumber'];
        $arrName[$row['TeamID']]['isDefault'] = $row['isDefault'];
        $arrName[$row['TeamID']]['IsHomeTeam'] = $row['IsHomeTeam'];
        $arrName[$row['TeamID']]['Email'] = $row['InternalEmail'] ?? '';
		    $arrName[$row['TeamID']]['ScheduledPersonID'] = $row['ScheduledPersonID'];

        if ($row['isDefault'] == 1) {
          $arrName['defaultSchedulingTeamId'] = $row['TeamID'];
        }
        if ($row['IsHomeTeam'] == 1) {
          $arrName['homeTeamId'] = $row['TeamID'];
        }
    }
    return ($arrName);
}


function GetUserandFullNameFromStaffNumber($ScheduledPersonID) {
    $pdo = OpenDBLinkA7();
    $strQuery = "exec [dbo].[usp_get_SchedulePersonByStaffNumber] ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $ScheduledPersonID, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $arrName = [];
    foreach ($result as $row) {
        $arrName[$row['schedulingTeamId']]['FullName'] = $row['DisplayName'];
        $arrName[$row['schedulingTeamId']]['Login'] = $row['NetLogin'];
        $arrName[$row['schedulingTeamId']]['StaffNumber'] = $row['StaffNumber'];
        $arrName[$row['schedulingTeamId']]['Email'] = $row['Email'] ?? '';
        $arrName[$row['schedulingTeamId']]['isDefault'] = $row['isDefault'];
		$arrName[$row['schedulingTeamId']]['ScheduledPersonID'] = $row['ScheduledPersonID'];

        if($row['isDefault'] == 1)
          $arrName['defaultSchedulingTeamId'] = $row['schedulingTeamId'];
    }

    return ($arrName);
}
/**
  * Get the allocation details of that allocation
  * @param  $allocationDutyId AllocationId of Allocations tbl
  * @return array
  */
function GetAllocationsDetailsByAllocationDutyId(int $allocationDutyId, $allocationSPId = 0) {
  $pdo = OpenDBLinkA7();
  try {
    if($allocationSPId > 0){
          $strQuery = "SELECT  AL_AllocationsID as AllocationID,
            AD.AD_AllocationsDutyID as AllocationsDutyID,
            ASP_AllocationsSPID as AllocationsSPID,
            AD_MasterDutyID AS  MasterDutyId,
			CASE WHEN AD_DutyType IN (8,11,12)
				THEN CASE WHEN ASP_LeaveType = 1
						THEN 'Leave'
						WHEN ASP_LeaveType = 2
						THEN 'OFF Leave'
						WHEN ASP_LeaveType = 3
						THEN 'Sick'
						WHEN ASP_LeaveType = 4
						THEN 'U-Sick'
						WHEN ASP_LeaveType = 5
						THEN '-Sick'
						WHEN ASP_LeaveType = 7
						THEN 'Absent'
					END
				ELSE AD_DutyName
				END	   AS DutyName,
            AD_DutyDate AS DutyDate,
            AL_WeekNumber AS  WeekNumber,
            AD_iDay as  iDay,
            AD_StartTimeSec as StartTime,
            AD_EndTimeSec as  EndTime,
            AL_SchedulingTeamID  AS SchedulingTeamId,
            ASP_SchedulingPersonID as SchedulingPersonID,
            AD_IsEditedDutyAttention AS   isEdited,
            ASP_Comments AS  PersonComments,
            AD_Comments AS  Comments,
            AD_DutyProgramID1 as DutyProgramID1,
            AD_DutyProgramID2 as DutyProgramID2,
            AD_DutyProgramID3 as DutyProgramID3,
            AD_DutyProgramID4 as DutyProgramID4,
            AD_DutyProgramID5 as DutyProgramID5,
            AD_DutyProgramID6 as DutyProgramID6,
            AD_DutyColourID as DutyColourID,
            AD_IsNeedCovering as IsNeedCovering,
            AD_PlannedDutyBreakTime as PlannedDutyBreakTime,
            AD_IsOverrideOver12 as IsOverrideOver12,
            UD_StaffNumber StaffNumber,
            sp.UD_DisplayName AS FullName,
            sp.UD_DisplayFirstName AS DisplayFirstName,
            sp.UD_DisplayLastName AS DisplayLastName,
            ec.EstablishCode,
            ec.EstablishCodeDescription,
            ec.EstablishCodeId,
            AD_DutyDate  as StartDate,
            CASE WHEN AD_DutyType IN (8,11,12)
				THEN ASP_LeaveDuration
				ELSE ISNULL(AD.AD_Duration,0) END AS Duration,
            UD_UserID AS  ScheduledPersonID,
            ap.ASP_OverTimeHours AS MannualOThours,
            st.schedulingTeamName AS schedulingTeamName,
        0 AS StaffDetailsID
      FROM Allocations AL
      INNER JOIN AllocationsScheduledPersons ap on AL_AllocationsID = ASP_AllocationsID
        INNER JOIN AllocationsDuties AD on ap.ASP_AllocationsDutyID = ad.AD_AllocationsDutyID 
      INNER JOIN schedulingTeams st on st.schedulingTeamId = AL_SchedulingTeamID
      INNER JOIN UserDetails sp ON sp.UD_UserID = ASP_SchedulingPersonID
      LEFT JOIN EstablishCode ec on ec.EstablishCodeId = st.establishCodeID
      WHERE ap.ASP_AllocationsSPID = ?";
            $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $allocationSPId, PDO::PARAM_INT);
    }else{
      $strQuery = "SELECT AD_AllocationsID as AllocationID,
          AD.AD_AllocationsDutyID as AllocationsDutyID,
          ASP_AllocationsSPID as AllocationsSPID,
          AD_MasterDutyID AS  MasterDutyId,
          AD_DutyName AS DutyName,
          AD_DutyDate AS DutyDate,
          AL_WeekNumber AS  WeekNumber,
          AD_iDay as  iDay,
          AD_StartTimeSec as StartTime,
          AD_EndTimeSec as  EndTime,
          AL_SchedulingTeamID  AS SchedulingTeamId,
          ASP_SchedulingPersonID as SchedulingPersonID,
          AD_IsEditedDutyAttention AS   isEdited,
          ASP_Comments AS  PersonComments,
          AD_Comments AS  Comments,
          AD_DutyProgramID1 as DutyProgramID1,
          AD_DutyProgramID2 as DutyProgramID2,
          AD_DutyProgramID3 as DutyProgramID3,
          AD_DutyProgramID4 as DutyProgramID4,
          AD_DutyProgramID5 as DutyProgramID5,
          AD_DutyProgramID6 as DutyProgramID6,
          AD_DutyColourID as DutyColourID,
          AD_IsNeedCovering as IsNeedCovering,
          AD_PlannedDutyBreakTime as PlannedDutyBreakTime,
          AD_IsOverrideOver12 as IsOverrideOver12,
          UD_StaffNumber StaffNumber,
          sp.UD_DisplayName AS FullName,
          sp.UD_DisplayFirstName AS DisplayFirstName,
          sp.UD_DisplayLastName AS DisplayLastName,
          ec.EstablishCode,
          ec.EstablishCodeDescription,
          ec.EstablishCodeId,
          AD_DutyDate  as StartDate,
          AD_Duration AS  Duration,
          UD_UserID AS  ScheduledPersonID,
          ap.ASP_OverTimeHours AS MannualOThours,
          st.schedulingTeamName AS schedulingTeamName,
          0 AS StaffDetailsID
        FROM Allocations AL
        INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
        INNER JOIN schedulingTeams st on st.schedulingTeamId = AL_SchedulingTeamID
        LEFT JOIN AllocationsScheduledPersons ap on ap.ASP_AllocationsDutyID = ad.AD_AllocationsDutyID
        LEFT JOIN UserDetails sp ON sp.UD_UserID = ASP_SchedulingPersonID
        LEFT JOIN EstablishCode ec on ec.EstablishCodeId = st.establishCodeID
        WHERE AD.AD_AllocationsDutyID = ?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $allocationDutyId, PDO::PARAM_INT);
    }
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
}

function GetDutyByDayAndTeam($intWeek, $intDay, $intTeamID, $selectedDate,$strDayName) {
  $pdo = OpenDBLinkA7();
  try {
    $strQuery = "SELECT AD.AD_AllocationsID as AllocationId,
    AD.AD_AllocationsDutyID as DutyId,
    AD.AD_DutyName as DutyName,
    ISNULL(ASP.ASP_SchedulingPersonID, 0) as SchedulingPersonID,
    ISNULL(UD.UD_DisplayName, 'Unallocated') as FullName,
    AD.AD_StartTimeSec as StartTime,
    AD.AD_EndTimeSec as EndTime
    FROM AllocationsDuties AD
    INNER JOIN Allocations AL ON AL.AL_AllocationsID = AD.AD_AllocationsID
    LEFT JOIN AllocationsScheduledPersons ASP ON AD.AD_AllocationsDutyID = ASP.ASP_AllocationsDutyID
    LEFT JOIN UserDetails UD ON UD.UD_UserID = ASP.ASP_SchedulingPersonID
    WHERE AL.AL_SchedulingTeamID = :teamId
    AND AD.AD_DutyDate = :dutyDate
    AND AD.AD_DutyType NOT IN (2,7,8,9,10)
    AND AD.AD_DutyStatus IN (1,0)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':teamId', $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(':dutyDate', $selectedDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $returnData = [];
    $counter = 0;
    foreach($result as $data) {
      $returnData[$counter] = $data;
      $returnData[$counter]['StartTime'] = !empty($data['StartTime']) ? secondsToTime($data['StartTime']) : '';
      $returnData[$counter]['EndTime'] = !empty($data['EndTime']) ? secondsToTime($data['EndTime']) : '';
      $counter++;
    }

    if(!empty($returnData)){
      $response['success'] = true;
      $response['result'] = $returnData;
      $response['weekNo'] = $strDayName.', '.'Week  '.substr((string) $intWeek,4,2);
  }else{
      $response['success'] = false;
  }
  return json_encode($response);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
    echo $e->getMessage();
  }
}

 /**
  * Get the staff belong to this team and for this day
  * @param  $intWeek WeekNumber of Allocation
  * @param  $intDay iDay of Allocation
  * @param  $intTeamID SchedulingTeamId of Allocation
  * @param  $selectedDate selected date of session allocationdates
  * @return array
  */
function GetStaffDetailsByDayAndTeam($intWeek, $intDay, $intTeamID, $selectedDate, $isShiftleader = '') {
  $pdo = OpenDBLinkA7();
  try {
    $strQuery = "exec [dbo].[usp_get_StaffDetailsByDayAndTeam] ?,?,?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intWeek, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDay, PDO::PARAM_INT);
    $stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(4, $selectedDate, PDO::PARAM_STR);
    $stmt->bindParam(5, $isShiftleader, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
}

/**
  * Get the fulName from staffDetails table
  * @param  $strLogin contains netlogin
  * @return array Fullname
  */
function GetFullNameFromLogin ($strLogin) {

  $pdo = OpenDBLinkA7();

  try {
    $strQuery = "SELECT UD_DisplayName UserDisplayName FROM UserDetails WHERE UD_NetLogin = ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $strLogin, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($result) && isset($result['UserDisplayName'])) {
      return $result['UserDisplayName'];
    }
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }

}

/**
 * Description : use to given Full anem and sort code by team id and ScheduledPersion ID wise
 * @param:$ScheduledPersonID int ,  $schedulingTeamId int
 * return []
 */

function getusernameandsortcode($ScheduledPersonID, $schedulingTeamId) {
  $pdo = OpenDBLinkA7();
  $strQuery = "[dbo].[usp_fetch_fullNameandsortCode_ByScheduledPer_Team] ?,?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $ScheduledPersonID, PDO::PARAM_INT);
  $stmt->bindParam(2, $schedulingTeamId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $ret[0] = $result['fullname'] ?? '';
  $ret[1] = $result['SortCode'] ?? '';
  return ($ret);
}

function GetLastPublish ($intWeek, $intTeamID) {

  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT AL_UpdatedDate as LastUpdate
              FROM Allocations
              WHERE  (AL_WeekNumber = N'$intWeek') AND (AL_SchedulingTeamId = $intTeamID) AND AL_Status=1";

   $stmt = $pdo->prepare($strQuery);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);

   if(isset($result['LastUpdate'])) {
    return strtotime($result['LastUpdate']);
   } else {
     return 0;
   }
}

function GetLastEditUTC ($intWeek, $intTeamID) {
  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT        MAX(Teampay_Aux.dbo.SchedJobs.LastModDateTimeUTC) AS MaxLastMod
  FROM            Teampay_Aux.dbo.SchedStaff INNER JOIN
              StaffDetails ON Teampay_Aux.dbo.SchedStaff.ExtStaffNumber = StaffDetails.StaffNumber INNER JOIN
        ScheduledPeople ON ScheduledPeople.StaffDetailsID = StaffDetails.StaffID INNER JOIN
        ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ScheduledPeople.ScheduledPersonID INNER JOIN
              Teampay_Aux.dbo.SchedJobs ON Teampay_Aux.dbo.SchedStaff.SchedStaffID = Teampay_Aux.dbo.SchedJobs.SchedResID INNER JOIN
              Teampay_Aux.dbo.TimeDimension ON Teampay_Aux.dbo.SchedJobs.JobDate = Teampay_Aux.dbo.TimeDimension.dDateTime
                WHERE        (Teampay_Aux.dbo.TimeDimension.ixYearWeek = $intWeek) AND (ScheduledPersonTeam_LINK.TeamID = $intTeamID)";

  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if(isset($result['MaxLastMod'])) {
    return $result['MaxLastMod'];
  } else {
    return 0;
  }
}





function getdayofweek ($date) {
  $dowMap = [
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
  ];
  return $dowMap[date('D', strtotime((string) $date))];
}
function getbst ($date) {
  date_default_timezone_set('Europe/London');
  return date("I", strtotime($date." 03:00"));
}

function timetohours ($duration) {
  $arrdur = explode(":", (string) $duration);
  $hours = (($arrdur[0] * 60) + $arrdur[1]) / 60;
  return $hours;
}
function timetoseconds ($duration) {
  $arrdur = explode(":", (string) $duration);
  $seconds = (($arrdur[0] * 3600) + ($arrdur[1] * 60));
  return $seconds;
}

function doubletoseconds ($double) {
    if(!isset($double) || $double == '')
        $double = 0;
  $time = ($double * 86400) + 1;
  // Round to the nearest minute
  $time = round($time/60)*60;
  return $time;
}

function escapeSingleQuotes($string){
    //escapse single quotes
    $singQuotePattern = "/'/i";
    $singQuoteReplace = "''";
    return(stripslashes((string) preg_replace($singQuotePattern, $singQuoteReplace, (string) $string)));
}

function GetMenuItems () {
  try {
    $pdo = OpenDBLinkA7();
     $strQuery = "SELECT ID, Description, URL
                  FROM   HomeMenuShortcuts
                  ORDER BY Description";
      $stmt = $pdo->prepare($strQuery);
      $stmt->execute();
      $rsMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $arrMenuItems = [];
    if(count($rsMenus)>0){
      foreach($rsMenus as $row){
            $arrMenuItems[$row['ID']]['Description'] = $row['Description'];
            $arrMenuItems[$row['ID']]['URL'] = $row['URL'];
          }
      }
      return ($arrMenuItems);
  }
  catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }

}

function GetStaffInTeamSimple ($teamId, $sDate, $eDate) {
  $intNoLogin = 0;
  $pdo = OpenDBLinkA7();
  $query = "EXEC Usp_GetStaffInTeam ?, ?, ?, ?";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
  $stmt->bindParam(2, $intNoLogin, PDO::PARAM_STR);
  $stmt->bindParam(3, $sDate, PDO::PARAM_STR);
  $stmt->bindParam(4, $eDate, PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $arrstaff = [];
  foreach($result as $row){
      $arrstaff[$row['ScheduledPersonID']] = $row['fullname'];
  }

  return ($arrstaff);
}
/**
 * Decription : Get Username Where logged user is Scheduler or above
 * @param :$strUserID int,
 * @param :$intWeekNumber int,
 * @param : $intDay int
 * return [];
 *
 */
function GetStaffInTeamWithAllocation($strUserID, $intWeekNumber, $intDay) {
 $pdo = OpenDBLinkA7();
 try {
    $strQuery = "exec [dbo].[usp_get_GetStaffInTeamWithAllocation] :intDay,:strUserID,:intWeekNumber";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intDay',$intDay, PDO::PARAM_INT);
    $stmt->bindParam(':strUserID',$strUserID, PDO::PARAM_INT);//Admin User
    $stmt->bindParam(':intWeekNumber',$intWeekNumber, PDO::PARAM_INT);
    $stmt->execute();
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
    if (!empty($staff)) {
    foreach ($staff as $row)
    {
      if(!empty($row['Netlogin'])) {
        $arrstaff[$row['ScheduledPersonID'].'_'.$row['Netlogin']] = $row['fullname'].' {'.$row['TeamFullName'].'}';
      }
    }
  }
  if (isset($arrstaff)) {
    return ($arrstaff);
  } else {
    return [];
  }
}
function GetStaffInDepartmentWithRota ($intTeamID, $intNoLogin = 0) {
  $pdo = OpenDBLinkA7();

  $query = "exec usp_GetStaffInTeamWithRota ?";
  $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$arrstaff[0] = 'Select person';
  foreach($result as $row){
      $arrstaff[$row['ScheduledPersonID']] = $row['fullname'];
  }
  if (isset($arrstaff)) {
    return ($arrstaff);
  }
}

function gettextcolour($dutyname, $arrTextColours) {
  $textcolour = '#330066';
  $dutyname = strtoupper((string) $dutyname);
  if (isset($arrTextColours)) {
    foreach($arrTextColours as $id => $arrTextColour) {
      $strDescription = strtoupper((string) $arrTextColour['Description']);
     if($strDescription == substr($dutyname, 0, strlen($dutyname))) {
        $textcolour = $arrTextColour['TextColour'];
        break;
      }
    }
  }
  return $textcolour;
}

function intotohex($int) {

  $hex = dechex($int);
  if ($int < 0) {
   $colour = '#0080D0';
  }
  else {
    switch (strlen($hex)) {
      case 0:
        $colour = '#000000';
        break;
      case 2:
        $colour = '#'.$hex.'0000';
        break;
      case 4:
        $colour = '#'.substr($hex, 2, 2).substr($hex, 0, 2).'00';
        break;
      case 6:
        $colour = '#'.substr($hex, 4, 2).substr($hex, 2, 2).substr($hex, 0, 2);
        break;
      default:
        $colour = '#0000DD';
        break;
    }
  }
   return ($colour);
}
/**
 * Description :  get Admin statsus in slectedLeave Group
 * @param :$strLogin str
 * @param :$intGroupID int
 */
function GetAdminLeaveRequestGroupsAdminFromLogin ($strLogin, $intGroupID) {
  $pdo = OpenDBLinkA7();
  try {
    $strQuery ="exec [dbo].[usp_fetchAdminStatusfromLeaveRequestGroups] :strLogin,:intGroupID";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intGroupID', $intGroupID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
   if (!isset($row) || !isset($row['Admin']) || is_null($row['Admin'])) {
     return(2);
   } else {
     return ($row['Admin']);
   }
}
/**
 * Dscription -GetLeaveRequestGroupsFromLogin
 * @param : $strLogin str,
 * @param : $isDivisionalAdmin str ,
 * @param : $systemAdmin str,
 * return []
 *
 */
function GetAdminLeaveRequestGroupsFromLogin ($strLogin,$isDivisionalAdmin = 0) {

  $pdo = OpenDBLinkA7();
  try {
    $systemAdmin = $_SESSION['user']['SysAdmin'] ?? '0';
    $strQuery ="exec [dbo].[usp_GetAdminLeaveRequestGroupsFromLogin] :strLogin,:isDivisionalAdmin,:systemAdmin";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':isDivisionalAdmin', $isDivisionalAdmin, PDO::PARAM_STR);
    $stmt->bindParam(':systemAdmin', $systemAdmin, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
  if (!empty($result)) {
    foreach($result as $row) {
      $arrGroups[$row['ID']]['Admin'] = $row['Admin'];
      $arrGroups[$row['ID']]['Description'] = $row['Description'];
      $arrGroups[$row['ID']]['HoursPerLeaveDay'] = $row['HoursPerLeaveDay'];
      $arrGroups[$row['ID']]['ShowLeaveOverLimit'] = $row['ShowLeaveOverLimit'];
      $arrGroups[$row['ID']]['SummerLeaveOverLimit'] = $row['SummerLeaveOverLimit'];
      $arrGroups[$row['ID']]['ExtraLeaveClicks'] = $row['ExtraLeaveClicks'];
      $arrRequestsAllowed = explode(',' , (string) $row['RequestsAllowedMonthly']);
      $arrGroups[$row['ID']]['RequestsAllowedYearly'] = $row['RequestsAllowedYearly'];
      foreach ($arrRequestsAllowed as $intMonth => $intNumAllowed) {
        $arrGroups[$row['ID']]['RequestsAllowedMonthly'][$intMonth] = $intNumAllowed;
      }
      $arrGroups[$row['ID']]['email'] = $row['email'];
      $arrGroups[$row['ID']]['emailcopiesto'] = $row['emailcopiesto'];
	  /* Allow Part Days of Leave Request - Start */
      $arrGroups[$row['ID']]['IsPartDayLeaveAllowed'] = $row['IsPartDayLeaveAllowed'];
      $arrGroups[$row['ID']]['ID'] = $row['ID'];
	  /* Allow Part Days of Leave Request - End */
      $arrGroups[$row['ID']]['DivisionID'] = $row['DivisionID'];
      $arrGroups[$row['ID']]['DivisionName'] = $row['DivisionName'];
	  $arrGroups[$row['ID']]['AllowEmails'] = $row['allowEmails'];
    }
  }
    if (isset($arrGroups)) {
      return ($arrGroups);
    } else{
      return [];
    }

}


function GetSiteIsDown(){
  $intIsDown=0;
  $row ='';
  $casevar ='FETCH';
  $rowID =null;
  $strStartTime ='';
  $strEndTime='';
  $pdo = OpenDBLinkA7();
  $strQuery = "exec [dbo].[usp_SystestemDownTime] :casevar ,:rowID ,:strStartTime, :strEndTime";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':casevar', $casevar, PDO::PARAM_STR);
  $stmt->bindParam(':rowID', $rowID, PDO::PARAM_INT);
  $stmt->bindParam(':strStartTime', $strStartTime, PDO::PARAM_STR);
  $stmt->bindParam(':strEndTime', $strEndTime, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!empty($row)) {
    $SavedstartTime = strtotime((string) $row['StartTime']);
    $Savedendtime = strtotime((string) $row['EndTime']);
   if ($SavedstartTime < time() && $Savedendtime > time()) {
      $intIsDown = 1;
    }
  }
 return ($intIsDown);
}





/**
  * Store or Update EDP overtime volunteers details
  * @param  $dteStartDate startdate of Edp
  * @param  $dteEndDate enddate of Edp
  * @param  $schedulingTeamId of Edp
  * @param  $schedulingPersonId of Edp
  * @return array
  */
function readedp($dteStartDate = '', $dteEndDate = '', $schedulingPersonId = 0, $schedulingTeamId = 0)
{
  $pdo = OpenDBLinkA7();
  if (($schedulingPersonId!=0) && ($schedulingTeamId!=0)) {
    $query = "SELECT id, SchedulingPersonId, ddate, SchedulingTeamId, history
              FROM  edp
              WHERE (ddate >= CONVERT(DATETIME, :dteStartDate, 102))
              AND (ddate <= CONVERT(DATETIME, :dteEndDate, 102))
              AND (SchedulingPersonId = :schedulingPersonId)";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':dteStartDate', $dteStartDate, PDO::PARAM_STR);
    $stmt->bindValue(':dteEndDate', $dteEndDate, PDO::PARAM_STR);
    $stmt->bindValue(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
  } else {
    $query = "SELECT id, SchedulingPersonId, ddate, SchedulingTeamId, history
              FROM  edp
              WHERE (ddate >= CONVERT(DATETIME, :dteStartDate, 102))
              AND (ddate <= CONVERT(DATETIME, :dteEndDate, 102))";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':dteStartDate', $dteStartDate, PDO::PARAM_STR);
    $stmt->bindValue(':dteEndDate', $dteEndDate, PDO::PARAM_STR);
  }
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach($result as $row) {
    $ddate = date("Y-m-d", strtotime((string) $row['ddate']));
    $arrEDP[$row['SchedulingPersonId']][$ddate] = 1;
  }
  if (isset($arrEDP)) {
    return ($arrEDP);
  }
}

/*
*    Function to calculate which days are British bank holidays (England & Wales) for a given year.
*
*    Created by David Scourfield, 07 August 2006, and released into the public domain.
*    Anybody may use and/or modify this code.
*
*    USAGE:
*
*    array calculateBankHolidays(int $yr)
*
*    ARGUMENTS
*
*    $yr = 4 digit numeric representation of the year (eg 1997).
*
*    RETURN VALUE
*
*    Returns an array of strings where each string is a date of a bank holiday in the format "yyyy-mm-dd".
*
*    See example below
*
*/

function calculateBankHolidays($yr) {
    $bankHols = [];

    // New year's:
    switch ( date("w", strtotime("$yr-01-01 12:00:00")) ) {
        case 6:
            $bankHols["$yr-01-03"] = "New Year's Day (*)";
            break;
        case 0:
            $bankHols["$yr-01-02"] = "New Year's Day (*)";
            break;
        default:
            $bankHols["$yr-01-01"] = "New Year's Day";
    }

    // Good friday:
    $bankHols[date("Y-m-d", strtotime( "+".(easter_days($yr) - 2)." days", strtotime("$yr-03-21 12:00:00")))] = "Good Friday";

    // Easter Monday:
    $bankHols[date("Y-m-d", strtotime( "+".(easter_days($yr) + 1)." days", strtotime("$yr-03-21 12:00:00")))] = "Easter Monday";

    // May Day:
    if ($yr == 1995) {
        $bankHols["1995-05-08"] = "May Day"; // VE day 50th anniversary year exception
    } elseif ($yr == 2020) {
        $bankHols["2020-05-08"] = "May Day"; // VE day 50th anniversary year exception
      }
      else {
        switch (date("w", strtotime("$yr-05-01 12:00:00"))) {
            case 0:
                $bankHols["$yr-05-02"] = "May bank holiday";
                break;
            case 1:
                $bankHols["$yr-05-01"] = "May bank holiday";
                break;
            case 2:
                $bankHols["$yr-05-07"] = "May bank holiday";
                break;
            case 3:
                $bankHols["$yr-05-06"] = "May bank holiday";
                break;
            case 4:
                $bankHols["$yr-05-05"] = "May bank holiday";
                break;
            case 5:
                $bankHols["$yr-05-04"] = "May bank holiday";
                break;
            case 6:
                $bankHols["$yr-05-03"] = "May bank holiday";
                break;
        }
    }

    // Whitsun:
    if ($yr == 2002) { // exception year
        $bankHols["2002-06-03"] ="Queen Elizabeth's Golden Jubilee";
        $bankHols["2002-06-04"] = "Spring Bank";;
    } else {
        switch (date("w", strtotime("$yr-05-31 12:00:00"))) {
            case 0:
                $bankHols["$yr-05-25"] = "Spring Bank";
                break;
            case 1:
                $bankHols["$yr-05-31"] = "Spring Bank";
                break;
            case 2:
                $bankHols["$yr-05-30"] = "Spring Bank";
                break;
            case 3:
                $bankHols["$yr-05-29"] = "Spring Bank";
                break;
            case 4:
                $bankHols["$yr-05-28"] = "Spring Bank";
                break;
            case 5:
                $bankHols["$yr-05-27"] = "Spring Bank";
                break;
            case 6:
                $bankHols["$yr-05-26"] = "Spring Bank";
                break;
        }
    }

    // Summer Bank Holiday:
    switch (date("w", strtotime("$yr-08-31 12:00:00"))) {
        case 0:
            $bankHols["$yr-08-25"] = "Summer bank holiday";
            break;
        case 1:
            $bankHols["$yr-08-31"] = "Summer bank holiday";
            break;
        case 2:
            $bankHols["$yr-08-30"] = "Summer bank holiday";
            break;
        case 3:
            $bankHols["$yr-08-29"] = "Summer bank holiday";
            break;
        case 4:
            $bankHols["$yr-08-28"] = "Summer bank holiday";
            break;
        case 5:
            $bankHols["$yr-08-27"] = "Summer bank holiday";
            break;
        case 6:
            $bankHols["$yr-08-26"] = "Summer bank holiday";
            break;
    }

    // Christmas:
    switch ( date("w", strtotime("$yr-12-25 12:00:00")) ) {
        case 5:
            $bankHols["$yr-12-25"] = "Christmas Day";
            $bankHols["$yr-12-28"] = "Boxing Day (*)";
            break;
        case 6:
            $bankHols["$yr-12-27"] = "Christmas Day (*)";
            $bankHols["$yr-12-28"] = "Boxing Day (*)";
            break;
        case 0:
            $bankHols["$yr-12-26"] = "Boxing Day";
            $bankHols["$yr-12-27"] = "Christmas Day (*)";
            break;
        default:
            $bankHols["$yr-12-25"] = "Christmas Day";
            $bankHols["$yr-12-26"] = "Boxing Day";
    }

    // Millenium eve
    if ($yr == 1999) {
        $bankHols["1999-12-31"] = "Millenium Bank Holiday";
    }
    return $bankHols;
}

function calculateBankHolidayst($yr) {
  $bankHols = [];
  try {
    $pdo = OpenDBLinkA7();
    $sql = "SELECT ixDayInMonth, ixMonthInYear, sEvent
            FROM TimeDimension
            WHERE fHolidayFlag = 1 AND ixYear = $yr";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rsBankHolidays = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
  }

  // $bankHols["1999-12-31"] = "Millenium Bank Holiday";
  foreach($rsBankHolidays as $rbh) {
    $intDate = (int)$rbh['ixDayInMonth'];
    $intDate = strlen(trim($intDate)) == 1 ? "0".$intDate : $intDate;

    $intMonth = (int)$rbh['ixMonthInYear'];
    $intMonth = strlen(trim($intMonth)) == 1 ? "0".$intMonth : $intMonth;

    $rbhdate = $yr.'-'.$intMonth.'-'.$intDate;
    $bankHols[$rbhdate] = $rbh['sEvent'];
  }

  // New year's:
  switch ( date("w", strtotime("$yr-01-01 12:00:00")) ) {
    case 6:
      $bankHols["$yr-01-03"] = "New Year's Day (*)";
      break;
    case 0:
      $bankHols["$yr-01-02"] = "New Year's Day (*)";
      break;
    default:
      $bankHols["$yr-01-01"] = "New Year's Day";
  }

  // Christmas:
  switch ( date("w", strtotime("$yr-12-25 12:00:00")) ) {
    case 5:
      $bankHols["$yr-12-25"] = "Christmas Day";
      $bankHols["$yr-12-28"] = "Boxing Day (*)";
      break;
    case 6:
      $bankHols["$yr-12-27"] = "Christmas Day (*)";
      $bankHols["$yr-12-28"] = "Boxing Day (*)";
      break;
    case 0:
      $bankHols["$yr-12-26"] = "Boxing Day";
      $bankHols["$yr-12-27"] = "Christmas Day (*)";
      break;
    default:
      $bankHols["$yr-12-25"] = "Christmas Day";
      $bankHols["$yr-12-26"] = "Boxing Day";
  }
  return ($bankHols);
}
// ########################################## Get the week day number from a week and date
function dayofweek($date, $intWeek) {
  for ($i=0; $i < 7; $i++ ) {
    if (datefromweek($intWeek, $i) == $date) {
      break;
    }
  }
  return $i;
}


function calcmonthdiff($startdate, $enddate) {
  $uStart = strtotime((string) $startdate);
  $uEnd = strtotime((string) $enddate);
  $diff = ((date("Y", $uEnd) * 12) + date("n", $uEnd)) - ((date("Y", $uStart) * 12) + date("n", $uStart));

  if ($diff < 0) {
    $diff = 0;
  }
  if ($diff > 12) {
    $diff = 12;
  }
  return $diff;
}

function ExplodeStringToArray ($strToExplode) {

    $arrTemp = explode(",", (string) $strToExplode);
    foreach ($arrTemp as $key) {
      $key = trim($key);
      $arrReturn[$key] = $key;
    }

  if (isset($arrReturn)) {
    return ($arrReturn);
  }
}

function RoundFifteenMins ($seconds) {
  $rounded_seconds = floor($seconds / (15 * 60)) * (15 * 60);
  return ($rounded_seconds);
}


function GetTimeInDutyFromOffset ($intDutyID, $offset) {

    $query = "SELECT AD_AllocationsDutyID AS ID, AD_StartTimeSec AS StartTime, AD_EndTimeSec AS EndTime, AD_DutyStartTimeUTC AS DutyStartDateTime, AD_DutyEndTimeUTC AS DutyEndDateTime FROM  AllocationsDuties WHERE (AD_AllocationsDutyID = $intDutyID)";

      $pdo = OpenDBLinkA7();
      $stmt = $pdo->prepare($query);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $dutyStartTime = intval($row['StartTime']);
      $dutyEndTime = intval($row['EndTime']);
      $dutyStartDateTime =  new DateTime(date('Y-m-d', strtotime($row['DutyStartDateTime'])));
      $dutyEndDateTime =  new DateTime(date('Y-m-d', strtotime($row['DutyEndDateTime'])));

      $intStartTime = intval($row['StartTime']);
      $intEndTime = intval($row['EndTime']);
      $intDuration = $intEndTime - $intStartTime;
      if(($intStartTime>$intEndTime)){
        $intEndTime= $intEndTime+86400;
      }
      $intStartTime = RoundFifteenMins($intStartTime + (($intEndTime - $intStartTime) * $offset));
      $result['StartTime'] =  $intStartTime;
      $result['dutyStartTime'] =  $dutyStartTime;
      $result['dutyEndTime'] =  $dutyEndTime;
      return $result;
}

function floorToFraction($number, $denominator = 1)
{
    $x = floor($number * $denominator)/$denominator;
    return $x;
}

function CheckisEdited($intTeamID, $intWeek, $day) {
    /* REM: NOT adding InstanceID in where clause as departmentid is coming on the basis of instanceid*/
  $pdo = OpenDBLinkA7();
  $day = intval($day);
  $query = "SELECT       isEdited
            FROM         DayIsEdited
            WHERE        (SchedulingTeamId = $intTeamID)
            AND          (WeekNumber = $intWeek)
            AND          (iDay = $day)";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if(!empty($row))
  {
      $intIsEdited = $row['isEdited'];
  }
  else{
      $intIsEdited = 0;
  }
  return ($intIsEdited);
}

function CheckisEditedPeriod($intTeamId, $intStartWeek, $intEndWeek) {
  $commonObj = new classCommonDBFunctions();
  try {
    $pdo = OpenDBLinkA7();
    $strQuery = "SELECT isEdited, WeekNumber, iDay FROM DayIsEdited WHERE (SchedulingTeamId = ?)
    AND (WeekNumber >= ?) AND (WeekNumber <= ?)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindValue(1, $intTeamId, PDO::PARAM_INT);
    $stmt->bindValue(2, $intStartWeek, PDO::PARAM_INT);
    $stmt->bindValue(3, $intEndWeek, PDO::PARAM_INT);
    $stmt->execute();
    $QueryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($QueryData)) {
      foreach($QueryData as $row) {
        $datefromweek =$commonObj->GetWeekStartDateByWeekNoFromTimeDim($row['WeekNumber'],'ByweeknoAndIDayOnly',$row['iDay']);
        $strCurrentDate = substr((string) $datefromweek['dDateTime'],0,10);
        $arrIsEdited[$strCurrentDate] = $row['isEdited'];
      }
    }
    if(isset($arrIsEdited)) {
      return ($arrIsEdited);
    } else{
      return false;
    }
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
return false;

}
function GetMessage($intMessageID) {
  $strMessage = '';
  $pdo = OpenDBLink();
  $strQuery = "SELECT messagecontent FROM messages WHERE messsageid = $intMessageID";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindValue(1, $intMessageID, PDO::PARAM_INT);
  $stmt->execute();
  $QueryData = $stmt->fetch(PDO::FETCH_ASSOC);
  $strMessage = nl2br((string) $QueryData['messagecontent']);
  return($strMessage);
}

//function to get lock and unloack day for daily allocation screen based on Scheduling Team
function LockUnlockDay ($strDate, $intSchedulingTeamId, $intAction) {

  switch ($intAction) {
    case 0;
      $history = '<hr>Day marked as Locked by '.$_SESSION['user']['FullName'].' at '.date("H:i").' on '.date("d/m/Y");
      break;
    case 1;
      $history = '<hr>Day marked as Un-Locked by '.$_SESSION['user']['FullName'].' at '.date("H:i").' on '.date("d/m/Y");
      break;
    case 2;
      $history = '<hr>Day marked as Auto-Lock by '.$_SESSION['user']['FullName'].' at '.date("H:i").' on '.date("d/m/Y");
      break;
  }
  $history = escapeSingleQuotes($history);
  $strDate = $strDate . " 00:00:00";

try{
	$pdo = OpenDBLinkA7();
	$strQuery = "exec usp_LockUnlockDay ?, ?, ?, ?";
	$stmt = $pdo->prepare($strQuery);
	$stmt->bindParam(1, $history, PDO::PARAM_STR);
	$stmt->bindParam(2, $strDate, PDO::PARAM_STR);
	$stmt->bindParam(3, $intSchedulingTeamId, PDO::PARAM_INT);
	$stmt->bindParam(4, $intAction, PDO::PARAM_INT);
	return (bool)$stmt->execute();
  } catch (PDOException $e) {
		logger()->critical('db error', (array) $e);
		echo $e->getMessage();
        }
}

/**
 * Description : GetLeaveRequest Decription By ID
 * @param : $intGroupID int
 * return []
 */
function GetLeaveRequestDescFromID ($intGroupID) {
  try {
  $pdo = OpenDBLinkA7();
  $query = "SELECT Description FROM LeaveRequestGroups WHERE (id = :intGroupID)";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':intGroupID', $intGroupID, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
 }
  if (!empty($row)) {
  $LeaveRequestGroupDesc = $row['Description'];
  } else {
    $LeaveRequestGroupDesc='';
  }
  return ($LeaveRequestGroupDesc);
}

function ShowInfoHistory ($strLogin) {
      echo '<img border="0" onclick="javascript:ShowUserInfo(\''.$strLogin.'\');" src="../images/info.png" width="12px" height="12px">';
      echo '&nbsp;&nbsp;&nbsp;<img border="0" onclick="javascript:ShowUserHistory(\''.$strLogin.'\');" src="../images/history.png" width="12px" height="12px">';
}

function GetBreaksByType($intTypeID = 0) {
  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT          ID, FromDate, TypeID, Hours, BreakTime
               FROM            BreaksTable (nolock)";
  if ($intTypeID !=0) {
    $strQuery.= " WHERE           (TypeID = $intTypeID)";
  }
  $strQuery.= " ORDER BY        Hours";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $rsBreaks = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach  ($rsBreaks as $row) {
    $intCurrTypeID = $row['TypeID'];
    $arrBreaks[$intCurrTypeID][$row['ID']]['StartDate'] = date("Y-m-d",strtotime((string) $row['FromDate']));
    $arrBreaks[$intCurrTypeID][$row['ID']]['Hours'] = $row['Hours'];
    $arrBreaks[$intCurrTypeID][$row['ID']]['BreakTime'] = $row['BreakTime'];
  }
  if (isset($arrBreaks)) {
    return ($arrBreaks);
  }
}


function GetBreakTypeDesc ($intID) {

  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT       Description, schedulingTeamId
                FROM         BreaksTableTypes (nolock)
                WHERE        (ID = $intID)";

  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  $arrBreak['Description'] = $result['Description'];
  $arrBreak['DefaultTeam'] = $result['schedulingTeamId'];
  return $arrBreak;
}

function GetMealTime($intDur, $strStartDate, $intBreakID, $arrBreaks) {
  $intDur = $intDur/ 100;
  $intBreakTime = 0;
  if (isset($arrBreaks[$intBreakID])) {
    foreach ($arrBreaks[$intBreakID] as $intID => $arrBreak) {
      if (strtotime((string) $strStartDate) >= strtotime((string) $arrBreak['StartDate']) && $intDur >= ($arrBreak['Hours'])) {
        $intBreakTime = $arrBreak['BreakTime'];
      }
    }
  }
  return ($intBreakTime);

}





function bbc_GetFromLDAPFull($username='') {
	$G_AppConfig = [];

	$G_AppConfig["servers"]['core.bbc.co.uk'] = [
												"username" => getenv('AD_CORE_UID'),
												"password" => getenv('AD_CORE_PWD'),
											];

	$G_AppConfig["servers"]['worldwide.bbc.co.uk'] = [
												"username" => getenv('AD_WORLDWIDE_UID'),
												"password" => getenv('AD_WORLDWIDE_PWD'),
											];

	$G_AppConfig["servers"]['global.mon.bbc.co.uk'] = [
												"username" => getenv('AD_GLOBAL_UID'),
												"password" => getenv('AD_GLOBAL_PWD'),
											];

	#
	# prepend $info values with @ to protect in the event of param not defined in AD
	#
	$ldap = [];
	foreach ($G_AppConfig["servers"] as $ldap_server=>$ldap_auth) {
		unset($con);
		if ($con=$con=ldap_connect('ldap://'.$ldap_server.':'. 3268)) {
			ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
			if ($bind=@ldap_bind($con, $ldap_auth["username"], $ldap_auth["password"])) {
				if ($srch=@ldap_search($con, 'dc=bbc,dc=co,dc=uk', "sAMAccountName=$username")) {
					if ($info=ldap_get_entries($con, $srch)) {
						$ldap['email']=@$info[0]['mail'][0];
						$ldap['fullname']=@$info[0]['cn'][0];
            			$ldap['lastname']=@$info[0]['sn'][0];
            			$ldap['firstname']=@$info[0]['givenname'][0];
						break;
					}
				}
			}
		}
	}
	if (isset($ldap) && !empty($ldap)){
		return($ldap);
	}
	return false;
}
// ****************************************************************************************************************'


function getContrastColor($hexColor) {

        // hexColor RGB
        $R1 = hexdec(substr((string) $hexColor, 1, 2));
        $G1 = hexdec(substr((string) $hexColor, 3, 2));
        $B1 = hexdec(substr((string) $hexColor, 5, 2));

        // Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

         // Calc contrast ratio
         $L1 = 0.2126 * ($R1 / 255) ** 2.2 +
               0.7152 * ($G1 / 255) ** 2.2 +
               0.0722 * ($B1 / 255) ** 2.2;

        $L2 = 0.2126 * ($R2BlackColor / 255) ** 2.2 +
              0.7152 * ($G2BlackColor / 255) ** 2.2 +
              0.0722 * ($B2BlackColor / 255) ** 2.2;

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else {
            // if not, return white color.
            return '#FFFFFF';
        }
}
function GetReportsConfig($intShowNull = 1) {
  $strQuery = "SELECT     schedulingTeamId as DepartmentID, schedulingTeamName FullName, staffAvailabilityReportStartDate ReportsStart, defaultDutyDuration DefaultShiftLength
               FROM       schedulingTeams
               WHERE      (isActive = 1)";
  if ($intShowNull == 0) {
    $strQuery.= " AND (NOT (staffAvailabilityReportStartDate IS NULL))";
  }
  $strQuery.= " ORDER BY schedulingTeamName";

  $pdo = OpenDBLinkA7();
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $arrDepartments[0]['DepartmentName']  = "All Departments";
  $arrDepartments[0]['ReportsStart']  = '204001';
  $arrDepartments[0]['DefaultShiftLength']  = 10;

  foreach ($result as $row) {
    $arrDepartments[$row["DepartmentID"]]['DepartmentName']  = $row["FullName"];
    $arrDepartments[$row["DepartmentID"]]['ReportsStart']  = bbcweeknumber(date('Y-m-d', strtotime((string) $row["ReportsStart"])));
    $arrDepartments[$row["DepartmentID"]]['DefaultShiftLength']  = $row["DefaultShiftLength"];
  }
  if (isset($arrDepartments)) {
    return $arrDepartments;
  }
}
function GetAllocateHelpDescriptions() {
  $pdo = OpenDBLinkA7();
  $arrAllocateHelp =$rsAllocateHelp = [];

  $strQuery = "SELECT    ID, Description
  FROM      AllocateDocuments
  ORDER BY Description";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $rsAllocateHelp = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if(!empty($rsAllocateHelp)){
      foreach ($rsAllocateHelp as $row ) {
        $arrAllocateHelp[$row["ID"]] = $row["Description"];
      }
  }
  return ($arrAllocateHelp);
}

function GetUserLogon () {
  if (isset($_SESSION['user']['user'])) {
    $strUser = $_SESSION['user']['user'];
  }
  else {
    $strUser = authUser();
    $_SESSION['user']['user'] = $strUser;
  }
  return ($strUser);
}

function GetLNEMT ($intStartMins, $intEndMins) {
 // after   1365
 // Before  390
  $intLNEMT = 0;
  //after 22:45
  if ($intStartMins >= 1365) {
    $intLNEMT = 1;
  }

  if ($intEndMins > 1365 && $intEndMins <= 1830) {
    $intLNEMT = 1;
  }

  if ($intStartMins < 390) {
    $intLNEMT = 1;
  }
  return ($intLNEMT);
}

function GetStaffDetailsByTeamId ($schedulingTeamId,$type='')
{
  $pdo = OpenDBLinkA7();
  $strQuery = "SELECT DISTINCT spt.ScheduledPersonID,
  UD_DisplayFirstName ,
  UD_DisplayLastName ,
  UD_DisplayName FullName
  FROM ScheduledPersonTeam_LINK (NOLOCK) as spt
  INNER JOIN UserDetails (NOLOCK) as sp ON spt.ScheduledPersonID = SP.UD_UserID
  WHERE spt.TeamID = :teamid
  AND spt.IsHomeTeam = 1
  AND spt.scheduledType = 1
  AND cast(getdate() as date) between spt.StartDate and  isnull(spt.EndDate,'9999-01-01')
  ORDER BY 3";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':teamid', $schedulingTeamId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $arrstaff = [];
  foreach($result as $row )
  {
    $arrstaff[$row['ScheduledPersonID']] = $row['FullName'];
  }

  return ($arrstaff);
}

function GetStaffDetailsByTeamIdMonthly ($schedulingTeamId,$type='')
{
  $pdo = OpenDBLinkA7();
  $strQuery = "exec usp_get_MonthlySchedulepersonDropDownList ?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $schedulingTeamId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $arrstaff = [];
  foreach($result as $row )
  {
    $arrstaff[$row['ScheduledPersonID']] = $row['FullName'];
  }

  return ($arrstaff);
}


function GetTeamsFiltersByLogin ($strLogin) {
  $db = OpenDBLinkA7();
  $query = "SELECT DISTINCT apf.SchedulingTeamId,
	   st.schedulingTeamName as FullName,
	   apf.Description,
	   apf.isPublic,
	   apf.id AS FilterID
    FROM UserDetails u
    INNER JOIN UserRoles utl ON utl.UR_UserID = u.UD_UserID
    INNER JOIN schedulingTeams st ON st.schedulingTeamId = utl.UR_SchedulingTeamID
    INNER JOIN AutoPagesFilters apf ON apf.SchedulingTeamId = st.schedulingTeamId
    WHERE u.UD_NetLogin = :strLogin
    AND CAST(GETDATE() AS DATE) BETWEEN  utl.UR_StartDate AND ISNULL(utl.UR_EndDate,'9999-12-01')
     AND apf.isPublic = 1
    ORDER BY st.schedulingTeamName, apf.Description";
  $stmt = $db->prepare($query);
  $stmt->bindValue(':strLogin', $strLogin, PDO::PARAM_STR);
  $stmt->execute();
  $data =  $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($data as $k => $v){
    $arrFilters[$v['SchedulingTeamId']]['TeamName'] = $v['FullName'];
    $arrFilters[$v['SchedulingTeamId']]['Filters'][$v['FilterID']] = $v['Description'];
  }
  if (isset($arrFilters)) {
    return ($arrFilters);
  }
}

// Function to fetch HourWidth from  User Web Config Details Based on Login
function GetUserWebConfigByUserLogin($strLogin) {
	$result  = "Error";
	$message = "";
	$response_array = [];
	try {
		  $pdo = OpenDBLinkA7();
		  $sql = "exec [dbo].[usp_get_UserWebConfigByUserLogon] ?";
		  $stmt = $pdo->prepare($sql);
		  // The parameters
		  $stmt->bindParam(1, $strLogin, PDO::PARAM_STR);
		  $stmt->execute();
		  $resultArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  $response_array ['status'] = "Success";
		  $response_array ['result'] = $resultArr;
	} catch (PDOException $e) {
			$response_array ['message'] =$e->getMessage();
            logger()->critical('db error', (array) $e);
      }
		return ($response_array);
}

// update and insert HourWidth in User_Web_Config
function UpdateHourWidthByUserLogin($strLogin, $HourWidth,$teamId){
	$result  = "Error";
	$message = "";
	$response_array = [];
    try {
          // Open the database
          $pdo = OpenDBLinkA7();
          // Set the statement to use
          //$sql = "exec [dbo].[usp_UpdateUserWebConfigHourWidth] ?,?";
		  $sql = "exec [dbo].[usp_UpdateUserWebConfigHourWidth] '".$strLogin."','".$HourWidth."',".$teamId;
          $stmt = $pdo->prepare($sql);
          // The parameters
		  $response_array ['result'] = (bool)$stmt->execute();
		  $response_array ['status'] = "Success";
    } catch (PDOException $e) {
			$response_array ['message'] =$e->getMessage();
            logger()->critical('db error', (array) $e);
        }
		return ($response_array);
    }

function drawtimecells ($date, $earlieststart, $lateststart, $hourwidth, $height, $dark = 0,$redlineval=0) {
  for ($hr = 0; $hr < $lateststart; $hr = $hr + 0.25) {
    if ($dark == 1) {
      if ($hr == intval($hr)) {
        $class = 'dayholderdarkhour';
      }
      else {
        $class = 'dayholderdarkparthour';
      }
    }
    else {
      if ($hr == intval($hr)) {
        $class = 'dayholderhour';
      }
      else {
        $class = 'dayholderparthour';
      }
    }
    echo '<div class="'.$class.'" style="width:'.floor($hourwidth / 4).'px; height: '.($height).'px; position: absolute; left:'.$hourwidth * $hr.'px; top:0px"></div>';
    if (strtotime((string) $date) == strtotime(date("Y-m-d"))) {
      //date_default_timezone_set('Europe/London');
      $now = date("H") + (date("i") / 60);
      // and a line at the current time (ish)
      $currtime = round(($now - $earlieststart) * $hourwidth);
	  if ($redlineval==1){
		echo '<div class="highlightedtransparent" style="width:2px; height: '.($height).'px; position: absolute; left:'.$currtime.'px; top:0px"></div>';
	  }
    }
  }
}

function drawtimecellsdaily ($date, $earlieststart, $lateststart, $hourwidth, $height, $dark = 0,$redlineval=0,$j=0) {
  for ($hr = 0; $hr < $lateststart; $hr = $hr + 0.25) {
    if ($dark == 1) {
      if ($hr == intval($hr)) {
        $class = 'dayholderdarkhour';
      }
      else {
        $class = 'dayholderdarkparthour';
      }
    }
    else {
      if ($hr == intval($hr)) {
        $class = 'dayholderhour';
      }
      else {
        $class = 'dayholderparthour';
      }
    }

    echo '<div class="'.$class.'" style="width:'.($hourwidth / 4).'px; height: '.$height.'; position: absolute; left:'.$hourwidth * $hr.'px; top:0px;min-height:100%"></div>';
    if (strtotime((string) $date) == strtotime(date("Y-m-d"))) {
      //date_default_timezone_set('Europe/London');
      $now = date("H") + (date("i") / 60);
      // and a line at the current time (ish)
      $currtime = round(($now - $earlieststart) * $hourwidth);
	  if ($redlineval==1){
		echo '<div class="highlightedtransparent" style="width:2px; height: '.$height.'; position: absolute; left:'.$currtime.'px; top:0px"></div>';
	  }
    }
  }
}

function drawtimecellsMasterJobs ($date, $earlieststart, $lateststart, $hourwidth, $height,$intDutyID,$dutyStartTime,$dutyEndTime,$teamid,$dark = 0) {
	echo '<div class="drawTimePlaceHolder dropeventscall" style="width:'.floor($hourwidth) * $lateststart.'px; height: '.($height).'px; position: absolute;" dutyteamid="'.$teamid.'" dutystart="'.$dutyStartTime.'" duty_end ="'.$dutyEndTime.'" dutyid ="'.$intDutyID.'" ondrop="drop(event)">';
  for ($hr = 0; $hr < $lateststart; $hr = $hr + 24) {
    if ($dark == 1) {
      if ($hr == intval($hr)) {
        $class = 'dayholderdarkhour';
      }
      else {
        $class = 'dayholderdarkparthour';
      }
    }
    else {
      if ($hr == intval($hr)) {
        $class = 'dayholderhour';
      }
      else {
        $class = 'dayholderparthour';
      }
    }
    echo '<div
            class="'.$class.' jobslineonTimeCells TimeCellBackImage" style="width:'.floor($hourwidth * 24).'px; height: '.($height).'px; position: absolute;
            left:'.$hourwidth * $hr.'px; top:0px;" dutyteamid="'.$teamid.'" dutystart="'.$dutyStartTime.'"
            duty_end ="'.$dutyEndTime.'"  dutyid ="'.$intDutyID.'"></div>';
    if (strtotime((string) $date) == strtotime(date("Y-m-d"))) {
      $now = date("H") + (date("i") / 60);
      // and a line at the current time (ish)
      $currtime = round(($now - $earlieststart) * $hourwidth);
      echo '<div class="highlightedtransparent" style="width:2px; height: '.($height).'px; position: absolute; left:'.$currtime.'px; top:0px"></div>';
    }
  }
  echo '</div>';
}

/**
 * Description : This Function gives Color Id By Name From Master Duty Color
 * e.g(Absent,Leave,Sick,Mark Request,UnMark Request)
 * @param $Colorname str
 * return int MasterColorID
 */
function GetColorIdByName($Colorname)
{
  try{
    $pdo = OpenDBLinkA7();
    $strQuery = "exec usp_GET_ColorID_ByName ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $Colorname, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(isset($result['MasterDutyColourID'])) {
      return $result['MasterDutyColourID'];
    } else {
      return 0;
    }
  } catch(PDOException $e) {
    logger()->critical('DB error', (array) $e);
    return false;
  }
}

/**
 * Description : This Function gives User Team Roles based on Team ID and User ID
 *
 * @param $schedulingTeamId
 * return User Team Roles
 */
function GetUserTeamRoleLink($schedulingTeamId)
{
	$result = [];
  try{
  $pdo = OpenDBLinkA7();
  $strQuery = "select UR_RoleID as RoleID,
    UR_UserID as ScheduledPersonID
	  from  UserRoles UR
    left join UserDetails UD on UD_UserID = UR_UserID
    where UR_SchedulingTeamID = :teamID
	  and GETDATE() between UR_StartDate and UR_EndDate";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(':teamID', $schedulingTeamId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $result;
   }
    catch(PDOException $e) {
    logger()->critical('DB error', (array) $e);
    return $result ;
}
}

/**
* Store or Update EDP overtime volunteers details
* @param  $edpId Id of Edp
* @param  $ddate current date of Edp
* @param  $teamId of Edp
* @param  $schedulingPersonId of Edp
* @param  $comments of overtime volunteers
* @param  $action of SP insert, update, delete
* @return string
*/
function saveEdpOvertimeVolunteers($edpId = 0, $ddate='', $teamId=0, $schedulingPersonId=0, $comments = NULL, $action='')
{
  $pdo = OpenDBLinkA7();
  try {
    $status = 1;
    $returnstr = 'success';
    $userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    $username = $_SESSION['user']['FullName'];
    $sql = "exec [dbo].[usp_mod_edpOvertimeVolunteers] ?,?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $edpId, PDO::PARAM_INT);
    $stmt->bindParam(2, $ddate, PDO::PARAM_STR);
    $stmt->bindParam(3, $teamId, PDO::PARAM_INT);
    $stmt->bindParam(4, $schedulingPersonId, PDO::PARAM_INT);
    $stmt->bindParam(5, $comments, PDO::PARAM_STR);
    $stmt->bindParam(6, $userId, PDO::PARAM_INT);
    $stmt->bindParam(7, $username, PDO::PARAM_STR);
    $stmt->bindParam(8, $action, PDO::PARAM_STR);
    $stmt->bindParam(9, $status, PDO::PARAM_INT);
    $stmt->bindParam(10, $returnstr, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return json_encode($result);
  } catch(PDOException $e) {
      logger()->critical('DB error', (array) $e);
  }
}

/**
* Get EDP comments and team
* @param  $ddate current date of Edp
* @param  $schedulingPersonId of Edp
* @return array
*/
function getEdpComments($schedulingPersonId, $ddate)
{
  $pdo = OpenDBLinkA7();
  try {
    $query = "SELECT e.ID, e.comments
          FROM edp e
          WHERE e.SchedulingPersonId = :schedulingPersonId
          AND e.ddate = CONVERT(DATETIME, :ddate, 102)
          ORDER BY e.ID DESC";
    $stmt = $pdo->prepare($query);

    $stmt->bindValue(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
    $stmt->bindValue(':ddate', $ddate, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row;
  }
  catch(PDOException $e) {
    logger()->critical('DB error', (array) $e);
  }
}

/**
* Get EDP overtime volunteers
* @param  $ddate current date of Edp
* @param  $teamId of Edp
* @return array
*/
function getOvertimeVolunteers($ddate, $teamId)
{
  $pdo = OpenDBLinkA7();
  try {
    $query = "select comments, FullName
              from
              (
              select ddate,
                      id,
                  comments,
                  FullName,
                   ROW_NUMBER() over (partition by ddate,fullname order by fullname,comments desc, id desc) as rownum
                from
				  (
				  SELECT e.ddate,
						 e.id,
						 e.comments,
					   sp.UD_DisplayName  AS FullName
				  FROM edp e
				  INNER JOIN UserDetails sp ON e.SchedulingPersonId = sp.UD_UserID
				  INNER JOIN ScheduledPersonTeam_LINK spTeamLink ON spTeamLink.ScheduledPersonID = sp.UD_UserID
				  WHERE e.ddate = CONVERT(DATETIME, :ddate, 102) AND spTeamLink.TeamID = :teamId
					AND spTeamLink.ScheduledType = 1
					AND CONVERT(DATETIME, :ddate2, 102) BETWEEN spTeamLink.startdate AND spTeamLink.enddate
				  ) fd
              ) EDP where rownum = 1 order by id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':ddate', $ddate, PDO::PARAM_STR);
    $stmt->bindValue(':ddate2', $ddate, PDO::PARAM_STR);
    $stmt->bindValue(':teamId', $teamId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }
  catch(PDOException $e) {
    logger()->critical('DB error', (array) $e);
  }
}

   /**
  * Get the allocation details of By ID
  * @param  $allocationId AllocationId of Allocations tbl
  * @return array
  */
  function GetAllocationsById($allocationId) {
    $pdo = OpenDBLinkA7();
    try {
      $strQuery = "exec [dbo].[usp_FetchAllocatioByID] ?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $allocationId, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }
  }

  /**
  * Description: This function gives color value by colorId
  * @param  $colorId colorId of masterDutyColour tbl
  * @return array
  */
  function getColorValueByColorId($colorId) {
    $pdo = OpenDBLinkA7();
    try {
      $query = "exec usp_getMasterColorValueByColorId ?";
      $stmt = $pdo->prepare($query);
      $stmt->bindParam(1, $colorId, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result;
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }

  }

function GetHolidaysByYear($intYear) {
  $pdo = OpenDBLinkA7();
  $query = "SELECT dDateTime, sEvent, ID FROM TimeDimension WHERE (ixLeaveYear = :intYear) AND (ISNULL(sEvent, N'') <> N'') ORDER BY dDateTime";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':intYear', $intYear, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($row as $k => $v){
    $arrEvents[$v['ID']]['Date'] = date('Y-m-d',strtotime((string) $v['dDateTime']));
    $arrEvents[$v['ID']]['Event'] = $v['sEvent'];
  }
  if (isset($arrEvents)) {
    return ($arrEvents);
  }
}

function GetLeaveCreditsByHolidays($intTeamID, $intLeaveYear, $strFieldName, $intTimeDemensionID = 0,$intActiveLeaveYear=0) {
  $activeScheduledPeople = (isset($_COOKIE["activescheduledpeoplePHL_".$intTeamID]) && ($intActiveLeaveYear == $intLeaveYear)) ? $_COOKIE["activescheduledpeoplePHL_".$intTeamID] : 0;

    $pdo = OpenDBLinkA7();
    $query = "exec [dbo].[usp_GET_LeaveCreditByHolidays] ?,?,?,?";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(1, $intTeamID, PDO::PARAM_INT);
    $stmt->bindValue(2, $intLeaveYear, PDO::PARAM_INT);
    $stmt->bindValue(3, $intTimeDemensionID, PDO::PARAM_INT);
    $stmt->bindParam(4, $activeScheduledPeople, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!empty($row)){
        foreach($row as $key => $val){
			$val['SortCode'] = $val['SortCode'] ? $val['SortCode'] : '';
            $arrLeave[$val['ScheduledPersonID']]['Name'] = $val['FullName'];
            $arrLeave[$val['ScheduledPersonID']]['SortCode'] = mb_convert_encoding($val['SortCode'], 'UTF-8', 'ISO-8859-1');
            $arrLeave[$val['ScheduledPersonID']]['Login'] = $val['NetLogin'];
            $arrLeave[$val['ScheduledPersonID']]['PHLLeaveAmount'] = $val['PHLLeaveAmount'];
            $arrLeave[$val['ScheduledPersonID']]['StaffNumber'] = $val['StaffNumber'];
            $arrLeave[$val['ScheduledPersonID']]['TimeDemensionID'] = isset($arrLeave[$val['ScheduledPersonID']]['TimeDemensionID']) ? $arrLeave[$val['ScheduledPersonID']]['TimeDemensionID'] : 0;
            if($intTimeDemensionID != 0 && $val['TDID']==$intTimeDemensionID)
            {
              $arrLeave[$val['ScheduledPersonID']]['TimeDemensionID'] = $intTimeDemensionID;
            }else if(!isset($arrLeave[$val['ScheduledPersonID']]['TimeDemensionID']) && $arrLeave[$val['ScheduledPersonID']]['TimeDemensionID']== 0){
              $arrLeave[$val['ScheduledPersonID']]['TimeDemensionID'] = 0;
            }
        }
    }

    if ($intTimeDemensionID != 0) {
        $query = "SELECT sp.UD_StaffNumber	AS StaffNumber,
			ad.AD_DutyName		AS DutyName,
			ad.AD_Duration		AS Duration,
			ad.AD_DutyBreakTime AS dutyBreakTime,
			sp.UD_UserID		AS SchedulingPersonID
      FROM TimeDimension tmd
      INNER JOIN Allocations AL ON AL.AL_WeekNumber = tmd.ixYearWeek
      INNER JOIN AllocationsScheduledPersons a WITH (NOLOCK) ON AL.AL_AllocationsID = a.ASP_AllocationsID and tmd.ixDayInWeek = a.ASP_iDay
      INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
      INNER JOIN UserDetails sp WITH (NOLOCK) ON sp.UD_UserID = a.ASP_SchedulingPersonID
      WHERE (tmd.ID = :timedimesionid)
        AND (AL_SchedulingTeamID = :teamid)";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':teamid', $intTeamID, PDO::PARAM_INT);
        $stmt->bindValue(':timedimesionid', $intTimeDemensionID, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($row)){
            foreach($row as $key => $val){
                if (isset($arrLeave[$val['SchedulingPersonID']])) {
                    $arrLeave[$val['SchedulingPersonID']]['DutyName'] = $val['DutyName'];
                    $arrLeave[$val['SchedulingPersonID']]['Duration'] = ($val['Duration'] - $val['dutyBreakTime']);
                }
            }
        }
    }
    if (isset($arrLeave)) {
        return ($arrLeave);
    }
  }

function getIDofAllocation($dutyDate,$schedulingPersonID)
{
  $pdo = OpenDBLinkA7();
  try {

    $sql = "select TOP 1 ID from Allocations (nolock)
    where DutyDate = CONVERT(DATETIME, '".$dutyDate."', 102)
    and SchedulingPersonID='".$schedulingPersonID."' and isActive=1 and IsHomeTeam=1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!empty($result)) {
      return $result['ID'];
    } else {
      return 0;
    }
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
}

  /**
  * Description: This function gives auto pages filter details by ID
  * @param  $intFilterID This param contains the filterID information
  * @return array
  */
  function GetFilterDataOptions($intFilterID) {
    $pdo = OpenDBLinkA7();
    try {
        $strQuery = "SELECT Description,SortOrder,SchedulingTeamId FROM AutoPagesFilters WHERE (id = ?)";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intFilterID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty($result)){
          $arrFilter['Description'] = $result['Description'];
          $arrFilter['DepartmentID'] = $result['SchedulingTeamId'];
          $arrFilter['SortOrder'] = $result['SortOrder'];
          if (isset($arrFilter)) {
            return ($arrFilter);
          }
        }
    } catch(Exception $e) {
        logger()->critical('DB Error', (array) $e);
    }
  }

/**
* Description: This function gives scheduled person name by ID
* @param  $schpersonId This param contains the scheduled person Id information
* @return array
*/
function GetScheduledPersonDetailsById($schPersonId)
{
  $pdo = OpenDBLinkA7();
  try {
    $strQuery = "select UD_DisplayName AS FullName from UserDetails where UD_UserID = ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $schPersonId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result;
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
}

 /*
  * @Description :get date range spliited
	* @access : Public
	* @global : Not Applicable
	* @param  :$dates
	* @return :array result
  */

function getDateRangeSplitted($dates)
{
    if (!is_array($dates) || empty($dates)) {
        return [];
    }
    sort($dates);
    $count = count($dates);
    $startDate  = $dates[0];
    $finishDate = $dates[$count-1];
    $result = [];
    // walk through the dates, breaking at gaps
    foreach ($dates as $key => $date){
        if (($key > 0) && (strtotime((string) $date)-strtotime((string) $dates[$key-1]) > 99999))
        {
            $result[] = [$startDate,$dates[$key-1]];
            $startDate = $date;
        }
    }
    // force the end
    $result[] = [$startDate,$finishDate];
    return $result;
}

/*
  * @Description :get leave year
	* @access : Public
	* @global : Not Applicable
	* @param  :$dates
	* @return :array result
  */

  function getLeaveYearOfCell($celldate,$isDivisionalAdmin,$isSysAdmin,$isSchedulingTeamAdmin)
  {
    $status='yes';
    $currentDate = date("Y-m-d");
    $currentYear = date("Y");

      if($currentDate <= $currentYear."-06-30"){

        $newYear = $currentYear - 1;
      }else{
        $newYear = $currentYear;
      }

      if($isDivisionalAdmin == 1 || $isSysAdmin == 1 || $isSchedulingTeamAdmin==1){
        $status='yes';
      }else{
        if($celldate < $newYear."-04-01"){
          $status='no';
        }
      }
      return $status;
  }

  /**
 * Description :Function to Get Schedulled Person ID By NetloginId
 * @param : netlogin str
 * return int
 */

function getScheduledPersonIDByNetLoginID($loginID)
{
  $pdo = OpenDBLinkA7();
  try {
    $sql = "SELECT UD_UserID as ScheduledPersonID FROM userDetails (Nolock) WHERE UD_NetLogin =?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $loginID, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty($result)) {
      return $result['ScheduledPersonID'];
    } else {
      return 0;
    }
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
}

/**
 * Description :Function to Get Assign Team and their emailId
 * @param : loginID int
 * return []
 */

function getAssignTeamEmailByNetLoginID($scheduledPersonId)
{
  $pdo = OpenDBLinkA7();
  try {
    $sql = "exec [dbo].[usp_getAssignTeamEmailByNetLoginID] :scheduledPersonId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':scheduledPersonId', $scheduledPersonId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($result)) {
      return $result;
    } else {
      return [];
    }
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
}

/**
 * Description :Get break by team
 * @param : teamid int
 * return []
 */

function GetBreaksByTeam($intTeamID) {
  $pdo = OpenDBLinkA7();
  try {
    $sql = "exec [dbo].[usp_get_BreaksByTeam] ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $rsStaff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($rsStaff)) {
      foreach($rsStaff as $row ){
        $arrStaff[$row['StaffNumber']] = $row['BreaksTypeID'];
      }
      return ($arrStaff);
    } else {
      return [];
    }
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }

}

 /**
  * Get the fulName from staffDetails table
  * @param  $ScheduledPersonId int
  * @return []
  */
  function GetFullNameFromScheduledPersonId($ScheduledPersonId) {
    $pdo = OpenDBLinkA7();
    try {
      $strQuery = "SELECT sd.UD_DisplayName AS userDisplayName,sd.UD_NetLogin as NetLogin
                    from UserDetails sd (nolock) 
                    where sd.UD_UserID = ?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $ScheduledPersonId, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }

  }
/*
This function is  replacement of GetStaffOtionsByDepartment()
*/
function getTeamsAndShowReportFlag() {
  $sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $pdo = OpenDBLinkA7();
  $sql = "exec [dbo].[usp_get_MySetUp] ?,?";
  $stmt = $pdo->prepare($sql);
  // The parameters
  $stmt->bindParam(1, $sessUserId, PDO::PARAM_INT);
  $stmt->bindParam(2, $sessUserNetLogin, PDO::PARAM_STR);
  $stmt->execute();
  $resultUserSetup = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $resultUserSetup;
}

  /**
  * Get the scheduling team by person id
  * @param  $ScheduledPersonId int
  * @return []
  */
  function GetSchedulingTeamIdByPersonID ($scheduledPersonId,$startDate) {
      try{
          $pdo = OpenDBLinkA7();
          $strQuery = "exec usp_get_SchedulingTeamIdByPersonID ?,?";
          $stmt = $pdo->prepare($strQuery);
          $stmt->bindParam(1, $startDate, PDO::PARAM_STR);
          $stmt->bindParam(2, $scheduledPersonId, PDO::PARAM_INT);
          $stmt->execute();
          $result = $stmt->fetch(PDO::FETCH_ASSOC);
          return $result;
      } catch(Exception $e) {
          logger()->critical('DB Error', (array) $e);
      }
  }

  /**
* This function is used to get the Scheduled Person Team details of a paticular scheduled person.
*
* @param $scheduledPersonId This param contains the ScheduledPersonId.
*
* @return $arrName array Returns the Details of Scheduled Person.
*/
function GetScheduledPersonTeamDetailsByUserID($userID){

  $pdo = OpenDBLinkA7();

    $strQuery = "exec [dbo].[usp_get_SchedulepersonteamsdetailsByUserID] ?";

    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $userID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $arrName = [];
    if(!empty($result)){
      $arrName['ScheduledPersonID']= $result[0]['ScheduledPersonID'];
    foreach ($result as $row) {
        $arrName[$row['TeamID']]['FullName'] = $row['DisplayName'];
        $arrName[$row['TeamID']]['Login'] = $row['NetLogin'];
        $arrName[$row['TeamID']]['StaffNumber'] = $row['StaffNumber'];
        $arrName[$row['TeamID']]['isDefault'] = $row['isDefault'];
        $arrName[$row['TeamID']]['IsHomeTeam'] = $row['IsHomeTeam'];
        $arrName[$row['TeamID']]['Email'] = $row['InternalEmail'];
		    $arrName[$row['TeamID']]['ScheduledPersonID'] = $row['ScheduledPersonID'];

        if ($row['isDefault'] == 1) {
          $arrName['defaultSchedulingTeamId'] = $row['TeamID'];
        }
        if ($row['IsHomeTeam'] == 1) {
          $arrName['homeTeamId'] = $row['TeamID'];
        }
    }
  }
    return ($arrName);
}

function checkScheduledOrNonScheduled($schedulingPersonId,$intGroupID){

  $pdo = OpenDBLinkA7();
  try {
        $strQuery = "select st.schedulingTeamId
        from UserDetails ud (NOLOCK)
        INNER join ScheduledPersonTeam_LINK spl (NOLOCK) on spl.ScheduledPersonID = ud.UD_UserID
        AND StartDate<=convert(date,getdate(),110) AND EndDate >= convert(date,getdate(),110)
        INNER join schedulingTeams st (NOLOCK) on st.schedulingTeamId= spl.TeamID  and st.isActive =1
        WHERE EXISTS (SELECT 1 FROM  Staff_Web_Config_LeaveGroups_Link sdlc WHERE ud.UD_UserID = sdlc.ScheduledPersonID and (LeaveGroupID = :intGroupID) and isActive=1)
        and ud.UD_UserID = :schedulingPersonId";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(':intGroupID', $intGroupID, PDO::PARAM_INT);
        $stmt->bindParam(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['schedulingTeamId'];

  } catch(Exception $e) {
       logger()->critical('DB Error', (array) $e);
  }
}

function GetPersonNamebyUserId($UserId)
{
   $pdo = OpenDBLinkA7();
   $strQuery = "select UD_UserID as ScheduledPersonID, UD_DisplayName as DisplayName from UserDetails (nolock) WHERE (UD_UserID = '$UserId')";
   $stmt = $pdo->prepare($strQuery);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result;
}

function getYearLastWeekNumber ($year=0) {
    $pdo = OpenDBLinkA7();
    $strQuery = "SELECT max(ixWeekInYear) as maxweek FROM TimeDimension (nolock) WHERE ixYear=?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $year, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getSchedulingTeamDetails ($teamId) {
  $pdo = OpenDBLinkA7();
  $strQuery = "exec [dbo].[usp_get_ScheduleTeamsdetails] ?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC);
}
/*
* Function returns the scheduled person team history
* @param int $scheduledPersonId Scheduled person ID
*/
function getScheduledPersonTeamHistory(int $scheduledPersonId) {
    $pdo = OpenDBLinkA7();
    $strQuery = "SELECT
    CASE
        WHEN SPL.IsHomeTeam = 1 THEN 'Y'
        WHEN SPL.IsHomeTeam = 0 THEN 'N'
        END as HomeTeam,
    FORMAT (SPL.StartDate,'dd-MM-yyyy') AS Startdate,
    FORMAT (SPL.Enddate,'dd-MM-yyyy') AS Enddate,
    SPL.SortCode,
    FORMAT(SPL.LastUpdatedDate, 'dd-MM-yyyy HH:mm:ss') as LastUpdatedDate,
    Te.schedulingTeamName as ScheduleTeam,
	  u.UD_DisplayName as 'LastupdatedBy',
	   CASE
        WHEN (SPL.IsHomeTeam  = 0 AND SPL.IsAvailable = 1) THEN 'Y'
        WHEN (SPL.IsHomeTeam  = 0 AND SPL.IsAvailable = 0) THEN 'N'
        END as IsAvailable,
		SPL.TeamID,
		SPL.CreatedBy,
		FORMAT(SPL.CreatedDate, 'dd-MM-yyyy HH:mm:ss') as CreatedDate
    FROM UserDetails SP (NOLOCK)
    INNER JOIN ScheduledPersonTeam_LINK SPL (NOLOCK) ON SP.UD_UserID = SPL.ScheduledPersonID
    INNER JOIN schedulingTeams Te (NOLOCK) ON  SPL.TeamID = Te.schedulingTeamid
    LEFT JOIN UserDetails u (NOLOCK) ON SPL.LastUpdatedBy = u.UD_UserID
    WHERE SP.UD_UserID = :userId
    and SPL.scheduledType = 1
    ORDER BY SPL.StartDate DESC";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':userId', $scheduledPersonId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function GetIsSysAdmin($strUser) {
  if (isset($_SESSION['user']['SysAdmin'])) {
    $intSysAdmin = $_SESSION['user']['SysAdmin'];
  }
  else {
    // Open the database
    $pdo = OpenDBLinkA7();
    $sql = "SELECT SysAdmin FROM Staff_Web_Config WHERE (Login = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1,$strUser,PDO::PARAM_STR);
    $stmt->execute();
    $isSysAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!empty($isSysAdmin)) {
        $intSysAdmin = $isSysAdmin['SysAdmin'];
      }
      else {
        $intSysAdmin = 0;
    }
  }
  return ($intSysAdmin);
}

function GetDepartmentAccessByLogin($strLogin, $intDepartmentID)
{
  $pdo = OpenDBLinkA7();
  $strQuery = "SELECT ISNULL(isAdmin, 0) AS Admin, ISNULL(isScheduler, 0) AS isScheduler
               FROM Staff_Web_Config_Departments_Link
               WHERE Login = ? AND DepartmentID = ?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute([$strLogin, $intDepartmentID]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $arrDepartment["isScheduler"]  = $row["isScheduler"];
    $arrDepartment["isAdmin"]      = $row["Admin"];
  } else {
    $arrDepartment["isScheduler"]  = 0;
    $arrDepartment["isAdmin"]      = 0;
  }
  return $arrDepartment;
}

function GetAllocateHelpDetail($intID) {
  $pdo = OpenDBLinkA7();
  $strQuery = "SELECT    ID, Description, ContentText
               FROM      AllocateDocuments
               WHERE     (ID = $intID)";
               $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1,$intID,PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row)
    {
      $arrAllocateHelp['Description'] = $row["Description"];
      $arrAllocateHelp['Content'] = $row["ContentText"];
      return ($arrAllocateHelp);
    }
    else{
      $arrAllocateHelp['Description'] = NULL;
      $arrAllocateHelp['Content'] = NULL;
    }
}

function GetGroupNameFromID($id) {
  $pdo = OpenDBLinkA7();
  $strQuery = "SELECT description
               FROM user_favourites
               WHERE ID = ?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $id, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    return $row['description'];
  } else {
    return null;
  }
}

function getallocationSpid($date, $scheduledPersonID, $allocID){
  $pdo = OpenDBLinkA7();
  $strQuery = "SELECT ap.ASP_AllocationsSPID AS AllocationsSPID 
   FROM Allocations AL
   INNER JOIN AllocationsScheduledPersons ap ON AL_AllocationsID = ASP_AllocationsID AND ap.ASP_DutyDate = ? AND ap.ASP_SchedulingPersonID = ?
   INNER JOIN UserDetails sp ON sp.UD_UserID = ap.ASP_SchedulingPersonID
   WHERE AL_AllocationsID = ?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $date, PDO::PARAM_STR);
  $stmt->bindParam(2, $scheduledPersonID, PDO::PARAM_INT);
  $stmt->bindParam(3, $allocID, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty($result)){
      return $result['AllocationsSPID'];
    }else{
      return 0;
    }
}

function spoofUserSendEmailSecurity($actualUser = '', $spoofUser = '', $spoofSecurityEmail = ''){
  $mail = new PHPMailer\PHPMailer\PHPMailer();

  $mail->isSMTP();
  $mail->SMTPDebug = 0;
  $mail->Host = getenv('SMTP_HOST');
  $mail->Port = 25;
  $mail->setFrom('noreply@bbc.co.uk', 'Allocate');
  $mail->addAddress($spoofSecurityEmail);
  $mail->Subject = "Allocate - Security Alert";
  $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
  $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
  $html = '<style>' . file_get_contents(getenv('EMAIL_CSS')) . '</style>
      <body>
      <img alt="Banner" src="cid:pobanner" /><br><br>';

  $html .= '<p>'.$actualUser.' is trying to access account of '.$spoofUser.'. Please connect and check for reason.</p>';

  $html .= '<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
  $html .= '</body></html>';
  $mail->msgHTML($html);
  if (!$mail->send()) {
      $emailSentLogMessage = $mail->ErrorInfo;
  } else {
      $emailSentLogMessage = "Email sent for security alert";
  }

  logger('Impersonate_User/impersonate_user')->CRITICAL($emailSentLogMessage);
}
