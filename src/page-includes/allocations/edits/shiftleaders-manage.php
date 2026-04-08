<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/shiftleaderfunctions.php';
include_once '../../../function-includes/genericfunctions.php';

$intTypeID = $_REQUEST['typeid'];
$teamId = $_REQUEST['teamId'];
$strCurrDate = $_REQUEST['currentDate'] ?? $_SESSION['allocattionsdate'];

$intWeek = bbcweeknumber($strCurrDate);
$intDay = getdayofweek($strCurrDate);

$arrLeaders = getleaders($intWeek, $intDay);

echo '<table class="redtable" width ="500px">';
  echo '<tr>';
  echo '<th colspan="3" height="40px">Shiftleaders for '.date("jS M Y", strtotime($strCurrDate)).'</th>';
  echo '</tr>';

if (isset($arrLeaders[$intTypeID])) {

  echo '<tr>';
  echo '<th width="200px">Name</th>';
  echo '<th width="150px">Times</th>';
  echo '<th></th>';
  echo '</tr>';

  foreach ($arrLeaders[$intTypeID] as $intID => $arrLeader) {
    if (isset($arrLeader['name'])) {
      echo '<tr>';
      echo '<td>'.$arrLeader['name'].'</td>';
      echo '<td>'.gmdate("H:i", $arrLeader['starttime']).'-'.gmdate("H:i", $arrLeader['endtime']).'</td>';  
      echo '<td align="center" class="handcursor" onclick="javascript:DeleteShiftLeaderManage(\''.$intID.'\')" ;>';
      echo '<img title="Delete this Entry" src="images/delete.gif" border="0" width="17" height="17">';  
      echo '</td>';
      echo '</tr>';
    }
  }
  echo '</table>';
}
else {
  echo '<tr>';
  echo '<td align="center"><br>There are no Shiftleaders assigned!<br><br></td>';
  echo '</tr>';
  echo '</table>';  
}
echo '<input type="button" value="Done" onclick="cancel()">';
?>
<script type="text/javascript">
function DeleteShiftLeaderManage(id) {
  $("#dialog-delete-shiftleader").dialog({
    title: "Please Confirm",
    resizable: false,
    modal: true,
      buttons: {
        "Yes": function() {
          $.post("page-includes/allocations/edits/shiftleader-delete.php", {
            id: id,
            'currentDate': '<?php echo $strCurrDate; ?>'
          })
          $.post("page-includes/allocations/edits/shiftleaders-manage.php", {
            typeid: <?php echo $intTypeID?>,
            teamId: <?php echo $teamId?>,
            'currentDate': '<?php echo $strCurrDate; ?>'
          },
          function(data,status){
           $.facebox(data);
          })
            $( this ).dialog( "close" );
        },
        "No": function() {
          $( this ).dialog( "close" );
        }
      }
  }); 
}
function cancel() {
    $.facebox.close();
    ShowDailyAllocations(<?php echo $teamId?>, '<?php echo $strCurrDate; ?>');
}
</script>