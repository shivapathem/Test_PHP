<?php
session_start();
include_once '../../function-includes/handoverfunctions.php';
$id = $_REQUEST['id'];
$strFullName = escapeSingleQuotes($_SESSION['user']['FullName']);
// Get the date
$now = date("Y-m-d H:i:s");
inRemovedHandover($now, $strFullName, $id);
