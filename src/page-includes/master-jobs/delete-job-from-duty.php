<?php 
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../class-includes/userRolePermissions.php';

$jobid = $_REQUEST['jobid'];
$mduty = $_REQUEST['mduty'];
//Check User Authentication
$pageid = 1;//Master duties form id

// Call User Permission function.
$permissions = getUserRolePermissions($pageid);
if ( ($permissions->candelete  == 1)) {
    $res = deleteJobFromDuty($mduty, $jobid);
    $response_array = array('status' => 1, 'strstatus' => 'success');
} else {
    $response_array = array('status' => 0, 'strstatus' => 'You do not have delete privileges');
}
echo json_encode($response_array);
