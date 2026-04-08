<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$pdo = OpenDBLinkA7();
$ddate = $_REQUEST['ddate'];
$schedulingPersonId = $_REQUEST['schedulingPersonId'];
$teamId = $_REQUEST['teamId']; 
$action = $_REQUEST['action']; 

echo saveEdpOvertimeVolunteers($edpId = 0,$ddate, $teamId, $schedulingPersonId, $comments = NULL, $action);
?>