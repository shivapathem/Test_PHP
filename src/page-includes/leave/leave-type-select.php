<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';

$commonDbobj = new classCommonDBFunctions();
$intLeaveGroup = $_REQUEST['leavegroup'] ?? 0;
$selectedUser = $_REQUEST['userNetlogin'];
$adminUser = GetUserLogon();
$arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLoginandRequesterID($adminUser, $selectedUser), true);
if (isset($arrUserSettings['LeaveRequests']) && is_array($arrUserSettings['LeaveRequests'])) {
    $arrGroupsCanRequest = $arrUserSettings['LeaveRequests'];
} else {
    $arrGroupsCanRequest = [];
}
echo '<select size="1" name="leavetype" id="leavetype">';
if (isset($arrUserSettings['LeaveRequests'][$intLeaveGroup]['LeaveTypes']) && is_array($arrUserSettings['LeaveRequests'][$intLeaveGroup]['LeaveTypes'])) {
    foreach ($arrUserSettings['LeaveRequests'][$intLeaveGroup]['LeaveTypes'] as $intTypeID => $strType) {
        echo '<option value="'.$intTypeID.'">'.$strType.'</option>';
    }
} else {
    echo '<option value="">No leave types available</option>';
}

echo '</select>';
