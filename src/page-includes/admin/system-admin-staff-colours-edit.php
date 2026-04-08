<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../users/process/classUserSetup.php';
$db = OpenDatabase();
$intID = $_POST["id"];
$colour = $_POST["colour"];
$description = $_POST["description"];
$divisionID = $_POST["divisionID"];
$setupObj = new classUserSetup();
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
if (isset($_POST['Update'])) {
  $strDescription = $_POST["Description"];
  $strDivisionID = $_POST["area"];
  $strColour = $_POST["textColor"];
  if ($intID == 0) {
    $setupObj->createOrUpdateUserColor(0, $strDescription, '', $strDivisionID);
  }
  else {
    $setupObj->createOrUpdateUserColor($intID, $strDescription, $strColour, $strDivisionID);
  } 
}
else {
  $strDescription = $description;
  $strDivisionID = $divisionID;
  $strColour = $colour;
  echo '<table class="tablesmalltidy">';
  echo '<tr>';
  echo '<td valign="top">';
  echo '<form id="wdedit">';
  echo '<table class="tablesmalltidy" width="400px">';
  echo '<tr height="40px">';
  echo '<th colspan="2">';
  echo 'Edit Text Desctiption'; 
  echo '</th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td><label for="area">Area</label></td>';
  echo '<td>';
  echo '<select class="chosen-select smalltext" id="area" name="area" >';
  echo '<option value="">Select Area</option>';
  foreach($userDivisionsList as $division){
    $selected = ($division['DivisionID'] == $strDivisionID) ? 'selected' : '';
    echo '<option value="'.$division['DivisionID'].'" '.$selected.'>'.$division['DivisionName'].'</option>';
  }
  echo '</select>';
  echo '</td>';
  echo ' </tr>';
  echo '<tr>';
  echo '<td>Description</td>';
  echo '<td><input type="text" name="Description" size="47" value="'.$strDescription.'"></td>';
  echo '</tr>';
  echo '<td><input type="hidden" name="textColor" value="'.$strColour.'"></td>';
  echo '<tr>';
  echo '<td colspan="2" align="center"><input type="submit" value="update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$intID.'">';
  echo '</form> ';
  echo '</td>'; 
  echo '</tr>';
  echo '</table>'; 
?>
<script type="text/javascript">
$('document').ready(function(){
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "350px"
  });
})
$('#wdedit').validate({
  rules: {  
    Description: {
      required: true,
      maxlength: 20
    },
    area: {
      required: true
    }
  },
  errorPlacement: function(){
   return false;
  },
  submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/admin/system-admin-staff-colours-edit.php', data:$('#wdedit').serialize(), success: function(data) {
        $.facebox.close();        
        ShowSystemAdminColours(); 
        
                  
      }});
  } 
});
</script>
<?php
}

function drawkey() {
  echo '<table class= "tablesmalltidy" width="300px">';
  echo '<tr>';
  echo '<th colspan="2">Working days require a code.<br>The explanations are below</th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>-</td>';
  echo '<td>A space</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>@</td>';
  echo '<td>Any text character</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>~</td>';
  echo '<td>Any numeric character</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>ABCDE...1234....</td>';
  echo '<td>Literal match (text or number)</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>!</td>';
  echo '<td>No further characters</td>';
  echo '</tr>';  
  echo '<tr>';
  echo '<td colspan="2">All characters are case insensitive</td>';
  echo '</tr>';
  echo '</table>';
}
?>
