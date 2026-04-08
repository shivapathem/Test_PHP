<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduledPeopleRequest\ScheduledPersonRequest;
use App\Http\Requests\ScheduledPeopleRequest\UpdateScheduledPersonRequest;
use App\Http\Requests\ScheduledPeopleRequest\ValidateAdditionalTeamRequest;
use App\Repositories\Contracts\Admin\ScheduledPeopleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

/**
 * Controller for managing Scheduled People - Core CRUD operations
 *
 * @package App\Http\Controllers\Setup
 */
class ScheduledPeopleController extends Controller
{
    /**
     * Scheduled People Repository
     *
     * @var ScheduledPeopleRepositoryInterface
     */
    protected $scheduledPeopleRepository;

    /**
     * ScheduledPeopleController constructor.
     *
     * @param ScheduledPeopleRepositoryInterface $scheduledPeopleRepository
     */
    public function __construct(ScheduledPeopleRepositoryInterface $scheduledPeopleRepository)
    {
        $this->scheduledPeopleRepository = $scheduledPeopleRepository;
    }

    /**
     * Redirect to create page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {

        try {
            return view('pages.setup.scheduled-people.index', [
                'mode'   => 'search'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching scheduled people list', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to load scheduled people.'], 500);
        }
    }

    /**
     * Show create form for scheduled person.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $this->authorize('create', 'scheduled-people');

        // Get query parameters from URL
        $userAction = $request->input('useraction', 'create');
        $selectedTeamId = $request->input('selectedteamid', '');
        $selectedUserId = $request->input('selecteduserid', '');
        $schedulePersonId = $request->input('schedulepersonid', null);

        return view('pages.setup.scheduled-people.index', [
            'mode'   => $userAction,
            'person' => null,
            'personId' => $schedulePersonId,
            'preSelectedTeamId' => $selectedTeamId,
            'preSelectedUserId' => $selectedUserId


        ]);
    }

    /**
     * Store a newly created scheduled person.
     *
     * @param ScheduledPersonRequest $request
     * @return JsonResponse
     */
    public function store(ScheduledPersonRequest $request): JsonResponse
    {
        $this->authorize('create', 'scheduled-people');

        $data = $request->validated();

        try {
            $rows = $this->scheduledPeopleRepository->createSchedulePerson(
                $data['SPTeamID'] ?? 0,
                $data['DisplayName'] ?? '',
                $data['SchedulingTeamID'] ?? 0,
                $data['HomeTeamStartDate'] ?? '',
                $data['HomeTeamEndDate'] ?? '',
                $data['HomeTeamSortCode'] ?? '',
                $data['HomeTeamBackColour'] ?? '',
                $data['HomeTeamFontColour'] ?? '',
                $data['HomeTeamAdminNotes'] ?? '',
                $data['HomeTeamFWANotes'] ?? '',
                $data['ActionType'] ?? 'create',
                $data['ScheduledPersonID'] ?? 0,
                $data['AdditionalTeamArray'] ?? '[]',
                Auth::id() ?? 0,
                $data['DisplayFirstName'] ?? '',
                $data['DisplayLastName'] ?? '',
                $data['IsDefaultBGColour'] ?? 0,
                $data['IsAdditionalLeave'] ?? 0
            );

            // Default failure response
            $response = response()->json([
                'success' => false,
                'message' => 'Unable to process your request. Please try again.'
            ], 500);

            if (!empty($rows)) {
                $row   = $rows;
                $newId = isset($row['intnewidschpeople']) ? (int) $row['intnewidschpeople'] : null;
                $status = strtolower(trim($row['strstatusschpeople'] ?? ''));

                if ($status === 'success' && $newId) {
                    $response = response()->json([
                        'success'  => true,
                        'message'  => 'New Scheduled Person saved successfully.',
                        'redirect' => route('setup.scheduled-people.edit', $newId),
                        'id'       => $newId
                    ]);
                } else {
                    $response = $this->handleStoredProcedureResponse(
                        $row,
                        'create scheduled person',
                        $newId,
                        'setup.scheduled-people.edit'
                    );
                }
            }

            return $response;
        } catch (\Exception $e) {
            return $this->handleException($e, 'store scheduled person');
        }
    }

    /**
     * Display scheduled person details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        $this->authorize('view', 'scheduled-people');

        $person = $this->scheduledPeopleRepository->getSchedulepersondetails($id);
        abort_unless(!empty($person), 404);

        return View::make('pages.setup.scheduled-people.index', [
            'mode'     => 'view',
            'person'   => $person,
            'personId' => $id
        ]);
    }

    /**
     * Show edit form for scheduled person.
     * Validates that user has permission to edit this person based on team membership.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $this->authorize('update', 'scheduled-people');

        if ($id <= 0) {
            abort(404);
        }

        $person = $this->scheduledPeopleRepository->getSchedulepersondetails($id);
        abort_unless(!empty($person), 404);

        // Security Check: Validate that user has permission to edit this scheduled person
        // User can edit a person only if the person's home team is in their authorized teams
        $authorizedTeams = $this->scheduledPeopleRepository->getSchedulingTeams(
            Auth::id() ?? 0,
            'edit',
            6,
            $id
        );

        // Extract team IDs from authorized teams
        $authorizedTeamIds = array_map(function ($team) {
            return (int)($team['TeamID'] ?? 0);
        }, $authorizedTeams);

        // Policy authorization for edit permission
        $policy = new \App\Policies\Setup\ScheduledPeoplePolicy();
        $personHomeTeamId = isset($person[0]['TeamID']) ? (int)$person[0]['TeamID'] : 0;

        if (!$policy->canEditScheduledPerson(Auth::user(), $authorizedTeamIds, $personHomeTeamId)) {
            abort(403, 'You do not have permission to edit this scheduled person.');
        }

        return View::make('pages.setup.scheduled-people.index', [
            'mode'     => 'edit',
            'person'   => $person,
            'personId' => $id
        ]);
    }

    /**
     * Update scheduled person.
     * Validates that user has permission to update this person based on team membership.
     *
     * @param UpdateScheduledPersonRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateScheduledPersonRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        $data = $request->validated();

        // Default response (used unless overwritten)
        $response = response()->json([
            'success' => false,
            'message' => 'Invalid scheduled person ID.'
        ], 422);

        if ($id <= 0) {
            $response = response()->json([
                'success' => false,
                'message' => 'Invalid scheduled person ID.'
            ], 422);

            return $response;
        }

        // Security Check
        $authorizedTeams = $this->scheduledPeopleRepository->getSchedulingTeams(
            Auth::id() ?? 0,
            'edit',
            6,
            $id
        );

        $authorizedTeamIds = array_map(
            fn($team) => (int)($team['TeamID'] ?? 0),
            $authorizedTeams
        );

        $policy = new \App\Policies\Setup\ScheduledPeoplePolicy();
        $personDetails = $this->scheduledPeopleRepository->getSchedulepersondetails($id);
        $personHomeTeamId = isset($personDetails[0]['TeamID'])
            ? (int) $personDetails[0]['TeamID']
            : 0;

        $canEdit = $policy->canEditScheduledPerson(
            Auth::user(),
            $authorizedTeamIds,
            $personHomeTeamId
        );

        if (!$canEdit) {
            $response = response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this scheduled person.'
            ], 403);

            return $response;
        }

        // Prepare values
        $spTeamId = !empty($data['SPTeamID']) ? (int) $data['SPTeamID'] : 0;
        $schedulingTeamId = (int) $data['SchedulingTeamID'];
        $homeTeamStartDate = $data['HomeTeamStartDate'] ?? '';
        $homeTeamEndDate   = $data['HomeTeamEndDate'] ?? '';

        try {
            $rows = $this->scheduledPeopleRepository->createSchedulePerson(
                $spTeamId,
                $data['DisplayName'],
                $schedulingTeamId,
                $homeTeamStartDate,
                $homeTeamEndDate,
                $data['HomeTeamSortCode'] ?? '',
                $data['HomeTeamBackColour'] ?? '',
                $data['HomeTeamFontColour'] ?? '',
                $data['HomeTeamAdminNotes'] ?? '',
                $data['HomeTeamFWANotes'] ?? '',
                'edit',
                $id,
                $data['AdditionalTeamArray'] ?? '[]',
                Auth::id() ?? 0,
                $data['DisplayFirstName'] ?? '',
                $data['DisplayLastName'] ?? '',
                $data['IsDefaultBGColour'] ?? 0,
                $data['IsAdditionalLeave'] ?? 0
            );

            $response = !empty($rows)
                ? $this->handleStoredProcedureResponse(
                    $rows,
                    'update scheduled person',
                    $id,
                    'setup.scheduled-people.edit'
                )
                : response()->json([
                    'success' => false,
                    'message' => 'Unable to process your request. Please try again.'
                ], 500);
        } catch (\Exception $e) {
            $response = $this->handleException($e, 'update scheduled person');
        }

        return $response;
    }

    /**
     * Get team list for scheduled person.
     * Filters teams according to create permissions:
     * - Freelancer permission only: shows only Freelancers team
     * - Staff permission only: shows all teams except Freelancers
     * - Both permissions: shows all teams
     * - No permissions: shows no teams (but authorize prevents this)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function teamList(Request $request): JsonResponse
    {
        $this->authorize('createAny', 'scheduled-people');

        $user = Auth::user();
        $actionType = $request->input('actionType', 'create');
        $rows = $this->scheduledPeopleRepository->getSchedulingTeams(
            Auth::id(),
            $actionType,
            6,
            $request->input('personId', 0)
        );
        // Get user permissions
        $policy = new \App\Policies\Setup\ScheduledPeoplePolicy();
        $permissions = $policy->getPermissionsForUser($user);
        $canCreateStaff = $permissions['canCreateStaff'];
        $canCreateFreelancer = $permissions['canCreateFreelancer'];

        // Filter teams based on permissions
        if ($canCreateStaff && $canCreateFreelancer) {
            // Both permissions: show all teams
            $filteredRows = $rows;
        } elseif ($canCreateFreelancer && !$canCreateStaff) {
            // Only freelancer permission: show only Freelancers team
            $filteredRows = array_filter($rows, function ($team) {
                $teamName = is_array($team) ? ($team['TeamName'] ?? '') : ($team->TeamName ?? '');
                return strtoupper($teamName) === 'FREELANCERS';
            });
        } elseif ($canCreateStaff && !$canCreateFreelancer) {
            // Only staff permission: show all teams except Freelancers
            $filteredRows = array_filter($rows, function ($team) {
                $teamName = is_array($team) ? ($team['TeamName'] ?? '') : ($team->TeamName ?? '');
                return strtoupper($teamName) !== 'FREELANCERS';
            });
        } else {
            // No permissions: show no teams (though authorize should prevent this)
            $filteredRows = [];
        }

        return response()->json(array_values($filteredRows));
    }

    /**
     * Get scheduled person details.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function details(int $id): JsonResponse
    {
        $data = $this->scheduledPeopleRepository->getPersonDetails($id);
        return response()->json($data);
    }

    /**
     * Validate additional team data.
     *
     * @param ValidateAdditionalTeamRequest $request
     * @return JsonResponse
     */
    public function validateAdditionalTeam(ValidateAdditionalTeamRequest $request): JsonResponse
    {
        $v = $request->validated();

        $schedulepersonid = (int)($v['schedulepersonid'] ?? 0);
        $ddlteamsid       = (int)($v['ddlteamsid'] ?? 0);
        $addteamsid       = (int)($v['addteamsid'] ?? 0);
        $addteamstartdate = $v['addteamstartdate'] ?? '';
        $addteamenddate   = $v['addteamenddate'] ?? '9999-01-01';

        return response()->json([
            'homeTeamFirstStartDate' =>
            $this->scheduledPeopleRepository->validateAddHomeTeamsStartDate($schedulepersonid),

            'homeTeamsDurations' =>
            $this->scheduledPeopleRepository->validateAddTeamBetweenAnyExistingHomeTeamsDuration($schedulepersonid),

            'endDateAllocationCheck' =>
            $this->scheduledPeopleRepository->validateAddTeamsEndDate(
                $ddlteamsid,
                $schedulepersonid,
                $addteamenddate,
                $addteamstartdate,
                $addteamsid
            ),

            'additionalTeamPastRecords' =>
            $this->scheduledPeopleRepository->validateAddTeamBetweenAnyExistingAdditioanlTeam(
                $schedulepersonid,
                $addteamsid,
                $addteamstartdate,
                $addteamenddate
            )
        ]);
    }

    /**
     * Handle stored procedure response and return appropriate JSON response.
     *
     * @param array $row Response row from stored procedure
     * @param string $operation Type of operation (create, update, etc.)
     * @param int|null $id Optional ID for redirect
     * @param string|null $redirectRoute Route name for redirect
     * @return JsonResponse
     */
    protected function handleStoredProcedureResponse(array $row, string $operation = 'operation', ?int $id = null, ?string $redirectRoute = null): JsonResponse
    {
        $status = $this->normalizeStatus($row);
        $returnString = $row['strreturnstringschpeople'] ?? '';

        $this->logStoredProcError($row, $operation);

        $category = $this->categorizeStatus($status);
        $responseData = $this->buildResponse($category, $returnString, $operation, $id, $redirectRoute);

        return response()->json($responseData);
    }

    /**
     * Handle exception and return consistent error response.
     *
     * @param \Exception $e
     * @param string $context Context description for logging
     * @return JsonResponse
     */
    protected function handleException(\Exception $e, string $context = 'Operation'): JsonResponse
    {
        $this->logError($e, "Error in {$context}");

        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again.'
        ], 500);
    }

    /**
     * Log an error message with exception details.
     *
     * @param \Exception $exception
     * @param string $message
     * @return void
     */
    protected function logError(\Exception $exception, string $message = 'An error occurred'): void
    {
        Log::error($message, [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Normalize stored procedure status string.
     */
    private function normalizeStatus(array $row): string
    {
        return strtolower(trim($row['strstatusschpeople'] ?? ''));
    }

    /**
     * Categorize normalized status into response category.
     */
    private function categorizeStatus(string $status): string
    {
        return match ($status) {
            'success' => 'success',
            'usererror' => 'user_error',
            'error' => 'system_error',
            'exception' => 'system_error',
            default => stripos($status, 'error') !== false || stripos($status, 'fail') !== false ? 'user_error' : 'success'
        };
    }

    /**
     * Log stored procedure error based on status.
     */
    private function logStoredProcError(array $row, string $operation): void
    {
        $status = $this->normalizeStatus($row);
        $returnString = $row['strreturnstringschpeople'] ?? '';

        if ($status === 'usererror') {
            $this->logError(new \Exception($returnString ?: 'User error'), "User error during {$operation}");
        } elseif ($status === 'error') {
            $this->logError(new \Exception($returnString ?: 'System error'), "System error during {$operation}");
        }
    }

    /**
     * Build standardized JSON response.
     */
    private function buildResponse(
        string $category,
        string $returnString,
        string $operation,
        ?int $id = null,
        ?string $redirectRoute = null
    ): array {

        $isSuccess   = $category === 'success';
        $isUserError = $category === 'user_error';

        if ($isSuccess) {
            $message = ucfirst($operation) . ' completed successfully.';
        } elseif ($isUserError) {
            $message = $returnString ?: 'Please check your input and try again.';
        } else {
            // system_error (legacy behavior preserved)
            $message = (!app()->environment('production') && $returnString) ? $returnString : 'An error occurred while processing your request. Please try again.';
        }

        $response = [
            'success' => $isSuccess,
            'message' => $message
        ];

        if ($isSuccess && $id && $redirectRoute) {
            $response['redirect'] = route($redirectRoute, $id);
            $response['id'] = $id;
        }

        return $response;
    }

    public function search(Request $request)
    {
        // Support both legacy (POST) and autocomplete (GET) parameter names
        // Keep as string to match legacy PDO::PARAM_STR behavior
        $teamId = (string) ($request->input('selectedTeamID') ?? $request->input('teamid', ''));
        $userName = $request->input('selectedUserName') ?? $request->input('term') ?? '';
        $excludeNoTeam = $request->input('excludeNoTeam', 1);

        // Pass teamId as string (like legacy code) and excludeNoTeam as int
        $people = $this->scheduledPeopleRepository->getScheduledPeopleUserList($teamId, $userName, 6, (int) $excludeNoTeam);
        // Check if this is an autocomplete request (GET with term but no selectedTeamID)
        if ($request->isMethod('GET') && !empty($userName) && empty($request->input('selectedTeamID'))) {
            $displayNames = array_map(function ($person) {
                return $person['DisplayName'] ?? '';
            }, $people);
            return response()->json($displayNames);
        }

        // Return legacy format for POST requests (matches original PHP behavior)
        if ($request->isMethod('POST')) {
            return response()->json([
                'status' => 'success',
                'data' => $people
            ]);
        }

        // Default JSON response for GET requests
        return response()->json($people);
    }

    public function getPermissions(Request $request)
    {
        $user = Auth::user();

        // Use policy to get permissions
        $policy = new \App\Policies\Setup\ScheduledPeoplePolicy();
        $permissions = $policy->getPermissionsForUser($user);

        // Set redirect URL when user doesn't have view permission
        $redirect = $permissions['canview'] == 0 ? route('setup.scheduled-people.index') : '';

        $response = [
            'status' => 'success',
            'permissions' => $permissions,
            'redirect' => $redirect
        ];

        return response()->json($response);
    }
}
