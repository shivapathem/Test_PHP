<?php

namespace App\Repositories\Admin;

use App\Models\User;
use App\Models\User\UserRole;
use App\Models\User\RefRole;
use App\Models\User\RolePermissionStatus;
use App\Models\Scheduling\Division;
use App\Models\History;
use App\Models\Scheduling\SchedulingTeam;
use App\Policies\Admin\AllocateUser\AllocateUserPolicy;
use App\Repositories\Contracts\Admin\AllocateUserRepositoryInterface;
use App\Mail\RoleChangeEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use stdClass;

class AllocateUserRepository implements AllocateUserRepositoryInterface
{
    private const ROLE_END_DATE = '9999-01-01 00:00:00.000';
    private const ERROR_PREFIX = 'Error: ';
    /**
     * Get all actions
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getAllocateUsers(User $user): Collection
    {
        $query = User::leftJoin('ScheduledPersonTeam_LINK as sptl', function ($join) {
            $join->on('sptl.ScheduledPersonID', '=', 'UD_UserID')
                ->where('sptl.scheduledType', 1)
                ->where('sptl.IsHomeTeam', 1)
                ->whereDate('sptl.StartDate', '<=', Carbon::now())
                ->whereDate('sptl.EndDate', '>=', Carbon::now());
        })->leftJoin('schedulingTeams as st', 'st.schedulingTeamId', '=', 'sptl.TeamID')
            ->whereNotNull('UD_NetLogin')
            ->orderBy('UD_DisplayName', 'asc')
            ->selectRaw("UserDetails.*, isNull(st.schedulingTeamName, '-') AS schedulingTeamName");
        return $query->get();
    }
    /*
    * Get all areas (divisions)
    */
    public function getArea(): Collection
    {
        return Division::active()
            ->orderBy('DivisionName', 'asc')
            ->get();
    }

    /*
    * Get area users by area id
    */
    public function getAreaUsersByAreaId(int $areaId): Collection
    {
        return User::select('UserDetails.UD_UserID as UserID')
            ->addSelect('ur.UR_UserRoleID as UserRoleID')
            ->addSelect('ur.UR_RoleID as RoleID')
            ->addSelect('ur.UR_DivisionId as DivisionId')
            ->addSelect('UserDetails.UD_DisplayFirstName as Forname')
            ->addSelect('UserDetails.UD_DisplayFirstName as PreferredForename')
            ->addSelect('UserDetails.UD_NetLogin as NetLogin')
            ->addSelect('UserDetails.UD_DisplayLastName as Surname')
            ->addSelect('UserDetails.UD_StaffNumber as StaffNumber')
            ->addSelect('UserDetails.UD_DisplayName as DisplayName')
            ->addSelect('UserDetails.UD_InternalEmail as InternalEmail')
            ->addSelect('UserDetails.UD_ExternalEmail as ExternalEmail')
            ->addSelect('RR.RoleName as RoleName')
            ->join('UserRoles as ur', function ($join) use ($areaId) {
                $join->on('ur.UR_UserID', '=', 'UserDetails.UD_UserID')
                    ->where('ur.UR_DivisionId', '=', $areaId);
            })
            ->join('REF_Roles as RR', 'ur.UR_RoleID', '=', 'RR.RoleID')
            ->where('RR.IsActive', 1)
            ->whereIn('RR.RoleName', [RefRole::AREA_ADMIN, RefRole::AREA_VIEWER])
            ->distinct()
            ->get();
    }

    /*
    * Get additional roles for area users
    */
    public function getAreaUsersAdditionalRoles(int $areaId): Collection
    {
        return User::select('UserDetails.UD_UserID as UserID')
            ->addSelect('ur.UR_RoleID as RoleID')
            ->addSelect('RR.RoleName as RoleName')
            ->addSelect('ur.UR_DivisionId as DivisionId')
            ->join('UserRoles as ur', function ($join) use ($areaId) {
                $join->on('ur.UR_UserID', '=', 'UserDetails.UD_UserID')
                    ->where('ur.UR_DivisionId', '=', $areaId);
            })
            ->join('REF_Roles as RR', 'ur.UR_RoleID', '=', 'RR.RoleID')
            ->where('RR.IsActive', 1)
            ->whereIn('RR.RoleName', ['Area Reports', 'Facility Administrator'])
            ->distinct()
            ->get();
    }

    /**
     * Get staff details for autocomplete
     *
     * @param string $termKey
     * @param string $term
     * @return Collection
     */
    public function getStaffDetailsAutocompleteList(string $termKey, string $term): Collection
    {
        // Map search keys to parameters
        $searchForeName = $searchNetLogin = $searchSurName = $searchStaffNumber = '';
        switch ($termKey) {
            case 'Forename':
                $searchForeName = $term;
                break;
            case 'Surname':
                $searchSurName = $term;
                break;
            case 'NetLogin':
                $searchNetLogin = $term;
                break;
            case 'StaffNumber':
                $searchStaffNumber = $term;
                break;
            default:
                break;
        }
        $actionType = 'search';
        $results = DB::select(
            'exec [dbo].[usp_GET_ScheduledPeopleStaffDetails] ?,?,?,?,?',
            [
                $searchStaffNumber,
                $searchForeName,
                $searchSurName,
                $searchNetLogin,
                $actionType
            ]
        );
        // Build autocomplete list (keyed by $termKey)
        $autocompleteList = collect();
        foreach ($results as $row) {
            if (isset($row->{$termKey})) {
                $autocompleteList->put($row->{$termKey}, $row->{$termKey});
            }
        }
        return $autocompleteList;
    }

    /**
     * Get user details from Active Directory
     *
     * @param string $netLogin
     *
     * @return stdClass
     */
    public function getUserDetailsFromActiveDirectory(string $netLogin): stdClass
    {
        try {
            $results = DB::select(
                'exec [dbo].[usp_get_UserDetailsFromDomain] ?',
                [$netLogin]
            );
            $result = $results[0] ?? new stdClass();
        } catch (\Exception $e) {
            $result = new stdClass();
        }

        return $result;
    }
    /**
     * Insert/allocate users record
     *
     * @param string $netLogin
     * @param User $user
     * @return array  result
     */
    public function insertAllocateUsers(string $netLogin, User $user): array
    {
            $userDetailsFromAD = $this->getUserDetailsFromActiveDirectory($netLogin);
            if (count((array) $userDetailsFromAD) == 0) {
                return [
                    'status' => 'error',
                    'message' => 'User details not found in Active Directory.'
                ];
            }

        // Check if user already exists
            $existingUser = User::where('UD_NetLogin', $netLogin)->first();
            if ($existingUser) {
                return [
                    'strstatus' => 'usererror',
                    'strreturnstring' => 'The Network Login you have entered is already a Allocate User.',
                    'strreturnstring2' => 'The Name of the user is ',
                    'strreturnstring3' => $existingUser->UD_DisplayName
                ];
            }

        // Compute staff number using checksum logic
            $staffNumber = $userDetailsFromAD->UserEmployeeNumber . $this->calculateStaffNumberLastChar($userDetailsFromAD->UserEmployeeNumber);

        DB::beginTransaction();
        try {
            $newUser = new User();
            $newUser->UD_EmpNumber = $userDetailsFromAD->UserEmployeeNumber;
            $newUser->UD_StaffNumber = $staffNumber;
            $newUser->UD_NetLogin = $netLogin;
            $newUser->UD_DisplayName = $userDetailsFromAD->UserFirstName . ' ' . $userDetailsFromAD->UserSurname;
            $newUser->UD_DisplayFirstName = $userDetailsFromAD->UserFirstName;
            $newUser->UD_DisplayLastName = $userDetailsFromAD->UserSurname;
            $newUser->UD_InternalEmail = $userDetailsFromAD->UserEmailAddress;
            $newUser->UD_StartDate = Carbon::now()->format('Y-m-d');
            $newUser->UD_Status = 1;
            $newUser->UD_CreatedBy = $user->UD_UserID;
            $newUser->save();

            DB::commit();
            return [
                'strstatus' => 'success',
                'strreturnstring' => 'Allocate user has been created successfully.',
                'strreturnstring2' => '',
                'strreturnstring3' => ''
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'strstatus' => 'error',
                'strreturnstring' => 'Due to some reason not able to complete this action',
                'strreturnstring2' => '',
                'strreturnstring3' => ''
            ];
        }
    }

    /*
    * Get area user permission history
    */
    public function getAreaUserPermissionHistory(int $areaId, int $userRoleId): array
    {
        $history = History::join('HistoryTypes as HY', 'HY.ID', '=', 'History.HistoryType')
            ->where('History.AttributeID', '=', $userRoleId)
            ->where('HY.HistoryType', '=', 'DivisionalAdmin')
            ->orderByRaw('History.[datetime] DESC')
            ->orderByRaw('History.HistoryID DESC')
            ->select('History.History')
            ->get();

        return $history ? $history->toArray() : [];
    }

    /**
     * Calculate the last character for staff number based on employee number
     * Refered db function  dbo.fn_StaffNumberLastChar
     *
     * @param string $empNumber
     * @return string
     */
    private function calculateStaffNumberLastChar(string $empNumber): string
    {
        $empStr = str_pad($empNumber, 6, '0', STR_PAD_LEFT);
        $weights = [7, 5, 3, 1, 11, 13];
        $total = 0;

        for ($i = 0; $i < 6; $i++) {
            $total += (int)$empStr[$i] * $weights[$i];
        }

        $chksum = ($total % 17) + 1;

        $chars = [
            1 => 'A',
            2 => 'B',
            3 => 'D',
            4 => 'E',
            5 => 'F',
            6 => 'H',
            7 => 'J',
            8 => 'K',
            9 => 'L',
            10 => 'N',
            11 => 'P',
            12 => 'R',
            13 => 'S',
            14 => 'T',
            15 => 'W',
            16 => 'X',
            17 => 'Y'
        ];

        return $chars[$chksum] ?? '';
    }
    /*
     * Get users for area allocation
     */
    public function getUsersForAreaAllocation(): array
    {
        $results = DB::select('exec [dbo].[usp_get_AllocateUsers] ?,?', ['', 'list']);

        return array_map(function ($user) {
            return [
                'UserId' => $user->UserID ?? '',
                'NetLogin' => $user->NetLogin ?? '',
                'DisplayName' => $user->DisplayName ?? '',
                'PreferredForename' => $user->Forename ?? '',
                'Surname' => $user->Surname ?? '',
                'InternalEmail' => $user->InternalEmail ?? '',
                'EmpNumber' => $user->EmpNumber ?? '',
                'StaffNumber' => $user->StaffNumber ?? ''
            ];
        }, $results);
    }

    /*
     * Get available roles for area allocation
     */
    public function getAvailableRoles(): array
    {
        $roles = RefRole::where('IsActive', 1)
            ->where('IsAdditional', 0)
            ->orderBy('isSequence')
            ->orderBy('RoleID')
            ->get(['RoleID', 'RoleName']);

        return $roles->toArray();
    }
    /**
     * Retrieve the list of teams the user belongs to.
     *
     * @param User $user
     * @return array
     */
    public function getUserTeamList(User $user)
    {
        $roleId = 0;
        $maxAllowedRoleId = 6;

        $results = DB::select('EXEC usp_get_GetUserTeamList ?, ?, ?', [$user->UD_UserID, $roleId, $maxAllowedRoleId]);

        return $results;
    }

    /**
     * Retrieve staff data for a given team and user type, with division context.
     *
     * @param SchedulingTeam $team
     * @param int|string $userType
     * @param int|string $divisionId
     * @return array
     */
    public function getStaffTeamData(SchedulingTeam $team, $userType, $divisionId, User $user)
    {
        // validation and early exit
        $teamId = $team->schedulingTeamId;
        if (!$teamId) {
            return ['error' => 'no_team', 'message' => 'Please apply the filter'];
        }

        $result = ['error' => 'exception', 'message' => 'Failed to load staff data'];
        try {
            // build query for links that are active for this team/type
            $commonQuery = function ($query) use ($teamId, $userType) {
                $query->where('TeamID', $teamId)
                    ->where('scheduledType', $userType)
                    ->whereDate('EndDate', '>', Carbon::now());
            };

            $isScheduled = (int)$userType === 1;

            $usersData = User::whereNotNull('UD_NetLogin')->whereHas('schedulingTeamLinks', function ($query) use ($commonQuery) {
                $commonQuery($query);
            })->with([
                'schedulingTeamLinks' => function ($query) use ($commonQuery) {
                    $commonQuery($query);
                },
                'schedulingTeamLinks.userRoles' => function ($role) use ($teamId, $isScheduled) {
                    if ($isScheduled) {
                        $role->where(function ($q) use ($teamId) {
                            $q->whereNull('UR_SchedulingTeamID')
                                ->orWhere('UR_SchedulingTeamID', $teamId);
                        });
                    } else {
                        $role->where('UR_SchedulingTeamID', $teamId);
                    }
                }
            ])->get();

            // load role definitions once
            $roles = RefRole::where('IsActive', 1)->get();
            $rolesById = $roles->keyBy('RoleID');

            // Pre load all permission statuses keyed by MainRoleID for non-scheduled staff
            $allPermissionStatuses = RolePermissionStatus::get()
                ->groupBy('MainRoleID')
                ->map(fn($group) => $group->keyBy('AdditionalRoleID'));

            // For scheduled staff, get permission statuses for SCHEDULED_PERSON role if it exists
            $scheduledPersonPermissionStatuses = collect();
            if ($isScheduled) {
                $scheduledPersonRoleId = $this->findRoleId($roles, RefRole::SCHEDULED_PERSON);
                if ($scheduledPersonRoleId) {
                    $scheduledPersonPermissionStatuses = $allPermissionStatuses->get($scheduledPersonRoleId, collect());
                }
            }

            $roleIds = [
                'shiftLeader' => $this->findRoleId($roles, RefRole::SHIFT_LEADER),
                'teamLeader' => $this->findRoleId($roles, RefRole::TEAM_LEADER),
                'facilityBooker' => $this->findRoleId($roles, RefRole::FACILITY_BOOKER),
                'editAllAllocations' => $this->findRoleId($roles, RefRole::EDIT_ALL_ALLOCATIONS),
                'editMasterDuties' => $this->findRoleId($roles, RefRole::EDIT_MASTER_DUTIES),
                'editRotaPatterns' => $this->findRoleId($roles, RefRole::EDIT_ROTA_PATTERNS),
                'editLeaveCredits' => $this->findRoleId($roles, RefRole::EDIT_LEAVE_CREDITS),
                'movePersonBetweenTeams' => $this->findRoleId($roles, RefRole::MOVE_PERSON_BETWEEN_TEAMS),
                'createNewFreelancer' => $this->findRoleId($roles, RefRole::CREATE_NEW_FREELANCER),
                'createNewStaff' => $this->findRoleId($roles, RefRole::CREATE_NEW_STAFF),
                'stv' => $this->findRoleId($roles, RefRole::SCHEDULING_TEAM_VIEWER),
            ];

            $mainRoleNames = [
                RefRole::SYSTEM_ADMIN,
                RefRole::AREA_ADMIN,
                RefRole::SCHEDULING_TEAM_ADMIN,
                RefRole::SCHEDULER,
                RefRole::SCHEDULING_TEAM_VIEWER,
                RefRole::SCHEDULED_PERSON,
            ];

            $staffList = $this->buildStaffList(
                $usersData,
                $roles,
                $rolesById,
                $allPermissionStatuses,
                $scheduledPersonPermissionStatuses,
                $roleIds,
                $mainRoleNames,
                $isScheduled
            );

            if ($staffList->isEmpty()) {
                $result = ['error' => 'no_data', 'message' => 'No data for selecting team'];
            } else {
                $additionalRoles = $this->buildAdditionalRolesMetadata((int)$userType, $roles);
                $mainRoles = $this->buildMainRoles((int)$userType, $roles);

                $policy = new AllocateUserPolicy();
                $canManage = $policy->canManageStaffTeam($user, $teamId) ? 1 : 0;
                $permissions = ['canmodify' => $canManage, 'candelete' => $canManage];

                $hideSchedulingTeamOption = (!$user->isSystemAdmin && $divisionId == 0 && (int)$userType === 0) ? 1 : 0;
                $currentNetLogin = strtolower($user->UD_NetLogin ?? '');

                $result = [
                    'staff' => $staffList->toArray(),
                    'mainRoles' => $mainRoles,
                    'additionalRoles' => $additionalRoles,
                    'schedulingTeamId' => $teamId,
                    'permissions' => $permissions,
                    'hideSchedulingTeamOption' => $hideSchedulingTeamOption,
                    'currentNetLogin' => $currentNetLogin,
                    'usertype' => (int)$userType,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('getStaffTeamData failed', ['teamId' => $teamId, 'error' => $e->getMessage()]);
        }

        return $result;
    }

    /**
     * Find the ID for a role by its role name from the active role collection.
     */
    private function findRoleId(Collection $roles, string $roleName): ?int
    {
        return $roles->where('RoleName', $roleName)->first()->RoleID ?? null;
    }

    /**
     * Identify the main role ID from a user's role set using known main role names.
     */
    private function findMainRoleId(Collection $userRoles, Collection $rolesById, array $mainRoleNames): ?int
    {
        foreach ($userRoles as $ur) {
            $roleMeta = $rolesById->get($ur->UR_RoleID);
            if ($roleMeta && in_array($roleMeta->RoleName, $mainRoleNames, true)) {
                return $ur->UR_RoleID;
            }
        }

        return null;
    }

    /**
     * Build additional role metadata for the front-end based on requested user type.
     */
    private function buildAdditionalRolesMetadata(int $userType, Collection $roles): array
    {
        $additionalRoles = [];
        foreach (UserRole::getPermission($userType) as $role) {
            $roleMeta = $roles->where('RoleName', $role['RoleName'])->first();
            if ($roleMeta) {
                $additionalRoles[] = ['RoleID' => $roleMeta->RoleID, 'RoleName' => $roleMeta->RoleName];
            }
        }
        return $additionalRoles;
    }

    /**
     * Determine the permission status for scheduled staff based on role and team leadership state.
     */
    private function computeScheduledPermissionStatus($role, $link, Collection $scheduledPersonPermissionStatuses, array $roleIds): string
    {
        $status = $scheduledPersonPermissionStatuses->has($role->RoleID)
            ? $scheduledPersonPermissionStatuses->get($role->RoleID)->PermissionKey
            : RolePermissionStatus::NA;

        $hasShiftLeader = $roleIds['shiftLeader'] && $link->userRoles->contains('UR_RoleID', $roleIds['shiftLeader']);
        $hasTeamLeader = $roleIds['teamLeader'] && $link->userRoles->contains('UR_RoleID', $roleIds['teamLeader']);

        if ($roleIds['facilityBooker'] && $role->RoleID === $roleIds['facilityBooker'] && !$hasShiftLeader && !$hasTeamLeader) {
            return RolePermissionStatus::NA;
        }
        if ($roleIds['editAllAllocations'] && $role->RoleID === $roleIds['editAllAllocations']) {
            return $hasTeamLeader ? RolePermissionStatus::MANDATORY : RolePermissionStatus::NA;
        }
        if ($roleIds['editMasterDuties'] && $role->RoleID === $roleIds['editMasterDuties']) {
            return $hasTeamLeader ? RolePermissionStatus::OPTIONAL : RolePermissionStatus::NA;
        }
        if ($roleIds['editRotaPatterns'] && $role->RoleID === $roleIds['editRotaPatterns']) {
            return $hasTeamLeader ? RolePermissionStatus::OPTIONAL : RolePermissionStatus::NA;
        }
        if ($roleIds['editLeaveCredits'] && $role->RoleID === $roleIds['editLeaveCredits']) {
            return $hasTeamLeader ? RolePermissionStatus::OPTIONAL : RolePermissionStatus::NA;
        }
        if ($roleIds['movePersonBetweenTeams'] && $role->RoleID === $roleIds['movePersonBetweenTeams']) {
            return $hasTeamLeader ? RolePermissionStatus::OPTIONAL : RolePermissionStatus::NA;
        }
        if ($roleIds['createNewFreelancer'] && $role->RoleID === $roleIds['createNewFreelancer']) {
            return $hasTeamLeader ? RolePermissionStatus::OPTIONAL : RolePermissionStatus::NA;
        }
        if ($roleIds['createNewStaff'] && $role->RoleID === $roleIds['createNewStaff']) {
            return $hasTeamLeader ? RolePermissionStatus::OPTIONAL : RolePermissionStatus::NA;
        }

        return $status;
    }

    /**
     * Determine the permission status for non-scheduled staff and apply STV-specific rules.
     */
    private function computeNonScheduledPermissionStatus($role, ?int $mainRoleId, Collection $permissionStatuses, $link, ?int $stvRoleId, ?int $shiftLeaderRoleId, ?int $facilityBookerRoleId): string
    {
        $isStvUser = $mainRoleId && $stvRoleId && (int)$mainRoleId === (int)$stvRoleId;
        $hasShiftLeader = $shiftLeaderRoleId && $link->userRoles->contains('UR_RoleID', $shiftLeaderRoleId);

        $status = $permissionStatuses->has($role->RoleID)
            ? $permissionStatuses->get($role->RoleID)->PermissionKey
            : RolePermissionStatus::NA;

        if ($isStvUser && $facilityBookerRoleId && $role->RoleID === $facilityBookerRoleId && !$hasShiftLeader) {
            $status = RolePermissionStatus::NA;
        }

        return $status;
    }

    private function buildStaffList(Collection $usersData, Collection $roles, Collection $rolesById, Collection $allPermissionStatuses, Collection $scheduledPersonPermissionStatuses, array $roleIds, array $mainRoleNames, bool $isScheduled): Collection
    {
        $staffList = collect();

        foreach ($usersData as $userData) {
            $entry = [
                'UserID' => $userData->UD_UserID,
                'NetLogin' => $userData->UD_NetLogin ?? '',
                'DisplayName' => $userData->UD_DisplayName,
                'userDisplayName' => $userData->UD_DisplayName,
                'PreferredForename' => $userData->UD_DisplayFirstName,
                'Surname' => $userData->UD_DisplayLastName,
                'InternalEmail' => $userData->UD_InternalEmail,
                'EmpNumber' => $userData->UD_EmpNumber ?? '',
                'StaffNumber' => $userData->UD_StaffNumber ?? '',
                'rota' => 0,
                'NonAdditionalRoleID' => 0,
                'isDefault' => 0,
                'scheduledType' => null,
                'IsHomeTeam' => 0,
            ];

            foreach ($userData->schedulingTeamLinks as $link) {
                $entry['isDefault'] = $link->isDefault;
                $entry['scheduledType'] = $link->scheduledType;
                $entry['rota'] = $link->rota;
                $entry['IsHomeTeam'] = $link->IsHomeTeam;

                $mainRoleId = $this->findMainRoleId($link->userRoles, $rolesById, $mainRoleNames);
                if ($mainRoleId) {
                    $entry['NonAdditionalRoleID'] = $mainRoleId;
                }

                $permissionStatuses = collect();
                if (!$isScheduled && $mainRoleId) {
                    $permissionStatuses = $allPermissionStatuses->get($mainRoleId, collect());
                }

                foreach ($roles as $role) {
                    $entry[$role->RoleName] = $link->userRoles->contains('UR_RoleID', $role->RoleID) ? 1 : 0;

                    if (in_array($role->RoleName, $mainRoleNames, true)) {
                        continue;
                    }

                    $status = $isScheduled
                        ? $this->computeScheduledPermissionStatus($role, $link, $scheduledPersonPermissionStatuses, $roleIds)
                        : $this->computeNonScheduledPermissionStatus(
                            $role,
                            $mainRoleId,
                            $permissionStatuses,
                            $link,
                            $roleIds['stv'],
                            $roleIds['shiftLeader'],
                            $roleIds['facilityBooker']
                        );

                    $entry[$role->RoleName . '_PermissionStatus'] = $status;
                }
            }

            $staffList->push($entry);
        }

        return $staffList;
    }

    private function buildMainRoles(int $userType, Collection $roles): array
    {
        if ($userType === 0) {
            return $roles->where('IsActive', 1)
                ->where('IsAdditional', 0)
                ->whereIn('RoleName', [RefRole::SCHEDULING_TEAM_ADMIN, RefRole::SCHEDULING_TEAM_VIEWER, RefRole::SCHEDULER])
                ->select(['RoleID', 'RoleName'])
                ->toArray();
        }

        return [
            ['RoleID' => 0, 'RoleName' => 'Show'],
            ['RoleID' => 1, 'RoleName' => 'Hide'],
            ['RoleID' => 2, 'RoleName' => 'Leave'],
        ];
    }

    /**
     * Persist changes to a user's permissions via stored procedure.
     *
     * @param array $data
     * @return array Status
     */
    public function setUsersPermissions(array $data, User $user)
    {
        $teamid = (int)($data['teamID'] ?? 0);
        $userid = (int)($data['userID'] ?? 0);
        $roleid = (int)($data['roleID'] ?? 0);
        $setparam = (int)($data['setparam'] ?? 0);
        $action = $data['action'] ?? '';
        $schedulepersonId = (int)($data['schedulepersonId'] ?? 0);
        $teamsname = $data['teamsname'] ?? '';
        $currentUserId = $user->UD_UserID;
        $currentUserName = $user->UD_DisplayName ?? '';
        $moduleName = 'NonScheduledTeamStaff';

        try {
            if ($action === 'rolepermission' && $schedulepersonId > 0) {
                $scheduledpersonDetails = DB::select('EXEC usp_get_NonScheduledpersondetailsbyID ?, ?', [$teamid, $schedulepersonId]);
                $oldRoleId = $scheduledpersonDetails[0]->RoleID ?? 0;
            }

            $result = DB::select(
                "EXEC usp_mod_setUsersPermissions {$userid}, {$teamid}, {$roleid}, ?, {$currentUserId}, {$setparam}, ?, ?, {$schedulepersonId}",
                [$action, $moduleName, $currentUserName]
            );

            $status = $result[0]->strstatus ?? 'error';

            if ($action === 'rolepermission' && $status === 'success') {
                $this->syncAdditionalPermissionsByMainRole($userid, $teamid, $roleid);
            }

            // If additional permission is removed, need to check if related facility booker or team leader permissions need to be removed
            if ($action === 'additionalpermission' && $setparam === 0 && $status === 'success') {
                $this->removeFacilityBookerIfShiftLeaderRemoved($userid, $teamid, $roleid);
                $this->removePermissionsIfTeamLeaderRemoved($userid, $teamid, $roleid);
            }

            // If Team Leader is being granted, auto-grant Edit All Allocations
            if ($action === 'additionalpermission' && $setparam === 1 && $status === 'success') {
                $teamLeaderRoleId = (int)(RefRole::where('RoleName', RefRole::TEAM_LEADER)->value('RoleID') ?? 0);
                if ($roleid === $teamLeaderRoleId) {
                    $editAllRoleId = (int)(RefRole::where('RoleName', RefRole::EDIT_ALL_ALLOCATIONS)->value('RoleID') ?? 0);
                    if ($editAllRoleId) {
                        $this->grantPermission($userid, $teamid, $editAllRoleId, $userid);
                    }
                }
            }

            if ($action === 'rolepermission' && isset($oldRoleId) && $oldRoleId != $roleid && $status === 'success') {
                $updatedRoleName = RefRole::where('RoleID', $roleid)->value('RoleName') ?? '';
                $oldRoleName = RefRole::where('RoleID', $oldRoleId)->value('RoleName') ?? '';
                $userFullname = User::where('UD_UserID', $userid)->value('UD_DisplayName') ?? '';

                if ($oldRoleName && $updatedRoleName && $teamsname) {
                    try {
                        $systemAdminEmails = $this->getSystemAdminEmails();
                        $divisionAdminEmails = $this->getDivisionAdminEmails($teamid);
                        $requesterName = $user->UD_DisplayName ?? 'Unknown';
                        if (!empty($systemAdminEmails)) {
                            Mail::to($systemAdminEmails)
                                ->cc($divisionAdminEmails)
                                ->send(new RoleChangeEmail($userFullname, $oldRoleName, $updatedRoleName, $teamsname, $requesterName));
                        } else {
                            \Log::warning('No system admin emails found - email not sent');
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to send role change email', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'user' => $userFullname,
                            'team' => $teamsname
                        ]);
                    }
                }
            }

            return ['strstatus' => $status];
        } catch (\Exception $e) {
            \Log::error('setUsersPermissions failed', [
                'userid' => $userid,
                'teamid' => $teamid,
                'action' => $action,
                'error'  => $e->getMessage()
            ]);
            return ['strstatus' => 'error'];
        }
    }

    /**
     * Get system admin email addresses
     *
     * @return array
     */
    private function getSystemAdminEmails(): array
    {
        try {
            return User::whereHas('userRoles', function ($q) {
                $q->join('REF_Roles', function ($join) {
                    $join->on('REF_Roles.RoleID', '=', 'UserRoles.UR_RoleID');
                });
                $q->where('REF_Roles.RoleName', RefRole::SYSTEM_ADMIN);
            })->pluck('UD_InternalEmail')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Error fetching system admin emails', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get division admin email addresses for a team
     *
     * @param int $teamid
     * @return array
     */
    private function getDivisionAdminEmails(int $teamid): array
    {
        try {
            $team = SchedulingTeam::find($teamid);

            if (!$team || !$team->divisionid) {
                return [];
            }

            return $team->divisionAdmins()
                ->get()
                ->map(fn($user) => $user->UD_InternalEmail ?: $user->UD_ExternalEmail)
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Error fetching division admin emails', ['teamid' => $teamid, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Remove a staff member from a team using stored procedure.
     *
     * @param array $data
     * @return array Status and view message.
     */
    public function removeStaff(array $data)
    {
        $schedulepersonid = (int)($data['schedulepersonid'] ?? 0);
        $scheduledteamid = (int)($data['scheduledteamid'] ?? 0);
        $currentUserId = Auth::user()->UD_UserID ?? 0;
        $moduleName = 'NonScheduledTeamStaff';

        $result = DB::select(
            "EXEC usp_del_nonScheduledTeamSatff {$schedulepersonid}, {$scheduledteamid}, {$currentUserId}, ?",
            [$moduleName]
        );

        return [
            'status' => $result[0]->strstatus ?? 'error',
            'view' => $result[0]->strreturnstring ?? 'Error removing staff'
        ];
    }

    /**
     * Add a non-scheduled staff member to a team using stored procedure.
     *
     * @param array $data
     * @param User $currentUser
     * @return array Status and view message.
     */
    public function addNonScheduledStaff(array $data, User $currentUser)
    {
        $netLogin = $data['netLogin'] ?? '';
        $teamid = (int)($data['teamid'] ?? 0);
        $currentUserId = $currentUser->UD_UserID ?? 0;
        $currentUserName = $currentUser->UD_DisplayName ?? '';
        $moduleName = 'NonScheduledTeamStaff';

        $result = DB::select(
            "EXEC usp_mod_setNonScheduledTeamStaff ?, {$teamid}, {$currentUserId}, ?, ?",
            [$netLogin, $moduleName, $currentUserName]
        );

        $status = $result[0]->strstatus ?? 'error';
        $message = $result[0]->strreturnstring ?? 'Error adding staff';

        if ($status === 'usererror') {
            $userData = User::where('UD_NetLogin', $netLogin)->first();
            $teamName = DB::table('schedulingTeams')->where('schedulingTeamId', $teamid)->value('schedulingTeamName') ?? '';

            return [
                'status' => 'usererror',
                'message' => $message,
                'userName' => $userData->UD_DisplayName ?? '',
                'teamName' => $teamName
            ];
        }

        if ($status === 'success') {
            $userData = User::where('UD_NetLogin', $netLogin)->first();

            if ($userData) {
                DB::select(
                    "EXEC usp_mod_setUsersPermissions {$userData->UD_UserID}, {$teamid}, 6, 'rolepermission', {$currentUserId}, 1, ?, ?, 0",
                    [$moduleName, $currentUserName]
                );
            }
        }

        return [
            'status' => $status,
            'view' => $message
        ];
    }

    /**
     * Set default team for a user using stored procedure.
     *
     * @param array $data
     * @param User $currentUser
     * @return array Status message.
     */
    public function setDefaultTeam(array $data, User $currentUser)
    {
        $schedulepersonid = (int)($data['schedulepersonid'] ?? 0);
        $defaultValue = (int)($data['defaultValue'] ?? 0);
        $scheduledteamid = (int)($data['scheduledteamid'] ?? 0);
        $currentUserId = $currentUser->UD_UserID;

        $result = DB::select(
            "EXEC usp_mod_setDefaultTeamRecord {$schedulepersonid}, {$defaultValue}, {$scheduledteamid}, {$currentUserId}"
        );

        return [
            'strstatus' => $result[0]->strstatus ?? 'error',
            'strreturnstring' => $result[0]->strreturnstring ?? 'Error setting default team'
        ];
    }

    /**
     * Get staff history for scheduled and non-scheduled staff
     *
     * @param int $teamId
     * @param int $userId
     * @return array
     */
    public function getStaffHistory(int $teamId, int $userId): array
    {
        $moduleName = 'NonScheduledTeamStaff';

        $result = DB::select(
            "EXEC usp_GET_AdditionalPermissionHistoryData ?, ?, ?",
            [$moduleName, $teamId, $userId]
        );

        return array_map(function ($item) {
            return ['History' => $item->History ?? ''];
        }, $result);
    }
    /*
     * Allocate user to area
     */
    public function allocateUserToArea(int $userId, int $areaId, int $roleId, User $currentUser): array
    {
        try {
            // Get role name first
            $role = RefRole::where('RoleID', $roleId)->first();
            $roleName = $role ? $role->RoleName : '';

            // Check if user already has the SAME primary role (Area Admin or Area Viewer) in this area
            // Additional roles (Facility Administrator, Area Reports) should not block allocation
            $existingRole = UserRole::where('UR_UserID', $userId)
                ->where('UR_DivisionId', $areaId)
                ->where('UR_RoleID', $roleId)
                ->first();

            if ($existingRole) {
                return [
                    'status' => 'error',
                    'message' => 'This user already has a role assigned to this area.'
                ];
            }

            // Insert new user role using UserRole Model
            $userRole = new UserRole();
            $userRole->UR_UserID = $userId;
            $userRole->UR_DivisionId = $areaId;
            $userRole->UR_RoleID = $roleId;
            $userRole->UR_StartDate = Carbon::now()->toDateString();
            $userRole->UR_EndDate = self::ROLE_END_DATE;
            $userRole->UR_CreatedBy = $currentUser->UD_UserID;
            $userRole->UR_CreatedDate = Carbon::now();
            $inserted = $userRole->save();

            if ($inserted) {
                $this->updateAreaPermissionHistory($userRole->UR_UserRoleID, $areaId, 'allocate', $roleName, $currentUser);

                if ($roleName === RefRole::AREA_ADMIN) {
                    $this->grantFacilityAdministratorForArea($userId, $areaId, $currentUser->UD_UserID);
                }
            }

            return $inserted
                ? ['status' => 'success', 'message' => 'User allocated to area successfully.']
                : ['status' => 'error',   'message' => 'Failed to allocate user to area.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => self::ERROR_PREFIX . $e->getMessage()];
        }
    }

    /*
     * Get user details by ID
     */
    public function getUserDetailsById(int $userId)
    {
        return User::find($userId);
    }

    /**
     * Remove user from area
     *
     * @param int $userId
     * @param int $areaId
     * @return array
     */
    public function removeUserFromArea(int $userId, int $areaId): array
    {
        try {
            $userRole = UserRole::where('UR_UserID', $userId)
                ->where('UR_DivisionId', $areaId)
                ->first();

            $userRoleId = $userRole ? $userRole->UR_UserRoleID : 0;

            $deleted = UserRole::where('UR_UserID', $userId)
                ->where('UR_DivisionId', $areaId)
                ->delete();

            if ($deleted) {
                if ($userRoleId > 0) {
                    $this->updateAreaPermissionHistory($userRoleId, $areaId, 'remove', '', \Auth::user());
                }

                return [
                    'status' => 'success',
                    'message' => 'User removed from area successfully.'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to remove user from area.'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => self::ERROR_PREFIX . $e->getMessage()
            ];
        }
    }

    /*
     * Update user role in area
     */
    public function updateUserRole(int $userId, int $areaId, int $roleId, User $currentUser): array
    {
        try {
            $userRole = UserRole::where('UR_UserID', $userId)
                ->where('UR_DivisionId', $areaId)
                ->first();

            if ($userRole) {
                $oldRole = RefRole::where('RoleID', $userRole->UR_RoleID)->first();
                $oldRoleName = $oldRole ? $oldRole->RoleName : '';

                $newRole = RefRole::where('RoleID', $roleId)->first();
                $newRoleName = $newRole ? $newRole->RoleName : '';

                $userRole->UR_RoleID = $roleId;
                $userRole->UR_UpdatedBy = $currentUser->UD_UserID;
                $userRole->UR_UpdatedDate = Carbon::now();
                $updated = $userRole->save();

                if ($updated) {
                    $historyMessage = "Role changed from {$oldRoleName} to {$newRoleName}";
                    $this->updateAreaPermissionHistory($userRole->UR_UserRoleID, $areaId, 'update_role', $historyMessage, $currentUser);

                    if ($newRoleName === RefRole::AREA_ADMIN) {
                        $this->grantFacilityAdministratorForArea($userId, $areaId, $currentUser->UD_UserID);
                    } elseif ($oldRoleName === RefRole::AREA_ADMIN) {
                        $this->revokeFacilityAdministratorForArea($userId, $areaId);
                    }

                    return [
                        'status' => 'success',
                        'message' => 'User role updated successfully.',
                        'users' => $this->getAreaUsersByAreaId($areaId),
                        'additionalRoles' => $this->getAreaUsersAdditionalRoles($areaId)
                    ];
                }
            }

            return [
                'status' => 'error',
                'message' => 'Failed to update user role.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => self::ERROR_PREFIX . $e->getMessage()
            ];
        }
    }

    /*
     * Get available area roles (Area Admin, Area Viewer) for the role dropdown in the table
     * Note: Facility Administrator and Area Reports are additional roles, not primary roles
     */
    public function getAreaRoles(): Collection
    {
        return RefRole::whereIn('RoleName', ['Area Admin', 'Area Viewer'])
            ->where('IsActive', 1)
            ->select('RoleID', 'RoleName')
            ->orderBy('RoleName', 'asc')
            ->get();
    }

    /*
     * Get additional area roles (Facility Administrator, Area Reports) for toggle functionality
     */
    public function getAdditionalAreaRoles(): Collection
    {
        return RefRole::whereIn('RoleName', ['Facility Administrator', 'Area Reports'])
            ->where('IsActive', 1)
            ->select('RoleID', 'RoleName')
            ->orderBy('RoleName', 'asc')
            ->get();
    }

    /*
     * Get user details for area allocation form with JSON data
     */
    public function getUserDetailsForAreaAllocationHtml(int $userId, int $areaId, bool $isSysAdmin): array
    {
        $userDetails = $this->getUserDetailsById($userId);

        if (!$userDetails) {
            return [];
        }

        // Fetch area-related roles directly from REF_Roles table
        $rolesQuery = RefRole::where('IsActive', 1)
            ->whereIn('RoleName', ['Area Viewer', 'Area Admin'])
            ->orderBy('isSequence')
            ->orderBy('RoleID')
            ->select('RoleID', 'RoleName')
            ->get();

        $roles = [];
        foreach ($rolesQuery as $r) {
            $rName = trim(strval($r->RoleName ?? ''));
            $rId = $r->RoleID ?? null;
            if ($rName === '') {
                continue;
            }

            // Include Area Admin only for sysadmins
            if (strtolower($rName) === 'area admin' && !$isSysAdmin) {
                continue;
            }
            $roles[] = ['RoleID' => $rId, 'RoleName' => $rName];
        }

        // Check if user already has a role in this area
        $existingMapping = UserRole::where('UR_UserID', $userId)
            ->where('UR_DivisionId', $areaId)
            ->first();

        return [
            'userDetails' => [
                'UD_NetLogin' => $userDetails->UD_NetLogin ?? '',
                'UD_DisplayName' => $userDetails->UD_DisplayName ?? '',
                'UD_DisplayFirstName' => $userDetails->UD_DisplayFirstName ?? '',
                'UD_DisplayLastName' => $userDetails->UD_DisplayLastName ?? '',
                'UD_InternalEmail' => $userDetails->UD_InternalEmail ?? '',
                'UD_EmpNumber' => $userDetails->UD_EmpNumber ?? ''
            ],
            'roles' => $roles,
            'existingMapping' => $existingMapping ? [
                'UR_RoleID' => $existingMapping->UR_RoleID
            ] : null,
            'userId' => $userId
        ];
    }

    /*
     * Toggle additional role (Facility Administrator / Area Reports) for an area user
    */
    public function toggleAreaAdditionalRole(int $userRoleId, int $roleId, int $userId, int $areaId, string $action, User $currentUser): array
    {
        try {
            $loggedInUser = $currentUser->UD_UserID;

            $role = RefRole::where('RoleID', $roleId)->first();
            $roleName = $role ? $role->RoleName : '';

            if ($action == 'enable') {
                // Insert new user role for the additional role
                $userRole = new UserRole();
                $userRole->UR_UserID = $userId;
                $userRole->UR_DivisionId = $areaId;
                $userRole->UR_RoleID = $roleId;
                $userRole->UR_StartDate = Carbon::now()->toDateString();
                $userRole->UR_EndDate = self::ROLE_END_DATE;
                $userRole->UR_CreatedBy = $loggedInUser;
                $userRole->UR_CreatedDate = Carbon::now();
                $userRole->save();

                $this->updateAreaPermissionHistory($userRoleId, $areaId, 'enable', $roleName, $currentUser);
            } else {
                // Delete the user role for the additional role
                UserRole::where('UR_UserID', $userId)
                    ->where('UR_RoleID', $roleId)
                    ->where('UR_DivisionId', $areaId)
                    ->delete();

                $this->updateAreaPermissionHistory($userRoleId, $areaId, 'disable', $roleName, $currentUser);
            }

            // Return refreshed area users and additional roles
            $areaUsers = $this->getAreaUsersByAreaId($areaId);
            $additionalRoles = $this->getAreaUsersAdditionalRoles($areaId);

            $additionalRolesByUser = [];
            foreach ($additionalRoles as $role) {
                $additionalRolesByUser[$role->UserID][$role->RoleName] = $role->RoleID;
            }

            return [
                'status' => 'success',
                'message' => $action == 'enable' ? 'Role enabled successfully.' : 'Role disabled successfully.',
                'users' => $areaUsers,
                'additionalRoles' => $additionalRolesByUser
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => self::ERROR_PREFIX . $e->getMessage()
            ];
        }
    }

    /**
     * Grant Facility Administrator additional role for an area user
     */
    private function grantFacilityAdministratorForArea(int $userId, int $areaId, int $createdBy): void
    {
        $facilityAdminRoleId = RefRole::where('RoleName', 'Facility Administrator')->where('IsActive', 1)->value('RoleID');
        if (!$facilityAdminRoleId) {
            return;
        }

        $exists = UserRole::where('UR_UserID', $userId)
            ->where('UR_DivisionId', $areaId)
            ->where('UR_RoleID', $facilityAdminRoleId)
            ->whereDate('UR_EndDate', '>=', Carbon::now())
            ->exists();

        if ($exists) {
            return;
        }

        $row = new UserRole();
        $row->UR_UserID      = $userId;
        $row->UR_DivisionId  = $areaId;
        $row->UR_RoleID      = $facilityAdminRoleId;
        $row->UR_StartDate   = Carbon::now()->toDateString();
        $row->UR_EndDate     = self::ROLE_END_DATE;
        $row->UR_CreatedBy   = $createdBy;
        $row->UR_CreatedDate = Carbon::now();
        $row->save();
    }

    /**
     * Revoke Facility Administrator additional role for an area user.
     */
    private function revokeFacilityAdministratorForArea(int $userId, int $areaId): void
    {
        $facilityAdminRoleId = RefRole::where('RoleName', 'Facility Administrator')->where('IsActive', 1)->value('RoleID');
        if (!$facilityAdminRoleId) {
            return;
        }

        UserRole::where('UR_UserID', $userId)
            ->where('UR_DivisionId', $areaId)
            ->where('UR_RoleID', $facilityAdminRoleId)
            ->update(['UR_EndDate' => Carbon::now()->subDay()->toDateString()]);
    }

    /*
     * Record area permission history
     * This method records history entries for area permission changes
     */
    private function updateAreaPermissionHistory(int $userRoleId, int $areaId, string $action, string $roleName = '', User $currentUser = null): void
    {
        if (!$currentUser) {
            $currentUser = Auth::user();
        }

        if (!$currentUser) {
            return;
        }

        $area = Division::where('DivisionID', $areaId)->first();
        $areaName = $area ? $area->DivisionName : '';

        $currentUserName = $currentUser->UD_DisplayName ?? 'Unknown';
        $currentDate = Carbon::now()->format('d-m-Y');

        $historyTypeId = DB::table('HistoryTypes')
            ->where('HistoryType', 'DivisionalAdmin')
            ->value('ID');

        if (!$historyTypeId) {
            return;
        }

        $historyMessage = '';
        switch ($action) {
            case 'allocate':
                $historyMessage = "User allocated to {$areaName} as {$roleName} by {$currentUserName} on {$currentDate}";
                break;
            case 'update_role':
                $historyMessage = $roleName;
                break;
            case 'enable':
                $historyMessage = "{$roleName} enabled by {$currentUserName} on {$currentDate}";
                break;
            case 'disable':
                $historyMessage = "{$roleName} disabled by {$currentUserName} on {$currentDate}";
                break;
            case 'remove':
                $historyMessage = "User removed from area by {$currentUserName} on {$currentDate}";
                break;
            default:
                $historyMessage = "Updated by {$currentUserName} on {$currentDate}";
        }

        DB::table('History')->insert([
            'HistoryType' => $historyTypeId,
            'UserID' => $currentUser->UD_UserID,
            'History' => $historyMessage,
            'datetime' => Carbon::now(),
            'AttributeID' => $userRoleId
        ]);
    }

    /*
     * If an enabling role (Shift Leader or Team Leader) is removed and no other enabling role
     * remains, expire Facility Booker for that user/team.
     */
    private function removeFacilityBookerIfShiftLeaderRemoved(int $userid, int $teamid, int $removedRoleId): void
    {
        $shiftLeaderRoleId = (int)(RefRole::where('RoleName', RefRole::SHIFT_LEADER)->value('RoleID') ?? 0);
        $teamLeaderRoleId  = (int)(RefRole::where('RoleName', RefRole::TEAM_LEADER)->value('RoleID') ?? 0);

        if ($removedRoleId !== $shiftLeaderRoleId && $removedRoleId !== $teamLeaderRoleId) {
            return;
        }

        $facilityBookerRoleId = (int)(RefRole::where('RoleName', RefRole::FACILITY_BOOKER)->value('RoleID') ?? 0);
        if (!$facilityBookerRoleId) {
            return;
        }

        $otherEnablingRoleId = ($removedRoleId === $shiftLeaderRoleId) ? $teamLeaderRoleId : $shiftLeaderRoleId;

        $otherStillActive = $otherEnablingRoleId && UserRole::where('UR_UserID', $userid)
            ->where('UR_SchedulingTeamID', $teamid)
            ->where('UR_RoleID', $otherEnablingRoleId)
            ->whereDate('UR_EndDate', '>=', Carbon::now())
            ->exists();

        if ($otherStillActive) {
            return;
        }

        UserRole::where('UR_UserID', $userid)
            ->where('UR_SchedulingTeamID', $teamid)
            ->where('UR_RoleID', $facilityBookerRoleId)
            ->whereDate('UR_EndDate', '>=', Carbon::now())
            ->update(['UR_EndDate' => Carbon::now()->subDay()->toDateString()]);
    }

    /**
     * Grant a permission (insert active UserRole row) if not already active.
     */
    private function grantPermission(int $userid, int $teamid, int $roleId, int $createdBy = 0): void
    {
        $exists = UserRole::where('UR_UserID', $userid)
            ->where('UR_SchedulingTeamID', $teamid)
            ->where('UR_RoleID', $roleId)
            ->whereDate('UR_EndDate', '>=', Carbon::now())
            ->exists();

        if ($exists) {
            return;
        }

        $row = new UserRole();
        $row->UR_UserID           = $userid;
        $row->UR_SchedulingTeamID = $teamid;
        $row->UR_RoleID           = $roleId;
        $row->UR_StartDate        = Carbon::now()->toDateString();
        $row->UR_EndDate          = self::ROLE_END_DATE;
        $row->UR_CreatedBy        = $createdBy;
        $row->UR_CreatedDate      = Carbon::now();
        $row->save();
    }

    /**
     * If Team Leader is removed, expire several dependent permissions for that user/team.
     */
    private function removePermissionsIfTeamLeaderRemoved(int $userid, int $teamid, int $removedRoleId): void
    {
        $teamLeaderRoleId = (int)(RefRole::where('RoleName', RefRole::TEAM_LEADER)->value('RoleID') ?? 0);
        if (!$teamLeaderRoleId || $removedRoleId !== $teamLeaderRoleId) {
            return;
        }

        $yesterday = Carbon::now()->subDay()->toDateString();

        $roleNamesToExpire = [
            RefRole::EDIT_ALL_ALLOCATIONS,
            RefRole::EDIT_MASTER_DUTIES,
            RefRole::EDIT_ROTA_PATTERNS,
            RefRole::EDIT_LEAVE_CREDITS,
            RefRole::MOVE_PERSON_BETWEEN_TEAMS,
            RefRole::CREATE_NEW_FREELANCER,
            RefRole::CREATE_NEW_STAFF
        ];

        $roleIdsToExpire = RefRole::whereIn('RoleName', $roleNamesToExpire)->pluck('RoleID')->all();
        if (empty($roleIdsToExpire)) {
            return;
        }

        UserRole::where('UR_UserID', $userid)
            ->where('UR_SchedulingTeamID', $teamid)
            ->whereIn('UR_RoleID', $roleIdsToExpire)
            ->whereDate('UR_EndDate', '>=', Carbon::now())
            ->update(['UR_EndDate' => $yesterday]);
    }

    /*
     * Sync additional permissions based on the main role assigned
     * This method is called after a main role change to ensure that additional permissions are correctly assigned or expired
    */
    private function syncAdditionalPermissionsByMainRole(int $userid, int $teamid, int $mainRoleId): void
    {
        $allAdditionalNames = array_column(UserRole::getPermission(0), 'RoleName');
        $allAdditionalIds   = RefRole::whereIn('RoleName', $allAdditionalNames)->pluck('RoleID')->all();

        if (empty($allAdditionalIds)) {
            return;
        }

        $yesterday = Carbon::now()->subDay()->toDateString();

        DB::beginTransaction();
        try {
            // On any main role change, expire all active optional permissions
            UserRole::where('UR_UserID', $userid)
                ->where('UR_SchedulingTeamID', $teamid)
                ->whereIn('UR_RoleID', $allAdditionalIds)
                ->whereDate('UR_EndDate', '>=', Carbon::now())
                ->update(['UR_EndDate' => $yesterday]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('syncAdditionalPermissionsByMainRole failed', [
                'userid'     => $userid,
                'teamid'     => $teamid,
                'mainRoleId' => $mainRoleId,
                'error'      => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /*
     * Get permission descriptions matrix for Permission Descriptions tab
     */
    public function getPermissionDescriptions(): array
    {
        // Get main roles in custom order
        $mainRoleOrder = [
            RefRole::SCHEDULING_TEAM_VIEWER,
            RefRole::SCHEDULED_PERSON,
            RefRole::SCHEDULER,
            RefRole::SCHEDULING_TEAM_ADMIN,
            RefRole::AREA_ADMIN,
            RefRole::SYSTEM_ADMIN,
        ];

        $mainRoles = RefRole::where('IsActive', 1)
            ->where('IsAdditional', 0)
            ->whereIn('RoleName', $mainRoleOrder)
            ->get()
            ->sortBy(function ($role) use ($mainRoleOrder) {
                return array_search($role->RoleName, $mainRoleOrder);
            })
            ->values();

        // Get additional roles using the same function as staff team data
        $additionalRoleNames = array_column(UserRole::getPermission(1), 'RoleName');

        $additionalRoles = RefRole::where('IsActive', 1)
            ->where('IsAdditional', 1)
            ->whereIn('RoleName', $additionalRoleNames)
            ->get()
            ->sortBy(function ($role) use ($additionalRoleNames) {
                return array_search($role->RoleName, $additionalRoleNames);
            })
            ->values();

        // Build permission matrix from RolePermissionStatus
        $permissionMatrix = [];
        foreach ($mainRoles as $mainRole) {
            $permissionMatrix[$mainRole->RoleID] = [];

            $permissions = RolePermissionStatus::where('MainRoleID', $mainRole->RoleID)
                ->where('IsActive', 1)
                ->get()
                ->keyBy('AdditionalRoleID');

            foreach ($additionalRoles as $additionalRole) {
                $status = $permissions->get($additionalRole->RoleID);
                $permissionKey = $status ? $status->PermissionKey : RolePermissionStatus::NA;
                $permissionDescription = $status ? $status->PermissionDescription : null;

                // Special case for Scheduled Person + Edit All Allocations
                if (
                    $mainRole->RoleName === RefRole::SCHEDULED_PERSON &&
                    $additionalRole->RoleName === RefRole::EDIT_ALL_ALLOCATIONS
                ) {
                    $permissionKey = RolePermissionStatus::TICK_CONDITIONAL;
                }

                $permissionMatrix[$mainRole->RoleID][$additionalRole->RoleID] = [
                    'key' => $permissionKey,
                    'description' => $permissionDescription,
                ];
            }
        }

        return [
            'mainRoles' => $mainRoles,
            'additionalRoles' => $additionalRoles,
            'permissionMatrix' => $permissionMatrix
        ];
    }
}
