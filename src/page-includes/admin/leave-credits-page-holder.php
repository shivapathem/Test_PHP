<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$strUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$arrLeaveCreditManagementTeams = getTeamsAndShowReportFlag($strUser, $strUserId);
$intFirstKey = $intFirstKey ?? 0;
$defaultTeam = current(array_filter($arrLeaveCreditManagementTeams, function($team) {
  return $team['isDefault'];
}));
$defaultTeamId = $defaultTeam ? $defaultTeam['schedulingTeamId'] : null;
usort($arrLeaveCreditManagementTeams, function($a, $b) {
  return strcasecmp($a['schedulingTeamName'], $b['schedulingTeamName']);
});
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h1 style="text-align: center;">Leave Credits and Management</h1><br><br>';
echo '</div>';

if (!isset($arrLeaveCreditManagementTeams)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You are not assigned as a Scheduling Team Admin in any Team!<br>Please contact your Scheduling Team administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}
echo '<div id="LeaveCreditTabsTeams">
Select Scheduling Team <select id="schedulingTeamSelect" class="chosen-select selectDesign" name="QL" onChange="GetTabCreditTeam(this.value);" >';
foreach ($arrLeaveCreditManagementTeams as $strDepartment) {
  if ($strDepartment['Scheduling Team Admin'] != '') {
    $selected = ($strDepartment['schedulingTeamId'] == $defaultTeamId) ? 'selected' : '';
    echo '<option value="'.$strDepartment['schedulingTeamId'].'" '.$selected.'>'.$strDepartment['schedulingTeamName'].'</option>';
  }
}
echo '</select>';
echo '<div id="LeaveCreditTabsTeams-0">';//.$strDepartment['schedulingTeamId'].'">';
  echo '</div>';

echo '</div>'; 

?>
<style>
	.chosen-container .chosen-results li.active-result{
		width: 100%;
	}
	.chosen-results{
		background: #FFF;
	}
</style>
<script type="text/javascript">
$(document).ready(function(){
	$(".chosen-select").chosen({
		no_results_text: "Oops, nothing found!",
		width : '200px'
	});
  $(function() {
    $("#LeaveCreditTabsTeams").tabs({
      activate : function( event, ui ) {
        var TeamID = $("#LeaveCreditTabsTeams").find('li.ui-tabs-active').data('teamid');
        var SelectedTab = $("#LeaveCreditTabsTeams").tabs( "option", "active" );
        GetTabCreditTeam(TeamID);     
      }
    });
  });
  
  <?php 
	if(empty($_REQUEST['teamid']))
	{
		echo "GetTabCreditTeam($('#schedulingTeamSelect').val())";
	}
	?>
});


function GetTabCreditTeam(TeamID, Year) {
  $.post("page-includes/admin/leave-credits-team.php", {
    teamid: TeamID,
    year: Year
  },  
  function(data,status){
    $('#LeaveCreditTabsTeams-0').html(data); 
  })
} 

</script>


