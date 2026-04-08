<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationRepository.php';

$request = Request::createFromGlobals();
$repository = new AllocationRepository();

try {
    $spResult = $repository->allocatedToUnallocated($request);
    $response['success'] = $spResult['spStatus'];
    $response['newId'] = $spResult['spData'][0]['UnAllocID'];
}catch(Exception $e){
    $response['success'] = false;
    $response['errors'] = $e->getMessage();
}
echo json_encode($response);
