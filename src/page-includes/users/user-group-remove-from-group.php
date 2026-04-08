<?php
session_start();
include_once '../../function-includes/init.php';
$id = $_REQUEST['id'];
$staffnumber = $_REQUEST['staffnumber'];

$db = OpenDatabase();

$query = "DELETE FROM user_favourites_staff_link
          WHERE  (StaffNumber = N'$staffnumber')
          AND (userfavouriteid = $id)";

sqlsrv_query($db, $query);
?>