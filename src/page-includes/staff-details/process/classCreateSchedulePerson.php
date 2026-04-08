<?php

include_once __DIR__.'/../../../function-includes/DBHelper.php';
include_once __DIR__.'/../../../function-includes/helpers.php';

class classCreateSchedulePerson
{
    /*
     * @Description : Fetch all of the accessible scheduling team for a user.
     * @access : Public
     * @global : Not Applicable
     * @param  : $intuserid
     * @return : JSON output
     */
    function getScedulingTeam($intuserid, $strtask,$pageid, $schedulepersonid)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_get_GetUserTeamListByUserPermission] ?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $intuserid, PDO::PARAM_INT);
        $stmt->bindParam(2, $strtask, PDO::PARAM_STR);
        $stmt->bindParam(3, $pageid, PDO::PARAM_INT);
		$stmt->bindParam(4, $schedulepersonid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        echo $resultjson;
    }

    /*
     * @Description : To create a schedule person and add its additional teams
     * @access : Public
     * @global : Not Applicable
     * @param  : $strdisplayname, $inthomeTeamid, $strhometeamstartDate, $strhometeamendDate, $strhometeamsortCode,
    $strhometeambackcolour, $strhometeamfontcolour, $stradminNotes, $strfwaNotes, $addteamarray, $intuserid,
    $intisavailable
     * @return : JSON output
     */
    public function createSchedulePerson($strdisplayfirstname, $strdisplaylastname, $strdisplayname, $inthomeTeamid, $strhometeamstartDate, $strhometeamendDate, $strhometeamsortCode, $strhometeambackcolour, $strhometeamfontcolour, $stradminNotes, $strfwaNotes, $useractiontype, $intScheduledPersonID, $addteamarray, $intuserid, $defaultBgColour,$additionalleave, $SPTeamID)
    {
		$addteamarray = is_array(json_decode($addteamarray)) ? $addteamarray: '[]';
        $pdo = OpenDBLinkA7();
		$strhometeamstartDate = date('Y-m-d', strtotime($strhometeamstartDate));
		$strhometeamendDate = date('Y-m-d', strtotime($strhometeamendDate));
		$sql = "exec [dbo].[usp_CreateUpdate_SchedulePerson] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $SPTeamID, PDO::PARAM_STR);
        $stmt->bindParam(2, $strdisplayname, PDO::PARAM_STR);
        $stmt->bindParam(3, $inthomeTeamid, PDO::PARAM_INT);
        $stmt->bindParam(4, $strhometeamstartDate, PDO::PARAM_STR);
        $stmt->bindParam(5, $strhometeamendDate, PDO::PARAM_STR);
        $stmt->bindParam(6, $strhometeamsortCode, PDO::PARAM_STR);
        $stmt->bindParam(7, $strhometeambackcolour, PDO::PARAM_STR);
        $stmt->bindParam(8, $strhometeamfontcolour, PDO::PARAM_STR);
        $stmt->bindParam(9, $stradminNotes, PDO::PARAM_STR);
        $stmt->bindParam(10, $strfwaNotes, PDO::PARAM_STR);
        $stmt->bindParam(11, $useractiontype, PDO::PARAM_STR);
        $stmt->bindParam(12, $intScheduledPersonID, PDO::PARAM_INT);
        $stmt->bindParam(13, $addteamarray, PDO::PARAM_STR);
        $stmt->bindParam(14, $intuserid, PDO::PARAM_INT);
        $stmt->bindParam(15, $strdisplayfirstname, PDO::PARAM_STR);
        $stmt->bindParam(16, $strdisplaylastname, PDO::PARAM_STR);
        $stmt->bindParam(17, $defaultBgColour, PDO::PARAM_INT);
		$stmt->bindParam(18, $additionalleave, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }

    /*
     * @access : Public
     * @global : Not Applicable
     * @param  : $searchForeName,$searchNetLogin,$searchSurName,$searchStaffNumber
     * @return : JSON output
     * @Description : Fetch all of the Staff details config (TeamPay) for a user.
     */
    public function getStaffDetailsConfig($searchForeName, $searchNetLogin, $searchSurName, $searchStaffNumber)
    {
        $returString = '';
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_ScheduledPeopleStaffDetails] ?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $searchStaffNumber, PDO::PARAM_STR);
        $stmt->bindParam(2, $searchForeName, PDO::PARAM_STR);
        $stmt->bindParam(3, $searchSurName, PDO::PARAM_STR);
        $stmt->bindParam(4, $searchNetLogin, PDO::PARAM_STR);
        $stmt->bindParam(5, $returString, PDO::PARAM_STR);
        $stmt->execute();
        $staffDetailsresult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($staffDetailsresult);
    }

    /*
     * @access : Public
     * @global : Not Applicable
     * @param  : $searchForeName,$searchNetLogin,$searchSurName,$searchStaffNumber
     * @return : JSON output
     * @Description : insert,update,select the staff details.
     */
    public function attachStaffDetailsConfig($secheduledPersonId, $staffId, $actionType, $status, $returString, $userID, $oldschedPersonID)
    {
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_insup_StaffLinkAddUser] ?,?,?,?,?,?,?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $secheduledPersonId, PDO::PARAM_INT);
        $stmt->bindParam(2, $actionType, PDO::PARAM_STR);
        $stmt->bindParam(3, $staffId, PDO::PARAM_INT);
        $stmt->bindParam(4, $oldschedPersonID, PDO::PARAM_INT);
        $stmt->bindParam(5, $userID, PDO::PARAM_INT);
        $stmt->bindParam(6, $status, PDO::PARAM_STR);
        $stmt->bindParam(7, $returString, PDO::PARAM_STR);
        $stmt->execute();
        $staffConfigResult = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($staffConfigResult);
    }

    public function getSchedulepersondetails($intschedulepersonid)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_get_ScheduledPersonTeams] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $intschedulepersonid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($result);
    }

    public function validateAddHomeTeamsStartDate($schedulepersonid)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "select TOP(1) FORMAT(convert(datetime,min(StartDate),105),'dd-MM-yyyy') StartDateHomeTeamFirst
                    from [dbo].[ScheduledPersonTeam_LINK] where ScheduledPersonID = ? and IsHomeTeam = 1 and scheduledType = 1";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $schedulepersonid, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * validate additional team duration exists in current or past home teams.
     * @param $schedulepersonid ScheduledPersonId
     * @return array
     */
    public function validateAddTeamBetweenAnyExistingHomeTeamsDuration($schedulepersonid)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "select TeamId,
                        FORMAT(convert(datetime,StartDate,105),'dd-MM-yyyy') StartDateHomeTeam,
                        FORMAT(convert(datetime,EndDate,105),'dd-MM-yyyy') EndDateHomeTeam
                from [dbo].[ScheduledPersonTeam_LINK]
                where ScheduledPersonID = $schedulepersonid and scheduledType = 1 and IsHomeTeam = 1";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $schedulepersonid, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * validate additional team enddate if allocations exists
     * @param  $ddlteamsid AdditionalTeamId
     * @param $schedulepersonid ScheduledPersonId
     * @param $addteamenddate Additional Team EndDate
     * @return array
     */
    public function validateAddTeamsEndDate($ddlteamsid, $schedulepersonid, $addteamenddate, $addteamstartdate, $addteamsid)
    {
        $resultresponse = false;
        $pdo = OpenDBLinkA7();
        try {
            $addteamenddate = date("Y-d-m", strtotime($addteamenddate));
            if ($addteamenddate != '9999-01-01') {

                $sql = "SELECT COUNT(AL_AllocationsID) AS AllocateCount, MAX(AL_WeekNumber) AS MaxWeekNumber
						 FROM Allocations AL
						INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
						INNER JOIN AllocationsScheduledPersons ASP ON AD_AllocationsDutyID = ASP_AllocationsDutyID
						WHERE AL_SchedulingTeamID = :ddlhometeamsid
						  AND ASP_DutyTeamId = :addteamsid
						  AND ASP_SchedulingPersonID = :schedulepersonid
						  AND AD_DutyDate  >  CONVERT(DATETIME,:addteamenddate,105)
						  AND AD_DutyType NOT IN (7,8,9)";
				$stmt = $pdo->prepare($sql);

				$stmt->bindParam(':ddlhometeamsid', $ddlteamsid, PDO::PARAM_INT);
				$stmt->bindParam(':addteamsid', $addteamsid, PDO::PARAM_INT);
				$stmt->bindParam(':schedulepersonid', $schedulepersonid, PDO::PARAM_INT);
				$stmt->bindParam(':addteamenddate', $addteamenddate, PDO::PARAM_STR);
				$stmt->execute();
				$resultresponse = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if (!empty($resultresponse)) {
                return $resultresponse;
            }
        } catch (Exception $e) {
            logger()->critical('DB Error', (array) $e);
        }
    }

    public function changeDisplayName($schedulepersonid, $newName)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "UPDATE UserDetails set DisplayName = ? where UD_UserID = ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $newName, PDO::PARAM_STR);
        $stmt->bindParam(2, $schedulepersonid, PDO::PARAM_INT);
        $stmt->execute();
    }
    /*
     * @access : Public
     * @global : Not Applicable
     * @param  : $schedulepersonid
     * @return : boolen
     * @Description : check person type.
     */

    public function checkScheduledType($schedulepersonid)
    {
        $pdo = OpenDBLinkA7();
        $sql = "select TOP(1) scheduledType from [dbo].[ScheduledPersonTeam_LINK]
         where ScheduledPersonID = ? and isnull(EndDate,'9999-01-01') >= getdate() and scheduledType= 1 ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $schedulepersonid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return 0;
        }
        return 1;

    }

    public function validateAddTeamBetweenAnyExistingAdditioanlTeam($schedulepersonid, $teamid, $addteamstartdate, $addteamenddate)
    {
        $pdo = OpenDBLinkA7();
        $addteamstartdate = date("Y-d-m", strtotime($addteamstartdate));
        $sql = "select IsHomeTeam,StartDate,EndDate,IsActive,scheduledType,ScheduledPersonID,TeamID from ScheduledPersonTeam_LINK (NOLOCK) where TeamID = ? and ScheduledPersonID = ? and EndDate < cast(getdate() AS DATE) and IsHomeTeam = 0 and IsActive = 1 and scheduledType = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $teamid, PDO::PARAM_INT);
        $stmt->bindParam(2, $schedulepersonid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}
