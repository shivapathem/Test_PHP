<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/editabledaystatus.php';

$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$allocationDutyId = $_POST['dutyId'];
$strDate = $_POST['dutyDate'];
$scheduledPersonId = $_POST['schPersonId'];
$teamId = $_POST['teamId'];
$rolepermission = $_POST['rolepermission'];
$allocationSPID = $_POST['allocationSPID'] ?? 0;
$allocationID = $_POST['allocationID'] ?? 0;

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
		$query = "exec [dbo].[usp_get_DutyPersonComment] ?,?,?,?";
		$stmt = $pdo->prepare($query);
    //For shift leader we will be using Allocations_Publish row id
    $allocationIdParam = $rolepermission == 1 ? $allocationID : $allocationDutyId;
		$stmt->bindParam(1, $rolepermission, PDO::PARAM_INT);    
		$stmt->bindParam(2, $allocationIdParam, PDO::PARAM_INT);
		$stmt->bindParam(3, $allocationDutyId, PDO::PARAM_INT);
		$stmt->bindParam(4, $allocationSPID, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
       logger()->critical('db error', (array)$e);
}
  $strUserName = $row['FullName'];
  $strDutyName = $row['DutyName'];  
  if (isset($row['DutyComments']) && strpos($row['DutyComments'], '__COMMENT_SEPARETOR__') !== false) {
    $commentArr = explode('__COMMENT_SEPARETOR__', $row['DutyComments']);
    $row['PersonComments'] = isset($commentArr[0]) ? $commentArr[0] : '';
    $row['DutyComments'] = isset($commentArr[1]) ? $commentArr[1] : '';
  }  
  $strDutyComments = $row['DutyComments'];
  $strPersonComments = trim(preg_replace('/\[[^\]]*\]/', '', $row['PersonComments'] ?? ''));
  $arrStaffOptions = GetStaffOtionsByTeam($strUser, $userId);
  // What Can We view?
    if (($arrStaffOptions[$teamId]['isScheduler'] == 1) || ($arrStaffOptions[$teamId]['isTeamAdmin'] == 1)){
    $intCanViewPersonComments = 1;
  }
  elseif ($arrStaffOptions[$teamId]['isShiftLeader'] == 1 && $lockUnlock==1){
    $intCanViewPersonComments=1;
  }
  else {
    $intCanViewPersonComments = 0;
  }

  echo'<table class="tablesmalltidy tooltip-table-signedtip" width="450px">';
  echo '<tr height="50px">';
  echo '<th colspan="2">Comments for '.$strUserName.'<br>Duty '.$strDutyName.'</th>';
  echo '</tr>';
  if ($strDutyComments != '') {
    echo '<tr class="tooltip-table-background-signedtip">';
    echo '<td valign="top">Duty<br>Comments</td>';
    echo '<td>';
    echo nl2br($strDutyComments);
    echo '</td>';
    echo '</tr>';
  }
  if ($intCanViewPersonComments == 1 && $strPersonComments != '') {
    echo '<tr class="tooltip-table-background-signedtip">';
    echo '<td valign="top">Person<br>Comments</td>';
    echo '<td>';
    echo nl2br($strPersonComments);
    echo '</td>';
    echo '</tr>';
  }
  echo '</table>';