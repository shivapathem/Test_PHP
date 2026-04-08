<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/AllocationRepository.php';
require_once __DIR__ .'/../../../../function-includes/DB_Functions.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
$repository = new AllocationRepository();
$actionName = $request->get('action');

if($actionName == 'checkDutyName'){
	$intAreaID = $_SESSION['user']['AreaID']??0;
	$intDutyTypeID = 0;
	$intArchived = 0;
	$strTeams = $request->get('teamId');
	$strsearch = '';

	$getMiscDutyList = ListAllMasterDuties($intAreaID, $intDutyTypeID, $intArchived, $strTeams, $strsearch);
	$getMiscDutyList = json_decode($getMiscDutyList,true);
	$returnData['status'] = false;

	if(!empty($getMiscDutyList)){
		$dutyDuration = '00:00';
		$dutyColourId = 0;
		$dutyBreakTimeHr = 0;
		$dutyBreakTimeMin = 0;
		$miscDutyName = '';
		foreach($getMiscDutyList as $miscK => $miscV){
			if((isset($miscV['DutyName'])) && strtolower($miscV['DutyName']) == strtolower($request->get('dutyName'))){
				$returnData['status'] = true;
				$dutyDuration = $service->secondsIntoTime($miscV['Duration']);
				$dutyColourId = $miscV['DutyColourID'];
				$dutyBreakTime = $service->secondsIntoTime($miscV['BreakTime']);
				$dutyBreakTimeHr = (int) explode(':',$dutyBreakTime)[0];
				$dutyBreakTimeMin = (int) explode(':',$dutyBreakTime)[1];
				$miscDutyName = $miscV['DutyName'];
				break;
			}
		}
		if (substr($request->get('dutyName'), 0, 1)=="-"){
			$returnData['status'] = true;
			$returnData['duration'] = '00:00';
			$returnData['dutyColourId'] = 0;
			$returnData['dutyBreakTimeHr'] = 0;
			$returnData['dutyBreakTimeMin'] = 0;
			$returnData['miscDutyName'] = $request->get('dutyName');
		} else if(substr($request->get('dutyName'), 0, 2)=="--"){
			$returnData['status'] = true;
			$returnData['duration'] = '00:00';
			$returnData['dutyColourId'] = 0;
			$returnData['dutyBreakTimeHr'] = 0;
			$returnData['dutyBreakTimeMin'] = 0;
			$returnData['miscDutyName'] = $request->get('dutyName');
		} else{
			$returnData['duration'] = $dutyDuration;
			$returnData['dutyColourId'] = $dutyColourId;
			$returnData['dutyBreakTimeHr'] = $dutyBreakTimeHr;
			$returnData['dutyBreakTimeMin'] = $dutyBreakTimeMin;
			$returnData['miscDutyName'] = $miscDutyName;
		}
	}
	echo json_encode($returnData);
}

if($actionName == 'calculateduration'){
	$startTimeInSec = $service->timeIntoSeconds($request->get('startTime'));
	$endTimeInSec = $service->timeIntoSeconds($request->get('endTime'));
	$breakHr = $request->get('breakTimeHr');
	if($request->get('breakTimeHr') < 10){
		$breakHr = '0'.$request->get('breakTimeHr');
	}
	$breakMin = $request->get('breakTimeMin');
	if($request->get('breakTimeMin') < 10){
		$breakMin = '0'.$request->get('breakTimeMin');
	}
	$breakTimeInSec = $service->timeIntoSeconds($breakHr.':'.$breakMin);
	if ($request->get('startTime')== '--:--') {
		$returnData['status'] = false;
	} else {
		if($startTimeInSec > $endTimeInSec){
			$seconds24Hrs = $service->timeIntoSeconds('24:00');
			$todayRemainHrsInSeconds = ($seconds24Hrs - $startTimeInSec);
			$totalFinalSeconds = ($todayRemainHrsInSeconds + $endTimeInSec);
			if($totalFinalSeconds < $breakTimeInSec){
				$returnData['status'] = false;
				$returnData['mess'] = "Break Time should not greater than duration.";
			} else {
				$finalDutyHrsInSec = ($totalFinalSeconds - $breakTimeInSec);
				$returnData['status'] = true;
				$returnData['duration'] = $service->secondsIntoTime($finalDutyHrsInSec);
			}
		} else {
			if(($startTimeInSec == $endTimeInSec) && (($startTimeInSec != '0') && ($endTimeInSec != '0'))){
				$seconds24Hrs = $service->timeIntoSeconds('24:00');
				$finalDutyHrsInSec = ($seconds24Hrs - $breakTimeInSec);
				$returnData['status'] = true;
				$returnData['duration'] = $service->secondsIntoTime($finalDutyHrsInSec);
				if($finalDutyHrsInSec == 86400){
					$returnData['duration'] = '24:00';
				}
			} else {
				if(($startTimeInSec == $endTimeInSec) && (($startTimeInSec == '0') && ($endTimeInSec == '0'))){
					$seconds24Hrs = $service->timeIntoSeconds('24:00');
					$finalDutyHrsInSec = ($seconds24Hrs - $breakTimeInSec);
					$returnData['status'] = true;
					$returnData['duration'] = $service->secondsIntoTime($finalDutyHrsInSec);
					if($finalDutyHrsInSec == 86400){
						$returnData['duration'] = '24:00';
					}
				} else {
					$finalDurationInSec = ($endTimeInSec - $startTimeInSec);
					if($finalDurationInSec < $breakTimeInSec){
						$returnData['status'] = false;
						$returnData['mess'] = "Break Time should not greater than duration.";
					} else {
						$duration = ($finalDurationInSec - $breakTimeInSec);
						$returnData['status'] = true;
						$returnData['duration'] = $service->secondsIntoTime($duration);
					}
				}
			}
		}
	}
	echo json_encode($returnData);
}

if($actionName == 'searchMiscDuty'){
	$intAreaID = $_SESSION['user']['AreaID']??0;
	$intDutyTypeID = 0;
	$intArchived = 0;
	$strTeams = $request->get('teamId');
	$strsearch = $request->get('searchStr');

	$getAllMiscDutyList = ListAllMasterDuties($intAreaID, $intDutyTypeID, $intArchived, $strTeams, '');
	$getAllMiscDutyList = json_decode($getAllMiscDutyList,true);

	$returnData['status'] = false;
	if($strsearch != ''){
		$getSearchMiscDutyList = ListAllMasterDuties($intAreaID, $intDutyTypeID, $intArchived, $strTeams, $strsearch);
		$getSearchMiscDutyList = json_decode($getSearchMiscDutyList,true);

		if(!empty($getSearchMiscDutyList)){
			$searchDutyIds = [];
			foreach($getSearchMiscDutyList as $searchDutyVal){
				$searchDutyIds[] = $searchDutyVal['MasterDutyID'];
			}
		}

		if(!empty($getAllMiscDutyList)){
			$returnData['status'] = true;
			$hideDutyIds = [];
			foreach($getAllMiscDutyList as $allDutyVal){
				if(!empty($searchDutyIds)){
					if(!in_array($allDutyVal['MasterDutyID'],$searchDutyIds)){
						$hideDutyIds[] = $allDutyVal['MasterDutyID'];
					}
				} else {
					$hideDutyIds[] = $allDutyVal['MasterDutyID'];
				}
			}
			$returnData['dataHide'] = $hideDutyIds;
			$returnData['dataShow'] = $searchDutyIds;
		}
	} else {
		if(!empty($getAllMiscDutyList)){
			$returnData['status'] = true;
			$showDutyIds = [];
			foreach($getAllMiscDutyList as $allDutyVal){
				$showDutyIds[] = $allDutyVal['MasterDutyID'];
			}
			$returnData['data'] = $showDutyIds;
		}
	}
	echo json_encode($returnData);
}

if($actionName == 'updatecalculateddurationbyhr'){
	$breakHr = $request->get('breakHr');
	if($request->get('breakHr') < 10){
		$breakHr = '0'.$request->get('breakHr');
	}
	$breakHrInSec = $service->timeIntoSeconds($breakHr.':00');
	$durationInSec = $service->timeIntoSeconds($request->get('Duration'));
	if($breakHrInSec > $durationInSec){
		$returnData['status'] = false;
		$returnData['mess'] = 'Break Time will not greater then Duty Duration';
	} else {
		$duration = ($durationInSec - $breakHrInSec);
		$returnData['status'] = true;
		$returnData['duration'] = $service->secondsIntoTime($duration);
	}
	echo json_encode($returnData);
}

if($actionName == 'updatecalculateddurationbymin'){
	$breakMin = $request->get('breakMin');
	if($request->get('breakMin') < 10){
		$breakMin = '0'.$request->get('breakMin');
	}
	$breakMinInSec = $service->timeIntoSeconds('00:'.$breakMin);
	$durationInSec = $service->timeIntoSeconds($request->get('Duration'));
	if($breakMinInSec > $durationInSec){
		$returnData['status'] = false;
		$returnData['mess'] = 'Break Time will not greater then Duty Duration';
	} else {
		$duration = ($durationInSec - $breakMinInSec);
		$returnData['status'] = true;
		$returnData['duration'] = $service->secondsIntoTime($duration);
	}
	echo json_encode($returnData);
}

if($actionName == 'updatecalculateddurationbyhrmin'){
	$breakHr = $request->get('breakHr');
	if($request->get('breakHr') < 10){
		$breakHr = '0'.$request->get('breakHr');
	}
	$breakMin = $request->get('breakMin');
	if($request->get('breakMin') < 10){
		$breakMin = '0'.$request->get('breakMin');
	}
	$breakTimeInSec = $service->timeIntoSeconds($breakHr.':'.$breakMin);
	$durationInSec = $service->timeIntoSeconds($request->get('Duration'));
	if($breakTimeInSec > $durationInSec){
		$returnData['status'] = false;
		$returnData['mess'] = 'Break Time will not greater then Duty Duration';
	} else {
		$duration = ($durationInSec - $breakTimeInSec);
		$returnData['status'] = true;
		$returnData['duration'] = $service->secondsIntoTime($duration);
	}
	echo json_encode($returnData);
}

if($actionName == 'updatecalculatedduration'){
	$breakHr = $request->get('breakHr');
	if($request->get('breakHr') < 10){
		$breakHr = '0'.$request->get('breakHr');
	}
	$breakMin = $request->get('breakMin');
	if($request->get('breakMin') < 10){
		$breakMin = '0'.$request->get('breakMin');
	}

	if($request->get('startTime') != ''){
		$startTimeInSec = $service->timeIntoSeconds($request->get('startTime'));
		$endTimeInSec = $service->timeIntoSeconds($request->get('endTime'));
		$breakTimeInSec = $service->timeIntoSeconds($breakHr.':'.$breakMin);
		if($startTimeInSec == $endTimeInSec){
			$durationInSec = 86400;
		} else {
			if($endTimeInSec == 0){
				$durationInSec = 86400 - $startTimeInSec;
			} else {
				$durationInSec = $endTimeInSec - $startTimeInSec;
			}
		}
	} else {
		$allocationDetailsById = $service->getAllocationByID($request->get('id'));
		$breakTimeInSec = $service->timeIntoSeconds($breakHr.':'.$breakMin);
		if($allocationDetailsById['DutyName'] == $request->get('DutyName')){
			if($allocationDetailsById['StartTime'] >= 0 && $allocationDetailsById['EndTime'] > 0){
				$durationInSec = (int) ($allocationDetailsById['EndTime'] - $allocationDetailsById['StartTime']);
			} else {
				if($allocationDetailsById['DutyName'] != 'U'){
					$durationInSec = (int) $allocationDetailsById['Duration'];
				} else {
					$durationInSec = $service->timeIntoSeconds($request->get('Duration'));
				}
			}
		} else {
			$durationInSec = $service->timeIntoSeconds($request->get('Duration'));
		}
	}

	if($breakTimeInSec > $durationInSec){
		$returnData['status'] = false;
		$returnData['mess'] = 'Break Time will not greater then Duty Duration';
	} else {
		$duration = ($durationInSec - $breakTimeInSec);
		$returnData['status'] = true;
		$returnData['duration'] = $service->secondsIntoTime($duration);
	}
	echo json_encode($returnData);
}

if($actionName == 'getallocationrowdetail'){
	$allocationDetailsById = $service->getAllocationByID($request->get('id'));
	$allocationDetailsById = is_array($allocationDetailsById ?? false) ? $allocationDetailsById : [];
	$allocationDetailsById['dutyBreakTime'] = isset($allocationDetailsById['dutyBreakTime']) ? $allocationDetailsById['dutyBreakTime'] : 0;
	$allocationDetailsById['StartTime'] = isset($allocationDetailsById['StartTime']) ? $allocationDetailsById['StartTime'] : 0;
	$allocationDetailsById['EndTime'] = isset($allocationDetailsById['EndTime']) ? $allocationDetailsById['EndTime'] : 0;
	$allocationDetailsById['Duration'] = isset($allocationDetailsById['Duration']) ? $allocationDetailsById['Duration'] : 0;

	$rowBreakTime = $service->secondsIntoTime($allocationDetailsById['dutyBreakTime']);
	$explodeBreakTime = explode(':',$rowBreakTime);
	$allocationDetailsById['rowBreakTimeHr'] = (int) $explodeBreakTime[0];
	$allocationDetailsById['rowBreakTimeMin'] = (int) $explodeBreakTime[1];

	$rowStartTime = $service->secondsIntoTime($allocationDetailsById['StartTime']);
	$explodeStartTime = explode(':',$rowStartTime);
	$allocationDetailsById['sTTime'] = $explodeStartTime[0].':'.$explodeStartTime[1];

	$rowEndTime = $service->secondsIntoTime($allocationDetailsById['EndTime']);
	$explodeEndTime = explode(':',$rowEndTime);
	$allocationDetailsById['eNTime'] = $explodeEndTime[0].':'.$explodeEndTime[1];

	$rowDuration = $service->secondsIntoTime($allocationDetailsById['Duration']);
	$explodeDuration = explode(':',$rowDuration);
	$allocationDetailsById['rDuration'] = $explodeDuration[0].':'.$explodeDuration[1];

	$returnData['status'] = true;
	$returnData['data'] = $allocationDetailsById;
	echo json_encode($returnData);
}

if($actionName == 'calculatedurationmisc'){
	$durationInSec = (int) $request->get('Duration');
	$breakHr = $request->get('breakTimeHr');
	if($request->get('breakTimeHr') < 10){
		$breakHr = '0'.$request->get('breakTimeHr');
	}
	$breakMin = $request->get('breakTimeMin');
	if($request->get('breakTimeMin') < 10){
		$breakMin = '0'.$request->get('breakTimeMin');
	}
	$breakTimeInSec = $service->timeIntoSeconds($breakHr.':'.$breakMin);
	if($breakTimeInSec > $durationInSec){
		$returnData['status'] = false;
		$returnData['mess'] = "Break Time should not greater than duration.";
	} else {
		$finalDurationInSec = ($durationInSec - $breakTimeInSec);
		$returnData['status'] = true;
		$returnData['duration'] = $service->secondsIntoTime($finalDurationInSec);
	}
	echo json_encode($returnData);
}