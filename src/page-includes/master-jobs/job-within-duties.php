<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/master-jobs-functions.php';
$start_time = $_REQUEST['startTime'];
$endTime = $_REQUEST['endTime'];
$jobid = $_REQUEST['jobid'];
$duty_id = $_REQUEST['duty_id'];
$duty = checkEndTimeExceed($_REQUEST['duty_id']);
$jobsResult = getMasterJobByMasterDutyID($_REQUEST['duty_id']);
$jobs = json_decode($jobsResult, true);
$range = array();
$warning = array();
$dutystarttime = $duty['StartTime'];
$dutyendtime = $duty['EndTime'];

//Check new job conflict
foreach ($jobs as $job) {
    $range[] = jobrange($start_time, $endTime, ($job['StartTime'] + 1), ($job['EndTime'] - 1));
}

if (in_array('conflict', $range)) {
    $msg = "Warning: The job you are trying to add or edit will confilict with the other existing job. Please try again.";
    $result = array('intStatus' => '0', 'strStatus' => $msg);
    header('Content-type: application/json');
}
else if (in_array('noconflcit', $range)) {
    $result = addJobToMasterduties($duty_id, $jobid);
}
else {
    $result = addJobToMasterduties($duty_id, $jobid);
}
echo json_encode($result);
die;
