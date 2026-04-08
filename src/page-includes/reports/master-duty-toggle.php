<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intDepartmentID  = $_REQUEST['departmentid'];
$intDutyID = $_REQUEST['dutyid'];
$strSQL = "exec [dbo].[Usp_InsertUpdateMasterDutiesHide] ?, ?";

$pdo  = OpenDBLinkA7();
$stmt = $pdo->prepare($strSQL);
$stmt->bindParam(1,$intDepartmentID, PDO::PARAM_INT);
$stmt->bindParam(2,$intDutyID, PDO::PARAM_INT);
$stmt->execute();