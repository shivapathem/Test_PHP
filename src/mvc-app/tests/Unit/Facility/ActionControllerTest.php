<?php

namespace Tests\Unit\Facility;

use Tests\TestCase;
use App\Http\Controllers\Facility\ActionController;
use App\Models\Facility\Action;
use App\Models\User;
use App\Repositories\Contracts\ActionRepositoryInterface;
use App\Http\Requests\StoreActionRequest;
use App\Http\Requests\UpdateActionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ActionControllerTest extends TestCase
{
    protected $controller;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ActionRepositoryInterface::class);
        $this->controller = Mockery::mock(ActionController::class, [$this->mockRepository])->makePartial();
        $this->controller->shouldReceive('authorize')->andReturn(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_instantiate_action_controller()
    {
        $this->assertNotNull($this->controller);
    }

    #[Test]
    public function index_method_returns_view_with_actions():void
    {
        // Arrange
        $mockActions = collect([
            ['id' => 1, 'name' => 'Test Action 1'],
            ['id' => 2, 'name' => 'Test Action 2']
        ]);

        $this->mockRepository
            ->shouldReceive('getAllActions')
            ->once()
            ->andReturn($mockActions);

        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.actions.actionList', ['actions' => $mockActions])
            ->andReturn('mocked_view');

        // Act
        $response = $this->controller->index();

        // Assert
        $this->assertEquals('mocked_view', $response);
    }

    #[Test]
    public function create_method_returns_create_form_view()
    {
        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.actions.actionForm')
            ->andReturn('mocked_create_form');

        $response = $this->controller->create();

        $this->assertEquals('mocked_create_form', $response);
    }

    #[Test]
    public function store_method_saves_action_and_returns_json_response()
    {
        // Arrange
        $mockRequest = Mockery::mock(StoreActionRequest::class);
        $validatedData = [
            'facility_id' => 1,
            'action_type' => 'maintenance',
            'description' => 'Test maintenance action',
            'scheduled_date' => '2024-01-15',
            'priority' => 'high'
        ];

        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn($validatedData);

        $mockUser = Mockery::mock(User::class);
        Auth::shouldReceive('user')->andReturn($mockUser);

        $this->mockRepository
            ->shouldReceive('saveAction')
            ->once()
            ->with($validatedData, $mockUser);

        // Act
        $response = $this->controller->store($mockRequest);

        // Assert
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(['Record added successfully'], $response->getData(true));
    }

    #[Test]
    public function show_method_returns_view_with_action()
    {
        // Arrange
        $mockAction = Mockery::mock(Action::class);

        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.actions.actionView', ['action' => $mockAction])
            ->andReturn('mocked_view');

        // Act
        $response = $this->controller->show($mockAction);

        // Assert
        $this->assertEquals('mocked_view', $response);
    }

    #[Test]
    public function edit_method_returns_edit_form_with_action()
    {
        // Arrange
        $mockAction = Mockery::mock(Action::class);

        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.actions.actionForm', ['action' => $mockAction])
            ->andReturn('mocked_edit_form');

        // Act
        $response = $this->controller->edit($mockAction);

        // Assert
        $this->assertEquals('mocked_edit_form', $response);
    }

    #[Test]
    public function update_method_updates_action_and_returns_json_response()
    {
        // Arrange
        $mockRequest = Mockery::mock(UpdateActionRequest::class);
        $mockAction = Mockery::mock(Action::class);
        $validatedData = [
            'action_type' => 'inspection',
            'description' => 'Updated description',
            'status' => 'completed'
        ];

        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn($validatedData);

        $mockUser = Mockery::mock(User::class);
        Auth::shouldReceive('user')->andReturn($mockUser);

        $this->mockRepository
            ->shouldReceive('updateAction')
            ->once()
            ->with($mockAction, $validatedData, $mockUser);

        // Act
        $response = $this->controller->update($mockRequest, $mockAction);

        // Assert
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(['Record updated successfully'], $response->getData(true));
    }

    #[Test]
    public function delete_method_returns_delete_view()
    {
        // Arrange
        $mockAction = Mockery::mock(Action::class);

        $this->mockRepository
            ->shouldReceive('getFutureBookingsCount')
            ->once()
            ->with($mockAction)
            ->andReturn(0);

        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.actions.actionDelete', [
                'action' => $mockAction,
                'futureBookingsCount' => 0
            ])
            ->andReturn('mocked_delete_view');

        // Act
        $response = $this->controller->delete($mockAction);

        // Assert
        $this->assertEquals('mocked_delete_view', $response);
    }

   #[Test]
    public function destroy_method_deletes_action_and_returns_json_response()
    {
        // Arrange
        $mockAction = Mockery::mock(Action::class);
        $mockUser = Mockery::mock(User::class);

        Auth::shouldReceive('user')->andReturn($mockUser);

        $this->mockRepository
            ->shouldReceive('removeActionFromFutureBookings')
            ->once()
            ->with($mockAction);

        $this->mockRepository
            ->shouldReceive('destroyAction')
            ->once()
            ->with($mockAction, $mockUser);

        // Act
        $response = $this->controller->destroy($mockAction);

        // Assert
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(['Record deleted successfully'], $response->getData(true));
    }


    #[Test]
    public function get_actions_list_returns_json_response()
    {
        // Arrange
        $mockActionsList = [
            ['id' => 1, 'name' => 'Test Action 1'],
            ['id' => 2, 'name' => 'Test Action 2']
        ];

        $this->mockRepository
            ->shouldReceive('getActionsList')
            ->once()
            ->andReturn($mockActionsList);

        // Act
        $response = $this->controller->getActionsList();

        // Assert
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals($mockActionsList, $response->getData(true));
    }
}
