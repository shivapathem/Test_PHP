<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/leavefunctions.php';

$LeaveDate = $_POST['leaveDate'];
$Sent = $_POST['checkedstatus'];
$sentOption = $_POST['sentOption'];
$Netlogin = $_POST['Netlogin'];

$result = UpdateSendOptionInLeaveApplication($LeaveDate, $Sent, $sentOption, $Netlogin);
if( $result == 1 ){
echo 'data updated successfully';
}else{
    echo 'some error ocurred';
}
?>