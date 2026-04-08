<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';

$strUser = GetUserLogon();
$id = $_REQUEST['id'];
$commonDbobj = new classCommonDBFunctions();
if (isset($_REQUEST['doupdate'])) {
  $leavetype = $_REQUEST['leavetype'] ?? 0;
  $leavegroup = GetLeaveGroupTypeDetails($id);
  $strOrigDesc = $leavegroup['GroupAndType'] ?? 0;
  
  $leavetypedetail = GetLeaveTypeDetails($leavetype);
  $strNewDesc = $leavetypedetail['GroupAndType'] ?? 0;

  $history =   $history = '<hr>Leave type changed from \''.$strOrigDesc.'\' to \''.$strNewDesc.'\'<br>By '.$_SESSION['user']['FullName'].' on '.getDateTimeInEuropeTimezone(1, 0).' at '.getDateTimeInEuropeTimezone(0, 1);
  $history = escapeSingleQuotes($history);
  $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $resultinsert = modLeaveTypeDetails($leavetype,$id,$history,$sessUserId);
  
}
else {

  $intWeek = $_REQUEST['week'];   
  if (isset( $_REQUEST['callpage'])) {
    $intCallPage = $_REQUEST['callpage']; 
  }
  else {
    $intCallPage = 0;   
  }
  $sessUserNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  $arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLogin($sessUserNetLogin, 0),true);

  foreach ($arrUserSettings['LeaveRequests'] as $intGroupID => $arrGroup) {
    if ($arrGroup['Admin'] >= 1) {
      $arrGroupsCanRequest[$intGroupID] = $arrGroup;
    }
  }

 
  $leaveapplicationdetails = getLeaveApplicationDetails($id);
  $strFullName = $leaveapplicationdetails['FullName'];
  $intCurrGroupID = $leaveapplicationdetails['GroupID'];
  $intTypeID = $leaveapplicationdetails['TypeID'];
  $dteDateCurrentText = date("l, jS F Y", strtotime($leaveapplicationdetails['dDate']));
  $dteDateCurrent = date("Y-m-d", strtotime($leaveapplicationdetails['dDate']));
  $intApproved =  $leaveapplicationdetails['Approved'];
  $intSent = $leaveapplicationdetails['Sent'];
  $strTypeDesc = $leaveapplicationdetails['TypeDesc'];

    
  echo '<form onsubmit="return submitForm();" id="form">';
  echo '<table class="smalltable" width="600px">';
  echo '<tr>';
  echo '<td colspan="2" class="tableheadersmall medtextboldcentre">';
  echo '<br>Change Leave Type for '.$strFullName.'<br><br>';

  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall">Date</td>';
  echo '<td class="lightcell smalltext">'.$dteDateCurrentText.'</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall" valign="top">Leave Type</td>';
  echo '<td class="lightcell smalltext">'.$strTypeDesc.'</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall" valign="top">Approved</td>';
  echo '<td class="lightcell smalltext">';
  if ($intApproved == 1) {
    $text = 'This request has been approved.<br>';
  }
  else {
    $text = 'This request has not been approved<br>Once you change its type it may change whether it is available or not.<br>';
  }
  if ($intSent==1) {
    $text.= 'This request has been Emailed to the person.<br>';
  }
  else {
    $text.= 'This request has not been Emailed to the person.<br>';
  }
  echo $text;
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td class="tableheadersmall" valign="top">Change Group</td>';
  echo '<td class="lightcell smalltext">';

  echo '<select size="1" name="leavegruop" onchange="javascript:ChangeLeaveGroup('.$id.',value)";>';
  foreach ($arrGroupsCanRequest as $intGroupID => $arrGroup) {
    if ($intGroupID == $intCurrGroupID) {
      echo '<option selected value="'.$intGroupID.'">'.$arrGroup['Description'].'</option>';
    }
    else {
      echo '<option value="'.$intGroupID.'">'.$arrGroup['Description'].'</option>';
    }
  }
  echo '</select>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<tr>';
  echo '<td class="tableheadersmall" valign="top">Change Type</td>';
  echo '<td class="lightcell smalltext">';
  echo '<div id="typescombo">';
  echo '</div>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';

  echo '<td class="lightcell smalltext">&nbsp;</td>';
  echo '<td class="lightcell smalltext"><input type="submit" value="update" name="Update">&nbsp;&nbsp;<input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$id.'">';
  echo '<input type="hidden" name="doupdate" value="0">';
  echo '</form> ';

?>
<script type="text/javascript">
ChangeLeaveGroup(<?php echo $id?>, <?php echo $intCurrGroupID?>)

function ChangeLeaveGroup(id, groupid) {
  $.post("page-includes/leave/leave-fill-teams-select.php", {
    id: id,
    leavegroup: groupid,
    typeid: <?php echo $intTypeID?>
  },
  function(data,status){
    $('#typescombo').html(data);
  });
}

function submitForm() {
$.ajax({type:'POST', url: 'page-includes/leave/leave-change-type.php', data:$('#form').serialize(), success: function(data) {
  $.facebox.close();
<?php
       if ($intCallPage == 2) {
         echo "AdminShowLeaveWeekly('".$intWeek."', ".$intCurrGroupID.")";          
       }else if($intCallPage == 8) {
			echo "GetTabContent('".$intCallPage."')";
	   } else {
         echo "ShowLeaveWeeklyAdmin('".$intWeek."', ".$intCurrGroupID.")";     
       }

?>
}});

return false;
}
</script>
<?php
}

?>
