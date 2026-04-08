<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();
  $intDepartmentID = $_REQUEST['departmentid'];
  $intDay = $_REQUEST['day'];  

if (isset($_REQUEST['Update'])) {
  $intLimit = $_REQUEST['limit'];
  $intBasicPoints = $_REQUEST['basicpoints'];


    $query = "if exists(SELECT   ID
              FROM           XmasPointsByDepartment
              WHERE          (MonthDay = $intDay) AND (DepartmentID = $intDepartmentID))
              UPDATE         XmasPointsByDepartment
              SET            BasicPoints = $intBasicPoints, 
                             Limit = $intLimit
              WHERE          (MonthDay = $intDay) 
              AND            (DepartmentID = $intDepartmentID)
              ELSE        
              INSERT  INTO   XmasPointsByDepartment(BasicPoints, Limit, MonthDay, DepartmentID)
                      VALUES   ($intBasicPoints, $intLimit, $intDay, $intDepartmentID)";

    sqlsrv_query($db, $query);

}
else {


  $query = "SELECT     BasicPoints, Limit
            FROM       XmasPointsByDepartment
            WHERE     (MonthDay = $intDay) 
            AND       (DepartmentID = $intDepartmentID)";

    $request = sqlsrv_query($db, $query);
    $row = sqlsrv_fetch_array($request);
    $intBasicPoints = $row['BasicPoints'];
    $intLimit = $row['Limit'];

echo '<form id="xmasform">';
echo '<table class="redtable" width="600px">';
echo '<tr height="30px">';
echo '<th colspan="2">Editing Christmas Points</th>';
echo '</tr>';



echo '<th width="100px">Basic Points</th>';
echo '<td><input type="text" name="basicpoints" size="10" value="'.$intBasicPoints.'"></td>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px">Maximum Points</th>';
echo '<td><input type="text" name="limit" size="10" value="'.$intLimit.'"></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td><input type="submit" value="Update" name="Update"></td>';
echo '</tr>';
echo '</table>';
echo '<input type="hidden" name="departmentid" value="'.$intDepartmentID.'">';
echo '<input type="hidden" name="day" value="'.$intDay.'">';
echo '</form>';
?>
<script type="text/javascript">
  $('document').ready(function(){
    $('#xmasform').validate({
      rules:{
        "basicpoints":{
          required:true,
          min: -10,
          max: 30,
        },
        "limit":{
        }
      },
      submitHandler: function(form) {
        $.ajax({type:'POST', url: 'page-includes/admin/xmas-points-edit-day.php', data:$('#xmasform').serialize(), success: function(data) {
          $.facebox.close();
          ShowXmasPointsAdmin(<?php echo $intDepartmentID?>);
        }});
      }
  })
});


</script>

<?php
 }
?>