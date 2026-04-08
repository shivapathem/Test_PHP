<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Symfony\Component\HttpFoundation\Request;
date_default_timezone_set('UTC');
require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/AllocationRepository.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/WTDService.php';
require_once __DIR__ . '/../../../../function-includes/init.php';
require_once __DIR__ . '/../../../../function-includes/masterduty_functions.php';
require_once __DIR__ . '/../service/HistoryService.php';
require_once __DIR__ .'/../../../../function-includes/DB_Functions.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
$repository = new AllocationRepository();
$historyService = new HistoryService();
$editAllocations = false;
$response['success'] = false;
function hours2seconds($ss) {
    $second =($ss*60)*60;

    return $second;
}

if($request->get('ID') != ''){
    $allocIdData = $repository->getAllocationByID($request->get('ID'));
}
$rightClickActionName = '';
$markOverTime = false;

switch ($request->get('action', 'wiad')) {
    case 'wiad':
        $dataParam = [
            'ID' => $request->get('ID'),
            'allocationsSpId' => $request->get('allocationsSpId'),
            'schedulingPersonId' => $request->get('schedulingPersonId'),
            'allocationsDutyId' => $request->get('allocationsDutyId'),
            'dutyDate' => $request->get('dutyDate'),
            'spActionParam' => 1,
            'action' => 'wiad'
        ];
        break;
    case 'actual':
        $dataParam = [
            'ID' => $request->get('ID'),
            'allocationsSpId' => $request->get('allocationsSpId'),
            'schedulingPersonId' => $request->get('schedulingPersonId'),
            'allocationsDutyId' => $request->get('allocationsDutyId'),
            'dutyDate' => $request->get('dutyDate'),
            'spActionParam' => 1,
            'action' => 'actual'
        ];
        break;
    case 'unmarkactual':
        $dataParam = [
            'ID' => $request->get('ID'),
            'allocationsSpId' => $request->get('allocationsSpId'),
            'schedulingPersonId' => $request->get('schedulingPersonId'),
            'allocationsDutyId' => $request->get('allocationsDutyId'),
            'dutyDate' => $request->get('dutyDate'),
            'spActionParam' => 0,
            'action' => 'unmarkactual'
        ];
        break;
    case 'unmarkwiad':
        $dataParam = [
            'ID' => $request->get('ID'),
            'allocationsSpId' => $request->get('allocationsSpId'),
            'schedulingPersonId' => $request->get('schedulingPersonId'),
            'allocationsDutyId' => $request->get('allocationsDutyId'),
            'dutyDate' => $request->get('dutyDate'),
            'spActionParam' => 0,
            'action' => 'unmarkwiad'
        ];
        break;
    case 'edit':
        if($request->get('StartTime') && $request->get('EndTime')) {
            $stTime = $service->timeIntoSeconds($request->get('StartTime'));
            $edTime = $service->timeIntoSeconds($request->get('EndTime'));
        } else {
            $stTime = 0;
            $edTime = 0;
        }

        if($request->get('miscDuty') == 'No'){
            if(($request->get('StartTime') == '00:00') && ($request->get('EndTime') == '00:00')){
                $edTime = 86400;
            }
        } else {
            if(($request->get('StartTime') == '') && ($request->get('EndTime') == '')){
                $edTime = 86400;
            }
        }

        $intAreaID = $_SESSION['user']['AreaID']??0;
        $intDutyTypeID = 0;
        $intArchived = 0;
        $strTeams = $request->get('SchedulingTeamID');
        $strsearch = '';

        $getMiscDutyList = ListAllMasterDuties($intAreaID, $intDutyTypeID, $intArchived, $strTeams, $strsearch);
        $getMiscDutyList = json_decode($getMiscDutyList,true);

        if(!empty($getMiscDutyList)){
            foreach($getMiscDutyList as $miscK => $miscV){
                if(strtolower($miscV['DutyName']) == strtolower($request->get('DutyName'))){
                    $edTime = 0;
                    break;
                }
            }
        }

        if (substr($request->get('DutyName'), 0, 1)=="-"){
            $edTime = 0;
        }
        if(substr($request->get('DutyName'), 0, 2)=="--"){
            $edTime = 0;
        }

        if($request->get('currDurationVal') != ''){
            $request->request->set('Duration',$request->get('currDurationVal'));
        }
        $request->request->set('id', $request->get('ID'));
        $totalBreakSec = $service->timeIntoSeconds($request->get('breakTimeHour').':'.$request->get('breakTimeMinute'));

        if($request->get('miscDuty') == 'No'){
            $newEndTimeForDur = $edTime;
            if($stTime > $edTime) {
                $newEndTimeForDur = 86400 + $edTime;
            }
            $durationSec = ($newEndTimeForDur - $stTime);
        } else {
            $durationSec = $service->timeIntoSeconds($request->get('currDurationVal'));
        }

        $dataParam = [
            'ID' => $request->get('ID'),
            'DutyName' => $request->get('DutyName'),
            'StartTime' => $stTime,
            'EndTime' => $edTime,
            'BreakTime' => $service->timeIntoSeconds($request->get('breakTimeHour').':'.$request->get('breakTimeMinute')),
            'Duration' => $durationSec,
            'DutyColorId' => $request->get('dutyColorId'),
            'DutyLabelId' => $request->get('labelIds')[0] ?? 0,
            'DutyLabelId2' => $request->get('labelIds')[1] ?? 0,
            'DutyLabelId3' => $request->get('labelIds')[2] ?? 0,
            'DutyLabelId4' => $request->get('labelIds')[3] ?? 0,
            'DutyLabelId5' => $request->get('labelIds')[4] ?? 0,
            'DutyLabelId6' => $request->get('labelIds')[5] ?? 0,
            'action' => 'editduty',
            'isNeedCovering' => $request->get('isNeedCovering') == 'on' ? 0 : 1,
            'isOverrideOver12' => $request->get('isOverrideOver12') == 'on' ? 0 : 1,
            'allocationsDutyId' => $request->get('allocationsDutyId') ?? 0,
            'allocationsSpId' => $request->get('allocationsSpId') ?? 0,
            'allocationsDate' => $request->get('allocationsDate') ?? 0,
            'allocationsSchPer' => $request->get('allocationsSchPer') ?? 0,
            'DutyComments' => $request->get('DutyComments') ?? '',
            'PersonComments' => $request->get('PersonComments') ?? '',
            'oldDutyComments' => $request->get('oldDutyComments') ?? '',
            'oldPersonComments' => $request->get('oldPersonComments') ?? '',
            'pCommentType' => 2
        ];
        break;
    case 'approveWtd':
        $data = [
            'MarkWTD' => WTDService::STATUS_APPROVED,
        ];
        $where = [
            'SchedulingPersonID' => $request->get('schedulingPersonId'),
            'DutyDate' => $request->get('dutyDate')
        ];
        break;
    case 'deleteWtd':
        $data = [
            'MarkWTD' => WTDService::STATUS_DELETED,
        ];
        $where = [
            'SchedulingPersonID' => $request->get('schedulingPersonId'),
            'DutyDate' => $request->get('dutyDate')
        ];
        break;
    case 'sick':
        # code...
        break;
    case 'leave':
        # code...
        break;
    case 'attention':
        $value =  1;
        if($request->get('allocationsDutyId') == 'null') {
            $request->request->set('allocationsDutyId', null);
        }

        if($request->get('allocationsSpId') == 'null') {
            $request->request->set('allocationsSpId', null);
        }
        $data = [
            'editType' =>  'MARKFORATTENTION',
            'isAttention' =>  $value,
			'ID' => $request->get('ID'),
            'allocationsDutyId' => $request->get('allocationsDutyId') ?? 0,
            'allocationsSpId' => $request->get('allocationsSpId') ?? 0,
            'allocationsDate' => $request->get('dutyDate') ?? 0,
            'allocationsSchPer' => $request->get('schedulingPersonId') ?? 0
        ];
        $where = [
            'SchedulingPersonID' => $request->get('schedulingPersonId'),
            'DutyDate' => $request->get('dutyDate')
        ];
		$editAllocations=true;
        //$historyService->markAttentionHistory($request);
        break;
    case 'unattention':
        if($request->get('allocationsDutyId') == 'null') {
            $request->request->set('allocationsDutyId', null);
        }

        if($request->get('allocationsSpId') == 'null') {
            $request->request->set('allocationsSpId', null);
        }
        $value =  0;
        $data = [
            'editType' =>  'MARKFORATTENTION',
            'isAttention' =>  $value,
			'ID' => $request->get('ID'),
            'allocationsDutyId' => $request->get('allocationsDutyId') ?? 0,
            'allocationsSpId' => $request->get('allocationsSpId') ?? 0,
            'allocationsDate' => $request->get('dutyDate') ?? 0,
            'allocationsSchPer' => $request->get('schedulingPersonId') ?? 0
        ];
        $where = [
            'SchedulingPersonID' => $request->get('schedulingPersonId'),
            'DutyDate' => $request->get('dutyDate')
        ];
		$editAllocations=true;
        //$historyService->markAttentionHistory($request);
        break;
    case 'request':
        $data = [
            'editType' =>  'MARKPURPLE',
            'isRequest' =>  $request->get('dutyRequest'),
			'ID' => $request->get('ID'),
            'allocationsDutyId' => $request->get('allocationsDutyId') ?? 0,
            'allocationsSpId' => $request->get('allocationsSpId') ?? 0,
            'allocationsDate' => $request->get('dutyDate') ?? 0,
            'allocationsSchPer' => $request->get('schedulingPersonId') ?? 0
        ];
        $where = [
            'SchedulingPersonID' => $request->get('schedulingPersonId'),
            'DutyDate' => $request->get('dutyDate')
        ];
		$editAllocations=true;
        //$historyService->markRequestHistory($request);
        break;
	case 'delete':
		$value =  null;
		$data = [
			 'SchedulingPersonID' =>  $value
		];
		$where = [
			'SchedulingPersonID' => $request->get('schedulingPersonId'),
			'DutyDate' => $request->get('dutyDate'),
			'IsHomeTeam' => 1
		];
		$deleteWhere = [
		    'SchedulingPersonID' => $request->get('schedulingPersonId'),
			'DutyDate' => $request->get('dutyDate'),
			'IsHomeTeam' => 0
		];
        break;
    case 'overtime':
        $data = [
            'MarkedOvertime' =>  $request->get('mannualothrs') == 0 ? 0 : 1,
            'MannualOThours' =>  hours2seconds($request->get('mannualothrs')),
            'UpdatedBy' => isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'],
            'UpdatedDate' => gmdate('Y-m-d H:i:s'),
			'ID' => $request->get('ID'),
            'allocationsDutyId' => $request->get('allocationsDutyId') ?? 0,
            'allocationsSpId' => $request->get('allocationsSpId') ?? 0
        ];
        //$where = [
        //    'SchedulingPersonID' => $request->get('schedulingPersonId'),
        //    'ID' => $request->get('allocationId')
        //];
		//$updateAllocation=true;
		//$beforeUpdateData =  new Allocation($service->getAllocationByID($request->get('allocationId')));
		//$historyService->insertDutyLogHistory($beforeUpdateData, new Allocation($data), $request->get('SchedulingTeamID'));
		$markOverTime = true;
        break;
    case 'add':
        $expWeekNum = explode('/',$request->get('weekNumber'));
        $startTime = $service->timeIntoSeconds($request->get('StartTime'));
        $endTime = $service->timeIntoSeconds($request->get('EndTime'));
        $duration = ($endTime - $startTime);
        $expDutyDate = explode(' ',$request->get('DutyDate'));
        $startDate = $expDutyDate[0].' '.$request->get('StartTime').':00';
        $endDate = $expDutyDate[0].' '.$request->get('EndTime').':00';
        $insertData = [
            'ID' => 0,
            'DutyName' => $request->get('DutyName'),
            'WeekNumber' => trim($expWeekNum[1]).trim($expWeekNum[0]),
            'dutyBreakTime' => (($request->get('breakTimeHour') * 3600) + ($request->get('breakTimeMinute') * 60)),
            'Duration' => $duration,
            'StartTime' => $startTime,
            'EndTime' => $endTime,
            'SchedulingTeamId' => $request->get('SchedulingTeamID'),
            'dutyColorId' => $request->get('dutyColorId'),
            'DutyDate' => $request->get('DutyDate'),
            'StartDate' => $startDate,
            'EndDate' => $endDate,
            'SchedulingPersonID' => $request->get('SchedulingPersonID'),
            'iDay' => $request->get('iday'),
            'userId' => isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId']
        ];
        break;
    default:
        # code...
        break;
}

if(isset($deleteWhere)){
	$delete = $repository->deleteAllocation($deleteWhere);
	$update = $repository->updateAllocation($data, $where);
	if($delete && $update){
		$response['success'] = true;
	}
}/*
else{
    if(!isset($insertData) && isset($updateAllocation) && $updateAllocation == true){
        $update = $repository->updateAllocation($data, $where);
        if($update){
			if($markOverTime == true){
				$repository->updateMarkOvertime($request->get('allocationId'));
			}
            $response['success'] = true;
        }
    }
}*/
if($editAllocations)
{
	$update = $repository->editAllocations($data);
	$response['success'] = $update ? true : false;
}

if($markOverTime)
{
	$update = $repository->updateMarkOvertime($data);
	$response['success'] = $update ? true : false;
}

if($request->get('ID')){
    $allocation =  new Allocation($service->getAllocationByID($request->get('ID')));
    $WTDService = new WTDService();
}

if(isset($insertData)){
    $insertMiscDuty = $repository->setMiscDutyInAllocation($insertData);
    $response['success'] = $insertMiscDuty;
}

if(isset($dataParam)){
    switch(strtolower($dataParam['action'])){
        case 'wiad':
        case 'unmarkwiad':
            $processResponse = $repository->markWiadAllocation($dataParam);
            break;
        case 'actual':
        case 'unmarkactual':
            $processResponse = $repository->markActualAllocation($dataParam);
            break;
        case 'editduty':
            $processResponse = $repository->editDutyAllocation($dataParam);

            //Person comments
            $existingComment = '';
            $SchedulingPersonID = $dataParam['allocationsSchPer'] ?? 0;
            if($SchedulingPersonID > 0 && $dataParam['allocationsDutyId'] != 'null') {
                $request->request->set('allocationsDutyId', $dataParam['allocationsDutyId']);
                $comments = $repository->getAllocationComments($request);
                $existingComment = $comments['PersonComments'] ?? '';
            }
            if($SchedulingPersonID > 0 && $existingComment != $dataParam['PersonComments']) {
               $repository->setComment($dataParam['ID'], $dataParam['allocationsDate'], $SchedulingPersonID, 0, $dataParam['PersonComments'], 2);
            }
            break;
        default:
            $processResponse['spStatus'] == 0;
            $processResponse['errorMessage'] == 'Something went wrong. Please try again later.';
    }
    if(($processResponse['SPExecStatus'] == 0) || ($processResponse['SPExecStatus'] == false)){
        $response['success'] = true;
    } else {
		$response['success'] = false;
        $response['errMsg'] = $processResponse['SPMessage'];
    }

}

echo json_encode($response);