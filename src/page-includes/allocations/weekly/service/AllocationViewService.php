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
require_once __DIR__ . "/AllocationViewRepository.php";

/**
 * 
 */
class AllocationViewService 
{

    /** @var AllocationViewRepository $repository */
    protected $repository;

    public $isactive = 1;

    public function __construct()
    {
        $this->repository = new AllocationViewRepository();
        $this->isactive = 1;
    }
 
    public function getPublishMasterRotasByTeam($schedulingTeamid,$currentWeek,$intEndWeek,$intMaskAfterUnixDate, $intNoMask,$intMaskType,$intColourWeek,$filterStrSchPerson,$filterStr2)
    {
       return  $this->repository->readRotaService($schedulingTeamid,$currentWeek,$intEndWeek,$intMaskAfterUnixDate, $intNoMask,$intMaskType,$intColourWeek,$filterStrSchPerson,$filterStr2);
      
    }
    public function getScheduledPersonByTeam($schedulingTeamid,$currentWeek,$intEndWeek,$filterStrSchPerson,$filterStr2, $selSchPersonId='')
    {
        return  $this->repository->readScheduledPersonByTeam($schedulingTeamid,$currentWeek,$intEndWeek,$filterStrSchPerson,$filterStr2, $selSchPersonId);
    }

     /**
     * checks if there are records between given dates
     *
     * @param Request $request
     * @return boolean
     */
    public function checkWeekPublishStatus($intTeamID,$intWeekNumber)
    {
        return $this->repository->checkkWeekPublishStatus($intTeamID,$intWeekNumber);
       
    }
	
    

}
