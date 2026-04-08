<?php

session_start();
include_once '../../function-includes/init.php';
$id = $_REQUEST['id'];
$staffnumber = $_REQUEST['staffnumber'];

$db = OpenDatabase();


$query = "INSERT INTO user_favourites_staff_link
          (staffnumber, userfavouriteid)
          VALUES (N'$staffnumber', $id)";
echo $query; 
sqlsrv_query($db, $query);

 
?>