<?php
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';

$intTeamID = $_REQUEST['teamid'];

$arrAllocLeaveTypes = GetLeaveAllocateTypes();
$intActiveLeaveYear = GetCurrentLeaveYearGeneric();

//fetch the leave carry data
$arrLeaveCarryOver = GetCarryOverLeaveData($intTeamID, $intActiveLeaveYear,$arrAllocLeaveTypes);
$sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  //insert carry over data
  if(!empty($arrLeaveCarryOver)){
    foreach($arrLeaveCarryOver as $staffnumber  => $carryoverleave){
      $returnResult = addCarryOverLeave($carryoverleave,$staffnumber,$intActiveLeaveYear,$intTeamID,$_SESSION['user']['FullName'],$sessUserId);
  
    }
}