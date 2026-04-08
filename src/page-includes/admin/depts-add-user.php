<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
 
$intDepID = $_REQUEST['department']; 
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
if (isset($_REQUEST['submit'])) {
}
else {
  // ###################################################################### Get the departments this user can administer.....
  $arrAdminDepts = GetAdminDepts($strUser, 1);

  echo '<table class="tablesmalltidy" width="800px">';
  echo '<tr>';
  echo '<th colspan="2" height="30px">';
  echo 'Add person to for '.$arrAdminDepts[$intDepID]['Description'];
  echo '</th>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td>';
  echo 'Enter the start of the Surname';
  echo '</td>';
  echo '<td>';
  echo '<input name="NameSearch" id="NameSearch" size="40" onfocus="this.select();" onmouseup="return false;" />';
  echo '</td>';  
  echo '</tr>'; 

  echo '<tr>';
  echo '<td>';
  echo '</td>';
  echo '<td>';
  echo '<input type="button" value="Cancel" onclick="cancel()">';
  echo '</td>';  
  echo '</tr>';



   
  echo '</table>';  

?>

<script type="text/javascript">

$(document).ready( function () {
  $('#NameSearch').focus();
})

$(function() {
  $( "#NameSearch" ).autocomplete({
    source: "page-includes/admin/calls/list-sched-available.php",
    minLength: 1,
    select: function( event, ui ) {
      AddNewName ( ui.item.id);
    }
  });
});

function AddNewName (login) {
  $.post("page-includes/admin/deptsadduser.php", {
  login: login,
  department: <?php echo $intDepID?>,
  isscheduled: 1
  },
  function(data,status){
    $.facebox.close();
    ShowStaffThisDepartment(<?php echo $intDepID?>)
   }
  )
}


</script>

<?php




  
}
?>