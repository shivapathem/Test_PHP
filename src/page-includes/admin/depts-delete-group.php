<?php
session_start();
include_once '../../function-includes/init.php';

include_once '../../function-includes/DBHelper.php';

$intID = $_POST['id'];

$pdo = OpenDBLinkA7();
$strQuery = "exec [dbo].[usp_del_prodviewgroup] ?";
$stmt = $pdo->prepare($strQuery);
$stmt->bindParam(1, $intID, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
