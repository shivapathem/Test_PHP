<?php
/**
 * Class userRolePermissions
 * This class use for User Role and Permission by Form ID
 */

use App\Models\Scheduling\SchedulingTeam;
use App\Policies\ForwardPlanning\MasterDutyJobPolicy;
use App\Policies\ForwardPlanning\RotaPatternPolicy;

// Check if a session is not already started
if (session_status() === PHP_SESSION_NONE ) {
    session_start();
}
include_once __DIR__.'/../function-includes/DB_Functions.php';
include_once __DIR__.'/../function-includes/common/classCommonDBFunctions.php';
include_once __DIR__ . '/../function-includes/laravel_init.php';

class UserRolePermissions
{
    public $canview = '';
    public $canmodify = '';
    public $cancreate = '';
    public $candelete = '';
    public $canmakepublic = '';
    public $userhighrole = '';
    public $teamId = '';
    public $highestrole = '';
}

/**
 * Function will be use for return User's permissions.
 * @param $pageid
 * @return usecreateRolePermissions
 */
function getUserRolePermissions($pageId, $teamId = 0)
{
    //Laravel policy permission
    if(in_array($pageId, [1, 2, 3, 4, 5, 24])) {
        return getUserPermissionPolicy($pageId, $teamId);
    }

    //Legacy validation
    $usernetlogin = isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ? $_SESSION['user']['user'] :$_COOKIE['editWeeklyUserNetLogin'];
    // Create instant of the userRolePermissions class.

    $permissions = new UserRolePermissions();
    $commonDbobj = new classCommonDBFunctions();

    $perms =  json_decode($commonDbobj->GetUserRolePermissionByPage($usernetlogin ,$pageId),true);
    
    //get highest role by form and  user id
    if(!empty($perms)){
            $highestrole = min(array_unique(array_column($perms,'RoleID')));
            for ($ArraySeq = 0; $ArraySeq < (count($perms)); $ArraySeq++) {
            
                if ($teamId > 0) {
                    if ($perms[$ArraySeq]["FormID"] == $pageId && $perms[$ArraySeq]["TeamID"] == $teamId) {
                        $permissions->canview = $perms[$ArraySeq]["IsView"];
                        $permissions->canmodify = $perms[$ArraySeq]["IsModify"];
                        $permissions->cancreate = $perms[$ArraySeq]["IsCreate"];
                        $permissions->candelete = $perms[$ArraySeq]["IsDelete"];
                        $permissions->canmakepublic = $perms[$ArraySeq]["IsMakePublic"];
                        $permissions->userhighrole = $perms[$ArraySeq]["RoleID"];
                        $permissions->teamId = $teamId;
                        $permissions->highestrole = $highestrole;
                    }elseif($perms[$ArraySeq]["FormID"] && ($perms[$ArraySeq]["TeamID"] != $teamId) && ($perms[$ArraySeq]["RoleID"] == 3))
					{
						$permissions->canview = 1;
                        $permissions->canmodify = ($permissions->canmodify) ? $permissions->canmodify : 0;
                        $permissions->cancreate = 0;
                        $permissions->candelete = 0;
                        $permissions->canmakepublic = 0;
                        $permissions->userhighrole = 0;
                        $permissions->teamId = $teamId;
                        $permissions->highestrole = $highestrole;
					}elseif($perms[$ArraySeq]["FormID"] && ($perms[$ArraySeq]["TeamID"] != $teamId) && ($perms[$ArraySeq]["RoleID"] < 3))
					{
						$permissions->canview = 1;
                        $permissions->canmodify = ($permissions->canmodify) ? $permissions->canmodify : 1;
                        $permissions->cancreate = 0;
                        $permissions->candelete = 0;
                        $permissions->canmakepublic = 0;
                        $permissions->userhighrole = 0;
                        $permissions->teamId = $teamId;
                        $permissions->highestrole = $highestrole;
					}
                } elseif (($perms[$ArraySeq]["FormID"] == $pageId) && ($perms[$ArraySeq]["RoleID"] == $highestrole)) {
                    $permissions->canview = $perms[$ArraySeq]["IsView"];
                    $permissions->canmodify = $perms[$ArraySeq]["IsModify"];
                    $permissions->cancreate = $perms[$ArraySeq]["IsCreate"];
                    $permissions->candelete = $perms[$ArraySeq]["IsDelete"];
                    $permissions->canmakepublic = $perms[$ArraySeq]["IsMakePublic"];
                    $permissions->userhighrole = $perms[$ArraySeq]["RoleID"];
                    $permissions->teamId = $teamId;
                    $permissions->highestrole = $highestrole;
                }
            }
    }
    return $permissions;
}

function getUserRoleByTeam($pageId, $teamId)
{
    //Laravel policy permission
    if(in_array($pageId, [1, 2, 3, 4, 5, 24])) {
        return getUserPermissionPolicy($pageId, $teamId);
    }
    //Legacy validation
    $permissions = new UserRolePermissions();
    $commonDbobj = new classCommonDBFunctions();
    $usernetlogin = isset($_SESSION['user']['user'])  && ($_SESSION['user']['user'] != '') ?$_SESSION['user']['user'] :$_COOKIE['editWeeklyUserNetLogin'];
    $permsWithTeams =  json_decode($commonDbobj->GetUserRolePermissionByPage($usernetlogin ,$pageId),true);
    $temp	=	99;
    if(!empty($permsWithTeams)){
		foreach($permsWithTeams as $permsWithTeamsVal)
		{
			if($permsWithTeamsVal['TeamID'] != 0 && $permsWithTeamsVal['RoleID'] < $temp)
			{
				$temp	=	$permsWithTeamsVal['RoleID'];
                $permissions->userhighrole 	=	$temp;
                $permissions->highestrole 	= 	$temp;
			}
            if (($permsWithTeamsVal['TeamID'] == 0) && ($permsWithTeamsVal['RoleID'] == 1)) {
                $permissions->canview 		=	$permsWithTeamsVal['IsView'];
				$permissions->canmodify 	=	$permsWithTeamsVal['IsModify'];
				$permissions->cancreate 	=	$permsWithTeamsVal['IsCreate'];
				$permissions->candelete 	=	$permsWithTeamsVal['IsDelete'];
				$permissions->canmakepublic = 	$permsWithTeamsVal['IsMakePublic'];
                $permissions->userhighrole = 1;
                $permissions->teamId = $teamId;
                $permissions->highestrole = $_SESSION['user']['SysAdmin'];
            }

			if (($permsWithTeamsVal['TeamID'] == 0) && ($permsWithTeamsVal['RoleID'] == 2)) {
                $permissions->canview 		=	$permsWithTeamsVal['IsView'];
				$permissions->canmodify 	=	$permsWithTeamsVal['IsModify'];
				$permissions->cancreate 	=	$permsWithTeamsVal['IsCreate'];
				$permissions->candelete 	=	$permsWithTeamsVal['IsDelete'];
				$permissions->canmakepublic = 	$permsWithTeamsVal['IsMakePublic'];
                $permissions->teamId 		= 	0;
            }

			if(($permsWithTeamsVal['TeamID'] == $teamId) && ($permsWithTeamsVal['FormID'] == $pageId))
			{
                $permissions->canview 		=	$permsWithTeamsVal['IsView'];
				$permissions->canmodify 	=	$permsWithTeamsVal['IsModify'];
				$permissions->cancreate 	=	$permsWithTeamsVal['IsCreate'];
				$permissions->candelete 	=	$permsWithTeamsVal['IsDelete'];
				$permissions->canmakepublic = 	$permsWithTeamsVal['IsMakePublic'];
				$permissions->teamId 		= 	$permsWithTeamsVal['TeamID'];
			}
		}

    }

	return $permissions;
}

/**
 * Laravel policy based authorization
 * @param int $pageId page id
 * @param int $teamId Team id
 */
function getUserPermissionPolicy(int $pageId, int $teamId = 0) {
    $permissions = new UserRolePermissions();
    global $userModel;
    switch($pageId) {
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
            $team = SchedulingTeam::find($teamId);
            $policy = new MasterDutyJobPolicy();
            $permissions->canview =	$policy->view($userModel, $team);
            $permissions->canmodify = $team != null ? $policy->update($userModel, $team) : 0;
            $permissions->cancreate = $team != null ? $policy->create($userModel, $team) : 0;
            $permissions->candelete = $team != null ? $policy->delete($userModel, $team) : 0;
            $permissions->canmakepublic = $team != null ? $policy->update($userModel, $team) : 0;
            $permissions->teamId = $teamId;
            break;
        case 24:
            $team = SchedulingTeam::find($teamId);
            $policy = new RotaPatternPolicy();
            $permissions->canview =	$policy->view($userModel, $team);
            $permissions->canmodify = $team != null ? $policy->update($userModel, $team) : 0;
            $permissions->cancreate = $team != null ? $policy->create($userModel, $team) : 0;
            $permissions->candelete = $team != null ? $policy->delete($userModel, $team) : 0;
            $permissions->canmakepublic = $team != null ? $policy->update($userModel, $team) : 0;
            $permissions->teamId = $teamId;
            break;
    }
    return $permissions;
}