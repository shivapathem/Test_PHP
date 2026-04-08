<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();

$intID = $_REQUEST['id'];
if (isset($_REQUEST['Update'])) {
  $staffnumber = $_REQUEST['staffnumber'];
  $year = $_REQUEST['year'];
  $notes = escapeSingleQuotes($_REQUEST['notes']);
  $points = $_REQUEST['points'];


  if ($intID == 0) {
    $query = "INSERT INTO
              DescXmasPoints(
              StaffNumber,
              year,
              points,
              notes)
              VALUES (
              N'$staffnumber',
              $year,
              $points,
              N'$notes');";
    echo $query;
    $result_id = sqlsrv_query($db, $query);
//    $row = sqlsrv_fetch_array($result_id);
//    $intID = $row['computed'];
//    echo $intID;
  }
  else {
    $query = "UPDATE DescXmasPoints
              SET
              StaffNumber = N'$staffnumber',
              year = $year,
              points = $points,
              notes = N'$notes'
              WHERE (id = $intID)";

    sqlsrv_query($db, $query);
  }

?>
    <script type="text/javascript">
      $.facebox.close();
      ShowXmasPointsExtra(<?php echo $intID?>);
    </script>
<?php

}
else {
  $intDepartmentID = $_REQUEST['departmentid'];
  if ($intID !=0) {
    $query = "SELECT      DescXmasPoints.id, DescXmasPoints.StaffNumber, DescXmasPoints.year, DescXmasPoints.points, DescXmasPoints.notes,
                          Staff.Surname + N', ' + Staff.Forename AS FullName
              FROM        DescXmasPoints
              INNER JOIN  Staff ON DescXmasPoints.StaffNumber = Staff.StaffNumber
              WHERE       (DescXmasPoints.id = $intID)";

    $request = sqlsrv_query($db, $query);
    $row = sqlsrv_fetch_array($request);
    $fullname = $row['FullName'];
    $staffnumber = $row['StaffNumber'];
    $year = $row['year'];
    $points = $row['points'];
    $notes = $row['notes'];

  }
  else {
    $fullname = '';
    $staffnumber = '';
    $year = date("Y");
    $points = 0;
    $notes = '';
    $arrStaffInDepartment = GetStaffInTeamSimple($intDepartmentID);
    
  }


echo '<form id="xmasform">';
echo '<table class="redtable" width="600px">';
echo '<tr height="30px">';
echo '<th colspan="2">Editing Additional Christmas Points</th>';
echo '</tr>';



echo '<tr height="30px">';
echo '<th width="200px">Person</th>';
echo '<td>';
if ($intID == 0) {
  echo '<select class="chosen-select" name="staffnumber">';
  foreach ($arrStaffInDepartment as $strSN => $strName) {
    echo '<option value="'.$strSN.'">'.$strName.'</option>';
  }
  echo '</select>';

}
else {
  echo $fullname;
  echo '<input type="hidden" id="staffnumber" name="staffnumber" value="'.$staffnumber.'">';
}
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px">Year</th>';
echo '<td><input type="text" name="year" size="10" value="'.$year.'"></td>';
echo '</tr>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px">Points</th>';
echo '<td><input type="text" name="points" size="10" value="'.$points.'"></td>';
echo '</tr>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px">Notes</th>';
echo '<td> <textarea rows="2" name="notes" cols="40">'.$notes.'</textarea></td>';
echo '</tr>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td><input type="submit" value="Update" name="Update"></td>';
echo '</tr>';
echo '</table>';
echo '<input type="hidden" name="id" value="'.$intID.'">';
echo '</form>';
?>
<script type="text/javascript">
  $('document').ready(function(){
    $('#xmasform').validate({
      rules:{
        "points":{
          required:true,
          min: -10,
          max: 30,
        },
        "year":{
          required:true,
          min: 2011,
          max: 2024,
        }
      },
      submitHandler: function(form) {
        $.ajax({type:'POST', url: 'page-includes/admin/depts-xmas-points-edit.php', data:$('#xmasform').serialize(), success: function(data) {
          $.facebox.close();
          ShowXmasPointsExtra(<?php echo $intDepartmentID?>);
        }});
      }
  })
   $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "350px"
  });
});


</script>

<?php
 }
?>