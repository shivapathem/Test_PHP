<?php
session_start();
/* common  functions*/

include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
/* call the history */
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
if ($_REQUEST['action'] == strtolower('history')) {
    
    //define the params
    $modulename = 'Divisions';
    $historylist = '';
    echo '<div class="historyScrollpopupScreen">';
    $historylist .= '<table class="tablesmalltidy " width="800px">
                    <tr class="historyheaderSticky"><th id="textcenter"><br><b>History</b><br><br></th></tr>';
		$historyResults = json_decode(getHistoryLists($_REQUEST['modulename'],$_REQUEST['divisionid']), true);
    if (!empty($historyResults)) {
        foreach ($historyResults as $history) {
            $historylist .= '
            <tr><td> ' . $history['History'] . '</td></tr>';
        }

    } else {
		switch($_REQUEST['modulename'])
		{
			case 'SchedulingTeams': 
									$historylist .= '
									<tr><td> There is no History for this scheduling team yet! </td></tr>';
									break;
			default :				$historylist .= '
									<tr><td> There is no History for this user yet! </td></tr>';
									break;
		}
    }
    $historylist .= '</table>';
  

    $resonse_array = array("status" => "success", "history" => $historylist);
    echo $historylist;
}

if ($_REQUEST['action'] == strtolower('additionalpermissionhistory')) {
   
    //define the params
    $historylist = '';
    $historylist .= '<table class="tablesmalltidy " width="800px">
                    <tr><th id="textcenter"><br><b>History</b><br><br></th></tr>';
    $additionalPermissionHistory= json_decode(getAdditioanlHistoryLists($_REQUEST['modulename'],$_REQUEST['attributeid'],$_REQUEST['attributeid2']), true);

    if (!empty($additionalPermissionHistory)) {
        foreach ($additionalPermissionHistory as $history) {
            $historylist .= '
            <tr><td> ' . $history['History'] . '</td></tr>';
        }

    } else {
        $historylist .= '
        <tr><td> There is no History for this user yet! </td></tr>';
    }
    $historylist .= '</table>';
    echo '</div>';

    $resonse_array = array("status" => "success", "history" => $historylist);
    echo $historylist;
}

if ($_REQUEST['action'] == 'contractHistory')
{
    $historylist = '<table class="tablesmalltidy " width="800px"><tr><th id="textcenter"><br><b>History</b><br><br></th></tr>';
    $contractHistoryData = base64_decode($_REQUEST['msg']);
    $contractHistoryDataArr = explode(' -- ', $contractHistoryData);
	//echo "<pre>"; print_r($contractHistoryDataArr);die;
    if (!empty($contractHistoryDataArr)) {
        foreach ($contractHistoryDataArr as $history) {
            $historylist .= '
            <tr><td> ' . $history . '</td></tr>';
        }

    } else {
        $historylist .= '
        <tr><td> There is no History for this user yet! </td></tr>';
    }
    $historylist .= '</table>';
    echo '</div>';

    $resonse_array = array("status" => "success", "history" => $historylist);
    echo $historylist;
}

if ($_REQUEST['action'] == 'showPublishHistory')
{
    $historylist = '<div class="historyScrollpopupScreen"> <table class="tablesmalltidy " ><tr><th id="textcenter"><br><b>History</b><br><br></th></tr>';
	$teamId = $_POST['teamId'];
	$weekNo = $_POST['weekNo'];
	$showPublishHistoryArr = json_decode(showPublishHistory($teamId,$weekNo), true);
	//echo "<pre>"; print_r($showPublishHistoryArr);die;
    if (!empty($showPublishHistoryArr)) {
        foreach ($showPublishHistoryArr as $history) {
            $historylist .= '
            <tr><td> ' . $history['History'] . '</td></tr>';
        }

    } else {
        $historylist .= '
        <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This week has not been published yet&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>';
    }
    $historylist .= '</table>';
    echo '</div>';

    $resonse_array = array("status" => "success", "history" => $historylist);
    echo $historylist;
}

if ($_REQUEST['action'] == strtolower('leavenrequesthistory')) {
    
    //define the params
    $historylist = '';
    echo '<div class="historyScrollpopupScreen">';
    $historylist .= '<table class="tablesmalltidy " width="800px">
                    <tr class="historyheaderSticky"><th id="textcenter"><br><b>History</b><br><br></th></tr>';
   $historyResults = json_decode(getLeaveRequesyHistoryLists($_REQUEST['modulename'],$_REQUEST['strLogin']), true);
    if (!empty($historyResults)) {
        foreach ($historyResults as $history) {
            $historylist .= '
            <tr><td> ' . $history['History'] . '</td></tr>';
        }

    } else {
		switch($_REQUEST['modulename'])
		{
			case 'SchedulingTeams': 
									$historylist .= '
									<tr><td> There is no History for this scheduling team yet! </td></tr>';
									break;
			default :				$historylist .= '
									<tr><td> There is no History for this user yet! </td></tr>';
									break;
		}
    }
    $historylist .= '</table>';
  

    $resonse_array = array("status" => "success", "history" => $historylist);
    echo $historylist;
}