<?php
session_start();
include_once '../../function-includes/init.php';
$progid = $_REQUEST['progid'];
$staffid = $_REQUEST['staffid'];

$db = OpenDatabase();

$query = "DELETE FROM skills_programmes_staff_link
          WHERE (programmes_id = $progid)
          AND (staff_id = $staffid)";

sqlsrv_query($db, $query);

?>