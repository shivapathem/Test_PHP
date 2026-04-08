<?php
/*
 * Created on Thu Nov 04 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name:  Class CreateWeekService
 * Description: A service to handle create week functionality
 *
 * Copyright (c) 2021 BBC
 */


use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/Allocation.php";
require_once __DIR__ . "/AllocationJob.php";
require_once __DIR__ . "/AllocationRepository.php";
require_once __DIR__ . "/AllocationService.php";
require_once __DIR__ . "/TimeDimensionService.php";
require_once __DIR__ . "/RequestService.php";
require_once __DIR__ . "/../../../../function-includes/init.php";
require_once __DIR__ . "/PublishWeekService.php";


class CreateWeekService
{

    /** @var AllocationRepository $repository */
    protected $repository;

    /** @var Request $request */
    protected $request;

    public function __construct(?Request $request = null)
    {
        $this->repository ??= new AllocationRepository();
        if ($request !== null) {
            $this->request = RequestService::prepareRequestDates($request);
        }
    }

    /**
     * checks if there are records between given dates
     *
     * @param Request $request
     * @return boolean
     */
    public function checkIfWeekExists(Request $request) : array
    {
        $request = RequestService::prepareRequestDates($request);
        return $this->repository->getWeekData($request);
    }

    public function createWeek($force = false)
    {
        $request = RequestService::prepareRequestDates($this->request);
        $check = $this->checkIfWeekExists($request);
		if($check['isWeekCreated']){
			return false;
        }
        return $this->repository->createWeekAllocations($request);
    }

    /**
     * generates an empty grid for people in given team
     *
     * @param Request $request
     * @return array
     */
    public function generateEmptyGridData(Request $request)
    {
        $allocations = [];

        $peopleInTeam = $this->repository->getSchedulingPeople($request);

        $timeDimension = new TimeDimensionService();
        $createWeekNumber = $timeDimension->findByWeekNumber($request->get('startWeek'))->ixYearWeek;

        foreach($peopleInTeam as $person){
            //create grid
            $startDate = new Carbon($request->get('startDate'));
            $startDate->startOfWeek(Allocation::BBC_CARBON_DAYS[0]);
            $endDate = clone $startDate;
            $endDate = $endDate->addWeek()->subDay();
            $period = new CarbonPeriod(
                $startDate,
                $endDate
            );

            foreach($period->toArray() as $date){
                $iDayTimeDimension = (int) $timeDimension->findByDateWithoutCarbon($date->format('Y-m-d H:i:s'))->ixDayInWeek;
                //add each person to date
                $allocation = new Allocation();
                $allocation->UnAllocated = 1;
                $allocation->SchedulingPersonID = $person['ScheduledPersonID'];
                $allocation->DutyDate = $date->format('Y-m-d H:i:s');
                $allocation->IsHomeTeam = $person['IsHomeTeam'];
                $allocation->WeekNumber = $createWeekNumber;
                $allocation->SchedulingTeamId = $request->get('teamId');
                $allocation->iDay   = $iDayTimeDimension;

                $allocations[] = $allocation;
            }
        }

        return $allocations;
    }

    /**
     * Generates Allocation data from master given master duties collection
     *
     * @param Request $request
     * @return array
     */
    public function generateAllocationsFromMasterDuties(Request $request) : array
    {
        $repository = $this->repository;

        $masterDuties = collect($this->repository->getMasterDuties($request));

        $timeDimension = new TimeDimensionService();
        $dimension = $timeDimension->findByWeekNumber($request->get('startWeek'));

        $startDate = new Carbon($dimension->dDateTime);
        $endDate = clone $startDate;
        $endDate->addDays(6);

        $period = new CarbonPeriod(
            $startDate,
            $endDate
        );

        $allocations = [];
        $masterDuties->map(function($row) use ($request, $repository, $period, &$allocations) {

            //get jobs for duty
            $jobs = $repository->getMasterDutyJobs($row['MasterDutyID']);

            foreach($period->toArray() as $date){

                //if duty day is not in our grid date then skip
                if(!$date->isBetween(new Carbon($row['StartDate']), new Carbon($row['EndDate']))){
                    continue;
                }

                //create dutie for each day
                $dailyDutieDays = ["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
                foreach($dailyDutieDays as $day){


                    $row['WeekNumber'] = $request->get('startWeek');
                    if($dailyDuties = $this->generateAllocationForDutiesPerDay($row, $day, $date, $jobs)){
                        //list daily duties and push allocations one by one
                        foreach($dailyDuties as $duty){
                            $allocations[] = $duty;
                        }

                    }
                }


            }

        });

        return $allocations;

    }

    /**
     * Generates the allocation row for based on Master Duties rules.
     *
     * @param array $row
     * @param string $day
     * @param Carbon $date
     * @return array
     */
    public function generateAllocationForDutiesPerDay($row = [], $day = 'Saturday', ?CarbonInterface $date = null, $jobs = []) : array
    {
        $allocations = [];

        //how many duty should be added per day?
        for($i = 1; $i <= $row[$day]; $i++){
            //if coming date is equal to row date, then add it
            if($date->format('l') === $day){
                $startTime = clone $date;
                $startTime->addSeconds($row['StartTime']);
                $endTIme = clone $date;
                $endTIme->addSeconds($row['EndTime']);

                if($row['Duration'] < 1 ){
                    $row['Duration'] = floatVal($endTIme->diffInSeconds($startTime));
                }

                $output['SchedulingTeamId'] = $row['TeamID'];
                $output['DutyName']         = $row['DutyName'];
                $output['DutyDate']         = $date->startOfDay()->format('Y-m-d H:i:s');
                $output['StartDate']        = $startTime->addSeconds($row['StartTime'])->format('Y-m-d H:i:s');
                $output['EndDate']          = $endTIme->addSeconds($row['EndTime'])->format('Y-m-d H:i:s');
                $output['WeekNumber']       = $row['WeekNumber'];
                $output['Duration']         = $row['Duration'];
                $output['FontColour']       = trim((string) $row['ForeColour']);
                $output['BackColour']       = trim((string) $row['BackColour']);
                $output['SchedulingPersonID'] = null;
                $output['UnAllocated']      = 1;
                $output['StartTime']        = $row['StartTime'];
                $output['EndTime']          = $row['EndTime'];
                $output['iDay']             = Allocation::BBC_DAYS_MAPPING[$date->dayOfWeek];
                $output['MasterDutyId']      = $row['MasterDutyID'];
                $output['dutyColorId']      = $row['DutyColourID'];
                $output['isActiveDuty']      = 1;

                $allocation = new Allocation($output);

                //add jobs if there are jobs
                if($jobs){
                    foreach($jobs as $job){
                        $allocationJob = new AllocationJob($job);
                        $allocation->addJob($allocationJob);
                    }
                }

                $allocations[] = $allocation;
            }

        }

        return $allocations;
    }

    /**
     * Generates allocation data from rotas
     *
     * @param Request $request
     * @return array
     */
    public function generateAllocationsFromRotas(Collection $rotas) : array
    {
        $allocations = [];


        $timeDimension = new TimeDimensionService();
        $dimension = $timeDimension->findByWeekNumber($rotas->first()['ixYearWeek']);


        $rotas->transform(function($row) use ($dimension){

            $date = new Carbon($dimension->dDateTime);
            //set the day of duty based on Day of Week
            $date->addDay($row['DOTW']);

            $row['WeekNumber'] = $row['ixYearWeek'];
            $row['iDay'] = $row['DOTW'];

            //set start date and end date if it has startTime and Endtime
            if($row['StartTime'] && $row['EndTime']){
                $endDate  = clone $date;
                $startDate = $date->addSeconds($row['StartTime']);
                $endDate->addSeconds($row['EndTime']);
                $row['StartDate'] = $startDate->format('Y-m-d H:i:s');
                $row['EndDate'] = $endDate->format('Y-m-d H:i:s');

                //set duration if not set
                if(empty($row['Duration'])){
                    $row['Duration'] = floatVal($endDate->diffInSeconds($startDate));
                }
            }

            $row['DutyDate']        = $date->startOfDay()->format('Y-m-d H:i:s');
            $row['BackColour']      = isset($row['BackColour']) ? trim((string) $row['BackColour']) : "0";
            $row['FontColour']      = isset($row['FontColour']) ? trim((string) $row['FontColour']) : "0";
            $row['MasterDutyId']    = $row['MasterDutyID'];
            $row['dutyColorId']    = $row['DutyColourID'] ?? 0;
            $row['UnAllocated']     = 0;

            return $row;
        });

        $rotas = $rotas->groupBy(['SchedulingPersonID','MasterDutyID', 'DutyDate']);

        //push to allocations
        foreach($rotas as $schedulingPerson => $personDuties){
            if(empty($schedulingPerson)) continue; //TODO cross check this scenario with business

            foreach($personDuties as $dutyId => $duties){
                //get jobs for duty
                $jobs = $this->repository->getMasterDutyJobs($dutyId);
                foreach($duties as $day => $duty){
                    $allocation = new Allocation($duty->first());

                    //add jobs
                    if($jobs){
                        foreach($jobs as $job){
                            $allocationJob = new AllocationJob($job);
                            $allocation->addJob($allocationJob);
                        }
                    }

                    $allocations[] = $allocation;
                }
            }
        }


        return $allocations;
    }


     /**
     * This function is used to form allocations per day from AdhocDuty for a given week
     *
     * @param $adhocDuties This param contains the AdhocDuties Information
     * @return array
     */
	private function generateAllocationFromAdhocDuties($adhocDuties, Request $request) : array
    {
	    $allocations = [];

        $timeDimensionService = new TimeDimensionService();
        $createWeekNumber = $timeDimensionService->findByWeekNumber($request->get('startWeek'))->ixYearWeek;

		foreach($adhocDuties as $duty){
            $date =  new Carbon($duty['StartDate']);
            $endDate = new Carbon($duty['EndDate']);
            $iDayTimeDimension = (int) $timeDimensionService->findByDateWithoutCarbon($date->format('Y-m-d H:i:s'))->ixDayInWeek;

			$allocationDutyPerDay['AdhocDuty'] = 1;
			$allocationDutyPerDay['DutyName'] = $duty['DutyName'];
			$allocationDutyPerDay['StaffNumber'] = $duty['StaffNumber'] ?? 0;
			$allocationDutyPerDay['PersonComments'] = $duty['Comment'];
			$allocationDutyPerDay['SchedulingPersonID'] = $duty['ScheduledPersonID'];
			$allocationDutyPerDay['SchedulingTeamId'] = $duty['SchedulingTeamID'];
			$allocationDutyPerDay['IsHomeTeam'] = $duty['IsHomeTeam'];
			$allocationDutyPerDay['DutyDate'] = $date->format('Y-m-d H:i:s');
			$allocationDutyPerDay['WeekNumber'] = $createWeekNumber;
			$allocationDutyPerDay['StartDate']= $date->format('Y-m-d H:i:s');
			$allocationDutyPerDay['EndDate'] = $endDate->format('Y-m-d H:i:s');
			$allocationDutyPerDay['StartTime'] = $duty['StartTime'];
			$allocationDutyPerDay['EndTime'] = $duty['EndTime'];
			$allocationDutyPerDay['iDay'] = $iDayTimeDimension;
			$allocationDutyPerDay['DutyComments'] = $duty['Comment'];
			$allocationDutyPerDay['dutyColorId'] = $duty['DutyColourID'] ?? 0;

			$allocationDutyPerDay['Duration'] = floatVal($allocationDutyPerDay['EndTime'] - $allocationDutyPerDay['StartTime']);
			$allocations[] = new Allocation($allocationDutyPerDay);
		}

        return $allocations;
	}

}