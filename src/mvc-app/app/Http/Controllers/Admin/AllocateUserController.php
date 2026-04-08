<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AllocateUser\StoreAllocateUserRequest;
use App\Models\Scheduling\SchedulingTeam;
use App\Models\User;
use App\Models\User\RefRole;
use App\Repositories\Contracts\Admin\AllocateUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AllocateUserController extends Controller
{
    /**
     * @var AllocateUserRepositoryInterface
     */
    protected $allocateUserRepositoryInterface;

    /**
     * AllocateUserController constructor.
     *
     * @param AllocateUserRepositoryInterface $allocateUserRepositoryInterface Allocate user repo
     */
    public function __construct(AllocateUserRepositoryInterface $allocateUserRepositoryInterface)
    {
        $this->allocateUserRepositoryInterface = $allocateUserRepositoryInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $showAreaPermissionsTab = $user->can('viewAreaPermissionsTab', 'allocate-user') ? 1 : 0;

        return View::make(
            "pages.admin.allocate-users.allocate-user-index",
            ['showAreaPermissionsTab' => $showAreaPermissionsTab]
        );
    }

    /**
     * Allocate user table.
     *
     * @return View
     */
    public function allocateUserTable(Request $request)
    {
        return View::make(
            "pages.admin.allocate-users.allocate-users-table"
        );
    }

    /**
     * Get allocate users data for DataTable.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllocateUsersData(Request $request)
    {
        $allocateUsers = $this->allocateUserRepositoryInterface->getAllocateUsers(Auth::user());

        $data = [];
        foreach ($allocateUsers as $user) {
            $data[] = [
                $user->UD_DisplayLastName,
                $user->UD_NetLogin,
                $user->UD_InternalEmail,
                $user->UD_EmpNumber,
                $user->UD_StaffNumber,
                $user->schedulingTeamName,
                '',
                $user->UD_UserID
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Add Allocate user form.
     *
     * @return View
     */
    public function addAllocateUserForm(Request $request)
    {
        $this->authorize('createAllocateUser', 'allocate-user');
        return View::make(
            "pages.admin.allocate-users.form.allocate-user-form"
        );
    }

    /**
     * Create Allocate user form.
     *
     * @return View
     */
    public function createAllocateUser(StoreAllocateUserRequest $request)
    {
        $this->authorize('createAllocateUser', 'allocate-user');
        return response()->json($this->allocateUserRepositoryInterface->insertAllocateUsers($request->net_login, Auth::user()));
    }

    /**
     * Get staff details autocomplete list.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStaffDetailsAutocompleteList(Request $request)
    {
        $staffList = $this->allocateUserRepositoryInterface->getStaffDetailsAutocompleteList(
            $request->get('termKey'),
            $request->get('term')
        );
        return response()->json($staffList);
    }

    /**
     * Allocate user table.
     * @param User $user
     *
     * @return View
     */
    public function getUserInfo(User $user)
    {
        $user->getUserRoleDetail();
        return View::make(
            "pages.admin.allocate-users.user-info",
            [
                'user' => $user,
                'roleLists' => RefRole::where('isActive', 1)
                    ->orderBy('isSequence')
                    ->orderBy('isAdditional')
                    ->orderBy('RoleID')
                    ->get()
            ]
        );
    }
    /**
     * Get add non-scheduled staff form
     *
     * @return View
     */
    public function addNonScheduledStaffForm(Request $request)
    {
        $teamId = $request->get('teamid');
        return View::make(
            "pages.admin.allocate-users.form.non-scheduled-staff-form",
            ['teamId' => $teamId]
        );
    }

    /**
     * Set default team
     *
     * @return JsonResponse
     */
    public function setDefaultTeam(Request $request)
    {
        return response()->json($this->allocateUserRepositoryInterface->setDefaultTeam($request->all(), Auth::user()));
    }

    /**
     * Get staff history
     *
     * @param Request $request
     * @return View
     */
    public function getStaffHistory(Request $request)
    {
        $teamId = $request->input('teamId');
        $userId = $request->input('userId');
        $history = $this->allocateUserRepositoryInterface->getStaffHistory($teamId, $userId);
        return View::make('pages.admin.allocate-users.staff-history', ['history' => $history]);
    }

    /**
     * Search staff team data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchStaffTeam(Request $request)
    {
        $selectedTeamID = $request->input('selectedTeamID');
        $usertype = $request->input('usertype');
        $divisionid = $request->input('divisionid');
        $user = Auth::user();
        $data = $this->allocateUserRepositoryInterface->getStaffTeamData(
            SchedulingTeam::find($selectedTeamID),
            $usertype,
            $divisionid,
            $user
        );
        return response()->json(['data' => $data]);
    }

    /**
     * Set users permissions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setUsersPermissions(Request $request)
    {
        return response()->json($this->allocateUserRepositoryInterface->setUsersPermissions($request->all(), Auth::user()));
    }

    /**
     * Remove staff
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeStaff(Request $request)
    {
        return response()->json($this->allocateUserRepositoryInterface->removeStaff($request->all()));
    }

    /**
     * Add non scheduled staff
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNonScheduledStaff(Request $request)
    {
        return response()->json($this->allocateUserRepositoryInterface->addNonScheduledStaff($request->all(), Auth::user()));
    }

    /**
     * Get staff view based on tab type
     *
     * @param Request $request
     * @return View|\Illuminate\Http\JsonResponse
     */
    public function getStaffView(Request $request)
    {
        $tabType = $request->input('tabType');
        switch ($tabType) {
            case 'scheduled_staff':
                // Only authorize if a valid team ID was provided from frontend
                // Skip authorization for initial load where user selects team first
                if ($request->input('selectedTeamID')) {
                    $this->authorize('canViewStaffTeam', ['allocate-user', $request->input('selectedTeamID')]);
                }
                return View::make(
                    "pages.admin.allocate-users.scheduled-staff-table",
                    [
                        'userTeamLists' => $this->allocateUserRepositoryInterface->getUserTeamList(Auth::user())
                    ]
                );
            case 'non_scheduled_staff':
                if ($request->input('selectedTeamID')) {
                    $this->authorize('canViewStaffTeam', ['allocate-user', $request->input('selectedTeamID')]);
                }
                $user = Auth::user();
                return View::make(
                    "pages.admin.allocate-users.non-scheduled-staff-table",
                    [
                        'userTeamLists'      => $this->allocateUserRepositoryInterface->getUserTeamList($user),
                        'showAddNonScheduled' => 1
                    ]
                );
            default:
                return response()->json(['error' => 'Invalid tab type'], 400);
        }
    }
}
