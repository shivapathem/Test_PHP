<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsstaffing.php';
include_once '../../function-includes/reports-functions.php';
$intDepartmentID  = $_REQUEST['department'] ?? '';
$intWeekNumber = $_REQUEST['weeknumber'] ?? '';
$strFilter = $_REQUEST['filter'] ?? '';
$strFilter1 = $_REQUEST['filter1'] ?? '';
$intRequiredEFT = $_REQUEST['requiredeft'] ?? '';

// The Staff EFT needs to come from the team.....

if ($strFilter1 == '') {
  $strQuery = "SELECT Sum(scp.UC_EFT) AS SumEFT
       FROM   userdetails sp
       INNER JOIN scheduledpersonteam_link sptl
               ON sptl.scheduledpersonid = sp.ud_userid
       INNER JOIN UserConfigs scp
               ON scp.UC_UserID = sp.UD_UserID
       WHERE  Getdate() BETWEEN sptl.startdate AND sptl.enddate
       AND sptl.ishometeam = 1
       AND sptl.teamid = $intDepartmentID
       AND sptl.scheduledtype = 1
       AND Getdate() BETWEEN scp.UC_StartDate AND scp.UC_EndDate
       AND NOT EXISTS(SELECT 1
       FROM   user_web_config uwc
       WHERE  uwc.login = sp.ud_netlogin
              AND uwc.schedulingteamid = sptl.teamid
              AND uwc.hidestafflist = 1)";
  $pdo  = OpenDBLinkA7();    
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $sumEFT = ($row && isset($row['SumEFT'])) ? (float)$row['SumEFT'] : 0;

  //FOR Adhoc/Train EFT
  $strQuery1 = "SELECT Sum(case when AD_DutyType IN  (8,11)
    then isnull(ASP_LeaveDuration,0)
    else isnull(AD_Duration,0) end  - Isnull(AD_DutyBreakTime, 0)) ADHOCEFT
    FROM   allocations Al
    inner join AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
    inner join AllocationsScheduledPersons ap on ap.ASP_AllocationsDutyID = AD_AllocationsDutyID
    WHERE  AL_SchedulingTeamID = $intDepartmentID
    AND ( ( Isnull(AD_MasterDutyID, 0) = 0
    AND ( Isnull(AD_StartTimeSec, 0) > 0
    OR Isnull(AD_EndTimeSec, 0) > 0 ) )
    OR ( Isnull(AD_StartTimeSec, 0) = 0
    AND Isnull(AD_EndTimeSec, 0) = 0
    AND case when AD_DutyType IN  (8,11)
    then isnull(ASP_LeaveDuration,0)
    else isnull(AD_Duration,0) end > 0 ) )
    AND AD_DutyType <> 8
    AND ISNULL(ASP_LeaveType,0) not in (1,3)
    AND AL_WeekNumber BETWEEN $intWeekNumber AND $intWeekNumber
    AND ASP_DutyTeamID = $intDepartmentID";

  $pdo  = OpenDBLinkA7();

  $stmt1 = $pdo->prepare($strQuery1);
  $stmt1->execute();
  $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
  $adhocEFT = ($row1 && isset($row1['ADHOCEFT'])) ? ((float)$row1['ADHOCEFT'] / 3600) / 28 : 0;

  //FOR Free EFT
  $strQuery2 = "SELECT Sum(case when AD_DutyType IN  (8,11)
              then isnull(ASP_LeaveDuration,0)
              else isnull(AD_Duration,0) end  - Isnull(AD_DutyBreakTime, 0)) AS FreeEft
              FROM   allocations AL
              INNER JOIN timedimension td  ON al_weeknumber = td.ixyearweek
              INNER JOIN AllocationsScheduledPersons ap on AL.AL_AllocationsID = ASP_AllocationsID
              AND  td.ixdayinweek = ap.ASP_iDay
              inner join AllocationsDuties ad on ap.ASP_AllocationsDutyID = ad.AD_AllocationsDutyID
              INNER JOIN scheduledpersonteam_link sl  ON sl.scheduledpersonid = ap.ASP_SchedulingPersonID
              INNER JOIN schedulingteams st  ON sl.teamid = st.schedulingteamid  AND st.schedulingteamname = 'Freelancers'
              WHERE  td.ixyearweek = $intWeekNumber
              AND case when AD_DutyType IN  (8,11)
              then isnull(ASP_LeaveDuration,0)
              else isnull(AD_Duration,0) end  <> 0
              AND td.ddatetime BETWEEN sl.startdate AND sl.enddate
              AND sl.scheduledtype = 1
              AND sl.ishometeam = 1
              AND ASP_DutyTeamID = $intDepartmentID";
  $pdo2  = OpenDBLinkA7();
  $stmt2 = $pdo2->prepare($strQuery2);
  $stmt2->execute();
  $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
  $freeEFT = ($row2 && isset($row2['FreeEft'])) ? number_format(($row2['FreeEft']/3600) / 28, 1, '.', '') : 0;
  echo '<table class="medtextbold">';
  echo '<tr>';
  echo '<td style="color:#990000" width="150px">';
  echo 'Required EFT ' . $intRequiredEFT;
  echo '<br>AdHoc/Train EFT ' . number_format($adhocEFT, 1, '.', '') . "&nbsp;&nbsp;";
  echo '<br>Total EFT ' . number_format(((float)$intRequiredEFT + $adhocEFT), 1, '.', '');
  echo '</td>';
  echo '<td>';
  echo 'Staff EFT ' . number_format($sumEFT, 1, '.', '');
  echo '<br>Freelance Equiv EFT ' . number_format($freeEFT, 1, '.', '') . "&nbsp;&nbsp;";
  echo '<br>Total ' . number_format(($sumEFT + (float)$freeEFT), 1, '.', '');
  echo '</td>';
  echo '</tr>';
  echo '</table>';
} else {
  echo 'Please clear the Names filter to get summaries.';
}
