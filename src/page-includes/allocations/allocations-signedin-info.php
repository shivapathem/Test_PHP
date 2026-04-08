<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/genericfunctions.php';

$pdo = OpenDBLinkA7();
$allocationsSPID = $_REQUEST["allocationsSPID"] ?? 0;
$intstartHour = isset($_POST["StartTime"]) ? intval($_POST["StartTime"] / 3600) : null;
$intstartHour = $intstartHour !== null ? (strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour) : "00";
$intstartMinute = isset($_POST["StartTime"]) ? intval(($_POST["StartTime"] % 3600) / 60) : null;
$intstartMinute = $intstartMinute !== null ? (strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute) : "00";
$startTime = $intstartHour . ':' . $intstartMinute;
$endtime = isset($_POST["EndTime"]) ? gmdate("H:i", $_POST["EndTime"] + 1) : null;
$strDutyName = $_POST['DutyName'] ?? null;
$class1 = (isset($_POST['isDaily']) && ($_POST['isDaily'] == 1)) ? 'tooltip-table-background-signedtip' : 'tooltip-table-background';
$class2 = (isset($_POST['isDaily']) && ($_POST['isDaily'] == 1)) ? 'tooltip-table-signedtip' : 'tooltip-table';
if(isset($_POST['StartTime']))
{
    $strDutyName .= '<br>' . $startTime . '-' . $endtime;
}

try {
    $strQuery = "select History from History where HistoryType = 10 AND AttributeID = $allocationsSPID";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $allocationsSPID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    logger()->critical('DB Error', (array)$e);
}

if (empty($result)) {
    echo '<table class="tablesmallnoborder '.$class2.'" width="400px">';
    echo '<tr height="35px">';
    echo '<th valign="top">' . $strDutyName . '</th>';
    echo '</tr>';
    echo '<tr class="'.$class1.'">';
    echo '<td valign="top">This duty has not been signed in.</td>';
    echo '</tr>';
    echo '</table>';

} else {
    echo '<table class="tablesmallnoborder '.$class2.'" width="400px">';
    echo '<tr>';
    echo '<th valign="top" height="35px">' . $strDutyName . '</th>';
    echo '</tr>';
    foreach ($result as $row) {
        echo '<tr class="'.$class1.'">';
        echo '<td valign="top">' . $row['History'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
?>