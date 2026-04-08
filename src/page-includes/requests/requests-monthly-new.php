<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/locksfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';


if (isset($_REQUEST['user'])) {
  $strUser = $_REQUEST['user'];
}
else {
  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
}

if (isset($_REQUEST['date'])) {
  $dteStartDate = date("Y-m-01", strtotime($_REQUEST['date']));
}
else {
  $dteStartDate = date("Y-m-01", time());
}
$strFullCurrentMonth  = date("M Y", strtotime($dteStartDate));
$strPreviousMonth =  date ("Y-m-d", strtotime("-1 month", strtotime($dteStartDate)));
$strNextMonth =  date ("Y-m-d", strtotime("+1 month", strtotime($dteStartDate)));

// New Code uses a function to read all the requests and types.
// Passing the start date and staff number.
// Because some requests can be weekly the function will read from before the start of the month until the end of the week after the 1 month period.
$dteEndDate = date ("Y-m-d", strtotime("+1 month", strtotime($dteStartDate)));
$arrRequests = GetRequests($strUser, $dteStartDate, $dteEndDate);
$intDaysInMonth = cal_days_in_month(CAL_GREGORIAN, date("m", strtotime($dteStartDate)), date("Y", strtotime($dteStartDate)));


//echo '<pre>';
//print_r($arrRequests);

// ########################################## Do the header
echo'<table class="tablesmall" width="1200px">';
echo'<tr height="40px">';
echo'<td class="lightcell smalltext handcursor" width="200px" nowrap="nowrap" onclick="javascript:ShowRequests(\''.$strPreviousMonth.'\')";>&nbsp;&lt;&lt;  '.date("F Y", strtotime($strPreviousMonth)).'</a></td>';
echo'<td class="lightcell smalltext handcursor" align="right" width="200px" nowrap="nowrap" onclick="javascript:ShowRequests(\''.$strNextMonth.'\')";>'.date("F Y", strtotime($strNextMonth)).' &gt;&gt;</a></td>';
echo'<td class="lightcell medtextbold" align="center">Requests for '.$strFullCurrentMonth.'</td>';


echo'</tr>';
echo'</table>';

  echo'<table class="tablesmalltidy" width="1200px">';
  // ############################################################################# Put Locks at the top of the page
  echo '<tr height=30px>';
  echo '<td colspan="'.($intDaysInMonth + 1).'" class="tableheadersmall medtextboldcentre">'; 
  echo 'Locks';       
  echo '</td>';        
  echo '</tr>';
 
  DrawDatesAndWeeks($dteStartDate, $dteEndDate, $dowMap);    
  $dteCurrent  = $dteStartDate;
  echo '<td>';
  echo 'Locks';
  echo '</td>';
  while (strtotime($dteCurrent) < strtotime($dteEndDate)) {
    // What do we allow?
    
    switch ($arrRequests['Locks'][$dteCurrent]) {
      case 0: 
        // Available
        $strClass = 'LeaveAvailable';
        break;
      case 1: 
        // Not Available
        $strClass = 'LeaveNotAvailable';
        break;
      case 2:
        // Request in 
        $strClass = 'LeaveOK';      
      
        break;
      default:
        $strClass = 'DeadDay';
        break;
    }      
    echo '<td class="handcursor box25x15 '.$strClass.'">';
    echo $arrRequests['Locks'][$dteCurrent];
    echo '</td>';
	  $dteCurrent = date ("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));        
  }

  foreach ($arrRequests['Groups'] as $intGroupID => $arrGroup) {
    echo '<tr height=30px>';
    echo '<td colspan="'.($intDaysInMonth + 1).'" class="tableheadersmall medtextboldcentre">'; 
    echo $arrGroup['Description'];       
    echo '</td>';        
    echo '</tr>';
    
    // ######################################### Amounts requested
    echo '<tr>';
    echo '<td colspan="'.($intDaysInMonth + 1).'" class="tableheadersmall">';
    $intNumberMayRequest = $arrRequests['Groups'][$intGroupID]['User']['General']['NumberMayRequest'];
    if (isset($arrRequests['Groups'][$intGroupID]['User']['General']['Count'])) {
      $intRequestsMade = $arrRequests['Groups'][$intGroupID]['User']['General']['Count'];
    }
    else {
      $intRequestsMade = 0;
    }
    echo 'You may request '.$intNumberMayRequest.' general requests. Currently you have made '.$intRequestsMade.' requests<br>';
    
    if (isset($arrRequests['Groups'][$intGroupID]['User']['Additional'])) {
      echo 'In addition you may also request the following types.<br>';
      foreach ($arrRequests['Groups'][$intGroupID]['User']['Additional'] as $intAddTypeID => $arrAddType)  {
        $intNumberMayRequest = $arrAddType['NumberMayRequest'];
        if (isset($arrAddType['Count'])) {
          $intRequestsMade = $arrAddType['Count'];
        }
        else {
          $intRequestsMade = 0;
        } 
        if ($intNumberMayRequest >= 31) {
          echo $arrRequests['Groups'][$intGroupID]['Types'][$intAddTypeID]['Description'].': You may request an Unlimited number. Currently you have made '.$intRequestsMade.' requests<br>';    
        
        }   
        else {
          echo $arrRequests['Groups'][$intGroupID]['Types'][$intAddTypeID]['Description'].': You may request '.$intNumberMayRequest.'. Currently you have made '.$intRequestsMade.' requests<br>';    
        }
      }
    }
    echo '</td>';        
    echo '</tr>'; 
    // ######################################### End amounts requested
    
   if (isset($arrGroup['Types'])) { 
     DrawDatesAndWeeks($dteStartDate, $dteEndDate, $dowMap);    
    
    foreach ($arrGroup['Types'] as $intTypeID => $arrType) {
      if ($arrType['CanRequest'] == 1)  {
        $dteCurrent  = $dteStartDate;
        echo '<tr>';
        echo '<td>';
        if ($arrType['UniqueCount'] == 0) {
          echo $arrType['Description']; 
        }
        else {
          echo '<i>'.$arrType['Description'].'</i>'; 
        }     
        echo '</td>';
        // Put all the amounts in.... for all the days
        while (strtotime($dteCurrent) < strtotime($dteEndDate)) {
          $uRequestsStarts = strtotime("+".$arrType['RequestsStart']." days", strtotime(date("Y-m-d")));
          $uRequestsEnds = strtotime("+".$arrType['RequestsEnd']." days", strtotime(date("Y-m-d")));       
          if (strtotime($dteCurrent) < strtotime($arrType['StartDate']) || strtotime($dteCurrent) > strtotime($arrType['EndDate']) || strtotime($dteCurrent) < $uRequestsStarts || strtotime($dteCurrent) > $uRequestsEnds || $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['MayRequest'] == 0) {
            // This is before/after the start and end date of the type and also before or after the number of days defined   
            $intAltIn = 0;
            if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Approved'])) {
              if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Approved'] == 1) {
                $strClass = 'RequestedApproved';
                $strTitle = "title='You have requested this and it has been approved.'";            
              }
              else {
                $strClass = 'DeadDay';
                $strTitle = '';
              }   
            }
            else {
              $strClass = 'DeadDay';
              $strTitle = '';
            }
          } 
          else {
            if (isset($arrRequests['UserDates'][$dteCurrent])) {
              if ($arrRequests['UserDates'][$dteCurrent] == $intTypeID) {
                $intAltIn = 0;
              }
              else {
                $intAltIn = 1;
              }
            }  
            else {
              $intAltIn = 0;
            }     
            // This is the requestable period for the type
            // There is a request in
            if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Position'])) {        
              if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Approved'] == 1) {
                $strClass = 'RequestedApproved'; 
                $strTitle = "title='You have requested this and it has been approved.'"; 
              }
              else {
                if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Position'] > $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['RequestsAllowed']) {
                  $strClass = 'LeaveNotOK';
                  $strTitle = "title='You have requested this and are on the waiting list.'"; 
                }
                else {
                  $strClass = 'LeaveOK';
                  $strTitle = "title='You have requested this and it is OK.'"; 
                }
              }
            }
            else {
              $intRequestsAllowed = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['RequestsAllowed'];
              if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['RequestsIn'])) {
                $intRequestsIn = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['RequestsIn'];            
              }
              else {
                $intRequestsIn = 0;
              }
              if ($intRequestsIn < $intRequestsAllowed) {
                if ($intAltIn == 0) {
                  // Available No Alternative request
                  $strClass = 'LeaveAvailable';
                  $strTitle = "title='This request is available.'";               
                }
                else {
                  $strClass = 'AlreadyRequestedAvailable';
                  $strTitle = "title='You have an alternative request on this day.'"; 
                }
              }   
              else {
                // Not Available
                if ($intAltIn == 0) {
                  // Available No Alternative request
                  $strClass = 'LeaveNotAvailable';
                  if ($arrRequests['Groups'][$intGroupID]['Types'][$intTypeID]['OverLimit'] == 1) {
                    $strTitle = "title='This type is fully requested. You may join the waiting list.<br>There are $intRequestsIn requests alraedy and $intRequestsAllowed requests allowed.'"; 
                  }
                  else {
                    $strTitle = "title='This type is fully requested. Additional Requests are not allowed.'"; 
                  }
                }
                else {
                  $strClass = 'AlreadyRequestedNotAvailable';
                  $strTitle = "title='You have an alternative request on this day.'";               
                }
              }
            }
          }
        
           if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['CanClick'] == 1) {
            $strJS = ' onclick="javascript:RequestApply(\''.$dteCurrent.'\','.$intTypeID.')"';
          }
          else {
            $strJS = '';
          }        
          
          if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Tip'])) {
            echo '<td title=\''.$arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Tip'].'\' class="handcursor box25x15 '.$strClass.'"'.$strJS.'>';
          }           
          else {
            echo '<td class="handcursor box25x15 '.$strClass.'"'.$strJS.'>';
          }
          echo  $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['CanClick'];
         
          echo '</td>';
	        $dteCurrent = date ("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));
        }    
    
    
    
    
            
      echo '</tr>';    
    
      }
    
    }
  }
  else {
  echo 'Other';
  
  }
    
    
    
}    
?>
<script type="text/javascript">
$(document).ready(function(){
  $('[title]').qtip({
    style: { classes: 'qtip-rounded qtip-shadow qtip-dark'}
  });
  $('.qtip').click(function(event) {
    api.toggle(false);
  })
})
function RequestApply(date, RequestType) {
  $('*').qtip('hide');
  $.post("page-includes/requests/request-apply.php", {
    date: date,
    type: RequestType,
    user: '<?php echo $strUser?>'
  },
  function(data,status){
    ShowRequests(date);
  }
  )
};
</script>
<?php
function DrawDatesAndWeeks ($dteStartDate, $dteEndDate, $dowMap ) {

//public $dteStartDate;


    // ############################################################################# Put the days m, T, w etc across the top
    echo'<tr>';
    echo'<td class="tableheadersmall medtextboldcentre">&nbsp;</td>';
    $dteCurrent  = $dteStartDate;
    while (strtotime($dteCurrent) < strtotime($dteEndDate)) {
      echo'<td class="tableheadersmall smalltextcentre">'.substr(date("D", strtotime($dteCurrent)), 0, 1).'</td>';
	    $dteCurrent = date ("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));
    }
    echo'</tr>';
    // ############################################################################# END Put the days m, T, w etc across the top
    // ############################################################################# Put the Weeks across the top    
    echo'<tr>';
    echo'<td width="300px" class="tableheadersmall smalltextcentre">&nbsp;</td>';
    $dteCurrent  = $dteStartDate;

    $startday =  $dowMap[date("D", strtotime($dteCurrent))];
    $endday =  $dowMap[date("D", strtotime($dteEndDate))];
    $firstspan = 7 - $startday;
    $lastspan = $endday;
    if ($lastspan == 0){
      $lastspan = 7;
    }
    echo'<td class="tableheadersmall smalltextcentre handcursor" onclick="javascript:ShowWeeklyRequests(\''.$dteCurrent.'\')"; colspan="'.$firstspan.'">';
    if ($firstspan > 2) {
      echo spinweek(bbcweeknumber($dteCurrent));
    }
    else {
      echo substr(bbcweeknumber($dteCurrent), 4);
    }
    echo '</td>';
    $dteCurrent = date ("Y-m-d", strtotime("+$firstspan days", strtotime($dteCurrent)));
    while (strtotime($dteCurrent) < strtotime(date("Y-m-d", strtotime("-1 week", strtotime($dteEndDate))))) {
      echo'<td class="tableheadersmall smalltextcentre handcursor" colspan="7" onclick="javascript:ShowWeeklyRequests(\''.$dteCurrent.'\')";>'.spinweek(bbcweeknumber($dteCurrent)).'</td>';
	    $dteCurrent = date("Y-m-d", strtotime("+1 week", strtotime($dteCurrent)));
    }

    echo'<td class="tableheadersmall smalltextcentre handcursor" onclick="javascript:ShowWeeklyRequests(\''.$dteCurrent.'\')"; colspan="'.$lastspan.'">';
    if ($lastspan > 2) {
      echo spinweek(bbcweeknumber($dteCurrent));
    }
    else {
      echo substr(bbcweeknumber($dteCurrent), 4);
    }
    echo '</td>';
    echo'</tr>';
    // ############################################################################# END The Requests Weeks across the top #############################################################################

    // ############################################################################# The Dates across the top  #############################################################################
    echo'<tr>';
    $dteCurrent  = $dteStartDate;
    echo'<td width="300px" class="medlightcell">&nbsp;</td>';
    while (strtotime($dteCurrent) < strtotime($dteEndDate)) {
      echo'<td class="medlightcell smalltextcentre">'.date("d", strtotime($dteCurrent)).'</td>';
	    $dteCurrent = date ("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));
    }
    echo'</tr>';
    // ############################################################################# END The Dates across the top  #############################################################################




}
