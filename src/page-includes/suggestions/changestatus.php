<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/suggestionsfunctions.php';

$id = $_REQUEST['id'];
if (isset($_REQUEST['submit'])) {
  $db = OpenDatabase();
  $intStatus = escapeSingleQuotes($_REQUEST['status']); 
 
  $dteNow = date("Y-m-d H:i:s");
  $strQuery = "SELECT        Status
               FROM          dbo.WebSiteSuggestions
               WHERE        (id = $id)";
  $rsSuggestion = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsSuggestion);
  $intOldStatus = $row['Status'];  
  
  $strHistory = "<hr>Suggestion status changed from ".$arrStatus[$intOldStatus]." to ".$arrStatus[$intStatus]." by ".$_SESSION['user']['FullName']." on ".date("jS M Y")." at ".date("H:i")."<hr>";
  $strHistory = escapeSingleQuotes($strHistory);  
  $strQuery = "UPDATE       dbo.WebSiteSuggestions
               SET          Status = $intStatus, 
               History = CONCAT(ISNULL(History,''), N'$strHistory')
               WHERE        (id = $id)";
  
  sqlsrv_query($db, $strQuery);
}

else {
  $db = OpenDatabase();
  $strQuery = "SELECT        Subject, Status
               FROM          dbo.WebSiteSuggestions
               WHERE        (id = $id)";
  $rsSuggestion = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsSuggestion);
  $strSubject = $row['Subject'];
  $intStatus = $row['Status'];


  echo '<form id="suggestionstatus">';
  echo '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 625px; z-index: 101;">';
  echo '<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">';
  echo '<span id="ui-id-5" class="ui-dialog-title">Change Suggestion Status</span>';
  echo '</div>';
  echo '<div style="width: auto; min-height: 0px; max-height: none;" class="ui-dialog-content ui-widget-content">';   
  echo '<table class="redtable" width="600px">';
  
  echo '<tr>';  
  echo '<td>Title</td>';
  echo '<td>';
  echo $strSubject;
  echo '</td>';
  echo '</tr>';

  echo '<tr>';  
  echo '<td>Current Status</td>';
  echo '<td>';
  echo $arrStatus[$intStatus];
  echo '</td>';
  echo '</tr>';
  
  echo '<tr>';  
  echo '<td>New Status</td>';
  echo '<td>';
    echo '<select class="chosen-select" style="width:250px" size="1" name="status" id="status">';
    foreach ($arrStatus as $key=>$value) {
      if ($key == $intStatus) {
          echo '<option selected value="'.$key.'">'.$value.'</option>';  
        }
        else {
          echo '<option value="'.$key.'">'.$value.'</option>';
        }
    }
    echo '</select>';
    
  echo '</td>';
  echo '</tr>';
  

  echo '<tr>';  
  echo '<td></td>';
  echo '<td><input id="submit" name="submit" type="submit" value="Update"></input><input type="button" name="Cancel" value="Cancel" onclick="cancel()"></td>';  
 
  echo '</tr>';
    
  echo '</table>';
  echo '</div>';
  echo '<input type="hidden" name="id" value="'.$id.'">';
  
  echo '</form>';
?>
<script type="text/javascript">
$('document').ready(function(){
    $('#suggestionstatus').validate({
      submitHandler: function(form) {
        $('input[type="submit"]').prop('disabled', true);
        $.ajax({type:'POST', url: 'page-includes/suggestions/changestatus.php', data:$('#suggestionstatus').serialize(), success: function(data) {
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