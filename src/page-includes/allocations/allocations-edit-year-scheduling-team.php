<?php

use App\Models\User\RefRole;

echo '<link rel="stylesheet" type="text/css" href="styles/allocations/allocations-yearly.css?v=' . time() . '">';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';
include_once '../../page-includes/users/process/classUserSetup.php';
include_once __DIR__. '/../../function-includes/user-scheduling-team-list.php';
use Traits\UserRoleTrait;

$userRoleTrait = new class {
    use UserRoleTrait;
};

$sessionUser = $_SESSION['user'];
$strUser = $sessionUser['user'];
$userId = $sessionUser['UserID'];
$fromDate = $_REQUEST["fromDate"] ?? '';
$toDate = $_REQUEST["toDate"] ?? '';

$service = new AllocationService();
$setupObj = new classUserSetup();

if (isset($_REQUEST["schedulingPersonId"]) && !empty($_REQUEST["schedulingPersonId"])) {
  $schedulingPersonId = $_REQUEST["schedulingPersonId"];
} else {
  $schedulingPersonId = 0;
}

$arrUser = GetScheduledPersonTeamDetails($schedulingPersonId);

if (isset($_REQUEST['teamId'])) {
  $intTeamId = $_REQUEST['teamId'];
} else {
  if (!empty($arrUser['homeTeamId'])) {
    $intTeamId   = $arrUser['homeTeamId'];
  } else {
    $intTeamId = key($arrUser);
  }
}
$arrTeamDefaults = GetTeamDefaults(0, $intTeamId);
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
if (($arrTeamDefaults[$intTeamId]['IsShowEditYearly'] == 1) && ($isScheduler != 1) && ($isTeamAdmin != 1) && ($isTeamLeader != 1)) {
  echo "Access denied";
  die;
}

$strUserLogin = $arrUser[$intTeamId]['Login'] ?? '';
if (strtolower((string) $strUserLogin) == strtolower((string) $strUser)) {
  $intThisIsMe = 1;
} else {
  $intThisIsMe = 0;
}

$schPersonData = GetScheduledPersonDetailsById($schedulingPersonId);
$strFullName = $schPersonData['FullName'] ?? '';

if (isset($_REQUEST['date']) && !empty($_REQUEST['date'])) {
  $strCurrentDate = date("Y-m-d", strtotime((string) $_REQUEST['date']));
} else {
  if (isset($_SESSION['allocattionsdate'])) {
    $strCurrentDate = $_SESSION['allocattionsdate'];
  } else {
    $strCurrentDate = date("Y-m-d");
  }
}

$currentYear = date('Y', strtotime((string) $strCurrentDate));

/* Set shiftleader flag for rotas --START */
$isShiftleader = 1;

$maxStartDate = new DateTime()->modify('-3 months')->format('Y-m-d');
if (($isTeamLeader == 1) || ($isTeamAdmin == 1) || ($isManager == 1)) {
  $maxStartDate = new DateTime()->modify('-393 days')->format('Y-m-d');
}

if (($isScheduler == 1) || ($isTeamAdmin == 1) || ($isManager == 1)) {
  $isShiftleader = 0;
}
/* Set shiftleader flag for rotas --START */
if ($isManager == 1 || $isScheduler == 1 || $isTeamAdmin == 1) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
} else if (($hasShiftleaderRole == 1 && $intThisIsMe == 1) || ($isSchedulingTeamViewer == 1 && $intThisIsMe == 1) || ($intThisIsMe == 1)) {
  $dteFirstDate = date("Y-m-d", strtotime("-$intAdminViewYears years"));
} else {
  $dteFirstDate = date("Y-m-d", strtotime("-$intViewYears years"));
}

$startdate = date("Y-01-01", strtotime((string) $strCurrentDate));
$enddate =   date('Y-m-d', strtotime($startdate . '+1 year'));
$lastyear =   date('Y-m-d', strtotime($startdate . '-1 year'));

$tdate = $startdate;
while (strtotime($tdate) <= strtotime($enddate)) {
  $arrweeks[] = bbcweeknumber($tdate);
  $tdate = date("Y-m-d", strtotime("+1 week", strtotime($tdate)));
}


$schedulingPersonTeams[] = $intTeamId;
$scheduledPersonTeamHistory = getScheduledPersonTeamHistory($schedulingPersonId);
foreach ($scheduledPersonTeamHistory as $scheduledPersonTeamHistoryData) {
  if (($scheduledPersonTeamHistoryData['HomeTeam'] == 'Y') && (DateTime::createFromFormat('d-m-Y', $scheduledPersonTeamHistoryData['Enddate'])->setTime(0, 0, 0) >= (new DateTime())->setTime(0, 0, 0))) {
    $schedulingPersonTeams[] = $scheduledPersonTeamHistoryData['TeamID'];
  }
}

$strQueryLastCreatedWeek = "select dDateTime from TimeDimension where ixDayInWeek = 6 AND ixYearWeek =
(select max(AL_WeekNumber) as MaxWeekNumber from Allocations where AL_SchedulingTeamID IN (" . implode(',', $schedulingPersonTeams) . "))";
$stmt = $pdo->prepare($strQueryLastCreatedWeek);
$stmt->execute();
$lastCreatedWeekDayFriday = $stmt->fetch(PDO::FETCH_ASSOC)['dDateTime'];
$arrAllocations = AllocationsEditYearly($arrweeks[0], $arrweeks[count($arrweeks) - 1], $schedulingPersonId, $arrTeamDefaults, 0, 0, $isShiftleader);
$arrStaffInTeam = GetStaffDetailsByTeamId($intTeamId, 'ALL');
uasort($arrStaffInTeam, fn($a, $b) => strcmp(strtolower((string) $a), strtolower((string) $b)));
$futureUsers = json_decode(GetUserTeamPeopleList(0, 0, $intTeamId, 2), true);
foreach ($futureUsers as $futureUser) {
  $scheduledPersonTeamHistory = getScheduledPersonTeamHistory($futureUser['ScheduledPersonID']);
  $currentTeam = '';
  foreach ($scheduledPersonTeamHistory as $scheduledPersonTeamHistoryData) {
    if (
      ($scheduledPersonTeamHistoryData['HomeTeam'] == 'Y') &&
      (DateTime::createFromFormat('d-m-Y', $scheduledPersonTeamHistoryData['Startdate'])->setTime(0, 0, 0) <= (new DateTime())->setTime(0, 0, 0)) &&
      (DateTime::createFromFormat('d-m-Y', $scheduledPersonTeamHistoryData['Enddate'])->setTime(0, 0, 0) >= (new DateTime())->setTime(0, 0, 0))
    ) {
      $currentTeam = ' (Current Team - ' . $scheduledPersonTeamHistoryData['ScheduleTeam'] . ')';
    }
  }
  $arrStaffInTeam[$futureUser['ScheduledPersonID']] = $futureUser['Fullname'] . $currentTeam;
}
uasort($arrStaffInTeam, function ($a, $b) {
  return strcmp(strtolower($a), strtolower($b));
});
$intTeamDetails = getSchedulingTeamDetails($intTeamId);
echo '<h1 class="sr-only">Edit Yearly Allocations</h1>';
echo '<div id="editYearlyAllocationFilters">';
echo '<input type="hidden" value="' . $strCurrentDate . '" id="year_date">';
echo '<input type="hidden" value="' . $arrweeks[0] . '" id="start_week">';
echo '<input type="hidden" value="' . $arrweeks[count($arrweeks) - 1] . '" id="end_week">';
echo '<input type="hidden" value="' . $isShiftleader . '" id="is_shiftleader">';
echo '<table class="tablesmall"  style="width:100%;">';
echo '<tr height="35px" class="yearlyAllocationTR1 yearlyZindex">';
echo '<td colspan="14" class="tableheadersmall bigtextbold">';
echo '&nbsp;&nbsp;Yearly Allocations for <select class="chosen-select" name="schedulingPeopleList" id="schedulingPeopleList" style="width: 200px">';
echo '<option value="">Select scheduled person</option>';
foreach ($arrStaffInTeam as $strSN => $strName) {
  if ($schedulingPersonId == $strSN) {
    echo '<option selected value="' . $strSN . '">' . mb_convert_encoding($strName, 'UTF-8', 'ISO-8859-1') . '</option>';
  } else {
    echo '<option value="' . $strSN . '">' . mb_convert_encoding($strName, 'UTF-8', 'ISO-8859-1') . '</option>';
  }
}
echo '</select>';
if ($schedulingPersonId != 0) {
  echo '<div style="display:inline-block;"><img id="person-team-detail-tooltip" data-id="' . $schedulingPersonId . '" data-title="' . $strFullName . '" class="team-info-icon" src="../../../images/info.png"></div>';
}
echo '</td>';
echo '<td colspan="14" class="tableheadersmall bigtextbold">';
echo '&nbsp;&nbsp;Team <select name="teamId_select" id="teamId_select" style="width: 200px">';
echo '<option value="">Select The Team</option>';
$teamOptions = getSchedulingTeamList($intTeamId, 'allocation-policy', 'viewEditYear');
echo $teamOptions;
echo '</select>';
echo '</td>';
echo '<td colspan="13" class="tableheadersmall bigtextbold">';
echo '<div style="display: inline-block;">
            <label for="from_date">Start date</label>
            <input style="width: 78px;" type="text" value="' . $fromDate . '" id="from_date" name="from_date" autocomplete="off">
        </div>
        <div style="display: inline-block;">
            <label for="to_date">End date</label>
            <input style="width: 78px;" type="text" value="' . $toDate . '" id="to_date" name="to_date" autocomplete="off">
        </div>';
echo '</td>';
echo '<td colspan="2" class="tableheadersmall bigtextbold">';
echo '<img id="reset-edit-yearly" class="handcursor" title="Reset" border="0" src="images/refresh.png" width="25px" height="25px" >';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '<div id="editYearlyAllocationBlock">';
echo '<table class="tablesmall" style="width:100%;">';
if ($schedulingPersonId == 0) {
  echo '<tr><td colspan="43" style="text-align: center">Please select a Scheduled Person</td></tr></table>';
  goto script;
}
$schedulingPersonData = GetScheduledPersonTeamDetailsNew($schedulingPersonId);
$spNetLogin = $schedulingPersonData[$intTeamId]['Login'];
$arrLeave = ReadLeaveForRota($spNetLogin, $startdate, $enddate);
echo '<tr class="yearlyAllocationTR2" style="top:0px">';
echo '<td class="tableheader">';
echo '</td>';

echo '<td colspan="7" class="tableheader handcursor" onclick="javascript:ShowSchedulingTeamYearAllocations(\'' . $intTeamId . '\',\'' . $lastyear . '\')";>';
echo '<br>&lt;&lt;&nbsp' . date('Y', strtotime($lastyear)) . '<br><br>';
echo '</td>';
echo '<td colspan="28" class="tableheader" align="center">';
echo date('Y', strtotime($startdate));
echo '</td>';
echo '<td colspan="7" class="tableheader handcursor" align="right" onclick="javascript:ShowSchedulingTeamYearAllocations(\'' . $intTeamId . '\',\'' . $enddate . '\')";>';
echo date('Y', strtotime($enddate)) . '&nbsp;&gt;&gt;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</td>';
echo '</tr>';


// Start with the table and write the days across the top
echo '<tr class="yearlyAllocationTR3"  style="top:42px;">';
echo '<td class="tableheadersmall"><div class="tableheadersmall"></div></td>';
for ($i = 0; $i <= 5; $i++) {
  for ($ii = 0; $ii <= 6; $ii++) {
    echo '<td class="tableheadersmall"><div class="tableheadersmall smalltextcentre box24x15">' . substr((string) $invdowMap[$ii], 0, 1) . '</div></td>';
  }
}
echo '</tr>';

// Now start on the months
$mdate = $startdate;
while (strtotime($mdate) <= strtotime("-1 month", strtotime($enddate))) {
  echo '<tr>';
  echo '<td rowspan="2" class="tableheadersmall"><div class="smalltextcentre handcursor" onclick="";>';
  echo date("M", strtotime($mdate));
  echo '</div></td>';
  for ($iweek = 0; $iweek <= 5; $iweek++) {
    $currweekstarts = date("Y-m-d", strtotime("+" . ($iweek * 7) . " days", strtotime($mdate)));
    $currweek = bbcweeknumber($currweekstarts);

    echo '<td colspan="7" class="tableheadersmall handcursor" align="center">';
    // Is the month equal to the current one?
    if (date("m", strtotime(datefromweek($currweek))) <= date("m", strtotime($mdate))) {
      echo '<b>' . spinweek($currweek) . '</b>';
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
  for ($i = 1; $i <= $startdaynumber; $i++) {
    echo '<td class="lightcell"></td>';
  }

  $nextmonth = strtotime("+1 month", strtotime($ddate));
  while (strtotime($ddate) < $nextmonth) {
    $weeknumber = bbcweeknumber($ddate);
    $daynumber = $dowMap[date("D", strtotime($ddate))];
    $isRota = isset($arrAllocations['Weeks'][$weeknumber][$daynumber]['IsRota']) ? $arrAllocations['Weeks'][$weeknumber][$daynumber]['IsRota'] : null;
    echo '<td valign="top" class="lightcell day-data-cell" data-date="' . $ddate . '" data-is-rota="' . $isRota . '">';
    // The day of the month
    $isSaturday = date('l', strtotime($ddate)) == 'Saturday' ? true : false;
    echo '<div class="medlightcell smalltextcentre box24x15">';
    echo $isSaturday ? '<b>' : '';
    echo date("d", strtotime($ddate));
    echo $isSaturday ? '</b>' : '';
    echo '</div>';
    if (isset($arrAllocations['Weeks'][$weeknumber][$daynumber]) && $dteFirstDate < $ddate) {
      // If this is a Rota entry and there is leave then show it
      if ($isRota == 1 && isset($arrLeave[$ddate])) {
        $intFirstKey = array_keys($arrLeave[$ddate])[0];
        if ($arrLeave[$ddate][$intFirstKey]['Approved'] == 1) {
            if ((($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] ?? 0) == 0) && (($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"] ?? 0) == 0)) {
                echo '<div class="LeveOK tipClass handcursor" title="Leave Approved">';
            } else {
                echo '<div class="LeaveOK tipClass handcursor" title="Leave Approved" style="background:none; color:#109146;">';
            }
        } else {
            if ($arrLeave[$ddate][$intFirstKey]['ShortNotice'] == 1) {
                echo '<div class="LeaveShortNoticeApplied tipClass handcursor" title="Short Notice">';
            } else {
                if ($arrLeave[$ddate][$intFirstKey]['OverSummer'] == 1) {
                    echo '<div class="LeaveOverSummer tipClass handcursor" title="Over Summer Limit Request">';
                } else {
                    if ($arrLeave[$ddate][$intFirstKey]['isOK'] == 1) {
                        echo '<div class="LeaveOK tipClass handcursor" title="Leave Requested Not Yet Approved">';
                    } else {
                        echo '<div class="LeaveNotOK tipClass handcursor" title="Leave Requested and In Waiting List">';
                    }
                }
            }
        }

        if (($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] ?? 0) == 0 && ($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"] ?? 0) == 0) {
            echo 'Lea';
        } else {
            echo 'PDL';
        }

        echo '</div>';
      } else {

        $dutyname = '';
        // Explode the duty name on spaces and then use the first 2 items....
        $arrdutyname = explode(" ", (string) $arrAllocations['Weeks'][$weeknumber][$daynumber]["Duty"]);

        foreach ($arrdutyname as $item) {
          if ((isset($arrLeave[$ddate]) && reset($arrLeave[$ddate])['Approved'] == 1 && !in_array(strtolower((string) $arrAllocations['Weeks'][$weeknumber][$daynumber]["Duty"]), ['off leave', 'leave', 'u-sick', 'sick'])) || (isset($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"]) && $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] != 0) && ($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"] != 0)) {
            $dutyname .= substr($item, 0, 3) . '<br><span style="color:#109146;">PDL</span>';
            break;
          } else {
            $dutyname .= substr($item, 0, 3) . '<br>';
          }
        }
        if (isset($arrAllocations['Weeks'][$weeknumber][$daynumber]['hiddenDays']) && $arrAllocations['Weeks'][$weeknumber][$daynumber]['hiddenDays'] == 0) {

          $title =  $arrAllocations['Weeks'][$weeknumber][$daynumber]["Duty"];

          if ((isset($arrAllocations['Weeks'][$weeknumber][$daynumber]["StartTime"])) && ($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] == 0) && ($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"] == 0)) {
            $title .= '<br>' . $arrAllocations['Weeks'][$weeknumber][$daynumber]["StartTime"] . '-' . $arrAllocations['Weeks'][$weeknumber][$daynumber]["EndTime"];
          } else if ((isset($arrAllocations['Weeks'][$weeknumber][$daynumber]["StartTime"])) && ($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] != 0) && ($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"] != 0)) {
            $title .= '<br>' . $arrAllocations['Weeks'][$weeknumber][$daynumber]["StartTime"] . '-' . $arrAllocations['Weeks'][$weeknumber][$daynumber]["EndTime"];
            $pdlETime = $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"];
            if ($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"] > $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"]) {
              $pdlETime = 86400 + $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlEndTime"];
            }
            $title .= '<br>PDL: ' . $service->convertSecondsIntoTime($arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"], ':', 'No') . '-' . $service->convertSecondsIntoTime($pdlETime, ':', 'No') . ' | ' . $service->convertSecondsIntoTime($pdlETime - $arrAllocations['Weeks'][$weeknumber][$daynumber]["pdlStartTime"], '.', 'Yes');
          }
        } else {
          $title = '';
        }
        $backGroundColour = $arrAllocations['Weeks'][$weeknumber][$daynumber]["BackColour"];
        if (isset($arrAllocations['Weeks'][$weeknumber][$daynumber]["IsAttention"]) && $arrAllocations['Weeks'][$weeknumber][$daynumber]["IsAttention"] == 1) {
          $backGroundColour = '#FFB6C1';
        } else if (isset($arrAllocations['Weeks'][$weeknumber][$daynumber]["IsAttention"]) && $arrAllocations['Weeks'][$weeknumber][$daynumber]["IsAttention"] == 2) {
          $backGroundColour = '#9370db';
        }
        if (!$intTeamDetails['colourWeek'] && $isRota == 1 && !empty($title) && $title != '-') {
          $backGroundColour = 'purple';
        }
        echo '<div class="smalltextcentre box30x25 tipClass handcursor" title="' . $title . '" style="background-color: ' . $backGroundColour . ';">';
        $style = [];
        if (isset($arrAllocations['Weeks'][$weeknumber][$daynumber]['hiddenDays']) && $arrAllocations['Weeks'][$weeknumber][$daynumber]['hiddenDays'] == 0) {
          $DutyNameTrim = trim(strtoupper((string) $arrAllocations['Weeks'][$weeknumber][$daynumber]["Duty"]));
          $style[] = $isRota == 1 ? 'font-style: italic' : '';
          $style[] = !$intTeamDetails['colourWeek'] && $isRota == 1 ? 'color: white' : '';
          if (($DutyNameTrim == 'SICK') || ($DutyNameTrim == 'U-SICK') || ($DutyNameTrim == '-SICK')) {
            // Set default mustard yellow colour for Sick and U-Sick
            echo '<font style="' . implode(';', $style) . ';" color="#e89e3c">';
            echo $dutyname;
            echo '</font>';
          } else if (($DutyNameTrim == 'LEAVE') || ($DutyNameTrim == 'OFF LEAVE')) {
            // Set default green colour for Leave and OFF Leave
            echo '<font style="' . implode(';', $style) . ';" color="#009900">';
            echo $dutyname;
            echo '</font>';
          } else {
            echo '<font style="' . implode(';', $style) . ';" color="' . $arrAllocations['Weeks'][$weeknumber][$daynumber]["allocateTextColour"] . '">';
            echo $dutyname;
            echo '</font>';
          }
        }
        echo '</div>';
      }
    } else {
      if (isset($arrLeave[$ddate])) {
        $intFirstKey = array_keys($arrLeave[$ddate])[0];

        if ($arrLeave[$ddate][$intFirstKey]['Approved'] == 1) {
          echo '<div class=" LeaveOK  tipClass handcursor" title="Leave Approved">';
        } else {
          if ($arrLeave[$ddate][$intFirstKey]['ShortNotice'] == 1) {
            echo '<div class=" LeaveShortNoticeApplied  tipClass handcursor" title="Short Notice">';
          } else {
            if ($arrLeave[$ddate][$intFirstKey]['OverSummer'] == 1) {
              echo '<div class=" LeaveOverSummer  tipClass handcursor" title="Over Summer Limit Request">';
            } else {
              if ($arrLeave[$ddate][$intFirstKey]['isOK'] == 1) {
                echo '<div class="LeaveOK  tipClass handcursor" title="Leave Requested Not Yet Approved">';
              } else {
                echo '<div class="LeaveNotOK  tipClass handcursor" title="Leave Requested and In Waiting List">';
              }
            }
          }
        }
        echo 'Lea';
        echo '</div>';
      } else {
        echo '<div class=" smalltextcentre box30x25 tipClass handcursor" title="No Rota!">';
        echo '</div>';
      }
    }
    echo '</td>';
    $ddate = date("Y-m-d", strtotime("+1 day", strtotime($ddate)));
  }
  // Now write in spaces at the end of the month
  for ($i = ($daysinmonth + $startdaynumber); $i <= 41; $i++) {
    echo '<td class="lightcell"></td>';
  }
  echo '</tr>';
  $mdate = date("Y-m-d", strtotime("+1 month", strtotime($mdate)));
}
echo '</table>';
echo '</div>';

script:
?>
<script type="text/javascript">
  function maxHeight() {
    $('#editYearlyAllocationBlock').height($(window).height() - 96);
  }
  maxHeight();
  $(window).on('resize', function() {
    maxHeight();
  });
  $('[title]').qtip({
    style: {
      classes: 'qtip-rounded qtip-shadow qtip-dark'
    }
  });
  $('.qtip').click(function(event) {
    api.toggle(false);
  });

  var dateFormat = "dd/mm/yy";
  var fromDate, toDate;
  $(document).ready(function() {
    $('#schedulingPeopleList').change(function() {
      ShowSchedulingTeamYearAllocations(<?php echo $intTeamId; ?>, '<?php echo $strCurrentDate; ?>', $(this).val(), $('#from_date').val(), $('#to_date').val());
      saveFilters();
    });

    $('#teamId_select').change(function() {
      ShowSchedulingTeamYearAllocations($(this).val());
    });

    $('#schedulingPeopleList').chosen({
      no_results_text: "Oops, nothing found!"
    });

    $('#teamId_select').chosen({
      no_results_text: "Oops, nothing found!"
    });

    $('#reset-edit-yearly').click(function() {
      resetFilters();
      ShowSchedulingTeamYearAllocations($('#teamId_select').val());
    });

    //Between dates
    fromDate = $("#from_date")
      .datepicker({
        firstDay: 6,
        changeMonth: true,
        changeYear: true,
        dateFormat: dateFormat,
        minDate: new Date('<?php echo $maxStartDate ?>'),
        maxDate: new Date('<?php echo $lastCreatedWeekDayFriday ?>')
      })
      .on("change", function() {
        toDate.datepicker("option", "minDate", getDate($(this)));
        saveFilters();
        if ($("#to_date").val() == '') {
          $("#to_date").datepicker("setDate", new Date('<?php echo $lastCreatedWeekDayFriday ?>'));
          $("#to_date").trigger("change");
        }
        choseCellsByDate();
      });
    toDate = $("#to_date").datepicker({
        firstDay: 6,
        changeMonth: true,
        changeYear: true,
        dateFormat: dateFormat,
        minDate: new Date('<?php echo $maxStartDate ?>'),
        maxDate: new Date('<?php echo $lastCreatedWeekDayFriday ?>')
      })
      .on("change", function() {
        fromDate.datepicker("option", "maxDate", getDate($(this)));
        choseCellsByDate();
        saveFilters();
      });

    choseCellsByDate();

    //Store scroll position in cookie
    var scrollCookieNameY = 'editYearlyAllocationBlock_table_scroll_position_y';
    var scrollCookieNameX = 'editYearlyAllocationBlock_table_scroll_position_x';
    if (!!$.cookie(scrollCookieNameY)) {
      setTimeout(function() {
        $('#editYearlyAllocationBlock').scrollTop($.cookie(scrollCookieNameY));
        $('#editYearlyAllocationBlock').scrollLeft($.cookie(scrollCookieNameX));
      }, 0);
    }
    $('#editYearlyAllocationBlock').on('scroll', function() {
      $.cookie(scrollCookieNameY, $(this).scrollTop());
      $.cookie(scrollCookieNameX, $(this).scrollLeft());
    });
    initializeSchedulingPersonTeamDetailTooltip();
  });

  function resetMaxDate() {
    $('#from_date').val('');
    $('#to_date').val('');
    fromDate.datepicker("option", "maxDate", new Date('<?php echo $lastCreatedWeekDayFriday ?>'));
    toDate.datepicker("option", "minDate", new Date('<?php echo $maxStartDate ?>'));
  }

  function choseCellsByDate() {
    $('.context-menu-date-range').each(function() {
      $(this).removeClass('context-menu-date-range');
    });
    var from = convertDateFormat($('#from_date').val());
    var to = convertDateFormat($('#to_date').val());
    if ($.trim(from) == '') {
      return false;
    }
    var startDate = new Date(from).getTime();
    var endDate = new Date(to).getTime();
    $('td.day-data-cell').each(function() {
      var cellDate = new Date($(this).attr('data-date')).getTime();
      if (cellDate >= startDate && (cellDate <= endDate || endDate == 0)) {
        $(this).find('.smalltextcentre').first().addClass('context-menu-date-range');
      }
    });
    initializeContextMenu();
  }

  function getDate(element) {
    var date;
    try {
      date = $.datepicker.parseDate(dateFormat, element.val());
    } catch (error) {
      date = null;
    }
    return date;
  }

  function initializeContextMenu() {
    $.contextMenu({
      selector: '.context-menu-date-range',
      items: {
        'unassign_duty': {
          icon: "unassign",
          name: "Unassign Duties",
          visible: function(key, options) {
            return (new Date(getDate($('#from_date'))).getTime() < new Date('<?php echo date('Y-m-d') . ' 00:00:00'; ?>').getTime() ? false : true);
          },
          callback: function(key, opt) {
            editYearlyUnassignDuties();
          }
        },
        'delete_duty': {
          icon: "delete",
          name: "Delete Duties",
          visible: function(key, options) {
            return (new Date(getDate($('#from_date'))).getTime() < new Date('<?php echo date('Y-m-d')  . ' 00:00:00'; ?>').getTime() ? false : true);
          },
          callback: function(key, opt) {
            editYearlyDeleteDuties();
          }
        },
        'apply_rota_pattern': {
          icon: "copy",
          name: "Apply Rota Pattern",
          callback: function(key, opt) {
            editYearlyApplyRotaDuties();
          }
        }
      }
    });
  }

  function editYearlyUnassignDuties() {
    if (new Date(getDate($('#from_date'))).getTime() < new Date().getTime()) {
      setFromDateCurrent();
    }
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/modals/edit-yearly-unassign.php',
      data: {
        'start_week': $('#start_week').val(),
        'end_week': $('#end_week').val(),
        'is_shiftleader': $('#is_shiftleader').val(),
        'teamId': $('#teamId_select').val(),
        'scheduledPersonId': $('#schedulingPeopleList').val(),
        'start_date': $('#from_date').val(),
        'end_date': $('#to_date').val()
      },
      success: function(data) {
        $.facebox(data);
      },
      error: function(data) {}
    });
  }

  function editYearlyDeleteDuties() {
    if (new Date(getDate($('#from_date'))).getTime() < new Date().getTime()) {
      setFromDateCurrent();
    }
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/modals/edit-yearly-delete.php',
      data: {
        'start_week': $('#start_week').val(),
        'end_week': $('#end_week').val(),
        'is_shiftleader': $('#is_shiftleader').val(),
        'teamId': $('#teamId_select').val(),
        'scheduledPersonId': $('#schedulingPeopleList').val(),
        'start_date': $('#from_date').val(),
        'end_date': $('#to_date').val()
      },
      success: function(data) {
        $.facebox(data);
      },
      error: function(data) {}
    });
  }

  function editYearlyApplyRotaDuties() {
    $.ajax({
      type: 'POST',
      url: 'page-includes/allocations/modals/edit-yearly-apply-rota.php',
      data: {
        'start_week': $('#start_week').val(),
        'end_week': $('#end_week').val(),
        'is_shiftleader': $('#is_shiftleader').val(),
        'teamId': $('#teamId_select').val(),
        'scheduledPersonId': $('#schedulingPeopleList').val(),
        'start_date': $('#from_date').val(),
        'end_date': $('#to_date').val()
      },
      success: function(data) {
        $.facebox(data);
      },
      error: function(data) {}
    });
  }

  function setFromDateCurrent() {
    $("#from_date").datepicker("setDate", '<?php echo date('d/m/Y'); ?>');
    $("#from_date").trigger("change");
  }

  function convertDateFormat(dateString) {
    var parts = dateString.split('/');
    return parts[2] + '-' + parts[1] + '-' + parts[0];
  }

  function saveFilters() {
    var teamId = $('#teamId_select').val();
    $.cookie('edit_yearly_allocations_scheduled_person_id' + teamId, $('#schedulingPeopleList').val());
    $.cookie('edit_yearly_allocations_start_date' + teamId, $('#from_date').val());
    $.cookie('edit_yearly_allocations_end_date' + teamId, $('#to_date').val());
    $.cookie('edit_yearly_allocations_year' + teamId, $('#year_date').val());
  }

  function resetFilters() {
    var teamId = $('#teamId_select').val();
    $.removeCookie('edit_yearly_allocations_start_date' + teamId);
    $.removeCookie('edit_yearly_allocations_end_date' + teamId);
  }

  function initializeSchedulingPersonTeamDetailTooltip() {
    $('#person-team-detail-tooltip').each(function() {
      $(this).qtip("destroy");
      $('#scheduling-team-history-table').remove();
      $(this).qtip({
        content: {
          text: function(event, api) {
            var reqData = {
              'scheduledPersonId': api.elements.target.attr('data-id'),
              'scheduledPersonTitle': api.elements.target.attr('data-title')
            };
            $.ajax({
                type: "post",
                data: reqData,
                url: 'page-includes/allocations/edit-yearly/edit-yearly-scheduled-person-team-detail.php'
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
  }
</script>