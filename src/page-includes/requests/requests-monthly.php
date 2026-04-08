<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/adminfunctions.php';
include_once '../../function-includes/locksfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';

$CellClass = '';
if (isset($_POST['user'])) {
  $strUser = $_POST['user'];
} else {
  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
}

$sheduledPersonId = getScheduledPersonIDByNetLoginID($strUser);

if (isset($_POST['date'])) {
  $dteStartDate = date("Y-m-01", strtotime($_POST['date']));
} else {
  $dteStartDate = date("Y-m-01", time());
}

if (isset($sheduledPersonId) && !empty($sheduledPersonId)) {
  $sql = "SELECT TeamID FROM UserDetails (nolock) INNER JOIN ScheduledPersonTeam_LINK (nolock) on UD_UserID=ScheduledPersonID WHERE UD_UserID= :sheduledPersonId and isDefault=1";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':sheduledPersonId',$sheduledPersonId, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
   if (isset($row['TeamID'])) {
     $arrUser['DefaultTeamID'] = $row['TeamID'];
   }
 }
 $userId = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
 $intTeamDepartment = $arrUser['DefaultTeamID'];
 $strFullCurrentMonth  = date("F Y", strtotime($dteStartDate));
 $strPreviousMonth =  date("Y-m-d", strtotime("-1 month", strtotime($dteStartDate)));
 $strNextMonth =  date("Y-m-d", strtotime("+1 month", strtotime($dteStartDate)));
 $arrTeamDefaults = GetTeamDefaults($userId,$intTeamDepartment);
  
  // New Code uses a function to read all the requests and types.
  // Passing the start date and staff number.
  // Because some requests can be weekly the function will read from before the start of the month until the end of the week after the 1 month period.
  $dteEndDate = date("Y-m-d", strtotime("+1 month", strtotime($dteStartDate)));
  $arrRequests = GetRequests($strUser,$sheduledPersonId,$dteStartDate, $dteEndDate);
  $intDaysInMonth = cal_days_in_month(CAL_GREGORIAN, date("m", strtotime($dteStartDate)), date("Y", strtotime($dteStartDate)));
  $getStartWeekandDayArr = GetAllocationWeekandDay($dteStartDate);
  $intStartWeek = $getStartWeekandDayArr['ixYearWeek'];
  $getEndWeekandDayArr = GetAllocationWeekandDay($dteEndDate);
  $intEndWeek = $getEndWeekandDayArr['ixYearWeek'];
  $arrAllocations = leaveRequestAllocationsAndRota($intStartWeek, $intEndWeek,$sheduledPersonId,$strUser);

  // ########################################## Do the header
  echo '<h1 class="sr-only">My Requests & Locks</h1>';
  echo'<div class="scrollH">';
  echo '<div class="my-request-lock-section">';
  echo '<div class="main-request-section">';
  echo '<div class="left-section" style="width: 66%;">';
  echo '<div class="request-section-box" style="width: 38%;">';
  echo '<button class="weeks-btn" onclick="javascript:ShowRequests(\''.$strPreviousMonth.'\')";> &nbsp;&lt;&lt;  '.date("F Y", strtotime($strPreviousMonth)).' </button>';
  echo '</div>';
  echo '<div class="request-section-box">';
  echo '<button class="weeks-btn" onclick="javascript:ShowRequests(\''.$strNextMonth.'\')";>'.date("F Y", strtotime($strNextMonth)).' &gt;&gt;</button>';
  echo '</div>';
  echo '</div>';
  echo '<div class="center-section" style="width: 35%;">';
  echo '<div class="request-section-box">';
  echo '<h2 style="font-size: 10px;"> Requests for '.$strFullCurrentMonth.'</h2>';
  echo '</div>';
  echo '</div>';
  echo '</div>';
  echo '</div>';

  echo'<table class="tablesmalltidy" width="100%">';
  // ############################################################################# Put Allocations at the top of the page 
  if (isset($arrAllocations)) {
    echo '<tr height=30px>';
    echo '<td colspan="'.($intDaysInMonth + 1).'" class="tableheadersmall medtextboldcentre">'; 
    echo 'Allocations';
    echo '</td>';
    echo '</tr>';
    DrawDatesAndWeeks($dteStartDate, $dteEndDate, $dowMap);
    echo'<tr>';
    echo'<td>&nbsp;</td>';
    $dteCurrent  = $dteStartDate;
    while (strtotime($dteCurrent) < strtotime($dteEndDate)) {
      echo '<td valign="top" class="medlightcell">';
        $getWeekandDayArr = GetAllocationWeekandDay($dteCurrent);
        $intAllocWeek = $getWeekandDayArr['ixYearWeek'];
        $intAllocDay = intval($getWeekandDayArr['ixDayInWeek']);
        $intAllocDay = intval($getWeekandDayArr['ixDayInWeek']);
        $title = isset($arrAllocations['Weeks'][$intAllocWeek][$intAllocDay]) ? $arrAllocations['Weeks'][$intAllocWeek][$intAllocDay]['Duty'] : '-';
        echo '<div class="handcursor box35x15" title="'.$title.'">';
        echo $title;
        echo '</div>' ;
      echo '</td>';
      $dteCurrent = date("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));
    }
    echo'</tr>';
  }
  // ############################################################################# END Allocations at the top of the page   
    // ############################################################################# Put Locks at the top of the page
    echo '<tr height=30px>';
    echo '<td colspan="'.($intDaysInMonth + 1).'" class="tableheadersmall medtextboldcentre">';
    echo 'Locks';
    echo '</td>';
    echo '</tr>';
    DrawDatesAndWeeks($dteStartDate, $dteEndDate, $dowMap);
    $dteCurrent  = $dteStartDate;
    echo '<tr>';
    echo '<td>';
    echo 'Locks';
    echo '</td>';
    while (strtotime($dteCurrent) < strtotime($dteEndDate)) {
      // What do we allow?
      $strClass = $arrRequests['Locks']['Dates'][$dteCurrent]['Class'];
      $strJS = $arrRequests['Locks']['Dates'][$dteCurrent]['strJS'];
               
      echo '<td '.$strJS;
      if (isset($arrRequests['Locks']['Dates'][$dteCurrent]['Tip'])) {
         echo ' title="'.$arrRequests['Locks']['Dates'][$dteCurrent]['Tip'].'"';
      }
      echo ' class="handcursor box35x15 '.$strClass.'">';
      echo '</td>';
  	  $dteCurrent = date("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));
    }

    if (isset($arrRequests['Groups'])) {
      foreach ($arrRequests['Groups'] as $intGroupID => $arrGroup) {
        echo '<tr height=30px>';
        echo '<td colspan="'.($intDaysInMonth + 1).'" class="tableheadersmall medtextboldcentre">'; 
        echo $arrGroup['Description'];
        echo '</td>';
        echo '</tr>';
        // ######################################### Amounts requested
        echo '<tr>';
        echo '<td colspan="'.($intDaysInMonth + 1).'" class="tableheadersmall">';
        if ($arrGroup['RequestsAllowedYearly'] != -1) {
          $intRequestsYearly = $arrGroup['RequestsAllowedYearly'];
          $intRequestsInYearly = $arrGroup['RequestsInYearly'];
          echo 'You may request '.$intRequestsYearly.' general requests in the Current Year. Currently you have requested '.$intRequestsInYearly.'<br>';        
        }
        $intNumberMayRequest = isset($arrRequests['Groups'][$intGroupID]['User']['General']['NumberMayRequest']) ? $arrRequests['Groups'][$intGroupID]['User']['General']['NumberMayRequest'] : '';
        if (isset($arrRequests['Groups'][$intGroupID]['User']['General']['Count'])) {
          $intRequestsMade = $arrRequests['Groups'][$intGroupID]['User']['General']['Count'];
        } else {
          $intRequestsMade = 0;
        }
        echo 'You may request '.$intNumberMayRequest.' general requests. Currently you have made '.$intRequestsMade.' requests<br>';
        
        if (isset($arrRequests['Groups'][$intGroupID]['User']['Additional'])) {
          echo 'In addition you may also request the following types:<br>';
          foreach ($arrRequests['Groups'][$intGroupID]['User']['Additional'] as $intAddTypeID => $arrAddType)  {
            if ($arrGroup['Types'][$intAddTypeID]['CanRequest'] == 1) {
              $intNumberMayRequest = $arrAddType['NumberMayRequest'];
              if (isset($arrAddType['Count'])) {
                $intRequestsMade = $arrAddType['Count'];
              } else {
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
            echo '<td nowrap>';
            if ($arrType['AffectsOthers'] == 0) {
              echo '<b>'.$arrType['Description'].'</b>';             
            }
            else {
              if ($arrType['UniqueCount'] == 0) {
                echo $arrType['Description']; 
              }
              else {
                echo '<i>'.$arrType['Description'].'</i>'; 
              }
            }
            
                 
            echo '</td>';
            // Put all the amounts in.... for all the days
            while (strtotime($dteCurrent) < strtotime($dteEndDate)) {
              $strClass = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Class'];
               if ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['CanClick'] == 1) {
                $strJS = ' onclick="javascript:RequestApply(\''.$dteCurrent.'\','.$intTypeID.')"';
              }
              else {
                $strJS = '';
              }
              if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['RequestID'])) {
                $strRequestID = ' CurrentDate="'.$dteCurrent.'" RequestID = "'.$arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['RequestID'].' "';
              } else {
                $strRequestID = '';
              }
                      
              if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Tip'])) {
                echo '<td '.$strRequestID.'title=\''.$arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteCurrent]['Tip'].'\' class="handcursor box35x15 '.$strClass.'"'.$strJS.'>';
              } else {
                echo '<td '.$strRequestID.'class="handcursor box35x15 '.$strClass.'"'.$strJS.'>';
              }
              echo '</td>';
    	        $dteCurrent = date("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));
            }    
          echo '</tr>';
          }
        
        }
      } else {
        echo 'Other';
      }
    }
  }
  echo '</table>';
  echo '</div>';
  echo '<br><br>';    
?>
<script type="text/javascript">
$('[title]').qtip({
  position: {
  my: 'right center',
  at: 'top left',
  viewport: $(window)
  },
  style: { classes: 'qtip-rounded qtip-shadow qtip-dark'}
});

function RequestApply(date, RequestType) {
  $('*').qtip('hide');
 
  $( "#dialog-request-apply" ).dialog({
    width:600,
    buttons: {
      "Yes": function() {
        $( this ).dialog( "close" );
        $.post("page-includes/requests/request-comments.php", { 
          id: 0,
          date: date,
          type: RequestType,
          user: '<?php echo $strUser?>'
        },
        function(data,status){
         $.facebox(data);
        })
      },
      "No": function() {
        document.getElementById('content').style.pointerEvents = 'none';
        $( this ).dialog( "close" );
       
        $.post("page-includes/requests/request-apply.php", {
          date: date,
          type: RequestType,
          user: '<?php echo $strUser?>'
        },
        function(data,status){
          ShowRequests(date);
        })
      },
      "Cancel": function() {
        $( this ).dialog( "close" );
      },
    }
  });   
};

function LockApply(week, day) {
  $('*').qtip('hide');
  $( "#dialog-lock-apply" ).dialog({
    width:640,
    buttons: {
      "Yes": function() {
        document.getElementById('content').style.pointerEvents = 'none';
        $( this ).dialog( "close" );
        $.post("page-includes/requests/lock-apply.php", {
          user: '<?php echo $strUser?>',
          week: week,
          day: day
        },
        function(data,status){
        <?php
          echo 'ShowRequests(\''.$strFullCurrentMonth.'\')';
        ?>
        }
        );
      },
      "No": function() {
        $( this ).dialog( "close" );
      },
    }
  });  
};
</script>
<?php 
function DrawDatesAndWeeks($dteStartDate, $dteEndDate, $dowMap ) {

//public $dteStartDate;
    // ############################################################################# Put the days m, T, w etc across the top
    echo'<tr>';
    echo'<td class="tableheadersmall medtextboldcentre">&nbsp;</td>';
    $dteCurrent  = $dteStartDate;
    while (strtotime($dteCurrent) < strtotime($dteEndDate)) {
      echo'<td class="tableheadersmall smalltextcentre">'.substr(date("D", strtotime($dteCurrent)), 0, 1).'</td>';
	    $dteCurrent = date("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));
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
    } else {
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
    } else {
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
	    $dteCurrent = date("Y-m-d", strtotime("+1 day", strtotime($dteCurrent)));
    }
    echo'</tr>';
    // ############################################################################# END The Dates across the top  #############################################################################
}

