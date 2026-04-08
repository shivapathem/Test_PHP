<?php

//date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/AllocationsFunctionsEditing.php';

$base = $_REQUEST['base'];
$date = $_REQUEST['date'];
$CurrTab = $_REQUEST['tab'];


$week = bbcweeknumber($date);
$day = getdayofweek($date);

$db = OpenDatabase();

// Get the department associated with this base
$intDepartmentID = GetDepartmentFromBase($base);

$arrCountEdits = GetEditsByDepartment($intDepartmentID, $week, $day);

  echo '<form id="editjob">';
  echo '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable" style="display: block; height: auto; width: 625px; z-index: 101;">';
  echo '<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">';
  echo '<span id="ui-id-5" class="ui-dialog-title">Delete Allocations</span>';
  echo '</div>';
  echo '<div style="width: auto; min-height: 0px; max-height: none;" class="ui-dialog-content ui-widget-content">';
  echo '<table class="redtable" width="600px">';

echo '<tr>';
echo '<td colspan="2">';
echo '<br>You can delete the copy of the Allocations and Jobs for this day.<br><br>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><br>';
if ($arrCountEdits['QuickDelete'] == 1) {
  echo 'There have been no edits for this day.<br>It is safe to delete the copy<br>Do you wish to proceed?';
  $DelOption = 0;
}
else {
  echo 'There are '.$arrCountEdits['Edits'].' Duties that have been edited, ';
  echo $arrCountEdits['JobEdits'].' Jobs that have been edited, ';
  echo 'and '.$arrCountEdits['InternalEdits'].' Duties that have been internally edited.<br>';
  if ($CurrTab == 4) {
    echo 'You are viewing all edits for this department and if you are sure you can delete the copy of Duties and Jobs for this day.<br>Do you wish to do this?';
    $DelOption = 0;
  }
  else {
      echo 'Because there are edits for this day you must view all the edits for the department before you can delete the copy of this day.<br>Do you want to do this now?';
      $DelOption = 1;
  }
}
echo '<br><br></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
if ($DelOption == 1) {
  echo '<td></input><input type="button" value="Yes" onclick="showall()">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
}
else {
  echo '<td></input><input type="button" value="Yes" onclick="deleteday()">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()"></td>';
}
echo '</tr>';
echo '</table>';
echo '</div>';

?>

<script type="text/javascript">

function showall() {
  $.facebox.close();
  ShowAction(4);
}
function deleteday() {
  $.post("page-includes/edits/delete-day-doit.php", {
    base: <?php echo $base?>,
    date: "<?php echo $date?>"
  },
  function(data,status){

    $.facebox.close();
    sda();
	  alert ('The day has been Deleted and Locked');

  })
}

function cancel() {
  $.facebox.close();
}

</script>