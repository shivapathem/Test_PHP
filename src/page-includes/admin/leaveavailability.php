<?php
 if (session_status() === PHP_SESSION_NONE) {
     session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
if (isset($_REQUEST['year'])) {
  $leaveyear = $_REQUEST['year'];
}
else {
  if (isset($_REQUEST['date'])) {
    $leaveyear = date ("Y", strtotime("-3 months", strtotime($_REQUEST['date']))); 
  }
  else {
    $leaveyear = date("Y", strtotime("-3 months"));
  }
}

$intLeaveGroupID = $_REQUEST['id'];
$intAdminLevel = GetAdminLeaveRequestGroupsAdminFromLogin ($strUser, $intLeaveGroupID);

$_SESSION['allocations']["leave"]['curentleaveyear'] = $leaveyear; 
$previousyear = $leaveyear - 1;
$nextyear = $leaveyear + 1;
$startdate = "$leaveyear-04-01";
$enddate = date("Y", strtotime("+1 year",strtotime($startdate)))."-03-31";  
$leaveends = date("Y-m-d", strtotime("+1 year")); 

$arrleave = GetLeaveAllowed($intLeaveGroupID, $startdate, $enddate); 

if (isset($arrleave)) {
  // The Header
  echo '<div id="tabcontent">';
  echo '<table class="tablesmall" width="100%">';
  echo'<td class="lightcell medtextbold" width="350px"><br>Leave for Year '.$leaveyear.'<br>'.$arrleave[$intLeaveGroupID]['Description'].'<br><br></td>';
  if ($intAdminLevel == 2) {
    echo '<td class="lightcell medtextbold handcursor" onclick="javascript:EditLeaveAvailability('.$intLeaveGroupID.','.$leaveyear.')";>Edit <img border="0" src="images/edit.gif" width="18" height="17"></td>';
  } 
  echo '<td class="lightcell smalltext handcursor" align="right" width="100px" nowrap="nowrap" onclick="javascript:ShowLeaveAvalability('.$intLeaveGroupID.',\''.$previousyear.'\')";>&nbsp;&lt;&lt; Year '.$previousyear.'</a></td>';
  echo '<td class="lightcell smalltext" align="right" width="50px" nowrap="nowrap">&nbsp;</td>';
  echo '<td class="lightcell smalltext handcursor" width="100px" nowrap="nowrap" onclick="javascript:ShowLeaveAvalability('.$intLeaveGroupID.',\''.$nextyear.'\')";>Year '.$nextyear.' &gt;&gt;</a></td>';
  echo '</tr>';
  echo '</table>';
  // Start with the table and write the days across the top 
  echo '<table class="tablesmall width100Percent"><tr>'; 
  echo '<td class="tableheadersmall width100Percent"><div class="tableheadersmall width100Percent"></div></td>';
  for ($i=0; $i <= 5; $i++) {
   for ($ii=0; $ii <= 6; $ii++) {
     echo '<td class="tableheadersmall"><div class="tableheadersmall smalltextcentre box24x15unsetwidth25">'.substr($invdowMap[$ii], 0, 1).'</div></td>'; 
   }
  }
  echo '</tr>'; 

  // Now start on the months
  $mdate = $startdate;

  while (strtotime($mdate) <= strtotime($enddate)) {
    echo '<tr>'; 
    echo '<td rowspan="2" class="tableheadersmall"><div class="smalltextcentre">'; 
    echo date("M", strtotime($mdate));
    echo '</div></td>';
    for ($iweek = 0; $iweek <= 5; $iweek++){
      $currweekstarts = date("Y-m-d", strtotime("+".($iweek * 7)." days", strtotime($mdate)));
      $getWeekandDayArr = GetAllocationWeekandDay($currweekstarts);
      $currweek = $getWeekandDayArr['ixYearWeek']; 
   
     echo '<td colspan="7" class="tableheadersmall" align="center">';
     // Is the month equal to the current one?
     if (date("m", strtotime(datefromweek($currweek))) <= date("m", strtotime($mdate))) {
 	     echo spinweek($currweek);
     }
     echo '</td>';  
    }
     echo '</tr>'; 
     echo '<tr>';   
    // Now put spacers in for the Saturday to the first day
    $ddate  = $mdate;
    $daysinmonth = date("t", strtotime($ddate));
    $startdaynumber = $dowMap[date("D", strtotime($ddate))];
    for ($i=1; $i <= $startdaynumber; $i++) {
      echo '<td class="lightcell"></td>';
    }
 
    $nextmonth = strtotime("+1 month", strtotime($ddate));
    while (strtotime($ddate) < $nextmonth) {
      echo '<td valign="top" class="lightcell">';
      // The day of the month
      echo '<div class="medlightcell smalltextcentre box24x15">';
      echo date("d", strtotime($ddate));
      echo '</div>';
      foreach ($arrleave[$intLeaveGroupID]['Types'] as $lt => $arrLeaveType) {      
       if ($arrLeaveType['dates'][$ddate]['available'] !=999) {
          if (strtotime($ddate) < time()) {
            echo '<div class="VeryLightBlue smalltextcentre box24x15 handcursor borderbottomwhite">';          
          }        
          else {
            if ($arrLeaveType['dates'][$ddate]['default'] == $arrLeaveType['dates'][$ddate]['available']) {
              if ($intAdminLevel == 2) {      
                echo '<div class="LightGreen smalltextcentre box24x15 handcursor borderbottomwhite" onclick="javascript:EditLeaveAvailabilityDay('.$intLeaveGroupID.',\''.$ddate.'\')";>';
              }
              else {
                echo '<div class="LightGreen smalltextcentre box24x15 borderbottomwhite">';              
              }
            }
            else {
              if ($intAdminLevel == 2) {              
                echo '<div class="LightYellow smalltextcentre box24x15 tipClass handcursor borderbottomwhite" title="Default Amount '.$arrLeaveType['dates'][$ddate]['default'].'" onclick="javascript:EditLeaveAvailabilityDay('.$intLeaveGroupID.',\''.$ddate.'\')";>';      
              }
              else {
                echo '<div class="LightYellow smalltextcentre box24x15 tipClass handcursor borderbottomwhite" title="Default Amount '.$arrLeaveType['dates'][$ddate]['default'].'">';      
              }
            }
          }
         echo $arrLeaveType['dates'][$ddate]['available'];
         echo '</div>';
       }
      }
      echo '</td>';   	
      $ddate = date ("Y-m-d", strtotime("+1 day", strtotime($ddate)));
    }
    // Now write in spaces at the end of the month
    for ($i=($daysinmonth + $startdaynumber); $i <= 41; $i++) {
      echo '<td class="lightcell"></td>';
    }   
    echo '</tr>'; 
	  $mdate = date ("Y-m-d", strtotime("+1 month", strtotime($mdate)));
  } 

  echo '</table><br><br><br>'; 
  }
else {
  echo '<div class="tableheadersmall medtextboldcentre" style="width: 1375px">';
  echo '<br>There are no Leave Types defined<br>Please create one or more before adjusting the availablity<br><br>';
  
  echo '</div>';

}
echo '</div>';

?>

<script type="text/javascript">

$('[title]').qtip({
  style: { classes: 'qtip-rounded qtip-shadow qtip-dark'},
  show: {
        solo: true
      }
});  
$('.qtip').click(function(event) {
    api.toggle(false); 
})

</script> 