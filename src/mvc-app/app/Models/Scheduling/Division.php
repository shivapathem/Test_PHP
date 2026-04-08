<?php

namespace App\Models\Scheduling;

use App\Models\Facility\Facility;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Division extends Model
{
    use HasFactory;

    const CREATED_AT = 'CreatedDate';
    const UPDATED_AT = 'LastModDate';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'DivisionID';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'CreatedDate' => 'datetime',
        'LastModDate' => 'datetime'
    ];

    /**
     * Scope a query to only include active divisions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('isActive', 1);
    }

    /**
     * The scheduling groups that belong to the area.
     */
    public function schedulingGroups()
    {
        return $this->hasMany(SchedulingGroup::class, 'DivisionID', 'DivisionID');
    }

    /**
     * The scheduling team that belong to the area.
     */
    public function schedulingTeam()
    {
        return $this->hasMany(SchedulingTeam::class, 'divisionid', 'DivisionID')->where('schedulingTeams.isActive', 1);
    }

    /**
     * Users belonging to this division
     */
    public function getUsersAttribute()
    {
        return User::join('schedulingTeams', function ($join) {
            $join->on('schedulingTeams.divisionid', '=', DB::raw($this->DivisionID));
        })
            ->join('UserRoles', function ($join) {
                $join->on('UserRoles.UR_SchedulingTeamID', '=', 'schedulingTeams.schedulingTeamId')
                    ->whereDate('UserRoles.UR_EndDate', '>=', Carbon::now())
                    ->where('UserRoles.UR_UserID', '=', DB::raw('UserDetails.UD_UserID'));
            })
            ->join('REF_Roles', function ($join) {
                $join->on('REF_Roles.RoleID', '=', 'UserRoles.UR_RoleID')
                    ->whereIn('RoleName', ['Scheduling Team Admin', 'Senior Scheduler', 'Scheduler']);
            })
            ->where('schedulingTeams.isActive', 1)->select('UserDetails.*')->distinct()->get();
    }

    /**
     * Get the Facility of this area.
     */
    public function facility()
    {
        return $this->hasOne(
            Facility::class,
            'FC_AreaOwnerID',
            'DivisionID'
        );
    }
}
