<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';

$arrReportTeams = getTeamsAndShowReportFlag();

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br>Allocations EFT - Reports<br><br><br>';
echo '</div>';

if (count($arrReportTeams) < 1 ) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Allocations groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}

echo '<div id="AllocationsReportTabsDepts">
	Select Scheduling Team <select id="schedulingTeamSelect" class="chosen-select" name="QL" onChange="ShowAllocSummaryDepartment(); ShowAllocReportMonthly();" >';
foreach ($arrReportTeams as $strDepartment) {
    if($strDepartment['isDefault'])
	{
		$isSelected = 'selected="selected"';
	}else
	{
		$isSelected = '"';
	}
    echo '<option value="'.$strDepartment['schedulingTeamId'].'" '.$isSelected.'>'.$strDepartment['schedulingTeamName'].'</option>';
}
echo '</select>
<input type="hidden" name="quickLinkCurrentVal" id="quickLinkCurrentVal" value="-1" >
<div id="AllocationsReportTabs">
	<div>
	  <div class="AllocationsReportTabs">
		<ul>
		  <li onChange="ShowAllocSummaryDepartment();"><a href="#AllocationsReportTabs-0">Summary</a></li>
		  <li onChange="ShowAllocReportMonthly();"><a href="#AllocationsReportTabs-1">Daily Detail</a></li>
		</ul>
		<div id="AllocationsReportTabs-0">
		</div>
		<div id="AllocationsReportTabs-1">
		</div> 
	  </div>
	</div>  
</div>
</div>'; 

?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $("#AllocationsReportTabsDepts").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#AllocationsReportTabsDepts").tabs( "option", "active" );
        GetTabTopContent(SelectedTab);     
      }
    });
  }); 
ShowAllocSummaryDepartment();
ShowAllocReportMonthly();
});

function GetTabTopContent(SelectedTab) {
switch (SelectedTab) {
<?php
$intCount = 0; 
foreach ($arrReportTeams as $strDepartment) {

echo "case ".$intCount.":\n";
echo "ShowAllocSummaryDepartment(".$strDepartment['schedulingTeamId'].");\n";
echo "break;\n";
$intCount++;
}
?>

}
}

function GetTabContent (DepartmentID, SelectedTab) {
  switch (SelectedTab) {
  case 0:   
   break;
  case 1:
   ShowAllocReportMonthly(DepartmentID);
   break;     
  }
}

function ShowAllocReportMonthly(DepartmentID=0, StartDate=new Date().toISOString().split('T')[0]) {
  $.post("page-includes/reports/allocations-report-monthly.php", {
    departmentid: $('#schedulingTeamSelect').val(),
    date: StartDate
  },  
  function(data,status){
    $('#AllocationsReportTabs-1').html(data);  
  })
}

function ShowAllocSummaryDepartment() {
  $.post("page-includes/reports/allocations-reports-summary.php", {
    departmentid: $('#schedulingTeamSelect').val(),
	period: $('#quickLinkCurrentVal').val()
  },  
  function(data,status){
    $('#AllocationsReportTabs-0').html(data); 
  })
}

function ShowAllocationsInReport(DepartmentID, WeekNumber) {
  $.post("page-includes/allocations/allocations-weekly.php", {
    department: DepartmentID,
    WeekNumber: WeekNumber
  },  
  function(data,status){
    $('#AllocationsReportTabs-2-'+DepartmentID).html(data); 
  })
}


</script>


