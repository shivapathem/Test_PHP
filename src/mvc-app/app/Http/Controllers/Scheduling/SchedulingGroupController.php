<?php

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Models\Scheduling\Division;
use App\Models\Scheduling\SchedulingGroup;
use App\Models\HistoryLog;
use App\Repositories\Contracts\SchedulingGroupRepositoryInterface;
use App\Http\Requests\StoreSchedulingGroupRequest;
use App\Http\Requests\UpdateSchedulingGroupRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class SchedulingGroupController extends Controller
{
    protected $schedulingGroupRepository;

    public function __construct(SchedulingGroupRepositoryInterface $schedulingGroupRepository)
    {
        $this->authorizeResource(SchedulingGroup::class, 'schedulingGroup');
        $this->schedulingGroupRepository = $schedulingGroupRepository;
    }

    /**
     * Display a listing of the scheduling groups.
     */
    public function index()
    {
        $user = Auth::user();
        $divisions = $this->getUserDivisions($user);

        $schedulingGroups = $this->schedulingGroupRepository->getAllSchedulingGroups();

        // Filter by user's divisions if not system admin
        if (!$user->isSystemAdmin) {
            $divisionIds = $divisions->pluck('DivisionID');
            $schedulingGroups = $schedulingGroups->whereIn('DivisionID', $divisionIds);
        }

        // Load relationships for the filtered collection
        $schedulingGroups->load(['area', 'schedulingTeams', 'updatedBy']);
        return view('pages.admin.scheduling.schedulingGroupList', compact('schedulingGroups', 'divisions'));
    }

    /**
     * Show the form for creating a new scheduling group.
     */
    public function create()
    {
        $user = Auth::user();
        $divisions = $this->getUserDivisions($user);

        return view('pages.admin.scheduling.schedulingGroupCreate', compact('divisions'));
    }

    /**
     * Store a newly created scheduling group.
     */
    public function store(StoreSchedulingGroupRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = Auth::user();
            $this->schedulingGroupRepository->saveSchedulingGroup($validated, $user);
            return response()->json([
                'redirect' => route('scheduling-group.index'),
                'message' => 'Scheduling group added successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'redirect' => route('scheduling-group.index'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified scheduling group.
     */
    public function show(SchedulingGroup $schedulingGroup)
    {
        $this->authorize('view', $schedulingGroup);

        return view('pages.admin.scheduling.schedulingGroupShow', compact('schedulingGroup'));
    }

    /**
     * Show the form for editing the specified scheduling group.
     */
    public function edit(SchedulingGroup $schedulingGroup)
    {
        $this->authorize('update', $schedulingGroup);

        $user = Auth::user();
        $divisions = $this->getUserDivisions($user);

        $schedulingGroup->load('schedulingTeams');

        return view('pages.admin.scheduling.schedulingGroupCreate', compact('schedulingGroup', 'divisions'));
    }

    /**
     * Update the specified scheduling group.
     */
    public function update(UpdateSchedulingGroupRequest $request, SchedulingGroup $schedulingGroup)
    {
        $this->authorize('update', $schedulingGroup);
        try {
            $validated = $request->validated();
            $user = Auth::user();
            $this->schedulingGroupRepository->updateSchedulingGroup($schedulingGroup, $validated, $user);
            return response()->json([
                'redirect' => route('scheduling-group.index'),
                'message' => 'Scheduling group updated successfully'
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'redirect' => route('scheduling-group.index'),
                'error' => 'Something went wrong. Please try again'
            ], 500);
        }
    }

    /**
     * Show delete confirmation form.
     */
    public function delete(SchedulingGroup $schedulingGroup)
    {
        $this->authorize('delete', $schedulingGroup);

        return view('pages.admin.scheduling.schedulingGroupDelete', compact('schedulingGroup'));
    }

    /**
     * Remove the specified scheduling group.
     */
    public function destroy(SchedulingGroup $schedulingGroup)
    {
        try {
            $this->authorize('delete', $schedulingGroup);
            $user = Auth::user();
            $this->schedulingGroupRepository->destroySchedulingGroup($schedulingGroup, $user);
            return response()->json([
                'success' => true,
                'message' => 'Scheduling group deleted successfully'
            ], 200);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Unable to delete scheduling group: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get divisions accessible to the current user.
     */
    private function getUserDivisions($user)
    {
        if ($user->isSystemAdmin) {
            return Division::all();
        }

        // For Area Admin, return only their assigned divisions
        return $user->getAreasRoles->unique('DivisionID');
    }

    /**
     * Get scheduling teams for a specific area.
     */
    public function getAreaTeams(Division $area)
    {
        $teams = $this->schedulingGroupRepository->getAreaTeams($area);
        return response()->json($teams->map(function ($team) {
            return [
                'id' => $team->schedulingTeamId,
                'name' => $team->schedulingTeamName
            ];
        }));
    }


    /**
     * Get scheduling group history.
     *
     * @param  SchedulingGroup $schedulingGroup
     * @return \Illuminate\Http\Response
     */
    public function history(SchedulingGroup $schedulingGroup)
    {
        return View::make("pages.history.history-view", [
            'title' => __('Scheuling Group History', ['schedulingGroup' => $schedulingGroup->SchedulingGroupsName]),
            'historyLogs' => $schedulingGroup->history()->orderBy('HL_ID', HistoryLog::HISTORY_SORT_ORDER_DESC)->get()
        ]);
    }
}
