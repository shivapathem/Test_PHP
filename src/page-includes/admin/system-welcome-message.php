<?php
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$pdo = OpenDBLinkA7();
if (isset($_REQUEST['submit'])) {
	$strSubmitMessage = $_REQUEST['message'];
	if ($strSubmitMessage == '')
	{
		$strSubmitMessage = "NULL";  
	}
	$strQuery = "UPDATE messages SET messagecontent = ? WHERE messsageid = 1";
	$stmt = $pdo->prepare($strQuery);
	$stmt->bindParam(1, $strSubmitMessage, PDO::PARAM_INT);
	$stmt->execute();
}

$strQuery = "SELECT messagecontent FROM messages WHERE messsageid = 1";
$stmt = $pdo->prepare($strQuery);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$strMessage = isset($result['messagecontent']) ? $result['messagecontent'] : '';

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">
      <br><h2 aria-label="Welcome Message">Welcome Message.</h2>The Welcome message appears when someone visits this site<br><br>
      </div>
	  
      <br>
	  
      <form id="welcome">  
      <table class="tablesmalltidy" width="100%">
      <tr>
      <th valign="top" width="150px">
      Current Message
      </th>
      <td>'.
		nl2br($strMessage)
      .'</td>
      </tr>

      <tr>
      <th valign="top" width="150px">
      Change Message
      </th>
      <td>
      <textarea rows="4" name="message" cols="60">'.$strMessage.'</textarea>
      </td>
      </tr>

      <tr>
      <td>
      </td>
      <td>
      <input name="submit" type="submit" value="Update">
      </td>
      </tr>

      </table> 
      </form>'; 



?>

<script type="text/javascript">
$('document').ready(function(){
  $('#welcome').validate({
    submitHandler: function(form) {
      $.ajax({type:'POST', url: 'page-includes/admin/system-welcome-message.php', data:$('#welcome').serialize(), success: function(data) {
        $('#systemoptiontabs-7').html(data);
      }});
    }
  })
});


</script>