<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$strLogin = $_REQUEST["login"];
$strColour = $_REQUEST["colour"];
$intDepartment = $_REQUEST["department"];



$strQuery = "if exists (SELECT        1 AS Expr1
                        FROM          Staff_Web_Config_Departments_Link
                        WHERE        (Login = N'$strLogin') AND (DepartmentID = $intDepartment))


UPDATE       Staff_Web_Config_Departments_Link
SET                TextColour = N'$strColour'
WHERE        (Login = N'$strLogin') AND (DepartmentID = $intDepartment)

ELSE 

INSERT INTO Staff_Web_Config_Departments_Link
                         (TextColour, Login, DepartmentID)
VALUES        (N'$strColour', N'$strLogin', $intDepartment)";

echo $strQuery;
sqlsrv_query($db, $strQuery);  

