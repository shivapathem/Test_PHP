<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/editabledaystatus.php';

$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$allocationId = $_POST['dutyId'] ?? NULL;
$allocationDutyID = $_POST['allocationDutyID'] ?? NULL;
$allocationSPID = $_POST['allocationSPID'] ?? NULL;
$strDate = $_POST['dutyDate'];
$scheduledPersonId = $_POST['schPersonId'];
$teamId = $_POST['teamId'];
$rolepermission = $_POST['rolepermission'];
$screen = isset($_POST['screen'])?$_POST['screen']:'';
if (isset($_POST['dutyDate'])) {
  $strCurrentDate = date("Y-m-d", strtotime($_POST['dutyDate']));
}
else {
  if (isset($_SESSION['allocattionsdate'])) {
    $strCurrentDate = $_SESSION['allocattionsdate'];
  }
  else {
    $strCurrentDate = date("Y-m-d");
  }
}
  
$arrTeamDefaults = GetTeamDefaults(0,$teamId); 
$arrEditable = DatIsEditable($strCurrentDate, $teamId, $arrTeamDefaults);
$lockUnlock = $arrEditable[0];

$pdo = OpenDBLinkA7();
try {
      $queryCheckAllocationPub = "SELECT ID FROM Allocations_Publish WHERE ID=".$allocationId;
      $stmtCheckAllocationPub = $pdo->prepare($queryCheckAllocationPub);
      $stmtCheckAllocationPub->execute();
      $resultSetPub = $stmtCheckAllocationPub->fetch(PDO::FETCH_ASSOC);
      if(empty($resultSetPub)){
        $query = "SELECT AllocationID, AllocationsDutyID, AllocationsSPID  FROM Allocations_Publish WHERE AllocationsDutyID = ".$allocationId;
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $rolepermission = 1;
        $allocationId = $result['AllocationID'];
        $allocationDutyID = $result['AllocationsDutyID'];
        $allocationSPID = $result['AllocationsSPID'];
      }

		$query = "exec [dbo].[usp_get_DutyPersonComment] ?,?,?,?";
		$stmt = $pdo->prepare($query);
		$stmt->bindParam(1, $rolepermission, PDO::PARAM_INT);
		$stmt->bindParam(2, $allocationId, PDO::PARAM_INT);
		$stmt->bindParam(3, $allocationDutyID, PDO::PARAM_INT);
		$stmt->bindParam(4, $allocationSPID, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		  
} catch (PDOException $e) {
       logger()->critical('db error', (array)$e);
}
 if (!empty($row)) {
  $strUserName = $row['FullName'];
  $strDutyName = $row['DutyName']; 
  if (isset($row['DutyComments']) && strpos($row['DutyComments'], '__COMMENT_SEPARETOR__') !== false) {
    $commentArr = explode('__COMMENT_SEPARETOR__', $row['DutyComments']);
    $row['PersonComments'] = isset($commentArr[0]) ? $commentArr[0] : '';
    $row['DutyComments'] = isset($commentArr[1]) ? $commentArr[1] : '';
  } 
  $strDutyComments = $row['DutyComments'];
  $strPersonComments = $row['PersonComments'] ? trim(preg_replace('/\[[^\]]*\]/', '', $row['PersonComments'])) : '';
 } else {
  $strUserName ='';
  $strDutyName ='';
  $strDutyComments='';
  $strPersonComments='';
 }
  
  $arrStaffOptions = GetStaffOtionsByTeam($strUser, $userId); 
  // What Can We view?
  if ((isset($arrStaffOptions[$teamId]['isScheduler']) && $arrStaffOptions[$teamId]['isScheduler'] == 1) || (isset($arrStaffOptions[$teamId]['isTeamAdmin']) && $arrStaffOptions[$teamId]['isTeamAdmin'] == 1)) {
    $intCanViewPersonComments = 1;
  } 
  elseif (isset($arrStaffOptions[$teamId]['isShiftLeader']) && $arrStaffOptions[$teamId]['isShiftLeader'] == 1 && $lockUnlock == 1) {
    $intCanViewPersonComments=1;
  }
  else {
    $intCanViewPersonComments = 0;
  }
  
  echo'<table class="tablesmalltidy" width="450px">';
  echo '<tr height="50px">';
  echo '<th colspan="2">Comments for '.$strUserName.'<br>Duty '.$strDutyName.'</th>';
  echo '</tr>';
  if ($strDutyComments != '') {
    echo '<tr>';
    echo '<td valign="top">Duty<br>Comments</td>';
    echo '<td>';
    echo nl2br($strDutyComments);
    echo '</td>';
    echo '</tr>';
  }
  if ($intCanViewPersonComments == 1 && $strPersonComments != '' && $screen=='') {
    echo '<tr>';
    echo '<td valign="top">Person<br>Comments</td>';
    echo '<td>';
    echo nl2br($strPersonComments);
    echo '</td>';
    echo '</tr>';
  }
  echo '</table>';