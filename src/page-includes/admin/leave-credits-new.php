<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';

$intTeamID = $_REQUEST['teamid'];
$intLeaveYear = $_REQUEST['year'];
$intLeaveType = $_REQUEST['LeaveType'];
$intLeaveAmount = $_REQUEST['Amount'];
$strLeaveComments = $_REQUEST['Comments'];
$strLeaveComments = str_replace("'", "''", $strLeaveComments);
$arrStaffNumbers =  $_REQUEST['staffnumbers'];
$strDate = $_REQUEST['sDate'];

$now = new DateTime("now", timezone: new DateTimeZone("Europe/London"));

$pdo = OpenDBLinkA7();
try {
	$strHistory = "New entry";
	$strHistory.= ' created by '.$_SESSION['user']['FullName'].' on '.$now->format("jS M Y").' at '.$now->format("H:i").'<hr>';
	$strDateNow = date("Y-m-d H:i:s");
	$arrAllocLeaveTypes = GetLeaveAllocateTypes();
	$loginuserid = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
	$strFieldName = $arrAllocLeaveTypes[$intLeaveType]['AllocName'];
	// History Part Needs to update as per current flow . Using Old Process
	
	foreach ($arrStaffNumbers as $arrID => $data) { 
      $userValue = explode('_',$data);
						$strStaffNumber = $userValue[0];
						$scheduledpersonId = $userValue[1];
			//Get the EFT      
			$rsEFT = getEFTByTeamByStaff($intTeamID,$scheduledpersonId); 
			if (isset($rsEFT) && ($rsEFT[0]['ScheduledPersonID']== $scheduledpersonId)) {
				$intPersonAmount = $intLeaveAmount * $rsEFT[0]['EFT'];
			} else {
				$intPersonAmount = $intLeaveAmount;
			}
			$intPersonAmount = ($intPersonAmount * 4) / 4;
			$intPersonAmount = customRoundOff($intPersonAmount);
			$strSQL = "INSERT INTO LeaveAllocation(StaffNumber, iYear, $strFieldName, dDate, Comments, SchedulingTeamid, WebCredit,SchedulingPersonID,CreatedDate,CreatedBy,UpdateDate,UpdatedBy) VALUES";      
			if ($intPersonAmount != 0) {
				
												$strSQL.= "('$strStaffNumber', 
												$intLeaveYear,
												$intPersonAmount,
												CONVERT(DATETIME, '$strDate', 102),
												'$strLeaveComments',
												$intTeamID, 
												1,
											$scheduledpersonId,
											 GETUTCDATE(),
											$loginuserid,
											GETUTCDATE(),
											$loginuserid),";
			}
					$strSQL = substr ($strSQL, 0, strlen($strSQL) -1);
					$stmt = $pdo->prepare($strSQL);
					if($stmt->execute()){
						$strQueryLastId = "SELECT TOP 1 ID FROM LeaveAllocation (NOLOCK) ORDER BY ID DESC";
				        $stmtLastId = $pdo->prepare($strQueryLastId);
				        $stmtLastId->execute();
				        $resLastId = $stmtLastId->fetch(PDO::FETCH_ASSOC);
				        $intID = $resLastId['ID'];
						//insert history
						$historytype=14;
						if($intID > 0){
							InsertHistory($historytype,$loginuserid,$strHistory,$strDateNow,$intID);
						}
					}
	}
} catch(Exception $e) {
	logger()->critical('DB Error', (array) $e);
}