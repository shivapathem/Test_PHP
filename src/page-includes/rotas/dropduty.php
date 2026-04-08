<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';
include_once '../../function-includes/masterduty_functions.php';

$intDutyID = $_REQUEST['DutyID'];
$intDutyTypeID = $_REQUEST['DutyTypeID'];
$intRotaID = $_REQUEST['RotaID'];
$intWeekID = $_REQUEST['WeekID'];
$intDayOfRota = $_REQUEST['DayOfRota'];
$intAssigned = $_REQUEST['IsAssigned'];
$intTemplate = $_REQUEST['IsTemplate'];
$strStartDate = '';
$strEndDate = '';
$rsDuty = GetDutyDetailsByID($intDutyID);
$row = json_decode($rsDuty, true);               
$pageid = 24;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $row['TeamID']);

if (($permissions->canmodify == 1) || ($permissions->cancreate == 1)) {
    list($intStatus, $strStatus) = DropDutyOnRota($intDutyID, $intDutyTypeID, $intRotaID, $intDayOfRota, $intWeekID, $intAssigned, $intTemplate, $strStartDate, $strEndDate);
    $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus);
}
else {
    $response_array = array('status' => 'fail', 'sqlstatus' => 0, 'sqlstatusstring' => 'You do not have permission to drop duty to rotas');
}

header('Content-type: application/json');
echo json_encode($response_array);
