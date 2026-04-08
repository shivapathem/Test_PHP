<?php
if (session_status() == PHP_SESSION_NONE) {
	  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/shiftleaderfunctions.php';
include_once '../../function-includes/editabledaystatus.php';
include_once '../../components/filters/filter-process.php';

$intDutyHeight  = 35;
$intRowHeight = 36;
$intScreenWidth =  $_SESSION['width'];
$intScreenHeight =  $_SESSION['height'];
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$UserID = GetUserIdbyNetlogin($strUser);
$intRowsAllowed  = round(($intScreenHeight - 100) / $intRowHeight);
$hoursmins = date ("H:i");
$intAnyAllocations = 0;
$currenttop = 50;
$intFilterID = $_REQUEST['filter'];
$arrFilter = GetFilterDataOptions($intFilterID);

if (isset($_REQUEST['teamId'])) {
  $intTeamID = $_REQUEST['teamId'];
}
else {
  $intTeamID = GetDefaultSchedulingTeamIdByLogin($UserID);
}

$filterQuery1 = '';
$filterQuery2 = '';
$filterQuery3 = $intTeamID;
$filterOrderStr = '';
$skillFilterDaily ='';
$dutyFilterDaily ='';
$jobFilterDaily ='';
$jobNameAll ='';
$jobLabelAll ='';

if (is_null($arrFilter['Description'])) {
  echo '<br><br><br><br><div style="width:60%; margin:0 auto; position:relative;">';
  echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
  echo '<br>You have chosen an invalid filter.<br>
        Please click <a href="index.php">here</a> to return to the home page
        and choose another.<br><br>';
  echo '</div><br>';
  die;
}

$strDescription = $arrFilter['Description'];
$arrTeamDefaults = GetTeamDefaults(0,$intTeamID);
$intHasGridChecks = $arrTeamDefaults[$intTeamID]['hasGridChecks'];
$intEditPeriod = $arrTeamDefaults[$intTeamID]['DailyEditPeriod'];
$intSignInDays = $arrTeamDefaults[$intTeamID]['SignInDays'];
$allowInBuilding = $arrTeamDefaults[$intTeamID]['AllowInBuilding'];
$intSortOrder = 0;

$arrCurrentFilter[0] = 0;
$arrCurrentFilter[1] = $intFilterID;

date_default_timezone_set('Europe/London');
$intCurrentHour = date("H");

$strCurrentDate = date("Y-m-d");
$strYesterday = date("Y-m-d", strtotime("-1 day", (strtotime($strCurrentDate))));
$strTomorrow = date("Y-m-d", strtotime("+1 day", (strtotime($strCurrentDate))));

// Yesterday

$getWeekandDayArr = GetAllocationWeekandDay($strYesterday);
$intWeek = $getWeekandDayArr['ixYearWeek'];
$intDay = $getWeekandDayArr['ixDayInWeek'];

  $arrStaffOptions = GetStaffOtionsByTeam($strUser, $UserID);
  $arrEditable = DatIsEditable($strCurrentDate, $intTeamID, $arrTeamDefaults);
  $lockUnlock = $arrEditable[0];

  $intIsShiftLeader = 2;
  if (isset($arrStaffOptions[$intTeamID]) && ($arrStaffOptions[$intTeamID]['isScheduler'] == 1 || $arrStaffOptions[$intTeamID]['isTeamAdmin']==1)) {
    $intIsShiftLeader=0;
  }

  if (isset($arrStaffOptions[$intTeamID]['isShiftLeader']) && $arrStaffOptions[$intTeamID]['isShiftLeader'] == 1 &&  $intIsShiftLeader != 0) {
    $intIsShiftLeader=1;
  }

  $intTimeEndSecs = ($intCurrentHour * 3600) + 86400;
  $arrAllocationsYesterday = ReadAllocationsDay($intWeek, $intDay,$intTeamID, $intSortOrder, $intIsShiftLeader,$filterQuery1,$filterQuery2,$filterQuery3,$filterOrderStr,$strYesterday,$strYesterday,$skillFilterDaily,$dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll);
  $arrAllocationsYesterday = json_decode($arrAllocationsYesterday,true);

  $arrAllocationsYesterday = ApplyTimeFilter($arrAllocationsYesterday, $intTimeEndSecs, -1);

  if (isset($arrAllocationsYesterday['assigned'])) {
    if(count($arrAllocationsYesterday['assigned']) > 0){
      $strFirstDate = $strYesterday;
    } else {
      $strFirstDate = $strCurrentDate;
    }
  }
  else {
    $strFirstDate = $strCurrentDate;
  }

//}
// #########################################################  Today
$getWeekandDayArr = GetAllocationWeekandDay($strCurrentDate);
$intWeek = $getWeekandDayArr['ixYearWeek'];
$intDay = $getWeekandDayArr['ixDayInWeek'];

$arrAllocationsToday = ReadAllocationsDay($intWeek, $intDay,$intTeamID, $intSortOrder, $intIsShiftLeader,$filterQuery1,$filterQuery2,$filterQuery3,$filterOrderStr,$strCurrentDate,$strCurrentDate,$skillFilterDaily,$dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll);
$arrAllocationsToday = json_decode($arrAllocationsToday,true);
$intTimeEndSecs = ($intCurrentHour * 3600);
$arrAllocationsToday = ApplyTimeFilter($arrAllocationsToday, $intTimeEndSecs, 0);
// #########################################################  Tomorrow

$getWeekandDayArr = GetAllocationWeekandDay($strTomorrow);
$intWeekTomorrow = $getWeekandDayArr['ixYearWeek'];
$intDayTomorrow = $getWeekandDayArr['ixDayInWeek'];

$arrAllocationsTomorrow = ReadAllocationsDay($intWeekTomorrow, $intDayTomorrow,$intTeamID, $intSortOrder, $intIsShiftLeader,$filterQuery1,$filterQuery2,$filterQuery3,$filterOrderStr,$strTomorrow,$strTomorrow,$skillFilterDaily,$dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll);
$arrAllocationsTomorrow = json_decode($arrAllocationsTomorrow,true);

$intTimeEndSecs = ($intCurrentHour * 3600) - 86400;
$arrAllocationsTomorrow = ApplyTimeFilter($arrAllocationsTomorrow, $intTimeEndSecs, 1);
$arrAllocations = [];
$arrAllocationsY = [];
$arrAllocationsTomr=[];
$arrAllocationsTod = [];

if(isset($arrAllocationsYesterday['assigned'])){
  $arrAllocationsY =  $arrAllocationsYesterday['assigned'][1];
}
if(isset($arrAllocationsToday['assigned'])){
  $arrAllocationsTod =  $arrAllocationsToday['assigned'][1];
}
if(isset($arrAllocationsTomorrow['assigned'])){
  $arrAllocationsTomr =  $arrAllocationsTomorrow['assigned'][1];
}
$arrAllocations = $arrAllocationsY + $arrAllocationsTod + $arrAllocationsTomr;

if(!empty($arrAllocations)){
  $arrAllocations = removeElementWithValue($arrAllocations, "duty", 'U');
  $arrAllocations = removeElementWithValue($arrAllocations, "duty", 'Absent');
  $arrAllocations = removeElementWithValue($arrAllocations, "duty", 'Leave');
  $arrAllocations = removeElementWithValue($arrAllocations, "duty", 'OFF Leave');
  $arrAllocations = removeElementWithValue($arrAllocations, "duty", 'Sick');
  $arrAllocations = removeElementWithValue($arrAllocations, "duty", 'U-Sick');
  $arrAllocations = removeElementWithValue($arrAllocations, "duty", '-Sick');
  $arrAllocations = removeElementWithValue($arrAllocations, "starttime", 0);
  $arrAllocations = removeElementWithValue($arrAllocations, "miscduty", 1);
}

  $dutyearlieststart = array();
  foreach ($arrAllocations as $row) {
     $dutyearlieststart[]=$row['starttime'];
  }

  if (!empty($dutyearlieststart) && count($dutyearlieststart)>0){
      $showdutystart = min($dutyearlieststart);
      $dutystart  = (($showdutystart/3600) - 1 );
      $mindutystart  =  intval(floor($dutystart));
  }  else {
    $mindutystart = 0;
  }

$earlieststart = 24;
$lateststart = 0;

$intRowCount = 0;
if(!empty($arrAllocations)){
  foreach ($arrAllocations as $dutyid => $value) {
    $starttime = $value["starttime"];
    $endtime = $value["endtime"];

    if ($earlieststart > $starttime) {
      $earlieststart = $starttime;
    }
    if ($lateststart < $endtime) {
      $lateststart = $endtime;
    }
    $intRowCount ++;
    if ($intRowCount > $intRowsAllowed) {
      break;
    }
  }
} else {
  $arrAllocations = [];
}

$earlieststart = $mindutystart;
$lateststart = 24;
$intHourwidth  = floor(($intScreenWidth - 250) / $lateststart);

  if(count($arrAllocationsTomorrow) > 0  ){
    if($arrAllocationsToday['showdutyend']==0){
      $showdutyend=  gmdate("H",$arrAllocationsToday['showdutyend'])+24;
    }else{
      $showdutyend = 24;
    }
} else {
   $showdutyend=  gmdate("H",$arrAllocationsToday['showdutyend'])+24;
}
$countallocations = count($arrAllocations);
  echo '<div id="bannernotext" style="width:'.(250 + ($intHourwidth * ($lateststart + $showdutyend+1))).'px;">';
  echo '<table width="100%">';
  echo '<tr>';
  echo '<td width="100px">';
  echo '</td>';
  echo '<td>';
  echo '<font size="3" font color="#FFFFFF">';
  echo 'Allocations for '.date("jS F Y - l", strtotime($strCurrentDate));
  echo '<br>Showing '.$arrTeamDefaults[$intTeamID]['Description'].' ('.$strDescription.')';
  echo '</font>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';

  if ($countallocations == 0) {
    echo '<br><br><br><br><div style="width:60%; margin:0 auto; position:relative;">';
    echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
    echo '<br>There ae no Allocations matching this Filter.<br>
          Please click <a href="index.php" style="text-decoration:underline;">here</a> to return to the home page
          and choose another.<br><br>';
    echo '</div><br>';
    die;
  }

  // The hours in the day....
  echo '<div style="position: absolute; width:250px; height:45px; left:0px; top:'.$currenttop.'px" id="fixed"  class="names">';
  echo '</div>';
  echo '<div style="position: absolute; width:250px; height:30px; left:0px; top:'.$currenttop.'px" id="fixed"  class="timebar">';
  echo '</div>';

  echo '<div style="position: absolute; height:45px; width:'.(($intHourwidth * ($lateststart + $showdutyend+1))).'px; left:250px; top:'.$currenttop.'px" id="top" class="names">';
  // Put the Days the view applies to
  // if the earliest start is negative (showing yesterday)
  if ($earlieststart < 0) {
    echo '<div style="text-align:center; width:'.($intHourwidth * abs($earlieststart)).'px;  position:absolute; left:0px; top:0px; height:15px" class="timebar">';
    echo '<b>'.date("l", strtotime("-1 Day", strtotime($strCurrentDate))).'</b>';
    echo '</div>';
  }
  else {
    echo '<div style="text-align:center; width:'.($intHourwidth * (24 - $earlieststart)).'px;  position:absolute; left:0px; top:0px; height:15px" class="timebar">';
    echo '<b>'.date("l", strtotime($strCurrentDate)).'</b>';
    echo '</div>';
  }

  if ($showdutyend == 0) {
    echo '<div style="text-align:center; width:'.($intHourwidth * ($lateststart + $showdutyend+1)).'px;  position:absolute; left:'.($intHourwidth * abs($earlieststart) + 1).'px; top:0px; height:15px" class="timebar">';
    echo '<b>'.date("l", strtotime($strCurrentDate)).'</b>';
  }else if ($earlieststart < 0){
    echo '<div style="text-align:center; width:'.($intHourwidth * ($lateststart - 24 + $showdutyend+1)).'px;  position:absolute; left:'.($intHourwidth * abs($earlieststart) + 1).'px; top:0px; height:15px" class="timebar">';
    echo '<b>'.date("l", strtotime($strCurrentDate)).'</b>';
  }
  else {
    echo '<div style="text-align:center; width:'.($intHourwidth * ($lateststart - 24 + $showdutyend+1)).'px;  position:absolute; left:'.($intHourwidth * (24 - $earlieststart) + 1).'px; top:0px; height:15px" class="timebar">';
    echo '<b>'.date("l", strtotime("+1 Day", strtotime($strCurrentDate))).'</b>';
  }
  echo '</div>';
    if ($earlieststart < 0){
        $final = $lateststart + $showdutyend - 24;
    }else{
        $final = $lateststart + $showdutyend;
    }
  for ($i=$earlieststart; $i <= ($final); $i++){
    echo '<div style="width:'.$intHourwidth.'px;  position:absolute; left:'.(($i - $earlieststart) * $intHourwidth).'px; top:15px">';

    if ($lateststart - $earlieststart > 35) {
      echo '<div class="timebar" style="position: absolute; left: 0px; top:0px; width:'.$intHourwidth.'px; height:15px">'.(gmdate("H",  $i * 3600)).'</div>';
    }
    else {
      echo '<div class="timebar" style="position: absolute; left: 0px; top:0px; width:'.$intHourwidth.'px; height:15px">'.(gmdate("H:00",  $i * 3600)).'</div>';
    }

    echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="../images/hourpointer.png" width="11" height="11" /></div>';

    echo '</div>';
  }
  echo '</div>';

  // ============================================================================== Shift Leaders
  $currenttop = $currenttop + 46;

  // ######################################################################### The names down the left side #########################################################################
  echo '<div  style="position: absolute; height:'.$intScreenHeight.'px; width:250px; left:0px; top:'.$currenttop.'px;" class="names" id="names">';
  $counter = 0;
  $intRowsCount = 0;
  foreach ($arrAllocations as $dutyid => $value) {
    // Get the sign in details so we can highlight the name if they havn't signed in......
    $flashit = '';
    $intHasBeenEdited = $value["iscopy"];
    $allowsignin = 0;
      if (($intSignInDays > 0) && (strtotime($strCurrentDate) >= strtotime(date("Y-m-d"))) && (strtotime($strCurrentDate) <= strtotime("+".$intSignInDays." days", strtotime(date("Y-m-d")))) && ($value["starttime"] > 0 || $value["endtime"] > 0) && (isset($value['duty']) && strtoupper($value['duty']) != 'U' && strtolower($value['duty']) != 'off leave' && strtolower($value['duty']) != 'leave') && $value['miscduty'] == 0 ) {
      $allowsignin = 1;
      $InBuilding = $value["inbuilding"];
      if (!isset($value["editable"])) {
        $editable = 1;
      }
      else {
        $editable = $value["editable"];
      }
      if ($editable == 1 && $allowInBuilding == 1 && $strCurrentDate == date("Y-m-d") && $value["inbuilding"] == 0 && (($value["starttime"] + 540) < (date('G') * 3600 + date('i') * 60))) {
        $flashit = ' flashit';
      }
      switch ($value["signin"]) {
        case 1:
          if ($InBuilding == 1) {
            // Signed in OK
            $img = 'blue_tick.png';
            $action = 0;
          }
          else {
            // Signed in OK
            $img = 'green_tick.png';
            $action = 0;
          }
          break;
         // Signed in NOT OK
        case 2:
          $img = 'messagebox_warning.png';
          $action = 1;
          break;
        default:
          // Not signed in
          //$flashit = '';
          $img = 'red_cross.png';
          $action = 1;
          break;
        }    }
    if ($flashit == '') {
      $hashit = '';
    }
    else {
     $hashit = 'cellflashhash';
    }
    $textcolour = $value["StaffTextColour"];
    if ($flashit != '') {
      $textcolour = '#000000';
    }
    echo '<div class="'.$hashit.$flashit.'  names person-data-cell" style="position: absolute; width:250px; height:'.($intRowHeight - 1).'px; left:0px; top:'.($counter * $intRowHeight).'px" data-id="'.$value['ScheduledPersonID'].'" data-order="'.$value["fullname"].'" data-sortcode="'.$value["sortcode"].'"  data-cost-code="' . $value["CostCode"] . '">';
    echo '<b><font color="'.$textcolour.'">'.$value["fullname"];
    echo '</b><br>';
    echo $value["sortcode"];
    echo '</font></div>';
    if ($allowsignin == 1) {
      echo '<div dutyid="'.$value['DutyID'].'" SchedulingPersonID="'.$value['ScheduledPersonID'].'" class="handcursor signedtip" style="position: absolute; width:12px; height:12px; left:215px; top:'.(($counter * $intRowHeight)).'px">';
      if (($strCurrentDate == date("Y-m-d") || $strCurrentDate == date("Y-m-d", strtotime("+1 Day"))) && $img == 'red_cross.png') {
        echo '<img src="../images/'.$img.'" border="0" height="18px" width="18px">';

      }
      else {
        echo '<img src="../images/'.$img.'" border="0" height="12px" width="12px">';

      }
      echo '</div>';
    }
    // Locks and requests

          if (isset($arrRequests[$value["StaffNumber"]][$intDay])) {
            if ($arrRequests[$value["StaffNumber"]][$intDay]['IsLock'] == 1) {
              $strTitle = 'Day Is Locked';
              $strImage = 'locked';
            }
            else {
              $strTitle = $arrRequests[$value["StaffNumber"]][$intDay]['Description'].'<br>';
              if ($arrRequests[$value["StaffNumber"]][$intDay]['Approved'] == 1) {
                $strTitle.= 'Approved';
                $strImage = 'requested';
              }
              else {
                if ($arrRequests[$value["StaffNumber"]][$intDay]['Approved'] == 1) {
                  $strTitle.= 'OK - Not yet Approved';
                  $strImage = 'requested';
                }
                else {
                  $strTitle.= 'Waiting List - Not yet Approved';
                  $strImage = 'requestedInQ';
                }
              }

            }
      echo '<div class="handcursor" style="position: absolute; width:12px; height:12px; left:190px; top:'.(($counter * $intRowHeight) + ($intRowHeight / 2) - 7).'px">';
      echo '<img title="'.$strTitle.'" width="15" height="15" border="0" src="../images/locks/'.$strImage.'.png"></img>';
      echo '</div>';

          }
    if (isset($arrRequests[$value['StaffNumber']][$intDay])) {

    }
    $counter++;
    $intRowsCount++;
    if ($intRowsCount > $intRowsAllowed) {
      // break;
    }
  }
  echo '</div>';
  // ######################################################################### END The names down the left side #########################################################################

  // ################################################################################# The duties in the div  #################################################################################
  echo '<div style="overflow:hidden; position: absolute; height:'.$intRowHeight * ($intRowsAllowed + 1).'px; width:2050px; left:250px; top:'.$currenttop.'px" id="duties" class="dayholderparthour">';

  $counter = 0;
  $intRowsCount = 0;

  drawtimecellsallocations ($strCurrentDate, $earlieststart, ($lateststart+ $showdutyend+1), $intHourwidth, ($intRowsAllowed + 1) * $intRowHeight, 0);

  foreach ($arrAllocations as $dutyid => $value) {
    echo '<div class="duties" style="position: absolute; width:'.($lateststart * $intHourwidth).'px; height:'.($intRowHeight - 1).'px; left:0px; top:'.($counter * $intRowHeight).'px">';
    $left = ((($value["starttime"] / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
    $right = ((($value["endtime"] / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;

    if (isset($value["backcolour"])) {
      $dutybackcolour = $value["backcolour"];
    }
    else {
      $dutybackcolour = '#ffffff';
    }
    if (isset($value["fontcolour"])) {
      $dutyfontcolour = $value["fontcolour"];
    }
    else {
      $dutyfontcolour = '#000000';
    }
    if ($right <= $left) {
      $right = $right + ($intHourwidth * 24);
    }
    $width = $right - $left;

    echo '<div id="'.$dutyid.'" class="boxed allocated_duty_' . ($value['ScheduledPersonID'] ?? 0).'" style="width: '.$width.'px; height: '.($intDutyHeight - 6).'px; position:absolute; left:'.$left.'px; top:3px; background-color:'.$dutybackcolour.'" data-duty-name="'.$value["duty"].'" data-duty-labels="' . implode(',', $value['DutyLabels'] ?? []) . '" data-duty-start-time="' . $value['starttime'] . '" data-duty-end-time="' . $value['endtime'] . '">';

    echo '<font color="'.$dutyfontcolour.'">';

    echo $value["duty"];

    if($value["starttime"]!=0 || $value["starttime"]!=''){

      echo ' ('.gmdate("H:i", $value["starttime"]).'-'.gmdate("H:i", $value["endtime"]).')';
    }else{

      echo '  >>>>';
    }

    echo '</font>';
    if ($value["internaledited"] == 1){
      echo '<div class="dailyisinternallyedited"></div>';
    }
    else
      if ($value["isEdited"] == 1) {
        echo '<div class="dailyisedited"></div>';
      }
    echo '</div>';
    $midval = isset($midval) ? $midval : 0;
    // The jobs.....
    if (isset($value['jobs'])) {
      foreach ($value['jobs'] as $jobid => $job) {

        $jobbackcolour = $job["backcolour"];
        $jobfontcolour = $job["fontcolour"];

        $jobid = $job["jobid"];
       if($value['starttime']> $job["starttime"] && $value['starttime']> $job["endtime"]){
        $job["starttime"]+=86400;
        $job["endtime"] +=86400;
        $left = (((($midval+$job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
			  $right = (((($midval+$job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
      }elseif( $job["starttime"] > $job["endtime"]){
        $job["endtime"] +=86400;
        $left = (((($midval+$job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
        $right = (((($midval+$job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
      }else{
        $left = (((($midval+$job["starttime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
			  $right = (((($midval+$job["endtime"]) / 3600) - $earlieststart + 1) * $intHourwidth) - $intHourwidth;
      }

        $width = $right - $left;
        $jobqtipcontent = '<font color=#99CCFF>'.$job["jobname"].'</font><br>';
        $jobqtipcontent.=  gmdate("H:i", $job["starttime"]).'-'.gmdate("H:i", $job["endtime"]);
        if ($job["comments"] != '') {
          $jobqtipcontent.= '<br><font color=#00CC00>';
          $jobqtipcontent.= $job["comments"].'</font>';
        }
        $jobqtipcontent.= '<br>('.$job["programme"].')';

        $jobqtipcontent = htmlspecialchars($jobqtipcontent);
        echo '<div align="center" qtip-content="'.$jobqtipcontent.' allocated_duty_job_' . ($value['ScheduledPersonID'] ?? 0).'" id="'.$jobid.'" dutyid="'.$dutyid.'" dutytype="0"  class="boxed tipjob" style="overflow:hidden; width: '.$width.'px; height: '.(($intDutyHeight / 2) - 5).'px; position:absolute; left:'.$left.'px; top:'.($intDutyHeight / 2).'px; background-color:'.$jobbackcolour.'" data-job-name="' . $job["jobname"] . '" data-job-programme="' . $job['programmeid'] . '">';

        echo '<font color="'.$jobfontcolour.'">'.$job["jobname"].'</font>';

        if ($job["edited"] == 1) {
          echo '<div class="dailyisedited"></div>';
        }
        echo '</div>';
      }
    }

    echo '</div>';
    $counter++;
    $intRowsCount++;
    if ($intRowsCount > $intRowsAllowed) {
      // break;
    }
  }
  echo '</div>';
  ################################################################################# END The duties in the div  #################################################################################

function drawtimecellsallocations ($strCurrentDate, $earlieststart, $lateststart, $intHourwidth, $height, $dark = 0) {
  for ($hr = 0; $hr <= $lateststart; $hr = $hr + 0.25) {
    if ($dark == 1) {
      if ($hr == intval($hr)) {
        $class = 'dayholderdarkhour';
      }
      else {
        $class = 'dayholderdarkparthour';
      }
    }
    else {
      if ($hr == intval($hr)) {
        $class = 'dayholderhour';
      }
      else {
        $class = 'dayholderparthour';
      }
    }
    echo '<div class="'.$class.'" style="width:'.floor($intHourwidth / 4).'px; height: '.($height).'px; position: absolute; left:'.$intHourwidth * $hr.'px; top:0px"></div>';
    if (strtotime($strCurrentDate) == strtotime(date("Y-m-d"))) {
      //date_default_timezone_set('Europe/London');
      $now = date("H") + (date("i") / 60);
      // and a line at the current time (ish)
      $currtime = round(($now - $earlieststart) * $intHourwidth);
      echo '<div class="highlightedtransparent" style="width:2px; height: '.($height).'px; position: absolute; left:'.$currtime.'px; top:0px"></div>';
    }
  }
}
?>
<script type="text/javascript">
$(document).ready( function () {
  setTimeout(function(){
      applyViewDailyFilterAutopages();
  }, 100);
})
function applyViewDailyFilterAutopages() {
    $('#loading').show();
    var StaffNameFilter = '<?php echo $request->get('StaffNameFilter')?>';
    var StaffName = '<?php echo $request->get('StaffName')?>';
    var SortCodeFilter = '<?php echo $request->get('SortCodeFilter')?>';
    var SortCode = '<?php echo $request->get('SortCode')?>';
    var CostCodeFilter = '<?php echo $request->get('CostCodeFilter')?>';
    var CostCode = '<?php echo $request->get('CostCode')?>';
    var SkillFilter = '<?php echo $request->get('SkillFilter')?>';
    var Skill = '<?php echo $request->get('Skill')?>';
    var DutyFilter = '<?php echo $request->get('DutyFilter')?>';
    var Duty = '<?php echo $request->get('Duty')?>';
    var DutyLabelFilter = '<?php echo $request->get('DutyLabelFilter')?>';
    var DutyLabel = '<?php echo $request->get('DutyLabel')?>';
    var startDate = '<?php echo $strCurrentDate?>';
    var endDate = '<?php echo $strCurrentDate?>';
    var teamId = '<?php echo $request->get('teamId')?>';
    var DutyTime = '<?php echo $request->get('DutyTime')?>';
    var JobNameFilter = '<?php echo $request->get('JobNameFilter')?>';
    var JobName = '<?php echo $request->get('JobName')?>';
    var JobLabelFilter = '<?php echo $request->get('JobLabelFilter')?>';
    var JobLabel = '<?php echo $request->get('JobLabel')?>';
    var groupCondition = '<?php echo $request->get('andMatch')?>';

    let filteredData = [];
// $strCurrentDate = date("Y-m-d");
// $strYesterday = date("Y-m-d", strtotime("-1 day", (strtotime($strCurrentDate))));
// $strTomorrow = date("Y-m-d", strtotime("+1 day", (strtotime($strCurrentDate))));
    var scheduledPersonAllSkills = [];
    if(Skill) {
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/filters/filters-get-scheduled-person-skills.php",
            data: {
                startDate: startDate,
                endDate: endDate,
                teamId: teamId
            },
            async: false,
            success: function (response) {
                scheduledPersonAllSkills = (response['status'] == true) ? response['data'] : [];
            }
        });
    }

    $('.person-data-cell').each(function() {
        let id = $(this).attr('data-id');
        let matchFound = true;
        let groupOr = false;


        if (StaffName) {
            let searchData = $(this).attr('data-order');
            let names = StaffName.split(',').map(s => s.trim());
            let containsMatch = names.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = names.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = names.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = names.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            var staffNameLoopMatch = true;
            if (StaffNameFilter === "*" && !containsMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === "!" && !notContainsMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === ";" && !(exactSomeMatch)) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === "__AND__" && !exactMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && staffNameLoopMatch) {
                groupOr = true;
            }
        }

        if (SortCode) {
            let searchData = $(this).attr('data-sortcode') ?? '';
            let codes = SortCode.split(',').map(s => s.trim());
            let containsMatch = codes.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = codes.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = codes.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = codes.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            let sortCodeLoopMatch = true;
            if (SortCodeFilter === "*" && !containsMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === "!" && !notContainsMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === ";" && !(exactSomeMatch)) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === "__AND__" && !exactMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && sortCodeLoopMatch) {
                groupOr = true;
            }
        }

        if (CostCode) {
            let searchData = $(this).attr('data-cost-code') ?? '';
            let codes = CostCode.split(',').map(s => s.trim());
            let containsMatch = codes.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = codes.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = codes.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = codes.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            let costCodeLoopMatch = true;
            if (CostCodeFilter === "*" && !containsMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === "!" && !notContainsMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === ";" && !(exactSomeMatch)) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === "__AND__" && !exactMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && costCodeLoopMatch) {
                groupOr = true;
            }
        }

        if (Duty) {
            let dutyNames = [];
            $('.allocated_duty_' + id).each(function () {
                dutyNames.push($(this).attr('data-duty-name'));
            });

            let duties = Duty.split(',').map(s => s.trim());
            let containsMatch = duties.some(name => new RegExp(name, "i").test(dutyNames));
            let notContainsMatch = duties.every(name => !new RegExp(name, "i").test(dutyNames));
            let exactMatch = duties.every(name =>
                dutyNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = duties.some(name =>
                dutyNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let startsWithSomeMatch = duties.some(name =>
                 dutyNames.some(dn => dn.startsWith(name))
            );

            let dutyLoopMatch = true;
            if (DutyFilter === "*" && !containsMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === "!" && !notContainsMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === ";" && !(exactSomeMatch)) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === "__AND__" && !exactMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if(DutyFilter === "%" && !startsWithSomeMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && dutyLoopMatch) {
                groupOr = true;
            }
        }

        if(Skill) {
            let scheduledPersonSkillsLabels = [];

            let scheduledPersonId = id;
            let filteredSkills = typeof scheduledPersonAllSkills[scheduledPersonId] !== 'undefined' ? scheduledPersonAllSkills[scheduledPersonId] : [];
            filteredSkills.forEach(function(item, index) {
                scheduledPersonSkillsLabels.push(item);
            });

            let skills = Skill;
            let containsMatch = skills.some(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let notContainsMatch = skills.every(name =>
                !scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactMatch = skills.every(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = skills.some(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );

            let skillLoopMatch = true;
            if (SkillFilter === "*" && !containsMatch) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === "!" && !notContainsMatch) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === ";" && !(exactSomeMatch)) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === "__AND__" && !exactMatch) {
                skillLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && skillLoopMatch) {
                groupOr = true;
            }
        }

        if (DutyLabel) {
            let dutyLabels = [];

            $('.allocated_duty_' + id).each(function () {
                let filteredLabels = $(this).attr('data-duty-labels').split(',').map(s => s.trim());
                filteredLabels.forEach(function(item, index) {
                    dutyLabels.push(item);
                });
            });

            let labels = DutyLabel;
            let containsMatch = labels.some(name => new RegExp(name, "i").test(dutyLabels));
            let notContainsMatch = labels.every(name => !new RegExp(name, "i").test(dutyLabels));
            let exactMatch = labels.every(name =>
                dutyLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = labels.some(name =>
                dutyLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );

            let dutyLabelLoopMatch = true;
            if (DutyLabelFilter === "*" && !containsMatch) {
                dutyLabelLoopMatch = false;
                matchFound = false;
            } else if (DutyLabelFilter === "!" && !notContainsMatch) {
                dutyLabelLoopMatch = false;
                matchFound = false;
            } else if (DutyLabelFilter === ";" && !(exactSomeMatch)) {
                dutyLabelLoopMatch = false;
                matchFound = false;
            } else if (DutyLabelFilter === "__AND__" && !exactMatch) {
                dutyLabelLoopMatch = false;
                matchFound = false;
            }

            if((groupCondition && dutyLabelLoopMatch)) {
                groupOr = true;
            }
        }

        if (DutyTime && DutyTime != '-1') {
            let $duty = $('.allocated_duty_' + id);
            let dutyStartTime = $duty.attr('data-duty-start-time');
            let dutyEndTime = $duty.attr('data-duty-end-time');
            let dutyName = $duty.attr('data-duty-name');

            containsMatch = (DutyTime >= dutyStartTime && DutyTime <= dutyEndTime && dutyName.toLowerCase() != 'u');

            let dutyTimeLoopMatch = true;
            if (DutyFilter === "*" && !containsMatch) {
                dutyTimeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && dutyTimeLoopMatch) {
                groupOr = true;
            }
        }

        if (JobName) {
            let jobNames = [];
            $('.allocated_duty_job_' + id).each(function () {
                jobNames.push($(this).attr('data-job-name'));
            });

            let jobs = JobName.split(',').map(s => s.trim());
            let containsMatch = jobs.some(name => new RegExp(name, "i").test(jobNames));
            let notContainsMatch = jobs.every(name => !new RegExp(name, "i").test(jobNames));
            let exactMatch = jobs.every(name =>
                jobNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = jobs.some(name =>
                jobNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let startsWithSomeMatch = jobs.some(name =>
                 jobNames.some(dn => dn.startsWith(name))
            );

            let jobLoopMatch = true;
            if (JobNameFilter === "*" && !containsMatch) {
                jobLoopMatch = false;
                matchFound = false;
            } else if (JobNameFilter === "!" && !notContainsMatch) {
                jobLoopMatch = false;
                matchFound = false;
            } else if (JobNameFilter === ";" && !(exactSomeMatch)) {
                jobLoopMatch = false;
                matchFound = false;
            } else if (JobNameFilter === "__AND__" && !exactMatch) {
                jobLoopMatch = false;
                matchFound = false;
            } else if(JobNameFilter === "%" && !startsWithSomeMatch) {
                jobLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && jobLoopMatch) {
                groupOr = true;
            }
        }

        if (JobLabel) {
            let jobLabels = [];

            $('.allocated_duty_job_' + id).each(function () {
                jobLabels.push($(this).attr('data-job-programme'));
            });

            let labels = JobLabel;
            let containsMatch = labels.some(name => new RegExp(name, "i").test(jobLabels));
            let notContainsMatch = labels.every(name => !new RegExp(name, "i").test(jobLabels));
            let exactMatch = labels.every(name =>
                jobLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = labels.some(name =>
                jobLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );

            let jobLabelLoopMatch = true;
            if (JobLabelFilter === "*" && !containsMatch) {
                jobLabelLoopMatch = false;
                matchFound = false;
            } else if (JobLabelFilter === "!" && !notContainsMatch) {
                jobLabelLoopMatch = false;
                matchFound = false;
            } else if (JobLabelFilter === ";" && !(exactSomeMatch)) {
                jobLabelLoopMatch = false;
                matchFound = false;
            } else if (JobLabelFilter === "__AND__" && !exactMatch) {
                jobLabelLoopMatch = false;
                matchFound = false;
            }

            if((groupCondition && jobLabelLoopMatch)) {
                groupOr = true;
            }
        }

        if (matchFound || groupOr) {
            filteredData.push(id);
        }
    });

    $('.filter-hide-row').removeClass('filter-hide-row');
    var totalVisible = 0;
    $('.person-data-cell').each(function() {
        if(filteredData.includes($(this).attr('data-id')) == false) {
          $(this).addClass('filter-hide-row');
          // $('#locks_' + $(this).attr('data-id')).addClass('filter-hide-row');
          // $('#Allocatednames div[dataschpersonid="' + $(this).attr('data-id') + '"]').addClass('filter-hide-row');
          $('.allocated_duty_' + $(this).attr('data-id')).parent().addClass('filter-hide-row');
          $('.names .signedtip[schedulingpersonid="' + $(this).attr('data-id') + '"]').addClass('filter-hide-row');
        } else {
          let top = <?php echo $intRowHeight ?> * totalVisible;
          $(this).css("top", top);
          $('.allocated_duty_' + $(this).attr('data-id')).parent().css("top", top);
          // $('#locks_' + $(this).attr('data-id')).css("top", top + 22);
          // $('#Allocatednames div[dataschpersonid="' + $(this).attr('data-id') + '"]').css("top", top + 28);
          $('.names .signedtip[schedulingpersonid="' + $(this).attr('data-id') + '"]').css("top", top + 4);
          totalVisible++;
        }
    });

    let timeCellHeight = <?php echo $intRowHeight ?> * totalVisible;

    $('#Allocatedduties .dayholderparthour').each(function() {
      $(this).height(timeCellHeight);
    });
    $('#Allocatedduties .dayholderhour').each(function() {
      $(this).height(timeCellHeight);
    });

    $('#loading').hide();
}
</script>

