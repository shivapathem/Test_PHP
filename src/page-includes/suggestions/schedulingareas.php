<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$intSuggestionType = 3;
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$arrAdminDepartments = GetAdminDepartmentsByLogin ($strUser);
$intSysAdmin = GetIsSysAdmin ($strUser);

if (count($arrAdminDepartments) >= 0 || $intSysAdmin == 1) {
  $intCanEdit = 1;
}
else {
  $intCanEdit = 0;
}

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
echo '<br>Scheduling Areas.<br><br>';


if ($intCanEdit == 1) {
  echo '<div class="DutyCellBottomLeft" onclick="javascript:EditSchedArea(0, '.$intSuggestionType.')";>';
  echo '<table>';
  echo '<tr>';
  echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
  echo '<td>';
  echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';
}
echo '</div><br>';

echo'<div id="shedareas" style="width:100%">';
echo'</div>';


?>


