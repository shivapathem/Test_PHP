<?php

function GetAllJobTypes () {
  $rsJobTypes = array();
  // Open the database
  $pdo = OpenDBLinkA7();

  // Set the statement to use
  $sql = "exec [dbo].[usp_get_AllJobTypes]";
  $stmt = $pdo->prepare($sql);
  // The parameters
  //None
    $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $rsJobTypes = json_encode($result);
   return $rsJobTypes;
}

function ListAllMasterDuties ($intAreaID, $intDutyTypeID, $intArchived, $teamIds = null, $strsearch = '')  {
  // ##########################################################################################################################
  // ################### List all Master Duties by Area                           #############################################
  // ################### Parameters: Area ID                                      #############################################
  // ################### Returns Master Duties                                    #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
  // Open the database
  $pdo = OpenDBLinkA7();
  // Set the statement to use
  $sql = "exec [dbo].[usp_GET_MasterDutiesReal] ?,?,?,?,?";
  $stmt = $pdo->prepare($sql);
  // The parameters
  $stmt->bindParam(1, $intAreaID, PDO::PARAM_INT);
  $stmt->bindParam(2, $intDutyTypeID, PDO::PARAM_INT);
  $stmt->bindParam(3, $intArchived, PDO::PARAM_INT);
  $stmt->bindParam(4, $teamIds, PDO::PARAM_STR);
  $stmt->bindParam(5, $strsearch, PDO::PARAM_STR);

  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $jsonresult = json_encode($result);
  return $jsonresult;
}

function ListAllMasterDutiesForRota ($intAreaID, $intDutyTypeID, $intArchived, $strSearchText, $strTeams)  {
  // ##########################################################################################################################
  // ################### List all Master Duties by Area                           #############################################
  // ################### Parameters: Area ID                                      #############################################
  // ################### Returns Master Duties                                    #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
  // Open the database
  $pdo = OpenDBLinkA7();
  // Set the statement to use
  $sql = "exec [dbo].[usp_GET_RotaMasterDuties] ?,?,?,?,?";
  $stmt = $pdo->prepare($sql);
  // The parameters
  $stmt->bindParam(1, $intAreaID, PDO::PARAM_INT);
  $stmt->bindParam(2, $intDutyTypeID, PDO::PARAM_INT);
  $stmt->bindParam(3, $intArchived, PDO::PARAM_INT);
  $stmt->bindParam(4, $strSearchText, PDO::PARAM_STR);
  $stmt->bindParam(5, $strTeams, PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $jsonresult = json_encode($result);
  return $jsonresult;
}

function GetUserAreaTeamsList ($intUserID, $intRoleId = 0) {
  // ##########################################################################################################################
  // ################### Function to get all the Teams for an Area                #############################################
  // ################### Requires no parameters                                   #############################################
  // ################### Returns All fields related                               #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
  // Open the database
  $pdo = OpenDBLinkA7();
  // Set the statement to use
  $sql = "exec [dbo].[usp_get_GetUserTeamList] ?,?";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1, $intUserID, PDO::PARAM_INT);
  $stmt->bindParam(2, $intRoleId, PDO::PARAM_INT);
//  $stmt->bindParam(2, $intType, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $jsonresult = json_encode($result);
  return $jsonresult;
}

function GetUserAreaTeamsListByFormId ($intUserID, $intRoleId = 0, $FormId=0) {

  // ##########################################################################################################################
  // ################### Function to get all the Teams for an Area                #############################################
  // ################### Requires no parameters                                   #############################################
  // ################### Returns All fields related                               #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
  // Open the database
  $pdo = OpenDBLinkA7();
  // Set the statement to use
  $sql = "exec [dbo].[usp_get_GetUserTeamList_ByFormId] ?,?,?";
  $stmt = $pdo->prepare($sql);

  $stmt->bindParam(1, $intUserID, PDO::PARAM_INT);
  $stmt->bindParam(2, $intRoleId, PDO::PARAM_INT);
  $stmt->bindParam(3, $FormId, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $jsonresult = json_encode($result);
  return $jsonresult;
}

function AreaTeamsListToArray ($rsJson) {
    $rs = json_decode($rsJson,true);
    $arrTeams[$rs['id']]['areaid'] = $rs['AreaID'];
    $arrTeams[$rs['id']]['teamname'] = $rs['TeamName'];

    if (isset($arrTeams)) {
      return($arrTeams);
    }
}

function PopulateTeamsDropDown ($rsJson, $intTeamID = 0, $intShowAll = 1) {
  $rs = json_decode($rsJson,true);
  $team_options="";

  if ($intShowAll == 0) {
    if ($intTeamID == 0) {
      $team_options.='<option value="" selected>Select The Team</option>';
    }
    else {
      $team_options.='<option value="">Select The Team</option>';
    }
  }
  else {
    if ($intTeamID == 0) {
      $team_options.='<option value="0" selected>All Teams</option>';
    }
    else {
      $team_options.='<option value="0">All Teams</option>';
    }
  }
  if(!empty($rs)){
    $arraySize = count($rs);
  }
  else{
    $arraySize = 1;
  }
   for($row = 0; $row < $arraySize; $row++) {
    if ($rs[$row]["TeamID"] == $intTeamID) {
      $team_options.='<option value="'.$rs[$row]["TeamID"].'" title="'.$rs[$row]["TeamName"].'" defaultrotaweek="'.$rs[$row]["defaultNumberweeksRotaPattern"].'" selected>'.substr($rs[$row]["TeamName"],0,25).'</option>';
    }
    else {
      $team_options.='<option value="'.$rs[$row]["TeamID"].'" title="'.$rs[$row]["TeamName"].'" defaultrotaweek="'.$rs[$row]["defaultNumberweeksRotaPattern"].'">'.substr($rs[$row]["TeamName"],0,25).'</option>';
    }
  }

  if (isset($team_options)) {
    return($team_options);
  }
}

function GetUserAreaPeopleList ($intuserid,$TeamId) {
  // ##########################################################################################################################
  // ################### Function to get all the People for an Area               #############################################
  // ################### Requires no parameters                                   #############################################
  // ################### Returns All fields related                               #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
  $rsPeopleList = array();
  $rsPeopleListJson = null;
  // Open the database
  $pdo = OpenDBLinkA7();
  // Set the statement to use
  $sql = "exec [dbo].[usp_get_AllPeopleByArea] ?,?";
  $stmt = $pdo->prepare($sql);
  // The parameters
  $stmt->bindParam(1, $intuserid, PDO::PARAM_INT);
  $stmt->bindParam(2, $TeamId, PDO::PARAM_INT);
  $stmt->execute();
  $rsPeopleList = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $rsPeopleListJson = json_encode($rsPeopleList);
  return $rsPeopleListJson;
}

function GetUserTeamPeopleList ($intType, $intUserID, $intTeamID, $StaffFlag) {
  // ##########################################################################################################################
  // ################### Function to get all the People for a Team                #############################################
  // ################### Requires no parameters                                   #############################################
  // ################### Returns All fields related                               #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################

  // Open the database
  $pdo = OpenDBLinkA7();
  // Set the statement to use
  $sql = "exec [dbo].[usp_get_AllPeopleByTeamIdRota] ?,?";
  $stmt = $pdo->prepare($sql);
  // The parameters
  $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
  $stmt->bindParam(2, $StaffFlag, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $jsonresult = json_encode($result);
  return $jsonresult;
}

function PopulateNumberDropDown ($intDefault, $intLowest, $intHighest) {
  $number_options = '';

  for ($i = $intLowest; $i <= $intHighest; $i++) {
    if ($i == $intDefault) {
      $number_options.='<option value="'.$i.'" selected>'.$i.'</option>';
    }
    else {
      $number_options.='<option value="'.$i.'">'.$i.'</option>';
    }

  }

  if (isset($number_options)) {
    return($number_options);
  }
}

function GetHighestRoleIdByForm($formid) {

  // ##########################################################################################################################
  // ################### Function to get the information on a highest role of user#############################################
  // ################### Requires user id form id  #############################################
  // ################### Returns highest role      #############################################
  // ###################                #############################################
  // ##########################################################################################################################
  // Open the database
  $pdo = OpenDBLinkA7();

  // Set the statement to use
  $sql = "exec [dbo].[usp_get_highestWaitUserRole] ?,?";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1,$_SESSION['user']["UserID"], PDO::PARAM_INT);
  $stmt->bindParam(2, $formid, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result['HighestRole'];
}
/*
* @Description : get history of division record
* @access : Public
* @global : Not Applicable
* @param  : $params
* @return : string
*/
function getHistoryLists($requestType = '', $attributeid = 0, $allocationId = 0)
{
    try {
        $pdo = OpenDBLinkA7();
        if ($requestType == 'PH') {
              $historyType = 'AllocationScheduledPerson';
              $historySubType = 'PH';
          } else {
              if ($requestType == 'DH') {
                  $requestType = 'AllocationDuty';
              }
              $historyType = $requestType;
              $historySubType = 'DH';
          }
		if($allocationId > 0)
		{
			$sql = "SELECT History, ? AS HistorySubType FROM History HT (NOLOCK) INNER JOIN HistoryTypes HY (NOLOCK) ON HY.ID = HT.HistoryType INNER JOIN AllocationsDuties AD ON AD.AD_AllocationsDutyID = HT.AttributeID WHERE AttributeID = ? AND HY.HistoryType = 'AllocationDuty' AND AD.AD_AllocationsID = ? ORDER BY [datetime] DESC, HistoryID DESC";//echo $sql;die;
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(1, $historySubType, PDO::PARAM_STR);
			$stmt->bindParam(2, $attributeid, PDO::PARAM_INT);
			$stmt->bindParam(3, $allocationId, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}else
		{
			$sql = "SELECT History,? AS HistorySubType FROM History HT (NOLOCK)
			INNER JOIN HistoryTypes HY (NOLOCK) ON HY.ID = HT.HistoryType
			WHERE AttributeID = ? AND HY.HistoryType = ?
			ORDER BY [datetime] DESC, HistoryID DESC";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(1, $historySubType, PDO::PARAM_STR);
			$stmt->bindParam(2, $attributeid, PDO::PARAM_INT);
			$stmt->bindParam(3, $historyType, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
        $resultjson = json_encode($result);
        return $resultjson;

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

/**
 * Get Week created history string
 */
function getWeekCreatedHistory($allocationId, $schecduledPersonId)
{
  try {
        $pdo = OpenDBLinkA7();
        $sql = "select  'Created '
            + ' On ' + FORMAT(AL_CreatedDate,'dd/MM/yyyy HH:mm')
            + ' By ' + UD.UD_DisplayName
            + ' assigned to '+SP.UD_DisplayName
            + '. Duty: U.' AS history
        from Allocations al
        inner join UserDetails ud on ud.UD_UserID = al.AL_CreatedBy
        inner join UserDetails sp on sp.UD_UserID = ?
        where AL_AllocationsID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $schecduledPersonId, PDO::PARAM_INT);
        $stmt->bindParam(2, $allocationId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

/*
* @Description : get roles
* @access : Public
* @global : Not Applicable
* @param  : $params
* @return : string
*/
function getRoleLists($action='',$roletype= '')
{
    try {
        $status = $returnstring = '';
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_get_rolesList] ?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $action, PDO::PARAM_STR);
        $stmt->bindParam(2, $roletype, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

/*
* @Description : Get  User team dropdown
* @access : Public
* @global : Not Applicable
* @param  :
* @return : Return the result which seearch by param
*/
function getUserAllTeamsLists()
{
  $roleId = '';
  $userID= $_SESSION['user']["UserID"];
  $maxAllowedRoleId = 6;	//only schedular[role id:6] and above can access team list
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_GetUserTeamList] ?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $userID, PDO::PARAM_INT);
    $stmt->bindParam(2, $roleId, PDO::PARAM_INT);
    $stmt->bindParam(3, $maxAllowedRoleId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

  function getStaffUserNetLogin($netlogin){
    try {
      // Open the database
      $pdo = OpenDBLinkA7();
      $sql = "exec [dbo].[usp_get_usersByNetlogin] ?";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(1, $netlogin, PDO::PARAM_STR);
      $stmt->execute();
      $resultUseresult = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $resultUseresult = json_encode($resultUseresult);

  } catch (PDOException $e) {
      echo $e->getMessage();
  }
  }

  /*
* @Description : get history of division record
* @access : Public
* @global : Not Applicable
* @param  : $params
* @return : string
*/
function getAdditioanlHistoryLists($modulename,$attributeid,$attributeid2)
{
    try {
        $status = $returnstring = '';
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_AdditionalPermissionHistoryData] ?,?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $modulename, PDO::PARAM_STR);
        $stmt->bindParam(2, $attributeid, PDO::PARAM_INT);
        $stmt->bindParam(3, $attributeid2, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;

    } catch (PDOException $e) {
        echo $e->getMessage();
    }


}

 /*
* @Description : get All Peopleby TeamId
* @access : Public
* @global : Not Applicable
* @param  : $params
* @return : string
*/

function getAllPeoplebyTeamId($teamid)
{
    try {
        $status = $returnstring = '';
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "select distinct sp.UD_UserID  ScheduledPersonID,
                  sp.UD_DisplayName  as Fullname,
                  1 as EFT
                from UserDetails as sp (nolock)
                inner join ScheduledPersonTeam_LINK as spt(nolock)  on sp.UD_UserID = spt.ScheduledPersonID
                where spt.TeamID = ?
                and  GETDATE() BETWEEN spt.StartDate and spt.EndDate
                AND spt.IsHomeTeam = 1
                and spt.scheduledType = 1";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $teamid, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function showPublishHistory($teamId,$weekNo)
{
    try {
        $status = $returnstring = '';
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "select History from History H inner join Allocations on AL_AllocationsID = h.AttributeID inner join  historytypes ht ON ht.id = h.historyType where ht.HistoryType ='PublishWeek' and AL_WeekNumber = ? and AL_SchedulingTeamID = ? order by [datetime] desc";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $weekNo, PDO::PARAM_INT);
        $stmt->bindParam(2, $teamId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function getLeaveRequesyHistoryLists($modulename,$netlogin)
{
    try {
        $status = $returnstring = '';
        // Open the database
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_GET_LeaveRequestHistorydata] ?,?";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(1, $modulename, PDO::PARAM_STR);
        $stmt->bindParam(2, $netlogin, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultjson = json_encode($result);
        return $resultjson;

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function getDayIndicatorData($teamId, $sDate, $eDate)
{
  $pdo = OpenDBLinkA7();
  $sql = "SELECT TD.dDateTime,
       CASE WHEN AHD.[isRestricted] = 1
	        THEN 'P'
			WHEN TD.dDateTime <= getdate() + 14
			THEN 'G'
	        WHEN TD.dDateTime <= getdate() + isnull(ST.maskafter,999) AND TD.dDateTime <= getdate() + 14
			THEN 'G'
	        WHEN TD.dDateTime <= getdate() + isnull(ST.maskafter,999) AND TD.dDateTime >= getdate() + 14
			THEN 'Y'
	   ELSE 'B' END AS BandColour
  FROM TimeDimension TD (NOLOCK)
  INNER JOIN schedulingTeams ST (NOLOCK) ON 1=1
  LEFT JOIN AllocationsHiddenDays AHD (NOLOCK) ON AHD.dDate = TD.dDateTime AND AHD.schedulingTeamId=ST.schedulingTeamId WHERE td.dDateTime between CONVERT(DATETIME,'$sDate',101) AND CONVERT(DATETIME,'$eDate',101)
   AND ST.schedulingTeamId = $teamId";
  $stmt = $pdo->prepare($sql);
  /*$stmt->bindParam(1, $sDate, PDO::PARAM_STR);
  $stmt->bindParam(2, $eDate, PDO::PARAM_STR);
  $stmt->bindParam(3, $teamId, PDO::PARAM_INT);*/
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $result;
}

/**
 * Get Week created history string
 */
function getPersonHistory($allocationId, $allocationsSPID)
{
  try {
        $pdo = OpenDBLinkA7();
        $sql = "SELECT History,
					   HistorySubType
				FROM
				(
				SELECT HT.History,
					   'PH' AS HistorySubType,
					   HT.[datetime],
					   HistoryID
				  FROM History HT (NOLOCK)
				INNER JOIN AllocationsScheduledPersons ASP on HT.AttributeID = ASP.ASP_AllocationsSPID
				INNER JOIN HistoryTypes HY (NOLOCK) ON HY.ID = HT.HistoryType
				WHERE ASP.ASP_AllocationsSPID = $allocationsSPID 
				   AND ASP.ASP_AllocationsID = $allocationId
				   AND HY.HistoryType = 'AllocationScheduledPerson'
				UNION
				SELECT History,
					   'PH' AS HistorySubType,
					   [datetime],
					   HistoryID
				  FROM History HT (NOLOCK)
				INNER JOIN AllocationsAddPersons AP ON AP.AAP_AllocationsAPID = HT.AttributeID
				INNER JOIN AllocationsScheduledPersons ASP on AP.AAP_AllocationsSPID = ASP.ASP_AllocationsSPID
				INNER JOIN HistoryTypes HY (NOLOCK) ON HY.ID = HT.HistoryType
				WHERE ASP.ASP_AllocationsSPID = $allocationsSPID
				   AND AAP_AllocationsID = $allocationId
				   AND HY.HistoryType = 'AllocationScheduledPersonAddnlTeam'
				) FD
				ORDER BY [datetime] DESC, HistoryID DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $allocationsSPID, PDO::PARAM_INT);
        $stmt->bindParam(2, $allocationId, PDO::PARAM_INT);
		$stmt->bindParam(3, $allocationsSPID, PDO::PARAM_INT);
        $stmt->bindParam(4, $allocationId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}