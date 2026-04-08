<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';

$strUser = GetUserLogon();
$leaveID = $_REQUEST['leaveId'];
$allocationid = $_REQUEST['allocationid'];
$currentuserid = $_SESSION['user']['StaffID'];
$username = isset($_SESSION['user']['FullName']) && !empty($_SESSION['user']['FullName']) ? $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
$pdlStartTime = 0;
$pdlEndTime = 0;
$isPDLAllow = isset($_POST['isPDLAllow']) ? $_POST['isPDLAllow'] : 0;
$currentuserid = isset($_COOKIE['editWeeklyUserId']) ? $_COOKIE['editWeeklyUserId'] : $_SESSION['user']['UserID'];
$pdo = OpenDBLinkA7();
$_SESSION['convert']=1;
$history = 'Converted to full day leave by ' . $username . ' On ' . getDateTimeInEuropeTimezone(1, 0, '') . ' ' . getDateTimeInEuropeTimezone(0, 1, '');
$x['status'] = false;
if(isset($_REQUEST['unapprovedPDL']) && $_REQUEST['unapprovedPDL'] == 1){
    $strUpdQuery = "UPDATE LeaveApplications SET LeaveStartTime=NULL, LeaveEndTime=NULL WHERE ID=".$leaveID;
    $stmtUpd = $pdo->prepare($strUpdQuery);
    if($stmtUpd->execute()){
        $x['status'] = true;
    }
	
	$attributeid = $leaveID; 
	$historytype = '15'; 
	$userid = $currentuserid;
	$message ='Converted unapproved part day of leave to full day leave by '.$username.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
	$status = 1;
	$historysubtype='NULL';
	PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);
	if ($allocationid>0){
		$attributeid = $allocationid; 
		$historytype = '8'; 
		$userid = $currentuserid;
		$message ='Converted unapproved part day of leave to full day leave by '.$username.' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, '');
		$status = 1;
		$historysubtype='PH';
		PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype);
	}
} else {
	$convert=$_SESSION['convert'];
    $strQuery = "exec [dbo].[usp_UnapproveLeave] ?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($strQuery);
	$stmt->bindParam(1, $leaveID, PDO::PARAM_INT);
	$stmt->bindParam(2, $history, PDO::PARAM_STR);
	$stmt->bindParam(3, $currentuserid, PDO::PARAM_INT);
	$stmt->bindParam(4, $username, PDO::PARAM_STR);
	$stmt->bindParam(5, $pdlStartTime, PDO::PARAM_INT);
	$stmt->bindParam(6, $pdlEndTime, PDO::PARAM_INT);
	$stmt->bindParam(7, $convert, PDO::PARAM_INT);
    if($stmt->execute()){
        $x['status'] = true;
    }
} 
echo json_encode($x);
exit;
?>