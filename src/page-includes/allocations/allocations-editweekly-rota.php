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
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/skillsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
include_once '../../page-includes/allocations/weekly/service/AllocationViewService.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../../function-includes/user-scheduling-team-list.php';
include_once '../../page-includes/allocations/weekly/service/CreateWeekService.php';
include_once '../../function-includes/masterduty_filter_functions.php';

// ################ A few settings that affect page layout ##########################################

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
$filterName = 'Filters';
$screenName = 'EditWeeklyRota';
$schedulingPersonId = GetScheduledPersonIdbyUserId($userId);
if (isset($_POST['teamId']) && ($_POST['teamId']!='') && (strtolower($_POST['teamId'])!='nan')) {
    $intTeamID = $_POST['teamId'];
} else {
    $intTeamID = GetDefaultSchedulingTeamIdByLogin($schedulingPersonId);
    $_POST['teamId'] = $intTeamID;
}
if (isset($_SESSION['screenwidth'])) {
    $intScreenWidth = $_SESSION['screenwidth'];
} else {
    $intScreenWidth = 1600;
}

$intDateHeight = 55;
$intStatusHeight= 10;
$intRowHeight = 45;
$intNamesWidth = 250;
$intDutyWidth = ($intScreenWidth - $intNamesWidth - 40) / 7;
$intTableWidth = $intNamesWidth + ($intDutyWidth * 7);
$intShowAll = 1;
$intCanViewComments = 0;
$intShowCanDo = 0;
// ########################################## END  ########################################################
// ################### Work Out the week we are to view ###################################################
//change into request
if (isset($_POST['WeekNumber']) && ($_POST['WeekNumber']!='') ) {
    // Passed a week Number?
    $intWeekNumber = $_POST['WeekNumber'];
} else {
    if (isset($_SESSION["allocations"]["WeekNumber"])) {
        // Is the session set?
        $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
    } else {
        $bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
        $intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? 0;
    }
}

$WeekNum = substr(strval($intWeekNumber), 4, 2);
$WeekYear = substr(strval($intWeekNumber), 0, 4);
$newweeknum = $WeekNum . '/' . $WeekYear;

$requestval="";
$argweeknumber="";
$argstartDate="";
$argendDate="";
$argendWeek="";
$finalargendweeknumber="";
$rotaData = !empty($_POST['rotaData']) ? ($_POST['rotaData']) : '';
if (isset($_POST['requestval'])) {
	$requestval = json_encode($_POST['requestval']);
	$_SESSION["allocations"]["requestval"] =$requestval ;
} else {
	if (isset($_SESSION["allocations"]["requestval"])) {
         $requestval = $_SESSION["allocations"]["requestval"];
    }
}

if (isset($_POST['argweeknumber'])) {
	$argweeknumber = $_POST['argweeknumber'];
	$_SESSION["allocations"]["argweeknumber"] =$argweeknumber ;
} else {
	if (isset($_SESSION["allocations"]["argweeknumber"])) {
         $argweeknumber = $_SESSION["allocations"]["argweeknumber"];
    }
}
if (isset($_POST['argstartDate'])) {
	$argstartDate = $_POST['argstartDate'];
	$_SESSION["allocations"]["argstartDate"] =$argstartDate ;
} else {
	if (isset($_SESSION["allocations"]["argstartDate"])) {
         $argstartDate = $_SESSION["allocations"]["argstartDate"];
    }
}
if (isset($_POST['argendDate'])) {
	$argendDate = $_POST['argendDate'];
	$_SESSION["allocations"]["argendDate"] =$argendDate ;
} else {
	if (isset($_SESSION["allocations"]["argendDate"])) {
         $argendDate = $_SESSION["allocations"]["argendDate"];
    }
}
if (isset($_POST['argendWeek'])) {
	$argendWeek = $_POST['argendWeek'];
	$_SESSION["allocations"]["argendWeek"] =$argendWeek ;
}	else {
	if (isset($_SESSION["allocations"]["argendWeek"])) {
         $argendWeek = $_SESSION["allocations"]["argendWeek"];
    }
}
if (isset($_POST['finalargendweeknumber'])) {
	$finalargendweeknumber = $_POST['finalargendweeknumber'];
	$_SESSION["allocations"]["finalargendweeknumber"] =$finalargendweeknumber ;
} else {
	if (isset($_SESSION["allocations"]["finalargendweeknumber"])) {
         $finalargendweeknumber = $_SESSION["allocations"]["finalargendweeknumber"];
    }
}

$filterQuery = '';
if($request->get('queryStr') != ''){
  $filterQuery = " AND ".$request->get('queryStr');
}
$filterQuery2 = $intTeamID;
if($request->get('queryStr2') != ''){
  $filterQuery2 = $request->get('queryStr2');
  preg_match_all('!\d+!', (string) $filterQuery2, $matches);
  $addteam=implode(",",$matches[0]);
  $addteam=$addteam.",".$intTeamID;
  $filterQuery2 = $addteam;
}

$filterOrderStr = '';
if($request->get('orderStr') != ''){
  $filterOrderStr = $request->get('orderStr');
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
$mastMiscId = 0;
if ($request->get('mastMiscFilterId') != '') {
	$mastMiscId = $request->get('mastMiscFilterId');
}
echo '<input type="hidden" name="selFilterType" id="selFilterType" value="'.$selFilterType.'">';
echo '<input type="hidden" name="selFilterId" id="selFilterId" value="'.$selFilterId.'">';

$datefromweek = $commonObj->GetWeekStartDateByWeekNoFromTimeDim($intWeekNumber,'ByweeknoOnly',NULL);
$dateStartStr = explode(' ',(string) $datefromweek['dDateTime']);
$dteStartDate = $dateStartStr[0];
$dteEndtDate = date('Y-m-d', strtotime($dteStartDate. ' + 6 days'));
$dteWeektDate = date('Y-m-d', strtotime($dteStartDate. ' + 7 days'));
$validDateNo =  strtotime('-7 year', strtotime(date('Y-m-d')));
$validDate= date('jS F Y',$validDateNo);

$request->request->set('startDate',$dteStartDate);
$request->request->set('endDate', $dteWeektDate);

$adHocDuties = count($service->getAdhocDuties($request));

$masterMiscDutiesFilterData =  GetMasterDutyFilter($intTeamID,$userId);
$mastMiscFilterData = json_decode($masterMiscDutiesFilterData,true);
$teamOptions = getSchedulingTeamList($intTeamID, 'allocation-policy', 'viewEditWeekly');   
if ($intTeamID == 0) {
    echo '<br>';
    echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
    echo '<br><br>';
    echo 'You are attempting to view the Allocations for your Default Team.<br>Please assign a default team or select the required team from the allocations menu.';
    echo '<br></br><br>';
    echo '</div>';
} else {
    $arrTeamDefaults = GetTeamDefaults($userId, $intTeamID);
    $isShiftleader = 1;
    $request->request->set('isShifttoCheck', 0);
    $request->request->set('cando', $intShowCanDo);
    $isScheduler = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULER);
    $isManager = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::MANAGER);
    $isTeamAdmin = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SCHEDULING_TEAM_ADMIN);
    $hasShiftleaderRole = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SHIFT_LEADER);
    $isTeamLeader = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::TEAM_LEADER);
    // If system admin or area admin the person have STA permission
    if($isTeamAdmin == 0) {
        $isTeamAdmin = $userRoleTrait->checkEditWeeeklyAdminRole($intTeamID);
    }
    if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isManager == 1)) {
        $isShiftleader = 0;
    }

    if ($isManager == 1 || $isScheduler == 1 || $isTeamAdmin == 1) {
        $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
	} else {
        $dteFirstDate = date("Y-m-d", strtotime("-$intViewYears years"));
    }

    $intIsFreelance = $_SESSION['user']["isFreelance"];
    // Restrict View for freelancers...

    $intMaskDays = $arrTeamDefaults[$intTeamID]['MaskAfter'];
    $intMaskType = $arrTeamDefaults[$intTeamID]['MaskType'];
    $intColourWeek = $arrTeamDefaults[$intTeamID]['ColourWeek'];
    $arrFilters = GetDutyFiltersByDepartment($intTeamID);

// Are we going to import this week (Dep negative)
    /*This  Code will replaced with WS (types teams in A7)
    earlier it was NegativeDept with Schedule All concept*/

    $arrHolidays = calculateBankHolidayst(date("Y", strtotime($dteStartDate)));
    $intPreviousWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate. ' - 7 days')));
    $intPreviousWeek = $intPreviousWeekArray['ixYearWeek'];
    $intNextWeekArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date('Y-m-d', strtotime($dteStartDate. ' + 7 days')));
    $intNextWeek = $intNextWeekArray['ixYearWeek'];
    $_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;
    $intTeamIDs = $intTeamID;
    $arrCurrentFilter[0] ??= 10;
    $arrCurrentFilter[1] ??= 10;
	$arrHiddenDays= [];
    $arrRequests = GetAppliedRequestsANdLocks($dteStartDate, $dteEndtDate);
    $arrHiddenDays = ReadHiddenDays($intTeamID, $dteStartDate, $dteEndtDate);
    $weekstatus = $Vservice->checkWeekPublishStatus($intTeamID,$intWeekNumber);
    if($weekstatus > 0) {
        $intIgnoreRota=1;//Check Week here
    }else{
        $intIgnoreRota=0;//Check Rota here
    }
    if($hasShiftleaderRole == 1 && $intShowCanDo == 1) {
        $request->request->set('isShifttoCheck', 1);
    }
    if(isset($_POST['isShifttoCheck'])) {
        $request->request->set('isShifttoCheck', $_POST['isShifttoCheck']);
    }

    $isShifttoCheck = $request->get('isShifttoCheck');

    if ($dteStartDate > $dteFirstDate) {
       $arrAllocations = ReadEditWeekRotaAllocations($intWeekNumber, $intWeekNumber, $intTeamIDs,  $intMaskDays, $intMaskType, $intCanViewComments, $filterQuery,$filterQuery2,$filterOrderStr, $schedulingPersonId,$skillFilterDaily,$dutyFilterDaily, $mastMiscId, $isShifttoCheck, json_decode($rotaData, true));
	} else {
        $arrAllocations = [];
    }

    $getTeamColorWeek = GetSchedulingTeamColorWeek($intTeamID);

// When do we restrict the text from?
    $maskfrom = date("Y-m-d", strtotime("+".$intMaskDays." days"));
    echo '<div id="loadingWeeklyRota" style="position: fixed; width: 100%; z-index: 15; height: 100%; padding: 15% 0 0 48%; display:none;">
        <img border="0" src="images/loading.gif" width="145px" height="100px">
    </div>';
    echo '<table class="tablegreysmallnoborder allocationWeekHeader" border="1" width="100%">';
    echo '<tr height="35px">';
    echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:allocationViewByTeam('.$intTeamID.',"'.$intPreviousWeek.'","'.$mastMiscId.'")\';>&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</span></td>';
    echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:allocationViewByTeam('.$intTeamID.',"'.$intNextWeek.'","'.$mastMiscId.'")\';>Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</span></td>';
    echo '<td width="120px" class="medtextbold" align="right">Choose Date</td>';
    echo '<td width="75px" class="medtextbold handcursor" align="left"><input type="hidden" id="datepicker"></td>';
    echo '<td width="200px" class="medtextbold" align="center" nowrap>';
    echo 'Allocations for Week '.spinweek($intWeekNumber).'</td>';
    echo '</td>';
    // ------Filters Start ----

    echo '<td width="200px" style="padding-left:10px">';
    echo '<select name="teamId" id="teamId" onchange=\'allocationViewByTeam(this.value,"'.$intWeekNumber.'","'.$mastMiscId.'")\' class="chosen-select">';
    if(!empty($teamOptions)) {
        echo '<option value="">Select The Team</option>';
        echo $teamOptions;
    } else {
        echo '<option value="">No Team Assigned</option>';
    }
    echo '</select>';
	echo '</td>';
	echo '<td  width="200px" style="padding-left:10px">';
    echo '<div>';
	include(__DIR__ . '/../../components/filters/filters.php');
    echo '</div>';
	echo '</td>';
	echo '<td  width="200px" style="padding-left:10px">';
    echo '<div class="filterContainer fContainer2" style="display:none">
          <span class="heading">Master & Misc. Filters</span>
          <div class="fieldBox">
          <select name="mastMiscFilterId" id="mastMiscFilterId" class="chosen-select" onchange=\'allocationViewByTeam('.$intTeamID.',"'.$intWeekNumber.'",this.value)\' >
			<option selected value="">No Filter</option>';
            if(!empty($mastMiscFilterData)) {
				foreach($mastMiscFilterData as $k => $v) {
					if($v['IsActive'] == '1'){
						echo '<option value="'. $v["MasterDutyFilterID"].'"';
						if($request->get('mastMiscFilterId') == $v['MasterDutyFilterID']) { echo 'selected="selected"'; }
						echo '>'. $v['FilterName'].'</option>';
					}
				}
            }
          echo '</select>
          </div>
          </div>';
    echo '</td>';
    echo '<td  width="50px" style="padding-left:10px">';
	if ($isTeamLeader == 0) {
		echo '
		<form name="createWeekForm" id="createWeekForm">
		<input type="button" value="Create Week" id="createWeekRota" style="background-color: #ededed;">
		<input type="hidden" id="newweeknum" name=newweeknum value="'.$newweeknum.'">
		<input type="hidden" id="intWeekNumber" name=intWeekNumber value="'.$intWeekNumber.'">
		<input type="hidden" id="argweeknumber" name=argweeknumber value="'.$newweeknum.'">
		<input type="hidden" id="argstartDate" name=argstartDate value="'.$dteStartDate.'">
		<input type="hidden" id="argendDate" name=argendDate value="'.$dteWeektDate.'">
		<input type="hidden" id="argendWeek" name=argendWeek value="'.$argendWeek.'">
		 <input type="hidden" name="shiftCountingCheckBox" id="hide">
		</form>';
	}
	echo '</td>';
    echo '</tr>';
	echo '<tr>';
    echo '<td class="medtextbold handcursor" colspan="3">&nbsp;&nbsp;';
    if ($arrTeamDefaults[$intTeamID]['HasDutiesView'] == 1) {
        echo '&nbsp;&nbsp;<img title="Show Production View" class="handcursor" onclick=\'javascript:ShowAllocationsDuties("'.$intTeamID.'","'.$intWeekNumber.'")\'; border="0" src="../images/AllocationsWeeklyDuties.png" width="53px" height="28px">&nbsp;&nbsp;';
    }
    if ($intIsFreelance == 0) {
        echo '<img title="Multi Week View" src="images/AllocationsMultuWeekl.png" width="53px" height="28px" border="0" onclick="javascript:ShowAllocationsMulti('.$intTeamID.', \''.$intWeekNumber.'\')";>';
    }
    echo '<img id="print" class="weeklyPrintIcon" class="handcursor" name="print" border="0" src="images/print.png"  alt="Print" onclick="window.print();">';
    echo '</td>';
    echo '<td colspan="4" class="medtextbold handcursor"></td>';
	echo '<td width="200px" align="center" class="medtextbold"></td>';

	echo '<td valign="top" style="padding-right:20px">';
	if (($isTeamLeader == 0) && ($adHocDuties>0)){
		echo '<input type="button" value="View Ad Hoc Duties" id="viewAdhocDutiesRota">';
	}
	echo '</td>';

    echo '</tr> ';
    echo '</table>';
  ?>

<style>
    .addpeople {
        background-color:#c7c7c7;
    }
</style>
<div class="tooltip-div" id="customTip" aria-atomic="true"></div>
<?php
if (isset($arrAllocations) && count($arrAllocations) > 0 && $arrAllocations!==false) {
    // The holder
    echo '<div style="position: relative">';
    // The top Left Corner
    echo '<div style="position: absolute; width:'.$intNamesWidth.'px; height:'.($intDateHeight - 1).'px; left:0px; top:0px" id="fixed"  class="dotw">';
    echo 'Week: '.spinweek($intWeekNumber);
    echo '</div>';
    // END the holder

    // The days of the week....
    echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($intDateHeight - 1).'px; left:'.$intNamesWidth.'px; top:0px" id="weeklytop">';
    for ($i = 0; $i <=6; $i++) {
        $strCurrDate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
        if ($strCurrDate == date("Y-m-d")) {
            echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - $intStatusHeight - 1).'px" class="dotwlight handcursor" onclick=\'javascript:ShowDailyAllocations('.$intTeamID.',"'.$strCurrDate.'",0,'.$intShowAll.')\';>';
        } else {
            echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - $intStatusHeight - 1).'px" class="dotw handcursor" onclick=\'javascript:ShowDailyAllocations('.$intTeamID.',"'.$strCurrDate.'",0,'.$intShowAll.')\';>';
        }
        echo $invdowMap[$i] .'<br>'.spindate($strCurrDate);
        if (isset($arrHolidays[$strCurrDate])) {
            echo "<br>(".$arrHolidays[$strCurrDate].")";
        }
        echo '</div>';
        $strStatusClass = GetDayStatus($strCurrDate, $intCanViewComments, $intMaskDays, @$arrHiddenDays[$intTeamID]);
        echo '<div pageid="1" date="'.$strCurrDate.'" team="'.$intTeamID.'" style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($intDateHeight - $intStatusHeight).'px; height:'.($intStatusHeight - 1).'px" class="'.$strStatusClass.' handcursor">';
        echo '</div>';
    }
    echo '</div>';
    // END the days of the week....
    // The names down the left side
    echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:'.$intNamesWidth.'px; height:400px; left:0px; top:'.$intDateHeight.'px" class="whitebackground" id="weeklynames">';
    $counter = 0;

    foreach ($arrAllocations as $sn => $value) {
        $TextColour = "#000000";
        $BackColour = "#cccccc";
        echo '<div TeamID="'.$intTeamID.'" LogIn="'.$value["Login"].'" class="names person-data-cell" style="position: absolute; width:100%; height:'.($intRowHeight - 1).'px; left:0px; top:'.($counter * $intRowHeight).'px;background-color:'.$BackColour.'" data-cost-code="' . $value['CostCode'] . '" data-sort-code="' . mb_convert_encoding($value["SortCode"], 'UTF-8', 'ISO-8859-1') . '" data-id="' . $value['SchedulingPersonID'] . '" data-order="' . mb_convert_encoding($value["FullName"], 'UTF-8', 'ISO-8859-1') . '">';


        echo '<font color="'.$TextColour.'">'. mb_convert_encoding($value["FullName"], 'UTF-8', 'ISO-8859-1');
        echo '<br>';
        echo mb_convert_encoding($value["SortCode"], 'UTF-8', 'ISO-8859-1');
        echo '</font></div>';
        $counter++;
    }
    echo '</div>';
  // ############################################################################### End the names

  // The Duties
  // The duties in the div
        echo '<div style="overflow: scroll; position: absolute; width:900px; height:400px; left:'.$intNamesWidth.'px; top:'.$intDateHeight.'px" id="weeklyduties" class="whitebackground">';
        $counter = 0;
        foreach ($arrAllocations as $sn => $value) {
            $hascomments=0;
            $hasmanualOT=0;
            $intFirstLock = 0;
			$tDutyName="";
            echo '<div class="scheduledPerson_duties_' . $value['SchedulingPersonID'] . '">';
            for ($i = 0; $i <=6; $i++) {
				$tDutyName="";
                $currdate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
			    $strMaskClass = GetDayStatus($currdate, $intCanViewComments, $intMaskDays,$arrHiddenDays[$intTeamID] ?? '');
                  // The day is hidden?
                $backColor  ='';
                $fontColor ='';
                $dutyLabels = [];
                if (isset($arrHiddenDays[$intTeamID][$currdate]) && $intCanViewComments == 0) {
                    echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="DutyCellNotWorking handcursor">';
                      echo '</div>';
                  } else {
                        if (isset($value[$intWeekNumber][$i])) {
                        // Do we colour the background?
                            if ($intColourWeek == 1) {
                                if (is_array($value[$intWeekNumber][$i]["Duty"])) {
                                    if (sizeof($value[$intWeekNumber][$i]["Duty"])>1) {
                                        $dutyKeys =array_keys($value[$intWeekNumber][$i]["Duty"]);
                                        sort($dutyKeys);
                                        $backColor  =$value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["BackColour"];
                                        $fontColor = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["FontColour"];
                                        $CellClass = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["CellClass"];
                                        $dutyLabels = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]['DutyLabels'];
                                        $Dutyid = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Dutyid"];
										$tDutyName = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duty"];
										if (($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["StartTime"]=='00:00') && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["EndTime"]=='00:00')) {
											$tDutyTime = ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]==0)?'':number_format((float)($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"] / 3600), 2, '.', '');
										} else {
											$tDutyTime = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["StartTime"].'-'.$value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["EndTime"];
										}
										$tDutyTeam = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["schedulingTeamName"];
										$tDutyLeaveType = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["LeaveType"];
										$AllocationID = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["AllocationID"];

                                    } else {
                                        foreach ($value[$intWeekNumber][$i]["Duty"] as $dutyData) {
											$tDutyName="";
                                            $backColor  = $dutyData["BackColour"];
                                            $fontColor  = $dutyData["FontColour"];
                                            $CellClass = $dutyData["CellClass"];
                                            $Dutyid = $dutyData["Dutyid"];
                                            $dutyLabels = $dutyData['DutyLabels'];
											$AllocationID = $dutyData["AllocationID"];
											$tDutyName = $dutyData["Duty"];
											if (($dutyData["StartTime"]=='00:00') && ($dutyData["EndTime"]=='00:00')) {
												$tDutyTime = ($dutyData["Duration"]==0)?'':number_format((float)($dutyData['Duration'] / 3600), 2, '.', '');
											} else {
												$tDutyTime = $dutyData["StartTime"].'-'.$dutyData["EndTime"];
											}
											$tDutyTeam = $dutyData["schedulingTeamName"];
											$tDutyLeaveType = $dutyData["LeaveType"];
							            }
                                    }
                            } else {
							    $CellClass = $value[$intWeekNumber][$i]["Duty"][0]["CellClass"] ?? '';
                                $backColor = $value[$intWeekNumber][$i]["BackColour"];
                                $fontColor = $value[$intWeekNumber][$i]["FontColour"];
                            }
							if((strtolower((string) $tDutyName) == 'leave' || strtolower((string) $tDutyName) == 'off leave'|| strtolower((string) $tDutyName) == 'u-sick'|| strtolower((string) $tDutyName) == 'sick'|| strtolower((string) $tDutyName) == '-sick') || ($tDutyName=="-") || ($tDutyName=="--") || ($tDutyName=="") || ($tDutyName=="U") ) {
								 echo '<div style="background-color:'.$backColor.'; width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px; overflow:hidden;" class="'.$CellClass.'  DutyCellNotWorking   handcursor scheduledPersonDuty" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '" >';
							} else {

							$dutyqtipcontentw1=$tDutyName;
							$dutyqtipcontentw1 .=($tDutyTime=='')?'':"<br>". $tDutyTime;
							$dutyqtipcontentw1 .="<br>".$tDutyTeam;
                            echo '<div style="background-color:'.$backColor.'; width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px; overflow:hidden;" class="'.$CellClass.'  handcursor scheduledPersonDuty" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '" id ='.$Dutyid.' data-qtip-content="' . $dutyqtipcontentw1 . '" onmouseover="customtipweekly(\'showtooltip\',this,'.$Dutyid.')" onmouseleave="customtipweekly(\'hidetooltip\',\'\','.$Dutyid.')">';
							}
					          echo '<font color="'. $fontColor.'">';
                        } else {
                            if (is_array($value[$intWeekNumber][$i]["Duty"])) {
                                if (sizeof($value[$intWeekNumber][$i]["Duty"])>1) {
                                    $dutyKeys = array_keys($value[$intWeekNumber][$i]["Duty"]);
                                    sort($dutyKeys);
                                    if (isset($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]) && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]>0)) {
                                        $CellClass = 'DutyCellWorking';
                                    } else {
                                        $CellClass = 'DutyCellNotWorking';
                                    }
                                    $backColor = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["BackColour"] ?? '#cccccc';
                                    $fontColor = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["FontColour"] ?? '#000000';
                                    $Dutyid = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Dutyid"] ?? 0;
									$AllocationID = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["AllocationID"] ?? 0;
									$tDutyName = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duty"];
                                    $dutyLabels = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]['DutyLabels'];
									if (($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["StartTime"]=='00:00') && ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["EndTime"]=='00:00')) {
										$tDutyTime = ($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"]==0)?'':number_format((float)($value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["Duration"] / 3600), 2, '.', '');
									} else {
										$tDutyTime = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["StartTime"].'-'.$value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["EndTime"];
									}
									$tDutyTeam = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["schedulingTeamName"];
									$tDutyLeaveType = $value[$intWeekNumber][$i]["Duty"][$dutyKeys[0]]["LeaveType"];
								} else {
                                    foreach ($value[$intWeekNumber][$i]["Duty"] as $dutyData) {
                                        $hascomments = 0;
										$tDutyName='';
                                        if ($dutyData["Duration"]>0) {
                                            $CellClass = 'DutyCellWorking';
                                            $backColor = $dutyData["BackColour"];
                                        } else {
                                            $CellClass = 'DutyCellNotWorking';
                                            $backColor = $dutyData["BackColour"];
                                        }
                                        $fontColor = $dutyData["FontColour"];
                                        $Dutyid = $dutyData["Dutyid"];
										$AllocationID = $dutyData["AllocationID"];
										$tDutyName = $dutyData["Duty"];
                                        $dutyLabels = $dutyData['DutyLabels'];
										if (($dutyData["StartTime"]=='00:00') && ($dutyData["EndTime"]=='00:00'))
										{
												$tDutyTime = ($dutyData["Duration"]==0)?'':number_format((float)($dutyData['Duration'] / 3600), 2, '.', '');
											} else {
												$tDutyTime = $dutyData["StartTime"].'-'.$dutyData["EndTime"];
											}
										$tDutyTeam = $dutyData["schedulingTeamName"];
										$tDutyLeaveType = $dutyData["LeaveType"];
								    }

                                }
                            } else {
                                $backColor = $value[$intWeekNumber][$i]["BackColour"];
                                $fontColor = $value[$intWeekNumber][$i]["FontColour"];
                                $CellClass = $value[$intWeekNumber][$i]["CellClass"] ?? '';
                            }
                            if((strtolower((string) $tDutyName) == 'leave' || strtolower((string) $tDutyName) == 'off leave'|| strtolower((string) $tDutyName) == 'u-sick'|| strtolower((string) $tDutyName) == 'sick'|| strtolower((string) $tDutyName) == '-sick') || ($tDutyName=="-") || ($tDutyName=="--") || ($tDutyName=="") || ($tDutyName=="U")) {
									 echo '<div style="background-color:'.$backColor.'; width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px; overflow:hidden;" class="'.$CellClass.' DutyCellNotWorking   handcursor scheduledPersonDuty" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '" >';
							} else {
							$dutyqtipcontentw=$tDutyName;
							$dutyqtipcontentw .=($tDutyTime=='')?'':"<br>". $tDutyTime;
							$dutyqtipcontentw .="<br>".$tDutyTeam;
                            echo '<div style="background-color:'.$backColor.'; width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px; overflow:hidden;" class="'.$CellClass.'  handcursor scheduledPersonDuty" data-duty-labels="' . implode(',', $dutyLabels) . '" data-duty-name="' . $tDutyName . '" id ='.$Dutyid.' data-qtip-content="' . $dutyqtipcontentw . '" onmouseover="customtipweekly(\'showtooltip\',this,'.$Dutyid.')" onmouseleave="customtipweekly(\'hidetooltip\',\'\','.$Dutyid.')">';
							}
						    echo '<font color="'. $fontColor.'">';
                        }
                //Color Week Close here

                if (is_array($value[$intWeekNumber][$i]["Duty"])) {
                    if (sizeof($value[$intWeekNumber][$i]["Duty"])>1) {
						//$dutyqtipcontent='';
                        foreach ($value[$intWeekNumber][$i]["Duty"] as $key => $DutyData) {
						    if ($key==0) {
                                if (isset($DutyData['display_priority']) && ($DutyData['display_priority'] == 2) && ($strMaskClass == 'daynotfixed' || $strMaskClass == 'daynotfixed allocations-hideday-menu'))  {
                                    if((strtolower((string) $DutyData['LeaveType']) == 'leave' || strtolower((string) $DutyData['LeaveType']) == 'off leave')) {
                                        echo '<font color="#009900"><b>'.$DutyData['LeaveType'].'</b></font><br> Was: '.$DutyData['Duty'];
                                    } else {
                                        if($intIgnoreRota == 0){
                                            echo '<i>'.$DutyData['Duty'].'</i>' ?? '';
                                        } else {
                                            echo $DutyData['Duty'] ?? '';
                                        }
                                    }
                                }else if(isset($DutyData['display_priority']) && ($DutyData['display_priority'] == 2) && (strtolower((string) $DutyData['LeaveType']) == 'leave' || strtolower((string) $DutyData['LeaveType']) == 'off leave')) {
                                    echo '<font color="#009900"><b>'.$DutyData['LeaveType'].'</b></font><br> Was: '.$DutyData['Duty'];
                                } else {
                                    if(!empty($getTeamColorWeek) && isset($getTeamColorWeek['colourWeek']) && $getTeamColorWeek['colourWeek'] == 1){
                                        echo '<i>'.$DutyData['Duty'] ?? ''.'</i>';
                                    } else {
                                        if($intIgnoreRota == 0){
                                            echo '<i>'.$DutyData['Duty'].'</i>' ?? '';
                                        } else {
                                            echo $DutyData['Duty'] ?? '';
                                        }
                                    }
                                }

                                if (isset($DutyData['IsTemplate']) && ($DutyData['IsTemplate'] != 0) && ($DutyData['Duration']>0) && ($DutyData['isEditable']==1) && empty($DutyData['LeaveType'])) {
                                    $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                                    echo "<br>".$intendHour ." Hours";
                                } else {
                                        if(isset($DutyData["Duty"]) && (strtolower((string) $DutyData["Duty"])!='sick') && (strtolower((string) $DutyData["Duty"])!='u-sick') && (strtolower((string) $DutyData["Duty"])!='-sick')){
                                            if((($DutyData["StartTime"] < $DutyData["EndTime"])|| ($DutyData["StartTime"] > $DutyData["EndTime"])) && !empty($DutyData["StartTime"]) && empty($DutyData['LeaveType'])) {
                                                echo "<br>".$DutyData["StartTime"].'-'.$DutyData["EndTime"];
                                            } else {
                                                if ($DutyData['Duration']>0 && empty($DutyData['LeaveType'])) {
                                                    $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                                                    echo "<br>".$intendHour ." Hours";
                                                }
                                            }
                                        }
                                        // Only Do SignIn if there is a start

								}
                            } //Check to Sghow only One Master Duty from Rota
				        } //Foreach close

                    } else
                    {

                        foreach ($value[$intWeekNumber][$i]["Duty"] as $DutyData) {
					        if(($DutyData['display_priority'] == 2) && ($strMaskClass == 'daynotfixed' || $strMaskClass == 'daynotfixed allocations-hideday-menu'))  {
                                if((strtolower((string) $DutyData['LeaveType']) == 'leave' || strtolower((string) $DutyData['LeaveType']) == 'off leave')){
                                    echo '<font color="#009900"><b>'.$DutyData['LeaveType'].'</b></font><br> Was: '.$DutyData['Duty'];
                                }else {
                                    if(!empty($getTeamColorWeek) && isset($getTeamColorWeek['colourWeek']) && $getTeamColorWeek['colourWeek'] == 1){
                                        echo '<i>'.$DutyData['Duty'] ?? ''.'</i>';
                                    } else {
                                        if($intIgnoreRota == 0){
                                            echo '<i>'.$DutyData['Duty'].'</i>' ?? '';
                                        } else {
                                            echo $DutyData['Duty'] ?? '';
                                        }
                                    }
                                }
                            }
                            else if(($DutyData['display_priority'] == 2) && (strtolower((string) $DutyData['LeaveType']) == 'leave' || strtolower((string) $DutyData['LeaveType']) == 'off leave')) {
                                echo '<font color="#009900"><b>'.$DutyData['LeaveType'].'</b></font><br> Was: '.$DutyData['Duty'];
                            } else {
                                if(!empty($getTeamColorWeek) && isset($getTeamColorWeek['colourWeek']) && $getTeamColorWeek['colourWeek'] == 1){
                                    echo '<i>'.$DutyData['Duty'] ?? ''.'</i>';
                                } else {
                                    if($intIgnoreRota == 0){
                                        echo '<i>'.$DutyData['Duty'].'</i>' ?? '';
                                    } else {
                                        echo $DutyData['Duty'] ?? '';
                                    }
                                }
                            }

                            if(($DutyData['IsTemplate'] != 0) && ($DutyData['Duration']>0) && ($DutyData['isEditable']==1)) {
                                if(empty($DutyData['LeaveType'])) {
                                $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                                    echo "<br>".$intendHour ." Hours";
                                }
                            } else {
                                if((strtolower((string) $DutyData["Duty"])!='sick') && (strtolower((string) $DutyData["Duty"])!='u-sick') && (strtolower((string) $DutyData["Duty"])!='-sick')){
								    if(($DutyData["StartTime"] < $DutyData["EndTime"]) || ($DutyData["StartTime"] > $DutyData["EndTime"]) && ($DutyData['LeaveType']=='')) {
										if(($DutyData['pdlStartTime'] != 0) || ($DutyData['pdlEndTime'] != 0)){
                                            $dispPDLEndTime = $DutyData['pdlEndTime'];
                                            if($DutyData['pdlStartTime'] > $DutyData['pdlEndTime']){
                                                $dispPDLEndTime = (86400 + $DutyData['pdlEndTime']);
                                            }
                                            $pdlTitle = 'PDL: '.$service->convertSecondsIntoTime($DutyData['pdlStartTime'],':','No').'-'.$service->convertSecondsIntoTime($DutyData['pdlEndTime'],':','No').'&nbsp;|&nbsp;'.$service->convertSecondsIntoTime($dispPDLEndTime-$DutyData['pdlStartTime'],'.','Yes');

                                            echo '<div style="width: 100px; white-space:nowrap; display: -webkit-box;"><div>'.$DutyData["StartTime"].'-'.$DutyData["EndTime"].'</div><div style="color:#109146; padding-left:4px;" title="'.$pdlTitle.'">[L:'.$service->convertSecondsIntoTime($DutyData['pdlStartTime'],':','No').'-'.$service->convertSecondsIntoTime($DutyData['pdlEndTime'],':','No').']</div></div>';
                                        } else {
                                            echo "<br>".$DutyData["StartTime"].'-'.$DutyData["EndTime"];
                                        }
                                    } else {
                                        if ($DutyData['Duration'] > 0 && ($DutyData['LeaveType']=='')) {
                                            $intendHour = number_format((float)($DutyData['Duration'] / 3600), 2, '.', '');
                                            echo "<br>".$intendHour ." Hours";
                                        }
                                    }
                                }

                            } //OnlyApply on Master Duty

                        } //Foreach Close

                    } //Else Close for Single Entry Check
                } else {
                    //echo str_replace('--', 'U', $value[$intWeekNumber][$i]["Duty"]);
					 echo str_replace('-', 'U', $value[$intWeekNumber][$i]["Duty"]);
                }
                echo '</font>';
                          // Show the comments?
                if (($hascomments == 1)) {
                    $scTeamId = $value[$intWeekNumber][$i]["Duty"][0]["TeamID"];
                    echo '<div class="DutyCellBottomRight handcursor tipremotecomments" teamid= "'.$scTeamId.'"  dutyId= "'.$Dutyid.'" DutyDate="'.$currdate.'" SchedulingPersonID="'.$sn.'" style="width:10%!important;z-index:1 !important"  >';
                    echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
                    echo '</div>';
                }
                if ($hasmanualOT == 1) {
                    $scTeamId = $value[$intWeekNumber][$i]["Duty"][0]["TeamID"];
                    echo '<div class="DutyCellBottomRight   handcursor " teamid= "'.$scTeamId.'"  dutyId= "'.$Dutyid.'" DutyDate="'.$currdate.'" SchedulingPersonID="'.$sn.'" style="right: 36px;z-index:1 !important;width:10%">';
                    echo '<img class="tipremote" schPersonId="'.$sn.'" dutydate="'.$currdate.'" teamId ="'.$scTeamId.'" border="0" src="images/money.png" width="12px" height="12px">';
                    echo '</div>';
                  }
            // Show the Locks....?
            if (isset($arrRequests[$value["SchedulingPersonID"]][$i])) {
                if ($arrRequests[$value["SchedulingPersonID"]][$i]['IsLock'] == 1) {
                    $intFirstLock = 1;
                    $strTitle = 'Day Is Locked<br>'.$arrRequests[$value["SchedulingPersonID"]][$i]['TipText'];
                    $strImage = 'locked';
                } else {
                    $strTitle = $arrRequests[$value["SchedulingPersonID"]][$i]['Description'];
                        if ($arrRequests[$value["SchedulingPersonID"]][$i]['Approved'] == 1) {
                            if ($intFirstLock == 0 && $arrRequests[$value["SchedulingPersonID"]][$i]['AffectLocks'] == 1) {
                                 $strTitle.= '<br>This is the Lock for this Week<br>Approved';
                                 $strImage = 'locked';
                                } else {
                                $strTitle.= '<br>Approved';
                                $strImage = 'requested';
                                }
                            $intFirstLock = 1;
                            } else {
                                      if ($arrRequests[$value["SchedulingPersonID"]][$i]['Approved'] == 1) {
                                          $strTitle.= '<br>OK - Not yet Approved';
                                          $strImage = 'requested';
                                      } else {
                                          $strTitle.= '<br>Waiting List - Not yet Approved';
                                          $strImage = 'requestedInQ';
                                      }
                                  }

                    }
                             echo '<div class="DutyCellBottomCentre">';
                            echo '<img title="'.$strTitle.'" width="15" height="15" border="0" src="images/locks/'.$strImage.'.png"></img>';
                            echo '</div>';
                          }
                          // Edit EDP here
                          echo '</div>';
                      }
                      else {
                          echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="DutyCellNotWorking handcursor">';

                          echo '</div>';
                      }
                  }
              }
              $counter++;
              echo "</div>";
             }
                echo '</div>';
                echo '</div>';

            } else {
                echo '<br>';
                echo '<div class="tableheadersmall bigtextboldcentre" style="width:'.$intTableWidth.'px">';
                echo '<br><br>';
                echo 'Unable to display The Allocations for this week<br>';
                if ($intShowCanDo == 1)  {
                    echo 'You are viewing Shifts To Check and it may be that all shifts are covered without any conflicts.<br>';
                }

                if ($arrCurrentFilter[0] != -1) {
                    echo 'You are have a filter set and it may be that no one matches the criteria.<br>';
                }

                echo '<br></br><br>';
                echo '</div>';
            }

    echo '<br><br><br><br>';
    echo '<div id="dialog-edp-offer" title="Information!" style="display:none;">';
    echo '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can delete this offer<br>or add comments to it.</p>';
    echo '</div>';
?>
    <div id="dialog-no-filter" title="Information!" style="display:none;">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You have a filter set!<br>Because the weekly view is restricted the filter has been removed for this week.<br>Do you want to keep the filter in place for viewing unrestricted weeks or clear it?</p>
    </div>

    <script language="JavaScript" type="text/javascript">
        <?php
        if (isset($_SESSION['showdailycomments'])) {
            echo "$('.comments').toggle();";
        }

        ?>
        function allocationViewByTeam(teamId,WeekNumber,mastMiscId){
	    	$('#loading').show();

			var myKeyVals = {'WeekNumber' : WeekNumber, 'teamId' : teamId,'actionname' :'checkrotaweekexists'};
			$.ajax({
				type: 'POST',
				url: 'page-includes/allocations/weekly/actions/check-week.php',
				data: myKeyVals,
				success: function (returnValue) {
					returnValue = $.parseJSON(returnValue);
					let argrequestval = <?php echo json_encode($requestval); ?>;
					var requestval1 = $.parseJSON(argrequestval);
					let selFilterId =  $('#selFilterId').val();
					let selFilterType =  $('#selFilterType').val();
					if (returnValue.isweekexist==""){
						let Weekno=WeekNumber;
						let Weekval= Weekno.substring(4,6);
						let yearval= Weekno.substring(0,4);
						WeekNumbernew=Weekval+"/"+yearval;
						let finalargweeknumber= Weekno;
						let argstartDate =  $('#argstartDate').val();
						let argendDate =  $('#argendDate').val();
						let argendWeek =  $('#argendWeek').val(); 	
						let rotaData =  '<?php echo $rotaData; ?>';	
						let finalargendweeknumber
						requestval1.startDate = argstartDate;
						requestval1.endDate = argendDate;
						requestval1.startWeekDate = argstartDate;
						requestval1.endWeekDate = argendDate;
						requestval1.startWeek = WeekNumber;
						ShowAllocationsRotaInEditWeekly(teamId,WeekNumbernew,finalargweeknumber,argstartDate,argendDate,argendWeek,finalargendweeknumber,requestval1,selFilterId,selFilterType,mastMiscId, rotaData);
					} else {
						let Weekno=WeekNumber;
						let Weekval= Weekno.substring(4,6);
						let yearval= Weekno.substring(0,4);
						WeekNumbernew=Weekval+"/"+yearval;
						let argDataInitialLoad = {};
						argDataInitialLoad.schedulingTeamId = teamId;
						argDataInitialLoad.userId = <?php echo $userId;?>;
						argDataInitialLoad.weekNumber = WeekNumbernew;
						argDataInitialLoad.teamId = teamId;
						argDataInitialLoad.selAutoPageFilterId = selFilterId;
						argDataInitialLoad.shiftCountingCheckBox = requestval1.shiftCountingCheckBox;
						argDataInitialLoad.shiftCountingFilterId = requestval1.selShiftCountingFilterId;
						argDataInitialLoad.mastMiscFilterId = requestval1.mastMiscFilterId;
						editWeeklyPageLoad('No',argDataInitialLoad, 'rotaInEditWeekly');
					}
				},
				 error:function (returnValue) {
					alert('some error found in menu script.');
				 }

			});
			 $('#loading').hide();
        }

        $(function() {
			 $("#shiftCountingCheckBox").val('hide');
			<?php if($validDateNo > strtotime($dteStartDate) && ($dteStartDate!='')){
				$bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim(date("Y-m-d"));
				$_SESSION["allocations"]["WeekNumber"] = $bbcweeknumberArray['ixYearWeek'] ?? 0;
				?>
				customAlert("Allocate cannot open a Week older than seven years. Please select another Week.");
				 ShowAllocations('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
			<?php } ?>
            $('#btnAdd').click(function() {
                $('.comments').toggle();
                $.post("page-includes/ajax-calls/allocations-toggle-comments.php", {
                })
            });

        });
        $(document).ready(function() {
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
            $(document).click(function (event) {
                if(!$(event.target).closest('#accordion').length) {//if you clicked outside of the accordion
                    $("#accordion").accordion({active: false});//collapse all the panels
                }
            });

            // If cookie is set, scroll to the position saved in the cookie.
            if ( $.cookie("vscroll") !== null ) {
                $("#weeklynames").scrollTop( $.cookie("W-vscroll") );
                $("#weeklyduties").scrollTop( $.cookie("W-vscroll") );
                $("#weeklyduties").scrollLeft( $.cookie("W-hscroll") );
            }
            // When scrolling happens....
            $("#weeklyduties").on("scroll", function() {
                // Set a cookie that holds the scroll position.
                $.cookie("W-vscroll", $("#weeklyduties").scrollTop() );
                $.cookie("W-hscroll", $("#weeklyduties").scrollLeft() );
                $('#weeklynames').scrollTop($(this).scrollTop());
                $('#weeklytop').scrollLeft($(this).scrollLeft());
            });
            $('[title]').qtip({
                position: {
                    at: 'bottom left',
                    my: 'right center',
                    effect: false,
                    viewport: $(window),
                    adjust: {
                        method: 'none shift'
                    }
                },
                style: { classes: 'qtip-rounded qtip-shadow qtip-light'}
            });

            $('.tipremotecomments').each(function() {
                $(this).qtip({
                    content: {
                        text: function(event, api) {
                            var reqData = {
                            'dutyDate' : api.elements.target.attr('DutyDate'),
                            'schPersonId' : api.elements.target.attr('SchedulingPersonID'),
                            'teamId' : api.elements.target.attr('teamid'),
                            'dutyId' : api.elements.target.attr('dutyId'),
                            'rolepermission' : 0
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

            $('.signedtip').each(function() {
                $(this).qtip({
                    content: {
                        text: function(event, api) {
                            $.ajax({
                                url: 'page-includes/allocations/allocations-signedin-info.php',
                                type: 'POST',
                                data: {
                                    dutyId: api.elements.target.attr('dutyid'),
                                    rolePermission: 0
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
            $('.tipremoteedp').each(function() {
                $(this).qtip({
                    content: {
                        text: function(event, api) {
                            $.ajax({
                                url: 'page-includes/allocations/allocations-edp-info.php',
                                type: 'POST',
                                data: {schedulingPersonId: api.elements.target.attr('SchedulingPersonID'),
                                    ddate: api.elements.target.attr('DutyDate'),
                                    teamId: api.elements.target.attr('teamid')
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
            $('.tipremote').each(function() {
            $(this).qtip({
            content: {
            text: function(event, api) {
            $.ajax({
            url: 'page-includes/allocations/allocations-miscinfo.php',
            type: 'POST',
            data: {
              schPersonId: api.elements.target.attr('schPersonId'),
              dutydate: api.elements.target.attr('dutydate'),
              teamId: api.elements.target.attr('teamId')
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
        });

     <?php
        if (isset($arrAllocations) && count($arrAllocations) > 0) {
        ?>
        ResizeWeeklyGrids();
        $('#weeklynames').on('scroll', function () {
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
        $('#FormDutyName').validate({
            rules:{
                "filter":{
                    required:true,
                },
            },
            submitHandler: function(form) {
                $.ajax({type:'POST', url: 'page-includes/allocations/allocations-setfilter.php', data:$('#FormDutyName').serialize(), success: function(data) {
                        ShowAllocations('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
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
                        ShowAllocations('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
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
                        ShowAllocations('<?php echo $intTeamID?>',<?php echo $intWeekNumber?>);
                    }});
            }
        })


        function ResizeWeeklyGrids() {
            //var offset = ($("#weeklytop").offset().top) + ($("#content").offset().top);
            var windowwidth = $(window).width() - 24 - <?php echo $intNamesWidth?>;
            if (windowwidth > <?php echo ($intDutyWidth * 7)?>) {
                windowwidth = <?php echo $intDutyWidth?> * 7;
            }
            var widowheight = $(window).height() - 186;
            $("#weeklyduties").width(windowwidth + 20).height(widowheight);
            $("#weeklynames").height(widowheight);
            $("#weeklytop").width(windowwidth);
        }



        $(function() {
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
                defaultDate: "<?php echo $dteStartDate?>" ,
                onSelect: function (dateText, inst) {
                    var currentdate = dateText;
						$.post("page-includes/allocations/allocations-set-week-from-date.php", {
                            date: currentdate
                        },
                        function(data,status){{
								allocationViewByTeam(<?php echo $intTeamID?>,data)
                        }});
                }
            });
        });

        function WeeklySignInToDay(date, action, SchedulingPersonID, team, dutyId) {
           $( "#dialog-sign-in" ).dialog(
                {
                    width:600,
                    open: function() {
                        $(this).siblings('.ui-dialog-buttonpane').find('button:eq(1)').focus();
                    },
                    buttons: {
                        "Sign-In / Un-Sign In": function() {
                            $('*').qtip('hide');
                            $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
                                    sdate: date,
                                    SchedulingPersonID: SchedulingPersonID,
                                    action: action,
                                    teamId: team,
                                    dutyId: dutyId,
                                    screenType: '1'
                                },
                                function(data,status){
                                    {
                                        ShowAllocations(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
                                    }
                                });
                            $( this ).dialog( "close" );
                        },
                        "Mark As In Building": function() {
                            $('*').qtip('hide');
                            $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
                                    sdate: date,
                                    SchedulingPersonID: SchedulingPersonID,
                                    action: 2,
                                    teamId: team,
                                    dutyId: dutyId,
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
                                            ShowAllocations(<?php echo $intTeamID?>,<?php echo $intWeekNumber?>)
                                        }

                                    }
                                });
                            $( this ).dialog( "close" );
                        },
                        "Cancel": function() {
                            $( this ).dialog( "close" );
                        }
                    }
                }
            );
        }



    </script>
    <?php
}
?>
<script type="text/javascript">
<?php if(empty($teamOptions)) { ?>
    customAlertByModel('Your session has been interrupted. Please reload the week.');
<?php }?>

function customtipweekly(type='',currObj, Dutyid){
	$('#loading').hide();
	if(type == 'showtooltip'){
	 let message = $('#'+Dutyid).attr('data-qtip-content');
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

 $(document).on('click', '#createWeekRota', function(e) {
	    e.preventDefault();
        e.stopImmediatePropagation();
        $('#loadingWeeklyRota').show();
		var argrequestval = <?php echo json_encode($requestval); ?>;
		let WeekNumber =  $('#intWeekNumber').val();
		let argstartDate =  $('#argstartDate').val();
		let argendDate =  $('#argendDate').val();
		let argendWeek =  $('#argendWeek').val();
		let argweeknumber =  $('#argweeknumber').val();
		let argteamId =  $('#teamId').val();
		var requestval1 = $.parseJSON(argrequestval);
		let selFilterId =  $('#selFilterId').val();
		requestval1.searchTeamId = argteamId;
		requestval1.teamId= argteamId;
		requestval1.startDate = argstartDate;
		requestval1.endDate = argendDate;
	    requestval1.startWeekDate = argstartDate;
		requestval1.endWeekDate = argendDate;
		requestval1.startWeek = WeekNumber;
		requestval1.weekNumber = argweeknumber;
		requestval1.endWeek = argendWeek;
		requestval1.shiftCountingCheckBox = 'hide';
		requestval1.ajaxLoadParams.schedulingTeamId= argteamId;
	    $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/create-week.php",
            data: requestval1,
            beforeSend: function(){
                $('#createWeekRota').attr('disabled', 'disabled');
                $('#loading').show();
            },
            success: function (response) {
			    response = $.parseJSON(response);
				if (response.success) {
					$.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/period-view.php",
                        data: requestval1,
                        success: function (data) {
                            $('#loadingWeeklyRota').hide();
							$('#content').html(data);
                            if(localStorage.getItem('EditWeeklyNameFilter') != '' && localStorage.getItem('EditWeeklyNameFilter') != null){
                                applyViewFilter('EditWeeklyNameFilter',selFilterId,'Yes');
                            }
                            if((parseInt(selFilterId) > 0) || ((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null))){
                                applyViewFilter('EditWeekly',selFilterId,'Yes');
                            }
                            if(parseInt(requestval1.selShiftCountingFilterId) > 0){
                                applyDutyShiftCountingFilter(requestval1.userId, requestval1.schedulingTeamId, requestval1.selShiftCountingFilterId);
                            }
                        }
                    });
                }
                $('#facebox .close').click();
            }
        });
		$('#loading').hide();
    })

$(document).on('click', '#viewAdhocDutiesRota', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
		$('#loading').show();
		var argrequestval = <?php echo json_encode($requestval); ?>;
		let WeekNumber =  $('#intWeekNumber').val();
		let argstartDate =  $('#argstartDate').val();
		let argendDate =  $('#argendDate').val();
		let argendWeek =  $('#argendWeek').val();
		let argweeknumber =  $('#argweeknumber').val();
		let argteamId =  $('#teamId').val();
		var requestval1 = $.parseJSON(argrequestval);
		requestval1.searchTeamId = argteamId;
		requestval1.teamId= argteamId;
		requestval1.startDate = argstartDate;
		requestval1.endDate = argendDate;
	    requestval1.startWeekDate = argstartDate;
		requestval1.endWeekDate = argendDate;
		requestval1.startWeek = WeekNumber;
		requestval1.weekNumber = argweeknumber;
		requestval1.endWeek = argendWeek;
		requestval1.shiftCountingCheckBox = 'hide';
		requestval1.ajaxLoadParams.schedulingTeamId= argteamId;
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/view-adhocduty.php",
            data: requestval1,
            success: function (data) {
                $('#content').html(data);
                viewAdhocDutyHeaderSettings();
            }
        });
        $('#facebox .close').click();
		$('#loading').hide();
    });

    /*
    Function used for filter data
    */
    function applyEditRotaWeeklyFilter() {
        if((localStorage.getItem('EditweeklyRotascreen_filter') == '') && (localStorage.getItem('EditweeklyRotascreen_filter') == null)){
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
                let top = <?php echo $intRowHeight ?> * totalVisible;
                $(this).css("top", top);
                $('.scheduledPerson_duties_' + $(this).attr('data-id') + ' .scheduledPersonDuty').css("top", top);
                totalVisible++;
            }
        });
        $('#loading').hide();
    }
</script>