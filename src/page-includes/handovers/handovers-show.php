<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/handoverfunctions.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$intschedulingTeamId =   $_POST['schedulingTeamId'];
if (($arrUsersTeamdata["isShiftLeader"] == 1) && ($intschedulingTeamId != 0)) {
$strDate = $_POST['date'];
$handovers = GetHandoversByLoginByDate($intschedulingTeamId, $strDate);

echo '<table class="tablesmall" width="100%">';
echo '<tr>';
echo '<td class="tableheadersmall bigtextbold" colspan="2"><br>';
echo 'Shift Handovers for '.date("l jS F Y", strtotime($strDate));
echo '<br><br></td>';
echo '<td class="tableheadersmall" colspan="2">';
echo 'New Entry&nbsp;&nbsp;<img border="0" src="images/menu/page_white_add.png" width="17" height="17" style="CURSOR: hand" onclick="javascript:NewEditHandover(\''.$strDate.'\','.$intschedulingTeamId.',0)"><br><br>';
echo '</td>';
echo '</tr>';

  foreach($handovers as $row){
    echo '<tr>';
    echo '<th width="200px" class="lightcell">Created by '.$row['fullname'];
    if (!is_null($row['messageupdated'])) {
      echo '<br>Updated by '.$row['updatedby'];
    }
    echo '</th>';
    echo '<th class="lightcell">'. date("l jS F Y H:i",strtotime($row['posttime']));
    if (!is_null($row['messageupdated'])) {
      echo '<br>'.date("l jS F Y H:i",strtotime($row['messageupdated']));
    }
    echo '</th>';
    echo '<th width="50px" class="lightcell">';
    echo '<img border="0" src="images/edit.gif" width="17" height="17" style="CURSOR: hand" onclick="javascript:NewEditHandover(\''.$strDate.'\','.$intschedulingTeamId.','.$row['ID'].')">';
    echo '</th>';

    echo '<th width="50px" class="lightcell">';
    echo '<img border="0" src="images/delete.gif" width="17" height="17" style="CURSOR: hand" onclick="javascript:ConfirmDelete('.$row['ID'].','.$intschedulingTeamId.')">';
    echo '</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="medtext" width="100%" colspan="4">'.stripslashes(nl2br($row['messagebody'])).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td width="100%" colspan="3"><br></td>';
    echo '</tr>';
  }
    echo '</table>';
  } else {
    echo 'Access Denied'; die;
  }