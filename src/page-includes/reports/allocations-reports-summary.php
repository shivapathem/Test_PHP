<?php
 session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/reports-functions.php';

$intDepartmentID = $_REQUEST['departmentid'];

$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);

$arrPeriods = array( 0 => '---',
                     -12 => '1 Year Back',
                     -9  => '9 Months Back',
                     -6  => '6 Months Back',
                     -3  => '3 Months Back',
                     -1 => '1 Month Back',
                      2  => 'Previous Fortnight',
                      1  => 'Previous Week',                           
                   );

if (isset($_REQUEST['period'])) {
  $intPeriod = $_REQUEST['period'];
  if ($intPeriod < 0) {
    // Backwards  in Months
    $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
    $strEndDate = date("Y-m-d");  
  }
  else {
    // Backwards in Weeks
    $strStartDate = date("Y-m-d", strtotime(-$intPeriod." weeks"));
    $strEndDate = date("Y-m-d");  
  
  }
}  
else {
  if (isset($_REQUEST['sDate'])) {
    $intPeriod = 0; 
    $strStartDate = $_REQUEST['sDate'];
    $strEndDate = $_REQUEST['eDate'];
  }
  else {
    $intPeriod = -1;  
    $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
    $strEndDate = date("Y-m-d");
  }  
}
if (isset($_REQUEST['SortCodeFilter'])) {
  $strSortcodeFilter = $_REQUEST['SortCodeFilter'];
}
else {
  if (isset($_SESSION["reports"]["sortcode$intDepartmentID"])) {
    // Is the session set?
    $strSortcodeFilter = $_SESSION["reports"]["sortcode$intDepartmentID"];    
  }
  else {
    $strSortcodeFilter = '';  
  }
}
$_SESSION["reports"]["sortcode$intDepartmentID"] = $strSortcodeFilter;
$arrSortCodeMapping;
//print_r(  $arrSortCodeMapping);
echo '<form id="allocationsdepartmentform">';
echo '<table class="tablesmall" width="100%">';
echo '<tr>';
echo '<th colspan="9">';
echo '<br>Allocations EFT Reports for '.$strDepartmentName.' (includes Presenters)<br><br>';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px" height="30px">Start Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="sDate" id="allocations-dept-report-datepicker-start" value="'.$strStartDate.'" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="slrralternate" size="30" value="'.date("l, j F, Y", strtotime($strStartDate)).'"></td>';
echo '<th width="100px">End Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="eDate" id="allocations-dept-report-datepicker-end" value="'.$strEndDate.'" size="20" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="elrralternate" size="30" value="'.date("l, j F, Y", strtotime($strEndDate)).'"></td>';


echo '<td class="thBackGround"><input id="submit" type="submit" value="Search" name="Update"></td>';
echo '</tr>';

echo '<tr>';
echo '<th colspan="2" height="30px">';
echo 'Quick Links';
echo '</th>';
echo '<td class="thBackGround" colspan="5">';
echo '<select class="chosen-select" name="QL" onchange="quickLinkCurrentVal.value = this.value; ShowQuickLinkDepallocations(value)";>';
foreach ($arrPeriods as $thisperiod => $text) {
  if ($thisperiod == $intPeriod) {
    echo '<option selected value="'.$thisperiod.'">'.$text.'</option>';
  }
  else {
    echo '<option value="'.$thisperiod.'">'.$text.'</option>';
  }
}
echo '</select>';

echo '</td>';
echo '</tr>';


echo '</table>';
echo '<input type="hidden" name="departmentid" value="'.$intDepartmentID.'">';
echo '</form>';
echo '<br>';

$tdate = $strStartDate;
while (strtotime($tdate) < strtotime($strEndDate)) {
  $arrweeks[] = bbcweeknumber($tdate);
  $tdate = date ("Y-m-d", strtotime("+1 week", strtotime($tdate)));
}
$arrAllocationsSum = GetAllocationsForExtras ($intDepartmentID, $arrweeks[0], $arrweeks[count($arrweeks) - 1], $strSortcodeFilter);
$intAverageCore = 0;
$intAverageAdHoc = 0;
$intAverageTraining = 0;
$intWeekDiv = count($arrAllocationsSum);

if (isset($arrAllocationsSum)) {
echo '<table id="depalloctable-'.$intDepartmentID.'" class="tablesmall compact stripe">';
echo '<thead>';
echo '<tr>';
echo '<th>Week</th>';  
echo '<th>';
echo 'Scheduled Core Shifts';
echo '</th>';
echo '<th>Scheduled Core Duration</th>';
//echo '<th>Scheduled Core/Training EFT</th>';  
echo '<th>Training Shifts</th>';
echo '<th>Training Duration</th>';
echo '<th>Scheduled Core/Training EFT</th>'; 
echo '<th>Adhoc Shifts</th>';
echo '<th>Adhoc Duration</th>';
echo '<th>Adhoc EFT</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($arrweeks as $id => $strCurrWeek) {
  if (isset($arrAllocationsSum[$strCurrWeek]['Totals'])) {
    echo '<tr>';
    echo '<td class="handcursor" onclick=\'javascript:ShowAllocations('.$intDepartmentID.',"'.$strCurrWeek.'")\'>';        
    echo '<span style="display:none">'.$strCurrWeek.'</span>'.spinweek($strCurrWeek);
    echo '</td>';
    // Core Work Shifts
    echo '<td>';        
    echo $arrAllocationsSum[$strCurrWeek]['Totals']['MasterCount'];
    echo '</td>';  
    echo '<td>';        
    echo round($arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['MasterCount']);
    echo '</td>';
//    echo '<td>';   
    $intTotalCore = round(($arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] * 52) / 1456);

    $intAverageCore = $intAverageCore + $intTotalCore;
//    echo '</td>'; 

    // Training Shifts
    echo '<td>';        
    echo $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingCount'];
    echo '</td>';
  
    echo '<td>';        
    echo round($arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingCount']);
    echo '</td>';      

    echo '<td>';        
    $intTotalTraining = round(($arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration']* 52) / 1456);
    echo $intTotalTraining + $intTotalCore;
    $intAverageTraining = $intAverageTraining + $intTotalTraining;
    echo '</td>';      

    // Additional Shifts
    echo '<td>';        
    echo $arrAllocationsSum[$strCurrWeek]['Totals']['WorkingCount'] - $arrAllocationsSum[$strCurrWeek]['Totals']['MasterCount'] - $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingCount'];
    echo '</td>';
  
    echo '<td>';        
    echo round($arrAllocationsSum[$strCurrWeek]['Totals']['WorkingDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration']);
    echo '</td>';      

    echo '<td>';        
    $intTotalAdHoc = round((($arrAllocationsSum[$strCurrWeek]['Totals']['WorkingDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration']) * 52) / 1456);
    echo $intTotalAdHoc;
    $intAverageAdHoc = $intAverageAdHoc + $intTotalAdHoc;
    echo '</td>'; 
    
    echo '</tr>'; 
  }
}  
echo '</tbody>';
echo '<tfoot>';
echo '<tr>';
echo '<th>';
if ($intWeekDiv != 0) {
  echo 'Grand Total '.round(($intAverageCore / $intWeekDiv) + ($intAverageAdHoc / $intWeekDiv)); 
}
echo '</th>';  
echo '<th></th>';
echo '<th></th>';
 

echo '<th></th>';
echo '<th></th>';
echo '<th>';
// Training
if ($intWeekDiv != 0) {
  echo round(($intAverageCore + $intAverageTraining) / $intWeekDiv);
}
echo '</th>';
echo '<th></th>';
echo '<th></th>';
echo '<th>';
if ($intWeekDiv != 0) {
  echo round($intAverageAdHoc / $intWeekDiv);
}
echo '</th>';
echo '</tr>';
echo '</tfoot>';

echo '</table>';
}
else {
  echo '<table class="tablesmall" width="100%">';
  echo '<tr>';
  echo '<th>';
  echo '<br>There are no matching Allocations<br><br>';
  echo '</th>';
  echo '</tr>';
  echo '</table>';



}
?>



<script type="text/javascript">
$(document).ready(function(){
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "200px"
  });
  
<?php
if (isset($arrAllocationsSum)) {
?>  
  
  
  var table = $("#depalloctable-<?php echo $intDepartmentID?>").DataTable({
    destroy: true,
    paging: false,
    scrollY: 400,
    scrollCollapse: true,
    info:     false,
    stateSave: true,
    deferRender: true,
    order: [[ 2, "desc" ]]
  });  
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]);
  if ( $.cookie("#depalloctable-<?php echo $intDepartmentID?>") !== null ) {
    scrollPos = $.cookie("#depalloctable-<?php echo $intDepartmentID?>");
    $("#depalloctable-<?php echo $intDepartmentID?>").closest('.dataTables_scrollBody').scrollTop(scrollPos);      
  };  
  
<?php 
}
?>  
  
  
  
  $(function() {
    $( "#allocations-dept-report-datepicker-start" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#slrralternate",
      altFormat: "DD, d MM, yy"
    });
  });
  $(function() {
    $( "#allocations-dept-report-datepicker-end" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#elrralternate",
      altFormat: "DD, d MM, yy"
    });
  });
 
    
}) 
$("#depalloctable-<?php echo $intDepartmentID?>").closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $("#depalloctable-<?php echo $intDepartmentID?>").closest('.dataTables_scrollBody').scrollTop();
  $.cookie("#depalloctable-<?php echo $intDepartmentID?>", currpos);
});


 
  $('#allocationsdepartmentform').validate({
    submitHandler: function(form) {
      $.ajax({type:'POST', url: 'page-includes/reports/allocations-reports-summary.php', data:$('#allocationsdepartmentform').serialize(), success: function(data) {
        $('#AllocationsReportTabs-0').html(data);
      }});
    }
  }) 
  
function ShowQuickLinkDepallocations(period) {
  $.post("page-includes/reports/allocations-reports-summary.php", {
    period: period,
    departmentid: $('#schedulingTeamSelect').val()
  },
  function(data,status){
    $('#AllocationsReportTabs-0').html(data); 
   }
  )
}
</script>
<style>
	.thBackGround{
		background-color:#DDDDDD;
	}
</style>