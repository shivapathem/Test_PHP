<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
$intPageID = $_REQUEST["pageid"];
$arrReportTeams = getTeamsAndShowReportFlag();
usort($arrReportTeams, function($a, $b) {
    return $a['isDefault'] < $b['isDefault'];
});
if (count($arrReportTeams)<=0) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Allocations groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}

echo '<div id="AllocationsReportSumTabsDepts" style="font-size: 10pt;">
Select Scheduling Team <select id="schedulingTeamSelect" class="chosen-select selectDesign" onChange="ShowAllocUsageSummary();" >
<option value="0">All Team</option>';
foreach ($arrReportTeams as $strDepartment) {
    echo '<option value="'.$strDepartment['schedulingTeamId'].'">'.$strDepartment['schedulingTeamName'].'</option>';
}
echo '</select>
<div id="AllocationsReportSumTabs">
	<div>
	  <div class="AllocationsReportSumTabs">
		<div id="AllocationsReportSumTabs-0">
		</div> 
	  </div>
	</div>  
</div>
</div>';

?>

<script type="text/javascript">
function ShowAllocUsageSummary() {
  $.post("page-includes/reports/allocations-usage-summary-department.php", {
    departmentid: $('#schedulingTeamSelect').val(),
    pageid: '<?php echo $intPageID?>'
  },  
  function(data,status){
    $('#AllocationsReportSumTabs-0').html(data); 
  })
}
  ShowAllocUsageSummary();  
</script>


