<?php
/**
 * Description : Leave Credit Summary
 * Return staff leave credit summary based on scheduling team and leave year
 */

function GetLeaveCreditsSummary($intTeamID, $intLeaveYear, $arrAllocLeaveTypes,$activeScheduledPeople=0,$additionalflag=0) {

	try {
		// Open the database
		$pdo = OpenDBLinkA7();
		// Set the statement to use
		$strQuery = "exec [dbo].[usp_get_LeaveCreditsSummary] ?,?,?,?";
		$stmt = $pdo->prepare($strQuery);
		// The parameters
		$stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
		$stmt->bindParam(2, $intLeaveYear, PDO::PARAM_INT);
		$stmt->bindParam(3, $activeScheduledPeople, PDO::PARAM_INT);
		$stmt->bindParam(4, $additionalflag, PDO::PARAM_INT);
		$stmt->execute();
		$rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!empty($rsLeave)){
          foreach ($rsLeave as $row) {
            $arrLeave[$row['ScheduledPersonID']]['Name'] = $row['FullName'];
            $arrLeave[$row['ScheduledPersonID']]['EFT'] = number_format((float)$row['EFT'], 3, '.', '');
            $arrLeave[$row['ScheduledPersonID']]['SortCode'] = $row['SortCode'];
            $arrLeave[$row['ScheduledPersonID']]['Login'] = $row['NetLogin'];
            $arrLeave[$row['ScheduledPersonID']]['ScheduledPersonID']=$row['ScheduledPersonID'];
            $arrLeave[$row['ScheduledPersonID']]['StaffNumber']=$row['StaffNumber'];
            foreach ($arrAllocLeaveTypes as $intTypeID => $arrAllocLeaveType) {
              if (isset($row[$arrAllocLeaveType['AllocName']])) {
                $arrLeave[$row['ScheduledPersonID']]['Leave'][$arrAllocLeaveType['AllocName']] = $row[$arrAllocLeaveType['AllocName']];
              }
            }
          }
    } else {
      $arrLeave = array();
    }

		if (isset($arrLeave)) {
			return ($arrLeave);
		}
	} catch (PDOException $e) {
			logger()->critical('db error', (array) $e);
	}
}

/**
 * Description : add carry over leave from prev year to current leav year
 * @param : $carryoverleave,$staffnumber,$leaveYear,$intTeamID
 * return : result array
 */
function addCarryOverLeave($carryoverleave,$staffnumber,$leaveYear,$intTeamID,$currentusername,$currentuserid){
  try {

      // Open the database
		  $pdo = OpenDBLinkA7();
      $query = "exec  [dbo].[usp_add_CarryOverLeave] ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
      $currentleaveyear  = $leaveYear;
      $prevleaveyear = $leaveYear-1;
      $comment1 = 'Carry over leave to '.$currentleaveyear;
      $comment2 = 'Carry over leave from '.$prevleaveyear;
      $strDatenow = date("jS M Y");
      $stmt = $pdo->prepare($query);
      $stmt->bindValue(1, $staffnumber, PDO::PARAM_STR);
      $stmt->bindValue(2, $leaveYear, PDO::PARAM_INT);
      $stmt->bindValue(3, $carryoverleave['ScheduledPersonID'], PDO::PARAM_INT);
      $stmt->bindValue(4,$carryoverleave['Annual']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(5,$carryoverleave['Annual']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(6,$carryoverleave['PHL']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(7,$carryoverleave['PHL']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(8,$carryoverleave['Comp']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(9,$carryoverleave['Comp']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(10,$carryoverleave['Under11TOIL']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(11,$carryoverleave['Under11TOIL']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(12,$carryoverleave['Over12TOIL']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(13,$carryoverleave['Over12TOIL']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(14,$carryoverleave['Additional']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(15,$carryoverleave['Additional']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(16,$carryoverleave['Exceptional']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(17,$carryoverleave['Exceptional']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(18,$carryoverleave['Casual']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(19,$carryoverleave['Casual']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(20,$carryoverleave['Other']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(21,$carryoverleave['Other']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(22,$carryoverleave['LongService']['prevyear'], PDO::PARAM_STR);
      $stmt->bindValue(23,$carryoverleave['LongService']['nextyear'], PDO::PARAM_STR);
      $stmt->bindValue(24, $intTeamID, PDO::PARAM_INT);
      $stmt->bindValue(25,$comment1, PDO::PARAM_STR);
      $stmt->bindValue(26,$comment2, PDO::PARAM_STR);
      $stmt->bindValue(27,$currentuserid, PDO::PARAM_INT);
      $stmt->bindValue(28,$currentusername, PDO::PARAM_STR);
      $stmt->bindValue(29,$strDatenow, PDO::PARAM_STR);

      $stmt->execute();
      $result =   $stmt->fetchAll(PDO::FETCH_ASSOC);
  }catch(Exception $e){
      logger()->critical('DB Error', (array) $e);
  }
  return false;
}

/**
 * Description :fetch eft for staff by team
 * @param : $intTeamID,$scheduledPersonID
 * return : result array
 */
function getEFTByTeamByStaff($intTeamID,$scheduledPersonID){
  try{
    $pdo = OpenDBLinkA7();
  $strQuery = "exec [dbo].[usp_get_EFTByTeamByStaffNumber] ?,?";
    $stmt = $pdo->prepare($strQuery);
    $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
    $stmt->bindParam(2, $scheduledPersonID, PDO::PARAM_INT);
    $stmt->execute();
    $rsEFT = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $rsEFT;
  }catch(Exception $e){
    print_r($e);exit();
    logger()->critical('DB Error', (array) $e);
  }
  return false;
}

/**
 * Description : Fetch PHl Start Date
 * return string
 *
 */
function getPHLStartDate()
  {
    $pdo = OpenDBLinkA7();
    try {
      $strQuery="SELECT PHLExpiryStartDate FROM PHLExpiryStartDate";
      $stmt = $pdo->prepare($strQuery);
      $stmt->execute();
      $PHLStartDateArray = $stmt->fetch(PDO::FETCH_ASSOC);
      return $PHLStartDateArray['PHLExpiryStartDate'];
    } catch(Exception $e) {
      logger()->critical('DB Error', (array) $e);
    }
    return false;
  }

/**
 * Decription: getAllExpiredPHL
 * @param : PHLStartDate STRING
 * @param : PHLEndDate STRING
 * @param : TeamID int
 * return [];
 */
function getAllExpirdPHL($PHLStartDate,$EndNoofWeeks,$TeamID)
  {
   $pdo = OpenDBLinkA7();
     try {
          $strQuery = "exec [dbo].[usp_get_ExpiredPHL] :PHLStartDate,:EndNoofWeeks,:TeamID";
          $stmt = $pdo->prepare($strQuery);
          $stmt->bindParam(':PHLStartDate', $PHLStartDate, PDO::PARAM_STR);
          $stmt->bindParam(':EndNoofWeeks', $EndNoofWeeks, PDO::PARAM_STR);
          $stmt->bindParam(':TeamID', $TeamID, PDO::PARAM_INT);
          $stmt->execute();
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) {
          logger()->critical('DB Error', (array) $e);
        }

    return [];
  }


  /**
 * Description : Leave Taken  Summary
 * Return staff leave taken summary based on scheduling team and leave year
 */

function GetLeaveApprovedTakenSummary ($intTeamID, $intLeaveYear, $arrAllocLeaveTypes) {

  $pdo = OpenDBLinkA7();
  // Get the Breaks ID for this department

  $startingdate = $intLeaveYear.'-04-01 00:00:00';
 $nextyear = $intLeaveYear+1;
  $enddate = $nextyear.'-03-31 00:00:00';

  //Allocate Leave
  $sql = "exec [dbo].[usp_get_ApprovedLeaveTakenSummary] ?,?,?";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
  $stmt->bindParam(2, $startingdate, PDO::PARAM_STR);
  $stmt->bindParam(3, $enddate, PDO::PARAM_STR);
  $stmt->execute();
  $rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($rsLeave as $row){
    foreach ($arrAllocLeaveTypes as $intTypeID => $arrAllocLeaveType) {
      if (isset($row[$arrAllocLeaveType['AllocName']])) {
        $arrLeave[$row['SchedulingPersonID']][$intTypeID] = $row[$arrAllocLeaveType['AllocName']];
      }
    }
  }

  if (isset($arrLeave)) {
    return ($arrLeave);
  }
}

/**
 * Description : Leave carry over
 * Return staff leave carry over data
 */

function GetCarryOverLeaveData($intTeamID, $intLeaveYear, $arrAllocLeaveTypes) {

	try {
    $prevyear = $nextyear = '';
    $arrLeave = array();
		// Open the database
		$pdo = OpenDBLinkA7();
		// Set the statement to use
		$strQuery = "exec [dbo].[usp_get_CarryOverData] ?,?";
		$stmt = $pdo->prepare($strQuery);
		// The parameters
		$stmt->bindParam(1, $intTeamID, PDO::PARAM_INT);
		$stmt->bindParam(2, $intLeaveYear, PDO::PARAM_INT);
		$stmt->execute();
		$rsLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if(!empty($rsLeave)){
          foreach($rsLeave as $row) {
              $arrLeave[$row['StaffNumber']]['ScheduledPersonID']=$row['UD_UserID'];
              foreach ($arrAllocLeaveTypes as $intTypeID => $arrAllocLeaveType) {
                  if (isset($row[$arrAllocLeaveType['AllocName'].'_prevyear'])) {
                    $prevyear =  $row[$arrAllocLeaveType['AllocName'].'_prevyear'] != 0 ? number_format((float)$row[$arrAllocLeaveType['AllocName'].'_prevyear'], 2, '.', '') : NULL ;
                    $nextyear =  $row[$arrAllocLeaveType['AllocName'].'_nextyear'] != 0 ? number_format((float)$row[$arrAllocLeaveType['AllocName'].'_nextyear'], 2, '.', '') : NULL ;

                    $arrLeave[$row['StaffNumber']][$arrAllocLeaveType['AllocName']]['prevyear'] =  $prevyear;
                    $arrLeave[$row['StaffNumber']][$arrAllocLeaveType['AllocName']]['nextyear'] = $nextyear;
                  }
              }
          }
    }
		if (isset($arrLeave)) {
			return ($arrLeave);
		}
	} catch (PDOException $e) {
			logger()->critical('db error', (array) $e);
	}
}

function customRoundOff($input)
{
  $roundOffArray = [0, 250, 500, 750, 1000];
  $strInput = strval($input);
  $strInput = number_format($strInput, 2, '.', '');
  $splitString = explode('.', $strInput);
  $splitByDot = array_map('intval', $splitString);
  $intPart = $splitByDot[0];
  $intDecimalPart = $splitByDot[1];
  if ($intDecimalPart < 10 && strlen($splitString[1]) == 1) {
    $intDecimalPart *= 100;
  } elseif ($intDecimalPart < 100) {
    $intDecimalPart *= 10;
  }
  $pos = 0;
  foreach ($roundOffArray as $roundOff) {
    if ($intDecimalPart <= $roundOff) {
      break;
    }
    $pos++;
  }
  if ($pos == 0) {
    return $input;
  }
  $previousRoundOff = $roundOffArray[$pos - 1];
  $currentRoundOffValue = $roundOffArray[$pos];

  if ($input>0){
	  if ($pos % 2 == 0) {
		if ($intDecimalPart - $previousRoundOff < 100 && $currentRoundOffValue - $intDecimalPart >= 210) {
		  return (float) $intPart + ($previousRoundOff / 1000.0);
		} else {
		  return (float) $intPart + ($currentRoundOffValue / 1000.0);
		}
	  } else {
		if ($intDecimalPart - $previousRoundOff < 100 && $currentRoundOffValue - $intDecimalPart >= 160) {
		  return (float) $intPart + ($previousRoundOff / 1000.0);
		} else {
		  return (float) $intPart + ($currentRoundOffValue / 1000.0);
		}
	  }
  } else {
	  if ($pos % 2 == 0) {
		if ($intDecimalPart - $previousRoundOff < 100 && $currentRoundOffValue - $intDecimalPart >= 210) {
		  return (float) $intPart - ($previousRoundOff / 1000.0);
		} else {
		  return (float) $intPart - ($currentRoundOffValue / 1000.0);
		}
	  } else {
		if ($intDecimalPart - $previousRoundOff < 100 && $currentRoundOffValue - $intDecimalPart >= 160) {
		  return (float) $intPart - ($previousRoundOff / 1000.0);
		} else {
		  return (float) $intPart - ($currentRoundOffValue / 1000.0);
		}
	  }
  }
}