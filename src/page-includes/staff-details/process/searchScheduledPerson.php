<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../process/classScheduledPerson.php';
include_once '../../../class-includes/userRolePermissions.php';
include_once '../../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$userDivisionsList = json_decode($setupObj->getUserDivisions(), true);
$arrUsersTeamdata = json_decode($setupObj->getUserSetupByIdNetlogin($type='menu'), true);
$intSysAdmin =  $_SESSION['user']['SysAdmin'] ?? 0;
if (($intSysAdmin == 1) || !empty($userDivisionsList) || ($arrUsersTeamdata["isSchedulingTeamAdmin"] == 1) || ($arrUsersTeamdata["isScheduler"] == 1)) {
//define the perms
$pageid = 6;
$selectedTeamID = $_POST['selectedTeamID'];
$selectedUserName = $_POST['selectedUserName'];
$actionType = $_POST['actionType'];
$excludeNoTeam = $_POST['excludeNoTeam'];
// Call User Permission function.
//call the class object
$scheduleobj = new classScheduledPerson;

$scheduledPeopleLists = json_decode($scheduleobj->getSearchScheduledPerson($selectedUserName, $selectedTeamID, $actionType, $excludeNoTeam), true);
$addlistview = '';
if (!empty($scheduledPeopleLists)) {  
$editActive = '';
if(!empty($selectedTeamID))
{
	$editActive = getPermission($pageid, $selectedTeamID);
}	
    //run the team loop start
    foreach ($scheduledPeopleLists as $key => $scheduledPeople) { 
		if(empty($selectedTeamID))
		{
			$editActive = getPermission($pageid, $scheduledPeople['TeamID']);
		}
        $addlistview .= '<tr onMouseOver="highlightTableRow(this)" onMouseOut="unHighlightTableRow(this)">';
        $addlistview .= '<td>' . $scheduledPeople['DisplayName'] . '</td>';
        $addlistview .= '<td>' . $scheduledPeople['Forename'] . '</td>';
        $addlistview .= '<td>' . $scheduledPeople['Surname'] . '</td>';
        $addlistview .= '<td>' . $scheduledPeople['NetLogin'] . '</td>';
        $addlistview .= '<td>' . $scheduledPeople['InternalEmail'] . '</td>';
        $addlistview .= '<td>' . $scheduledPeople['TeamName'] . '</td>';
        $addlistview .= '<td><a  id ="js_viewbutton" class="viewaction js_viewbutton" href="javascript:void(0)" title="View" value="' . $scheduledPeople['ScheduledPersonID'] . '" onClick="CreateScheduledPerson(\'view\', ' . $scheduledPeople['ScheduledPersonID'] . ', ' . $scheduledPeople['TeamID'] . ')"><i class="fa fa-eye"></i></a> &nbsp;&nbsp; <a id="js_editbutton" href="javascript:void(0)" class="editaction ' . $editActive . ' js_editbutton" title="Edit" value="' . $scheduledPeople['ScheduledPersonID'] . '" onClick="CreateScheduledPerson(\'edit\', ' . $scheduledPeople['ScheduledPersonID'] . ', ' . $scheduledPeople['TeamID'] . ')"><i class="fa fa-edit"></i></a>';
        $addlistview .= ' </tr>';
    }
    //run the team loop stop
} elseif (empty($scheduledPeopleLists)) {
    $addlistview .= '<tr>
                    <td colspan="7" class="filter_data_unavailabl">No records to display.</td>   
                    </tr>';
}
 else {
    $addlistview .= '<tr>
                    <td colspan="7" class="filter_data_unavailabl">Please Apply filter to display records.</td>   
                    </tr>';
}

$response_array = array('status' => 'success', 'view' => $addlistview);

header('Content-type: application/json');
echo json_encode($response_array);
} else {
    $response_array = array('status' => '', 'view' => 'Access Denied');
    header('Content-type: application/json');
    echo json_encode($response_array);
}

function getPermission($pageid, $selectedTeamID)
{
	$editActive = '';
	$permissions = getUserRolePermissions($pageid, $selectedTeamID);
		//set the permissions
	if ($permissions->canmodify == 0 )
	{
		$editActive = 'activeclass';
	}
	return $editActive;
}