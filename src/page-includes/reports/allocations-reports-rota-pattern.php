<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/rota_functions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intDepartmentID = $_REQUEST["departmentid"];
$ScheduledPersonID = isset($_REQUEST["ScheduledPersonID"]) ? $_REQUEST["ScheduledPersonID"] : '';


$arrStaffInDepartment = GetStaffInDepartmentWithRota($intDepartmentID);
if (isset($arrStaffInDepartment))
{

  $strStaffNumber = $_REQUEST["staffnumber"];
  $arrEFT = GetNameAndEFTFromStaffNumber($ScheduledPersonID, $intDepartmentID);

  $intCurWeek = bbcweeknumber(date("Y-m-d"));

  $arrRotaPattern = ReadRotaPattern ($ScheduledPersonID);

  echo '<table class="tablesmall" width="100%">';
  echo '<tr height="50px">'; 
  echo '<td width="600px" nowrap class="tableheadersmall medtextbold">Underlying Rota Pattern for &nbsp;&nbsp;';
  echo '<select class="chosen-select" name="department" onchange="javascript:ShowRotasIndividual('.$intDepartmentID.', 0, value);">';
    foreach ($arrStaffInDepartment as $strSN => $strName) {
      if ($ScheduledPersonID == $strSN) {
        echo '<option selected value="'.$strSN.'">'.$strName.'</option>';
      }
      else {
        echo '<option value="'.$strSN.'">'.$strName.'</option>';
      }  
    }
    echo '</select>';
   
    echo '&nbsp;&nbsp;who has an EFT of '.round((float)$arrEFT['EFT'], 3).'<br>';    
    echo '</td>';    
    
    
  echo '</tr>';   
  echo '</table>';
  echo '<br>';
  if (count($arrRotaPattern) > 0) {
	foreach ($arrRotaPattern  as $intWeek => $arrRotaWeek) {
    	$intWeeksInRota = $arrRotaPattern[$intWeek]['WeeksInRota'];
    	$intRotaStarts = $arrRotaPattern[$intWeek]['RotaStartWeek'];
    	$intWeekOfRota = $arrRotaPattern[$intWeek]['CurrentWeekInRota'];
		break;
	}
	// $intLoopWeek = $intCurWeek - ($arrRotaPattern[1]['CurrentWeekInRota']-1);
    $currWeekYearOnly = substr($intCurWeek, 0, 4);
  	$currWeekNumOnly = substr($intCurWeek, 4, 2);
    $intLoopWeek = ((int) $currWeekNumOnly) - ($intWeekOfRota - 1);
    if(strpos($intLoopWeek,'-') !== false){
    	$startweeknumforrep = 52 + $intLoopWeek;
    	$startweekyearforrep = ((int) $currWeekYearOnly - 1);
    	$intLoopWeek = $startweekyearforrep.$startweeknumforrep;
    } else {
    	if($intLoopWeek == 0){
    		$startweekyearforrep = ((int) $currWeekYearOnly - 1);
    		$getYearLastWeekNumber = getYearLastWeekNumber($startweekyearforrep);
    		$intLoopWeek = $startweekyearforrep.$getYearLastWeekNumber['maxweek'];
    	} else if(($intLoopWeek > 0) && ($intLoopWeek < 10)){
    		$intLoopWeek = $currWeekYearOnly.'0'.$intLoopWeek;
    	} else {
    		$intLoopWeek = $currWeekYearOnly.$intLoopWeek;
    	}
    }

	$strEffDateArr  =   explode('/', date("d/m/Y"));
    $dateNewFormate = $strEffDateArr['0'] . '-'. $strEffDateArr['1'] . '-' . $strEffDateArr['2'];
    $firstSatDate = date('d/m/Y', strtotime("first saturday of $dateNewFormate"));
    if(strtotime($dateNewFormate) < strtotime(str_replace('/','-',$firstSatDate)))
    {
        $dateNewFormate = date('d-m-Y', strtotime("-1 week $dateNewFormate"));
        $firstSatDate = date('d/m/Y', strtotime("first saturday of $dateNewFormate"));
    }
	$personRota = json_decode(GetRotasByPerson($ScheduledPersonID), true);
	$rsRotaDetailsJson = GetRotaDutiesDetails($personRota[0]['RotaID'] ?? '', $firstSatDate);    
    $rsRotaDetails = json_decode($rsRotaDetailsJson, true);
	
	$intFirstWeek = $rsRotaDetails['RotaStartWeek'];
    $intTotalDuration = 0;
    $intCountDays = 0;
    $intCountNights = 0;
    $intTotalAppearence = 0;
    
    $strHTML = '<tr>';       
    $strHTML.= '<th colspan="2">Week</th>';
    $strHTML.= '<th colspan="3">Saturday</th>';
    $strHTML.= '<th colspan="3">Sunday</th>';
    $strHTML.= '<th colspan="3">Monday</th>';
    $strHTML.= '<th colspan="3">Tuesday</th>';
    $strHTML.= '<th colspan="3">Wednesday</th>';
    $strHTML.= '<th colspan="3">Thursday</th>';
    $strHTML.= '<th colspan="3">Friday</th>';
    $strHTML.= '<th width="75px">Total Hours</th>';
    $strHTML.= '</tr>';
	ksort($arrRotaPattern);
    foreach ($arrRotaPattern  as $intWeek => $arrRotaWeek) {
		$intLoopWeek = addweeks($intFirstWeek, $intWeek - 1);
      if ($intLoopWeek == $intCurWeek) {
        $strHTML.= '<tr class="DarkGrey">';
      }
      else {
        $strHTML.= '<tr>';
      }
	  $intWeekDurationNew = 0;
	  $arrRotaWeek['Saturday']['Shift'] = isset($arrRotaWeek['Saturday']['Shift']) ? $arrRotaWeek['Saturday']['Shift'] : '';
	  $arrRotaWeek['Sunday']['Shift'] = isset($arrRotaWeek['Sunday']['Shift']) ? $arrRotaWeek['Sunday']['Shift'] : '';
	  $arrRotaWeek['Monday']['Shift'] = isset($arrRotaWeek['Monday']['Shift']) ? $arrRotaWeek['Monday']['Shift'] : '';
	  $arrRotaWeek['Tuesday']['Shift'] = isset($arrRotaWeek['Tuesday']['Shift']) ? $arrRotaWeek['Tuesday']['Shift'] : '';
	  $arrRotaWeek['Wednesday']['Shift'] = isset($arrRotaWeek['Wednesday']['Shift']) ? $arrRotaWeek['Wednesday']['Shift'] : '';
	  $arrRotaWeek['Thursday']['Shift'] = isset($arrRotaWeek['Thursday']['Shift']) ? $arrRotaWeek['Thursday']['Shift'] : '';
	  $arrRotaWeek['Friday']['Shift'] = isset($arrRotaWeek['Friday']['Shift']) ? $arrRotaWeek['Friday']['Shift'] : '';
	  $arrRotaWeek['Saturday']['DutyName'] = isset($arrRotaWeek['Saturday']['DutyName']) ? $arrRotaWeek['Saturday']['DutyName'] : '';
	  $arrRotaWeek['Saturday']['Duration'] = isset($arrRotaWeek['Saturday']['Duration']) ? $arrRotaWeek['Saturday']['Duration'] : 0;
	  $arrRotaWeek['Sunday']['DutyName'] = isset($arrRotaWeek['Sunday']['DutyName']) ? $arrRotaWeek['Sunday']['DutyName'] : '';
	  $arrRotaWeek['Sunday']['Duration'] = isset($arrRotaWeek['Sunday']['Duration']) ? $arrRotaWeek['Sunday']['Duration'] : 0;
	  $arrRotaWeek['Monday']['DutyName'] = isset($arrRotaWeek['Monday']['DutyName']) ? $arrRotaWeek['Monday']['DutyName'] : '';
	  $arrRotaWeek['Monday']['Duration'] = isset($arrRotaWeek['Monday']['Duration']) ? $arrRotaWeek['Monday']['Duration'] : 0;
	  $arrRotaWeek['Tuesday']['DutyName'] = isset($arrRotaWeek['Tuesday']['DutyName']) ? $arrRotaWeek['Tuesday']['DutyName'] : '';
	  $arrRotaWeek['Tuesday']['Duration'] = isset($arrRotaWeek['Tuesday']['Duration']) ? $arrRotaWeek['Tuesday']['Duration'] : 0;
	  $arrRotaWeek['Wednesday']['DutyName'] = isset($arrRotaWeek['Wednesday']['DutyName']) ? $arrRotaWeek['Wednesday']['DutyName'] : '';
	  $arrRotaWeek['Wednesday']['Duration'] = isset($arrRotaWeek['Wednesday']['Duration']) ? $arrRotaWeek['Wednesday']['Duration'] : 0;
	  $arrRotaWeek['Thursday']['DutyName'] = isset($arrRotaWeek['Thursday']['DutyName']) ? $arrRotaWeek['Thursday']['DutyName'] : '';
	  $arrRotaWeek['Thursday']['Duration'] =isset($arrRotaWeek['Thursday']['Duration']) ? $arrRotaWeek['Thursday']['Duration'] : 0;
	  $arrRotaWeek['Friday']['DutyName'] = isset($arrRotaWeek['Friday']['DutyName']) ? $arrRotaWeek['Friday']['DutyName'] : '';
	  $arrRotaWeek['Friday']['Duration'] = isset($arrRotaWeek['Friday']['Duration']) ? $arrRotaWeek['Friday']['Duration'] : 0;
      $strHTML.= '<td width="100px">'.$intWeek.'</td>';
      $strHTML.= '<td width="200px">'.spinweek($intLoopWeek).'</td>';
		$intWeekDuration = number_format(((is_array($arrRotaWeek['Saturday']['Duration']) ? array_sum(array_map('floatval', $arrRotaWeek['Saturday']['Duration'])) : (float)$arrRotaWeek['Saturday']['Duration']) + (is_array($arrRotaWeek['Sunday']['Duration']) ? array_sum(array_map('floatval', $arrRotaWeek['Sunday']['Duration'])) : (float)$arrRotaWeek['Sunday']['Duration']) + (is_array($arrRotaWeek['Monday']['Duration']) ? array_sum(array_map('floatval', $arrRotaWeek['Monday']['Duration'])) : (float)$arrRotaWeek['Monday']['Duration']) + (is_array($arrRotaWeek['Tuesday']['Duration']) ? array_sum(array_map('floatval', $arrRotaWeek['Tuesday']['Duration'])) : (float)$arrRotaWeek['Tuesday']['Duration']) + (is_array($arrRotaWeek['Wednesday']['Duration']) ? array_sum(array_map('floatval', $arrRotaWeek['Wednesday']['Duration'])) : (float)$arrRotaWeek['Wednesday']['Duration']) + (is_array($arrRotaWeek['Thursday']['Duration']) ? array_sum(array_map('floatval', $arrRotaWeek['Thursday']['Duration'])) : (float)$arrRotaWeek['Thursday']['Duration']) + (is_array($arrRotaWeek['Friday']['Duration']) ? array_sum(array_map('floatval', $arrRotaWeek['Friday']['Duration'])) : (float)$arrRotaWeek['Friday']['Duration'])) / 3600, 2, '.', '');
	  
		if (isset($arrRotaWeek['Saturday']['Shift']) && is_array($arrRotaWeek['Saturday']['Shift']) && !empty($arrRotaWeek['Saturday']['Shift']) && $arrRotaWeek['Saturday']['Shift'][0] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Sunday']['Shift']) && is_array($arrRotaWeek['Sunday']['Shift']) && !empty($arrRotaWeek['Sunday']['Shift']) && $arrRotaWeek['Sunday']['Shift'][0] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Monday']['Shift']) && is_array($arrRotaWeek['Monday']['Shift']) && !empty($arrRotaWeek['Monday']['Shift']) && $arrRotaWeek['Monday']['Shift'][0] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Tuesday']['Shift']) && is_array($arrRotaWeek['Tuesday']['Shift']) && !empty($arrRotaWeek['Tuesday']['Shift']) && $arrRotaWeek['Tuesday']['Shift'][0] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Wednesday']['Shift']) && is_array($arrRotaWeek['Wednesday']['Shift']) && !empty($arrRotaWeek['Wednesday']['Shift']) && $arrRotaWeek['Wednesday']['Shift'][0] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Thursday']['Shift']) && is_array($arrRotaWeek['Thursday']['Shift']) && !empty($arrRotaWeek['Thursday']['Shift']) && $arrRotaWeek['Thursday']['Shift'][0] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Friday']['Shift']) && is_array($arrRotaWeek['Friday']['Shift']) && !empty($arrRotaWeek['Friday']['Shift']) && $arrRotaWeek['Friday']['Shift'][0] === 'D')
			$intCountDays++;
	  
		if (isset($arrRotaWeek['Saturday']['Shift'][1]) && $arrRotaWeek['Saturday']['Shift'][1] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Sunday']['Shift'][1]) && $arrRotaWeek['Sunday']['Shift'][1] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Monday']['Shift'][1]) && $arrRotaWeek['Monday']['Shift'][1] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Tuesday']['Shift'][1]) && $arrRotaWeek['Tuesday']['Shift'][1] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Wednesday']['Shift'][1]) && $arrRotaWeek['Wednesday']['Shift'][1] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Thursday']['Shift'][1]) && $arrRotaWeek['Thursday']['Shift'][1] === 'D')
			$intCountDays++;
		if (isset($arrRotaWeek['Friday']['Shift'][1]) && $arrRotaWeek['Friday']['Shift'][1] === 'D')
			$intCountDays++;

		if (isset($arrRotaWeek['Saturday']['Shift']) && is_array($arrRotaWeek['Saturday']['Shift']) && !empty($arrRotaWeek['Saturday']['Shift']) && $arrRotaWeek['Saturday']['Shift'][0] == 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Sunday']['Shift']) && is_array($arrRotaWeek['Sunday']['Shift']) && !empty($arrRotaWeek['Sunday']['Shift']) && $arrRotaWeek['Sunday']['Shift'][0] == 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Monday']['Shift']) && is_array($arrRotaWeek['Monday']['Shift']) && !empty($arrRotaWeek['Monday']['Shift']) && $arrRotaWeek['Monday']['Shift'][0] == 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Tuesday']['Shift']) && is_array($arrRotaWeek['Tuesday']['Shift']) && !empty($arrRotaWeek['Tuesday']['Shift']) && $arrRotaWeek['Tuesday']['Shift'][0] == 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Wednesday']['Shift']) && is_array($arrRotaWeek['Wednesday']['Shift']) && !empty($arrRotaWeek['Wednesday']['Shift']) && $arrRotaWeek['Wednesday']['Shift'][0] == 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Thursday']['Shift']) && is_array($arrRotaWeek['Thursday']['Shift']) && !empty($arrRotaWeek['Thursday']['Shift']) && $arrRotaWeek['Thursday']['Shift'][0] == 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Friday']['Shift']) && is_array($arrRotaWeek['Friday']['Shift']) && !empty($arrRotaWeek['Friday']['Shift']) && $arrRotaWeek['Friday']['Shift'][0] == 'N')
			$intCountNights++;

		if (isset($arrRotaWeek['Saturday']['Shift'][1]) && $arrRotaWeek['Saturday']['Shift'][1] === 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Sunday']['Shift'][1]) && $arrRotaWeek['Sunday']['Shift'][1] === 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Monday']['Shift'][1]) && $arrRotaWeek['Monday']['Shift'][1] === 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Tuesday']['Shift'][1]) && $arrRotaWeek['Tuesday']['Shift'][1] === 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Wednesday']['Shift'][1]) && $arrRotaWeek['Wednesday']['Shift'][1] === 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Thursday']['Shift'][1]) && $arrRotaWeek['Thursday']['Shift'][1] === 'N')
			$intCountNights++;
		if (isset($arrRotaWeek['Friday']['Shift'][1]) && $arrRotaWeek['Friday']['Shift'][1] === 'N')
			$intCountNights++;
	  
		$intTotalAppearence = $arrRotaWeek['totalWorkingRecord1'];
	  
	  //for sat
	  if(!empty($arrRotaWeek['Saturday']['Shift'][1]))
	  {
		  $satTempDutyNameStr = $arrRotaWeek['Saturday']['DutyName'][1];
		  $satTempDurationStr = number_format(($arrRotaWeek['Saturday']['Duration'][1]/3600), 2, '.', '');
		  $satTempShiftStr = $arrRotaWeek['Saturday']['Shift'][1];
		  $intWeekDurationNew += number_format(($arrRotaWeek['Saturday']['Duration'][1]/3600), 2, '.', '');
	  }
	  else
	  {
		$saturdayData = $arrRotaWeek['Saturday'] ?? [];
		$dutyName = is_array($saturdayData) ? ($saturdayData['DutyName'][0] ?? '') : '';
		$duration = is_array($saturdayData) ? ($saturdayData['Duration'][0] ?? 0) : 0;
		$shift = is_array($saturdayData) ? ($saturdayData['Shift'][0] ?? '') : '';

		$satTempDutyNameStr = is_string($dutyName) ? $dutyName : '';
		$satTempDurationStr = is_numeric($duration) ? number_format(($duration/3600), 2, '.', '') : '0.00';
		$satTempShiftStr = is_string($shift) || is_numeric($shift) ? (string)$shift : '';

		if (is_numeric($duration) && $duration > 0) {
			$intWeekDurationNew += number_format(($duration/3600), 2, '.', '');
		}
	  }
      $strHTML.= '<td width="150px">'. $satTempDutyNameStr . '</td>';
	  $strHTML.= '<td width="50px">'. $satTempDurationStr .'</td>';
	  $strHTML.= '<td align="center" width="50px">'. $satTempShiftStr .'</td>';
	  //for sun
	  if(!empty($arrRotaWeek['Sunday']['Shift'][1]))
	  {
		  $sunTempDutyNameStr = $arrRotaWeek['Sunday']['DutyName'][1];
		  $sunTempDurationStr = number_format(($arrRotaWeek['Sunday']['Duration'][1]/3600), 2, '.', '');
		  $sunTempShiftStr = $arrRotaWeek['Sunday']['Shift'][1];
		  $intWeekDurationNew += number_format(($arrRotaWeek['Sunday']['Duration'][1]/3600), 2, '.', '');
	  }
	  else
	  {
		$sundayData = $arrRotaWeek['Sunday'] ?? [];
		$dutyName = is_array($sundayData) ? ($sundayData['DutyName'][0] ?? '') : '';
		$duration = is_array($sundayData) ? ($sundayData['Duration'][0] ?? 0) : 0;
		$shift = is_array($sundayData) ? ($sundayData['Shift'][0] ?? '') : '';

		$sunTempDutyNameStr = is_string($dutyName) ? $dutyName : '';
		$sunTempDurationStr = is_numeric($duration) ? number_format(($duration/3600), 2, '.', '') : '0.00';
		$sunTempShiftStr = is_string($shift) || is_numeric($shift) ? (string)$shift : '';

		if (is_numeric($duration) && $duration > 0) {
			$intWeekDurationNew += number_format(($duration/3600), 2, '.', '');
		}
	  }
      $strHTML.= '<td width="150px">'. $sunTempDutyNameStr .'</td>';
	  $strHTML.= '<td width="50px">' . $sunTempDurationStr .'</td>';
	  $strHTML.= '<td align="center" width="50px">'. $sunTempShiftStr .'</td>';
	  //for mon
	  if(!empty($arrRotaWeek['Monday']['Shift'][1]))
	  {
		  $monTempDutyNameStr = $arrRotaWeek['Monday']['DutyName'][1];
		  $monTempDurationStr = number_format(($arrRotaWeek['Monday']['Duration'][1]/3600), 2, '.', '');
		  $monTempShiftStr = $arrRotaWeek['Monday']['Shift'][1];
		  $intWeekDurationNew += number_format(($arrRotaWeek['Monday']['Duration'][1]/3600), 2, '.', '');
	  }
	  else
	  {
		$mondayData = $arrRotaWeek['Monday'] ?? [];
		$dutyName = is_array($mondayData) ? ($mondayData['DutyName'][0] ?? '') : '';
		$duration = is_array($mondayData) ? ($mondayData['Duration'][0] ?? 0) : 0;
		$shift = is_array($mondayData) ? ($mondayData['Shift'][0] ?? '') : '';

		$monTempDutyNameStr = is_string($dutyName) ? $dutyName : '';
		$monTempDurationStr = is_numeric($duration) ? number_format(($duration/3600), 2, '.', '') : '0.00';
		
		$monTempShiftStr = is_string($shift) || is_numeric($shift) ? (string)$shift : '';
		if (is_numeric($duration) && $duration > 0) {
			$intWeekDurationNew += number_format(($duration/3600), 2, '.', '');
		}
	  }
      $strHTML.= '<td width="150px">'. $monTempDutyNameStr .'</td>';
	  $strHTML.= '<td width="50px">'. $monTempDurationStr .'</td>';
	  $strHTML.= '<td align="center" width="50px">'. $monTempShiftStr .'</td>';
	  //for tue
	  if(!empty($arrRotaWeek['Tuesday']['Shift'][1]))
	  {
		  $tueTempDutyNameStr = $arrRotaWeek['Tuesday']['DutyName'][1];
		  $tueTempDurationStr = number_format(($arrRotaWeek['Tuesday']['Duration'][1]/3600), 2, '.', '');
		  $tueTempShiftStr = $arrRotaWeek['Tuesday']['Shift'][1];
		  $intWeekDurationNew += number_format(($arrRotaWeek['Tuesday']['Duration'][1]/3600), 2, '.', '');
	  }
	  else
	  {
		$tuesdayData = $arrRotaWeek['Tuesday'] ?? [];
		$dutyName = is_array($tuesdayData) ? ($tuesdayData['DutyName'][0] ?? '') : '';
		$duration = is_array($tuesdayData) ? ($tuesdayData['Duration'][0] ?? 0) : 0;
		$shift = is_array($tuesdayData) ? ($tuesdayData['Shift'][0] ?? '') : '';

		$tueTempDutyNameStr = is_string($dutyName) ? $dutyName : '';
		$tueTempDurationStr = is_numeric($duration) ? number_format(($duration/3600), 2, '.', '') : '0.00';
		$tueTempShiftStr = is_string($shift) || is_numeric($shift) ? (string)$shift : '';

		if (is_numeric($duration) && $duration > 0) {
			$intWeekDurationNew += number_format(($duration/3600), 2, '.', '');
		}
	  }
      $strHTML.= '<td width="150px">'. $tueTempDutyNameStr .'</td>';
	  $strHTML.= '<td width="50px">'. $tueTempDurationStr .'</td>';
	  $strHTML.= '<td align="center" width="50px">'. $tueTempShiftStr .'</td>';
	  //for wed
	  if(!empty($arrRotaWeek['Wednesday']['Shift'][1]))
	  {
		  $wedTempDutyNameStr = $arrRotaWeek['Wednesday']['DutyName'][1];
		  $wedTempDurationStr = number_format(($arrRotaWeek['Wednesday']['Duration'][1]/3600), 2, '.', '');
		  $wedTempShiftStr = $arrRotaWeek['Wednesday']['Shift'][1];
		  $intWeekDurationNew += number_format(($arrRotaWeek['Wednesday']['Duration'][1]/3600), 2, '.', '');
	  }
	  else
	  {
		$wednesdayData = $arrRotaWeek['Wednesday'] ?? [];
		$dutyName = is_array($wednesdayData) ? ($wednesdayData['DutyName'][0] ?? '') : '';
		$duration = is_array($wednesdayData) ? ($wednesdayData['Duration'][0] ?? 0) : 0;
		$shift = is_array($wednesdayData) ? ($wednesdayData['Shift'][0] ?? '') : '';

		$wedTempDutyNameStr = is_string($dutyName) ? $dutyName : '';
		$wedTempDurationStr = is_numeric($duration) ? number_format(($duration/3600), 2, '.', '') : '0.00';
		$wedTempShiftStr = is_string($shift) || is_numeric($shift) ? (string)$shift : '';

		if (is_numeric($duration) && $duration > 0) {
			$intWeekDurationNew += number_format(($duration/3600), 2, '.', '');
		}
	  }
      $strHTML.= '<td width="150px">'. $wedTempDutyNameStr .'</td>';
	  $strHTML.= '<td width="50px">'. $wedTempDurationStr .'</td>';
	  $strHTML.= '<td align="center" width="50px">'. $wedTempShiftStr .'</td>';
	  //for thus
	  if(!empty($arrRotaWeek['Thursday']['Shift'][1]))
	  {
		  $thusTempDutyNameStr = $arrRotaWeek['Thursday']['DutyName'][1];
		  $thusTempDurationStr = number_format(($arrRotaWeek['Thursday']['Duration'][1]/3600), 2, '.', '');
		  $thusTempShiftStr = $arrRotaWeek['Thursday']['Shift'][1];
		  $intWeekDurationNew += number_format(($arrRotaWeek['Thursday']['Duration'][1]/3600), 2, '.', '');
	  }
	  else
	  {
		$thursdayData = $arrRotaWeek['Thursday'] ?? [];
		$dutyName = is_array($thursdayData) ? ($thursdayData['DutyName'][0] ?? '') : '';
		$duration = is_array($thursdayData) ? ($thursdayData['Duration'][0] ?? 0) : 0;
		$shift = is_array($thursdayData) ? ($thursdayData['Shift'][0] ?? '') : '';

		$thusTempDutyNameStr = is_string($dutyName) ? $dutyName : '';
		$thusTempDurationStr = is_numeric($duration) ? number_format(($duration/3600), 2, '.', '') : '0.00';
		$thusTempShiftStr = is_string($shift) || is_numeric($shift) ? (string)$shift : '';

		if (is_numeric($duration) && $duration > 0) {
			$intWeekDurationNew += number_format(($duration/3600), 2, '.', '');
		}
	  }
      $strHTML.= '<td width="150px">'. $thusTempDutyNameStr .'</td>';
	  $strHTML.= '<td width="50px">'. $thusTempDurationStr .'</td>';
	  $strHTML.= '<td align="center" width="50px">'. $thusTempShiftStr .'</td>';
	  //for fri
	  if(!empty($arrRotaWeek['Friday']['Shift'][1]))
	  {
		  $friTempDutyNameStr = $arrRotaWeek['Friday']['DutyName'][1];
		  $friTempDurationStr = number_format(($arrRotaWeek['Friday']['Duration'][1]/3600), 2, '.', '');
		  $friTempShiftStr = $arrRotaWeek['Friday']['Shift'][1];
		  $intWeekDurationNew += number_format(($arrRotaWeek['Friday']['Duration'][1]/3600), 2, '.', '');
	  }
	  else
	  {
		$fridayData = $arrRotaWeek['Friday'] ?? [];
		$dutyName = is_array($fridayData) ? ($fridayData['DutyName'][0] ?? '') : '';
		$duration = is_array($fridayData) ? ($fridayData['Duration'][0] ?? 0) : 0;
		$shift = is_array($fridayData) ? ($fridayData['Shift'][0] ?? '') : '';

		$friTempDutyNameStr = is_string($dutyName) ? $dutyName : '';
		$friTempDurationStr = is_numeric($duration) ? number_format(($duration/3600), 2, '.', '') : '0.00';
		$friTempShiftStr = is_string($shift) || is_numeric($shift) ? (string)$shift : '';

		if (is_numeric($duration) && $duration > 0) {
			$intWeekDurationNew += number_format(($duration/3600), 2, '.', '');
		}
	  }
      $strHTML.= '<td width="150px">'. $friTempDutyNameStr .'</td>';
	  $strHTML.= '<td width="50px">'. $friTempDurationStr .'</td>';
	  $strHTML.= '<td align="center" width="50px">'. $friTempShiftStr .'</td>';

      $strHTML.= '<td>';
      $strHTML.=  $intWeekDurationNew;
      $strHTML.= '</td>';      
      
      $strHTML.= '</tr>';
	  $intTotalDuration += $intWeekDurationNew;
	  $intLoopWeek = addweeks($intFirstWeek, $intWeek);
    } 
    
    $intEFTPattern = round($intTotalDuration / ($intWeeksInRota * 35), 3);

    
    echo '<table class="redtable">';
    echo '<tr height="50px">';    
    echo '<th colspan="11" valign="top">';  
    echo '<br>The EFT for this Pattern is  '.$intEFTPattern.'<br>';
    echo 'This pattern has '. ($intTotalAppearence) .' appearances, giving an average of '.round(($intTotalAppearence)/ $intWeeksInRota, 2).' appearances per week.<br>';
    echo 'This pattern has '.$intTotalDuration.' total hours (without mealbreaks), giving an average of '.round($intTotalDuration / $intWeeksInRota, 2).' hours per week.<br>'; 
	echo 'The expected number of hours is '.(round((float)$intWeeksInRota * (float)$arrEFT['EFT'] * 35, 3)).'<br>';   
    echo 'This Underlying Rota Pattern starts in week '.spinweek($intRotaStarts).', and has a duration of '.$intWeeksInRota.' Weeks.<br><br>'; 
    echo '</th>';
    echo '<th colspan="6" valign="top">';
    echo '<br>Of the '. ($intTotalAppearence) .' appearances '.$intCountNights.' are defined as night shifts.';
    if (($intTotalAppearence) > 0) {
      echo '<br>This means that '.number_format($intCountNights * 100 / ($intTotalAppearence) , 1).'% of the rota pattern is night shifts.';
    } 
    echo '</th>';     
    echo '<th colspan="7" valign="top">';
    echo '<br>The Current Week of Allocations is '.spinweek($intCurWeek).'<br>';
    echo 'The Week of the Pattern is '.$intWeekOfRota;  
    echo '</th>';
    echo '</tr>';      

    echo $strHTML;

     
    echo '</table>';
  }
  else {
    echo '<br><div class="tableheadersmall bigtextboldcentre">';
    echo "<br>It's not possible to show the Underlying Rota Pattern.<br>This is because this person does not have an Underlying Rota Pattern.<br><br>";
    echo '</div>';
  }  
  

?>

<script type="text/javascript">
 $(document).ready(function() {
   $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "350px"
  });
$(document).ready(function(){
  $("table.tablesmall tr:odd").addClass("odd");
  $("table.tablesmall tr:even").addClass("even");
})  
})  


</script>

<?php
}
else {
  echo '<br>';
  echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
  echo '<br><br>';
  echo 'You are attempting to view the Underlying Rota Patterns for a Department that has not yet either cretaed any or has not published them.<br><br>';
  echo '<br></br><br>';
  echo '</div>';
}