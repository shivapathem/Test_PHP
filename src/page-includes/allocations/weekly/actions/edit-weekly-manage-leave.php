<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
include_once __DIR__ . '/../../../../function-includes/common/classCommonDBFunctions.php';
include_once __DIR__ . '/../../../../function-includes/leavefunctions.php';
$request = Request::createFromGlobals();
$service = new AllocationService();
$commonDbobj = new classCommonDBFunctions;
$action = $request->get('actionname');
$leaveId = $request->get('leaveId');
$response['status'] = false;
$response['message'] = '';
if($action == 'checkgroup'){
	$sessUserNetId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
	$arrUserGroups = json_decode($commonDbobj->userLeaveRequestByNetLoginandRequesterID($sessUserNetId, $request->get('scheduledNetLogin')), true);
	if (empty($arrUserGroups)) {
	    $response['message'] = 'You do not have leave authoriser permissions in the leave group that this person is in and/or the person is not in a leave group.';
	} else {
		$response['status'] = true;
	}
	if(!empty($leaveId)) {
		$leaveDetails = getLeaveApplicationDetails($leaveId);
		if(isset($arrUserGroups['LeaveRequests'][$leaveDetails['GroupID']]) &&
		$arrUserGroups['LeaveRequests'][$leaveDetails['GroupID']]['Admin'] == 3
		) {
			$response['message'] = 'You do not have leave authoriser permissions in the leave group that this person is in and/or the person is not in a leave group.';
			$response['status'] = false;
		}
	}
}
echo json_encode($response);
exit;