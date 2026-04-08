<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';


$intDeptID = $_REQUEST['department'];
$strStaffLogin = $_REQUEST['login']; 
if (isset($_REQUEST['showweek'])) {
  $intShowWeek = 1;
}
else {
  $intShowWeek = 0;
}



$db = OpenDatabase();
if (isset($_REQUEST['submit'])) {

  $strSurname = escapeSingleQuotes($_REQUEST['Surname']);
  $strForename = escapeSingleQuotes($_REQUEST['Forename']);
  $strSortCode = escapeSingleQuotes($_REQUEST['SortCode']);
  $strEmail1 = escapeSingleQuotes($_REQUEST['Email']);

    $strQuery = "UPDATE       Staff
                 SET          Surname = '$strSurname', Forename = '$strForename', SortCode = '$strSortCode', Email1 = '$strEmail1'
                 WHERE        (DepartmentID = $intDeptID) AND (Login = N'$strStaffLogin')";


  sqlsrv_query($db, $strQuery);
    


}
else {
  $strQuery = "SELECT        Surname, Forename, Login, SortCode, Email1
               FROM          Staff
               WHERE         (DepartmentID = $intDeptID) AND (Login = '$strStaffLogin')";
               
  $rsUser = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsUser); 
  $strSurname = $row['Surname'];
  $strForename = $row['Forename'];
  $strSortCode = $row['SortCode'];
  $strEmail1 = $row['Email1'];

  echo '<form id="editstaff">';

  echo '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 525px; z-index: 101;">';
  echo '<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">';
  echo '<span id="ui-id-5" class="ui-dialog-title">Edit User '.$strForename.' '.$strSurname.'</span>';
  echo '</div>';
  echo '<div style="width: auto; min-height: 0px; max-height: none;" class="ui-dialog-content ui-widget-content">';
  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>';
  echo '<table class="redtable">';
  
  echo '<tr>';
  echo '<td>Surname</td>';
  echo '<td>';
  echo '<input id="Surname" name="Surname" type="text" size="50" value="'.$strSurname.'"/>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>Forename</td>';
  echo '<td>';
  echo '<input id="Forename" name="Forename" type="text" size="50" value="'.$strForename.'"/>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>Sort Code</td>';
  echo '<td>';
  echo '<input id="SortCode" name="SortCode" type="text" size="50" value="'.$strSortCode.'"/>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>eMail</td>';
  echo '<td>';
  echo '<input id="Email" name="Email" type="text" size="50" value="'.$strEmail1.'"/>';
  echo '</td>';
  echo '</tr>'; 
  
  echo '<tr>';
  echo '<td></td>';
  echo '<td><input id="submit" name="submit" type="submit" value="Submit">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
     
  echo '</table>';
  echo '</div>';

  echo '<input type="hidden" name="department" value="'.$intDeptID.'">';
  echo '<input type="hidden" name="login" value="'.$strStaffLogin.'">';
  echo '</form>';
  echo '</div>';
?>
<script type="text/javascript">
$('document').ready(function(){
    $('#editstaff').validate({
      errorLabelContainer: "#errorBox",
      rules:{
        "Surname":{
          required:true,
        },
        "Forename":{
          required:true,
          //date: true
        }
      },
      messages: {
        Surname: "Please Enter a Surname<br>",
        Forename: "Please Enter a Forename<br>",
      },

        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/edituser.php', data:$('#editstaff').serialize(), success: function(data) {
          $.facebox.close();
          <?php
          if ($intShowWeek == 0) {
            echo 'ShowStaffThisDepartment('.$intDeptID.');';
          }
          else {
            echo 'ShowAllocations('.$intDeptID.');';
          }
          ?>  
          
          
          
          }});
        }
    })
  });
</script> 

<?php
}

?>