<?php
/* Description :Check the master duty name 
  created by :Naveeta Sharma
  created on : 07-april-2021
*/
  session_start();

  include_once '../../function-includes/init.php';
  include_once '../../function-includes/masterduty_functions.php';

  //check the master duty name available or not
  $name_avaialble = IsMasterDutyAvailable($_REQUEST['dutyName'],$_REQUEST['dutyId']);

  if($name_avaialble == true){

      $response_array = array('status' => 'success'); 
  }else{
      $response_array = array('status' => 'fail'); 
  }


  header('Content-type: application/json');
  echo json_encode($response_array);
?>