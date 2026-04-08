<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$intRotaPeopleID = $_REQUEST['RotaPeopleID'];
$intRotaID = $_REQUEST['RotaID'];
$intWeekID = $_REQUEST['WeekID'];
$intScheduledPersonID = $_REQUEST['ScheduledPersonID'];
$strStartDate = $_REQUEST['StartDate'];
$strEndDate = $_REQUEST['EndDate'];
$teamId = $_REQUEST['teamId'];
$pageid = 24;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $teamId);

$strLastMod = '';
$strHistory = '';
if ($permissions->candelete == 1) {
    list($intStatus, $strStatus) = DropPersonOnRota($intRotaPeopleID, $intRotaID, $intWeekID, $intScheduledPersonID, $strStartDate, $strEndDate, $strLastMod, $strHistory);
    $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus);
}
else
{
    $response_array = array('status' => 'fail', 'sqlstatus' => 0, 'sqlstatusstring' => 'No Access to create and edit');
}
header('Content-type: application/json');
echo json_encode($response_array);
