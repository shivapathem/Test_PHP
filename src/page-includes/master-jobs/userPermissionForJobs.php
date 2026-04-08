<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
include_once '../../class-includes/userRolePermissions.php';
include_once 'setTeamCookieJob.php';
//Check User Authentication
$pageid = 1;//Master duties form id

// Call User Permission function.
if(isset($_COOKIE["masterdutyteams"]) && !empty($_COOKIE["masterdutyteams"])){
    $strTeams = $_COOKIE["masterdutyteams"] ?: '0';
}else{
    $strTeams = 0;
}
if($strTeams == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $strTeams);
}
$canView = $permissions->canview;
$canCreate = $permissions->cancreate;
$canModify = $permissions->canmodify;
$canDelete = $permissions->candelete;
echo json_encode(array('canview'=>$canView, 'cancreate'=>$canCreate, 'canmodify'=>$canModify, 'candelete'=>$canDelete));
