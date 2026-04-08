<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
if (($intSysAdmin == 1) || !empty($userDivisionsList)) {
  echo '<form id="findpeople">'; 
  echo '<table class="tablesmalltidy" width="1000px">';
  echo '<tr>';
  echo '<th colspan="2" height="30px">';
  echo 'Find Staff in Scheduall who are not yet added to this site.';
  echo '</th>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td>';
  echo 'Enter any part of the name';
  echo '</td>';
  echo '<td>';
  echo '<input name="personname" id="personname" size="40" onfocus="this.select();">';
  echo '</td>';  
  echo '</tr>';
  
  echo '<tr>';  
  echo '<td></td>';
  echo '<td><input id="submit" name="submit" type="submit" value="Search">&nbsp;&nbsp;</input><input type="button" value="Close" onclick="cancel()"></td>';
  echo '</tr>';  

  echo '</table>';  
  echo '</form>'; 
  echo '<div style="overflow-y:scroll; position: relative; width=1000px; max-height:500px;" id="persondetails">';
  echo '</div>';
} else {
  echo 'Access Denied'; die;
}
?>

<script type="text/javascript">

$('document').ready(function(){
    $('#findpeople').validate({
      errorLabelContainer: "#errorBox",
      rules:{
        "PersonName":{
          required:true,
        }

      },
      messages: {
        PersonName: "Please Part of the name.<br>",
      },

        submitHandler: function(form) {
          $.ajax({type:'POST', url: 'page-includes/admin/list-scheduall-staff.php', data:$('#findpeople').serialize(), success: function(data) {
             $('#persondetails').html(data);
          }});
        }
    })
  });



</script>

