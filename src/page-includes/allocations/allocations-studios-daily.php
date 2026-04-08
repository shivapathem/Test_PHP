<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
ini_set("zlib.output_compression", 1);
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/studiofunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intSchedulingTeamId = $_REQUEST['teamId'];

if (isset($_REQUEST['date'])) {
    $strCurrentDate = date("Y-m-d", strtotime($_REQUEST['date']));
}
else {
  if (isset($_SESSION['allocattionsdate'])) {
    $strCurrentDate = $_SESSION['allocattionsdate'];
  }
  else {
    $strCurrentDate = date("Y-m-d");
  }
}

$jobheight = 32;

$strYesterday = date("Y-m-d", strtotime("-1 day", (strtotime($strCurrentDate))));
$strTomorrow = date("Y-m-d", strtotime("+1 day", (strtotime($strCurrentDate))));
$intWeek = bbcweeknumber($strCurrentDate);
$intDay = getdayofweek($strCurrentDate);

$arrTeamDefaults = GetTeamDefaults($intUserID,$intSchedulingTeamId);

$arrStaffOptions = GetStaffOtionsByTeam ($strUser, $intSchedulingTeamId, $intUserID); //need to change in yearly
$intHourwidth = $arrStaffOptions[$intSchedulingTeamId]['HourWidth'];

$arrStudioJobs = GetStudioUsage($intWeek, $intDay, $intSchedulingTeamId);

// ******************************************************************* Now write the days and dates in the fixed div
echo '<h1 class="sr-only">Allocation by Studio</h1>';
echo '<table class="tablegreysmallnoborder" width="100%">';
echo '<tr>';
echo '<td width="130px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowStudioUsage('.$intSchedulingTeamId.',"'.$strYesterday.'")\';>&nbsp;&lt;&lt; '.date("jS M Y", strtotime($strYesterday)).'</span></td>';
echo '<td width="130px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowStudioUsage('.$intSchedulingTeamId.',"'.$strTomorrow.'")\';>'.date("jS M Y", strtotime($strTomorrow)).'&nbsp;&gt;&gt;</span></td>';
echo '<td width="150px" align="right" class="medtextbold handcursor">Choose a Date</td>';
echo '<td width="50px" class="medtextbold handcursor"><input type="hidden" id="datepicker"></td>';

echo '<td align="center" class="medtextbold lightcell">';
  echo '<font size="3">';
  echo date("jS F Y - l", strtotime($strCurrentDate));
  echo '</font>';
echo '<br>Week '.spinweek($intWeek);
echo '<br>';
echo 'Showing '.$arrTeamDefaults[$intSchedulingTeamId]['Description'];
echo '</td>';
echo '<td width="150px" align="center" class="medtextbold handcursor" onclick=\'javascript:ShowDailyAllocations('.$intSchedulingTeamId.',"'.$strCurrentDate.'")\';>Show Allocations<br>for this Day</td>';
echo '<td width="150px" align="center" class="medtextbold handcursor" onclick=\'javascript:ShowAllocations("'.$intSchedulingTeamId.'",'.$intWeek.')\';>Show Allocations<br>for this Week</td>';
echo '</tr>';
echo '</table>';

//echo '<div id="refonly">';
//echo '<img border="0" src="images/refonly.png">';
//echo '</div>';


if (isset($arrStudioJobs)) {

  $intEarliestStart = floor($arrStudioJobs['EarliestStart'] / 3600);
  $intLatestEnd = round($arrStudioJobs['LatestEnd'] / 3600) - $intEarliestStart;

  $hoursinday = $intLatestEnd - $intEarliestStart;
  
  
  if ($hoursinday != 0) {
    $screenwidth = round($intHourwidth * $hoursinday);
  }

// The holder
echo '<div style="position: relative">';
// The top Left Corner
echo '<div style="position: absolute; width:250px; height:30px; left:0px; top:0px" id="fixed"  class="names">';
echo '</div>';

// The hours in the day....
echo '<div style="overflow: hidden; position: absolute; width:900px; height:30px; left:250px; top:0px" id="top" class="times">';
  for ($i=$intEarliestStart; $i <= ($intLatestEnd + $intEarliestStart); $i++){
    echo '<div style="width:'.$intHourwidth.'px;  position:absolute; left:'.(($i - $intEarliestStart) * $intHourwidth).'px; top:0px">';
    echo '<div class="timebar" style="position: absolute; left: 0px; top:0px; width:'.$intHourwidth.'px; height:15px">'.(gmdate("H:00",  $i * 3600)).'</div>';
    echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
    echo '</div>';

  }
echo '</div>';

// The Studio names down the left side
echo '<div style="overflow: scroll; position: absolute; width:250px; height:400px; left:0px; top:32px" id="names">';
  $top = 0;
  foreach ($arrStudioJobs['Studios'] as $strStudioName => $arrJobs)  {
    $linecount =  $arrJobs['linecount'];
    echo '<div class="namesbig handcursor" style="position: absolute; width:250px; height:'.(($jobheight  * $linecount) - 1).'px; left:0px; top:'.$top.'px">';
    echo $strStudioName;
    echo '</div>';
    $top = $top + $jobheight * $linecount;

}
echo '</div>';
// End the Studio names

// The duties in the div
echo '<div style="overflow: scroll; position: absolute; width:900px; height:400px; left:250px; top:32px" id="duties">';
  $counter = 0;
  drawtimecells_studio ($intLatestEnd, $intHourwidth, $top, 0);

  $top = 0;
  foreach ($arrStudioJobs['Studios'] as $strStudioName => $arrJobs)  {

      $linecount =  $arrJobs['linecount'];

      echo '<div class="duties" style="position: absolute; width:'.($intLatestEnd * $intHourwidth).'px; height:'.(($jobheight  * $linecount) - 1).'px; left:0px; top:'.$top.'px">';
      foreach ($arrJobs['Jobs'] as $jobid => $job) {
        $jobbackcolour = $job["BackColour"];
        $jobfontcolour = $job["FontColour"];

        $left = floor((($job["StartTime"] / 3600) - $intEarliestStart + 1) * $intHourwidth) - $intHourwidth;
        $right = ceil((($job["EndTime"] / 3600) - $intEarliestStart + 1) * $intHourwidth) - $intHourwidth;
        $width = $right - $left;
        $line = $job["line"];
        $jobname = $job["JobName"];
        $allocatedto = $job["AllocatedTo"];
        $jobqtipcontent = $jobname;
        $jobqtipcontent.= ' ('.gmdate("H:i", $job["StartTime"]).'-'.gmdate("H:i", $job["EndTime"]).')<br>';
        $jobqtipcontent.= $allocatedto;  
        // Any Comments?      
        //if ($job["comments"] != '') {
        //  $jobqtipcontent.= '<br><font color=#00CC00>Comments:<br>'.$job["comments"].'</font>';
        //}
        echo '<div qtip-content="'.$jobqtipcontent.'" align="center" class="boxed tipjob handcursor" style="overflow:hidden; width: '.$width.'px; height: '.($jobheight - 5).'px; position:absolute; left:'.$left.'px; top:'.($line * $jobheight).'px; background-color:'.$jobbackcolour.'">';
        echo '<font color="'.$jobfontcolour.'">'.$jobname.'<br>'.$allocatedto.'</font>';
        echo '</div>';
      }

      echo '</div>';
      $top = $top + ($jobheight * $linecount);
     //}

}
  echo '</div>';
  // End the duties

echo '</div>';
$windowheightoffset = 185;
echo '</table>';

}
else {
  echo '<br>';
  echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
  echo '<br><br>';
  echo 'There are no Allocations for this day';
  echo '<br></br><br>';
  echo '</div>';
  $windowheightoffset = 0;
}



echo '<br><br><br><br>';

/**
* This function is used to get drawtimecells.
*
* @param $intLatestEnd This param contains the End time.
*
*@param $intHourwidth This param contains Hour Width.
*
*@param $height This param contains Hight.
*
*@param $dark This param set value 0.
*/
function drawtimecells_studio ($intLatestEnd, $intHourwidth, $height, $dark = 0) {

  for ($hr = 0; $hr <= $intLatestEnd; $hr = $hr + 0.25) {
    if ($dark == 1) {
      if ($hr == intval($hr)) {
        $class = 'dayholderdarkhour';
      }
      else {
        $class = 'dayholderdarkparthour';
      }
    }
    else {
      if ($hr == intval($hr)) {
        $class = 'dayholderhour';
      }
      else {
        $class = 'dayholderparthour';
      }
    }
    echo '<div class="'.$class.'" style="width:'.floor($intHourwidth / 4).'px; height: '.($height).'px; position: absolute; left:'.$intHourwidth * $hr.'px; top:0px"></div>';

  }
}

?>

<script language="JavaScript" type="text/javascript">

$(document).ready( function () {
  var widowwidth = $(window).width() - 285;
  var widowheight = $(window).height() - <?php echo $windowheightoffset?>;
    $("#duties").width(widowwidth + 20).height(widowheight);
    $("#names").height(widowheight);
    $("#unallocatedduties").width(widowwidth);
    $("#unallocatedjobs").width(widowwidth);
    $("#top").width(widowwidth);
  	    // If cookie is set, scroll to the position saved in the cookie.
	    if ( $.cookie("vscrollstudios") !== null ) {
	        $("#duties").scrollTop( $.cookie("vscrollstudios") );
          $("#names").scrollTop( $.cookie("vscrollstudios") );
          $("#duties").scrollLeft( $.cookie("hscrollstudios") );
	    }

	    // When scrolling happens....
	    $("#duties").on("scroll", function() {
        // Set a cookie that holds the scroll position.
	      $.cookie("vscrollstudios", $("#duties").scrollTop() );
        $.cookie("hscrollstudios", $("#duties").scrollLeft() );
	    });

    $(".btnPrint").printPage();

    // This will automatically grab the 'title' attribute and replace
    // the regular browser tooltips for all <a> elements with a title attribute!
    $('img[title]').qtip();

  })

  $( window ).resize(function() {
  var widowwidth = $(window).width() - 285;
  var widowheight = $(window).height() - <?php echo $windowheightoffset?>;
    $("#duties").width(widowwidth).height(widowheight);
    $("#names").height(widowheight);
    $("#unallocatedduties").width(widowwidth);
    $("#unallocatedjobs").width(widowwidth);
    $("#top").width(widowwidth);
  })


$('#duties').on('scroll', function () {
    $('#names').scrollTop($(this).scrollTop());
    $('#top').scrollLeft($(this).scrollLeft());
    $('#unallocatedduties').scrollLeft($(this).scrollLeft());
    $('#unallocatedjobs').scrollLeft($(this).scrollLeft());
});

$('#names').on('scroll', function () {
    $('#duties').scrollTop($(this).scrollTop());

});

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
    defaultDate: "<?php echo $strCurrentDate?>" ,
    onSelect: function (dateText, inst) {
      var currentdate = dateText;
      ShowStudioUsage(<?php echo $intSchedulingTeamId?>, currentdate)
    }
  });
});

$('.tipjob').qtip({
    content: {
        text: function(event, api) {
            // Retrieve content from custom attribute of the $('.selector') elements.
            return $(this).attr('qtip-content');
        }
    },
      position: {
        my: 'top right',  // Position my top left...
        at: 'bottom middle', // at the bottom right of...
        viewport: $(window),
        adjust: {y: -3}
      },
      show: {
        solo: true
      },
      style: 'qtip-rounded qtip-shadow qtip-dark'
});






</script>


