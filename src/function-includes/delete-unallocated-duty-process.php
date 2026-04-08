<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
/**
 * --------------------------------------------
 * Description : This function is used to get Jobs Count Under Allocation Duty
 * Access : Public
 * Param : @dutyID int ,@teamID int
 * Return : This function return DutyName,AllocationID,jobcount under Duty
 * ---------------------------------------------
 */
function getJobsCountUnderAllocationDuty($dutyID,$teamID)
{
 try {
   $pdo = OpenDBLinkA7();
   $sql = "SELECT Allocations.DutyName ,Allocations.ID,COUNT(Allocations_jobs.ID) as jobcount
   FROM  Allocations
   LEFT OUTER JOIN  Allocations_jobs ON
   Allocations.ID = Allocations_jobs.AllocationID AND
   Allocations.SchedulingTeamId = Allocations_jobs.SchedulingTeamId AND (Allocations_jobs.SchedulingPersonID is NULL OR Allocations_jobs.SchedulingPersonID=0)
   WHERE (Allocations.ID = ? AND (Allocations.SchedulingPersonID is NULL OR Allocations.SchedulingPersonID =0)  AND Allocations.SchedulingTeamId = ?)
   GROUP BY  Allocations.DutyName, Allocations.ID, Allocations.SchedulingTeamId";
   $stmt = $pdo->prepare($sql);
   $stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
   $stmt->bindParam(2, $teamID, PDO::PARAM_INT);
   $stmt->execute();
   return $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  logger()->critical('DB Error', (array) $e);
}
return false;

}

 /**
 * --------------------------------------------
 * Description : This function is used to get Jobs Count Under Allocation Edit Duty
 * Access : Public
 * Param : @dutyID int ,@teamID int
 * Return : This function return DutyName,AllocationID,jobcount under Duty
 * ---------------------------------------------
 */

function getJobsCountUnderAllocationEditDuty($dutyID)
{
 try {
   $pdo = OpenDBLinkA7();
   $sql = "SELECT COUNT(aj.AJ_AllocateJobID) as jobcount FROM AllocationsJobs aj WHERE AJ_AllocationsDutyID = ? ";
   $stmt = $pdo->prepare($sql);
   $stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
   $stmt->execute();
   return $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  logger()->critical('DB Error', (array) $e);
}
return false;

}

/**
 * ----------------------------------------------------------------
 * Description : This function delete unallocated Duty with Jobs by Team from daily Allocation
 * Access : Public
 * Param  : @dutyID int for dutyId ,@teamID int for teamId ,$isEdited for Checking Edited status
 * Return : This function return response status and Message
 * ------------------------------------------------------------------
 */

function deleteUnallocatedDutyFromTeam($dutyID,$teamID,$weeknum, $isShiftleader)
{
  try {
		$pdo = OpenDBLinkA7();
		$current_User = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
		$current_UserName = $_SESSION['user']['FullName'];
		$status = 1;
		$activeflag =0;
		$strstatus = 'success';
    $sql = "exec usp_Edit_Allocations 'DELETEDUTY', ?, @Fromid=?";
		$stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $current_UserName, PDO::PARAM_STR);
		$stmt->bindParam(2, $dutyID, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$resultJson = json_encode($result);
		return $resultJson;
  }
  catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
  return false;
}

?>