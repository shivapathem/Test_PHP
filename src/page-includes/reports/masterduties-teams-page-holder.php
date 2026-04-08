<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';

$arrReportTeams = getTeamsAndShowReportFlag($strUser);
usort($arrReportTeams, function($a, $b) {
    return $a['isDefault'] <= $b['isDefault'];
});
if (!isset($arrReportTeams)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Allocations groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}

echo '<div id="TeamsConfigReportTabsDepts" style="font-size: 10pt;">
Select Scheduling Team <select id="schedulingTeamSelect" class="chosen-select selectDesign" onChange="ShowTeamsConfigDepartment(this.value);" style="min-width: 100px;">';
foreach ($arrReportTeams as $strTeam) {
	if(($strTeam['Advanced Reports']!= NULL) || ($strTeam['Scheduling Team Admin']!= NULL) || ($strTeam['Scheduler']!= NULL) || ($strTeam['Senior Scheduler']!= NULL)){
    echo '<option value="'.$strTeam['schedulingTeamId'].'">'.$strTeam['schedulingTeamName'].'</option>';
}}
echo '</select>
<div id="TeamsConfigReportTabs">
	<div>
	  <div class="TeamsConfigReportTabs">
		<div id="TeamsConfigReportTabs-0">
		</div> 
	  </div>
	</div>  
</div>
</div>';
?>

<script type="text/javascript">
$(document).ready(function(){
	ShowTeamsConfigDepartment($('#schedulingTeamSelect').val());  
  $("#schedulingTeamSelect").chosen();
});

function ShowTeamsConfigDepartment(DepartmentID) {
  $.post("page-includes/reports/master-duties-teams.php", {
    departmentid: DepartmentID
  },  
  function(data,status){
    $('#TeamsConfigReportTabs-0').html(data); 
  })
}

</script>


