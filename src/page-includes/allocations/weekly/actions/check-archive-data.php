<?php

use App\Models\User\RefRole;
use Symfony\Component\HttpFoundation\Request;
use Traits\UserRoleTrait;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$actionName = $request->get('action');
$returnData['status'] = false;

if($actionName == 'checkweekandrole'){
	$intTeamID = $request->get('teamId');
	$userTrait = new class {
        use UserRoleTrait;
    };
	$isScheduler = $userTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULER);
	$isSchTeamAdmin = $userTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_ADMIN);
    $isTeamLeader = $userTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::TEAM_LEADER);
	if($isSchTeamAdmin == 0) {        
        $isSchTeamAdmin = $userTrait->isSystemAdmin() || $userTrait->isAreaAdminByTeam($request->get('teamId')) ? 1 : 0;
    }

	$date3MonthsBack = date('Y-m-d', strtotime('-3 months'));
	$date2YearsBack = date('Y-m-d', strtotime('-2 years'));
	$getWeekNum3MonthsBack = (int) $service->getWeekNumberByDate($date3MonthsBack)['ixYearWeek'];
	$getWeekNum2YearsBack = (int) $service->getWeekNumberByDate($date2YearsBack)['ixYearWeek'];
	$weeknum = $request->get('weeknum');
	$explodeWeekNumber = explode('/', $weeknum ?? '');
	$currentWeek = $weeknum;
	if(!empty($explodeWeekNumber[1]) && isset($explodeWeekNumber[1])){
		$inpWeek = (!empty($explodeWeekNumber) && isset($explodeWeekNumber[0])) ? $explodeWeekNumber[0] : '';
		$inpYear = (!empty($explodeWeekNumber) && isset($explodeWeekNumber[1])) ? $explodeWeekNumber[1] : '';
		if($inpYear == ''){
			$inpWeek = str_pad($inpWeek,2,"0",STR_PAD_LEFT);
			$inpYear = date('Y');
			$currentWeek = $inpYear.$inpWeek;
		}
		if((isset($inpYear)) && (strlen($inpYear) == 2) && (!empty($inpYear))){
			$inpWeek = str_pad($inpWeek,2,"0",STR_PAD_LEFT);
			$inpYear = '20'.$inpYear;
			$currentWeek = $inpYear.$inpWeek;
		}

		if((isset($inpYear)) && ($inpYear != '') && (isset($inpWeek)) && ($inpWeek != '')){
			$currentWeek = $inpYear.$inpWeek;
		}
	} else {
		$currentWeek = (int) $request->get('weeknum');
	}

	switch($isScheduler.'-'.$isSchTeamAdmin.'-'.$isTeamLeader){
            case "1-0-0":
				$isScheduler = 1;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 0;
				break;
			case "0-0-0":
			case "1-0-0":
				$isScheduler = 0;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 0;
				break;
			case "0-1-0":
			case "1-1-0":
			case "0-1-0":
			case "1-1-0":
				$isScheduler = 0;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 0;
				break;
			case "0-0-1":
				$isScheduler = 0;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 1;
				break;
			case "1-0-1":
				$isScheduler = 1;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 1;
				break;
			case "0-0-1":
				$isScheduler = 0;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 1;
				break;
			case "0-1-1":
				$isScheduler = 0;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 1;
				break;
			case "1-1-1":
				$isScheduler = 1;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 1;
				break;
			case "1-0-1":
				$isScheduler = 1;
                $isSchTeamAdmin = 0;
				$isTeamLeader = 1;
				break;
			case "0-1-1":
				$isScheduler = 0;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 1;
				break;
			case "1-1-1":
				$isScheduler = 1;
                $isSchTeamAdmin = 1;
				$isTeamLeader = 1;
				break;
        }

	$returnData['status'] = true;
	$returnData['screenView'] = 0;
	$returnData['screenEdit'] = 0;

	if(($isScheduler == 1) && ($currentWeek < $getWeekNum3MonthsBack)){
            $returnData['screenView'] = 1;
        } else if(($isScheduler == 1) && ($currentWeek >= $getWeekNum3MonthsBack)){
            $returnData['screenEdit'] = 1;
        } else if((($isSchTeamAdmin == 1)) && ($currentWeek < $getWeekNum2YearsBack)){
            $returnData['screenView'] = 1;
        } else if((($isSchTeamAdmin == 1)) && ($currentWeek >= $getWeekNum2YearsBack)){
            $returnData['screenEdit'] = 1;
        } else if(($isTeamLeader == 1) && ($currentWeek >= $getWeekNum3MonthsBack)){
            $returnData['screenEdit'] = 1;
        } else if(($isTeamLeader == 1) && ($currentWeek < $getWeekNum3MonthsBack)){
            $returnData['screenView'] = 1;
        }
}
echo json_encode($returnData);
exit;