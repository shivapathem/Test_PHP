<?php
//in this file we are handling the create/edit,history action of divsiosns 
include_once 'classSchedulingTeam.php';

$action = '';
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    
}

if ($action == 'openmodal' && (isset($_REQUEST['staffId']) && $_REQUEST['staffId'] > 0)) {        
       $bbcMailObj = new classSchedulingTeam();
       //update query
       $StaffDetails = $bbcMailObj->getEmailByStaffID($_REQUEST['staffId']);
       $ExternalEmail = $StaffDetails[0]['ExternalEmail'];
       $AltTelephone = $StaffDetails[0]['AltTelephone'];
       
echo json_encode(array("status" => 'success',"ExternalEmail" =>$ExternalEmail,"AltTelephone" =>$AltTelephone));
}

if ($action == 'savemail') {

    $params = [];
    $params['nonBBCPhone'] = $_REQUEST['nonBBCPhone'];
    $params['staffId'] = $_REQUEST['staffId'];
    $params['nonBBCEmail'] = $_REQUEST['nonBBCEmail'];
    $bbcMailObj = new classSchedulingTeam();
    //update query
  $EmailResult = $bbcMailObj->updateEmailByStaffID($params);
    echo json_encode(array("status" => 'success', "EmailResult" => $EmailResult));
 }



