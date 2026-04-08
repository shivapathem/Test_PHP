<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

  $ddate = $_REQUEST['ddate'] ?? '';
  $StartDate = $_REQUEST['StartDate'] ?? '';
  $schedulingPersonId = $_REQUEST['schedulingPersonId'] ?? '';
  $teamId = $_REQUEST['teamId'] ?? '';
  $action = $_REQUEST['action'] ?? '';
  $page = $_REQUEST['page'] ?? 'monthly';
  $intWeekNumber = $_REQUEST['intWeekNumber'] ?? '';
  $pdo = OpenDBLinkA7();

if (!isset($_REQUEST['submit'])) {
  $query = "exec  [dbo].[usp_mod_readEDPComment] :schedulingPersonId, :ddate";
  $stmt = $pdo->prepare($query);
  $stmt->bindValue(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
  $stmt->bindValue(':ddate', $ddate, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $comments = $row['comments'];
  $edpId = $row['id'];

  echo '<form id="edpcomments">';
  echo '<table class="tablesmalltidy" width="100%">';
  echo '<tr>';
  echo '<th colspan="2" class="tableheadersmall"><br>You have volunteered on '.date("jS M Y", strtotime($ddate)).'<br><br></th>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>Comments</td>';
  echo '<td>';
  echo '<textarea rows="3" name="comments" cols="60">'.$comments.'</textarea>';
  echo '</td>';
  echo '</tr>';

  echo '<td></td>';
  echo '<td><input name="submit" type="submit" value="Submit"></input></td>';
  echo '</tr>';
  echo '</table>';

  echo '<input type="hidden" name="edpId" value="'.$edpId.'">';
  echo '<input type="hidden" name="action" value="'.$action.'">';
  echo '<input type="hidden" id="pagename" value="'.$page.'">';
  echo '</form>';
?>

<script type="text/javascript">
$('document').ready(function(){
  $('#edpcomments').validate({
    submitHandler: function(form) {
      $.ajax({type:'POST', url: 'page-includes/allocations/allocations-edp-comments.php',   data:$('#edpcomments').serialize(), success: function(data) {
        $.facebox.close();
        if ($('#pagename').val() =='weekly') {
          ShowAllocations(<?php echo $teamId?>,<?php echo $intWeekNumber?>)
        } else {
          ShowRota ('<?php echo $StartDate?>', '<?php echo $schedulingPersonId?>', '<?php echo $teamId?>');
        }
       
      }});
    }
  })
});
</script>
<?php
}
else {
  $comments = escapeSingleQuotes($_REQUEST['comments']);
  $edpId = $_REQUEST['edpId'];
  echo saveEdpOvertimeVolunteers($edpId,$ddate, $teamId, $schedulingPersonId, $comments, $action);
}