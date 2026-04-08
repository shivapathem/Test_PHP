<?php
if (session_status() === PHP_SESSION_NONE) {
 session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/DB_Functions.php';

$intID = $_REQUEST['leaveid'];

$leaveAllocationDetails =GetLeaveAllocationByID($intID);
$moduleName = 'LeaveAllocation';
$historyResults = json_decode(getHistoryLists($moduleName,$intID), true);

echo '<table class="tablesmalltidy" width="600px">';
echo '<tr><th>';

echo "<br>Leave History for ".$leaveAllocationDetails["FullName"]."<br><br>";
echo '</th></tr>';

if(count($historyResults) > 0) {
 foreach($historyResults as $key => $value){
  echo '<tr><td>';
   echo $value['History'];
   echo '</td></tr>';
 }
}
echo '<tr><td align="center"> <br><input type="button" value="OK" onclick="cancel()"> </td></tr>';


echo '</table>';

//echo '<br><br></div>';
?>