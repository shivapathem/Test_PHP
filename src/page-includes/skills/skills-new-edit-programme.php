<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$id = $_REQUEST["id"];
$intDepartmentID = $_REQUEST['department'];



if (isset($_POST['Update'])) {
  $prog = trim($_REQUEST["prog"]);
  if ($id == 0) {
    $query = "INSERT INTO skills_programmes
              (programmename, DepartmentID)
              VALUES (
              '$prog',
              $intDepartmentID
              )";

    sqlsrv_query($db, $query);
  }
  else {
   $query = "UPDATE skills_programmes
              SET programmename = N'$prog'
              WHERE  (ID = $id)";

    sqlsrv_query($db, $query);
  }
 echo $query;
}
else {
  if ($id == 0) {
    $prog = '';
  }
  else {

    $query = "SELECT programmename
              FROM skills_programmes
              WHERE  (ID = $id)";

    $rsprog = sqlsrv_query($db, $query);
    $row = sqlsrv_fetch_array($rsprog);
    $prog = $row['programmename'];
  }

  echo '<form id="prognameeditform">';
  echo '<table class="tablesmalltidy" width="100%">';
  echo '<tr>';
  echo '<th colspan="2"><br>';
  if($id == 0) {
    echo 'New Skill';
  }
  else {
    echo 'Edit Skill';
  }
  echo '<br><br></th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td valign="top">Skill Description</td>';
  echo '<td><input type="text" id="prog" name="prog" size="30" value="'.$prog.'"></td>';
  echo '</tr>';

  echo '<td><input type="submit" value="update" name="Update"></td>';
  
  if ($id != 0) {
    echo '<td align="right"><img border="0" src="images/delete.gif" width="18" height="17" Style="cursor: pointer" onclick="javascript:DeleteProgramme('.$id.','.$intDepartmentID.')"; ></td>';
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
    $('#prognameeditform').validate({
      rules:{
         "prog":{
          required:true,
          }
        },
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/skills/skills-new-edit-programme.php', data:$('#prognameeditform').serialize(), success: function(data) {
            $.facebox.close();
            FillProgrammesList(<?php echo $id?>, <?php echo $intDepartmentID?>);           
          }});
        }         
     })
  });
</script>


<?php
}

?>