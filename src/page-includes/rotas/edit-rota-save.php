<?php
/*

* objective: Call function containing Database Insert, update and delete of rota using stored procedure
* Created By: Vipin Kushwaha
* Created Date: 05-04-2021
* Description: Call function containing Database Insert, update and delete of rota using stored procedure
* */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$intAreaID = $_SESSION['user']['AreaID'];
$intStaffID = $_SESSION['user']['StaffID'];
$intTeamID = isset($_REQUEST["ddlRotaTeam"]) && $_REQUEST["ddlRotaTeam"] != "" ? $_REQUEST["ddlRotaTeam"] : $_REQUEST["ddlRotaTeamhide"];

$pageid = 24;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid, $intTeamID);

$intType = 0;
$intShowForm = 1;
$intID = $_REQUEST["id"];
$intTask = $_REQUEST["task"];
$strRotaName = $_REQUEST["RotaName"];
$strRotaNotes = $_REQUEST["RotaNotes"];
$strRotaStart = $_REQUEST["startdate"];
$strRotaWeeks = $_REQUEST["rotaweeks"];
$intNewRotaStartsWeek = isset($_REQUEST["newRotaStartsWeek"]) ? $_REQUEST["newRotaStartsWeek"] : 0;
$stroldRotaName = $_REQUEST["oldRota"];
$stroldRotaNotes = $_REQUEST["oldNotes"];
$stroldRotaWeeks = $_REQUEST["oldWeeks"];
$stroldRotaStart = $_REQUEST["oldStart"];
$strUser = $_SESSION['user']['FullName'];
$strLastMod = $_REQUEST["LastModDate"];
$strHistory = '';
$intStartWeek = bbcweeknumberrota($strRotaStart);
if ($intTask == 1) {
  if ($stroldRotaName != $strRotaName) {
    $strHistory .= PHP_EOL .'-- Rota Name changed from ['.$stroldRotaName.'] to ['.$strRotaName.']';
  }
  if ($stroldRotaNotes != $strRotaNotes) {
      $strHistory .= PHP_EOL . '-- Rota Notes changed from ['.$stroldRotaNotes.'] to ['.$strRotaNotes.']';
  }
  if ($stroldRotaStart != $strRotaStart) {
    $strHistory .= PHP_EOL . '-- Rota Start Date changed from ['.$stroldRotaStart.'] to ['.$strRotaStart.']';
  }
  if ($stroldRotaWeeks != $strRotaWeeks) {
    $strHistory .= PHP_EOL . '-- Rota Weeks changed from ['.$stroldRotaWeeks.'] to ['.$strRotaWeeks.']';
  }
}

if (($permissions->canmodify == 1) || ($permissions->cancreate == 1)) {
    list($intStatus, $strStatus, $intNewID) = InsUpdRotaName($intID, $intTask, $intAreaID, $intTeamID, $strRotaName, $strRotaNotes, $strRotaStart, $intStartWeek, $strRotaWeeks, $strLastMod, $strUser, $strHistory, $intNewRotaStartsWeek);
    $response_array = array('status' => 'success', 'sqlstatus' => $intStatus, 'sqlstatusstring' => $strStatus, 'sqlnewID' => $intNewID);
}
else{
    $response_array = array('status' => 'failed', 'sqlstatus' => 0, 'sqlstatusstring' => 'You do not have permission to save Rota Pattern.', 'sqlnewID' => 0);
}
header('Content-type: application/json');
echo json_encode($response_array);
