<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';

$ddate = $_REQUEST['ddate'];
$schedulingPersonId = $_REQUEST['schedulingPersonId'];
$teamId = $_REQUEST['teamId'];

//get edp comments
$row = getEdpComments($schedulingPersonId, $ddate, $teamId);
$comments = $row['comments'];
$edpId = $row['ID'];

//get Edp history 
$historyResults = json_decode(getHistoryLists('EDP',$edpId), true);

echo'<table>';
// Duty Comments
if (!is_null($comments)) {
  echo'<tr>';
  echo'<td valign="top" width="75px" class="vTop">Comments</td>';
  echo'<td valign="top" width="320px">'.$row['comments'].'</td>';
  echo'</tr>';
}


foreach ($historyResults as $key => $value) {
  echo'<tr>';
  if ($key != 0) {
    echo '<td></td>';
    echo'<td class="historyRow">'.$value['History'].'</td>';
  } else {
    echo'<td valign="top" width="75px" class="vTop">History</td>';
    echo'<td valign="top" width="320px" class="historyRow">'.$value['History'].'</td>';
  }
  echo'</tr>';
}

echo'</table>';
?>