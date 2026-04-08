<?php

use Symfony\Component\HttpFoundation\Request;

include_once '../../../function-includes/init.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../page-includes/allocations/weekly/service/AllocationRepository.php';

$request = Request::createFromGlobals();
$repository = new AllocationRepository();

if (!empty($_REQUEST['submit'])) {
  $jobInfor['JobID']  = $_REQUEST["JobID"] ?? 0;
  $jobInfor['Job'] = trim($_REQUEST["Job"]);
  $jobInfor['intStartTime'] = seconds_from_time($_REQUEST["StartTime"]);
  $jobInfor['intEndTime'] = seconds_from_time($_REQUEST["EndTime"]);
  $jobInfor['info'] = trim($_REQUEST["info"]);
  $jobInfor['contact'] = trim($_REQUEST["contact"]);
  $jobInfor['location'] = trim($_REQUEST["location"]);
  $jobInfor['backcolor'] = trim($_REQUEST["backcolor"]);
  $jobInfor['forecolor'] = trim($_REQUEST["forecolor"]);
  $jobInfor['programme_id'] = trim($_REQUEST["programme_id"]);
  $jobInfor['team_id']  = trim($_REQUEST["scheduling_team"]);
  $jobInfor['TaskType'] = 'updatejob';
  $jobInfor['aftermidnight'] = $_REQUEST["aftermidnight"] ?? 0;
  $jobInfor['unallocated'] = $_REQUEST["unallocated"] ?? 0;
  $jobInfor['JobEditID'] = $_REQUEST["JobID"] ?? 0;
  $jobInfor['allocationID'] = $_REQUEST["allocationID"] ?? 0;
  $jobInfor['WeekNumber'] = $_REQUEST["WeekNumber"];
  $jobInfor['iDay'] = $_REQUEST["iDay"];
  $jobInfor['dutyDate'] = $_REQUEST["dutyDate"];
  $jobInfor['dutyId'] = $_REQUEST["dutyId"] ?? 0;
  $jobInfor['role'] = $_REQUEST["role"] ?? 0;
  $jobInfor['scheduledpersonid'] = $_REQUEST["scheduledpersonid"] ?? '';
  if ($jobInfor['scheduledpersonid'] != '' && $_REQUEST["dutyId"] != 0) {
    $request->request->set('id', $_REQUEST["dutyId"]);
  }
  $dutyComment = $_REQUEST['DutyComments'] ?? '';
  $personComment = $_REQUEST['PersonComments'] ?? '';
  $oldDutyComments = $_REQUEST['oldDutyComments'] ?? '';
  $oldPersonComments = $_REQUEST['oldPersonComments'] ?? '';

  /* Below function is used for ADD /EDIT/COPY task */

  $willfit = checkJobOverlap($jobInfor['dutyId'], $jobInfor['intStartTime'], $jobInfor['intEndTime'], $jobInfor['aftermidnight'], $jobInfor['JobID']);

  if (($willfit == 0) ||  ($jobInfor['unallocated'] == 1)) {
    $response = AddEditAllocationJob($jobInfor);

    //Add comments
    if ($jobInfor['dutyId'] != 0) {
		if(!empty($dutyComment) && !empty($personComment))
		{
			if(($oldDutyComments != $dutyComment) && ($oldPersonComments != $personComment))
			{
				$repository->setComment($jobInfor['allocationID'], $jobInfor['dutyDate'], $jobInfor['scheduledpersonid'], $jobInfor['role'], $dutyComment, 0, $jobInfor['dutyId']);
			}else
			{
				if ($oldDutyComments != $dutyComment)
				{
					$repository->setComment($jobInfor['allocationID'], $jobInfor['dutyDate'], $jobInfor['scheduledpersonid'], $jobInfor['role'], $dutyComment, 1, $jobInfor['dutyId']);
				}
				if ($oldPersonComments != $personComment)
				{
					$repository->setComment($jobInfor['allocationID'], $jobInfor['dutyDate'], $jobInfor['scheduledpersonid'], $jobInfor['role'], $personComment, 2);
				}
			}
		}elseif(!empty($dutyComment))
		{
			if ($oldDutyComments != $dutyComment) {
				$repository->setComment($jobInfor['allocationID'], $jobInfor['dutyDate'], $jobInfor['scheduledpersonid'], $jobInfor['role'], $dutyComment, 1, $jobInfor['dutyId']);
			}
		}
		elseif(!empty($personComment))
		{
			if ($oldPersonComments != $personComment) {
				$repository->setComment($jobInfor['allocationID'], $jobInfor['dutyDate'], $jobInfor['scheduledpersonid'], $jobInfor['role'], $personComment, 2);
			}
		}
    }

    $response = json_decode($response, true);
    $finalArray = array("intstatus" => $response['SPExecStatus'], "strstatus" => $response['SPMessage']);
  } else {
    $finalArray = array("intstatus" => 1, "strstatus" => "The job you are trying to add will conflict with existing job. Please try again.");
  }
  echo json_encode($finalArray);
  die();
}
