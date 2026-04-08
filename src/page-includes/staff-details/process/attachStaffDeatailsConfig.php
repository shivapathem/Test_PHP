<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//Scheduled Person 
include_once '../../../function-includes/DBHelper.php';
include_once '../process/classCreateSchedulePerson.php';


$userID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
//define the perms
$staffID = $_POST['selectedstaffID'];
$actionType = $_POST['actionType'] == null ? 'select' : $_POST['actionType'];
$secheduledPersonId = $_POST['schedPersonID'];
$oldschedPersonID = $_POST['oldschedPersonID'];
$status = 'success';
$returnString = '';

//call the class
$creaSchePersobj = new classCreateSchedulePerson;
switch($actionType){
        case 'save':
            $staffDetailsLists = json_decode($creaSchePersobj->attachStaffDetailsConfig($secheduledPersonId, $staffID, $actionType, $status, $returnString,$userID, $oldschedPersonID), true);
            $status = $staffDetailsLists ['strstatus'];
            $returnString = $staffDetailsLists ['strreturnstring'];
        break;

        case 'select':
             $staffDetailsLists = json_decode($creaSchePersobj->attachStaffDetailsConfig($secheduledPersonId, $staffID, $actionType, $status, $returnString,$userID, $oldschedPersonID), true);
            break;
        
        case 'cancel':
            $staffDetailsLists['StaffNumber'] = $staffDetailsLists['PreferredForename'] = '';
            $staffDetailsLists['Surname'] = $staffDetailsLists['Forename'] = $staffDetailsLists['NetLogin'] = '';
            $staffDetailsLists['JobTitle'] = '';
            break;
    }
	
$title = '';
$preferredName = (isset($staffDetailsLists['PreferredForename'])) ? $staffDetailsLists['PreferredForename'] : '';
$staffNumber = (isset($staffDetailsLists['StaffNumber'])) ? $staffDetailsLists['StaffNumber'] : '';
$netLogin = (isset($staffDetailsLists['NetLogin'])) ? $staffDetailsLists['NetLogin'] : '';
$foreName = (isset($staffDetailsLists['Forename'])) ? $staffDetailsLists['Forename'] : '';
$surName = (isset($staffDetailsLists['Surname'])) ? $staffDetailsLists['Surname'] : '';
$jobTitle = (isset($staffDetailsLists['JobTitle'])) ? $staffDetailsLists['JobTitle'] : '';


$addListView = '';
$addListView .= '
            <tr id="STFdtl">
                <td>Title</td>
                <td id="js_title">' . $title . '</td>
            </tr>
            <tr class="STFdtl">
                <td>Staff Number</td>
                <td id="js_staffnumber">' . $staffNumber . '</td>
            </tr>
            <tr class="STFdtl">
                <td>Network ID</td>
                <td id="js_netlogin">' . $netLogin . '</td>
            </tr>
            <tr class="STFdtl">
                <td>Forename</td>
                <td id="js_forename">' . $foreName . '</td>
            </tr>
            <tr class="STFdtl">
                <td>Surname</td>
                <td id="js_surname">' . $surName . '</td>
            </tr>
            <tr class="STFdtl">
                <td>Middle Name</td>
                <td id="js_midname">' . $title . '</td>
            </tr>
            <tr class="STFdtl">
                <td>Preferred Name</td>
                <td id="js_prefname">' . $preferredName . '</td>
            </tr>
            <tr class="STFdtl">
                <td>Designation</td>
                <td id="js_designation">' . $jobTitle . '</td>
            </tr>';


$response_array = array('status' => $status, 'view' => $addListView, 'message' => $returnString);

header('Content-type: application/json');
echo json_encode($response_array);

