<?php

date_default_timezone_set('UTC');
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/leavefunctions.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/allocationsfunctionsday.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';


$intDepartmentID = $_REQUEST['department'];
$strCurrentDate = $_SESSION['allocattionsdate'];

$strStartTime = '';
$strEndTime  = '';
$jobname = '';

$db = OpenDatabase();
echo '<div id="page">';
if (isset($_REQUEST['submit'])) {
  // The form is being submitted....
  $strTitle = $_REQUEST['Title'];
  $strStartTime = $_REQUEST['StartTime'];
  $strEndTime = $_REQUEST['EndTime'];
  $strBackColour = $_REQUEST['BackColour'];
  $strFontColour = $_REQUEST['FontColour'];
  if (isset($_REQUEST['aftermidnight'])) {
    $intAfterMidnight = 1;
  }
  else {
    $intAfterMidnight = 0;
  }
  $time = time();
  $dblStartTime = strtotime('1970-01-01 '.$strStartTime) / 86400;
  $dblEndTime = strtotime('1970-01-01 '.$strEndTime) / 86400;

  if ($dblEndTime < $dblStartTime) {
    $dblEndTime = $dblEndTime + 1;
  }
  if ($intAfterMidnight == 1) {
    $dblStartTime = $dblStartTime + 1;
    $dblEndTime = $dblEndTime + 1;
  }
  // Duration
  $intDuration = ($dblEndTime - $dblStartTime) * 24;

  $history = "New duty created by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i")."<br>";
  $history.= "The job was called ".$strTitle." and the times were ".gmdate("H:i", doubletoseconds($dblStartTime))."-".gmdate("H:i", doubletoseconds($dblEndTime))."<hr>";
  $history = escapeSingleQuotes($history);
  // Create as unassigned
  $week = bbcweeknumber($strCurrentDate);
  $day = getdayofweek($strCurrentDate);
  $query = "INSERT INTO dbo.Allocations_webedit(
            StaffNumber,
            DutyName,
            WeekNumber,
            iDay,
            StartTime,
            EndTime,
            Duration,
            AllocationID,
            editable,
            isworking,
            OrigAllocationID,
            History,
            DepartmentID,
            BackColour,
            FontColour,
            edited)
            VALUES (
            N'0',
            N'$strTitle',
            N'$week',
            $day,
            $dblStartTime,
            $dblEndTime,
            $intDuration,
            $time,
            1,
            1,
            $time,
            N'$history',
            $intDepartmentID,
            N'$strBackColour',
            N'$strFontColour',
            1)";

        sqlsrv_query($db, $query);

        ?>
        <script type="text/javascript">
          $(function(){
            $.facebox.close();
            ShowDailyAllocations('<?php echo $intDepartmentID?>')
          }
         )
        </script>
        <?php

}
else {
  $strBackColour = '#ff0000';
  $strFontColour = '#ffffff';
  $intAfterMidnight = 0;
  showform ('', $strStartTime, $strEndTime, $strBackColour, $strFontColour, $intAfterMidnight, $intDepartmentID); 
}
echo '</div>';

function showform ($strTitle, $strStartTime, $strEndTime, $strBackColour, $strFontColour, $intAfterMidnight, $intDepartmentID) {
  echo '<form id="newjob">';

  echo '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 625px; z-index: 101;">';
  echo '<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">';
  echo '<span id="ui-id-5" class="ui-dialog-title">New Duty</span>';
  echo '</div>';
  echo '<div style="width: auto; min-height: 0px; max-height: none;" class="ui-dialog-content ui-widget-content">';
  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>';

  echo '<table class="redtable" width="600px">';
  echo '<tr>';
  echo '<td colspan="4" class="tableheadersmall">';
  echo '<br>You are creating a new Duty which will be unallocated<br><br>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>Title</td>';
  echo '<td colspan="3">';
  echo '<input id="Title" name="Title" type="text" size="40"value="'.$strTitle.'" />';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>Start</td>';
  echo '<td>';
  echo '<input id="StartTime" name="StartTime" type="text" class="time" size="10" value="'.$strStartTime.'"/>';
  echo '</td>';
  echo '<td>End</td>';
  echo '<td>';
  echo '<input id="EndTime" name="EndTime" type="text" class="time" size="10"  value="'.$strEndTime.'"/>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="3">Duty starts after midnight</td>';
  echo '<td>';
  echo '<input type="checkbox" name="aftermidnight" value="ON"';
  if ($intAfterMidnight == 1) {
    echo ' checked';
  }
  echo '>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>Back Colour</td>';
  echo '<td>';
  echo '<input type="text" name="BackColour" id="BackColour"  value="'.$strBackColour.'" />';
  echo '</td>';
  echo '<td>Text Colour</td>';
  echo '<td>';
  echo '<input type="text" name="FontColour" id="FontColour" value="'.$strFontColour.'"/>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td></td>';
  echo '<td colspan="3"><input name="submit" type="submit" value="Submit"></input><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';
  echo '<input type="hidden" name="department" value="'.$intDepartmentID.'">';
  echo '</form>';

?>

<script type="text/javascript">
$(function() {
  $('#StartTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

$(function() {
  $('#EndTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

$('document').ready(function(){
    $('#newjob').validate({
      errorLabelContainer: "#errorBox",
      rules:{
        "Title":{
          required:true,
        },
        "StartTime":{
          required:true,
        },
        "EndTime":{
          required:true,
        }
      },
      messages: {
        StartTime: "Please Enter a Start Time<br>",
        EndTime: "Please Enter an End Time<br>",
        Title: "Please Enter a Duty Name<br>"
      },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/allocations/edits/new-duty.php', data:$('#newjob').serialize(), success: function(data) {
            $('#page').html(data);
          }});
        }
  })
  });


  $("#BackColour").spectrum({
    showPaletteOnly: true,
    togglePaletteOnly: false,
    hideAfterPaletteSelect:true,
    //color: 'red',
    preferredFormat: "hex",
    palette: [
        ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
        ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
        ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
        ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
        ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
        ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
        ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
        ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
    ]
  });

  $("#FontColour").spectrum({
    showPaletteOnly: true,
    togglePaletteOnly: false,
    hideAfterPaletteSelect:true,
    //color: 'white',
    preferredFormat: "hex",
    palette: [
        ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
        ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
        ["#f4cccc","#fce5cd","#fff2cc","#d9ead3","#d0e0e3","#cfe2f3","#d9d2e9","#ead1dc"],
        ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
        ["#e06666","#f6b26b","#ffd966","#93c47d","#76a5af","#6fa8dc","#8e7cc3","#c27ba0"],
        ["#c00","#e69138","#f1c232","#6aa84f","#45818e","#3d85c6","#674ea7","#a64d79"],
        ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"],
        ["#600","#783f04","#7f6000","#274e13","#0c343d","#073763","#20124d","#4c1130"]
    ]
  });
function cancel() {
    $.facebox.close();
}

</script>

<?php
}


function checkjobtimes($dblStartTime, $dblEndTime) {
  $error = 0;
  // How long - no more than 6 hours
  if ($dblEndTime - $dblStartTime > 0.99) {
    $error = 2;
  }
  // Can't start after 09:00 if starting after midnoght
  if ($dblStartTime >= 1.375) {
    $error = 3;
  }
  // if the job starts after midnight and $dblEndTime > 2 then it needs to be not starting after midnight
  if ($dblEndTime >= 2) {
    $error = 4;
  }

  return ($error);

}

?>