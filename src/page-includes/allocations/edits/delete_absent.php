<?php
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
$dataUniqueId = $_REQUEST['dataUniqueId'];
$allocationId = $_REQUEST['allocationId'];
  /**
   * Description : Delete Duty From Mark Absent
   * @param $dataUniqueId int
   * return json response
   */
  function restoredDutyFromAbsent($dataUniqueId, $allocationId)
  {
   try {
     $pdo = OpenDBLinkA7();
     $current_UserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
     $netLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
     $markAbsent = 'UNMARKABSENT';
     $msgstatus = 'success';
     $sql = "exec [dbo].[usp_Edit_Allocations] ?,?,?,@pAllocationsID=".$allocationId;
     $stmt = $pdo->prepare($sql);
     $stmt->bindParam(1, $markAbsent, PDO::PARAM_STR);
     $stmt->bindParam(2, $netLogin, PDO::PARAM_STR);
     $stmt->bindParam(3, $dataUniqueId, PDO::PARAM_INT);
     $stmt->execute();
     $result = $stmt->fetch(PDO::FETCH_ASSOC);
     return json_encode($result);
     } catch (PDOException $e) {
     logger()->critical('DB Error', (array) $e);
      }
     return false;
 }
 echo restoredDutyFromAbsent($dataUniqueId, $allocationId);