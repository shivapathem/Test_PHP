<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'UserRoles';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'UR_UserRoleID';

    /**
     * Relation to REF_RolePermissionStatus via UR_RoleID -> MainRoleID
     */
    public function rolePermissionStatuses()
    {
        return $this->hasMany(RolePermissionStatus::class, 'MainRoleID', 'UR_RoleID');
    }

    /**
     * Relation to ScheduledPersonTeam_LINK
     */
    public function schedulingTeamLink()
    {
        return $this->hasMany(ScheduledPersonTeamLink::class, 'ScheduledPersonID', 'UR_UserID')->where('TeamID', $this->UR_SchedulingTeamID);
    }

    /**
     * Relation to RefRole
     */
    public function role()
    {
        return $this->belongsTo(RefRole::class, 'UR_RoleID', 'RoleID');
    }

    /**
     * Return array of scheduling and non scheduling permissions.
     */
    public static function getPermission(int $type): array
    {
        $roles =  [
            ['RoleName' => RefRole::BASIC_REPORTS],
            ['RoleName' => RefRole::ADVANCED_REPORTS],
            ['RoleName' => RefRole::SKILLS_AUTHORISER],
            ['RoleName' => RefRole::SKILLS_ADMIN],
            ['RoleName' => RefRole::MANAGER],
            ['RoleName' => RefRole::SHIFT_LEADER],
            ['RoleName' => RefRole::TEAM_LEADER],
            ['RoleName' => RefRole::FACILITY_BOOKER],
            ['RoleName' => RefRole::EDIT_ALL_ALLOCATIONS],
            ['RoleName' => RefRole::EDIT_MASTER_DUTIES],
            ['RoleName' => RefRole::EDIT_ROTA_PATTERNS],
            ['RoleName' => RefRole::EDIT_LEAVE_CREDITS],
            ['RoleName' => RefRole::MOVE_PERSON_BETWEEN_TEAMS],
            ['RoleName' => RefRole::CREATE_NEW_FREELANCER],
            ['RoleName' => RefRole::CREATE_NEW_STAFF],
            ['RoleName' => RefRole::GIVE_TEAM_PERMISSIONS],
            ['RoleName' => RefRole::EDIT_TEAM_SETTINGS],
            ['RoleName' => RefRole::CREATE_NEW_TEAM],
            ['RoleName' => RefRole::CREATE_NEW_GROUP],
            ['RoleName' => RefRole::EDIT_MASTER_DUTY_COLOURS],
            ['RoleName' => RefRole::CREATE_NEW_AREA],
            ['RoleName' => RefRole::EDIT_SYSTEM_SETTINGS],
        ];
        if ((int)$type === 0) {
            unset($roles[6]);
        }
        return $roles;
    }
}
