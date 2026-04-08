<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';

$pdo = OpenDBLinkA7();

$intTypeID = $_REQUEST['typeid'];

if (isset($_REQUEST['Update'])) {
  $strDescription = $_REQUEST['description'];
  
  $intDefaultTeam = $_REQUEST['DefaultTeam'];
  
  if ($intTypeID == 0) {
    $strQuery = "INSERT INTO BreaksTableTypes
                            (Description, schedulingTeamId)
                 VALUES     ('$strDescription', $intDefaultTeam)";
  }
  else {
    $strQuery = "UPDATE       BreaksTableTypes
                 SET          Description = '$strDescription',
                 schedulingTeamId = $intDefaultTeam
                 WHERE        (ID = $intTypeID)";

  }

  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();

}

  if ($intTypeID == 0) {
    $strTypeName = "";
    $intDefaultTeam = 0;    
  }
  else {    
    $arrBreak =  GetBreakTypeDesc($intTypeID);
    $strTypeName = $arrBreak['Description'];
    $intDefaultTeam = $arrBreak['DefaultTeam'];    
  }  

  $userTeamLists = json_decode(getUserAllTeamsLists(),true);
  echo '<form id="neweditbreaktypeform">';
  echo '<table class="tablesmalltidy" width="500px">';
  echo '<tr height="30px">';
  echo '<th colspan="2">';
  echo 'Edit Break Type';
  echo '</th></tr>';

  echo '<tr><td valign="top">Description</td>';
  echo '<td><input type="text" name="description" size="20" style="width: 188px;" value="'.$strTypeName.'"></td></tr>';  

  echo '<tr>';
  echo '<td valign="top">Default Scheduling Team</td>';
  echo '<td>';
  echo '<select size="1" name="DefaultTeam" class="chosen-select">';  
  echo '<option value="">Select The Team</option>';
  foreach ($userTeamLists as $teamDetail) {
    if ($intDefaultTeam == $teamDetail['TeamID']){
      echo '<option selected value="'.$teamDetail['TeamID'].'">'.$teamDetail['TeamName'].'</option>';
    }
    else {
      echo '<option value="'.$teamDetail['TeamID'].'">'.$teamDetail['TeamName'].'</option>';
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

  echo '<input type="hidden" name="typeid" value="'.$intTypeID.'">';  
  echo '</form>';  

             
?>
<div id="dialog-delete-break" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete this Break?.</p>
</div>
<script type="text/javascript">
$('document').ready(function(){

  $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
  $.validator.setDefaults({ ignore: ":hidden:not(.chosen-select)" });
  $('#neweditbreaktypeform').validate({
    rules:{
    "description":{
    required:true,
    },
    "DefaultTeam":{
    required:true,
    }},

    submitHandler: function(form) {
      $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/admin/system-admin-break-type-edit.php', data:$('#neweditbreaktypeform').serialize(), success: function(data) {
          $.facebox.close();
          ShowBreaksTable();
      }});
    }
  })
});

  $(function() {
    $( "#datepicker-start" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#salternate",
      altFormat: "DD, d MM, yy"      
    });
  });             


function DeleteBreak (id) {
  $( "#dialog-delete-break" ).dialog({
    width:500,
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/admin/system-admin-break-delete.php", {
        id: id
      },
      function(data,status){
        $.facebox.close();       
        FillBreaksHolder(<?php echo $intTypeID?>); 
      });
      },
      "No": function() {
        $( this ).dialog( "close" );
      },
    }
  }); 
}