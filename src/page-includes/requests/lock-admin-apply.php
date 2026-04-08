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
$pdo = OpenDBLinkA7();
$strDate = $_REQUEST['date'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$strUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

  $dowMap = array(
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
  );
$intWeekNumber = bbcweeknumber($strDate);
$intDay =  $dowMap[date("D", strtotime($strDate))];
 if (isset($_REQUEST['submit'])) {
  $strCurrentDate = datefromweek($intWeekNumber, $intDay);
  $DataInfo = explode("_",$_REQUEST['StaffLogin']);
  if (!empty($DataInfo[1]) || $DataInfo[1]!=null) {
    $strUserLogin = $DataInfo[1];
  } else {
    $strUserLogin = $DataInfo[0];
  } 
  $rsAllocation=[];
  $strscheduledPersonID = $DataInfo[0];
  $arrUserTeamSettings = getAssignTeamEmailByNetLoginID($strscheduledPersonID);
  // Add a new lock for this date.....
  try { 
    $strQuery = "exec [dbo].[usp_fetchAllocationInforByschedullerorAboveRole] :intWeekNumber,:intDay,:strUserID,:strscheduledPersonID"; 
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intWeekNumber',$intWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(':intDay',$intDay, PDO::PARAM_INT);
    $stmt->bindParam(':strUserID',$strUserID, PDO::PARAM_INT);
    $stmt->bindParam(':strscheduledPersonID',$strscheduledPersonID, PDO::PARAM_INT); 
    $stmt->execute();
    $rsAllocation = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  };
  $strAllocInfo = '';
  $strHeaderTimes = '';     
  if (empty($rsAllocation)) {
    $strAllocInfo = 'Unknown Duty';
    $intDutyDuration = null;
    $intDutyStart = null;
    $intDutyEnd = null;    
    $intDutyDuration = null; 
    $strDutyName = null;
  } else {  
    foreach ($rsAllocation as $row) {
      $strDutyName = $row['DutyName'];
      $strDepartmentName = $row['TeamName'];
      $scheduledPersonID = $row['ScheduledPersonID'];
      $strAllocInfo.= '<b>'.$strDepartmentName.'</b><br>';
      $strAllocInfo.= $strDutyName.'<br>';  
      if ((!is_null($row['StartTime'])) && ($row['StartTime']!=0)) {
        $intDutyDuration = $row['Duration'];
        $intDutyStart = $row['StartTime'];
        $intDutyEnd = $row['EndTime'];      
        $strHeaderTimes = ' ('.gmdate("H:i", $row['StartTime']).'-'.gmdate("H:i", $row['EndTime']).')';
        $strAllocInfo.= gmdate("H:i", $row['StartTime']).'-'.gmdate("H:i", $row['EndTime']).'<br>';
      } else {
        $intDutyStart = null;
        $intDutyEnd = null;
        if (!is_null($row['Duration'])) {
          $intDutyDuration = $row['Duration'];
          $strAllocInfo.= round(gmdate("H",$row["Duration"]), 2, PHP_ROUND_HALF_UP).' Hours<br>'; 
        }
        else {
          $intDutyDuration = null;
        }      
      }
    }
  } //else End here

  $strHistory = "Admin Lock created on ".date("d/m/Y")." at ".date("H:i")." by ".$_SESSION['user']['FullName'].".<br><br>";
  $strHistory.= $strAllocInfo.'<br>';
  $strHistory = escapeSingleQuotes($strHistory);
  $strNow = date("Y-m-d H:i:s");
  $strQuery = "INSERT INTO 
       LockRequests(Login,WeekNumber,iDay,RequestedOn,AdminRequest,History,DutyName,StartTime,EndTime,Duration,ScheduledPersonID,AllocationsInformation)
                VALUES    (:strscheduledPersonID ,:intWeekNumber,:intDay,:strNow,:AdminRequest,:History,:strDutyName,:intDutyStart,:intDutyEnd,:intDutyDuration,:scheduledPersonID,:strAllocInfo
                            )";
  try { 
      $AdminRequest=1;
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(':strscheduledPersonID',$strUserLogin, PDO::PARAM_STR);
      $stmt->bindParam(':intWeekNumber',$intWeekNumber, PDO::PARAM_STR);
      $stmt->bindParam(':intDay',$intDay, PDO::PARAM_STR);
      $stmt->bindParam(':strNow',$strNow, PDO::PARAM_STR);
      $stmt->bindParam(':AdminRequest',$AdminRequest, PDO::PARAM_STR);
      $stmt->bindParam(':History',$strHistory, PDO::PARAM_STR);
      $stmt->bindParam(':strDutyName', $strDutyName, PDO::PARAM_STR);
      $stmt->bindParam(':intDutyStart',$intDutyStart, PDO::PARAM_INT);
      $stmt->bindParam(':intDutyEnd',$intDutyEnd, PDO::PARAM_INT);
      $stmt->bindParam(':intDutyDuration',$intDutyDuration, PDO::PARAM_INT);
      $stmt->bindParam(':scheduledPersonID',$strscheduledPersonID, PDO::PARAM_STR);
      $stmt->bindParam(':strAllocInfo',$strAllocInfo, PDO::PARAM_STR);
      $stmt->execute();

      $query = "exec usp_UPDAllocationLockAndRequest @ScheduledPersonID = $strscheduledPersonID, @WeekNumber = $intWeekNumber, @iDay = $intDay, @DutyDate = NULL, @UserID = $strUserID";
      $stmt = $pdo->prepare($query);
      $stmt->execute();
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }

    //Mail Code

  $_SESSION['locksdate'] = $strDate;  
  // #### Now the email
  // Get the email addresses....
  foreach ($arrUserTeamSettings as $arrTeam) {
    if ($arrTeam['scheduledType'] == 1 && !is_null($arrTeam['Email'])) {
      $arrEmailFrom[$arrTeam['schedulingTeamId']] = $arrTeam['Email'];
    }
  }
  if (isset($arrEmailFrom)) {
    $mail = new PHPMailer\PHPMailer\PHPMailer();

    $strUserFullName = GetFullNameFromLogin($strUserLogin);
    $strEmailTo = GetEmailFromLogin($strUserLogin);
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->setFrom('allocate@bbc.co.uk', 'Allocate');

    $mail->Host = getenv('SMTP_HOST');
    $mail->Port = 25;

    $mail->Subject = getenv('EMAIL_SUFFIX').$strUserFullName.' - Lock';
    $mail->addAddress($strEmailTo);
    foreach ($arrEmailFrom as $emailfrom) {
      $arrDepFrom = explode(";", $emailfrom);
      foreach ($arrDepFrom as $strPartDepEmail) {
      echo $strPartDepEmail;
        $mail->addAddress($strPartDepEmail);
      }
    }
    $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
    $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
    $html='<style>'.(@file_get_contents(getenv('EMAIL_CSS')) ?? '').'</style>
           <body>
           <img alt="Banner" src="cid:pobanner" /><br><br>';

    $html.= '<h2>This is a Lock for '.$strUserFullName.'</h2>';
  
  
    $html.= '<h3>On '.date("l, jS F Y", strtotime($strCurrentDate)).$strHeaderTimes.'</h3>';

    $html.=$strHistory;

    $html.='<br><br><br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC '.romanNumerals(date("Y")).'<font></p>';
    $html.='</body></html>';

    $mail->msgHTML($html);

    if (!$mail->send()) {
      echo "Mailer Error: " . $mail->ErrorInfo; exit;
    } 
    else {
      echo "Message sent!";
    } 
  } 

 } else {
  echo '<form id="adminlock">';
  echo '<table class="tablesmallgrey" width="600px">';
  echo '<tr height="80px">';    
  echo '<th colspan="3">';       
  echo 'You are adding a New Lock for '.date("l, jS F Y", strtotime($strDate)).'<br>';
  $arrUsersInGroup = GetStaffInTeamWithAllocation($strUserID, $intWeekNumber, $intDay);
    if (!empty($arrUsersInGroup)) {
      echo 'Below is a list of people who can request.<br>';
      echo 'Please choose a person from the list and click on \'Add\'';
      echo '</th>';  
      echo '</tr>';          
      echo '<tr>';  
      echo '<th>'; 
      echo 'Available Staff';
      echo '</th>'; 
      echo '<td colspan="2">';      
      echo '<select class="chosen-select" size="1" name="StaffLogin">';
      foreach ($arrUsersInGroup as $strUserInLogon => $strUser) {
         echo '<option value="'.$strUserInLogon.'">'.$strUser.'</option>';    
      }
      echo '</select>';  
      echo '</td>';  
      echo '</tr>';  
             
      echo '<tr>';  
      echo '<td>'; 
      echo '</td>'; 
      echo '<td colspan="2">';      
      echo '<input name="submit" type="submit" value="Add"></input><input type="button" value="Cancel" onclick="cancel()">';
      echo '</td>';  
      echo '</tr>';   
      echo '</table>';    
      echo '<input type="hidden" name="date" value="'.$strDate.'">';

      echo '</form>';
    } //not empty check
    else {
            echo '</th>';  
            echo '</tr>'; 
            echo '<tr>';  
            echo '<td colspan="2">';
            echo 'You can only apply a Lock to a person with an Allocation on this day.<br>There are no Staff with an Allocation!<br><br>';
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
    $('#adminlock').validate({
        submitHandler: function(form) {
          $.ajax({type:'POST', url: 'page-includes/requests/lock-admin-apply.php', data:$('#adminlock').serialize(), success: function(data) {
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