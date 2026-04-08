<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$id = $_REQUEST["id"];


  $query = "Delete
            FROM skills_duties
            WHERE  (ID = $id)";

  sqlsrv_query($db, $query);


  $query = "DELETE FROM  skills_duties_programmes_link
            WHERE  (duties_id = $id)";

  sqlsrv_query($db, $query);
  
?>            
