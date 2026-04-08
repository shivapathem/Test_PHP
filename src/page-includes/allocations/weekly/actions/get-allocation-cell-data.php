<?php
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/AllocationRepository.php';
require_once __DIR__ . '/../../../../function-includes/allocationsfunctions.php';
require_once __DIR__ . '/../../../../function-includes/masterduty_filter_functions.php';
$service = new AllocationService();
$repository = new AllocationRepository();
$request = $request ?? $service->prepareRequestDates(Request::createFromGlobals());

$reqParams = json_decode($request->get('dataParams'));
$request->request->set('schedulingTeamId', $reqParams->schedulingTeamId);
$request->request->set('userId', $reqParams->userId);
$request->request->set('weekNumber', $reqParams->weekNumber);
$request->request->set('teamId', $reqParams->teamId);
$request->request->set('newAvailablePerson', $reqParams->newAvailablePerson);
$request->request->set('days', $reqParams->days);
$request->request->set('startDate', $request->get('pStartDate'));
$request->request->set('endDate', $request->get('pEtartDate'));
$request->request->set('startWeekDate', $reqParams->startWeekDate);
$request->request->set('endWeekDate', $reqParams->endWeekDate);
$request->request->set('startWeek', $reqParams->startWeek);
$request->request->set('endWeek', $reqParams->endWeek);

if ($request->get('weekCount') > 1) {
    $days = 7;
    $queryDays = (($request->get('weekCount') * $days));
    $dateRangeDays = $queryDays;
    $days = (($request->get('weekCount') * $days) - 1);

    $endDate = date('Y-m-d', strtotime($request->get('startDate') . ' + ' . $queryDays . ' days'));
    $request->request->set('days', $days);
    $request->request->set('endDate', $endDate);
    $request->request->set('endWeekDate', $endDate);

    $sTDate = strtotime($request->get('startDate'));
    $eDDate = strtotime($endDate);
    $datediff = $eDDate - $sTDate;
    $totalDays = round($datediff / (60 * 60 * 24));

    $sixDayBeforeDates = [];
    $sixDayAfterDates = [];
    for ($i = 1; $i <= 6; $i++) {
        $date1 = date('Y-m-d', strtotime('-' . $i . ' day', strtotime($request->get('dutyDate'))));
        $date2 = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($request->get('dutyDate'))));
        if ($date1 >= $request->get('startDate')) {
            $sixDayBeforeDates[] = $date1;
        }
        if ($date2 <= $request->get('endDate')) {
            $sixDayAfterDates[] = $date2;
        }
    }

    if (!empty($sixDayBeforeDates)) {
        $reqStartDate = min($sixDayBeforeDates);
        $request->request->set('startDate', $reqStartDate);
    }

    if (!empty($sixDayAfterDates)) {
        $reqEndDate = max($sixDayAfterDates);
        $request->request->set('endDate', $reqEndDate);
    } else {
        $request->request->set('endDate', $endDate);
    }

    $periodStart = new Carbon($request->get('startDate'));
    $periodEnd = clone $periodStart;
    $periodEnd->addDays($days);
    $period = CarbonPeriod::create($periodStart, $periodEnd);
    $periodArr = $period->toArray();
} else {
    $periodStart = new Carbon($request->get('pStartDate'));
    $periodEnd = clone $periodStart;
    $periodEnd->addDays(abs((int) $reqParams->days));
    $period = CarbonPeriod::create($periodStart, $periodEnd);
    $periodArr = $period->toArray();
    $dateRangeDays = 7;
    $totalDays = 7;
}

if ($request->get('unallocDutyData') != '') {
    $unallocDutyData = json_decode(stripslashes($request->get('unallocDutyData')));
    $unallocDutyData = json_decode(json_encode($unallocDutyData), true);
}

$processHtml = false;
$request->request->set('showDataType', 'ALLOC');
$request->request->set('startDate', $request->get('pStartDate'));
$setEndDate = date('Y-m-d', strtotime($request->get('pEndDate') . ' + 1 days'));
$request->request->set('endDate', $setEndDate);
$dutyExists = 'No';
$mastFilterDutyNames = [];
if (strtoupper($request->get('dataDragType')) == 'SWAP') {
    $spResultData = $repository->swapAllocatedDuty($request);
    $spResult = json_decode($spResultData, true);
    if (isset($spResult['spStatus']) && $spResult['spStatus'] == 1) {
        $allocatedsdata = $repository->getEditWeeklyDataCell($request);
		$allocateds = $allocatedsdata['alloc'];
        $processHtml = true;
    }
} else if (strtoupper($request->get('dataDragType')) == 'UNALLOCTOALLOC') {
    if ($request->get('mastMiscFilterId') != '') {
        $rsAssignedDutiesJson = GetAssignedDutiesToFilter($request->get('mastMiscFilterId'), 1);
        $rsAssignedDutiesJson = json_decode($rsAssignedDutiesJson, true);
        $allocationDetail = $repository->getAllocationByID($request->get('dataTargetId'));
        if (!empty($rsAssignedDutiesJson)) {
            foreach ($rsAssignedDutiesJson as $rsKey => $rsVal) {
                if (strtolower($rsVal['DutyName']) == strtolower($allocationDetail['DutyName'])) {
                    $dutyExists = 'Yes';
                } else {
                    $mastFilterDutyNames[] = strtolower($rsVal['DutyName']);
                }
            }
        }
    }
    $spResultData = $repository->unAllocatedToAllocated($request);
    $spResult = json_decode($spResultData, true);
    if (isset($spResult['spStatus']) && $spResult['spStatus'] == true) {
        $allocatedsdata = $repository->getEditWeeklyDataCell($request);
		$allocateds = $allocatedsdata['alloc'];
        $processHtml = true;
    }
} else if (strtoupper($request->get('dataDragType')) == 'ALLOCTOUNALLOC') {
    if ($request->get('mastMiscFilterId') != '') {
        $rsAssignedDutiesJson = GetAssignedDutiesToFilter($request->get('mastMiscFilterId'), 1);
        $rsAssignedDutiesJson = json_decode($rsAssignedDutiesJson, true);
        $allocationDetail = $repository->getAllocationByID($request->get('dragAllocationsDutyId'));
        if (!empty($rsAssignedDutiesJson)) {
            foreach ($rsAssignedDutiesJson as $rsKey => $rsVal) {
				$allocationDetail['DutyName'] = isset($allocationDetail['DutyName']) ? $allocationDetail['DutyName'] : '';
				$rsVal['DutyName'] = isset($rsVal['DutyName']) ? $rsVal['DutyName'] : '';
                if (strtolower($rsVal['DutyName']) == strtolower($allocationDetail['DutyName'])) {
                    $dutyExists = 'Yes';
                } else {
                    $mastFilterDutyNames[] = strtolower($rsVal['DutyName']);
                }
            }
        }
    }
    $spResultData = $repository->allocatedToUnallocated($request);
    $spResult = json_decode($spResultData, true);
    if (isset($spResult['spStatus']) && $spResult['spStatus'] == true && !empty($spResult['spData'])) {
        $allocatedsdata = $repository->getEditWeeklyDataCell($request);
		$allocateds = $allocatedsdata['alloc'];
        $processHtml = true;
    }
} else if (strtoupper($request->get('dataDragType')) == 'MISC') {
    if ($request->get('mastMiscFilterId') != '') {
        $rsAssignedDutiesJson = GetAssignedDutiesToFilter($request->get('mastMiscFilterId'), 1);
        $rsAssignedDutiesJson = json_decode($rsAssignedDutiesJson, true);
        if (!empty($rsAssignedDutiesJson)) {
            foreach ($rsAssignedDutiesJson as $rsKey => $rsVal) {
                if (strtolower($rsVal['DutyName']) == strtolower($request->get('dropExistDutyName'))) {
                    $dutyExists = 'Yes';
                } else {
                    $mastFilterDutyNames[] = strtolower($rsVal['DutyName']);
                }
            }
        }
    }
    $spResultData = $repository->setMiscDutyInAllocation($request);
    $spResult = json_decode($spResultData, true);
    if (isset($spResult['spStatus']) && $spResult['spStatus'] == 1 && !empty($spResult['spData'])) {
        $allocatedsdata = $repository->getEditWeeklyDataCell($request);
		$allocateds = $allocatedsdata['alloc'];
        $processHtml = true;
    }
} else {
    $request->request->set('showDataType', 'ALLOC');
    if ($request->get('mastMiscFilterId') != '' && strtoupper($request->get('dataDragType')) == 'CONTEXTMENUEDITSICK') {
        $rsAssignedDutiesJson = GetAssignedDutiesToFilter($request->get('mastMiscFilterId'), 1);
        $rsAssignedDutiesJson = json_decode($rsAssignedDutiesJson, true);
        if (!empty($rsAssignedDutiesJson)) {
            foreach ($rsAssignedDutiesJson as $rsKey => $rsVal) {
                if (strtolower($rsVal['DutyName']) == strtolower($request->get('dropExistDutyName'))) {
                    $dutyExists = 'Yes';
                } else {
                    $mastFilterDutyNames[] = strtolower($rsVal['DutyName']);
                }
            }
        }
    }
    $allocatedsdata = $service->getEditWeeklyDataCell($request);
    $allocateds = isset($allocatedsdata['alloc']) ? $allocatedsdata['alloc'] : [];
    $processHtml = true;
}

$teamDefaults = GetTeamDefaults(0, $request->get('teamId'));
$teamDefaults = isset($teamDefaults[$request->get('teamId')]) ? $teamDefaults[$request->get('teamId')] : [];

if ($processHtml == true) {
    $alloatedData = [];
	if(count($allocateds) > 1)
	{
		foreach ($allocateds as $k1 => $v1) {
			$alloatedData[$v1['SchedulingPersonID']][$v1['DutyDate']] = $v1;
		}
	}
    ksort($alloatedData);

    $cellValue = [];
    $cellDivHtml = '';

    if ($request->get('dataDragType') == 'SWAP') {
        $explodeSchPerson = explode(',', $request->get('scheduledPersonId'));
        $firstPerson = (int) $explodeSchPerson[0];
        $fPersonWeekDur = $alloatedData[$firstPerson][$request->get('dutyDate')]['WeekDuration'];
        $fpersonDutyDays = $alloatedData[$firstPerson][$request->get('dutyDate')]['AccDays'] != '' ? (int) $alloatedData[$firstPerson][$request->get('dutyDate')]['AccDays'] : "0";
        $secondPerson = (int) $explodeSchPerson[1];
        $sPersonWeekDur = $alloatedData[$secondPerson][$request->get('dutyDate')]['WeekDuration'];
        $spersonDutyDays = $alloatedData[$secondPerson][$request->get('dutyDate')]['AccDays'] != '' ? (int) $alloatedData[$secondPerson][$request->get('dutyDate')]['AccDays'] : "0";
        $cellValue['div25'][$firstPerson]['dtCellVal'] = (number_format((float) (($fPersonWeekDur) / 3600), 2, '.', '') > 0) ? number_format((float) (($fPersonWeekDur) / 3600), 2, '.', '') : '00.00';
        $cellValue['div25'][$firstPerson]['dtDayVal'] = $fpersonDutyDays;
        $cellValue['div25'][$firstPerson]['dtCellValSeconds'] = $fPersonWeekDur;
        $cellValue['div25'][$secondPerson]['dtCellVal'] = (number_format((float) (($sPersonWeekDur) / 3600), 2, '.', '') > 0) ? number_format((float) (($sPersonWeekDur) / 3600), 2, '.', '') : '00.00';
        $cellValue['div25'][$secondPerson]['dtDayVal'] = $spersonDutyDays;
        $cellValue['div25'][$secondPerson]['dtCellValSeconds'] = $sPersonWeekDur;
    } else {
        $firstPerson = (int) $request->get('scheduledPersonId');
        $dutyDate = $request->get('dutyDate');
        $fPersonWeekDur = isset($alloatedData[$firstPerson][$dutyDate]['WeekDuration']) ? $alloatedData[$firstPerson][$dutyDate]['WeekDuration'] : 0;
        $fpersonDutyDays = isset($alloatedData[$firstPerson][$dutyDate]['AccDays']) ? (int) $alloatedData[$firstPerson][$request->get('dutyDate')]['AccDays'] : "0";
        $cellValue['div25']['dtCellVal'] = (number_format((float) (($fPersonWeekDur) / 3600), 2, '.', '') > 0) ? number_format((float) (($fPersonWeekDur) / 3600), 2, '.', '') : '00.00';
        $cellValue['div25']['dtDayVal'] = $fpersonDutyDays;
        $cellValue['div25']['dtCellValSeconds'] = $fPersonWeekDur;
    }
    if (!empty($alloatedData)) {
        foreach ($alloatedData as $id => $allocations) {
            $wtdClassNameForCell = [];
            $lockClassNameForCell = [];
            $under11ClassNameForCell = [];
            $loopCounter = 0;
            if (!empty($periodArr)) {
                $dataRightInnerStr = '';
                foreach ($periodArr as $date) {
                    if (!empty($allocations[$date->format('Y-m-d')])) {
                        $dataRightInner = $allocations[$date->format('Y-m-d')];
                        // For Images Starts
						if($dataRightInner['ID'] == $request->get('dragUid'))
						{
							$dataRightInner['ID'] = $request->get('dragUid');
						}elseif($dataRightInner['ID'] == $request->get('dropUid'))
						{
							$dataRightInner['ID'] = $request->get('dropUid');
						}
                        $imageNameSignInClsName = '';
                        switch ($dataRightInner['ImageNameSignin']) {
                            case "1":
                                $imageNameSignInClsName = 'signin-red-cross';
                                break;
                            case "2":
                                $imageNameSignInClsName = 'signin-green-tick';
                                break;
                            case "3":
                                $imageNameSignInClsName = 'signin-blue-tick';
                                break;
                            case "4":
                                $imageNameSignInClsName = 'msg-box-warning';
                                break;
                        }
                        //For Images Ends
                        if (isset($_POST['scheduledPersonId']) && (strpos($request->get('scheduledPersonId'), (string) $id) !== false) && ($date->format('Y-m-d') != $request->get('dutyDate'))) {
                            $sTime = (int) $dataRightInner['StartTime'];
                            $eTime = (int) $dataRightInner['EndTime'];
                            if ((($dataRightInner['ColourBackground'] != '') && ($dataRightInner['isAttentionClsName'] == 0) && ($sTime == 0 && $eTime == 0)) || (($dataRightInner['isAttentionClsName'] == 0) && ($dataRightInner['MarkedSickness'] == 1))) {
                                $wtdClassNameForCell[$loopCounter]['bgcolorcode'] = $dataRightInner['ColourBackground'];
                            } else {
                                $wtdClassNameForCell[$loopCounter]['bgcolorcode'] = '';
                            }
                            $wtdClassNameForCell[$loopCounter]['wtdDate'] = $date->format('Y-m-d');
                            $wtdClassNameForCell[$loopCounter]['scheduledPerson'] = $dataRightInner['SchedulingPersonID'];
                            $wtdClassNameForCell[$loopCounter]['titleVal'] = $dataRightInner['DutyName'] . ' (' . number_format((float) (($dataRightInner['Duration'] - $dataRightInner['dutyBreakTime']) / 3600), 2, '.', '') . ')' . ' - ' . $dataRightInner['schedulingTeamName'];
                            $wtdClassNameForCell[$loopCounter]['dutyName'] = $dataRightInner['DutyName'];
                            $wtdClassNameForCell[$loopCounter]['id'] = $dataRightInner['AllocationsSPID'] . '_' . $dataRightInner['DutyDate'];
                            $wtdClassNameForCell[$loopCounter]['underElevenIcon'] = $dataRightInner['IsUnderElevenBreakOverride'];
                            $loopmarkWiadClass = '';
                            switch ($dataRightInner['WTDBreachClassName']) {
                                case "1":
                                    $loopmarkWiadClass = 'cross-red';
                                    break;
                                case "2":
                                    $loopmarkWiadClass = 'cross-blue';
                                    break;
                                default:
                                    $loopmarkWiadClass = '';
                            }
                            $wtdClassNameForCell[$loopCounter]['classNameWTD'] = $loopmarkWiadClass;
                            $wtdClassNameForCell[$loopCounter]['IsUnderElevenBreakOverride'] = $dataRightInner['IsUnderElevenBreakOverride'];
                            $wtdClassNameForCell[$loopCounter]['IsUnderElevenBreak'] = $dataRightInner['IsUnderElevenBreak'];
                            $wtdClassNameForCell[$loopCounter]['WTDBreachClassName'] = $dataRightInner['WTDBreachClassName'];
                            $wtdClassNameForCell[$loopCounter]['AllocationsDutyID'] = $dataRightInner['AllocationsDutyID'];

                            $loopCounter++;
                        }
                        if (!isset($lockClassNameForCell) || !is_array($lockClassNameForCell)) {
                            $lockClassNameForCell = [];
                        }
                        if ($dataRightInner['ShowLock'] == 1) {
                            $lockClassNameForCell[] = $dataRightInner['ID'];
                        }
                        if (isset($_POST['scheduledPersonId']) && (strpos($request->get('scheduledPersonId'), (string) $id) !== false) && ($date->format('Y-m-d') == $request->get('dutyDate'))) {
                            $isEdpVal = "";
                            if ($dataRightInner['ManualEDP'] == 0) {
                                $isEdpVal = "C";
                            } else if ($dataRightInner['ManualEDP'] == 1) {
                                $isEdpVal = "M";
                            } else {
                                $isEdpVal = "";
                            }
							$uIdStr = $dataRightInner['AllocationsSPID'] . '_' . $dataRightInner['DutyDate'];
                            if ($date->format('Y-m-d') == date("Y-m-d")) {
                                $js = ' onclick=\'javascript:SignInToDay("' . $dataRightInner['DutyName'] . '",' . $dataRightInner['ActionNameForSignin'] . ', "'. $date->format('Y-m-d') .'", "' . $dataRightInner['StartTime'] . '", ' . $dataRightInner['EndTime'] . ', "' . $uIdStr . '", "'.$dataRightInner['signin'].'")\';';
                            } else {
                                $js = ' onclick=\'javascript:SignInDay("' . $dataRightInner['DutyName'] . '",' . $dataRightInner['ActionNameForSignin'] . ', "'. $date->format('Y-m-d') .'", "' . $dataRightInner['StartTime'] . '", ' . $dataRightInner['EndTime'] . ', "' . $uIdStr . '", "'.$dataRightInner['signin'].'")\';';
                            }

                            $allowsignin = 0;
                            if ($dataRightInner['StartTime'] != 0 || $dataRightInner['EndTime'] != 0) {
                                if ($teamDefaults['SignInDays'] > 0 && $date->isBetween((new Carbon())->subDay(), (new Carbon)->addDays(abs((int)$teamDefaults['SignInDays'])), true)
                                ) {
                                    $allowsignin = 1;
                                }
                            }

                            $cellbgcolour = '';
                            $cellBgColor = 'ebebeb';
                            $sTime = (int) $dataRightInner['StartTime'];
                            $eTime = (int) $dataRightInner['EndTime'];
                            $DutyNameUpTrim = !is_null($dataRightInner['DutyName']) ? trim(strtoupper($dataRightInner['DutyName'])) : '';
					        if (($DutyNameUpTrim == "SICK") || ($DutyNameUpTrim == "U-SICK") || ($DutyNameUpTrim == "-SICK")) {
                                // Set default mustard yellow colour for Sick and U-Sick
                                $cellbgcolour = 'style="background-color:#' . $dataRightInner['ColourBackground'] . '; color:#' . $dataRightInner['ColourFont'] . ';"';
                                $cellBgColor = $dataRightInner['ColourBackground'];
                            } else if (($DutyNameUpTrim == "LEAVE") || ($DutyNameUpTrim == "OFF LEAVE")) {
                                // Set default green colour for Leave and OFF Leave
                                $cellbgcolour = 'style="background-color:#' . $dataRightInner['ColourBackground'] . '; color:#' . $dataRightInner['ColourFont'] . ';"';
                                $cellBgColor = $dataRightInner['ColourBackground'];
                            } else if ((($dataRightInner['ColourBackground'] != '') && ($dataRightInner['isAttentionClsName'] == 0) && ($sTime == 0 && $eTime == 0)) || (($dataRightInner['isAttentionClsName'] == 0) && ($dataRightInner['MarkedSickness'] == 1))) {
                                $cellbgcolour = 'style="background-color:#' . $dataRightInner['ColourBackground'] . '; color:#' . $dataRightInner['ColourFont'] . ';"';
                                $cellBgColor = $dataRightInner['ColourBackground'];
                            }

                            $contextClassName = '';
                            switch ($dataRightInner['contextMenuClsName']) {
                                case "0":
                                    $contextClassName = 'context-menu-additional';
                                    break;
                                case "1":
                                    $contextClassName = 'context-menu-leave';
                                    break;
                                case "2":
                                    $contextClassName = 'context-menu';
                            }
                            $markWiadClass = '';
                            switch ($dataRightInner['WTDBreachClassName']) {
                                case "1":
                                    $markWiadClass = 'cross-red';
                                    break;
                                case "2":
                                    $markWiadClass = 'cross-blue';
                                    break;
                                default:
                                    $markWiadClass = '';
                            }
                            $attentionClsName = '';
                            if ($dataRightInner['isAttentionClsName'] == 1) {
                                $attentionClsName = 'attentionClass';
                            }
                            if ($dataRightInner['isAttentionClsName'] == 2) {
                                $attentionClsName = 'attentionCopyDutyClass';
                            }

                            $doesntNeedCoveringIcon = (strlen((string)$dataRightInner['IsNeedCovering']) > 0 && $dataRightInner['IsNeedCovering'] == 0) ? 'doesntNeedCoveringIcon' : '';
                            $cellDivHtml .= '<div id="rowUnqId_' . $uIdStr . '" ' . $cellbgcolour . ' class="NotFixedon weekly-body-cell allocatedDutyCell ' . $contextClassName . ' ' . $markWiadClass . ' ' . $doesntNeedCoveringIcon . ' ';
                            $cellDivHtml .= $attentionClsName;
                            $cellDivHtml .= ' alloc-cell person_'.$dataRightInner['SchedulingPersonID'].'" ';
                            $cellDivHtml .= ' data-duty-labels="' . implode(',', [
                                $dataRightInner['dutyProgramId'] ?? '',
                                $dataRightInner['dutyProgramId2'] ?? '',
                                $dataRightInner['dutyProgramId3'] ?? '',
                                $dataRightInner['dutyProgramId4'] ?? '',
                                $dataRightInner['dutyProgramId5'] ?? '',
                                $dataRightInner['dutyProgramId6'] ?? ''
                            ]) . '" ';
                            $cellDivHtml .= ' data-order="' . $dataRightInner['DutyName'] . $dataRightInner['DisplayName'] . '"
                                            data-mannualedp="';
                            if ($dataRightInner['ManualEDP'] == 0) {
                                $cellDivHtml .= 1;
                            } else {
                                $cellDivHtml .= 0;
                            }

                            $dMarkWiad = false;
                            if (($markWiadClass != '') && (($markWiadClass == 'cross-red') || ($markWiadClass == 'cross-blue'))) {
                                $dMarkWiad = true;
                            }
                            $cellDivHtml .= '"';
                            $cellDivHtml .= ' data-duty-name="' . $dataRightInner['DutyName'] . '"';
                            $cellDivHtml .= ' data-id="' . $dataRightInner['ID'] . '"';
                            $cellDivHtml .= ' date-range-days="' . $dateRangeDays . '"';
                            $cellDivHtml .= ' date-start-date="' . $request->get('startDate') . '"';
                            $cellDivHtml .= ' data-mark-wiad-option="' . $dMarkWiad . '"';
                            $cellDivHtml .= ' date-end-date="' . date('Y-m-d', strtotime($request->get('endDate') . ' -1 day ')) . '"';
                            $cellDivHtml .= ' data-unique-id="' . $uIdStr . '"';
                            if ($dataRightInner['EditDuty'] == 1) {
                                if ($dataRightInner['DutyName'] == 'U') {
                                    $modalTitle = 'createduty';
                                } else {
                                    $modalTitle = 'editduty';
                                }
                                $dutyParamsForFn = "'" . $modalTitle . "','edit','" . $uIdStr . "'";
                                $cellDivHtml .= 'ondblclick="editAllocateDuty(' . $dutyParamsForFn . ')"';
                            }
                            $cellDivHtml .= '>';
                            $cellDivHtml .= '<div id="colUnqId_' . $uIdStr . '" class="item-cell dragClass_' . $date->format('Y-m-d') . '_' . $dataRightInner['SchedulingPersonID'] . ' alloc ';
                            if (($dataRightInner['TriangleColour'] == null) || (strtolower($dataRightInner['DutyName']) == 'sick')) {
                                $cellDivHtml .= '';
                            } else {
                                $cellDivHtml .= 'cornerIcon cornerIcon-' . $dataRightInner['TriangleColour'];
                            }
                            $leaveStartTime = 0;
                            $dataRightInner['pdlStartTime'] = 0;
                            if ($dataRightInner['LeaveStartTime'] != null && $dataRightInner['LeaveStartTime'] != 0 && $dataRightInner['LeaveStartTime'] != '') {
                                $leaveStartTime = $dataRightInner['LeaveStartTime'];
                                $dataRightInner['pdlStartTime'] = $dataRightInner['LeaveStartTime'];
                            }
                            $leaveEndTime = 0;
                            $dataRightInner['pdlEndTime'] = 0;
                            if ($dataRightInner['LeaveEndTime'] != null && $dataRightInner['LeaveEndTime'] != 0 && $dataRightInner['LeaveEndTime'] != '') {
                                $leaveEndTime = $dataRightInner['LeaveEndTime'];
                                $dataRightInner['pdlEndTime'] = $dataRightInner['LeaveEndTime'];
                            }

                            if (($leaveStartTime == 0) && ($leaveEndTime == 0)) {
                                $cellDivHtml .= ' dragAlloc ';
                            }

                            $cellDivHtml .= ' dutydroppable ';
                            if ($dataRightInner['EditDuty'] == 1) {
                                $cellDivHtml .= ' dropeventscall ';
                            }

                            $isAttentionVal = 0;
                            if (isset($dataRightInner['isAttentionClsName']) && ($dataRightInner['isAttentionClsName'] != '')) {
                                $isAttentionVal = $dataRightInner['isAttentionClsName'];
                            }

                            $isPubVal = 0;
                            if ($dataRightInner['isPublished'] != 0) {
                                $isPubVal = 1;
                            }

                            $isWtdBreachVal = false;
                            if (($dataRightInner['WTDBreachClassName'] == 1) || ($dataRightInner['WTDBreachClassName'] == 2)) {
                                $isWtdBreachVal = true;
                            }

                            $isStartTimeVal = 0;
                            if (!empty($dataRightInner['StartTime'])) {
                                $isStartTimeVal = $dataRightInner['StartTime'];
                            }

                            $isEndTimeVal = 0;
                            if (!empty($dataRightInner['EndTime'])) {
                                $isEndTimeVal = $dataRightInner['EndTime'];
                            }

                            if ($dataRightInner['MarkOverTwelve'] == -1) {
                                $cellDivHtml .= ' purpleTriangleIcon ';
                            }
                            $actingLabelIcon = ($dataRightInner['ActingFlag'] == 1) ? '' : 'visibility: hidden';
                            $cellDivHtml .= ' alloc-drag-drop max-height-duty-cell" ';

                            if (($dataRightInner['EditDuty'] == 1) && ($dataRightInner['DutyName'] != 'U') && ($leaveStartTime == 0) && ($leaveEndTime == 0)) {
                                $cellDivHtml .= ' draggable="true" ';
                            } else {
								$dataRightInner['DutyName'] = (!is_null($dataRightInner['DutyName'])) ? $dataRightInner['DutyName'] : '';
                                if ((trim(strtolower($dataRightInner['DutyName'])) == 'off leave') || (strtolower($dataRightInner['DutyName']) == 'leave') || (strtolower($dataRightInner['DutyName']) == 'absent') || (strtolower($dataRightInner['DutyName']) == 'sick') || (strtolower($dataRightInner['DutyName']) == 'u-sick') || (strtolower($dataRightInner['DutyName']) == '-sick')) {
                                    $cellDivHtml .= ' draggable="false" ';
                                }
                            }
                            if (($dataRightInner['EditDuty'] == 1) && ($leaveStartTime == 0) && ($leaveEndTime == 0)) {
                                $cellDivHtml .= ' ondrop="dropAlloc(event,this);" ';
                            } else {
								$dataRightInner['DutyName'] = (!is_null($dataRightInner['DutyName'])) ? $dataRightInner['DutyName'] : '';
                                if ((trim(strtolower($dataRightInner['DutyName'])) == 'off leave') || (strtolower($dataRightInner['DutyName']) == 'leave') || (strtolower($dataRightInner['DutyName']) == 'absent') || (strtolower($dataRightInner['DutyName']) == 'sick') || (strtolower($dataRightInner['DutyName']) == 'u-sick') || (strtolower($dataRightInner['DutyName']) == '-sick')) {
                                    $cellDivHtml .= ' ondrop="dropSickLeaveAbsent(event,this);" ';
                                } else {
                                    if(($leaveStartTime != 0) || ($leaveEndTime != 0)){
                                        $cellDivHtml .= ' ondrop="dropPDL(event,this);" ';
                                    }
                                }
                            }

                            $leaveId = (($dataRightInner['LeaveID'] == '') || ($dataRightInner['LeaveID'] == NULL)) ? 0 : $dataRightInner['LeaveID'];

                            $cellDivHtml .= ' data-iday="' . $dataRightInner['iDay'] . '" data-dateonly="' . $date->format('Y-m-d') . '" data-date="' . $dataRightInner['DutyDate'] . '" data-source="allocated" data-id="' . $dataRightInner['ID'] . '" data-duty-name="' . $dataRightInner['DutyName'] . '" data-scheduling-person="' . $dataRightInner['SchedulingPersonID'] . '" data-is-home-team="' . $dataRightInner['IsHomeTeam'] . '" data-attention="' . $isAttentionVal . '" data-scheduling-team-id="' . $dataRightInner['SchedulingTeamId'] . '" data-UnAllocated="' . $dataRightInner['UnAllocated'] . '" data-ispublished="' . $isPubVal . '" data-edp="' . $isEdpVal . '" data-wiad="' . $dataRightInner['MarkWiad'] . '" data-actual="' . $dataRightInner['MarkActual'] . '" data-bgcolor="' . $dataRightInner['ColourBackground'] . '" data-font-color="' . $dataRightInner['ColourFont'] . '" data-mark-wiad-option="' . $isWtdBreachVal . '" data-edit-duty-flag="' . $dataRightInner['EditDuty'] . '" data-row-id="' . $request->get('dutyRowId') . '" data-row-start="' . $isStartTimeVal . '" data-row-end="' . $isEndTimeVal . '" data-unique-id="' . $uIdStr . '" date-range-days="' . $request->get('pDateRangeDays') . '" date-start-date="' . $request->get('pStartDate') . '" date-end-date="' . $request->get('pEndDate') . '" data-bg-color="ebebeb" data-mark-twelve="' . $dataRightInner['MarkOverTwelve'] . '" data-mark-eleven="' . $dataRightInner['IsUnderElevenBreak'] . '" data-show-wiad-actual="' . $dataRightInner['ShowWIAD'] . '" data-purple-font="' . $dataRightInner['isRequest'] . '"
                                data-duty-duration="' . $dataRightInner['Duration'] . '" data-isactive="' . $dataRightInner['isActive'] . '"  data-masterdutyid="' . $dataRightInner['MasterDutyId'] . '" data-eleven-icon="' . $dataRightInner['IsUnderElevenBreakOverride'] . '" data-leave-starttime="' . $leaveStartTime . '" data-leave-endtime="' . $leaveEndTime . '" data-scheduled-netlogin="' . $dataRightInner['NetLogin'] . '" data-leave-id="' . $leaveId . '" data-edit-screen="1" data-allocations-duty-id="' . $dataRightInner['AllocationsDutyID'] . '" data-allocations-sp-id="' . $dataRightInner['AllocationsSPID'] . '" data-is-need-covering="'.$doesntNeedCoveringIcon.'">';
                            if ($markWiadClass != '' && $markWiadClass == 'cross-red') {
                                $dataWiadOpt = true;
                            } else {
                                $dataWiadOpt = false;
                            }
                            if ($dataRightInner['IsDutyFromOtherTeam'] == 1) {
                                $otherTeamClass = 'otherTeamClass';
                            } else {
                                $otherTeamClass = '';
                            }

                            $purpleFontClass = '';
                            $spanStyle = ['overflow:hidden'];
                            if ($dataRightInner['isRequest'] != 0) {
                                $purpleFontClass = 'requestClass';
                                if($dataRightInner['DutyName'] == 'U'){
                                    $spanStyle[] = 'font-weight: bold';
                                }
                            }

                            if ($dataRightInner['DutyName'] != 'U' && $dataRightInner['DutyName'] != '') {
                                if ($request->get('dataSource') != 'misc') {
                                    if ($dataRightInner['DutyName'] != 'U') {
                                        if ($isStartTimeVal != 0 || $isEndTimeVal != 0) {
                                            $cellDivHtml .= '<span style="' . implode(';', $spanStyle) . '" id="dutytipID_' . $date->format('Y-m-d') . '_' . $dataRightInner['SchedulingPersonID'] . '" class="dutyTip duityNameoverflow ' . $otherTeamClass . ' ' . $purpleFontClass;
                                            if ($dataRightInner['MarkOverTwelve'] == -1) {
                                                $cellDivHtml .= ' triangleIconSpan ';
                                            }
                                            $cellDivHtml .= '" ';
                                            if ($markWiadClass != '') {
                                                $wtdtooltipshow = "'showtooltip',this," . $dataRightInner['AllocationsDutyID'] . ",'" . $dataWiadOpt . "'";
                                                $wtdtooltiphide = "'hidetooltip'";
                                                $cellDivHtml .= ' onmouseover="showwtdtip(' . $wtdtooltipshow . ')" onmouseleave="showwtdtip(' . $wtdtooltiphide . ')"';
                                            }
                                            $cellDivHtml .= ' data-mark-wiad-option="';
                                            if (($markWiadClass != '') && (($markWiadClass == 'cross-red') || ($markWiadClass == 'cross-blue'))) {
                                                $cellDivHtml .= true;
                                            } else {
                                                $cellDivHtml .= false;
                                            }
                                            $cellDivHtml .= '" data-id="' . $dataRightInner['ID'] . '" ';
                                            if ($markWiadClass == '') {
                                                $cellDivHtml .= 'title="' . $dataRightInner['DutyName'] . ' (' . number_format((float) (($dataRightInner['Duration'] - $dataRightInner['dutyBreakTime']) / 3600), 2, '.', '') . ')' . ' - ' . $dataRightInner['schedulingTeamName'] . '"  onmouseover="removetip();" ';
                                            }
                                            $cellDivHtml .= '>';
                                            $cellDivHtml .= '<b>';
                                            $cellDivHtml .= $dataRightInner['DutyName'];
                                            if ((strtolower($dataRightInner['DutyName']) != 'sick') && (strtolower($dataRightInner['DutyName']) != '-sick')) {
                                                $cellDivHtml .= '</b>';
                                                $cellDivHtml .= '<br/>';

                                                if (($dataRightInner['LeaveStartTime'] != '') && ($dataRightInner['LeaveEndTime'] != '') && (($dataRightInner['LeaveStartTime'] != 0) || ($dataRightInner['LeaveEndTime'] != 0))) {
                                                    $cellDivHtml .= '<span style="width: 100px; white-space:nowrap;">';
                                                    $cellDivHtml .= gmdate('H:i', $dataRightInner['StartTime']) . ' - ' . gmdate('H:i', $dataRightInner['EndTime']);

                                                    $dispPdlEndTime = $dataRightInner['LeaveEndTime'];
                                                    if ($dataRightInner['LeaveStartTime'] > $dataRightInner['LeaveEndTime']) {
                                                        $dispPdlEndTime = (86400 + $dataRightInner['LeaveEndTime']);
                                                    }

                                                    $pdlTitle = 'PDL: ' . $service->convertSecondsIntoTime($dataRightInner['LeaveStartTime'], ':', 'No') . '-' . $service->convertSecondsIntoTime($dataRightInner['LeaveEndTime'], ':', 'No') . '&nbsp;|&nbsp;' . $service->convertSecondsIntoTime($dispPdlEndTime - $dataRightInner['LeaveStartTime'], '.', 'Yes');

                                                    $cellDivHtml .= '<span style="color:#109146; padding-left:4px;" title="' . $pdlTitle . '">[L:' . $service->convertSecondsIntoTime($dataRightInner['LeaveStartTime'], ':', 'No') . '-' . $service->convertSecondsIntoTime($dataRightInner['LeaveEndTime'], ':', 'No') . ']</span>';
                                                    $cellDivHtml .= '</span>';
                                                    $cellDivHtml .= '</span>';
                                                } else {
                                                    $cellDivHtml .= '<span style="width: 100px; display:inline-block;">';
                                                    $cellDivHtml .= gmdate('H:i', $dataRightInner['StartTime']) . ' - ' . gmdate('H:i', $dataRightInner['EndTime']);
                                                    $cellDivHtml .= '</span>';
                                                }
                                            }
                                        } else {
                                            $cellDivHtml .= '<span style="' . implode(';', $spanStyle) . '" id="dutytipID_' . $date->format('Y-m-d') . '_' . $dataRightInner['SchedulingPersonID'] . '" class="dutyTip' . $purpleFontClass;
                                            if ($dataRightInner['MarkOverTwelve'] == -1) {
                                                $cellDivHtml .= ' triangleIconSpan';
                                            }
                                            $cellDivHtml .= '" ';
                                            if ($markWiadClass != '') {
                                                $wtdtooltipshow = "'showtooltip',this," . $dataRightInner['AllocationsDutyID'] . ",'" . $dataWiadOpt . "'";
                                                $wtdtooltiphide = "'hidetooltip'";
                                                $cellDivHtml .= ' onmouseover="showwtdtip(' . $wtdtooltipshow . ')" onmouseleave="showwtdtip(' . $wtdtooltiphide . ')"';
                                            }
                                            $cellDivHtml .= ' data-mark-wiad-option="';
                                            if (($markWiadClass != '') && (($markWiadClass == 'cross-red') || ($markWiadClass == 'cross-blue'))) {
                                                $cellDivHtml .= true;
                                            } else {
                                                $cellDivHtml .= false;
                                            }
                                            $cellDivHtml .= '" data-id="' . $dataRightInner['ID'] . '" ';
                                            if ($markWiadClass == '') {
                                                $cellDivHtml .= 'title="' . $dataRightInner['DutyName'] . ' (' . number_format((float) (($dataRightInner['Duration'] - $dataRightInner['dutyBreakTime']) / 3600), 2, '.', '') . ')' . ' - ' . $dataRightInner['schedulingTeamName'] . '"  onmouseover="removetip();" ';
                                            }
                                            $cellDivHtml .= '>';
                                            $cellDivHtml .= '<b>';
                                            $cellDivHtml .= $dataRightInner['DutyName'];
                                            $cellDivHtml .= '</b></span>';
                                        }
                                    } else {
                                        $cellDivHtml .= '<span style="' . implode(';', $spanStyle) . '" id="dutytipID_' . $date->format('Y-m-d') . '_' . $dataRightInner['SchedulingPersonID'] . '" class="dutyTip ' . $purpleFontClass . ' ">U</span>';
                                    }
                                } else {
                                    $cellDivHtml .= '<span style="' . implode(';', $spanStyle) . '" id="dutytipID_' . $date->format('Y-m-d') . '_' . $dataRightInner['SchedulingPersonID'] . '" class="dutyTip ' . $purpleFontClass;
                                    if ($dataRightInner['MarkOverTwelve'] == -1) {
                                        $cellDivHtml .= ' triangleIconSpan';
                                    }
                                    $cellDivHtml .= '" ';
                                    if ($markWiadClass != '') {
                                        $wtdtooltipshow = "'showtooltip',this," . $dataRightInner['AllocationsDutyID'] . ",'" . $dataWiadOpt . "'";
                                        $wtdtooltiphide = "'hidetooltip'";
                                        $cellDivHtml .= ' onmouseover="showwtdtip(' . $wtdtooltipshow . ')" onmouseleave="showwtdtip(' . $wtdtooltiphide . ')"';
                                    }
                                    $cellDivHtml .= ' data-mark-wiad-option="';
                                    if (($markWiadClass != '') && (($markWiadClass == 'cross-red') || ($markWiadClass == 'cross-blue'))) {
                                        $cellDivHtml .= true;
                                    } else {
                                        $cellDivHtml .= false;
                                    }
                                    $cellDivHtml .= '" data-id="' . $dataRightInner['ID'] . '" ';
                                    if ($markWiadClass == '') {
                                        $cellDivHtml .= 'title="' . $dataRightInner['DutyName'] . ' (' . number_format((float) (($dataRightInner['Duration'] - $dataRightInner['dutyBreakTime']) / 3600), 2, '.', '') . ')' . ' - ' . $dataRightInner['schedulingTeamName'] . '"  onmouseover="removetip();" ';
                                    }
                                    $cellDivHtml .= '>';
                                    $cellDivHtml .= '<b>';
                                    $cellDivHtml .= $dataRightInner['DutyName'];
                                    $cellDivHtml .= '</b></span>';
                                }
                            } else {
                                $cellDivHtml .= '<span style="' . implode(';', $spanStyle) . '" id="dutytipID_' . $date->format('Y-m-d') . '_' . $dataRightInner['SchedulingPersonID'] . '" class="dutyTip ' . $purpleFontClass . ' ">U</span>';
                            }
							$dataRightInner['DutyComments'] = (trim($dataRightInner['DutyComments']) == '') ? "0" : $dataRightInner['DutyComments'];
							$dataRightInner['PersonComments'] = (trim($dataRightInner['PersonComments']) == '') ? "0" : $dataRightInner['PersonComments'];
                            if ($dataRightInner['DutyComments'] == "0" && $dataRightInner['PersonComments'] != "0") {
                                $className = 'golden displayInlineBlock';
                            } else if ($dataRightInner['DutyComments'] != "0" && $dataRightInner['PersonComments'] == "0") {
                                $className = 'blue displayInlineBlock';
                            } else if ($dataRightInner['DutyComments'] != "0" && $dataRightInner['PersonComments'] != "0") {
                                $className = 'pink displayInlineBlock';
                            } else {
                                $className = 'hide';
                            }

                            $cellDivHtml .= '<div class="DutyIconPositionLeft">';
                            $cellDivHtml .= '<div class="IconPositionClass blueEuro displayInlineBlock">';
                            if ($dataRightInner['ShowEDPIcon'] == 1) {
                                $cellDivHtml .= '<div class="blue-pound-icon"></div>';
                            }
                            $cellDivHtml .= '</div>';

                            if ($dataRightInner['MarkedOvertime'] == 1) {
                                $mclass = 'activemarkovertime';
                            } else {
                                $mclass = 'inactivemarkovertime';
                            }

                            $cellDivHtml .= '<div class="IconPositionClass redEuro displayInlineBlock ' . $mclass . '" id="redEuro_' . $uIdStr . '">';
                            if ($dataRightInner['MarkedOvertime'] == 1) {
                                $cellDivHtml .= '<div class="red-pound-icon"></div>';
                            }
                            $cellDivHtml .= '</div>';
                            $cellDivHtml .= '<div class="markWA icons displayInlineBlock IconPositionClass">';
                            if ($dataRightInner['MarkWiad']) {
                                $cellDivHtml .= 'W';
                            }
                            if ($dataRightInner['MarkActual']) {
                                $cellDivHtml .= 'A';
                            }
                            $cellDivHtml .= '</div>';
                            $cellDivHtml .= '<div class="displayInlineBlock IconPositionClass twelveIcon"><span style="padding: 1px; font-weight: 600;">';
                            if ($dataRightInner['MarkOverTwelve'] == 1) {
                                $cellDivHtml .= '12';
                            }
                            $cellDivHtml .= '</span></div>';
                            $cellDivHtml .= '<div class="displayInlineBlock IconPositionClass elevenIcon"><span class="padd1FontW600">';
                            if ($dataRightInner['IsUnderElevenBreakOverride'] == 1) {
                                $cellDivHtml .= '11';
                            }
                            $cellDivHtml .= '</span></div>';
                            $cellDivHtml .= '<div style="' . $actingLabelIcon . '" class="displayInlineBlock IconPositionClass actingLabelIconContainer"><span class="actingLabelIcon">A</span></div>';
                            $cellDivHtml .= '<div class="displayInlineBlock IconPositionClass iconL">';
                            if ($dataRightInner['LeaveApproved'] == '1' && $dataRightInner['LeaveDeleted'] == '0' && $dataRightInner['CountLeave'] == '1') {
                                $cellDivHtml .= '<span class="leaveOrange"><b>L</b></span>';
                            } else if ($dataRightInner['LeaveApproved'] == '1' && $dataRightInner['LeaveDeleted'] == '0' && $dataRightInner['CountLeave'] == '0') {
                                $cellDivHtml .= '<span class="leaveHashedOrange"><b>L</b></span>';
                            } else {
                                if($dataRightInner['LeaveDeleted'] == '0' && $dataRightInner['IsAgreed'] == '1') {
                                    $cellDivHtml .= '<span class="LeaveAgreed"><b>L</b></span>';
                                } else if ($dataRightInner['LeaveisOK'] == '1' && $dataRightInner['LeaveDeleted'] == '0') {
                                    if ($dataRightInner['LeaveDeleted'] == '0') {
                                        if ($dataRightInner['LeaveShortNotice'] == '1') {
                                            $cellDivHtml .= '<span class="leavePowderBlue"><b>L</b></span>';
                                        } else if ($dataRightInner['Leaveoversummer'] == '1') {
                                            $cellDivHtml .= '<span class="leavePurple"><b>L</b></span>';
                                        } else {
                                            $cellDivHtml .= '<span class="leaveYellow"><b>L</b></span>';
                                        }
                                    }
                                } else if ($dataRightInner['LeaveisOK'] == '0' && $dataRightInner['LeaveDeleted'] == '0') {
                                    if($dataRightInner['LeaveShortNotice'] == '1') {
                                        $cellDivHtml .= '<span class="leavePowderBlue"><b>L</b></span>';
                                    } else {
                                        $cellDivHtml .= '<span class="leaveGray"><b>L</b></span>';
                                    }
                                }
                            }
                            $cellDivHtml .= '</div>';
                            $cellDivHtml .= '<div class="displayInlineBlock IconPositionClass iconLock">';
                            if ($dataRightInner['ShowLock'] == 1 && (count($lockClassNameForCell)>= 1) && min($lockClassNameForCell) == $dataRightInner['ID']) {
                                $cellDivHtml .= '<div class="lock-icon-image"></div>';
                            }
                            $RequestClass = '';
                            if ($dataRightInner['ReqCount'] >= 1) {
                                $RequestClass = ' multirequestvaialble';
                            }
                            $cellDivHtml .= '</div>';
                            $cellDivHtml .= '<div class="displayInlineBlock IconPositionClass iconR' . $RequestClass . '" id="requestIcon_' . $dataRightInner['ID'] . '">';
                            $RequestImag = null;
                            if ($dataRightInner['ReqCount'] > 1) {
                                $RequestImag = '<span class="requestPurple"><b>R</b></span>';
                            } else {
                                if ($dataRightInner['LockIconColour'] == 'O' && $dataRightInner['ReqCount'] == 1) {
                                    $RequestImag = '<span class="requestOrange"><b>R</b></span>';
                                }
                                if ($dataRightInner['LockIconColour'] == 'B' && $dataRightInner['ReqCount'] == 1) {
                                    $RequestImag = '<span class="requestPowderBlue"><b>R</b></span>';
                                }
                                if ($dataRightInner['LockIconColour'] == 'Y' && $dataRightInner['ReqCount'] == 1) {
                                    $RequestImag = '<span class="requestYellow"><b>R</b></span>';
                                }
                                if ($dataRightInner['LockIconColour'] == 'G' && $dataRightInner['ReqCount'] == 1) {
                                    $RequestImag = '<span class="requestGray"><b>R</b></span>';
                                }
                            }
                            $cellDivHtml .= $RequestImag;
                            $cellDivHtml .= '</div>';
                            $cellDivHtml .= '<div class="displayInlineBlock IconPositionClass twelveIcon">';
                            $cellDivHtml .= '<span class="padd1FontW600">';
                            if ($dataRightInner['MarkOverTwelve'] == 1) {
                                $cellDivHtml .= '12';
                            }
                            $cellDivHtml .= '</span>';
                            $cellDivHtml .= '</div>';
                            $cellDivHtml .= '</div>';
                            $cellDivHtml .= '<div class="DutyIconPositionRight">';
                            if (($allowsignin) && ((trim($dataRightInner['DutyName']) != "U") && ($dataRightInner['DutyName'] != ""))) {
                                $cellDivHtml .= '<div dutyid="' . $dataRightInner['MasterDutyId'] . ' "date="' . $date->format('Y-m-m') . '" team="' . $request->get('teamId') . '" SchedulingPersonID="' . $dataRightInner['SchedulingPersonID'] . '"  dataid="' . $dataRightInner['ID'] . '" class="handcursor displayInlineBlock"' . $js;
                                $signedtooltipshow = "'showtooltip',this,'" . $uIdStr."'";
                                $signedtooltiphide = "'hidetooltip'";
                                $cellDivHtml .= ' onmouseover="showsignedtip(' . $signedtooltipshow . ')" onmouseleave="showsignedtip(' . $signedtooltiphide . ')">';
                                $cellDivHtml .= '<div class="' . $imageNameSignInClsName . '"></div>';
                                $cellDivHtml .= '</div>';
                            }
                            $cellDivHtml .= '<div id="circle" class="' . $className . ' circleIcon" ';
                            if ($dataRightInner['DutyComments'] != "0" || $dataRightInner['PersonComments'] != "0") {
                                $dutytooltipshow = "'showtooltip',this," . $dataRightInner['AllocationsDutyID']. ", ". $dataRightInner['ID']. ", ". $dataRightInner['AllocationsSPID'];
                                $dutytooltiphide = "'hidetooltip'";
                                $cellDivHtml .= ' onmouseover="showdutycomments(' . $dutytooltipshow . ')" onmouseleave="showdutycomments(' . $dutytooltiphide . ')"';
                                
                            }
                            $cellDivHtml .= ' datateamid="' . $dataRightInner['SchedulingTeamId'] . '" dataschpersonid="' . $dataRightInner['SchedulingPersonID'] . '" dataid="' . $dataRightInner['ID'] . '" dutydate="' . $dataRightInner['DutyDate'] . '" ></div>';
                            $cellDivHtml .= '</div>';

                            $cellDivHtml .= '</div>';
                            $cellDivHtml .= '</div>';
                            $dataRightInnerStr = $dataRightInner;
                        }
                    }
                }
                if ($request->get('dataDragType') == 'SWAP') {
                    $cellValue['div75'][$id]['dtCellWTDClassName'] = $wtdClassNameForCell;
                    $cellValue['div75'][$id]['dtCellUnder11ClassName'] = $under11ClassNameForCell;
                    $cellValue['div75'][$id]['dtCellVal'] = $cellDivHtml;
                    $cellValue['div75'][$id]['cellData'] = $dataRightInnerStr;
                    $cellDivHtml = '';
                } else {
                    $cellValue['div75']['dtCellWTDClassName'] = $wtdClassNameForCell;
                    $cellValue['div75']['dtCellUnder11ClassName'] = $under11ClassNameForCell;
                    $cellValue['div75']['dtCellVal'] = $cellDivHtml;
                    if ((strtoupper($request->get('dataDragType')) == 'ALLOCTOUNALLOC') || (strtoupper($request->get('dataDragType')) == 'MISC')) {
                        $cellValue['div75']['unAllocNewId'] = $allocateds[0]['AllocationsDutyID'];
						//setcookie("uId", $_COOKIE['uId']+1);
                    }
                    if ((strtoupper($request->get('dataDragType')) == 'UNALLOCTOALLOC') || (strtoupper($request->get('dataDragType')) == 'ALLOCTOUNALLOC') || (strtoupper($request->get('dataDragType')) == 'MISC')) {
                        $cellValue['div75']['unallocDutyData'] = $unallocDutyData;
                    }
                    $cellValue['div75']['dutyExists'] = $dutyExists;
                    $cellValue['div75']['cellData'] = $dataRightInnerStr;
                    $cellValue['div75']['mastMiscFilterDuties'] = $mastFilterDutyNames;
                }
            }
        }
    }
    $response['dataTd'] = $cellValue;
    $response['spStatus'] = $processHtml;
} else {
    $response['spStatus'] = isset($spResult['spStatus']) ? $spResult['spStatus'] : null;
    $response['spErrorMessage'] = isset($spResult['errorMessage']) ? $spResult['errorMessage'] : null;
}

echo json_encode($response);
