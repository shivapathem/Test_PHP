<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/init.php';
include_once '../../function-includes/master-jobs-functions.php';
$job_data=[];
$job_data=array('StartTime'=>seconds_from_time($_REQUEST['StartTime']),
              'EndTime'=>seconds_from_time($_REQUEST['EndTime']),
              'info'=>$_REQUEST['info'], 
              'contact' => trim($_REQUEST["contact"]),
  'location'        => trim($_REQUEST["location"]),
  'backcolor'        => trim($_REQUEST["backcolor"]),
  'forecolor'        => trim($_REQUEST["forecolor"]),
  'programme_id'    => trim($_REQUEST["programme_id"]),
    'master_duty_id'=>$_REQUEST['master_duty_id']);
$master_duties=getMasterDutiesByID($_REQUEST['master_duty_id']);
$duty=json_decode($master_duties,true);
$duty_start=$duty['StartTime'];
$duty_end=$duty['EndTime'];

$job_start=seconds_from_time($_REQUEST["StartTime"]);
$job_end=seconds_from_time($_REQUEST["EndTime"]);
//If "Job Will created with in Duties"
if($job_start >=$duty_start && $job_end <=$duty_end)
{

  $job_data1=array('duty_check'=>1);
 echo json_encode($job_data+$job_data1);
  
}
//"Job Will created with in Duties"
else if($job_start >=$duty_start && $job_end >=$duty_end){
  
   $job_data2=array('duty_check'=>2);
  echo json_encode($job_data+$job_data2);
}
else{

}
?>