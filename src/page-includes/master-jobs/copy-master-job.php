<?php
session_start();
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';
$master_job_id = $_REQUEST['id'];
$job = '';
$starttime = 0;
$endtime = 0;
$info = '';
$contact = '';
$location = '';
$backcolor = '';
$forecolor = '';
$programme_id = 0;
$teamid = 0;
$TaskType = 'copyjob';
$result = addUpdateMasterJob($master_job_id, $job, $starttime, $endtime, $info, $contact, $location, $backcolor, $forecolor, $programme_id, $teamid, $TaskType);
echo $result;
