<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';

//Check User Authentication
$pageid = 1;//Master duties form id
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);

$intDutyID = $_REQUEST['dutyid'];
$intAction = $_REQUEST['action'];
$strLastModDate = $_REQUEST['lastmoddate'] == "" ? date("Y-m-d H:i:s"): $_REQUEST['lastmoddate'];
if ( ($permissions->candelete  == 1)){
    list($intStatus, $strNewLastModDate) = DeleteMasterDuty($intDutyID, $intAction, $strLastModDate);
    $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'lastmoddate' => $strNewLastModDate);
}
else {
    $intStatus = 0;
    $response_array = array('status' => 'You do not have delete privileges', 'sqlstatus' => $intStatus, 'lastmoddate' => $strLastModDate);
}

header('Content-type: application/json');
echo json_encode($response_array);
