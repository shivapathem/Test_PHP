<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';


$intDeptID = $_REQUEST['department'];
$strStaffLogin = $_REQUEST['login'];  
$intActionType = $_REQUEST['actiontype']; 
$intAction = $_REQUEST['action']; 
$intDepartment = $_REQUEST['department']; 
  
$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$db = OpenDatabase();
  
switch ($intActionType) {
  case 0:
    // Update the default Department
    $intResult = 1;
    $tsql_callSP = "{call usp_RED_UpdateStaffDefaultDept( ?, ?, ?)}"; 
    $params = array(   
                   array($intDeptID, SQLSRV_PARAM_IN),  
                   array($strStaffLogin, SQLSRV_PARAM_IN),  
                   array($strLogin, SQLSRV_PARAM_IN) 
                   );  
    $stmt = sqlsrv_query( $db, $tsql_callSP, $params);  
    if( $stmt === false ) {  
       echo "Error in executing statement 3.\n";  
       die( print_r( sqlsrv_errors(), true));  
    }
    else {
      $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
      echo $row['ReturnValue'];  
    }   
    break;

  default:
  
    // Update other settings.....
    $intResult = 1;
    $tsql_callSP = "{call usp_RED_UpdateStaffAttributes( ?, ?, ?, ?, ?)}";
    /*
    @intDept			      INTEGER,
	  @intActionType			INTEGER,
	  @intAction			    INTEGER,
	  @strStaffLogin			VARCHAR(50),
	  @strAdminLogin			VARCHAR(50)
    */
    
    $params = array(   
                   array($intDeptID, SQLSRV_PARAM_IN),
                   array($intActionType, SQLSRV_PARAM_IN),
                   array($intAction, SQLSRV_PARAM_IN),  
                   array($strStaffLogin, SQLSRV_PARAM_IN),  
                   array($strLogin, SQLSRV_PARAM_IN)
                    );  
    $stmt = sqlsrv_query( $db, $tsql_callSP, $params);  
    if( $stmt === false ) {  
       echo "Error in executing statement 3.\n";  
       die( print_r( sqlsrv_errors(), true));  
    }
    else {
      $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
      echo $row['ReturnValue'];  
    } 
    // If it's changin Appraiser, manager or mento remove the affected items.....
    
    if ($intActionType == 10 && $intAction == 0){
      $strQuery = "UPDATE     Staff_Web_Config_Departments_Link
                   SET        ManagerLogin = '0'
                   WHERE     (ManagerLogin = N'$strStaffLogin') AND (DepartmentID = $intDeptID)";

                 sqlsrv_query($db, $strQuery);
    
    }
    if ($intActionType == 1 && $intAction == 0){
      $strQuery = "UPDATE     Staff_Web_Config_Departments_Link
                   SET        AppraiserLogin = '0'
                   WHERE     (AppraiserLogin = N'$strStaffLogin') AND (DepartmentID = $intDeptID)";

                 sqlsrv_query($db, $strQuery);
    
    }
    if ($intActionType == 9 && $intAction == 0){
      $strQuery = "UPDATE     Staff_Web_Config_Departments_Link
                   SET        MentorLogin = '0'
                   WHERE     (MentorLogin = N'$strStaffLogin') AND (DepartmentID = $intDeptID)";

                 sqlsrv_query($db, $strQuery);
    
    }
}  
  
  




