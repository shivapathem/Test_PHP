<?php
session_start();
include_once '../../function-includes/init.php';

$id = $_REQUEST['id'];


$db = OpenDatabase();
$query = "SELECT dDate, approved
          FROM  dbo.LeaveApplications
          WHERE (ID = $id)";
$leave = sqlsrv_query($db, $query);
$row = sqlsrv_fetch_array($leave);

$approved = $row["approved"];  
$date = $row['dDate']->format('Y-m-d');

if ($approved == 0) {
  $setapproved = 1;
  $history = 'Leave approved by '.$_SESSION['user']['FullName'].' On '.date("d/m/Y").' '.date("H:i");
}
else {
  $setapproved = 0;
  $history = 'Leave un-approved by '.$_SESSION['user']['FullName'].' On '.date("d/m/Y").' '.date("H:i");
}

$query = "UPDATE  dbo.LeaveApplications
          SET
          Approved = $setapproved,
          History = CONCAT(ISNULL(History,''), '$history'),
          unlikely = 0,
          sent = 0
          WHERE (ID = $id)";

$leave = sqlsrv_query($db, $query); 


?>