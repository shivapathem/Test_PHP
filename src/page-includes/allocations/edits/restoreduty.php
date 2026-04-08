<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
$allocationId = $_REQUEST['allocationId'];
$allocationSPId = $_REQUEST['allocationSPId'];
$isEditedStatus = $_REQUEST['isEdited'];
$isShiftleader = $_REQUEST['isShiftleader'] ?? 0;
$dutyName = strtolower($_REQUEST['dutyName']);
$teamid = $_REQUEST['teamId'];
  /**
   * Description : Restored Duty From Mark Absent | Leave |Other
   * @param $allocationSPId int ,$teamid int,$isEditedStatus int
   * return json response
   */
  function restoredDutyFromAbsent($allocationSPId,$teamid,$isEditedStatus,$isShiftleader,$dutyName)
  {
   try {
     $pdo = OpenDBLinkA7();
     $current_UserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
     $current_UserName = $_SESSION['user']['FullName'];
     $status = 1;
     $msgstatus = 'success';
     $sql = "exec [dbo].[usp_mod_RestoreDuty_From_Sick_Leave_Absent] ?,?,?,?,?,?,?,?,?";
     $stmt = $pdo->prepare($sql);
     $stmt->bindParam(1, $allocationSPId, PDO::PARAM_INT);
     $stmt->bindParam(2, $teamid, PDO::PARAM_INT);
     $stmt->bindParam(3, $isEditedStatus, PDO::PARAM_INT);
     $stmt->bindParam(4, $current_UserId, PDO::PARAM_INT);
     $stmt->bindParam(5, $current_UserName, PDO::PARAM_STR);
     $stmt->bindParam(6, $status, PDO::PARAM_INT);
     $stmt->bindParam(7, $msgstatus, PDO::PARAM_STR);
     $stmt->bindParam(8, $isShiftleader, PDO::PARAM_INT);
     $stmt->bindParam(9, $dutyName, PDO::PARAM_STR);
     $stmt->execute();
     $result = $stmt->fetch(PDO::FETCH_ASSOC);
     return json_encode($result);
     } catch (PDOException $e) {
     logger()->critical('DB Error', (array) $e);
      }
     return false;
 }
 echo restoredDutyFromAbsent($allocationSPId,$teamid,$isEditedStatus,$isShiftleader,$dutyName);