<?php
include_once '../function-includes/init.php';
$date = date("Y-m-d");
$arrAttachments = GetStaffOnAttachment();

// London Staff
echo '<table class="redtable">';
echo '<tr>';
echo '<th colspan="13"><br>Staff On Attachment (London)<br><br></th>';
echo '</tr>';
echo '<tr>';
echo '<td width="50px">&nbsp;</td>';
for ($i = 0; $i <12; $i++) {
    echo '<td align="center" width="50px">'.date("M", strtotime($date)).'</td>';
    $date = date("Y-m-d", strtotime("+1 month", strtotime($date)));
}
echo '</tr>';

echo '<tr>';
echo '<td width="50px">Internal</td>';
for ($i = 0; $i <12; $i++) {
  if (isset($arrAttachments[$i][$departmentID]["1"])) {
    $intCount = $arrAttachments[$i][$departmentID]["1"];
  }
  else {
    $intCount = 0;
  }
  echo '<td align="center">'.$intCount.'</td>';

}
echo '</tr>';

echo '<tr>';
echo '<td width="50px">External</td>';
for ($i = 0; $i <12; $i++) {
  if (isset($arrAttachments[$i][$departmentID]["2"])) {
    $intCount = $arrAttachments[$i][$departmentID]["2"];
  }
  else {
    $intCount = 0;
  }
  
   
  if ($intCount >= 6) {
    echo '<td align="center" bgcolor="#cc0000"><font color="#FFFFFF">'.$intCount.'</font></td>';
  }
  else{
    echo '<td align="center">'.$intCount.'</td>';
  }

}
echo '</tr>';
echo '</table>';

// Get the people and show the photos....
ShowPhotos($departmentID);
// End London Staff

?>