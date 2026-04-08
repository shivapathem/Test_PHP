<?php
/*
 * Created on Thu Oct 28 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Class AllocationJob
 * Description: A value object that mapped to allocations_jobs table
 *
 * Copyright (c) 2021 BBC
 */

include_once __DIR__ . "/ArrayableTrait.php";

use Carbon\Carbon;

class AllocationJob
{

    use ArrayableTrait;

    public $ID;

    public $AllocateInstanceID = 0;

    /** @deprecated */
    public $DepartmentID = 0;

    public $AllocationID = 0;

    public $AllocateJobID = 0;

    public $StaffNumber = null;

    public $JobName = null;

    public $WeekNumber;

    public $iDay = 0;

    public $Programme = null;

    public $Contact = null;

    public $Location = null;

    public $StartTime = null;

    public $EndTime = null;

    public $JobBackColour = null;

    public $JobFontColour = null;

    public $Comments = null;

    public $MasterJobID;

    public $AdhocDuty = 0;

    public $UnAllocated;

    public $SchedulingTeamId = null;

    public $SchedulingPersonID = null;

    public $isActive = 1;

    public $Edited;

    public $isEdited;

    public $isPublished;

    public $ProgrammeID;

    public $Job_Info;

    public $aftermidnight;

    public function __construct($data = [])
    {
        if (!empty($data)){
            return $this->fromArray($data);
        }
    }

}    
