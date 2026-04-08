<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';


$id = $_REQUEST['id'];
$intTypeID = $_REQUEST['typeid'];
if (isset($_REQUEST['submit'])) {
  $db = OpenDatabase();
  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  $strSubject = escapeSingleQuotes($_REQUEST['subject']); 
  $strDetails = escapeSingleQuotes($_REQUEST['details']);   
  $strJustification = escapeSingleQuotes($_REQUEST['justification']);
  $strRemedyRef = escapeSingleQuotes($_REQUEST['RemedyRef']);
  if (isset($_REQUEST['progress'])) {
    $strProgress = escapeSingleQuotes($_REQUEST['progress']);  
  } 
  $dteNow = date("Y-m-d H:i:s");
  if ($id == 0) {
    $strHistory = "New Suggestion created by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i");
    $strHistory = escapeSingleQuotes($strHistory);
    
    $strQuery = "INSERT     
                 INTO           WebSiteSuggestions(TypeID, CreatorLog1n, DateCreated, Subject, Details, BusinessJustification, RemedyRef, History)
                 VALUES         ($intTypeID,
                                 N'$strUser', 
                                CONVERT(DATETIME, '$dteNow', 102), 
                                N'$strSubject', 
                                N'$strDetails', 
                                N'$strJustification',
                                '$strRemedyRef',
                                '$strHistory')";
  echo $strQuery;
  
  }
  else {
     $strHistory = "<hr>Suggestion edited by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i");
     $strHistory = escapeSingleQuotes($strHistory);
  
  
    $strQuery = "UPDATE       dbo.WebSiteSuggestions
                 SET          Subject = N'$strSubject', 
                 Details = N'$strDetails', 
                 BusinessJustification = N'$strJustification',
                 Progress = '$strProgress',
                 RemedyRef = '$strRemedyRef', 
                 History = CONCAT(ISNULL(History,''), N'$strHistory')
                 WHERE        (id = $id)";
  
  }
      sqlsrv_query($db, $strQuery);

  
  
}
else {
  if ($id == 0) {
    $strSubject = "";
    $strJustification = "";
    $strProgress = "";
    $strDetails = "";
    $strRemedyRef = "";
  }
  else {
    $db = OpenDatabase();
    $strQuery = "SELECT        Subject, Details, BusinessJustification, Progress, RemedyRef
                 FROM          dbo.WebSiteSuggestions
                 WHERE        (id = $id)";
    echo $strQuery;             
    $rsSuggestion = sqlsrv_query($db, $strQuery);
    $row = sqlsrv_fetch_array($rsSuggestion);
    $strSubject = $row['Subject'];
    $strJustification = $row['BusinessJustification'];
    $strProgress = $row['Progress'];
    $strDetails = $row['Details'];
    $strRemedyRef = $row['RemedyRef'];    
  }


  echo '<form id="editsuggestion">';
  echo '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 625px; z-index: 101;">';
  echo '<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">';
  echo '<span id="ui-id-5" class="ui-dialog-title">New Suggestion</span>';
  echo '</div>';
  echo '<div style="width: auto; min-height: 0px; max-height: none;" class="ui-dialog-content ui-widget-content">'; 
  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>';   
  echo '<table class="redtable" width="600px">';
  
  echo '<tr>';  
  echo '<td>Subject</td>';
  echo '<td>';
  echo '<input id="subject" name="subject" type="text" size="40" value="'.$strSubject.'" />';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';  
  echo '<td>Details</td>';
  echo '<td>';
  echo '<textarea rows="4" name="details" cols="60">'.$strDetails.'</textarea>';
  echo '</td>';
  echo '</tr>';
  
  echo '<tr>';  
  echo '<td>Business Reason</td>';
  echo '<td>';
  echo '<textarea rows="4" name="justification" cols="60">'.$strJustification.'</textarea>';
  echo '</td>';
  echo '</tr>';
  if ($id != 0) {  
    echo '<tr>';  
    echo '<td>Progress</td>';
    echo '<td>';
    echo '<textarea rows="4" name="progress" cols="60">'.$strProgress.'</textarea>';
    echo '</td>';
    echo '</tr>';
  }
  echo '<tr>';  
  echo '<td>Remedy Reference</td>';
  echo '<td>';
  echo '<input id="RemedyRef" name="RemedyRef" type="text" size="40" value="'.$strRemedyRef.'" />';
  echo '</td>';
  echo '</tr>';  
  
  echo '<tr>';  
  echo '<td></td>';
  if ($id == 0) {
    echo '<td><input id="submit" name="submit" type="submit" value="Add"></input><input type="button" name="Cancel" value="Cancel" onclick="cancel()"></td>';
  }
  else {
    echo '<td><input id="submit" name="submit" type="submit" value="Update"></input><input type="button" name="Cancel" value="Cancel" onclick="cancel()"></td>';  
  }
  
  echo '</tr>';
    
  echo '</table>';
  echo '</div>';
  echo '<input type="hidden" name="id" value="'.$id.'">';
  echo '<input type="hidden" name="typeid" value="'.$intTypeID.'">';  
  echo '</form>';
?>
<script type="text/javascript">
$('document').ready(function(){
    $('#editsuggestion').validate({
      errorLabelContainer: "#errorBox",  
      rules:{
        "subject":{
          required:true,
        },
        "details":{
          required:true,
        },                
        "justification":{
          required:true,
        }           
      },
      messages: {
        subject: "Please Enter a Title for this item.<br>",
        details: "Please some Details for this item.<br>",
        justification: "Please Enter a Justification for this item.<br>"
      },      
      submitHandler: function(form) {
        $('input[type="submit"]').prop('disabled', true);
        $.ajax({type:'POST', url: 'page-includes/suggestions/editsuggestion.php', data:$('#editsuggestion').serialize(), success: function(data) {
            $.facebox.close();
            showsuggestions();
            
        }});
      }
    })
});

function cancel() {
    $.facebox.close();          
} 
</script>


<?php

}