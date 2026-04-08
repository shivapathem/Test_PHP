<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';

$now = new DateTime("now", timezone: new DateTimeZone("Europe/London"));
$strStaffLogin = $_REQUEST['login'];  
$intGroupID = $_REQUEST['groupid']; 
$rsStaffDetail=  GetStaffLeaveGroupDetail($strStaffLogin, $intGroupID);  
if (isset($_REQUEST['Update'])) {
  $intEFT = $_REQUEST['eft'];
  $intEFT1 = $_REQUEST['eft1'];
  $intEFTSummer = $_REQUEST['eftsummer'];
  $strEFTNotes = $_REQUEST['eftnotes'];
  $intOldEFT  = $rsStaffDetail['EFT'];
  $intOldEFT1  = $rsStaffDetail['EFT1'];
  $intOldEFTSummer  = $rsStaffDetail['EFTSummer'];  
  $strOldEFTNotes = $_REQUEST['EFTNotes'] ?? '';
  $strHistory = 'Record Updated by '.$_SESSION['user']['FullName'].' on '.$now->format("d/m/Y").' at '.$now->format("H:i").'<br>';
  $strHistory.= 'The EFT for General Requests was changed from '.$intOldEFT.'% to '.$intEFT.'%<br>';
  $strHistory.= 'The EFT for Requests counted seperately was changed from '.$intOldEFT1.'% to '.$intEFT1.'%<hr>';  
  $strHistory.= 'The EFT for Summer Leave was changed from '.$intOldEFTSummer.'% to '.$intEFTSummer.'%<hr>';
  if ($strEFTNotes != $strOldEFTNotes) {
    $strHistory.= 'The EFT Notes were changed<hr>'; 
  }
  $modulename = 'Staff&Percentages'; 
  $currentuserid =  isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    UpdateStaffLeaveEFT($intGroupID,$intEFT,$intEFT1,$intEFTSummer,$strStaffLogin,$strEFTNotes,$strHistory,$modulename,$currentuserid) ;
}
else {
  echo '<form id="leaveeft">';
  echo '<table class="tablesmalltidy" width="500px">';
  echo '<tr>';
  echo '<th colspan="2">';
  echo '<br>Edit Leave/Request Percentages for '.$rsStaffDetail['FullName'].'.<br>Leave Group '.$rsStaffDetail['Description'].'.<br><br>';
  echo '</th>';
  echo '</tr>';
  echo '</table>';  
  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>'; 
  echo '<table class="tablesmalltidy" width="500px">';  
  echo '<tr>';
  echo '<td>';
  echo 'Grouped Requests %';
  echo '</td>';
  echo '<td>';
  echo '<input type="text" name="eft" size="20" value="'.$rsStaffDetail['EFT'].'">';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>';
  echo 'Seperately Counted Requests %';
  echo '</td>';
  echo '<td>';
  echo '<input type="text" name="eft1" size="20" value="'.$rsStaffDetail['EFT1'].'">';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>';
  echo 'Summer Leave %';
  echo '</td>';
  echo '<td>';
  echo '<input type="text" name="eftsummer" size="20" value="'.$rsStaffDetail['EFTSummer'].'">';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td>';
  echo 'EFT Notes';
  echo '</td>';
  echo '<td>';
  echo '<textarea rows="8" name="eftnotes" cols="40">'.$rsStaffDetail['EFTNotes'].'</textarea>';
  echo '</td>';
  echo '</tr>';
      
  echo '<tr>';
  echo '<td>&nbsp;</td>';
  echo '<td><input type="submit" value="Update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';

  echo '</table>';
  echo '<input type="hidden" name="login" value="'.$strStaffLogin.'">';
  echo '<input type="hidden" name="groupid" value="'.$intGroupID.'">';
  echo '</form>';
  echo '</table>';

?>
<script language="JavaScript" type="text/javascript">
$('#leaveeft').validate({
  errorLabelContainer: "#errorBox",
  rules: {
    eft: {
      required: true,
      number: true
    },
    eft1: {
      required: true,
      number: true
    },
    eftsummer: {
      required: true,
      number: true
    },    
  },
  messages: {
    eft: "Please Enter a Percentage for Grouped Requests<br>",
    eft1: "Please Enter a Percentage for Seperately Counted Requests<br>",
    eftsummer: "Please Enter a Percentage for Summer Leave<br>"
  },
  submitHandler: function(form) {
    $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/admin/leaveediteft.php', data:$('#leaveeft').serialize(), success: function(data) {
        $.facebox.close();
        ShowLeaveStaff(<?php echo $intGroupID?>);           
      }});
  } 
});
</script>    


<?php
}

