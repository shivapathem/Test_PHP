<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';

//Check User Authentication
$pageid = 1;
// Call User Permission function.
$intTeamID = $_REQUEST["dutyteam"];
$permissions = getUserRolePermissions($pageid, $intTeamID);
  $intDutyID = $_REQUEST['DutyID'];
  $strDutyName = trim($_REQUEST['DutyName']);
  
  $intDutyType = $_REQUEST["DutyTypeID"];
  $strHistory = $_REQUEST["History"];

  $intDBDutyType = ($intDutyType + 1);
  $intStaffID = $_SESSION['user']['StaffID'];
  $intAreaID = $_SESSION['user']['AreaID'];  
    
    $strStartHour = $_REQUEST["starthour"];
    $strStartMinute = $_REQUEST["startminute"];
    $strEndHour = $_REQUEST["endhour"];
    $strEndMinute = $_REQUEST["endminute"];
    $strDurationHour = isset($_REQUEST["durationhour"]) ? $_REQUEST["durationhour"] : 0;
    $strDurationMinute = isset($_REQUEST["durationminute"]) ? $_REQUEST["durationminute"] : 0;
    $strWindowHour = isset($_REQUEST["windowhour"]) ? $_REQUEST["windowhour"] : 0;
    $strWindowMinute = isset($_REQUEST["windowminute"])? $_REQUEST["windowminute"] : 0;
    $intSaturday = $_REQUEST["numsaturday"];
    $intSunday = $_REQUEST["numsunday"];
    $intMonday = $_REQUEST["nummonday"];
    $intTuesday = $_REQUEST["numtuesday"];
    $intWednesday = $_REQUEST["numwednesday"];
    $intThursday = $_REQUEST["numthursday"];
    $intFriday = $_REQUEST["numfriday"];
    $breakTimeHour = $_REQUEST["breakTimeHour"];
    $breakTimeMinute = $_REQUEST["breakTimeMinute"];
    $labelId1 = $_REQUEST["labelIds"][0] ?? 0;
    $labelId2 = $_REQUEST["labelIds"][1] ?? 0;
    $labelId3 = $_REQUEST["labelIds"][2] ?? 0;
    $labelId4 = $_REQUEST["labelIds"][3] ?? 0;
    $labelId5 = $_REQUEST["labelIds"][4] ?? 0;
    $labelId6 = $_REQUEST["labelIds"][5] ?? 0;
    $strBreakTime = (($breakTimeHour * 3600) + ($breakTimeMinute * 60));

    $intStartTime = (($strStartHour * 3600) + ($strStartMinute * 60));
    $intEndTime = (($strEndHour * 3600) + ($strEndMinute * 60));
    $intDuration = (($strDurationHour * 3600) + ($strDurationMinute * 60));
    $intWindow = (($strWindowHour * 3600) + ($strWindowMinute * 60));
    $startWeek =  $_REQUEST["weekavaialblefrom"];
    $endWeek = $_REQUEST["weekavaialbleto"];
    $startYear =$_REQUEST["yearavaialblefrom"] ;
    $endYear =  $_REQUEST["yearavaialbleto"] ;
    $strBackColour = 0;
    $strForeColour = 0;
    $intDutyColour = $_REQUEST["dutycolour"];
    $strHistory = '';
    $strLastMod = '';    
    $isNeedCovering = $_REQUEST["isNeedCovering"] ?? 1;
    $isOverrideOver12 = $_REQUEST["isOverrideOver12"] ?? 1;
if (($permissions->canmodify == 1) || ($permissions->cancreate == 1)) {
      list($intStatus, $strStatus) = InsUpdMasterDutyDetails($intDutyID,$intTeamID,$intAreaID, $intDBDutyType, $strDutyName, $intStartTime, $intEndTime, $intDuration, $intWindow, $intDutyColour, $strBackColour, $strForeColour, $intSaturday, $intSunday, $intMonday, $intTuesday, $intWednesday, $intThursday, $intFriday, $strLastMod, $strHistory,$startYear,$endYear,$startWeek,$endWeek,$strBreakTime,$labelId1, $labelId2, $labelId3, $labelId4, $labelId5, $labelId6, $isNeedCovering, $isOverrideOver12);
      
      if( $intStatus == 0 ){
        $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
      }
      else{
        $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
      }
    }
    else {
      $intStatus = 0;
      $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
    }
  
  header('Content-type: application/json');
  echo json_encode($response_array);

