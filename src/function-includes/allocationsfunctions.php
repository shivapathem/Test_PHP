<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'DBHelper.php';
include_once 'helpers.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/page-includes/admin/process/classSchedulingTeam.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/page-includes/allocations/weekly/service/AllocationViewService.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/common/classCommonDBFunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/components/filters/filter-process.php';
include_once 'genericfunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/page-includes/users/process/classUserSetup.php';

function ReadAllocationsIndividual($intStartWeek = 0, $intEndWeek = 0, $schedulingPersonId = '', $arrDepDefaults = [], $strUserLogin = 0, $intLeaveCaller = 0, $isShiftleader = 0, $email = '')
{
    global $intViewYears;
    $arrTextColours = GetShiftTextColours();
    $pNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $pdo = OpenDBLinkA7();
    try {
        $strQuery = "exec [dbo].[usp_fetch_monthlyAllocationAndRota] $intStartWeek, $intEndWeek, $schedulingPersonId, $isShiftleader, '" . $pNetLogin . "'";
        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }

    if (count($result) > 0) {
        foreach ($result as $row) {
            $intBreakTime = $row['dutyBreakTime'];
            $intCurrDep = $row['SchedulingTeamId'];
            $strCurrentWeek = $row['WeekNumber'];
            $intCurrentDay = $row['DOTW'];
            $intMaskDays = $row['maskAfter'];
            $intMaskType = $row['maskType'];
            $intColourWeek = $row['colourWeek'];

            $intMaskAfterUnixDate = strtotime("+$intMaskDays Days");
            $uThisDate = strtotime(datefromweek($strCurrentWeek, $intCurrentDay));

            $strLeaveHasMealFrom = isset($row['defaultRotaStartDate']) ? date('Y-m-d', strtotime($row['defaultRotaStartDate'])) : '';

            $strDutyname = $row['DutyName'];
            if ($uThisDate > $intMaskAfterUnixDate) {
                $arrAllocations['Weeks'][$row['WeekNumber']][$row['DOTW']]['Masked'] = 1;
                $arrAllocations['Weeks'][$row['WeekNumber']][$row['DOTW']]['MaskType'] = $intMaskType;
            }

            if ($row["display_priority"] == 2) {
                $rowIsRota = 1;
                $arrAllocations['Weeks'][$strCurrentWeek]['WeekDisplayPriority'] = 2;
            } else {
                $rowIsRota = 0;
                $arrAllocations['Weeks'][$strCurrentWeek]['WeekDisplayPriority'] = 1;
            }

            if (!is_null($row['dutyBreakTime']) || !empty($row['dutyBreakTime'])) {
                $arrAllocations['Weeks'][$strCurrentWeek]['HasBreaks'] = 1;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek]['HasBreaks'] = 0;
            }

            $arrAllocations['Weeks'][$strCurrentWeek]['TotalDuration'] = (int) $row['TotalDuration'];
            $arrAllocations['Weeks'][$strCurrentWeek]['TDExBreak'] = (int) $row['TDExBreak'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['SchedulingTeamId'] = $row['SchedulingTeamId'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['SchedulingTeamName'] = $row['schedulingTeamName'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['ColourWeek'] = $row['colourWeek'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['Duty'] = $strDutyname;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['DutyId'] = (int)$row['ID'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["IsRota"] = $rowIsRota;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["RotaLeave"] = $row['ROTA'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["display_priority"] = $row["display_priority"];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["hiddenDays"] = $row['isHiddenDays'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['ActingFlag'] = $row['ActingFlag'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['StartTimeSec'] = $row['StartTime'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['EndTimeSec'] = $row['EndTime'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['AllocationsDutyID'] = $row['AllocationsDutyID'];
            if ((!empty($row['DutyName']) && strtolower((string) $row['DutyName']) != 'sick') && (!empty($row['DutyName']) && strtolower((string) $row['DutyName']) != 'leave')) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["AllowApplyOvertime"] = $row["allowOvertimeRequests"];
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["AllowApplyOvertime"] = 0;
            }

            $row["Duration"] = $row["Duration"] != null ? $row["Duration"] : 0;
            $duration = (int) $row["Duration"];
            $duration = number_format((float) ($row["Duration"] / 3600), 2, '.', '');
            $isworking = 1;
            if (isset($arrTextColours)) {
                $isworking = GetIsNotWorking($strDutyname, $arrTextColours);
            }

            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['isworking'] = $isworking;
            //checking starttime is not null
            if (($row["StartTime"] > 0 || $row["EndTime"] > 0) && (isset($row['DutyName']) && strtoupper($row['DutyName']) != 'U')) {
                $starttime = gmdate("H:i", intval($row["StartTime"]) + 1);
                $endtime = gmdate("H:i", intval($row["EndTime"]) + 1);

                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["StartTime"] = $starttime;
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["EndTime"] = $endtime;

                if (is_null($row["active"])) {
                    $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignIn"] = 0;
                } else {
                    if ($row['StartTime'] == $row["SignInStartTime"] && $row['EndTime'] == $row["SignInEndTime"]) {
                        $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignIn"] = $row["active"];
                    } else {
                        $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignIn"] = 2;
                    }
                }

                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignInID"] = $row["SignInID"];
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignInDays"] = $row["signInDays"];
            }
            // Any financial reward?   MarkedOvertime ActingGrade
            if ($row["MarkedOvertime"] == 0 && $row["ActingGrade"] == 0 && $row["MarkedPTExtraDay"] == 0 && $row["MarkedCompLeave"] == 0) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["financial"] = 0;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["financial"] = 1;
            }
            // set flag for markedovertime or not
            if ($row["ManualOThours"] > 0) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["ManualOThours"] = 1;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["ManualOThours"] = 0;
            }

            if (is_null($row["inBuilding"])) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["InBuilding"] = 0;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["InBuilding"] = $row["inBuilding"];
            }

            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["Duration"] = $duration;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["BreakTime"] = $intBreakTime;

            if (strtotime($strLeaveHasMealFrom) < $uThisDate && $strDutyname !== null && stripos($strDutyname, 'leave') !== false) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['DurationLessMeal'] = $duration + $intBreakTime;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['DurationLessMeal'] = $duration;
            }

            $intConfirmedDays = $arrDepDefaults['ConfirmedDays'] ?? '';
            $thisdate = datefromweek($row['WeekNumber'], $row['DOTW']);

            if ($row["display_priority"] == 2) {
                $CellClass = SetRotaDutyClass($isworking);
            } else {
                $CellClass = SetDutyClass($thisdate, $intMaskDays, $intMaskType, $isworking);
            }

            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['ShowRotaAsWell'] = ShowRotaAsWell($thisdate, $intConfirmedDays);
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['CellClass'] = $CellClass;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["Duration"] = $duration;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["BreakTime"] = $intBreakTime;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["DutyComments"] = $row['DutyComments'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["PersonComments"] = $row['PersonComments'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["pdlStartTime"] = $row['LeaveStartTime'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["pdlEndTime"] = $row['LeaveEndTime'];
            if (!empty($row['DutyName'])) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['BackColour'] = '#' . $row['BackColour'];
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['allocateTextColour'] = '#' . $row['FontColour'];
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['BackColour'] = '#EFEFEF';
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['allocateTextColour'] = '#000000';
            }
        }
    }

    if (!empty($arrAllocations)) {
        return MakeMonthlyRotaAllocations($intStartWeek, $intEndWeek, $schedulingPersonId, $arrAllocations, $email);
    } else {
        return [];
    }
}

function MakeMonthlyRotaAllocations(int $CurrentWeekNo = 0, int $intEndWeek = 0, $schedulingPersonId = 0, $arrRota = '', $email = '')
{
    $arrAllocations = [];
    $commonObj = new classCommonDBFunctions();
    if ($email != 'send') {
        foreach ($arrRota as $arv) {
            // Now read this into the dates.....
            $intCurWeek = $CurrentWeekNo;
            while ($intCurWeek <= $intEndWeek) {
                for ($i = 0; $i <= 6; $i++) {
                    $arrAllocations["Weeks"][$intCurWeek]["TotalDuration"] = $arv[$intCurWeek]["TotalDuration"] ?? '';
                    $arrAllocations["Weeks"][$intCurWeek]["TDExBreak"] = $arv[$intCurWeek]["TDExBreak"] ?? '';
                    $arrAllocations["Weeks"][$intCurWeek]["HasBreaks"] = $arv[$intCurWeek]["HasBreaks"] ?? '';
                    $arrAllocations["Weeks"][$intCurWeek][$i]["SchedulingTeamName"] = $arv[$intCurWeek][$i]["SchedulingTeamName"] ?? '';
                    $arrAllocations["Weeks"][$intCurWeek][$i]["ActingFlag"] = $arv[$intCurWeek][$i]["ActingFlag"] ?? 0;

                    if (isset($arv[$intCurWeek][$i])) {
                        $arrAllocations["Weeks"][$intCurWeek][$i] = $arv[$intCurWeek][$i];
                    } else {
                        if ((isset($arv[$intCurWeek]["WeekDisplayPriority"])) && ($arv[$intCurWeek]["WeekDisplayPriority"] == 1)) {
                            $arrAllocations["Weeks"][$intCurWeek][$i]["Duty"] = '';
                            $arrAllocations["Weeks"][$intCurWeek][$i]['BackColour'] = "#EFEFEF";
                            $arrAllocations["Weeks"][$intCurWeek][$i]['allocateTextColour'] = "#330066";
                            $arrAllocations["Weeks"][$intCurWeek][$i]['IsRota'] = 0;
                        } else {
                            $arrAllocations["Weeks"][$intCurWeek][$i]["Duty"] = '-';
                            $arrAllocations["Weeks"][$intCurWeek][$i]['BackColour'] = "#D8D8FF";
                            $arrAllocations["Weeks"][$intCurWeek][$i]['allocateTextColour'] = "#57575c";
                            $arrAllocations["Weeks"][$intCurWeek][$i]['IsRota'] = 1;
                        }

                        if (isset($arv[$intCurWeek]["WeekDisplayPriority"])) {
                            $arrAllocations["Weeks"][$intCurWeek]["WeekDisplayPriority"] = $arv[$intCurWeek]["WeekDisplayPriority"];
                        }

                        $arrAllocations["Weeks"][$intCurWeek][$i]['CellClass'] = 'DutyCellNotWorking';
                        $arrAllocations["Weeks"][$intCurWeek][$i]['CellClass'] = 'DutyCellNotWorking';
                    }
                }
                $intCurWeek = addweeks($intCurWeek, 1);
            } //while Close
        }
    } else {
        foreach ($arrRota as $arv) {
            // Now read this into the dates.....
            $intCurWeek = $CurrentWeekNo;
            while ($intCurWeek <= $intEndWeek) {
                for ($i = 0; $i <= 6; $i++) {
                    $arrAllocations["Weeks"][$intCurWeek][$i]["SchedulingTeamName"] = $arv[$intCurWeek][$i]["SchedulingTeamName"] ?? '';

                    if (isset($arv[$intCurWeek][$i])) {
                        $arrAllocations["Weeks"][$intCurWeek][$i] = $arv[$intCurWeek][$i];
                    } else {
                        if (isset($arv[$intCurWeek]["WeekDisplayPriority"]) && $arv[$intCurWeek]["WeekDisplayPriority"] == 1) {
                            $arrAllocations["Weeks"][$intCurWeek][$i]["Duty"] = 'U';
                            $arrAllocations["Weeks"][$intCurWeek][$i]["Duration"] = '0.00';
                        } else {
                            $arrAllocations["Weeks"][$intCurWeek][$i]["Duty"] = 'U';
                            $arrAllocations["Weeks"][$intCurWeek][$i]["Duration"] = '0.00';
                        }
                    }
                }
                $intCurWeek = addweeks($intCurWeek, 1);
            } //while Close
        }
    }

    return $arrAllocations;
}

function GetTeamDefaults($intUserId = 0, $schedulingteamid = 0)
{
    $arrTeam = [];
    $schedulingObj = new classSchedulingTeam();
    $resultschedteam = json_decode($schedulingObj->getSchedulingTeamDetails($intUserId, $schedulingteamid, ''), true);
    if (!empty($resultschedteam)) {
        foreach ($resultschedteam as $row) {
            $intCurrID = $row["schedulingTeamId"];
            $arrTeam[$intCurrID]["Description"] = $row["schedulingTeamName"];
            $arrTeam[$intCurrID]["MaskAfter"] = $row["maskAfter"];
            $arrTeam[$intCurrID]["MaskType"] = $row["maskType"];
            $arrTeam[$intCurrID]["ConfirmedDays"] = $row["ConfirmedDays"] ?? '';
            $arrTeam[$intCurrID]["DailyEditPeriod"] = $row["restrictedEditing"];
            $arrTeam[$intCurrID]["DailyEditStart"] = $row["editingStart"];
            $arrTeam[$intCurrID]["DailyEditEnd"] = $row["editingEnd"];
            $arrTeam[$intCurrID]["DailyAutoWeekend"] = $row["WeekendOnly"];
            $arrTeam[$intCurrID]["SignInDays"] = $row["signInDays"];
            $arrTeam[$intCurrID]["AllowInBuilding"] = $row["allowInBuilding"];
            $arrTeam[$intCurrID]["ColourWeek"] = $row["colourWeek"];
            $arrTeam[$intCurrID]["AllowApplyOvertime"] = $row["allowOvertimeRequests"];
            $arrTeam[$intCurrID]["LocksEnd"] = $row["locksEnd"];
            $arrTeam[$intCurrID]["HideDailyView"] = $row["DailyViewMasking"];
            $arrTeam[$intCurrID]["LocksRollWeek"] = $row["locksWeekataTime"];
            $arrTeam[$intCurrID]["WeeksViewAllowed"] = $row["restrictedEditing"];
            $arrTeam[$intCurrID]["AutoImport"] = $row["NoofAutoAutoimportWeeks"];
            $arrTeam[$intCurrID]["HasDutiesView"] = $row["showProductionView"];
            $arrTeam[$intCurrID]["HasJobsInWeeklyView"] = $row["ShowJobsInWeeklyView"];
            $arrTeam[$intCurrID]["AutoLockToday"] = $row["autoLockTodayTimer"];
            $arrTeam[$intCurrID]["LeaveHasMealDate"] = isset($row['defaultRotaStartDate']) ? date('Y-m-d', strtotime((string) $row['defaultRotaStartDate'])) : '';
            $arrTeam[$intCurrID]["hasGridChecks"] = $row["hasGridChecks"];
            $arrTeam[$intCurrID]["FreelancerMaskingDays"] = $row["freelancerMaskingDays"];
            $arrTeam[$intCurrID]["FreelancerMasking"] = $row["freelancerMasking"];
            $arrTeam[$intCurrID]["DailyViewMaskingDays"] = ($row["dailyViewMaskingDays"] == 0) ? '999' : $row["dailyViewMaskingDays"];
            $arrTeam[$intCurrID]["numberofDaysAllowedEditing"] = $row["numberofDaysAllowedEditing"];
            $arrTeam[$intCurrID]["showLock"] = $row["restrictedEditing"] ?? 0;
            $arrTeam[$intCurrID]["IsRestrictCopyDuty"] = $row["IsRestrictCopyDuty"] ?? 0;
            $arrTeam[$intCurrID]["IsShowEditYearly"] = $row["IsShowEditYearly"] ?? 0;
        }
    }

    return $arrTeam;
}

function GetDeptDefaults($intDeptID = 0)
{
    $db = OpenDatabase();

    $strQuery = "SELECT        ID, FullName, MaskAfter, MaskType, ConfirmedDays, DailyEditStart, DailyEditEnd, DailyEditPeriod, DailyAutoWeekend,
                             SignInDays, AllowInBuilding, ColourWeek, AllowApplyOvertime, LocksEnd, HideDailyView, LocksRollWeek,
                             isnull(WeeksView, 0) as WeeksView, isnull(AutoImport, 0) as AutoImport,  isnull(HasDutiesView, 0) as HasDutiesView,
                             isnull(AutoLockToday, 0) as AutoLockToday, LeaveHasMealDate
               FROM          Departments";
    if ($intDeptID != 0) {
        $strQuery .= " WHERE        (ID = $intDeptID)";
    }
    //echo $strQuery;
    $rsDepts = sqlsrv_query($db, $strQuery);
    while ($row = sqlsrv_fetch_array($rsDepts)) {
        $intCurrID = $row["ID"];
        $arrDept[$intCurrID]["Description"] = $row["FullName"];
        $arrDept[$intCurrID]["MaskAfter"] = $row["MaskAfter"];
        $arrDept[$intCurrID]["MaskType"] = $row["MaskType"];
        $arrDept[$intCurrID]["ConfirmedDays"] = $row["ConfirmedDays"];
        $arrDept[$intCurrID]["DailyEditPeriod"] = $row["DailyEditPeriod"];
        $arrDept[$intCurrID]["DailyEditStart"] = $row["DailyEditStart"];
        $arrDept[$intCurrID]["DailyEditEnd"] = $row["DailyEditEnd"];
        $arrDept[$intCurrID]["DailyAutoWeekend"] = $row["DailyAutoWeekend"];
        $arrDept[$intCurrID]["SignInDays"] = $row["SignInDays"];
        $arrDept[$intCurrID]["AllowInBuilding"] = $row["AllowInBuilding"];
        $arrDept[$intCurrID]["ColourWeek"] = $row["ColourWeek"];
        $arrDept[$intCurrID]["AllowApplyOvertime"] = $row["AllowApplyOvertime"];
        $arrDept[$intCurrID]["LocksEnd"] = $row["LocksEnd"];
        $arrDept[$intCurrID]["HideDailyView"] = $row["HideDailyView"];
        $arrDept[$intCurrID]["LocksRollWeek"] = $row["LocksRollWeek"];
        $arrDept[$intCurrID]["WeeksViewAllowed"] = $row["WeeksView"];
        $arrDept[$intCurrID]["AutoImport"] = $row["AutoImport"];
        $arrDept[$intCurrID]["HasDutiesView"] = $row["HasDutiesView"];
        $arrDept[$intCurrID]["AutoLockToday"] = $row["AutoLockToday"];
        $arrDept[$intCurrID]["LeaveHasMealDate"] = $row['LeaveHasMealDate']->format('Y-m-d');
    }
    if (isset($arrDept)) {
        return ($arrDept);
    }
}

/**
 *   This function is used to return the Duty Name based on the masking type.
 *
 * @param $strDutyname This param contains the Duty Name information
 * @param $intMaskType This param contains the Mask Type information
 *        0 - Hide All Letters
 *        1 - Show First Letter Only
 *        2 - Show All Letter
 *
 * @return string Returns the Duty Name as per the masking type
 */

function filterDuty($strDutyname  = '', $intMaskType = 0)
{

    $strDutyname = trim((string) $strDutyname);

    switch ($intMaskType) {
        case 0:
            $returnDuty = '';
            break;
        case 1:
            if (is_null($strDutyname)) {
                $returnDuty = "Z";
            } else {
                if (is_numeric(substr($strDutyname, 2, 1)) || substr($strDutyname, 2, 1) == " ") {
                    $returnDuty = substr($strDutyname, 0, 1);
                } else {
                    $returnDuty = $strDutyname;
                }
            }
            break;
        case 2:
            $returnDuty = $strDutyname;
            break;
    }
    return $returnDuty;
}

function GetDutyFiltersByDepartment($intTeamID = 0, $intOption = 0)
{
    //change to SP
    $pdo = OpenDBLinkA7();
    if ($intOption == 0) {
        $strQuery = "SELECT      Description, DutyFilter, SortCodeFilter, JobStarts, JobContains,
                            Filter, SortCode, SortOrder, isnull(AndMatch, 0) as AndMatch, FilterType,
                            ISNULL(BaseCodes, '') AS BaseCodes, ISNULL(ExtraDepartments, '') AS ExtraDepartments, id,isPublic
                            FROM        AutoPagesFilters
                            WHERE       (SchedulingTeamId = $intTeamID)
                            ORDER BY    Description";
    } else {
        if ($intOption == 1) {
            $strQuery = "SELECT Description,
                                        ISNULL(DutyFilter, N'') AS DutyFilter, SortCodeFilter, JobStarts, JobContains,
                                        Filter, SortCode, SortOrder, ISNULL(AndMatch, 0) AS AndMatch, FilterType,
                                        ISNULL(BaseCodes, '') AS BaseCodes, ISNULL(ExtraDepartments, '') AS ExtraDepartments, id,isPublic
                                    FROM AutoPagesFilters (NOLOCK)
                                    WHERE (isPublic = 1) AND (SchedulingTeamId = $intTeamID) AND (ISNULL(DutyFilter, N'') <> N'')
                                        AND (DutyFilter not in ('*','&','!',';','','NULL') OR AdditionalTeams not in ('*','&','!',';','','NULL') OR DutyLabel not in ('*','&','!',';','','NULL'))
                                    ORDER BY Description";
        } else {
            $strQuery = "SELECT      Description, DutyFilter, SortCodeFilter, JobStarts, JobContains,
                            Filter, SortCode, SortOrder, isnull(AndMatch, 0) as AndMatch, FilterType,
                            ISNULL(BaseCodes, '') AS BaseCodes, ISNULL(ExtraDepartments, '') AS ExtraDepartments, id,isPublic
                            FROM        AutoPagesFilters
                            WHERE       SchedulingTeamId = $intTeamID AND FilterType IN(0,1)
                            ORDER BY    Description";
        }
    }
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $arrFilters = [];
    foreach ($result as $row) {
        $arrFilters[$row['FilterType']][$row['id']]['Description'] = $row['Description'];
        $arrFilters[$row['FilterType']][$row['id']]['JobFilter'] = $row['Filter'];
        $arrFilters[$row['FilterType']][$row['id']]['JobContains'] = $row['JobContains'];
        $arrFilters[$row['FilterType']][$row['id']]['JobStarts'] = $row['JobStarts'];
        $arrFilters[$row['FilterType']][$row['id']]['DutyFilter'] = $row['DutyFilter'];
        $arrFilters[$row['FilterType']][$row['id']]['SortCodeFilter'] = $row['SortCodeFilter'];
        $arrFilters[$row['FilterType']][$row['id']]['ShowSortCode'] = $row['SortCode'];
        $arrFilters[$row['FilterType']][$row['id']]['SortOrder'] = $row['SortOrder'];
        $arrFilters[$row['FilterType']][$row['id']]['AndMatch'] = $row['AndMatch'];
        $arrFilters[$row['FilterType']][$row['id']]['FilterType'] = $row['FilterType'];
        $arrFilters[$row['FilterType']][$row['id']]['isPublic'] = $row['isPublic'];
        if ($row['BaseCodes'] != '') {
            $arrBaseCodes = explode(',', (string) $row['BaseCodes']);
            $arrFilters[$row['FilterType']][$row['id']]['BaseCodes'] = $arrBaseCodes;
        }
        if ($row['ExtraDepartments'] != '') {
            $arrExtraDepartments = explode(',', (string) $row['ExtraDepartments']);
            $arrFilters[$row['FilterType']][$row['id']]['ExtraDepartments'] = $arrExtraDepartments;
        }
    }

    return ($arrFilters);
}
/**
 * Description : get Product Group information
 * @param SchedulingTeamId
 * return [] on sucess and false on error
 */

function GetProdViewGroups($intTeamID = 0)
{
    $pdo = OpenDBLinkA7();
    try {
        $strQuery = "SELECT id, Description, Filter, SortOrder FROM  ProdViewGroups WHERE ([SchedulingTeamId] = ?) ORDER BY SortOrder";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindValue(1, $intTeamID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($result)) {
            foreach ($result as $row) {
                $arrViews[$row['id']]['Description'] = $row['Description'];
                $arrViews[$row['id']]['Filter'] = $row['Filter'];
                $arrViews[$row['id']]['SortOrder'] = $row['SortOrder'];
            }
        }
        if (isset($arrViews)) {
            return ($arrViews);
        } else {
            return false;
        }
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    return false;
}
/**
 * Description : GetShiftTextColours By SEtup
 * return []
 */

function GetShiftTextColours()
{
    $pdo = OpenDBLinkA7();
    $arrColours = [];
    try {
        $strQuery = "SELECT Description, TextColour, ID, DivisionID FROM  AllocationsTextColours ORDER BY   Description";
        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $arrColours[$row['ID']]['Description'] = $row['Description'];
            $arrColours[$row['ID']]['TextColour'] = $row['TextColour'];
            $arrColours[$row['ID']]['DivisionID'] = $row['DivisionID'];
        }
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }

    return $arrColours;
}

function GetIsNotWorking($strDutyname = '', $arrNotWorking = [])
{
    // $arrNotWorking contains a list of duties that are to be marked as Not working
    $intIsWorking = 1;
    foreach ($arrNotWorking as $intID => $arrValue) {
        if ($strDutyname !== null && strtoupper(substr($strDutyname, 0, strlen((string) $arrValue['Description']))) == strtoupper((string) $arrValue['Description'])) {
            $intIsWorking = 0;
            break;
        }
    }
    return $intIsWorking;
}

function ReadRotaPattern($ScheduledPersonID = 0)
{

    $pdo = OpenDBLinkA7();
    // Get the Rota.....
    $query = 'exec [dbo].[usp_GET_RotasByPerson] ?, ?';
    $stmtR = $pdo->prepare($query);
    $stmtR->bindValue(1, $ScheduledPersonID, PDO::PARAM_INT);
    $stmtR->bindValue(2, date('d/m/Y', strtotime("first saturday of " . date('Y-m-d'))), PDO::PARAM_STR);
    $stmtR->execute();
    $resultR = $stmtR->fetch(PDO::FETCH_ASSOC);
    $rotaId = $resultR['RotaID'] ?? '';
    $strQuery = "exec [dbo].[usp_GET_RotaDuties] ?,0,0,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindValue(1, $rotaId, PDO::PARAM_INT);
    $stmt->bindValue(2, date('d/m/Y'), PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $arrRota = [];
    $totalWorkingRecord = 0;
    $totalWorkingRecord1 = 0;
    foreach ($result as $row) {
        switch ($row["DayOfRota"]) {
            case 0:
                $strDay = 'Saturday';
                break;
            case 1:
                $strDay = 'Sunday';
                break;
            case 2:
                $strDay = 'Monday';
                break;
            case 3:
                $strDay = 'Tuesday';
                break;
            case 4:
                $strDay = 'Wednesday';
                break;
            case 5:
                $strDay = 'Thursday';
                break;
            case 6:
                $strDay = 'Friday';
                break;
        }
        if (empty($row["DutyName"])) {
            continue;
        }
        $arrRota[$row["RotaWeek"]][$strDay]['DutyName'][] = substr((string) $row["DutyName"], 0, 20);
        $arrRota[$row["RotaWeek"]][$strDay]['Duration'][] = (($arrRota[$row["RotaWeek"]][$strDay]['DutyName']) && (!in_array($row["DutyName"], ['-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*']))) ? $row["DurationExc"] : '';

        $tempShiftType = (($row["StartTime"] >= 68400) || ($row["StartTime"] < 10800)) ? 'N' : 'D';
        $tempShiftType1 = ($row["IsNightShift"]) ? 'N' : 'D';
        $tempShiftType = in_array($row["DutyName"], ['-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*']) ? '' : $tempShiftType;
        $tempShiftType1 = in_array($row["DutyName"], ['-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*']) ? '' : $tempShiftType1;
        $arrRota[$row["RotaWeek"]][$strDay]['Shift'][] = ($row["DutyTypeID"] > 1) ? $tempShiftType1 : $tempShiftType;

        $arrRota[$row["RotaWeek"]]['CurrentWeekInRota'] = $row['CurrentWeekInRota'];
        $arrRota[$row["RotaWeek"]]['WeeksInRota'] = $row['WeeksInRota'];
        $arrRota[$row["RotaWeek"]]['RotaStartWeek'] = $row['RotaStartWeek'];
        $totalWorkingRecord1 = ((count($arrRota[$row["RotaWeek"]][$strDay]['Shift']) == 1) && (!in_array($row["DutyName"], ['-', '--', '--(N>>)', '--(N<<)', 'U(N<<)', 'U(N>>)', '-*']))) ? ++$totalWorkingRecord : $totalWorkingRecord;
        $arrRota[$row["RotaWeek"]]['totalWorkingRecord1'] = $totalWorkingRecord1;
    }

    if (isset($arrRota)) {
        return ($arrRota);
    } else {
        return [];
    }
}

function ReadRotaPatternDepartment($teamId = 0)
{
    $pdo = OpenDBLinkA7();
    $strQuery = "exec [dbo].[usp_ReadRotaPatternTeam] ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $teamId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $satVar = 0;
    $arrRota1 = array();
    foreach ($result as $row) {
        $arrRota[$row["StaffNumber"]]["FullName"] = $row["FullName"];
        $arrRota[$row["StaffNumber"]]["RotaStarts"] = $row["RotaStartWeek"];
        $arrRota[$row["StaffNumber"]]["EFT"] = $row["EFT"];
        $arrRota[$row["StaffNumber"]]["ContractType"] = $row["ContractType"] ?? '';
        $arrRota[$row["StaffNumber"]]["SortCode"] = $row["SortCode"] ?? '';
        $arrRota[$row["StaffNumber"]]["Team"] = $row["TeamDescription"] ?? '';
        $arrRota[$row["StaffNumber"]]["Manager"] = $row["Manager"] ?? '';
        $arrRota[$row["StaffNumber"]]["WeeksInRota"] = $row["WeeksInRota"] ?? '';
        $arrRota[$row["StaffNumber"]]["RotaID"] = $row["RotaID"] ?? 0;
        $arrRota[$row["StaffNumber"]]["ScheduledPersonID"] = $row["ScheduledPersonID"] ?? 0;
        $row["NightShiftCount"] = $row["NightShiftCount"] ?? 0;
        $row["DayShiftCount"] = $row["DayShiftCount"] ?? 0;
        $row["MiscDutyCount"] = $row["MiscDutyCount"] ?? 0;
        $arrRota[$row["StaffNumber"]]["NightsInRota"] = (($arrRota[$row["StaffNumber"]]["NightsInRota"] ?? 0) + $row["NightShiftCount"]) ?? 0;
        $arrRota[$row["StaffNumber"]]["DaysInRota"] = (($arrRota[$row["StaffNumber"]]["DaysInRota"] ?? 0) + $row["DayShiftCount"]) ?? 0;
        $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] = (($arrRota[$row["StaffNumber"]]["MiscDaysInRota"] ?? 0) + $row["MiscDutyCount"]) ?? 0;
        $arrRota[$row["StaffNumber"]]["HoursInRota"] = ($arrRota[$row["StaffNumber"]]["HoursInRota"] ?? 0) + ((isset($row["HoursInRota"])) && ($row["HoursInRota"] > 0)) ? number_format((float) $row["HoursInRota"], 2, '.', '') : 0;
        $NightsInRota = $row["NightsInRota"] ?? 0;
        if (!empty($row["Saturday"])) {
            $satVar = $satVar + 1;
            if (isset($arrRota1[$row["StaffNumber"]]["Saturday"][$row["RotaWeek"]])) {
                if (($row["MiscDutyCount"] > 0)) {
                    $arrRota[$row["StaffNumber"]]["HoursInRota"] = $arrRota[$row["StaffNumber"]]["HoursInRota"] - ($row["HoursInRota"] / ($NightsInRota + $row["MiscDutyCount"] + $row["NightShiftCount"]));
                    $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] = $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] - 1;
                }
                $arrRota1[$row["StaffNumber"]]["Saturday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Saturday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            } else {
                $arrRota1[$row["StaffNumber"]]["Saturday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Saturday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            }
        }
        if (!empty($row["Sunday"])) {
            if (isset($arrRota1[$row["StaffNumber"]]["Sunday"][$row["RotaWeek"]])) {
                if (($row["MiscDutyCount"] > 0)) {
                    $arrRota[$row["StaffNumber"]]["HoursInRota"] = $arrRota[$row["StaffNumber"]]["HoursInRota"] - ($row["HoursInRota"] / ($NightsInRota + $row["MiscDutyCount"] + $row["NightShiftCount"]));
                    $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] = $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] - 1;
                }
                $arrRota1[$row["StaffNumber"]]["Sunday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Sunday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            } else {
                $arrRota1[$row["StaffNumber"]]["Sunday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Sunday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            }
        }
        if (!empty($row["Monday"])) {
            if (isset($arrRota1[$row["StaffNumber"]]["Monday"][$row["RotaWeek"]])) {
                if (($row["MiscDutyCount"] > 0)) {
                    $arrRota[$row["StaffNumber"]]["HoursInRota"] = $arrRota[$row["StaffNumber"]]["HoursInRota"] - ($row["HoursInRota"] / ($NightsInRota + $row["MiscDutyCount"] + $row["NightShiftCount"]));
                    $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] = $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] - 1;
                }
                $arrRota1[$row["StaffNumber"]]["Monday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Monday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            } else {
                $arrRota1[$row["StaffNumber"]]["Monday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Monday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            }
        }
        if (!empty($row["Tuesday"])) {
            if (isset($arrRota1[$row["StaffNumber"]]["Tuesday"][$row["RotaWeek"]])) {
                if (($row["MiscDutyCount"] > 0)) {
                    $arrRota[$row["StaffNumber"]]["HoursInRota"] = $arrRota[$row["StaffNumber"]]["HoursInRota"] - ($row["HoursInRota"] / ($NightsInRota + $row["MiscDutyCount"] + $row["NightShiftCount"]));
                    $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] = $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] - 1;
                }
                $arrRota1[$row["StaffNumber"]]["Tuesday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Tuesday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            } else {
                $arrRota1[$row["StaffNumber"]]["Tuesday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Tuesday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            }
        }
        if (!empty($row["Wednesday"])) {
            if (isset($arrRota1[$row["StaffNumber"]]["Wednesday"][$row["RotaWeek"]])) {
                if (($row["MiscDutyCount"] > 0)) {
                    $arrRota[$row["StaffNumber"]]["HoursInRota"] = $arrRota[$row["StaffNumber"]]["HoursInRota"] - ($row["HoursInRota"] / ($NightsInRota + $row["MiscDutyCount"] + $row["NightShiftCount"]));
                    $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] = $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] - 1;
                }
                $arrRota1[$row["StaffNumber"]]["Wednesday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Wednesday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            } else {
                $arrRota1[$row["StaffNumber"]]["Wednesday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Wednesday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            }
        }
        if (!empty($row["Thursday"])) {
            if (isset($arrRota1[$row["StaffNumber"]]["Thursday"][$row["RotaWeek"]])) {
                if (($row["MiscDutyCount"] > 0)) {
                    $arrRota[$row["StaffNumber"]]["HoursInRota"] = $arrRota[$row["StaffNumber"]]["HoursInRota"] - ($row["HoursInRota"] / ($NightsInRota + $row["MiscDutyCount"] + $row["NightShiftCount"]));
                    $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] = $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] - 1;
                }
                $arrRota1[$row["StaffNumber"]]["Thursday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Thursday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            } else {
                $arrRota1[$row["StaffNumber"]]["Thursday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Thursday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            }
        }
        if (!empty($row["Friday"])) {
            if (isset($arrRota1[$row["StaffNumber"]]["Friday"][$row["RotaWeek"]])) {
                if (($row["MiscDutyCount"] > 0)) {
                    $arrRota[$row["StaffNumber"]]["HoursInRota"] = $arrRota[$row["StaffNumber"]]["HoursInRota"] - ($row["HoursInRota"] / ($NightsInRota + $row["MiscDutyCount"] + $row["NightShiftCount"]));
                    $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] = $arrRota[$row["StaffNumber"]]["MiscDaysInRota"] - 1;
                }
                $arrRota1[$row["StaffNumber"]]["Friday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Friday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            } else {
                $arrRota1[$row["StaffNumber"]]["Friday"][$row["RotaWeek"]] = ((($arrRota1[$row["StaffNumber"]]["Friday"][$row["RotaWeek"]]) ?? 0) + 1) ?? 0;
            }
        }
    }
    if (isset($arrRota)) {
        return ($arrRota);
    } else {
        return [];
    }
}

function ReadAllocationsAndJobsIndividual($intStartWeek = 0, $intEndWeek = 0, $schedulingPersonId = '', $arrTeamDefaults = [], $strUserLogin = 0, $isShiftleader = 0)
{
    $arrTextColours = GetShiftTextColours();

    $uStartDate = strtotime(datefromweek($intEndWeek));
    if ($uStartDate > time()) {
        ReadAllocationsIndividual($intStartWeek, $intEndWeek, $schedulingPersonId, $arrTeamDefaults, $strUserLogin, 0, $isShiftleader = null);
    }

    $pdo = OpenDBLinkA7();
    try {

        $strQuery = "exec [dbo].[usp_get_ReadAllocationsAndJobsIndividual] ?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
        // The parameters
        $stmt->bindParam(1, $intStartWeek, PDO::PARAM_INT);
        $stmt->bindParam(2, $intEndWeek, PDO::PARAM_INT);
        $stmt->bindParam(3, $schedulingPersonId, PDO::PARAM_INT);
        $stmt->bindParam(4, $strUserLogin, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }

    if (count($result) > 0) {
        foreach ($result as $row) {
            $intCurrDep = $row['schedulingTeamId'];
            $strCurrentWeek = $row['WeekNumber'];
            $intCurrentDay = $row['iDay'];
            $intMaskDays = $arrTeamDefaults[$intCurrDep]['MaskAfter'];
            $intMaskAfterUnixDate = strtotime("+$intMaskDays Days");
            $uThisDate = strtotime(datefromweek($strCurrentWeek, $intCurrentDay));
            $strDutyname = $row['DutyName'];
            if ($uThisDate > $intMaskAfterUnixDate) {
                $intMaskType = $arrTeamDefaults[$intCurrDep]['MaskType'];
                $strDutyname = filterDuty($strDutyname, $intMaskType);
                $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]['Masked'] = 1;
            }
            $arrAllocations[$row['schedulingTeamId']]['StaffNumber'] = $row['StaffNumber'];
            $arrAllocations[$row['schedulingTeamId']]['FullName'] = $row['FullName'];
            $arrAllocations[$row['schedulingTeamId']]['SortCode'] = $row['SortCode'];
            $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]['Duty'] = $strDutyname;
            $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]["IsRota"] = 0;
            $duration = round($row["Duration"], 2, \RoundingMode::HalfAwayFromZero);
            if (!is_null($row["StartTime"])) {
                $starttime = gmdate("H:i", (intval($row["StartTime"])) + 1);
                $endtime = gmdate("H:i", (intval($row["EndTime"])) + 1);
                $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]["StartTime"] = $starttime;
                $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]["EndTime"] = $endtime;
            }
            $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]["Duration"] = $duration;
            $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]["HiddenDays"] = $row['HiddenDays'];
            $intConfirmedDays = $arrTeamDefaults[$row['schedulingTeamId']]['ConfirmedDays'];
            $thisdate = datefromweek($row['WeekNumber'], $row['iDay']);
            $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]['ShowRotaAsWell'] = ShowRotaAsWell($thisdate, $intConfirmedDays);
            if (!is_null($row["JobID"])) {
                $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]['Jobs'][$row["JobID"]]['JobName'] = $row['JobName'];
                $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]['Jobs'][$row["JobID"]]['StartTime'] = gmdate("H:i", intval($row["JobStartTime"]) + 1);
                $arrAllocations[$row['schedulingTeamId']]['Weeks'][$row['WeekNumber']][$row['iDay']]['Jobs'][$row["JobID"]]['EndTime'] = gmdate("H:i", intval($row["JobEndTime"]) + 1);
            }
        }
    }

    if (isset($arrAllocations)) {
        return ($arrAllocations);
    }
}

// ################################################################################### ScheduAll Functions ###################################################################################
function ImportScheduAll($intDepartmentID = 0, $intWeekNumber = 0)
{
    $db = OpenDatabase();
    $tsql_callSP = "{call usp_RED_ImportScheduAllDuties( ?, ?)}";
    $params = [
        [$intDepartmentID, SQLSRV_PARAM_IN],
        [$intWeekNumber, SQLSRV_PARAM_IN],
    ];
    $stmt = sqlsrv_query($db, $tsql_callSP, $params);
    if ($stmt === false) {
        echo "Error in executing statement 3.\n";
        die(print_r(sqlsrv_errors(), true));
    } else {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        echo $row['ReturnValue'];
    }
}

function ReadFreelanceAllocations($intDepartmentID = 0, $intWeekNumber = 0, $arrStaffBreaks = '', $userID = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        $strQuery = "exec [dbo].[usp_getAllocationsFreelancers] ?, ?, ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intWeekNumber, PDO::PARAM_INT);
        $stmt->bindParam(2, $intDepartmentID, PDO::PARAM_INT);
        $stmt->bindParam(3, $userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arrAllocations = [];
        foreach ($result as $row) {
            $strCurrentschNumber = $row["ScheduledPersonID"] ?? '';
            $strCurrentStaffNumber = $row['Staffnumber'] ?? '';
            $intCurrWeekNumber = $row['Weeknumber'] ?? 0;
            $intDay = $row['Iday'] ?? 0;
            $arrAllocations['Duties'][$strCurrentschNumber]['Name'] = $row['FullName'] ?? '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Contract'] = '';
            $arrAllocations['Duties'][$strCurrentschNumber]['StaffNumber'] = $strCurrentStaffNumber;
            if (!isset($arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay])) {
                $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay] = [];
            }
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['DutyName'] = $row['Dutyname'] ?? '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['TeamDescription'] = $row['schedulingTeamDescription'] ?? '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['Role'] = '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['ChargeCode'] = '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['HourlyRate'] = '';

            if (!empty($row["StartTime"])) {
                $strStartTime = gmdate("H:i", (($row["StartTime"] ?? 0) * 86400) + 1);
                $strEndTime = gmdate("H:i", (($row["EndTime"] ?? 0) * 86400) + 1);
                $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]["StartTime"] = $strStartTime;
                $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]["EndTime"] = $strEndTime;
            }
            $intDuration = $row['Duration'] ?? 0;
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]["Duration"] = number_format((float) $intDuration / 3600, 2, '.', '');

            $intMealBreak = $row['dutyBreakTime'] ?? 0;
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]["DurationLessMeal"] = number_format((float) ($intDuration - $intMealBreak) / 3600, 2, '.', '');
            if (!empty($row['HourlyRate'])) {
                $hourlyRate = (float)($row['HourlyRate'] ?? 0);
                $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['Cost'] = $hourlyRate * number_format((float) ($intDuration - $intMealBreak) / 3600, 2, '.', '');
            } else {
                $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['Cost'] = 0;
            }
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['DepartmentName'] = $row['AllocationDepartmentName'] ?? '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['DepartmentID'] = $row['DepartmentID'] ?? 0;

            $strPersonComments = '';
            $strFullSBRef = '';
            $personComments = $row['PersonComments'] ?? '';

            if (!empty($personComments)) {
                preg_match_all("/\\[(.*?)\\]/", (string) $personComments, $arrPersonComments);
                if (isset($arrPersonComments[1])) {
                    foreach ($arrPersonComments[1] as $strComments) {
                        $intRefPos = stripos($strComments, 'ref');
                        if ($intRefPos === false) {
                            $strReason = $strComments;
                            $strSBRef = '';
                        } else {
                            $strReason = substr($strComments, 0, $intRefPos);
                            $strSBRef = trim(substr($strComments, $intRefPos + 3));
                        }
                        $strPersonComments .= ' ' . trim($strReason);
                        $strFullSBRef .= ' ' . $strSBRef;
                    }
                }
            }

            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['PersonComments'] = $personComments;
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intCurrWeekNumber][$intDay]['SmartBookRef'] = $strFullSBRef;

            if (!isset($arrAllocations['Hours'][$intCurrWeekNumber][$intDay])) {
                $arrAllocations['Hours'][$intCurrWeekNumber][$intDay] = 0;
            }
            if (!isset($arrAllocations['Count'][$intCurrWeekNumber][$intDay])) {
                $arrAllocations['Count'][$intCurrWeekNumber][$intDay] = 0;
            }
            if (!isset($arrAllocations['Total'])) {
                $arrAllocations['Total'] = 0;
            }
            if (!isset($arrAllocations['TotalCount'])) {
                $arrAllocations['TotalCount'] = 0;
            }
            $arrAllocations['Hours'][$intCurrWeekNumber][$intDay] = number_format((float) ($arrAllocations['Hours'][$intCurrWeekNumber][$intDay] + $intDuration - $intMealBreak) / 3600, 2, '.', '');
            $arrAllocations['Count'][$intCurrWeekNumber][$intDay] = $arrAllocations['Count'][$intCurrWeekNumber][$intDay] + 1;
            $arrAllocations['Total'] = number_format((float) ($arrAllocations['Total'] + $intDuration - $intMealBreak) / 3600, 2, '.', '');
            $arrAllocations['TotalCount'] = $arrAllocations['TotalCount'] + 1;
        }

        if (!empty($arrAllocations)) {
            return $arrAllocations;
        }

        return [];
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        return [];
    }
}

function TidyDutyName($strDutyName = '')
{

    $intNeedsTrim = preg_match("/^[A-Za-z][0-9][0-9]+/", (string) $strDutyName);
    if ($intNeedsTrim == 1) {
        $strDutyName = substr((string) $strDutyName, strpos((string) $strDutyName, ' '));
    }
    $pos = strpos((string) $strDutyName, '|');
    if ($pos !== false) {
        $strDutyName = substr((string) $strDutyName, 0, $pos);
    }
    $strDutyName = trim((string) $strDutyName);
    return ($strDutyName);
}

function GetHiddenDays($intDepartmentID = 0, $dteStartDate = '', $dteEndtDate = '')
{
    $pdo = OpenDBLinkA7();
    if ($intDepartmentID == 0) {

        $strQuery = "SELECT  dDate, SchedulingTeamId
FROM         AllocationsHiddenDays
WHERE        (isRestricted = 1)
AND          (dDate >= CONVERT(DATETIME, '$dteStartDate 00:00:00', 102))
AND          (dDate <= CONVERT(DATETIME, '$dteEndtDate 00:00:00', 102))";
    } else {

        $strQuery = "SELECT       dDate, SchedulingTeamId
               FROM         AllocationsHiddenDays
               WHERE        (SchedulingTeamId = $intDepartmentID)
               AND          (isRestricted = 1)
               AND          (dDate >= CONVERT(DATETIME, '$dteStartDate 00:00:00', 102))
               AND          (dDate <= CONVERT(DATETIME, '$dteEndtDate 00:00:00', 102))";
    }
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $rsHiddenDays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rsHiddenDays as $row) {
        $arrHiddenDays[$row['SchedulingTeamId']][(new DateTime($row['dDate']))->format('Y-m-d')] = 1;
    }
    if (isset($arrHiddenDays)) {
        return ($arrHiddenDays);
    }
}
/**
 * Desc : Apply the group on allocations
 * Parms : Array
 */
function ApplyGrouping($arrAllocations = '', $arrGroups = '')
{
    if (isset($arrAllocations['Duties'])) {
        foreach ($arrGroups as $intGroupID => $arrGroup) {
            $strFilterText = $arrGroup['Filter'];
            $arrFilterText = explode(';', (string) $strFilterText);
            foreach ($arrAllocations['Duties'] as $strDutyName => $arrAllocation) {
                $intGroupIt = 0;
                $pos1 = false;
                foreach ($arrFilterText as $strThisFilterText) {
                    if (trim($strThisFilterText) === '') {
                        continue;
                    }
                    if (isset($strDutyName)) {
                        $pos = strpos($strDutyName, $strThisFilterText);
                        if ($pos) {
                            $expfilter = explode(" ", $strDutyName);
                            if ((count($expfilter) > 1)) {
                                $countPos1True = [];
                                for ($k = 0; $k < count($expfilter); $k++) {
                                    if ($expfilter[$k][0] == '!') {
                                        $pos1 = strpos($expfilter[$k], $strThisFilterText);
                                        $countPos1True[] = $pos1;
                                    }
                                }
                                if (in_array(1, $countPos1True)) {
                                    $pos1 = 1;
                                }
                            } else {
                                if ($strDutyName[0] == '!') {
                                    $pos1 = strpos($strDutyName, ('!' . $strThisFilterText));
                                    if ($pos1 === false) {
                                        $pos1 = strpos($strDutyName, $strThisFilterText);
                                    }
                                }
                            }
                        }
                        if ($pos !== false && $pos1 === false) {
                            $intGroupIt = 1;
                        }
                    }
                }
                if ($intGroupIt == 1) {
                    $arrGrouped[$intGroupID][$strDutyName] = $arrAllocation;
                    unset($arrAllocations['Duties'][$strDutyName]);
                }
            }
        }
        if (isset($arrGrouped)) {
            $arrAllocations['Grouped'] = $arrGrouped;
        }
    }
    return ($arrAllocations);
}

function SanitiseDuty($strDutyName = '')
{
    $strDutyName = preg_replace('`\[[^\]]*\]`', '', (string) $strDutyName);
    // Remove any exclammation points
    $strDutyName = str_replace('!', '', $strDutyName);
    $strDutyName = preg_replace('/\s+/', ' ', $strDutyName);
    $strDutyName = trim($strDutyName);
    return ($strDutyName);
}

function GetDayStatus($strCurrDate = '', $intCanViewComments = 0, $intMaskDays = 0, $arrHiddenDays = false)
{
    global $intFixedDays;
    if ($intCanViewComments == 1) {
        if (isset($arrHiddenDays[$strCurrDate])) {
            $strStatusClass = 'dayhidden allocations-showday-menu';
        } else {
            $interval = date_diff(date_create(date("y-m-d")), date_create($strCurrDate));
            $interval = $interval->format('%r%a');
            if ($interval <= $intFixedDays) {
                $strStatusClass = 'dayfixed allocations-hideday-menu';
            } else {
                //always > 14
                if ($interval > $intFixedDays && $interval <= $intMaskDays) {
                    $strStatusClass = 'daynotfixed allocations-hideday-menu'; //Yellow
                } else {
                    $strStatusClass = 'dotw allocations-hideday-menu'; //Red
                }
            }
        }
    } else {
        $interval = date_diff(date_create(date("y-m-d")), date_create($strCurrDate));
        $interval = $interval->format('%r%a');

        if ($interval <= $intFixedDays) {
            $strStatusClass = 'dayfixed';
        } else {
            //always > 14
            if ($interval > $intFixedDays && $interval <= $intMaskDays) {
                $strStatusClass = 'daynotfixed'; //Yellow
            } else {
                $strStatusClass = 'dotw'; //Red
            }
        }
    }

    return ($strStatusClass);
}

/* This function is used to get the Production Views Filters from the DB as per the scheduling teams ID.
 *
 * @param $intTeamID This param contains the TeamID information
 *
 * @return array Returns the list of Production Views Filters from the DB as per the scheduling teams ID
 */
function GetProdViewGroupsTeam($intTeamID = 0)
{
    $pdo = OpenDBLinkA7();

    $strQuery = "SELECT        id, Description, Filter, SortOrder
               FROM          ProdViewGroups
               WHERE         SchedulingTeamId = :teamID
               ORDER BY      SortOrder";

    $stmt = $pdo->prepare($strQuery);
    $stmt->bindValue(':teamID', $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($row)) {
        foreach ($row as $prodViewDataKey => $prodViewDataVal) {
            $arrViews[$prodViewDataVal['id']]['Description'] = $prodViewDataVal['Description'];
            $arrViews[$prodViewDataVal['id']]['Filter'] = $prodViewDataVal['Filter'];
            $arrViews[$prodViewDataVal['id']]['SortOrder'] = $prodViewDataVal['SortOrder'];
        }
    }
    if (isset($arrViews)) {
        return ($arrViews);
    }
}

/**
 * This function is used to make Duty Request /UnMark Request By DutyID.
 *
 * @param $DutyID This param contains the DutyID information
 * @param $isRequestStatus This param contains the isRequestStatus information
 * @param $isEditedStatus This param contains the isEditedStatus information
 *
 * @return array Returns the list of Production Views Filters from the DB as per the scheduling teams ID
 */
function markOrUnmarkDutyForRequest($dutyId = 0, $isRequestStatus = 0, $teamId = 0)
{
    try {
        $isRequestStatus = ($isRequestStatus == 0) ? 1 : 0;
        $pdo = OpenDBLinkA7();
        $current_User = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
        $current_UserName = $_SESSION['user']['FullName'];
        $status = 1;
        $strstatus = 'success';
        $sql = "exec [dbo].[usp_mod_MarkAllocationDutyRequest] ?,?,?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $dutyId, PDO::PARAM_INT);
        $stmt->bindValue(2, $teamId, PDO::PARAM_INT);
        $stmt->bindValue(3, $isRequestStatus, PDO::PARAM_INT);
        $stmt->bindParam(4, $current_User, PDO::PARAM_INT);
        $stmt->bindParam(5, $current_UserName, PDO::PARAM_STR);
        $stmt->bindParam(6, $status, PDO::PARAM_INT);
        $stmt->bindParam(7, $strstatus, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($result);
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
        return false;
    }
}

/**
 * Decription: Function to Fetch Hidden Days for Weekly View
 * @param int $intTeamId, str $dteStartDate, str $dteEndtDate
 * return [] on sucess Or false
 */
function ReadHiddenDays($intTeamId = 0, $dteStartDate = '', $dteEndtDate = '')
{
    try {
        $pdo = OpenDBLinkA7();
        if ($intTeamId == 0) {
            $strQuery = "SELECT dDate, SchedulingTeamId FROM AllocationsHiddenDays WHERE (isRestricted = 1) AND (dDate >= CONVERT(DATETIME, ?, 102)) AND (dDate <= CONVERT(DATETIME, ?, 102))";
            $stmt = $pdo->prepare($strQuery);
            $stmt->bindValue(1, $dteStartDate, PDO::PARAM_STR);
            $stmt->bindValue(2, $dteEndtDate, PDO::PARAM_STR);
        } else {
            $strQuery = "SELECT dDate, SchedulingTeamId FROM AllocationsHiddenDays WHERE (SchedulingTeamId = ?) AND (isRestricted = 1) AND (dDate >= CONVERT(DATETIME, ?, 102)) AND (dDate <= CONVERT(DATETIME, ?, 102))";
            $stmt = $pdo->prepare($strQuery);
            $stmt->bindValue(1, $intTeamId, PDO::PARAM_INT);
            $stmt->bindValue(2, $dteStartDate, PDO::PARAM_STR);
            $stmt->bindValue(3, $dteEndtDate, PDO::PARAM_STR);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            $arrHiddenDays[$row['SchedulingTeamId']][$row['dDate']] = 1;
        }

        if (isset($arrHiddenDays)) {
            return ($arrHiddenDays);
        } else {
            return false;
        }
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    return false;
}
/*
* Filter data of array
*/
function filterData($filter, $condition, $data)
{
    // Convert comma-separated filter string to array
    $filterArray = array_map('trim',  $filter);
    $matched = false;
    if ($condition == '*') {
        foreach ($data as $item) {
            foreach ($filterArray as $word) {
                if (!empty($word) && !empty($item) && stripos(mb_strtolower($item), mb_strtolower($word)) !== false) {
                    $matched = true;
                    break 2;
                }
            }
        }
    }
    if ($condition == '&') {
        foreach ($data as $item) {
            foreach ($filterArray as $word) {
                if (!empty($word) && !empty($item)) {
                    continue;
                }
                if (mb_strtolower($item) == mb_strtolower($word)) {
                    $matched = true;
                } else {
                    $matched = false;
                    break 2;
                }
            }
        }
    }
    if ($condition == ';') {
        foreach ($data as $item) {
            foreach ($filterArray as $word) {
                if (!empty($word) && !empty($item) && mb_strtolower($item) == mb_strtolower($word)) {
                    $matched = true;
                    break 2;
                }
            }
        }
    }

    if ($condition == '!') {
        foreach ($data as $item) {
            foreach ($filterArray as $word) {
                if (!empty($word) && !empty($item) && stripos($item, $word) !== false) {
                    $matched = false;
                    break 2;
                }
            }
        }
    }

    if ($condition == '!') {
        foreach ($data as $item) {
            foreach ($filterArray as $word) {
                if (!empty($word) && !empty($item) && stripos($item, $word) !== false) {
                    $matched = false;
                    break 2;
                }
            }
        }
    }

    if ($condition == '%') {
        foreach ($data as $item) {
            foreach ($filterArray as $word) {
                if (!empty($word) && !empty($item) && str_starts_with(mb_strtolower($item), mb_strtolower($word)) !== false) {
                    $matched = true;
                    break 2;
                }
            }
        }
    }
    return $matched;
}

/***
 * Description :New Production View Method
 */
function GetReadAllocationsByDuties($intStartWeekNumber = 0, $intTeamID = 0, $intDayStart = 0, $intDayCount = 0, $intCanViewComments = 0, $intIsShiftLeader = 0, $prodStartDate = 0, $prodEndDate = 0, $loggedUserNetLogin = 0, $arrBaseCodes = '', $arrFilterText = '', $intMatchType = 0, $currentteam = 0, $filterId = 0, $dutyFilter = '', $canViewAdditional = 1)
{
    global $intFixedDays;
    $pdo = OpenDBLinkA7();
    $intEndWeek = $intStartWeekNumber;
    if (($intDayStart == 0) && ($intDayCount > 7)) {
        $intEndWeek = addweeks($intStartWeekNumber, ceil($intDayCount / 7) - 1);
    } else {
        $intEndWeek = addweeks($intStartWeekNumber, ceil($intDayCount / 7) - 1);
        $intEndWeek = addweeks($intEndWeek, 1);
    }
    if (($intDayStart != 0) && ($intDayCount == 7)) {
        $intEndWeek = addweeks($intEndWeek, 1);
    }
    $intLoopWeeks = $intStartWeekNumber;
    $intLoopEndWeek = $intEndWeek;
    $intArrPos = 0;
    while ($intLoopWeeks <= $intLoopEndWeek) {
        $arrWeeksToArray[$intLoopWeeks] = $intArrPos;
        $intLoopWeeks = addweeks($intLoopWeeks, 1);
        $intArrPos++;
    }
    try {
        $strQuery = "exec [dbo].[usp_get_ReadAllocationsByDuties] '" . $intTeamID . "','" . $intStartWeekNumber . "','" . $intEndWeek . "','" . $prodStartDate . "','" . $prodEndDate . "', '" . $intIsShiftLeader . "', '" . $loggedUserNetLogin . "'";
        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
        $QueryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('DB Error', (array) $e);
    }

    /** Check Day Editable Code Start and filter */
    $arrEditable = [];
    $arrEdited = [];
    $arrAllocations = [];
    if (!empty($QueryData)) {
        $arrEdited = [];
        $arrEditable = [];
        $filterData = [];
        if ($dutyFilter != '') {
            $filterData['DutyFilter'] = [
                'filter' => explode(',', $dutyFilter),
                'condition' => '*'
            ];
            $filterData['AND'] = 0;
        } else if ($filterId != 0) {
            $strQuery = "SELECT DutyFilter,DutyLabel,AdditionalTeams,SortCodeFilter,AndMatch FROM AutoPagesFilters (NOLOCK) WHERE ID=:filterId";
            $stmt = $pdo->prepare($strQuery);
            $stmt->bindValue(':filterId', $filterId, PDO::PARAM_INT);
            $stmt->execute();
            $filterQueryData = $stmt->fetch(PDO::FETCH_ASSOC);
            $filterData['AND'] = empty($filterQueryData['AndMatch']) ? 0 : $filterQueryData['AndMatch'];
            foreach(['DutyFilter', 'DutyLabel', 'SortCodeFilter'] as $dataColumn) {
                if(isset($filterQueryData[$dataColumn]) && strlen($filterQueryData[$dataColumn]) == 1) {
                    continue;
                }
                if (isset($filterQueryData[$dataColumn]) && str_contains($filterQueryData[$dataColumn], '&')) {
                    $filterData[$dataColumn] = [
                        'filter' => explode('&', $filterQueryData[$dataColumn]),
                        'condition' => '&'
                    ];
                } else if (isset($filterQueryData[$dataColumn]) && str_contains($filterQueryData[$dataColumn], ';')) {
                    $filterData[$dataColumn] = [
                        'filter' => explode(';', $filterQueryData[$dataColumn]),
                        'condition' => ';'
                    ];
                } else if (isset($filterQueryData[$dataColumn]) && str_contains($filterQueryData[$dataColumn], '!')) {
                    $filterData[$dataColumn] = [
                        'filter' => explode('!', $filterQueryData[$dataColumn]),
                        'condition' => '!'
                    ];
                } else if (isset($filterQueryData[$dataColumn]) && str_contains($filterQueryData[$dataColumn], '%')) {
                    $filterData[$dataColumn] = [
                        'filter' => explode('%', $filterQueryData[$dataColumn]),
                        'condition' => '%'
                    ];
                } else if (isset($filterQueryData[$dataColumn]) && str_contains($filterQueryData[$dataColumn], '*')) {
                    $filterData[$dataColumn] = [
                        'filter' => explode('*', $filterQueryData[$dataColumn]),
                        'condition' => '*'
                    ];
                }
            }
        }
         
        foreach ($QueryData as $key => $row) {

            //filter the data
            if (!empty($filterData)) {
                $allMatched = true;
                $singleMatch = false;
                if (isset($filterData['DutyFilter'])) {
                    $matchFound = filterData($filterData['DutyFilter']['filter'], $filterData['DutyFilter']['condition'], [$row['DutyName']]);
                    if ($matchFound) {
                        $singleMatch = true;
                    }
                    if (!$matchFound) {
                        $allMatched = false;
                    }
                }
                if (isset($filterData['DutyLabel'])) {
                    $matchFound = filterData($filterData['DutyLabel']['filter'], $filterData['DutyLabel']['condition'], [
                        $row['dutyProgramId'],
                        $row['DutyProgramId2'],
                        $row['DutyProgramId3'],
                        $row['DutyProgramId4'],
                        $row['DutyProgramId5'],
                        $row['DutyProgramId6']
                    ]);
                    if ($matchFound) {
                        $singleMatch = true;
                    }
                    if (!$matchFound) {
                        $allMatched = false;
                    }
                }
                if(isset($filterData['SortCodeFilter'])) {
                    $matchFound = filterData($filterData['SortCodeFilter']['filter'], $filterData['SortCodeFilter']['condition'], [$row['SortCode']]);
                    if ($matchFound) {
                        $singleMatch = true;
                    }
                    if (!$matchFound) {
                        $allMatched = false;
                    }
                }

                if(($filterData['AND'] == 0 && $singleMatch == false) || ($filterData['AND'] == 1 && $allMatched == false)) {
                    unset($QueryData[$key]);
                    continue;
                }
            }

            $dutyDateinfor = explode(" ", (string) $row['DutyDate']);
            $dutyDate = $dutyDateinfor[0];
            if (!isset($arrEditable[$dutyDate]['IsDayEditable']) && ($row['IsDayEditable'] > 0 && $row['SchedulingTeamId'] == $currentteam)) {
                $arrEditable[$dutyDate]['IsDayEditable'] = $row['IsDayEditable'];
            }
            if (!isset($arrEdited[$dutyDate]['isEdited']) && $row['isEdited'] > 0) {
                $arrEdited[$dutyDate]['isEdited'] = $row['isEdited'];
            }
        }
        $arrAllocations['IsDayEditable'] = $arrEditable;
        $arrAllocations['isEdited'] = $arrEdited;
    }

    /** Check Day Editable Code End */

    if (!empty($QueryData)) {
        foreach ($QueryData as $row) {

            if ((int)$canViewAdditional === 0 && in_array((int)($row['IsHomeTeam'] ?? 1), [0, 2], true) && (int)($row['DisplayInViewScreen'] ?? 1) === 0) {
                // Skip this row completely in Production View
                continue;
            }

            $dutyNameToSkip = strtoupper((string) $row['DutyName']);
            if ($dutyNameToSkip === 'U' || $dutyNameToSkip === 'U (N<<)' || $dutyNameToSkip === 'U (N>>)') {
                continue;
            }
            if (preg_match("/^[A-Za-z]7/", (string) $row['DutyName'])) {
                if (strlen((string) $row['DutyName']) >= 1 && $row['DutyName'][1] === '7') {
                    $HasSevenFlag = 1;
                }
            } else {
                $HasSevenFlag = 0;
            }
            if (!empty($row['DutyName']) && !str_contains(strtoupper((string) $row['DutyName']), 'ABSENT') && !str_contains(strtoupper((string) $row['DutyName']), 'SICK')) {
                // The position in the array
                $intArrPos = ($arrWeeksToArray[$row['WeekNumber']] * 7) + $row['iDay'];
                $strDutyName = $row['DutyName'];
                if ($intArrPos >= 0) {
                    // First Find any leave....
                    if (strtoupper((string) $strDutyName) == 'LEAVE' || strtoupper((string) $strDutyName) == 'OFF LEAVE') {
                        if ($intMatchType == -1) {
                            $intKeepit = 1;
                        } else {
                            $intKeepit = 0;
                            if (!empty($arrFilterText)) {
                                foreach ($arrFilterText as $intArrID => $strFilter) {
                                    if (str_starts_with((string) $strFilter, '*')) {
                                        // It's a wildcard match
                                        if (stripos((string) $row['SortCode'], substr((string) $strFilter, 1)) !== false) {
                                            $intKeepit = 1;
                                        }
                                    } else {
                                        if (strtoupper(substr((string) $row['SortCode'], 0, strlen((string) $strFilter))) == strtoupper((string) $strFilter)) {
                                            $intKeepit = 1;
                                        }
                                    }
                                }
                            }
                        }
                        if ($intKeepit == 1) {
                            $arrAllocations['Leave'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['FullName'] = $row['FullName'];
                            $arrAllocations['Leave'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['Initials'] = $row['Initials'];
                        }
                    } else {
                        // Duties starting Letter and space
                        if (((strlen((string) $strDutyName) == 1 && strtoupper((string) $strDutyName) != "U" && strtoupper((string) $strDutyName) != "Z" && $strDutyName != "-") && ($intCanViewComments == 1 || $intIsShiftLeader == 1)) || (ctype_alpha(substr((string) $strDutyName, 0, 1)) == 1 && (substr((string) $strDutyName, 1, 1) == ' ' || substr((string) $strDutyName, 1, 1) == '/') && ($intCanViewComments == 1 || $intIsShiftLeader == 1))) {
                            if ($intMatchType == -1) {
                                $intKeepit = 1;
                            } else {
                                $intKeepit = 0;
                                if (!empty($arrFilterText)) {
                                    foreach ($arrFilterText as $intArrID => $strFilter) {
                                        if (str_starts_with((string) $strFilter, '*')) {
                                            // It's a wildcard match
                                            if (stripos((string) $row['SortCode'], substr((string) $strFilter, 1)) !== false) {
                                                $intKeepit = 1;
                                            }
                                        } else {
                                            if (strtoupper(substr((string) $row['SortCode'], 0, strlen((string) $strFilter))) == strtoupper((string) $strFilter)) {
                                                $intKeepit = 1;
                                            }
                                        }
                                    }
                                }
                            }
                            if (isset($arrBaseCodes[$row['BaseCode']])) {
                                $intKeepit = 1;
                            }
                            if ($intKeepit == 1) {
                                if ($row['StaffNumber'] != '0' || !is_null($row['StaffNumber'])) {
                                    $strDutyName = TidyDutyName($strDutyName);
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['FullName'] = $row['FullName'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['Initials'] = $row['Initials'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['ID'] = $row['AllocationsDutyID'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['isCopy'] = $row['isCopy'];
                                    $starttime = gmdate("H:i", ($row["StartTime"]));
                                    $endtime = gmdate("H:i", ($row["EndTime"]));
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['StartTime'] = $starttime;
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['EndTime'] = $endtime;
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['Duration'] = gmdate("H", ($row['Duration']));
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['SchedulingTeamId'] = $row['SchedulingTeamId'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['LeaveStartTime'] = $row['LeaveStartTime'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['LeaveEndTime'] = $row['LeaveEndTime'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['HasSevenFlag'] = $HasSevenFlag;
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['allocationSPID'] = $row['ID'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['allocationId'] = $row['AllocationID'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['scheduledpersonid'] = $row['StaffNumber'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['weeknum'] = $row['WeekNumber'];
                                    $arrAllocations['AvailableStaff'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['dutydate'] = $row['DutyDate'];
                                }
                            }
                        } else {
                            // Any duties with a start time
                            if (!is_null($row['StartTime']) && ($row['Duration'] > 0) && !empty($strDutyName)) {
                                $intKeepit = 1;
                                // Apply Filter here...
                                if ($intMatchType == 0) {
                                    // Match any occurance
                                    $intKeepit = 0;
                                    if (!empty($arrFilterText)) {
                                        foreach ($arrFilterText as $intArrID => $strFilter) {
                                            if (str_starts_with((string) $strFilter, '*')) {
                                                // It's a wildcard match
                                                if (stripos((string) $strDutyName, substr((string) $strFilter, 1)) !== false) {
                                                    $intKeepit = 1;
                                                }
                                            } else {
                                                if (strtoupper(substr((string) $strDutyName, 0, strlen((string) $strFilter))) == strtoupper((string) $strFilter)) {
                                                    $intKeepit = 1;
                                                }
                                            }
                                        }
                                    }
                                }
                                if ($intMatchType == 1) {
                                    // Match any occurance
                                    $intKeepit = 1;
                                    if (!empty($arrFilterText)) {
                                        foreach ($arrFilterText as $intArrID => $strFilter) {
                                            if (str_starts_with((string) $strFilter, '*')) {
                                                // It's a wildcard match
                                                if (stripos((string) $strDutyName, substr((string) $strFilter, 1)) === false) {
                                                    $intKeepit = 0;
                                                }
                                            } else {
                                                if (strtoupper(substr((string) $strDutyName, 0, strlen((string) $strFilter))) != strtoupper((string) $strFilter)) {
                                                    $intKeepit = 0;
                                                }
                                            }
                                        }
                                    }
                                } //if match type=1
                                if (isset($arrBaseCodes[$row['BaseCode']])) {
                                    $intKeepit = 1;
                                }
                                if ($intKeepit == 1) {
                                    $strOrigDutyName = $strDutyName;
                                    $strDutyName = TidyDutyName($strDutyName);
                                    if ($row['StaffNumber'] > 0) {
                                        // Add the DutyID to the array
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['SchedulingTeamId'] = $row['SchedulingTeamId'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['FullName'] = $row['FullName'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['Initials'] = $row['Initials'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['ID'] = $row['AllocationsDutyID'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['isCopy'] = $row['isCopy'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['Jobs'] = $row['jobs'] ? json_decode((string) $row['jobs'], true) : [];
                                        $starttime = gmdate("H:i", ($row["StartTime"]));
                                        $endtime = gmdate("H:i", ($row["EndTime"]));
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['StartTime'] = $starttime;
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['EndTime'] = $endtime;
                                        if ($row['DutyComments'] == 1) {
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['HasComments'] = 1;
                                        } else {
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['HasComments'] = 0;
                                        }
                                        if (substr((string) $strOrigDutyName, 1, 1) == 8) {
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['isDuplicate'] = 1;
                                        }
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['LeaveStartTime'] = $row['LeaveStartTime'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['LeaveEndTime'] = $row['LeaveEndTime'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['HasSevenFlag'] = $HasSevenFlag;
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['allocationSPID'] = $row['ID'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['allocationId'] = $row['AllocationID'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['scheduledpersonid'] = $row['StaffNumber'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['weeknum'] = $row['WeekNumber'];
                                        $arrAllocations['Duties'][$strDutyName][$intArrPos]['Names'][$row['StaffNumber']]['dutydate'] = $row['DutyDate'];
                                    } else {
                                        if (!str_starts_with(strtoupper((string) $strDutyName), 'NO')) {
                                            if (isset($arrAllocations['Duties'][$strDutyName][$intArrPos]['NeedsCover'])) {
                                                $arrAllocations['Duties'][$strDutyName][$intArrPos]['NeedsCover'] = $arrAllocations['Duties'][$strDutyName][$intArrPos]['NeedsCover'] + 1;
                                            } else {
                                                $arrAllocations['Duties'][$strDutyName][$intArrPos]['NeedsCover'] = 1;
                                            }
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['ID'] = $row['AllocationsDutyID'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['isCopy'] = $row['isCopy'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['WeekNumber'] = $row['WeekNumber'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['iDay'] = $row['iDay'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['dutyName'] = $strDutyName;
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['dutyDate'] = $dutyDate;
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['SchedulingTeamId'] = $row['SchedulingTeamId'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['HasSevenFlag'] = $HasSevenFlag;
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['allocationSPID'] = $row['ID'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['allocationId'] = $row['AllocationID'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['scheduledpersonid'] = $row['StaffNumber'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['weeknum'] = $row['WeekNumber'];
                                            $arrAllocations['Duties'][$strDutyName][$intArrPos]['dutydate'] = $row['DutyDate'];
                                        }
                                    }
                                }
                            }
                        }
                    } //else Close
                }
            } //Absent and Sick Check
        } //Foreach Close
    }
    if (isset($arrAllocations['Duties'])) {
        // Get the max number of people pertype....
        foreach ($arrAllocations['Duties'] as $strDutyName => $arrDuty) {
            if ($strDutyName != "") {
                $intCountPeople = 0;
                foreach ($arrDuty as $intDay => $arrDay) {
                    $intCountDay = 0;
                    if (isset($arrDay['NeedsCover'])) {
                        $intCountDay = 1;
                    }
                    if (isset($arrDay['Names'])) {
                        $intCountDay = $intCountDay + count($arrDay['Names']);
                    }
                    if ($intCountDay > $intCountPeople) {
                        $intCountPeople = $intCountDay;
                    }
                }
                $arrAllocations['Duties'][$strDutyName]['CountPeople'] = $intCountPeople;
            }
        }
    }
    // Do the Leave
    if (isset($arrAllocations['Leave'])) {
        foreach ($arrAllocations['Leave'] as $strDutyName => $arrDuty) {
            $intCountPeople = 0;
            foreach ($arrDuty as $intDay => $arrDay) {
                $intCountDay = 0;
                if (isset($arrDay['NeedsCover'])) {
                    $intCountDay = 1;
                }
                if (isset($arrDay['Names'])) {
                    $intCountDay = $intCountDay + count($arrDay['Names']);
                }
                if ($intCountDay > $intCountPeople) {
                    $intCountPeople = $intCountDay;
                }
            }
            $arrAllocations['Leave'][$strDutyName]['CountPeople'] = $intCountPeople;
        }
    }
    // AvailableStaff
    if (isset($arrAllocations['AvailableStaff'])) {
        // Get the max number of people pertype....
        foreach ($arrAllocations['AvailableStaff'] as $strDutyName => $arrDuty) {
            $intCountPeople = 0;
            foreach ($arrDuty as $intDay => $arrDay) {
                $intCountDay = 0;
                if (isset($arrDay['Names'])) {
                    $intCountDay = $intCountDay + count($arrDay['Names']);
                }
                if ($intCountDay > $intCountPeople) {
                    $intCountPeople = $intCountDay;
                }
            }
            $arrAllocations['AvailableStaff'][$strDutyName]['CountPeople'] = $intCountPeople;
        }
    }
    return ($arrAllocations);
}

/**
 * This function is use to make Allocation Assign Request By AllocationID.
 * @param $AllocationID This param contains the AllocationID information
 * @param $schdeulledPersonID This param contains the schdeulledPersonID information
 * @return String if Sucess OR false
 */
function markAllocationCover($AllocationDutyID = 0, $schdeulledPersonID = 0, $swapAllocationDutyID = 0, $intTeamID = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        $current_User = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
        $current_UserName = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
        $query1 = "SELECT     chargingdutydate,
           UD_DisplayFirstName  displayfirstname,
           UD_DisplayLastName displaylastname,
           AD_DutyName dutyname,
           establishcode,
           establishcodedescription,
           isactual,
           activitycodename,
           AC.description,
           unitprice,
           chargewbscodename,
           establishcode,
           establishcodedescription,
           comments,
           contact,
           telephone,
           quantity
        FROM       chargingdutymapping_link cdml
        INNER JOIN establishcode EC ON         EC.establishcodeid = cdml.estabcodeid
        INNER JOIN activitycode AC ON         AC.activitycodeid = cdml.activitycodeid
        INNER JOIN chargewbscode CWC ON         CWC.chargewbscodeid = cdml.chargecodeid
        INNER JOIN AllocationsScheduledPersons AL ON         al.ASP_AllocationsSPID = cdml.allocationid
        INNER JOIN AllocationsDuties AD on AD.AD_AllocationsDutyID = ASP_AllocationsDutyID
        INNER JOIN UserDetails SP ON         SP.UD_UserID = cdml.personid
        WHERE      cdml.allocationid IN ($AllocationDutyID, $swapAllocationDutyID)";
        $stmt1 = $pdo->prepare($query1);
        $stmt1->execute();
        $chargingResult = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        $oldAllocationData = GetAllocationsDetailsByAllocationDutyId($AllocationDutyID);
        $oldAllocationSPId = $oldAllocationData['AllocationsSPID'] ?? $oldAllocationData['AllocationsDutyID'];
        $allocationID = $oldAllocationData['AllocationID'];
        $newDuty = GetAllocationsDetailsByAllocationDutyId($swapAllocationDutyID);
        $newAllocationSPId = $newDuty['AllocationsSPID'] ?? 0;
        $assignVal = '';
        if (!empty($newAllocationSPId)) {
            $assignVal = 'ASSIGN';
        } else {
            $assignVal = 'ASSGNTOADDPERSON';
        }
        $sql = "EXEC [dbo].[usp_Edit_Allocations] '" . $assignVal . "', :netLogin, @pAllocationsID = :allocationID1 , @Fromid= :oldAllocationSPId, @ToId= :newAllocationSPID, @pSchedulingpersonid= :newScheduledPersonId, @pIsShiftleader=1;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':netLogin', $current_UserName, PDO::PARAM_STR);
        $stmt->bindValue(':allocationID1', $allocationID, PDO::PARAM_INT);
        $stmt->bindValue(':oldAllocationSPId', $oldAllocationSPId, PDO::PARAM_INT);
        $stmt->bindValue(':newAllocationSPID', $newAllocationSPId, PDO::PARAM_INT);
        $stmt->bindValue(':newScheduledPersonId', $schdeulledPersonID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $oldAllocationData = GetAllocationsDetailsByAllocationDutyId($AllocationDutyID);
        $oldAllocationSPId = $oldAllocationData['AllocationsSPID'];
        $sql = "EXEC [usp_mod_PublishIndividulAllocations] @AllocationsID = :allocationID1, @AllocationsDutyID = :allocationDutyId1, @AllocationsSPID = :oldAllocationSPId1;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':allocationID1', $allocationID, PDO::PARAM_INT);
        $stmt->bindValue(':allocationDutyId1', $AllocationDutyID, PDO::PARAM_INT);
        $stmt->bindValue(':oldAllocationSPId1', $oldAllocationSPId, PDO::PARAM_INT);
        $stmt->execute();
        if ($swapAllocationDutyID > 0) {
            $newDuty = GetAllocationsDetailsByAllocationDutyId($swapAllocationDutyID);
            $newAllocationSPId = $newDuty['AllocationsSPID'];
            $sql = "EXEC [usp_mod_PublishIndividulAllocations] @AllocationsID = :allocationID1, @AllocationsDutyID = :allocationDutyId1, @AllocationsSPID = :newAllocationSPId1;";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':allocationID1', $allocationID, PDO::PARAM_INT);
            $stmt->bindValue(':allocationDutyId1', $swapAllocationDutyID, PDO::PARAM_INT);
            $stmt->bindValue(':newAllocationSPId1', $newAllocationSPId, PDO::PARAM_INT);
            $stmt->execute();
        }

        if (($result['SPExecStatus'] == 0) && (count($chargingResult) > 0)) {
            $chargingDate = date('d/m/Y', strtotime((string) $chargingResult[0]['ChargingDutyDate']));
            $query2 = "select Email, schedulingTeamName from schedulingTeams where schedulingTeamId = $intTeamID";
            $stmt2 = $pdo->prepare($query2);
            $stmt2->execute();
            $emailResult = $stmt2->fetch(PDO::FETCH_ASSOC);
            $chargingResult1 = [];
            foreach ($chargingResult as $chargingResultVal1) {
                if ($chargingResultVal1['ID'] == $swapAllocationDutyID) {
                    $chargingResult1 = $chargingResultVal1;
                }
            }
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = getenv('SMTP_HOST');
            $mail->Port = 25;
            $mail->setFrom('noreply@bbc.co.uk', 'Allocate');
            $mailAdd = $emailResult['Email'];
            $schedulingTeamName = $emailResult['schedulingTeamName'];
            $arrMailAdd = explode(";", (string) $mailAdd);
            for ($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
                $mail->addAddress($arrMailAdd[$intCount]);
            }
            $mail->Subject = "Charging on $chargingDate for $schedulingTeamName has been deleted because a Shiftleader swapped or unassigned a duty";
            require_once __DIR__ . '/../page-includes/allocations/edits/ChargingEmail.php';
            $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
            $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
            $htmlStr = '<style>' . file_get_contents(getenv('EMAIL_CSS')) . '</style>
            <body><img alt="Banner" src="cid:pobanner" /><br><br>' . $html;
            $mail->msgHTML($htmlStr);
            $mail->send();
        }
        return json_encode($result);
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    return false;
}

/**
 * This function is use to get current selected filter ID
 * @param $teamID This param contains the TeamID information
 * @return Array
 */
function GetUserCurrentFilter($teamID = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        $userNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
        $sql = "SELECT CurrentFilter,ProdDayCount,ProdStartDay,Dailyunallocated,Checkstartendshift FROM User_Web_Config WHERE Login='" . $userNetLogin . "'";
        if (!empty($teamID)) {
            $sql .= "	AND SchedulingTeamId=" . $teamID;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    return false;
}

/**
 * This function is use to get query condition for production view filter
 *
 * @param $filterID This param contains the FilterID information
 * @param $startWeekNumber This param contains the Start Week Number information
 * @param $endWeekNumber This param contains the End Week Number information
 * @param $teamId This param contains the TeamID information
 * @param $prodStartDate This param contains the Start Date information
 * @param $prodEndDate This param contains the End Date information
 *
 * @return Array
 */
function GetQueryCondition($filterID = 0, $startWeekNumber = 0, $endWeekNumber = 0, $teamId = 0, $prodStartDate = '', $prodEndDate = '')
{
    $setupObj = new classUserSetup();
    $arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
    try {
        $pdo = OpenDBLinkA7();
        $finalQueryStringCond = [];
        $dutyName = '';
        $additionalTeams = '';
        $dutyLabel = '';
        $checkAndCond = 0;
        $SortCodeFilter = '';
        if (is_numeric($filterID) && $filterID != '') {
            $checkAndCond = 1;
            $sql = "SELECT DutyFilter,DutyLabel,AdditionalTeams,SortCodeFilter FROM AutoPagesFilters (NOLOCK) WHERE ID=:filterID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':filterID', $filterID, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (isset($row['DutyFilter']) && $row['DutyFilter'] != null) {
                $dutyName = $row['DutyFilter'];
            }
            if (isset($row['DutyLabel']) && $row['DutyLabel'] != null) {
                $dutyLabel = $row['DutyLabel'];
            }
            if (isset($row['AdditionalTeams']) && $row['AdditionalTeams'] != null) {
                $expAdditionTeam = explode(",", (string) $row['AdditionalTeams']);
                $AddTeams = [];
                foreach ($expAdditionTeam as $TeamID) {
                    foreach ($arrUsersTeamdata['Teams'] as $keyTeamID => $arrTeam) {
                        if ((isset($arrTeam['HasDutiesView'])) && ($arrTeam['HasDutiesView'] == 1)) {
                            $AddTeams[] = $TeamID;
                        }
                    }
                }
                if (!empty($AddTeams)) {
                    $AddTeams = array_unique($AddTeams);
                    $additionalTeams = implode(",", $AddTeams);
                }
            }

            if (isset($row['SortCodeFilter']) && $row['SortCodeFilter'] != null) {
                $SortCodeFilter = $row['SortCodeFilter'];
            }
        }
        if (!is_numeric($filterID) && $filterID != '') {
            $explodeStrFilter = explode(',', (string) $filterID);
            if (count($explodeStrFilter) > 1) {
                $filterID = str_replace(',', '*', $filterID);
            } else {
                $filterID = $filterID . '*';
            }
            $dutyName = $filterID;
        }

        if ($SortCodeFilter != '') {
            if (isset($row['SortCodeFilter']) && str_contains($row['SortCodeFilter'], '&')) {
                $filterSymbol = '&';
            } else if (isset($row['SortCodeFilter']) && str_contains($row['SortCodeFilter'], ';')) {
                $filterSymbol = ';';
            } else if (isset($row['SortCodeFilter']) && str_contains($row['SortCodeFilter'], '!')) {
                $filterSymbol = '!';
            } else if (isset($row['SortCodeFilter']) && str_contains($row['SortCodeFilter'], '%')) {
                $filterSymbol = '%';
            } else {
                $filterSymbol = '*';
            }
            if (!empty(str_replace($filterSymbol, "", $SortCodeFilter))) {
                $filterSortCodeCond = createQueryCondition('spl.SortCode', $filterSymbol, $SortCodeFilter, 'ProductionView', $pdo);
                $finalQueryStringCond['SortCodeFilter'] = $filterSortCodeCond;
            }
        }

        if ($dutyName != '') {
            if (isset($row['DutyFilter']) && str_contains($row['DutyFilter'], '&')) {
                $filterSymbol = '&';
            } else if (isset($row['DutyFilter']) && str_contains($row['DutyFilter'], ';')) {
                $filterSymbol = ';';
            } else if (isset($row['DutyFilter']) && str_contains($row['DutyFilter'], '!')) {
                $filterSymbol = '!';
            } else if (isset($row['DutyFilter']) && str_contains($row['DutyFilter'], '%')) {
                $filterSymbol = '%';
            } else {
                $filterSymbol = '*';
            }
            if (!empty(str_replace($filterSymbol, "", $dutyName))) {
                $filterDutyCond = createQueryCondition('a.DutyName', $filterSymbol, $dutyName, 'ProductionView', $pdo);
                if ($checkAndCond == 1) {
                    $finalQueryStringCond['DutyFilter'] = getFinalConditionString(['optStr' => $filterSymbol, 'condStr' => str_replace($filterSymbol, ',', $dutyName), 'colName' => 'a.DutyName', 'screenName' => 'ProductionView', 'startWeekNumber' => $startWeekNumber, 'endWeekNumber' => $endWeekNumber, 'teamId' => $teamId, 'prodStartDate' => $prodStartDate, 'prodEndDate' => $prodEndDate, 'filterQueryCond' => $filterDutyCond], $pdo);
                } else {
                    $finalQueryStringCond['DutyFilter'] = $filterDutyCond;
                }
            }
        }
        if ($additionalTeams != '') {

            if (str_contains((string) $row['AdditionalTeams'], '&')) {
                $filterSymbol = '&';
            } else if (str_contains((string) $row['AdditionalTeams'], ';')) {
                $filterSymbol = ';';
            } else if (str_contains((string) $row['AdditionalTeams'], '!')) {
                $filterSymbol = '!';
            } else if (str_contains((string) $row['AdditionalTeams'], '%')) {
                $filterSymbol = '%';
            } else {
                $filterSymbol = '*';
            }
            if (!empty(str_replace($filterSymbol, "", $additionalTeams))) {
                $filterDutyCond = createQueryCondition('a.SchedulingTeamId', $filterSymbol, $additionalTeams, 'ProductionView', $pdo);
                if ($checkAndCond == 1) {
                    $finalQueryStringCond['additionalteam'] = getFinalConditionString(['optStr' => $filterSymbol, 'condStr' => str_replace($filterSymbol, ',', $additionalTeams), 'colName' => 'a.SchedulingTeamId', 'screenName' => 'ProductionView', 'startWeekNumber' => $startWeekNumber, 'endWeekNumber' => $endWeekNumber, 'teamId' => $teamId, 'prodStartDate' => $prodStartDate, 'prodEndDate' => $prodEndDate, 'filterQueryCond' => $filterDutyCond], $pdo);
                } else {
                    $finalQueryStringCond['additionalteam'] = $filterDutyCond;
                }
            }
        }
        if ($dutyLabel != '') {

            if (str_contains((string) $row['DutyLabel'], '&')) {
                $filterSymbol = '&';
            } else if (str_contains((string) $row['DutyLabel'], ';')) {
                $filterSymbol = ';';
            } else if (str_contains((string) $row['DutyLabel'], '!')) {
                $filterSymbol = '!';
            } else if (str_contains((string) $row['DutyLabel'], '%')) {
                $filterSymbol = '%';
            } else {
                $filterSymbol = '*';
            }
            if (!empty(str_replace($filterSymbol, "", $dutyLabel))) {
                $filterDutyCond = createQueryCondition('a.dutyProgramId', $filterSymbol, $dutyLabel, 'ProductionView', $pdo);
                if ($checkAndCond == 1) {
                    $finalQueryStringCond['dutylabel'] = getFinalConditionString(['optStr' => $filterSymbol, 'condStr' => str_replace($filterSymbol, ',', $dutyLabel), 'colName' => 'a.dutyProgramId', 'screenName' => 'ProductionView', 'startWeekNumber' => $startWeekNumber, 'endWeekNumber' => $endWeekNumber, 'teamId' => $teamId, 'prodStartDate' => $prodStartDate, 'prodEndDate' => $prodEndDate, 'filterQueryCond' => $filterDutyCond], $pdo);
                    $finalQueryStringCond['dutyFilterDaily'] = '';
                    if ((str_contains((string) $row['DutyLabel'], '&')) && ($row['DutyLabel'] != '')) {
                        $finalQueryStringCond['dutyFilterDaily'] = str_replace($filterSymbol, ',', $dutyLabel);
                    }
                    $finalQueryStringCond['dutylabel'] = $filterDutyCond;
                } else {
                    $finalQueryStringCond['dutylabel'] = $filterDutyCond;
                }
            }
        }
        return $finalQueryStringCond;
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    return false;
}

/**
 * This function is use to get filter name
 *
 * @param $filterID This param contains the FilterID information
 *
 * @return String
 */
function GetProductionFilterName($filterID = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        $filterName = '';

        $sql = "SELECT Description FROM AutoPagesFilters WHERE ID=:filterID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':filterID', $filterID, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($row['Description']) && $row['Description'] != null) {
            $filterName = $row['Description'];
        }
        return $filterName;
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    return false;
}

/**
 * This function is use to get filter name
 *
 * @param $weekNumber This param contains the Week Number information
 * @param $startDay This param contains the Start iDay information
 *
 * @return String
 */
function GetStartDateByBBCWeekNumber($weekNumber = 0, $startiDay = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        return getBBCWeekDetails($weekNumber, $startiDay, $pdo);
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    return false;
}

/**
 *  This function is used to get current set filter for the weekly screen
 *
 * @param $screenName This param contains the Screen Name Information
 * @param $teamId This param contains the TeamID Information
 *
 * @return Integer
 */
function GetCurrentSetWeeklyFilter($screenName = '', $teamId = 0)
{
    $pdo = OpenDBLinkA7();
    $weeklyFilterId = getCurrentSetFilter($screenName, $teamId, $pdo);
    if ($weeklyFilterId != '') {
        return $weeklyFilterId;
    } else {
        return 0;
    }
}

/**
 * Description : ReadMultiWeekAllocationsallocation
 * @param int $intStartWeekNumber, int $intEndWeekNumber,int $schedulingTeamid,int $intIgnoreRota,int   $intSortOrder ,int $myschdeullingPersonID,int $intColourWeek,string $filterQuery,string $filterQuery2,string $filterQuery3,string $filterOrderStr
 * return array
 */

function ReadMultiWeekAllocations($intStartWeekNumber = 0, $intEndWeekNumber = 0, $schedulingTeamid = 0, $intConfirmedDays = 0, $intMaskDays = 0, $intMaskType = 0, $intNoMask = 0, $intIgnoreRota = 0, $intSortOrder = 0, $myschdeullingPersonID = '', $intColourWeek = 0, $filterStr = '', $filterStr2 = '', $filterStr3 = '', $filterOrderStr = '', $selSchPersonId = '', $isShiftleader = 0, $isShifttoCheck = 0, $schedulingPersonId = 0, $intShowCanDo = 0, $weeklyViewPage = 0, $canViewAdditional = 1)
{
    $pdo = OpenDBLinkA7();
    $intMaskAfterUnixDate = strtotime("+$intMaskDays Days");
    $arrTextColours = GetShiftTextColours();
    $pNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $filterOrderStr = $intSortOrder;
    try {
        if ($isShifttoCheck == 0 && $intShowCanDo == 0) {
            $selSchPersonId = 0; //after filter this value will be set
            $sql = "exec [dbo].[usp_fetch_weeklyAllocationAndRota] @intweekStart = '" . $intStartWeekNumber . "', @intweekEnd = '" . $intEndWeekNumber . "', @filterTeamCond = '$filterStr2' , @OrderbyCondition = '" . $filterOrderStr . "', @scheduledPersonId = '" . $selSchPersonId . "', @pNetLogin = '" . $pNetLogin . "', @isShiftLeader = '" . $isShiftleader . "'";
        } else {
            $sql = "exec [dbo].[usp_fetch_weeklyshifts] @scheduledPersonId = '" . $schedulingPersonId . "', @StartWeek = '" . $intStartWeekNumber . "', @EndWeek = '" . $intEndWeekNumber . "'";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }

    //Sort
    usort($result, function ($a, $b) {
        return strcasecmp($a['DisplayName'], $b['DisplayName']);
    });
    switch ($intSortOrder) {
        case '':
        case 0:
        case 1;
            usort($result, function ($a, $b) {
                return strcasecmp($a['SurName'], $b['SurName']);
            });
            break;
        case 2;
            usort($result, function ($a, $b) {
                return strcasecmp($a['SortCode'], $b['SortCode']);
            });
            break;
    }
    $filterStrSchPerson = '';
    if ($filterStr != '') {
        $scheduledPersonIds = [];
        if (!empty($result)) {
            foreach ($result as $rowKey => $rowVal) {
                if (!in_array($rowVal['SchedulingPersonID'], $scheduledPersonIds)) {
                    $scheduledPersonIds[] = $rowVal['SchedulingPersonID'];
                }
            }
            $filterStrSchPerson = implode(',', $scheduledPersonIds);
            $filterStrSchPerson = 'sp.ScheduledPersonID IN(' . $filterStrSchPerson . ')';
        } else {
            $filterStrSchPerson = $filterStr3;
        }
    }

    if (!empty($result)) {
        $arrAllocations = [];
        $scheduledPeople = [];
        $hideScheduledPeople = [];
        foreach ($result as $row) {
            if (!in_array($row["SchedulingPersonID"], $scheduledPeople)) {
                $scheduledPeople[] = $row["SchedulingPersonID"];
            }
            $strCurrentStaffNumber = $row["StaffNumber"] ?? '';
            $strCurrentWeek = $row["WeekNumber"];
            $strCurrentDay = $row["DOTW"];
            $schedulingPersonID = $row["SchedulingPersonID"];
            $arrAllocations[$schedulingPersonID]["SchedulingPersonID"] = $schedulingPersonID;
            $arrAllocations[$schedulingPersonID]["StaffNumber"] = $strCurrentStaffNumber;
            $arrAllocations[$schedulingPersonID]["Login"] = $row["NetLogin"];
            $arrAllocations[$schedulingPersonID]["TeamID"] = $row["StaffTeamID"];
            $arrAllocations[$schedulingPersonID]["IsHomeTeam"] = $row["IsHomeTeam"];
            $arrAllocations[$schedulingPersonID]["CostCode"] = $row["CostCode"];
            $arrAllocations[$schedulingPersonID]["DisplayInViewScreenDOTW"][$strCurrentDay] = isset($arrAllocations[$schedulingPersonID]["DisplayInViewScreenDOTW"][$strCurrentDay]) && $arrAllocations[$schedulingPersonID]["DisplayInViewScreenDOTW"][$strCurrentDay] == 1 ? 1 : (int)$row['DisplayInViewScreen'];

            //Hide additional team record if Do not display view screen checked
            if ($canViewAdditional == 0 && in_array((int)$row["IsHomeTeam"], [0, 2]) && $row['DisplayInViewScreen'] == 0) {
                $row["DutyName"] = '-';
                $hideScheduledPeople[] = $schedulingPersonID;
            }
            //Future home team default text
            if ((int)$row["IsHomeTeam"] == 2 && ($row["DutyName"] == 'U' || $row["DutyName"] == '-')) {
                $row["DutyName"] = 'New Joiner';
            }


            if ($myschdeullingPersonID == $schedulingTeamid) {
                $arrAllocations[$schedulingPersonID]["FullName"] = $row["DisplayName"];
                if (empty($row["SortCode"])) {
                    $arrAllocations[$schedulingPersonID]["SortCode"] = $row["SortCode"];
                }
                if ($row["DutyName"] != 'U') {
                    $arrAllocations[$schedulingPersonID]["SortCode"] = $row["SortCode"];
                }
            } else {
                if (!isset($arrAllocations[$schedulingPersonID]["DisplayName"])) {
                    $arrAllocations[$schedulingPersonID]["FullName"] = $row["DisplayName"];
                }
                if (!isset($arrAllocations[$schedulingPersonID]["SortCode"])) {
                    if (empty($row["SortCode"])) {
                        $arrAllocations[$schedulingPersonID]["SortCode"] = $row["SortCode"];
                    }
                    if ($row["DutyName"] != 'U') {
                        $arrAllocations[$schedulingPersonID]["SortCode"] = $row["SortCode"];
                    }
                }
            }
            $arrAllocations[$schedulingPersonID]["email"] = $row["Email1"];
            $arrAllocations[$schedulingPersonID]["StaffTextColour"] = $row["StaffTextColour"];
            $arrAllocations[$schedulingPersonID]["StaffBackColour"] = $row["StaffBackColour"];
            $strDutyname = $row["DutyName"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["Dutyid"] = $row["ID"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["AllocationID"] = $row["AllocationID"] ?? '';
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["isEditable"] = $row["isEditable"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["IsTemplate"] = $row["IsTemplate"];

            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["Duty"] = $strDutyname;
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["Jobs"] = json_decode($row["jobs"] ?? '[]', true);
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["display_priority"] = $row['display_priority'];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["isShifttoCheckDuty"] = $isShifttoCheck;
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["LeaveType"] = $row["LeaveType"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]['DisplayInViewScreen'] = $row['DisplayInViewScreen'];
            // Duty Comments?
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["DutyComments"] = $row["DutyCommentsFlag"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["PersonComments"] = $row["PersonCommentsFlag"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["MannualOThours"] = $row["MannualOThours"] ?? '';
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["StartTimerowvalue"] = $row["StartTime"];
            if ($row["EndTime"] > 86400) {
                $row['EndTime'] = $row['EndTime'] - 86400;
            }
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["EndTimerowvalue"] = $row["EndTime"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["AllocationsDutyID"] = $row["AllocationsDutyID"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["AllocationsSPID"] = $row["AllocationsSPID"];
            // End Comments

            if (($row["StartTime"] > 0) && ($row['EndTime'] > 0)) {
                $duration = ($row['EndTime'] - $row["StartTime"]);
            } else {
                if ($strDutyname != '-') {
                    $duration = $row["Duration"];
                } else {
                    $duration = 0;
                }
            }
            $isworking = 1;
            if (isset($arrTextColours)) {
                $isworking = GetIsNotWorking($strDutyname, $arrTextColours);
            }
            $trimstrDutyName = trim(strtoupper((string) $strDutyname));
            if (!empty($trimstrDutyName)) {
                if ($row['display_priority'] == 1) {
                    if (($trimstrDutyName != 'U') && ($trimstrDutyName != '-') && ($trimstrDutyName != '--') && ($row['Duration'] != 0) && ($trimstrDutyName != 'SICK') && ($trimstrDutyName != 'LEAVE')) {
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#' . $row['BackColour'];
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                    } else if (($trimstrDutyName == 'OFF LEAVE') || ($trimstrDutyName == 'LEAVE')) {
                        // Set default green colour for Leave and OFF Leave
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#DEDEDE';
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                    } else if (($trimstrDutyName == 'U-SICK') || ($trimstrDutyName == 'SICK') || ($trimstrDutyName == '-SICK')) {
                        // Set default mustard yellow colour for Sick and U-Sick
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#DEDEDE';
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                    } else {
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#DEDEDE';
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#330066';
                    }
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["LeaveFontColour"] = '#' . ($row['LeaveFontColour'] ?? '');
                } else {
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#' . $row['BackColour'];
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["LeaveFontColour"] = '#' . ($row['LeaveFontColour'] ?? '');
                }
            } else if ((strtolower((string) $row['LeaveType']) == 'leave' || strtolower((string) $row['LeaveType']) == 'off leave')) {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#D8D8FF';
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                $row["Duration"] = 0;
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#EFEFEF';
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#330066';
                $row["Duration"] = 0;
            }

            if (($row["StartTime"] >= 0) && ($row['EndTime'] >= 0)) {
                $intstartHour = intval($row["StartTime"] / 3600);
                $intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
                $intstartMinute = intval(($row["StartTime"] % 3600) / 60);
                $intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
                $starttime = $row["StartTime"];
                $starttime = $intstartHour . ":" . $intstartMinute;
                //When end date comes as 86400 (24hrs) make it as 0                
                $row['EndTime'] = $row['EndTime'] == 86400 ? 0 : $row['EndTime'];
                $intendHour = intval($row['EndTime'] / 3600);
                $intendHour = strlen(trim($intendHour)) == 1 ? "0" . $intendHour : $intendHour;
                $intendMinute = intval(($row['EndTime'] % 3600) / 60);
                $intendMinute = strlen(trim($intendMinute)) == 1 ? "0" . $intendMinute : $intendMinute;
                $endtime = $intendHour . ":" . $intendMinute;
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["StartTime"] = $starttime;
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["EndTime"] = $endtime;
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["StartTime"] = "00:00";
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["EndTime"] = "00:00";
            }
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["StartTimeSec"] = $row["StartTime"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["EndTimeSec"] = $row['EndTime'];

            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["Duration"] = $duration;
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["edited"] = $row["isEdited"];

            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["isworking"] = $isworking;
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BaseCode"] = $row["BaseCode"] ?? '';
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["TeamID"] = $row["SchedulingTeamId"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["schedulingTeamName"] = $row["schedulingTeamName"] ?? '';

            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["DutyLabels"] = [
                $row['dutyProgramId'] ?? '',
                $row['DutyProgramId2'] ?? '',
                $row['DutyProgramId3'] ?? '',
                $row['DutyProgramId4'] ?? '',
                $row['DutyProgramId5'] ?? '',
                $row['DutyProgramId6'] ?? ''
            ];

            $thisdate = datefromweek($strCurrentWeek, $strCurrentDay);
            $CellClass = SetDutyClass($thisdate, $intMaskDays, $intMaskType, $isworking);
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["CellClass"] = $CellClass;
            if (is_null($row["active"])) {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["signin"] = 0;
            } else {
                if ($row['StartTime'] == $row["SignInStartTime"] && $row['EndTime'] == $row["SignInEndTime"]) {
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["signin"] = $row["active"];
                } else {
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["signin"] = 2;
                }
            }

            if (is_null($row["inBuilding"])) {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["inbuilding"] = 0;
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["inbuilding"] = $row["inBuilding"];
            }

            // Any financial reward?   MarkedOvertime ActingGrade
            if (@$row["MarkedOvertime"] == 0 && @$row["ActingGrade"] == 0 && @$row["MarkedPTExtraDay"] == 0) {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["financial"] = 0;
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["financial"] = 1;
            }
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["pdlStartTime"] = (int) ($row["LeaveStartTime"] ?? 0);
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["pdlEndTime"] = (int) ($row["LeaveEndTime"] ?? 0);
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["IsPublished"] = $row["IsPublished"] ?? 0;
        }
        //Hide Additional team records in view screen based on role
        foreach ($hideScheduledPeople as $hideScheduledPeopleId) {
            if (isset($arrAllocations[$hideScheduledPeopleId]) && array_sum($arrAllocations[$hideScheduledPeopleId]["DisplayInViewScreenDOTW"]) == 0) { // If None of additional team is not visible hide the record
                unset($arrAllocations[$hideScheduledPeopleId]);
                foreach ($scheduledPeople as $key => $value) {
                    if ($value == $hideScheduledPeopleId) {
                        unset($scheduledPeople[$key]);
                    }
                }
            }
        }
    } else {
        $arrAllocations = [];
    }
    if (!empty($arrAllocations)) {
        if ($isShifttoCheck == 1) {
            return MakeShiftsAllocations($intStartWeekNumber, $intEndWeekNumber, $intMaskAfterUnixDate, $intNoMask, $intMaskType, $scheduledPeople, $arrAllocations);
        } else {
            return MakeRotaAllocations($intStartWeekNumber, $intEndWeekNumber, $intMaskAfterUnixDate, $intNoMask, $intMaskType, $scheduledPeople, $arrAllocations);
        }
    } else {
        return [];
    }
}

/**
 * Description :MakeRotaAllocations
 * @param int $CurrentWeekNo ,
 * @param int $intEndWeek ,
 * @param int $intMaskAfterUnixDate int ,
 * @param int $intNoMask ,
 * @param int $intMaskType ,
 * @param array $scheduledPeople ,
 * @param arry $arrRota ,
 * return array
 *
 */

function MakeRotaAllocations(int $CurrentWeekNo = 0, int $intEndWeek = 0, $intMaskAfterUnixDate = 0, $intNoMask = 0, $intMaskType = 0, array $scheduledPeople = [], $arrRota = '')
{
    $arrAllocations = [];
    $commonObj = new classCommonDBFunctions();
    foreach ($scheduledPeople as $row) {
        $scheduledPersonId = $row;
        $arrAllocations[$scheduledPersonId]["SchedulingPersonID"] = $scheduledPersonId;
        $arrAllocations[$scheduledPersonId]["StaffNumber"] = $arrRota[$scheduledPersonId]["StaffNumber"] ?? '';
        $arrAllocations[$scheduledPersonId]["Login"] = $arrRota[$scheduledPersonId]["Login"] ?? '';
        $arrAllocations[$scheduledPersonId]["TeamID"] = $arrRota[$scheduledPersonId]["TeamID"];
        $arrAllocations[$scheduledPersonId]["FullName"] = $arrRota[$scheduledPersonId]["FullName"] ?? 'None';
        $arrAllocations[$scheduledPersonId]["SortCode"] = $arrRota[$scheduledPersonId]["SortCode"] ?? '';
        $arrAllocations[$scheduledPersonId]["CostCode"] = $arrRota[$scheduledPersonId]["CostCode"] ?? '';
        $arrAllocations[$scheduledPersonId]["email"] = @$arrRota[$scheduledPersonId]["email"] ?? '';
        $arrAllocations[$scheduledPersonId]["IsHomeTeam"] = $arrRota[$scheduledPersonId]["IsHomeTeam"] ?? 0;
        $arrAllocations[$scheduledPersonId]["StaffTextColour"] = @$arrRota[$scheduledPersonId]["StaffTextColour"] ?? '#000000';
        $arrAllocations[$scheduledPersonId]["StaffBackColour"] = @$arrRota[$scheduledPersonId]['StaffBackColour'] ?? '#dddddd';
        $arrAllocations[$scheduledPersonId]["DutyLabels"] = $arrRota["DutyLabels"] ?? [];

        // Now read this into the dates.....
        $intCurWeek = $CurrentWeekNo;
        while ($intCurWeek <= $intEndWeek) {
            for ($i = 0; $i <= 6; $i++) {
                $strDutyname = $arrRota[$scheduledPersonId][$intCurWeek][$i] ?? '-';
                $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["Duty"] = $strDutyname;
                if ($strDutyname == '-') {
                    $arrAllocations[$scheduledPersonId][$intCurWeek][$i]['BackColour'] = '#D8D8FF';
                    $arrAllocations[$scheduledPersonId][$intCurWeek][$i]['FontColour'] = '#57575c';
                }
            }
            $intCurWeek = addweeks($intCurWeek, 1);
        } //while Close
    }
    return $arrAllocations;
}

/**
 * Description :MakeShiftsAllocations
 * @param int $CurrentWeekNo ,
 * @param int $intEndWeek ,
 * @param int $intMaskAfterUnixDate int ,
 * @param int $intNoMask ,
 * @param int $intMaskType ,
 * @param array $scheduledPeople ,
 * @param arry $arrRota ,
 * return array
 *
 */

function MakeShiftsAllocations(int $CurrentWeekNo = 0, int $intEndWeek = 0, $intMaskAfterUnixDate = 0, $intNoMask = 0, $intMaskType = 0, array $scheduledPeople = [], $arrRota = '')
{
    $arrAllocations = [];
    $commonObj = new classCommonDBFunctions();
    foreach ($scheduledPeople as $row) {
        $scheduledPersonId = $row;
        $arrAllocations[$scheduledPersonId]["SchedulingPersonID"] = $scheduledPersonId;
        $arrAllocations[$scheduledPersonId]["StaffNumber"] = $arrRota[$scheduledPersonId]["StaffNumber"] ?? '';
        $arrAllocations[$scheduledPersonId]["Login"] = $arrRota[$scheduledPersonId]["Login"] ?? '';
        $arrAllocations[$scheduledPersonId]["TeamID"] = $arrRota[$scheduledPersonId]["TeamID"];
        $arrAllocations[$scheduledPersonId]["FullName"] = $arrRota[$scheduledPersonId]["FullName"] ?? 'None';
        $arrAllocations[$scheduledPersonId]["SortCode"] = $arrRota[$scheduledPersonId]["SortCode"] ?? '';
        $arrAllocations[$scheduledPersonId]["email"] = @$arrRota[$scheduledPersonId]["email"] ?? '';
        $arrAllocations[$scheduledPersonId]["IsHomeTeam"] = $arrRota[$scheduledPersonId]["IsHomeTeam"] ?? 0;
        $arrAllocations[$scheduledPersonId]["StaffTextColour"] = @$arrRota[$scheduledPersonId]["StaffTextColour"] ?? '#000000';
        $arrAllocations[$scheduledPersonId]["StaffBackColour"] = @$arrRota[$scheduledPersonId]['StaffBackColour'] ?? '#dddddd';

        // Now read this into the dates.....
        $intCurWeek = $CurrentWeekNo;
        while ($intCurWeek <= $intEndWeek) {
            for ($i = 0; $i <= 6; $i++) {
                $strDutyname = $arrRota[$scheduledPersonId][$intCurWeek][$i] ?? '';
                $arrAllocations[$scheduledPersonId][$intCurWeek][$i]["Duty"] = $strDutyname;
                if ($strDutyname == '') {
                    $arrAllocations[$scheduledPersonId][$intCurWeek][$i]['BackColour'] = '#D8D8FF';
                    $arrAllocations[$scheduledPersonId][$intCurWeek][$i]['FontColour'] = '#57575c';
                }
            }
            $intCurWeek = addweeks($intCurWeek, 1);
        } //while Close
    }
    return $arrAllocations;
}

/**
 *  This function is used to get current set filter for the multi week screen
 *
 * @param $screenName This param contains the Screen Name Information
 * @param $teamId This param contains the TeamID Information
 *
 * @return Integer
 */
function GetCurrentSetMultiWeekFilter($screenName = '', $teamId = 0)
{
    $pdo = OpenDBLinkA7();
    $weeklyFilterId = getCurrentSetFilter($screenName, $teamId, $pdo);
    if ($weeklyFilterId != '') {
        return $weeklyFilterId;
    } else {
        return 0;
    }
}

/**
 * Get the  Allocations AND ROTA for Leave module
 * @param  $intStartWeek contains startweek
 * @param  $intEndWeek contains endweek
 * @param  $schedulingPersonId contains schedulingpersonId
 * @return array $arrAllocations
 */
function ReadAllocationsAndRotaLeave($intStartWeek = 0, $intEndWeek = 0, $schedulingPersonId = 0)
{
    global $intViewYears;
    $pdo = OpenDBLinkA7();
    try {
        $strQuery = "exec [dbo].[usp_fetch_AllocationAndRota_leave] ?,?,?";
        $stmt = $pdo->prepare($strQuery);
        // The parameters
        $stmt->bindParam(1, $intStartWeek, PDO::PARAM_INT);
        $stmt->bindParam(2, $intEndWeek, PDO::PARAM_INT);
        $stmt->bindParam(3, $schedulingPersonId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }

    if (!empty($result)) {
        foreach ($result as $row) {
            $intCurrDep = $row['SchedulingTeamId'];
            $strCurrentWeek = $row['WeekNumber'];
            $intCurrentDay = $row['DOTW'];
            $uThisDate = strtotime(datefromweek($strCurrentWeek, $intCurrentDay));
            if ($uThisDate > strtotime("-$intViewYears years")) {
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]['Duty'] = $row['DutyName'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]['MarkedOvertime'] = $row['MarkedOvertime'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]['MarkedSickness'] = $row['MarkedSickness'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]['ChargingId'] = !empty($row["ChargingId"]) ? 1 : 0;
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["Duration"] = number_format((float) (($row['Duration'] - $row['dutyBreakTime']) / 3600), 2, '.', '');
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["DutyStartTime"] = ($row['Duration'] == 0) ? 0 : $row['StartTime'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["DutyEndTime"] = ($row['Duration'] == 0) ? 0 : $row['EndTime'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["LeaveStartTime"] = $row['LeaveStartTime'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["LeaveEndTime"] = $row['LeaveEndTime'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["IsPartDayLeaveApplied"] = $row['IsPartDayLeaveApplied'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["LeaveID"] = $row['LeaveID'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["IsLeaveApproved"] = $row['IsLeaveApproved'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["IsPartDayAllowed"] = $row['IsPartDayAllowed'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["DutyDurationExcBreak"] = $row['Duration'] - $row['dutyBreakTime'];
                $arrAllocations['Weeks'][$strCurrentWeek][$intCurrentDay]["DutyID"] = $row['ID'];
            }
        }
    }

    if (isset($arrAllocations)) {
        return ($arrAllocations);
    } else {
        return [];
    }
}

/**
 * Function Name : ReadAllocationANDRotaByShedulledPerson
 * @param : $arrScheduledPersons array,$intWeekNumber int,$intAdmin int
 * return []
 *
 */

function ReadAllocationANDRotaByShedulledPerson($arrScheduledPersons = '', $intWeekNumber = 0, $intAdmin = 0)
{
    $pdo = OpenDBLinkA7();
    $strScheduledPersons = implode(",", $arrScheduledPersons);
    $arrAllocations = [];
    try {
        $strQuery = "exec [dbo].[usp_fetch_AllocationAndRota_request] ?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intWeekNumber, PDO::PARAM_INT);
        $stmt->bindParam(2, $intWeekNumber, PDO::PARAM_INT);
        $stmt->bindParam(3, $intAdmin, PDO::PARAM_INT);
        $stmt->bindParam(4, $strScheduledPersons, PDO::PARAM_STR);
        $stmt->execute();
        $rsRota = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }
    if (!empty($rsRota)) {
        foreach ($rsRota as $row) {
            $UsersData = GetFullNameFromScheduledPersonId($row["SchedulingPersonID"]);
            if (!empty($UsersData)) {
                $strLogin = strtolower((string) $UsersData['NetLogin']);
            } else {
                $strLogin = '';
            }
            $arrRota[$strLogin]["WeekNumber"] = $row["WeekNumber"];
            if ($row["DOTW"] == 0) {
                $arrRota[$strLogin][$row["WeekNumber"]][0] = $row["DutyName"];
            }
            if ($row["DOTW"] == 1) {
                $arrRota[$strLogin][$row["WeekNumber"]][1] = $row["DutyName"];
            }
            if ($row["DOTW"] == 2) {
                $arrRota[$strLogin][$row["WeekNumber"]][2] = $row["DutyName"];
            }
            if ($row["DOTW"] == 3) {
                $arrRota[$strLogin][$row["WeekNumber"]][3] = $row["DutyName"];
            }
            if ($row["DOTW"] == 4) {
                $arrRota[$strLogin][$row["WeekNumber"]][4] = $row["DutyName"];
            }
            if ($row["DOTW"] == 5) {
                $arrRota[$strLogin][$row["WeekNumber"]][5] = $row["DutyName"];
            }
            if ($row["DOTW"] == 6) {
                $arrRota[$strLogin][$row["WeekNumber"]][6] = $row["DutyName"];
            }
        } //Foreach Close
    }
    if (isset($arrRota)) {
        foreach ($arrRota as $strLogin => $arrDepRota) {
            $intWeekOfRota = $arrRota[$strLogin]["WeekNumber"];
            for ($i = 0; $i <= 6; $i++) {
                if (isset($arrRota[$strLogin][$intWeekOfRota][$i])) {
                    $strDutyname = $arrRota[$strLogin][$intWeekOfRota][$i];
                } else {
                    $strDutyname = 'Unallocated';
                }
                $arrAllocations[$strLogin][$i] = $strDutyname;
            }
        }
    }
    return $arrAllocations;
}
/**
 * This function is use to get filter details
 *
 * @param $filterID This param contains the FilterID information
 *
 * @return String
 */
function GetProductionFilterDetails($filterID = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        $filterName = '';

        $sql = "SELECT AndMatch FROM AutoPagesFilters (NOLOCK) WHERE ID=:filterID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':filterID', $filterID, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    return false;
}
/**
 * Description : ReadAllocationsForXmas
 * @param str $intStartWeekNumber, str $intEndWeekNumber,str $varScheduledpersonIDS
 * return array
 */
function ReadAllocationsForXmas($intTeamID, $currentYear)
{
    $commonObj = new classCommonDBFunctions();
    $pdo = OpenDBLinkA7();
    try {
        $sql = "exec [dbo].[usp_fetch_XmasAllocationAndRota] ?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
        $stmt->bindParam(2, $currentYear, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }

    if (!empty($result)) {
        $arrAllocations = [];
        $scheduledPeople = [];
        foreach ($result as $row) {
            $strCurrentWeek = $row["WeekNumber"];
            $strCurrentDay = $row["DOTW"];
            $schedulingPersonID = $row["SchedulingPersonID"];
            $strDutyname = $row["DutyName"];

            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay]['Duty'][$row["IsTemplate"]]["Duty"] = $strDutyname;
            if (($row["StartTime"] > 0) && ($row['EndTime'] > 0)) {
                $duration = ($row['EndTime'] - $row["StartTime"]);
            } else {
                if ($strDutyname != '-') {
                    $duration = $row["Duration"];
                } else {
                    $duration = 0;
                }
            }

            if (($row["StartTime"] >= 0) && ($row['EndTime'] >= 0)) {
                $intstartHour = intval($row["StartTime"] / 3600);
                $intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
                $intstartMinute = intval(($row["StartTime"] % 3600) / 60);
                $intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
                $starttime = $row["StartTime"];
                $starttime = $intstartHour . ":" . $intstartMinute;
                if ($row["EndTime"] > 86400) {
                    $row['EndTime'] = $row['EndTime'] - 86400;
                }
                $intendHour = intval($row['EndTime'] / 3600);
                $intendHour = strlen(trim($intendHour)) == 1 ? "0" . $intendHour : $intendHour;
                $intendMinute = intval(($row['EndTime'] % 3600) / 60);
                $intendMinute = strlen(trim($intendMinute)) == 1 ? "0" . $intendMinute : $intendMinute;
                $endtime = $intendHour . ":" . $intendMinute;
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay]['Duty'][$row["IsTemplate"]]["StartTime"] = $starttime;
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay]['Duty'][$row["IsTemplate"]]["EndTime"] = $endtime;
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["Duration"] = $duration;
            }
        }
    } else {
        $arrAllocations = [];
    }

    return $arrAllocations;
}
/**
 * Description : get user Allocations And Rota
 * return array
 */

function leaveRequestAllocationsAndRota($intStartWeek = 0, $intEndWeek = 0, $schedulingPersonId = 0, $strUserLogin = '')
{
    $pdo = OpenDBLinkA7();
    try {

        $strQuery = "exec [dbo].[usp_fetch_leaverequestAllocationAndRota] ?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intStartWeek, PDO::PARAM_INT);
        $stmt->bindParam(2, $intEndWeek, PDO::PARAM_INT);
        $stmt->bindParam(3, $schedulingPersonId, PDO::PARAM_INT);
        $stmt->bindParam(4, $strUserLogin, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('DB error', (array) $e);
    }
    $arrAllocations = [];
    if (!empty($result)) {
        foreach ($result as $row) {
            $strCurrentWeek = $row['WeekNumber'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['Duty'] = $row["DutyName"] ?? '';
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['Duration'] = isset($row["DutyName"]) ? $row["Duration"] : '';
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['StartTime'] = isset($row["DutyName"]) ? $row["StartTime"] : '';
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['EndTime'] = isset($row["DutyName"]) ? $row["EndTime"] : '';
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['AllocationID'] = $row["AllocationID"] ?? '';
        }
    }
    return $arrAllocations;
}

/**
 *  This function is used to get filter details
 *
 * @param $filterId This param contains the Filter ID Information
 * @param $paramTeamId This param contains the Team ID Information
 * @param $screenName This param contains the Screen Name Information
 * @param $sessNetLoginId This param contains the Net Login ID Information
 *
 * @return Boolean
 */
function GetFilterDetails($filterId = 0, $paramTeamId = 0, $screenName = '', $sessNetLoginId = '')
{
    $pdo = OpenDBLinkA7();
    $strQuery = "SELECT SchedulingTeamId FROM AutoPagesFilters WHERE ID=" . $filterId;
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $filterTeamId = 0;
    if (!empty($result)) {
        $filterTeamId = (int) $result['SchedulingTeamId'];
    }
    if (!empty($result) && ($paramTeamId != $filterTeamId)) {
        switch ($screenName) {
            case 'ViewDaily':
                $strUpdQuery = "UPDATE User_Web_Config SET DailyFilter=NULL WHERE Login = '" . $sessNetLoginId . "' AND SchedulingTeamId = " . $paramTeamId;
                break;
            case 'ViewWeekly':
                $strUpdQuery = "UPDATE User_Web_Config SET WeeklyFilter=NULL WHERE Login = '" . $sessNetLoginId . "' AND SchedulingTeamId = " . $paramTeamId;
                break;
            case 'MultiWeek':
                $strUpdQuery = "UPDATE User_Web_Config SET MultiWeekFilter=NULL WHERE Login = '" . $sessNetLoginId . "' AND SchedulingTeamId = " . $paramTeamId;
                break;
            case 'EditWeeklyRota':
                $strUpdQuery = "UPDATE User_Web_Config SET EditWeeklyRota=NULL WHERE Login = '" . $sessNetLoginId . "' AND SchedulingTeamId = " . $paramTeamId;
                break;
        }
        $stmtUpd = $pdo->prepare($strUpdQuery);
        if ($stmtUpd->execute()) {
            return true;
        } else {
            return false;
        }
    } else {
        // No data found.
        return false;
    }
    return false;
}

function ReadEditWeekRotaAllocations($intStartWeekNumber = 0, $intEndWeekNumber = 0, $schedulingTeamid = 0,  $intMaskDays = 0, $intMaskType = 0, $intNoMask = 0, $filterStr = '', $filterStr2 = '', $filterOrderStr = '', $schedulingPersonId = 0, $skillFilterDaily = null, $dutyFilterDaily = null, $mastMiscId = null, $isShifttoCheck = 0, $rotaData = [])
{
    if ((!empty($rotaData) && count($rotaData) == 1) || $rotaData == null) {
        global $request;
        $createWeekService = new CreateWeekService();
        $checkIfWeekExists = $createWeekService->checkIfWeekExists($request);
        $rotaData = $checkIfWeekExists["rota"];
    }

    if (!empty($rotaData)) {
        $arrAllocations = [];
        $scheduledPeople = [];
        $rotaData = $rotaData[0];
        foreach ($rotaData as $row) {
            if (!in_array($row["SchedulingPersonID"], $scheduledPeople)) {
                $scheduledPeople[] = $row["SchedulingPersonID"];
            }
            $strCurrentStaffNumber = $row["staffnumber"] ?? '';
            $strCurrentWeek = $row["WeekNumber"];
            $strCurrentDay = $row["DOTW"];
            $schedulingPersonID = $row["SchedulingPersonID"];
            $arrAllocations[$schedulingPersonID]["SchedulingPersonID"] = $schedulingPersonID;
            $arrAllocations[$schedulingPersonID]["StaffNumber"] = $strCurrentStaffNumber;
            $arrAllocations[$schedulingPersonID]["Login"] = $row["NetLogin"];
            $arrAllocations[$schedulingPersonID]["TeamID"] = $row["StaffTeamID"];
            $arrAllocations[$schedulingPersonID]["IsHomeTeam"] = $row["IsHomeTeam"];
            if (!isset($arrAllocations[$schedulingPersonID]["DisplayName"])) {
                $arrAllocations[$schedulingPersonID]["FullName"] = $row["DisplayName"];
            }
            if (!isset($arrAllocations[$schedulingPersonID]["SortCode"])) {
                $arrAllocations[$schedulingPersonID]["SortCode"] = $row["SortCode"];
            }
            $arrAllocations[$schedulingPersonID]["CostCode"] = $row["CostCode"];
            $arrAllocations[$schedulingPersonID]["email"] = $row["Email1"];
            $arrAllocations[$schedulingPersonID]["StaffTextColour"] = $row["StaffTextColour"];
            $arrAllocations[$schedulingPersonID]["StaffBackColour"] = $row["StaffBackColour"];
            $strDutyname = $row["DutyName"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["Dutyid"] = $row["ID"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["AllocationID"] = $row["AllocationID"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["isEditable"] = $row["isEditable"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["IsTemplate"] = $row["IsTemplate"];

            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["Duty"] = $strDutyname;
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["display_priority"] = $row['display_priority'];

            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["LeaveType"] = $row["LeaveType"];
            // Duty Comments?
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["DutyComments"] = $row["DutyCommentsFlag"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["PersonComments"] = $row["PersonCommentsFlag"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["MannualOThours"] = $row["MannualOThours"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["StartTimerowvalue"] = $row["StartTime"];
            if ($row["EndTime"] >= 86400) {
                $row['EndTime'] = $row['EndTime'] - 86400;
            }
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["EndTimerowvalue"] = $row["EndTime"];
            // End Comments

            if (($row["StartTime"] > 0) && ($row['EndTime'] > 0)) {
                $duration = ($row['EndTime'] - $row["StartTime"]);
            } else {
                if ($strDutyname != '-') {
                    $duration = $row["Duration"];
                } else {
                    $duration = 0;
                }
            }
            $isworking = 1;
            if (isset($arrTextColours)) {
                $isworking = GetIsNotWorking($strDutyname, $arrTextColours);
            }
            $trimstrDutyName = trim(strtoupper((string) $strDutyname));
            if (!empty($trimstrDutyName)) {
                if ($row['display_priority'] == 1) {
                    if (($trimstrDutyName != 'U') && ($trimstrDutyName != '-') && ($trimstrDutyName != '--') && ($row['Duration'] != 0) && ($trimstrDutyName != 'SICK') && ($trimstrDutyName != 'LEAVE')) {
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#' . $row['BackColour'];
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                    } else if (($trimstrDutyName == 'OFF LEAVE') || ($trimstrDutyName == 'LEAVE')) {
                        // Set default green colour for Leave and OFF Leave
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#DEDEDE';
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                    } else if (($trimstrDutyName == 'U-SICK') || ($trimstrDutyName == 'SICK') || ($trimstrDutyName == '-SICK')) {
                        // Set default mustard yellow colour for Sick and U-Sick
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#DEDEDE';
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                    } else {
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#D8D8FF';
                        $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#330066';
                    }
                } else {
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#' . $row['BackColour'];
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#' . $row['FontColour'];
                }
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BackColour"] = '#EFEFEF';
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["FontColour"] = '#330066';
                $row["Duration"] = 0;
            }

            if (($row["StartTime"] >= 0) && ($row['EndTime'] >= 0)) {
                $intstartHour = intval($row["StartTime"] / 3600);
                $intstartHour = strlen(trim($intstartHour)) == 1 ? "0" . $intstartHour : $intstartHour;
                $intstartMinute = intval(($row["StartTime"] % 3600) / 60);
                $intstartMinute = strlen(trim($intstartMinute)) == 1 ? "0" . $intstartMinute : $intstartMinute;
                $starttime = $row["StartTime"];
                $starttime = $intstartHour . ":" . $intstartMinute;
                $intendHour = intval($row['EndTime'] / 3600);
                $intendHour = strlen(trim($intendHour)) == 1 ? "0" . $intendHour : $intendHour;
                $intendMinute = intval(($row['EndTime'] % 3600) / 60);
                $intendMinute = strlen(trim($intendMinute)) == 1 ? "0" . $intendMinute : $intendMinute;
                $endtime = $intendHour . ":" . $intendMinute;
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["StartTime"] = $starttime;
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["EndTime"] = $endtime;
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["StartTime"] = "00:00";
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["EndTime"] = "00:00";
            }

            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["Duration"] = $duration;
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["edited"] = $row["isEdited"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["DutyLabels"] = [
                $row['dutyProgramId'] ?? '',
                $row['dutyProgramId2'] ?? '',
                $row['dutyProgramId3'] ?? '',
                $row['dutyProgramId4'] ?? '',
                $row['dutyProgramId5'] ?? '',
                $row['dutyProgramId6'] ?? ''
            ];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["isworking"] = $isworking;
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["BaseCode"] = $row["BaseCode"] ?? '';
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["TeamID"] = $row["SchedulingTeamId"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["schedulingTeamName"] = $row["schedulingTeamName"];
            $thisdate = datefromweek($strCurrentWeek, $strCurrentDay);
            $CellClass = SetDutyClass($thisdate, $intMaskDays, $intMaskType, $isworking);
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["CellClass"] = $CellClass;
            if (is_null($row["active"])) {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["signin"] = 0;
            } else {
                if ($row['StartTime'] == $row["SignInStartTime"] && $row['EndTime'] == $row["SignInEndTime"]) {
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["signin"] = $row["active"];
                } else {
                    $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["signin"] = 2;
                }
            }

            if (is_null($row["inBuilding"])) {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["inbuilding"] = 0;
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["inbuilding"] = $row["inBuilding"];
            }

            // Any financial reward?   MarkedOvertime ActingGrade
            if (@$row["MarkedOvertime"] == 0 && @$row["ActingGrade"] == 0 && @$row["MarkedPTExtraDay"] == 0) {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["financial"] = 0;
            } else {
                $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["financial"] = 1;
            }
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["pdlStartTime"] = (int) $row["LeaveStartTime"];
            $arrAllocations[$schedulingPersonID][$strCurrentWeek][$strCurrentDay][$row["IsTemplate"]]["pdlEndTime"] = (int) $row["LeaveEndTime"];
        }
    } else {
        $arrAllocations = [];
    }
    $intMaskAfterUnixDate = isset($intMaskAfterUnixDate) ? $intMaskAfterUnixDate : '';
    if (!empty($arrAllocations)) {
        if ($isShifttoCheck == 1) {
            return MakeShiftsAllocations($intStartWeekNumber, $intEndWeekNumber, $intMaskAfterUnixDate, $intNoMask, $intMaskType, $scheduledPeople, $arrAllocations);
        } else {
            return MakeRotaAllocations($intStartWeekNumber, $intEndWeekNumber, $intMaskAfterUnixDate, $intNoMask, $intMaskType, $scheduledPeople, $arrAllocations);
        }
    } else {
        return [];
    }
}

/*
* Set font color based on team settings FAST-1789
* @param int $displayPriority data is rota or allocation
* @param int $weeklyColourChecked week color checked in scheduling team
* @param int $currentTeam check if its home team
* @param int $scheduledTeamId scheduled team
* @param string $dutyName Duty Name
*/
function getFontStyle($displayPriority = 0, $weeklyColourChecked = 0, $currentTeam = 0, $scheduledTeamId = 0, $dutyName = '', $data = ''): string
{
    $fontStyle = '';
    switch ((int)$displayPriority) {
        case 2:
            if ($weeklyColourChecked == 1) {
                $fontStyle = 'font-style: italic;';
                if ((strtolower((string) $dutyName) == 'leave') || (strtolower((string) $dutyName) == 'off leave')) {
                    $leaveColour = isset($data['LeaveFontColour']) ? $data['LeaveFontColour'] : 'black';
                    $fontStyle = 'font-style: italic; color:' . $leaveColour . ';';
                }
            } else if ((strtolower((string) $dutyName) != 'leave') && (strtolower((string) $dutyName) != 'off leave')) {
                $fontStyle = 'color: blueviolet;';
            }
            break;
        default:
            if (($weeklyColourChecked != 1 && strtolower((string) $dutyName) != 'leave') && (strtolower((string) $dutyName) != 'off leave')) {
                if ($currentTeam != $scheduledTeamId) {
                    $fontStyle = 'color: grey;';
                } else {
                    $fontStyle = 'color: black;';
                }
            } else if ((((strtolower((string) $dutyName) == 'leave') || (strtolower((string) $dutyName) == 'off leave'))) && !empty($data['LeaveType'])) {
                $fontStyle = 'color:' . $data['LeaveFontColour'] . ';';
            }
            break;
    }
    return $fontStyle;
}

function ReadFreelanceAllocationsArea($intDepartmentID = 0, $intAreaID = 0, $intWeekNumber = '', $startDate = '', $endDate = '', $ScheduledPersonID = 0, $arrStaffBreaks = [], $userID = '')
{
    try {
        $pdo = OpenDBLinkA7();
        $strQuery = "exec [dbo].[usp_getAllocationsFreelancers] ?, ?, ?, ?, ?, ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intWeekNumber, PDO::PARAM_INT);
        $stmt->bindParam(2, $intDepartmentID, PDO::PARAM_INT);
        $stmt->bindParam(3, $userID, PDO::PARAM_INT);
        $stmt->bindParam(4, $intAreaID, PDO::PARAM_INT);
        $stmt->bindParam(5, $startDate, PDO::PARAM_STR);
        $stmt->bindParam(6, $endDate, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($ScheduledPersonID !== 0) {
            $result = array_filter($result, fn($row) => $row['ScheduledPersonID'] == $ScheduledPersonID);
        }
        $arrAllocations = [];
        foreach ($result as $row) {
            $strCurrentschNumber = $row["ScheduledPersonID"];
            $strCurrentStaffNumber = $row['Staffnumber'];
            $intCurrWeekNumber = $row['Weeknumber'];
            $intDutyDate = $row['DutyDate'];
            $intDay = $row['Iday'];
            $arrAllocations['Duties'][$strCurrentschNumber]['Name'] = $row['FullName'];
            $arrAllocations['Duties'][$strCurrentschNumber]['Contract'] = '';
            $arrAllocations['Duties'][$strCurrentschNumber]['StaffNumber'] = $strCurrentStaffNumber;
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['DutyName'] = $row['Dutyname'];
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['TeamDescription'] = $row['schedulingTeamDescription'] ?? '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['Role'] = '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['ChargeCode'] = '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['HourlyRate'] = '';
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['AreaName'] = $row['DivisionName'];
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['CostCode'] = $row['CostCode'];
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['IsAreaUnderUser'] = $row['IsAreaUnderUser'];
            $row["StartTime"] = isset($row["StartTime"]) ?? null;
            $row["EndTime"] = isset($row["EndTime"]) ?? null;
            if (!is_null($row["StartTime"])) {
                $strStartTime = gmdate("H:i", ($row["StartTime"] * 86400) + 1);
                $strEndTime = gmdate("H:i", ($row["EndTime"] * 86400) + 1);
                $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]["StartTime"] = $strStartTime;
                $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]["EndTime"] = $strEndTime;
            }
            $intDuration = $row['Duration'];
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]["Duration"] = number_format((float) $intDuration / 3600, 2, '.', '');
            $intMealBreak = $row['dutyBreakTime'] ?? null;
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]["DurationLessMeal"] = number_format((float) ($intDuration - $intMealBreak) / 3600, 2, '.', '');
            // Work out the cost......
            $row['HourlyRate'] = isset($row['HourlyRate']) ?? 0;
            if (!is_null($row['HourlyRate'])) {
                $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['Cost'] = $row['HourlyRate'] * number_format((float) ($intDuration - $intMealBreak) / 3600, 2, '.', '');
            }
            $row['DepartmentID'] = isset($row['DepartmentID']) ?? 0;
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['DepartmentName'] = $row['AllocationDepartmentName'];
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['DepartmentID'] = $row['DepartmentID'];
            $strPersonComments = '';
            $strFullSBRef = '';
            preg_match_all("/\\[(.*?)\\]/", (string) $row['PersonComments'], $arrPersonComments);
            if (isset($arrPersonComments[1])) {
                foreach ($arrPersonComments[1] as $strComments) {
                    $intRefPos = stripos($strComments, 'ref');
                    if ($intRefPos === false) {
                        $strReason = $strComments;
                        $strSBRef = '';
                    } else {
                        $strReason = substr($strComments, 0, $intRefPos);
                        $strSBRef = trim(substr($strComments, $intRefPos + 3));
                    }
                    $strPersonComments .= ' ' . trim($strReason);
                    $strFullSBRef .= ' ' . $strSBRef;
                }
            }

            global $intFixedDays;

            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['PersonComments'] = $row['PersonComments'];
            $arrAllocations['Duties'][$strCurrentschNumber]['Duties'][$intDutyDate][0]['SmartBookRef'] = $strFullSBRef;
            if (isset($arrAllocations['Hours'][$intDutyDate][0])) {
                $arrAllocations['Hours'][$intDutyDate][0] = number_format((float) ($arrAllocations['Hours'][$intDutyDate][0] + $intDuration - $intMealBreak) / 3600, 2, '.', '');
                $arrAllocations['Hours'][$intDutyDate]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
            } else {
                $arrAllocations['Hours'][$intDutyDate][0] = number_format((float) ($intDuration - $intMealBreak) / 3600, 2, '.', '');
                $arrAllocations['Hours'][$intDutyDate]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
            }
            if (isset($arrAllocations['Count'][$intDutyDate][0])) {
                $arrAllocations['Count'][$intDutyDate][0] = $arrAllocations['Count'][$intDutyDate][0] + 1;
                $arrAllocations['Count'][$intDutyDate]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
            } else {
                $arrAllocations['Count'][$intDutyDate][0] = 1;
                $arrAllocations['Count'][$intDutyDate]["IsAreaUnderUser"] = $row["IsAreaUnderUser"];
            }
            if (isset($arrAllocations['Total'])) {
                $arrAllocations['Total'] = number_format((float) ($arrAllocations['Total'] + $intDuration - $intMealBreak) / 3600, 2, '.', '');
            } else {
                $arrAllocations['Total'] = number_format((float) ($intDuration - $intMealBreak) / 3600, 2, '.', '');
            }
            if (isset($arrAllocations['TotalCount'])) {
                $arrAllocations['TotalCount'] = $arrAllocations['TotalCount'] + 1;
            } else {
                $arrAllocations['TotalCount'] = 1;
            }
        }
        if (isset($arrAllocations)) {
            return ($arrAllocations);
        }

        $intLoopWeeks = $intStartWeekNumber;
        $intLoopEndWeek = addweeks($intEndWeek, 1);
        $intArrPos = 0;

        while ($intLoopWeeks != $intLoopEndWeek) {
            $arrWeeksToArray[$intLoopWeeks] = $intArrPos;
            $intLoopWeeks = addweeks($intLoopWeeks, 1);
            $intArrPos++;
        }
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }
}

function GetSchedulingTeamColorWeek($schTeamId = 0)
{
    $pdo = OpenDBLinkA7();
    $strQuery = "SELECT colourWeek FROM schedulingTeams WHERE schedulingTeamId=" . $schTeamId;
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function AllocationsEditYearly($intStartWeek = 0, $intEndWeek = 0, $schedulingPersonId = '', $arrDepDefaults = [], $strUserLogin = 0, $intLeaveCaller = 0, $isShiftleader = '', $email = '')
{
    $arrTextColours = GetShiftTextColours();
    $pNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $pdo = OpenDBLinkA7();
    try {
        $strQuery = "exec [dbo].[usp_fetch_monthlyAllocationAndRota] :intStartWeek, :intEndWeek, :schedulingPersonId, :isShiftleader, :pNetLogin, 2";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(':intStartWeek', $intStartWeek, PDO::PARAM_INT);
        $stmt->bindParam(':intEndWeek', $intEndWeek, PDO::PARAM_INT);
        $stmt->bindParam(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
        $stmt->bindParam(':isShiftleader', $isShiftleader, PDO::PARAM_INT);
        $stmt->bindParam(':pNetLogin', $pNetLogin, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }

    if (count($result) > 0) {
        foreach ($result as $row) {
            $intBreakTime = $row['dutyBreakTime'] ?? 0;
            $strCurrentWeek = $row['WeekNumber'] ?? 0;
            $intCurrentDay = $row['DOTW'] ?? 0;
            $intMaskDays = $row['maskAfter'] ?? 0;
            $intMaskType = $row['maskType'] ?? 0;

            $intMaskAfterUnixDate = strtotime("+$intMaskDays Days");
            $uThisDate = strtotime(datefromweek($strCurrentWeek, $intCurrentDay));

            $strLeaveHasMealFrom = isset($row['defaultRotaStartDate']) ? date('Y-m-d', strtotime($row['defaultRotaStartDate'])) : '';

            $strDutyname = $row['DutyName'] ?? '';
            if ($uThisDate > $intMaskAfterUnixDate) {
                $arrAllocations['Weeks'][$row['WeekNumber']][$row['DOTW']]['Masked'] = 1;
                $arrAllocations['Weeks'][$row['WeekNumber']][$row['DOTW']]['MaskType'] = $intMaskType;
            }

            if ($row["display_priority"] == 2) {
                $rowIsRota = 1;
                $arrAllocations['Weeks'][$strCurrentWeek]['WeekDisplayPriority'] = 2;
            } else {
                $rowIsRota = 0;
                $arrAllocations['Weeks'][$strCurrentWeek]['WeekDisplayPriority'] = 1;
            }

            if (!is_null($row['dutyBreakTime']) || !empty($row['dutyBreakTime'])) {
                $arrAllocations['Weeks'][$strCurrentWeek]['HasBreaks'] = 1;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek]['HasBreaks'] = 0;
            }

            $arrAllocations['Weeks'][$strCurrentWeek]['TotalDuration'] = (int) $row['TotalDuration'] ?? 0;
            $arrAllocations['Weeks'][$strCurrentWeek]['TDExBreak'] = (int) $row['TDExBreak'] ?? 0;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['SchedulingTeamId'] = $row['SchedulingTeamId'] ?? 0;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['SchedulingTeamName'] = $row['schedulingTeamName'] ?? 0;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['ColourWeek'] = $row['colourWeek'] ?? 0;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['Duty'] = $strDutyname;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['DutyId'] = $row['ID'] ?? 0;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["IsRota"] = $rowIsRota;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["RotaLeave"] = $row['ROTA'] ?? 0;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["display_priority"] = $row["display_priority"] ?? '';
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["hiddenDays"] = $row['isHiddenDays'] ?? 0;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['ActingFlag'] = $row['ActingFlag'] ?? '';
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['IsAttention'] = $row['IsAttention'] ?? '';
            if ((strtolower((string) $row['DutyName']) != 'sick') && (strtolower((string) $row['DutyName']) != 'leave')) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["AllowApplyOvertime"] = $row["allowOvertimeRequests"];
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["AllowApplyOvertime"] = 0;
            }

            $duration = (int) $row["Duration"];
            $duration = number_format((float) ($row["Duration"] / 3600), 2, '.', '');
            $isworking = 1;
            if (isset($arrTextColours)) {
                $isworking = GetIsNotWorking($strDutyname, $arrTextColours);
            }

            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['isworking'] = $isworking;
            //checking starttime is not null
            if (($row["StartTime"] > 0 || $row["EndTime"] > 0) && (isset($row['DutyName']) && strtoupper($row['DutyName']) != 'U')) {
                $starttime = gmdate("H:i", intval($row["StartTime"]) + 1);
                $endtime = gmdate("H:i", intval($row["EndTime"]) + 1);

                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["StartTime"] = $starttime;
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["EndTime"] = $endtime;

                if (is_null($row["active"])) {
                    $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignIn"] = 0;
                } else {
                    if ($row['StartTime'] == $row["SignInStartTime"] && $row['EndTime'] == $row["SignInEndTime"]) {
                        $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignIn"] = $row["active"];
                    } else {
                        $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignIn"] = 2;
                    }
                }

                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignInID"] = $row["SignInID"];
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["SignInDays"] = $row["signInDays"];
            }
            // Any financial reward?   MarkedOvertime ActingGrade
            if ($row["MarkedOvertime"] == 0 && $row["ActingGrade"] == 0 && $row["MarkedPTExtraDay"] == 0 && $row["MarkedCompLeave"] == 0) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["financial"] = 0;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["financial"] = 1;
            }
            // set flag for markedovertime or not
            if ($row["ManualOThours"] > 0) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["ManualOThours"] = 1;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["ManualOThours"] = 0;
            }

            if (is_null($row["inBuilding"])) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["InBuilding"] = 0;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["InBuilding"] = $row["inBuilding"];
            }

            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["Duration"] = $duration;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["BreakTime"] = $intBreakTime;

            if ((strtotime($strLeaveHasMealFrom) < $uThisDate) && (stripos((string) $strDutyname, 'leave') !== false)) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['DurationLessMeal'] = $duration + $intBreakTime;
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['DurationLessMeal'] = $duration;
            }

            $intConfirmedDays = $arrDepDefaults['ConfirmedDays'] ?? '';
            $thisdate = datefromweek($row['WeekNumber'], $row['DOTW']);

            if ($row["display_priority"] == 2) {
                $CellClass = SetRotaDutyClass($isworking);
            } else {
                $CellClass = SetDutyClass($thisdate, $intMaskDays, $intMaskType, $isworking);
            }

            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['ShowRotaAsWell'] = ShowRotaAsWell($thisdate, $intConfirmedDays);
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['CellClass'] = $CellClass;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["Duration"] = $duration;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["BreakTime"] = $intBreakTime;
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["DutyComments"] = $row['DutyComments'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["PersonComments"] = $row['PersonComments'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["pdlStartTime"] = $row['LeaveStartTime'];
            $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]["pdlEndTime"] = $row['LeaveEndTime'];
            if (!empty($row['DutyName'])) {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['BackColour'] = '#' . $row['BackColour'];
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['allocateTextColour'] = '#' . $row['FontColour'];
            } else {
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['BackColour'] = '#EFEFEF';
                $arrAllocations['Weeks'][$strCurrentWeek][$row['DOTW']]['allocateTextColour'] = '#000000';
            }
        }
    }

    if (!empty($arrAllocations)) {
        return MakeMonthlyRotaAllocations($intStartWeek, $intEndWeek, $schedulingPersonId, $arrAllocations, '');
    } else {
        return [];
    }
}

function getEditYearlyMailList(int $teamId)
{
    $emails = [];

    $query = "SELECT Email
    FROM schedulingTeams (NOLOCK)
    WHERE schedulingTeamId = :teamId
    ORDER BY 1;";

    $pdo = OpenDBLinkA7();
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':teamId', $teamId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    foreach (explode(';', $result['Email'] ?? '') as $dataEmail) {
        $emails[] = ['InternalEmail' => $dataEmail];
    }

    $query = "SELECT InternalEmail FROM StaffDetails  WHERE NetLogin = :user";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user', $_SESSION['user']['user'], PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $emails[] = ['InternalEmail' => $stmt->fetch(PDO::FETCH_ASSOC)['InternalEmail']];
    } else {
        $emails[] = ['InternalEmail' => ''];
    }
    return $emails;
}

function getStartEndDateBasedOnHomeTeam($startDate, $endDate, $schedulingPersonId)
{
    $setupObj = new classUserSetup();
    $arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
    $scheduledPersonTeamHistory = getScheduledPersonTeamHistory($schedulingPersonId);
    $teamDate = [];
    foreach ($scheduledPersonTeamHistory as $history) {
        $schedulingTeamId = $history['TeamID'];
        if ($history['HomeTeam'] != 'Y' || !isset($arrUsersTeamdata['Teams'][$schedulingTeamId])) {
            continue;
        }
        $actualStartDate = DateTime::createFromFormat('Y-m-d', $startDate)->setTime(0, 0, 0);
        $actualEndDate = DateTime::createFromFormat('Y-m-d', $endDate)->setTime(0, 0, 0);
        $historyStartData = DateTime::createFromFormat('d-m-Y', $history['Startdate'])->setTime(0, 0, 0);
        $historyEndData = DateTime::createFromFormat('d-m-Y', $history['Enddate'])->setTime(0, 0, 0);
        if (($historyStartData >= $actualStartDate && $historyStartData <= $actualEndDate) ||
            ($historyEndData >= $actualStartDate && $historyEndData <= $actualEndDate) ||
            ($historyStartData <= $actualEndDate && $historyEndData >= $actualStartDate)
        ) {
        } else {
            continue;
        }
        if (($arrUsersTeamdata['Teams'][$schedulingTeamId]['Scheduler'] == 1) || ($arrUsersTeamdata['Teams'][$schedulingTeamId]['SchedulingTeamAdmin'] == 1) || ($arrUsersTeamdata['Teams'][$schedulingTeamId]['TeamLeader'] == 1)) {
            $teamDate[$schedulingTeamId]['teamId'] = $schedulingTeamId;
            $teamDate[$schedulingTeamId]['startDate'] = $startDate;
            if ($historyStartData >= $actualStartDate) {
                $teamDate[$schedulingTeamId]['startDate'] = $historyStartData->format('Y-m-d');
            }
            $teamDate[$schedulingTeamId]['endDate'] = $endDate;
            if ($historyEndData <= $actualEndDate) {
                $teamDate[$schedulingTeamId]['endDate'] = $historyEndData->format('Y-m-d');
            }
        }
    }
    return $teamDate;
}
