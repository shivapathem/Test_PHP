<?php
session_start();
include_once '../../function-includes/init.php';
$action = $_REQUEST["action"] ?? '';
$_SESSION['hideduties'] = $action;
?>