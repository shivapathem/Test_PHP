<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$dutyID = $_REQUEST['dutyid'];
$weeknum = $_REQUEST['weeknumber'];
$teamID = $_REQUEST['teamId'];
$isShiftleader = $_REQUEST['isShiftleader'];

try {
		$pdo = OpenDBLinkA7();
		$current_User_Net = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
		$current_UserName = $_SESSION['user']['FullName'];
		$current_User = GetUserIdbyNetlogin($current_User_Net);
		$status = 1;
		$activeflag =1;
		$strstatus = 'success';
    	$sql = "exec [dbo].[usp_delete_restore_Unallocated_Duty] ?,?,?,?,?,?,?";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
		$stmt->bindParam(2, $weeknum, PDO::PARAM_INT);
		$stmt->bindParam(3, $teamID, PDO::PARAM_INT);
		$stmt->bindParam(4, $current_User, PDO::PARAM_INT);
		$stmt->bindParam(5, $current_UserName, PDO::PARAM_STR);
		$stmt->bindParam(6, $activeflag, PDO::PARAM_INT);
		$stmt->bindParam(7, $isShiftleader, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$resultJson = json_encode($result);
		return $resultJson;
  }
  catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
  return false;