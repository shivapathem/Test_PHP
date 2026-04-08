<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();

$intID = $_REQUEST['id'];
$intDepartmentID = $_REQUEST['departmentid'];
$strMatch = $_REQUEST['rotaname'];


if (isset($_REQUEST['Update'])) {
  $intHours = $_REQUEST['hours'];
  if (isset($_REQUEST['IsNight'])) {
    $intIsNight = 1;
  }
  else {
    $intIsNight = 0;   
  }
  
  if ($intID < 0) {
    $query = "INSERT INTO RotaHoursMatch
                          (Match, Hours, DepartmentID)
              VALUES      (N'$strMatch', $intHours, $intDepartmentID)";
  }
  else {
    $query = "UPDATE RotaHoursMatch
              SET
              Match = N'$strMatch',
              hours = $intHours,
              IsNight = $intIsNight
              WHERE (id = $intID)";


  }
  echo $query;
    sqlsrv_query($db, $query);  

?>
    <script type="text/javascript">
      $.facebox.close();
      ShowPatternHours(<?php echo $intDepartmentID?>);
    </script>
<?php

}
else {
  
  if ($intID != 0) {
    $query = "SELECT        Match, Hours, isnull(isNight, 0) as isNight
              FROM          RotaHoursMatch
              WHERE        (id = $intID)";

    $request = sqlsrv_query($db, $query);
    $row = sqlsrv_fetch_array($request);
    $intHours = $row['Hours'];
    $intIsNight = $row['isNight'];

  }
  else {
    $intHours = 0;
    $intIsNight = 0;
  }


echo '<table class="redtable" width="600px">';
echo '<tr height="30px">';
echo '<th colspan="2">Editing Underlying Rota Pattern - Hours Match</th>';
echo '</tr>';  
echo '</table>';
echo '<div id="errorBox" class="lightcell">';   
echo '</div>'; 
echo '<form id="matchform">';
echo '<table class="redtable" width="600px">';
echo '<tr>';
echo '<th width="100px">Pattern</th>';
echo '<td><input type="text" id="pattern" name="rotaname" size="50" value="'.$strMatch.'"></td>';
echo '</tr>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px">Hours</th>';
echo '<td><input type="text" name="hours" size="10" value="'.$intHours.'"></td>';
echo '</tr>';
echo '<th width="100px">Night Shift</th>';
echo '<td>';
echo '<input name="IsNight" id="IsNight" value="ON" type="checkbox"';
if ($intIsNight != 0) {
    echo ' checked';
}
echo '>';
echo '<td></td>';
echo '</tr>';


echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td><input type="submit" value="Update" name="Update"></td>';
echo '</tr>';
echo '</table>';
echo '<input type="hidden" name="id" value="'.$intID.'">';
 echo '<input type="hidden" name="departmentid" value="'.$intDepartmentID.'">';
echo '</form>';
?>
<script type="text/javascript">
  $('document').ready(function(){

    $('#matchform').validate({
      errorLabelContainer: "#errorBox",
      rules:{
        "pattern":{
          required:true,
        },
        "hours":{
          required:true,
          min: 0,
          max: 24,
        }
      },
      messages: {
        pattern: "Please Enter a value to match<br>",
        hours: "Please Enter the number of hours to match<br>",
      },
      submitHandler: function(form) {
        $.ajax({type:'POST', url: 'page-includes/admin/depts-pattern-edit.php', data:$('#matchform').serialize(), success: function(data) {
          $.facebox.close();
          ShowPatternHours(<?php echo $intDepartmentID?>);
        }});
      }
  })
  $( "#pattern" ).focus();    
  $( "#pattern" ).select();  
});


</script>

<?php
 }
?>