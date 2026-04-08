<?php
session_start();
include_once 'init.php';
include_once 'genericfunctions.php';
$staffnumber = $_SESSION['allocations']['staffnumber'];

if (isset($_SESSION['filter']['type'])) {
  $filtertype = $_SESSION['filter']['type'];
  echo  $filtertype;
  if ($filtertype == 0) {
    SetFilter (-1, 0, $staffnumber);  
  }
}


?>