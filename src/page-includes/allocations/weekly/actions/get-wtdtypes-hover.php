<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ .'/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
$wtdTypes  = $service->getWTDTypes($request);

$wtdDutyName = isset($wtdTypes[0]['Dutyname']) ? $wtdTypes[0]['Dutyname'] : '';
$wtdTeamName = isset($wtdTypes[0]['TeamName']) ? $wtdTypes[0]['TeamName'] : '';
$pdlStartTime = (int) $request->get('pdlStartTime');
$pdlEndTime = (int) $request->get('pdlEndTime');
$pdlDuration = (int) ($request->get('pdlEndTime') - $request->get('pdlStartTime'));
if($request->get('pdlStartTime') > $request->get('pdlEndTime')){
	$pdlDuration = (int) ((86400+$request->get('pdlEndTime')) - $request->get('pdlStartTime'));
}


$bodyStr = '';
$bodyStr .='<table class="tablesmalltidy tooltip-table" width="250px">';
$bodyStr .= '<tr class="tooltip-table-background">';
$bodyStr .= '<td colspan="2">'.$wtdDutyName.'</td>';
$bodyStr .= '</tr>';
$bodyStr .= '<tr class="tooltip-table-background">';
$bodyStr .= '<td colspan="2"><b>'.$wtdTeamName.'</b></td>';
$bodyStr .= '</tr>';
if (!empty($wtdTypes) > 0 ) {
	foreach($wtdTypes as $key => $value){
		$bodyStr .= '<tr class="tooltip-table-background">';
		$bodyStr .= '<td>';
		$bodyStr .= isset($value['BreachType'])?($value['BreachType']):'';
		$bodyStr .= '</td>';
		$bodyStr .= '</tr>';
	}
}
if(($pdlStartTime != 0) || ($pdlEndTime != 0)){
	$bodyStr .= '<tr class="tooltip-table-background">';
	$bodyStr .= '<td style="color:#109146; font-weight:bold;">';
	$bodyStr .= 'PDL: '.$service->convertSecondsIntoTime($pdlStartTime,':','No').'-'.$service->convertSecondsIntoTime($pdlEndTime,':','No').'&nbsp;&nbsp;&nbsp;'.$service->convertSecondsIntoTime($pdlDuration,'.','Yes');
	$bodyStr .= '</td>';
	$bodyStr .= '</tr>';
}
$bodyStr .= '</table>';
echo $bodyStr;