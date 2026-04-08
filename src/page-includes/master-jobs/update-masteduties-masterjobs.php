<?php
//Update Master Duties Time when job is assigned from JObs list to duties Drag and Drop
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/master-jobs-functions.php';
$startTime = $_REQUEST['startTime'];
$endTime = $_REQUEST['endTime'];
$duty_id   = $_REQUEST['duty_id'];
$jobid     = $_REQUEST['jobid'];
$flag = $_REQUEST['flag'];

$getDutyTime = getDutyTime($duty_id);

if($flag=='Before'){
	$result = addJobToMasterduties($duty_id,$jobid);
	echo json_encode($result);
}

if($flag=='After'){	
    $result = addJobToMasterduties($duty_id,$jobid);
	echo json_encode($result);
}

