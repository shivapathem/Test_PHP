<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../../function-includes/allocationsfunctions.php';
include_once '../../../function-includes/genericfunctions.php';
$dutyid = $_REQUEST['dutyid'];
$isRequestStatus = $_REQUEST['isRequest'];
$teamId =$_REQUEST['teamId'];
echo markOrUnmarkDutyForRequest($dutyid,$isRequestStatus,$teamId);
