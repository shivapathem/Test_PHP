<?php
/* Save scheduled people by week on team sort code*/
use Symfony\Component\HttpFoundation\Request;
require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationRepository.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";
$request = Request::createFromGlobals();
$repository = new AllocationRepository();
$sortcode_res = $repository->modAllocationScheduledPersonSortCode($request);
echo json_encode($sortcode_res);exit();

