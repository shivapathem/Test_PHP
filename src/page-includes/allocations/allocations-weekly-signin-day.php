<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
date_default_timezone_set('Europe/London');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/DBHelper.php';

$curdate = $_REQUEST['sdate'];
$screenType = isset($_REQUEST['screenType']) ? $_REQUEST['screenType'] : 0;
$action = $_REQUEST['action'];
$allocationsDutyId = $_REQUEST['allocationsDutyId'];
$allocationsSpId = $_REQUEST['allocationsSpId'];
$startdate = date('Y-m-d',strtotime($curdate));

$pdo = OpenDBLinkA7();
$strFullName = $_SESSION['user']['FullName'];
$UserID = $_SESSION['user']['UserID'];

$dutyname = $_REQUEST['DutyName'] ?? null;
$starttimesecs = $_REQUEST['StartTime'] ?? null;
$endtimesecs = $_REQUEST['EndTime'] ?? null;
$SigninStatus = $_REQUEST['SigninStatus'] ?? null;
$intAllowSecs = (date("H") * 3600) +  (date("i") * 60) + 3600;
$inBuilding = 0;
$active = 0;
switch($action)
{
	case 0 	: //case of unmark sign in
			$history = '<font color="#FF0000">Marked as not signed in <br>By '.$strFullName.' on '. date("d/m/y") .' at '.date("H:i").'</font><hr>';
			if($SigninStatus == 2){
				$active = 0;
        $inBuilding = 0;
			}
			break;
	case 1	: // case of re-sigin in
			$history = '<font color="#00FF00">Signed in for:<br><b>'. $dutyname.'</b> ('.gmdate("H:i", $starttimesecs) .'-'.gmdate("H:i", $endtimesecs).') <br>By '.$strFullName.' on '. date("d/m/y") .' at '.date("H:i").'</font>';
			if($SigninStatus == 2){
				$history = '<font color="#00FF00">Re-Signed in for:<br><b>'. $dutyname.'</b> ('.gmdate("H:i", $starttimesecs) .'-'.gmdate("H:i", $endtimesecs).') <br>By '.$strFullName.' on '. date("d/m/y") .' at '.date("H:i").'</font><hr>';
			}
			$active = 1;
        $inBuilding = 0;
			break;
	case 2	: // case of building
			if(($startdate == date('Y-m-d')) && ($starttimesecs <= $intAllowSecs))
			{
				$history = '<font color="#00FF00">Signed in for:<br><b>'. $dutyname.'</b> ('.gmdate("H:i", $starttimesecs) .'-'.gmdate("H:i", $endtimesecs).')<br>Also marked as in the building.<br>By '.$strFullName.' on '. date("d/m/y") .' at '.date("H:i").'</font>';
				$active = 1;
        $inBuilding = 1;
			}
			break;
}
try
{
	$strQuery1 = "UPDATE Allocations_Publish SET SigninStatus = $active, SigninStartTime = $starttimesecs, SigninEndTime = $endtimesecs, SigninINBuilding = $inBuilding WHERE AllocationsDutyID = $allocationsDutyId AND AllocationsSPID = $allocationsSpId";


	$strQuery2 = "UPDATE AllocationsScheduledPersons SET ASP_SigninStatus = $active, ASP_SigninEndTime = $endtimesecs, ASP_SigninINBuilding = $inBuilding, ASP_SigninStartTime = $starttimesecs WHERE ASP_AllocationsDutyID = $allocationsDutyId AND ASP_AllocationsSPID = $allocationsSpId";

	$strQuery3 = "insert into AllocationsUpdated( AU_AllocationsDutyID, AU_AllocationsSPID, AU_Status, AU_UpdatedBy, AU_UpdatedDate) values($allocationsDutyId, $allocationsSpId, 0, $UserID, CURRENT_TIMESTAMP)";

	$pdo->beginTransaction();
	$stmt1 = $pdo->prepare($strQuery1);
	$stmt1->execute();

	$stmt2 = $pdo->prepare($strQuery2);
	$stmt2->execute();

	$stmt3 = $pdo->prepare($strQuery3);
	$stmt3->execute();

	$sql = "exec [dbo].[usp_mod_AllocationHistory] ?,10,?,?,1";
	$stmt4 = $pdo->prepare($sql);
	$stmt4->bindParam(1, $allocationsSpId, PDO::PARAM_INT);
	$stmt4->bindParam(2, $UserID, PDO::PARAM_INT);
	$stmt4->bindParam(3, $history, PDO::PARAM_STR);
	$stmt4->execute();
	$pdo->commit();
} catch(Exception $e) {
	$pdo->rollBack();
	logger()->critical('DB Error', (array) $e);
}
echo  $inBuilding;
?>
