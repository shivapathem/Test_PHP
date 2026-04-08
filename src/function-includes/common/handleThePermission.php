<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
//Scheduled Person 

include_once '../../function-includes/DBHelper.php';
include_once '../../class-includes/userRolePermissions.php';
/*Set the permission of the user for this page*/
//Check User Authentication
$pageid = $_REQUEST['pageId'];
$teamid  = 0;
if(isset($_REQUEST['TeamID'])){
 $teamid = $_REQUEST['TeamID'];
}

// Call User Permission function.
if($teamid == 0){
 $permissions = getUserRolePermissions($pageid,$teamid);
 
 $response_array = array('status' => 'success', 'permissions' => (array)$permissions);
header('Content-type: application/json');
  echo json_encode($response_array);
}else{
 $permissions = getUserRolePermissions($pageid,$teamid);
 $response_array = array('status' => 'success', 'permissions' => (array)$permissions);
  echo json_encode($response_array);
}



