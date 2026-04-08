<?php
session_start();
include_once '../../function-includes/init.php';

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';
$intJobID = $_REQUEST['jobid'];
ToggleInactiveMasterJob($intJobID);


