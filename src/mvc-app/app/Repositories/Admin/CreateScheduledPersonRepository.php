<?php

namespace App\Repositories\Admin;

use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use App\Models\User\RefRole;
use App\Models\User\ScheduledPersonTeamLink;
use App\Models\User\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Repositories\Contracts\Admin\CreateScheduledPersonRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 *
 * A rewrite of the SQL Server stored procedure:
 *   [dbo].[usp_CreateUpdate_SchedulePerson]
 *
 * This class preserves the stored procedure's behavior (success/error codes, validations,
 * transactionality, and side effects across related tables) while using Laravel's
 * Query Builder / Eloquent style where practical.
 *
 * Source SP: dbo.usp_CreateUpdate_SchedulePerson
 */
class CreateScheduledPersonRepository implements CreateScheduledPersonRepositoryInterface
{
    /**
     * Execute the "create or update Scheduled Person" flow in one atomic transaction.
     *
     * @param int         $SPTeamID
     * @param string      $DisplayName
     * @param int         $SchedulingTeamID
     * @param string      $HomeTeamStartDate
     * @param string      $HomeTeamEndDate
     * @param string|null $HomeTeamSortCode
     * @param string|null $HomeTeamBackColour
     * @param string|null $HomeTeamFontColour
     * @param string|null $HomeTeamAdminNotes
     * @param string|null $HomeTeamFWANotes
     * @param string      $ActionType            'edit' | 'create'
     * @param int|null    $ScheduledPersonID
     * @param string      $AdditionalTeamArray   JSON string
     * @param int         $AuditUserID
     * @param string      $DisplayFirstName
     * @param string      $DisplayLastName
     * @param bool        $IsDefaultBGColour
     * @param bool        $IsAdditionalLeave
     *
     * @return array ['intstatusschpeople' => int, 'strstatusschpeople' => string, 'intnewidschpeople' => int|null]
     */
    public function createOrUpdate(
        int $SPTeamID,
        string $DisplayName,
        int $SchedulingTeamID,
        string $HomeTeamStartDate,
        string $HomeTeamEndDate,
        ?string $HomeTeamSortCode,
        ?string $HomeTeamBackColour,
        ?string $HomeTeamFontColour,
        ?string $HomeTeamAdminNotes,
        ?string $HomeTeamFWANotes,
        string $ActionType,
        ?int $ScheduledPersonID,
        string $AdditionalTeamArray,
        int $AuditUserID,
        string $DisplayFirstName,
        string $DisplayLastName,
        bool $IsDefaultBGColour,
        bool $IsAdditionalLeave
    ): array {
        // Assemble and normalize inputs to mirror SP behavior. Dates normalized to 'Y-m-d'.
        $p = [
            'SPTeamID'             => $SPTeamID,
            'DisplayName'          => trim($DisplayName),
            'SchedulingTeamID'     => $SchedulingTeamID,
            'HomeTeamStartDate'    => $this->normalizeDate($HomeTeamStartDate),
            'HomeTeamEndDate'      => $this->normalizeDate($HomeTeamEndDate ?: '9999-01-01', '9999-01-01'),
            'HomeTeamSortCode'     => $HomeTeamSortCode,
            'HomeTeamBackColour'   => $HomeTeamBackColour,
            'HomeTeamFontColour'   => $HomeTeamFontColour,
            'HomeTeamAdminNotes'   => $HomeTeamAdminNotes,
            'HomeTeamFWANotes'     => $HomeTeamFWANotes,
            'ActionType'           => strtolower($ActionType ?: 'edit'),
            'ScheduledPersonID'    => $ScheduledPersonID ? (int)$ScheduledPersonID : null,
            'AdditionalTeamArray'  => $AdditionalTeamArray ?? '[]',
            'AuditUserID'          => (int)$AuditUserID,
            'DisplayFirstName'     => $DisplayFirstName ?? '',
            'DisplayLastName'      => $DisplayLastName ?? '',
            'IsDefaultBGColour'    => (int)$IsDefaultBGColour,
            'IsAdditionalLeave'    => (int)$IsAdditionalLeave,
        ];

        if ($p['ActionType'] === 'edit' && empty($p['ScheduledPersonID'])) {
            return $this->result(0, 'Scheduled Person details are missing.', null);
        }

        try {
            DB::beginTransaction();

            $auditNetLogin                 = $this->getNetLogin($p['AuditUserID']); // UserDetails.UD_NetLogin
            $historyTypeNonScheduledStaff  = $this->getHistoryTypeId('NonScheduledTeamStaff'); // HistoryTypes
            $historyTypeAllocationDuty     = $this->getHistoryTypeId('AllocationDuty');        // HistoryTypes
            $historyTypeAllocationSP       = $this->getHistoryTypeId('AllocationScheduledPerson'); // HistoryTypes
            $archiveTeamId                 = $this->getArchiveTeamId();                        // SchedulingTeams.Name='Archive'
            $viewerRoleId                  = $this->getRoleIdByName('Scheduling Team Viewer'); // REF_Roles

            $existingLinks = [];
            if (!empty($p['ScheduledPersonID'])) {
                $existingLinks = ScheduledPersonTeamLink::where('ScheduledPersonID', $p['ScheduledPersonID'])
                    ->get()
                    ->toArray();
            }

            // --- Parse additional teams JSON ----------------------------------------------------
            $parsedAdditionalTeams = $this->parseAdditionalTeams($p['AdditionalTeamArray']);

            // --- Branch: EDIT vs CREATE ---------------------------------------------------------
            if ($p['ActionType'] === 'edit') {
                // Validations: prevent home team overlap with additional, archived rule, etc.
                $error = $this->validateNoHomeTeamClashWithActiveAdditional(
                    $p,
                    $existingLinks,
                    $parsedAdditionalTeams,
                    $archiveTeamId
                );

                if ($error) {
                    return $error; // already a result()
                }

                // Update UserDetails (case-sensitive compare behavior approximated)
                $this->updateUserDetails($p);

                // If SPTeamID=0 (creating new home row), ensure not before existing home start
                $check = $this->checkHomeStartNotBeforeExistingHomeStart($p, $existingLinks);
                if ($check) {
                    return $check;
                }

                // If SPTeamID>0, update home-team cosmetic fields
                $this->maybeUpdateHomeTeamCosmetics($p);

                // Home team end-date normalized to 9999-01-01
                $p['HomeTeamEndDate'] = '9999-01-01';

                // Prevent start-date change for existing home team row
                $check = $this->blockHomeStartChangeIfSameSPTeamId($p, $existingLinks);
                if ($check) {
                    return $check;
                }

                // Get prior home context
                $priorHome = $this->getMostRecentHomeTeam($existingLinks);

                // If changing home forward in time, enforce no duties past proposed date
                if ($this->isChangingHomeTeamForward($p, $priorHome)) {
                    $conflictMsg = $this->detectAllocationConflictsForHomeTeamChange($p, $priorHome);
                    if ($conflictMsg) {
                        return $this->result(0, $conflictMsg, $p['ScheduledPersonID']);
                    }
                }

                // Additional team edits validation and end-date checks
                $check = $this->validateAndPrepareAdditionalTeamEdits($p, $existingLinks, $parsedAdditionalTeams);

                if ($check) {
                    return $check;
                }

                //Only rotate home team when it truly needs rotating
                $mustRotate = $this->shouldRotateHomeTeam($p, $priorHome);
                if ($mustRotate) {
                    // Rotate home team (close prior spans, insert new)
                    $this->rotateHomeTeam($p, $priorHome);

                    // Shift allocations from old home -> new home (and DEL mirrors)
                    if ($this->isChangingHomeTeamForward($p, $priorHome)) {
                        $this->shiftAllocationsFromOldHomeToNewHome($p, $priorHome, $historyTypeAllocationSP);
                    }
                }

                // Upsert Additional Teams + Viewer roles + history
                $this->upsertAdditionalTeamsForEdit($p, $parsedAdditionalTeams, $existingLinks, $viewerRoleId, $historyTypeNonScheduledStaff);

                // Archive side-effects (cut additional teams if home is Archive*)
                $this->applyArchiveSideEffectsIfNeeded($p);
            } else {
                // --- CREATE branch --------------------------------------------------------------
                $p['ScheduledPersonID'] = $this->insertUserDetailsForCreate($p);

                // Insert Home team link
                $this->insertHomeTeamLink($p);

                // Insert Additional Teams (scheduled) + possible viewer/non-scheduled gaps
                $this->insertAdditionalTeamsForCreate($p, $parsedAdditionalTeams);

                // If future-dated home start, create temporary non-scheduled span + viewer
                $this->maybeCreateTemporaryNonScheduledHomeGap($p, $parsedAdditionalTeams);

                // Seed AllocationsAddPersons from availability windows
                $this->seedAddPersonsFromAvailabilityWindows($p);
            }

            DB::commit();
            // Final success return mirrors SP
            return $this->result(1, 'success', $p['ScheduledPersonID']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Error handling mirrors SP CATCH -> ErrorLog insert + 'Failed'
            $this->logError($e, $AuditUserID ?? null);
            $return = $this->result(1, 'exception', $ScheduledPersonID ?? null);
            $return['strreturnstringschpeople'] = $e->getMessage();
            return $return;
        }
    }

    /**
     *  Determine if we truly need to rotate the home team (close current + insert new).
     * Matches SP intent:
     *  - Rotate when team actually changes; OR
     *  - When SPTeamID=0 (new home row) AND start date differs from prior span (even if same team).
     *  - Otherwise, do not rotate (cosmetic edits handled elsewhere).
     */
    private function shouldRotateHomeTeam(array $p, ?array $priorHome): bool
    {
        // If no prior home found, rotate only when explicitly creating a new home row
        if (!$priorHome) {
            return ((int)$p['SPTeamID'] === 0);
        }
        $teamChanged = ((int)$priorHome['TeamID'] !== (int)$p['SchedulingTeamID']);
        $startChangedViaNewRow = ((int)$p['SPTeamID'] === 0) && (
            Carbon::parse($p['HomeTeamStartDate'])->format('Y-m-d') !== Carbon::parse($priorHome['StartDate'])->format('Y-m-d')
        );
        return $teamChanged || $startChangedViaNewRow;
    }

    // ============================================================================================
    // Utilities — normalization, lookups, shared helpers
    // ============================================================================================
    private function result(int $status, string $message, ?int $id): array
    {
        return [
            'intstatusschpeople' => $status,
            'strstatusschpeople' => $message,
            'intnewidschpeople'  => $id,
        ];
    }

    /**
     * Normalize a date value to 'Y-m-d'. Accepts 'Y-m-d', 'd/m/Y', or parseable string.
     */
    private function normalizeDate(?string $value, ?string $default = null): ?string
    {
        if ($value === null || $value === '') {
            return $default;
        }
        $v = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $v)) {
            return Carbon::parse($v)->format('Y-m-d');
        }
        if (preg_match('#^\d{2}/\d{2}/\d{4}#', $v)) {
            // Handles 'd/m/Y' (ignore any trailing time)
            return Carbon::createFromFormat('d/m/Y', substr($v, 0, 10))->format('Y-m-d');
        }
        return Carbon::parse($v)->format('Y-m-d');
    }

    /** Lookup helpers for related data. These mirror the SP's lookups against UserDetails, HistoryTypes, SchedulingTeams, REF_Roles, etc. */
    private function getNetLogin(int $userId): ?string
    {
        return User::where('UD_UserID', $userId)->value('UD_NetLogin');
    }

    /** Get HistoryType ID by name. Returns null if not found, which should be handled gracefully by the calling code (SP seems to assume these exist). */
    private function getHistoryTypeId(string $historyType): ?int
    {
        return DB::table('HistoryTypes')->where('HistoryType', $historyType)->value('id');
    }
    /** Get Archive team ID. Returns null if not found, which should be handled gracefully by the calling code (SP seems to assume this exists). */
    private function getArchiveTeamId(): ?int
    {
        return SchedulingTeam::where('schedulingTeamName', 'Archive')->value('schedulingTeamId');
    }

    /** Get Role ID by name. Returns null if not found, which should be handled gracefully by the calling code (SP seems to assume this exists). */
    private function getRoleIdByName(string $name): ?int
    {
        return RefRole::where('RoleName', $name)->value('RoleID');
    }

    /** Log an error to the database, mirroring the SP's CATCH block that inserts into ErrorLog. */
    private function getTeamName(int $teamId): ?string
    {
        return SchedulingTeam::where('schedulingTeamId', $teamId)->value('schedulingTeamName');
    }

    /** Get user's display name by ID, used for error messages. */
    private function getUserDisplayName(int $userId): ?string
    {
        return User::where('UD_UserID', $userId)->value('UD_DisplayName');
    }

    // ============================================================================================
    // JSON → Additional teams parsing (mirrors OPENJSON in SP)
    // ============================================================================================

    private function parseAdditionalTeams(string $json): array
    {
        $arr = json_decode($json, true) ?: [];

        return array_map(function ($it) {
            return [
                'SPTeamID'            => (int)($it['addteamSPTeamID'] ?? 0),
                'TeamID'              => (int)($it['addteamsid'] ?? 0),
                'StartDate'           => $this->normalizeDate($it['addteamstartdate'] ?? null),
                'EndDate'             => $this->normalizeDate($it['addteamenddate'] ?? null),
                'SortCode'            => $it['addteamsortcode'] ?? null,
                'Backcolor'           => $it['addteamBackcolor'] ?? null,
                'Fontcolor'           => $it['addteamfontcolor'] ?? null,
                'IsAvailable'         => isset($it['addteamisavailable']) ? (int)$it['addteamisavailable'] : null,
                'CreatedBy'           => isset($it['addteamCreatedBy']) ? (int)$it['addteamCreatedBy'] : null,
                'CreatedDate'         => $this->normalizeDate($it['addteamCreatedDate'] ?? null),
                'LastUpdatedBy'       => isset($it['addteamLastUpdatedBy']) ? (int)$it['addteamLastUpdatedBy'] : null,
                'LastUpdatedDate'     => $this->normalizeDate($it['addteamLastUpdatedDate'] ?? null),
                'AddTeamUpdate'       => isset($it['addteamupdate']) ? (int)$it['addteamupdate'] : 0,
                'IsDefaultBGColour'   => isset($it['addteamIsDefaultBGColour']) ? (int)$it['addteamIsDefaultBGColour'] : 0,
                'IsHomeTeam'          => isset($it['isHomeTeam']) ? (int)$it['isHomeTeam'] : ScheduledPersonTeamLink::ADDITIONAL_TEAM,
                'scheduledType'       => 1,
                'DisplayInViewScreen' => ($it['doNotDisplayInViewScreen'] ?? 0) == 0 ? 1 : 0
            ];
        }, $arr);
    }

    // ============================================================================================
    // EDIT: Validations (mapped from SP)
    // ============================================================================================

    private function validateNoHomeTeamClashWithActiveAdditional(array $p, array $existingLinks, array $parsedAdditional, ?int $archiveTeamId): ?array
    {
        $homeStart = Carbon::parse($p['HomeTeamStartDate'])->startOfDay();
        $homeEnd   = $p['HomeTeamEndDate'] ? Carbon::parse($p['HomeTeamEndDate'])->endOfDay() : null;
        $origHomeEnd = $homeEnd;

        // 1) Payload additional vs new home overlap
        foreach ($parsedAdditional as $at) {
            if ($at['TeamID'] === $p['SchedulingTeamID']) {
                $s = Carbon::parse($at['StartDate'])->startOfDay();
                $e = Carbon::parse($at['EndDate'])->endOfDay();
                if ($e >= $homeStart && $s <= ($origHomeEnd ?? Carbon::maxValue())) {
                    return $this->result(0, 'Home team cannot be same as active additional team. Please use another team as home team or update enddate of additional team.', $p['ScheduledPersonID']);
                }
            }
        }

        // 2) Existing additional rows not replaced by payload
        foreach ($existingLinks as $row) {
            if (
                (int)$row['TeamID'] === (int)$p['SchedulingTeamID']
                && (int)$row['IsHomeTeam'] === ScheduledPersonTeamLink::ADDITIONAL_TEAM
                && (int)$row['scheduledType'] === 1
            ) {
                $s = Carbon::parse($row['StartDate'])->startOfDay();
                $e = Carbon::parse($row['EndDate'])->endOfDay();

                $stillActiveNotReplaced = !collect($parsedAdditional)->first(fn($x) => $x['SPTeamID'] === (int)$row['SPTeamID']);
                if ($stillActiveNotReplaced && $e >= $homeStart && $s <= ($origHomeEnd ?? Carbon::maxValue())) {
                    return $this->result(0, 'Home team cannot be same as active additional team. Please use another team as home team or update enddate of additional team.', $p['ScheduledPersonID']);
                }
            }
        }

        // 3) Archived home team active ➜ cannot add additional team
        $hasArchivedHomeActive = collect($existingLinks)->contains(function ($r) use ($archiveTeamId, $parsedAdditional) {
            return (int)$r['TeamID'] === (int)$archiveTeamId
                && (int)$r['IsHomeTeam'] === ScheduledPersonTeamLink::HOME_TEAM
                && (int)$r['scheduledType'] === 1
                && $this->spanOverlapsAny($r, $parsedAdditional);
        });

        if ($hasArchivedHomeActive && !empty($parsedAdditional)) {
            return $this->result(0, 'Cannot add additional team as the person is moved under Archived team. Please update start date/end date of additional team.', $p['ScheduledPersonID']);
        }

        return null;
    }

    /** Helper to detect if a given home team span overlaps with any of the additional teams in the payload, used for validation when SPTeamID=0 (new home team) to prevent overlap with active additional teams. */
    private function spanOverlapsAny(array $spanRow, array $parsedAdditional): bool
    {
        $s1 = Carbon::parse($spanRow['StartDate'])->startOfDay();
        $e1 = Carbon::parse($spanRow['EndDate'])->endOfDay();

        foreach ($parsedAdditional as $r) {
            $s2 = Carbon::parse($r['StartDate'])->startOfDay();
            $e2 = Carbon::parse($r['EndDate'])->endOfDay();
            if ($e1 >= $s2 && $s1 <= $e2) {
                return true;
            }
        }
        return false;
    }

    /** If creating a new home team span (SPTeamID=0), ensure the new start date is not before the start date of any existing home team spans. This mirrors the SP's check that prevents creating a new home team with a start date that would overlap with an existing home team, which could cause data integrity issues and confusion in the scheduling logic. If such a conflict is detected, return a user-friendly error message indicating the issue. */
    private function checkHomeStartNotBeforeExistingHomeStart(array $p, array $existingLinks): ?array
    {
        if ($p['SPTeamID'] !== 0) {
            return null;
        }
        $homeStart = Carbon::parse($p['HomeTeamStartDate'])->startOfDay();

        $existingHome = collect($existingLinks)
            ->filter(fn($r) => (int)$r['IsHomeTeam'] === ScheduledPersonTeamLink::HOME_TEAM && (int)$r['scheduledType'] === 1)
            ->sortBy('StartDate')
            ->first();

        if ($existingHome) {
            $exStart = Carbon::parse($existingHome['StartDate'])->startOfDay();
            if ($exStart >= $homeStart && (int)$existingHome['TeamID'] !== (int)$p['SchedulingTeamID']) {
                return $this->result(0, 'New home team Start Date can not be before or same as current home team Start Date.', $p['ScheduledPersonID']);
            }
        }
        return null;
    }

    /** Update home team cosmetics if the home team is being modified. */
    private function maybeUpdateHomeTeamCosmetics(array $p): void
    {
        if ($p['SPTeamID'] > 0) {
            ScheduledPersonTeamLink::where('SPTeamID', $p['SPTeamID'])
                ->update([
                    'BackgroundColour'  => $p['HomeTeamBackColour'],
                    'fontcolour'        => $p['HomeTeamFontColour'],
                    'SortCode'          => $p['HomeTeamSortCode'],
                    'IsDefaultBGColour' => $p['IsDefaultBGColour'],
                    'LastUpdatedBy'     => $p['AuditUserID'],
                    'LastUpdatedDate'   => DB::raw('GETDATE()'),
                ]);
        }
    }

    /** If modifying an existing home team span (SPTeamID>0), block changes to the start date that would effectively create a new home team span with a different start date, as this could lead to data integrity issues and confusion in the scheduling logic. This mirrors the SP's check that prevents changing the start date of an existing home team span if it would result in a new span being created with a different start date, which could cause issues with how the system tracks home team history and allocations. If such a change is detected, return a user-friendly error message indicating that changing the start date in this way is not allowed. */
    private function blockHomeStartChangeIfSameSPTeamId(array $p, array $existingLinks): ?array
    {
        $curr = collect($existingLinks)
            ->first(fn($r) => (int)$r['SPTeamID'] === (int)$p['SPTeamID']);

        if ($curr) {
            $currStart = Carbon::parse($curr['StartDate'])->format('Y-m-d');
            if ($currStart !== $p['HomeTeamStartDate']) {
                return $this->result(0, 'It is not possible to update start date of a existing home team.', $p['ScheduledPersonID']);
            }
        }
        return null;
    }

    /** Get the most recent home team span for the person, used for context in validations and updates when editing. This mirrors the SP's logic of selecting the most recent home team span for the person to determine the prior home team context, which is important for enforcing validations around changing home teams and ensuring that any changes to the home team are consistent with the person's existing scheduling history. */
    private function getMostRecentHomeTeam(array $existingLinks): ?array
    {
        return collect($existingLinks)
            ->filter(fn($r) => (int)$r['IsHomeTeam'] === ScheduledPersonTeamLink::HOME_TEAM && (int)$r['scheduledType'] === 1)
            ->sortByDesc('EndDate')
            ->first();
    }

    /** Detect if the home team is being changed forward in time (i.e. new start date is after prior start date), which would trigger additional validations around existing allocations that could be impacted by the home team change. This mirrors the SP's logic of comparing the new home team start date with the prior home team start date to determine if the home team is effectively being changed forward in time, which has implications for how existing allocations are handled and whether additional checks need to be performed to ensure that there are no scheduling conflicts or orphaned duties as a result of the home team change. */
    private function isChangingHomeTeamForward(array $p, ?array $priorHome): bool
    {
        if (!$priorHome) {
            return false;
        }
        $priorTeamId = (int)$priorHome['TeamID'];
        $newTeamId   = (int)$p['SchedulingTeamID'];
        $priorStart  = Carbon::parse($priorHome['StartDate']);
        $newStart    = Carbon::parse($p['HomeTeamStartDate']);
        return ($priorTeamId !== $newTeamId) && ($newStart->gt($priorStart));
    }
    /**
     * Detect if there are any allocation/duty conflicts for the person in the prior home team that would be impacted by changing home team forward in time (i.e. duties that would now fall outside of home team span). If so, return a user-friendly message describing the issue and next steps. This mirrors the SP's checks against AL/AD/ASP and MasterDuties/TimeDimension for any duties that would be orphaned by the home team change, and surfaces the max week of conflict to guide the user in resolving the issue.
     * Note: the SP's logic is a bit convoluted and seems to have some redundant checks, but this implementation aims to mirror the core intent of preventing a home team change that would leave duties without a valid home team span, and providing guidance to the user on how to resolve such conflicts.
     */
    private function detectAllocationConflictsForHomeTeamChange(array $p, array $priorHome): ?string
    {
        // Condensed SP checks for AL/AD/ASP and MasterDuties/TimeDimension.
        $maxWeekRow = DB::table('Allocations as AL')
            ->join('AllocationsScheduledPersons as ASP', 'AL.AL_AllocationsID', '=', 'ASP.ASP_AllocationsID')
            ->join('AllocationsDuties as AD', function ($j) {
                $j->on('AL.AL_AllocationsID', '=', 'AD.AD_AllocationsID')
                    ->on('ASP.ASP_AllocationsDutyID', '=', 'AD.AD_AllocationsDutyID');
            })
            ->where('AL.AL_SchedulingTeamID', (int)$priorHome['TeamID'])
            ->where('ASP.ASP_SchedulingPersonID', (int)$p['ScheduledPersonID'])
            ->where('ASP.ASP_DutyDate', '>=', $p['HomeTeamStartDate'])
            ->where('AD.AD_DutyType', '<', 7)
            ->selectRaw('MAX(AL.AL_WeekNumber) as MaxWeek, MAX(AD.AD_DutyDate) as MaxDutyDate')
            ->first();

        $maxAdHocWeek = DB::table('MasterDuties as md')
            ->join('TimeDimension as TD', function ($j) {
                $j->whereRaw('TD.dDateTime BETWEEN md.StartDate AND md.EndDate');
            })
            ->where('md.ScheduledPersonID', (int)$p['ScheduledPersonID'])
            ->where('md.TeamID', (int)$priorHome['TeamID'])
            ->where('md.StartDate', '>=', $p['HomeTeamStartDate'])
            ->where('md.DutyTypeID', 6)
            ->selectRaw('MAX(ixYearWeek) as MaxYearWeek')
            ->first();

        $maxDutyDate = $maxWeekRow?->MaxDutyDate;
        $maxWeek     = $maxWeekRow?->MaxWeek;
        $maxAdhoc    = $maxAdHocWeek?->MaxYearWeek;

        if ($maxDutyDate || $maxAdhoc) {
            $oldTeamName = $this->getTeamName((int)$priorHome['TeamID']);
            $uiMsg = "You cannot change the Team for {$p['DisplayName']} while he/she still has Duties allocated in the {$oldTeamName} Team. ";

            if ($maxWeek) {
                $year = substr($maxWeek, 0, 4);
                $week = substr($maxWeek, 4);
                $uiMsg .= "Please open the Weeks Form and remove all the Duties that occur after and including the Effective From date you have chosen. The maximum Week that the person can be found in is - Week {$week}/{$year}. ";
            }

            if ($maxAdhoc) {
                $year = substr($maxAdhoc, 0, 4);
                $week = substr($maxAdhoc, 4);
                $uiMsg .= "Please open the Ad Hoc Duty Form and remove all the Duties that are scheduled after and including the Effective From date you have chosen. The maximum Week that the person can be found in is - Week {$week}/{$year}.";
            }

            return $uiMsg;
        }

        return null;
    }

    /** Validations for additional team edits in the edit flow:
     * 1) No new additional team can start before first home team start
     * 2) If reducing end-date on existing additional, ensure no duties beyond new end-date
     *    (and mark AAP past new end as status 9)
     */
    private function validateAndPrepareAdditionalTeamEdits(array $p, array $existingLinks, array $parsedAdditional): ?array
    {
        // 1) No additional team starting before first home team start (for new additional rows)
        $homeStarts = collect($existingLinks)
            ->filter(fn($r) => (int)$r['IsHomeTeam'] === ScheduledPersonTeamLink::HOME_TEAM && (int)$r['scheduledType'] === 1)
            ->pluck('StartDate')
            ->all();

        if (!empty($homeStarts)) {
            $minHomeStart = Carbon::parse(min($homeStarts))->startOfDay();
            foreach ($parsedAdditional as $at) {
                if (empty($at['SPTeamID'])) {
                    $s = Carbon::parse($at['StartDate'])->startOfDay();
                    if ($s->lt($minHomeStart)) {
                        $dateTxt = Carbon::parse($minHomeStart)->format('d/m/Y H:i');
                        return $this->result(0, "Start Date of an Additional Team cannot be before the start date of the first Home Team. Please select a date on or after {$dateTxt}", $p['ScheduledPersonID']);
                    }
                }
            }
        }

        // 2) If reducing end-date on existing additional, ensure no duties beyond new end-date
        foreach ($parsedAdditional as $at) {
            if (!empty($at['SPTeamID'])) {
                $old = collect($existingLinks)->first(fn($r) => (int)$r['SPTeamID'] === (int)$at['SPTeamID']);
                if (!$old) {
                    continue;
                }

                $oldEnd = Carbon::parse($old['EndDate'])->endOfDay();
                $newEnd = Carbon::parse($at['EndDate'])->endOfDay();

                if ($newEnd->lt($oldEnd)) {
                    $existsDuty = DB::table('Allocations as AL')
                        ->join('AllocationsScheduledPersons as ASP', 'AL.AL_AllocationsID', '=', 'ASP.ASP_AllocationsID')
                        ->join('AllocationsDuties as AD', 'AD.AD_AllocationsDutyID', '=', 'ASP.ASP_AllocationsDutyID')
                        ->where('ASP.ASP_SchedulingPersonID', $p['ScheduledPersonID'])
                        ->where('ASP.ASP_DutyTeamID', $at['TeamID'])
                        ->where('ASP.ASP_DutyDate', '>', $newEnd->format('Y-m-d'))
                        ->exists();

                    if ($existsDuty) {
                        return $this->result(0, 'The end date for the additional team cannot be updated because duties have already been assigned beyond the proposed new end date.', $p['ScheduledPersonID']);
                    }

                    // Mark AAP past new end as status 9
                    DB::table('AllocationsAddPersons as AAP')
                        ->join('Allocations as AL', 'AL.AL_AllocationsID', '=', 'AAP.AAP_AllocationsID')
                        ->where('AAP.AAP_SchedulingPersonID', $p['ScheduledPersonID'])
                        ->where('AL.AL_SchedulingTeamID', $at['TeamID'])
                        ->where('AAP.AAP_DutyDate', '>', $newEnd->format('Y-m-d'))
                        ->update(['AAP.AAP_Status' => 9]);
                }
            }
        }

        return null;
    }

    // ============================================================================================
    // EDIT: Mutations — home rotation, allocation shifts, additional teams, archive
    // ============================================================================================
    private function rotateHomeTeam(array $p, ?array $priorHome): void
    {
        // Close current non-scheduled viewer link for same team covering HomeTeamStartDate
        ScheduledPersonTeamLink::where('ScheduledPersonID', $p['ScheduledPersonID'])
            ->where('scheduledType', 0)
            ->where('TeamID', $p['SchedulingTeamID'])
            ->whereDate('EndDate', '>=', $p['HomeTeamStartDate'])
            ->update([
                'EndDate'         => Carbon::parse($p['HomeTeamStartDate'])->subDay()->format('Y-m-d'),
                'IsActive'        => 0,
                'LastUpdatedBy'   => $p['AuditUserID'],
                'LastUpdatedDate' => DB::raw('GETDATE()'),
            ]);

        // Close overlapping UserRoles for same window
        UserRole::where('UR_SchedulingTeamID', $p['SchedulingTeamID'])
            ->where('UR_UserID', $p['ScheduledPersonID'])
            ->whereDate('UR_StartDate', '<=', $p['HomeTeamEndDate'] ?? $p['HomeTeamStartDate'])
            ->whereDate('UR_EndDate', '>=', $p['HomeTeamStartDate'])
            ->update([
                'UR_EndDate'     => Carbon::parse($p['HomeTeamStartDate'])->subDay()->format('Y-m-d'),
                'UR_UpdatedBy'   => $p['AuditUserID'],
                'UR_UpdatedDate' => DB::raw('GETUTCDATE()'),
            ]);

        if ($priorHome) {
            // Close old home link
            ScheduledPersonTeamLink::where('SPTeamID', $priorHome['SPTeamID'])
                ->update([
                    'EndDate'         => Carbon::parse($p['HomeTeamStartDate'])->subDay()->format('Y-m-d'),
                    'IsActive'        => 0,
                    'LastUpdatedBy'   => $p['AuditUserID'],
                    'LastUpdatedDate' => DB::raw('GETDATE()'),
                ]);
        }

        // Insert new home link
        ScheduledPersonTeamLink::insert([
            'ScheduledPersonID' => $p['ScheduledPersonID'],
            'TeamID'            => $p['SchedulingTeamID'],
            'IsHomeTeam'        => ScheduledPersonTeamLink::HOME_TEAM,
            'SortCode'          => $p['HomeTeamSortCode'],
            'CreatedBy'         => $p['AuditUserID'],
            'CreatedDate'       => DB::raw('GETDATE()'),
            'StartDate'         => $p['HomeTeamStartDate'],
            'EndDate'           => $p['HomeTeamEndDate'] ?? '9999-01-01',
            'LastUpdatedBy'     => $p['AuditUserID'],
            'LastUpdatedDate'   => DB::raw('GETDATE()'),
            'BackgroundColour'  => $p['HomeTeamBackColour'],
            'fontcolour'        => $p['HomeTeamFontColour'],
            'IsActive'          => 1,
            'IsAvailable'       => 0,
            'isDefault'         => 0,
            'scheduledType'     => 1,
            'IsDefaultBGColour' => $p['IsDefaultBGColour'],
            'rota'              => $priorHome['ROTA'] ?? null,
        ]);

        // RotaPeople close-out for old home
        if ($priorHome) {
            DB::table('RotaPeople as RP')
                ->join('MasterRotas as MR', 'MR.RotaID', '=', 'RP.RotaID')
                ->where('MR.TeamID', (int)$priorHome['TeamID'])
                ->where('MR.IsActive', 1)
                ->where('RP.IsActive', 1)
                ->where('RP.ScheduledPersonID', $p['ScheduledPersonID'])
                ->where('RP.EndDate', '>', $p['HomeTeamStartDate'])
                ->update([
                    'RP.EndDate'  => Carbon::parse($p['HomeTeamStartDate'])->subDay()->format('Y-m-d'),
                    'LastModBy'   => $p['AuditUserID'],
                    'LastModDate' => DB::raw('GETDATE()'),
                ]);

            // Exported_rota close-out
            DB::table('Exported_rota')
                ->where('SchedulingPersonID', $p['ScheduledPersonID'])
                ->where('SchedulingTeamId', (int)$priorHome['TeamID'])
                ->update([
                    'RotaAssignmentEndDate' => Carbon::parse($p['HomeTeamStartDate'])->subDay()->format('Y-m-d'),
                    'TeamJoinEndDate'       => Carbon::parse($p['HomeTeamStartDate'])->subDay()->format('Y-m-d'),
                    'AssignmentEndWeek'     => DB::raw(
                        "CONVERT(varchar, DATEPART(YEAR, DATEADD(day, -1, CAST('{$p['HomeTeamStartDate']}' AS date))))"
                            . "+''+CONVERT(varchar, FORMAT(DATEPART(Week, DATEADD(day, -1, CAST('{$p['HomeTeamStartDate']}' AS date))), '00'))"
                    ),
                ]);
        }
    }

    /**
     * Shift allocations from old home team to new home team by matching on WeekNumber, and update related duty ids. Also insert history for impacted ASP rows, and delete any publish/addperson mirror rows for the shifted ASPs.
     */
    private function shiftAllocationsFromOldHomeToNewHome(array $p, array $priorHome, ?int $historyTypeAllocationSP): void
    {
        // History text same as SP
        $currentUserName = $this->getUserDisplayName($p['AuditUserID']) ?? '';
        $oldName = $this->getTeamName((int)$priorHome['TeamID']) ?? '';
        $newName = $this->getTeamName((int)$p['SchedulingTeamID']) ?? '';
        $historyText = "Scheduling Team changed from {$oldName} to {$newName} on " .
            Carbon::now()->format('d M Y H:i') . " by {$currentUserName}";

        // Insert History for impacted ASP rows
        DB::table('History')->insertUsing(
            ['HistoryType', 'UserID', 'History', 'datetime', 'AttributeID', 'HistorySubType'],
            DB::table('Allocations as AL')
                ->join('AllocationsScheduledPersons as ASP', 'AL.AL_AllocationsID', '=', 'ASP.ASP_AllocationsID')
                ->join('AllocationsDuties as AD', function ($j) {
                    $j->on('AL.AL_AllocationsID', '=', 'AD.AD_AllocationsID')
                        ->on('ASP.ASP_AllocationsDutyID', '=', 'AD.AD_AllocationsDutyID');
                })
                ->where('AL.AL_SchedulingTeamID', (int)$priorHome['TeamID'])
                ->where('ASP.ASP_SchedulingPersonID', $p['ScheduledPersonID'])
                ->where('ASP.ASP_DutyDate', '>=', $p['HomeTeamStartDate'])
                ->where('AD.AD_DutyType', '<', 7)
                ->selectRaw(
                    (int)$historyTypeAllocationSP . " as HistoryType, "
                        . (int)$p['AuditUserID']      . " as UserID, "
                        . "CAST(? as nvarchar(max)) as History, GETDATE() as datetime, "
                        . "ASP.ASP_AllocationsSPID as AttributeID, 'PH' as HistorySubType",
                    [$historyText]
                )
        );

        // Move ASP to new team's AL by matching WeekNumber; update duty ids;
        DB::statement("
            DECLARE @UpdatedSPIDs TABLE (AllocationsSPID INT);

            UPDATE ASP
               SET ASP.ASP_AllocationsID = ALN.AL_AllocationsID,
                   ASP.ASP_DutyTeamID    = ALN.AL_SchedulingTeamID,
                   ASP.ASP_WIADStatus    = 0,
                   ASP.ASP_UpdatedBy     = :auditUser,
                   ASP.ASP_UpdatedDate   = GETUTCDATE()
              OUTPUT INSERTED.ASP_AllocationsSPID INTO @UpdatedSPIDs
            FROM Allocations AL
            INNER JOIN AllocationsScheduledPersons ASP ON AL.AL_AllocationsID = ASP.ASP_AllocationsID
            INNER JOIN Allocations ALN ON ALN.AL_WeekNumber = AL.AL_WeekNumber
            WHERE AL.AL_SchedulingTeamID = :oldTeam
              AND ALN.AL_SchedulingTeamID = :newTeam
              AND ASP.ASP_SchedulingPersonID = :spid
              AND ASP.ASP_DutyDate >= :homeStart;

            UPDATE ASP
               SET ASP.ASP_AllocationsDutyID = AD.AD_AllocationsDutyID
            FROM Allocations AL
            INNER JOIN AllocationsScheduledPersons ASP ON AL.AL_AllocationsID = ASP.ASP_AllocationsID
            INNER JOIN @UpdatedSPIDs TP ON TP.AllocationsSPID = ASP.ASP_AllocationsSPID
            INNER JOIN AllocationsDuties AD ON AL.AL_AllocationsID = AD.AD_AllocationsID
                                           AND ASP.ASP_iDay       = AD.AD_iDay;

            INSERT INTO AllocationsUpdated (AU_AllocationsID, AU_AllocationsDutyID, AU_AllocationsSPID, AU_Status, AU_UpdatedBy, AU_UpdatedDate)
            SELECT ASP_AllocationsID, ASP_AllocationsDutyID, ASP_AllocationsSPID, 0, :auditUser2, GETUTCDATE()
            FROM AllocationsScheduledPersons ASP
            INNER JOIN @UpdatedSPIDs TP ON TP.AllocationsSPID = ASP.ASP_AllocationsSPID;

            DELETE AJ FROM Allocations_Jobs_Publish AJ
            INNER JOIN Allocations_Publish AP ON AP.ID = AJ.AllocationID
            INNER JOIN @UpdatedSPIDs TP ON TP.AllocationsSPID = AP.AllocationsSPID;

            DELETE AP FROM Allocations_Publish AP
            INNER JOIN @UpdatedSPIDs TP ON TP.AllocationsSPID = AP.AllocationsSPID;

            DELETE AUPD FROM AllocationsUpdated AUPD
            INNER JOIN @UpdatedSPIDs TP ON TP.AllocationsSPID = AUPD.AU_AllocationsSPID;

            DELETE AAP FROM AllocationsAddPersons AAP
            INNER JOIN @UpdatedSPIDs TP ON TP.AllocationsSPID = AAP.AAP_AllocationsSPID;

            DELETE AASP FROM AllocationsScheduledPersons AASP
            INNER JOIN @UpdatedSPIDs TP ON TP.AllocationsSPID = AASP.ASP_AllocationsSPID;
        ", [
            'auditUser'     => $p['AuditUserID'],
            'oldTeam'   => (int)$priorHome['TeamID'],
            'newTeam'   => (int)$p['SchedulingTeamID'],
            'spid'      => (int)$p['ScheduledPersonID'],
            'homeStart' => $p['HomeTeamStartDate'],
            'auditUser2'     => $p['AuditUserID']
        ]);

        // Populate *_DEL mirrors and clean residuals (compact form of SP)
        $this->executeDelMirrorsForMovedAllocations($p, $priorHome);
    }

    /**
     * Mirrors the logic in the SP to insert into Allocations_DEL and AllocationsScheduledPersons_DEL for the impacted allocations, then delete from live tables.
     * This is needed to trigger the necessary business logic in the application that relies on these DEL tables as change feeds for moved allocations (e.g. auto reassigning duties on home team change).
     */
    private function executeDelMirrorsForMovedAllocations(array $p, array $priorHome): void
    {
        DB::statement("
                    DECLARE @audit INT = :audit;
                    DECLARE @oldTeam INT = :oldTeam;
                    DECLARE @newTeam INT = :newTeam;
                    DECLARE @spid INT = :spid;
                    DECLARE @homeStart DATE = :homeStart;

                    INSERT INTO Allocations_DEL (
                        AL_ORIG_AllocationsID, AL_WeekNumber, AL_SchedulingTeamID, AL_Status,
                        AL_CreatedBy, AL_CreatedDate, AL_UpdatedBy, AL_UpdatedDate
                    )
                    SELECT DISTINCT
                        AL.AL_AllocationsID,
                        AL.AL_WeekNumber,
                        AL.AL_SchedulingTeamID,
                        AL.AL_Status,
                        AL.AL_CreatedBy,
                        AL.AL_CreatedDate,
                        @audit,
                        GETUTCDATE()
                    FROM Allocations AL
                    INNER JOIN AllocationsScheduledPersons ASP
                        ON AL.AL_AllocationsID = ASP.ASP_AllocationsID
                    WHERE AL.AL_SchedulingTeamID = @oldTeam
                    AND ASP.ASP_SchedulingPersonID = @spid
                    AND ASP.ASP_DutyDate >= @homeStart
                    AND NOT EXISTS (
                            SELECT 1
                            FROM Allocations_DEL ADL
                            WHERE ADL.AL_ORIG_AllocationsID = AL.AL_AllocationsID
                    );

                    DECLARE @UpdatedSPIDs TABLE (AllocationsSPID INT);

                    INSERT INTO AllocationsScheduledPersons_DEL (
                        ASP_ORIG_AllocationsSPID, ASP_AllocationsID, ASP_AllocationsDutyID,
                        ASP_SchedulingPersonID, ASP_iDay, ASP_SortCode, ASP_LeaveStatus,
                        ASP_LeaveType, ASP_DutyDate, ASP_WIADStatus, ASP_MarkedOverTime,
                        ASP_OverTimeHours, ASP_DutyTeamID, ASP_SigninStartTime,
                        ASP_SigninEndTime, ASP_SigninStatus, ASP_SigninINBuilding,
                        ASP_EDPStatus, ASP_Comments, ASP_LeaveStartTimeSec,
                        ASP_LeaveEndTimeSec, ASP_LeaveStartTimeLocal, ASP_LeaveEndTimeLocal,
                        ASP_UnderElevenBreakStatus, ASP_CalculatedUnderElevenHrs,
                        ASP_OverrideUnderElevenHrs, ASP_RequestsStatus, ASP_RequestsCount,
                        ASP_LockRequestsStatus, ASP_ChargingStatus, ASP_CreatedBy,
                        ASP_CreatedDate, ASP_UpdatedBy, ASP_UpdatedDate
                    )
                    OUTPUT INSERTED.ASP_ORIG_AllocationsSPID INTO @UpdatedSPIDs
                    SELECT
                        ASP.ASP_AllocationsSPID,
                        ADL.AL_AllocationsID,
                        NULL,
                        ASP.ASP_SchedulingPersonID,
                        ASP.ASP_iDay,
                        ASP.ASP_SortCode,
                        ASP.ASP_LeaveStatus,
                        ASP.ASP_LeaveType,
                        ASP.ASP_DutyDate,
                        ASP.ASP_WIADStatus,
                        ASP.ASP_MarkedOverTime,
                        ASP.ASP_OverTimeHours,
                        ASP.ASP_DutyTeamID,
                        ASP.ASP_SigninStartTime,
                        ASP.ASP_SigninEndTime,
                        ASP.ASP_SigninStatus,
                        ASP.ASP_SigninINBuilding,
                        ASP.ASP_EDPStatus,
                        ASP.ASP_Comments,
                        ASP.ASP_LeaveStartTimeSec,
                        ASP.ASP_LeaveEndTimeSec,
                        ASP.ASP_LeaveStartTimeLocal,
                        ASP.ASP_LeaveEndTimeLocal,
                        ASP.ASP_UnderElevenBreakStatus,
                        ASP.ASP_CalculatedUnderElevenHrs,
                        ASP.ASP_OverrideUnderElevenHrs,
                        ASP.ASP_RequestsStatus,
                        ASP.ASP_RequestsCount,
                        ASP.ASP_LockRequestsStatus,
                        ASP.ASP_ChargingStatus,
                        ASP.ASP_CreatedBy,
                        ASP.ASP_CreatedDate,
                        @audit,
                        GETUTCDATE()
                    FROM Allocations AL
                    INNER JOIN AllocationsScheduledPersons ASP
                        ON AL.AL_AllocationsID = ASP.ASP_AllocationsID
                    INNER JOIN Allocations_DEL ADL
                        ON ADL.AL_ORIG_AllocationsID = AL.AL_AllocationsID
                    WHERE AL.AL_SchedulingTeamID = @oldTeam
                    AND ASP.ASP_SchedulingPersonID = @spid
                    AND ASP.ASP_DutyDate >= @homeStart;

                    INSERT INTO AllocationsAddPersons_DEL (
                        AAP_ORIG_AllocationsAPID, AAP_AllocationsID, AAP_AllocationsSPID,
                        AAP_SchedulingPersonID, AAP_Status, AAP_CreatedBy,
                        AAP_CreatedDate, AAP_UpdatedBy, AAP_UpdatedDate
                    )
                    SELECT
                        AAP.AAP_AllocationsAPID,
                        ADL.AL_AllocationsID,
                        APL.ASP_AllocationsSPID,
                        AAP.AAP_SchedulingPersonID,
                        AAP.AAP_Status,
                        AAP.AAP_CreatedBy,
                        AAP.AAP_CreatedDate,
                        @audit,
                        GETUTCDATE()
                    FROM AllocationsAddPersons AAP
                    INNER JOIN Allocations AL
                        ON AL.AL_AllocationsID = AAP.AAP_AllocationsID
                    INNER JOIN Allocations_DEL ADL
                        ON ADL.AL_AllocationsID = ADL.AL_ORIG_AllocationsID
                    INNER JOIN AllocationsScheduledPersons_DEL APL
                        ON ADL.AL_AllocationsID = APL.ASP_AllocationsID
                    INNER JOIN @UpdatedSPIDs TP
                        ON TP.AllocationsSPID = APL.ASP_ORIG_AllocationsSPID
                    WHERE AAP.AAP_DutyDate >= @homeStart
                    AND AL.AL_SchedulingTeamID = @newTeam
                    AND AAP.AAP_SchedulingPersonID = @spid;

                    INSERT INTO Allocations_DEL (
                        AL_ORIG_AllocationsID, AL_WeekNumber, AL_SchedulingTeamID,
                        AL_Status, AL_CreatedBy, AL_CreatedDate,
                        AL_UpdatedBy, AL_UpdatedDate
                    )
                    SELECT DISTINCT
                        AL.AL_AllocationsID,
                        AL.AL_WeekNumber,
                        AL.AL_SchedulingTeamID,
                        AL.AL_Status,
                        AL.AL_CreatedBy,
                        AL.AL_CreatedDate,
                        @audit,
                        GETUTCDATE()
                    FROM AllocationsAddPersons AAP
                    INNER JOIN Allocations AL
                        ON AL.AL_AllocationsID = AAP.AAP_AllocationsID
                    WHERE AAP.AAP_DutyDate >= @homeStart
                    AND AL.AL_SchedulingTeamID = @newTeam
                    AND AAP.AAP_SchedulingPersonID = @spid
                    AND NOT EXISTS (
                            SELECT 1
                            FROM Allocations_DEL ADL
                            WHERE ADL.AL_ORIG_AllocationsID = AL.AL_AllocationsID
                    );

                    INSERT INTO AllocationsAddPersons_DEL (
                        AAP_ORIG_AllocationsAPID, AAP_AllocationsID,
                        AAP_AllocationsSPID, AAP_SchedulingPersonID,
                        AAP_Status, AAP_CreatedBy, AAP_CreatedDate,
                        AAP_UpdatedBy, AAP_UpdatedDate
                    )
                    SELECT
                        AAP.AAP_AllocationsAPID,
                        ADL.AL_AllocationsID,
                        NULL,
                        AAP.AAP_SchedulingPersonID,
                        AAP.AAP_Status,
                        AAP.AAP_CreatedBy,
                        AAP.AAP_CreatedDate,
                        @audit,
                        GETUTCDATE()
                    FROM AllocationsAddPersons AAP
                    INNER JOIN Allocations AL
                        ON AL.AL_AllocationsID = AAP.AAP_AllocationsID
                    INNER JOIN Allocations_DEL ADL
                        ON ADL.AL_AllocationsID = ADL.AL_ORIG_AllocationsID
                    WHERE AAP.AAP_DutyDate >= @homeStart
                    AND AL.AL_SchedulingTeamID = @newTeam
                    AND AAP.AAP_SchedulingPersonID = @spid;

                    INSERT INTO AllocationsDelPersons_DEL (
                        ADP_Orig_AllocationsADPID, ADP_AllocationsID,
                        ADP_SchedulingPersonID, ADP_Status,
                        ADP_CreatedBy, ADP_CreatedDate,
                        ADP_UpdatedBy, ADP_UpdatedDate
                    )
                    SELECT
                        ADP.ADP_AllocationsADPID,
                        ADL.AL_AllocationsID,
                        ADP.ADP_SchedulingPersonID,
                        ADP.ADP_Status,
                        ADP.ADP_CreatedBy,
                        ADP.ADP_CreatedDate,
                        ADP.ADP_UpdatedBy,
                        ADP.ADP_UpdatedDate
                    FROM AllocationsDelPersons ADP
                    INNER JOIN Allocations AL
                        ON ADP.ADP_AllocationsID = AL.AL_AllocationsID
                    INNER JOIN Allocations_DEL ADL
                        ON ADL.AL_AllocationsID = ADL.AL_ORIG_AllocationsID
                    WHERE AL.AL_SchedulingTeamID = @newTeam
                    AND ADP.ADP_SchedulingPersonID = @spid;

                    DELETE AAP
                    FROM AllocationsAddPersons AAP
                    INNER JOIN Allocations AL
                        ON AL.AL_AllocationsID = AAP.AAP_AllocationsID
                    WHERE AAP.AAP_DutyDate >= @homeStart
                    AND AL.AL_SchedulingTeamID = @newTeam
                    AND AAP.AAP_SchedulingPersonID = @spid;

                    DELETE ADP
                    FROM AllocationsDelPersons ADP
                    INNER JOIN Allocations AL
                        ON ADP.ADP_AllocationsID = AL.AL_AllocationsID
                    INNER JOIN Allocations_DEL ADL
                        ON ADL.AL_AllocationsID = ADL.AL_ORIG_AllocationsID
                    WHERE AL.AL_SchedulingTeamID = @newTeam
                    AND ADP.ADP_SchedulingPersonID = @spid;
                ", [
            'audit'     => $p['AuditUserID'],
            'oldTeam'   => (int) $priorHome['TeamID'],
            'newTeam'   => (int) $p['SchedulingTeamID'],
            'spid'      => (int) $p['ScheduledPersonID'],
            'homeStart' => $p['HomeTeamStartDate'],
        ]);
    }

    /** Upsert additional teams for edit flow: insert new additional team links, and update existing additional team links. Also, for any additional team that is added or updated to be active during the home team span, ensure there is a non-scheduled viewer role in place for the person for that team and time span (mirroring SP logic of checking for existing viewer role and inserting if not found). */
    private function upsertAdditionalTeamsForEdit(array $p, array $parsedAdditional, array $existingLinks, ?int $viewerRoleId, ?int $historyType): void
    {
        foreach ($parsedAdditional as $at) {
            $isInsert = empty($at['SPTeamID']);

            if ($isInsert) {
                ScheduledPersonTeamLink::insert([
                    'ScheduledPersonID' => $p['ScheduledPersonID'],
                    'TeamID'            => $at['TeamID'],
                    'IsHomeTeam'        => $at['IsHomeTeam'] ?? 0,
                    'DisplayInViewScreen' => ($at['DisplayInViewScreen'] ?? 1),
                    'SortCode'          => $at['SortCode'],
                    'CreatedBy'         => $p['AuditUserID'],
                    'CreatedDate'       => DB::raw('GETDATE()'),
                    'StartDate'         => $at['StartDate'],
                    'EndDate'           => $at['EndDate'],
                    'LastUpdatedBy'     => $p['AuditUserID'],
                    'LastUpdatedDate'   => DB::raw('GETDATE()'),
                    'BackgroundColour'  => $at['Backcolor'],
                    'fontcolour'        => $at['Fontcolor'],
                    'IsActive'          => 1,
                    'IsAvailable'       => (int)($at['IsAvailable'] ?? 0),
                    'isDefault'         => 0,
                    'scheduledType'     => 1,
                    'IsDefaultBGColour' => (int)($at['IsDefaultBGColour'] ?? 0),
                ]);

                $this->maybeCreateNonScheduledGapAndViewerRole(
                    $p['ScheduledPersonID'],
                    $at,
                    $p['AuditUserID'],
                    $viewerRoleId,
                    $historyType
                );
            } else {
                ScheduledPersonTeamLink::where('SPTeamID', $at['SPTeamID'])
                    ->update([
                        'StartDate'         => $at['StartDate'],
                        'DisplayInViewScreen' => ($at['DisplayInViewScreen'] ?? 1),
                        'EndDate'           => $at['EndDate'],
                        'SortCode'          => $at['SortCode'],
                        'BackgroundColour'  => $at['Backcolor'],
                        'fontcolour'        => $at['Fontcolor'],
                        'IsAvailable'       => (int)($at['IsAvailable'] ?? 0),
                        'IsDefaultBGColour' => (int)($at['IsDefaultBGColour'] ?? 0),
                        'LastUpdatedBy'     => $p['AuditUserID'],
                        'LastUpdatedDate'   => DB::raw('GETDATE()'),
                    ]);

                $this->maybeCreateNonScheduledGapAndViewerRole(
                    $p['ScheduledPersonID'],
                    $at,
                    $p['AuditUserID'],
                    $viewerRoleId,
                    $historyType
                );
            }
        }
    }

    /** If the new home team is an archive team, apply the necessary side-effect of closing out any non-home team links for the person that are active beyond the new home team start date (mirroring SP logic). This is because archive teams are not meant to have any additional teams alongside them, so we need to ensure any existing additional teams are closed out if the person is being moved to an archive team. */
    private function applyArchiveSideEffectsIfNeeded(array $p): void
    {
        $homeTeamName = $this->getTeamName((int)$p['SchedulingTeamID']) ?? '';
        if (Str::startsWith($homeTeamName, 'Archive')) {
            ScheduledPersonTeamLink::where('ScheduledPersonID', $p['ScheduledPersonID'])
                ->where('IsHomeTeam', '!=', ScheduledPersonTeamLink::HOME_TEAM)
                ->where('scheduledType', 1)
                ->whereDate('EndDate', '>', $p['HomeTeamStartDate'])
                ->update([
                    'EndDate'         => Carbon::parse($p['HomeTeamStartDate'])->subDay()->format('Y-m-d'),
                    'IsActive'        => 0,
                    'LastUpdatedBy'   => $p['AuditUserID'],
                    'LastUpdatedDate' => DB::raw('GETDATE()'),
                ]);
        }
    }

    // ============================================================================================
    // CREATE: Inserts (mapped from SP)
    // ============================================================================================

    private function insertUserDetailsForCreate(array $p): int
    {
        $id = User::insertGetId([
            'UD_DisplayName'                      => $p['DisplayName'],
            'UD_DisplayFirstName'                 => $p['DisplayFirstName'],
            'UD_DisplayLastName'                  => $p['DisplayLastName'],
            'UD_AdminNotes'                       => $p['HomeTeamAdminNotes'],
            'UD_FWANotes'                         => $p['HomeTeamFWANotes'],
            'UD_CreatedBy'                        => $p['AuditUserID'],
            'UD_CreatedDate'                      => DB::raw('GETDATE()'),
            'UD_IsEligibleForAdditionalLeave'     => $p['IsAdditionalLeave'],
        ]);

        return (int)$id;
    }

    /** Insert home team link for create flow. This is done after inserting user details, because some of the fields for the home team link (e.g. colours) are coming from the form and not defaulted until the form is filled, so we need to wait until we have all the necessary data before inserting the home team link. */
    private function insertHomeTeamLink(array $p): void
    {
        ScheduledPersonTeamLink::insert([
            'ScheduledPersonID' => $p['ScheduledPersonID'],
            'TeamID'            => $p['SchedulingTeamID'],
            'IsHomeTeam'        => ScheduledPersonTeamLink::HOME_TEAM,
            'SortCode'          => $p['HomeTeamSortCode'],
            'CreatedBy'         => $p['AuditUserID'],
            'CreatedDate'       => DB::raw('GETDATE()'),
            'StartDate'         => $p['HomeTeamStartDate'],
            'EndDate'           => $p['HomeTeamEndDate'] ?? '9999-01-01',
            'LastUpdatedBy'     => $p['AuditUserID'],
            'LastUpdatedDate'   => DB::raw('GETDATE()'),
            'BackgroundColour'  => $p['HomeTeamBackColour'],
            'fontcolour'        => $p['HomeTeamFontColour'],
            'IsActive'          => 1,
            'IsAvailable'       => 0,
            'isDefault'         => 0,
            'scheduledType'     => 1,
            'IsDefaultBGColour' => $p['IsDefaultBGColour'],
        ]);
    }

    /** Insert additional team links for create flow. This is done after inserting home team link, because we need the home team link to be in place first to properly set the start date for the additional teams (which cannot start before the home team start date). Also, we need to skip inserting an additional team link for the scheduling team if it is included in the additional teams list, because the scheduling team is already being inserted as a home team link, and we don't want duplicate links for the same team. */
    private function insertAdditionalTeamsForCreate(array $p, array $parsedAdditional): void
    {
        foreach ($parsedAdditional as $at) {
            if ((int)$at['TeamID'] === (int)$p['SchedulingTeamID'] && $at['IsHomeTeam'] != ScheduledPersonTeamLink::FUTURE_HOME_ADDITIONAL_TEAM) {
                continue;
            }
            ScheduledPersonTeamLink::insert([
                'ScheduledPersonID' => $p['ScheduledPersonID'],
                'TeamID'            => $at['TeamID'],
                'IsHomeTeam'        => $at['IsHomeTeam'] ?? 0,
                'DisplayInViewScreen' => ($at['DisplayInViewScreen'] ?? 1),
                'SortCode'          => $at['SortCode'],
                'CreatedBy'         => $p['AuditUserID'],
                'CreatedDate'       => DB::raw('GETDATE()'),
                'StartDate'         => $at['StartDate'],
                'EndDate'           => $at['EndDate'],
                'BackgroundColour'  => $at['Backcolor'],
                'fontcolour'        => $at['Fontcolor'],
                'IsActive'          => 1,
                'IsAvailable'       => (int)($at['IsAvailable'] ?? 0),
                'isDefault'         => 0,
                'scheduledType'     => 1,
                'IsDefaultBGColour' => (int)($at['IsDefaultBGColour'] ?? 0),
            ]);

            $this->maybeCreateNonScheduledGapAndViewerRole(
                $p['ScheduledPersonID'],
                $at,
                $p['AuditUserID'],
                $this->getRoleIdByName('Scheduling Team Viewer'),
                $this->getHistoryTypeId('NonScheduledTeamStaff')
            );
        }
    }

    /** If the home team start date is in the future, create a temporary non-scheduled viewer link for the scheduling team covering the gap between today and the home team start date, to mirror the SP logic of allowing future-dated home team links to be created by ensuring there is a viewer role in place for the person for that team for any active window that covers the home team start date. This is to ensure that if a home team link is created with a future start date, the person will still have access to the schedule for that team in the period leading up to the home team start date, which is important for scenarios where schedules are published in advance. */

    private function maybeCreateTemporaryNonScheduledHomeGap(array $p, array $parsedAdditionalTeams): void
    {
        $futureHomeTeam =  collect($parsedAdditionalTeams)->where('TeamID', $p['SchedulingTeamID'])->where('IsHomeTeam', ScheduledPersonTeamLink::FUTURE_HOME_ADDITIONAL_TEAM)->first();
        $homeStartDate = $futureHomeTeam != null ? $futureHomeTeam['StartDate'] : $p['HomeTeamStartDate'];
        if (Carbon::parse($homeStartDate)->gt(Carbon::today())) {
            $today = Carbon::today();
            $gapEnd = Carbon::parse($homeStartDate)->subDay()->format('Y-m-d');

            // Close existing non-scheduled span for the same team covering today
            $affected = ScheduledPersonTeamLink::where('ScheduledPersonID', $p['ScheduledPersonID'])
                ->where('TeamID', $p['SchedulingTeamID'])
                ->where('IsHomeTeam', ScheduledPersonTeamLink::ADDITIONAL_TEAM)
                ->where('scheduledType', 0)
                ->whereDate('StartDate', '<=', $today->format('Y-m-d'))
                ->whereDate('EndDate', '>=', $today->format('Y-m-d'))
                ->update([
                    'EndDate' => $gapEnd,
                    'LastUpdatedBy' => $p['AuditUserID'],
                    'LastUpdatedDate' => DB::raw('GETDATE()'),
                ]);

            // Only insert if no row was updated and the exact gap row doesn't already exist
            if ($affected == 0) {
                $exists = ScheduledPersonTeamLink::where('ScheduledPersonID', $p['ScheduledPersonID'])
                    ->where('TeamID', $p['SchedulingTeamID'])
                    ->where('IsHomeTeam', ScheduledPersonTeamLink::ADDITIONAL_TEAM)
                    ->where('scheduledType', 0)
                    ->whereDate('StartDate', '=', $today->format('Y-m-d'))
                    ->whereDate('EndDate', '=', $gapEnd)
                    ->exists();
                if (!$exists) {
                    ScheduledPersonTeamLink::insert([
                        'ScheduledPersonID' => $p['ScheduledPersonID'],
                        'TeamID' => $p['SchedulingTeamID'],
                        'IsHomeTeam' => ScheduledPersonTeamLink::ADDITIONAL_TEAM,
                        'SortCode' => $p['HomeTeamSortCode'] ?? null,
                        'CreatedBy' => $p['AuditUserID'],
                        'CreatedDate' => DB::raw('GETDATE()'),
                        'StartDate' => $today->format('Y-m-d'),
                        'EndDate' => $gapEnd,
                        'LastUpdatedBy' => $p['AuditUserID'],
                        'LastUpdatedDate' => DB::raw('GETDATE()'),
                        'BackgroundColour' => $p['HomeTeamBackColour'] ?? null,
                        'fontcolour' => $p['HomeTeamFontColour'] ?? null,
                        'IsActive' => 1,
                        'IsAvailable' => 0,
                        'isDefault' => 0,
                        'scheduledType' => 0,
                        'IsDefaultBGColour' => (int)$p['IsDefaultBGColour'],
                    ]);
                }
            }

            $this->applyViewerRoleAndHistory(
                (int)$p['ScheduledPersonID'],
                (int)$p['SchedulingTeamID'],
                $today->format('Y-m-d'),
                $gapEnd,
                (int)$p['AuditUserID'],
                $this->getRoleIdByName('Scheduling Team Viewer'),
                $this->getHistoryTypeId('NonScheduledTeamStaff')
            );
        }
    }


    /** Insert add person records for any allocations that the person is currently scheduled on in the period leading up to the home team start date, for any additional teams that are active during that period, to mirror the SP logic of ensuring that if a home team link is created with a future start date, and there are additional teams that are active during the period leading up to the home team start date, the person will be added as a non-scheduled person on those allocations for those additional teams for any active windows that cover the home team start date. This is important for scenarios where schedules are published in advance and there are additional teams that need to be included in the schedule leading up to the home team start date. */
    private function seedAddPersonsFromAvailabilityWindows(array $p): void
    {
        DB::statement("
            INSERT INTO AllocationsAddPersons (
                AAP_AllocationsID, AAP_AllocationsSPID, AAP_SchedulingPersonID, AAP_Status, AAP_iDay, AAP_DutyDate, AAP_CreatedBy, AAP_CreatedDate
            )
            SELECT AL1.AL_AllocationsID,
                   AP.ASP_AllocationsSPID,
                   AP.ASP_SchedulingPersonID,
                   1,
                   AP.ASP_iDay,
                   AP.ASP_DutyDate,
                   :audit,
                   GETDATE()
            FROM Allocations AL
            INNER JOIN ScheduledPersonTeam_LINK SL  ON SL.TeamID = AL.AL_SchedulingTeamID
            INNER JOIN ScheduledPersonTeam_LINK SLA ON SLA.ScheduledPersonID = SL.ScheduledPersonID
            INNER JOIN AllocationsScheduledPersons AP ON AL.AL_AllocationsID = AP.ASP_AllocationsID
                                                      AND SLA.ScheduledPersonID = AP.ASP_SchedulingPersonID
            INNER JOIN Allocations AL1 ON SLA.TeamID = AL1.AL_SchedulingTeamID
                                      AND AL.AL_WeekNumber = AL1.AL_WeekNumber
            WHERE AP.ASP_DutyDate BETWEEN SL.StartDate AND SL.EndDate
              AND AP.ASP_DutyDate BETWEEN SLA.StartDate AND SLA.EndDate
              AND SL.scheduledtype = 1
              AND SL.IsHomeTeam = 1
              AND SLA.scheduledtype = 1
              AND SLA.IsHomeTeam IN (0,2)
              AND SLA.IsAvailable = 1
              AND SL.ScheduledPersonID = :spid
              AND SLA.StartDate > DATEADD(MONTH, -3, GETDATE())
              AND SLA.IsAvailable = 1
              AND NOT EXISTS (
                    SELECT 1 FROM AllocationsAddPersons AAP
                    WHERE AAP.AAP_AllocationsSPID = AP.ASP_AllocationsSPID
                      AND AAP.AAP_SchedulingPersonID = SLA.ScheduledPersonID
                      AND AAP.AAP_AllocationsID = AL1.AL_AllocationsID
              );
        ", [
            'audit' => $p['AuditUserID'],
            'spid'  => $p['ScheduledPersonID'],
        ]);
    }

    // ============================================================================================
    // Shared helpers: UserDetails updates, Viewer roles, History, Error log
    // ============================================================================================

    private function updateUserDetails(array $p): void
    {
        User::where('UD_UserID', $p['ScheduledPersonID'])
            ->update([
                'UD_DisplayName'                      => $p['DisplayName'],
                'UD_DisplayFirstName'                 => $p['DisplayFirstName'],
                'UD_DisplayLastName'                  => $p['DisplayLastName'],
                'UD_UpdatedBy'                        => $p['AuditUserID'],
                'UD_UpdatedDate'                      => DB::raw('GETDATE()'),
                'UD_AdminNotes'                       => $p['HomeTeamAdminNotes'],
                'UD_FWANotes'                         => $p['HomeTeamFWANotes'],
                'UD_IsEligibleForAdditionalLeave'     => $p['IsAdditionalLeave'],
            ]);
    }

    /** If the additional team is starting in the future, create a temporary non-scheduled viewer link for that team covering the gap between today and the additional team start date, to mirror the SP logic of allowing future-dated additional team links to be created by ensuring there is a viewer role in place for the person for that team for any active window that covers the additional team start date. This is to ensure that if an additional team link is created with a future start date, the person will still have access to the schedule for that team in the period leading up to the additional team start date, which is important for scenarios where schedules are published in advance. */

    private function maybeCreateNonScheduledGapAndViewerRole(int $spId, array $at, int $auditUserId, ?int $viewerRoleId, ?int $historyType): void
    {
        // Guard against missing dates/ids
        if (empty($at['StartDate']) || empty($at['TeamID'])) {
            return;
        }
        $today = Carbon::today();
        $start = Carbon::parse($at['StartDate']);
        if ($start->gt($today)) {
            $gapEnd = $start->copy()->subDay()->format('Y-m-d');

            // Close an existing non-scheduled (viewer) span covering today, if any
            $affected = ScheduledPersonTeamLink::where('ScheduledPersonID', $spId)
                ->where('TeamID', $at['TeamID'])
                ->where('IsHomeTeam', ScheduledPersonTeamLink::ADDITIONAL_TEAM)
                ->where('scheduledType', 0)
                ->whereDate('StartDate', '<=', $today->format('Y-m-d'))
                ->whereDate('EndDate', '>=', $today->format('Y-m-d'))
                ->update([
                    'EndDate' => $gapEnd,
                    'LastUpdatedBy' => $auditUserId,
                    'LastUpdatedDate' => DB::raw('GETDATE()'),
                ]);

            // Insert only if nothing was updated (idempotent) and an identical span doesn't already exist
            if ($affected == 0) {
                $exists = ScheduledPersonTeamLink::where('ScheduledPersonID', $spId)
                    ->where('TeamID', $at['TeamID'])
                    ->where('IsHomeTeam', ScheduledPersonTeamLink::ADDITIONAL_TEAM)
                    ->where('scheduledType', 0)
                    ->whereDate('StartDate', '=', $today->format('Y-m-d'))
                    ->whereDate('EndDate', '=', $gapEnd)
                    ->exists();

                if (!$exists) {
                    ScheduledPersonTeamLink::insert([
                        'ScheduledPersonID' => $spId,
                        'TeamID' => $at['TeamID'],
                        'IsHomeTeam' => ScheduledPersonTeamLink::ADDITIONAL_TEAM,
                        'SortCode' => $at['SortCode'] ?? null,
                        'CreatedBy' => $auditUserId,
                        'CreatedDate' => DB::raw('GETDATE()'),
                        'StartDate' => $today->format('Y-m-d'),
                        'EndDate' => $gapEnd,
                        'LastUpdatedBy' => $auditUserId,
                        'LastUpdatedDate' => DB::raw('GETDATE()'),
                        'BackgroundColour' => $at['Backcolor'] ?? null,
                        'fontcolour' => $at['Fontcolor'] ?? null,
                        'IsActive' => 0,
                        'IsAvailable' => 0,
                        'isDefault' => 0,
                        'scheduledType' => 0,
                        'IsDefaultBGColour' => (int)($at['IsDefaultBGColour'] ?? 0),
                    ]);
                }
            }

            // Ensure a viewer role spans the same temporary gap (update if exists, otherwise insert)
            $this->applyViewerRoleAndHistory(
                $spId,
                (int)$at['TeamID'],
                $today->format('Y-m-d'),
                $gapEnd,
                $auditUserId,
                $viewerRoleId,
                $historyType
            );
        }
    }


    /** Checks if there is an existing viewer role for the person for the team that is active during the start date of the team link, and if not, creates a viewer role for that team covering the same span as the team link. This is to mirror the SP logic of ensuring that there is a viewer role in place for the person for the team for any active window that covers the start date of the team link, which is important for scenarios where schedules are published in advance and we want to ensure the person has access to the schedule for that team in the period leading up to the start date of the team link. Also inserts a history record if a history type id is provided, with details of the viewer role assignment. */
    private function applyViewerRoleAndHistory(int $spId, int $teamId, string $startDate, string $endDate, int $auditUserId, ?int $viewerRoleId, ?int $historyType): void
    {
        if (!$viewerRoleId) {
            return;
        }

        $exists = UserRole::where('UR_UserID', $spId)
            ->where('UR_SchedulingTeamID', $teamId)
            ->whereDate('UR_StartDate', '<=', $startDate)
            ->whereDate('UR_EndDate', '>=', $startDate)
            ->exists();

        if ($exists) {
            UserRole::where('UR_UserID', $spId)
                ->where('UR_SchedulingTeamID', $teamId)
                ->whereDate('UR_StartDate', '<=', $startDate)
                ->whereDate('UR_EndDate', '>=', $startDate)
                ->update([
                    'UR_EndDate'     => $endDate,
                    'UR_UpdatedBy'   => $auditUserId,
                    'UR_UpdatedDate' => DB::raw('GETUTCDATE()'),
                ]);
        } else {
            UserRole::insert([
                'UR_UserID'          => $spId,
                'UR_SchedulingTeamID' => $teamId,
                'UR_StartDate'       => $startDate,
                'UR_EndDate'         => $endDate,
                'UR_RoleID'          => $viewerRoleId,
                'UR_CreatedBy'       => $auditUserId,
                'UR_CreatedDate'     => DB::raw('GETUTCDATE()'),
            ]);
        }

        if ($historyType) {
            $auditor = $this->getUserDisplayName($auditUserId) ?? '';
            $team    = $this->getTeamName($teamId) ?? '';

            $hist = "Scheduling Team Viewer role assigned by {$auditor} on " . Carbon::now()->format('d/m/Y') .
                " at " . Carbon::now()->format('H:i') . ". \n.<br>" .
                "{$team} permission assigned by {$auditor} on " . Carbon::now()->format('d/m/Y') .
                " at " . Carbon::now()->format('H:i') . ".";

            DB::table('AdditionalPermissionsHistory')->insert([
                'HistoryType'  => $historyType,
                'UserID'       => $auditUserId,
                'History'      => $hist,
                'datetime'     => DB::raw('GETDATE()'),
                'AttributeID'  => $teamId,
                'AttributeID2' => $spId,
            ]);
        }
    }

    /** Logs an error to the ErrorLog table with details of the exception and the user performing the action, to mirror the SP logic of logging errors that occur during the create or update process. This is important for troubleshooting and auditing purposes, to have a record of any errors that occur along with the context of who was performing the action when the error occurred. */
    private function logError(\Throwable $e, ?int $auditUserId): void
    {
        Log::error('Create update scheduled person', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        DB::table('ErrorLog')->insert([
            'ErrorNumber'   => method_exists($e, 'getCode') ? (int)$e->getCode() : 0,
            'ErrorState'    => 0,
            'ErrorSeverity' => 0,
            'ErrorProcedure' => 'SchedulePersonMonolithicRepository::createOrUpdate',
            'ErrorLine'     => 0,
            'ErrorMessage'  => substr($e->getMessage() . ' trace - ' . $e->getTraceAsString(), 0, 4000),
            'ErrorDateTime' => DB::raw('GETUTCDATE()'),
            'UserName'      => $auditUserId,
        ]);
    }
}
