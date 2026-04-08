<?php

namespace App\Policies\Setup;

use App\Models\User;
use App\Models\User\ScheduledPersonTeamLink;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User\RefRole;
use Illuminate\Container\Attributes\Auth;

class ScheduledPeoplePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view scheduled people.
     *
     * @param User $user
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->isSchedulingTeamAdmin == 1 ||  $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1 || $user->isScheduler == 1;
    }

    /**
     * Determine whether the user can create scheduled people.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        $canCreateStaff = ($user->userRoles->where('RoleName', RefRole::CREATE_NEW_STAFF)->count() > 0) ? 1 : 0;
        $canCreateFreelancer = ($user->userRoles->where('RoleName', RefRole::CREATE_NEW_FREELANCER)->count() > 0) ? 1 : 0;

        return $user->isSchedulingTeamAdmin == 1 || $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1 || $canCreateStaff == 1 || $canCreateFreelancer == 1;
    }

    /**
     * Determine whether the user can update scheduled people.
     *
     * @param User $user
     * @return bool
     */
    public function update(User $user): bool
    {
        $canUpdateStaff = ($user->userRoles->where('RoleName', RefRole::CREATE_NEW_STAFF)->count() > 0) ? 1 : 0;
        $canUpdateFreelancer = ($user->userRoles->where('RoleName', RefRole::CREATE_NEW_FREELANCER)->count() > 0) ? 1 : 0;

        return $user->isSchedulingTeamAdmin == 1 || $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1 || $canUpdateStaff == 1 || $canUpdateFreelancer == 1;
    }

    /**
     * Determine whether the user can delete scheduled people.
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->isSchedulingTeamAdmin == 1 || $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1;
    }

    /**
     * Check if user can manage specific scheduled person's team.
     *
     * @param User $user
     * @param int|string $teamId
     * @return bool
     */
    public function canManageTeam(User $user, $teamId): bool
    {
        if ($user->isSystemAdmin == 1) {
            return true;
        }

        $teamData = collect($user->userSetup)->firstWhere('schedulingteamid', $teamId);

        if (!$teamData) {
            return false;
        }

        return $teamData->scheduling_team_admin >= 1 || $teamData->scheduler >= 1;
    }

    /**
     * Check if user can view specific scheduled person's team.
     *
     * @param User $user
     * @param int|string $teamId
     * @return bool
     */
    public function canViewTeam(User $user, $teamId): bool
    {
        if ($user->isSystemAdmin == 1) {
            return true;
        }

        $teamData = collect($user->userSetup)->firstWhere('schedulingteamid', $teamId);

        return $teamData !== null;
    }

    /**
     * Determine if user can view schedule team history for this scheduled person.
     */
    public function viewHistory(User $user, ScheduledPersonTeamLink $teamLink): bool
    {
        return $this->hasTeamPermission($user, $teamLink->TeamID, 'canview');
    }

    /**
     * Determine if user can modify a history entry.
     */
    public function modifyHistory(User $user, ScheduledPersonTeamLink $teamLink): bool
    {
        return $this->hasTeamPermission($user, $teamLink->TeamID, 'canmodify');
    }

    /**
     * Determine if user can delete a history entry.
     */
    public function deleteHistory(User $user, ScheduledPersonTeamLink $teamLink): bool
    {
        return $this->hasTeamPermission($user, $teamLink->TeamID, 'candelete');
    }

    /**
     * Legacy-compatible: canDeleteHomeTeamHistory with DeletePermission propagation from current home team.
     *
     */
    public function canDeleteHomeTeamHistory(User $user, ScheduledPersonTeamLink $teamLink, bool $deletePermissionFromCurrent): bool
    {
        $ownPermission = $this->hasTeamPermission($user, $teamLink->TeamID, 'candelete');
        return $ownPermission || $deletePermissionFromCurrent;
    }

    /**
     * Shared helper: maps your current permission logic
     */
    public function hasTeamPermission(User $user, int $teamId, string $permType): bool
    {
        if (!isset($user->userSetup)) {
            $user->getUserRoleDetail();
        }

        // System admins have full permissions
        if ($user->isSystemAdmin == 1) {
            return true;
        }

        $teamData = collect($user->userSetup)->firstWhere('schedulingteamid', $teamId);

        if (!$teamData) {
            return false;
        }

        // Team role permissions (legacy DB flags IsDelete/IsModify/etc)
        $isAdmin = $teamData->scheduling_team_admin ?? 0;
        $isScheduler = $teamData->scheduler ?? 0;

        return match ($permType) {
            'canview'   => true,
            'canmodify' => $isAdmin > 0 || $isScheduler > 0,
            'candelete' => ($teamData->IsDelete ?? 0) > 0,  // Legacy match: IsDelete flag from DB for FormID=6
            default     => false,
        };
    }

    /**
     * Check if user can edit scheduled person - home team must be in authorized teams
     */
    public function canEditScheduledPerson(User $user, array $authorizedTeamIds, int $personHomeTeamId): bool
    {
        return !$personHomeTeamId || in_array($personHomeTeamId, $authorizedTeamIds);
    }

    public function createFreelancer(User $user): bool
    {
        // Get user's active main roles (from UserRoles relation)
        $access =  ($user->userRoles->where('RoleName', RefRole::CREATE_NEW_FREELANCER)->count() > 0) ? 1 : 0;
        // Scheduling Team Admins, Divisional Admins, and System Admins can always create freelancer
        if ($user->isSchedulingTeamAdmin == 1 || $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1 || $access == 1) {
            return true;
        }

        return false;
    }

    /**
     * Create New Staff
     *
     * User must have "Create New Staff" permission assigned in Allocate Users,
     * OR be a Scheduling Team Admin, Divisional Admin, or System Admin.
     */
    public function createStaff(User $user): bool
    {
        $access =  ($user->userRoles->where('RoleName', RefRole::CREATE_NEW_STAFF)->count() > 0) ? 1 : 0;
        // Scheduling Team Admins, Divisional Admins, and System Admins can always create staff
        if ($user->isSchedulingTeamAdmin == 1 || $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1 || $access == 1) {
            return true;
        }
        return false;
    }

    /**
     * Determine if user can create any scheduled person (staff or freelancer).
     *
     * This is used for the main permission check to show the "Add New" button and allow access to the creation form.
     */
    public function createAny(User $user)
    {
        return $this->createStaff($user) || $this->createFreelancer($user);
    }

    /**
     * Get all permissions for a user related to scheduled people functionality
     *
     * @param User $user
     * @return array
     */
    public function getPermissionsForUser(User $user): array
    {
        // Check Laravel policy permissions
        $canedit = $user ? $user->can('update', \App\Models\User::class) : false;
        $cancreate = $user ? $user->can('create', \App\Models\User::class) : false;
        $canCreateFreelancer = $user->can('createFreelancer', 'scheduled-people') ? '1' : '0';
        $canCreateStaff = $user->can('createStaff', 'scheduled-people') ? '1' : '0';

        return [
            'canview' => 1, // Default to 1 for viewing
            'canedit' => $canedit ? 1 : 0,
            'cancreate' => $cancreate ? 1 : 0,
            'canCreateFreelancer' => $canCreateFreelancer ? 1 : 0,
            'canCreateStaff' => $canCreateStaff ? 1 : 0,
        ];
    }
}
