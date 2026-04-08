<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// header('Content-Encoding: gzip');
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
if ($request->get('weeks') > 1) {
    $days = 7;
    $queryDays = (($request->get('weeks') * $days));
    $days = (($request->get('weeks') * $days) - 1);
    $dateRangeDays = $queryDays;

    $endDate = date('Y-m-d', strtotime($request->get('startDate') . ' + ' . $queryDays . ' days'));
    $request->request->set('days', $days);
    $request->request->set('endDate', $endDate);
    $request->request->set('endWeekDate', $endDate);
}else
{
	$days = 7;
    $queryDays = (($request->get('weeks') * $days));
    $days = (($request->get('weeks') * $days) - 1);
    $endDate = date('Y-m-d', strtotime($request->get('startDate') . ' + ' . $queryDays . ' days'));
}
if ($request->get('date') != '') {
    $request->request->set('startDate', date('Y-m-d', strtotime($request->get('date'))));
    $endDate = date('Y-m-d', strtotime($request->get('startDate') . ' +7 days'));
    $request->request->set('endDate', $endDate);

    $weekNumber = $timeDimensionService->findByDateWithoutCarbon($request->get('startDate'));
    $week = substr(strval($weekNumber->ixYearWeek), 4, 2);
    $year = substr(strval($weekNumber->ixYearWeek), 0, 4);
    $nextWeek = $timeDimensionService->findImmediateNextWeekOfCurrentWeek($year . $week);
    $nextWeekNum = substr(strval($nextWeek->ixYearWeek), 4, 2);
    $nextWeekYear = substr(strval($nextWeek->ixYearWeek), 0, 4);
    $datePickerData = [
        'dateWeekNumber' => $week,
        'dateWeekYear' => $year,
        'nextWeekNum' => $nextWeekNum,
        'nextWeekYear' => $nextWeekYear,
        'startDate' => $request->get('startDate'),
        'endDate' => $endDate,
    ];
}
$request->request->set('endDate', date('Y-m-d', strtotime($request->get('endDate') . ' -1 days')));
$getDayIndicatorData = '';
if ($request->get('showDataType') == 'ALL') {
    $alleditWeeklyData = $service->getEditWeeklyData($request);
    $decodealldata = json_decode($alleditWeeklyData, true);
    $unAllocatedDutyData = (isset($decodealldata['unalloc']) && !empty($decodealldata['unalloc'])) ? $decodealldata['unalloc'] : [];
    $allocatedDutyData = (isset($decodealldata['alloc']) && !empty($decodealldata['alloc'])) ? $decodealldata['alloc'] : [];
    if (!empty($unAllocatedDutyData)) {
        usort($unAllocatedDutyData, function($x, $y) {
            return strnatcmp( strtolower($x[0] . $x[4] . $x[5]) , strtolower($y[0] . $y[4] . $y[5]));
        });

        $unqDutyIdArr = [];
        foreach ($unAllocatedDutyData as $unK => $unV) {
            $unqDutyIdArr[$unV[0] . $unV[4] . $unV[5]][$unV[9]][] = $unV[15];
        }

        foreach ($unAllocatedDutyData as $unKe => $unVa) {
            sort($unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
            $unAllocatedDutyData[$unKe]['InstanceIds'] = implode(',', $unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
            $unAllocatedDutyData[$unKe][14] = count($unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
        }
        $unAllocatedDutyData = json_encode($unAllocatedDutyData);
    }

    if (!empty($allocatedDutyData)) {
        $allocatedDutyData = json_encode($allocatedDutyData);
    }

    $areaID = $_SESSION['user']['AreaID'] ?? 0;
    $dutyType = 0;
    $isArchived = 0;
    $strsearch = '';
    $getMiscDutyList = ListAllMasterDuties($areaID, $dutyType, $isArchived, $request->get('teamId'), $strsearch);
    $getMiscDutyList = json_decode($getMiscDutyList, true);
	$getDayIndicatorData = getDayIndicatorData($request->get('teamId'), $request->get('startDate'), $endDate);

    $returnData = [
        'allocatedduty' => $allocatedDutyData,
        'unallocatedduty' => $unAllocatedDutyData,
        'miscduty' => $getMiscDutyList,
        'dateparam' => $datePickerData,
        'getDayIndicatorData' => $getDayIndicatorData,
    ];
} else if ($request->get('showDataType') == 'UNALLOC') {
    $areaID = $_SESSION['user']['AreaID'] ?? 0;
    $dutyType = 0;
    $isArchived = 0;
    $strsearch = '';
    $getMiscDutyList = ListAllMasterDuties($areaID, $dutyType, $isArchived, $teamId, $strsearch);
    $getMiscDutyList = json_decode($getMiscDutyList, true);

    $unallocatedsdata = $service->getEditWeeklyData($request);
    $decodeunallocateds = json_decode($unallocatedsdata, true);
    $unAllocateds = (isset($decodeunallocateds['unalloc']) && !empty($decodeunallocateds['unalloc'])) ? $decodeunallocateds['unalloc'] : [];

    if (!empty($unAllocateds)) {
        $unqDutyIdArr = [];
        usort($unAllocateds, function($x, $y) {
            return strnatcmp( strtolower($x[0] . $x[4] . $x[5]) , strtolower($y[0] . $y[4] . $y[5]));
        });
        foreach ($unAllocateds as $unK => $unV) {
            $unqDutyIdArr[$unV[0] . $unV[4] . $unV[5]][$unV[9]][] = $unV[15];
        }

        foreach ($unAllocateds as $unKe => $unVa) {
            sort($unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
            $unAllocateds[$unKe]['InstanceIds'] = implode(',', $unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
            $unAllocateds[$unKe][14] = count($unqDutyIdArr[$unVa[0] . $unVa[4] . $unVa[5]][$unVa[9]]);
        }
		$getDayIndicatorData = getDayIndicatorData($request->get('teamId'), $request->get('startDate'), $endDate);
    }

    $chkShiftCountingFilterId = $service->getCurrentShiftCountingFilter($request);
    $shiftCountingFilterId = 0;
    $shiftCountingFiltersList = [];
    if(!empty($chkShiftCountingFilterId) && $chkShiftCountingFilterId['ShiftCountingFilter'] != ''){
        $shiftCountingFilterId = $chkShiftCountingFilterId['ShiftCountingFilter'];
    }

    if($shiftCountingFilterId == 0){
        $getDutyShiftCountingFiltersList = $service->getDutyShiftCountingFilters($request,0,1);
        $shiftCountingFiltersList = $getDutyShiftCountingFiltersList;
    }

    $returnData = [
        'unallocatedduty' => json_encode($unAllocateds),
        'miscduty' => $getMiscDutyList,
        'dateparam' => $datePickerData,
        'shiftCountingFilterId' => $shiftCountingFilterId,
        'shiftCountingFiltersList' => $shiftCountingFiltersList,
        'getDayIndicatorData' => $getDayIndicatorData,
    ];
} else {
    $allocatedsdata = $service->getEditWeeklyData($request);
    $decodeallocateds = json_decode($allocatedsdata, true);
    $allocateds = (isset($decodeallocateds['alloc']) && !empty($decodeallocateds['alloc'])) ? $decodeallocateds['alloc'] : [];

	$getDayIndicatorData = getDayIndicatorData($request->get('teamId'), $request->get('startDate'), $endDate);

    $returnData = [
        'allocatedduty' => json_encode($allocateds),
        'getDayIndicatorData' => $getDayIndicatorData,
    ];
}

echo json_encode($returnData);exit;
