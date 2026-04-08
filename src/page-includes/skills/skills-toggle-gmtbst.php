<?php
session_start();
include_once '../../function-includes/init.php';

$intID = $_REQUEST['id'];
$intGMTBST = $_REQUEST['gmtbst'];
// $intGMTBST is what we are looking at so it's the otther to toggle!
if ($intGMTBST == 1) {
  // Toggle GMT.....
  $strSQL = "UPDATE       skills_duties
             SET          GMT = GMT ^ 1
             WHERE       (ID = $intID)";  
  
  

}
else {
 // Toggle BST

  $strSQL = "UPDATE       skills_duties
             SET          BST = BST ^ 1
             WHERE       (ID = $intID)";

}
$db = OpenDatabase();
sqlsrv_query($db, $strSQL);
