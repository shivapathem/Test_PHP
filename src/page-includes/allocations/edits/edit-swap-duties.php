<?php

include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/allocations_functions.php';






 $intID1 = $_REQUEST['id1']; 
 $intID2 = $_REQUEST['id2'];


  SwapDutiesForPeriod ($intID1, $intID2);





?>