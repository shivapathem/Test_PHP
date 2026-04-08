<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
  }
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$intTeamID = $_POST["teamId"] ?? null;
$date = $_POST["dutydate"];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

$week= bbcweeknumber($date);
$day = $dowMap[date("D", strtotime($date))];
$db = OpenDatabase();

$arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);

// Get the Locl and Unlock status
 $pdo = OpenDBLinkA7();
  try {
	 $query = "SELECT  history
            FROM released_days (NOLOCK)
            WHERE (dDate = CONVERT(DATETIME, '$date 00:00:00', 102))
            AND (schedulingTeamId = $intTeamID)";
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$strHistory	='';
		if (empty($row)) {
			$strHistory.= "There is no history available.";
		} else {
			$strHistory.= $row['history'];
			if ($arrStaffOptions[$intTeamID]['isAdmin'] == 1 || $arrStaffOptions[$intTeamID]['isManager'] == 1 || $arrStaffOptions[$intTeamID]['isScheduler'] == 1 || $arrStaffOptions[$intTeamID]['isTeamAdmin'] == 1) {
				$strHistory.= "<br>This day become editable"." at ".date("G:i")." on ".date("d/m/Y") ."<br>It was made editable by ".$_SESSION['user']['FullName'];
			}
		}
		echo '<table class="tablesmallnoborder tooltip-table-signedtip" width="400px">';
        echo '<tr class="tooltip-table-background-signedtip">';
        echo '<td valign="top">' . $strHistory . '</td>';
        echo '</tr>';
	    echo '</table>';
	} catch (PDOException $e) {
        logger()->critical('DB error', (array)$e);
	}	
?>