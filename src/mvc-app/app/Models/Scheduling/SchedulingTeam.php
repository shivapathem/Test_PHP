<?php

namespace App\Models\Scheduling;

use App\Models\User;
use App\Models\User\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulingTeam extends Model
{
    use HasFactory;

    const CREATED_AT = 'createddate';
    const UPDATED_AT = 'modifieddate';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'schedulingTeams';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'schedulingTeamId';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'createddate' => 'datetime',
        'modifieddate' => 'datetime'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

     /**
     * Get division admins for this team
     */
    public function divisionAdmins()
    {
        return $this->hasManyThrough(
            User::class,
            UserRole::class,
            'UR_DivisionId',
            'UD_UserID',
            'divisionid',
            'UR_UserID'
        )
        ->join('REF_Roles', 'UserRoles.UR_RoleID', '=', 'REF_Roles.RoleID')
        ->where('REF_Roles.IsActive', 1)
        ->whereIn('REF_Roles.RoleName', ['Area Admin', 'Area Viewer'])
        ->whereDate('UserRoles.UR_StartDate', '<=', now())
        ->whereDate('UserRoles.UR_EndDate', '>', now())
        ->select('UserDetails.UD_InternalEmail', 'UserDetails.UD_ExternalEmail');
    }
}
