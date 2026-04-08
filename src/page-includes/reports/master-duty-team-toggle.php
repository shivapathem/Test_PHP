<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intDepartmentID  = $_REQUEST['departmentid'];
$strLogin = $_REQUEST['login'];
$intAction = $_REQUEST['action'];
$strSQL = "exec [dbo].[Usp_InsertUpdateHideStaffFlag] ?, ?";

$pdo  = OpenDBLinkA7();
$stmt = $pdo->prepare($strSQL);
$stmt->bindParam(1,$intDepartmentID, PDO::PARAM_INT);
$stmt->bindParam(2,$strLogin, PDO::PARAM_STR);
$stmt->execute();
