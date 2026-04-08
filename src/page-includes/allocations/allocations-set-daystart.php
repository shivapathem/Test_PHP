<?php
session_start();
include_once __DIR__. '/../../function-includes/DBHelper.php';


$intDayCount = $_REQUEST['daynumber'];
$intTeamID = $_REQUEST['teamId'];
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$pdo = OpenDBLinkA7();

$strSQL = "SELECT id FROM User_Web_Config WHERE (Login = :strLogin) AND (SchedulingTeamId = :intTeamID)";
$stmt = $pdo->prepare($strSQL);
$stmt->bindParam(':intTeamID', $intTeamID, PDO::PARAM_INT);
$stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR); 
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if(empty($row)){
    $strSQL = "INSERT INTO User_Web_Config(Login, SchedulingTeamId, CreatedBy, CreatedDate) VALUES (:strLogin, :intTeamID, :userId, CONVERT(DATETIME, GETDATE(), 101))";
    $stmt = $pdo->prepare($strSQL);
    $stmt->bindParam(':intTeamID', $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
}

$strQuery = "UPDATE User_Web_Config SET ProdStartDay = :intDayCount WHERE (Login = :strLogin) AND (SchedulingTeamId = :intTeamID)";
$stmt = $pdo->prepare($strQuery);
$stmt->bindParam(':intDayCount', $intDayCount, PDO::PARAM_INT);
$stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
$stmt->bindParam(':intTeamID', $intTeamID, PDO::PARAM_INT);
$stmt->execute();

