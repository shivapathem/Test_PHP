<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';


$strStaffLogin = $_REQUEST['login'];  
  
$db = OpenDatabase();

$strQuery = "SELECT       History
             FROM         Staff_Web_Config
             WHERE        (Login = N'$strStaffLogin')";


$rsUser = sqlsrv_query($db, $strQuery);
$row = sqlsrv_fetch_array($rsUser);
echo '<div style="width: 800px; height: 400px; position: relative; overflow:auto">'; 
echo '<table class="tablesmalltidy" width="100%">';
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
echo '</div>';
echo '<br><input type="button" value="Close" onclick="cancel()">';
?>


