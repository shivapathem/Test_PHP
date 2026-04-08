<?php

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/datetimefunctions.php';
include_once '../../function-includes/allocations_functions.php';

$intID = $_REQUEST['id']; 
MarkAllocationAsDeleted ($intID);





?>