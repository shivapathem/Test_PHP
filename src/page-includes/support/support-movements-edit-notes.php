<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$id = $_REQUEST['id'];
$activitytype = $_REQUEST['activitytype'];
$intDepartmentID = $_REQUEST['department'];
$intTabOption = $_REQUEST['taboption'];





// activitytype
// 1 = Attachment Start
// 2 = Attachment End
// 3 = FWR Start
// 4 = FWR End
// 5 = FTC Start
// 6 = FTC End
$db = OpenDatabase();

if (isset($_POST['Update'])) {
  $notes = $_REQUEST['Notes'];
  $notes = escapeSingleQuotes($notes);
  switch ($activitytype) {
    case 1:
      $query = "UPDATE    staff_status
                SET       AttachStartNotes = '$notes'
                WHERE     (id = $id)";
                break;
  case 2:
    $query = "UPDATE    staff_status
              SET       AttachEndNotes = '$notes'
              WHERE     (id = $id)"; 
              break;              
  case 3:
    $query = "UPDATE    staff_status
              SET       FWRStartNotes = '$notes'
              WHERE     (id = $id)";
              break;
  case 4:
    $query = "UPDATE    staff_status
              SET       FWREndNotes = '$notes'
              WHERE     (id = $id)";
              break;              
  case 5:
    $query = "UPDATE    staff_status
              SET       FTCStartNotes = '$notes'
              WHERE     (id = $id)";
              break;
  case 6:
    $query = "UPDATE    staff_status
              SET       FTCEndNotes = '$notes'
              WHERE     (id = $id)";
              break;
}
  sqlsrv_query($db, $query); 
  
}
                                           
else {

  $query = "SELECT      staff_status.FWRStartNotes, staff_status.FWREndNotes, staff_status.AttachStartNotes, staff_status.AttachEndNotes, staff_status.FTCStartNotes, 
                        staff_status.FTCEndNotes, Staff.Surname, Staff.Forename + N' ' + Staff.Surname AS FullName
            FROM        staff_status 
            INNER JOIN  Staff ON staff_status.staffid = Staff.ID
            WHERE       (staff_status.id = $id)";

  $rsNotes = sqlsrv_query($db, $query);
  $row = sqlsrv_fetch_array($rsNotes);
  // Which Field?
  switch ($activitytype) {
    case 1:
      $txtNotes = $row['AttachStartNotes']; 
      break;
    case 2:
      $txtNotes = $row['AttachEndNotes']; 
      break;
    case 3:
      $txtNotes = $row['FWRStartNotes']; 
      break;
    case 4:
      $txtNotes = $row['FWREndNotes']; 
      break;              
    case 5:
      $txtNotes = $row['FTCStartNotes']; 
      break;
    case 6:
      $txtNotes = $row['FTCEndNotes']; 
      break;
  }
  echo '<form id="movementcommentsform">';
  echo '<table class="tablesmall" width="600px">';
  echo '<tr>';
  echo '<th colspan="2"><br>Editing Notes for '.$row['FullName'].'<br><br></th>';
  echo '</tr>';
  echo '<tr>';
  echo '<th valign="top" width="200px">Notes</th>';
  echo '<td><textarea rows="6" name="Notes" cols="60">'.$txtNotes.'</textarea></td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td colspan="2" align="center"><input type="submit" value="Update" name="Update">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$id.'">';
  echo '<input type="hidden" name="activitytype" value="'.$activitytype.'">';  
  echo '<input type="hidden" name="Update" value="Update">'; 
  echo '</form>';

?>
<script type="text/javascript">

$('document').ready(function(){
  $('#movementcommentsform').validate({
    submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
    $.ajax({type:'POST', url: 'page-includes/support/support-movements-edit-notes.php', data:$('#movementcommentsform').serialize(), success: function(data) {
      $.facebox.close();
      GetSupportTabContent (<?php echo $intDepartmentID?>, <?php echo $intTabOption?>);
    }});
  }
  })
});

</script> 
<?php
}
?>