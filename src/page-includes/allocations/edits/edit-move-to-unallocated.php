<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/allocations_functions.php';
$intID = $_REQUEST['id']; 
$dutyID = MoveDutyToUnallocated ($intID);
echo $dutyID;
?>