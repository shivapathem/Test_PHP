<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';

$intDepartmentID = $_REQUEST['id'];

$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);

$arrMappings = GetSortCodeMappingByTeam($intDepartmentID);
//print_r($arrMappings);
echo '<div class="tableheadersmall medtextboldcentre" style="width:510px; position:relative">';
echo '<br>Sorccode Mapping for '.$strDepartmentName.'<br>Double-Click to edit<br><br>';
echo '<div class="DutyCellBottomRight"><img border="0" src="images/add.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:NewEditSortCode(0, '.$intDepartmentID.')";></div>';
echo '</div>';

echo '<table class="tablesmalltidy" id="sortcodemapping">';   
echo '<tr height="40px">';  
echo '<th width="200px">';  
echo 'Sort Code';
echo '</th>';
echo '<th width="300px">';  
echo 'Mapped Description'; 
echo '</th>'; 
echo '</tr>';
if (isset($arrMappings)) {
  foreach ($arrMappings as $intID => $arrMapping ) {
    echo '<tr height="30px" class="handcursor" onclick="javascript:NewEditSortCode('.$intID.', '.$intDepartmentID.')";>';
    echo '<td>';
    echo $arrMapping['Abbreviated'];
    echo '</td>';
    echo '<td>';
    echo $arrMapping['Description'];
    echo '</td>';
    echo '</tr>';
  }
}
echo '</table>';


?>

<script type="text/javascript">
$(document).ready(function(){

  var table = $("#sortcodemapping").DataTable({
    paging: false,
    scrollY: 400,
    info:     false,
    stateSave: true,
    deferRender: true,
    scrollCollapse: true,
    "initComplete": function( settings, json ) {
        ResizeDeptsGrid();
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]);
  table.$('#<?php echo $intDefaultDepartment?>').addClass('selected');    
  $('#departmentslist tbody').on( 'click', 'tr', function () {
    if ( $(this).hasClass('selected') ) {
      $(this).removeClass('selected');
    }
    else {
      table.$('tr.selected').removeClass('selected');
      $(this).addClass('selected');
    }
  }); 
  ResizeDeptsGrid(); 
});


