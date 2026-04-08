<?php

namespace Tests\Unit\AllocateUsers;

use Tests\TestCase;
use App\Http\Controllers\Admin\AreaPermissionController;
use App\Models\User;
use App\Models\User\RefRole;
use App\Models\User\RolePermissionStatus;
use App\Repositories\Contracts\Admin\AllocateUserRepositoryInterface;
use App\Http\Requests\Admin\AllocateUser\StoreAreaAllocateUserRequest;
use App\Http\Requests\Admin\AllocateUser\DeleteAreaUserRequest;
use App\Http\Requests\Admin\AllocateUser\UpdateAreaUserRequest;
use App\Http\Requests\Admin\AllocateUser\UpdateAreaAdditionalRoleRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class AreaPermissionControllerTest extends TestCase
{
    protected $controller;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(AllocateUserRepositoryInterface::class);
        $this->controller = new AreaPermissionController($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ===================== AREA PERMISSIONS TAB TESTS =====================

    #[Test]
    public function area_permissions_table_returns_view_with_areas()
    {
        $mockUserModel = Mockery::mock(User::class)->makePartial();
        $mockUserModel->shouldReceive('can')
            ->with('viewAreaPermissionsTab', 'allocate-user')
            ->andReturn(true);
        $mockUserModel->shouldReceive('getAttribute')
            ->with('isSystemAdmin')
            ->andReturn(1);

        $mockAreas = collect([
            ['id' => 1, 'name' => 'Area 1'],
            ['id' => 2, 'name' => 'Area 2']
        ]);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        Gate::shouldReceive('authorize')
            ->with('viewAreaPermissionsTab', 'allocate-user')
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('getArea')
            ->once()
            ->andReturn($mockAreas);

        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.allocate-users.area-permissions-table', [
                'allArea' => $mockAreas,
                'canAllocate' => 1,
                'isSysAdmin' => 1
            ])
            ->andReturn('mocked_view');

        $response = $this->controller->areaPermissionsTable(Mockery::mock(\Illuminate\Http\Request::class));

        $this->assertEquals('mocked_view', $response);
    }

    #[Test]
    public function get_area_users_returns_json_response()
    {
        $areaId = 1;

        $mockUserModel = Mockery::mock(User::class)->makePartial();
        $mockUserModel->shouldReceive('can')
            ->with('modifyAreaUser', ['allocate-user', $areaId])
            ->andReturn(true);
        $mockUserModel->shouldReceive('can')
            ->with('allocateToArea', ['allocate-user', $areaId])
            ->andReturn(true);
        $mockUserModel->shouldReceive('getAttribute')
            ->with('isSystemAdmin')
            ->andReturn(1);

        $mockAreaUsers = collect([
            (object)['UserID' => 1, 'name' => 'User 1'],
            (object)['UserID' => 2, 'name' => 'User 2']
        ]);
        $mockAdditionalRoles = collect([
            (object)['UserID' => 1, 'RoleName' => 'Admin', 'RoleID' => 1]
        ]);
        $mockAvailableRoles = collect([
            ['RoleID' => 1, 'RoleName' => 'Viewer']
        ]);
        $mockAdditionalAreaRoles = collect([
            ['RoleID' => 2, 'RoleName' => 'Editor']
        ]);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        $this->mockRepository
            ->shouldReceive('getAreaUsersByAreaId')
            ->once()
            ->with($areaId)
            ->andReturn($mockAreaUsers);

        $this->mockRepository
            ->shouldReceive('getAreaUsersAdditionalRoles')
            ->once()
            ->with($areaId)
            ->andReturn($mockAdditionalRoles);

        $this->mockRepository
            ->shouldReceive('getAreaRoles')
            ->once()
            ->andReturn($mockAvailableRoles);

        $this->mockRepository
            ->shouldReceive('getAdditionalAreaRoles')
            ->once()
            ->andReturn($mockAdditionalAreaRoles);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);

        $response = $this->controller->getAreaUsers($mockRequest, $areaId);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('additionalRoles', $data);
        $this->assertArrayHasKey('availableAreaRoles', $data);
        $this->assertArrayHasKey('additionalAreaRoles', $data);
        $this->assertArrayHasKey('canModify', $data);
        $this->assertArrayHasKey('canAllocate', $data);
        $this->assertArrayHasKey('isSysAdmin', $data);
    }

    #[Test]
    public function get_area_user_permission_history_returns_view()
    {
        $areaId = 1;
        $userRoleId = 2;
        $mockHistory = [
            ['id' => 1, 'action' => 'Created'],
            ['id' => 2, 'action' => 'Updated']
        ];

        $this->mockRepository
            ->shouldReceive('getAreaUserPermissionHistory')
            ->once()
            ->with($areaId, $userRoleId)
            ->andReturn($mockHistory);

        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.allocate-users.area-user-history', ['history' => $mockHistory])
            ->andReturn('mocked_history_view');

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);

        $response = $this->controller->getAreaUserPermissionHistory($mockRequest, $areaId, $userRoleId);

        $this->assertEquals('mocked_history_view', $response);
    }

    #[Test]
    public function add_area_allocate_user_form_returns_view_with_data()
    {
        $mockUsers = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2']
        ];
        $mockRoles = [
            ['RoleID' => 1, 'RoleName' => 'Admin']
        ];
        $areaId = 5;

        Gate::shouldReceive('authorize')
            ->with('allocateToArea', ['allocate-user', $areaId])
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('getUsersForAreaAllocation')
            ->once()
            ->andReturn($mockUsers);

        $this->mockRepository
            ->shouldReceive('getAvailableRoles')
            ->once()
            ->andReturn($mockRoles);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('get')
            ->with('area_id')
            ->andReturn($areaId);

        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.allocate-users.form.area-allocate-user-form', [
                'users' => $mockUsers,
                'roles' => $mockRoles,
                'areaId' => $areaId
            ])
            ->andReturn('mocked_form_view');

        $response = $this->controller->addAreaAllocateUserForm($mockRequest);

        $this->assertEquals('mocked_form_view', $response);
    }

    #[Test]
    public function create_area_allocate_user_returns_success_json()
    {
        $mockUserModel = Mockery::mock(User::class)->makePartial();

        $mockRequest = Mockery::mock(StoreAreaAllocateUserRequest::class);
        $mockRequest->shouldReceive('__get')
            ->with('user_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('__get')
            ->with('area_id')
            ->andReturn(2);
        $mockRequest->shouldReceive('__get')
            ->with('role_id')
            ->andReturn(3);
        $mockRequest->shouldReceive('all')
            ->andReturn(['user_id' => 1, 'area_id' => 2, 'role_id' => 3]);

        $mockResult = [
            'status' => 'success',
            'message' => 'User allocated to area successfully.'
        ];

        $mockAreaUsers = collect([
            (object)['UserID' => 1, 'name' => 'User 1']
        ]);
        $mockAdditionalRoles = collect([]);

        Gate::shouldReceive('authorize')
            ->with('allocateToArea', ['allocate-user', 2])
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        $this->mockRepository
            ->shouldReceive('allocateUserToArea')
            ->once()
            ->with(1, 2, 3, $mockUserModel)
            ->andReturn($mockResult);

        $this->mockRepository
            ->shouldReceive('getAreaUsersByAreaId')
            ->once()
            ->with(2)
            ->andReturn($mockAreaUsers);

        $this->mockRepository
            ->shouldReceive('getAreaUsersAdditionalRoles')
            ->once()
            ->with(2)
            ->andReturn($mockAdditionalRoles);

        $response = $this->controller->createAreaAllocateUser($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('User allocated to area successfully.', $data['message']);
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('additionalRoles', $data);
    }

    #[Test]
    public function create_area_allocate_user_returns_error_json()
    {
        $mockUserModel = Mockery::mock(User::class)->makePartial();

        $mockRequest = Mockery::mock(StoreAreaAllocateUserRequest::class);
        $mockRequest->shouldReceive('__get')
            ->with('user_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('__get')
            ->with('area_id')
            ->andReturn(2);
        $mockRequest->shouldReceive('__get')
            ->with('role_id')
            ->andReturn(3);
        $mockRequest->shouldReceive('all')
            ->andReturn(['user_id' => 1, 'area_id' => 2, 'role_id' => 3]);

        $mockResult = [
            'status' => 'error',
            'message' => 'Failed to allocate user.'
        ];

        Gate::shouldReceive('authorize')
            ->with('allocateToArea', ['allocate-user', 2])
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        $this->mockRepository
            ->shouldReceive('allocateUserToArea')
            ->once()
            ->with(1, 2, 3, $mockUserModel)
            ->andReturn($mockResult);

        $response = $this->controller->createAreaAllocateUser($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('error', $data['status']);
    }

    #[Test]
    public function get_user_details_for_area_allocation_returns_json()
    {
        $mockUserModel = Mockery::mock(User::class)->makePartial();
        $mockUserModel->shouldReceive('userSystemRole')
            ->andReturn(true);

        $mockRequest = Mockery::mock(\Illuminate\Http\Request::class);
        $mockRequest->shouldReceive('get')
            ->with('area_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('get')
            ->with('user_id')
            ->andReturn(2);

        $mockData = ['user' => 'details'];

        Gate::shouldReceive('authorize')
            ->with('allocateToArea', ['allocate-user', 1])
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        $this->mockRepository
            ->shouldReceive('getUserDetailsForAreaAllocationHtml')
            ->once()
            ->with(2, 1, true)
            ->andReturn($mockData);

        $response = $this->controller->getUserDetailsForAreaAllocation($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals($mockData, $data);
    }

    #[Test]
    public function remove_area_user_returns_success_json()
    {
        $mockRequest = Mockery::mock(DeleteAreaUserRequest::class);
        $mockRequest->shouldReceive('__get')
            ->with('user_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('__get')
            ->with('area_id')
            ->andReturn(2);
        $mockRequest->shouldReceive('all')
            ->andReturn(['user_id' => 1, 'area_id' => 2]);

        $mockResult = [
            'status' => 'success',
            'message' => 'User removed from area successfully.'
        ];

        $mockAreaUsers = collect([]);
        $mockAdditionalRoles = collect([]);

        Gate::shouldReceive('authorize')
            ->with('modifyAreaUser', ['allocate-user', 2])
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('removeUserFromArea')
            ->once()
            ->with(1, 2)
            ->andReturn($mockResult);

        $this->mockRepository
            ->shouldReceive('getAreaUsersByAreaId')
            ->once()
            ->with(2)
            ->andReturn($mockAreaUsers);

        $this->mockRepository
            ->shouldReceive('getAreaUsersAdditionalRoles')
            ->once()
            ->with(2)
            ->andReturn($mockAdditionalRoles);

        $response = $this->controller->removeAreaUser($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('additionalRoles', $data);
    }

    #[Test]
    public function remove_area_user_returns_error_json()
    {
        $mockRequest = Mockery::mock(DeleteAreaUserRequest::class);
        $mockRequest->shouldReceive('__get')
            ->with('user_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('__get')
            ->with('area_id')
            ->andReturn(2);
        $mockRequest->shouldReceive('all')
            ->andReturn(['user_id' => 1, 'area_id' => 2]);

        $mockResult = [
            'status' => 'error',
            'message' => 'Failed to remove user.'
        ];

        Gate::shouldReceive('authorize')
            ->with('modifyAreaUser', ['allocate-user', 2])
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('removeUserFromArea')
            ->once()
            ->with(1, 2)
            ->andReturn($mockResult);

        $response = $this->controller->removeAreaUser($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('error', $data['status']);
    }

    #[Test]
    public function update_area_user_role_returns_success_json()
    {
        $mockUserModel = Mockery::mock(User::class)->makePartial();

        $mockRequest = Mockery::mock(UpdateAreaUserRequest::class);
        $mockRequest->shouldReceive('__get')
            ->with('user_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('__get')
            ->with('area_id')
            ->andReturn(2);
        $mockRequest->shouldReceive('__get')
            ->with('role_id')
            ->andReturn(3);
        $mockRequest->shouldReceive('all')
            ->andReturn(['user_id' => 1, 'area_id' => 2, 'role_id' => 3]);

        $mockResult = [
            'status' => 'success',
            'message' => 'User role updated successfully.'
        ];

        $mockAreaUsers = collect([
            (object)['UserID' => 1, 'RoleID' => 3]
        ]);
        $mockAdditionalRoles = collect([]);

        Gate::shouldReceive('authorize')
            ->with('canUpdateAreaUserRole', 'allocate-user')
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        $this->mockRepository
            ->shouldReceive('updateUserRole')
            ->once()
            ->with(1, 2, 3, $mockUserModel)
            ->andReturn($mockResult);

        $this->mockRepository
            ->shouldReceive('getAreaUsersByAreaId')
            ->once()
            ->with(2)
            ->andReturn($mockAreaUsers);

        $this->mockRepository
            ->shouldReceive('getAreaUsersAdditionalRoles')
            ->once()
            ->with(2)
            ->andReturn($mockAdditionalRoles);

        $response = $this->controller->updateAreaUserRole($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('success', $data['status']);
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('additionalRoles', $data);
    }

    #[Test]
    public function update_area_user_role_returns_error_json()
    {
        $mockUserModel = Mockery::mock(User::class)->makePartial();

        $mockRequest = Mockery::mock(UpdateAreaUserRequest::class);
        $mockRequest->shouldReceive('__get')
            ->with('user_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('__get')
            ->with('area_id')
            ->andReturn(2);
        $mockRequest->shouldReceive('__get')
            ->with('role_id')
            ->andReturn(3);
        $mockRequest->shouldReceive('all')
            ->andReturn(['user_id' => 1, 'area_id' => 2, 'role_id' => 3]);

        $mockResult = [
            'status' => 'error',
            'message' => 'Failed to update user role.'
        ];

        Gate::shouldReceive('authorize')
            ->with('canUpdateAreaUserRole', 'allocate-user')
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        $this->mockRepository
            ->shouldReceive('updateUserRole')
            ->once()
            ->with(1, 2, 3, $mockUserModel)
            ->andReturn($mockResult);

        $response = $this->controller->updateAreaUserRole($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('error', $data['status']);
    }

    #[Test]
    public function toggle_area_additional_role_returns_success_json()
    {
        $mockUserModel = Mockery::mock(User::class)->makePartial();

        $mockRequest = Mockery::mock(UpdateAreaAdditionalRoleRequest::class);
        $mockRequest->shouldReceive('__get')
            ->with('user_role_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('__get')
            ->with('role_id')
            ->andReturn(2);
        $mockRequest->shouldReceive('__get')
            ->with('user_id')
            ->andReturn(3);
        $mockRequest->shouldReceive('__get')
            ->with('area_id')
            ->andReturn(4);
        $mockRequest->shouldReceive('__get')
            ->with('action')
            ->andReturn('add');
        $mockRequest->shouldReceive('input')
            ->with('permission', '')
            ->andReturn('');
        $mockRequest->shouldReceive('all')
            ->andReturn(['user_role_id' => 1, 'role_id' => 2, 'user_id' => 3, 'area_id' => 4, 'action' => 'add', 'permission' => '']);

        $mockResult = [
            'status' => 'success',
            'message' => 'Additional role added successfully.'
        ];

        Gate::shouldReceive('authorize')
            ->with('canToggleAreaRole', 'allocate-user')
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        $this->mockRepository
            ->shouldReceive('toggleAreaAdditionalRole')
            ->once()
            ->with(1, 2, 3, 4, 'add', $mockUserModel)
            ->andReturn($mockResult);

        $response = $this->controller->toggleAreaAdditionalRole($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('success', $data['status']);
    }

    #[Test]
    public function toggle_area_additional_role_returns_error_json()
    {
        $mockUserModel = Mockery::mock(User::class)->makePartial();

        $mockRequest = Mockery::mock(UpdateAreaAdditionalRoleRequest::class);
        $mockRequest->shouldReceive('__get')
            ->with('user_role_id')
            ->andReturn(1);
        $mockRequest->shouldReceive('__get')
            ->with('role_id')
            ->andReturn(2);
        $mockRequest->shouldReceive('__get')
            ->with('user_id')
            ->andReturn(3);
        $mockRequest->shouldReceive('__get')
            ->with('area_id')
            ->andReturn(4);
        $mockRequest->shouldReceive('__get')
            ->with('action')
            ->andReturn('remove');
        $mockRequest->shouldReceive('input')
            ->with('permission', '')
            ->andReturn('');
        $mockRequest->shouldReceive('all')
            ->andReturn(['user_role_id' => 1, 'role_id' => 2, 'user_id' => 3, 'area_id' => 4, 'action' => 'remove', 'permission' => '']);

        $mockResult = [
            'status' => 'error',
            'message' => 'Failed to toggle additional role.'
        ];

        Gate::shouldReceive('authorize')
            ->with('canToggleAreaRole', 'allocate-user')
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->andReturn($mockUserModel);

        $this->mockRepository
            ->shouldReceive('toggleAreaAdditionalRole')
            ->once()
            ->with(1, 2, 3, 4, 'remove', $mockUserModel)
            ->andReturn($mockResult);

        $response = $this->controller->toggleAreaAdditionalRole($mockRequest);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertEquals('error', $data['status']);
    }

    #[Test]
    public function permission_descriptions_table_returns_view_with_permission_data()
    {
        $mockMainRoles = collect([
            (object)['RoleID' => 1, 'RoleName' => RefRole::SCHEDULING_TEAM_VIEWER, 'RoleDescription' => 'Can view team allocations'],
            (object)['RoleID' => 2, 'RoleName' => RefRole::SCHEDULED_PERSON, 'RoleDescription' => 'Can view own allocations']
        ]);

        $mockAdditionalRoles = collect([
            (object)['RoleID' => 3, 'RoleName' => RefRole::EDIT_ALL_ALLOCATIONS, 'RoleDescription' => 'Can edit all allocations'],
            (object)['RoleID' => 4, 'RoleName' => RefRole::MANAGER, 'RoleDescription' => 'Management permissions']
        ]);

        $mockPermissionMatrix = [
            1 => [3 => RolePermissionStatus::MANDATORY, 4 => RolePermissionStatus::OPTIONAL],
            2 => [3 => RolePermissionStatus::NA, 4 => RolePermissionStatus::CONDITIONAL]
        ];

        $mockPermissionData = [
            'mainRoles' => $mockMainRoles,
            'additionalRoles' => $mockAdditionalRoles,
            'permissionMatrix' => $mockPermissionMatrix
        ];

        Gate::shouldReceive('authorize')
            ->with('createAllocateUser', 'allocate-user')
            ->andReturn(true);

        $this->mockRepository
            ->shouldReceive('getPermissionDescriptions')
            ->once()
            ->andReturn($mockPermissionData);

        View::shouldReceive('make')
            ->once()
            ->with('pages.admin.allocate-users.permission-descriptions-table', [
                'mainRoles' => $mockMainRoles,
                'additionalRoles' => $mockAdditionalRoles,
                'permissionMatrix' => $mockPermissionMatrix
            ])
            ->andReturn('mocked_permission_descriptions_view');

        $response = $this->controller->permissionDescriptionsTable(Mockery::mock(\Illuminate\Http\Request::class));

        $this->assertEquals('mocked_permission_descriptions_view', $response);
    }

    #[Test]
    public function permission_descriptions_table_requires_authorization()
    {
        Gate::shouldReceive('authorize')
            ->with('createAllocateUser', 'allocate-user')
            ->andThrow(new \Illuminate\Auth\Access\AuthorizationException('Unauthorized'));

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->expectExceptionMessage('Unauthorized');

        $this->controller->permissionDescriptionsTable(Mockery::mock(\Illuminate\Http\Request::class));
    }
}