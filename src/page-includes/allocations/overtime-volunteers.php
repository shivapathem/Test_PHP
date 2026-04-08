<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$teamId = $_REQUEST['teamId'];
$ddate = $_REQUEST['ddate'];
//get overtime volunteers
$row = getOvertimeVolunteers($ddate, $teamId);

echo '<table class="tablesmalltidy" width="600px">';
echo '<tr height="30px">';
echo '<th  colspan="2">Overtime Offers</th>';
echo '</tr>';
echo '<tr height="30px">';
echo '<th width="175px">Name</th>';
echo '<th>Comments</th>';
echo '</tr>';
foreach($row as $redp) {
  echo '<tr>';
  echo '<td valign="top">';
  echo $redp['FullName'];
  echo '</td>';
  echo '<td valign="top">';
  echo $redp['comments'];
  echo '</td>';
  echo '</tr>';
}
echo '</table>';
?>
<p align="center"><input type="button" value="Close" onclick="cancel()"></p>