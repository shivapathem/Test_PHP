<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../../../vendor/autoload.php";
require_once __DIR__ . '/../../page-includes/allocations/weekly/service/AllocationService.php';
require_once __DIR__ . '/../../function-includes/leavefunctions.php';
include_once __DIR__ ."/../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$service = new AllocationService();

$dDate = $request->get('dDate') ?: '';
$baseDate = DateTime::createFromFormat('D, jS M Y', $dDate);
if ($baseDate === false) {
    $baseDate = new DateTime();
}
$startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $baseDate->format('Y-m-d') . ' 00:00:00');
$endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $baseDate->format('Y-m-d') . ' 00:00:00');
$startDateTime->modify("+{$request->get('pdlStartTime')} seconds");
$endDateTime->modify("+{$request->get('pdlEndTime')} seconds");
$LeaveStartDateTime = $startDateTime->format('Y-m-d H:i');
$LeaveEndDateTime = $endDateTime->format('Y-m-d H:i');
$request->request->set('LeaveStartDateTime', $LeaveStartDateTime);
$request->request->set('LeaveEndDateTime', $LeaveEndDateTime);

$actionName = strtoupper($request->get('action'));

if($actionName == 'VERIFYPDL'){
	$returnData['status'] = $service->verifyPartDayLeave($request);
}

$currentuserid = ($_COOKIE['editWeeklyUserId']) ?? $_SESSION['user']['UserID'];
$username = isset($_COOKIE['editWeeklyUserFullName']) ? $_COOKIE['editWeeklyUserFullName'] : $_SESSION['user']['FullName'];
$start_time = $service->convertSecondsIntoTime($_SESSION['start_time'],':','No');
$end_time = $service->convertSecondsIntoTime($_SESSION['end_time'],':','No');
$update_start_time = $service->convertSecondsIntoTime($_POST['pdlStartTime'],':','No');
$update_end_time = $service->convertSecondsIntoTime($_POST['pdlEndTime'],':','No');
$_SESSION['convert']=2;
if($actionName == 'AUTOAPPROVEPDL'){
$attributeid = $_POST['leaveId']; $historytype = '15'; $userid = $currentuserid;$message ='Converted to part day of leave with start time '.$update_start_time.' and end time '.$update_end_time.' by '.$username.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, ''); 
$status = 1;
$historysubtype='NULL';
PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);

$attributeid = $_SESSION['attributeid']; $historytype = '8'; $userid = $currentuserid;$message ='Converted to part day of leave with start time '.$update_start_time.' and end time '.$update_end_time.' by '.$username.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
$status = 1;
$historysubtype='PH';
PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);
}

if(($start_time!=$update_start_time) && ($end_time==$update_end_time)){
	$attributeid = $_POST['leaveId']; $historytype = '15'; $userid = $currentuserid;$message ='Part day of leave start time amended by '.$username.' from '.$start_time.' to '.$update_start_time.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
    $status = 1;
    $historysubtype='NULL';
	PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);

	$attributeid = $_SESSION['attributeid']; $historytype = '8'; $userid = $currentuserid;$message ='Part day of leave start time amended by '.$username.' from '.$start_time.' to '.$update_start_time.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
    $status = 1;
    $historysubtype='PH';
	PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);
}
if(($end_time!=$update_end_time) && ($start_time==$update_start_time)){
	$attributeid = $_POST['leaveId']; $historytype = '15'; $userid = $currentuserid;$message ='Part day of leave end time amended by '.$username.' from '.$end_time.' to '.$update_end_time.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
    $status = 1;
    $historysubtype='NULL';
	PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);

	$attributeid = $_SESSION['attributeid']; $historytype = '8'; $userid = $currentuserid;$message ='Part day of leave end time amended by '.$username.' from '.$end_time.' to '.$update_end_time.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
    $status = 1;
    $historysubtype='PH';
	PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);
}
if($start_time && $end_time !='00:00'){
	if(($start_time!=$update_start_time ) && ($end_time!=$update_end_time)){
		$attributeid = $_POST['leaveId']; $historytype = '15'; $userid = $currentuserid;$message ='Part day of leave start time and end time amended by '.$username.' from '.$start_time.' to '.$update_start_time.' and '.$end_time.' to '.$update_end_time.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
        $status = 1;
        $historysubtype='NULL';
		PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);

		$attributeid = $_SESSION['attributeid']; $historytype = '8'; $userid = $currentuserid;$message ='Part day of leave start time and end time amended by '.$username.' from '.$start_time.' to '.$update_start_time.' and '.$end_time.' to '.$update_end_time.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
        $status = 1;
        $historysubtype='PH';
		PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);
	}
}

if($actionName == 'AUTOAPPROVEPDL'){
	$pdlStartTime = (($request->get('pdlStartTime') != '') && ($request->get('pdlStartTime') != 0)) ? $request->get('pdlStartTime') : 0;
	$pdlEndTime = (($request->get('pdlEndTime') != '') && ($request->get('pdlEndTime') != 0)) ? $request->get('pdlEndTime') : 0;
    $openPopupId = $request->get('openPopupId');
    $openPopupWeek = $request->get('openPopupWeek');

    $jsonAmounts = '';
    $jsonPDLLeaves = '';
    $arrAllocLeaveTypes = GetLeaveAllocateTypes();

    if(!empty($arrAllocLeaveTypes)){
      	$pdlLeaveAmountSplitData = [];
      	foreach($arrAllocLeaveTypes as $leaveTypeKey => $leaveTypeVal){
        	if(strtoupper($leaveTypeVal['Description']) == 'ANNUAL'){
        		$nwPdlEndTime = ($pdlStartTime > $pdlEndTime) ? (86400+$pdlEndTime) : $pdlEndTime;
          		$pdlLeaveAmountSplitData[0]["AppId"] = (string) $request->get('leaveId');
          		$pdlLeaveAmountSplitData[0]["TypeId"] = $leaveTypeVal['ID'];
          		$pdlLeaveAmountSplitData[0]["Amount"] = displayTime(number_format((float) (($nwPdlEndTime-$pdlStartTime) / 3600), 2, '.', ''),'.');
          		break;
        	}
      	}
      	$jsonAmounts = json_encode($pdlLeaveAmountSplitData);
    }

    if(($pdlStartTime != 0) || ($pdlEndTime != 0)){
        $pdlData = [];
        $pdlData[0]["LeaveID"] = (string) $request->get('leaveId');
        $pdlData[0]["LeaveStartTime"] = $pdlStartTime;
        $pdlData[0]["LeaveEndTime"] = ($pdlStartTime > $pdlEndTime) ? (86400+$pdlEndTime) : $pdlEndTime;
        $jsonPDLLeaves = json_encode($pdlData);
    }

    if(($jsonAmounts != '') && ($jsonPDLLeaves != '')){
    	$request->request->set('jsonAmounts',$jsonAmounts);
    	$request->request->set('jsonPDLLeaves',$jsonPDLLeaves);
    	$returnData['status'] = $service->autoApplyApprovePDL($request);
    }
    $returnData['openPopupId'] = $openPopupId;
    $returnData['openPopupWeek'] = $openPopupWeek;
}

if($actionName == 'AUTOAPPROVEPDLEDITWEEKLY'){
	$pdlStartTime = (($request->get('pdlStartTime') != '') && ($request->get('pdlStartTime') != 0)) ? $request->get('pdlStartTime') : 0;
	$pdlEndTime = (($request->get('pdlEndTime') != '') && ($request->get('pdlEndTime') != 0)) ? $request->get('pdlEndTime') : 0;
    $openPopupId = $request->get('openPopupId');
    $openPopupWeek = $request->get('openPopupWeek');

    $dataSchedulingPerson = $request->get('dataSchedulingPerson');
    $dataRowId = $request->get('dataRowId');
    $dataDutyName = $request->get('dataDutyName');
    $dataRowStart = $request->get('dataRowStart');
    $dataRowEnd = $request->get('dataRowEnd');
    $dataUniqueId = $request->get('dataUniqueId');
    $dataDutyDate = $request->get('dataDutyDate');

    $jsonAmounts = '';
    $jsonPDLLeaves = '';
    $arrAllocLeaveTypes = GetLeaveAllocateTypes();

    if(!empty($arrAllocLeaveTypes)){
      	$pdlLeaveAmountSplitData = [];
      	foreach($arrAllocLeaveTypes as $leaveTypeKey => $leaveTypeVal){
        	if(strtoupper($leaveTypeVal['Description']) == 'ANNUAL'){
        		$nwPdlEndTime = ($pdlStartTime > $pdlEndTime) ? (86400+$pdlEndTime) : $pdlEndTime;
          		$pdlLeaveAmountSplitData[0]["AppId"] = (string) $request->get('leaveId');
          		$pdlLeaveAmountSplitData[0]["TypeId"] = $leaveTypeVal['ID'];
          		$pdlLeaveAmountSplitData[0]["Amount"] = displayTime(number_format((float) (($nwPdlEndTime-$pdlStartTime) / 3600), 2, '.', ''),'.');
          		break;
        	}
      	}
      	$jsonAmounts = json_encode($pdlLeaveAmountSplitData);
    }

    if(($pdlStartTime != 0) || ($pdlEndTime != 0)){
        $pdlData = [];
        $pdlData[0]["LeaveID"] = (string) $request->get('leaveId');
        $pdlData[0]["LeaveStartTime"] = $pdlStartTime;
        $pdlData[0]["LeaveEndTime"] = ($pdlStartTime > $pdlEndTime) ? (86400+$pdlEndTime) : $pdlEndTime;
        $jsonPDLLeaves = json_encode($pdlData);
    }

    if(($jsonAmounts != '') && ($jsonPDLLeaves != '')){
    	$request->request->set('jsonAmounts',$jsonAmounts);
    	$request->request->set('jsonPDLLeaves',$jsonPDLLeaves);
    	$returnData['status'] = $service->autoApplyApprovePDL($request);
    }
    $returnData['openPopupId'] = $openPopupId;
    $returnData['openPopupWeek'] = $openPopupWeek;
    $returnData['dataSchedulingPerson'] = $request->get('dataSchedulingPerson');
    $returnData['dataRowId'] = $request->get('dataRowId');
    $returnData['dataDutyName'] = $request->get('dataDutyName');
    $returnData['dataRowStart'] = $request->get('dataRowStart');
    $returnData['dataRowEnd'] = $request->get('dataRowEnd');
    $returnData['dataUniqueId'] = $request->get('dataUniqueId');
    $returnData['dataDutyDate'] = $request->get('dataDutyDate');
}
echo json_encode($returnData);
exit;
?>