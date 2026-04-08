<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$addPerson = $service->addPersonToAllocations($request);

if($addPerson['SPExecStatus'] == 0){
    $response['success'] = true;
}else{
    $response['success'] = false;
    $response['errMsg'] = $addPerson['SPMessage'];
}

echo json_encode($response);


