<?php
session_start();
//Scheduled Person 

include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../process/classCreateSchedulePerson.php';


$autocompleteList = [];
$searchTermKey = $_REQUEST['termKey'];
$searchKey = $_REQUEST['term'];
$actionType = 'search';

switch ($searchTermKey) {
    case 'Forename':
        $searchForeName = $searchKey;
        $searchNetLogin = '';
        $searchSurName = '';
        $searchStaffNumber = '';
        break;

    case 'Surname':
        $searchForeName = '';
        $searchNetLogin = '';
        $searchSurName = $searchKey;
        $searchStaffNumber = '';
        break;

    case 'NetLogin':
        $searchForeName = '';
        $searchNetLogin = $searchKey;
        $searchSurName = '';
        $searchStaffNumber = '';
        break;
    case 'StaffNumber':
        $searchForeName = '';
        $searchNetLogin = '';
        $searchSurName = '';
        $searchStaffNumber = $searchKey;
        break;
}
//call the class object
$creaSchePersobj = new classCreateSchedulePerson;
$searchAutocompleteList = json_decode($creaSchePersobj->getStaffDetailsConfig($searchForeName, $searchNetLogin, $searchSurName, $searchStaffNumber, $actionType), true);

if (!empty($searchAutocompleteList)) {
    foreach ($searchAutocompleteList as $data) {
        $autocompleteList[$data[$searchTermKey]] = $data[$searchTermKey];
    }
}


header('Content-type: application/json');
echo json_encode($autocompleteList);
