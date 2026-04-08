<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/CreateWeekService.php';

$request = Request::createFromGlobals();
$service = new CreateWeekService($request);
$isCreataed = $service->createWeek($request->get('force', false));

if($isCreataed){
    $response['success'] = true;
}else{
    $response['success'] = false;
}

echo json_encode($response);