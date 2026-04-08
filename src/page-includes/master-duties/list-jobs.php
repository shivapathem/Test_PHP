<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/master-jobs-functions.php';


$strSessionAdditionalFields = 'JobsUserFields';
$intListType = 0;
$intAreaID = $_SESSION['user']['AreaID'];

$rsJobs = GetAllMasterJobs($intAreaID, $intListType);
if (isset($rsJobs)) {
  $arrMasterJobs = JobsRsToArray($rsJobs);
}

       
echo '<table id="joblist" class="compact stripe bluetable handcursor" width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th width="200px">Job</th>';
echo '<th>Start</th>';
echo '<th>End</th>';
echo '<th>Job Type</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if (isset($arrMasterJobs)) {
foreach ($arrMasterJobs as $intJobID => $arrJob) {
  echo '<tr class="draggable" jobid="'.$intJobID.'" id="'.$intJobID.'" lastmoddate="'.$arrJob['lastmoddate'].'">';
  echo '<td jobid="'.$intJobID.'">';
  echo $arrJob['job'];
  echo '</td>';
  echo '<td jobid="'.$intJobID.'">';
  echo $arrJob['starttime'];
  echo '</td>';
  echo '<td jobid="'.$intJobID.'">';
  echo $arrJob['endtime'];
  echo '</td>';
  echo '</td>';
  echo '<td jobid="'.$intJobID.'">';
  echo $arrJob['jobtype'];
  echo '</td>';  
  echo '</tr>';
}
}
echo '</tbody>';
echo '</table>';
  
?>
  
 
  
<script type="text/javascript"> 
$(document).ready(function(){
  $("#joblist").DataTable({
    paging: false,
    scrollY: 250, 
    info:     false,
    stateSave: true,
  });
  var table = $('#joblist').DataTable();
  $('#joblist tbody').on( 'mouseup', 'tr', function () {
    if ( $(this).hasClass('selected') ) {
      $(this).removeClass('selected');
    }
    else {
      table.$('tr.selected').removeClass('selected');
      $(this).addClass('selected');
    }
  });    
})

$(function() {
  $(".draggable" ).draggable({
    cursor: "move",
    appendTo: '#drag_helper',
    revert: "invalid",
    containment: "document",
    helper: "clone",
    zIndex: 100,
    onStartDrag:function(){
      $(this).draggable('options').cursor = 'not-allowed';
      $(this).draggable('proxy').css('z-index',10);
    },
    onStopDrag:function(){
        $(this).draggable('options').cursor='move';
    }
  });
});
  

</script>