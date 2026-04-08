<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Symfony\Component\HttpFoundation\Request;

include_once __DIR__. '/service/LeaveService.php';

$pdo = OpenDBLinkA7();
$request = $request ?? Request::createFromGlobals();
$leaveService = new LeaveService();
$action = $request->get('action');

if($action == 'updatePHLLeaveAmount'){
			if($request->get('PHLLeaveAmount') > 22){
				$returnData['status'] = 'error';
			}else{
				$returnData['status'] = $leaveService->updatePHLLeaveAmountSchPerson($request);
   }
			echo json_encode($returnData);
}

if($action == 'updatePHLonleaveAllocation'){
	$returnData['status'] = false;
	if(!empty($request->get('scheduledPersonIds'))){
		foreach($request->get('scheduledPersonIds') as $key => $val){
			$explodeValue = explode(',',$val);
			if($explodeValue[1] == 0){
						continue;
			}
			$request->request->set('ScheduledPersonId',$explodeValue[0]);
			$request->request->set('StaffNumber',$explodeValue[2]);
			$request->request->set('AllocateID',$explodeValue[3]);
			if($explodeValue[4] == 'WD'){
				$request->request->set('PHLLeaveAmount',$explodeValue[1]);
			} else {
				$PHLLeaveAmount =  number_format((float) (($explodeValue[1]) / 3600), 2, '.', '');
				$request->request->set('PHLLeaveAmount',$PHLLeaveAmount);
			}
			$insertPHLLeave = $leaveService->addPHLLeave($request);
		}
		$returnData['status'] = true;
	}
    echo json_encode($returnData);
}

