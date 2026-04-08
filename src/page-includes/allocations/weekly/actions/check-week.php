<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ .'/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';
include_once __DIR__ ."/../../../../function-includes/genericfunctions.php";
$request = Request::createFromGlobals();
$service = new AllocationService();
$action = $request->get('actionname');

if($action == 'checkweekexists'){
	$explodeWeekNumber = explode('/',$request->get('weeknumber'));
	$weekNumber = $request->get('weeknumber');
	$inpWeek = (!empty($explodeWeekNumber) && isset($explodeWeekNumber[0])) ? $explodeWeekNumber[0] : '';
	$inpYear = (!empty($explodeWeekNumber) && isset($explodeWeekNumber[1])) ? $explodeWeekNumber[1] : '';
	if($inpYear == ''){
		$inpWeek = str_pad($inpWeek,2,"0",STR_PAD_LEFT);
		$inpYear = date('Y');
		$weekNumber = $inpWeek.'/'.$inpYear;
	}
	if((isset($inpYear)) && (strlen($inpYear) == 2) && (!empty($inpYear))){
		$inpWeek = str_pad($inpWeek,2,"0",STR_PAD_LEFT);
		$inpYear = '20'.$inpYear;
		$weekNumber = $inpWeek.'/'.$inpYear;
	}
	$qWeekNum = $inpYear.$inpWeek;
	$request->request->set('checkweeknumber',$qWeekNum);
	$checkWeekExistsForTeam = $service->checkWeekExists($request);
	$response['success'] = false;
	$timeDimensionService = new TimeDimensionService();

	$teamId = ($request->get('schedulingTeamId') != '') ?  $request->get('schedulingTeamId') : 0;
	$arrStaffOptions = $service->getRolePermissionEditWeekly($teamId);
	if (($arrStaffOptions['isTeamAdmin']==1)){
		$backdate7yr =  date('Y-m-d',strtotime('-2 year', strtotime(date('Y-m-d'))));
	} else {
		$backdate7yr =  date('Y-m-d',strtotime('-3 month', strtotime(date('Y-m-d'))));
	}
	$backdate7yralternate = date('Y-m-d',strtotime(getenv('ACCESS_DATE')));

	if($backdate7yralternate>$backdate7yr){
		$validDateNo =$backdate7yralternate;
	} else {
		$validDateNo =$backdate7yr;
	}

	$validDate= date('jS F Y',strtotime($validDateNo));
	$getWeekStartDate = $timeDimensionService->getWeekStartDate($weekNumber);
	$weekDate = isset($getWeekStartDate->dDateTime) ? $getWeekStartDate->dDateTime : '';

	$backDateWeekNum = '';
	if($backdate7yr != ''){
		$getBackDateWeek = $timeDimensionService->findByDateWithoutCarbon($backdate7yr);
		$backDateWeekNum = isset($getBackDateWeek->ixYearWeek) ? (int) $getBackDateWeek->ixYearWeek : '';
	}

	$weekStartDateWeekNum = '';
	if($weekDate != ''){
		$getWeekStartDateWeek = $timeDimensionService->findByDateWithoutCarbon($weekDate);
		$weekStartDateWeekNum = isset($getWeekStartDateWeek->ixYearWeek) ? (int) $getWeekStartDateWeek->ixYearWeek : '';
	}

	if(($validDateNo > date('Y-m-d',strtotime($weekDate))) && ($weekDate != '')){
		if($backdate7yralternate > date('Y-m-d',strtotime($weekDate))){
			$response['errormessage']="Allocate cannot open dates before ".date('jS F Y',strtotime($backdate7yralternate))." in the Edit Weekly Allocations view.";
			$response['openWeek']="No";
		} else {
			if($weekStartDateWeekNum < $backDateWeekNum){
				$response['errormessage']="You do not have permissions to edit this week, but you can view it.";
				$response['openWeek']="Yes";
			}
		}
		if($request->get('oldPrevWeek') != ''){
			$explodeOldPrevWeekNumber = explode('/',$request->get('oldPrevWeek'));
			$inpOldPrevWeek = (!empty($explodeOldPrevWeekNumber) && isset($explodeOldPrevWeekNumber[0])) ? $explodeOldPrevWeekNumber[0] : '';
			$inpOldPrevYear = (!empty($explodeOldPrevWeekNumber) && isset($explodeOldPrevWeekNumber[1])) ? $explodeOldPrevWeekNumber[1] : '';
			$nextWeek = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($inpOldPrevYear.$inpOldPrevWeek);
			$response['cookieSetWeek'] = substr(strval($nextWeek->ixYearWeek),4,2).'/'.substr(strval($nextWeek->ixYearWeek),0,4);
		}
	}
	if(!empty($checkWeekExistsForTeam)){
		$weekStartDate = $timeDimensionService->getWeekStartDate($weekNumber)->dDateTime;
		$weekEndDate = $timeDimensionService->getWeekEndDate($weekNumber)->dDateTime;
	    $response['success'] = true;
	    $response['startDate'] = date('Y-m-d',strtotime($weekStartDate));
	    $response['endDate'] = date('Y-m-d',strtotime($weekEndDate));
	} else {
		$getCurrentWeek = $timeDimensionService->findByDateWithoutCarbon(date('Y-m-d'));
		$weekYear = substr(strval($getCurrentWeek->ixYearWeek),0,4);
		$weekNum = substr(strval($getCurrentWeek->ixYearWeek),4,2);
		$response['currentweeknum'] = $weekNum.'/'.$weekYear;
		if(strlen($inpYear) == 4){
			$response['validWeek'] = $timeDimensionService->checkValidWeekOfYear($inpYear.$inpWeek);
		} else if(strlen($inpYear) == 2){
			$fullWeekNum = (int) substr(strval(date('Y')),0,2).$inpYear.$inpWeek;
			$response['validWeek'] = $timeDimensionService->checkValidWeekOfYear($fullWeekNum);
		} else {
			$response['validWeek'] = null;
		}
	}
	echo json_encode($response);
}

if($action == 'getweekforcookieweeknum'){
	$timeDimensionService = new TimeDimensionService();
	$explodeWeekNumber = explode('/',$request->get('prevweeknum'));
	$inpWeek = (!empty($explodeWeekNumber) && isset($explodeWeekNumber[0])) ? $explodeWeekNumber[0] : '';
	$inpYear = (!empty($explodeWeekNumber) && isset($explodeWeekNumber[1])) ? $explodeWeekNumber[1] : '';

	$enteredWeekNum = explode('/',$request->get('enteredWeekNum'));
	$inpWeekEnt = (!empty($enteredWeekNum) && isset($enteredWeekNum[0])) ? $enteredWeekNum[0] : '';
	$inpYearEnt = (!empty($enteredWeekNum) && isset($enteredWeekNum[1])) ? $enteredWeekNum[1] : '';

	$getImmediateNextWeekNum = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($inpYear.$inpWeek);

	$teamId = ($request->get('schedulingTeamId') != '') ?  $request->get('schedulingTeamId') : 0;
	$arrStaffOptions = $service->getRolePermissionEditWeekly($teamId);
	if (($arrStaffOptions['isTeamAdmin']==1)){
		$backdate7yr =  date('Y-m-d',strtotime('-2 year', strtotime(date('Y-m-d'))));
	} else {
		$backdate7yr =  date('Y-m-d',strtotime('-3 month', strtotime(date('Y-m-d'))));
	}
	$backdate7yralternate = date('Y-m-d',strtotime(getenv('ACCESS_DATE')));

	if($backdate7yralternate>$backdate7yr){
		$validDateNo =$backdate7yralternate;
	} else {
		$validDateNo =$backdate7yr;
	}
	$weekNumber = $inpWeekEnt.'/'.$inpYearEnt;
	$validDate= date('jS F Y',strtotime($validDateNo));
	$getWeekStartDate = $timeDimensionService->getWeekStartDate($weekNumber);
	$weekDate = isset($getWeekStartDate->dDateTime) ? $getWeekStartDate->dDateTime : '';

	$backDateWeekNum = '';
	if($backdate7yr != ''){
		$getBackDateWeek = $timeDimensionService->findByDateWithoutCarbon($backdate7yr);
		$backDateWeekNum = isset($getBackDateWeek->ixYearWeek) ? (int) $getBackDateWeek->ixYearWeek : '';
	}

	$weekStartDateWeekNum = '';
	if($weekDate != ''){
		$getWeekStartDateWeek = $timeDimensionService->findByDateWithoutCarbon($weekDate);
		$weekStartDateWeekNum = isset($getWeekStartDateWeek->ixYearWeek) ? (int) $getWeekStartDateWeek->ixYearWeek : '';
	}

	if(($validDateNo > date('Y-m-d',strtotime($weekDate))) && ($weekDate != '')){
		if($backdate7yralternate > date('Y-m-d',strtotime($weekDate))){
			$response['errormessage']="Allocate cannot open dates before ".date('jS F Y',strtotime($backdate7yralternate))." in the Edit Weekly Allocations view.";
			$response['openWeek']="No";
		} else {
			if($weekStartDateWeekNum < $backDateWeekNum){
				$response['errormessage']="You do not have permissions to edit this week, but you can view it.";
				$response['openWeek']="Yes";
			}
		}
		//$response['errormessage']="Allocate cannot open dates before ".$validDate." in the Edit Weekly Allocations view.";
	}

	$response['success'] = false;
	if(!empty($getImmediateNextWeekNum->ixYearWeek)){
		$cookieWeekYear = substr(strval($getImmediateNextWeekNum->ixYearWeek),0,4);
		$cookieWeekNum = substr(strval($getImmediateNextWeekNum->ixYearWeek),4,2);
	    $response['success'] = true;
	    $response['cookieSetWeek'] = strval($cookieWeekNum.'/'.$cookieWeekYear);
	}
	echo json_encode($response);
}

if($action == 'getprevweekforcookieweeknum'){
	$timeDimensionService = new TimeDimensionService();
	$getImmediateNextWeekNum = $service->findPrevCreatedWeek($request);
	$response['success'] = false;
	if(!empty($getImmediateNextWeekNum)){
		$cookieWeekYear = substr(strval($getImmediateNextWeekNum['WeekNumber']),0,4);
		$cookieWeekNum = substr(strval($getImmediateNextWeekNum['WeekNumber']),4,2);
	    $response['success'] = true;
	    $response['cookieSetWeek'] = strval($cookieWeekNum.'/'.$cookieWeekYear);
	}
	echo json_encode($response);
}

if($action == 'addnewpersoncreatedweek'){
	$addNewPerson = $service->createAllocationNewPerson($request);
	$response['success'] = false;
	if($addNewPerson){
		$response['success'] = true;
	}
	echo json_encode($response);
}

if($action == 'getweeks'){
	$timeDimensionService = new TimeDimensionService();
	$teamId = ($request->get('teamId') != '') ? $request->get('teamId') : 0;
	$arrStaffOptions = $service->getRolePermissionEditWeekly($teamId);
	if (($arrStaffOptions['isTeamAdmin']==1)){
		$backdate7yr =  date('Y-m-d',strtotime('-2 year', strtotime(date('Y-m-d'))));
	} else {
		$backdate7yr =  date('Y-m-d',strtotime('-3 month', strtotime(date('Y-m-d'))));
	}
	$backdate7yralternate = date('Y-m-d',strtotime(getenv('ACCESS_DATE')));
	if($backdate7yralternate>$backdate7yr){
		$validDateNo =$backdate7yralternate;
	} else {
		$validDateNo =$backdate7yr;
	}

	$validDate= date('jS F Y',strtotime($validDateNo));
	$reqDate = ($request->get('date') != '') ? date('Y-m-d',strtotime($request->get('date'))) : 0;

	if(($validDateNo > $reqDate) && ($reqDate != 0)){
		$response['errormessage']="Allocate cannot open dates before ".$validDate." in the Edit Weekly Allocations view.";
	} else {
		$weekNumber = $request->get('weekNumber');
		if(($request->get('checkValidity') != '') && ($request->get('checkValidity') == 'Yes')){
			$getWeekStartDate = $timeDimensionService->getWeekStartDate($weekNumber);
			$weekDate = $getWeekStartDate->dDateTime;

			$backDateWeekNum = '';
			if($backdate7yr != ''){
				$getBackDateWeek = $timeDimensionService->findByDateWithoutCarbon($backdate7yr);
				$backDateWeekNum = isset($getBackDateWeek->ixYearWeek) ? (int) $getBackDateWeek->ixYearWeek : '';
			}

			$weekStartDateWeekNum = '';
			if($weekDate != ''){
				$getWeekStartDateWeek = $timeDimensionService->findByDateWithoutCarbon($weekDate);
				$weekStartDateWeekNum = isset($getWeekStartDateWeek->ixYearWeek) ? (int) $getWeekStartDateWeek->ixYearWeek : '';
			}

			if(($validDateNo > date('Y-m-d',strtotime($weekDate))) && ($weekDate != '')){
				if($backdate7yralternate > date('Y-m-d',strtotime($weekDate))){
					$response['errormessage']="Allocate cannot open dates before ".date('jS F Y',strtotime($backdate7yralternate))." in the Edit Weekly Allocations view.";
					$response['openWeek']="No";
				} else {
					if($weekStartDateWeekNum < $backDateWeekNum){
						$response['errormessage']="You do not have permissions to edit this week, but you can view it.";
						$response['openWeek']="Yes";
					}
				}
				//$response['errormessage']="Allocate cannot open dates before ".$validDate." in the Edit Weekly Allocations view.";
			}
		}
		$explodeWeekNum = explode('/',$weekNumber);
		$queryWeekNumber = $explodeWeekNum[1].$explodeWeekNum[0];

		$request->request->set('checkweeknumber',$queryWeekNumber);
		$request->request->set('schedulingTeamId',$request->get('teamId'));
		$checkWeekExists = $service->checkWeekExists($request);
		if($checkWeekExists == ''){
			$response['success'] = false;
		} else {
			$prevWeekNumber = $timeDimensionService->findImmediatePrevWeekOfCurrentWeek($queryWeekNumber);
			$nextWeekNumber = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($queryWeekNumber);
			$prevWeekYear = substr(strval($prevWeekNumber->ixYearWeek),0,4);
			$prevWeekNum = substr(strval($prevWeekNumber->ixYearWeek),4,2);
			$nextWeekYear = substr(strval($nextWeekNumber->ixYearWeek),0,4);
			$nextWeekNum = substr(strval($nextWeekNumber->ixYearWeek),4,2);
			$weekStartDate = $timeDimensionService->getWeekStartDate($request->get('weekNumber'));
			$weekEndDate = $timeDimensionService->getWeekEndDate($request->get('weekNumber'));

			$request->request->set('checkweeknumber',$queryWeekNumber);
			$request->request->set('schedulingTeamId',$request->get('teamId'));
			$checkWeekExistsForTeam = $service->checkWeekExists($request);
			$response['weekExist'] = 'No';
			if(!empty($checkWeekExistsForTeam)){
				$response['weekExist'] = 'Yes';
			}

			$checkWeekPublish = $service->checkWeekPublish($request);
			$IsPublished = isset($checkWeekPublish['IsPublished']) ? $checkWeekPublish['IsPublished'] : 0;
			$response['success'] = true;
			$response['prevWeekNum'] = $prevWeekNum.'/'.$prevWeekYear;
			$response['nextWeekNum'] = $nextWeekNum.'/'.$nextWeekYear;
			$response['weekStartDate'] = date('Y-m-d',strtotime($weekStartDate->dDateTime));
			$response['weekEndDate'] = $endDate = date('Y-m-d', strtotime(date('Y-m-d',strtotime($weekEndDate->dDateTime)). ' + 1 days'));
			$response['prevWeekNumStr'] = $prevWeekNumber->ixYearWeek;
			$response['nextWeekNumStr'] = $nextWeekNumber->ixYearWeek;
			$response['currWeekNumStr'] = $explodeWeekNum[1].$explodeWeekNum[0];
			$response['weekPublish'] = (int) $IsPublished;
		}
	}
	echo json_encode($response);
}

if($action == 'getweekstartdate'){
	$weekNumber = $request->get('weekNumber');
	$explodeWeekNum = explode('/',$weekNumber);
	$queryWeekNumber = $explodeWeekNum[1].$explodeWeekNum[0];
	$timeDimensionService = new TimeDimensionService();
	$weekStartDate = $timeDimensionService->getWeekStartDate($request->get('weekNumber'));
	$response['success'] = true;
	$response['weekStartDate'] = date('Y-m-d',strtotime($weekStartDate->dDateTime));
	echo json_encode($response);
}

if($action == 'getdateweeknumber'){
	$timeDimensionService = new TimeDimensionService();
	$teamId = ($request->get('teamId') != '') ?  $request->get('teamId') : 0;
	$arrStaffOptions = $service->getRolePermissionEditWeekly($teamId);
	if (($arrStaffOptions['isTeamAdmin']==1)){
		$backdate7yr =  date('Y-m-d',strtotime('-2 year', strtotime(date('Y-m-d'))));
	} else {
		$backdate7yr =  date('Y-m-d',strtotime('-3 month', strtotime(date('Y-m-d'))));
	}
	$backdate7yralternate = date('Y-m-d',strtotime(getenv('ACCESS_DATE')));
	if($backdate7yralternate>$backdate7yr){
		$validDateNo =$backdate7yralternate;
	} else {
		$validDateNo =$backdate7yr;
	}
	$validDate= date('jS F Y',strtotime($validDateNo));
	$reqWeekNum = ($request->get('date') != '') ? date('Y-m-d',strtotime($request->get('date'))) : 0;

	$backDateWeekNum = '';
	if($backdate7yr != ''){
		$getBackDateWeek = $timeDimensionService->findByDateWithoutCarbon($backdate7yr);
		$backDateWeekNum = isset($getBackDateWeek->ixYearWeek) ? (int) $getBackDateWeek->ixYearWeek : '';
	}

	$weekStartDateWeekNum = '';
	if($reqWeekNum != 0){
		$getWeekStartDateWeek = $timeDimensionService->findByDateWithoutCarbon($reqWeekNum);
		$weekStartDateWeekNum = isset($getWeekStartDateWeek->ixYearWeek) ? (int) $getWeekStartDateWeek->ixYearWeek : '';
	}

	if(($validDateNo > $reqWeekNum) && ($reqWeekNum != 0)){
		if($backdate7yralternate > date('Y-m-d',strtotime($reqWeekNum))){
			$response['errormessage']="Allocate cannot open dates before ".date('jS F Y',strtotime($backdate7yralternate))." in the Edit Weekly Allocations view.";
			$response['openWeek']="No";
		} else {
			if($weekStartDateWeekNum < $backDateWeekNum){
				$response['errormessage']="You do not have permissions to edit this week, but you can view it.";
				$response['openWeek']="Yes";
			}
			$dateWeek = $timeDimensionService->findByDateWithoutCarbon(date('Y-m-d',strtotime($request->get('date'))));
			$prevWeek = $timeDimensionService->findImmediatePrevWeekOfCurrentWeek($dateWeek->ixYearWeek);
			$nextWeek = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($dateWeek->ixYearWeek);
			$weekStartDate = $timeDimensionService->getWeekStartDate(substr(strval($dateWeek->ixYearWeek),4,2).'/'.substr(strval($dateWeek->ixYearWeek),0,4));

			$request->request->set('checkweeknumber',substr(strval($dateWeek->ixYearWeek),0,4).substr(strval($dateWeek->ixYearWeek),4,2));
			$request->request->set('schedulingTeamId',$request->get('teamId'));
			$checkWeekExistsForTeam = $service->checkWeekExists($request);

			$response['success'] = true;
			$response['weekexists'] = 'No';
			if(!empty($checkWeekExistsForTeam)){
				$response['weekexists'] = 'Yes';
			}

			$response['dateWeek'] = substr(strval($dateWeek->ixYearWeek),4,2).'/'.substr(strval($dateWeek->ixYearWeek),0,4);
			$response['prevWeek'] = substr(strval($prevWeek->ixYearWeek),4,2).'/'.substr(strval($prevWeek->ixYearWeek),0,4);
			$response['nextWeek'] = substr(strval($nextWeek->ixYearWeek),4,2).'/'.substr(strval($nextWeek->ixYearWeek),0,4);
			$response['weekStartDate'] = date('Y-m-d',strtotime($weekStartDate->dDateTime));
		}
		// $response['errormessage']="Allocate cannot open dates before ".$validDate." in the Edit Weekly Allocations view.";
	} else {
		$dateWeek = $timeDimensionService->findByDateWithoutCarbon(date('Y-m-d',strtotime($request->get('date'))));
		$prevWeek = $timeDimensionService->findImmediatePrevWeekOfCurrentWeek($dateWeek->ixYearWeek);
		$nextWeek = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($dateWeek->ixYearWeek);
		$weekStartDate = $timeDimensionService->getWeekStartDate(substr(strval($dateWeek->ixYearWeek),4,2).'/'.substr(strval($dateWeek->ixYearWeek),0,4));

		$request->request->set('checkweeknumber',substr(strval($dateWeek->ixYearWeek),0,4).substr(strval($dateWeek->ixYearWeek),4,2));
		$request->request->set('schedulingTeamId',$request->get('teamId'));
		$checkWeekExistsForTeam = $service->checkWeekExists($request);

		$response['success'] = true;
		$response['weekexists'] = 'No';
		if(!empty($checkWeekExistsForTeam)){
			$response['weekexists'] = 'Yes';
		}

		$response['dateWeek'] = substr(strval($dateWeek->ixYearWeek),4,2).'/'.substr(strval($dateWeek->ixYearWeek),0,4);
		$response['prevWeek'] = substr(strval($prevWeek->ixYearWeek),4,2).'/'.substr(strval($prevWeek->ixYearWeek),0,4);
		$response['nextWeek'] = substr(strval($nextWeek->ixYearWeek),4,2).'/'.substr(strval($nextWeek->ixYearWeek),0,4);
		$response['weekStartDate'] = date('Y-m-d',strtotime($weekStartDate->dDateTime));
	}
	echo json_encode($response);
}

if($action == 'getweekdata'){
	$weekNumber = $request->get('weekNumber');
	$explodeWeekNum = explode('/',$weekNumber);
	$queryWeekNumber = $explodeWeekNum[1].$explodeWeekNum[0];
	$timeDimensionService = new TimeDimensionService();
	$nextWeek = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($queryWeekNumber);
	$weekStartDate = $timeDimensionService->getWeekStartDate($weekNumber);
	$weekEndDate = $timeDimensionService->getWeekEndDate($weekNumber);
	$response['success'] = true;
	$response['nexttoNextWeekNumber'] = substr(strval($nextWeek->ixYearWeek),4,2).'/'.substr(strval($nextWeek->ixYearWeek),0,4);
	$response['weekStartDate'] = date('Y-m-d',strtotime($weekStartDate->dDateTime));
	$response['weekEndDate'] = date('Y-m-d',strtotime($weekEndDate->dDateTime. ' +1 days'));
	$response['currentUserId'] = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
	echo json_encode($response);
}


if($action == 'checkrotaweekexists'){
	$qWeekNum = $request->get('WeekNumber');
	$teamId = $request->get('teamId');
	$request->request->set('checkweeknumber',$qWeekNum);
	$request->request->set('schedulingTeamId',$teamId);
	$checkWeekExistsForTeam = $service->checkWeekExists($request);
	$response['success'] = false;
	$response['isweekexist'] = $checkWeekExistsForTeam;
	echo json_encode($response);
}