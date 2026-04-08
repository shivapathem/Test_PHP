<?php

namespace Tests\Unit\ScheduledPeople;

use App\Models\User;
use App\Repositories\Admin\ScheduledPeopleRepository;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class ScheduledPeopleRepositoryTest extends TestCase
{
    private const TEAM_A = 'Team A';
    private const TEAM_B = 'Team B';

    protected function tearDown(): void
    {
        Mockery::close();
        Mockery::resetContainer();
        
        // Ensure Mockery handlers are restored
        $currentErrorHandler = set_error_handler(null);
        if ($currentErrorHandler !== null) {
            restore_error_handler();
        }
        $currentExceptionHandler = set_exception_handler(null);
        if ($currentExceptionHandler !== null) {
            restore_exception_handler();
        }
        
        // Force restore in case
        @restore_error_handler();
        @restore_exception_handler();
        
        parent::tearDown();
    }

    public function test_getSchedulingTeams_includes_all_teams_for_move_person_between_teams()
    {
        $userId = 123;
        $pageId = 10;
        $schedulePersonId = 20;
        $actionType = 'Edit';

        $baseRows = [
            (object)['TeamID' => 100, 'TeamName' => self::TEAM_A],
            (object)['TeamID' => 101, 'TeamName' => self::TEAM_B],
        ];

        DB::shouldReceive('select')
            ->once()
            ->with(
                'exec [dbo].[usp_get_GetUserTeamListByUserPermission] ?,?,?,?',
                [$userId, $actionType, $pageId, $schedulePersonId]
            )
            ->andReturn($baseRows);

        $userModel = Mockery::mock(User::class);
        $user = Mockery::mock();
        $userRoles = Mockery::mock();
        $userRoles->shouldReceive('whereHas')
            ->once()
            ->with('role', Mockery::type('Closure'))
            ->andReturnSelf();
        $userRoles->shouldReceive('exists')->once()->andReturn(true);
        $user->shouldReceive('userRoles')->once()->andReturn($userRoles);

        $userAccessibleQuery = Mockery::mock();
        $userAccessibleQuery->shouldReceive('select')
            ->once()
            ->with('schedulingTeamId as TeamID', 'schedulingTeamName as TeamName')
            ->andReturnSelf();
        $userAccessibleQuery->shouldReceive('get')
            ->once()
            ->andReturn(collect([
                ['TeamID' => 201, 'TeamName' => 'Areas Team 1'],
                ['TeamID' => 100, 'TeamName' => self::TEAM_A], // reused constant
            ]));

        $user->shouldReceive('userAccessibleTeams')->once()->andReturn($userAccessibleQuery);
        $user->shouldReceive('can')->with('createStaff', 'scheduled-people')->andReturn(false);

        $userModel->shouldReceive('find')->once()->with($userId)->andReturn($user);
        $specialQuery = Mockery::mock();
        $specialQuery->shouldReceive('whereIn')->andReturnSelf();
        $specialQuery->shouldReceive('orWhere')->andReturnSelf();
        $specialQuery->shouldReceive('where')->andReturnSelf();
        $specialQuery->shouldReceive('select')->andReturnSelf();
        $specialQuery->shouldReceive('get')->once()->andReturn(collect([
            (object)['TeamID' => 301, 'TeamName' => 'Freelancers'],
            (object)['TeamID' => 302, 'TeamName' => 'Archive'],
            (object)['TeamID' => 303, 'TeamName' => 'Other BBC'],
            (object)['TeamID' => 304, 'TeamName' => 'Maternity/Paternity'],
            (object)['TeamID' => 305, 'TeamName' => 'Apprentices'],
        ]));

        DB::shouldReceive('table')
            ->once()
            ->with('schedulingTeams')
            ->andReturn($specialQuery);

        $repo = new ScheduledPeopleRepository($userModel);
        $actual = $repo->getSchedulingTeams($userId, $actionType, $pageId, $schedulePersonId);

        $expectedNames = [
            self::TEAM_A,
            self::TEAM_B,
            'Areas Team 1',
            'Freelancers',
            'Archive',
            'Other BBC',
            'Maternity/Paternity',
            'Apprentices'
        ];

        $actualNames = array_column($actual, 'TeamName');

        sort($expectedNames);
        sort($actualNames);

        $this->assertEquals($expectedNames, $actualNames);

        $actualIds = array_column($actual, 'TeamID');
        $this->assertContains(201, $actualIds);
        $this->assertContains(301, $actualIds);
        $this->assertContains(302, $actualIds);
        $this->assertContains(303, $actualIds);
        $this->assertContains(304, $actualIds);
        $this->assertContains(305, $actualIds);
    }

    public function test_getSchedulingTeams_returns_base_teams_only_without_move_role()
    {
        $userId = 555;
        $pageId = 6;
        $schedulePersonId = 9;
        $actionType = 'edit';

        $baseRows = [
            (object)['TeamID' => 400, 'TeamName' => 'Alpha'],
            (object)['TeamID' => 401, 'TeamName' => 'Beta'],
        ];

        DB::shouldReceive('select')
            ->once()
            ->with(
                'exec [dbo].[usp_get_GetUserTeamListByUserPermission] ?,?,?,?',
                [$userId, $actionType, $pageId, $schedulePersonId]
            )
            ->andReturn($baseRows);

        $user = Mockery::mock();
        $userRoles = Mockery::mock();
        $userRoles->shouldReceive('whereHas')
            ->once()
            ->with('role', Mockery::type('Closure'))
            ->andReturnSelf();
        $userRoles->shouldReceive('exists')->once()->andReturn(false);
        $user->shouldReceive('userRoles')->once()->andReturn($userRoles);
        $user->shouldReceive('can')->with('createStaff', 'scheduled-people')->andReturn(false);

        $userModel = Mockery::mock(User::class);
        $userModel->shouldReceive('find')->once()->with($userId)->andReturn($user);
        $repo = new ScheduledPeopleRepository($userModel);
        $actual = $repo->getSchedulingTeams($userId, $actionType, $pageId, $schedulePersonId);

        $this->assertCount(2, $actual);
        $this->assertEquals('Alpha', $actual[0]['TeamName']);
        $this->assertEquals('Beta', $actual[1]['TeamName']);
    }
}
