<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;

$progid = $_REQUEST['progid'];
$dutyid = $_REQUEST['dutyid'];

//Add Skill duties Program Link.
$teamskillobj->AddSkillDutiesProgramLink($dutyid, $progid);
?>