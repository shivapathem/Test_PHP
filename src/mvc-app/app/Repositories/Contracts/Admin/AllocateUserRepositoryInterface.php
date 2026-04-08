<?php

namespace App\Repositories\Contracts\Admin;

use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use Illuminate\Support\Collection;
use stdClass;

interface AllocateUserRepositoryInterface
{
    /**
     * Get all actions
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getAllocateUsers(User $user): Collection;

    /*
     * Get all areas (divisions)
     * */
    public function getArea(): Collection;

    /*
    * Get area users by area id
    * */
    public function getAreaUsersByAreaId(int $areaId): Collection;

    /**
     * Get additional roles for area users
     * @param int $areaId
     * @return Collection
     */
    public function getAreaUsersAdditionalRoles(int $areaId): Collection;

    /**
     * Get staff details for autocomplete
     *
     * @param string $termKey
     * @param string $term
     * @return Collection
     */
    public function getStaffDetailsAutocompleteList(string $termKey, string $term): Collection;

    /**
     * Get user details from Active Directory
     *
     * @param string $netLogin
     * @return stdClass
     */
    public function getUserDetailsFromActiveDirectory(string $netLogin): stdClass;

    /**
     * Insert/allocate users record
     *
     * @param string $netLogin
     * @param User $user
     * @return array  result
     */
    public function insertAllocateUsers(string $netLogin, User $user): array;

    /*
    * Get area user permission history
    */
    public function getAreaUserPermissionHistory(int $areaId, int $userRoleId): array;

    /*
     * Get users for area allocation
     */
    public function getUsersForAreaAllocation(): array;

    /*
     * Get available roles for area allocation
     */
    public function getAvailableRoles(): array;

    /**
     * Get user team list
     * @param User $user
     * @return array
     */
    public function getUserTeamList(User $user);

    /**
     * Fetch staff data for given team/user type.
     *
     * Returns an array with the following keys:
     *  - staff: array of user rows (see AllocateUserRepository implementation)
     *  - mainRoles: list of roles used for the main dropdown (rota or non-additional)
     *  - additionalRoles: list of additional role definitions
     *  - schedulingTeamId: numeric team identifier
     *  - permissions: object containing {canmodify, candelete} flags for current user
     *  - hideSchedulingTeamOption: boolean flag used by UI
     *  - currentNetLogin: lowercase login name of current user
     *  - usertype: numeric type passed through
     *
     * The payload has been normalised so that the front‑end can render tables without
     * additional lookups or permission checks.
     *
     * @param SchedulingTeam $team
     * @param mixed $userType
     * @param mixed $divisionId
     * @param User $currentUser
     * @return mixed
     */
    public function getStaffTeamData(SchedulingTeam $team, $userType, $divisionId, User $currentUser);

    /**
     * Update permissions for a user/team.
     *
     * @param array $data
     * @param User $currentUser  Current authenticated user (for audit)
     * @return mixed
     */
    public function setUsersPermissions(array $data, User $currentUser);

    /**
     * Remove a staff member from a team.
     *
     * @param array $data
     * @return mixed
     */
    public function removeStaff(array $data);

    /**
     * Add a non-scheduled staff member to a team.
     *
     * @param array $data
     * @param User $currentUser
     * @return mixed
     */
    public function addNonScheduledStaff(array $data, User $currentUser);

    /**
     * Set default team for a user.
     *
     * @param array $data
     * @param User $currentUser
     * @return mixed
     */
    public function setDefaultTeam(array $data, User $currentUser);

    /**
     * Get staff history for scheduled and non-scheduled staff
     *
     * @param int $teamId
     * @param int $userId
     * @return array
     */
    public function getStaffHistory(int $teamId, int $userId): array;

    public function allocateUserToArea(int $userId, int $areaId, int $roleId, User $currentUser): array;

    /*
     * Get user details by ID
     */
    public function getUserDetailsById(int $userId);

    /*
     * Remove user from area
     */
    public function removeUserFromArea(int $userId, int $areaId): array;

    /*
     * Update user role in area
    */
    public function updateUserRole(int $userId, int $areaId, int $roleId, User $currentUser): array;

    /*
     * Get available area roles (Area Admin, Area Viewer)
    */
    public function getAreaRoles(): Collection;

    /**
     * Get additional area roles (Facility Administrator, Area Reports) for toggle functionality
     * @return Collection
     */
    public function getAdditionalAreaRoles(): Collection;

    /*
     * Get user details for area allocation form with HTML
    */
    public function getUserDetailsForAreaAllocationHtml(int $userId, int $areaId, bool $isSysAdmin): array;

    /*
     * Toggle additional role (Facility Administrator / Area Reports) for an area user
    */
    public function toggleAreaAdditionalRole(int $userRoleId, int $roleId, int $userId, int $areaId, string $action, User $currentUser): array;

     /**
     * Get permission descriptions for a user role
     * @return Collection
     */
    public function getPermissionDescriptions(): array;
}
