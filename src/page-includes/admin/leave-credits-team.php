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
$intTeamID = $_REQUEST['teamid'] ?? 0;
$additionalflag = $_REQUEST['additionalflag'] ?? 0;
echo '<div id="LeaveCreditTabs'.$intTeamID.'">';
echo '<ul>';
echo '<li><a href="#LeaveTabsTeams'.$intTeamID.'-0">Credits</a></li>';  
echo '<li><a href="#LeaveTabsTeams'.$intTeamID.'-1">Balances</a></li>'; 
echo '<li><a href="#LeaveTabsTeams'.$intTeamID.'-2">Individual Report</a></li>';
echo '<li><a href="#LeaveTabsTeams'.$intTeamID.'-3">PHL Credits</a></li>';
echo '</ul>';
echo '<div id="LeaveTabsTeams'.$intTeamID.'-0" class="mainDiv">';
echo '</div>';
echo '<div id="LeaveTabsTeams'.$intTeamID.'-1" class="mainDiv">';
echo '</div>';
echo '<div id="LeaveTabsTeams'.$intTeamID.'-2">';
echo '</div>';
echo '<div id="LeaveTabsTeams'.$intTeamID.'-3" class="mainDiv">';
echo '</div>';
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    $("#LeaveCreditTabs<?php echo $intTeamID?>").tabs({
      activate : function( event, ui ) {
        var SelectedTab = $("#LeaveCreditTabs<?php echo $intTeamID?>").tabs( "option", "active" );
           GetLeaveCreditTab(<?php echo $intTeamID?>, SelectedTab);  
      }
    });
  });
  GetLeaveCreditTab(<?php echo $intTeamID?>, 0);
});


function GetLeaveCreditTab(TeamID, SelectedTab, CurrentYear, HolidayId,additionalflag,typeval,updateCheckedIdsVal = 0) {
  switch (SelectedTab) {
  case 0:
 
    $.post("page-includes/admin/leave-credits-content.php", {
      teamid: TeamID,
      taboption: SelectedTab,
      year: CurrentYear,
      additionalflag: additionalflag,
      typeval:typeval,
      updateCheckedIdsVal:updateCheckedIdsVal
    },  
    function(data,status){
      $('.mainDiv').html('');
      $('#LeaveTabsTeams<?php echo $intTeamID?>-'+SelectedTab).html(data); 
    })
    break;  
  case 1:
    $.post("page-includes/admin/leave-balances-content.php", {
      teamid: TeamID,
      taboption: SelectedTab,
      year: CurrentYear
    },  
    function(data,status){
      $('.mainDiv').html('');
      $('#LeaveTabsTeams<?php echo $intTeamID?>-'+SelectedTab).html(data); 
    })
    break;  
  case 3:
    $.post("page-includes/admin/leave-credits-content-holidays.php", {
      teamid: TeamID,
      taboption: SelectedTab,
      catid: 2,
      year: CurrentYear,
      HolID: HolidayId
    },  
    function(data,status){
    $('.mainDiv').html('');
      $('#LeaveTabsTeams<?php echo $intTeamID?>-'+SelectedTab).html(data); 
    })
    break;
  }
} 
function ShowAllocateLeaveCredit(logon, leaveyear, teamid) {
  $.post("page-includes/admin/leave-allocate-credit.php", {
    user: logon,
    year: leaveyear,
    teamid: teamid
  },
  function(data,status){{
      $('#LeaveTabsTeams'+teamid+'-2').html(data);
      $("#LeaveCreditTabs<?php echo $intTeamID?>").tabs( "option", "active", 2 ); 
    }}
  );
}
</script>