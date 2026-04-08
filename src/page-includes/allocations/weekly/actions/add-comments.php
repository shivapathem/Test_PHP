<?php

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
if((!empty($request->get('DutyComments')) || ($request->get('DutyComments') != $request->get('oldDutyComments'))) && (!empty($request->get('PersonComments')) || ($request->get('PersonComments') != $request->get('oldPersonComments'))))
{
	$request->request->set('pCommentType', 0);
}elseif(!empty($request->get('DutyComments')) || ($request->get('DutyComments') != $request->get('oldDutyComments')) )
{
	$request->request->set('pCommentType', 1);
}elseif(!empty($request->get('PersonComments')) || ($request->get('PersonComments') != $request->get('oldPersonComments')))
{
	$request->request->set('pCommentType', 2);
}
$add = $service->addCommentsToAllocations($request);
if($add){
    $response['success'] = true;
}else{
    $response['success'] = false;
}

echo json_encode($response);


