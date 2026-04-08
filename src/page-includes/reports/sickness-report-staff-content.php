<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/reports-functions.php';

$ScheduledPersonID = $_REQUEST['ScheduledPersonID'];
$teamId = $_REQUEST['teamId'];
$arrPeriods = array( 0 => '---',
                     -12 => '1 Year Back',
                     -9  => '9 Months Back',
                     -6  => '6 Months Back',
                     -3  => '3 Months Back',
                     -1 => '1 Month Back'                           
                   );

if (!empty($_REQUEST['period'])) {
  $intPeriod = $_REQUEST['period'];
  if ($intPeriod < 0) {
    // Backwards
    $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
    $strEndDate = date("Y-m-d");  
  }
  else {
    // Forwards
    $strStartDate = date("Y-m-d");
    $strEndDate = date("Y-m-d", strtotime($intPeriod." months"));    
  
  }
}  
else {
  if (!empty($_REQUEST['sDate'])) {
    $intPeriod = 0; 
    $strStartDate = $_REQUEST['sDate'];
    $strEndDate = $_REQUEST['eDate'];
  }
  else {
    $intPeriod = -12;  
    $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
    $strEndDate = date("Y-m-d");
  }  
}
/*
if ($ScheduledPersonID == 0) {
  echo '<br><div class="tableheadersmall medtextbold" style="width:100%">';
  echo '<br>Please choose a name from the list.<br><br>';
  echo '</div>';
}
else {*/
echo '<br>';
echo '<form id="sicknessindividualform">';
echo '<table class="tablesmall" width="100%">';
echo '<tr>';
echo '<th colspan="7">';
echo '<br>Individual Sickness Record<br><br>';
echo '<a style="float: right; position: relative; top: -37px; right: 25px; text-decoration:none;" href="javascript:void(0)" onClick="createExcelForSicknessByPerson();" >Create Excel<br/><img style="position: absolute; right:20px" width="24px" height="24px" border="0" src="images/excel.png"></a>';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px">Start Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="sDate" id="sickness-report-datepicker-start" value="'.$strStartDate.'" required/></th>';
echo '<td width="250px"><input type="text" id="slrralternate" size="30" value="'.date("l, j F, Y", strtotime($strStartDate)).'"></td>';
echo '<th width="100px">End Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="eDate" id="sickness-report-datepicker-end" value="'.$strEndDate.'" size="20" required/></th>';
echo '<td width="250px"><input type="text" id="elrralternate" size="30" value="'.date("l, j F, Y", strtotime($strEndDate)).'"></td>';
echo '<td><input id="submit" type="submit" value="Search" name="Update"></td>';
echo '</tr>';

echo '<tr>';
echo '<th colspan="2">';
echo 'Quick Links';
echo '</th>';
echo '<td colspan="5">';
echo '<select class="chosen-select" name="QL" id="QL" onchange="javascript:ShowQuickLinkSickness(value)";>';
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
echo '<tr><td>';
$teamId = $_REQUEST['teamId'];
$arrStaffInDepartment = GetStaffInTeamSimple($teamId, $strStartDate, $strEndDate);
if (isset($_REQUEST['ScheduledPersonID'])) {
  $strStaffNumber = $_REQUEST['ScheduledPersonID'];
}
else {
  $strStaffNumber = '';
  $strStaffNumber = array_keys($arrStaffInDepartment)[0];
}

echo '<table class="tablesmall">';
echo '<tr>';
echo '<td>';
echo '<select class="chosen-select" name="department" id="schedulePeopleList" onchange="javascript:ShowIndividualSicknessNew('."'".$teamId."'".', value, $(\'#sickness-report-datepicker-start\').val(), $(\'#sickness-report-datepicker-end\').val(), $(\'#QL\').val())";>';
echo '<option selected value="0">----</option>';
foreach ($arrStaffInDepartment as $strSN => $strName) { 
  if ($strStaffNumber == $strSN) {
    echo '<option selected value="'.$strSN.'">'.$strName.'</option>';
  }
  else {
    echo '<option value="'.$strSN.'">'.$strName.'</option>';
  }  
}
echo '</select>';
echo '   </td>';
echo ' </tr>';
echo '</table> ';

echo '<div id="staffsicknessreport-'.$teamId.'">';
echo '</div>';
echo '</td></tr>';
echo '</table>';
echo '<input type="hidden" name="teamId" value="'.$teamId.'">';
echo '<input type="hidden" name="ScheduledPersonID" value="'.$ScheduledPersonID.'">';
echo '</form>';
echo '<br>';

$arrcollated = getSicknessReport(null, $strStartDate, $strEndDate, $ScheduledPersonID);//echo "<pre>";print_r($arrcollated);die;
$intTotalDutyHrs = array_sum(array_map(function($item){return $item['TotalHoursSick'];}, $arrcollated));
$tdate = $strStartDate;
while (strtotime($tdate) <= strtotime($strEndDate)) {
  $arrweeks[] = bbcweeknumber($tdate);
  $tdate = date ("Y-m-d", strtotime("+1 week", strtotime($tdate)));
}

$arrAllocations = getSicknessByPerson($strStartDate, $strEndDate, $ScheduledPersonID, $teamId);
echo '<br>';
$arrcollated[0]['TotalHoursSick'] = $arrcollated[0]['TotalHoursSick'] ?? 0;
$arrcollated[0]['Occurrences'] = $arrcollated[0]['Occurrences'] ?? '';
$arrcollated[0]['TotalDaysWorked'] = $arrcollated[0]['TotalDaysWorked'] ?? 0;
$arrcollated[0]['Percentage'] = $arrcollated[0]['Percentage'] ?? 0;
  if(isset($arrcollated)) {
    echo '<table id="depsicktable" class="tablesmalltidy">';
    echo '<thead>';

    echo '<tr>';
    echo '<th width="200px">Days Sick</th>';
    echo '<th width="200px">Occurrences</th>';
    echo '<th width="200px">Hours</th>';
    echo '<th width="200px">Worked Days</th>';
    echo '<th width="200px">Percentage</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
	$hrs = $arrcollated[0]['TotalHoursSick'] / 60; 
    echo '<tr>';
	echo '<td>'.count($arrAllocations).'</td>';
	echo '<td>'. $arrcollated[0]['Occurrences'] .'</td>';
	echo '<td>'. round($intTotalDutyHrs / 3600, 2) .'</td>';
	echo '<td>'. $arrcollated[0]['TotalDaysWorked'] .'</td>';	
	echo '<td>'. $arrcollated[0]['Percentage'] .'%</td>';
	echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    echo '<br>';
	$arrForExcel = array(count($arrAllocations), $arrcollated[0]['Occurrences'], round($intTotalDutyHrs / 3600, 2), $arrcollated[0]['TotalDaysWorked'], $arrcollated[0]['Percentage']);
    if (isset($arrAllocations)) {
      echo '<table class="tablesmall compact stripe" id="sickdatestable-'.$teamId.'">';
      echo '<thead>';
      echo '<tr>';
      echo '<th width="200px">Date</th>';
      echo '<th width="100px">Week</th>';
      echo '<th width="100px">Day</th>';
      echo '<th width="100px">Duty</th>';
      echo '<th width="100px">Duration</th>';
      echo '</tr>';
      echo '</thead>';
      echo '<tbody>';
      foreach ($arrAllocations as $arrAllocationWeek)
		{
			echo '<tr>';
			echo '<td><span style="display:none">'.strtotime($arrAllocationWeek['DutyDate']).'</span>'.date("jS F Y", strtotime($arrAllocationWeek['DutyDate'])).'</td>';
			echo '<td>'.spinweek($arrAllocationWeek['WeekNumber']).'</td>';
			echo '<td>'.date("l", strtotime($arrAllocationWeek['DutyDate'])).'</td>';
			echo '<td>'. $arrAllocationWeek['DutyName'].'</td>';
			echo '<td>'. round($arrAllocationWeek['Duration']/3600, 2).'</td>';
			echo '</tr>';
		}
      echo '</tbody>'; 
      echo '</table>';
    }
  }
  else {
//No Allocations......
    echo '<table width="600px" id="depsicktable" class="tablesmall">';
    echo '<thead>';

    echo '<tr>';
    echo '<td><br>There is no sickness in the chosen period.<br><br></th>';
    echo '</tr>';
    echo '</thead>';
    echo '</table>';
  }

?>
<script type="text/javascript">
$(document).ready(function(){
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "200px"
  });
  
  var table = $("#sickdatestable-<?php echo $teamId?>").DataTable({
    paging: false,
    scrollY: 300,
    scrollCollapse: true,
    info:     false,
    stateSave: true,
    deferRender: true,
    "initComplete": function( settings, json ) {
    }
  });
  
  
  $('#sicknessindividualform').validate({
    submitHandler: function(form) {
      $('input[type="submit"]').prop('disabled', true);
      $.ajax({type:'POST', url: 'page-includes/reports/sickness-report-staff-content.php', data:$('#sicknessindividualform').serialize(), success: function(data) {
        $('#staffsicknessreport-<?php echo $teamId?>').html(data);
      }});
    }
  })  
  $(function() {
    $( "#sickness-report-datepicker-start" ).datepicker({
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
    $( "#sickness-report-datepicker-end" ).datepicker({
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
    
});



function ShowQuickLinkSickness(period) {
  $.post("page-includes/reports/sickness-report-staff-content.php", {
    period: period,
    teamId: '<?php echo $teamId?>',
    ScheduledPersonID: '<?php echo $ScheduledPersonID?>'
  },
  function(data,status){
    $('#staffsicknessreport-<?php echo $teamId?>').html(data); 
   }
  )
}

function ShowIndividualSicknessNew(teamId, ScheduledPersonID, sDate, eDate, QL)
{
if(QL > -1)
{
	$.post("page-includes/reports/sickness-report-staff-nav.php", {
		ScheduledPersonID: ScheduledPersonID,
		teamId: $('#schedulingTeamSelect').val(),
		sDate: sDate,
		eDate: eDate,
		department: ScheduledPersonID
	  },  
	  function(data,status){    
		$('#SicknessReportTabs-0').html(data);
		if (ScheduledPersonID != 0) {
		  $('#SicknessReportTabs').tabs( "option", "active", 0);
		} 
	})
}else
{
	$.post("page-includes/reports/sickness-report-staff-nav.php", {
		ScheduledPersonID: ScheduledPersonID,
		teamId: $('#schedulingTeamSelect').val(),
		QL:QL,
		department: ScheduledPersonID
	  },  
	  function(data,status){    
		$('#SicknessReportTabs-0').html(data);
		if (ScheduledPersonID != 0) {
		  $('#SicknessReportTabs').tabs( "option", "active", 0);
		} 
	})
}
}

function createExcelForSicknessByPerson()
{
	let excelJson1 = '<?php echo json_encode($arrForExcel); ?>';
	let excelJson2 = '<?php echo json_encode($arrAllocations); ?>';
	let schedulePeopleList = $('#schedulePeopleList option:selected').text();
	window.open("page-includes/reports/sicknessByPerson-report-team-create-excel.php?excelJson1="+excelJson1+"&ScheduledPersonID=<?php echo $ScheduledPersonID; ?>&sDate=<?php echo $strStartDate?>&eDate=<?php echo $strEndDate?>&schedulePeopleList="+schedulePeopleList+"&teamId=<?php echo $teamId; ?>");
	console.log(excelJson2);
}
</script>
