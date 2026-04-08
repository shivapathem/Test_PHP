<?php
if (session_status() === PHP_SESSION_NONE) {
	  session_start(); 
}
date_default_timezone_set('Europe/London');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$intTeamID = $_REQUEST['teamId'];
$date = $_REQUEST['date'];
$period = $_REQUEST['period'];

$pdo = OpenDBLinkA7();
	echo '<div id="page">';
	// Before the form is submitted
	$date = $date.' '.'00:00:00';
    $sql = "SELECT comments, history
              FROM  GridChecks
              WHERE (period = ?) 
              AND (dDate = CONVERT(DATETIME, ?, 102)) 
              AND (SchedulingTeamId = ?)";
			  
	$stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $period, PDO::PARAM_INT);
    $stmt->bindParam(2, $date, PDO::PARAM_STR);
	$stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchall(PDO::FETCH_ASSOC);
    foreach ($result as $row) {	
	$comments = $row['comments'];
	$history = $row['history'];  
	}
  echo'<table class="redtable tooltip-table-signedtip" width="700px">';
  echo '<tr>';
  if ($period == 2) {
    echo '<th height="35px" colspan="2">Comments & History for '.date("D d M Y", strtotime($date)).'<br>1 Day Grid Checks</th>'; 
  }
  else {
    echo '<th height="35px" colspan="2">Comments & History for '.date("D d M Y", strtotime($date)).'<br>'.$period.' Day Grid Checks</th>';  
  }
  echo '</tr>';
  if ($comments != '') {  
    echo '<tr class="tooltip-table-background-signedtip">'; 
    echo '<td style="vertical-align: top;" width="100px">Comments</td>';
    echo '<td>';
    echo $comments;
    echo '</td>';
    echo '</tr>';
  }
  echo '<tr class="tooltip-table-background-signedtip">'; 
  echo '<td style="vertical-align: top;" width="100px">History</td>';
  echo '<td>';
  echo $history;
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';