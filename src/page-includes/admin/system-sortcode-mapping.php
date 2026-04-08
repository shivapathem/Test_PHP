<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$arrMappings = GetSortCodeMappingByTeam();
echo '<div class="tableheadersmall medtextboldcentre" style="width:100%; position:relative">';
echo '<br>Teams / Pipe / Base Code Mapping<br>Double-Click to edit<br><br>';
echo '<div class="DutyCellBottomLeft">New.... <img border="0" src="images/add.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:NewEditSortCode(0)";><br>&nbsp;</div>';
echo '</div>';

echo '<table  class="tablesmall compact stripe" width="100%" id="sortcodemapping">';   
echo '<thead>';   
echo '<th>';  
echo 'Abbreviation';
echo '</th>';
echo '<th>';  
echo 'Mapped Description'; 
echo '</th>'; 
echo '<th>';  
echo 'Department'; 
echo '</th>';
echo '<th>';  
echo 'Base Code'; 
echo '</th>';
echo '<th>';  
echo 'Role'; 
echo '</th>';
echo '<th>';  
echo 'Charge Code'; 
echo '</th>';
echo '<th>';  
echo 'Hourly Rate'; 
echo '</th>';
echo '<th>';  
echo 'Manager'; 
echo '</th>';
echo '<th>';  
echo 'Scheduler'; 
echo '</th>';
echo '<th>';  
echo 'Smart Book Admin'; 
echo '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';    
if (isset($arrMappings)) {
  foreach ($arrMappings as $intID => $arrMapping ) {
    echo '<tr class="handcursor" onclick="javascript:NewEditSortCode('.$intID.')";>';
    echo '<td>';
    echo $arrMapping['Abbreviated'];
    echo '</td>';
    echo '<td>';
    echo $arrMapping['Description'];
    echo '</td>';
    echo '<td>';
    echo $arrMapping['DepartmentName'];
    echo '</td>';
    echo '<td>';
    echo $arrMapping['BaseDescription'];
    echo '</td>';
    echo '<td>';
    echo $arrMapping['Role'];
    echo '</td>';
    echo '<td>';
    echo $arrMapping['ChargeCode'];
    echo '</td>';
    echo '<td>&pound;';
    echo $arrMapping['HourlyRate'];
    echo '</td>';    
    echo '<td>';
    echo $arrMapping['Manager'];
    echo '</td>';
    echo '<td>';
    echo $arrMapping['Scheduler'];
    echo '</td>';
    echo '<td>';
    echo $arrMapping['SmartBookUser'];
    echo '</td>';

    echo '</tr>';
  }
}
echo '</tbody>'; 
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
        ResizeSCGrid();
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
    {column_number: 1,
      filter_type: 'text'
    },
    {column_number: 2,
      filter_type: 'select'
    },    
  ]);
  ResizeSCGrid(); 
});

$(window).resize(function() {
  ResizeSCGrid();
})

function RzzesizeSCGrid () {
  var offset = ($("#sortcodemapping").offset().top);
  var windowheight = $(window).height() - offset - 40;
  $('.dataTables_scrollBody:has(#sortcodemapping)').height(windowheight+'px');
  $('#sortcodemapping').dataTable().fnAdjustColumnSizing(); 
}
function ResizeSCGrid () {
  var windowheight = $(window ).height() - 250;  
  $('.dataTables_scrollBody').height((windowheight));  
}

function NewEditSortCode (id) {
  $.post("page-includes/admin/system-admin-sortcode-edit.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
}


</script>

