<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
date_default_timezone_set('Europe/London');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/DB_Functions.php';
  $allocationDutyId = $_REQUEST['id'];
  $moduleName = 'AllocationDuty';
  $pdo = OpenDBLinkA7();
  $scheduledPersonId = $_REQUEST['scheduledPersonId'] ?? 0;
  $allocationId = $_REQUEST['allocationId'] ?? 0;
  // Edited Duty
  $query = "SELECT AD_DutyName as DutyName, AD_IsDutyEdited as isEdited, ASP.ASP_AllocationsSPID as AllocationsSPID FROM  AllocationsDuties (NOLOCK)
  LEFT JOIN AllocationsScheduledPersons (NOLOCK) ASP on ASP.ASP_AllocationsDutyID = AD_AllocationsDutyID AND ASP.ASP_SchedulingPersonID = :scheduledPersonId
  WHERE AD_AllocationsDutyID = :allocationDutyId";
  $stmt = $pdo->prepare($query);
  $stmt->bindValue(':scheduledPersonId', $scheduledPersonId, PDO::PARAM_INT);
  $stmt->bindValue(':allocationDutyId', $allocationDutyId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $dutyName = trim($result['DutyName'] ?? 'U');
  $aspId = $result['AllocationsSPID'] ?? 0;

  $historyQuery = "WITH HT AS (
    SELECT History, datetime, ROW_NUMBER() OVER (PARTITION BY History ORDER BY datetime DESC) AS RowNum
        FROM (
            SELECT History, datetime FROM History (NOLOCK) WHERE HistoryType = 8 AND History IS NOT NULL AND AttributeID = :dutyId AND AttributeID != 0
            UNION ALL
            SELECT History, datetime FROM History (NOLOCK) WHERE HistoryType = 17 AND History IS NOT NULL AND AttributeID = :aspId
              AND AttributeID != 0
        ) x
    )
    SELECT History, datetime FROM HT
    WHERE RowNum = 1
    ORDER BY datetime DESC;
  ";
  $stmt = $pdo->prepare($historyQuery);
  $stmt->bindValue(':dutyId', $allocationDutyId, PDO::PARAM_INT);
  $stmt->bindValue(':aspId', $aspId, PDO::PARAM_INT);
  $stmt->execute();
  $historyResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if (count($historyResults) == 0) {
    $uDutyHistory = getWeekCreatedHistory($allocationId, $scheduledPersonId);
    $historyResults[] = [
      'HistorySubType' => 'PH',
      'History' => $uDutyHistory[0]['history']
    ];
  }
  echo '<table class="tablesmalltidy" width="600px">';
  echo '<tr height="30px">';
  echo '<th>History for Duty '.$dutyName.'</th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>';
  echo '<hr>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="historyholder  scrollbar-history-td">';
  if (count($historyResults) > 0) {
    foreach($historyResults as $key => $value) {
      if(!isset($value['History'])) {
        continue;
      }
      echo '<div style="white-space: pre-wrap;">';
	    $historydata=str_replace("<br>", "", $value['History']);
      if (strpos($historydata, '<hr>') !== false) {
      echo $historydata;
      } else  {
        echo $historydata."<hr>";
      }
      echo '</div>';
    }
  } else {
    echo '<div>';
    echo 'There is no History for this Duty yet!';
    echo '</div>';
  }
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td align="center">';
  echo '<input type="button" value="Done" onclick="cancel()">';
  echo '</td>';
  echo '</tr>';
  echo '</table>';