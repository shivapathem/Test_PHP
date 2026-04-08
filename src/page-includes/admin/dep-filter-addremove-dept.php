<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';


$intFilterID = $_REQUEST['filterid'];
$intAction = $_REQUEST['action'];
$intbcid = $_REQUEST['deptid'];

$db = OpenDatabase();

    $strQuery = "SELECT   isnull(ExtraDepartments, '') as ExtraDepartments
                 FROM      AutoPagesFilters
                 WHERE   (ID = $intFilterID)";

  $rsFilter = sqlsrv_query($db, $strQuery);
  $row = sqlsrv_fetch_array($rsFilter);
  if ($row['ExtraDepartments'] != '') {
    $arrBCs = explode(",", $row['ExtraDepartments']);
  }
  else {
    $arrBCs = array();
  }
  
  
if ($intAction == 1) {
  //Remove
  foreach ($arrBCs as $intArrID => $intCurrBCID) {
     if ($intCurrBCID == $intbcid) {
       unset ($arrBCs[$intArrID]);
     }
  }
}
else {
  array_push($arrBCs, $intbcid);
}

  
  $strBCs = implode(',', $arrBCs);
  
  $strQuery = "UPDATE      AutoPagesFilters
               SET         ExtraDepartments = N'$strBCs'
               WHERE       (ID = $intFilterID)";
  
sqlsrv_query($db, $strQuery);
  

