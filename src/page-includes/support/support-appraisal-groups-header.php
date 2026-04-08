<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$intCellWidth = 250;
$intAppraiserHeight = 22;
$intAppraiseesHeight = 175;
$intTop = 55;
$db = OpenDatabase();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];


$arrDepartmentOprions = GetStaffOtionsByDepartment($strUser);
echo '<div style="width:90%; margin:0 auto; position:relative;">';
echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
echo '<br>Appraisal Groups<br><br>';
echo '</div><br>';

echo'<div id="appraisaltabs" style="width:100%">';
echo'<ul>';
// The default...
foreach ($arrDepartmentOprions as $intDepID => $arrDepartment) { 
  if ($arrDepartment['isDefault'] == 1) {
    echo'<li><a href="page-includes/support/support-appraisal-groups.php?depid='.$intDepID.'">'.$arrDepartment['DepartmentName'].'</a></li>';  
  }
}
// The others...
foreach ($arrDepartmentOprions as $intDepID => $arrDepartment) { 
  if ($arrDepartment['isDefault'] == 0) {
    echo'<li><a href="page-includes/support/support-appraisal-groups.php?depid='.$intDepID.'">'.$arrDepartment['DepartmentName'].'</a></li>';  
  }
}


echo '</ul>';
echo '</div>';


$intFirstKey = array_keys($arrDepartmentOprions)[0];

?>

<script type="text/javascript">

$(document).ready(function() {
  var tabCookieName = "accordion-app-groups"; 
  $( function() {
    $( "#appraisaltabs" ).tabs({
      beforeLoad: function( event, ui ) {
        ui.jqXHR.fail(function() {
          ui.panel.html(
            "Couldn't load this tab. We'll try to fix this as soon as possible. " +
            "If this wouldn't be a demo." );
        });
      }
    });
  } );  
});

</script>       