<?php
/*
* objective: Call of function containing validtion for duty and rota start date and number of week
* Created Date: 20-07-2021
* Description: Call of function containing validtion for duty and rota start date and number of week
* */

session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$intStaffID = $_SESSION['user']['StaffID'];

$pageid = 24;
$teamId = $_REQUEST['teamId'];
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $teamId);

$intType = 0;
$intShowForm = 1;
$rotaid = $_REQUEST["rotaid"];
$dutyid = $_REQUEST["dutyid"];
$dayofrota = $_REQUEST["dayofrota"];
if (($permissions->canmodify == 1) || ($permissions->cancreate == 1)) {
    list($strConflictRotaName, $intStatus) = validationForDiffDateweekRota($rotaid, $dutyid, $dayofrota);
    $response_array = array('status' => 'warning', 'sqlstatus' => $intStatus, 'strConflictRotaName' => $strConflictRotaName);
}
else{
    $response_array = array('status' => 'failed', 'sqlstatus' => 0, 'strConflictRotaName' => '');
}
header('Content-type: application/json');
echo json_encode($response_array);

