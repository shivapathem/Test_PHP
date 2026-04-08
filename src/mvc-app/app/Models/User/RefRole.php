<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefRole extends Model
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
    protected $table = 'REF_Roles';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'RoleID';

    // Role Name Constants
    const SYSTEM_ADMIN = 'System Admin';
    const AREA_ADMIN = 'Area Admin';
    const SCHEDULING_TEAM_ADMIN = 'Scheduling Team Admin';
    const SENIOR_SCHEDULER = 'Senior Scheduler';
    const SCHEDULER = 'Scheduler';
    const SCHEDULING_TEAM_VIEWER = 'Scheduling Team Viewer';
    const SCHEDULED_PERSON = 'Scheduled Person';
    const SKILLS_ADMIN = 'Skills Admin';
    const SKILLS_AUTHORISER = 'Skills Authoriser';
    const SHIFT_LEADER = 'Shift Leader';
    const MANAGER = 'Manager';
    const SMARTBOOK_USER = 'Smartbook user';
    const TIMESHEET_AUTHORISER = 'Timesheet Authoriser';
    const ADVANCED_REPORTS = 'Advanced Reports';
    const TEAM_LEADER = 'Team Leader';
    const BASIC_REPORTS = 'Basic Reports';
    const AREA_VIEWER = 'Area Viewer';
    const AREA_REPORTS = 'Area Reports';
    const FACILITY_ADMINISTRATOR = 'Facility Administrator';
    const FACILITY_BOOKER = 'Facility Booker';
    const EDIT_ALL_ALLOCATIONS = 'Edit All Allocations';
    const EDIT_MASTER_DUTIES = 'Edit Master Duties';
    const EDIT_ROTA_PATTERNS = 'Edit Rota Patterns';
    const EDIT_LEAVE_CREDITS = 'Edit Leave Credits';
    const MOVE_PERSON_BETWEEN_TEAMS = 'Move Person Between Teams';
    const CREATE_NEW_FREELANCER = 'Create New Freelancer';
    const CREATE_NEW_STAFF = 'Create New Staff';
    const GIVE_TEAM_PERMISSIONS = 'Give Team Permissions';
    const EDIT_TEAM_SETTINGS = 'Edit Team Settings';
    const CREATE_NEW_TEAM = 'Create New Team';
    const CREATE_NEW_GROUP = 'Create New Group';
    const EDIT_MASTER_DUTY_COLOURS = 'Edit Master Duty Colours';
    const CREATE_NEW_AREA = 'Create New Area';
    const EDIT_SYSTEM_SETTINGS = 'Edit System Settings';
}
