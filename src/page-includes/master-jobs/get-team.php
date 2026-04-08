<?php
session_start();
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/genericfunctions.php';

include_once '../../function-includes/master-jobs-functions.php';
$dutyID = $_REQUEST['duty_id'];
$team = getTeam($dutyID);
echo json_encode(['team_id' => $team]);
die;
