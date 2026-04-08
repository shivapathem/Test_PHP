<?php
/*
* objective: delete any rota
* Created By: Vipin Kushwaha
* Created Date: 12-04-2021
* Description: Contains logic that calls delete rotas function.
* */
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';
//Check User Authentication
$pageid = 24;
// Call User Permission function.
$teamId = $_REQUEST['teamId'];
$permissions = getUserRolePermissions($pageid, $teamId);

$intRotaID = $_REQUEST['RotaID'];
//$strEffDate = date('d/m/Y');
$strNames = '';
$record = array();

  if ($permissions->candelete == 1) {
    
    //First check there are no people on the Rota
    $rsPeopleJson = GetRotaPeople($intRotaID);
    $PeopleResult =  json_decode($rsPeopleJson,true);
    if (isset($row)) {
        $strNames = $PeopleResult[0]['Forename'].' '.$PeopleResult[0]['Surname'];
    }
    if (str_replace(' ', '', $strNames) == '') {
      list($intStatus, $strStatus) = DeleteRota($intRotaID);
      $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
    }
    else {
      $intStatus = 0;
      $strStatus = "The following people must be removed from the Rota before it can be deleted :\n".$strNames;
      $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
    }
  }
  else {
    $response_array = array('status' => 'fail', 'sqlstatus' => 0, 'sqlstatusstring' => 'You do not have permission to Delete Rotas'); 
  }
    
  header('Content-type: application/json');
  echo json_encode($response_array);
