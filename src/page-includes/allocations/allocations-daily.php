<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../../vendor/autoload.php';
include_once '../../function-includes/bootstrap.php';
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/shiftleaderfunctions.php';
include_once '../../function-includes/editabledaystatus.php';
include_once '../../function-includes/skillsfunctions.php';
include_once __DIR__ . '/../../page-includes/allocations/weekly/service/AllocationService.php';
include_once __DIR__. '/../../function-includes/user-scheduling-team-list.php';

if (isset($_REQUEST['print'])) {
    $intPrint = $_REQUEST['print'];
} else {
    $intPrint = 0;
}
if ($intPrint == 0) {
    include_once __DIR__ . '/../../page-includes/allocations/weekly/filters/filters-script.php';
}

include_once '../users/process/classUserSetup.php';
?>
<style type="text/css">
#content{
  overflow-y:hidden;
}
.tooltip-table {
    border: 1px solid #505050;
    border-radius: 3px;
    color: #FFFFFF;
}

.tooltip-table-background {
    background-color: #505050;
}

.tooltip-div {
    position: absolute;
    z-index:9999;
    display: none;
}

.tooltip-table-white {
    border: 1px solid #FFFFFF;
    border-radius: 3px;
    color: #000000;
}

.tooltip-table-white {
    background-color: #FFFFFF;
}
</style>
<?php
use App\Models\User\RefRole;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$setupObj = new classUserSetup();
/* new data  team wise*/
$loggedUsedInfo = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
use Symfony\Component\HttpFoundation\Request;
$request = Request::createFromGlobals($request);
$service = new AllocationService();
$intIsShiftLeader = 2; //As scheduled person, freelancer,schduleing team Viewer.
$intHasBeenEdited = 0;
$intCanViewComments = 0;
$intCanViewPersonComments = 0;
$editpermission = 0;
$rolepermission = 0;
$editableimg = 'locked_timer.png';
$role = 0;
$intCanMakeEdited = 0;
$isViewPage = 0;
$lockUnlock = 0;
$selectedDay = 1;
$screenName = 'ViewDaily';
$filterName = 'Filters';
$arrDateEditable = [];
$uLastPublish = 0;
$intFillMenu = 0;
$intDutyHeight = 40;
$intUnallocDutyHeight = 0;
$intUnallocJobHeight = 0;
$intRowHeight = 43;
$intDontShowDay = 0;
$intShowForSchedulers = 0;
$isAttention = 0;
$attentionClass = '';
$greenClass = '';
$autopageLink = "";
$cookiechecked = '';
$isGridCheck = 0;
$intAllowUserSort = 1;
$parentDutyID = 0;
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intScheduledPersonID = getScheduledPersonIDByNetLoginID($strUser);
if (isset($_REQUEST['teamId'])) {
    $intTeamID = $_REQUEST['teamId'];
} else {
    $intTeamID = GetDefaultSchedulingTeamIdByLogin($intScheduledPersonID);
}
// Check cookie value for unallocated section
$getuserfilter = GetUserCurrentFilter($team = '');

if (!empty($getuserfilter['Dailyunallocated'])) {
    $cookiechecked = 'Checked';
    $displayUnallocated = 'block';
} else {
    $displayUnallocated = 'none';
}

if (!empty($getuserfilter['Checkstartendshift']) || !empty($_REQUEST['valstartendshift'])) {
    $intvalstartendshift = 1;
    $startendchecked = 'Checked';
} else {
    $intvalstartendshift = 0;
    $startendchecked = '';
}

if (isset($_REQUEST['selectedDay']) && $_REQUEST['selectedDay'] >= 1) {
    $selectedDay = $_REQUEST['selectedDay'];
    $_SESSION['selectedDay'] = $selectedDay;
    if ($selectedDay == 'default') {
        $selectedDay = 1;
    }
} elseif (isset($_SESSION['selectedDay']) && $_SESSION['selectedDay'] >= 1) {
    $selectedDay = $_SESSION['selectedDay'];
}

if (isset($_REQUEST['callerpage'])) {
    $intCallerPage = $_REQUEST['callerpage'];
} else {
    $intCallerPage = 0;
}

if ($request->get('type') == '') {
    $getSetFilterId = GetCurrentSetDailyFilter($screenName, $intTeamID);
    if ($getSetFilterId > 0) {
        echo '<script type="text/javascript">applyViewFilter("' . $screenName . '",' . $getSetFilterId . ')</script>';
    }
}

$filterQuery1 = '';
if ($request->get('queryStr1') != '') {
    $filterQuery1 = $request->get('queryStr1');
}

$filterQuery2 = '';
if ($request->get('queryStr2') != '') {
    $filterQuery2 = $request->get('queryStr2');
}

$filterQuery3 =  $intTeamID;

if ($request->get('queryStr3') != '') {
    $filterQuery3 = $request->get('queryStr3');
    preg_match_all('!\d+!', (string) $filterQuery3, $matches);
    $addteam = implode(",", $matches[0]);
    $addteam = $addteam . "," . $intTeamID;
    $filterQuery3 =  $addteam ;
}

$filterOrderStr = '';
$selFilterType = '';
if ($request->get('selFilterType') != '') {
    $selFilterType = $request->get('selFilterType');
}
$selFilterId = '';
if ($request->get('selFilterId') != '') {
    $selFilterId = $request->get('selFilterId');
}
$skillFilterDaily = '';
if ($request->get('skillFilterDaily') != '') {
    $skillFilterDaily = $request->get('skillFilterDaily');
}
$dutyFilterDaily = '';
if ($request->get('dutyFilterDaily') != '') {
    $dutyFilterDaily = $request->get('dutyFilterDaily');
}

$jobFilterDaily = '';
if ($request->get('jobFilterDaily') != '') {
    $jobFilterDaily = $request->get('jobFilterDaily');
}

$jobNameAll = '';
if ($request->get('jobNameAll') != '') {
    $jobNameAll = $request->get('jobNameAll');
}

$jobLabelAll = '';
if ($request->get('jobLabelAll') != '') {
    $jobLabelAll = $request->get('jobLabelAll');
}

$dutyTimeFilterNow = '';
if ($request->get('dutyTimeFilterNowDaily') == 1) {
    $dutyTimeFilterNow = 1;
}

echo '<input type="hidden" name="selFilterType" id="selFilterType" value="' . $selFilterType . '">';
echo '<input type="hidden" name="selFilterId" id="selFilterId" value="' . $selFilterId . '">';

if ($intTeamID == 0) {
    echo '<br>';
    echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
    echo '<br><br>';
    echo 'You are attempting to view the Allocations for your Default Team.<br>Please assign a default team or select the required team from the allocations menu.';
    echo '<br></br><br>';
    echo '</div>';
    die;
}

$arrTeamDefaults = GetTeamDefaults(0, $intTeamID);
$teamDescription = $arrTeamDefaults[$intTeamID]['Description'];
$intHideDailyView = $arrTeamDefaults[$intTeamID]['HideDailyView'];
$intHasGridChecks = $arrTeamDefaults[$intTeamID]['hasGridChecks'];
$intEditPeriod = $arrTeamDefaults[$intTeamID]['DailyEditPeriod'];
$intSignInDays = $arrTeamDefaults[$intTeamID]['SignInDays'];
$intAllowApplyOvertime = $arrTeamDefaults[$intTeamID]['AllowApplyOvertime'];
$intHasDutiesView = $arrTeamDefaults[$intTeamID]['HasDutiesView'];
$showLock = $arrTeamDefaults[$intTeamID]['showLock'] ?? 0;
$intIsFreelance = $_SESSION['user']["isFreelance"] ?? 0;
// Restrict View for freelancers....
$intFreelanceMaskDays = $arrTeamDefaults[$intTeamID]['FreelancerMaskingDays'] ?? '';
$intFreelanceMaskflag = $arrTeamDefaults[$intTeamID]['FreelancerMasking'] ?? '';
$strMyStaffNumber = $UserID;

$checkdate=strtotime('1970-01-01');
if (isset($_REQUEST['date']) && ($_REQUEST['date'] != '') && ($_REQUEST['date'] != 'undefined') && (strtotime((string) $_REQUEST['date']) > $checkdate)) {
    $strCurrentDate = date("Y-m-d", strtotime((string) $_REQUEST['date']));
} else {
    if (isset($_SESSION['allocattionsdate'])) {
      $strCurrentDate = $_SESSION['allocattionsdate'];
    } else {
      $strCurrentDate = date("Y-m-d");
    }
}

/** Lock Setting By Team */
$defaultLockSetting = getDefaultLocksettingByTeamONiDay($intTeamID, $strCurrentDate);
if (isset($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus']) && ($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus'] == 0) && ($defaultLockSetting['ManualLock'][$strCurrentDate]['ManualLock'] == 2)) {
    $editableimg = "locked_timer.png";
}
if (isset($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus']) && ($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus'] == 0) && ($defaultLockSetting['ManualLock'][$strCurrentDate]['ManualLock'] == 1)) {
    $editableimg = "locked.png";
}
if (isset($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus']) && ($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus'] == 1) && ($defaultLockSetting['ManualLock'][$strCurrentDate]['ManualLock'] == 1)) {
    $editableimg = "unlocked.png";
}

if (isset($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus']) && ($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus'] == 1) && ($defaultLockSetting['ManualLock'][$strCurrentDate]['ManualLock'] == 2)) {
    $editableimg = "unlocked_timer.png";
}

if (isset($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus']) && ($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus'] == 1) && ($defaultLockSetting['ManualLock'][$strCurrentDate]['ManualLock'] == 0)) {
    $editableimg = "unlocked.png";
}

/** Lock Setting By Team */
$lockUnlock = $showLock;

if (isset($defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus']) && $defaultLockSetting['lockStatus'][$strCurrentDate]['LockStatus'] == 1) {
    $lockUnlock = 1;
} else {
    $lockUnlock = 0;
}

$intCanSignin = 0;
if (isset($_SESSION['dailyorder'])) {
    $intSortOrder = $_SESSION['dailyorder'];
} else {
    $intSortOrder = 0;
}

if ($request->get('orderStr') != '') {
    $intSortOrder = $request->get('orderStr');
}
$isScheduler = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULER);
$isManager = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::MANAGER);
$isTeamAdmin = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_ADMIN);
$hasShiftleaderRole = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SHIFT_LEADER);
$isTeamLeader = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::TEAM_LEADER);
$isSchedulingTeamViewer = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_VIEWER);
$isScheduledPerson = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULED_PERSON);
//If system admin or area admin the person have STA permission
if($isTeamAdmin == 0) {
  $isTeamAdmin = $userRoleTrait->checkEditWeeeklyAdminRole($intTeamID);
}
/** Role & Permission Settings START **/
/*** Sign flag settings START ***/
if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($hasShiftleaderRole == 1) || ($isTeamLeader == 1)) {
    $intCanSignin = 1;
    $isGridCheck = 1;
}
if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isTeamLeader == 1)) {
    $intCanMakeEdited = 1;
    $intCanViewComments = 1;
    $intCanViewPersonComments = 1;
    $role = 2;
    $rolepermission = 1;
    $editpermission = 1;
    $intIsShiftLeader = 0;
} elseif ($hasShiftleaderRole == 1 && $lockUnlock == 1) {
    $intCanViewComments = 1;
    $rolepermission = 1;
    $role = 1;
}
$shiftleaderflag = 0;
if (($isScheduler == 1) || ($isTeamAdmin == 1) && ($hasShiftleaderRole == 1) || ($isTeamLeader == 1)) {
    $shiftleaderflag = 0;
}

if (($isSchedulingTeamViewer == 1) && ($hasShiftleaderRole == 1)) {
    $shiftleaderflag = 1;
}

if (($isScheduledPerson == 1) && ($hasShiftleaderRole == 1)) {
    $shiftleaderflag = 1;
}
$intMarkabsent =0;
if ($hasShiftleaderRole == 1 && $intIsShiftLeader != 0) {
    $intIsShiftLeader = 1;
	$intMarkabsent = 1;
    if ($isManager == 1) {
        $intIsShiftLeader = 3;
    }
    $intCanViewPersonComments = 1;
}
/** Role & Permission Settings END **/
// ############################################### Are wa allowed to make the day editable?

$userwebconfigResult = GetUserWebConfigByUserLogin($strUser);
$intHourwidth = $userwebconfigResult["result"][0]["HourWidth"] ?? 0;
// check $intHourwidth value, if its 0 then set defult value
if ($intHourwidth == 0) {
    $intHourwidth = 75;
}

// end get filter options
$showall = 0;
$isvisible = 1;

if (isset($_SESSION['hideduties'])) {
    $hideduties = $_SESSION['hideduties'];
} else {
    $hideduties = 0;
}
$_SESSION['allocattionsdate'] = $strCurrentDate;

if ($intIsFreelance == 1) {
    $dteFirstDate = date("Y-m-d", strtotime("-1 Day"));
    $dteLastDate = date("Y-m-d", strtotime("+$intFreelanceMaskDays Days"));
} else {
    if ($intCanViewComments == 1) {
        // isAdmin isScheduler isManager
        $dteFirstDate = date("Y-m-d", strtotime("-7 years"));
    } else {
        $dteFirstDate = date("Y-m-d", strtotime("-4 years"));
    }
    $dteLastDate = date("Y-m-d", strtotime("+999 Days"));
}

if (($intFreelanceMaskflag==1) && (!($strCurrentDate >= $dteFirstDate && $strCurrentDate <= $dteLastDate))) {
    $intDontShowDay = 1;
}
$yesterday = date("Y-m-d", strtotime("-1 day", (strtotime((string) $strCurrentDate))));
$tomorrow = date("Y-m-d", strtotime("+1 day", (strtotime((string) $strCurrentDate))));
$day7date = date("Y-m-d", strtotime("+6 day", (strtotime((string) $strCurrentDate))));
if ($selectedDay > 1) {
    $daysadded = $selectedDay - 1;
    $day7date = date("Y-m-d", strtotime("+$daysadded day", (strtotime((string) $strCurrentDate))));
} else {
    $day7date = date("Y-m-d", strtotime((string) $strCurrentDate));
}

$getWeekandDayArr = GetAllocationWeekandDay($strCurrentDate);
$intWeek = $getWeekandDayArr['ixYearWeek'];
$intDay = $getWeekandDayArr['ixDayInWeek'];
$strDayName = $getWeekandDayArr['sDayName'];

$arrHiddenDays = GetHiddenDays($intTeamID, $strCurrentDate, $strCurrentDate);
$intDayIsHidden = 0;
if (isset($arrHiddenDays[$intTeamID][$strCurrentDate])) {
    $intDayIsHidden = 1;
}

if (isset($arrHiddenDays[$intTeamID][$strCurrentDate]) && $intCanViewComments == 0) {
    $intDontShowDay = 1;
}

// Now check if the day is available for viewing
if (strtotime("+$intHideDailyView Days") < strtotime((string) $strCurrentDate)) {
    $intShowForSchedulers = 1;
}

$maskingCurrDate = date('Y-m-d');
$dateAfterXDays = date('Y-m-d', strtotime(" +" . $arrTeamDefaults[$intTeamID]['DailyViewMaskingDays'] . " day", strtotime($maskingCurrDate)));
if ($_SESSION['allocattionsdate'] > $dateAfterXDays) {
    $intDontShowDay = 1;
}

$allowInBuilding = $arrTeamDefaults[$intTeamID]['AllowInBuilding'];
// Get whether it's been edited
// Get the requests and locks

$canViewAdditional = 0;
if ($isScheduler == 1 || $isTeamAdmin == 1 || $isManager == 1) {
    $canViewAdditional = 1;
}
$arrRequests = GetAppliedRequestsANdLocks($strCurrentDate, $strCurrentDate);
$arrAllocations = ReadAllocationsDay($intWeek, $intDay, $intTeamID, $intSortOrder, $intIsShiftLeader, $filterQuery1, $filterQuery2, $filterQuery3, $filterOrderStr, $strCurrentDate, $day7date, $skillFilterDaily, $dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll, $canViewAdditional);
$arrAllocations = json_decode($arrAllocations, true);
$arrDateEditable = $arrAllocations['IsDayEditable'] ?? [];
$arrDateMLock = $arrAllocations['ManualLock'] ?? $defaultLockSetting['ManualLock'] ?? [];
$arrDateLock = $arrAllocations['lockStatus'] ?? $defaultLockSetting['lockStatus'] ?? [];
$arrDayEdited = $arrAllocations['isDayEdited'] ?? [];
if (isset($arrDayEdited[$strCurrentDate]['isDayEdited']) && $arrDayEdited[$strCurrentDate]['isDayEdited'] >= 1) {
    $intHasBeenEdited = 1;
}

if (!empty($arrDateEditable) && isset($arrDateEditable[$strCurrentDate]['IsDayEditable']) && $arrDateEditable[$strCurrentDate]['IsDayEditable'] == 1) {
    $isViewPage = 0;
    $iseditable = 1;
} else {
    $isViewPage = 1;
    $iseditable = 0;
}
/** Shiftleader Edit permission start **/
if (isset($iseditable) && $iseditable >= 1 && $hasShiftleaderRole == 1) {
    $intCanMakeEdited = 1;
    $editpermission = 1;
}

if (($hasShiftleaderRole == 1) && ($isManager == 1) && ($lockUnlock == 1)) {
    $intCanMakeEdited = 1;
    $editpermission = 1;
}

if ($iseditable == 1 && $intCanMakeEdited == 0) {
    $iseditable = 0;
}
/** Shiftleader Edit permission end **/

//Check For Archive Data Week
if (($hasShiftleaderRole == 0)) {
    $request->request->set('startWeek',$intWeek);
    $checkWeekAndRole = $service->checkWeekAndRole($request);
    $dataEdit = 0;
    if(($checkWeekAndRole['screenView'] == 0) && ($checkWeekAndRole['screenEdit'] == 1)){
        $dataEdit = 1;
    }
    if($dataEdit == 0){
        $intIsShiftLeader = 1;
        $isViewPage = 1;
        $iseditable = 0;
        $editpermission = 0;
    }
}

/* timer data*/
$timerdata = GetLastDayUpdate($strCurrentDate, $day7date, $intTeamID, $isViewPage, 0, 0, 0, 0);
$dutyhistoryid = $timerdata['DutyHistoryID'] ?? 0;
$jobhistoryid = $timerdata['JobHistoryID'] ?? 0;
$signinid = $timerdata['SignINID'] ?? 0;
$signinlastupdate = $timerdata['SigninLastUpdate'] ?? 0;
$isRefresh = $timerdata['IsRefresh'] ?? 0;
/* timer data*/
$miscdutyflag = [];
$normaldutyflag = [];
for ($i = 1; $i <= $selectedDay; $i++) {
    if (isset($arrAllocations['assigned'][$i])) {
        foreach ($arrAllocations['assigned'][$i] as $scid => $value) {
            if ((($value['miscduty'] == 1) || ($value["duty"] == "Sick")) && (!in_array($scid, $normaldutyflag))) {
                $miscdutyflag[] = $scid;
            } else {
                $miscdutyflag = array_flip($miscdutyflag);
                unset($miscdutyflag[$scid]);
                $miscdutyflag = array_flip($miscdutyflag);
                $normaldutyflag[] = $scid;

            }
        }
    }
}

if ($editpermission == 1) {
    if (($isTeamAdmin == 1)) {
        $backdate7yr = strtotime('-2 year', strtotime(date('Y-m-d')));
    } else {
        $backdate7yr = strtotime('-3 month', strtotime(date('Y-m-d')));
    }
    $backdate7yralternate = strtotime(getenv('ACCESS_DATE'));
    if ($backdate7yralternate > $backdate7yr) {
        $validDateNo = $backdate7yralternate;
    } else {
        $validDateNo = $backdate7yr;
    }
    $validDate = date('jS F Y', $validDateNo);
    $accessmsg = "Allocate cannot open a day older than two years, or before " . $validDate . ". Please select another day.";
    if ($validDateNo > strtotime((string) $strCurrentDate)) {
        $editpermission = 0;
    }
} else {
    $validDateNo = strtotime('-2 year', strtotime(date('Y-m-d')));
    $validDate = date('jS F Y', $validDateNo);
    $accessmsg = "Allocate cannot open a day older than two years.Please select another day.";
}

if ($editpermission == 0) {
    $intvalstartendshift = 1;
    $startendchecked = 'Checked';
}
if ($intvalstartendshift == 1) {
    for ($i = 1; $i <= $selectedDay; $i++) {
        if (isset($arrAllocations['assigned'][$i])) {
            foreach ($arrAllocations['assigned'][$i] as $scid => $value) {
                if (in_array($scid, $miscdutyflag)) {
                    unset($arrAllocations['assigned'][$i][$scid]);
                }
            }
        }
    }
}

if ($selectedDay > 1) {
    if (isset($arrAllocations['assigned'][1])) {
        $sortseqarray = array_keys($arrAllocations['assigned'][1]);
        for ($i = 1; $i <= $selectedDay; $i++) {
            if (isset($arrAllocations['assigned'][$i])) {
                $orderedArray = [];
                foreach ($sortseqarray as $key) {
                    $orderedArray[$i][$key] = $arrAllocations['assigned'][$i][$key];

                }
                $arrAllocations['assigned'][$i] = $orderedArray[$i];
            }
        }
    }
}

// The 'draggable' entry in the divs

if ($editpermission == 1) {
    $draggable = ' draggable jobresizable ';
    $allocationcontextmenunotworking = ' allocation-context-menu-notworking';
    $editedallocationcontextmenu = ' resizable edited-allocation-context-menu';
    $jobcontextmenu = ' job-context-menu';
    $allocationgreycontextmenu = ' allocation-absent-context-menu';
} else {
    if ($intHasBeenEdited == 1) {
        $draggable = '';
        $editedallocationcontextmenu = '';
        $jobcontextmenu = ' job-locked-context-menu';
        $allocationcontextmenunotworking = '';
        $allocationgreycontextmenu = '';
    } else {
        $draggable = '';
        $editedallocationcontextmenu = '';
        $jobcontextmenu = '';
        $allocationcontextmenunotworking = '';
        $allocationgreycontextmenu = '';
    }
}

$arrHolidays = calculateBankHolidayst(date("Y", strtotime((string) $strCurrentDate)));
// Filter out the unallocated duties?
if ($hideduties == 1 && isset($arrAllocations['unassigned'][$selectedDay])) {
    foreach ($arrAllocations['unassigned'][$selectedDay] as $allocationid => $value) {
        if (!isset($value['jobs'])) {
            unset($arrAllocations['unassigned'][$selectedDay][$allocationid]);
        }
    }
}

$countunallocated[] = 1;
for ($m = 1; $m <= $selectedDay; $m++) {
    if (isset($arrAllocations['unassigned'][$m])) {
        $countunallocated[] = count($arrAllocations['unassigned'][$m]);
    }
}
$countunallocated = max($countunallocated);
// Get any EDP voulenteers
$arrEDP = readedp($strCurrentDate, $strCurrentDate);
$earlieststart = floor($arrAllocations['earlieststart']);
$lateststart = round($arrAllocations['lateststart']);
if (gmdate("i", $arrAllocations['showdutyend']) > 0) {
    $showdutyend = gmdate("H", $arrAllocations['showdutyend']) + 1;
} else {
    $showdutyend = gmdate("H", $arrAllocations['showdutyend']);
}

$timeString = gmdate("H:i", $arrAllocations['showdutystart']);
$timeParts = explode(':', $timeString);
$hours = (int)$timeParts[0];
$minutes = (int)$timeParts[1];
$showdutystart = $hours + ($minutes / 60);
$showdutystart = floor($showdutystart);

echo '<input type="hidden" id="showdutystart" value=' . $showdutystart . '>';

if ($selectedDay > 1) {
    $lateststart = round($arrAllocations['lateststart']);
    if ($lateststart == 0) {
        $lateststart = 24;
    }
} else {
    if ($showdutyend > 6) {
        $lateststart = 24;
    } else {
        $lateststart = 30;
        $showdutyend = 0;
    }
}

$hoursinday = $lateststart + $earlieststart;
if ($hoursinday != 0) {
    $screenwidth = round($intHourwidth * $hoursinday);
}
$plusfifteen = timetoseconds(date("H:i")) + 900;
$accessibility_title = '';
if(array_key_exists("callerpage",$_REQUEST) && isset($_REQUEST['callerpage']))
{
	if($_REQUEST['callerpage'] === 'ViewDailyToday'){
	  $accessibility_title = "Today's Grid";
	}elseif($_REQUEST['callerpage'] === 'ViewDailyYesterday'){
	  $accessibility_title = "Yesterday's Grid";
	}elseif($_REQUEST['callerpage'] === 'ViewDailyTomorrow'){
	  $accessibility_title = "Tomorrow's Grid";
	}else{
	  $accessibility_title = "Daily Allocations";
	}
}
echo '<h1 class="sr-only">'.$accessibility_title.'</h1>';
if ($intPrint == 0) {
    // ############################### The Header that does not print
    echo '<table id="headerfirst" class="tablegreysmallnoborder grayBG" width="100%">';
    echo '<tr height="35px">';
    echo '<td class="medtextbold handcursor" nowrap><span onclick=\'javascript:ShowDailyAllocations(' . $intTeamID . ',"' . $yesterday . '")\';>&nbsp;&lt;&lt; ' . date("jS M Y", strtotime($yesterday)) . '</span></td>';
    echo '<td class="medtextbold handcursor" nowrap><span onclick=\'javascript:ShowDailyAllocations(' . $intTeamID . ',"' . $tomorrow . '")\';>' . date("jS M Y", strtotime($tomorrow)) . '&nbsp;&gt;&gt;</span></td>';
    echo '<td class="medtextbold">Choose Date:</td>';
    echo '<td class="medtextbold handcursor"><input type="hidden" id="datepicker"></td>';
    echo '<td  class="medtextbold allocationDate" nowrap>';
    echo '<input type="hidden" id="strCurrentDate" value=' . $strCurrentDate . '>';
    echo '<input type="hidden" id="CurrentUserID" value=' . $UserID . '>';
    echo '<input type="hidden" id="getweek" value=' . $intWeek . '>';
    echo '<input type="hidden" id="getDay" value=' . intval($intDay) . '>';
    echo '<input type="hidden" id="getDayName" value=' . $strDayName . '>';
    echo '<input type="hidden" id="strCurrentTeam" value=' . $intTeamID . '>';
    echo '<input type="hidden" id="roleid" value=' . $role . '>';
    echo '<input type="hidden" id="rolepermission" value=' . $rolepermission . '>';
    echo '<input type="hidden" id="filterQuery1" value="' . ($filterQuery1) . '">';
    echo '<input type="hidden" id="filterQuery2" value="' . ($filterQuery2) . '">';
    echo '<input type="hidden" id="filterQuery3" value="' . ($filterQuery3) . '">';
    echo '<input type="hidden" id="filterOrderStr" value="' . ($filterOrderStr) . '">';
    echo '<input type="hidden" id="skillFilterDaily" value="' . ($skillFilterDaily) . '">';
    echo '<input type="hidden" id="dutyFilterDaily" value="' . ($dutyFilterDaily) . '">';
    echo '<input type="hidden" id="jobFilterDaily" value="' . ($jobFilterDaily) . '">';
    echo '<input type="hidden" id="jobNameAll" value="' . ($jobNameAll) . '">';
    echo '<input type="hidden" id="jobLabelAll" value="' . ($jobLabelAll) . '">';
    echo '<span class="daily-day date-comment handcursor" data-date-comment-date="' . $strCurrentDate . '" data-date-comment-team-id="' . $intTeamID . '" style="margin-right: 21px;">' . date("jS F Y - l", strtotime((string) $strCurrentDate)) . '<span class="date-commment-info-icon" style="cursor: pointer;margin-left:4px;position:absolute;"></span></span>';
    echo "<br>Week " . substr((string) $intWeek, -2);
    if (isset($arrHolidays[$strCurrentDate])) {
        echo "<br>(" . $arrHolidays[$strCurrentDate] . ")";
    }
    echo '</td>';

    echo '<td>';
    echo '<div>';
    echo '<select name="teamId" id="teamId" class="chosen-select" onchange="allocationViewByTeam(this.value)">';
    echo "<option value=''>Select The Team</option>";
    $teamOptions = getSchedulingTeamList($request->get("teamId"), 'allocation-policy', 'view');
    echo $teamOptions;
    echo '</select>';
    echo '</div>';
    echo '</td>';
//############################################################### Filters here
    echo '<td>';

    echo '<div>';
    include __DIR__ . '/../../components/filters/filters.php';
    echo '</div>';
    echo '</td>';
    if (($intDayIsHidden == 1) && ($hasShiftleaderRole == 1)) {
        $intDontShowDay = 1;
    }
    if ($intDayIsHidden == 1 && $intDontShowDay == 0 && (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isTeamLeader == 1))) {
        echo '<td rowspan="2" align="center" width="50px" class="medtextbold handcursor" align="center"><font color="#990000">Day Hidden & Visible only to Schedulers.</font></td>';
    }

    switch ($intSortOrder) {
        case 0:
            echo '<td department="' . $intTeamID . '" class="sort-order-menu medtextbold handcursor">Sort<br><img data-title="Set Sort Order<br>(Currently: Start Time)<br>You can also press alt+o to change the Sort Order." border="0" src="images/sorttimedaily.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
            break;
        case 1:
            echo '<td department="' . $intTeamID . '" class="sort-order-menu medtextbold handcursor">Sort<br><img data-title="Set Sort Order<br>(Currently: Duty Name)<br>You can also press alt+o to change the Sort Order." border="0" src="images/sortduty.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
            break;
        case 2:
            echo '<td department="' . $intTeamID . '" class="sort-order-menu medtextbold handcursor">Sort<br><img data-title="Set Sort Order<br>(Currently: Staff Sort Code)<br>You can also press alt+o to change the Sort Order." border="0" src="images/sortsortcode.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
            break;
    }    
    if (($isTeamAdmin == 1) || ($isScheduler == 1) || ($hasShiftleaderRole == 1) || ($isTeamLeader == 1)) {
        if ($intAllowApplyOvertime == 1) {
            echo '<td class="medtextbold handcursor"  onclick=\'javascript:ShowOvertimeVolunteers(' . $intTeamID . ',"' . $strCurrentDate . '")\';><img data-title="Show Volunteers" border="0" src="images/overtime-volunteers.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
        }
    }
    echo '<td class="medtextbold handcursor" onclick=\'javascript:ShowDailyAllocations(' . $intTeamID . ',"' . $strCurrentDate . '")\';><img data-title="Refresh this Day" border="0" src="images/refresh.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
    echo '<td class="medtextbold handcursor" onclick=\'javascript:SetZoom("-10","' . $intTeamID . '")\';><img data-title="Zoom out<br>(Decrease the width of each hour)" border="0" src="images/zoom_out.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
    echo '<td class="medtextbold handcursor" onclick=\'javascript:SetZoom("+10","' . $intTeamID . '")\';><img border="0" data-title="Zoom in<br>(Increase the width of each hour)" src="images/zoom_in.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
    echo '<td class="medtextbold handcursor" onclick=\'javascript:PrintDailyAllocations(' . $intTeamID . ',"' . $strCurrentDate . '")\';><img data-title="Print this day" border="0" width="25px" height="25px" src="images/printdaily.png" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';

    if ((($isTeamAdmin == 1) || ($isScheduler == 1) || ($isTeamLeader == 1)) && strtotime((string) $strCurrentDate) >= strtotime(date("y-m-d"))) {
        echo '<td  class="medtextbold handcursor" onclick=\'javascript:CreateEmailOptions(' . $intTeamID . ',"' . $strCurrentDate . '",' . $intIsShiftLeader . ',' . $intvalstartendshift . ',"' . str_replace("'", "-", $filterQuery1) . '","' . str_replace("'", "-", $filterQuery2) . '","' . $filterQuery3 . '","' . $filterOrderStr . '",' . $selectedDay . ',"' . $jobNameAll . '","' . $jobLabelAll . '")\';><img data-title="Create a list (to send by email)<br>of people working on this day." border="0" src="images/emaildaily.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
    } else {
        echo '<td class="medtextbold handcursor" onclick=\'javascript:CreateEmailList(' . $intTeamID . ',"' . $strCurrentDate . '",' . $intIsShiftLeader . ',' . $intvalstartendshift . ',"' . str_replace("'", "-", $filterQuery1) . '","' . str_replace("'", "-", $filterQuery2) . '","' . $filterQuery3 . '","' . $filterOrderStr . '",' . $selectedDay . ',"' . $jobNameAll . '","' . $jobLabelAll . '")\';><img data-title="Create a list (to send by email)<br>of people working on this day." border="0" src="images/emaildaily.png" width="25px" height="25px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
    }

    if (isset($arrDateLock[$strCurrentDate]['LockStatus']) && ($arrDateLock[$strCurrentDate]['LockStatus'] == 1) && ($arrDateMLock[$strCurrentDate]['ManualLock'] == 0)) {
        $locked_img = 'unlocked.png';
        if ($intHasBeenEdited == 1) {
            $editableimg = str_replace(".png", "_1.png", $editableimg);
        } else {
            $editableimg = str_replace("_1.png", ".png", $editableimg);
        }
    } elseif (isset($arrDateLock[$strCurrentDate]['LockStatus']) && ($arrDateLock[$strCurrentDate]['LockStatus'] == 1) && ($arrDateMLock[$strCurrentDate]['ManualLock'] == 2)) {
        $locked_img = 'unlocked_timer.png';
        if ($intHasBeenEdited == 1) {
            $editableimg = str_replace(".png", "_1.png", $editableimg);
        } else {
            $editableimg = str_replace("_1.png", ".png", $editableimg);
        }
    } elseif (isset($arrDateLock[$strCurrentDate]['LockStatus']) && ($arrDateLock[$strCurrentDate]['LockStatus'] == 0) && ($arrDateMLock[$strCurrentDate]['ManualLock'] == 1)) {
        $editableimg = 'locked.png';
        if ($intHasBeenEdited == 1) {
            $editableimg = str_replace(".png", "_1.png", $editableimg);
        } else {
            $editableimg = str_replace("_1.png", ".png", $editableimg);
        }
    } elseif (isset($arrDateLock[$strCurrentDate]['LockStatus']) && ($arrDateLock[$strCurrentDate]['LockStatus'] == 0) && ($arrDateMLock[$strCurrentDate]['ManualLock'] == 2)) {
        $editableimg = 'locked_timer.png';
        if ($intHasBeenEdited == 1) {
            $editableimg = str_replace(".png", "_1.png", $editableimg);
        } else {
            $editableimg = str_replace("_1.png", ".png", $editableimg);
        }
    }
    if (($showLock == 1) && ($intIsShiftLeader != 2)) {
        if ((($isTeamAdmin == 1) || ($isScheduler == 1) || ($isTeamLeader == 1))) {
            if ($intHasBeenEdited == 0) {
                echo '<td class="medtextbold handcursortip tipremotelock" onclick=\'javascript:LockDayUnedited(' . $intTeamID . ',"' . $strCurrentDate . '")\';><img border="0" width="25px" height="25px" src="images/' . $editableimg . '" onmouseover="customtipremotelock(\'showtooltip\',this)" onmouseleave="customtipremotelock(\'hidetooltip\')"></td>';
            } else {
                echo '<td class="medtextbold handcursor tipremotelock" onclick=\'javascript:LockDay(' . $intTeamID . ',"' . $strCurrentDate . '")\';><img border="0" width="25px" height="25px" src="images/' . $editableimg . '" onmouseover="customtipremotelock(\'showtooltip\',this)" onmouseleave="customtipremotelock(\'hidetooltip\')"></td>';
            }
        } else {
            echo '<td class="medtextbold handcursortip tipremotelock" onmouseover="customtipremotelock(\'showtooltip\',this)" onmouseleave="customtipremotelock(\'hidetooltip\')"><img border="0" width="25px" height="25px" src="images/' . $editableimg . '"></td>';
        }
    }

    echo '<td class="lightcell" nowrap id="countdown">';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '<table id="headersecond" class="tablegreysmallnoborder grayBG" width="100%">';
    echo '<tr>';
    echo '<td class="medtextbold handcursor" style="width:10%;"> ';
    if ($intHasDutiesView == 1) {
        echo '<img data-title="Show Production View" class="handcursor" onclick=\'javascript:ShowAllocationsDuties("' . $intTeamID . '","' . $intWeek . '")\'; border="0" src="../images/AllocationsWeeklyDuties.png" width="53px" height="28px" onmouseover="customtiptitlewhiteleft(\'showtooltip\',this)" onmouseleave="customtiptitlewhiteleft(\'hidetooltip\')">';
    }
     echo '<img data-title="Show Allocations for this Week" class="handcursor" onclick=\'javascript:ShowAllocations("' . $intTeamID . '","' . $intWeek . '","","",1)\'; border="0" src="../images/AllocationsWeekly.png" width="53px" height="28px" onmouseover="customtiptitlewhiteleft(\'showtooltip\',this)" onmouseleave="customtiptitlewhiteleft(\'hidetooltip\')">';
    echo '</td>';
    echo '<td style="width:10%;">';
    echo '<img data-title="Show Studios for this Day" class="medtextbold handcursor" onclick=\'javascript:ShowStudioUsage(' . $intTeamID . ')\'; border="0" src="../images/studio.png" width="53px" height="28px" onmouseover="customtiptitlewhiteleft(\'showtooltip\',this)" onmouseleave="customtiptitlewhiteleft(\'hidetooltip\')">';
    if (($hasShiftleaderRole == 1) || ($isTeamAdmin == 1 ) || ($isTeamLeader == 1 )){
        echo '&nbsp;<img data-title="Show Allocations Edit Weekly" class="medtextbold handcursor" onclick=\'javascript:openEditWeekly("' . $intTeamID . '","' . $intWeek . '")\'; border="0" src="../images/AllocationsEditWeekly.png" width="53px" height="28px" onmouseover="customtiptitlewhiteleft(\'showtooltip\',this)" onmouseleave="customtiptitlewhiteleft(\'hidetooltip\')">';
    }
    echo '</td>';
    echo '<td align="center" class="medtextbold lightcell">';
    echo '<button id="yesterdayBtm" onclick="moveDays(\'yesterday\')">Yesterday</button>';
    echo '<button onclick=\'javascript:ShowDailyAllocations(' . $intTeamID . ',"' . date("Y-m-d") . '")\'>Today</button>';
    echo '<button id="tomorrowBtm" onclick="moveDays(\'tomorrow\')">Tomorrow</button>';
    echo '<span style="margin-left: 10px;">';
    echo 'Start Time:';
    echo '</span>';
    echo '<select id="starttime" name="starttime">';
    $range = range(strtotime("00:00"), strtotime("21:00"), 10800);

    foreach ($range as $time) {
        echo '<option value=' . date("H:i", $time) . ' data-sel>' . date("H:i", $time) . '</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '<td align="center" class="medtextbold lightcell">Days: ';
    echo '<select id="days" name="days" onchange="getDayNo(this.value);">';
    echo '<option value="default">Default</option>';
    $range = range(2, 7);
    foreach ($range as $days) {
        echo '<option value=' . $days . ' ';if ($selectedDay == $days) {echo 'selected';}
        echo '>' . $days . '</option>';
    }
    echo '</select>';

    echo '</td>';
    echo '<td class="medtextbold lightcell">';
    if ($editpermission == 1) {
        echo '<input type="checkbox" id="startendshift"' . $startendchecked . ' /> <label for="startendshift"> Show only Shifts with Start and End Time </label>';
        echo '<input type="checkbox" id="unallocatedduty"' . $cookiechecked . ' /> <label for="unallocatedduty"> Unallocated Duties </label>';
    }
    echo '</td>';
    if (($role == 2) && ($editpermission == 1)) {
        $request->request->set('startWeek', $intWeek);
        $request->request->set('teamId', $intTeamID);
        $request->request->set('endDate', $strCurrentDate);
        $request->request->set('startDate', $strCurrentDate);
            echo '<div id="allocations-container_daily" data-is-week-published="' . $service->isWeekPublishedDaily($request) . '">';
            echo '<td>';
            echo '<div class="pIcon">
              <span id="allocationPublish" class="handcursor" data-title="Publish" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')">P</span>
              <form id="dailyAllocationspublish">
                <input type="hidden" name="searchTeamId" id="searchTeamId"
                value="' . $intTeamID . '">
                <input type="hidden" id="teamId" name="teamId" value="' . $intTeamID . '" >
                <input type="hidden" name="getWeekNumber" id="getWeekNumber" value="' . $intWeek . '">
                <input type="hidden" name="screenName" id="screenName" value="' . $screenName . '">
                <input type="hidden" name="weekNumber" id="weekNumber" value="' . spinweek($intWeek) . '">
              </form>
            </div>';
            echo '</td></div>';

    }

    echo '<td class="globIcon">';
    if ($isTeamAdmin == 1) {
        $autopageLink = '/auto-pages';
        echo '<a href="' . $autopageLink . '" target="_blank"><img src="../images/autopage.png" width="25px" height="25px"></a>';
    }
    echo '</td>';

    echo '<td>';
    if ($editpermission == 1) {
        echo '<td><img data-title="Shiftleader Edits" class="medtextbold handcursor pencileIcon" onclick=\'javascript:ShowDailyAllocationsCompare(' . $intTeamID . ',"' . $strCurrentDate . '", 0)\'; border="0" src="../images/compareEdit.png" width="43px" height="28px" onmouseover="customtiptitlewhite(\'showtooltip\',this)" onmouseleave="customtiptitlewhite(\'hidetooltip\')"></td>';
        $uLastPublish = 0;
    }
    echo '<td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';
}
// ################################### End Of the Header that does not print
else {
    // ################################# The Header that for printing
    echo '<div class="printonly noselect">';
    echo '<table class="tablegreysmallnoborder" width="100%">';
    echo '<tr height="40px">';
    echo '<td align="center">';
    echo '<font size="3">';
    echo 'Allocations for ' . date("jS F Y - l", strtotime((string) $strCurrentDate));
    echo '</font>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';
    // ############################ End Of the Header that for printing
}

  if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isManager == 1) || ($isTeamLeader == 1)) {
      $intDontShowDay = 0;
  }

if (isset($arrAllocations['assigned']) && $isvisible == 1 && $intDontShowDay == 0) {
    $countallocations = 0;
    for ($k = 1; $k <= $selectedDay; $k++) {
        if (isset($arrAllocations['assigned'][$k])) {
            $countallocations += count($arrAllocations['assigned'][$k]);
        }
    }
    $currenttop = 0;
    echo '<div id ="weeklyUnAllocation-2" style="display: none;"></div>';
    echo '<div id ="weeklyAllocation-2" style="display: none;"></div>';
    // The holder
    echo '<div style="position: relative; display: inline-block; width: 100%;">';
    // The top Left Corner
    echo '<div style="position: relative; display: block; width: 100%;">';
    echo '<div style="position: relative; width:14%; float: left; height:30px; left:0px; top:' . $currenttop . 'px"  class="names" id="headerthird">';
    // Parameters for Grid Checks
    if ($isGridCheck == 1 && $intHasGridChecks == 1 && $intPrint == 0) {
        $arrgridchecks = getgridchecks($strCurrentDate, $intTeamID);
        // Grid Checks
        echo '<table class="tablesmallnoborder tablesmallfont" width="100%">';
        echo '<tr>';
        echo '<td nowrap colspan="3">';
        echo 'This Grid Checked at:';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        $arrret = getgridchecksinfo($arrgridchecks, 15);
        echo '<td class="handcursor' . $arrret['tip'] . '" ' . $arrret['customtipgridcheck'] . ' period="15" align="center" nowrap onclick=\'javascript:GridChecks(' . $intTeamID . ',"' . $strCurrentDate . '","15")\';>';
        echo '<img src="images/' . $arrret['image'] . '" border="0" height="11" width="11">';
        echo '15 Days';
        echo '</td>';

        $arrret = getgridchecksinfo($arrgridchecks, 8);
        echo '<td class="handcursor' . $arrret['tip'] . '" ' . $arrret['customtipgridcheck'] . ' period="8" align="center" nowrap onclick=\'javascript:GridChecks(' . $intTeamID . ',"' . $strCurrentDate . '","8")\';>';
        echo '<img src="images/' . $arrret['image'] . '" border="0" height="11" width="11">';
        echo '8 Days';
        echo '</td>';

        $arrret = getgridchecksinfo($arrgridchecks, 2);
        echo '<td class="handcursor' . $arrret['tip'] . '" ' . $arrret['customtipgridcheck'] . ' period="2" align="center" nowrap onclick=\'javascript:GridChecks(' . $intTeamID . ',"' . $strCurrentDate . '","2")\';>';
        echo '<img src="images/' . $arrret['image'] . '" border="0" height="11" width="11">';
        echo '1 Day';
        echo '</td>';

        echo '</tr>';
        echo '</table>';
    }
    echo '</div>';
    // The hours in the day....
    if ($intPrint == 1) {
        echo '<div style="overflow: hidden; float: left;  position: relative; height:30px; top:' . $currenttop . 'px" id="top" class="times">';
    } else {
        echo '<div style="overflow: hidden scroll; float: left;  position: relative; width:86%;height:30px; top:' . $currenttop . 'px" id="top" class="times">';
    }

    if ($selectedDay > 1) {
        $outerwidth = 1500 / $selectedDay;
        $intHourwidth = $outerwidth / 4;
        $intleftwidth = $intHourwidth;
        for ($j = $earlieststart; $j < $selectedDay; $j++) {
            $editdatasus = 'N';
            $date = date("Y-m-d", strtotime("$strCurrentDate + $j day"));
            if (!empty($arrDateEditable) && isset($arrDateEditable[$date]['IsDayEditable']) && $arrDateEditable[$date]['IsDayEditable'] == 1 && isset($arrDateLock[$date]['LockStatus']) && $arrDateLock[$date]['LockStatus'] == 1) {
                $editdatasus = 'Y';
            }
            $disdate = date("jS F Y - D", strtotime("$strCurrentDate + $j day"));
            echo '<span class="selectedDays" style="width:' . $outerwidth . 'px; background-color: coral; border:1px solid black;  position:absolute; text-align:center; left:' . (($j - $earlieststart) * $outerwidth) . 'px; top:0px">' . $disdate . '</span>';
            echo '<div class="outergrid" style="width:' . $outerwidth . 'px; background-color: coral; border:1px solid black;  position:absolute; left:' . (($j - $earlieststart) * $outerwidth) . 'px; top:13px">';
            $k = 0;
            for ($i = $earlieststart; $i < ($lateststart + $earlieststart); $i += 6) {
                echo '<div style="width:' . $intHourwidth . 'px;  position:absolute; left:' . (($k) * $intleftwidth) . 'px; top:0px">';
                echo '<div class="timebar" style="position: absolute; left: 0px; top:0px; width:' . $intHourwidth . 'px; height:15px">' . ($i) . '</div>';
                echo '</div>';
                $k++;
            }
            echo $j;
            echo '</div>';
        }
    } else {
        $lateststart = $lateststart + $showdutyend;
        for ($i = $earlieststart; $i < ($lateststart); $i++) {
            echo '<div style="width:' . $intHourwidth . 'px;  position:absolute; left:' . (($i - $earlieststart) * $intHourwidth) . 'px; top:0px">';
            echo '<div class="timebar" style="position: absolute; left: 0px; top:1px; width:' . $intHourwidth . 'px; height:15px">' . (gmdate("H:00", $i * 3600)) . '</div>';
            echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
            echo '</div>';
        }
    }
    echo '</div>';
    echo '</div>';

    // ============================================================= Shift Leaders
    $currenttop = $currenttop + 0;
    $arrLeaderTypes = GetLeaderTypes($intTeamID);
    $arrLeaders = GetLeaders($intWeek, $intDay);
    $lateststart = ($lateststart + $earlieststart) * $selectedDay;
    if (isset($arrLeaderTypes)) {
        echo '<div style="position: relative; top:2px; width: 100%; display:inline-block;" id="headerfourth">';
        if ($selectedDay > 1) {
            $intHourwidth = round($outerwidth / $hoursinday);
        }

        if (count($arrLeaderTypes) > 0) {
            $countshiftleaders = count($arrLeaderTypes);
        } else {
            $countshiftleaders = 0;
        }

        foreach ($arrLeaderTypes as $intLeaderTypeID => $arrLeaderType) {
            if ($arrLeaderType['SchedulingTeamId'] == $intTeamID) {
                $leaderheight = doleaders($intLeaderTypeID, $arrLeaderTypes, $arrLeaders, $arrLeaderType, $earlieststart, $lateststart, $intHourwidth, $intTeamID, $currenttop, $strCurrentDate, $intPrint, $rolepermission, $filterQuery1, $filterQuery2, $filterQuery3, $filterOrderStr, $editpermission, $hasShiftleaderRole,$skillFilterDaily, $dutyFilterDaily, $jobFilterDaily, $jobNameAll, $jobLabelAll);
                $currenttop = $currenttop + $leaderheight + 1;
            }
        }
        foreach ($arrLeaderTypes as $intLeaderTypeID => $arrLeaderType) {
            if ($arrLeaderType['SchedulingTeamId'] != $intTeamID) {
                $leaderheight = doleaders($intLeaderTypeID, $arrLeaderTypes, $arrLeaders, $arrLeaderType, $earlieststart, $lateststart, $intHourwidth, $intTeamID, $currenttop, $strCurrentDate, $intPrint, $rolepermission, $filterQuery1, $filterQuery2, $filterQuery3, $filterOrderStr, $editpermission, $hasShiftleaderRole,$skillFilterDaily, $dutyFilterDaily, $jobFilterDaily, $jobNameAll, $jobLabelAll);
                $currenttop = $currenttop + $leaderheight + 1;
            }
        }

    } else {
        echo '<div style="top:0px; width: 100%;height:30px;" id="headerfourth">';
    }
    echo '</div>';
    if (!isset($countshiftleaders)) {
        $countshiftleaders = 0;
    }
    $unallocatedBlockHeight = $currenttop;
    echo '<input type="hidden" id="unallocatedBlockHeight" value="' . $unallocatedBlockHeight . 'px" />';
    echo '<input type="hidden" id="shiftleadercount" value="' . $countshiftleaders . '" />';
    echo '<input type="hidden" id="unallocsectionheight" value="' . $unallocatedBlockHeight . '" />';
    echo '<input type="hidden" id="editpermissionval" value="' . $editpermission . '" />';
    // Do we show unallocated Duties
    // ######## The names down the left side #######################################
    $allocatedheight = 200;

    if ($editpermission == 1) {
        if ($intPrint == 1) {
            echo '<div style="display: block; top: 0;  width: 100%; position: relative;height: 100%;" id="allocunallocid">';
        } else {
            echo '<div style="display: block; top: 0;  width: 100%; position: relative;height: 67vh;" id="allocunallocid">';
        }
        if ($countunallocated != 0) {
            // The  Left of Unallocated Duties
            $unallocheight = 200;
            if ($unallocheight > ($countunallocated * $intRowHeight)) {
                $maxheight = "200px";
            } else {
                $maxheight = "100%";
            }
            if ($intPrint == 1) {
                echo '<div style="display: ' . $displayUnallocated . '; position: relative; height: 50% ;top:0px;" >';
                echo '<div style="display: inline-block; position: relative; width: 100%; height:' . ($countunallocated * $intRowHeight) . 'px" >';
                echo '<div style="overflow: hidden; position: relative; width:14%; float: left;height:' . ($countunallocated * $intRowHeight) . 'px;  left:0px;border-right:1px solid #fff"  class="names">';
            } else {
                echo '<div style="display: ' . $displayUnallocated . '; position: relative; height: 50vh; top:0px;" class="unallocdutygrid resiz allocationSections resizeBox1" >';
                echo '<div style="display: inline-block; position: relative; width: 100%; height:75%;">';
                echo '<div style="overflow: hidden; position: relative; width:14%; float: left; min-height: 100%; left:0px;border-right:1px solid #fff"  class="names">';
            }
            echo 'Unallocated Duties';
            if ($hideduties == 0) {
                echo '<div style="overflow: hidden; position: relative; width:25px; height:25px; left:86%; top:0px" data-qtip-content="Hide Duties with no jobs" class="tipjob handcursor" onclick=\'javascript:HideDuties(1)\' onmouseover="customtiptitle(\'showtooltip\',this,\'customTipHideDuties\')" onmouseleave="customtiptitle(\'hidetooltip\',\'\',\'customTipHideDuties\')" id="customTipHideDuties">';
                echo '<img border="0" src="images/hide.png" width="18" height="17">';
                echo '</div>';
            } else {
                echo '<div style="overflow: hidden; position: relative; width:25px; height:25px; left:86%; top:0px" data-qtip-content="Show Duties with no jobs" class="tipjob handcursor" onclick=\'javascript:HideDuties(0)\' onmouseover="customtiptitle(\'showtooltip\',this,\'customTipShowDuties\')" onmouseleave="customtiptitle(\'hidetooltip\',\'\',\'customTipShowDuties\')" id="customTipShowDuties">';
                echo '<img border="0" src="images/unhide.png" width="18" height="17">';
                echo '</div>';
            }
            if ($editpermission == 1) {
                echo '<div style="overflow: hidden; position: relative; width:25px; height:17px; left:86%; top:20px" data-qtip-content="Create a new Duty" class="tipjob handcursor" onclick=\'javascript:NewDuty()\' onmouseover="customtiptitle(\'showtooltip\',this,\'customTipCreateDuty\')" onmouseleave="customtiptitle(\'hidetooltip\',\'\',\'customTipCreateDuty\')" id="customTipCreateDuty">';
                echo '<img border="0" src="images/menu/new.png" width="18" height="17">';
                echo '</div>';
            }
            echo '</div>';

            // End  Left of Unallocated Duties
            // =================================== The unallocated duties
            if ($intPrint == 1) {
                echo '<div style="position: absolute; width: 100%;left:14%;  height:75%;" id="unallocatedduties">';
            } else {
                echo '<div style="overflow-y: scroll;overflow-x: hidden; position: relative; width:86%; min-height:100%; height: auto;  float: left; " id="unallocatedduties">';
            }

            for ($j = 1; $j <= $selectedDay; $j++) {
                $counter = 0;
                if ($selectedDay > 1) {
                    /**Edit Permission For Day Start */
                    $ik = $j - 1;
                    $date = date("Y-m-d", strtotime("$strCurrentDate + $ik day"));
                    if ((!empty($arrDateEditable) && isset($arrDateEditable[$date]['IsDayEditable']) && $arrDateEditable[$date]['IsDayEditable'] == 1 && isset($arrDateLock[$date]['LockStatus']) && $arrDateLock[$date]['LockStatus'] == 1) || ($editpermission == 1)) {
                        $editpermission = 1;
                    } else {
                        $editpermission = 0;
                    }
                    if ($editpermission == 1) {
                        $draggable = ' draggable jobresizable ';
                        $allocationcontextmenunotworking = ' allocation-context-menu-notworking';
                        $editedallocationcontextmenu = ' resizable edited-allocation-context-menu';
                        $jobcontextmenu = ' job-context-menu';
                        $allocationgreycontextmenu = ' allocation-absent-context-menu';
                    } else {
                        $draggable = '';
                        $editedallocationcontextmenu = '';
                        $jobcontextmenu = '';
                        $allocationcontextmenunotworking = '';
                        $allocationgreycontextmenu = '';
                    }
                    /**Edit Permission For Day END */

                    $left = (($j * $outerwidth) - $outerwidth);
                } else {
                    $intHourwidth = $userwebconfigResult["result"][0]["HourWidth"] ?? 0;
                    // check $intHourwidth value, if its 0 then set defult value
                    if ($intHourwidth == 0) {
                        $intHourwidth = 75;
                    }
                }
                $redlineval = 0;
                if ($j == 1) {
                    $redlineval = 1;
                }
                $totunallocated = $lateststart * $intHourwidth;
                if ($selectedDay > 1) {
                    $intHourwidth = round($outerwidth / $hoursinday);
                    $totunallocated = $hoursinday * $selectedDay * $intHourwidth;
                }
                $midunalloc = ($j - 1) * 86400;
                if ($unallocheight > ($countunallocated * $intRowHeight)) {
                    drawtimecellsdaily($strCurrentDate, $earlieststart, $lateststart, $intHourwidth, ($unallocheight + 1) . "px", 1, $redlineval, $j);
                } else {
                    drawtimecellsdaily($strCurrentDate, $earlieststart, $lateststart, $intHourwidth, (($countunallocated * $intRowHeight) + 1) . "px", 1, $redlineval, $j);
                }

                if (isset($arrAllocations['unassigned'][$j])) {
                    foreach ($arrAllocations['unassigned'][$j] as $dutyid => $value) {
                        if (isset($value["backcolour"])) {
                            $dutybackcolour = $value["backcolour"];
                        } else {
                            $dutybackcolour = '#ffffff';
                        }

                        if (isset($value["fontcolour"])) {
                            $dutyfontcolour = $value["fontcolour"];
                        } else {
                            $dutyfontcolour = '#000000';
                        }
                        $left = (((($midunalloc + $value["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                        $right = (((($midunalloc + $value["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                        if ($right <= $left) {
                            $right = $right + ($intHourwidth * 24);
                        }
                        $width = $right - $left;
                        if ($value["starttime"] == 0 && $value["endtime"] == 0 && $value["duration"] != '') {
                            $width = ((($value["duration"] / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                        }
                        $dutyunallocdroppable = '';
                        $ondropfunction = '';
                        if ($value["starttime"] != 0 || $value["endtime"] != 0) {
                            $dutyunallocdroppable = 'dutyunallocdroppable';
                        }

                        $position = "absolute";
                        if (empty($value["starttime"]) && empty($value["endtime"])) {
                            $width = $intHourwidth;
                            $position = "sticky";
                        }
                        $unallocatedDoubleClickEdit = '';
                        if (strtolower((string) $value["duty"]) != 'sick') {
                          $unallocatedDoubleClickEdit = 'ondblclick="editUnallocatedDuty($(this))"';
                        }
                        echo '<input type="hidden" id="dutystart_' . $dutyid . '" value=' . gmdate("H:i", $value["starttime"]) . '>';
                        echo '<input type="hidden" id="duty_end_' . $dutyid . '" value=' . gmdate("H:i", $value["endtime"]) . '>';
                        $overFlowCssUnalloc = '';
                        if ($selectedDay > 1) {
                            $overFlowCssUnalloc = ' overflow:hidden;';
                        }
                        echo '<div class="unallocduties" style="position: absolute; width:' . $totunallocated . 'px; height:' . ($intRowHeight) . 'px; left:0px; top:' . ($counter * $intRowHeight) . 'px; '.$overFlowCssUnalloc.'">';
                        echo '<div ' . $unallocatedDoubleClickEdit . ' id="' . $dutyid . '" isEdited="' . $value['edited'] . '" parentId="' . $value['AllocationParentID'] . '" data-teamid ="' . $intTeamID . '" data-dutydate="' . $value["dutyDate"] . '" data-iday="' . $intDay . '" data-weeknum="' . $intWeek . '" data-attention="' . $isAttention . '" data-edp=""  data-duty-name="' . $value["duty"] . '" class="boxed ' . $editedallocationcontextmenu . ' handcursor ' . $dutyunallocdroppable . '" style="width: ' . $width . 'px; height: ' . ($intDutyHeight - 6) . 'px; position:absolute; left:' . $left . 'px; top:3px; background-color:' . $dutybackcolour . ';z-index:1;" >';
                        echo '<font color="' . $dutyfontcolour . '">';
                        echo $value["duty"];
                        if (!empty($value["starttime"]) || !empty($value["endtime"])) {
                            echo ' (' . gmdate("H:i", $value["starttime"]) . '-' . gmdate("H:i", $value["endtime"]) . ')';
                        } else {
                            echo '  >>>>';
                        }
                        echo '</font>';
                        if ($value["edited"] == 1) {
                            echo '<div class="dailyisedited"></div>';
                        }
                        echo '</div>';

                        // Any Comments?
                        if ($value["dutycomments"] != '') {
                            $dutytipcontent = $value["dutycomments"];
                            $dutyIDval = $value["DutyID"] ?? 0;
                            echo '<div data-qtip-content="' . $dutytipcontent . '" class="handcursor tipdutycomments" style="position: absolute; width:12px; height:12px; left:' . ($left + $width - 15) . 'px; top:6px" onmouseover="customtipdutycomments(\'showtooltip\',this,' . $dutyIDval . ')" onmouseleave="customtipdutycomments(\'hidetooltip\')" id="dutyComments' . $dutyIDval . '">';
                            echo '<img src="images/info.png" border="0" height="12" width="12">';
                            echo '</div>';
                        }
                        // The jobs.....
                        if (isset($value['jobs'])) {
                            foreach ($value['jobs'] as $allocatejobid => $job) {
                                if (!isset($arrEditedAllocations['jobs'][$allocatejobid])) {
                                    $jobedited = $job["edited"];
                                    $jobbackcolour = $job["backcolour"];
                                    $jobfontcolour = $job["fontcolour"];
                                    $jobid = $job["jobid"];
                                    if ($value['starttime'] > $job["starttime"] && $value['starttime'] > $job["endtime"]) {
                                        $job["starttime"] += 86400;
                                        $job["endtime"] += 86400;
                                        $left = (((($midunalloc + $job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                                        $right = (((($midunalloc + $job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                                    } elseif ($job["starttime"] > $job["endtime"]) {
                                        $job["endtime"] += 86400;
                                        $left = (((($midunalloc + $job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                                        $right = (((($midunalloc + $job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                                    } else {
                                        $left = (((($midunalloc + $job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                                        $right = (((($midunalloc + $job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                                    }
                                    $width = $right - $left;
                                    $jobqtipcontent = '<font color=#99CCFF>' . $job["jobname"] . '</font><br>';
                                    $jobqtipcontent .= gmdate("H:i", $job["starttime"]) . '-' . gmdate("H:i", $job["endtime"]);
                                    if ($job["Job_Info"] != '') {
                                        $jobqtipcontent .= '<br><font color=#00CC00>';
                                        $jobqtipcontent .= $job["Job_Info"] . '</font>';
                                    }
                                    if (!empty($job["programme"])) {
                                        $jobqtipcontent .= '<br>(' . $job["programme"] . ')';
                                    }
                                    $jobqtipcontent = htmlspecialchars($jobqtipcontent);
                                    if (isset($value['dutyId']) && $value['dutyId'] != '') {
                                        $DutyID = $value['dutyId'];
                                    } else {
                                        $DutyID = 0;
                                    }

                                    echo '<div align="center" data-qtip-content="' . $jobqtipcontent . '" dutyid="' . $DutyID . '" id="' . $jobid . '" isEdited="' . $jobedited . '" parentId="' . $job['JobParentID'] . '"  data-teamid ="' . $intTeamID . '" data-iday="' . $intDay . '" data-weeknum="' . $intWeek . '"  class="boxed ' . $draggable . ' tipjob handcursor' . $jobcontextmenu . '" style="overflow:hidden; width: ' . $width . 'px; height: ' . (($intDutyHeight / 2) - 5) . 'px; position:absolute; left:' . $left . 'px; top:' . (($intDutyHeight / 2) - 4) . 'px; background-color:' . $jobbackcolour . ';z-index:1;" onmouseover="customtipjob(\'showtooltip\',this,' . $jobid . ',1,100)"  onmouseleave="customtipjob(\'hidetooltip\')">';
                                    echo '<font color="' . $jobfontcolour . '">' . $job["jobname"] . '</font>';
                                    echo '</div>';
                                    echo '<input type="hidden" id="unaljobstart_' . $jobid . '" value=' . gmdate("H:i", $job["starttime"]) . '>';
                                    echo '<input type="hidden" id="unaljobend_' . $jobid . '" value=' . gmdate("H:i", $job["endtime"]) . '>';
									echo '<input type="hidden" id="jobmidnight_' . $jobid . '" value=' . $job["MidnightFlag"] . '>';
                                }
                            }
                        }
                        echo '</div>';
                        $counter++;
                    }
                }

            }
            echo '</div>';
        } else {
            $unallocheight = 0;
        }
        $currenttop = $currenttop + $unallocheight;
        // End the unallocated Duties
        echo '</div>';
        if ($intPrint == 1) {
            $countjobar = [];
            for ($j = 1; $j <= $selectedDay; $j++) {
                // Any unallocated jobs?
                if ($j > 1) {
                    $jobdate = date("Y-m-d", strtotime("+" . ($j - 1) . "day", (strtotime((string) $strCurrentDate))));
                } else {
                    $jobdate = $strCurrentDate;
                }
                $arrjobs = drawunallocjobs($arrAllocations['jobs'][$jobdate], $earlieststart, $intHourwidth, $editpermission, $intTeamID, $selectedDay, $j);
                if (isset($arrjobs['jobs'])) {
                    $countjobar[] = $arrjobs['rows'];
                }
            }
            $printheight = max($countjobar) * 20;
            echo '<div  id="allocatedjobdiv" style="position: relative; width: 100%; top:0px; height: ' . $printheight . 'px; display: block;">';
        } else {

            echo '<div  id="allocatedjobdiv" style="position: relative; width: 100%; height: 25%; display: inline-block; bottom: 3px;"  class="resiz allocationSections resizeBox2">';

        }

        if ($selectedDay > 1) {
            for ($j = 1; $j <= $selectedDay; $j++) {
                // Any unallocated jobs?
                if ($j > 1) {
                    $jobdate = date("Y-m-d", strtotime("+" . ($j - 1) . "day", (strtotime((string) $strCurrentDate))));
                } else {
                    $jobdate = $strCurrentDate;
                }
                $arrjobs = drawunallocjobs($arrAllocations['jobs'][$jobdate] ?? [], $earlieststart, $intHourwidth, $editpermission, $intTeamID, $selectedDay, $j);
                $intUnallocJobHeight = 100;
            }
        } else {
            $intHourwidth = isset($userwebconfigResult["result"][0]["HourWidth"]) ? (int) $userwebconfigResult["result"][0]["HourWidth"] : 0;
            // check $intHourwidth value, if its 0 then set defult value
            if ($intHourwidth == 0) {
                $intHourwidth = 75;
            }
            if (isset($arrAllocations['jobs'][$strCurrentDate])) {
                $allocationjobs = $arrAllocations['jobs'][$strCurrentDate];
            } else {
                $allocationjobs = [];
            }
            $arrjobs = drawunallocjobs($allocationjobs, $earlieststart, $intHourwidth, $editpermission, $intTeamID, $selectedDay, 1);
            $intUnallocJobHeight = 100;
        }
        if ($editpermission == 1) {
            echo '<div style= "background-color:grey;z-index:1;height: 2px;position: sticky;  top:0;width:100%;">&nbsp;</div>';
            if ($intPrint == 1) {
                echo '<div style="overflow: hidden; position: relative; width:14%; float: left; height:' . $printheight . 'px;  left:0px;border-right:1px solid #fff;"   class="names">';
                echo 'Unallocated Jobs';
                echo '</div>';
                echo '<div style="overflow: hidden; position: absolute; width:25px; left:12%; top: 0; " data-qtip-content="Create a new Job" class="tipjob handcursor" onclick=\'javascript:NewJob()\' onmouseover="customtiptitle(\'showtooltip\',this,\'customTipCreateJobs\')" onmouseleave="customtiptitle(\'hidetooltip\',\'\',\'customTipCreateJobs\')" id="customTipCreateJobs">';
                echo '<img border="0" src="images/menu/new.png" width="18" height="17">';
                echo '</div>';
            } else {
                echo '<div style="overflow: hidden; position: relative; width:14%; float: left; max-height:' . $intUnallocJobHeight . 'px;height:100%;  left:0px;border-right:1px solid #fff;"   class="names">';
                echo 'Unallocated Jobs';
                echo '</div>';

                echo '<div style="overflow: hidden; position: absolute; width:25px; max-height:' . $intUnallocJobHeight . 'px;height:100%;  left:12%; top: 0; " data-qtip-content="Create a new Job" class="tipjob handcursor" onclick=\'javascript:NewJob()\' onmouseover="customtiptitle(\'showtooltip\',this,\'customTipCreateJobs\')" onmouseleave="customtiptitle(\'hidetooltip\',\'\',\'customTipCreateJobs\')" id="customTipCreateJobs">';
                echo '<img border="0" src="images/menu/new.png" width="18" height="17">';
                echo '</div>';
            }
        } else {
            echo '<div style="overflow: hidden; position: relative; width:14%; float: left; max-height:' . $intUnallocJobHeight . 'px;height:' . ($arrjobs['rows'] * 20) . 'px;  left:0px;border-right:1px solid #fff;"  class="names">';
            echo 'Unallocated Jobs';
            echo '</div>';
        }

        if ($selectedDay > 1) {
            $overFlowYUnAllocJobs = 'auto;';
            $widthUnallocJobs = '86%;';
            if(empty($arrAllocations['jobs'])){
                $overFlowYUnAllocJobs = 'hidden;';
                $widthUnallocJobs = '85.5%;';
            }
            echo '<div class="jobsunallocgrid" style="overflow-y:'.$overFlowYUnAllocJobs.' overflow-x: hidden; position: relative; float: left; width:'.$widthUnallocJobs.' max-height:' . $intUnallocJobHeight . 'px;height:100%" id="unallocatedjobsgrid">';
            // End The  Left of Unallocated Jobs
            if ($selectedDay > 1) {
                $intHourwidth = round($outerwidth / $hoursinday);
            }
            $countjobar = [];

            for ($j = 1; $j <= $selectedDay; $j++) {
                $midval = ($j - 1) * 86400;
                $ik = $j - 1;
                $date = date("Y-m-d", strtotime("$strCurrentDate + $ik day"));
                if ((!empty($arrDateEditable) && isset($arrDateEditable[$date]['IsDayEditable']) && $arrDateEditable[$date]['IsDayEditable'] == 1 && isset($arrDateLock[$date]['LockStatus']) && $arrDateLock[$date]['LockStatus'] == 1) || ($editpermission == 1)) {
                    $editpermission = 1;
                } else {
                    $editpermission = 0;
                }
                // The Unallocated Jobs
                $left = (($j * $outerwidth) - $outerwidth);
                echo '<div class="jobsunallocdroppable" style="overflow: hidden; width:' . $outerwidth . 'px; height:100%;display:inline-block;"   id="unallocatedjobs">';
                $counter = 0;
                $redlineval = 0;
                if ($j == 1) {
                    $redlineval = 1;
                }
                drawtimecellsdaily($strCurrentDate, $earlieststart, $lateststart, $intHourwidth, '160px', 1, $redlineval, $j);

                if ($j > 1) {
                    $jobdate = date("Y-m-d", strtotime("+" . ($j - 1) . "day", (strtotime((string) $strCurrentDate))));
                } else {
                    $jobdate = $strCurrentDate;
                }

                if (isset($arrAllocations['jobs'][$jobdate])) {
                    $arrjobs = drawunallocjobs($arrAllocations['jobs'][$jobdate], $earlieststart, $intHourwidth, $editpermission, $intTeamID, $selectedDay, $j);
                    if (isset($arrjobs['jobs'])) {
                        echo $arrjobs['jobs'];
                        $countjobar[] = $arrjobs['rows'];
                    }
                }
                echo '</div>';
            } // j loop jobs
            echo '</div>'; // new added div
        } else {
            // selected day
            // The Unallocated Jobs
            if ($intPrint == 1) {
                echo '<div class="jobsunallocdroppable" style="position: absolute; max-height:' . $intUnallocJobHeight . 'px;height:100%; left:14%;" id="unallocatedjobs">';
            } else {
                echo '<div class="jobsunallocdroppable" style="overflow-y: scroll; overflow-x: hidden;position: absolute; width:86%; max-height:' . $intUnallocJobHeight . 'px;height:100%; left:14%;z-index:1;" id="unallocatedjobs">';
            }
            $counter = 0;
            $redlineval = 1;
            if (($arrjobs['rows'] * 20) > $intUnallocJobHeight) {
                $jobheight = ($arrjobs['rows'] * 20) . "px";
            } else {
                $jobheight = $intUnallocJobHeight . "px";
            }
            drawtimecellsdaily($strCurrentDate, $earlieststart, $lateststart, $intHourwidth, $jobheight, 1, $redlineval, 0);
            if (isset($arrjobs['jobs'])) {
                echo $arrjobs['jobs'];
            }
            echo '</div>';
        }
        echo '</div>';
        if ($selectedDay > 1) {
            $currenttop = $currenttop + 20;
        } else {
            if (($arrjobs['rows'] * 20) > 100) {
                $currenttop = $currenttop + $intUnallocJobHeight + 1;
            } else {
                $currenttop = $currenttop + ($arrjobs['rows'] * 20) + 1;
            }
        }
        $unallocatedBlockHeight_1 = $currenttop;
        echo '<input type="hidden" id="unallocatedBlockHeight_1" value="' . $unallocatedBlockHeight_1 . 'px" />';
        echo '<input type="hidden" id="allocscrolltop" value="' . $unallocatedBlockHeight_1 . '"/>';
        //End The Unallocated Jobs
        echo '</div>';
    }

    // ######## The names down the left side #######################################
    $allocatedheight = 200;
    echo '<div id="allocatedDutydaily" style="background-color: #fff; padding-top:0px; width: 100%; display: block; position: relative; height: 50% !important;" class="resiz allocationSections resizeBox3">';
    echo '<div style= "background-color:grey;z-index:2;height: 2px;position: sticky;  top:0;width:100%;" id="allocatemid" >&nbsp;</div>';

    if ($intPrint == 1) {
        echo '<div style="position: absolute; overflow:scroll;width:14%;left:0px; top:0px; height: 100%" class="names resiz" id="Allocatednames">';
    } else {
        echo '<div style="overflow:scroll;position: absolute; width:14%; height:100%; left:0px; top:0px;" class="names resiz" id="Allocatednames">';
    }
    $schPersonNames = [];
    for ($j = 1; $j <= $selectedDay; $j++) {
        $counter = 0;
        if (isset($arrAllocations['assigned'][$j])) {
            foreach ($arrAllocations['assigned'][$j] as $dutyid => $value) {
                // Get the sign in details so we can highlight the name if they havn't signed in......
                $flashit = '';
                $allowsignin = 0;
                $parentDutyID = $value['DutyParentID'];
                if (($intSignInDays > 0) && (strtotime((string) $strCurrentDate) >= strtotime(date("Y-m-d"))) && (strtotime((string) $strCurrentDate) <= strtotime("+" . $intSignInDays . " days", strtotime(date("Y-m-d")))) && ($value["starttime"] > 0 || $value["endtime"] > 0) && (isset($value['duty']) && strtoupper((string) $value['duty']) != 'U' && strtolower((string) $value['duty']) != 'off leave' && strtolower((string) $value['duty']) != 'leave') && $value['miscduty'] == 0) {
                    $allowsignin = 1;
                    $InBuilding = $value["inbuilding"];
                    if ($value["editable"] != 0) {
                        $editable = 1;
                    } else {
                        $editable = $value["editable"];
                    }

                    if (($editable == 1) && ($allowInBuilding == 1) && ($strCurrentDate == date("Y-m-d")) && ($value["inbuilding"] == 0) && (($value["starttime"] + 540) < (date('G') * 3600 + date('i') * 60))) {
                        $flashit = ' flashit';
                    }
                    if ($value['signin'] == 0) {
                        $img = 'red_cross.png';
                        $action = 1;
                    } else {
                        switch ($value["signin"]) {
                            case 1:
                                if ($InBuilding == 1) {
                                    // Signed in OK
                                    $img = 'blue_tick.png';
                                    $action = 0;
                                } else {
                                    // Signed in OK
                                    $img = 'green_tick.png';
                                    $action = 0;
                                }
                                break;
                            // Signed in NOT OK
                            case 2:
                                $img = 'messagebox_warning.png';
                                $action = 1;
                                break;
                            default:
                                // Not signed in
                                //$flashit = '';
                                $img = 'red_cross.png';
                                $action = 1;
                                break;
                        }
                    }
                    $allocationsigninid = $value['DutyID'];

                    if (($strCurrentDate == date("Y-m-d")) && ($allowInBuilding == 1)) {
                        if ($intCanSignin == 1) {
                            $js = ' onclick=\'javascript:SignInToDay("' . $strCurrentDate . '","' . $value['duty'] . '",' . $action . ', "' . $value['starttime'] . '", "'.$value['endtime'].'", "' . $value['allocationSPID'] . '", "'.$value['DutyID'].'")\';';
                        } else {
                            if ($strMyStaffNumber == $value["ScheduledPersonID"]) {
                                $js = ' onclick=\'javascript:SignInToDay("' . $strCurrentDate . '","' . $value['duty'] . '",' . $action . ', "' . $value['starttime'] . '", "'.$value['endtime'].'", "' . $value['allocationSPID'] . '", "'.$value['DutyID'].'")\';';
                            } else {
                                $js = '';
                            }
                        }
                    } else {
                        if ($intCanSignin == 1) {
                            $js = ' onclick=\'javascript:SignInDay("' . $strCurrentDate . '","' . $value['duty'] . '",' . $action . ', "' . $value['starttime'] . '", "'.$value['endtime'].'", "' . $value['allocationSPID'] . '", "'.$value['DutyID'].'")\';';
                        } else {
                            if ($strMyStaffNumber == $value["ScheduledPersonID"]) {
                                $js = ' onclick=\'javascript:SignInDay("' . $strCurrentDate . '","' . $value['duty'] . '",' . $action . ', "' . $value['starttime'] . '", "'.$value['endtime'].'", "' . $value['allocationSPID'] . '", "'.$value['DutyID'].'")\';';
                            } else {
                                $js = '';
                            }
                        }
                    }
                }

                if ($flashit == '') {
                    $hashit = '';
                } else {
                    $hashit = 'cellflashhash';
                }
                $textcolour = $value["StaffTextColour"];
                if ($flashit != '') {
                    $textcolour = '#000000';
                }
						$TriangleColour = $value["TriangleColour"] ? mb_strtolower($value["TriangleColour"]) : '';
				        $triangleColour = $TriangleColour != 'none' ? $value["TriangleColour"] : 'transparent';
                if((($isSchedulingTeamViewer == 1) || ($isScheduledPerson == 1)) && (($hasShiftleaderRole == 1) || ($isManager == 1)))
                {
                  $triangleColour = 'transparent';
                }
                $backgroundcolour = $value["StaffBackColour"];
                echo '<div class="' . $hashit . $flashit . '  names handcursor person-data-cell" style="position: absolute; width:100%; padding:4px; height:' . ($intRowHeight - 1) . 'px; left:0px; top:' . ($counter * $intRowHeight) . 'px ; background-color:' . $backgroundcolour . ' " onclick=\'javascript:ShowDailyRota("' . $strCurrentDate . '","' . $value['ScheduledPersonID'] . '",' . $intTeamID . ')\';
                data-id="' . $value['ScheduledPersonID'] . '"  data-order="'. $value["fullname"] .'"
                data-sort-code="' . $value["sortcode"] . '"
                data-cost-code="' . $value["CostCode"] . '"
                >';
                echo '<b><font color="' . $textcolour . '">';
                if($value["fullname"] != ''){
                    echo mb_convert_encoding($value["fullname"], 'UTF-8', 'ISO-8859-1');
                    if(!isset($schPersonNames[$value['ScheduledPersonID']])){
                        $schPersonNames[$value['ScheduledPersonID']] = mb_convert_encoding($value["fullname"], 'UTF-8', 'ISO-8859-1');
                    }
                } else {
                    if(isset($schPersonNames[$dutyid])){
                        echo $schPersonNames[$dutyid] ? mb_convert_encoding($schPersonNames[$dutyid], 'UTF-8', 'ISO-8859-1') : '';
                    }
                }
                echo '<div id="schPersonId_'.$value['ScheduledPersonID'].'" class="DutyIconPositionLeft" style="font-size:15px; top:25px; color:'.$triangleColour.'" >&#9699</div></b><br>';
                echo mb_convert_encoding($value["sortcode"], 'UTF-8', 'ISO-8859-1');
                echo '</font></div>';

                if (($value["personcomments"] != '') && (($intCanViewPersonComments != 0) && ($intCanViewComments != 0))) {
                    echo '<div dutydate="' . $strCurrentDate . '" dataTeamId="' . $intTeamID . '" StaffNumber="' . $value["StaffNumber"] . '" dataSchPersonId="' . $value['ScheduledPersonID'] . '" data-allocation-id="' . $value['DutyParentID'] . '" data-allocation-spid="' . $value['allocationSPID'] . '" id="' . $value['DutyID'] . '"  class="handcursor tipremotepersoncomments" style="position: absolute; width:12px; height:12px; left:90%; top:' . (($counter * $intRowHeight) + ($intRowHeight - 20)) . 'px" onmouseover="customtipremotepersoncommentsleft(\'showtooltip\',this,' . $value["DutyID"] . ')" onmouseleave="customtipremotepersoncommentsleft(\'hidetooltip\')">';
                    echo '<img src="images/info.png" border="0" height="12" width="12">';
                    echo '</div>';
                    $edpleft = 82;
                } else {
                    $edpleft = 82;
                }
                if (isset($arrEDP[$value['ScheduledPersonID']][$strCurrentDate])) {
                    echo '<div StaffNumber="' . $value["StaffNumber"] . '" dataSchPersonId="' . $value['ScheduledPersonID'] . '" class="handcursor tipremoteedp" style="position: absolute; width:12px; height:12px; left:' . $edpleft . '%; top:' . (($counter * $intRowHeight) + ($intRowHeight - 20)) . 'px" onmouseover="customtipremoteedp(\'showtooltip\',this)" onmouseleave="customtipremoteedp(\'hidetooltip\')">';
                    echo '<img src="images/overtime.png" border="0" height="16" width="16">';
                    echo '</div>';
                }
                if ($allowsignin == 1) {
                    echo '<div dutyid="' . $value['DutyID'] . '" SchedulingPersonID="' . $value['ScheduledPersonID'] . '"  allocationSPID="' . $value['allocationSPID'] . '"  DutyName="' . $value['duty'] . '"  StartTime="' . $value['starttime'] . '"  EndTime="' . $value['endtime'] . '" class="handcursor signedtip" style="position: absolute; width:12px; height:12px; left:90%; top:' . (($counter * $intRowHeight)) . 'px"' . $js . ' onmouseover="customsignedtip(\'showtooltip\',this,' . $value['DutyID'] . ')" onmouseleave="customsignedtip(\'hidetooltip\')" >';
                    if (($strCurrentDate == date("Y-m-d") || $strCurrentDate == date("Y-m-d", strtotime("+1 Day"))) && $img == 'red_cross.png') {
                        echo '<img src="images/' . $img . '" border="0" height="18px" width="18px">';
                    } else {
                        echo '<img src="images/' . $img . '" border="0" height="12px" width="12px">';
                    }
                    echo '</div>';
                }

                // Locks and requests
                if (isset($arrRequests[$value["ScheduledPersonID"]])) {

                    $strImage = '';
                    foreach ($arrRequests[$value["ScheduledPersonID"]] as $lockkey => $rdata) {
                        if ($lockkey == $intDay) {
                            if ($rdata['IsLock'] == 1) {
                                $strTitle = 'Day Is Locked<br>' . $rdata['TipText'];
                                $strImage = 'locked';
                            } else {
                                $strTitle = $rdata['Description'] . '<br>';
                                if ($rdata['Approved'] == "1") {
                                    $strTitle .= 'Approved';
                                    $strImage = 'requested';
                                } else {
                                    if ($rdata['Approved'] == 1) {
                                        $strTitle .= 'OK - Not yet Approved';
                                        $strImage = 'requested';
                                    } else {
                                        $strTitle .= 'Waiting List - Not yet Approved';
                                        $strImage = 'requestedInQ';
                                    }
                                }
                            }
                        }
                    }
                    if(!empty($value["ScheduledPersonID"]) && $strImage != "") {
                      echo '<div id="locks_' . $value["ScheduledPersonID"] . '" class="handcursor" style="position: absolute; width:12px; height:12px; left:73%; top:' . (($counter * $intRowHeight) + ($intRowHeight / 2)) . 'px">';
                          echo '<img data-title="' . $strTitle . '" width="15" height="15" border="0" src="images/locks/' . $strImage . '.png" onmouseover="customtiptitlewhiteleft(\'showtooltip\',this,\'lockToolTip\')" onmouseleave="customtiptitlewhiteleft(\'hidetooltip\')"></img>';
                      echo '</div>';
                    }
                }
                $counter++;
            }
        }
    } //j loop allocated
    echo '</div>';

    // ########## The names down the left side ###################################################
    // ########### The duties in the div  ##################################################
    if ($intPrint == 1) {
        echo '<div style="position: absolute; width:1200px; left:14%; top:0px; bottom: 0;  height:100%;" id="Allocatedduties" class="dayholderparthour resiz">';
    } else {
        if ($selectedDay == 1) {
            $intHourwidth = $userwebconfigResult["result"][0]["HourWidth"] ?? 0;
            // check $intHourwidth value, if its 0 then set defult value
            if ($intHourwidth == 0) {
                $intHourwidth = 75;
            }
            echo '<div style="overflow: auto; position: absolute; width:calc(86% + 1px); bottom: 0;  height:100%; left:calc(14% - 0.8px); bottom:0px ;top:0px; z-index:1;" id="Allocatedduties" class="dayholderparthour resiz">';
        } else {
            if ($intHourwidth == 0) {
                $intHourwidth = 75;
            }
            echo '<div style="overflow: auto;position: absolute; width:86%; bottom: 0;  height:100%; left:14%;  bottom:0px;top:0px; z-index:1;" id="Allocatedduties" class="dayholderparthour resiz">';
        }
    }

    for ($j = 1; $j <= $selectedDay; $j++) {
        $counter = 0;
        if ($selectedDay > 1) {
            /**Edit Permission For Day Start */
            $ik = $j - 1;
            $date = date("Y-m-d", strtotime("$strCurrentDate + $ik day"));
            if ((!empty($arrDateEditable) && isset($arrDateEditable[$date]['IsDayEditable']) && $arrDateEditable[$date]['IsDayEditable'] == 1 && isset($arrDateLock[$date]['LockStatus']) && $arrDateLock[$date]['LockStatus'] == 1) || ($editpermission == 1)) {
                $editpermission = 1;
            } else {
                $editpermission = 0;
            }

            if ($editpermission == 1) {
                $draggable = ' draggable jobresizable ';
                $allocationcontextmenunotworking = ' allocation-context-menu-notworking';
                $editedallocationcontextmenu = ' resizable edited-allocation-context-menu';
                $jobcontextmenu = ' job-context-menu';
                $allocationgreycontextmenu = ' allocation-absent-context-menu';
                $zindex = "";
            } else {
                $draggable = '';
                $editedallocationcontextmenu = '';
                $jobcontextmenu = '';
                $allocationcontextmenunotworking = '';
                $allocationgreycontextmenu = '';
                $zindex = "z-index:1;";
            }
            /**Edit Permission For Day END */
            $left = (($j * $outerwidth) - $outerwidth);
        }
        $redlineval = 0;
        if ($j == 1) {
            $redlineval = 1;
        }
        if ($selectedDay > 1) {
            $intHourwidth = round($outerwidth / $hoursinday);
        }

        $midval = ($j - 1) * 86400;
        drawtimecellsdaily($strCurrentDate, $earlieststart, $lateststart, $intHourwidth, (($countallocations/$selectedDay) * $intRowHeight) . "px", 0, $redlineval, $j);

        if (isset($arrAllocations['assigned'][$j])) {
            $dutyIncCounter = 1;
            foreach ($arrAllocations['assigned'][$j] as $dutyid => $value) {
                $dutyedited = $value['edited'] ?? 0;
                $zindex ??= '';
                $overFlowCss = '';
                if ($selectedDay > 1) {
                    $overFlowCss = ' overflow:hidden;';
                }
                if ($intPrint == 1) {
                    echo '<div class="duties" style="position: absolute; width:' . ($lateststart * $intHourwidth) . 'px; height:' . ($intRowHeight - 1) . 'px; left:0px; ' . $zindex . 'top:' . ($counter * $intRowHeight) . 'px; '.$overFlowCss.'">';
                } else {
                    echo '<div class="duties" style="position: absolute; width:' . ($lateststart * $intHourwidth) . 'px; height:' . ($intRowHeight - 1) . 'px; left:0px; ' . $zindex . 'top:' . ($counter * $intRowHeight) . 'px; '.$overFlowCss.'">';
                }
                $left = (((($midval + $value["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                $right = (((($midval + $value["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;

                if (isset($value["backcolour"])) {
                    $dutybackcolour = $value["backcolour"];
                } else {
                    $dutybackcolour = '#ffffff';
                }
                if (isset($value["fontcolour"])) {
                    $dutyfontcolour = $value["fontcolour"];
                } else {
                    $dutyfontcolour = '#000000';
                }
                if ($right <= $left) {
                    $right = $right + ($intHourwidth * 24);
                }
                $width = $right - $left;

                if (isset($value["IsAttention"])) {
                    $isAttention = $value["IsAttention"];
                } else {
                    $isAttention = 0;
                }

                if (isset($value["IsRequest"])) {
                    $isRequested = $value["IsRequest"];
                } else {
                    $isRequested = 0;
                }

                if (isset($value["ManualEDP"])) {
                    $dataEdp = $value['ManualEDP'] == 0 ? "C" : "M";
                } else {
                    $dataEdp = "C";
                }
                if ($value['IsAttention'] != 0) {
                    $dutyColorId = GetColorIdByName("Mark Attention");
                    $colorValue = getColorValueByColorId($dutyColorId);
                    $dutybackcolour = '#' . $colorValue['ColourBackground'];
                    $dutyfontcolour = '#' . $colorValue['ColourFont'];
                }
                if ($value['IsAttention'] == 2) {
                  $colorValue = getColorValueByColorId($dutyColorId);
                  $dutybackcolour = '#9370db';
                  $dutyfontcolour = '#' . $colorValue['ColourFont'];
                }
                if (array_key_exists('IsRequest', $value) && is_array($value) && !empty($value['IsRequest'])) {
                    $dutyColorId = GetColorIdByName("Mark Request");
                    $colorValue = getColorValueByColorId($dutyColorId);
                    if ($colorValue && isset($colorValue['ColourFont'])) {
                        $dutyfontcolour = '#' . $colorValue['ColourFont'];
                    } else {
                        $dutyfontcolour = '#000000'; // fallback color
                    }
                }
                $attentionClass = '';
                if ($value['MannualOThours'] > 0 && $value['IsAttention'] != 0) {
                    $attentionClass = 'stripped-bar';
                }

                $greenClass = $value['MarkedOvertime'] == 0 ? ' ' : 'green';
                $dataDutyDate = $value["DutyDate"];
                $postion = "absolute";
                if ($value["miscduty"] == 0) {
                    $miscduty = 0;

                } else {
                    $miscduty = 1;
                    if ($selectedDay == 1) {
                        $postion = "sticky";
                    }
                }
                if ((($value["miscduty"] == 1) || ($value["duty"] == "Sick")) && ($intvalstartendshift == 1)) {
                    $displayduty = "none";
                } else {
                    $displayduty = "block";
                }
                $dutydroppableclass = '';
                if ($value["starttime"] != 0 || $value["endtime"] != 0) {
                    $dutydroppableclass = 'dutydroppable';
                }
				        $dataChargingPresent = mb_strtolower((!empty($value["TriangleColour"]) ? $value["TriangleColour"] : 'none')) != 'none' ? 1 : 0;
                if ($editpermission == 1) {
                    echo '<div data-charging-present="'.$dataChargingPresent.'" data-id="' . $value['allocationSPID'] . '_' . $dataDutyDate . '" id="' . $value['DutyID'] . '" isEdited="' . $dutyedited . '" pdl-start-time="' . $value['pdlStartTime'] . '" pdl-end-time="' . $value['pdlEndTime'] . '" parentId="' . $value['DutyParentID'] . '" scheduledPersonId="' . $value["ScheduledPersonID"] . '" data-teamid ="' . $intTeamID . '" data-iday="' . $intDay . '"data-weeknum="' . $intWeek . '" data-attention="' . $isAttention . '" data-request="' . $isRequested . '" data-edp="' . $dataEdp . '" data-duty-name="' . $value["duty"] . '" data-dutydate="' . $dataDutyDate . '" data-ispublished="' . $value['isPublished'] . '" data-overtime="' . $value['MarkedOvertime'] . '" data-ishometeam="' . $value['IsHomeTeam'] . '" misc ="' . $miscduty . '" data-allocation-spid="' . $value['allocationSPID'] . '" data-allocation-duty-id="' . $value['allocationDutyID'] . '" class="' . $dutydroppableclass . ' boxed ' . $attentionClass . ' ' . $greenClass . ' cellID_' . $dataDutyDate . '_' . $value['ScheduledPersonID'] . ' allocated_duty_' . $value['ScheduledPersonID'] . ' ';
					if (($value["duty"] == 'Absent') && ($value['DutyID'] != '')) {
                        echo 'allocation-absentrestore-context-menu ';
                    }else
					{
						if (($value["ScheduledPersonID"] != 0) && ($value["editable"] == 1) && ($value["duty"] != 'U') && ($value["duty"] != 'Leave') && ($value["duty"] != 'OFF Leave') && ($miscduty != '1')) {
							//
							echo 'allocation-context-menu resizable ';
						} elseif ($value["duty"] == 'Leave' || $value["duty"] == 'OFF Leave') {
							echo 'allocation-leave-context-menu ';
						} elseif(($value["duty"] == 'Sick') || ($value["duty"] == 'U-Sick') || ($value["duty"] == '-Sick')) {
							echo 'allocation-absent-context-menu ';
						} elseif (($value["edited"] == 0) && ($value['DutyID'] != '')) {
							echo 'allocation-absent-context-menu ';
						} elseif (($value["duty"] == 'Absent') && ($value['DutyID'] != '')) {
							echo 'allocation-absentrestore-context-menu ';
						} else {
							echo 'allocation-context-menu ';
						}
					}
                    if (($value["duty"] == 'Sick') || ($value["miscduty"] == 1)) {
                        $width = $intHourwidth;
                    }
                    $dutyDivZIndex = 'z-index: 1;';
                    $dutybackcolour = $dutybackcolour;
                    $displayduty = $displayduty;
                    if(empty($value)){
                        $dutyDivZIndex = '';
                        $dutybackcolour = '';
                        $displayduty = 'none;';
                    }
                    echo 'handcursor" style="width: ' . $width . 'px; '.$dutyDivZIndex.' height: ' . ($intDutyHeight - 6) . 'px; position:' . $postion . '; display : ' . $displayduty . ' ;left:' . $left . 'px; text-align:left;top:3px; background-color:' . $dutybackcolour . '"';
                    if (strtolower((string) $value["duty"]) != 'sick' && strtolower((string) $value["duty"]) != 'u-sick') {
                      if($value['DutyID'] == 0) {
						  $value['allocationSPID'] = $value['allocationSPID'] ? $value['allocationSPID'] : 0;
                        echo 'ondblclick="javascript:NewDuty(' . $value['ScheduledPersonID'] . ', \''. $dataDutyDate .'\',' . $value['allocationSPID'] . ',' . $value['allocationDutyID'] . ',' . $value['DutyParentID'] . ')"';
                      } else {
                        echo 'ondblclick="javascript:editDailyDuty(' . $value['DutyID'] . ',' . $intWeek . ', ' . $intDay . ',' . $intTeamID . ',' . $shiftleaderflag . ',' . $value['DutyParentID'] . ',' . $value['ScheduledPersonID'] . ',\'' . $dataDutyDate . '\',' . $miscduty . ',' . $value['pdlStartTime'] . ',' . $value['pdlEndTime'] . ',' . $value['allocationSPID'] . ',' . $value['allocationDutyID'] . ',' . $value['DutyParentID'] . ')"';
                      }
                    }
                    echo ' data-duty-labels="' . implode(',', $value['DutyLabels'] ?? []) . '" ';
                    echo ' data-duty-start-time="' . $value['starttime'] . '" ';
                    echo ' data-duty-end-time="' . $value['endtime'] . '" ';
                    echo '>';
                } else {
                    if ($value["iscopy"] == 1) {
                        echo '<div id="' . $value['DutyID'] . '" data-duty-labels="' . implode(',', $value['DutyLabels'] ?? []) . '" data-duty-start-time="' . $value['starttime'] . '" data-duty-end-time="' . $value['endtime'] . '"  isEdited="' . $dutyedited . '" parentId="' . $value['DutyParentID'] . '" data-attention="' . $isAttention . '" data-request="' . $isRequested . '" data-scheduledpersonid = "' . $value['ScheduledPersonID'] . '"  data-teamid ="' . $intTeamID . '" data-iday="' . $intDay . '" data-weeknum="' . $intWeek . '" data-dateonly = "' . $strCurrentDate . '" data-edp="' . $dataEdp . '" data-duty-name="' . $value["duty"] . '" data-dutydate="' . $dataDutyDate . '" data-ispublished="' . $value['isPublished'] . '" data-allocation-spid="' . $value['allocationSPID'] . '" class="boxed ' . $allocationgreycontextmenu . ' handcursor allocated_duty_' . $value['ScheduledPersonID'] . '"' . $greenClass . '" style="width: ' . $width . 'px; height: ' . ($intDutyHeight - 6) . 'px; position:absolute; left:' . $left . 'px; text-align:left ;top:3px; background-color:' . $dutybackcolour . '" data-allocation-duty-id="' . $value['allocationDutyID'] . '">';
                    } else {
                        echo '<div id="' . $value['DutyID'] . '" data-duty-labels="' . implode(',', $value['DutyLabels'] ?? []) . '" data-duty-start-time="' . $value['starttime'] . '" data-duty-end-time="' . $value['endtime'] . '"  isEdited="' . $dutyedited . '" parentId="' . $value['DutyParentID'] . '" data-attention="' . $isAttention . '" data-request="' . $isRequested . '" data-scheduledpersonid = "' . $value['ScheduledPersonID'] . '"  data-teamid ="' . $intTeamID . '" data-iday="' . $intDay . '" data-weeknum="' . $intWeek . '" data-dateonly = "' . $strCurrentDate . '" data-edp="' . $dataEdp . '" data-duty-name="' . $value["duty"] . '" data-dutydate="' . $dataDutyDate . '" data-ispublished="' . $value['isPublished'] . '" data-allocation-spid="' . $value['allocationSPID'] . '" class="boxed allocated_duty_' . $value['ScheduledPersonID'] . '" style="width: ' . $width . 'px; height: ' . ($intDutyHeight - 6) . 'px; position:absolute; left:' . $left . 'px; top:3px; background-color:' . $dutybackcolour . '" data-allocation-duty-id="' . $value['allocationDutyID'] . '">';
                    }
                }
                echo '<font color="' . $dutyfontcolour . '">';
                echo $value["duty"];
                if ($value["miscduty"] == 0) {
                    if ($value["duty"] != 'Sick') {
                        echo ' (' . gmdate("H:i", $value["starttime"]) . '-' . gmdate("H:i", $value["endtime"]) . ')';
                        if(($value['pdlStartTime'] != 0) || ($value['pdlEndTime'] != 0)){
                            $pStTime = (int) $value['pdlStartTime'];
                            $pEdTime =  (int) $value['pdlEndTime'];
                            if($pStTime > $pEdTime){
                                $pEdTime = (86400 + $pEdTime);
                            }
                            $pdlTimeTitle = 'PDL: '.$service->convertSecondsIntoTime($pStTime,':','No').'-'.$service->convertSecondsIntoTime($pEdTime,':','No').' | '.$service->convertSecondsIntoTime(($pEdTime-$pStTime),'.','Yes');

                            echo " <span style='color:#109146; padding-left:4px;' title='".$pdlTimeTitle."'>[L: ".$service->convertSecondsIntoTime($pStTime,':','No').'-'.$service->convertSecondsIntoTime($pEdTime,':','No').' | '.$service->convertSecondsIntoTime(($pEdTime-$pStTime),'.','Yes').']</span>';
                        }
                    }
                    echo '<input type="hidden" id="dutystart_' . $value['DutyID'] . '" value=' . gmdate("H:i", $value["starttime"]) . '>';
                    echo '<input type="hidden" id="duty_end_' . $value['DutyID'] . '" value=' . gmdate("H:i", $value["endtime"]) . '>';
                    echo '<input type="hidden" id="dutyduration_' . $value["DutyID"] . '" value=' . $value["duration"] . '>';
                } else {
                    if (($value["duty"] != 'Sick') && ($value["duty"] != 'U-Sick') && ($value["duty"] != '-Sick')) {
                        echo '  >>>>';
                    }
                }

                echo '</font>';
                if ($value["internaledited"] == 1) {
                    echo '<div class="dailyisinternallyedited"></div>';
                } else if ($value["isEdited"] == 1) {
                    echo '<div class="dailyisedited"></div>';
                }
                echo '</div>';
                if ($value["dutycomments"] != '') {
                    if ($value["endtime"] > 12600) {
                        echo '<div dutydate="' . $strCurrentDate . '" dataTeamId="' . $intTeamID . '" StaffNumber="' . $value["StaffNumber"] . '" dataSchPersonId="' . $value['ScheduledPersonID'] . '" data-allocation-id="' . $value['DutyParentID'] . '" data-allocation-spid="' . $value['allocationSPID'] . '" id="' . $value['DutyID'] . '"  class="handcursor tipremotepersoncomments" style="position: absolute; width:12px; height:12px; left:' . ($left + $width - 13) . 'px; top:5px;z-index:1" onmouseover="customtipremotepersoncomments(\'showtooltip\',this,' . $value["DutyID"] . ')" onmouseleave="customtipremotepersoncomments(\'hidetooltip\')">';
                        echo '<img src="images/info.png" border="0" height="12" width="12">';
                        echo '</div>';
                    } else {
                        echo '<div dutydate="' . $strCurrentDate . '" dataTeamId="' . $intTeamID . '" StaffNumber="' . $value["StaffNumber"] . '" dataSchPersonId="' . $value['ScheduledPersonID'] . '" data-allocation-id="' . $value['DutyParentID'] . '" data-allocation-spid="' . $value['allocationSPID'] . '" id="' . $value['DutyID'] . '"  class="handcursor tipremotepersoncomments" style="position: absolute; width:12px; height:12px; left:' . ($left + $width - 13) . 'px; top:5px;z-index:1" onmouseover="customtipremotepersoncommentsleft(\'showtooltip\',this,' . $value["DutyID"] . ')" onmouseleave="customtipremotepersoncommentsleft(\'hidetooltip\')">';
                        echo '<img src="images/info.png" border="0" height="12" width="12">';
                        echo '</div>';
                    }
                }

                // The jobs.....
                if (isset($value['jobs'])) {
                    $pdlJobId = 0;
                    foreach ($value['jobs'] as $jobid => $job) {
                        $jobbackcolour = $job["backcolour"];
                        $jobfontcolour = $job["fontcolour"];

                        $jobid = $job["jobid"];
                        if ($value['starttime'] > $job["starttime"] && $value['starttime'] > $job["endtime"]) {
                            $job["starttime"] += 86400;
                            $job["endtime"] += 86400;
                            $left = (((($midval + $job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                            $right = (((($midval + $job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                        } elseif ($job["starttime"] > $job["endtime"]) {
                            $job["endtime"] += 86400;
                            $left = (((($midval + $job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                            $right = (((($midval + $job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                        } else {
                            $left = (((($midval + $job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                            $right = (((($midval + $job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                        }

                        $width = $right - $left;
                        $jobqtipcontent = '<font color=#99CCFF>' . $job["jobname"] . '</font><br>';
                        $jobqtipcontent .= gmdate("H:i", $job["starttime"]) . '-' . gmdate("H:i", $job["endtime"]);
                        if ($job["Job_Info"] != '') {
                            $jobqtipcontent .= '<br><font color=#00CC00>';
                            $jobqtipcontent .= $job["Job_Info"] . '</font>';
                        }
                        if ($job["programme"] != '') {
                            $jobqtipcontent .= '<br>(' . $job["programme"] . ')';
                        }
                        $jobqtipcontent = htmlspecialchars($jobqtipcontent);

                        $jobDraggable = $draggable;
                        $pdljobcontextmenu = $jobcontextmenu;
                        if(strtoupper((string) $job["jobname"]) == 'LEAVE'){
                            $pdlJobId = $jobid;
                            $jobDraggable = '';
                            $pdljobcontextmenu = '';
                            $jobbackcolour = '#109146';
                            $jobfontcolour = '#000000';
                        }
                        echo '<div align="center" data-job-name="' . $job["jobname"] . '" data-job-programme="' . $job['programmeid'] . '"  data-qtip-content="' . $jobqtipcontent . '" id="' . $jobid . '" dutyid="' . $value['DutyID'] . '" dutytype="1" isEdited="' . $job["edited"] . '" parentId="' . $job['JobParentID'] . '"   data-teamid ="' . $intTeamID . '" data-iday="' . $intDay . '" data-weeknum="' . $intWeek . '" data-scheduled-person-id="' . ($value['ScheduledPersonID'] ?? 0) . '"   class="boxed ' . $jobDraggable . ' tipjob handcursor' . $pdljobcontextmenu . ' allocated_duty_job_' . ($value['ScheduledPersonID'] ?? 0) . '" style="overflow:hidden; width: ' . $width . 'px; height: ' . (($intDutyHeight / 2) - 5) . 'px; position:absolute; left:' . $left . 'px; top:' . ($intDutyHeight / 2) . 'px; z-index:1; background-color:' . $jobbackcolour . '" onmouseover="customtipjob(\'showtooltip\',this,' . $jobid . ',' . $dutyIncCounter . ',' . count($arrAllocations['assigned'][$j]) . ')"  onmouseleave="customtipjob(\'hidetooltip\')" data-allocation-spid="' . $value['allocationSPID'] . '" data-duty-parentId="' . $value['DutyParentID'] . '">';
                        echo '<input type="hidden" id="unaljobstart_' . $jobid . '" value=' . gmdate("H:i", $job["starttime"]) . '>';
                        echo '<input type="hidden" id="unaljobend_' . $jobid . '" value=' . gmdate("H:i", $job["endtime"]) . '>';
                        echo '<input type="hidden" id="jobmidnight_' . $jobid . '" value=' . ($job["MidnightFlag"] ?? '') . '>';
                        echo '<font color="' . $jobfontcolour . '">' . $job["jobname"] . '</font>';

                        if ($job["edited"] == 1) {
                            echo '<div class="dailyisedited"></div>';
                        }
                        echo '</div>';
                    }
                    if (isset($value['jobs'][$pdlJobId]) && $value['jobs'][$pdlJobId] !== null) {
                      if((count($value['jobs']) == 1) && (strtoupper((string) $value['jobs'][$pdlJobId]['jobname']) == 'LEAVE') && ($pdlJobId > 0)){
                          $jobbackcolour = '#FFFFFF';
                          $jobfontcolour = '#000000';

                          $pdlJobStartTime = $value['jobs'][$pdlJobId]['starttime'];
                          $pdlJobEndTime = $value['jobs'][$pdlJobId]['endtime'];
                          if($pdlJobStartTime > $pdlJobEndTime){
                              $pdlJobEndTime = (int) (86400+$pdlJobEndTime);
                          }

                          if($value["starttime"] < $pdlJobStartTime && $pdlJobEndTime < $value["endtime"]){
                              $left1 = (((($midval + $value["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                              $right1 = (((($midval + $pdlJobStartTime) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                              $width1 = $right1 - $left1;
                              $qtipJobId1 = (int) $pdlJobId.'1';

                              $jobqtipcontentw1 = '<font color=#99CCFF>Working</font><br>';
                              $jobqtipcontentw1 .= $service->convertSecondsIntoTime($value["starttime"],':','No') . '-' . $service->convertSecondsIntoTime($pdlJobStartTime,':','No');
                              $jobqtipcontentw1 = htmlspecialchars($jobqtipcontentw1);

                              echo '<div align="center" data-qtip-content="' . $jobqtipcontentw1 . '" id="PDL' . $qtipJobId1 . '" class="boxed tipjob handcursor" style="overflow:hidden; width: ' . $width1 . 'px; height: ' . (($intDutyHeight / 2) - 5) . 'px; position:absolute; left:' . $left1 . 'px; top:' . ($intDutyHeight / 2) . 'px; z-index:1; background-color:' . $jobbackcolour . '" onmouseover="customtipjob(\'showtooltip\',this,' . $qtipJobId1 . ',' . $dutyIncCounter . ',' . count($arrAllocations['assigned'][$j]) . ',\'PDL\')"  onmouseleave="customtipjob(\'hidetooltip\')">';
                              echo '<font color="' . $jobfontcolour . '">Working</font>';
                              echo '</div>';

                              $left2 = (((($midval + $pdlJobEndTime) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                              $right2 = (((($midval + $value["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                              $width2 = $right2 - $left2;
                              $qtipJobId2 = (int) $pdlJobId.'2';

                              $jobqtipcontentw2 = '<font color=#99CCFF>Working</font><br>';
                              $jobqtipcontentw2 .= $service->convertSecondsIntoTime($pdlJobEndTime,':','No') . '-' . $service->convertSecondsIntoTime($value["endtime"],':','No');
                              $jobqtipcontentw2 = htmlspecialchars($jobqtipcontentw2);

                              echo '<div align="center" data-qtip-content="' . $jobqtipcontentw2 . '" id="PDL' . $qtipJobId2 . '" class="boxed tipjob handcursor" style="overflow:hidden; width: ' . $width2 . 'px; height: ' . (($intDutyHeight / 2) - 5) . 'px; position:absolute; left:' . $left2 . 'px; top:' . ($intDutyHeight / 2) . 'px; z-index:1; background-color:' . $jobbackcolour . '" onmouseover="customtipjob(\'showtooltip\',this,' . $qtipJobId2 . ',' . $dutyIncCounter . ',' . count($arrAllocations['assigned'][$j]) . ',\'PDL\')"  onmouseleave="customtipjob(\'hidetooltip\')">';
                              echo '<font color="' . $jobfontcolour . '">Working</font>';
                              echo '</div>';
                          }
                          if($value["starttime"] == $pdlJobStartTime && $pdlJobEndTime < $value["endtime"]){
                              $left2 = (((($midval + $pdlJobEndTime) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                              $right2 = (((($midval + $value["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                              $width2 = $right2 - $left2;
                              $qtipJobId1 = (int) $pdlJobId.'1';

                              $jobqtipcontentw1 = '<font color=#99CCFF>Working</font><br>';
                              $jobqtipcontentw1 .= $service->convertSecondsIntoTime($pdlJobEndTime,':','No') . '-' . $service->convertSecondsIntoTime($value["endtime"],':','No');
                              $jobqtipcontentw1 = htmlspecialchars($jobqtipcontentw1);

                              echo '<div align="center" data-qtip-content="' . $jobqtipcontentw1 . '" id="PDL' . $qtipJobId1 . '" class="boxed tipjob handcursor" style="overflow:hidden; width: ' . $width2 . 'px; height: ' . (($intDutyHeight / 2) - 5) . 'px; position:absolute; left:' . $left2 . 'px; top:' . ($intDutyHeight / 2) . 'px; z-index:1; background-color:' . $jobbackcolour . '" onmouseover="customtipjob(\'showtooltip\',this,' . $qtipJobId1 . ',' . $dutyIncCounter . ',' . count($arrAllocations['assigned'][$j]) . ',\'PDL\')"  onmouseleave="customtipjob(\'hidetooltip\')">';
                              echo '<font color="' . $jobfontcolour . '">Working</font>';
                              echo '</div>';
                          }
                          if($value["starttime"] < $pdlJobStartTime && $pdlJobEndTime == $value["endtime"]){
                              $left1 = (((($midval + $value["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                              $right1 = (((($midval + $job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
                              $width1 = $right1 - $left1;
                              $qtipJobId1 = (int) $pdlJobId.'1';

                              $jobqtipcontentw1 = '<font color=#99CCFF>Working</font><br>';
                              $jobqtipcontentw1 .= $service->convertSecondsIntoTime($value["starttime"],':','No') . '-' . $service->convertSecondsIntoTime($pdlJobStartTime,':','No');
                              $jobqtipcontentw1 = htmlspecialchars($jobqtipcontentw1);

                              echo '<div align="center" data-qtip-content="' . $jobqtipcontentw1 . '" id="PDL' . $qtipJobId1 . '" class="boxed tipjob handcursor" style="overflow:hidden; width: ' . $width1 . 'px; height: ' . (($intDutyHeight / 2) - 5) . 'px; position:absolute; left:' . $left1 . 'px; top:' . ($intDutyHeight / 2) . 'px; z-index:1; background-color:' . $jobbackcolour . '" onmouseover="customtipjob(\'showtooltip\',this,' . $qtipJobId1 . ',' . $dutyIncCounter . ',' . count($arrAllocations['assigned'][$j]) . ',\'PDL\')"  onmouseleave="customtipjob(\'hidetooltip\')">';
                              echo '<font color="' . $jobfontcolour . '">Working</font>';
                              echo '</div>';
                          }
                      }
                    }
                }
                echo '</div>';
                $counter++;
                $dutyIncCounter++;
            }
        }
    } // j loop right allocated close

    echo '</div>';
    ############## END The duties in the div  ################################################
    echo '</div>';

    $windowheightoffset = $currenttop + 120;
} else {
    echo '<br>';
    echo '<div id="duties">';
    echo '<div class="tableheadersmall bigtextboldcentre" style="position: absolute; width:1000px; left:250px; top:100px">';
    echo '<br><br>';
    echo 'Unable to display Allocations for this day.<br>';
    echo '<br></br><br>';
    echo '</div>';
    echo '</div>';
    $windowheightoffset = 0;
}
echo "</div>";
//die(__file__."/".__line__);
// adding this hidden input variable to read from index.php on first page load
echo '<input id="heightOffset" type="hidden" name="" value="' . $windowheightoffset . '"/>';
echo '<div id="drag_helper">';
?>
<?php if ($intPrint == 0) {
    ?>
<script language="JavaScript" type="text/javascript">
  function getDayNo(selectedDay) {
    var dayselectedvalue = selectedDay;
    var selFilterType = $('#selFilterType').val();
    var selFilterId = $('#selFilterId').val();
    var dayselectedvalue = selectedDay;
    if(selectedDay=='default') {
      selectedDay = 1;
    }
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/allocations-daily.php',
      data: {
        'selectedDay': selectedDay,
        'teamId': <?php echo $intTeamID; ?>,
        'queryStr1': "<?php echo $filterQuery1; ?>",
        'queryStr2': "<?php echo $filterQuery2; ?>",
        'queryStr3': "<?php echo $filterQuery3; ?>",
        'orderStr': "<?php echo $filterOrderStr; ?>",
        'selFilterType': selFilterType,
        'selFilterId': selFilterId,
        'date': $('#strCurrentDate').val()
      },
      success: function (data) {
        $('#content').html(data);
        $('#days').val(dayselectedvalue);
        if(selFilterId != ''){
          applyViewFilter('ViewDaily',selFilterId);
        }
      }
    });
  }

  /** code for additional buttons yesterday, today, tomorrow */
  $(document).ready(function () {
      initializeButtons();
  });
function moveDays(direction) {
    const systemToday = new Date();
    systemToday.setHours(0, 0, 0, 0);
    let targetDate = new Date(systemToday);

    if (direction === 'yesterday') {
        targetDate.setDate(systemToday.getDate() - 1);
    } else if (direction === 'tomorrow') {
        targetDate.setDate(systemToday.getDate() + 1);
    }
    targetDate.setHours(0, 0, 0, 0);
    const limits = getDateLimits();
    // Stop if outside allowed range (retains limits but allows from any view date)
    if (targetDate < limits.minDate || targetDate > limits.maxDate) {
        return;
    }
    $('#strCurrentDate').val(formatDate(targetDate));
    // Update button states relative to system today
    updateButtons(systemToday, limits);
    // Reload allocations
    let teamId = $('#strCurrentTeam').val();
    ShowDailyAllocations(teamId, formatDate(targetDate));
}

function getCurrentPageDate() {
    const parts = $('#strCurrentDate').val().split('-');
    const date = new Date(parts[0], parts[1] - 1, parts[2]);
    date.setHours(0, 0, 0, 0);
    return date;
}

function getDateLimits() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const minDate = new Date(today);
    minDate.setDate(today.getDate() - 1); // Yesterday
    const maxDate = new Date(today);
    maxDate.setDate(today.getDate() + 1); // Tomorrow
    return { minDate, maxDate };
}

function updateButtons(currentDate, limits) {
    $('#yesterdayBtm').prop(
        'disabled',
        currentDate.getTime() === limits.minDate.getTime()
    );
    $('#tomorrowBtm').prop(
        'disabled',
        currentDate.getTime() === limits.maxDate.getTime()
    );
}

function initializeButtons() {
    const currentDate = getCurrentPageDate();
    const limits = getDateLimits();
    updateButtons(currentDate, limits);
}

function formatDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

  function allocationViewByTeam(teamId) {
    $('#strCurrentTeam').val(teamId);
    currentDate = $('#strCurrentDate').val();
    if (currentDate) {
      ShowDailyAllocations(teamId,currentDate);
    } else {
      today = new Date();
      currentDate  = today.getFullYear() + '-' + (today.getMonth()+1) + '-' + today.getDate();
      ShowDailyAllocations(teamId,currentDate);
    }
  }
  var scrollcheck=0;
  $(document).ready( function () {
    if ($('#editpermissionval').val()==1){
      $('#allocunallocid').css("height",window.innerHeight-(190+$('#shiftleadercount').val()*20));
    } else {
      $('#allocatedDutydaily').css("height",window.innerHeight-(190+$('#shiftleadercount').val()*20));
    }
    Mousetrap.bind("alt+j", function() { NewJob() });
    Mousetrap.bind("esc", function() { $.facebox.close(); });
    var allocatedscreen=(window.innerHeight*3)/4-10;
    $(function () {
      $(".resiz").resizable({
        handles: 's',
        minHeight: 100,
        start: function(e, ui) {
          if ($(this).next('.resiz').length > 0) {
            other = $(this).next('.resiz');
          } else {
            other = $(this).prev('.resiz');
          }
          startingHeight = other.height();
        },
        stop: function(e, ui) {
          var unallocheight = ui.size.height;
          $.cookie("screenunallocheight",unallocheight);
        },
        resize: function(e, ui) {
          var dh = ui.size.height - ui.originalSize.height;
          if (dh > startingHeight) { // can't resize the box more then it's neighbour
            dh = startingHeight;
            ui.size.height = ui.originalSize.height + dh;
          }
          other.height(startingHeight - dh);
          $('#allocatedjobdiv').css('min-height', '15%');
          $('#allocatedjobdiv').css('max-height', '30%');
          $(".unallocdutygrid").css("max-height", "50%");
          $("#allocatedDutydaily").css("min-height", "50%");
        }
      });

      for (let i = 0; i < $('.ui-resizable-handle').length; i++) {
        if (i==4){
          $('.ui-resizable-handle')[i].className = '';
        }
      }

            $("#unallocatedduty").on("click", function () {
        if ($(this).is(":checked")) {
            var starttime = $("#starttime").val();
            var hourMark = '<?php echo $intHourwidth; ?>';
            var startTimeSplit = starttime.substr(0, starttime.indexOf(':'));
            if ($.cookie("hscroll") == null ||  $.cookie("hscroll") == 0) {
            $("#Allocatedduties").scrollLeft((hourMark * startTimeSplit));
            $("#unallocatedduties").scrollLeft((hourMark * startTimeSplit));
            }
          $(".resiz").resizable('enable');
          $(".unallocdutygrid").attr("style", "display:block; height: 50%; position: relative;");
          $("#allocatedDutydaily").attr("style", "display:block; height: 50%; position: relative;");
          $(".ui-resizable-handle ui-resizable-e").attr("style", "display:block;");
          var checkboxval=1;
        }else{
          $(".resiz").resizable('disable');
          $(".unallocdutygrid").attr("style", "display:none");
          $("#allocatedDutydaily").attr("style", "display:block; height: 100%; position: relative;");
          $(".ui-resizable-handle").attr("style", "display:block;");
          var checkboxval=0;
                }
        $.ajax({
          type: 'POST',
          url: 'components/filters/filter-process.php',
          data: {
            'action': 'Dailycheckbox',
            'teamId': <?php echo $intTeamID ?>,
            'checkboxval': checkboxval
          }
        });

      });

      if ($("#unallocatedduty").is(":checked")) {
        $(".resiz").resizable('enable');
        if((typeof $.cookie('screenunallocheight')=='undefined') ||  ($.cookie('screenunallocheight')=='')) {
          $(".unallocdutygrid").attr("style", "height: 50%;display:block;  position: relative;");
          $("#allocatedDutydaily").attr("style", "display:block; height: 50%; position: relative;");
          $.cookie("screenunallocheight",'');
          $.cookie("screenallocheight",'');
        } else {
          $(".unallocdutygrid").attr("style", "height: "+$.cookie("screenunallocheight")+"px; display:block;  position: relative;");
          var allocheight = (window.innerHeight - (190+parseInt($.cookie("screenunallocheight")) + parseInt($('#shiftleadercount').val()*20)));
          $('#allocatedDutydaily').attr("style","height :"+allocheight+"px; display:block;  position: relative;");
          $('#allocatedjobdiv').css('min-height', '15%');
          $('#allocatedjobdiv').css('max-height', '30%');
          $(".unallocdutygrid").css("max-height", "50%");
          $("#allocatedDutydaily").css("min-height", "50%");
        }
        var unallocatedBlockHeight_1 = $("#unallocatedBlockHeight_1").val();
      }else{
        $(".resiz").resizable('disable');
          $(".unallocdutygrid").attr("style", "display:none");
        $('#allocatedDutydaily').css("height",window.innerHeight-(190+$('#shiftleadercount').val()*20));
      }

      $("#startendshift").on("click", function () {
        var selectedDay = $('#days').val();
        var dayselectedvalue = selectedDay;
        if(selectedDay=='default'){
          selectedDay = 1;
        }
        if ($(this).is(":checked")) {
          var checkstartendshift=1
        }else{
          var checkstartendshift=0
        }

        $.ajax({
          type: 'POST',
          url: 'components/filters/filter-process.php',
          data: {
            'action': 'Checkstartandend',
            'teamId': <?php echo $intTeamID ?>,
            'checkstartendshift': checkstartendshift
          }
        });

        $.ajax({
          type: 'POST',
          url: 'page-includes/allocations/allocations-daily.php',
          data: {
            'selectedDay': selectedDay,
            'valstartendshift': checkstartendshift,
            'teamId': <?php echo $intTeamID ?>,
            'queryStr1': "<?php echo $filterQuery1; ?>",
            'queryStr2': "<?php echo $filterQuery2; ?>",
            'queryStr3': "<?php echo $filterQuery3; ?>",
            'orderStr': "<?php echo $filterOrderStr; ?>",
            'selFilterType': "<?php echo $selFilterType; ?>",
            'selFilterId': "<?php echo $selFilterId; ?>",
            'skillFilterDaily': "<?php echo $skillFilterDaily; ?>",
            'dutyFilterDaily': "<?php echo $dutyFilterDaily; ?>",
            'jobFilterDaily': "<?php echo $jobFilterDaily; ?>",
			'screenName' : 'ViewDaily',
      'date': $('#strCurrentDate').val()
          },
          success: function (data) {
            $('#content').html(data);
            $('#days').val(dayselectedvalue);
            if (($("#selFilterId").val()!='') && ($("#selFilterId").val() > 0)){
              applyViewFilter('ViewDaily',$("#selFilterId").val());
            } else {
              if((localStorage.getItem('dailyscreen_filter') != '') && (localStorage.getItem('dailyscreen_filter') != null) && (localStorage.getItem('dailyscreen_filter') != 'undefined')) {
                applyViewFilter('ViewDaily',0);
              }
            }
          }
        });
      });

    });
    <?php
if ((isset($intAllowUserSort)) && ($intAllowUserSort == 1)) {
        if ($intSortOrder == 0) {
            echo 'Mousetrap.bind("alt+o", function() { SetSortOrder(1,' . $intTeamID . ')});';
        } else {
            echo 'Mousetrap.bind("alt+o", function() { SetSortOrder(0,' . $intTeamID . ')});';
        }
    }
    ?>

    Mousetrap.bind("alt+n", function() { ShowDailyAllocations(<?php echo $intTeamID ?>, '<?php echo $tomorrow ?>') });
    Mousetrap.bind("alt+p", function() { ShowDailyAllocations(<?php echo $intTeamID ?>, '<?php echo $yesterday ?>')});
    Mousetrap.bind("alt+c", function() {
      $("#accordion").accordion( "option", "active", 0 );
      $('#PresetFilters').focus();
    });
    $(".chosen-select").chosen({
      no_results_text: "Oops, nothing found!",
      width: "160px"
    });

    $(function() {
      $( "#accordion" ).accordion({
        heightStyle: "content",
        collapsible: true,
        active: false,
        beforeActivate: function( event, ui ) {
        }
      });
      $('.ui-accordion-content').css({
        "padding":"2px",
        "width":"500px"
      });
      $('.accordiontop').css({
        "width":"250px"
      });
    });
    $(document).click(function (event) {
      if(!$(event.target).closest('#accordion').length) {//if you clicked outside of the accordion
        $("#accordion").accordion({active: false});//collapse all the panels
      }
    });
    StartTimer();
    // If cookie is set, scroll to the position saved in the cookie.

    if ( ($.cookie("vscroll") !== null) && ($.cookie("vscroll") != undefined)) {
      $("#Allocatedduties").scrollTop($.cookie("vscroll"));
      $("#Allocatednames").scrollTop($.cookie("vscroll"));
    }

    $(function() {
      if ( ($.cookie("vscrollunalloc") !== null)  && ($.cookie("vscrollunalloc") != undefined)) {
        $("#unallocatedduties").scrollTop($.cookie("vscrollunalloc"));
      }
    });
    if ( ($.cookie("vscrollunallocjobs") !== null) && ($.cookie("vscrollunallocjobs") != undefined)) {
      $("#unallocatedjobs").scrollTop($.cookie("vscrollunallocjobs"));
    }

    if ( ($.cookie("hscroll") !== null) && ($.cookie("hscroll") != undefined)) {
      $("#Allocatedduties").scrollLeft($.cookie("hscroll"));
      $("#unallocatedduties").scrollLeft($.cookie("hscroll"));
    }

    // When scrolling happens....
    $("#Allocatedduties").on("scroll", function() {
    // Set a cookie that holds the scroll position.
      if (scrollcheck==1){
        $.cookie("vscroll", $("#Allocatedduties").scrollTop() );
        $.cookie("vscroll", $("#Allocatednames").scrollTop() );
        $.cookie("hscroll", $("#unallocatedduties").scrollLeft() );
        $.cookie("hscroll", $("#Allocatedduties").scrollLeft() );
      }
      scrollcheck=1;
    });

    $("#Allocatednames").on("scroll", function() {
    // Set a cookie that holds the scroll position.
      $.cookie("vscroll", $("#Allocatedduties").scrollTop() );
      $.cookie("vscroll", $("#Allocatednames").scrollTop() );
    });

    $("#unallocatedduties").on("scroll", function() {
      if (scrollcheck==1){
        // Set a cookie that holds the scroll position.
        $.cookie("vscrollunalloc", $("#unallocatedduties").scrollTop() );
      }
      scrollcheck=1;
    });

    $("#unallocatedjobs").on("scroll", function() {
      if (scrollcheck==1){
        // Set a cookie that holds the scroll position.
        $.cookie("vscrollunallocjobs", $("#unallocatedjobs").scrollTop());
      }
      scrollcheck=1;
    });


  })

  function HideDuties(action) {
    $(".tipjob").hide();
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/allocations-hideduties.php',
      data: {
        'action': action
      },
      success: function (data,status) {
        var currentDate = $('#strCurrentDate').val();
        ShowDailyAllocations('<?php echo $intTeamID ?>',currentDate)
      }
    });
  }
  function SetZoom(zoomamount,teamId) {
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/allocations-set-zoom.php',
      data: {
        'zoomamount': zoomamount,
        'teamId':teamId
      },
      success: function (data,status) {
        var currentDate = $('#strCurrentDate').val();
        ShowDailyAllocations(teamId,currentDate)
      }
    });
  }
  function StopTimer() {
    $('#countdown').timeTo("stop");
  }
  function StartTimer () {
    today=new Date();
    currentDate = $('#strCurrentDate').val();
    if (currentDate == false) {
      currentDate = today.getFullYear() + '-' + (today.getMonth()+1) + '-' + today.getDate();
    }
    $('#countdown').timeTo({
      seconds: 60,
      displayDays: 0,
      displayHours: 0,
      fontSize: 10,
      callback : function() {
        var day7date='<?php echo $day7date; ?>';
        var intTeamID=<?php echo $intTeamID; ?>;
        var isViewPage=<?php echo $isViewPage; ?>;
        var dutyhistoryid=<?php echo $dutyhistoryid; ?>;
        var jobhistoryid=<?php echo $jobhistoryid; ?>;
        var signinid=<?php echo $signinid; ?>;
        var signinlastupdate=<?php echo $signinlastupdate; ?>;
        $.ajax({
          type: 'POST',
          url: 'page-includes/allocations/edits/getlastupdate.php',
          data: {
            'strCurrentDate': currentDate,
            'day7date': day7date,
            'intTeamID': intTeamID,
            'isViewPage': isViewPage,
            'dutyhistoryid': dutyhistoryid,
            'jobhistoryid': jobhistoryid,
            'signinid':signinid,
            'signinlastupdate': signinlastupdate
          },
          success: function (data) {
            if (data == 1) {
              ShowDailyAllocations (intTeamID,currentDate);
            } else {
              StartTimer();
            }
          }
        });
      }
    });
  }
  function ShowDailyAllocationsOptions(base, date) {
    ShowDailyAllocations(base,date, 0, <?php echo $showall ?>);
  }
  function LockDay(teamId, date) {
    $(function() {
      $( "#dialog-lock-day" ).dialog( {
        width: 800,
        buttons: {
          "Lock": function() {
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/allocations-daylock.php',
              data: {
                'teamId': teamId,
                'date': date,
                'action': 0
              },
              success: function (data,status) {
                 ShowDailyAllocations (teamId,date);
              }
            });
            $( this ).dialog( "close" );
          },
          "Un-Lock": function() {
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/allocations-daylock.php',
              data: {
                'teamId': teamId,
                'date': date,
                'action': 1
              },
              success: function (data,status) {
                 ShowDailyAllocations (teamId,date);
              }
            });
            $( this ).dialog( "close" );
          },
          "Auto Timer": function() {
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/allocations-daylock.php',
              data: {
                'teamId': teamId,
                'date': date,
                'action': 2
              },
              success: function (data,status) {
                 ShowDailyAllocations (teamId,date);
              }
            });
            $( this ).dialog( "close" );
          },
          "Cancel": function() {
            $( this ).dialog( "close" );
          }
        }
      });
    });
  }
  function LockDayUnedited(teamId, date) {
    $(function() {
      $( "#dialog-lock-day" ).dialog({
        width: 800,
        buttons: {
          "Lock": function() {
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/allocations-daylock.php',
              data: {
                'teamId': teamId,
                'date': date,
                'action': 0
              },
              success: function (data,status) {
                 ShowDailyAllocations (teamId,date);
              }
            });
            $( this ).dialog( "close" );
          },
          "Un-Lock": function() {
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/allocations-daylock.php',
              data: {
                'teamId': teamId,
                'date': date,
                'action': 1
              },
              success: function (data,status) {
                 ShowDailyAllocations (teamId,date);
              }
            });
            $( this ).dialog( "close" );
                    },
          "Auto Timer": function() {
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/allocations-daylock.php',
              data: {
                'teamId': teamId,
                'date': date,
                'action': 2
              },
              success: function (data,status) {
                 ShowDailyAllocations (teamId,date);
              }
            });
            $( this ).dialog( "close" );
          },
          "Cancel": function() {
            $( this ).dialog( "close" );
          }
        }
      });
    });
  }

/**
* This function is used to display the Grid Checks.
* @param $intTeamID This param contains the Team ID.
* @param $date This param contains the date.
* @param $period This param contains the period.
*/
  function GridChecks(teamId, date, period) {
    $(function() {
      $( "#dialog-grid-checks" ).dialog({
        width: 800,
        buttons: {
          "Checked": function() {
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/allocations-gridchecked.php',
              data: {
                'teamId': teamId,
                'date': date,
                'action': 0,
                'period': period,
              },
              success: function (data,status) {
                ShowDailyAllocations (teamId,date);
              }
            });
            $( this ).dialog( "close" );
          },
          "Checked with problems": function() {
            $( this ).dialog( "close" );
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/allocations-gridcheck-comments.php',
              data: {
                'teamId': teamId,
                'date': date,
                'period': period,
              },
              success: function (data,status) {
                $.facebox(data);
              }
            });
          },
          "Cancel": function() {
            $( this ).dialog( "close" );
          }
        }
      });
    });
  }

  window.onscroll = function() {myFixedHeader()};
  var headerfirst = document.getElementById("headerfirst");
  // Get the offset position of the navbar
  var stickyone = headerfirst.offsetTop;
  var allocscrolltop = $("#allocscrolltop").val();
  // Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
  function myFixedHeader() {
    // Get the header
    if (window.pageYOffset > stickyone) {
      $('#menu,#banner').css({"z-index":"1"});
      $('#headerfirst').css({"position":"fixed","top":"0px","z-index":"9"});
      $('#headersecond').css({ "position":"fixed","top":"42px","z-index":"8" });
      $('#headerthird').css({ "position":"fixed","top":"75px","z-index":"8" ,"margin-left":"10px"});
      $('#headerfourth').css({ "position":"fixed","top":"105px","z-index":"8" });
      $('#top').css({ "position":"fixed","top":"75px","z-index":"8","margin-left":"0", "float": "right", "right": "25px"});
    } else {
      $('#menu,#banner').css({"z-index":"999"});
      $('#headerfirst').css({"position":"relative","top":"0px","z-index":"9"});
      $('#headersecond').css({ "position":"relative","top":"0px","z-index":"8"});
      $('#headerthird').css({ "position":"relative","top":"0px","z-index":"8","margin-left":"0px"});
      $('#headerfourth').css({ "position":"relative","top":"0px","z-index":"8"});
      $('#top').css({ "position":"absolute","top":"0px","z-index":"8","margin-left":"0px", "right":"34px"});
    }
  }

  $( window ).resize(function() {
    //ResizeDailyGrids();
  })
  function ResizeDailyGrids () {
    var windowwidth = $(window).width() - 285;
    $("#Allocatedduties").width(windowwidth);
    $("#unallocatedjobsgrid").width(windowwidth);
    $(".unallocdutygrid").width(windowwidth + 250);
    $("#unallocatedduties").width(windowwidth);
    $("#unallocatedjobs").width(windowwidth);
    $(".leaders").width(windowwidth);
    $("#top").width(windowwidth);
  }
  $('#Allocatedduties').on('scroll', function () {
    $('#unallocatedjobs').scrollLeft($(this).scrollLeft());
    $('#Allocatednames').scrollTop($(this).scrollTop());
    $('#unallocatedjobsgrid').scrollLeft($(this).scrollLeft());
    $('#unallocatedduties').scrollLeft($(this).scrollLeft());
    $(".leaders").scrollLeft($(this).scrollLeft());
    $('#top').scrollLeft($(this).scrollLeft());
  });

  $('#Allocatednames').on('scroll', function () {
    $('#Allocatedduties').scrollTop($(this).scrollTop());
  });

  $(function() {
    var showdutystart = $("#showdutystart").val();
    if ((showdutystart >0) && ($("#starttime").val()=="00:00")){
      var currdate = $("#strCurrentDate").val();
      var hourMark = '<?php echo $intHourwidth; ?>';
      var startTimeSplit = showdutystart;
      if ($.cookie("hscroll") == null) {
        $("#Allocatedduties").scrollLeft((hourMark * startTimeSplit));
      }
      if (showdutystart%3==0) {
        $.cookie("cookieStartTime", showdutystart.padStart(2, '0')+':00');//set starttime cookie
      } else {
        if($.cookie("cookieStartTime")=="") {
          $.cookie("cookieStartTime", '00:00');
        }
      }
    }

    $( "#datepicker" ).datepicker({
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
      defaultDate: "<?php echo $strCurrentDate ?>" ,
      onSelect: function (dateText, inst) {
        var currentdate = dateText;
        ShowDailyAllocations(<?php echo $intTeamID ?>,currentdate)
      },
      beforeShow:function(input) {
        $("#datepicker").css({
          "position": "relative",
          "z-index": 3
        });
      }
    });

    if($.cookie("cookieStartTime")) {
      $("#starttime").val($.cookie("cookieStartTime"));//set starttime selected value on next day
      var starttime = $("#starttime").val();
      var hourMark = '<?php echo $intHourwidth; ?>';
      var startTimeSplit = starttime.substr(0, starttime.indexOf(':'));
      if ($.cookie("hscroll") == null ) {
        $("#Allocatedduties").scrollLeft((hourMark * startTimeSplit));
        $("#unallocatedduties").scrollLeft((hourMark * startTimeSplit));
      }
    } else {
      $("#starttime").val("00:00");//set default starttime if cookie not set initially
    }

    $("#starttime").on('change', function() {
      var starttime = $("#starttime").val();
      var currdate = $("#strCurrentDate").val();
      var hourMark = '<?php echo $intHourwidth; ?>';
      var startTimeSplit = starttime.substr(0, starttime.indexOf(':'));
      $("#Allocatedduties").scrollLeft((hourMark * startTimeSplit));
      $("#unallocatedduties").scrollLeft((hourMark * startTimeSplit));
      $.cookie("cookieStartTime", starttime);//set starttime cookie
    });
  });

  $(document).on('click', '#js_SickSubmit',function (e) {
    e.stopImmediatePropagation();
    e.preventDefault();

    if($("#reasonlist").val() == 0){
      $('#reasonerror').show();
      return false;
    }
    var data = $("#sicknessrecordform").serialize();

    if($("#ischeck").prop('checked') == true){
      data+='&checkbox=1';
    }else{
      data+='&checkbox=0';
    }

    if($("#js_action").val()=='insert'){
      var sicknessflag=0;
      if(($("#js_prevDayReasonId").val() != $("#reasonlist").val()) && ($("#js_prevDayReasonId").val()!=0)){
        sicknessflag=1;
      }
      if(($("#js_nextDayReasonId").val() != $("#reasonlist").val()) && ($("#js_nextDayReasonId").val()!=0)){
        sicknessflag=1;
      }
      if ((sicknessflag==1)  && ($("#reasonlist").val()!=0)){
        $confirmboxmessage= 'Note that there are existing sickness records before and after this date <br/>';
        $confirmboxmessage +='that have different Sickness Reasons. <br/> Would you like to align them ALL together as one sickness period with <br/>';
        $confirmboxmessage+='the Sickness Reasons selected for this date ?';

        customConfirmModal($confirmboxmessage,function(){
          data +='&synctype=1';
          modDailySicknessRecord(data);
        },
          function() {
            if( $("#js_nextPrevDayReasonId").val() !=0){
              if(($("#reasonlist").val() != $("#js_nextPrevDayReasonId").val()) ){
                $confirmboxmessage= 'You have made changes to the following fields :<br/> -Reasons <br/><br/> Related sickness days will be updated with these changes';
              }
              if($("#ischeck").prop('checked') == true && ($("#comments").val() != $("#js_nextPrevDayComment").val())){
                $confirmboxmessage= 'You have made changes to the following fields :<br/> -Comment <br/><br/> Related sickness days will be updated with these changes';
              }
              if(($("#reasonlist").val() != $("#js_nextPrevDayReasonId").val()) &&$("#ischeck").prop('checked') == true && ($("#comments").val() != $("#js_nextPrevDayComment").val())  ){
                $confirmboxmessage= 'You have made changes to the following fields :<br/> -Reasons <br/>-Comment <br/><br/> Related sickness days will be updated with these changes';
              }
              if($confirmboxmessage!= ''){
                data +='&synctype=2';
                customConfirmModal($confirmboxmessage,function(){
                  modDailySicknessRecord(data);
                },
                function() {
                  $.facebox.close();
                  return false;
                }
                );
              }else{
                data +='&synctype=0';
                modDailySicknessRecord(data);
              }
            }
          }
        );
      }else{
        data +='&synctype=0';
        modDailySicknessRecord(data);
      }
    }else if( $("#js_nextPrevDayReasonId").val() !=0){
      $confirmboxmessage='';
      if(($("#reasonlist").val() != $("#js_nextPrevDayReasonId").val()) ){
        $confirmboxmessage= 'You have made changes to the following fields :<br/> -Reasons <br/><br/> Related sickness days will be updated with these changes';
      }
      if($("#ischeck").prop('checked') == true && ($("#comments").val() != $("#js_nextPrevDayComment").val())){
        $confirmboxmessage= 'You have made changes to the following fields :<br/> -Comment <br/><br/> Related sickness days will be updated with these changes';
      }
      if(($("#reasonlist").val() != $("#js_nextPrevDayReasonId").val()) &&$("#ischeck").prop('checked') == true && ($("#comments").val() != $("#js_nextPrevDayComment").val())  ){
        $confirmboxmessage= 'You have made changes to the following fields :<br/> -Reasons <br/>-Comment <br/><br/> Related sickness days will be updated with these changes';
      }
      if($confirmboxmessage!= ''){
        if(($("#js_prevDayReasonId").val() == $("#js_nextDayReasonId").val())){
          data +='&synctype=1';
        } else{
          data +='&synctype=2';
        }
        customConfirmModal($confirmboxmessage,function(){
          modDailySicknessRecord(data);
        },
        function() {
          $.facebox.close();
           return false;
        }
        );
      }else{
        data +='&synctype=0';
        modDailySicknessRecord(data);
      }
    }else{
      data +='&synctype=0';
      modDailySicknessRecord(data);
    }
  });

  function modDailySicknessRecord(data){
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php',
      data: data,
      success: function (data) {
        $('#loading').hide();
        data = $.parseJSON(data);
        var currentDate = $('#strCurrentDate').val();
        if (data.strstatus == 'success') {
           customAlert(data.strreturnstring, 1000);
        }else{
          customAlert(data.strreturnstring, 1000);
        }
        $.facebox.close();
        ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
      }
    });
  }

  function NewJob() {
    var WeekNumber =  $("#getweek").val();
    var getDay =  $("#getDay").val();
    var roleid =  $("#roleid").val();
    var intTeamID =  $("#strCurrentTeam").val();
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/edits/newjob.php',
      data: {
        'allocation': 0,
        'WeekNumber':WeekNumber,
        'iDay':getDay,
        'teamId': intTeamID,
        'role' :roleid,
        'unallocated_job' : 1,
        'dutyDate': $('#strCurrentDate').val()
      },
      success: function (data,status) {
        $.facebox(data);
      }
    });
  }

  function NewDuty(scheduledPersonId = 0, date = null, allocationsSpId = 0, allocationsDutyId = 0 , allocationId = 0) {
    var currdate = $("#strCurrentDate").val();
    if(date != null) {
      currdate = date;
    }
    var WeekNumber =  $("#getweek").val();
    var getDay =  $("#getDay").val();
    var roleid =  $("#roleid").val();
    var intTeamID =  $("#strCurrentTeam").val();
    var dutyParentId = <?php echo $parentDutyID;?>;
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/edits/newduty.php',
      data: {
        'teamId':intTeamID,
        'WeekNumber':WeekNumber,
        'iDay':getDay,
        'currdate':currdate,
        'role' :roleid,
        'dutyParentId' : dutyParentId,
        'scheduledPersonId' : scheduledPersonId,
        'allocationsSpId' : allocationsSpId,
        'allocationsDutyId' : allocationsDutyId,
        'id' : allocationId
      },
      success: function (data,status) {
        $.facebox(data);
      }
    });
  }
/**
 * Description :Mark Absent By Shift Leader
 * @param int typeid for absent typem id allocationId
 */
  function NewEditShiftLeader(typeid, id,roleIDPermission,filterQuery1,filterQuery2,filterQuery3,filterOrderStr,skillFilterDaily,dutyFilterDaily,jobFilterDaily,jobNameAll,jobLabelAll) {
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/edits/shiftleader-new-edit.php',
      data: {
        'id': id,
        'typeid': typeid,
        'roleIDPermission' : roleIDPermission,
        'filterQuery1' : filterQuery1.replaceAll('-', "'"),
        'filterQuery2' : filterQuery2.replaceAll('-', "'"),
        'filterQuery3' : filterQuery3,
        'filterOrderStr' : filterOrderStr,
        'selectedDay': <?php echo $selectedDay; ?>,
        'skillFilterDaily' : skillFilterDaily,
        'dutyFilterDaily' : dutyFilterDaily,
        'jobFilterDaily' : jobFilterDaily,
        'jobNameAll' : jobNameAll,
        'jobLabelAll' : jobLabelAll,
        'currentDate' : $('#strCurrentDate').val()
      },
      success: function (data,status) {
        $.facebox(data);
      }
    });
  }
	var isShiftLeader = "<?php echo isset($hasShiftleaderRole) ? $hasShiftleaderRole : 0; ?>";
	var isSchedulingTeamViewer = "<?php echo isset($isSchedulingTeamViewer) ? $isSchedulingTeamViewer : 0; ?>";
	var isScheduledPerson = "<?php echo isset($isScheduledPerson) ? $isScheduledPerson : 0; ?>";
  $(function(){
    $.contextMenu({
      selector: '.allocation-context-menu',
      callback: function(key, options) {
        id = options.$trigger.attr("id");
      },
      items: {
        "delete": {
          name: "Unassign",
          icon: "delete",
          visible: function(key, opt){
            if((opt.$trigger.attr('data-duty-name') != "U") && (opt.$trigger.attr("pdl-start-time") == 0) && (opt.$trigger.attr("pdl-end-time") == 0)){
              return true;
            }
          },
          // superseeds "global" callback
          callback: function(key, options) {
			  let shiftLeaderRole = Number(isShiftLeader) + Number(isSchedulingTeamViewer) + Number(isScheduledPerson);
			  if(shiftLeaderRole < 2)
			  {
				  if (options.$trigger.attr("data-charging-present") == 1 || options.$trigger.attr("data-charging-present") == "1") {
							customAlert("This duty cannot be deleted/swapped as it has one or more Charging records associated with it. Please remove the Charging records before trying to delete");
							return false;
				  }
			  }
            var dutyid =  options.$trigger.attr("id");
            var dutytype =  options.$trigger.attr("dutytype");
            var teamId =  options.$trigger.attr("data-teamid");
            var roleid =  $("#roleid").val();
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/unassign-alloc-duty.php',
              data: {
                'dutyid': dutyid,
                'teamId': teamId,
                'action': 0,
                'isShiftleader':roleid
              },
              success: function (data,status) {
                $.facebox(data);
              }
            });
                    }
        },
        "edit": {
          name: "Edit",
          icon: "edit",
          // superseeds "global" callback
          callback: function(key, options) {
            var id =  options.$trigger.attr("id");
            var parentid =  options.$trigger.attr("parentid");
            var scheduledPersonId =  options.$trigger.attr("scheduledPersonId");
            var miscduty =  options.$trigger.attr("misc");
            var currdate = options.$trigger.attr('data-dutydate');
            var pdlStartTime = options.$trigger.attr("pdl-start-time");
            var pdlEndTime = options.$trigger.attr("pdl-end-time");
            var allocationsSpId = options.$trigger.attr("data-allocation-spid");
            var allocationsDutyId = options.$trigger.attr("data-allocation-duty-id");
            var allocationId = options.$trigger.attr("parentid");
            if(options.$trigger.attr('data-duty-name') == "U") {
              NewDuty(scheduledPersonId, options.$trigger.attr("data-dutydate"), allocationsSpId, allocationsDutyId, allocationId);
              return true;
            }
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/editduty.php',
              data: {
                'dutyid': id,
                'WeekNumber': <?php echo $intWeek ?>,
                'iDay': <?php echo $intDay ?>,
                'teamId': options.$trigger.attr("data-teamid"),
                'role' : <?php echo $shiftleaderflag ?>,
                'parentid' : parentid,
                'scheduledPersonId':scheduledPersonId,
                'currdate':currdate,
                'miscduty': miscduty,
                'pdlStartTime': pdlStartTime,
                'pdlEndTime': pdlEndTime,
                'allocationsSpId': allocationsSpId
              },
              success: function (data,status) {
                $.facebox(data);
              }
            });
          }
        },
        "swap": {
          name: "Swap",
          icon: "swap",
          visible: function(key, opt){
            if((opt.$trigger.attr("data-overtime") == 0) && (opt.$trigger.attr("pdl-start-time") == 0) && (opt.$trigger.attr("pdl-end-time") == 0)){
            return true;
            }
          },
          // superseeds "global" callback
          callback: function(key, options) {
			  let shiftLeaderRole = Number(isShiftLeader) + Number(isSchedulingTeamViewer) + Number(isScheduledPerson);
			  if(shiftLeaderRole < 2)
			  {
				  if (options.$trigger.attr("data-charging-present") == 1 || options.$trigger.attr("data-charging-present") == "1") {
							customAlert("This duty cannot be deleted/swapped as it has one or more Charging records associated with it. Please remove the Charging records before trying to delete");
							return false;
				  }
			  }
            var allocationId =  options.$trigger.attr("id");
            var dutytype =  options.$trigger.attr("dutytype");
            var isEdited =  options.$trigger.attr("isedited");
            var parentId =  options.$trigger.attr("parentid");
            var scheduledPersonId = options.$trigger.attr("scheduledpersonid");
            var iday = options.$trigger.attr("data-iday");
            var weekNumber = options.$trigger.attr("data-weeknum");
            var teamId = options.$trigger.attr("data-teamid");
            var dutyName = options.$trigger.attr("data-duty-name");
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/swapduty.php',
              data: {
                'allocationId': allocationId,
                'dutytype': dutytype,
                'isEdited': isEdited,
                'parentId': parentId,
                'isShiftleader':<?php echo $shiftleaderflag ?? 0; ?>,
                'currentDate' : $('#strCurrentDate').val(),
                'scheduledPersonId' : scheduledPersonId,
                'iday': iday,
                'weekNumber' : weekNumber,
                'teamId': teamId,
                'dutyName': dutyName
              },
              success: function (data,status) {
                $.facebox(data);
              }
            });
          }
        },
        "new": {
          name: "New Job",
          icon: "add",
          visible: function(key, opt){
            if(opt.$trigger.attr('misc') == 0){
              return true;
            }
          },
          // superseeds "global" callback
          callback: function(key, options) {
            var id =  options.$trigger.attr("id");
            var scheduledpersonid =  options.$trigger.attr("scheduledpersonid");
            var dutyparentid =  options.$trigger.attr("parentid");
            var getweek = $('#getweek').val();
            var getday = $('#getDay').val();
            var dutyid =  options.$trigger.attr("id");
            var dutystart = $('#dutystart_'+dutyid).val();
            var duty_end = $('#duty_end_'+dutyid).val();
            var dutyduration = $('#dutyduration_'+dutyid).val();
            var roleid =  $("#roleid").val();
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/newjob.php',
              data: {
                'jobId': id,
                'allocation': dutyparentid,
                'WeekNumber': getweek,
                'iDay': getday,
                'teamId': options.$trigger.attr("data-teamid"),
                'scheduledpersonid' : scheduledpersonid,
                'dutyparentid' : dutyparentid,
                'dutyId': dutyid,
                'role' : roleid,
                'dutystart' : dutystart,
                'duty_end' : duty_end,
                'dutyduration' : dutyduration,
                 'dutyDate': $('#strCurrentDate').val()
              },
              success: function (data,status) {
                $.facebox(data);
              }
            });
          }
        },
        "sep1": "---------",
        "comments": {
          name: "Comments",
          icon: "comment",
          // superseeds "global" callback
          callback: function(key, options) {
            var dutyId =  options.$trigger.attr("id");
            var isEdited =  options.$trigger.attr("isedited");
            var parentId =  options.$trigger.attr("parentid");
            var dutyType =  options.$trigger.attr("dutytype");
            var isShiftLeader = '<?php echo $hasShiftleaderRole; ?>';
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/dutycomments.php',
              data: {
                'dutyId': dutyId,
                'isEdited': isEdited,
                'parentId': parentId,
                'teamId': options.$trigger.attr("data-teamid"),
                'dutyType': dutyType,
                'isShiftLeader':isShiftLeader,
                'dutyDate': options.$trigger.attr("data-dutydate"),
                'schedulingPersonId' : options.$trigger.attr("scheduledpersonid"),
                'dutyName' : options.$trigger.attr("data-duty-name")
              },
              success: function (data,status) {
                $.facebox(data);
              }
            });
          }
        },

        "exclaim": {
          name: "Mark for Attention",
          icon: "exclaim",
          visible: function(key, opt){
            if((opt.$trigger.attr('data-attention') == 0) && ($("#roleid").val()==2)){
              return true;
            }
          },
          callback: function(key, options) {
            var id =  options.$trigger.attr("id");
            var isAttention =  options.$trigger.attr("data-attention");
            var isEdited =  options.$trigger.attr("isedited");
            var parentId =  options.$trigger.attr("parentid");
            var teamId = options.$trigger.attr("data-teamid");
            var scheduledPersonId =  options.$trigger.attr('scheduledpersonid');
            var date = options.$trigger.attr('data-dutydate');
            var currentDate = $('#strCurrentDate').val();
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/mark-unmark-attention-duty.php',
              data: {
                'dutyid': id,
                'isAttention': isAttention,
                'isEdited': isEdited,
                'parentId': parentId,
                'scheduledPersonId': scheduledPersonId,
                'date' : date
              },
              success: function (data,status) {
                ShowDailyAllocations(teamId,currentDate);
              }
            });
          }
        },
        "unmark": {
          name: "Unmark for Attention",
          icon: "unmark",
          visible: function(key, opt){
            if((opt.$trigger.attr('data-attention') > 0) && ($("#roleid").val()==2)){
              return true;
            }
          },
          callback: function(key, options) {
            var id = options.$trigger.attr("id");
            var isAttention = options.$trigger.attr("data-attention");
            var isEdited = options.$trigger.attr("isedited");
            var parentId = options.$trigger.attr("parentid");
            var teamId= options.$trigger.attr("data-teamid");
            var scheduledPersonId =  options.$trigger.attr('scheduledpersonid');
            var date = options.$trigger.attr('data-dutydate');
            var currentDate = $('#strCurrentDate').val();
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/mark-unmark-attention-duty.php',
              data: {
                'dutyid': id,
                'isAttention': isAttention,
                'isEdited': isEdited,
                'parentId': parentId,
                'scheduledPersonId': scheduledPersonId,
                'date' : date
              },
              success: function (data,status) {
                ShowDailyAllocations(teamId,currentDate);
              }
            });
          }
        },
        "markovertime": {
          name: "Mark Overtime",
          icon: "markovertime",
          selected: false,
          callback: function (key, options) {
            var data = {
              "schedulingPersonId" : options.$trigger.attr("scheduledpersonid"),
              "dutyDate" : options.$trigger.attr("data-dutydate"),
              "teamId" :  options.$trigger.attr("data-teamid"),
              "dateonly": options.$trigger.attr("data-dutydate"),
              "iday" : <?php echo $intDay; ?>,
              "rolepermission" : <?php echo $rolepermission; ?>,
              "allocationId" :  options.$trigger.attr("parentid"),
              "allocationsDutyId": options.$trigger.attr("id"),
              "allocationsSpId": options.$trigger.attr("data-allocation-spid")
            }
            MarkOvertimeOpenPopup(data);
          },
          visible: function(key, options) {
            if(($(".cellID_"+options.$trigger.attr("data-dutydate")+"_"+options.$trigger.attr("scheduledpersonid")).hasClass('green') == false) && (options.$trigger.attr("data-edp") == "M") && (options.$trigger.attr('data-duty-name') != "U")  && ($("#roleid").val()==2)) {
              return true;
            }
          }
        },
        "editmarkovertime": {
          name: "Edit Mark Overtime",
          icon: "markovertime",
          selected: false,
          callback: function (key, options) {
            var data = {
              "schedulingPersonId" : options.$trigger.attr("scheduledpersonid"),
              "dutyDate" : options.$trigger.attr("data-dutydate"),
              "teamId" :  options.$trigger.attr("data-teamid"),
              "dateonly": options.$trigger.attr("data-dutydate"),
              "iday" : <?php echo $intDay; ?>,
              "rolepermission" : <?php echo $rolepermission; ?>,
              "allocationId" :  options.$trigger.attr("parentid"),
              "allocationsDutyId": options.$trigger.attr("id"),
              "allocationsSpId": options.$trigger.attr("data-allocation-spid")
            }
            MarkOvertimeOpenPopup(data);
          },
          visible: function(key, options) {
            if(($(".cellID_"+options.$trigger.attr("data-dutydate")+"_"+options.$trigger.attr("scheduledpersonid")).hasClass('green')) && (options.$trigger.attr("data-edp") == "M") && (options.$trigger.attr('data-duty-name') != "U") && ($("#roleid").val()==2)) {
              return true;
            }
          }
        },

        "sick": {
          name: "Mark as Sick",
          icon: "sick",
          visible: function(key, opt) {
            if((opt.$trigger.attr('data-IshomeTeam') == "1") && (opt.$trigger.attr("pdl-start-time") == 0) && (opt.$trigger.attr("pdl-end-time") == 0) && ($("#roleid").val()==2)) {
              return true;
            }
          },
          // superseeds "global" callback
          callback: function(key, options) {
			  let shiftLeaderRole = Number(isShiftLeader) + Number(isSchedulingTeamViewer) + Number(isScheduledPerson);
			  if(shiftLeaderRole < 2)
			  {
				  if (options.$trigger.attr("data-charging-present") == 1 || options.$trigger.attr("data-charging-present") == "1") {
							customAlert("This duty cannot be deleted/swapped as it has one or more Charging records associated with it. Please remove the Charging records before trying to delete");
							return false;
				  }
			  }
            var dutyid =  options.$trigger.attr("id");
            var dutytype =  options.$trigger.attr("dutytype");
            var roleid =  $("#roleid").val();
            var reqdata = {
              "schedulingPersonId" :options.$trigger.attr("scheduledpersonid") ,
              "teamId" : options.$trigger.attr("data-teamid") ,
              "dateonly": options.$trigger.attr('data-dutydate'),
              "action" : 'showsicknesspopup',
              "allocid": options.$trigger.attr("parentid"),
              'allocationsDutyId': options.$trigger.attr("id"),
              "screen":"EditDaily",
              "isShiftleader":roleid,
              'dutyDate': options.$trigger.attr('data-dutydate'),
              'allocationsSpId': options.$trigger.attr('data-allocation-spid'),
              'isChargingPresent': options.$trigger.attr('data-charging-present')
            }
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php',
              data: {dataval: reqdata},
              success: function (data,status) {
                $.facebox(data);
              }
            });
          }
        },

        "other": {
          name: "Mark as Absent",
          icon: "exclaim",
		   visible: function(key, opt){
            if(($("#roleid").val()==1)){
              return true;
            }
          },
          // superseeds "global" callback
          callback: function(key, options) {
			 let shiftLeaderRole = Number(isShiftLeader) + Number(isSchedulingTeamViewer) + Number(isScheduledPerson);
			if(shiftLeaderRole < 2)
			{
				if (options.$trigger.attr("data-charging-present") == 1 || options.$trigger.attr("data-charging-present") == "1") {
						customAlert("This duty cannot be deleted/swapped as it has one or more Charging records associated with it. Please remove the Charging records before trying to delete");
						return false;
				}
			}
            var allocationDutyId = options.$trigger.attr("id");
            var allocationId = options.$trigger.attr("parentid");
            var isEdited =  options.$trigger.attr("isedited");
            var teamId= options.$trigger.attr("data-teamid");
            var roleid =  $("#roleid").val();
            var currentDate = $('#strCurrentDate').val();

            var scheduledpersonid = options.$trigger.attr("scheduledpersonid");
            var allocationSPid = options.$trigger.attr("data-allocation-spid");
            var dutydate = options.$trigger.attr("data-dutydate");
            var weeknum = options.$trigger.attr("data-weeknum");
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/markabsent.php',
              data: {
                'allocationDutyId' : allocationDutyId,
                'allocationId': allocationId,
                'action': '2',
                'teamId': teamId,
                'isEdited':isEdited,
                'isShiftleader' : '<?php echo $intMarkabsent?>',
                'scheduledpersonid':scheduledpersonid,
                'allocationSPid':allocationSPid,
                'dutydate':dutydate,
                'weeknum':weeknum
              },
              success: function (data,status) {
                data = JSON.parse(data);
                if(data.strstatus != null && data.strstatus != 1) {
                  customAlert(data.strsmsg);
                }
                ShowDailyAllocations(teamId,currentDate);
              }
            });
          }
        },
    	"charging": {
			name: "Charging",
			icon: "charge",
			selected: false,
			visible: function(key, opt) {
				var dutyName = opt.$trigger.attr("data-duty-name");
				if (($("#roleid").val() == 2) && (dutyName != 'U'))
				{
						return true;
				}else
				{
					return false;
				}

			},
			callback: function(key, options) {
				var schedulingPersonId = options.$trigger.attr("scheduledpersonid");
				var allocDutyId = options.$trigger.attr("id");
        var allocid = options.$trigger.attr("parentid");
				var teamId = options.$trigger.attr("data-teamid");
				ChargingOpenPopup(schedulingPersonId, allocid, teamId, allocDutyId);
			}
		},
        "sep3": "---------",
        "history": {
          name: "History",
          icon: "history",
          // superseeds "global" callback
          callback: function(key, options) {
            var dutyid =  options.$trigger.attr("id");
            var dutytype =  options.$trigger.attr("dutytype");
            var isEdited =  options.$trigger.attr("isedited");
            var parentid =  options.$trigger.attr("parentid");
            var scheduledPersonId = options.$trigger.attr("scheduledpersonid");
            if (dutytype == 0) {
              $(function() {
                $( "#dialog-no-history" ).dialog({
                  buttons: {
                    "OK": function() {
                      $( this ).dialog( "close" );
                    }
                  }
                });
              });
            } else {
              $.ajax({
                type: 'POST',
                url: 'page-includes/allocations/edits/dutyhistory.php',
                data: {
                  'id': dutyid,
                  'scheduledPersonId': scheduledPersonId,
                  'allocationId' : parentid
                },
                success: function (data,status) {
                  $.facebox(data);
                }
              });
            }
          }
        },
      },
    })
  }),

  $.contextMenu({
    selector: '.allocation-locked-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("id");
          var scheduledPersonId = options.$trigger.attr("scheduledpersonid");
          var parentid =  options.$trigger.attr("parentid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/dutyhistory.php',
            data: {
              'id': dutyid,
              'scheduledPersonId': scheduledPersonId,
              'allocationId' : parentid
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    },
  }),

    $.contextMenu({
    selector: '.allocation-leave-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "comments": {
        name: "Comments",
        icon: "comment",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyId =  options.$trigger.attr("id");
          var isEdited =  options.$trigger.attr("isedited");
          var parentId =  options.$trigger.attr("parentid");
          var dutyType =  options.$trigger.attr("dutytype");
          var teamId= options.$trigger.attr("data-teamid");
          var isShiftLeader = '<?php echo $hasShiftleaderRole; ?>';
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/dutycomments.php',
            data: {
              'dutyId': dutyId,
              'isEdited': isEdited,
              'parentId': parentId,
              'teamId': teamId,
              'dutyType': dutyType,
              'isShiftLeader':isShiftLeader,
              'dutyDate': options.$trigger.attr("data-dutydate"),
              'schedulingPersonId' : options.$trigger.attr("scheduledpersonid"),
              'dutyName' : options.$trigger.attr("data-duty-name"),
              'currentDate': $('#strCurrentDate').val()
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
          }
      },
      "exclaim": {
        name: "Mark for Attention",
        icon: "exclaim",
        visible: function(key, opt){
          if(opt.$trigger.attr('data-attention') == 0){
            return true;
          }
        },
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var isAttention =  options.$trigger.attr("data-attention");
          var isEdited =  options.$trigger.attr("isedited");
          var parentId =  options.$trigger.attr("parentid");
          var teamId= options.$trigger.attr("data-teamid");
          var scheduledPersonId =  options.$trigger.attr('scheduledpersonid');
          var date = options.$trigger.attr('data-dutydate');
          var currentDate = $('#strCurrentDate').val();
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/mark-unmark-attention-duty.php',
            data: {
              'dutyid': id,
              'isAttention': isAttention,
              'isEdited': isEdited,
              'parentId': parentId,
              'scheduledPersonId': scheduledPersonId,
              'date': date
            },
            success: function (data,status) {
              ShowDailyAllocations(teamId,currentDate);
            }
          });
        }
      },
      "unmark": {
        name: "Unmark for Attention",
        icon: "unmark",
        visible: function(key, opt){
          if(opt.$trigger.attr('data-attention') > 0){
            return true;
          }
        },
        callback: function(key, options) {
          var id = options.$trigger.attr("id");
          var isAttention = options.$trigger.attr("data-attention");
          var isEdited = options.$trigger.attr("isedited");
          var parentId = options.$trigger.attr("parentid");
          var teamId= options.$trigger.attr("data-teamid");
          var scheduledPersonId =  options.$trigger.attr('scheduledpersonid');
          var date = options.$trigger.attr('data-dutydate');
          var currentDate = $('#strCurrentDate').val();
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/mark-unmark-attention-duty.php',
            data: {
              'dutyid': id,
              'isAttention': isAttention,
              'isEdited': isEdited,
              'parentId': parentId,
              'scheduledPersonId': scheduledPersonId,
              'date' : date
            },
            success: function (data,status) {
              ShowDailyAllocations(teamId,currentDate);
            }
          });
        }
      },
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("id");
          var scheduledPersonId = options.$trigger.attr("scheduledpersonid");
          var parentid =  options.$trigger.attr("parentid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/dutyhistory.php',
            data: {
              'id': dutyid,
              'scheduledPersonId': scheduledPersonId,
              'allocationId' : parentid
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    },
  }),

  $.contextMenu({
    selector: '.allocation-absentrestore-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "restore": {
        name: "Restore",
        icon: "restore",
        visible: function(key, opt){
          if(opt.$trigger.attr('data-duty-name').toLowerCase() == "absent"){
            return true;
          }
        },
        // superseeds "global" callback
        callback: function(key, options) {
          var allocationId =  options.$trigger.attr("id");
          var isEdited =  options.$trigger.attr("isedited");
          var teamId= options.$trigger.attr("data-teamid");
          var roleid =  $("#roleid").val();
          var allocationSPId =  options.$trigger.attr("data-allocation-spid");
          $(function() {
            currentDate = $('#strCurrentDate').val();
            $("#dialog-confirm").dialog({
              title: "Please Confirm",
              resizable: false,
              height:200,
              width:500,
              modal: true,
              buttons: {
                "Restore": function() {
                  $.ajax({
                    type: 'POST',
                    url: 'page-includes/allocations/edits/restoreduty.php',
                    data: {
                      'allocationId': allocationId,
                      'isEdited':isEdited,
                      'teamId':teamId,
                      'isShiftleader':roleid,
                      'dutyName' :options.$trigger.attr("data-duty-name"),
                      'allocationSPId' : allocationSPId
                    },
                    success: function (data,status) {
                      data = JSON.parse(data);
                      if(data.strstatus != null && data.strstatus != 1) {
                        $.facebox(data.strsmsg);
                      }
                      ShowDailyAllocations (teamId,currentDate);
                    }
                  });
                  $( this ).dialog( "close" );
                },
                Cancel: function() {
                  $( this ).dialog( "close" );
                }
              }
            });
          });
        }
      },
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("id");
          var scheduledPersonId = options.$trigger.attr("scheduledpersonid");
          var parentid =  options.$trigger.attr("parentid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/dutyhistory.php',
            data: {
              'id': dutyid,
              'scheduledPersonId': scheduledPersonId,
              'allocationId' : parentid
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    },
  }),

  $.contextMenu({
    selector: '.allocation-absent-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "deletesickness": {
        name: "Delete Sickness",
        icon: "delete",
        visible: function(key, opt){
          if((opt.$trigger.attr('data-duty-name').toLowerCase() == "u-sick" || opt.$trigger.attr('data-duty-name').toLowerCase() == "sick") && (opt.$trigger.attr('data-IshomeTeam') == "1")) {
            return true;
          }
        },
        // superseeds "global" callback
        callback: function(key, options) {
          var allocationId =  options.$trigger.attr("id");
          var allocationSPId =  options.$trigger.attr("data-allocation-spid");
          var isEdited =  options.$trigger.attr("isedited");
          var teamId= options.$trigger.attr("data-teamid");
          var roleid =  $("#roleid").val();
          currentDate = $('#strCurrentDate').val();
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/restoreduty.php',
            data: {
              'allocationId': allocationId,
              'isEdited':isEdited,
              'teamId':teamId,
              'isShiftleader':roleid,
              'dutyName' :options.$trigger.attr("data-duty-name"),
              'allocationSPId': allocationSPId
            },
            success: function (data,status) {
              data = JSON.parse(data);
              if(data.strstatus != null && data.strstatus != 1) {
                $.facebox(data.strsmsg);
              }
              ShowDailyAllocations (teamId,currentDate);
            }
          });
        }
      },
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("id");
          var scheduledPersonId = options.$trigger.attr("scheduledpersonid");
          var parentid =  options.$trigger.attr("parentid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/dutyhistory.php',
            data: {
              'id': dutyid,
              'scheduledPersonId': scheduledPersonId,
              'allocationId' : parentid
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    },
  }),

  $.contextMenu({
    selector: '.allocation-context-menu-noteditable',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "comments": {
        name: "Comments",
        icon: "comment",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var dutytype =  options.$trigger.attr("dutytype");
          var isShiftLeader = '<?php echo $hasShiftleaderRole; ?>';
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/dutycomments.php',
            data: {
              'id': id,
              'teamId': <?php echo $intTeamID; ?>,
              'dutytype': dutytype,
              'isShiftLeader':isShiftLeader,
              'dutyDate': options.$trigger.attr("data-dutydate"),
              'schedulingPersonId' : options.$trigger.attr("scheduledpersonid"),
              'dutyName' : options.$trigger.attr("data-duty-name")
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("id");
          var scheduledPersonId = options.$trigger.attr("scheduledpersonid");
          var parentid =  options.$trigger.attr("parentid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/dutyhistory.php',
            data: {
              'id': dutyid,
              'scheduledPersonId': scheduledPersonId,
              'allocationId' : parentid
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    },
  }),

  $.contextMenu({
    // TYhe context menu for jobs assigned to a duty
    selector: '.job-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "edit": {
        name: "Edit Job",
        icon: "edit",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var dutyid =  options.$trigger.attr("dutyid");
          var WeekNumber =  $("#getweek").val();
          var getDay =  $("#getDay").val();
          var roleid =  $("#roleid").val();
          var intTeamID =  $("#strCurrentTeam").val();
          var dutystart = $('#dutystart_'+dutyid).val();
          var duty_end = $('#duty_end_'+dutyid).val();
          var dutyduration = $('#dutyduration_'+dutyid).val();
          var midnightJob = $('#jobmidnight_' + id).val();
          var scheduledPersonId = options.$trigger.attr('data-scheduled-person-id');
          var dutyParentid = options.$trigger.attr('data-duty-parentid');
          var allocationSpid = options.$trigger.attr('data-allocation-spid');
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/editjob.php',
            data: {
              'jobId': id,
              'role' : roleid,
              'teamId': intTeamID,
              'WeekNumber':WeekNumber,
              'iDay':getDay,
              'dutystart' : dutystart,
              'duty_end' : duty_end,
              'dutyduration' :dutyduration,
              'dutyid': dutyid,
              'scheduledPersonId': scheduledPersonId,
              'midnightJob': midnightJob,
              'dutyParentid': dutyParentid,
              'allocationSpid': allocationSpid
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
          }
      },
      "split": {
        name: "Split Job",
        icon: "split",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var isEdited =  options.$trigger.attr("isedited");
          var parentId =  options.$trigger.attr("parentid");
          var dutyid =  options.$trigger.attr("dutyid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/splitjob.php',
            data: {
              'id': id,
              'isEdited' : isEdited,
              'parentId' : parentId,
              'dutyid' : dutyid
            },
            success: function (data,status) {
              StopTimer();
              $.facebox(data);
            }
          });
                }
      },
      "copy": {
        name: "Copy Job",
        icon: "copy",
        callback: function(key, options) {
          var jobid =  options.$trigger.attr("id");
          var teamId= options.$trigger.attr("data-teamid");
          var currentDate = $('#strCurrentDate').val();
          $(function() {
            $("#dialog-confirm-copy-job").dialog({
              title: "Please Confirm",
              resizable: false,
              height:200,
              width:400,
              modal: true,
              buttons: {
                "Copy": function() {
                  $.ajax({
                    type: 'POST',
                    url: 'page-includes/allocations/edits/copyjob.php',
                    data: {
                      'jobid': jobid,
                    },
                    success: function (data,status) {
                      ShowDailyAllocations (teamId,currentDate);
                    }
                  });
                  $( this ).dialog( "close" );
                      },
                Cancel: function() {
                  $( this ).dialog( "close" );
                }
              }
            });
          });
        }
      },
      "copyother": {
        name: "Copy Job To Date(s)",
        icon: "copy",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var dutyid =  options.$trigger.attr("dutyid");
          var dutystart = $('#dutystart_'+dutyid).val();
          var duty_end = $('#duty_end_'+dutyid).val();
          var teamId =  options.$trigger.attr("data-teamid");
          var WeekNumber =  options.$trigger.attr("data-weeknum");
          var iDay =  options.$trigger.attr("data-iday");
          var roleid =  $("#roleid").val();
          var scheduledPersonId = options.$trigger.attr('data-scheduled-person-id');
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/copyjobto.php',
            data: {
              'jobId': id,
              'role' : roleid,
              'teamId': teamId,
              'WeekNumber':WeekNumber,
              'iDay': iDay,
              'dutystart' : dutystart,
              'duty_end': duty_end,
              'scheduledPersonId': scheduledPersonId,
              'date': $('#strCurrentDate').val()
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
      "move": {
        name: "Move Job To Date",
        icon: "move",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var dutyid =  options.$trigger.attr("dutyid");
          var dutystart = $('#dutystart_'+dutyid).val();
          var duty_end = $('#duty_end_'+dutyid).val();
          var teamId =  options.$trigger.attr("data-teamid");
          var roleid =  $("#roleid").val();
          var WeekNumber =  options.$trigger.attr("data-weeknum");
          var iDay =  options.$trigger.attr("data-iday");
          var currentDate = $('#strCurrentDate').val();
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/shiftjob.php',
            data: {
              'jobId': id,
              'role' : roleid,
              'teamId': teamId,
              'WeekNumber':WeekNumber,
              'iDay':iDay,
              'dutystart' : dutystart,
              'duty_end' : duty_end,
              'currentDate': currentDate
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
      "unasign": {
        name: "Unassign Job",
        icon: "unassign",
        // superseeds "global" callback
        callback: function(key, options) {
          $.contextMenu( 'destroy' );
          var jobid =  options.$trigger.attr("id");
          var allocationId =  options.$trigger.attr("dutyid");
          var teamId =  options.$trigger.attr("data-teamid");
          var roleid =  $("#roleid").val();
          var currentDate = $('#strCurrentDate').val();
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/unassignjob.php',
            data: {
              'jobid': jobid,
              'role' : roleid,
              'AllocationId' : allocationId,
              'date': currentDate,
              'teamId': teamId
            },
            success: function (data,status) {
              ShowDailyAllocations (teamId,currentDate);
            }
          });
        }
      },
      "sep1": "---------",
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/jobhistory.php',
            data: {
              'id': id
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    }
  }),

  $.contextMenu({
    // TYhe context menu for jobs assigned to a duty
    selector: '.job-locked-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/jobhistory.php',
            data: {
              'id': id
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    }
  }),

  $.contextMenu({
    // TYhe context menu for jobs NOT assigned to a duty
    selector: '.job-context-menu-unassigned',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "edit": {
        name: "Edit Job",
        icon: "edit",
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var dutystart = $('#dutystart_'+id).val();
          var duty_end = $('#duty_end_'+id).val();
          var WeekNumber =  $("#getweek").val();
          var getDay =  $("#getDay").val();
          var roleid =  $("#roleid").val();
          var rolepermission =  $("#rolepermission").val();
          var intTeamID =  $("#strCurrentTeam").val();
          var midnightJob = $('#jobmidnight_' + id).val();
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/editjob.php',
            data: {
              'jobId': id,
              'role' : rolepermission,
              'teamId': intTeamID,
              'WeekNumber':WeekNumber,
              'iDay':getDay,
              'dutystart' : dutystart,
              'duty_end' : duty_end,
              'unallocated_job' : 1,
              'midnightJob': midnightJob
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
                }
      },
      "split": {
        name: "Split Job",
        icon: "split",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var isEdited =  options.$trigger.attr("isedited");
          var parentId =  options.$trigger.attr("parentid");
          var dutyid =  options.$trigger.attr("dutyid");
          if(dutyid=='undefined') {dutyid=0;}
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/splitjob.php',
            data: {
              'id': id,
              'isEdited' : isEdited,
              'parentId' : parentId,
              'dutyid' : dutyid
            },
            success: function (data,status) {
              StopTimer();
              $.facebox(data);
            }
          });
          }
      },
      "delete": {
        name: "Delete Job",
        icon: "delete",
        callback: function(key, options) {
          var jobid =  options.$trigger.attr("id");
          var isEdited =  options.$trigger.attr("isedited");
          var currentDate = $('#strCurrentDate').val();
          var teamId =  options.$trigger.attr("data-teamid");
          $("#dialog-confirm-delete-job").dialog({
            title: "Please Confirm",
            resizable: false,
            height:'auto',
            width:480,
            modal: true,
            buttons: {
              "Delete": function() {
                $.ajax({
                  type: 'POST',
                  url: 'page-includes/allocations/edits/deletejob.php',
                  data: {
                    'jobid': jobid,
                    'isEdited': isEdited
                  },
                  success: function (data,status) {
                    data = JSON.parse(data);
                    ShowDailyAllocations (teamId,currentDate);
                  }
                });
                      $( this ).dialog( "close" );
              },
              Cancel: function() {
                $( this ).dialog( "close" );
              }
            }
          });
        }
      },
      "sep1": "---------",
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/jobhistory.php',
            data: {
              'id': id
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
          }
      },
    }
  }),

  // The context menu for duties which are unassigned
  $.contextMenu({
    selector: '.edited-allocation-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "assign": {
        name: "Assign to person",
        icon: "assign",
        // superseeds "global" callback
        callback: function(key, options) {
          var allocationId =  options.$trigger.attr("id");
          var dutytype =  options.$trigger.attr("dutytype");
          var isEdited = options.$trigger.attr("isedited");
          var parentId = options.$trigger.attr("parentid");
          var teamId =  options.$trigger.attr("data-teamid");
          var iday =  parseInt(options.$trigger.attr("data-iday"));
          var weeknum =  options.$trigger.attr("data-weeknum");
          var currentDate = $('#strCurrentDate').val();
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/assignduty.php',
            data: {
              'allocationId': allocationId,
              'dutytype': dutytype,
              'isEdited': isEdited,
              'parentId': parentId,
              'teamId': teamId,
              'WeekNumber':weeknum,
              'iDay':iday,
              'isShiftleader':<?php echo $shiftleaderflag ?? 0; ?>,
              'currentDate' : currentDate
            },
            success: function (data) {
              $.facebox(data);
            }
          });
        }
      },
      <?php if ($intCanMakeEdited == 1) {?>
      "edit": {
          name: "Edit",
          icon: "edit",
          // superseeds "global" callback
          callback: function(key, options) {
              editUnallocatedDuty(options.$trigger);
          }
      },
      "deleteduty": {
        name: "Delete Duty",
        icon: "deleteduty",
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          var weeknum =  options.$trigger.attr("data-weeknum");
          var dutyName =  options.$trigger.attr("data-duty-name");
          var dayName = $("#getDayName").val();
          var teamId =  options.$trigger.attr("data-teamid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/delete-duty.php',
            data: {
              'dutyid': id,
              'teamId': teamId,
              'userName' :"<?php echo $strUser; ?>",
              'weeknum' : weeknum,
              'dutyName': dutyName,
              'dayName': dayName
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    <?php }?>
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("id");
          var scheduledPersonId = options.$trigger.attr("scheduledpersonid");
          var parentid =  options.$trigger.attr("parentid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/dutyhistory.php',
            data: {
              'id': dutyid,
              'scheduledPersonId': scheduledPersonId,
              'allocationId' : parentid
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    }
  }),

  $.contextMenu({
    selector: '.sl-context-menu',
    callback: function(key, options) {
    },
    items: {
      "new": {
        name: "New",
        icon: "new",
        // superseeds "global" callback
        callback: function(key, options) {
          typeid = options.$trigger.attr("typeid");
          filterQuery1= "<?php echo $filterQuery1; ?>";
          filterQuery2= "<?php echo $filterQuery2; ?>";
          filterQuery3= "<?php echo $filterQuery3; ?>";
          filterOrderStr= "<?php echo $filterOrderStr; ?>";
          role="<?php echo $intIsShiftLeader; ?>";
          NewEditShiftLeader(typeid, 0,role,filterQuery1,filterQuery2,filterQuery3,filterOrderStr);
        }
      },
      "manage": {
        name: "Manage",
        icon: "user",
        // superseeds "global" callback
        callback: function(key, options) {
          typeid = options.$trigger.attr("typeid");
          teamid = options.$trigger.attr("schedulingteamid");
          $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/shiftleaders-manage.php',
            data: {
              'typeid': typeid,
              'teamId': teamid,
              'currentDate' : $('#strCurrentDate').val()
            },
            success: function (data,status) {
              $.facebox(data);
            }
          });
        }
      },
    },
  })

  function editUnallocatedDuty($obj) {
    var id =  $obj.attr("id");
    var parentid =  $obj.attr("parentid");
    var miscduty =  $obj.attr("misc");
    var currdate = $obj.attr('data-dutydate');
    var pdlStartTime = $obj.attr("pdl-start-time");
    var pdlEndTime = $obj.attr("pdl-end-time");
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/edits/editduty.php',
      data: {
        'dutyid': id,
        'WeekNumber': <?php echo $intWeek ?>,
        'iDay': <?php echo $intDay ?>,
        'teamId': $obj.attr("data-teamid"),
        'role' : <?php echo $shiftleaderflag ?>,
        'parentid' : parentid,
        'currdate':currdate,
        'miscduty': miscduty,
        'pdlStartTime': pdlStartTime,
        'pdlEndTime': pdlEndTime
      },
      success: function (data,status) {
        $.facebox(data);
      }
    });
  }

  /** Mark overtime open the popup */
  function MarkOvertimeOpenPopup(data){
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/weekly/modals/markOverTimeUI.php',
      data: data,
      success: function (response) {
        $.facebox(response);
      }
    });
  }

  $(document).on('click','#js_saveOvertTime', function () {
    SaveMarkOvertime();
  });

  /* save/update  divisions*/
  function SaveMarkOvertime() {
    var hrsArr = [0, 15, 30, 45];
    var currentDate = $('#strCurrentDate').val();
    $.validator.addMethod('compare', function (value, element, param) {
      return this.optional(element) || parseInt(value) <= parseInt($("#availhrs").val());
    }, '<br/>You cannot enter Mannual OT Hours (exec breaks) value higher than the Available Hours of this duty ('+$("#availhrs").val()+')');

    $.validator.addMethod('minStrict', function (value, el, param) {
      return value >= param;
    },"</br/>Maximum Hrs Should Be Equal To Or Greater Than 0");
    $.validator.addMethod('validHrs', function(value, el, param) {
      return $.inArray(((value * 3600)%3600)/60, hrsArr) != -1 ? true : false;
    }, "</br/>Only '0.25', '0.50' and '0.75' fractions are allowed for Overtime entries");
    $('#markovertimeform').validate({
      debug: false,
      errorPlacement: function(error, element) {
        error.appendTo('#overTimeError');
      },
      rules: {
        "mannualothrs": {
          required: true,
          compare:true,
          validHrs: true
        }
      },
      messages: {
        "markovertimeot": {
          required : jQuery.validator.format("<br/> This Field is required."),
          minlength: jQuery.validator.format("<br/> At least {0} characters are necessary."),
        }
      },
      submitHandler: function (form) {
        $.ajax({
          type: 'POST',
          url: '/page-includes/allocations/weekly/actions/mark-action.php',
          dataType: "json",
          data: $('#markovertimeform').serialize(),
          success: function (response) {
            if(response.success === true){
              if(form.elements["mannualothrs"].value > 0 && $(".cellID_"+form.elements["dateonly"].value+"_"+form.elements["schedulingPersonId"].value).hasClass('attentionClass')== true ){
                $(".cellID_"+form.elements["dateonly"].value+"_"+form.elements["schedulingPersonId"].value).addClass('stripped-bar');
              }
              if(form.elements["mannualothrs"].value > 0) {
                $(".cellID_"+form.elements["dateonly"].value+"_"+form.elements["schedulingPersonId"].value).addClass('green');
              } else {
                if($(".cellID_"+form.elements["dateonly"].value+"_"+form.elements["schedulingPersonId"].value).hasClass('stripped-bar') == true){
                  $(".cellID_"+form.elements["dateonly"].value+"_"+form.elements["schedulingPersonId"].value).removeClass('stripped-bar');
                  $(".cellID_"+form.elements["dateonly"].value+"_"+form.elements["schedulingPersonId"].value).addClass('attentionClass');
                }
                $(".cellID_"+form.elements["dateonly"].value+"_"+form.elements["schedulingPersonId"].value).removeClass('green');
              }
              $('#facebox .close').click();
              ShowDailyAllocations(<?php echo $intTeamID ?>,currentDate);
            } else {
              customAlert(data.strreturnstring);
              $('#facebox .close').click()
            }
          }
        });
      }
    })
  }

  $(function() {
    $(".draggable" ).draggable({
      appendTo: '#drag_helper',
      revert: "invalid",
      containment: "document",
      helper: "clone",
      zIndex: 100,
    });
    $( ".dutyunallocdroppable" ).droppable({
      greedy: true,
      drop: function( event, ui ) {
        var allocunallocidposition= getOffset(document.getElementById("allocunallocid"));
        var maintopposition = allocunallocidposition.top;
        var unallocateddutiesheight = document.getElementById("unallocatedduties").offsetHeight;
        var droppableheight = Math.round(maintopposition+unallocateddutiesheight);
        var dropableflag=1;
        if (event.pageY>droppableheight ){
          dropableflag=0;
        }
        if (dropableflag==0){
          return;
        }
        var jobid = ui.draggable.attr("id");
        var dutyid = $(this).attr("id");
        var dutyparentid =  $(this).attr("parentid");
        var dutystart = $('#dutystart_'+dutyid).val();
        var duty_end = $('#duty_end_'+dutyid).val();
        var jobsttime = $('#unaljobstart_'+jobid).val();
        var jobentime = $('#unaljobend_'+jobid).val();

        //not allowed job assigne where duty has no satrt time or end time
        if(((typeof dutystart=='undefined' )|| dutystart== '00:00' )&&  (typeof  duty_end=='undefined'|| duty_end =='00:00')){
          return;
        }

        // the absolute position of the
        var pos = $("#Allocatedduties").offset().top;
        // the absolute position on the page
        var dpos = $(this).offset().top;
        if (event.ctrlKey) {
          var droppos = (ui.offset.left - $(this).offset().left) / $(this).width();
          if (droppos < 0) (droppos = 0);
        } else {
          var droppos = -1;
        }

        var flag=0;
        let dutystarttime = dutystart.split(':');
        startTimeSec = (+dutystarttime[0]) * 60 * 60 + (+dutystarttime[1]) * 60;
        let dutyendtime = duty_end.split(':');
        endTimeSec = (+dutyendtime[0]) * 60 * 60 + (+dutyendtime[1]) * 60;
        if(startTimeSec > endTimeSec){
          endTimeSec = 86400+endTimeSec;
        }
        let jobstarttime = jobsttime.split(':');
        jobstartTimeSec = (+jobstarttime[0]) * 60 * 60 + (+jobstarttime[1]) * 60;
        let jobendtime = jobentime.split(':');
        jobendTimeSec = (+jobendtime[0]) * 60 * 60 + (+jobendtime[1]) * 60;
        var addflag=0;
				if(jobstarttime > jobendTimeSec){
					jobendTimeSec = 86400+jobendTimeSec;
					addflag=1;
				}
				var midnight =0;
				if ((((jobstartTimeSec < startTimeSec) &&  (addflag==0) )||  (jobendTimeSec > endTimeSec)) && (dutystart!='')) {
					flag=1;
				}
        if (duty_end<dutystart){
          if ((jobstartTimeSec >= 86400 || jobstartTimeSec <= endTimeSec-86400) ||  (jobendTimeSec >= 86400 || jobendTimeSec <= endTimeSec-86400) ){
            var midnight =1;
          }
          if (jobsttime>jobentime){
            if (jobendTimeSec > endTimeSec-86400) {
              flag=1;
            }
          }
          if ((jobstartTimeSec+86400 >= startTimeSec) &&  (jobendTimeSec <= endTimeSec-86400)){
            flag=0;
          }
        } else {
          if ((jobstartTimeSec > endTimeSec) ||  (jobendTimeSec > endTimeSec)){
            flag=1;
          }
          if (jobsttime>jobentime){
            if ((jobstartTimeSec > endTimeSec) ||  (jobendTimeSec+86400 >= endTimeSec)){
              flag=1;
            }
          }
        }
		var overflowflag=0;
				if (endTimeSec>86400){
					if ((endTimeSec-86400) == jobstartTimeSec ){
						overflowflag =1
					}
				} else {
					if (endTimeSec == jobstartTimeSec ){
						overflowflag =1
					}
				}

				if (jobendTimeSec==startTimeSec){
					overflowflag =1
				}

				if (((endTimeSec==86400) && (jobstartTimeSec==0)) || ((jobendTimeSec==86400) && (startTimeSec==0))){
					overflowflag =1
				}
				if (overflowflag ==1){
					return;
				}
        if ((flag ==1 ) && (droppos==-1)){
          $mes = '<table class="smalltable bluetable" width="100%"><tr><td colspan="2">The Job start time or end time is outside of the Duty start and end time. Click OK to continue and amend the Duty Start or End Time to that of the Job.</td></tr></td></tr></td></tr><tr><td ><input style="float:right;" type="button" name="ok" id="afterAdd" value="OK">&nbsp;&nbsp;<input  style="float:right;" type="button" name="cancel" id="cancelafter" value="Cancel"></td></tr></table>';
          $.facebox($mes);
          $("#afterAdd").click(function (e) {
            addunallocatejobtoallocated(dutyid,jobid,droppos);
            $.facebox.close();
          });
          $("#cancelafter").click(function (e) {
            $.facebox.close();
          });
        } else {
          addunallocatejobtoallocated(dutyid,jobid,droppos);
        }
      }
    });

    $( ".dutydroppable" ).droppable({
      greedy: true,
      drop: function( event, ui ) {
        var jobid = ui.draggable.attr("id");
        var dutyid = $(this).attr("id");
        var dutyparentid =  $(this).attr("parentid");
        var dutyname = $(event.target).attr("data-duty-name");
        // the absolute position of the
        var pos = $("#Allocatedduties").offset().top;
        var dutystart = $('#dutystart_'+dutyid).val();
        var duty_end = $('#duty_end_'+dutyid).val();
        var jobsttime = $('#unaljobstart_'+jobid).val();
        var jobentime = $('#unaljobend_'+jobid).val();
        var dutyduration = $('#dutyduration_'+dutyid).val();
        var jobmidnight = $('#jobmidnight_'+jobid).val();
        //not allowed job assigne where duty has no satrt time or end time
        if(((typeof dutystart=='undefined' )|| (dutystart== '00:00' )) && ((typeof  duty_end=='undefined')|| ((duty_end =='00:00') && (dutyduration!='86400')))){
          return;
        }
        if (dutyname=="Absent"){
          return ;
        }
        // the absolute position on the page
        var dpos = $(this).offset().top;
        if ( pos < dpos) {
          if (event.ctrlKey) {
            var droppos = (ui.offset.left - $(this).offset().left) / $(this).width();
            if (droppos < 0) (droppos = 0);
          }else if(event.shiftKey){
            var shiftdrag = 1;
            var dropposnw = (ui.offset.left - $(this).offset().left) / $(this).width();
            if (dropposnw < 0) (dropposnw = 0);
            var droppos = -1;
          }else {
            var droppos = -1;
          }

          var flag=0;
          let dutystarttime = dutystart.split(':');
          startTimeSec = (+dutystarttime[0]) * 60 * 60 + (+dutystarttime[1]) * 60;
          let dutyendtime = duty_end.split(':');
          endTimeSec = (+dutyendtime[0]) * 60 * 60 + (+dutyendtime[1]) * 60;
          if((startTimeSec > endTimeSec) || (dutyduration==86400)){
            endTimeSec = 86400+endTimeSec;
          }
          let jobstarttime = jobsttime.split(':');
          jobstartTimeSec = (+jobstarttime[0]) * 60 * 60 + (+jobstarttime[1]) * 60;
          let jobendtime = jobentime.split(':');
          jobendTimeSec = (+jobendtime[0]) * 60 * 60 + (+jobendtime[1]) * 60;
          if(jobstarttime > jobendTimeSec){
            jobendTimeSec = 86400+jobendTimeSec;
          }
          var midnight =0;
          if (((jobstartTimeSec < startTimeSec) ||  (jobendTimeSec > endTimeSec)) && (dutystart!='')) {
            flag=1;
          }
          if (duty_end<dutystart){
            if ((jobstartTimeSec >= 86400 || jobstartTimeSec <= endTimeSec-86400) ||  (jobendTimeSec >= 86400 || jobendTimeSec <= endTimeSec-86400) ){
                var midnight =1;
            }
            if (jobsttime>jobentime){
              if (jobendTimeSec > endTimeSec-86400) {
                flag=1;
              }
            }
            if ((jobstartTimeSec+86400 >= startTimeSec) &&  (jobendTimeSec <= endTimeSec-86400)){
              flag=0;
            }
          } else {
            if ((jobstartTimeSec > endTimeSec) ||  (jobendTimeSec > endTimeSec)){
              flag=1;
            }
            if (jobsttime>jobentime){
              if ((jobstartTimeSec > endTimeSec) ||  (jobendTimeSec+86400 >= endTimeSec)){
              flag=1;
            }
            }

          }
          /* Prevent outside job from duty */
          var outerflag=0;
          var newjobstart=jobstartTimeSec;
          var newjobend=jobendTimeSec;
          var newduration = jobendTimeSec-jobstartTimeSec;
          if (droppos>0){
            newjobstart = startTimeSec + ((endTimeSec - startTimeSec) * droppos);
            newjobstart = Math.round(newjobstart);
            newjobend = newjobstart + newduration;
          } else {
            if (jobmidnight==1){
              if (jobstartTimeSec>jobendTimeSec){
                newjobend=86400+jobendTimeSec;
              } else {
                newjobstart=86400+jobstartTimeSec;
                newjobend=86400+jobendTimeSec;
              }
            } else {
              if (jobstartTimeSec>jobendTimeSec){
                newjobend=86400+jobendTimeSec;;
              } else {
                newjobstart=jobstartTimeSec;
                newjobend=jobendTimeSec;
              }
            }
          }

          if ((newjobstart<=startTimeSec) && (newjobend<=startTimeSec)){
            outerflag=1;
          }
          if ((newjobstart>=endTimeSec) && (newjobend>=endTimeSec)){
            outerflag=1;
          }
          if (outerflag==1){
            return false;
          }
          if (((newjobend-startTimeSec)>86400) ||  ((endTimeSec-newjobstart)>86400)){
            return false;
          }
          if  ((endTimeSec<=86400) && (newjobend > 86400)){
            return false;
          }

          if (flag==1) {
              check_jobOverlap(dutyid,jobid,droppos,flag,shiftdrag,dropposnw);
          } else {
            if(shiftdrag==1){
              addunallocatejobtoallocated(dutyid,jobid,dropposnw,shiftdrag);
            } else {
              addunallocatejobtoallocated(dutyid,jobid,droppos);
            }
          }
        }
      }
    });

  $( ".editdutydroppable" ).droppable({
    greedy: true,
    drop: function( event, ui ) {
      var jobid = ui.draggable.attr("id");
      var dutyid = $(this).attr("id");
      var dutyparentid =  $(this).attr("parentid");
      var dutystart = $('#dutystart_'+dutyid).val();
      var duty_end = $('#duty_end_'+dutyid).val();
      var roleid =  $("#roleid").val();
      var dutyduration = $('#dutyduration_'+dutyid).val();
      var currentDate = $('#strCurrentDate').val();
      var jobmidnight = $('#jobmidnight_'+jobid).val();
      //not allowed job assigne where duty has no satrt time or end time
      if(((typeof dutystart=='undefined' )|| (dutystart== '00:00' )) || ((typeof  duty_end=='undefined')|| ((duty_end =='00:00') && (dutyduration!='86400')))){
        return;
      }
      // the absolute position of the
      var pos = $("#Allocatedduties").offset().top;
      // the absolute position on the page
      var dpos = $(this).offset().top;
      if ( pos < dpos) {
        if (event.ctrlKey) {
          var droppos = (ui.offset.left - $(this).offset().left) / $(this).width();
          if (droppos < 0) (droppos = 0);
        } else {
          var droppos = -1;
        }
      }
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/edits/movejob.php',
        data: {
          'dutyid': dutyid,
          'jobid': jobid,
          'droppos': droppos,
          'role' : roleid,
          'date': currentDate,
          'midnightJob': jobmidnight
        },
        success: function (data,status) {
          if(data == 0) {
            ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
          } else {
            alert ('Jobs can only be moved where there is space. Either edit this job to fit, or edit the jobs around it to make room')
          }
        }
      });
    }
  });

  $( ".jobsunallocdroppable" ).droppable({
    greedy: true,
    drop: function( event, ui ) {
      var jobid = ui.draggable.attr("id");
      var dutyid = ui.draggable.attr("dutyid");
      var jobbase = ui.draggable.attr("base");
      var roleid =  $("#roleid").val();
      var currentDate = $('#strCurrentDate').val();
      if (event.ctrlKey) {
        var droppos = Math.round((ui.offset.left - $(this).offset().left) / <?php echo $intHourwidth ?> * 1000) / 1000 + <?php echo $earlieststart ?>;
        if (droppos < 0) (droppos = 0);
      } else {
        var droppos = -1;
      }
      $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/edits/unassignjob.php',
        data: {
          'jobid': jobid,
          'droppos': droppos,
          'AllocationId': dutyid,
          'role' : roleid,
          'date' : currentDate,
          'teamId' : <?php echo $intTeamID ?>
        },
        success: function (data,status) {
          ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
        }
      });
    }
  });

});

function addunallocatejobtoallocated(dutyid,jobid,droppos,shiftDrop = 0){
  var roleid =  $("#roleid").val();
  var currentDate = $('#strCurrentDate').val();
  var jobmidnight = $('#jobmidnight_'+jobid).val();
  $.ajax({
    type: 'POST',
    url: 'page-includes/allocations/edits/movejob.php',
    data: {
      'dutyid': dutyid,
      'jobid': jobid,
      'droppos': droppos,
      'role' : roleid,
      'date' : currentDate,
      'shiftDrop': shiftDrop,
      'midnightJob': jobmidnight
    },
    success: function (data,status) {
      data = JSON.parse(data);
      if(data.strstatus == null && data.strstatus == null && data.candropstatus!=0) {
        $(function() {
          $( "#no-space-dialog" ).dialog({
            height:200,
            width:600,
            buttons: {
              "OK": function() {
                $( this ).dialog( "close" );
              }
            }
          });
        });
      } else if (data.strstatus == 1 && data.strsmsg != null && data.candropstatus==0){
        customAlert(data.strsmsg);
      } else {
        ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
      }
    }
  });
}

function check_jobOverlap(dutyid,jobid,droppos,flagstatus=0,shiftdrag=0,dropposnw=0) {
  var roleid =  $("#roleid").val();
  var currentDate = $('#strCurrentDate').val();
  $.ajax({
    type: 'POST',
    url: 'page-includes/allocations/edits/check-jobs-overlap.php',
    data: {
      'dutyid': dutyid,
      'jobid': jobid,
      'droppos': droppos,
      'role' : roleid
    },
    success: function (data,status) {
      data = JSON.parse(data);
      if(data.candropstatus == 1) {
        $.facebox('Jobs can only be moved where there is space. Either edit this job to fit, or edit the jobs around it to make room');
      } else {
        if (flagstatus == 1)
        {
          $mes = '<table class="smalltable bluetable" width="100%"><tr><td colspan="2">The Job start time or end time is outside of the Duty start and end time. Click OK to continue and amend the Duty Start or End Time to that of the Job.</td></tr></td></tr></td></tr><tr><td ><input style="float:right;" type="button" name="ok" id="afterAdd" value="OK">&nbsp;&nbsp;<input  style="float:right;" type="button" name="cancel" id="cancelafter" value="Cancel"></td></tr></table>';
          $.facebox($mes);
            $("#afterAdd").click(function (e) {
                if (shiftdrag == 1) {
                  addunallocatejobtoallocated(dutyid,jobid,dropposnw, shiftdrag);
                } else {
                  addunallocatejobtoallocated(dutyid,jobid,droppos);
                }
              $.facebox.close();
            });
              $("#cancelafter").click(function (e) {
              $.facebox.close();
            });
        }
      }
    }
  });
}

function altdragjob(dutyid,jobid,droppos){
  var roleid =  $("#roleid").val();
  var currentDate = $('#strCurrentDate').val();
  $.ajax({
    type: 'POST',
    url: 'page-includes/allocations/edits/altdrag-job.php',
    data: {
      'dutyid': dutyid,
      'jobid': jobid,
      'droppos': droppos,
      'role' : roleid
    },
    success: function (data,status) {
      data = JSON.parse(data);
      if(data.strstatus == null && data.strstatus == null && data.candropstatus!=0) {
        $(function() {
          $( "#no-space-dialog" ).dialog({
            height:200,
            width:600,
            buttons: {
              "OK": function() {
                $( this ).dialog( "close" );
              }
            }
          });
        });
      } else if (data.strstatus == 0 && data.strsmsg != null && data.candropstatus==0){
        customAlert(data.strsmsg);
      }else{
        ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
      }
    }
  });
}

function SignInToDay(curdate, DutyName, action, StartTime, EndTime, allocationSPID, allocationsDutyId) {
  var rolePermission= <?php echo $rolepermission ?>;
  if (rolePermission==1) {
    screenType=0;
  }  else {
    screenType=1;
  }
  var currentDate = $('#strCurrentDate').val();
  $( "#dialog-sign-in" ).dialog({
    width:600,
    open: function() {
      $(this).siblings('.ui-dialog-buttonpane').find('button:eq(1)').focus();
    },
    buttons: {
      "Sign-In / Un-Sign In": function() {
        $.ajax({
          type: 'POST',
          url: 'page-includes/allocations/allocations-weekly-signin-day.php',
          data: {
            'sdate': currentDate,
            'DutyName': DutyName,
            'action': action,
            'StartTime': StartTime,
            'EndTime': EndTime,
            'screenType': screenType,
            'allocationsSpId' : allocationSPID,
            'allocationsDutyId' : allocationsDutyId
          },
          success: function (data,status) {
            ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
          }
        });
        $( this ).dialog( "close" );
      },
      "Mark As In Building": function() {
        $.ajax({
          type: 'POST',
          url: 'page-includes/allocations/allocations-weekly-signin-day.php',
          data: {
            'sdate': currentDate,
            'DutyName': DutyName,
            'action': 2,
            'StartTime': StartTime,
            'EndTime': EndTime,
            'screenType': screenType,
            'allocationsSpId' : allocationSPID,
            'allocationsDutyId' : allocationsDutyId
          },
          success: function (data,status) {
            if (data == 0) {
              $("#dialog-noinbuilding").dialog({
                title: "Alert!!",
                resizable: false,
                height:180,
                width:500,
                modal: true,
                buttons: {
                  OK: function() {
                    $( this ).dialog( "close" );
                  }
                }
              });
            } else {
              ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
            }
          }
        });
        $( this ).dialog( "close" );
      },
      "Cancel": function() {
        $( this ).dialog( "close" );
      }
    }
    });
}

function SignInDay(curdate, DutyName, action, StartTime, EndTime, allocationSPID, allocationsDutyId) {
  var rolePermission= <?php echo $rolepermission ?>;
  var currentDate = $('#strCurrentDate').val();
  if (rolePermission==1) {
    screenType=0;
  } else {
    screenType=1;
  }
  $.ajax({
    type: 'POST',
    url: 'page-includes/allocations/allocations-weekly-signin-day.php',
    data: {
		'sdate': currentDate,
		'DutyName': DutyName,
		'action': action,
		'StartTime': StartTime,
		'EndTime': EndTime,
		'screenType': screenType,
		'allocationsSpId' : allocationSPID,
		'allocationsDutyId' : allocationsDutyId
    },
    success: function (data,status) {
      ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
    }
  });
}
<?php
if ($editpermission == 1) {
        ?>

$( ".resizable" ).resizable({
  handles: "e, w",
  resize: function( event, ui ) {}
});

$( ".resizable" ).on( "resizestop", function( event, ui ) {
  var dutyid = $(this).attr("id");
  var parentid =  $(this).attr("parentid");
  var OrigLeft = ui.originalPosition.left;
  var OrigWidth = ui.originalSize.width;
  var NewLeft = ui.position.left;
  var NewWidth = ui.size.width;
  var roleid =  $("#roleid").val();
  $.ajax({
    type: 'POST',
    url: 'page-includes/allocations/edits/resizeduty.php',
    data: {
      'dutyid': dutyid,
      'OrigLeft': OrigLeft,
      'OrigWidth': OrigWidth,
      'NewLeft': NewLeft,
      'NewWidth': NewWidth,
      'parentid' : parentid,
      'isShiftleader':roleid
    },
    success: function (data,status) {
      data = JSON.parse(data);
      if(data.strstatus != null && data.strstatus != 1) {
        customAlertByModel(data.strsmsg);
      }
      ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
    }
  });
});

$( ".jobresizable" ).resizable({
  maxHeight: <?php echo (($intDutyHeight / 2) - 5) ?>,
  handles: "e, w",
  resize: function( event, ui ) {}
});

$( ".jobresizable" ).on( "resizestop", function( event, ui ) {
  var jobid = $(this).attr("id");
  var OrigLeft = ui.originalPosition.left;
  var OrigWidth = ui.originalSize.width;
  var NewLeft = ui.position.left;
  var NewWidth = ui.size.width;
  var dutyid = $(this).attr("dutyid");
  var roleid =  $("#roleid").val();
  var currentDate = $('#strCurrentDate').val();
  $.ajax({
    type: 'POST',
    url: 'page-includes/allocations/edits/resizejob.php',
    data: {
      'jobid': jobid,
      'OrigLeft': OrigLeft,
      'OrigWidth': OrigWidth,
      'NewLeft': NewLeft,
      'NewWidth': NewWidth,
      'dutyid' : dutyid,
      'role' : roleid
    },
    success: function (data,status) {
      switch(data) {
        case "1":
          // wont fit
          $("#dialog-jobnofit").dialog({
            title: "Alert!!",
            resizable: false,
            height:200,
            width:400,
            modal: true,
            buttons: {
              OK: function() {
                $( this ).dialog( "close" );
              }
            }
          });
          ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
          break;
        case "2":
          // Extend?
          $mes = 'The Job start time or end time is outside of the Duty start and end time. Click OK to continue and amend the Duty Start or End Time to that of the Job';
          customConfirmModal($mes, function() {
            $.ajax({
              type: 'POST',
              url: 'page-includes/allocations/edits/resizejob.php',
              data: {
                'jobid': jobid,
                'OrigLeft': OrigLeft,
                'OrigWidth': OrigWidth,
                'NewLeft': NewLeft,
                'NewWidth': NewWidth,
                'ForceUpdate': 1,
                'dutyid' : dutyid,
                'role' : roleid
              },
              success: function (data,status) {
              }
            });
            ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
            $.facebox.close();
          },
          function() {
            ShowDailyAllocations (<?php echo $intTeamID ?>,currentDate);
            $.facebox.close();
          },0,'EditWeeklyPublish'
          );
          break;
        default:
          break;
      }
    }
  });
});

function editDailyDuty(id,intWeek,intDay,intTeamID,role,parentid,scheduledPersonId,currdate,miscduty,pdlStartTime=0,pdlEndTime=0, allocationsSpId=0){
  $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/edits/editduty.php',
        data: {
            'dutyid': id,
            'WeekNumber': intWeek,
            'iDay': intDay,
            'teamId': intTeamID,
            'role' : role,
            'parentid' : parentid,
            'scheduledPersonId':scheduledPersonId,
            'currdate':currdate,
            'miscduty': miscduty,
            'pdlStartTime': pdlStartTime,
            'pdlEndTime': pdlEndTime,
            'allocationsSpId': allocationsSpId
        },
        success: function (data) {
            $.facebox(data);
        }
    });
}

function getOffset(el) {
  const rect = el.getBoundingClientRect();
  return {
    left: rect.left + window.scrollX,
    top: rect.top + window.scrollY
  };
}
<?php }?>
</script>
<?php }?>
<script type="text/javascript">
function customtipjob(type='',currObj, dutyId, seqCount=0, totCount=0,pdlJob=''){
    $('#loading').hide();
    if(type == 'showtooltip'){
        if(pdlJob.toUpperCase() == 'PDL'){
            dutyId = pdlJob.toUpperCase()+dutyId;
        }
        let message = $('#'+dutyId).attr('data-qtip-content'); 
        if(message != ''){
            let tipHtml = '';
            tipHtml +='<table class="tablesmalltidy tooltip-table" width="250px" cellpadding="0">';
            tipHtml += '<tr class="tooltip-table-background">';
            tipHtml += '<td colspan="2" style="height:62px; vertical-align:top; padding:3px; border: 1px solid #505050; border-collapse: collapse; font-size:10px;">'+message+'</td>';
            tipHtml += '</tr>';
            tipHtml += '</table>';

            let nativeTopPos = $(currObj).offset().top;
            let nativeLeftPos = $(currObj).offset().left;

            var topPos = 0;

            if($('#Allocatedduties').hasAllocScrollBar()){
                if(seqCount == 0 && totCount == 0){
                    if($('#unallocatedduties').length > 0){
                        topPos = nativeTopPos-200;
                    } else {
                        topPos = nativeTopPos-130;
                    }
                } else {
                let checkLandSLastDuty = parseInt(totCount) - parseInt(seqCount);
                if(checkLandSLastDuty == 0 || checkLandSLastDuty == 1){
                    if($('#unallocatedduties').length > 0){
                        topPos = nativeTopPos-200;
                    } else {
                        topPos = nativeTopPos-120;
                    }
                } else {
                    if($('#unallocatedduties').length > 0){
                        topPos = nativeTopPos-120;
                    } else {
                        topPos = nativeTopPos-40;
                    }
                }
            }
        } else {
            if($('#unallocatedduties').length > 0){
                topPos = nativeTopPos-120;
            } else {
                topPos = nativeTopPos-40;
            }
        }

        let leftPos = nativeLeftPos-193;

        $('#customTip').css('left',leftPos+'px');
        $('#customTip').css('top',topPos+'px');
        $('#customTip').html(tipHtml);
        $('#customTip').show();
      }
    } else {
        $('#customTip').hide();
    }
}

function customtiptitle(type='',currObj, divId){
    $('#loading').hide();
    if(type == 'showtooltip'){
      let message = $('#'+divId).attr('data-qtip-content');
      if(message != ''){
        let tipHtml = '';
        tipHtml +='<table class="tablesmalltidy tooltip-table" width="250px" cellpadding="0">';
        tipHtml += '<tr class="tooltip-table-background">';
        tipHtml += '<td colspan="2" style="height:23px; vertical-align:top; padding:3px; border: 1px solid #505050; border-collapse: collapse;">'+message+'</td>';
        tipHtml += '</tr>';
        tipHtml += '</table>';

        let nativeTopPos = $(currObj).offset().top;
        let nativeLeftPos = $(currObj).offset().left;

        let topPos = nativeTopPos-135;
        let leftPos = nativeLeftPos+10;

        $('#customTip').css('left',leftPos+'px');
        $('#customTip').css('top',topPos+'px');
        $('#customTip').html(tipHtml);
        $('#customTip').show();
      }
    } else {
        $('#customTip').hide();
    }
}

var dutyIdContainer=0;
function customsignedtip(type='',currObj,dutyId){
    $('#loading').hide();
    if(type == 'showtooltip'){
      if(dutyId != dutyIdContainer)
      {
        var reqData = {
          'allocationsSPID': currObj.getAttribute('allocationSPID'),
          'DutyName': currObj.getAttribute('DutyName'),
          'StartTime': currObj.getAttribute('StartTime'),
          'EndTime': currObj.getAttribute('EndTime'),
          'isDaily': 1
        };
        dutyIdContainer = dutyId;
        $.ajax({
          type: "post",
          url: "/page-includes/allocations/allocations-signedin-info.php",
          data: reqData,
          success: function(result){
            let nativeTopPos = $(currObj).offset().top;
            let nativeLeftPos = $(currObj).offset().left;

            let topPos = nativeTopPos-155;
            let leftPos = nativeLeftPos+10;

            $('#customTipsigned').css('left',leftPos+'px');
            $('#customTipsigned').css('top',topPos+'px');
            $('#customTipsigned').html(result);
            $('#customTipsigned').show();
          }
        });
      } else {
        $('#customTipsigned').show();
      }
    } else {
       $('#customTipsigned').html('');
	   $('#customTipsigned').hide();
    }
}

var dutyIdCommentsContainer=0;
function customtipremotepersoncomments(type='',currObj,dutyId){
    $('#loading').hide();
    if(type == 'showtooltip'){
      if(dutyId != dutyIdContainer)
      {
        var reqData = {
            'dutyDate' : $(currObj).attr('dutydate'),
            'schPersonId' : $(currObj).attr('dataSchPersonId'),
            'teamId' : $(currObj).attr('dataTeamId'),
            'dutyId' : $(currObj).attr('id'),
            'allocationSPID' : $(currObj).attr('data-allocation-spid'),
            'allocationID' : $(currObj).attr('data-allocation-id'),
            'rolepermission' : <?php echo $rolepermission; ?>
        };
        dutyIdCommentsContainer = dutyId;
        $.ajax({
          type: "post",
          url: "/page-includes/allocations/allocations-duty-comments-daily.php",
          data: reqData,
          success: function(result){
            let nativeTopPos = $(currObj).offset().top;
            let nativeLeftPos = $(currObj).offset().left;
            let topPos = 0;
            let leftPos = 0;

            if($('#unallocatedduties').length > 0){
              topPos = nativeTopPos-250;
              leftPos = nativeLeftPos-470;
            } else {
              topPos = nativeTopPos-180;
              leftPos = nativeLeftPos-460;
            }

            $('#customTip').css('left',leftPos+'px');
            $('#customTip').css('top',topPos+'px');
            $('#customTip').html(result);
            $('#customTip').show();
          }
        });
      } else {
        $('#customTip').show();
      }
    } else {
        $('#customTip').hide();
    }
}

var dutyIdCommentsPersonContainer=0;
function customtipremotepersoncommentsleft(type='',currObj,dutyId){
    $('#loading').hide();
    if(type == 'showtooltip'){
      if(dutyId != dutyIdContainer)
      {
        var reqData = {
            'dutyDate' : $(currObj).attr('dutydate'),
            'schPersonId' : $(currObj).attr('dataSchPersonId'),
            'teamId' : $(currObj).attr('dataTeamId'),
            'dutyId' : $(currObj).attr('id'),
            'allocationSPID' : $(currObj).attr('data-allocation-spid'),
            'allocationID' : $(currObj).attr('data-allocation-id'),
            'rolepermission' : <?php echo $rolepermission; ?>
        };
        dutyIdCommentsPersonContainer = dutyId;
        $.ajax({
          type: "post",
          url: "/page-includes/allocations/allocations-duty-comments-daily.php",
          data: reqData,
          success: function(result){
            let nativeTopPos = $(currObj).offset().top;
            let nativeLeftPos = $(currObj).offset().left;
            let topPos = 0;
            let leftPos = 0;

            if($('#unallocatedduties').length > 0){
              topPos = nativeTopPos-220;
              leftPos = nativeLeftPos+20;
            } else {
              topPos = nativeTopPos-160;
              leftPos = nativeLeftPos+20;
            }

            $('#customTip').css('left',leftPos+'px');
            $('#customTip').css('top',topPos+'px');
            $('#customTip').html(result);
            $('#customTip').show();
          }
        });
      } else {
        $('#customTip').show();
      }
    } else {
        $('#customTip').hide();
    }
}

function customtipremoteedp(type='',currObj){
    $('#loading').hide();
    if(type == 'showtooltip'){
      var reqData = {
          'schedulingPersonId' : $(currObj).attr('dataSchPersonId'),
          'ddate' : '<?php echo $strCurrentDate ?>',
          'teamId' : <?php echo $intTeamID ?>
      };
      $.ajax({
        type: "post",
        url: "/page-includes/allocations/allocations-edp-info-daily.php",
        data: reqData,
        success: function(result){
          let nativeTopPos = $(currObj).offset().top;
          let nativeLeftPos = $(currObj).offset().left;
          let topPos = 0;
          let leftPos = 0;

          if($('#unallocatedduties').length > 0){
            topPos = nativeTopPos-180;
            leftPos = nativeLeftPos+10;
          } else {
            topPos = nativeTopPos-110;
            leftPos = nativeLeftPos+10;
          }

          $('#customTip').css('left',leftPos+'px');
          $('#customTip').css('top',topPos+'px');
          $('#customTip').html(result);
          $('#customTip').show();
        }
      });
    } else {
        $('#customTip').hide();
    }
}

function customtipremotelock(type='',currObj){
    $('#loading').hide();
    if(type == 'showtooltip'){
      var reqData = {
          'teamId' : <?php echo $intTeamID ?>,
          'dutydate' : '<?php echo $strCurrentDate ?>'
      };
      $.ajax({
        type: "post",
        url: "/page-includes/allocations/allocations-locked-info.php",
        data: reqData,
        success: function(result){
          let nativeTopPos = $(currObj).offset().top;
          let nativeLeftPos = $(currObj).offset().left;
          let topPos = 0;
          let leftPos = 0;

          if($('#unallocatedduties').length > 0){
            topPos = nativeTopPos-110;
            leftPos = nativeLeftPos-390;
          } else {
            topPos = nativeTopPos-30;
            leftPos = nativeLeftPos-390;
          }

          $('#customTip').css('left',leftPos+'px');
          $('#customTip').css('top',topPos+'px');
          $('#customTip').html(result);
          $('#customTip').show();
        }
      });
    } else {
        $('#customTip').hide();
    }
}

function customtiptitlewhite(type='',currObj){
    $('#loading').hide();
    if(type == 'showtooltip'){
      let message = $(currObj).attr('data-title');
      if(message != ''){
        let tipHtml = '';
        tipHtml +='<table class="tablesmalltidy tooltip-table-white" width="250px" cellpadding="0">';
        tipHtml += '<tr class="tooltip-table-background-white">';
        tipHtml += '<td colspan="2" style="height:23px; vertical-align:top; padding:3px; border: 1px solid #FFFFFF; border-collapse: collapse;">'+message+'</td>';
        tipHtml += '</tr>';
        tipHtml += '</table>';

        let nativeTopPos = $(currObj).offset().top;
        let nativeLeftPos = $(currObj).offset().left;
        let topPos = 0;
        let leftPos = 0;

        if($('#unallocatedduties').length > 0){
          topPos = nativeTopPos-110;
          leftPos = nativeLeftPos-250;
        } else {
          topPos = nativeTopPos-30;
          leftPos = nativeLeftPos-250;
        }

        $('#customTip').css('left',leftPos+'px');
        $('#customTip').css('top',topPos+'px');
        $('#customTip').html(tipHtml);
        $('#customTip').show();
      }
    } else {
        $('#customTip').hide();
    }
}

function customtiptitlewhiteleft(type='',currObj,toolTipType=''){
    $('#loading').hide();
    if(type == 'showtooltip'){
      let message = $(currObj).attr('data-title');
      if(message != ''){
        let tipHtml = '';
        tipHtml +='<table class="tablesmalltidy tooltip-table-white" width="250px" cellpadding="0">';
        tipHtml += '<tr class="tooltip-table-background-white">';
        tipHtml += '<td colspan="2" style="height:23px; vertical-align:top; padding:3px; border: 1px solid #FFFFFF; border-collapse: collapse;">'+message+'</td>';
        tipHtml += '</tr>';
        tipHtml += '</table>';

        let nativeTopPos = $(currObj).offset().top;
        let nativeLeftPos = $(currObj).offset().left;
        let topPos = 0;
        let leftPos = 0;

        if($('#unallocatedduties').length > 0){
          topPos = nativeTopPos-105;
          leftPos = nativeLeftPos+10;
        } else {
          topPos = nativeTopPos-25;
          leftPos = nativeLeftPos+10;
        }
        if(toolTipType == 'lockToolTip'){
          if($('#unallocatedduties').length > 0){
            topPos = nativeTopPos-200;
          } else {
            topPos = nativeTopPos-120;
          }
        }

        $('#customTip').css('left',leftPos+'px');
        $('#customTip').css('top',topPos+'px');
        $('#customTip').html(tipHtml);
        $('#customTip').show();
      }
    } else {
        $('#customTip').hide();
    }
}

function customtipgridcheck(type='',currObj){
    $('#loading').hide();
    if(type == 'showtooltip'){
      var reqData = {
        'period': $(currObj).attr('period'),
        'date': $('#strCurrentDate').val(),
        'teamId': <?php echo $intTeamID ?>
      };
      $.ajax({
        type: "post",
        url: "/page-includes/allocations/allocations-grid-checks-info.php",
        data: reqData,
        success: function(result){
          let nativeTopPos = $(currObj).offset().top;
          let nativeLeftPos = $(currObj).offset().left;

          let topPos = nativeTopPos-135;
          let leftPos = nativeLeftPos+10;

          $('#customTip').css('left',leftPos+'px');
          $('#customTip').css('top',topPos+'px');
          $('#customTip').html(result);
          $('#customTip').show();
        }
      });
    } else {
        $('#customTip').hide();
    }
}

$('#allocationPublish').on('click', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
  var screenName = $('#dailyAllocationspublish').find('input[name="screenName"]').val();
var isPublished = $("#allocations-container_daily").attr('data-is-week-published');
if (isPublished == 1) {
        showPublishWeekModal();
    } else {
        customConfirmModal(
    'This week has not been published before.',
            function(){
                showPublishWeekModal();
            },
            function(){},
            0,
            'EditWeeklyPublish'
        );
    }
});

function showPublishWeekModal() {
  var screenName = $('#dailyAllocationspublish').find('input[name="screenName"]').val();
  if (screenName=='ViewDaily'){
    var data = $("#dailyAllocationspublish").serialize();
  } else {
    var data = $("#editWeeklyAllocations").serialize();
  }

    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/publish-week.php",
        data: data,
        success: function (response) {
            $.facebox(response);
        }
    });
}

function openEditWeekly(teamId, weekNumber){
    $.ajax({
        type: "post",
        url: "/components/filters/filter-process.php",
        data: {
          'action':'checkselectedfilter',
          'teamId':teamId
        },
        beforeSend: function (jqXHR, settings){
           $('#loading').hide();
        },
        success: function (response) {
          let returnData = $.parseJSON(response);
          let argDataInitialLoad = {};
          argDataInitialLoad.schedulingTeamId = teamId;
          argDataInitialLoad.userId = $.cookie('editWeeklyUserId');
          argDataInitialLoad.weekNumber = weekNumber.substr(4,2)+'/'+weekNumber.substr(0,4);
          argDataInitialLoad.teamId = teamId;
          if(returnData.status == true){
              argDataInitialLoad.selAutoPageFilterId = returnData.selectedFilterId;
              argDataInitialLoad.shiftCountingFilterId = returnData.selectedShiftCountFilterId;
          } else {
              argDataInitialLoad.selAutoPageFilterId = 0;
              argDataInitialLoad.shiftCountingFilterId = 0;
          }
          editWeeklyPageLoad('No',argDataInitialLoad);
        }
    });
}

(function($) {
    $.fn.hasAllocScrollBar = function() {
        if(this.length > 0) {
            return this.get(0).scrollHeight > this.height();
        }
    }
    dateCommentOptionsSet(2, 'fa-2x', 'lightblue');

    <?php echo ($editpermission == 1 || $shiftleaderflag == 1)  ? 'initializeDateComment()' : 'initializeDateCommentViewIcon()'; ?>
})(jQuery);
</script>
<div class="tooltip-div" id="customTip" aria-atomic="true"></div>
<div class="tooltip-div" id="customTipsigned" aria-atomic="true"></div>
<script type="text/javascript">
<?php if (empty($teamOptions)) {?>
    customAlertByModel('Your session has been interrupted. Please reload the week.');
<?php }?>
</script>
<script>

/*
  Function used for filter data
  */
function applyViewDailyFilter() {
    if((localStorage.getItem('dailyscreen_filter') == '') && (localStorage.getItem('dailyscreen_filter') == null)){
        return false;
    }

    $('#loading').show();
    var StaffNameFilter = $('#StaffNameFilter').val();
    var StaffName = $('#StaffName').val();
    var SortCodeFilter = $('#SortCodeFilter').val();
    var SortCode = $('#SortCode').val();
    var CostCodeFilter = $('#CostCodeFilter').val();
    var CostCode = $('#CostCode').val();
    var SkillFilter = $('#SkillFilter').val();
    var Skill = $('#Skill').val();
    var DutyFilter = $('#DutyFilter').val();
    var Duty = $('#Duty').val();
    var DutyLabelFilter = $('#DutyLabelFilter').val();
    var DutyLabel = $('#DutyLabel').val();
    var startDate = $('#strCurrentDate').val();
    var endDate = $('#strCurrentDate').val();
    var teamId = $('#teamId').val();
    var DutyTime = $('#DutyTime').val();
    var JobNameFilter = $('#JobNameFilter').val();
    var JobName = $('#JobName').val();
    var JobLabelFilter = $('#JobLabelFilter').val();
    var JobLabel = $('#JobLabel').val();
    var groupCondition = $('#andFilter').prop('checked') ? false : true;
    var SortOrder = $('#SortOrder').val();

    let filteredData = [];

    //Get Skills to use in filter
    var scheduledPersonAllSkills = [];
    if(Skill) {
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/filters/filters-get-scheduled-person-skills.php",
            data: {
                startDate: startDate,
                endDate: endDate,
                teamId: teamId
            },
            async: false,
            success: function (response) {
                scheduledPersonAllSkills = (response['status'] == true) ? response['data'] : [];
            }
        });
    }

    //Start filtering
    $('.person-data-cell').each(function() {
        let id = $(this).attr('data-id');
        let matchFound = true;
        let groupOr = false;

        // Staff Name Filter
        if (StaffName) {
            let searchData = $(this).attr('data-order');
            let names = StaffName.split(',').map(s => s.trim());
            let containsMatch = names.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = names.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = names.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = names.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            var staffNameLoopMatch = true;
            if (StaffNameFilter === "*" && !containsMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === "!" && !notContainsMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === ";" && !(exactSomeMatch)) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === "__AND__" && !exactMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && staffNameLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        // Sort Code Filter
        if (SortCode) {
            let searchData = $(this).attr('data-sort-code') ?? '';
            let codes = SortCode.split(',').map(s => s.trim());
            let containsMatch = codes.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = codes.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = codes.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = codes.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            let sortCodeLoopMatch = true;
            if (SortCodeFilter === "*" && !containsMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === "!" && !notContainsMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === ";" && !(exactSomeMatch)) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === "__AND__" && !exactMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && sortCodeLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Cost code
        if (CostCode) {
            let searchData = $(this).attr('data-cost-code') ?? '';
            let codes = CostCode.split(',').map(s => s.trim());
            let containsMatch = codes.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = codes.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = codes.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = codes.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            let costCodeLoopMatch = true;
            if (CostCodeFilter === "*" && !containsMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === "!" && !notContainsMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === ";" && !(exactSomeMatch)) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === "__AND__" && !exactMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && costCodeLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        // Duty name Filter
        if (Duty) {
            let dutyNames = [];
            $('.allocated_duty_' + id).each(function () {
                dutyNames.push($(this).attr('data-duty-name'));
            });

            let duties = Duty.split(',').map(s => s.trim());
            let containsMatch = duties.some(name => new RegExp(name, "i").test(dutyNames));
            let notContainsMatch = duties.every(name => !new RegExp(name, "i").test(dutyNames));
            let exactMatch = duties.every(name =>
                dutyNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = duties.some(name =>
                dutyNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let startsWithSomeMatch = duties.some(name =>
                 dutyNames.some(dn => dn.startsWith(name))
            );

            //filter type
            let dutyLoopMatch = true;
            if (DutyFilter === "*" && !containsMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === "!" && !notContainsMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === ";" && !(exactSomeMatch)) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === "__AND__" && !exactMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if(DutyFilter === "%" && !startsWithSomeMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && dutyLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Skill filter
        if(Skill) {
            let scheduledPersonSkillsLabels = [];

            let scheduledPersonId = id;
            let filteredSkills = typeof scheduledPersonAllSkills[scheduledPersonId] !== 'undefined' ? scheduledPersonAllSkills[scheduledPersonId] : [];
            filteredSkills.forEach(function(item, index) {
                scheduledPersonSkillsLabels.push(item);
            });

            let skills = Skill;
            let containsMatch = skills.some(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let notContainsMatch = skills.every(name =>
                !scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactMatch = skills.every(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = skills.some(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );

            //filter type
            let skillLoopMatch = true;
            if (SkillFilter === "*" && !containsMatch) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === "!" && !notContainsMatch) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === ";" && !(exactSomeMatch)) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === "__AND__" && !exactMatch) {
                skillLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && skillLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Duty Label filter
        if (DutyLabel) {
            let dutyLabels = [];

            $('.allocated_duty_' + id).each(function () {
                let filteredLabels = $(this).attr('data-duty-labels').split(',').map(s => s.trim());
                filteredLabels.forEach(function(item, index) {
                    dutyLabels.push(item);
                });
            });

            let labels = DutyLabel;
            let containsMatch = labels.some(name => new RegExp(name, "i").test(dutyLabels));
            let notContainsMatch = labels.every(name => !new RegExp(name, "i").test(dutyLabels));
            let exactMatch = labels.every(name =>
                dutyLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = labels.some(name =>
                dutyLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );

            //filter type
            let dutyLabelLoopMatch = true;
            if (DutyLabelFilter === "*" && !containsMatch) {
                dutyLabelLoopMatch = false;
                matchFound = false;
            } else if (DutyLabelFilter === "!" && !notContainsMatch) {
                dutyLabelLoopMatch = false;
                matchFound = false;
            } else if (DutyLabelFilter === ";" && !(exactSomeMatch)) {
                dutyLabelLoopMatch = false;
                matchFound = false;
            } else if (DutyLabelFilter === "__AND__" && !exactMatch) {
                dutyLabelLoopMatch = false;
                matchFound = false;
            }

            if((groupCondition && dutyLabelLoopMatch)) { // for or condition
                groupOr = true;
            }
        }

        // Duty time Filter
        if (DutyTime && DutyTime != '-1') {
            let $duty = $('.allocated_duty_' + id);
            let dutyStartTime = $duty.attr('data-duty-start-time');
            let dutyEndTime = $duty.attr('data-duty-end-time');
            let dutyName = $duty.attr('data-duty-name');

            containsMatch = (DutyTime >= dutyStartTime && DutyTime <= dutyEndTime && dutyName.toLowerCase() != 'u');

            //filter type
            let dutyTimeLoopMatch = true;
            if (DutyFilter === "*" && !containsMatch) {
                dutyTimeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && dutyTimeLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        // Job name Filter
        if (JobName) {
            let jobNames = [];
            $('.allocated_duty_job_' + id).each(function () {
                jobNames.push($(this).attr('data-job-name'));
            });

            let jobs = JobName.split(',').map(s => s.trim());
            let containsMatch = jobs.some(name => new RegExp(name, "i").test(jobNames));
            let notContainsMatch = jobs.every(name => !new RegExp(name, "i").test(jobNames));
            let exactMatch = jobs.every(name =>
                jobNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = jobs.some(name =>
                jobNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let startsWithSomeMatch = jobs.some(name =>
                 jobNames.some(dn => dn.startsWith(name))
            );

            //filter type
            let jobLoopMatch = true;
            if (JobNameFilter === "*" && !containsMatch) {
                jobLoopMatch = false;
                matchFound = false;
            } else if (JobNameFilter === "!" && !notContainsMatch) {
                jobLoopMatch = false;
                matchFound = false;
            } else if (JobNameFilter === ";" && !(exactSomeMatch)) {
                jobLoopMatch = false;
                matchFound = false;
            } else if (JobNameFilter === "__AND__" && !exactMatch) {
                jobLoopMatch = false;
                matchFound = false;
            } else if(JobNameFilter === "%" && !startsWithSomeMatch) {
                jobLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && jobLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Job Label filter
        if (JobLabel) {
            let jobLabels = [];

            $('.allocated_duty_job_' + id).each(function () {
                jobLabels.push($(this).attr('data-job-programme'));
            });

            let labels = JobLabel;
            let containsMatch = labels.some(name => new RegExp(name, "i").test(jobLabels));
            let notContainsMatch = labels.every(name => !new RegExp(name, "i").test(jobLabels));
            let exactMatch = labels.every(name =>
                jobLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = labels.some(name =>
                jobLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );

            //filter type
            let jobLabelLoopMatch = true;
            if (JobLabelFilter === "*" && !containsMatch) {
                jobLabelLoopMatch = false;
                matchFound = false;
            } else if (JobLabelFilter === "!" && !notContainsMatch) {
                jobLabelLoopMatch = false;
                matchFound = false;
            } else if (JobLabelFilter === ";" && !(exactSomeMatch)) {
                jobLabelLoopMatch = false;
                matchFound = false;
            } else if (JobLabelFilter === "__AND__" && !exactMatch) {
                jobLabelLoopMatch = false;
                matchFound = false;
            }

            if((groupCondition && jobLabelLoopMatch)) { // for or condition
                groupOr = true;
            }
        }

        if (matchFound || groupOr) {
            filteredData.push(id);
        }
    });

    if (SortOrder && filteredData.length > 1) {
        filteredData.sort(function(a, b) {
            var aEl = $('.person-data-cell[data-id="' + a + '"]');
            var bEl = $('.person-data-cell[data-id="' + b + '"]');
            var aVal, bVal;
          
            if (SortOrder === "1") {
                aVal = (aEl.attr('data-order') || '').toLowerCase();
                bVal = (bEl.attr('data-order') || '').toLowerCase();
            } else if (SortOrder === "2") {
                aVal = (aEl.attr('data-sort-code') || '').toLowerCase();
                bVal = (bEl.attr('data-sort-code') || '').toLowerCase();
            } else if (SortOrder === "3") {
                  var aDuty = $('.allocated_duty_' + a).first();
                  var bDuty = $('.allocated_duty_' + b).first();
                aVal = (aDuty.attr('data-duty-name') || '').toLowerCase();
                bVal = (bDuty.attr('data-duty-name') || '').toLowerCase();
            } else if (SortOrder === "4") {
                var aDuty = $('.allocated_duty_' + a).first();
                var bDuty = $('.allocated_duty_' + b).first();
                var aStartTime = aDuty.attr('data-duty-start-time') || '9999';
                var bStartTime = bDuty.attr('data-duty-start-time') || '9999';
                function timeToMinutes(time) {
                    var parts = time.split(':');
                    return parseInt(parts[0]) * 60 + (parseInt(parts[1]) || 0);
                }
                aVal = timeToMinutes(aStartTime);
                bVal = timeToMinutes(bStartTime);
            } else {
                return 0;
            }

            if (aVal < bVal) return -1;
            if (aVal > bVal) return 1;
            return 0;
        });
    }

    //Show only filtered data and hide remaining
    $('.filter-hide-row').removeClass('filter-hide-row');
    $('.person-data-cell').each(function() {
        let id = $(this).attr('data-id');
        if(filteredData.includes(id) == false) {
          $(this).addClass('filter-hide-row');
          $('#locks_' + id).addClass('filter-hide-row');
          $('#Allocatednames div[dataschpersonid="' + id + '"]').addClass('filter-hide-row');
          $('.allocated_duty_' + id).parent().addClass('filter-hide-row');
          $('#Allocatednames .signedtip[schedulingpersonid="' + id + '"]').addClass('filter-hide-row');
        } else {
          let index = filteredData.indexOf(id);
          let top = <?php echo $intRowHeight ?> * index;
          $(this).css("top", top);
          $('.allocated_duty_' + id).parent().css("top", top);
          $('#locks_' + id).css("top", top + 22);
          $('#Allocatednames div[dataschpersonid="' + id + '"]').css("top", top + 28);
          $('#Allocatednames .signedtip[schedulingpersonid="' + id + '"]').css("top", top + 4);
        }
    });
    let timeCellHeight = <?php echo $intRowHeight ?> * filteredData.length;

    $('#Allocatedduties .dayholderparthour').each(function() {
      $(this).height(timeCellHeight);
    });
    $('#Allocatedduties .dayholderhour').each(function() {
      $(this).height(timeCellHeight);
    });
   $('.highlightedtransparent').each(function() {
      this.style.setProperty('height',  timeCellHeight + 'px', 'important');
    });
 

    $('#loading').hide();
}
</script>