<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/allocationsfunctions.php';

$intDeptID = $_REQUEST['id'];

$arrFilters = GetDutyFiltersByDepartment($intDeptID);

//print_r($arrFilters);
// Simple Filters are Type 0.......

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>You can fiter by Duty Name and / or Job \'Labels\'.<br>In addition you can sort by Duty Start Time or Duty Title.<br>Separate multiple entries using the | (pipe) character.<br><br>';
echo '<div class="DutyCellBottomLeft" onclick="javascript:AddEditFilter(0, 1, '.$intDeptID.')";>';
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
  echo '<table class="tablesmall compact stripe" id="complexfiltertable">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Description';
  echo '</th>';
  echo '<th>';
  echo 'Duties';
  echo '</th>';
  echo '<th>';
  echo 'Sort Codes';
  echo '</th>';  
  echo '<th>';
  echo 'Job Labels';
  echo '</th>';
  echo '<th>';
  echo 'Job Name Starts';
  echo '</th>';
  echo '<th>';
  echo 'Job Name Contains';
  echo '</th>';    
  echo '<th>';
  echo 'Sort Order';
  echo '</th>';
  echo '<th>';
  echo 'Match All';
  echo '</th>';  
  echo '<th>';
  echo 'Link';
  echo '</th>';
  echo '<th>';
  echo 'Delete';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  
if (isset($arrFilters[1])) {   
  foreach ($arrFilters[1] as $intFilterID => $arrFilter) {
    echo '<tr ondblclick="javascript:AddEditFilter('.$intFilterID.', 1, '.$intDeptID.');" class="handcursor">';
    echo '<td>';
    echo $arrFilter['Description'];
    echo '</td>';
    echo '<td>';
    echo $arrFilter['DutyFilter'];
    echo '</td>';
    echo '<td>';
    echo $arrFilter['SortCodeFilter'];
    echo '</td>';    
    echo '<td>';
    echo $arrFilter['JobFilter'];
    echo '</td>';
    echo '<td>';
    echo $arrFilter['JobStarts'];
    echo '</td>';
    echo '<td>';
    echo $arrFilter['JobContains'];
    echo '</td>';    
    echo '<td>';
    switch ($arrFilter['SortOrder']) {
      case 0:
        echo 'Duty Start';  
        break;    
      case 1:
        echo 'Duty Name';  
        break;
      case 2:
        echo 'Sort Code Then Name';  
        break;      
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
    echo '<a target="_blank" href="'.getenv('BASE_URL').'/auto-pages/index.php?filter='.$intFilterID.'">';
    echo '<img border="0" src="../images/url.png" width="12px" height="12px">'; 
    echo '</a>';
    echo '</td>'; 
    
    echo '<td align="center">';
    echo '<img border="0" src="../images/delete.png" width="12px" height="12px" onclick="javascript:DeleteFilter('.$intFilterID.', 1, '.$intDeptID.');">';
    echo '</td>';  

   
        
    echo '</tr>';
  }
}   
echo '</tbody>'; 
echo '</table>';   
?>


<script type="text/javascript">

$(document).ready( function () {
  var complexfiltertable = $("#complexfiltertable").DataTable({
    paging: false,
    destroy: true,
    scrollY: 400,
    info:     false,
    stateSave: true,
    deferRender: true,
  });
  yadcf.init(complexfiltertable, [
    {column_number: 0,
       filter_type: 'text'
    },
    ]);  
})

</script>