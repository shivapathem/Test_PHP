<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/AllocationRepository.php';

$request = Request::createFromGlobals();
$repository = new AllocationRepository();

$response['success'] = false;
$isDeleted = $repository->removeFromWeek($request);
$response['success'] = true;
$response['errMsg'] = '';
if($isDeleted['SPExecStatus'] > 0){
	$response['success'] = false;
	$response['errMsg'] = $isDeleted['SPMessage'];
}

echo json_encode($response);