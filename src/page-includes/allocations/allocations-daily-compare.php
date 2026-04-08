<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';
$commonObj = new classCommonDBFunctions();
$admin = 0;
$intDutyHeight = 40;
$rowheight = 43/2;
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];


if (isset($_REQUEST['teamId'])) {
  $intTeamID = $_REQUEST['teamId'];
}  
else {
  $intTeamID = GetDefaultSchedulingTeamIdByLogin($UserID);
}

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
  $arrTeamDefaults = GetTeamDefaults(0,$intTeamID);

  $teamDescription = $arrTeamDefaults[$intTeamID]['Description'];
  $intHideDailyView = $arrTeamDefaults[$intTeamID]['HideDailyView'];
  $intHasGridChecks = $arrTeamDefaults[$intTeamID]['hasGridChecks'];
  $intEditPeriod = $arrTeamDefaults[$intTeamID]['DailyEditPeriod'];
  $intSignInDays = $arrTeamDefaults[$intTeamID]['SignInDays'];
  $intAllowApplyOvertime = $arrTeamDefaults[$intTeamID]['AllowApplyOvertime'];
  $intHasDutiesView = $arrTeamDefaults[$intTeamID]['HasDutiesView'];

  $arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);

  if ((isset($arrStaffOptions[$intTeamID]['isScheduler']) && $arrStaffOptions[$intTeamID]['isScheduler'] == 1) || (isset($arrStaffOptions[$intTeamID]['isTeamAdmin']) && $arrStaffOptions[$intTeamID]['isTeamAdmin'] == 1)|| (isset($arrStaffOptions[$intTeamID]['isManager']) && $arrStaffOptions[$intTeamID]['isManager'] == 1) || (isset($loggedUsedInfo[$intTeamID]['SystemAdmin']) && $loggedUsedInfo[$intTeamID]['SystemAdmin']==1)) {
   $intCanMakeEdited = 1; 
  }
  else {
  $intCanMakeEdited = 0;
  } 

  $userwebconfigResult = GetUserWebConfigByUserLogin($strUser);
  $intHourwidth = (int) $userwebconfigResult["result"][0]["HourWidth"];
  // check $intHourwidth value, if its 0 then set defult value 
  if ($intHourwidth == 0) {
    $intHourwidth = 75;
  }
  $bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim($strCurrentDate);
 
  $intWeek = $bbcweeknumberArray['ixYearWeek'];
  $intDay = intval($bbcweeknumberArray['ixDayInWeek']);
 
  $arrAllocations = ReadAllocationsDayCompare($intTeamID, $intWeek, $intDay);
//Now write the days and dates in the fixed div

echo '<table class="tablegreysmallnoborder" width="100%">';
echo '<tr>';
echo '<td width="600px" align="center" class="medtextbold" valign="top" nowrap>';
echo 'Allocations for '.date("d F Y (l)", strtotime($strCurrentDate)).'<br>';
echo 'Week '.spinweek($intWeek);
echo '<br>';
echo 'Showing '.$arrTeamDefaults[$intTeamID]['Description'];
echo '</td>';
$ddate = $dpage='""';
$ckLocalstorage=1;
echo '<td width="325px" align="center" class="medtextbold handcursor" onclick=\'javascript:ShowDailyAllocations('.$intTeamID.','.$ddate.','.$dpage.','.$ckLocalstorage.')\';>Show Daily Grid</td>';
echo '<td width="200px"></td>';

if (($arrStaffOptions[$intTeamID]['isShiftLeader'] == 1) || ($arrStaffOptions[$intTeamID]['isScheduler'] == 1) || ($arrStaffOptions[$intTeamID]['isTeamAdmin'] == 1)){
  echo '<td width="150px" class="medtextbold handcursor" onclick=\'javascript:ShowDailyDeletions('.$intTeamID.',"'.$strCurrentDate.'")\';><img title="View Deleted Duties and Jobs" border="0" src="images/button_view_deletions.png" width="30px" height="30px"></td>'; 
}
if (($arrStaffOptions[$intTeamID]['isScheduler'] == 1) || ($arrStaffOptions[$intTeamID]['isTeamAdmin'] == 1)){
  if ($intCanMakeEdited == 1) {
    echo '<td width="50px" class="medtextbold handcursor" onclick=\'javascript:DeleteEdits('.$intTeamID.',"'.$strCurrentDate.'")\';><img title="Accept all shiftleader changes.<br>" border="0" src="images/green_tick.png" width="30px" height="30px"></td>';
      
  }
}
echo '<td></td>';
echo '</table>';

if (isset($arrAllocations)) {
  $countallocations = count($arrAllocations['duties']);
  $earlieststart =floor($arrAllocations['earlieststart']);
  $lateststart = round($arrAllocations['lateststart']);
  if($lateststart==0){
    $lateststart=30;
  }
  $hoursinday = $lateststart - $earlieststart;
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
for ($i=$earlieststart; $i <= ($lateststart + $earlieststart); $i++) {
  echo '<div style="width:'.$intHourwidth.'px;  position:absolute; left:'.(($i - $earlieststart) * $intHourwidth).'px; top:0px">';
  echo '<div class="timebar" style="position: absolute; left: 0px; top:0px; width:'.$intHourwidth.'px; height:15px">'.(gmdate("H:00",  $i * 3600)).'</div>';
  echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
  echo '</div>';
}
echo '</div>';

// The names down the left side
echo '<div style="overflow: scroll; position: absolute; width:250px; height:400px; left:0px; top:32px" id="names">';
  $counter = 0;
  foreach ($arrAllocations['duties'] as $dutyid => $value) {
    
  if($value["StaffTextColour"]=='#ffffff'){
      $textcolour='#000000';
      $backcolour = $value["StaffBackColour"];
    } else {
    $textcolour = $value["StaffTextColour"];
    $backcolour = $value["StaffBackColour"];
    }
    if(!empty($value["fullname"])){
      $leftdata = $value["fullname"];
    }else{
      $leftdata = 'Unallocated';
    }
    echo '<div class="names"  style="position: absolute; width:250px; height:'.(($rowheight * 2) - 1).'px; left:0px; top:'.($counter * $rowheight * 2).'px;background-color:'.$backcolour.';">';
    echo '<font color="'.$textcolour.'"><b>'.$leftdata;
    echo '<br>';
    echo $value["sortcode"];
    echo '</b></font></div>';

    $counter++;
  }
echo '</div>';
// End the names


// The duties in the div
echo '<div style="overflow: scroll; position: absolute; width:900px; height:400px; left:250px; top:32px" id="duties">';
  $counter = 0;
  drawtimecells_compare ($lateststart, $intHourwidth, $countallocations * $rowheight * 2, 0);
  foreach ($arrAllocations['duties'] as $dutyid => $value) {
    echo '<div class="duties" style="position: absolute; width:'.($lateststart * $intHourwidth).'px; height:'.(($rowheight * 2) - 1).'px; left:0px; top:'.($counter * $rowheight * 2).'px">';
    // ======================================================== First do the original duty and jobs
    $dstart = $value["unedited"]["starttime"];
    $dend = $value["unedited"]["endtime"];
    $dutybackcolour = $value["unedited"]["backcolour"];
    $dutyfontcolour = $value["unedited"]["fontcolour"];
    if ($dstart == -1) {
    // Its a duty with no duration.....
      $dstart = $value["unedited"]["starttime"];
      $dend = $dstart + 10800;
    }

    $left = ((($dstart / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
    $right = ((($dend / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
    if ($right <= $left) {
      $right = $right + ($intHourwidth * 24);
    }
    $width = $right - $left;
	$postion="absolute";
	if (empty($value["unedited"]["starttime"]) && empty($value["unedited"]["endtime"])) {
		$width=$intHourwidth;
		$postion="sticky";
	}  
    echo '<div id="'.$dutyid.'" qtip-content="Last Published" class="boxed handcursor tipjob compareedit-duty-context-menu" style="width: '.$width.'px;position:'. $postion.';  height: '.($intDutyHeight - 6).'px; left:'.$left.'px; top:3px; background-color:'.$dutybackcolour.'">';
    echo '<font color="'.$dutyfontcolour.'">';

    echo $value["unedited"]["dutyname"];
    if(!empty($value["unedited"]["duration"]) && $value["unedited"]["duration"] > 0.0) {
      $intendHour = number_format((float)($value["unedited"]["duration"] / 3600), 2, '.', '');
      echo ' (Duration: '.$intendHour.' Hours)';
    }
	
	if (!empty($value["unedited"]["starttime"]) && !empty($value["unedited"]["endtime"])) {
		echo ' ('.gmdate("H:i", $value["unedited"]["starttime"]).'-'.gmdate("H:i", $value["unedited"]["endtime"]).')';
	} else {	
		echo '  >>>>';
	}	
    echo '</font>';
	echo '</div>';
    // The jobs.....
    if (isset($value["unedited"]['jobs'])) {
	  foreach ($value["unedited"]['jobs'] as $jobID => $job) {
          $jobbackcolour = $job["backcolour"];
          $jobfontcolour = $job["fontcolour"];
		  $midnightFlag = $job["midnightFlag"];
		  $jobmidnightstart = 0;
		  $jobmidnightend = 0;
		  if ($midnightFlag==1){
			if ($job["starttime"]> $job["endtime"]){
				$jobmidnightstart = 0;
				$jobmidnightend = 86400;
			} else {
				$jobmidnightstart = 86400;
				$jobmidnightend = 86400;
			}			
		  }	  
		  
          $left = (((($jobmidnightstart+$job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
          $right = (((($jobmidnightend+$job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
          $width = $right - $left;
          $jobqtipcontent = '<font color=#99CCFF>'.$job["jobname"].'</font><br>';
          $jobqtipcontent.=  gmdate("H:i", $job["starttime"]).'-'.gmdate("H:i", $job["endtime"]);
          if ($job["comments"] != '') {
            $jobqtipcontent.= '<br><font color=#00CC00>Comments:<br>';
            $jobqtipcontent.= $job["comments"].'</font>';
         }
          echo '<div id="'.$jobID.'" align="center" qtip-content="'.$jobqtipcontent.'" class="boxed tipjob handcursor compareedit-job-context-menu" style="overflow:hidden; width: '.$width.'px; height: '.(($intDutyHeight / 2) - 5).'px; position:absolute; left:'.$left.'px; top:'.($intDutyHeight / 2).'px; background-color:'.$jobbackcolour.'">';
          echo '<font color="'.$jobfontcolour.'">'.$job["jobname"].'</font>';
          echo '</div>';
      }
    }
    
	echo '</div>';
    $counter++;

  }
  echo '</div>';
  // End the duties

echo '</div>';
$windowheightoffset = 165;

}
else {
  echo '<table class="tablesmallnoborder" id="daytable" width="100%">';
  echo '<thead>';
  echo '<tr class="GridviewScrollHeader">';
  echo '<th class="tableheadersmall">';
  //echo 'There are no allocations for this day';
  echo "</th>";
  echo '<th class="tableheadersmall">';
  echo '<br>There are no edited duties to display<br>';
  echo "</th>";
  echo "</tr>";
  echo "</thead>";
  $windowheightoffset = 0;
}

echo '</table>';

echo '<br><br><br><br>';

/**
* This function is used to display drawtimecells.
*
* @param $lateststart This param contains the start time.
*
* @param $intHourWidth This param contains the hour width.
*
* @param $height This param contains the height.
*/
function drawtimecells_compare ($lateststart, $intHourWidth, $height, $dark = 0) {

  for ($hr = 0; $hr <= $lateststart; $hr = $hr + 0.25) {
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
    echo '<div class="'.$class.'" style="width:'.($intHourWidth / 4).'px; height: '.($height).'px; position: absolute; left:'.$intHourWidth * $hr.'px; top:0px"></div>';

  }
}

?>
<div id="dialog-delete-edits" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>By clicking on Accept All you are indicating that you have checked all the changes made by Shiftleaders for this day.</p>
</div>
<div id="dialog-recopy" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You can re-import any Duties and Jobs that have been published from Allocate since the day became editable.<br><b>Warning!!</b><br>People who have been added to the week and have Jobs assigned to them may cause Jobs to appear twice.</p>
</div>
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
	    if ( $.cookie("vscroll") !== null ) {
	        $("#duties").scrollTop( $.cookie("vscroll") );
          $("#names").scrollTop( $.cookie("vscroll") );
          $("#duties").scrollLeft( $.cookie("hscroll") );
	    }

	    // When scrolling happens....
	    $("#duties").on("scroll", function() {
        // Set a cookie that holds the scroll position.
	      $.cookie("vscroll", $("#duties").scrollTop() );
        $.cookie("hscroll", $("#duties").scrollLeft() );
	    });
    // This will automatically grab the 'title' attribute and replace
    // the regular browser tooltips for all <a> elements with a title attribute!
    $('img[title]').qtip({
        position: {
        viewport: $(window)
      }
    });

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
        style: {
          classes: 'qtip-rounded qtip-shadow qtip-dark',
          width: 250
        }
});

})

function ShowEditInfo(id) {
  $.post("page-includes/edits/allocations-edit-allocate-info.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
}

function DeleteEdits (teamId, date) {
  $(function() {
    $( "#dialog-delete-edits" ).dialog(
      {
      width: 800,
      buttons: {
        "Accept All": function() {
          $.post("page-includes/allocations/edits/delete-day.php", {
            teamId: teamId,
            date: date
          },
          function(data,status){
            ShowDailyAllocations (<?php echo $intTeamID?>);
          })
          $( this ).dialog( "close" );
        },
        "Cancel": function() {
          $( this ).dialog( "close" );
        }
      }
    });
  });
}

function ReCopy (departmentid, date) {
  $(function() {
    $( "#dialog-recopy" ).dialog(
      {
      width: 800,
      buttons: {
        "Re-import": function() {
          $.post("page-includes/allocations/edits/recopy-day.php", {
            departmentid: departmentid,
            date: date
          },
          function(data,status){
            ShowDailyAllocations (<?php echo $intTeamID?>);
          })
          $( this ).dialog( "close" );
        },
        "Cancel": function() {
          $( this ).dialog( "close" );
        }
      }
    });
  });
}

$(function() {

  $.contextMenu({
    selector: '.compareedit-duty-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
    },
    items: {
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("id");
          $.post("page-includes/allocations/edits/dutyhistory.php", {
            id: dutyid
          },
          function(data,status){
           $.facebox(data);
          })
        }
      },
    },
  }),

  $.contextMenu({
    // The context menu for edited jobs assigned to a duty
    selector: '.compareedit-job-context-menu',
    callback: function(key, options) {
      id = options.$trigger.attr("id");
      //alert(id);
    },
    items: {
      "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var id =  options.$trigger.attr("id");
          $.post("page-includes/allocations/edits/jobhistory.php", {
            id: id
          },
          function(data,status){
            $.facebox(data);
          })
        }
      },
    }
  })
});

</script>