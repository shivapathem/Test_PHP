<?php

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/PublishWeekService.php';
require_once __DIR__ . '/../service/Allocation.php';
$request = Request::createFromGlobals();
$request->request->set('teamId', $request->get('schedulingTeamId'));

$service = new PublishWeekService($request);

$response['send'] = $request->get('send');


$success = $service->update();
$response['success'] = $success;
$response['message'] = $success ? $service->getError() : null;


echo json_encode($response);