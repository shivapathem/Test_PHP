<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$id = $_REQUEST["id"];
$db = OpenDatabase();
// Get the allocation
$query = "SELECT Allocations_webedit.DutyName, Allocations_webedit.StartTime, Allocations_webedit.EndTime, Allocations_webedit.History,
          Jobs_webedit.JobName, Jobs_webedit.StartTime AS JobStartTime, Jobs_webedit.EndTime AS JobEndTime,
          Jobs_webedit.history AS JobHistory, Jobs_webedit.ID AS JobID
          FROM  Allocations_webedit
          LEFT OUTER JOIN Jobs_webedit ON Allocations_webedit.AllocationID = Jobs_webedit.AllocationID
          AND Allocations_webedit.AllocateInstanceID = Jobs_webedit.AllocateInstanceID
          WHERE (Allocations_webedit.ID = $id)
          AND Allocations_webedit.AllocateInstanceID = " . getCurrentInstanceId();


  $allocation = sqlsrv_query($db, $query);
  while($row = sqlsrv_fetch_array($allocation)){

    $arrallocation['duty'] = $row['DutyName'];
    $arrallocation['starttime'] = doubletoseconds($row['StartTime']);
    $arrallocation['endtime'] = doubletoseconds($row['EndTime']);
    $arrallocation['history'] = $row['History'];

    $arrallocation['jobs'][$row['JobID']]['job'] = $row['JobName'];
    $arrallocation['jobs'][$row['JobID']]['starttime'] = doubletoseconds($row['JobStartTime']);
    $arrallocation['jobs'][$row['JobID']]['endtime'] = doubletoseconds($row['JobEndTime']);
    $arrallocation['jobs'][$row['JobID']]['history'] = $row['JobHistory'];
  }
//echo '<div style="overflow: scroll; position: absolute; width:400px; height:400px; left:0px; top:0px">';
echo '<table class="tablesmall" width="1000px">';
echo '<tr>';
echo '<td class="LightGrey" width="50px">Duty</td>';
echo '<td>'.$arrallocation['duty'].'</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="LightGrey">Times</td>';
echo '<td>'.date("H:i", $arrallocation['starttime']).'-'.date("H:i", $arrallocation['endtime']).'</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="LightGrey" valign="top">History</td>';
echo '<td>'.$arrallocation['history'].'</td>';
echo '</tr>';


foreach ($arrallocation['jobs'] as $id => $job) {
  if ($job['history'] != '') {
      echo '<tr>';
    echo '<td class="LightGrey" colspan="2">&nbsp;</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="LightGrey" width="50px">Job</td>';
    echo '<td>'.$job['job'].'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="LightGrey">Times</td>';
    echo '<td>'.date("H:i", $job['starttime']).'-'.date("H:i", $job['endtime']).'</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="LightGrey" valign="top">History</td>';
    echo '<td>'.$job['history'].'</td>';
    echo '</tr>';
  }
}
echo '</table>';
//echo '</div>';

 //echo '<pre>';
 //print_r($arrallocation);
?>