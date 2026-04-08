<?php
session_start();
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ .'/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ .'/../../../../function-includes/DB_Functions.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$lastCreatedWeek = (int) $service->getLastCreatedWeek($request);
$returnData['status'] = false;
$weekMonth = '00';
$weekYear = '0000';
if($lastCreatedWeek > 0){
	$returnData['status'] = true;
	$splitLastCreatedWeek = str_split($lastCreatedWeek,4);
	$weekMonth = ($splitLastCreatedWeek[1] < 10)?'0'.$splitLastCreatedWeek[1]:$splitLastCreatedWeek[1];
	$weekYear = $splitLastCreatedWeek[0];
}
$returnData['lastCreatedWeekMonth'] = $weekMonth;
$returnData['lastCreatedWeekYear'] = $weekYear;
echo json_encode($returnData);