<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intZoomAmount = $_REQUEST['zoomamount'];
$teamId = $_REQUEST['teamId'];
//update zoom amount
UpdateHourWidthByUserLogin($strUser,$intZoomAmount,$teamId);