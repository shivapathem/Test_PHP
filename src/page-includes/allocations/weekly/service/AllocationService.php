<?php
/*
 * Created on Thu Oct 28 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name:  Class AllocaionService
 * Description: A service layer between domain and repository for allocations
 *
 * Copyright (c) 2021 BBC
 */

use App\Models\User\RefRole;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;
use Traits\UserRoleTrait;

require_once __DIR__ . "/../../../../function-includes/DBHelper.php";
require_once __DIR__ . "/../../../../function-includes/helpers.php";
require_once __DIR__ . "/Allocation.php";
require_once __DIR__ . "/AllocationJob.php";
require_once __DIR__ . "/AllocationRepository.php";
require_once __DIR__ . "/PublishWeekService.php";
require_once __DIR__ . "/CreateWeekService.php";
require_once __DIR__ . "/RequestService.php";
require_once __DIR__ . "/TimeDimensionService.php";

/**
 *
 */
class AllocationService
{
    /** @var AllocationRepository $repository */
    protected AllocationRepository $repository;

    /** @var CreateWeekService $repository */
    protected CreateWeekService $createWeekService;

    protected int $isactive;

    public function __construct()
    {
        $this->repository ??= new AllocationRepository();
        $this->createWeekService ??= new CreateWeekService();
        $this->isactive = 1;
    }

    /**
     * Prepares the request object with dates and week number
     *
     * @param Request $request
     * @return Request
     */
    public function prepareRequestDates(Request $request)
    {
        return RequestService::prepareRequestDates($request);
    }

    /**
     * checks if there are records between given dates
     *
     * @param Request $request
     * @return boolean
     */
    public function checkIfWeekExists(Request $request): bool
    {
        if (is_null($request->get('startWeek')) and is_null($request->get('endWeek'))) {
            $request = RequestService::prepareRequestDates($request);
        }
        return $this->repository->checkIfWeekExists($request)['isWeekCreated'];
    }

    /**
     * Gets the Allocated duties
     *
     * @param Request $request
     * @return void
     */
    public function getAllocatedDuties(Request $request)
    {
        //prepare request
        $request = RequestService::prepareRequestDates($request);

        //get allocations
        return $this->repository->getAllocatedDuties($request);
    }

    /**
     * get unallocated duties by week
     * if the week is not created returns false
     *
     * @param Request $request
     * @return false, array
     */
    public function getUnAllocatedDuties(Request $request)
    {
        //prepare request
        $request = RequestService::prepareRequestDates($request);
        //get allocations
        return $this->repository->getUnAllocatedDuties($request);
    }

    public function setIsPublished(Request $request, $isPublished = 1): bool
    {
        return $this->repository->setPublishStatus($request, $isPublished);
    }

    public function dataFormatForMenu($resultUserSetup, $refRolesResult)
    {
        foreach ($refRolesResult as $roleValue) {
            $newRoleNameSet = "is" . str_replace(' ', '', $roleValue['RoleName']);
            $data[$newRoleNameSet] = 0;
        }

        $data['HasXmasPoints'] = $data['HasHandovers'] = $data['isAdmin'] = $data['HasTeamAdmin'] = 0;
        $teamdata = [];
        foreach ($resultUserSetup as $value) {
            $schedulingTeamId = $value['schedulingTeamId'];
            if (!isset($teamdata[$schedulingTeamId])) {
                $teamdata[$schedulingTeamId]['schedulingTeamName'] = $value['schedulingTeamName'];
                $teamdata[$schedulingTeamId]['hasXmasPoints'] = $value['hasXmasPoints'];
                $teamdata[$schedulingTeamId]['hasHandovers'] = $value['hasHandovers'];
                if ($value['hasXmasPoints'] == 1) {
                    $data['HasXmasPoints'] = 1;
                }
                if ($value['hasHandovers'] == 1) {
                    $data['HasHandovers'] = 1;
                }
                if ($value['isDefault'] == 1) {
                    $data['DefaultTeam'] = $schedulingTeamId;
                }
                $teamdata[$schedulingTeamId]['isDefault'] = (($value['isDefault'] == 0 || $value['isDefault'] == null)) ? 0 : 1;
                foreach ($refRolesResult as $roleValue) {
                    $newRoleName = str_replace(' ', '', $roleValue['RoleName']);

                    if ($newRoleName == 'SchedulingTeamAdmin' and $value[$roleValue['RoleName']] != 0) {
                        $data['HasTeamAdmin'] = 1;
                        $data['isAdmin'] = 1;
                    }
                    if (isset($value[$roleValue['RoleName']]) and $value[$roleValue['RoleName']] != 0) {
                        $newRoleNameBasic = "is" . str_replace(' ', '', $roleValue['RoleName']);
                        $data[$newRoleNameBasic] = 1;
                    }
                    if ($roleValue['RoleID'] == 2) {
                        $teamdata[$schedulingTeamId][$newRoleName] = (($value['DivisionId'] == 0 || $value['DivisionId'] == null) ? '<img src="../../../images/red_cross.png" class="tick">' : '<img src="../../../images/green_tick.png" class="tick">');
                    } else {
                        $teamdata[$schedulingTeamId][$newRoleName] = (($value[$roleValue['RoleName']] == 0 || $value[$roleValue['RoleName']] == null) ? 0 : 1);
                    }
                }
            }
        }
        $data['Teams'] = $teamdata;
        return $data;
    }

    public function getPublicFilters(Request $request)
    {
        $publicFilters = $this->repository->getPublicFilters($request);
        if ($publicFilters) {
            $cnt = 0;
            foreach ($publicFilters as $pubFilKey => $pubFilVal) {
                $publicFiltersData[$cnt]['ID'] = $pubFilVal['ID'];
                $publicFiltersData[$cnt]['FilterName'] = $pubFilVal['Description'];
                $cnt++;
            }
            return $publicFiltersData;
        } else {
            return $publicFilters;
        }
    }

    public function getPrivateFilters(Request $request)
    {
        $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
        $privateFilters = $this->repository->getPrivateFilters($request, $sessUserId);
        if ($privateFilters) {
            $cnt = 0;
            foreach ($privateFilters as $prvFilKey => $prvFilVal) {
                $privateFiltersData[$cnt]['ID'] = $prvFilVal['ID'];
                $privateFiltersData[$cnt]['FilterName'] = $prvFilVal['Description'];
                $cnt++;
            }
            return $privateFiltersData;
        } else {
            return $privateFilters;
        }
    }

    public function getUserTeamRole(Request $request)
    {
        $userTrait = new class {
            use UserRoleTrait;
        };
        $isSchedulingTeamAdmin = $userTrait->checkEditWeeeklyAdminRole($request->get('teamId'));        
        $returnData['hasAllAccess'] = $isSchedulingTeamAdmin == 1 ? 1 : 0;
        if($returnData['hasAllAccess'] == 0) {
            $returnData['hasLimitedAccess'] = 1;
        }
        return $returnData;
    }

    /**
     * Returns Scheduling people with additonal teams
     *
     * @param Request $request
     * @param integer $isAdditionalTeamAvailable
     * @return void
     */
    public function getSchedulingPeople(Request $request, $isAdditionalTeamAvailable = 1): array
    {
        return $this->repository->getSchedulingPeople($request, $isAdditionalTeamAvailable);
    }

    /**
     * Add allocation data for person per day on grid
     *
     * @param Request $request
     * @return boolean
     */
    public function addPersonToAllocations(Request $request)
    {
        return $this->repository->addPersonToAllocations($request);
    }

    /**
     * Get allocation record by id
     *
     * @param [type] $id
     * @return array|null
     */
    public function getAllocationByID($id = null)
    {
        return $this->repository->getAllocationByID($id);
    }

	/**
     * Get allocation scheduled person record by id
     *
     * @param [type] $id
     * @return array|null
     */
    public function getSchPersonByID($spid = null, $dutyid = null)
    {
        return $this->repository->getSchPersonByID($spid, $dutyid);
    }

    /**
     * save over twelve for the Allocation ID.
     *
     * @param Request $request
     * @return string
     */
    public function updateOverTwelveAllocation(Request $request)
    {
        return $this->repository->updateOverTwelveAllocation($request);
    }

    /**
     * save over under eleven for the Allocation ID.
     *
     * @param Request $request
     * @return string
     */
    public function updateUnderElevenAllocation(Request $request)
    {
        return $this->repository->updateUnderElevenAllocation($request);
    }

    /**
     * Get Allocation Duty and Person Comments
     *
     * @param Request $request
     * @return array
     */
    public function getAllocationComments(Request $request)
    {
        return $this->repository->getAllocationComments($request);
    }

    /**
     * Add comments for the Allocation ID.
     *
     * @param Request $request
     * @return boolean
     */
    public function addCommentsToAllocations(Request $request): bool
    {
        return $this->repository->addCommentsToAllocations($request);
    }

    /**
     * Get Master & Misc Duties Name by FilterID
     *
     * @param Request $request
     * @return array
     */

    public function getMasterMiscFilterDuties(Request $request)
    {
        $dutyNamesFromFilterID = $this->repository->getMasterMiscFilterDuties($request);
        $dutyNames = [];
        if (!empty($dutyNamesFromFilterID)) {
            foreach ($dutyNamesFromFilterID as $key => $value) {
                $dutyNames[] = $value['DutyName'];
            }
        }
        return $dutyNames;
    }

    /**
     * Get Allocation Duty and Person Comments
     *
     * @param Request $request
     * @return array
     */
    public function getAllocationDetailsByStaff(Request $request)
    {
        return $this->repository->getAllocationDetailsByStaff($request);
    }

    public function checkweek(Request $request)
    {

        $date = new Carbon($request->get('dateonly'));
        //set the start and end of week
        $startWeekDate = $date->startOfWeek(Allocation::BBC_CARBON_DAYS[0]);
        $endWeekDate = clone $startWeekDate;
        $endWeekDate = $endWeekDate->addWeeks($request->get('weeks', 1));
        $endWeekDate = $endWeekDate->subDays(1);
        //check if the duty week in desired weeks
        $dutyWeek = $date->format('YW');
        $dutyWeekSlash = $date->format('W/Y');
        //set week number
        $request->request->set('weekNumber', $dutyWeek);
        $request->request->set('startWeekDate', $startWeekDate->format('Y-m-d'));
        $request->request->set('endWeekDate', $endWeekDate->format('Y-m-d'));
        $request->request->set('weekNumberslash', $dutyWeekSlash);

        $this->request = $request;

        return $request;

    }

    /**
     * Get Duty Allocated Jobs
     *
     * @param Request $request
     * @return array
     */
    public function getDutyAllocatedJobs(Request $request)
    {
        $dutyAllocatedJobs = $this->repository->getDutyAllocatedJobs($request);
        $allocatedJobs = [];
        if (!empty($dutyAllocatedJobs)) {
            $cnt = 0;
            foreach ($dutyAllocatedJobs as $key => $value) {
                $allocatedJobs[$cnt]['JobName'] = $value['JobName'];
                $allocatedJobs[$cnt]['StartTime'] = $value['StartTime'];
                $allocatedJobs[$cnt]['EndTime'] = $value['EndTime'];
                $cnt++;
            }
        }
        return $allocatedJobs;
    }

    /**
     * This function is used to convert seconds into hour minute time
     *
     * @param $seconds This param contains the seconds information
     *
     * @return string
     */
    public function secondsIntoTime($seconds)
    {
        // extract hours
        $hours = floor($seconds / (60 * 60));
        if ($hours >= 24) {
            $hours = $hours - 24;
        }
        // extract minutes
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
        $minutes = ceil($minutes / 15) * 15;
        if ($minutes == 60) {
            $minutes = 0;
        }
        $hours = sprintf("%02s", $hours);
        $minutes = sprintf("%02s", $minutes);
        return $hours . ':' . $minutes;
    }
    /**
     * This function is used to save history
     *
     * @param Request $request
     * @return boolean
     */
    public function addAllocationHistory(Request $request)
    {
        return $this->repository->addAllocationHistory($request);
    }

    /**
     * This function is used to add unallocated duties from master duties
     *
     * @param Request $request
     *
     * @return array
     */

    public function addUnallocteDutiesFromMasterDuties(Request $request)
    {
        return $this->repository->addUnallocteDutiesFromMasterDuties($request);
    }

    /**
     * This function is used to copy all master duties from master duties to allocation
     *
     * @param Request $request
     *
     * @return array
     */

    public function copyAllDutiesToAllocation(Request $request)
    {
        return $this->repository->copyAllDutiesToAllocation($request);
    }

    /**
     * This function is to collect and push the data to add/update unallocated duties
     * into created week
     *
     * @param Request $request
     * @return array
     */

    public function addUnallocteDuties(Request $request)
    {

        $this->request = $request;
        //first get the duty details
        $allocations = new Collection();
        $masterDuties = json_decode((string) $this->repository->getMasterDutiesDetailsByID($request), true);
        $masterDutiyName = $masterDuties[0]['DutyName'];
        //get allocation created week by team with start week and end week
        $allocationCreatedWeeks = json_decode((string) $this->repository->getAllocationsWeekByTeamID($request, $masterDuties[0]['EndWeek']), true);

        //first check we will insert or update the unallocated duties for this
        $action = $this->repository->getDutyForUnallocatedCheck($masterDuties[0]);

        $request->request->set('action', $action['retrstatus']);
        $masterDuties = collect($masterDuties);

        foreach ($allocationCreatedWeeks as $weekdata) {

            $weekarray = str_split((string) $weekdata['WeekNumber'], 4);
            $request->request->set('allocationYear', $weekarray[0]);
            $request->request->set('allocationWeekNumber', $weekarray[1]);
            $request->request->set('startWeek', $weekdata['WeekNumber']);
            //if action is update then calu. the allocation per day and update for each week
            $masterDutiesDetails = $this->generateUnAllocationsFromMasterDuties($masterDuties);
            if (!empty($masterDutiesDetails)) {
                foreach ($masterDutiesDetails as $duty) {
                    $allocations->push($duty);
                }
            }
        }

        $response_array = ['status' => 'success', 'message' => $masterDutiyName . " Assigned as unallocated duty ."];
        if (!empty($masterDutiesDetails)) {
            //insert to allocations table
            $resultData = $this->repository->createWeek($allocations);
            $response_array = ['status' => 'error', 'message' => $masterDutiyName . "Can't Assigned as unallocated duty ."];
            if ($resultData == 1) {
                $response_array = ['status' => 'success', 'message' => $masterDutiyName . " Assigned as unallocated duty ."];
            }
        }
        return $response_array;
    }

    /**
     * This function is to collect the data from master duties by id
     * @param Request $request
     * @return array
     */
    public function getMasterDutiesDetailsByID(Request $request)
    {
        return $this->repository->getMasterDutiesDetailsByID($request);
    }

    /**
     * Get first date of the from week number
     *
     * @param Request $request
     * @return array
     */
    public function getFirstDateOfWeekFromWeek(Request $request)
    {
        $dateVar = new Carbon(); // or $date = new Carbon();
        $dateVar->setISODate($request->get('allocationYear'), $request->get('allocationWeekNumber'));
        $day = $dateVar->startOfWeek(Allocation::BBC_CARBON_DAYS[0]);
        $request->request->set('dateonly', $dateVar->format('Y-m-d'));
        $this->checkweek($request);
    }
    /**
     * This function is used to get Allocated Duty Data From Edit Table based on the AllocationID
     *
     * @param $allocationId This param contains the AllocationID Information
     * @return array
     */
    public function getAllocatedDutiesEdit($allocationId = 0)
    {
        if ($allocationId > 0) {
            return $this->repository->getAllocatedDutiesEdit($allocationId);
        }
        return [];
    }

    /**
     * This function is used to get Duty Shiftcounting Filters
     *
     * @param Request $request
     * @param $filterId This param contains the filterId information for 0 -> Fetch all.
     * @param $isActive This param contains the isActive information.
     * @return array
     */
    public function getDutyShiftCountingFilters(Request $request, $filterId = 0, $isActive = 1)
    {
        return $this->repository->getDutyShiftCountingFilters($request, $filterId, $isActive);
    }

    /**
     * This function is used to get Scheduling Team Details based on Scheduling Team ID.
     *
     * @param Request $request
     * @return array
     */
    public function getSchedulingTeamDetails(Request $request)
    {
        return $this->repository->getSchedulingTeamDetails($request);
    }

    /**
     * This function is used to check duplicate shiftcounting filter name for the particular person in team
     *
     * @param Request $request
     * @return array
     */
    public function checkDuplicateDutyShiftcountingFilter(Request $request)
    {
        return $this->repository->checkDuplicateDutyShiftcountingFilter($request);
    }

    /**
     * This function is used to Add Duty Shiftcounting Filters
     *
     * @param Request $request
     * @return boolean
     */
    public function saveDutyShiftCountingFilter(Request $request)
    {
        return $this->repository->saveDutyShiftCountingFilter($request);
    }

    /**
     * Generates Allocation data from master given master duties collection
     *
     * @param Collection $masterDuties
     * @return array
     */
    private function generateUnAllocationsFromMasterDuties(Collection $masterDuties): array
    {
        $request = $this->request;

        $allocations = [];
        $masterDuties->map(function ($row) use ($request, &$allocations) {
            $row['MasterDutyID'] = $row['DutyID'];
            //create row for each day in request
            $timeDimension = new TimeDimensionService();
            $dimension = $timeDimension->findByWeekNumber($request->get('startWeek'));
            $startDate = new Carbon($dimension->dDateTime);
            $endDate = clone $startDate;
            $endDate->addDays(6);
            $period = new CarbonPeriod(
                $startDate,
                $endDate
            );

            $newduty = $row;

            foreach ($period->toArray() as $date) {

                //create dutie for each day
                $dailyDutieDays = ["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];

                foreach ($dailyDutieDays as $key => $day) {
                    $row['WeekNumber'] = $request->get('startWeek');
                    if ($request->get('action') == 'updateunallocate') {
                        $request->request->set('idayno', $key);
                        $request->request->set('dayvalue', $newduty[$day]);

                        //get the allocation on weeknumber,day and teamid for unallocated record
                        $allocationdaycount = $this->repository->getUnallocatedAllocationPerDay($request);

                        $row[$day] = $allocationdaycount['AllocationDayCount'];
                        if ($allocationdaycount['actualcount'] > 0) {

                            if ($dailyDuties = $this->createWeekService->generateAllocationForDutiesPerDay($row, $day, $date)) {
                                //list daily duties and push allocations one by one
                                foreach ($dailyDuties as $duty) {
                                    $allocations[] = $duty;
                                }
                            }
                        }
                    } else {
                        if ($dailyDuties = $this->createWeekService->generateAllocationForDutiesPerDay($row, $day, $date)) {
                            //list daily duties and push allocations one by one
                            foreach ($dailyDuties as $duty) {
                                $allocations[] = $duty;
                            }
                        }
                    }

                }

            }

        });

        return $allocations;
    }
    /**
     * This function is used to Delete Duty Shiftcounting Filters
     *
     * @param Request $request
     * @return boolean
     */
    public function deleteDutyCountFilter(Request $request)
    {
        return $this->repository->deleteDutyCountFilter($request);
    }

    /**
     * This function is used to Activate Duty Shiftcounting Filters
     *
     * @param Request $request
     * @return boolean
     */
    public function activateDutyCountFilter(Request $request)
    {
        return $this->repository->activateDutyCountFilter($request);
    }

    /**
     * This function is used to Make Duty Shiftcounting Filter Public
     *
     * @param Request $request
     * @return boolean
     */
    public function setPublicDutyCountFilter(Request $request)
    {
        return $this->repository->setPublicDutyCountFilter($request);
    }

    /**
     * Gets the Adhoc duties
     *
     * @param Request $request
     * @return array
     */
    public function getAdhocDuties(Request $request)
    {
        $request = RequestService::prepareRequestDates($request);
        return $this->repository->getAdhocDuties($request);
    }

    /**
     * Delete AdhocDuty for the given ID.
     *
     * @param Request $request
     * @return boolean
     */
    public function deleteAdhocDuty(Request $request): bool
    {
        return $this->repository->deleteAdhocDuty($request);
    }

    /**
     * get active pre defined scikness list
     *@param none
     * @return array
     */
    public function getSicknessList()
    {
        return $this->repository->getSicknessList();
    }
    /**
     * get consecutive sickness record of user
     *@param Request $request
     * @return array
     */
    public function getConsecutiveSicknessRecord($sickDate, $schedulingPersonId)
    {

        return $this->repository->getConsecutiveSicknessRecord($sickDate, $schedulingPersonId);
    }
    /**
     * get scheduled persons record of user
     * @param Request $request
     * @return array
     */
    public function getSchedulePersondetailsByID(Request $request)
    {
        return $this->repository->getSchedulePersondetailsByID($request);
    }

    /**
     * get scheduled persons record of usegr
     * @param Request $request
     * @return array
     */
    public function getScheduleTeamsdetailsByID(Request $request)
    {
        return $this->repository->getScheduleTeamsdetailsByID($request);
    }

    /**
     * convert sec to hours
     *  @param $seconds
     *  @return $hours,$min
     */

    public function seconds2hours($ss)
    {
        $m = floor(($ss % 3600) / 60);
        $h = floor(($ss % 86400) / 3600);
        return "$h:$m";
    }

    /**
     * Gets the Sickness data
     *
     * @param Request $request
     * @return void
     */
    public function getUserSicknessRecord($schedulingPersonId, $dutyDate)
    {

        return $this->repository->getUserSicknessRecord($schedulingPersonId, $dutyDate);
    }

    /**
     * Gets the Sickness data
     *
     * @param Request $request
     * @return void
     */
    public function insertSicknessRecord(Request $request)
    {

        return $this->repository->insertSicknessRecord($request);
    }
    /* This function is used to convert time into seconds
     *
     * @param $str_time This param contains the time information
     *
     * @return string
     */
    public function timeIntoSeconds($str_time = '')
    {
        if ($str_time == '--:--') {
            $str_time = '00:00';
        }
        $arrdur = explode(":", (string) $str_time);
        $hr = 0;
        if (!empty($arrdur) && isset($arrdur[0])) {
            $hr = (int) $arrdur[0];
        }

        $min = 0;
        if (!empty($arrdur) && isset($arrdur[1])) {
            $min = (int) $arrdur[1];
        }

        $sec = 0;
        if (!empty($arrdur) && isset($arrdur[2])) {
            $sec = (int) $arrdur[2];
        }

        $seconds = ($hr * 3600);
        if (isset($min)) {
            $seconds = $seconds + ($min * 60);
        }
        if (isset($sec)) {
            $seconds = $seconds + $sec;
        }
        return $seconds;
    }

    /**
     * Get Adhoc Duty record by id
     *
     * @param [type] $id
     * @return array|null
     */
    public function getAdhocDutyByID($id = null)
    {
        return $this->repository->getAdhocDutyByID($id);
    }

    /**
     * Update AdhocDuty for the given ID.
     *
     * @param Request $request
     * @return boolean
     */
    public function updateAdhocDuty(Request $request): string
    {
        return $this->repository->updateAdhocDuty($request);
    }

    /**
     * Update AdhocDuty for the given ID.
     *
     * @param Request $request
     * @return booleang
     */
    public function getPublishMasterRotasByTeam($schedulingTeamid)
    {

        return $this->repository->getPublishMasterRotasByTeam($schedulingTeamid);
    }
    /**
     * Update AdhocDuty Comments for the given ID.
     *
     * @param Request $request
     * @return boolean
     */
    public function updateAdhocDutyComments(Request $request): bool
    {
        return $this->repository->updateAdhocDutyComments($request);
    }

    /**
     * Remove created week of allocations
     *
     * @param Request $request
     * @return boolean
     */
    public function removeWeekAllocations(Request $request): array
    {
        return $this->repository->removeWeekAllocations($request);
    }

    /**
     * Gets the published record of the week
     *
     * @param Request $request
     * @return boolean
     */
    public function isWeekPublished(Request $request)
    {
        $publishWeekService = new PublishWeekService($request);
        return $publishWeekService->getPubslishRecord();
    }

    /**
     *  This function is used to get current set filter for the daily screen
     *
     * @param Request $request
     * @return Integer
     */
    public function getCurrentSetEditWeeklyFilter(Request $request)
    {
        return $this->repository->getCurrentSetEditWeeklyFilter($request);
    }

    /**
     *  This function is used to get last created week for the team
     *
     * @param Request $request
     * @return Integer
     */
    public function getLastCreatedWeek(Request $request)
    {
        return $this->repository->getLastCreatedWeek($request);
    }

    /**
     *  This function is check if duty is associated with charging or not
     *
     * @param $allocationid
     * @return Integer
     */

    public function getDutyCharging($allocationid)
    {
        return $this->repository->getDutyCharging($allocationid);
    }

    /**
     *  This function is used to insert data in Allocations Edit Table
     *
     * @param Request $request
     * @param $data This param contains the data which was going to update
     *
     * @return boolean
     */
    public function insertAllocationsEdit(Request $request, $data)
    {
        return $this->repository->insertAllocationsEdit($request, $data);
    }

    /**
     *  This function is used to update data in Allocations Edit Table
     *
     * @param Request $request
     * @param $data This param contains the data which was going to update
     * @param $allocationId This param contains the allocation ID information
     *
     * @return boolean
     */
    public function updateAllocationsEdit(Request $request, $data, $allocationId)
    {
        return $this->repository->updateAllocationsEdit($request, $data, $allocationId);
    }

    /**
     *  This function is check next and prev day sickness record
     *
     * @param $allocationid
     * @return Integer
     */

    public function getNextPrevSicknessRecord($dateVal, $schedulingPersonId)
    {
        $getConsecutiveSickness = $this->getConsecutiveSicknessRecord($dateVal, $schedulingPersonId);
        $sickDate = $dateVal;
        $response_array = [];
        $response_array['nextPrevDayReasonId'] = $response_array['nextDayReasonId'] = $response_array['prevDayReasonId'] = $response_array['prevDayMarkSickness'] = $response_array['beforePrevDay'] = 0;
        $response_array['nextPrevDayComment'] = $response_array['nextDayComment'] = $response_array['prevDayComment'] = '';
        $response_array['anotherSicknessRecord'] = '';
        if ($getConsecutiveSickness['totalcount'] > 0) {
            if ($getConsecutiveSickness['PrevDay'] > 0) {
                $prevDate = date('Y-m-d', strtotime('-1 day', strtotime((string) $sickDate)));
                $beforePrevDate = date('Y-m-d', strtotime('-2 day', strtotime((string) $sickDate)));
                //get previous day sickness record
                $getPrevUserSicknessRecord = $this->getUserSicknessRecord($schedulingPersonId, $prevDate);
                $getBeforePrevUserSicknessRecord = $this->getUserSicknessRecord($schedulingPersonId, $beforePrevDate);
                $getPrevUserSicknessRecord[0]['SicknessReasonsId'] ??= 0;
                $getPrevUserSicknessRecord[0]['Comment'] ??= '';
                $response_array['nextPrevDayReasonId'] = $response_array['prevDayReasonId'] = $getPrevUserSicknessRecord[0]['SicknessReasonsId'];
                $response_array['nextPrevDayComment'] = $response_array['prevDayComment'] = $getPrevUserSicknessRecord[0]['Comment'];
                $response_array['prevDayMarkSickness'] = $getPrevUserSicknessRecord[0]['MarkedSickness'];
                $response_array['beforePrevDay'] = $getBeforePrevUserSicknessRecord[0]['SicknessReasonsId'] ?? '';
                $getPrevUserSicknessRecord[0]['Hours'] ??= 0;
                unset($getPrevUserSicknessRecord[0]['Hours']);
                $response_array['anotherSicknessRecord'] = $getPrevUserSicknessRecord;
            }
            if ($getConsecutiveSickness['NextDay'] > 0) {
                $nextDate = date('Y-m-d', strtotime('+1 day', strtotime((string) $sickDate)));
                // get next day sickness record
                $getNextUserSicknessRecord = $this->getUserSicknessRecord($schedulingPersonId, $nextDate);
                $response_array['nextDayReasonId'] = $getNextUserSicknessRecord[0]['SicknessReasonsId'] ?? '';
                $response_array['nextDayComment'] = $getPrevUserSicknessRecord[0]['Comment'] ?? '';
                if ($response_array['prevDayReasonId'] > 0) {
                    $response_array['nextPrevDayReasonId'] = $getNextUserSicknessRecord[0]['SicknessReasonsId'] ?? '';
                    $response_array['nextPrevDayComment'] = $getPrevUserSicknessRecord[0]['Comment'] ?? '';
                    $getNextUserSicknessRecord[0]['Hours'] ??= 0;
                    unset($getNextUserSicknessRecord[0]['Hours']);
                    $getNextUserSicknessRecord[0]['SicknessReasonsId'] = $getPrevUserSicknessRecord[0]['SicknessReasonsId'] ?? '';
                    $getNextUserSicknessRecord[0]['Comment'] = $getPrevUserSicknessRecord[0]['Comment'] ?? '';
                    $response_array['anotherSicknessRecord'] = $getNextUserSicknessRecord;
                } else {
                    $response_array['nextPrevDayReasonId'] = $getNextUserSicknessRecord[0]['SicknessReasonsId'] ?? '';
                    $response_array['nextPrevDayComment'] = $getPrevUserSicknessRecord[0]['Comment'] ?? '';
                    $getNextUserSicknessRecord[0]['Hours'] ??= 0;
                    unset($getNextUserSicknessRecord[0]['Hours']);
                    $response_array['anotherSicknessRecord'] = $getNextUserSicknessRecord;
                }
            }
        }

        return $response_array;
    }

    /**
     *  This function is used to update data in Allocations Edit Table
     *
     * @param Request $request
     *
     * @return string
     */
    public function checkWeekExists(Request $request)
    {
        return $this->repository->checkWeekExists($request);
    }

    /**
     *  This function is used to find previous created week from the passing allocation week
     *
     * @param Request $request
     *
     * @return Integer
     */
    public function findPrevCreatedWeek(Request $request)
    {
        return $this->repository->findPrevCreatedWeek($request);
    }

    /**
     *  This function is used to swap duty within allocation on edit weekly screen
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function createAllocationNewPerson(Request $request)
    {
        return $this->repository->createAllocationNewPerson($request);
    }

    /**
     *  This function is used to get data for Edit Weekly Screen for the Team
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function getEditWeeklyData(Request $request)
    {
        return $this->repository->getEditWeeklyData($request);
    }

    /**
     *  This function is used to get cell data for Edit Weekly Screen for the Team
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function getEditWeeklyDataCell(Request $request)
    {
        return $this->repository->getEditWeeklyDataCell($request);
    }

    public function getWTDTypes(Request $request)
    {
        return $this->repository->getWTDTypes($request);
    }

    /**
     *  This function is used to get Unique Scheduled Persons List for Multiweek
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function getMultiweekSPList(Request $request)
    {
        return $this->repository->getMultiweekSPList($request);
    }

    /**
     *  This function is used to get Duties for Shift Counting Filters
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function getShiftCountingFilterData(Request $request)
    {
        return $this->repository->getShiftCountingFilterData($request);
    }

    /**
     * This function is used to Save Duty Shiftcounting Filter ID for remembering
     *
     * @param $teamId This param contains the Scheduling Team ID information
     * @param $filterId This param contains the filter ID information
     * @return boolean
     */
    public function saveShiftCountingFilter($teamId, $filterId)
    {
        return $this->repository->saveShiftCountingFilter($teamId, $filterId);
    }

    /**
     *  This function is used to get current shift counting filter ID for team
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function getCurrentShiftCountingFilter(Request $request)
    {
        return $this->repository->getCurrentShiftCountingFilter($request);
    }

    /**
     *  This function is used to clear current shift counting filter ID for team
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function clearDutyShiftCountingFilter(Request $request)
    {
        return $this->repository->clearDutyShiftCountingFilter($request);
    }

    /**
     *  This function is used to check week is published or not
     *
     * @param Request $request
     *
     * @return array
     */
    public function checkWeekPublish(Request $request)
    {
        return $this->repository->checkWeekPublish($request);
    }

    /**
     * Gets the published record of the daily screen
     *
     * @param Request $request
     * @return boolean
     */
    public function isWeekPublishedDaily(Request $request)
    {
        $publishWeekService = new PublishWeekService($request);
        return $publishWeekService->getPubslishRecordDaily();
    }

    /**
     * Gets the role of user based on teamID
     *
     * @param $teamId This param contains Team ID information
     *
     * @return array
     */
    public function getRolePermissionEditWeekly($teamId = 0)
    {
        return $this->repository->getRolePermissionEditWeekly($teamId);
    }

    /**
     * Gets the holiday list based on start and end date
     *
     * @param Request $request
     *
     * @return array
     */
    public function getHolidayList(Request $request)
    {
        return $this->repository->getHolidayList($request);
    }

    /**
     * Get Allocation Details For Leave
     *
     * @param Request $request
     *
     * @return array
     */
    public function getAllocationDetailsForLeave(Request $request)
    {
        return $this->repository->getAllocationDetailsForLeave($request);
    }

    /**
     * This function is used to convert seconds into time with options
     *
     * @param $secondsDuration This param contains the duration in seconds.
     * @param $seperator This param contains the separator type information (':','.').
     * @param $displayHours This param contains yes/no information to show text Hours in return string or not
     * @return array
     */
    public function convertSecondsIntoTime($secondsDuration, $seperator = ':', $displayHours = 'Yes')
    {
        $m = floor(($secondsDuration % 3600) / 60);
        $h = floor(($secondsDuration % 86400) / 3600);

        $hDisplay = intval($h);
        $mDisplay = (string) $m;
        if ($seperator == ':') {
            $hDisplay = 0;
            if ($h <= 9) {
                $hDisplay = '0'.$h;
            } else {
                if ($h > 24) {
                    if (($h - 24) <= 9) {
                        $hDisplay = '0'.$h;
                    } else {
                        $hDisplay = $h;
                    }
                } else {
                    if($h == 24){
                        $hDisplay = '00';
                    } else {
                        $hDisplay = $h;
                    }
                }
            }
            $mDisplay = ($m <= '9') ? '0' . $mDisplay : $mDisplay;
        }

        if ($seperator == '.') {
            $mDisplay = match ($mDisplay) {
                "15" => '25',
                "30" => '5',
                "45" => '75',
                default => '00',
            };
        }
        if ($displayHours == 'Yes') {
            return $hDisplay . $seperator . $mDisplay . ' hrs';
        } else {
            return $hDisplay . $seperator . $mDisplay;
        }
    }

    /**
     * Verify Part Day Leave
     *
     * @param Request $request
     *
     * @return array
     */
    public function verifyPartDayLeave(Request $request)
    {
        return $this->repository->verifyPartDayLeave($request);
    }

    /**
     * Auto Apply and Approve PDL
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function autoApplyApprovePDL(Request $request)
    {
        return $this->repository->autoApplyApprovePDL($request);
    }

	/**
     * Get Allocation Duty and Person Comments
     *
     * @param Request $request
     * @return array
     */
    public function getSchedulingNotes(Request $request)
    {
        return $this->repository->getSchedulingNotes($request);
    }

    /**
     * Get scheduled persons list including allocated additional persons
     *
     * @param Request $request
     * @return array
     */
    public function getTeamScheduledPersons(Request $request)
    {
        return $this->repository->getTeamScheduledPersons($request);
    }

    /**
     * This function is used to copy duty from one peron to another
     *
     * @param Request $request
     * @return array
     */
    public function copyDuty(Request $request)
    {
        return $this->repository->copyDuty($request);
    }

    /**
     * Get Week Number by Date
     *
     * @param $date This param contains date
     * @return array
     */
    public function getWeekNumberByDate($date = '')
    {
        return $this->repository->getWeekNumberByDate($date);
    }

    /**
     * Check week and Role
     *
     * @param Request $request
     *
     * @return array
     */
    public function checkWeekAndRole(Request $request)
    {
        $intTeamID = $request->get('teamId');
        $userTrait = new class {
            use UserRoleTrait;
        };
        $isScheduler = $userTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULER);
        $isSchTeamAdmin = $userTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_ADMIN);
        $isTeamLeader = $userTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::TEAM_LEADER);
        if($isSchTeamAdmin == 0) {        
            $isSchTeamAdmin = $userTrait->isSystemAdmin() || $userTrait->isAreaAdminByTeam($request->get('teamId')) ? 1 : 0;
        }
        $date3MonthsBack = date('Y-m-d', strtotime('-3 months'));
        $date2YearsBack = date('Y-m-d', strtotime('-2 years'));
        $getWeekNum3MonthsBack = (int) $this->getWeekNumberByDate($date3MonthsBack)['ixYearWeek'];
        $getWeekNum2YearsBack = (int) $this->getWeekNumberByDate($date2YearsBack)['ixYearWeek'];
        $currentWeek = (int) $request->get('startWeek');
		switch($isScheduler.'-'.$isSchTeamAdmin.'-'.$isTeamLeader){
            case "1-0-0":
				$isScheduler = 1;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 0;
				break;
			case "0-0-0":
			case "1-0-0":
				$isScheduler = 0;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 0;
				break;
			case "0-1-0":
			case "1-1-0":
			case "0-1-0":
			case "1-1-0":
				$isScheduler = 0;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 0;
				break;
			case "0-0-1":
				$isScheduler = 0;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 1;
				break;
			case "1-0-1":
				$isScheduler = 1;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 1;
				break;
			case "0-0-1":
				$isScheduler = 0;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 1;
				break;
			case "0-1-1":
				$isScheduler = 0;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 1;
				break;
			case "1-1-1":
				$isScheduler = 1;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 1;
				break;
			case "1-0-1":
				$isScheduler = 1;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 1;
				break;
			case "0-1-1":
				$isScheduler = 0;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 1;
				break;
			case "1-1-1":
				$isScheduler = 1;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 1;
				break;
	    }

        $returnData['screenView'] = 0;
        $returnData['screenEdit'] = 0;

        if(($isScheduler == 1) && ($currentWeek < $getWeekNum3MonthsBack)){
            $returnData['screenView'] = 1;
        } else if(($isScheduler == 1) && ($currentWeek >= $getWeekNum3MonthsBack)){
            $returnData['screenEdit'] = 1;
        } else if((($isSchTeamAdmin == 1)) && ($currentWeek < $getWeekNum2YearsBack)){
            $returnData['screenView'] = 1;
        } else if((($isSchTeamAdmin == 1)) && ($currentWeek >= $getWeekNum2YearsBack)){
            $returnData['screenEdit'] = 1;
        } else if(($isTeamLeader == 1) && ($currentWeek >= $getWeekNum3MonthsBack)){
            $returnData['screenEdit'] = 1;
        } else if(($isTeamLeader == 1) && ($currentWeek < $getWeekNum3MonthsBack)){
            $returnData['screenView'] = 1;
        }
        return $returnData;
    }

    /**
     * This function is used to check duty exists on dates
     *
     * @param Request $request
     * @return integer
     */
    public function checkDutyExists(Request $request)
    {
        return $this->repository->checkDutyExists($request);
    }

    /**
     * This function is used to get max end date based on team
     *
     * @param Request $request
     * @return array
     */
    public function getCopyDutyEndDate(Request $request)
    {
        return $this->repository->getCopyDutyEndDate($request);
    }

    /**
     * This function is used to get schedulers and sr.schedulers list for the duty belongs to
     *
     * @param Request $request
     * @return array
     */
    public function getSchedulersList(Request $request)
    {
        return $this->repository->getSchedulersList($request);
    }
}
