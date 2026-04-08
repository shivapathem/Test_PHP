<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';

$id = $_REQUEST['id'];
$intDepartmentID = $_REQUEST['department'];
if ($id == 0)  {
  echo '&nbsp;';

}
else {

$db = OpenDatabase();

$assignedprogs = ProgrammesAssigned ($id);  
$allprogs = ListAllProgrammes($intDepartmentID);

echo '<div class="tableheadersmall medtextboldcentre" style="width:810px; position:relative">';
echo '<br>Skills Availability...<br><br>';
echo '</div>';

echo '<table class="tablesmall">';   
echo '<tr height="40px">';  
echo '<th width="400px">';  
echo 'Skills Assigned';
echo '</th>';
echo '<th width="400px">';  
echo 'Available Skills'; 
echo '</th>'; 
echo '</tr>';
echo '<tr>';

echo '<td valign="top">';
if (isset($assignedprogs)) {
  echo '<div id="assignedprogsdiv" class="scrolldiv400">';
  echo '<table width="100%" class="stripe">';
  foreach($assignedprogs as $progid => $details) {
    echo '<tr>';
    echo '<td class="handcursor" onclick="javascript:RemoveProgFromDuty('.$progid.','.$id.')";>';
    echo $details;
    echo '</td>';  
    echo '</tr>';
  }
  echo '</table>';
  echo '</div>';
}
echo '</td>';

echo '<td valign="top">';
if (isset($allprogs)) {
  echo '<div id="unassignedprogsdiv" class="scrolldiv400">';
  echo '<table width="100%" class="stripe">';
  foreach($allprogs as $progid => $details) {
    if (!isset($assignedprogs[$progid])) {
      echo '<tr>';
      echo '<td class="handcursor" onclick="javascript:AddProgToDuty('.$progid.','.$id.')";>';
      echo $details;
      echo '</td>';  
      echo '</tr>';
    }
  }
  echo '</table>';
  echo '</div>';
}

echo '</td>';


echo '</tr>';
echo '</table>';

}
  
?>

<script type="text/javascript">
$(document).ready(function(){
  $("table.stripe tr:odd").addClass("odd");
  $("table.stripe tr:even").addClass("even");
  
	if ( $.cookie("assignedprogsdiv") !== null ) {
    $("#assignedprogsdiv").scrollTop( $.cookie("assignedprogsdiv") );
  }  
  $("#assignedprogsdiv").on("scroll", function() {
    $.cookie("assignedprogsdiv", $("#assignedprogsdiv").scrollTop() );
  });  
  
	if ( $.cookie("unassignedprogsdiv") !== null ) {
    $("#unassignedprogsdiv").scrollTop( $.cookie("unassignedprogsdiv") );
  }  
  $("#unassignedprogsdiv").on("scroll", function() {
    $.cookie("unassignedprogsdiv", $("#unassignedprogsdiv").scrollTop() );
  });   
  
    
})


</script>