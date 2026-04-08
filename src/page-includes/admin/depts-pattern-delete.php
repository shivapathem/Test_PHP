<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$id = $_REQUEST["id"];


  $query = "DELETE
            FROM  RotaHoursMatch
            WHERE (ID = $id)";

sqlsrv_query($db, $query);
