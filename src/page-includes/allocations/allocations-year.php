<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
use App\Models\User\RefRole;
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};
$sessionUser = $_SESSION['user'];
$strUser = $sessionUser['user'];
$userId = $sessionUser['UserID'];

$service = new AllocationService();

if (isset($_REQUEST["schedulingPersonId"]) && !empty($_REQUEST["schedulingPersonId"])) {
  $schedulingPersonId = $_REQUEST["schedulingPersonId"];
} else {
  $schedulingPersonId = GetScheduledPersonIdbyUserId($userId);
}

$arrUser = GetScheduledPersonTeamDetails($schedulingPersonId);

if (isset($_REQUEST['teamId'])) {
  $intTeamId = $_REQUEST['teamId'];
}
else {
  if(!empty($arrUser['homeTeamId'])) {
    $intTeamId   = $arrUser['homeTeamId'];
  }
  else {
    $intTeamId = key($arrUser);
  }
}
$strUserLogin = $arrUser[$intTeamId]['Login'] ?? 0;
if (strtolower($strUserLogin) == strtolower($strUser)) {
  $intThisIsMe = 1;
}
else {
  $intThisIsMe = 0;
}

$schPersonData = GetScheduledPersonDetailsById($schedulingPersonId);
$strFullName = $schPersonData['FullName']  ?? '';

$arrTeamDefaults = GetTeamDefaults($schedulingPersonId,$intTeamId);

if (isset($_REQUEST['date'])) {
  $strCurrentDate = date("Y-m-d", strtotime($_REQUEST['date']));
} else {
  if (isset($_SESSION['allocattionsdate'])) {
    $strCurrentDate = $_SESSION['allocattionsdate'];
  } else {
    $strCurrentDate = date("Y-m-d");
  }
}

$arrStaffOptions = GetStaffOtionsByTeam ($strUser, $userId);

//Permissions
$isScheduler = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamId, RefRole::SCHEDULER);
$isManager = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamId, RefRole::MANAGER);
$isTeamAdmin = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamId, RefRole::SCHEDULING_TEAM_ADMIN);
$hasShiftleaderRole = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamId, RefRole::SHIFT_LEADER);
$isTeamLeader = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamId, RefRole::TEAM_LEADER);
$isSchedulingTeamViewer = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamId, RefRole::SCHEDULING_TEAM_VIEWER);
$isScheduledPerson = $userRoleTrait->checkSchedulingTeamRoleExists($intTeamId, RefRole::SCHEDULED_PERSON);
$isAdmin = $userRoleTrait->isSystemAdmin();
//If system admin or area admin the person have STA permission
if($isTeamAdmin == 0) {
  $isTeamAdmin = $userRoleTrait->checkEditWeeeklyAdminRole($intTeamId);
}

/* Set shiftleader flag for rotas --START */
$isShiftleader = 1;

if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isManager == 1)) {
  $isShiftleader = 0;
}
/* Set shiftleader flag for rotas --START */


$intHideRota = $arrStaffOptions[$intTeamId]['HideRota'] ?? 0;

if($isManager == 1 || $isScheduler == 1 || $isTeamAdmin == 1) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
}
else if((($hasShiftleaderRole == 1) && $intThisIsMe == 1) || (($isSchedulingTeamViewer == 1) && $intThisIsMe == 1) || ($intThisIsMe == 1)) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
}
else {
  $dteFirstDate = date("Y-m-d", strtotime("-$intViewYears years"));
}

$startdate = date("Y-01-01", strtotime($strCurrentDate));
$enddate =   date('Y-m-d', strtotime($startdate. '+1 year'));
$lastyear =   date('Y-m-d', strtotime($startdate. '-1 year'));

$tdate = $startdate;
while (strtotime($tdate) <= strtotime($enddate)) {
  $arrweeks[] = bbcweeknumber($tdate);
  $tdate = date ("Y-m-d", strtotime("+1 week", strtotime($tdate)));
}

$arrLeave = ReadLeaveForRota($strUser, $startdate, $enddate);
$arrAllocations = ReadAllocationsIndividual($arrweeks[0], $arrweeks[count($arrweeks) - 1], $schedulingPersonId, $arrTeamDefaults, 0, 0, $isShiftleader);
  echo '<h1 class="sr-only">Yearly Allocations</h1>';
  echo '<div id="yearlyAllocationBlock">';
  echo '<table class="tablesmall">';
  echo '<tr height="35px" class="yearlyAllocationTR1 yearlyZindex">';
  echo '<td colspan="43" class="tableheadersmall bigtextbold">';
  if ($intHideRota == 0) {
    echo 'Yearly Allocations / Underlying Rota Pattern for '.$strFullName;
  }
  else {
    echo 'Yearly Allocations for '.$strFullName;
  }
  echo '</td>';
  echo '</tr>';

  echo '<tr class="yearlyAllocationTR2 yearlyZindex">';
  echo '<td class="tableheader">';
  echo '</td>';

  echo '<td colspan="7" class="tableheader handcursor" onclick="javascript:ShowYearAllocations(\''.$lastyear.'\',\''.$schedulingPersonId.'\')";>';
  echo '<br>&lt;&lt;&nbsp'.date('Y', strtotime($lastyear)).'<br><br>';
  echo '</td>';
  echo '<td colspan="28" class="tableheader" align="center">';
  echo date('Y', strtotime($startdate));
  echo '</td>';
  echo '<td colspan="7" class="tableheader handcursor" align="right" onclick="javascript:ShowYearAllocations(\''.$enddate.'\',\''.$schedulingPersonId.'\')";>';
  echo date('Y', strtotime($enddate)).'&nbsp;&gt;&gt;';
  echo '</td>';
  echo '</tr>';


  // Start with the table and write the days across the top
  echo '<tr class="yearlyAllocationTR3 yearlyZindex">';
  echo '<td class="tableheadersmall"><div class="tableheadersmall"></div></td>';
  for ($i=0; $i <= 5; $i++) {
    for ($ii=0; $ii <= 6; $ii++) {
      echo '<td class="tableheadersmall"><div class="tableheadersmall smalltextcentre box24x15">'.substr($invdowMap[$ii], 0, 1).'</div></td>';
    }
  }
  echo '</tr>';

  // Now start on the months
  $mdate = $startdate;
  while (strtotime($mdate) <= strtotime("-1 month", strtotime($enddate))) {
    echo '<tr>';
    echo '<td rowspan="2" class="tableheadersmall"><div class="smalltextcentre handcursor" onclick="javascript:ShowRota(\''.$mdate.'\',\''.$schedulingPersonId.'\','.$intTeamId.')";>';
    echo date("M", strtotime($mdate));
    echo '</div></td>';
    for ($iweek = 0; $iweek <= 5; $iweek++){
     $currweekstarts = date("Y-m-d", strtotime("+".($iweek * 7)." days", strtotime($mdate)));
     $currweek = bbcweeknumber($currweekstarts);

     echo '<td colspan="7" class="tableheadersmall handcursor" align="center" onclick="javascript:ShowAllocations('.$intTeamId.',\''.$currweek.'\')">';
     // Is the month equal to the current one?
     if (date("m", strtotime(datefromweek($currweek))) <= date("m", strtotime($mdate))) {
 	     echo spinweek($currweek);
     }
     echo '</td>';
    }
    echo '</tr>';

  echo '<tr>';
  // New row and ....
   // Now put spacers in for the Saturday to the first day
  $ddate  = $mdate;
  $daysinmonth = date("t", strtotime($ddate));
  $startdaynumber = $dowMap[date("D", strtotime($ddate))];
  for ($i=1; $i <= $startdaynumber; $i++) {
    echo '<td class="lightcell"></td>';
  }

  $nextmonth = strtotime("+1 month", strtotime($ddate));
  while (strtotime($ddate) < $nextmonth) {
    echo '<td valign="top" class="lightcell">';
    // The day of the month
    echo '<div class="medlightcell smalltextcentre box24x15">';
    echo date("d", strtotime($ddate));
    echo '</div>';

    $weeknumber = bbcweeknumber($ddate);
    $daynumber = $dowMap[date("D", strtotime($ddate))];
    if (isset($arrAllocations['Weeks'][$weeknumber][$daynumber]) && $dteFirstDate < $ddate) {
      // If this is a Rota entry and there is leave then show it
      if ($arrAllocations['Weeks'][$weeknumber][$daynumber]['IsRota'] == 1 && isset($arrLeave[$ddate])) {
        $intFirstKey = array_keys($arrLeave[$ddate])[0];

        $pdlStartTime = $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] ?? 0;
        $pdlStartTime = !empty($pdlStartTime) ? $pdlStartTime : 0;
        $pdlEndTime = $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"] ?? 0;
        $pdlEndTime = !empty($pdlEndTime) ? $pdlEndTime : 0;
        if ($arrLeave[$ddate][$intFirstKey]['Approved'] == 1) {
          if(($pdlStartTime == 0) && ($pdlEndTime == 0)){
            echo '<div class=" LeaveOK  tipClass handcursor" title="Leave Approved">';
          } else {
            echo '<div class=" LeaveOK  tipClass handcursor" title="Leave Approved" style="background:none; color:#109146;">';
          }
        }
        else {
          if ($arrLeave[$ddate][$intFirstKey]['ShortNotice'] == 1) {
            echo '<div class=" LeaveShortNoticeApplied  tipClass handcursor" title="Short Notice">';
          }
          else {
            if ($arrLeave[$ddate][$intFirstKey]['OverSummer'] == 1) {
              echo '<div class=" LeaveOverSummer  tipClass handcursor" title="Over Summer Limit Request">';
            }
            else {
              if ($arrLeave[$ddate][$intFirstKey]['isOK'] == 1) {
                echo '<div class="LeaveOK  tipClass handcursor" title="Leave Requested Not Yet Approved">';
              }
              else {
                echo '<div class="LeaveNotOK  tipClass handcursor" title="Leave Requested and In Waiting List">';
              }

            }
         }
        }
        if(($pdlStartTime == 0) && ($pdlEndTime == 0)){
          echo 'Lea';
        } else {
          echo 'PDL';
        }
        echo '</div>';
      }
      else {

        $dutyname = '';
        // Explode the duty name on spaces and then use the first 2 items....
        $arrdutyname = explode(" ", $arrAllocations['Weeks'][$weeknumber][$daynumber]["Duty"]);
        foreach ($arrdutyname as $item) {
          $pdlStartTime = $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] ?? 0;
          $pdlStartTime = !empty($pdlStartTime) ? $pdlStartTime : 0;
          $pdlEndTime = $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"] ?? 0;
          $pdlEndTime = !empty($pdlEndTime) ? $pdlEndTime : 0;
          if(( $pdlStartTime == 0 ) && ( $pdlEndTime == 0 )){
            $dutyname.= substr($item, 0, 3).'<br>';
          } else {
            $dutyname.= substr($item, 0, 3).'<br><span style="color:#109146;">PDL</span>';
            break;
          }
        }
        if(isset($arrAllocations['Weeks'][$weeknumber][$daynumber]['hiddenDays']) && $arrAllocations['Weeks'][$weeknumber][$daynumber]['hiddenDays']==0){

          $title =  $arrAllocations['Weeks'][$weeknumber][$daynumber]["Duty"];
          $pdlStartTime = $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] ?? 0;
          $pdlStartTime = !empty($pdlStartTime) ? $pdlStartTime : 0;
          $pdlEndTime = $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"] ?? 0;
          $pdlEndTime = !empty($pdlEndTime) ? $pdlEndTime : 0;
          if ((isset($arrAllocations['Weeks'][$weeknumber][$daynumber]["StartTime"])) && ($pdlStartTime == 0) && ($pdlEndTime == 0)) {
            $title.= '<br>'.$arrAllocations['Weeks'][$weeknumber][$daynumber]["StartTime"].'-'.$arrAllocations['Weeks'][$weeknumber][$daynumber]["EndTime"];
          } else if($pdlStartTime != 0 || $pdlEndTime != 0) {
            $title.= '<br>'.($arrAllocations['Weeks'][$weeknumber][$daynumber]["StartTime"] ?? '').'-'.($arrAllocations['Weeks'][$weeknumber][$daynumber]["EndTime"] ?? '');
            $pdlETime = $pdlEndTime;

            if($pdlStartTime > $pdlEndTime){
              $pdlETime = 86400 + $pdlEndTime;
            }
            $title.= '<br>PDL: '.$service->convertSecondsIntoTime($pdlStartTime,':','No').'-'.$service->convertSecondsIntoTime($pdlETime,':','No').' | '.$service->convertSecondsIntoTime($pdlETime - $pdlStartTime,'.','Yes');
          }
        }else{
          $title = '';
        }
        echo '<div class="smalltextcentre box30x25 tipClass handcursor" title="'.$title.'" style="background-color: '.$arrAllocations['Weeks'][$weeknumber][$daynumber]["BackColour"].';">';

        if(isset($arrAllocations['Weeks'][$weeknumber][$daynumber]['hiddenDays']) && $arrAllocations['Weeks'][$weeknumber][$daynumber]['hiddenDays']==0){
          $DutyNameTrim = trim(strtoupper($arrAllocations['Weeks'][$weeknumber][$daynumber]["Duty"]));
          if (($DutyNameTrim == 'SICK') || ($DutyNameTrim == 'U-SICK') || ($DutyNameTrim == '-SICK')) {
              // Set default mustard yellow colour for Sick and U-Sick
              echo '<font color="#e89e3c">';
              echo $dutyname;
              echo '</font>';
          } else if (($DutyNameTrim == 'LEAVE') || ($DutyNameTrim == 'OFF LEAVE')) {
              // Set default green colour for Leave and OFF Leave
              echo '<font color="#009900">';
              echo $dutyname;
              echo '</font>';
          } else {
              echo '<font color="' . $arrAllocations['Weeks'][$weeknumber][$daynumber]["allocateTextColour"] . '">';
              echo $dutyname;
              echo '</font>';
          }
        }
        echo '</div>';
        }
      }
     else {
        if (isset($arrLeave[$ddate])) {
          $intFirstKey = array_keys($arrLeave[$ddate])[0];

          if ($arrLeave[$ddate][$intFirstKey]['Approved'] == 1 ) {
            echo '<div class=" LeaveOK  tipClass handcursor" title="Leave Approved">';
          }
          else {
            if ($arrLeave[$ddate][$intFirstKey]['ShortNotice'] == 1) {
              echo '<div class=" LeaveShortNoticeApplied  tipClass handcursor" title="Short Notice">';
            }
            else {
              if ($arrLeave[$ddate][$intFirstKey]['OverSummer'] == 1) {
                echo '<div class=" LeaveOverSummer  tipClass handcursor" title="Over Summer Limit Request">';
              }
              else {
                if ($arrLeave[$ddate][$intFirstKey]['isOK'] == 1) {
                  echo '<div class="LeaveOK  tipClass handcursor" title="Leave Requested Not Yet Approved">';
                }
                else {
                  echo '<div class="LeaveNotOK  tipClass handcursor" title="Leave Requested and In Waiting List">';
                }

              }
           }
          }
          echo 'Lea';
          echo '</div>';
        }
        else {
          echo '<div class=" smalltextcentre box30x25 tipClass handcursor" title="No Rota!">';
          echo '</div>';
        }
     }
    echo '</td>';
    $ddate = date ("Y-m-d", strtotime("+1 day", strtotime($ddate)));
  }
  // Now write in spaces at the end of the month
   for ($i=($daysinmonth + $startdaynumber); $i <= 41; $i++) {
    echo '<td class="lightcell"></td>';
  }
  echo '</tr>';
	$mdate = date ("Y-m-d", strtotime("+1 month", strtotime($mdate)));
  }
  echo '</table>';
  echo '</div>';

?>
<script type="text/javascript">
$('#yearlyAllocationBlock').height($(window).height() - 60);
$('[title]').qtip({
  style: { classes: 'qtip-rounded qtip-shadow qtip-dark'}
});
$('.qtip').click(function(event) {
    api.toggle(false);
})

</script>