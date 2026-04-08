<?php

//in this file we are handling the create/edit,history action of Master Colour 
include_once 'classColorMaster.php';
include_once '../../../../function-includes/DB_Functions.php';

//call the class ColorMaster
$MasterDutyColorobj = new classColorMaster();
$action = '';
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
}
$_REQUEST['js_actionbutton'] = $_REQUEST['js_actionbutton'] ?? '';
/* For Create/Edit Master Colour*/
if ($action == 'openmodal') {
    if (isset($_REQUEST['colourId']) && $_REQUEST['colourId'] > 0) {
        $colourdetails = json_decode($MasterDutyColorobj->getColorsList($_REQUEST['colourId']), true);
        $colourdetails['title'] = 'Update Colour';
        $colourdetails['button'] = 'Update';
        $colourdetails['actionbutton'] = 'update';
        echo json_encode(array("status" => 'success', "colourdetail" => $colourdetails));

    } else {
        $colourdetails['title'] = 'Save Colour';
        $colourdetails['button'] = 'Save';
        $colourdetails['actionbutton'] = 'create';
        echo json_encode(array("status" => 'success', "colourdetail" => $colourdetails));
    }
}
/* check unique Master Colour name validation */
if ($action == strtolower('validation')) {
 
    $divisionId = $_REQUEST['divisionid'];
    $divisionName = $_REQUEST['divisionname'];
    $isDivisionName = $MasterDutyColorobj->checkDivisionNameValidation($divisionId, $divisionName);
    echo json_encode($isDivisionName);
}
/* create Master Colour record*/
if ($_REQUEST['js_actionbutton'] == strtolower('create')) {
    //define the params
    $params = [];
    $params['colourid'] = 0;
    $params['actionname'] = 'insert';
    $params['divisionid'] = $_REQUEST['DivisionID'];
    $params['colourname'] = trim($_REQUEST['ColourName']);
    $params['colournotes'] = trim($_REQUEST['ColourNotes']);
    $params['backgroundcolour'] = str_replace('#','',$_REQUEST['ColourBackground']);
    $params['fontcolour'] = str_replace('#','',$_REQUEST['ColourFont']);
    $params['isdefault'] = 0;
    $params['isactive'] = 1;
    $params['username'] = $MasterDutyColorobj->userName;
    $params['createdby'] = $MasterDutyColorobj->userID;
    $params['modifyby'] = $MasterDutyColorobj->userID;
    //INSERT 
    $colourResult = $MasterDutyColorobj->insertUpdateColour($params);
    echo $colourResult;
}

/* update Master Colour record */
if ($_REQUEST['js_actionbutton'] == strtolower('update')) {
    //define the params
    $params = [];
    $params['colourid'] = $_REQUEST['js_colourid'];
    $params['actionname'] = strtolower($_REQUEST['js_actionbutton']);
    $params['divisionid'] = $_REQUEST['DivisionID'];
    $params['colourname'] = trim($_REQUEST['ColourName']);
    $params['colournotes'] = trim($_REQUEST['ColourNotes']);
    $params['backgroundcolour'] = str_replace('#','',$_REQUEST['ColourBackground']);
    $params['fontcolour'] = str_replace('#','',$_REQUEST['ColourFont']);
    $params['isdefault'] = (int) $_REQUEST['js_isDefault'];
    $params['isactive'] = (int) $_REQUEST['js_isActive'];
    $params['username'] = $MasterDutyColorobj->userName;
    $params['createdby'] = $MasterDutyColorobj->userID;
    $params['modifyby'] = $MasterDutyColorobj->userID;
    //update query
    $colourResult = json_decode($MasterDutyColorobj->insertUpdateColour($params),true);
   
    echo json_encode($colourResult);
}

/* set default status of Master Color*/
if ($action == strtolower('isdefault')) {
    //define the params
    $params = [];
    $params['colourid'] = $_REQUEST['colourid'];
    $params['actionname'] = strtolower('isdefault');
    $params['divisionid'] = $_REQUEST['divisionid'];
    $params['colourname'] = '';
    $params['colournotes'] = '';
    $params['backgroundcolour'] = '';
    $params['fontcolour'] = '';
    $params['isdefault'] = (int) $_REQUEST['actionValue'];
    $params['isactive'] = 1;
    $params['username'] = $MasterDutyColorobj->userName;
    $params['createdby'] = $MasterDutyColorobj->userID;
    $params['modifyby'] = $MasterDutyColorobj->userID;
    //update query
    $colourResult = $MasterDutyColorobj->insertUpdateColour($params);
    echo $colourResult;
}

/* set active/deactive status of Master Color*/
if ($action == strtolower('updatestatus')) {
    //define the params
    $params = [];
    $params['colourid'] = $_REQUEST['colourid'];
    $params['actionname'] = strtolower('updatestatus');
    $params['divisionid'] = 0;
    $params['colourname'] = '';
    $params['colournotes'] = '';
    $params['backgroundcolour'] = '';
    $params['fontcolour'] = '';
    $params['isdefault'] = 0;
    $params['isactive'] = (int) $_REQUEST['status'];
    $params['username'] = $MasterDutyColorobj->userName;
    $params['createdby'] = $MasterDutyColorobj->userID;
    $params['modifyby'] = $MasterDutyColorobj->userID;
    //update query
    $colourResult = $MasterDutyColorobj->insertUpdateColour($params);
    echo $colourResult;
}
