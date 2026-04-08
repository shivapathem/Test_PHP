<?php

namespace App\Repositories\Admin;

use App\Models\User\ScheduledPersonTeamLink;
use App\Models\User;
use App\Models\Scheduling\Allocation;
use App\Models\Scheduling\AllocationScheduledPerson;
use App\Repositories\Contracts\Admin\ScheduledPeopleRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Policies\Setup\ScheduledPeoplePolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Repository for managing Scheduled People operations
 *
 * @package App\Repositories
 */
class ScheduledPeopleRepository implements ScheduledPeopleRepositoryInterface
{
    private User $userModel;

    public function __construct(User $userModel = null)
    {
        $this->userModel = $userModel ?? new User();
    }

    /**
     * Convert query rows to associative array
     *
     * @param array $rows
     * @return array
     */
    private function rowsToArray(array $rows): array
    {
        return array_map(static fn($r) => (array)$r, $rows);
    }

    /**
     * Convert first row to associative array
     *
     * @param array $rows
     * @return array|null
     */
    private function firstRowToArray(array $rows): ?array
    {
        if (empty($rows)) {
            return null;
        }
        return (array)$rows[0];
    }

    /**
     * Get scheduling teams for a user
     *
     * @param int $userId
     * @param string $actionType
     * @param int $pageId
     * @param int $schedulePersonId
     * @return array
     */
    public function getSchedulingTeams(int $userId, string $actionType, int $pageId, int $schedulePersonId): array
    {
        $rows = DB::select(
            'exec [dbo].[usp_get_GetUserTeamListByUserPermission] ?,?,?,?',
            [$userId, $actionType, $pageId, $schedulePersonId]
        );

        // Check for 'Move Person Between Teams' role using userRoles() and expand teams
        $user = $this->userModel->find($userId);
        if (!$user) {
            Log::warning('User not found in getSchedulingTeams', ['userId' => $userId]);
        }
        $hasMoveRole = $user && $user->userRoles()
            ->whereHas('role', function ($q) {
                $q->where('RoleName', 'Move Person Between Teams')
                  ->where('IsActive', 1);
            })
            ->exists();

        $canCreateStaff = $user ? $user->can('createStaff', 'scheduled-people') : false;
        $shouldExpandTeams = $hasMoveRole || $canCreateStaff;

        if ($shouldExpandTeams) {
            // 1. Teams in Areas where user has Scheduler OR STA permissions (refactored to model)
            $user = $user ?: $this->userModel->find($userId);
            if (!$user) {
                Log::warning('User not found in getSchedulingTeams second lookup', ['userId' => $userId]);
            }
            $areaTeams = $user ? $user->userAccessibleTeams()
                ->select('schedulingTeamId as TeamID', 'schedulingTeamName as TeamName')
                ->get()
                ->toArray() : [];

            // 2. Hardcoded special teams
            $specialNames = ['Freelancers', 'Archive', 'Other BBC', 'Maternity/Paternity', 'Apprentices'];
            $specialTeams = DB::table('schedulingTeams')
                ->whereIn('schedulingTeamName', $specialNames)
                ->orWhere('schedulingTeamName', 'LIKE', '%Freelancer%')
                ->orWhere('schedulingTeamName', 'LIKE', '%Archive%')
                ->orWhere('schedulingTeamName', 'LIKE', '%BBC%')
                ->orWhere('schedulingTeamName', 'LIKE', '%Maternity%')
                ->orWhere('schedulingTeamName', 'LIKE', '%Apprentice%')
                ->where('isActive', 1)
                ->select('schedulingTeamId as TeamID', 'schedulingTeamName as TeamName')
                ->get()
                ->map(fn($team) => (array)$team)
                ->toArray();

            // Merge unique extras (avoid dups with standard SP)
            $extraTeams = array_merge($areaTeams, $specialTeams);
            $existingIds = array_column($rows, 'TeamID');
            $extras = array_filter($extraTeams, fn($team) => !in_array($team['TeamID'], $existingIds));

            foreach ($extras as $extra) {
                $rows[] = (object) [
                    'TeamID' => $extra['TeamID'],
                    'TeamName' => $extra['TeamName']
                ];
            }

            // Sort by TeamName
            usort($rows, fn($a, $b) => strcmp($a->TeamName ?? '', $b->TeamName ?? ''));
        }

        return $this->rowsToArray($rows);
    }

    /**
     * Create or update a scheduled person
     *
     * @param int $spTeamId
     * @param string $displayName
     * @param int $schedulingTeamId
     * @param string $homeTeamStartDate
     * @param string $homeTeamEndDate
     * @param string $homeTeamSortCode
     * @param string $homeTeamBackColour
     * @param string $homeTeamFontColour
     * @param string $homeTeamAdminNotes
     * @param string $homeTeamFWANotes
     * @param string $actionType
     * @param int $scheduledPersonId
     * @param string $additionalTeamArray
     * @param int $auditUserId
     * @param string $displayFirstName
     * @param string $displayLastName
     * @param int $isDefaultBGColour
     * @param int $isAdditionalLeave
     * @return array
     */
    public function createSchedulePerson(int $spTeamId, string $displayName, int $schedulingTeamId, string $homeTeamStartDate, string $homeTeamEndDate, string $homeTeamSortCode, string $homeTeamBackColour, string $homeTeamFontColour, string $homeTeamAdminNotes, string $homeTeamFWANotes, string $actionType, int $scheduledPersonId, string $additionalTeamArray, int $auditUserId, string $displayFirstName, string $displayLastName, int $isDefaultBGColour, int $isAdditionalLeave): array
    {
        // Dates: normalize to ISO (SP has DATEFORMAT ymd and DATE-typed params)
        $homeTeamStartDate = $this->normalizeDate($homeTeamStartDate, date('Y-m-d'));
        $homeTeamEndDate   = $this->normalizeInfinityDate($homeTeamEndDate);

        // Ensure AdditionalTeamArray is a JSON string in the schema the SP expects
        if (is_array($additionalTeamArray)) {
            // Already an array → encode it
            $additionalTeamArray = json_encode($additionalTeamArray, JSON_UNESCAPED_SLASHES);
        } else {
            // Not an array: might be JSON string, null, or empty
            $decoded = json_decode((string)$additionalTeamArray, true);
            $additionalTeamArray = is_array($decoded)
                ? json_encode($decoded, JSON_UNESCAPED_SLASHES)
                : '[]';
        }

        $rows = app()->make(\App\Repositories\Contracts\Admin\CreateScheduledPersonRepositoryInterface::class)->createOrUpdate(
            $spTeamId,
            $displayName,
            $schedulingTeamId,
            $homeTeamStartDate,
            $homeTeamEndDate,
            $homeTeamSortCode,
            $homeTeamBackColour,
            $homeTeamFontColour,
            $homeTeamAdminNotes,
            $homeTeamFWANotes,
            $actionType,
            $scheduledPersonId,
            $additionalTeamArray,
            $auditUserId,
            $displayFirstName,
            $displayLastName,
            (bool)$isDefaultBGColour,
            (bool)$isAdditionalLeave
        );
        return $rows;
    }

    /**
     * Get staff details configuration for search
     *
     * @param string|null $searchForeName
     * @param string|null $searchNetLogin
     * @param string|null $searchSurName
     * @param string|null $searchStaffNumber
     * @return array
     */
    public function getStaffDetailsConfig(?string $searchStaffNumber, ?string $searchForeName, ?string $searchSurName, ?string $searchNetLogin): array
    {
        $returnString = '';
        $rows = DB::select(
            'exec [dbo].[usp_GET_ScheduledPeopleStaffDetails] ?,?,?,?,?',
            [$searchStaffNumber, $searchForeName, $searchSurName, $searchNetLogin, $returnString]
        );
        return $this->rowsToArray($rows);
    }

    /**
     * Attach staff details configuration
     *
     * @param int $scheduledPersonId
     * @param int $staffId
     * @param string $actionType
     * @param string $status
     * @param string $returnString
     * @param int $userID
     * @param int $oldScheduledPersonID
     * @return array
     */
    public function attachStaffDetailsConfig(int $scheduledPersonId, int $staffId, string $actionType, string $status, string $returnString, int $userID, int $oldScheduledPersonID): array
    {
        $rows = DB::select(
            'exec [dbo].[usp_insup_StaffLinkAddUser] ?,?,?,?,?,?,?',
            [$scheduledPersonId, $actionType, $staffId, $oldScheduledPersonID, $userID, $status, $returnString]
        );
        return $this->firstRowToArray($rows) ?? [];
    }

    /**
     * Get schedule person details/teams
     *
     * @param int $schedulePersonId
     * @return array
     */
    public function getSchedulepersondetails(int $schedulePersonId): array
    {
        $q1 = ScheduledPersonTeamLink::with(['schedulingTeam', 'scheduledPerson.userConfig'])
            ->where('ScheduledPersonID', $schedulePersonId)
            ->where('scheduledType', 1)
            ->whereIn('IsHomeTeam', [ScheduledPersonTeamLink::ADDITIONAL_TEAM, ScheduledPersonTeamLink::FUTURE_HOME_ADDITIONAL_TEAM])
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Additional Teams (IsHomeTeam = 0): Only show active entries based on date
                    $q->where('IsHomeTeam', ScheduledPersonTeamLink::ADDITIONAL_TEAM)
                        ->whereDate(DB::raw("ISNULL(EndDate, GETDATE())"), ">=", now()->toDateString());
                })->orWhere(function ($q) {
                    $q->where('IsHomeTeam', ScheduledPersonTeamLink::FUTURE_HOME_ADDITIONAL_TEAM)
                        ->where('IsActive', 1);
                });
            })
            ->get();

        $latestEndDate = ScheduledPersonTeamLink::where('ScheduledPersonID', $schedulePersonId)
            ->where('IsHomeTeam', 1)
            ->where('scheduledType', 1)
            ->whereDate('EndDate', '>=', today())
            ->max('EndDate');

        $q2 = ScheduledPersonTeamLink::with(['schedulingTeam', 'scheduledPerson.userConfig'])
            ->where('ScheduledPersonID', $schedulePersonId)
            ->where('scheduledType', 1)
            ->where('IsHomeTeam', 1)
            ->where('EndDate', $latestEndDate)
            ->get();

        // Combine results like UNION
        $rows = $q2->merge($q1)->sortByDesc('IsHomeTeam');

        $data = $rows->map(function ($row) {

            $sp = $row->scheduledPerson;
            $team = $row->schedulingTeam;

            return [
                'ScheduledPersonID' => $sp->UD_UserID,
                'DisplayName'       => $sp->UD_DisplayName,
                'UserID'            => $sp->UD_UserID,
                'StaffDetailsID'    => $sp->UD_TeampayStaffID,
                'InternalEmail'     => $sp->UD_InternalEmail,
                'AdminNotes'        => $sp->UD_AdminNotes,
                'FWANotes'          => $sp->UD_FWANotes,
                'ScheduledPersonID2' => $row->ScheduledPersonID,
                'TeamID'            => $row->TeamID,
                'TeamName'          => $team->schedulingTeamName,
                'IsHomeTeam'        => $row->IsHomeTeam,
                'SortCode'          => $row->SortCode,
                'CreatedBy'         => $row->CreatedBy,
                'CreatedDate'       => $row->CreatedDate
                    ? \Carbon\Carbon::parse($row->CreatedDate)->format('d-m-Y')
                    : null,
                'StartDate'         => $row->StartDate
                    ? \Carbon\Carbon::parse($row->StartDate)->format('d-m-Y')
                    : null,
                'EndDate'           => $row->EndDate
                    ? \Carbon\Carbon::parse($row->EndDate)->format('d-m-Y')
                    : null,
                'LastUpdatedBy'     => $row->LastUpdatedBy,
                'LastUpdatedDate'   => $row->LastUpdatedDate
                    ? \Carbon\Carbon::parse($row->LastUpdatedDate)->format('d/m/Y H:i:s')
                    : null,
                'BackgroundColour'  => $row->BackgroundColour,
                'fontcolour'        => $row->fontcolour,
                'isDefault'         => $row->isDefault,
                'IsAvailable'       => $row->IsAvailable,
                'Forename'          => $sp->UD_DisplayFirstName,
                'PreferredForename' => $sp->UD_PreferredFirstName,
                'NetLogin'          => $sp->UD_NetLogin,
                'Surname'           => $sp->UD_DisplayLastName,
                'JobTitle'          => $sp->userConfig?->UC_JobTitle,
                'Title'             => '',
                'StaffNumber'       => $sp->UD_StaffNumber,
                'AveDayLen'         => 0,
                'ShiftBreak'        => 0,
                'DisplayFirstName'  => $sp->UD_DisplayFirstName,
                'DisplayLastName'   => $sp->UD_DisplayLastName,
                'IsDefaultBGColour' => $row->IsDefaultBGColour,
                'IsAdditionalLeave' => $sp->UD_IsEligibleForAdditionalLeave,
                'SPTeamID'          => $row->SPTeamID,
                'DisplayInViewScreen' => $row->DisplayInViewScreen
            ];
        })->values();
        return $data->toArray();
    }

    /**
     * Get person details (alias for getSchedulepersondetails)
     *
     * @param int $schedulePersonId
     * @return array
     */
    public function getPersonDetails(int $schedulePersonId): array
    {
        return $this->getSchedulepersondetails($schedulePersonId);
    }

    /**
     * Validate add home teams start date
     *
     * @param int $schedulePersonId
     * @return array|null
     */
    public function validateAddHomeTeamsStartDate(int $schedulePersonId): ?array
    {
        $record = ScheduledPersonTeamLink::where('ScheduledPersonID', $schedulePersonId)
            ->where('IsHomeTeam', 1)
            ->where('scheduledType', 1)
            ->orderBy('StartDate', 'asc')
            ->first();

        if (!$record) {
            return null;
        }

        return [
            'StartDateHomeTeamFirst' => Carbon::parse($record->StartDate)->format('d-m-Y')
        ];
    }

    /**
     * Validate add team between any existing home teams duration
     *
     * @param int $schedulePersonId
     * @return array
     */
    public function validateAddTeamBetweenAnyExistingHomeTeamsDuration(int $schedulePersonId): array
    {
        $records = ScheduledPersonTeamLink::where('ScheduledPersonID', $schedulePersonId)
            ->where('scheduledType', 1)
            ->where('IsHomeTeam', 1)
            ->get(['TeamID', 'StartDate', 'EndDate']);

        return $records->map(function ($record) {
            return [
                'TeamId' => $record->TeamID,
                'StartDateHomeTeam' => Carbon::parse($record->StartDate)->format('d-m-Y'),
                'EndDateHomeTeam' => Carbon::parse($record->EndDate)->format('d-m-Y'),
            ];
        })->filter()->values()->toArray();
    }

    /**
     * Validate add teams end date
     *
     * @param int $ddlteamsid
     * @param int $schedulepersonid
     * @param string $addteamenddate
     * @param string $addteamstartdate
     * @param int $addteamsid
     * @return array|null
     */
    public function validateAddTeamsEndDate(int $ddlteamsid, int $schedulepersonid, string $addteamenddate, string $addteamstartdate, int $addteamsid): ?array
    {
        $resultresponse = null;
        try {
            $addteamenddate = Carbon::parse($addteamenddate)->format('Y-m-d');
            if ($addteamenddate !== '9999-01-01') {
                $result = Allocation::query()
                    ->join('AllocationsDuties as AD', 'Allocations.AL_AllocationsID', '=', 'AD.AD_AllocationsID')
                    ->join('AllocationsScheduledPersons as ASP', 'AD.AD_AllocationsDutyID', '=', 'ASP.ASP_AllocationsDutyID')
                    ->where('Allocations.AL_SchedulingTeamID', $ddlteamsid)
                    ->where('ASP.ASP_DutyTeamId', $addteamsid)
                    ->where('ASP.ASP_SchedulingPersonID', $schedulepersonid)
                    ->where('AD.AD_DutyDate', '>', Carbon::parse($addteamenddate))
                    ->whereNotIn('AD.AD_DutyType', [7, 8, 9])
                    ->selectRaw('COUNT(Allocations.AL_AllocationsID) AS AllocateCount, MAX(Allocations.AL_WeekNumber) AS MaxWeekNumber')
                    ->first();

                if ($result && $result->AllocateCount > 0) {
                    $resultresponse = (array) $result;
                }
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return $resultresponse;
    }

    /**
     * Validate add team between any existing additional team
     * Checks for date conflicts with all existing additional teams (not just past ones)
     *
     * @param int $schedulepersonid
     * @param int $teamid
     * @param string $addteamstartdate
     * @param string $addteamenddate
     * @return array
     */
    public function validateAddTeamBetweenAnyExistingAdditioanlTeam(int $schedulepersonid, int $teamid, string $addteamstartdate, string $addteamenddate): array
    {
        // Parse input dates
        try {
            $inputStart = Carbon::parse($addteamstartdate)->format('Y-m-d');
            $inputEnd = Carbon::parse($addteamenddate)->format('Y-m-d');
        } catch (\Exception $e) {
            return [];
        }

        // Get all additional teams for this person (including active and future, not just past)
        $records = ScheduledPersonTeamLink::where('TeamID', $teamid)
            ->where('ScheduledPersonID', $schedulepersonid)
            ->whereIn('IsHomeTeam', [ScheduledPersonTeamLink::ADDITIONAL_TEAM, ScheduledPersonTeamLink::FUTURE_HOME_ADDITIONAL_TEAM]) // Additional teams and Future Home teams
            ->where('IsActive', 1)
            ->where('scheduledType', 1)
            ->get(['IsHomeTeam', 'StartDate', 'EndDate', 'IsActive', 'scheduledType', 'ScheduledPersonID', 'TeamID']);

        $conflicts = [];

        foreach ($records as $record) {
            $existingStart = Carbon::parse($record->StartDate)->format('Y-m-d');
            $existingEnd = $record->EndDate ? Carbon::parse($record->EndDate)->format('Y-m-d') : '9999-01-01';

            // Check for date overlap: (newStart <= existingEnd) AND (newEnd >= existingStart)
            if ($inputStart <= $existingEnd && $inputEnd >= $existingStart) {
                $conflicts[] = [
                    'IsHomeTeam' => $record->IsHomeTeam,
                    'StartDate' => Carbon::parse($record->StartDate)->format('d-m-Y'),
                    'EndDate' => $record->EndDate ? Carbon::parse($record->EndDate)->format('d-m-Y') : '01-01-9999',
                    'IsActive' => $record->IsActive,
                    'scheduledType' => $record->scheduledType,
                    'ScheduledPersonID' => $record->ScheduledPersonID,
                    'TeamID' => $record->TeamID,
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Change display name for a scheduled person
     *
     * @param int $schedulepersonid
     * @param string $newName
     * @return void
     */
    public function changeDisplayName(int $schedulepersonid, string $newName): void
    {
        $user = User::find($schedulepersonid);
        if ($user) {
            $user->UD_DisplayName = $newName;
            $user->save();
        }
    }

    /**
     * Check scheduled type
     *
     * @param int $schedulepersonid
     * @return int
     */
    public function checkScheduledType(int $schedulepersonid): int
    {
        $today = Carbon::today()->format('Y-m-d');

        $record = ScheduledPersonTeamLink::where('ScheduledPersonID', $schedulepersonid)
            ->where(function ($query) use ($today) {
                $query->whereNull('EndDate')
                    ->orWhere('EndDate', '>=', $today);
            })
            ->where('scheduledType', 1)
            ->first();

        return $record ? 1 : 0;
    }

    /**
     * Delete home team
     * After the stored procedure executes, this method also performs a soft-delete
     * on any Future Home Team (IsHomeTeam = 2) entries associated with the same team,
     * but ONLY if there are no outstanding duties in those teams.
     *
     * @param int $homeTeamId
     * @param int $personId
     * @param int $deleteFromRota
     * @param int $auditUserId
     * @return array
     */
    public function deleteHomeTeam(int $homeTeamId, int $personId, int $deleteFromRota, int $auditUserId): array
    {
        // First, check if there are outstanding duties in the Future Home Team (IsHomeTeam = 2) that has the same TeamID as the Home Team being deleted
        $hasOutstandingDuties = $this->checkFutureHomeTeamHasDuties($personId, $homeTeamId);

        // If there are NO outstanding duties, perform soft-delete on Future Home Team entries
        if (!$hasOutstandingDuties) {
            $this->softDeleteFutureHomeTeamEntries($personId, $homeTeamId, $auditUserId);
        }

        // Execute the stored procedure
        $rows = DB::select(
            'exec [dbo].[usp_Mod_DeleteScheduledPersonHomeTeam] ?,?,?,?',
            [$personId, $homeTeamId, $deleteFromRota, $auditUserId]
        );

        return $this->rowsToArray($rows);
    }

    /**
     * Check if the Future Home Team (IsHomeTeam = 2) has outstanding duties
     * An outstanding duty is one that occurs after today's date
     *
     * @param int $personId
     * @param int $homeTeamId
     * @return bool True if duties exist, False otherwise
     */
    public function checkFutureHomeTeamHasDuties(int $personId, int $homeTeamId): bool
    {
        try {
            // Get the Future Home Team entry for this person and team
            $futureHomeTeam = ScheduledPersonTeamLink::where('ScheduledPersonID', $personId)
                ->where('TeamID', $homeTeamId)
                ->where('IsHomeTeam', ScheduledPersonTeamLink::FUTURE_HOME_ADDITIONAL_TEAM) // IsHomeTeam = 2
                ->where('scheduledType', 1)
                ->where('IsActive', 1)
                ->first();

            if (!$futureHomeTeam) {
                return false; // No Future Home Team exists, no duties to check
            }

            // Check if there are any allocations/duties for this person in this team that have a duty date >= today (outstanding/future duties)
            //$today = Carbon::today()->format('Y-m-d');

            $start = \Carbon\Carbon::parse($futureHomeTeam->StartDate)->format('Y-m-d');
            $end   = $futureHomeTeam->EndDate ? \Carbon\Carbon::parse($futureHomeTeam->EndDate)->format('Y-m-d') : '9999-01-01';

            // Check AllocationsScheduledPersons for duties in this team using Laravel ORM
            return AllocationScheduledPerson::where('ASP_SchedulingPersonID', $personId)
                ->where('ASP_DutyTeamId', $futureHomeTeam->TeamID)
                ->whereHas('duty', function ($q) use ($start, $end) {
                    $q->whereBetween('AD_DutyDate', [$start, $end])
                        ->whereNotIn('AD_DutyType', [7, 8, 9]);
                })->exists();
        } catch (\Exception $e) {
            Log::error('Error checking Future Home Team duties', [
                'personId' => $personId,
                'homeTeamId' => $homeTeamId,
                'error' => $e->getMessage(),
            ]);
            return true;
        }
    }

    /**
     * Soft-delete (set IsActive = 0) Future Home Team entries
     * This makes the automatic Additional Team entry inactive so it's not displayed in UI
     *
     * @param int $personId
     * @param int $homeTeamId
     * @param int $auditUserId
     */
    private function softDeleteFutureHomeTeamEntries(int $personId, int $homeTeamId, int $auditUserId): void
    {
        try {
            $affected = ScheduledPersonTeamLink::where('ScheduledPersonID', $personId)
                ->where('TeamID', $homeTeamId)
                ->where('IsHomeTeam', ScheduledPersonTeamLink::FUTURE_HOME_ADDITIONAL_TEAM) // IsHomeTeam = 2
                ->where('scheduledType', 1)
                ->where('IsActive', 1)
                ->update([
                    'IsActive' => 0,
                    'LastUpdatedBy' => $auditUserId,
                    'LastUpdatedDate' => Carbon::now()->toDateTimeString(),
                ]);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            Log::error('Error soft-deleting Future Home Team entries', [
                'personId' => $personId,
                'homeTeamId' => $homeTeamId,
                'affectedRows' => $affected,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate if scheduled person home team has rota
     *
     * @param int $homeTeamId
     * @param int $scheduledPersonId
     * @return array
     */
    public function validateSchPersonHomeTeamHaveRota(int $homeTeamId, int $scheduledPersonId): array
    {
        $rows = DB::select(
            'exec [dbo].[usp_get_validateSchPersonHomeTeamHaveRota] ?,?',
            [$scheduledPersonId, $homeTeamId]
        );

        return $this->firstRowToArray($rows) ?? [];
    }

    /**
     * Get staff details by person ID
     * Returns staff details from UserDetails table only if a staff member is linked
     * A staff member is considered "linked" if they have a StaffNumber or NetLogin
     *
     * @param int $scheduledPersonId
     * @return array|null
     */
    public function getStaffDetailsByPersonId(int $scheduledPersonId): ?array
    {
        $user = User::find($scheduledPersonId);
        if (!$user) {
            Log::warning('Scheduled person not found in getStaffDetailsByPersonId', ['scheduledPersonId' => $scheduledPersonId]);
            return ['error' => 'Scheduled person not found'];
        }

        // Check if this scheduled person has a linked staff member.A staff is considered "linked" if they have a StaffNumber or NetLogin
        $staffNumber = $user->UD_StaffNumber ?? '';
        $netLogin = $user->UD_NetLogin ?? '';

        // If no StaffNumber and no NetLogin, then no staff is linked. Return null to indicate empty staff details
        if (empty($staffNumber) && empty($netLogin)) {
            return null;
        }

        return [
            'UD_UserID' => $user->UD_UserID,
            'StaffNumber' => $staffNumber,
            'NetLogin' => $netLogin,
            'Forename' => $user->UD_DisplayFirstName,
            'Surname' => $user->UD_DisplayLastName,
            'PreferredForename' => $user->UD_PreferredFirstName ?? '',
            'JobTitle' => $user->UD_JobTitle ?? '',
        ];
    }

    /**
     * Update staff details
     *
     * @param int $id
     * @param array $payload
     * @return int
     */
    public function updateStaffDetails(int $id, array $payload): int
    {
        // Map incoming fields -> DB columns (only update non-empty values) Only include fields that actually exist in the database
        $updateData = [];

        if (!empty($payload['FirstName'])) {
            $updateData['UD_DisplayFirstName'] = $payload['FirstName'];
        }
        if (!empty($payload['Surname'])) {
            $updateData['UD_DisplayLastName'] = $payload['Surname'];
        }
        if (!empty($payload['PreferredName'])) {
            $updateData['UD_PreferredFirstName'] = $payload['PreferredName'];
        }

        if (empty($updateData)) {
            return 0;
        }

        $user = User::find($id);

        if (!$user) {
            return 0;
        }

        return $user->update($updateData) ? 1 : 0;
    }

    /**
     * Normalize date string to ISO format
     *
     * @param string|null $d
     * @param string|null $fallback
     * @return string|null
     */
    private function normalizeDate(?string $d, ?string $fallback = null): ?string
    {
        $d = trim((string)$d);
        if ($d === '' || $d === 'null' || $d === 'NULL') {
            return $fallback;
        }
        $d = str_replace('/', '-', $d);

        try {
            return Carbon::parse($d)->format('Y-m-d');
        } catch (Exception $e) {
            return $fallback;
        }
    }

    /**
     * Normalize infinity date (9999-01-01)
     *
     * @param string|null $d
     * @return string
     */
    private function normalizeInfinityDate(?string $d): string
    {
        $d = trim((string)$d);
        if ($d === '' || $d === '01-01-9999' || $d === '9999-01-01') {
            return '9999-01-01';
        }
        return $this->normalizeDate($d, '9999-01-01');
    }

    public function getScheduledPeopleUserList(string $teamID, string $userName, int $pageid, int $excludeNoTeam): array
    {
        // Use the legacy stored procedure like the original PHP code
        // This matches the behavior in classScheduledPerson::getSearchScheduledPerson
        // Note: teamID and userID are passed as strings to match legacy PDO::PARAM_STR behavior
        try {
            $userId = Auth::user()?->UD_UserID ?? 0;
            $rows = DB::select(
                'exec [dbo].[usp_get_ScheduledPeopleDetails] ?,?,?,?,?',
                [
                    $userName,
                    $teamID,
                    '',  // actionType - empty for search
                    $userId,   // userID - actual logged in user
                    $excludeNoTeam
                ]
            );
            return $this->rowsToArray($rows);
        } catch (\Exception $e) {
            Log::error('getScheduledPeopleUserList error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return [];
        }
    }

    /**
     * Get contract history for a scheduled person
     * Uses the legacy stored procedure usp_get_ContractHistory
     *
     * @param int $schedulePersonId
     * @return array
     */
    public function getContractHistory(int $schedulePersonId): array
    {
        try {
            $rows = DB::select('exec [dbo].[usp_get_ContractHistory] ?', [$schedulePersonId]);
            return $this->rowsToArray($rows);
        } catch (\Exception $e) {
            Log::error('getContractHistory error: ' . $e->getMessage(), [
                'exception' => $e,
                'schedulePersonId' => $schedulePersonId
            ]);
            return [];
        }
    }

    /**
     * Get schedule team history for a scheduled person
     * Includes team-specific permissions for each record
     * Only returns active records (IsActive = 1)
     *
     * @param int $scheduledPersonId
     * @param int|null $userId The authenticated user ID (optional, for permission checking)
     * @return array
     */
    public function getScheduleTeamHistory(int $scheduledPersonId, ?int $userId = null): array
    {
        // Get all team links - include all for history (inactive records filtered in display)
        $teamLinks = ScheduledPersonTeamLink::where('ScheduledPersonID', $scheduledPersonId)
            ->where('scheduledType', 1)
            ->where(function ($query) {
                $query->where('IsActive', 1)
                    ->orWhere('IsHomeTeam', 1);
            })
            ->with(['team', 'lastUpdatedByUser', 'createdByUser'])
            ->orderBy('StartDate', 'desc')
            ->get();

        // Fallback to explicit auth user ID if not provided (CLI-safe)
        $currentUserId = $userId ?? (Auth::user()?->UD_UserID ?? 0);

        $user = $currentUserId ? User::find($currentUserId) : null;
        if ($user) {
            $user->getUserRoleDetail();  // Load userSetup with perms
        }

        // Legacy logic count home teams (only IsHomeTeam = 1)
        $homeTeamCounter = $teamLinks->where('IsHomeTeam', 1)->count();

        // DeletePermission from CURRENT home teams (today overlap)
        $today = Carbon::today();
        $policy = new ScheduledPeoplePolicy();

        $deletePermissionFromCurrent = 0;
        foreach ($teamLinks->where('IsHomeTeam', 1) as $potentialCurrent) {
            $start = Carbon::parse($potentialCurrent->StartDate);
            $end = $potentialCurrent->EndDate ? Carbon::parse($potentialCurrent->EndDate) : Carbon::create(9999, 1, 1);
            if ($today->gt($start) && $today->lte($end)) {
                if ($policy->hasTeamPermission($user, $potentialCurrent->TeamID, 'candelete')) {
                    $deletePermissionFromCurrent = 1;
                    break;
                }
            }
        }

        // Map each record with Policy-based permissions
        return $teamLinks->map(function ($link) use ($user, $homeTeamCounter, $deletePermissionFromCurrent, $policy) {
            $homeTeamDisplay = match ($link->IsHomeTeam) {
                1 => 'Home',
                2 => 'Future Home',
                default => 'Additional',
            };

            $isHomeTeam = $link->IsHomeTeam == 1;

            $endDate = $link->EndDate ? Carbon::parse($link->EndDate) : null;
            $isFarFuture = is_null($endDate) || $endDate->gte(Carbon::create(9999, 1, 1));

            // Legacy match: future && home && counter>1 && (own perm OR deletePermissionFromCurrent)
            $ownPolicyPerm = $user ? $policy->deleteHistory($user, $link) : false;
            $canDeleteTeam = $isFarFuture && $isHomeTeam && $homeTeamCounter > 1 && ($ownPolicyPerm || $deletePermissionFromCurrent);

            return [
                'HomeTeam' => $homeTeamDisplay,
                'IsHomeTeam' => $link->IsHomeTeam,
                'Startdate' => $link->StartDate ? Carbon::parse($link->StartDate)->format('d-m-Y') : null,
                'Enddate' => $link->EndDate ? Carbon::parse($link->EndDate)->format('d-m-Y') : null,
                'SortCode' => $link->SortCode,
                'LastUpdatedDate' => $link->LastUpdatedDate ? Carbon::parse($link->LastUpdatedDate)->format('d-m-Y H:i:s') : null,
                'ScheduleTeam' => $link->team->schedulingTeamName ?? '',
                'LastUpdatedBy' => $link->lastUpdatedByUser->UD_DisplayName ?? '',
                'CreatedBy' => $link->createdByUser->UD_DisplayName ?? '',
                'CreatedDate' => $link->CreatedDate ? Carbon::parse($link->CreatedDate)->format('d-m-Y H:i:s') : null,
                'IsAvailable' => ($link->IsAvailable == 1) ? 'Y' : 'N',
                'IsActive' => $link->IsActive,
                'TeamID' => $link->TeamID,
                'SPTeamID' => $link->SPTeamID,
                'ScheduledPersonID' => $link->ScheduledPersonID,
                'canDelete' => $canDeleteTeam ? 1 : 0
            ];
        })->values()->toArray();
    }

    /**
     * Send email notification to Scheduling Team about removed duties (moved from controller)
     * Only sends if duty <8 days away (today=0)
     * To: SchedulingTeams.Email, CC: deleting user
     * Groups by team, separates deleted/unallocated duties
     *
     * @param array $dutyIds AD_AllocationsDutyID array
     * @param int $personId
     * @param array $dutiesToDeleteIDs ASP_AllocationsSPID of "Doesn't Need Covering" duties
     * @param array $dutiesToUnallocateIDs ASP_AllocationsSPID moved to Unallocated
     * @param mixed $currentUser Auth::user()
     * @return void
     */
    public function notifySchedulingTeamOfRemovedDuties(array $dutyIds, int $personId, array $dutiesToDeleteIDs = [], array $dutiesToUnallocateIDs = [], $currentUser = null): void
    {
        try {
            $now = Carbon::now();
            $eightDaysFromNow = $now->copy()->addDays(8);

            // Get duties <8 days with team info (only future duties)
            $dutiesToNotify = DB::table('AllocationsScheduledPersons as ASP')
                ->join('AllocationsDuties as AD', 'ASP.ASP_AllocationsDutyID', '=', 'AD.AD_AllocationsDutyID')
                ->join('Allocations as AL', 'AD.AD_AllocationsID', '=', 'AL.AL_AllocationsID')
                ->join('SchedulingTeams as ST', 'AL.AL_SchedulingTeamID', '=', 'ST.schedulingTeamId')
                ->whereIn('AD.AD_AllocationsDutyID', $dutyIds)
                ->where('ASP.ASP_SchedulingPersonID', $personId)
                ->where('AD.AD_DutyDate', '>=', $now->toDateString())
                ->where('AD.AD_DutyDate', '<=', $eightDaysFromNow->toDateString())
                ->select(
                    'ASP.ASP_AllocationsSPID',
                    'AD.AD_AllocationsDutyID',
                    'AD.AD_DutyDate',
                    'AD.AD_DutyName',
                    'ST.schedulingTeamId',
                    'ST.schedulingTeamName',
                    'ST.Email as teamEmail'
                )
                ->get();

            if ($dutiesToNotify->isEmpty()) {
                return;
            }

            // Scheduled person display name
            $scheduledPerson = DB::table('UserDetails')
                ->where('UD_UserID', $personId)
                ->value('UD_DisplayName');

            if (!$scheduledPerson) {
                return;
            }

            // CC email from deleting user
            $ccEmail = null;
            if ($currentUser && isset($currentUser->email)) {
                $ccEmail = $currentUser->email ?: ($currentUser->UD_InternalEmail ?? null);
            }

            // Group duties by team, categorize deleted vs unallocated
            $teamsData = [];
            foreach ($dutiesToNotify as $duty) {
                $teamId = $duty->schedulingTeamId;
                if (!isset($teamsData[$teamId])) {
                    $teamsData[$teamId] = [
                        'teamName' => $duty->schedulingTeamName,
                        'teamEmail' => $duty->teamEmail,
                        'deleted' => [],
                        'unallocated' => []
                    ];
                }

                $dutyData = [
                    'date' => $duty->AD_DutyDate,
                    'name' => $duty->AD_DutyName
                ];

                // Categorize by ASP_AllocationsSPID (from controller)
                if (in_array($duty->ASP_AllocationsSPID, $dutiesToDeleteIDs)) {
                    $teamsData[$teamId]['deleted'][] = $dutyData;
                } elseif (in_array($duty->ASP_AllocationsSPID, $dutiesToUnallocateIDs)) {
                    $teamsData[$teamId]['unallocated'][] = $dutyData;
                }
            }

            // Send per-team email
            foreach ($teamsData as $teamId => $teamData) {
                if (
                    empty($teamData['teamEmail']) ||
                    (empty($teamData['deleted']) && empty($teamData['unallocated']))
                ) {
                    continue;
                }

                // Team admin preferred forename for greeting - GRACEFUL FALLBACK (no admin column crash)
                $adminForename = $teamData['teamName']; // Default fallback

                // Queue mailable
                $mail = new \App\Mail\DutiesRemovedFromScheduledPersonMail(
                    $scheduledPerson,
                    $teamData['teamName'],
                    $adminForename,
                    $teamData['deleted'],
                    $teamData['unallocated'],
                    $ccEmail
                );

                Mail::to($teamData['teamEmail'])
                    ->cc($ccEmail)
                    ->queue($mail);
            }
        } catch (\Exception $e) {
            Log::error('Error in notifySchedulingTeamOfRemovedDuties', [
                'personId' => $personId,
                'dutyIds' => $dutyIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get duties in Additional (Future Home) Teams during Home Team period
     * Returns duties that would be affected if Home Team is deleted
     *
     * @param int $homeTeamId The Home Team record ID (SPTeamID)
     * @param int $scheduledPersonId The scheduled person ID
     * @return array Array of duties with date, team, name, and times
     */
    /**
     * Handle Additional Team after duty removal (moved from controller)
     * - Convert Future Home → Additional if duties remain (IsHomeTeam: 2→0)
     * - Delete Additional Team record if no duties remain
     *
     * @param int $personId
     * @param int $homeTeamId
     * @return void
     */
    public function handleAdditionalTeamAfterDutyRemoval(int $personId, int $homeTeamId): void
    {
        try {
            // Get home team date range
            $homeTeam = DB::table('ScheduledPersonTeam_LINK')
                ->where('SPTeamID', $homeTeamId)
                ->where('ScheduledPersonID', $personId)
                ->where('IsHomeTeam', 1)
                ->select('StartDate', 'EndDate')
                ->first();

            if (!$homeTeam) {
                return;
            }

            // Find Additional/Future Home teams overlapping home team period
            $additionalTeams = DB::table('ScheduledPersonTeam_LINK')
                ->where('ScheduledPersonID', $personId)
                ->whereIn('IsHomeTeam', [0, 2]) // ADDITIONAL_TEAM=0, FUTURE_HOME=2
                ->where('StartDate', '<=', $homeTeam->EndDate)
                ->where('EndDate', '>=', $homeTeam->StartDate)
                ->get();

            foreach ($additionalTeams as $additionalTeam) {
                // Count remaining duties in this team during its period
                $dutyCount = DB::table('AllocationsScheduledPersons as ASP')
                    ->join('AllocationsDuties as AD', 'ASP.ASP_AllocationsDutyID', '=', 'AD.AD_AllocationsDutyID')
                    ->where('ASP.ASP_SchedulingPersonID', $personId)
                    ->where('ASP.ASP_DutyTeamId', $additionalTeam->TeamID)
                    ->whereDate('AD.AD_DutyDate', '>=', $additionalTeam->StartDate)
                    ->whereDate('AD.AD_DutyDate', '<=', $additionalTeam->EndDate)
                    ->count();

                if ($dutyCount === 0) {
                    // No duties: delete Additional Team record
                    DB::table('ScheduledPersonTeam_LINK')
                        ->where('SPTeamID', $additionalTeam->SPTeamID)
                        ->update(['IsActive' => 0,'EndDate' => Carbon::yesterday()->toDateString()]);
                } else {
                    // Duties remain: convert Future Home → normal Additional
                    if ($additionalTeam->IsHomeTeam == 2) {
                        DB::table('ScheduledPersonTeam_LINK')
                            ->where('SPTeamID', $additionalTeam->SPTeamID)
                            ->update(['IsHomeTeam' => 0]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error handling Additional Team after duty removal', [
                'personId' => $personId,
                'homeTeamId' => $homeTeamId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getConflictingDutiesForHomeTeamDeletion(int $homeTeamId, int $scheduledPersonId): array
    {
        // Get all Additional Teams (Future Home Teams) for the same scheduled person
        // These are the teams that might have conflicting duties
        $additionalTeams = ScheduledPersonTeamLink::where('ScheduledPersonID', $scheduledPersonId)
            ->where('IsHomeTeam', '<>', 1) // Get ADDITIONAL_TEAM (0) and FUTURE_HOME_ADDITIONAL_TEAM (2)
            ->get();

        // For each additional team, find duties during the home team period
        $allConflictingDuties = [];

        foreach ($additionalTeams as $additionalTeam) {
            $duties = DB::table('AllocationsDuties as AD')
                ->join('AllocationsScheduledPersons as ASP', 'AD.AD_AllocationsDutyID', '=', 'ASP.ASP_AllocationsDutyID')
                ->join('Allocations as AL', 'AD.AD_AllocationsID', '=', 'AL.AL_AllocationsID')
                ->join('SchedulingTeams as T', 'AL.AL_SchedulingTeamID', '=', 'T.schedulingTeamId')
                ->select(
                    'AD.AD_AllocationsDutyID',
                    'ASP.ASP_DutyTeamId',
                    'AD.AD_DutyDate',
                    'T.schedulingTeamName as TeamName',
                    'AD.AD_DutyName',
                    'AD.AD_StartTimeSec',
                    'AD.AD_EndTimeSec',
                    'AD.AD_Duration',
                    'AD.AD_DutyBreakTime',
                    'AD.AD_IsNeedCovering'
                )
                ->where('ASP.ASP_SchedulingPersonID', $scheduledPersonId)
                ->where('ASP.ASP_DutyTeamId', $additionalTeam->TeamID)
                    ->whereDate('AD.AD_DutyDate', '>=', $additionalTeam->StartDate)
                    ->whereDate('AD.AD_DutyDate', '<=', $additionalTeam->EndDate)
                ->whereNotIn('AD.AD_DutyStatus', [7, 8, 9]) // Filter out special duty types
                ->orderBy('AD.AD_DutyDate')
                ->orderBy('AD.AD_StartTimeSec')
                ->get();

            foreach ($duties as $duty) {
                $duty->TeamID = $additionalTeam->TeamID;
                $duty->SPTeamID = $additionalTeam->SPTeamID;
                $duty->AdditionalTeamStartDate = $additionalTeam->StartDate;
                $duty->AdditionalTeamEndDate = $additionalTeam->EndDate;

                // Format the dates and times
                $duty->DutyDate = Carbon::parse($duty->AD_DutyDate)->format('d-m-Y');
                $duty->DutyName = $duty->AD_DutyName;

                // Convert seconds to HH:MM format
                $startHours = intval($duty->AD_StartTimeSec / 3600);
                $startMins = intval(($duty->AD_StartTimeSec % 3600) / 60);
                $endHours = intval($duty->AD_EndTimeSec / 3600);
                $endMins = intval(($duty->AD_EndTimeSec % 3600) / 60);

                $duty->StartTime = sprintf('%02d:%02d', $startHours, $startMins);
                $duty->EndTime = sprintf('%02d:%02d', $endHours, $endMins);
                $duty->Time = $duty->StartTime . ' - ' . $duty->EndTime;

                $allConflictingDuties[] = $duty;
            }
        }

        return $allConflictingDuties;
    }

    /**
     * Bulk unassign duties using usp_Edit_Allocations 'UNASSIGN' SP
     * Replaces direct DB delete/update for AD_IsNeedCovering logic
     * SP handles both cases: status=9 (no cover) or unallocated (needs cover)
     *
     * @param array $aspIds ASP_AllocationsSPID to unassign
     * @param int $personId
     * @param string $netLogin Auth::user()->UD_NetLogin
     * @param int|null $allocationsId Fallback null (query from ASP table)
     * @return array ['success_count' => int, 'errors' => array]
     */
    public function bulkUnassignDuties(array $aspIds, int $personId, string $netLogin, ?int $allocationsId = null): array
    {
        if (empty($aspIds)) {
            return ['success_count' => 0, 'errors' => []];
        }

        $successCount = 0;
        $errors = [];

        foreach ($aspIds as $aspId) {
            // Query ASP_AllocationsID if not provided
            $aspData = DB::table('AllocationsScheduledPersons')
                ->where('ASP_AllocationsSPID', $aspId)
                ->where('ASP_SchedulingPersonID', $personId)
                ->select('ASP_AllocationsID')
                ->first();

            if (!$aspData) {
                $errors[] = "ASP ID {$aspId} not found for person {$personId}";
                continue;
            }

            $allocId = $allocationsId ?? $aspData->ASP_AllocationsID;

            try {
                // SP call - minimal params for UNASSIGN (other params NULL/default)
                $rows = DB::select(
                    'exec [dbo].[usp_Edit_Allocations] ?, ?, ?, ?',
                    ['UNASSIGN', $netLogin, $aspId, $allocId]
                );

                $result = $this->firstRowToArray($rows);
                if (($result['SPExecStatus'] ?? 1) === 0) {
                    $successCount++;
                } else {
                    $errors[] = $result['SPMessage'] ?? 'Unknown SP error for ASP ' . $aspId;
                }
            } catch (\Exception $e) {
                Log::error('SP unassign error', ['aspId' => $aspId, 'error' => $e->getMessage()]);
                $errors[] = 'SP Exception: ' . $e->getMessage();
            }
        }

        Log::info('Bulk unassign complete', [
            'aspIds_count' => count($aspIds),
            'success_count' => $successCount,
            'errors_count' => count($errors)
        ]);

        return ['success_count' => $successCount, 'errors' => $errors];
    }
}
