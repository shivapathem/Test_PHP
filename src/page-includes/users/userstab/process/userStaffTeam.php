<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../view/classAllocateUsersStaffUI.php';
include_once 'classTeamStaff.php';
include_once '../../../../function-includes/DB_Functions.php';
include_once '../../../../function-includes/DBHelper.php';
include_once '../../../admin/allocate-users/view/classAllocateUsersUI.php';
include_once '../../../../function-includes/adminfunctions.php';
include_once '../../../users/process/classUserSetup.php';
include_once __DIR__ . '../../../../admin/divisions/process/classDivisionalAdmin.php';
include_once __DIR__ . '../../../../admin/process/classSchedulingTeam.php';
include_once __DIR__ .  '../../../../../function-includes/genericfunctions.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
//Call class object
$userStaffObjUI = new classAllocateUsersStaffUI();
$teamStaffObj = new classTeamStaff();
$allocateuserobjhtml = new classAllocateUsersUI();
$setupObj = new classUserSetup();
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type = 'menu'), true);
$intSysAdmin = $_SESSION['user']['SysAdmin'];

if ($action == 'getSearchTeamStaff') {
    if (($intSysAdmin == 1) || !empty($userDivisionsList) || ($arrUsersTeamdata["isSchedulingTeamAdmin"] == 1) || ($arrUsersTeamdata["isScheduler"] == 1)) {
        //get additional role only
        $roletype = 1;
        $getAdditionalRoleLists = json_decode(getRoleLists($roleaction = '', $roletype), true);
        //get scheduled team list
        $userTeamLists = json_decode(getUserAllTeamsLists(), true);
        $roletype = 0;
        $getMainRoleLists = json_decode(getRoleLists($roleaction = '', $roletype), true);
        if ($_POST['usertype'] == 0) {
            $userStaffObjUI = $userStaffObjUI->getNonScheduledStaffTeamHTML($userTeamLists, $getAdditionalRoleLists, $_POST['usertype']);
        } else if ($_POST['usertype'] == 1) {
            $userStaffObjUI = $userStaffObjUI->getScheduledStaffTeamHTML($userTeamLists, $getAdditionalRoleLists, $_POST['usertype']);
        }
        echo $userStaffObjUI;
    } else {
        echo "Access Denied";
        die;
    }
}

if ($action == 'searchStaffTeam') {
    $teamid = $_POST['selectedTeamID'];
    $usertype = $_POST['usertype'];
    $divisionid = (isset($_POST['divisionid']) && ($_POST['divisionid'] != 0 || $_POST['divisionid'] != '')) ? $_POST['divisionid'] : 0;

    $getSearchTeamStaffLists = json_decode($teamStaffObj->getSearchTeamStaff($teamid, $usertype, $divisionid), true);

    echo json_encode(array("data" => $getSearchTeamStaffLists));
}

if ($action == 'additionalpermission') {
    $teamid = $_POST['teamID'] ?? 0;
    $userid = $_POST['userID'] ?? 0;
    $roleid = $_POST['roleID'] ?? 0;
    $actionValue = $_POST['setparam'] ?? 0;
    $schedulepersonid = $_POST['schedulepersonId'] ?? 0;
    $updatedRoleName = json_decode(getRoleNameById($roleid), true);

    //Only Basic or Advanced report role a person can have at a time
    $userDetails = GetScheduledPersonTeamDetails(GetScheduledPersonIdbyUserId($userid));
    if((($updatedRoleName == 'Basic Reports' && !empty($userDetails)) || ($updatedRoleName == 'Advanced Reports' && !empty($userDetails))) && $actionValue == 1) {
        $roles = json_decode(getRoleLists('all'), true);        
        $existingUserRoles = GetStaffOtionsByTeam($userDetails[$userDetails['homeTeamId']]['Login'], $teamid, $userid)[$teamid];
        $filteredRoles = array_filter($roles, function ($var) use($updatedRoleName) {
            if($updatedRoleName == 'Basic Reports') {
                return $var['RoleName'] == 'Advanced Reports';
            } else {
                return $var['RoleName'] == 'Basic Reports';
            }
        });
        $removeRole = array_pop($filteredRoles);
        if($existingUserRoles[$removeRole['RoleName']] > 0) {
            $teamStaffObj->setUsersPermissions($teamid, $userid, $removeRole['RoleID'], 0, $action, $schedulepersonid);
        }        
    }

    $setUserPermission = json_decode($teamStaffObj->setUsersPermissions($teamid, $userid, $roleid, $actionValue, $action, $schedulepersonid), true);
    echo json_encode(array("strstatus" => $setUserPermission[0]['strstatus']));
}

if ($action == 'rolepermission') {
    $teamid = $_POST['teamID'] ?? 0;
    $userid = $_POST['userID'] ?? 0;
    $roleid = $_POST['roleID'] ?? 0;
    $actionValue = $_POST['setparam'];
    $schedulepersonid = $_POST['schedulepersonId'] ?? 0;
    $teamsname = $_POST['teamsname'];
    $updatedRoleName = json_decode(getRoleNameById($roleid), true);
    $scheduledpersonDetails = json_decode(NonScheduledpersondetailsbyID($teamid, $schedulepersonid), true);

    $setUserPermission = json_decode($teamStaffObj->setUsersPermissions($teamid, $userid, $roleid, $actionValue, $action, $schedulepersonid), true);
    if ($setUserPermission[0]['strstatus'] == 'success' && $scheduledpersonDetails['RoleID'] != $roleid) {
        SendmailstoSystemAdminDivisionAdmin($teamsname, $scheduledpersonDetails['userDisplayName'], $scheduledpersonDetails['RoleName'], $updatedRoleName, $teamid);
    }
    echo json_encode(array("strstatus" => $setUserPermission[0]['strstatus']));
}

/* create division record*/
if (isset($_POST['js_checkalloacteuser']) && $_POST['js_checkalloacteuser'] == strtolower('checkalloacate')) {

    //first check user has been not assigned on selected team
    $assigNonScheduledteam = json_decode($teamStaffObj->createNonScheduledTeamStaff($_POST['netLogin'], $_POST['teamid']), true);
    $userData = json_decode($teamStaffObj->getUserIdbyNetLogin($_POST['netLogin']), true);

    switch ($assigNonScheduledteam[0]['strstatus']) {

        case 'usererror':
            $assigNonScheduledteam[0]['type'] = 'nonscheduled';
            $staffusershtml = $allocateuserobjhtml->getCheckUserHTML($assigNonScheduledteam[0]);
            $respons_array = array('status' => 'usererror', 'view' => $staffusershtml);
            break;
        case 'success':
            $userPermission = json_decode($teamStaffObj->setUsersPermissions($_POST['teamid'], $userData[0]['UD_UserID'], 6, 1, 'rolepermission', '', ''), true);
            $respons_array = array('status' => $assigNonScheduledteam[0]['strstatus'], 'view' => $assigNonScheduledteam[0]['strreturnstring']);
            break;
        case 'error':
            $respons_array = array('status' => $assigNonScheduledteam[0]['strstatus'], 'view' => $assigNonScheduledteam[0]['strreturnstring']);
            break;
    }

    echo json_encode($respons_array);
}

//autocomplete
if ($action == 'searchNetLogin') {
    $autocompleteList = [];
    $searchKey = $_REQUEST['term'];
    $searchAutocompleteList = json_decode(getStaffUserNetLogin($searchKey), true);
    if (!empty($searchAutocompleteList)) {
        foreach ($searchAutocompleteList as $data) {
            $autocompleteList[$data['UD_NetLogin']] = $data['UD_NetLogin'];
        }
    }
    header('Content-type: application/json');
    echo json_encode($autocompleteList);
}

//remove non scheduled staff from team also remove
if ($action == strtolower('removestaff')) {

    $removeNonScheduledteam = json_decode($teamStaffObj->removeNonScheduledTeamStaff($_POST['schedulepersonid'], $_POST['scheduledteamid']), true);
    $respons_array = array('status' => $removeNonScheduledteam[0]['strstatus'], 'view' => $removeNonScheduledteam[0]['strreturnstring']);
    echo json_encode($respons_array);
}

if ($action == 'rotapermission') {
    $teamid = $_POST['teamID'] ?? 0;
    $userid = $_POST['userID'] ?? 0;
    $roleid = $_POST['roleID'] ?? 0;
    $schedulepersonid = $_POST['schedulepersonId'] ?? 0;
    $actionValue = $_POST['setparam'];
    $setUserPermission = json_decode($teamStaffObj->setUsersPermissions($teamid, $userid, $roleid, $actionValue, $action, $schedulepersonid), true);
    echo json_encode(array("strstatus" => $setUserPermission[0]['strstatus']));
}

function SendmailstoSystemAdminDivisionAdmin($teamsname, $userFullname, $oldRoleName, $newRoleName, $teamid)
{
    $divisionobj = new ClassDivisionalAdmin();
    $teamObj = new classSchedulingTeam();
    $teamDetail = json_decode($teamObj->getSchedulingTeamDetails(0, $teamid, ''), true);
    $divisionAdminList = $divisionobj->getDivisonalMapList($teamDetail[0]['divisionid']);
    $divisionAdminList = $divisionAdminList ? json_decode($divisionAdminList, true) : '';
    $rsAdminUsers = json_decode(getAllSystemAdmin(), true);
    if (!empty($rsAdminUsers)) {
        $strTo = implode(" ", $rsAdminUsers);
    } else {
        $strTo = '';
    }
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = getenv('SMTP_HOST');
    $mail->Port = 25;
    $mail->setFrom('noreply@bbc.co.uk', 'Allocate');
    $arrMailAdd = explode(";", $strTo);
    for ($intCount = 0; $intCount < count($arrMailAdd); $intCount++) {
        $mail->addAddress(trim($arrMailAdd[$intCount]));
    }
    //CC to division admin
    foreach ($divisionAdminList as $divisionAdmin) {
        $divAdminEmail = $divisionAdmin['InternalEmail'] ?? $divisionAdmin['ExternalEmail'] ?? GetEmailFromLogin($divisionAdmin['NetLogin']);
        if (!in_array($divAdminEmail, $arrMailAdd)) {
            $mail->AddCC($divAdminEmail);
        }
    }
    $strRequesterName = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
    $emailSub = getenv('EMAIL_SUFFIX_N');
    if ($emailSub != '') {
        $mail->Subject = $emailSub . ' Allocate 7: ' . $userFullname . ' - Role Updated by ' . $strRequesterName . ' from ' . $oldRoleName . ' to ' . $newRoleName;
    } else {
        $mail->Subject = 'Allocate 7: ' . $userFullname . ' - Role Updated by ' . $strRequesterName . ' from ' . $oldRoleName . ' to ' . $newRoleName;
    }
    $mail->AddEmbeddedImage(getenv('PO_BANNER'), 'pobanner', 'po_banner.png');
    $mail->AddEmbeddedImage(getenv('BBC_LOGO'), 'bbclogo', 'bbc_logo.png');
    $html = '<style>' . (@file_get_contents(getenv('EMAIL_CSS')) ?: '') . '</style>
    <body>
    <img src="cid:pobanner" /><br><br>';

    $html .= '<h2>Hi Admins,</h2>
				 <p>The role of user ' . $userFullname . ' was updated by ' . $strRequesterName . ' from ' . $oldRoleName . ' to ' . $newRoleName . ' in ' . $teamsname . ' on ' . date("jS M Y") . ' at ' . date("H:i") . '</p>
         <br><br><br><p align="left"> Thanks </p>';
    $html .= '<br><br><p align="center"><img alt="BBC" src="cid:bbclogo" /><br><font size="1">BBC ' . romanNumerals(date("Y")) . '<font></p>';
    $html .= '</body></html>';
    $mail->msgHTML($html);

    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        exit;
    } else {
        echo "Message sent!";
    }
}
