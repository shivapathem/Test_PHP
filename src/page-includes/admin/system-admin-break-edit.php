<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase();

$intTypeID = $_REQUEST['typeid'];
$intID = $_REQUEST['id'];
if (isset($_REQUEST['Update'])) {
  $strStartDate = $_REQUEST['sDate'];
  $intHours = $_REQUEST['duration'];
  $intBreakTime = $_REQUEST['breakhours'];

  if ($intID == 0) {
    $strQuery = "INSERT INTO BreaksTable (FromDate, Hours, BreakTime, TypeID)
                 VALUES      (CONVERT(DATETIME, '$strStartDate 00:00:00', 102), 
                             $intHours, 
                             $intBreakTime, 
                             $intTypeID)";
  }
  else {
    $strQuery = "UPDATE       BreaksTable
                 SET          FromDate = CONVERT(DATETIME, '$strStartDate 00:00:00', 102), 
                              Hours = $intHours, 
                              BreakTime = $intBreakTime
                 WHERE        (ID = $intID)";

  }

  sqlsrv_query($db, $strQuery);

}

  if ($intID == 0) {
    $strStartDate = "2019-01-01"; //date("Y-m-d");
    $intHours = "";
    $intBreakTime = "";
  }
  else {
    $strQuery = "SELECT       FromDate, Hours, BreakTime
                 FROM         BreaksTable
                 WHERE        (ID = $intID)";
    $rsBreak = sqlsrv_query($db, $strQuery);
    $row = sqlsrv_fetch_array($rsBreak);
    $strStartDate = $row['FromDate']->format('Y-m-d');
    $intHours = $row['Hours'];
    $intBreakTime = $row['BreakTime'];
  }  
  
  echo '<form id="neweditbreakform">';
  echo '<table class="tablesmalltidy" width="500px">';
  echo '<tr height="30px">';
  echo '<th colspan="3">';
  echo 'Edit Break';
  echo '</th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td valign="top">Start Date</td>';
  echo '<td align="right"><input type="hidden" name="sDate" id="datepicker-start" value="'.$strStartDate.'" required/></td>';
  echo '<td><input type="text" id="salternate" size="30" value="'.date("l, j F, Y", strtotime($strStartDate)).'" readonly="true"></td>';  
  echo '</tr>';
  
  echo '<tr>';
  echo '<td valign="top" colspan="2">Duty Duration</td>';
  echo '<td><input type="text" name="duration" size="10" value="'.$intHours.'"></td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td valign="top" colspan="2">Break Hours</td>';
  echo '<td><input type="text" name="breakhours" size="10" value="'.$intBreakTime.'"></td>';
  echo '</tr>';  

  echo '<tr>';
  echo '<td></td>';
  echo '<td><input type="submit" value="Update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  if ($intID != 0) {
    echo '<td align="right"><img border="0" src="images/delete.gif" width="18" height="17" Style="cursor: pointer" onclick="javascript:DeleteBreak('.$intID.','.$intTypeID.')"; ></td>';
  }
  else {
    echo '<td></td>';
  }
  echo '</tr>';                
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$intID.'">';  
  echo '<input type="hidden" name="typeid" value="'.$intTypeID.'">';  
  echo '</form>';  

             
?>
<div id="dialog-delete-break" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete this Break?.</p>
</div>
<script type="text/javascript">
  $('document').ready(function(){
    $('#neweditbreakform').validate({
      rules:{
        "datepicker-start":{
          required:true,
          date: true,
        },
        "duration":{
          required:true,
          min: 0,
          max: 36,
        },
         "breakhours":{
          required:true,
          min: 0,
          max: 10,
        }},

        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/system-admin-break-edit.php', data:$('#neweditbreakform').serialize(), success: function(data) {
            $.facebox.close();
            FillBreaksHolder(<?php echo $intTypeID?>);            
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