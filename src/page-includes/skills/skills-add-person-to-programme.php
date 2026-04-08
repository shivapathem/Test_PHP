<?php
session_start();
include_once '../../function-includes/init.php';
$progid = $_REQUEST['progid'];
$staffid = $_REQUEST['staffid'];

$db = OpenDatabase();


$query = "INSERT INTO
          skills_programmes_staff_link (programmes_id, staff_id)
          VALUES (
          $progid,
          $staffid)";
echo $query; 
sqlsrv_query($db, $query);

 
?>