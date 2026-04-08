<?php
session_start();
include_once '../../function-includes/init.php';

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';
include_once '../../function-includes/testaccess.php';
include_once '../../class-includes/userRolePermissions.php';
$alljobs=getAllJobs();
$jobs=json_decode($alljobs,TRUE);
if(!empty($jobs)):
echo '<table id="joblist" class="compact stripe bluetable" width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th width="50px">Start</th>';
echo '<th width="200px">End</th>';
echo '<th width="300px">Job</th>';
echo '<th>Programme</th>';
echo '<th>Location</th>';
echo '<th>Contact</th>';
echo '<th>Info</th>';
echo '</tr>';
echo '</thead>';
foreach($jobs as $job):
     echo '<tr>';
     	 echo '<td> ';
  		echo $job['StartTime'];
 		 echo '</td>';
 		  echo '<td> ';
  		echo $job['EndTime'];
 		 echo '</td>';
 		  echo '<td> ';
  		echo $job['Job'];
 		 echo '</td>';
 		  echo '<td> ';
  		echo $job['Details'];
 		 echo '</td>';
 		 echo '<td> ';
  		//echo $job['Details'];
 		 echo '</td>';
 		 echo '<td> ';
  		//echo $job['Details'];
 		 echo '</td>';
 		 echo '<td> ';
  		//echo $job['Details'];
 		 echo '</td>';

      echo '</tr>';
endforeach;
echo "</table>";
endif;