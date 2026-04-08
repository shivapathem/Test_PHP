<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();
$id = $_REQUEST["id"];

  $query = "DELETE FROM SortCodeMapping
            WHERE   (ID = $id)";

  $rsoff = sqlsrv_query($db, $query);
  echo $base;
?>