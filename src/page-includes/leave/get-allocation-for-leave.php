<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/../../../vendor/autoload.php";
require_once __DIR__ . '/../../page-includes/allocations/weekly/service/AllocationService.php';
include_once __DIR__ ."/../../function-includes/bootstrap.php";

$request = Request::createFromGlobals();
$service = new AllocationService();

$request->request->set('pdlStartDate', date('Y-m-d', strtotime($request->get('pdlStartDate'))));
$request->request->set('pdlEndDate', date('Y-m-d', strtotime($request->get('pdlEndDate'))));
$getAllocDetailsForLeave = $service->getAllocationDetailsForLeave($request);
$returnData['status'] = false;
$returnData['message'] = '';
$returnData['enableSbmtButton'] = 0;
if ($request->get('pdlStartDate') == $request->get('pdlEndDate')) {
    if (!empty($getAllocDetailsForLeave) && $getAllocDetailsForLeave[0]['ID'] != '') {
        switch ($getAllocDetailsForLeave[0]['DutyType']) {
            case "1":
                $returnData['message'] = 'Part Day Leave already applied and approved for the day';
                break;
            case "2":
                $returnData['message'] = 'Part Day Leave can not be applied on Sick marked day';
                break;
            case "3":
                $returnData['message'] = 'Full day leave already marked for the day';
                break;
            case "4":
                $returnData['message'] = 'Part Day Leave can not be applied on Absent day';
                break;
            case "5":
                $returnData['message'] = 'Part Day Leave can not be applied for the Misc. duty assigned day';
                break;
            case "6":
                $returnData['message'] = 'Assign any duty to apply for Part Day Leave';
                break;
            case "7":
                $returnData['message'] = 'Part Day Leave already applied for the day';
                break;
            default:
                $returnData['status'] = true;
                $request->request->set('id', $getAllocDetailsForLeave[0]['ID']);
                $request->request->set('getType', 'PDL');
                $dataAllocatedJobs = $service->getDutyAllocatedJobs($request);

                $dutyJobsStr = '';
                if (!empty($dataAllocatedJobs)) {
                    $dutyJobs = [];
                    foreach ($dataAllocatedJobs as $k => $v) {
                        $dutyJobs[$v['JobName']]['startTime'] = $v['StartTime'];
                        $dutyJobs[$v['JobName']]['endTime'] = $v['EndTime'];
                    }
                    if (!empty($dutyJobs)) {
                        $returnData['allocDutyJobs'] = json_encode($dutyJobs);
                    }
                }
                $getAllocDetailsForLeave[0]['dutyDuration'] = $getAllocDetailsForLeave[0]['Duration'] - $getAllocDetailsForLeave[0]['dutyBreakTime'];
                $returnData['allocDetails'] = $getAllocDetailsForLeave[0];
        }
    } else {
        $returnData['message'] = 'Week is not created for scheduled person on ' . date('d-M-Y', strtotime($request->get('pdlStartDate'))) . ' in any team.';
    }
} else {
    if (!empty($getAllocDetailsForLeave) && count($getAllocDetailsForLeave) > 0) {
        $pdlAppliedDate = [];
        foreach ($getAllocDetailsForLeave as $leaveDKey => $leaveDVal) {
            if ($leaveDVal['IsPDLApplied'] == 1) {
                $pdlAppliedDate[] = date('d-M-Y', strtotime($leaveDVal['DutyDate']));
            }
        }
        $returnData['enableSbmtButton'] = 1;
        // $returnData['message'] = 'From ' . date('d-M-Y', strtotime($request->get('pdlStartDate'))) . ' to ' . date('d-M-Y', strtotime($request->get('pdlEndDate'))) . '. Part Day Leave already applied for (' . implode(' , ', $pdlAppliedDate) . ').';
        $returnData['message'] = 'You cannot apply for a part day of leave over a date range.';
    }
}
echo json_encode($returnData);
