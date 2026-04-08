<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once '../users/process/classUserSetup.php';
$setupObj = new classUserSetup();
$arrUserSettings = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);

foreach ($arrUserSettings['Teams'] as $intDepartmentID => $arrDep) {
  if ($arrDep['SkillsAdmin'] == 1 || $arrDep['Scheduler'] == 1) {
    $arrDeptsCanRequest[$intDepartmentID] = $arrDep;
  }
}
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
echo '<br><h1 style="text-align: center;">Allocate Helper.</h1><br>';
echo '</div>';

if (!isset($arrDeptsCanRequest)) {
  // No request groups defined
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>You do not have any Leave groups defined!<br>Please contact your Team administrator to get these assigned.<br><br>';
  echo '</div>';
  die;
} else {
  echo '<div id="accordion">';
  foreach ($arrDeptsCanRequest as $intDepID => $arrDep) {
    echo '<h2 id="' . $intDepID . '">';
    echo $arrDep['schedulingTeamName'];
    echo '</h2>';
    echo '<div id="Dep' . $intDepID . '">';
    echo '</div>';
  }
  echo '</div>';
}
echo '<br><br>';
$intFirstKey = array_keys($arrDeptsCanRequest)[0];

?>
<script type="text/javascript">
  $(document).ready(function() {
    $(function() {
      $("#accordion").accordion({
        heightStyle: "content",
        collapsible: true,
        activate: function(event, ui) {
          var depid = ($('.ui-accordion-header-active').attr('id'));
          ShowHelper(depid);
        }
      });
    });
    <?php
    foreach ($arrDeptsCanRequest as $intDepID => $strDepName) {
      echo 'ShowHelper(' . $intDepID . ');';
    }
    ?>

  });

  function ShowHelper(teamid) {
    $.post("page-includes/allocate-helper/allocate-helper.php", {
        SchedulingteamId: teamid,
      },
      function(data, status) {
        $('#Dep' + teamid).html(data);
      })
  }
</script>