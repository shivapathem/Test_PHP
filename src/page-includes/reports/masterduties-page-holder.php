<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once __DIR__ . '/../../function-includes/user-scheduling-team-list.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$teamOptions = getSchedulingTeamList(auth()->user()->defaultTeamId, 'reports-policy', 'viewAdvancedReports');
$teamDetails = getSchedulingTeamListArray('reports-policy', 'viewAdvancedReports');
if (empty($teamOptions)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Allocations groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}
echo '<h1 class="sr-only">Master Duty Report</h1>';
echo '<div id="MasterDutiesReportTabsDepts" >
Select Scheduling Team <select id="schedulingTeamSelect" class="chosen-select selectDesign" onChange="ShowMasterDutiesDepartmentHolder(this.value);" style="width:200px"; >';
echo $teamOptions;
echo '</select>
<div id="TeamsConfigReportTabs">
	<div>
	  <div class="MasterDutiesReportTabs" style="style="min-width:1333px">
		<div id="MasterDutiesReportTabs-0">
		</div> 
	  </div>
	</div>  
</div>
</div>';

?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $("#MasterDutiesReportTabsDepts").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#MasterDutiesReportTabsDepts").tabs( "option", "active" ); 
        GetTabContent(SelectedTab);     
      }
    });
  });



function GetTabContent(SelectedTab)
{
	switch (SelectedTab)
	{
		<?php
		$intCount = 0;
		foreach ($teamDetails as $strDepartment)
		{
			echo "case ".$intCount.":\n";
			echo "ShowMasterDutiesDepartmentHolder(".$strDepartment['id'].");\n";
			echo "break;\n";
			$intCount++;
		}
		?>
	}
}


ShowMasterDutiesDepartmentHolder ($('#schedulingTeamSelect').val());  
});


</script>


