<?php

namespace Tests\Unit\ScheduledPeople;

use Tests\TestCase;
use App\Http\Controllers\Setup\ScheduledPeopleController;
use App\Models\User;
use App\Repositories\Contracts\Admin\ScheduledPeopleRepositoryInterface;
use App\Http\Requests\ScheduledPeopleRequest\ScheduledPersonRequest;
use App\Http\Requests\ScheduledPeopleRequest\UpdateScheduledPersonRequest;
use App\Http\Requests\ScheduledPeopleRequest\ValidateAdditionalTeamRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ScheduledPeopleControllerTest extends TestCase
{
    protected $controller;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ScheduledPeopleRepositoryInterface::class);
        $this->controller = new ScheduledPeopleController($this->mockRepository);
    }

    protected function tearDown(): void
    {
        \Illuminate\Support\Facades\Facade::clearResolvedInstances();
        Mockery::close();
        parent::tearDown();
    }

    // ===================== INSTANTIATION TESTS =====================

    #[Test]
    public function it_can_instantiate_scheduled_people_controller()
    {
        $this->assertNotNull($this->controller);
        $this->assertInstanceOf(ScheduledPeopleController::class, $this->controller);
    }

    // ===================== INDEX TESTS =====================

    #[Test]
    public function index_returns_scheduled_people_index_view()
    {
        $response = $this->controller->index();

        // Verify the response is a View object from the view() helper
        $this->assertInstanceOf('Illuminate\View\View', $response);
    }

    #[Test]
    public function index_handles_exception_gracefully()
    {
        View::shouldReceive('make')
            ->once()
            ->andThrow(new \Exception('Test exception'));

        $response = $this->controller->index();

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Failed to load scheduled people.', $data['message']);
    }

    // ===================== CREATE TESTS =====================

    #[Test]
    public function create_returns_index_view_with_create_mode()
    {
        Gate::shouldReceive('authorize')
            ->with('create', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('useraction', 'create')
            ->andReturn('create');
        $mockRequest->shouldReceive('input')
            ->with('selectedteamid', '')
            ->andReturn('');
        $mockRequest->shouldReceive('input')
            ->with('selecteduserid', '')
            ->andReturn('');
        $mockRequest->shouldReceive('input')
            ->with('schedulepersonid', null)
            ->andReturn(null);

        $response = $this->controller->create($mockRequest);

        // Verify the response is a View object
        $this->assertInstanceOf('Illuminate\View\View', $response);
    }

    #[Test]
    public function create_returns_edit_mode_with_pre_selected_values()
    {
        Gate::shouldReceive('authorize')
            ->with('create', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('useraction', 'create')
            ->andReturn('edit');
        $mockRequest->shouldReceive('input')
            ->with('selectedteamid', '')
            ->andReturn(5);
        $mockRequest->shouldReceive('input')
            ->with('selecteduserid', '')
            ->andReturn(10);
        $mockRequest->shouldReceive('input')
            ->with('schedulepersonid', null)
            ->andReturn(1);

        $response = $this->controller->create($mockRequest);

        // Verify the response is a View object
        $this->assertInstanceOf('Illuminate\View\View', $response);
    }

    // ===================== STORE TESTS =====================

    #[Test]
    public function store_creates_scheduled_person_successfully()
    {
        Gate::shouldReceive('authorize')
            ->with('create', 'scheduled-people')
            ->andReturn(true);

        $mockUser = Mockery::mock(User::class)->makePartial();
        $mockUser->id = 1;

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        $mockRequest = Mockery::mock(ScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([
                'SPTeamID' => 1,
                'DisplayName' => 'John Doe',
                'SchedulingTeamID' => 5,
                'HomeTeamStartDate' => '2026-01-01',
                'HomeTeamEndDate' => '2026-12-31',
                'HomeTeamSortCode' => 'JD',
                'HomeTeamBackColour' => '#FFFFFF',
                'HomeTeamFontColour' => '#000000',
                'HomeTeamAdminNotes' => 'Admin notes',
                'HomeTeamFWANotes' => 'FWA notes',
                'ActionType' => 'create',
                'ScheduledPersonID' => 0,
                'AdditionalTeamArray' => '[]',
                'DisplayFirstName' => 'John',
                'DisplayLastName' => 'Doe',
                'IsDefaultBGColour' => 1,
                'IsAdditionalLeave' => 0
            ]);

        $this->mockRepository
            ->shouldReceive('createSchedulePerson')
            ->once()
            ->with(1, 'John Doe', 5, '2026-01-01', '2026-12-31', 'JD', '#FFFFFF', '#000000', 'Admin notes', 'FWA notes', 'create', 0, '[]', 1, 'John', 'Doe', 1, 0)
            ->andReturn([
                'intnewidschpeople' => 100,
                'strstatusschpeople' => 'success',
                'strreturnstringschpeople' => 'Scheduled person created successfully'
            ]);

        $response = $this->controller->store($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(100, $data['id']);
    }

    #[Test]
    public function store_returns_error_for_empty_response()
    {
        Gate::shouldReceive('authorize')
            ->with('create', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        $mockRequest = Mockery::mock(ScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([
                'SPTeamID' => 1,
                'DisplayName' => 'John Doe',
                'SchedulingTeamID' => 5,
                'HomeTeamStartDate' => '2026-01-01',
                'HomeTeamEndDate' => '2026-12-31',
                'HomeTeamSortCode' => 'JD',
                'HomeTeamBackColour' => '#FFFFFF',
                'HomeTeamFontColour' => '#000000',
                'HomeTeamAdminNotes' => '',
                'HomeTeamFWANotes' => '',
                'ActionType' => 'create',
                'ScheduledPersonID' => 0,
                'AdditionalTeamArray' => '[]',
                'DisplayFirstName' => 'John',
                'DisplayLastName' => 'Doe',
                'IsDefaultBGColour' => 0,
                'IsAdditionalLeave' => 0
            ]);

        $this->mockRepository
            ->shouldReceive('createSchedulePerson')
            ->once()
            ->andReturn(null);

        $response = $this->controller->store($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Unable to process your request. Please try again.', $data['message']);
    }

    #[Test]
    public function store_returns_user_error_from_repository()
    {
        Gate::shouldReceive('authorize')
            ->with('create', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        $mockRequest = Mockery::mock(ScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([
                'SPTeamID' => 1,
                'DisplayName' => 'John Doe',
                'SchedulingTeamID' => 5,
                'HomeTeamStartDate' => '2026-01-01',
                'HomeTeamEndDate' => '2026-12-31',
                'HomeTeamSortCode' => 'JD',
                'HomeTeamBackColour' => '#FFFFFF',
                'HomeTeamFontColour' => '#000000',
                'HomeTeamAdminNotes' => '',
                'HomeTeamFWANotes' => '',
                'ActionType' => 'create',
                'ScheduledPersonID' => 0,
                'AdditionalTeamArray' => '[]',
                'DisplayFirstName' => 'John',
                'DisplayLastName' => 'Doe',
                'IsDefaultBGColour' => 0,
                'IsAdditionalLeave' => 0
            ]);

        $this->mockRepository
            ->shouldReceive('createSchedulePerson')
            ->once()
            ->andReturn([
                'strstatusschpeople' => 'usererror',
                'strreturnstringschpeople' => 'Invalid team selection'
            ]);

        $response = $this->controller->store($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals(422, $response->getStatusCode());
    }

    #[Test]
    public function store_handles_exception()
    {
        Gate::shouldReceive('authorize')
            ->with('create', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(ScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);

        $this->controller->store($mockRequest);
    }

    // ===================== SHOW TESTS =====================

    #[Test]
    public function show_returns_view_with_person_details()
    {
        Gate::shouldReceive('authorize')
            ->with('view', 'scheduled-people')
            ->andReturn(true);

        $personId = 5;
        $mockPerson = [
            'id' => 5,
            'name' => 'John Doe',
            'team' => 'Team A'
        ];

        $this->mockRepository
            ->shouldReceive('getSchedulepersondetails')
            ->once()
            ->with($personId)
            ->andReturn($mockPerson);

        View::shouldReceive('make')
            ->once()
            ->with('pages.setup.scheduled-people.index', [
                'mode' => 'view',
                'person' => $mockPerson,
                'personId' => $personId
            ])
            ->andReturn('mocked_view');

        $response = $this->controller->show($personId);

        $this->assertEquals('mocked_view', $response);
    }

    #[Test]
    public function show_throws_404_for_non_existent_person()
    {
        Gate::shouldReceive('authorize')
            ->with('view', 'scheduled-people')
            ->andReturn(true);

        $personId = 999;

        $this->mockRepository
            ->shouldReceive('getSchedulepersondetails')
            ->once()
            ->with($personId)
            ->andReturn([]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->controller->show($personId);
    }

    // ===================== EDIT TESTS =====================

    #[Test]
    public function edit_returns_view_with_person_details()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        Auth::shouldReceive('user')
            ->andReturn(Mockery::mock(\App\Models\User::class)->makePartial());

        $personId = 5;
        $mockPerson = [
            ['id' => 5, 'name' => 'John Doe', 'team' => 'Team A', 'TeamID' => 1]
        ];

        $mockTeams = [
            ['TeamID' => 1],
            ['TeamID' => 2]
        ];

        $this->mockRepository
            ->shouldReceive('getSchedulingTeams')
            ->once()
            ->with(1, 'edit', 6, $personId)
            ->andReturn($mockTeams);

        $this->mockRepository
            ->shouldReceive('getSchedulepersondetails')
            ->once()
            ->with($personId)
            ->andReturn($mockPerson);

        View::shouldReceive('make')
            ->once()
            ->with('pages.setup.scheduled-people.index', [
                'mode' => 'edit',
                'person' => $mockPerson,
                'personId' => $personId
            ])
            ->andReturn('mocked_view');

        $response = $this->controller->edit($personId);

        $this->assertEquals('mocked_view', $response);
    }

    #[Test]
    public function edit_throws_404_for_invalid_id()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->controller->edit(0);
    }

    #[Test]
    public function edit_throws_404_for_non_existent_person()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $personId = 999;

        $this->mockRepository
            ->shouldReceive('getSchedulepersondetails')
            ->once()
            ->with($personId)
            ->andReturn([]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->controller->edit($personId);
    }

    // ===================== UPDATE TESTS =====================

    #[Test]
    public function update_updates_scheduled_person_successfully()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        Auth::shouldReceive('user')
            ->andReturn(Mockery::mock(\App\Models\User::class)->makePartial());

        $personId = 5;
        $mockRequest = Mockery::mock(UpdateScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([
                'SPTeamID' => 1,
                'DisplayName' => 'Jane Doe',
                'SchedulingTeamID' => 5,
                'HomeTeamStartDate' => '2026-01-01',
                'HomeTeamEndDate' => '2026-12-31',
                'HomeTeamSortCode' => 'JD',
                'HomeTeamBackColour' => '#FFFFFF',
                'HomeTeamFontColour' => '#000000',
                'HomeTeamAdminNotes' => 'Updated notes',
                'HomeTeamFWANotes' => '',
                'DisplayFirstName' => 'Jane',
                'DisplayLastName' => 'Doe',
                'IsDefaultBGColour' => 1,
                'IsAdditionalLeave' => 0,
                'AdditionalTeamArray' => '[]'
            ]);

        $mockTeams = [
            ['TeamID' => 5],
            ['TeamID' => 6]
        ];

        $mockPerson = [
            ['TeamID' => 5]
        ];

        $this->mockRepository
            ->shouldReceive('getSchedulingTeams')
            ->once()
            ->with(1, 'edit', 6, $personId)
            ->andReturn($mockTeams);

        $this->mockRepository
            ->shouldReceive('getSchedulepersondetails')
            ->once()
            ->with($personId)
            ->andReturn($mockPerson);

        $this->mockRepository
            ->shouldReceive('createSchedulePerson')
            ->once()
            ->with(1, 'Jane Doe', 5, '2026-01-01', '2026-12-31', 'JD', '#FFFFFF', '#000000', 'Updated notes', '', 'edit', 5, '[]', 1, 'Jane', 'Doe', 1, 0)
            ->andReturn([
                'strstatusschpeople' => 'success',
                'strreturnstringschpeople' => 'Scheduled person updated successfully'
            ]);

        $response = $this->controller->update($mockRequest, $personId);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
    }

    #[Test]
    public function update_returns_error_for_invalid_id()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(UpdateScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->andReturn([]);

        $response = $this->controller->update($mockRequest, 0);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Invalid scheduled person ID.', $data['message']);
    }

    #[Test]
    public function update_handles_empty_dates()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        Auth::shouldReceive('user')
            ->andReturn(Mockery::mock(\App\Models\User::class)->makePartial());

        $personId = 5;
        $mockRequest = Mockery::mock(UpdateScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([
                'SPTeamID' => 1,
                'DisplayName' => 'John Doe',
                'SchedulingTeamID' => 5,
                'HomeTeamStartDate' => '',
                'HomeTeamEndDate' => '',
                'HomeTeamSortCode' => 'JD',
                'HomeTeamBackColour' => '#FFFFFF',
                'HomeTeamFontColour' => '#000000',
                'HomeTeamAdminNotes' => '',
                'HomeTeamFWANotes' => '',
                'DisplayFirstName' => 'John',
                'DisplayLastName' => 'Doe',
                'IsDefaultBGColour' => 0,
                'IsAdditionalLeave' => 0,
                'AdditionalTeamArray' => '[]'
            ]);

        $mockTeams = [
            ['TeamID' => 5],
            ['TeamID' => 6]
        ];

        $mockPerson = [
            ['TeamID' => 5]
        ];

        $this->mockRepository
            ->shouldReceive('getSchedulingTeams')
            ->once()
            ->with(1, 'edit', 6, $personId)
            ->andReturn($mockTeams);

        $this->mockRepository
            ->shouldReceive('getSchedulepersondetails')
            ->once()
            ->with($personId)
            ->andReturn($mockPerson);

        $this->mockRepository
            ->shouldReceive('createSchedulePerson')
            ->once()
            ->with(1, 'John Doe', 5, '', '', 'JD', '#FFFFFF', '#000000', '', '', 'edit', 5, '[]', 1, 'John', 'Doe', 0, 0)
            ->andReturn([
                'strstatusschpeople' => 'success',
                'strreturnstringschpeople' => ''
            ]);

        $response = $this->controller->update($mockRequest, $personId);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
    }

    // ===================== TEAM LIST TESTS =====================

    #[Test]
    public function team_list_returns_filtered_teams_based_on_permissions()
    {
        try {
            Gate::shouldReceive('authorize')
                ->with('createAny', 'scheduled-people')
                ->andReturn(true);

            // Mock user with permissions
            $mockUser = Mockery::mock(\App\Models\User::class);
            $mockUser->shouldReceive('can')
                ->with('createStaff', 'scheduled-people')
                ->andReturn(true);
            $mockUser->shouldReceive('can')
                ->with('createFreelancer', 'scheduled-people')
                ->andReturn(false);

            Auth::shouldReceive('user')
                ->andReturn($mockUser);
            Auth::shouldReceive('id')
                ->andReturn(1);

            // Mock teams with proper structure (objects with TeamName property)
            $mockTeams = [
                (object)['TeamID' => 1, 'TeamName' => 'Team A'],
                (object)['TeamID' => 2, 'TeamName' => 'Freelancers'],
                (object)['TeamID' => 3, 'TeamName' => 'Team B']
            ];

            $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
            $mockRequest->shouldReceive('input')
                ->with('actionType', 'create')
                ->andReturn('create');
            $mockRequest->shouldReceive('input')
                ->with('personId', 0)
                ->andReturn(0);

            $this->mockRepository
                ->shouldReceive('getSchedulingTeams')
                ->once()
                ->with(1, 'create', 6, 0)
                ->andReturn($mockTeams);

            $response = $this->controller->teamList($mockRequest);

            $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
            $data = $response->getData(true);
            // Should return only non-Freelancer teams (Team A and Team B)
            $this->assertCount(2, $data);
            $this->assertEquals('Team A', $data[0]['TeamName']);
            $this->assertEquals('Team B', $data[1]['TeamName']);
        } finally {
            // Clean up exception handlers
            restore_exception_handler();
        }
    }

    #[Test]
    public function team_list_returns_only_freelancers_when_freelancer_permission_only()
    {
        try {
            Gate::shouldReceive('authorize')
                ->with('createAny', 'scheduled-people')
                ->andReturn(true);

            // Mock user with only freelancer permission
            $mockUser = Mockery::mock(\App\Models\User::class);
            $mockUser->shouldReceive('can')
                ->with('createStaff', 'scheduled-people')
                ->andReturn(false);
            $mockUser->shouldReceive('can')
                ->with('createFreelancer', 'scheduled-people')
                ->andReturn(true);

            Auth::shouldReceive('user')
                ->andReturn($mockUser);
            Auth::shouldReceive('id')
                ->andReturn(1);

            $mockTeams = [
                (object)['TeamID' => 1, 'TeamName' => 'Team A'],
                (object)['TeamID' => 2, 'TeamName' => 'Freelancers'],
                (object)['TeamID' => 3, 'TeamName' => 'Team B']
            ];

            $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
            $mockRequest->shouldReceive('input')
                ->with('actionType', 'create')
                ->andReturn('create');
            $mockRequest->shouldReceive('input')
                ->with('personId', 0)
                ->andReturn(0);

            $this->mockRepository
                ->shouldReceive('getSchedulingTeams')
                ->once()
                ->with(1, 'create', 6, 0)
                ->andReturn($mockTeams);

            $response = $this->controller->teamList($mockRequest);

            $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
            $data = $response->getData(true);
            // Should return only Freelancers team
            $this->assertCount(1, $data);
            $this->assertEquals('Freelancers', $data[0]['TeamName']);
        } finally {
            // Clean up exception handlers
            restore_exception_handler();
        }
    }

    #[Test]
    public function team_list_returns_all_teams_when_both_permissions()
    {
        try {
            Gate::shouldReceive('authorize')
                ->with('createAny', 'scheduled-people')
                ->andReturn(true);

            // Mock user with both permissions
            $mockUser = Mockery::mock(\App\Models\User::class);
            $mockUser->shouldReceive('can')
                ->with('createStaff', 'scheduled-people')
                ->andReturn(true);
            $mockUser->shouldReceive('can')
                ->with('createFreelancer', 'scheduled-people')
                ->andReturn(true);

            Auth::shouldReceive('user')
                ->andReturn($mockUser);
            Auth::shouldReceive('id')
                ->andReturn(1);

            $mockTeams = [
                (object)['TeamID' => 1, 'TeamName' => 'Team A'],
                (object)['TeamID' => 2, 'TeamName' => 'Freelancers'],
                (object)['TeamID' => 3, 'TeamName' => 'Team B']
            ];

            $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
            $mockRequest->shouldReceive('input')
                ->with('actionType', 'create')
                ->andReturn('create');
            $mockRequest->shouldReceive('input')
                ->with('personId', 0)
                ->andReturn(0);

            $this->mockRepository
                ->shouldReceive('getSchedulingTeams')
                ->once()
                ->with(1, 'create', 6, 0)
                ->andReturn($mockTeams);

            $response = $this->controller->teamList($mockRequest);

            $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
            $data = $response->getData(true);
            // Should return all teams including Freelancers
            $this->assertCount(3, $data);
            $this->assertEquals('Team A', $data[0]['TeamName']);
            $this->assertEquals('Freelancers', $data[1]['TeamName']);
            $this->assertEquals('Team B', $data[2]['TeamName']);
        } finally {
            // Clean up exception handlers
            restore_exception_handler();
        }
    }

    // ===================== DETAILS TESTS =====================

    #[Test]
    public function details_returns_person_details_json()
    {
        $personId = 5;
        $mockDetails = [
            'id' => 5,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $this->mockRepository
            ->shouldReceive('getPersonDetails')
            ->once()
            ->with($personId)
            ->andReturn($mockDetails);

        $response = $this->controller->details($personId);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals($mockDetails, $data);
    }

    // ===================== TEAM HISTORY TESTS =====================

    #[Test]
    public function team_history_returns_history_data()
    {
        Gate::shouldReceive('authorize')
            ->with('view', 'scheduled-people')
            ->andReturn(true);

        $mockUser = new class {
            public $id = 1;
        };

        Auth::shouldReceive('user')
            ->andReturn($mockUser);

        $personId = 5;
        $mockHistory = [
            ['date' => '2026-01-01', 'action' => 'Created'],
            ['date' => '2026-06-01', 'action' => 'Updated']
        ];

        $this->mockRepository
            ->shouldReceive('getScheduleTeamHistory')
            ->once()
            ->with($personId, 1)
            ->andReturn($mockHistory);

        $response = $this->controller->teamHistory($personId);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals($mockHistory, $data['data']);
    }

    #[Test]
    public function team_history_returns_error_for_invalid_id()
    {
        Gate::shouldReceive('authorize')
            ->with('view', 'scheduled-people')
            ->andReturn(true);

        $response = $this->controller->teamHistory(0);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Invalid scheduled person ID.', $data['message']);
    }

    #[Test]
    public function team_history_handles_exception()
    {
        Gate::shouldReceive('authorize')
            ->with('view', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andThrow(new \Exception('Database error'));

        $personId = 5;

        $response = $this->controller->teamHistory($personId);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
    }

    // ===================== VALIDATE ADDITIONAL TEAM TESTS =====================

    #[Test]
    public function validate_additional_team_returns_validation_results()
    {
        $mockRequest = Mockery::mock(ValidateAdditionalTeamRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([
                'schedulepersonid' => 5,
                'ddlteamsid' => 2,
                'addteamsid' => 3,
                'addteamstartdate' => '2026-01-01',
                'addteamenddate' => '2026-12-31'
            ]);

        $this->mockRepository
            ->shouldReceive('validateAddHomeTeamsStartDate')
            ->once()
            ->with(5)
            ->andReturn(null);

        $this->mockRepository
            ->shouldReceive('validateAddTeamBetweenAnyExistingHomeTeamsDuration')
            ->once()
            ->with(5)
            ->andReturn([]);

        $this->mockRepository
            ->shouldReceive('validateAddTeamsEndDate')
            ->once()
            ->with(2, 5, '2026-12-31', '2026-01-01', 3)
            ->andReturn(null);

        $this->mockRepository
            ->shouldReceive('validateAddTeamBetweenAnyExistingAdditioanlTeam')
            ->once()
            ->with(5, 3, '2026-01-01', '2026-12-31')
            ->andReturn([]);

        $response = $this->controller->validateAdditionalTeam($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertArrayHasKey('homeTeamFirstStartDate', $data);
        $this->assertArrayHasKey('homeTeamsDurations', $data);
        $this->assertArrayHasKey('endDateAllocationCheck', $data);
        $this->assertArrayHasKey('additionalTeamPastRecords', $data);
    }

    // ===================== VALIDATE ROTA TESTS =====================

    #[Test]
    public function validate_rota_returns_validation_result()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(1);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);

        $mockResult = ['IntStatus' => 0, 'StrStatus' => 'No rota assigned'];

        $this->mockRepository
            ->shouldReceive('validateSchPersonHomeTeamHaveRota')
            ->once()
            ->with(1, 5)
            ->andReturn($mockResult);

        $this->mockRepository
            ->shouldReceive('checkFutureHomeTeamHasDuties')
            ->once()
            ->with(5, 1)
            ->andReturn(false);

        $response = $this->controller->validateRota($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertArrayHasKey('IntStatus', $data);
        $this->assertArrayHasKey('StrStatus', $data);
        $this->assertArrayHasKey('FutureHomeHasDuties', $data);
        $this->assertEquals(0, $data['FutureHomeHasDuties']);
    }

    #[Test]
    public function validate_rota_returns_error_for_invalid_parameters()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(0);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(0);

        $response = $this->controller->validateRota($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals(1, $data['IntStatus']);
    }

    // ===================== DELETE HOME TEAM TESTS =====================

    #[Test]
    public function delete_home_team_deletes_successfully()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(1);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);
        $mockRequest->shouldReceive('input')
            ->with('DeleteFromRota', 0)
            ->andReturn(1);

        $mockResult = [['intStatus' => 0, 'strStatus' => 'Deleted successfully']];

        $this->mockRepository
            ->shouldReceive('deleteHomeTeam')
            ->once()
            ->with(1, 5, 1, 1)
            ->andReturn($mockResult);

        $response = $this->controller->deleteHomeTeam($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertIsArray($data);
    }

    #[Test]
    public function delete_home_team_returns_error_for_invalid_parameters()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(0);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(0);
        $mockRequest->shouldReceive('input')
            ->with('DeleteFromRota', 0)
            ->andReturn(0);

        $response = $this->controller->deleteHomeTeam($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertIsArray($data);
    }

    // ===================== CONTRACT HISTORY TESTS =====================

    #[Test]
    public function contract_history_returns_history_data()
    {
        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('personId')
            ->andReturn(5);

        $mockHistory = [
            ['id' => 1, 'startDate' => '2026-01-01', 'endDate' => '2026-12-31'],
            ['id' => 2, 'startDate' => '2025-01-01', 'endDate' => '2025-12-31']
        ];

        $this->mockRepository
            ->shouldReceive('getContractHistory')
            ->once()
            ->with(5)
            ->andReturn($mockHistory);

        $response = $this->controller->contractHistory($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals($mockHistory, $data['data']);
    }

    #[Test]
    public function contract_history_returns_empty_array_for_no_person_id()
    {
        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('personId')
            ->andReturn(null);

        $response = $this->controller->contractHistory($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEmpty($data['data']);
    }

    #[Test]
    public function contract_history_handles_exception()
    {
        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('personId')
            ->andReturn(5);

        $this->mockRepository
            ->shouldReceive('getContractHistory')
            ->once()
            ->with(5)
            ->andThrow(new \Exception('Database error'));

        $response = $this->controller->contractHistory($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEmpty($data['data']);
        $this->assertEquals(500, $response->getStatusCode());
    }

    // ===================== CONTRACT HISTORY POPUP TESTS =====================

    #[Test]
    public function contract_history_popup_renders_view_with_decoded_data()
    {
        $historyData = 'Date1 -- Action1 -- Date2 -- Action2';
        $encodedMsg = base64_encode($historyData);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('msg', '')
            ->andReturn($encodedMsg);

        $response = $this->controller->contractHistoryPopup($mockRequest);

        $this->assertIsString($response);
    }

    #[Test]
    public function contract_history_popup_renders_empty_view_for_no_msg()
    {
        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('msg', '')
            ->andReturn('');

        $response = $this->controller->contractHistoryPopup($mockRequest);

        $this->assertIsString($response);
    }

    // ===================== SEARCH TESTS =====================

    #[Test]
    public function search_returns_people_list_for_post_request()
    {
        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('selectedTeamID')
            ->andReturn('5');
        $mockRequest->shouldReceive('input')
            ->with('selectedUserName')
            ->andReturn(null);
        $mockRequest->shouldReceive('input')
            ->with('term')
            ->andReturn('John');
        $mockRequest->shouldReceive('input')
            ->with('excludeNoTeam', 1)
            ->andReturn(1);
        $mockRequest->shouldReceive('isMethod')
            ->with('POST')
            ->andReturn(true);
        $mockRequest->shouldReceive('isMethod')
            ->with('GET')
            ->andReturn(false);

        $mockPeople = [
            ['DisplayName' => 'John Doe', 'id' => 1],
            ['DisplayName' => 'John Smith', 'id' => 2]
        ];

        $this->mockRepository
            ->shouldReceive('getScheduledPeopleUserList')
            ->once()
            ->with('5', 'John', 6, 1)
            ->andReturn($mockPeople);

        $response = $this->controller->search($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals($mockPeople, $data['data']);
    }

    #[Test]
    public function search_returns_display_names_for_autocomplete_get_request()
    {
        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('selectedTeamID')
            ->andReturn(null);
        $mockRequest->shouldReceive('input')
            ->with('selectedUserName')
            ->andReturn(null);
        $mockRequest->shouldReceive('input')
            ->with('term')
            ->andReturn('John');
        $mockRequest->shouldReceive('input')
            ->with('excludeNoTeam', 1)
            ->andReturn(1);
        $mockRequest->shouldReceive('isMethod')
            ->with('GET')
            ->andReturn(true);
        $mockRequest->shouldReceive('isMethod')
            ->with('POST')
            ->andReturn(false);
        $mockRequest->shouldReceive('input')
            ->with('teamid', '')
            ->andReturn('');

        $mockPeople = [
            ['DisplayName' => 'John Doe'],
            ['DisplayName' => 'John Smith']
        ];

        $this->mockRepository
            ->shouldReceive('getScheduledPeopleUserList')
            ->once()
            ->with('', 'John', 6, 1)
            ->andReturn($mockPeople);

        $response = $this->controller->search($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertCount(2, $data);
        $this->assertEquals('John Doe', $data[0]);
    }

    // ===================== PERMISSIONS TESTS =====================

    #[Test]
    public function get_permissions_returns_default_permissions()
    {
        $mockUser = Mockery::mock(User::class)->makePartial();
        
        Auth::shouldReceive('user')
            ->atLeast()
            ->once()
            ->andReturn($mockUser);

        $mockUser->shouldReceive('can')
            ->with('view', \App\Models\User::class)
            ->andReturn(false);

        $mockUser->shouldReceive('can')
            ->with('update', \App\Models\User::class)
            ->andReturn(false);

        $mockUser->shouldReceive('can')
            ->with('create', \App\Models\User::class)
            ->andReturn(false);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);

        $response = $this->controller->getPermissions($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('permissions', $data);
    }

    #[Test]
    public function get_permissions_returns_user_permissions()
    {
        $mockUser = Mockery::mock(User::class)->makePartial();
        
        Auth::shouldReceive('user')
            ->atLeast()
            ->once()
            ->andReturn($mockUser);

        $mockUser->shouldReceive('can')
            ->with('view', \App\Models\User::class)
            ->andReturn(true);

        $mockUser->shouldReceive('can')
            ->with('update', \App\Models\User::class)
            ->andReturn(true);

        $mockUser->shouldReceive('can')
            ->with('create', \App\Models\User::class)
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);

        $response = $this->controller->getPermissions($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals(1, $data['permissions']['canview']);
        $this->assertEquals(1, $data['permissions']['canedit']);
        $this->assertEquals(1, $data['permissions']['cancreate']);
    }

    // ===================== DELETE HOME TEAM KEEP ADDITIONAL TESTS =====================

    #[Test]
    public function delete_home_team_keep_additional_deletes_successfully()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(1);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);
        $mockRequest->shouldReceive('input')
            ->with('DeleteFromRota', 0)
            ->andReturn(1);

        $mockResult = [['intStatus' => 0, 'strStatus' => 'Deleted successfully without affecting additional teams']];

        $this->mockRepository
            ->shouldReceive('deleteHomeTeam')
            ->once()
            ->with(1, 5, 1, 1)
            ->andReturn($mockResult);

        $response = $this->controller->deleteHomeTeamKeepAdditional($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertIsArray($data);
    }

    #[Test]
    public function delete_home_team_keep_additional_returns_error_for_invalid_parameters()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(0);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(0);
        $mockRequest->shouldReceive('input')
            ->with('DeleteFromRota', 0)
            ->andReturn(0);

        $response = $this->controller->deleteHomeTeamKeepAdditional($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertIsArray($data);
        $this->assertEquals(1, $data[0]['intStatus']);
    }

    #[Test]
    public function delete_home_team_keep_additional_handles_exception()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(1);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);
        $mockRequest->shouldReceive('input')
            ->with('DeleteFromRota', 0)
            ->andReturn(1);

        $this->mockRepository
            ->shouldReceive('deleteHomeTeam')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $response = $this->controller->deleteHomeTeamKeepAdditional($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
    }

    // ===================== GET CONFLICTING DUTIES TESTS =====================

    #[Test]
    public function get_conflicting_duties_returns_duties_list()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(1);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);

        $mockDuties = [
            ['id' => 101, 'date' => '2026-01-15', 'team' => 'Team B'],
            ['id' => 102, 'date' => '2026-01-16', 'team' => 'Team B']
        ];

        $this->mockRepository
            ->shouldReceive('getConflictingDutiesForHomeTeamDeletion')
            ->once()
            ->with(1, 5)
            ->andReturn($mockDuties);

        $response = $this->controller->getConflictingDuties($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals($mockDuties, $data['duties']);
        $this->assertEquals(2, $data['count']);
    }

    #[Test]
    public function get_conflicting_duties_returns_empty_list()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(1);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);

        $this->mockRepository
            ->shouldReceive('getConflictingDutiesForHomeTeamDeletion')
            ->once()
            ->with(1, 5)
            ->andReturn([]);

        $response = $this->controller->getConflictingDuties($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(0, $data['count']);
        $this->assertEmpty($data['duties']);
    }

    #[Test]
    public function get_conflicting_duties_returns_error_for_invalid_parameters()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(0);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(0);

        $response = $this->controller->getConflictingDuties($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Invalid parameters', $data['message']);
    }

    #[Test]
    public function get_conflicting_duties_handles_exception()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(1);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);

        $this->mockRepository
            ->shouldReceive('getConflictingDutiesForHomeTeamDeletion')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $response = $this->controller->getConflictingDuties($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
    }

    // ===================== REMOVE CONFLICTING DUTIES TESTS =====================

    #[Test]
    public function remove_conflicting_duties_removes_successfully()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockUser = Mockery::mock(\App\Models\User::class)->makePartial();
        $mockUser->UD_NetLogin = 'testuser';
        $mockUser->id = 1;

        Auth::shouldReceive('user')
            ->andReturn($mockUser);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('dutyIds', [])
            ->andReturn([101, 102]);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);
        $mockRequest->shouldReceive('input')
            ->with('homeTeamId', 0)
            ->andReturn(1);

        // Mock the first DB query to get duties with flags
        $mockDutyData = collect([
            (object)['ASP_AllocationsSPID' => 1, 'ASP_AllocationsDutyID' => 101, 'AD_DutyDate' => '2024-01-01', 'AD_IsNeedCovering' => 1],
            (object)['ASP_AllocationsSPID' => 2, 'ASP_AllocationsDutyID' => 102, 'AD_DutyDate' => '2024-01-02', 'AD_IsNeedCovering' => 0],
        ]);

        $mockBuilder = Mockery::mock();
        $mockBuilder->shouldReceive('join')->once()->andReturnSelf();
        $mockBuilder->shouldReceive('whereIn')->once()->andReturnSelf();
        $mockBuilder->shouldReceive('where')->once()->andReturnSelf();
        $mockBuilder->shouldReceive('select')->once()->andReturnSelf();
        $mockBuilder->shouldReceive('get')->once()->andReturn($mockDutyData);

        DB::shouldReceive('table')
            ->with('AllocationsScheduledPersons as ASP')
            ->andReturn($mockBuilder);

        // Mock the second DB query for duty IDs to cancel
        $mockDutyIds = collect([102]);
        
        $mockBuilder2 = Mockery::mock();
        $mockBuilder2->shouldReceive('whereIn')->once()->andReturnSelf();
        $mockBuilder2->shouldReceive('pluck')->once()->andReturn($mockDutyIds);

        DB::shouldReceive('table')
            ->with('AllocationsScheduledPersons')
            ->andReturn($mockBuilder2);

        // Mock the third DB query to update duty status
        $mockBuilder3 = Mockery::mock();
        $mockBuilder3->shouldReceive('whereIn')->once()->andReturnSelf();
        $mockBuilder3->shouldReceive('update')->once()->andReturn(1);

        DB::shouldReceive('table')
            ->with('AllocationsDuties')
            ->andReturn($mockBuilder3);

        $this->mockRepository
            ->shouldReceive('bulkUnassignDuties')
            ->once()
            ->andReturn(['success' => true, 'removed' => 1, 'errors' => []]);

        $this->mockRepository
            ->shouldReceive('notifySchedulingTeamOfRemovedDuties')
            ->once();

        $this->mockRepository
            ->shouldReceive('handleAdditionalTeamAfterDutyRemoval')
            ->once()
            ->with(5, 1);

        $response = $this->controller->removeConflictingDuties($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Selected duties processed.', $data['message']);
    }

    #[Test]
    public function remove_conflicting_duties_returns_error_for_invalid_parameters()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('dutyIds', [])
            ->andReturn([]);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(0);
        $mockRequest->shouldReceive('input')
            ->with('homeTeamId', 0)
            ->andReturn(0);

        $response = $this->controller->removeConflictingDuties($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Invalid parameters', $data['message']);
    }

    #[Test]
    public function remove_conflicting_duties_handles_exception()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('dutyIds', [])
            ->andReturn([101, 102]);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);
        $mockRequest->shouldReceive('input')
            ->with('homeTeamId', 0)
            ->andReturn(1);

        // Mock the DB query to throw an exception
        DB::shouldReceive('table')
            ->with('AllocationsScheduledPersons as ASP')
            ->andThrow(new \Exception('Database error'));

        $response = $this->controller->removeConflictingDuties($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('Failed to remove duties', $data['message']);
    }

    // ===================== ADDITIONAL AUTHORIZATION TESTS =====================

    #[Test]
    public function update_returns_403_for_insufficient_permissions()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        Auth::shouldReceive('user')
            ->andReturn(Mockery::mock(\App\Models\User::class)->makePartial());

        $personId = 5;
        $mockRequest = Mockery::mock(UpdateScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([
                'SPTeamID' => 1,
                'DisplayName' => 'Jane Doe',
                'SchedulingTeamID' => 5,
                'HomeTeamStartDate' => '2026-01-01',
                'HomeTeamEndDate' => '2026-12-31',
                'HomeTeamSortCode' => 'JD',
                'HomeTeamBackColour' => '#FFFFFF',
                'HomeTeamFontColour' => '#000000',
                'HomeTeamAdminNotes' => '',
                'HomeTeamFWANotes' => '',
                'DisplayFirstName' => 'Jane',
                'DisplayLastName' => 'Doe',
                'IsDefaultBGColour' => 0,
                'IsAdditionalLeave' => 0,
                'AdditionalTeamArray' => '[]'
            ]);

        $this->mockRepository
            ->shouldReceive('getSchedulingTeams')
            ->once()
            ->andReturn([
                ['TeamID' => 10],
                ['TeamID' => 11]
            ]);

        $this->mockRepository
            ->shouldReceive('getSchedulepersondetails')
            ->once()
            ->with($personId)
            ->andReturn([
                ['TeamID' => 5]  // Different team - user not authorized
            ]);

        $response = $this->controller->update($mockRequest, $personId);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function team_history_passes_user_id_to_repository()
    {
        Gate::shouldReceive('authorize')
            ->with('view', 'scheduled-people')
            ->andReturn(true);

        $mockUser = Mockery::mock(\App\Models\User::class)->makePartial();
        $mockUser->id = 1;

        Auth::shouldReceive('user')
            ->andReturn($mockUser);

        $personId = 5;
        $mockHistory = [
            ['date' => '2026-01-01', 'action' => 'Created']
        ];

        $this->mockRepository
            ->shouldReceive('getScheduleTeamHistory')
            ->once()
            ->with($personId, 1)
            ->andReturn($mockHistory);

        $response = $this->controller->teamHistory($personId);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
    }

    #[Test]
    public function validate_rota_includes_future_home_duties_flag()
    {
        Gate::shouldReceive('authorize')
            ->with('update', 'scheduled-people')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('HomeTeamId', 0)
            ->andReturn(1);
        $mockRequest->shouldReceive('input')
            ->with('personid', 0)
            ->andReturn(5);

        $mockResult = ['IntStatus' => 0, 'StrStatus' => 'No rota assigned'];

        $this->mockRepository
            ->shouldReceive('validateSchPersonHomeTeamHaveRota')
            ->once()
            ->with(1, 5)
            ->andReturn($mockResult);

        $this->mockRepository
            ->shouldReceive('checkFutureHomeTeamHasDuties')
            ->once()
            ->with(5, 1)
            ->andReturn(true);

        $response = $this->controller->validateRota($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals(1, $data['FutureHomeHasDuties']);
    }

    #[Test]
    public function store_returns_user_error_status_422()
    {
        Gate::shouldReceive('authorize')
            ->with('create', 'scheduled-people')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->atLeast()
            ->once()
            ->andReturn(1);

        $mockRequest = Mockery::mock(ScheduledPersonRequest::class);
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn([
                'SPTeamID' => 1,
                'DisplayName' => 'John Doe',
                'SchedulingTeamID' => 0,  // Invalid team
                'HomeTeamStartDate' => '2026-01-01',
                'HomeTeamEndDate' => '2026-12-31',
                'HomeTeamSortCode' => 'JD',
                'HomeTeamBackColour' => '#FFFFFF',
                'HomeTeamFontColour' => '#000000',
                'HomeTeamAdminNotes' => '',
                'HomeTeamFWANotes' => '',
                'ActionType' => 'create',
                'ScheduledPersonID' => 0,
                'AdditionalTeamArray' => '[]',
                'DisplayFirstName' => 'John',
                'DisplayLastName' => 'Doe',
                'IsDefaultBGColour' => 0,
                'IsAdditionalLeave' => 0
            ]);

        $this->mockRepository
            ->shouldReceive('createSchedulePerson')
            ->once()
            ->andReturn([
                'strstatusschpeople' => 'usererror',
                'strreturnstringschpeople' => 'Invalid team selection'
            ]);

        $response = $this->controller->store($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
    }

    #[Test]
    public function search_handles_get_request_without_team_id()
    {
        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('input')
            ->with('selectedTeamID')
            ->andReturn(null);
        $mockRequest->shouldReceive('input')
            ->with('teamid', '')
            ->andReturn('');
        $mockRequest->shouldReceive('input')
            ->with('selectedUserName')
            ->andReturn(null);
        $mockRequest->shouldReceive('input')
            ->with('term')
            ->andReturn('John');
        $mockRequest->shouldReceive('input')
            ->with('excludeNoTeam', 1)
            ->andReturn(1);
        $mockRequest->shouldReceive('isMethod')
            ->with('GET')
            ->andReturn(true);
        $mockRequest->shouldReceive('isMethod')
            ->with('POST')
            ->andReturn(false);

        $mockPeople = [
            ['DisplayName' => 'John Doe'],
            ['DisplayName' => 'John Smith']
        ];

        $this->mockRepository
            ->shouldReceive('getScheduledPeopleUserList')
            ->once()
            ->with('', 'John', 6, 1)
            ->andReturn($mockPeople);

        $response = $this->controller->search($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertCount(2, $data);
        $this->assertEquals('John Doe', $data[0]);
        $this->assertEquals('John Smith', $data[1]);
    }
}
