<?php
session_start();
require_once __DIR__ . '/../service/AllocationViewRepository.php';
$repositoryObj = new AllocationViewRepository();
$TeamID = $_REQUEST['TeamID']??0;
$current_User_id = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$res=0;
if(!empty($TeamID) && !empty($current_User_id)){
$res= $repositoryObj->makeExport($TeamID,$current_User_id);
}
if($res==1) {
    $response['status']=1;
    $response['msg']="Rota Exported Sucessfully.";
}elseif($res==2){
    $response['status']=2;
    $response['msg']="No Rota Found to Export.Please Check Once Again.";
}elseif($res==3){
    $response['status']=3;
    $response['msg']="Error While Creating Accounting Period Summary.";
}else{
    $response['status']=0;
    $response['msg']="Oops! Some Error Found in Export,Try Later";
}

echo json_encode($response);