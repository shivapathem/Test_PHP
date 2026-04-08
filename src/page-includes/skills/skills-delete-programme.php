<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$id = $_REQUEST["id"];

  $query = "DELETE
            FROM skills_programmes
            WHERE  (ID = $id)";

  sqlsrv_query($db, $query);

  $query = "DELETE FROM  skills_programmes_staff_link
            WHERE  (programmes_id = $id)";

  sqlsrv_query($db, $query);

?>