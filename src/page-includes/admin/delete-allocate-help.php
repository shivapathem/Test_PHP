<?php
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase(); 
$intID = $_REQUEST['id'];

  $strQuery = "DELETE FROM AllocateDocuments
               WHERE       (ID = $intID)";
sqlsrv_query($db, $strQuery);
