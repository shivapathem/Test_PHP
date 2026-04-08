<?php

namespace App\Providers;

use App\Models\Scheduling\SchedulingTeam;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\User\RefRole;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\Facility\Facility::class => \App\Policies\FacilityPolicy::class,
        \App\Models\Scheduling\SchedulingGroup::class => \App\Policies\SchedulingGroupPolicy::class,
        'allocate-user' => \App\Policies\Admin\AllocateUser\AllocateUserPolicy::class,
        'scheduled-people' => \App\Policies\Setup\ScheduledPeoplePolicy::class,
        'master-duty-job' => \App\Policies\ForwardPlanning\MasterDutyJobPolicy::class,
        'rota-pattern' => \App\Policies\ForwardPlanning\RotaPatternPolicy::class,
        'allocation-policy' => \App\Policies\Allocation\AllocationPolicy::class,
        'reports-policy' => \App\Policies\ManagementInfo\ReportsPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('system-admin', function (User $user) {
            if ($user->isSystemAdmin == 1) {
                return true;
            } else {
                return false;
            }
        });
        Gate::define('divisional-admin', function (User $user) {
            return $user->isDivisionalAdmin;
        });

        Gate::define('system-or-divisional-admin', function (User $user) {
            return $user->isDivisionalAdmin == 1 || $user->isSystemAdmin == 1 ? true : false;
        });
        Gate::define('has-team', function (User $user) {
            return $user->hasTeam;
        });
        Gate::define('has-hometeam', function (User $user) {
            return $user->homeTeam == 1 ? true : false;
        });
        Gate::define('has-scheduler', function (User $user) {
            if (($user->hasTeam) && ($user->isSchedulingTeamAdmin == 1 || $user->isScheduler == 1)) {
                return true;
            } else {
                return false;
            }
        });
        Gate::define('is-scheduleperson', function (User $user) {
            return $user->isScheduledPerson;
        });
        Gate::define('team-permissions', function (User $user, SchedulingTeam $team, string $permission) {
            $index = array_search($team->schedulingTeamId, array_column($user->userSetup, 'schedulingteamid'));
            $userdetails = $index !== false ? $user->userSetup[$index] : null;
            $isAreaAdmin = $user->getAreasRoles->where('RoleName', RefRole::AREA_ADMIN)->where('DivisionID', $team->divisionid)->count() > 0 ?  1 : 0;
            $hasEditAllocation = $user->userRoles->where('UR_SchedulingTeamID', $team->schedulingTeamId)->where('RoleName', RefRole::EDIT_ALL_ALLOCATIONS)->count() > 0 ? 1 : 0;
            switch ($permission) {
                case 'editweekly_allocateteam_admin':
                case 'editweekly_allocateteam_view':
                    if ($user->isSystemAdmin == 1 || $isAreaAdmin == 1 || $hasEditAllocation == 1) {
                        return true;
                    }
                    if ($userdetails && ($userdetails->scheduling_team_admin >= 1
                        || ($userdetails->scheduler >= 1 && $permission == 'editweekly_allocateteam_view')
                        || ($userdetails->team_leader >= 1 && $permission == 'editweekly_allocateteam_view')
                        || ($userdetails->scheduling_team_viewer >= 1 && $permission == 'editweekly_allocateteam_view'))) {
                        return true;
                    }
                    return false;
                    break;
                case 'edityear_allocateteam':
                    if ($team->IsShowEditYearly != 1) {
                        return false;
                    }
                    if ($user->isSystemAdmin == 1 || $isAreaAdmin == 1 || $hasEditAllocation == 1) {
                        return true;
                    }
                    if ($userdetails && $userdetails->isshowedityearly >= 1) {
                        return true;
                    }
                    return false;
                    break;
                case 'edit_prodview':
                    if ($team->showProductionView != 1) {
                        return false;
                    }
                    if ($user->isSystemAdmin == 1 || $isAreaAdmin == 1 || $hasEditAllocation == 1) {
                        return true;
                    }
                    if ($userdetails && $userdetails->showproductionview >= 1) {
                        return true;
                    }
                    return false;
                    break;
                case 'team-xmas-point':
                    if ($user->isSystemAdmin == 1 || $isAreaAdmin == 1) {
                        return true;
                    }
                    if (($userdetails && $userdetails->hasxmaspoints >= 1) || ($userdetails->scheduling_team_admin >= 1)) {
                        return true;
                    }
                    $hasEditLeaveCredits = $user->userRoles->where('UR_SchedulingTeamID', $team->schedulingTeamId)->where('RoleName', RefRole::EDIT_LEAVE_CREDITS)->count() > 0 ? 1 : 0;
                    if ($hasEditLeaveCredits == 1) {
                        return true;
                    }
                    return false;
                    break;
                case 'team-skill-admin':
                    if ($user->isSystemAdmin == 1 || $isAreaAdmin == 1) {
                        return true;
                    }
                    if (($userdetails->scheduling_team_admin >= 1)) {
                        return true;
                    }
                    $hasSkillAdmin = $user->userRoles->where('UR_SchedulingTeamID', $team->schedulingTeamId)->where('RoleName', RefRole::SKILLS_ADMIN)->count() > 0 ? 1 : 0;
                    if ($hasSkillAdmin == 1) {
                        return true;
                    }
                    return false;
                    break;
            }
        });
        Gate::define('has-shiftleader-manager', function (User $user) {
            if (($user->hasTeam) && ($user->isShiftLeader == 1 || $user->isManager == 1)) {
                return true;
            } else {
                return false;
            }
        });
        Gate::define('has-shiftleader', function (User $user) {
            if (($user->hasTeam) && ($user->isShiftLeader == 1)) {
                return true;
            } else {
                return false;
            }
        });
        Gate::define('has-shiftleader-scheduler', function (User $user) {
            if (($user->isShiftLeader == 1 || $user->isSchedulingTeamAdmin == 1 || $user->isScheduler == 1)) {
                return true;
            } else {
                return false;
            }
        });
        Gate::define('has-handover', function (User $user) {
            return $user->hasHandovers;
        });
        Gate::define('has-report', function (User $user) {
            if (($user->isBasicReports == 1) || ($user->isAdvancedReports == 1) || ($user->isDivisionalAdmin == 1) || ($user->isSystemAdmin == 1)) {
                return true;
            } else {
                return false;
            }
        });
        Gate::define('is-advancereport', function (User $user) {
            if (($user->isAdvancedReports == 1) || ($user->isDivisionalAdmin == 1) || ($user->isSystemAdmin == 1)) {
                return true;
            } else {
                return false;
            }
        });
        Gate::define('is-skilladmin', function (User $user) {
            return $user->isSchedulingTeamAdmin || $user->isSkillsAdmin || $user->isSystemAdmin || $user->isDivisionalAdmin;
        });
        Gate::define('is-facilityadmin', function (User $user) {
            return $user->isFacilityAdministrator;
        });
        Gate::define('has-scheduledadmin', function (User $user) {
            return $user->hasTeam == 1 && $user->isSchedulingTeamAdmin == 1 ? true : false;
        });
        Gate::define('is-areareport', function (User $user) {
            return $user->isAreaReports;
        });
        Gate::define('is-leave', function (User $user) {
            return $user->hasLeave;
        });
        Gate::define('is-leaveAdmin', function (User $user) {
            return $user->hasLeaveAdmin;
        });
        Gate::define('is-requestadmin-or-request', function (User $user) {
            return $user->hasRequest == 1 || $user->hasRequestAdmin == 1 ? true : false;;
        });
        Gate::define('is-request', function (User $user) {
            return $user->hasRequest;
        });
        Gate::define('is-requestadmin', function (User $user) {
            return $user->hasRequestAdmin;
        });
        Gate::define('has-xmaspoint', function (User $user) {
            return ($user->hasXmasPoints || $user->isEditLeaveCredit == 1) ? true : false;
        });
        Gate::define('has-edit-leave-credits', function (User $user) {
            return ($user->isEditLeaveCredit == 1) ? true : false;
        });
        Gate::define('has-skills', function (User $user) {
            return $user->SkillCount > 0 &&  $user->isScheduledPerson == 1 ? true : false;
        });
        Gate::define('is-skillsshow', function (User $user) {
            return $user->SkillCount > 0;
        });
        Gate::define('has-only-facilityadmin', function (User $user) {
            if (($user->isFacilityAdministrator == 1 || $user->isDivisionalAdmin == 1) && ($user->isSchedulingTeamAdmin == 0 || $user->isScheduler == 0)) {
                return true;
            } else {
                return false;
            }
        });
        Gate::define('is-edit-master-duties', function (User $user) {
            return $user->userRoles->where('RoleName', RefRole::EDIT_MASTER_DUTIES)->count() > 0;
        });
        Gate::define('is-edit-rota-pattern', function (User $user) {
            return $user->userRoles->where('RoleName', RefRole::EDIT_ROTA_PATTERNS)->count() > 0;
        });
    }
}
