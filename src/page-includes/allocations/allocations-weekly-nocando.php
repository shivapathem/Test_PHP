<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/skillsfunctions.php';
$topheight = 50;
$rowheight = 45;
$dutywidth = 195;
$nameswidth = 250;
$windowheightoffset = 240;


if (isset($_SESSION['allocations']["allocations"]['curentweekstarts'])) {
  $WeekNumber = bbcweeknumber($_SESSION['allocations']["allocations"]['curentweekstarts']) ;
}
else {
  $WeekNumber = bbcweeknumber(date("Y-m-d"));
}
// Override the default?
if (isset($_REQUEST['date'])) {
  $dateisvalid = validateDate($_REQUEST['date']);
  if ($dateisvalid == true) {
    $WeekNumber = bbcweeknumber($_REQUEST['date']);
  }
}
// Overrride it?
if (isset($_REQUEST['base'])) {
    $base = $_REQUEST['base'];
    $_SESSION['allocations']["allocations"]["base"] = $base;
}
else {
    $base = $_SESSION['allocations']["allocations"]["base"];
}
$startdate = datefromweek($WeekNumber);

date_default_timezone_set('Europe/London');

$enddate = date('Y-m-d', strtotime($startdate. ' + 6 days'));
$bst = date("I", strtotime($startdate));

$lastweekstarts =  date('Y-m-d', strtotime($startdate. ' - 7 days'));
$nextweekstarts = date('Y-m-d', strtotime($startdate. ' + 7 days'));
$_SESSION['allocations']["allocations"]['curentweekstarts'] = $startdate;

//$arrallduties = listalldutieswithprogrammes($bst, 999);
$arrAllocations = readallocations($WeekNumber, $WeekNumber, $base, 0, 0, 1);
if (isset($arrAllocations)) {
  $arrDutiesCanDo = GetAllDutiesAndPeople($base);
  $arrAllocations = FilterAllocations($arrAllocations, $arrDutiesCanDo, $WeekNumber);
}

// First the header
$tablewidth =  $nameswidth + ($dutywidth * 7) - 50;
echo '<table class="tablesmallnoborder" width="'.$tablewidth.'px">';
echo '<tr>';
echo '<td width="200px" class="lightcell medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowAllocationsNoCanDo('.$base.', "'.$lastweekstarts.'")\';>&nbsp;&lt;&lt; Week '.spinweek(bbcweeknumber($lastweekstarts)).'</span></td>';
echo '<td width="200px" class="lightcell medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowAllocationsNoCanDo('.$base.', "'.$nextweekstarts.'")\';>Week '.spinweek(bbcweeknumber($nextweekstarts)).'&nbsp;&gt;&gt;</span></td>';
echo '<td width="120px" class="lightcell medtextbold" align="right">Choose a Date</td>';
echo '<td width="75px" class="lightcell medtextbold handcursor" align="left"><input type="hidden" id="datepicker"></td>';

echo '<td width="500px" class="lightcell medtextbold" align="center" nowrap>';
echo '<font size="3">';
echo 'Allocations for Week '.spinweek($WeekNumber).'<br>';
echo '</font>';
echo 'Showing '.$_SESSION['allocations']['base'][$base]['name'].'</td>';

echo '<td class="lightcell medtextbold">&nbsp;';
echo '</td>';
echo '</tr>';

if(isset($arrAllocations)) {
  echo '<tr>';
  echo '<td class="medtextboldcentre lightcell" colspan="6">';
  echo '<br>According to the Skills database, the shifts shown below are unsuitable for the people they are allocated to.<br>It may be that the internal content has been changed so that the shift is now suitable, but please check carefully.<br><br>';
  echo '</td>';
  echo '</tr>';
}
else {
  echo '<tr>';
  echo '<td class="medtextboldcentre lightcell" colspan="6">';
  echo '<br>This week has not yet been published.<br>Please choose another week.<br><br>';
  echo '</td>';
  echo '</tr>';
}
echo '</table>';
if(isset($arrAllocations)) {
  // The holder
  echo '<div style="position: relative">';
  // The top Left Corner
  echo '<div style="position: absolute; width:'.$nameswidth.'px; height:'.($topheight - 1).'px; left:0px; top:0px" id="fixed"  class="dotw">';
  echo 'Week: '.spinweek($WeekNumber);
  if ($admin >= 5) {
    echo '<div class="WeekDutyCellTopRight handcursor" id="togglecomments">';
    echo '<img width="20" height="20" border="0" src="images/dropdown.png" id="img-swap"></img>';
    echo '</div>';
  }
  echo '</div>';
  // END the holder

  // The days of the week....
  $showall = 0;
  echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($rowheight - 1).'px; left:'.$nameswidth.'px; top:0px" id="weeklytop">';
    for ($i = 0; $i <=6; $i++) {
      $thisdate = date("Y-m-d", strtotime("+".$i." days", strtotime($startdate)));
      echo '<div style="width:'.$dutywidth.'px; position:absolute; left:'.($i * $dutywidth).'px; top:0px; height:'.($rowheight - 1).'px" class="dotw handcursor" onclick=\'javascript:ShowDailyAllocations('.$base.',"'.$thisdate.'",0,'.$showall.')\';>';
      echo $invdowMap[$i] .'<br>'.spindate($thisdate);
      echo '</div>';


      if ($admin >= 5) {
        $arrcomments = getdailycomments($startdate,$enddate);
        // Comments?
        echo '<div style="width:'.$dutywidth.'px; position:absolute; left:'.($i * $dutywidth).'px; top:'.$rowheight.'px; height:'.($rowheight - 1).'px" class="dotw handcursor" onclick="javascript:EditComments(\''.$thisdate.'\','.$base.')";>';
        if (isset($arrcomments[$thisdate])) {
          echo $arrcomments[$thisdate];
        }
        echo '</div>';

        // Duties count
        if (isset($arrdutycount)) {
        $bst = getbst($thisdate);
        echo '<div style="width:'.$dutywidth.'px; position:absolute; left:'.($i * $dutywidth).'px; top:'.($rowheight * 2).'px; height:'.($rowheight - 1).'px" class="dotw handcursor">';
        echo 'People Working '.$arrdutycount[$WeekNumber][$i]['working'].'<br>';
        if (isset($arrrequired[$bst][$i])) {
          echo 'Duty Count '.$arrrequired[$bst][$i].'<br>';
          echo 'Balance '.($arrdutycount[$WeekNumber][$i]['working'] - $arrrequired[$bst][$i]);
          //echo '<br>Not Working '.$arrdutycount[$i]['notworking'];
        }
        echo '</div>';
        }
      }
    }
  echo '</div>';
  // END the days of the week....


// The names down the left side
echo '<div style="overflow: scroll; position: absolute; width:'.$nameswidth.'px; height:400px; left:0px; top:'.$rowheight.'px" class="whitebackground" id="weeklynames">';
  $counter = 0;
  foreach ($arrAllocations as $sn => $value) {
    $textcolour = getnamecolour($value["dutycolour"]);
    echo '<div class="names handcursor" style="position: absolute; width:100%; height:'.($rowheight - 1).'px; left:0px; top:'.($counter * $rowheight).'px" onclick=\'javascript:ShowRota("'.$startdate.'","'.$value["staffnumber"].'")\';>';
    echo '<b><font color="'.$textcolour.'">'.$value["fullname"];
    echo '</b><br>';
    echo $value["sortcode"];
    echo '<br>';
    if (isset($_SESSION['allocations']['base'][$value["base"]]['name'])) {
      echo $_SESSION['allocations']['base'][$value["base"]]['name'];
    }
    else {
      echo 'Unknown';
    }
    echo '</font></div>';
    $counter++;
  }
echo '</div>';
// End the names

// The Duties
// The duties in the div
echo '<div style="overflow: scroll; position: absolute; width:900px; height:400px; left:'.$nameswidth.'px; top:'.$rowheight.'px" id="weeklyduties" class="whitebackground">';
  $counter = 0;
  foreach ($arrAllocations as $sn => $value) {

    for ($i = 0; $i <=6; $i++) {
      if (isset($value[$WeekNumber][$i])) {
          $currdate = datefromweek($WeekNumber, $i);
          if (isset($value[$WeekNumber][$i]["dutyid"])) {
            $dutyid = $value[$WeekNumber][$i]["dutyid"];
          }
          else {
            $dutyid = 0;
          }
          $edited = $value[$WeekNumber][$i]["edited"];
          // Are there any duty comments?
          $hascomments = 0;
          if($value[$WeekNumber][$i]["dutycomments"] == 1 || $value[$WeekNumber][$i]["personcomments"] == 1) {
            $hascomments = 1;
          }

          $cellclass = $value[$WeekNumber][$i]["cellclass"];
          echo '<div style="width:'.($dutywidth - 1).'px; position:absolute; left:'.($i * $dutywidth).'px; top:'.($counter * $rowheight).'px; height:'.($rowheight - 1).'px" class="'.$cellclass.' handcursor">';
          echo '<font color="'. $value[$WeekNumber][$i]["textcolour"].'">';
          echo $value[$WeekNumber][$i]["duty"];
          if (isset($value[$WeekNumber][$i]["starttime"])) {
            echo '<br>';
            echo $value[$WeekNumber][$i]["starttime"].'-'.$value[$WeekNumber][$i]["endtime"];
         }
          else {
            if (isset($value[$WeekNumber][$i]["duration"])) {
              if (!$value[$WeekNumber][$i]["duration"] == 0) {
                echo '<br>'.$value[$WeekNumber][$i]["duration"].' Hours';
              }
            }
          }
          echo '</font>';
        echo '</div>';
      }
      else {
        echo '<div style="width:'.($dutywidth - 1).'px; position:absolute; left:'.($i * $dutywidth).'px; top:'.($counter * $rowheight).'px; height:'.($rowheight - 1).'px" class="DutyCellNotWorking">';
        echo '';
        echo '</div>';
      }
    }
    $counter++;
  }
}


?>

<script language="JavaScript" type="text/javascript">
$(document).ready(function() {
  var widowwidth = $(window).width() - 40 - <?php echo $nameswidth?>;
  if (widowwidth > <?php echo ($dutywidth * 7)?>) {
   widowwidth = <?php echo $dutywidth?> * 7;
  }
  var widowheight = $(window).height() - <?php echo $windowheightoffset?>;
    $("#weeklyduties").width(widowwidth + 20).height(widowheight);
    $("#weeklynames").height(widowheight);
    $("#weeklytop").width(widowwidth);
  	// If cookie is set, scroll to the position saved in the cookie.
	    if ( $.cookie("vscroll") !== null ) {
	        $("#weeklynames").scrollTop( $.cookie("W-vscroll") );
          $("#weeklyduties").scrollTop( $.cookie("W-vscroll") );
          $("#weeklyduties").scrollLeft( $.cookie("W-hscroll") );
    }

	    // When scrolling happens....
    $("#weeklyduties").on("scroll", function() {
      // Set a cookie that holds the scroll position.
	    $.cookie("W-vscroll", $("#weeklyduties").scrollTop() );
      $.cookie("W-hscroll", $("#weeklyduties").scrollLeft() );
   });
  if ( $.cookie("showcomments") !== null ) {
    if ( $.cookie("showcomments") == 1 ) {
       togglecomments();
    }
  }

	$("#img-swap").click(function(){
   togglecomments();
	});
});

$('#weeklyduties').on('scroll', function () {
    $('#weeklynames').scrollTop($(this).scrollTop());
    $('#weeklytop').scrollLeft($(this).scrollLeft());
});

$('#weeklynames').on('scroll', function () {
    $('#weeklyduties').scrollTop($(this).scrollTop());
});

$(window).resize(function() {
  var widowwidth = $(window).width() - 40 - <?php echo $nameswidth?>;
  if (widowwidth > <?php echo ($dutywidth * 7)?>) {
    widowwidth = <?php echo $dutywidth?> * 7;
  }
  var widowheight = $(window).height() - <?php echo $windowheightoffset?>;
  $("#weeklyduties").width(widowwidth + 20).height(widowheight);
  $("#weeklynames").height(widowheight);
  $("#weeklytop").width(widowwidth);
})

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
    defaultDate: "<?php echo $startdate?>" ,
    onSelect: function (dateText, inst) {
      var currentdate = dateText;
      ShowAllocationsNoCanDo(<?php echo $base?>, currentdate)
    }
  });
});



</script>