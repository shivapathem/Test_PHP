 <?php
	include_once '../../../function-includes/helpers.php';
	include_once '../../../function-includes/allocationsfunctions.php';
	include_once '../../../function-includes/leavefunctions.php';
	include_once '../../../function-includes/genericfunctions.php';
	include_once '../../../function-includes/AllocationsFunctionsEditing.php';
	include_once '../../../function-includes/common/classCommonDBFunctions.php';
	include_once '../weekly/service/AllocationService.php';
	$allocService = new AllocationService();
	$commonObj = new classCommonDBFunctions();
	$allocationDutyId = $_POST['allocationDutyId'] ?? 0;
	$intReason = $_POST['action'];
	$isEdited = $_POST['isEdited'] ?? 0;
	$teamId = $_POST['teamId'] ?? 0;
	$isShiftleader = $_POST['isShiftleader'] ?? 0;
	$teamName = GetTeamNameFromID($teamId);
	$editype = 'MARKABSENT';
	$allocationId = $_POST['allocationId'] ?? 0;
	$week = $_POST['weeknum'] ?? '';
	$date = $_POST['dutydate'] ?? '';
	$allocationSPID = $_POST['allocationSPid'] ?? 0;
	$scheduledPersonID = $_POST['scheduledpersonid'] ?? 0;

	//MarkAbsent
	$markdutyabsent = makeDutyAbsentSickLeave($allocationId, $teamId, $week, $isShiftleader, $editype, '', '', '', $date, 0, '', '', $allocationSPID, 0, $scheduledPersonID, $date, $allocationDutyId);
	$checkResult = json_decode($markdutyabsent, true);
	if($allocationSPID === 0){
		$allocationSPID = getallocationSpid($date, $scheduledPersonID, $checkResult['newunallocid']);
	}

	$Allocation_and_staff_info = GetAllocationsDetailsByAllocationDutyId($allocationDutyId, $allocationSPID);
	if (!is_array($Allocation_and_staff_info)) {
		$Allocation_and_staff_info = [];
	}
	$Allocation_and_staff_info['type'] = 'absent';
	echo $markdutyabsent;
	
	if ($checkResult['strstatus'] == '1') {
		include_once 'emailNotificationOnAbsent.php';
	}
