<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationRepository.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";
$repository = new AllocationRepository();
$request = Request::createFromGlobals();

$result = $repository->createDutyFromRota($request->get('allocationId'), $request->get('schedulingTeamId'));
if(isset($result[0]))
{
	echo json_encode($result[0]);
}else
{
	echo json_encode(array("SPExecStatus" => 0, "SPMessage" => 'Failed'));
}

