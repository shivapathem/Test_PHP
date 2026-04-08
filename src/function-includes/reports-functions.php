<?php

function CountStaffInDepartmentWithSkill ($intDepartmentID) {
  $strQuery = "SELECT COUNT(DISTINCT UD_UserID) AS CountStaff 
                FROM UserDetails ud(NOLOCK) 
                INNER JOIN ScheduledPersonTeam_LINK sptl(nolock) ON sptl.ScheduledPersonID = ud.UD_UserID 
                INNER JOIN skills_programmes_staff_link spl(nolock) ON ud.UD_UserID = spl.userid
                INNER JOIN skills_programmes skp (nolock) ON spl.programmes_id = skp.ID AND sptl.TeamID = skp.TeamID
                WHERE (sptl.TeamID = ?) AND (skp.TeamID = ?)";
  $pdo = OpenDBLinkA7();
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
  $stmt->bindParam(2, $intDepartmentID, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return ($result[0]['CountStaff']);
}

function CountProgrammesInDepartment ($intDepartmentID) {
  $strQuery = "SELECT COUNT(programmename) AS CountProgs
            FROM  skills_programmes
            WHERE (TeamId = $intDepartmentID)";

  $db = OpenDatabase();
  $rscountprogs = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rscountprogs);

  return ($row['CountProgs']);

}

function CountDutiesInDepartment ($intDepartmentID, $intBST) {
  if ($intBST == 1) {
    $strQuery = "SELECT        COUNT(ID) AS CountDuties
              FROM          skills_duties
              WHERE         (BST = 1)
              AND           (TeamId = $intDepartmentID)";
  }
  else {
    $strQuery = "SELECT        COUNT(ID) AS CountDuties
              FROM          skills_duties
              WHERE         (GMT = 1)
              AND           (TeamId = $intDepartmentID)";
  }
  //echo $strQuery;
  $db = OpenDatabase();
  $rscountprogs = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rscountprogs);

  return ($row['CountDuties']);

}

function GetSkillsCountByDepartment($intDepartmentID) {
  $pdo = OpenDBLinkA7();
  $strQuery = "exec [dbo].[usp_GetSkillsCountByTeam] ?";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($result as $row) {
    $arrStaff[$row['StaffNumber']]['name'] = $row['FullName'];
    if ($row['TeamID'] == $intDepartmentID) {
      $arrStaff[$row['StaffNumber']][$row['TeamID']] = $row['skillscount'];
    }
    else {
      $arrStaff[$row['StaffNumber']]['AdditionalDepartments'][$row['TeamID']] = $row['skillscount'].' ('.$row['DepartmentName'].')';
    }
  }
  if (isset($arrStaff)) {
    return ($arrStaff);
  }

}

function GetStaffAllowedOnAttachment($intDepartmentID) {
  $db = OpenDatabase();

    $query = "SELECT        Value3, Value4
              FROM          ReportsConfig
              WHERE         (Value1 = $intDepartmentID) AND (Value2 = $intDepartmentID)";


    $rsAttachments = sqlsrv_query($db, $query);
    $row = sqlsrv_fetch_array($rsAttachments);
    if (is_null($row['Value3'])) {
      $arrAllowedAttachments[1] = 0;
    }
    else {
      $arrAllowedAttachments[1] = $row['Value3'];
    }
    if (is_null($row['Value4'])) {
      $arrAllowedAttachments[2] = 0;
    }
    else {
      $arrAllowedAttachments[2] = $row['Value4'];
    }

  if (isset($arrAllowedAttachments)) {
    return ($arrAllowedAttachments);
  }
}

function GetStaffOnAttachment($intDepartmentID) {
  $db = OpenDatabase();
  $strSQL = "SELECT         Staff.Forename + ' ' + Staff.Surname as FullName, Staff.DepartmentID, Staff.Login, ISNULL(staff_status.IsOnAttach, 0) AS IsOnAttach, staff_status.AttachStartDate,
                           staff_status.AttachEndDate
             FROM          Staff
             INNER JOIN    staff_status ON Staff.Login = staff_status.Login
             WHERE         (Staff.DepartmentID = $intDepartmentID)
             AND           (NOT (staff_status.AttachStartDate IS NULL))
             AND           (NOT (staff_status.AttachEndDate IS NULL))
             AND           (ISNULL(staff_status.IsOnAttach, 0) <> 0)
             ORDER BY       Staff.Surname, Staff.Forename";
  $rsAttachments = sqlsrv_query($db, $strSQL);
  while ($row = sqlsrv_fetch_array($rsAttachments)) {
    $arrAttachments[$row['IsOnAttach']][$row['Login']]['FullName'] = $row['FullName'];
    $strStartDate = $row['AttachStartDate']->format('Y-m-d');
    $strEndDate = $row['AttachEndDate']->format('Y-m-d');


    $arrAttachments[$row['IsOnAttach']][$row['Login']]['StartDate'] = strtotime($strStartDate);
    $arrAttachments[$row['IsOnAttach']][$row['Login']]['EndDate'] = strtotime($strEndDate);

  }


  if (isset($arrAttachments)) {
    return ($arrAttachments);
  }

}

function GetAllocationsForExtras ($intTeamId, $intStartWeek, $intEndWeek, $strSortcodeFilter) {
  $pdo = OpenDBLinkA7();
  $strSQL = "SELECT         WeekNumber, iDay, SUM(CASE WHEN NOT (StartTime IS NULL) AND NOT (StaffNumber IS NULL) THEN 1 ELSE 0 END) AS WorkingCount,
                            SUM(CASE WHEN NOT (StartTime IS NULL) AND NOT (StaffNumber IS NULL) THEN Duration ELSE 0 END) AS WorkingDuration,
                            SUM(CASE WHEN DutyName LIKE N'[a-z]%9%' AND NOT (StaffNumber IS NULL) THEN 1 ELSE 0 END) AS TrainingCount,
                            SUM(CASE WHEN DutyName LIKE N'[a-z]%9%' AND NOT (StaffNumber IS NULL) THEN Duration ELSE 0 END) AS TrainingDuration,

                            SUM(CASE WHEN DutyName LIKE N'[a-z][0-7]%' AND NOT (StaffNumber IS NULL) THEN 1 ELSE 0 END) AS MasterCount,
                            SUM(CASE WHEN DutyName LIKE N'[a-z][0-7]%' AND NOT (StaffNumber IS NULL) THEN Duration ELSE 0 END) AS MasterDuration
             FROM           Allocations
             WHERE          (schedulingTeamId = ?)";
             if ($strSortcodeFilter != '') {
               $strSQL.= " AND (REPLACE(DutyName, ' ', '') LIKE N'%|$strSortcodeFilter%')";
             }

  $strSQL.= " GROUP BY       WeekNumber, iDay
              HAVING         (WeekNumber >= ?)
              AND            (WeekNumber <= ?)
              ORDER BY       WeekNumber, iDay";
  $stmt = $pdo->prepare($strSQL);
  $stmt->bindParam(1, $intTeamId, PDO::PARAM_INT);
  $stmt->bindParam(2, $intStartWeek, PDO::PARAM_INT);
  $stmt->bindParam(3, $intEndWeek, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($result as $row)
  {
    $arrAllocations[$row['WeekNumber']]['Days'][$row['iDay']]['WorkingCount'] = $row['WorkingCount'];
    $arrAllocations[$row['WeekNumber']]['Days'][$row['iDay']]['WorkingDuration'] = $row['WorkingDuration'] - $row['WorkingCount'];
    $arrAllocations[$row['WeekNumber']]['Days'][$row['iDay']]['TrainingCount'] = $row['TrainingCount'];
    $arrAllocations[$row['WeekNumber']]['Days'][$row['iDay']]['TrainingDuration'] = $row['TrainingDuration'] - $row['TrainingCount'];
    $arrAllocations[$row['WeekNumber']]['Days'][$row['iDay']]['MasterCount'] = $row['MasterCount'];
    $arrAllocations[$row['WeekNumber']]['Days'][$row['iDay']]['MasterDuration'] = $row['MasterDuration'] - $row['MasterCount'];

    if (isset($arrAllocations[$row['WeekNumber']]['Totals']['WorkingCount'])) {
      $arrAllocations[$row['WeekNumber']]['Totals']['WorkingCount'] = $arrAllocations[$row['WeekNumber']]['Totals']['WorkingCount'] + $row['WorkingCount'];
    }
    else {
      $arrAllocations[$row['WeekNumber']]['Totals']['WorkingCount'] = $row['WorkingCount'];
    }

    if (isset($arrAllocations[$row['WeekNumber']]['Totals']['WorkingDuration'])) {
      $arrAllocations[$row['WeekNumber']]['Totals']['WorkingDuration'] = $arrAllocations[$row['WeekNumber']]['Totals']['WorkingDuration'] + $row['WorkingDuration'];
    }
    else {
      $arrAllocations[$row['WeekNumber']]['Totals']['WorkingDuration'] = $row['WorkingDuration'];
    }

    if (isset($arrAllocations[$row['WeekNumber']]['Totals']['TrainingCount'])) {
      $arrAllocations[$row['WeekNumber']]['Totals']['TrainingCount'] = $arrAllocations[$row['WeekNumber']]['Totals']['TrainingCount'] + $row['TrainingCount'];
    }
    else {
      $arrAllocations[$row['WeekNumber']]['Totals']['TrainingCount'] = $row['TrainingCount'];
    }

    if (isset($arrAllocations[$row['WeekNumber']]['Totals']['TrainingDuration'])) {
      $arrAllocations[$row['WeekNumber']]['Totals']['TrainingDuration'] = $arrAllocations[$row['WeekNumber']]['Totals']['TrainingDuration'] + $row['TrainingDuration'];
    }
    else {
      $arrAllocations[$row['WeekNumber']]['Totals']['TrainingDuration'] = $row['TrainingDuration'];
    }

    if (isset($arrAllocations[$row['WeekNumber']]['Totals']['MasterCount'])) {
      $arrAllocations[$row['WeekNumber']]['Totals']['MasterCount'] = $arrAllocations[$row['WeekNumber']]['Totals']['MasterCount'] + $row['MasterCount'];
    }
    else {
      $arrAllocations[$row['WeekNumber']]['Totals']['MasterCount'] = $row['MasterCount'];
    }

    if (isset($arrAllocations[$row['WeekNumber']]['Totals']['MasterDuration'])) {
      $arrAllocations[$row['WeekNumber']]['Totals']['MasterDuration'] = $arrAllocations[$row['WeekNumber']]['Totals']['MasterDuration'] + $row['MasterDuration'];
    }
    else {
      $arrAllocations[$row['WeekNumber']]['Totals']['MasterDuration'] = $row['MasterDuration'];
    }
  }
  if (isset($arrAllocations)) {
    return($arrAllocations);
  }
}

function ReadStaffForReports ($teamId, $intIsAdmin = 1)
{
  $pdo = OpenDBLinkA7();
  $strSQL = "exec ReadStaffForReports ?";
  $stmt = $pdo->prepare($strSQL);
  $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($result as $row){
    $arrStaff[$row['StaffNumber']]['Name'] = $row['FullName'];
    $arrStaff[$row['StaffNumber']]['SortCode'] = $row['SortCode'];
    $arrStaff[$row['StaffNumber']]['SortCodeExpanded'] = $row['SortCode'];
    $arrStaff[$row['StaffNumber']]['Login'] = $row['Login'];
    $arrStaff[$row['StaffNumber']]['Designation'] = $row['Designation'] ?? null;
    $arrStaff[$row['StaffNumber']]['HidePerson'] = $row['HidePerson'];
    $arrStaff[$row['StaffNumber']]['EFT'] = $row['EFT'];
    $arrStaff[$row['StaffNumber']]['Team'] = $row['TeamDescription'];
    $arrStaff[$row['StaffNumber']]['Manager'] = $row['Manager'] ?? null;
    $arrStaff[$row['StaffNumber']]['Scheduler'] = $row['Scheduler'] ?? null;
    $arrStaff[$row['StaffNumber']]['HideStaffList'] = $row['HideStaffList'] ?? null;
    $arrStaff[$row['StaffNumber']]['ContractType'] = $row['ContractType'] ?? null;
  }

  if (isset($arrStaff)) {
    return($arrStaff);
  }
}

function ReadMasterDuties($intDepartmentID, $intStartWeekNumber, $intEndWeekNumber) {
  global $intNightStartMins;
  $pdo = OpenDBLinkA7();
  $intCountWeekNumber = $intStartWeekNumber;
  while ($intCountWeekNumber <= $intEndWeekNumber) {
    $arrWeeks[$intCountWeekNumber] = spinweek($intCountWeekNumber);
    $intCountWeekNumber = addweeks($intCountWeekNumber, 1);
  }
	$strSQL = "exec [dbo].[usp_ReadMasterDuties] ?, ?, ?";
	$stmt = $pdo->prepare($strSQL);
	$stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
	$stmt->bindParam(2, $intStartWeekNumber, PDO::PARAM_INT);
	$stmt->bindParam(3, $intEndWeekNumber, PDO::PARAM_INT);
	$stmt->execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	///////////////////////////Fetch night code rate starts//////////////////////////////////////
	$strSQL1 = "SELECT  top 1 * FROM NightPay ORDER BY  EffectiveFrom";
	$stmt1 = $pdo->prepare($strSQL1);
	$stmt1->execute();
	$nightRateArr = $stmt1->fetch(PDO::FETCH_ASSOC);
	///////////////////////////Fetch night code rate ends//////////////////////////////////////

  foreach($result as $row) {
    foreach ($arrWeeks as $intCurrWeek => $strCurrWeek) {
      $intDoIt = 0;
      for ($iDay = 0; $iDay <=6; $iDay++) {
        if ($row[$iDay] != 0) {
          $intDoIt = 1;
        }
      }

      if ($intStartWeekNumber <= $row['WeekNumber'] && $intEndWeekNumber >= $row['WeekNumber'] && $intDoIt == 1) {
        // Do it....
        // Only if one day isn't zero
        $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['DutyName'] = trim($row['DutyName']);
        $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['DutyDesc'] = trim($row['TeamDesc']);
        $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['BaseName'] = trim($row['BaseName']);
		if($row["EndTime"] > 86400)
		{
			$row["EndTime"] = $row["EndTime"] - 86400;
		}
		$startTimeHr	=	intval($row["StartTime"]/ 3600);
		$startTimeMin	=	intval(($row["StartTime"] % 3600) / 60);
		$endTimeHr		=	intval($row["EndTime"]/ 3600);
		$endTimeMin		=	intval(($row["EndTime"] % 3600) / 60);
		$startTimeHr    = 	(strlen($startTimeHr) == 2) ? $startTimeHr : '0'.$startTimeHr;
		$startTimeMin   = 	(strlen($startTimeMin) == 2) ? $startTimeMin : '0'.$startTimeMin;
		$endTimeHr	    = 	(strlen($endTimeHr) == 2) ? $endTimeHr : '0'.$endTimeHr;
		$endTimeMin	    = 	(strlen($endTimeMin) == 2) ? $endTimeMin : '0'.$endTimeMin;
        $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['StartMinutes'] = $startTimeHr.':'.$startTimeMin;
        $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['EndMinutes'] = $endTimeHr.':'.$endTimeMin;

        if (($row['StartTime'] >= 68400) || ($row['StartTime'] <= 10800)) {
          $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['isNight'] = 1;
        }
        else {
          $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['isNight'] = 0;
        }
			$intDuration = $row['DutyDuration'];
        $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['DutyDuration'] = number_format(($intDuration / 3600), 2, '.', '');

        $intMealBreak = $row['BreakTime'];
        $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['DutyDurationLessMeal'] =  number_format(($intDuration - $intMealBreak) / 3600, 2, '.', '');
        // Loop throught the days
		$arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekCount'] = 0;
        for ($iDay = 0; $iDay <=6; $iDay++) {
          if (isset($arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekCount'])) {
            $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekCount'] = $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekCount'] + $row[$iDay];
          }
          else {
            $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekCount'] = $row[$iDay];
          }

          if (isset($arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekHours'])) {
            $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekHours'] = $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekHours'] + ($intDuration - $intMealBreak) * $row[$iDay];
          }
          else {
            $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['WeekHours'] = ($intDuration - $intMealBreak) * $row[$iDay];
          }

			$nightCostHigh = 0;
			$nightCostLow = 0;
			if(($row["StartTime"] + $row["EndTime"]) > 0)
			{
				if($row["StartTime"] <= $row["EndTime"])
				{
					//logic for same day duty
					//21600 = 06:00 AM, 14400 = 04:00 AM
					if(($row["EndTime"] >= 21600) && ($row["StartTime"] <= 14400))
					{
						$nightCostHigh	=	(2 * $nightRateArr['HighNight']) + (((14400 - $row["StartTime"]) / 3600) * $nightRateArr['LowNight']);
					}
					if(($row["EndTime"] >= 21600) && ($row["StartTime"] > 14400) && ($row["StartTime"] <= 21600))
					{
						$nightCostHigh	=	((21600 - $row["StartTime"]) / 3600) * $nightRateArr['HighNight'];
					}
					if(($row["EndTime"] < 21600) && ($row["StartTime"] > 14400))
					{
						$nightCostHigh	=	(($row["EndTime"] - $row["StartTime"]) / 3600) * $nightRateArr['HighNight'];
					}
					if(($row["EndTime"] < 21600) && ($row["StartTime"] <= 14400) && ($row["EndTime"] > 14400))
					{
						$nightCostHigh	=	(($row["EndTime"] - 14400) / 3600) * $nightRateArr['HighNight'];
					}
					if(($row["EndTime"] >= 14400) && ($row["StartTime"] == 0))
					{
						$nightCostLow	=	4 * $nightRateArr['LowNight'];
					}
					if(($row["EndTime"] < 14400) && ($row["StartTime"] == 0))
					{
						$nightCostLow	=	(($row["EndTime"] - $row["StartTime"]) / 3600) * $nightRateArr['LowNight'];
					}
					if(($row["EndTime"] >= 14400) && ($row["StartTime"] > 0) && ($row["EndTime"] <= 21600))
					{
						$nightCostLow	=	((14400 - $row["StartTime"]) / 3600) * $nightRateArr['LowNight'];
					}
					if(($row["EndTime"] <= 14400) && ($row["StartTime"] > 0) && ($row["StartTime"] < $row["EndTime"]))
					{
						$nightCostLow	=	(($row["EndTime"] - $row["StartTime"]) / 3600) * $nightRateArr['LowNight'];
					}
					$arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['NightCostLow'] = abs($nightCostLow);
					$arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['NightCostHigh'] = abs($nightCostHigh);
				}else
				{//logic for mid-night duty
					if($row["EndTime"] >= 21600)
					{
						$nightCostHigh	=	2 * $nightRateArr['HighNight'];
					}
					if(($row["EndTime"] < 21600) && ($row["EndTime"] >= 14400))
					{
						$nightCostHigh	=	(($row["EndTime"] - 14400) / 3600) * $nightRateArr['HighNight'];
					}
					if($row["EndTime"] >= 14400)
					{
						$nightCostLow	=	4 * $nightRateArr['LowNight'];
					}
					if($row["EndTime"] < 14400)
					{
						$nightCostLow	=	(($row["EndTime"]) / 3600) * $nightRateArr['LowNight'];
					}
					$arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['NightCostLow'] = abs($nightCostLow);
					$arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['NightCostHigh'] = abs($nightCostHigh);
				}
			}
          $intIsLNEMT = GetLNEMT($row['StartMinutes'], $row['EndMinutes']);
          $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']]['LNEMT'] = $intIsLNEMT;



          $arrMasterDuties['Weeks'][$intCurrWeek][$row['AllocMasterDutyID']][$iDay] = $row[$iDay];
        }
      }
    }
  }
  if (isset($arrMasterDuties)) {
    return ($arrMasterDuties);
  }

}

function ReadAllMasterDuties($intDepartmentID) {

  $pdo = OpenDBLinkA7();

  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
  }
  else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));
  }
$strSQL = "exec [dbo].[usp_GETAllMasterDuties] ?, ?";
  $stmt = $pdo->prepare($strSQL);
  $stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
  $stmt->bindParam(2, $intWeekNumber, PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($result as $row) {
    $intID = $row['AllocMasterDutyID'];
    $arrMasterDuties[$intID]['DutyName'] = trim($row['DutyName']);
    $arrMasterDuties[$intID]['DutyDesc'] = trim($row['TeamDesc']);
    $arrMasterDuties[$intID]['BaseName'] = trim($row['BaseName']);
    $arrMasterDuties[$intID]['StartWeek'] = $row['StartWeek'];
    $arrMasterDuties[$intID]['EndWeek'] = $row['EndWeek'];
    for ($iDay = 0; $iDay <=6; $iDay++) {
      $arrMasterDuties[$intID][$iDay] = $row[$iDay];
   }
   $startTimeHr	=	intval($row["StartTime"]/ 3600);
	$startTimeMin	=	intval(($row["StartTime"] % 3600) / 60);
	$endTimeHr		=	intval($row["EndTime"]/ 3600);
	$endTimeMin		=	intval(($row["EndTime"] % 3600) / 60);
	$startTimeHr    = 	(strlen($startTimeHr) == 2) ? $startTimeHr : '0'.$startTimeHr;
	$startTimeMin   = 	(strlen($startTimeMin) == 2) ? $startTimeMin : '0'.$startTimeMin;
	$endTimeHr	    = 	(strlen($endTimeHr) == 2) ? $endTimeHr : '0'.$endTimeHr;
	$endTimeMin	    = 	(strlen($endTimeMin) == 2) ? $endTimeMin : '0'.$endTimeMin;
   $arrMasterDuties[$intID]['StartTime'] = $startTimeHr.':'.$startTimeMin;
   $arrMasterDuties[$intID]['EndTime'] = $endTimeHr.':'.$endTimeMin;
   $arrMasterDuties[$intID]['IsHidden'] = $row['isHidden'];


  }
  if (isset($arrMasterDuties)) {
    return ($arrMasterDuties);
  }

}

function GetGlobalSick ($intStartWeek, $intEndWeek, $teamIds) {
  $pdo= OpenDBLinkA7();
  $loggedInUserTeamDetails = getTeamsAndShowReportFlag();
  $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $teamId = [];
  foreach($loggedInUserTeamDetails as $data) {
    $teamId[] = $data['schedulingTeamId'];
  }
    $strSQL = "SELECT AL_SchedulingTeamID DepartmentID, 
      AL_WeekNumber WeekNumber, 
      COUNT(1) AS CountSickDays, 
      SUM(ISNULL(ASP_LeaveDuration,0)) AS HourSum 
      FROM Allocations AL 
      INNER JOIN AllocationsScheduledPersons ASP ON AL_AllocationsID = ASP_AllocationsID 
      JOIN UserRoles UL ON UL.UR_SchedulingTeamID = AL_SchedulingTeamID AND UL.UR_SchedulingTeamID IN (".implode(',', $teamIds).") 
      JOIN REF_Roles RR ON UL.UR_RoleID = RR.RoleID 
      WHERE UL.UR_UserID = ?
      AND UL.UR_EndDate > GETDATE() 
      AND AL_WeekNumber >= ?
      AND AL_WeekNumber <= ?
      AND ASP_LeaveType IN (3,4,5) 
      GROUP BY AL_SchedulingTeamID, AL_WeekNumber 
      union
      SELECT AL_SchedulingTeamID DepartmentID, 
      AL_WeekNumber WeekNumber, 
      COUNT(1) AS CountSickDays, 
      SUM(ISNULL(ASP_LeaveDuration,0)) AS HourSum 
      FROM Allocations AL 
      inner join AllocationsAddPersons ap on AL.AL_AllocationsID = ap.AAP_AllocationsID
      INNER JOIN AllocationsScheduledPersons ASP ON  ap.AAP_AllocationsSPID = ASP.ASP_AllocationsSPID
      JOIN UserRoles UL ON UL.UR_SchedulingTeamID = AL_SchedulingTeamID AND UL.UR_SchedulingTeamID IN (".implode(',', $teamIds).")
      JOIN REF_Roles RR ON UL.UR_RoleID = RR.RoleID 
      WHERE UL.UR_UserID = ?
      AND UL.UR_EndDate > GETDATE() 
      AND AL_WeekNumber >= ?
      AND AL_WeekNumber <= ?
      AND ASP_LeaveType IN (3,4,5) 
      GROUP BY AL_SchedulingTeamID, AL_WeekNumber 
      ORDER BY AL_WeekNumber";
  $intTotalCount = 0;
  $intTotalHours = 0;
  $stmt = $pdo->prepare($strSQL);
  $stmt->bindParam(1,$sessUserId, PDO::PARAM_INT);
  $stmt->bindParam(2,$intStartWeek, PDO::PARAM_INT);
  $stmt->bindParam(3,$intEndWeek, PDO::PARAM_INT);
  $stmt->bindParam(4,$sessUserId, PDO::PARAM_INT);
  $stmt->bindParam(5,$intStartWeek, PDO::PARAM_INT);
  $stmt->bindParam(6,$intEndWeek, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($result as $row){
    $arrSick['Weeks'][$row['DepartmentID']][$row['WeekNumber']]['Count'] = $row['CountSickDays'];
    $arrSick['Weeks'][$row['DepartmentID']][$row['WeekNumber']]['Hours'] = $row['HourSum'];
    $intTotalCount = $intTotalCount + $row['CountSickDays'];
    $intTotalHours = $intTotalHours + $row['HourSum'];
    if (isset($arrSick['Totals']['Weeks'][$row['WeekNumber']]['Count'])) {
      $arrSick['Totals']['Weeks'][$row['WeekNumber']]['Count'] = $arrSick['Totals']['Weeks'][$row['WeekNumber']]['Count'] + $row['CountSickDays'];
    }
    else {
      $arrSick['Totals']['Weeks'][$row['WeekNumber']]['Count'] = $row['CountSickDays'];
    }
    if (isset($arrSick['Totals']['Weeks'][$row['WeekNumber']]['Hours'])) {
      $arrSick['Totals']['Weeks'][$row['WeekNumber']]['Hours'] = $arrSick['Totals']['Weeks'][$row['WeekNumber']]['Hours'] + $row['HourSum'];
    }
    else {
      $arrSick['Totals']['Weeks'][$row['WeekNumber']]['Hours'] = $row['HourSum'];
    }
  }

  $arrSick['Totals']['Count'] = $intTotalCount;
  $arrSick['Totals']['Hours'] = $intTotalHours;


if (isset($arrSick)) {
  return ($arrSick);

}
}

function ReadAllocationsCapacity($intWeekNumber)
{
  $pdo = OpenDBLinkA7();
  $strQuery = "SELECT U.ud_staffnumber AS StaffID,
                  U.UD_UserID as UserID,
                  U.ud_displayname AS FullName,
                  st.schedulingteamid AS StaffDepartmentID,
                  U.ud_staffnumber AS StaffNumber,
                  U.ud_netlogin AS NetLogin,
                  AL.al_weeknumber AS WeekNumber,
                  ASP.asp_iday AS iDay,
                  AL.al_allocationsid AS DutyID,
                  AL.al_schedulingteamid AS AllocationDepartmentID,
                  AD.ad_dutyname AS DutyName,
                  AD.ad_starttimesec StartTime,
                  AD.ad_endtimesec EndTime,
                  AD.ad_duration Duration,
                  ASP.asp_sortcode AS BaseCode,
                  st.schedulingteamname AS DepartmentName,
                  U.UD_displaylastname,
                  U.UD_DisplayFirstName
              FROM allocations AL WITH(nolock)
              INNER JOIN allocationsduties AD WITH(nolock) ON AL_AllocationsID = AD_AllocationsID
              INNER JOIN allocationsscheduledpersons ASP WITH(nolock) ON ASP_AllocationsDutyID = AD_AllocationsDutyID
              INNER JOIN userdetails U ON ud_userid = ASP.asp_schedulingpersonid
              INNER JOIN schedulingteams st ON st.schedulingteamid = ASP_DutyTeamID
			  INNER JOIN ScheduledPersonTeam_LINK SL on SL.ScheduledPersonID  = UD_UserID
			  INNER JOIN schedulingTeams STH on SL.TeamID = STH.schedulingTeamId
              WHERE AL.al_weeknumber = ?
                AND (AD.ad_dutyname LIKE '_8%'
                    OR AD.ad_dutyname LIKE '_ %')
                AND AD.ad_duration <> 0
				AND SL.scheduledType = 1
				AND SL.IsHomeTeam = 1
				AND ASP.ASP_DutyDate BETWEEN SL.StartDate and SL.EndDate
				AND STH.schedulingTeamName NOT IN ('Archive','TO Archive','Freelancers','Other BBC')
              ORDER BY U.UD_displaylastname,
                      U.UD_DisplayFirstName,
                      AL.al_weeknumber,
                      ASP.asp_iday";
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $intWeekNumber, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach($result as $row){
    $intStaffID = $row["StaffID"];
    $strCurrentUserID = $row["UserID"];
    $strCurrentWeek = $row["WeekNumber"];
    $strCurrentDay = $row["iDay"];
    $row["SortCode"] = $row["SortCode"] ?? '';
    $thisdate = datefromweek($strCurrentWeek, $strCurrentDay);
      $arrAllocations[$strCurrentUserID]["StaffNumber"] = $row["StaffNumber"];
      $arrAllocations[$strCurrentUserID]["Login"] = $row["NetLogin"];
      $arrAllocations[$strCurrentUserID]["DepartmentID"] = $row["StaffDepartmentID"];
      $arrAllocations[$strCurrentUserID]["FullName"] = $row["FullName"];
      $arrAllocations[$strCurrentUserID]["SortCode"] = $row["SortCode"];
      $strDutyname = $row["DutyName"];

      $duration = round($row["Duration"], 2, PHP_ROUND_HALF_UP);
      if ($duration == 0) {
        $isworking = 0;
      }
      else {
        if (isset($arrTextColours)) {
          $isworking = GetIsNotWorking($strDutyname, $arrTextColours);
        }
        else {
          $isworking = 1;
        }
      }
      $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["isworking"] = $isworking;
      $CellClass = SetDutyClass($thisdate, 999, 0, $isworking);
      $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["CellClass"] = $CellClass;
      $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["Dutyid"] = $row["DutyID"];
      $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["BaseCode"] = $row["BaseCode"];
      $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["DepartmentID"] = $row["AllocationDepartmentID"];
      $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["DepartmentName"] = $row["DepartmentName"];       
      $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["Duty"] = $strDutyname;
      if (!((is_null($row["StartTime"]) && is_null($row["EndTime"])) || ($row["StartTime"] === 0 && $row["EndTime"] === 0))) {
          $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["starttime"] = $row["StartTime"];
          $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["endtime"] = $row["EndTime"];
      }
      $arrAllocations[$strCurrentUserID][$strCurrentWeek][$strCurrentDay]["duration"] = $duration;
    }



    if (isset($arrAllocations)) {
     return ($arrAllocations);
    }
}

function getSicknessReport($teamId, $sDate, $eDate, $schedulePersonId = 'NULL')
{
  $pdo = OpenDBLinkA7();
  $squery = "exec usp_sickness_report ?, ?, ?, $schedulePersonId";
  $stmt = $pdo->prepare($squery);
  $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
  $stmt->bindParam(2, $sDate, PDO::PARAM_STR);
  $stmt->bindParam(3, $eDate, PDO::PARAM_STR);
  $stmt->execute();
  return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

function getSicknessByPerson($sDate, $eDate, $ScheduledPersonID, $teamId)
{
	$pdo = OpenDBLinkA7();
	$squery = "SELECT AD.AD_DutyDate AS DutyDate, 
		A.AL_WeekNumber AS WeekNumber, 
		AD.AD_iDay AS iDay, 
		CASE WHEN ASP_LeaveType = 3
				THEN 'Sick'
				WHEN ASP_LeaveType = 4
				THEN 'U-Sick'
				WHEN ASP_LeaveType = 5
				THEN '-Sick'
			END AS DutyName,
		ASP.ASP_LeaveDuration AS Duration 
   FROM Allocations AS A WITH(NOLOCK)
  INNER JOIN AllocationsScheduledPersons AS ASP WITH(NOLOCK)  ON A.AL_AllocationsID=ASP.ASP_AllocationsID 
  INNER JOIN AllocationsDuties AS AD WITH(NOLOCK)  ON ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID
  WHERE ASP.ASP_SchedulingPersonID = ?
	AND AD.AD_DutyType = 8
	AND ASP_LeaveType IN (3,4,5)
	AND ASP_DutyDate BETWEEN ? AND ? 
  ORDER BY DutyDate ASC"; 
	$stmt = $pdo->prepare($squery);
	$stmt->bindParam(1, $ScheduledPersonID, PDO::PARAM_INT);
	$stmt->bindParam(2, $sDate, PDO::PARAM_STR);
	$stmt->bindParam(3, $eDate, PDO::PARAM_STR);
	$stmt->execute();
	return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSicknessOccurrencesReport($teamId)
{
  $pdo = OpenDBLinkA7();
  $squery = "exec usp_Sickness_Occurences_Report ?, ?, ?";
  $stmt = $pdo->prepare($squery);
  $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
  $prev_year_date = date('Y-m-d', strtotime('-1 year', time()) );
  $today = date('Y-m-d');
  $stmt->bindParam(2, $prev_year_date , PDO::PARAM_STR);
  $stmt->bindParam(3, $today , PDO::PARAM_STR);
  $stmt->execute();
  return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

?>