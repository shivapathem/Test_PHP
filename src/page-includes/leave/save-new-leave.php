<?php   
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  include_once '../../function-includes/init.php';
  include_once '../../function-includes/genericfunctions.php';
  include_once '../../function-includes/userfunctions.php';
  include_once '../../function-includes/leavefunctions.php';
  include_once '../../function-includes/allocationsfunctions.php';
  include_once __DIR__.'/../../function-includes/common/classCommonDBFunctions.php';
  include_once '../../page-includes/allocations/weekly/service/AllocationService.php';

  $service = new AllocationService();
  $commonDbobj = new classCommonDBFunctions;
  $schedulingPersonId = null;
  if ($_POST['task']=="addLeave") {
  $strUser = $_POST['userNetlogin'];
  $fullname = $_POST['fullname'];
  $userid = isset($_COOKIE['editWeeklyUserId']) ? $_COOKIE['editWeeklyUserId'] : $_SESSION['user']['UserID'];
  $schedulingPersonId = getScheduledPersonIDByNetLoginID($strUser);
  $intLeaveTypeID = $_POST['leavetype'];
  $strstartLeaveDate = date('Y-m-d',strtotime($_POST['startDate'])) ?? date('Y-m-d');
  $bbcweeknumberArray =  $commonDbobj->GetWeekNoAndIDayByDateFromTimeDim($strstartLeaveDate);
  $intWeekNumber = $bbcweeknumberArray['ixYearWeek'];
  $strEndLeaveDate = date('Y-m-d',strtotime($_POST['endDate'])) ?? date('Y-m-d');
  $strRequesterName = isset($_SESSION['user']['FullName'])  && ($_SESSION['user']['FullName'] != '') ? $_SESSION['user']['FullName'] :$_COOKIE['editWeeklyUserFullName'];
  $warningType = $_POST['warning'];
  $pdlStartTime = ((isset($_POST['pdlStartTime'])) && ($_POST['pdlStartTime'] != '') && ($_POST['pdlStartTime'] != 0)) ? $_POST['pdlStartTime'] : NULL;
  $pdlEndTime = ((isset($_POST['pdlEndTime'])) && ($_POST['pdlEndTime'] != '') && ($_POST['pdlEndTime'] != 0)) ? $_POST['pdlEndTime'] : NULL;
  $arrAllLeaveGroups = GetLeaveGroupAndTeamsFromID();
  $arrTypeGroup = GetLeaveTypeAndGroupFromID($intLeaveTypeID);
  $intGroupID = $arrTypeGroup['GroupID'];
  $leaveStartday = $strstartLeaveDate;
  
  $dateDiff = strtotime($strEndLeaveDate) - strtotime($strstartLeaveDate);

  $intLeaveYear = date("Y",strtotime($strstartLeaveDate));
  $strYearStart = $intLeaveYear.'-04-01';
  $strYearEnds = ($intLeaveYear + 1).'-03-31';
  $arrLeaveForYearCount = GetLeaveForUser($strUser, $strYearStart, $strYearEnds,$schedulingPersonId);
    
  $intRemainingRequests = ceil(($arrLeaveForYearCount['UserLeave']['Allocated'] - $arrLeaveForYearCount['UserLeave']['TotalHours']) / $arrLeaveForYearCount['UserLeave']['HoursPerDay']) +  $arrLeaveForYearCount['UserLeave']['ExtraLeaveClicks'];
  $zeroCountLeave = isset($arrLeaveForYearCount['UserLeave']['zeroCountLeave']) ? $arrLeaveForYearCount['UserLeave']['zeroCountLeave'] : 0;
  $intRemainingRequests = $intRemainingRequests + $zeroCountLeave;

  if ($dateDiff < 0) {
    $msg[]= "StartDate and EndDate are wrong.";
    $errorStatus=1;
    $finalArray = array('msg'=>implode(" ",array_values($msg)),'status'=>0);
    echo json_encode($finalArray);
    return;
  }
  if($intRemainingRequests <= 0 && $warningType!='ok')  
    {
      $errorStatus=1;
      $msg= [];
      $msg[] = "The Remaining clicks available for this user are ".$intRemainingRequests." Do you want to authorise negative leave count and continue?";
      $finalArray = array('msg'=>implode(" ",array_values($msg)),'status'=>2);
      echo json_encode($finalArray);
      return; 
    }
  $levaeAdded=1;
  while ($leaveStartday <=  $strEndLeaveDate) { 
      if ($schedulingPersonId!=null) {
        $arrLeave = GetLeaveForUser($strUser, $leaveStartday, $leaveStartday,$schedulingPersonId);
      }    
    $intLeaveStarts = strtotime("+".$arrAllLeaveGroups[$intGroupID]['Types'][$intLeaveTypeID]['LeaveStarts']." days");
    $intAvailable = $arrLeave['Available'][$intLeaveTypeID][$leaveStartday];
    $intOverSummer = 0;
    if (isset($arrLeave['Applications'][$intLeaveTypeID][$leaveStartday])) {
      $intRequestsIn = $arrLeave['Applications'][$intLeaveTypeID][$leaveStartday];
    } else {
      $intRequestsIn = 0;
    }
 
    if ($intAvailable > $intRequestsIn) {
      $intIsOK = 1;
    } else {
      $intIsOK = 0;
    }
    if (strtotime($leaveStartday) <= $intLeaveStarts) {
      $intShortNotice = 1;
    } else {
      $intShortNotice = 0;
    }  

  if (isset($arrLeave['Summer'][$intLeaveTypeID]['Dates'][$leaveStartday])) {
    $intDoSummer = 1;
    // The number of summer requests allowed....
    $intSummerRequestsAllowed = $arrLeave['SummerClicksAllowed'];
    $intSummerCount = $arrLeave['UserLeave']['SummerLeaveClicks'];
  
    if ($intSummerCount >= $intSummerRequestsAllowed) {
      $intOverSummer = 1;  
     } else {
      $intOverSummer = 0;
    }
  } else {
    $intDoSummer = 0;
    $intOverSummer = 0;
  } 

  /**
   * Check for Sickness | Charging | OverTime | Duplicate 
   */
   
    $errorStatus = 0;
    $Vaidation_status = CheckDateIsavaialable($leaveStartday,$schedulingPersonId);
    if($Vaidation_status['sickness']!=0 || $Vaidation_status['overTime']!=0 || $Vaidation_status['charging']!=0) {
    $errorStatus = 1;
    $msg[]="Leave could not be created/modified as it would affect certain Duty Allocation Settings <br> Please fix/remove these and try again.<br><br>";
    }
    $pattern = '/,/';
    $replacement = '-';
    $leaveStartday= preg_replace($pattern, $replacement, $leaveStartday);
   
    if ($Vaidation_status['sickness']!=0)
    {
      $msg[]= date('l, jS M Y',strtotime($leaveStartday))." Marked for Sickness.<br>";
      $errorStatus=1;
    }
 
    if ($Vaidation_status['overTime']!=0)
    {
      $msg[]= date('l, jS M Y',strtotime($leaveStartday))." Marked for OverTime.<br>";
      $errorStatus=1;
    }
    if ($Vaidation_status['charging']!=0)
    {
      $msg[]= date('l, jS M Y',strtotime($leaveStartday))." Marked for charging.<br>";
      $errorStatus=1;
    }
    if ($Vaidation_status['Duplicate']!=0)
    {
      $msg[]="$fullname has been edited by another user Or is already on Leave on one or more of these date(s).<br>Please re-enter or click on cancel.";
      $errorStatus=1;
    } 

  /**
   * Check for Sickness | Charging | OverTime | Duplicate
   * 
   */

  if($_POST['pdlStartTime'] && $_POST['pdlEndTime']!=''){
	  $pdlStartTimeCon = $service->convertSecondsIntoTime($_POST['pdlStartTime'],':','No');
	  $pdlEndTimeCon = $service->convertSecondsIntoTime($_POST['pdlEndTime'],':','No');
	  $strHistory ='Part day of leave requested by '.$strRequesterName.' from '.$pdlStartTimeCon.' to '.$pdlEndTimeCon. ' On '.getDateTimeInEuropeTimezone(1, 0, '').' '.getDateTimeInEuropeTimezone(0, 1, ''). '. <br>';
  }
  else{
  $strHistory = '<br>Leave Type \''.$arrTypeGroup['TypeDescription'].'\' in Leave Group \''.$arrTypeGroup['GroupDescription'].'\' Applied for on '.getDateTimeInEuropeTimezone(1, 0, "jS M Y").' at '.getDateTimeInEuropeTimezone(0, 1, ''). '
               <br>By '.$strRequesterName. '. <br> There were '.$intAvailable.' Spaces available and '.$intRequestsIn.' Requests already in. ';
  }
  if ($intDoSummer == 1){
    $strHistory.= '<br>This is a Summer Leave Request. You have '.$intSummerCount.' Requests Already and '.$intSummerRequestsAllowed.' are allowed. ';
	}
  if ($intShortNotice == 1){
    $strHistory.= '<br>This is a Short Notice Request. ';               
  }
  $strHistory = escapesinglequotes($strHistory);

  if ($intAvailable < $intRequestsIn) {
    $intLeaveOK = 1;
  } else {
    $intLeaveOK = 0;
  }
  if(!empty($pdlStartTime) && !empty($pdlEndTime)){
      $baseDate = DateTime::createFromFormat('Y-m-d', $leaveStartday);
      $startDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $baseDate->format('Y-m-d') . ' 00:00:00');
      $endDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $baseDate->format('Y-m-d') . ' 00:00:00');
      $startDateTime->modify("+{$pdlStartTime} seconds");
      $endDateTime->modify("+{$pdlEndTime} seconds");
      $LeaveStartTimeLocal = $startDateTime->format('Y-m-d H:i');
      $LeaveEndTimeLocal = $endDateTime->format('Y-m-d H:i');
    }else{
      $LeaveStartTimeLocal = NULL;
      $LeaveEndTimeLocal = NULL;
    }
if ($errorStatus==0) {
  try {
    $pdo = OpenDBLinkA7();
    $status = 1;
    $strQuery = "exec [dbo].[usp_mod_LeaveApplications] ?,?,?,?,?,?,?,?,?,?,?,?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $leaveStartday, PDO::PARAM_STR);
    $stmt->bindParam(2, $strUser, PDO::PARAM_STR);
    $stmt->bindParam(3, $intLeaveTypeID, PDO::PARAM_INT);
    $stmt->bindParam(4, $intShortNotice, PDO::PARAM_INT);
    $stmt->bindParam(5, $intOverSummer, PDO::PARAM_INT);
    $stmt->bindParam(6, $intIsOK, PDO::PARAM_INT);
    $stmt->bindParam(7, $strHistory, PDO::PARAM_STR);
    $stmt->bindParam(8, $userid, PDO::PARAM_INT);
    $stmt->bindParam(9, $status, PDO::PARAM_INT);
    $stmt->bindParam(10, $schedulingPersonId, PDO::PARAM_INT);
    $stmt->bindParam(11, $pdlStartTime, PDO::PARAM_INT);
    $stmt->bindParam(12, $pdlEndTime, PDO::PARAM_INT);
    $stmt->bindParam(13, $LeaveStartTimeLocal, PDO::PARAM_STR);
    $stmt->bindParam(14, $LeaveEndTimeLocal, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($levaeAdded==1) {
      $leaveID = $result['intstatus'];

      if((!empty($result)) && isset($result['intstatus']) && (isset($_POST['pdlStartTime'])) && ($_POST['pdlStartTime'] != '') && (isset($_POST['pdlEndTime'])) && ($_POST['pdlEndTime'] != '') && (($_POST['pdlStartTime'] != 0) || ($_POST['pdlEndTime'] != 0))){
        $currentUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
        $NetLoginId = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
        $UserFullName  = isset($_SESSION['user']['FullName']) && !empty($_SESSION['user']['FullName']) ?  $_SESSION['user']['FullName'] : $_COOKIE['editWeeklyUserFullName'];
        $jsonAmounts = '';
        $jsonPDLLeaves = '';
        $arrAllocLeaveTypes = GetLeaveAllocateTypes();

        if(!empty($arrAllocLeaveTypes)){
          $pdlLeaveAmountSplitData = [];
          foreach($arrAllocLeaveTypes as $leaveTypeKey => $leaveTypeVal){
            if(strtoupper($leaveTypeVal['Description']) == 'ANNUAL'){
              $nwPdlEndTime = ($pdlStartTime > $pdlEndTime) ? (86400+$pdlEndTime) : $pdlEndTime;
              $pdlLeaveAmountSplitData[0]["AppId"] = (string) $leaveID;
              $pdlLeaveAmountSplitData[0]["TypeId"] = $leaveTypeVal['ID'];
              $pdlLeaveAmountSplitData[0]["Amount"] = displayTime(number_format((float) (($nwPdlEndTime-$pdlStartTime) / 3600), 2, '.', ''),'.');
              break;
            }
          }
          $jsonAmounts = json_encode($pdlLeaveAmountSplitData);
        }

        if((isset($_POST['pdlStartTime'])) && ($_POST['pdlStartTime'] != '') && (isset($_POST['pdlEndTime'])) && ($_POST['pdlEndTime'] != '') && (($_POST['pdlStartTime'] != 0) || ($_POST['pdlEndTime'] != 0))){
          $pdlData = [];
          $pdlData[0]["LeaveID"] = (string) $leaveID;
          $pdlData[0]["LeaveStartTime"] = $_POST['pdlStartTime'];
          $pdlData[0]["LeaveEndTime"] = $_POST['pdlEndTime'];
          $jsonPDLLeaves = json_encode($pdlData);
        }

        if(($jsonAmounts != '') && ($jsonPDLLeaves != '')){
          $strQueryPDL = "exec [dbo].[usp_CreateUpdate_LeaveManagePopup] ?,?,?,?,?,?";
          $stmtPDL = $pdo->prepare($strQueryPDL);
          $stmtPDL->bindParam(1, $leaveID, PDO::PARAM_STR);
          $stmtPDL->bindParam(2, $jsonAmounts, PDO::PARAM_STR);
          $stmtPDL->bindParam(3, $currentUserID, PDO::PARAM_INT);
          $stmtPDL->bindParam(4, $NetLoginId, PDO::PARAM_STR);
          $stmtPDL->bindParam(5, $UserFullName, PDO::PARAM_STR);
          $stmtPDL->bindParam(6, $jsonPDLLeaves, PDO::PARAM_STR);
          $stmtPDL->execute();
          $result = $stmtPDL->fetch(PDO::FETCH_ASSOC);
        }
      }
    }

  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
    $e->getMessage();
  }
}
  $leaveStartday = date('Y-m-d', strtotime($leaveStartday . " +1 days"));
 $levaeAdded++;

} //Loop closed
   
 if ($errorStatus) {
     $finalArray = array('msg'=>implode(" ",array_values($msg)),'status'=>0);
     echo  json_encode($finalArray);
     return; 
 } else {
    $msg[] = "Leave AddedSuccesfully.";
    $finalArray = array('msg'=>implode(" ",array_values($msg)),'status'=>1,'weekno'=>$intWeekNumber,'LeaveId'=>$leaveID);
    echo json_encode($finalArray);
    return; 
 }
    
 } //if submit


?>
