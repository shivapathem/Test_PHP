<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService();
date_default_timezone_set('Europe/London');
$id = $_REQUEST['id'];
$agreed = $_REQUEST['agree'];

$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

if ($agreed == 1) {
  $history = '<hr>Leave agreed by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
}
else {
  $history = '<hr>Leave unagreed by '.$_SESSION['user']['FullName'].' on '.date("d/m/Y").' at '.date("H:i");
}
//set unlikely col value

$leave =  ModApproverAgreedLeaveApplication($id,$agreed,$history,$sessUserId);

echo 1;