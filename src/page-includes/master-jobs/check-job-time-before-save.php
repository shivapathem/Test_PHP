<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/master-jobs-functions.php';

$start_time = seconds_from_time($_REQUEST['startTime']);
$endTime = seconds_from_time($_REQUEST['endTime']);
$jobResults = getMasterJobByMasterDutyID($_REQUEST['duty_id']);
$jobs = json_decode($jobResults, true);
$duty = checkEndTimeExceed($_REQUEST['duty_id']);
$jobname = $_REQUEST['job'];
$info = $_REQUEST['info'];
$contact = $_REQUEST['contact'];
$location = $_REQUEST['location'];
$backcolor = $_REQUEST['backcolor'];
$forecolor = $_REQUEST['forecolor'];
$programme_id = $_REQUEST['programme_id'];
$duty_starttime = $_REQUEST['duty_starttime'];
$duty_endtime = $_REQUEST['duty_endtime'];
$teamid = $_REQUEST['schedule_team'];
$master_job_id = 0;
if(isset($_REQUEST['master_job_id'])) {
    $master_job_id = $_REQUEST['master_job_id'];
}
if($start_time > $endTime)
{
	$endTime = $endTime + 86400;
}
if ($start_time < $duty_starttime || $endTime > $duty_endtime) {
    if ($duty_endtime > 86400) {
        if (($start_time + 86400) > $duty_starttime && ($endTime + 86400) > $duty_endtime) {
            $msg = "<br><br><br><br>Warning: the job you are trying to add or edit will produce a conflict with either another job or the duty start/end time. Please try again.<br><br><br><br><br><br>";
            $warning = array('status' => 'Conflict', 'msg' => $msg);
            echo json_encode($warning);
            die;
        }
    } else {
        $msg = "<br><br><br><br>Warning: the job you are trying to add or edit will produce a conflict with either another job or the duty start/end time. Please try again.<br><br><br><br><br><br>";
        $warning = array('status' => 'Conflict', 'msg' => $msg);
        echo json_encode($warning);
        die;
    }
}

$jobExists = checkMasterJobExists($jobname,$teamid, $master_job_id, $start_time, $endTime);

if (!empty($jobExists['MasterJobID'])) {

$msg = "Warning: Master Job already exists";
$warning = array('status' => 'Conflict', 'msg' => $msg);
echo json_encode($warning);
die;
}

if (count($jobs) > 0) {
    foreach ($jobs as $job) {

        if($master_job_id && $job['MasterJobID'] == $master_job_id) {
            continue;
        }

        $range = jobrange($start_time, $endTime, $job['StartTime'], $job['EndTime']);

        if($job['StartTime'] > $job['EndTime']) {
            $JobStartTime = $job['StartTime'];
            $JobEndTime = $job['EndTime'] + 86400;
        } else {
            $JobEndTime = $job['EndTime'];
        }
        if (in_array(($start_time + 1), range($job['StartTime'], $JobEndTime))) {
            $msg = "<br><br><br><br>Warning: The job you are trying to add or edit will conflict with the existing job. Please try again.<br><br><br><br><br><br>";
            $warning = array('status' => 'Conflict', 'msg' => $msg);
            echo json_encode($warning);
            die;
        } else if ((in_array(($job['StartTime']), range(($start_time + 1), ($endTime - 1))) ||
            in_array(($job['EndTime']), range(($start_time + 1), ($endTime - 1)))) &&
            ($endTime > $start_time && $job['EndTime'] > $job['StartTime'])
            ) {
            $msg = "<br><br><br><br>Warning: The job you are trying to add or edit will conflict with the existing job. Please try again.<br><br><br><br><br><br>";
            $warning = array('status' => 'Conflict', 'msg' => $msg);
            echo json_encode($warning);
            die;
        }
    }
}

//Before for new new job
if (($master_job_id == 0) && ($start_time < $duty_starttime)) {
    $msg = "";
    $warning = array('status' => 'Before', 'msg' => $msg);
    echo json_encode($warning);
    die;
}
//Before end for new new job

//After append for new new job
if (($master_job_id == 0) && (($start_time + 1 > $duty_endtime) || ($endTime > $duty['EndTime']))) {
    $msg = "";
    $warning = array('status' => 'After', 'msg' => $msg);
    echo json_encode($warning);
    die;

}
//End After amend for new new job


//Jobs will lie between duty time
if (in_array($start_time, range($duty['StartTime'], $duty['EndTime'])) && ($endTime <= $duty['EndTime'])) {
    
    $range = [];
    //Check new job conflict
    foreach ($jobs as $job) {
        if($master_job_id && $job['MasterJobID'] == $master_job_id) {
            continue;
        }
        $range[] = jobrange($start_time, $endTime, $job['StartTime'], $job['EndTime']);
    }
    if (in_array('conflict', $range)) {
        $msg = "<br><br><br><br>Warning: The job you are trying to add or edit will conflict with the existing job. Please try again.<br><br><br><br><br><br>";
        $warning = array('status' => 'Conflict', 'msg' => $msg);
        echo json_encode($warning);
        die;
    }
    else {
        $warning = array('status' => 'ok', 'msg' => 'New Record will create');
        echo json_encode($warning);
        die;
    }
    //End new job conflict
}

$warning = array('status' => 'ok', 'msg' => '');
echo json_encode($warning);
die;
//End Jobs will lies between duty time.
//Check If Job Conflicts
