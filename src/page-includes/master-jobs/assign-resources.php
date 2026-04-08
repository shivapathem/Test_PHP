<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';

$intID = $_REQUEST["id"];
$intListType = $_REQUEST["listtype"];
$intAreaID = $_SESSION['user']['AreaID'];

$rsJob = GetMasterJobByID ($intID, $intAreaID);
$row = json_decode($rsJob,true);

echo '<div style="width: 600px">';
echo '<table class="bluetable" width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th>';
echo 'Add/Remove Resources from '.$row['Job'].'.<br>';
echo '</th>';

echo '</tr>';
echo '</thead>';
echo '<tbody>';

echo '<tr>';
echo '<td><div id="FillAddMRResourceBody"></div></td>';
echo '</thead>';
echo '</tbody>';
echo '</table>';

?>


<script type="text/javascript">
$(document).ready(function(){ 
  FillAddMRResourceBody(<?=$intID?>);
}) 
function FillAddMRResourceBody(jobid) {
  $.post("page-includes/master-jobs/listresourcesbyjobid.php", {
  jobid: jobid,
  listtype: <?=$intListType?>
  },
  function(data,status){
    $('#FillAddMRResourceBody').html(data);
   }
  )
}


</script>
