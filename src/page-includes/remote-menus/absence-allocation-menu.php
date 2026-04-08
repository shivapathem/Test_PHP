<?
session_start();
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocations_functions.php';
include_once '../../function-includes/leave_functions.php';

$intStaffID = $_REQUEST['staffid'];
$dteCurrent = date("Y-m-d", $_REQUEST['currdate']);



$arrAllocation = GetAllocationByStaffIDAndDate($intStaffID, $dteCurrent);
$arrLeave = GetAllLeaveRequestByStaffID($intStaffID, $dteCurrent, date("Y-m-d", strtotime("+1 day", strtotime($dteCurrent))));
if(isset($arrLeave)){
  $intLeaveRequest = 1;
}
else {
  $intLeaveRequest = 0;
}



$intEditStatus = $arrAllocation['EditStatus'];
$intDutyID = $arrAllocation['ID'];


// #####################################  0 - Can do anything!
// #####################################  1 - Is Leave
// #####################################  0 - Is Sick





echo '<ul class="context-menu-list context-menu-root handcursor">'; 
// #################################### Start Leave
echo '<li class="context-menu-item icon icon-leave">';
switch ($intEditStatus) {
  case 0:
    if ($intLeaveRequest == 0) {
      echo '<span onclick="javascript:NewAdminLeave('.$intStaffID.',\''.$dteCurrent.'\',2)";>New Leave</span>';
    }
    else {
      echo '<span onclick="javascript:EditAdminLeave('.$intStaffID.',\''.$dteCurrent.'\',2)";>Edit Leave</span>';    
    }
    break;
  case 1:
    echo '<span onclick="javascript:EditAdminLeave('.$intStaffID.',\''.$dteCurrent.'\',2)";>Edit Leave</span>';
    break;
  case 2:
    echo '<span class="greytext">Leave</span>';
    break;
}
echo '</li>';
// #################################### End Leave  

// #################################### Start Sick

switch ($intEditStatus) {
  case 0:
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span onclick="javascript:NewEditAdminSick('.$intStaffID.',\''.$dteCurrent.'\',0)";>New Sickness</span>';
    echo '</li>';
    break;
  case 2:
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span onclick="javascript:NewEditAdminSick('.$intStaffID.',\''.$dteCurrent.'\',0)";>Edit Sickness</span>';
    echo '</li>';
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span onclick="javascript:CopyAdminSick('.$intStaffID.',\''.$dteCurrent.'\',0)";>Extend Sickness</span>';
    echo '</li>';
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span onclick="javascript:DeleteAdminSick('.$intStaffID.',\''.$dteCurrent.'\',0)";>Delete Sickness</span>';
    echo '</li>';
    break;
  case 1:
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span class="greytext">Sick</span>';
    echo '</li>';
    break;
}
// #################################### End Sick 
// Edit Duty
/*  
  echo '<li class="context-menu-separator">';  
  echo '</li>'; 
  // Can only edit the duty if the status is 0 
  if ($intEditStatus == 0) {
    echo '<li class="context-menu-item icon icon-edit">';
      echo '<span onclick="javascript:EditAllocatedDuty('.$intStaffID.',\''.$dteCurrent.'\',0)";>Edit Allocation</span>';
    echo '</li>';
  }
  else {
    echo '<li class="context-menu-item icon icon-edit">';
      echo '<span class="greytext">Edit Allocation</span>';
    echo '</li>';  
  
  }
 
    
  echo '<li class="context-menu-separator">';  
  echo '</li>'; 
    
  echo '<li class="context-menu-item icon icon-exclaim">';
    echo '<span onclick="javascript:EditDuty($intStaffID)";>Mark For Attention</span>';
  echo '</li>';
  

  
  echo '<li class="context-menu-item icon icon-comment">';
    echo '<span onclick="javascript:ShowDutyComments('.$intDutyID.')";>Comments</span>';
  echo '</li>';
*/   
  
?>  
  <li class="context-menu-separator">  
  </li> 
    
  <li class="context-menu-item icon icon-history">
    <span onclick='javascript:ShowDutyHistory(<?=$intDutyID?>)';>History</span>
  </li>
</ul>



 


