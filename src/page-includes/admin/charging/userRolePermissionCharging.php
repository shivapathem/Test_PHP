<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once $_SERVER['DOCUMENT_ROOT'].'/class-includes/userRolePermissions.php';
//Check User Authentication
$pageid = $_POST["page"];//Master duties form id
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);
$canView = $permissions->canview;
$canCreate = $permissions->cancreate;
$canModify = $permissions->canmodify;
$canDelete = $permissions->candelete;
echo json_encode(array('canview'=>$canView, 'cancreate'=>$canCreate, 'canmodify'=>$canModify, 'candelete'=>$canDelete));
