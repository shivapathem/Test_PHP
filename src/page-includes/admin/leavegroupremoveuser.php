<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';


$intGroupID = $_REQUEST['group'];
$strStaffLogin = $_REQUEST['login'];  
 
$staffsheduledPersonId = getScheduledPersonIDByNetLoginID($strStaffLogin);
$userFullName = $_SESSION['user']['FullName']; 
$modulename = 'Staff&Percentages'; 
$currentuserid =  isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];


$strLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$addStaffInLeavegroup =   AddStaffInLeaveGroup($intGroupID,$strStaffLogin,$staffsheduledPersonId,$userFullName,$modulename,$currentuserid);
