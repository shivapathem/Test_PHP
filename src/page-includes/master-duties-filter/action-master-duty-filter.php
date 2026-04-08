<?php
session_start();
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../function-includes/masterduty_filter_functions.php';
include_once '../../class-includes/userRolePermissions.php';
    /*Set the permission of the user for this page*/
    //Check User Authentication
$pageid = 4;
$teamId = $_REQUEST['teamId'] ?? 0;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $teamId);

    //get all request param master duties
    if ($permissions->canmodify == 0) {
      $intStatus = 0;
      $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
      header('Content-type: application/json');
      echo json_encode($response_array); exit();
    }
    $filterID =  $_REQUEST['dataFilterID'];
    $actionKey =  $_REQUEST['actionKey'];
    $actionValue =  $_REQUEST['actionValue'];
    $userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    $addNewRow = '';
    list($filterID,$intStatus, $strStatus)  = ActionMasterDutyFilter($filterID,$actionKey,$actionValue,$userID);
   
    if($intStatus == false){
        $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
      }
      else{
        
        $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus,'newrow'=>$addNewRow,'filterID'=>$filterID); 
      }
    
    /*else {
      $intStatus = 0;
      $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
    }*/
    header('Content-type: application/json');
  echo json_encode($response_array);
?>
