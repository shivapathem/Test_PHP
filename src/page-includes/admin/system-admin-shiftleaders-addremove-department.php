<?php
include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';

$intID = $_REQUEST['id'];
$intschedulingTeamId = $_REQUEST['schedulingTeamId'];
$intAction = $_REQUEST['action'];

$pdo = OpenDBLinkA7();

if ($intAction == 0) {
  // Remove
  $strQuery = "DELETE FROM shiftleadertypes_appliesto
               WHERE        (schedulingTeamId = $intschedulingTeamId) AND (TypeID = $intID)";
}
else {
  // add
  $strQuery = "INSERT INTO shiftleadertypes_appliesto
                         (schedulingTeamId, TypeID)
                    VALUES        ($intschedulingTeamId, $intID)";

}

$stmt = $pdo->prepare($strQuery);
$stmt->execute();
