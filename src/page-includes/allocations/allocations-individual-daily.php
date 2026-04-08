<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
include_once '../../page-includes/users/process/classUserSetup.php';
use App\Models\User\RefRole;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};

$commonObj = new classCommonDBFunctions();
$service = new AllocationService();
$setupObj = new classUserSetup();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

if (isset($_REQUEST["schedulingPersonId"])) {
    $schedulingPersonId = $_REQUEST["schedulingPersonId"];

} else {

    $schedulingPersonId = GetScheduledPersonIdbyUserId($UserID);
}

$arrDepDefaults = GetTeamDefaults();

$arrUser = GetScheduledPersonTeamDetails($schedulingPersonId);

if (isset($_REQUEST["teamId"])) {
    $intSchedulingTeamId = $_REQUEST["teamId"];
} else {
    $intSchedulingTeamId = 0;
    foreach ($arrUser as $intDepID => $arrUserDep) {
        if ($arrUserDep['IsHomeTeam'] == 1) {
            $intSchedulingTeamId = $intDepID;
            break;
        }
    }
    if ($intSchedulingTeamId == 0) {
        $intSchedulingTeamId = key($arrUser);
    }
}

foreach ($arrUser as $intTeamID => $arrSubUser) {
    if ($intTeamID != 'homeTeamId') {
        $arrFullName[] = isset($arrSubUser['FullName']) ? $arrSubUser['FullName'] : '';
    }
}
if (isset($arrFullName) && !empty($arrFullName) && is_array($arrFullName)) {
    $strFullName = $arrFullName[0];
}
if (isset($_REQUEST['date'])) {
    $strCurrentDate = date("Y-m-d", strtotime($_REQUEST['date']));
} else {
    if (isset($_SESSION['allocattionsdate'])) {
        $strCurrentDate = $_SESSION['allocattionsdate'];
    } else {
        $strCurrentDate = date("Y-m-d");
    }
}
$_SESSION['allocattionsdate'] = $strCurrentDate;

//Permissions
$isScheduler = $userRoleTrait->checkSchedulingTeamRoleExists($intSchedulingTeamId, RefRole::SCHEDULER);
$isManager = $userRoleTrait->checkSchedulingTeamRoleExists($intSchedulingTeamId, RefRole::MANAGER);
$isTeamAdmin = $userRoleTrait->checkSchedulingTeamRoleExists($intSchedulingTeamId, RefRole::SCHEDULING_TEAM_ADMIN);
$hasShiftleaderRole = $userRoleTrait->checkSchedulingTeamRoleExists($intSchedulingTeamId, RefRole::SHIFT_LEADER);
$isTeamLeader = $userRoleTrait->checkSchedulingTeamRoleExists($intSchedulingTeamId, RefRole::TEAM_LEADER);
$isSchedulingTeamViewer = $userRoleTrait->checkSchedulingTeamRoleExists($intSchedulingTeamId, RefRole::SCHEDULING_TEAM_VIEWER);
$isScheduledPerson = $userRoleTrait->checkSchedulingTeamRoleExists($intSchedulingTeamId, RefRole::SCHEDULED_PERSON);
$isAdmin = $userRoleTrait->isSystemAdmin();
//If system admin or area admin the person have STA permission
if($isTeamAdmin == 0) {
  $isTeamAdmin = $userRoleTrait->checkEditWeeeklyAdminRole($intSchedulingTeamId);
}

$arrStaffInDepartment = GetStaffDetailsByTeamId($intSchedulingTeamId);
if (!array_key_exists($schedulingPersonId, $arrStaffInDepartment)) {
    $arrStaffInDepartment[$schedulingPersonId] = $strFullName;
}
asort($arrStaffInDepartment);
$yesterday = date("Y-m-d", strtotime("-1 day", (strtotime($strCurrentDate))));
$tomorrow = date("Y-m-d", strtotime("+1 day", (strtotime($strCurrentDate))));

$getWeekandDayArr = GetAllocationWeekandDay($strCurrentDate);
$week = $getWeekandDayArr['ixYearWeek'];
$day = $getWeekandDayArr['ixDayInWeek'];

$intIsFreelance = $commonObj->GetIsFreelencerByNetlogin($strUser);
if($setupObj->isOtherBBCUser()) {
    $intIsFreelance = 1;
}
$arrDepDefaults = GetTeamDefaults(0, $intSchedulingTeamId);

$intMaskDays = $arrDepDefaults[$intSchedulingTeamId]['MaskAfter'];
// When do we restrict the text from?
$maskfrom = date("Y-m-d", strtotime("+" . $intMaskDays . " days"));
$intFreelanceMaskDays = $arrDepDefaults[$intSchedulingTeamId]['FreelancerMaskingDays'];

$arrHiddenDays = ReadHiddenDays($intSchedulingTeamId, $strCurrentDate, $strCurrentDate);

$intDontShowDay = 0;

/* Set shiftleader flag for rotas --START */
$isShiftleader = 1;

if (($isScheduler== 1) || ($isTeamAdmin == 1) || ($isManager == 1)) {
    $isShiftleader = 0;
}
/* Set shiftleader flag for rotas --END */

if ($intIsFreelance == 1) {
    $dteFirstDate = date("Y-m-d", strtotime("-1 Day"));
    $dteLastDate = date("Y-m-d", strtotime("+$intFreelanceMaskDays Days"));
    if (!($strCurrentDate >= $dteFirstDate && $strCurrentDate <= $dteLastDate)) {
        $intDontShowDay = 1;
    }
}

if ($intDontShowDay == 0) {

    $arrallocations = ReadIndividualAllocationsDay($week, $day, $schedulingPersonId, $isShiftleader);

    $earlieststart = floor(($arrallocations['earlieststart'] / 3600));
    if ($arrallocations['earlieststart'] <= $arrallocations['lateststart']) {
        $lateststart = round($arrallocations['lateststart'] / 3600);
    } else {
        $lateststart = round(($arrallocations['lateststart'] + 86400) / 3600);
    }

    if ($earlieststart == 24) {
        $earlieststart = 8;
        $lateststart = 18;
    } else {
        $earlieststart = $earlieststart - 1;
        $lateststart = $lateststart + 1;
    }
}

// ******************************************************************* Now write the days and dates in the fixed div
echo '<div id="maindata">';
echo '<h1 class="sr-only">Daily Allocations</h1>';
echo '<table class="tablesmallnoborder" width="100%">';
echo '<tr>';
if ($intIsFreelance == 0) {
    echo '<td nowrap class="medtextbold lightcell" width="550px">Showing:&nbsp;&nbsp;';
    echo '<select class="chosen-select" name="department" onchange="javascript:ShowDailyRota(\'' . $strCurrentDate . '\',value,' . $intSchedulingTeamId . ')";>';
    foreach ($arrStaffInDepartment as $strSN => $strName) {
        if ($schedulingPersonId == $strSN) {
            echo '<option selected value="' . $strSN . '">' . $strName . '</option>';
        } else {
            echo '<option value="' . $strSN . '">' . $strName . '</option>';
        }
    }
    echo '</select>';
    echo '</td>';
}
echo '<td width="150px" class="lightcell medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowDailyRota("' . $yesterday . '","' . $schedulingPersonId . '",' . $intSchedulingTeamId . ')\';>&nbsp;&lt;&lt; ' . date("jS M Y", strtotime($yesterday)) . '</span></td>';
echo '<td width="150px" class="lightcell medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowDailyRota("' . $tomorrow . '","' . $schedulingPersonId . '",' . $intSchedulingTeamId . ')\';>' . date("jS M Y", strtotime($tomorrow)) . '&nbsp;&gt;&gt;</span></td>';
echo '<td width="150px" align="center" class="lightcell medtextbold handcursor" onclick=\'javascript:ShowRota("' . $strCurrentDate . '","' . $schedulingPersonId . '",' . $intSchedulingTeamId . ')\';>Show Monthly<br>Allocations</td>';

echo '<td width="150px" align="center" class="lightcell medtextbold handcursor" onclick=\'javascript:ShowAllocations(' . $intSchedulingTeamId . ',' . $week . ')\';>Show Allocations<br>for this Week</td>';

if (
    isset($arrallocations['Allocations']) 
    && is_array($arrallocations['Allocations'])
    && array_key_exists($intSchedulingTeamId, $arrallocations['Allocations'])
    && isset($arrallocations['Allocations'][$intSchedulingTeamId]['IsHomeTeam'])
    && $arrallocations['Allocations'][$intSchedulingTeamId]['IsHomeTeam'] == 1
) {
    echo '<td width="150px" align="center" class="lightcell medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowDailyAllocations(' . $intSchedulingTeamId . ',"' . $strCurrentDate . '")\';>Show Allocations<br>for this Day</span></td>';
}
echo '<td width="150px" align="right" class="lightcell medtextbold handcursor">Choose a Date</td>';
echo '<td class="lightcell medtextbold handcursor"><input type="hidden" id="datepicker"></td>';
echo '</tr>';
echo '</table><br>';
if (!empty($arrHiddenDays)) {
    echo '<br><div class="tableheadersmall bigtextboldcentre">';
    echo "<br>Hidden Days<br><br>";
    echo '</div>';
    die;
}

if (!isset($arrallocations['Allocations'])) {
    echo '<br><div class="tableheadersmall bigtextboldcentre">';
    echo "<br>There is no Allocation for this Day.<br><br>";
    echo '</div>';
    die;
}

$intteamCount = count($arrallocations['Allocations']);
echo '<div class="tableheadersmall medtextboldcentre" style="width:80%; margin:0 auto; position:relative;">';
echo '<br>Daily Allocation for ' . $arrallocations['FullName'] . '<br>';
echo '<span>' . date("jS F Y (l)", strtotime($strCurrentDate));
echo '&nbsp; &nbsp; <span style="text-align: center;position:absolute;" class="date-comment" data-date-comment-date="' . $strCurrentDate . '" data-date-comment-team-id="' . $intSchedulingTeamId . '">';
echo '<span class="date-commment-info-icon" style="cursor: pointer"></span>';   
echo '</span></span><br>';
echo 'Week ' . spinweek($week) . '<br><br>';
echo '</div>';

echo '<br>';
echo '<div style="width:80%; margin:0 auto; position:relative;">';
$height1 = 0;
if (isset($arrallocations)) {

    $iTimestamp = mktime($earlieststart, 0, 0, 1, 1, 2011);

    for ($i = $earlieststart; $i < $lateststart; $i++) {
        echo '<div style="width: 100px; height:60px; position: absolute; left: 0px; top:' . (($i - $earlieststart) * 60) . 'px" class="calendartimecell">' . date('H:i', $iTimestamp) . '</div>';
        for ($ii = 0; $ii < 4; $ii++) {
            echo '<div style="width: 25px; height:15px; position: absolute; left: 100px; top:' . ((($i - $earlieststart) * 60) + ($ii * 15)) . 'px" class="calendartimecell"></div>';
        }
        echo '<div style="width:' . ($intteamCount * 500) . 'px; height:60px; position: absolute; left: 125px; top:' . (($i - $earlieststart) * 60) . 'px" class="calendardutycell"></div>';
        $iTimestamp += 3600;
    }

    $intDepCount = 0;
    foreach ($arrallocations['Allocations'] as $intCurrentDepartmentID => $arrAllocation) {
        $dutyname = $arrAllocation['dutyname'];
        $dutyduration = $arrAllocation['duration'];

        if (($arrAllocation['misc'] == 0) && ((strtolower($arrAllocation["dutyname"]) != 'leave') && (strtolower($arrAllocation["dutyname"]) != 'off leave'))) {
            $dutystart = $arrAllocation['starttime'];
            $dutyend = $arrAllocation['endtime'];
            $intdutystart = floor($dutystart / 60);

            $intdutyend = floor($dutyend / 60);
            if ($intdutyend <= $intdutystart) {
                $intdutyend = $intdutyend + 1440;
            }

            $dutytop = floor($intdutystart - ($earlieststart * 60));
            $dutyheight = ceil($intdutyend - ($earlieststart * 60) - $dutytop) - 1;
            echo '<div style="overflow:hidden; width:225px; height: ' . $dutyheight . 'px; position: absolute; left:' . (($intDepCount * 500) + 125) . 'px; top: ' . $dutytop . 'px; background-color:' . $arrAllocation['AllocBackColour'] . '" class="calendarduty">';
            echo '<div class="padded"><br>';
            echo $dutyname . '<br>';
            echo gmdate("H:i", $dutystart) . '-' . gmdate("H:i", $dutyend);                        
            if (isset($arrAllocation['dutycomments']) && strpos($arrAllocation['dutycomments'], '__COMMENT_SEPARETOR__') !== false) {
                $commentArr = explode('__COMMENT_SEPARETOR__', $arrAllocation['dutycomments']);
                $arrAllocation['personcomments'] = isset($commentArr[0]) ? $commentArr[0] : '';
                $arrAllocation['dutycomments'] = isset($commentArr[1]) ? $commentArr[1] : '';
            }            
            if ($arrAllocation['dutycomments'] != '') {
                echo "<br><br><b>Comments against this duty</b><br>";
                echo $arrAllocation['dutycomments']; 
            }
            if ((($isAdmin == 1) || ($isScheduler== 1))) {
                if ($arrAllocation['personcomments'] != '') {
                    echo "<br><br><b>Comments against this person</b><br>";
                    echo $arrAllocation['personcomments'];
                }
            }
            echo '</div>';
            echo '</div>';
            // The Jobs
            if (isset($arrAllocation['jobs'])) {
                foreach ($arrAllocation['jobs'] as $jobid => $job) {
                    $jobname = $job["jobname"];
                    $top = floor(($job["jobstart"] / 60) - ($earlieststart * 60));
                    if($job["jobstart"] == 0 && strtoupper($jobname) == 'LEAVE'){
                        $top = floor((86400 / 60) - ($earlieststart * 60));
                    }
                    if (($job["jobstart"] < $job["jobend"]) && ($job["aftermidnight"] == 1)) {
                        $height = ceil((($job["jobend"] + 86400) / 60) - (($job["jobstart"] + 86400) / 60));
                        $top = floor((($job["jobstart"] + 86400) / 60) - ($earlieststart * 60));
                    } elseif ($job["jobstart"] > $job["jobend"]) {
                        $height = ceil((($job["jobend"] + 86400) / 60) - ($job["jobstart"] / 60));
                    } else {
                        $height = ceil(($job["jobend"] / 60) - ($job["jobstart"] / 60));
                        if($job["jobstart"] == 0 && strtoupper($jobname) == 'LEAVE'){
                            $height = ceil(($job["jobend"] / 60) - ($job["jobstart"] / 60)) + 1.8;
                        }
                    }
                    $jobbackcolour = $job["backcolour"];
                    $jobfontcolour = $job["fontcolour"];
                    if (strtoupper($jobname) == 'LEAVE') {
                        $pdlJobId = $jobid;
                        $jobbackcolour = '#109146';
                        $jobfontcolour = '#000000';
                    }
                    echo '<div style="width:275px; height: ' . ($height - 2) . 'px; position: absolute; left:' . (($intDepCount * 500) + 350) . 'px; top: ' . $top . 'px; overflow:hidden; background-color:' . $jobbackcolour . '" class="calendarduty">';
                    // The name and times
                    echo '<div style="width: 200px; height: ' . $height . 'px; position: absolute; left: 10px; top:0px; overflow:hidden">';
                    echo '<font color="' . $jobfontcolour . '">' . $jobname;
                    if ($height > 30) {
                        echo '<br>';
                    } else {
                        echo ' ';
                    }
                    echo gmdate("H:i", $job["jobstart"]) . '-' . gmdate("H:i", $job["jobend"]);
                    echo '</font>';
                    echo '</div>';
                    // End The name and times

                    if ($job["comments"] != '') {
                        echo '<div class="mark handcursor" id="mark' . $jobid . '" style="width: 200px; height: ' . $height . 'px; position: absolute; left: 200px; top:0px; overflow:hidden">';
                        echo '<font color="' . $jobfontcolour . '">' . $job["comments"] . '</font>';
                        echo '</div>';
                    }
                    echo '</div>';

                    echo '<div class="icontent' . $jobid . ' calendardutypadded" id="mark' . $jobid . '" style="z-index:9999; width: 200px; position: absolute; left:' . (($intDepCount * 500) + 450) . 'px; top: ' . ($top + 20) . 'px; display: none; background-color:' . $jobbackcolour . '">';
                    //style="style="width: 399px; height: '.$height.'px; position: absolute; left: 401px; top: '.$top.'px; display: none; background-color:'.$jobbackcolour.'"">';
                    echo '<font color="' . $jobfontcolour . '">' . $job["comments"] . '</font>';
                    echo '</div>';
                }

                if ((count($arrAllocation['jobs']) == 1) && isset($pdlJobId) && $pdlJobId > 0 && isset($arrAllocation['jobs'][$pdlJobId]['jobname']) && strtoupper($arrAllocation['jobs'][$pdlJobId]['jobname']) == 'LEAVE') {
                    $jobbackcolour = '#FFFFFF';
                    $jobfontcolour = '#000000';

                    $pdlJobStartTime = ($arrAllocation['jobs'][$pdlJobId]['jobstart'] != 0) ? $arrAllocation['jobs'][$pdlJobId]['jobstart'] : 86400;
                    $pdlJobEndTime = $arrAllocation['jobs'][$pdlJobId]['jobend'];
                    
                    $intpdlstart = floor($pdlJobStartTime / 60);

                    $intpdlend = floor($pdlJobEndTime / 60);
                    if ($intpdlend <= $intpdlstart) {
                        $intpdlend = $intpdlend + 1440;
                    }

                    if ($dutystart < $pdlJobStartTime && $pdlJobEndTime < $dutyend) {
                        $top1 = $dutytop;
                        $height1 = ceil($intpdlstart - ($earlieststart * 60) - $dutytop) + 1.8;
                        $workingDuration1 = ' ['.$service->convertSecondsIntoTime($dutystart,':','No').'-'.$service->convertSecondsIntoTime($pdlJobStartTime,':','No').']';

                        echo '<div style="width:275px; height: ' . ($height1 - 2) . 'px; position: absolute; left:' . (($intDepCount * 500) + 350) . 'px; top: ' . $top1 . 'px; overflow:hidden; background-color:' . $jobbackcolour . '" class="calendarduty">';
                        echo '<div style="width: 200px; height: ' . $height1 . 'px; position: absolute; left: 10px; top:0px; overflow:hidden">';
                        echo '<font color="' . $jobfontcolour . '">Working'.$workingDuration1.'</font>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="icontent' . $pdlJobId . ' calendardutypadded" id="mark' . $pdlJobId . '" style="z-index:9999; width: 200px; position: absolute; left:' . (($intDepCount * 500) + 450) . 'px; top: ' . ($top1 + 20) . 'px; display: none; background-color:' . $jobbackcolour . '">';
                        echo '</div>';

                        $checkMidNight = 0;
                        if($pdlJobStartTime > $pdlJobEndTime){
                            $checkMidNight = (86400/60);
                        }
                        $top2 = ($height1 + $checkMidNight + (ceil(($pdlJobEndTime / 60) - ($pdlJobStartTime / 60))) + $dutytop) - 4;
                        $height2 = ((ceil($intdutyend - ($earlieststart * 60) - $dutytop) + 5) - ((ceil($intpdlend - $intpdlstart)) + $height1));
                        $workingDuration2 = ' ['.$service->convertSecondsIntoTime($pdlJobEndTime,':','No').'-'.$service->convertSecondsIntoTime($dutyend,':','No').']';

                        echo '<div style="width:275px; height: ' . ($height2 - 2) . 'px; position: absolute; left:' . (($intDepCount * 500) + 350) . 'px; top: ' . $top2 . 'px; overflow:hidden; background-color:' . $jobbackcolour . '" class="calendarduty">';
                        echo '<div style="width: 200px; height: ' . $height2 . 'px; position: absolute; left: 10px; top:0px; overflow:hidden">';
                        echo '<font color="' . $jobfontcolour . '">Working'.$workingDuration2.'</font>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="icontent' . $pdlJobId . ' calendardutypadded" id="mark' . $pdlJobId . '" style="z-index:9999; width: 200px; position: absolute; left:' . (($intDepCount * 500) + 450) . 'px; top: ' . ($top2 + 20) . 'px; display: none; background-color:' . $jobbackcolour . '">';
                        echo '</div>';
                    }
                    if ($dutystart == $pdlJobStartTime && $pdlJobEndTime < $dutyend) {
                        $checkMidNight = 0;
                        if($pdlJobStartTime > $pdlJobEndTime){
                            $checkMidNight = (86400/60);
                        }
                        $top1 = ($dutytop + $checkMidNight + (ceil(($pdlJobEndTime / 60) - ($pdlJobStartTime / 60)))) - 1;
                        $height1 = ((ceil($intdutyend - ($earlieststart * 60) - $dutytop) + 2) - ((ceil($intpdlend - $intpdlstart)) + $height1));
                        $workingDuration1 = ' ['.$service->convertSecondsIntoTime($pdlJobEndTime,':','No').'-'.$service->convertSecondsIntoTime($dutyend,':','No').']';

                        echo '<div style="width:275px; height: ' . ($height1 - 2) . 'px; position: absolute; left:' . (($intDepCount * 500) + 350) . 'px; top: ' . $top1 . 'px; overflow:hidden; background-color:' . $jobbackcolour . '" class="calendarduty">';
                        echo '<div style="width: 200px; height: ' . $height1 . 'px; position: absolute; left: 10px; top:0px; overflow:hidden">';
                        echo '<font color="' . $jobfontcolour . '">Working'.$workingDuration1.'</font>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="icontent' . $pdlJobId . ' calendardutypadded" id="mark' . $pdlJobId . '" style="z-index:9999; width: 200px; position: absolute; left:' . (($intDepCount * 500) + 450) . 'px; top: ' . ($top1 + 20) . 'px; display: none; background-color:' . $jobbackcolour . '">';
                        echo '</div>';
                    }
                    if ($dutystart < $pdlJobStartTime && $pdlJobEndTime == $dutyend) {
                        $top1 = $dutytop;
                        $height1 = ceil($intpdlstart - ($earlieststart * 60) - $dutytop) + 1.8;
                        $workingDuration1 = ' ['.$service->convertSecondsIntoTime($dutystart,':','No').'-'.$service->convertSecondsIntoTime($pdlJobStartTime,':','No').']';

                        echo '<div style="width:275px; height: ' . ($height1 - 2) . 'px; position: absolute; left:' . (($intDepCount * 500) + 350) . 'px; top: ' . $top1 . 'px; overflow:hidden; background-color:' . $jobbackcolour . '" class="calendarduty">';
                        echo '<div style="width: 200px; height: ' . $height1 . 'px; position: absolute; left: 10px; top:0px; overflow:hidden">';
                        echo '<font color="' . $jobfontcolour . '">Working'.$workingDuration1.'</font>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="icontent' . $pdlJobId . ' calendardutypadded" id="mark' . $pdlJobId . '" style="z-index:9999; width: 200px; position: absolute; left:' . (($intDepCount * 500) + 450) . 'px; top: ' . ($top1 + 20) . 'px; display: none; background-color:' . $jobbackcolour . '">';
                        echo '</div>';
                    }
                }
            }
        } else {
            echo '<div style="width:' . ($intteamCount * 500) . 'px; height: 200px; position: absolute; left: 125px; top:60px;" class="LightYellow">';
            echo '<div class="indentleft">';
            echo '<br>Your duty today does not have defined start time<br>';
            echo $dutyname . '<br>';
            echo number_format((float) ($dutyduration / 3600), 2, '.', '') . ' (Hours)';
            echo '</div>';
            echo '</div>';
        }
        $intDepCount++;
    }
} else {
    echo '<div class="tableheadersmall medtextboldcentre" style="width:800px; margin:0 auto; position:relative;">';
    echo '<br>Unable to show you allocation for this day.<br>This is either because the day has not yet been published or you are not an Allocate user.<br><br>';
    echo '</div>';
}
echo '</div>';
echo '</div>';

?>


<script language="JavaScript" type="text/javascript">
 $(document).ready(function() {
   $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "350px"
  });
})

$(function () {
  dateCommentOptionsSet(2, 'fa-2x', 'lightblue');
  initializeDateCommentViewIcon();
  $(".mark").bind("mouseover", function () {
    var index = $(this).attr("id").replace("mark", "");
      $(".icontent" + index).show();
    });
    $(".mark").bind("mouseout", function () {
      var index = $(this).attr("id").replace("mark", "");
        $(".icontent" + index).hide();
      });
});


$(function() {
  $( "#datepicker" ).datepicker({
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    dateFormat: "yy-mm-dd",
    defaultDate: "<?php echo $strCurrentDate ?>" ,
    onSelect: function (dateText, inst) {
      var currentdate = dateText;
      ShowDailyRota( currentdate, '<?php echo $schedulingPersonId ?>')
    }
  });
});

</script>