<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();


$strLogin = $_REQUEST["login"];
$intTeamID = $_REQUEST["team"];
$intDepartment = $_REQUEST["department"];
$strDepName = GetDepartmentNameFromID($intDepartment);
$arrSCMappings = GetSortCodeMappingByTeam($intDepartment);


$strQuery = "if exists     (SELECT        1 AS Expr1
                            FROM          Staff_Web_Config_Departments_Link
                            WHERE        (Login = N'$strLogin') AND (DepartmentID = $intDepartment))
               UPDATE       Staff_Web_Config_Departments_Link
               SET          SortCodeMappingID = N'$intTeamID'
               WHERE        (Login = N'$strLogin') AND (DepartmentID = $intDepartment)
             ELSE 
               INSERT INTO  Staff_Web_Config_Departments_Link
                            (SortCodeMappingID, Login, DepartmentID)
             VALUES         ('$intTeamID', '$strLogin', $intDepartment)";


sqlsrv_query($db, $strQuery);  
$strHistory = 'Team changed to '.$arrSCMappings[$intTeamID]['Description'].' for '.$strDepName;
$strHistory.= ' by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i").'<hr>';



// Update the history.........
$strQuery = "UPDATE     Staff_Web_Config
             SET        History = CONCAT('$strHistory', ISNULL(History,''))
             WHERE   (Login = N'$strLogin')";

sqlsrv_query($db, $strQuery);  

