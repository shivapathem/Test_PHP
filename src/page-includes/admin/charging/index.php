<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
  }
include_once '../../../function-includes/testaccess.php';
include_once '../../../class-includes/userRolePermissions.php';
require_once '../../../function-includes/DBHelper.php';
include_once 'controller.php';
include_once 'error/errorContainer.php';
require_once '../../../page-includes/allocations/weekly/service/AllocationService.php';
$conrollerName = $_POST['conrollerName'] ? $_POST['conrollerName'] : '';
$requestData = $_POST;
$obj = new ControllerClass();
$obj->$conrollerName($requestData);