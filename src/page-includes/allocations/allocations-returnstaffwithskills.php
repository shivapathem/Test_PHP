<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$db = OpenDatabase();

$term = $_REQUEST['term'];
$intDepartmentID = $_REQUEST['department'];

$sql = "SELECT  programmename as value, id FROM skills_programmes
        where (programmename LIKE '%$term%') AND DepartmentID = $intDepartmentID 
        ORDER BY programmename";

$allocations = sqlsrv_query($db, $sql);
$out = []; $rs = [];
while($rs = sqlsrv_fetch_array($allocations)){
  $out[] = $rs;
}

// print_r($out);


$encoded = json_encode($out);
if ($encoded!='[false]') {
  echo $encoded;

}

