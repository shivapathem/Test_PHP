<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;
$progid = $_REQUEST['progid'];
$staffid = $_REQUEST['staffid'];

$teamskillobj->DeletePersonWithProgram($progid, $staffid);
?>