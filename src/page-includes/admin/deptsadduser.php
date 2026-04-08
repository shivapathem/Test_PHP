<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';


$intDeptID = $_REQUEST['department'];
$strStaffLogin = $_REQUEST['login'];  
if (isset($_REQUEST['isscheduled'])) {
  $intIsScheduled = $_REQUEST['isscheduled'];
}
else {
  $intIsScheduled = 0;
}
 
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$db = OpenDatabase();

  $tsql_callSP = "{call usp_RED_InsAddStaffTotDept( ?, ?, ?, ?)}"; 
    $params = array(   
                   array($intDeptID, SQLSRV_PARAM_IN),  
                   array($strStaffLogin, SQLSRV_PARAM_IN),  
                   array($strLogin, SQLSRV_PARAM_IN),
                   array($intIsScheduled, SQLSRV_PARAM_IN)  
                   );  
    $stmt = sqlsrv_query( $db, $tsql_callSP, $params);  
    if( $stmt === false ) {  
       echo "Error in executing statement 3.\n";  
       die( print_r( sqlsrv_errors(), true));  
    }
    else {
        //$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)
      if($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
          echo $row['ReturnValue'];
      }

    }
