<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
$strStaffLogin = $_REQUEST['login'];
$intTypeID = $_REQUEST['typeid'];
$intAction = $_REQUEST['action'];
$strAdminLogin = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$pdo = OpenDBLinkA7();
if ($intAction == 1) {  
  try {
    $strQuery ="exec [dbo].[usp_InsAddStaffToRestrictedRequestType] :intTypeID,:strStaffLogin,:strAdminLogin";  
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intTypeID', $intTypeID, PDO::PARAM_INT);
    $stmt->bindParam(':strStaffLogin', $strStaffLogin, PDO::PARAM_STR);
    $stmt->bindParam(':strAdminLogin', $strAdminLogin, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['ErrorMessage'];  
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
}
else {
  try {
    $strQuery ="exec [dbo].[usp_InsRemoveStaffFromRestrictedRequestType] :intTypeID,:strStaffLogin,:strAdminLogin";  
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':intTypeID', $intTypeID, PDO::PARAM_INT);
    $stmt->bindParam(':strStaffLogin', $strStaffLogin, PDO::PARAM_STR);
    $stmt->bindParam(':strAdminLogin', $strAdminLogin, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['ErrorMessage'];  
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }

}

?>