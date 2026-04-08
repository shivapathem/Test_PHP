<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
  }
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/helpers.php';
$pdo = OpenDBLinkA7();
$id = $_REQUEST["id"];
$colour = str_replace('#', '', $_REQUEST["colour"]);
$query = "UPDATE  AllocationsTextColours
             SET   TextColour = :colour
             WHERE  (ID = :id )";
try {
   $stmt = $pdo->prepare($query);
   $stmt->bindParam(':colour', $colour, PDO::PARAM_STR);
   $stmt->bindParam(':id', $id, PDO::PARAM_STR);
   $stmt->execute();
}  catch (Exception $e) {
 logger()->critical('DB Error', (array) $e);
}
?>