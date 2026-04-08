<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';

$id = $_REQUEST['id'];

$pdo = OpenDBLinkA7();

$query = "SELECT  ud.UD_DisplayName AS FullName,LA.Login,LA.ID,LA.dDate,LA.Comments,LA.OfficeComments 
FROM LeaveApplications LA(nolock) 
INNER JOIN UserDetails ud (nolock) ON la.SchedulingPersonID=ud.UD_UserID 
WHERE (LA.ID  = $id)";

$stmt = $pdo->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$moduleName = 'LeaveApplication';
$historyResults = json_decode(getHistoryLists($moduleName,$id), true);
echo '<div class="leaveHistoryBox">';
echo '<table class="tablesmalltidy" width="600px">';
echo '<tr>';
echo '<th>';
echo "<br>Leave History for ".$row["FullName"]."<br>";

if(!is_null($row['dDate']))
    echo "Date ". date("jS F Y", strtotime($row['dDate']));

echo '<br><br>';
echo '</th>';
echo '</tr>';

echo '<tr>';
  echo '<td class="historyholder">';
  if(count($historyResults) > 0) {
    foreach($historyResults as $key => $value){
      echo '<div>';
	  if($value['History']!=''){echo strip_tags($value['History']).'<hr>';}
      echo '</div>';
    }
  } 
  echo '</td>';
  echo '</tr>';

echo '</table>';
echo '</div>';
echo '<div class="leaveHisAction">';
echo '<input type="button" value="OK" onclick="cancel()">';
echo '</div>';

?>