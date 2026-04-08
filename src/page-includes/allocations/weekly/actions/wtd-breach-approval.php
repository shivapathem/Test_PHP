<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../service/WTDService.php';

$request = Request::createFromGlobals();
$WTDservice = new WTDService();

$returnData['status'] = false;
$returnData['errMsg'] = '';
$sessUserFullName = isset($_SESSION['user']['FullName']) ? $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
$approvedByName = isset($_SESSION['user']['DisplayName']) ? $_SESSION['user']['DisplayName'] : $sessUserFullName;
switch(strtoupper($request->get('actionName'))){
	case 'GETDETAILS':
		$breachDetails = $WTDservice->getBreachDetailById($request);
		if(!empty($breachDetails)){
			$returnData['status'] = true;
			if(isset($breachDetails['History']) && !empty($breachDetails['History'])){
				$breachDetails['History'] = htmlspecialchars(preg_replace('/<br\s*\/?>/i', "\n", preg_replace('/<br\s*\/?>\s*/i', '<br>', $breachDetails['History'])));
			}
			$returnData['breachDetail'] = $breachDetails;
			$returnData['approveDate'] = date('d/m/Y');
			$returnData['aprrovedBy'] = $approvedByName;
		} else {
			$returnData['errMsg'] = 'Error in getting breach details.';
		}
		break;
	case 'SESSIONUSERDETAILS':
		$returnData['status'] = true;
		$returnData['approveDate'] = date('d/m/Y');
		$returnData['aprrovedBy'] = $approvedByName;
		break;
	case 'UPDATEBREACHDETAILS':
		if($request->get('breachApproved') == 0){
			$historyLog = 'WTD Breach Approved by '.$approvedByName.' at '.date('H:i').' On '.date('d/m/Y');
		} else {
			$historyLog = 'Comments Updated by '.$approvedByName.' at '.date('H:i').' On '.date('d/m/Y');
		}
		$request->request->set('historyLog',$historyLog);
		$request->request->set('ApprovedBy', $approvedByName);
		$request->request->set('approveDate', date('Y-m-d'));

		$updateBreachDetails = $WTDservice->updateBreachDetails($request);
		if ($updateBreachDetails == true){
			$returnData['status'] = true;
		} else {
			$returnData['errMsg'] = 'Error in updating breach comments and history.';
		}
		break;
	case 'VERIFYBREACHDETAILS':
		$sessUserNetLoginId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
		$request->request->set('NetLogin',$sessUserNetLoginId);
		$spResult = $WTDservice->verifyWTDBreachDetails($request);
		if(isset($spResult['SPExecStatus']) && ((int)$spResult['SPExecStatus'] == 0)){
			$returnData['status'] = true;
        }else{
			$returnData['errMsg'] =	$spResult['SPMessage'];
		}
		break;
	case 'DELETEWTDBREACHDETAILS':
		$deleteWTDBreachDetails = $WTDservice->deleteWTDBreachDetails($request);
		if($deleteWTDBreachDetails == true){
			$returnData['status'] = true;
		} else {
			$returnData['errMsg'] = 'Error in delete WTD breach.';
		}
		break;
	default:
		$returnData['status'] = false;
		$returnData['errMsg'] = 'No case passed as a argument.';
}

echo json_encode($returnData);