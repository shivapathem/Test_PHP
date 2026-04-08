<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//in this file we are handling the create/edit,history action of divsiosns 
include_once 'classDivisions.php';
include_once '../../../../function-includes/DB_Functions.php';

//call the class divisions
$divisionobj = new ClassDivisions();
$action = $_REQUEST['action'] ?? '';
$update = 'update';
/* For Create/Edit Divisions*/
if ($action == 'openmodal') {
	$divisionId = $_REQUEST['divisionId'];
    if ($divisionId > 0) {
        $divisiondetails = json_decode($divisionobj->getDivisionsList($divisionId), true);
        $divisiondetails['title'] = 'Edit Area';
        $divisiondetails['button'] = 'Update Area';
        $divisiondetails['actionbutton'] = $update;
        $divisiondetails['EffectedFrom'] = date("d-m-Y", strtotime($divisiondetails['EffectedFrom']));
        echo json_encode(array("status" => 'success', "divisiondetail" => $divisiondetails));

    } else {
        $divisiondetails['title'] = 'New Area';
        $divisiondetails['button'] = 'Create Area';
        $divisiondetails['EffectedFrom'] = date("d-m-Y");
        $divisiondetails['actionbutton'] = 'create';
        echo json_encode(array("status" => 'success', "divisiondetail" => $divisiondetails));
    }
}
/* check unique divisions name validation */
if ($action == strtolower('validation')) {
 
    $divisionId = $_REQUEST['divisionid'];
    $divisionName = $_REQUEST['divisionname'];
    $isDivisionName = $divisionobj->checkDivisionNameValidation($divisionId, $divisionName);
    echo json_encode($isDivisionName);
}
/* create division record*/
if (isset($_REQUEST['js_actionbutton']) && $_REQUEST['js_actionbutton'] == strtolower('create')) {
    //define the params
    $params = [];
    $params['action'] = strtolower('insert');
    $params['divisionid'] = 0;
    $params['notes'] = $_REQUEST['notes'];
    $params['divisionname'] = $_REQUEST['divisionname'];
    $params['isactive'] = isset($_REQUEST['isactive']) ? true : false;
    $params['effectivedate'] = date("d-m-Y");
    //INSERT 
    $divisionResult = $divisionobj->insertUpdateDivision($params);
    echo $divisionResult;
}

/* update division record */
if (isset($_REQUEST['js_actionbutton']) && $_REQUEST['js_actionbutton'] == strtolower($update)) {
    //define the params
    $params = [];
    $params['action'] = strtolower($update);
    $params['divisionid'] = $_REQUEST['js_divisionid'];
    $params['notes'] = $_REQUEST['notes'];
    $params['divisionname'] = $_REQUEST['divisionname'];
    $params['isactive'] = isset($_REQUEST['isactive']) ? true : false;
    $params['effectivedate'] = date("d-m-Y");
    //update query
    $divisionResult = json_decode($divisionobj->insertUpdateDivision($params),true);
    $divisionResult['isactiveval'] = $params['isactive'];
   
    echo json_encode($divisionResult);
}

/* set active/deactive  divisions*/
if ($action == strtolower('isactive')) {
    //define the params
    $params = [];
    $params['action'] = strtolower('isactive');
    $params['divisionid'] = $_REQUEST['divisionid'];
    $params['notes'] = '';
    $params['divisionname'] = '';
    $params['isactive'] = $_REQUEST['actionValue'];
    $params['effectivedate'] = date("Y-d-m");
    //update query
    $divisionResult = $divisionobj->insertUpdateDivision($params);
    echo $divisionResult;
}
