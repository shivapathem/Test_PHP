<?php
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';

$strCurrentDate = $_REQUEST['strCurrentDate'];
$day7date = $_REQUEST['day7date'];
$intTeamID = $_REQUEST['intTeamID'];
$isViewPage = $_REQUEST['isViewPage'];
$dutyHistoryID = $_REQUEST['dutyhistoryid'];
$jobHistoryID = $_REQUEST['jobhistoryid'];
$signinID = $_REQUEST['signinid'];
$signinLastUpdte = $_REQUEST['signinlastupdate'];

$timerdata=GetLastDayUpdate ($strCurrentDate, $day7date,$intTeamID, $isViewPage, $dutyHistoryID,$jobHistoryID,$signinID,$signinLastUpdte);

echo $timerdata['IsRefresh'];
?>