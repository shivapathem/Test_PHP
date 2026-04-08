<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once 'classCreateSchedulePerson.php';
include_once '../view/createScheduledPersonUI.php';
include_once 'classContractHistory.php';
include_once 'classSchedulTeamHistory.php';

$task = $_POST['task'];
$intuserid = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$scheduleobjUI = new createSchedulePersonUI;
$scheduleobj = new classCreateSchedulePerson;
$historyobj = new classContractHistory;
$SchTeamhistoryobj = new classSchedulTeamHistory;
$addteamstartdate = '';
$addteamenddate = '';
// Controller to fetch all html for createperson
if ($task === 'schedulepersonhtmlcall') {
    $useraction = $_POST['useraction'];
    $schedulepersonid = isset($_POST['schedulepersonid']) ? $_POST['schedulepersonid'] : 0;
    $selectedteamid = isset($_POST['selectedteamid']) ? $_POST['selectedteamid'] : 0;
    $selecteduserid = isset($_POST['selecteduserid']) ? $_POST['selecteduserid'] : '';
    return $scheduleobjUI->schedulepersonhtmlcall($useraction, $schedulepersonid, $selectedteamid, $selecteduserid);
}

// Controller to fetch all accessible scheduling teams for a user
if ($task === 'getteamlist') {
    $struseractiontype = $_POST['struseractiontype'];
	$schedulepersonid = $_POST['schedulepersonid'] ? $_POST['schedulepersonid'] : 0;    
    $pageid = 6;
    return $scheduleobj->getScedulingTeam($intuserid, $struseractiontype,$pageid, $schedulepersonid);
}

// Controller for create schedule person
if ($task === 'createscheduleperson') {
    $displayfirstname = $_POST['displayfirstName'];
    $displaylastname = $_POST['displaylastname'];
    $displayname = $_POST['displayfirstName'] . ' ' . $_POST['displaylastname'];
    $homeTeamid = $_POST['homeTeamid'];
    $hometeamstartDate = $_POST['hometeamstartDate'];
    $hometeamendDate = $_POST['hometeamendDate'];
    $hometeamsortCode = $_POST['hometeamsortCode'];
    $hometeambackcolour = $_POST['hometeambackcolour'];
    $hometeamfontcolour = $_POST['hometeamfontcolour'];
    $adminNotes = $_POST['adminNotes'];
    $fwaNotes = $_POST['fwaNotes'] == ''  ? NULL :  $_POST['fwaNotes'];
    $useractiontype = $_POST['useractiontype'];
    $intScheduledPersonID = $_POST['intScheduledPersonID'];
    $addteamarray = $_POST['addteamarray'];
    $defaultBgColour = $_POST['defaultBgColour'];
	$additionalleave = $_POST['additionalleave'];
	$SPTeamID = $_POST['SPTeamID'];
    $schpersonresponse = $scheduleobj->createSchedulePerson($displayfirstname, $displaylastname, $displayname,
        $homeTeamid, $hometeamstartDate, $hometeamendDate, $hometeamsortCode, $hometeambackcolour, $hometeamfontcolour,
        $adminNotes, $fwaNotes, $useractiontype, $intScheduledPersonID, $addteamarray, $intuserid, $defaultBgColour,$additionalleave, $SPTeamID);
    $schpersonresponsedecode = json_decode($schpersonresponse, true);
    $responsearray = array('status' => 'success',
        'sqlstatus' => $schpersonresponsedecode[0]["intstatusschpeople"],
        'sqlstatusstring' => $schpersonresponsedecode[0]["strstatusschpeople"],
        'sqlnewID' => $schpersonresponsedecode[0]["intnewidschpeople"]);
    echo json_encode($responsearray);
}

// Controller to get details of a person
if ($task === 'getSchedulepersondetails') {
    $intschedulepersonid = $_POST['intschedulepersonid'];
    $schpersonresponse = json_decode($scheduleobj->getSchedulepersondetails($intschedulepersonid), true);
    echo json_encode($schpersonresponse);
}

//Controller to fetch all Contract history of scheduled person
if ($task == 'gethistory') {
    $staffid = $_POST['staffid'];
    $result = $historyobj->getContractHistory($staffid);
    return $result;
}

if ($task == 'validateAddTeams') {
    $prevaddteamarray = isset($_POST['prevaddteamarray']) && $_POST['prevaddteamarray'] != '' ? $_POST['prevaddteamarray'] : array();

    $addteamSPTeamID = $_POST['addteamSPTeamID'];
    $ddlteamsid = $_POST['ddlteamsid'];
    $teamName = $_POST['ddlteamsname'] == '' ? '' : $_POST['ddlteamsname'];
    $schedulepersonid = $_POST['schedulepersonid'];
    $addteamstartdate = $_POST['addteamstartdate'];
    $addteamenddate = $_POST['addteamenddate'] == '' ? "01-01-9999" : $_POST['addteamenddate'];
    $hometeamhiddenVal = $_POST['hometeamhiddenVal'];
    $adduseractiontype = isset($_POST['adduseractiontype']) ? $_POST['adduseractiontype'] : '';
    $hometeamStartDate = $_POST['hometeamStartDate'];
    $homestartdateHideNewTeam = $_POST['homestartdateHideNewTeam'];
    $schDisplayName = $_POST['schDisplayName'];
    $addteamdefaultbgcolour = isset($_POST['addteamdefaultbgcolour']) ? $_POST['addteamdefaultbgcolour'] : '';

    $errorStatus = 0;
    $count = 1;
    $resultHomeTeamExisting = $scheduleobj->validateAddTeamBetweenAnyExistingHomeTeamsDuration($schedulepersonid);
    //cross check in db as well
    $resultAdditionalTeamExisting = $scheduleobj->validateAddTeamBetweenAnyExistingAdditioanlTeam($schedulepersonid, $ddlteamsid, $addteamstartdate, $addteamenddate);

    $message = '';
    foreach ($resultHomeTeamExisting as $homeTeamArray) {
        if ($homestartdateHideNewTeam != '' || $homestartdateHideNewTeam != null) {
            $EnddateHomeTeam = $homestartdateHideNewTeam;
        } else {
            $EnddateHomeTeam = $homeTeamArray['EndDateHomeTeam'];
        }
        if ($ddlteamsid == $homeTeamArray['TeamId']
            && ((strtotime($addteamstartdate) >= strtotime($homeTeamArray['StartDateHomeTeam'])
                && strtotime($addteamstartdate) <= strtotime($EnddateHomeTeam))
                || (strtotime($addteamenddate) <= strtotime($EnddateHomeTeam)
                    && strtotime($addteamenddate) >= strtotime($homeTeamArray['StartDateHomeTeam']))
            )) {
            $message .= "Additional Teams that you are trying to add having conflict of date with previous home team dates. Please select new Date that doesn't lie on or between (" . $homeTeamArray['StartDateHomeTeam'] . " to " . date("d-m-Y", strtotime($EnddateHomeTeam)) . ") for the Additional Team or select any other team.\n";
            $errorStatus = array('status' => 0, 'strStatus' => $message);
        }
    }

    if (!empty($resultAdditionalTeamExisting)) {
        foreach ($resultAdditionalTeamExisting as $resultAdditionalTeamExistingEle) {

            if ((strtotime($addteamstartdate) >= strtotime($resultAdditionalTeamExistingEle['StartDate']) &&
                strtotime($addteamstartdate) <= strtotime($resultAdditionalTeamExistingEle['EndDate'])) ||
                (strtotime($addteamenddate) >= strtotime($resultAdditionalTeamExistingEle['StartDate']) &&
                    strtotime($addteamenddate) < strtotime($resultAdditionalTeamExistingEle['EndDate']))) {
                $message = "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $resultAdditionalTeamExistingEle['StartDate'] . " to " . $resultAdditionalTeamExistingEle['EndDate'] . ") for the Additional Teams.\n";
                $errorStatus = array('status' => 0, 'strStatus' => $message);
            } else if ((strtotime($addteamstartdate) >= strtotime($resultAdditionalTeamExistingEle['StartDate'])) &&
                (strtotime($addteamenddate) <= strtotime($resultAdditionalTeamExistingEle['EndDate']))) {
                $message = "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $resultAdditionalTeamExistingEle['StartDate'] . " to " . $resultAdditionalTeamExistingEle['EndDate'] . ") for the Additional Teams.\n";
                $errorStatus = array('status' => 0, 'strStatus' => $message);
            } else if ((strtotime($addteamstartdate) >= strtotime($resultAdditionalTeamExistingEle['StartDate'])) &&
                (strtotime($addteamenddate) == strtotime($resultAdditionalTeamExistingEle['EndDate']))) {
                $message = "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $resultAdditionalTeamExistingEle['StartDate'] . " to " . $resultAdditionalTeamExistingEle['EndDate'] . ") for the Additional Teams.\n";
                $errorStatus = array('status' => 0, 'strStatus' => $message);
            } else if ((strtotime($addteamstartdate) <= strtotime($resultAdditionalTeamExistingEle['StartDate'])) &&
                (strtotime($addteamenddate) == strtotime($resultAdditionalTeamExistingEle['EndDate']))) {
                $message = "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $resultAdditionalTeamExistingEle['StartDate'] . " to " . $resultAdditionalTeamExistingEle['EndDate'] . ") for the Additional Teams.\n";
                $errorStatus = array('status' => 0, 'strStatus' => $message);
            } else if ((strtotime($addteamstartdate) <= strtotime($resultAdditionalTeamExistingEle['StartDate'])) &&
                (strtotime($addteamenddate) >= strtotime($resultAdditionalTeamExistingEle['EndDate']))) {
                $message = "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $resultAdditionalTeamExistingEle['StartDate'] . " to " . $resultAdditionalTeamExistingEle['EndDate'] . ") for the Additional Teams.\n";
                $errorStatus = array('status' => 0, 'strStatus' => $message);
            }
        }
    }

    foreach ($prevaddteamarray as $prevaddteamarrayEle) {
        if ($ddlteamsid == $prevaddteamarrayEle['addteamsid'] && (strtotime($addteamstartdate) != strtotime($prevaddteamarrayEle['addteamstartdate']))) {
            if ($prevaddteamarrayEle['addteamenddate'] != '01-01-9999') {

                if ((strtotime($addteamstartdate) >= strtotime($prevaddteamarrayEle['addteamstartdate']) &&
                    strtotime($addteamstartdate) <= strtotime($prevaddteamarrayEle['addteamenddate'])) ||
                    (strtotime($addteamenddate) >= strtotime($prevaddteamarrayEle['addteamstartdate']) &&
                        strtotime($addteamenddate) < strtotime($prevaddteamarrayEle['addteamenddate']))) {
                    $message .= "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $prevaddteamarrayEle['addteamstartdate'] . " to " . $prevaddteamarrayEle['addteamenddate'] . ") for the Additional Teams.\n";
                    $errorStatus = array('status' => 0, 'strStatus' => $message);
                }
                if ((strtotime($addteamstartdate) <= strtotime($prevaddteamarrayEle['addteamstartdate'])) &&
                    (strtotime($addteamenddate) >= strtotime($prevaddteamarrayEle['addteamenddate']))) {
                    $message .= "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $prevaddteamarrayEle['addteamstartdate'] . " to " . $prevaddteamarrayEle['addteamenddate'] . ") for the Additional Teams.\n";
                    $errorStatus = array('status' => 0, 'strStatus' => $message);
                }
            } else {

                if ((strtotime($addteamstartdate) >= strtotime($prevaddteamarrayEle['addteamstartdate']) &&
                    strtotime($addteamstartdate) <= strtotime($prevaddteamarrayEle['addteamenddate']))
                    && $prevaddteamarrayEle['addteamenddate'] != '01-01-9999') {
                    $message .= "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $prevaddteamarrayEle['addteamstartdate'] . " to " . $prevaddteamarrayEle['addteamenddate'] . ") for the Additional Teams.\n";
                    $errorStatus = array('status' => 0, 'strStatus' => $message);
                } else if ((strtotime($addteamstartdate) >= strtotime($prevaddteamarrayEle['addteamstartdate'])) || (strtotime($addteamenddate) >= strtotime($prevaddteamarrayEle['addteamenddate'])) && ($prevaddteamarrayEle['addteamenddate'] == '01-01-9999')) {
                    $message .= "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $prevaddteamarrayEle['addteamstartdate'] . " to " . $prevaddteamarrayEle['addteamenddate'] . ") for the Additional Teams.\n";
                    $errorStatus = array('status' => 0, 'strStatus' => $message);
                } else if ((strtotime($addteamstartdate) >= strtotime($prevaddteamarrayEle['addteamstartdate']) &&
                    strtotime($addteamstartdate) <= strtotime($prevaddteamarrayEle['addteamenddate'])) ||
                    (strtotime($addteamenddate) >= strtotime($prevaddteamarrayEle['addteamstartdate']) &&
                        strtotime($addteamenddate) <= strtotime($prevaddteamarrayEle['addteamenddate']))
                    && $prevaddteamarrayEle['addteamenddate'] == '01-01-9999') {
                    $message .= "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $prevaddteamarrayEle['addteamstartdate'] . " to " . $prevaddteamarrayEle['addteamenddate'] . ") for the Additional Teams.\n";
                    $errorStatus = array('status' => 0, 'strStatus' => $message);
                }

            }
        }

        if ((strtolower($adduseractiontype) == 'add') && ($ddlteamsid == $prevaddteamarrayEle['addteamsid']) && (strtotime($addteamstartdate) == strtotime($prevaddteamarrayEle['addteamstartdate']))) {
            $message .= "Additional Teams that you are trying to add already exist for the dates. Please select new Date that doesn't lie on or between (" . $prevaddteamarrayEle['addteamstartdate'] . " to " . $prevaddteamarrayEle['addteamenddate'] . ") for the Additional Teams.\n";
            $errorStatus = array('status' => 0, 'strStatus' => $message);
        }
        $count++;
    }

    $resultHomeTeam = $scheduleobj->validateAddHomeTeamsStartDate($schedulepersonid);
    $StartDateHomeTeamFirst = empty($resultHomeTeam['StartDateHomeTeamFirst']) ? $hometeamStartDate : $resultHomeTeam['StartDateHomeTeamFirst'];
    if ($adduseractiontype != 'Add') {
		foreach ($prevaddteamarray as $prevaddteamarrayEle) {
			if ($ddlteamsid == $prevaddteamarrayEle['addteamsid'] && ($prevaddteamarrayEle['addteamenddate'] != $addteamenddate)) {
			$resultAdditionalTeam = $scheduleobj->validateAddTeamsEndDate($ddlteamsid, $schedulepersonid, $addteamenddate, $addteamstartdate, $prevaddteamarrayEle['addteamsid']);
				if (is_array($resultAdditionalTeam) && $resultAdditionalTeam['AllocateCount'] > 0) {
					$ixWeek = substr($resultAdditionalTeam['MaxWeekNumber'], 4);
					$ixYear = substr($resultAdditionalTeam['MaxWeekNumber'], 0, 4);

					$MaxWeekNumber = $ixWeek . "/" . (int) $ixYear;
					$message .= "You cannot change the Team for " . $schDisplayName . " while he/she still has Duties allocated in the " . $teamName . " Team. Please open the Weeks Form and remove all the Duties that occur after and including the Effective From date you have chosen. The maximum Week that the person can be found in is - Week " . $MaxWeekNumber;
					$errorStatus = array('status' => 0, 'strStatus' => $message);
				}
			}
		}	
    }

    if (strtotime($addteamstartdate) < strtotime($StartDateHomeTeamFirst)) {
        $message .= "Start Date of an Additional Team cannot be before the start date of the first Home Team. Please select a date on or after " . $StartDateHomeTeamFirst . ".<br>";
        $errorStatus = array('status' => 0, 'strStatus' => $message);
    }

    echo json_encode($errorStatus);
    die;
}

if ($task === 'deleteSchPersonHomeTeam') {
    $HomeTeamId = (int) ($_POST['HomeTeamId']);
    $Schpersonid = (int) ($_POST['personid']);
    $DeleteFromRota = (int) ($_POST['DeleteFromRota']);
    return $SchTeamhistoryobj->deleteSchPersonHomeTeam($HomeTeamId, $Schpersonid, $DeleteFromRota);
}

if ($task == 'ValidateSchPersonHomeTeamHaveRota') {
    $HomeTeamId = (int) ($_POST['HomeTeamId']);
    $Schpersonid = (int) ($_POST['personid']);
    return $SchTeamhistoryobj->validateSchPersonHomeTeamHaveRota($HomeTeamId, $Schpersonid);
}
