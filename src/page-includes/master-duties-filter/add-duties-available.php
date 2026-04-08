<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
  include_once '../../function-includes/DBHelper.php';
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
    $masterDutyIds =  json_decode(stripslashes($_REQUEST['masterDutyIds']));
    $filterID =  $_REQUEST['dataFilterID'];
    $isMiscDuty = $_REQUEST['isMiscDuty'];
    $actionType = $_REQUEST['actionType'];
    
    list($intStatus, $strStatus)  =  InsertAvailableDutiesToFilter($filterID,$masterDutyIds,$isMiscDuty,$actionType);
   
    if($intStatus == false){
        $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
      } else{
        $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
      }
    
    /*else {
      $intStatus = 0;
      $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
    }*/
    header('Content-type: application/json');
  echo json_encode($response_array);
?>
