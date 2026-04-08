<?php
include_once '../../../function-includes/helpers.php';
include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/delete-unallocated-duty-process.php';


$dutyid = isset($_POST['dutyid']) ? trim($_POST['dutyid']) : '';
$team_id = isset($_POST['teamid']) ? trim($_POST['teamid']) : '';
$weeknum = isset($_POST['weeknum']) ? trim($_POST['weeknum']) : '';
$isShiftleader = isset($_POST['isShiftleader']) ? trim($_POST['isShiftleader']) : '';

if (isset($_POST['action'])){
   echo deleteUnallocatedDutyFromTeam($dutyid,$team_id,$weeknum , $isShiftleader);

}
