<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/genericfunctions.php';

$pdo = OpenDBLinkA7();

$allocationId = $_POST["dutyId"];
$rolePermission = $_POST["rolePermission"];

try {
    $Query = "exec [dbo].[usp_get_AllocationsSignIn] ?,?";
    $stmt = $pdo->prepare($Query);
    $stmt->bindParam(1, $rolePermission, PDO::PARAM_INT);
    $stmt->bindParam(2, $allocationId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    logger()->critical('DB Error', (array)$e);
}

$SchedulingPersonID = $row['SchedulingPersonID'];
$intWeek = $row['WeekNumber'];
$intDay = $row['iDay'];

$intstartHour = intval($row["StartTime"] / 3600);
$intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
$intstartMinute = intval(($row["StartTime"] % 3600) / 60);
$intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
$startTime = $intstartHour . ':' . $intstartMinute;

$endtime = gmdate("H:i", $row["EndTime"] + 1);

$strDutyName = $row['DutyName'];
if (!is_null($row['StartTime'])) {
    $strDutyName .= '<br>' . $startTime . '-' . $endtime;
}

try {
    $strQuery = "SELECT H.History  FROM  AllocationsDuties (NOLOCK)
		INNER JOIN Allocations (NOLOCK) AL on AL.AL_AllocationsID = AD_AllocationsID
		INNER JOIN AllocationsScheduledPersons (NOLOCK) ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID
		INNER JOIN History (NOLOCK) H ON ASP.ASP_AllocationsSPID = H.AttributeID and H.HistoryType=10
        WHERE  AD_AllocationsDutyID = :allocationDutyId";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':allocationDutyId', $allocationId, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    logger()->critical('DB Error', (array)$e);
}

if (empty($result)) {
    echo '<table class="tablesmallnoborder tooltip-table-signedtip" width="400px">';
    echo '<tr height="35px">';
    echo '<th valign="top">' . $strDutyName . '</th>';
    echo '</tr>';
    echo '<tr class="tooltip-table-background-signedtip">';
    echo '<td valign="top">This duty has not been signed in.</td>';
    echo '</tr>';
    echo '</table>';

} else {
    echo '<table class="tablesmallnoborder tooltip-table-signedtip" width="400px">';
    echo '<tr>';
    echo '<th valign="top" height="35px">' . $strDutyName . '</th>';
    echo '</tr>';
    foreach ($result as $row) {
        echo '<tr class="tooltip-table-background-signedtip">';
        echo '<td valign="top">' . $row['History'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
?>