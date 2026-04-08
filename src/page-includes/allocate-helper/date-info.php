<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once __DIR__ . '/../../function-includes/common/classCommonDBFunctions.php';
$commonObj = new classCommonDBFunctions();
$date = $_REQUEST['date'];
$bbcweeknumberArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim($date);
$week = spinweek($bbcweeknumberArray['ixYearWeek']);
echo "<br>You have chosen " . date("d M Y", strtotime($date)) . "<br>";
echo "This corresponds to week $week (" . date("l", strtotime($date)) . ")<br>";
$bst = getbst($date);
if ($bst == 0) {
  echo "The time setting is GMT";
} else {
  echo "The time setting is BST";
}
echo '<br><br>';
