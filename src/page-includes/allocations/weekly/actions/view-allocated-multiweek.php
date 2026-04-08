<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../service/Allocation.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../../../../function-includes/DB_Functions.php';
include_once __DIR__ . '/../../../../function-includes/masterduty_filter_functions.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';

$request = $request ?? Request::createFromGlobals();
$service = new AllocationService();
$timeDimensionService = new TimeDimensionService();
$request = $service->prepareRequestDates($request);
$days = $request->get('days');
$teamId = $request->get('teamId');

$datePickerData = [];
$days = 7;
$queryDays = (($request->get('weeks') * $days));
$days = (($request->get('weeks') * $days)-1);
$dateRangeDays = $queryDays;

$request->request->set('startDate',$request->get('orgStartDate'));
$endDate = date('Y-m-d', strtotime($request->get('orgStartDate'). ' + '.$queryDays.' days'));
$request->request->set('days',$days);
$request->request->set('endDate',$endDate);
$request->request->set('endWeekDate',$endDate);

$request->request->set('showDataType','ALLOC');
$allocatedsdata  = $service->getEditWeeklyData($request);
$decodeallocateds = json_decode($allocatedsdata,true);
$allocateds = (isset($decodeallocateds['alloc']) && !empty($decodeallocateds['alloc'])) ? $decodeallocateds['alloc'] : [];

ini_set("zlib.output_compression", 1);
echo json_encode($allocateds); exit;
