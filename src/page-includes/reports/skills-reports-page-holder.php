<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once __DIR__ . '/../../function-includes/user-scheduling-team-list.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$teamOptions = getSchedulingTeamList(auth()->user()->defaultTeamId, 'reports-policy', 'viewAdvancedReports');
$teamDetails = getSchedulingTeamListArray('reports-policy', 'viewAdvancedReports');
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h1 style="text-align: center;">Skills Reports</h1><br><br>';
echo '</div>';

if (empty($teamOptions)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Leave groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}


echo '<div id="SkillsReportTabsDepts">
Select Scheduling Team <select id="schedulingTeamSelect" class="chosen-select selectDesign" name="QL" onChange="GetTabContent(tabVal.value);" style ="width:200px;">';
echo $teamOptions;
echo '</select>
<input type="hidden" name="quickLinkCurrentVal" id="quickLinkCurrentVal" value="-1" >
<div id="SkillsReportTabs">
	<div>
	  <div class="SkillsReportTabs">
		<ul>
		  <li onClick="tabVal.value=0;" ><a href="#SkillsReportTabs-0">Staff Skills</a></li>
		  <li onClick="tabVal.value=1;" ><a href="#SkillsReportTabs-1">Skills Utilisation</a></li>
		  <li onClick="tabVal.value=2;" ><a href="#SkillsReportTabs-2">Duty Utilisation (GMT)</a></li>
		  <li onClick="tabVal.value=3;" ><a href="#SkillsReportTabs-3">Duty Utilisation (BST)</a></li>
		  <li onClick="tabVal.value=4;" ><a href="#SkillsReportTabs-4">Staff/Duties Utilisation (GMT)</a></li>
		  <li onClick="tabVal.value=5;" ><a href="#SkillsReportTabs-5">Staff/Duties Utilisation (BST)</a></li>
		</ul>
		<div id="SkillsReportTabs-0">
		</div>
		<div id="SkillsReportTabs-1">
		</div>
		<div id="SkillsReportTabs-2">
		</div>
		<div id="SkillsReportTabs-3">
		</div>
		<div id="SkillsReportTabs-4">
		</div>
		<div id="SkillsReportTabs-5">
		</div>
	  </div>
	</div>  
</div>
</div>'; 

?>
<input type="hidden" name="tabVal" id="tabVal" value="0" >
<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $("#SkillsReportTabsDepts").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#SkillsReportTabsDepts").tabs( "option", "active" );
        GetTabContent(SelectedTab);   
      }
    });
  });    
});

function GetTabContent (SelectedTab) {
  switch (parseInt(SelectedTab)) {
  case 0:
   ShowSkillsStaff();
   break;
  case 1:
   ShowSkillsProgrammes();
   break;  
  case 2:
     ShowSkillsDuties(0);
     break; 
  case 3:
     ShowSkillsDuties(1);
     break;
  case 4:
     ShowSkillsStaffDuties(0);
     break;     
  case 5:
     ShowSkillsStaffDuties(1);
     break     
  }
}


function ShowSkillsStaff() {
  $.post("page-includes/reports/skills-reports-staff-utilisation.php", {
    departmentid: $('#schedulingTeamSelect').val()
  },  
  function(data,status){
    $('#SkillsReportTabs-0').html(data); 
  })
}

function ShowSkillsProgrammes() {
  $.post("page-includes/reports/skills-reports-programme-utilisation.php", {
    departmentid: $('#schedulingTeamSelect').val()
  },  
  function(data,status){
    $('#SkillsReportTabs-1').html(data); 
  })
}

function ShowSkillsDuties(BST) {
  $.post("page-includes/reports/skills-reports-duty-utilisation.php", {
    departmentid: $('#schedulingTeamSelect').val(),
    BST:BST
  },  
  function(data,status){
    if (BST == 0) {
      $('#SkillsReportTabs-2').html(data); 
    }
    else {
      $('#SkillsReportTabs-3').html(data); 
    }    
  })
}


function ShowSkillsStaffDuties(BST) {
  $.post("page-includes/reports/skills-reports-staff-duty-utilisation.php", {
    departmentid: $('#schedulingTeamSelect').val(),
    BST:BST
  },  
  function(data,status){
    if (BST == 0) {
      $('#SkillsReportTabs-4').html(data); 
    }
    else {
      $('#SkillsReportTabs-5').html(data); 
    }    
  })
}

<?php 
if(empty($_REQUEST['departmentid']))
{
	echo "GetTabContent(0)";
}
?>
</script>


