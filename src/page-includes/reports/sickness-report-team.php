<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/reports-functions.php';

$intTeamID = $_REQUEST['teamId'];

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
    $intPeriod = -12;  
    $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
    $strEndDate = date("Y-m-d");
  }  
}
if (isset($_REQUEST['SortCodeFilter'])) {
  $strSortcodeFilter = $_REQUEST['SortCodeFilter'];
}
else {
  if (isset($_SESSION["reports"]["sortcodesick$intTeamID"])) {
    // Is the session set?
    $strSortcodeFilter = $_SESSION["reports"]["sortcodesick$intTeamID"];    
  }
  else {
    $strSortcodeFilter = '';  
  }
}
$_SESSION["reports"]["sortcodesick$intTeamID"] = $strSortcodeFilter;
echo '<form id="sicknessdepartmentform'.$intTeamID.'">';
echo '<table class="tablesmall">';
echo '<tr>';
echo '<th colspan="9">';
echo '<br>Sickness Record for<br><br>';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px" height="30px">Start Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="sDate" id="sickness-dept-report-datepicker-start'.$intTeamID.'" value="'.$strStartDate.'" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="slrralternate'.$intTeamID.'" size="30" value="'.date("l, j F, Y", strtotime($strStartDate)).'"></td>';
echo '<th width="100px">End Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="eDate" id="sickness-dept-report-datepicker-end'.$intTeamID.'" value="'.$strEndDate.'" size="20" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="elrralternate'.$intTeamID.'" size="30" value="'.date("l, j F, Y", strtotime($strEndDate)).'"></td>';
echo '<th colspan="2" height="30px">';
echo 'Quick Links';
echo '</th>';
echo '<td class="thBackGround" colspan="7">';
echo '<select class="chosen-select" name="QL" onchange="javascript:ShowQuickLinkDepSickness(value)";>';
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
echo '<td align="left"><input id="submit" type="submit" value="&nbsp;&nbsp;Go&nbsp;&nbsp;" name="Update"></td>';
echo '</tr>';
echo '</table>';
echo '<input type="hidden" name="teamId" value="'.$intTeamID.'">';
echo '</form>';
echo '<br>';

$arrCollated = getSicknessReport($intTeamID, $strStartDate, $strEndDate);

$arrCollatedNew = array();
foreach($arrCollated as $arrCollatedVal)
{
	$arrCollatedVal['TotalHoursSick'] = round($arrCollatedVal['TotalHoursSick'] / 3600, 2);
	if(!$arrCollatedVal['HideStats'])
	{
		$arrCollatedNew[] = $arrCollatedVal;
	}
}

if (isset($arrCollated)) {
  $intTotalDutyCount = array_sum(array_map(function($item){return $item['TotalDaysWorked'];}, $arrCollatedNew));
  $intTotalDaysSick =  array_sum(array_map(function($item){return $item['TotalDaysSick'];}, $arrCollatedNew));
  $intTotalHoursSick =  array_sum(array_map(function($item){return $item['TotalHoursSick'];}, $arrCollatedNew));
  $StaffInBase = count($arrCollatedNew);

  if ($intTotalDutyCount == 0) {
    $strPercentSick = '0.00';
  }
  else {
    $strPercentSick = number_format(($intTotalDaysSick / ($intTotalDutyCount + $intTotalDaysSick)) * 100, 2);
  }
  echo '<div class="tableheadersmall medtextbold" style="width:100%">';
  echo '<table width="100%">';
  echo '<tr class="medtextbold">';  
  echo '<td valign="top" nowrap>';
  echo 'Within this period:<br><br>';
  echo 'There have been '.$intTotalDaysSick.' days of sickness/unavailability to work.<br>';
  echo number_format($intTotalDutyCount, 0, '',",").' days have been worked.<br>';
  echo $StaffInBase.' people Scheduled. ';
  echo '<br>';
  echo '</td>';
  echo '<td valign="top" nowrap>';
  echo '<br><br>The sickness average is '.$strPercentSick.'%<br>';
  if ($StaffInBase != 0) {
    echo 'The average number of days sick/unavailable to work per person  '.ceil($intTotalDaysSick / $StaffInBase).'<br>';
    echo 'The average number of hours sick/unavailable per person '.ceil($intTotalHoursSick / $StaffInBase);       
  }

  echo '</td>';  

  echo '<td valign="top" nowrap>';
  echo '<br><br>Total hours of Sickness '.ceil($intTotalHoursSick).'<br>';
  echo 'Indicative cost of Sickness &pound;'.number_format($intTotalHoursSick * 25 , 0).'<br>';     
  echo '</td>'; 

echo '<td align="center" class=" handcursor" onclick="javascript:CreateDepSickExcel()">Create Excel&nbsp;<br>';
echo '<img width="24px" height="24px" border="0" src="images/excel.png"></img></td>';
      
  echo '</tr>';  
  echo '</table>';  
  
  
  echo '</div><br>';

  echo '<table id="depsicktable-'.$intTeamID.'" class="tablesmall compact stripe">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>Hidden</th>';  
  echo '<th>Name</th>';
  echo '<th>Days Unavailable/Sick</th>';
  echo '<th>Weeks Sick</th>';
  echo '<th>Occurrences</th>';
  echo '<th>Hours</th>';
  echo '<th>Worked Days</th>';
  echo '<th>Percentage</th>';
  echo '<th>Most Recent</th>'; 
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  $unavailableDays = 0;
  if(isset($arrCollated)) {
    foreach ($arrCollated as $record)
	{
		if($record['DaysUnavailable'] > 0)
		{
      ?>
		  <tr class="handcursor">
			<td align="center">
				<span style="display:none"></span>
				<?php 
					if($record['HideStats'])
					{ 
				?>
			  <img onclick="javascript:ToggleStats('<?php echo $intTeamID; ?>', '<?php echo $record['SchedulingPersonID']; ?>');" border="0" src="images/green_tick.png" width="12px" height="12px">
				<?php 
					}else
					{
				?>
			  <img onclick="javascript:ToggleStats('<?php echo $intTeamID; ?>', '<?php echo $record['SchedulingPersonID']; ?>');" border="0" src="images/red_cross.png" width="12px" height="12px">
				<?php 
					} 
				?>
			</td>
			<td onclick="javascript:ShowIndividualSickness('<?php echo $intTeamID; ?>', '<?php echo $record['SchedulingPersonID']; ?>')"><?php echo $record['DisplayName']; ?></td>
			<td align="center"><?php echo $record['DaysUnavailable']; ?></td>
			<td align="center"><?php echo $record['WeeksSick']; ?></td>  
			<td align="center"><?php echo $record['Occurrences']; ?></td>      
			<td align="center"><?php echo round($record['TotalHoursSick'] / 3600, 2); ?></td>
			<td align="center"><?php echo $record['TotalDaysWorked']; ?></td>
			<td align="center"><?php echo $record['Percentage']; ?></td>
			<td><?php echo (!empty($record['MaxDutyDate']) ? date("jS F Y", strtotime($record['MaxDutyDate'])) : ''); ?><span style="display:none"></span></td>
		</tr>
	  <?php
		}
    }
  }
  echo '</tbody>';
  echo '</table>';
}
else {
  echo '<table id="depsicktable-'.$intTeamID.'" class="tablesmall compact stripe" width="100%">';
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
<?php
if (isset($arrCollated)) {
?>  
  
  
  var table = $("#depsicktable-<?php echo $intTeamID?>").DataTable({
    paging: false,
    scrollY: 400,
    scrollCollapse: true,
    info:     false,
    stateSave: true,
    deferRender: true,
    order: [[ 2, "desc" ]],
  columnDefs: [
    { width: 100, targets: 4 },
    { width: 100, targets: 5 },
    { width: 100, targets: 6 },
    { width: 100, targets: 7 },
    { width: 100, targets: 8 },
  ],     
  });  
  yadcf.init(table, [
    {column_number: 1,
      filter_type: 'text'
    },
  ]);
  if ( $.cookie("#depsicktable-<?php echo $intTeamID?>") !== null ) {
    scrollPos = $.cookie("#depsicktable-<?php echo $intTeamID?>");
    $("#depsicktable-<?php echo $intTeamID?>").closest('.dataTables_scrollBody').scrollTop(scrollPos);      
  };  
  
<?php 
}
?>  
  
  
  
  $(function() {
    $( "#sickness-dept-report-datepicker-start<?php echo $intTeamID?>" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#slrralternate<?php echo $intTeamID?>",
      altFormat: "DD, d MM, yy"
    });
  });
  $(function() {
    $( "#sickness-dept-report-datepicker-end<?php echo $intTeamID?>" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOn: "button",
      firstDay: '6',
      buttonImage: "images/calendar.gif",
      buttonImageOnly: true,
      dateFormat: 'yy-mm-dd',
      altField: "#elrralternate<?php echo $intTeamID?>",
      altFormat: "DD, d MM, yy"
    });
  });
 
    
}) 
$("#depsicktable-<?php echo $intTeamID?>").closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $("#depsicktable-<?php echo $intTeamID?>").closest('.dataTables_scrollBody').scrollTop();
  $.cookie("#depsicktable-<?php echo $intTeamID?>", currpos);
});


 
  $('#sicknessdepartmentform<?php echo $intTeamID?>').validate({
    submitHandler: function(form) {
      $.ajax({type:'POST', url: 'page-includes/reports/sickness-report-team.php', data:$('#sicknessdepartmentform<?php echo $intTeamID?>').serialize(), success: function(data) {
        $('#SicknessReportTabs-1').html(data);
      }});
    }
  }) 
  
function ShowQuickLinkDepSickness(period) {
  $.post("page-includes/reports/sickness-report-team.php", {
    period: period,
    teamId: '<?php echo $intTeamID?>'
  },
  function(data,status){
    $('#SicknessReportTabs-1').html(data); 
   }
  )
}

function ToggleStats(teamId, ScheduledPersonID) {
  $.post("page-includes/reports/togglestaffvisibility.php", {
    teamId: teamId,
    ScheduledPersonID: ScheduledPersonID
  },
  function(data,status){
  <?php
  if ($intPeriod == 0) {
  ?>
    $.ajax({type:'POST', url: 'page-includes/reports/sickness-report-team.php', data:$('#sicknessdepartmentform').serialize(), success: function(data) {
      $('#SicknessReportTabs-1').html(data);
    }});
  <?php
  }
  else {
  ?>
  ShowQuickLinkDepSickness(<?php echo $intPeriod?>);
  <?php
  }
  ?>
   }
  )
}


function CreateDepSickExcel() {
  window.open("page-includes/reports/sickness-report-team-create-excel.php?teamId=<?php echo $intTeamID?>&sDate=<?php echo $strStartDate?>&eDate=<?php echo $strEndDate?>");
}
</script>
<style>
	.thBackGround{
		background-color:#DDDDDD;
	}
</style>