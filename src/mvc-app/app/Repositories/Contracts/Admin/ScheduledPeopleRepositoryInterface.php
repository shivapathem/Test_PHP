<?php

namespace App\Repositories\Contracts\Admin;

interface ScheduledPeopleRepositoryInterface
{
    // Teams / lists
    public function getSchedulingTeams(int $userId, string $actionType, int $pageId, int $schedulePersonId): array;

    // Create/Update scheduled person
    public function createSchedulePerson(
        int $spTeamId,
        string $displayName,
        int $schedulingTeamId,
        string $homeTeamStartDate,
        string $homeTeamEndDate,
        string $homeTeamSortCode,
        string $homeTeamBackColour,
        string $homeTeamFontColour,
        string $homeTeamAdminNotes,
        string $homeTeamFWANotes,
        string $actionType,
        int $scheduledPersonId,
        string $additionalTeamArray,
        int $auditUserId,
        string $displayFirstName,
        string $displayLastName,
        int $isDefaultBGColour,
        int $isAdditionalLeave
    );

    // Staff details config (search)
    public function getStaffDetailsConfig(
        ?string $searchForeName,
        ?string $searchNetLogin,
        ?string $searchSurName,
        ?string $searchStaffNumber
    ): array;

    // Attach/Update staff link
    public function attachStaffDetailsConfig(
        int $scheduledPersonId,
        int $staffId,
        string $actionType,
        string $status,
        string $returnString,
        int $userID,
        int $oldScheduledPersonID
    ): array;

    // Person teams/details
    public function getSchedulepersondetails(int $schedulePersonId): array;

    public function getPersonDetails(int $schedulePersonId): array; // alias, for controller compatibility

    // Home team checks
    public function validateAddHomeTeamsStartDate(int $schedulePersonId): ?array;

    public function validateAddTeamBetweenAnyExistingHomeTeamsDuration(int $schedulePersonId): array;

    // Additional team validations
    public function validateAddTeamsEndDate(
        int $ddlteamsid,
        int $schedulepersonid,
        string $addteamenddate,
        string $addteamstartdate,
        int $addteamsid
    ): ?array;

    public function validateAddTeamBetweenAnyExistingAdditioanlTeam(
        int $schedulepersonid,
        int $teamid,
        string $addteamstartdate,
        string $addteamenddate
    ): array;

    // Mutations
    public function changeDisplayName(int $schedulepersonid, string $newName): void;

    // Checks
    public function checkScheduledType(int $schedulepersonid): int;

    public function deleteHomeTeam(int $homeTeamId, int $personId, int $deleteFromRota, int $auditUserId): array;

    public function validateSchPersonHomeTeamHaveRota(int $homeTeamId, int $scheduledPersonId): array;

    public function notifySchedulingTeamOfRemovedDuties(array $dutyIds, int $personId, array $dutiesToDeleteIDs = [], array $dutiesToUnallocateIDs = [], $currentUser = null): void;
    public function getConflictingDutiesForHomeTeamDeletion(int $homeTeamId, int $scheduledPersonId): array;
    public function handleAdditionalTeamAfterDutyRemoval(int $personId, int $homeTeamId): void;

    // Staff details save
    public function getStaffDetailsByPersonId(int $scheduledPersonId): ?array;

    public function updateStaffDetails(int $id, array $payload): int;

    // Scheduled people list

    public function getScheduledPeopleUserList(string $teamID, string $userName, int $pageid, int $excludeNoTeam): array;

    // Contract history
    public function getContractHistory(int $schedulePersonId): array;

    // Schedule Team History
    public function getScheduleTeamHistory(int $scheduledPersonId, ?int $userId = null): array;

    // Check if future duties exist for a person's home team
    public function checkFutureHomeTeamHasDuties(int $personId, int $homeTeamId): bool;

    /**
     * Bulk unassign duties using usp_Edit_Allocations 'UNASSIGN'
     * Handles AD_IsNeedCovering cases, replaces direct DB ops
     *
     * @param array $aspIds ASP_AllocationsSPID array
     * @param int $personId
     * @param string $netLogin Auth UD_NetLogin
     * @param int|null $allocationsId Default null (query from ASP)
     * @return array ['success_count' => int, 'errors' => []]
     */
    public function bulkUnassignDuties(array $aspIds, int $personId, string $netLogin, ?int $allocationsId = null): array;
}
