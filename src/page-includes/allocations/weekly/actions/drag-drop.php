<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/WTDService.php';
require_once __DIR__ . '/../service/HistoryService.php';
require_once __DIR__ . '/../service/DragDropHandler.php';
require_once __DIR__ . '/../service/Allocation.php';
require_once __DIR__ . "/../service/RequestService.php";

$request = Request::createFromGlobals();
$service = new AllocationService();
$handler = new DragDropHandler();

$source = $request->get('source');
$target = $request->get('target');


if($source['dataSource'] == 'allocated'){
    //check charging available or not
    $chargingStatus = $service->getDutyCharging($source['ID']);
    if($chargingStatus['chargingstatus'] == 1){
        $response['success'] = false;
        $response['errors'] = 'This duty cannot be deleted as it has one or more charging records are associated with it. Please remove charging records before trying to delete it';
        echo json_encode($response); die;
    }
}
$handler->setSourceType($source['dataSource'] ?? '');

try {
    $handler->handle($source, $target);
    if($handler->spStatus){
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['errors'] = $handler->spErrorMessage;
    }
}catch (\Exception $e){
    $response['success'] = false;
    $response['errors'] = $handler->getErrors();
}


echo json_encode($response);
