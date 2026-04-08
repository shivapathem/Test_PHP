<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$teamId  = $_REQUEST['teamId'];
$ScheduledPersonID = $_REQUEST['ScheduledPersonID'];
$arrUser = GetUserandFullNameFromStaffNumber ($ScheduledPersonID);
$strUserLogin = $arrUser[$teamId]['Login'];
$strSQL = "exec [dbo].[Usp_InsertUpdateHideStatsFlag] ?, ?";
$pdo  = OpenDBLinkA7();
$stmt = $pdo->prepare($strSQL);
$stmt->bindParam(1,$teamId, PDO::PARAM_INT);
$stmt->bindParam(2,$strUserLogin, PDO::PARAM_INT);
$stmt->execute();