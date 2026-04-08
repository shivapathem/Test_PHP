<?php
session_start();
//Scheduled Person 

include_once '../../../function-includes/DBHelper.php';
include_once '../../../function-includes/DB_Functions.php';
include_once '../process/classScheduledPerson.php';

//call the class object
$scheduleobj = new classScheduledPerson;
$teamID = $_REQUEST['teamid'];
$userName = $_REQUEST['term'];
$excludeNoTeam = $_REQUEST['excludeNoTeam'];
$pageid = 6;
$scheduledPeopleLists = json_decode($scheduleobj->getScheduledPeopleUserList($teamID, $userName,$pageid, $excludeNoTeam), true);

$peopleList = [];
if (!empty($scheduledPeopleLists)) {
    foreach ($scheduledPeopleLists as $data) {
        $userName = $data['DisplayName'];
        $peopleList[$data['DisplayName']] = $userName;
    }
}
header('Content-type: application/json');
echo json_encode($peopleList);
