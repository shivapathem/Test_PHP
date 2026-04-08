<?php
session_start();
include_once '../../function-includes/init.php';
$progid = $_REQUEST['progid'];
$dutyid = $_REQUEST['dutyid'];

$db = OpenDatabase();
$query = "DELETE FROM skills_duties_programmes_link
          WHERE  (duties_id = $dutyid)
          AND (programmes_id = $progid)";

sqlsrv_query($db, $query);

?>