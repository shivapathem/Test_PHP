<?php
session_start();

?>


<div class="tableheadersmall medtextboldcentre">
  <br>Leave Help<br><br>
</div>

<table class="redtable" cellpadding="2px" border="0" width="400px">
  <tr>
    <td width="150px">Short Notice applied for</td>
    <td class="LeaveShortNoticeApplied box50x20" width="50px">&nbsp;</td>
  </tr>
  <tr>
    <td width="150px">Leave Approved or<br>Leave OK - Not yet approved</td>
    <td class="LeaveOK box50x20" width="50px">&nbsp;</td>
  </tr>
  <tr>
    <td width="150px">Leave applied for and on waiting list</td>
    <td class="LeaveNotOK box50x20" width="50px">&nbsp;</td>
  </tr>

  <tr>
    <td width="150px">Approved not counted requests</td>
    <td class="ApprovedNot box50x20" width="50px">&nbsp;</td>
  </tr>
  <tr>
    <td width="150px">Unlikely</td>
    <td width="50px">[Name]</td>
  </tr>
  <tr>
    <td align="center" colspan="2"><input type="button" value="OK" onclick="cancel()"></td>

  </tr>  
  
  
  
</table>
