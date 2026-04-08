<?php
function ListAllRotas ($intAreaID, $teamId = '0') {
  // ##########################################################################################################################
  // ################### List all Rotas by Area                           #############################################
  // ################### Parameters: Area ID, Team ID                     #############################################
  // ################### id, RotaName, WeeksInRota                                #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_RotasByName] ?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $teamId, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function GetRotaDuties ($intRotaID, $intShowTemplates, $intShowAll, $strDate) {
  // ##########################################################################################################################
  // ################### List Rota Duties by ID                                  #############################################
  // ################### Parameters: RotaID                                      #############################################
  // ################### id, RotaName, WeeksInRota                                #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_RotaDuties] ?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intRotaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intShowTemplates, PDO::PARAM_INT);
    $stmt->bindParam(3, $intShowAll, PDO::PARAM_INT);
    $stmt->bindParam(4, $strDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function GetRotaDetailsByID ($intType, $intAreaID, $intRotaID) {
  // ##########################################################################################################################
  // ################### Function to get all the Rota Info                        #############################################
  // ################### Requires Rota ID                                         #############################################
  // ################### Returns All fields related                               #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################

    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_RotaDetails] ?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intType, PDO::PARAM_INT);
    $stmt->bindParam(2, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(3, $intRotaID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function GetRotaDutiesDetails ($intRotaID, $strDate) {
  // ##########################################################################################################################
  // ################### List Rota Duties by ID                                  #############################################
  // ################### Parameters: RotaID                                      #############################################
  // ################### id, RotaName, WeeksInRota                                #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_RotaDutiesDetails] ?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intRotaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $strDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function GetRotaDutyCounts ($intRotaID, $intBreaks, $strDate) {
  // ##########################################################################################################################
  // ################### List Rota Duties by ID                                  #############################################
  // ################### Parameters: RotaID                                      #############################################
  // ################### id, RotaName, WeeksInRota                                #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_RotaDutyCounts] ?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intRotaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intBreaks, PDO::PARAM_INT);
    $stmt->bindParam(3, $strDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function InsUpdRotaName($intID, $intTask, $intAreaID, $intTeamID, $strRota, $strNotes, $strStart, $intStartWeek, $intWeeks, $strLastMod, $strUser, $strHistory, $intNewRotaStartsWeek) {
  // ##########################################################################################################################
  // ################### Function to Update a Rota	                          #############################################
  // ################### intID = 0: Insert New Othewise update existing           #############################################
  // ################### Also updates History and History Table                   #############################################
  // ################### Returns Nothing                                          #############################################
  // ##########################################################################################################################
  $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $strCurrUser = $_SESSION['user']['FullName'];
  // Open the database
  $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_Rota] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intTask, PDO::PARAM_INT);
    $stmt->bindParam(3, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(4, $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(5, $strRota, PDO::PARAM_STR);
    $stmt->bindParam(6, $strStart, PDO::PARAM_STR);
    $stmt->bindParam(7, $intStartWeek, PDO::PARAM_INT);
    $stmt->bindParam(8, $intWeeks, PDO::PARAM_INT);
    $stmt->bindParam(9, $strNotes, PDO::PARAM_STR);
    $stmt->bindParam(10, $strLastMod, PDO::PARAM_STR);
    $stmt->bindParam(11, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(12, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(13, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(14, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(15, $strStatus, PDO::PARAM_STR);
    $stmt->bindParam(16, $intNewID, PDO::PARAM_INT);
    $stmt->bindParam(17, $intNewRotaStartsWeek, PDO::PARAM_INT);

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $intStatus = $result["intStatus"];
    $strStatus = $result["strStatus"];
    $intNewID = $result["newRotaID"];
    return array($intStatus, $strStatus, $intNewID);
}

function DeleteRota($intRotaID) {
  // ##########################################################################################################################
  // ################### Function to Remove a Duty from a Rota                    #############################################
  // ################### intID = 0: Insert New Othewise update existing           #############################################
  // ################### Also updates History and History Table                   #############################################
  // ################### Returns Nothing                                          #############################################
  // ##########################################################################################################################
    $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    $strCurrUser = $_SESSION['user']['FullName'];
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_del_Rota] ?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intRotaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(3, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(4, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(5, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(6, $strStatus, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $intStatus = $result["intStatus"];
    $strStatus = $result["strStatus"];
    return array($intStatus, $strStatus);
}

function DropDutyOnRota($intDutyID, $intDutyTypeID, $intRotaID, $intDayOfRota, $intWeek, $intAssigned, $intTemplate, $strStartDate, $strEndDate) {
  // ##########################################################################################################################
  // ################### Function to Update a Rota with a Duty                    #############################################
  // ################### intID = 0: Insert New Othewise update existing           #############################################
  // ################### Also updates History and History Table                   #############################################
  // ################### Returns Nothing                                          #############################################
  // ##########################################################################################################################
  $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $strCurrUser = $_SESSION['user']['FullName'];
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_RotaDuty] ?,?,?,?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intRotaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDutyID, PDO::PARAM_INT);
    $stmt->bindParam(3, $intDutyTypeID, PDO::PARAM_INT);
    $stmt->bindParam(4, $intDayOfRota, PDO::PARAM_INT);
    $stmt->bindParam(5, $intWeek, PDO::PARAM_INT);
    $stmt->bindParam(6, $intAssigned, PDO::PARAM_INT);
    $stmt->bindParam(7, $intTemplate, PDO::PARAM_INT);
    $stmt->bindParam(8, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(9, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(10, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(11, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(12, $strStatus, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $intStatus = $result["intStatus"];
    $strStatus = $result["strStatus"];
    return array($intStatus, $strStatus);
}

function RemoveDutyFromRota($intRotaID, $intDutyID, $intRotaDutyID, $strLastModDate) {
  // ##########################################################################################################################
  // ################### Function to Remove a Duty from a Rota                    #############################################
  // ################### intID = 0: Insert New Othewise update existing           #############################################
  // ################### Also updates History and History Table                   #############################################
  // ################### Returns Nothing                                          #############################################
  // ##########################################################################################################################
  $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? (int) $_SESSION['user']['UserID'] : (int) $_COOKIE['editWeeklyUserId'];
  $strCurrUser = $_SESSION['user']['FullName'];
  $RotaDutyIdInt = (int)$intRotaDutyID;

    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_del_RotaDuty] ?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $RotaDutyIdInt, PDO::PARAM_INT);
    $stmt->bindParam(2, $intCurrUserID, PDO::PARAM_INT);
    $stmt->execute();
    $Result = $stmt->fetch(PDO::FETCH_ASSOC);
    $intStatus = $Result["intStatus"];
    $strStatus = $Result["strStatus"];
    return array($intStatus, $strStatus);
}

function GetRotaPeople ($intRotaID) {
//function GetRotaPeople ($intRotaID, $strDate) {
  // ##########################################################################################################################
  // ################### List Rota Duties by ID                                  #############################################
  // ################### Parameters: RotaID                                      #############################################
  // ################### id, Ro[usp_mod_RotaPerson]taName, WeeksInRota                                #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_RotaPeople] ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intRotaID, PDO::PARAM_INT);
    // $stmt->bindParam(2, $strDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function DropPersonOnRota($intRotaPeopleID, $intRotaID, $intWeek, $intScheduledPersonID, $strStartDate, $strEndDate, $strLastMod, $strHistory) {
    // ##########################################################################################################################
    // ################### Function to Update a Rota	                          #############################################
    // ################### intID = 0: Insert New Othewise update existing           #############################################
    // ################### Also updates History and History Table                   #############################################
    // ################### Returns Nothing                                          #############################################
    // ##########################################################################################################################
    $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    $strCurrUser = $_SESSION['user']['FullName'];

    // Open the database
    $pdo = OpenDBLinkA7();
	$strStartDate = date('Y-m-d', strtotime($strStartDate));
	$strEndDate = date('Y-m-d', strtotime($strEndDate));
    $sql = "exec [dbo].[usp_mod_RotaPerson] ?,?,?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intRotaPeopleID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intRotaID, PDO::PARAM_INT);
    $stmt->bindParam(3, $intWeek, PDO::PARAM_INT);
    $stmt->bindParam(4, $intScheduledPersonID, PDO::PARAM_INT);
    $stmt->bindParam(5, $strStartDate, PDO::PARAM_STR);
    $stmt->bindParam(6, $strEndDate, PDO::PARAM_STR);
    $stmt->bindParam(7, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(8, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(9, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(10, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(11, $strStatus, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $intStatus = isset($result["intStatus"]) ? $result["intStatus"] : 0;
    $strStatus = isset($result["strStatus"]) ? $result["strStatus"] : '';
    return array($intStatus, $strStatus);
}

function GetRotasByPerson ($intPersonID, $requestDate=false) {
  // ##########################################################################################################################
  // ################### List Rota Duties by ID                                  #############################################
  // ################### Parameters: RotaID                                      #############################################
  // ################### id, RotaName, WeeksInRota                                #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################
    // Open the database

	if($requestDate == false)
	{
		//
		$currentDate = date('Y-m-d');
		$firstSatDate = date('Y-m-d', strtotime("first saturday of $currentDate"));
		if(strtotime($currentDate) < strtotime(str_replace('/','-',$firstSatDate)))
		{
			$dateNewFormate = date('Y-m-d', strtotime("-1 week $currentDate"));
			$firstSatDate = date('Y-m-d', strtotime("first saturday of $currentDate"));
		}
	}else
	{
		$firstSatDate = date('Y-m-d', strtotime($requestDate));
	}
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_GET_RotasByPerson] ?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intPersonID, PDO::PARAM_INT);
    $stmt->bindParam(2, $firstSatDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function DeletePersonFromRota ($rotaid, $rotapersonid) {
    // ##########################################################################################################################
    // ################### Delete person from rota table                                  #############################################
    // ################### Parameters: $rotaid and $rotapersonid                                     #############################################
    // ################### SP, usp_Delete_RotaPeople                                #############################################
    // ###################                                                          #############################################
    // ##########################################################################################################################
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_Delete_RotaPeople] ?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $rotaid, PDO::PARAM_INT);
    $stmt->bindParam(2, $rotapersonid, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $intStatus = $result["intStatus"];
    $strStatus = $result["strStatus"];
    return array($intStatus, $strStatus);
}

function validationForDiffDateweekRota($rotaid, $dutyid, $dayofrota) {
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_ValidateRotaPattern] ?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $rotaid, PDO::PARAM_INT);
    $stmt->bindParam(2, $dutyid, PDO::PARAM_INT);
    $stmt->bindParam(3, $dayofrota, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $strConflictRotaName = isset($result["strConflictRotaName"]) ? $result["strConflictRotaName"] : '';
    $intStatus = isset($result["intStatus"]) ? $result["intStatus"] : '-1';
    return array($strConflictRotaName, $intStatus);
}