<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Symfony\Component\HttpFoundation\Request;

include_once __DIR__ . '/../../function-includes/DBHelper.php';
include_once __DIR__ . '/../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService();
$pdo = OpenDBLinkA7();
$request ??= Request::createFromGlobals();
$action = $request->get('action');
$filterOperators = '&;*!%';
$rpStr = ',';

if ($action == 'getdetails') {
    $returnData = [];
    $filterId = $request->get('filterId');
    $strQuery = "SELECT SchedulingTeamId,UserID,Description,DutyFilter,SortCodeFilter,SortOrder,AndMatch,ID,StaffName,CostCode,SkillName,JobName,JobLabel,AdditionalTeams,DutyLabel,isPublic,JobNameAll,JobLabelAll,DutyTime FROM AutoPagesFilters WHERE ID=:filterId";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindValue(':filterId', $filterId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $returnData['UserID'] = (isset($row['UserID']) && !empty($row['UserID'])) ? $row['UserID'] : 0;
    $returnData['Description'] = (isset($row['Description']) && !empty($row['Description'])) ? $row['Description'] : '';
    $returnData['SortOrder'] = (isset($row['SortOrder']) && !empty($row['SortOrder'])) ? $row['SortOrder'] : 0;
    $returnData['AndMatch'] = (isset($row['AndMatch']) && !empty($row['AndMatch'])) ? $row['AndMatch'] : 0;
    $returnData['ID'] = (isset($row['ID']) && !empty($row['ID'])) ? $row['ID'] : 0;
    $returnData['isPublic'] = (isset($row['isPublic']) && !empty($row['isPublic'])) ? $row['isPublic'] : 0;
    $returnData['SchedulingTeamId'] = (isset($row['SchedulingTeamId']) && !empty($row['SchedulingTeamId'])) ? $row['SchedulingTeamId'] : 0;

    $row['DutyFilter'] = (isset($row['DutyFilter']) && !empty($row['DutyFilter'])) ? $row['DutyFilter'] : '';
	
    $row['SortCodeFilter'] = (isset($row['SortCodeFilter']) && !empty($row['SortCodeFilter'])) ? $row['SortCodeFilter'] : '';
    $row['StaffName'] = (isset($row['StaffName']) && !empty($row['StaffName'])) ? $row['StaffName'] : '';
    $row['CostCode'] = (isset($row['CostCode']) && !empty($row['CostCode'])) ? $row['CostCode'] : '';
    $row['SkillName'] = (isset($row['SkillName']) && !empty($row['SkillName'])) ? $row['SkillName'] : '';
	$row['DutyTime'] = (isset($row['DutyTime']) && !empty($row['DutyTime'])) ? $row['DutyTime'] : '';
    $row['JobName'] = (isset($row['JobName']) && !empty($row['JobName'])) ? $row['JobName'] : '';
    $row['JobLabel'] = (isset($row['JobLabel']) && !empty($row['JobLabel'])) ? $row['JobLabel'] : '';
    $row['AdditionalTeams'] = (isset($row['AdditionalTeams']) && !empty($row['AdditionalTeams'])) ? $row['AdditionalTeams'] : '';
    $row['DutyLabel'] = (isset($row['DutyLabel']) && !empty($row['DutyLabel'])) ? $row['DutyLabel'] : '';

    $DutyFilter = replaceFilterWithComma($row['DutyFilter'], $filterOperators, $rpStr);
    $returnData['DutyFilter'] = $DutyFilter['filterStr'];
    $returnData['DutyOptFilter'] = $DutyFilter['filterOpt'];

    $SortCodeFilter = replaceFilterWithComma($row['SortCodeFilter'], $filterOperators, $rpStr);
    $returnData['SortCodeFilter'] = $SortCodeFilter['filterStr'];
    $returnData['SortCodeOptFilter'] = $SortCodeFilter['filterOpt'];

    $StaffNameFilter = replaceFilterWithComma($row['StaffName'], $filterOperators, $rpStr);
    $returnData['StaffName'] = $StaffNameFilter['filterStr'];
    $returnData['StaffNameOptFilter'] = $StaffNameFilter['filterOpt'];

    $CostCodeFilter = replaceFilterWithComma($row['CostCode'], $filterOperators, $rpStr);
    $returnData['CostCode'] = $CostCodeFilter['filterStr'];
    $returnData['CostCodeOptFilter'] = $CostCodeFilter['filterOpt'];

    $SkillNameFilter = replaceFilterWithComma($row['SkillName'], $filterOperators, $rpStr);
    $returnData['SkillName'] = $SkillNameFilter['filterStr'];
    $returnData['SkillNameOptFilter'] = $SkillNameFilter['filterOpt'];
	
    $returnData['DutyTime'] = (isset($row['DutyTime']) && !empty($row['DutyTime'])) ? $row['DutyTime'] : -1;
    
    $JobNameFilter = replaceFilterWithComma($row['JobName'], $filterOperators, $rpStr);
    $returnData['JobName'] = $JobNameFilter['filterStr'];
    $returnData['JobNameOptFilter'] = $JobNameFilter['filterOpt'];

    $JobLabelFilter = replaceFilterWithComma($row['JobLabel'], $filterOperators, $rpStr);
    $returnData['JobLabel'] = $JobLabelFilter['filterStr'];
    $returnData['JobLabelOptFilter'] = $JobLabelFilter['filterOpt'];

    $AdditionalTeamsFilter = replaceFilterWithComma($row['AdditionalTeams'], $filterOperators, $rpStr);
    $returnData['AdditionalTeams'] = $AdditionalTeamsFilter['filterStr'];
    $returnData['AdditionalTeamsOptFilter'] = $AdditionalTeamsFilter['filterOpt'];

    $DutyLabelFilter = replaceFilterWithComma($row['DutyLabel'], $filterOperators, $rpStr);
    $returnData['DutyLabel'] = $DutyLabelFilter['filterStr'];
    $returnData['DutyLabelOptFilter'] = $DutyLabelFilter['filterOpt'];

    $returnData['JobNameAll'] = (isset($row['JobNameAll']) && !empty($row['JobNameAll'])) ? $row['JobNameAll'] : 0;
    $returnData['JobLabelAll'] = (isset($row['JobLabelAll']) && !empty($row['JobLabelAll'])) ? $row['JobLabelAll'] : 0;
    echo json_encode($returnData);
}

if ($action == 'savepublic') {
    $filterStaffName = replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('StaffName'), $rpStr), $request->get('StaffNameFilter'), $rpStr);
    $filterSortCode = replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), $request->get('SortCodeFilter'), $rpStr);
    $filterCostCode = replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('CostCode'), $rpStr), $request->get('CostCodeFilter'), $rpStr);
    $filterSkill = replaceCommaBySelectedOperator($request->get('Skill'), $request->get('SkillFilter'), $rpStr);
	$filterDutyTime = $request->get('DutyTime');
    $filterDuty = replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('Duty'), $rpStr), $request->get('DutyFilter'), $rpStr);
    $filterJobName = replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('JobName'), $rpStr), $request->get('JobNameFilter'), $rpStr);
    $filterJobLabel = replaceCommaBySelectedOperator($request->get('JobLabel'), $request->get('JobLabelFilter'), $rpStr);
    $filterAdditionalTeams = replaceCommaBySelectedOperator($request->get('AdditionalTeams'), $request->get('AdditionalTeamsFilter'), $rpStr);
    $filterDutyLabel = replaceCommaBySelectedOperator($request->get('DutyLabel'), $request->get('DutyLabelFilter'), $rpStr);
    $SortOrder = $request->get('SortOrder');
    $FilterName = $request->get('FilterName');
    $userId = $_COOKIE['editWeeklyUserId'] ?? $_SESSION['user']['UserID'];
    $teamId = $request->get('teamId');
    $actionType = $request->get('actionType');
    $isPublic = $request->get('isPublic');
    $ID = $request->get('ID');
    $andMatch = 0;
    $jobNameAll = $request->get('jobNameAll');
    $jobLabelAll = $request->get('jobLabelAll');
    if ($request->get('andMatch') == '1') {
        $andMatch = 1;
    }

    $returnFilters['status'] = false;
    $returnFilters['updateDropdown'] = false;

    $checkFilterExists = checkFilterExistsForTeam($FilterName, $teamId, $pdo);
    if (!empty($checkFilterExists['ID']) && $request->get('checkExists') == 'Yes') {
        if ($checkFilterExists['isPublic'] == 1) {
            $returnFilters['status'] = true;
            $returnFilters['isExists'] = true;
            $returnFilters['confirmMessage'] = 'Filter with name (' . $FilterName . ') already exists. Would you like to update the filter';
        } else {
            $returnFilters['status'] = true;
            $returnFilters['isExists'] = false;
        }
    } else {
        if (!empty($checkFilterExists['ID']) && $checkFilterExists['isPublic'] == 1) {
            $updateFilter = setAutoPagesFilters($checkFilterExists['ID'], $FilterName, $filterDuty, $filterSortCode, $teamId, $userId, $filterStaffName, $filterCostCode, $filterSkill, $filterJobName, $filterJobLabel, $filterAdditionalTeams, $filterDutyLabel, $checkFilterExists['isPublic'], $andMatch, $pdo, $SortOrder,$jobNameAll, $jobLabelAll,$filterDutyTime);
            if ($updateFilter) {
                $returnFilters['status'] = true;
                $returnFilters['lastInsId'] = $checkFilterExists['ID'];
                $returnFilters['errMsg'] = '';
            } else {
                $returnFilters['status'] = false;
                $returnFilters['errMsg'] = 'Error in updating filter.';
            }
        } else {
            if ($request->get('checkExists') == 'No') {
                $insertFilter = setAutoPagesFilters(0, $FilterName, $filterDuty, $filterSortCode, $teamId, $userId, $filterStaffName, $filterCostCode, $filterSkill, $filterJobName, $filterJobLabel, $filterAdditionalTeams, $filterDutyLabel, $isPublic, $andMatch, $pdo, $SortOrder,$jobNameAll, $jobLabelAll,$filterDutyTime);
                $returnFilters['status'] = false;
                if ($insertFilter) {
                    $returnFilters['status'] = true;
                    $returnFilters['updateDropdown'] = true;
                    $returnFilters['filters'] = getFiltersListByType(1, $teamId, $userId, $pdo);
                    $returnFilters['lastInsId'] = $insertFilter;
                    $returnFilters['errMsg'] = '';
                } else {
                    $returnFilters['errMsg'] = 'Error in updating filter.';
                }
            } else {
                $returnFilters['status'] = true;
                $returnFilters['isExists'] = false;
            }
        }
    }
    echo json_encode($returnFilters);
}

if ($action == 'saveprivate') {
    $filterStaffName = replaceCommaBySelectedOperator(
        replaceWhiteSpaces($request->get('StaffName'), $rpStr),
        $request->get('StaffNameFilter'),
        $rpStr
    );
    $filterSortCode = replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), $request->get('SortCodeFilter'), $rpStr);
    $filterCostCode = replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('CostCode'), $rpStr), $request->get('CostCodeFilter'), $rpStr);
    $filterSkill = replaceCommaBySelectedOperator($request->get('Skill'), $request->get('SkillFilter'), $rpStr);
    $filterDuty = replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('Duty'), $rpStr), $request->get('DutyFilter'), $rpStr);
	$filterDutyTime = $request->get('DutyTime');
    $filterJobName = replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('JobName'), $rpStr), $request->get('JobNameFilter'), $rpStr);
    $filterJobLabel = replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('JobLabel'), $rpStr), $request->get('JobLabelFilter'), $rpStr);
    $filterAdditionalTeams = replaceCommaBySelectedOperator($request->get('AdditionalTeams'), $request->get('AdditionalTeamsFilter'), $rpStr);
    $filterDutyLabel = replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('DutyLabel'), $rpStr), $request->get('DutyLabelFilter'), $rpStr);

    $SortOrder = $request->get('SortOrder');
    $FilterName = $request->get('FilterName');
    $userId = $_COOKIE['editWeeklyUserId'] ?? $_SESSION['user']['UserID'];
    $teamId = $request->get('teamId');
    $actionType = $request->get('actionType');
    $isPublic = $request->get('isPublic');
    $ID = $request->get('ID');
    $andMatch = 0;
    if ($request->get('andMatch') == '1') {
        $andMatch = 1;
    }
    $jobNameAll = $request->get('jobNameAll');
    $jobLabelAll = $request->get('jobLabelAll');
    $selectedPublicFilter = $request->get('selectedPublicFilter');
    $returnFilters['status'] = false;
    $returnFilters['updateDropdown'] = false;

    $checkFilterExists = checkPrivateFilterExistsForTeam($FilterName, $teamId, $userId, $pdo);
    if (!empty($checkFilterExists['ID']) && $request->get('checkExists') == 'Yes') {
        if ($checkFilterExists['isPublic'] == 0) {
            $returnFilters['status'] = true;
            $returnFilters['isExists'] = true;
            $returnFilters['confirmMessage'] = 'Filter with name (' . $FilterName . ') already exists. Would you like to update the filter';
        } else {
            $returnFilters['status'] = true;
            $returnFilters['isExists'] = false;
        }
    } else {
        if (!empty($checkFilterExists['ID']) && $checkFilterExists['isPublic'] == 0) {
            $updateFilter = setAutoPagesFilters($checkFilterExists['ID'], $FilterName, $filterDuty, $filterSortCode, $teamId, $userId, $filterStaffName, $filterCostCode, $filterSkill, $filterJobName, $filterJobLabel, $filterAdditionalTeams, $filterDutyLabel, $checkFilterExists['isPublic'], $andMatch, $pdo, $SortOrder,$jobNameAll, $jobLabelAll,$filterDutyTime);
            if ($updateFilter) {
                $returnFilters['status'] = true;
                $returnFilters['lastInsId'] = $checkFilterExists['ID'];
                $returnFilters['errMsg'] = '';
            } else {
                $returnFilters['status'] = false;
                $returnFilters['errMsg'] = 'Error in updating filter.';
            }
        } else {
            if ($request->get('checkExists') == 'No') {
                $insertFilter = setAutoPagesFilters(0, $FilterName, $filterDuty, $filterSortCode, $teamId, $userId, $filterStaffName, $filterCostCode, $filterSkill, $filterJobName, $filterJobLabel, $filterAdditionalTeams, $filterDutyLabel, $isPublic, $andMatch, $pdo, $SortOrder,$jobNameAll, $jobLabelAll,$filterDutyTime);
                if ($insertFilter) {
                    $returnFilters['status'] = true;
                    $returnFilters['updateDropdown'] = true;
                    $returnFilters['filters'] = getFiltersListByType(0, $teamId, $userId, $pdo);
                    $returnFilters['lastInsId'] = $insertFilter;
                    $returnFilters['errMsg'] = '';
                } else {
                    $returnFilters['errMsg'] = 'Error in updating filter.';
                }
            } else {
                $returnFilters['status'] = true;
                $returnFilters['isExists'] = false;
            }
        }
    }
    echo json_encode($returnFilters);
}

if ($action == 'deletefilter') {
    $filterType = $request->get('filterType');
    $teamId = $request->get('teamId');
    $ID = $request->get('filterId');
    $userId = $request->get('userId');
    $strQuery = "DELETE FROM AutoPagesFilters WHERE ID=:ID";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_INT);
    $stmt->execute();
	
	/* Update  daily, weekly,multiweek and editweekly filter  as NULL */
	
	UpdateFiltersAfterDelete($ID);
	
    $updatedDD = getFiltersListByType(1, $teamId, $userId, $pdo);
    if (strtoupper((string) $filterType) == 'PRIVATE') {
        $updatedDD = getFiltersListByType(0, $teamId, $userId, $pdo);
    }
    $returnFilters['status'] = true;
    $returnFilters['filters'] = $updatedDD;
    echo json_encode($returnFilters);
}

if ($action == 'createquery') {
    $screenName = $request->get('screenName');
    $orderByCond = '';
    if ($screenName == 'EditWeekly' || $screenName == 'EditWeeklyNameFilter') {
        $finalQueryStringCond = [];
        if ($request->get('StaffName') != '') {
            $filterStaffNameCond = createQueryCondition('sp.DisplayName', $request->get('StaffNameFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('StaffName'), $rpStr), $request->get('StaffNameFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('StaffNameFilter'), 'condStr' => replaceWhiteSpaces($request->get('StaffName'), $rpStr), 'colName' => 'sp.DisplayName', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterStaffNameCond], $pdo);
        }

        if ($request->get('SortCode') != '') {
            $filterSortCodeCond = createQueryCondition('spl.SortCode', $request->get('SortCodeFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), $request->get('SortCodeFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('SortCodeFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), 'colName' => 'spl.SortCode', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterSortCodeCond], $pdo);
        }

        if ($request->get('CostCode') != '') {
            $filterCostCodeCond = createQueryCondition('sct.CostCode', $request->get('CostCodeFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('CostCode'), $rpStr), $request->get('CostCodeFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('CostCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('CostCode'), $rpStr), 'colName' => 'sct.CostCode', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterCostCodeCond], $pdo);
        }

        if (!empty($request->get('Skill'))) {
            $searchSkills = $request->get('Skill');
            if (is_array($request->get('Skill'))) {
                $searchSkills = implode(',', $request->get('Skill'));
            }
            $filterSkillCond = createQueryCondition('spsl.programmes_id', $request->get('SkillFilter'), replaceCommaBySelectedOperator($searchSkills, $request->get('SkillFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = $filterSkillCond;
        }

         if ($request->get('Duty') != '') {
            $filterDutyCond = createQueryCondition('a.DutyName', $request->get('DutyFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('Duty'), $rpStr), $request->get('DutyFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('DutyFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('Duty'), $rpStr), 'colName' => 'a.DutyName', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterDutyCond], $pdo);
        }

        if ($request->get('JobName') != '') {
            $filterJobNameCond = createQueryCondition('aj.JobName', $request->get('JobNameFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('JobName'), $rpStr), $request->get('JobNameFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('JobNameFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('JobName'), $rpStr), 'colName' => 'aj.JobName', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterJobNameCond], $pdo);
        }

        if (!empty($request->get('JobLabel'))) {
            $searchJobLabel = $request->get('JobLabel');
            if (is_array($request->get('JobLabel'))) {
                $searchJobLabel = implode(',', $request->get('JobLabel'));
            }
            $filterJobLabelCond = createQueryCondition('aj.ProgrammeId', $request->get('JobLabelFilter'), replaceCommaBySelectedOperator($searchJobLabel, $request->get('JobLabelFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('JobLabelFilter'), 'condStr' => $searchJobLabel, 'colName' => 'aj.ProgrammeId', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterJobLabelCond], $pdo);
        }

        if (!empty($request->get('DutyLabel'))) {
            $searchDutyLabel = $request->get('DutyLabel');
            if (is_array($request->get('DutyLabel'))) {
                $searchDutyLabel = implode(',', $request->get('DutyLabel'));
            }
            $filterDutyLabelCond = createQueryCondition('a.dutyProgramId', $request->get('DutyLabelFilter'), replaceCommaBySelectedOperator($searchDutyLabel, $request->get('DutyLabelFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = $filterDutyLabelCond;
        }

        match ($request->get('SortOrder')) {
            "1" => $orderByCond .= 'DisplayLastName, DisplayFirstName ASC',
            "2" => $orderByCond .= 'spl.SortCode, DisplayLastName, DisplayFirstName ASC',
            default => $orderByCond .= 'a.DutyName',
        };
        if(!empty($request->get('quickFilter')) && count($finalQueryStringCond) > 1) {
            $nameFilter = $finalQueryStringCond[0];
            unset($finalQueryStringCond[0]);
        }        
        $finalQueryString = implode(' OR ', $finalQueryStringCond);
        if ($request->get('andMatch') == '1') {
            $finalQueryString = implode(' AND ', $finalQueryStringCond);
        }
        if(isset($nameFilter)) {
            $finalQueryString = $nameFilter . ' AND (' . $finalQueryString . ')';
        }
        $returnQueryData['queryString'] = $finalQueryString;
        $returnQueryData['orderByString'] = $orderByCond;
    }

    if ($screenName == 'ViewDaily') {
        $subQueryStringCond1 = [];
        $subQueryStringCond2 = [];
        $subQueryStringCond3 = [];
		
        if ($request->get('StaffName') != '') {
            $filterStaffNameCond = createQueryCondition('sp.DisplayName', $request->get('StaffNameFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('StaffName'), $rpStr), $request->get('StaffNameFilter'), $rpStr), $screenName, $pdo);
		    $subQueryStringCond1[] = getFinalConditionString(['optStr' => $request->get('StaffNameFilter'), 'condStr' => replaceWhiteSpaces($request->get('StaffName'), $rpStr), 'colName' => 'sp.DisplayName', 'screenName' => $screenName, 'intWeek' => $request->get('intWeek'), 'intDay' => $request->get('intDay'), 'intTeamID' => $request->get('intTeamID'), 'rolepermission' => $request->get('rolepermission'), 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'filterQueryCond' => $filterStaffNameCond, 'applyOn' => 'both'], $pdo);
        }

        if ($request->get('SortCode') != '') {
            $filterSortCodeCond = createQueryCondition('spl.SortCode', $request->get('SortCodeFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), $request->get('SortCodeFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond1[] = getFinalConditionString(['optStr' => $request->get('SortCodeFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), 'colName' => 'spl.SortCode', 'screenName' => $screenName, 'intWeek' => $request->get('intWeek'), 'intDay' => $request->get('intDay'), 'intTeamID' => $request->get('intTeamID'), 'rolepermission' => $request->get('rolepermission'), 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'filterQueryCond' => $filterSortCodeCond, 'applyOn' => 'both'], $pdo);
        }

        if ($request->get('CostCode') != '') {
            $filterCostCodeCond = createQueryCondition('sct.CostCode', $request->get('CostCodeFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('CostCode'), $rpStr), $request->get('CostCodeFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond1[] = getFinalConditionString(['optStr' => $request->get('CostCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('CostCode'), $rpStr), 'colName' => 'sct.CostCode', 'screenName' => $screenName, 'intWeek' => $request->get('intWeek'), 'intDay' => $request->get('intDay'), 'intTeamID' => $request->get('intTeamID'), 'rolepermission' => $request->get('rolepermission'), 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'filterQueryCond' => $filterCostCodeCond, 'applyOn' => 'both'], $pdo);
        }

        if (!empty($request->get('Skill'))) {
            $searchSkills = $request->get('Skill');
            if (is_array($request->get('Skill'))) {
                $searchSkills = implode(',', $request->get('Skill'));
            }
            $filterSkillCond = createQueryCondition('spsl.programmes_id', $request->get('SkillFilter'), replaceCommaBySelectedOperator($searchSkills, $request->get('SkillFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond1[] = $filterSkillCond;
        }
		
		if ($request->get('DutyTime') != '-1') {
		    $filterDutyTimeCond = createQueryCondition('a.StartTime', $request->get('DutyTimeFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('DutyTime'), $rpStr), $request->get('DutyTimeFilter'), $rpStr), $screenName, $pdo);
		    $subQueryStringCond2[] = getFinalConditionString(['optStr' => $request->get('DutyTimeFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('DutyTime'), $rpStr), 'colName' => 'a.StartTime', 'screenName' => $screenName, 'intWeek' => $request->get('intWeek'), 'intDay' => $request->get('intDay'), 'intTeamID' => $request->get('intTeamID'), 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'rolepermission' => $request->get('rolepermission'), 'filterQueryCond' => $filterDutyTimeCond, 'applyOn' => 'single'], $pdo);
	    }
		
        if (!empty($request->get('AdditionalTeams'))) {
            $searchAdditionalTeams = $request->get('AdditionalTeams');
            if (is_array($request->get('AdditionalTeams'))) {
                $searchAdditionalTeams = implode(',', $request->get('AdditionalTeams'));
            }
            $filterAdditionalTeamsCond = createQueryCondition('spl.TeamID', $request->get('AdditionalTeamsFilter'), replaceCommaBySelectedOperator($searchAdditionalTeams, $request->get('AdditionalTeamsFilter'), $rpStr), $screenName, $pdo);
            $condStr = $searchAdditionalTeams;
        } else {
            $filterAdditionalTeamsCond = "(spl.TeamID IN(" . $request->get('intTeamID') . "))";
            $condStr = $request->get('AdditionalTeams');
        }
        $subQueryStringCond3[] = getFinalConditionString(['optStr' => $request->get('AdditionalTeamsFilter'), 'condStr' => $condStr, 'colName' => 'spl.TeamID', 'screenName' => $screenName, 'intWeek' => $request->get('intWeek'), 'intDay' => $request->get('intDay'), 'intTeamID' => $request->get('intTeamID'), 'rolepermission' => $request->get('rolepermission'), 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'filterQueryCond' => $filterAdditionalTeamsCond, 'applyOn' => 'both'], $pdo);

         if ($request->get('Duty') != '') {
            $filterDutyCond = createQueryCondition('a.DutyName', $request->get('DutyFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('Duty'), $rpStr), $request->get('DutyFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond2[] = getFinalConditionString(['optStr' => $request->get('DutyFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('Duty'), $rpStr), 'colName' => 'a.DutyName', 'screenName' => $screenName, 'intWeek' => $request->get('intWeek'), 'intDay' => $request->get('intDay'), 'intTeamID' => $request->get('intTeamID'), 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'rolepermission' => $request->get('rolepermission'), 'filterQueryCond' => $filterDutyCond, 'applyOn' => 'single'], $pdo);
        }

        if ($request->get('JobName') != '') {
            $filterJobNameCond = createQueryCondition('aj.JobName', $request->get('JobNameFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('JobName'), $rpStr), $request->get('JobNameFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond2[] = getFinalConditionString(['optStr' => $request->get('JobNameFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('JobName'), $rpStr), 'colName' => 'aj.JobName', 'screenName' => $screenName, 'intWeek' => $request->get('intWeek'), 'intDay' => $request->get('intDay'), 'intTeamID' => $request->get('intTeamID'), 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'rolepermission' => $request->get('rolepermission'), 'filterQueryCond' => $filterJobNameCond, 'applyOn' => 'single'], $pdo);
        }

        if (!empty($request->get('JobLabel'))) {
            $searchJobLabel = $request->get('JobLabel');
            if (is_array($request->get('JobLabel'))) {
                $searchJobLabel = implode(',', $request->get('JobLabel'));
            }
            $filterJobLabelCond = createQueryCondition('aj.ProgrammeId', $request->get('JobLabelFilter'), replaceCommaBySelectedOperator($searchJobLabel, $request->get('JobLabelFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond2[] = $filterJobLabelCond;
        }

        if (!empty($request->get('DutyLabel'))) {
            $searchDutyLabel = $request->get('DutyLabel');
            if (is_array($request->get('DutyLabel'))) {
                $searchDutyLabel = implode(',', $request->get('DutyLabel'));
            }
            $filterDutyLabelCond = createQueryCondition('a.dutyProgramId', $request->get('DutyLabelFilter'), replaceCommaBySelectedOperator($searchDutyLabel, $request->get('DutyLabelFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond2[] = $filterDutyLabelCond;

        }

        match ($request->get('SortOrder')) {
            "1" => $orderByCond .= ' ORDER BY DisplayFirstName, DisplayLastName ASC',
            "2" => $orderByCond .= ' ORDER BY spl.SortCode ASC',
            "3" => $orderByCond .= ' ORDER BY a.DutyName ASC',
            "4" => $orderByCond .= ' ORDER BY a.StartTime, DisplayFirstName, DisplayLastName ASC',
            default => $orderByCond .= ' ORDER BY StartTime ASC',
        };

        $queryStr = '';
        $subQueryString1 = '';
        $queryStr3 = '';
        if (!empty($subQueryStringCond1) || !empty($subQueryStringCond2)) {
            $subQueryString1 = implode(' OR ', $subQueryStringCond1);
            $subQueryString2 = implode(' OR ', $subQueryStringCond2);
            if ($request->get('andMatch') == '1') {
                $subQueryString1 = implode(' AND ', $subQueryStringCond1);
                $subQueryString2 = implode(' AND ', $subQueryStringCond2);
            }
            if ($subQueryString1 != '') {
                $queryStr = $subQueryString1;
            }
            if ($subQueryString2 != '') {
                $queryStr = $subQueryString1 . " OR " . $subQueryString2;
            }
            if ($subQueryString1 != '' && $subQueryString2 == '') {
                $queryStr = $subQueryString1;
            }
            if ($subQueryString1 == '' && $subQueryString2 != '') {
                $queryStr = $subQueryString2;
            }
            if ($subQueryString1 != '' && $subQueryString2 != '') {
                if ($request->get('andMatch') == '1') {
                    $queryStr = $subQueryString1 . " AND " . $subQueryString2;
                }
                if ($request->get('andMatch') == '0') {
                    $queryStr = $subQueryString1 . " OR " . $subQueryString2;
                }
            }
            if ($subQueryString1 == '' && $subQueryString2 == '') {
                $queryStr = '';
            }
        }

        if (!empty($subQueryStringCond3)) {
            $queryStr3 = $subQueryStringCond3[0];
        }

        $returnQueryData['queryStr1'] = $queryStr;
        $returnQueryData['queryStr2'] = $subQueryString1;
        $returnQueryData['queryStr3'] = $queryStr3;
        $returnQueryData['orderStr'] = $orderByCond;
    }

    if ($screenName == 'ViewWeekly') {
        $subQueryStringCond = [];
        $subQueryStringCond2 = [];
        $queryStrForSchduled = [];
        if ($request->get('StaffName') != '') {
            $filterStaffNameCond = createQueryCondition('sp.DisplayName', $request->get('StaffNameFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('StaffName'), $rpStr), $request->get('StaffNameFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('StaffNameFilter'), 'condStr' => replaceWhiteSpaces($request->get('StaffName'), $rpStr), 'colName' => 'sp.DisplayName', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterStaffNameCond], $pdo);
            $queryStrForSchduled[] = getFinalConditionString(['optStr' => $request->get('StaffNameFilter'), 'condStr' => replaceWhiteSpaces($request->get('StaffName'), $rpStr), 'colName' => 'sp.DisplayName', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterStaffNameCond], $pdo);
        }

        if ($request->get('SortCode') != '') {
            $filterSortCodeCond = createQueryCondition('spl.SortCode', $request->get('SortCodeFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), $request->get('SortCodeFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('SortCodeFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), 'colName' => 'spl.SortCode', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterSortCodeCond], $pdo);
            $queryStrForSchduled[] = getFinalConditionString(['optStr' => $request->get('SortCodeFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), 'colName' => 'spl.SortCode', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterSortCodeCond], $pdo);
        }

        if ($request->get('CostCode') != '') {
            $filterCostCodeCond = createQueryCondition('sct.CostCode', $request->get('CostCodeFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('CostCode'), $rpStr), $request->get('CostCodeFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('CostCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('CostCode'), $rpStr), 'colName' => 'sct.CostCode', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterCostCodeCond], $pdo);
            $queryStrForSchduled[] = getFinalConditionString(['optStr' => $request->get('CostCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('CostCode'), $rpStr), 'colName' => 'sct.CostCode', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterCostCodeCond], $pdo);
        }

        if (!empty($request->get('Skill'))) {
            $searchSkills = $request->get('Skill');
            if (is_array($request->get('Skill'))) {
                $searchSkills = implode(',', $request->get('Skill'));
            }
            $filterSkillCond = createQueryCondition('spsl.programmes_id', $request->get('SkillFilter'), replaceCommaBySelectedOperator($searchSkills, $request->get('SkillFilter'), $rpStr), $screenName, $pdo);
            $queryStrForSchduled[] = getFinalConditionString(['optStr' => $request->get('SkillFilter'), 'condStr' => $searchSkills, 'colName' => 'spsl.programmes_id', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterSkillCond], $pdo);
            $subQueryStringCond[] = $filterSkillCond;
        }

        if (!empty($request->get('AdditionalTeams'))) {
            $searchAdditionalTeams = $request->get('AdditionalTeams');
            if (is_array($request->get('AdditionalTeams'))) {
                $searchAdditionalTeams = implode(',', $request->get('AdditionalTeams'));
            }
            $filterAdditionalTeamsCond = createQueryCondition('spl.TeamID', $request->get('AdditionalTeamsFilter'), replaceCommaBySelectedOperator($searchAdditionalTeams, $request->get('AdditionalTeamsFilter'), $rpStr), $screenName, $pdo);
            $condStr = $searchAdditionalTeams;

        } else {
            $filterAdditionalTeamsCond = "(spl.TeamID IN(" . $request->get('intTeamIDs') . "))";
            $condStr = $request->get('AdditionalTeams');
        }
        $subQueryStringCond2[] = getFinalConditionString(['optStr' => $request->get('AdditionalTeamsFilter'), 'condStr' => $condStr, 'colName' => 'spl.TeamID', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterAdditionalTeamsCond], $pdo);

        if ($request->get('Duty') != '') {
            $filterDutyCond = createQueryCondition('a.DutyName', $request->get('DutyFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('Duty'), $rpStr), $request->get('DutyFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('DutyFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('Duty'), $rpStr), 'colName' => 'a.DutyName', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterDutyCond], $pdo);
        }

        if ($request->get('JobName') != '') {
            $filterJobNameCond = createQueryCondition('aj.JobName', $request->get('JobNameFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('JobName'), $rpStr), $request->get('JobNameFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('JobNameFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('JobName'), $rpStr), 'colName' => 'aj.JobName', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterJobNameCond], $pdo);
        }

        if (!empty($request->get('JobLabel'))) {
            $searchJobLabel = $request->get('JobLabel');
            if (is_array($request->get('JobLabel'))) {
                $searchJobLabel = implode(',', $request->get('JobLabel'));
            }
            $filterJobLabelCond = createQueryCondition('aj.ProgrammeId', $request->get('JobLabelFilter'), replaceCommaBySelectedOperator($searchJobLabel, $request->get('JobLabelFilter'), $rpStr), $screenName, $pdo);
        }

        if (!empty($request->get('DutyLabel'))) {
            $searchDutyLabel = $request->get('DutyLabel');
            if (is_array($request->get('DutyLabel'))) {
                $searchDutyLabel = implode(',', $request->get('DutyLabel'));
            }
            $filterDutyLabelCond = createQueryCondition('a.dutyProgramId', $request->get('DutyLabelFilter'), replaceCommaBySelectedOperator($searchDutyLabel, $request->get('DutyLabelFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = $filterDutyLabelCond;
        }

        match ($request->get('SortOrder')) {
            "1" => $orderByCond .= ' ORDER BY Surname ASC ',
            "2" => $orderByCond .= ' ORDER BY spl.SortCode ASC ',
            "3" => $orderByCond .= ' ORDER BY a.DutyName ASC ',
            "4" => $orderByCond .= ' ORDER BY a.StartTime ASC ',
            default => $orderByCond .= ' ORDER BY Surname ASC ',
        };

        $queryStr = '';
        $queryStr2 = '';
        $queryStr3 = '';
        if (!empty($subQueryStringCond)) {
            $queryStr = implode(' OR ', $subQueryStringCond);
            if ($request->get('andMatch') == '1') {
                $queryStr = implode(' AND ', $subQueryStringCond);
            }
        }

        if (!empty($queryStrForSchduled)) {
            $queryStr3 = implode(' OR ', $queryStrForSchduled);
            if ($request->get('andMatch') == '1') {
                $queryStr3 = implode(' AND ', $queryStrForSchduled);
            }
        }

        if (!empty($subQueryStringCond2)) {
            $queryStr2 = $subQueryStringCond2[0];
        }

        $returnQueryData['queryStr'] = $queryStr;
        $returnQueryData['queryStr2'] = $queryStr2;
        $returnQueryData['queryStr3'] = $queryStr3;
        $returnQueryData['orderStr'] = $orderByCond;

        if (!empty($request->get('isShifttoCheck'))) {
            $request->request->set('isShifttoCheck', $request->get('isShifttoCheck'));
        }
    }

    if ($screenName == 'MultiWeek') {
        $subQueryStringCond = [];
        $subQueryStringCond2 = [];
        $queryStrForSchduled = [];
        if ($request->get('StaffName') != '') {
            $filterStaffNameCond = createQueryCondition('sp.DisplayName', $request->get('StaffNameFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('StaffName'), $rpStr), $request->get('StaffNameFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('StaffNameFilter'), 'condStr' => replaceWhiteSpaces($request->get('StaffName'), $rpStr), 'colName' => 'sp.DisplayName', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'filterQueryCond' => $filterStaffNameCond], $pdo);
            $queryStrForSchduled[] = getFinalConditionString(['optStr' => $request->get('StaffNameFilter'), 'condStr' => replaceWhiteSpaces($request->get('StaffName'), $rpStr), 'colName' => 'sp.DisplayName', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'filterQueryCond' => $filterStaffNameCond], $pdo);
        }

        if ($request->get('SortCode') != '') {
            $filterSortCodeCond = createQueryCondition('spl.SortCode', $request->get('SortCodeFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('SortCode'), $rpStr), $request->get('SortCodeFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('SortCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('SortCode'), $rpStr), 'colName' => 'spl.SortCode', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterSortCodeCond], $pdo);
            $queryStrForSchduled[] = getFinalConditionString(['optStr' => $request->get('SortCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('SortCode'), $rpStr), 'colName' => 'spl.SortCode', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterSortCodeCond], $pdo);
        }

        if ($request->get('CostCode') != '') {
            $filterCostCodeCond = createQueryCondition('sct.CostCode', $request->get('CostCodeFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('CostCode'), $rpStr), $request->get('CostCodeFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('CostCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('CostCode'), $rpStr), 'colName' => 'sct.CostCode', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterCostCodeCond], $pdo);
            $queryStrForSchduled[] = getFinalConditionString(['optStr' => $request->get('CostCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('CostCode'), $rpStr), 'colName' => 'sct.CostCode', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterCostCodeCond], $pdo);
        }

        if (!empty($request->get('Skill'))) {
            $searchSkills = $request->get('Skill');
            if (is_array($request->get('Skill'))) {
                $searchSkills = implode(',', $request->get('Skill'));
            }
            $filterSkillCond = createQueryCondition('spsl.programmes_id', $request->get('SkillFilter'), replaceCommaBySelectedOperator($searchSkills, $request->get('SkillFilter'), $rpStr), $screenName, $pdo);
            $queryStrForSchduled[] = getFinalConditionString(['optStr' => $request->get('SkillFilter'), 'condStr' => $searchSkills, 'colName' => 'spsl.programmes_id', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterSkillCond], $pdo);
            $subQueryStringCond[] = $filterSkillCond;
        }
		if (!empty($request->get('AdditionalTeams'))) {
            $searchAdditionalTeams = $request->get('AdditionalTeams');
            if (is_array($request->get('AdditionalTeams'))) {
                $searchAdditionalTeams = implode(',', $request->get('AdditionalTeams'));
            }
            $filterAdditionalTeamsCond = createQueryCondition('a.SchedulingTeamId', $request->get('AdditionalTeamsFilter'), replaceCommaBySelectedOperator($request->get('AdditionalTeams'), $request->get('AdditionalTeamsFilter'), $rpStr), $screenName, $pdo);
            $condStr = $searchAdditionalTeams;

        } else {
            $filterAdditionalTeamsCond = "(a.SchedulingTeamId IN(" . $request->get('intTeamIDs') . "))";
            $condStr = $request->get('AdditionalTeams');
        }
        $subQueryStringCond2[] = getFinalConditionString(['optStr' => $request->get('AdditionalTeamsFilter'), 'condStr' => $condStr, 'colName' => 'a.SchedulingTeamId', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterAdditionalTeamsCond], $pdo);

        if ($request->get('Duty') != '') {
            $filterDutyCond = createQueryCondition('a.DutyName', $request->get('DutyFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('Duty'), $rpStr), $request->get('DutyFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('DutyFilter'), 'condStr' => replaceWhiteSpaces($request->get('Duty'), $rpStr), 'colName' => 'a.DutyName', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterDutyCond], $pdo);
        }

        if ($request->get('JobName') != '') {
            $filterJobNameCond = createQueryCondition('aj.JobName', $request->get('JobNameFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('JobName'), $rpStr), $request->get('JobNameFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('JobNameFilter'), 'condStr' => replaceWhiteSpaces($request->get('JobName'), $rpStr), 'colName' => 'aj.JobName', 'screenName' => $screenName, 'intSWeekNumber' => $request->get('intSWeekNumber'), 'intEWeekNumber' => $request->get('intEWeekNumber'), 'intTeamIDs' => $request->get('intTeamIDs'), 'intSortOrder' => $request->get('intSortOrder'), 'filterQueryCond' => $filterJobNameCond], $pdo);
        }

        if (!empty($request->get('JobLabel'))) {
            $searchJobLabel = $request->get('JobLabel');
            if (is_array($request->get('JobLabel'))) {
                $searchJobLabel = implode(',', $request->get('JobLabel'));
            }
            $filterJobLabelCond = createQueryCondition('aj.ProgrammeId', $request->get('JobLabelFilter'), replaceCommaBySelectedOperator($searchJobLabel, $request->get('JobLabelFilter'), $rpStr), $screenName, $pdo);
        }

        if (!empty($request->get('DutyLabel'))) {
            $searchDutyLabel = $request->get('DutyLabel');
            if (is_array($request->get('DutyLabel'))) {
                $searchDutyLabel = implode(',', $request->get('DutyLabel'));
            }
            $filterDutyLabelCond = createQueryCondition('a.dutyProgramId', $request->get('DutyLabelFilter'), replaceCommaBySelectedOperator($searchDutyLabel, $request->get('DutyLabelFilter'), $rpStr), $screenName, $pdo);
            $subQueryStringCond[] = $filterDutyLabelCond;
        }

        match ($request->get('SortOrder')) {
            "1" => $orderByCond .= ' ORDER BY Surname ASC ',
            "2" => $orderByCond .= ' ORDER BY spl.SortCode ASC ',
            "3" => $orderByCond .= ' ORDER BY a.DutyName ASC ',
            "4" => $orderByCond .= ' ORDER BY a.StartTime ASC ',
            default => $orderByCond .= ' ORDER BY Surname ASC ',
        };

        $queryStr = '';
        $queryStr2 = '';
        $queryStr3 = '';
        if (!empty($subQueryStringCond)) {
            $queryStr = implode(' OR ', $subQueryStringCond);
            if ($request->get('andMatch') == '1') {
                $queryStr = implode(' AND ', $subQueryStringCond);
            }
        }

        if (!empty($queryStrForSchduled)) {
            $queryStr3 = implode(' OR ', $queryStrForSchduled);
            if ($request->get('andMatch') == '1') {
                $queryStr3 = implode(' AND ', $queryStrForSchduled);
            }
        }

        if (!empty($subQueryStringCond2)) {
            $queryStr2 = $subQueryStringCond2[0];
        }

        $returnQueryData['queryStr'] = $queryStr;
        $returnQueryData['queryStr2'] = $queryStr2;
        $returnQueryData['queryStr3'] = $queryStr3;
        $returnQueryData['orderStr'] = $orderByCond;
    }

	if ($screenName == 'EditWeeklyRota') {
	    $finalQueryStringCond = [];
        if ($request->get('StaffName') != '') {
            $filterStaffNameCond = createQueryCondition('sp.DisplayName', $request->get('StaffNameFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('StaffName'), $rpStr), $request->get('StaffNameFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('StaffNameFilter'), 'condStr' => replaceWhiteSpaces($request->get('StaffName'), $rpStr), 'colName' => 'sp.DisplayName', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterStaffNameCond], $pdo);
        }

        if ($request->get('SortCode') != '') {
            $filterSortCodeCond = createQueryCondition('spl.SortCode', $request->get('SortCodeFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), $request->get('SortCodeFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('SortCodeFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('SortCode'), $rpStr), 'colName' => 'spl.SortCode', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterSortCodeCond], $pdo);
        }

        if ($request->get('CostCode') != '') {
            $filterCostCodeCond = createQueryCondition('sct.CostCode', $request->get('CostCodeFilter'), replaceCommaBySelectedOperator(replaceWhiteSpaces($request->get('CostCode'), $rpStr), $request->get('CostCodeFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('CostCodeFilter'), 'condStr' => replaceWhiteSpaces($request->get('CostCode'), $rpStr), 'colName' => 'sct.CostCode', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterCostCodeCond], $pdo);
        }

        if (!empty($request->get('Skill'))) {
            $searchSkills = $request->get('Skill');
            if (is_array($request->get('Skill'))) {
                $searchSkills = implode(',', $request->get('Skill'));
            }
            $filterSkillCond = createQueryCondition('spsl.programmes_id', $request->get('SkillFilter'), replaceCommaBySelectedOperator($searchSkills, $request->get('SkillFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = $filterSkillCond;
        }

         if ($request->get('Duty') != '') {
            $filterDutyCond = createQueryCondition('a.DutyName', $request->get('DutyFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('Duty'), $rpStr), $request->get('DutyFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('DutyFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('Duty'), $rpStr), 'colName' => 'a.DutyName', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterDutyCond], $pdo);
        }

        if ($request->get('JobName') != '') {
            $filterJobNameCond = createQueryCondition('aj.JobName', $request->get('JobNameFilter'), replaceCommaBySelectedOperatorWithoutTrim(noReplaceWhiteSpaces($request->get('JobName'), $rpStr), $request->get('JobNameFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('JobNameFilter'), 'condStr' => noReplaceWhiteSpaces($request->get('JobName'), $rpStr), 'colName' => 'aj.JobName', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterJobNameCond], $pdo);
        }

        if (!empty($request->get('JobLabel'))) {
            $searchJobLabel = $request->get('JobLabel');
            if (is_array($request->get('JobLabel'))) {
                $searchJobLabel = implode(',', $request->get('JobLabel'));
            }
            $filterJobLabelCond = createQueryCondition('aj.ProgrammeId', $request->get('JobLabelFilter'), replaceCommaBySelectedOperator($searchJobLabel, $request->get('JobLabelFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = getFinalConditionString(['optStr' => $request->get('JobLabelFilter'), 'condStr' => $searchJobLabel, 'colName' => 'aj.ProgrammeId', 'screenName' => $screenName, 'startDate' => $request->get('startDate'), 'endDate' => $request->get('endDate'), 'teamId' => $request->get('teamId'), 'filterQueryCond' => $filterJobLabelCond], $pdo);
        }

        if (!empty($request->get('DutyLabel'))) {
            $searchDutyLabel = $request->get('DutyLabel');
            if (is_array($request->get('DutyLabel'))) {
                $searchDutyLabel = implode(',', $request->get('DutyLabel'));
            }
            $filterDutyLabelCond = createQueryCondition('a.dutyProgramId', $request->get('DutyLabelFilter'), replaceCommaBySelectedOperator($searchDutyLabel, $request->get('DutyLabelFilter'), $rpStr), $screenName, $pdo);
            $finalQueryStringCond[] = $filterDutyLabelCond;
        }

        match ($request->get('SortOrder')) {
            "1" => $orderByCond .= ' ORDER BY Surname ASC ',
            "2" => $orderByCond .= 'ORDER BY spl.SortCode, DisplayLastName, DisplayFirstName ASC',
            default => $orderByCond .= 'ORDER BY a.DutyName',
        };

        $finalQueryString = implode(' OR ', $finalQueryStringCond);
        if ($request->get('andMatch') == '1') {
            $finalQueryString = implode(' AND ', $finalQueryStringCond);
        }
        $returnQueryData['queryString'] = $finalQueryString;
        $returnQueryData['orderByString'] = $orderByCond;
	}
    echo json_encode($returnQueryData);
}	
	
if ($action == 'setfilter') {
    $setFilterData = [];
    if ($request->get('screenName') == 'ViewDaily') {
        $setFilterData = [
            'action' => 'setfilterDaily',
            'screenName' => $request->get('screenName'),
            'filterId' => $request->get('filterId'),
            'teamId' => $request->get('teamId'),
        ];
    }
    if ($request->get('screenName') == 'ViewWeekly') {
        $setFilterData = [
            'action' => 'setfilterWeekly',
            'screenName' => $request->get('screenName'),
            'filterId' => $request->get('filterId'),
            'teamId' => $request->get('teamId'),
        ];
    }
    if ($request->get('screenName') == 'MultiWeek') {
        $setFilterData = [
            'action' => 'setfilterMultiWeek',
            'screenName' => $request->get('screenName'),
            'filterId' => $request->get('filterId'),
            'teamId' => $request->get('teamId'),
        ];
    }
    if ($request->get('screenName') == 'EditWeekly'){
        $setFilterData = [
            'action' => 'setfilterEditWeekly',
            'screenName' => $request->get('screenName'),
            'filterId' => $request->get('filterId'),
            'teamId' => $request->get('teamId'),
        ];
    }
	
	if ($request->get('screenName') == 'EditWeeklyRota'){
        $setFilterData = [
            'action' => 'setfilterEditWeeklyRota',
            'screenName' => $request->get('screenName'),
            'filterId' => $request->get('filterId'),
            'teamId' => $request->get('teamId'),
        ];
    }
    $returnData['status'] = setViewFilter($setFilterData, $pdo);
    echo json_encode($returnData);
}

if ($action == 'clearfilter') {
	
    if ($request->get('screenName') == 'ViewDaily') {
        $params = [
            'screenName' => $request->get('screenName'),
            'action' => 'clearDailyfilter',
            'teamId' => $request->get('teamId'),
        ];
    }
    if ($request->get('screenName') == 'ViewWeekly') {
        $params = [
            'screenName' => $request->get('screenName'),
            'action' => 'clearWeeklyfilter',
            'teamId' => $request->get('teamId'),
        ];
    }
    if ($request->get('screenName') == 'MultiWeek') {
        $params = [
            'screenName' => $request->get('screenName'),
            'action' => 'clearMultiWeekfilter',
            'teamId' => $request->get('teamId'),
        ];
    }
    if ($request->get('screenName') == 'EditWeekly') {
        $params = [
            'screenName' => $request->get('screenName'),
            'action' => 'clearEditWeeklyfilter',
            'teamId' => $request->get('teamId'),
        ];
    }
	
	if ($request->get('screenName') == 'EditWeeklyRota') {
        $params = [
            'screenName' => $request->get('screenName'),
            'action' => 'clearEditWeeklyRotafilter',
            'teamId' => $request->get('teamId'),
        ];
    }
    if(!empty($_SESSION['filterdata'])){
        $_SESSION['filterdata'] = [];
    }
    $returnData['status'] = setViewFilter($params, $pdo);
    echo json_encode($returnData);
}

if ($action == 'checkselectedfilter') {
    $getSetFilterId = $service->getCurrentSetEditWeeklyFilter($request);
    $getShiftCountFilterId = $service->getCurrentShiftCountingFilter($request);
    $shiftCountFilterId = 0;
    if (isset($getShiftCountFilterId['ID']) && !empty($getShiftCountFilterId['ShiftCountingFilter'])) {
        $shiftCountFilterId = $getShiftCountFilterId['ShiftCountingFilter'];
    }
    $returnData['status'] = true;
    $returnData['selectedFilterId'] = $getSetFilterId;
    $returnData['selectedShiftCountFilterId'] = $shiftCountFilterId;
    echo json_encode($returnData);
}

if ($action == 'Dailycheckbox') {
    $params = [
        'action' => 'Dailycheckbox',
        'screenName' => 'EditDailyScreen',
        'checkboxval' => $request->get('checkboxval'),
        'teamId' => $request->get('teamId'),
    ];
    $returnData['status'] = setViewFilter($params, $pdo);
    echo json_encode($returnData);
}

if ($action == 'Checkstartandend') {
    $params = [
        'action' => 'Checkstartandend',
        'screenName' => 'EditDailyScreen',
        'checkstartendshift' => $request->get('checkstartendshift'),
        'teamId' => $request->get('teamId'),
    ];
    $returnData['status'] = setViewFilter($params, $pdo);
    echo json_encode($returnData);
}

/**
 *  This function is used to check public filter for the team
 *
 * @param $filterName This param contains the filter name information
 * @param $teamId This param contains the teamid information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return array
 */
function checkFilterExistsForTeam($filterName = '', $teamId = '', $pdoObj = null)
{
    $strQueryCheckDup = "SELECT ID,isPublic,Description FROM AutoPagesFilters (NOLOCK) WHERE Description=:FilterName AND SchedulingTeamId=:teamId AND isPublic=1";
    $stmt = $pdoObj->prepare($strQueryCheckDup);
    $stmt->bindValue(':FilterName', $filterName, PDO::PARAM_STR);
    $stmt->bindValue(':teamId', $teamId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 *  This function is used to check private filter for the team
 *
 * @param $filterName This param contains the filter name information
 * @param $teamId This param contains the teamid information
 * @param $userID This param contains the userid information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return array
 */
function checkPrivateFilterExistsForTeam($filterName = '', $teamId = '', $userID = '', $pdoObj = null)
{
    $strQueryCheckDup = "SELECT ID,isPublic,Description FROM AutoPagesFilters (NOLOCK)  WHERE Description=:FilterName AND SchedulingTeamId=:teamId AND UserID =:userID AND isPublic=0";
    $stmt = $pdoObj->prepare($strQueryCheckDup);
    $stmt->bindValue(':FilterName', $filterName, PDO::PARAM_STR);
    $stmt->bindValue(':teamId', $teamId, PDO::PARAM_INT);
    $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 *  This function is used to replace passing string by the string which matches from the second string
 *
 * @param $replaceStr This param contains the passign string in which we will replace the substring
 * @param $searchOperators This param contains the second string which will compard by the first string and return the matched substring
 * @param $replaceWith This param contains the replace with string in the passing string
 *
 * @return array
 */
function replaceFilterWithComma($replaceStr, $searchOperators, $replaceWith)
{
    $returnStr = '';
    $returnOpt = '';
    $getMatchOpt = similar_text((string) $replaceStr, (string) $searchOperators);
    if ($getMatchOpt > 0) {
        $returnOpt = getMatchingString($searchOperators, $replaceStr);
        $expStr = explode($returnOpt, (string) $replaceStr);
        $returnStr = str_replace($returnOpt, '', $replaceStr);
        if ($expStr[1] != '') {
            $returnStr = str_replace($returnOpt, $replaceWith, $replaceStr);
        }
    }
    return ['filterStr' => $returnStr, 'filterOpt' => $returnOpt];
}

/**
 *  This function is used to replace comma separated string by the operator passed as a second argument
 *
 * @param $str This param contains the original string information
 * @param $selectedOperator This param contains the operator information which we replace in original string
 * @param $replaceWith This param contains the replace with string in the passing string
 *
 * @return string
 */
function replaceCommaBySelectedOperator($str, $selectedOperator, $replaceWith)
{
    $returnFilterStr = '';
    if ((str_contains((string) $str, (string) $replaceWith)) && $str != '') {
        $expFilterStr = explode(',', (string) $str);
        foreach ($expFilterStr as $expFilterStrVal) {
            $updFilterStr[] = trim($expFilterStrVal);
        }
        $impFilterStr = implode(',', $updFilterStr);
        $returnFilterStr = str_replace($replaceWith, $selectedOperator, $impFilterStr);
    } else {
        $returnFilterStr = $str . $selectedOperator;
    }
    return $returnFilterStr;
}

/**
 *  This function is used to add auto pages filters information in the table
 *
 * @param $ID This param contains the ID information. If it is 0 then we will add otherwise update
 * @param $FilterName This param contains the filter name information
 * @param $filterDuty This param contains the filter duty names information
 * @param $filterSortCode This param contains the filter sortcode information
 * @param $teamId This param contains the scheduling team ID information
 * @param $userId This param contains the user ID information
 * @param $filterStaffName This param contains the filter staff names information
 * @param $filterCostCode This param contains the filter costcode information
 * @param $filterSkill This param contains the filter skills information
 * @param $filterJobName This param contains the filter job name information
 * @param $filterJobLabel This param contains the filter job labels information
 * @param $filterAdditionalTeams This param contains the filter additional teams information
 * @param $filterDutyLabel This param contains the filter Duty labels information
 * @param $isPublic This param contains the filter is public/private information. If 0 then private otherwise public
 * @param $andMatch This param contains the filter and/or information. If 0 then it will apply with OR otherwise AND
 * @param $pdoObj This param contains the pdo object information
 * @param $jobNameAll This param contains the checkbox information of jobname all
 * @param $jobLabelAll This param contains the checkbox information of joblabel all
 *
 * @return string
 */
function setAutoPagesFilters($ID, $FilterName, $filterDuty, $filterSortCode, $teamId, $userId, $filterStaffName, $filterCostCode, $filterSkill, $filterJobName, $filterJobLabel, $filterAdditionalTeams, $filterDutyLabel, $isPublic, $andMatch, $pdoObj, $SortOrder,$jobNameAll, $jobLabelAll,$filterDutyTime)
{
	$teamId = (int) $teamId;
    $userId = (int) $userId;
    $isPublic = (int) $isPublic;
    $andMatch = (int) $andMatch;
    $ID = (int) $ID;

    //echo $query = "exec [dbo].[usp_add_AutoPagesFilter] '".$spresult."', '".$FilterName."','".$filterDuty."','". $filterSortCode."',".$teamId.",".$userId.",'".$filterStaffName."','".$filterCostCode."','".$filterSkill."','". $filterJobName."','".$filterJobLabel."','".$filterAdditionalTeams."',".$isPublic.",".$andMatch.",".$ID.",'". $filterDutyLabel."','".$SortOrder."',".$jobNameAll.",".$jobLabelAll.",".$filterDutyTime;
	$query = "exec ? = [dbo].[usp_add_AutoPagesFilter] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
    $spresult = 0;
    $stmt = $pdoObj->prepare($query);
    $stmt->bindParam(1, $spresult, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, PDO::SQLSRV_PARAM_OUT_DEFAULT_SIZE);
    $stmt->bindValue(2, $FilterName, PDO::PARAM_STR);
    $stmt->bindValue(3, $filterDuty, PDO::PARAM_STR);
    $stmt->bindValue(4, $filterSortCode, PDO::PARAM_STR);
    $stmt->bindValue(5, $teamId, PDO::PARAM_INT);
    $stmt->bindValue(6, $userId, PDO::PARAM_INT);
    $stmt->bindValue(7, $filterStaffName, PDO::PARAM_STR);
    $stmt->bindValue(8, $filterCostCode, PDO::PARAM_STR);
    $stmt->bindValue(9, $filterSkill, PDO::PARAM_STR);
    $stmt->bindValue(10, $filterJobName, PDO::PARAM_STR);
    $stmt->bindValue(11, $filterJobLabel, PDO::PARAM_STR);
    $stmt->bindValue(12, $filterAdditionalTeams, PDO::PARAM_STR);
    $stmt->bindValue(13, $isPublic, PDO::PARAM_INT);
    $stmt->bindValue(14, $andMatch, PDO::PARAM_INT);
    $stmt->bindValue(15, $ID, PDO::PARAM_INT);
    $stmt->bindValue(16, $filterDutyLabel, PDO::PARAM_STR);
    $stmt->bindValue(17, $SortOrder, PDO::PARAM_INT);
    $stmt->bindValue(18, $jobNameAll, PDO::PARAM_INT);
    $stmt->bindValue(19, $jobLabelAll, PDO::PARAM_INT);
	$stmt->bindValue(20, $filterDutyTime, PDO::PARAM_INT);
    $stmt->execute();
    return $spresult;
}

/**
 *  This function is used to get Auto pages filters list by passing type (public/private)
 *
 * @param $isPublic This param contains the filter is public/private information. If 0 then private otherwise public
 * @param $teamId This param contains the scheduling team ID information
 * @param $userId This param contains the user ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return string
 */
function getFiltersListByType($isPublic = 0, $teamId = 0, $userId = 0, $pdoObj = null)
{
    $filtersArray = [];
    if ($teamId > 0) {
        $sqlCond = "";
        if ($isPublic == 0) {
            $sqlCond = " AND UserID=" . $userId;
        }
        $query = "SELECT ID,Description FROM AutoPagesFilters WHERE SchedulingTeamId=:teamId AND isPublic=:isPublic" . $sqlCond . " ORDER BY Description";
        $stmt = $pdoObj->prepare($query);
        $stmt->bindValue(':teamId', $teamId, PDO::PARAM_INT);
        $stmt->bindValue(':isPublic', $isPublic, PDO::PARAM_INT);
        $stmt->execute();
        $filterRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cnt = 0;
        foreach ($filterRows as $filterRowsKey => $filterRowsVal) {
            $filtersArray[$cnt]['ID'] = $filterRowsVal['ID'];
            $filtersArray[$cnt]['FilterName'] = $filterRowsVal['Description'];
            $cnt++;
        }
    }
    return $filtersArray;
}

/**
 *  This function is used to create query string
 *
 * @param $columnName This param contains the column name information
 * @param $optStr This param contains the operator(&,!,*,;) information (&=AND, ;=OR !=NOT *=LIKE)
 * @param $queryStr This param contains the raw query string containing operators
 * @param $pdoObj This param contains the PDO object information
 *
 * @return string
 */
function createQueryCondition($columnName, $optStr, $queryStr, $screenName, $pdoObj)
{
    $returnQueryStr = '';
    $explodeQueryStr = [];
    if (str_contains((string) $queryStr, (string) $optStr)) {
        $returnQueryStr .= '(';
        if (!empty($optStr)) {
            $explodeQueryStr = array_filter(explode($optStr, (string) $queryStr));
        }
        switch ($columnName) {
            case "a.dutyProgramId":
                $totalCol = 6;
                $qcolName= $columnName == "a.dutyProgramId" ? "dutyProgramId" : $columnName;
                $mulitpleColumnNames = [];
                for($i = 1; $i <= $totalCol; $i++) {
                    if (($optStr == ';') || ($optStr == '*')) {
                        $returnQueryStr .= $qcolName . ($i != 1 ? $i: '') . " IN(" . implode(',', $explodeQueryStr) . ") " . ($i !=  $totalCol ? ' OR ' : '');
                    } else if ($optStr == '!') {
                        $returnQueryStr .= $qcolName . ($i != 1 ? $i: '') . " NOT IN(0," . implode(',', $explodeQueryStr) . ") " . ($i ==  $totalCol ? ' OR ' : '');
                    } else if ($optStr == '&') {
                        $mulitpleColumnNames[] =  $qcolName . ($i != 1 ? $i: '');
                    }
                }
                if ($optStr == '&') {
                    foreach($explodeQueryStr as $key => $id) {
                        $returnQueryStr .= "'" . $id . "' IN(" . implode(',', $mulitpleColumnNames) . ") " . ($key != (count($explodeQueryStr) - 1) ? " AND " : '');
                    }
                }
                break;
            case "aj.ProgrammeId":
            case "spsl.programmes_id":
            case "spl.TeamID":
            case "a.SchedulingTeamId":
                if (($optStr == '&') || ($optStr == ';') || ($optStr == '*')) {
                    $returnQueryStr .= $columnName . " IN(";
                } else if ($optStr == '!') {
                    $returnQueryStr .= $columnName . " NOT IN(0,";
                }
                break;
            case "a.StartTime":
                if (!empty($explodeQueryStr) && $explodeQueryStr[0] != '-1') {
                    $startval = $queryStr + 900;
                    $endval = $queryStr - 900;
                    //$returnQueryStr .= "((StartTime <= ".$explodeQueryStr[0] ." AND EndTime>= ".$explodeQueryStr[0]." ) OR (StartTime = ".$startval .") OR ( EndTime = ".$endval."))";
                    $returnQueryStr .= "(( " . $explodeQueryStr[0] . " between StartTime AND case when starttime > EndTime then EndTime + 86400 else endtime end ) OR (StartTime = " . $startval . ") OR ( EndTime = " . $endval . "))";
                }
                break;
            default:
                if (($optStr == '&') || ($optStr == ';')) {
                    $returnQueryStr .= $columnName . " = '";
                } else if ($optStr == '!') {
                    $returnQueryStr .= $columnName . " NOT LIKE '%";
                } else if ($optStr == '*') {
                    $returnQueryStr .= $columnName . " LIKE '%";
                } else if ($optStr == '%') {
                    $returnQueryStr .= $columnName . " LIKE '";
                }
                break;
        }
        for ($i = 0; $i < count($explodeQueryStr); $i++) {
            if ($explodeQueryStr[$i] != '') {
                if ($columnName != 'a.StartTime' && $columnName != 'a.dutyProgramId') {
                    $returnQueryStr .= str_replace("'", "_REPSQLCOND_", $explodeQueryStr[$i]);
                }
                if ($i < (count($explodeQueryStr) - 1) && $explodeQueryStr[$i + 1] != '') {
                    switch ($columnName) {
                        case "a.dutyProgramId":
                            break; //Query created in above switch
                        case "aj.ProgrammeId":
                        case "spsl.programmes_id":
                        case "spl.TeamID":
                        case "a.SchedulingTeamId":
                            $returnQueryStr .= ",";
                            break;
                        case "a.StartTime":
                            break;
                        case "spl.SortCode":
                        case "a.DutyName":
                        case "aj.JobName":
                            if (($optStr == '&') || ($optStr == ';')) {
                                $returnQueryStr .= "' COLLATE SQL_Latin1_General_CP1_CS_AS OR ";
                                $returnQueryStr .= $columnName . " = '";
                            } else if ($optStr == '!') {
                                $returnQueryStr .= "%' COLLATE SQL_Latin1_General_CP1_CS_AS AND ";
                                $returnQueryStr .= $columnName . " NOT LIKE '%";
                            } else if ($optStr == '*') {
                                $returnQueryStr .= "%' COLLATE SQL_Latin1_General_CP1_CS_AS OR ";
                                $returnQueryStr .= $columnName . " LIKE '%";
                            } else if ($optStr == '%') {
                                $returnQueryStr .= "%' COLLATE SQL_Latin1_General_CP1_CS_AS OR ";
                                $returnQueryStr .= $columnName . " LIKE '";
                            }
                            break;
                        default:
                            if (($optStr == '&') || ($optStr == ';')) {
                                $returnQueryStr .= "' OR ";
                                $returnQueryStr .= $columnName . " = '";
                            } else if ($optStr == '!') {
                                $returnQueryStr .= "%' AND ";
                                $returnQueryStr .= $columnName . " NOT LIKE '%";
                            } else if ($optStr == '*') {
                                $returnQueryStr .= "%' OR ";
                                $returnQueryStr .= $columnName . " LIKE '%";
                            } else if ($optStr == '%') {
                                $returnQueryStr .= "%' OR ";
                                $returnQueryStr .= $columnName . " LIKE '";
                            }
                            break;
                    }
                } else {
                    switch ($columnName) {
                        case "a.dutyProgramId":
                            break; // Query created in first switch
                        case "aj.ProgrammeId":
                        case "spsl.programmes_id":
                        case "spl.TeamID":
                        case "a.SchedulingTeamId":
                            $returnQueryStr .= ")";
                            break;
                        case "spl.SortCode":
                        case "a.DutyName":
                        case "aj.JobName":
                            if (($optStr == '&') || ($optStr == ';')) {
                                $returnQueryStr .= "'  COLLATE SQL_Latin1_General_CP1_CS_AS";
                            } else if ($optStr == '!') {
                                $returnQueryStr .= "%'  COLLATE SQL_Latin1_General_CP1_CS_AS";
                            } else if (($optStr == '*') || ($optStr == '%')) {
                                $returnQueryStr .= "%'  COLLATE SQL_Latin1_General_CP1_CS_AS";
                            }
                            break;
                        case "a.StartTime":
                            break;
                        default:
                            if (($optStr == '&') || ($optStr == ';')) {
                                $returnQueryStr .= "'";
                            } else if ($optStr == '!') {
                                $returnQueryStr .= "%'";
                            } else if (($optStr == '*') || ($optStr == '%')) {
                                $returnQueryStr .= "%'";
                            }
                            break;
                    }
                }
            }
        }
        $returnQueryStr .= ')';
    } else {
        $returnQueryStr .= '(';
        switch ($columnName) {
            case "a.StartTime":
                if ($optStr == "") {
                    $timeval = $queryStr;
                } else {
                    if (!empty($optStr)) {
                        $explodeQueryStr = explode($optStr, (string) $queryStr);
                    }
                    $timeval = !empty($explodeQueryStr) ? $explodeQueryStr[0] : '-1';
                }
                if ($timeval != '-1') {
                    $startval = $queryStr - 900;
                    $endval = $queryStr + 900;
                    //$returnQueryStr .= "((StartTime <= ".$timeval ." AND EndTime>= ".$timeval." ) OR (StartTime = ".$startval .") OR ( EndTime = ".$endval."))";
                    $returnQueryStr .= "(( " . $timeval . " between StartTime AND case when starttime > EndTime then EndTime + 86400 else endtime end ) OR (StartTime = " . $startval . ") OR ( EndTime = " . $endval . "))";
                }
                break;
            default:
                break;
        }
        $returnQueryStr .= ')';
    }
    return  $returnQueryStr;
}

/**
 *  This function is used to compare two string and return the matched string between two strings
 *
 * @param $str1 This param contains the first string information
 * @param $str2 This param contains the second string information
 *
 * @return string
 */
function getMatchingString($str1, $str2)
{
    $len_1 = strlen((string) $str1);
    $matchStr = '';
    for ($i = 0; $i < $len_1; $i++) {
        for ($j = $len_1 - $i; $j > 0; $j--) {
            $sub = substr((string) $str1, $i, $j);
            if (str_contains((string) $str2, $sub) && strlen($sub) > strlen($matchStr)) {
                $matchStr = $sub;
                break;
            }
        }
    }
    return $matchStr;
}

/**
 *  This function is used to create final where condition for query for filters
 *
 * @param $params This param contains the data which need in the process of creating final where condition
 * @param $pdoObj This param contains the pdo object information
 *
 * @return string
 */
function getFinalConditionString($params, $pdoObj)
{
    $finalConditionStr = '';
    if ($params['optStr'] == '&') {
        $expStr = explode(',', (string) $params['condStr']);
        if (count($expStr) > 1) {
            $spCond = "";
            $spTeamCond = "";
            $spCond .= " " . $params['colName'] . " IN(";
            $cnt = 1;
            foreach ($expStr as $k => $v) {
                match ($params['colName']) {
                    "spsl.programmes_id", "aj.ProgrammeId", "a.dutyProgramId", "spl.TeamID", "a.SchedulingTeamId" => $spCond .= $v,
                    default => $spCond .= "'" . str_replace("'", "_REPSQLCOND_", $v) . "'",
                };
                if ($cnt < count($expStr)) {
                    $spCond .= ",";
                }
                $cnt++;
            }
            $spCond .= ")";
            if (($params['colName'] == 'spl.TeamID') || ($params['colName'] == 'a.SchedulingTeamId')) {
                $spTeamCond = $spCond;
                $spCond = "";
            }
            if ($params['screenName'] == 'ViewDaily') {
                if (($params['colName'] != 'spl.TeamID') && ($params['colName'] != 'a.SchedulingTeamId')) {
                    $spTeamCond = $params['intTeamID'];
                }
                $getCount = checkCountViewDaily($params['intWeek'], $params['intDay'], $params['intTeamID'], $params['rolepermission'], $params['startDate'], $params['endDate'], $params['applyOn'], $spCond, $spTeamCond, $pdoObj);

            }
            if ($params['screenName'] == 'ViewWeekly') {
                $getCount = checkCountViewWeekly($params['intSWeekNumber'], $params['intEWeekNumber'], $params['intTeamIDs'], $params['intSortOrder'], $spCond, $spTeamCond, $pdoObj, $params['screenName']);
            }
            if ($params['screenName'] == 'MultiWeek') {
                $getCount = checkCountViewWeekly($params['intSWeekNumber'], $params['intEWeekNumber'], $params['intTeamIDs'], $params['intSortOrder'], $spCond, $spTeamCond, $pdoObj, $params['screenName']);
            }
            if (($params['screenName'] == 'EditWeekly') || ($screenName == 'EditWeeklyRota')) {
                $getCount = checkCountEditWeekly($params['startDate'], $params['endDate'], $params['teamId'], ' AND ' . $spCond, $pdoObj);
            }
            if ($params['screenName'] == 'ProductionView') {
                $getCount = checkCountProductionView($params['startWeekNumber'], $params['endWeekNumber'], $params['teamId'], $params['prodStartDate'], $params['prodEndDate'], $spCond, $pdoObj);
            }
            if ($getCount >= count($expStr)) {
                $finalConditionStr = $params['filterQueryCond'];
            } else {
                $finalConditionStr = match ($params['colName']) {
                    "spsl.programmes_id", "aj.ProgrammeId", "a.dutyProgramId", "spl.TeamID", "a.SchedulingTeamId" => '(' . $params['colName'] . ' IN(0) AND 1=2)',
                    default => str_replace(" OR ", " AND ", $params['filterQueryCond']),
                };
            }
        } else {
            $finalConditionStr = $params['filterQueryCond'];
        }
    } else {
        $finalConditionStr = $params['filterQueryCond'];
    }
    return $finalConditionStr;
}

/**
 *  This function is used to check count on SP for View Daily Screen for comparing the result and input
 *
 * @param $intWeek This param contains the week number information
 * @param $intDay This param contains the week day information
 * @param $intTeamID This param contains the TeamID information
 * @param $rolepermission This param contains the rolepermission information for the user
 * @param $applyOn This param contains the apply on information (single/both)
 * @param $cntCond This param contains the count condition information
 * @param $cntTeamCond This param contains the TeamID information comes from the filters
 * @param $pdoObj This param contains the pdo object information
 *
 * @return integer
 */
function checkCountViewDaily($intWeek, $intDay, $intTeamID, $rolepermission, $startDate, $endDate, $applyOn, $cntCond, $cntTeamCond, $pdoObj)
{
    $query = "exec [dbo].[usp_get_ReadAllocationsDay_FilterCount] '" . $startDate . "','" . $endDate . "','" . str_replace("'", "''", $cntTeamCond) . "','" . $rolepermission . "','" . str_replace("'", "''", $cntCond) . "'";
    $stmt = $pdoObj->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['AllocationCount'];
}

/**
 *  This function is used to check count on SP for View Weekly Screen for comparing the result and input
 *
 * @param $intSWeekNumber This param contains the start week number information
 * @param $intEWeekNumber This param contains the end week day information
 * @param $intTeamIDs This param contains the TeamID information
 * @param $intSortOrder This param contains the sort order information
 * @param $cntCond This param contains the count condition information
 * @param $cntTeamCond This param contains the TeamID information comes from the filters
 * @param $pdoObj This param contains the pdo object information
 * @param $screenName This param contains the screen name information
 *
 * @return integer
 */
function checkCountViewWeekly($intSWeekNumber, $intEWeekNumber, $intTeamIDs, $intSortOrder, $cntCond, $cntTeamCond, $pdoObj, $screenName)
{
    $dteStartDate = datefromweek($intSWeekNumber);
    $dteEndDate = date('Y-m-d', strtotime($dteStartDate . ' + 6 days'));
    if ($screenName == 'MultiWeek') {
        $dteEndDate = datefromweek($intEWeekNumber);
        $dteEndDate = date('Y-m-d', strtotime($dteEndDate . ' - 1 days'));
    }
    $rolepermission = 1;
    if (empty($cntTeamCond)) {
        $cntTeamCond = $intTeamIDs;
    }
    if (empty($cntCond)) {
        $cntCond .= str_replace("spl.", " ", $cntTeamCond);
    }
    $query = "exec [dbo].[usp_get_ReadAllocationsDay_FilterCount] '" . $dteStartDate . "','" . $dteEndDate . "','" . str_replace("'", "''", $cntTeamCond) . "','0','" . str_replace("'", "''", $cntCond) . "'";
    $stmt = $pdoObj->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['AllocationCount'];
}

/**
 *  This function is used to check count on SP for Edit Weekly Screen for comparing the result and input
 *
 * @param $startDate This param contains the start date information
 * @param $endDate This param contains the end date day information
 * @param $teamId This param contains the TeamID information
 * @param $cntCond This param contains the count condition information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return integer
 */
function checkCountEditWeekly($startDate, $endDate, $teamId, $cntCond, $pdoObj)
{
    $query = "exec [dbo].[usp_get_ReadAllocationsEditWeekly_FilterCount] '" . $startDate . "','" . $endDate . "'," . $teamId . ",'" . str_replace("'", "''", $cntCond) . "'";
    $stmt = $pdoObj->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['AllocationCount'];
}

/**
 *  This function is used to check count on SP for Production View Screen for comparing the result and input
 *
 * @param $startDate This param contains the start date information
 * @param $endDate This param contains the end date day information
 * @param $teamId This param contains the TeamID information
 * @param $prodStartDate This param contains the Start Date information
 * @param $prodEndDate This param contains the End Date information
 * @param $cntCond This param contains the count condition information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return integer
 */
function checkCountProductionView($startWeek, $endWeek, $teamId, $prodStartDate, $prodEndDate, $cntCond, $pdoObj)
{
    $query = "exec [dbo].[usp_get_ReadAllocationsByDuties_FilterCount] '" . $teamId . "','" . $startWeek . "','" . $endWeek . "','" . $prodStartDate . "','" . $prodEndDate . "','" . str_replace("'", "''", $cntCond) . "'";
    $stmt = $pdoObj->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return count($result);
}

/**
 *  This function is used to check count on SP for Multi Week Screen for comparing the result and input
 *
 * @param $intSWeekNumber This param contains the start week number information
 * @param $intEWeekNumber This param contains the end week day information
 * @param $intTeamIDs This param contains the TeamID information
 * @param $cntCond This param contains the count condition information
 * @param $cntTeamCond This param contains the TeamID information comes from the filters
 * @param $pdoObj This param contains the pdo object information
 *
 * @return integer
 */
function checkCountMultiWeek($intSWeekNumber, $intEWeekNumber, $intTeamIDs, $cntCond, $cntTeamCond, $pdoObj)
{
    $query = "exec [dbo].[usp_fetch_multiweeklyAllocationAndRota_FilterCount] '" . $intTeamIDs . "','" . $intSWeekNumber . "','" . $intEWeekNumber . "','" . str_replace("'", "''", $cntCond) . "','" . str_replace("'", "''", $cntTeamCond) . "'";
    $stmt = $pdoObj->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return count($result);
}

/**
 *  This function is used to get week number details from Time Dimension
 *
 * @param $weekNumber This param contains the Week Number information
 * @param $iday This param contains the iDay information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return Array
 */
function getBBCWeekDetails($weekNumber = '', $iDay = 0, $pdoObj = null)
{
    $query = "SELECT TOP 1 CONVERT(DATETIME, dDateTime, 101) startDate FROM TimeDimension WHERE ixYearWeek=:weekNumber AND ixDayInWeek=:iDay";
    $stmt = $pdoObj->prepare($query);
    $stmt->bindValue(':weekNumber', $weekNumber, PDO::PARAM_STR);
    $stmt->bindValue(':iDay', $iDay, PDO::PARAM_STR);
    $stmt->execute();
    $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
    return $dataRow['startDate'];
}

/**
 *  This function is used to get setted filters for the Daily,Weekly,Edit Weekly and Multiweek Screens
 *
 * @param $screenName This param contains the screen name information
 * @param $teamId This param contains the Team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return string
 */
function getCurrentSetFilter($screenName, $teamId, $pdoObj)
{
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];

    $returnVal = 0;
    $strSQL = "SELECT DailyFilter,WeeklyFilter,MultiWeekFilter,EditWeeklyFilter FROM User_Web_Config WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
	$stmt = $pdoObj->prepare($strSQL);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($screenName == 'ViewDaily') {
        if (isset($row['DailyFilter']) && (!empty($row['DailyFilter']))) {
            return $row['DailyFilter'];
        } else {
            return $returnVal;
        }
    }
    if ($screenName == 'ViewWeekly') {
        if (isset($row['WeeklyFilter']) && (!empty($row['WeeklyFilter']))) {
            return $row['WeeklyFilter'];
        } else {
            return $returnVal;
        }
    }
    if ($screenName == 'MultiWeek') {
        if (isset($row['MultiWeekFilter']) && (!empty($row['MultiWeekFilter']))) {
            return $row['MultiWeekFilter'];
        } else {
            return $returnVal;
        }
    }
	if ($screenName == 'EditWeeklyRota') {
        if (isset($row['EditWeeklyFilter']) && (!empty($row['EditWeeklyFilter']))) {
            return $row['EditWeeklyFilter'];
        } else {
            return $returnVal;
        }
    }
}

/**
 *  This function is used to set filters for the Daily,Weekly,Edit Weekly and Multiweek Screens
 *
 * @param $params This param contains the data which need in the process of creating final where condition
 * @param $pdoObj This param contains the pdo object information
 *
 * @return string
 */
function setViewFilter($params, $pdoObj)
{
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $userId = $_COOKIE['editWeeklyUserId'] ?? $_SESSION['user']['UserID'];

    $strSQL = "SELECT id FROM User_Web_Config WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strSQL);
    $stmt->bindParam(':intTeamID', $params['teamId'], PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($row)) {
        $strSQL = "INSERT INTO User_Web_Config(Login, SchedulingTeamId, CreatedBy, CreatedDate) VALUES (:strLogin, :intTeamID, :userId, CONVERT(DATETIME, GETDATE(), 101))";
        $stmt = $pdoObj->prepare($strSQL);
        $stmt->bindParam(':intTeamID', $params['teamId'], PDO::PARAM_INT);
        $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    if ($params['screenName'] == 'ViewDaily') {
        if ($params['action'] == 'setfilterDaily') {
            return setDailyFilter($params['filterId'], $params['teamId'], $pdoObj);
        }
        if ($params['action'] == 'clearDailyfilter') {
            return clearDailyFilter($params['teamId'], $pdoObj);
        }
    }
    if ($params['screenName'] == 'ViewWeekly') {
        if ($params['action'] == 'setfilterWeekly') {
            return setWeeklyFilter($params['filterId'], $params['teamId'], $pdoObj);
        }
        if ($params['action'] == 'clearWeeklyfilter') {
            return clearWeeklyFilter($params['teamId'], $pdoObj);
        }
    }
    if ($params['screenName'] == 'MultiWeek') {
        if ($params['action'] == 'setfilterMultiWeek') {
            return setMultiWeekFilter($params['filterId'], $params['teamId'], $pdoObj);
        }
        if ($params['action'] == 'clearMultiWeekfilter') {
            return clearMultiWeekFilter($params['teamId'], $pdoObj);
        }
    }
    if ($params['screenName'] == 'EditWeekly') {
        if ($params['action'] == 'setfilterEditWeekly') {
            return setEditWeeklyFilter($params['filterId'], $params['teamId'], $pdoObj);
        }
        if ($params['action'] == 'clearEditWeeklyfilter') {
            return clearEditWeeklyFilterDB($params['teamId'], $pdoObj);
        }
    }
	
	if ($params['screenName'] == 'EditWeeklyRota'){
	    if ($params['action'] == 'setfilterEditWeeklyRota') {
            return setEditWeeklyRotaFilter($params['filterId'], $params['teamId'], $pdoObj);
        }
        if ($params['action'] == 'clearEditWeeklyRotafilter') {
            return clearEditWeeklyRotaFilterDB($params['teamId'], $pdoObj);
        }
    }

    if ($params['screenName'] == 'EditDailyScreen') {
        if ($params['action'] == 'Dailycheckbox') {
            return setEditDailyCheckbox($params['checkboxval'], $params['teamId'], $pdoObj);
        }
        if ($params['action'] == 'Checkstartandend') {
            return setStartAndEnd($params['checkstartendshift'], $params['teamId'], $pdoObj);
        }
    }

}

/**
 *  This function is used to set filter for the Daily Screen
 *
 * @param $filterId This param contains the filter ID information
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function setDailyFilter($filterId, $teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET DailyFilter = :intDailyFilter WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':intDailyFilter', $filterId, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to clear filter for the Daily Screen
 *
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function clearDailyFilter($teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET DailyFilter = NULL WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to set filter for the Weekly Screen
 *
 * @param $filterId This param contains the filter ID information
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function setWeeklyFilter($filterId, $teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET WeeklyFilter = :intWeeklyFilter WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':intWeeklyFilter', $filterId, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to clear filter for the Weekly Screen
 *
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function clearWeeklyFilter($teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET WeeklyFilter = NULL WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to set filter for the Multi Week Screen
 *
 * @param $filterId This param contains the filter ID information
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function setMultiWeekFilter($filterId, $teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET MultiWeekFilter = :intMultiWeekFilter WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':intMultiWeekFilter', $filterId, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to clear filter for the Multi Week Screen
 *
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function clearMultiWeekFilter($teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET MultiWeekFilter = NULL WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to set filter for the Edit Weekly Screen
 *
 * @param $filterId This param contains the filter ID information
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function setEditWeeklyFilter($filterId, $teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET EditWeeklyFilter = :intEditWeeklyFilter WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':intEditWeeklyFilter', $filterId, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}
/**
 *  This function is used to set filter for the Edit Weekly Rota Screen
 *
 * @param $filterId This param contains the filter ID information
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function setEditWeeklyRotaFilter($filterId, $teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET EditWeeklyFilter = :intEditWeeklyRotaFilter WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':intEditWeeklyRotaFilter', $filterId, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to clear filter for the Edit Weekly Screen
 *
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function clearEditWeeklyFilterDB($teamId, $pdoObj)
{
    if(!empty($_SESSION['filterdata'])){
        $_SESSION['filterdata'] = [];
    }
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET EditWeeklyFilter = NULL WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}


/**
 *  This function is used to clear filter for the Edit Weekly Rota Screen
 *
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function clearEditWeeklyRotaFilterDB($teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET EditWeeklyFilter = NULL WHERE Login = :strLogin AND SchedulingTeamId = :intTeamID";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    $stmt->bindParam(':intTeamID', $teamId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to set checkbox value on Edit Daily Screen
 *
 * @param $teamId This param contains the team ID information
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function setEditDailyCheckbox($checkboxval, $teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET Dailyunallocated = :checkboxval WHERE Login = :strLogin ";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':checkboxval', $checkboxval, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to set Show only Shifts with Start and End Time  value on Edit Daily Screen
 *
 * @param $teamId This param contains the team ID information
 * @param $checkstartendshift This param contains the checkbox value
 * @param $pdoObj This param contains the pdo object information
 *
 * @return boolean
 */
function setStartAndEnd($checkstartendshift, $teamId, $pdoObj)
{
    $status = false;
    $strLogin = $_COOKIE['editWeeklyUserNetLogin'] ?? $_SESSION['user']['user'];
    $strQuery = "UPDATE User_Web_Config SET Checkstartendshift = :checkstartendshift WHERE Login = :strLogin ";
    $stmt = $pdoObj->prepare($strQuery);
    $stmt->bindParam(':checkstartendshift', $checkstartendshift, PDO::PARAM_INT);
    $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
    if ($stmt->execute()) {
        $status = true;
    }
    return $status;
}

/**
 *  This function is used to replace all white spaces
 *
 * @param $str This param contains the input string information
 * @param $repStr This param contains the replace with string information
 *
 * @return string
 */
function replaceWhiteSpaces($str = '', $repStr = '')
{
    $returnStr = '';
    if ($str != '' && $repStr != '') {
        $withoutSpaceStr = [];
        $splitStr = explode(",", (string) $str);
        if (!empty($splitStr)) {
            foreach ($splitStr as $strVal) {
                $withoutSpaceStr[] = trim($strVal);
            }
            $returnStr = implode(",", $withoutSpaceStr);
        }
    }
    return $returnStr;
}

/**
 *  This function is used to replace comma separated string by the operator passed as a second argument without trimming white spaces
 *
 * @param $str This param contains the original string information
 * @param $selectedOperator This param contains the operator information which we replace in original string
 * @param $replaceWith This param contains the replace with string in the passing string
 *
 * @return string
 */
function replaceCommaBySelectedOperatorWithoutTrim($str, $selectedOperator, $replaceWith)
{
    $returnFilterStr = '';
    if ((str_contains((string) $str, (string) $replaceWith)) && $str != '') {
        $expFilterStr = explode(',', (string) $str);
        foreach ($expFilterStr as $expFilterStrVal) {
            $updFilterStr[] = $expFilterStrVal;
        }
        $impFilterStr = implode(',', $updFilterStr);
        $returnFilterStr = str_replace($replaceWith, $selectedOperator, $impFilterStr);
    } else {
        $returnFilterStr = $str . $selectedOperator;
    }
    return $returnFilterStr;
}

/**
 *  This function is used to not trim white spaces
 *
 * @param $str This param contains the input string information
 * @param $repStr This param contains the replace with string information
 *
 * @return string
 */
function noReplaceWhiteSpaces($str = '', $repStr = '')
{
    $returnStr = '';
    if ($str != '' && $repStr != '') {
        $withoutSpaceStr = [];
        $splitStr = explode(",", (string) $str);
        if (!empty($splitStr)) {
            foreach ($splitStr as $strVal) {
                $withoutSpaceStr[] = $strVal;
            }
            $returnStr = implode(",", $withoutSpaceStr);
        }
    }
    return $returnStr;
}

/**
 *  This function is used to set NULL for filters after delete
 *
 * @param $filterId This param contains the filterid which deleted

 * @return string
 */
function UpdateFiltersAfterDelete($filterId){
    $pdo = OpenDBLinkA7();
	
    /* For daily Filter */
	$strQuery = "SELECT DailyFilter  FROM User_Web_Config WHERE DailyFilter='".$filterId."'" ;
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $resultdaily = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty($resultdaily)){
		$DailyFilter = $resultdaily['DailyFilter'];
        $strUpdQuery = "UPDATE User_Web_Config SET DailyFilter=NULL WHERE DailyFilter='".$filterId."'";
		$stmtUpd = $pdo->prepare($strUpdQuery);
		$stmtUpd->execute();
	}
	
	/* For Weekly Filter */
	$strQuery = "SELECT WeeklyFilter  FROM User_Web_Config WHERE WeeklyFilter='".$filterId."'" ;
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $resultweekly = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty($resultweekly)){
		$WeeklyFilter = $resultweekly['WeeklyFilter'];
	    $strUpdQuery = "UPDATE User_Web_Config SET WeeklyFilter=NULL WHERE WeeklyFilter='".$filterId."'";
		$stmtUpd = $pdo->prepare($strUpdQuery);
		$stmtUpd->execute();
	}
	
	/* For MultiWeekly Filter */
	$strQuery = "SELECT MultiWeekFilter  FROM User_Web_Config WHERE MultiWeekFilter='".$filterId."' " ;
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $resultmultiweek = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty($resultmultiweek)){
		$MultiWeekFilter = $resultmultiweek['MultiWeekFilter'];
		$strUpdQuery = "UPDATE User_Web_Config SET MultiWeekFilter=NULL WHERE MultiWeekFilter='".$filterId."'";
		$stmtUpd = $pdo->prepare($strUpdQuery);
		$stmtUpd->execute();
	}
	
	/* For Edit Weekly Filter */
	$strQuery = "SELECT EditWeeklyFilter  FROM User_Web_Config WHERE EditWeeklyFilter='".$filterId."'" ;
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $resulteditweekly = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty($resulteditweekly) ){
		$strUpdQuery = "UPDATE User_Web_Config SET EditWeeklyFilter=NULL WHERE EditWeeklyFilter='".$filterId."'";
		$stmtUpd = $pdo->prepare($strUpdQuery);
		$stmtUpd->execute();
	}	
	
	/* For Edit Weekly Rota Filter */
	$strQuery = "SELECT EditWeeklyFilter  FROM User_Web_Config WHERE EditWeeklyFilter='".$filterId."'" ;
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $resulteditrotaweekly = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty($resulteditrotaweekly) ){
		$strUpdQuery = "UPDATE User_Web_Config SET EditWeeklyFilter=NULL WHERE EditWeeklyFilter='".$filterId."'";
		$stmtUpd = $pdo->prepare($strUpdQuery);
		$stmtUpd->execute();
	}	
}
