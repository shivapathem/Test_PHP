<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';

$intJobID = $_POST['jobid'];
$date = $_POST['date'];
$teamId = $_POST['teamId'];
$allocationId =$_POST['AllocationId']?? 0;
$role = empty($_POST["role"]) ? 0 : $_POST["role"];

if (isset($_POST['droppos'])) {
  $intDropPos = $_POST['droppos'];
}
else {
  $intDropPos = -1;
}

MoveJobToOtherDuty(0, $intJobID, $teamId, $date, 0, $role);
