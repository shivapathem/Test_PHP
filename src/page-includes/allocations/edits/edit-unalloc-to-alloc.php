<?php
session_start();
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/DB_Functions.php';

include_once '../../../function-includes/allocations_functions.php';

// The id of the unallocated duty
$intSourceID = $_REQUEST['sourceid']; 
$intDestStaffidID = $_REQUEST['deststaffid']; 
$dteDate = $_REQUEST['destdate']; 

UnallocToAlloc ($intSourceID, $intDestStaffidID, $dteDate);



?>