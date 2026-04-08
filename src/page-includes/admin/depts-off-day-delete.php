<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();
$id = $_REQUEST["id"];

  $query = "SELECT  base
            FROM workingdays
            WHERE  (id = $id) AND AllocateInstanceID = ". getCurrentInstanceId();

  $rsoff = sqlsrv_query($db, $query);
  $row = sqlsrv_fetch_array($rsoff);
  $base = $row['base'];

  $query = "DELETE FROM workingdays
            WHERE  (id = $id) AND AllocateInstanceID = ". getCurrentInstanceId();

  $rsoff = sqlsrv_query($db, $query);
  echo $base;
?>