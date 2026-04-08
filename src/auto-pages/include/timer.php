<?php
  $intFilterID = $_REQUEST['filter'];
  $intTeamID = $_REQUEST['teamId'];
?>

<table border="0" width="100%">
  <tr>
    <td width="50%" align="right" class="bigtextbold"><font color="#FFFFFF">Refresh in:&nbsp;</font></td>
    <td width="50%" id="countdown"></td>
  </tr>
</table>


<script language="JavaScript" type="text/javascript">

$(document).ready( function () {
   $('#countdown').timeTo({
    seconds: 30,
    displayDays: 0,
    displayHours: 0,
    fontSize: 18,
    callback : function(){ ShowAllocations(<?php echo $intFilterID?>,<?php echo $intTeamID?>); }
  });
})
</script>