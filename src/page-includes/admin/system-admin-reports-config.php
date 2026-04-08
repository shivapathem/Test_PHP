<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$arrDepartments = GetReportsConfig();

echo '<div class="tableheadersmall medtextboldcentre" style="width:99%; position:relative">';
echo '<br><h2 aria-label="Department Reports Configuration">Department Reports Configuration</h2><br>';
echo '</div>';

echo '<table id="configrepstable" class="tablesmall compact stripe" width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th>';
echo 'Department';
echo '</th>';
echo '<th>';
echo 'Staff Availability Reports Start';
echo '</th>';
echo '<th>';
echo 'Default Shift Length';
echo '</th>';  
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($arrDepartments as $intDepartmentID => $arrDepartment) {
  if ($intDepartmentID !=0) {
    echo '<tr class="handcursor" ondblclick="javascript:EditDepartment('.$intDepartmentID.')";>';
    echo '<td>';
    echo $arrDepartment['DepartmentName'];
    echo '</td>';
    echo '<td>';
    if (!is_null($arrDepartment['ReportsStart'])) {
      echo spinweek($arrDepartment['ReportsStart']);
    }
  
    echo '</td>';
    echo '<td>';
    echo $arrDepartment['DefaultShiftLength'];
    echo '</td>';  
    echo '</tr>';
  }
}
echo '</tbody>';


echo '</table>';




?>

<script type="text/javascript">
$(document).ready(function(){
        var configrepstable = $("#configrepstable").DataTable({
          paging: false,
          scrollY: 400,
          info:     false,
          stateSave: true,
          deferRender: true,
          "initComplete": function( settings, json ) {
            DoResize();
          }
        });

      yadcf.init(configrepstable, [
        {column_number: 0,
          filter_type: 'text'
        },       
      ]);

})
$(window).resize(function() {
  DoResize();
})

function DoResize() {
  var windowheight = $(window ).height() - 250;  
  $('.dataTables_scrollBody').height((windowheight));  
}

function EditDepartment (id) {
  $.post("page-includes/admin/system-admin-reports-edit.php", {
    id: id
  },
  function(data,status){
	  $.facebox(data);
  })
}



</script>
