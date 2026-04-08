<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once 'DBHelper.php';
include_once 'helpers.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/common/classCommonDBFunctions.php';
/*
 * Description : Read Locks and Request information Week wise and Logged User LOGIN
 * @param : int $intStartWeek,
 * @param : int $intEndWeek,
 * @param : int $schedulingPersonId,
 * @param : array $arrTeamDefaults
 * return []
 */
function ReadLocksByScheduledPersonId($intStartWeek, $intEndWeek, $schedulingPersonId) {
  $commonObj = new classCommonDBFunctions();
  $pdo = OpenDBLinkA7();
  $arrLocksOptions =[];
  $arrLocksOptions = GetLocksOptionsByUser($schedulingPersonId);
  if (!empty($arrLocksOptions) && ($arrLocksOptions['LocksStart'] == -1  || is_null($arrLocksOptions['Locks']) || $arrLocksOptions['Locks']==0)) {
    $intLocksStart = 0; 
    $intLocksEnd = -1;     
    $intRollWeeks = 0;  
  } else {
    $intLocksStart = $arrLocksOptions['LocksStart']; 
    $intLocksEnd = $arrLocksOptions['LocksEnd'];     
    $intRollWeeks = $arrLocksOptions['LocksRollWeeks'];
  }
  $dteLocksStartDate = date("Y-m-d", strtotime("+$intLocksStart Days")); 
  $dteLocksEndDate = date("Y-m-d", strtotime("+$intLocksEnd Days"));
  if ($intRollWeeks == 1) {
    $dteLocksEndDate = date("Y-m-d", strtotime("+6 Days", strtotime(datefromweek(bbcweeknumber($dteLocksEndDate)))));
  }
  $arrRequestWeeks['LocksStart'] = $dteLocksStartDate;
  $arrRequestWeeks['LocksEnd'] = $dteLocksEndDate;
  // Get the weeks we are looking at..... 
  $intLoopWeek = $intStartWeek;
  while ($intLoopWeek <= $intEndWeek) {
    $arrRequestWeeks['Weeks'][$intLoopWeek]['HasRequest'] = 0;  
    $intLoopWeek = addweeks($intLoopWeek, 1);
  }
  $rsRequests =[];
  // Get the start and end dates for requests
  reset($arrRequestWeeks['Weeks']);
  $strRequestsStarWeek =  key($arrRequestWeeks['Weeks']);
  end($arrRequestWeeks['Weeks']);
  $strRequestsEndWeek = key($arrRequestWeeks['Weeks']);
  // We need to get requests and Locks.....
  $bbcweeknumberArray =  $commonObj->GetWeekStartDateByWeekNoFromTimeDim($strRequestsStarWeek,'ByweeknoOnly',0);
  $bbcEndweeknumberArray =  $commonObj->GetWeekStartDateByWeekNoFromTimeDim($strRequestsEndWeek,'ByweeknoOnly',0);
  $strRequestsStartDate = $bbcweeknumberArray['dDateTime'];
  $strDateOfEndWeek = $bbcEndweeknumberArray['dDateTime'];
  $strRequestsEndDate = date("Y-m-d", strtotime("+6 Days",strtotime($strDateOfEndWeek)));

  try { 
    $strQuery = "exec [dbo].[usp_GetRequestsaffectbylockByDates] ?,?,?"; 
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $schedulingPersonId, PDO::PARAM_INT);
    $stmt->bindParam(2, $strRequestsStartDate,PDO::PARAM_STR);
    $stmt->bindParam(3, $strRequestsEndDate,PDO::PARAM_STR);
    $stmt->execute();  
    $rsRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
  
  if (!empty($rsRequests)) {
    foreach ($rsRequests as $row) { 
      $strCurrDate = date('Y-m-d',strtotime($row['dDate']));
      $intApproved = $row['Approved'] + 1;
      $bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim($strCurrDate);
      $intRequestWeek = $bbcweeknumberArray['ixYearWeek'];
      if ($intApproved > $arrRequestWeeks['Weeks'][$intRequestWeek]['HasRequest']) {
        $arrRequestWeeks['Weeks'][$intRequestWeek]['HasRequest'] = $intApproved ;
      }
    }
  }
  $rsLocksRestricted=[];
// get any weeks where the locks are denied........
  try {
    $strQuery = "SELECT WeekNumber FROM LockRequestsRestricted WHERE (ScheduledPersonID = '$schedulingPersonId') 
                AND (WeekNumber BETWEEN $intStartWeek AND $intEndWeek)
                AND (Deleted = 0)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $rsLocksRestricted = $stmt->fetchAll(PDO::FETCH_ASSOC);  
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
  if (!empty($rsLocksRestricted)) {
    foreach($rsLocksRestricted as $row) { 
      $intLockWeek = $row['WeekNumber'];
      $arrRequestWeeks['Weeks'][$intLockWeek]['HasRequest'] = 3;
    } 
  }
  $rsLocks=[];
  // Finally get any Locks....
  try {
    $strQuery = "SELECT  WeekNumber, iDay, isnull(AllocationsInformation, '') as AllocationsInfo FROM LockRequests (Nolock) WHERE (ScheduledPersonID = '$schedulingPersonId') 
                AND (WeekNumber BETWEEN $intStartWeek AND $intEndWeek)
                AND (deleted = 0)"; 
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $rsLocks = $stmt->fetchAll(PDO::FETCH_ASSOC); 
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
  if (!empty($rsLocks)) {
    foreach ($rsLocks as $row) { 
      $intLockWeek = $row['WeekNumber'];
      $arrRequestWeeks['Weeks'][$intLockWeek]['HasRequest'] = 1;
      $arrRequestWeeks['Weeks'][$intLockWeek]['Locks'][$row['iDay']] = $row['AllocationsInfo'];
    } 
  } 
  return $arrRequestWeeks;
}

/**
 * Description : get Mylock information By NetLogin onMy request and locks
 * @param : $sheduledPersonId int ,
 * @param : $strStartDate str,
 * @param : $strEndDate str
 * retrun []
 */

function ReadLocksForUser($sheduledPersonId, $strStartDate, $strEndDate ) { 
  $getStartWeekandDayArr = GetAllocationWeekandDay($strStartDate);
  $intStartWeek = $getStartWeekandDayArr['ixYearWeek'] ?? 0;
  $getEndWeekandDayArr = GetAllocationWeekandDay($strEndDate);
  $intEndWeek = $getEndWeekandDayArr['ixYearWeek'] ?? 0;
  $arrLocks = [];
  $arrLocksOptions = GetLocksOptionsByUser($sheduledPersonId); //As per home Team 
  if (($arrLocksOptions['LocksStart'] == -1) || is_null($arrLocksOptions['Locks']) || ($arrLocksOptions['Locks']==0)) {
    $intLocksStart = 0; 
    $intLocksEnd = -1;     
    $intRollWeeks = 0;  
  } else {
    $intLocksStart = $arrLocksOptions['LocksStart']; 
    $intLocksEnd = $arrLocksOptions['LocksEnd'];     
    $intRollWeeks = $arrLocksOptions['LocksRollWeeks'];
  }
  $dteLocksStartDate = date("Y-m-d", strtotime("+$intLocksStart Days")); 
  $dteLocksEndDate = date("Y-m-d", strtotime("+$intLocksEnd Days")); 
  $arrRequestWeeks['Dates']['Starts'] = $dteLocksStartDate;
  $arrRequestWeeks['Dates']['Ends'] = $dteLocksEndDate;  
  // Populate the array with all the dates....    
  $strDateLoop = $strStartDate;
  while (strtotime($strDateLoop) <= strtotime($strEndDate)) {
    $arrLocks['Dates'][$strDateLoop]['Status'] = -1;
	  $strDateLoop = date ("Y-m-d", strtotime("+1 day", strtotime($strDateLoop)));
  }
  $strLoopDate = $dteLocksStartDate;
  // Get the weeks we are looking at..... 
  $intLoopWeek = $intStartWeek;
  while ($intLoopWeek <= $intEndWeek) {
    $arrRequestWeeks['Weeks'][$intLoopWeek]['isSet'] = 0;  
    $intLoopWeek = addweeks($intLoopWeek, 1);
  }
  // Get any Lock dates available
  if ($intRollWeeks == 1) {
    $dteToEnd = date("Y-m-d", strtotime("+6 Days", strtotime(datefromweek(bbcweeknumber($dteLocksEndDate)))));
  } else {
    $dteToEnd = $dteLocksEndDate;
  }
  
  while (strtotime($strLoopDate) <= strtotime($strEndDate)) {
    $intCurrWeek = bbcweeknumber($strLoopDate);
    if (isset($arrRequestWeeks['Weeks'][$intCurrWeek])) {
      $arrRequestWeeks['Weeks'][$intCurrWeek]['isSet'] = 1;
    }
    if (strtotime($strLoopDate) >= strtotime($dteLocksStartDate) && strtotime($strLoopDate) <= strtotime($dteToEnd)) {
      $arrLocks['Dates'][$strLoopDate]['Status'] = 0;
    }
    $strLoopDate = date ("Y-m-d", strtotime("+1 day", strtotime($strLoopDate)));
  }
  if (count($arrRequestWeeks['Weeks']) != 0) {
      $pdo = OpenDBLinkA7();
      // Get the start and end dates for requests
      reset($arrRequestWeeks['Weeks']);
      $strRequestsStarWeek =  key($arrRequestWeeks['Weeks']);
      end($arrRequestWeeks['Weeks']);
      $strRequestsEndWeek = key($arrRequestWeeks['Weeks']);
      $rsLocks=[];
      // Finally get any Locks....
      $strQuery = "SELECT WeekNumber, iDay, id FROM LockRequests (nolock) WHERE (ScheduledPersonID = ?) AND (WeekNumber BETWEEN  ? AND ? ) AND (deleted = 0)";
      try { 
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(1,$sheduledPersonId, PDO::PARAM_INT);
        $stmt->bindParam(2,$strRequestsStarWeek, PDO::PARAM_STR);
        $stmt->bindParam(3,$strRequestsEndWeek, PDO::PARAM_STR);
        $stmt->execute();
        $rsLocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch(Exception $e) {
        logger()->critical('DB Error', (array) $e);
      }
   
    if (!empty($rsLocks)) {
      foreach ($rsLocks as $row) { 
        $intLockWeek = $row['WeekNumber'];
        $arrRequestWeeks['Weeks'][$intLockWeek]['HasRequest'] = 1;
        $arrRequestWeeks['Weeks'][$intLockWeek]['Locks'] = $row['iDay'];
        $arrRequestWeeks['Weeks'][$intLockWeek]['ID'] = $row['id'];
      }
    }
    // Now get any denied weeks... 
      $strQuery = "SELECT ID, WeekNumber FROM LockRequestsRestricted (nolock) WHERE (ScheduledPersonID = :sheduledPersonId) AND ( WeekNumber BETWEEN :strRequestsStarWeek AND :strRequestsEndWeek ) AND (Deleted = 0)";
      try { 
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(':sheduledPersonId',$sheduledPersonId, PDO::PARAM_INT);
        $stmt->bindParam(':strRequestsStarWeek',$strRequestsStarWeek, PDO::PARAM_STR);
        $stmt->bindParam(':strRequestsEndWeek',$strRequestsEndWeek, PDO::PARAM_STR);
        $stmt->execute();
        $rsDeniedLocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch(Exception $e) {
        logger()->critical('DB Error', (array) $e);
      }
      if (!empty($rsDeniedLocks)) {
        foreach ($rsDeniedLocks as $row) { 
          $arrDenied[$row['WeekNumber']] = 1;   
        } 
      }
    if (!empty($arrRequestWeeks) && isset($arrRequestWeeks['Weeks'])) {     
      foreach ($arrRequestWeeks['Weeks'] as $strWeekNumber => $arrWeek) {
          $strCurrWeekStartDate =  datefromweek($strWeekNumber);
          for ($dteCounter = 0; $dteCounter <= 6; $dteCounter++) {
            $strCurrLoopDate = date("Y-m-d", strtotime("+$dteCounter Days", (strtotime($strCurrWeekStartDate)))); 
            if (isset($arrWeek['HasRequest'])) {
              if ($arrWeek['Locks'] == $dteCounter) {
                $arrLocks['Dates'][$strCurrLoopDate]['Status'] = 2;           
              } else {
                $arrLocks['Dates'][$strCurrLoopDate]['Status'] = 1;
              }
            }
            if (isset($arrDenied[$strWeekNumber])) {
              $arrLocks['Dates'][$strCurrLoopDate]['Status'] = 4;
            }
          } 
        } 
    }
    $arrLocks['ReadDates'] = $arrRequestWeeks['Dates'];
   }   
      
  $arrLocks['Weeks'] = $arrRequestWeeks['Weeks'];
   return $arrLocks;
}

/**
 * Description : Read Weekly Lock Request By Team
 * @param : $strUserID (Admin userd ID )int,
 * @param : $intWeekNumber int
 * return [] 
 */
function GetWeeklyLocksByTeams($strUserID, $intWeekNumber) {
  $pdo = OpenDBLinkA7();
  $rsLocks=$arrLocks=[];
  try {
      $strQuery = "exec [dbo].[usp_GetWeeklyLocksByTeams] :intWeekNumber,:strUserID";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(':intWeekNumber',$intWeekNumber, PDO::PARAM_INT);
      $stmt->bindParam(':strUserID',$strUserID, PDO::PARAM_INT);
      $stmt->execute();
      $rsLocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }
                
 if (!empty($rsLocks)) {
  foreach ($rsLocks as $row) { 
    $strDateLocked  = date('l, jS F Y \a\t H:i:s',strtotime($row['RequestedOn']));
    $intDay = $row['iDay'];
    $arrLocks[$intDay][$row['ScheduledPersonID']]['FullName'] = $row['FullName'];
    $arrLocks[$intDay][$row['ScheduledPersonID']]['SortCode'] = $row['SortCode'];    
    $arrLocks[$intDay][$row['ScheduledPersonID']]['LockedOn'] = $strDateLocked;    
    $arrLocks[$intDay][$row['ScheduledPersonID']]['ID'] = $row['LockID'];
    $arrLocks[$intDay][$row['ScheduledPersonID']]['AdminRequest'] = $row['AdminRequest'];
    $arrLocks[$intDay][$row['ScheduledPersonID']]['AllocationsInfo'] = $row['AllocationsInformation'];
    if (!is_null($row['LockDuration'])) {
      $arrLocks[$intDay][$row['ScheduledPersonID']]['LockDuration'] = $row["LockDuration"];
    }
    if (!is_null($row['LockStartTime'])) {
      $arrLocks[$intDay][$row['ScheduledPersonID']]['LockStartTime'] = gmdate("H:i", $row["LockStartTime"]);
      $arrLocks[$intDay][$row['ScheduledPersonID']]['LockEndTime'] = gmdate("H:i", $row["LockEndTime"]);      
    }
    if (!is_null($row['DutyName'])) {
      if (isset($arrLocks[$intDay][$row['ScheduledPersonID']]['DutyName'])) {
        $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyName'] = $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyName'].'<br><b>'.$row['TeamFullName'].'</b><br> '.$row['DutyName'];
      } else {
        $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyName'] = '<b>'.$row['TeamFullName'].'</b><br> '.$row['DutyName'];
      } 
      $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyDuration'] = $row['Duration'];  
  
      if (!is_null($row['StartTime']) && ($row['StartTime']!=0) && ($row["EndTime"]!=0)) {
        $strStartTime = gmdate("H:i", $row["StartTime"]);
        $strEndTime = gmdate("H:i",$row["EndTime"]);
        $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyStart'] = $strStartTime;
        $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyEnd'] = $strEndTime;                
        $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyName'] = $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyName'].'<br>'.$strStartTime.'-'.$strEndTime;
      } else {
        if(!empty($row["Duration"])){
          $strDuration = round(gmdate("H",$row["Duration"]), 2, PHP_ROUND_HALF_UP);
        }else{
          $strDuration = 0;
        }
        $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyName'] = $arrLocks[$intDay][$row['ScheduledPersonID']]['CurrentDutyName'].'<br>'.$strDuration.' Hours';
      }
    }
   }
  }
    return $arrLocks;
}

/**
 * Description : This function gives information about Lock option in myrequest and lock subsection
 * @param : $strUserLogin int 
 * return []
 */

function GetLocksOptionsByUser($sheduledPersonId) {
  $pdo = OpenDBLinkA7();
  // Set the default
  $arrLockOptions['Locks'] = 0;
  $arrLockOptions['LocksStart'] = -1;
  $arrLockOptions['LocksEnd'] = -1;
  $arrLockOptions['LocksRollWeeks'] = 0; 
  $rsUser=[];
  try {
    $strQuery = "exec [dbo].[usp_GetLocksOptionsByScheduledPerson] ?";             
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $sheduledPersonId, PDO::PARAM_INT);
    $stmt->execute();
    $rsUser = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
  if (!empty($rsUser)) {
    foreach ($rsUser as $row) { 
        $arrLockOptions['Locks'] = $row['locks'];  
        $arrLockOptions['LocksStart'] = $row['LocksStart'];
        $arrLockOptions['LocksEnd'] = $row['LocksEnd'];
        $arrLockOptions['LocksRollWeeks'] = $row['LocksRollWeek'];     
      }
  } else {
    $arrLockOptions['Locks'] = 0;  
    $arrLockOptions['LocksStart'] = -1;
    $arrLockOptions['LocksEnd'] = -1;
    $arrLockOptions['LocksRollWeeks'] = 0; 
  }
return $arrLockOptions;  
}
/**
 * Description : get the lock denied Users in Teams
 * @param :$strUserID Adminlogin User Id int
 * @param :$intWeekNumber string
 * return []
 */

function GetLocksDenied ($strUserID, $intWeekNumber) {
  $pdo = OpenDBLinkA7();
  $arrLoclsRestricted=[];
  try {
    $strQuery = "exec [dbo].[usp_GetLocksDenied] :intWeekNumber,:strUserID";           
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(":intWeekNumber",$intWeekNumber, PDO::PARAM_INT);
    $stmt->bindParam(":strUserID", $strUserID, PDO::PARAM_INT);
    $stmt->execute();
    $rsLocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
   if (!empty($rsLocks)) {
    foreach ($rsLocks as $row) { 
      $arrLoclsRestricted[$row['ID']]['Login'] = $row['Login'];
      $arrLoclsRestricted[$row['ID']]['ScheduledPersonID'] = $row['ScheduledPersonID'];
      $arrLoclsRestricted[$row['ID']]['Name'] = $row['FullName'];
    }
  }
  return $arrLoclsRestricted;
}