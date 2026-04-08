<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/handoverfunctions.php';

$intID = $_REQUEST['id'];
$date = $_REQUEST['date'];
$intTeamID = $_REQUEST['teamid'];

if (isset($_REQUEST['content'])) {
    $content = $_REQUEST['content'];
}
else {
  $content = 'People:&#13;&#10;&#13;&#10;Technical:&#13;&#10;&#13;&#10;Other Information:';
}

if (isset($_REQUEST['Update'])) {            //Form Submitted
  $strFullName = $_SESSION['user']['FullName'] ? $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
  $strFullName = escapeSingleQuotes($strFullName);
  $content = addslashes($content);
  $now = date("Y-m-d H:i:s");
  $messagedate = date("Y-m-d 00:00:00", strtotime($date));  
  
  if ($intID == 0) {
    insertHandover($strFullName,$messagedate,$now,$content,$intTeamID);
  }
 else {
  updateHandover($content, $now, $strFullName, $intID);
 }
  
}
else {
  if ($intID == 0) {
     $content = 'People:&#13;&#10;&#13;&#10;Technical:&#13;&#10;&#13;&#10;Other Information:';  
  }
  else {
  // Het the content
    $content = getHandoverMessage($intID);
  }
  echo '<form id="newhandoverform">';
  echo '<table class="redtable" width="600px">';

  echo '<tr height="40px">';
  echo '<th colspan="2">Shift Handover</th>';
  echo '</tr>';
  echo '<tr>';
  
  echo '<tr>';
  echo '<th width="100" valign="top">Content</th>';
  echo '<td><textarea rows="6" name="content" cols="50">'.stripslashes($content).'</textarea></td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td width="100">&nbsp;</td>';
  echo '<td><input type="submit" value="Update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="date" value="'.$date.'">';
  echo '<input type="hidden" name="teamid" value="'.$intTeamID.'">';
  echo '<input type="hidden" name="id" value="'.$intID.'">';  
  echo '</form>';

?>
<script type="text/javascript">

$('document').ready(function(){
  $('#newhandoverform').validate({
    submitHandler: function(form) {
      $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/handovers/handovers-new-edit.php', data:$('#newhandoverform').serialize(), success: function(data) {
        ShowTeamHandovers('<?php echo $intTeamID?>','<?php echo $date?>');
      $.facebox.close();
      }});
    }
  })
});
</script>
<?php
}
?>