<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intGroupID = $_REQUEST['id'];

$strGroupDescription = GetLeaveRequestDescFromID($intGroupID);
$intAdminLevel = GetAdminLeaveRequestGroupsAdminFromLogin ($strUser, $intGroupID);  


// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
echo '<br><h1 style="text-align:center;">Requests Admin for '.$strGroupDescription.'</h1>';
if ($intAdminLevel != 2) {
  echo '&nbsp;&circledR;';
}
echo  '<br>';
echo '</div>';

echo'<div id="admingrouprequesttabs" style="width:100%">';
echo'<ul>';
echo'<li><a href="#admingrouprequesttabs-1">Request Types Config</a></li>';
echo'<li><a href="#admingrouprequesttabs-2">Requests Closed</a></li>';
echo'</ul>';

echo'<div id="admingrouprequesttabs-1">';
echo '</div>';
echo'<div id="admingrouprequesttabs-2">';
echo '</div>';
echo'</div>';
?>

<script type="text/javascript">
ShowRequestTypes(<?php echo $intGroupID?>);

$(function() {
  $( "#admingrouprequesttabs" ).tabs({
    activate : function( event, ui ) {
      var active = $( "#admingrouprequesttabs" ).tabs( "option", "active" );
      switch (active) {
        case 0:
          ShowRequestTypes(<?php echo $intGroupID?>);
          break;
        case 1:
          ShowRequestsClosed(<?php echo $intGroupID?>)
          break;

      }
    }
  });
});

</script>