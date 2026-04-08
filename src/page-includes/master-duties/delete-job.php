<?
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';


  //Check User Authentication
  $pageid = 4;
  $perms = unserialize($_SESSION['areaperms']);
  $perms = $perms[$pageid];
  $candelete = $perms->get_isdelete();
  
 
  $intDutyID = $_REQUEST['dutyid'];
  $intJobID = $_REQUEST['jobid'];
  $strLastModDate = $_REQUEST['lastmoddate'];
  
  list($intStatus, $strNewLastModDate) = DeleteMasterDutyJob($intDutyID, $intJobID, $strLastModDate);
    
  $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'lastmoddate' => $strNewLastModDate);
  header('Content-type: application/json');
  echo json_encode($response_array);

?>