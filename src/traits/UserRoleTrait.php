<?php

namespace Traits;

use App\Models\Scheduling\SchedulingTeam;
use App\Models\User\RefRole;
use Illuminate\Support\Facades\Gate;

include_once __DIR__ . "../../function-includes/laravel_init.php";

trait UserRoleTrait
{
    /**
     * Check roles exists for a team
     * 
     * @param mixed $schedulingTeam
     * @param string $roleName
     */
    public function checkSchedulingTeamRoleExists(int $schedulingTeam, string $roleName)
    {
        return auth()->user()->userRoles
            ->where('UR_SchedulingTeamID', $schedulingTeam)
            ->where('RoleName', $roleName)->count() > 0 ? 1 : 0;
    }

    /**
     * Check edit weekly admin role
     * 
     * @param mixed $schedulingTeam
     * @param string $roleName
     */
    public function checkEditWeeeklyAdminRole(int $schedulingTeam)
    {
        return Gate::check('team-permissions', [SchedulingTeam::find($schedulingTeam), 'editweekly_allocateteam_admin']) ?  1 : 0;
    }

    /**
     * Check Skill admin role
     * 
     * @param mixed $schedulingTeam
     */
    public function checkSkillAdminRole($schedulingTeam)
    {
        if ($schedulingTeam instanceof SchedulingTeam) {
            $schedulingTeam = $schedulingTeam;
        } else {
            $schedulingTeam = SchedulingTeam::find($schedulingTeam);
        }
        return Gate::check('team-permissions', [$schedulingTeam, 'team-skill-admin']) ?  1 : 0;
    }

    /**
     * Check if user is system admin
     */
    public function isSystemAdmin()
    {
        return auth()->user()->userRoles->where('RoleName', RefRole::SYSTEM_ADMIN)->count() > 0 ? 1 : 0;
    }

    /**
     * Check if user is area admin based on scheduled team
     */
    public function isAreaAdminByTeam(int $schedulingTeam)
    {
        return auth()->user()->getAreasRoles->where('RoleName', RefRole::AREA_ADMIN)->where('DivisionID', SchedulingTeam::find($schedulingTeam)->DivisionID)->count() > 0 ? 1 : 0;
    }
}
