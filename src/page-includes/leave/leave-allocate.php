<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/userfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';

if (isset($_POST['user'])) {
  $strUser = $_POST['user'];
}
else {
  $strUser = GetUserLogon();
}
$arrStaffDetail = GetStaffDetailsByLogon($strUser);

if(isset($_POST['teamID'])) {
  $intTeamtID = $_POST['teamID'];
}
else {
  $intTeamtID = 0;
}

$strStaffNumbers = $arrStaffDetail['StaffNumber'];
$scheduledPersonID = $arrStaffDetail['ScheduledPersonID'];
$fullname = $arrStaffDetail['userDisplayName'];

if(isset( $_POST['year'])) {
  $intLeaveYear = $_POST['year'];
}
else {
  if (isset($_SESSION['allocations']["leave"]['curentleaveyear'])) {
    $intLeaveYear = $_SESSION['allocations']["leave"]['curentleaveyear'];
  }
  else {
    $intLeaveYear = date("Y", strtotime("-3 months"));
  }
}
$intPreviousYear = $intLeaveYear - 1;
$intNextYear = $intLeaveYear + 1;
$_SESSION['allocations']["leave"]['curentleaveyear'] = $intLeaveYear;
$arrAllocLeaveTypes = GetLeaveAllocateTypes();
$arrLeaveCredits = GetLeaveCredits($strStaffNumbers,$scheduledPersonID, $intLeaveYear);
$arrLeaveTaken = GetLeaveTakenIndivdualReport ($scheduledPersonID, $intLeaveYear,1);

// Go through the Allocate Leave Tyoes and unset....
foreach ($arrAllocLeaveTypes as $intArrayID => $arrAllocLeaveType) {
  if (($arrAllocLeaveType['ShowZero'] == 0) || ($arrStaffDetail['leaveSelectiveHide'] == 1)) {
    $strLeaveType = $arrAllocLeaveType['AllocName'];  
    if (($arrStaffDetail['leaveSelectiveHide'] == 1) && ($arrAllocLeaveType['SelectiveHide']) || ($arrLeaveCredits['Types'][$strLeaveType] == 0 && $arrLeaveTaken['Types'][$intArrayID] == 0)) {
      unset($arrAllocLeaveTypes[$intArrayID]);
    }
  }
}  

echo '<h1 class="sr-only">My Leave Record</h1>';
echo'<table class="tablesmall" width="100%">';
echo'<tr>';
echo'<td colspan="6" class="lightcell medtextbold"><br>Leave Record for '.$fullname.'<br><br></td>';
echo'</tr>';
echo'<tr>';
echo'<td colspan="2" class="lightcell medtextbold" valign="top">Leave for Year '.$intLeaveYear.'</td>';
if ($intTeamtID == 0) {
  echo'<td class="lightcell smalltext handcursor" align="right" valign="top" width="100px" nowrap="nowrap" onclick="javascript:ShowAllocateLeave('.$intPreviousYear.')";>&nbsp;&lt;&lt; Year '.$intPreviousYear.'</a></td>';
  echo'<td class="lightcell smalltext" align="right" valign="top" width="50px" nowrap="nowrap">&nbsp;</td>';
  echo'<td class="lightcell smalltext handcursor" valign="top" width="100px" nowrap="nowrap" onclick="javascript:ShowAllocateLeave('.$intNextYear.')";>Year '.$intNextYear.' &gt;&gt;</a></td>';
}
else {
  echo'<td class="lightcell smalltext handcursor" align="right" valign="top" width="100px" nowrap="nowrap" onclick="javascript:ShowLeaveBalances(\''.$strUser.'\','.$intPreviousYear.','.$intTeamtID.')";>&nbsp;&lt;&lt; Year '.$intPreviousYear.'</a></td>';
  echo'<td class="lightcell smalltext" align="right" valign="top" width="50px" nowrap="nowrap">&nbsp;</td>';
  echo'<td class="lightcell smalltext handcursor" valign="top" width="100px" nowrap="nowrap" onclick="javascript:ShowLeaveBalances(\''.$strUser.'\','.$intNextYear.','.$intTeamtID.')";>Year '.$intNextYear.' &gt;&gt;</a></td>';
}


echo'</tr>';
echo'<tr>';
echo'</table>';

  echo '<div class="tableheadersmall medtextcentre" style="width:100%">';
  echo '<br><b>Leave entered into Allocate for '.$intLeaveYear.'</b><br>';
  echo '(1st April '.$intLeaveYear.' - 31st March '.($intLeaveYear + 1).')';
  if ($fullname != '') {
    echo '<br><b>Showing '.$fullname.'</b>';
  }

echo '<br><br>';
echo '</div>';
echo '<br>';


echo '<table width="100%" class="tablesmalltidy">';
if (isset($arrLeaveCredits['Leave'])) {
  echo '<tr>';
  echo '<th colspan="'.(count($arrAllocLeaveTypes) + 4).'" height="30px">';
  echo 'Leave Credits';
  echo '</th>';
  echo '</tr>';

  echo '<tr height="30px">';
  echo '<th colspan="2">';
  echo 'Date Credited';
  echo '</th>';
  foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    echo '<th width="100px">';
    if ($arrAllocLeaveType['HasCredits'] == 1) { 
      echo $arrAllocLeaveType['Description'];
     }  
     echo '</th>'; 
      
  }
  echo '<th class="width20Percent">';
  echo 'Comments';
  echo '</th>';
  echo '<th class="width20Percent">';
  echo ' Credit Team';
  echo '</th>';
  echo '</tr>';
  foreach ($arrLeaveCredits['Leave'] as $intLeaveID => $arrLeaveCredit) {
    echo '<tr>';
    echo '<td  colspan="2">';
    echo date("jS F Y", strtotime($arrLeaveCredit['Date']));
    echo '</td>';

    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      echo '<td>';
      
        if ($arrAllocLeaveType['HasCredits'] == 1) {
         
        if (isset($arrLeaveCredit[$arrAllocLeaveType['AllocName']])) {
          echo number_format(($arrLeaveCredit[$arrAllocLeaveType['AllocName']]),2, '.', '');
          if (isset($arrTotalCredits[$LTID])) {
            $arrTotalCredits[$LTID] = $arrTotalCredits[$LTID] + $arrLeaveCredit[$arrAllocLeaveType['AllocName']];
          }
          else {
            $arrTotalCredits[$LTID] = $arrLeaveCredit[$arrAllocLeaveType['AllocName']];
          }
        }
        echo '</td>';
       } 
        
      
    }
    echo '<td class="wordsBreak">';
    echo $arrLeaveCredit['Comments'];
    echo '</td>';
    echo '<td>';
    echo $arrLeaveCredit['schedulingTeamName'];
    echo '</td>';
    echo '</tr>';
  }
}
// Totals
echo '<tr height="30px">';
echo '<th colspan="2">';
echo '</th>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  echo '<th>';
  if (isset($arrTotalCredits[$LTID])) {
    echo number_format(($arrTotalCredits[$LTID]),2, '.', '');
  }
  else {
    $arrTotalCredits[$LTID] = 0;  
  }
  
  echo '</th>';
}
echo '<th colspan="2">';
echo '</th>';
echo '</tr>';


echo '<th colspan="'.(count($arrAllocLeaveTypes) + 4).'" height="30px">';
echo 'Leave Taken';
echo '</th>';
echo '</tr>';

echo '<tr height="30px">';
echo '<th colspan="2">';
echo 'Dates';
echo '</th>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  echo '<th>';
  echo $arrAllocLeaveType['Description'];
  echo '</th>';
}
echo '<th colspan="2">';
echo 'Comments';
echo '</td>';
echo '</tr>';
  if (isset($arrLeaveTaken['Leave'])) {
    foreach ($arrLeaveTaken['Leave'] as $intID => $arrLeaveEntry){
      echo '<tr>';
      if (isset($arrLeaveEntry['Summary'])) {
        echo '<td width="200px" title ="<table class=tablesmalltidy><tr><th colspan=4>Breakdown</th></tr>'.$arrLeaveEntry['Summary'].'</table>">';      
      }
      else {
        echo '<td width="200px">';
      }      
      
      
      echo date('l, jS F Y', strtotime($arrLeaveEntry['StartDate']));
      echo '</td>';
      
      echo '<td width="200px">';
      echo date('l, jS F Y', strtotime($arrLeaveEntry['EndDate']));
      echo '</td>';
      
      
          
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      echo '<td>';
      if (isset($arrLeaveEntry[$LTID])) {
        $intDuration = $arrLeaveEntry[$LTID]['Amount'];
        echo number_format(($intDuration),2, '.', '');
        if (isset($arrLeaveEntry[$LTID]['Type'])) {
          echo '<br>'.$arrLeaveEntry[$LTID]['Type'];
        }
        if (isset($arrTotalTaken[$LTID])) {
          $arrTotalTaken[$LTID] = $arrTotalTaken[$LTID] + $intDuration;
        }
        else {
          $arrTotalTaken[$LTID] = $intDuration;
        }
    }    
    echo '</td>';    
    }    
    echo '<td colspan="2">'; 
    echo $arrLeaveEntry['Comments']    ;
    echo '</td>';     
    echo '</tr>';
    }
  }

// Taken
echo '<tr height="30px">';
echo '<th colspan="2">';
echo 'Total Taken';
echo '</th>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  echo '<th>';
    if (isset($arrTotalTaken[$LTID])) {
      echo number_format(($arrTotalTaken[$LTID]),2, '.', '');
    }
  echo '</th>';
}
echo '<th colspan="2">';
echo '</th>';

// Balance
echo '<tr height="30px">';
echo '<th colspan="2">';
echo 'Balance';
echo '</th>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  echo '<th>';
  if ($arrAllocLeaveType['HasCredits'] == 1) {
    if (isset($arrTotalTaken[$LTID])) {
      echo number_format(($arrTotalCredits[$LTID] - $arrTotalTaken[$LTID]),2, '.', '');
    }
    else {
        echo number_format(($arrTotalCredits[$LTID]),2, '.', '');
    }
  }
  
  echo '</th>';
}
echo '<th colspan="2">';
echo '</th>';




echo '</tr>';
echo '</table>';


?>

<script type="text/javascript">
$(document).ready( function () {
  $('[title]').qtip({
        position: {
         my: 'top left',
        at: 'bottom left',
        viewport: $(window)
      },
    style: { classes: 'qtip-rounded qtip-shadow qtip-light'}
  }); 
})

</script>