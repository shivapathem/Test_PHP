<?php
session_start();
include_once '../../function-includes/init.php';
$db = OpenDatabase();
$strUser = $_REQUEST['login'];
if (isset($_REQUEST['action'])) {
  $intAction = $_REQUEST['action'];
}
else {
  $intAction = 1;
}

echo $strUser.' '.$intAction;

$strQuery = "DELETE FROM            Staff_Guests
             WHERE                  (Login = N'$strUser')";
             
sqlsrv_query($db, $strQuery);
   
if ($intAction == 1) {                
  $strQuery = "DELETE FROM            Staff_Web_Config_Departments_Link
               WHERE                  (Login = N'$strUser')";             
  sqlsrv_query($db, $strQuery);
}
