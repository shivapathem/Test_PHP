<?php
// This file is used to Add History for AllocationJobs or AllocationDuty.
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../weekly/service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

if($service->addAllocationHistory($request)) {
    $res['intstatus'] = 1;
    $res['message'] = 'History saved successfully.';
} else {
    $res['intstatus'] = 0;
    $res['message'] = 'Error while saving History';
}
echo json_encode($res);