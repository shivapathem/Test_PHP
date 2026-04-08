<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';

$currid = $_REQUEST["id"];
if (isset($_REQUEST["showbuttons"])) {
  $intCanEdit = $_REQUEST["showbuttons"];
}
else {
  $intCanEdit = 1;
}
$intDepartmentID = $_REQUEST['department'];

if (isset($_REQUEST["page"])) {
  $intPageID = $_REQUEST["page"];
}
else {
  $intPageID = 0;
}
$bst = $_SESSION['bst'];
$db = OpenDatabase();

$duties = listallduties($intDepartmentID);
echo '<div class="tableheadersmall medtextboldcentre" style="width:99%; position:relative">';
echo '<br>Duties<br><br>';
if ($intCanEdit == 1) {
  echo '<div class="DutyCellBottomRight"><img border="0" src="images/add.png" width="16" height="16" Style="cursor: pointer" onclick="javascript:EditDuty(0, '.$intDepartmentID.')";></div>';
}
echo '</div>';
echo '<table id="dutiestable-'.$intDepartmentID.'-'.$intPageID.'" class="tablesmall compact stripe" width="100%">';
echo '<thead>';
echo '<tr height="40px">';
echo '<th>Duties</th>';

echo '<th>S</th>';
echo '<th>S</th>';
echo '<th>M</th>';
echo '<th>T</th>';
echo '<th>W</th>';
echo '<th>T</th>';
echo '<th>F</th>';
if ($bst == 1) {
  echo '<th>GMT</th>';
}
else {
  echo '<th>BST</th>';
}
echo '<th>Duration<br>(Inc Meals)</th>';

echo '</tr>';
echo '</thead>';
echo '<tbody>';

if (isset($duties)) { 

  foreach ($duties as $id => $dutydetails) {
    if ($intCanEdit == 1) {
      echo '<tr id="tr'.$id.'" onclick="javascript:FillDutiesPage('.$id.', '.$intDepartmentID.')"; ondblclick="javascript:EditDuty('.$id.', '.$intDepartmentID.')";>';
    }
    else {
      echo '<tr id="tr'.$id.'" onclick="javascript:FillCanDoPage('.$id.', '.$intDepartmentID.')";>';
    }
    echo '<td class="handcursor">';
    echo $dutydetails['dutyname'];
    echo '</td>';
    for ($i=0; $i <= 6; $i++) {
      if ($intCanEdit == 1) {
        echo '<td class="handcursor" align="center" onclick="javascript:ToggleDutyDay('.$id.','.$i.')";>';
      }
      else {
        echo '<td class="handcursor" align="center">';      
      }
      if (isset($dutydetails['days'][$i])) {
        echo '<img border="0" src="images/greenblob.png" width="12" height="12">';
      }
      else {
        echo '<img border="0" src="images/blueblob.png" width="12" height="12">';

      }
      echo '</td>';
    }
    echo '<td class="handcursor"  align="center" onclick="javascript:ToggleGMTBST('.$id.','.$bst.')";>';
    if ($bst == 1) {
      if ($dutydetails['GMT'] == 1) {
        echo '<img  border="0" src="../images/green_tick.png" width="12px" height="12px">'; 
      }
      else {
        echo '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';       
      }
    }
    else {
      if ($dutydetails['BST'] == 1) {
        echo '<img  border="0" src="../images/green_tick.png" width="12px" height="12px">'; 
      }
      else {
        echo '<img border="0" src="../images/red_cross.png" width="12px" height="12px">';       
      }    
    
    }
    echo '</td>';

    echo '<td>';
    echo $dutydetails['duration'];
    echo '</td>';
    
    echo '</tr>';
  }
}
echo '</tbody>';
echo '</table>';



?>
<script type="text/javascript">

$(document).ready(function(){
  var table = $("#dutiestable-<?php echo $intDepartmentID?>-<?php echo $intPageID?>").DataTable({
    paging: false,
    scrollY: 400, 
    info:     false,
    stateSave: true,
    "initComplete": function( settings, json ) {
    },
  "columnDefs": [
    { "orderable": false, "targets": 1 },
    { "orderable": false, "targets": 2 },
    { "orderable": false, "targets": 3 },
    { "orderable": false, "targets": 4 },
    { "orderable": false, "targets": 5 },
    { "orderable": false, "targets": 6 },
    { "orderable": false, "targets": 7 },
  ]    
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]); 

  $('#dutiestable-<?php echo $intDepartmentID?>-<?php echo $intPageID?> tbody').on( 'click', 'tr', function () {
    if ( $(this).hasClass('selected') ) {
      $(this).removeClass('selected');
    }
    else {
      table.$('tr.selected').removeClass('selected');
      $(this).addClass('selected');
    }
  });
  
  
  
  if ( $.cookie("dutiestable-<?php echo $intDepartmentID?>-<?php echo $intPageID?>") !== null ) {
    scrollPos = $.cookie("dutiestable-<?php echo $intDepartmentID?>-<?php echo $intPageID?>");
    $('#dutiestable-<?php echo $intDepartmentID?>-<?php echo $intPageID?>').closest('.dataTables_scrollBody').scrollTop(scrollPos);
  };  
  table.$('#tr<?php echo $currid?>').addClass('selected');
  
  if (<?php echo $currid?> != 0) {
    FillDutiesPage(<?php echo $currid?>, <?php echo $intDepartmentID?>);  
  }
})

$('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $('#dutiestable-<?php echo $intDepartmentID?>-<?php echo $intPageID?>').closest('.dataTables_scrollBody').scrollTop();
  $.cookie("dutiestable-<?php echo $intDepartmentID?>-<?php echo $intPageID?>", currpos);
});




</script>