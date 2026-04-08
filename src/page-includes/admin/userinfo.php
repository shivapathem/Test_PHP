<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/userfunctions.php';

if (isset($_REQUEST['login'])) {
  $intShowClose = 1;
  $strStaffLogin = $_REQUEST['login'];  
  $intTotalCols = 11;
}
else {
  $intShowClose = 0;
  $strStaffLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  $intTotalCols = 10;
}

$arrUserSettings = GetUserSettings($strStaffLogin, 0);

echo '<table class="tablesmalltidy" width="1000px">';
echo '<tr height="70px">';
echo '<th colspan="'.$intTotalCols.'">';
echo '<br>Information for '.$arrUserSettings['Forename'].' '.$arrUserSettings['Surname'].' ('.$strStaffLogin.')<br>';
if ($intShowClose == 0) {
  echo 'You can set the default Department by clicking on the Red Cross<br>You can only set a default to a Department you are scheduled in.';
}
echo '</th>';
echo '</tr>';

echo '<tr><th colspan="'.$intTotalCols.'" height="30px">Scheduling Teams</th></tr>';

echo '<tr><th width="200px">Description</th>';

echo '<th width="100px">Default</th>';

echo '<th width="100px">Scheduled</th>';

echo '<th width="100px">Shift Leader</th>';

echo '<th width="100px">Skills Administrator</th>';

echo '<th width="100px">Scheduler</th>';

echo '<th width="100px">Scheduling Team Admin</th>';

if ($intShowClose == 1) {   
  echo '<th width="100px">';
  echo 'View Reports';
  echo '</th>';
}

echo '<th width="100px">Manager</th>';

echo '<th width="100px">Appraiser</th>';

echo '<th width="100px">Mentor</th>';

echo '</tr><tr>';

foreach ($arrUserSettings['Departments'] as $intDepID => $arrDepartment) {
  echo '<td>';
  echo $arrDepartment['DepartmentName'];
  echo '</td>';

  echo '<td align="center">';
  if (isset($arrDepartment['Default'])) {
    if ($arrDepartment['Default'] == 1) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';    
    }
    else {
      echo '<img border="0" class="handcursor" onclick="javascript:EditUserAtributes(\''.$strStaffLogin.'\', 0, 0, '.$intDepID.');" src="../images/red_cross.png" width="12px" height="12px">';    
    }
  }
  else {
    if ($intShowClose == 0) {
      echo '<img border="0" class="handcursor" onclick="javascript:EditUserAtributes(\''.$strStaffLogin.'\', 0, 0, '.$intDepID.');" src="../images/red_cross.png" width="12px" height="12px">';    
    }
    else {
      echo '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';    
    } 
  }  
  echo '</td>';
    
  echo '<td align="center">';
  if ($arrDepartment['IsScheduled'] == 1) {
    echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';    
  }
  echo '</td>';

  echo '<td align="center">';
  if (isset($arrDepartment['isShiftLeader']) && ($arrDepartment['isShiftLeader'] == 1)) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';  
  }  
  echo '</td>';
  
  echo '<td align="center">';
  if (isset($arrDepartment['SkillsAdmin']) && ($arrDepartment['SkillsAdmin'] == 1)) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';  
  }  
  echo '</td>';  

  echo '<td align="center">';  
  if (isset($arrDepartment['DepartmentScheduler']) && ($arrDepartment['DepartmentScheduler'] == 1)) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';    
  }  
  echo '</td>';
  
  echo '<td align="center">';
  if (isset($arrDepartment['Admin']) && $arrDepartment['Admin'] == 1) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">'; 
  }  
  echo '</td>';   
    
  if ($intShowClose == 1) {    
    echo '<td align="center">';
    if (isset($arrDepartment['ShowReports']) && $arrDepartment['ShowReports'] == 1) {
        echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';
    } 
    echo '</td>';  
  }

  echo '<td align="center">';  
  if (isset($arrDepartment['Manager']) && $arrDepartment['Manager'] == 1) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';
  }  
  echo '</td>';
  
  echo '<td align="center">';  
  if (isset($arrDepartment['Appraiser']) && $arrDepartment['Appraiser'] == 1) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">'; 
  }  
  echo '</td>';

  echo '<td align="center">';  
  if (isset($arrDepartment['Mentor']) && $arrDepartment['Mentor'] == 1) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';
  }  
  echo '</td>';


  

   
  echo '</tr>';  
}


echo '<tr><td colspan="'.$intTotalCols.'">&nbsp;</td></tr>';


if (isset($arrUserSettings['LeaveRequests'])) {
  echo '<tr height="30px">';
  if ($intShowClose == 1) {
    echo '<th colspan="6">';  
  }
  else {
    echo '<th colspan="5">';
  }
  echo 'Leave & Request Group(s)';
  echo '</th>';
  echo '<th colspan="5">';
  echo 'Administrator';
  echo '</th>';
  echo '</tr>';

  foreach ($arrUserSettings['LeaveRequests'] as $intGroupID => $arrLeaveRequest) {
    echo '<tr>';
    if ($intShowClose == 1) {
      echo '<td colspan="6">';  
    }
    else {
      echo '<td colspan="5">';
    }
    echo $arrLeaveRequest['Description'];
    echo '</td>';
    echo '<td colspan="5" align="center">';

    switch ($arrLeaveRequest['Admin']) {
      case 3:
        echo '<img title="You may Leave manage the configuration of this Group" border="0" src="../images/purple_tick.png" width="12px" height="12px">';          
        break;
      case 2:
        echo '<img title="You may administer the configuration of this Group" border="0" src="../images/green_tick.png" width="12px" height="12px">';    
        break;
      case 1:
        echo '<img title="You may approve Leave in this Group" border="0" src="../images/yellow_tick.png" width="12px" height="12px">';    
        break;
      default:
        echo '<img title="You may request Leave in this Group" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
        break;
    
    }   
    echo '</td>';
    echo '</tr>';
  }
}
else {
  echo '<tr height="30px">';
  echo '<td colspan="'.$intTotalCols.'">';
  echo 'No Leave & Request Groups defined';
  echo '</td>';
  echo '</tr>';
}  
echo '</table>';
if ($intShowClose == 1) {
  echo '<br><input type="button" value="Close" onclick="cancel()">';
}
?>

<script language="JavaScript" type="text/javascript">

$(document).ready( function () {
  $('img[title]').qtip({
      position: {
      viewport: $(window)
    },
    style: 'qtip-rounded qtip-shadow qtip-light'
  });
})

function EditUserAtributes(LogIn, ActionType, Action, Department) {
  $.post("page-includes/admin/deptschangestaffstatus.php", {
    login: LogIn,
    actiontype: ActionType,
    action: Action,
    department: Department
    },
    function(data,status){
        ShowUserInfoInOptions();
    }
  )
}
</script>  
  