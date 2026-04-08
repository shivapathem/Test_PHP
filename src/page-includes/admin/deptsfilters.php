<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../users/process/classUserSetup.php';

$intDeptID = $_REQUEST['id'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrFilters = GetDutyFiltersByDepartment($intDeptID,2);
$arrAdminDepts = GetAdminDepts($strUser, 1);
$arrBaseCodes = GetBaseCodes();

$setupObj = new classUserSetup();
$arrStaffTeams = json_decode($setupObj->GetDefaultTeam('ALL'), true);

// echo '<pre>';
// print_r($arrFilters); exit;
// Simple Filters are Type 0.......

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>You can fiter by Duty Name and / or Sort Code<br>Separate multiple entries using the ; (semi-colon) character.<br><br>';
echo '<div class="DutyCellBottomLeft">';
echo '<table>';
echo '<tr>';
echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
echo '<td>';
echo '<a href="javascript:void(0);" onclick="javascript:AddEditFilter(0, 0, '.$intDeptID.')";><img border="0" src="images/button_add.png" width="30px" height="30px"></img></a>';
echo '</td>';
echo '<td align="right">&nbsp;&nbsp;Scheduling Team&nbsp;</td>';
echo '<td>';
echo '<select name="schedulingTeam" onchange="getTeamFiltersList(this.value)">';
echo '<option value="">Select Scheduling Team</option>';
if(!empty($arrStaffTeams) && count($arrStaffTeams) > 0){
  foreach($arrStaffTeams as $teamIdKey => $teamIdVal){
    if($intDeptID == $teamIdVal['schedulingTeamId']){
      echo '<option value="'.$teamIdVal['schedulingTeamId'].'" selected="selected">'.$teamIdVal['schedulingTeamName'].'</option>';
    } else {
      echo '<option value="'.$teamIdVal['schedulingTeamId'].'">'.$teamIdVal['schedulingTeamName'].'</option>';
    }
  }
}
echo '</select>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';

// echo '<div onclick="javascript:AddEditFilter(0, 0, '.$intDeptID.')";>';
// echo '<table>';

// echo '</table>';
// echo '</div>';

echo '</div>';
  echo '<table class="tablesmall compact stripe" id="filtertable">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Description';
  echo '</th>';
  echo '<th>';
  echo 'Duties';
  echo '</th>';
  echo '<th>';
  echo 'Base Codes<br>(Production View)';
  echo '</th>';
  echo '<th>';
  echo 'Additional Departments<br>(Production View)';
  echo '</th>';  
  echo '<th>';
  echo 'Sort Codes<br>(Not Production View)';
  echo '</th>';
  echo '<th>';
  echo 'Sort Order';
  echo '</th>';  
  echo '<th align="center">';
  echo 'Show Sort Codes';
  echo '</th>';
  echo '<th align="center">';
  echo 'Match All';
  echo '</th>';
  echo '<th align="center">';
  echo 'Public';
  echo '</th>';
  echo '<th align="center">';
  echo 'Delete';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  
if (isset($arrFilters[0]) || isset($arrFilters[1])) { 
  if(isset($arrFilters[0]) && !isset($arrFilters[1])){
    $combArray = $arrFilters[0];
  } else if(!isset($arrFilters[0]) && isset($arrFilters[1])){
    $combArray = $arrFilters[1];
  } else if(isset($arrFilters[0]) && isset($arrFilters[1])){
    $combArray = $arrFilters[0]+$arrFilters[1];
  } 
  foreach ($combArray as $intFilterID => $arrFilter) {
    echo '<tr ondblclick="javascript:AddEditFilter('.$intFilterID.','.$arrFilter['FilterType'].', '.$intDeptID.');" class="handcursor">';
    echo '<td>';
    echo $arrFilter['Description'];
    echo '</td>';
    echo '<td>';
    echo $arrFilter['DutyFilter'];
    echo '</td>';
    
    echo '<td>';
    if (isset($arrFilter['BaseCodes'])) {
      foreach ($arrFilter['BaseCodes'] as $intBCID) {
        echo $arrBaseCodes[$intBCID].'<br>';      
      }
    }
    echo '</td>';
    
    echo '<td>';    
    if (isset($arrFilter['ExtraDepartments'])) {
      foreach ($arrFilter['ExtraDepartments'] as $intDepID) {
        echo $arrAdminDepts[$intDepID]['Description'].'<br>';      
      }
    }    
    echo '</td>';    
    
    echo '<td>';
    echo $arrFilter['SortCodeFilter'];
    echo '</td>';
    echo '<td>';

    if ($arrFilter['SortOrder'] == 0) {
      echo 'Names';   
    }
    else {
      echo 'Sort Codes';  
    }
    echo '</td>';
    
    echo '<td align="center">';      
    if ($arrFilter['ShowSortCode'] == 1) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';   
    }
    else {
      echo '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';  
    }
    echo '</td>';
    echo '<td align="center">';
    if ($arrFilter['AndMatch'] == 1) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';   
    }
    else {
      echo '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';  
    }    
    echo '</td>'; 

    echo '<td align="center">';
    if ($arrFilter['isPublic'] == 1) {
      echo '<img border="0" src="../images/green_tick.png" width="12px" height="12px">';   
    }
    else {
      echo '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';  
    }    
    echo '</td>';

    echo '<td align="center">';
    echo '<img border="0" src="../images/delete.png" width="12px" height="12px" onclick="javascript:DeleteCombFilters('.$intFilterID.', '.$intDeptID.');">';
    echo '</td>';   
    echo '</tr>';
  }
}   
echo '</tbody>'; 
echo '</table>';   
?>


<script type="text/javascript">

$(document).ready( function () {
  var filtertable = $("#filtertable").DataTable({
    paging: false,
    destroy: true,
    scrollY: 400,
    info:     false,
    stateSave: true,
    deferRender: true,
  });
  yadcf.init(filtertable, [
    {column_number: 0,
       filter_type: 'text'
    },
    ]);  
})

</script>