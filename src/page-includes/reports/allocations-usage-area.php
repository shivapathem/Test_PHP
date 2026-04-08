<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsstaffing.php';
include_once __DIR__ . '/../../page-includes/allocations/weekly/service/AllocationService.php';
include_once __DIR__ . '/../../page-includes/admin/divisions/process/classDivisionalAdmin.php';

$areaobj = new ClassDivisionalAdmin;

$arrArea = $areaobj->getDivisionsListBasedOnAreaReportRole();
$arrTeams = GetReportsConfig(0);

$intAreaID = isset($_POST['areaId']) ? $_POST['areaId'] : 0;
$intTeamID = 0;
if ($intAreaID == 0) {
  $strAreaName = 'All Areas';
}
if (isset($_POST['refpage'])) {
  $intRefPage = $_POST['refpage'];
} else {
  $intRefPage = 0;
}

// if $intRefPage = 1 don't show money

// ########### A few settings that affect page layout ##########################################
if (isset($_SESSION['screenwidth'])) {
  $intScreenWidth = $_SESSION['screenwidth'];
} else {
  $intScreenWidth = 1600;
}

$intDateHeight = 35;
$intStatusHeight = 10;
$intRowHeight = 45;
$intStaffRowHeight = 35;
$intNamesRowHeight = 20;
$intNamesMinRowHeight = 30;
$intNamesWidth = 270;
$intDutyWidth = round($intScreenWidth - $intNamesWidth - 80) / 7;
$intTableWidth = round($intNamesWidth + ($intDutyWidth * 7));
$intShowAll = 1;

// ######################### END  ###########################################################################

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ? $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

// ################# Work Out the week we are to view #######################################################
if (isset($_POST['WeekNumber'])) {
  // Passed a week Number?
  $intWeekNumber = $_POST['WeekNumber'];
} else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
  } else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));
  }
}

$dteStartDate = datefromweek($intWeekNumber);
$dteEndtDate = date('Y-m-d', strtotime($dteStartDate . ' + 6 days'));
$arrHolidays = array();
$arrHolidays = GetHolidaysByYear(date("Y", strtotime($dteStartDate)));
$intPreviousWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate . ' - 7 days')));
$intNextWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate . ' + 7 days')));
$_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;
$arrStaffBreaks = array();
$arrFreelancers = array();

$weekNumber = '';
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : date('Y-m-01', strtotime('-1 month'));
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : date('Y-m-t', strtotime('-1 month'));

$scheduledPersonID = isset($_POST['scheduledPersonID']) ? $_POST['scheduledPersonID'] : 0;
$startDateTime = new DateTime($startDate);
$endDateTime = new DateTime($endDate);
$interval = $startDateTime->diff($endDateTime);
$totalDays = $interval->days + 1;

$arrFreelancers = ReadAllocationsFreelancersArea($weekNumber, $weekNumber, $arrTeams, $arrStaffBreaks, $intTeamID, $userID, $intAreaID, $startDate, $endDate, $scheduledPersonID);
$arrFreelancerSearch = ReadAllocationsFreelancersAreaSearch($weekNumber, $weekNumber, $arrTeams, $arrStaffBreaks, $intTeamID, $userID, $intAreaID, $startDate, $endDate);

$arrFreelancers['Appearance'] = $arrFreelancers['Appearance'] ?? [];
$arrFreelancers['Hours'] = $arrFreelancers['Hours'] ?? [];

$intTotalDays = 0;
foreach ($arrFreelancers['Appearance'] as $date => $appearances) {
  if (is_array($appearances) && isset($appearances[0]) && $appearances['IsAreaUnderUser'] == 1) {
    $intTotalDays += $appearances[0];
  }
}

$intTotalFreelanceHours = 0;
foreach ($arrFreelancers['Hours'] as $date => $hours) {
  if (is_array($hours) && isset($hours['IsAreaUnderUser']) && $hours['IsAreaUnderUser'] == 1) {
    if (isset($hours[0])) {
      $intTotalFreelanceHours += $hours[0];
    }
  }
}
echo '<h1 class="sr-only">Freelance Usage by Area</h1>';
echo '<form id="dateForm">';
echo '<table class="tablegreysmallnoborder" id="maintable" border="1" width="' . $intTableWidth . 'px">';
echo '<tr height="40px">';
echo '<td class="medtextbold">';
echo '<td width="80px" class="medtextbold">Start Date</td>';
echo '<td width="110px" class="medtextbold">';
echo '<input type="text" id="startDate" name="startDate" value="' . date("d/m/Y", strtotime($startDate)) . '">';
echo '</td>';
echo '</td>';

echo '<td class="medtextbold">';
echo '<td width="70px" class="medtextbold">End Date</td>';
echo '<td width="110px" class="medtextbold">';
echo '<input type="text" id="endDate" name="endDate" value="' . date("d/m/Y", strtotime($endDate)) . '">';
echo '</td>';
echo '</td>';

echo '<td width="60px" align="left" class="medtextbold handcursor">';
echo '<button type="submit" id="goButton">Go</button>';
echo '</td>';

echo '<td style="vertical-align:middle">';
if (count($arrArea) == 1) {
  echo '<select name="areaId" id="areaId" class="chosen-select" disabled>';
  echo '<option value="' . $arrArea[0]['DivisionID'] . '" >' . $arrArea[0]['DivisionName'] . '</option>';
  echo '</select>';
} else {
  echo '<select name="areaId" id="areaId" class="chosen-select">';
  echo '<option value=0 >All My Areas</option>';
  if (!empty($arrArea)) {
    foreach ($arrArea as $areaLists) {
      $isSelected = ($intAreaID == $areaLists['DivisionID']) ? 'selected="selected"' : '';
      echo '<option value="' . $areaLists['DivisionID'] . '" ' . $isSelected . ' >' . $areaLists['DivisionName'] . '</option>';
    }
  } else {
    echo '<option value="">No area Assigned</option>';
  }
  echo '</select>';
}
echo '</td>';
echo '<td class="medtextbold" nowrap style="vertical-align:middle">';
echo '&nbsp&nbspTotal Approximate Freelance Cost: &pound;' . number_format((round($intTotalFreelanceHours) * $intFreelanceCostPerHour), 0, '.', ',');
echo '</td>';
echo '<td style="vertical-align:middle" class=" medtextbold handcursor" onclick="javascript:CreateFreelanceExcel(' . $intAreaID . ', \'' . $startDate . '\', \'' . $endDate . '\', \'' . $scheduledPersonID . '\')">Excel Export &nbsp;&nbsp;';
echo '<img width="24px" height="24px" border="0" src="images/excel.png" style="vertical-align:middle"></img></td>';
echo '</tr> ';
echo '</table>';
echo '</form>';
// End the header

// The holder
echo '<div style="position: relative">';
// The top Left Corner
echo '<div style="position: absolute; width:' . $intNamesWidth . 'px; height:' . ($intDateHeight - 1) . 'px; left:0px; top:0px" id="fixed" class="dotw">';
echo 'Start Date: ' . spindate($startDate) . ' <br>';
echo 'End Date: &nbsp&nbsp' . spindate($endDate);
echo '</div>';
// END the holder

// The days of the week....
echo '<div style="overflow: hidden; position: absolute; width:900px; height:' . ($intDateHeight - 1) . 'px; left:' . $intNamesWidth . 'px; top:0px" id="weeklytop' . $intAreaID . '">';
$holidayflag = 0;
for ($i = 0; $i < $totalDays; $i++) {
  $strCurrDate = date("Y-m-d", strtotime("+" . $i . " days", strtotime($startDate)));
  $dayOfWeek = date("l", strtotime($strCurrDate));
  if ($strCurrDate == date("Y-m-d")) {
    echo '<div style="width:' . $intDutyWidth . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:0px; height:' . ($intDateHeight - 1) . 'px" class="dotwlight handcursor" id="headfshift' . $i . '">';
  } else {
    echo '<div style="width:' . $intDutyWidth . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:0px; height:' . ($intDateHeight - 1) . 'px" class="dotw handcursor" id="headfshift' . $i . '">';
  }
  echo $dayOfWeek . '<br>' . spindate($strCurrDate);
  $holidayDescription = getHolidayDescription($strCurrDate, $arrHolidays);
  if ($holidayDescription != '') {
    $holidayflag = 1;
    echo "<br>(" . $holidayDescription . ")";
  }
  echo '</div>';
}
echo '</div>';
// END the days of the week....
// The totals
// Now the Freelance ataff....

// The top of the holder
echo '<div id="freelancetop-holder' . $intAreaID . '" style="position: absolute; width:' . $intNamesWidth . 'px; height:' . ($intDateHeight + 9) . 'px; left:0px; display:flex; flex-direction: column; justify-content: start" class="dotw">';
if ($intTotalFreelanceHours > 0) {
  echo '<span style="margin-bottom: 5px">Total: ' . round($intTotalFreelanceHours) . ' Hrs (' . $intTotalDays . ' Days)</span>';
}

echo '<select name="freelanceSearch" id="freelanceSearch" class="freelanceSearch-select" style="margin-top: 5px; margin-bottom: 5px" data-placeholder="All Freelancers">';
echo '<option value=0 >All Freelancers</option>';
if (!empty($arrFreelancerSearch['Duties'])) {
  foreach ($arrFreelancerSearch['Duties'] as $sn => $value) {
    echo '<option value="' . $value['ScheduledPersonID'] . '">' . $value["FullName"] . '</option>';
  }
}
echo '</select>';

echo '</div>';

echo '<div style="overflow: hidden; position: absolute; width:900px; height:' . ($intDateHeight + 10) . 'px; left:' . $intNamesWidth . 'px; top:0px" id="freelancetop' . $intAreaID . '">';
for ($i = 0; $i < $totalDays; $i++) {
  $strCurrDate = date("Y-m-d", strtotime("+" . $i . " days", strtotime($startDate)));
  echo '<div style="width:' . $intDutyWidth . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:0px; height:' . ($intDateHeight + 10) . 'px" class="dotw handcursor" id="fshift' . $i . '">';

  if (isset($arrFreelancers['Hours'][$strCurrDate][0]) && $arrFreelancers['Hours'][$strCurrDate]['IsAreaUnderUser'] == 1) {
    echo round($arrFreelancers['Hours'][$strCurrDate][0]) . ' Hrs (' . $arrFreelancers['Appearance'][$strCurrDate][0] . ' Days)';
  }
  echo '</div>';
}
echo '</div>';

//############################## The FREELANCE names down the left side

echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:' . $intNamesWidth . 'px;  left:0px; top:' . ($intDateHeight + 15) . 'px" class="whitebackground" id="weeklyfreelancenames' . $intAreaID . '">';
$counter = 0;
if (isset($arrFreelancers['Duties'])) {
  foreach ($arrFreelancers['Duties'] as $sn => $value) {
    echo '<div class="names handcursor" style="position: absolute; width:100%; height:' . ($intStaffRowHeight - 1) . 'px; left:0px; top:' . ($counter * $intStaffRowHeight) . 'px">';
    if (!is_null($value["StaffColour"])) {
      echo '<b>' . $value["FullName"];
    } else {
      echo '<b>' . $value["FullName"];
    }
    echo '</b><br>';
    echo $value["SortCode"];
    echo '</div>';
    $counter++;
  }
}
echo '</div>';

// The Duties
// The duties in the div
echo '<div style="overflow-y: scroll; position: absolute; width:900px;left:' . $intNamesWidth . 'px; top:' . $intDateHeight . 'px" id="weeklyfreelanceduties' . $intAreaID . '" class="whitebackground">';
$counter = 0;
if (isset($arrFreelancers['Duties'])) {
  foreach ($arrFreelancers['Duties'] as $sn => $value) {
    for ($i = 0; $i < $totalDays; $i++) {
      $strCurrDate = date("Y-m-d", strtotime("+" . $i . " days", strtotime($startDate)));
      $teamname = '';
      if (isset($value[$strCurrDate][0])) {
        $teamname = $value[$strCurrDate][0]['TeamName'];
        $currdate = datefromweek($strCurrDate, $i);
        // Are there any Duty comments?
        $hascomments = 0;
        if ($value[$strCurrDate][0]["DutyComments"] != 0 || $value[$strCurrDate][0]["PersonComments"] != 0) {
          $hascomments = 1;
        }
        if ($value[$strCurrDate][0]['IsAreaUnderUser'] == 0) {
          $dutyColor = 'gray';
        } else {
          $dutyColor = '#000';
        }
        if (($value[$strCurrDate][0]['StartTime'] >= 0) && ($value[$strCurrDate][0]['EndTime'] > 0)) {
          $strTitle = '<b>' . $teamname . '</b><br>' . $value[$strCurrDate][0]['StartTime'] . '-' . $value[$strCurrDate][0]['EndTime'] . '<br>' . $value[$strCurrDate][0]['DurationLessMeal'];
        } else {
          $strTitle = '<b>' . $teamname . '</b><br>' . $value[$strCurrDate][0]['DurationLessMeal'];
        }
        echo '<div title="' . $strTitle . '" style="width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($counter * $intStaffRowHeight) . 'px; height:' . ($intStaffRowHeight - 1) . 'px; color:' . $dutyColor . '" class="DutyCellWorking handcursor" id="dutyshift_' . $counter . '_' . $i . '">';
        echo $value[$strCurrDate][0]["Duty"];
        echo '</div>';
      } else {
        echo '<div style="width:' . ($intDutyWidth - 1) . 'px; position:absolute; left:' . ($i * $intDutyWidth) . 'px; top:' . ($counter * $intStaffRowHeight) . 'px; height:' . ($intStaffRowHeight - 1) . 'px" class="DutyCellNotWorking handcursor" id="dutyshift_' . $counter . '_' . $i . '">';
        echo '</div>';
      }
    }
    $counter++;
  }
}
echo '</div>';
echo '</div>';
function getHolidayDescription($date, $holidayLists)
{
  $date = date("Y-m-d", strtotime($date));
  if (!empty($holidayLists)) {
    foreach ($holidayLists as $holiday) {
      if (($holiday['Date']) == $date) {
        return $holiday['Event'];
      }
    }
  }
  return false;
}
?>
<div id="dialog-freelance-email<?php echo $intAreaID ?>" title="Information!" style="display:none;">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This will send you an email of
    the Freelancers working in <?php echo $strAreaName ?>.<br>Do you wish to continue?</p>
</div>

<script language="JavaScript" type="text/javascript">

  $(document).ready(function () {
    $(".chosen-select").chosen({
      no_results_text: "Oops, nothing found!",
      width: "190px"
    });

    var freelanceSearchWidth = <?php echo ($intNamesWidth - 8); ?>;
    $(".freelanceSearch-select").chosen({
      no_results_text: "Oops, nothing found!",
      width: freelanceSearchWidth
    });

    var freelanceSearchValue = $("#freelanceSearch").val();
    $("#freelanceSearch").val(freelanceSearchValue).trigger("chosen:updated");

    $("#goButton").on("click", function (event) {
      event.preventDefault();
      var startDate = $("#startDate").val();
      var endDate = $("#endDate").val();
      var areaId = $("#areaId").val();
      var freelanceSearchValue = $("#freelanceSearch").val();
      var startDateParts = startDate.split('/');
      var endDateParts = endDate.split('/');
      var startDateFormatted = startDateParts[2] + '-' + startDateParts[1] + '-' + startDateParts[0];
      var endDateFormatted = endDateParts[2] + '-' + endDateParts[1] + '-' + endDateParts[0];

      var startDateTimestamp = Date.parse(startDateFormatted);
      var endDateTimestamp = Date.parse(endDateFormatted);

      if (startDateTimestamp > endDateTimestamp) {
        customAlert("Start Date can’t be after End Date.");
        return;
      }
      
      $.post("page-includes/reports/allocations-usage-area.php", {
        startDate: startDateFormatted,
        endDate: endDateFormatted,
        areaId: areaId,
        scheduledPersonID: freelanceSearchValue
      },
        function (data, status) {
          $('#content').html(data);
          $("#freelanceSearch").val(freelanceSearchValue).trigger("chosen:updated");
        });
    })
  });

  $("#freelanceSearch").change(function () {
    var selectedValue = $(this).val();
    if (selectedValue == 0) {
      fetchFreelancerData(null);
    } else {
      fetchFreelancerData(selectedValue);
    }
  });

  function fetchFreelancerData(ScheduledPersonID) {
    var startDate = $("#startDate").val();
    var endDate = $("#endDate").val();
    var areaId = $("#areaId").val();
    var startDateParts = startDate.split('/');
    var endDateParts = endDate.split('/');
    var startDateFormatted = startDateParts[2] + '-' + startDateParts[1] + '-' + startDateParts[0];
    var endDateFormatted = endDateParts[2] + '-' + endDateParts[1] + '-' + endDateParts[0];
    $.post("page-includes/reports/allocations-usage-area.php", {
      startDate: startDateFormatted,
      endDate: endDateFormatted,
      areaId: areaId,
      scheduledPersonID: ScheduledPersonID
    },
      function (data, status) {
        $('#content').html(data);
        // Retain the selected value in the dropdown
        $("#freelanceSearch").val(ScheduledPersonID).trigger("chosen:updated");
      });
  }


  // If cookie is set, scroll to the position saved in the cookie.
  if ($.cookie("W-vscrollud<?php echo $intAreaID ?>") !== null) {
    $("#weeklynames<?php echo $intAreaID ?>").scrollTop($.cookie("W-vscrollud<?php echo $intAreaID ?>"));
    $("#weeklyduties<?php echo $intAreaID ?>").scrollTop($.cookie("W-vscrollud<?php echo $intAreaID ?>"));
    $("#weeklyduties<?php echo $intAreaID ?>").scrollLeft($.cookie("W-hscrollud<?php echo $intAreaID ?>"));
  }
  // When scrolling happens....
  $("#weeklyduties<?php echo $intAreaID ?>").on("scroll", function () {
    // Set a cookie that holds the scroll position.
    $.cookie("W-vscrollud<?php echo $intAreaID ?>", $("#weeklyduties<?php echo $intAreaID ?>").scrollTop());
    $.cookie("W-hscrollud<?php echo $intAreaID ?>", $("#weeklyduties<?php echo $intAreaID ?>").scrollLeft());
    $('#weeklynames<?php echo $intAreaID ?>').scrollTop($(this).scrollTop());
    $('#weeklytop<?php echo $intAreaID ?>').scrollLeft($(this).scrollLeft());
    $('#freelancetop<?php echo $intAreaID ?>').scrollLeft($(this).scrollLeft());
    $('#weeklytotals<?php echo $intAreaID ?>').scrollLeft($(this).scrollLeft());
  });

  $('#weeklynames<?php echo $intAreaID ?>').on('scroll', function () {
    $('#weeklyduties<?php echo $intAreaID ?>').scrollTop($(this).scrollTop());
  });

  if ($.cookie("W-vscrollfl<?php echo $intAreaID ?>") !== null) {
    $("#weeklyfreelancenames<?php echo $intAreaID ?>").scrollTop($.cookie("W-vscrollfl<?php echo $intAreaID ?>"));
    $("#weeklyfreelanceduties<?php echo $intAreaID ?>").scrollTop($.cookie("W-vscrollfl<?php echo $intAreaID ?>"));
    $("#weeklyfreelanceduties<?php echo $intAreaID ?>").scrollLeft($.cookie("W-hscrollfl<?php echo $intAreaID ?>"));
    $('#weeklyfreelancetop<?php echo $intAreaID ?>').scrollLeft($.cookie("W-hscrollfl<?php echo $intAreaID ?>"));
  }

  // When scrolling happens....
  $("#weeklyfreelanceduties<?php echo $intAreaID ?>").on("scroll", function () {
    // Set a cookie that holds the scroll position.
    $.cookie("W-vscrollfl<?php echo $intAreaID ?>", $("#weeklyfreelanceduties<?php echo $intAreaID ?>").scrollTop());
    $.cookie("W-hscrollfl<?php echo $intAreaID ?>", $("#weeklyfreelanceduties<?php echo $intAreaID ?>").scrollLeft());
    $('#weeklyfreelancenames<?php echo $intAreaID ?>').scrollTop($(this).scrollTop());
    $('#weeklyfreelancetop<?php echo $intAreaID ?>').scrollLeft($(this).scrollLeft());
    $('#weeklytop<?php echo $intAreaID ?>').scrollLeft($(this).scrollLeft());
    $('#freelancetop<?php echo $intAreaID ?>').scrollLeft($(this).scrollLeft());
  });

  $('#weeklyfreelancenames<?php echo $intAreaID ?>').on('scroll', function () {
    $('#weeklyfreelanceduties<?php echo $intAreaID ?>').scrollTop($(this).scrollTop());
  });

  ResizeWeeklyGrids();

  $(window).resize(function () {
    ResizeWeeklyGrids();
  })
  function ResizeWeeklyGrids() {
    var offset = ($("#weeklytop<?php echo $intAreaID ?>").length > 0) ? ($("#weeklytop<?php echo $intAreaID ?>").offset().top) + ($("#content").offset().top) : 0;
    var windowwidth = $(window).width() - 40 - <?php echo $intNamesWidth ?>;
    var holidayflag = <?php echo $holidayflag; ?>;
    if (windowwidth > <?php echo ($intDutyWidth * 7) ?>) {
      // windowwidth = <?php echo $intDutyWidth ?> * 7;
    }
    var widowheight = $(window).height() - offset;
    var counter = <?php echo $counter; ?>;
    var totalDays = <?php echo $totalDays; ?>;
    $("#maintable").width($(window).width() - 43);
    $("#maintable").css("margin-left", 1);
    $("#weeklyduties<?php echo $intAreaID ?>").width(windowwidth + 20);
    $("#weeklyduties<?php echo $intAreaID ?>").height(widowheight / 2);
    $("#weeklynames<?php echo $intAreaID ?>").height(widowheight / 2);
    $("#weeklytop<?php echo $intAreaID ?>").width(windowwidth);
    $("#weeklytotals<?php echo $intAreaID ?>").width(windowwidth);
    $("#freelancetop<?php echo $intAreaID ?>").width(windowwidth);
    $("#weeklyfreelanceduties<?php echo $intAreaID ?>").width(windowwidth + 10).height(widowheight - 30);
    $("#weeklyfreelancenames<?php echo $intAreaID ?>").height(widowheight - 30);
    $("#fixed").css("z-index", 1);
    $("#weeklytop<?php echo $intAreaID ?>").css("z-index", 1);
    if (holidayflag == 1) {
      var newheight = <?php echo $intDateHeight; ?> + 10;
      $("#weeklytop<?php echo $intAreaID ?>").height(newheight);
      $("#fixed").css({ 'height': <?php echo ($intDateHeight) + 10; ?> + 'px' });
      $("#freelancetop<?php echo $intAreaID ?>").css({ 'top': '45px' });
      $("#freelancetop-holder<?php echo $intAreaID ?>").css({ 'top': '46px' });
      $('#weeklyfreelanceduties<?php echo $intAreaID ?>').css({ 'top': <?php echo ($intDateHeight * 2) + 21; ?> + 'px' });
      $("#weeklyfreelancenames<?php echo $intAreaID ?>").css({ 'top': <?php echo ($intDateHeight * 2) + 21; ?> + 'px' });
    } else {
      $("#fixed").css({ 'height': <?php echo ($intDateHeight - 1); ?> + 'px' });
      $("#freelancetop<?php echo $intAreaID ?>").css({ 'top': '35px' });
      $("#freelancetop-holder<?php echo $intAreaID ?>").css({ 'top': '35px' });
      $('#weeklyfreelanceduties<?php echo $intAreaID ?>').css({ 'top': <?php echo ($intDateHeight * 2) + 10; ?> + 'px' });
      $("#weeklyfreelancenames<?php echo $intAreaID ?>").css({ 'top': <?php echo ($intDateHeight * 2) + 10; ?> + 'px' });
    }

    for (let j = 0; j <= counter; j++) {
      for (let k = 0; k <= totalDays; k++) {
        let cellwidth = windowwidth / 7;
        let cellleft = k * cellwidth;
        if (holidayflag == 1) {
          $("#headfshift" + k).css("height", newheight);
          $("#fshift" + k).css("top", 1);
          $("#fshift" + k).css("z-index", 1);
        }
        $("#headfshift" + k).css("width", cellwidth);
        $("#headfshift" + k).css("left", cellleft);
        $("#fshift" + k).css("width", cellwidth);
        $("#fshift" + k).css("left", cellleft);
        $("#dutyshift_" + j + "_" + k).css("width", cellwidth - 1);
        $("#dutyshift_" + j + "_" + k).css("left", cellleft + 1);
      }
    }
  }

  $("#startDate").datepicker({
    dateFormat: 'dd/mm/yy',
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    changeMonth: true,
    changeYear: true
  });

  $("#endDate").datepicker({
    dateFormat: 'dd/mm/yy',
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    changeMonth: true,
    changeYear: true
  });

  $("#areaId").change(function () {
    var startDate = $("#startDate").val();
    var endDate = $("#endDate").val();
    var areaId = $(this).val();
    var startDateParts = startDate.split('/');
    var endDateParts = endDate.split('/');
    var startDateFormatted = startDateParts[2] + '-' + startDateParts[1] + '-' + startDateParts[0];
    var endDateFormatted = endDateParts[2] + '-' + endDateParts[1] + '-' + endDateParts[0];

    $.post("page-includes/reports/allocations-usage-area.php", {
      startDate: startDateFormatted,
      endDate: endDateFormatted,
      areaId: areaId
    },
      function (data, status) {
        $('#content').html(data);
      });
  });

  function ShowAllocUsage(areaId, WeekNumber) {
    $.post("page-includes/reports/allocations-usage-area.php", {
      areaId: areaId,
      WeekNumber: WeekNumber,
    },
      function (data, status) {
        $('#content').html(data);
      })
  }

  function CreateFreelanceEmail(weeknumber) {
    $("#dialog-freelance-email<?php echo $intAreaID ?>").dialog(
      {
        width: 600,
        buttons: {
          "Yes": function () {
            $(this).dialog("close");
            $.post("page-includes/reports/allocations-weekly-freelancers-email.php", {
              id: weeknumber,
              department: <?php echo $intAreaID ?>
            },
              function (data, status) {
              });
          },
          "No": function () {
            $(this).dialog("close");
          },
        }
      }
    );
  }

  function CreateFreelanceExcel(intAreaID, startDate, endDate, scheduledPersonID) {
    window.open("page-includes/reports/allocations-weekly-freelancers-area-create-excel.php?areaId=" + intAreaID + "&startDate=" + startDate + "&endDate=" + endDate + "&scheduledPersonID=" + scheduledPersonID);
  }

  $('[title]').qtip({
    position: {
      my: 'top center',
      at: 'bottom center',
      viewport: $(window)
    },
    style: 'qtip-rounded qtip-shadow qtip-light'
  }); 
</script>