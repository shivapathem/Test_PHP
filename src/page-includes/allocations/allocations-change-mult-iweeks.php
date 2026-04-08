<?php
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/helpers.php';
include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';
$commonObj = new classCommonDBFunctions();
$action = $_REQUEST['action'];
$TeamId = $_REQUEST['TeamId'];
echo $commonObj->changeMultiWeek($action,$TeamId);

?>