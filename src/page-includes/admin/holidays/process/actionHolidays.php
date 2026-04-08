<?php

//in this file we are handling the create/edit,history action of divsiosns 
include_once 'classHolidays.php';
include_once '../../../../function-includes/DB_Functions.php';

//call the class holidays
$holidayobj = new ClassHolidays();
$action = '';
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
}

/* set active/deactive  holidays*/
if ($action == 'active') {
    //define the params
    $params = [];
    $params['action'] = 'active';
    $params['id'] = $_REQUEST['id'];
    $params['currentuserid'] = '';
    $params['currentstatus'] = '';
    //update query
    $holidayresult = $holidayobj->activeInactiveHoliday($params);
    echo $holidayresult;
}

/* Show week number */
if($action == 'weeknumber') {
	$indate = $_REQUEST['date'];
	$date = new DateTime($indate);
	echo $week = $date->format("W/Y");
}

/* Delete  holidays*/
if ($action == 'delete') {
    //define the params
    $params = [];
    $params['action'] = 'delete';
    $params['id'] = $_REQUEST['id'];
    $params['currentuserid'] = '';
    $params['currentstatus'] = '';
    //update query
    $holidayresult = $holidayobj->activeInactiveHoliday($params);
    echo $holidayresult;
}

/* create Holiday record*/
if ($_REQUEST['js_actionbutton'] == strtolower('create')) {
    //define the params
    $params = [];
    $params['action'] = strtolower('insert');
    $params['id'] = 0;
    $params['calenderyear'] = $_REQUEST['calenderyear'];
    $params['description'] = $_REQUEST['description'];
    $params['holidaydate'] = $_REQUEST['holidaydate'];
    $indate = $_REQUEST['holidaydate'];
	$date = new DateTime($indate);
    $week = $date->format("W/Y");

    if(isset($_REQUEST['weekno']) && $_REQUEST['weekno'])
	{
		$params['week'] = $_REQUEST['weekno'];
	}
    else
	{
		$params['week'] = $week;
	}

    //INSERT 
    $holidayobj = $holidayobj->insertUpdateHoliday($params);
    echo $holidayobj;
}

/* For Create/Edit Holidays*/
if ($action == 'openmodal') {
	$holidayid = $_REQUEST['holidayid'];
    if ($holidayid > 0) {
        $holidaydetails = json_decode($holidayobj->getHolidaysList($holidayid), true);
       	$holidaydetails['title'] = 'Edit Holiday';
        $holidaydetails['button'] = 'Update Holiday';
        $holidaydetails['actionbutton'] = 'update';
        echo json_encode(array("status" => 'success', "holidaydetails" => $holidaydetails));

    } else {
        $holidaydetails['title'] = 'New Holiday';
        $holidaydetails['button'] = 'Create Holiday';
        $holidaydetails['EffectedFrom'] = date("d-m-Y");
        $holidaydetails['actionbutton'] = 'Create';
        echo json_encode(array("status" => 'success', "holidaydetails" => $holidaydetails));
    }
}

/* Edit Holiday record*/
if ($_REQUEST['js_actionbutton'] == strtolower('edit')) {
    //define the params
    $params = [];
    $params['action'] = 'update';
    $params['id'] = $_REQUEST['js_holidayid'];
    $params['calenderyear'] = $_REQUEST['calenderyear'];
    $params['description'] = $_REQUEST['description'];
    $params['holidaydate'] = $_REQUEST['holidaydate'];
    $params['week'] = $_REQUEST['weekno'];
    //INSERT 
    $holidayobj = $holidayobj->insertUpdateHoliday($params);
    echo $holidayobj;
}