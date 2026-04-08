<?php

namespace App\Repositories\Contracts\Admin;

interface CreateScheduledPersonRepositoryInterface
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
    ): array;
}
