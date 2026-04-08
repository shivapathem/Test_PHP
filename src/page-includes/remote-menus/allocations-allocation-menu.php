<?php
session_start();
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocations_functions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/helpers.php';

$intAreaID = $_SESSION['user']['AreaID'];
$intStaffID = $_REQUEST['staffid'];
$dteAllocationDate = date("Y-m-d", $_REQUEST['currdate']);

$arrDuties = GetAllocationsByDate($dteAllocationDate, $dteAllocationDate, $intAreaID, $intStaffID);
$arrDuty = $arrDuties[$intStaffID]['Dates'][$dteAllocationDate]['Duties'][0];

//print_r($arrDuty);


$intEditStatus = $arrDuty['EditStatus'];
$intRota = $arrDuty['IsRota'];
$intDutyAreaID = $arrDuty['AreaID'];
$intDutyTypeID = $arrDuty['DutyTypeID'];

$intMarkRequest = $arrDuty['MarkRequest'];
$intMarkAttention = $arrDuty['MarkAttention'];
$intMarkOvertime = $arrDuty['MarkOvertime'];
$intMarkPTExtraDay = $arrDuty['MarkPTExtraDay'];
$intMarkCompLeave = $arrDuty['MarkCompLeave'];

// Allocation ID or Master Duty ID....
if ($intRota == 0) {
  $intDutyID = $arrDuty['id'];
}
else {
  $intDutyID = 0;
}


if ($intDutyAreaID != $intAreaID) {
  $intIsEditable = 0;
}
else if ($intDutyTypeID != 1) {
  $intIsEditable = 0;
}
else if ($intRota == 1) {
  $intIsEditable = 0;
}
else if ($intEditStatus > 0) {
  $inIsEditable = 0;
}
else {
  $intIsEditable = 1;
}

//EditStatus



$arrLeave = GetLeaveByStaffIDAndDate($intStaffID, $dteAllocationDate);
if(isset($arrLeave)){
  $intLeaveRequest = 1;
}
else {
  $intLeaveRequest = 0;
}


?>



<ul class="context-menu-list context-menu-root handcursor">

<?
  if ($intEditStatus == 1) {
    
    echo '<li class="context-menu-item icon icon-delete">';
    echo '<span>Delete Duty</span>';
    echo '</li>';    
    echo '<li class="context-menu-item icon icon-edit">';
    echo '<span onclick=\'javascript:EditDuty('.$intDutyID.')\';>Edit Duty</span>';
    echo '</li>';
    
  }
  if ($intRota == 0) {  
    echo '<li class="context-menu-item icon icon-question">';
    echo '<span onclick=\'javascript:DutyOptions('.$intStaffID.',"'.$dteAllocationDate.'")\';>Duty Options</span>';
    echo '</li>';
    
  }
?>
  <li class="context-menu-separator">  
  </li>
  
  

<?
echo '<li class="context-menu-item icon icon-leave">';
switch ($intEditStatus) {
  case 0:
    if ($intLeaveRequest == 0) {
      echo '<span onclick="javascript:NewAdminLeave('.$intStaffID.',\''.$dteAllocationDate.'\',2)";>New Leave</span>';
    }
    else {
      echo '<span onclick="javascript:EditAdminLeave('.$intStaffID.',\''.$dteAllocationDate.'\',2)";>Edit Leave</span>';    
    }
    break;
  case 1:
    echo '<span onclick="javascript:EditAdminLeave('.$intStaffID.',\''.$dteAllocationDate.'\',2)";>Edit Leave</span>';
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
    echo '<span onclick="javascript:NewEditAdminSick('.$intStaffID.',\''.$dteAllocationDate.'\',0)";>New Sickness</span>';
    echo '</li>';
    break;
  case 2:
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span onclick="javascript:NewEditAdminSick('.$intStaffID.',\''.$dteAllocationDate.'\',0)";>Edit Sickness</span>';
    echo '</li>';
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span onclick="javascript:CopyAdminSick('.$intStaffID.',\''.$dteAllocationDate.'\',0)";>Extend Sickness</span>';
    echo '</li>';
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span onclick="javascript:DeleteAdminSick('.$intStaffID.',\''.$dteAllocationDate.'\',0)";>Delete Sickness</span>';
    echo '</li>';
    break;
  case 1:
    echo '<li class="context-menu-item icon icon-firstaid">';
    echo '<span class="greytext">Sick</span>';
    echo '</li>';
    break;
}
// #################################### End Sick 

?>
  <li class="context-menu-item icon icon-comment">
  <span onclick='javascript:EditDuty(<?=$intDutyID?>)';>Duty Comments</span>
  </li>
  <li class="context-menu-item icon icon-comment">
  <span onclick='javascript:EditDuty(<?=$intDutyID?>)';>Person Comments</span>
  </li>
  <li class="context-menu-separator">  
  </li>  
  <li class="context-menu-item icon icon-misc">
  <span onclick='javascript:EditDuty(<?=$intDutyID?>)';>Misc Duty</span>
  </li>
<?


  if (($intMarkOvertime == 1) ||($intMarkPTExtraDay == 1) || ($intMarkCompLeave == 1)) {
    $intMarkedAs = 1;
  }
  else {
    $intMarkedAs = 0;
  }

  if ($intIsEditable == 1) {
    echo '<li class="context-menu-item icon icon-money">';
    echo '<span onclick=\'javascript:EditAllocatedDutyCharging('.$intDutyID.','.$intStaffID.')\';>Charging</span>';
    echo '</li>';
  
    if ($intMarkRequest == 1) {
      echo '<li class="context-menu-item icon icon-exclaim">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',1, 0)\';>UnMark Request</span>';
      echo '</li>';
    }
    else {
      echo '<li class="context-menu-item icon icon-exclaim">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',1, 1)\';>Request</span>';
      echo '</li>';
    }
    if ($intMarkAttention == 1) {
      echo '<li class="context-menu-item icon icon-exclaim">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',2, 0)\';>UnMark Attention</span>';
      echo '</li>';
    }
    else {
      echo '<li class="context-menu-item icon icon-exclaim">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',2, 1)\';>Attention</span>';
      echo '</li>';
    }
    if ($intMarkOvertime == 1) {
      echo '<li class="context-menu-item icon icon-overtime">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',3, 0)\';>UnMark Overtime</span>';
      echo '</li>';
    }
    else if ($intMarkedAs == 0) {
      echo '<li class="context-menu-item icon icon-overtime">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',3, 1)\';>Overtime</span>';
      echo '</li>';
    }   
    if ($intMarkPTExtraDay == 1) {
      echo '<li class="context-menu-item icon icon-overtime">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',4, 0)\';>UnMark PartTime Extra Day</span>';
      echo '</li>';
    }
    else if ($intMarkedAs == 0) {
      echo '<li class="context-menu-item icon icon-overtime">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',4, 1)\';>PartTime Extra Day</span>';
      echo '</li>';
    }    
    if ($intMarkCompLeave == 1) {
      echo '<li class="context-menu-item icon icon-leave">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',5, 0)\';>UnMark Comp Leave</span>';
      echo '</li>';
    }
    else if ($intMarkedAs == 0) {
      echo '<li class="context-menu-item icon icon-leave">';
      echo '<span onclick=\'javascript:EditDutyMarkAs('.$intDutyID.','.$intStaffID.',5, 1)\';>Comp Leave</span>';
      echo '</li>';
    }
    
    echo '<li class="context-menu-separator">';
    echo '</li>';
  }
?>

<?  
  if ($intDutyID != 0) {  
    echo '<li class="context-menu-item icon icon-history">';
    echo '<span onclick="javascript:ShowDutyHistory('.$intStaffID.',\''.$dteAllocationDate.'\')";>History</span>';
    echo '</li>';
  }
?>

</ul>




