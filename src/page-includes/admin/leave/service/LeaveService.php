<?php
/*
 * Created on Thu Jan 21 2022
 *
 * Author: Ashish Tripathi
 * Name:  Class LeaveService
 * Description: A service layer between domain and repository for leaves
 *
 * Copyright (c) 2022 BBC
 */

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../../../../function-includes/DBHelper.php";
require_once __DIR__ . "/../../../../function-includes/helpers.php";
require_once __DIR__ . "/../../../../page-includes/allocations/weekly/service/AllocationService.php";
require_once __DIR__ . "/LeaveRepository.php";

/**
 * 
 */
class LeaveService 
{

    /** @var LeaveRepository $repository */
    protected $repository;
    protected $allocationService;

    public function __construct()
    {
        $this->repository = $this->repository ?? new LeaveRepository();
        $this->allocationService = $this->allocationService ?? new AllocationService();
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
        return $this->allocationService->secondsIntoTime($seconds);
    }
     /* This function is used to convert time into seconds
     *
     * @param $str_time This param contains the time information
     *
     * @return string
     */
    public function timeIntoSeconds($str_time)
    {
        return $this->allocationService->timeIntoSeconds($str_time);
    }

    /**
     *  This function is used to set PHL Leave Amount for Scheduled Person
     *
     * @param $request
     *
     * @return boolean
     */
    function updatePHLLeaveAmountSchPerson(Request $request){
        return $this->repository->updatePHLLeaveAmountSchPerson($request);
    }

    /**
     *  This function is used to set PHL Leave Amount for Scheduled Person
     *
     * @param $request
     *
     * @return boolean
     */
    function addPHLLeave(Request $request){
        return $this->repository->addPHLLeave($request);
    }

    /**
     *  This function is used to get details from TimeDimension table
     *
     * @param $request
     *
     * @return array
     */
    function getTimeDimesionDataById($id){
        return $this->repository->getTimeDimesionDataById($id);
    }
}
