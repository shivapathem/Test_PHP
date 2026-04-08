<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

include_once  __DIR__.'/../function-includes/init.php';
include_once __DIR__.'/../function-includes/common/classCommonDBFunctions.php';
$commonObj = new classCommonDBFunctions();

$struserlogin = isset($_SESSION['user']['user']) && ($_SESSION['user']['user']!= '')? $_SESSION['user']['user']:'';
$rsStaff = [];
if($struserlogin != ''){
        if(!empty($commonObj->GetUserDetailsFromDomaiDirectory($_SESSION['user']['user']))){
            $rsStaff = $commonObj->GetStaffDetailsUserDetailsByLogin($_SESSION['user']['user']);
            $isFreelencer =  $commonObj->GetIsFreelencerByNetlogin($_SESSION['user']['user']);
            $_SESSION['user']["isFreelance"] = !empty($isFreelencer) ? 1 : 0;
        }
}
// In the Teamwork table but no Area defined
if (!empty($rsStaff)) {
    $_SESSION['user']["StaffID"] = $rsStaff["StaffID"];
    $_SESSION['user']["StaffNumber"] = $rsStaff["StaffNumber"];
    if (isset($rsStaff["userDisplayName"])) {
        $_SESSION['user']["FullName"] = $rsStaff["userDisplayName"];
    }else{
        $_SESSION['user']["FullName"] = $_SESSION['user']['user'];
    }
    $_SESSION['user']["AreaName"] = '';
    $_SESSION['user']["UserID"] = $rsStaff["UserID"];
} else {
    //Can't find user details or staff details
    $_SESSION['user']["StaffID"] = 0;
    $_SESSION['user']["StaffNumber"] = '';
    $_SESSION['user']["FullName"] = $struserlogin;
    $_SESSION['user']["AreaID"] = 0;
    $_SESSION['user']["AreaName"] = '';
    $_SESSION['user']["UserID"] = 0;
}

$_SESSION['user']["SysAdmin"] = 0;
$intSysAdmin = $commonObj->UserIsSysAdmin($_SESSION['user']['UserID']);

$_SESSION['user']["SysAdmin"] = $intSysAdmin == ''? 0 :$intSysAdmin;
$_SESSION['user']['AreaID'] = 1;
if(!empty($_SESSION['user']['user']) && isset($_SESSION['user']['user'])){
    setcookie('editWeeklyUserNetLogin', $_SESSION['user']['user'], ['expires' => time() + 86400, 'path' => "/"]);
}
if(!empty($_SESSION['user']['UserID']) && isset($_SESSION['user']['UserID'])){
    setcookie('editWeeklyUserId', $_SESSION['user']['UserID'], ['expires' => time() + 86400, 'path' => "/"]);
}
if(!empty($_SESSION['user']['FullName']) && isset($_SESSION['user']['FullName'])){
    setcookie('editWeeklyUserFullName', $_SESSION['user']['FullName'], ['expires' => time() + 86400, 'path' => "/"]);
}
