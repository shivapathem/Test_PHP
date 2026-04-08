<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
 
  echo '<table class="tablesmalltidy" width="1000px">';
  echo '<tr>';
  echo '<th colspan="2" height="30px">';
  echo 'Find Staff in Allocate or Guest Users on this site.';
  echo '</th>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td>';
  echo 'Enter the start of the Surname';
  echo '</td>';
  echo '<td>';
  echo '<input name="PersonSearch" id="PersonSearch" size="40" onfocus="this.select();" onmouseup="return false;" />';
  echo '</td>';  
  echo '</tr>'; 

  echo '</table>';  

  echo '<div style="overflow-y:scroll; position: relative; width=1000px; max-height:500px;" id="persondetails">';
  echo '</div>';

?>

<script type="text/javascript">

$(document).ready( function () {
  $('#PersonSearch').focus();
})

$(function() {
  $( "#PersonSearch" ).autocomplete({
    source: "page-includes/admin/calls/list-available-people.php",
    minLength: 1,
    select: function( event, ui ) {
      DisplayPerson ( ui.item.id);
    }
  });
});

function DisplayPerson (login) {
  $.post("page-includes/admin/userinfo.php", {
    login: login,
  },
  function(data,status){
    $('#persondetails').html(data);
   }
  )
}


</script>

