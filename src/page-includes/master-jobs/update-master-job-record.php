<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';
if (!empty($_REQUEST['submit'])) {
    $masterJobID = $_REQUEST['master_job_id'];
    $strStartTime = $_REQUEST["StartTime"];
    $strEndTime = $_REQUEST["EndTime"];
    $Job = trim($_REQUEST["Job"]);
    $intStartTime = seconds_from_time($strStartTime);
    $intEndTime = seconds_from_time($strEndTime);
    $info = trim($_REQUEST["info"]);
    $contact = trim($_REQUEST["contact"]);
    $location = trim($_REQUEST["location"]);
    $backcolor = trim($_REQUEST["backcolor"]);
    $forecolor = trim($_REQUEST["forecolor"]);
    $programme_id = trim($_REQUEST["programme_id"]);
    $team_id = trim($_REQUEST["scheduling_team"]);
    $TaskType = 'editjob';
    echo addUpdateMasterJob($masterJobID, $Job, $intStartTime, $intEndTime, $info, $contact, $location, $backcolor, $forecolor, $programme_id, $team_id, $TaskType);
}
