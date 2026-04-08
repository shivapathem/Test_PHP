<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
$intAction = $_REQUEST['action'];
$strDate = $_REQUEST['date'];
$intSchedulingTeamId = $_REQUEST['teamId'];

LockUnlockDay ($strDate, $intSchedulingTeamId, $intAction);