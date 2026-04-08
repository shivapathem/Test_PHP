<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';
$teamID = $_REQUEST['department'];
  
  $db = OpenDatabase();
  $allstaff = ListAllStaff($teamID);
  if (isset($allstaff)) {
    echo '<table id="spdstafflist-'.$teamID.'" class="tablesmall compact stripe">';
    echo '<thead>';
    echo '<tr height="30px">';  
    echo '<th width="300px">';  
    echo 'Staff';
    echo '</th>'; 
    echo '</tr>';
    echo '</thesd>';    
    echo '<tbody>';    
    echo '<tr>';
    echo '<td>';

    foreach($allstaff as $staffid => $details) {
      echo '<tr>';
      echo '<td class="handcursor" onclick="javascript:ShowProgsCanDo(\''.$details['staffnumber'].'\')";>';
      echo $details['name'];
      echo '</td>';  
      echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';    




  }
  else {
    echo 'No Staff Found!';
  }

  
?>

<script type="text/javascript">
$(document).ready(function(){

    var table = $("#spdstafflist-<?php echo $teamID?>").DataTable({
      paging: false,
      scrollY: 400, 
      info:     false,
      stateSave: true,
      "initComplete": function( settings, json ) {
      //DoResize();
      }
    });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]); 


  $('#spdstafflist-<?php echo $teamID?> tbody').on( 'click', 'tr', function () {
    if ( $(this).hasClass('selected') ) {
      $(this).removeClass('selected');
    }
    else {
      table.$('tr.selected').removeClass('selected');
      $(this).addClass('selected');
    }
  });
   
});
</script>