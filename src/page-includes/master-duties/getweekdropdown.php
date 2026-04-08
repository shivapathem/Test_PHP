<?php
/* Update  the Week Dropdown While changing the Yearsv in Create/Edit Master Duty */
  session_start();

  include_once '../../function-includes/DBHelper.php';
  include_once '../../function-includes/masterduty_functions.php';

      $year =  $_REQUEST['year'];
      $dutyid = $_REQUEST['dutyid'];
      $startWeek = "onchange";

      /*Populate the week number as per year*/
      $week_avaialble = PopulateWeekNumberDropDown($startWeek,$year,$dutyid);

      if($week_avaialble == ""){
          $response_array = array('status' => 'fail', 'Weeks' => ""); 
      }else{
          $response_array = array('status' => 'success', 'Weeks' => $week_avaialble); 
      }

      header('Content-type: application/json');
      echo json_encode($response_array);
?>