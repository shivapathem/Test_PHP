<?php
/*
 * Created on Fri Dec 10 2021
 *
 * Author: Ashish Tripathi
 * Name:  Class HistoryService
 * Description: A service layer between domain and History
 *
 * Copyright (c) 2021 BBC
 */


use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../../../../function-includes/DBHelper.php";
require_once __DIR__ . "/../../../../function-includes/helpers.php";
require_once __DIR__ . "/AllocationService.php";
require_once __DIR__ . '/../../../../function-includes/masterduty_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 
 */
class HistoryService 
{

    /** @var AllocationService */
    protected $service;

    /** @var string append log to "old" or "new"  */
    protected $appendTo = 'old';

    public $sessUserNetId;
    public $sessUserId;
    public $sessUserFullName;

    public function __construct()
    {
        $this->service = new AllocationService();
        $this->sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
        $this->sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
        $this->sessUserFullName = isset($_SESSION['user']['FullName']) ? $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
    }

    public function setAppendTo($to = 'old')
    {
        $this->appendTo = $to;

        return $this;
    }

    public function getAppendTo()
    {
        return $this->appendTo;
    }

    /**
     * This function is used to log the history for Edit Duty
     *
     * @param $beforeUpdateData This param contains the before update data of the duty
     * @param $updatedDutyData This param contains the updated data of the duty
     *
     * @return boolean
     */
    public function insertDutyLogHistory(Allocation $beforeUpdateData, Allocation $updatedDutyData, $teamId = 0){
        $historyLog = '';
        $historyLog .= $this->dutyMarkOvertimeHistory($beforeUpdateData, $updatedDutyData);
        
        if($historyLog != ''){
            $attributeId = $this->getAppendTo() == 'old' ? $beforeUpdateData->ID : $updatedDutyData->ID;
            $request = $request ?? Request::createFromGlobals();
            $request->request->set('attributeId', $attributeId);
            $request->request->set('historyType',8);
            $request->request->set('userId',$this->sessUserId);
            $request->request->set('message',$historyLog);
            $request->request->set('historySubType', 'DH');
            return $this->service->addAllocationHistory($request);
        }
        return true;
    }

    public function schedulingPersonHistory(Allocation $old, Allocation $new){
        $logText = '';
        if($old->SchedulingPersonID != $new->SchedulingPersonID){
            $repository = new AllocationRepository();
            
            if($new->SchedulingPersonID != 0 || !is_null($new->SchedulingPersonID)){
                $dataval['schedulingPersonId'] = $new->SchedulingPersonID;
                $personDetails = $repository->getSchedulePersondetailsByID(new Request($dataval));

                $logText = sprintf(
                    "Duty unassigned from %s by %s on %s at %s<br><br>",
                    $personDetails['DisplayName'],
                    $_SESSION['user']['DisplayName'],
                    date('jS M Y'),
                    date('H:i')
                );
            }else{ //for unasigned duty
                $dataval['schedulingPersonId'] = $old->SchedulingPersonID;
                $personDetails = $repository->getSchedulePersondetailsByID(new Request($dataval));

                $logText = sprintf(
                    "Duty assigned to %s by %s on %s at %s<br><br>",
                    $personDetails['DisplayName'],
                    $_SESSION['user']['DisplayName'],
                    date('jS M Y'),
                    date('H:i')
                );
            }
        }

        return $logText;
    }

    public function dutyNameChangeHistory(Allocation $old, Allocation $new){
        $logText = '';
        if($old->DutyName != $new->DutyName){
            $logText = sprintf(
                "Duty name changed from %s to %s by %s on %s at %s<br><br>",
                $old->DutyName,
                $new->DutyName,
                $_SESSION['user']['DisplayName'],
                date('jS M Y'),
                date('H:i')
            );
        }
        return $logText;
    }

    public function startEndTimeChangeHistory(Allocation $old, Allocation $new){
        $old->StartTime = (int) $old->StartTime;
        $old->EndTime = (int) $old->EndTime;
        $new->StartTime = (int) $new->StartTime;
        $new->EndTime = (int) $new->EndTime;
        $logText = '';
        if(
            (empty($old->StartTime) && !empty($new->StartTime)) ||
            (empty($old->EndTime) && !empty($new->EndTime)) ||
            (!empty($old->StartTime) && !empty($new->StartTime) && ($old->StartTime != $new->StartTime)) ||
            (!empty($old->EndTime) && !empty($new->EndTime) && ($old->EndTime != $new->EndTime))
        ){
            $logText = sprintf(
                "Duty time changed from %s-%s to %s-%s by %s on %s at %s<br><br>",
                $this->service->secondsIntoTime(!empty($old->StartTime) ? (int) $old->StartTime : 0),
                $this->service->secondsIntoTime(!empty($old->EndTime) ? (int) $old->EndTime : 0),
                $this->service->secondsIntoTime(!empty($new->StartTime) ? (int) $new->StartTime : 0),
                $this->service->secondsIntoTime(!empty($new->EndTime) ? (int) $new->EndTime : 0),
                $_SESSION['user']['DisplayName'],
                date('jS M Y'),
                date('H:i')
            );
        }

        if(
            (!empty($old->StartTime) && empty($new->StartTime)) &&
            (!empty($old->EndTime) && empty($new->EndTime))
        ){
            $logText = sprintf(
                "Duty time changed from %s-%s to Duration %s by %s on %s at %s<br><br>",
                $this->service->secondsIntoTime(!empty($old->StartTime) ? (int) $old->StartTime : 0),
                $this->service->secondsIntoTime(!empty($old->EndTime) ? (int) $old->EndTime : 0),
                $this->service->secondsIntoTime($new->Duration),
                $_SESSION['user']['DisplayName'],
                date('jS M Y'),
                date('H:i')
            );
        }
        return $logText;
    }

    public function dutyColourChangeHistory(Allocation $old, Allocation $new, $teamId=0){
        $logText = '';
        if(($old->dutyColorId != $new->dutyColorId) && !empty($old->dutyColorId)){
            $rsColours = GetDutyColourList($teamId);
            $rsColoursArray = json_decode($rsColours,true);
            $beforeColourName = '';
            $nowColourName = '';
            if(!empty($rsColoursArray)){
                foreach($rsColoursArray as $colourK => $colourV){
                    if($colourV["MasterDutyColourID"] == $old->dutyColorId) {
                        $beforeColourName = $colourV["ColourName"];
                    }
                    if($colourV["MasterDutyColourID"] == $new->dutyColorId) {
                        $nowColourName = $colourV["ColourName"];
                    }
                }
            }
            $logText = sprintf(
                "Duty colour changed from %s to %s by %s on %s at %s<br><br>",
                $beforeColourName,
                $nowColourName,
                $_SESSION['user']['DisplayName'],
                date('jS M Y'),
                date('H:i')
            );
        }
        return $logText;
    }

    public function dutyProgramChangeHistory(Allocation $old, Allocation $new){
        $logText = '';
        if(($old->dutyProgramId != $new->dutyProgramId) && !empty($old->dutyProgramId)){
            $prog = getAllLabels();
            $allProg = json_decode($prog);
            $beforeProgName = '';
            $nowProgName = '';
            if(!empty($allProg)){
                foreach($allProg as $progK => $progV){
                    if($progV->ID == $old->dutyProgramId) {
                        $beforeProgName = $progV->Programme;
                    }
                    if($progV->ID == $new->dutyProgramId) {
                        $nowProgName = $progV->Programme;
                    }
                }
            }
            $logText = sprintf(
                "Duty label changed from %s to %s by %s on %s at %s<br><br>",
                $beforeProgName,
                $nowProgName,
                $_SESSION['user']['DisplayName'],
                date('jS M Y'),
                date('H:i')
            );
        }
        return $logText;
    }

    public function dutyBreakTimeChangeHistory(Allocation $old, Allocation $new){
        $logText = '';
        if($old->dutyBreakTime != $new->dutyBreakTime){
            $logText = sprintf(
                "Duty Break Time changed from %s to %s by %s on %s at %s<br><br>",
                $this->service->secondsIntoTime($old->dutyBreakTime),
                $this->service->secondsIntoTime($new->dutyBreakTime),
                $_SESSION['user']['DisplayName'],
                date('jS M Y'),
                date('H:i')
            );
        }
        return $logText;
    }
	
	public function dutyMarkOvertimeHistory(Allocation $old, Allocation $new){
        $logText = '';
        $dateStringInUtc = date('Y-m-d H:i');
        $date = new DateTime($dateStringInUtc, new DateTimeZone('BST'));
        $date->setTimezone(new DateTimeZone('Europe/London'));
        if($old->MarkedOvertime != $new->MarkedOvertime){
            if ($new->MarkedOvertime == 0) {
                $logText = sprintf(
                    "Manual Overtime deleted on %s by %s<br>",
                    $date->format('d/m/Y H:i'),
                    $this->sessUserFullName
                );
            } else {
                $logText = sprintf(
                    "Manual Overtime of %s hours added on %s by %s<br>",
					number_format((float)($new->MannualOThours / 3600), 2, '.', ''),
                    $date->format('d/m/Y H:i'),
                    $this->sessUserFullName
                );
            }
        } else {
			$logText = sprintf(
                "Manual Overtime hours changed from  %s to  %s on  %s by  %s.<br>",
				number_format((float)($old->MannualOThours / 3600), 2, '.', ''), 
				number_format((float)($new->MannualOThours / 3600), 2, '.', ''), 
				$date->format('d/m/Y H:i'),
                $this->sessUserFullName
            );
		}	
        return $logText;
    }

    public function markAttentionHistory($request){
        $request = $request ?? Request::createFromGlobals();
        $nameStr = !empty($_SESSION['user']['DisplayName']) ? $_SESSION['user']['DisplayName'] : $this->sessUserFullName;
        $dateStringInUtc = date('Y-m-d H:i');
        $date = new DateTime($dateStringInUtc, new DateTimeZone('BST'));
        $date->setTimezone(new DateTimeZone('Europe/London'));
        if($request->get('dutyAttention') == 0) {
            $attMsg = 'Marked for Attention on '.$date->format('d/m/Y H:i').' by '. $nameStr;
        } else {
            $attMsg = 'Unmarked for Attention on '.$date->format('d/m/Y H:i').' by '. $nameStr;
        }
        
        $request->request->set('attributeId', $request->get('ID'));
        $request->request->set('historyType',8);
        $request->request->set('userId',$this->sessUserId);
        $request->request->set('message',$attMsg);
        $request->request->set('historySubType','DH');
        $addhistory = $this->service->addAllocationHistory($request);
        
        if($addhistory) {
            return true;
        } else {
            return false;
        }
    }

    public function markRequestHistory($request){
        $request = $request ?? Request::createFromGlobals();
        $nameStr = !empty($_SESSION['user']['DisplayName']) ? $_SESSION['user']['DisplayName'] : $this->sessUserFullName;
        $dateStringInUtc = date('Y-m-d H:i');
        $date = new DateTime($dateStringInUtc, new DateTimeZone('BST'));
        $date->setTimezone(new DateTimeZone('Europe/London'));
        if($request->get('dutyRequest') == 1) {
            $reqMsg = 'Marked as Purple Font on '.$date->format('d/m/Y H:i').' by '. $nameStr;
        } else {
            $reqMsg = 'Unmarked Purple Font on '.$date->format('d/m/Y H:i').' by '. $nameStr;
        }
        
        $request->request->set('attributeId', $request->get('ID'));
        $request->request->set('historyType',8);
        $request->request->set('userId',$this->sessUserId);
        $request->request->set('message',$reqMsg);
        $request->request->set('historySubType', 'DH');
        $addReqHistory = $this->service->addAllocationHistory($request);
        
        if($addReqHistory) {
            return true;
        } else {
            return false;
        }
    }
}
