<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$add = $service->deleteAdhocDuty($request);

if($add){
    $response['success'] = true;
}else{
    $response['success'] = false;
}

echo json_encode($response);


