<?php
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
if (!empty($_REQUEST['submit'])) {
    $jobInfor['JobID'] = 0;
    $jobInfor['Job'] = trim($_REQUEST["Job"]);
    $jobInfor['intStartTime'] = seconds_from_time($_REQUEST["StartTime"]);
    $jobInfor['intEndTime'] = seconds_from_time($_REQUEST["EndTime"]);
    $jobInfor['info'] = trim($_REQUEST["info"]);
    $jobInfor['contact'] = trim($_REQUEST["contact"]);
    $jobInfor['location'] = trim($_REQUEST["location"]);
    $jobInfor['backcolor'] = trim($_REQUEST["backcolor"]);
    $jobInfor['forecolor'] = trim($_REQUEST["forecolor"]);
    $jobInfor['programme_id'] = trim($_REQUEST["programme_id"]);
    $jobInfor['team_id']  = trim($_REQUEST["scheduling_team"]);
    $jobInfor['TaskType'] = 'addjob';
    $jobInfor['aftermidnight'] = $_REQUEST["aftermidnight"]??0;
    $jobInfor['unallocated'] = $_REQUEST["unallocated"] ?? 0;
    $jobInfor['jobId'] = $_REQUEST["jobId"]??0;
    $jobInfor['allocationID'] = $_REQUEST["allocationID"]??0;
    $jobInfor['dutyId'] = $_REQUEST["dutyId"] ?? 0;
    if(isset($_REQUEST['unallocated']) && $_REQUEST['unallocated'] == 1) { // Create in Unallocated section
        $jobInfor['dutyId'] = 0;
    }
    $jobInfor['WeekNumber']= $_REQUEST["WeekNumber"];
    $jobInfor['iDay'] = intval($_REQUEST["iDay"]);
    $jobInfor['role'] = $_REQUEST["role"] ?? 0;
    $jobInfor['scheduledpersonid'] = $_REQUEST["scheduledpersonid"] ?? '';
     $jobInfor['dutyDate'] = $_REQUEST["dutyDate"];
    /* Below function is used for ADD /EDIT/COPY task */
	$response=AddEditAllocationJob($jobInfor);
	$response = json_decode($response,true);
	$finalArray = array("intstatus"=>$response['SPExecStatus'],"strstatus"=>$response['SPMessage']);
    echo json_encode($finalArray);
    die();
}