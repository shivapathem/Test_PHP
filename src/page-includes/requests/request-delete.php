<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$id = $_REQUEST['id'] ?? 0;
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$strHistory = escapeSingleQuotes("<hr>Deleted on ".date("jS M Y \a\t H:i")." by ".$_SESSION['user']['FullName']);

try {
    $pdo = OpenDBLinkA7();
    $strQuery ="exec [dbo].[usp_fetch_request_by_id] ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
}
$intScheduledPersonID = $row["ScheduledPersonID"];  
$dteRequestDate =  date('Y-m-d',strtotime($row['dDate']));

try {
    $pdo = OpenDBLinkA7();
    $query = "UPDATE Requests SET Approved = 0, Deleted = 1,History = CONCAT(ISNULL(History,''), '$strHistory'), unlikely = 0 WHERE (ID = :ID)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':ID', $id, PDO::PARAM_INT);
    $stmt->execute();

    $query = "exec usp_UPDAllocationLockAndRequest @ScheduledPersonID = $intScheduledPersonID, @WeekNumber = NULL, @iDay = NULL, @DutyDate = '".$dteRequestDate."', @UserID = $UserID";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
} catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
}
?>