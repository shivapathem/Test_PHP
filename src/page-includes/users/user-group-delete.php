<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$id = $_REQUEST['id'];

$query = "DELETE FROM  user_favourites
          WHERE  (id = $id)";
sqlsrv_query($db, $query); 

$query = "DELETE FROM user_favourites_staff_link
          WHERE  (userfavouriteid = $id)";
sqlsrv_query($db, $query);  
?>