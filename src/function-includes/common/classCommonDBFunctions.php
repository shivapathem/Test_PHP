<?php
//Common DB functions file
include_once __DIR__ .'/../DBHelper.php';
include_once __DIR__ .'/../helpers.php';


class classCommonDBFunctions{

/*
*
* @Description : Fetch all of the users staff details by team id.
* @access : Public
* @global : Not Applicable
* @param  : no param
* @return : JSON output
*/
public function getStaffContactDetailsByTeamID($teamId){
try {
$status = $returnstring = '';
// Open the database
$pdo = OpenDBLinkA7();
// Set the statement to use
$sql = "exec [dbo].[usp_GET_StaffDetailsByTeamID] ?";
$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1,$teamId, PDO::PARAM_INT);

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$resultjson = json_encode($result);
return $resultjson;

} catch (PDOException $e) {
echo $e->getMessage();
}
}
/*
*
* @Description : Check user is system admin or not.
* @access : Public
* @global : Not Applicable
* @param  : userid
* @return : Bollean output or  exception message will echo
*/

function UserIsSysAdmin($userid) {
try {

$isSysadmin = false;
// Open the database
$pdo = OpenDBLinkA7();
// Set the statement to use
$sql = "select isnull(
        (select RoleId from UserRoles ur
        inner join REF_Roles rr on rr.RoleID = ur.UR_RoleID and rr.RoleName='System Admin'
        Where UR_UserId = ? AND getdate() BETWEEN UR_StartDate AND UR_EndDate)
        ,0) as RoleId";
$stmt = $pdo->prepare($sql);
// The parameters
$stmt->bindParam(1,$userid, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($result) && $result['RoleId'] > 0) {
    $isSysadmin = true;
}
return $isSysadmin;

} catch (PDOException $e) {
echo $e->getMessage();
}
}

/*
*
* @Description : Fetch all of the users created group
* @access : Public
* @global : Not Applicable
* @param  : netlogin
* @return : group lists/Catch message
*/

function GetUserFavouritesGroup ($netLogin) {
try{
$pdo = OpenDBLinkA7();

$sql = "exec [dbo].[usp_get_userFavouriteGroup] ? ";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(1, $netLogin, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
return  json_encode($result);
} catch (PDOException $e) {
echo $e->getMessage();
}
}

function GetUserRolePermissionByPage($strUser,$pageid) {

    // ##########################################################################################################################
    // ################### Function to get the information on a user                #############################################
    // ################### Requires Nework Logon                                    #############################################
    // ################### Returns Staff ID, Staff Number, Forename, Surname        #############################################
    // ################### The Default Area ID, The Default Area Name               #############################################
    // ##########################################################################################################################
    // Open the database
    $pdo = OpenDBLinkA7();
    $results=[];
    // Set the statement to use
    $sql = "exec [dbo].[USP_Get_UserRolePermissionByPage] ?,?";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
    $stmt->bindParam(2, $pageid, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results = json_encode($result);
    return $results;
  }


  /*
    * @Description : Fetch the staffdeatils By ID.
	* @access : Public
	* @global : Not Applicable
	* @param  : $intuserid
	* @return : JSON output
    */
    function getStaffDetailsByID($loginID)
    {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "select UD_DisplayName userDisplayName, UD_NetLogin NetLogin, UD_StaffNumber StaffNumber, UC_JobTitle JobTitle
                from UserDetails
                left join UserConfigs uc on uc.UC_UserID = UD_UserID
                and GETDATE() between UC_StartDate and UC_EndDate
                where UD_NetLogin = ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $loginID, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;
    }

    function getStaffDetailsDisplayNameByID($userID)
    {
        $pdo = OpenDBLinkA7();
        $sql = " SELECT UD_DisplayFirstName Forename, UD_DisplayFirstName PreferredForename,UD_NetLogin NetLogin,UD_DisplayLastName Surname, UD_DisplayLastName Title,UD_StaffNumber StaffNumber,UD_DisplayName DisplayName  from UserDetails  where UD_UserID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $userID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;
    }


    /*
    * @Description : Fetch the signin person By staffnumber on particular week and day.
	* @access : Public
	* @global : Not Applicable
	* @param  : $params array
	* @return : JSON output
    */
    function getAllocationsDetailsByStaffNumber($params)
    {
        try{
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_getAllocationsDetailsByStaffNumber] ?,?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $params['schedulingpersonid'], PDO::PARAM_INT);
        $stmt->bindParam(2, $params['weeknumber'], PDO::PARAM_INT);
        $stmt->bindParam(3, $params['iday'], PDO::PARAM_INT);
        $stmt->bindParam(4, $params['scheduledteamid'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($result);
         }catch (PDOException $e) {
        logger()->critical('DB Error', (array) $e);
      }
      return false;
    }

    function GetStaffDetailsUserDetailsByLogin($strLogin) {
        $pdo = OpenDBLinkA7();
        $sql = "select UD_StaffNumber as StaffNumber, UD_DisplayName userDisplayName, UD_UserID UserID, 0 StaffID FROM UserDetails (nolock) where UD_NetLogin = ltrim(?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,$strLogin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
          }
        return false;
    }

    /**
     * Description : Check FreeLancer Status
     * @param $strLogin str
     * return array
     */


    function GetIsFreelencerByNetlogin($strLogin) {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT DISTINCT ud.UD_DisplayName Displayname,
								ud.UD_TeampayStaffID Staffdetailsid,
								ud.UD_UserID Userid,
								ud.UD_TeampayStaffID as Staffid,
								spl.Teamid,
								spl.Enddate,
								ud.UD_NetLogin Netlogin
				FROM UserDetails ud
				INNER JOIN Scheduledpersonteam_link spl ON ud.UD_UserID = spl.ScheduledPersonID
				INNER JOIN schedulingTeams ST on st.schedulingTeamId = spl.TeamID
				WHERE Isnull(spl.Enddate, '9999-01-01') >=  convert(date,Getdate(),110)
					AND spl.Ishometeam = 1
					AND spl.scheduledType = 1
					AND st.schedulingTeamName = 'Freelancers'
					AND ud.UD_NetLogin = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,$strLogin, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
          }
        return false;
    }

    /**
     * Description :Fetching Data From TimeDimesion Table Lokking For IDay and
     */

    function GetWeekNoAndIDayByDateFromTimeDim($date) {
        $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_get_DailyAllocationWeekandDay] ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,$date, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
          }
          return false;
    }

    /**
     * Description :GetWeekStartDateByWeekNoFromTimeDim
     * @param int Weekno
     * @param string task
     * @param int iDayoftheWeek
     * return array on sucess or false
     */
    function GetWeekStartDateByWeekNoFromTimeDim($Weekno,$task,$iDayoftheWeek=0) {
	    $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_get_WeekStartDateByWeekNo] ?,?,?";
	    try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,$Weekno, PDO::PARAM_INT);
            $stmt->bindParam(2,$task, PDO::PARAM_STR);
            $stmt->bindParam(3,$iDayoftheWeek, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
          }
          return false;
    }

    /**
     * Description :changeMultiWeek Value
     * @param int teamId
     * return bool
     */
    function changeMultiWeek($WeekCount,$TeamId) {
        $pdo = OpenDBLinkA7();
        $sql = "UPDATE schedulingTeams SET MultiWeeks = ISNULL(MultiWeeks, 4) + ? WHERE (schedulingTeamId = ?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $WeekCount, PDO::PARAM_INT);
            $stmt->bindParam(2, $TeamId, PDO::PARAM_INT);
            return  $stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
          }
          return false;
    }

    /**
     * Description :Read getMultiWeekSeetmgByTeam;
     * @param int teamId
     * return int on Success Or false
     */

    function getMultiWeekSeetmgByTeam($TeamId) {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT MultiWeeks FROM schedulingTeams WHERE (schedulingTeamId = ?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $TeamId, PDO::PARAM_INT);
            $stmt->execute();
            $res =$stmt->fetch(PDO::FETCH_ASSOC);
           if(!empty($res)) {
               return $res['MultiWeeks'];
           }else{
               return false;
           }
        }catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
          }
        return false;
    }

    /**
     * Description :Read Hisdden Days History;
     * @param int teamId
     * @param string Date
     * return array on Success Or false
     */
    function getHideDayHistory($TeamId,$strDate) {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT History
        FROM AllocationsHiddenDays
        WHERE (SchedulingTeamId = ?) AND (dDate = CONVERT(DATETIME, ?, 102))";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $TeamId, PDO::PARAM_INT);
            $stmt->bindParam(2, $strDate, PDO::PARAM_STR);
            $stmt->execute();
            $res =$stmt->fetch(PDO::FETCH_ASSOC);
           if(!empty($res)) {
               return $res;
           }else{
               return false;
           }
        }catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
          }
    }


    /*
    * @Description : Fetch the  List of staff for specific TeamID by Scheduled Type.
	* @access : Public
	* @global : Not Applicable
	* @param  : $teamid ,$intScheduledTypeID
	* @return : JSON output
    */
    function GetListStaffByType($intTeamID,$intScheduledTypeID)
      {
          try {
              // Open the database
              $pdo = OpenDBLinkA7();
              // Set the statement to use
              $sql = "SELECT DISTINCT ud.UD_DisplayName AS userDisplayName,ud.UD_UserID as StaffID,ud.UD_StaffNumber as StaffNumber,spt.scheduledType
                FROM UserDetails(nolock)ud
                JOIN ScheduledPersonTeam_LINK spt on spt.ScheduledPersonID = ud.UD_UserID and   isnull(spt.EndDate,'9999-01-01') >= getdate()
                WHERE spt.TeamID = ?
                and spt.scheduledType = ?
                and ud.UD_TeampayStaffID > 0
                ORDER BY userDisplayName";
              $stmt = $pdo->prepare($sql);
              // The parameters
              $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
              $stmt->bindParam(2, $intScheduledTypeID, PDO::PARAM_INT);
              $stmt->execute();
              $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
              foreach ($result as $row => $data) {
                  $arrstaff[$row]['staffnumber'] = $data['StaffNumber'];
                  $arrstaff[$row]['StaffID'] = $data['StaffID'];
                  $arrstaff[$row]['name'] = $data['userDisplayName'];
              }
              if (isset($arrstaff)) {
                  return $resultjson = json_encode($arrstaff);
              } else {
                  return false;
              }
          } catch (PDOException $e) {
              logger()->critical('db error', (array) $e);
          }
      }


    /*
    * @Description : User Team By Scheduled type StffId
	* @access : Public
	* @global : Not Applicable
	* @param  : $teamid ,$intScheduledTypeID
	* @return : JSON output
    */
    function UserTeamByScheduledTypeByStaffId($strStaffNumber,$intScheduledTypeID)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_UserTeamByScheduledTypeByStaffid] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $strStaffNumber, PDO::PARAM_STR);
            $stmt->bindParam(2, $intScheduledTypeID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultjson = json_encode($result);
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

     /**
     * @params: $strlogin,$intgroupid
     * @desc:get admin leave request group admin from login
     */
    function GetAdminLeaveRequestGroupsAdminFromLogin ($strLogin, $intGroupID) {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $strQuery ="exec [dbo].[usp_fetchAdminStatusfromLeaveRequestGroups] :strLogin,:intGroupID";
            $stmt = $pdo->prepare($strQuery);
            $stmt->bindParam(':strLogin', $strLogin, PDO::PARAM_STR);
            $stmt->bindParam(':intGroupID', $intGroupID, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!isset($row) || !isset($row['Admin']) || is_null($row['Admin'])) {
                return(2);
            } else {
                return ($row['Admin']);
            }

        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }

    }

    /*
    * @Description : User Team By Scheduled type StffId
	* @access : Public
	* @global : Not Applicable
	* @param  : $teamid ,$intScheduledTypeID
	* @return : JSON output
    */
    function userLeaveRequestByNetLogin($strNetLogin)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_userLeaveRequestByNetLogin] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $strNetLogin, PDO::PARAM_STR);
            $stmt->execute();
            $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$arrUser=[];
            $arrUser['HasLeave'] = $arrUser['HasRequests'] = $arrUser['HasRequestsAdmin'] = $arrUser['HasLeaveAdmin'] = 0;

            foreach ($rsLeave as $row ) {
                $arrUser['LeaveRequests'][$row['ID']]['Description'] = $row['Description'];
                $arrUser['LeaveRequests'][$row['ID']]['Admin'] = $row['LeaveAdmin'];
                $arrUser['LeaveRequests'][$row['ID']]['IsPartDayLeaveAllowed'] = $row['IsPartDayLeaveAllowed'];
                $arrUser['IsHomeTeam'] =  !is_null($row['IsHomeTeam']) ? 1:0;
                if(!is_null($row['LeaveTypeID'])) {
                  $arrUser['LeaveRequests'][$row['ID']]['LeaveTypes'][$row['LeaveTypeID']] =  $row['LeaveTypeDescription'];
                }
                if($arrUser['HasLeave']==0){
                    $arrUser['HasLeave'] = ($row['LeaveAdmin'] == 0) && !is_null($row['LeaveTypeID']) ? 1:0;
                }
                if($arrUser['HasLeaveAdmin']==0){
                    $arrUser['HasLeaveAdmin'] = isset($row['LeaveAdmin']) && ($row['LeaveAdmin'] >= 1) ? 1:0;
                }

                if (!is_null($row['RequestTypeID'])) {
                    if($arrUser['HasRequests']==0){
                    $arrUser['HasRequests'] = isset($row['LeaveAdmin'] ) && ($row['LeaveAdmin'] == 0) ? 1:0;
                    }
                    if($arrUser['HasRequestsAdmin']==0){
                    $arrUser['HasRequestsAdmin'] = isset($row['LeaveAdmin']) && ($row['LeaveAdmin'] >= 1) ? 1:0;
                    }
                }

             }

            return $resultjson = json_encode($arrUser);
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    /*
    * @Description : User Team By Scheduled type StffId
	* @access : Public
	* @global : Not Applicable
	* @param  : $strNetLogin str,$selectedUserLogin str
	* @return : JSON output
    */
    function userLeaveRequestByNetLoginandRequesterID($strNetLogin,$selectedUserLogin)
    {
        try {
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_userLeaveRequestByNetLoginandRequester] ?,?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $strNetLogin, PDO::PARAM_STR);
            $stmt->bindParam(2, $selectedUserLogin, PDO::PARAM_STR);
            $stmt->execute();
            $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $arrUser = [];
            foreach ($rsLeave as $row ) {
                if(!is_null($row['LeaveTypeID'])) {
                        $arrUser['LeaveRequests'][$row['ID']]['Description'] = $row['Description'];
                        $arrUser['LeaveRequests'][$row['ID']]['Admin'] = $row['LeaveAdmin'];
                        $arrUser['LeaveRequests'][$row['ID']]['LeaveTypes'][$row['LeaveTypeID']] =  $row['LeaveTypeDescription'];
                        $arrUser['LeaveRequests'][$row['ID']]['IsPartDayLeaveAllowed'] = $row['IsPartDayLeaveAllowed'];
                }
                if ($row['LeaveAdmin'] == 0 && !is_null($row['LeaveTypeID'])) {
                  $arrUser['HasLeave'] = 1;
                }
                if ($row['LeaveAdmin'] >= 1) {
                  $arrUser['HasLeaveAdmin'] = 1;
                }
                if (!is_null($row['RequestTypeID'])) {
                  if ($row['LeaveAdmin'] == 0) {
                    $arrUser['HasRequests'] = 1;
                  }
                  if ($row['LeaveAdmin'] >= 1) {
                    $arrUser['HasRequestsAdmin'] = 1;
                  }
                }
             }

            return json_encode($arrUser);
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

    /*
*
* @Description : Fetch all of the Scheduled/Non-Scheduled staff details by team id.
* @access : Public
* @global : Not Applicable
* @param  : teamId
* @return : JSON output
*/
public function getAllHomeScheduledPeopleListsByTeamID($teamId){
    try {
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_AllHomeScheduledPersonListsByTeamID] ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1,$teamId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;
        } catch (PDOException $e) {
            logger()->critical('db error', (array) $e);
        }
    }

  /*
*
* @Description : Fetch user is divisional admin or not.
* @access : Public
* @global : Not Applicable
* @param  : userid
* @return : JSON output
*/
function UserIsDivAdmin($userid) {
    try {

        $isDivadmin = 0;
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "select distinct 1 from UserRoles inner join REF_Roles on RoleID = UR_RoleID and IsActive = 1 where RoleName = 'Area Admin' and UR_UserID = ?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1,$userid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty($result)){
            $isDivadmin = 1;
        }
        return $isDivadmin;

        } catch (PDOException $e) {
        echo $e->getMessage();
        }
    }
     /**
     * Description :GetCurrentNextPRevWeekNoByWeekNoFromTimeDim
     * @param int Weekno
     * return array on sucess or false
     */
    function GetCurrentNextPRevWeekNoByWeekNoFromTimeDim($Weekno) {
	    $pdo = OpenDBLinkA7();
        $sql = "exec [dbo].[usp_get_NextPRevWeekNoByWeekNo] ?";
	    try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1,$Weekno, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
          }
          return false;
    }

    /*
     * @Description : check in AD
     * @access : Public
     * @global : Not Applicable
     * @param  : $params
     * @return : string return and boolean
     */
    function GetUserDetailsFromDomaiDirectory($netLogin)
    {
        try {
            $result = [];
            // Open the database
            $pdo = OpenDBLinkA7();
            // Set the statement to use
            $sql = "exec [dbo].[usp_get_UserDetailsFromDomain] ?";
            $stmt = $pdo->prepare($sql);
            // The parameters
            $stmt->bindParam(1, $netLogin, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if(isset($result['UserEmployeeNumber']) && strlen($result['UserEmployeeNumber']) > 7) {
                $result['UserEmployeeNumber'] = ltrim($result['UserEmployeeNumber'], '0');
            }
            return json_encode($result);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
	/*
	*
	* @Description : Check user is area admin or not.
	* @access : Public
	* @global : Not Applicable
	* @param  : userid
	* @return : Bollean output or  exception message will echo
	*/

	function UserIsAreaAdmin($mainNetLogin, $requestedNetLogin)
	{
		try
		{
			$isAreaAdmin = false;
			$pdo = OpenDBLinkA7();
			// Set the statement to use
			$sql = "Select count(stdl.schedulingTeamId) totalTeamCount from UserRoles ur (nolock)
					inner join schedulingTeams stdl (nolock) on stdl.divisionId = ur.UR_DivisionId
					inner join UserDetails ud (nolock) on ud.UD_UserID = ur.UR_UserID
                    JOIN REF_Roles rr (nolock) on rr.RoleID = ur.UR_RoleID
					where ud.UD_NetLogin = ? and stdl.isActive = 1 AND rr.RoleName = 'Area Admin'
					and  exists(select 1 from schedulingTeams st
                    inner join ScheduledPersonTeam_LINK sptl (nolock) on sptl.TeamID = st.schedulingTeamId
                    inner join UserDetails ud (nolock) on ud.UD_UserID=sptl.ScheduledPersonID
                    where ud.UD_NetLogin = ? and sptl.IsHomeTeam = 1 AND sptl.EndDate >= cast(GETDATE() as date) and scheduledType = 1
                    AND stdl.schedulingTeamId = sptl.TeamID)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(1, $mainNetLogin, PDO::PARAM_INT);
			$stmt->bindParam(2, $requestedNetLogin, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($result['totalTeamCount'] > 0)
			{
				$isAreaAdmin = true;
			}
			return $isAreaAdmin;

		}catch (PDOException $e)
		{
			echo $e->getMessage();
		}
	}

    function updateLoginDateTimeofUser($userNetLogin='') {
        $pdo = OpenDBLinkA7();
        $utcDateTime = new DateTime('now', new DateTimeZone('UTC'));
        $londonDateTime = clone $utcDateTime;
        $londonDateTime->setTimezone(new DateTimeZone('Europe/London'));
        $loginDateTime = $londonDateTime->format('Y-m-d H:i:s.v');
        $sql = "UPDATE UserDetails SET UD_LastLoginDate = ? WHERE UD_NetLogin = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(1, $loginDateTime, PDO::PARAM_STR);
            $stmt->bindParam(2, $userNetLogin, PDO::PARAM_STR);
            return  $stmt->execute();
        } catch (PDOException $e) {
            logger()->critical('DB Error', (array) $e);
        }
        return false;
    }
}
