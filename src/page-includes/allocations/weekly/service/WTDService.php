<?php
/*
 * Created on Fri Oct 29 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Class WTDService
 * Description: Work Time Directive breach service. 
 *
 * Copyright (c) 2021 BBC
 */


use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Dumper\JsonFileDumper;

require_once __DIR__ . "/Allocation.php";
require_once __DIR__ . "/AllocationRepository.php";
require_once __DIR__ . "/AllocationService.php";

class WTDService 
{

    const STATUS_BREACH = 1;
    const STATUS_NO_BREACH = 0;
    const STATUS_APPROVED = 2;
    const STATUS_DELETED = 3;

    /**
     * @var AllocationRepository
     */
    public $repository;

    /**
     * @var AllocationService
     */
    public $allocationService;

    /**
     * @var Request
     */
    public $request;


    public function __construct(?Request $request = null)
    {
        $this->repository = new AllocationRepository();
        $this->allocationService = new AllocationService();
        $this->request = $request;
    }

    /**
     * Checks rules per allocation row.
     *
     * @param Allocation $allocation
     * @return void
     */
    public function checkByAllocation(Allocation $allocation)
    {
        $this->seventeenWeeksRule($allocation);

        //11 hours rule
        $this->elevenHoursRule($allocation);

        //6 days in a row
        $this->sixDaysInRowRule($allocation);


    }

    /**
     * Applies six days in a row rule
     * Gets the scheduling person allocations +/- 6 days 
     * If the scheduling person has duties in 6 days in a row
     * Then applies the breach on those allocations
     *
     * @param Allocation $allocation
     * @return void
     */
    public function sixDaysInRowRule(Allocation $allocation)
    {
        $allocationDate = new Carbon($allocation->DutyDate);

        //get previous 6 days
        $startDate = clone $allocationDate;
        $startDate->subDays(6);

        //get next 6 days
        $endDate = clone $allocationDate;
        $endDate->addDays(6);

        $allocations = collect($this->repository->getAllocationByUserAndDate(
            $allocation->SchedulingPersonID, 
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ));
        
        //set default wtd as no breach
        $allocations->transform(function($row){
            if($row['MarkWTD'] !== self::STATUS_DELETED) {
                $row['MarkWTD'] = self::STATUS_NO_BREACH;
            }

            return $row;
        });

        //group by date
        $allocations = $allocations->groupBy(['DutyDate']);
        $allocations = $allocations->sortBy(function ($product, $key) {
            $date = new Carbon($key);
            return $date->timestamp;
        });

        //get the constraint allocation ids
        $constraintIds = [];

        //check if user has allocations in six days in row
        $daysInRow = 1;
        $previousDay = null;

        $allocs = collect();

        foreach($allocations as $dutyDate => $allocation){
            if(null !== $previousDay){  
                $previousDay = new Carbon($previousDay);
                $previousDay->startOfDay();
                $currentDay  = new Carbon($dutyDate);
                $currentDay->startOfDay();

                //skip if the same day
                if($currentDay->isSameDay($previousDay)){
                    foreach($allocation as $duty){
                        if($duty['MarkWTD'] !== self::STATUS_DELETED) {
                            $duty['MarkWTD'] = self::STATUS_BREACH;
                            $duty['WTDComments'] = "WTD Breach - 6 days in a row";
                        }
                        $allocs->push(new Allocation($duty));
                    }
                    continue;
                }

                //set no breach if diff is more than one day
                if($currentDay->diffInDays($previousDay) > 1){
                    $daysInRow = 1;
                    foreach($allocation as $duty){
                        if($duty['MarkWTD'] !== self::STATUS_DELETED) {
                            $duty['MarkWTD'] = self::STATUS_NO_BREACH;
                            $duty['WTDComments'] = null;
                        }
                        $allocs->push(new Allocation($duty));
                    }
                }else{
                    foreach($allocation as $duty){
                        if($duty['MarkWTD'] !== self::STATUS_DELETED) {
                            $duty['MarkWTD'] = self::STATUS_BREACH;
                            $duty['WTDComments'] = "WTD Breach - 6 days in a row";
                        }
                        $allocs->push(new Allocation($duty));
                    }
                    $daysInRow++;
                }
            }

            $previousDay = $dutyDate;
        }

        foreach($allocs as $allocation){
            $this->repository->updateAllocation($allocation->toArray(), ['ID' => $allocation->ID]);
        }

    }


    /**
     * Applies 11 hours rule for each allocation
     * Gets the scheduling person allocations per day
     * Checks the duty end date with next duty start day
     * If the gap is less than 11 hours apply the WTD 
     *
     * @param Allocation $allocation
     * @return void
     */
    public function elevenHoursRule(Allocation $allocation)
    {
        if(is_null($allocation->ID)){
            return;
        }

        //next dutie start should be at least eleven hours
        $allocationDate = new Carbon($allocation->DutyDate);
        $allocationDate->startOfDay();

        $nextDay = clone $allocationDate;
        $nextDay = $nextDay->addDay();

        //get next day allocation
        $nextAllocations = $this->repository->getAllocationByUserAndDate(
            $allocation->SchedulingPersonID, 
            $nextDay->format('Y-m-d')
        );

        $nextIds = collect($nextAllocations)->pluck('ID');
        $nextAllocation = $nextAllocations[0] ?? null;

        if(is_null($nextAllocation)){
            return;
        }

        $nextAllocation = new Allocation($nextAllocation);

        if($nextAllocation->MarkedSickness || $nextAllocation->MarkedCompLeave) {
            return;
        }

        //if next duty starts less than 11 hours after current duty, then mark WTD
        $nextDutyDate = new Carbon($nextAllocation->DutyDate);
        $nextDutyDate->addSeconds($nextAllocation->StartTime);

        $allocationDate->addSeconds($allocation->EndTime);

        //check for next day
        $diff = $nextDutyDate->diffInHours($allocationDate);
        if($diff <= 11){
            //mark WDT of next allocation
            $data['MarkWTD'] = 1;
            $data['WTDComments'] = "WTD Breach - 11 hours";
        }else{
            $data['MarkWTD'] = 0;
            $data['WTDComments'] = null;
        }

        foreach($nextIds as $id){
            $this->repository->updateAllocation($data, ['ID' => $id]);
        }

    }


    /**
     * Applies 17 weeks rule for WTD
     * Checks the previous and 17 weeks average hours
     * If averare hour is more than 48 then applies the breach
     *
     * @param Allocation $allocation
     * @return void
     */
    public function seventeenWeeksRule(Allocation $allocation)
    {
        $weekNumber = $allocation->WeekNumber;
        $year = substr($weekNumber, 0, 4);
        $week = substr($weekNumber, 4, 6);

        $date = new Carbon();
        $date->setISODate($year, $week);

        $startWeek = clone $date;
        $startWeek->subWeeks(17);

        $endWeek = clone $date;
        $endWeek->addWeeks(17);

        //calculate the previous weeks
        $previousAllocations = $this
                ->repository
                ->getAllocationsByWeekRange(
                    $allocation->SchedulingTeamId,
                    $startWeek->format('YW'), 
                    $allocation->WeekNumber
                );

        $previousAllocations = collect($previousAllocations);
        
        $isBreachPreviousWeeks = $this->calculateSeventeenWeeksRule($previousAllocations);

        if($isBreachPreviousWeeks){
            $data['MarkWTD'] = self::STATUS_BREACH;
            $data['WTDComments'] = "WTD Breach - 17 weeks";
        }else{
            $data['MarkWTD'] = self::STATUS_NO_BREACH;
            $data['WTDComments'] = null;
        }

        $this->repository->updateAllocation($data, [
            'ID' => $previousAllocations->implode('ID',',')
        ]);

        //calculate the next weeks
        $nextAllocations = $this
                ->repository
                ->getAllocationsByWeekRange(
                    $allocation->SchedulingTeamId,
                    $allocation->WeekNumber, 
                    $endWeek->format('YW')
                );
        $nextAllocations = collect($nextAllocations);
        $isBreachNextWeeks = $this->calculateSeventeenWeeksRule($nextAllocations);

        if($isBreachNextWeeks){
            $data['MarkWTD'] = self::STATUS_BREACH;
            $data['WTDComments'] = "WTD Breach - 17 weeks";
        }else{
            $data['MarkWTD'] = self::STATUS_NO_BREACH;
            $data['WTDComments'] = null;
        }

        $this->repository->updateAllocation($data, [
            'ID' => $nextAllocations->implode('ID',',')
        ]);
    }

    /**
     * Checks the average hours per week in given collection
     * If average hours of 17 weeks is more than 48 hours 
     *
     * @param Collection $allocations
     * @return boolean
     */
    private function calculateSeventeenWeeksRule(Collection $allocations) : bool
    {
        //group by week and date
        $allocations = $allocations->groupBy(['WeekNumber', 'DutyDate']);

        $weekBreaches = [];
        $totalSeconds = 0;
        foreach($allocations as $weekNumber => $date){
            foreach ($date as $allocation){
                $first = $allocation->first();

                if(isset($weekBreaches[$weekNumber])){
                    $weekBreaches[$weekNumber] += $first['Duration'];
                }else{
                    $weekBreaches[$weekNumber] = $first['Duration'];
                }

                $totalSeconds += $first['Duration'];
            }
        }

        //if average is more than 48 hours return true
        $totalHours = $totalSeconds / 60 / 60;
        if ($totalHours / 17 >= 48 ){
            return true;
        }

        return false;

    }

    /**
     * This function is used to get breach details by ID
     *
     * @param Request $request
     *
     * @return array
     */
    public function getBreachDetailById(Request $request)
    {
        return $this->repository->getBreachDetailById($request);
    }

    /**
     *  This function is used to get breaches list for the scheduled person based on duty date
     *
     * @param Request $request
     *
     * @return array
     */
    public function getBreachesList(Request $request)
    {
        return $this->repository->getBreachesList($request);
    }

    /**
     *  This function is used to get Scheduling Person Name
     *
     * @param Request $request
     *
     * @return array
     */
    public function getSchedulingPersonName(Request $request)
    {
        return $this->repository->getSchedulingPersonName($request);
    }

    /**
     *  This function is used to update breach details
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function updateBreachDetails(Request $request)
    {
        return $this->repository->updateBreachDetails($request);
    }
   
   /**
     *  This function is used to verify breach details
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function verifyWTDBreachDetails(Request $request)
    {
        return $this->repository->verifyWTDBreachDetails($request); 
    }

    /**
     *  This function is used to delete breach details
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function deleteWTDBreachDetails(Request $request)
    {
        return $this->repository->deleteWTDBreachDetails($request); 
    }
}