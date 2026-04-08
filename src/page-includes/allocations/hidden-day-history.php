<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';
$intTeamId =  $_REQUEST['teamID'];
$strDate =  $_REQUEST['date'];
$commonObj = new classCommonDBFunctions();
$row = $commonObj->getHideDayHistory($intTeamId,$strDate);
echo '<table class="tablesmalltidy" width="600px">';
echo '<tr>';
echo '<th>';
echo "<br>Day Visibliity<br><br>";

echo '</th>';
echo '</tr>';

echo '<tr>';
echo '<td>';
echo $row["History"] ?? '';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td align="center">';
echo '<br><input type="button" value="OK" onclick="cancel()">';
echo '</td>';
echo '</tr>';

echo '</table>';

//echo '<br><br></div>';
?>