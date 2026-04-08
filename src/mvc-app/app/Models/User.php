<?php

namespace App\Models;

use App\Models\Facility\Facility;
use App\Models\Facility\FacilityUserRole;
use App\Models\Leave\LeaveRequestGroup;
use App\Models\Scheduling\Division;
use App\Models\Scheduling\Programme;
use App\Models\Scheduling\SchedulingTeam;
use App\Models\Scheduling\SkillsProgrammesStaffLink;
use App\Models\Scheduling\TimeDimension;
use App\Models\User\RefRole;
use App\Models\User\ScheduledPersonTeamLink;
use App\Models\User\UserConfig;
use App\Models\User\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use Authorizable;

    public $timestamps = false;


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'UD_UserID';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'UserDetails';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'UD_DisplayFirstName',
        'UD_DisplayLastName',
        'UD_MiddleName',
        'UD_PreferredFirstName',
        'UD_JobTitle',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'UD_StartDate' => 'date',
    ];

    public function fixArrayKey(&$arr)
    {
        // Rebuild the array with modified keys
        $arr = array_combine(
            array_map(function ($str) {
                return str_replace(' ', '_', $str);
            }, array_keys($arr)),
            array_values($arr)
        );

        // Recurse for nested arrays
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                fixArrayKey($arr[$key]);
            }
        }
    }

    function deepFixObjectProps(&$data)
    {

        if ($data instanceof stdClass) {
            foreach (get_object_vars($data) as $key => $value) {
                $newKey = str_replace(' ', '_', $key);
                if ($newKey !== $key) {
                    $data->{$newKey} = $value;
                    unset($data->{$key});
                }
                deepFixProps($data->{$newKey});
            }
        } elseif (is_array($data)) {
            foreach ($data as &$elem) {
                foreach (get_object_vars($elem) as $key => $value) {
                    if (strpos($key, ' ')) {
                        $newKey = strtolower(str_replace(' ', '_', $key));
                        $elem->{$newKey} = $value;
                    } else {
                        $newKey = strtolower($key);
                        $elem->{$newKey} = $value;
                    }
                    unset($elem->{$key});
                }
            }
            return $data;
        }
    }

    public function getUserRoleDetail()
    {
        if ($this->UD_NetLogin) {
            $this->userSetup = DB::select('EXEC usp_get_MySetUp ?, ?', [$this->UD_UserID, $this->UD_NetLogin]);
        } else {
            $this->userSetup = [];
        }
        $setup = $this->userSetup;
        $usersetupArray =  $this->deepFixObjectProps($setup);

        if (!empty($usersetupArray)) {
            //fetch the default team
            $isDefaultTeam = array_filter($usersetupArray, function ($item) {
                return isset($item->isdefault) && $item->isdefault == 1;
            });
        }
        $this->isSystemAdmin = $this->userSystemRole() != null ? 1 : 0;
        $this->isDivisionalAdmin = $this->getAreasRoles->where('RoleName', 'Area Admin')->count() > 0 ? 1 : 0;
        $this->hasTeam = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'schedulingteamid')) > 0 ? 1 : 0) : 0;
        $this->defaultTeamId =  !empty($isDefaultTeam) ? $isDefaultTeam[key($isDefaultTeam)]->schedulingteamid : 0;
        $this->homeTeam = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'home_team')) > 0 ? 1 : 0) : 0;
        $this->isScheduledPerson =  !empty($usersetupArray) ? (max(array_column($usersetupArray, 'scheduledtype')) > 0 ? 1 : 0) : 0;
        $this->isSchedulingTeamAdmin = $this->hasTeamAdmin = $this->isAdmin = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'scheduling_team_admin')) > 0 ? 1 : 0) : 0;
        $this->isScheduler = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'scheduler')) > 0 ? 1 : 0) : 0;
        $this->isSchedulingTeamViewer =  !empty($usersetupArray) ? (max(array_column($usersetupArray, 'scheduling_team_viewer')) > 0 ? 1 : 0) : 0;
        $this->isTeamLeader = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'team_leader')) > 0 ? 1 : 0) : 0;
        $this->isSkillsAdmin = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'skills_admin')) > 0 ? 1 : 0) : 0;
        $this->isSkillsAuthoriser = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'skills_authoriser')) > 0 ? 1 : 0) : 0;
        $this->isManager = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'manager')) > 0 ? 1 : 0) : 0;
        $this->isShiftLeader =  !empty($usersetupArray) ? (max(array_column($usersetupArray, 'shift_leader')) > 0 ? 1 : 0) : 0;
        $this->isBasicReports =  !empty($usersetupArray) ? (max(array_column($usersetupArray, 'basic_reports')) > 0 ? 1 : 0) : 0;
        $this->isAdvancedReports = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'advanced_reports')) > 0 ? 1 : 0) : 0;
        $this->isAreaViewer = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'area_viewer')) > 0 ? 1 : 0) : 0;
        $this->isAreaReports =  !empty($usersetupArray) ? (max(array_column($usersetupArray, 'area_reports')) > 0 ? 1 : 0) : 0;
        $this->hasXmasPoints = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'hasxmaspoints')) > 0 ? 1 : 0) : 0;
        $this->hasHandovers = !empty($usersetupArray) ? (max(array_column($usersetupArray, 'hashandovers')) > 0 ? 1 : 0) : 0;
        $this->hasLeave = ($this->userLeaveGroup->where('LeaveAdmin', 0)->where('LeaveTypeID', '>', 0)->count() > 0) ? 1 : 0;
        $this->hasRequestAdmin = $this->hasLeaveAdmin = ($this->userLeaveGroup->where('LeaveAdmin', '>', 0)->count() > 0) ? 1 : 0;
        $this->hasRequest = ($this->userLeaveGroup->where('LeaveAdmin', 0)->count() > 0) ? 1 : 0;
        $this->SkillCount  = $this->userSkill()->count();
        $this->isEditLeaveCredit = $this->userRoles->where('RoleName', RefRole::EDIT_LEAVE_CREDITS)->count() > 0 ? 1 : 0;
        //Facility adminstrator roles
        $this->isFacilityAdministrator = ($this->getAreasRoles->where('RoleName', RefRole::FACILITY_ADMINISTRATOR)->count() > 0 || $this->getFacilityAdministratorRole->count() > 0) ? 1 : 0;
        $this->isRealFacilityAdministrator = $this->getAreasRoles->where('RoleName', RefRole::FACILITY_ADMINISTRATOR)->count() > 0 ?  1 : 0;
    }

    /**
     * Get User accessible scheduling teams
     */
    public function userAccessibleTeams($excludeSpecialTeams = false)
    {
        $schedulingTeamIds = collect($this->userSetup)->pluck('schedulingteamid')->unique()->toArray();
        $areaAdminIds = $this->getAreasRoles->where('RoleName', 'Area Admin')->pluck('DivisionID')->toArray();
        $isSystemAdmin = $this->isSystemAdmin;
        return SchedulingTeam::where('schedulingTeams.isActive', 1)
            ->where(function ($query) use ($areaAdminIds, $isSystemAdmin, $schedulingTeamIds, $excludeSpecialTeams) {
                if ($excludeSpecialTeams) {
                    $query->whereNotIn('schedulingTeams.schedulingTeamName', ['Freelancers', 'Archive', 'Other BBC', 'Maternity/Paternity', 'Apprentices']);
                }
                if ($isSystemAdmin != 1) {
                    if (empty($schedulingTeamIds) && empty($areaAdminIds)) {
                        $query->whereRaw('1=0');
                    } else {
                        if (!empty($areaAdminIds)) {
                            $query->where(function ($q) use ($areaAdminIds, $schedulingTeamIds) {
                                $q->whereIn('schedulingTeams.divisionid', $areaAdminIds);
                                if (!empty($schedulingTeamIds)) {
                                    $q->orWhereIn('schedulingTeams.schedulingTeamId', $schedulingTeamIds);
                                }
                            });
                        }
                        if (!empty($schedulingTeamIds)) {
                            $query->whereIn('schedulingTeams.schedulingTeamId', $schedulingTeamIds);
                        }
                    }
                }
            })->orderBy('schedulingTeamName');
    }

    /**
     * Get User leave groups
     */
    public function userLeaveGroup()
    {
        return $this->belongsToMany(LeaveRequestGroup::class, 'Staff_Web_Config_LeaveGroups_Link', 'ScheduledPersonID', 'LeaveGroupID')
            ->leftJoin('leave_types as lt', 'LeaveRequestGroups.ID', '=', 'lt.GroupID')
            ->leftJoin('RequestTypes as r', 'LeaveRequestGroups.ID', '=', 'r.GroupID')
            ->where('Staff_Web_Config_LeaveGroups_Link.isActive', 1)
            ->select(
                'LeaveRequestGroups.*',
                DB::raw('ISNULL(Staff_Web_Config_LeaveGroups_Link.Admin, 0) as LeaveAdmin'),
                'lt.ID as LeaveTypeID'
            );
    }

    /**
     * Get User configuration
     */
    public function userConfig()
    {
        return $this->hasOne(UserConfig::class, 'UC_UserID', 'UD_UserID');
    }

    /**
     * Get the user SystemAdmin detail.
     */
    public function userSystemRole()
    {
        return $this->userRoles->where('RoleName', RefRole::SYSTEM_ADMIN)->first();
    }

    /**
     * Get the user roles detail.
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'UR_UserID', 'UD_UserID')
            ->whereDate('UR_StartDate', '<=', now())
            ->whereDate('UR_EndDate', '>=', now())
            ->join('REF_Roles', function ($join) {
                $join->on('REF_Roles.RoleID', '=', 'UserRoles.UR_RoleID');
            })->select('UserRoles.*', 'REF_Roles.RoleName');
    }

    /**
     * Get the user areas roles.
     */
    public function getAreasRoles()
    {
        return $this->belongsToMany(Division::class, 'UserRoles', 'UR_UserID', 'UR_DivisionId')
            ->whereDate('UR_StartDate', '<=', now())
            ->whereDate('UR_EndDate', '>=', now())
            ->join('REF_Roles', function ($join) {
                $join->on('REF_Roles.RoleID', '=', 'UserRoles.UR_RoleID');
            })->select('Divisions.*', 'REF_Roles.RoleName');
    }

    /**
     * Is facility administrator
     *
     * @param Facility $facility,
     * @param int $isReal Check if its real facility admin
     */
    public function isFacilityAdministrator(Facility $facility, $isReal = 0): bool
    {
        $isAdmin = 0;
        if ($this->getAreasRoles->where('RoleName', RefRole::FACILITY_ADMINISTRATOR)->where('DivisionID', $facility->FC_AreaOwnerID)->count() > 0) {
            $isAdmin = 1;
        } elseif ($this->getFacilityAdministratorRole->where('FC_AreaOwnerID', $facility->FC_AreaOwnerID)->where('FUR_FacilityID', $facility->FC_FacilityID)->count() > 0 && $isReal == 0) {
            //Check user have spoof facility admin role
            $isAdmin = 1;
        }
        return $isAdmin;
    }

    /**
     * Get Facility roles
     */
    public function getFacilityAdministratorRole()
    {
        return $this->hasMany(FacilityUserRole::class, 'FUR_UserID')
            ->join('Facilities', 'FC_FacilityID', 'FUR_FacilityID')
            ->join('schedulingTeams', function ($join) {
                $join->on('schedulingTeams.divisionid', '=', 'Facilities.FC_AreaOwnerID');
            })->join('UserRoles', function ($join) {
                $join->on('UserRoles.UR_SchedulingTeamID', '=', 'schedulingTeams.schedulingTeamId')
                    ->whereDate('UserRoles.UR_EndDate', '>=', Carbon::now())
                    ->where('UserRoles.UR_UserID', '=', $this->UD_UserID);
            })
            ->join('REF_Roles', function ($join) {
                $join->on('REF_Roles.RoleID', '=', 'UserRoles.UR_RoleID')
                    ->whereIn('RoleName', ['Scheduling Team Admin', 'Scheduler']);
            })
            ->where('schedulingTeams.isActive', 1)
            ->where('FUR_Role', Facility::FACILITY_ROLE_ADMINISTRATOR)
            ->select('FacilityUserRoles.*', 'Facilities.FC_AreaOwnerID');
    }

    /**
     * Is divisional admin
     */
    public function getAreaReport()
    {
        return $this->getAreasRoles->where('RoleName', 'Area Reports')->count() > 0 ? 1 : 0;
    }

    /**
     * Is divisional admin
     */
    public function userSkill()
    {
        return $this->hasManyThrough(
            Programme::class,
            SkillsProgrammesStaffLink::class,
            'staff_id',
            'ID',
            'UD_TeampayStaffID',
            'programmes_id'
        );
    }

    /**
     * User have access to facility
     * @param Facility $facility facility
     *
     * @return bool
     */
    public function haveAccessToFacility(Facility $facility): bool
    {
        $access = false;
        if ($this->isFacilityAdministrator($facility)) {
            return true;
        }
        $userTeamSettings = collect($this->userSetup);

        //Function to check admin access
        $hasAccessFunction = static function ($setting): bool {
            return ($setting->scheduling_team_admin ?? 0) > 0
                || ($setting->scheduler ?? 0) > 0
                || ($setting->facility_booker ?? 0) > 0;
        };

        if ($facility->facilityRestrictBookers->count() > 0) {
            //When Restrict Bookers are chosen in facility catalogue
            foreach ($userTeamSettings->whereIn('schedulingteamid', $facility->facilityRestrictBookers->pluck('schedulingTeamId')) as $userTeamSetting) {
                if ($hasAccessFunction($userTeamSetting)) {
                    $access = true;
                    break;
                }
            }
        } else {
            //When No Restrict Bookers are chosen in facility catalogue
            foreach ($userTeamSettings->where('schedulingteamdivisionid', $facility->FC_AreaOwnerID) as $userTeamSetting) {
                if ($hasAccessFunction($userTeamSetting)) {
                    $access = true;
                    break;
                }
            }
        }
        return $access;
    }

    /**
     * getixYearWeek: current week
     */
    public function getixYearWeek()
    {
        $bbcWeekNumber = TimeDimension::whereDate('dDateTime', date("Y-m-d"))->get()->first();
        return $bbcWeekNumber->ixYearWeek;
    }

    /**
     * Check if user is a Booker
     *
     */
    public function isFacilityBooker(): bool
    {
        $isBooker = false;
        foreach (Facility::all() as $facility) {
            $isBooker = $this->haveAccessToFacility($facility);
            if ($isBooker) {
                break;
            }
        }
        return $isBooker;
    }

    /**
     * Check if user have any team of the facility area
     */
    public function haveAreaTeamFacility(Facility $facility): bool
    {
        $haveArea = false;
        foreach ($this->userSetup as $schedulingDetail) {
            if (isset($schedulingDetail->schedulingteamdivisionid) && $schedulingDetail->schedulingteamdivisionid == $facility->FC_AreaOwnerID) {
                $haveArea = true;
                break;
            }
        }
        return $haveArea;
    }

    /**
     * Get the scheduling team links for the user.
     */
    public function schedulingTeamLinks()
    {
        return $this->hasMany(ScheduledPersonTeamLink::class, 'ScheduledPersonID', 'UD_UserID')
            ->where('isActive', 1)->where('EndDate', '>', Carbon::now());
    }
}
