<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/userGroupfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

  $intOption = $_REQUEST["option"];
  InsertWeeklyFilterOption($strUser, $intOption);

 
  