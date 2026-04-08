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
echo '<h1 class="sr-only">Master Duty Summary</h1>';
echo '<div id="MasterDutiesReportTabsSummDepts" style="min-width:1333px">
Select Scheduling Teams : <select id="schedulingTeamSelect" class="chosen-select" onChange="ShowMasterDutiesDepartmentSummary(this.value);" >';
 echo $teamOptions;
 echo '</select>
 <div id="MasterDutiesReportTabsSumm">
 </div>
</div>'; 
?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $("#MasterDutiesReportTabsSummDepts").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#MasterDutiesReportTabsSummDepts").tabs( "option", "active" ); 
        GetTabContent(SelectedTab);     
      }
    });
  });



function GetTabContent(SelectedTab) {
switch (SelectedTab) {
<?php
$intCount = 0;
foreach ($teamDetails as $strDepartment) {
echo "case ".$intCount.":\n";
echo "ShowMasterDutiesDepartmentSummary($('#schedulingTeamSelect').val());\n";
echo "break;\n";
$intCount++;
}
?>

}
}


ShowMasterDutiesDepartmentSummary($('#schedulingTeamSelect').val());  
});



</script>


