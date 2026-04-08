<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
$timeDimensionService = new TimeDimensionService();
$action = $request->get('actionname');

$returnData['status'] = false;
$returnData['message'] = '';
if($action == 'copyduty'){
    $getWeekNum = $timeDimensionService->findByDateWithoutCarbon(date('Y-m-d',strtotime($request->get('fromDate'))));
    $copyDuty = $service->copyDuty($request);
    $returnData['status'] = $copyDuty['SPExecStatus'];
    $returnData['message'] = $copyDuty['SPMessage'];
    $returnData['weekNum'] = $getWeekNum->ixYearWeek;
}

if($action == 'getschedulingpersons'){
    $scheduledPersonsList = $service->getTeamScheduledPersons($request);
    if(!empty($scheduledPersonsList)){
        $returnData['status'] = true;
        $returnData['schgeduledPeoplesList'] = $scheduledPersonsList;
    }
}

if($action == 'checkdutyexists'){
    $existDutyCount = $service->checkDutyExists($request);
    if($existDutyCount == 0){
        $returnData['status'] = true;
    }
}

if($action == 'getcopydutyenddate'){
    $getMaxEndDate = $service->getCopyDutyEndDate($request);
    if(!empty($getMaxEndDate) && isset($getMaxEndDate['Dutydate'])){
        $returnData['status'] = true;
        $returnData['maxEndDate'] = date('d-M-Y', strtotime($getMaxEndDate['Dutydate']));
    }
}
echo json_encode($returnData);
