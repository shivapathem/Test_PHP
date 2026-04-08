<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';

$db = OpenDatabase();


$intID = $_REQUEST['id'];


$strQuery = "DELETE 
             FROM         BreaksTable
             WHERE        (ID = $intID)";
sqlsrv_query($db, $strQuery);
               