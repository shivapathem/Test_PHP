<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once '../users/process/classUserSetup.php';
$setupObj = new classUserSetup();
$arrUser = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$intWeekNumber = $_POST['weeknumber'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$strUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$strDate = datefromweek($intWeekNumber);
$pdo = OpenDBLinkA7();
if (isset($_POST['submit'])) {
  $DataInfo = explode("_", $_POST['StaffLogin']);
  $strUserLogin = $DataInfo[1];
  $strscheduledPersonID = $DataInfo[0];
  $strHistory = "Locks denied for week ".spinweek($intWeekNumber)." restriction set on ".date("d/m/Y")." at ".date("H:i")." by ".$_SESSION['user']['FullName'].".<br><br>";
  $strHistory = escapeSingleQuotes($strHistory);
  $strNow = date("Y-m-d H:i:s");
try {
  $strQuery = "INSERT INTO LockRequestsRestricted(Login, WeekNumber, History,ScheduledPersonID) VALUES (?,
  ?,?,?)";   
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1,$strUserLogin, PDO::PARAM_STR);
    $stmt->bindParam(2,$intWeekNumber, PDO::PARAM_STR);
    $stmt->bindParam(3,$strHistory, PDO::PARAM_STR);
    $stmt->bindParam(4,$strscheduledPersonID, PDO::PARAM_STR);
    $stmt->execute();
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
  $_SESSION['locksdate'] = $strDate;  
}
else {
  $arrUsersInGroup = GetStaffInTeamWithAllocation($strUserID,$intWeekNumber, -1); 
  // The page option is Leave Groups = 0 and Department = 2
  echo '<form id="adminrestrictlocks">';
  echo '<table class="tablesmallgrey" width="600px">';
  echo '<tr height="80px">';    
  echo '<th colspan="3">';       
  echo 'You are restricting the ability to apply for a Lock in Week '.spinweek($intWeekNumber).'<br>';
    if (isset($arrUsersInGroup) && !empty($arrUsersInGroup)) {
      echo 'Please choose a person from the list and click on \'Restrict\'';
      echo '</th>';  
      echo '</tr>';          
      echo '<tr>';  
      echo '<th>'; 
      echo 'Available Staff';
      echo '</th>'; 
      echo '<td colspan="2">';      
      echo '<select class="chosen-select" size="1" name="StaffLogin">';
      foreach ($arrUsersInGroup as $strUserInLogon => $UserName) {
        $DataItems = explode("_",$strUserInLogon);
        $recuserID=$DataItems[1];
         if ($strUser != $recuserID) {
              echo '<option value="'.$strUserInLogon.'">'.$UserName.'</option>';
           }
       
      }

      echo '</select>';  
      echo '</td>';  
      echo '</tr>';  
             
      echo '<tr>';  
      echo '<td>'; 
      echo '</td>'; 
      echo '<td colspan="2">';      
      echo '<input name="submit" type="submit" value="Restrict"></input><input type="button" value="Cancel" onclick="cancel()">';
      echo '</td>';  
      echo '</tr>';   
      echo '</table>';    
      echo '<input type="hidden" name="weeknumber" value="'.$intWeekNumber.'">';

      echo '</form>';
    }
    else {
      echo '</th>';  
      echo '</tr>'; 
      
      echo '<tr>';  
      echo '<td colspan="2">';
      echo 'You can only restrict Locks to a person with an Publish Allocation on this day.<br>There are no Staff with an Allocation!<br><br>';
      echo '</td>';  
      echo '</tr>';
      
      echo '<tr>';  
      echo '<td>'; 
      echo '</td>'; 
      echo '<td>';      
      echo '<input type="button" value="Cancel" onclick="cancel()">';
      echo '</td>';  
      echo '</tr>';      
            
      echo '</table>';    
    
    }
?>


<script language="JavaScript" type="text/javascript">
$(document).ready(function() {
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "400px"
  });
})

$('document').ready(function(){
    $('#adminrestrictlocks').validate({
        submitHandler: function(form) {
          $.ajax({type:'POST', url: 'page-includes/requests/lock-restriction.php', data:$('#adminrestrictlocks').serialize(), success: function(data) {
            $.facebox.close();
            ShowLocksAdmin('<?php echo $strDate ?>');
          }});
        }
    })    
});
function cancel() {
    $.facebox.close();
}  
</script>
<?php
}
?>