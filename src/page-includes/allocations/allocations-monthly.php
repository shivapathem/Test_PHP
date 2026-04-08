<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/locksfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
include_once '../../page-includes/users/process/classUserSetup.php';
use App\Models\User\RefRole;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$strUser = empty($strUser) ? '' : $strUser;
$UserID = GetUserIdbyNetlogin($strUser);
$commonObj = new classCommonDBFunctions();
$setupObj = new classUserSetup();
$intIsFreelance =  $commonObj->GetIsFreelencerByNetlogin($strUser);
$intIsFreelance = !empty($intIsFreelance) ? 1 : 0;
$service = new AllocationService();
if($setupObj->isOtherBBCUser()) {
  $intIsFreelance = 1;
}

if (isset($_POST["schedulingPersonId"]) && !empty($_POST["schedulingPersonId"])) {
  $schedulingPersonId = $_POST["schedulingPersonId"];
} else {
  $schedulingPersonId = GetScheduledPersonIdbyUserId($UserID);
}

if ($schedulingPersonId == "0") {
  echo '<br>';
  echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
  echo '<br><br>';
  echo 'You are attempting to view the Allocations for your Default Team.<br>This is not possible Please choose a Department from the menu.';
  echo '<br></br><br>';
  echo '</div>';
  die;
}

$arrUser = GetScheduledPersonTeamDetailsNew($schedulingPersonId);

if (isset($_POST["teamId"])) {
  $schedulingTeamId = $_POST["teamId"];
} else {
  if (!empty($arrUser['homeTeamId'])) {
    $schedulingTeamId = $arrUser['homeTeamId'];
  } else {
    $schedulingTeamId = key($arrUser);
  }
}

//For freelancer and other BBC we use additional team
if($intIsFreelance) {
  foreach($arrUser as $teamId => $userData) {
    if(isset($userData['TeamName']) && ($userData['TeamName'] != 'Freelancers' && $userData['TeamName'] != 'Other BBC')) {
      if($userData['isDefault']) {
        $schedulingTeamId = $teamId;
        break;
      } else {
        $schedulingTeamId = $teamId;
      }
    }
  }
}

$arrTeamDefaults =[];
if (!empty($schedulingPersonId) && !empty($schedulingTeamId)) {
  $arrTeamDefaults = GetTeamDefaults($schedulingPersonId,$schedulingTeamId);
}

//Overtime is allowed based on home team.
$arrayHomeTeamSettings = [];
if (!empty($schedulingPersonId) && isset($arrUser['homeTeamId'])) {
  $arrayHomeTeamSettings = GetTeamDefaults($schedulingPersonId, $arrUser['homeTeamId']);
}
$allowInBuilding = $arrayHomeTeamSettings[$arrUser['homeTeamId']]['AllowInBuilding'] ?? 0;
$allowOverTime = $arrayHomeTeamSettings[$arrUser['homeTeamId']]['AllowApplyOvertime'] ?? 0;
$intMaskDays = $arrayHomeTeamSettings[$arrUser['homeTeamId']]['MaskAfter'];

foreach ($arrUser as $intTeamID => $arrSubUser) {
  if ($intTeamID != 'homeTeamId'){
    $arrFullName[] = isset($arrSubUser['FullName']) ? $arrSubUser['FullName'] : '';
  }
}

if (isset($arrFullName) && !empty($arrFullName) && is_array($arrFullName)) {
  $strFullName = $arrFullName[0];
}
$strUserLogin = $arrUser[$schedulingTeamId]['Login'] ?? '';

if (mb_strtolower($strUserLogin) == mb_strtolower($strUser)) {
  $intThisIsMe = 1;
} else {
  $intThisIsMe = 0;
}

$arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);
//Permissions
$isScheduler = $userRoleTrait->checkSchedulingTeamRoleExists($schedulingTeamId, RefRole::SCHEDULER);
$isManager = $userRoleTrait->checkSchedulingTeamRoleExists($schedulingTeamId, RefRole::MANAGER);
$isTeamAdmin = $userRoleTrait->checkSchedulingTeamRoleExists($schedulingTeamId, RefRole::SCHEDULING_TEAM_ADMIN);
$hasShiftleaderRole = $userRoleTrait->checkSchedulingTeamRoleExists($schedulingTeamId, RefRole::SHIFT_LEADER);
$isTeamLeader = $userRoleTrait->checkSchedulingTeamRoleExists($schedulingTeamId, RefRole::TEAM_LEADER);
$isSchedulingTeamViewer = $userRoleTrait->checkSchedulingTeamRoleExists($schedulingTeamId, RefRole::SCHEDULING_TEAM_VIEWER);
$isScheduledPerson = $userRoleTrait->checkSchedulingTeamRoleExists($schedulingTeamId, RefRole::SCHEDULED_PERSON);
$isAdmin = $userRoleTrait->isSystemAdmin();
//If system admin or area admin the person have STA permission
if($isTeamAdmin == 0) {
  $isTeamAdmin = $userRoleTrait->checkEditWeeeklyAdminRole($schedulingTeamId);
}
/* Set shiftleader flag for rotas --START */
$isShiftleader = 1;

$dteFirstDate = date("Y-m-d", strtotime("-$intViewYears years"));

/* Set shiftleader flag for rotas --END */
if (($isScheduler == 1)) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
  $isShiftleader = 0;
}

if (($isTeamAdmin == 1)) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
  $isShiftleader = 0;
}

$intHideRota = $arrStaffOptions[$schedulingTeamId]['HideRota'] ?? 0;
if (($isManager == 1) && ($intHideRota == 0)) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
  $isShiftleader = 0;
}


if (isset($_REQUEST["althead"])) {
  $althead = $_REQUEST["althead"];
} else {
  $althead = 0;
}

if (isset($_REQUEST['date'])) {
  $strStartDate = $_REQUEST['date'];
} else {
  $strStartDate = date("Y-m-d");
}
$arrStaffInTeam = GetStaffDetailsByTeamId($schedulingTeamId,'ALL');
if (!array_key_exists($schedulingPersonId, $arrStaffInTeam)) {
	$arrStaffInTeam[$schedulingPersonId]=$strFullName;
}
asort($arrStaffInTeam);

$currentweek = bbcweeknumber(date("Y-m-d"));
$strStartDate = date('Y-m-01', strtotime($strStartDate));
$currentmonth = date('Y-m-d', strtotime($strStartDate));

$nextmonth = date('Y-m-d', strtotime($strStartDate. ' +1 month'));
$lasttmonth = date('Y-m-d', strtotime($strStartDate. '-1 month'));
$strEndDate = date('Y-m-d', strtotime($nextmonth. '+4 days'));

$arrHolidays = calculateBankHolidayst(date("Y", strtotime($strStartDate)));
$arrayholiday=[];
if (!empty($arrHolidays)) {
	foreach ($arrHolidays as $keyholiday=>$valholiday){
		$arrayholiday[]=bbcweeknumber($keyholiday);
	}
}
$breakFlag = 0;
$tdate = $strStartDate;

while (strtotime($tdate) < strtotime($strEndDate)) {
  $arrweeks[] = bbcweeknumber($tdate);
  $tdate = date("Y-m-d", strtotime("+1 week", strtotime($tdate)));
}
$arrAllocations=[];
if ($strStartDate > $dteFirstDate) {
  $arrAllocations = ReadAllocationsIndividual($arrweeks[0], $arrweeks[count($arrweeks) - 1], $schedulingPersonId, $arrTeamDefaults, $strUserLogin, 0, $isShiftleader);
}

// Read Locks, Leave and Requests only of there are any allocations
$arrEDP = [];
if (!empty($arrAllocations)) {
  if ($strUserLogin != "0") {
   $arrLocks = ReadLocksByScheduledPersonId($arrweeks[0], $arrweeks[count($arrweeks) - 1], $schedulingPersonId);
    $arrLeave = ReadLeaveForRota($strUserLogin, date("Y-m-d", strtotime("-7 Days", strtotime($strStartDate))), $strEndDate);
    $arrRequests = GetRequestsForscheduledPersonID($schedulingPersonId, datefromweek($arrweeks[0]), datefromweek(addweeks($arrweeks[count($arrweeks) - 1], 1)));
    $strLocksStartDate = $arrLocks['LocksStart'];
    $strLocksEndDate = $arrLocks['LocksEnd'];
  } else {
    $strLocksStartDate = 0;
    $strLocksEndDate = 0;
  }

  $bbcweeknumberArray =  $commonObj->GetWeekStartDateByWeekNoFromTimeDim($arrweeks[0],'ByweeknoOnly',0);
  // How many Departments have been returned?
  $intCountDepts = count($arrAllocations);
  $StartDayofWeek = $bbcweeknumberArray['dDateTime'];
  foreach($arrAllocations as $arrAllocation) {
    $teamId = $arrAllocation['SchedulingTeamId'] ?? null;
    if(!isset($arrEDP[$teamId])) {
        $edpData = ReadEDP($StartDayofWeek, $strEndDate, $schedulingPersonId, $teamId);
        $arrEDP = is_array($edpData) ? $arrEDP + $edpData : $arrEDP;
    }
  }
} else {
  $bbcweeknumberArray =  $commonObj->GetWeekStartDateByWeekNoFromTimeDim($arrweeks[0],'ByweeknoOnly',0);
  $intCountDepts = count($arrAllocations);
  $StartDayofWeek = $bbcweeknumberArray['dDateTime'];
  $edpData = ReadEDP($StartDayofWeek, $strEndDate, $schedulingPersonId, 0);
  $arrEDP = is_array($edpData) ? $arrEDP + $edpData : $arrEDP;
}
echo '<h1 class="sr-only">Monthly Allocations</h1>';
echo '<table class="tablesmall" width="100%">';
// ######################################################### The Month across the top #########################################################
echo '<tr>';
if ($althead == 0) {
 // if ($intThisIsMe == 1) {
    echo '<td colspan="4" class="tableheadersmall medtextbold">';
    if ($intHideRota == 0) {
      echo '&nbsp;&nbsp;Monthly Allocations / Underlying Rota Pattern for ';
    } else {
        echo '&nbsp;&nbsp;Monthly Allocations for ';
    }
    if ($intIsFreelance == 0) {
      echo '&nbsp;&nbsp;<select class="chosen-select" name="department" onchange="javascript:ShowRota(\''.$currentmonth.'\',value,'.$schedulingTeamId.')";>';
      foreach ($arrStaffInTeam as $strSN => $strName) {
        $strName = !empty($strName) ? $strName : '';
        if ($schedulingPersonId == $strSN) {
          echo '<option selected value="'.$strSN.'">'.mb_convert_encoding($strName, 'UTF-8').'</option>';
        } else {
          echo '<option value="'.$strSN.'">'.mb_convert_encoding($strName, 'UTF-8').'</option>';
        }
      }
      echo '</select>';
    }
    else {
      echo $strFullName;

    }
    echo '</td>';
    echo '<td colspan="2" class="tableheadersmall bigtextbold">';
    if ($intThisIsMe == 1) {
      echo '<table class="tablesmallnoborder" width="100%">';
      echo '<tr>';
      echo '<td class="tableheadersmall medtextbold" align="right">Email iCal file</td>';
      echo '<td class="tableheadersmall handcursor" onclick="javascript:CreateIcal(\''.$strStartDate.'\',\''.$schedulingPersonId.'\','.$schedulingTeamId.')"><img width="45px" height="45px" border="0" src="images/ics.png"></img></td>';
      echo '</tr>';
      echo '</table>';
    }
  //}
  //else {
   // echo '<td colspan="6" class="tableheadersmall bigtextbold">';
   // echo 'Monthly Allocations / Underlying Rota Pattern for '.$strFullName;
  //}
  echo '</td>';
  echo '<td colspan="2" class="tableheadersmall">';
  echo '<table class="tablesmallnoborder" width="100%">';
  echo '<tr>';
  echo '<td align="right">Yearly Allocations&nbsp;&nbsp;</td>';
  echo '<td><img class="handcursor" width="45px" height="45px" border="0" src="images/calendar.png" onclick="javascript:ShowYearAllocations(\''.$strStartDate.'\',\''.$schedulingPersonId.'\','.$schedulingTeamId.')";></img></td>';
  echo '</tr>';
  echo '</table>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="8">';
  echo '<table class="tablesmallnoborder" width="100%">';
  echo '<tr>';
  echo '<td width="20%" class="tableheadernoborder  bigtextbold handcursor" onclick="javascript:ShowRota(\''.$lasttmonth.'\',\''.$schedulingPersonId.'\','.$schedulingTeamId.')";>';
  echo '<br>&lt;&lt;&nbsp'.date('F', strtotime($lasttmonth)).'<br><br>';
  echo '</td>';
  echo '<td width="60%" class="tableheadernoborder bigtextbold" align="center">';
  echo date('F', strtotime($strStartDate));
  echo '<br>';
  echo date('Y', strtotime($strStartDate));
  echo '</td>';
  echo '<td width="20%" class="tableheadernoborder bigtextbold handcursor" align="right" onclick="javascript:ShowRota(\''.$nextmonth.'\',\''.$schedulingPersonId.'\','.$schedulingTeamId.')";>';
  echo date('F', strtotime($nextmonth)).'&nbsp;&gt;&gt;';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</td>';
}
else {
  echo '<td colspan="8" class="tableheadersmall bigtextbold">';

    if ($intHideRota == 1) {
      echo '&nbsp;&nbsp;Monthly Allocations / Underlying Rota Pattern for '.$strFullName.'<br><br>';
    }
    else {
        echo '&nbsp;&nbsp;Monthly Allocations for '.$strFullName.'<br><br>';
    }
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="8">';
  echo '<table class="tablesmallnoborder" width="100%">';
  echo '<tr>';
  echo '<td colspan="2" class="tableheadernoborder  bigtextbold handcursor" onclick="javascript:showrotatab(\''.$lasttmonth.'\',\''.$schedulingPersonId.'\','.$schedulingTeamId.')";>';
  echo '<br>&lt;&lt;&nbsp'.date('F', strtotime($lasttmonth)).'<br><br>';
  echo '</td>';
  echo '<td colspan="4" class="tableheadernoborder bigtextbold" align="center">';
  echo date('F', strtotime($strStartDate));
  echo '</td>';
  echo '<td colspan="2" class="tableheadernoborder bigtextbold handcursor" align="right" onclick="javascript:showrotatab(\''.$nextmonth.'\',\''.$schedulingPersonId.'\','.$schedulingTeamId.')";>';
  echo date('F', strtotime($nextmonth)).'&nbsp;&gt;&gt;';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</td>';
}
echo '</tr>';
  // ######################################################### END The Month across the top #########################################################
echo '</table>';
  echo '<table class="tablesmall">';
  echo '<tr>';
  echo '<th width="200px"></th>';
  echo '<th width="200px">Saturday</th>';
  echo '<th width="200px">Sunday</th>';
  echo '<th width="200px">Monday</th>';
  echo '<th width="200px">Tuesday</th>';
  echo '<th width="200px">Wednesday</th>';
  echo '<th width="200px">Thursday</th>';
  echo '<th width="200px">Friday</th>';
  echo '</tr>';
  // Loop through each week we are expecting
  foreach ($arrweeks as $id => $strCurrWeek) {
    $intWeekHours = 0;
    $intWeekHoursLessMeal = 0;
	$intmasking=1;
    // Set a variable so Requests can trigger a Lock
    $intFirstLock = 0;

    echo '<tr>';
    echo '<td class="tableheadersmall handcursor box90x90" align="center" onclick="javascript:ShowAllocations('.$schedulingTeamId.', \''.$strCurrWeek.'\')";><b>';
    echo 'Week<br>'.spinweek($strCurrWeek);
    echo '</b></td>';
	$heighthol="";
    for ($i=0; $i <= 6; $i++) {
      $strCurrDate = date("Y-m-d", strtotime(datefromweek($strCurrWeek, $i)));
      if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isManager == 1) ) {
        $intAdmin = 1;
        $intmasking=0;
      } else if ($hasShiftleaderRole == 1){
        $intAdmin = 1;
      } else if(($isSchedulingTeamViewer == 1 && $intThisIsMe == 1) || ($isScheduledPerson == 1 && $intThisIsMe == 1)) {
        $intAdmin = 1;
      } else {
        $intAdmin = 0;
      }
      if(($isAdmin == 1 || $isScheduler == 1 || $isTeamAdmin == 1)) {
        $intCanViewComments = 1;
      } else {
        $intCanViewComments = 0;
      }

	 if (in_array($strCurrWeek,$arrayholiday)) {
       $heighthol="style=height:30px;";
      }
      echo '<td style="overflow:hidden;">';
      echo '<table width="100%" class="tablesmallnoborder">';
      echo '<tr '.$heighthol.'>';

      $showDailyDefaultTeam = $schedulingTeamId;

      if ($strCurrDate == date("Y-m-d")) {
        echo '<td colspan="3" class="tableheaderlight handcursor date-comment" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $showDailyDefaultTeam . '" onclick=\'javascript:ShowDailyAllocations("'.$showDailyDefaultTeam.'","'.$strCurrDate.'")\';>';
      }
      else {
        echo '<td colspan="3" class="tableheader handcursor date-comment" data-date-comment-date="' . $strCurrDate . '" data-date-comment-team-id="' . $showDailyDefaultTeam . '" onclick=\'javascript:ShowDailyAllocations("'.$showDailyDefaultTeam.'","'.$strCurrDate.'")\';>';
      }
      echo date("d", strtotime($strCurrDate));
      if (isset($arrHolidays[$strCurrDate])) {
        echo " (".$arrHolidays[$strCurrDate].")";
      }
      echo '<span class="date-commment-info-icon" style="cursor: pointer; float:right; margin-right: 4px;"></span>';
      echo '</td>';
      echo '</tr>';
      $arrHiddenDays = ReadHiddenDays($schedulingTeamId, $strStartDate, $strEndDate);
      if (isset($arrHiddenDays[$schedulingTeamId])) {
        $arrDepHiddenDays = $arrHiddenDays[$schedulingTeamId];
      }
      else {
        $arrDepHiddenDays = array();
      }

      $strStatusClass = GetDayStatus($strCurrDate, 0, $intMaskDays, $arrDepHiddenDays);
      echo '<tr>';
      echo '<td colspan="3"  class="'.$strStatusClass.'" style="height:5px;">';
      echo '<td>';
      echo '</tr>';

      $intDepCount = 1;

      $arrDepAllocations = $arrAllocations;
      $intTeamID = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['SchedulingTeamId'] ?? 0;
      $schedulingTeamName = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['SchedulingTeamName'] ?? '';
      $intColourWeek = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['ColourWeek'] ?? 0;
      if (isset($arrDepAllocations['Weeks'][$strCurrWeek]) && $arrDepAllocations['Weeks'][$strCurrWeek][$i]['IsRota'] == 0) {
        $breakFlag = 1;
      } else {
        $breakFlag = 0;
      }

      //Permissions
      $hasShiftleaderRole2 = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamID, RefRole::SHIFT_LEADER);

      $arrHiddenDays = ReadHiddenDays($intTeamID, $strStartDate, $strEndDate);
      echo '<tr>';
      echo '<td class="DutyCellNotWorking volunteerTd" width="33%"><img src="images/transparentpixel.png" width="1px" height="14px">';
      // Now do Err, marked financial Signed in
      // Allow the applying of Overtime
      if (strtotime($strCurrDate) >= strtotime(date("Y-m-d")) && $allowOverTime ) {
          if (isset($arrEDP[$schedulingPersonId][$strCurrDate])) {
            // There is an EDP requet in....
            if ($intAdmin == 1) {
              if (($strStatusClass!="dotw") || ($intmasking==0)) {
                echo '<div class="handcursor tipremoteedp volunteerIcon" dutydate="'.$strCurrDate.'" schPersonId="'.$schedulingPersonId.'" teamId="'.$intTeamID.'" onclick="javascript:EDPEdit(\''.$strCurrDate.'\',\''.$strStartDate.'\',\''.$schedulingPersonId.'\', '.$intTeamID.')">V</div>';
              } else {
                // echo '<img dutydate="'.$strCurrDate.'" schPersonId="'.$schedulingPersonId.'" teamId="'.$intTeamID.'" border="0" src="images/overtime.png" width="14px" height="14px">';
              }
            } else {
             // echo '<img dutydate="'.$strCurrDate.'" schPersonId="'.$schedulingPersonId.'" teamId="'.$intTeamID.'" border="0" src="images/overtime.png" width="14px" height="14px">';
            }
		      } else {
              if ($intAdmin == 1) {
				        if (($strStatusClass!="dotw") || ($intmasking==0)) {
							$allocationsSPID = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignInID'] ?? 0;
                    echo '<div class="handcursor volunteerIconGray" onclick="javascript:EDPApply(\''.$strCurrDate.'\',\''.$strStartDate.'\',\''.$schedulingPersonId.'\',\''.$allocationsSPID.'\');">V</div>';
                } else {
                    //echo '<img dutydate="'.$strCurrDate.'" schPersonId="'.$schedulingPersonId.'" teamId="'.$intTeamID.'"  border="0" src="images/overtimenotyet.png" width="14px" height="14px">';
                }
              } else {
                  //echo '<img dutydate="'.$strCurrDate.'" schPersonId="'.$schedulingPersonId.'" teamId="'.$intTeamID.'"  border="0" src="images/overtimenotyet.png" width="14px" height="14px">';
              }
          }
      }
      echo '</td>';
      echo '<td align="center" class="DutyCellNotWorking" width="33%">';
      // Is there any ERR
      if (isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]["ManualOThours"])) {
        if ($arrDepAllocations['Weeks'][$strCurrWeek][$i]["ManualOThours"] > 0) {
          echo '<div class="tipremote overTimeIcon" schPersonId="'.$schedulingPersonId.'" dutydate="'.$strCurrDate.'" teamId ="'.$intTeamID.'">V</div>';
        }
      }
      echo '</td>';

      echo '<td align="right" class="DutyCellNotWorking" width="34%">';
      if ($intAdmin == 1 && isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['ActingFlag']) && $arrDepAllocations['Weeks'][$strCurrWeek][$i]['ActingFlag'] == 1) {
          echo '<div class="actingLabelIconMonthyContainer"><span class="actingLabelIconMonthy">A</span></div>';
      }
      //  Sign In Calculation
      if ((isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignInDays'])) && ($arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignInDays'] > 0) && (strtotime($strCurrDate) >= strtotime(date("Y-m-d"))) && (strtotime($strCurrDate) <= strtotime("+".$arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignInDays']." days", strtotime(date("Y-m-d"))))) {
        $InBuilding = $arrDepAllocations['Weeks'][$strCurrWeek][$i]["InBuilding"];
        if (!isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignIn'])) {
          $img = 'red_cross.png';
          $action = 1;
        }
        else {
          switch ($arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignIn']) {
            case 1:
              if ($InBuilding == 1) {
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
              $img = 'red_cross.png';
              $action = 1;
              break;
          }
          if ($strCurrDate == date("Y-m-d") && $allowInBuilding == 1) {
            if (($intAdmin == 1) || ($intThisIsMe == 1) || ($hasShiftleaderRole2 == 1)) {
				$allocationsDutyId = isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['AllocationsDutyID']) ? $arrDepAllocations['Weeks'][$strCurrWeek][$i]['AllocationsDutyID'] : '';
              $js = ' onclick=\'javascript:MonthlySignInToDay("'.$strCurrDate.'",'.$action.',"'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty'].'","'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['StartTimeSec'].'", "'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['EndTimeSec'].'", "'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignInID'].'", "'.$allocationsDutyId.'", "'.$schedulingPersonId.'",'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignIn'].')\';';
            }
            else {
              $js = '';
            }
          }
          else {
            if (($intAdmin == 1) || ($intThisIsMe == 1) || ($hasShiftleaderRole2 == 1)) {
				$allocationsDutyId = isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['AllocationsDutyID']) ? $arrDepAllocations['Weeks'][$strCurrWeek][$i]['AllocationsDutyID'] : '';
              $js = ' onclick=\'javascript:MonthlySignInDay("'.$strCurrDate.'",'.$action.',"'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty'].'","'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['StartTimeSec'].'", "'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['EndTimeSec'].'", "'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignInID'].'", "'.$allocationsDutyId.'", "'.$schedulingPersonId.'",'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignIn'].')\';';
            }
            else {
              $js = '';
            }
          }
        }
        $DutyId = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['DutyId'] ?? 0;
        $DutyName = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty'] ?? 0;
        $allocationsSPID = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['SignInID'] ?? 0;
        $StartTime = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['StartTimeSec']?? 0;
        $EndTime = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['EndTimeSec'] ?? 0;
        echo '<div dutyId = "'.$DutyId.'" allocationsSPID="'.$allocationsSPID.'" DutyName="'.$DutyName.'" StartTime="'.$StartTime.'" EndTime="'.$EndTime.'" date="'.$strCurrDate.'" SchedulingPersonID="'.$schedulingPersonId.'" teamId="'.$schedulingTeamId.'" id="signInIcon_'.$strCurrDate.'" class="handcursor signedtip" '.$js.'>';
        echo '<img src="images/'.$img.'" border="0" height="12px" width="12px">';
        echo '</div>';
      }
      // End Sign In Calculation

      echo '</th>';
      echo '</tr>';
      echo '<tr>';

      if((isset($arrDepAllocations['Weeks'][$strCurrWeek][$i])) && (isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['hiddenDays'])) && ($arrDepAllocations['Weeks'][$strCurrWeek][$i]['hiddenDays']==0)) {
        if ((isset($arrDepAllocations['Weeks'][$strCurrWeek][$i])) && (isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['Masked'])) && isset($arrDepAllocations[$schedulingTeamId]['MaskType']) && ($arrDepAllocations[$schedulingTeamId]['MaskType'] == 1)) {
          echo '<td colspan="3"  class="DutyCellNotWorking" style="height:80px;">';
          echo '<td>';
        }
        else {
            $DutyNameTrim = trim(strtoupper($arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty']));
            $cellclass = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['CellClass'];
          if ($intColourWeek == 1 && isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['StartTime'])) {
              if (($DutyNameTrim == 'SICK') || ($DutyNameTrim == 'U-SICK') || ($DutyNameTrim == '-SICK')) {
                  // Set default mustard yellow colour for Sick and U-Sick
                  echo '<td colspan="3" class="handcursor verticalAlingment" style="background-color:' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]["BackColour"] . '; height:80px;" onclick=\'javascript:ShowDailyRota("' . $strCurrDate . '","' . $schedulingPersonId . '",' . $schedulingTeamId . ')\';>';
                   echo '<font color="' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]['allocateTextColour'] . '">';
              } else if (($DutyNameTrim == 'LEAVE') || ($DutyNameTrim == 'OFF LEAVE')) {
                  // Set default green colour for Leave and OFF Leave
                  echo '<td colspan="3" class="handcursor verticalAlingment" style="background-color:' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]["BackColour"] . '; height:80px;" onclick=\'javascript:ShowDailyRota("' . $strCurrDate . '","' . $schedulingPersonId . '",' . $schedulingTeamId . ')\';>';
                   echo '<font color="' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]['allocateTextColour'] . '">';
              } else {
                  echo '<td colspan="3" class="handcursor verticalAlingment" style="background-color:' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]["BackColour"] . '; height:80px;" onclick=\'javascript:ShowDailyRota("' . $strCurrDate . '","' . $schedulingPersonId . '",' . $schedulingTeamId . ')\';>';
                  echo '<br><font color="' . ($arrDepAllocations['Weeks'][$strCurrWeek][$i]['allcaoteTextColour'] ?? '#000000') . '">';
              }
              if ($intCountDepts >= 1) {
                if (($DutyNameTrim == 'U') || ($DutyNameTrim == '--') || ($DutyNameTrim == '--*') || ($DutyNameTrim == '-') || ($DutyNameTrim == '')){
                  echo '';
                }else{
                 echo  $arrDepAllocations['Weeks'][$strCurrWeek][$i]['SchedulingTeamName'] . '<br>';
                }
            }
          }
          else {
              if (($DutyNameTrim == 'SICK') || ($DutyNameTrim == 'U-SICK') || ($DutyNameTrim == '-SICK')) {
                  echo '<td colspan="3"  class="handcursor verticalAlingment" style="background-color:' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]["BackColour"] . '; height:80px;" onclick=\'javascript:ShowDailyRota("' . $strCurrDate . '","' . $schedulingPersonId . '",' . $schedulingTeamId . ')\';>';
                  if ($intCountDepts >= 1) {
                      echo  $arrDepAllocations['Weeks'][$strCurrWeek][$i]['SchedulingTeamName'] . '<br>';
                  }
                   echo '<font color="' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]['allocateTextColour'] . '">';
              } else if (($DutyNameTrim == 'LEAVE') || ($DutyNameTrim == 'OFF LEAVE')) {
                  echo '<td colspan="3"  class="handcursor verticalAlingment" style="background-color:' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]["BackColour"] . '; height:80px;" onclick=\'javascript:ShowDailyRota("' . $strCurrDate . '","' . $schedulingPersonId . '",' . $schedulingTeamId . ')\';>';
                  if ($intCountDepts >= 1) {
                      echo '<br>';
                  }
                   echo '<font color="' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]['allocateTextColour'] . '">';
              } else {
                  echo '<td colspan="3"  class="handcursor verticalAlingment" style="background-color:' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]["BackColour"] . '; height:80px;" onclick=\'javascript:ShowDailyRota("' . $strCurrDate . '","' . $schedulingPersonId . '",' . $schedulingTeamId . ')\';>';
                  if ($intCountDepts >= 1) {
                    if (($DutyNameTrim == 'U') || ($DutyNameTrim == '--') || ($DutyNameTrim == '--*') || ($DutyNameTrim == '-') || ($DutyNameTrim == '')){
                       echo '<br>';
                    }else{
                      echo  $arrDepAllocations['Weeks'][$strCurrWeek][$i]['SchedulingTeamName'] . '<br>';
                    }
                  }
                  echo '<font color="' . $arrDepAllocations['Weeks'][$strCurrWeek][$i]['allocateTextColour'] . '">';
              }
          }

          if($arrDepAllocations['Weeks'][$strCurrWeek][$i]['IsRota'] == 1 && ($strStatusClass == 'daynotfixed' || $strStatusClass == 'daynotfixed allocations-hideday-menu')) {
            echo '<i><b>'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty'].'</b></i>'.'<br>';
          } else {
            if($arrDepAllocations['Weeks'][$strCurrWeek][$i]['IsRota'] == 1){
              echo '<i><b>'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty'].'</b></i>'.'<br>';
            } else {
              echo '<b>'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty'].'</b>'.'<br>';
            }
          }

          if (!empty($arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty']) && strtoupper($arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty']) != 'U') {
            if (isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['StartTime'])) {
              $pdlStartTime = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['pdlStartTime'];
              $pdlEndTime = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['pdlEndTime'];
              $dStartTime = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['StartTime'];
              $dEndTime = $arrDepAllocations['Weeks'][$strCurrWeek][$i]['EndTime'];

              if(($pdlStartTime != 0) || ($pdlEndTime != 0)){
                if($pdlStartTime > $pdlEndTime){
                  $pdlEndTime = (86400 + $pdlEndTime);
                }
                $pdlTitle = 'PDL: '.$service->convertSecondsIntoTime($pdlStartTime,':','No').'-'.$service->convertSecondsIntoTime($pdlEndTime,':','No').'&nbsp;|&nbsp;'.$service->convertSecondsIntoTime($pdlEndTime-$pdlStartTime,'.','Yes');

                echo '<b>'.$dStartTime.'-'.$dEndTime .'</b>'.'<br>';
                echo '<b><span style="color:#109146;" title="'.$pdlTitle.'">[L: '.$service->convertSecondsIntoTime($pdlStartTime,':','No').'-'.$service->convertSecondsIntoTime($pdlEndTime,':','No').']</span></b>'.'<br>';
              } else {
                echo '<b>'.$dStartTime.'-'.$dEndTime .'</b>';
              }
            }
            else {
              if (isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duration']) && $arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duration'] != 0) {
                echo '<b>'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duration'].' Hours </b><br>';
              }
            }

            if (isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duration'])) {
              $intWeekHours = $intWeekHours + $arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duration'] + $arrDepAllocations['Weeks'][$strCurrWeek][$i]['BreakTime'];

              $intWeekHoursLessMeal = $intWeekHoursLessMeal + $arrDepAllocations['Weeks'][$strCurrWeek][$i]['DurationLessMeal'];
            }
          }
          echo '</font>';
          if ($arrDepAllocations['Weeks'][$strCurrWeek][$i]['IsRota'] == 0) {
            if ($arrDepAllocations['Weeks'][$strCurrWeek][$i]['ShowRotaAsWell'] == 1 && isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['RotaDuty']) && !isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['Masked'])) {
              echo '<div style="position: absolute;" class="DutyCellBottom">';
              echo '<font color="'. $arrDepAllocations['Weeks'][$strCurrWeek][$i]['RotaTextColour'].'">';
              if($intColourWeek == 1){
                echo '<i>';
                echo $arrDepAllocations['Weeks'][$strCurrWeek][$i]['RotaDuty'].'<br>';
                echo '</i>';
              } else {
                echo $arrDepAllocations['Weeks'][$strCurrWeek][$i]['RotaDuty'].'<br>';
              }
              echo '</font>';
              echo '</div>';
            }
          }
          echo '</td>';
        }
      }
      else {
        if($strStatusClass != "dotw" && isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty']) && $arrDepAllocations['Weeks'][$strCurrWeek][$i]['Duty'] != '-') {
          echo '<td colspan="3" class="handcursor verticalAlingment" style="background-color:#EFEFEF; height:80px;" onclick="javascript:ShowDailyRota(\'' . $strCurrDate . '\',' . $schedulingPersonId . ',' . $schedulingTeamId . ');"><br><font color="#000000"><b>U</b><br></font></td>';
          echo '<td>';
        } else {
          echo '<td colspan="3"  class="DutyCellNotWorking" style="height:80px;">';
          echo '<td>';
          if ($intCountDepts != $intDepCount) {
            echo '<tr height="28px"><td class="DutyCellNotWorking" width="34%"></td><td align="center" class="DutyCellNotWorking" width="34%"></td><td align="right" class="DutyCellNotWorking" width="34%"></td></tr>';
          }
        }
      }
      echo '</tr>';
      if ($intCountDepts == $intDepCount) {
        // Put the Leave, Requests and Locks in.....
        echo '<tr height="28px">';
        echo '<td class="DutyCellNotWorking" width="34%">';
        // ############################################## Leave
        $intApprovedLeave = 0;
        if (isset($arrLeave[$strCurrDate]) && isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['pdlStartTime'], $arrDepAllocations['Weeks'][$strCurrWeek][$i]['pdlEndTime']) && $arrDepAllocations['Weeks'][$strCurrWeek][$i]['pdlStartTime'] == 0 && $arrDepAllocations['Weeks'][$strCurrWeek][$i]['pdlEndTime'] == 0) {
          $strTip = '';
          foreach ($arrLeave[$strCurrDate] as $intTypeID => $arrDayLeave) {
            $strTip.= 'Leave Type '.$arrDayLeave['TypeDescription'].' in Group '.$arrDayLeave['GroupDescription'].'<br>';
            if ($arrDayLeave['Approved'] == 1) {
              $intApprovedLeave = 1;
              $strTip.= 'Is Approved';
            }
            else {
              $strTip.= 'Is Not yet Approved but is OK';
            }
          }
          if ($intApprovedLeave == 1) {
            echo '<div title="'.$strTip.'" class="LeaveOK box50x20 tipClass"><sub>Leave</sub></div>';
          }
        }
        echo '</td>';

        // Requests
        echo '<td align="center" class="DutyCellNotWorking" width="34%">';
        // Do the Requests......
        if (isset($arrRequests[$strCurrDate])) {
          $strRequestTip = 'Request Type '.$arrRequests[$strCurrDate]['Description'].'<br>';
          if ($arrRequests[$strCurrDate]['Approved'] == 1) {
            $strRequestTip.= 'Is Approved';
            $strRequestClass = "LeaveOK";
          }
          else {
            if ($arrRequests[$strCurrDate]['IsOK'] == 1) {
                $strRequestTip.= 'Is OK but Not Yet Approved<br>';
                $strRequestClass = "LeaveOK";
            }
            else {
                $strRequestTip.= 'Is in the Waiting List and Not Yet Approved<br>';
                $strRequestClass = "LeaveNotOK";
            }

          }
            echo '<div title="'.$strRequestTip.'" class="'.$strRequestClass.' box50x20 tipClass"><sub>Request</sub></div>';

        }
        echo '</td>';

        echo '<td align="right" class="DutyCellNotWorking" width="34%">';
		  if ((isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['DutyComments']) && $arrDepAllocations['Weeks'][$strCurrWeek][$i]['DutyComments']==1) || ((isset($arrDepAllocations['Weeks'][$strCurrWeek][$i]['PersonComments']) && $arrDepAllocations['Weeks'][$strCurrWeek][$i]['PersonComments']==1) && ($intCanViewComments==1))) {
	       echo '<div class="DutyCellBottomRight handcursor tipremotecomments" style="left:0px !important;"  teamid= "'.$intTeamID.'"  dutyId= "'.$arrDepAllocations['Weeks'][$strCurrWeek][$i]['DutyId'].'" DutyDate="'.$strCurrDate.'" SchedulingPersonID="'.$schedulingPersonId.'">';
           echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
           echo '</div>';
	     }
        // ###################################################################  LOCKS
        if (strtotime($strCurrDate) >= strtotime($strLocksStartDate) && strtotime($strCurrDate) <= strtotime($strLocksEndDate)) {
          // Is there already a Lock in?
          if (isset($arrLocks['Weeks'][$strCurrWeek]['Locks'])) {
            // There is a Lock......
            if (isset($arrLocks['Weeks'][$strCurrWeek]['Locks'][$i])) {
              // This is the locked day
              echo '<img title="This is your Lock for this week.<br>'.$arrLocks['Weeks'][$strCurrWeek]['Locks'][$i].'" border="0" src="../images/locks/locked.png" width="20px" height="20px">';
            }
            else {
              echo '<img title="You already have a Lock in this week." border="0" src="../images/locks/unlocked-unavailalable.png" width="20px" height="20px">';
            }
          }
          else {
            // Can Have Locks..............
            if ($arrLocks['Weeks'][$strCurrWeek]['HasRequest'] == 3) {
              echo '<img title="You may not apply for a Lock during this week." border="0" src="../images/locks/unlocked-unavailalable.png" width="20px" height="20px">';

            }
            else {
              if ($arrLocks['Weeks'][$strCurrWeek]['HasRequest'] == 2) {
                // If there is an approved request this is the lock... But Only Do It Once
                if (isset($arrRequests[$strCurrDate]) && $intFirstLock == 0) {
                  if ($arrRequests[$strCurrDate]['Approved'] == 1) {
                    echo '<img title="You have an Approved Request on this Day.<br>It has been marked as your Lock for this week." border="0" src="../images/locks/locked.png" width="20px" height="20px">';
                    $intFirstLock = 1;
                  }
                  else {
                    //Has an approved request - can't apply
                    echo '<img title="You have Approved Requests in this week.<br>You may not apply for a Lock." border="0" src="../images/locks/unlocked-unavailalable.png" width="20px" height="20px">';
                  }
                }
                else {
                  //Has an approved request - can't apply
                  echo '<img title="You have Approved Requests in this week.<br>You may not apply for a Lock." border="0" src="../images/locks/unlocked-unavailalable.png" width="20px" height="20px">';
                }
              }
              else {
                if ($intThisIsMe == 1) {
                  if ($arrLocks['Weeks'][$strCurrWeek]['HasRequest'] == 1) {
                    //Has an Unapproved request - can apply
                    echo '<img title="You have Unpproved Requests in this week.<br>If You apply for a Lock any Requests you have will be deleted." class="handcursor" border="0" src="../images/locks/unlocked-availalable.png" width="20px" height="20px" onclick="javascript:LockApplyConditional(\''.$strCurrWeek.'\',\''.$i.'\')">';
                  }
                  else {
                    echo '<img title="You may apply for a Lock this week." class="handcursor" border="0" src="../images/locks/unlocked-availalable.png" width="20px" height="20px" onclick="javascript:LockApply(\''.$strCurrWeek.'\',\''.$i.'\')">';
                  }
                }
                else {
                  if ($arrLocks['Weeks'][$strCurrWeek]['HasRequest'] == 1) {
                    //Has an Unapproved request - can apply
                    echo '<img border="0" src="../images/locks/unlocked-availalable.png" width="20px" height="20px">';
                  }
                  else {
                    echo '<img border="0" src="../images/locks/unlocked-availalable.png" width="20px" height="20px">';
                  }
                }
              }
            }
          }
        }
        else {
          if (isset($arrLocks['Weeks'][$strCurrWeek]['Locks'])) {
            // There is a Lock......
            if (isset($arrLocks['Weeks'][$strCurrWeek]['Locks'][$i])){
              // This is the locked day
              echo '<img title="This is your Lock for this week.<br>'.$arrLocks['Weeks'][$strCurrWeek]['Locks'][$i].'" border="0" src="../images/locks/locked.png" width="20px" height="20px">';
            }
          }
        }

        echo '</td>';
        echo '</tr>';
      }
      else {
        echo '<tr>';
        echo '<td colspan="3" height="2px">';
        echo '</td>';
        echo '</tr>';
      }
      $intDepCount++;

      echo '</table>';
      echo '</td>';
    }
    echo '</tr>';

    $intHasBreaks = 1;

    if (!isset($arrAllocations['Weeks'][$strCurrWeek]['HasBreaks']) && ($intHasBreaks == 1)) {
      $intHasBreaks = 0;
    }

    // The Hours worked this week
    if ($breakFlag == 1 && isset($arrDepAllocations['Weeks'][$strCurrWeek])) {
      echo '<tr>';
      echo '<th colspan="8" style="text-align:right">';
      echo 'Total Hours Excluding Breaks: '.SecondsToHoursDecimal($arrDepAllocations['Weeks'][$strCurrWeek]['TDExBreak']).'&nbsp;&nbsp;&nbsp;&nbsp;<br>';
      echo '</td>';
      echo '</tr>';
    }
  }
  echo '</table><br><br>';
  echo '<div id="dialog-edp-offer" title="Information!" style="display:none;">';
  echo '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can delete this offer<br>or add comments to it.</p>';
  echo '</div>';

?>
<script type="text/javascript">
 $(document).ready(function() {
   $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "350px"
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
})

$('[title]').qtip({
  position: {
    viewport: $(window)
  },
  style: { classes: 'qtip-rounded qtip-shadow qtip-light'}
});


function EDPApply(dDate,StartDate,schedulingPersonId, teamId) {
  $('*').qtip('hide');
  $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/allocations-edp-apply.php',
        data: {
          ddate: dDate,
          schedulingPersonId: schedulingPersonId,
          teamId: teamId,
          action: 'insert'
        },
        success: function(data,status) {
          ShowRota(StartDate, schedulingPersonId, <?php echo $schedulingTeamId; ?>);
          },
        error:function (data) {
          customAlert('some error found in EDPApply call.');
        }
    });
};

function EDPEdit(ddate,StartDate,schedulingPersonId, teamId) {
$('*').qtip('hide');
$(function() {
  $( "#dialog-edp-offer" ).dialog(
    {
      width: 400,
      buttons: {
        "Add Comments": function() {
          $.post("page-includes/allocations/allocations-edp-comments.php", {
            ddate: ddate,
            StartDate :StartDate,
            schedulingPersonId: schedulingPersonId,
            teamId: teamId,
            action: 'update'
          },
          function(data,status){
            $.facebox(data);
          })
          $(this).dialog("close");
        },
        "Delete": function() {
          $.post("page-includes/allocations/allocations-deleteedp.php", {
            ddate: ddate,
            schedulingPersonId: schedulingPersonId,
            teamId: teamId,
            action: 'delete'
          },
          function(data,status){
            ShowRota(StartDate, schedulingPersonId, <?php echo $schedulingTeamId; ?>);
          }
          )
          $( this ).dialog( "close" );
          },
          "Cancel": function() {
            $( this ).dialog( "close" );
          }
        }
    }
  );
});
}

  $('.tipremoteedp').each(function() {
    $(this).qtip({
      content: {
        text: function(event, api) {
          $.ajax({
            url: 'page-includes/allocations/allocations-edp-info.php',
            type: 'POST',
            data: {
              schedulingPersonId: api.elements.target.attr('schPersonId'),
              ddate: api.elements.target.attr('dutydate'),
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


function MonthlySignInToDay(curdate, action, DutyName, StartTime, EndTime, allocationsSPID, allocationsDutyId, SchedulingPersonID,signinStatus) {
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
            sdate: curdate,
            DutyName: DutyName,
            StartTime: StartTime,
            EndTime: EndTime,
            action: action,
            screenType: '1',
            allocationsSpId: allocationsSPID,
            allocationsDutyId: allocationsDutyId,
            'SigninStatus' : signinStatus
          },
          function(data,status){
          {
            ShowRota (curdate, SchedulingPersonID , <?php echo $schedulingTeamId; ?>);
          }
          });
          $( this ).dialog( "close" );
        },
        "Mark As In Building": function() {
          $('*').qtip('hide');
          $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
            sdate: curdate,
            DutyName: DutyName,
            StartTime: StartTime,
            EndTime: EndTime,
            action: 2,
            screenType: '1',
            allocationsSpId: allocationsSPID,
            allocationsDutyId: allocationsDutyId
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
            ShowRota (curdate, SchedulingPersonID, <?php echo $schedulingTeamId; ?>);
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
/**
* Store or Update EDP overtime volunteers details
* @param  date of the allocation
* @param  action based on action value set history and inbuilding
* @param  teamId of allocation
* @param  $schedulingPersonId of allocation
* @return load the data
*/
function MonthlySignInDay(curdate, action, DutyName, StartTime, EndTime, allocationsSPID, allocationsDutyId, SchedulingPersonID, signinStatus) {
  $('*').qtip('hide');
  $('#signInIcon_'+curdate).addClass('activeclass');
  $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
    sdate: curdate,
	DutyName: DutyName,
	StartTime: StartTime,
	EndTime: EndTime,
	action: action,
	screenType: '1',
	allocationsSpId: allocationsSPID,
	allocationsDutyId: allocationsDutyId,
	SigninStatus: signinStatus,
  },
  function(data,status){
  {
    ShowRota (curdate, SchedulingPersonID, <?php echo $schedulingTeamId; ?>);
  }
  });
}

function LockApply(week, day) {
  $('*').qtip('hide');
  $( "#dialog-lock-apply" ).dialog({
    width:640,
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/requests/lock-apply.php", {
          user: '<?php echo $strUserLogin?>',
          week: week,
          day: day
        },
        function(data,status){
        <?php
          echo 'ShowRota(\''.$strStartDate.'\',\''.$schedulingPersonId.'\','.$schedulingTeamId.')';
        ?>
        }
        );
      },
      "No": function() {
        $( this ).dialog( "close" );
      },
    }
  });
};

function LockApplyConditional(week, day) {
  $('*').qtip('hide');
  $( "#dialog-lock-conditional-apply" ).dialog({
    width:600,
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/requests/lock-apply.php", {
          user: '<?php echo $strUserLogin?>',
          week: week,
          day: day
        },
        function(data,status){
        <?php
          echo 'ShowRota(\''.$strStartDate.'\',\''.$schedulingPersonId.'\','.$schedulingTeamId.')';
        ?>
        }
        );
      },
      "No": function() {
        $( this ).dialog( "close" );
      },
    }
  });
};

  $( "#username" ).autocomplete({
    source: "page-includes/ajax-calls/returnstaff-allocations-rota.php",
    minLength: 2,
    select: function( event, ui ) {
      ShowRota ('<?php echo $strStartDate?>',ui.item.id, <?php echo $schedulingTeamId; ?>);
    }
  });

  $(function() {
    dateCommentOptionsSet(0, 'fa-lg', 'lightblue');
    initializeDateCommentViewIcon();
  });

</script>