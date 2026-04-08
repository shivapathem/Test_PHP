<?php
 session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';

$teamId = $_REQUEST['teamId'];
$arrStaffInDepartment = '';//GetStaffInTeamSimple($teamId);
if (isset($_REQUEST['ScheduledPersonID'])) {
  $strStaffNumber = $_REQUEST['ScheduledPersonID'];
}
else {
  $strStaffNumber = '';
  $strStaffNumber = array_keys($arrStaffInDepartment)[0];
}
echo '<div id="staffsicknessreport-'.$teamId.'">';
echo '</div>';

$_REQUEST["sDate"] = $_REQUEST["sDate"] ?? null;
$_REQUEST["eDate"] = $_REQUEST["eDate"] ?? null;
$_REQUEST["QL"] = $_REQUEST["QL"] ?? null;

?>
<script type="text/javascript">
 $(document).ready(function() {
   $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "350px"
  });
  ShowSicknessReportStaff(<?php echo $teamId?>, '<?php echo $strStaffNumber?>', '<?php echo $_REQUEST["sDate"]; ?>', '<?php echo $_REQUEST["eDate"]; ?>', '<?php echo $_REQUEST["QL"]; ?>');
})

function ShowSicknessReportStaff (teamId, ScheduledPersonID, sDate, eDate, QL) {
if(sDate != '' && eDate !='')
{
  $.post("page-includes/reports/sickness-report-staff-content.php", {
    teamId: teamId,
    ScheduledPersonID: ScheduledPersonID,
	sDate: sDate,
	eDate: eDate,
	department : ScheduledPersonID
  },  
  function(data,status){
    $('#staffsicknessreport-<?php echo $teamId?>').html(data); 
  })
}else if(QL !=0)
{
	$.post("page-includes/reports/sickness-report-staff-content.php", {
		teamId: teamId,
		ScheduledPersonID: ScheduledPersonID,
		period: QL,
		department : ScheduledPersonID
	  },  
	  function(data,status){
		$('#staffsicknessreport-<?php echo $teamId?>').html(data); 
	  })
}else
{
	$.post("page-includes/reports/sickness-report-staff-content.php", {
		teamId: teamId,
		ScheduledPersonID: ScheduledPersonID,
	  },  
	  function(data,status){
		$('#staffsicknessreport-<?php echo $teamId?>').html(data); 
	  })
}
}


  
</script>