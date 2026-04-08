<?php
if (session_status() === PHP_SESSION_NONE) {
 session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';

$intID = $_REQUEST['leaveid'];
//Delete the leave allocation 
DelLeaveAllocationByID($intID);
