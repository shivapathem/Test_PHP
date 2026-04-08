<?php
// This function returns all the allocations for the day
function ReadAllocationsDay($week, $day, $DepartmentID, $arrFilterText, $arrDutyFilterText, $intSortOrder, $dutyend, $rowsallowed = 0) {
  $earlieststart = 86400;
  $lateststart = 0;

  $thisdate = datefromweek($week, $day);
  $db = OpenDatabase();

  $arrFilterText = array_flip($arrFilterText);

  // ================================================================================ The Allocated Duties ================================================================================

  // Select allocations based on the Programme field matching the search term
  // Restore this query when the data is updated


  $query = "SELECT
        Staff.ID                             AS StaffID ,
        Staff.Forename + ' ' + Staff.Surname AS FullName,
        Staff.StaffNumber                               ,
        Staff.dutycolour                                ,
        Staff.RadioBase                                 ,
        Staff.SortCode                                  ,
        signin.active                                   ,
        signin.inBuilding                               ,
        signin.starttime                                                                                                                                                                                                                                                                      AS SignInStartTime             ,
        signin.endtime                                                                                                                                                                                                                                                                        AS SignInEndTime               ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.id ELSE allocations_webedit.id END                                                                                                                                                                                          AS DutyID                      ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE 1 END                                                                                                                                                                                                                            AS iscopy                      ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE allocations_webedit.edited END                                                                                                                                                                                                   AS isedited                    ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.dutyname ELSE allocations_webedit.Dutyname END                                                                                                                                                                              AS DutyName                    ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.starttime ELSE allocations_webedit.starttime END                                                                                                                                                                            AS StartTime                   ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.EndTime ELSE allocations_webedit.EndTime END                                                                                                                                                                                AS EndTime                     ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.Duration ELSE allocations_webedit.Duration END                                                                                                                                                                              AS Duration                    ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE allocations_webedit.InternalEdited END                                                                                                                                                                                           AS InternalEdited              ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE allocations_webedit.editable END                                                                                                                                                                                                 AS editable                    ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE allocations_webedit.Edited END                                                                                                                                                                                                   AS Edited                      ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(allocations.BackColour AS                                                           nvarchar) ELSE CAST(allocations_webedit.BackColour AS nvarchar) END                                                                            AS AllocBackColour             ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(allocations.FontColour AS                                                           nvarchar) ELSE CAST(allocations_webedit.FontColour AS nvarchar) END                                                                            AS AllocFontColour             ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CASE WHEN Allocations.DutyComments IS NULL THEN '' ELSE CAST(Allocations.DutyComments AS nvarchar) END ELSE CASE WHEN Allocations_webedit.DutyComments IS NULL THEN '' ELSE CAST (Allocations_webedit.DutyComments AS nvarchar) END END AS DutyComments                ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CASE WHEN Allocations.PersonComments IS NULL THEN 0 ELSE DATALENGTH(Allocations.PersonComments) END ELSE CASE WHEN Allocations_webedit.PersonComments IS NULL THEN 0 ELSE DATALENGTH(Allocations_webedit.PersonComments) END END        AS PersonComments              ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.ID ELSE jobs_webedit.ID END                                                                                                                                                                                                        AS JobID                       ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.StartTime ELSE jobs_webedit.StartTime END                                                                                                                                                                                          AS JobStartTime                ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.EndTime ELSE jobs_webedit.EndTime END                                                                                                                                                                                              AS JobEndTime                  ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.JobName ELSE jobs_webedit.JobName END                                                                                                                                                                                              AS JobName                     ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.Programme ELSE jobs_webedit.Programme END                                                                                                                                                                                          AS Programme                   ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE Jobs_webedit.Edited END                                                                                                                                                                                                          AS JobEdited                   ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(Jobs.JobBackColour AS nvarchar) ELSE CAST(Jobs_webedit.JobBackColour AS nvarchar) END                                                                                                                                              AS JobBackColour               ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(Jobs.JobFontColour AS nvarchar) ELSE CAST(Jobs_webedit.JobFontColour AS nvarchar) END                                                                                                                                              AS JobFontColour               ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(jobs.Comments AS      nvarchar(4000)) ELSE CAST(jobs_webedit.Comments AS nvarchar(4000)) END                                                                                                                                       AS JobComments                 ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CASE WHEN prog_bases.base IS NULL THEN staff.Radiobase ELSE prog_bases.base END ELSE jobs_webedit.Base END                                                                                                                              AS JobBase                     ,
        Staff.DepartmentID
FROM
        signin
RIGHT OUTER JOIN
        prog_bases
RIGHT OUTER JOIN
        Jobs
ON
        prog_bases.firstletter        = Jobs.Programme
AND     prog_bases.AllocateInstanceID = Jobs.AllocateInstanceID
AND     prog_bases.AllocateInstanceID = ". getCurrentInstanceId() ."
RIGHT OUTER JOIN
        Staff
INNER JOIN
        Allocations
ON
        Staff.StaffNumber              = Allocations.StaffNumber
AND     Allocations.AllocateInstanceID = Staff.AllocateInstanceID
AND     Allocations.AllocateInstanceID = ". getCurrentInstanceId() ."
ON
        Jobs.StaffNumber               = Allocations.StaffNumber
AND     Jobs.WeekNumber                = Allocations.WeekNumber
AND     Allocations.AllocateInstanceID = Jobs.AllocateInstanceID
AND     Jobs.AllocateInstanceID = ". getCurrentInstanceId() ."
AND     Jobs.iDay                      = Allocations.iDay
ON
        signin.staffnumber            = Allocations.StaffNumber
AND     Allocations.AllocateInstanceID=signin.AllocateInstanceID
AND     signin.AllocateInstanceID     = ". getCurrentInstanceId() ."
AND     signin.iWeek                  = Allocations.WeekNumber
AND     signin.iDay                   = Allocations.iDay
LEFT OUTER JOIN
        Jobs_webedit
RIGHT OUTER JOIN
        Allocations_webedit
ON
        Jobs_webedit.StaffNumber               = Allocations_webedit.StaffNumber
AND     Jobs_webedit.WeekNumber                = Allocations_webedit.WeekNumber
AND     Jobs_webedit.iDay                      = Allocations_webedit.iDay
AND     Jobs_webedit.AllocateInstanceID        =Allocations_webedit.AllocateInstanceID
AND     Jobs_webedit.AllocateInstanceID = ". getCurrentInstanceId() ."
ON
        Allocations.StaffNumber = Allocations_webedit.StaffNumber
AND     Allocations.WeekNumber  = Allocations_webedit.WeekNumber
AND     Allocations.iDay        = Allocations_webedit.iDay
AND     Allocations.AllocateInstanceID        =Allocations_webedit.AllocateInstanceID
WHERE
        (
                NOT (CASE WHEN Allocations_webedit.id IS NULL THEN allocations.starttime ELSE allocations_webedit.starttime END IS NULL))
AND     (
                Allocations.WeekNumber = N'$week')
AND     (
                Allocations.iDay = $day)
AND     (
                Staff.DepartmentID              = $DepartmentID)
AND     (CASE WHEN Allocations_webedit.id IS NULL THEN allocations.endtime ELSE allocations_webedit.endtime END > $dutyend)
GROUP BY
        Staff.ID                                                                                                                                                                                                                                                                             ,
        Staff.Forename + ' ' + Staff.Surname                                                                                                                                                                                                                                                 ,
        Staff.StaffNumber                                                                                                                                                                                                                                                                    ,
        Staff.dutycolour                                                                                                                                                                                                                                                                     ,
        Staff.RadioBase                                                                                                                                                                                                                                                                      ,
        Staff.SortCode                                                                                                                                                                                                                                                                       ,
        signin.starttime                                                                                                                                                                                                                                                                     ,
        signin.endtime                                                                                                                                                                                                                                                                       ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE allocations_webedit.edited END                                                                                                                                                                                                  ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.dutyname ELSE allocations_webedit.Dutyname END                                                                                                                                                                             ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.starttime ELSE allocations_webedit.starttime END                                                                                                                                                                           ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.EndTime ELSE allocations_webedit.EndTime END                                                                                                                                                                               ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.Duration ELSE allocations_webedit.Duration END                                                                                                                                                                             ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(allocations.BackColour AS                                                           nvarchar) ELSE CAST(allocations_webedit.BackColour AS nvarchar) END                                                                           ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(allocations.FontColour AS                                                           nvarchar) ELSE CAST(allocations_webedit.FontColour AS nvarchar) END                                                                           ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CASE WHEN Allocations.DutyComments IS NULL THEN '' ELSE CAST(Allocations.DutyComments AS nvarchar) END ELSE CASE WHEN Allocations_webedit.DutyComments IS NULL THEN '' ELSE CAST (Allocations_webedit.DutyComments AS nvarchar) END END,
        CASE WHEN Allocations_webedit.id IS NULL THEN CASE WHEN Allocations.PersonComments IS NULL THEN 0 ELSE DATALENGTH(Allocations.PersonComments) END ELSE CASE WHEN Allocations_webedit.PersonComments IS NULL THEN 0 ELSE DATALENGTH(Allocations_webedit.PersonComments) END END       ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.ID ELSE jobs_webedit.ID END                                                                                                                                                                                                       ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.StartTime ELSE jobs_webedit.StartTime END                                                                                                                                                                                         ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.EndTime ELSE jobs_webedit.EndTime END                                                                                                                                                                                             ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.JobName ELSE jobs_webedit.JobName END                                                                                                                                                                                             ,
        CASE WHEN Allocations_webedit.id IS NULL THEN jobs.Programme ELSE jobs_webedit.Programme END                                                                                                                                                                                         ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE Jobs_webedit.Edited END                                                                                                                                                                                                         ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(Jobs.JobBackColour AS nvarchar) ELSE CAST(Jobs_webedit.JobBackColour AS nvarchar) END                                                                                                                                             ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(Jobs.JobFontColour AS nvarchar) ELSE CAST(Jobs_webedit.JobFontColour AS nvarchar) END                                                                                                                                             ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CAST(jobs.Comments AS      nvarchar(4000)) ELSE CAST(jobs_webedit.Comments AS nvarchar(4000)) END                                                                                                                                      ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE allocations_webedit.InternalEdited END                                                                                                                                                                                          ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE allocations_webedit.editable END                                                                                                                                                                                                ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE allocations_webedit.Edited END                                                                                                                                                                                                  ,
        CASE WHEN Allocations_webedit.id IS NULL THEN CASE WHEN prog_bases.base IS NULL THEN staff.Radiobase ELSE prog_bases.base END ELSE jobs_webedit.Base END                                                                                                                             ,
        signin.active                                                                                                                                                                                                                                                                        ,
        signin.inBuilding                                                                                                                                                                                                                                                                    ,
        CASE WHEN Allocations_webedit.id IS NULL THEN 0 ELSE 1 END                                                                                                                                                                                                                           ,
        CASE WHEN Allocations_webedit.id IS NULL THEN allocations.id ELSE allocations_webedit.id END                                                                                                                                                                                         ,
        Staff.DepartmentID";
            if ($intSortOrder == 0) {
              $query.= " ORDER BY  StartTime, DutyName";
            }
            else {
              $query.= " ORDER BY  DutyName, StartTime";
            }
 $allocations = sqlsrv_query($db, $query);


  while($row = sqlsrv_fetch_array($allocations)){
    if (!is_null($row["StartTime"])) {
      $dutyid = $row["DutyID"];
      $arrallocations['assigned'][$dutyid]["staffnumber"] = $row["StaffNumber"];
      $arrallocations['assigned'][$dutyid]["fullname"] = $row["FullName"];
      $arrallocations['assigned'][$dutyid]["sortcode"] = $row["SortCode"];
      $arrallocations['assigned'][$dutyid]["dutycolour"] = $row["dutycolour"];
      $arrallocations['assigned'][$dutyid]["base"] = $row["RadioBase"];
      $arrallocations['assigned'][$dutyid]["iscopy"] = $row["iscopy"];
      $arrallocations['assigned'][$dutyid]["edited"] = $row["Edited"];
      $arrallocations['assigned'][$dutyid]["editable"] = $row["editable"];
      $starttime = doubletoseconds($row["StartTime"]);
      $endtime = doubletoseconds($row["EndTime"]);
      if ($earlieststart > $starttime) {
        $earlieststart = $starttime;
      }
      if ($lateststart < $endtime) {
        $lateststart = $endtime;
      }

      $arrallocations['assigned'][$dutyid]["starttime"] = $starttime;
      $arrallocations['assigned'][$dutyid]["endtime"] = $endtime;

      if (is_null($row["inBuilding"])) {
        $arrallocations['assigned'][$dutyid]["inbuilding"] = 0;
      }
      else {
        $arrallocations['assigned'][$dutyid]["inbuilding"] = $row["inBuilding"];
      }
      if (is_null($row["active"])) {
        $arrallocations['assigned'][$dutyid]["signin"] = 0;
      }
      else {
        if (gmdate("H:i", $starttime) == $row["SignInStartTime"] && gmdate("H:i", $endtime) == $row["SignInEndTime"]) {
          $arrallocations['assigned'][$dutyid]["signin"] = $row["active"];
        }
        else {
          $arrallocations['assigned'][$dutyid]["signin"] = 2;
        }
      }
      $arrallocations['assigned'][$dutyid]["duty"] = $row["DutyName"];
      $arrallocations['assigned'][$dutyid]["duration"] = $row["Duration"];

      if (is_null( $row["AllocBackColour"]) ||  $row["AllocBackColour"] == '') {
        $arrallocations['assigned'][$dutyid]["backcolour"] = '#000080';
      }
      else {
        if (is_numeric($row["AllocBackColour"])) {
          $arrallocations['assigned'][$dutyid]["backcolour"] = intotohex($row["AllocBackColour"]);
        }
        else {
         $arrallocations['assigned'][$dutyid]["backcolour"] = $row["AllocBackColour"];
        }
      }
      if (is_null( $row["AllocFontColour"]) || $row["AllocFontColour"] == '') {
        $arrallocations['assigned'][$dutyid]["fontcolour"] = '#ffffff';
      }
      else {
        if (is_numeric($row["AllocFontColour"])) {
          $arrallocations['assigned'][$dutyid]["fontcolour"] = intotohex($row["AllocFontColour"]);
        }
        else {
         $arrallocations['assigned'][$dutyid]["fontcolour"] = $row["AllocFontColour"];
        }
      }
      // Set the flag to Internally edited if the duty starts 1 day from now
      if ($row["InternalEdited"] == 1 && ($starttime + strtotime($thisdate) < strtotime("+ 1 Day"))) {
        $arrallocations['assigned'][$dutyid]["internaledited"] = 1;
      }
      else {
        $arrallocations['assigned'][$dutyid]["internaledited"] = 0;
      }
      // And the Job info
      if (!is_null($row["JobID"])) {
        $jobstarttime = doubletoseconds($row["JobStartTime"]);
        $jobendtime = doubletoseconds($row["JobEndTime"]);
        if ($jobendtime < $jobstarttime) {
          $jobendtime = $jobendtime + 86400;
        }
        $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["jobid"] = $row["JobID"];
        //$arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["allocatejobid"] = $row["AllocateJobID"];
        $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["jobname"] = $row["JobName"];
        $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["starttime"] = $jobstarttime;
        $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["endtime"] = $jobendtime;
        $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["edited"] = $row["JobEdited"];
        $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["programme"] = $row["Programme"];
        if (is_null( $row["JobBackColour"])) {
          $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["backcolour"] = '#ddffdd';
        }
        else {
          if (is_numeric($row["JobBackColour"])) {
             $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["backcolour"] = intotohex($row["JobBackColour"]);
          }
          else {
            $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["backcolour"] = $row["JobBackColour"];
          }
        }
        if (is_null( $row["JobFontColour"])) {
          $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["fontcolour"] = '#000000';
        }
        else {
          if (is_numeric($row["JobFontColour"])) {
            $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["fontcolour"] = intotohex($row["JobFontColour"]);
          }
          else {
            $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["fontcolour"] = $row["JobFontColour"];
          }
        }
        if (is_null($row["JobComments"])) {
          $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["comments"] = '';
        }
        else {
          $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["comments"] = htmlspecialchars($row["JobComments"]);
        }
        $arrallocations['assigned'][$dutyid]['jobs'][$row["JobID"]]["base"] = $row["JobBase"];
      }
      if ($rowsallowed != 0 && count($arrallocations['assigned']) >= $rowsallowed) {
        break;
      }
    }
  }
  if (isset($arrallocations)) {
    foreach ($arrallocations['assigned'] as $intDutyID => $arrDuty) {
      $intShowDuty = 0;
      if (!empty($arrDutyFilterText)) {
        $strDutyName = $arrDuty['duty'];
        foreach ($arrDutyFilterText as $intFilterID => $strFilter) {
          if (strtoupper(substr($strDutyName, 0, strlen($strFilter))) == strtoupper($strFilter)) {
            $intShowDuty = 1;
          }
        }
      }
      if (isset($arrDuty['jobs'])) {
        foreach ($arrDuty['jobs'] as $intJobID => $arrJob) {
          //if (strtoupper(trim($arrJob['programme'])) == trim(strtoupper($strSearch))) {
          if (isset($arrFilterText[trim($arrJob['programme'])])) {
            $intShowDuty = 1;
          }
        }
      }
      // Do we unset it?
      if ($intShowDuty == 0) {
        unset ($arrallocations['assigned'][$intDutyID]);
      }
    }
    if (count($arrallocations['assigned']) == 0){
      unset ($arrallocations);
    }

  }
  // ================================================================================ END The Allocated Duties ================================================================================

  if (isset($arrallocations)) {
    if (($lateststart - $earlieststart) <= 36000) {
      $lateststart  = $earlieststart + 54000;
    }
    $arrallocations['earlieststart'] = $earlieststart;
    $arrallocations['lateststart'] = $lateststart;
    return ($arrallocations);
  }
}