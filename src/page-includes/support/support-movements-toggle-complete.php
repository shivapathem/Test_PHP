<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$id = $_REQUEST['id'];
$action = $_REQUEST['action'];
$activitytype = $_REQUEST['activitytype'];

// activitytype
// 1 = Attachment Start
// 2 = Attachment End
// 3 = FWR Start
// 4 = FWR End
// 5 = FTC Start
// 6 = FTC End

$db = OpenDatabase();
$strHistory = 'Record updated by '.$_SESSION['user']['FullName'].' on '.date("jS M Y").' at '.date("H:i").'.<br>';
if ($action == 0) {
  $strHistory.= 'Marked as Not Complete';
}
else {
  $strHistory.= 'Marked as Complete';
}
$strHistory.='<hr>';
$strHistory = escapeSingleQuotes($strHistory);
      
      
switch ($activitytype) {
  case 1:
    $query = "UPDATE    staff_status
              SET       AttachStartComplete = $action,
                        History = '$strHistory'
              WHERE     (id = $id)";
              break;
  case 2:
    $query = "UPDATE    staff_status
              SET       AttachEndComplete = $action,
                        History = '$strHistory'
              WHERE     (id = $id)";
              break;              
  case 3:
    $query = "UPDATE    staff_status
              SET       FWRStartComplete = $action,
                        History = '$strHistory'
              WHERE     (id = $id)";
              break;
  case 4:
    $query = "UPDATE    staff_status
              SET       FWREndComplete = $action,
                        History = '$strHistory'
              WHERE     (id = $id)";
              break;              
  case 5:
    $query = "UPDATE    staff_status
              SET       FTCStartComplete = $action,
                        History = '$strHistory'
              WHERE     (id = $id)";
              break;
  case 6:
    $query = "UPDATE    staff_status
              SET       FTCEndComplete = $action,
                        History = '$strHistory'
              WHERE     (id = $id)";
              break;
}
  sqlsrv_query($db, $query);  

?>