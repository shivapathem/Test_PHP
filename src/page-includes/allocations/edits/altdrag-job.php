<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';  

// The duty we are dropping on
$intDutyID = $_REQUEST['dutyid'];
$intJobID = $_REQUEST['jobid'];
$intDrpoPos = $_REQUEST['droppos'];
$role = empty($_REQUEST["role"]) ? 0 : $_REQUEST["role"];

$arrCanDrop = candropshiftjob ($intDutyID, $intJobID, $intDrpoPos);

if ($arrCanDrop['Success'] == 0) {
  // Now assign the job to the new duty ($intDestEditedDutyID)
  // Get the new ID of the job (edited)
  // Chage the allocation ID, Staff Number over
  $response=altDragCopyJob ($intJobID, $intDutyID, $arrCanDrop['StartTime'], $arrCanDrop['EndTime'],$role);
	$response = json_decode($response,true);	
}    
$finalArray = array("strstatus"=>$response['spStatus'],"strsmsg"=>$response['errorMessage'],"candropstatus"=>$arrCanDrop['Success']);
echo json_encode($finalArray);
die();
