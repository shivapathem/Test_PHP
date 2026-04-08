<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/shiftleaderfunctions.php';
$intID = $_REQUEST['id'];
getShiftActiveInactive($intID);
             