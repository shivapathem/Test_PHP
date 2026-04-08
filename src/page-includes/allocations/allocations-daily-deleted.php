<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intTeamID = $_REQUEST['teamId'];
$strCurrentDate = $_SESSION['allocattionsdate'];

$arrTeamDefaults = GetTeamDefaults(0,$intTeamID);
$arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);
$isShiftleader=0;
 if (($arrStaffOptions[$intTeamID]['isScheduler'] != 1) && ($arrStaffOptions[$intTeamID]['isTeamAdmin'] != 1) && ($arrStaffOptions[$intTeamID]['isShiftLeader'] == 1)) {
	 $isShiftleader=1;
 } 
$getWeekandDayArr = GetAllocationWeekandDay($strCurrentDate);
$intWeek = $getWeekandDayArr['ixYearWeek'];
$intDay = intval($getWeekandDayArr['ixDayInWeek']);

$arrAllocations = ReadAllocationsDayDeleted($intTeamID, $intWeek, $intDay);

echo '<table class="tablegreysmallnoborder" width="700px">';
echo '<tr>';
echo '<td align="center" class="medtextbold" valign="top" nowrap>';
echo 'Deletions for '.date("d F Y (l)", strtotime($strCurrentDate)).'<br>';
echo 'Week '.spinweek($intWeek);
echo '<br>';
echo 'Showing '.$arrTeamDefaults[$intTeamID]['Description'];
echo '</td>';
echo '</table>';

if (isset($arrAllocations)) {
  if (isset($arrAllocations['Jobs'])) {
    echo '<table class="tablesmalltidy" width="700px">';
    echo '<tr height="20px">';
    echo '<th colspan="5">';
    echo 'Deleted Jobs';
    echo '</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<th>';
    echo 'Job Name';
    echo '</th>';
    echo '<th>';
    echo 'Start Time';
    echo '</th>';
    echo '<th>';
    echo 'End Time';
    echo '</th>';
    echo '<th>';
    echo 'Programme';
    echo '</th>';
    echo '<th>';
    echo '</th>';
    echo '</tr>';    
    foreach ($arrAllocations['Jobs'] as $intJobID => $arrJob) {
      echo '<tr>';
      echo '<td>';
      echo $arrJob['JobName'];
      echo '</td>';
      echo '<td>';
      echo gmdate("H:i", $arrJob['JobStartTime']);
      echo '</td>';
      echo '<td>';
      echo gmdate("H:i", $arrJob['JobEndTime']);
      echo '</td>';
      echo '<td>';
      echo $arrJob['Programme'];
      echo '</td>';        
      echo '<td align="right">';
      echo '<img onclick=\'javascript:RestoreJob('.$intJobID.','.$intTeamID.', '.$strCurrentDate.')\'; border="0" src="../images/menu/restore.png" width="12px" height="12px">&nbsp;&nbsp;';
      echo '</td>'; 
      echo '</tr>';     
    }
    echo '<tr height="20px">';
    echo '<td colspan="5"><hr>';
    echo '</td>';
    echo '</tr>';      
    echo '</table>'; 
    echo '<br>'; 
  }

  if (isset($arrAllocations['Duties'])) {
    echo '<table class="tablesmalltidy" width="700px">';
    echo '<tr height="20px">';
    echo '<th colspan="5">';
    echo 'Deleted Duties';
    echo '</th>';
    echo '</tr>';
   
    foreach ($arrAllocations['Duties'] as $intDutyID => $arrDuty) {
      
      echo '<tr>';
      echo '<th colspan="2">';
      echo 'Duty Name';
      echo '</th>';
      echo '<th>';
      echo 'Start Time';
      echo '</th>';
      echo '<th colspan="2">';
      echo 'End Time';
      echo '</th>';
      echo '</tr>';     
      echo '<tr>';
      echo '<td colspan="2">';    
      echo $arrDuty['DutyName'];
      echo '</td>';
      echo '<td>';
      echo gmdate("H:i", $arrDuty['DutyStartTime']);
      echo '</td>';
      echo '<td>';
      echo gmdate("H:i", $arrDuty['DutyEndTime']);
      echo '</td>';
      echo '<td align="right">';
      echo '<img  onclick=\'javascript:RestoreDuty('.$arrDuty['id'].', '.$intTeamID.', '.$strCurrentDate.','.$intWeek.','.$isShiftleader.')\';  border="0" src="../images/menu/restore.png" width="12px" height="12px">&nbsp;&nbsp;';
      echo '</td>';  
      echo '</tr>'; 
      if (isset($arrDuty['Jobs'])) {
        echo '<tr>';
        echo '<th>';
        echo '</th>';   
        echo '<th colspan="4">';      
        echo 'Jobs';
        echo '</th>';
        echo '</tr>';
        echo '<tr>';
        echo '<th width="100px">';
        echo '</th>';
        echo '<th>';
        echo 'Job Name';
        echo '</th>';
        echo '<th>';
        echo 'Start Time';
        echo '</th>';
        echo '<th>';
        echo 'End Time';
        echo '</th>';
        echo '<th>';
        echo 'Programme';
        echo '</th>';
        echo '</tr>';                 
        foreach ($arrDuty['Jobs'] as $intJobID => $arrJob) {
          echo '<tr>';
          echo '<td>';
          echo '</td>';
          
          echo '<td>';
          echo $arrJob['JobName'];
          echo '</td>';
          echo '<td>';
          echo gmdate("H:i", $arrJob['JobStartTime']);
          echo '</td>';
          echo '<td>';
          echo gmdate("H:i", $arrJob['JobEndTime']);
          echo '</td>';
          echo '<td>';
          echo $arrJob['Programme'];
          echo '</td>';        
          echo '</tr>';     
        }      
      }
      echo '<tr height="20px">';
      echo '<td colspan="5"><hr>';
      echo '</td>';
      echo '</tr>';  
    }
    echo '</table>'; 
  }
}
else {
  echo '<br><table class="tablesmallnoborder" id="daytable" width="100%">';
  echo '<thead>';
  echo '<tr class="GridviewScrollHeader">';
  echo '<th class="tableheadersmall">';
  echo "</th>";
  echo '<th class="tableheadersmall">';
  echo '<br>There are no Deleted Allocations or Jobs for this day.<br><br><br>';
  echo "</th>";
  echo "</tr>";
  echo "</thead>";
}
echo '</table>';

?>
<div id="dialog-restore-duty" title="Grid Checks" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to restore this Duty Or Job ?.</p>
</div>