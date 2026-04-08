<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once __DIR__ . '/../../function-includes/user-scheduling-team-list.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$teamOptions = getSchedulingTeamList(auth()->user()->defaultTeamId, 'reports-policy', 'viewAdvancedReports');
$teamDetails = getSchedulingTeamListArray('reports-policy', 'viewAdvancedReports');

echo '<div class="tableheadersmall medtextboldcentre" >'; 
echo '<br><h1 style="text-align: center;">Underlying Rota Pattern - Reports</h1><br><br>';
echo '</div>';

if (empty($teamOptions)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" >';
  echo '<br>You do not have any Allocations groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}

echo '<div id="RotasReportTabsDepts">
Select Scheduling Teams : <select id="schedulingTeamSelect" class="chosen-select selectDesign" onChange="GetTabTopContent(chkTab.value);" style="width:200px;">';
echo $teamOptions;
echo '</select>
<div id="TeamsConfigReportTabs">
	<div>
	<ul>
		<li><a onclick="$(\'#chkTab\').val(0);" href="#RotasReportTabs-0">Patterns Summary</a></li>
		<li><a onclick="$(\'#chkTab\').val(0);" id="tab2" href="#RotasReportTabs-1">Underlying Rota  Patterns</a></li>
	</ul>
	  <div class="RotasReportTabs">
		<div id="RotasReportTabs-0">
		</div> 
		<div id="RotasReportTabs-1">
		</div> 
	  </div>
	</div>  
</div>
</div>';
?>
<input type="hidden" name="chkTab" id="chkTab" value="0" />
<script type="text/javascript">
var staffnumber = '';
$(document).ready(function(){
  $(function() {
    $("#RotasReportTabsDepts").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#RotasReportTabsDepts").tabs( "option", "active" );
		if($('#chkTab').val() == 0)
		{
			GetTabTopContent(SelectedTab);
		}
      }
    });
  });
});

function GetTabTopContent(SelectedTab) {
	SelectedTab = parseInt(SelectedTab);
	switch (SelectedTab) {
		case 0 : ShowRotasDepartment($('#schedulingTeamSelect').val());
				break;
		case 1 : ShowRotasIndividualPre($('#schedulingTeamSelect').val());
				break;
	}
}

function ShowRotasIndividual(DepartmentID, staffnumber, ScheduledPersonID=0) {
  $.post("page-includes/reports/allocations-reports-rota-pattern.php", {
    departmentid: DepartmentID,
    staffnumber: staffnumber,
	ScheduledPersonID : ScheduledPersonID
  },  
  function(data,status){
	$('#chkTab').val(1);
    $('#RotasReportTabsDepts').tabs( "option", "active", 1 );
    $('#RotasReportTabs-1').html(data);   
  })
}

function ShowRotasIndividualPre(DepartmentID, staffnumber=0) {
  $.post("page-includes/reports/allocations-reports-rota-pattern.php", {
    departmentid: DepartmentID,
    staffnumber: staffnumber
  },  
  function(data,status){
    $('#RotasReportTabs-1').html(data);  
  })
}

function ShowRotasDepartment(DepartmentID) {
  $.post("page-includes/reports/allocations-reports-rotas-team.php", {
    departmentid: DepartmentID
  },  
  function(data,status){
    $('#RotasReportTabs-0').html(data); 
  })
}


<?php 
if(empty($_REQUEST['departmentid']))
{
	echo "GetTabTopContent(0);";
}
?>

</script>


