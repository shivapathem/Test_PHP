<?php

namespace App\Policies\Admin\AllocateUser;

use App\Models\User;

class AllocateUserPolicy
{
    /**
     * Determine whether the user can create allocate users.
     *
     * @param User $user
     * @return bool
     */
    public function createAllocateUser(User $user): bool
    {
        return ($user->isSchedulingTeamAdmin == 1 || $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1);
    }
    /*
     * Determine whether the user can create area allocate users.
     */
    public function createAreaAllocateUser(User $user): bool
    {
        return ($user->isSchedulingTeamAdmin == 1 || $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1);
    }

    /**
     * Determine whether the Area Permissions tab is visible.
     * ($intSysAdmin == 1) || !empty($userDivisionsList)
     * Only SysAdmin or Area Admin — STA/Scheduler do NOT see this tab.
     */
    public function viewAreaPermissionsTab(User $user): bool
    {
        return $user->isSystemAdmin == 1 || $user->isDivisionalAdmin == 1;
    }

    /**
     * Determine whether the user can allocate/add users to a specific area.
     * SysAdmin OR Area Admin of that specific area.
     *
     * @param User $user
     * @param int  $areaId
     */
    public function allocateToArea(User $user, int $areaId): bool
    {
        if ($user->isSystemAdmin == 1) {
            return true;
        }
        return $user->getAreasRoles
            ->where('RoleName', 'Area Admin')
            ->where('DivisionID', $areaId)
            ->count() > 0;
    }

    /**
     * Determine whether the user can modify area users (remove, toggle Area Report) for a specific area.
     * SysAdmin OR Area Admin of that specific area.
     *
     * @param User $user
     * @param int  $areaId
     */
    public function modifyAreaUser(User $user, int $areaId): bool
    {
        return $this->allocateToArea($user, $areaId);
    }

    /**
     * Determine whether the user can toggle area additional roles (Facility Administrator / Area Reports).
     * Requires SysAdmin or Area Admin of any area.
     *
     * @param User $user
     * @return bool
     */
    public function canToggleAreaRole(User $user): bool
    {
        if ($user->isSystemAdmin == 1) {
            return true;
        }
        return $user->getAreasRoles->where('RoleName', 'Area Admin')->count() > 0;
    }

    /**
     * Determine whether the user can update area user roles.
     * Only System Administrators can change roles.
     *
     * @param User $user
     * @return bool
     */
    public function canUpdateAreaUserRole(User $user): bool
    {
        return $user->isSystemAdmin == 1;
    }

    /**
     * Check if user can manage staff team.
     *
     * @param User $user
     * @param int|string $teamId
     * @return bool
     */
    public function canManageStaffTeam(User $user, $teamId): bool
    {
        if ($user->isSystemAdmin == 1) {
            return true;
        }

        // Area Admin (RoleID < 3) grants canmodify for all teams (RoleID < 3 rule)
        if ($user->isDivisionalAdmin == 1) {
            return true;
        }

        // STA/Scheduler: only manage the specific team they belong to (getUserRoleByTeam)
        $teamData = collect($user->userSetup)->first(function ($item) use ($teamId) {
            return (int) ($item->schedulingteamid ?? 0) === (int) $teamId;
        });

        if (!$teamData) {
            return false;
        }

        return ($teamData->scheduling_team_admin ?? 0) >= 1
            || ($teamData->scheduler ?? 0) >= 1;
    }

    /**
     * Check if user can view staff team.
     *
     * @param User $user
     * @param int|string $teamId
     * @return bool
     */
    public function canViewStaffTeam(User $user, $teamId): bool
    {
        if ($user->isSystemAdmin == 1) {
            return true;
        }

        $teamData = collect($user->userSetup)->firstWhere('schedulingteamid', $teamId);

        return $teamData !== null;
    }
}
