<?php
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/masterduty_functions.php';

$intDutyId = $_REQUEST['intDutyId'];
$result = verifyDutyAssignedtoRota($intDutyId);
echo $result;
