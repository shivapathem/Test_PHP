<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../vendor/autoload.php";
include_once '../../function-includes/bootstrap.php';
require_once __DIR__ . '/weekly/service/AllocationRepository.php';
$repository = new AllocationRepository();
$request = Request::createFromGlobals();
if($request->get('teamId') === null || $request->get('dates') === null) {
    $response['success'] = true;
    $response['data'] = [];
    echo json_encode($response);
    die;
}
$teamId = $request->get('teamId') ?? 0;
$data = $repository->getDateComment($teamId, $request->get('dates'));
$response['success'] = true;
$response['data'] = $data;
echo json_encode($response);


