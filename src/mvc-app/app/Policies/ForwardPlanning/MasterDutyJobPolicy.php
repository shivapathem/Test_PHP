<?php

namespace App\Policies\ForwardPlanning;

use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use App\Models\User\RefRole;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class MasterDutyJobPolicy
{
    use HandlesAuthorization;

    public $userdetails;
    public $isAreaAdmin;
    public $hasEditMasterDutyRole;

    /**
     * Determine whether the user can view Duty.
     *
     * @param User $user
     * @return bool
     */
    public function view(User $user, ?SchedulingTeam $schedulingTeam = null): bool
    {
        if ($schedulingTeam == null) {
            return Gate::any(['system-or-divisional-admin', 'has-scheduler']);
        } else {
            $this->getUserDetailsByTeam($user, $schedulingTeam);
            if ($user->isSystemAdmin == 1 || $this->isAreaAdmin == 1 || $this->hasEditMasterDutyRole == 1) {
                return true;
            }
            if ($this->userdetails && ($this->userdetails->scheduling_team_admin >= 1 || $this->userdetails->scheduler >= 1)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can create Duty.
     *
     * @param User $user
     * @param SchedulingTeam $schedulingTeam scheduling team mdoel instance
     * @return bool
     */
    public function create(User $user, SchedulingTeam $schedulingTeam): bool
    {
        $this->getUserDetailsByTeam($user, $schedulingTeam);
        if ($user->isSystemAdmin == 1 || $this->isAreaAdmin == 1 || $this->hasEditMasterDutyRole) {
            return true;
        }
        if ($this->userdetails && ($this->userdetails->scheduling_team_admin >= 1)) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update Duty.
     *
     * @param User $user
     * @param mixed $schedulingTeam scheduling team mdoel instance
     * @return bool
     */
    public function update(User $user, SchedulingTeam $schedulingTeam): bool
    {
        return $this->create($user, $schedulingTeam);
    }

    /**
     * Determine whether the user can delete Duty.
     *
     * @param User $user
     * @param mixed $schedulingTeam scheduling team mdoel instance
     * @return bool
     */
    public function delete(User $user, SchedulingTeam $schedulingTeam): bool
    {
        return $this->create($user, $schedulingTeam);
    }

    /**
     *
     */
    private function getUserDetailsByTeam(User $user, SchedulingTeam $team)
    {
        $index = array_search($team->schedulingTeamId, array_column($user->userSetup, 'schedulingteamid'));
        $this->userdetails = $index !== false ? $user->userSetup[$index] : null;
        $this->isAreaAdmin = $user->getAreasRoles->where('RoleName', RefRole::AREA_ADMIN)->where('DivisionID', $team->divisionid)->count() > 0 ?  1 : 0;
        $this->hasEditMasterDutyRole = $user->userRoles->where('UR_SchedulingTeamID', $team->schedulingTeamId)->where('RoleName', RefRole::EDIT_MASTER_DUTIES)->count() > 0 ? 1 : 0;
    }
}
