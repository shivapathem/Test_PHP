<?php
/*
* objective: Delete People from rota
* Created By: Vipin Kushwaha
* Created Date: 16-04-2021
* Description: Contains call back of delete people from rota
* */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$pageid = 24;
// Call User Permission function.
$intTeamID = $_REQUEST['teamid'];
if($intTeamID == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $intTeamID);
}

$rotaid = $_REQUEST['rotaid'];
$rotapersonid = $_REQUEST['rotapersonid'];

if ($permissions->candelete == 1) {
    list($intStatus, $strStatus) = DeletePersonFromRota($rotaid, $rotapersonid);
    $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus);
}
else {
    $response_array = array('status' => 'fail', 'sqlstatus' => 0, 'sqlstatusstring' => 'You do not have permission to unallocate person from Rota Pattern.');
}
header('Content-type: application/json');
echo json_encode($response_array);

