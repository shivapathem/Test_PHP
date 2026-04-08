<?php
session_start();
include_once __DIR__. '/../../function-includes/DBHelper.php';

$intTeamID =  $_REQUEST['teamid'];
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$returnData['status'] = false;

$pdo = OpenDBLinkA7();

$strSQL = "SELECT id FROM User_Web_Config WHERE (Login = :strLogin) AND (SchedulingTeamId = :intTeamID)";
$stmt = $pdo->prepare($strSQL);
$stmt->bindParam(':intTeamID', $intTeamID, PDO::PARAM_INT);
$stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR); 
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if(!empty($row)){
 	$strSQL = "UPDATE User_Web_Config SET CurrentFilter=NULL WHERE (Login = :strLogin) AND (SchedulingTeamId = :intTeamID)";
    $stmt = $pdo->prepare($strSQL);
    $stmt->bindParam(':intTeamID', $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    if($stmt->execute()){
    	$returnData['status'] = true;
    }
}
echo json_encode($returnData);
?>