<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';


if(isset($_REQUEST["teamId"]) && !empty($_REQUEST["teamId"])){
    $ddldutyteamId = isset($_REQUEST["teamId"]) ? $_REQUEST["teamId"] : 0;
    $intDutyColourID = isset($_REQUEST["dutyColourID"]) ? $_REQUEST["dutyColourID"] : 0;
    $dutyId = isset($_REQUEST["dutyId"]) ? $_REQUEST["dutyId"] : 0;
     $rsColours = GetDutyColourList($ddldutyteamId);
     $ColourOptions = PopulateDutyColoursDropDown($rsColours, $intDutyColourID, $dutyId);
      echo json_encode(array("status" => 'success', "ColourOptions" => $ColourOptions));
 }