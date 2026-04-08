<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';


  //Check User Authentication
  $pageid = 4;
  $perms = unserialize($_SESSION['areaperms']);
  $perms = $perms[$pageid];
  $canview = $perms->get_canview();
  $canmodify = $perms->get_canmodify();
  $canviewextended = $perms->get_isviewextended();
  $canmodifyextended = $perms->get_ismodifyextended();
  $candelete = $perms->get_isdelete();
  
  
  $intDutyID = $_REQUEST["dutyid"];
  $intTeamID = $_REQUEST["teamid"];
  $intAction = $_REQUEST["action"];

  
  if ($canmodify == 1) {  
    list($intStatus, $strStatus) = UpdateDutyTeam($intDutyID, $intTeamID, $intAction);
    $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus);
  }
  else {
    $intStatus = 0;
    $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
  }
  
  header('Content-type: application/json');
  echo json_encode($response_array);

  

