<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/common/classCommonDBFunctions.php';

$commonObj = new classCommonDBFunctions();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intGroupID = $_REQUEST['leavegroup'];
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$isDivisionalAdmin = $commonObj->UserIsDivAdmin($sessUserId);

if(isset( $_SESSION['user']['SysAdmin'] ) && $_SESSION['user']['SysAdmin'] == 1){
  $intSysAdmin = 1;
}else{
  $intSysAdmin = 0;
}
if (($intSysAdmin == 1) || ($isDivisionalAdmin == 1)) {
  $intAdminLevel = 2;
}
else {
  $intAdminLevel = GetAdminLeaveRequestGroupsAdminFromLogin ($strUser, $intGroupID);
}
$arrGroupsAndTeams = GetLeaveGroupAndTeamsFromID($intGroupID);

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h2>Leave Types and Defaults for '.$arrGroupsAndTeams[$intGroupID]['Description'].'</h2>Double-Click to edit the Entry.<br><br>';
echo '<div class="DutyCellBottomLeft" onclick="javascript:EditLeaveType(0, '.$intGroupID.')">';
if ($intAdminLevel == 2) {
  echo '<table>';
  echo '<tr>';
  echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
  echo '<td>';
  echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
}  
  echo '</div>';
  echo '</div>';


  echo '<table border="0" width="800px" class="tablesmall compact stripe" id="leavetypestable">';
  echo '<thead>';
  echo '<tr>';
  echo '<td class="lightcell medtextbold">';  
  echo 'Leave Type';  
  echo '</td>';   
  echo '<td class="lightcell medtextbold">';  
  echo 'Short Notice Starts';  
  echo '</td>';
  echo '<td class="lightcell medtextbold">';  
  echo 'Starts';  
  echo '</td>';
  echo '<td class="lightcell medtextbold">';  
  echo 'Ends';  
  echo '</td>';    
  echo '<td class="lightcell medtextbold">';  
  echo 'Count Clicks';  
  echo '</td>';     
  echo '<td class="lightcell medtextbold">';  
  echo 'Saturday';  
  echo '</td>';  
  echo '<td class="lightcell medtextbold">';  
  echo 'Sunday';  
  echo '</td>'; 
  echo '<td class="lightcell medtextbold">';  
  echo 'Monday';  
  echo '</td>'; 
  echo '<td class="lightcell medtextbold">';  
  echo 'Tuesday';  
  echo '</td>'; 
  echo '<td class="lightcell medtextbold">';  
  echo 'Wednesday';  
  echo '</td>'; 
  echo '<td class="lightcell medtextbold">';  
  echo 'Thursday';  
  echo '</td>'; 
  echo '<td class="lightcell medtextbold">';  
  echo 'Friday';  
  echo '</td>';           
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';    
  if (isset($arrGroupsAndTeams[$intGroupID]['Types'])) {
  foreach ($arrGroupsAndTeams[$intGroupID]['Types'] as $TypeID => $arrTypes){
    if ($intAdminLevel == 2)  {
      echo '<tr ondblclick=\'javascript:EditLeaveType('.$TypeID.','.$intGroupID.')\'>';    
    } 
    else {
      echo '<tr>';
    }
    
    echo '<td>';   
    echo $arrTypes['Description'];
    echo '</td>';
    echo '<td align="center">';   
    echo $arrTypes['ShortNoticeLeaveStarts'];
    echo '</td>';
    echo '<td align="center">';   
    echo $arrTypes['LeaveStarts'];
    echo '</td>';
    echo '<td align="center">';   
    echo $arrTypes['LeaveEnds'];
    echo '</td>';            
    
    echo '<td align="center">'; 
    echo '<span class="customdateSort">'.$arrTypes['countclicks'].'</span>';
    if ($arrTypes['countclicks'] == 1) {   
      echo '<img border="0" src="images/green_tick.png" width="16" height="16">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="16" height="16">';    
    }  
    echo '</td>';
    echo '<td>';
    $arrDays = explode(',', $arrTypes['defaultamounts']);
     
    if (isset($arrDays[0])) {
      echo $arrDays[0];
    }
    else {
      echo '0';  
    }
    echo '</td>';  
    echo '<td>';  
    if (isset($arrDays[1])) {
      echo $arrDays[1];
    }
    else {
      echo '0';  
    }
    echo '</td>';
    echo '<td>';    
    if (isset($arrDays[2])) {
      echo $arrDays[2];
    }
    else {
      echo '0';  
    }
    echo '</td>';  
    echo '<td>';  
    if (isset($arrDays[3])) {
      echo $arrDays[3];
    }
    else {
      echo '0';  
    }
    echo '</td>';
    echo '<td>';  
    if (isset($arrDays[4])) {
      echo $arrDays[4];
    }
    else {
      echo '0';  
    }
    echo '</td>';
    echo '<td>';  
    if (isset($arrDays[5])) {
      echo $arrDays[5];
    }
    else {
      echo '0';  
    }
    echo '</td>';
    echo '<td>';  
    if (isset($arrDays[6])) {
      echo $arrDays[6];
    }
    else {
      echo '0';  
    }
    echo '</td>';      
    echo '</tr>'; 
  }
  }
echo '</tbody>';
echo '</table>'; 
?>

<script language="JavaScript" type="text/javascript">
$(document).ready(function(){
  var table = $("#leavetypestable").DataTable({
    paging: false,
    scrollY: parseInt($(window).height() - 320),
    info:     false,
    stateSave: true,
    deferRender: true,
  });
})  
  
  
function DeleteLeaveType(id) {
  customConfirm('Do you want to delete this Leave Type?',function(){
			$.post("page-includes/ajax-calls/deleteleavetype.php", {
			  id: id
			})
			$("#admintabs").tabs("option", "active", 0);  
			$("#admintabs").tabs("option", "active", 2); 
		},
		function() {
			return false;
		}
	);
}

</script>