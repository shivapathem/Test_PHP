<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$intGroupID = $_REQUEST['id'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
 
if(isset( $_SESSION['user']['SysAdmin'] ) && $_SESSION['user']['SysAdmin'] == 1){
  $intSysAdmin = 1;
}else{
  $intSysAdmin = 0;
}

$strGroupDescription = GetLeaveRequestDescFromID($intGroupID);
$intAdminLevel = GetAdminLeaveRequestGroupsAdminFromLogin ($strUser, $intGroupID);  
  
// Holder for clicking on the tab when no group is selected
echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
echo '<br><h1 style="text-align:center;">Leave Admin for '.$strGroupDescription.'</h1>';
if ($intAdminLevel != 2) {
  echo '&nbsp;&circledR;';
}

echo '<br><br>';
echo '</div>';

echo'<div id="admingroupleavetabs" style="width:100%">';
echo'<ul>';
echo'<li><a href="#admingroupleavetabs-1">Leave Types Config</a></li>';
echo'<li><a href="#admingroupleavetabs-2">Guaranteed Leave Availability</a></li>';
echo'<li><a href="#admingroupleavetabs-3">Summer Leave</a></li>';
echo'</ul>';

echo'<div id="admingroupleavetabs-1">';
echo '</div>';
echo'<div id="admingroupleavetabs-2">';
echo '</div>';
echo'<div id="admingroupleavetabs-3">';
echo '</div>';
echo'</div>';
?>

<script type="text/javascript">
showleavetypes(<?php echo $intGroupID?>);

$(function() {
  $( "#admingroupleavetabs" ).tabs({
    activate : function( event, ui ) {
      var active = $( "#admingroupleavetabs" ).tabs( "option", "active" );
      switch (active) {
        case 0:
          showleavetypes(<?php echo $intGroupID?>);
          break;
        case 1:
          ShowLeaveAvalability(<?php echo $intGroupID?>)
          break;
        case 2:
          ShowSummerLeave(<?php echo $intGroupID?>)
          break;
      }
    }
  });
});

function showleavetypes (leavegroup) {
  $.post("page-includes/admin/leavetypes.php", {
    leavegroup: leavegroup
  },
  function(data,status){
    $('#admingroupleavetabs-1').html(data);
   }
  )
}

</script>