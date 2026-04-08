<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../service/Allocation.php';
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../../../../function-includes/DB_Functions.php';
include_once __DIR__ . '/../../../../function-includes/masterduty_filter_functions.php';
require_once __DIR__ . '/../service/TimeDimensionService.php';

$request = $request ?? Request::createFromGlobals();
$service = new AllocationService();

$timeDimension = new TimeDimensionService();

$request = $service->prepareRequestDates($request);
$days = $request->get('days');

$days = 7;
$queryDays = (($request->get('weeks') * $days));
$days = (($request->get('weeks') * $days)-1);
$dateRangeDays = $queryDays;

$endDate = date('Y-m-d', strtotime($request->get('startDate'). ' + '.$queryDays.' days'));
$request->request->set('days',$days);
$request->request->set('endDate',$endDate);
$request->request->set('endWeekDate',$endDate);
$request->request->set('showDataType','ALL');
$unallocatedsdata  = $service->getEditWeeklyData($request);
$decodeunallocateds = json_decode($unallocatedsdata,true);
$unAllocateds = $decodeunallocateds['unalloc'];
$allocateds = $decodeunallocateds['alloc'];


$startWeek = $request->get('startWeek');
$periodArr = [];
for($p=0; $p<$request->get('weeks'); $p++){
	if($p == 0){
		$expWeekNum = explode('/',$startWeek);
		if((isset($expWeekNum[1])) && (!empty($expWeekNum))){
			$periodArr[$p]['weekNum'] = $expWeekNum[0];
			$periodArr[$p]['weekYear'] = $expWeekNum[1];
		} else {
			$periodArr[$p]['weekNum'] = substr(strval($startWeek),4,2);
			$periodArr[$p]['weekYear'] = substr(strval($startWeek),0,4);
		}
	} else {
		$expWeekNum = explode('/',$startWeek);
		if((isset($expWeekNum[1])) && (!empty($expWeekNum))){
			$startWeek = $timeDimension->findImmediateNextWeekOfCurrentWeek($expWeekNum[1].$expWeekNum[0]);
		} else {
			$startWeek = $timeDimension->findImmediateNextWeekOfCurrentWeek($startWeek);
		}
		$periodArr[$p]['weekNum'] = substr(strval($startWeek->ixYearWeek),4,2);
		$periodArr[$p]['weekYear'] = substr(strval($startWeek->ixYearWeek),0,4);
		$startWeek = $startWeek->ixYearWeek;
	}
}

$unqDutyIdArr = [];
usort($unAllocateds, function($x, $y) {
    return strnatcmp( strtolower($x[0] . $x[4] . $x[5]) , strtolower($y[0] . $y[4] . $y[5]));
});

foreach($unAllocateds as $unK => $unV){
    $unqDutyIdArr[$unV[0] . $unV[4] . $unV[5]][$unV[9]][] = $unV[15];
}

$unallocateddutynamearr = [];
foreach($unAllocateds as $unKe => $unVa){
	$unallocateddutynamearr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]['DutyInstances'] = $unVa[14];
	sort($unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
    $unAllocateds[$unKe]['InstanceIds'] = implode(',',$unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
	$unAllocateds[$unKe][14] = count($unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
}

$areaID = $_SESSION['user']['AreaID']??0;
$dutyType = 0;
$isArchived = 0;
$strsearch = '';
$getMiscDutyList = ListAllMasterDuties($areaID, $dutyType, $isArchived, $request->get('teamId'), $strsearch);
$getDayIndicatorData = getDayIndicatorData($request->get('teamId'), $request->get('startDate'), $endDate);

ini_set("zlib.output_compression", 1);
echo json_encode(array($unAllocateds, $allocateds, $periodArr, $unallocateddutynamearr, count($unallocateddutynamearr), $getMiscDutyList, $getDayIndicatorData));
die;