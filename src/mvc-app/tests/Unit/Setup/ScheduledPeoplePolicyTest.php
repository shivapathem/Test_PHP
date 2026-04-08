<?php

namespace Tests\Unit\Setup;

use Tests\TestCase;
use App\Policies\Setup\ScheduledPeoplePolicy;
use App\Models\User;
use App\Models\User\RefRole;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ScheduledPeoplePolicyTest extends TestCase
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

    #[Test]
    public function createAny_returns_true_if_user_can_create_staff()
    {
        // Arrange
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        
        // Mock userRoles relationship - handle both staff and freelancer checks
        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        
        // First call checks for CREATE_NEW_STAFF (should return 1)
        $staffCheckMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $staffCheckMock->shouldReceive('count')->andReturn(1);
        
        // Second call checks for CREATE_NEW_FREELANCER (not needed but setup just in case)
        $freelancerCheckMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $freelancerCheckMock->shouldReceive('count')->andReturn(0);
        
        $userRolesMock->shouldReceive('where')
             ->with('RoleName', RefRole::CREATE_NEW_STAFF)
             ->andReturn($staffCheckMock);
        $userRolesMock->shouldReceive('where')
             ->with('RoleName', RefRole::CREATE_NEW_FREELANCER)
             ->andReturn($freelancerCheckMock);
        $user->userRoles = $userRolesMock;

        // Act
        $result = $this->policy->createAny($user);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function createAny_returns_true_if_user_can_create_freelancer()
    {
        // Arrange
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        
        // Mock userRoles relationship - handle both staff and freelancer checks
        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        
        // First call checks for CREATE_NEW_STAFF (should return 0)
        $staffCheckMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $staffCheckMock->shouldReceive('count')->andReturn(0);
        
        // Second call checks for CREATE_NEW_FREELANCER (should return 1)
        $freelancerCheckMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $freelancerCheckMock->shouldReceive('count')->andReturn(1);
        
        $userRolesMock->shouldReceive('where')
             ->with('RoleName', RefRole::CREATE_NEW_STAFF)
             ->once()
             ->andReturn($staffCheckMock);
        $userRolesMock->shouldReceive('where')
             ->with('RoleName', RefRole::CREATE_NEW_FREELANCER)
             ->once()
             ->andReturn($freelancerCheckMock);
        $user->userRoles = $userRolesMock;

        // Act
        $result = $this->policy->createAny($user);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function createAny_returns_true_if_user_can_create_both_staff_and_freelancer()
    {
        // Arrange
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        
        // Mock userRoles with both staff and freelancer permissions
        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        
        // First call checks for CREATE_NEW_STAFF (should return 1, so freelancer check not called due to || operator)
        $staffCheckMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $staffCheckMock->shouldReceive('count')->andReturn(1);
        
        $userRolesMock->shouldReceive('where')
             ->with('RoleName', RefRole::CREATE_NEW_STAFF)
             ->andReturn($staffCheckMock);
        $user->userRoles = $userRolesMock;

        // Act
        $result = $this->policy->createAny($user);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function createAny_returns_true_if_user_is_scheduling_team_admin()
    {
        // Arrange
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 1;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        
        // Mock empty userRoles collection
        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $userRolesMock->shouldReceive('where')
             ->andReturn($userRolesMock);
        $userRolesMock->shouldReceive('count')
             ->andReturn(0);
        $user->userRoles = $userRolesMock;

        // Act
        $result = $this->policy->createAny($user);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function createAny_returns_true_if_user_is_divisional_admin()
    {
        // Arrange
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 1;
        $user->isSystemAdmin = 0;
        
        // Mock empty userRoles collection
        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $userRolesMock->shouldReceive('where')
             ->andReturn($userRolesMock);
        $userRolesMock->shouldReceive('count')
             ->andReturn(0);
        $user->userRoles = $userRolesMock;

        // Act
        $result = $this->policy->createAny($user);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function createAny_returns_true_if_user_is_system_admin()
    {
        // Arrange
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 1;
        
        // Mock empty userRoles collection
        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $userRolesMock->shouldReceive('where')
             ->andReturn($userRolesMock);
        $userRolesMock->shouldReceive('count')
             ->andReturn(0);
        $user->userRoles = $userRolesMock;

        // Act
        $result = $this->policy->createAny($user);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function createAny_returns_false_if_user_has_no_permissions()
    {
        // Arrange
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;
        
        // Mock empty userRoles collection
        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $userRolesMock->shouldReceive('where')
             ->andReturn($userRolesMock);
        $userRolesMock->shouldReceive('count')
             ->andReturn(0);
        $user->userRoles = $userRolesMock;

        // Act
        $result = $this->policy->createAny($user);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function create_returns_true_if_user_is_scheduling_team_admin()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 1;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;

        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $userRolesMock->shouldReceive('where')->andReturn($userRolesMock);
        $userRolesMock->shouldReceive('count')->andReturn(0);
        $user->userRoles = $userRolesMock;

        $this->assertTrue($this->policy->create($user));
    }

    #[Test]
    public function update_returns_true_if_user_has_create_new_freelancer_role()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;

        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $staffCheckMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $staffCheckMock->shouldReceive('count')->andReturn(0);

        $freelanceCheckMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $freelanceCheckMock->shouldReceive('count')->andReturn(1);

        $userRolesMock->shouldReceive('where')->with('RoleName', RefRole::CREATE_NEW_STAFF)->andReturn($staffCheckMock);
        $userRolesMock->shouldReceive('where')->with('RoleName', RefRole::CREATE_NEW_FREELANCER)->andReturn($freelanceCheckMock);

        $user->userRoles = $userRolesMock;

        $this->assertTrue($this->policy->update($user));
    }

    #[Test]
    public function create_returns_false_if_user_has_no_permissions()
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->isSchedulingTeamAdmin = 0;
        $user->isDivisionalAdmin = 0;
        $user->isSystemAdmin = 0;

        $userRolesMock = Mockery::mock('Illuminate\Database\Eloquent\Collection');
        $userRolesMock->shouldReceive('where')->andReturn($userRolesMock);
        $userRolesMock->shouldReceive('count')->andReturn(0);
        $user->userRoles = $userRolesMock;

        $this->assertFalse($this->policy->create($user));
        $this->assertFalse($this->policy->update($user));
    }
}
