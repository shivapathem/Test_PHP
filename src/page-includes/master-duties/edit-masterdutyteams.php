<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';

$intDutyID = $_REQUEST["dutyid"];
$intDutyType = $_REQUEST["dutytypeid"];
$intStaffID = $_SESSION['user']['StaffID'];
$intAreaID = $_SESSION['user']['AreaID'];

$intIsRota = $_SESSION['isrota'];
if (!(isset($intIsRota))) {
  $intIsRota = 1;
  $_SESSION['isrota'] = 1;
}
$intType = 0;

//Check User Authentication
if ($intIsRota == 1) {
    $pageid = 6;
    $perms = $_SESSION['areaperms'];
    for($ArraySeq = 0; $ArraySeq < count($perms); $ArraySeq++){
        if($perms[$ArraySeq]["formid"] == $pageid){
            $canview = $perms[$ArraySeq]["isview"];
            $canmodify = 0;
            $canviewextended = $perms[$ArraySeq]["isviewextended"];
            $canmodifyextended = 0;
            $candelete = 0;
        }
    }
}
else {
    $pageid = 4;
    $perms = $_SESSION['areaperms'];
    for($ArraySeq = 0; $ArraySeq < count($perms); $ArraySeq++){
        if($perms[$ArraySeq]["formid"] == $pageid){
            $canview = $perms[$ArraySeq]["isview"];
            $canmodify = $perms[$ArraySeq]["ismodify"];;
            $canviewextended = $perms[$ArraySeq]["isviewextended"];
            $canmodifyextended = $perms[$ArraySeq]["ismodifyextended"];;
            $candelete = $perms[$ArraySeq]["isdelete"];;
        }
    }
}

    
echo '<div id="masterdutyteams-body" style="width: 600px">';
echo '</div>';


?>

<script type="text/javascript">
$(document).ready(function(){
  if (<?php echo empty($canview) ? 0:$canview ?> == 0) {
    customAlert('You do not have view privileges for this form.');
  }
  else {
    FillMasterDutyTeamBody(<?=$intDutyID?>, <?=$intDutyType?>);
  }
})

function FillMasterDutyTeamBody(dutyid, dutytypeid) {
  $.post("page-includes/master-duties/edit-masterdutyteamdetails.php", {
  dutyid: dutyid,
  dutytypeid: dutytypeid
  },
  function(data,status){
    $('#masterdutyteams-body').html(data);
   }
  )
}
</script>

