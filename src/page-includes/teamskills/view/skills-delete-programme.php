<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;

$id = $_REQUEST["id"];
//Delete Skill Program.
$teamskillobj->DeleteSkillProgram($id);

//Delete Skill Program Staff Link.
$teamskillobj->DeleteSkillProgramStaffLink($id);
?>