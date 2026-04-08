<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../users/process/classUserSetup.php';

$strUser = GetUserLogon();
$setupObj = new classUserSetup();
$intID = $_REQUEST['id'];
$pdo = OpenDBLinkA7();

$loggedUsedInfo = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$isDivisionalAdmin = $loggedUsedInfo['DivisionalAdmin'];
$isSysAdmin = $_SESSION['user']['SysAdmin']==''? 0 : 1;
$isSchedulingTeamAdmin = 0;
if (isset($loggedUsedInfo['isSchedulingTeamAdmin'])){
   $isSchedulingTeamAdmin = $loggedUsedInfo['isSchedulingTeamAdmin'];
}
$arrAllocLeaveTypes = GetLeaveAllocateTypes();
 
$intAction = $_REQUEST['action']; 

$callingPagename = $_REQUEST['callerPage'] ?? 'weekly';

  $leavedetails = getLeaveApplicationDetails($intID);
  
  $strFullName = $leavedetails['FullName'];
  $strLogin = $leavedetails['Login']; 
  $UserID = $leavedetails['UserID']; 

  $schedulingPersonId = GetScheduledPersonIdbyUserId($UserID);
  
  $strDate = date("Y-m-d",strtotime($leavedetails['dDate']));

  if ($intAction == 1) {
    $strNewDate = date('Y-m-d', strtotime("+1 day", strtotime($strDate)));
  }
  else {
    $strNewDate = date('Y-m-d', strtotime("-1 day", strtotime($strDate)));
  }
  $intGroupID = $leavedetails['GroupID'];

  $sicknessFlag = GetAllocationSickness($strNewDate,$schedulingPersonId);

  $overtimeFlag = GetAllocationOvertime($strNewDate,$schedulingPersonId);

  $leaveexists = GetLeaveApplicationbyDate($strNewDate,$schedulingPersonId);

  $chargingFlag = GetAllocationCharging($strNewDate,$schedulingPersonId);
  
  $query = "SELECT    ID, description
            FROM       dbo.leave_types (nolock)
            WHERE     (GroupID = $intGroupID)";

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $rsTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  foreach($rsTypes as $row){
    $arrTypes[$row['ID']] = $row['description'];
  }

  $closedLeaveYear=0;
  $currentDate = date("Y-m-d");
  $currentYear = date("Y");
  
    if($currentDate <= $currentYear."-06-30"){
      
      $newYear = $currentYear - 1; 
    }else{
      $newYear = $currentYear;
    }
   
    if($isDivisionalAdmin == 1 || $isSysAdmin == 1 || $isSchedulingTeamAdmin==1){
      $closedLeaveYear=0;
    }else{
      if($strNewDate < $newYear."-04-01"){
        $closedLeaveYear=1; 
      }
    }
    
  $intLeaveYear = date("Y",strtotime($strNewDate));
  $strYearStars = $intLeaveYear.'-04-01';
  $strYearEnds = ($intLeaveYear + 1).'-03-31';
  $arrLeave = GetLeaveForUser ($strLogin, $strYearStars, $strYearEnds,$schedulingPersonId);

$intRemainingRequests = ceil(($arrLeave['UserLeave']['Allocated'] - $arrLeave['UserLeave']['TotalHours']) / $arrLeave['UserLeave']['HoursPerDay']) +  $arrLeave['UserLeave']['ExtraLeaveClicks'];

  echo '<form id="adminaddleaveform">';
  echo '<table class="tablesmallborder" width="600px">';
  echo '<tr>';
  echo '<th colspan="2">';
  echo '<br>You are adding a new leave application for '.$strFullName.'<br>';
  echo 'The date of this new request is '.date("D, jS M Y", strtotime($strNewDate)).'<br><br>';
  echo '</th>';
  echo '</tr>';
  echo '<tr height="40px">';
  if (count($arrTypes) == 1) {
    echo '<td colspan="2">';
    $intNewType = key($arrTypes);
    echo '<input type="hidden" name="leavetype" value="'.$intNewType.'">';
    echo 'There is one type of leave defined ('.$arrTypes[$intNewType].') and this will be used.';   
    echo '</td>';  
  }
  else {

    echo '<td valign="top">Type</td>';
    echo '<td>';
    echo '<select size="1" name="leavetype">';
    foreach ($arrTypes as $inTypeID => $strType) {
      echo '<option value="'.$inTypeID.'">'.$strType.'</option>';
    }
    echo '</select>';
    echo '</td>';
    
  }
  echo '</tr>';

  echo '<tr>';

  echo '<td>&nbsp;</td>';
  echo '<td><input type="submit" value="Add" name="Update">&nbsp;&nbsp;<input type="button" id="cancel" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="user" value="'.$strLogin.'">';
  echo '<input type="hidden" name="date" value="'.$strNewDate.'">';
  echo '<input type="hidden" name="UserID" value="'.$UserID.'">';
  echo '<input type="hidden" name="sickness" value="'.$sicknessFlag.'">';
  echo '<input type="hidden" name="overtime" value="'.$overtimeFlag.'">';
  echo '<input type="hidden" name="leaveexists" value="'.$leaveexists.'">';
  echo '<input type="hidden" name="userFullName" value="'.$strFullName.'">';
  echo '<input type="hidden" name="charging" value="'.$chargingFlag.'">';
  echo '<input type="hidden" name="strDate" value="'.$strDate.'">';
  echo '<input type="hidden" name="closedleaveyr" value="'.$closedLeaveYear.'">';
  echo '<input type="hidden" name="remainingRequest" id="remainingRequest" value="'.$intRemainingRequests.'">';
  echo '<input type="hidden" name="callerPage" id="callerPage" value="'.$callingPagename.'">';
  echo '</form> ';  
?>  

<script type="text/javascript">
$('document').ready(function(){

  $('#cancel').click(function(e) {
    $.post("page-includes/leave/leave-approve-popup.php", {
          id: <?php echo $intID ?>,
          week :<?php echo $_REQUEST['week']; ?>,
          callpage:$('#callerPage').val()
        },
        function(data,status){
	        $.facebox(data);
        })
  });

  $('#adminaddleaveform').validate({
    submitHandler: function(form) {
    
      var sicknessflag = form.elements["sickness"].value;
      var overtimeflag = form.elements["overtime"].value;
      var userFullName = form.elements["userFullName"].value;
      var leaveexistsflag = form.elements["leaveexists"].value;
      var chargingflag = form.elements["charging"].value;
      var strDate = form.elements["strDate"].value;
      var closedleaveyrflag = form.elements["closedleaveyr"].value;
      var callerPage = $('#callerPage').val();
     
    if (closedleaveyrflag == 1) {
       customAlertByModel("Leave year is closed");
    } else if (sicknessflag == 1) {
          customAlertByModel("<span style='font-size:8pt;'>Leave could not be created/modified as it would affect certain Duty Allocation settings.<br>Please fix/remove these and then try again.<br>Marked For Sickness</span>");       
          $.post("page-includes/leave/leave-approve-popup.php", {
          id: <?php echo $intID ?>,
          week :<?php echo $_REQUEST['week']; ?>,
          callpage:callerPage
          },
          function(data,status){
          $.facebox(data);
          })
 }else if(overtimeflag==1){
          customAlertByModel("<span style='font-size:8pt;'>Leave could not be created/modified as it would affect certain Duty Allocation settings.<br>Please fix/remove these and then try again.<br>Marked For Overtime</span>");
          $.post("page-includes/leave/leave-approve-popup.php", {
          id: <?php echo $intID ?>,
          week :<?php echo $_REQUEST['week']; ?>,
          callpage:callerPage
          },
          function(data,status){
          $.facebox(data);
          }) 
 }else if(leaveexistsflag==1){
          customAlertByModel(userFullName+" has been edited by other user OR is already on leave on one or more of these date(s)<br>Either Re-Enter or click on Cancel");
          $.post("page-includes/leave/leave-approve-popup.php", {
            id: <?php echo $intID ?>,
            week :<?php echo $_REQUEST['week']; ?>,
          callpage:callerPage
          },
          function(data,status){
            $.facebox(data);
          })  
} else if(chargingflag==1){
          customAlertByModel("<span style='font-size:8pt;'>Leave could not be created/modified as it would affect certain Duty Allocation settings.<br>Please fix/remove these and then try again.<br>Marked For Charging </span>");
          $.post("page-includes/leave/leave-approve-popup.php", {
            id: <?php echo $intID ?>,
            week :<?php echo $_REQUEST['week']; ?>,
          callpage:callerPage
          },
          function(data,status){
            $.facebox(data);
          })
}else{
      var remainingreq = form.elements["remainingRequest"].value;
      

if (remainingreq <= 0) {
  if(confirm("The Remaining clicks available for this user are  "+remainingreq+". Do you want to authorise negative leave count and continue?")) {
  
      $.ajax({type:'POST', url: 'page-includes/leave/leave-apply.php', data:$('#adminaddleaveform').serialize(), success: function(data) {
        
      if(callerPage==1) {
          ShowLeaveWeeklyAdminByDate(strDate, <?php echo $intGroupID ?>)
      }else if(callerPage==2) {
          AdminShowLeaveWeekly(<?php echo $_REQUEST['week']; ?>);
          }
      else {
          ShowYealyLeaves();
          }
        $.post("page-includes/leave/leave-approve-popup.php", {
          id: <?php echo $intID ?>,
          week :<?php echo $_REQUEST['week']; ?>,
          callpage:callerPage
        },
        function(data,status){
	        $.facebox(data);
        })
      
      }});

    } else{ 

      $.facebox.close();
    }
  }else{   

     $.ajax({type:'POST', url: 'page-includes/leave/leave-apply.php', data:$('#adminaddleaveform').serialize(), success: function(data) {
      
        if(callerPage==1) {
          ShowLeaveWeeklyAdminByDate(strDate,<?php echo $intGroupID ?>)
      }else if(callerPage==2) {
          AdminShowLeaveWeekly(<?php echo $_REQUEST['week']; ?>);
          }
      else {
          ShowYealyLeaves();
          }
        $.post("page-includes/leave/leave-approve-popup.php", {
          id: <?php echo $intID ?>,
          week :<?php echo $_REQUEST['week']; ?>,
          callpage:callerPage
        },
        function(data,status){
	        $.facebox(data);
        })
      
      }});

  } 
}
    }
  })  
});
</script>