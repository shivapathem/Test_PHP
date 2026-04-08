<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start(); 
}
date_default_timezone_set('Europe/London');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$action = $_REQUEST['action'];
$intTeamID = $_REQUEST['teamId'];
$date = $_REQUEST['date'];
$period = $_REQUEST['period'];
$pdo = OpenDBLinkA7();

$dateTime = $date.' '.'00:00:00';
$strRequesterName = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
try{ 
	$sql = "SELECT COUNT(id) AS Countchecks
          FROM  GridChecks
          WHERE (dDate =  ?)
          AND (period = ?)
          AND (SchedulingTeamId = ?)";
		  
	$stmt = $pdo->prepare($sql); 
	$stmt->bindParam(1, $dateTime, PDO::PARAM_STR);
	$stmt->bindParam(2, $period, PDO::PARAM_INT);
	$stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchall(PDO::FETCH_ASSOC);  
}catch (PDOException $e) {
      echo $e->getMessage();
	  $result =  array();
 }	
 $row = $result[0] ?? [];
  if ($action == 1) {
    $history = "Marked as Checked with problems by ".$strRequesterName." on ".date("d/m/Y")." at ".date("H:i")."<br>";
  }
  else {
    $history = "Marked as Checked and OK by ".$strRequesterName." on ".date("d/m/Y")." at ".date("H:i")."<br>";
  }
  $history = escapeSingleQuotes($history);


if (isset($row['Countchecks']) && $row['Countchecks'] == 0) {
	try{ 
		$query = "INSERT INTO GridChecks(
            dDate,
            status,
            history,
            period,
            SchedulingTeamId)
            VALUES  (?,?,?,?,?)";

		$stmt = $pdo->prepare($query); 
		$stmt->bindParam(1, $dateTime, PDO::PARAM_STR);
		$stmt->bindParam(2, $action, PDO::PARAM_STR);
		$stmt->bindParam(3, $history, PDO::PARAM_STR);
		$stmt->bindParam(4, $period, PDO::PARAM_INT);
		$stmt->bindParam(5, $intTeamID, PDO::PARAM_INT);	 
		$stmt->execute();
	  }catch (PDOException $e) {
		  echo $e->getMessage();
	  }
  }
else { 
	try{ 
		$query = "UPDATE GridChecks
            SET
            status = ?,
            history = CONCAT(ISNULL(history,''), ?)
            WHERE (dDate =  ?)
            AND (SchedulingTeamId = ?)
            AND (period = ?)";

		$stmt = $pdo->prepare($query);
		$stmt->bindParam(1, $action, PDO::PARAM_STR);
		$stmt->bindParam(2, $history, PDO::PARAM_STR);
		$stmt->bindParam(3, $dateTime, PDO::PARAM_STR);
		$stmt->bindParam(4, $intTeamID, PDO::PARAM_INT);
		$stmt->bindParam(5, $period, PDO::PARAM_INT);		
		$stmt->execute();
	  }catch (PDOException $e) {
		  echo $e->getMessage();
	  }
}