<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';

$strStaffLogin = $_REQUEST['login'];  
$intAction = $_REQUEST['action']; 
$intGroup = $_REQUEST['groupid'];
$fullName = $_SESSION['user']['FullName']; 
$modulename = 'Staff&Percentages'; 
$currentuserid =  isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
UpdateStaffLeaveGroupAdmin($intGroup,$intAction,$fullName,$strStaffLogin,$modulename,$currentuserid);
