<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();

$term = $_REQUEST['term'];
$date = $_REQUEST['date'];
$intDepartmentID = $_REQUEST['departmentID'];
$week = bbcweeknumber($date);
$day = getdayofweek($date);

$sql = "SELECT        Programme as value, Programme as id
        FROM          dbo.Jobs
        WHERE         (WeekNumber = $week)
        AND           (iDay = $day)
        AND           DepartmentID = $intDepartmentID
        GROUP BY      Programme
        HAVING        (Programme LIKE '%$term%')
        ORDER BY Programme";

$rsProgs = sqlsrv_query($db, $sql);
$arrNetworks = [];
while($row = sqlsrv_fetch_array($rsProgs)){
    $arrNetworks[] = $row;
}

//while ($row = sqlsrv_fetch_array($rsProgs)) {
//  $arrNetworks[]['value'] = $row['Programme'];
//  $arrNetworks[]['id'] = $row['Programme'];
//}

$encoded = json_encode($arrNetworks);
if ($encoded!='[false]') {
  echo $encoded;

}

