<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$id = $_REQUEST["id"];
$intDepartmentID = $_REQUEST['department'];



if (isset($_POST['Update'])) {
  $duty = trim($_REQUEST["duty"]);
  $description = trim($_REQUEST["description"]);
  $bst = $_SESSION['bst'];
  $intDuration = trim($_REQUEST["duration"]);
  
  if ($bst == 0) {
    $gmt = 1;
  }
  else {
    $gmt = 0;  
  }
  
  if ($id == 0) {
    $query = "INSERT INTO skills_duties
              (duty, description, gmt, bst, DurationWith, DepartmentID)
              VALUES (
                '$duty',
                '$description',
                $gmt,
                $bst,
                $intDuration,
                $intDepartmentID  
              )";

    sqlsrv_query($db, $query);
  }
  else {
   $query = "UPDATE skills_duties
              SET duty = '$duty',
              description = '$description',
              DurationWith = $intDuration
              WHERE  (ID = $id)";
    //echo $query;
    sqlsrv_query($db, $query);
  }
//echo $id;

}
else {
  if ($id == 0) {
    $duty = '';
    $description = '';
    $intDuration = 0;
  }
  else {
    $query = "SELECT duty, description, DurationWith
              FROM  skills_duties
              WHERE  (ID = $id)";

    $rsduty = sqlsrv_query($db, $query);
    $row = sqlsrv_fetch_array($rsduty);
    $duty = $row['duty'];
    $description = $row['description'];
    $intDuration = $row['DurationWith'];
  }
  echo '<form id="neweditdutyform">';
  echo '<table class="tablesmalltidy" width="100%">';
  echo '<tr height="30px">';
  echo '<th colspan="2">';
  echo 'Edit Duty';
  echo '</th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td valign="top">Duty Name</td>';
  echo '<td ><input type="text" id="duty" name="duty" size="30" value="'.$duty.'"></td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td  valign="top">Duty Description</td>';
  echo '<td><input type="text" id="description" name="description" size="30" value="'.$description.'"></td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td  valign="top">Duration (Inc Meals)</td>';
  echo '<td><input type="text" id="duration" name="duration" size="30" value="'.$intDuration.'"></td>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td align="center"><input type="submit" value="update" name="Update"></td>';

  if ($id != 0) {
    echo '<td align="right"><img border="0" src="images/delete.gif" width="18" height="17" Style="cursor: pointer" onclick="javascript:DeleteDuty('.$id.')"; ></td>';
  }
  else {
    echo '<td></td>';
  }


  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$id.'">';
  echo '<input type="hidden" name="department" value="'.$intDepartmentID.'">';
  echo '</form> ';

?>

<script type="text/javascript">
$('document').ready(function(){
  $('#neweditdutyform').validate({
    rules:{
      "duty":{
        required:true,
      },
      "description":{
        required:true,
      },
      "duration":{
        required:true,
        min: 0,
        max: 36
      }                   
    },
    submitHandler: function(form) {
      $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/skills/skills-new-edit-duty.php', data:$('#neweditdutyform').serialize(), success: function(data) {
          $.facebox.close();
          FillDutiesList(<?php echo $id?>,<?php echo $intDepartmentID?>);
          FillDutiesPage(<?php echo $id?>,<?php echo $intDepartmentID?>); 
                     
      }});
    }     
  })
});
</script>


<?php
}
?>