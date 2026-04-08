<?php
session_start();
include_once __DIR__. '/../../function-includes/DBHelper.php';

$intTeamID =  $_REQUEST['teamid'] ?? '';
$intFilterID = $_REQUEST['filterid'] ?? '';
$intFilterType = $_REQUEST['filtertype'] ?? '';
$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intCallerPage = $_REQUEST['callerpage'] ?? '';
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

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

if($intFilterType == '0'){
    $strQuery = "UPDATE User_Web_Config SET CurrentFilter = :intFilterID WHERE (Login = :strLogin) AND (SchedulingTeamId = :intTeamID)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intFilterID', $intFilterID, PDO::PARAM_STR);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $strQuery = "UPDATE User_Web_Config SET CurrentFilter = :intFilterID WHERE (Login = :strLogin) AND (SchedulingTeamId = :intTeamID)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intFilterID', $intFilterID, PDO::PARAM_STR);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
}
?>