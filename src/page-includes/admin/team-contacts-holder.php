<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../users/process/classUserSetup.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
/* new data  team wise*/

$setupObj = new classUserSetup();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$intFirstTeam = 0;
$strFirstTeamName = '';
echo '<div id="ContactsTabsTeams">';
echo '<h1 class="sr-only">Team Contact Details</h1>';
echo '<ul class="ContactsTabsTeamsScrollH">';
 foreach ($arrUsersTeamdata['Teams'] as $intTeamID => $arrUsersTeam) {
   if ($arrUsersTeam['ShiftLeader'] == 1 || $arrUsersTeam['Scheduler'] == 1 || $arrUsersTeam['Manager'] == 1 || $arrUsersTeam['SystemAdmin'] == 1 || $arrUsersTeam['SchedulingTeamAdmin'] == 1) {
     if ($intFirstTeam == 0) {
       $intFirstTeam = $intTeamID;
       $strFirstTeamName = $arrUsersTeam['schedulingTeamName'];
      
     }
   
     echo '<li data-teamname="'.$arrUsersTeam['schedulingTeamName'].'"><a href="#ContactsTabs'.$intTeamID.'"data-teamname="'.$arrUsersTeam['schedulingTeamName'].'">'.$arrUsersTeam['schedulingTeamName'].'</a></li>';   
   }
 }
echo '</ul>';
foreach ($arrUsersTeamdata['Teams'] as $intTeamID => $arrUsersTeam) {
  if ($arrUsersTeam['ShiftLeader'] == 1 || $arrUsersTeam['Scheduler'] == 1 || $arrUsersTeam['Manager'] == 1 || $arrUsersTeam['SystemAdmin'] == 1 || $arrUsersTeam['SchedulingTeamAdmin'] == 1) { 
    echo '<div id="ContactsTabs'.$intTeamID.'" class = "ContactsTabsBlock">';
    echo '</div>'; 
  }
}
echo '</div>'; 
?>

<script type="text/javascript">
$(document).ready(function(){
  $(function() {
    let jsstrFirstTeamName = '';
    $("#ContactsTabsTeams").tabs({
      create: function( event, ui ) {
        $('#ContactsTabsTeams .ui-tabs-panel').removeAttr('aria-labelledby');
      },
      activate : function( event, ui ) {
        var SelectedTab = $("#ContactsTabsTeams").tabs( "option", "active" );
         jsstrFirstTeamName= $("#ContactsTabsTeams").find('li.ui-tabs-active').data('teamname');
        GetTabContent(SelectedTab);
           
      }
    });
    ShowTeamContacts(<?php echo $intFirstTeam?>,<?php echo "'".$strFirstTeamName."'"?>); 
  });
  

function GetTabContent(SelectedTab) {
switch (SelectedTab) {
<?php
$intCount = 0;
foreach ($arrUsersTeamdata['Teams'] as $intTeamID => $arrUsersTeam) {
  if ($arrUsersTeam['ShiftLeader'] == 1 || $arrUsersTeam['Scheduler'] == 1 || $arrUsersTeam['Manager'] == 1 || $arrUsersTeam['SystemAdmin'] == 1 || $arrUsersTeam['SchedulingTeamAdmin'] == 1)  {
    echo "case ".$intCount.":\n";
    echo "ShowTeamContacts(".$intTeamID.",'".str_replace("'","\'",$arrUsersTeam['schedulingTeamName'])."');\n";
    echo "break;\n";
    $intCount++;
  }
}
?>
}
}

function ShowTeamContacts(teamID,teamName) {
  
  $.post("page-includes/admin/scheduling-team-contacts.php", {
    teamid: teamID,
    teamname:teamName
  },  
  function(data,status){
    $('#ContactsTabs'+teamID).html(data); 
  })
}
  
});

</script>
