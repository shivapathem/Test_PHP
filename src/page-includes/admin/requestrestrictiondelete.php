<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$id = $_REQUEST['id'];

$query = "DELETE FROM  RequestDatesClosed
          WHERE (id = $id)";

sqlsrv_query($db, $query);
?>