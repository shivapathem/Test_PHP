<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AllocateUser\StoreAreaAllocateUserRequest;
use App\Http\Requests\Admin\AllocateUser\DeleteAreaUserRequest;
use App\Http\Requests\Admin\AllocateUser\UpdateAreaUserRequest;
use App\Http\Requests\Admin\AllocateUser\UpdateAreaAdditionalRoleRequest;
use App\Repositories\Contracts\Admin\AllocateUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AreaPermissionController extends Controller
{
    /**
     * @var AllocateUserRepositoryInterface
     */
    protected $allocateUserRepositoryInterface;

    /**
     * AreaPermissionController constructor.
     *
     * @param AllocateUserRepositoryInterface $allocateUserRepositoryInterface Allocate user repo
     */
    public function __construct(AllocateUserRepositoryInterface $allocateUserRepositoryInterface)
    {
        $this->allocateUserRepositoryInterface = $allocateUserRepositoryInterface;
    }

    /**
     * Area permissions table.
     *
     * @return View
     */
    public function areaPermissionsTable(Request $request)
    {
        $this->authorize('viewAreaPermissionsTab', 'allocate-user');

        $user = Auth::user();
         // canAllocate = SysAdmin OR Area Admin of any area (controls Add button visibility)
        $canAllocate = $user->can('viewAreaPermissionsTab', 'allocate-user') ? 1 : 0;

        return View::make(
            "pages.admin.allocate-users.area-permissions-table",
            [
                'allArea'     => $this->allocateUserRepositoryInterface->getArea(),
                'canAllocate' => $canAllocate,
                'isSysAdmin'  => $user->isSystemAdmin == 1 ? 1 : 0,
            ]
        );
    }

    /**
     * Get area users by area id.
     *
     * @param Request $request
     * @param int $areaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreaUsers(Request $request, int $areaId)
    {
        $user = Auth::user();

        $areaUsers = $this->allocateUserRepositoryInterface->getAreaUsersByAreaId($areaId);
        $additionalRoles = $this->allocateUserRepositoryInterface->getAreaUsersAdditionalRoles($areaId);
        $availableAreaRoles = $this->allocateUserRepositoryInterface->getAreaRoles();
        $additionalAreaRoles = $this->allocateUserRepositoryInterface->getAdditionalAreaRoles();

        $additionalRolesByUser = [];
        foreach ($additionalRoles as $role) {
            $additionalRolesByUser[$role->UserID][$role->RoleName] = $role->RoleID;
        }
        // Per-area permission flags ( $haveAccess / $disableReportClass logic)
        $canModify   = $user->can('modifyAreaUser', ['allocate-user', $areaId]) ? 1 : 0;
        $canAllocate = $user->can('allocateToArea', ['allocate-user', $areaId]) ? 1 : 0;

        return response()->json([
            'users'              => $areaUsers,
            'additionalRoles'    => $additionalRolesByUser,
            'availableAreaRoles' => $availableAreaRoles,
            'additionalAreaRoles' => $additionalAreaRoles,
            'canModify'          => $canModify,
            'canAllocate'        => $canAllocate,
            'isSysAdmin'         => $user->isSystemAdmin == 1 ? 1 : 0,
        ]);
    }

    /**
     * Get area user permission history.
     *
     * @param Request $request
     * @param int $areaId
     * @param int $userRoleId
     * @return View
     */
    public function getAreaUserPermissionHistory(Request $request, int $areaId, int $userRoleId)
    {
        $history = $this->allocateUserRepositoryInterface->getAreaUserPermissionHistory($areaId, $userRoleId);
        return View::make("pages.admin.allocate-users.area-user-history", ['history' => $history]);
    }

    /**
     * Add area allocate user form.
     *
     * @return View
     */
    public function addAreaAllocateUserForm(Request $request)
    {
        $areaId = (int) $request->get('area_id');
        $this->authorize('allocateToArea', ['allocate-user', $areaId]);
        $users = $this->allocateUserRepositoryInterface->getUsersForAreaAllocation();
        $roles = $this->allocateUserRepositoryInterface->getAvailableRoles();

        return View::make(
            "pages.admin.allocate-users.form.area-allocate-user-form",
            [
                'users'  => $users,
                'roles'  => $roles,
                'areaId' => $areaId
            ]
        );
    }

    /**
     * Create area allocate user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAreaAllocateUser(StoreAreaAllocateUserRequest $request)
    {
        $this->authorize('allocateToArea', ['allocate-user', (int) $request->area_id]);

        $result = $this->allocateUserRepositoryInterface->allocateUserToArea(
            $request->user_id,
            $request->area_id,
            $request->role_id,
            Auth::user()
        );
        
        // If allocation successful, return refreshed area users and additional roles
        if (isset($result['status']) && $result['status'] === 'success') {
            $areaUsers = $this->allocateUserRepositoryInterface->getAreaUsersByAreaId($request->area_id);
            $additionalRoles = $this->allocateUserRepositoryInterface->getAreaUsersAdditionalRoles($request->area_id);

            $additionalRolesByUser = [];
            foreach ($additionalRoles as $role) {
                $additionalRolesByUser[$role->UserID][$role->RoleName] = $role->RoleID;
            }

            return response()->json([
                'status'          => 'success',
                'message'         => $result['message'] ?? 'User allocated to area successfully.',
                'users'           => $areaUsers,
                'additionalRoles' => $additionalRolesByUser
            ]);
        }

        return response()->json($result);
    }

    /**
     * Get user details for area allocation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserDetailsForAreaAllocation(Request $request)
    {
        $areaId = (int) $request->get('area_id');
        $this->authorize('allocateToArea', ['allocate-user', $areaId]);

        $userId     = $request->get('user_id');

        // Determine current user's sysadmin status
        $isSysAdmin = Auth::user() && method_exists(Auth::user(), 'userSystemRole') && Auth::user()->userSystemRole() ? true : false;
        /*
        * Get JSON data from repository
        */
        $data = $this->allocateUserRepositoryInterface->getUserDetailsForAreaAllocationHtml($userId, $areaId, $isSysAdmin);

        return response()->json($data);
    }

    /**
     * Remove area user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeAreaUser(DeleteAreaUserRequest $request)
    {
        $this->authorize('modifyAreaUser', ['allocate-user', (int) $request->area_id]);

        $result = $this->allocateUserRepositoryInterface->removeUserFromArea(
            $request->user_id,
            $request->area_id
        );

        // If removal successful, return refreshed area users and additional roles
        if (isset($result['status']) && $result['status'] === 'success') {
            $areaUsers = $this->allocateUserRepositoryInterface->getAreaUsersByAreaId($request->area_id);
            $additionalRoles = $this->allocateUserRepositoryInterface->getAreaUsersAdditionalRoles($request->area_id);

            $additionalRolesByUser = [];
            foreach ($additionalRoles as $role) {
                $additionalRolesByUser[$role->UserID][$role->RoleName] = $role->RoleID;
            }

            return response()->json([
                'status'          => 'success',
                'message'         => $result['message'] ?? 'User removed from area successfully.',
                'users'           => $areaUsers,
                'additionalRoles' => $additionalRolesByUser
            ]);
        }

        return response()->json($result);
    }

    /**
     * Update area user role.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAreaUserRole(UpdateAreaUserRequest $request)
    {
        $this->authorize('canUpdateAreaUserRole', 'allocate-user');

        $result = $this->allocateUserRepositoryInterface->updateUserRole(
            $request->user_id,
            $request->area_id,
            $request->role_id,
            Auth::user()
        );

        if (isset($result['status']) && $result['status'] === 'success') {
            $areaUsers = $this->allocateUserRepositoryInterface->getAreaUsersByAreaId($request->area_id);
            $additionalRoles = $this->allocateUserRepositoryInterface->getAreaUsersAdditionalRoles($request->area_id);

            $additionalRolesByUser = [];
            foreach ($additionalRoles as $role) {
                $additionalRolesByUser[$role->UserID][$role->RoleName] = $role->RoleID;
            }

            return response()->json([
                'status'          => 'success',
                'message'         => $result['message'] ?? 'User role updated successfully.',
                'users'           => $areaUsers,
                'additionalRoles' => $additionalRolesByUser
            ]);
        }

        return response()->json($result);
    }

    /**
     * Toggle additional role for an area user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAreaAdditionalRole(UpdateAreaAdditionalRoleRequest $request)
    {
        $areaId     = (int) $request->area_id;
        $permission = $request->input('permission', '');

        // Both facility and report toggles require at least SysAdmin or Area Admin of any area
        $this->authorize('canToggleAreaRole', 'allocate-user');

        // Facility Administrator and Area Reports both require being admin of THIS specific area
        if ($permission === 'report' || $permission === 'facility') {
            $this->authorize('allocateToArea', ['allocate-user', $areaId]);
        }

        $result = $this->allocateUserRepositoryInterface->toggleAreaAdditionalRole(
            $request->user_role_id,
            $request->role_id,
            $request->user_id,
            $request->area_id,
            $request->action,
            Auth::user()
        );

        return response()->json($result);
    }

    /**
     * Permission descriptions table.
     *
     * @return View
     */
    public function permissionDescriptionsTable(Request $request)
    {
        $this->authorize('createAllocateUser', 'allocate-user');

        $permissionData = $this->allocateUserRepositoryInterface->getPermissionDescriptions();

        return View::make("pages.admin.allocate-users.permission-descriptions-table", [
            'mainRoles'        => $permissionData['mainRoles'],
            'additionalRoles'  => $permissionData['additionalRoles'],
            'permissionMatrix' => $permissionData['permissionMatrix']
        ]);
    }
}
