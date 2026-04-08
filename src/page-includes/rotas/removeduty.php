<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$pageid = 24;
$teamId = $_REQUEST['teamId'];
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $teamId);

//$intRotaDutyID = $_REQUEST['RotaDutyID'];
$intDutyID = $_REQUEST['DutyID'];
$intRotaDutyID = $_REQUEST['RotaDutyID'];
$intRotaID = $_REQUEST['RotaID'];
$strLastModDate = date("Y-m-d H:i:s");
if ($permissions->candelete == 1) {
    list($intStatus, $strStatus) = RemoveDutyFromRota($intRotaID, $intDutyID, $intRotaDutyID, $strLastModDate);
    $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus); 
}
else{
    $response_array = array('status' => 'fail', 'sqlstatus' => 0, 'sqlstatusstring' => 'You do not have permission to delete and duty');
}
header('Content-type: application/json');
echo json_encode($response_array);
