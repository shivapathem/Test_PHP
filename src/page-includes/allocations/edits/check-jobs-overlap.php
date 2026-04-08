<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
// The duty we are dropping on
$intDutyID = $_POST['dutyid'];
$intJobID = $_POST['jobid'];
$intDrpoPos = $_POST['droppos'];
$role = empty($_POST["role"]) ? 0 : $_POST["role"];
$arrCanDrop = candropjob($intDutyID, $intJobID, $intDrpoPos);
$finalArray = array("candropstatus"=>$arrCanDrop['Success']);
echo json_encode($finalArray);
die();
?>