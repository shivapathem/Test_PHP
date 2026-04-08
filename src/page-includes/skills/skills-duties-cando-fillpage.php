<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';

$id = $_REQUEST['id'];

if ($id == 0)  {
  echo '&nbsp;';
}
else {

$db = OpenDatabase();

  $assignedprogs = ProgrammesAssigned ($id);
  $arrCanDoDuty = GetStaffCanDoDuty($id);
  


echo '<div class="tableheadersmall medtextboldcentre" style="height:40px; width:805px; position:relative">';
echo '<br>Skills and Staff...';
echo '</div>';


echo '<table class="tablesmall">';
echo '<tr height="40px">'; 
echo '<th width="400px">';
echo 'Skills';
echo '</th>';
echo '<th width="400px">';
echo 'Staff Who Can Do This Duty';
echo '</th>';
echo '</tr>';
echo '<tr>';

echo '<td valign="top">';
echo '<div id="sdassignedprogsdiv" class="scrolldiv400">';

if (isset($assignedprogs)) {
  echo '<table class="tablesmall stripe" width="100%">';
  foreach($assignedprogs as $progid => $details) {
    echo '<tr>';
    echo '<td>';
    echo $details;
    echo '</td>';
    echo '</tr>';
  }
  echo '</table>';
}
echo '</div>';
echo '</td>';

echo '<td valign="top">';
echo '<div id="sdassignedstaffdiv" class="scrolldiv400">';
echo '<table class="tablesmall stripe" width="100%">';
if (isset($arrCanDoDuty)) {
  foreach ($arrCanDoDuty as $intStaffID => $strFullName) {
    echo '<tr>';
    echo '<td>';
    echo $strFullName;
    echo '</td>';
    echo '</tr>';
  }
}
echo '</table>';
echo '</td>';
echo '</div>';
echo '</tr>';
echo '</table>';

}

?>

<script type="text/javascript">
$(document).ready(function(){
  $("table.stripe tr:odd").addClass("odd");
  $("table.stripe tr:even").addClass("even");

	if ( $.cookie("sdassignedprogsdiv") !== null ) {
    $("#sdassignedprogsdiv").scrollTop( $.cookie("sdassignedprogsdiv") );
  }  
  $("#sdassignedprogsdiv").on("scroll", function() {
    $.cookie("sdassignedprogsdiv", $("#sdassignedprogsdiv").scrollTop() );
  });  
  
	if ( $.cookie("sdassignedstaffdiv") !== null ) { 
    $("#sdassignedstaffdiv").scrollTop( $.cookie("sdassignedstaffdiv") );
  }  
  $("#sdassignedstaffdiv").on("scroll", function() {
    $.cookie("sdassignedstaffdiv", $("#sdassignedstaffdiv").scrollTop() );
  });    
  
})


</script>