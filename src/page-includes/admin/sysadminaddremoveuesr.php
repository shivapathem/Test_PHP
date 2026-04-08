<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/adminfunctions.php';
include_once '../users/process/classUserSetup.php';
$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'];
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
if (($intSysAdmin == 1) || !empty($userDivisionsList)) {
$userid = $_POST['userid'];
$action = $_POST['action'];
$currentuser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$currentuserid = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$resultMod = json_decode(modSystemAdminList($userid,$action,$currentuserid,$currentuser),true); 
} else {
    echo 'Access Denied'; die;
}
            