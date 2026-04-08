<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
require_once "../../../function-includes/helpers.php";
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
$jobid = $_REQUEST['jobid'];
$isEdited = $_REQUEST['isEdited'];
echo deleteUnallocatedJob($jobid,$isEdited);