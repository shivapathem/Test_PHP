<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/allocationsfunctions.php';


$intDepartmentID = $_REQUEST['DepartmentID']; 
$intWeekNumber = $_REQUEST['WeekNumber']; 
ImportScheduAll($intDepartmentID, $intWeekNumber);

