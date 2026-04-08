<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Admin\ScheduledPeopleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller for managing Scheduled Team Histoy - Core CRUD operations
 *
 * @package App\Http\Controllers\Setup
 */
class ScheduleTeamHistoryController extends Controller
{
    /**
     * Invalid parameters error message constant
     */
    private const INVALID_PARAMETERS_MESSAGE = 'Invalid parameters';

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
     * Get schedule team history for a scheduled person.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function teamHistory(int $id): JsonResponse
    {
        $this->authorize('view', 'scheduled-people');

        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid scheduled person ID.'
            ], 422);
        }

        try {
            $user = Auth::user();
            $history = $this->scheduledPeopleRepository->getScheduleTeamHistory($id, $user->id);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Get schedule team history');
        }
    }

    /**
     * Validate if person is assigned to a rota before deletion.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateRota(Request $request): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        $homeTeamId = (int)$request->input('HomeTeamId', 0);
        $personid = (int)$request->input('personid', 0);

        if ($homeTeamId <= 0 || $personid <= 0) {
            return response()->json([
                'IntStatus' => 1,
                'StrStatus' => self::INVALID_PARAMETERS_MESSAGE,
                'FutureHomeHasDuties' => 0
            ]);
        }

        try {
            $result = $this->scheduledPeopleRepository->validateSchPersonHomeTeamHaveRota($homeTeamId, $personid);

            $fhHasDuties = $this->scheduledPeopleRepository->checkFutureHomeTeamHasDuties($personid, $homeTeamId) ? 1 : 0;
            $result['FutureHomeHasDuties'] = $fhHasDuties;

            return response()->json($result);
        } catch (\Exception $e) {
            return $this->handleException($e, 'validate rota');
        }
    }

    /**
     * Delete home team for scheduled person.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteHomeTeam(Request $request): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        $homeTeamId = (int)$request->input('HomeTeamId', 0);
        $personid = (int)$request->input('personid', 0);
        $deleteFromRota = (int)$request->input('DeleteFromRota', 0);

        if ($homeTeamId <= 0 || $personid <= 0) {
            return response()->json([
                ['intStatus' => 1, 'strStatus' => self::INVALID_PARAMETERS_MESSAGE]
            ]);
        }

        try {
            $result = $this->scheduledPeopleRepository->deleteHomeTeam($homeTeamId, $personid, $deleteFromRota, Auth::id() ?? 0);
            return response()->json($result);
        } catch (\Exception $e) {
            return $this->handleException($e, 'delete home team');
        }
    }

    /**
     * Delete Home Team but keep Additional Team intact
     * Choice 1 path - skips duty removal, keeps Additional Team + duties
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteHomeTeamKeepAdditional(Request $request): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        $homeTeamId = (int)$request->input('HomeTeamId', 0);
        $personid = (int)$request->input('personid', 0);
        $deleteFromRota = (int)$request->input('DeleteFromRota', 0);

        if ($homeTeamId <= 0 || $personid <= 0) {
            return response()->json([
                ['intStatus' => 1, 'strStatus' => self::INVALID_PARAMETERS_MESSAGE]
            ]);
        }

        try {
            // Same logic as deleteHomeTeam - repo handles Future Home preservation
            $result = $this->scheduledPeopleRepository->deleteHomeTeam($homeTeamId, $personid, $deleteFromRota, Auth::id() ?? 0);
            return response()->json($result);
        } catch (\Exception $e) {
            return $this->handleException($e, 'delete home team keep additional');
        }
    }

    /**
     * Get conflicting duties for home team deletion
     * Returns duties in Additional (Future Home) Teams that would be affected
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getConflictingDuties(Request $request): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        $homeTeamId = (int)$request->input('HomeTeamId', 0);
        $personid = (int)$request->input('personid', 0);

        if ($homeTeamId <= 0 || $personid <= 0) {
            return response()->json([
                'success' => false,
                'duties' => [],
                'message' => self::INVALID_PARAMETERS_MESSAGE
            ]);
        }

        try {
            $conflictingDuties = $this->scheduledPeopleRepository->getConflictingDutiesForHomeTeamDeletion($homeTeamId, $personid);

            return response()->json([
                'success' => true,
                'duties' => $conflictingDuties,
                'count' => count($conflictingDuties)
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'get conflicting duties');
        }
    }

    /**
     * Remove selected duties from Additional Teams
     * Moves duties to Unallocated status (or deletes if 'Doesn't Need Covering' is set)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeConflictingDuties(Request $request): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        $dutyIds = $request->input('dutyIds', []);
        $personid = (int)$request->input('personid', 0);
        $homeTeamId = (int)$request->input('homeTeamId', 0);

        if (empty($dutyIds) || $personid <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters: ' . json_encode(['dutyIds_count' => count($dutyIds), 'personid' => $personid])
            ]);
        }

        try {
            // Convert duty IDs to integers for safety
            $dutyIds = array_map('intval', $dutyIds);

            // Get duty details including the 'Doesn't Need Covering' flag (AD_IsNeedCovering)
            $dutiesWithFlags = DB::table('AllocationsScheduledPersons as ASP')
                ->join('AllocationsDuties as AD', 'ASP.ASP_AllocationsDutyID', '=', 'AD.AD_AllocationsDutyID')
                ->whereIn('ASP.ASP_AllocationsDutyID', $dutyIds)
                ->where('ASP.ASP_SchedulingPersonID', $personid)
                ->select('ASP.ASP_AllocationsSPID', 'ASP.ASP_AllocationsDutyID', 'AD.AD_DutyDate', 'AD.AD_IsNeedCovering')
                ->get();

            $removed = 0;
            // Separate duties ONLY for email categorization (SP handles actual status/unassign)
            $dutiesToDelete = [];     // AD_IsNeedCovering = 0
            $dutiesToUnallocate = []; // AD_IsNeedCovering = 1

            foreach ($dutiesWithFlags as $duty) {
                if ((int)$duty->AD_IsNeedCovering == 0) {
                    $dutiesToDelete[] = $duty->ASP_AllocationsSPID;
                } else {
                    $dutiesToUnallocate[] = $duty->ASP_AllocationsSPID;
                }
            }

            // Direct UPDATE for AD_IsNeedCovering=0 (status=9) - SP unreliable for this case
            if (!empty($dutiesToDelete)) {
                // Collect AD_AllocationsDutyID for accurate update (ASP_AllocationsSPID ≠ AD_ID)
                $adIdsToCancel = DB::table('AllocationsScheduledPersons')
                    ->whereIn('ASP_AllocationsSPID', $dutiesToDelete)
                    ->pluck('ASP_AllocationsDutyID');

                $cancelled = DB::table('AllocationsDuties')
                    ->whereIn('AD_AllocationsDutyID', $adIdsToCancel)
                    ->update(['AD_DutyStatus' => 9]);
                $removed += $cancelled;
            }

            $netLogin = Auth::user()->UD_NetLogin ?? '';
            $results = $this->scheduledPeopleRepository->bulkUnassignDuties($dutiesToUnallocate, $personid, $netLogin);

            if (!empty($results['errors'])) {
                Log::warning('Some unassign operations failed', [
                    'errors' => $results['errors'],
                    'person_id' => $personid
                ]);
            }

            // Send email notifications for duties within 8 days - categorization now accurate (both arrays passed to SP)
            $this->scheduledPeopleRepository->notifySchedulingTeamOfRemovedDuties($dutyIds, $personid, $dutiesToDelete, $dutiesToUnallocate, Auth::user());

            // Check if Additional Team should be converted/updated - moved to repo
            if ($homeTeamId > 0) {
                $this->scheduledPeopleRepository->handleAdditionalTeamAfterDutyRemoval($personid, $homeTeamId);
            }

            return response()->json([
                'success' => true,
                'message' => 'Selected duties processed.',
                'removed' => $removed,
                'stats' => $results,
                'errors' => $results['errors'] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove duties. Please try again.'
            ], 500);
        }
    }

    /**
     * Get contract history for a scheduled person.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function contractHistory(Request $request)
    {
        $personId = $request->input('personId');

        if (!$personId) {
            return response()->json([
                'data' => []
            ]);
        }

        try {
            $contractHistory = $this->scheduledPeopleRepository->getContractHistory($personId);

            return response()->json([
                'data' => $contractHistory
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching contract history', [
                'personId' => $personId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'data' => [],
                'error' => 'Failed to load contract history'
            ], 500);
        }
    }

    /**
     * Show contract history popup (mimics legacy common.php action).
     *
     * @param Request $request
     * @return string
     */
    public function contractHistoryPopup(Request $request)
    {
        $msg = $request->input('msg', '');
        if (!empty($msg)) {
            $contractHistoryData = base64_decode($msg);
            $contractHistoryDataArr = explode(' -- ', $contractHistoryData);
        }
        // Return rendered blade view instead of raw HTML string (using existing decoded data)
        return view('pages.setup.scheduled-people.partials.contract-history-popup', [
            'historyDataArr' => isset($contractHistoryDataArr) ? $contractHistoryDataArr : []
        ])->render();
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
}
