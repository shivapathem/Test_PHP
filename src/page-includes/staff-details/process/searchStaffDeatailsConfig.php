<?php
//Scheduled Person 
include_once '../../../function-includes/DBHelper.php';
include_once '../process/classCreateSchedulePerson.php';

//define the perms
$searchForeName = $_REQUEST['selectedForeName'];
$searchNetLogin = $_REQUEST['selectedNetLogin'];
$searchSurName = $_REQUEST['selectedSurName'];
$searchStaffNumber = $_REQUEST['selectedStaffNumber'];
$actionType = $_REQUEST['actionType'];

$addListView = '';
if ($actionType != 'search') {
    $addListView .= '<tr>
        <td colspan="7" class="filter_data_unavailabl">Please Apply filter to display records.</td>   
    </tr>';
} else {
    //call the class object

    $creaSchePersobj = new classCreateSchedulePerson;
    $staffDeatailsLists = json_decode($creaSchePersobj->getStaffDetailsConfig($searchForeName, $searchNetLogin, $searchSurName, $searchStaffNumber), true);


    if (!empty($staffDeatailsLists)) {
        //run the team loop start

        foreach ($staffDeatailsLists as $key => $staffDeatail) {
            $button = 'Linked';
            $scheduledType = 1;
            $StaffID = $staffDeatail['StaffID'] ?? 0;
            $ScheduledPersonID = $staffDeatail['ScheduledPersonID'] ?? 0;
            if($staffDeatail['ScheduledPersonID'] != ''){
                $scheduledType = $creaSchePersobj->checkScheduledType($staffDeatail['ScheduledPersonID']);

            }
            if (($staffDeatail['StaffDetailsID'] == '') || ($scheduledType == 0)) {
                $userType = $staffDeatail['userType'] != null ? $staffDeatail['userType'] : 'Freelancer';
                $buttonlink ='<a class="btn-class staffattach" onclick="staffattachonClickAttach('.$StaffID.','.$ScheduledPersonID.')" id ="js_attach" data-staffid=' . $StaffID . '>';
                if ($userType == 'Freelencer') {
                    if ($staffDeatail['IsConfig'] == 1) {
                        $button = $buttonlink.'Attach-TP</a>';
                    }
                }
                else {
                    if ($staffDeatail['IsConfig'] == 1 && $staffDeatail['IsLinked']  == 0) {
                        $button = $buttonlink.'Attach-TP</a>';
                    } else if($staffDeatail['IsLinked']  == 0){
                        $button = $buttonlink.'Attach-AD</a>';
                    }
                }
            }

            $addListView .= '<tr>';
            $addListView .= '<td>' . $staffDeatail['StaffNumber'] . '</td>';
            $addListView .= '<td>' . $staffDeatail['NetLogin'] . '</td>';
            $addListView .= '<td id="forename_'.$StaffID.'">' . $staffDeatail['Forename'] . '</td>';
            $addListView .= '<td id="surname_'.$StaffID.'">' . $staffDeatail['Surname'] . '</td>';
            $addListView .= ' <td class="btntbl">' . $button . '</td>';
            $addListView .= ' </tr>';

        }
        //run the team loop stop
    } else {
        $addListView .= '<tr>
        <td colspan="7" class="filter_data_unavailabl">No matching records found.</td>   
    </tr>';
    }
}


$response_array = array('status' => 'success', 'view' => $addListView);

header('Content-type: application/json');
echo json_encode($response_array);

