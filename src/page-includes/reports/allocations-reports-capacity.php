<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';
include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

$service = new AllocationService();

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
$intPreviousWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate. ' - 7 days')));
$intNextWeek = bbcweeknumber(date('Y-m-d', strtotime($dteStartDate. ' + 7 days')));
echo '<h1 class="sr-only">Spare Capacity</h1>';
echo '<div class="spare-staff-section">';
echo '<div class="main-section" style="background-color: #dddddd;">';
echo '<div class="left-section" style="width: 47%;">';
echo '<div class="section-box">';
echo '<button class="spare-weeks-btn" width="150px" nowrap onclick=\'javascript:ShowAllocationsCapacity('.$intPreviousWeek.')\';>&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</button>';
echo '</div>';
echo '<div class="section-box" style="margin-left: 20px;">';
echo '<button class="spare-weeks-btn" align="right" width="150px" nowrap onclick=\'javascript:ShowAllocationsCapacity('.$intNextWeek.')\';>Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</button>';
echo '</div>';
echo '</div>';
echo '<div class="center-section" style="width: 53%;">';
echo '<div class="section-box">';
echo '<h2 style="font-size: 15px;font-weight: 600;"> Spare Staff Capacity for Week '.spinweek($intWeekNumber).'</h2>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';



$arrAllocations = ReadAllocationsCapacity($intWeekNumber);
// End the header
//have we any allocations to show?


//if (isset($arrAllocations)) {
 if (count((array)$arrAllocations) > 0) {
  // The holder
  echo '<div style="position: relative">';
  // The top Left Corner
  echo '<div style="position: absolute; width:'.$intNamesWidth.'px; height:'.($intDateHeight - 1).'px; left:0px; top:0px" id="fixed"  class="dotw">';
  echo 'Week: '.spinweek($intWeekNumber);
  echo '</div>';
  // END the holder

  // The days of the week....
  echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($intDateHeight - 1).'px; left:'.$intNamesWidth.'px; top:0px" id="weeklytop">';
    for ($i = 0; $i <=6; $i++) {
      $strCurrDate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
      if ($strCurrDate == date("Y-m-d")) {
        echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - 1).'px" class="dotwlight handcursor">';
      }
      else {
        echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intDateHeight - 1).'px" class="dotw handcursor">';
      }      
      echo $invdowMap[$i] .'<br>'.spindate($strCurrDate);
      if (isset($arrHolidays[$strCurrDate])) {
        echo "<br>(".$arrHolidays[$strCurrDate].")";
      }      
      echo '</div>';      
    }
  echo '</div>';

  // END the days of the week....
// ################################################################################## The names down the left side
echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:'.$intNamesWidth.'px; height:400px; left:0px; top:'.$intDateHeight.'px" class="whitebackground" id="weeklynames">';
  $counter = 0;
  foreach ($arrAllocations as $sn => $value) {
    echo '<div class="names handcursor" style="position: absolute; width:100%; height:'.($intRowHeight - 1).'px; left:0px; top:'.($counter * $intRowHeight).'px">';   
    echo '<b>'.mb_convert_encoding($value["FullName"], 'ISO-8859-1', 'UTF-8');
    echo '</b><br>';
    echo mb_convert_encoding($value["SortCode"], 'ISO-8859-1', 'UTF-8');
    echo '</div>';
    $counter++;
  }
echo '</div>';
// ############################################################################### End the names

// The Duties
// The duties in the div
echo '<div style="overflow-y:scroll; overflow-x:hidden; position: absolute; width:900px; height:400px; left:'.$intNamesWidth.'px; top:'.$intDateHeight.'px" id="weeklyduties" class="whitebackground">';
  $counter = 0;
  foreach ($arrAllocations as $sn => $value) {
//   echo '<pre>';
//  print_r($value);
//  die;

    $intFirstLock = 0;
    for ($i = 0; $i <=6; $i++) {
      $currdate = datefromweek($intWeekNumber, $i);
      // The day is hidden?
    
        if (isset($value[$intWeekNumber][$i])) {
            if (isset($value[$intWeekNumber][$i]["Dutyid"])) {
              $Dutyid = $value[$intWeekNumber][$i]["Dutyid"];
            }
            else {
              $Dutyid = 0;
            }

            $CellClass = $value[$intWeekNumber][$i]["CellClass"];

            echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="'.$CellClass.' handcursor">';


            echo '<div style="white-space: nowrap; overflow: hidden;" title="'.$value[$intWeekNumber][$i]["Duty"].'">'.$value[$intWeekNumber][$i]["Duty"].'</div>';

            $starttime = $value[$intWeekNumber][$i]["starttime"] ?? null;
            $endtime = $value[$intWeekNumber][$i]["endtime"] ?? null;
            $dutyDuration = 0;

            if (isset($value[$intWeekNumber][$i]["duration"]) && $value[$intWeekNumber][$i]["duration"] != '' && $value[$intWeekNumber][$i]["duration"] != null) {
                $dutyDuration = $value[$intWeekNumber][$i]["duration"];
            }

            if ((is_null($starttime) && is_null($endtime)) || ($starttime == 0 && $endtime == 0)) {
                echo $service->convertSecondsIntoTime($dutyDuration, '.', 'No') . ' Hours<br>';
            } else {
                echo $service->convertSecondsIntoTime($starttime, ':', 'No') . ' - ' . $service->convertSecondsIntoTime($endtime, ':', 'No') . '<br>';
            }

            echo $value[$intWeekNumber][$i]["DepartmentName"];

            // Show the Locks....?
  
            if (isset($arrRequests[$value["StaffNumber"]][$i])) {
              if ($arrRequests[$value["StaffNumber"]][$i]['IsLock'] == 1) {
                $intFirstLock = 1;
                $strTitle = 'Day Is Locked<br>'.$arrRequests[$value["StaffNumber"]][$i]['TipText'];
                $strImage = 'locked';
              }
              else {
                $strTitle = $arrRequests[$value["StaffNumber"]][$i]['Description'];
                if ($arrRequests[$value["StaffNumber"]][$i]['Approved'] == 1) {
                  if ($intFirstLock == 0 && $arrRequests[$value["StaffNumber"]][$i]['AffectLocks'] == 1) {
                    $strTitle.= '<br>This is the Lock for this Week<br>Approved';
                    $strImage = 'locked';                
                  }  
                  else {
                    $strTitle.= '<br>Approved';
                    $strImage = 'requested';                
                  
                  }
                  $intFirstLock = 1;
                
                }
                else {
                  if ($arrRequests[$value["StaffNumber"]][$i]['Approved'] == 1) {
                    $strTitle.= '<br>OK - Not yet Approved';
                    $strImage = 'requested';
                  }
                  else {
                    $strTitle.= '<br>Waiting List - Not yet Approved';
                    $strImage = 'requestedInQ';
                  }            
                }
           
              }
              echo '<div class="DutyCellBottomCentre">';            
              echo '<img title="'.$strTitle.'" width="15" height="15" border="0" src="images/locks/'.$strImage.'.png"></img>';
              echo '</div>'; 
            }
  
          echo '</div>';
        }  
        else {
          echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="NotFixedOFF handcursor">';
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
<div id="dialog-no-filter" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You have a filter set!<br>Because the weekly view is restricted the filter has been removed for this week.<br>Do you want to keep the filter in place for viewing unrestricted weeks or clear it?</p>
</div>

<script language="JavaScript" type="text/javascript">
<?php
if (isset($_SESSION['showdailycomments'])) {
  echo "$('.comments').toggle();";
}

?>
$(function() {
    $('#btnAdd').click(function() {
      $('.comments').toggle();
      $.post("page-includes/ajax-calls/allocations-toggle-comments.php", {
      })
    });
});
$(document).ready(function() {
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "200px"
  });
  $(function() {
    $( "#accordion" ).accordion({
      heightStyle: "content",
      collapsible: true,
      active: false,
      beforeActivate: function( event, ui ) {
        $('*').qtip('hide');
      }      
    });
    $('.ui-accordion-content').css({
      "padding":"2px",
      "width":"500px"
    });
    $('.accordiontop').css({
      "width":"250px"
    });    
  });
  $(document).click(function (event) {
    if(!$(event.target).closest('#accordion').length) {//if you clicked outside of the accordion
      $("#accordion").accordion({active: false});//collapse all the panels
    }
  });

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
    $('#weeklynames').scrollTop($(this).scrollTop());
    $('#weeklytop').scrollLeft($(this).scrollLeft());    
  });
  $('[title]').qtip({
    position: {
      viewport: $(window)
    },
    style: { classes: 'qtip-rounded qtip-shadow qtip-light'}
  });

  $('.tipremotecomments').each(function() {
    $(this).qtip({
      content: {
        text: function(event, api) {
          $.ajax({
            url: 'page-includes/allocations/allocations-duty-comments.php?DutyDate=' + api.elements.target.attr('DutyDate') + '&StaffNumber=' +api.elements.target.attr('StaffNumber')+'&Department=' +api.elements.target.attr('departmentid')
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

  $('.signedtip').each(function() {
    $(this).qtip({
      content: {
        text: function(event, api) {
          $.ajax({
            url: 'page-includes/allocations/allocations-signedin-info.php',
            type: 'POST',
            data: {date: api.elements.target.attr('date'),
                   staffnumber: api.elements.target.attr('StaffNumber'),
                   departmentid: api.elements.target.attr('departmentid'),
                  }
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
  $('.tipremoteedp').each(function() {
    $(this).qtip({
      content: {
        text: function(event, api) {
          $.ajax({
            url: 'page-includes/allocations/allocations-edp-info.php',
            type: 'POST',
            data: {staffnumber: api.elements.target.attr('StaffNumber'),
                   dutydate: api.elements.target.attr('DutyDate'),
                   departmentid: api.elements.target.attr('departmentid')
                  }
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
});


  $.contextMenu({
    selector: '.staff-context-menu',
    //trigger: 'left',
    callback: function(key, options) {
    },
    items: {

      "edit": {
        name: "Edit",
        icon: "edit",
        // superseeds "global" callback
        callback: function(key, options) {
          LogIn = options.$trigger.attr("LogIn");
          DepartmentID = options.$trigger.attr("DepartmentID");  
          $.post("page-includes/admin/edituser.php", {
            login: LogIn,
            department: DepartmentID,
            showweek: 1,            
          },
          function(data,status){
            $.facebox(data);
          }
          )
        }
      },
    },
  }) 






<?php 
  if (count((array)$arrAllocations) > 0) {
?>  
ResizeWeeklyGrids();
$('#weeklynames').on('scroll', function () {
  $('#weeklyduties').scrollTop($(this).scrollTop());
});

$(window).resize(function() {
  if ($("#weeklyduties").length) {
  ResizeWeeklyGrids();
  }
})
<?php
}
?>




function ResizeWeeklyGrids() {
	if(($("#weeklytop").length > 0) && ($("#content").length > 0))
	{
	  var offset = ($("#weeklytop").offset().top) + ($("#content").offset().top);
	  var windowwidth = $(window).width() - 40 - <?php echo $intNamesWidth?>;
	  if (windowwidth > <?php echo ($intDutyWidth * 7)?>) {
		windowwidth = <?php echo $intDutyWidth?> * 7;
	  }
	  var widowheight = $(window).height() - offset;
	  $("#weeklyduties").width(windowwidth + 20).height(widowheight);
	  $("#weeklynames").height(widowheight);
	  $("#weeklytop").width(windowwidth);
	}
}




</script>

