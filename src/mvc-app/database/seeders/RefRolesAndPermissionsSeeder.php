<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User\RefRole;
use App\Models\User\RolePermissionStatus;

class RefRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add new roles if they don't exist
        $maxSequence = RefRole::max('isSequence') ?? 0;
        
        $newRoles = [
            ['RoleName' => RefRole::FACILITY_BOOKER, 'RoleDescription' => 'Facility Booker', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 1],
            ['RoleName' => RefRole::EDIT_ALL_ALLOCATIONS, 'RoleDescription' => 'Allows editing all allocations', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 2],
            ['RoleName' => RefRole::EDIT_MASTER_DUTIES, 'RoleDescription' => 'Allows editing master duties', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 3],
            ['RoleName' => RefRole::EDIT_ROTA_PATTERNS, 'RoleDescription' => 'Allows editing rota patterns', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 4],
            ['RoleName' => RefRole::EDIT_LEAVE_CREDITS, 'RoleDescription' => 'Allows editing leave credits', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 5],
            ['RoleName' => RefRole::MOVE_PERSON_BETWEEN_TEAMS, 'RoleDescription' => 'Allows moving people between teams', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 6],
            ['RoleName' => RefRole::CREATE_NEW_FREELANCER, 'RoleDescription' => 'Allows creating new freelancers', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 7],
            ['RoleName' => RefRole::CREATE_NEW_STAFF, 'RoleDescription' => 'Allows creating new staff members', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 8],
            ['RoleName' => RefRole::GIVE_TEAM_PERMISSIONS, 'RoleDescription' => 'Allows giving permissions to team members', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 9],
            ['RoleName' => RefRole::EDIT_TEAM_SETTINGS, 'RoleDescription' => 'Allows editing team configuration and settings', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 10],
            ['RoleName' => RefRole::CREATE_NEW_TEAM, 'RoleDescription' => 'Allows creating new scheduling teams', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 11],
            ['RoleName' => RefRole::CREATE_NEW_GROUP, 'RoleDescription' => 'Allows creating new groups within teams', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 12],
            ['RoleName' => RefRole::EDIT_MASTER_DUTY_COLOURS, 'RoleDescription' => 'Allows editing master duty colour schemes', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 13],
            ['RoleName' => RefRole::CREATE_NEW_AREA, 'RoleDescription' => 'Allows creating new areas in the system', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 14],
            ['RoleName' => RefRole::EDIT_SYSTEM_SETTINGS, 'RoleDescription' => 'Allows editing global system settings and configuration', 'isAdditional' => 1, 'isActive' => 1, 'isSequence' => $maxSequence + 15],
        ];

        foreach ($newRoles as $role) {
            $exists = RefRole::where('RoleName', $role['RoleName'])->exists();
            if (!$exists) {
                RefRole::create($role);
            }
        }

        // Disable specified roles
        RefRole::whereIn('RoleName', [RefRole::SMARTBOOK_USER, RefRole::TIMESHEET_AUTHORISER, RefRole::SENIOR_SCHEDULER])->update(['isActive' => 0]);

        // Update role descriptions
        RefRole::where('RoleName', RefRole::BASIC_REPORTS)->update([
            'RoleDescription' => 'Freelance Usage, Leave, Sickness; toggles with Advanced Reports'
        ]);

        RefRole::where('RoleName', RefRole::ADVANCED_REPORTS)->update([
            'RoleDescription' => 'All reports, including Freelance Usage, Leave, Sickness; toggles with Basic Reports'
        ]);

        RefRole::where('RoleName', RefRole::SKILLS_AUTHORISER)->update([
            'RoleDescription' => 'Can assign Skills to people, but not create new ones'
        ]);

        RefRole::where('RoleName', RefRole::SKILLS_ADMIN)->update([
            'RoleDescription' => 'Can create new Skills and assign them to people'
        ]);

        RefRole::where('RoleName', RefRole::MANAGER)->update([
            'RoleDescription' => 'Allows user to see all published allocations, unaffected by masking'
        ]);

        RefRole::where('RoleName', RefRole::SHIFT_LEADER)->update([
            'RoleDescription' => 'Allows user to edit people and facilities at specified times of day, according to the team\'s "Time Restricted Editing" settings. Typically, this permission would be used for people leading teams outside of office hours when there is no scheduler, and when there is a need to cover sickness and breaking news. If \'Facility Booker\' is selected as an option, the Shift Leader is able to manage Facility Bookings during the \'Time Restricted Editing\' period for that Team, to align with their permissions to edit Duties, Jobs and Scheduled People\'s allocations in that Team. If Facility Booker is NOT selected, then a Shift Leader is NOT able to edit Facilities, unless they have \'Facility Booker\' selected in another Team.'
        ]);

        RefRole::where('RoleName', RefRole::TEAM_LEADER)->update([
            'RoleDescription' => 'Allows user to be a Scheduled Person in a team and also edit all allocations without time restrictions, as if they were a Scheduler. Unlike other Scheduled People, Team Leaders have the option to be given any of the extended permissions which Schedulers can be given. They can also be given Facility Booker as an option, which allows them to manage Facility Bookings without time restrictions. Team Leaders cannot create new weeks.'
        ]);

        RefRole::where('RoleName', RefRole::FACILITY_BOOKER)->update([
            'RoleDescription' => 'Can add/edit/delete bookings for facilities in this Area. Some facilities also have a further restriction by Scheduling Team'
        ]);

        RefRole::where('RoleName', RefRole::SCHEDULING_TEAM_VIEWER)->update([
            'RoleDescription' => 'Someone who is neither scheduled in the team nor a Scheduler or above. They can view the allocations for everyone in the team, and have the option to be given other permissions, such as \'Manager\''
        ]);

        RefRole::where('RoleName', RefRole::SCHEDULED_PERSON)->update([
            'RoleDescription' => 'Someone who is scheduled in the team'
        ]);

        RefRole::where('RoleName', RefRole::SCHEDULER)->update([
            'RoleDescription' => 'Can carry out all basic scheduling functions in the team, such as assigning work to people; has the option to be given other permissions, such as \'Edit Rota Patterns\''
        ]);

        RefRole::where('RoleName', RefRole::SCHEDULING_TEAM_ADMIN)->update([
            'RoleDescription' => 'Can edit all settings for the Scheduling Team itself, and give permissions to other people in the team, as well as carry out all options available to Schedulers in the team'
        ]);

        RefRole::where('RoleName', RefRole::AREA_ADMIN)->update([
            'RoleDescription' => 'Can administer Scheduling Team Admin, Facility Admin and Area Viewer permissions in their Area, administer Duty Colours in their Area and add/edit/delete Scheduling Teams in their Area, as well as doing everything that Scheduling Team Admins can do, in all Scheduling Teams in their Area; when new Scheduling Teams are added, Area Admins are given these permissions automatically'
        ]);

        RefRole::where('RoleName', RefRole::SYSTEM_ADMIN)->update([
            'RoleDescription' => 'Can give Area Admin permissions, and administer all the tabs in the System menu, as well as doing everything that Area Admins can do, in all Areas; when new Areas are added, System Admins get permissions to these Areas and all Scheduling Teams within them automatically'
        ]);

        // Truncate existing permission status data
        RolePermissionStatus::truncate();

        // Get role IDs by role names for dynamic lookup
        $roleIds = RefRole::pluck('RoleID', 'RoleName');

        $data = [
            // SCHEDULING TEAM VIEWER - Can be granted these permissions by STA
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::BASIC_REPORTS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::ADVANCED_REPORTS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::SKILLS_AUTHORISER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::SKILLS_ADMIN], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::MANAGER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::SHIFT_LEADER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::TEAM_LEADER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::FACILITY_BOOKER], RolePermissionStatus::CONDITIONAL, 'Only if also Shiftleader, and only at the times of day that Shiftleading is active'],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::EDIT_ALL_ALLOCATIONS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::EDIT_MASTER_DUTIES], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::EDIT_ROTA_PATTERNS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::EDIT_LEAVE_CREDITS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::MOVE_PERSON_BETWEEN_TEAMS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::CREATE_NEW_FREELANCER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::CREATE_NEW_STAFF], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::GIVE_TEAM_PERMISSIONS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::EDIT_TEAM_SETTINGS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::CREATE_NEW_TEAM], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::CREATE_NEW_GROUP], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::EDIT_MASTER_DUTY_COLOURS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::CREATE_NEW_AREA], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_VIEWER], $roleIds[RefRole::EDIT_SYSTEM_SETTINGS], RolePermissionStatus::NA],

            // SCHEDULER - Basic permissions optional, Manager/Shift Leader/Team Leader disabled, Facility Booker mandatory
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::BASIC_REPORTS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::ADVANCED_REPORTS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::SKILLS_AUTHORISER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::SKILLS_ADMIN], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::MANAGER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::SHIFT_LEADER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::TEAM_LEADER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::FACILITY_BOOKER], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::EDIT_ALL_ALLOCATIONS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::EDIT_MASTER_DUTIES], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::EDIT_ROTA_PATTERNS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::EDIT_LEAVE_CREDITS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::MOVE_PERSON_BETWEEN_TEAMS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::CREATE_NEW_FREELANCER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::CREATE_NEW_STAFF], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::GIVE_TEAM_PERMISSIONS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::EDIT_TEAM_SETTINGS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::CREATE_NEW_TEAM], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::CREATE_NEW_GROUP], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::EDIT_MASTER_DUTY_COLOURS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::CREATE_NEW_AREA], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULER], $roleIds[RefRole::EDIT_SYSTEM_SETTINGS], RolePermissionStatus::NA],

            // SCHEDULING TEAM ADMIN - Mixed permissions
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::BASIC_REPORTS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::ADVANCED_REPORTS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::SKILLS_AUTHORISER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::SKILLS_ADMIN], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::MANAGER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::SHIFT_LEADER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::TEAM_LEADER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::FACILITY_BOOKER], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::EDIT_ALL_ALLOCATIONS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::EDIT_MASTER_DUTIES], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::EDIT_ROTA_PATTERNS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::EDIT_LEAVE_CREDITS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::MOVE_PERSON_BETWEEN_TEAMS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::CREATE_NEW_FREELANCER], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::CREATE_NEW_STAFF], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::GIVE_TEAM_PERMISSIONS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::EDIT_TEAM_SETTINGS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::CREATE_NEW_TEAM], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::CREATE_NEW_GROUP], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::EDIT_MASTER_DUTY_COLOURS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::CREATE_NEW_AREA], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULING_TEAM_ADMIN], $roleIds[RefRole::EDIT_SYSTEM_SETTINGS], RolePermissionStatus::NA],

            // SCHEDULED PERSON - All basic permissions are optional, Facility Booker conditional
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::BASIC_REPORTS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::ADVANCED_REPORTS], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::SKILLS_AUTHORISER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::SKILLS_ADMIN], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::MANAGER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::SHIFT_LEADER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::TEAM_LEADER], RolePermissionStatus::OPTIONAL],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::FACILITY_BOOKER], RolePermissionStatus::CONDITIONAL, 'Only if also Shiftleader, and only at the times of day that Shiftleading is active, or if also Team Leader'],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::EDIT_ALL_ALLOCATIONS], RolePermissionStatus::CONDITIONAL, 'Only if also Team Leader'],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::EDIT_MASTER_DUTIES], RolePermissionStatus::CONDITIONAL, 'Only if also Team Leader'],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::EDIT_ROTA_PATTERNS], RolePermissionStatus::CONDITIONAL, 'Only if also Team Leader'],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::EDIT_LEAVE_CREDITS], RolePermissionStatus::CONDITIONAL, 'Only if also Team Leader'],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::MOVE_PERSON_BETWEEN_TEAMS], RolePermissionStatus::CONDITIONAL, 'Only if also Team Leader'],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::CREATE_NEW_FREELANCER], RolePermissionStatus::CONDITIONAL, 'Only if also Team Leader'],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::CREATE_NEW_STAFF], RolePermissionStatus::CONDITIONAL, 'Only if also Team Leader'],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::GIVE_TEAM_PERMISSIONS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::EDIT_TEAM_SETTINGS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::CREATE_NEW_TEAM], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::CREATE_NEW_GROUP], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::EDIT_MASTER_DUTY_COLOURS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::CREATE_NEW_AREA], RolePermissionStatus::NA],
            [$roleIds[RefRole::SCHEDULED_PERSON], $roleIds[RefRole::EDIT_SYSTEM_SETTINGS], RolePermissionStatus::NA],

            // AREA ADMIN - Same pattern as STA
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::BASIC_REPORTS], RolePermissionStatus::NA],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::ADVANCED_REPORTS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::SKILLS_AUTHORISER], RolePermissionStatus::NA],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::SKILLS_ADMIN], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::MANAGER], RolePermissionStatus::NA],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::SHIFT_LEADER], RolePermissionStatus::NA],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::TEAM_LEADER], RolePermissionStatus::NA],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::FACILITY_BOOKER], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::EDIT_ALL_ALLOCATIONS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::EDIT_MASTER_DUTIES], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::EDIT_ROTA_PATTERNS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::EDIT_LEAVE_CREDITS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::MOVE_PERSON_BETWEEN_TEAMS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::CREATE_NEW_FREELANCER], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::CREATE_NEW_STAFF], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::GIVE_TEAM_PERMISSIONS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::EDIT_TEAM_SETTINGS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::CREATE_NEW_TEAM], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::CREATE_NEW_GROUP], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::EDIT_MASTER_DUTY_COLOURS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::CREATE_NEW_AREA], RolePermissionStatus::NA],
            [$roleIds[RefRole::AREA_ADMIN], $roleIds[RefRole::EDIT_SYSTEM_SETTINGS], RolePermissionStatus::NA],

            // SYSTEM ADMIN - Same pattern as STA
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::BASIC_REPORTS], RolePermissionStatus::NA],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::ADVANCED_REPORTS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::SKILLS_AUTHORISER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::SKILLS_ADMIN], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::MANAGER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::SHIFT_LEADER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::TEAM_LEADER], RolePermissionStatus::NA],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::FACILITY_BOOKER], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::EDIT_ALL_ALLOCATIONS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::EDIT_MASTER_DUTIES], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::EDIT_ROTA_PATTERNS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::EDIT_LEAVE_CREDITS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::MOVE_PERSON_BETWEEN_TEAMS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::CREATE_NEW_FREELANCER], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::CREATE_NEW_STAFF], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::GIVE_TEAM_PERMISSIONS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::EDIT_TEAM_SETTINGS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::CREATE_NEW_TEAM], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::CREATE_NEW_GROUP], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::EDIT_MASTER_DUTY_COLOURS], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::CREATE_NEW_AREA], RolePermissionStatus::MANDATORY],
            [$roleIds[RefRole::SYSTEM_ADMIN], $roleIds[RefRole::EDIT_SYSTEM_SETTINGS], RolePermissionStatus::MANDATORY],
        ];

        foreach ($data as $row) {
            RolePermissionStatus::create([
                'MainRoleID' => $row[0],
                'AdditionalRoleID' => $row[1],
                'PermissionKey' => $row[2],
                'PermissionDescription' => $row[3] ?? null,
                'IsActive' => 1,
                'CreatedDate' => now()
            ]);
        }
    }
}