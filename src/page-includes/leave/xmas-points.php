<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/xmasfunctions.php';
include_once '../../page-includes/admin/process/classSchedulingTeam.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';

//call the class object
$commonDBObj = new classCommonDBFunctions();
$schedteamobj = new classSchedulingTeam();

$intTeamID = $_REQUEST['teamid'];
$arrDates = array('23', '24', '25', '26', '27', '28', '29', '30', '31', '1');

if (isset($_REQUEST['sortorder'])) {
  $intSortOrder = $_REQUEST['sortorder'];
} else {
  $intSortOrder = 0;
}
$intEndYear = date("Y") - 1;
$teamDetails = json_decode(getScheduleTeamsdetailsByID($intTeamID), true);

$arrPoints = json_decode(getXmasPointByTeam($intTeamID), true);

// ############################################## First get the people we are interested in
$arrStaff = $arrText = [];
$arrStaffResult = json_decode($commonDBObj->getAllHomeScheduledPeopleListsByTeamID($intTeamID), true);
if (!empty($arrStaffResult)) {
  foreach ($arrStaffResult as $row) {
    $arrStaff[$row['ScheduledPersonID']]['name'] = $row['FullName'];
  }
  $varScheduledpersonIDS = implode(",", array_column($arrStaffResult, 'ScheduledPersonID'));
}

$currentYear = date("Y");
$arrAllocations = ReadAllocationsForXmas($intTeamID, $currentYear);

for ($year = ($intEndYear - 2); $year <= $intEndYear; $year++) {
  // ############################## Get the week numbers for the dates and add the pints from the array above
  for ($curdate = strtotime($year . '-12-23'); $curdate <= strtotime(($year + 1) . '-01-01'); $curdate = $curdate + 86400) {
    $dotm = date("j", $curdate);
    $currweek = bbcweeknumber(date("Y-m-d", $curdate));
    $dates[date("Y-m-d", $curdate)]['week'] = $currweek;
    $dates[date("Y-m-d", $curdate)]['day'] = dayofweek(date("Y-m-d", $curdate), $currweek);
    // add the points....
    if (isset($arrPoints[$dotm])) {
      $dates[date("Y-m-d", $curdate)]['amounts'] = $arrPoints[$dotm];
    } else {
      $dates[date("Y-m-d", $curdate)]['amounts'] = 0;
    }
  }

  $nextyear = $year + 1;
  $StartWeekNumber = bbcweeknumber(date("$year-12-23"));
  $EndWeekNumber = bbcweeknumber(date("$nextyear-01-01"));
  $arrStaff = $schedteamobj->SchedulingTeamExtraXmasPointByYear($year, $intTeamID, $arrStaff);
}

// ############################## Loop through the allocations and add the duty name, duration and times to the staff array
if (is_array($arrAllocations) && count($arrAllocations) > 0) {
  foreach ($arrAllocations as $sn => $allocweek) {
    if (isset($arrStaff[$sn])) {
      foreach ($dates as $date => $values) {
        if (isset($allocweek[$values['week']][$values['day']])) {
          if (is_array($allocweek[$values['week']][$values['day']]['Duty'])) {
            foreach ($allocweek[$values['week']][$values['day']]['Duty'] as $dutyData) {
              $arrStaff[$sn]['dates'][$date]['duty'] = $dutyData["Duty"];
              if (isset($dutyData["StartTime"]) && ($dutyData["StartTime"] != '00:00') || ($dutyData["EndTime"] != '00:00')) {
                $arrStaff[$sn]['dates'][$date]['starttime'] = $dutyData["StartTime"];
                $arrStaff[$sn]['dates'][$date]['endtime'] = $dutyData['EndTime'];
              } else {
                if (isset($dutyData['duration'])) {
                  if (strpos($dutyData["Duty"], 'leave') !== false) {
                    $arrStaff[$sn]['dates'][$date]['duration'] = $dutyData['Duration'];
                  } else {
                    $arrStaff[$sn]['dates'][$date]['duration'] = 0;
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}


// now loop through the staff, match the date with the ammounts
echo '<h1 class="sr-only">Christmas Points</h1>';
echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
echo '<br><h2>Showing Christmas Points for ' . $teamDetails['schedulingTeamName'] . '</h2><br>';
echo '</div>';
echo '<div class="tableheadersmall" style="position: relative; width:100%">';
echo '<table>';
echo '<tr>';
echo '<td>Sort Order</td>';
echo '<td>';
echo '<select class="chosen-select" size="1" name="PointsOrder" onchange="javascript:ShowXmasPoints(' . $intTeamID . ',value)";>';
echo '<option value="0"';
if ($intSortOrder == 0) {
  echo ' selected';
}
echo '>Names</option>';
echo '<option value="1"';
if ($intSortOrder == 1) {
  echo ' selected';
}
echo '>Points</option>';
echo '</select>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '</div>';
if (!empty($arrStaff)) {
  foreach ($arrStaff as $sn => $content) {
    $arrStaff[$sn]['basictotal'] = 0;
    $arrStaff[$sn]['additionaltotal'] = 0;
    $arrStaff[$sn]['total'] = 0;
    $basictotal = 0;
    $additionaltotal = 0;
    if (isset($content['dates'])) {
      $basictotal = 0;
      $additionaltotal = 0;
      foreach ($content['dates'] as $date => $allocation) {
        if (isset($allocation['duration'])) {
          $duration = $allocation['duration'];
        } else {
          $duration = 0;
        }
        $dotm = date("j", strtotime($date));
        if ($duration > 0 || strtoupper(substr($allocation['duty'], 0, 1)) == "Z" || isset($allocation['starttime'])) {
          if (isset($arrPoints[$dotm]['Basic'])) {
            $arrStaff[$sn]['dates'][$date]['basicpoints'] = $arrPoints[$dotm]['Basic'];
            $basictotal = $basictotal + $arrPoints[$dotm]['Basic'];
          } else {
            $arrStaff[$sn]['dates'][$date]['basicpoints'] = 0;
          }
        } else {
          $arrStaff[$sn]['dates'][$date]['basicpoints'] = 0;
        }
        $arrStaff[$sn]['dates'][$date]['additionalpoints'] = 0;
        // now do the additional points
        // Not for sickness
        if (stripos($allocation['duty'], 'sick') === false) {
          if (isset($allocation['starttime']) && isset($arrPoints[$dotm]['Times'])) {
            $secstart = timetoseconds($allocation['starttime']);
            $secend = timetoseconds($allocation['endtime']);
            if ($secend < $secstart) {
              $secend = $secend + 86400;
            }
            foreach ($arrPoints[$dotm]['Times'] as $times) {
              //if ($secstart <= $times['StartTime'] && $secend >= $times['EndTime']) {
              if ($times['StartTime'] < $secend  && $times['EndTime'] > $secstart) {
                $arrStaff[$sn]['dates'][$date]['additionalpoints'] = $arrStaff[$sn]['dates'][$date]['additionalpoints'] + $times['Points'];
              }
            }
            if ($arrStaff[$sn]['dates'][$date]['additionalpoints'] > $arrPoints[$dotm]['Limit']) {
              $arrStaff[$sn]['dates'][$date]['additionalpoints'] = $arrPoints[$dotm]['Limit'];
              $arrStaff[$sn]['dates'][$date]['capped'] = 1;
            }
            $additionaltotal = $additionaltotal + $arrStaff[$sn]['dates'][$date]['additionalpoints'];
          }
        }
      }
    }
    // Now do any additional amounts
    if (isset($arrStaff[$sn]['additional'])) {
      foreach ($arrStaff[$sn]['additional'] as $year => $values) {
        $additionaltotal = $additionaltotal + $values['points'];
      }
    }
    $arrStaff[$sn]['basictotal'] = $basictotal;
    $arrStaff[$sn]['additionaltotal'] = $additionaltotal;
    $arrStaff[$sn]['total'] = $basictotal + $additionaltotal;
  }
}

// Sort it....
$arrsort = [];
if (!empty($arrStaff)) {
  foreach ($arrStaff as $sn => $content) {
    $arrsort[$content['total'] . '.' . rand()] = $sn;
  }
}
if ($intSortOrder == 1) {
  krsort($arrsort);
}

echo '<div id="accordion">';
echo '<h3>';
echo 'Key';
echo '</h3>';
echo '<div>';
echo '<table width="100%" class="smalltable">';
echo '<tr>';
foreach ($arrDates as $day) {
  echo '<td class="datecell" width="11%">';
  echo $day;
  echo '</td>';
}
echo '</tr>';

echo '<tr>';
foreach ($arrDates as $day) {
  //foreach ($arrDates as $day) {
  echo '<td valign="top" class="lightcell">';
  if (isset($arrPoints[$day]['Basic'])) {
    echo 'Basic Points ' . $arrPoints[$day]['Basic'];
  }
  echo '</td>';
}
echo '</tr>';

echo '<tr>';
//foreach ($arrPoints as $day => $values) {
foreach ($arrDates as $day) {
  echo '<td valign="top" class="lightcell">';
  if (isset($arrPoints[$day]['Times'])) {
    echo 'Additional Points<br>';
    foreach ($arrPoints[$day]['Times'] as $times) {
      if ($times['Points'] == 1) {
        $points = 'Point';
      } else {
        $points = 'Points';
      }
      echo gmdate("H:i", $times['StartTime']) . '-' . gmdate("H:i", $times['EndTime']) . ' ' . $times['Points'] . ' ' . $points;
      if ($times['StartTime'] >= 86400) {
        echo ' *';
      }
      echo '<br>';
    }
  }
  echo '</td>';
}
echo '</tr>';
echo '</table>';
echo '</div>';
foreach ($arrsort as $sn) {
  $content = $arrStaff[$sn];

  echo '<h3>';
  echo $content['name'];
  echo '&nbsp;(Total Points ';
  echo $content['total'] . ')';
  $arrText[$sn] = $content['total'];
  echo '</h3>';
  echo '<div>';

  echo '<table width="100%" class="smalltable">';
  // Go from 23rd -> 1st Header
  echo '<thead>';
  echo '<tr>';
  echo '<td class="datecell">';
  echo '&nbsp;';
  echo '</td>';

  for ($curdate = strtotime($year . '-12-23'); $curdate <= strtotime(($year + 1) . '-01-01'); $curdate = $curdate + 86400) {
    $tdate = date("d M", $curdate);
    echo '<th width="10%" colspan="3" class="datecell medtextbold">' . $tdate . '</th>';
  }
  echo '<th class="datecell medtextbold">Additional</th>';
  echo '<th class="datecell medtextbold">Totals</th>';
  echo '</tr>';
  echo '</thead>';

  echo '<tbody>';
  //for ($year = 2016; $year >= 2014; $year--) {
  for ($year = $intEndYear; $year >= ($intEndYear - 2); $year--) {
    // Go from 24th -> 1st
    echo '<tr>';
    echo '<td class="datecell">';
    echo $year;
    echo '</td>';
    $subtotal = 0;
    for ($curdate = strtotime($year . '-12-23'); $curdate <= strtotime(($year + 1) . '-01-01'); $curdate = $curdate + 86400) {
      $tdate = date("Y-m-d", $curdate);
      if (isset($content['dates'][$tdate])) {
        echo '<td valign="top" class="lightcell">';
        //echo substr(trim($content['dates'][$tdate]['duty']), 0, 10);
        echo $content['dates'][$tdate]['duty'];

        if (isset($content['dates'][$tdate]['starttime'])) {
          echo '<br>' . $content['dates'][$tdate]['starttime'] . '-' . $content['dates'][$tdate]['endtime'];
        } else {
          if (isset($content['dates'][$tdate]['duration'])) {
            if ($content['dates'][$tdate]['duration'] != 0) {
              echo '<br>' . $content['dates'][$tdate]['duration'] . ' Hours';
            }
          }
        }
        if (isset($content['dates'][$tdate]['comments'])) {
          echo '<br>' . $content['dates'][$tdate]['comments'];
        }

        echo '</td>';
        $subtotal = $subtotal + $content['dates'][$tdate]['basicpoints'];
        echo '<td align="center" class="lightcell"><b>' . $content['dates'][$tdate]['basicpoints'] . '</b></td>';
        $subtotal = $subtotal + $content['dates'][$tdate]['additionalpoints'];
        echo '<td align="center" class="lightcell"><b>' . $content['dates'][$tdate]['additionalpoints'];

        if (isset($content['dates'][$tdate]['capped'])) {
          echo '<font color="#FF0000">*</font>';
        }

        echo '</b></td>';
      } else {
        echo '<td valign="top" class="lightcell">???</td>';
        echo '<td align="center" class="lightcell"><b>0</b></td>';
        echo '<td align="center" class="lightcell"><b>0</b></td>';
      }
    }

    if (isset($arrStaff[$sn]['additional'][$year])) {
      //if ($mystaffnumber == $sn || $admin == 5) {
      echo '<td align="center" class="lightcell tip handcursor" qtip-content="' . $arrStaff[$sn]['additional'][$year]['notes'] . '">';
      //}
      //else {
      //   echo '<td align="center" class="lightcell handcursor">';
      //}

      echo $arrStaff[$sn]['additional'][$year]['points'];
      $subtotal = $subtotal + $arrStaff[$sn]['additional'][$year]['points'];
    } else {
      echo '<td align="center" class="lightcell">';
    }
    echo '</td>';
    echo '<td align="center" class="lightcell"><b>';
    echo $subtotal;
    echo '</b></td>';

    echo '</tr>';
  }
  echo '</tbody>';
  echo '</table>';
  echo '</div>';
}
echo '</div>';
SaveXmasPoints($intTeamID, $arrText);
?>
<script>
  $(document).ready(function() {
    $(".chosen-select").chosen({
      no_results_text: "Oops, nothing found!",
      width: "200px"
    });

    $(function() {
      $("#accordion").accordion({
        collapsible: true,
        heightStyle: content
      });
    });
    $('.tip').qtip({
      content: {
        text: function(event, api) {
          // Retrieve content from custom attribute of the $('.selector') elements.
          return $(this).attr('qtip-content');
        }
      },
      position: {
        my: 'top right', // Position my top left...
        at: 'bottom middle', // at the bottom right of...
        viewport: $(window),
        adjust: {
          y: -3
        }
      },
      show: {
        solo: true
      },
      style: 'qtip-rounded qtip-shadow qtip-dark'
    });
  })
</script>