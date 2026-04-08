<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase();

$strLogin = $_REQUEST['login'];
$intDepartment = $_REQUEST['department'];



if (isset($_REQUEST['Update'])) {
  $intBreakType = $_REQUEST['breaktype'];
  $strQuery = "UPDATE         Staff
               SET            BreaksType = $intBreakType
               WHERE          (DepartmentID = $intDepartment) 
               AND            (Login = '$strLogin')";

  sqlsrv_query($db, $strQuery);

}
else {

  $arrBreakTypes = GetBreaksTypes();
  
  $strQuery = "SELECT          Forename + N' ' + Surname AS FullName, BreaksType
               FROM            Staff
               WHERE           (DepartmentID = $intDepartment) 
               AND             (Login = '$strLogin')";
  $rsBreak = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsBreak);

  $intBreaksType = $row['BreaksType'];
  $strFullName = $row['FullName'];
  
  echo '<form id="neweditstaffbreakform">';
  echo '<table class="tablesmalltidy" width="500px">';
  echo '<tr height="30px">';
  echo '<th colspan="2">';
  echo 'Edit Break for '.$strFullName;
  echo '</th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td valign="top">Break Type</td>';
  echo '<td>';
  echo '<select size="1" name="breaktype">';    
    foreach ($arrBreakTypes as $intID => $arrBreakType) {
      if ($intBreaksType == $intID){
        echo '<option selected value="'.$intID.'">'.$arrBreakType['Description'].'</option>';
      }
      else {
        echo '<option value="'.$intID.'">'.$arrBreakType['Description'].'</option>';
      }
    }
    echo '</select>';
  
  echo '</td>';
  echo '</tr>';  

  echo '<tr>';
  echo '<td></td>';
  echo '<td><input type="submit" value="Update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';                
  echo '</table>';
  echo '<input type="hidden" name="login" value="'.$strLogin.'">';  
  echo '<input type="hidden" name="department" value="'.$intDepartment.'">';  
  echo '</form>';  

             
?>

<script type="text/javascript">
  $('document').ready(function(){
    $('#neweditstaffbreakform').validate({
        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/depts-update-staff-breaks.php', data:$('#neweditstaffbreakform').serialize(), success: function(data) {
            $.facebox.close();
            ShowStaffThisDepartment('<?php echo $intDepartment?>');            
          }});
        }  
  })
  });
</script>

<?php
}
?>  

