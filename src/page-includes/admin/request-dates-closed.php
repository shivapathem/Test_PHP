<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/requestfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intGroupID = $_REQUEST["requestgroup"];
$intAdminLevel = GetAdminLeaveRequestGroupsAdminFromLogin ($strUser, $intGroupID);

$date = date("Y-m-d");
$strLeaveGroupDesc = GetLeaveRequestDescFromID ($intGroupID);
$arrRequestClosedDates = GetRequestClosedDays($intGroupID);
// Restricted dates
echo '<br><div class="tableheadersmall medtextboldcentre" style="position: relative; width:800px">';
echo '<br>Dates the Request book is either closed or restricted.<br>
      For '.$strLeaveGroupDesc.'<br><br>';
echo '<div class="DutyCellBottomLeft" onclick="javascript:EditRestriction(0, '.$intGroupID.')";>';
if ($intAdminLevel == 2) {
  echo '<table class="tablesmall">';
  echo '<tr>';
  echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
  echo '<td>';
  echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
}
echo '</div>';
echo '</div>';

echo '<table class="tablesmalltidy" id="proglist" width="800px">';
echo '<tr>';
echo '<th>';
echo 'Start Date';
echo '</th>';
echo '<th>';
echo 'End Date';
echo '</th>';
echo '<th>';
echo 'Type';
echo '</th>';
if ($intAdminLevel == 2) {
  echo '<th>';
  echo 'Delete';
  echo '</th>';
}
echo '</tr>';
if (isset($arrRequestClosedDates)) {
  foreach ($arrRequestClosedDates as $intID => $arrRequestClosedDate) {
    if ($intAdminLevel == 2) {
      echo '<tr id="tr'.$intID.'" class="handcursor" ondblclick="javascript:EditRestriction('.$intID.','.$intGroupID.')";>';  
    }
    else {
      echo '<tr>';
    }

    echo '<td>'; 
    echo $arrRequestClosedDate['StartDate'];
    echo '</td>';
    echo '<td>';
    echo $arrRequestClosedDate['EndDate'];
    echo '</td>';
    echo '<td>';
    switch ($arrRequestClosedDate['ClosureType']) {
      case 0:
       echo 'Closed - no requests possible';
       break;
      case 1:
       echo 'Requests possible - all shown as Not Available - Counted';
       break;
      case 2:
       echo 'Requests possible - all shown as Not Available - Not Counted';
       break;    
    }
    echo '</td>';
    if ($intAdminLevel == 2) {
      echo '<td align="center" onclick="javascript:DeleteRestriction('.$intID.','.$intGroupID.')";>';
      echo '<img src="images/delete.gif" border="0" height="16" width="16">';
      echo '</td>';
    }
  }
}
echo '</tr>';
echo '</table>';

echo '<br><br><br><br>';

echo '</div>';
?>
