<?php
session_start();
include_once '../process/classTeamskills.php';
$teamskillobj = new classTeamskills;

$intID = $_REQUEST['id'];
$intGMTBST = $_REQUEST['gmtbst'];
// $intGMTBST is what we are looking at so it's the otther to toggle!
$teamskillobj->UpdateToggleByID($intID, $intGMTBST);
?>
