<?php
session_start();
require_once __DIR__ . '/../service/AllocationViewRepository.php';
$repositoryObj = new AllocationViewRepository();
$TeamID = $_REQUEST['TeamID']??0;
$current_User_id = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$res=0;
if(!empty($TeamID)){
$res = $repositoryObj->CountUnExportedRota($TeamID);
}
if($res) {
    $response['count']=$res;
    $response['status']=1;
}else{
    $response['count']=0;
    $response['status']=0;
}
            
echo json_encode($response);