<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();

$intID = $_REQUEST["id"];
$intDepartmentID = $_REQUEST["department"];

if (isset($_REQUEST['Update'])) {
  $strDescription = $_REQUEST["Description"];
  if ($intID == 0) {
    $query = "INSERT INTO workingdays
              (description, DepartmentID, isworking)
              VALUES (
              N'$strDescription',
              $intDepartmentID,
              0
              )";
                     
    sqlsrv_query($db, $query);
    echo $query;
  }
  else {
   $query = "UPDATE workingdays
              SET description = N'$strDescription'
              WHERE  (ID = $intID)";
  
    sqlsrv_query($db, $query);  
  } 
}
else {
  $query = "SELECT id, description, base
            FROM workingdays
            WHERE  (id = $intID)";

  $rsoff = sqlsrv_query($db, $query);
  $row = sqlsrv_fetch_array($rsoff);
  $strDescription = $row['description'];
  
  echo '<form id="offdedit">';   
  echo '<table class="tablesmalltidy" width="500px">';
  echo '<tr>';
  echo '<th colspan="2" class="medtextboldcentre">';
  echo '<br>Edit a Day Off<br><br>';
  echo '</th>';
  echo '</tr>';
  echo '<tr>';
  echo '<th valign="top">Key</th>';
  echo '<td><input type="text" name="Description" size="20" value="'.$strDescription.'"></td>';
  echo '</tr>';
  echo '<td>&nbsp;</td>';
  echo '<td><input type="submit" value="update" name="Update"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '<input type="hidden" name="department" value="'.$intDepartmentID.'">';
  echo '</form> ';

?>
<script type="text/javascript">

$('#offdedit').validate({
  rules: {  
    Description: {
      required: true,
      maxlength: 20
    },     
  },
  errorPlacement: function(){
   return false;
  },
  submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/admin/depts-off-day-edit.php', data:$('#offdedit').serialize(), success: function(data) {
        $.facebox.close();        
        ShowOnOffDays(<?php echo $intDepartmentID?>); 
        
                  
      }});
  } 
});


</script>


<?php
}
?>