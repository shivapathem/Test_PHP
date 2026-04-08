<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;
$id = $_REQUEST["id"];

//Delete Skill Duty.
$teamskillobj->DeleteSkillDuty($id);

//Delete Skill Duty Program Link.
$teamskillobj->DeleteSkillDutyProgramLink($id);