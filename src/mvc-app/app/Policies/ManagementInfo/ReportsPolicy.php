<?php

namespace App\Policies\ManagementInfo;

use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use App\Models\User\RefRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportsPolicy
{
    use HandlesAuthorization;

    public $userdetails;
    public $isAreaAdmin;
    public $hasAdvancedReporting;
    public $hasBasicReporting;

    /**
     * Determine whether the user can view basic reports.
     *
     * @param User $user
     * @return bool
     */
    public function viewBasicReports(User $user, SchedulingTeam $schedulingTeam): bool
    {
        $this->getUserDetailsByTeam($user, $schedulingTeam);
        if ($user->isSystemAdmin == 1 || $this->isAreaAdmin == 1 || $this->hasBasicReporting == 1) {
            return true;
        }
        if ($this->userdetails && ($this->userdetails->scheduling_team_admin >= 1)) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view basic reports.
     *
     * @param User $user
     * @return bool
     */
    public function viewAdvancedReports(User $user, SchedulingTeam $schedulingTeam): bool
    {
        $this->getUserDetailsByTeam($user, $schedulingTeam);
        if ($user->isSystemAdmin == 1 || $this->isAreaAdmin == 1 || $this->hasAdvancedReporting == 1) {
            return true;
        }
        if ($this->userdetails && ($this->userdetails->scheduling_team_admin >= 1)) {
            return true;
        }
        return false;
    }

    /**
     *
     */
    private function getUserDetailsByTeam(User $user, SchedulingTeam $team)
    {
        $index = array_search($team->schedulingTeamId, array_column($user->userSetup, 'schedulingteamid'));
        $this->userdetails = $index !== false ? $user->userSetup[$index] : null;
        $this->isAreaAdmin = $user->getAreasRoles->where('RoleName', RefRole::AREA_ADMIN)->where('DivisionID', $team->divisionid)->count() > 0 ?  1 : 0;
        $this->hasAdvancedReporting = $user->userRoles->where('UR_SchedulingTeamID', $team->schedulingTeamId)->where('RoleName', RefRole::BASIC_REPORTS)->count() > 0 ? 1 : 0;
        $this->hasBasicReporting = $user->userRoles->where('UR_SchedulingTeamID', $team->schedulingTeamId)->where('RoleName', RefRole::ADVANCED_REPORTS)->count() > 0 ? 1 : 0;
    }
}
