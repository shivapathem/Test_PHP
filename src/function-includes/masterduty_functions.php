<?php

function GetDutyDatesByID($intDutyID = 0) {
  // ##########################################################################################################################
  // ################### Function to return one or more Master Duty Dates         #############################################
  // ################### Not passing any parameters will return all Dutys         #############################################
  //#################### Passing a DutyID will return one Duty                    #############################################
  //#################### Passing a Netwotk Logon will return one Duty             #############################################
  // ##########################################################################################################################

    // Open the database
    $pdo = OpenDBLink();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_MasterDutyDates] ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function GetDutyDetailsByID($intDutyID = 0) {
    // ##########################################################################################################################
    // ################### Function to return one or more Dutys from the Duty table #############################################
    // ################### Not passing any parameters will return all Dutys         #############################################
    //#################### Passing a DutyID will return one Duty                    #############################################
    //#################### Passing a Netwotk Logon will return one Duty             #############################################
    // ##########################################################################################################################
    $intAreaID = 0;
    $intDutyType = 0;

    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_MasterDutyDetails] ?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDutyType, PDO::PARAM_INT);
    $stmt->bindParam(3, $intDutyID, PDO::PARAM_INT);

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function GetDutyHistoryByID ($intDutyID) {
  // ##########################################################################################################################
  // ################### Function to get the Duty History Info                    #############################################
  // ################### Requires Duty ID                                         #############################################
  // ################### Returns All fields related                               #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################

    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_MasterDuty_History] ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function GetDutyTeamsByDutyID ($intDutyID) {
  // ##########################################################################################################################
  // ################### Function to get all the Teams for a Duty                 #############################################
  // ################### Requires no parameters                                   #############################################
  // ################### Returns All fields related                               #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################

    // Open the database
    $pdo = OpenDBLink();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_AllTeamsByDuty] ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;
}

function GetRotasByDutyID ($intDutyID) {
  // ##########################################################################################################################
  // ################### Function to get all the Rotas for a Duty                 #############################################
  // ################### Requires no parameters                                   #############################################
  // ################### Returns All fields related                               #############################################
  // ###################                                                          #############################################
  // ##########################################################################################################################

    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_AllRotasByDuty] ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (is_array($result)) {
        $jsonresult = json_encode($result);
    }
    else {
        $jsonresult = json_encode(['RotaName'=>'No Record Found','WeeksInRota'=>'','RotaWeek'=>'','DOTW'=>'']);
    }

    return $jsonresult;
}

function InsUpdMasterDutyName($intID, $intAreaID, $intDutyType, $strDutyName, $strLastMod, $strHistory) {
  // ##########################################################################################################################
  // ################### Function to Update a Master Duty                         #############################################
  // ################### intID = 0: Insert New Othewise update existing           #############################################
  // ################### Also updates History and History Table                   #############################################
  // ################### Returns Nothing                                          #############################################
  // ##########################################################################################################################
  $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $strCurrUser = $_SESSION['user']['FullName'];
  $intNewDutyID = $intID;
  //$intStatus = 1;
  //$strStatus = '';

    // Open the database
    $pdo = OpenDBLink();
    // While adding the new duty we need last insert id so we have follow the Transaction concept
    $pdo->beginTransaction();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_MasterDutyName] ?,?,?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(3, $intDutyType, PDO::PARAM_INT);
    $stmt->bindParam(4, $strDutyName, PDO::PARAM_STR);
    $stmt->bindParam(5, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(6, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(7, $strLastMod, PDO::PARAM_STR);
    $stmt->bindParam(8, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(9, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(10, $intNewDutyID, PDO::PARAM_INT);
    $stmt->bindParam(11, $strStatus, PDO::PARAM_STR);
    $stmt->execute();
    $pdo->commit();
    if ($intNewDutyID == 0 ){
        $intNewDutyID = $pdo->lastInsertId(); // Not Needed as SP (usp_mod_MasterDutyName) Not Exists
    }
    return [$intNewDutyID, $intStatus, $strStatus];
}

function InsUpdMasterDutyDate($intDutyDateID, $intDutyID, $intDutyType, $strStartDate, $strEndDate, $strLastMod, $strHistory) {
  // ##########################################################################################################################
  // ################### Function to Update a Master Duty                         #############################################
  // ################### intID = 0: Insert New Othewise update existing           #############################################
  // ################### Also updates History and History Table                   #############################################
  // ################### Returns Nothing                                          #############################################
  // ##########################################################################################################################
  $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $strCurrUser = $_SESSION['user']['FullName'];

    $pdo = OpenDBLink();
  // Set the statement to use
    $sql = "exec [dbo].[usp_mod_MasterDutyDate] ?,?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $intDutyDateID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDutyID, PDO::PARAM_INT);
    $stmt->bindParam(3, $strStartDate, PDO::PARAM_STR);
    $stmt->bindParam(4, $strEndDate, PDO::PARAM_STR);
    $stmt->bindParam(5, $strLastMod, PDO::PARAM_STR);
    $stmt->bindParam(6, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(7, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(8, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(9, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(10, $strStatus, PDO::PARAM_STR);

    $stmt->execute();

  return [$intStatus, $strStatus];
}

function InsUpdMasterDutyDetails($intDutyID, $intTeamID,$intAreaID, $intDutyType, $strDutyName, $intStartTime, $intEndTime, $intDuration, $intWindow, $intColourID, $strBackColour, $strForeColour, $intSaturday, $intSunday, $intMonday, $intTuesday, $intWednesday, $intThursday, $intFriday, $strLastMod, $strHistory,$intStartYear,$intEndYear,$intStartWeek,$intEndWeek, $strBreakTime, $labelId1, $labelId2, $labelId3, $labelId4, $labelId5, $labelId6, $isNeedCovering, $isOverrideOver12) {
  // ##########################################################################################################################
  // ################### Function to Update a Master Duty                         #############################################
  // ################### intID = 0: Insert New Othewise update existing           #############################################
  // ################### Also updates History and History Table                   #############################################
  // ################### Returns Nothing                                          #############################################
  // ##########################################################################################################################

  $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $_SESSION['user']['DisplayName'] ??= '';
  $_SESSION['user']['PreferredForename'] ??= '';
  $strCurrUser = $_SESSION['user']['DisplayName'] ?: (($_SESSION['user']['PreferredForename'] ?: $_SESSION['user']['FullName']));
  $intStatus = 1;
  $isNightDuty = null;
  $strStatus = '';
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_MasterDutyDetails] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";

    $stmt = $pdo->prepare($sql);

    // The parameters
    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(3, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(4, $intDutyType, PDO::PARAM_INT);
    $stmt->bindParam(5, $strDutyName, PDO::PARAM_STR);
    $stmt->bindParam(6, $intDuration, PDO::PARAM_INT);
    $stmt->bindParam(7, $intWindow, PDO::PARAM_INT);
    $stmt->bindParam(8, $intStartTime, PDO::PARAM_INT);
    $stmt->bindParam(9, $intEndTime, PDO::PARAM_INT);
    $stmt->bindParam(10, $intStartYear, PDO::PARAM_INT);
    $stmt->bindParam(11, $intEndYear, PDO::PARAM_INT);
    $stmt->bindParam(12, $intStartWeek, PDO::PARAM_INT);
    $stmt->bindParam(13, $intEndWeek, PDO::PARAM_INT);
    $stmt->bindParam(14, $intColourID, PDO::PARAM_INT);
    $stmt->bindParam(15, $strBackColour, PDO::PARAM_STR);
    $stmt->bindParam(16, $strForeColour, PDO::PARAM_STR);
    $stmt->bindParam(17, $intMonday, PDO::PARAM_INT);
    $stmt->bindParam(18, $intTuesday, PDO::PARAM_INT);
    $stmt->bindParam(19, $intWednesday, PDO::PARAM_INT);
    $stmt->bindParam(20, $intThursday, PDO::PARAM_INT);
    $stmt->bindParam(21, $intFriday, PDO::PARAM_INT);
    $stmt->bindParam(22, $intSaturday, PDO::PARAM_INT);
    $stmt->bindParam(23, $intSunday, PDO::PARAM_INT);
    $stmt->bindParam(24, $strLastMod, PDO::PARAM_STR);
    $stmt->bindParam(25, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(26, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(27, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(28, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(29, $strStatus, PDO::PARAM_STR);
    $stmt->bindParam(30, $strBreakTime, PDO::PARAM_INT);
    $stmt->bindParam(31, $labelId1, PDO::PARAM_INT);
    $stmt->bindParam(32, $isNightDuty, PDO::PARAM_INT);
    $stmt->bindParam(33, $labelId2, PDO::PARAM_INT);
    $stmt->bindParam(34, $labelId3, PDO::PARAM_INT);
    $stmt->bindParam(35, $labelId4, PDO::PARAM_INT);
    $stmt->bindParam(36, $labelId5, PDO::PARAM_INT);
    $stmt->bindParam(37, $labelId6, PDO::PARAM_INT);
    $stmt->bindParam(38, $isNeedCovering, PDO::PARAM_INT);
    $stmt->bindParam(39, $isOverrideOver12, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!empty($result))
    {
      if(isset($result[0]['status']))
      {
        $intStatus = $result[0]['status'];
        $strStatus = $result[0]['returnstring'];
      }
    }
    return [$intStatus, $strStatus];
}

function DeleteMasterDuty($intDutyID, $intAction, $strLastModDate) {
  // ##########################################################################################################################
  // ################### Function to Update a Master Duty Job Link                #############################################
  // ################### intID = 0: Insert New Othewise update existing           #############################################
  // ################### Also updates History and History Table                   #############################################
  // ################### Returns Nothing                                          #############################################
  // ##########################################################################################################################
    $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    $strCurrUser = $_SESSION['user']['FullName'];
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_del_MasterDuty] ?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intAction, PDO::PARAM_INT);
    $stmt->bindParam(3, $strLastModDate, PDO::PARAM_STR);
    $stmt->bindParam(4, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(5, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(6, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(7, $strStatus, PDO::PARAM_STR);
    $stmt->bindParam(8, $strNewLastModDate, PDO::PARAM_STR);
    $stmt->execute();
    return [$intStatus, $strNewLastModDate];
}

function PopulateHoursDropDown ($intHour) {
  $hour_options = '';
  for ($i = 0; $i < 24; $i++) {
    $val = "0".strval($i);
    if ($i == $intHour) {
	$hour_options.='<option value="'.$i.'" selected>'.substr($val,-2).'</option>';
    }
    else {
	$hour_options.='<option value="'.$i.'">'.substr($val,-2).'</option>';
    }

  }

  if (isset($hour_options)) {
    return($hour_options);
  }
}

function PopulateMinutesDropDown ($intMinute) {
  $minute_options = '';
  for ($i = 0; $i <= 59; $i++) {
      if($i == 0 || $i == 15 || $i == 30 || $i == 45) {
          $val = "0" . strval($i);
          if ($i == $intMinute) {
              $minute_options .= '<option value="' . $i . '" selected>' . substr($val, -2) . '</option>';
          } else {
              $minute_options .= '<option value="' . $i . '">' . substr($val, -2) . '</option>';
          }
      }
  }

  if (isset($minute_options)) {
    return($minute_options);
  }
}

function PopulateMinutesDropDown_limited ($intMinute) {
  $minute_options = '';
  for ($i = 0; $i <= 59; $i++) {
    if($i == 0 || $i == 15 || $i == 30 || $i == 45) {
      $val = "0".strval($i);
      if ($i == $intMinute) {
        $minute_options.='<option value="'.$i.'" selected>'.substr($val,-2).'</option>';
      }
      else {
        $minute_options.='<option value="'.$i.'">'.substr($val,-2).'</option>';
      }
    }
  }

  if (isset($minute_options)) {
    return($minute_options);
  }
}

function GetDutyColourList($TeamID) {
  // ##########################################################################################################################
  // ################### Function to return one or more Duties from the Duty table #############################################
  // ################### Not passing any parameters will return all Dutys          #############################################
  //#################### Passing a DutyID will return one Duty                     #############################################
  //#################### Passing a Netwotk Logon will return one Duty              #############################################
  // ##########################################################################################################################

    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_get_MasterDutyColoursByArea] ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $TeamID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;

}

function PopulateDutyColoursDropDown($rsJson, $intDefaultID = 0, $dutyId = 0)
{
  $colour_options = "";
  $rs = json_decode((string) $rsJson, true);
  $ArrCount = count($rs);
  $colour_options .= '<option value="">Select The Colour</option>';
  $isSelected = false;
  for ($row = 0; $row < $ArrCount; $row++) {
    if ($rs[$row]["MasterDutyColourID"] == $intDefaultID) {
      $colour_options .= '<option value="' . $rs[$row]["MasterDutyColourID"] . '" selected>' . $rs[$row]["ColourName"] . '</option>';
      $isSelected = true;
    } elseif ($dutyId == 0 && $rs[$row]["IsDefaultColour"] == 1) {
      $colour_options .= '<option value="' . $rs[$row]["MasterDutyColourID"] . '" selected>' . $rs[$row]["ColourName"] . '</option>';
      $isSelected = true;
    } else {
      $colour_options .= '<option value="' . $rs[$row]["MasterDutyColourID"] . '">' . $rs[$row]["ColourName"] . '</option>';
    }
  }

  if (!$isSelected && $dutyId != 0) {
    $colour_options = '<option value="">Select The Colour</option>';
    for ($row = 0; $row < $ArrCount; $row++) {
        if ($rs[$row]["IsDefaultColour"] == 1) {
            $colour_options .= '<option value="' . $rs[$row]["MasterDutyColourID"] . '" selected>' . $rs[$row]["ColourName"] . '</option>';
        } else {
            $colour_options .= '<option value="' . $rs[$row]["MasterDutyColourID"] . '">' . $rs[$row]["ColourName"] . '</option>';
        }
    }
  }

  if (isset($colour_options)) {
    return ($colour_options);
  }
}

/* Created By:Naveeta
   Purpose:Get the week number dropdown
   Created On: 03-april-2021
*/
function PopulateWeekNumberDropDown($selectdWeek="", $year = '', $dutyID = 0){

  if ($year == '1995'){
      $weekRange = 48;
  }
  else {
      $weekRange = 1;
  }

  if($year == date("Y")){
    if($dutyID == 0){
    $selectdWeek = date("W");
    }
    if($selectdWeek == 'onchange'){
     $selectdWeek = date("W");
    }
  }
  $weeklist = range($weekRange,52);

  $week_list="";
  foreach($weeklist as  $row) {
    $selectvalue =  $selectdWeek == $row  ? "selected" : "";
    $week_list.='<option value="'.$row .'"'. $selectvalue.'>'.$row .'</option>';
  }

  if (isset($week_list)) {
    return($week_list);
  }

}

/* Created By:Naveeta
   Purpose:Teams dropdown
   Created On: 03-april-2021
*/
function TeamListDropDown($rsAreaTeams,$selectedTeam){
    $team_list = null;
    $rsTeams = json_decode((string) $rsAreaTeams,true);
    $team_list.='';
    $arrCount = count($rsTeams);
    $team_list.='<option value="">Select The Team</option>';
    for($row = 0; $row < $arrCount; $row++) {
      $selectvalue =  $selectedTeam == $rsTeams[$row]['TeamID']  ? "selected" : "";
      $team_list.='<option value="'.$rsTeams[$row]['TeamID'] .'"'. $selectvalue.'>'.$rsTeams[$row]['TeamName'] .'</option>';
    }
    if (isset($team_list)) {
      return($team_list);
    }

}

/* Created By:Naveeta
   Purpose:Chedck is master duty already available  or not with same name
   Created On: 07-april-2021
*/
function IsMasterDutyAvailable($duty_name,$duty_id){
    // Open the database
    $pdo = OpenDBLink();
    // Set the statement to use
    $sql = "Select id from MasterDuties where DutyName = ? and id != ?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $duty_name, PDO::PARAM_STR);
    $stmt->bindParam(2, $duty_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!empty($result)){
      return true;
    }else{
      return false;
    }
}

function GetMiscellaneousDutyTypes() {
    // ##########################################################################################################################
    // ################### Function to return one or more Duties from the Duty table #############################################
    // ################### Not passing any parameters will return all Dutys          #############################################
    //#################### Passing a DutyID will return one Duty                     #############################################
    //#################### Passing a Netwotk Logon will return one Duty              #############################################
    // ##########################################################################################################################

    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "select Id as MiscellaneousDutyTypeId, DutyTypeName from [dbo].[REF_DutyTypes] where Id <> 1";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $jsonresult = json_encode($result);
    return $jsonresult;

}

function PopulateMiscDutyTypes($MiscDutyTypes,$selectedDutyType, $intID)
{
    $MiscDutyTypeList = null;
    $MiscDutyTypesDecode = json_decode((string) $MiscDutyTypes,true);
    if($intID == 0)
    {
      $MiscDutyTypeList.='<option value="0">Select Duty Type</option>';
    }else
    {
      $MiscDutyTypeList.='<option value="0">Select Duty Type</option>';
    }
    $ArrCount = count($MiscDutyTypesDecode);
    for($row = 0; $row < $ArrCount; $row++) {
        $selectvalue =  ($intID >0 && $selectedDutyType == $MiscDutyTypesDecode[$row]['MiscellaneousDutyTypeId'])  ? "selected" : "";
        $MiscDutyTypeList.='<option value="'.$MiscDutyTypesDecode[$row]['MiscellaneousDutyTypeId'] .'"'. $selectvalue.'>'.$MiscDutyTypesDecode[$row]['DutyTypeName'] .'</option>';
    }
    if (isset($MiscDutyTypeList)) {
        return($MiscDutyTypeList);
    }
}

function InsUpdMiscellaneousDutyDetails($intDutyID,$strDutyName,$intTeamID,$intDutyType,$intDuration,$intDutyColour,$strHistory,$strBreakTime,$labelId1,$isNightDuty, $labelId2, $labelId3, $labelId4, $labelId5, $labelId6) {
    // ##########################################################################################################################
    // ################### Function to Update a Master Duty                         #############################################
    // ################### intID = 0: Insert New Othewise update existing           #############################################
    // ################### Also updates History and History Table                   #############################################
    // ################### Returns Nothing                                          #############################################
    // ##########################################################################################################################
    $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? (int) $_SESSION['user']['UserID'] : (int) $_COOKIE['editWeeklyUserId'];
    $strCurrUser = $_SESSION['user']['DisplayName'] ?: (($_SESSION['user']['PreferredForename'] ?: $_SESSION['user']['FullName']));
    $intAreaID = $_SESSION['user']['AreaID'];
    $intStatus = 1;
    $strStatus = '';
    if ($intDutyID == 0){
        $strHistory = "Created by ".$strCurrUser." on ".date("d M Y", time()) . ' at ' . date("H:i:s", time());
    }
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_mod_MiscellaneousDutyDetails] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);
    $stmt->bindParam(2, $strDutyName, PDO::PARAM_STR);
    $stmt->bindParam(3, $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(4, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(5, $intDutyType, PDO::PARAM_INT);
    $stmt->bindParam(6, $intDuration, PDO::PARAM_INT);
    $stmt->bindParam(7, $intDutyColour, PDO::PARAM_INT);
    $stmt->bindParam(8, $intCurrUserID, PDO::PARAM_INT);
    $stmt->bindParam(9, $strCurrUser, PDO::PARAM_STR);
    $stmt->bindParam(10, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(11, $intStatus, PDO::PARAM_INT);
    $stmt->bindParam(12, $strStatus, PDO::PARAM_STR);
    $stmt->bindParam(13, $strBreakTime, PDO::PARAM_INT);
	  $stmt->bindParam(14, $labelId1, PDO::PARAM_INT);
	  $stmt->bindParam(15, $isNightDuty, PDO::PARAM_INT);
    $stmt->bindParam(16, $labelId2, PDO::PARAM_INT);
    $stmt->bindParam(17, $labelId3, PDO::PARAM_INT);
    $stmt->bindParam(18, $labelId4, PDO::PARAM_INT);
    $stmt->bindParam(19, $labelId5, PDO::PARAM_INT);
    $stmt->bindParam(20, $labelId6, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $intStatus = $result["intStatus"];
    $strStatus = $result["strStatus"];
    $NewMiscDutyId = $result["NewMiscDutyId"];
    return [$intStatus, $strStatus, $NewMiscDutyId];
}

function copyDutyAndJobDetails($newDutyName, $intID, $intDutyType)
{
  /*
  Purpose   : This function is used to create new duties and its related jobs with refrence of existing given duty.
  Author    : HCL
  Created Dt: 09th Sept, 2021
  */
  $intCurrUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? (int) $_SESSION['user']['UserID'] : (int) $_COOKIE['editWeeklyUserId'];
  $strCurrUser = $_SESSION['user']['DisplayName'] ?: (($_SESSION['user']['PreferredForename'] ?: $_SESSION['user']['FullName']));
  $intStatus = 1;
  $strStatus = '';
  $strHistory = "Created by ".$strCurrUser." on ".date("d M Y", time()) . ' at ' . date("h:i:s", time());
  $pdo = OpenDBLinkA7();
  $sql  = "exec [dbo].[usp_copy_DutyAndJobDetails] ?,?,?,?,?,?,?,?";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1, $intID, PDO::PARAM_INT);
  $stmt->bindParam(2, $newDutyName, PDO::PARAM_STR);
  $stmt->bindParam(3, $intDutyType, PDO::PARAM_INT);
  $stmt->bindParam(4, $intCurrUserID, PDO::PARAM_INT);
  $stmt->bindParam(5, $strCurrUser, PDO::PARAM_STR);
  $stmt->bindParam(6, $strHistory, PDO::PARAM_STR);
  $stmt->bindParam(7, $intStatus, PDO::PARAM_INT);
  $stmt->bindParam(8, $strStatus, PDO::PARAM_STR);
  $stmt->execute();
  $result       =   $stmt->fetch(PDO::FETCH_ASSOC);
  return json_encode($result);
}

function verifyDutyAssignedtoRota($intDutyID) {
    // ##########################################################################################################################
    // ################### Function to Verify a duty is assigned to any rota        #############################################
    // ################### Returns Status 0 or 1                                    #############################################
    // ##########################################################################################################################
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "select 1 status from RotaDuties where MasterDutyID = ? and IsActive = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($result["status"]) && $result["status"] == 1) {
        $results = json_encode(["status"=>$result['status']]);
    } else {
        $results = json_encode(["status"=>0]);
    }
    return $results;
}

function getAllLabels()
{
    $pdo = OpenDBLinkA7();
    $sql = "SELECT Programme,ID from Programmes ORDER BY Programme";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rsResources = json_encode($result);
}

function PopulateYearsTimeDimension(){
  $pdo = OpenDBLinkA7();
  $sql = "SELECT DISTINCT ixYear from TimeDimension ORDER BY ixYear ASC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $years = array_map(fn($item) => $item['ixYear'],$result);

  $years = array_unique($years);
  sort($years);
  return $result;
}

function GenerateYearOptions($years, $selectedYear){
  $options = "";
  foreach($years as $year) {
    $selected =  ($year["ixYear"] == $selectedYear)  ? " selected" : "";
    $options .= '<option value="'.$year["ixYear"].'" '. $selected .'>'.$year["ixYear"].'</option>';
  }
  return $options;
}