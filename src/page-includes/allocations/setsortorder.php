<?php
 session_start();
require_once '../../../vendor/autoload.php';
include_once '../../function-includes/bootstrap.php';
$intOrder = $_REQUEST['order'];
$_SESSION['dailyorder'] = $intOrder;