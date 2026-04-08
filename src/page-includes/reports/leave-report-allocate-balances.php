<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/genericfunctions.php';
$arrAllocLeaveTypes = GetLeaveAllocateTypes();
$intTeamID = $_POST['teamID'] ?? 0;
$strTeamName = GetTeamNameFromID($intTeamID);

if (!empty($_POST['year'])) {
    $intLeaveYear = $_POST['year'];
} elseif (!empty($_SESSION['allocations']['leave']['curentleaveyear'])) {
    $intLeaveYear = $_SESSION['allocations']['leave']['curentleaveyear'];
} else {
    $intLeaveYear = date("Y", strtotime("-3 months"));
}
$_SESSION['allocations']["leave"]['curentleaveyear'] = $intLeaveYear;
$startdate = $intLeaveYear."-04-01";
$enddate = date("Y-m-d", strtotime("+1 Year", strtotime($startdate)));

$intCurrentYear = date("Y", strtotime("-4 months"));
$intPercentofYear = (GetPercentageOfYear($intLeaveYear, $intCurrentYear));

$arrLeaveCredits = GetLeaveCreditsSummary($intTeamID, $intLeaveYear, $arrAllocLeaveTypes);
$arrLeaveTaken = GetLeaveApprovedTakenSummary($intTeamID, $intLeaveYear, $arrAllocLeaveTypes);

// ################################################################## Total The credits
$arrCredits = [];
foreach ($arrLeaveCredits as $strStaffNumber => $arrLeaveCredit) {
  foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    if (isset($arrCredits[$LTID])) {
      $arrCredits[$LTID] = $arrCredits[$LTID] +  $arrLeaveCredit['Leave'][$arrAllocLeaveType['AllocName']];
    }else {
      $arrCredits[$LTID] = $arrLeaveCredit['Leave'][$arrAllocLeaveType['AllocName']];
    }
  }
}
if (isset($arrLeaveTaken)) {
  foreach ($arrLeaveTaken as $strStaffNumber => $arrLeaveTakenPerson) {
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      if (isset($arrLeaveTakenPerson[$LTID])) {
        if (isset($arrLeaveTakenTotals[$LTID])) {
          $arrLeaveTakenTotals[$LTID] = $arrLeaveTakenTotals[$LTID] + $arrLeaveTakenPerson[$LTID];
        } else {
          $arrLeaveTakenTotals[$LTID] = $arrLeaveTakenPerson[$LTID];
        }
      }
    }
  }
}
echo '<table class="tablesmall compact stripe" width="100%">';
echo '<tr>';
echo '<td  width="200px" class="tableheadersmall handcursor" onclick="javascript:ShowLeaveReportBalances('.$intTeamID.','.($intLeaveYear - 1).')";>';
echo '<br>&lt;&lt;&nbsp;'.($intLeaveYear - 1).'<br><br>';
echo '</td>';
echo '<td  width="200px" align="right" class="tableheadersmall handcursor" onclick="javascript:ShowLeaveReportBalances('.$intTeamID.','.($intLeaveYear + 1).')";>';
echo '<br>'.($intLeaveYear + 1).'&nbsp;&gt;&gt;<br><br>';
echo '</td>';
echo '<td class="tableheadersmall medtextboldcentre">';
echo '<br>Leave Summary for \''.$strTeamName.'\'<br>Leave Year '.$intLeaveYear.'/'.($intLeaveYear + 1).' - '.$intPercentofYear.'% through the Leave Year<br><br>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '<br>';
echo '<table class="tablesmalltidy" width="100%">';
echo '<tr>';
echo '<th>';
echo '</th>';
// Loop through the $arrAllocLeaveTypes and use the Include and calc first
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 1) {
    echo '<th width="150px">';
    echo $arrAllocLeaveType['Description'];
    echo '</th>';
  }
}

echo '<th width="150px">';
echo 'Combined Total';
echo '</th>';
echo '<th>';
echo '&nbsp;';
echo '</th>';

foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 0) {
    echo '<th width="150px">';
    echo $arrAllocLeaveType['Description'];
    echo '</th>';
  }
}

echo '</tr>';
echo '<tr>';
echo '<td>';
echo 'Total Credits';
echo '</td>';
$intCombTotal = 0;

foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 1) {
        $formattedCredit = number_format(($arrCredits[$LTID] ?? 0), 2, '.', '');
        echo "<td>{$formattedCredit}</td>";
        $intCombTotal += $arrCredits[$LTID] ?? 0;
    }
}

echo '<td>';
echo number_format($intCombTotal, 2, '.', '');
echo '</td>';
echo '<th>';
echo '</th>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 0) {
    echo '<td>';
    echo number_format($arrCredits[$LTID] ?? 0, 2, '.', '');
    echo '</td>';
  }
}
echo '</tr>';
echo '<tr>';
  // The Leave taken
echo '<td>';
echo 'Total Leave Granted';
echo '</td>';
$intCombTotalTaken = 0;
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 1) {
    echo '<td>';
    if (isset($arrLeaveTakenTotals[$LTID])) {
      echo number_format(($arrLeaveTakenTotals[$LTID] ?? 0), 2, '.', '');
      $intCombTotalTaken = $intCombTotalTaken + $arrLeaveTakenTotals[$LTID];
    } else {
      echo '0';
    }
    echo '</td>';
  }
}
echo '<td>';
echo number_format($intCombTotalTaken, 2, '.', '');
echo '</td>';

echo '<th>';
echo '&nbsp;';
echo '</th>';

foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 0) {
    echo '<td>';
    if (isset($arrLeaveTakenTotals[$LTID])) {
      echo number_format($arrLeaveTakenTotals[$LTID], 2, '.', '');
    } else {
      echo '0';
    }
    echo '</td>';
  }
}
echo '</tr>';

echo '<tr>';
echo '<td>';
echo 'Total Leave still to book';
echo '</td>';

foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 1) {
    echo '<td>';
    if (isset($arrLeaveTakenTotals[$LTID])) {
      echo number_format(($arrCredits[$LTID] - $arrLeaveTakenTotals[$LTID]), 2, '.', '');
    } else {
      echo '0';
    }
    echo '</td>';
  }
}
echo '<td>';
echo number_format(($intCombTotal - $intCombTotalTaken), 2, '.', '');
echo '</td>';
echo '<th>';
echo '&nbsp;';
echo '</th>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 0) {
    echo '<td>';
    if (isset($arrLeaveTakenTotals[$LTID])) {
      echo number_format(($arrCredits[$LTID] - $arrLeaveTakenTotals[$LTID]), 2, '.', '');
    }
    echo '</td>';
  }
}
echo '</tr>';
echo '<tr>';
echo '<td>';
echo 'Percentage taken/booked this year';
echo '</td>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 1) {
    echo '<td>';
    if (isset($arrCredits[$LTID])) {
      if ($arrCredits[$LTID] == 0) {
        echo 'No Leave Credits';
      } else {
        echo round((100 - ($arrCredits[$LTID] - ($arrLeaveTakenTotals[$LTID] ?? 0)) / $arrCredits[$LTID] * 100), 1).'%';
     }
    }
    echo '</td>';
  }
}

echo '<td>';
if ($intCombTotal == 0) {
  echo 'No Leave Credits';
} else {
  echo round((100 - ($intCombTotal - $intCombTotalTaken) / $intCombTotal * 100), 1).'%';
}
echo '</td>';

echo '<th>';
echo '&nbsp;';
echo '</th>';

foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['IncludeInReports'] == 1 && $arrAllocLeaveType['CalcInReports'] == 0) {
    echo '<td>';
    if (isset($arrCredits[$LTID])) {
      if ($arrCredits[$LTID] == 0) {
        echo 'No Leave Credits';
      } else {
        echo round((100 - ($arrCredits[$LTID] - $arrLeaveTakenTotals[$LTID]) / $arrCredits[$LTID] * 100), 1).'%';
     }
    }
    echo '</td>';
  }
}
echo '</tr>';
echo '</table>';
?>