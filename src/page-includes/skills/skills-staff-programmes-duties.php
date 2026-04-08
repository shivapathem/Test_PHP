<?php
session_start();
$intDepartmentID = $_REQUEST['department'];

if (isset($_SESSION['bst'])) {
  $bst = $_SESSION['bst'];
}
else {
  $bst = date("I");
  $_SESSION['bst'] = $bst;
}


if (isset($_REQUEST['readonly'])) {
  $intReadOnly = $_REQUEST['readonly'];
  if ($intReadOnly == 0) {
    $intTab = 3;
  }
  else {
  $intTab = 1;
  }
}
else {
  $intTab = 3;
}

echo '<div class="tableheadersmall medtextboldcentre" width="550px">';
echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
echo '<tr>';
echo '<td>Staff, Skills and Duties</td>';
echo '<td rowspan="2" align="right">';
echo '<img border="0" src="images/clock.png" width="32" height="32" onclick="javascript:ToggleTimeZone('.$intDepartmentID.', '.$intTab.');" class="handcursor" title="Clck here to change timezone"></td>';
echo '</tr>';
echo '<tr>';
echo '<td>';
echo 'You are Currently Viewing Duties in <font color="#990000">';
if ($bst == 0) {
  echo "GMT";
}
else {
  echo "BST";
}
echo '</font>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';
echo '<br>';

echo '<table class="tablesmall" width="100%">';
echo '<tr>';

echo '<td valign="top" width="300px">';
echo '<div id="stafflist-'.$intDepartmentID.'" class="scrolldiv">';
echo '</div>';
echo '</td>';

echo '<td valign="top">';
echo '<div id="staffcandolist-'.$intDepartmentID.'">';
echo '<div class="tableheadersmall medtextboldcentre" style="width:50%">';
echo '<br>Choose a person from the list<br><br>';
echo '</div>';


echo '</div>';
echo '</td>';

echo '</tr>';
echo '</table>';

?>
<script type="text/javascript">
$(document).ready(function(){
  FillStaffList(<?php echo $intDepartmentID?>);
  
})

function FillStaffList (department) {
  $.post("page-includes/skills/skills-fill-stafflist-list.php", {
    department: department
  },
    function(data,status){
      $('#stafflist-<?php echo $intDepartmentID?>').html(data);
    }
  )
}

function ShowProgsCanDo (staffnumber) {
  $.post("page-includes/skills/skills-staff-progs-cando-fillpage.php", {
    staffnumber: staffnumber,
    department: <?php echo $intDepartmentID?>
  },
    function(data,status){
      $('#staffcandolist-<?php echo $intDepartmentID?>').html(data);
    }
  )
}


</script>