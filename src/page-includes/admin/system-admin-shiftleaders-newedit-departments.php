<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/DB_Functions.php';
$intID = $_REQUEST['id'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$db = OpenDatabase();
if ($intID == 0) {
  echo 'You are entering a New Shiftleader item.<br>You need to create this entry, save it and then edit it to add Scheduling Team';
}

else {
  $teams = getUserAllTeamsLists();
 $allteam =json_decode($teams,true);
  // Get the departments it's alread available to
  
  
  $strQuery = "SELECT       ID, schedulingTeamId
             FROM           shiftleadertypes_appliesto
             WHERE          (TypeID = $intID)";

  $rsShiftLeaderTypes = sqlsrv_query($db, $strQuery);
  while($row = sqlsrv_fetch_array($rsShiftLeaderTypes)){
    $arrAlreadyIn[$row['schedulingTeamId']] = 1;
  }
  
  echo '<table class="tablesmall" width="100%">';
  echo '<tr>';
  echo '<th colspan="2" height="20px">';
  echo 'Additional Scheduling team that can view.';
  echo '</th></tr>';

  echo '<tr>';
  echo '<th width="50%">';
  echo 'Available';
  echo '</th>';
  echo '<th width="50%">';
  echo 'Visible In';
  echo '</th>';
  echo '</tr>';
  
  echo '<tr>';
  echo '<td style="vertical-align: top;">';
  
  echo '<table class="tablesmalltidy" width="100%">';
  foreach ($allteam as  $team) {
    if (!isset($arrAlreadyIn[$team['TeamID']])) {
      echo '<tr>';
      echo '<td class="handcursor" onclick=\'javascript:AddRemoveDepartment('.$team['TeamID'].',1)\';>';
      echo $team['TeamName'].'<br>';
      echo '</td></tr>';  
    }    
  }
  echo '</table></td>';

  echo '<td style="vertical-align: top;">';
  echo '<table class="tablesmalltidy" width="100%">';
  foreach ($allteam as  $team) {
    if (isset($arrAlreadyIn[$team['TeamID']])) {
      echo '<tr>';
      echo '<td class="handcursor" onclick=\'javascript:AddRemoveDepartment('.$team['TeamID'].',0)\';>';
      echo $team['TeamName'].'<br>';
      echo '</td>';
    echo '</tr>';  
    }    
  }
  echo '</table>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
}



?>
<script type="text/javascript">
function AddRemoveDepartment(schedulingTeamId, action)  {
  $.post("page-includes/admin/system-admin-shiftleaders-addremove-department.php", {
    id: <?php echo $intID?>,
          schedulingTeamId: schedulingTeamId,
    action: action
  },
  function(data,status){
    LoadDepartments (<?php echo $intID?>);
  }
  )
}