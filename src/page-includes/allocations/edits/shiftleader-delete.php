<?php
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctions.php';
$id = $_REQUEST['id'];

$pdo = OpenDBLinkA7();
    $history = "<hr>Shiftleader deleted by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i")."<br>";
    $history = escapeSingleQuotes($history); 
    $query = "UPDATE shiftleaders
              SET       
              deleted = 1, 
              history = CONCAT(ISNULL(History,''), '$history')
              WHERE (id = $id)";
$stmt = $pdo->prepare($query);
$stmt->execute();
?>