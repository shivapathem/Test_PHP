<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
  }
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
$id = $_REQUEST['id'] ?? 0;
$UserID = ($_COOKIE['editWeeklyUserId']) ?? $_SESSION['user']['UserID'];
$username = ($_COOKIE['editWeeklyUserFullName']) ?? ($_SESSION['user']['FullName']);
$history = 'Restored by '.$username.' on '.getDateTimeInEuropeTimezone(1, 0).' '.getDateTimeInEuropeTimezone(0, 1). '<hr>';
$history = escapeSingleQuotes($history);
$pdo = OpenDBLinkA7();  
  $status = 0;
  $strstatus = 'Success';
 try {
    $strQuery = "exec [dbo].[usp_UndeleteLeaveApplications] ?,?,?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->bindParam(2, $history, PDO::PARAM_STR);
    $stmt->bindParam(3, $UserID, PDO::PARAM_INT);
	  $stmt->bindParam(4, $status, PDO::PARAM_INT);
    $stmt->bindParam(5, $strstatus, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $resultJson = json_encode($result);

} catch(Exception $e) {
        logger()->critical('DB Error', (array) $e);
}
echo $resultJson;