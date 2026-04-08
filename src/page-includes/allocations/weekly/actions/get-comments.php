<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ .'/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$data = $service->getAllocationComments($request);
if (isset($data['DutyComments']) && strpos($data['DutyComments'], '__COMMENT_SEPARETOR__') !== false) {
	$commentArr = explode('__COMMENT_SEPARETOR__', $data['DutyComments']);
	$data['PersonComments'] = isset($commentArr[0]) ? $commentArr[0] : '';
	$data['DutyComments'] = isset($commentArr[1]) ? $commentArr[1] : '';
}
if(!isset($data['DutyComments']))
{
	$data = [];
	$data['PersonComments'] = '';
	$data['DutyComments'] = '';
	$data['DutyName'] = 'U';
}

$returnData['status'] = true;
$returnData['data'] = $data;
echo json_encode($returnData);