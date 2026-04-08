<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ ."/../../../../../vendor/autoload.php";
require_once __DIR__ . '/../../../../function-includes/bootstrap.php';
require_once __DIR__ . '/../service/AllocationService.php';

$request = Request::createFromGlobals();
$service = new AllocationService();

$actionName = $request->get('action');

if($actionName == 'Add'){
	$add = $service->saveDutyShiftCountingFilter($request);
	$returnData['success'] = false;
	if((!empty($add)) && ($add > 0)){
		$saveShiftCountingFilter = $service->saveShiftCountingFilter($request->get('SchedulingTeamId'),$add);
		$getDutyShiftCountingFilters = $service->getDutyShiftCountingFilters($request,0,1);
		$returnData['success'] = true;
		$returnData['savedFilterId'] = $add;
		$returnData['dutyFiltersList'] = $getDutyShiftCountingFilters;
	}
}

if($actionName == 'Getdetails'){
	$data = $service->getDutyShiftCountingFilters($request,$request->get('filterId'));
	if(!empty($data)){
		$returnData['success'] = true;
		$returnData['data'] = $data;
	} else {
		$returnData['success'] = false;
	}
}

if($actionName == 'Checkduplicate'){
	$checkDuplicate = $service->checkDuplicateDutyShiftcountingFilter($request);
	if((!empty($checkDuplicate)) && (isset($checkDuplicate['isExist'])) && ($checkDuplicate['isExist'] > 0)){
	    $returnData['success'] = true;
	    $returnData['isActive'] = $checkDuplicate['isActive'];
	}else{
	    $returnData['success'] = false;
	}
}

if($actionName == 'Delete'){
	$deleteStatus = $service->deleteDutyCountFilter($request);
	if((!empty($deleteStatus)) && (isset($deleteStatus['delStatus'])) && ($deleteStatus['delStatus'])){
		$returnData['success'] = true;
	} else {
		$returnData['success'] = false;
	}
}

if($actionName == 'Activate'){
	$activateStatus = $service->activateDutyCountFilter($request);
	if($activateStatus){
		$returnData['success'] = true;
	} else {
		$returnData['success'] = false;
	}
}

if($actionName == 'Filterlist'){
	$getDutyShiftCountingFiltersList = $service->getDutyShiftCountingFilters($request,$request->get('filterId'),$request->get('isActive'));
	if(!empty($getDutyShiftCountingFiltersList)){
		$returnData['success'] = true;
		$returnData['data'] = $getDutyShiftCountingFiltersList;
	} else {
		$returnData['success'] = false;
	}
}

if($actionName == 'Public'){
	$publicRespStatus = $service->setPublicDutyCountFilter($request);
	if((!empty($publicRespStatus)) && (isset($publicRespStatus['status'])) && ($publicRespStatus['status'] == true) && ($publicRespStatus['message'] == '')){
		$returnData['success'] = true;
	} else {
		$returnData['success'] = false;
		$returnData['message'] = $publicRespStatus['message'];
	}
}

if($actionName == 'Getdetailswithremember'){
	$data = $service->getDutyShiftCountingFilters($request,$request->get('filterId'));
	if(!empty($data)){
		$saveShiftCountingFilter = $service->saveShiftCountingFilter($request->get('teamId'),$request->get('filterId'));
		$returnData['success'] = true;
		$returnData['data'] = $data;
	} else {
		$returnData['success'] = false;
	}
}

if($actionName == 'Clearshiftcountfilter'){
	$returnData['success'] = $service->clearDutyShiftCountingFilter($request);
}

if($actionName == 'applyfilter'){
	$_SESSION['filterdata'] = [];
	$filterDutyData = json_decode($request->get('fdata'), true);
	if(!empty($filterDutyData)){
		$cnt = 0;
	    foreach ($filterDutyData as $id => $dutyData) {
	    	if(is_array($dutyData)){
	    		foreach($dutyData as $k => $v){
	    			if (isset($v['dutyName']) && isset($v['dutyDate'])) {
		    			$_SESSION['filterdata'][$cnt][1] = $v['dutyName'];
		    			$_SESSION['filterdata'][$cnt][15] = $id;
		    			$_SESSION['filterdata'][$cnt][16] = $v['dutyDate'];
	    				$cnt++;
	    			}
	    		}
	    	}
	    }
	}
    $returnData['success'] = true;
}

if($actionName == 'clearApplyfilter'){
	$_SESSION['filterdata'] = [];
    $returnData['success'] = true;
}
echo json_encode($returnData);