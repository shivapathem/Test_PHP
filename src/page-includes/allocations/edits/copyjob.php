<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';

$intJobID = $_REQUEST['jobid'];
CopyEditedJob($intJobID); 