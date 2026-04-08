<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();


$intID = $_REQUEST['id'];

    $strQuery = "DELETE FROM AutoPagesFilters
                 WHERE id = $intID";

  sqlsrv_query($db, $strQuery);
