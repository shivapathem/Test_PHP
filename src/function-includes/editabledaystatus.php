<?php
function DatIsEditable($strDate, $intTeamID, $arrTeamDefaults) {
  date_default_timezone_set('Europe/London');
  $currdotw = date("w", strtotime($strDate));
  $starttime = $arrTeamDefaults[$intTeamID]['DailyEditStart'] ?? null;
  $endtime = isset($arrTeamDefaults[$intTeamID]['DailyEditEnd']) ? gmdate($arrTeamDefaults[$intTeamID]['DailyEditEnd']) : null;
  $editperiod = $arrTeamDefaults[$intTeamID]['DailyEditPeriod'] ?? null;
  $intOnlyWeekends = $arrTeamDefaults[$intTeamID]['DailyAutoWeekend'] ?? null;
  $intAutoLockToday = $arrTeamDefaults[$intTeamID]['AutoLockToday'] ?? null;
  $numberofDaysAllowedEditing = $arrTeamDefaults[$intTeamID]['numberofDaysAllowedEditing'] ?? null;
  $curtime =timetoseconds(date("H:i"));

   $startdate = strtotime($strDate);
   $enddate = strtotime("+".($numberofDaysAllowedEditing)." days");
   if ($intAutoLockToday == 1 || $intOnlyWeekends == 1 ) {
	  $enddate = strtotime("+ 1 days",strtotime($strDate));
    }


  // Find out if the day is on a timer
  $intIsOpen = GetIsOpen ($strDate, $intTeamID);
  switch ($intIsOpen) {
    case 0;                                      // #######################################  Locked
      $arret[0] = 0;
      $arret[1] = 'locked.png';
      break;
    case 1:                                     // #######################################  Unlocked
      $arret[0] = 1;
      $arret[1] = 'unlocked.png';
    break;

    case 2:
      if ($editperiod == 0) {
        $arret[0] = 0;
        $arret[1] = 'locked.png';
      }
      else {

      // and finally open days upto the editable period
		$editabledays= array();

		for ($idate = $startdate; $idate < $enddate; $idate = $idate + 86400) {
			$editabledays[date("Y-m-d", $idate)] = 1;

			$tomorrow =$idate + 86400;
			if (($starttime > $endtime) && ((($curtime > $starttime) || ($curtime < $endtime)))){
				$editabledays[date("Y-m-d", $tomorrow)] = 1;
			}

        }

		// Now depending on the day of the week.....
        switch ($currdotw) {
          case 6:                                     //Saturday
            // set Friday, Saturday, Sunday and Monday to editable
            $startdate = strtotime("-1 days",strtotime($strDate));
            $enddate = strtotime("+2 days",strtotime($strDate));
            // set Friday, Saturday, Sunday and Monday to editable
            for ($idate = $startdate; $idate <= $enddate; $idate = $idate + 86400) {
				if (($curtime > $starttime) && ($curtime < $endtime)) {
					$editabledays[date("Y-m-d", $idate)] = 1;
				}
            }
            break;
          case 0:                                     //Sunday
            $startdate = strtotime("-2 days",strtotime($strDate));
            $enddate = strtotime("+1 day",strtotime($strDate));
            // set Friday, Saturday, Sunday and Monday to editable
            for ($idate = $startdate; $idate <= $enddate; $idate = $idate + 86400) {
              $editabledays[date("Y-m-d", $idate)] = 1;
            }
            break;

          case 1:                                     // Monday
            // set Friday, Saturday, Sunday and Monday to editable if the time is before the day is locaked
           $startdate = strtotime("-3 days",strtotime($strDate));
           $enddate = strtotime($strDate);
           if ($curtime < $endtime) {
             // set Friday, Saturday, Sunday and Monday to editable
             for ($idate = $startdate; $idate <= $enddate; $idate = $idate + 86400) {
               $editabledays[date("Y-m-d", $idate)] = 1;
             }
            }
          break;

        case 5:                                     // Friday
          // set Friday, Saturday, Sunday and Monday to editable if the time is after the time it's open
         $startdate = strtotime($strDate);
         $enddate = strtotime("+3 days",strtotime($strDate));
         if ($curtime > $starttime) {
           // set Friday, Saturday, Sunday and Monday to editable
           for ($idate = $startdate; $idate <= $enddate; $idate = $idate + 86400) {
             $editabledays[date("Y-m-d", $idate)] = 1;
           }
        }
        break;

      default:                                     // Every other day
          // set Tomorrowto editable if the time is after the time it's open
         $startdate = strtotime($strDate);
         $enddate = strtotime("+1 days",strtotime($strDate));
         //if (strtotime(date("H:i")) > strtotime($starttime) || strtotime(date("H:i")) < strtotime($endtime)) {
         if ($curtime > $starttime) {
           // set Friday, Saturday, Sunday and Monday to editable
           for ($idate = $startdate; $idate <= $enddate; $idate = $idate + 86400) {
             $editabledays[date("Y-m-d", $idate)] = 1;
           }
        }
        break;
      }

      // Check yesterday and open it if it's before the lock time of the day
      if ($curtime < $endtime) {
        $yesterday = strtotime("-1 day");
        $editabledays[date("Y-m-d", $yesterday)] = 1;
      }
      // Check tomorrow and open it if it's after the lock time of the day
      if ($curtime > $starttime) {
        $tomorrow = strtotime("1 day");
        $editabledays[date("Y-m-d", $tomorrow)] = 1;
      }

      // and finally open days upto the editable period
      $startdate = strtotime("now");
      $enddate = strtotime("+".($editperiod - 1)." days");
      for ($idate = $startdate; $idate <= $enddate; $idate = $idate + 86400) {
        $editabledays[date("Y-m-d", $idate)] = 1;
      }

      // Check today and if $intAutoLockToday is set don't mark as editable depending on times....
	  if ($intAutoLockToday == 1 && date("N") < 6) {
	   if (($curtime >= $starttime) || ($curtime <= $endtime)) {
          $editabledays[$strDate] = 1;
        } else {
		  $editabledays[$strDate] = 0;
		}
      }
	  $intDayFromDate = date("w", strtotime($strDate));

	 if ($intOnlyWeekends == 1 && ( $intDayFromDate>1 &&  $intDayFromDate<5)) {
        $arret[0] = 0;
        $arret[1] = 'locked.png';
      }
	  else {

	    if (isset($editabledays[$strDate])) {
          if ($editabledays[$strDate] == 1) {
            $arret[0] = 1;
            $arret[1] = 'unlocked_timer.png';
          }
          else {
            $arret[0] = 0;
            $arret[1] = 'locked_timer.png';
          }
        }
        else {
          $arret[0] = 0;
          $arret[1] = 'locked_timer.png';
        }
      }
    }
  }

  return ($arret);
}

/* This funcion is checking lock release condition
The status is:
   0 = Locked
   1 = Relaesed
   2 = Timer
*/
function GetIsOpen ($strDate, $intTeamId) {
  $today = date("Y-m-d");

  $pdo = OpenDBLinkA7();
  try {
	    $query = "exec [dbo].[usp_get_LockRealeasedays] ?,?";
        $stmt = $pdo->prepare($query);
		$stmt->bindParam(1, $intTeamId, PDO::PARAM_INT);
		$stmt->bindParam(2, $strDate, PDO::PARAM_STR);
		$stmt->execute();
		$rsReleased = $stmt->fetch(PDO::FETCH_ASSOC);
		if(empty($rsReleased)) {
		// done on a timer.....
			$status = 2;
		} else {
		    $status = $rsReleased['status'];
		}
	} catch (PDOException $e) {
        logger()->critical('DB error', (array)$e);
    }
   return ($status);
}

?>