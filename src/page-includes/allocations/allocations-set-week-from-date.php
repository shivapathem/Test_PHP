<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';
$commonObj = new classCommonDBFunctions();
$strDate = $_REQUEST['date'];
$intWeekNumberArray = $commonObj->GetWeekNoAndIDayByDateFromTimeDim($strDate);
$intWeekNumber =$intWeekNumberArray['ixYearWeek'];
$_SESSION["allocations"]["WeekNumber"] = $intWeekNumber;
echo $intWeekNumber;