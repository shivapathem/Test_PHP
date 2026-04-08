<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';

$id = $_REQUEST['id'];

$commonDbobj = new classCommonDBFunctions();

$sessUserId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLogin($sessUserId, 0),true);

$pdo = OpenDBLinkA7();
 $strQuery ="exec [dbo].[usp_fetch_request_by_id] :id";
  try { 
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':id',$id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }

$leaveCommentflag = 0;
if((strtolower($sessUserId) == strtolower($row['Login'])) || ($arrUserSettings['HasRequestsAdmin'] == 1)){
    $leaveCommentflag = 1;
}

 if (!empty($row)) {
  $name =  $row['FullName'];
  $date =  date("l, jS F Y", strtotime($row['dDate']));
  $strOfficeComments = $row['Comments'];
  $strUserComments = $row['UserComments'];
  echo'<table class="tablesmalltidy" width="400px">';
  echo'<tr height="35px">';
  echo'<th colspan="2">Comments for '.$name.'<br>On '.$date.'</th>';
  echo'</tr>';
  if ($strUserComments != '' && $leaveCommentflag == 1) {
    echo'<tr>';
    echo'<td valign="top" width="100px">User Comments</td>';
    echo'<td valign="top">'.$strUserComments.'</td>';
    echo'</tr>';
  }
  if ($strOfficeComments != '' && $leaveCommentflag == 1) {
    echo'<tr>';
    echo'<td valign="top" width="100px">Office Comments</td>';
    echo'<td valign="top">'.$strOfficeComments.'</td>';
    echo'</tr>';
  }

echo'</table>';
 }

?>