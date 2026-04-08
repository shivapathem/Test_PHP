<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$dteDate = $_REQUEST['dutydate'];
$schPersonId = $_REQUEST['schPersonId'];
$teamId = $_REQUEST['teamId']; 

$getWeekandDayArr = GetAllocationWeekandDay($dteDate);
$intWeek = $getWeekandDayArr['ixYearWeek'];
$intDay = (int)$getWeekandDayArr['ixDayInWeek'];

$pdo = OpenDBLinkA7();
try {
  $sql = " SELECT a.MarkedOvertime,
        a.MannualOThours, 
        st.schedulingTeamName as TeamName
		    FROM Allocations_Publish AS a (NOLOCK)
        INNER JOIN schedulingTeams AS st ON a.schedulingTeamId = st.schedulingTeamId 
        WHERE (a.WeekNumber = '".$intWeek."') 
        AND (a.iDay = '".$intDay."') 
        AND (a.SchedulingPersonID = '".$schPersonId."')
        AND (a.schedulingTeamId = '".$teamId."')";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logger()->critical('db error', (array)$e);
}

$mannualothr = $row['MannualOThours'] == 0 || $row['MannualOThours'] == '' ? number_format((float)0, 2, '.', '') : number_format((float)($row['MannualOThours'] / 3600), 2, '.', ''); 
if (isset($row['TeamName'])) {
echo'<table class="tabletidy" width="350px">';
  echo'<tr>';
  echo'<td valign="top" width="125px">Scheduling Team</td>';
  echo'<td valign="top">'.$row['TeamName'].'</td>';
  echo'</tr>';
}
  
  if ($row['MarkedOvertime'] != 0) {
    echo'<tr>';
    echo'<td valign="top" width="125px">Marked Overtime</td>';
    echo'<td valign="top">'.$mannualothr.' Hours</td>';
    echo'</tr>';
  }
echo'</table>';
