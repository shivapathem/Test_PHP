<?php

//date_default_timezone_set('UTC');
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctionsday.php';

$intDepartmentID = $_REQUEST['departmentid'];
$strDate = $_REQUEST['date'];

$intWeek = bbcweeknumber($strDate);
$intDay = getdayofweek($strDate);



MakeDayEdited ($intWeek, $intDay, $intDepartmentID);





 