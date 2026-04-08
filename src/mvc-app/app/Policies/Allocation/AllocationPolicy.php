<?php

namespace App\Policies\Allocation;

use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use App\Models\User\RefRole;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class AllocationPolicy
{
    use HandlesAuthorization;

    public $userdetails;
    public $isAreaAdmin;
    public $hasEditAllocation;

    /**
     * Determine whether the user can view Allocation.
     *
     * @param User $user
     * @return bool
     */
    public function view(User $user, SchedulingTeam $schedulingTeam): bool
    {
        $this->getUserDetailsByTeam($user, $schedulingTeam);
        return Gate::check('team-permissions', [$schedulingTeam, 'editweekly_allocateteam_view']);
    }

    /**
     * Determine whether the user can view Allocation.
     *
     * @param User $user
     * @return bool
     */
    public function viewEditWeekly(User $user, SchedulingTeam $schedulingTeam): bool
    {
        return Gate::check('team-permissions', [$schedulingTeam, 'editweekly_allocateteam_admin']);
    }

    /**
     * Determine whether the user can view Allocation.
     *
     * @param User $user
     * @return bool
     */
    public function viewEditYear(User $user, SchedulingTeam $schedulingTeam): bool
    {
        return Gate::check('team-permissions', [$schedulingTeam, 'edityear_allocateteam']);
    }

    /**
     * Determine whether the user can create Allocation.
     *
     * @param User $user
     * @param SchedulingTeam $schedulingTeam scheduling team mdoel instance
     * @return bool
     */
    public function create(User $user, SchedulingTeam $schedulingTeam): bool
    {
        return $this->view($user, $schedulingTeam);
    }

    /**
     * Determine whether the user can update Allocation.
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
     * Determine whether the user can delete Allocation.
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
        $this->hasEditAllocation = $user->userRoles->where('UR_SchedulingTeamID', $team->schedulingTeamId)->where('RoleName', RefRole::EDIT_ALL_ALLOCATIONS)->count() > 0 ? 1 : 0;
    }
}
