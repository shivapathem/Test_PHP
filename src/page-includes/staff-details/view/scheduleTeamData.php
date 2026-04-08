<?php
include_once '../process/classSchedulTeamHistory.php';
require_once(__DIR__.'/../../../function-includes/genericfunctions.php');
$scteamobj = new classSchedulTeamHistory;
$personid = $_GET['personid'];
$result  = $scteamobj->getScheduleTeamHistory($personid);
