<?php
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';

$intTeamID = $_REQUEST['teamId'];
$date = $_REQUEST['date'];

$week = bbcweeknumber($date);
$day = getdayofweek($date);

$pdo = OpenDBLinkA7();

// Lock the day for this Team.


try {
    $query = "UPDATE AD
    SET AD.AD_IsEditedDutyAttention = 0
    FROM Allocations AL
    INNER JOIN AllocationsDuties AD ON AL_AllocationsID = AD_AllocationsID
    WHERE AL_WeekNumber = ?
    AND AD_iDay = ?
    AND AL_SchedulingTeamID = ?
    AND AD_IsEditedDutyAttention = 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $week, PDO::PARAM_INT);
	  $stmt->bindParam(2, $day, PDO::PARAM_INT);
	  $stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }


?>