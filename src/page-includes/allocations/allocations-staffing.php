<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsstaffing.php';

$intDepartmentID = 4;


// ########################################## A few settings that affect page layout ##########################################
if (isset($_SESSION['screenwidth'])) {
  $intScreenWidth = $_SESSION['screenwidth'];
}
else {
  $intScreenWidth = 1600;
}
$intDateHeight = 55;
$intStatusHeight= 10;
$intRowHeight = 45;
$intNamesWidth = 250;
$intDutyWidth = ($intScreenWidth - $intNamesWidth - 40) / 7;
$intTableWidth = $intNamesWidth + ($intDutyWidth * 7);
$intShowAll = 1;



// ########################################## END  ###########################################################################

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

// ######################################################## Work Out the week we are to view ###############################################################
if (isset($_REQUEST['WeekNumber'])) {
  // Passed a week Number?
  $intWeekNumber = $_REQUEST['WeekNumber'];
}
else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];    
  }
  else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));  
  }
}

$dteStartDate = datefromweek($intWeekNumber);
$dteEndtDate = date('Y-m-d', strtotime($dteStartDate. ' + 6 days'));
$arrHolidays = calculateBankHolidays(date("Y", strtotime($dteStartDate))); 
$intPreviousWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate. ' - 7 days')));
$intNextWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate. ' + 7 days')));
$_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;
$arrDepartments = GetReportsConfig(0);
$arrAllocations = ReadAllocationsStaffingSimple($intWeekNumber, $intWeekNumber, $arrDepartments);
$arrUncovered = ReadAllocationsUncovered($intWeekNumber, $intWeekNumber, $arrDepartments);

//echo '<pre>';
//print_r($arrUncovered);
//die;

echo '<table class="tablegreysmallnoborder" border="1" width="'.$intTableWidth.'px">';
//echo '<table border="1" width="'.$intTableWidth.'px">';
echo '<tr height="35px">';
echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowStaffing('.$intDepartmentID.',"'.$intPreviousWeek.'")\';>&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</span></td>';
echo '<td width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowStaffing('.$intDepartmentID.',"'.$intNextWeek.'")\';>Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</span></td>';
echo '<td width="120px" class="medtextbold" align="right">Choose a Date</td>';
echo '<td width="75px" class="medtextbold handcursor" align="left"><input type="hidden" id="datepicker"></td>';

echo '<td width="300px" class="medtextbold" align="center" nowrap>';
echo '<font size="3">';
echo 'Staffing for Week '.spinweek($intWeekNumber).'</td>';
echo '</font>';
echo '</td>';
echo '</tr> ';
echo '</table>';

// End the header
//have we any allocations to show?
//if (isset($arrAllocations)) {
if (count($arrAllocations) > 0) {
  // The holder
  echo '<div style="position: relative">';
  // The top Left Corner

  echo '<div style="font-size:14px; position: absolute; width:100%; height:20px; left:0px; top:0px" class="tableheadersmall medtextboldcentre">';
  echo 'Uncovered Shifts';
  echo '</div>';  

  echo '<div style="position: absolute; width:'.$intNamesWidth.'px; height:'.($intDateHeight - 1).'px; left:0px; top:20px" id="fixed"  class="dotw">';
  echo 'Week: '.spinweek($intWeekNumber);
  echo '</div>';
  // END the holder

  // The days of the week....
  echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($intDateHeight - 1).'px; left:'.$intNamesWidth.'px; top:20px" id="weeklytop">';
    for ($i = 0; $i <=6; $i++) {
      $strCurrDate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
      if ($strCurrDate == date("Y-m-d")) {
        echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - 1).'px" class="dotwlight handcursor" onclick=\'javascript:ShowDailyAllocations('.$intDepartmentID.',"'.$strCurrDate.'",0,'.$intShowAll.')\';>';
      }
      else {
        echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - 1).'px" class="dotw handcursor" onclick=\'javascript:ShowDailyAllocations('.$intDepartmentID.',"'.$strCurrDate.'",0,'.$intShowAll.')\';>';
      }      
      echo $invdowMap[$i] .'<br>'.spindate($strCurrDate);
      if (isset($arrHolidays[$strCurrDate])) {
        echo "<br>(".$arrHolidays[$strCurrDate].")";
      }      
      echo '</div>';     
    }
  echo '</div>';
  // END the days of the week....
  
// ################################################################################## The duty name for the uncovered...
  echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:'.$intNamesWidth.'px; height:400px; left:0px; top:'.$intDateHeight.'px" class="whitebackground" id="uncoverednames">';
  $counter = 0;
  foreach ($arrUncovered as $strDutyName => $value) {
    $arrDuty = explode('<br>', $strDutyName);
    echo '<div class="names handcursor" style="position: absolute; width:100%; height:'.($intRowHeight - 1).'px; left:0px; top:'.($counter * $intRowHeight).'px">';
    echo '<b>'.$arrDuty[1];
    echo '</b>';
    echo '</div>';
    $counter++;
  }
echo '</div>';


echo '<div style="overflow: scroll; position: absolute; width:900px; height:400px; left:'.$intNamesWidth.'px; top:'.$intDateHeight.'px" id="uncoveredduties" class="whitebackground">';
  $counter = 0;
  foreach ($arrUncovered as $strDutyName => $value) {
    for ($i = 0; $i <=6; $i++) {
      if (isset($value[$intWeekNumber][$i])) {
        echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="DutyCellWorking handcursor">';
        echo $value[$intWeekNumber][$i]['Duty'];       
        echo '</div>';
      }
      else {
        echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="DutyCellNotWorking handcursor">';
        echo '</div>';
      
      
      }
    }
    $counter++;
  }
  echo '</div>';


























// ################################################################################## The names down the left side

  echo '<div style="font-size:14px; position: absolute; width:100%; height:20px; left:0px; top:0px" class="tableheadersmall medtextboldcentre" id="standbytop">';
  echo 'Standby Shifts';
  echo '</div>'; 


  echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:'.$intNamesWidth.'px; height:400px; left:0px; top:'.$intDateHeight.'px" class="whitebackground" id="weeklynames">';
  $counter = 0;
  foreach ($arrAllocations as $sn => $value) {
    //echo '<pre>';
    //print_r($value);
    //die;
  
  
    $TextColour = $value["StaffTextColour"];
    echo '<div DepartmentID="'.$intDepartmentID.'" LogIn="'.$value["Login"].'" class="names handcursor" style="position: absolute; width:100%; height:'.($intRowHeight - 1).'px; left:0px; top:'.($counter * $intRowHeight).'px" onclick=\'javascript:ShowRota("'.$dteStartDate.'","'.$value["StaffNumber"].'",'.$intDepartmentID.')\';>';
    echo '<b><font color="'.$TextColour.'">'.$value["FullName"];
    echo '</b><br>';
    echo $value["SortCode"];
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
    for ($i = 0; $i <=6; $i++) {
      if (isset($value[$intWeekNumber][$i])) {
        $currdate = datefromweek($intWeekNumber, $i);
        $edited = $value[$intWeekNumber][$i]["edited"];
        // Are there any Duty comments?
        $hascomments = 0;
        if($value[$intWeekNumber][$i]["Dutycomments"] != 0 || $value[$intWeekNumber][$i]["personcomments"] != 0) {
          $hascomments = 1;
        }
        $CellClass = $value[$intWeekNumber][$i]["CellClass"];
        echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="'.$CellClass.' handcursor">';
        echo '<font color="'. $value[$intWeekNumber][$i]["TextColour"].'">';
        echo $value[$intWeekNumber][$i]["Duty"];
        if (isset($value[$intWeekNumber][$i]["starttime"])) {
          echo '<br>';
          echo $value[$intWeekNumber][$i]["starttime"].'-'.$value[$intWeekNumber][$i]["endtime"];
        }
        else {
          if (isset($value[$intWeekNumber][$i]["duration"])) {
            if (!$value[$intWeekNumber][$i]["duration"] == 0) {
              echo '<br>'.$value[$intWeekNumber][$i]["duration"].' Hours';
            }
          }
        }
        echo '</font>';
        echo '<br>['.$value[$intWeekNumber][$i]["DepartmentName"].']';
        
        
        // Show the comments?
        if ($hascomments == 1) {  
          echo '<div class="DutyCellBottomRight handcursor tipremotecomments" departmentid= "'.$intDepartmentID.'" DutyDate="'.$currdate.'" StaffNumber="'.$value["StaffNumber"].'">';
          echo '<img width="12" height="12" border="0" src="images/info.png"></img>';
          echo '</div>';
        }
        echo '</div>';
      }
      else {
        echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="DutyCellNotWorking handcursor">';
        echo '</div>';
      
      
      }
    }
    $counter++;
  }
  echo '</div>';
  echo '</div>';

}
else {
  echo '<br>';
  echo '<div class="tableheadersmall bigtextboldcentre" style="width:'.$intTableWidth.'px">';
  echo '<br><br>';
  echo 'Unable to display The Allocations for this week<br>'; 
  echo '<br></br><br>';
  echo '</div>';
}

echo '<br><br><br><br>';

?>

<script language="JavaScript" type="text/javascript">
<?php
if (count($arrAllocations) > 0) {
?>
  // If cookie is set, scroll to the position saved in the cookie.
	if ( $.cookie("W-vscroll") !== null ) {
	  $("#weeklynames").scrollTop( $.cookie("W-vscroll") );
    $("#weeklyduties").scrollTop( $.cookie("W-vscroll") );
    $("#weeklyduties").scrollLeft( $.cookie("W-hscroll") );
  }

	if ( $.cookie("W-vscroll-uc") !== null ) {
	  $("#uncoverednames").scrollTop( $.cookie("W-vscroll-uc") );
    $("#uncoveredduties").scrollTop( $.cookie("W-vscroll-uc") );
    $("#uncoveredduties").scrollLeft( $.cookie("W-hscroll-uc") );
  }
    
  // When scrolling happens....
  $("#weeklyduties").on("scroll", function() {
    // Set a cookie that holds the scroll position.
	  $.cookie("W-vscroll", $("#weeklyduties").scrollTop() );
    $.cookie("W-hscroll", $("#weeklyduties").scrollLeft() );
    $('#weeklynames').scrollTop($(this).scrollTop());
    $('#weeklytop').scrollLeft($(this).scrollLeft());    
  });
  
  $("#uncoverednames").on("scroll", function() {
    // Set a cookie that holds the scroll position.
	  $.cookie("W-vscroll-uc", $("#uncoverednames").scrollTop() );
    $.cookie("W-hscroll-uc", $("#uncoverednames").scrollLeft() );
    $('#uncoveredduties').scrollTop($(this).scrollTop());
    $('#weeklytop').scrollLeft($(this).scrollLeft());    
  });
  
  $('#weeklynames').on('scroll', function () {
    $('#weeklyduties').scrollTop($(this).scrollTop());
  });
  $('#uncoveredduties').on('scroll', function () {
    $('#uncoverednames').scrollTop($(this).scrollTop());
  });

ResizeWeeklyGrids();

$(window).resize(function() {
  ResizeWeeklyGrids();
})


function ResizeWeeklyGrids() {
  if($("#weeklytop").length>0) {
  var offset = ($("#weeklytop").offset().top) + ($("#content").offset().top);
  }else{
    var offset=0; 
  }
  var windowwidth = $(window).width() - 40 - <?php echo $intNamesWidth?>;
  if (windowwidth > <?php echo ($intDutyWidth * 7)?>) {
    windowwidth = <?php echo $intDutyWidth?> * 7;
  }
  var widowheight = $(window).height() - offset;
  $("#uncoverednames").height(widowheight / 2);  
  $("#uncoveredduties").width(windowwidth + 20).height(widowheight/2);
  $('#weeklyduties').css({'top':(widowheight / 2 + 80)});    
  $("#weeklyduties").width(windowwidth + 20).height(widowheight/2);
  $('#standbytop').css({'top':(widowheight / 2 + 60) + 'px'});   
  $("#weeklynames").height(widowheight / 2); 
  $('#weeklynames').css({'top':(widowheight / 2 + 80) + 'px'});  
  $("#weeklytop").width(windowwidth);
}    
<?php 
}
?>
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
        ShowStaffing(<?php echo $intDepartmentID?>)
      }});
    }
  });
});

</script>
