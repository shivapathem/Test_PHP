<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/allocationsfunctions.php';


$intDepartmentID = $_REQUEST['DepartmentID']; 
$intWeekNumber = $_REQUEST['WeekNumber']; 

$db = OpenDatabase();
  

$strQuery = "DELETE FROM      Allocations
             WHERE            (DepartmentID = $intDepartmentID) 
             AND              (WeekNumber = $intWeekNumber)";

 sqlsrv_query($db, $strQuery);
 
$strQuery = "DELETE FROM      Jobs
             WHERE            (DepartmentID = $intDepartmentID) 
             AND              (WeekNumber = $intWeekNumber)"; 
             
 sqlsrv_query($db, $strQuery);            
             
$strQuery = "UPDATE Weeks set LastUpdate = 0
WHERE        (DepartmentID = $intDepartmentID) AND (WeekNumber = $intWeekNumber)";             
sqlsrv_query($db, $strQuery);
              