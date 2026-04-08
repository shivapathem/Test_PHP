<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
include_once '../../function-includes/allocationsfunctions.php';
$intTeamID = $_REQUEST['teamId'] ?? '';
$screenName = $_REQUEST['screenName'] ?? '';
$sessNetLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$getSetFilterId = 0;
$getSetFilterId = GetCurrentSetWeeklyFilter($screenName, $intTeamID);
if(($getSetFilterId > 0) || ($getSetFilterId != '') || ($getSetFilterId != NULL)){
	$getFilterDetails = GetFilterDetails($getSetFilterId,$intTeamID,$screenName,$sessNetLogin);
	if($getFilterDetails == true){
		echo 0;
	} else {
		echo $getSetFilterId;
	}
}
?>