<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';

$arrReportTeams = getTeamsAndShowReportFlag();
usort($arrReportTeams, function($a, $b) {
    return $a['isDefault'] < $b['isDefault'];
});
if (count($arrReportTeams) < 1) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Allocations groups defined!<br>Please contact your Scheduling Team Admin to get these assigned.<br><br>';
  echo '</div>';
  die;
}

echo '<div id="AllocationsReportTabsDepts">
Select Scheduling Team <select id="schedulingTeamSelect" class="chosen-select selectDesign" onChange="ShowAllocUsage();" >
<option value="0">All Team</option>';
foreach ($arrReportTeams as $strDepartment) {
    echo '<option value="'.$strDepartment['schedulingTeamId'].'">'.$strDepartment['schedulingTeamName'].'</option>';
}
echo '</select>
<input type="hidden" name="quickLinkCurrentVal" id="quickLinkCurrentVal" value="-1" >
<div id="AllocationsReportTabs">
	<div>
	  <div class="AllocationsReportTabs">
		<div id="AllocationsReportTabs-0">
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
        GetTabContent(SelectedTab);     
      }
    });
  });
  ShowAllocUsage();



function GetTabContent(SelectedTab) {
switch (SelectedTab) {
<?php
echo "case 0:\n";
echo "ShowAllocUsage();\n";
echo "break;\n";
$intCount = 1;
foreach ($arrReportDepartments as $intDepartmentID => $strDepartment) {
echo "case ".$intCount.":\n";
echo "ShowAllocUsage();\n";
echo "break;\n";
$intCount++;
}
?>

}
}

function ShowAllocUsage() {
  $.post("page-includes/reports/allocations-usage-team.php", {
    departmentid: $('#schedulingTeamSelect').val()
  },  
  function(data,status){
    $('#AllocationsReportTabs-0').html(data); 
  })
}
  
});



</script>


