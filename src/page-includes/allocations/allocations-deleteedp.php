<?php
date_default_timezone_set('UTC');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$ddate = $_REQUEST['ddate'];
$schedulingPersonId = $_REQUEST['schedulingPersonId'];
$teamId = $_REQUEST['teamId'];
$action = $_REQUEST['action']; 

$pdo = OpenDBLinkA7();

echo saveEdpOvertimeVolunteers($edpId = 0,$ddate, $teamId, $schedulingPersonId, $comments = NULL, $action);
?>