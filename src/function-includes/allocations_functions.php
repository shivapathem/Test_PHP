<?php

// Generic variables ...................
$nameswidth = 200;
$scwidth = 150;
$rowheight = 45;
$intDayWidth = 150;
$intDayCount = 10;
$datesheight = 20;

function GetAllTeams($intType, $intAreaID, $intTeamTypeID) {
    // ##########################################################################################################################
    // ################### Function to get the Teams                                #############################################
    // ################### No Parameters                                            #############################################
    // ################### Returns All fields related                               #############################################
    // ###################                                                          #############################################
    // ##########################################################################################################################
    //Open the database
    $pdo = OpenDBLinkTeamWork();

    // Set the statement to use
    $sql = "exec [dbo].[usp_get_Teams] ?,?,?,?";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(1, $intType, PDO::PARAM_INT);
    $stmt->bindParam(2, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(3, $intTeamID, PDO::PARAM_STR);
    $stmt->bindParam(4, $intTeamTypeID, PDO::PARAM_STR);
    $stmt->execute();
    $rsTeams = $stmt->fetchall(PDO::FETCH_ASSOC);
    if (isset($rsTeams)) {
        return ($rsTeams);
    }
}

function GetAllocationsByDate($dteStartDate, $dteEndDate, $intAreaID, $intStaffID = 0) {

  // ##########################################################################################################################
  // ################### Function to get all duties allocated between 2 dates     #############################################
  // ################### AreaID, Start Date and End Date required                 #############################################
  // ##########################################################################################################################
    $pdo = OpenDBLinkTeamWork();
// Set the statement to use
    $sql = "exec [dbo].[usp_GET_AllocationsByDate] ?,?,?";
    $stmt = $pdo->prepare($sql);
    $dteStartDate =  date("Y-d-m",strtotime($dteStartDate));
    $dteEndDate =  date("Y-d-m",strtotime($dteEndDate));

    $stmt->bindParam(1, $intAreaID, PDO::PARAM_INT);
    $stmt->bindParam(2, $dteStartDate, PDO::PARAM_STR);
    $stmt->bindParam(3, $dteEndDate, PDO::PARAM_STR);
    $stmt->execute();
    $rsAlloc = $stmt->fetchall(PDO::FETCH_ASSOC);
  //echo '<pre>';
  foreach ($rsAlloc as $row ) {


    $dteDutyDate = date("Y-m-d", strtotime($row['DutyDate']));
    $intTeamworkID = $row['TeamworkID'];

    $arrAlloc[$intTeamworkID]['Name'] = $row['Surname'].', '.$row['Forename'];
    $arrAlloc[$intTeamworkID]['StaffNumber'] = $row['StaffNumber'];
    $arrAlloc[$intTeamworkID]['IsDefaultArea'] = $row['IsDefaultArea'];
    $arrAlloc[$intTeamworkID]['DefaultArea'] = $row['AreaName'];


    if (!is_null($row['SchedTeamIDList'])) {
      $arrSchedTeamIDList = ExplodeStringToArray($row['SchedTeamIDList']);
      $arrAlloc[$intTeamworkID]['SchedTeamIDs'] = $arrSchedTeamIDList;
      $arrAlloc[$intTeamworkID]['SchedTeamsNames'] = $row['SchedTeamNameList'];
    }



    if(isset($arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'])) {
      $intDutyCount = count($arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties']);
    }
    else {
      $intDutyCount = 0;
    }
    if (is_null($row['ID'])) {
      $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['id'] = 0;
    }
    else {
      $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['id'] = $row['ID'];
    }
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['StartTime'] = $row['StartTime'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['EndTime'] = $row['EndTime'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['Duration'] = $row['Duration'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['DutyName'] = $row['DutyName'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['AreaID'] = $row['AreaID'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['IsRota'] = $row['IsRota'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['EditStatus'] = $row['EditStatus'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['DutyTypeID'] = $row['DutyTypeID'];
    //$arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['CommentsPerson'] = $row['CommentsPerson'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['CommentsDuty'] = $row['CommentsDuty'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['TeamID'] = $row['TeamID'];

    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['MarkRequest'] = $row['MarkRequest'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['MarkAttention'] = $row['MarkAttention'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['MarkOvertime'] = $row['MarkOvertime'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['MarkPTExtraDay'] = $row['MarkPTExtraDay'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['MarkCompLeave'] = $row['MarkCompLeave'];
    $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['MarkCharging'] = $row['MarkCharging'];


    if (is_null($row['BackColour'])) {
      $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['BackColour'] = 'eeeeee';
    }
    else {
      $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['BackColour'] = $row['BackColour'];
    }
    if (is_null($row['ForeColour'])) {
      $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['ForeColour'] = '000000';
    }
    else {
      $arrAlloc[$intTeamworkID]['Dates'][$dteDutyDate]['Duties'][$intDutyCount]['ForeColour'] = $row['ForeColour'];
    }
  }

  $stmt->closeCursor();
  $intDoLeave = 1;
  if ($intDoLeave == 1) {
      // Now get the leacve requests..
      $pdo = OpenDBLinkTeamWork();
// Set the statement to use
      $sql = "exec [dbo].[usp_GET_LeaveRequestsByAreaAndDate] ?,?,?";
      $stmt = $pdo->prepare($sql);

      $dteStartDate =  date("Y-d-m", strtotime($dteStartDate));
      $dteEndDate =  date("Y-d-m", strtotime($dteEndDate));

      $stmt->bindParam(1, $intAreaID, PDO::PARAM_INT);
      $stmt->bindParam(2, $dteStartDate, PDO::PARAM_STR);
      $stmt->bindParam(3, $dteEndDate, PDO::PARAM_STR);

      $stmt->execute();
      $rsLeave = $stmt->fetchall(PDO::FETCH_ASSOC);
      foreach ($rsLeave as $row) {
          $dteDutyDate = date("Y-m-d", strtotime($row['dDate']));
          $arrAlloc[$row['TeamWorkStaffID']]['Dates'][$dteDutyDate]['Leave']['LeaveCatagory'] = $row['LeaveCatagory'];
          $arrAlloc[$row['TeamWorkStaffID']]['Dates'][$dteDutyDate]['Leave']['LeaveStatus'] = $row['LeaveStatus'];
          $arrAlloc[$row['TeamWorkStaffID']]['Dates'][$dteDutyDate]['Leave']['LeaveStatusDesc'] = $row['StatusDesc'];
          $arrAlloc[$row['TeamWorkStaffID']]['Dates'][$dteDutyDate]['Leave']['LeaveOK'] = $row['isOK'];
    }
  }

  if (isset($arrAlloc)) {
    return($arrAlloc);
  }
}

function UnallocToAlloc ($intSourceID, $intDestStaffidID, $intDate) {

    $dteDate = date("Y-m-d", $intDate);

    $pdo = OpenDBLinkTeamWork();

    $sql = "exec [dbo].[usp_UPD_AllocationsUnallocToAlloc] ?,?,?";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(1, $intSourceID, PDO::PARAM_INT);
    $stmt->bindParam(2, $intDestStaffidID, PDO::PARAM_INT);
    $stmt->bindParam(3, $dteDate, PDO::PARAM_STR);
    $stmt->execute();
}

function AllocToUnalloc ($intDutyID) {
  $intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
  $pdo = OpenDBLinkTeamWork();
  $sql = "exec [dbo].[usp_UPDINS_AllocationToUnallocated] ?";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(1, $intDutyID, PDO::PARAM_INT);

    $stmt->execute();

}
