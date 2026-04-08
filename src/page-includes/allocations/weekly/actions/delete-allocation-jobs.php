<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationRepository.php';

$request = Request::createFromGlobals();
$repository = new AllocationRepository();

try {
    $response['success'] = $repository->deleteAllocationJobs($request);
}catch(Exception $e){
    $response['success'] = false;
    $response['errors'] = $e->getMessage();
}
echo json_encode($response);
