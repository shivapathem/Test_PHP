<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;
$intDutyID = $_REQUEST['id'];
$intDay = $_REQUEST['day'];

$teamskillobj->UpdateToggleDay($intDutyID, $intDay);
?> 