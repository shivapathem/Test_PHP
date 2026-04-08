<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once __DIR__.'/DBHelper.php';
include_once __DIR__.'/helpers.php';
// ############################################################################### New Code

function ReadLeaveForRota ($strUser, $strStartDate, $strEndDate) {

  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT        LeaveApplications.dDate, LeaveApplications.Approved, LeaveApplications.isOK, LeaveApplications.ShortNotice, LeaveApplications.oversummer, leave_types.ID as TypeID, leave_types.description AS TypeDescription,
                             LeaveRequestGroups.Description AS GroupDescription
               FROM          LeaveApplications
               INNER JOIN    leave_types ON LeaveApplications.LeaveTypesID = leave_types.ID
               INNER JOIN    LeaveRequestGroups ON leave_types.GroupID = LeaveRequestGroups.ID
               WHERE         (LeaveApplications.dDate >= CONVERT(DATETIME, '$strStartDate 00:00:00', 102))
               AND           (LeaveApplications.Login = '$strUser')
               AND           (LeaveApplications.dDate <= CONVERT(DATETIME, '$strEndDate 00:00:00', 102))
               AND           (LeaveApplications.Deleted = 0)";

  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($result as $row) {
    $strDate = date("Y-m-d", strtotime($row['dDate']));
    $intTypeID = $row['TypeID'];
    $arrLeave[$strDate][$intTypeID]['Approved']  = $row['Approved'];
    $arrLeave[$strDate][$intTypeID]['isOK']  = $row['isOK'];
    $arrLeave[$strDate][$intTypeID]['ShortNotice']  = $row['ShortNotice'];
    $arrLeave[$strDate][$intTypeID]['OverSummer']  = $row['oversummer'];
    $arrLeave[$strDate][$intTypeID]['TypeDescription']  = $row['TypeDescription'];
    $arrLeave[$strDate][$intTypeID]['GroupDescription']  = $row['GroupDescription'];
  }
  if (isset($arrLeave)) {
    return $arrLeave;
  }
}

/**
  * Get the Sickness Allocations
  * @param  $strNewDate contains leave date
  * @param  $schedulingPersonId contains schedulingpersonId
  * @return array $result['MarkedSickness'];
  */
function GetAllocationSickness($strNewDate,$schedulingPersonId) {

$pdo = OpenDBLinkA7();
try{
      $strQuery = "select case when ASP_LeaveType IN (3,4,5) THEN 1 ELSE 0 END AS MarkedSickness
      from AllocationsScheduledPersons where ASP_SchedulingPersonID = ? and ASP_DutyDate = ?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $schedulingPersonId, PDO::PARAM_INT);
      $stmt->bindParam(2, $strNewDate, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result['MarkedSickness'] ?? 0;
   } catch (PDOException $e) {
  logger()->critical('db error', (array) $e);
  echo $e->getMessage();
 }
}

/**
  * Get the overtime Allocations
  * @param  $strNewDate contains leave date
  * @param  $schedulingPersonId contains schedulingpersonId
  * @return array $result['MarkedOvertime'];
  */
function GetAllocationOvertime($strNewDate,$schedulingPersonId) {

  $pdo = OpenDBLinkA7();
  try{
        $strQuery = "select ASP_MarkedOverTime AS MarkedOvertime
        from AllocationsScheduledPersons where ASP_SchedulingPersonID = ? and ASP_DutyDate = ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $schedulingPersonId, PDO::PARAM_INT);
        $stmt->bindParam(2, $strNewDate, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['MarkedOvertime'] ?? 0;
     } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
    echo $e->getMessage();
   }
  }

  /**
  * Get the ID of LeaveApplications for a particular date and schedulingperson
  * @param  $strNewDate contains leave date
  * @param  $schedulingPersonId contains schedulingpersonId
  * @return array $result['ID'];
  */
function GetLeaveApplicationbyDate($strNewDate,$schedulingPersonId) {

  $pdo = OpenDBLinkA7();
  try{
      $strQuery = "select ID from LeaveApplications (nolock) where dDate=CONVERT(DATETIME, '$strNewDate 00:00:00', 102)
      and SchedulingPersonID=N'$schedulingPersonId' and Deleted=0";

      $stmt = $pdo->prepare($strQuery);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if(!empty($result['ID'])){
        return 1;
      }else{
         return 0;
      }

  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
    echo $e->getMessage();
   }
}

/**
  * Get the charging Allocations
  * @param  $strNewDate contains leave date
  * @param  $schedulingPersonId contains schedulingpersonId
  * @return array $result['ChargingId'];
  */
function GetAllocationCharging($strNewDate,$schedulingPersonId) {

  $pdo = OpenDBLinkA7();
  try{
      $strQuery = "select C.ChargingId from Allocations (nolock) A INNER JOIN ChargingDutyMapping_Link (nolock) C
      ON C.AllocationId=A.ID  where A.DutyDate=CONVERT(DATETIME, '$strNewDate 00:00:00', 102)
      and isActive=1 and A.SchedulingPersonID=N'$schedulingPersonId'";

        $stmt = $pdo->prepare($strQuery);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty($result['ChargingId'])){
          return 1;
        }else{
           return 0;
        }
     } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
    echo $e->getMessage();
   }
  }

/**
  * Get the leave details of user
  * @param  $strUser contains netlogin
  * @param  $strStartDate contains startDate
  * @param  $strEndDate contains EndDate
  * @param  $schedulingPersonId contains schedulingpersonId
  * @return array $arrLeave
  */
function GetLeaveForUser ($strUser, $strStartDate, $strEndDate,$schedulingPersonId) {

  $pdo = OpenDBLinkA7();
  // Get the defaults (available, summer etc...)
  $arrLeave = GetLeaveDefaults ($strUser, $strStartDate, $strEndDate);
  $strToday = date("Y-m-d");
  $intYear = date("Y", strtotime("-3 months", strtotime($strStartDate)));
  $intNextYear = $intYear + 1;

  // ############################################################################# Get the leave Allocated.....

  try{
      $strQuery = "exec [dbo].[usp_get_SumLeaveAllocation] ?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $intYear, PDO::PARAM_INT);
      $stmt->bindParam(2, $schedulingPersonId, PDO::PARAM_INT);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }

  if (is_null($row['TotalLeave'])) {
      $arrLeave['UserLeave']['Allocated'] = 0;
  }
  else {
    $arrLeave['UserLeave']['Allocated'] = $row['TotalLeave'];
  }
  // ############################################################################# END Get the leave Allocated.....

  // ############################################################################# Get the count of requests already in.....
 try{
    $strQuery = "exec [dbo].[usp_get_LeaveCountApplications] ?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intYear, PDO::PARAM_STR);
    $stmt->bindParam(2, $intNextYear, PDO::PARAM_STR);
    $stmt->bindParam(3, $strUser, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }

    foreach($result as $row){
    $strCurrDate = date("Y-m-d", strtotime($row['dDate']));
    $arrLeave['Applications'][$row['TypeID']][$strCurrDate] = $row['CountApplications'];
  }

  // ############################################################################# And finally get this person's requests....

  try{
      $strQuery = "exec [dbo].[usp_get_LeaveApplicationsbyNetLogin] ?,?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $intYear, PDO::PARAM_STR);
      $stmt->bindParam(2, $intNextYear, PDO::PARAM_STR);
      $stmt->bindParam(3, $strUser, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }

  $intTotalHours = 0;
  $intExtraClicks = 0;
  $intSummerClicks = 0;
  $intApprovedClicks = 0;

  if(!empty($result) && count($result)>0){
    foreach($result as $row){
      $strCurrDate = date("Y-m-d", strtotime($row['dDate']));
      if (strtotime($strCurrDate) >= strtotime($strToday) || $row['Approved'] == 1) {
        if ($row['ExtraLeaveClicks'] > $intExtraClicks && isset($arrLeave['Available'][$row['LeaveTypesID']])) {
          $intExtraClicks = $row['ExtraLeaveClicks'];
        }
        $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['LeaveID'] = $row['LeaveID'];
        $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['Approved'] = $row['Approved'];
        $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['isOK'] = $row['isOK'];
        $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['ShortNotice'] = $row['ShortNotice'];
        $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['Hours'] = $row['HoursPerLeaveDay'];
        $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['CountLeave'] = $row['CountLeave'];
        $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['isPDLApplied'] = $row['IsPartDayLeave'];
        if (isset($arrLeave['Summer'][$row['LeaveTypesID']]['Dates'][$strCurrDate])) {
          $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['isSummer'] = 1;
          $intSummerClicks = $intSummerClicks + 1;
        }
        else {
          $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['isSummer'] = 0;
        }
        $arrLeave['UserLeave']['Dates'][$strCurrDate][$row['LeaveTypesID']]['OverSummer'] = $row['oversummer'];
        if ($row['countclicks'] == 1 && $row['CountLeave']==1) {
          $intTotalHours = $intTotalHours + $row['HoursPerLeaveDay'];
          $intApprovedClicks = $intApprovedClicks + 1;
        }
      }
    }

    if (isset($arrLeave['UserLeave']['Dates'])) {
      if($intApprovedClicks > 0){
        $arrLeave['UserLeave']['HoursPerDay'] = $intTotalHours / $intApprovedClicks;
        if ($arrLeave['UserLeave']['HoursPerDay'] == 0) {
            $arrLeave['UserLeave']['HoursPerDay'] = 10;
        }
        $arrLeave['UserLeave']['DayCount'] = $intApprovedClicks;
      } else{
          $arrLeave['UserLeave']['HoursPerDay'] = $row['HoursPerLeaveDay'];
          $arrLeave['UserLeave']['DayCount'] = 0;
      }
    }
    else {
      $arrLeave['UserLeave']['HoursPerDay'] = 10;
      $arrLeave['UserLeave']['DayCount'] = 0;
    }
    $arrLeave['UserLeave']['ExtraLeaveClicks'] = $intExtraClicks;
    $arrLeave['UserLeave']['TotalHours'] = $intTotalHours;
    $arrLeave['UserLeave']['SummerLeaveClicks'] = $intSummerClicks;

    $zeroCountLeave = GetZeroCountLeave ($intYear,$intNextYear,$strUser);
    $arrLeave['UserLeave']['zeroCountLeave'] = $zeroCountLeave;
  }
  else {
    // Get the defaults
    $intHoursPerDay = 0;
    try{
      $strQuery = "exec [dbo].[usp_get_LeaveRequestGroupsDetails] ?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }

    foreach($result as $row){
      if ($intExtraClicks < $row['ExtraLeaveClicks']) {
        $intExtraClicks = $row['ExtraLeaveClicks'];
      }
      if ($intHoursPerDay < $row['HoursPerLeaveDay']) {
        $intHoursPerDay = $row['HoursPerLeaveDay'];
      }

    }

    $arrLeave['UserLeave']['ExtraLeaveClicks'] = $intExtraClicks;
    $arrLeave['UserLeave']['HoursPerDay'] = $intHoursPerDay;
    $arrLeave['UserLeave']['DayCount'] = 0;
    $arrLeave['UserLeave']['TotalHours'] = 0;
    $arrLeave['UserLeave']['SummerLeaveClicks'] = 0;
  }
  return ($arrLeave);
}

/**
  * Get the count of CountLeave=0 in LeaveApplications
  * @param  $strUser contains netlogin
  * @param  $intYear contains startYear
  * @param  $intNextYear contains EndYear
  * @return array $result['CountLeave']
  */
function GetZeroCountLeave ($intYear,$intNextYear,$strUser) {
$pdo = OpenDBLinkA7();
  try{
      $strQuery = "exec [dbo].[usp_get_ZeroCountLeave] ?,?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $intYear, PDO::PARAM_STR);
      $stmt->bindParam(2, $intNextYear, PDO::PARAM_STR);
      $stmt->bindParam(3, $strUser, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
     $zeroCount = !empty($result) ? $result['CountLeave'] : 0;
     return $zeroCount;

  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }

}

function GetLeaveGroupAndTeamsFromID ($id = 0) {
  $pdo = OpenDBLinkA7();
  try{
      $strQuery = "exec [dbo].[usp_GET_LeaveGroupAndTeamsFromID] ?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $id, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }

  foreach($result as $row) {
    $arrLeaveGroups[$row['ID']]['Description'] = $row['Description'];
    $arrLeaveGroups[$row['ID']]['email'] = $row['email'];
    $arrLeaveGroups[$row['ID']]['emailcopiesto'] = $row['emailcopiesto'];
    $arrLeaveGroups[$row['ID']]['HoursPerLeaveDay'] = $row['HoursPerLeaveDay'];
    $arrLeaveGroups[$row['ID']]['ShowLeaveOverLimit'] = $row['ShowLeaveOverLimit'];
    $arrLeaveGroups[$row['ID']]['ExtraLeaveClicks'] = $row['ExtraLeaveClicks'];
    $arrLeaveGroups[$row['ID']]['SummerLeaveOverLimit'] = $row['SummerLeaveOverLimit'];

    if (!is_null($row['TypeID'])) {
      $arrLeaveGroups[$row['ID']]['Types'][$row['TypeID']]['Description'] = $row['TypeDescription'];
      $arrLeaveGroups[$row['ID']]['Types'][$row['TypeID']]['dependant'] = $row['dependant'];
      $arrLeaveGroups[$row['ID']]['Types'][$row['TypeID']]['countclicks'] = $row['countclicks'];
      $arrLeaveGroups[$row['ID']]['Types'][$row['TypeID']]['defaultamounts'] = $row['defaultamounts'];
      $arrLeaveGroups[$row['ID']]['Types'][$row['TypeID']]['LeaveStarts'] = $row['LeaveStarts'];
      $arrLeaveGroups[$row['ID']]['Types'][$row['TypeID']]['ShortNoticeLeaveStarts'] = $row['SNLeaveStarts'];
      $arrLeaveGroups[$row['ID']]['Types'][$row['TypeID']]['LeaveEnds'] = $row['LeaveEnds'];
    }
 }
  return ($arrLeaveGroups);
}

function GetLeaveTypeAndGroupFromID ($intLeaveTypeID) {
$pdo = OpenDBLinkA7();
try {
    $strQuery =  "SELECT  lg.ID as GroupID, lg.Description AS GroupDescription, lt.description AS TypeDescription FROM LeaveRequestGroups lg (nolock)INNER JOIN leave_types lt (nolock) ON lg.ID = lt.GroupID WHERE (lt.ID = ?)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intLeaveTypeID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($row);
  } catch(Exception $e) {
  logger()->critical('DB Error', (array) $e);
  }
}

Function ReadLeaveWeekly ($strUser='', $strStartDate='', $strEndDate='', $intAdmin = 0,$intWeekNumber='') {
  $pdo = OpenDBLinkA7();
  // Get the defaults (available, summer etc...)
  $arrLeave = GetLeaveDefaults ($strUser, $strStartDate, $strEndDate, $intAdmin);
 // Now Get All the Leave applications for these dates;
    $startDate = $strStartDate.' '.'00:00:00';
    $EndDate = $strEndDate.' '.'00:00:00';
    try {
    $strQuery = "exec [dbo].[usp_get_LeaveApplicationsByWeek] ?,?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $startDate, PDO::PARAM_STR);
    $stmt->bindParam(2, $EndDate, PDO::PARAM_STR);
    $stmt->bindParam(3, $intAdmin, PDO::PARAM_INT);
    $stmt->bindParam(4, $strUser, PDO::PARAM_STR);
    $stmt->execute();
    $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
    }
if(!empty($rsLeave) && count($rsLeave)>0){
  foreach($rsLeave as $row ){
    $strDate = date('Y-m-d',strtotime($row['dDate']));
    $intGroupID = $row['GroupID'];
    $intTypeID = $row['TypeID'];
    $intRequestID = $row['ID'];
    if (!is_null($row['ScheduledPersonID'])) {
      $arrScheduledPersonIDs[] = $row['ScheduledPersonID'];
    }
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['Login'] = $row['Login'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['FullName'] = $row['FullName'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['Approved'] = $row['Approved'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['ShortNotice'] = $row['ShortNotice'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['Attention'] = $row['Attention'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['Unlikely'] = $row['unlikely'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['OverSummer'] = $row['oversummer'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['IsOK'] = $row['isOK'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['CountLeave'] = $row['CountLeave'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['LeaveStartTime'] = $row['LeaveStartTime'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['LeaveEndTime'] = $row['LeaveEndTime'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['AllocationID'] = $row['AllocationID'];
    $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['IsAgreed'] = $row['IsAgreed'];
    $arrLeave['IsPartDayLeaveApplied'] = $row['IsPartDayLeaveApplied'];

    if (!is_null($row['OfficeComments']) || (!is_null($row['Comments']))) {
      $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['HasComments'] = 1;
    }
    else {
      $arrLeave['LeaveRequests'][$intGroupID][$intTypeID][$strDate][$intRequestID]['HasComments'] = 0;
    }
  }
}

  if (isset($arrScheduledPersonIDs)) {
    if($intAdmin ==0){
      $arrAllocations = weeklyLeaveAllocationsAndRota($intWeekNumber,$strUser,$startDate,$EndDate);
    }else{
      $arrAllocations = weeklyLeaveAdminAllocationsAndRota($intWeekNumber,$strUser,$startDate,$EndDate);
    }

   if (isset($arrAllocations)) {
      $arrLeave['Allocations'] = $arrAllocations;
    }
    $arrLeave['ScheduledPersonIDs'] = $arrScheduledPersonIDs;
  }

  if (isset($arrLeave)) {
    return ($arrLeave);
  }
}

function GetLeaveDefaults ($strUser, $strStartDate, $strEndDate, $intAdmin = 0) {

  $pdo = OpenDBLinkA7();
  $arrLeave=[];
  $dowMap = array(
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
  );
  $strToday = date("Y-m-d");
  $intYear = date("Y", strtotime("-3 months", strtotime($strStartDate)));
  $intNextYear = $intYear + 1;
    // #############################################################################  Get the default amounts  of leave available
  try{
    $strQuery = "exec [dbo].[usp_get_LeaveDefaultamounts] ?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intAdmin, PDO::PARAM_INT);
    $stmt->bindParam(2, $strUser, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }

if(!empty($result) && count($result)>0){
  foreach($result as $row){
    $defaultAmounts = $row['defaultamounts'] ?? '';
    $arrDays = explode(',', $defaultAmounts);

    for ($i=0; $i<=6; $i++) {
      if (isset($arrDays[$i])) {
        $arrDefaults[$row["TypeID"]][$i] = $arrDays[$i];
      }
      else {
        $arrDefaults[$row["TypeID"]][$i] = 0;
      }
    }
  }
}
  // ############################################################################# Now Read these into the Leave Array Dates
    $strDateLoop = $strStartDate;
    while (strtotime($strDateLoop) <= strtotime($strEndDate)) {
      $daynumber = $dowMap[date("D", strtotime($strDateLoop))];
      if (isset($arrDefaults)) {
        foreach ($arrDefaults as $intTypeID => $arrTypeDays) {
          if (isset($arrTypeDays[$daynumber])) {
            $arrLeave['Available'][$intTypeID][$strDateLoop] =  $arrTypeDays[$daynumber];
          }
          else {
            $arrLeave['Available'][$intTypeID][$strDateLoop] =  0;
          }
        }
	    }
      $strDateLoop = date ("Y-m-d", strtotime("+1 day", strtotime($strDateLoop)));
    }
  // ############################################################################# END Read these into the Leave Array Dates

  // ############################################################################# Get any Overridden dates

    $startDate = $strStartDate.' '.'00:00:00';
    $EndDate = $strEndDate.' '.'00:00:00';
    try {
    $strQuery = "exec [dbo].[usp_get_LeaveRequestsAvailability] ?,?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $startDate, PDO::PARAM_STR);
    $stmt->bindParam(2, $EndDate, PDO::PARAM_STR);
    $stmt->bindParam(3, $intAdmin, PDO::PARAM_INT);
    $stmt->bindParam(4, $strUser, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
    }

if(!empty($result) && count($result)>0){
  foreach($result as $row){
    $strCurrDate = date("Y-m-d", strtotime($row['dDate']));
    $arrLeave['Available'][$row['LeaveType']][$strCurrDate] = $row['Amount'];
  }
}
  // ############################################################################# END Get any Overridden dates

  // ############################################################################# Get any SummerLeave
  $TodayDate = $strToday.' '.'00:00:00';
  $Nextyear = $intNextYear.'-03-31'.' '.'00:00:00';
    try {
    $strQuery = "exec [dbo].[usp_get_SummerLeave] ?,?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $TodayDate, PDO::PARAM_STR);
    $stmt->bindParam(2, $Nextyear, PDO::PARAM_STR);
    $stmt->bindParam(3, $intAdmin, PDO::PARAM_INT);
    $stmt->bindParam(4, $strUser, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
    }

  $intSummerAllowedMax = 0;
 if(!empty($result) && count($result)>0){
  foreach($result as $row){
    $strSummerStartDate = date("Y-m-d", strtotime($row['dStart']));
    $strSummerEndDate = date("Y-m-d", strtotime($row['dEnd']));
    $intAllowed = ceil(($row['amount'] * $row['EFTSummer']) / 100);
    if ($intAllowed > $intSummerAllowedMax) {
      $intSummerAllowedMax = $intAllowed;
    }

    $strDateLoop = $strSummerStartDate;
    $arrLeave['Summer'][$row['TypeID']]['SummerStarts'] = $strSummerStartDate;
    $arrLeave['Summer'][$row['TypeID']]['SummerEnds'] = $strSummerEndDate;
    $arrLeave['Summer'][$row['TypeID']]['RequestsAllowed'] = $intAllowed;
    while (strtotime($strDateLoop) <= strtotime($strSummerEndDate)) {
      $arrLeave['Summer'][$row['TypeID']]['Dates'][$strDateLoop] = $row['amount'];
      $strDateLoop = date ("Y-m-d", strtotime("+1 day", strtotime($strDateLoop)));
    }
  }
  $arrLeave['SummerClicksAllowed'] = $intSummerAllowedMax;
}
  // ############################################################################# End any SummerLeave
  return ($arrLeave);
}

function GetLeaveUnapproved ($strUser) {
  $pdo = OpenDBLinkA7();
  $strToday = date("Y-m-d");
      // Set the statement to use
      $sql = "exec [dbo].[usp_get_UnapprovedLeave] ?,?";
      $stmt = $pdo->prepare($sql);
      // The parameters
      $stmt->bindParam(1, $strToday, PDO::PARAM_STR);
      $stmt->bindParam(2, $strUser, PDO::PARAM_STR);
      $stmt->execute();
      $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach($rsLeave as $row ){
    $strDate = date('Y-m-d',strtotime($row['dDate']));
    $intGroupID = $row['GroupID'];
    $intTypeID = $row['TypeID'];
    $intRequestID = $row['ID'];
    $intShortNotice = $row['ShortNotice'];
    $intIsOK = $row['isOK'];
    $strCreatedDate = date('jS M Y H:i',strtotime($row['Created']));
    if ($intShortNotice == 1) {
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['GroupID'] = $intGroupID;
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['GroupDescription'] = $row['GroupDescription'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['TypeID'] = $intTypeID;
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['TypeDescription'] = $row['TypeDescription'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['Login'] = $row['Login'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['FullName'] = $row['FullName'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['IsOK'] = $row['isOK'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['Approved'] = $row['Approved'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['Attention'] = $row['Attention'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['Unlikely'] = $row['unlikely'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['OverSummer'] = $row['oversummer'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['Created'] = $strCreatedDate;
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['OfficeComments'] = $row['OfficeComments']; ;
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['UserComments'] = $row['Comments'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['Deleted'] = $row['Deleted'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['History'] = $row['History'] ?? '';
	  $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['LeaveStartTime'] = $row['LeaveStartTime'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['LeaveEndTime'] = $row['LeaveEndTime'];
      $arrLeave['LeaveRequests']['ShortNotice'][$strDate][$intRequestID]['IsAgreed'] = $row['IsAgreed'];
    }
    else {
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['GroupID'] = $intGroupID;
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['GroupDescription'] = $row['GroupDescription'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['TypeID'] = $intTypeID;
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['TypeDescription'] = $row['TypeDescription'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['Login'] = $row['Login'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['FullName'] = $row['FullName'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['IsOK'] = $row['isOK'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['Approved'] = $row['Approved'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['Attention'] = $row['Attention'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['Unlikely'] = $row['unlikely'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['OverSummer'] = $row['oversummer'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['Created'] = $strCreatedDate;
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['OfficeComments'] = $row['OfficeComments']; ;
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['UserComments'] = $row['Comments'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['Deleted'] = $row['Deleted'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['History'] = $row['History'] ?? '';
	  $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['LeaveStartTime'] = $row['LeaveStartTime'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['LeaveEndTime'] = $row['LeaveEndTime'];
      $arrLeave['LeaveRequests']['General'][$strDate][$intRequestID]['IsAgreed'] = $row['IsAgreed'];
    }



  }
  if (isset($arrLeave)) {
    return ($arrLeave);
  }
}

function GetEmailsToSend($strUser)
{

  $pdo = OpenDBLinkA7();
  try {
    $strQuery = "exec [dbo].[usp_Get_EmailsToSend] ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
    $stmt->execute();
    $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $arrLeave = [];
    foreach ($rsLeave as $row) {
      $row['Login'] = strtolower(trim($row['Login']));
      $strDate = date("Y-m-d", strtotime($row['dDate']));
      $arrLeave[$row['Login']]['GroupID'] = $row['GroupID'];
      $arrLeave[$row['Login']]['grGroupID'][] = $row['GroupID'];
      $arrLeave[$row['Login']]['Name'] = $row['FullName'];
      $arrLeave[$row['Login']]['Dates'][$strDate]['desc'] = $row['TypeDescription'] . ' (' . $row['GroupDescription'] . ')';
      $arrLeave[$row['Login']]['Dates'][$strDate]['sGroupID'] = $row['GroupID'];
      $arrLeave[$row['Login']]['Dates'][$strDate]['sentOptionValue'] = $row['sentOptionValue'];
      if (!isset($arrLeave[$row['Login']]['Dates'][$strDate]['LeaveCategories'])) {
        $arrLeave[$row['Login']]['Dates'][$strDate]['LeaveCategories'] = [];
        $arrLeave[$row['Login']]['Dates'][$strDate]['LeaveCategoryAmounts'] = 0;
      }
      $arrLeave[$row['Login']]['Dates'][$strDate]['LeaveCategories'][] = $row['LeaveCategory'];
      $arrLeave[$row['Login']]['Dates'][$strDate]['LeaveCategoryAmounts'] += $row['LeaveCategoryAmount'];
    }

    if (isset($arrLeave)) {
      return ($arrLeave);
    } else {
      return false;
    }

  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
    echo $e->getMessage();
  }
}

function GetLeaveDefaultsByGroup ($intGroupID) {

  $pdo = OpenDBLinkA7();

  $strQuery = "SELECT id, defaultamounts
            FROM leave_types
            WHERE (GroupID = $intGroupID)";

  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach($result as $row){
    $arrDays = explode(',', $row['defaultamounts']);

    for ($i=0; $i<=6; $i++) {
      if (isset($arrDays[$i])) {
        $arrdefaults[$i][$row["id"]] = $arrDays[$i];
      }
      else {
        $arrdefaults[$i][$row["id"]] = 0;
      }
    }
  }
  if (isset($arrdefaults)) {
    return $arrdefaults;
  }
}

function ReadLeaveByDepartment($teamId, $startdate, $enddate) {

  $strQuery = "exec ReadLeaveByTeam ?, ?, ?";
  $pdo = OpenDBLinkA7();
  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $startdate, PDO::PARAM_INT);
  $stmt->bindParam(2, $enddate, PDO::PARAM_STR);
  $stmt->bindParam(3, $teamId, PDO::PARAM_STR);
  $stmt->execute();
  $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $date = $startdate;

  foreach($rsLeave as $row){
    $intLeaveGroupID = $row['LeaveGroupID'];
    $intLeaveTypeID = $row['LeaveTypeID'];
    $intApproved = $row['Approved'];
    $intIsOK = $row['isOK'];

    $arrLeave['Groups'][$intLeaveGroupID]['Description'] = $row['LeaveGroup'];
    $arrLeave['Groups'][$intLeaveGroupID]['Types'][$intLeaveTypeID] = $row['LeaveType'];

    if (isset($arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['Count'])) {
      $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['Count'] =$arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['Count'] + 1;
    }
    else {
      $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['Count'] = 1;
    }

    if ($intIsOK == 1) {
      if (isset($arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOK'])) {
        $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOK'] =$arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOK'] + 1;
      }
      else {
        $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOK'] = 1;
      }
    }
    else {
      if (isset($arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOK'])) {
        $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOK'] =$arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOK'] + 1;
      }
      else {
        $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOK'] = 1;
      }


    }
    if ($intApproved == 1) {
      if (isset($arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['Approved'])) {
        $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['Approved'] =$arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['Approved'] + 1;
      }
      else {
        $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['Approved'] = 1;
      }
      if ($intIsOK == 1) {
        if (isset($arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOKApproved'])) {
          $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOKApproved'] =$arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOKApproved'] + 1;
        }
        else {
          $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOKApproved'] = 1;
        }
      }
      else {
        if (isset($arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOKApproved'])) {
          $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOKApproved'] =$arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOKApproved'] + 1;
        }
        else {
          $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOKApproved'] = 1;
        }
      }
    }
    else {
      if ($intIsOK == 1) {
        if (isset($arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOKNotApproved'])) {
          $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOKNotApproved'] =$arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOKNotApproved'] + 1;
        }
        else {
          $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isOKNotApproved'] = 1;
        }
      }
      else {
        if (isset($arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOKNotApproved'])) {
          $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOKNotApproved'] =$arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOKNotApproved'] + 1;
        }
        else {
          $arrLeave['Leave'][$intLeaveGroupID][$intLeaveTypeID]['isNotOKNotApproved'] = 1;
        }
      }
    }
  }
  if (isset($arrLeave)) {
    return($arrLeave);
  }


}

function GetPercentageOfYear ($intCurrentYear) {
  $intYear = date("Y", strtotime("-3 months"));


  if ($intCurrentYear > $intYear) {
    $intPercentOfYear = 0;
  }
  else {
    if ($intCurrentYear < $intYear) {
      $intPercentOfYear = 100;
    }
    else {
      $date1 = new DateTime($intCurrentYear.'-04-01');
      $date2 = new DateTime(($intCurrentYear + 1).'-04-01');
      $date3 = new DateTime(date("Y-m-d"));
      $intDaysInYear  = $date2->diff($date1)->format('%a');

      // Now get the number of days we are in to the leave year
      $intDaysSoFar  = $date3->diff($date1)->format('%a');
      $intPercentOfYear = round($intDaysSoFar / $intDaysInYear * 100);
     }
   }
  return($intPercentOfYear);
}

function GetXmasPoints($arrScheduledPersonIDs) {
  $strScheduledPersonIDs = implode(",", $arrScheduledPersonIDs);
  $pdo = OpenDBLinkA7();
    try{
      $strQuery = "exec [dbo].[usp_get_xmaspoints] ?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $strScheduledPersonIDs, PDO::PARAM_STR);
      $stmt->execute();
      $rsXmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
    }
  foreach($rsXmas as $row){
    $arrXmasPoints[$row['Login']] = $row['Points'];
  }

  if (isset($arrXmasPoints)) {
    return ($arrXmasPoints);
  }
}

function GetLeaveAllowed ($intGroupID, $strStartDate, $strEndtDate){
   $dowMap = array(
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
  );

  $arrdefaults = GetLeaveDefaultsByGroup($intGroupID);

  if (is_array($arrdefaults)) {
    $arrLeaveAvailable = GetLeaveGroupAndTeamsFromID($intGroupID);
    // ***************************************************************  Now put the defaults into the leave array for the specified period
    $date = $strStartDate;
    while (strtotime($date) <= strtotime($strEndtDate)) {
      $daynumber = $dowMap[date("D", strtotime($date))];
      foreach ($arrLeaveAvailable[$intGroupID]['Types'] as $lt => $leavedesc) {
        if (isset($arrdefaults[$daynumber][$lt])) {
          $arrLeaveAvailable[$intGroupID]['Types'][$lt]['dates'][$date]['available'] =  $arrdefaults[$daynumber][$lt];
          $arrLeaveAvailable[$intGroupID]['Types'][$lt]['dates'][$date]['default'] =  $arrdefaults[$daynumber][$lt];
        }
        else {
          $arrLeaveAvailable[$intGroupID]['Types'][$lt]['dates'][$date]['available'] =  0;
          $arrLeaveAvailable[$intGroupID]['Types'][$lt]['dates'][$date]['default'] =  0;
        }
      }
	    $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
    }


    // ***************************************************************  Now get the overridden amounts
    $strQuery = "SELECT         LeaveRequestsAvailability.dDate, LeaveRequestsAvailability.LeaveType, LeaveRequestsAvailability.Amount
              FROM           LeaveRequestsAvailability
              INNER JOIN     leave_types ON LeaveRequestsAvailability.LeaveType = leave_types.id
              WHERE          (leave_types.GroupID = $intGroupID)
              AND (dDate >= CONVERT(DATETIME, '$strStartDate 00:00:00', 102))
              AND (dDate <= CONVERT(DATETIME, '$strEndtDate 00:00:00', 102))
              ORDER by dDate";

   $pdo = OpenDBLinkA7();
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach($result as $row) {

       $date = date("Y-m-d", strtotime($row['dDate']));
      if (isset($arrLeaveAvailable[$intGroupID]['Types'][$row['LeaveType']]['dates'][$date])) {
        $arrLeaveAvailable[$intGroupID]['Types'][$row['LeaveType']]['dates'][$date]['available'] = $row['Amount'];
      }
    }
    // *************************************************************** The array is filled with the amounts available

	return ($arrLeaveAvailable);
  }

}

function GetLeaveAllowedTotals($intGroupID, $strStartDate, $strEndDate) {

  $arrLeaveAllowed = GetLeaveAllowed($intGroupID, $strStartDate, $strEndDate);
  // Apend the reuuests
  $arrLeaveRequested = GetLeaveRequestedTotals($intGroupID, $strStartDate, $strEndDate);
  foreach ($arrLeaveAllowed[$intGroupID]['Types'] as $intTypeID => $arrType) {
    $arrTotals[$intTypeID]['UnderAvailable'] = 0;
    $arrTotals[$intTypeID]['MatchesAvailable'] = 0;
    $arrTotals[$intTypeID]['OverAvailable'] = 0;

    foreach ($arrType['dates'] as $strDate => $arrAmounts) {
      if (!isset($arrLeaveRequested[$intTypeID][$strDate])) {
        $intCountRequests = 0;
      }
      else {
        $intCountRequests = $arrLeaveRequested[$intTypeID][$strDate];
      }

      if (!isset($arrTotals[$intTypeID]['Available'])) {
        $arrTotals[$intTypeID]['Available'] = $arrAmounts['available'];
      }
      else {
        $arrTotals[$intTypeID]['Available'] = $arrTotals[$intTypeID]['Available'] + $arrAmounts['available'];
      }
      // Under, matches or over available?
      // Under
      if ($intCountRequests < $arrAmounts['available']) {
        $arrTotals[$intTypeID]['UnderAvailable'] = $arrTotals[$intTypeID]['UnderAvailable'] + 1;
      }
      if ($intCountRequests == $arrAmounts['available']) {
        $arrTotals[$intTypeID]['MatchesAvailable'] = $arrTotals[$intTypeID]['MatchesAvailable'] + 1;
      }
      if ($intCountRequests > $arrAmounts['available']) {
        $arrTotals[$intTypeID]['OverAvailable'] = $arrTotals[$intTypeID]['OverAvailable'] + 1;
      }
    }
  }
  if (isset($arrTotals)) {
    return ($arrTotals);
  }
}

function GetLeaveRequestedTotals($intGroupID, $strStartDate, $strEndDate) {
  $pdo = OpenDBLinkA7();
  $strQuery = "EXEC [dbo].[Usp_getLeaveRequestedTotals] ?, ?, ?";

  $stmt = $pdo->prepare($strQuery);
  $stmt->bindParam(1, $intGroupID, PDO::PARAM_INT);
  $stmt->bindParam(2, $strStartDate, PDO::PARAM_STR);
  $stmt->bindParam(3, $strEndDate, PDO::PARAM_STR);
  $stmt->execute();
  $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($rsLeave as $row){
    $date = date('Y-m-d', strtotime($row['dDate']));
    $arrLeaveRequested[$row['LeaveTypesID']][$date] = $row['CountRequests'];


  }

  if (isset($arrLeaveRequested)) {
    return ($arrLeaveRequested);
  }
}

function MakeLeaveOK ($intID) {

  $pdo = OpenDBLinkA7();
  try{
    $strHistory = '<hr>This Leave Request which was marked as on the waiting list was checked against the availability and was amended to be OK on '.date("d/m/Y").' at '.date("H:i");

    $strQuery = "UPDATE  LeaveApplications SET isOK = 1  WHERE (ID = $intID)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();

    $historytype = 15;
    $strDateNow = date("Y-m-d H:i:s");
    $currentUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    InsertHistory($historytype,$currentUserID,$strHistory,$strDateNow,$intID);

  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
}
}

function GetLeaveAllocateTypes($intCanCredit=1) {
	$pdo = OpenDBLinkA7();
	try {
		$strQuery = "SELECT id, Description, AllocName,DisplayAllocName, ScheID, isCreditable, ShowZero, IncludeInReports, CalcInReports, HasCredits, SelectiveHide FROM LeaveAllocateTypes (nolock) ORDER BY SortOrder";
		$stmt = $pdo->prepare($strQuery);
		$stmt->execute();
		$rsLeaveTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!empty($rsLeaveTypes) && count($rsLeaveTypes)>0) {
			foreach ($rsLeaveTypes as $key => $row) {
				$arrLeaveTypes[$row['id']]['ID'] = $row['id'];
        $arrLeaveTypes[$row['id']]['Description'] = $row['Description'];
				$arrLeaveTypes[$row['id']]['AllocName'] = $row['AllocName'];
				$arrLeaveTypes[$row['id']]['SchedID'] = $row['ScheID'];
				$arrLeaveTypes[$row['id']]['isCreditable'] = $row['isCreditable'];
				$arrLeaveTypes[$row['id']]['ShowZero'] = $row['ShowZero'];
				$arrLeaveTypes[$row['id']]['IncludeInReports'] = $row['IncludeInReports'];
				$arrLeaveTypes[$row['id']]['CalcInReports'] = $row['CalcInReports'];
				$arrLeaveTypes[$row['id']]['HasCredits'] = $row['HasCredits'];
				$arrLeaveTypes[$row['id']]['SelectiveHide'] = $row['SelectiveHide'];
        $arrLeaveTypes[$row['id']]['DisplayAllocName'] = $row['DisplayAllocName'];
			}
			return ($arrLeaveTypes);
		}

	} catch(Exception $e) {
       logger()->critical('DB Error', (array) $e);
	}
}

function GetLeaveCredits($strStaffNumbers, $strScheduledPeopleId,$intLeaveYear) {

  $pdo = OpenDBLinkA7();
  $strQuery = "SELECT          AllocName, isCreditable
               FROM            LeaveAllocateTypes (nolock)";
  $stmt = $pdo->prepare($strQuery);
  $stmt->execute();
  $rsLeaveAllocateTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach($rsLeaveAllocateTypes as $row){
    $arrEditable[$row['AllocName']] = $row['isCreditable'];
  }

    $strQuery1 = "exec [dbo].[usp_get_ScheduledPersonLeaveCredit] ?,?";
    $stmt = $pdo->prepare($strQuery1);
    $stmt->bindParam(1, $intLeaveYear, PDO::PARAM_INT);
    $stmt->bindParam(2, $strScheduledPeopleId, PDO::PARAM_INT);
    $stmt->execute();
    $rsLeaveAllocated = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $arrLeaveCredired['Types']['Annual'] = 0;
  $arrLeaveCredired['Types']['PHL'] = 0;
  $arrLeaveCredired['Types']['Comp'] = 0;
  $arrLeaveCredired['Types']['Additional'] = 0;
  $arrLeaveCredired['Types']['Exceptional'] = 0;
  $arrLeaveCredired['Types']['TOIL'] = 0;
  $arrLeaveCredired['Types']['Under11TOIL'] = 0;
  $arrLeaveCredired['Types']['Over12TOIL'] = 0;
  $arrLeaveCredired['Types']['Casual'] = 0;
  $arrLeaveCredired['Types']['Other'] = 0;
  $arrLeaveCredired['Types']['LongService'] = 0;
  foreach($rsLeaveAllocated as $row){
    $intID = $row['ID'];
    $arrLeaveCredired['Leave'][$intID]['CanEdit'] = 0;
    if ($row['Annual'] != 0) {
      $arrLeaveCredired['Types']['Annual'] = 1;
      $arrLeaveCredired['Leave'][$intID]['Annual'] = $row['Annual'];
      if ($arrEditable['Annual'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }

    if ($row['PHL'] != 0) {
      $arrLeaveCredired['Types']['PHL'] = 1;
      $arrLeaveCredired['Leave'][$intID]['PHL'] = $row['PHL'];
      if ($arrEditable['PHL'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }

    if ($row['Comp'] != 0) {
      $arrLeaveCredired['Types']['Comp'] = 1;
      $arrLeaveCredired['Leave'][$intID]['Comp'] = $row['Comp'];
      if ($arrEditable['Comp'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }

    if ($row['Additional'] != 0) {
      $arrLeaveCredired['Types']['Additional'] = 1;
      $arrLeaveCredired['Leave'][$intID]['Additional'] = $row['Additional'];
      if ($arrEditable['Additional'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }

    if ($row['Exceptional'] != 0) {
      $arrLeaveCredired['Types']['Exceptional'] = 1;
      $arrLeaveCredired['Leave'][$intID]['Exceptional'] = $row['Exceptional'];
      if ($arrEditable['Exceptional'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }

    if ($row['TOIL'] != 0) {
      $arrLeaveCredired['Types']['TOIL'] = 1;
      $arrLeaveCredired['Leave'][$intID]['TOIL'] = $row['TOIL'];
      if ($arrEditable['TOIL'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] = 1;
      }
    }

    if ($row['Under11TOIL'] != 0) {
      $arrLeaveCredired['Types']['Under11TOIL'] = 1;
      $arrLeaveCredired['Leave'][$intID]['Under11TOIL'] = $row['Under11TOIL'];
      if ($arrEditable['Under11TOIL'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }

    if ($row['Over12TOIL'] != 0) {
      $arrLeaveCredired['Types']['Over12TOIL'] = 1;
      $arrLeaveCredired['Leave'][$intID]['Over12TOIL'] = $row['Over12TOIL'];
      if ($arrEditable['Over12TOIL'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }
    if ($row['Casual'] != 0) {
      $arrLeaveCredired['Types']['Casual'] = 1;
      $arrLeaveCredired['Leave'][$intID]['Casual'] = $row['Casual'];
      if ($arrEditable['Casual'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }

    if ($row['Other'] != 0) {
      $arrLeaveCredired['Types']['Other'] = 1;
      $arrLeaveCredired['Leave'][$intID]['Other'] = $row['Other'];
      if ($arrEditable['Other'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }

    if ($row['LongService'] != 0) {
      $arrLeaveCredired['Types']['LongService'] = 1;
      $arrLeaveCredired['Leave'][$intID]['LongService'] = $row['LongService'];
      if ($arrEditable['LongService'] == 1) {
        $arrLeaveCredired['Leave'][$intID]['CanEdit'] += 1;
      }
    }
    $arrLeaveCredired['Leave'][$intID]['Comments'] = $row['Comments'];
    $arrLeaveCredired['Leave'][$intID]['schedulingTeamName'] = '';
    $arrLeaveCredired['Leave'][$intID]['IsCarryOver'] = $row['IsCarryOver'];
    if(($row['IsCarryOver'] == 0) &&  ($arrLeaveCredired['Leave'][$intID]['CanEdit'] >1 )){
      $arrLeaveCredired['Leave'][$intID]['IsCarryOver'] = 1;
    }
    if (isset($row['schedulingTeamName']) && !is_null($row['schedulingTeamName'])) {
      $arrLeaveCredired['Leave'][$intID]['schedulingTeamName'] = $row['schedulingTeamName'];
    }
    $arrLeaveCredired['Leave'][$intID]['Date'] = date("Y-m-d", strtotime($row['dDate']));

  }

  if (isset($arrLeaveCredired)) {
    return ($arrLeaveCredired);
  }
}

 /*
  * @Description : Insert Update summer leave
	* @access : Public
	* @global : Not Applicable
	* @param  : $year,$intleavegroupid,$startdate,$enddate,$untildate,$amount
	* @return :result
  */
function insUpdSummerLeave($year,$intLeaveGroupID,$startdate,$enddate,$untildate,$amount,$userid){
  $pdo = OpenDBLinkA7();

  $sql = "exec [dbo].[usp_insup_SummerLeave] ?,?,?,?,?,?,?";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1, $year, PDO::PARAM_STR);
  $stmt->bindParam(2, $intLeaveGroupID, PDO::PARAM_INT);
  $stmt->bindParam(3, $startdate, PDO::PARAM_STR);
  $stmt->bindParam(4, $enddate, PDO::PARAM_STR);
  $stmt->bindParam(5, $untildate, PDO::PARAM_STR);
  $stmt->bindParam(6, $amount, PDO::PARAM_INT);
  $stmt->bindParam(7, $userid, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $result;
}

 /*
  * @Description :get summer leave details
	* @access : Public
	* @global : Not Applicable
	* @param  :$intLeaveGroupID,$year
	* @return :array result
  */
function GetSummerLeaveDetail($intLeaveGroupID,$year) {

  $pdo = OpenDBLinkA7();
  $query = "SELECT  id, GroupID, iYear, dStart, dEnd, dUntil, amount
          FROM SummerLeave
          WHERE  (GroupID = $intLeaveGroupID)
          AND (iYear = $year)";

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  return $result;
}

function GetCurrentLeaveYearGeneric(){
  if ( date('m') > 3 ) {
    $currentLeaveYear = date('Y') ;
  }
  else {
    $currentLeaveYear = date('Y') -1;
  }
  return $currentLeaveYear;
}
/*
  * @Description :get summer leave details
	* @access : Public
	* @global : Not Applicable
	* @param  :$intLeaveGroupID,$year
	* @return :array result
  */
   function GetLeavExceptionalTypes($leavedate) {

    $pdo = OpenDBLinkA7();
    $query = "select ID,Name from LeaveExceptionalTypes (nolock) where
    '".$leavedate."' between ValidFrom and isnull (ValidTo, '".$leavedate."')
    order by Name asc";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }

 /*
  * @Description :get leave types which are not to be counted
	* @access : Public
	* @global : Not Applicable
	* @return :array result
  */
  function GetIsLeaveCounted() {

    $pdo = OpenDBLinkA7();
    $query = "select id from LeaveAllocateTypes (nolock) where isLeaveCounted=0";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }

  /*
  * @Description : get id of exceptional from LeaveAllocateTypes table
  * @access : Public
  * @global : Not Applicable
  * @return : array result
  */
  function GetIdExceptionalAllocateType() {
    $pdo = OpenDBLinkA7();
    $query = "select id from LeaveAllocateTypes (nolock) where AllocName='Exceptional'";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['id'];
  }

/*
  * @Description :Leave Application
	* @access : Public
	* @global : Not Applicable
	* @param  :$intLeaveGroupID
	* @return :array result
  */
  function GetLeaveGroupTypeDetails($intApplicationID) {

    $pdo = OpenDBLinkA7();
    $query = "SELECT     LeaveRequestGroups.Description + N' - ' + leave_types.description AS GroupAndType
    FROM       LeaveApplications
    INNER JOIN leave_types ON LeaveApplications.LeaveTypesID = leave_types.id
    INNER JOIN LeaveRequestGroups ON leave_types.GroupID = LeaveRequestGroups.id
    WHERE      (LeaveApplications.ID = $intApplicationID)";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
  }

  function InsertLeaveAmount($ApproveApplicationIds,$jsonAmounts,$jsonPDLLeaves) {
    $currentUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    $NetLoginId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $UserFullName  = isset($_SESSION['user']['FullName']) && !empty($_SESSION['user']['FullName']) ?  $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
    $pdo = OpenDBLinkA7();
    $ApproveApplicationIds = trim($ApproveApplicationIds);
      try{
		$convert=$_SESSION['convert'] ?? NULL;
        $strQuery = "exec [dbo].[usp_CreateUpdate_LeaveManagePopup] ?,?,?,?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
		$stmt->bindParam(1, $ApproveApplicationIds, PDO::PARAM_STR);
		$stmt->bindParam(2, $jsonAmounts, PDO::PARAM_STR);
		$stmt->bindParam(3, $currentUserID, PDO::PARAM_INT);
		$stmt->bindParam(4, $NetLoginId, PDO::PARAM_STR);
		$stmt->bindParam(5, $UserFullName, PDO::PARAM_STR);
		$stmt->bindParam(6, $jsonPDLLeaves, PDO::PARAM_STR);
		$stmt->bindParam(7, $convert, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        echo $e->getMessage();
    }
		unset($_SESSION['convert']);
  }

  /**
  * Unapprove the leave applied
  * @param  $ApplicationID contains LeaveApplicationId
  * @param  $history contains History
  * @param  $currentuserid contains userId
  */
  function UnapproveLeave($ApplicationID,$history,$currentuserid,$username,$LeaveStartTime,$LeaveEndTime) {

    $pdo = OpenDBLinkA7();

    try{
      if($LeaveStartTime != 'No' && $LeaveEndTime != 'No'){
        $strQuery = "exec [dbo].[usp_UnapproveLeave] ?,?,?,?,?,?";
      } else {
        $strQuery = "exec [dbo].[usp_UnapproveLeave] ?,?,?,?";
      }

      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $ApplicationID, PDO::PARAM_INT);
      $stmt->bindParam(2, $history, PDO::PARAM_STR);
      $stmt->bindParam(3, $currentuserid, PDO::PARAM_INT);
      $stmt->bindParam(4, $username, PDO::PARAM_STR);
      if($LeaveStartTime != 'No' && $LeaveEndTime != 'No'){
        $stmt->bindParam(5, $LeaveStartTime, PDO::PARAM_INT);
        $stmt->bindParam(6, $LeaveEndTime, PDO::PARAM_INT);
      }
      $stmt->execute();
  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }
  }

  /*
  * @Description :Leave Type Details
	* @access : Public
	* @global : Not Applicable
	* @param  :$leavetype
	* @return :array result
  */
  function GetLeaveTypeDetails($leavetype) {

    $pdo = OpenDBLinkA7();
    $query = "SELECT       LeaveRequestGroups.Description + N' - ' + leave_types.description AS GroupAndType, leave_types.id
    FROM         leave_types
    INNER JOIN   LeaveRequestGroups ON leave_types.GroupID = LeaveRequestGroups.id
    WHERE        (leave_types.id = $leavetype)";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
  }

  /*
  * @Description :Update the leave type
	* @access : Public
	* @global : Not Applicable
	* @param  :$leavetype
	* @return :array result
  */
  function modLeaveTypeDetails($leavetype,$intApplicationID,$history,$currentuserid) {

    $pdo = OpenDBLinkA7();
    $modulename ='LeaveApplication';
    try{
      $strQuery = "exec [dbo].[usp_mod_LeaveTypeLeaveApplications] ?,?,?,?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $leavetype, PDO::PARAM_INT);
      $stmt->bindParam(2, $intApplicationID, PDO::PARAM_INT);
      $stmt->bindParam(3, $history, PDO::PARAM_STR);
      $stmt->bindParam(4, $modulename, PDO::PARAM_STR);
      $stmt->bindParam(5, $currentuserid, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }

  }

  /*
  * @Description :LeaveApplication Details
	* @access : Public
	* @global : Not Applicable
	* @param  :$intApplicationID
	* @return :array result
  */
  function getLeaveApplicationDetails($intApplicationID) {

    $pdo = OpenDBLinkA7();
      try{
        $strQuery = "exec [dbo].[usp_get_LeaveApplicationsDetails] ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intApplicationID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
      } catch (PDOException $e) {
          logger()->critical('db error', (array) $e);
          echo $e->getMessage();
      }
  }

  /*
  * @Description :Update Unlikely Leave Applications
	* @access : Public
	* @global : Not Applicable
	* @param  :$id,$setunlikely,$history
	* @return :array result
  */
  function ModUnlikelyLeaveApplication($intApplicationID,$setunlikely,$history,$currentuserid) {

    $pdo = OpenDBLinkA7();
    $modulename ='LeaveApplication';
    try{
      $strQuery = "exec [dbo].[usp_mod_LeaveApplicationsUnlikely] ?,?,?,?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $setunlikely, PDO::PARAM_INT);
      $stmt->bindParam(2, $intApplicationID, PDO::PARAM_INT);
      $stmt->bindParam(3, $history, PDO::PARAM_STR);
      $stmt->bindParam(4, $modulename, PDO::PARAM_STR);
      $stmt->bindParam(5, $currentuserid, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }

  }

  /*
  * @Description :get User Home team Duty Duration
	* @access : Public
	* @global : Not Applicable
	* @param  :$struser
	* @return :array result
  */
  function GetUserHometeamDutyDurtaion($scheduledpersonID) {
    $pdo = OpenDBLinkA7();
    try{
        $strQuery = "exec [dbo].[usp_get_HomeTeamDetailsByScheduler] ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $scheduledpersonID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        echo $e->getMessage();
    }
  }
  /*
  * @Description :send email set 1
	* @access : Public
	* @global : Not Applicable
	* @param  :$intApplicationID
	* @return :array result
  */

  function ModEmailsToSend($strUser = null) {
    $pdo = OpenDBLinkA7();
    $currentUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    try{
        $strQuery = "exec [dbo].[usp_mod_EmailsToSend] ?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
        $stmt->bindParam(2, $currentUserID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        echo $e->getMessage();
    }
  }

/*
* @Description : Get leave allocation by ID
* @access : Public
* @global : Not Applicable
* @param  : $leaveallocationid
* @return : Array
*/
function GetLeaveAllocationByID($leaveallocationid) {

  try {
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "SELECT la.StaffNumber, la.iYear, la.Annual, la.PHL, la.TOIL, la.Comp, la.Additional, la.Exceptional, la.dDate, la.Comments, la.History, la.ID, la.Under11TOIL, la.LongService, la.Casual, la.Other, la.Over12TOIL, UD_DisplayName AS FullName
           FROM LeaveAllocation la (nolock)
          INNER JOIN userDetails (nolock) on UD_UserID = la.SchedulingPersonID
          WHERE la.ID = ?
		    AND la.IsActive=1";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $leaveallocationid, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result;
} catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
}
}

/*
  * @Description : Inssert/Update leave allocation
	* @access : Public
	* @global : Not Applicable
	* @param  : $intGroupID,$strStaffLogin,$userFullName,$modulename,$currentuserid
	* @return : boolean
  */
  function ModLeaveAllocation($intID,$staffnumber,$intLeaveYear,$intAmount,$strFieldName,$strComments,$intTeamID,$strOrigFieldName,$intTypeID,$intOrigTypeID,$strHistory,$ScheduledPersonID,$currentUserID) {
    try {
      // Open the database
      $pdo = OpenDBLinkA7();
      $historytype = 14;
      $strDateNow = date("Y-m-d H:i:s");
	   $strComments = str_replace("'", "''", $strComments);
      if ($intID == 0) {
        $strQuery = "INSERT INTO  LeaveAllocation(StaffNumber, iYear, $strFieldName, dDate, Comments, SchedulingTeamid, WebCredit,SchedulingPersonID,CreatedDate,CreatedBy,UpdateDate,UpdatedBy)
                     VALUES        ('$staffnumber',$intLeaveYear,  $intAmount,  '$strDateNow', '$strComments', $intTeamID, 1,$ScheduledPersonID,GETUTCDATE(),
											$currentUserID,GETUTCDATE(),$currentUserID)";
      }
      else {
            if ($intTypeID == $intOrigTypeID) {
              $strQuery = "UPDATE       LeaveAllocation
                          SET          $strFieldName = $intAmount,
                                        Comments = '$strComments',
                                        UpdateDate = GETUTCDATE(),
										                    UpdatedBy = $currentUserID
                          WHERE        ID = $intID";
            }
            else {
              $strQuery = "UPDATE       LeaveAllocation
                          SET          $strFieldName = $intAmount,
                                        $strOrigFieldName = Null,
                                        Comments = '$strComments',
                                        UpdateDate = GETUTCDATE(),
										                    UpdatedBy = $currentUserID
                          WHERE        ID = $intID";
            }
      }

      $stmt = $pdo->prepare($strQuery);
      $stmt->execute();

      if($intID==0){
        $strQueryLastId = "SELECT TOP 1 ID FROM LeaveAllocation (NOLOCK) ORDER BY ID DESC";
        $stmtLastId = $pdo->prepare($strQueryLastId);
        $stmtLastId->execute();
        $resLastId = $stmtLastId->fetch(PDO::FETCH_ASSOC);
        $intID = $resLastId['ID'];
      }
      if($intID > 0){
        InsertHistory($historytype,$currentUserID,$strHistory,$strDateNow,$intID);
      }

    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }
  }

  /*
  * @Description : Delete leave allocation by ID
	* @access : Public
	* @global : Not Applicable
	* @param  : $leaveallocationid
	* @return : boolean
  */
function DelLeaveAllocationByID($leaveallocationid) {

  try {
    $sessUserId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
    // Open the database
    $pdo = OpenDBLinkA7();
    // Set the statement to use
    $sql = "exec [dbo].[usp_del_LeaveAllocationsByID] ?,?,?";
    $stmt = $pdo->prepare($sql);
    // The parameters
    $stmt->bindParam(1, $leaveallocationid, PDO::PARAM_INT);
    $stmt->bindParam(2, $sessUserId, PDO::PARAM_INT);
    $stmt->bindParam(3, $_SESSION['user']["FullName"], PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result;
} catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
}
}
  /**
   * Description :GetLeaveBy Selected User Netlogin ID
   * @param :$strUser str,
   * @param :$scheduledPersonID str selected User,
   * @param :$adminUser str,
   * @param :$dteStartDate str,
   * @param :$dteEndDate str,
   * @param :$checked_approved str,
   * @param :$checked_pending str,
   * @param :$checked_deleted str,
   * @param :$checked_agreed str
   * return []
   */
  function GetYealyLeaveInformationByNetLoginId($scheduledPersonID,$adminUser,$dteStartDate,$dteEndDate,$checked_approved,$checked_pending,$checked_deleted,$checked_agreed) {
    $rsLeave=[];
    $arrLeave=[];
     try {
        $pdo = OpenDBLinkA7();
        // Set the statement to use
        $sql = "exec [dbo].[usp_Yearly_Leave_Info_ByScheduledPersonID] :dteStartDate,:dteEndDate,:scheduledPersonID,:adminUser,:checked_approved,:checked_pending,:checked_deleted,:checked_agreed";
        $stmt = $pdo->prepare($sql);
        // The parameters
        $stmt->bindParam(":dteStartDate", $dteStartDate, PDO::PARAM_STR);
        $stmt->bindParam(":dteEndDate", $dteEndDate, PDO::PARAM_STR);
        $stmt->bindParam(":scheduledPersonID", $scheduledPersonID, PDO::PARAM_STR);
        $stmt->bindParam(":adminUser", $adminUser, PDO::PARAM_STR);
        $stmt->bindParam(":checked_approved", $checked_approved, PDO::PARAM_STR);
        $stmt->bindParam(":checked_pending", $checked_pending, PDO::PARAM_STR);
        $stmt->bindParam(":checked_deleted", $checked_deleted, PDO::PARAM_STR);
        $stmt->bindParam(":checked_agreed", $checked_agreed, PDO::PARAM_STR);
        $stmt->execute();
        $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
  }
  if (!empty($rsLeave)) {
    foreach($rsLeave as $row ) {
      $strDate = date('Y-m-d',strtotime($row['dDate']));
      $intGroupID = $row['GroupID'];
      $intTypeID  = $row['TypeID'];
      $intRequestID = $row['ID'];
      $strCreatedDate = date('jS M Y H:i',strtotime($row['Created']));
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['GroupID'] = $intGroupID;
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['GroupDescription'] = $row['GroupDescription'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['TypeID'] = $intTypeID;
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['TypeDescription'] = $row['TypeDescription'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['Login'] = $row['Login'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['FullName'] = $row['FullName'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['IsOK'] = $row['isOK'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['Approved'] = $row['Approved'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['Attention'] = $row['Attention'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['Unlikely'] = $row['unlikely'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['OverSummer'] = $row['oversummer'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['Created'] = $strCreatedDate;
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['OfficeComments'] = $row['OfficeComments']; ;
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['UserComments'] = $row['Comments'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['Deleted'] = $row['Deleted'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['History'] = $row['History'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['ID'] = $row['ID'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['ShortNotice'] = $row['ShortNotice'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['CountLeave'] = $row['CountLeave'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['IsPartDayLeaveApplied'] = $row['IsPartDayLeaveApplied'];
	    $arrLeave['LeaveRequests'][$strDate][$intRequestID]['LeaveStartTime'] = $row['LeaveStartTime'];
	    $arrLeave['LeaveRequests'][$strDate][$intRequestID]['LeaveEndTime'] = $row['LeaveEndTime'];
      $arrLeave['LeaveRequests'][$strDate][$intRequestID]['IsAgreed'] = $row['IsAgreed'];
	  $arrLeave['LeaveRequests'][$strDate][$intRequestID]['AllocationID'] = $row['ASP_AllocationsSPID'];
    }
  }
   return $arrLeave;
  }

  /**
   * function : CheckDateIsavaialable Check for Sicknes | Charging |  OverTime| Duplicate
   * @param :$date string
   * @param :$SchedulledpersonID int
   * return [];
   */

  function CheckDateIsavaialable($date,$SchedulledpersonID)
  {
    $duplicate_chk  = chkDuplicateLeaveRequest($date,$SchedulledpersonID);
    $sickness_chk   = checkforSickness($date,$SchedulledpersonID);
    $overTime_chk   = checkOverTime($date,$SchedulledpersonID);
    $charging_chk   = checkChargingonAllocation($date,$SchedulledpersonID);
    return array('sickness'=>$sickness_chk,'overTime'=>$overTime_chk,'charging'=>$charging_chk,'Duplicate'=>$duplicate_chk);
  }

  /**
   * Decription : checking for Sickeness available on Selected Date For schedulled Person
   * @param : $optLdate string,
   * @param : $SchedulledpersonID int
   * return int
   */

  function checkforSickness($optLdate,$SchedulledpersonID)
  {
   $pdo = OpenDBLinkA7();
     try{
         $res['tot']=0;
         $strQuery = "SELECT count(SchedulingPersonID) as tot 
            FROM LeaveApplications (NOLOCK)  
          WHERE SchedulingPersonID=:SchedulledpersonID
            AND dDate = convert(date,:opteddate,102) 
            AND Deleted = 0
            AND ISNULL(LeaveTypeID,0) IN (3,4,5)";
         $stmt = $pdo->prepare($strQuery);
         $stmt->bindParam(':SchedulledpersonID', $SchedulledpersonID, PDO::PARAM_INT);
         $stmt->bindParam(':opteddate',$optLdate, PDO::PARAM_STR);
         $stmt->execute();
         $res = $stmt->fetch(PDO::FETCH_ASSOC);
         return $res ? $res['tot']: 0;
     } catch (PDOException $e) {
         logger()->critical('db error', (array) $e);
         echo $e->getMessage();
     }
  }

   /**
   * Decription : checkOverTime available on Selected Date For schedulled Person
   * @param : $optLdate string,
   * @param : $SchedulledpersonID int
   * return int
   */

   function checkOverTime($optLdate,$SchedulledpersonID)
   {
    $res['tot'] = 0;
    $pdo = OpenDBLinkA7();
      try{
          $strQuery = "SELECT count(ASP_SchedulingPersonID) as tot
          FROM AllocationsScheduledPersons as ASP (NOLOCK)
          Inner Join ScheduledPersonTeam_LINK as ST (NOLOCK)
          on ASP.ASP_SchedulingPersonID = ST.ScheduledPersonID
          WHERE (ST.IsActive = 1 AND ASP_SchedulingPersonID=:SchedulledpersonID
          AND ASP_DutyDate = convert(date,:opteddate,102)
          AND ASP_MarkedOverTime=1)";
          $stmt = $pdo->prepare($strQuery);
          $stmt->bindParam(':SchedulledpersonID', $SchedulledpersonID, PDO::PARAM_INT);
          $stmt->bindParam(':opteddate',$optLdate, PDO::PARAM_STR);
          $stmt->execute();
          $res = $stmt->fetch(PDO::FETCH_ASSOC);
           return $res ? $res['tot']: 0;
      } catch (PDOException $e) {
          logger()->critical('db error', (array) $e);
          echo $e->getMessage();
      }
      return 0;
   }

   /**
   * Decription : checkChargingonAllocation available on Allocation
   * @param : $optLdate string,
   * @param : $SchedulledpersonID int
   * return int
   */

   function checkChargingonAllocation($optLdate,$SchedulledpersonID)
   {
     $pdo = OpenDBLinkA7();
      try{
          $strQuery = "select count(ChargingId) as ctot from AllocationsScheduledPersons (nolock) A
          INNER JOIN ChargingDutyMapping_Link (nolock) C
          ON C.AllocationId=ASP_AllocationsSPID where ASP_DutyDate = convert(date,:opteddate,102)
          and ASP_SchedulingPersonID=:SchedulledpersonID";
          $stmt = $pdo->prepare($strQuery);
          $stmt->bindParam(':SchedulledpersonID', $SchedulledpersonID, PDO::PARAM_INT);
          $stmt->bindParam(':opteddate',$optLdate, PDO::PARAM_STR);
          $stmt->execute();
          $res = $stmt->fetch(PDO::FETCH_ASSOC);
          return $res ? $res['ctot']: 0;
      } catch (PDOException $e) {
          logger()->critical('db error', (array) $e);
          echo $e->getMessage();
      }
      return 0;
   }

   /**
   * Decription : chkDuplicateLeaveRequest available on Allocation
   * @param : $optLdate string,
   * @param : $SchedulledpersonID int
   * return int
   */

   function chkDuplicateLeaveRequest($optLdate,$SchedulledpersonID)
   {
   $res['tot'] = 0;
    $pdo = OpenDBLinkA7();
      try {
          $strQuery = "SELECT count(dDate) as tot From LeaveApplications WHERE SchedulingPersonID=:SchedulledpersonID AND Deleted=0 AND dDate=convert(date,:opteddate,102) AND ISNULL(LeaveTypeID,0) NOT IN (3,4,5)";
          $stmt = $pdo->prepare($strQuery);
          $stmt->bindParam(':SchedulledpersonID', $SchedulledpersonID, PDO::PARAM_INT);
          $stmt->bindParam(':opteddate',$optLdate, PDO::PARAM_STR);
          $stmt->execute();
          $res = $stmt->fetch(PDO::FETCH_ASSOC);
          return  $res ? $res['tot'] : 0;
      } catch (PDOException $e) {
          logger()->critical('db error', (array) $e);
          return $e->getMessage();
      }
      return 0;
   }

   /*
  * @Description : leave details of all types
	* @access : Public
	* @global : Not Applicable
	* @param  : $applicationID
	* @return :result
  */
function GetAllLeaveTypesDetailsForApprove($userLogin,$dDate){

  $pdo = OpenDBLinkA7();
  $sql = "exec [dbo].[usp_get_LeaveApplicationsDetailsForApprove] ?,?";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1, $userLogin, PDO::PARAM_STR);
  $stmt->bindParam(2, $dDate, PDO::PARAM_STR);

  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  return $result;
}

  function InsertHistory($historytype,$currentUserID,$strHistory,$strDateNow,$intID) {

    $pdo = OpenDBLinkA7();
    try{
         //insert the history
      $strQuery = "INSERT INTO [dbo].[History]([HistoryType],[UserID],[History],[datetime],[AttributeID])
      VALUES ($historytype,$currentUserID,'$strHistory','$strDateNow',$intID)";
      $stmt1 = $pdo->prepare($strQuery);
      $stmt1->execute();

    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        echo $e->getMessage();
    }
  }

   /*
  * @Description : leave application amount of all types
	* @access : Public
	* @global : Not Applicable
	* @param  : $applicationID
	* @return :result
  */

  function GetApplicationAmounts($applicationID){

    $pdo = OpenDBLinkA7();
    $sql = "select Amount,ReasonID,LeaveTypeID from ref_LeaveApplications_Amounts (nolock) where ApplicationID = $applicationID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }

  function GetLeaveAdminGroups($netlogin){
    $pdo = OpenDBLinkA7();
    $sql = " select LeaveGroupID,Admin from Staff_Web_Config_LeaveGroups_Link where Login='$netlogin' and Admin>0 AND IsActive=1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }

 /**
   * Description : readYearlyFilter
   * return stringh
   */
  function readYearlyFilter() {
    $NetLoginId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
    $fres='';
    $pdo = OpenDBLinkA7();
    try {
      $strQuery = "SELECT DataInput,DataDefault,UsedOnce FROM yearlyStaffFilter nolock WHERE SearchBy=?";
      $stmt =  $pdo->prepare($strQuery);
      $stmt->bindValue(1, $NetLoginId, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if(!empty($result)){
          if(isset($result['UsedOnce']) && $result['UsedOnce']!= 0) {
              $fres = $result['DataInput'];
            }else{
              $fres =  $result['DataDefault'];
            }
      }
        return $fres;

      } catch (PDOException $e) {
          logger()->critical('DB error', (array)$e);
      }
    }

  function weeklyLeaveAllocationsAndRota($intStartWeekNumber,$strUser,$startDate,$EndDate)
    {
      $pdo = OpenDBLinkA7();
    try {
        $intEndWeekNumber = $intStartWeekNumber;
         $strQuery = "exec [dbo].[usp_fetch_leaveweeklyAllocationAndRota] ?,?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intStartWeekNumber, PDO::PARAM_STR);
        $stmt->bindParam(2, $intEndWeekNumber, PDO::PARAM_STR);
        $stmt->bindParam(3, $startDate, PDO::PARAM_STR);
        $stmt->bindParam(4, $EndDate, PDO::PARAM_STR);
        $stmt->bindParam(5, $strUser, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          logger()->critical('DB error', (array)$e);
      }

    if(!empty($result)){
        $arrAllocations=[];
        foreach ($result as $row) {
          $strCurrentDay = $row["DOTW"];
          $strlogin = strtolower($row["NetLogin"]);
          $arrAllocations[$strlogin][$strCurrentDay] =  $row["DutyName"];
        }
    } else {
       $arrAllocations = [];
    }
    return $arrAllocations;
}

function weeklyLeaveAdminAllocationsAndRota($intStartWeekNumber,$strUser,$startDate,$EndDate)
  {
      $pdo = OpenDBLinkA7();
    try {
         $strQuery = "exec [dbo].[usp_fetch_leaveweeklyAdminAllocationAndRota] ?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $intStartWeekNumber, PDO::PARAM_STR);
        $stmt->bindParam(2, $startDate, PDO::PARAM_STR);
        $stmt->bindParam(3, $EndDate, PDO::PARAM_STR);
        $stmt->bindParam(4, $strUser, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          logger()->critical('DB error', (array)$e);
      }

    if(!empty($result)){
        $arrAllocations=[];
        foreach ($result as $row) {
          $strCurrentWeek = $row["WeekNumber"];
          $strCurrentDay = $row["DOTW"];
          $strlogin = strtolower($row["NetLogin"]);
          $arrAllocations[$strlogin][$strCurrentDay] =  $row["DutyName"];
        }
    } else {
       $arrAllocations = [];
    }
    return $arrAllocations;
  }

  /**
   * Description : get user office and reasons for leave
   * return string
   */
  function GetLeaveCommentReason($leaveID) {

    $officecomments = $resasons = $comments = $usercomments = '';
    $pdo = OpenDBLinkA7();
    try {
      $strQuery = "exec usp_Get_LeaveCommentReason ?";
      $stmt =  $pdo->prepare($strQuery);
      $stmt->bindValue(1, $leaveID, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
      $officecommentarr =array_filter(array_column($result, 'OfficeComments'));
      $reasonnamearr = array_unique(array_filter(array_column($result, 'ReasonName')));
      $usercommentsarr =array_filter(array_column($result, 'UserComments'));
      if(!empty($result)) {
          if(!empty($officecommentarr)){
            $officecomments = implode("; ",$officecommentarr);
            $comments .=  "<b>Office:</b> ".$officecomments ."<br>";
          }
          if(!empty($usercommentsarr)){
            $usercomments = implode("; ",$usercommentsarr);
            $comments .=  "<b>User:</b> ".$usercomments ."<br>";
          }
          if(!empty($reasonnamearr)){
            $reasonName = implode("; ",$reasonnamearr);
            $comments .=  "<b>Reasons:</b> ". $reasonName;
          }
        }
        return $comments;

      } catch (PDOException $e) {
          logger()->critical('DB error', (array)$e);
      }
  }

  /* @Description :Get Approved Leave Email To Send User
	* @access : Public
	* @global : Not Applicable
	* @param  :$strUser
	* @return :array result
  */

  function GetApprovedLeavsEmailToSendUser($strUser = null) {

    $pdo = OpenDBLinkA7();
    try{
        $strQuery = "exec [dbo].[usp_Get_UserAprrovedLeavsEmailToSend] ?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
        $stmt->execute();
        $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rsLeave;
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }
  }

  /* @Description :Get Leave Taken Record By User
	* @access : Public
	* @global : Not Applicable
	* @param  :$strUser
	* @return :array result
  */

  function GetLeaveTakenByUser($strScheduledPeopleId,$intLeaveYear) {
    $startingdate = $intLeaveYear.'-04-01 00:00:00';
    $nextyear = $intLeaveYear+1;
    $enddate = $nextyear.'-03-31 00:00:00';

    $pdo = OpenDBLinkA7();
    try{
        $strQuery = "exec [dbo].[usp_get_LeaveTakenByPerson] ?,?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $strScheduledPeopleId, PDO::PARAM_STR);
        $stmt->bindParam(2, $startingdate, PDO::PARAM_STR);
        $stmt->bindParam(3, $enddate, PDO::PARAM_STR);
        $stmt->execute();
        $rsLeave = $stmt->fetch(PDO::FETCH_ASSOC);

        return $rsLeave;
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
    }
  }

  function GetLeaveTakenIndivdualReport ($scheduledPersonID, $intLeaveYear,$showcomment =0) {
    $pdo = OpenDBLinkA7();
    $arrAllocLeaveTypes = GetLeaveAllocateTypes();
    $rsLeaveApplicationsDates = '';
    $startingdate = $intLeaveYear.'-04-01 00:00:00';
    $nextyear = $intLeaveYear+1;
    $enddate = $nextyear.'-03-31 00:00:00';
    $strQuery = "select ID,CONVERT(DATE,dDate,108) AS ApplicationDate from LeaveApplications (NOLOCK) where dDate>=CONVERT(DATETIME,'$startingdate',102)
                  AND dDate <=CONVERT(DATETIME,'$enddate',102) AND SchedulingPersonID =$scheduledPersonID and Deleted = 0 and Approved = 1 and LeaveTypeID in (1,2,6)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $rsLeaveApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rsLeaveApplicationsDates = array_column($rsLeaveApplications,'ApplicationDate');
    $arrRanges = '';
    if(!empty($rsLeaveApplicationsDates)){
        $arrRanges = getDateRangeSplitted($rsLeaveApplicationsDates);
    }
    foreach ($arrAllocLeaveTypes as $intTypeID => $arrAllocLeaveType) {
      $arrLeave['Types'][$intTypeID] = 0;

    }
    $intCounter = 1;
    if(is_array($arrRanges)){
    foreach ($arrRanges as $arrRange) {

    $sql = "exec [dbo].[usp_get_LeaveTakenByPerson] ?,?,?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $scheduledPersonID, PDO::PARAM_INT);
    $stmt->bindParam(2, $arrRange[0], PDO::PARAM_STR);
    $stmt->bindParam(3, $arrRange[1], PDO::PARAM_STR);
    $stmt->execute();
    $rsLeaveTaken[$arrRange[0].'_'.$arrRange[1]] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    foreach($rsLeaveTaken as $key => $row ){
      $arrDateLeave = explode('_', $key);
      $comments = '';
      //fetch the office comment and reasonslist
      if($showcomment == 1){
        $comments = GetLeaveApplicationCommentReason($arrDateLeave[0],$arrDateLeave[1],$scheduledPersonID);
      }
      $arrLeave['Leave'][$intCounter]['StartDate'] = date("Y-m-d",strtotime($arrDateLeave[0]));
      $arrLeave['Leave'][$intCounter]['EndDate'] = date("Y-m-d",strtotime($arrDateLeave[1]));
      foreach ($arrAllocLeaveTypes as $intTypeID => $arrAllocLeaveType) {
        if (isset($row[$arrAllocLeaveType['AllocName']]) && $row[$arrAllocLeaveType['AllocName']]!= 0) {
          $arrLeave['Leave'][$intCounter][$intTypeID]['Amount'] = number_format((float)($row[$arrAllocLeaveType['AllocName']]),2, '.', '');
          $arrLeave['Types'][$intTypeID] = 1;
        }
      }

      $arrLeave['Leave'][$intCounter]['Comments'] = $comments;
      $arrLeave['Leave'][$intCounter]['Status'] = isset($row['Status']) ? $row['Status'] : '';
      $intCounter++;
    }
  }

    if (isset($arrLeave)) {
      return ($arrLeave);
    }
  }

   /**
   * Description : get user office and reasons for leave
   * return string
   */
  function GetLeaveApplicationCommentReason($startDate,$endDate,$scheduledPersonID) {

    $officecomments = $resasons = $comments = $usercomments = '';
    $pdo = OpenDBLinkA7();
    try {
      $strQuery = "SELECT distinct la.OfficeComments,
              Name AS ReasonName ,
              la.Comments as UserComments,
              la.LeaveStartTime,
              la.LeaveEndTime,
              la.Approved
              from LeaveApplications  la (nolock)
              INNER JOIN ref_LeaveApplications_Amounts RFL  (nolock) ON RFL.ApplicationID = la.ID
              LEFT JOIN LeaveExceptionalTypes let  (nolock) on let.ID = RFL.ReasonID
              WHERE la.dDate >= CONVERT(DATETIME,?,102) and dDate <= CONVERT(DATETIME,?,102)
              and la.SchedulingPersonID = ?
              and la.Deleted = 0";
      $stmt =  $pdo->prepare($strQuery);
      $stmt->bindValue(1, $startDate, PDO::PARAM_STR);
      $stmt->bindValue(2, $endDate, PDO::PARAM_STR);
      $stmt->bindValue(3, $scheduledPersonID, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
      $officecommentarr =array_filter(array_column($result, 'OfficeComments'));
      $reasonnamearr = array_unique(array_filter(array_column($result, 'ReasonName')));
      $usercommentsarr =array_filter(array_column($result, 'UserComments'));
      $pdlleavestarttime =array_filter(array_column($result, 'LeaveStartTime'));
      $pdlleaveendtime =array_filter(array_column($result, 'LeaveEndTime'));

      if(!empty($result)) {
          if(!empty($officecommentarr)){
            $officecomments = implode("; ",$officecommentarr);
			if($pdlleavestarttime && $pdlleaveendtime !=''){$comments .=  "<b>Office: </b> PDL:  ".$officecomments ."<br>";}
            else{ $comments .=  "<b>Office:</b> ".$officecomments ."<br>";}
          }
          if(!empty($usercommentsarr)){
            $usercomments = implode("; ",$usercommentsarr);
			if($pdlleavestarttime && $pdlleaveendtime !=''){$comments .=  "<b>User: </b> PDL:  ".$usercomments ."<br>";}
            else{ $comments .=  "<b>User:</b> ".$usercomments ."<br>";}
          }
          if(!empty($reasonnamearr)){
            $reasonName = implode("; ",$reasonnamearr);
			if($pdlleavestarttime && $pdlleaveendtime !=''){$comments .=  "<b>Reasons: </b> PDL:  ".$reasonName ."<br>";}
            else{ $comments .=  "<b>Reasons:</b> ". $reasonName;}
          }
        }
        return $comments;

      } catch (PDOException $e) {
          logger()->critical('DB error', (array)$e);
      }
  }

  function UpdateSendOptionInLeaveApplication($LeaveDate, $sent, $sentOption, $Netlogin) {
    $pdo = OpenDBLinkA7();
    $currentUserID = isset($_SESSION['user']['UserID']) && ($_SESSION['user']['UserID'] != '') ? $_SESSION['user']['UserID'] :  $_COOKIE['editWeeklyUserId'];
    $status = 1;
    $LeaveDate = $LeaveDate.' '.'00:00:00';
      try{
        $strQuery = "exec [dbo].[usp_UpdateSent_LeaveApplications] ?,?,?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $LeaveDate, PDO::PARAM_STR);
        $stmt->bindParam(2, $sent, PDO::PARAM_INT);
        $stmt->bindParam(3, $sentOption, PDO::PARAM_STR);
        $stmt->bindParam(4, $currentUserID, PDO::PARAM_INT);
        $stmt->bindParam(5, $Netlogin, PDO::PARAM_STR);
        $stmt->bindParam(6, $status, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result[0]['intstatus'];
      } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        echo $e->getMessage();
      }
  }

  function displayTime($time='',$seperator=':'){
      $explTime = explode($seperator,$time);
      $dtTimeHr = $explTime[0];
      if(($dtTimeHr <= 9) && ($seperator == ':')){
          $dtTimeHr = '0'.$dtTimeHr;
      }
      $dtTimeSec = $explTime[1];
      if(($dtTimeSec <= 9) && (substr_count($dtTimeSec, 0) == 1) && ($seperator == ':')){
          $dtTimeSec = '0'.$dtTimeSec;
      }

      if(($seperator=='.') && ($dtTimeSec == '50')){
          $dtTimeSec = '5';
      }
      return $dtTimeHr.$seperator.$dtTimeSec;
  }

  function PDLHistoryUpdate($attributeid,$historytype,$userid,$message,$status,$historysubtype){
	$pdo = OpenDBLinkA7();
        try{
        $strQuery = "exec [dbo].[usp_mod_AllocationHistory] ?,?,?,?,?,?";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $attributeid, PDO::PARAM_INT);
        $stmt->bindParam(2, $historytype, PDO::PARAM_INT);
        $stmt->bindParam(3, $userid, PDO::PARAM_INT);
        $stmt->bindParam(4, $message, PDO::PARAM_STR);
        $stmt->bindParam(5, $status, PDO::PARAM_INT);
		$stmt->bindParam(6, $historysubtype, PDO::PARAM_STR);
        $stmt->execute();
      } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        echo $e->getMessage();
      }
  }
  function WeeksInYear($curYear){
	$pdo = OpenDBLinkA7();
	$sql = "SELECT max(ixWeekInYear) as maxweek FROM TimeDimension WHERE ixYear=" .$curYear ." and ixMonthInYear=12";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result[0]['maxweek'];
  }

 function GetRequestEmailsToSend($strUser) {
	  $pdo = OpenDBLinkA7();
	  try{
        $strQuery = "exec [dbo].[usp_get_RequestEmailsToSend] ?";
		$stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
        $stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $result[0]['CountToSend'];
      } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
        echo $e->getMessage();
      }

  }

function GetLeaveHolidaysByYear($intYear) {
  $pdo = OpenDBLinkA7();
  $query = "SELECT * FROM TimeDimension WHERE ixYear = :intYear and fHolidayFlag = 1";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':intYear', $intYear, PDO::PARAM_INT);
  $stmt->execute();
  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $result;
}

/*
* @Description :Update agreed of leave application by Leave manager
* @access : Public
* @global : Not Applicable
* @param  :$id,$agreed,$history
* @return :array result
*/
function ModApproverAgreedLeaveApplication($intApplicationID,$agreed,$history,$currentuserid) {

    $pdo = OpenDBLinkA7();
    $modulename ='LeaveApplication';
    try{
      $strQuery = "exec [dbo].[usp_mod_LeaveApplicationsApproverApprove] ?,?,?,?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $agreed, PDO::PARAM_INT);
      $stmt->bindParam(2, $intApplicationID, PDO::PARAM_INT);
      $stmt->bindParam(3, $history, PDO::PARAM_STR);
      $stmt->bindParam(4, $modulename, PDO::PARAM_STR);
      $stmt->bindParam(5, $currentuserid, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
      logger()->critical('db error', (array) $e);
      echo $e->getMessage();
  }

  }