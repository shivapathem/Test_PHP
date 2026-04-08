<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
  }
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$strUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intID = $_REQUEST['id'];
$pdo = OpenDBLinkA7();
$strHistory = "<hr>Lock deleted on ".date("jS M Y \a\t H:i")." by ".$_SESSION['user']['FullName'].".<br>";

$strHistory = escapeSingleQuotes($strHistory);  

try {
    $pdo = OpenDBLinkA7();
    $strQuery ="select iDay, WeekNumber, ScheduledPersonID from LockRequests where ID = ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
}
$intScheduledPersonID = $row["ScheduledPersonID"];  
$intWeekNumber = $row["WeekNumber"];
$intDay = $row["iDay"];

$strQuery = "UPDATE   LockRequests
             SET      deleted = 1, 
                      History = CONCAT(ISNULL(History,''), :strHistory)
             WHERE    (ID = :intID)";
try { 
   $stmt = $pdo->prepare($strQuery);
   $stmt->bindParam(':strHistory',$strHistory, PDO::PARAM_STR);
   $stmt->bindParam(':intID',$intID, PDO::PARAM_INT); 
   $stmt->execute();

   $query = "exec usp_UPDAllocationLockAndRequest @ScheduledPersonID = $intScheduledPersonID, @WeekNumber = $intWeekNumber, @iDay = $intDay, @DutyDate = NULL, @UserID = $strUserID";
   $stmt = $pdo->prepare($query);
   $stmt->execute();
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }          