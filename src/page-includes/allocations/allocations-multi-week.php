<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('UTC');
require_once '../../../vendor/autoload.php';
include_once '../../function-includes/bootstrap.php';
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';
include_once '../../function-includes/skillsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
include_once '../../page-includes/allocations/weekly/service/AllocationViewService.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../../page-includes/allocations/weekly/filters/filters-script.php';
include_once __DIR__. '/../../function-includes/user-scheduling-team-list.php';

use App\Models\User\RefRole;
use Symfony\Component\HttpFoundation\Request;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$request = Request::createFromGlobals();

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$service = new AllocationService();
$Vservice = new AllocationViewService();
$commonObj = new classCommonDBFunctions();
$isFreelencer =  $commonObj->GetIsFreelencerByNetlogin($strUser);
$schedulingPersonId = GetScheduledPersonIdbyUserId($userId);
$_SESSION['user']["isFreelance"] = !empty($isFreelencer) ? 1 : 0;
$filterName = 'Filters';
$screenName = 'MultiWeek';

if (isset($_REQUEST['teamId'])) {
    $intTeamID = $_REQUEST['teamId'];
} else {
    $intTeamID = GetDefaultSchedulingTeamIdByLogin($userId);
}

if($request->get('type') == ''){
    $getSetFilterId = GetCurrentSetMultiWeekFilter($screenName, $intTeamID);
    if($getSetFilterId > 0){
        echo '<script type="text/javascript">applyViewFilter("'.$screenName.'",'.$getSetFilterId.')</script>';
    }
}

$validDateNo =  strtotime('-7 year', strtotime(date('Y-m-d')));
$validDate= date('jS F Y',$validDateNo);

$filterQuery = '';
if($request->get('queryStr') != ''){
  $filterQuery = " AND " .$request->get('queryStr');
}
$filterQuery2 = $intTeamID;
if(is_array($request->get('additionalTeams'))){
  $filterQuery2 = $request->get('additionalTeams');
  $addteam=implode(",",$filterQuery2);
  $addteam=$addteam.",".$intTeamID;
  $filterQuery2 = $addteam;
}

$filterQuery3 = '';
if($request->get('queryStr3') != ''){
  $filterQuery3 = $request->get('queryStr3');
}
$filterOrderStr = '';
$intSortOrder = 0;
if($request->get('orderStr') != ''){
  $intSortOrder = $request->get('orderStr');
}
$selFilterType = '';
if($request->get('selFilterType') != ''){
  $selFilterType = $request->get('selFilterType');
}
$selFilterId = '';
if($request->get('selFilterId') != ''){
  $selFilterId = $request->get('selFilterId');
}
$skillFilterDaily ='';
if ($request->get('skillFilterDaily') != '') {
  $skillFilterDaily = $request->get('skillFilterDaily');
}
$dutyFilterDaily ='';
if ($request->get('dutyFilterDaily') != '') {
  $dutyFilterDaily = $request->get('dutyFilterDaily');
}

echo '<input type="hidden" name="selFilterType" id="selFilterType" value="'.$selFilterType.'">';
echo '<input type="hidden" name="selFilterId" id="selFilterId" value="'.$selFilterId.'">';
// Set a few variable for the cell sizes.....
$intDateHeight = 30;
$intStatusHeight= 10;
$rowheight = 56;
$dutywidth = 75;
$nameswidth = 250;
$topheight = 0;
$intWeeksCount = 4;
$arrDepDefaults = GetTeamDefaults(0, $intTeamID);
$arrUser = GetScheduledPersonTeamDetails($schedulingPersonId);
$strUserLogin = $arrUser[$intTeamID]['Login'] ?? '';
if (strtolower($strUserLogin) == strtolower($strUser)) {
  $intThisIsMe = 1;
} else {
  $intThisIsMe = 0;
}

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

// Restrict View for freelancers....
$intFreelanceMaskDays = $arrDepDefaults[$intTeamID]['FreelancerMaskingDays'];
$selSchPersonId = $schedulingPersonId;

$isShiftleader = 1;
$canViewAdditional = 0;
if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isManager == 1)) {
  $isShiftleader = 0;
  $canViewAdditional = 1;
}

if ($isAdmin == 1 || $isManager == 1 || $isScheduler == 1 || $isTeamAdmin == 1 || $hasShiftleaderRole == 1) {
  $intCanViewComments = 1;
  $intSignInAll = 1;
}
else {
  $intCanViewComments = 0;
  $intSignInAll = 0;
}

if ($isManager == 1 || $isScheduler == 1 || $isTeamAdmin == 1) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
} else if(($hasShiftleaderRole == 1 && $intThisIsMe == 1) || ($isSchedulingTeamViewer== 1 && $intThisIsMe == 1) || ($intThisIsMe == 1)) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
}
else {
  $dteFirstDate = date("Y-m-d", strtotime("-$intViewYears years"));
}

$intAllowInBuilding = $arrDepDefaults[$intTeamID]['AllowInBuilding'];
$intSignInDays = $arrDepDefaults[$intTeamID]['SignInDays'];
$intConfirmedDays = $arrDepDefaults[$intTeamID]['ConfirmedDays'];
$intMaskDays = $arrDepDefaults[$intTeamID]['MaskAfter'];
$intMaskType = $arrDepDefaults[$intTeamID]['MaskType'];
$strteamName = $arrDepDefaults[$intTeamID]['Description'];
$intColourWeek = $arrDepDefaults[$intTeamID]['ColourWeek'];
$myschdeullingPersonID = $schedulingPersonId;

if($commonObj->getMultiWeekSeetmgByTeam($intTeamID)!==false) {
  if(!is_null($commonObj->getMultiWeekSeetmgByTeam($intTeamID))) {
  $intWeeksCount = $commonObj->getMultiWeekSeetmgByTeam($intTeamID);
  }else{
    $intWeeksCount =4;
  }
}else{
  $intWeeksCount =4;
}

if (isset($_REQUEST['week'])) {
  // Passed a week Number?
  $intWeekNumber = $_REQUEST['week'];
}
else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
  }
  else {
    $bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
    $intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? 0;
  }
}

$datefromweek =$commonObj->GetWeekStartDateByWeekNoFromTimeDim($intWeekNumber,'ByweeknoOnly',NULL);

$dteStartDate = $datefromweek['dDateTime'];
$dteEndtDate = date('Y-m-d', strtotime($dteStartDate. ' + 4 weeks'));
$intPreviousWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate. ' - 7 days')));
$intPreviousWeek = $intPreviousWeekArray['ixYearWeek'];
$nextweekinList =$intWeeksCount+1;

$intNextWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate. ' + '.$nextweekinList.' weeks')));
$intNextWeek = $intNextWeekArray['ixYearWeek'];

$intSchNextWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate. ' + '.$nextweekinList.' weeks'.'-1 days')));
$intSchNextWeek = $intSchNextWeekArray['ixYearWeek'];

$_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;

$arrHolidays = calculateBankHolidays(date("Y", strtotime($dteStartDate)));
$arrHiddenDays = ReadHiddenDays($intTeamID, $dteStartDate, $dteEndtDate);

// ################################################################################ Is there a filter set?
$strSkillFilter = '';
$strDutyNameFilter = '';
$strPresetFilterFilter = '';
$strSortCodeFilter = '';
$strNameCodeFilter  = '';
$strPresetFilterDesc = '';
$intIsFreelance = $_SESSION['user']["isFreelance"];
$intSkillsCount = CountJobSkillsInDepartment($intTeamID);
$arrFilters = GetDutyFiltersByDepartment($intTeamID);
// ################################################################################ END Is there a filter set?
$intTeamIDs = $intTeamID;
    $weekstatus = $Vservice->checkWeekPublishStatus($intTeamID,$intWeekNumber);
    if($weekstatus > 0) {
        $intIgnoreRota=1;//Check Week here
    }else{
        $intIgnoreRota=0;//Check Week here
    }
$arrAllocations = ReadMultiWeekAllocations($intWeekNumber, $intNextWeek, $intTeamIDs, $intConfirmedDays, $intMaskDays, $intMaskType, $intCanViewComments, $intIgnoreRota, $intSortOrder, $selSchPersonId,$intColourWeek,$filterQuery,$filterQuery2,$filterQuery3,$filterOrderStr,$selSchPersonId='', $isShiftleader,0, 0,0,0,$canViewAdditional);

$arredp = readedp($dteStartDate, $dteEndtDate);

$intTeamDetails = getSchedulingTeamDetails($intTeamID);

// When do we restrict the text from?
$maskfrom = date("Y-m-d", strtotime("+".$intMaskDays." days"));
echo '<h1 class="sr-only">Multi Week Allocation</h1>';
// First the header
echo '<table class="tablegreysmallnoborder" width="100%">';
//echo '<table border="1" width="100%">';
echo '<tr height="38px">';
echo '<td class="medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowAllocationsMulti('.$intTeamID.',"'.$intPreviousWeek.'")\';>&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</span></td>';
echo '<td class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowAllocationsMulti('.$intTeamID.',"'.$intNextWeek.'")\';>Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</span></td>';

echo '<td class="lightcell medtextbold" align="right">Choose Date</td>';
echo '<td class="lightcell medtextbold handcursor" align="center"><input type="hidden" id="multidatepicker"></td>';
echo '<td align="center" class="medtextbold">';
echo '</td>';

// #################################################################################### Filters here
  echo '<td valign="top" class="multiweekFiltertd">';
  echo '<select name="teamId" id="teamId" onchange="allocationViewByTeam(this.value,'.$intNextWeek.')" class="chosen-select">';
  echo '<option value="">Select The Team</option>';
  $teamOptions = getSchedulingTeamList($intTeamID, 'allocation-policy', 'view');
  echo $teamOptions;
  echo '</select>';
        echo '<div class="multiweekfilter">';
        include(__DIR__ . '/../../components/filters/filters.php');
        echo '</div>';

        echo '</div>';
    echo '</td>';
// #################################################################################### END Filters

echo '<td class="lightcell medtextbold" align="right">';
echo 'Currently showing '.($intWeeksCount + 1).' Weeks';
echo '</td>';
echo '<td class="lightcell medtextbold handcursor" align="right">';
echo '<img border="0" src="images/up.png" width="15px" height="15px"';
if ($intWeeksCount <= 20) {
  echo ' onclick="javascript:ChangeWeeks(1)"';
}
echo '></td>';
echo '<td class="lightcell medtextbold handcursor">';
echo '<img border="0" src="images/down.png" width="15px" height="15px"';
if ($intWeeksCount > 1) {
  echo ' onclick="javascript:ChangeWeeks(-1)"';
}
echo '></td>';
echo '<td align="center" valign="top">';
echo '</td>';
echo '<td></td>';
echo '</tr>';
echo '</table>';

// End the header

//have we any allocations to show?
if (isset($arrAllocations) && !empty($arrAllocations)) {
  // The holder
  echo '<div style="position: relative">';
  // The top Left Corner
  echo '<div style="position: absolute; width:'.($nameswidth - 4).'px; height:'.($intDateHeight * 2 + $intStatusHeight).'px; left:0px; top:0px" id="fixed"  class="dotw">';
  echo 'Weeks: '.spinweek($intWeekNumber).' to '.spinweek($intSchNextWeek);
  echo '</div>';
  // END the holder

  // The weeks holder
  echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($intDateHeight * 2 + $intStatusHeight).'px; left:'.$nameswidth.'px; top:0px" id="multiweektop">';
  for ($i = 0; $i <= $intWeeksCount; $i++) {
    $WeekNumber = addweeks($intWeekNumber, $i);
    echo '<div class="dotwcentre handcursor" style="overflow: hidden; position: absolute; width:'.($dutywidth * 7).'px; height:'.($intDateHeight -1).'px; left:'.($dutywidth * 7 * $i).'px; top:0px" onclick=\'javascript:ShowAllocations('.$intTeamID.', "'.$WeekNumber.'")\';>';
    echo spinweek($WeekNumber);
    echo '</div>';
    // The days of the week....
    for ($ii = 0; $ii <=6; $ii++) {
      $addeddays = ($i * 7) + $ii;
      $strCurrDate = date("Y-m-d", strtotime("+".$addeddays." days", strtotime($dteStartDate)));
      if (isset($arrHolidays[$strCurrDate])) {
        echo '<div title="'.$arrHolidays[$strCurrDate].'" style="width:'.$dutywidth.'px; position:absolute; left:'.($addeddays * $dutywidth).'px; top:'.$intDateHeight.'px; height:'.($intDateHeight - 1).'px" class="dotw handcursor date-comment" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $intTeamID . '" onclick=\'javascript:ShowDailyAllocations('.$intTeamID.', "'.$strCurrDate.'")\';>';
      } else {
        echo '<div style="width:'.$dutywidth.'px; position:absolute; left:'.($addeddays * $dutywidth).'px; top:'.$intDateHeight.'px; height:'.($intDateHeight - 1).'px" class="dotw handcursor date-comment" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $intTeamID . '" onclick=\'javascript:ShowDailyAllocations('.$intTeamID.', "'.$strCurrDate.'")\';>';
      }
      echo $invdowMap[$ii];
      if (isset($arrHolidays[$strCurrDate])) {
        echo " *";
      }
      echo '<br>'.spindate($strCurrDate);
      echo '<span class="date-commment-info-icon" style="cursor: pointer; float:right;position: absolute; padding: 5px; top: 8px; right: -4px;"></span>';
      echo '</div>';

            $strStatusClass = GetDayStatus($strCurrDate, $intCanViewComments, $intMaskDays, @$arrHiddenDays[$intTeamID]);
            echo '<div pageid="2" date="' . $strCurrDate . '" team="' . $intTeamID . '" style="width:' . $dutywidth . 'px; position:absolute; left:' . ($addeddays * $dutywidth) . 'px; top:' . ($intDateHeight * 2) . 'px; height:' . $intStatusHeight . 'px" class="' . $strStatusClass . ' handcursor" onclick=\'javascript:ShowDailyAllocations(' . $intTeamID . ', "' . $strCurrDate . '")\';>';
            echo '</div>';

    }
  }
  echo '</div>';
  // END the days of the week....

// ################################################################################## The names down the left side
  echo '<div style="overflow: scroll; position: absolute; width:'.$nameswidth.'px; height:400px; left:0px; top:'.($intDateHeight * 2 + $intStatusHeight).'px" class="whitebackground" id="multiweeknames">';
    $counter = 0;
    foreach ($arrAllocations as $sn => $value) {
      $TextColour = $value["StaffTextColour"];
      $BackColour = $value["StaffBackColour"];
      echo '<div class="names handcursor person-data-cell personname_'.$sn.'" style="position: absolute; width:100%; height:'.($rowheight - 1).'px; left:0px; top:'.($counter * $rowheight).'px;background-color:'.$BackColour.'" data-cost-code="' . $value['CostCode'] . '" data-sort-code="' . mb_convert_encoding($value["SortCode"], 'UTF-8', 'ISO-8859-1') . '" data-id="' . $value['SchedulingPersonID'] . '" data-order="' . mb_convert_encoding($value["FullName"], 'UTF-8', 'ISO-8859-1') . '" onclick="highlightRowPerson('.$sn.')">';
      if(isset($value["FullName"]))echo '<b><font color="'.$TextColour.'"   onclick=\'event.stopPropagation(); ShowRota("'.$dteStartDate.'","'.$sn.'",'.$intTeamID.')\';>'.$value["FullName"];
      echo '</b><br>';
      if(isset($value["SortCode"]))echo $value["SortCode"];
      echo '</font></div>';
      $counter++;
    }
  echo '</div>';
// ############################################################################### End the names

// The Duties
// The duties in the div
echo '<div style="overflow: scroll; position: absolute; width:900px; height:400px; left:'.$nameswidth.'px; top:'.($intDateHeight * 2 + $intStatusHeight).'px" id="multiweekduties" class="whitebackground">';
$counter = 0;

foreach ($arrAllocations as $sn => $value) {
  echo '<div class="scheduledPerson_duties_' . $value['SchedulingPersonID'] . '">';
  for ($wc = 0; $wc <= $intWeeksCount; $wc++) {
    $WeekNumber = addweeks($intWeekNumber, $wc);
    $weekleftoffset = $wc * $dutywidth * 7;
    for ($i = 0; $i <= 6; $i++) {
      $currdate = datefromweek($WeekNumber, $i);
      $strMaskClass = GetDayStatus($currdate, $intCanViewComments, $intMaskDays,isset($arrHiddenDays[$intTeamID])?$arrHiddenDays[$intTeamID]:'');
      $backColor  ='';
      $fontColor ='';
      $fontStyle = '';
      $jobs = [];
      $dutyLabels = [];
      $tDutyName = '';
      if (isset($arrHiddenDays[$intTeamID][$currdate]) && $intCanViewComments == 0 || $dteFirstDate > $currdate) {
        echo '<div style="width:'.($dutywidth - 1).'px; position:absolute; left:'.($weekleftoffset + ($i * $dutywidth)).'px; top:'.($counter * $rowheight).'px; height:'.($rowheight - 1).'px" class="DutyCellNotWorking handcursor dutyAllocation_'.$sn.'">';
         echo '';
         echo '</div>';
      }
      else {
       // may not have an entry here.....
        if (isset($value[$WeekNumber][$i])) {
          if ($intColourWeek == 1) {
              if (is_array($value[$WeekNumber][$i]["Duty"])) {
                if (sizeof($value[$WeekNumber][$i]["Duty"])>1) {
                  $dutyKeys =array_keys($value[$WeekNumber][$i]["Duty"]);
                  sort($dutyKeys);
                  $backColor  =$value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["BackColour"];
                  $fontColor =$value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["FontColour"];
                  $fontStyle = getFontStyle($value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['display_priority'] ?? 0,
                      $intTeamDetails['colourWeek'],
                      $intTeamID,
                      $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['TeamID'],
                      $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['LeaveType'] ?? $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['Duty'],
                      $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]
                  );
                  $CellClass = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["CellClass"];
                  $Dutyid = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Dutyid"];
                  $dutyLabels = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['DutyLabels'];
                  $tDutyName = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Duty"];
                  $jobs = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Jobs"] ?? [];
                  if (($value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Dutycomments"] ?? 0) != 0) {
                    $hascomments = 1;
                  }
                  if ($intCanViewComments == 1 && isset($value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["personcomments"]) && $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["personcomments"] != 0) {
                    $hascomments = 1;
                  }
                } else {
                    foreach ($value[$WeekNumber][$i]["Duty"] as $dutyData) {
                      $backColor  = $dutyData["BackColour"];
                      $fontColor  = $dutyData["FontColour"];
                      $fontStyle = getFontStyle($dutyData['display_priority'] ?? 0,
                          $intTeamDetails['colourWeek'],
                          $intTeamID,
                          $dutyData['TeamID'],
                          $dutyData['LeaveType'] ?? $dutyData['Duty'],
                          $dutyData
                      );
                      $CellClass = $dutyData["CellClass"];
                      $dutyLabels = $dutyData['DutyLabels'];
                      $tDutyName = $dutyData["Duty"];
                      $Dutyid = $dutyData["Dutyid"];
                      $hascomments = isset($dutyData["personcomments"]) ? $dutyData["personcomments"] : null;
                      $jobs = $dutyData["Jobs"] ?? [];
                    }
                  }
              } else {
                  $CellClass = isset($value[$intWeekNumber][$i]["Duty"][0]["CellClass"])?$value[$intWeekNumber][$i]["Duty"][0]["CellClass"]:'';
                  $backColor = $value[$WeekNumber][$i]["BackColour"];
                  $fontColor = $value[$WeekNumber][$i]["FontColour"];
              }
              echo '<div style="background-color:'.$backColor.'; width:'.($dutywidth - 1).'px; position:absolute; left:'.($weekleftoffset + ($i * $dutywidth)).'px; top:'.($counter * $rowheight).'px; height:'.($rowheight - 1).'px" class="'.$CellClass.' handcursor scheduledPersonDuty dutyAllocation_'.$sn.'" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '">';
              echo '<font color="'. $fontColor.'" style="' . $fontStyle . '">';
            }
            else {
              if (is_array($value[$WeekNumber][$i]["Duty"])) {
                if (sizeof($value[$WeekNumber][$i]["Duty"])>1) {
                  $dutyKeys =array_keys($value[$WeekNumber][$i]["Duty"]);
                  sort($dutyKeys);
                  if($value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]>0) {
                    $CellClass = 'DutyCellWorking';
                  } else {
                    $CellClass = 'DutyCellNotWorking';
                  }
                  $backColor = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["BackColour"];
                  $fontColor = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["FontColour"];
                  $fontStyle = getFontStyle($value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['display_priority'] ?? 0,
                      $intTeamDetails['colourWeek'],
                      $intTeamID,
                      $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['TeamID'],
                      $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['LeaveType'] ?? $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['Duty'],
                      $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]
                  );
                  $Dutyid = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Dutyid"];
                  $dutyLabels = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]['DutyLabels'];
                  $tDutyName = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Duty"];
                  $jobs = $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Jobs"] ?? [];
                  if (($value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["Dutycomments"] ?? 0) != 0) {
                    $hascomments = 1;
                  }
                  if ($intCanViewComments == 1 && isset($value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["personcomments"]) && $value[$WeekNumber][$i]["Duty"][$dutyKeys[0]]["personcomments"] != 0) {
                    $hascomments = 1;
                  }
                } else {
                    foreach ($value[$WeekNumber][$i]["Duty"] as $dutyData) {
                      if($dutyData["Duration"]>0) {
                        $CellClass = 'DutyCellWorking';
                      } else {
                        $CellClass = 'DutyCellNotWorking';
                      }
                      $backColor = isset($dutyData["BackColour"]) ? $dutyData["BackColour"] : '';
                      $fontColor = isset($dutyData["FontColour"]) ? $dutyData["FontColour"] : '';
                      $fontStyle = getFontStyle($dutyData['display_priority'] ?? 0,
                          $intTeamDetails['colourWeek'],
                          $intTeamID,
                          $dutyData['TeamID'],
                          $dutyData['LeaveType'] ?? $dutyData['Duty'],
                          $dutyData
                      );
                      $Dutyid = isset($dutyData["Dutyid"]) ? $dutyData["Dutyid"] : '';
                      $tDutyName = isset($dutyData["Duty"]) ? $dutyData["Duty"] : '';
                      $dutyLabels = isset($dutyData["DutyLabels"]) ? $dutyData["DutyLabels"] : '';
                      $hascomments = isset($dutyData["personcomments"]) ? $dutyData["personcomments"] : null;
                      $jobs = $dutyData["Jobs"] ?? [];
                    }
                  }
              } else {
                $CellClass = isset($value[$WeekNumber][$i]['Duty'][0]["CellClass"])?$value[$WeekNumber][$i]['Duty'][0]["CellClass"]:'';
                $backColor = $value[$WeekNumber][$i]["BackColour"];
                $fontColor = $value[$WeekNumber][$i]["FontColour"];
              }
              echo '<div style="background-color:'.$backColor.'; width:'.($dutywidth - 1).'px; position:absolute; left:'.($weekleftoffset + ($i * $dutywidth)).'px; top:'.($counter * $rowheight).'px; height:'.($rowheight - 1).'px" class="'.$CellClass.' handcursor scheduledPersonDuty dutyAllocation_'.$sn.'" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '">';
              echo '<font color="'.$fontColor.'" style="' . $fontStyle . '">';
            }

// If Rota Duties
if(is_array($value[$WeekNumber][$i]["Duty"])) {
  if(sizeof($value[$WeekNumber][$i]["Duty"])>1) {
      foreach($value[$WeekNumber][$i]["Duty"] as $key=>$DutyData) {
        if($key==0) {
          if(($DutyData["display_priority"] == 2) && ($strMaskClass == 'daynotfixed' || $strMaskClass == 'daynotfixed allocations-hideday-menu')) {
            if($DutyData['LeaveType'] !== null && (strtolower($DutyData['LeaveType']) == 'leave' || strtolower($DutyData['LeaveType']) == 'off leave')) {
              echo '<font ><b>'.$DutyData['LeaveType'].'</b></font><br> ' . (!empty($DutyData['Duty']) ? '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . '">Was: '. $DutyData['Duty'] . '</font>' : '');
            } else {
              if($DutyData['IsPublished'] == 0){
                echo '<i>'.$DutyData['Duty'].'</i>';
              } else {
                echo $DutyData['Duty'];
              }
            }
          }
           else if($DutyData['LeaveType'] !== null && (strtolower($DutyData['LeaveType']) == 'leave' || strtolower($DutyData['LeaveType']) == 'off leave')) {
            echo '<font ><b>'.$DutyData['LeaveType'].'</b></font><br> ' . (!empty($DutyData['Duty']) ? '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . '">Was: '. $DutyData['Duty'] . '</font>' : '');
          } else {
            if($DutyData['IsPublished'] == 0){
              echo '<i>'.$DutyData['Duty'].'</i>';
            } else {
              echo $DutyData['Duty'];
            }
          }

          if ($DutyData['IsTemplate'] != 0 && $DutyData['Duration'] > 0 && $DutyData['isEditable']==1) {
            $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
            if($DutyData['Duty']!='U') {
              echo "<br>".$intendHour ." Hours";
            }
          } else {
              if((strtolower($DutyData["Duty"])!='sick') && (strtolower($DutyData["Duty"])!='u-sick') && (strtolower($DutyData["Duty"])!='-sick')){
                  if(($DutyData["StartTime"] < $DutyData["EndTime"])|| ($DutyData["StartTime"] > $DutyData["EndTime"]) && is_null($DutyData['LeaveType'])) {
                    echo "<br>".$DutyData["StartTime"].'-'.$DutyData["EndTime"];
                    if (($DutyData['pdlStartTime'] != 0) || ($DutyData['pdlEndTime'] != 0)) {
                      $pdlStartTime = (int) $DutyData["pdlStartTime"];
                      $pdlEndTime = (int) $DutyData['pdlEndTime'];
                      if ($pdlStartTime > $pdlEndTime) {
                          $pdlEndTime = (86400 + $pdlEndTime);
                      }
                      $pdlTitle = 'PDL: ' . $service->convertSecondsIntoTime($pdlStartTime, ':', 'No') . '-' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') . ' | ' . $service->convertSecondsIntoTime(($pdlEndTime - $pdlStartTime), '.', 'Yes');
                      echo '<br><span style="color:#109146;">[L]</span>';
                    }
                  } else {
                      if ($DutyData['Duration'] > 0 && is_null($DutyData['LeaveType'])) {
                          $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                          echo "<br>".$intendHour ." Hours";
                      }
                  }
              }
            // Only Do SignIn if there is a start
            if(($intSignInDays > 0) && ($DutyData["StartTime"] !='00:00') && $currdate >= date("Y-m-d") && (strtotime($currdate) <= strtotime("+".$intSignInDays." days", strtotime(date("Y-m-d")))) && strtoupper($DutyData["Duty"])!='U' && ($DutyData["Duty"] != "") && (strtoupper($DutyData["Duty"])!='ABSENT' && strtoupper($DutyData["Duty"])!='LEAVE' && strtoupper($DutyData["Duty"]!='SICK')) && ($DutyData["isEditable"]==1) && ($DutyData["display_priority"] == 1)) {
              $js = '';

              if (!isset($DutyData["signin"])) {
                $img = 'red_cross.png';
                $action = 1;
              } else {
                  switch ($DutyData["signin"]) {
                    case 1:
                        if ($DutyData["inbuilding"] == 1) {
                            // Signed in OK
                            $img = 'blue_tick.png';
                            $action = 0;
                        }
                        else {
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
                  } //Switch End
                  if ($currdate == date("Y-m-d") && $intAllowInBuilding == 1) {
                    if ($intSignInAll == 1) {
                      $js = ' onclick=\'javascript:MultiWeekSignInToDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'")\';';
                    } else {
                      if($myschdeullingPersonID == $value["SchedulingPersonID"])  {
                        $js = ' onclick=\'javascript:MultiWeekSignInToDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'")\';';
                      } else {
                        $js = '';
                      }
                    }
                  }
                  else {
                    if ($intSignInAll == 1) {
                      $js = ' onclick=\'javascript:MultiWeekSignInDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'")\';';
                    }
                    else {
                      if($myschdeullingPersonID == $DutyData["SchedulingPersonID"])  {
                        $js = ' onclick=\'javascript:MultiWeekSignInDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'")\';';
                      }
                      else {
                        $js = '';
                      }
                    }
                  } //For Other Dates
              } //Else Close when Sign is set
              echo '<div allocationsSPID = "'.$DutyData["AllocationsSPID"].'" StartTime = "'.$DutyData["StartTimeSec"].'"  EndTime = "'.$DutyData["EndTimeSec"].'"  DutyName = "'.$DutyData["Duty"].'" date="'.$currdate.'" ScheduledPerson="'.$sn.'" teamid="'.$intTeamID.'"  class="handcursor signedtip WeekDutyCellTopRight" '.$js.'>';
              echo '<img src="images/'.$img.'" border="0" height="12px" width="12px">';
              echo '</div>';
          } //Conditinal check for Is Editable Entry only
        }//Else Close if Check for template Type
      } //Only one Entry Display Check
    } //Foreach End
  } else {
      foreach($value[$WeekNumber][$i]["Duty"] as $DutyData) {
        if(($DutyData["display_priority"] == 2) && ($strMaskClass == 'daynotfixed' || $strMaskClass == 'daynotfixed allocations-hideday-menu')) {
          if(isset($DutyData['LeaveType']) && (strtolower($DutyData['LeaveType']) == 'leave' || strtolower($DutyData['LeaveType']) == 'off leave')) {
            echo '<font ><b>'.$DutyData['LeaveType'].'</b></font><br> ' . (!empty($DutyData['Duty']) ? '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . '">Was: '. $DutyData['Duty'] . '</font>' : '');
          } else {
            if($DutyData['IsPublished'] == 0){
              echo '<i>'.$DutyData['Duty'].'</i>';
            } else {
              echo $DutyData['Duty'];
            }
          }
        } else if(isset($DutyData['LeaveType']) && (strtolower($DutyData['LeaveType']) == 'leave' || strtolower($DutyData['LeaveType']) == 'off leave')) {
          echo '<font ><b>'.$DutyData['LeaveType'].'</b></font><br> ' . (!empty($DutyData['Duty']) ? '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . '">Was: '. $DutyData['Duty'] . '</font>' : '');
        } else {
          if($DutyData['IsPublished'] == 0){
            echo '<i>'.$DutyData['Duty'].'</i>';
          } else {
            echo $DutyData['Duty'];
          }
        }

        if($DutyData['IsTemplate'] != 0 && $DutyData['Duration']>0 && $DutyData['isEditable']==1) {
            $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
            if($DutyData['Duty']!='U') {
            echo "<br>".$intendHour ." Hours";
            }
        } else {
            $duty = strtolower($DutyData["Duty"] ?? '');
            if ($duty !== 'sick' && $duty !== 'u-sick' && $duty !== '-sick') {
                if(($DutyData["StartTime"] < $DutyData["EndTime"])|| ($DutyData["StartTime"] > $DutyData["EndTime"]) && is_null($DutyData['LeaveType'])) {
                  echo "<br>". '<font style="color:' . ($intTeamDetails['colourWeek'] == 0 && $DutyData['display_priority'] == 2  ? 'blueviolet' : ($intTeamDetails['colourWeek'] == 1 ? $fontColor : '')) . ';">' . $DutyData["StartTime"].'-'.$DutyData["EndTime"] . '</font>';
                  if (($DutyData['pdlStartTime'] != 0) || ($DutyData['pdlEndTime'] != 0)) {
                    $pdlStartTime = (int) $DutyData["pdlStartTime"];
                    $pdlEndTime = (int) $DutyData['pdlEndTime'];
                    if ($pdlStartTime > $pdlEndTime) {
                        $pdlEndTime = (86400 + $pdlEndTime);
                    }
                    $pdlTitle = 'PDL: ' . $service->convertSecondsIntoTime($pdlStartTime, ':', 'No') . '-' . $service->convertSecondsIntoTime($pdlEndTime, ':', 'No') . ' | ' . $service->convertSecondsIntoTime(($pdlEndTime - $pdlStartTime), '.', 'Yes');
                    echo '<br><span style="color:#109146;" title="'.$pdlTitle.'">[L]</span>';
                  }
                } else {
                    if ($DutyData['Duration'] > 0 && is_null($DutyData['LeaveType'])) {
                        $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                        echo "<br>".$intendHour ." Hours";
                    }
                }
            }

          //# Only Do SignIn if there is a start
          if (($intSignInDays > 0) && ($DutyData["StartTime"] !='00:00') && $currdate >= date("Y-m-d") && (strtotime($currdate) <= strtotime("+".$intSignInDays." days", strtotime(date("Y-m-d")))) && strtoupper($DutyData["Duty"])!='U' && !empty($DutyData["Duty"]) &&(strtoupper($DutyData["Duty"])!='ABSENT' && strtoupper($DutyData["Duty"])!='LEAVE' && strtoupper($DutyData["Duty"]!='SICK')) && ($DutyData["isEditable"]==1) && ($DutyData["display_priority"] == 1)) {
            $js = '';

            if (!isset($DutyData["signin"])) {
                $img = 'red_cross.png';
                $action = 1;
            }
            else {
                switch ($DutyData["signin"]) {
                    case 1:
                        if ($DutyData["inbuilding"] == 1) {
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
                        $img = 'red_cross.png';
                        $action = 1;
                    break;
                }//Switch Close
                if ($currdate == date("Y-m-d") && $intAllowInBuilding == 1) {
                    if ($intSignInAll == 1) {
                        $js = ' onclick=\'javascript:MultiWeekSignInToDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'")\';';
                    } else {
                        if ($myschdeullingPersonID == $value["SchedulingPersonID"]) {
                            $js = ' onclick=\'javascript:MultiWeekSignInToDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'")\';';
                            } else {
                                $js = '';
                            }
                    }
                } else {
                    if ($intSignInAll == 1) {
                        $js = ' onclick=\'javascript:MultiWeekSignInDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'")\';';
                    } else {
                        if($myschdeullingPersonID == $value["SchedulingPersonID"]) {
                            $js = ' onclick=\'javascript:MultiWeekSignInDay("'.$currdate.'",'.$action.',"'.$DutyData["Duty"].'" , '.$DutyData["StartTimeSec"].', "'.$DutyData["EndTimeSec"].'", "'.$DutyData["AllocationsDutyID"].'", "'.$DutyData["AllocationsSPID"].'")\';';
                        } else {
                            $js = '';
                        }
                    }
                }
            } //When isset is set
            echo '<div AllocationsSPID = "'.$DutyData["AllocationsSPID"].'" StartTime = "'.$DutyData["StartTimeSec"].'"  EndTime = "'.$DutyData["EndTimeSec"].'"  DutyName = "'.$DutyData["Duty"].'" date="'.$currdate.'" ScheduledPerson="'.$sn.'" teamid="'.$intTeamID.'"  class="handcursor signedtip WeekDutyCellTopRight" '.$js.'>';
            echo '<img src="images/'.$img.'" border="0" height="12px" width="12px">';
            echo '</div>';
        } //Check close of Sign days condition
      }
    }
  }
} else {
            echo  str_replace('--', '-', $value[$WeekNumber][$i]["Duty"]);
      }
            echo '</font>';
            if(is_array($jobs) && count($jobs) > 0 && $arrDepDefaults[$intTeamID]['HasJobsInWeeklyView'] == 1) {
                $jobData = [];
                foreach($jobs as $job) {
                    if(($job['JobBackColour'] == '#ffffff' && $job['JobFontColour'] == '#000000') ||
                    ($job['JobBackColour'] == '#fff' && $job['JobFontColour'] == '#000')
                    ) {
                        continue;
                    }
                    $jobData[] = [
                        'jobName' => $job['JobName'],
                        'jobStartTime' => gmdate("H:i", (intval($job['JobStartTime']))),
                        'jobEndTime' => gmdate("H:i", (intval($job['JobEndTime'])))
                    ];
                }
                if(!empty($jobData)) {
                  echo '<span class="view-job-details-icon" data-job-detail=\'' . json_encode($jobData) . '\' style="float:right; position: absolute; top: 42px; right: 0px;"><i class="fa fa-circle" style="color: blueviolet;margin-right: 5px;" aria-hidden="true"></i></span>';
                }
            }
            echo '</div>';
    } else {
           echo '<div style="width:'.($dutywidth - 1).'px; position:absolute; left:'.($weekleftoffset + ($i * $dutywidth)).'px; top:'.($counter * $rowheight).'px; height:'.($rowheight - 1).'px" class="DutyCellNotWorking handcursor dutyAllocation_'.$sn.'">';
           echo '';
           echo '</div>';
         }
      }
    }
  }
  $counter++;
  echo '</div>';
 }
echo '</div>';

}
else {
  echo '<br>';
  echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
  echo '<br><br>';
  echo 'There are no Allocations for this period';
  echo '<br></br><br>';
  echo '</div>';
}

?>
<script type="text/javascript" src="js/allocations/weekly/weekly-jobs-view.js?v=<?php echo time(); ?>" data-key-name="highlightRowMultiWeeklyPerson-"></script>
<script language="JavaScript" type="text/javascript">
$(function() {
  dateCommentOptionsSet(0, 'fa-1x', 'lightblue');
  initializeDateCommentViewIcon();
});
function allocationViewByTeam(teamId,weekno) {
  ShowAllocationsMulti(teamId,weekno);
}

function MultiWeekSignInToDay(date, action, DutyName, StartTime, EndTime, allocationsDutyId, allocationsSpId) {
  $( "#dialog-sign-in" ).dialog({
    width:600,
    open: function() {
      $(this).siblings('.ui-dialog-buttonpane').find('button:eq(1)').focus();
    },
    buttons: {
      "Sign-In / Un-Sign In": function() {
        $('*').qtip('hide');
        $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
          sdate: date,
          DutyName: DutyName,
          action: action,
          StartTime: StartTime,
          EndTime: EndTime,
          allocationsDutyId: allocationsDutyId,
          allocationsSpId: allocationsSpId,
          screenType: '1'
        },
        function(data,status) {
          {
            ShowAllocationsMulti(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
          }
        });
        $( this ).dialog( "close" );
      },
      "Mark As In Building": function() {
        $('*').qtip('hide');
        $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
          sdate: date,
          DutyName: DutyName,
          action: 2,
          StartTime: StartTime,
          EndTime: EndTime,
          allocationsDutyId: allocationsDutyId,
          allocationsSpId: allocationsSpId,
          screenType: '1'
        },
        function(data,status){
          {
            if (data == 0) {
              $("#dialog-noinbuilding").dialog({
                title: "Alert!!",
                resizable: false,
                height:160,
                width:600,
                modal: true,
                buttons: {
                  OK: function() {
                    $( this ).dialog( "close" );
                  }
                }
              });
            }
            else {
              ShowAllocationsMulti(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
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

function MultiWeekSignInDay(date, action, DutyName, StartTime, EndTime, allocationsDutyId, allocationsSpId) {
  $('*').qtip('hide');
  $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
    sdate: date,
    DutyName: DutyName,
    action: action,
    StartTime: StartTime,
    EndTime: EndTime,
	allocationsDutyId: allocationsDutyId,
	allocationsSpId: allocationsSpId,
    screenType: '1'
  },
  function(data,status) {
    {
      ShowAllocationsMulti(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
    }
  }
  );
}
$(document).ready(function() {
	<?php if($validDateNo > strtotime($dteStartDate)){
			$bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
			$_SESSION["allocations"]["WeekNumber"] = $bbcweeknumberArray['ixYearWeek'] ?? 0;
			?>
			customAlert("Allocate cannot open a Week older than seven years. Please select another Week.");
				 ShowAllocationsMulti('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
			<?php } ?>

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
        $('*').qtip('hide');
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

  $('.signedtip').each(function() {
    $(this).qtip({
      content: {
        text: function(event, api) {
          $.ajax({
              url: 'page-includes/allocations/allocations-signedin-info.php',
              type: 'POST',
              data: {
                allocationsSPID: api.elements.target.attr('allocationsSPID'),
				StartTime: api.elements.target.attr('StartTime'),
				EndTime: api.elements.target.attr('EndTime'),
				DutyName: api.elements.target.attr('DutyName')
              }
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


  $(document).click(function (event) {
    if(!$(event.target).closest('#accordion').length) {//if you clicked outside of the accordion
      $("#accordion").accordion({active: false});//collapse all the panels
    }
  });

  var widowwidth = $(window).width() - 24 - <?php echo $nameswidth?>;
  var widowheight = $(window).height() - $("#multiweektop").height() - 98;

  $("#multiweekduties").width(widowwidth + 20).height(widowheight);
  $("#multiweeknames").height(widowheight);
  $("#multiweektop").width(widowwidth);
  	// If cookie is set, scroll to the position saved in the cookie.
	if ( $.cookie("vscroll") !== null ) {
    $("#multiweeknames").scrollTop( $.cookie("W-vscroll") );
    $("#multiweeknames").scrollTop( $.cookie("W-vscroll") );
    $("#multiweekduties").scrollLeft( $.cookie("W-hscroll") );
  }

	    // When scrolling happens....
	$("#multiweekduties").on("scroll", function() {
    // Set a cookie that holds the scroll position.
    $.cookie("W-vscroll", $("#multiweekduties").scrollTop() );
    $.cookie("W-hscroll", $("#multiweekduties").scrollLeft() );
  });

  $('[title]').qtip({
        position: {
        viewport: $(window)
      },
    style: { classes: 'qtip-rounded qtip-shadow qtip-light'}
  });
});

$('#multiweekduties').on('scroll', function () {
    $('#multiweeknames').scrollTop($(this).scrollTop());
    $('#multiweektop').scrollLeft($(this).scrollLeft());

});

$('#multiweeknames').on('scroll', function () {
    $('#multiweekduties').scrollTop($(this).scrollTop());
});

$(window).resize(function() {
  var widowwidth = $(window).width() - 24 - <?php echo $nameswidth?>;
  var widowheight = $(window).height() - $("#multiweektop").height() - 98;
  $("#multiweekduties").width(widowwidth + 20).height(widowheight);
  $("#multiweeknames").height(widowheight);
  $("#multiweektop").width(widowwidth);
})

$(function() {
  $( "#multidatepicker" ).datepicker({
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    dateFormat: "yy-mm-dd",
    defaultDate: "<?php echo $dteStartDate?>" ,
    onSelect: function (dateText, inst) {
      var currentdate = dateText;
      $.post("page-includes/allocations/allocations-set-week-from-date.php", {
        date: currentdate
      },
      function(data,status){{
        ShowAllocationsMulti('<?php echo $intTeamID?>',data)
      }});
    }
  });
});
$(function() {
  $( "#skill" ).autocomplete({
    source: "page-includes/allocations/allocations-returnstaffwithskills.php?department=<?php echo $intTeamID?>",
    minLength: 1,
    select: function( event, ui ) {
    SetFilter('11', ui.item.id, <?php echo $intTeamID?>, 2)
    }
  })
  .on('mouseup', function() {
    $(this).select();
  });;
});
$('#FormDutyName').validate({
  rules:{
    "filter":{
      required:true,
    },
  },
  submitHandler: function(form) {
    $.ajax({type:'POST', url: 'page-includes/allocations/allocations-setfilter.php', data:$('#FormDutyName').serialize(), success: function(data) {
    ShowAllocationsMulti('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
    }});
  }
})

$('#FormSortCode').validate({
  rules:{
    "filter":{
      required:true,
    },
  },
  submitHandler: function(form) {
    $.ajax({type:'POST', url: 'page-includes/allocations/allocations-setfilter.php', data:$('#FormSortCode').serialize(), success: function(data) {
    ShowAllocationsMulti('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
    }});
  }
})
$('#FormNameFilter').validate({
  rules:{
    "filter":{
      required:true,
    },
  },
  submitHandler: function(form) {
    $.ajax({type:'POST', url: 'page-includes/allocations/allocations-setfilter.php', data:$('#FormNameFilter').serialize(), success: function(data) {
    ShowAllocationsMulti('<?php echo $intTeamID;?>',<?php echo $intWeekNumber?>);
    }});
  }
})

function ChangeWeeks(action) {
  $.post("page-includes/allocations/allocations-change-mult-iweeks.php", {
    action: action,TeamId:<?php echo $intTeamID;?>
  });
  ShowAllocationsMulti('<?php echo $intTeamID;?>',<?php echo $intWeekNumber?>)

}

<?php if(empty($teamOptions)) { ?>
    customAlertByModel('Your session has been interrupted. Please reload the week.');
<?php }?>

/*
  Function used for filter data
*/
function applyViewMultiWeeklyFilter() {
    if((localStorage.getItem('weeklyscreen_filter') == '') && (localStorage.getItem('weeklyscreen_filter') == null)){
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
    var startDate = '<?php echo $dteStartDate; ?>';
    var endDate = '<?php echo $dteEndtDate; ?>';
    var teamId = $('#teamId').val();
    var AdditionalTeamsFilter = $('#AdditionalTeamsFilter').val();
    var AdditionalTeams = $('#AdditionalTeams').val();

    var groupCondition = $('#andFilter').prop('checked') ? false : true;

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
            $('.scheduledPerson_duties_' + id + ' .scheduledPersonDuty').each(function () {
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

             $('.scheduledPerson_duties_' + id + ' .scheduledPersonDuty').each(function () {
                let dutyLabels = [];
                let filteredLabels = $(this).attr('data-duty-labels').split(',').map(s => s.trim());
                filteredLabels.forEach(function(item, index) {
                    dutyLabels.push(item);
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

                if(matchFound) {
                    return false;
                }
            });
        }

        if (matchFound || groupOr) {
            filteredData.push(id);
        }
    });


    //Show only filtered data and hide remaining
    $('.filter-hide-row').removeClass('filter-hide-row');
    var totalVisible = 0;
    $('.person-data-cell').each(function() {
        if(filteredData.includes($(this).attr('data-id')) == false) {
            $(this).addClass('filter-hide-row');
            $('.scheduledPerson_duties_' + $(this).attr('data-id')).addClass('filter-hide-row');
        } else {
            let top = <?php echo $rowheight ?> * totalVisible;
            $(this).css("top", top);
            $('.scheduledPerson_duties_' + $(this).attr('data-id') + ' .scheduledPersonDuty').css("top", top);
            totalVisible++;
        }
    });
    $('#loading').hide();
}
</script>