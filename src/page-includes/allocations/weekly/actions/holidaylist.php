<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../service/AllocationService.php';

$request = $request ?? Request::createFromGlobals();
$service = new AllocationService();
if ($request->get('dateInp') != '') {
    $request->request->set('startDate', date('Y-m-d', strtotime($request->get('dateInp'))));
    $endDate = date('Y-m-d', strtotime($request->get('startDate') . ' +7 days'));
    $request->request->set('endDate', $endDate);
}
$holidayData = $service->getHolidayList($request);
$jsonResp['holidayData'] = $holidayData;

$chkShiftCountingFilterId = $service->getCurrentShiftCountingFilter($request);
$jsonResp['shiftCountingFilterId'] = 0;
if(!empty($chkShiftCountingFilterId) && $chkShiftCountingFilterId['ShiftCountingFilter'] != ''){
    $jsonResp['shiftCountingFilterId'] = $chkShiftCountingFilterId['ShiftCountingFilter'];
}

if (($chkShiftCountingFilterId['ShiftCountingFilter'] ?? 0) == 0) {
    $getDutyShiftCountingFiltersList = $service->getDutyShiftCountingFilters($request,0,1);
    $jsonResp['shiftCountingFiltersList'] = $getDutyShiftCountingFiltersList;
}
echo json_encode($jsonResp);exit;