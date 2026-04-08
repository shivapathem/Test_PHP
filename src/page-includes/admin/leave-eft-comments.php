<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';


$strStaffLogin = $_REQUEST['Login'];  
$intGroup = $_REQUEST['GroupID'];  
  
$db = OpenDatabase();

$strQuery = "SELECT        EFTNotes
FROM            Staff_Web_Config_LeaveGroups_Link
WHERE        (Login = N'$strStaffLogin') AND (LeaveGroupID = $intGroup)";


$rsUser = sqlsrv_query($db, $strQuery);
$row = sqlsrv_fetch_array($rsUser);
echo $row['EFTNotes'];
?>


