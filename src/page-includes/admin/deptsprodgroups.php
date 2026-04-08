<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../users/process/classUserSetup.php';

$intTeamID = $_POST['id'] ;
$arrGroups = GetProdViewGroupsTeam($intTeamID);
$setupObj = new classUserSetup();
$arrStaffTeams = [];
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
if (($intSysAdmin == 1) || !empty($userDivisionsList) || ($arrUsersTeamdata["isSchedulingTeamAdmin"] == 1) || ($arrUsersTeamdata["isScheduler"] == 1)) {
if (isset($arrUsersTeamdata["Teams"])) {
  $arrStaffTeams = $arrUsersTeamdata["Teams"];
}
echo '<div id="dialog-group-delete" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you wish to delete this Group?</span></p>
</div>';

echo '<div id="adminuserstabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
        <ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
                
        <li role="tab" tabindex="0" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="scheduling" aria-labelledby="ui-id-1" aria-selected="true" aria-expanded="true" onclick="javascript:showSchedulingTeamUI()";><a href="#scheduling" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-1">Scheduling Team</a></li>
        
        <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default  ui-state-hover  ui-state-focus ui-state-active" aria-controls="prodgroups" aria-labelledby="ui-id-4" aria-selected="false" aria-expanded="false" onclick="javascript:ShowProductionViewGroupFilters(0)";><a href="#prodviewgroups" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-2">Production View Groups</a></li>

        <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="christmas" aria-labelledby="ui-id-1" aria-selected="false" aria-expanded="false" onclick="javascript:showTeamXmasPoint()";><a href="#christmas" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-3">Christmas Points</a></li>
        
        <li role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-state-hover  ui-state-focus" aria-controls="extra" aria-labelledby="ui-id-3" aria-selected="false" aria-expanded="false" onclick="javascript:showSchedulingTeamExtraXmasPointUI()";><a href="#extra" role="presentation" tabindex="-1" class="ui-tabs-anchor" id="ui-id-4">Extra Christmas Points</a></li>

        </ul></div>';

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>On the Production View you can group by the name of the duty.<br><br>';
echo '<div class="DutyCellBottomLeft" style="width: 30%;">';
echo '<table>';
echo '<tr>';
echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
echo '<td>';
echo '<a href="javascript:void(0);" onclick="javascript:AddEditGroup(0, '.$intTeamID.')";><img border="0" src="images/button_add.png" width="30px" height="30px"></img></a>';
echo '</td>';
echo '<td align="right">&nbsp;&nbsp;Scheduling Team&nbsp;</td>';
echo '<td>';
echo '<select name="schedulingTeam" onchange="getTeamProductionFiltersList(this.value)">';
echo '<option value="">Select Scheduling Team</option>';
if(!empty($arrStaffTeams)){
  foreach($arrStaffTeams as $teamIdKey => $teamIdVal){
    if($teamIdVal['SchedulingTeamAdmin'] == 1 ){
      if($intTeamID == $teamIdKey){
        echo '<option value="'.$teamIdKey.'" selected="selected">'.$teamIdVal['schedulingTeamName'].'</option>';
      } else {
        echo '<option value="'.$teamIdKey.'">'.$teamIdVal['schedulingTeamName'].'</option>';
      }
    }
  }
}
echo '</select>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '</div>';

  echo '<table class="tablesmalltidy" id="simplefiltertable">';
  echo '<thead>';
  echo '<tr height="30px">';
  echo '<th width="300px" class="row-pad-left-10">Description</th>';
  echo '<th width="300px" class="row-pad-left-10">Filter</th>';
  echo '<th width="50px" class="row-pad-left-10">Order</th>';
  echo '<th width="100px" class="row-pad-left-10">Delete</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  
if (isset($arrGroups)) {   
  $intCounter = 0;
  $intLast = count($arrGroups) - 1;
  
  foreach ($arrGroups as $intFilterID => $arrFilter) {
    echo '<tr ondblclick="javascript:AddEditGroup('.$intFilterID.', '.$intTeamID.');" class="handcursor">';
    echo '<td class="row-pad-left-10">';
    echo $arrFilter['Description'];
    echo '</td><td class="row-pad-left-10">';
    echo $arrFilter['Filter'];
    echo '</td><td align="center" class="row-pad-left-10">';
    if ($intCounter == 0) {
      echo '<img border="0" src="../images/pixel.png" width="12px" height="12px">';    
    }
    else {
      echo '<img border="0" src="../images/hourpointer-up.png" width="12px" height="12px" onclick="javascript:MoveGroupPosition('.$intFilterID.', 0, '.$intTeamID.');">';   
    } 
    if ($intCounter == $intLast) {
      echo '<br><img border="0" src="../images/pixel.png" width="12px" height="12px">';       
    }
    else {
      echo '<br><img border="0" src="../images/hourpointer.png" width="12px" height="12px" onclick="javascript:MoveGroupPosition('.$intFilterID.', 1, '.$intTeamID.');">';    
    }
    
    echo '</td>';

    echo '<td class="row-pad-left-10">';
    echo '<img border="0" src="../images/delete.png" width="12px" height="12px" onclick="javascript:DeleteGroup('.$intFilterID.', '.$intTeamID.');">';
    echo '</td>';   
    echo '</tr>';
    $intCounter++;
  }
}   
echo '</tbody>'; 
echo '</table>';  
} else {
  echo 'Access Denied'; die;
} 
?>
