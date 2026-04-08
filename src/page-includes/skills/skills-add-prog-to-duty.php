<?php
session_start();
include_once '../../function-includes/init.php';
$progid = $_REQUEST['progid'];
$dutyid = $_REQUEST['dutyid'];

$db = OpenDatabase();


$query = "INSERT  
          INTO skills_duties_programmes_link(
          duties_id, 
          programmes_id)
          VALUES ($dutyid, $progid)";
 
sqlsrv_query($db, $query);

 
?>