<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';


$intDepartmentID = $_REQUEST['departmentid'];
$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];


$arrMasterDuties = ReadAllMasterDuties($intDepartmentID);
  
echo '<table class="tablegreysmallnoborder" border="1" width="100%">';
echo '<tr height="35px">';
echo '<td class="medtextbold">';
echo 'Manage Duties for '.$strDepartmentName;
echo '</td>';
echo '</tr> ';
echo '</table>';

echo '<table class="tablesmall stripe" id="MasterDutiesManage">';   
echo '<thead>'; 
echo '<tr>'; 
echo '<th>Visible</th>';
echo '<th>Duty Name</th>';
echo '<th>Start Week</th>';
echo '<th>End Week</th>';
echo '<th>Start Time</th>';
echo '<th>End Time</th>';
echo '<th>Sat</th>';
echo '<th>Sun</th>';
echo '<th>Mon</th>';
echo '<th>Tue</th>';
echo '<th>Wed</th>';
echo '<th>Thur</th>';
echo '<th>Fri</th>';
echo '</tr>';
echo '</thead>'; 

echo '<tbody>';
if (isset($arrMasterDuties)) {   
  foreach($arrMasterDuties as $intDutyID => $arrMasterDuty) {
    echo '<tr>';
    echo '<td align="center" class="handcursor"><span style="display:none">'.$arrMasterDuty['IsHidden'].'</span>';
    if ($arrMasterDuty['IsHidden'] == 0) {
      echo '<img onclick="javascript:ToggleDuty('.$intDepartmentID.', \''.$intDutyID.'\');" border="0" src="images/green_tick.png" width="12px" height="12px">';
    }
    else {
      echo '<img onclick="javascript:ToggleDuty('.$intDepartmentID.', \''.$intDutyID.'\');" border="0" src="images/red_cross.png" width="12px" height="12px">';    
    }  
     echo '</td>';        
     echo '<td>';
     echo $arrMasterDuty['DutyName'];
     echo '</td>'; 
     echo '<td style="text-align:center;">';   
     echo spinweek($arrMasterDuty['StartWeek']);
     echo '</td>';   
     echo '<td style="text-align:center;">';   
     echo spinweek($arrMasterDuty['EndWeek']);
     echo '</td>';    
     echo '<td style="text-align:center;">';
     echo $arrMasterDuty['StartTime'];
     echo '</td>';   
     echo '<td style="text-align:center;">';
     echo $arrMasterDuty['EndTime'];
     echo '</td>'; 
     for ($i = 0; $i <= 6; $i++) {
       echo '<td style="text-align:center;">';
       echo $arrMasterDuty["$i"];
       echo '</td>'; 
     }
    
     echo '</tr>';
  
  }
}      
echo '</tbody>';    
echo '</table>';      
?>

<script type="text/javascript">
 $(document).ready(function() {
  var table = $("#MasterDutiesManage").DataTable({
    paging: false,
    scrollY: 1000,
    scrollCollapse: true,
    info:     false,
    stateSave: true,
    deferRender: true,
    stateSave: true,
    
  "initComplete": function( settings, json ) {
    ResizeMasterDutiesManageTable();
    $('#loading').hide();
  },       
  }); 
  yadcf.init(table, [
	{column_number: 1,
      filter_type: 'text'
    }
  ]);
  if ( $.cookie("#MasterDutiesManage") !== null ) {
    scrollPos = $.cookie("#MasterDutiesManage");
    $("#MasterDutiesManage").closest('.dataTables_scrollBody').scrollTop(scrollPos);      
  };  
})  

$("#MasterDutiesManage").closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $("#MasterDutiesManage").closest('.dataTables_scrollBody').scrollTop();
  $.cookie("#MasterDutiesManage", currpos);
});


$(window).resize(function() {
  ResizeMasterDutiesManageTable();
})

function ResizeMasterDutiesManageTable () {
  var offset = ($("#MasterDutiesManage").length > 0) ? ($("#MasterDutiesManage").offset().top) : 0;
  var windowheight = $(window).height() - offset - 40;
  $('.dataTables_scrollBody:has(#MasterDutiesManage)').height(windowheight+'px');
  $('#MasterDutiesManage').dataTable().fnAdjustColumnSizing(); 
}


function ToggleDuty(department, dutyid) {
  $.post("page-includes/reports/master-duty-toggle.php", {
    departmentid: department,
    dutyid: dutyid
  },  
  function(data,status){
  $.post("page-includes/reports/master-duties-manage.php", {
    departmentid: department
  },  
  function(data,status){
    $('#MDReportTabs-1').html(data); 
  })
  })
}


</script>    