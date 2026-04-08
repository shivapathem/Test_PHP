<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//in this file we are handling the create/listing, users
include_once 'classAllocateUsers.php';
include_once '../view/classAllocateUsersUI.php';
include_once __DIR__.'/../../../../function-includes/common/classCommonDBFunctions.php';

//call the class allocate users
$allocateusersobj = new ClassAllocateUsers();
$allocateuserobjhtml = new classAllocateUsersUI();
$commonDbobj = new classCommonDBFunctions();
$action = '';
if (isset($_POST['action'])) {
    $action = $_POST['action'];
}
/* For listing allocate users*/
if ($action == 'getAllocateUsers') {
    $type = "list";
    $alloacteuserslists = json_decode($allocateusersobj->getAllocateUsers($type), true);
    echo json_encode(array("data" => $alloacteuserslists));
}
/* create division record*/
else if ($action == strtolower('createuserspopup')) {
    //return the HTML
    $param['formname'] = $_POST['formname'];
    $param['buttonid']=$_POST['buttonid'];
    $param['hiddenactionname']= $_POST['hiddenactionname'];
    $param['hiddenactionvalue']=  $_POST['hiddenactionvalue'];
    $param['teamid']= $_POST['teamid'];
    $param['title']= $_POST['title'];
    $allocatusershtml = $allocateuserobjhtml->getAllocateUsersHTML($param);
    echo $allocatusershtml;
}
/* create division record*/
else if (isset($_POST['js_checkuser']) &&  $_POST['js_checkuser'] == strtolower('check')) {
    $userLogin = trim($_POST['netLogin']);
    //first check in users table ,if we have then send the bug
    //INSERT  allocate users 

    $checkallocateUserResult = json_decode($allocateusersobj->checkAllocateUser($userLogin), true);
    if ($checkallocateUserResult['strstatus'] == strtolower('success')) {
        $checkallocateUserResult['type'] = 'allaocteuser';
        $allocatusershtml = $allocateuserobjhtml->getCheckUserHTML($checkallocateUserResult);
        $respons_array = array('status' => 'usererror', 'view' => $allocatusershtml);
    } else {
        
        //check in AD spr if result found then add the data into user table
        $allocateUserADResult = json_decode($allocateusersobj->UserDetailsFromActiveDirectory($userLogin), true);

        if (!empty($allocateUserADResult)) {
            $allocateUserResult = json_decode($allocateusersobj->insertAllcoateUsers($userLogin,$allocateUserADResult['UserEmployeeNumber'],$allocateUserADResult['UserSurname'],$allocateUserADResult['UserFirstName'],$allocateUserADResult['UserEmailAddress']), true);
            if($allocateUserResult['strstatus']) {
                $respons_array = array('status' => $allocateUserResult['strstatus'], 'view' => $allocateUserResult['strreturnstring']);
            } else {
                $respons_array = array('status' => 'error', 'view' => 'Due to some reason  not able to complete this action');
            }
        } else {
            $respons_array = array('status' => 'error', 'view' => '<br>The Login has not been found. Please try again.<br><br>');
        }
    }
    echo json_encode($respons_array);
}
/* create division record*/
else {
    $netLogin = $_POST['netlogin'];
    $userID =  $_POST['userid'];
    $staffid = isset($_POST['staffid']) ? $_POST['staffid'] : '';
    //fetch the roles
    $getallocateUserInfoResult = json_decode($allocateusersobj->showAllocateUserInfo($userID,$netLogin), true);
    $roleLists  =    json_decode($allocateusersobj->getAllRoles(),true);
    //check staff details
    $userStaffDetails =  json_decode($commonDbobj->getStaffDetailsDisplayNameByID($userID),true); 
      $userStaffDetails =$userStaffDetails ? $userStaffDetails : []; 
    $userStaffDetails['DisplayName'] = isset($userStaffDetails['DisplayName']) ? $userStaffDetails['DisplayName'] : '';
    $userStaffDetails['PreferredForename'] = isset($userStaffDetails['PreferredForename']) ? $userStaffDetails['PreferredForename'] : '';
    $userStaffDetails['Forename'] = isset($userStaffDetails['Forename']) ? $userStaffDetails['Forename'] : '';
    $userStaffDetails['Surname'] = isset($userStaffDetails['Surname']) ? $userStaffDetails['Surname'] : '';
    $userStaffDetails['NetLogin'] = isset($userStaffDetails['NetLogin']) ? $userStaffDetails['NetLogin'] : '';
    if($userStaffDetails['DisplayName']) {
        $userStaffDetails['PreferredForename'] = $userStaffDetails['DisplayName'];
    } else if($userStaffDetails['PreferredForename']) {
        $userStaffDetails['PreferredForename'] = $userStaffDetails['PreferredForename'].' '.$userStaffDetails['Surname'];
    } else {
        $userStaffDetails['PreferredForename'] = $userStaffDetails['Forename'].' '.$userStaffDetails['Surname'];
    }
    //fetch leave request data
    $arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLogin($netLogin, 0),true);
   //get response html
    $allocatusershtml = $allocateuserobjhtml->userInfoHTML($roleLists,$userStaffDetails,$getallocateUserInfoResult, $arrUserSettings );

    //send response
    $respons_array = array('status' => 'success', 'view' => $allocatusershtml);
    echo json_encode($respons_array);
}
