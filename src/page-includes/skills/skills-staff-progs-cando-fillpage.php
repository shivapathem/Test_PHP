<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/skillsfunctions.php';



if (isset($_SESSION['bst'])) {
  $bst = $_SESSION['bst'];
}
else {
  $bst = date("I");
  $_SESSION['bst'] = $bst;
}

$intDepartmentID = $_REQUEST['department'];
$strStaffNumber = $_REQUEST['staffnumber'];

if ($strStaffNumber == 0)  {
  echo '&nbsp;';
}
else {

  $db = OpenDatabase();
  // Get all the programmes
  $arrAllProgs = ListAllProgrammes($intDepartmentID);
  // Now get the people who can do this duty
  $arrprogscando = GetStaffProgsCanDo($strStaffNumber);
  // Now get all the duties .....
  // It's possible that they can do duties in other bases
  $arrallduties = listalldutieswithprogrammes($bst, $intDepartmentID);

  echo '<table class="tablesmall">';
  echo '<tr height="30px">';
  echo '<th width="250px">';
  echo 'Skills Can Do';
  echo '</th>';
  echo '<th width="250px">';
  echo 'Skills Can\'t Do';
  echo '</th>';
  echo '<th width="250px">';
  echo 'Duties Can Do';
  echo '</th>';
  echo '<th width="250px">';
  echo 'Duties Can\'t Do';
  echo '</th>';
  echo '</tr>';
  echo '<tr>';

  echo '<td valign="top">';
  if (isset($arrprogscando)) {
    echo '<div class="scrolldiv250">';
    echo '<table width="100%" class="stripe">';
    foreach($arrprogscando as $thisprog) {
      echo '<tr>';
      echo '<td>';
      echo $thisprog;
      echo '</td>';
      echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
  }
  else {
    echo 'No Skills Defined';

  }
  echo '</td>';

    echo '<td valign="top">';
  if (isset($arrAllProgs)) {
    echo '<div class="scrolldiv300">';
    echo '<table width="100%" class="stripe">';
    foreach($arrAllProgs as $progid => $thisprog) {
      if (!isset($arrprogscando[$progid])) {

      echo '<tr>';
      echo '<td>';
      echo $thisprog;
      echo '</td>';
      echo '</tr>';
      }
    }
    echo '</div>';
    echo '</table>';
  }
  else {
    echo 'No Skills Defined';

  }
  echo '</td>';

  echo '<td valign="top">';
  if (isset($arrallduties)) {
    // The duties they can do
    echo '<div class="scrolldiv300">';    
    echo '<table width="100%" class="stripe">';
    foreach($arrallduties as $dutyid => $thisduty) {
      $nodo = 0;
      foreach($thisduty['progs'] as $progid => $prog) {
        if (!isset($arrprogscando[$progid])) {
          $nodo = 1;
        }
      }
      if ($nodo == 0) {
        echo '<tr>';
        echo '<td>';
        echo $thisduty['dutyname'];
        echo '</td>';
        echo '</tr>';
        // Remove it from the array
        unset ($arrallduties[$dutyid]);
      }
    }
    echo '</table>';
    echo '</div>';
   }
   else {
     echo 'No duties defined';
   }
  echo '</td>';


  echo '<td valign="top">';
  if (isset($arrallduties)) {
    // The duties they can't do
    echo '<div class="scrolldiv300">';
    echo '<table width="100%" class="stripe">';
    foreach($arrallduties as $dutyid => $thisduty) {
      echo '<tr>';
      echo '<td>';
      echo $thisduty['dutyname'];
      echo '</td>';
      echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
  }
  else {
    echo 'No duties defined';
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
})

function AddProg (progid, dutyid) {
  $.post("page-includes/ajax-calls/skills-add-prog-to-duty.php", {
     dutyid: dutyid,
     progid: progid
  },
  function(data,status){
    FillDutiesPage(dutyid)
  }
  )
}


function RemoveProg (progid, dutyid) {
  $.post("page-includes/ajax-calls/skills-remove-prog-from-duty.php", {
     dutyid: dutyid,
     progid: progid
  },
  function(data,status){
    FillDutiesPage(dutyid)
  }
  )
}
</script>