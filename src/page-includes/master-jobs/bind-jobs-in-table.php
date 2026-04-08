<?php
session_start();
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/master-jobs-functions.php';
$dutyid = $_REQUEST['dutyid'];
$jobsResult = getMasterJobByMasterDutyID($dutyid);
$jobs = json_decode($jobsResult,true);
$str = '';
foreach($jobs as $job){
    $JobStartTime = $job['StartTime'] > 86400 ? ($job['StartTime'] - 86400) : $job['StartTime'];
    $JobEndTime = $job['EndTime'] > 86400 ? ($job['EndTime'] - 86400) : $job['EndTime'];
    $str.="<tr>";
	$str.="<td>".$job['JobName']."</td>"."<td>".FormatTime($JobStartTime)."</td>"."<td>".FormatTime($JobEndTime)."</td>";
	$str.="</tr>";
}
echo $str;
