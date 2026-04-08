<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';

$intDeptID = $_REQUEST['id'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrFilters = GetDutyFiltersByDepartment($intDeptID);
$arrAdminDepts = GetAdminDepts($strUser, 1);
$arrBaseCodes = GetBaseCodes();
//echo '<pre>';
//print_r($arrFilters);
// Simple Filters are Type 0.......

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>You can fiter by Duty Name and / or Sort Code<br>Separate multiple entries using the ; (semi-colon) character.<br><br>';
echo '<div class="DutyCellBottomLeft" onclick="javascript:AddEditFilter(0, 0, '.$intDeptID.')";>';
echo '<table>';
echo '<tr>';
echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
echo '<td>';
echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '</div>';
  echo '<table class="tablesmall compact stripe" id="simplefiltertable">';
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
  echo '<th>';
  echo 'Show Sort Codes';
  echo '</th>';
  echo '<th>';
  echo 'Match All';
  echo '</th>';
  echo '<th>';
  echo 'Delete';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  
if (isset($arrFilters[0])) {   
  foreach ($arrFilters[0] as $intFilterID => $arrFilter) {
    echo '<tr ondblclick="javascript:AddEditFilter('.$intFilterID.', 0, '.$intDeptID.');" class="handcursor">';
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
    
    echo '<td>';      
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
    echo '<img border="0" src="../images/delete.png" width="12px" height="12px" onclick="javascript:DeleteFilter('.$intFilterID.', 0, '.$intDeptID.');">';
    echo '</td>';   
    echo '</tr>';
  }
}   
echo '</tbody>'; 
echo '</table>';   
?>


<script type="text/javascript">

$(document).ready( function () {
  var simplefiltertable = $("#simplefiltertable").DataTable({
    paging: false,
    destroy: true,
    scrollY: 400,
    info:     false,
    stateSave: true,
    deferRender: true,
  });
  yadcf.init(simplefiltertable, [
    {column_number: 0,
       filter_type: 'text'
    },
    ]);  
})

</script>