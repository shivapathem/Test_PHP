<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsstaffing.php';
include_once __DIR__ . '/../../page-includes/allocations/weekly/service/AllocationService.php';
include_once __DIR__ . '/../../function-includes/user-scheduling-team-list.php';

$intTeamID = $_POST['teamId'] ?? 0;
$arrTeams = GetReportsConfig(0);
if ($intTeamID == 0) {
  $strTeamName = 'All Teams';
} else {
   $strTeamName = $arrTeams[$intTeamID]['DepartmentName'];
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
$intStatusHeight= 10;
$intRowHeight = 45;
$intStaffRowHeight = 35;
$intNamesRowHeight = 20;
$intNamesMinRowHeight = 30;
$intNamesWidth = 270;
$intDutyWidth = round($intScreenWidth - $intNamesWidth - 80) / 7;
$intTableWidth = round($intNamesWidth + ($intDutyWidth * 7));
$intShowAll = 1;

// ######################### END  ###########################################################################

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

// ################# Work Out the week we are to view #######################################################
if (isset($_POST['WeekNumber'])) {
  // Passed a week Number?
  $intWeekNumber = $_POST['WeekNumber'];
} else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];
  }  else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));
  }
}

$dteStartDate = datefromweek($intWeekNumber);
$dteEndtDate = date('Y-m-d', strtotime($dteStartDate. ' + 6 days'));
$arrHolidays=array();
$arrHolidays = GetHolidaysByYear(date("Y", strtotime($dteStartDate)));
$intPreviousWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate. ' - 7 days')));
$intNextWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate. ' + 7 days')));
$_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;
$arrStaffBreaks = array();
$arrFreelancers=array();
$arrAllocations = ReadAllocationsStaffing($intWeekNumber, $intWeekNumber, $arrTeams, $arrStaffBreaks, $intTeamID);
$arrFreelancers = ReadAllocationsFreelancers($intWeekNumber, $intWeekNumber, $arrTeams, $arrStaffBreaks, $intTeamID,$userID);
$intTotalDays = 0;
for ($i = 0; $i <=6; $i++) {
  if (isset($arrFreelancers['Appearance'][$intWeekNumber][$i])) {
   $intTotalDays = $intTotalDays + $arrFreelancers['Appearance'][$intWeekNumber][$i];
  }
}

$intTotalFreelanceHours = 0;
for ($i = 0; $i <=6; $i++) {
  if (isset($arrFreelancers['Hours'][$intWeekNumber][$i])) {
   $intTotalFreelanceHours = $intTotalFreelanceHours + $arrFreelancers['Hours'][$intWeekNumber][$i];
  }
}
echo '<link rel="stylesheet" href="mvc-app/node_modules/select2/dist/css/select2.min.css">';
echo '<script src="mvc-app/node_modules/select2/dist/js/select2.min.js"></script>';

echo '<h1 class="sr-only">Freelance Usage</h1>';
echo '<table class="tablegreysmallnoborder" id="maintable" border="1" width="'.$intTableWidth.'px">';
echo '<tr height="40px">';
if ($intRefPage == 1) {
  echo '<td width="140px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span tabindex="0" role="button" onclick=\'javascript:ShowAllocationsUsageSched("'.$intPreviousWeek.'")\' onkeydown="if(event.key===\'Enter\'||event.key===\' \') {event.preventDefault(); javascript:ShowAllocationsUsageSched(\"'.$intPreviousWeek.'\")}">&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</span></td>';
  echo '<td width="140px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span tabindex="0" role="button" onclick=\'javascript:ShowAllocationsUsageSched("'.$intNextWeek.'")\' onkeydown="if(event.key===\'Enter\'||event.key===\' \') {event.preventDefault(); javascript:ShowAllocationsUsageSched(\"'.$intNextWeek.'\")}">Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</span></td>';
} else {
  echo '<td width="140px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span tabindex="0" role="button" onclick=\'javascript:ShowAllocUsage('.$intTeamID.',"'.$intPreviousWeek.'")\' onkeydown="if(event.key===\'Enter\'||event.key===\' \') {event.preventDefault(); javascript:ShowAllocUsage('.$intTeamID.',\"'.$intPreviousWeek.'\")\' }">&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</span></td>';
  echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span tabindex="0" role="button" onclick=\'javascript:ShowAllocUsage('.$intTeamID.',"'.$intNextWeek.'")\' onkeydown="if(event.key===\'Enter\'||event.key===\' \') {event.preventDefault(); javascript:ShowAllocUsage('.$intTeamID.',\"'.$intNextWeek.'\")\' }">Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</span></td>';
}
echo '<td width="110px" class="medtextbold" align="right">Choose a Date</td>';
echo '<td width="55px" class="medtextbold handcursor" align="left"><input type="hidden" id="datepicker'.$intTeamID.'"><button type="button" id="datepicker-button-'.$intTeamID.'" style="display: flex; align-items: center; border: none; padding: 0px; background: transparent; cursor: pointer;"><img src="images/calendar.gif" alt="Calendar" title="Calendar" style="width: 25px; height: 25px;"></button></td>';
echo '<td style="vertical-align:middle">';
$teamOptions = getSchedulingTeamList($intTeamID, 'reports-policy', 'viewBasicReports');
echo '<select name="teamId" id="teamId" class="select2" tabindex="0" onchange="ShowAllocUsage(this.value,'.$intWeekNumber.')">';
echo '<option value=0 >All My Teams</option>';

if (!empty($teamOptions)) {
  echo $teamOptions;
} else {
  echo '<option value="">No Team Assigned</option>';
}
echo '</select>';
echo ' <span class="medtextbold" nowrap style="vertical-align:middle">&nbsp; for Week '.spinweek($intWeekNumber).'</span></td>';
echo '</td>';
echo '<td class="medtextbold" nowrap style="vertical-align:middle">';
  echo '&nbsp&nbspTotal Approximate Freelance Cost: &pound;'.number_format((round($intTotalFreelanceHours) * $intFreelanceCostPerHour), 0, '.', ',');
echo '</td>';
echo '<td style="vertical-align:middle" class=" medtextbold handcursor" tabindex="0" role="button" onclick="javascript:CreateFreelanceExcel('.$intTeamID.','.$intWeekNumber.')" onkeydown="if(event.key===\'Enter\'||event.key===\' \') {event.preventDefault(); javascript:CreateFreelanceExcel('.$intTeamID.','.$intWeekNumber.')}">Excel Export &nbsp;&nbsp;';
echo '<img width="24px" height="24px" border="0" src="images/excel.png" style="vertical-align:middle" alt="Excel Export"></img></td>';
echo '</tr> ';
echo '</table>';

// End the header

  // The holder
  echo '<div style="position: relative">';
  // The top Left Corner
  echo '<div style="position: absolute; width:'.$intNamesWidth.'px; height:'.($intDateHeight - 1).'px; left:0px; top:0px" id="fixed"  class="dotw">';
  echo 'Week: '.spinweek($intWeekNumber);
  echo '</div>';
  // END the holder

  // The days of the week....
  echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($intDateHeight - 1).'px; left:'.$intNamesWidth.'px; top:0px" id="weeklytop'.$intTeamID.'">';
  $holidayflag=0;
    for ($i = 0; $i <=6; $i++) {
      $strCurrDate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
    if ($strCurrDate == date("Y-m-d")) {
        echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - 1).'px" class="dotwlight handcursor" id="headfshift'.$i.'">';
      }
      else {
        echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - 1).'px" class="dotw handcursor" id="headfshift'.$i.'">';
      }
      echo $invdowMap[$i] .'<br>'.spindate($strCurrDate);
    $holidayDescription = getHolidayDescription($strCurrDate, $arrHolidays);
      if ($holidayDescription!='') {
      $holidayflag=1;
        echo "<br>(".$holidayDescription.")";
      }
      echo '</div>';
    }
  echo '</div>';
  // END the days of the week....
  // The totals
  // Now the Freelance ataff....

  // The top of the holder

  echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($intDateHeight - 1).'px; left:0px; top:0px" id="freelancetop'.$intTeamID.'">';
  echo '<div style="position: absolute; width:'.$intNamesWidth.'px; height:'.($intDateHeight - 1).'px; left:0px; top:0px" id="fixed1"  class="dotw">';
  if ($intTotalFreelanceHours >0){
  echo 'Total: ' .round($intTotalFreelanceHours).' Hrs ('.$intTotalDays.' Days)';
    }
  echo '</div>';

    for ($i = 0; $i <=6; $i++) {
      echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth + $intNamesWidth).'px; top:0px; height:'.($intDateHeight - 1).'px" class="dotw handcursor" id="fshift'.$i.'">';
      if (isset($arrFreelancers['Hours'][$intWeekNumber][$i])) {
        echo round($arrFreelancers['Hours'][$intWeekNumber][$i]).' Hrs ('.$arrFreelancers['Appearance'][$intWeekNumber][$i].' Days)';
      }
      echo '</div>';
    }
  echo '</div>';

//############################## The FREELANCE names down the left side
  echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:'.$intNamesWidth.'px;  left:0px; top:'.$intDateHeight.'px" class="whitebackground" id="weeklyfreelancenames'.$intTeamID.'">';
  $counter = 0;
  if (isset($arrFreelancers['Duties'])) {
    foreach ($arrFreelancers['Duties'] as $sn => $value) {
      echo '<div class="names handcursor" style="position: absolute; width:100%; height:'.($intStaffRowHeight - 1).'px; left:0px; top:'.($counter * $intStaffRowHeight).'px">';
      if(!is_null($value["StaffColour"])) {
        echo '<b>'.$value["FullName"];
      }
      else {
        echo '<b>'.$value["FullName"];
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
echo '<div style="overflow-y: scroll; position: absolute; width:900px;left:'.$intNamesWidth.'px; top:'.$intDateHeight.'px" id="weeklyfreelanceduties'.$intTeamID.'" class="whitebackground">';
  $counter = 0;
  if (isset($arrFreelancers['Duties'])) {
  foreach ($arrFreelancers['Duties'] as $sn => $value) {
  for ($i = 0; $i <=6; $i++) {
    $teamname='';
    if (isset($value[$intWeekNumber][$i])) {
    $teamname= $value[$intWeekNumber][$i]['TeamName'];
    $currdate = datefromweek($intWeekNumber, $i);
        // Are there any Duty comments?
        $hascomments = 0;
        if($value[$intWeekNumber][$i]["DutyComments"] != 0 || $value[$intWeekNumber][$i]["PersonComments"] != 0) {
          $hascomments = 1;
        }
    if (($value[$intWeekNumber][$i]['StartTime'] >= 0) && ($value[$intWeekNumber][$i]['EndTime'] > 0)) {
      $strTitle = '<b>'.$teamname.'</b><br>'.$value[$intWeekNumber][$i]['StartTime'].'-'.$value[$intWeekNumber][$i]['EndTime'].'<br>'.$value[$intWeekNumber][$i]['DurationLessMeal'];
    } else {
      $strTitle = '<b>'.$teamname.'</b><br>'.$value[$intWeekNumber][$i]['DurationLessMeal'];
    }
        echo '<div title="'.$strTitle.'" style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intStaffRowHeight).'px; height:'.($intStaffRowHeight - 1).'px" class="DutyCellWorking handcursor" id="dutyshift_'. $counter.'_'.$i.'">';
        echo $value[$intWeekNumber][$i]["Duty"];
        echo '</div>';
      }
      else {
        echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intStaffRowHeight).'px; height:'.($intStaffRowHeight - 1).'px" class="DutyCellNotWorking handcursor" id="dutyshift_'. $counter.'_'.$i.'">';
        echo '</div>';
      }
  }
    $counter++;
  }
  }
  echo '</div>';
  echo '</div>';
 function getHolidayDescription($date, $holidayLists) {
    $date = date("Y-m-d", strtotime($date));
  if (!empty($holidayLists)){
    foreach ($holidayLists as $holiday) {
      if (($holiday['Date']) == $date) {
      return $holiday['Event'];
      }
    }
  }
    return false;
  }
?>
<div id="dialog-freelance-email<?php echo $intTeamID?>" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This will send you an email of the Freelancers working in <?php echo $strTeamName?>.<br>Do you wish to continue?</p>
</div>

<script language="JavaScript" type="text/javascript">
// If cookie is set, scroll to the position saved in the cookie.
if ( $.cookie("W-vscrollud<?php echo $intTeamID?>") !== null ) {
  $("#weeklynames<?php echo $intTeamID?>").scrollTop( $.cookie("W-vscrollud<?php echo $intTeamID?>") );
  $("#weeklyduties<?php echo $intTeamID?>").scrollTop( $.cookie("W-vscrollud<?php echo $intTeamID?>") );
  $("#weeklyduties<?php echo $intTeamID?>").scrollLeft( $.cookie("W-hscrollud<?php echo $intTeamID?>") );
}
// When scrolling happens....
$("#weeklyduties<?php echo $intTeamID?>").on("scroll", function() {
  // Set a cookie that holds the scroll position.
  $.cookie("W-vscrollud<?php echo $intTeamID?>", $("#weeklyduties<?php echo $intTeamID?>").scrollTop() );
  $.cookie("W-hscrollud<?php echo $intTeamID?>", $("#weeklyduties<?php echo $intTeamID?>").scrollLeft() );
  $('#weeklynames<?php echo $intTeamID?>').scrollTop($(this).scrollTop());
  $('#weeklytop<?php echo $intTeamID?>').scrollLeft($(this).scrollLeft());
  $('#weeklytotals<?php echo $intTeamID?>').scrollLeft($(this).scrollLeft());
});

$('#weeklynames<?php echo $intTeamID?>').on('scroll', function () {
  $('#weeklyduties<?php echo $intTeamID?>').scrollTop($(this).scrollTop());
});

if ($.cookie("W-vscrollfl<?php echo $intTeamID?>") !== null ) {
  $("#weeklyfreelancenames<?php echo $intTeamID?>").scrollTop( $.cookie("W-vscrollfl<?php echo $intTeamID?>") );
  $("#weeklyfreelanceduties<?php echo $intTeamID?>").scrollTop( $.cookie("W-vscrollfl<?php echo $intTeamID?>") );
  $("#weeklyfreelanceduties<?php echo $intTeamID?>").scrollLeft( $.cookie("W-hscrollfl<?php echo $intTeamID?>") );
  $('#weeklyfreelancetop<?php echo $intTeamID?>').scrollLeft( $.cookie("W-hscrollfl<?php echo $intTeamID?>") );
}

// When scrolling happens....
$("#weeklyfreelanceduties<?php echo $intTeamID?>").on("scroll", function() {
  // Set a cookie that holds the scroll position.
  $.cookie("W-vscrollfl<?php echo $intTeamID?>", $("#weeklyfreelanceduties<?php echo $intTeamID?>").scrollTop() );
  $.cookie("W-hscrollfl<?php echo $intTeamID?>", $("#weeklyfreelanceduties<?php echo $intTeamID?>").scrollLeft() );
  $('#weeklyfreelancenames<?php echo $intTeamID?>').scrollTop($(this).scrollTop());
  $('#weeklyfreelancetop<?php echo $intTeamID?>').scrollLeft($(this).scrollLeft());
  $('#weeklytop<?php echo $intTeamID?>').scrollLeft($(this).scrollLeft());
  $('#weeklytop<?php echo $intTeamID?>').scrollLeft($(this).scrollLeft());
});

$('#weeklyfreelancenames<?php echo $intTeamID?>').on('scroll', function () {
  $('#weeklyfreelanceduties<?php echo $intTeamID?>').scrollTop($(this).scrollTop());
});

ResizeWeeklyGrids();

$(window).resize(function() {
  ResizeWeeklyGrids();
})
function ResizeWeeklyGrids() {
  var offset = ($("#weeklytop<?php echo $intTeamID?>").length > 0) ? ($("#weeklytop<?php echo $intTeamID?>").offset().top) + ($("#content").offset().top) : 0;
  var windowwidth = $(window).width() - 40 - <?php echo $intNamesWidth?>;
  var holidayflag=<?php echo $holidayflag;?>;
  if (windowwidth > <?php echo ($intDutyWidth * 7)?>) {
   // windowwidth = <?php echo $intDutyWidth?> * 7;
  }
  var widowheight = $(window).height() - offset;
  $("#maintable").width($(window).width()- 43);
  $("#maintable").css("margin-left", 1);
  $("#weeklyduties<?php echo $intTeamID?>").width(windowwidth + 20);
  $("#weeklyduties<?php echo $intTeamID?>").height(widowheight / 2);
  $("#weeklynames<?php echo $intTeamID?>").height(widowheight / 2);
  $("#weeklytop<?php echo $intTeamID?>").width(windowwidth);
  $("#weeklytotals<?php echo $intTeamID?>").width(windowwidth);
  $("#freelancetop<?php echo $intTeamID?>").width(windowwidth+<?php echo $intNamesWidth?>);
  $("#freelancetop<?php echo $intTeamID?>").css({'top': '35px'});
  $('#weeklyfreelanceduties<?php echo $intTeamID?>').css({'top':<?php echo $intDateHeight*2;?>+ 'px'});
  $("#weeklyfreelanceduties<?php echo $intTeamID?>").width(windowwidth + 20).height(widowheight-10);
  $("#weeklyfreelancenames<?php echo $intTeamID?>").height(widowheight-10);
  $("#weeklyfreelancenames<?php echo $intTeamID?>").css({'top':<?php echo $intDateHeight*2;?> + 'px'});
  $("#fixed1").css({'top': 0})
  if (holidayflag==1){
    var newheight = <?php echo $intDateHeight;?>  +10;
    $("#weeklytop<?php echo $intTeamID?>").height(newheight);
    $("#fixed").height(newheight);
  }

  var counter = <?php echo  $counter; ?>;

   for(let j=0;j<= counter;j++){
    for (let k=0;k<=6;k++){
     let cellwidth=windowwidth/7;
     let cellleft = k*cellwidth;
     if (holidayflag==1){
      $("#headfshift"+k).css("height", newheight);
      $("#fixed").height(newheight);
      $("#weeklytop<?php echo $intTeamID?>").css("z-index", 1);
      $("#fixed").css("z-index", 1);
      //$("#dutyshift_"+j+"_"+k).css("top", 10);
      //$(".names.handcursor").css("top", 10);
      $("#fshift"+k).css("top", 1);
      $("#fixed1").css("z-index", 1);
      $("#fixed1").css("top", 1);
      $("#fshift"+k).css("z-index", 1);
      $("#freelancetop<?php echo $intTeamID?>").css({'top': '45px'});
      $("#weeklyfreelancenames<?php echo $intTeamID?>").css({'top':<?php echo ($intDateHeight*2) +10;?> + 'px'});
      $('#weeklyfreelanceduties<?php echo $intTeamID?>').css({'top':<?php echo ($intDateHeight*2) +10;?> + 'px'});
     }
     $("#headfshift"+k).css("width", cellwidth);
     $("#headfshift"+k).css("left", cellleft);
     $("#fshift"+k).css("width", cellwidth);
     $("#fshift"+k).css("left", cellleft+270);
     $("#dutyshift_"+j+"_"+k).css("width", cellwidth-1);
     $("#dutyshift_"+j+"_"+k).css("left", cellleft+1);
    }
   }
}

$(".select2").select2({
    width: "190px",
    allowClear: false
});
$(function() {
  $( "#datepicker-button-<?php echo $intTeamID?>" ).click(function() {
    $( "#datepicker<?php echo $intTeamID?>" ).datepicker( "show" );
  });

  $( "#datepicker<?php echo $intTeamID?>" ).datepicker({
    showOn: "none",
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
<?php
if ($intRefPage == 1) {
  echo 'ShowAllocationsUsageSched()';
} else {
  echo 'ShowAllocUsage('.$intTeamID.')';
}
?>

      }});
    }
  });
});
function ShowAllocUsage(teamId, WeekNumber) {
  $.post("page-includes/reports/allocations-usage-team.php", {
    teamId: teamId,
    WeekNumber: WeekNumber,
  },
  function(data,status){
    $('#content').html(data);
  })
}

function CreateFreelanceEmail(weeknumber) {
  $( "#dialog-freelance-email<?php echo $intTeamID?>" ).dialog(
  {
    width: 600,
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/reports/allocations-weekly-freelancers-email.php", {
        id: weeknumber,
        department: <?php echo $intTeamID?>
      },
      function(data,status){
      });
      },
      "No": function() {
       $( this ).dialog( "close" );
      },
      }
    }
  );
}

function CreateFreelanceExcel(intTeamID,weeknumber) {
  window.open("page-includes/reports/allocations-weekly-freelancers-create-excel.php?id="+weeknumber+"&teamId="+intTeamID);
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
