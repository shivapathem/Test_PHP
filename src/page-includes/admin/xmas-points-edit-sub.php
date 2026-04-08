<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();
$intDay = $_REQUEST["day"];
$intDepartmentID = $_REQUEST["departmentid"];
$intID = $_REQUEST["id"];

if (isset($_REQUEST['Update'])) {
  $intPoints = $_REQUEST['points'];
  $strStartTime = $_REQUEST['starttime'];
  $strEndTime = $_REQUEST['endtime']; 
  
  $intStartTime = timetoseconds($strStartTime);
  $intEndTime = timetoseconds($strEndTime);
  if (isset($_REQUEST["AfterMidnight"]) ) {
    $intStartTime = $intStartTime + 86400;
    $intEndTime = $intEndTime + 86400;
  }
  if ($intEndTime < $intStartTime) {
    $intEndTime = $intEndTime + 86400;  
  }  
  
  if ($intID != 0) {
    $strQuery = "UPDATE  XmasPointsByDepartmentLink
                 SET         StartTime = $intStartTime, EndTime = $intEndTime, Points = $intPoints
                 WHERE   (ID = $intID)";
  }
  else {
    // Get the ID for this day.....
    $strQuery = "SELECT       ID
                 FROM         XmasPointsByDepartment
                 WHERE        (MonthDay = $intDay) 
                 AND          (DepartmentID = $intDepartmentID)";

        echo $strQuery;
    $rsID = sqlsrv_query($db, $strQuery);
    $row = sqlsrv_fetch_array($rsID);
    $intRecID = $row['ID'];    
    
    $strQuery = "INSERT INTO  XmasPointsByDepartmentLink
                              (XmasPointsByDepartmentID, StartTime, EndTime, Points)
                               VALUES   (
                               $intRecID, 
                               $intStartTime, 
                               $intEndTime, 
                               $intPoints)";
    }  


    sqlsrv_query($db, $strQuery);

}
else {
  if ($intID == 0) {
    $intStartTime = 0;
    $intEndTime = 0;    
    $intPoints = 0;
    $intAfterMidnight = 0;  
  }
  else {
  $strQuery = "SELECT    XmasPointsByDepartmentID, StartTime, EndTime, Points
            FROM         XmasPointsByDepartmentLink
            WHERE        (ID = $intID)";

    $rsDay = sqlsrv_query($db, $strQuery);
    $row = sqlsrv_fetch_array($rsDay);
    $intStartTime = $row['StartTime'];
    $intEndTime = $row['EndTime'];    
    $intPoints = $row['Points'];
    if ($intStartTime >= 86400) {
      $intAfterMidnight = 1;
    }
    else {
      $intAfterMidnight = 0;
    }
    
  }

//echo $strQuery;

 
echo '<form id="xmasformsub">';
echo '<table class="redtable" width="600px">';
echo '<tr height="30px">';
echo '<th colspan="2">Editing Christmas Points</th>';
echo '</tr>';

echo '<th width="100px">Start Time</th>';
echo '<td>';
echo '<input id="starttime" name="starttime" type="text" class="time" size="10" value="'.gmdate("H:i", $intStartTime).'"/>';
echo '</td>';
echo '</tr>';

echo '<th width="100px">End Time</th>';
echo '<td>';
echo '<input id="endtime" name="endtime" type="text" class="time" size="10" value="'.gmdate("H:i", $intEndTime).'"/>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<th width="100px">Points</th>';
echo '<td><input type="text" name="points" size="10" value="'.$intPoints.'"></td>';
echo '</tr>';

echo '<tr>';
echo '<th width="100px">After Midnight</th>';
echo '<td>';
  echo '<input name="AfterMidnight" id="AfterMidnight" value="ON" type="checkbox"';
  if ($intAfterMidnight === 1) {
      echo ' checked';
  }
  echo '>'; 

echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td><input type="submit" value="Update" name="Update"></td>';
echo '</tr>';
echo '</table>';
echo '<input type="hidden" name="departmentid" value="'.$intDepartmentID.'">';
echo '<input type="hidden" name="day" value="'.$intDay.'">';
echo '<input type="hidden" name="id" value="'.$intID.'">';
echo '</form>';
?>
<script type="text/javascript">
  $('document').ready(function(){
    $('#xmasformsub').validate({
      rules:{
        "points":{
          required:true,
          max: 30,
        },
        "limit":{
        }
      },
      submitHandler: function(form) {
        $.ajax({type:'POST', url: 'page-includes/admin/xmas-points-edit-sub.php', data:$('#xmasformsub').serialize(), success: function(data) {
          $.facebox.close();
          ShowXmasPointsAdmin(<?php echo $intDepartmentID?>);
        }});
      }
  })
$(function() {
  $('#starttime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

$(function() {
  $('#endtime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});  
});


</script>

<?php
 }
?>