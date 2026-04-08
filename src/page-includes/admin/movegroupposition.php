<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once __DIR__. '/../../function-includes/DBHelper.php';
$pdo = OpenDBLinkA7();
$intID = $_POST['id'];
$intAction = $_POST['action'];

$strQuery = "exec [dbo].[usp_update_prodviewgroupposition] ?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intAction, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

die;
