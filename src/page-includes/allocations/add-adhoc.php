<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\Request;
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/genericfunctions.php';
include_once  'weekly/service/AllocationRepository.php';

$repository = new AllocationRepository();
$request = Request::createFromGlobals();

if($_REQUEST['task'] == 'addAdhocJob') {
	$ScheduledPersonID = trim($_REQUEST["StaffID"]);
	$StartDate = trim($_REQUEST["StartDate"]);
	$StartDate = (new Carbon( $StartDate));
	$EndDate = trim($_REQUEST["EndDate"]);
	$EndDate = (new Carbon( $EndDate));
	$StartTime = $_REQUEST["StartTime"];
	$EndTime = $_REQUEST["EndTime"];
	$Dutyname = trim($_REQUEST["DutyName"]);
	$SchedulingTeamID = trim($_REQUEST["SchedulingTeamID"]);
	$dutyColorId = trim($_REQUEST["DutyColorID"]);
	$Comment = ($_REQUEST["Comment"] != '') ? trim($_REQUEST["Comment"]) : NULL;
	$finalComment = $Comment . '__COMMENT_SEPARETOR__';
	$breakTimeHour = $_REQUEST["breakTimeHour"];
    $breakTimeMinute = $_REQUEST["breakTimeMinute"];
	$isNeedCovering =  $_REQUEST['isNeedCovering'];
    $strBreakTime = (($breakTimeHour * 3600) + ($breakTimeMinute * 60));

	if($StartTime < $EndTime) {
		$duration = $EndTime - $StartTime;
	} else {
		$duration = (86400 - $StartTime) + $EndTime;
	}

	if($StartTime == 0 && $EndTime == 0) {
		$EndTime = 86400;
	}

	$pdo = OpenDBLinkA7();
    $current_User = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
	if($strBreakTime > $duration) {
		$resultJson = "BreakTime-Error";
		echo $resultJson;
		return;
	}

	//Validation: -	Adhoc Duty with same name cannot be created for a person if that person already has an adhoc duty with same name for a day/days.
	try{
		$sql = "SELECT count(MasterDutyID) as totaladhocduty FROM MasterDuties WHERE
				TeamID = :SchedulingTeamID
				AND ScheduledPersonID = :ScheduledPersonID
				AND StartDate >= :StartDate
				AND EndDate <= :EndDate
				AND DutyTypeID = 6
				AND IsActive != 0
				";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':SchedulingTeamID', $SchedulingTeamID, PDO::PARAM_INT);
        $stmt->bindValue(':ScheduledPersonID', $ScheduledPersonID, PDO::PARAM_INT);
		$stmt->bindValue(':StartDate', $StartDate, PDO::PARAM_STR);
		$stmt->bindValue(':EndDate', $EndDate, PDO::PARAM_STR);

	    $stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if(isset($row['totaladhocduty']) && ($row['totaladhocduty'] > 0)){
			$resultJson = "StaffID-DutyName-Error";
			echo $resultJson;
			return;
		}
	} catch(PDOException $e) {
		$resultJson = $e->getMessage();
		echo $resultJson;
		return;
	}

	//Validation: Adhoc duty cannot be created if the week is already created for the selected team.
	try {
		$sql = "SELECT DISTINCT AL_WeekNumber WeekNumber from Allocations INNER JOIN TimeDimension td ON td.ixYearWeek = AL_WeekNumber WHERE AL_SchedulingTeamID = ? and td.ddatetime between Convert(datetime,?, 101) and Convert(datetime, ?, 101) and AL_Status in(1, 0)";

		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(1, $SchedulingTeamID, PDO::PARAM_INT);
        $stmt->bindParam(2, $StartDate, PDO::PARAM_STR);
		$stmt->bindParam(3, $EndDate, PDO::PARAM_STR);

	    $stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if(count($result) > 0 ){
			$resultJson = "Allocations-Exist-Error";
			echo $resultJson;
			return;
		}
	} catch(PDOException $e) {
		$resultJson = $e->getMessage();
		echo $resultJson;
		return;
	}
	$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

	$startDateWeek = GetAllocationWeekandDay($StartDate)['ixYearWeek'];
	$endDateWeek = GetAllocationWeekandDay($EndDate)['ixYearWeek'];
	$period = new CarbonPeriod($StartDate,$EndDate);
	$days = [];
	foreach($period->toArray() as $date) {
		$days[mb_strtolower($date->format('l'))] = 1;
	}
	// Creating transaction per day for the selected range and inserting into adhoc_duty.
	try{
		$pdo->beginTransaction();
		$ah_history = 'Created at '.date("H:i").' On '.date("d/m/Y").' by '.$_SESSION['user']['FullName']. '. Ad Hoc Duty : '.$Dutyname;
		$sql = "INSERT INTO MasterDuties
			(
			ScheduledPersonID,
			StartDate,
			EndDate,
			StartTime,
			EndTime,
			DutyName,
			TeamID,
			DutyComment,
			DutyColourID,
			BreakTime,
			Duration,
			CreatedDate,
			CreatedBy,
			LastModBy,
      		LastModDate,
			IsNeedCovering,
			DutyTypeID,
			StartWeek,
			EndWeek,
			Saturday,
			Sunday,
			Monday,
			Tuesday,
			Wednesday,
			Thursday,
			Friday,
			History
			)
			values ( :ScheduledPersonID, :StartDate , :EndDate , :StartTime , :EndTime , :Dutyname , :SchedulingTeamID , :Comment, :dutyColorId, :strBreakTime, :duration, GETUTCDATE(), :current_User, :LastModBy, GETUTCDATE(), :isNeedCovering, 6, :startWeek, :endWeek,
			:Saturday,
			:Sunday,
			:Monday,
			:Tuesday,
			:Wednesday,
			:Thursday,
			:Friday,
			:History
			)";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':ScheduledPersonID', $ScheduledPersonID, PDO::PARAM_INT);
		$stmt->bindValue(':StartDate', $StartDate, PDO::PARAM_STR);
		$stmt->bindValue(':EndDate', $EndDate, PDO::PARAM_STR);
		$stmt->bindValue(':StartTime', $StartTime, PDO::PARAM_STR);
		$stmt->bindValue(':EndTime', $EndTime, PDO::PARAM_STR);
		$stmt->bindValue(':Dutyname', $Dutyname, PDO::PARAM_STR);
		$stmt->bindValue(':SchedulingTeamID', $SchedulingTeamID, PDO::PARAM_INT);
		$stmt->bindValue(':dutyColorId', $dutyColorId, PDO::PARAM_INT);
		$stmt->bindValue(':strBreakTime', $strBreakTime, PDO::PARAM_STR);
		$stmt->bindValue(':duration', $duration, PDO::PARAM_STR);
		$stmt->bindValue(':Comment', $finalComment, PDO::PARAM_STR);
		$stmt->bindValue(':current_User', $current_User, PDO::PARAM_STR);
		$stmt->bindValue(':LastModBy', $current_User, PDO::PARAM_STR);
		$stmt->bindValue(':isNeedCovering', $isNeedCovering, PDO::PARAM_INT);
		$stmt->bindValue(':startWeek', $startDateWeek, PDO::PARAM_INT);
		$stmt->bindValue(':endWeek', $endDateWeek, PDO::PARAM_INT);
		$stmt->bindValue(':Saturday', isset($days['saturday']) ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':Sunday', isset($days['sunday']) ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':Monday', isset($days['monday']) ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':Tuesday', isset($days['tuesday']) ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':Wednesday', isset($days['wednesday']) ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':Thursday', isset($days['thursday']) ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':Friday', isset($days['friday']) ? 1 : 0, PDO::PARAM_INT);
		$stmt->bindValue(':History', $ah_history, PDO::PARAM_STR);
		$stmt->execute();

		$lastAhdutyId = $pdo->lastInsertId();
		$request->request->set('attributeId', $lastAhdutyId);
		$request->request->set('historyType', 11);
		$request->request->set('userId', $sessUserId);
		$request->request->set('message', $ah_history);
		$historyLog = $repository->addAllocationHistory($request);

		$pdo->commit();
		$resultJson =   'SUCCESS';
	}
	catch(PDOException $e){
		$pdo->rollBack();
		$resultJson = $e->getMessage();
	}
	echo  $resultJson;
	return;

}

if($_REQUEST['task'] == 'getStaffTeam') {

	$startdate = trim($_REQUEST["startDate"]);
	$enddate = trim($_REQUEST["endDate"]);
	$team_id = trim($_REQUEST["schedulingTeamId"]);
	try{
		$pdo = OpenDBLinkA7();
			$sql = "SELECT DISTINCT spl.ScheduledPersonID, UD_DisplayName FullName
			FROM ScheduledPersonTeam_LINK as spl WITH (NOLOCK)
			INNER JOIN UserDetails as sp WITH (NOLOCK) ON UD_UserID = spl.ScheduledPersonID
				WHERE  spl.TeamID = '".$team_id."'
				AND (spl.StartDate <= CONVERT(DATETIME, '".$startdate."', 102))
				AND (spl.EndDate >= CONVERT(DATETIME, '".$enddate."', 102))
				AND spl.IsHomeTeam = 1 AND spl.scheduledType = 1
				ORDER BY FullName";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$response_array = array('status' => 'success', 'result' => (array)$result);
		header('Content-type: application/json');
		echo json_encode($response_array);
	}
	catch(PDOException $e) {
		logger()->critical('DB Error', (array) $e);
   }
}

if($_REQUEST['task'] == 'adGetWeekNumber') {
	$selDate = $_REQUEST['seldate'];
	$getWeekandDayArr = GetAllocationWeekandDay($selDate);
	if($getWeekandDayArr) {
		$ixWeek = str_pad( (int) $getWeekandDayArr['ixWeekInYear'], 2, '0', STR_PAD_LEFT);
		$getWeekandDayArr['weekNumber'] = $ixWeek."/".(int) $getWeekandDayArr['ixYear'];
		$getWeekandDayArr['sDayName'] = $getWeekandDayArr['sDayName'];
	} else {
		$getWeekandDayArr['weekNumber'] = '';
		$getWeekandDayArr['sDayName'] = '';
	}
	echo json_encode($getWeekandDayArr);
}