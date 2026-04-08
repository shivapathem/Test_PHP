<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/master-jobs-functions.php';

$duty_id=$_REQUEST['duty_id'];
$startTime=seconds_from_time($_REQUEST['startTime']);
$endTime=seconds_from_time($_REQUEST['endTime']);
$job=$_REQUEST['job'];
$backcolor=$_REQUEST['backcolor'];
$forecolor=$_REQUEST['forecolor'];
$programme_id=$_REQUEST['programme_id'];
$contact=$_REQUEST['contact'];
$location=$_REQUEST['location'];
$info=$_REQUEST['info'];
$flag=$_REQUEST['flag'];
$schedule_team=$_REQUEST['schedule_team'];
$actionVal=(isset($_REQUEST['actionVal']) && $_REQUEST['actionVal']=='EDIT') ? $_REQUEST['actionVal'] : 'ADD';
$current_user='1';
$areaid=1;

if($flag=='Before'){
    $result = addJobFromDuty($duty_id,$job,$startTime,$endTime,$info,$programme_id,$backcolor,
			$forecolor,$contact,$location,$current_user,$areaid,$schedule_team, $actionVal);
    $warning = array('status' => (int)$result['intStatus'], 'msg' => $result['strStatus']);
    echo json_encode($warning);
    die;
}

if($flag=='After'){
	$result = addJobFromDuty($duty_id,$job,$startTime,$endTime,$info,$programme_id,$backcolor,
			$forecolor,$contact,$location,$current_user,$areaid,$schedule_team, $actionVal);
	$warning = array('status' => (int)$result['intStatus'], 'msg' => $result['strStatus']);
    echo json_encode($warning);
    die;
}

if($flag=='newrecord'){
    $result = addJobFromDuty($duty_id,$job,$startTime,$endTime,$info,$programme_id,$backcolor,
			$forecolor,$contact,$location,$current_user,$areaid,$schedule_team, $actionVal);
    $warning = array('status' => (int)$result['intStatus'], 'msg' => $result['strStatus'], 'masterjobid' => $result['masterjobid']);
    echo json_encode($warning);
    die;
}
