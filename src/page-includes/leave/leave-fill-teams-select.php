<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';

$commonDbobj = new classCommonDBFunctions();

$id = $_REQUEST['id'];
$intLeaveGroup = $_REQUEST['leavegroup'];
$intCurrType = $_REQUEST['typeid'];
$sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  $arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLogin($sessUserNetLogin, 0),true);

  foreach ($arrUserSettings['LeaveRequests'] as $intGroupID => $arrGroup) {
    if ($arrGroup['Admin'] == 1) {
      $arrGroupsCanRequest[$intGroupID] = $arrGroup;
    }
  } 
  echo '<select size="1" name="leavetype">';
  foreach ($arrUserSettings['LeaveRequests'][$intLeaveGroup]['LeaveTypes'] as $intTypeID => $strType) {
    if ($intTypeID == $intCurrType) {
      echo '<option selected value="'.$intTypeID.'">'.$strType.'</option>';      
    }
    else {
      echo '<option value="'.$intTypeID.'">'.$strType.'</option>';    
    }
  }
  echo '</select>';
