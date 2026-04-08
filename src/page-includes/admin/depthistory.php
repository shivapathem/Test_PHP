<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';


$intDeptID = $_REQUEST['DepID'];  
  
$db = OpenDatabase();

$strQuery = "SELECT       History
             FROM         Departments
             WHERE        (ID = $intDeptID)";


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
 echo $row['History'];
echo '</td>';
echo '</tr>';
