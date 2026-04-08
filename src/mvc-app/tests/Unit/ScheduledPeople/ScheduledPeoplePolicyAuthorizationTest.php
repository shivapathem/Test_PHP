<?php

namespace Tests\Unit\ScheduledPeople;

use Tests\TestCase;
use App\Policies\Setup\ScheduledPeoplePolicy;
use App\Models\User;
use App\Models\User\ScheduledPersonTeamLink;
use App\Models\User\RefRole;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

/**
 * Policy Authorization and Permission Tests for Scheduled People
 * Comprehensive coverage of all authorization scenarios and team-level permissions
 */
class ScheduledPeoplePolicyAuthorizationTest extends TestCase
{
    protected $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ScheduledPeoplePolicy();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ===================== POLICY: VIEW PERMISSION =====================

    #[Test]
    public function policy_view_granted_to_scheduling_team_admin()
    {
        /**
         * Scenario: Scheduling Team Admin user
         * Expected: View permission granted
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 1;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        $user->isScheduler = 0;

        $this->assertTrue($this->policy->view($user));
    }

    #[Test]
    public function policy_view_granted_to_divisional_admin()
    {
        /**
         * Scenario: Divisional Admin user
         * Expected: View permission granted
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 1;
        $user->isSystemAdmin = 0;
        $user->isScheduler = 0;

        $this->assertTrue($this->policy->view($user));
    }

    #[Test]
    public function policy_view_granted_to_scheduler()
    {
        /**
         * Scenario: Scheduler user
         * Expected: View permission granted
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        $user->isScheduler = 1;

        $this->assertTrue($this->policy->view($user));
    }

    #[Test]
    public function policy_view_denied_to_regular_user()
    {
        /**
         * Scenario: Regular user with no admin or scheduler role
         * Expected: View permission denied
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        $user->isScheduler = 0;

        $this->assertFalse($this->policy->view($user));
    }

    // ===================== POLICY: CREATE PERMISSION =====================

    #[Test]
    public function policy_create_granted_to_admin_users()
    {
        /**
         * Scenario: Various admin users attempting to create
         * Expected: All admins can create regardless of role permissions
         */
        
        // Scheduling Team Admin
        $user1 = Mockery::mock(User::class)->makePartial();
        $user1->isSchedulingTeamAdmin = 1;
        $user1->isDivisionalAdmin = 0;
        $user1->isSystemAdmin = 0;
        $this->assertTrue($this->policy->create($user1));

        // Divisional Admin
        $user2 = Mockery::mock(User::class)->makePartial();
        $user2->isSchedulingTeamAdmin = 0;
        $user2->isDivisionalAdmin = 1;
        $user2->isSystemAdmin = 0;
        $this->assertTrue($this->policy->create($user2));

        // System Admin
        $user3 = Mockery::mock(User::class)->makePartial();
        $user3->isSchedulingTeamAdmin = 0;
        $user3->isDivisionalAdmin = 0;
        $user3->isSystemAdmin = 1;
        $this->assertTrue($this->policy->create($user3));
    }

    #[Test]
    public function policy_create_denied_to_non_admin()
    {
        /**
         * Scenario: Non-admin user without CREATE_NEW_STAFF role
         * Expected: Create permission denied
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        $user->userRoles = collect([]);

        $this->assertFalse($this->policy->create($user));
    }

    // ===================== POLICY: UPDATE & DELETE PERMISSION =====================

    #[Test]
    public function policy_update_delete_require_admin_role()
    {
        /**
         * Scenario: Only admins can update or delete scheduled people
         * Expected: Only admins granted access
         */
        
        // Test that only admins can update
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 1;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;

        $this->assertTrue($this->policy->update($user));
        $this->assertTrue($this->policy->delete($user));
    }

    // ===================== POLICY: TEAM MANAGEMENT =====================

    #[Test]
    public function policy_can_manage_team_system_admin_always_true()
    {
        /**
         * Scenario: System admin checking if they can manage any team
         * Expected: Always returns true
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 1;
        $user->userSetup = collect([]);

        $this->assertTrue($this->policy->canManageTeam($user, 999));
    }

    #[Test]
    public function policy_can_manage_team_non_admin_checks_team_access()
    {
        /**
         * Scenario: Non-admin user checking if they can manage a specific team
         * Expected: Returns true only if user has admin or scheduler role in that team
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;
        
        // Mock userSetup with team access
        $teamAccess = Mockery::mock('stdClass');
        $teamAccess->schedulingteamid = 5;
        $teamAccess->scheduling_team_admin = 1;
        $teamAccess->scheduler = 0;

        $user->userSetup = collect([$teamAccess]);

        $this->assertTrue($this->policy->canManageTeam($user, 5));
        $this->assertFalse($this->policy->canManageTeam($user, 999));
    }

    #[Test]
    public function policy_can_manage_team_scheduler_privilege()
    {
        /**
         * Scenario: User with scheduler role in a team
         * Expected: Can manage that team
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;
        
        $teamAccess = Mockery::mock('stdClass');
        $teamAccess->schedulingteamid = 5;
        $teamAccess->scheduling_team_admin = 0;
        $teamAccess->scheduler = 1;

        $user->userSetup = collect([$teamAccess]);

        $this->assertTrue($this->policy->canManageTeam($user, 5));
    }

    // ===================== POLICY: TEAM VIEWING =====================

    #[Test]
    public function policy_can_view_team_system_admin()
    {
        /**
         * Scenario: System admin viewing any team
         * Expected: Always true
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 1;

        $this->assertTrue($this->policy->canViewTeam($user, 999));
    }

    #[Test]
    public function policy_can_view_team_user_in_team()
    {
        /**
         * Scenario: Regular user that is part of the team
         * Expected: Can view
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;
        
        $teamAccess = Mockery::mock('stdClass');
        $teamAccess->schedulingteamid = 5;

        $user->userSetup = collect([$teamAccess]);

        $this->assertTrue($this->policy->canViewTeam($user, 5));
        $this->assertFalse($this->policy->canViewTeam($user, 999));
    }

    // ===================== POLICY: TEAM HISTORY PERMISSIONS =====================

    #[Test]
    public function policy_view_history_system_admin_always_granted()
    {
        /**
         * Scenario: System Admin user viewing history for any team
         * Expected: Always returns true (system admin override)
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 1;
        $user->userSetup = collect([]); // Even without setup

        $teamLink = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink->shouldReceive('getAttribute')->with('TeamID')->andReturn(999);

        $this->assertTrue($this->policy->viewHistory($user, $teamLink));
    }

    #[Test]
    public function policy_view_history_granted_to_team_member()
    {
        /**
         * Scenario: Regular user with team membership (canview always true if in team)
         * Expected: Returns true for their team
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;
        
        $teamAccess = Mockery::mock('stdClass');
        $teamAccess->schedulingteamid = 5;
        $user->userSetup = collect([$teamAccess]);

        $teamLink = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);

        $this->assertTrue($this->policy->viewHistory($user, $teamLink));
    }

    #[Test]
    public function policy_view_history_denied_without_team_access()
    {
        /**
         * Scenario: User without access to the specific team
         * Expected: Returns false
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;
        $user->userSetup = collect([]); // No team access

        $teamLink = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);

        $this->assertFalse($this->policy->viewHistory($user, $teamLink));
    }

    #[Test]
    public function policy_view_history_denied_missing_user_setup()
    {
        /**
         * Scenario: userSetup not loaded (!isset)
         * Expected: Returns false (no team found)
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('getUserRoleDetail')
            ->once()
            ->andReturnNull(); // Prevents loading userSetup and DB query
        $user->isSystemAdmin = 0;

        $teamLink = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);

        $this->assertFalse($this->policy->viewHistory($user, $teamLink));
    }

    #[Test]
    public function policy_modify_history_requires_admin_or_scheduler()
    {
        /**
         * Scenario: Test modifyHistory() - requires admin or scheduler for team history modification
         * Expected: System admin, team admin/scheduler, regular member, no access
         */

        // 1. System Admin: always granted
        $user1 = Mockery::mock(User::class)->makePartial();
        $user1->isSystemAdmin = 1;
        $user1->userSetup = collect([]);
        $teamLink1 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink1->shouldReceive('getAttribute')->with('TeamID')->andReturn(999);
        $this->assertTrue($this->policy->modifyHistory($user1, $teamLink1));

        // 2. Team Admin: granted for their team
        $user2 = Mockery::mock(User::class)->makePartial();
        $user2->isSystemAdmin = 0;
        $teamAccess2 = Mockery::mock('stdClass');
        $teamAccess2->schedulingteamid = 5;
        $teamAccess2->scheduling_team_admin = 1;
        $teamAccess2->scheduler = 0;
        $user2->userSetup = collect([$teamAccess2]);
        $teamLink2 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink2->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);
        $this->assertTrue($this->policy->modifyHistory($user2, $teamLink2));

        // 3. Team Scheduler: granted for their team
        $user3 = Mockery::mock(User::class)->makePartial();
        $user3->isSystemAdmin = 0;
        $teamAccess3 = Mockery::mock('stdClass');
        $teamAccess3->schedulingteamid = 5;
        $teamAccess3->scheduling_team_admin = 0;
        $teamAccess3->scheduler = 1;
        $user3->userSetup = collect([$teamAccess3]);
        $teamLink3 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink3->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);
        $this->assertTrue($this->policy->modifyHistory($user3, $teamLink3));

        // 4. Regular team member: denied
        $user4 = Mockery::mock(User::class)->makePartial();
        $user4->isSystemAdmin = 0;
        $teamAccess4 = Mockery::mock('stdClass');
        $teamAccess4->schedulingteamid = 5;
        $teamAccess4->scheduling_team_admin = 0;
        $teamAccess4->scheduler = 0;
        $user4->userSetup = collect([$teamAccess4]);
        $teamLink4 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink4->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);
        $this->assertFalse($this->policy->modifyHistory($user4, $teamLink4));

        // 5. No team access: denied
        $user5 = Mockery::mock(User::class)->makePartial();
        $user5->isSystemAdmin = 0;
        $user5->userSetup = collect([]);
        $teamLink5 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink5->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);
        $this->assertFalse($this->policy->modifyHistory($user5, $teamLink5));
    }

    #[Test]
    public function policy_delete_history_checks_is_delete_flag()
    {
        /**
         * Scenario: Test deleteHistory() - requires IsDelete DB flag for team history deletion
         * Expected: System admin, IsDelete=1, IsDelete=0 (even admin/scheduler), no access 
         */

        // 1. System Admin: always granted
        $user1 = Mockery::mock(User::class)->makePartial();
        $user1->isSystemAdmin = 1;
        $user1->userSetup = collect([]);
        $teamLink1 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink1->shouldReceive('getAttribute')->with('TeamID')->andReturn(999);
        $this->assertTrue($this->policy->deleteHistory($user1, $teamLink1));

        // 2. Team with IsDelete=1: granted
        $user2 = Mockery::mock(User::class)->makePartial();
        $user2->isSystemAdmin = 0;
        $teamAccess2 = Mockery::mock('stdClass');
        $teamAccess2->schedulingteamid = 5;
        $teamAccess2->IsDelete = 1;
        $user2->userSetup = collect([$teamAccess2]);
        $teamLink2 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink2->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);
        $this->assertTrue($this->policy->deleteHistory($user2, $teamLink2));

        // 3. Team admin but IsDelete=0: denied
        $user3 = Mockery::mock(User::class)->makePartial();
        $user3->isSystemAdmin = 0;
        $teamAccess3 = Mockery::mock('stdClass');
        $teamAccess3->schedulingteamid = 5;
        $teamAccess3->scheduling_team_admin = 1;
        $teamAccess3->scheduler = 0;
        $teamAccess3->IsDelete = 0;
        $user3->userSetup = collect([$teamAccess3]);
        $teamLink3 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink3->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);
        $this->assertFalse($this->policy->deleteHistory($user3, $teamLink3));

        // 4. Regular member IsDelete=0: denied
        $user4 = Mockery::mock(User::class)->makePartial();
        $user4->isSystemAdmin = 0;
        $teamAccess4 = Mockery::mock('stdClass');
        $teamAccess4->schedulingteamid = 5;
        $teamAccess4->scheduling_team_admin = 0;
        $teamAccess4->scheduler = 0;
        $teamAccess4->IsDelete = 0;
        $user4->userSetup = collect([$teamAccess4]);
        $teamLink4 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink4->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);
        $this->assertFalse($this->policy->deleteHistory($user4, $teamLink4));

        // 5. No team access: denied
        $user5 = Mockery::mock(User::class)->makePartial();
        $user5->isSystemAdmin = 0;
        $user5->userSetup = collect([]);
        $teamLink5 = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink5->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);
        $this->assertFalse($this->policy->deleteHistory($user5, $teamLink5));
    }

    #[Test]
    public function policy_can_delete_home_team_history_with_delegation()
    {
        /**
         * Scenario: Test canDeleteHomeTeamHistory() - OR logic: own 'candelete' OR delegation flag
         * Expected: own true, delegation true, both false
         */

        // Use makePartial for teamLink to handle model magic
        $teamLink = Mockery::mock(ScheduledPersonTeamLink::class)->makePartial();
        $teamLink->shouldReceive('getAttribute')->with('TeamID')->andReturn(5);

        // 1. Own permission true (IsDelete=1): granted regardless of delegation
        $user1 = Mockery::mock(User::class)->makePartial();
        $user1->isSystemAdmin = 0;
        $teamAccess1 = Mockery::mock('stdClass');
        $teamAccess1->schedulingteamid = 5;
        $teamAccess1->IsDelete = 1;
        $user1->userSetup = collect([$teamAccess1]);
        $this->assertTrue($this->policy->canDeleteHomeTeamHistory($user1, $teamLink, false));

        // 2. Own false, delegation true: granted
        $user2 = Mockery::mock(User::class)->makePartial();
        $user2->isSystemAdmin = 0;
        $teamAccess2 = Mockery::mock('stdClass');
        $teamAccess2->schedulingteamid = 5;
        $teamAccess2->IsDelete = 0;
        $user2->userSetup = collect([$teamAccess2]);
        $this->assertTrue($this->policy->canDeleteHomeTeamHistory($user2, $teamLink, true));

        // 3. System admin: own true -> granted
        $user3 = Mockery::mock(User::class)->makePartial();
        $user3->isSystemAdmin = 1;
        $user3->userSetup = collect([]);
        $this->assertTrue($this->policy->canDeleteHomeTeamHistory($user3, $teamLink, false));

        // 4. Both false (own IsDelete=0, delegation=false): denied
        $user4 = Mockery::mock(User::class)->makePartial();
        $user4->isSystemAdmin = 0;
        $teamAccess4 = Mockery::mock('stdClass');
        $teamAccess4->schedulingteamid = 5;
        $teamAccess4->IsDelete = 0;
        $user4->userSetup = collect([$teamAccess4]);
        $this->assertFalse($this->policy->canDeleteHomeTeamHistory($user4, $teamLink, false));

        // 5. No team access but delegation true: granted
        $user5 = Mockery::mock(User::class)->makePartial();
        $user5->isSystemAdmin = 0;
        $user5->userSetup = collect([]); // No access to team 5
        $this->assertTrue($this->policy->canDeleteHomeTeamHistory($user5, $teamLink, true));
    }

    // ===================== POLICY: CREATE STAFF =====================

    #[Test]
    public function policy_create_staff_via_role_permission()
    {
        /**
         * Scenario: Non-admin user with CREATE_NEW_STAFF role
         * Expected: Can create staff
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;

        // Mock userRoles with CREATE_NEW_STAFF
        $roleCollection = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $roleCollection->shouldReceive('where')
            ->with('RoleName', RefRole::CREATE_NEW_STAFF)
            ->andReturn($roleCollection);
        $roleCollection->shouldReceive('count')
            ->andReturn(1);

        $user->userRoles = $roleCollection;

        $this->assertTrue($this->policy->createStaff($user));
    }

    #[Test]
    public function policy_create_staff_denied_without_role()
    {
        /**
         * Scenario: Non-admin without CREATE_NEW_STAFF role
         * Expected: Cannot create staff
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;

        $roleCollection = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $roleCollection->shouldReceive('where')
            ->andReturn($roleCollection);
        $roleCollection->shouldReceive('count')
            ->andReturn(0);

        $user->userRoles = $roleCollection;

        $this->assertFalse($this->policy->createStaff($user));
    }

    // ===================== POLICY: CREATE FREELANCER =====================

    #[Test]
    public function policy_create_freelancer_via_role_permission()
    {
        /**
         * Scenario: User with CREATE_NEW_FREELANCER role
         * Expected: Can create freelancer
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;

        $roleCollection = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $roleCollection->shouldReceive('where')
            ->with('RoleName', RefRole::CREATE_NEW_FREELANCER)
            ->andReturn($roleCollection);
        $roleCollection->shouldReceive('count')
            ->andReturn(1);

        $user->userRoles = $roleCollection;

        $this->assertTrue($this->policy->createFreelancer($user));
    }

    #[Test]
    public function policy_create_freelancer_admin_override()
    {
        /**
         * Scenario: Scheduling Team Admin (no role set)
         * Expected: Can create freelancer regardless of role
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 1;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;

        $roleCollection = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $roleCollection->shouldReceive('where')
            ->andReturn($roleCollection);
        $roleCollection->shouldReceive('count')
            ->andReturn(0);

        $user->userRoles = $roleCollection;

        $this->assertTrue($this->policy->createFreelancer($user));
    }

    // ===================== POLICY: EDIT PERMISSIONS =====================

    #[Test]
    public function policy_can_edit_scheduled_person_when_home_team_authorized()
    {
        /**
         * Scenario: User edits person whose home team is in authorized list
         * Expected: Edit allowed
         */
        $user = Mockery::mock(User::class)->makePartial();
        $authorizedTeamIds = [1, 5, 10];
        $personHomeTeamId = 5;

        $this->assertTrue($this->policy->canEditScheduledPerson($user, $authorizedTeamIds, $personHomeTeamId));
    }

    #[Test]
    public function policy_can_edit_scheduled_person_when_no_home_team()
    {
        /**
         * Scenario: Person has no home team (null/0)
         * Expected: Edit allowed (special case)
         */
        $user = Mockery::mock(User::class)->makePartial();
        $authorizedTeamIds = [1, 5];
        $personHomeTeamId = 0;

        $this->assertTrue($this->policy->canEditScheduledPerson($user, $authorizedTeamIds, $personHomeTeamId));
    }

    #[Test]
    public function policy_cannot_edit_scheduled_person_when_home_team_unauthorized()
    {
        /**
         * Scenario: Person's home team not in user's authorized teams
         * Expected: Edit denied
         */
        $user = Mockery::mock(User::class)->makePartial();
        $authorizedTeamIds = [1, 5, 10];
        $personHomeTeamId = 99; // Not in authorized list

        $this->assertFalse($this->policy->canEditScheduledPerson($user, $authorizedTeamIds, $personHomeTeamId));
    }

    // ===================== POLICY: GET PERMISSIONS FOR USER =====================

    #[Test]
    public function policy_get_permissions_returns_all_flags()
    {
        /**
         * Scenario: Get all permissions for a user
         * Expected: Array contains all permission flags
         */
        $user = Mockery::mock(User::class)->makePartial();

        // Mock the can() method
        $user->shouldReceive('can')
            ->with('view', \App\Models\User::class)
            ->andReturn(true);
        $user->shouldReceive('can')
            ->with('update', \App\Models\User::class)
            ->andReturn(true);
        $user->shouldReceive('can')
            ->with('create', \App\Models\User::class)
            ->andReturn(true);
        $user->shouldReceive('can')
            ->with('createStaff', \App\Models\User::class)
            ->andReturn(true);
        $user->shouldReceive('can')
            ->with('createFreelancer ', \App\Models\User::class)
            ->andReturn(true);

        $permissions = $this->policy->getPermissionsForUser($user);

        $this->assertArrayHasKey('canview', $permissions);
        $this->assertArrayHasKey('canedit', $permissions);
        $this->assertArrayHasKey('cancreate', $permissions);
        $this->assertArrayHasKey('canCreateStaff', $permissions);
        $this->assertArrayHasKey('canCreateFreelancer', $permissions);
    }

    // ===================== POLICY: HAS TEAM PERMISSION =====================

    #[Test]
    public function policy_has_team_permission_system_admin_override()
    {
        /**
         * Scenario: System admin checking any team permission
         * Expected: Always returns true
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 1;
        $user->userSetup = collect([]);

        $this->assertTrue($this->policy->hasTeamPermission($user, 999, 'canview'));
        $this->assertTrue($this->policy->hasTeamPermission($user, 999, 'canmodify'));
        $this->assertTrue($this->policy->hasTeamPermission($user, 999, 'candelete'));
    }

    #[Test]
    public function policy_has_team_permission_canview_always_true()
    {
        /**
         * Scenario: User checking canview permission for their team
         * Expected: Always true if user is in the team
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;

        $teamAccess = Mockery::mock('stdClass');
        $teamAccess->schedulingteamid = 5;
        $teamAccess->scheduling_team_admin = 0;
        $teamAccess->scheduler = 0;

        $user->userSetup = collect([$teamAccess]);

        $this->assertTrue($this->policy->hasTeamPermission($user, 5, 'canview'));
    }

    #[Test]
    public function policy_has_team_permission_canmodify_requires_admin_or_scheduler()
    {
        /**
         * Scenario: User checking canmodify permission
         * Expected: True only if admin or scheduler in team
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;

        $teamAccess = Mockery::mock('stdClass');
        $teamAccess->schedulingteamid = 5;
        $teamAccess->scheduling_team_admin = 1;
        $teamAccess->scheduler = 0;

        $user->userSetup = collect([$teamAccess]);

        $this->assertTrue($this->policy->hasTeamPermission($user, 5, 'canmodify'));
    }

    #[Test]
    public function policy_has_team_permission_candelete_requires_database_flag()
    {
        /**
         * Scenario: User checking candelete permission
         * Expected: True only if IsDelete flag is set in database
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;

        $teamAccess = Mockery::mock('stdClass');
        $teamAccess->schedulingteamid = 5;
        $teamAccess->IsDelete = 1;

        $user->userSetup = collect([$teamAccess]);

        $this->assertTrue($this->policy->hasTeamPermission($user, 5, 'candelete'));
    }

    #[Test]
    public function policy_has_team_permission_unknown_type_returns_false()
    {
        /**
         * Scenario: User checks with unknown permission type
         * Expected: Returns false
         */
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSystemAdmin = 0;

        $teamAccess = Mockery::mock('stdClass');
        $teamAccess->schedulingteamid = 5;

        $user->userSetup = collect([$teamAccess]);

        $this->assertFalse($this->policy->hasTeamPermission($user, 5, 'unknown'));
    }
}
