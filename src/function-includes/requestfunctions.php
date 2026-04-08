<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// ##### New Code ######
/**
 * Description : Read Request By Schedulled Person ID
 * @param :$schedulingPersonId int
 * @param :$strStartDate str
 * @param :$strEndDate str
 * return []
 */
function GetRequestsForscheduledPersonID($schedulingPersonId, $strStartDate, $strEndDate) {
  $pdo = OpenDBLinkA7();
  $rsRequests=$arrRequests=[];
  try {
    $strQuery ="exec [dbo].[usp_fetch_RequestsByscheduledPersonID] :strStartDate,:strEndDate,:schedulingPersonId";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':strStartDate', $strStartDate, PDO::PARAM_STR);
    $stmt->bindParam(':strEndDate', $strEndDate, PDO::PARAM_STR);
    $stmt->bindParam(':schedulingPersonId', $schedulingPersonId, PDO::PARAM_INT);
    $stmt->execute();
    $rsRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
  if (!empty($rsRequests)) {
    foreach ($rsRequests as $row) {
      $strDate = date("Y-m-d",strtotime($row['dDate']));
      $arrRequests[$strDate]['Approved'] = $row['Approved'];
      $arrRequests[$strDate]['IsOK'] = $row['isOK'];
      $arrRequests[$strDate]['Description'] = $row['Description'];
      $arrRequests[$strDate]['ID'] = $row['ID'];
    }
  }
  return $arrRequests;
}

/**
 * Description : GetAppliedRequestsANdLocks For Week And Teamwise use at View Wekly and View Daily
 * @param : $intTeamID int,
 * @param : $strStartDate Str,
 * @param : $strEndDate Str
 * return []
 *
 */

function GetAppliedRequestsANdLocks($strStartDate, $strEndDate) {
  $dowMap = array(
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
  );
  $intStartWeek = bbcweeknumber($strStartDate);
  $intEndWeek = bbcweeknumber($strEndDate);
  $pdo = OpenDBLinkA7();
  $result=$arrRequests=[];
  try {
    $sql = "exec [dbo].[usp_GetRequestsForWeek] ?,?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $strStartDate, PDO::PARAM_STR);
    $stmt->bindParam(2, $strEndDate, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
  if (!empty($result)) {
   foreach ($result as $row) {
    $strDate = date("Y-m-d",strtotime($row['dDate']));
    $intDay =  $dowMap[date("D", strtotime($strDate))];
    $arrRequests[$row['ScheduledPersonID']][$intDay]['Approved'] = $row['Approved'];
    $arrRequests[$row['ScheduledPersonID']][$intDay]['IsOK'] = $row['isOK'];
    $arrRequests[$row['ScheduledPersonID']][$intDay]['Description'] = $row['Description'];
    $arrRequests[$row['ScheduledPersonID']][$intDay]['ID'] = $row['ID'];
    $arrRequests[$row['ScheduledPersonID']][$intDay]['AffectLocks'] = $row['AffectLocks'];
    $arrRequests[$row['ScheduledPersonID']][$intDay]['IsLock'] = 0;
  }
}
  // Get the Locks.....
  $rsLocks =[];
  try {
    $sql = "exec [dbo].[usp_GetLocksForWeek] :intStartWeek,:intEndWeek";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':intStartWeek', $intStartWeek, PDO::PARAM_INT);
    $stmt->bindParam(':intEndWeek', $intEndWeek, PDO::PARAM_INT);
    $stmt->execute();
    $rsLocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
if (!empty($rsLocks)) {
 foreach ($rsLocks as $row) {
    $intDay = $row['iDay'];
    $arrRequests[$row['ScheduledPersonID']][$intDay]['ID'] = $row['ID'];
    $arrRequests[$row['ScheduledPersonID']][$intDay]['IsLock'] = 1;
    $arrRequests[$row['ScheduledPersonID']][$intDay]['TipText'] = $row['AllocationsInfo'];
  }
}
  return $arrRequests;
}

/**
 * Description : Fetch Request Types in adminsection
 * @param :$intGroupID int
 * @param :$date str
 * return []
 */

function GetRequestTypesfromGroupID ($intGroupID, $date) {

  $pdo = OpenDBLinkA7();
  $rsRequestTypes=$arrRequestTypes=[];
  $strQuery = "SELECT id, description, startdate, enddate, day_0, day_1, day_2, day_3, day_4, day_5, day_6, isRestricted, UniqueCount, AffectsOthers, SendEmails,
          RequestsAllowed, Starts, Ends, AllowOverLimit, AffectLocks
          FROM RequestTypes (NOLOCK)
          WHERE GroupID = $intGroupID
          AND enddate >= CONVERT(DATETIME, '$date 00:00:00', 102)
          ORDER BY startdate";
   try {
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $rsRequestTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }

  if (!empty($rsRequestTypes))  {
    foreach ($rsRequestTypes as $row) {
      $arrRequestTypes[$row['id']]['description'] = $row['description'];
      $arrRequestTypes[$row['id']]['StartDate'] = date("d/m/Y",strtotime($row['startdate']));
      $arrRequestTypes[$row['id']]['EndDate'] =  date("d/m/Y",strtotime($row['enddate']));
      $arrRequestTypes[$row['id']]['Starts'] = $row['Starts'];
      $arrRequestTypes[$row['id']]['Ends'] = $row['Ends'];
      $arrRequestTypes[$row['id']]['RequestsAllowed'] = $row['RequestsAllowed'];
      $arrRequestTypes[$row['id']]['AllowOverLimit'] = $row['AllowOverLimit'];
      $arrRequestTypes[$row['id']]['isRestricted'] = $row['isRestricted'];
      $arrRequestTypes[$row['id']]['UniqueCount'] = $row['UniqueCount'];
      $arrRequestTypes[$row['id']]['AffectLocks'] = $row['AffectLocks'];
      $arrRequestTypes[$row['id']]['AffectsOthers'] = $row['AffectsOthers'];
      $arrRequestTypes[$row['id']]['SendEmail'] = $row['SendEmails'];
      $arrRequestTypes[$row['id']]['days'][0] = $row['day_0'];
      $arrRequestTypes[$row['id']]['days'][1] = $row['day_1'];
      $arrRequestTypes[$row['id']]['days'][2] = $row['day_2'];
      $arrRequestTypes[$row['id']]['days'][3] = $row['day_3'];
      $arrRequestTypes[$row['id']]['days'][4] = $row['day_4'];
      $arrRequestTypes[$row['id']]['days'][5] = $row['day_5'];
      $arrRequestTypes[$row['id']]['days'][6] = $row['day_6'];
    }
 }
  return $arrRequestTypes;
}

/**
 * Description : GetRequestTypesFromUser used in Admin arear
 * @param :$strUser string
 * @param :$dteStartDate string
 * @param :$dteEndDate string
 * return []
 */

function GetRequestTypesFromUser($strUser, $dteStartDate, $dteEndDate) {
  $pdo = OpenDBLinkA7();
  $rsRequestTypes  = [];
  $arrRequestTypes = [];
  $intMonthsAhead  = calcmonthdiff(date("Y-m-d"), $dteStartDate);
  try {
    $strQuery = "exec [dbo].[GetRequestTypesFromUser] ?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
    $stmt->bindParam(2, $dteStartDate,PDO::PARAM_STR);
    $stmt->bindParam(3, $dteEndDate,PDO::PARAM_STR);
    $stmt->execute();
    $rsRequestTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    logger()->critical('DB Error', (array) $e);
  }
  if (!empty($rsRequestTypes)) {
    foreach ($rsRequestTypes as $row) {
      $arrMonthlyAllowed = explode(",", $row['RequestsAllowedMonthly']);
      $intGroupID = $row['GroupID'];
      $intTypeID = $row['TypeID'];
      $arrRequestTypes[$intGroupID]['Description'] = $row['GroupDescription'];
      $arrRequestTypes[$intGroupID]['RequestsAllowed'] = $arrMonthlyAllowed[$intMonthsAhead];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['Description'] = $row['TypeDescription'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['StartDate'] = date("Y-m-d",strtotime($row['startdate']));
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['EndDate'] = date("Y-m-d",strtotime($row['enddate']));
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['Starts'] = $row['Starts'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['Ends'] = $row['Ends'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['RequestsAllowed'] = $row['RequestsAllowed'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['AllowOverLimit'] = $row['AllowOverLimit'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['AffectLocks'] = $row['AffectLocks'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['isRestricted'] = $row['isRestricted'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['UniqueCount'] = $row['UniqueCount'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['days'][0] = $row['day_0'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['days'][1] = $row['day_1'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['days'][2] = $row['day_2'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['days'][3] = $row['day_3'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['days'][4] = $row['day_4'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['days'][5] = $row['day_5'];
      $arrRequestTypes[$intGroupID]['Types'][$intTypeID]['days'][6] = $row['day_6'];
    }
  }
  if (isset($arrRequestTypes)) {
    return($arrRequestTypes);
  }
}

/**
 * Description :Get Request on My reqsuest and Lock Page
 * @param:$strUser str
 * @param:$sheduledPersonId int ,
 * @param :$strStartDate string,
 * @param :$strEndDate string
 * @param : int
 * return []
 *
 */

function GetRequests($strUser, $sheduledPersonId, $strStartDate, $strEndDate, $intAdmin = 0) {
  $dowMap = array(
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
  );
  $pdo = OpenDBLinkA7();
  $rsMyRequests=[];
  $arrLocks =  ReadLocksForUser($sheduledPersonId,$strStartDate, $strEndDate);
  $arrDatesWeeks = GetWeeksFromDate($strStartDate, $strEndDate);
  $intMonthsAhead = calcmonthdiff(date("Y-m-d"), $strStartDate);
  $arrRequests = GetGroupsTypesAndAmountsByDate($strUser,$sheduledPersonId, $strStartDate, $strEndDate, $intMonthsAhead, $intAdmin);
  // #### Get just the requests ####
   try {
    $strQuery = "exec [dbo].[usp_GetRequestsOnlyByDates] ?,?,?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
    $stmt->bindParam(2, $strStartDate,PDO::PARAM_STR);
    $stmt->bindParam(3, $strEndDate,PDO::PARAM_STR);
    $stmt->bindParam(4, $intAdmin,PDO::PARAM_INT);
    $stmt->execute();
    $rsMyRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
   }catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
  }
    $intTotalCount = 0;
    if (!empty($rsMyRequests)) {
      foreach ($rsMyRequests as $row) {
        $intGroupID = $row['GroupID'];
        $intTypeID = $row['RequestType'];
        $strRequestLogin = $row['Login'];
        $strCurrDate = date('Y-m-d',strtotime($row['dDate']));
        // Update the number in......
        if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'])) {
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'] = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'] + 1;
        } else {
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'] = 1;
        }
        if (strtolower($strRequestLogin) == strtolower($strUser)) {
          // If it's approved and affects lock then set the lock for this day to type ONE
          if ($row['Approved'] == 1 && $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['AffectLocks'] == 1) {
            if (strtotime($strCurrDate) >= strtotime($arrLocks['ReadDates']['Starts']) ) {
              $strCounterStartDate = datefromweek($arrDatesWeeks[$strCurrDate]);
              for ($intCounter = 0; $intCounter <= 6; $intCounter++) {
              $arrLocks['Dates'][$strCounterStartDate]['Status'] = 3;
              $strCounterStartDate = date("Y-m-d", strtotime("+1 day", strtotime($strCounterStartDate)));
              }
            }
          }
          $arrRequests['UserDates'][$strCurrDate][$row['RequestType']] = $row['RequestType'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Position'] = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Approved'] = $row['Approved'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestID'] = $row['RequestID'];

          if (isset($arrRequests['Groups'][$intGroupID]['Types'][$row['RequestType']])) {
            if ($arrRequests['Groups'][$intGroupID]['Types'][$row['RequestType']]['UniqueCount'] == 0) {
              if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CountDay'] == 1) {
                $intTotalCount = $intTotalCount + 1;
                if (isset($arrRequests['Groups'][$intGroupID]['User']['General']['Count'])) {
                  $arrRequests['Groups'][$intGroupID]['User']['General']['Count'] = $arrRequests['Groups'][$intGroupID]['User']['General']['Count'] + 1;
                }
                else {
                  $arrRequests['Groups'][$intGroupID]['User']['General']['Count'] = 1;
                }
                $arrRequests['Groups'][$intGroupID]['User']['General']['Remaining'] = $arrRequests['Groups'][$intGroupID]['User']['General']['Remaining'] - 1;
              }
            } else {
              // Is there a closure in place?    if not count the day
              if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CountDay'] == 1) {
                if (isset($arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['Count'])) {
                  $arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['Count'] = $arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['Count'] + 1;
                }
                else {
                  $arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['Count'] = 1;
                }
                $arrRequests['Groups'][$intGroupID]['User']['Additional'][$row['RequestType']]['Remaining'] = $arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['Remaining'] - 1;
              }
            } //else close
          }
        }
      }
    }
    // #### END Get just this persons requests ####
  if (isset($arrRequests)) {
   // Loop through and set whether the person can request these.....
   foreach ($arrRequests['Groups'] as $intGroupID => $arrGroup) {
     $intYearlyLimit = 0;
     if ($arrGroup['RequestsAllowedYearly'] != -1) {
       // Get the number of group counted requests for this person for this year....
       $intCountYearly = GetYearlyRequestCount ($intGroupID, $strStartDate, $strUser);
       $arrRequests['Groups'][$intGroupID]['RequestsInYearly'] = $intCountYearly;
       if ($intCountYearly >= $arrGroup['RequestsAllowedYearly']) {
         $intYearlyLimit = 1;
       }
     } //RequestsAllowedYearly is not set
     foreach ($arrGroup['Types'] as $intTypeID => $arrType) {
        foreach ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'] as $strCurrDate => $arrRequestDate) {
          $uRequestsStarts = strtotime("+".$arrType['RequestsStart']." days", strtotime(date("Y-m-d")));
          $uRequestsEnds = strtotime("+".$arrType['RequestsEnd']." days", strtotime(date("Y-m-d")));
          if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'])) {
            $intRequestsIn = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'];
          } else {
            $intRequestsIn = 0;
          }
          if (isset($arrRequests['UserDates'][$strCurrDate])) {
            // #### The user has a request in and therefor can't request another one.....
            // #### set IsAvailable to 0
            if ($arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['AffectsOthers'] == 1) {
              $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
            } else {
              $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
            }
            if (isset($arrRequests['UserDates'][$strCurrDate][$intTypeID])) {
              // ### This is the type that's been requested
              if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Approved'] == 1) {
                $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveApproved';
                $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'Request Approved';
              } else {
                if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsAllowed'] == -1) {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'BestEndevoursApplied request-context-menu';
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'You have expressed your preference.<br>Not yet Approved';
                  } else {
                  if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Position'] <= $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsAllowed']) {
                     $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveOK request-context-menu';
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'Request OK.<br>Not yet Approved.';
                  } else {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveNotOK request-context-menu';
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'Request in Waiting List.<br>Not Approved.';
                  }
                }
              }
            } else {
              if (strtotime($strCurrDate) < strtotime($arrType['StartDate']) || strtotime($strCurrDate) > strtotime($arrType['EndDate']) || strtotime($strCurrDate) < $uRequestsStarts || strtotime($strCurrDate) > $uRequestsEnds) {
                $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'DeadDay';
              } else {
                // Find out if there is a dependent request in....
                $intDepRequestIn = 0;
                foreach ($arrRequests['UserDates'][$strCurrDate] as $intCurrType => $intSomething)  {
                  if (isset($arrRequests['Groups'][$intGroupID]['Types'][$intCurrType])) {
                    if ($arrRequests['Groups'][$intGroupID]['Types'][$intCurrType]['AffectsOthers'] == 1) {
                      $intDepRequestIn = 1;
                    }
                  }
                }
                if ($intDepRequestIn == 0) {
                  if (isset($arrLocks['Weeks'][$arrDatesWeeks[$strCurrDate]]['HasRequest'])) {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You have a Lock.";
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'haslock';
                  } else {
                    // This is a grouped request
                    if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsAllowed'] == -1) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'BestEndevours';
                    } else {
                      if ($intRequestsIn < $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsAllowed']) {
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveAvailable';
                      } else {
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveNotAvailable';
                      }
                    }
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'Alternate Non Dependant Request In on this day.<br>You May request this.';
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                  }
                }
                else {
                  if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsAllowed'] == -1) {
                    if ($arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['AffectsOthers'] == 1) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'AlreadyRequested';
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'Alternate Request In on this day.';
                    } else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'BestEndevours';
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'Alternate Request In on this day.<br>However you may still express your preference here.';
                    }
                  } else {
                    if ($intRequestsIn < $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsAllowed']) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'AlreadyRequestedAvailable';
                    } else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'AlreadyRequestedNotAvailable';
                    }
                    if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] == 1) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'Alternate Request In on this day.<br>However you may still express your preference here.';
                    } else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = 'Alternate Request In on this day.';
                    }
                  }
                }
              }
            }
          }
          // ### END The user has a request in and therefor can't request another one.....
          else {
          // #### This is a day that's not been requested.....
            if (strtotime($strCurrDate) < strtotime($arrType['StartDate']) || strtotime($strCurrDate) > strtotime($arrType['EndDate']) || strtotime($strCurrDate) < $uRequestsStarts || strtotime($strCurrDate) > $uRequestsEnds) {
              $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
              $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'DeadDay';
            } else {
              // Find out whether the person has reached the limit.....
              $intUniqueID = $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['UniqueCount'];
              // The number is set for general and unique count so just check this
              $intNumberAllowed = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsAllowed'];
              $intAllowOver = $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['OverLimit'];
              if (isset($arrDatesWeeks[$strCurrDate])) {
                  if (isset($arrLocks['Weeks'][$arrDatesWeeks[$strCurrDate]]['HasRequest']) && $arrType['AffectLocks'] == 1) {
                    $intHasLock = 1;
                  } else {
                    $intHasLock = 0;
                  }
                } else {
                  $intHasLock = 0;
                }
              if ($intUniqueID == 0) {
              // Is there a closure in place?
              if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['ClosedType'])) {
                if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['ClosedType'] == 2) {
                  $intAllowOver = 1;
                }
              }
              //General type
                // This is a General request type
                if (isset($arrRequests['Groups'][$intGroupID]['User']['General']['Count'])) {
                  $intUserRequestsIn = $arrRequests['Groups'][$intGroupID]['User']['General']['Count'];
                } else {
                  $intUserRequestsIn = 0;
                }
            //Are we over the limit this month?
                if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'])) {
                  $intRequestsIn = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'];
                } else {
                  $intRequestsIn = 0;
                }
                $intUserRequestsAllowed = $arrRequests['Groups'][$intGroupID]['User']['General']['NumberMayRequest'];
                if ($intUserRequestsIn >= $intUserRequestsAllowed || $intTotalCount >= $arrRequests['User']['MaxGeneralRequests'] || $intYearlyLimit == 1) {
                  // Over the limit......
                  $intOverrideClosed = 0;
                  if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['ClosedType'])) {
                    if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['ClosedType'] == 2) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                      $intOverrideClosed = 1;
                    } else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                    }
                  } else {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                  }
                  if ($intYearlyLimit == 1 && $intOverrideClosed == 0) {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You have reached your Request Limit this Year.";
                  } else {
                    if ($intOverrideClosed == 0) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You have reached your Request Limit this Month.";
                    } else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You may join the waiting list.";
                    }
                  }
                  if ($intNumberAllowed == -1) {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'BestEndevours';
                  } else {
                    if ($intRequestsIn >= $intNumberAllowed) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveNotAvailable';
                    } else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveAvailable';
                    }
                  }
                } else {
                  if ($intHasLock == 1) {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You have a Lock.";
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'haslock';
                  } else {
                  // Allow clicks over the number allowed?
                  if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['MayRequest'] == 1) {
                    if ($intNumberAllowed == -1) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You may express your preference here.";
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'BestEndevours';
                    } else {
                      if ($intRequestsIn >= $intNumberAllowed) {
                        if ($intAllowOver == 1) {
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You may join the waiting list.";
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveNotAvailable';
                        } else {
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "This type is fully requested.<br>Additional Requests are not allowed.";
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveNotAvailable';
                        }
                      } else {
                        // We are not over the limit.....
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "This Request is available.";
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveAvailable';
                      }
                    }
                  } else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "This Request is not available.";
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'DeadDay';
                  }
                } //else end
               }
              } //intUniqueID if close
              else {
                // #### It's a seperately counted type....
                // The class will have been set to 'AltIn' if another request is in.....
                if (isset($arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['Count'])) {
                  $intUserRequestsIn = $arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['Count'];
                } else {
                  $intUserRequestsIn = 0;
                }
                //Are we over the limit this month?
                if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'])) {
                  $intRequestsIn = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['RequestsIn'];
                } else {
                  $intRequestsIn = 0;
                }
                $intUserRequestsAllowed = $arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['NumberMayRequest'];
                if ($intUserRequestsIn >= $intUserRequestsAllowed) {
                  // Over the limit......
                  $intClosedType = -1;
                  if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['ClosedType'])) {
                    $intClosedType = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['ClosedType'];
                    if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['ClosedType'] == 2) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You may join the waiting list.";
                    } else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You have reached your Request Limit this Month.";
                    }
                  } else {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You have reached your Request Limit this Month.";
                  } if ($intNumberAllowed == -1) {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'BestEndevours';
                  } else {
                    if ($intRequestsIn >= $intNumberAllowed) {
                      if ($intClosedType == 0) {
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'DeadDay';
                      } else {
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveNotAvailable';
                      }
                    }
                    else {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveAvailable';
                    }
                  }
                } else {
                if ($intHasLock == 1) {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You have a Lock.";
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'haslock';
                  } else {
                  // Allow clicks over the number allowed?
                  if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['MayRequest'] == 1) {
                    if ($intNumberAllowed == -1) {
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You may express your preference here.";
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'BestEndevours';
                    }else {
                      if ($intRequestsIn >= $intNumberAllowed) {
                        if ($intAllowOver == 1) {
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "You may join the waiting list.";
                        }
                        else {
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "This type is fully requested.<br>Additional Requests are not allowed.";
                        }
                      $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveNotAvailable';
                      }
                      else {
                        // We are not over the limit.....
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 1;
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "This Request is available.";
                        $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'LeaveAvailable';
                      }
                    }
                  }
                  else {
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['CanClick'] = 0;
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Tip'] = "This Request is not available.";
                    $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Class'] = 'DeadDay';
                  }
                }
                }
              }
            }
          }
        }
      }
    }
  }

if (!empty($arrLocks) && isset($arrLocks['Dates'])) {
  foreach ($arrLocks['Dates'] as $strCurrDate => $arrLockStatus) {
    $arrRequests['Locks']['Dates'][$strCurrDate]['Status'] = $arrLockStatus['Status'];
    switch ($arrLockStatus['Status']) {
      case 0:
        // Available
        $arrRequests['Locks']['Dates'][$strCurrDate]['Class'] = 'LeaveAvailable';
        $arrRequests['Locks']['Dates'][$strCurrDate]['Tip'] = 'You may request a Lock in this week.';
        $intWeekNumber = bbcweeknumber($strCurrDate);
        $intDay =  $dowMap[date("D", strtotime($strCurrDate))];
        $arrRequests['Locks']['Dates'][$strCurrDate]['strJS'] = 'onclick="javascript:LockApply(\''.$intWeekNumber.'\',\''.$intDay.'\')"';
        break;
      case 1:
        // Not Available
        $arrRequests['Locks']['Dates'][$strCurrDate]['Class'] = 'haslock';
        $arrRequests['Locks']['Dates'][$strCurrDate]['Tip'] = 'You already have a Lock this week.';
        $arrRequests['Locks']['Dates'][$strCurrDate]['strJS'] = '';
        break;
      case 2:
        // Request in
        $arrRequests['Locks']['Dates'][$strCurrDate]['Class'] = 'LeaveOK';
        $arrRequests['Locks']['Dates'][$strCurrDate]['Tip'] = 'This is your Lock for this week.';
        $arrRequests['Locks']['Dates'][$strCurrDate]['strJS'] = '';
        break;
      case 3:
        // Request that's been approved in
        $arrRequests['Locks']['Dates'][$strCurrDate]['Class'] = 'haslock';
        $arrRequests['Locks']['Dates'][$strCurrDate]['Tip'] = 'You have an approved Request in this week.';
        $arrRequests['Locks']['Dates'][$strCurrDate]['strJS'] = '';
        break;
      case 4:
        // Request that's been approved in
        $arrRequests['Locks']['Dates'][$strCurrDate]['Class'] = 'haslock';
        $arrRequests['Locks']['Dates'][$strCurrDate]['Tip'] = 'You may not apply for a Lock during this week.';
        $arrRequests['Locks']['Dates'][$strCurrDate]['strJS'] = '';
        break;

      default:
        $arrRequests['Locks']['Dates'][$strCurrDate]['Class'] = 'DeadDay';
        $arrRequests['Locks']['Dates'][$strCurrDate]['strJS'] = '';
        break;
     }
   }
 }

  if (isset($arrRequests)) {
    return ($arrRequests);
  } else{
    return [];
  }
}

/**
 * Decription : This function is working under weekly request
 * @param :$strUser str ,
 * @param :$strStartDate str,
 * @param :$strEndDate str,
 * @param :$intMonthsAhead str,
 * @param :$intAdmin int Admin Type
 * return []
 */

function GetGroupsTypesAndAmountsByDate($strUser,$sheduledPersonId, $strStartDate, $strEndDate, $intMonthsAhead, $intAdmin = 0) {
  $pdo = OpenDBLinkA7();
  $rsClosed =[];
  $intMaxGeneralRequests = 0;
  $requestdefaults=[];
  $dowMap = array(
      "Sat"  => 0,
      "Sun"  => 1,
      "Mon"  => 2,
      "Tue"  => 3,
      "Wed"  => 4,
      "Thu"  => 5,
      "Fri"  => 6
  );
  // Get the defaults
  // zGroupID is now used as to whether this type affercts whether the day becomes locked once a request is in
  try {
    $strQuery ="exec [dbo].[usp_Get_Request_GroupsTypesAndAmountsByDate] :strStartDate,:strEndDate,:strUser,:intAdmin";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':strStartDate', $strStartDate, PDO::PARAM_STR);
    $stmt->bindParam(':strEndDate', $strEndDate, PDO::PARAM_STR);
    $stmt->bindParam(':strUser', $strUser, PDO::PARAM_STR);
    $stmt->bindParam(':intAdmin', $intAdmin, PDO::PARAM_STR);
    $stmt->execute();
    $requestdefaults = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
  if (!empty($requestdefaults)) {
    foreach ($requestdefaults as $row) {
      $intGroupID = $row['GroupID'];
      $intTypeID = $row['TypeID'];
      $arrMonths = explode(",", $row['RequestsAllowedMonthly']);
      $intGeneralAllowed = $arrMonths[$intMonthsAhead];
      $arrRequests['Groups'][$intGroupID]['Description'] = $row['RequestGroupDesc'];
      $arrRequests['Groups'][$intGroupID]['RequestsAllowedYearly'] = $row['RequestsAllowedYearly'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['OverLimit'] = $row['AllowOverLimit'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['Description'] = $row['description'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['StartDate'] = date("Y-m-d",strtotime($row['startdate']));
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['EndDate'] = date("Y-m-d",strtotime($row['enddate']));
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['UniqueCount'] = $row['UniqueCount'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['AffectsOthers'] = $row['AffectsOthers'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['isRestricted'] = $row['isRestricted'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['RequestsAllowed'] = $row['RequestsAllowed'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['AffectLocks'] = $row['AffectLocks'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['RequestsStart'] = $row['Starts'];
      $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['RequestsEnd'] = $row['Ends'];
      // Is this a restricted type that the uesr can/cannot see?
      if ($row['isRestricted'] == 1) {
        if (!is_null($row['LinkedLogin'])) {
          $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['CanRequest'] = 1;
        } else {
          $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['CanRequest'] = 0;
        }
      } else {
        $arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['CanRequest'] = 1;
      }

      if ($arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['UniqueCount'] == 0) {
        if ($intMaxGeneralRequests < ceil(($intGeneralAllowed * $row['EFT']) / 100)) {
          $intMaxGeneralRequests = ceil(($intGeneralAllowed * $row['EFT']) / 100);
        }
        $arrRequests['Groups'][$intGroupID]['User']['General']['NumberMayRequest'] = ceil(($intGeneralAllowed * $row['EFT']) / 100);
        $arrRequests['Groups'][$intGroupID]['User']['General']['Remaining'] = ceil(($intGeneralAllowed * $row['EFT']) / 100);
      } else {
        $arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['NumberMayRequest'] = ceil(($row['RequestsAllowed'] * $row['EFT1']) / 100);
        $arrRequests['Groups'][$intGroupID]['User']['Additional'][$intTypeID]['Remaining'] = ceil(($row['RequestsAllowed'] * $row['EFT1']) / 100);
      }
      for ($i=0; $i <= 6; $i++) {
        $arrdefaults[$intGroupID][$intTypeID][$i] = $row['day_'.$i];
      }
    }
  }
  if (isset($arrdefaults)) {
    //### END Get the defaults ###
    //###  Now put the defaults into the requests array for the specified period
      $strLoopDate = $strStartDate;
      while (strtotime($strLoopDate) <= strtotime($strEndDate)) {
        $daynumber = $dowMap[date("D", strtotime($strLoopDate))];
        foreach ($arrdefaults as $intCurrGroup => $arrTypes) {
          foreach ($arrTypes as $intCurrType => $arrDates) {
            $arrRequests['Groups'][$intCurrGroup][$intCurrType]['Dates'][$strLoopDate]['RequestsAllowed'] = $arrDates[$daynumber];
            $arrRequests['Groups'][$intCurrGroup][$intCurrType]['Dates'][$strLoopDate]['MayRequest'] = 1;
            $arrRequests['Groups'][$intCurrGroup][$intCurrType]['Dates'][$strLoopDate]['CountDay'] = 1;
          }
        }
	      $strLoopDate = date ("Y-m-d", strtotime("+1 day", strtotime($strLoopDate)));
      }
      //### Get any closed dates
      try {
        $strQuery ="exec [dbo].[usp_Get_ClosedRequestByDate] :strStartDate,:strEndDate,:strUser,:intAdmin";
        $stmt = $pdo->prepare($strQuery);
        $stmt->bindParam(':strStartDate', $strStartDate, PDO::PARAM_STR);
        $stmt->bindParam(':strEndDate', $strEndDate, PDO::PARAM_STR);
        $stmt->bindParam(':strUser', $strUser, PDO::PARAM_STR);
        $stmt->bindParam(':intAdmin', $intAdmin, PDO::PARAM_STR);
        $stmt->execute();
        $rsClosed = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch(Exception $e) {
        logger()->critical('DB Error', (array) $e);
       }

     if (!empty($rsClosed)) {
      foreach ($rsClosed as $row) {
        $intGroupID = $row['GroupID'];
        if (isset($arrRequests['Groups'][$intGroupID])) {
          $strClosedStart = date('Y-m-d',strtotime($row['startdate']));
          $strClosedEnd = date('Y-m-d',strtotime($row['enddate']));
          $intCloseType = $row['type'];
          $strLoopDate = $strClosedStart;
          while (strtotime($strLoopDate) <= strtotime($strClosedEnd)) {
            //foreach ($arrRequests['Groups'] as $intGroupID => $arrGroup) {
            foreach ($arrRequests['Groups'][$intGroupID]['Types'] as $intTypeID => $arrType) {
              $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['ClosedType'] = $intCloseType;
              switch ($intCloseType) {
                case 0;
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['RequestsAllowed'] = 0;
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['MayRequest'] = 0;
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['CountDay'] = 0;
                  break;
                case 1:
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['RequestsAllowed'] = 0;
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['MayRequest'] = 1;
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['CountDay'] = 1;
                  break;
                case 2:
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['RequestsAllowed'] = 0;
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['MayRequest'] = 1;
                  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strLoopDate]['CountDay'] = 0;
                  break;
              }
            }
	        $strLoopDate = date ("Y-m-d", strtotime("+1 day", strtotime($strLoopDate)));
          }
        }
      }
    }
      $arrRequests['User']['MaxGeneralRequests'] = $intMaxGeneralRequests;
      return $arrRequests;
    }
}

// #### END New Code ####
/**
 * Decription : GetWeeklyRequests
 * @param :$strUser str,
 * @param :$strStartDate str,
 * @param :$strEndDate str,
 * @param :$intAdmin int
 * return []
 */

function GetWeeklyRequests($strUser, $intScheduledPersonID,$strStartDate, $strEndDate, $intAdmin = 0) {
  $pdo = OpenDBLinkA7();
  $rsRequests=[];
  $groupIDs=[];
  $arrAllocations=[];
  $intWeekNumber = bbcweeknumber($strStartDate);
  // Get all the allowed requests and types and put it into the $arrRequests array
  $intMonthsAhead = calcmonthdiff(date("Y-m-d"), $strStartDate);
  $arrRequests = GetGroupsTypesAndAmountsByDate($strUser, $intScheduledPersonID,$strStartDate, $strEndDate, $intMonthsAhead, $intAdmin);
  if(!empty($arrRequests) && isset($arrRequests['Groups'])) {
    $groupIDs = array_keys($arrRequests['Groups']);
  }
  if (isset($arrRequests)) {
      try {
      $strQuery = "exec [dbo].[usp_GetWeeklyRequests] ?,?,?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
      $stmt->bindParam(2, $strStartDate,PDO::PARAM_STR);
      $stmt->bindParam(3, $strEndDate,PDO::PARAM_STR);
      $stmt->bindParam(4, $intAdmin,PDO::PARAM_INT);
      $stmt->execute();
      $rsRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        logger()->critical('db error', (array) $e);
      }
     if (!empty($rsRequests)) {
      foreach ($rsRequests as $row) {
        $intGroupID = $row['GroupID'];
        if (!empty($groupIDs) && in_array($intGroupID, $groupIDs)) {
          $intTypeID = $row['RequestType'];
          $strRequestLogin = $row['Login'];
          $strCurrDate = date('Y-m-d',strtotime($row['dDate']));
          if ($row['ScheduledPersonID'] != '') {
            $arrScheduledPersons[] = $row['ScheduledPersonID'];
          }
          $arrScheduledPersons= array_unique($arrScheduledPersons);
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['ID'] = $row['RequestID'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['FullName'] = $row['FullName'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['Login'] = $strRequestLogin;
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['isOK'] = $row['isOK'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['Unlikely'] = $row['Unlikely'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['Approved'] = $row['Approved'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['ShortNotice'] = $row['ShortNotice'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['OfficeComments'] = $row['Comments'];
          $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$strCurrDate]['Requests'][$row['RequestID']]['UserComments'] = $row['UserComments'];
       }
      }
    }
      if (isset($arrScheduledPersons)) {
        if ($intAdmin ==0) {
          $arrAllocations = weeklyRequestAllocationsAndRotaWIhMasking($intWeekNumber,$strUser,$strStartDate,$strEndDate);
          $arrRequests['Allocations'] = $arrAllocations;
        } else {
          $arrAllocations = ReadAllocationANDRotaByShedulledPerson($arrScheduledPersons, $intWeekNumber, $intAdmin);
          $arrRequests['Allocations'] = $arrAllocations;
        }
    }
  }
  return $arrRequests;
}
/**
 * Decription : GetDailyRequestsBasic using in SKILL ADMIN
 */

function GetDailyRequestsBasic($strDate, $intTeamID) {
  // Gets the requests for a peiod - simply whether one
  $pdo = OpenDBLinkA7();
  $rsRequests = [];
  $requests   = [];
  try {
    $strQuery = "exec [dbo].[usp_GetDailyRequestsBasic] ?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $strDate,PDO::PARAM_STR);
    $stmt->bindParam(2, $intTeamID,PDO::PARAM_INT);
    $stmt->execute();
    $rsRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch(Exception $e) {
        logger()->critical('DB Error', (array) $e);
       }
   if (!empty($rsRequests)) {
    foreach($rsRequests as $row) {
    $type = $row['description'];
    $staffnumber = $row['StaffNumber'];
    $requests[$staffnumber] = $type;
   }
 }
  if (isset($requests)) {
    return ($requests);
  }
}

/**
 * Description : GetRequestTypeDescriptionFromID
 * @param $intID int
 * return string
 */
function GetRequestTypeDescriptionFromID ($intID) {
  $pdo = OpenDBLinkA7();
   // Get request group....
  $strQuery = "SELECT description FROM RequestTypes (Nolock) WHERE (ID = $intID)";
  try {
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($row) && isset($row['description'])) {
      return $row['description'];
    } else {
      return '';
    }
  } catch (PDOException $e) {
    $response_array ['message'] =$e->getMessage();
      logger()->critical('db error', (array) $e);
    }
}

/**
 * Description :GetStaffCanRequestType
 * @param :$intTypeID int,
 * return []
 *
 */
function GetStaffCanRequestType($intTypeID) {
  $arrStaff=[];
  try {
    $pdo = OpenDBLinkA7();
    $strQuery = "exec [dbo].[usp_get_StaffCanRequestType] ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1,$intTypeID, PDO::PARAM_INT);
    $stmt->execute();
    $rsStaff = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    logger()->critical('db error', (array) $e);
  }
  if (!empty($rsStaff)) {
    foreach ($rsStaff as $row) {
      $arrStaff[strtolower($row['Login'])] = $row['Name'];
    }
  }
  return $arrStaff;
}
/**
 * Description: GetGroupIDFromRequestTypeID
 * @param : $intTypeID int
 * return int
 */

function GetGroupIDFromRequestTypeID ($intTypeID) {
  $pdo = OpenDBLinkA7();
  try {
   // Get request group....
    $strQuery = "SELECT GroupID FROM RequestTypes WHERE (ID = :intTypeID)";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindValue(':intTypeID', $intTypeID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($row) && isset($row['GroupID'])) {
      return($row['GroupID']);
    } else {
      return 0;
    }
  } catch (PDOException $e) {
    logger()->critical('DB error', (array)$e);
    }
}

/**
 * Description : GetRequestClosedDays  data by Gorup ID
 * @param : $intGroupID int
 * return []
 */
function GetRequestClosedDays($intGroupID) {
  $pdo = OpenDBLinkA7();
  $edate = date("Y")."-01-01";
  $arrClosed =[];
  $rsClosed  =[];
  try {
    $strQuery ="SELECT startdate, enddate, GroupID, id, type FROM  RequestDatesClosed (nolock)
    WHERE (GroupID = :intGroupID) AND (enddate > :edate) ORDER BY startdate";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(':edate', $edate, PDO::PARAM_STR);
    $stmt->bindParam(':intGroupID', $intGroupID, PDO::PARAM_INT);
    $stmt->execute();
    $rsClosed = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
  if (!empty($rsClosed)) {
    foreach ($rsClosed as $row) {
      $arrClosed[$row['id']]['StartDate'] = date("d/m/Y",strtotime($row['startdate']));
      $arrClosed[$row['id']]['EndDate'] = date("d/m/Y",strtotime($row['enddate']));
      $arrClosed[$row['id']]['ClosureType'] = $row['type'];
    }
  }
  return $arrClosed;
}

/**
 * description : MakeRequestOK
 * @param :$intID int (request ID)
 */

function MakeRequestOK ($intID) {
  $pdo = OpenDBLinkA7();
  $strHistory = '<hr>This Request which was maked as on the waiting list was checked against the availability and was amended to be OK on '.date("d/m/Y").' at '.date("H:i");

  $strQuery = "UPDATE Requests SET isOK = 1, History = CONCAT(ISNULL(History,''), '$strHistory')
               WHERE (ID = $intID)";
  try {
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    return true;
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }
}

/**
 * Description :This function is giving information Yearly Request Count
 * @param : $intGroupID int,
 * @param: $strStartDate str
 * @param :$strUser str
 * return int
 */

function GetYearlyRequestCount($intGroupID, $strStartDate, $strUser) {
  $pdo = OpenDBLinkA7();
  //Count the requests between April 1st and March 31st....
  $intRealYear = date("Y");
  $intYear = date("Y", strtotime("-3 months", strtotime($strStartDate)));
  $intNextYear = $intYear + 1;
  if ($intRealYear == $intYear) {
    $strToday = date("Y-m-d");
    $strQuery = "SELECT  COUNT(Requests.ID) AS CountRequests FROM  RequestTypes (nolock) INNER JOIN Requests (nolock) ON RequestTypes.ID = Requests.RequestType WHERE (RequestTypes.GroupID = $intGroupID) AND (RequestTypes.UniqueCount = 0) AND (Requests.Login = N'$strUser') AND (Requests.Deleted = 0) AND (Requests.Approved = 1) AND (Requests.dDate >= CONVERT(DATETIME, '$intYear-04-01 00:00:00', 102)) AND (Requests.dDate < CONVERT(DATETIME, '$strToday 00:00:00', 102))
    OR (RequestTypes.GroupID = $intGroupID) AND (RequestTypes.UniqueCount = 0) AND (Requests.Login = N'$strUser') AND (Requests.Deleted = 0)
   AND (Requests.dDate >= CONVERT(DATETIME, '$strToday 00:00:00', 102)) AND (Requests.dDate < CONVERT(DATETIME, '$intNextYear-04-01 00:00:00', 102))";
  } else {
    $strQuery = "SELECT COUNT(Requests.ID) AS CountRequests FROM RequestTypes (nolock) INNER JOIN Requests (nolock) ON RequestTypes.ID = Requests.RequestType WHERE (RequestTypes.GroupID = $intGroupID) AND (RequestTypes.UniqueCount = 0) AND (Requests.Login = N'$strUser') AND (Requests.Deleted = 0) AND (Requests.dDate >= CONVERT(DATETIME, '$intYear-04-01 00:00:00', 102)) AND (Requests.dDate < CONVERT(DATETIME, '$intNextYear-04-01 00:00:00', 102))";
  }
  try {
    $stmt = $pdo->prepare($strQuery);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($row['CountRequests'])) {
     return ($row['CountRequests']);
    } else{
      return 0;
    }
  } catch(Exception $e) {
    logger()->critical('DB Error', (array) $e);
   }

}

/**
 * Description : Get All unapproved Request By Login
 * @param :$strUser str
 * return []
 */

function GetRequestsUnapproved ($strUser)
{
    $pdo = OpenDBLinkA7();
    $strToday = date("Y-m-d");
    $arrRequests=[];
    try {
    $strQuery = "exec [dbo].[usp_Get_UnapprovedRequests] ?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1,$strUser, PDO::PARAM_STR);
    $stmt->bindParam(2,$strToday, PDO::PARAM_STR);
    $stmt->execute();
    $rsRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }
  if (!empty($rsRequests )) {
    foreach ($rsRequests as $row) {
      $strDate = date('Y-m-d',strtotime($row['dDate']));
      $intGroupID = $row['GroupID'];
      $intTypeID = $row['TypeID'];
      $intRequestID = $row['ID'];
      $intIsOK  = $row['isOK'];
      $strCreatedDate = date('jS M Y H:i',strtotime($row['Created']));
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['GroupID'] = $intGroupID;
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['GroupDescription'] = $row['GroupDescription'];
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['TypeID'] = $intTypeID;
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['TypeDescription'] = $row['TypeDescription'];
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['Login'] = $row['Login'];
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['FullName'] = $row['FullName'];
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['IsOK'] = $intIsOK ;
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['NotPossible'] = $row['NotPossible'];
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['Unlikely'] = $row['Unlikely'];
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['Comments'] = $row['Comments'];
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['UserComments'] = $row['UserComments'];
      $arrRequests['LeaveRequests']['General'][$strDate][$intRequestID]['Created'] = $strCreatedDate;
    }
  }
  return $arrRequests;
}
// ####
/**
 * Decription : GetAllDailyRequestsBSchedulledPerson
 * @param :$strUser str,
 * @param :$strStartDate str,
 * return []
 */

function GetAllDailyRequestsByScheduledPerson($strUser, $strStartDate) {
  $pdo = OpenDBLinkA7();
  $rsRequests=[];
    try {
      $strQuery = "exec [dbo].[usp_GetDailyRequestsByscheduledPersonId] ?,?";
      $stmt = $pdo->prepare($strQuery);
      $stmt->bindParam(1, $strUser, PDO::PARAM_STR);
      $stmt->bindParam(2, $strStartDate,PDO::PARAM_STR);
      $stmt->execute();
      $rsRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
   } catch (PDOException $e) {
      $response_array ['message'] =$e->getMessage();
      logger()->critical('db error', (array) $e);
    }
  return $rsRequests;
}

/**
 * DESCRIPTION : GetNetloginByscheduledPerson
 * @param :$ScheduledPersonID int
 */

function GetNetloginByscheduledPerson($ScheduledPersonID) {
  $pdo = OpenDBLinkA7();
  try {
    $strQuery = "SELECT UD_DisplayName AS userDisplayName, UD_NetLogin AS NetLogin FROM UserDetails WHERE UD_UserID = ?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $ScheduledPersonID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['NetLogin'] ?? 0;
  } catch (PDOException $e) {
    $response_array ['message'] =$e->getMessage();
      logger()->critical('db error', (array) $e);
    }
  return false;
}

/**
 * DESCRIPTION : this function read allocation and rota by week with masking
 * @param :$intStartWeekNumber int
 * @param :$strUser string
 * @param :$startDate Date
 * @param :$EndDate Date
 * return []
 *
 */

function weeklyRequestAllocationsAndRotaWIhMasking($intStartWeekNumber,$strUser,$startDate,$EndDate)
{
  $pdo = OpenDBLinkA7();
  try {
    $intEndWeekNumber = $intStartWeekNumber;
    $strQuery = "exec [dbo].[usp_fetch_requestweeklyAllocationAndRotawithmasking] ?,?,?,?,?";
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
  if (!empty($result)) {
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
?>
