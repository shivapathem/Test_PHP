<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';

echo '<div id="loader" class="loader">
</div>';
$commonObj = new classCommonDBFunctions();

// Line 381 sort out summenr leave
if (isset($_POST['user'])) {
  $strUser = $_POST['user'];
  $UserID = GetUserIdbyNetlogin($strUser);
} else {
  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
  $UserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
 }
if (isset($_POST['year'])) {
  $intLeaveYear = $_POST['year'];
}
else {  
  $intLeaveYear = date("Y", strtotime("-3 months"));
}

if (isset($_POST['admin'])) {
  $intLeaveAdmin = $_POST['admin'];
}
else {
  $intLeaveAdmin = 0;
}
if (isset($_POST['groupid'])) {
  $intPassedGroupID = $_POST['groupid'];
}
else {  
  $intPassedGroupID = 0;
}

$pageflag =0;
if (isset($_POST['pageflag'])) {
	$pageflag = $_POST['pageflag'];
}

$arrUser = GetScheduledPersonTeamDetailsByUserID($UserID);
$schedulingPersonId = isset($arrUser['ScheduledPersonID']) ? $arrUser['ScheduledPersonID'] : 0;
  if(!empty($arrUser['homeTeamId'])) {
    $schedulingTeamId = $arrUser['homeTeamId'];
  }
  else {
    $schedulingTeamId = key($arrUser);
  }

if ($intLeaveAdmin == 0) {
  $strContectMenu = 'leave-context-menu';
  $pdlLeaveApprovedContextMenu = 'pdl-leave-context-menu-approved';
}
else {
  $strContectMenu = '';
  $pdlLeaveApprovedContextMenu = '';
}
$strYearStars = $intLeaveYear.'-04-01';
$strYearEnds = ($intLeaveYear + 1).'-03-31';
$arrUserLeaveSetting = json_decode($commonObj->userLeaveRequestByNetLogin($strUser, 0),true);

if (isset($arrUserLeaveSetting['LeaveRequests'])) {
  foreach ($arrUserLeaveSetting['LeaveRequests'] as $intGroupID => $arrGroup) {
    if ($arrGroup['Admin'] == 0) {
      $arrGroupsCanRequest[$intGroupID] = $arrGroup;
    }
  }
}
if (isset($arrGroupsCanRequest)) {
  $arrAllLeaveGroups = GetLeaveGroupAndTeamsFromID();
  $arrLeave = GetLeaveForUser ($strUser, $strYearStars, $strYearEnds,$schedulingPersonId);
  if (isset($arrLeave['SummerClicksAllowed']) &&  $arrLeave['SummerClicksAllowed'] > $arrLeave['UserLeave']['SummerLeaveClicks']) {

    $intCanRequestSummer = 1; 
  }
  else {
    $intCanRequestSummer = 0;   
  }
  $intHasTypes = 0;
  foreach ($arrGroupsCanRequest as $intGroupID => $arrGroup) {
    if (isset($arrGroup['LeaveTypes'])) {
      $intHasTypes = 1;        
    }
  }      
  $jlink = '';

  $intEmailsToSend = GetRequestEmailsToSend($strUser);

  // Function to check if a date is a holiday
  function getHolidayDescription($date, $holidayLists) {
    $date = date("Y-m-d", strtotime($date));
    foreach ($holidayLists as $holiday) {
      if (($holiday['dDateTime']) == $date) {
        return $holiday['sEvent'];
      }
    }
    return false;
  }
  
  if ($intHasTypes == 0) {
    echo '<br><div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
    echo '<br>Even though you are in one or more Leave Groups.<br>There are no Leave Types defined.<br>This mean you can\'t request any Leave at present.<br><br>';
    echo '</div>';
    ?>
    <script type="text/javascript">
    $('#loader').hide();
    </script>
    <?php 
    die;
  }
    $tdate = $strYearStars;
    $getWeekStartArr = GetAllocationWeekandDay($tdate);
	$arrstartweek = $getWeekStartArr['ixYearWeek'];
	$getWeekEndArr = GetAllocationWeekandDay($strYearEnds);
	$arrendweek = $getWeekEndArr['ixYearWeek'];
	$arrAllocations = leaveRequestAllocationsAndRota($arrstartweek, $arrendweek,$schedulingPersonId,$strUser);

    // Last value = force read tota 
  //  ############################################################## Header
  echo '<h1 class="sr-only">My Leave Requests</h1>';
  echo '<table class="tablesmalltidy  leavePageScroollbar" onScroll="setScrollPosition(this);">';
  echo '<tr>';
  echo '<td colspan="44" >';  
  echo '<table class="tablegreysmallnoborder" width="100%">';
  echo '<tr>';
  if ($intLeaveAdmin == 0) {
    echo '<td width="200px" class="medtextbold handcursor" onclick="javascript:ShowLeave(\''.($intLeaveYear - 1).'\')">';
    echo '&lt;&lt; '.($intLeaveYear - 1);
    echo '</td>';   
    echo '<td width="50px" class="medtextbold handcursor">';
    echo '|';
    echo '</td>';  
    echo '<td width="200px" align="right" class="medtextbold handcursor" onclick="javascript:ShowLeave(\''.($intLeaveYear + 1).'\')">';
    echo ($intLeaveYear + 1).' &gt;&gt;';
    echo '</td>';
  }
  else {
    echo '<td width="200px" class="medtextbold handcursor" onclick="javascript:ShowUserLeave(\''.$strUser.'\',\''.($intLeaveYear - 1).'\')">';
    echo '&lt;&lt; '.($intLeaveYear - 1);
    echo '</td>';   
    echo '<td width="50px" class="medtextbold handcursor">';
    echo '|';
    echo '</td>';  
    echo '<td width="200px" align="right" class="medtextbold handcursor" onclick="javascript:ShowUserLeave(\''.$strUser.'\',\''.($intLeaveYear + 1).'\')">';
    echo ($intLeaveYear + 1).' &gt;&gt;';
    echo '</td>';  
  }
  echo '<td align="center" class="medtextbold">';
  echo '<font size="3">Leave For '.$intLeaveYear.'</font>';
  echo '</td>';   
  echo '</tr>';        

  echo '<tr>';
  echo '<td colspan="3">';
  if ($arrLeave['UserLeave']['ExtraLeaveClicks'] <= 20) {
    echo '<br>You have requested '.$arrLeave['UserLeave']['DayCount'].' Days so far.'; 
    
    $intRemainingRequests = ceil(($arrLeave['UserLeave']['Allocated'] - $arrLeave['UserLeave']['TotalHours']) / $arrLeave['UserLeave']['HoursPerDay']) +  $arrLeave['UserLeave']['ExtraLeaveClicks'];
    echo ' You may request '.$intRemainingRequests.' more days this year.<br>';
    if ($intRemainingRequests <= 0) {
      echo '<font color="#990000"><b>You have reached your limit for this year - No more requests are available!</b></font><br>';
    }
    else {
      echo 'This is calculated as your total Leave Credit for this year divided by '.number_format($arrLeave['UserLeave']['HoursPerDay'],2).' Hours. There are, in addition, '.$arrLeave['UserLeave']['ExtraLeaveClicks'].' courtesy Requests<br>';
      if (isset($arrLeave['Summer'])) {
        echo 'During the Summer Leave period you may request '.$arrLeave['SummerClicksAllowed'].' days. Currently You Have requested '.$arrLeave['UserLeave']['SummerLeaveClicks'];
      }
    } 
    echo '<br><br>';
  }
  else{
    echo '<br>You have requested '.$arrLeave['UserLeave']['DayCount'].' Days so far.<br>'; 
    $intRemainingRequests = ceil(($arrLeave['UserLeave']['Allocated'] - $arrLeave['UserLeave']['TotalHours']) / $arrLeave['UserLeave']['HoursPerDay']) +  $arrLeave['UserLeave']['ExtraLeaveClicks'];
    if ($intRemainingRequests <= 0) {
      echo '<font color="#990000"><b>You have reached your limit for this year - No more requests are available!</b></font><br>';
    }
    else {
      if (isset($arrLeave['Summer'])) {
        echo 'During the Summer Leave period you may request '.$arrLeave['SummerClicksAllowed'].' days. Currently You Have requested '.$arrLeave['UserLeave']['SummerLeaveClicks'];
      }
    } 
    echo '<br><br>';  
  }  
  echo '</td>';
  echo '<td  align="right">';
  if ($intEmailsToSend >= 1) {    
    echo '<table>';
    echo '<tr>';
    echo '<td  align="right">';
    echo 'When you have finished requesting or deleting, please click here:<br>This will send an email to your authoriser.';
    echo '</td>';  
    echo '<td class="handcursor" align="right" onclick="javascript:SendApplicationLeaveEmail(\''.$strUser.'\',\''.$pageflag.'\')">';         
    echo '<img title="Send Leave Request Email" border="0" src="../images/Email.png" width="35px" height="35px">';
    echo '</td>'; 
    echo '</tr>';
    echo '</table>'; 
  }
  echo '</td>'; 
  echo '</table>'; 
  echo '<div  id="tblLeftPost" name="tblLeftPost"></div>';
  echo '</td>';
  echo '</tr>';

  //  ############################################################## Start with the table and write the days across the top allowing for enough weeks in each month!
  
  echo '<tr>';
  echo '<td colspan="2" class="tableheadersmall"><div class="tableheadersmall"></div></td>';
  for ($i=0; $i <= 5; $i++) {
    for ($ii=0; $ii <= 6; $ii++) {
      echo '<td class="tableheadersmall"><div class="tableheadersmall smalltextcentre box25x15">'.substr($invdowMap[$ii], 0, 1).'</div></td>';
    }
  }
  echo '</tr>';    
  //  ############################################################## End Days across the top
  $TotalWeekInCurYear = WeeksInYear($intLeaveYear);
  $strLoopDate = $strYearStars;  
  $rCounter = 1;
  while (strtotime($strLoopDate) <= strtotime($strYearEnds)) {
    // The Month
    echo '<tr>';
    echo '<td colspan="2" class="tableheadersmall">';
    echo '</td>';
 
    // End The Month
    // The Weeks
    for ($iweek = 0; $iweek <= 5; $iweek++){
     $currweekstarts = date("Y-m-d", strtotime("+".($iweek * 7)." days", strtotime($strLoopDate)));
     $getWeekandDayArr = GetAllocationWeekandDay($currweekstarts);
     $currweek = $getWeekandDayArr['ixYearWeek'];
     
     // Do we allow the link to the weekly leave?
     //if ($jlink == 'ShowLeave') {
     if ($intLeaveAdmin == 1) {
       echo '<td class="tableheadersmall handcursor" colspan="7" align="center" onclick="javascript:ShowLeaveWeeklyAdmin(\''.$currweek.'\',\''.$intPassedGroupID.'\')">';
     }
     else {
       echo '<td class="tableheadersmall handcursor" colspan="7" align="center" onclick="javascript:ShowLeaveWeekly(\''.$currweek.'\',\''.$strUser.'\')">';
     }
     // Is the month equal to the current one?
     if ((date("m", strtotime(datefromweek($currweek))) <= date("m", strtotime($strLoopDate))) && (date("y", strtotime(datefromweek($currweek))) == date("y", strtotime($strLoopDate))) ) {
 	       echo spinweek($currweek);
     }
     echo '</td>';
     // End the weeks
    }
    echo '</tr>';
    // Now a Row with spacers and the day of the month
    echo '<tr>';
    echo '<td colspan="2" valign="top" class="medlightcell">';
    echo '<b>'.date("F", strtotime($strLoopDate)).'</b>';
    echo '</td>';
    
    $ddate  = $strLoopDate;
    $daysinmonth = date("t", strtotime($ddate));
    $startdaynumber = $dowMap[date("D", strtotime($ddate))];
    // Spacers
    for ($i=1; $i <= $startdaynumber; $i++) {
      echo '<td class="medlightcell"></td>';
    }
    // End Spacers
    $nextmonth = strtotime("+1 month", strtotime($ddate));
    // Dates in the month
    $holidayLists = GetLeaveHolidaysByYear(date("Y", strtotime($strLoopDate)));
    while (strtotime($ddate) < $nextmonth) {
      $dayOfWeek = date('N', strtotime($ddate));
      $holidayDate = date('d-m-Y', strtotime($ddate));
      $holidayDescription = getHolidayDescription($holidayDate, $holidayLists);
      $isHoliday = $holidayDescription ? true : false;
      $backgroundColor = ($isHoliday) ? 'style="background: #ff9999"' : (($dayOfWeek == 6 || $dayOfWeek == 7) ? 'style="background: #D0D0D0"' : '');
      $holidayTitle = $isHoliday ? $holidayDescription : '';
      echo '<td valign="top" class="medlightcell" '.$backgroundColor.'>';
      echo '<div class="handcursor box35x15" onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$holidayTitle.'">';
        echo date("d", strtotime($ddate)).'<br>';
        echo '</div>';
      echo '</td>';
      $ddate = date ("Y-m-d", strtotime("+1 day", strtotime($ddate)));
    }
    // Now write in spaces at the end of the month
     for ($i=($daysinmonth + $startdaynumber); $i <= 41; $i++) {
      echo '<td class="medlightcell"></td>';
    }
    echo '</tr>';
    // End Now a Row with spacers and the day of the month
    // Now the Allocations
    if (isset($arrAllocations)) {
      echo '<tr>';
      echo '<td colspan="2" valign="top" class="medlightcell">';
      echo '</td>';
    
      $ddate  = $strLoopDate;
      $daysinmonth = date("t", strtotime($ddate));
      $startdaynumber = $dowMap[date("D", strtotime($ddate))];
      // Spacers
      for ($i=1; $i <= $startdaynumber; $i++) {
        echo '<td class="medlightcell"></td>';
      }
      // End Spacers
      $nextmonthdate = date("Y-m-d",strtotime("+1 month", strtotime($ddate)));
	  $getWeekStartMonthArr = GetAllocationWeekandDay($ddate);
	  $arrstartweek = $startcheck=$getWeekStartMonthArr['ixYearWeek'];
	  $arrstartday = $getWeekStartMonthArr['ixDayInWeek'];
	  $arrstartyear = $getWeekStartMonthArr['ixYear'];
	  $getWeekEndMonthArr = GetAllocationWeekandDay($nextmonthdate);
	  $arrendweek = $getWeekEndMonthArr['ixYearWeek'];
	  $arrendday = $getWeekEndMonthArr['ixDayInWeek'];
	  $arrendyear = $getWeekEndMonthArr['ixYear'];
      // Dates in the month
      while ($arrstartweek <= $arrendweek) { 
		$startday=0;
		$endday=6;
		if ($startcheck ==$arrstartweek) {
		  $startday = $arrstartday;
		} 
		if ($arrendweek ==$arrstartweek) {
		  $endday = $arrendday-1;
		} 
	  // $start =  $arrstartday
	  for ($k=$startday;$k<=$endday;$k++) { 
        echo '<td valign="top" class="medlightcell">';   
        $intAllocWeek = $arrstartweek;
		$intAllocDay = $k;
        $title = isset($arrAllocations['Weeks'][$intAllocWeek][$intAllocDay]) ? $arrAllocations['Weeks'][$intAllocWeek][$intAllocDay]['Duty'] : '-';
        echo '<div class="handcursor box35x15" onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$title.'">';
        echo $title;
        echo '</div>';
        echo '</td>';
      } 
	  $strtyear = substr($arrstartweek, 0, 4);
	  $strtweek = substr($arrstartweek, 4);
	  
	  if ($strtweek>=$TotalWeekInCurYear){
			$arrstartweek=($strtyear+1)."01";
		}	else {
			$arrstartweek ++;
		}
	  }
      // Now write in spaces at the end of the month
       for ($i=($daysinmonth + $startdaynumber); $i <= 41; $i++) {
        echo '<td class="medlightcell"></td>';
      }
      echo '</tr>';
    // End Now a Row with spacers and the day of the month
    
    }
    // Now the Requests
    foreach ($arrGroupsCanRequest as $intGroupID => $arrGroup) {
      if (isset($arrGroup['LeaveTypes'])) {
      echo '<tr>';
      echo '<td valign="top" class="lightcell cellWmm" width="200px">'; 
      echo $arrGroup['Description'];
      echo '</td>';
      // Put the types available in....
      echo '<td align="right" valign="top" class="lightcell cellWmm" width="100px">'; 
      echo '<br>';
      foreach ($arrGroup['LeaveTypes'] as $intTypeID => $strTypeDescription) {
        echo $strTypeDescription.'&nbsp;&nbsp;<br>';
      }
      echo '</td>';  
      $ddate  = $strLoopDate;
      $daysinmonth = date("t", strtotime($ddate));
      $startdaynumber = $dowMap[date("D", strtotime($ddate))];
      // Spacers at the start
      for ($i=1; $i <= $startdaynumber; $i++) {
        echo '<td class="lightcell"></td>';
      }
      // End Spacers
      $nextmonth = strtotime("+1 month", strtotime($ddate));
       $nextmonthdate = date("Y-m-d",strtotime("+1 month", strtotime($ddate)));
	  $getWeekStartMonthArr = GetAllocationWeekandDay($ddate);
	  $arrstartweek = $startcheck=$getWeekStartMonthArr['ixYearWeek'];
	  $arrstartday = $getWeekStartMonthArr['ixDayInWeek'];
	  $arrstartyear = $getWeekStartMonthArr['ixYear'];
	  $getWeekEndMonthArr = GetAllocationWeekandDay($nextmonthdate);
	  $arrendweek = $getWeekEndMonthArr['ixYearWeek'];
	  $arrendday = $getWeekEndMonthArr['ixDayInWeek'];
	  $arrendyear = $getWeekEndMonthArr['ixYear'];
	  $TotalWeekInCurYear = WeeksInYear($intLeaveYear);
       //while (strtotime($ddate) < $nextmonth) {
	  while ($arrstartweek <= $arrendweek) { 
		$startday=0;
		$endday=6;
		if ($startcheck ==$arrstartweek) {
		  $startday = $arrstartday;
		} 
		if ($arrendweek ==$arrstartweek) {
		  $endday = $arrendday-1;
		} 
	   for ($k=$startday;$k<=$endday;$k++) { 
        echo '<td valign="top" class="lightcell" width="1%">';
        // ################################################################################## Now the actual leave
        $intCountTypes = 0;
        foreach ($arrGroup['LeaveTypes'] as $intTypeID => $strTypeDescription) {
          $intAllowOver = $arrAllLeaveGroups[$intGroupID]['ShowLeaveOverLimit'];
          $intShortNoticeLeaveStarts = strtotime("+".$arrAllLeaveGroups[$intGroupID]['Types'][$intTypeID]['ShortNoticeLeaveStarts']." days");
          $intLeaveStarts = strtotime("+".$arrAllLeaveGroups[$intGroupID]['Types'][$intTypeID]['LeaveStarts']." days");
          $intLeaveEnds = strtotime("+".$arrAllLeaveGroups[$intGroupID]['Types'][$intTypeID]['LeaveEnds']." days");
          // Create a string for applying - this depends on whether we have reached our limit.....
          if ($intRemainingRequests > 0) {
            if ($intLeaveAdmin == 0) {
              $strJSApply = 'onclick="javascript:LeaveApply(\''.$strUser.'\',\''.$ddate.'\','.$intTypeID.','.$intLeaveYear.')"';
            }
            else {
              $strJSApply = 'onclick="javascript:LeaveApplyAdmin(\''.$strUser.'\',\''.$ddate.'\','.$intTypeID.','.$intLeaveYear.')"';
            }
            $strTitleNoMore = '';
          }
          else {
            $strJSApply = '';
            $strTitleNoMore = 'You have reached your limit this year!';
          }
          
          if ($intCountTypes == 0) {
            if (isset($arrLeave['Summer'][$intTypeID]['Dates'][$ddate])) {
              echo '<span class="summerNotice LeaveBlank1 displayInlineBlock"></span>';
            }
            else {
              echo '<span class="whiteNotice LeaveBlank1 displayInlineBlock"></span>';
            }
          } 
          if (strtotime($ddate) > $intLeaveEnds) {
            echo '<span class="LeaveOutsideBounds displayInlineBlock"></span>';            
          
          }
          else {
          $isPartDayLeaveAllow = 0;
		  $intAllocWeekPDL = $arrstartweek;
		  $intAllocDayPDL = $k;
		  $leaveDutyName = isset($arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['Duty'])?$arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['Duty']:'';
          $leaveDutySTime = isset($arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['StartTime'])?$arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['StartTime']:'';
          $arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['EndTime'] = isset($arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['EndTime']) ? $arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['EndTime'] : 0;
          
          if($arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['EndTime'] > 86400){
								$arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['EndTime'] = $arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['EndTime'] - 86400;
          }
          $leaveDutyETime = isset($arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['EndTime'])?$arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['EndTime']:'';
		  $leaveDutyAllocationID = isset($arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['AllocationID'])?$arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL]['AllocationID']:'';

          // Has this person got a request in on this date?
          if (isset($arrLeave['UserLeave']['Dates'][$ddate])) { 
            
            if (isset($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID])) {
              $intLeaveID = $arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['LeaveID'];
              $isPDLApplied = $arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['isPDLApplied'];
              $isPDLAppr = $arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['Approved'];
              $isLeaveApproved = $arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['Approved'];
              $pdlAppliedIcon = '';
              if(($isPDLApplied == 1) || ($isPDLAppr == 1)){
                $pdlAppliedIcon = '*';
                if(($isPDLApplied == 0) && ($isPDLAppr == 1)){
                  $pdlAppliedIcon = '';
                }
              }
              // It's this type
              // Is it after the Leave starts?
              if((isset($arrAllocations['Weeks'][$intAllocWeekPDL][$intAllocDayPDL])) && ($arrGroup['IsPartDayLeaveAllowed'] ==  1) && (!empty($leaveDutySTime) || !empty($leaveDutyETime))){
                $isPartDayLeaveAllow = 1;
              }
              if (strtotime($ddate) >= $intLeaveStarts) {
                // It's in the application period
                if ($isLeaveApproved == 1) {
                    if ($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['CountLeave'] == 1) {
        				if(($isPDLApplied == 1) && ($isPDLAppr !=1)){
                            echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK pdl-div-star LeaveBlank displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
        				} else{
        					if(($isPDLAppr == 1) && ($isPDLApplied == 1)){
        						echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK LeaveBlank pdl-div-star displayInlineBlock '.$pdlLeaveApprovedContextMenu.'">'.$pdlAppliedIcon.'</span>';
        					} else {
        						echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'-approved">'.$pdlAppliedIcon.'</span>';
        					}
					    }
                    } else {
                        // hashed yellow
			            echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="LeaveHashedYellow LeaveBlank pdl-div-star displayInlineBlock handcursor '.$strContectMenu.'-approved">'.$pdlAppliedIcon.'</span> ';
                    } 
                } else {
                    if ($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['isOK']) {
                        if ($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['OverSummer'] == 1) {
                            echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This a request over the Summer Limit" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeavePurple LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
                        } else {
                            echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is a Leave request that is OK.<br>It has not yet been approved." starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
                        }
                    } else {
                        // A request That's NOT OK
                        if ($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['OverSummer'] == 1) {
                          echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This a request over the Summer Limit<br>In addition it is in the waiting List" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeavePurple LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
                        } else {
                          echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is a Leave request that is in the Queue.<br>It has not yet been approved." starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveNotOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
                        }                    
                    }                 
                }
              } else {
                if (strtotime($ddate) >= $intShortNoticeLeaveStarts) {
                  // It's in the short notice period
                    if ($isLeaveApproved == 1) { 
                        if ($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['CountLeave'] == 1) {
          					if($isPDLApplied == 1){
          						echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
          					 } else {
                                echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
          					}
                        } else {
                            // hashed yellow
					        echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'"  leaveDutyAllocationID="'.$leaveDutyAllocationID.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="LeaveHashedYellow LeaveBlank pdl-div-star displayInlineBlock handcursor '.$strContectMenu.'-approved">'.$pdlAppliedIcon.'</span> ';
				        }
                    } else {
                        if ($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['ShortNotice'] == 1) {
                            echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is a Short Notice Leave request.<br>It has not been approved." leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveShortNoticeApplied LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
                        } else {
                            if ($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['isOK'] == 1) {
                                echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is a Leave request that is OK.<br>It has not yet been approved." leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
                            } else {
                                echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is a Leave request that is in the Queue.<br>It has not yet been approved." leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveNotOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
                            }
                        }
                    }                  
                } else {
                    // It's in the closed period - only show approved
                    if ($isLeaveApproved == 1) {
                        if ($arrLeave['UserLeave']['Dates'][$ddate][$intTypeID]['CountLeave'] == 1) {
          					if(($isPDLApplied == 1) && ($isPDLAppr != 1)){
          					    echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'"  leaveDutyAllocationID="'.$leaveDutyAllocationID.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'">'.$pdlAppliedIcon.'</span>';
          					} else {
                                if($isPDLApplied == 0){
                                    echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request" starttime="'.$leaveDutySTime.'" endtime="'.$leaveDutyETime.'" dutyname="'.$leaveDutyName.'" leaveid="'.$intLeaveID.'" year="'.$intLeaveYear.'" user="'.$strUser.'" leaveDutyAllocationID="'.$leaveDutyAllocationID.'" ispartdayleaveallow="'.$isPartDayLeaveAllow.'" isLeaveProved="'.$isLeaveApproved.'" isPDLApplied="'.$isPDLApplied.'" class="handcursor LeaveOK LeaveBlank pdl-div-star displayInlineBlock '.$strContectMenu.'-approved"></span>';
                                } else { 
          							echo '<span class="LeaveOK LeaveBlank displayInlineBlock" onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request"></span>';
                                }
						    }
                        } else {
                            echo '<span class="LeaveHashedYellow LeaveBlank displayInlineBlock" onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="This is an approved Leave request"></span>';
                        }
                    } else {
                        echo '<span class="LeaveOutsideBounds displayInlineBlock"></span>';
                    }                 
                }
              }
            } else {
              // It's not this type
              // Go through the options leave stars, Short Notice Leave Starts etc
              if (strtotime($ddate) >= $intLeaveStarts) {
                // Its in the normal leave period and there is'nt an application in.....
                if (isset($arrLeave['Applications'][$intTypeID][$ddate])) {
                  $intRequestsIn = $arrLeave['Applications'][$intTypeID][$ddate];
                } else {
                  $intRequestsIn = '0';           
                }
                if (isset($arrLeave['Available'][$intTypeID][$ddate])) {
                  $intRequestsAvailable = $arrLeave['Available'][$intTypeID][$ddate];
                } else {
                  $intRequestsAvailable = 0;
                }           
                if ($intRequestsIn < $intRequestsAvailable) {
                  if (isset($arrLeave['Summer'][$intTypeID]['Dates'][$ddate])) {
                    echo '<span class="LeaveBlank displayInlineBlock availableAltIN"></span>';
                  } else {
                    echo '<span class="LeaveBlank displayInlineBlock availableAltIN"></span>';
                  }
                }
                else {
                  if (isset($arrLeave['Summer'][$intTypeID]['Dates'][$ddate])) {
                    echo '<span class="handcursor LeaveBlank displayInlineBlock NotAvailableAltIn"></span>';
                  } else {
                    echo '<span class="LeaveBlank displayInlineBlock NotAvailableAltIn"></span>';
                  }                  
                }
              }
              else {
                if (strtotime($ddate) >= $intShortNoticeLeaveStarts) {              
                  // It's in the Short notice 
                  if (isset($arrLeave['Applications'][$intTypeID][$ddate])) {
                    $intRequestsIn = $arrLeave['Applications'][$intTypeID][$ddate];
                  } else {
                    $intRequestsIn = '0';           
                  }
                  if (isset($arrLeave['Available'][$intTypeID][$ddate])) {
                    $intRequestsAvailable = $arrLeave['Available'][$intTypeID][$ddate];
                  } else {
                    $intRequestsAvailable = 0;
                  }           
                  if ($intRequestsIn <= $intRequestsAvailable) {
                    echo '<span class="LeaveBlank displayInlineBlock ShortNoticeAltIn"></span>';
                  } else {
                    echo '<span class="LeaveBlank displayInlineBlock ShortNoticeAltIn"></span>';
                  }                 
                } else {
                  echo '<span class="LeaveOutsideBounds displayInlineBlock"></span>';
                }                                                                                              
              }
           }
          echo '<br>';  
          } else {  
            //  There isn't a request in......
            if (strtotime($ddate) >= $intLeaveStarts) {
              // Its in the normal leave period and there is'nt an application in.....
              if (isset($arrLeave['Applications'][$intTypeID][$ddate])) {
                $intRequestsIn = $arrLeave['Applications'][$intTypeID][$ddate];
              } else {
                $intRequestsIn = '0';           
              }
              if (isset($arrLeave['Available'][$intTypeID][$ddate])) {
                $intRequestsAvailable = $arrLeave['Available'][$intTypeID][$ddate];
              } else {
                $intRequestsAvailable = 0;
              }           
              if ($intRequestsIn < $intRequestsAvailable) {
                if (isset($arrLeave['Summer'][$intTypeID]['Dates'][$ddate])) {
                  if ($intCanRequestSummer == 1) {
                    // Has Summer Requests available
                    if ($strJSApply == '') {
                      $strTitle = $strTitleNoMore;                       
                    } else {
                      $strTitle = 'This is a Summer Leave Date.<br>It is available<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.' requests already in.';
                    }               
                    echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveAvailable LeaveBlank displayInlineBlock" '.$strJSApply.'></span>'; 
                  } else {
                    // Are we allowed to request over the limit?
                    if ($arrAllLeaveGroups[$intGroupID]['SummerLeaveOverLimit'] == 1) {
                      if ($strJSApply == '') {
                        $strTitle = $strTitleNoMore;                       
                      } else {
                        $strTitle = 'This is a Summer Leave Date.<br>You may request it and it will be over the number allowed<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.' requests already in.';
                      }                        
                      echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveAvailable LeaveBlank displayInlineBlock" '.$strJSApply.'></span>';
                    } else {
                      if ($strJSApply == '') {
                        $strTitle = $strTitleNoMore;                       
                      } else {
                        $strTitle = 'This is a Summer Leave Date.<br>You are over the number allowed and may not request this day.';
                      }     
                          if ($arrLeave['UserLeave']['SummerLeaveClicks'] >= $arrLeave['SummerClicksAllowed'] && $arrAllLeaveGroups[$intGroupID]['SummerLeaveOverLimit'] == 0) {
                               $strJSApply = '';
                          }
                       echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveAvailable LeaveBlank displayInlineBlock" '.$strJSApply.'></span>';
                    }
                  }
                }
                else {
                  if ($strJSApply == '') {
                    $strTitle = $strTitleNoMore;                       
                  } else {
                    $strTitle = 'This day is available and you may<br> request it. There are '.$intRequestsAvailable.' guaranteed<br> slots and '.$intRequestsIn.' requests already in.';
                  }   
                  echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveAvailable LeaveBlank displayInlineBlock" '.$strJSApply.'></span>';
                }
              }
              else {
                if (isset($arrLeave['Summer'][$intTypeID]['Dates'][$ddate])) {
                  if ($intCanRequestSummer == 1) {
                    // Has Summer Requests available
                    if ($intAllowOver == 1) {
                      if ($strJSApply == '') {
                        $strTitle = $strTitleNoMore;                       
                      } else {
                        if ($intRequestsAvailable == 0) {
                          $strTitle = 'This is a Summer Leave Date.<br>You may join the waiting list<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.' requests already in.';
                        } else {
                          $strTitle = 'This is a Summer Leave Date.<br>It is fully subscribed.<br>You may join the waiting list<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.' requests already in.';
                        }
                      }                         
                      //It's not available but can request  
                      echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveNotAvailable LeaveBlank displayInlineBlock" '.$strJSApply.'></span>';
                    } else {
                      if ($strJSApply == '') {
                        $strTitle = $strTitleNoMore;                       
                      } else {
                        $strTitle = 'This is a Summer Leave Date.<br>It is fully subscribed.<br>It is not avaliable.';
                      }                        
                       echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.' requests already in." class="handcursor LeaveNotAvailable LeaveBlank displayInlineBlock"></span>';
                    }
                  } else {
                    // Are we allowed to request over the limit?
                    if ($arrAllLeaveGroups[$intGroupID]['SummerLeaveOverLimit'] == 1) {
                      if ($intAllowOver == 1) {
                        //It's not available but can request  
                        if ($strJSApply == '') {
                          $strTitle = $strTitleNoMore;                       
                        } else {
                          if ($intRequestsAvailable == 0) {
                            $strTitle = 'This is a Summer Leave Date.<br>You may join the waiting list<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.' requests already in.';
                          } else {
                            $strTitle = 'This is a Summer Leave Date.<br>It is fully subscribed.<br>You may join the waiting list<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.' requests already in.';
                          }                        
                        }  
                        echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveNotAvailable LeaveBlank displayInlineBlock" '.$strJSApply.'></span>';
                      } else {
                        if ($strJSApply == '') {
                          $strTitle = $strTitleNoMore;                       
                        } else {
                          $strTitle = 'This is a Summer Leave Date.<br>It is fully subscribed.<br>It is not avaliable.';
                        }                        
                        echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveNotAvailable LeaveBlank displayInlineBlock"></span>';
                      }  
                    } else {   
                      if ($strJSApply == '') {
                        $strTitle = $strTitleNoMore;                       
                      } else {
                        $strTitle = 'This is a Summer Leave Date.<br>You are over the number allowed and may not request this day.';
                      }                        
                      echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveNotAvailable LeaveBlank displayInlineBlock"></span>';
                    }
                  }
                } else {
                  if ($intAllowOver == 1) {
                    //It's not available but can request 
                    if ($strJSApply == '') {
                      $strTitle = $strTitleNoMore;                       
                    } else {
                      if ($intRequestsAvailable == 0) {
                        $strTitle = 'You may join the waiting list<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.'<br> requests already in.';
                      } else {
                        $strTitle = 'This Day is fully subscribed.<br>You may join the waiting list<br>There are '.$intRequestsAvailable.' guaranteed slots and '.$intRequestsIn.' requests already in.';
                      }
                    }                        
                    echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveNotAvailable LeaveBlank displayInlineBlock" '.$strJSApply.'></span>';
                  } else {
                    if ($strJSApply == '') {
                      $strTitle = $strTitleNoMore;                       
                    } else {
                      $strTitle = 'This Day is fully subscribed.<br>It is not avaliable.';
                    }                     
                    echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="'.$strTitle.'" class="handcursor LeaveNotAvailable LeaveBlank displayInlineBlock"></span>';
                  }
                }                  
              }
            } else {
              if (strtotime($ddate) >= $intShortNoticeLeaveStarts) {              
                // It's in the Short notice 
                if (isset($arrLeave['Applications'][$intTypeID][$ddate])) {
                  $intRequestsIn = $arrLeave['Applications'][$intTypeID][$ddate];
                } else {
                  $intRequestsIn = '0';           
                }
                if (isset($arrLeave['Available'][$intTypeID][$ddate])) {
                  $intRequestsAvailable = $arrLeave['Available'][$intTypeID][$ddate];
                } else {
                  $intRequestsAvailable = 0;
                }           
                if ($intRequestsIn <= $intRequestsAvailable) {
                  echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="Short Notice Leave" class="handcursor LeaveShortNotice LeaveBlank displayInlineBlock" '.$strJSApply.'></span>';
                } else {
                  echo '<span onmouseover="customtiptitleMLR(\'showtooltip\',this,'.$rCounter.')" onmouseleave="customtiptitleMLR(\'hidetooltip\')" data-title="Short Notice Leave" class="handcursor LeaveShortNotice LeaveBlank displayInlineBlock" '.$strJSApply.'></span>';
                }                 
              } else {
                echo '<span class="LeaveOutsideBounds displayInlineBlock"></span>';
              }                                                                                              
            }
          }
        }
        $intCountTypes++;
      }
      echo '</td>';
      $ddate = date ("Y-m-d", strtotime("+1 day", strtotime($ddate)));
     }
	   $strtyear = substr($arrstartweek, 0, 4);
		$strtweek = substr($arrstartweek, 4);
		
		if ($strtweek>=$TotalWeekInCurYear){
			$arrstartweek=($strtyear+1)."01";
		}	else {
			$arrstartweek ++;
		}
    }
    // Now write in spaces at the end of the month
     for ($i=($daysinmonth + $startdaynumber); $i <= 41; $i++) {
      echo '<td class="lightcell"></td>';
     }        
     echo '</tr>';
    }
    $rCounter++;
  }
  $strLoopDate = date ("Y-m-d", strtotime("+1 month", strtotime($strLoopDate)));
  }
echo '</table>';   
}

else {
    echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
    echo '<br>You are not in any Leave Groups.<br>Please contact your Scheduling Team administrator to get these assigned.<br><br>';
    echo '</div>';
}

?>
<script type="text/javascript">
  $('#loader').hide();
  function customtiptitleMLR(type='',currObj,rCounter=0){
    $('#loading').hide();
    if(type == 'showtooltip'){
      let totRowCount = parseInt('<?php echo ($rCounter-1);?>');
      let message = $(currObj).attr('data-title');
      if(message != ''){
        let tipHtml = '';
        tipHtml +='<table class="tablesmalltidy leave-tooltip-table" cellpadding="0">';
        tipHtml += '<tr class="leave-tooltip-table-background-white">';
        tipHtml += '<td colspan="2" style="height:23px; vertical-align:top; padding:3px; border: 1px solid #FFFFFF; border-collapse: collapse;">'+message+'</td>';
        tipHtml += '</tr>';
        tipHtml += '</table>';

        let nativeTopPos = $(currObj).offset().top;
        let nativeLeftPos = $(currObj).offset().left;
        let topPos = 0;
        let leftPos = 0;
        let messageWordCount = countWords(message);
        if((message == '-') || (message.toUpperCase() == 'U') || (message.toLowerCase() == 'leave')){
          if(totRowCount == rCounter){
            topPos = nativeTopPos-30;
            leftPos = nativeLeftPos;
          } else {
            topPos = nativeTopPos+25;
            leftPos = nativeLeftPos-10;
          }
        } else if(message.toLowerCase() == 'you have reached your limit this year!'){
          if(totRowCount == rCounter){
            topPos = nativeTopPos-30;
            leftPos = nativeLeftPos-190;
          } else {
            topPos = nativeTopPos+15;
            leftPos = nativeLeftPos-190;
          }
        } else {
          if(totRowCount == rCounter){
            if(messageWordCount >= 11 && messageWordCount < 15){
              topPos = nativeTopPos-40;
              leftPos = nativeLeftPos-170;
            }else if(messageWordCount >= 15){
              topPos = nativeTopPos-53;
              leftPos = nativeLeftPos-170;             
            }else{
              topPos = nativeTopPos-30;
              leftPos = nativeLeftPos-100;
            }
          } else {
            if(messageWordCount >= 11 && messageWordCount < 15){
              topPos = nativeTopPos+15;
              leftPos = nativeLeftPos-170;
            }else if(messageWordCount >= 15){
              topPos = nativeTopPos+15;
              leftPos = nativeLeftPos-170;              
            }else{
              topPos = nativeTopPos+20;
              leftPos = nativeLeftPos-50;
            }
          }
        }

        $('#customMLRTip').css('left',leftPos+'px');
        $('#customMLRTip').css('top',topPos+'px');
        $('#customMLRTip').html(tipHtml);
        $('#customMLRTip').show();
      }
    } else {
        $('#customMLRTip').hide();
        $('#customMLRTip').html('');
    }
  }

function countWords(str) {
    return str.split(/\s+/).filter(word => word !== '').length;
}

function setScrollPosition(thisVal)
{
  let left = $('#tblLeftPost').offset().left;
  $.cookie('leaveRequestLeftPosition', left);
}
$('.leavePageScroollbar').scrollLeft(Math.abs($.cookie('leaveRequestLeftPosition')));

function SendApplicationLeaveEmail(Login,pageflag) {
  $('*').qtip('hide');
  $.post("page-includes/leave/leave-send-application-emails.php", {
     login: Login
  },
    function(){
		if (pageflag==1){
			ShowUserLeavefromyearly('<?php echo $strUser;?>', "<?php echo $intLeaveYear;?>")
		} else {
			ShowLeave("<?php echo $intLeaveYear ?>");
		}
    }
  )
};

function ShowUserLeavefromyearly(user, year) {
  if (user != '') {
    $.ajax({
        type: 'POST',
        url: 'page-includes/leave/leave-yearly.php',
        data: {
            'year': year,
            'user': user,
            'admin': 1,
        },
        success: function (data) {
            document.getElementById('content').style.pointerEvents = 'auto';
            $('#loader').hide();
            $('#leaverecord').html(data);
        },
        error:function (data) {
            alert('some error found in leave yearly call.');
        }
    });
  }
}
</script>