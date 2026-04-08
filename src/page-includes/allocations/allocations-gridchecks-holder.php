<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
if ($arrUsersTeamdata["isShiftLeader"] == 1) {
  if (isset($_POST['days'])) {
    $intDays = $_POST['days'];
    $intSchedulingTeamID = $_POST['team'];
    
    
    
    if ($intDays != -1) {
      $strMiddleDate = date("Y-m-d", strtotime("+$intDays days"));
     }
     else {
       $strMiddleDate = $_POST['date'];
     }
  }
  else {
     $strMiddleDate = date("Y-m-d", strtotime("+21 days"));
  }

  echo'<div id="tabs" style="width: 1375px">';
  echo'<ul>';
  echo'<li><a href="page-includes/allocations/allocations-grid-checks.php?team='.$intSchedulingTeamID.'&date='.$strMiddleDate.'">Turnaround Checks</a></li>';
  echo'<li><a href="#tabs-1">Monthly View</a></li>';
  echo'</ul>';
  echo'<div id="tabs-1">';
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 800px">';
  echo '<br>This Tab will be filled once you have clicked on a Person<br><br>';
  echo '</div>';
  echo '</div>';

  echo '<br><br><br><br>';
} else {
  echo 'Access Denied'; die;
}
?>
<script type="text/javascript">

$(function() {
  $( "#tabs" ).tabs({
    beforeLoad: function( event, ui ) {
      ui.jqXHR.fail(function() {
      ui.panel.html(
        "Couldn't load this tab. We'll try to fix this as soon as possible.");
      }
      );
    }
  });
});
$("#tabs .ui-tabs-panel").removeAttr("aria-labelledby");
$('[title]').qtip({
  style: { classes: 'qtip-rounded qtip-shadow qtip-dark'}
});
$('.qtip').click(function(event) {
    api.toggle(false);
})

function showrotatab(date, schedulingPersonId,teamID) {
  $.ajax({
  type: 'POST',
  url: 'page-includes/allocations/allocations-monthly.php',
  data: {
  'date': date,
  'schedulingPersonId': schedulingPersonId,
  'teamId': teamID,
  'althead':1
  },
  success: function (data) {
    $('#tabs-1').html(data);
  },
  error:function (data) {
    customAlert('some error found in allocations monthly call.');
  }
  });
  $( "#tabs" ).tabs( "option", "active", 1 );
}
</script>