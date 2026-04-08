<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../../vendor/autoload.php';
include_once '../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../weekly/service/AllocationRepository.php';
include_once __DIR__ . '/../../users/process/classUserSetup.php';
$repository = new AllocationRepository();
$setupObj = new classUserSetup();
$request = Request::createFromGlobals();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$teamRole = $arrUsersTeamdata['Teams'][$request->get('teamId')] ?? [];
if(empty($teamRole)) {
    $response['message'] = 'Unauthorized';
    $response['success'] = false;
}
if(($teamRole['Scheduler'] == 1) || ($teamRole['SchedulingTeamAdmin'] == 1) || ($teamRole['TeamLeader'] == 1 || (isset($teamRole['ShiftLeader']) && $teamRole['ShiftLeader'] == 1)) ){
    $add = $repository->addEditDateComment($request);
    $response['message'] = 'Date comment added successfully';
    $response['success'] = true;
} else {
    $response['message'] = 'Unauthorized';
    $response['success'] = false;
}


echo json_encode($response);


