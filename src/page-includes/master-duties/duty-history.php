<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';

//Check User Authentication
$pageid = 4;
$perms = $_SESSION['areaperms'];
for($ArraySeq = 0; $ArraySeq < (count($perms) - 1); $ArraySeq++){
  if($perms[$ArraySeq]["formid"] == $pageid){
    $canview = $perms[$ArraySeq]["isview"];
    $canmodify = $perms[$ArraySeq]["ismodify"];
    $canviewextended = $perms[$ArraySeq]["isviewextended"];
    $canmodifyextended = $perms[$ArraySeq]["ismodifyextended"];
    $candelete = $perms[$ArraySeq]["isdelete"];
  }
}

$intID = $_REQUEST["id"];

if ($canview == 1) {
  $rsHistory = GetDutyHistoryByID($intID);
  $row = json_decode($rsHistory,true);
  

  echo '<div style="width: 600px">';
  echo '<form id="dutyhistory">';
  echo '<table id="dutynewedittable" class="smalltable bluetable" width="100%">';
  echo '<thead>';
  echo '<tr>';
  echo '<th >Duty History</th>';

  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  echo '<tr>';
  echo '<td>';
  echo '<textarea rows="12" name="DutyHistory" cols="82">'.$row['History'].'</textarea>';
  echo '</td>';
  echo '</tr>';
											    
  echo '</tbody>';
  echo '</table>';
  echo '<input type="hidden" name="id" value="'.$intID.'">';

  echo '</form>';  
  echo '</div>';
}

?>
