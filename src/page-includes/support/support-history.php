<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';


$strStaffLogin = $_REQUEST['login'];  
  
$db = OpenDatabase();

$strQuery = "SELECT       History
             FROM         staff_status
             WHERE        (Login = N'$strStaffLogin')";


$rsUser = sqlsrv_query($db, $strQuery);
$row = sqlsrv_fetch_array($rsUser);

echo '<table class="tablesmalltidy" width="800px">';
echo '<tr>';
echo '<th>';
echo '<br>History<br><br>';
echo '</th>';
echo '</tr>';

echo '<tr>';
echo '<td>';
if (is_null($row['History'])) {
  echo 'There is no History for this user yet!';
}
else {
  echo $row['History'];
}
echo '</td>';
echo '</tr>';
echo '</table>';
echo '<br><input type="button" value="Close" onclick="cancel()">';
