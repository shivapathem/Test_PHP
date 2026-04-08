<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/common/classCommonDBFunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$commonObj = new classCommonDBFunctions();
$arrUserSettings = json_decode($commonObj->userLeaveRequestByNetLogin($strUser, 0),true);
if (!empty($arrUserSettings) && isset($arrUserSettings['LeaveRequests'])) {
  foreach ($arrUserSettings['LeaveRequests'] as $intGroupID => $arrGroup) {
    if ($arrGroup['Admin'] >= 1) {
      $arrGroupsCanRequest[$intGroupID] = $arrGroup;
    }
  }
}
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h1 style="text-align: center;">Requests Administration</h1><br>Click on a row to Highlight it and then choose an option from the Tabs.<br><br>';
echo '</div>';

if (!isset($arrGroupsCanRequest)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Requests groups defined!<br>Please contact your Scheduling Team Admin to get these assigned.<br><br>';
  echo '</div>';
  die;
} else {

    echo '  <div class="requestsadmintabs">';
    echo '    <ul>';
    echo '      <li><a href="#requestsadmintabs-0">Guaranteed</a></li>';
    echo '      <li><a href="#requestsadmintabs-1">Waiting List</a></li>';
    echo '      <li><a href="#requestsadmintabs-2">Unlikely List</a></li>';
    echo '      <li><a href="#requestsadmintabs-3">Weekly Requests</a></li>';
    echo '    </ul>';
    echo '    <div id="requestsadmintabs-0">';
    echo '    </div>';
    echo '    <div id="requestsadmintabs-1">';
    echo '    </div>';
    echo '    <div id="requestsadmintabs-2">';
    echo '    </div>';
    echo '    <div id="requestsadmintabs-3">';
    echo '    </div>';        
    echo '  </div>';
}
$intFirstKey = array_keys($arrGroupsCanRequest)[0];
?>
<script type="text/javascript">
$(document).ready(function() {
  $(function() {
    $(".requestsadmintabs").tabs({
    heightStyle: 'content', 
      disabled: [3],
      activate : function( event, ui ) {
        var SelectedTab = $(".requestsadmintabs").tabs( "option", "active" );
          GetTabContent(SelectedTab);
      }      
    });
  }); 
GetTabContent (0); 
});

function GetTabContent (SelectedTab) {
  if (SelectedTab >= 0 && SelectedTab <= 2) { 
    $.post("page-includes/requests/requests-admin-needingapproval.php", {
      option: SelectedTab
    },  
    function(data,status){
      $('#requestsadmintabs-'+SelectedTab).html(data); 
    })
  }
}

  function ShowWeeklyRequestsAdmin (Date, Group) {
    $( ".requestsadmintabs" ).tabs( "enable", 3 );
    $.post("page-includes/requests/requests-weekly.php", {
      date: Date,
      group: Group,
      admin: 1
    },
    function(data,status){
      $('#requestsadmintabs-3').html(data);
     }
    ) 
    $( ".requestsadmintabs" ).tabs( "option", "active", 3 );
  } 
  
  function ShowRequestWeeklyAdminByDate (Date, Group) {
    $( ".requestsadmintabs" ).tabs( "enable", 3 );
    $.post("page-includes/requests/requests-weekly.php", {
      date: Date,
      group: Group,
      admin: 1
    },
    function(data,status){
      $('#requestsadmintabs-3').html(data);
     }
    )    
    $( ".requestsadmintabs" ).tabs( "option", "active", 3 );
  }  
</script>


