<?php
session_start();
date_default_timezone_set('UTC');

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/skillsfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

if (isset($_REQUEST['department'])) {
  $intDepartmentID = $_REQUEST['department'];
}
else {
  $intDepartmentID = GetDefaultTeamByLogin($strUser);
}


if (is_null($intDepartmentID)) {
  echo '<br>';
  echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
  echo '<br><br>';
  echo 'You are attempting to view the Allocations for your Default Team.<br>However you do not have a Default set.<br>Go to \'Admin\' -> \'My Options\' to set a default';
  echo '<br></br><br>';
  echo '</div>';
}
else {

$arrDepDefaults = GetDeptDefaults($intDepartmentID);
$arrStaffOptions = GetStaffOtionsByDepartment ($strUser, $intDepartmentID);

$intAllowInBuilding = $arrDepDefaults[$intDepartmentID]['AllowInBuilding'];
$intSignInDays = $arrDepDefaults[$intDepartmentID]['SignInDays'];
$intConfirmedDays = $arrDepDefaults[$intDepartmentID]['ConfirmedDays'];
$intMaskDays = $arrDepDefaults[$intDepartmentID]['MaskAfter'];
$strDepartmentName = $arrDepDefaults[$intDepartmentID]['Description'];
$intColourWeek = $arrDepDefaults[$intDepartmentID]['ColourWeek'];
$intWeeklyFilterOption = $arrStaffOptions[$intDepartmentID]["WeeklyFilterOption"];
$strMyStaffNumber = $arrStaffOptions[$intDepartmentID]['StaffNumber'];

// ########################################## A few settings that affect page layout ##########################################
$intTopHeight = 0;
$intRowHeight = 45;
$intDutyWidth = 185;
$intNamesWidth = 250;
$intTableWidth =  $intNamesWidth + ($intDutyWidth * 7);
$intShowAll = 1;

// ######################################################## End ###############################################################

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
$_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;

$arrAllocations =  ReadAllocationsAndRotas ($intWeekNumber, $intDepartmentID);
//echo '<pre>';
//print_r($arrAllocations);
//die;

echo '<table class="tablegreysmallnoborder" width="'.$intTableWidth.'px">';
//echo '<table border="1" width="'.$intTableWidth.'px">';
echo '<tr>';
echo '<td rowspan="2" width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap"><span onclick=\'javascript:ShowWeeklyMismatch('.$intDepartmentID.',"'.$intPreviousWeek.'")\';>&nbsp;&lt;&lt; Week '.spinweek($intPreviousWeek).'</span></td>';
echo '<td rowspan="2" width="150px" class="medtextbold handcursor" valign="center" nowrap="nowrap" align="right"><span onclick=\'javascript:ShowWeeklyMismatch('.$intDepartmentID.',"'.$intNextWeek.'")\';>Week '.spinweek($intNextWeek).'&nbsp;&gt;&gt;</span></td>';
echo '<td rowspan="2" width="120px" class="medtextbold" align="right">Choose a Date</td>';
echo '<td rowspan="2" width="75px" class="medtextbold handcursor" align="left"><input type="hidden" id="datepicker"></td>';
echo '<td class="medtextbold" align="center" nowrap>';
echo '<font size="3">';
echo 'Allocations / Underlying Rota Pattern for Week '.spinweek($intWeekNumber).'</td>';
echo '</font>';
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td align="center" class="medtextbold">';
echo $strDepartmentName;
echo '</td>';
echo '</tr>';
echo '</table>';

// End the header
//have we any allocations to show?


//if (isset($arrAllocations)) {
if (count($arrAllocations) > 0) {
    $intTopHeight = $intRowHeight * 2;
  // The holder
  echo '<div style="position: relative">';
  // The top Left Corner
  echo '<div style="position: absolute; width:'.$intNamesWidth.'px; height:'.($intRowHeight - 1).'px; left:0px; top:0px" id="fixed"  class="dotw">';
  echo 'Week: '.spinweek($intWeekNumber);
  echo '</div>';
  // END the holder

  // The days of the week....
  echo '<div style="overflow: hidden; position: absolute; width:900px; height:'.($intRowHeight - 1).'px; left:'.$intNamesWidth.'px; top:0px" id="weeklytop">';
    for ($i = 0; $i <=6; $i++) {
      $thisdate = date("Y-m-d", strtotime("+".$i." days", strtotime($dteStartDate)));
      echo '<div style="width:'.$intDutyWidth.'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:0px; height:'.($intRowHeight - 1).'px" class="dotw handcursor">';
      echo $invdowMap[$i] .'<br>'.spindate($thisdate);
      echo '</div>';
    }
  echo '</div>';
  // END the days of the week....
// ################################################################################## The names down the left side
echo '<div style="overflow: scroll; position: absolute; width:'.$intNamesWidth.'px; height:400px; left:0px; top:'.$intRowHeight.'px" class="whitebackground" id="weeklynames">';
  $counter = 0;
  foreach ($arrAllocations as $sn => $value) {
    $TextColour = $value["StaffTextColour"];
    echo '<div class="names handcursor" style="position: absolute; width:100%; height:'.(($intRowHeight * 2) - 1).'px; left:0px; top:'.($counter * $intRowHeight * 2).'px">';
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
echo '<div style="overflow: scroll; position: absolute; width:900px; height:400px; left:'.$intNamesWidth.'px; top:'.$intRowHeight.'px" id="weeklyduties" class="whitebackground">';
  $counter = 0;
  foreach ($arrAllocations as $sn => $value) {
  //echo '<pre>';
  //print_r($value);
  //die;
    for ($i = 0; $i <=6; $i++) {
      $currdate = datefromweek($intWeekNumber, $i);
      if (isset($value['Days'][$i])) {
          $CellClass = $value['Days'][$i]['Allocations']["CellClass"];
          echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight * 2).'px; height:'.($intRowHeight - 1).'px" class="'.$CellClass.' handcursor">';
          echo '<font color="'. $value['Days'][$i]['Allocations']["TextColour"].'">';
          echo $value['Days'][$i]['Allocations']["Duty"];
          if (isset($value['Days'][$i]['Allocations']["StartTime"])) {
            echo '<br>';
            echo $value['Days'][$i]['Allocations']["StartTime"].'-'.$value['Days'][$i]['Allocations']["EndTime"];
          }
          else {
            if (isset($value['Days'][$i]['Allocations']["Duration"])) {
              if (!$value['Days'][$i]['Allocations']["Duration"] == 0) {
                echo '<br>'.$value['Days'][$i]['Allocations']["Duration"].' Hours';
              }
            }
          }
          echo '</font>';
        echo '</div>';
      }
      else {
        echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight * 2).'px; height:'.($intRowHeight - 1).'px" class="NotFixedOFF handcursor">';
        echo '</div>';
      }
      // And The Rota
      if (isset($value['Days'][$i])) {
          $CellClass = $value['Days'][$i]['RotaEntry']["CellClass"];
          echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.(($counter * $intRowHeight * 2) + $intRowHeight).'px; height:'.($intRowHeight - 1).'px" class="'.$CellClass.' handcursor">';
          echo '<font color="'. $value['Days'][$i]['RotaEntry']["TextColour"].'">';
          echo $value['Days'][$i]['RotaEntry']["Duty"];
          echo '</font>';
        echo '</div>';
      }
      else {
        echo '<div style="width:'.($intDutyWidth - 1).'px; position:absolute; left:'.($i * $intDutyWidth).'px; top:'.($counter * $intRowHeight * 2).'px; height:'.($intRowHeight - 1).'px" class="NotFixedOFF handcursor">';
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
  echo 'Unable to display Allocations for this week<br>';

  
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
            url: 'page-includes/allocations/allocations-duty-comments.php?DutyDate=' + api.elements.target.attr('DutyDate') + '&StaffNumber=' +api.elements.target.attr('StaffNumber')
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
<?php
  if (count($arrAllocations) > 0) {
?>
  if ($("#weeklyduties").length) {
    ResizeWeeklyGrids();
  }
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
  var offset = ($("#weeklytop").offset().top) + ($("#content").offset().top);
  var widowwidth = $(window).width() - 40 - <?php echo $intNamesWidth?>;
  if (widowwidth > <?php echo ($intDutyWidth * 7)?>) {
    widowwidth = <?php echo $intDutyWidth?> * 7;
  }
  var widowheight = $(window).height() - offset;
  $("#weeklyduties").width(widowwidth + 20).height(widowheight);
  $("#weeklynames").height(widowheight);
  $("#weeklytop").width(widowwidth);
}    

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
        ShowWeeklyMismatch(<?php echo $intDepartmentID?>)
      }});
    }
  });
});

function WeeklySignInToDay(date, action, staffnumber, department) {
  $( "#dialog-sign-in" ).dialog(
    {
     width:600,
      open: function() {
        $(this).siblings('.ui-dialog-buttonpane').find('button:eq(1)').focus();
    },
      buttons: {
        "Sign-In / Un-Sign In": function() {
          $('*').qtip('hide');
          $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
            date: date,
            staffnumber: staffnumber,
            action: action,
            department: department
          },
          function(data,status){
          {
            ShowAllocations(<?php echo $intDepartmentID?>)
          }
          });
          $( this ).dialog( "close" );
        },
        "Mark As In Building": function() {
          $('*').qtip('hide');
          $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
            date: date,
            staffnumber: staffnumber,
            action: 2,
            department: department
          },
          function(data,status){
          {
          if (data == 0) {
            $("#dialog-noinbuilding").dialog({            
              title: "Alert!!",
              resizable: false,
              height:160,
              width:600,
              modal: true,
              buttons: {
                OK: function() {
                  $( this ).dialog( "close" );
                }
              }
            });
          }
          else {
            ShowAllocations(<?php echo $intDepartmentID?>)
          }

          }
          });
          $( this ).dialog( "close" );
        },
        "Cancel": function() {
          $( this ).dialog( "close" );
        }
      }
    }
  );
}

function WeeklySignInDay(date, action, staffnumber, department) {
  $('*').qtip('hide');
  $.post("page-includes/allocations/allocations-weekly-signin-day.php", {
    date: date,
    staffnumber: staffnumber,
    action: action,
    department: department
  },
  function(data,status){
    {
      ShowAllocations(<?php echo $intDepartmentID?>)
    }
  });
}

$(function() {
  $( "#skill" ).autocomplete({
    source: "page-includes/allocations/allocations-returnstaffwithskills.php?department=<?php echo $intDepartmentID?>",
    minLength: 1,
    select: function( event, ui ) {
    SetFilter('11', ui.item.id, <?php echo $intDepartmentID?>, 1)
    }
  })
  .on('mouseup', function() {
    $(this).select();
  });;
});
</script>

<?php
}
?>