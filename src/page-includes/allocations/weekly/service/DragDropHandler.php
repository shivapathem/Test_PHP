<?php
/*
 * Created on Thu Oct 28 2021
 *
 * Author: Cagri S. Kirbiyik
 * Name: Class DragDropHandler
 * Description: A handler for drag and drop actions for allocations
 *
 * Copyright (c) 2021 BBC
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}


use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;

require_once __DIR__ . "/AllocationRepository.php";

class DragDropHandler 
{
    /** @var AllocationRepository $repository */
    protected $repository;

    /** @var Allocation $repository */
    protected $source;

    /** @var Allocation $repository */

    protected $target;

    protected $errors = [];

    protected $sourceType = '';

    public $spErrorMessage = '';

    public $spStatus = 0;


    public function __construct()
    {
        $this->repository = new AllocationRepository();
    }

    /**
     * source setter
     *
     * @param Allocation $allocation
     * @return self
     */
    public function setSource(Allocation $allocation)
    {
        $this->source = $allocation;

        return $this;
    }

    /**
     * target setter
     *
     * @param Allocation $allocation
     * @return self
     */
    public function setTarget(Allocation $allocation)
    {
        $this->target = $allocation;

        return $this;
    }

    /**
     * returns errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Sets the source type
     *
     * @param [type] $type
     * @return void
     */
    public function setSourceType($type = null)
    {
        $this->sourceType = $type;

        return $this;
    }

     /**
     * Handle the drag drop action
     *
     * @throws Exception
     * @return boolean|array
     */
    public function handle($source, $target)
    {
        if(empty($source) || empty($target)){
            $this->errors[] = 'Set source and target duties.';
            throw new Exception();
        }

        if(($source['SchedulingPersonID'] > 0) && ($target['SchedulingPersonID'] > 0) && ($source['dataSource'] != 'misc')){
            $this->swap($source['ID'], $target['ID']);
        }

        if((empty($source['SchedulingPersonID'])) && ($source['dataSource'] != 'misc') && (!empty($target['ID']))){
            $this->unAllocToAlloc($source['ID'], $target['ID']);
        }

        if(($source['SchedulingPersonID'] > 0) && empty($target['SchedulingPersonID']) && ($source['dataSource'] != 'misc')){
            $this->allocToUnalloc($source['ID']);
        }

        if($source['dataSource'] == 'misc'){
            $this->setMiscduty($source['MasterDutyId'], $target['ID']);
        }
        return $this;
    }

    /**
     * Swap action for allcoations. It changes the source and target allocation scheduleperson
     *
     * @param Allocation $source
     * @param Allocation $target
     * @return void
     */
    private function swap($sourceId, $targetId)
    {
        $spResult = $this->repository->swapAllocatedDuty($sourceId, $targetId);
        $this->spStatus = $spResult['spStatus'];
        $this->spErrorMessage = $spResult['errorMessage'];
        return $this;
    }

    /**
     * unAllocToAlloc action for allcoations. It changes the source and target allocation scheduleperson
     *
     * @param Allocation $source
     * @param Allocation $target
     * @return void
     */
    private function unAllocToAlloc($sourceId, $targetId)
    {
        $spResult = $this->repository->unAllocatedToAllocated($sourceId, $targetId);
        $this->spStatus = $spResult['spStatus'];
        $this->spErrorMessage = $spResult['errorMessage'];
        return $this;
    }

     /**
     * allocToUnalloc action for allcoations. It changes the source and target allocation scheduleperson
     *
     * @param Allocation $source
     * @param Allocation $target
     * @return void
     */

    private function allocToUnalloc($sourceId)
    {
        $spResult = $this->repository->allocatedToUnallocated($sourceId);
        $this->spStatus = $spResult['spStatus'];
        $this->spErrorMessage = $spResult['errorMessage'];
        return $this;
    }

    /**
     * allocToUnalloc action for allcoations. It changes the source and target allocation scheduleperson
     *
     * @param Allocation $source
     * @param Allocation $target
     * @return void
     */

    private function setMiscduty($sourceDutyId, $targetId)
    {
        $spResult = $this->repository->setMiscDutyInAllocation($sourceDutyId,$targetId);
        $this->spStatus = $spResult['spStatus'];
        $this->spErrorMessage = $spResult['errorMessage'];
        return $this;
    }

    /**
     * This function is used to swap data in the variable and return that update data
     *
     * @param Allocation $source
     * @param Allocation $target
     * @return void
     */
    private function getSwapData($isEdited, $schedulingPersonId, $allocData, $allocEditData)
    {
        $swData = [];
        $swData['DutyName'] = ($isEdited == 1) ? $allocEditData['DutyName'] : $allocData['DutyName'];
        $swData['Duration'] = ($isEdited == 1) ? $allocEditData['Duration'] : $allocData['Duration'];
        $swData['iDay'] = ($isEdited == 1) ? $allocEditData['iDay'] : $allocData['iDay'];
        $swData['StartTime'] = ($isEdited == 1) ? $allocEditData['StartTime'] : $allocData['StartTime'];
        $swData['EndTime'] = ($isEdited == 1) ? $allocEditData['EndTime'] : $allocData['EndTime'];
        $swData['DutyComments'] = ($isEdited == 1) ? $allocEditData['DutyComments'] : $allocData['DutyComments'];
        $swData['AdhocDuty'] = ($isEdited == 1) ? $allocEditData['AdhocDuty'] : $allocData['AdhocDuty'];
        $swData['MarkedOvertime'] = ($isEdited == 1) ? $allocEditData['MarkedOvertime'] : $allocData['MarkedOvertime'];
        $swData['MarkedSickness'] = ($isEdited == 1) ? $allocEditData['MarkedSickness'] : $allocData['MarkedSickness'];
        $swData['SchedulingPersonID'] = $schedulingPersonId;
        $swData['DutyDate'] = ($isEdited == 1) ? $allocEditData['DutyDate'] : $allocData['DutyDate'];
        $swData['StartDate'] = ($isEdited == 1) ? $allocEditData['StartDate'] : $allocData['StartDate'];
        $swData['EndDate'] = ($isEdited == 1) ? $allocEditData['EndDate'] : $allocData['EndDate'];
        $swData['MasterDutyId'] = ($isEdited == 1) ? $allocEditData['MasterDutyId'] : $allocData['MasterDutyId'];
        $swData['dutyColorId'] = ($isEdited == 1) ? $allocEditData['dutyColorId'] : $allocData['dutyColorId'];
        $swData['dutyProgramId'] = ($isEdited == 1) ? $allocEditData['dutyProgramId'] : $allocData['dutyProgramId'];
        $swData['dutyBreakTime'] = ($isEdited == 1) ? $allocEditData['dutyBreakTime'] : $allocData['dutyBreakTime'];
        $swData['isAttention'] = ($isEdited == 1) ? $allocEditData['IsAttention'] : $allocData['isAttention'];
        $swData['isRequest'] = ($isEdited == 1) ? $allocEditData['isRequest'] : $allocData['isRequest'];
                
        return (object) $swData;
    }
}