<?php
date_default_timezone_set('UTC');
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/helpers.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/skillsfunctions.php';
include_once __DIR__ . '/../../page-includes/teamskills/process/classTeamskills.php';
include_once __DIR__ . '/../../function-includes/common/classCommonDBFunctions.php';
$commonObj = new classCommonDBFunctions();
$teamskillobj = new classTeamskills();
$pdo = OpenDBLinkA7();
$intTeamID = $_REQUEST['teamId'] ?? 0;
$intDutyID = $_REQUEST['id'] ?? 0;
$strDate = $_REQUEST['date'] ?? '';
$bbcweeknumberInfo = $commonObj->GetWeekNoAndIDayByDateFromTimeDim($strDate);
$intWeekNumber = $bbcweeknumberInfo['ixYearWeek'] ?? 0;
if (isset($bbcweeknumberInfo['ixDayInWeek'])) {
  $intDayOfWeek  = intval($bbcweeknumberInfo['ixDayInWeek']);
}
try {
  $row = '';
  // Get the Duty and name
  $query = "SELECT duty, description FROM skills_duties (NOLOCK) WHERE (id = $intDutyID)";
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  logger()->critical('DB Error', (array) $e);
}

if (!empty($row)) {
  $duty = $row['duty'];
  $dutyname = $row['duty'] . ' ' . $row['description'];
  $dutyletter = substr($dutyname, 0, 1);
} else {
  $duty = '';
  $dutyname = '';
  $dutyletter = '';
}

try {
  $arrAllocateData = '';
  $sql = "SELECT a.DutyName, a.SchedulingPersonID as ScheduledPersonID, ud.UD_DisplayName AS fullname FROM Allocations_Publish as a (NOLOCK) INNER JOIN UserDetails as ud (NOLOCK) ON ud.UD_UserID = a.SchedulingPersonID WHERE a.WeekNumber=? AND a.iDay=? AND a.SchedulingTeamId=? AND a.isPublished=1 AND a.isActive=1";

  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1, $intWeekNumber, PDO::PARAM_INT);
  $stmt->bindParam(2, $intDayOfWeek, PDO::PARAM_INT);
  $stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
  $stmt->execute();
  $arrAllocateData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  logger()->critical('DB Error', (array) $e);
}

if (empty($arrAllocateData)) {    // ## NEW CODE
  $intAllocationsPresent = 0;
} else {
  $intAllocationsPresent = 1;
  try {
    $arrDutyCount = '';
    $sql2 = "SELECT SchedulingPersonID,Count(Allocations_Publish.DutyName) AS dutycount FROM Allocations_Publish (NOLOCK) WHERE WeekNumber >=? AND iDay=? AND SchedulingTeamId=? AND isPublished=1 AND isActive=1 GROUP BY SchedulingPersonID HAVING (Not SchedulingPersonID IS Null)";
    $stmt = $pdo->prepare($sql2);
    $stmt->bindParam(1, $intWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDayOfWeek, PDO::PARAM_INT);
    $stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $arrDutyCount = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }

  $arrRequests = GetDailyRequestsBasic($strDate, $intTeamID);
  try {
    $arrStaffSimple = [];
    $query = "SELECT	ud.UD_DisplayName AS fullname, ud.UD_UserID as ScheduledPersonID FROM ScheduledPersonTeam_LINK sptl (NOLOCK) INNER JOIN UserDetails ud (NOLOCK) ON ud.UD_UserID = sptl.ScheduledPersonID WHERE (sptl.TeamID = ?) AND sptl.isActive=1 ORDER BY UD_DisplayName";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
    $stmt->execute();
    $arrStaffSimple = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
  // Now make this into the $arrStaff
  foreach ($arrStaffSimple as $key => $StaffInfo) {
    $arrStaff[$StaffInfo['ScheduledPersonID']]['FullName'] = $StaffInfo['fullname'];
  }

  $arrCanDoDuty = json_decode($teamskillobj->GetStaffCanDoDuty($intDutyID));

  if (isset($arrCanDoDuty)) {
    foreach ($arrCanDoDuty as $key => $StaffInfo) {
      if (isset($arrStaff[$StaffInfo->ScheduledPersonID])) {
        $arrStaff[$StaffInfo->ScheduledPersonID]['cando'] = 1;
      }
    }
  }

  // Now loop through the data from Allocate and add it.....
  if (isset($arrAllocateData)) {
    foreach ($arrAllocateData as $value) {
      $arrStaff[$value['ScheduledPersonID']]['duty'] = $value['DutyName'];
    }
  }

  if (isset($arrRequests)) {
    foreach ($arrRequests as $strStaffNumber => $strRequestType) {
      if (isset($arrStaff[$strStaffNumber])) {
        $arrStaff[$strStaffNumber]['request'] = $strRequestType;
      }
    }
  }
}
echo '<table width="100%">';
echo '<tr>';
echo '<td valign="top">';
if ($intAllocationsPresent == 1) {
  // First list the people on the same type of duty
  echo '<table class="tablesmall">';
  echo '<tr>';
  echo '<td colspan="4" class="tableheadersmall medtextboldcentre">';
  echo 'Staff on a similar shift';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="tableheadersmall" width="350px">';
  echo 'Name';
  echo '</td>';
  echo '<td class="tableheadersmall" width="450px">';
  echo 'Duty';
  echo '</td>';
  echo '<td class="tableheadersmall" width="250px">';
  echo 'Requests';
  echo '</td>';
  echo '<td class="tableheadersmall" width="150px">';
  echo 'Count';
  echo '</td>';
  echo '</tr>';
  // Lots of loops
  // First staff on the same type of shift and who can do this duty
  if (!empty($arrStaff)) {
    foreach ($arrStaff as $sn => $value) {
      // We have returned all the allocations so if the fullname isn't set they don't work in this dept
      if (isset($value['FullName']) && isset($value['duty'])) {
        $thisdutyname = $value['duty'];
        $dc = calcdutycount($arrDutyCount, $sn);

        if (substr($thisdutyname, 0, 1) == $dutyletter && (isset($value['cando']))) {
          if ($dc == 0) {
            $class = "VeryLightGreen";
          } else {
            if (substr($thisdutyname, 0, strlen($duty) + 1) == $duty . ' ') {
              $class = "LightYellow";
            } else {
              $class = "LightGreen";
            }
          }

          echo '<tr>';
          echo '<td class="' . $class . '">';
          echo $value['FullName'];
          echo '</td>';
          echo '<td class="' . $class . '">';
          echo $thisdutyname;
          echo '</td>';
          echo '<td class="' . $class . '">';
          if (isset($value['request'])) {
            echo $value['request'];
          }
          echo '</td>';
          echo '<td class="' . $class . '" align="center">';
          echo $dc;
          echo '</td>';
          echo '</tr>';
        }
      }
    }
  }

  // Now the rest of the duties....
  echo '<tr>';
  echo '<td colspan="4" class="tableheadersmall medtextboldcentre">';
  echo 'Staff on a other shifts';
  echo '</td>';
  echo '</tr>';
  // First staff on the same type of shift and who can do this duty
  if (!empty($arrStaff)) {
    foreach ($arrStaff as $sn => $value) {
      // We have returned all the allocations so if the FullName isn't set they don't work in this dsept
      if (isset($value['FullName']) && isset($value['duty'])) {
        $thisdutyname = $value['duty'];
        $dc = calcdutycount($arrDutyCount, $sn);
        if ($dc == 0) {
          $class = "VeryLightGreen";
        } else {
          $class = "LightGreen";
        }
        if (substr($thisdutyname, 0, 1) != $dutyletter && (isset($value['cando']))) {
          echo '<tr>';
          echo '<td class="' . $class . '">';
          echo $value['FullName'];
          echo '</td>';
          echo '<td class="' . $class . '">';
          echo $thisdutyname;
          echo '</td>';
          echo '<td class="' . $class . '">';
          if (isset($value['request'])) {
            echo $value['request'];
          }
          echo '</td>';
          echo '<td class="' . $class . '" align="center">';
          echo $dc;
          echo '</td>';
          echo '</tr>';
        }
      }
    }
  }
  echo '</table>';
} else {
  echo '<div class="tableheadersmall medtextboldcentre" id="skillsinfo" style="width: 100%">
  <br>Unable to find any allocations for this date<br><br></div>';
}

function calcdutycount($arrDutyCount, $sn)
{
  $dc = 0;
  foreach ($arrDutyCount as $dcvalue) {
    if ($dcvalue['SchedulingPersonID'] == $sn) {
      $dc = $dcvalue['dutycount'];
      break;
    }
  }
  return ($dc);
}
