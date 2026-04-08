<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
  }
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/userfunctions.php';

$intID = $_POST['id'];
$intWeekNumber = $_POST['weeknumber'];
$strHistory = "Locks denied removed for week ".spinweek($intWeekNumber)." restriction remo on ".date("d/m/Y")." at ".date("H:i")." by ".$_SESSION['user']['FullName'].".<br><br>";
$strHistory = escapeSingleQuotes($strHistory);
$pdo = OpenDBLinkA7();
  try {
    $strQuery = "UPDATE LockRequestsRestricted
    SET History = CONCAT(ISNULL(History,''), :strHistory), 
    Deleted = 1
    WHERE (ID = :intID)";         
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(":strHistory",$strHistory, PDO::PARAM_STR);
    $stmt->bindParam(":intID",$intID, PDO::PARAM_INT);
    $stmt->execute();
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
