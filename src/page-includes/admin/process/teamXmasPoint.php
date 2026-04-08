<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

include_once '../view/xmasPointUI.php';
include_once '../view/schedulingTeamUI.php';
include_once 'classTeamXmasPoints.php';
include_once 'classSchedulingTeam.php';
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../../../function-includes/genericfunctions.php';

$intUserID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
$task = '';
if(isset($_POST['task'])){
    $task = $_POST['task'];
}

$teamxmasuiobj = new xmasPointUI();
$teamxmasobj = new ClassTeamXmasPoints();
$schedteamobj = new classSchedulingTeam();
$userTeamRoles = $schedteamobj->checkForLinkAccess();
$schedteamuiobj = new schedulingTeamUI($userTeamRoles);
// Controller for team Xmaspoint HtmlCall .
if ($task === 'teamXmaspointHtmlCall') {
    $pageid = 14;
    $isSysAdmin = isset($_SESSION['user']['SysAdmin']) && ($_SESSION['user']['SysAdmin'] != '')? $_SESSION['user']['SysAdmin']:$schedteamobj->getIsSysAdmin($intUserID);
    $userTeamLists = json_decode(GetUserAreaTeamsListByFormId ($intUserID, 0, $pageid),true);
    $xmasPointByTeamUI = $teamxmasuiobj->teamXmaspointHtmlCall($userTeamLists,$userTeamRoles,$isSysAdmin);
    echo $xmasPointByTeamUI;
}

//for populate the table .
if ($task === 'teamXmaspointLists') {
    $teamXmasPoint = json_decode($teamxmasobj->getXmasPointByTeam($_REQUEST['teamid']),true);
    $xmasPointByTeamUI = $teamxmasuiobj->xmasTableListingDesign($teamXmasPoint,$_REQUEST['teamid']);
    echo json_encode(array("status"=>'success',"view"=>$xmasPointByTeamUI));
}

// call for populate the xmas point .
if ($task === 'teamxmaspointpopup') {
    $teamXmasPointDetail = json_decode($teamxmasobj->getXmasPointDayDetail($_REQUEST['teamid'],$_REQUEST['day']),true);
    $params = array();
    $params['BasicPoints'] = !empty($teamXmasPointDetail ) ? $teamXmasPointDetail ['BasicPoints']: 0;
    $params['Limit'] = !empty($teamXmasPointDetail ) ? $teamXmasPointDetail ['Limit']: 0;
    $params['xmasPointsByTeamID'] = !empty($teamXmasPointDetail ) ? $teamXmasPointDetail ['xmasPointsByTeamID']: 0;
    $params['teamid'] = $_REQUEST['teamid'];
    $params['day'] = $_REQUEST['day'];
    $xmasPointByTeamUI = $teamxmasuiobj->xmaspoinByDayPopup($params);
    echo json_encode(array("status"=>'success',"view"=>$xmasPointByTeamUI));
}

// add for populate the xmas day point .
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'xmasdaypoint') {
    echo $teamxmasobj->modXmasPointDayByTeam($_REQUEST);
}

// delete for populate the xmas sub point .
if ($task === 'deletesubxmas') {
    $deleteSmasSub =  $teamxmasobj->delXmasPointTimeByTeam($_REQUEST['subxmaspointid']);
    echo json_encode(array("strstatus"=>'success'));
}
// show for populate the xmas sub point .
if ($task === 'xmaspointsubpopup') {
    $teamXmasPointSubDetail = '';
    if($_REQUEST['subxmaspointid'] > 0){
        $teamXmasPointSubDetail = json_decode($teamxmasobj->getXmasPointSubDetail($_REQUEST['subxmaspointid']),true);
    }
    $params['day']= $_REQUEST['day'];
    $params['teamid']= $_REQUEST['teamid'];
    $params['subxmaspointid']= $_REQUEST['subxmaspointid'];
    $params['xmaspointBySubDetails']= $teamXmasPointSubDetail;
    $xmasPointByTimeUI = $teamxmasuiobj->xmasPointByTimePopup($params);
    echo json_encode(array("status"=>'success',"view"=>$xmasPointByTimeUI));
}
// add for populate the xmas sub point .
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'xmassubpoint') {
    $intStartTime = timetoseconds($_REQUEST['starttime']);
    $intEndTime = timetoseconds($_REQUEST['endtime']);
    if (isset($_REQUEST["AfterMidnight"]) ) {
        $intStartTime = $intStartTime + 86400;
        $intEndTime = $intEndTime + 86400;
    }
    if ($intEndTime < $intStartTime) {
        $intEndTime = $intEndTime + 86400;  
    }  
    $_REQUEST['starttime'] = $intStartTime;
    $_REQUEST['endtime'] = $intEndTime;
    echo $teamxmasobj->modXmasPointTimeByTeam($_REQUEST);
}

// List for Scheduling team Extra Xmas Point.
if ($task === 'extraXmasPointHtmlListCall') { 
    //fetch the scheduling team
    $pageid = 13;
    $isSysAdmin = isset($_SESSION['user']['SysAdmin']) && ($_SESSION['user']['SysAdmin'] != '')? $_SESSION['user']['SysAdmin']:$schedteamobj->getIsSysAdmin($intUserID);
    $userTeamLists = json_decode(GetUserAreaTeamsListByFormId ($intUserID, 0, $pageid),true);
    $extraPointListingResult = $schedteamuiobj->extraXmasPointListingHTML($userTeamLists,$isSysAdmin);
    echo $extraPointListingResult;
}
if ($task === 'extraXmasPointListCall') { 
    $extraXmasPointID = 0;
    $extraPointLists  ='' ;
    $schedulingTeamid= isset($_POST['teamid']) ? $_POST['teamid']: 0; 
    $type ='list';
    
    if($schedulingTeamid !=0){
        $extraPointLists  = json_decode($schedteamobj->getExtraXmasPointByTeamID($extraXmasPointID,$schedulingTeamid,$type), true);
    }
     echo json_encode(array("data" => $extraPointLists));
    
}


if( $task === 'createxmaspoint'){
    $param['title'] = 'Add';
    $param['button'] = 'Create';
    $param['teamid'] = $_REQUEST['teamid'];
    $param['extraxmaspoint']= $_REQUEST['extrapointid'];
    $scheduledPeopleLists = json_decode(getAllPeoplebyTeamId($_REQUEST['teamid']), true);
    
    $extraPointPoupResult = $schedteamuiobj->extraXmasPointPopupHTML($scheduledPeopleLists,$param);
    $respons_array = array('status' => 'success', 'view' => $extraPointPoupResult);
    echo json_encode($respons_array);

}

if( $task === 'updateextrapoint'){
   
    $scheduledPeopleLists= array();
    $params['title'] = 'Edit';
    $params['button'] = 'Update';
    $params['teamid'] = $_REQUEST['teamid'];
    $params['extraxmaspoint']= $_REQUEST['extrapointid'];
    $params['type'] ='details';
    $params['extraXmasPointDetails'] =  json_decode($schedteamobj->getExtraXmasPointByTeamID($params['extraxmaspoint'],$params['teamid'],$params['type']), true);
    $extraPointPoupResult = $schedteamuiobj->extraXmasPointPopupHTML($scheduledPeopleLists,$params);
    $respons_array = array('status' => 'success', 'view' => $extraPointPoupResult);
    echo json_encode($respons_array);

}
$js_xtrapointubmit = isset($_POST['js_xtrapointubmit']) ? $_POST['js_xtrapointubmit'] : '';
if($js_xtrapointubmit === 'Create'){
    $insertUserXmasPoint = json_decode($schedteamobj->modUserExtraXmasPointByTeam($_POST), true);
    echo json_encode($insertUserXmasPoint);
}

if( $js_xtrapointubmit === 'Update'){
    $updateUserXmasPoint = json_decode($schedteamobj->modUserExtraXmasPointByTeam($_REQUEST), true);
    echo json_encode($updateUserXmasPoint);
}
//remove the extra point
if( $task === 'delete'){
    $updateUserXmasPoint = json_decode($schedteamobj->deleUserExtraXmasPointByTeam($_REQUEST['extrapointid']), true);
   
    echo json_encode($updateUserXmasPoint);
}

