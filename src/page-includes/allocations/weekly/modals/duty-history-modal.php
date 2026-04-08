<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../service/AllocationService.php';
include_once __DIR__ . '/../../../../function-includes/DB_Functions.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";
require_once __DIR__ . '/../service/Allocation.php';

$request = Request::createFromGlobals();
$requestType = $request->get('requestType');
$id = $request->get('id');
$service = new AllocationService();
$allocationDetail = new Allocation($service->getAllocationByID($id));

if($id == null && $requestType == 'PH') {
	$uDutyHistory = getWeekCreatedHistory($request->get('allocationId'), $request->get('scheduledPersonId'));
	$historyResults[] = [
		'HistorySubType' => 'PH',
		'History' => $uDutyHistory[0]['history']
	];
}elseif($requestType == 'PH' && !empty($request->get('allocationsSPID')))
{
	$historyResults = getPersonHistory($request->get('allocationId'), $request->get('allocationsSPID'));
}else
{
	$historyResults = $id != null ? json_decode(getHistoryLists($requestType, $id, $request->get('allocationId')), true) : [];
}
$rowCount = 0;
$i = 1;
$className = "odd";
if(count($historyResults) > 0)
{
	foreach($historyResults as $key => $historyResultsVal)
	{
		if((($i % 2) != 0))
		{
			$className = "odd";
		}else
		{
			$className = "even";
		}
		if((!empty($requestType)) && (in_array($historyResultsVal['HistorySubType'], array($requestType, 'CH'))))
		{
			echo '<tr role="row" class="'.$className.'" >
				<td style="width:703px; white-space: pre-wrap;" >'. $historyResultsVal['History'] .'</td>
			</tr>';
			$rowCount++;
			$i++;
		}
		if(empty($requestType) || ($requestType == 'NULL'))
		{
			echo '<tr role="row" class="'.$className.'">
				<td style="width:703px; white-space: pre-wrap;" >'. $historyResultsVal['History'] .'</td>
			</tr>';
			$rowCount++;
			$i++;
		}
	}
	if($rowCount == 0)
	{
		echo '<tr role="row" class="'.$className.'">
				<td style="width:703px" >No Record Found.</td>
			</tr>';
	}
}else
{
	echo '<tr role="row" class="'.$className.'">
			<td style="width:703px" >No Record Found.</td>
		</tr>';
}
?>