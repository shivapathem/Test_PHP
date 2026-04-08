<?php
session_start();

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';

$dutyid = $_REQUEST['dutyid'] ;
$dutytype = $_REQUEST['dutytype'] ;
$action = $_REQUEST['action'];
$time = time();

if ($dutytype == 0) {
$query = "SELECT StaffNumber, DutyName, WeekNumber, iDay, StartTime, EndTime
          FROM Allocations
          WHERE (id = $dutyid)
          AND AllocateInstanceID = " . getCurrentInstanceId();
}
else {
$query = "SELECT StaffNumber, DutyName, WeekNumber, iDay, StartTime, EndTime
          FROM Allocations_webedit
          WHERE (id = $dutyid)
          AND AllocateInstanceID = " . getCurrentInstanceId();
}
$db = OpenDatabase();
$allocations = sqlsrv_query($db, $query);

$row = sqlsrv_fetch_array($allocations);

$staffnumber = $row["StaffNumber"];
$dutyname = $row["DutyName"];
$weeknumber = $row["WeekNumber"];
$iday = $row["iDay"];
$starttime = gmdate("H:i", ($row["StartTime"] * 86400) + 1);
$starttimesecs = ($row["StartTime"] * 86400) + 1;
$endtime = gmdate("H:i", ($row["EndTime"] * 86400) + 1);

$intAllowSecs = (date("H") * 3600) +  (date("i") * 60) + 3600;

$allowInBuilding = 1;
if ($action == 2) {
  if ($starttimesecs >= $intAllowSecs) {
    $allowInBuilding = 0;
  }
}

$query = "SELECT  id, staffnumber, history, iWeek, iDay, starttime, endtime
          FROM signin
          WHERE  (staffnumber = N'$staffnumber')
          AND (iWeek = N'$weeknumber')
          AND (iDay = $iday)
          AND AllocateInstanceID = " . getCurrentInstanceId();

$allocations = sqlsrv_query($db, $query, [], ["Scrollable"=>"buffered"]);
  // Row Not returned

if (sqlsrv_num_rows($allocations) == 0) {
  if ($allowInBuilding == 1) {
    if ($action == 2) {
      $history = '<font color="#00FF00">Signed in for:<br><b>'. $dutyname.'</b> ('.$starttime .'-'.$endtime
                 .')<br>Also marked as in the building.<br>By '.escapeSingleQuotes($_SESSION['allocations']['fullname']).'</font>';
      $inBuilding = 1;
    }
    else {
      $history = '<font color="#00FF00">Signed in for:<br><b>'. $dutyname.'</b> ('.$starttime .'-'.$endtime
                 .')<br>By '.escapeSingleQuotes($_SESSION['allocations']['fullname']).' on '. date("d/m/y") .' at '.date("H:i").'</font>';
      $inBuilding = 0;
    }

    $query = "INSERT INTO
            signin(
            staffnumber,
            iWeek,
            iDay,
            inBuilding,
            starttime,
            endtime,
            history,
            LastUpdate, AllocateInstanceID)
            VALUES (
            N'$staffnumber',
            N'$weeknumber',
            $iday,
            $inBuilding,
            '$starttime',
            '$endtime',
            N'$history',
            $time, " . getCurrentInstanceId() . ")";

    sqlsrv_query($db, $query);
  }
}
else {
  if ($allowInBuilding == 1) {
    switch ($action) {
      case 1:
        $history = '<font color="#00FF00">Re-Signed in for:<br><b>'. $dutyname.'</b> ('.$starttime .'-'.$endtime.')
        <br>By '.$_SESSION['allocations']['fullname'].' on '. date("d/m/y") .' at '.date("H:i").'</font><hr>';
        $active = 1;
        $inBuilding = 0;
        break;
      case 0:
        $history = '<font color="#FF0000">Marked as not signed in
        <br>By '.$_SESSION['allocations']['fullname'].' on '. date("d/m/y") .' at '.date("H:i").'</font><hr>';
        $active = 0;
        $inBuilding = 0;
        break;
      case 2:
        $history = '<font color="#00FF00">Marked as in the building
        <br>By '.$_SESSION['allocations']['fullname'].'</font><hr>';
        $active = 1;
        $inBuilding = 1;
        break;
    }

    $query = "UPDATE signin
               SET
               history =  N'$history' + history,
               starttime = N'$starttime',
               endtime = N'$endtime',
               active = $active,
               inBuilding = $inBuilding,
               LastUpdate = $time
               WHERE  (staffnumber = N'$staffnumber')
               AND (iWeek = N'$weeknumber')
               AND (iDay = $iday)
               AND AllocateInstanceID = " . getCurrentInstanceId();
    
    sqlsrv_query($db, $query);
  }
}

echo  $allowInBuilding;

?>
