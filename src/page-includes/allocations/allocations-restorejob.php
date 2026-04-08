<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intjobid = $_REQUEST['jobid'];
$pdo = OpenDBLinkA7();
$jobhistory= 'This Job was restored because the underlying Duty was also restored by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i").'<hr>';
$jobhistory = escapeSingleQuotes($jobhistory);

    $query = "UPDATE AllocationsJobs SET AJ_JobStatus = 1 WHERE (AJ_AllocateJobID = :jobId)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':jobId', $intjobid, PDO::PARAM_INT);
    $stmt->execute();