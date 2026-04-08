<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();

$arrContractTypes = GetContractTypes();

$strLogin = $_REQUEST["login"];
$intContractID = $_REQUEST["contract"];
$intDepartment = $_REQUEST["department"];
$strDepName = GetDepartmentNameFromID($intDepartment);



$strQuery = "if exists     (SELECT        1 AS Expr1
                            FROM          Staff_Web_Config_Departments_Link
                            WHERE        (Login = N'$strLogin') AND (DepartmentID = $intDepartment))
               UPDATE       Staff_Web_Config_Departments_Link
               SET          ContractTypeID = N'$intContractID'
               WHERE        (Login = N'$strLogin') AND (DepartmentID = $intDepartment)
             ELSE 
               INSERT INTO  Staff_Web_Config_Departments_Link
                            (ContractTypeID, Login, DepartmentID)
             VALUES         ('$intContractID', '$strLogin', $intDepartment)";


sqlsrv_query($db, $strQuery);  
$strHistory = 'Contract type changed to '.$arrContractTypes[$intContractID].' for '.$strDepName;
$strHistory.= ' by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i").'<hr>';



// Update the history.........
$strQuery = "UPDATE     Staff_Web_Config
             SET        History = CONCAT('$strHistory', ISNULL(History,''))
             WHERE   (Login = N'$strLogin')";

sqlsrv_query($db, $strQuery);  

