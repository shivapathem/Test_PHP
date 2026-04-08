<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';
$intJobID = $_REQUEST['jobid'];
$intListType = $_REQUEST["listtype"];
$rsResources = json_decode($rsResourcesJson,true);
if(!empty($rsResources)) {
    $rowcount = count($rsResources);
}
else{
    $rowcount = 1;
}
for ($row = 0; $row < $rowcount; $row++) {
    if (!is_null($rsResources[$row]['ResourceID'])) {
        $arrResourcesIn[$rsResources[$row]['ResourceID']] = $rsResources[$row]['ResourceName'];
    }
}

echo '<table id="mjobsandprogs"  width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th>Available</th>';
echo '<th>Assigned</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
echo'<tr>';
echo'<td width="50%">';
// A able with the bases the user is NOT in
echo '<table width="100%" class="progsnotin">';

    echo'<tr>';
    echo'<td>';
    echo 'Search for a programme';
    echo'</td>';
    echo'</tr>';
    echo'<tr>';
    echo'<td>';
    echo '<input id="progname" size="30">';
    echo'</td>';
    echo'</tr>';
echo'</table>';
echo'</td>';
echo'<td width="50%">';

if (isset($arrProgsIn)) {
  echo '<table width="100%" class="progsin handcursor">';
  foreach ($arrProgsIn as $intProgID => $strProgName) {
    echo'<tr>';
    echo'<td onclick="javascript:RemoveProg('.$intProgID.')";>';
    echo $strProgName;
    echo'</td>';
    echo'</tr>';
  }
  echo'</table>';
}
echo'</td>';
echo'</tr>';
echo'</table>';
?>
<script type="text/javascript">
$(document).ready(function(){
  $("table.progsin tr:odd").addClass("odd");
  $("table.progsin tr:even").addClass("even");
});

$(function() {
  $( "#progname" ).autocomplete({
    select: function( event, ui ) {
      $.post("page-includes/master-jobs/addprogtojob.php", {
        ProgID:  ui.item.id,
        JobID: <?=$intJobID?>
      },
      function(data,status){
        FillAddMJProgBody(<?=$intJobID?>);
        ListJobs(<?=$intJobID?>, <?=$intListType?>);
      });
    }
  });
});

function RemoveProg (ProgID) {
  $.post("page-includes/master-jobs/removeprogfromjob.php", {
        ProgID:  ProgID,
        JobID: <?=$intJobID?>
  },
  function(data,status){
    FillAddMJProgBody(<?=$intJobID?>);
    ListJobs(<?=$intJobID?>, <?=$intListType?>);
  }
  )
}
</script>