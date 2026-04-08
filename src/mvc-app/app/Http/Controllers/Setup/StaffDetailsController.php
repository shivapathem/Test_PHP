<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduledPeopleRequest\UpdateStaffRequest;
use App\Http\Requests\ScheduledPeopleRequest\SearchStaffRequest;
use App\Http\Requests\ScheduledPeopleRequest\AttachStaffRequest;
use App\Repositories\Contracts\Admin\ScheduledPeopleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller for managing Staff Details related to Scheduled People
 *
 * @package App\Http\Controllers\Setup
 */
class StaffDetailsController extends Controller
{
    /**
     * Error message for invalid scheduled person ID
     */
    private const ERROR_INVALID_PERSON_ID = 'Invalid scheduled person ID.';

    /**
     * Scheduled People Repository
     *
     * @var ScheduledPeopleRepositoryInterface
     */
    protected $scheduledPeopleRepository;

    /**
     * StaffDetailsController constructor.
     *
     * @param ScheduledPeopleRepositoryInterface $scheduledPeopleRepository
     */
    public function __construct(ScheduledPeopleRepositoryInterface $scheduledPeopleRepository)
    {
        $this->scheduledPeopleRepository = $scheduledPeopleRepository;
    }

    /**
     * Update staff details for scheduled person.
     *
     * @param UpdateStaffRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStaff(UpdateStaffRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => self::ERROR_INVALID_PERSON_ID
            ], 422);
        }

        $data = $request->validated();

        // Update staff details via repository
        $result = $this->scheduledPeopleRepository->updateStaffDetails(
            $id,
            [
                'FirstName' => $data['FirstName'] ?? '',
                'Surname' => $data['Surname'] ?? '',
                'MiddleName' => $data['MiddleName'] ?? '',
                'PreferredName' => $data['PreferredName'] ?? '',
                'Designation' => $data['Designation'] ?? ''
            ]
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Staff details updated successfully.',
                'redirect' => route('setup.scheduled-people.edit', $id),
                'FirstName' => $data['FirstName'] ?? '',
                'Surname' => $data['Surname'] ?? '',
                'MiddleName' => $data['MiddleName'] ?? '',
                'PreferredName' => $data['PreferredName'] ?? '',
                'Designation' => $data['Designation'] ?? ''
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update staff details.'
        ], 500);
    }

    /**
     * Search staff details with filters.
     *
     * @param SearchStaffRequest $request
     * @return JsonResponse
     */
    public function searchStaff(SearchStaffRequest $request): JsonResponse
    {
        $this->authorize('view', 'scheduled-people');

        $data = $request->validated();

        $searchForeName = $data['selectedForeName'] ?? null;
        $searchNetLogin = $data['selectedNetLogin'] ?? null;
        $searchSurName = $data['selectedSurName'] ?? null;
        $searchStaffNumber = $data['selectedStaffNumber'] ?? null;
        $actionType = $data['actionType'] ?? 'search';

        try {
            // Note: The stored procedure expects parameters in order: StaffNumber, ForeName, Surname, NetLogin
            $staffDetailsLists = $this->scheduledPeopleRepository->getStaffDetailsConfig(
                $searchStaffNumber,
                $searchForeName,
                $searchSurName,
                $searchNetLogin
            );

            if (empty($staffDetailsLists) && $actionType === 'search') {
                return response()->json([
                    'status' => 'success',
                    'staffDetails' => []
                ]);
            }

            // Process staff details and build structured data for JavaScript
            $staffDetails = [];
            foreach ($staffDetailsLists as $staffDetail) {
                $scheduledType = 1;
                $staffID = (int)($staffDetail['StaffID'] ?? 0);
                $scheduledPersonID = $staffDetail['ScheduledPersonID'] ?? 0;

                if (!empty($staffDetail['ScheduledPersonID'])) {
                    $scheduledType = $this->scheduledPeopleRepository->checkScheduledType($staffDetail['ScheduledPersonID']);
                }

                // Determine button state using helper method
                $buttonState = $this->determineButtonState($staffID, $staffDetail, $scheduledType);

                $staffDetails[] = [
                    'StaffID' => $staffID,
                    'StaffNumber' => $staffDetail['StaffNumber'] ?? '',
                    'NetLogin' => $staffDetail['NetLogin'] ?? '',
                    'Forename' => $staffDetail['Forename'] ?? '',
                    'Surname' => $staffDetail['Surname'] ?? '',
                    'ScheduledPersonID' => $scheduledPersonID,
                    'buttonState' => $buttonState
                ];
            }

            return response()->json([
                'status' => 'success',
                'staffDetails' => $staffDetails
            ]);
        } catch (\Exception $e) {
            $this->logError($e, 'Error in searchStaff');
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while searching staff details.'
            ], 500);
        }
    }

    /**
     * Determine button state for staff detail row.
     * Extracted to reduce cognitive complexity of searchStaff method.
     *
     * @param int $staffID
     * @param array $staffDetail
     * @param int $scheduledType
     * @return string
     */
    private function determineButtonState(int $staffID, array $staffDetail, int $scheduledType): string
    {
        // Default to linked - only show attach buttons when StaffID is valid
        if ($staffID <= 0 || !empty($staffDetail['StaffDetailsID']) && $scheduledType != 0) {
            return 'linked';
        }

        $userType = $staffDetail['userType'] ?? 'Freelancer';
        $isConfig = (int)($staffDetail['IsConfig'] ?? 0);
        $isLinked = (int)($staffDetail['IsLinked'] ?? 0);

        return $this->getButtonStateForUserType($userType, $isConfig, $isLinked);
    }

    /**
     * Determine button state based on user type and configuration.
     *
     * @param string $userType
     * @param int $isConfig
     * @param int $isLinked
     * @return string
     */
    private function getButtonStateForUserType(string $userType, int $isConfig, int $isLinked): string
    {
        if ($userType === 'Freelancer') {
            return $isConfig === 1 ? 'attach-tp' : 'linked';
        }

        // Non-Freelancer user type
        if ($isConfig === 1 && $isLinked === 0) {
            return 'attach-tp';
        }

        return $isLinked === 0 ? 'attach-ad' : 'linked';
    }

    /**
     * Attach staff to a scheduled person.
     *
     * @param AttachStaffRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function attachStaff(AttachStaffRequest $request, int $id): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        $data = $request->validated();

        $staffId = $data['selectedstaffID'] ?? 0;
        $actionType = $data['actionType'] ?? 'select';
        $oldScheduledPersonID = $data['oldschedPersonID'] ?? 0;

        // Handle cancel action BEFORE calling repository to avoid stored procedure errors
        // when empty staffId is passed for cancel action
        if ($actionType === 'cancel') {
            return response()->json([
                'status' => 'success',
                'staffDetails' => []
            ]);
        }

        try {
            $result = $this->scheduledPeopleRepository->attachStaffDetailsConfig(
                $id,
                $staffId,
                $actionType,
                'success',
                '',
                Auth::id() ?? 0,
                $oldScheduledPersonID
            );

            $status = $result['strstatus'] ?? 'success';
            $returnString = $result['strreturnstring'] ?? '';

            // Build staff details from stored procedure result
            $staffDetails = [
                'PreferredForename' => $result['PreferredForename'] ?? '',
                'StaffNumber' => $result['StaffNumber'] ?? '',
                'NetLogin' => $result['NetLogin'] ?? '',
                'Forename' => $result['Forename'] ?? '',
                'Surname' => $result['Surname'] ?? '',
                'MiddleName' => $result['MiddleName'] ?? '',
                'JobTitle' => $result['JobTitle'] ?? ''
            ];

            return response()->json([
                'status' => $status,
                'staffDetails' => $staffDetails,
                'message' => $returnString,
                'staffFirstName'  => $staffDetails['Forename'] ?? '',
                'staffLastName'   => $staffDetails['Surname'] ?? '',
                'staffNumber'     => $staffDetails['StaffNumber'] ?? '',
                'staffNetLogin'   => $staffDetails['NetLogin'] ?? '',
                'staffPreferred'  => $staffDetails['PreferredForename'] ?? '',
                'staffJobTitle'   => $staffDetails['JobTitle'] ?? '',
                'staffMiddleName' => $staffDetails['MiddleName'] ?? '',
            ]);
        } catch (\Exception $e) {
            $this->logError($e, 'Error in attachStaff');
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while attaching staff.'
            ], 500);
        }
    }

    /**
     * Detach staff from a scheduled person.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function detachStaff(int $id): JsonResponse
    {
        $this->authorize('update', 'scheduled-people');

        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => self::ERROR_INVALID_PERSON_ID
            ], 422);
        }

        try {
            // Detach by setting actionType to 'cancel'
            $result = $this->scheduledPeopleRepository->attachStaffDetailsConfig(
                $id,
                0,
                'cancel',
                'success',
                '',
                Auth::id() ?? 0,
                0
            );

            $status = $result['strstatus'] ?? 'success';
            $returnString = $result['strreturnstring'] ?? 'Staff detached successfully.';

            return response()->json([
                'success' => $status === 'success',
                'message' => $returnString,
                'staffDetails' => []
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'detach staff');
        }
    }

    /**
     * Get staff details for a scheduled person.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getStaff(int $id): JsonResponse
    {
        $this->authorize('view', 'scheduled-people');

        if ($id <= 0) {
            return response()->json([
                'success' => false,
                'message' => self::ERROR_INVALID_PERSON_ID
            ], 422);
        }

        try {
            $staffDetails = $this->scheduledPeopleRepository->getStaffDetailsByPersonId($id);

             // Return null if no staff is linked (not empty array)
            // This ensures the UI shows empty fields when no staff is attached
            return response()->json([
                'success' => true,
                'staffDetails' => $staffDetails
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'get staff');
        }
    }

    /**
     * Staff autocomplete - returns suggestions based on search term and field.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function staffAutocomplete(Request $request): JsonResponse
    {
        $this->authorize('view', 'scheduled-people');

        $termKey = $request->input('termKey', 'NetLogin');
        $term = $request->input('term', '');

        if (empty($term)) {
            return response()->json([]);
        }

        // Get search parameters using helper method
        $searchParams = $this->getAutocompleteSearchParams($termKey, $term);

        try {
            $staffList = $this->scheduledPeopleRepository->getStaffDetailsConfig(
                $searchParams['searchStaffNumber'],
                $searchParams['searchForeName'],
                $searchParams['searchSurName'],
                $searchParams['searchNetLogin']
            );

            // Build autocomplete list using helper method
            $autocompleteList = $this->buildAutocompleteList($staffList, $termKey);

            return response()->json(array_values($autocompleteList));
        } catch (\Exception $e) {
            $this->logError($e, 'Error in staffAutocomplete');
            return response()->json([]);
        }
    }

    /**
     * Get search parameters for autocomplete based on termKey.
     *
     * @param string $termKey
     * @param string $term
     * @return array
     */
    private function getAutocompleteSearchParams(string $termKey, string $term): array
    {
        return [
            'searchForeName' => $termKey === 'Forename' ? $term : null,
            'searchNetLogin' => $termKey === 'NetLogin' ? $term : null,
            'searchSurName' => $termKey === 'Surname' ? $term : null,
            'searchStaffNumber' => $termKey === 'StaffNumber' ? $term : null,
        ];
    }

    /**
     * Build autocomplete list from staff data.
     *
     * @param array $staffList
     * @param string $termKey
     * @return array
     */
    private function buildAutocompleteList(array $staffList, string $termKey): array
    {
        if (empty($staffList)) {
            return [];
        }

        $autocompleteList = [];
        foreach ($staffList as $data) {
            if ($this->isValidAutocompleteValue($data, $termKey)) {
                $autocompleteList[] = $data[$termKey];
            }
        }

        return array_unique($autocompleteList);
    }

    /**
     * Check if a value is valid for autocomplete.
     *
     * @param array $data
     * @param string $termKey
     * @return bool
     */
    private function isValidAutocompleteValue(array $data, string $termKey): bool
    {
        return isset($data[$termKey]) && !empty($data[$termKey]);
    }

    /**
     * Log an error message with exception details.
     *
     * @param \Exception $exception
     * @param string $message
     * @return void
     */
    private function logError(\Exception $exception, string $message = 'An error occurred'): void
    {
        Log::error($message, [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Handle exception and return consistent error response.
     *
     * @param \Exception $e
     * @param string $context Context description for logging
     * @return JsonResponse
     */
    private function handleException(\Exception $e, string $context = 'Operation'): JsonResponse
    {
        $this->logError($e, "Error in {$context}");

        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred. Please try again.'
        ], 500);
    }
}
