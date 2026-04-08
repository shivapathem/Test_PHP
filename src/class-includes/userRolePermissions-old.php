<?php
/**
 * Class userRolePermissions
 * This class use for User Role and Permission by Form ID
 */

include_once __DIR__.'/../function-includes/DB_Functions.php';
include_once __DIR__.'/../function-includes/common/classCommonDBFunctions.php';

class userRolePermissions
{
    public $canview;
    public $canmodify;
    public $cancreate;
    public $candelete;
    public $canmakepublic;
}

/**
 * Function will be use for return User's permissions.
 * @param $pageid
 * @return usecreateRolePermissions
 */
function getUserRolePermissions($pageId, $teamId = 0)
{
    // Create instant of the userRolePermissions class.
    $permissions = new userRolePermissions();
    
    //This is for System Admin Role
    if ($_SESSION['user']['SysAdmin'] == 1) {
               $permissions->canview = 1;
                $permissions->canmodify = 1;
                $permissions->cancreate = 1;
                $permissions->candelete = 1;
                $permissions->canmakepublic = 1;
                $permissions->userhighrole = 1;
                $permissions->teamId = $teamId;
                $permissions->highestrole = $_SESSION['user']['SysAdmin'];
                return $permissions;
    }
    //get highest role by form and  user id
    $highestrole = '';
    $highestrole = GetHighestRoleIdByForm($pageId);
    for ($ArraySeq = 0; $ArraySeq < (count($perms) - 1); $ArraySeq++) {
        if ($teamId > 0) {
            if (@$perms[$ArraySeq]["formid"] == $pageId && $perms[$ArraySeq]["TeamID"] == $teamId) {
                $permissions->canview = $perms[$ArraySeq]["isview"];
                $permissions->canmodify = $perms[$ArraySeq]["ismodify"];
                $permissions->cancreate = $perms[$ArraySeq]["iscreate"];
                $permissions->candelete = $perms[$ArraySeq]["isdelete"];
                $permissions->canmakepublic = $perms[$ArraySeq]["ismakepublic"];
                $permissions->userhighrole = $perms[$ArraySeq]["RoleID"];
                $permissions->teamId = $teamId;
                $permissions->highestrole = $highestrole;
            }
        } elseif ((@$perms[$ArraySeq]["formid"] == $pageId) && ($perms[$ArraySeq]["RoleID"] == $highestrole)) {
            $permissions->canview = $perms[$ArraySeq]["isview"];
            $permissions->canmodify = $perms[$ArraySeq]["ismodify"];
            $permissions->cancreate = $perms[$ArraySeq]["iscreate"];
            $permissions->candelete = $perms[$ArraySeq]["isdelete"];
            $permissions->canmakepublic = $perms[$ArraySeq]["ismakepublic"];
            $permissions->userhighrole = $perms[$ArraySeq]["RoleID"];
            $permissions->teamId = $teamId;
            $permissions->highestrole = $highestrole;
        }
    }
   
    return $permissions;
}
