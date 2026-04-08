<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';


$arrAllocLeaveTypes = GetLeaveAllocateTypes();

$intID = $_REQUEST['leaveid'];
$intTeamID = $_REQUEST['teamid'];
$intLeaveYear = $_REQUEST['leaveyear'];
$strUser = $_REQUEST['struser'];

if (isset($_REQUEST['submit'])) {
  $intAmount = floor($_REQUEST['leaveamount']*4)/4;
  $intTypeID = $_REQUEST['leavetype'];
  $intOrigTypeID = $_REQUEST['origtype'];
  $strComments = $_REQUEST['comments']; 
  $strStaffDetails = GetStaffDetailsByLogon($strUser);
  $strFieldName = $arrAllocLeaveTypes[$intTypeID]['AllocName'];
  $strOrigFieldName = $intOrigTypeID > 0 ? $arrAllocLeaveTypes[$intOrigTypeID]['AllocName']: '';

  $now = new DateTime(datetime: "now", timezone: new DateTimeZone("Europe/London"));
  if ($intID == 0) {
    $strHistory = 'Leave Credit added by '.$_SESSION['user']['FullName'].' on '.$now->format("jS M Y").' at '.$now->format("H:i").'<br>';
    $strHistory.=  'The leave type was '.$arrAllocLeaveTypes[$intTypeID]['Description'].' and the amount was '.$intAmount;
    $strFieldName = $arrAllocLeaveTypes[$intTypeID]['AllocName'];
  }
  else {
    $strHistory = '<hr>Leave Credit edited by '.$_SESSION['user']['FullName'].' on '.$now->format("jS M Y").' at '.$now->format("H:i").'<br>';
    $strHistory.=  'The leave type was '.$arrAllocLeaveTypes[$intTypeID]['Description'].' and the amount was '.$intAmount;
    $strFieldName = $arrAllocLeaveTypes[$intTypeID]['AllocName'];
  }
  $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  ModLeaveAllocation($intID,$strStaffDetails['StaffNumber'],$intLeaveYear,$intAmount,$strFieldName,$strComments,$intTeamID,$strOrigFieldName,$intTypeID,$intOrigTypeID,$strHistory,$strStaffDetails['ScheduledPersonID'],$sessUserId);
}
else {
  if ($intID ==0) {
    $strFullName = GetFullNameFromLogin($strUser);
    $intLeaveType = 0;
    $intAmount = 0;
    $strComments = '';
    $strheading = 'Add';
  }
  else {
      $row = GetLeaveAllocationByID($intID);
      $strFullName = $row['FullName'];
      $strComments = $row['Comments'];
      foreach ($arrAllocLeaveTypes as $intTypeID =>$arrAllocLeaveType) {
        if (isset($row[$arrAllocLeaveType['AllocName']]))  {
          if ($row[$arrAllocLeaveType['AllocName']] != 0) {
            $intLeaveType = $intTypeID;
            $intAmount =  number_format(($row[$arrAllocLeaveType['AllocName']]),2, '.', '');
            break;
          }
        }
      } 
      $strheading = 'Edit';
  }
  echo '<form id="leavecreditedit">';
  echo '<table class="tablesmalltidy" width="600px">';
  echo '<tr>';
  echo '<th colspan="4">';
  echo '<br> '.$strheading.'  Leave Credit for '.$strFullName.' <br><br>';
  echo '</th>';
  echo '</tr>';
  echo '</table>';  
  echo '<div id="errorBox" class="lightcell">';   
  echo '</div>';
  
  echo '<table class="redtable" width="600px">';
  echo '<tr>';
  echo '<td>Leave Type</td>';
  echo '<td>';
  echo '<select class="chosen-select" name="leavetype" id="leavetype">';
  foreach ($arrAllocLeaveTypes as $intTypeID =>$arrAllocLeaveType) {
    if ($arrAllocLeaveType['isCreditable'] == 1) {
      if ($intTypeID == $intLeaveType) {
        echo '<option selected value="'.$intTypeID.'">'.$arrAllocLeaveType['Description'].'</option>';
      }
      else {
        echo '<option  value="'.$intTypeID.'">'.$arrAllocLeaveType['Description'].'</option>';
      }
    }  
  }
  echo '</select>';
  echo '</td>';
  echo '</tr>';  
  
  echo '<tr>';
  echo '<td>Amount</td>';
  echo '<td>';
  echo '<input id="amount" name="leaveamount" type="text" size="10" value="'.$intAmount.'"/>';
  echo '</td>';
  echo '</tr>'; 

  echo '<tr>';
  echo '<td>Comments</td>';
  echo '<td>';
  echo '<textarea rows="4" name="comments" cols="45" class="searchbox">'.$strComments.'</textarea>';
  echo '</td>';
  echo '</tr>'; 




  echo '<tr>';
  echo '<td></td>';
  echo '<td>';
  echo '<input id="submit" name="submit" type="submit" value="Submit">&nbsp;&nbsp;</input><input type="button" value="Cancel" onclick="cancel()">';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  
  echo '<input type="hidden" name="leaveid" value="'.$intID.'">'; 
  echo '<input type="hidden" name="leaveyear" value="'.$intLeaveYear.'">'; 
  echo '<input type="hidden" name="teamid" value="'.$intTeamID.'">';     
  echo '<input type="hidden" name="struser" value="'.$strUser.'">';  
  echo '<input type="hidden" name="origtype" value="'.$intLeaveType.'">';  
      
  echo '</form>'
?>

<script type="text/javascript">
$('document').ready(function(){
    $('#leavecreditedit').validate({
      errorLabelContainer: "#errorBox",
      rules:{
        "leaveamount":{
          required:true,
        }

      },
      messages: {
        leaveamount: "Please Enter an amount<br>"
      },

        submitHandler: function(form) {
          $('input[type="submit"]').prop('disabled', true);
          $.ajax({type:'POST', url: 'page-includes/admin/leave-credit-edit.php', data:$('#leavecreditedit').serialize(), success: function(data) {
          
          $.facebox.close();
          ShowAllocateLeaveCredit('<?php echo $strUser?>',<?php echo $intLeaveYear ?>,<?php echo $intTeamID?>);
          }});
        }
    })
  });
</script>  
  
<?php
}
?>  
    