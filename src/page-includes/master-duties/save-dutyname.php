<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';


  //Check User Authentication
$pageid = 4;

$perms = $_SESSION['areaperms'];
for($ArraySeq = 0; $ArraySeq < count($perms); $ArraySeq++){
    if($perms[$ArraySeq]["formid"] == $pageid){
        $canview = $perms[$ArraySeq]["isview"];
        $canmodify = $perms[$ArraySeq]["ismodify"];
        $canviewextended = $perms[$ArraySeq]["isviewextended"];
        $canmodifyextended = $perms[$ArraySeq]["ismodifyextended"];
        $candelete = $perms[$ArraySeq]["isdelete"];
    }
}

  $intDutyID = $_REQUEST['DutyID'];
  $strDutyName = $_REQUEST['DutyName'];
  $intDutyType = $_REQUEST["DutyTypeID"];
  $strHistory = $_REQUEST["History"];

  $intDBDutyType = ($intDutyType + 1);
  $intStaffID = $_SESSION['user']['StaffID'];
  $intAreaID = $_SESSION['user']['AreaID'];

  $strLastMod = '';

  if ($canmodify == 1) {  
    list($intNewDutyID, $intStatus, $strStatus) = InsUpdMasterDutyName($intDutyID, $intAreaID, $intDBDutyType, $strDutyName, $strLastMod, $strHistory);
    $response_array = array('status' => 'success', 'sqlstatus' => 1, 'sqlstatusstring' => $strStatus, 'newdutyid' => $intNewDutyID);
  }
  else {
    $intStatus = 0;
    $intNewDutyID = 0;
    $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges', 'newdutyid' => $intNewDutyID);
  }
  
  header('Content-type: application/json');

  echo json_encode($response_array);

