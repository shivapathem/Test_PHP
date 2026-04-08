<?php
if (session_status() == PHP_SESSION_NONE) {
 session_start();
}
//Scheduled Person 

include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';
/*Set the permission of the user for this page*/
//Check User Authentication
$pageid = 6;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);
$response_array = array('status' => 'success', 'permissions' => (array)$permissions);

header('Content-type: application/json');
echo json_encode($response_array);

