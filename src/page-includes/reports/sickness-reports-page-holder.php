<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once __DIR__ . '/../../function-includes/user-scheduling-team-list.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

$teamOptions = getSchedulingTeamList(auth()->user()->defaultTeamId, 'reports-policy', 'viewBasicReports');

$teamDetails = getSchedulingTeamListArray('reports-policy', 'viewBasicReports');
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h1 style="text-align: center;">Sickness Reports</h1><br><br>';
echo '</div>';

if (!isset($teamOptions)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
  echo '<br>You do not have any Leave groups defined!<br>Please contact your Departmental administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
}

echo '<div id="SicknessReportTabsDepts">
Select Scheduling Teams : <select id="schedulingTeamSelect" class="chosen-select" onChange="GetTabContent(this.value);" >';
echo $teamOptions;
echo '</select>';
echo '<div id="SicknessReportTabs">';
echo '<div>';
echo '  <div class="SicknessReportTabs">';
echo '    <ul>';
echo '      <li><a href="#SicknessReportTabs-0">Sickness by Person</a></li>';
echo '      <li><a href="#SicknessReportTabs-1">Team Sickness</a></li>';
echo '      <li><a href="#SicknessReportTabs-2">Sickness Occurrences</a></li>';
echo '    </ul>';
echo '    <div id="SicknessReportTabs-0">';
echo '    </div>';
echo '    <div id="SicknessReportTabs-1">';
echo '    </div>';
echo '    <div id="SicknessReportTabs-2">';
echo '    </div>';  
echo '  </div>';
echo '</div>';  
echo '</div>';  
  
echo '</div>'; 
?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $("#SicknessReportTabsDepts").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#SicknessReportTabsDepts").tabs( "option", "active" );
        GetTabContent(SelectedTab);     
      }
    });
  });

<?php 
foreach ($teamDetails as $strDepartment) {
  $intTeamID = $strDepartment['id'];
?>

$("#SicknessReportTabs").tabs({  
  active: 1,
  create: function( event, ui ) {
    ShowIndividualSickness(<?php echo $intTeamID ?>, 0);
  },
  activate : function( event, ui ) {
    var SelectedTab = $("#SicknessReportTabs").tabs( "option", "active" );
    if ($.trim($("#SicknessReportTabs-"+SelectedTab).html()).length == 0) { 
      GetTabContent(<?php echo $intTeamID?>, SelectedTab);
    }
  } 
});
<?php
}
?> 

ShowDepartmentSickness();

 
})
function GetTabTopContent(SelectedTab) {
switch (SelectedTab) {
<?php
$intCount = 0; 
foreach ($teamDetails as $intTeamID => $strDepartment) {

echo "case ".$intCount.":\n";
echo "ShowDepartmentSickness();\n";
echo "break;\n";
$intCount++;
}
?>

}
}

function GetTabContent (teamId, SelectedTab) {
	SelectedTab = SelectedTab ? SelectedTab : $("#SicknessReportTabsDepts").tabs( "option", "active" );
  switch (SelectedTab) {
  case 0:
   ShowIndividualSickness(teamId, 0);
   break;
  case 1:
   ShowDepartmentSickness();
   break;  
  case 2:
   ShowSicknessOccurrences();
   break; 
  }
}

function ShowIndividualSickness(teamId, ScheduledPersonID) {
  $.post("page-includes/reports/sickness-report-staff-nav.php", {
    ScheduledPersonID: ScheduledPersonID,
    teamId: $('#schedulingTeamSelect').val()
  },  
  function(data,status){    
    $('#SicknessReportTabs-0').html(data);
    if (ScheduledPersonID != 0) {
      $('#SicknessReportTabs').tabs( "option", "active", 0);
    } 
  })
}

function ShowDepartmentSickness() {
  $.post("page-includes/reports/sickness-report-team.php", {
    teamId: $('#schedulingTeamSelect').val()
  },  
  function(data,status){
    $('#SicknessReportTabs-1').html(data); 
  })
}
 
 
function ShowSicknessOccurrences() {
  $.post("page-includes/reports/sickness-report-collated.php", {
    teamId: $('#schedulingTeamSelect').val()
  },  
  function(data,status){
    $('#SicknessReportTabs-2').html(data); 
  })
} 








</script>


