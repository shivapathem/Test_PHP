<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';


$intDepartmentID = $_REQUEST['departmentid'];
$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

// ######################################################## Work Out the week we are to view ###############################################################
if (isset($_REQUEST['WeekNumber'])) {
  // Passed a week Number?
  $intWeekNumber = $_REQUEST['WeekNumber'];
}
else {
  if (isset($_SESSION["allocations"]["WeekNumber"])) {
    // Is the session set?
    $intWeekNumber = $_SESSION["allocations"]["WeekNumber"];    
  }
  else {
    $intWeekNumber = bbcweeknumber(date("Y-m-d"));  
  }
}

$arrMasterDuties = ReadAllMasterDuties($intDepartmentID);
  
echo '<table class="tablegreysmallnoborder" border="1" width="100%">';
//echo '<table border="1" width="100%">';
echo '<tr height="35px">';
echo '<td class="medtextbold">';
echo 'Manage Staff for '.$strDepartmentName.' for Week';
echo '</td>';
echo '</tr> ';
echo '</table>';

echo '<table class="tablesmall stripe" id="MasterDutiesStaff-'.$intDepartmentID.'">';   
echo '<thead>'; 
echo '<tr>'; 
echo '<th>Hidden</th>';   
echo '<th>Duty Desc</th>';
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
    echo '<td align="center" class="handcursor">';
    if ($arrMasterDuty['IsHidden'] == 1) {
      echo '<img onclick="javascript:ToggleDuty('.$intDepartmentID.', \''.$intDutyID.'\');" border="0" src="images/green_tick.png" width="12px" height="12px">';
    }
    else {
      echo '<img onclick="javascript:ToggleDuty('.$intDepartmentID.', \''.$intDutyID.'\');" border="0" src="images/red_cross.png" width="12px" height="12px">';    
    }  
     echo '</td>'; 
     echo '<td>';
     echo $arrMasterDuty['DutyDesc'];
     echo '</td>';   
     echo '<td>';
     echo $arrMasterDuty['DutyName'];
     echo '</td>'; 
     echo '<td>';   
     echo spinweek($arrMasterDuty['StartWeek']);
     echo '</td>';   
     echo '<td>';   
     echo spinweek($arrMasterDuty['EndWeek']);
     echo '</td>';    
     echo '<td>';
     echo $arrMasterDuty['StartTime'];
     echo '</td>';   
     echo '<td>';
     echo $arrMasterDuty['EndTime'];
     echo '</td>'; 
     for ($i = 0; $i <= 6; $i++) {
       echo '<td>';
       echo $arrMasterDuty["$i"];
       echo '</td>'; 
     }
    
     echo '</tr>';
  
  }
}      
echo '</tbody>';    
echo '</table>';    
    
    
//echo '<pre>';
//print_r($arrMasterDuties);   
?>

<script type="text/javascript">
 $(document).ready(function() {
  var table = $("#MasterDutiesStaff-<?php echo $intDepartmentID?>").DataTable({
    paging: false,
    scrollY: 1000,
    scrollCollapse: true,
    info:     false,
    stateSave: true,
    deferRender: true,
    order: [[ 2, "desc" ]],
    
  "initComplete": function( settings, json ) {
    ResizeMasterDutiesStaffTable();
    $('#loading').hide();
  },       
  }); 
  yadcf.init(table, [
    {column_number: 1,
      filter_type: 'text'
    },
    {column_number: 2,
      filter_type: 'text'
    },
      

  ]);
  if ( $.cookie("#MasterDutiesStaff-<?php echo $intDepartmentID?>") !== null ) {
    scrollPos = $.cookie("#MasterDutiesStaff-<?php echo $intDepartmentID?>");
    $("#MasterDutiesStaff-<?php echo $intDepartmentID?>").closest('.dataTables_scrollBody').scrollTop(scrollPos);      
  };  
})  

$("#MasterDutiesStaff-<?php echo $intDepartmentID?>").closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $("#MasterDutiesStaff-<?php echo $intDepartmentID?>").closest('.dataTables_scrollBody').scrollTop();
  $.cookie("#MasterDutiesStaff-<?php echo $intDepartmentID?>", currpos);
});


$(window).resize(function() {
  ResizeMasterDutiesStaffTable();
})

function ResizeMasterDutiesStaffTable () {
  var offset = ($("#MasterDutiesStaff-<?php echo $intDepartmentID?>").offset().top);
  var windowheight = $(window).height() - offset - 40;
  $('.dataTables_scrollBody:has(#MasterDutiesStaff-<?php echo $intDepartmentID?>)').height(windowheight+'px');
  $('#MasterDutiesStaff-<?php echo $intDepartmentID?>').dataTable().fnAdjustColumnSizing(); 
}


function ToggleDuty(department, dutyid) {
  $.post("page-includes/reports/master-duty-toggle.php", {
    departmentid: department,
    dutyid: dutyid
  },  
  function(data,status){
  $.post("page-includes/reports/master-duties-manage.php", {
    departmentid: <?php echo $intDepartmentID?>
  },  
  function(data,status){
    $('#MDReportTabs-1-<?php echo $intDepartmentID?>').html(data); 
  })
  })
}


</script>    