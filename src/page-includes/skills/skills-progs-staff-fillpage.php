<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';

$id = $_REQUEST['id'];
$intDepartmentID = $_REQUEST['department'];

if (isset($_REQUEST['readonly'])) {
  $intReadOnly = $_REQUEST['readonly'];
}
else {
  $intReadOnly = 0;
}

if ($id == 0)  {
  echo '&nbsp;';
}
  else {

  $db = OpenDatabase();

  $trainedstaff = StaffWhoCanDoProg ($id);
  $allstaff = ListAllStaff($intDepartmentID);
echo '<div class="tableheadersmall medtextboldcentre" style="width:810px; position:relative">';
echo '<br>Staff...<br><br>';
echo '</div>';
echo '<table class="tablesmall staff">';
echo '<tr height="40px">';
echo '<th width="400px">';
echo 'Staff with Skill Assigned';
echo '</td>';
echo '<th width="400px">';
echo 'Staff without Skill Assigned';
echo '</td>';
echo '</tr>';
echo '<tr>';

echo '<td valign="top">';
echo '<div id="trainedstaffdiv" class="scrolldiv400">';
if (isset($trainedstaff)) {
  echo '<table width="100%">';
  foreach($trainedstaff as $staffid => $details) {
    echo '<tr>';
    if ($intReadOnly == 0) {
      echo '<td class="handcursor" onclick="javascript:RemovePerson('.$staffid.','.$id.', '.$intDepartmentID.')";>';
    }
    else {
      echo '<td class="handcursor";>';
    }
    echo $details['name'];
    echo '</td>';
    echo '</tr>';
  }
  echo '</table>';
  
}
echo '</td>';
echo '</div>';
echo '<td valign="top" colspan="3">';
echo '<div id="allstaffdiv" class="scrolldiv400">';
if (isset($allstaff)) {
  echo '<table width="100%">';
  foreach($allstaff as $staffid => $details) {
    if (!isset($trainedstaff[$staffid])) {
      echo '<tr>';
      if ($intReadOnly == 0) {
        echo '<td class="handcursor" onclick="javascript:AddPerson('.$staffid.','.$id.', '.$intDepartmentID.')";>';
      }
      else {
        echo '<td class="handcursor">';
      }
      echo $details['name'];
      echo '</td>';
      echo '</div>';
      echo '</tr>';
    }
  }
  echo '</table>';
}
else {
  echo 'No Staff Found!';

}
echo '</td>';


echo '</tr>';
echo '</table>';
}

?>

<script type="text/javascript">
$(document).ready(function(){
  $("table.staff tr:odd").addClass("odd");
  $("table.staff tr:even").addClass("even");
  
	if ( $.cookie("allstaffdiv") !== null ) {
    $("#allstaffdiv").scrollTop( $.cookie("allstaffdiv") );
  }  
  $("#allstaffdiv").on("scroll", function() {
    $.cookie("allstaffdiv", $("#allstaffdiv").scrollTop() );
  });  
  
	if ( $.cookie("trainedstaffdiv") !== null ) {
    $("#trainedstaffdiv").scrollTop( $.cookie("trainedstaffdiv") );
  }  
  $("#allstaffdiv").on("scroll", function() {
    $.cookie("trainedstaffdiv", $("#trainedstaffdiv").scrollTop() );
  }); 
  



  
  
})

function AddPerson (staffid, progid, department) {
  $.post("page-includes/skills/skills-add-person-to-programme.php", {
     staffid: staffid,
     progid: progid
  },
  function(data,status){
    FillProgsStaffPage(progid, department)
  }
  )
}

function RemovePerson (staffid, progid, department) {
  $.post("page-includes/skills/skills-remove-user-from-programme.php", {
     staffid: staffid,
     progid: progid
  },
  function(data,status){
    FillProgsStaffPage(progid, department)
  }
  )
}
</script>