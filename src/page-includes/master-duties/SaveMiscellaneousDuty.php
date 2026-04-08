<?php
session_start();
//include_once '../../function-includes/init.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';

//Check User Authentication
//Check User Authentication
$pageid = 5;
// Call User Permission function.
$intTeamID = (int)$_REQUEST["dutyteam"];
$permissions = getUserRolePermissions($pageid, $intTeamID);

$intDutyID = (int)$_REQUEST['DutyID'];
$strDutyName = trim($_REQUEST['DutyName']);
$intDutyType = (int)$_REQUEST["DutyTypeID"];
$strHistory = $_REQUEST["History"];
$intDBDutyType = ($intDutyType + 1);
$strDurationHour = $_REQUEST["durationhour"];
$strDurationMinute = $_REQUEST["durationminute"];
$strBreakTimeHour = $_REQUEST["breakTimeHour"];
$strBreakTimeMinute = $_REQUEST["breakTimeMinute"];
$intDutyColour = (int)$_REQUEST["dutycolour"];
$labelId1 = $_REQUEST["labelIds"][0] ?? 0;
$labelId2 = $_REQUEST["labelIds"][1] ?? 0;
$labelId3 = $_REQUEST["labelIds"][2] ?? 0;
$labelId4 = $_REQUEST["labelIds"][3] ?? 0;
$labelId5 = $_REQUEST["labelIds"][4] ?? 0;
$labelId6 = $_REQUEST["labelIds"][5] ?? 0;
$isNightDuty = (int)$_REQUEST["isNightDuty"];
$strLastMod = '';
$intDuration = (((int)$strDurationHour * 3600) + ((int)$strDurationMinute * 60));
$strBreakTime = (((int)$strBreakTimeHour * 3600) + ((int)$strBreakTimeMinute * 60));
if (($permissions->canmodify == 1) || ($permissions->cancreate == 1)) {
    list($intStatus, $strStatus, $NewMiscDutyId) = InsUpdMiscellaneousDutyDetails($intDutyID,$strDutyName,$intTeamID,$intDutyType,$intDuration,$intDutyColour,$strHistory,$strBreakTime,$labelId1,$isNightDuty, $labelId2, $labelId3, $labelId4, $labelId5, $labelId6);
    if($intStatus == 0){
        $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus, 'NewMiscDutyId' => $NewMiscDutyId);
    }
    else{
        $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus, 'NewMiscDutyId' => $NewMiscDutyId);
    }
}
else {
    $intStatus = 0;
    $response_array = array('status' => 'fail', 'sqlstatus' => $intStatus, 'sqlstatusstring' => 'You do not have modify privileges');
}

header('Content-type: application/json');
echo json_encode($response_array);
