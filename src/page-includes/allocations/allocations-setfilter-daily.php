<?php
session_start();
include_once '../../function-includes/init.php';
//include_once '../../genericfunctions.php';

$intDepartmentID =  $_REQUEST['departmentid'];
$intFilterID = $_REQUEST['filterid'];
$intFilterType = $_REQUEST['filtertype'];
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];


$db = OpenDatabase();
if ($intFilterID == "0") {
  $strQuery = "UPDATE      Staff_Web_Config_Departments_Link
               SET         CurrentDailyFilter = NULL
               WHERE        (Login = N'$strLogin') AND (DepartmentID = $intDepartmentID)";
}
else {
  $strFilter = $intFilterType.','.$intFilterID;
  
  $strQuery = " if exists      (SELECT  id
                               FROM    Staff_Web_Config_Departments_Link
                               WHERE   (Login = N'$strLogin') 
                               AND (DepartmentID = $intDepartmentID))  
                UPDATE         Staff_Web_Config_Departments_Link
                SET            CurrentDailyFilter = '$strFilter'
                WHERE          (Login = N'$strLogin') AND (DepartmentID = $intDepartmentID)
               
                ELSE   
                INSERT INTO    Staff_Web_Config_Departments_Link
                               (Login, DepartmentID, CurrentDailyFilter)
                VALUES         (N'$strLogin', $intDepartmentID, N'$strFilter')";          
}
//echo $strQuery;
sqlsrv_query($db, $strQuery);
?>