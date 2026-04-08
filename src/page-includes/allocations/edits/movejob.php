<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService();
// The duty we are dropping on
$intDutyID = $_REQUEST['dutyid'];
$intJobID = $_REQUEST['jobid'];
$intDrpoPos = $_REQUEST['droppos'];
$date = $_REQUEST['date'];
$role = empty($_REQUEST["role"]) ? 0 : $_REQUEST["role"];
$midnightJob = $_REQUEST['midnightJob'];
$weekNumber =  $service->getWeekNumberByDate($date)['ixYearWeek'];
$iday = getdayofweek($date);
$dutyDetails = GetAllocationsDetailsByAllocationDutyId($intDutyID);
$arrCanDrop = candropjob($intDutyID, $intJobID, $intDrpoPos, $midnightJob);
$scheduledPersonId = $dutyDetails['SchedulingPersonID'] ?? '';
$teamId = $dutyDetails['SchedulingTeamId'] ?? 0;
if ($arrCanDrop['Success'] == 0) {
  // Now assign the job to the new duty ($intDestEditedDutyID)
  // Get the new ID of the job (edited)
  // Chage the allocation ID, Staff Number over
    $row = fetchEditedUnAllocationJob($intJobID);
    $jobInfor['JobID']  = $_REQUEST['shiftDrop'] == 1 ? 0 : $row["AllocateJobID"];
    $jobInfor['dutyDate'] = $row['DutyDate'];
    $jobInfor['dutyId'] = !empty($intDutyID) ? $intDutyID : 0;
    $jobInfor['Job'] = trim($row["JobName"]);
    $jobInfor['intStartTime'] = $arrCanDrop['StartTime'];
    $jobInfor['intEndTime'] = $arrCanDrop['EndTime'];
    $jobInfor['info'] = trim($row["Job_Info"] ?? '');
    $jobInfor['contact'] = trim($row["Contact"] ?? '');
    $jobInfor['location'] = trim($row["Location"] ?? '');
    $jobInfor['backcolor'] = trim($row["JobBackColour"]);
    $jobInfor['forecolor'] = trim($row["JobFontColour"]);
    $jobInfor['programme_id'] = trim($row["ProgrammeId"] ?? '');
    $jobInfor['team_id']  = trim($row["schedulingTeamId"]);
    $jobInfor['TaskType'] = 'updatejob';
    $jobInfor['aftermidnight'] = $arrCanDrop['aftermidnight'];
    $jobInfor['unallocated'] = 0;
    $jobInfor['JobEditID'] = $row["IsEditedJobAttention"] ?? 0;
    $jobInfor['allocationID'] = $row["AllocationID"];
    $jobInfor['WeekNumber'] = $weekNumber;
    $jobInfor['iDay'] = $iday;
    $jobInfor['role'] = $role ?? 0;
    $jobInfor['scheduledpersonid'] = $scheduledPersonId;
    $result = AddEditAllocationJob($jobInfor);
    $response = json_decode($result, true);
}
if (!empty($response)) {
  $finalArray = array("strstatus" => $response['SPExecStatus'], "strsmsg" => $response['SPMessage'], "candropstatus" => $arrCanDrop['Success']);
} else {
  $finalArray = array("strstatus" => NULL, "strsmsg" => NULL, "candropstatus" => $arrCanDrop['Success']);
}
echo json_encode($finalArray);
die();
