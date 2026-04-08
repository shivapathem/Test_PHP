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

$query = "Select programmes_id ,
		UD_TeampayStaffID as staff_id,
		sptl.ScheduledPersonID as scheduledPersonID
    FROM skills_programmes_staff_link spl(nolock)
    INNER JOIN UserDetails ud(nolock) ON ud.UD_UserID =  spl.UserID
    INNER join ScheduledPersonTeam_LINK sptl (nolock) on sptl.ScheduledPersonID = ud.UD_UserID
    WHERE  CONVERT(DATETIME, :startDate1, 101) <=  isnull( sptl.enddate, CONVERT(DATETIME, :startDate2, 101) )
    AND CONVERT(DATETIME, :endDate1, 101)  >=  isnull( sptl.startdate, CONVERT(DATETIME, :endDate2, 101))
    AND sptl.scheduledType = 1 AND sptl.TeamID = :teamId
  ";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':startDate1', $startDate, PDO::PARAM_STR);
$stmt->bindParam(':endDate1', $endDate, PDO::PARAM_STR);
$stmt->bindParam(':startDate2', $startDate, PDO::PARAM_STR);
$stmt->bindParam(':endDate2', $endDate, PDO::PARAM_STR);
$stmt->bindParam(':teamId', $teamId, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchall(PDO::FETCH_ASSOC);
$response = [];
foreach ($result as $skill) {
  $response[$skill['scheduledPersonID']][] = $skill['programmes_id'] ?? '';
}

header('Content-Type: application/json');

echo json_encode(['status' => (count($response) > 0 ? true : false), 'data' => $response]);
