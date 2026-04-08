<?php
include_once 'init.php';
include_once 'genericfunctions.php';

function addUpdateMasterJob($masterJobID, $job, $starttime, $endtime, $info, $contact, $location, $backcolor, $forecolor, $programme_id, $team_id, $TaskType, $copyFlag = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        $areaID = 1;
        $current_User = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
        $sql = "exec [dbo].[usp_mod_MasterJobs] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
        $status = 1;
        $strstatus = 'success';
        $programme_id = (int)$programme_id;
        $areaID = (int)$areaID;
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $masterJobID, PDO::PARAM_INT);
        $stmt->bindParam(2, $job, PDO::PARAM_STR);
        $stmt->bindParam(3, $starttime, PDO::PARAM_INT);
        $stmt->bindParam(4, $endtime, PDO::PARAM_INT);
        $stmt->bindParam(5, $info, PDO::PARAM_STR);
        $stmt->bindParam(6, $backcolor, PDO::PARAM_STR);
        $stmt->bindParam(7, $forecolor, PDO::PARAM_STR);
        $stmt->bindParam(8, $programme_id, PDO::PARAM_INT);
        $stmt->bindParam(9, $contact, PDO::PARAM_STR);
        $stmt->bindParam(10, $location, PDO::PARAM_STR);
        $stmt->bindParam(11, $current_User, PDO::PARAM_STR);
        $stmt->bindParam(12, $areaID, PDO::PARAM_INT);
        $stmt->bindParam(13, $team_id, PDO::PARAM_INT);
        $stmt->bindParam(14, $status, PDO::PARAM_INT);
        $stmt->bindParam(15, $strstatus, PDO::PARAM_STR);
        $stmt->bindParam(16, $TaskType, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $resultJson = json_encode($result);
        return $resultJson;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function addJobFromDuty($masterDutyId, $job, $starttime, $endtime, $info, $programme_id, $backcolor, $forecolor, $contact, $location, $current_User, $areaID, $schedule_team, $flag)
{
    try {
        $pdo = OpenDBLinkA7();
        $areaID = 1;
        $current_User = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
        $sql = "exec [dbo].[usp_mod_MasterJobsFromMasterDuties] ?,?,?,?,?,?,?,?,?,?,?,?,?,?";
        $status = 1;
        $masterDutyId = (int)$masterDutyId;
        $programme_id = (int)$programme_id;
        $schedule_team = (int)$schedule_team;
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $masterDutyId, PDO::PARAM_INT);
        $stmt->bindParam(2, $job, PDO::PARAM_STR);
        $stmt->bindParam(3, $starttime, PDO::PARAM_INT);
        $stmt->bindParam(4, $endtime, PDO::PARAM_INT);
        $stmt->bindParam(5, $info, PDO::PARAM_STR);
        $stmt->bindParam(6, $programme_id, PDO::PARAM_INT);
        $stmt->bindParam(7, $backcolor, PDO::PARAM_STR);
        $stmt->bindParam(8, $forecolor, PDO::PARAM_STR);
        $stmt->bindParam(9, $contact, PDO::PARAM_STR);
        $stmt->bindParam(10, $location, PDO::PARAM_STR);
        $stmt->bindParam(11, $current_User, PDO::PARAM_STR);
        $stmt->bindParam(12, $areaID, PDO::PARAM_INT);
        $stmt->bindParam(13, $schedule_team, PDO::PARAM_INT);
        $stmt->bindParam(14, $flag, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function getTeam($dutyID)
{
    try {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT TeamID from MasterDutyTeams Where DutyID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rsResources = $result['TeamID'];
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}


function getAllProgrammes()
{
    $pdo = OpenDBLinkA7();
    $sql = "SELECT Programme,ID from Programmes ORDER BY Programme";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rsResources = json_encode($result);
}

/****This function is used fetch All masterjobs
 * $masterJobID=0 and
 * when $masterJobID is passed with specific then it fetch single record
 *******/

function getJobs($masterJobID, $teamID = '0', $edit = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        $areaID = 1;
        $current_User = getenv('ALLOCATE_DB_NAME');
        $sql = "exec [dbo].[usp_get_MasterJobs] ?,?,?,?";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $areaID, PDO::PARAM_INT);
        $stmt->bindParam(2, $current_User, PDO::PARAM_STR);
        $stmt->bindParam(3, $masterJobID, PDO::PARAM_INT);
        $stmt->bindParam(4, $teamID, PDO::PARAM_STR);
        $stmt->execute();

        if ($masterJobID == 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            if($edit) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $temp_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $result= array();
                foreach($temp_result as $value) {
                    if($value['MasterJobID'] == $masterJobID) {
                        $result = $value;
                    }
                }
            }
        }
        $rsResources = json_encode($result);
        return $rsResources;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function ToggleInactiveMasterJob($intJobID)
{
    try {
        $areaID = 1;
        $current_User = getenv('ALLOCATE_DB_USER');
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_del_MasterJobs] ?,?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $areaID, PDO::PARAM_INT);
        $stmt->bindParam(2, $intJobID, PDO::PARAM_INT);
        $stmt->bindParam(3, $current_User, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function checkJobAssignToMasterDuty($intJobID)
{
    try {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT 1 FROM MasterDutiesMasterJobs_LINK WHERE MasterJobID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$intJobID,]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) {
            return 1;
        } else {
            return 0;
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

}

function getMasterDutyId($intJobID)
{
    try {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT MasterDutyID FROM MasterDutiesMasterJobs_LINK WHERE MasterJobID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$intJobID,]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['MasterDutyID'] ?? 0;
    } catch (PDOException $e) {
        logger()->critical('pdo exception on master duties', (array) $e);
    }

}

function checkMasterJobExists($jobname,$teamid,$master_job_id, $start_time, $endTime)
{
    try {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT MasterJobID FROM MasterJobs WHERE IsActive = 1 and JobName=? and TeamID=? and MasterJobID != ? and StartTime=? and EndTime=?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $jobname, PDO::PARAM_STR);
        $stmt->bindParam(2, $teamid, PDO::PARAM_INT);
        $stmt->bindParam(3, $master_job_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $start_time, PDO::PARAM_INT);
        $stmt->bindParam(5, $endTime, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function totalJobAssignToMasterDuty($intJobID)
{
    try {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT * FROM MasterDutiesMasterJobs_LINK WHERE MasterJobID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$intJobID,]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return count($result);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

}

function getMasterDutiesByID($masterDutyId)
{

    try {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT m.StartTime,m.EndTime,m.TeamID FROM MasterDuties m WHERE m.MasterDutyID=? ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $masterDutyId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($result);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}


function deleteJobFromDuty($mdutyID, $jobid)
{
    try {
        $pdo = OpenDBLinkA7();
        $sql = "Delete FROM MasterDutiesMasterJobs_LINK  WHERE MasterDutyID=? AND MasterJobID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $mdutyID, PDO::PARAM_INT);
        $stmt->bindParam(2, $jobid, PDO::PARAM_INT);
        $res = $stmt->execute();
        return $res;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}


function getMasterJobByMasterDutyID($dutyID)
{
    try {
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[mod_get_MasterJobByMasterDutyID] ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}


function jobrange($newst, $newend, $existJobStart, $existJobend)
{
    if ($existJobStart > $existJobend) {
        $existJobend = $existJobend + 86400;
    }
    if (in_array($newst + 1, range($existJobStart, $existJobend)) || (in_array($newend - 1, range($existJobStart, $existJobend)))) {
        return 'conflict';
    } else if ($newend > $existJobend && in_array($existJobStart, range($newst, $newend))) {
        return 'conflict';
    } else {
        return 'noconflcit';
    }
}

function checkEndTimeExceed($dutyID)
{
    try {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT * FROM MasterDuties WHERE MasterDutyID=?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

//Job Will add in Master Duites when drag from Job list to Master duties
function addJobToMasterduties($dutyID, $jobid)
{
    $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    try {

        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_mod_MasterDutyJob_Link] ?,?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
        $stmt->bindParam(2, $jobid, PDO::PARAM_INT);
        $stmt->bindParam(3, $intCurrUserID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function JobsRsToArray($rsJson)
{
    $rs = json_decode($rsJson, true);
    if (!empty($rs)) {
        $rowcount = count($rs);
    } else {
        $rowcount = 1;
    }
    for ($row = 0; $row < $rowcount; $row++) {
        $arrMasterJobs[$rs[$row]['ID']]['job'] = $rs[$row]['Job'];
        $arrMasterJobs[$rs[$row]['ID']]['details'] = $rs[$row]['Details'];
        $arrMasterJobs[$rs[$row]['ID']]['starttime'] = FormatTime($rs[$row]['StartTime']);
        $arrMasterJobs[$rs[$row]['ID']]['endtime'] = FormatTime($rs[$row]['EndTime']);
        $arrMasterJobs[$rs[$row]['ID']]['chargecode'] = $rs[$row]['ChargeCode'];
        $arrMasterJobs[$rs[$row]['ID']]['user1'] = $rs[$row]['User1'];
        $arrMasterJobs[$rs[$row]['ID']]['user2'] = $rs[$row]['User2'];
        $arrMasterJobs[$rs[$row]['ID']]['jobtype'] = $rs[$row]['JobTypeName'];
        $arrMasterJobs[$rs[$row]['ID']]['backcolour'] = $rs[$row]['ColourBackground'];
        $arrMasterJobs[$rs[$row]['ID']]['forecolour'] = $rs[$row]['ColourFont'];
        $arrMasterJobs[$rs[$row]['ID']]['inactive'] = $rs[$row]['Inactive'];
        $arrMasterJobs[$rs[$row]['ID']]['lastmoddate'] = $rs[$row]['LastModDate'];
        if (isset($rs[$row]['CountDuties'])) {
            $arrMasterJobs[$rs[$row]['ID']]['countduties'] = $rs[$row]['CountDuties'];
        }
        $arrMasterJobs[$rs[$row]['ID']]['programmes'][$rs[$row]['ProgrammeID']] = $rs[$row]['Programme'];
        $arrMasterJobs[$rs[$row]['ID']]['resources'][$rs[$row]['ResourceID']] = $rs[$row]['ResourceName'];
    }

    if (!empty($arrMasterJobs)) {
        return (json_encode($arrMasterJobs));
    }
}

/*
* @Description : This function is used get all duties and its jobs
* @access : Public
* @global : Not Applicable
* @param  : $intAreaID Default 0, $intDutyTypeID Default 0, $intArchived Default 0, $strSearchText, $strTeams
* @return : JSON Output
*/
function getMasterDutiesList($intAreaID, $intDutyTypeID, $intArchived, $strSearchText, $strTeams, $isShowEnded = 0)
{
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_JobsMasterDuties] ?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDutyTypeID, PDO::PARAM_INT);
    $stmt->bindParam(3, $intArchived, PDO::PARAM_INT);
    $stmt->bindParam(4, $strSearchText, PDO::PARAM_STR);
    $stmt->bindParam(5, $strTeams, PDO::PARAM_STR);
    $stmt->bindParam(6, $isShowEnded, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function TeamListDropDown($rsAreaTeams, $selectedTeam)
{
    $team_list = null;
    $rsTeams = json_decode($rsAreaTeams, true);
    $team_list .= '';
    $arrCount = count($rsTeams);
    $team_list .= "<option value=''>Select The Team</option>";
    for ($row = 0; $row < $arrCount; $row++) {
        $selectvalue = $selectedTeam == $rsTeams[$row]['TeamID'] ? "selected" : "";
        $team_list .= '<option value="' . $rsTeams[$row]['TeamID'] . '"' . $selectvalue . '>' . $rsTeams[$row]['TeamName'] . '</option>';
    }
    if (isset($team_list)) {
        return ($team_list);
    }

}

function getDutyTime($dutyID)
{
    try {
		$pdo = OpenDBLinkA7();
        $sql = "SELECT StartTime, Endtime FROM MasterDuties WHERE MasterDutyID=?";
        $stmt = $pdo->prepare($sql);
		$stmt->bindParam(1, $dutyID, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        logger()->critical('pdo exception on master duties', (array) $e);
        return array();
    }
}
