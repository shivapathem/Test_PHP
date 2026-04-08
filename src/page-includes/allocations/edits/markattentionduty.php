<?php

date_default_timezone_set('UTC');
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';


$dutyid = $_REQUEST['dutyid'];
$isattention = $_REQUEST['isattention'];
$pdo = OpenDBLinkA7();

$backcolour = '#ffdbde';

$dutyhistory= "Marked for Attention by ".$_SESSION['user']['FullName']." on ".date("d/m/Y")." at ".date("H:i").".<hr>";
$dutyhistory = escapeSingleQuotes($dutyhistory);

$upQuery = "UPDATE Allocations_edit
            SET
            BackColour = :backcolour,
            IsAttention = :isattention,
            History = CONCAT(ISNULL(History,''), :dutyhistory)
            WHERE ID = :dutyid";
  
$stmt = $pdo->prepare($upQuery);
$stmt->bindValue(':backcolour', $backcolour, PDO::PARAM_STR);
$stmt->bindValue(':isattention', $isattention, PDO::PARAM_INT);
$stmt->bindValue(':dutyhistory', $dutyhistory, PDO::PARAM_STR);
$stmt->bindValue(':dutyid', $dutyid, PDO::PARAM_INT);
$stmt->execute();




  


  