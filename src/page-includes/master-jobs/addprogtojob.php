<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
$intJobID = $_REQUEST['JobID'];
$intProgID = $_REQUEST['ProgID'];

AddProgToMasterJob($intJobID, $intProgID);

