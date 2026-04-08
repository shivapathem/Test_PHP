<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';


  //Check User Authentication
  $pageid = 4;
  //$perms = unserialize($_SESSION['areaperms']);
  $perms = $_SESSION['areaperms'][$pageid];

  /*$canview = $perms->get_canview();
  $canmodify = $perms->get_canmodify();
  $canviewextended = $perms->get_isviewextended();
  $canmodifyextended = $perms->get_ismodifyextended();
  $candelete = $perms->get_isdelete();*/
$canview = $perms['isview'];
$canmodify = $perms['ismodify'];
$canviewextended = $perms['isviewextended'];
$canmodifyextended = $perms['ismodifyextended'];
$candelete = $perms['isdelete'];
    
    
  $intDutyDateID = $_REQUEST['DutyDateID'];
  $intDutyID = $_REQUEST['DutyID'];
  //$strDutyName = $_REQUEST['DutyName'];
  $strDutyName = '';
  $intDutyType = $_REQUEST["DutyTypeID"];
  $strHistory = $_REQUEST["History"];
  $strStartDate = $_REQUEST["startdate"];
  $strEndDate = $_REQUEST["enddate"];

  $intDBDutyType = ($intDutyType + 1);
  $intStaffID = $_SESSION['user']['StaffID'];
  $intAreaID = $_SESSION['user']['AreaID'];


  $strHistory = '';
  $strLastMod = '';
    
  if ($canmodify == 1) {  
    list($intStatus, $strStatus) = InsUpdMasterDutyDate($intDutyDateID, $intDutyID, $intDutyType, $strStartDate, $strEndDate, $strLastMod, $strHistory);
    $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'success');
  }
  else {
    $intStatus = 0;
    $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
  }


  header('Content-type: application/json');
  echo json_encode($response_array);

