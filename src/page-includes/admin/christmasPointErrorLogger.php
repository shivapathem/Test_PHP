<?php 
session_start();
include_once '../../function-includes/helpers.php';

$status = $_REQUEST['status'];
$teamId = $_REQUEST['teamId'];

logger('ExtraXmasPoint')->critical('Status:'.$status.',TeamID:'.$teamId);
echo json_encode(array('status'=>'success'));
exit;
?>