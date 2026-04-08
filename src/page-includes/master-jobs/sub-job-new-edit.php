<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';

$intJobID = $_REQUEST["jobid"];
if (isset($_REQUEST["subjobid"])) {
  $intSubJobID = $_REQUEST["subjobid"];
}
else {
  $intSubJobID = 0;
}



if (isset($_POST["submit"])) {            //Update the Master Job

}
else {

$rsJobTypes = GetAllJobTypes();
$starttime = 0;
$endtime = 0;
echo '<div style="width: 600px">';
echo '<form id="neweditsubjob">';
echo '<table id="jobnewedittable" class="smalltable bluetable" width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th colspan="5">Editing Sub Job...</th>';

echo '</tr>';
echo '</thead>';
echo '<tbody>';

echo '<tr>';
echo '<td>Start Time</td>';
echo '<td><input id="SubStartTime" name="SubStartTime" type="text" class="time" size="10" value="'.FormatTime($starttime).'"/></td>';
echo '<td>End Time</td>';
echo '<td><input id="SubEndTime" name="SubEndTime" type="text" class="time" size="10" value="'.FormatTime($endtime).'"/></td>';



echo '<tr>';
echo '<td>Job Type</td>';
echo '<td colspan="3">';
echo '<select name="JobType" id="JobType">';  

$rsJobTypes = json_decode($rsJobTypes,true);

foreach($rsJobTypes as $jobType){
  if ($intSubJobID == $jobType['JobTypeid']) {
  echo '<option value="'.$jobType['JobTypeid'].'"" selected>'.$jobType['JobTypeName'].'</option>';
}else{
  echo '<option value="'.$jobType['JobTypeid'].'"" >'.$jobType['JobTypeName'].'</option>';
}
}   
echo '</select>';
echo '</td>';
echo '</tr>';


echo '<tr>';  
echo '<td></td>';
echo '<td colspan="3"><input name="submit" type="submit" value="Submit"></input></td>';
echo '</tr>';
                                                                                          
echo '</tbody>';
echo '</table>';
echo '<input type="hidden" name="jobid" value="'.$intJobID.'">';
echo '<input type="hidden" name="subjobid" value="'.$intSubJobID.'">';

echo '</form>';  
echo '</div>';


}

?>

<script type="text/javascript">

$(document).ready(function(){ 

  $('#neweditsubjob').validate({
    debug: true,
    rules:{           
      "SubStartTime":{
        required:true,
      },
      "SubEndTime":{
        required:true,
      }            
    },
      messages:{      
        "SubStartTime":{
          required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the start time.' />"
        },          
        "SubEndTime":{
          required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the end time.' />"
        },
      },
      submitHandler: function(form) {
        $.ajax({type:'POST', url: 'page-includes/master-jobs/sub-job-new-edit.php', data:$('#neweditsubjob').serialize(), success: function(data) {

        //$.facebox.close(); 
        }});
      }
    })
});

$(function() {
  $('#SubStartTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

$(function() {
  $('#SubEndTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});


</script>



