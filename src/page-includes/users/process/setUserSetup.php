<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//in this file we are handling the create/edit,history action of divsiosns
include_once 'classUserSetup.php';
//call the class divisions
$setupObj = new classUserSetup();
$resuldata = json_decode($setupObj->getUserSetupByIdNetlogin(), true);
if (empty($resuldata)) {
    echo 'Access Denied';die;
}

$action = '';
if (isset($_POST['action'])) {
    $action = $_POST['action'];
}

/* check unique divisions name validation */
if ($action == 'getUserSetup') {
    $resuldata1 = json_decode($setupObj->getUserSetupByIdNetlogin(), true);

	$resuldata = [];
	foreach($resuldata1 as $key=>$resuldataVal2)
	{
		foreach($resuldataVal2 as $key2=>$resuldataVal)
		{
			if(($key2 == 'IsShowEditYearly') || ($key2 == 'Home Team')) 
			{
				continue;
			}
			if($key2 == 'ShowJobsInWeeklyView')
			{
				$resuldata[$key]['Todayactivehometeam'] = '';
				continue;
			}
			$resuldata[$key][$key2] = $resuldataVal;
		}
	}
    echo json_encode(array("data" => $resuldata));
}
//set default team
if ($action == strtolower('setdefault')) {
    $personId = $_POST['schedulepersonid'];
    $default = $_POST['defaultValue'];
    $schedulingteamId = $_POST['scheduledteamid'];
    $image = '';

    $setTeamResult = json_decode($setupObj->setDefaultTeam($personId, $default, $schedulingteamId), true);

    $image = '<img src="../../../images/red_cross.png" class="tick" data-default= "1" id="js_defaultimg_' . $schedulingteamId . '">';
    if ($default == 1) {
        $image = '<img src="../../../images/green_tick.png" class="tick" data-default= "0" id="js_defaultimg_' . $schedulingteamId . '">';
    }
    $setTeamResult['view'] = $image;
    header('Content-type: application/json');
    echo json_encode($setTeamResult);
}

//set who's in flag
if (strtolower($action) == 'setwhosin') {
    $personId = $_POST['schedulepersonid'];
    $schedulingteamId = $_POST['scheduledteamid'];
    $whosInFlag = $_POST['whosInFlag'];
    $image = '';

    $setTeamResult = json_decode($setupObj->setWhosInTeam($personId, $schedulingteamId), true);

    $image = '../../../images/red_cross.png';
    if ($whosInFlag == 1) {
        $image = '../../../images/green_tick.png';
    }
    $setTeamResult['view'] = $image;
    $setTeamResult['schedulingteamId'] = $schedulingteamId;
    header('Content-type: application/json');
    echo json_encode($setTeamResult);
}
