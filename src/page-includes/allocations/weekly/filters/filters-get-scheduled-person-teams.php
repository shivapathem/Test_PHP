<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../service/Allocation.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../../../../function-includes/DB_Functions.php';

$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];
$teamId = $_POST['teamId'];
$pdo = OpenDBLinkA7();

$query = "select sl.ScheduledPersonID, sl1.TeamID
  from ScheduledPersonTeam_LINK sl
  inner join TimeDimension td on 1 = 1
  inner JOIN ScheduledPersonTeam_LINK sl1 ON sl1.ScheduledPersonID = sl.ScheduledPersonID
where td.dDateTime between sl.StartDate and sl.EndDate
AND td.dDateTime between sl1.StartDate and sl1.EndDate
   and sl.scheduledType = 1
   and sl1.scheduledType = 1
   and sl.TeamID = :teamId
   AND sl.TeamID != sl1.TeamID
   and td.dDateTime between :startDate1 and :endDate1
   GROUP BY sl.ScheduledPersonID, sl1.TeamID
   order by 1
  ";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':startDate1', $startDate, PDO::PARAM_STR);
$stmt->bindParam(':endDate1', $endDate, PDO::PARAM_STR);
$stmt->bindParam(':teamId', $teamId, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchall(PDO::FETCH_ASSOC);
$response = [];
foreach($result as $team) {
   $response[$team['ScheduledPersonID']][] = $team['TeamID'] ?? '';
}

header('Content-Type: application/json');

echo json_encode(['status' => (count($response) > 0 ? true : false), 'data' => $response]);