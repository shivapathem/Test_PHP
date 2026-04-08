<?php
/*
 * Created on Thu Oct 02 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Class Allocation
 * Description: An Entity (value object) that mapped to Allocations table. 
 *
 * Copyright (c) 2021 BBC
 */

include_once __DIR__ . "/ArrayableTrait.php";
include_once __DIR__ . "/AllocationJob.php";

use Carbon\CarbonInterface;

class Allocation {

    use ArrayableTrait;

    const BBC_DAYS_MAPPING = [
        CarbonInterface::SATURDAY => 0,
        CarbonInterface::SUNDAY => 1,
        CarbonInterface::MONDAY => 2,
        CarbonInterface::TUESDAY => 3,
        CarbonInterface::WEDNESDAY => 4,
        CarbonInterface::THURSDAY => 5,
        CarbonInterface::FRIDAY => 6,
    ];

    const BBC_CARBON_DAYS = [
        0 => CarbonInterface::SATURDAY,
        1 => CarbonInterface::SUNDAY,
        2 => CarbonInterface::MONDAY,
        3 => CarbonInterface::TUESDAY,
        4 => CarbonInterface::WEDNESDAY,
        5 => CarbonInterface::THURSDAY,
        6 => CarbonInterface::FRIDAY,
    ];

    public $ID;

    public $AllocateInstanceID = 0;

    /** @deprecated */
    public $DepartmentID = 0;

    public $AllocationID = 0;

    public $StaffNumber = null;

    public $DutyName = "U";

    public $Duration = 0;

    public $WeekNumber;

    public $iDay = 0;

    public $StartTime = null;

    public $EndTime = null;

    public $ActingGrade = 0;

    public $SortCode = null;

    public $LeaveID = 0;

    public $ManualERR = null;

    public $DutyComments = null;

    public $BaseCode = 0;

    public $BackColour = null;

    public $FontColour = null;

    public $PersonComments = null;
    
    public $AdhocDuty = 0;

    public $MarkedOvertime= 0;

    public $MarkedPTExtraDay = 0;
   
    public $MarkedCompLeave = 0;

    public $MarkedSickness = 0;

    public $ManualOTAmount = 0.0;

    public $MarkWiad = 0;
    
    public $MarkActual = 0;

    public $ManualOTExcBreaksAmount = 0.0;
	
	public $MannualOThours = 0.0; 

    public $UnAllocated = 0;

    public $SchedulingTeamId = null;

    public $SchedulingPersonID = null;

    public $DutyDate = null;

    public $StartDate = null;

    public $EndDate = null;

    public $IsHomeTeam = 1;

    public $MasterDutyId = 0;
    
    public $isActiveDuty = 1;
    
    public $dutyColorId = 0;

    public $dutyProgramId = 0;

    public $dutyBreakTime = 0;

    public $isAttention = 0;

    public $isEdited = null;

    public $isPublished = null;

    public $isRequest = null;
    
    public function __construct($data = [])
    {
        if (!empty($data)){
            return $this->fromArray($data);
        }
    }

    public function getJobs()
    {
        return $this->AllocationJobs ?? null;
    }
    
    public function addJob(AllocationJob $job)
    {
        $this->AllocationJobs[] = $job;
    }

}