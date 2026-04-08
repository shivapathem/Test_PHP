<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Symfony\Component\HttpFoundation\Request;
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/leave-admin-functions.php';
include_once '../../../function-includes/leavefunctions.php';
include_once __DIR__.'/../../../function-includes/common/classCommonDBFunctions.php';
include_once  __DIR__.'/service/LeaveService.php';

$leaveService = new LeaveService();
$pdo = OpenDBLinkA7();
$request = Request::createFromGlobals();
$commonObj = new classCommonDBFunctions();

$teamID = $_REQUEST['teamId'];
$PHLCredits = [];
$intWeekNumber = 0;
$PHLStartDate = getPHLStartDate(); 
$getExpiredPHL = getAllExpirdPHL($PHLStartDate,$intWeekNumber,$teamID);//Step 3
//Check for Credit Sum
if (!empty($getExpiredPHL)) {
    foreach ($getExpiredPHL as $index => $ExpiredPHL) {
        $scheduledPerson = $ExpiredPHL['SchedulingPersonID'];
        $creditdateData = explode(" ",$ExpiredPHL['PHLDATE']);
        $creditdate = $creditdateData[0];
        $expireddateData = explode(" ",$ExpiredPHL['ExpDate']);
        $PHLData[$scheduledPerson][$creditdate]['Balance'] = $ExpiredPHL['diff']; 
        $PHLData[$scheduledPerson][$creditdate]['SchedulingPersonID']=$scheduledPerson;        
        $PHLData[$scheduledPerson][$creditdate]['CreditDate'] = $creditdate;
        $PHLData[$scheduledPerson][$creditdate]['PHLAmount'] = $ExpiredPHL['rolling_sum']; 
        $PHLData[$scheduledPerson][$creditdate]['PHLUsed'] = $ExpiredPHL['rolling_d_sum'];
        $PHLData[$scheduledPerson][$creditdate]['expiryDate'] = $expireddateData[0];
        $PHLData[$scheduledPerson][$creditdate]['StaffNumber'] = $ExpiredPHL['StaffNumber'];
        $PHLData[$scheduledPerson][$creditdate]['Name'] =  $ExpiredPHL['userDisplayName'];
        $PHLData[$scheduledPerson][$creditdate]['iYear'] = $ExpiredPHL['iYear'];
        $PHLData[$scheduledPerson][$creditdate]['comment']= $ExpiredPHL['Comments'];
        $PHLData[$scheduledPerson][$creditdate]['TimeDemensionID']= $ExpiredPHL['TimeDemensionID'];
        $PHLData[$scheduledPerson][$creditdate]['SchedulingTeamid']= $teamID; 
        $PHLData[$scheduledPerson][$creditdate]['ID']= $ExpiredPHL['ID'];   
    }
}
$returnData['status'] = false;
$returnData['response'] = "Opps! Some error in process,please try Later.";
if (!empty($PHLData)) {
foreach ($PHLData as $key => $PHLExpireRow) {
    foreach ($PHLExpireRow as $innerKey=> $innerRow) {
        if (isset($innerRow['Balance']) && $innerRow['Balance'] < 0) {
            $balance = $innerRow['Balance'];
            $comment = "Unused PHL Leave of ".$innerRow ['Balance']." hours for  ".date('d/m/Y',strtotime($innerRow['CreditDate']))." [ ".$innerRow['comment']." ] has been expired on ".date('d/m/Y')."By ".$_SESSION['user']['FullName'];
			$request->request->set('ScheduledPersonId',$innerRow['SchedulingPersonID']);
			$request->request->set('year',$innerRow['iYear']);
			$request->request->set('comment',$comment);
			$request->request->set('StaffNumber',$innerRow['StaffNumber']);
			$request->request->set('Holiday',$innerRow['TimeDemensionID']);
			$request->request->set('teamid',$innerRow['SchedulingTeamid']);
			$request->request->set('pagecall','ExpiredPHL');
			$request->request->set('PHLLeaveAmount',$balance);
			$insertPHLLeave = $leaveService->addPHLLeave($request);
        }
     }
   }
    $returnData['status'] = true;
    $returnData['response'] = "All PHL Expired Successfully!";
    
}
echo json_encode($returnData);
