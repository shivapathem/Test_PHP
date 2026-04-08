  <?php

function ReadAllocationsStaffing($intStartWeekNumber, $intEndWeekNumber, $arrDepartments, $arrStaffBreaks, $intDepartmentID = 0)
{
  try {
    $pdo = OpenDBLinkA7();
    $strQuery = "exec [dbo].[usp_ReadAllocationsStaffing] ?, ?";

    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intStartWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDepartmentID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
      $intStaffID = $row["StaffID"];
      $strCurrentStaffNumber = $row["StaffNumber"];
      $strCurrentWeek = $row["WeekNumber"];
      $strCurrentDay = $row["iDay"];
      $intCurrentDepartmentID = $row["AllocationDepartmentID"];
      if (isset($arrDepartments[$intCurrentDepartmentID])) {
        // Is the week number greater than the start of reports?
        if ($strCurrentWeek >= $arrDepartments[$intCurrentDepartmentID]['ReportsStart']) {
          $strDutyname = sanitiseduty($row["DutyName"]);
          $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]['Name'] = $row["FullName"];
          $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]['SortCode'] = $row["SortCode"];
          $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]['StaffTextColour'] = $row["StaffTextColour"];
          $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]['DepartmentName'] = $row["AllocationDepartmentName"];

          if (isset($arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Count'])) {
            $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Count'] = $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Count'] + 1;
          } else {
            $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Count'] = 1;
          }
          if (isset($arrAllocations['Duties'][$strDutyname]['Count'])) {
            if ($arrAllocations['Duties'][$strDutyname]['Count'] < $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Count']) {
              $arrAllocations['Duties'][$strDutyname]['Count'] = $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Count'];
            }
          } else {
            $arrAllocations['Duties'][$strDutyname]['Count'] = $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Count'];
          }


          if (!is_null($row["StartTime"])) {
            $starttime = gmdate("H:i", ($row["StartTime"] * 86400) + 1);
            $endtime = gmdate("H:i", ($row["EndTime"] * 86400) + 1);
            $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]["StartTime"] = $starttime;
            $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]["EndTime"] = $endtime;
          }
          $intDuration = round($row["Duration"], 2, PHP_ROUND_HALF_UP);
          //$intDuration = number_format((float)($intDuration) / 3600, 2, '.', '');
          $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]["Duration"] = number_format((float) ($intDuration) / 3600, 2, '.', '');

          $intMealBreak = $row['dutyBreakTime'] ?? 0;
          $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]["DurationLessMeal"] = number_format((float) ($intDuration - $intMealBreak) / 3600, 2, '.', '');
          if (isset($arrAllocations['Hours'][$strCurrentWeek][$strCurrentDay])) {
            $arrAllocations['Hours'][$strCurrentWeek][$strCurrentDay] = number_format((float) ($arrAllocations['Hours'][$strCurrentWeek][$strCurrentDay] + $intDuration - $intMealBreak) / 3600, 2, '.', '');
          } else {
            $arrAllocations['Hours'][$strCurrentWeek][$strCurrentDay] = number_format((float) ($intDuration - $intMealBreak) / 3600, 2, '.', '');
          }

          // Duty Comments?
          $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]["Dutycomments"] = $row["DutyComments"];
          $arrAllocations['Duties'][$strDutyname][$strCurrentWeek][$strCurrentDay]['Staff'][$strCurrentStaffNumber]["personcomments"] = $row["PersonComments"];
          // End Comments
        }
      }
    }
    if (isset($arrAllocations)) {
      return ($arrAllocations);
    }
  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
  }
}
// ######################################################### Start ReadAllocationsStaffingSummary
function ReadAllocationsStaffingSummary($intStartWeekNumber, $intEndWeekNumber, $arrDepartments, $arrStaffBreaks, $intDepartmentID = 0)
{
  try {
    $pdo = OpenDBLinkA7();
    $strQuery = "usp_getAllocationsStaffingSummary ?, ?, ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intStartWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(2, $intEndWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(3, $intDepartmentID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $intTotalHours = 0;
    foreach ($result as $row) {
      $strCurrentStaffNumber = $row["StaffNumber"];
      $strCurrentWeek = $row["WeekNumber"];
      $strCurrentDay = $row["iDay"];
      $intCurrentDepartmentID = $row["AllocationDepartmentID"];
      if (isset($arrDepartments[$intCurrentDepartmentID])) {
        // Is the week number greater than the start of reports?
        if ($strCurrentWeek >= $arrDepartments[$intCurrentDepartmentID]['ReportsStart']) {
          $intDuration = round($row["Duration"], 2, PHP_ROUND_HALF_UP);
          if ($intDuration == 0) {
            $intDuration = $arrDepartments[$intCurrentDepartmentID]['DefaultShiftLength'];
          }
          $intMealBreak = $row['dutyBreakTime'];
          if (isset($arrAllocations['Totals'][$strCurrentWeek])) {
            $arrAllocations['Totals'][$strCurrentWeek] = $arrAllocations['Totals'][$strCurrentWeek] + number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          } else {
            $arrAllocations['Totals'][$strCurrentWeek] = number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          }
          $intTotalHours = $intTotalHours + number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          if (isset($arrAllocations['Duties'][$strCurrentWeek][$strCurrentDay])) {
            $arrAllocations['Duties'][$strCurrentWeek][$strCurrentDay] = $arrAllocations['Duties'][$strCurrentWeek][$strCurrentDay] + number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          } else {
            $arrAllocations['Duties'][$strCurrentWeek][$strCurrentDay] = number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          }
        }
      }
    }

    $arrAllocations['TotalHours'] = $intTotalHours;

    if (isset($arrAllocations)) {
      return ($arrAllocations);
    }
  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
  }
}
// ################################################################### End ReadAllocationsStaffingSummary

function ReadAllocationsFreelancers($intStartWeekNumber = 0, $intEndWeekNumber = 0, $arrDepartments = '', $arrStaffBreaks = '', $intTeamID = 0, $userID = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        $strQuery = "exec [dbo].[usp_getAllocationsFreelancers] ?, ?, ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intStartWeekNumber, PDO::PARAM_INT);
        $stmt->bindParam(2, $intTeamID, PDO::PARAM_INT);
        $stmt->bindParam(3, $userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $arrAllocations = [];
        
        foreach ($result as $row) {
            $intStaffID = $row["StaffID"] ?? null;
            $strCurrentStaffNumber = $row["Staffnumber"] ?? '';
            $strCurrentschNumber = $row["ScheduledPersonID"] ?? '';
            $strCurrentWeek = $row["Weeknumber"] ?? '';
            $strCurrentDay = $row["Iday"] ?? '';
            $intCurrentDepartmentID = $row["AllocationDepartmentID"] ?? 0;
            $intDuration = $row["Duration"] ?? 0;
            if (is_array($arrDepartments) && isset($arrDepartments[$intCurrentDepartmentID])) {
                $arrAllocations['Duties'][$strCurrentschNumber]["StaffTextColour"] = $row["StaffTextColour"] ?? '';
                $arrAllocations['Duties'][$strCurrentschNumber]["FullName"] = $row["FullName"] ?? '';
                $arrAllocations['Duties'][$strCurrentschNumber]["ScheduledPersonID"] = $row["ScheduledPersonID"] ?? '';
                $arrAllocations['Duties'][$strCurrentschNumber]["StaffNumber"] = $strCurrentStaffNumber;
                $arrAllocations['Duties'][$strCurrentschNumber]["SortCode"] = $row["SortCode"] ?? '';
                $arrAllocations['Duties'][$strCurrentschNumber]["StaffColour"] = $row["StaffColour"] ?? '';
                
                $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["Duty"] = $row["Dutyname"] ?? '';
                $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["TeamName"] = $row["AllocationDepartmentName"] ?? '';
                
                if (isset($row["Starttime"]) && isset($row['Endtime']) && ($row["Starttime"] >= 0) && ($row['Endtime'] >= 0)) {
                    $intstartHour = intval($row["Starttime"] / 3600);
                    $intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
                    $intstartMinute = intval(($row["Starttime"] % 3600) / 60);
                    $intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
                    $starttime = $intstartHour . ":" . $intstartMinute;
                    
                    $intendHour = intval($row['Endtime'] / 3600);
                    $intendHour = strlen(trim($intendHour)) == 1 ? "0" . $intendHour : $intendHour;
                    $intendMinute = intval(($row['Endtime'] % 3600) / 60);
                    $intendMinute = strlen(trim($intendMinute)) == 1 ? "0" . $intendMinute : $intendMinute;
                    $endtime = $intendHour . ":" . $intendMinute;
                    
                    $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["StartTime"] = $starttime;
                    $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["EndTime"] = $endtime;
                } else {
                    $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["StartTime"] = "00:00";
                    $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["EndTime"] = "00:00";
                }
                
                $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["Duration"] = number_format((float) ($intDuration) / 3600, 2, '.', '');
                
                $intMealBreak = $row['Dutybreaktime'] ?? 0;
                $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["DurationLessMeal"] = number_format((float) ($intDuration) / 3600, 2, '.', '');
                
                if (isset($arrAllocations['Hours'][$strCurrentWeek][$strCurrentDay])) {
                    $arrAllocations['Appearance'][$strCurrentWeek][$strCurrentDay] = ($arrAllocations['Appearance'][$strCurrentWeek][$strCurrentDay] ?? 0) + 1;
                    $arrAllocations['Hours'][$strCurrentWeek][$strCurrentDay] += number_format((float) ($intDuration) / 3600, 2, '.', '');
                } else {
                    $arrAllocations['Hours'][$strCurrentWeek][$strCurrentDay] = number_format((float) ($intDuration) / 3600, 2, '.', '');
                    $arrAllocations['Appearance'][$strCurrentWeek][$strCurrentDay] = 1;
                }

                $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["DutyComments"] = $row["DutyComments"] ?? '';
                $arrAllocations['Duties'][$strCurrentschNumber][$strCurrentWeek][$strCurrentDay]["PersonComments"] = $row["PersonComments"] ?? '';
            }
        }

        return $arrAllocations ?? [];

    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        return [];
    }
}
// #################################################### End ReadAllocationsFreelancers

function ReadAllocationsFreelancersSummary($intStartWeekNumber, $intEndWeekNumber, $arrDepartments, $arrStaffBreaks, $intDepartmentID = 0)
{
  try {
    $strQuery = '';
    $pdo = OpenDBLinkA7();
    $strQuery .= "exec [dbo].[usp_getAllocationsFreelancersSummary] ?, ?, ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intStartWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(2, $intEndWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(3, $intDepartmentID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $intTotalHours = 0;
    foreach ($result as $row) {
      $strCurrentStaffNumber = $row["StaffNumber"];
      $strCurrentWeek = $row["WeekNumber"];
      $strCurrentDay = $row["iDay"];

      $intCurrentDepartmentID = $row["AllocationDepartmentID"];
      $intDuration = round($row["Duration"], 2, PHP_ROUND_HALF_UP);
      $intCurrentDepartmentID = $row["AllocationDepartmentID"];
      if (isset($arrDepartments[$intCurrentDepartmentID])) {
        if ($strCurrentWeek >= $arrDepartments[$intCurrentDepartmentID]['ReportsStart']) {

          $intMealBreak = $row['dutyBreakTime'];
          if (isset($arrAllocations['Totals'][$strCurrentWeek])) {
            $arrAllocations['Totals'][$strCurrentWeek] = $arrAllocations['Totals'][$strCurrentWeek] + number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          } else {
            $arrAllocations['Totals'][$strCurrentWeek] = number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          }
          $intTotalHours = $intTotalHours + number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          if (isset($arrAllocations['Duties'][$strCurrentWeek][$strCurrentDay])) {
            $arrAllocations['Duties'][$strCurrentWeek][$strCurrentDay] = $arrAllocations['Duties'][$strCurrentWeek][$strCurrentDay] + number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          } else {
            $arrAllocations['Duties'][$strCurrentWeek][$strCurrentDay] = number_format((float) (($intDuration - $intMealBreak) / 3600), 2, '.', '');
          }
        }
      }
    }
    $arrAllocations['TotalHours'] = $intTotalHours;
    if (isset($arrAllocations)) {
      return ($arrAllocations);
    }
  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
  }
}

function ReadAllocationsStaffingSimple($intStartWeekNumber, $intEndWeekNumber, $arrDepartments)
{
  $db = OpenDatabase();
  $arrTextColours = GetShiftTextColours();

  $strQuery = "





SELECT        Staff.ID AS StaffID, Staff.Forename + ' ' + Staff.Surname AS FullName, Staff.DepartmentID AS StaffDepartmentID, Staff.StaffNumber, Staff.SortCode, Staff.Login, 
                         Allocations.WeekNumber, Allocations.iDay, Allocations.ID AS DutyID, Allocations.DepartmentID AS AllocationDepartmentID, 
                         Departments.FullName AS AllocationDepartmentName, CASE WHEN Allocations_webedit.ID IS NULL THEN 0 ELSE 1 END AS iscopy, 
                         CASE WHEN Allocations_webedit.ID IS NULL THEN 0 ELSE Allocations_webedit.edited END AS isedited, CASE WHEN Allocations_webedit.ID IS NULL 
                         THEN Allocations.DutyName ELSE Allocations_webedit.DutyName END AS DutyName, CASE WHEN Allocations_webedit.ID IS NULL 
                         THEN Allocations.StartTime ELSE Allocations_webedit.StartTime END AS StartTime, CASE WHEN Allocations_webedit.ID IS NULL 
                         THEN Allocations.EndTime ELSE Allocations_webedit.EndTime END AS EndTime, CASE WHEN Allocations_webedit.ID IS NULL 
                         THEN Allocations.Duration ELSE Allocations_webedit.Duration END AS Duration, CASE WHEN Allocations_webedit.ID IS NULL 
                         THEN CAST(Allocations.BackColour AS nvarchar) ELSE CAST(Allocations_webedit.BackColour AS nvarchar) END AS AllocBackColour, 
                         CASE WHEN Allocations_webedit.ID IS NULL THEN CAST(Allocations.FontColour AS nvarchar) ELSE CAST(Allocations_webedit.FontColour AS nvarchar) 
                         END AS AllocFontColour, CASE WHEN Allocations_webedit.ID IS NULL THEN CASE WHEN Allocations.DutyComments IS NULL 
                         THEN 0 ELSE DataLength(Allocations.DutyComments) END ELSE CASE WHEN Allocations_webedit.DutyComments IS NULL 
                         THEN 0 ELSE DataLength(Allocations_webedit.DutyComments) END END AS DutyComments, CASE WHEN Allocations_webedit.ID IS NULL 
                         THEN CASE WHEN Allocations.PersonComments IS NULL THEN 0 ELSE DataLength(Allocations.PersonComments) 
                         END ELSE CASE WHEN Allocations_webedit.PersonComments IS NULL THEN 0 ELSE DataLength(Allocations_webedit.PersonComments) 
                         END END AS PersonComments, Staff_Web_Config_Departments_Link.TextColour as StaffTextColour 
FROM            Staff INNER JOIN
                         Allocations ON Staff.StaffNumber = Allocations.StaffNumber AND Staff.DepartmentID = Allocations.DepartmentID INNER JOIN
                         Departments ON Allocations.DepartmentID = Departments.ID LEFT OUTER JOIN
                         Staff_Web_Config_Departments_Link ON Staff.DepartmentID = Staff_Web_Config_Departments_Link.DepartmentID AND 
                         Staff.Login = Staff_Web_Config_Departments_Link.Login LEFT OUTER JOIN
                         Allocations_webedit ON Allocations.DepartmentID = Allocations_webedit.DepartmentID AND Allocations.StaffNumber = Allocations_webedit.StaffNumber AND 
                         Allocations.WeekNumber = Allocations_webedit.WeekNumber AND Allocations.iDay = Allocations_webedit.iDay
WHERE        (Allocations.WeekNumber >= N'$intStartWeekNumber') AND (Allocations.WeekNumber <= '$intEndWeekNumber') AND (Allocations.DutyName LIKE N'[a-zA-z][ ]%') AND (Allocations.Duration <> 0)
ORDER BY Staff.Surname, Staff.Forename, Allocations.WeekNumber, Allocations.iDay";
  $allocations = sqlsrv_query($db, $strQuery);

  while ($row = sqlsrv_fetch_array($allocations)) {
    $intStaffID = $row["StaffID"];
    $strCurrentStaffNumber = $row["StaffNumber"];
    $strCurrentWeek = $row["WeekNumber"];
    $strCurrentDay = $row["iDay"];
    $intCurrentDepartmentID = $row["AllocationDepartmentID"];
    if (isset($arrDepartments[$intCurrentDepartmentID])) {
      $arrAllocations[$strCurrentStaffNumber]["StaffNumber"] = $strCurrentStaffNumber;
      $arrAllocations[$strCurrentStaffNumber]["Login"] = $row["Login"];
      $arrAllocations[$strCurrentStaffNumber]["FullName"] = $row["FullName"];
      $arrAllocations[$strCurrentStaffNumber]["SortCode"] = $row["SortCode"];
      $arrAllocations[$strCurrentStaffNumber]["StaffTextColour"] = $row["StaffTextColour"];

      $thisdate = datefromweek($strCurrentWeek, $strCurrentDay);
      $strDutyname = $row["DutyName"];

      $intDuration = round($row["Duration"], 2, PHP_ROUND_HALF_UP);
      if ($intDuration == 0) {
        $isworking = 0;
      } else {
        if (isset($arrTextColours)) {
          $isworking = GetIsNotWorking($strDutyname, $arrTextColours);
        } else {
          $isworking = 1;
        }
      }

      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["DepartmentName"] = $row["AllocationDepartmentName"];
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["copy"] = $row["iscopy"];
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["edited"] = $row["isedited"];
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["isworking"] = $isworking;
      $CellClass = SetDutyClass($thisdate, 999, 0, $isworking);
      $TextColour = getTextColour($strDutyname, $arrTextColours);
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["CellClass"] = $CellClass;
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["TextColour"] = $TextColour;
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["Dutyid"] = $row["DutyID"];
      if (is_null($row["AllocBackColour"]) || $row["AllocBackColour"] == '') {
        $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["backcolour"] = '#000080';
      } else {
        if (is_numeric($row["AllocBackColour"])) {
          $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["backcolour"] = intotohex($row["AllocBackColour"]);
        } else {
          $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["backcolour"] = $row["AllocBackColour"];
        }
      }

      if (is_null($row["AllocFontColour"]) || $row["AllocFontColour"] == '') {
        $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["allocateTextColour"] = getContrastColor($arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["backcolour"]);
      } else {
        if (is_numeric($row["AllocFontColour"])) {
          $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["allocateTextColour"] = intotohex($row["AllocFontColour"]);
        } else {
          $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["allocateTextColour"] = $row["AllocFontColour"];
        }
      }
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["Duty"] = $strDutyname;
      if (!is_null($row["StartTime"])) {
        $starttime = gmdate("H:i", ($row["StartTime"] * 86400) + 1);
        $endtime = gmdate("H:i", ($row["EndTime"] * 86400) + 1);
        $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["starttime"] = $starttime;
        $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["endtime"] = $endtime;
      }
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["duration"] = $intDuration;
      // Duty Comments?
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["Dutycomments"] = $row["DutyComments"];
      $arrAllocations[$strCurrentStaffNumber][$strCurrentWeek][$strCurrentDay]["personcomments"] = $row["PersonComments"];
      // End Comments
    }
  }
  if (isset($arrAllocations)) {
    return ($arrAllocations);
  }
}

function ReadAllocationsUncovered($intStartWeekNumber, $intEndWeekNumber, $arrDepartments)
{
  $db = OpenDatabase();

  $strQuery = "SELECT           Allocations.WeekNumber, Allocations.iDay, 
                                CASE WHEN Allocations_webedit.ID IS NULL 
                                THEN Allocations.DutyName ELSE Allocations_webedit.DutyName END AS DutyName, CASE WHEN Allocations_webedit.ID IS NULL 
                                THEN Allocations.StartTime ELSE Allocations_webedit.StartTime END AS StartTime, 
                                CASE WHEN Allocations_webedit.ID IS NULL 
                                THEN CASE WHEN isnull(Allocations.DutyComments, '') = '' THEN 0 ELSE 1 END ELSE CASE WHEN isnull(Allocations_webedit.DutyComments, '') = '' THEN 0 ELSE 1 END END AS DutyComments, 
                                Allocations.DepartmentID, Departments.FullName as DepartmentName
               FROM             Allocations 
               INNER JOIN       Departments ON Allocations.DepartmentID = Departments.ID 
               LEFT OUTER JOIN  Allocations_webedit ON Allocations.DepartmentID = Allocations_webedit.DepartmentID 
                    AND         Allocations.StaffNumber = Allocations_webedit.StaffNumber 
                    AND         Allocations.WeekNumber = Allocations_webedit.WeekNumber AND Allocations.iDay = Allocations_webedit.iDay
               WHERE           (Allocations.WeekNumber >= N'$intStartWeekNumber') 
               AND             (Allocations.WeekNumber <= '$intEndWeekNumber') 
               AND             (Allocations.StaffNumber IS NULL)
               ORDER BY        CASE WHEN Allocations.StartTime IS NULL THEN 1 ELSE 0 END, DutyName, Allocations.iDay";
  $allocations = sqlsrv_query($db, $strQuery);

  while ($row = sqlsrv_fetch_array($allocations)) {
    $intCurrentDepartmentID = $row['DepartmentID'];
    if (isset($arrDepartments[$intCurrentDepartmentID])) {
      $strDutyName = $row['DutyName'] . '<br>' . $row['DepartmentName'];
      if (isset($arrAllocations[$strDutyName][$row['WeekNumber']][$row['iDay']]['Count'])) {
        $arrAllocations[$strDutyName][$row['WeekNumber']][$row['iDay']]['Count'] = $arrAllocations[$strDutyName][$row['WeekNumber']][$row['iDay']]['Count'] + 1;
      } else {
        $arrAllocations[$strDutyName][$row['WeekNumber']][$row['iDay']]['Count'] = 1;
      }
      $arrAllocations[$strDutyName][$row['WeekNumber']][$row['iDay']]['Department'] = $row['DepartmentName'];
      $arrAllocations[$strDutyName][$row['WeekNumber']][$row['iDay']]['Duty'] = $row['DutyName'];
    }
  }
  if (isset($arrAllocations)) {
    return ($arrAllocations);
  }

}

function ReadAllocationsFreelancersArea($intStartWeekNumber = '', $intEndWeekNumber = '', $arrDepartments = '', $arrStaffBreaks = '', $intTeamID = 0, $userID = 0, $intAreaID = 0, $intStartDate = '', $intEndDate = '', $ScheduledPersonID = 0)
{
  try {
    $pdo = OpenDBLinkA7();
    $strQuery = "exec [dbo].[usp_getAllocationsFreelancers] ?, ?, ?, ?, ?, ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intStartWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(2, $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(3, $userID, PDO::PARAM_INT);
    $stmt->bindParam(4, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(5, $intStartDate, PDO::PARAM_STR);
    $stmt->bindParam(6, $intEndDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($ScheduledPersonID)) {
      $result = array_filter($result, function($row) use ($ScheduledPersonID) {
        return $row['ScheduledPersonID'] == $ScheduledPersonID;
      });
    }
    foreach ($result as $row) {
      $intStaffID = $row["StaffID"] ?? 0;
      $strCurrentStaffNumber = $row["Staffnumber"];
      $strCurrentschNumber = $row["ScheduledPersonID"];
      $strCurrentWeek = $row["Weeknumber"];
      $strDutyDate = $row["DutyDate"];
      $strCurrentDay = $row["Iday"];
      $intCurrentDepartmentID = $row["AllocationDepartmentID"];
      $intDuration = $row["Duration"];
      $intCurrentDepartmentID = $row["AllocationDepartmentID"];
      if (isset($arrDepartments[$intCurrentDepartmentID])) {
        $arrAllocations['Duties'][$strCurrentschNumber]["StaffTextColour"] = $row["StaffTextColour"];
        $arrAllocations['Duties'][$strCurrentschNumber]["FullName"] = $row["FullName"];
        $arrAllocations['Duties'][$strCurrentschNumber]["ScheduledPersonID"] = $row["ScheduledPersonID"];
        $arrAllocations['Duties'][$strCurrentschNumber]["StaffNumber"] = $strCurrentStaffNumber;
        $arrAllocations['Duties'][$strCurrentschNumber]["SortCode"] = $row["SortCode"] ?? '';
        $arrAllocations['Duties'][$strCurrentschNumber]["StaffColour"] = $row["StaffColour"];
        $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["Duty"] = $row["Dutyname"];
        $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["TeamName"] = $row["AllocationDepartmentName"];
        $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
        $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["DivisionName"] = $row["DivisionName"];
        if (($row["Starttime"] >= 0) && ($row['Endtime'] >= 0)) {
          $intstartHour = intval($row["Starttime"] / 3600);
          $intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
          $intstartMinute = intval(($row["Starttime"] % 3600) / 60);
          $intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
          $starttime = $intstartHour . ":" . $intstartMinute;
          $intendHour = intval($row['Endtime'] / 3600);
          $intendHour = strlen(trim($intendHour)) == 1 ? "0" . $intendHour : $intendHour;
          $intendMinute = intval(($row['Endtime'] % 3600) / 60);
          $intendMinute = strlen(trim($intendMinute)) == 1 ? "0" . $intendMinute : $intendMinute;
          $endtime = $intendHour . ":" . $intendMinute;
          $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["StartTime"] = $starttime;
          $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["EndTime"] = $endtime;
        } else {
          $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["StartTime"] = "00:00";
          $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["EndTime"] = "00:00";
        }
        $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["Duration"] = number_format((float) ($intDuration) / 3600, 2, '.', '');
        $intMealBreak = $row['Dutybreaktime'];
        $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["DurationLessMeal"] = number_format((float) ($intDuration) / 3600, 2, '.', '');
        if ($row["IsAreaUnderUser"] == 1) {
          if (isset($arrAllocations['Hours'][$strDutyDate][0])) {
            $arrAllocations['Appearance'][$strDutyDate][0] = $arrAllocations['Appearance'][$strDutyDate][0] + 1;
            $arrAllocations['Appearance'][$strDutyDate]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
            $arrAllocations['Hours'][$strDutyDate][0] = $arrAllocations['Hours'][$strDutyDate][0] + number_format((float) ($intDuration) / 3600, 2, '.', '');
            $arrAllocations['Hours'][$strDutyDate]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
          } else {
            $arrAllocations['Hours'][$strDutyDate][0] = number_format((float) ($intDuration) / 3600, 2, '.', '');
            $arrAllocations['Hours'][$strDutyDate]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
            $arrAllocations['Appearance'][$strDutyDate][0] = 1;
            $arrAllocations['Appearance'][$strDutyDate]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
          }
        }

      $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["DutyComments"] = $row["DutyComments"] ?? '';
        $arrAllocations['Duties'][$strCurrentschNumber][$strDutyDate][0]["PersonComments"] = ($row["PersonComments"]);
      }
    }

    if (isset($arrAllocations)) {
      return ($arrAllocations);
    }
  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
  }
}

function ReadAllocationsFreelancersAreaSearch($intStartWeekNumber = '', $intEndWeekNumber = '', $arrDepartments = '', $arrStaffBreaks = '', $intTeamID = 0, $userID = '', $intAreaID = '', $intStartDate = '', $intEndDate = '')
{
  try {
    $pdo = OpenDBLinkA7();
    $strQuery = "exec [dbo].[usp_getAllocationsFreelancers] ?, ?, ?, ?, ?, ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intStartWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(2, $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(3, $userID, PDO::PARAM_INT);
    $stmt->bindParam(4, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(5, $intStartDate, PDO::PARAM_STR);
    $stmt->bindParam(6, $intEndDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
      $strCurrentschNumber = $row["ScheduledPersonID"];
      $intCurrentDepartmentID = $row["AllocationDepartmentID"];
      if (isset($arrDepartments[$intCurrentDepartmentID])) {
        $arrAllocations['Duties'][$strCurrentschNumber]["FullName"] = $row["FullName"];
        $arrAllocations['Duties'][$strCurrentschNumber]["ScheduledPersonID"] = $row["ScheduledPersonID"];
      }
    }
    if (isset($arrAllocations)) {
      return ($arrAllocations);
    }
  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
  }
}

function getDatesBetween($startDate, $endDate) {
  $dates = array();
  $currentDate = new DateTime($startDate);
  $endDateTime = new DateTime($endDate);

  while ($currentDate <= $endDateTime) {
      $dates[] = $currentDate->format('Y-m-d');
      $currentDate->modify('+1 day');
  }

  return $dates;
}
