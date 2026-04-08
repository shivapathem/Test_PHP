<style type="text/css">
    span.clearFilterIcon {
        position: absolute;
        right: -4px;
        top: 0;
        z-index: 11;
    }

    span.clearFilterIcon img {
        width: 15px;
        height: 15px;
        cursor: pointer;
    }
</style>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('UTC');
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/skillsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/editabledaystatus.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
require_once '../../page-includes/allocations/weekly/service/PublishWeekService.php';
include_once __DIR__. '/../../function-includes/user-scheduling-team-list.php';

use App\Models\User\RefRole;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$service = new AllocationService();
$commonObj = new classCommonDBFunctions();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intIsShiftLeader = 2; //As scheduled person, freelancer,schduleing team Viewer.
$intCanViewComments = 0;
$intTickAllow = $intCanMakeEdited = 0;
$selFilterName = '';
$filterCond = '';
$dteFirstDate = '';
$strStatusClass = '';
if (isset($_SESSION['screenwidth'])) {
    $intScreenWidth = $_SESSION['screenwidth'];
} else {
    $intScreenWidth = 1600;
}
//Set Default Team
if (isset($_POST['teamId'])) {
    $intTeamID = $_POST['teamId'];
} else {
    $intTeamID = GetDefaultSchedulingTeamIdByLogin($userId);
}
//Set Week No
if (!empty($_POST['WeekNumber']) && $_POST['WeekNumber'] != 'undefined') {
    // Passed a week Number?
    $intWeekNumber = $_POST['WeekNumber'];
} else {
    if (isset($_SESSION["allocations"]["WeekNumber"]) && $_SESSION["allocations"]["WeekNumber"] != '' && $_SESSION["allocations"]["WeekNumber"] != 'undefined') {
        // Is the session set?
        $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
    } else {
        $bbcweeknumberArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
        $intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? date('Y') . date('W');
    }
}
//staffOption Roles & Permission Fetch
$isScheduler = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULER);
$isManager = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::MANAGER);
$isTeamAdmin = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_ADMIN);
$hasShiftleaderRole = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SHIFT_LEADER);
$isTeamLeader = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::TEAM_LEADER);
$isSchedulingTeamViewer = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_VIEWER);
$isScheduledPerson = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULED_PERSON);
$isAdmin = $userRoleTrait->isSystemAdmin();
//If system admin or area admin the person have STA permission
if($isTeamAdmin == 0) {
  $isTeamAdmin = $userRoleTrait->checkEditWeeeklyAdminRole($intTeamID);
}
$arrTeamDefaults = GetTeamDefaults(0, $intTeamID);

$canViewAdditional = 0;
if ($isScheduler == 1 || $isTeamAdmin == 1 || $isManager == 1) {
    $canViewAdditional = 1;
}

$isFreelencer = $commonObj->GetIsFreelencerByNetlogin($strUser);
$_SESSION['user']["isFreelance"] = !empty($isFreelencer) ? 1 : 0;
$intIsFreelance = $_SESSION['user']["isFreelance"];
if (isset($isTeamAdmin) && isset($isManager) && isset($isScheduler) && isset($isTeamAdmin)) {
    if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isManager == 1)) {
        $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
        $intCanViewComments = 1;
        $intIsShiftLeader = 0;
    } else {
        $dteFirstDate = date("Y-m-d", strtotime("-$intViewYears years"));
    }
}
$prodStartDate = '0000-00-00';
$prodEndDate = '0000-00-00';
$strDutyName = '';
$getCurrentFilter = GetUserCurrentFilter($intTeamID);

if (isset($getCurrentFilter['ProdStartDay']) && !empty($getCurrentFilter)) {

    if ($getCurrentFilter['ProdStartDay'] != '' && $getCurrentFilter !== false) {
        $prodStartDate = date('Y-m-d', strtotime(GetStartDateByBBCWeekNumber($intWeekNumber, $getCurrentFilter['ProdStartDay'])));
    } else {

        $prodStartDate = date('Y-m-d', strtotime(GetStartDateByBBCWeekNumber($intWeekNumber, 0)));
    }
} else {
    $prodStartDate = date('Y-m-d', strtotime(GetStartDateByBBCWeekNumber($intWeekNumber, 0)));
}

if (isset($getCurrentFilter['ProdDayCount']) && !empty($getCurrentFilter)) {
    if ($getCurrentFilter['ProdDayCount'] != '' && $prodStartDate != '0000-00-00') {
        $prodEndDate = date('Y-m-d', strtotime($prodStartDate . ' +' . ($getCurrentFilter['ProdDayCount'] - 1) . ' days'));
    } elseif ($prodStartDate != '0000-00-00') {
        $prodEndDate = date('Y-m-d', strtotime($prodStartDate . ' +6 days'));
    }
} else {
    $prodEndDate = date('Y-m-d', strtotime($prodStartDate . ' +6 days'));
}

if (!empty($getCurrentFilter) && isset($getCurrentFilter['CurrentFilter'])) {
    $filterCond = GetQueryCondition($getCurrentFilter['CurrentFilter'], $intWeekNumber, $intWeekNumber, $intTeamID, $prodStartDate, $prodEndDate);
    $getFilterColRelation = GetProductionFilterDetails($getCurrentFilter['CurrentFilter']);
}
$filterCondarray = !empty($getCurrentFilter) ? GetQueryCondition($getCurrentFilter['CurrentFilter'], $intWeekNumber, $intWeekNumber, $intTeamID, $prodStartDate, $prodEndDate) : [];
$filterCond = '';

$dutyFilter = ((isset($filterCondarray['DutyFilter'])) && ($filterCondarray['DutyFilter'] != '')) ? $filterCondarray['DutyFilter'] : '';
$dutyLabel = ((isset($filterCondarray['dutylabel'])) && ($filterCondarray['dutylabel'] != '')) ? $filterCondarray['dutylabel'] : '';
$dutyFilterDaily = ((isset($filterCondarray['dutyFilterDaily'])) && ($filterCondarray['dutyFilterDaily'] != '')) ? $filterCondarray['dutyFilterDaily'] : '';
$additionalTeams = ((isset($filterCondarray['additionalteam'])) && ($filterCondarray['additionalteam'] != '')) ? $filterCondarray['additionalteam'] : '';

$sortCodeFilter = ((isset($filterCondarray['SortCodeFilter'])) && ($filterCondarray['SortCodeFilter'] != '')) ? $filterCondarray['SortCodeFilter'] : '';

if ($dutyFilterDaily != '') {
    $dutyFilterDaily = $filterCondarray['dutyFilterDaily'];
} else {
    $dutyFilterDaily = '';
}

if (isset($filterCondarray['additionalteam']) && ($filterCondarray['additionalteam'] != '')) {
    $filteraddqry = $filterCondarray['additionalteam'];
    preg_match_all('!\d+!', $filteraddqry, $matches);
    $addteam = implode(",", $matches[0]);
    $addteam = $addteam . "," . $intTeamID;
    $filterTeam = $addteam;
} else {
    $filterTeam = $intTeamID;
}

if (isset($getCurrentFilter['CurrentFilter']) && is_numeric($getCurrentFilter['CurrentFilter']) && $getCurrentFilter['CurrentFilter'] != '') {
    $selFilterName = GetProductionFilterName($getCurrentFilter['CurrentFilter']);
}
if (isset($getCurrentFilter['CurrentFilter']) && !is_numeric($getCurrentFilter['CurrentFilter']) && $getCurrentFilter['CurrentFilter'] != '') {
    $strDutyName = $getCurrentFilter['CurrentFilter'];
}
// ########################################## A few settings that affect page layout ##########################################
// Number of days to show
$intDayStart = 0;
$intDayCount = 7;

if (isset($getCurrentFilter['ProdDayCount']) && $getCurrentFilter['ProdDayCount'] != '') {
    $intDayCount = (int) $getCurrentFilter['ProdDayCount'];
}
if (isset($getCurrentFilter['ProdStartDay']) && $getCurrentFilter['ProdStartDay'] != '') {
    $intDayStart = (int) $getCurrentFilter['ProdStartDay'];
}
$intHeightMultiply = 20;
$intMinHeight = 28;
$intDateHeight = 35;
$intStatusHeight = 10;
$intRowHeight = 26;
$intNamesWidth = 200;
$intMinWidth = 110;

// If the width is less than 110 abbreviate the names....
$intDutyWidth = round($intScreenWidth - $intNamesWidth - 40) / $intDayCount;
if ($intDutyWidth < $intMinWidth) {
    $strUncovered = "U";
} else {
    $strUncovered = "Uncovered";
}
$intTableWidth = round($intNamesWidth + ($intDutyWidth * $intDayCount));
$intMarkabsent = 0;
$dateCommentIsShiftLeader = 0;
if (($hasShiftleaderRole == 1) && (($isScheduler !== 1) && ($isTeamAdmin !== 1))) {
    $intIsShiftLeader = 1;
    $intCanMakeEdited = 1;
    $intMarkabsent = 1;
    $dateCommentIsShiftLeader = 1;
}

if ($isTeamLeader == 1) {
    $dateCommentIsShiftLeader = 0;
}

if (($hasShiftleaderRole == 1) && $isManager == 1) {
    $intIsShiftLeader = 3;
    $intMarkabsent = 1;
}

if (isset($_POST['WeekNumber'])) {
    // Passed a week Number?
    $intWeekNumber = $_POST['WeekNumber'];
} else {
    if (isset($_SESSION["allocations"]["WeekNumber"])) {
        // Is the session set?
        $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
    } else {
        $bbcweeknumberArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
        $intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? 0;
    }
}

function filterJobs($jobDataP)
{
    $jobData = [];
    foreach ($jobDataP as $job) {
        if (
            (($job['JobBackColour'] ?? '') == '#ffffff' && ($job['JobFontColour'] ?? '') == '#000000') ||
            (($job['JobBackColour'] ?? '') == '#fff' && ($job['JobFontColour'] ?? '') == '#000')
        ) {
            continue;
        }
        $jobData[] = [
            'jobName' => $job['JobName'] ?? '',
            'jobStartTime' => gmdate("H:i", intval($job['JobStartTime'] ?? 0)),
            'jobEndTime' => gmdate("H:i", intval($job['JobEndTime'] ?? 0))
        ];
    }
    return $jobData;
}

if ($intTeamID == 0) {
    echo '<br>';
    echo '<div class="tableheadersmall bigtextboldcentre">';
    echo '<br><br>';
    echo 'You are attempting to view the Allocations for your Default Department.<br>This is not possible Please choose a Department from the menu.';
    echo '<br></br><br>';
    echo '</div>';
} else {
    $arrDepartmentDefaults = GetTeamDefaults($userId, $intTeamID);
    $strDepartmentName = $arrDepartmentDefaults[$intTeamID]['Description'] ?? '';
    $intMaskDays = $arrDepartmentDefaults[$intTeamID]['MaskAfter'] ?? 0;
    $arrFilters = GetDutyFiltersByDepartment($intTeamID, 1);
    $arrGroups = GetProdViewGroups($intTeamID);
    $datefromweek = $commonObj->GetWeekStartDateByWeekNoFromTimeDim($intWeekNumber, 'ByweeknoOnly', null);
    $dteStartDate = $datefromweek['dDateTime'];
    $dteEndtDate = date('Y-m-d', strtotime($dteStartDate . ' + 7 days'));
    $intEndWeek = $intWeekNumber;
    $arrHolidays = calculateBankHolidayst(date("Y", strtotime($dteStartDate)));
    $intPreviousWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate . ' - 7 days')));
    $intPreviousWeek = $intPreviousWeekArray['ixYearWeek'];
    $intNextWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate . ' + 7 days')));

    $intNextWeek = $intNextWeekArray['ixYearWeek'];
    $_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;
    $arrHiddenDays = ReadHiddenDays($intTeamID, $dteStartDate, $dteEndtDate);
    $strLoopDate = $dteStartDate;
    // ################################################################################ Is there a filter set?
    $strDutyNameFilter = '';
    $strPresetFilterFilter = '';
    $strPresetFilterDesc = '';

    $request->request->set('startWeek', $intWeekNumber);
    $publishWeekService = new PublishWeekService($request);
    $pubrecord = $publishWeekService->getPubslishRecordUserName();
    // ################################################################################ END Is there a filter set?

    // ######################################################## End ###############################################################
    $arrFilterText = array();
    $arrBaseCodes = array();
    $intMatchType = -1;
    $intTeamIDs = $intTeamID;

    if ($dteStartDate > $dteFirstDate) {
        $arrAllocations = GetReadAllocationsByDuties($intWeekNumber, $filterTeam, $intDayStart, $intDayCount, $intCanViewComments, $intIsShiftLeader, $prodStartDate, $prodEndDate, $strUser, $arrBaseCodes, $arrFilterText, $intMatchType, $intTeamID, $getCurrentFilter['CurrentFilter'] ?? 0, $strDutyName, $canViewAdditional);
    } else {
        $arrAllocations = array();
    }

    $arrEditable = $arrAllocations['IsDayEditable'] ?? [];
    $arrEdited = $arrAllocations['isEdited'] ?? [];
    if (isset($arrGroups) && !empty($arrGroups)) {
        $arrAllocations = ApplyGrouping($arrAllocations, $arrGroups); //Done
    }
    // When do we restrict the text from?
    echo '<div class="production-section">';
    echo '<div class="production-main">';
    echo '<div class="left">';
    echo '<div class="left-btn-section">';
    echo '<div style="margin-right: 25px;">';
    echo '<button class="acc-btn handcursor" onclick=\'javascript:ShowAllocationsDuties(' . $intTeamID . ',"' . $intPreviousWeek . '")\';>&nbsp;&lt;&lt; Week ' . spinweek($intPreviousWeek) . '</button>';
    echo '</div> ';
    echo '<div style="margin-right: 10px;">';
    echo '<button class="acc-btn handcursor" onclick=\'javascript:ShowAllocationsDuties(' . $intTeamID . ',"' . $intNextWeek . '")\';>Week ' . spinweek($intNextWeek) . '&nbsp;&gt;&gt;</button>';
    echo '</div>';
    echo '<div>';
    echo '<span style="font-size: 12px; font-weight: 600;vertical-align: super;" tabindex="0">Choose a Date</span>';
    echo '<button style="border: none; padding: 0px; height: 15px; margin-left: 5px;"><input type="hidden" id="datepicker"></button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="fourth">';
    echo '&nbsp;&nbsp;<button class="allocation-btn">';
    echo '<img title="Show Allocations for this Week" border="0" src="../images/AllocationsWeekly.png" width="53px" height="28px"  onclick="javascript:ShowAllocations(' . $intTeamID . ', \'' . $intWeekNumber . '\')";>';
    echo '</button>';
    if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isTeamLeader == 1)) {
        echo '&nbsp;<button class="allocation-btn">';
        echo '<img title="Show Allocations Edit Weekly" class="medtextbold handcursor" onclick=\'javascript:openEditWeekly("' . $intTeamID . '","' . $intWeekNumber . '")\'; border="0" src="../images/AllocationsEditWeekly.png" width="53px" height="28px" >';
        echo '</button>';
    }
    echo '</div>';
    echo '</div>';

    echo '<div class="center" style="width: 30%; text-align: center; font-weight: bold;">';
    echo '<h1 style="text-align: center;">Production View</h1>';
    echo '<h2 style="font-size: 15px; padding-top: 8px;">Showing ' . $strDepartmentName . '</h3>';
    echo '</div>';

    echo '<div class="right" style="width: 37%;display: flex;gap:30px">';
    echo '<div class="section-box" >';

    //if (isset($arrTeam['HasDutiesView']) && $arrTeam['HasDutiesView'] == 1) {
    echo '<select name="teamId" id="teamId" onchange="ShowAllocationsDuties(this.value,' . $intWeekNumber . ')" class="chosen-select">';
    echo '<option value="">Select The Team</option>';
    $teamOptions = getSchedulingTeamList($intTeamID, 'allocation-policy', 'view');
    echo $teamOptions;
    echo '</select>';
    echo '</div>';
    echo '<div class="section-box">';
    echo '<div style="position: relative; width:250px">';
    if ($selFilterName != '' || $strDutyName != '') {
        echo '<span class="clearFilterIcon qtip-hover" title="Clear Filter" onclick="clearProductionViewFilter(' . $intTeamID . ')">
                <img src="../../images/red_cross.png" alt="Clear Filter" />
        </span>';
    }
    echo '<div style="" id="accordion" class="accordiontop production-view-select2">';
    echo '<div class="qtip-hover" title="' . $selFilterName . '">';
    if ($selFilterName != '') {
        if (strlen($selFilterName) > 30) {
            echo substr($selFilterName, 0, 30) . '...';
        } else {
            echo $selFilterName;
        }
    } else {
        echo 'Filters & Options';
    }
    echo '</div>';
    echo '<div style="overflow:visible;">';
    // fiter table code start
    echo '<table width="100%" class="filter-table" >';

    // ################################################################################## Preset Filter
    if (isset($arrFilters[0])) {
        echo '<tr>';
        echo '<td>';
        echo 'Preset Filters&nbsp;';
        echo '</td>';
        echo '<td>';
        echo '<select class="chosen-select" size="1" name="PreFilters" onchange="javascript:SetFilter(0, value, ' . $intTeamID . ')";>';
        echo '<option value="0">--Select Filter--</option>';
        foreach ($arrFilters[0] as $intID => $arrFilter) {
            if (isset($getCurrentFilter['CurrentFilter']) && is_numeric($getCurrentFilter['CurrentFilter']) && $getCurrentFilter['CurrentFilter'] != '') {
                if ($getCurrentFilter['CurrentFilter'] == $intID) {
                    echo '<option selected value="' . $intID . '">' . $arrFilter['Description'] . '</option>';
                    $strPresetFilterFilter = $arrFilter['Description'];
                } else {
                    echo '<option value="' . $intID . '">' . $arrFilter['Description'] . '</option>';
                }
            } else {
                echo '<option value="' . $intID . '">' . $arrFilter['Description'] . '</option>';
            }
        }
        echo '</select>';
        echo '</td>';
        echo '<td align="center">';
        echo '<img title="Preset Filter<br>Please Choose a filter from the list" border="0" src="../images/info.png" width="12px" height="12px">';
        echo '</td>';

        echo '</tr>';
    }
    // ################################################################################## END Preset Filter

    // ################################################################################## Duty Name Filter
    echo '<tr>';
    echo '<td>Filter Duties (Shifts) by Name</td> ';
    echo '<td>';
    echo '<form method="POST" id="FormDutyName1">';
    echo '<input type="text" name="filterid" size="20" value="' . $strDutyName . '"><input type="submit" value="Go" name="Go">';
    echo '<input type="hidden" name="filtertype" value="1">';
    echo '<input type="hidden" name="teamid" value="' . $intTeamID . '">';

    echo '</form>';
    echo '</td>';
    echo '<td align="center">';
    echo '<img title="Filter on Duties by Name<br>Please enter the filter<br>Separate words for multiple duties by \',\'" border="0" src="../images/info.png" width="12px" height="12px">';
    echo '</td>';
    echo '</tr>';
    // ################################################################################## END Duty Name Filter

    // ################################################################################## Number of Days to View
    echo '<tr>';
    echo '<td>';
    echo 'Number of Days to View&nbsp;';
    echo '</td>';
    echo '<td>';
    echo '<select class="chosen-select" size="1" name="SetDaysToView" onchange="javascript:SetDaysToView(value, ' . $intTeamID . ')";>';

    //$intDayCount
    for ($i = 1; $i < 29; $i++) {
        if ($i == $intDayCount) {
            echo '<option selected value="' . $i . '">' . $i . '</option>';
        } else {
            echo '<option value="' . $i . '">' . $i . '</option>';
        }
    }
    echo '</select>';
    echo '</td>';
    echo '<td align="center">';
    echo '<img title="Number of Days to View<br>Please Choose from the list" border="0" src="../images/info.png" width="12px" height="12px">';
    echo '</td>';
    echo '</tr>';
    // ################################################################################## END Number of Days to View

    // ################################################################################## Day to start on
    echo '<tr>';
    echo '<td>';
    echo 'First Day to View&nbsp;';
    echo '</td>';
    echo '<td>';
    echo '<select class="chosen-select" size="1" name="SetDaysToView" onchange="javascript:SetDaysStart(value, ' . $intTeamID . ')";>';

    //$intDayCount
    for ($i = 0; $i <= 6; $i++) {
        if ($i == $intDayStart) {
            echo '<option selected value="' . $i . '">' . $invdowMap[$i] . '</option>';
        } else {
            echo '<option value="' . $i . '">' . $invdowMap[$i] . '</option>';
        }
    }
    echo '</select>';
    echo '</td>';
    echo '<td align="center">';
    echo '<img title="Start Day on Week<br>Please Choose from the list" border="0" src="../images/info.png" width="12px" height="12px">';
    echo '</td>';
    echo '</tr>';
    // ################################################################################## END Day to start on

    echo '</table>';
    echo '</div>';
    echo '</div>';
    // Clear Filter here
    if ($intMatchType != -1) {
        echo '<div class="clear-filter-wrapper">';
        echo '<div width="100px" valign="middle" align="right">';
        echo '<br>Clear Filter';
        echo '</div>';
        echo '<div width="100px" valign="middle">';
        echo '<br>&nbsp;&nbsp;<img class="handcursor" onclick="javascript:SetFilter(-1, 0, ' . $intTeamID . ', 3);" border="0" src="../images/red_cross.png" width="12px" height="12px">';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div></div>';
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // end of table

    // End the header
    //have we any allocations to show?
    $x = 0;
    if ((isset($arrAllocations['Duties'])) || (isset($arrAllocations['Grouped'])) || (isset($arrAllocations['AvailableStaff'])) || (isset($arrAllocations['Leave']))) {
        // The holder
        echo '<div style="position: relative">';
        // The top Left Corner
        echo '<div style="position: absolute; width:' . $intNamesWidth . 'px; height:' . ($intDateHeight * 2 + 11) . 'px; left:0px; top:0px" id="fixed"  class="dotw">';
        echo '<div style="height: 10px; margin-top: 33%; font-size:9px;" class="qtip-hover" title="' . (!empty($pubrecord['DisplayName']) ? $pubrecord['DisplayName'] : '') . '">Last published : ' . (!empty($pubrecord['UpdatedDate']) ? date('d/m/Y H:i', strtotime($pubrecord['UpdatedDate'])) : 'Never') . '</div>';
        echo '</div>';
        // END the holder
        // The days of the week....
        echo '<div style="overflow: hidden; position: absolute; width:900px; height:' . ($intDateHeight * 2 + 11) . 'px; left:' . $intNamesWidth . 'px; top:0px" id="weeklytop" class="weeklytop">';

        // The week numbers
        //First Week
        $intWeekDayCount = (7 - $intDayStart);
        echo '<div style="font-size:14px; position:absolute; left:0px; top:0px; height:' . ($intDateHeight - 11) . 'px; text-align: center;" class="dotwcentre weeklytop-week">';
        echo spinweek($intWeekNumber);
        echo '</div>';
        $strLoopWeek = $intWeekNumber;
        $strCurrentDate = '';
        $w = 0;
        for ($i = $intWeekDayCount; $i < $intDayCount; $i = $i + 7) {
            $strLoopWeek = addweeks($strLoopWeek, 1);
            echo '<div style="font-size:14px; position:absolute; top:0px; height:' . ($intDateHeight - 11) . 'px; text-align: center;" class="dotwcentre handcursor weeklytop-week-inner" id="weeklytop-week-inner' . $w . '" onclick="javascript:ShowDailyAllocationsCompare(' . $intTeamID . ', \'' . $strCurrentDate . '\')";>';
            echo spinweek($strLoopWeek);
            echo '</div>';
            $w++;
        }
        $c = 0;
        for ($i = 0; $i < $intDayCount; $i++) {
            $actualDayIndex = $i + $intDayStart;
            $strCurrDate = date("Y-m-d", strtotime("+" . $actualDayIndex . " days", strtotime($dteStartDate)));
            echo '<div class="dotwcentre-cell">';
            $intDOTW = date("N", strtotime($strCurrDate));
            if ($strCurrDate == date("Y-m-d")) {
                echo '<div id="fshift' . $i . '" style="width:' . $intDutyWidth . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intDateHeight - 10) . 'px; height:' . ($intDateHeight - $intStatusHeight + 20) . 'px" class="dotwlight handcursor date-comment" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $intTeamID . '" onclick=\'javascript:ShowDailyAllocations(' . $intTeamID . ',"' . $strCurrDate . '",1)\';>';
            } else {
                if ($intDOTW == 6 || $intDOTW == 7) {
                    echo '<div id="fshift' . $i . '" style="width:' . $intDutyWidth . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intDateHeight - 10) . 'px; height:' . ($intDateHeight - $intStatusHeight + 20) . 'px" class="dotwwe handcursor  date-comment" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $intTeamID . '" onclick=\'javascript:ShowDailyAllocations(' . $intTeamID . ',"' . $strCurrDate . '", 1)\';>';
                } else {
                    echo '<div id="fshift' . $i . '" style="width:' . $intDutyWidth . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intDateHeight - 10) . 'px; height:' . ($intDateHeight - $intStatusHeight + 20) . 'px" class="dotw handcursor  date-comment" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $intTeamID . '" onclick=\'javascript:ShowDailyAllocations(' . $intTeamID . ',"' . $strCurrDate . '", 1)\';>';
                }
            }
            if ($intDutyWidth < $intMinWidth) {
                echo date("D", strtotime($strCurrDate)) . '<br>' . date("d/m", strtotime($strCurrDate));
            } else {
                echo date("l", strtotime($strCurrDate)) . '<br>' . spindate($strCurrDate);
                if (isset($arrHolidays[$strCurrDate])) {
                    echo "<br>(" . $arrHolidays[$strCurrDate] . ")";
                }
            }

            echo '<span class="date-commment-info-icon" style="cursor: pointer; position:absolute; padding:20px; top: 1px; right:-10px;"></span>';

            /** Lock Setting By Team */

            $defaultLockSetting = getDefaultLocksettingByTeamONiDay($intTeamID, $strCurrDate);
            if (isset($defaultLockSetting['lockStatus'][$strCurrDate]['LockStatus']) && $defaultLockSetting['lockStatus'][$strCurrDate]['LockStatus'] == 1) {
                $intTickAllow = 1;
            } else {
                $intTickAllow = 0;
            }

            if (isset($arrEditable[$strCurrDate]) && ($arrEditable[$strCurrDate]['IsDayEditable'] >= 1 && ($intIsShiftLeader == 1 || $intIsShiftLeader == 3))) {
                echo '<div class="TopRight" style="height:30px;">';
                if (isset($arrEdited[$strCurrDate]['isEdited']) && $arrEdited[$strCurrDate]['isEdited'] >= 1) {
                    //Has been edited
                    echo '<img border="0" width="20px" height="20px" src="images/yellow_tick.png">&nbsp;&nbsp;';
                } else {
                    echo '<img border="0" width="20px" height="20px" src="images/blue_tick.png">&nbsp;&nbsp;';
                }
                echo '</div>';
            } else if ((($isAdmin == 1) || ($isScheduler == 1) || ($isTeamAdmin == 1)) && $intTickAllow == 1) {
                echo '<div class="TopRight" style="height:30px;">';
                if (isset($arrEdited[$strCurrDate]['isEdited']) && $arrEdited[$strCurrDate]['isEdited'] >= 1) {
                    //Has been edited
                    echo '<img border="0" width="20px" height="20px" src="images/yellow_tick.png">&nbsp;&nbsp;';
                } else {
                    echo '<img border="0" width="20px" height="20px" src="images/blue_tick.png">&nbsp;&nbsp;';
                }
                echo '</div>';
            }
            echo '</div>';
            $strStatusClass = GetDayStatus($strCurrDate, $intCanViewComments, $intMaskDays, isset($arrHiddenDays[$intTeamID]) ? $arrHiddenDays[$intTeamID] : '');
            echo '<div id="fshift-b' . $i . '" pageid="3" date="' . $strCurrDate . '" team="' . $intTeamID . '" style="width:' . $intDutyWidth . 'px; position:absolute; left:' . ($c * $intDutyWidth) . 'px; top:' . (($intDateHeight * 2 + 11) - $intStatusHeight) . 'px; height:' . ($intStatusHeight - 1) . 'px" class="' . $strStatusClass . ' handcursor">';
            echo '</div>';
            echo '</div>';
            $c++;
        }
        echo '</div>';
        // END the days of the week....
        // ################################################################################## The names down the left side
        echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:' . $intNamesWidth . 'px; height:400px; left:0px; top:' . ($intDateHeight * 2 + 11) . 'px" class="whitebackground" id="weeklynames">';
        $counter = 0;
        $intCurrTop = 0;
        if (isset($arrAllocations['Grouped'])) {
            foreach ($arrAllocations['Grouped'] as $intGroupID => $arrGroupedAllocations) {
                echo '<div class="ProdGroup" style="position: absolute; width:100%; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
                echo '</div>';
                $counter++;
                $intCurrTop = $intCurrTop + $intRowHeight;
                foreach ($arrGroupedAllocations as $strDutyName => $arrAllocation) {
                    $intThisRowHeight = ($arrAllocation['CountPeople']) * $intHeightMultiply;
                    if ($intThisRowHeight < $intMinHeight) {
                        $intThisRowHeight = $intMinHeight;
                    }
                    echo '<div class="names handcursor" style="position: absolute; width:100%; height:' . ($intThisRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
                    echo '<b>' . SanitiseDuty($strDutyName);
                    echo '</b>';
                    echo '</div>';
                    $intCurrTop = $intCurrTop + $intThisRowHeight;
                }
            }
        }
        // Any non grouped allocateions?
        if (count($arrAllocations['Duties']) > 0) {
            echo '<div class="ProdGroup handcursor" style="position: absolute; width:100%; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
            echo '</div>';
            $intCurrTop = $intCurrTop + $intRowHeight;

            foreach ($arrAllocations['Duties'] as $strDutyName => $arrAllocation) {
                $intThisRowHeight = ($arrAllocation['CountPeople']) * $intHeightMultiply;
                if ($intThisRowHeight < $intMinHeight) {
                    $intThisRowHeight = $intMinHeight;
                }
                echo '<div class="names handcursor" style="position: absolute; width:100%; height:' . ($intThisRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
                echo '<b>' . SanitiseDuty($strDutyName);
                echo '</b>';
                echo '</div>';
                $intCurrTop = $intCurrTop + $intThisRowHeight;
            }
        }
        // The AvailableStaff
        if (isset($arrAllocations['AvailableStaff'])) {
            echo '<div class="ProdGroup handcursor" style="position: absolute; width:100%; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
            echo '</div>';
            $intCurrTop = $intCurrTop + $intRowHeight;

            foreach ($arrAllocations['AvailableStaff'] as $strDutyName => $arrAllocation) {
                $intThisRowHeight = ($arrAllocation['CountPeople']) * $intHeightMultiply;
                if ($intThisRowHeight < $intMinHeight) {
                    $intThisRowHeight = $intMinHeight;
                }
                echo '<div class="names handcursor" style="position: absolute; width:100%; height:' . ($intThisRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
                echo '<b>' . SanitiseDuty($strDutyName);
                echo '</b>';
                echo '</div>';
                $intCurrTop = $intCurrTop + $intThisRowHeight;
            }
        }
        // The Leave
        if (isset($arrAllocations['Leave'])) {
            echo '<div class="ProdGroup handcursor" style="position: absolute; width:100%; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px"zz>';
            echo '</div>';
            $intCurrTop = $intCurrTop + $intRowHeight;

            foreach ($arrAllocations['Leave'] as $strDutyName => $arrAllocation) {
                $intThisRowHeight = ($arrAllocation['CountPeople']) * $intHeightMultiply;
                if ($intThisRowHeight < $intMinHeight) {
                    $intThisRowHeight = $intMinHeight;
                }
                echo '<div class="names handcursor" style="position: absolute; width:100%; height:' . ($intThisRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
                echo '<b>' . SanitiseDuty($strDutyName);
                echo '</b>';
                echo '</div>';
                $intCurrTop = $intCurrTop + $intThisRowHeight;
            }
        }
        echo '</div>';
        // ############################################################################### End the names
        // The Duties
        // The duties in the div
        echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:900px; height:400px; left:' . $intNamesWidth . 'px; top:' . ($intDateHeight * 2 + 11) . 'px" id="weeklyduties" class="whitebackground">';
        $intCurrTop = 0;
        if (isset($arrAllocations['Grouped'])) {
            foreach ($arrAllocations['Grouped'] as $intGroupID => $arrGroupedAllocations) {
                echo '<div class="ProdGroup prodGroup_duties" style="position: absolute; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
                echo $arrGroups[$intGroupID]['Description'];
                echo '</div>';
                $intCurrTop = $intCurrTop + $intRowHeight;
                $counter++;
                foreach ($arrGroupedAllocations as $strDutyName => $arrAllocation) {
                    $intThisRowHeight = ($arrAllocation['CountPeople']) * $intHeightMultiply;
                    if ($intThisRowHeight < $intMinHeight) {
                        $intThisRowHeight = $intMinHeight;
                    }
                    for ($i = 0; $i < $intDayCount; $i++) {
                        $actualDayIndex = $i + $intDayStart;
                        $strCurrDate = date("Y-m-d", strtotime("+" . $actualDayIndex . " days", strtotime($dteStartDate)));
                        if (isset($arrHiddenDays[$intTeamID][$strCurrDate]) && $intCanViewComments == 0) {
                            echo '<div id="dutyshift_' . $x . '_' . $i . '" style="overflow-y:auto; overflow-x:hidden; width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intCurrTop) . 'px; height:' . ($intThisRowHeight - 1) . 'px" class="DutyCellNotWorking handcursor">';
                            echo '</div>';
                        } else {
                            $intDOTW = date("N", strtotime($strCurrDate));
                            if ($intDOTW == 6 || $intDOTW == 7) {
                                if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                    $CellClass = 'DutyCellWorkingHashed';
                                } else {
                                    if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                        if (strpos(strtoupper($strDutyName), 'WFH') !== false) {
                                            $CellClass = 'PaleHashedYellow';
                                        } else {
                                            $CellClass = 'DutyCellWorkingHashed';
                                        }
                                    } else {
                                        $CellClass = 'DutyCellNotWorkingHashed';
                                    }
                                }
                            } else {
                                if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                    $CellClass = 'DutyCellWorking';
                                } else {
                                    if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                        if (strpos(strtoupper($strDutyName), 'WFH') !== false) {
                                            $CellClass = 'VeryLightYellow';
                                        } else {
                                            $CellClass = 'DutyCellWorking';
                                        }
                                    } else {
                                        $CellClass = 'DutyCellNotWorking';
                                    }
                                }
                            }
                            echo '<div id="dutyshift_' . $x . '_' . $i . '" style="overflow-y:auto; overflow-x:hidden; width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intCurrTop) . 'px; height:' . ($intThisRowHeight - 1) . 'px" class="' . $CellClass . ' handcursor">';
                            if (isset($arrAllocation[$actualDayIndex]['Names']) || isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                echo '<table width="100%">';
                                if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                    foreach ($arrAllocation[$actualDayIndex]['Names'] as $strStaffNumber => $arrName) {
                                        if (($intCanMakeEdited == 1) && isset($arrEditable[$strCurrDate]) && ($arrEditable[$strCurrDate]['IsDayEditable'] == 1) && ($arrName['SchedulingTeamId'] == $intTeamID)) {
                                            $strEditClass = ' class="assigned-context-menu"';
                                        } else {
                                            $strEditClass = '';
                                        }
                                        echo '<tr><td intteamid ="' . $intTeamID . '" intMarkabsent ="' . $intMarkabsent . '" id="' . $arrName['ID'] . '" allocationSPID="' . $arrName['allocationSPID'] . '" allocationId="' . $arrName['allocationId'] . '" scheduledpersonid="' . $arrName['scheduledpersonid'] . '" weeknum="' . $arrName['weeknum'] . '" dutydate="' . $arrName['dutydate'] . '"' . $strEditClass . ' nowrap title="' . $arrName['FullName'];
                                        echo '<br>' . $arrName['StartTime'] . '-' . $arrName['EndTime'];
                                        if (($arrName['LeaveStartTime'] != 0) || ($arrName['LeaveEndTime'] != 0)) {
                                            $pdlStartTime = (int) $arrName['LeaveStartTime'];
                                            $pdlEndTime = (int) $arrName['LeaveEndTime'];
                                            if ($pdlStartTime > $pdlEndTime) {
                                                $pdlEndTime = (86400 + $pdlEndTime);
                                            }
                                            echo '<br>PDL: ' . $service->convertSecondsIntoTime($pdlStartTime, ':', 'No') . '-' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') . ' | ' . $service->convertSecondsIntoTime(($pdlEndTime - $pdlStartTime), '.', 'Yes');
                                        }
                                        echo '" data-pdl-starttime="' . $arrName['LeaveStartTime'] . '" data-pdl-endtime="' . $arrName['LeaveEndTime'] . '">';
                                        if (
                                            isset($arrName['isDuplicate']) && ($intCanViewComments == 1 || $intIsShiftLeader == 1
                                            )
                                        ) {
                                            echo '<font color="#009900">';
                                        }

                                        $nameColorStyle = '';
                                        if ($arrName['HasSevenFlag'] == 1) {
                                            $nameColorStyle = ' style="color: #2a2ecb;"';
                                        }

                                        if ($intDutyWidth < $intMinWidth) {
                                            echo $arrName['Initials'];
                                        } else {
                                            if (($arrName['LeaveStartTime'] != 0) || ($arrName['LeaveEndTime'] != 0)) {
                                                echo '<span style="width: 100px; white-space:nowrap;">' . $arrName['FullName'] . '<span style="color:#109146; padding-left:4px;">[L]</span></span>';
                                            } else {
                                                echo '<span' . $nameColorStyle . '>' . $arrName['FullName'] . '</span>';
                                            }
                                        }
                                        if (isset($arrName['isDuplicate']) && ($intCanViewComments == 1 || $intIsShiftLeader == 1)) {
                                            echo '</font>';
                                        }
                                        echo '</td>';
                                        if (isset($arrName['Jobs']) && is_array($arrName['Jobs']) && count($arrName['Jobs']) > 0 && $arrTeamDefaults[$intTeamID]['HasJobsInWeeklyView'] == 1) {
                                            $jobData = filterJobs($arrName['Jobs']);
                                            if (!empty($jobData)) {
                                                echo '<td style="display:block;padding-top: 0;"><span class="view-job-details-icon" data-job-detail=\'' . json_encode($jobData) . '\' style="float:right"><i class="fa fa-circle" style="color: blueviolet; ' . ($arrName['HasComments'] == '1' ? 'margin-right: 20px;' : '') . '" aria-hidden="true"></i></span>';
                                            }
                                        }
                                        echo '<td style="display:block;padding-top:0px;">';
                                        if ($arrName['HasComments'] == '1') {
                                            if (isset($arrEditable[$strCurrDate]['IsDayEditable'])) {
                                                $viewcomment = $arrEditable[$strCurrDate]['IsDayEditable'];
                                            } else {
                                                $viewcomment = 0;
                                            }
                                            echo '<span class="tipremotecomments"  style="display: inline; position: absolute;right: 3px;" teamid= "' . $intTeamID . '"  dutyId= "' . $arrName['ID'] . '" DutyDate="' . $strCurrDate . '" SchedulingPersonID="' . $strStaffNumber . '" intCanViewComments="' . $viewcomment . '">';
                                            echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
                                            echo '</span>';
                                        }
                                        echo '</td></tr>';
                                    }
                                }
                                if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                    // If it;s editable then set the class
                                    if (($intCanMakeEdited == 1) && isset($arrEditable[$strCurrDate]['IsDayEditable']) && ($arrEditable[$strCurrDate]['IsDayEditable'] == 1) && ($arrAllocation[$actualDayIndex]['SchedulingTeamId'] == $intTeamID)) {
                                        $strEditClass = ' class="unassigned-context-menu"';
                                    } else {
                                        $strEditClass = '';
                                    }
                                    echo '<tr><td' . $strEditClass . ' id="' . $arrAllocation[$actualDayIndex]['ID'] . '" week="' . $arrAllocation[$actualDayIndex]['WeekNumber'] . '" day="' . $arrAllocation[$actualDayIndex]['iDay'] . '" intteamid="' . $intTeamID . '" intMarkabsent ="' . $intMarkabsent . '" curdate="' . $strCurrDate . '" dutyName="' . $arrAllocation[$actualDayIndex]['dutyName'] . '">';
                                    if ($arrAllocation[$actualDayIndex]['NeedsCover'] == 1) {
                                        echo '<font color="#990000"><b>' . $strUncovered . '</b></font>';
                                    } else {
                                        echo '<font color="#990000"><b>' . $strUncovered . ' (' . $arrAllocation[$actualDayIndex]['NeedsCover'] . ')</b></font>';
                                    }
                                    echo '</td></tr>';
                                }
                                echo '</table>';
                            }
                            echo '</div>';
                        } //esle close
                    }
                    $intCurrTop = $intCurrTop + $intThisRowHeight;
                    $counter++;
                    $x++;
                }
            }
        }

        if (count($arrAllocations['Duties']) > 0) {
            echo '<div class="ProdGroup prodGroup_duties" style="position: absolute; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
            echo '</div>';
            $intCurrTop = $intCurrTop + $intRowHeight;
            foreach ($arrAllocations['Duties'] as $strDutyName => $arrAllocation) {
                $intThisRowHeight = ($arrAllocation['CountPeople']) * $intHeightMultiply;
                if ($intThisRowHeight < $intMinHeight) {
                    $intThisRowHeight = $intMinHeight;
                }
                for ($i = 0; $i < $intDayCount; $i++) {
                    $actualDayIndex = $i + $intDayStart;
                    $strCurrDate = date("Y-m-d", strtotime("+" . $actualDayIndex . " days", strtotime($dteStartDate)));
                    if (isset($arrHiddenDays[$intTeamID][$strCurrDate]) && $intCanViewComments == 0) {
                        echo '<div id="dutyshift_' . $x . '_' . $i . '" style="overflow-y:auto; overflow-x:hidden; width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intCurrTop) . 'px; height:' . ($intThisRowHeight - 1) . 'px" class="DutyCellNotWorking handcursor">';
                        echo '</div>';
                    } else {
                        $intDOTW = date("N", strtotime($strCurrDate));
                        if ($intDOTW == 6 || $intDOTW == 7) {
                            if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                $CellClass = 'DutyCellWorkingHashed';
                            } else {
                                if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                    if (strpos(strtoupper($strDutyName), 'WFH') !== false) {
                                        $CellClass = 'PaleHashedYellow';
                                    } else {
                                        $CellClass = 'DutyCellWorkingHashed';
                                    }
                                } else {
                                    $CellClass = 'DutyCellNotWorkingHashed';
                                }
                            }
                        } else {
                            if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                $CellClass = 'DutyCellWorking';
                            } else {
                                if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                    if (strpos(strtoupper($strDutyName), 'WFH') !== false) {
                                        $CellClass = 'VeryLightYellow';
                                    } else {
                                        $CellClass = 'DutyCellWorking';
                                    }
                                } else {
                                    $CellClass = 'DutyCellNotWorking';
                                }
                            }
                        }
                        echo '<div id="dutyshift_' . $x . '_' . $i . '" style="overflow-y:auto; overflow-x:hidden; width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intCurrTop) . 'px; height:' . ($intThisRowHeight - 1) . 'px" class="' . $CellClass . ' handcursor">';
                        if (isset($arrAllocation[$actualDayIndex]['Names']) || isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                            echo '<table width="100%">';
                            if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                foreach ($arrAllocation[$actualDayIndex]['Names'] as $strStaffNumber => $arrName) {
                                    if ((isset($arrEditable[$strCurrDate]['IsDayEditable'])) && ($intCanMakeEdited == 1) && ($arrEditable[$strCurrDate]['IsDayEditable'] == 1) && ($arrName['SchedulingTeamId'] == $intTeamID)) {
                                        $strEditClass = ' class="assigned-context-menu"';
                                    } else {
                                        $strEditClass = '';
                                    }
                                    echo '<tr><td intteamid ="' . $intTeamID . '" intMarkabsent ="' . $intMarkabsent . '" id="' . $arrName['ID'] . '" allocationSPID="' . $arrName['allocationSPID'] . '" allocationId="' . $arrName['allocationId'] . '" scheduledpersonid="' . $arrName['scheduledpersonid'] . '" weeknum="' . $arrName['weeknum'] . '" dutydate="' . $arrName['dutydate'] . '"' . $strEditClass . ' nowrap title="' . $arrName['FullName'];
                                    echo '<br>' . $arrName['StartTime'] . '-' . $arrName['EndTime'];
                                    if (($arrName['LeaveStartTime'] != 0) || ($arrName['LeaveEndTime'] != 0)) {
                                        $pdlStartTime = (int) $arrName['LeaveStartTime'];
                                        $pdlEndTime = (int) $arrName['LeaveEndTime'];
                                        if ($pdlStartTime > $pdlEndTime) {
                                            $pdlEndTime = (86400 + $pdlEndTime);
                                        }
                                        echo '<br>PDL: ' . $service->convertSecondsIntoTime($pdlStartTime, ':', 'No') . '-' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') . ' | ' . $service->convertSecondsIntoTime(($pdlEndTime - $pdlStartTime), '.', 'Yes');
                                    }
                                    echo '" data-pdl-starttime="' . $arrName['LeaveStartTime'] . '" data-pdl-endtime="' . $arrName['LeaveEndTime'] . '">';

                                    $nameColorStyle = '';
                                    if ($arrName['HasSevenFlag'] == 1) {
                                        $nameColorStyle = ' style="color: #2a2ecb;"';
                                    }

                                    if ($intDutyWidth < $intMinWidth) {
                                        echo $arrName['Initials'];
                                    } else {
                                        if (($arrName['LeaveStartTime'] != 0) || ($arrName['LeaveEndTime'] != 0)) {
                                            echo '<span style="width: 100px; white-space:nowrap;">' . $arrName['FullName'] . '<span style="color:#109146; padding-left:4px;">[L]</span></span>';
                                        } else {
                                            echo '<span' . $nameColorStyle . '>' . $arrName['FullName'] . '</span>';
                                        }
                                    }
                                    echo '</td>';
                                    if (is_array($arrName['Jobs']) && count($arrName['Jobs']) > 0 && $arrTeamDefaults[$intTeamID]['HasJobsInWeeklyView'] == 1) {
                                        $jobData = filterJobs($arrName['Jobs']);
                                        if (!empty($jobData)) {
                                            echo '<td style="display:block;padding-top: 0;"><span class="view-job-details-icon" data-job-detail=\'' . json_encode($jobData) . '\' style="float:right"><i class="fa fa-circle" style="color: blueviolet; ' . ($arrName['HasComments'] == '1' ? 'margin-right: 20px;' : '') . '" aria-hidden="true"></i></span>';
                                        }
                                    }
                                    echo '<td style="display:block;padding-top:0px;">';
                                    if ($arrName['HasComments'] == '1') {
                                        if (isset($arrEditable[$strCurrDate]['IsDayEditable'])) {
                                            $viewcomment = $arrEditable[$strCurrDate]['IsDayEditable'];
                                        } else {
                                            $viewcomment = 0;
                                        }
                                        echo '<span class="tipremotecomments"  style="display: inline; position: absolute; right: 3px;" teamid= "' . $intTeamID . '"  dutyId= "' . $arrName['ID'] . '" DutyDate="' . $strCurrDate . '" SchedulingPersonID="' . $strStaffNumber . '" intCanViewComments="' . $viewcomment
                                            . '">';
                                        echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
                                        echo '</span>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                            if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                // If it;s editable then set the class
                                if ((isset($arrEditable[$strCurrDate]['IsDayEditable'])) && ($intCanMakeEdited == 1) && ($arrEditable[$strCurrDate]['IsDayEditable'] == 1) && ($arrAllocation[$actualDayIndex]['SchedulingTeamId'] == $intTeamID)) {
                                    $strEditClass = ' class="unassigned-context-menu"';
                                } else {
                                    $strEditClass = '';
                                }

                                echo '<tr><td' . $strEditClass . ' id="' . $arrAllocation[$actualDayIndex]['ID'] . '" week="' . $arrAllocation[$actualDayIndex]['WeekNumber'] . '" day="' . $arrAllocation[$actualDayIndex]['iDay'] . '" intTeamID="' . $intTeamID . '" intMarkabsent ="' . $intMarkabsent . '" curdate="' . $strCurrDate . '" dutyName="' . $arrAllocation[$actualDayIndex]['dutyName'] . '">';
                                if ($arrAllocation[$actualDayIndex]['NeedsCover'] == 1) {
                                    echo '<font color="#990000"><b>' . $strUncovered . '</b></font>';
                                } else {
                                    echo '<font color="#990000"><b>' . $strUncovered . ' (' . $arrAllocation[$actualDayIndex]['NeedsCover'] . ')</b></font>';
                                }
                                echo '</td></tr>';
                            }
                            echo '</table>';
                        }
                        echo '</div>';
                    }
                }
                $x++;
                $intCurrTop = $intCurrTop + $intThisRowHeight;
            }
        }
        //Available Staff
        if (isset($arrAllocations['AvailableStaff'])) {
            echo '<div class="ProdGroup prodGroup_duties" style="position: absolute; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
            echo 'Available Staff';
            echo '</div>';
            $intCurrTop = $intCurrTop + $intRowHeight;
            foreach ($arrAllocations['AvailableStaff'] as $strDutyName => $arrAllocation) {

                $intThisRowHeight = ($arrAllocation['CountPeople']) * $intHeightMultiply;
                if ($intThisRowHeight < $intMinHeight) {
                    $intThisRowHeight = $intMinHeight;
                }
                for ($i = 0; $i < $intDayCount; $i++) {
                    $actualDayIndex = $i + $intDayStart;
                    $strCurrDate = date("Y-m-d", strtotime("+" . $actualDayIndex . " days", strtotime($dteStartDate)));
                    if (isset($arrHiddenDays[$intTeamID][$strCurrDate]) && $intCanViewComments == 0) {
                        echo '<div id="dutyshift_' . $x . '_' . $i . '" style="overflow-y:auto; overflow-x:hidden; width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intCurrTop) . 'px; height:' . ($intThisRowHeight - 1) . 'px" class="DutyCellNotWorking handcursor">';
                        echo '</div>';
                    } else {
                        if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                            $CellClass = 'DutyCellWorkingHashed';
                        } else {
                            if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                $CellClass = 'DutyCellWorking';
                            } else {
                                $CellClass = 'DutyCellNotWorking';
                            }
                        }
                        echo '<div id="dutyshift_' . $x . '_' . $i . '" style="overflow-y:auto; overflow-x:hidden; width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intCurrTop) . 'px; height:' . ($intThisRowHeight - 1) . 'px" class="' . $CellClass . ' handcursor">';
                        if (isset($arrAllocation[$actualDayIndex]['Names']) || isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                            echo '<table width="100%">';
                            if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                foreach ($arrAllocation[$actualDayIndex]['Names'] as $strStaffNumber => $arrName) {
                                    if (($intCanMakeEdited == 1) && (isset($arrEditable[$strCurrDate]) && $arrEditable[$strCurrDate]['IsDayEditable'] == 1) && ($arrName['SchedulingTeamId'] == $intTeamID)) {
                                        $strEditClass = ' class="assigned-context-menu"';
                                    } else {
                                        $strEditClass = '';
                                    }
                                    echo '<tr><td intteamid ="' . $intTeamID . '" intMarkabsent ="' . $intMarkabsent . '" id="' . $arrName['ID'] . '" allocationSPID="' . $arrName['allocationSPID'] . '" allocationId="' . $arrName['allocationId'] . '" scheduledpersonid="' . $arrName['scheduledpersonid'] . '" weeknum="' . $arrName['weeknum'] . '" dutydate="' . $arrName['dutydate'] . '"' . $strEditClass . ' nowrap title="' . $arrName['FullName'];
                                    echo '<br>' . $arrName['StartTime'] . '-' . $arrName['EndTime'];
                                    if (($arrName['LeaveStartTime'] != 0) || ($arrName['LeaveEndTime'] != 0)) {
                                        $pdlStartTime = (int) $arrName['LeaveStartTime'];
                                        $pdlEndTime = (int) $arrName['LeaveEndTime'];
                                        if ($pdlStartTime > $pdlEndTime) {
                                            $pdlEndTime = (86400 + $pdlEndTime);
                                        }
                                        echo '<br>PDL: ' . $service->convertSecondsIntoTime($pdlStartTime, ':', 'No') . '-' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') . ' | ' . $service->convertSecondsIntoTime(($pdlEndTime - $pdlStartTime), '.', 'Yes');
                                    }
                                    echo '<br>Duration : ' . $arrName['Duration'] . ' Hours';
                                    echo '" data-pdl-starttime="' . $arrName['LeaveStartTime'] . '" data-pdl-endtime="' . $arrName['LeaveEndTime'] . '">';

                                    $nameColorStyle = '';
                                    if ($arrName['HasSevenFlag'] == 1) {
                                        $nameColorStyle = ' style="color: #2a2ecb;"';
                                    }

                                    if ($intDutyWidth < $intMinWidth) {
                                        echo $arrName['Initials'];
                                    } else {
                                        if (($arrName['LeaveStartTime'] != 0) || ($arrName['LeaveEndTime'] != 0)) {
                                            echo '<span style="width: 100px; white-space:nowrap;">' . $arrName['FullName'] . '<span style="color:#109146; padding-left:4px;">[L]</span></span>';
                                        } else {
                                            echo '<span' . $nameColorStyle . '>' . $arrName['FullName'] . '</span>';
                                        }
                                    }
                                    echo '</td>';
                                    if (isset($arrName['Jobs']) && is_array($arrName['Jobs']) && count($arrName['Jobs']) > 0 && $arrTeamDefaults[$intTeamID]['HasJobsInWeeklyView'] == 1) {
                                        $jobData = filterJobs($arrName['Jobs']);
                                        if (!empty($jobData)) {
                                            echo '<td style="display:block;padding-top: 0;"><span class="view-job-details-icon" data-job-detail=\'' . json_encode($jobData) . '\' style="float:right"><i class="fa fa-circle" style="color: blueviolet; ' . ($arrName['HasComments'] == '1' ? 'margin-right: 20px;' : '') . '" aria-hidden="true"></i></span>';
                                        }
                                    }
                                    echo '<td style="display:block;padding-top:0px;">';
                                    if (isset($arrName['HasComments']) && $arrName['HasComments'] == '1') {
                                        if (isset($arrEditable[$strCurrDate]['IsDayEditable'])) {
                                            $viewcomment = $arrEditable[$strCurrDate]['IsDayEditable'];
                                        } else {
                                            $viewcomment = 0;
                                        }
                                        echo '<span class="tipremotecomments"  style="display: inline; position: absolute; right: 3px;" teamid= "' . $intTeamID . '"  dutyId= "' . $arrName['ID'] . '" DutyDate="' . $strCurrDate . '" SchedulingPersonID="' . $strStaffNumber . '" intCanViewComments="' . $viewcomment . '">';
                                        echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
                                        echo '</span>';
                                    }
                                    echo '</td></tr>';
                                }
                            }
                            if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                echo '<tr><td>';
                                if ($arrAllocation[$actualDayIndex]['NeedsCover'] == 1) {
                                    echo '<font color="#990000"><b>' . $strUncovered . '</b></font>';
                                } else {
                                    echo '<font color="#990000"><b>' . $strUncovered . ' (' . $arrAllocation[$actualDayIndex]['NeedsCover'] . ')</b></font>';
                                }
                                echo '</td></tr>';
                            }
                            echo '</table>';
                        }
                        echo '</div>';
                    }
                }
                $x++;
                $intCurrTop = $intCurrTop + $intThisRowHeight;
            }
        }
        // Do any Leave
        if (isset($arrAllocations['Leave'])) {
            echo '<div class="ProdGroup prodGroup_duties" style="position: absolute; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($intCurrTop) . 'px">';
            echo 'Leave';
            echo '</div>';
            $intCurrTop = $intCurrTop + $intRowHeight;
            foreach ($arrAllocations['Leave'] as $strDutyName => $arrAllocation) {
                $intThisRowHeight = ($arrAllocation['CountPeople']) * $intHeightMultiply;
                if ($intThisRowHeight < $intMinHeight) {
                    $intThisRowHeight = $intMinHeight;
                }
                for ($i = 0; $i < $intDayCount; $i++) {
                    $actualDayIndex = $i + $intDayStart;
                    $strCurrDate = date("Y-m-d", strtotime("+" . $actualDayIndex . " days", strtotime($dteStartDate)));
                    if (isset($arrHiddenDays[$intTeamID][$strCurrDate]) && $intCanViewComments == 0) {
                        echo '<div id="dutyshift_' . $x . '_' . $i . '" style="overflow-y:auto; overflow-x:hidden; width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intCurrTop) . 'px; height:' . ($intThisRowHeight - 1) . 'px" class="DutyCellNotWorking handcursor">';
                        echo '</div>';
                    } else {
                        if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                            $CellClass = 'DutyCellWorkingHashed';
                        } else {
                            if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                $CellClass = 'DutyCellWorking';
                            } else {
                                $CellClass = 'DutyCellNotWorking';
                            }
                        }
                        echo '<div id="dutyshift_' . $x . '_' . $i . '" style="overflow-y:auto; overflow-x:hidden; width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($intCurrTop) . 'px; height:' . ($intThisRowHeight - 1) . 'px" class="' . $CellClass . ' handcursor">';
                        if (isset($arrAllocation[$actualDayIndex]['Names']) || isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                            echo '<table width="100%">';
                            if (isset($arrAllocation[$actualDayIndex]['Names'])) {
                                foreach ($arrAllocation[$actualDayIndex]['Names'] as $strStaffNumber => $arrName) {
                                    echo '<tr><td nowrap title="' . $arrName['FullName'];
                                    if ((isset($arrName['LeaveStartTime']) && $arrName['LeaveStartTime'] != 0) || (isset($arrName['LeaveEndTime']) && $arrName['LeaveEndTime'] != 0)) {
                                        $pdlStartTime = (int) ($arrName['LeaveStartTime'] ?? '');
                                        $pdlEndTime = (int) ($arrName['LeaveEndTime'] ?? '');
                                        if ($pdlStartTime > $pdlEndTime) {
                                            $pdlEndTime = (86400 + $pdlEndTime);
                                        }
                                        echo '<br>PDL: ' . $service->convertSecondsIntoTime($pdlStartTime, ':', 'No') . '-' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') . ' | ' . $service->convertSecondsIntoTime(($pdlEndTime - $pdlStartTime), '.', 'Yes');
                                    }
                                    echo '">';

                                    $nameColorStyle = '';
                                    if (isset($arrName['HasSevenFlag']) && $arrName['HasSevenFlag'] == 1) {
                                        $nameColorStyle = ' style="color: #2a2ecb;"';
                                    }

                                    if ($intDutyWidth < $intMinWidth) {
                                        echo $arrName['Initials'];
                                    } else {
                                        if ((isset($arrName['LeaveStartTime']) && $arrName['LeaveStartTime'] != 0) || (isset($arrName['LeaveEndTime']) && $arrName['LeaveEndTime'] != 0)) {
                                            echo '<span style="width: 100px; white-space:nowrap;">' . $arrName['FullName'] . '<span style="color:#109146; padding-left:4px;">[L]</span></span>';
                                        } else {
                                            echo '<span' . $nameColorStyle . '>' . $arrName['FullName'] . '</span>';
                                        }
                                    }
                                    echo '</td>';
                                    if (isset($arrName['Jobs']) && is_array($arrName['Jobs']) && count($arrName['Jobs']) > 0 && $arrTeamDefaults[$intTeamID]['HasJobsInWeeklyView'] == 1) {
                                        $jobData = filterJobs($arrName['Jobs']);
                                        if (!empty($jobData)) {
                                            echo '<td style="display:block;padding-top: 0;"><span class="view-job-details-icon" data-job-detail=\'' . json_encode($jobData) . '\' style="float:right"><i class="fa fa-circle" style="color: blueviolet; ' . ($arrName['HasComments'] == '1' ? 'margin-right: 20px;' : '') . '" aria-hidden="true"></i></span>';
                                        }
                                    }
                                    echo '<td style="display:block;padding-top:0px;">';
                                    if (isset($arrName['HasComments']) && $arrName['HasComments'] == '1') {
                                        if (isset($arrEditable[$strCurrDate]['IsDayEditable'])) {
                                            $viewcomment = $arrEditable[$strCurrDate]['IsDayEditable'];
                                        } else {
                                            $viewcomment = 0;
                                        }
                                        echo '<span class="tipremotecomments"  style="display: inline; position: absolute;right: 3px;" teamid= "' . $intTeamID . '"  dutyId= "' . $arrName['ID'] . '" DutyDate="' . $strCurrDate . '" SchedulingPersonID="' . $strStaffNumber . '" intCanViewComments="' . $viewcomment . '">';
                                        echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
                                        echo '</span>';
                                    }
                                    echo '</td></tr>';
                                }
                            }
                            if (isset($arrAllocation[$actualDayIndex]['NeedsCover'])) {
                                echo '<tr><td>';
                                if ($arrAllocation[$actualDayIndex]['NeedsCover'] == 1) {
                                    echo '<font color="#990000"><b>' . $strUncovered . '</b></font>';
                                } else {
                                    echo '<font color="#990000"><b>' . $strUncovered . ' (' . $arrAllocation[$actualDayIndex]['NeedsCover'] . ')</b></font>';
                                }
                                echo '</td></tr>';
                            }
                            echo '</table>';
                        }
                        echo '</div>';
                    }
                }
                $x++;
                $intCurrTop = $intCurrTop + $intThisRowHeight;
            }
        }
        echo '</div>';
        echo '<br><br><br><br>';
        echo '</div>';
    } else {
        echo '<br>';
        echo '<div class="tableheadersmall bigtextboldcentre"';
        echo '<br><br>';
        echo 'Unable to display the allocations for this week<br>';
        echo '<br></br><br>';
        echo '</div>';
    }
    echo '<br><br><br><br>';
?>
    <script type="text/javascript" src="js/allocations/weekly/weekly-jobs-view.js?v=<?php echo time(); ?>"></script>
    <script language="JavaScript" type="text/javascript">
        ResizeWeeklyGrids();
        $(window).resize(function() {
            ResizeWeeklyGrids();
        })
        $(function() {
            dateCommentOptionsSet(2, 'fa-2x', 'lightblue');
            <?php echo ($dateCommentIsShiftLeader == 1) ? 'initializeDateComment()' : 'initializeDateCommentViewIcon()' ?>;
        });
        $(document).ready(function() {
            ResizeWeeklyGrids();
            // If cookie is set, scroll to the position saved in the cookie.
            // if ($.cookie("vscroll") !== null) {
            //     $("#weeklynames").scrollTop($.cookie("W-vscroll"));
            //     $("#weeklyduties").scrollTop($.cookie("W-vscroll"));
            //     $("#weeklyduties").scrollLeft($.cookie("W-hscroll"));
            // }
            // When scrolling happens....
            $("#weeklyduties").on("scroll", function() {
                // Set a cookie that holds the scroll position.
                // $.cookie("W-vscroll", $("#weeklyduties").scrollTop());
                // $.cookie("W-hscroll", $("#weeklyduties").scrollLeft());
                $('#weeklynames').scrollTop($(this).scrollTop());
                $('#weeklytop').scrollLeft($(this).scrollLeft());
                $('.weeklytop-week').scrollLeft($(this).scrollLeft());
            });

            $(".chosen-select").chosen({
                no_results_text: "Oops, nothing found!",
                width: "200px"
            });
            $(function() {
                $("#accordion").accordion({
                    heightStyle: "content",
                    collapsible: true,
                    active: false,
                    beforeActivate: function(event, ui) {
                        $('*').qtip('hide');
                    }
                });
                $('.ui-accordion-content').css({
                    "padding": "2px",
                    "width": "500px",
                    "position": "absolute",
                    "right": "0"
                });
                $('.accordiontop').css({
                    "width": "250px"
                });
            });
            $('[title]').qtip({
                position: {
                    viewport: $(window)
                },
                style: {
                    classes: 'qtip-rounded qtip-shadow qtip-light'
                }
            });

            $('.tipremotecomments').each(function() {
                $(this).qtip({
                    content: {
                        text: function(event, api) {
                            var reqData = {
                                'dutyDate': api.elements.target.attr('DutyDate'),
                                'schPersonId': api.elements.target.attr('SchedulingPersonID'),
                                'teamId': api.elements.target.attr('teamid'),
                                'dutyId': api.elements.target.attr('dutyId'),
                                'rolepermission': api.elements.target.attr('intCanViewComments'),
                                'screen': 'productionview'
                            };
                            $.ajax({
                                    type: "post",
                                    data: reqData,
                                    url: 'page-includes/allocations/allocations-duty-comments.php'
                                })
                                .then(function(content) {
                                        // Set the tooltip content upon successful retrieval
                                        api.set('content.text', content);
                                    },
                                    function(xhr, status, error) {
                                        // Upon failure... set the tooltip content to error
                                        api.set('content.text', status + ': ' + error);
                                    });
                            return 'Loading...'; // Set some initial text
                        }
                    },
                    position: {
                        viewport: $(window)
                    },
                    style: 'qtip-rounded qtip-shadow qtip-light'
                });
            });
        });

        <?php
        if ((isset($arrAllocations['Duties'])) || (isset($arrAllocations['Grouped'])) || (isset($arrAllocations['AvailableStaff'])) || (isset($arrAllocations['Leave']))) {
        ?>
            ResizeWeeklyGrids();
            $('#weeklynames').on('scroll', function() {
                $('#weeklyduties').scrollTop($(this).scrollTop());
            });

            $(window).resize(function() {
                if ($("#weeklyduties").length) {
                    ResizeWeeklyGrids();
                }
            })
        <?php
        }
        ?>
        $('#FormDutyName1').validate({
            rules: {
                "filter": {
                    required: true,
                },
            },
            submitHandler: function(form) {
                $.ajax({
                    type: 'POST',
                    url: 'page-includes/allocations/allocations-setfilter.php',
                    data: $('#FormDutyName1').serialize(),
                    success: function(data) {
                        ShowAllocationsDuties('<?php echo $intTeamID ?>');
                    }
                });
            }
        })

        function ResizeWeeklyGrids() {
            var weeklytopOffset = $("#weeklytop").offset();
            var contentOffset = $("#content").offset();
            if (!weeklytopOffset || !contentOffset) {
                return;
            }
            var offset = weeklytopOffset.top + contentOffset.top;


            var counter = <?php echo isset($x) && !empty($x) ? $x : 0; ?>;
            var intDayCount = <?php echo $intDayCount; ?>;
            var intDayStart = <?php echo $intDayStart; ?>;

            var windowwidth = $(window).width() - 40 - <?php echo $intNamesWidth; ?>;
            var widowheight = $(window).height() - offset - 31;
            $("#weeklyduties").width(windowwidth + 20).height(widowheight);
            $(".bigtextboldcentre").css({
                'width': windowwidth
            });
            $("#weeklynames").height(widowheight);
            $("#weeklytop").width(windowwidth);
            let cellwidth = windowwidth / intDayCount;
            $(".prodGroup_duties").css({
                'width': cellwidth * intDayCount
            });
            var zoom = window.devicePixelRatio || 1;
            var widthOffset = 0;
            var z = Math.round(zoom * 100) / 100;
            if (z >= 1.5) {
                widthOffset = 1;
            } else if (z <= 1.35 && z > 1.25) {
                widthOffset = 1.3;
            } else if (z <= 1.25 && z > 0.75) {
                widthOffset = 1.5;
            } else {
                widthOffset = 2;
            }
            for (let j = 0; j <= counter; j++) {
                for (let k = 0; k < intDayCount; k++) {
                    let cellleft = k * cellwidth;
                    $("#fshift" + k).css("width", cellwidth);
                    $("#fshift" + k).css("left", cellleft);
                    $("#fshift-b" + k).css("width", cellwidth);
                    $("#fshift-b" + k).css("left", cellleft);
                    $("#dutyshift_" + j + "_" + k).css("width", cellwidth - widthOffset);
                    $("#dutyshift_" + j + "_" + k).css("left", cellleft + 1);
                    let initialWidth;
                    if (intDayCount < 7) {
                        $(".weeklytop-week").css("width", cellwidth * intDayCount);
                        initialWidth = cellwidth * (7);
                    } else {
                        if (intDayStart != 0) {
                            $(".weeklytop-week").css("width", cellwidth * 7);
                            initialWidth = cellwidth * (7);
                        } else {
                            $(".weeklytop-week").css("width", cellwidth * (7 - intDayStart));
                            initialWidth = cellwidth * (7 - intDayStart);
                        }
                    }
                    let weekInnerWidth = cellwidth * (initialWidth / cellwidth);
                    let weekInnerLeft = (cellwidth * 7) + (j * initialWidth);
                    $("#weeklytop-week-inner" + j).css("width", weekInnerWidth);
                    $("#weeklytop-week-inner" + j).css("left", weekInnerLeft);

                }
            }
        }


        $(function() {
            $("#datepicker").datepicker({
                showOn: "button",
                buttonImage: "images/calendar.gif",
                buttonImageOnly: true,
                showOtherMonths: 'true',
                selectOtherMonths: 'true',
                firstDay: '6',
                gotoCurrent: 'true',
                changeMonth: true,
                changeYear: true,
                dateFormat: "yy-mm-dd",
                defaultDate: "<?php echo $dteStartDate ?>",
                onSelect: function(dateText, inst) {
                    var currentdate = dateText;
                    $.post("page-includes/allocations/allocations-set-week-from-date.php", {
                            date: currentdate
                        },
                        function(data, status) {
                            {
                                ShowAllocationsDuties(<?php echo $intTeamID ?>)
                            }
                        });
                }
            });
        });

        $.contextMenu({
            selector: '.unassigned-context-menu',
            //trigger: 'left',
            callback: function(key, options) {
                id = options.$trigger.attr("id");
            },
            items: {
                "assign": {
                    name: "Assign to person",
                    icon: "assign",
                    // superseeds "global" callback
                    callback: function(key, options) {
                        var allocationid = options.$trigger.attr("id");
                        var weekno = options.$trigger.attr("week");
                        var dayno = options.$trigger.attr("day");
                        var intteamid = options.$trigger.attr("intteamid");
                        var Dutydate = options.$trigger.attr("curdate");
                        var dutytype = options.$trigger.attr("dutytype");
                        var dutyName = options.$trigger.attr("dutyname");
                        $.ajax({
                            type: 'POST',
                            url: 'page-includes/allocations/edits/coverDuty.php',
                            data: {
                                allocationid: allocationid,
                                pagename: 'production',
                                weekno: weekno,
                                dayno: dayno,
                                intteamid: intteamid,
                                Dutydate: Dutydate,
                                dutyname: dutyName
                            },
                            success: function(data, status) {
                                $.facebox(data);
                            }
                        });
                    }
                }
            }
        })

        $.contextMenu({
            selector: '.assigned-context-menu',
            //trigger: 'left',
            callback: function(key, options) {
                id = options.$trigger.attr("id");
            },
            items: {
                "other": {
                    name: "Mark as Absent",
                    icon: "exclaim",
                    // superseeds "global" callback
                    callback: function(key, options) {
                        var dutyid = options.$trigger.attr("id");
                        var dutytype = options.$trigger.attr("dutytype");
                        var intteamid = options.$trigger.attr("intteamid");
                        var intMarkabsent = options.$trigger.attr("intMarkabsent");
                        var allocationSPID = options.$trigger.attr("allocationSPID");
                        var allocationId = options.$trigger.attr("allocationId");
                        var scheduledpersonid = options.$trigger.attr("scheduledpersonid");
                        var weeknum = options.$trigger.attr("weeknum");
                        var dutydate = options.$trigger.attr("dutydate");

                        $.ajax({
                            type: 'POST',
                            url: 'page-includes/allocations/edits/markabsent.php',
                            data: {
                                allocationDutyId: dutyid,
                                teamId: intteamid,
                                action: '2',
                                isShiftleader: intMarkabsent,
                                allocationId: allocationId,
                                weeknum: weeknum,
                                dutydate: dutydate,
                                allocationSPid: allocationSPID,
                                scheduledpersonid: scheduledpersonid
                            },
                            success: function(data, status) {
                                data = JSON.parse(data);
                                if (data.strstatus != null && data.strstatus != 1) {
                                    customAlert(data.strsmsg);
                                }
                                ShowAllocationsDuties(intteamid);
                            }
                        });
                    }
                },
                "delete": {
                    name: "Unassign",
                    icon: "delete",
                    visible: function(key, opt) {
                        if ((opt.$trigger.attr('data-duty-name') != "U") && (opt.$trigger.attr('data-pdl-starttime') == '0') && (opt.$trigger.attr('data-pdl-endtime') == '0')) {
                            return true;
                        }
                    },
                    // superseeds "global" callback
                    callback: function(key, options) {
                        var dutyid = options.$trigger.attr("id");
                        var dutytype = options.$trigger.attr("dutytype");
                        var teamId = options.$trigger.attr("intteamid");
                        $.ajax({
                            type: 'POST',
                            url: 'page-includes/allocations/edits/unassign-alloc-duty.php',
                            data: {
                                'dutyid': dutyid,
                                'teamId': teamId,
                                'action': 0,
                                'isShiftleader': 1,
                                'pagename': 'production'
                            },
                            success: function(data, status) {
                                $.facebox(data);
                            }
                        });
                    }
                },

                "sep3": "---------",
                "history": {
                    name: "History",
                    icon: "history",
                    // superseeds "global" callback
                    callback: function(key, options) {
                        var dutyid = options.$trigger.attr("id");
                        $.ajax({
                            type: 'POST',
                            url: 'page-includes/allocations/edits/dutyhistory.php',
                            data: {
                                id: dutyid
                            },
                            success: function(data) {
                                $.facebox(data);
                            }
                        });
                    }
                },

            },
        })

        function openEditWeekly(teamId, weekNumber) {
            $.ajax({
                type: "post",
                url: "/components/filters/filter-process.php",
                data: {
                    'action': 'checkselectedfilter',
                    'teamId': teamId
                },
                beforeSend: function() {
                    $('#loading').hide();
                },
                success: function(response) {
                    let returnData = $.parseJSON(response);
                    let argDataInitialLoad = {};
                    argDataInitialLoad.schedulingTeamId = teamId;
                    argDataInitialLoad.userId = $.cookie('editWeeklyUserId');
                    argDataInitialLoad.weekNumber = weekNumber.substr(4, 2) + '/' + weekNumber.substr(0, 4);
                    argDataInitialLoad.teamId = teamId;
                    if (returnData.status == true) {
                        argDataInitialLoad.selAutoPageFilterId = returnData.selectedFilterId;
                        argDataInitialLoad.shiftCountingFilterId = returnData.selectedShiftCountFilterId;
                    } else {
                        argDataInitialLoad.selAutoPageFilterId = 0;
                        argDataInitialLoad.shiftCountingFilterId = 0;
                    }
                    editWeeklyPageLoad('No', argDataInitialLoad);
                }
            });
        }
    </script>

<?php
}
?>