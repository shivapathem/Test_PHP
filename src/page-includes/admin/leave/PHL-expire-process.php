<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Symfony\Component\HttpFoundation\Request;
include_once __DIR__. '/service/LeaveService.php';
require_once __DIR__ . "/../../../function-includes/DBHelper.php";
require_once __DIR__ . "/../../../function-includes/helpers.php";
include_once __DIR__ .'/../../../function-includes/leave-admin-functions.php';
include_once __DIR__ .'/../../../function-includes/leavefunctions.php';
$leaveService = new LeaveService();
$pdo = OpenDBLinkA7();
$request = $request ?? Request::createFromGlobals();
$leaveService = new LeaveService();

$ExpiredYEar = $request->get('year');
$leaveYear = $ExpiredYEar+1;
$carryOverDate = date('Y',strtotime($leaveYear)).'-04-01';
$action = $request->get('action');

if ($action == 'updatePHLonleaveAllocation'){
	$returnData['status'] = false;
	$returnData['response'] = "Opps! Some error in process,please try Later.";
	if(!empty($request->get('scheduledPersonId'))){
			try {
			$request->request->set('ScheduledPersonId',$request->get('scheduledPersonId'));
			$request->request->set('year',$request->get('year'));
			$request->request->set('comment',$request->get('comment'));
			$request->request->set('StaffNumber',$request->get('staffnumber'));
			$request->request->set('Holiday',$request->get('Holiday'));
			$request->request->set('teamid',$request->get('SchedulingTeamid'));
			$request->request->set('pagecall','ExpiredPHL');
			$request->request->set('PHLLeaveAmount',$request->get('ExpiredAmount'));
			$insertPHLLeave = $leaveService->addPHLLeave($request);
			$returnData['status'] = true;
			$returnData['response'] = $request->get('comment');
			}catch(Exception $e){
				logger()->critical('DB Error', (array) $e);
			}		
		
	}
    
}
echo json_encode($returnData);	