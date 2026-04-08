<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/common/classCommonDBFunctions.php';

$commonObj = new classCommonDBFunctions();
if (isset($_REQUEST['user'])) {
  $strUser = $_REQUEST['user'];
} else {
  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
}
$intScheduledPersonID = getScheduledPersonIDByNetLoginID($strUser);
$arrUserSettings = json_decode($commonObj->userLeaveRequestByNetLogin($strUser, 0),true);

if (isset($_REQUEST['date'])) {
  $dteStartDate = $_REQUEST['date'];
} else {
  if (isset($_SESSION['locksdate'])) {
    $dteStartDate = $_SESSION['locksdate'];
  } else {
    $dteStartDate = date("Y-m-d", time());
  }
}
$_SESSION['locksdate'] = $dteStartDate;
if (isset($_REQUEST['admin'])) {
  $intAdmin = $_REQUEST['admin'];
} else {
  $intAdmin = 0;
}

if (isset($_REQUEST['group'])) {
  $intGroup = $_REQUEST['group'];
} else {
  $intGroup = 0;
}

$bbcweeknumberArray =  $commonObj->GetWeekNoAndIDayByDateFromTimeDim($dteStartDate);
$intWeekNumber = $bbcweeknumberArray['ixYearWeek'] ?? 0;

$dteStartDate =  date('Y-m-d',strtotime($commonObj->GetWeekStartDateByWeekNoFromTimeDim($intWeekNumber,'ByDayWeek',0)['dDateTime']));
$dteEndDate = date('Y-m-d',strtotime($commonObj->GetWeekStartDateByWeekNoFromTimeDim($intWeekNumber,'ByDayWeek',6)['dDateTime']));

$dtePreviousWeekStarts =  date('Y-m-d', strtotime($dteStartDate. ' - 7 days'));
$dteNextWeekStarts = date('Y-m-d', strtotime($dteStartDate. ' + 8 days'));

$arrRequests = GetWeeklyRequests($strUser,$intScheduledPersonID, $dteStartDate, $dteEndDate, $intAdmin);
if($intAdmin == 1){
  echo '<h1 class="sr-only">Requests By Week</h1>';
}else{
  echo '<h1 class="sr-only">My Requests By Week</h1>';
}
// The Header
echo'<table class="tablesmall" width="100%">';
echo'<tr height="40px">';
if ($intAdmin == 1) {
  echo'<td class="lightcell smalltext handcursor" width="150px" onclick="javascript:ShowRequestWeeklyAdminByDate(\''.$dtePreviousWeekStarts.'\','.$intGroup.')";>&lt;&lt;Week '.spinweek(bbcweeknumber($dtePreviousWeekStarts)).'</a></td>';
  echo'<td class="lightcell smalltext handcursor" align="right" width="150px" nowrap="nowrap" onclick="javascript:ShowRequestWeeklyAdminByDate(\''.$dteNextWeekStarts.'\','.$intGroup.')";>Week '.spinweek(bbcweeknumber($dteNextWeekStarts)).' &gt;&gt;</a></td>';
} else {
  echo'<td class="lightcell smalltext handcursor" width="150px" onclick="javascript:ShowWeeklyRequests(\''.$dtePreviousWeekStarts.'\')";>&nbsp;&lt;&lt;  '.spinweek(bbcweeknumber($dtePreviousWeekStarts)).'</a></td>';
  echo'<td class="lightcell smalltext handcursor" align="right" width="150px" nowrap="nowrap" onclick="javascript:ShowWeeklyRequests(\''.$dteNextWeekStarts.'\')";>'.spinweek(bbcweeknumber($dteNextWeekStarts)).' &gt;&gt;</a></td>';
}
echo '<td class="lightcell medtextbold" width="125px"; align="right">Choose Week</td>';
echo '<td class="lightcell" align="left" width="100px"><input type="hidden" id="datepicker"></td>';
echo'<td class="lightcell medtextbold" align="center">Requests for Week '.spinweek($intWeekNumber).'</td>';
echo '</tr>';
echo '</table>';
echo '<br>';
if (isset($arrRequests)) {
  if ($intGroup == 0) {
    echo '<div id="accordionrw">';
    // Loop through the available Groups
    foreach ($arrRequests['Groups'] as $intGroupID => $arrGroup) {
      echo '<h2 id="'.$intGroupID.'">';
      echo $arrGroup['Description'];
      echo '</h2>';
      DrawRequests($arrGroup, $intGroupID, $arrRequests, $dteStartDate, $dteEndDate, $intAdmin, $strUser, $arrUserSettings);
      }
      echo '</div>';
    } else {
      echo '<div class="tableheadersmall medtextboldcentre"><br>';
      echo $arrRequests['Groups'][$intGroup]['Description'];
      echo '<br><br></div>';
      DrawRequests($arrRequests['Groups'][$intGroup], $intGroup, $arrRequests, $dteStartDate, $dteEndDate, $intAdmin, $strUser, $arrUserSettings);        
    }
  } else {
    echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
    echo '<br>Although you are defined as an Administrator for one or more Leave/Requests Group<br>The groups you can administer have no Request Types defined!<br><br>';
    echo '</div>';
  }
?>
<script type="text/javascript">
$('[title]').qtip({
  style: { classes: 'qtip-rounded qtip-shadow qtip-dark'}
});
$('.qtip').click(function(event) {
    api.toggle(false);
});

// Create the tooltips only when document ready
 $(document).ready(function() {
  $("#datepicker" ).datepicker({
    showOn: "button",
    buttonImage: "images/calendar.gif",
    buttonImageOnly: true,
    showOtherMonths: 'true',
    selectOtherMonths: 'true',
    firstDay: '6',
    gotoCurrent: 'true',
    dateFormat: "yy-mm-dd",
    defaultDate: "<?php echo  $dteStartDate?>" ,
    onSelect: function (dateText, inst) {
    var currentdate = dateText;
  <?php
    if ($intAdmin == 1) {
        echo 'ShowRequestWeeklyAdminByDate(currentdate, '.$intGroup.')';
    } else {
        echo 'ShowWeeklyRequests(currentdate)';
    }
  ?>
    }
  });

  $("#accordionrw").accordion({
    heightStyle: "content",
    collapsible: true,
    activate: function(event, ui) {
      $.cookie('locks_accordion_index_rw', $("#accordionrw").accordion("option", "active"));
    },
    active: parseInt($.cookie('locks_accordion_index_rw') || 0),
  });

   $('.tipremote').each(function() {
     $(this).qtip({
       content: {
         text: function(event, api) {
           $.ajax({
             url: 'page-includes/requests/request-comments-info.php?id=' + api.elements.target.attr('requestid')
           })
           .then(function(content) {
             // Set the tooltip content upon successful retrieval
             api.set('content.text', content);
           },
           function(xhr, status, error) {
             // Upon failure... set the tooltip content to error
             api.set('content.text', status + ': ' + error);
           });
           return 'Loading...'; // Set some initial text
         }
       },
         position: {
         viewport: $(window)
         },
         style: {
          classes: 'qtip-rounded qtip-shadow qtip-light',
          width: 420,
         }
         });
     });
});
</script>
<?php
function DrawRequests($arrGroup, $intGroupID, $arrRequests, $dteStartDate, $dteEndDate, $intAdmin, $strUser, $arrUserSettings) {
    // This is the div with the requests
    echo '<div>';
    echo '<table class="tablesmalltidy" width="100%">';
    // The Days across the top
    echo'<tr>';
    echo'<td class="tableheadersmall medtextboldcentre samecolsWidthB"></td>';
    $dteLoopDate  = $dteStartDate;
    while (strtotime($dteLoopDate) <= strtotime($dteEndDate)) {
      echo'<td class="tableheadersmall medtextboldcentre samecolsWidth">'.date("jS M Y", strtotime($dteLoopDate));
      echo'<br>'.date("l", strtotime($dteLoopDate)).'</td>';
	    $dteLoopDate = date("Y-m-d", strtotime("+1 day", strtotime($dteLoopDate)));
    }
    echo'</tr>';
    echo'<tr>';
    // Loop through the types
    foreach ($arrGroup['Types'] as $intTypeID => $arrType) {
      echo'<tr height="30px">';
        echo '<td class="samecolsWidth" valign="top">';
        if ($arrType['UniqueCount'] == 0) {
          echo $arrType['Description'];
        } else {
          echo '<i>'.$arrType['Description'].'</i>';
        }
        echo '</td>';
      $dteLoopDate  = $dteStartDate;
      while (strtotime($dteLoopDate) <=strtotime($dteEndDate)) {
        echo '<td valign="top">';
        //echo '<div class="lightcell smalltextcentre box200x15">';
        $intAllowed = $arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteLoopDate]['RequestsAllowed'];
        if (isset($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteLoopDate]['Requests'])) {
          $intCount = 1;
        foreach ($arrRequests['Groups'][$intGroupID][$intTypeID]['Dates'][$dteLoopDate]['Requests'] as $intRequestID => $arrRequest) {
           if ($arrRequest['Approved'] == 1) {
             $style = "Approved";
           } else {
             if ($intAllowed == -1) {
               $style = "BestEndevoursApplied";
             } else {
               if ($intCount <= $intAllowed) {
                 if ($arrRequest['isOK'] == 0) {
                   MakeRequestOK($arrRequest['ID']);
                 }
                 $style = "RequestedAvailable";
               } else {
                 $style = "LeaveNotOK";
               }
             }
           }
           echo '<div requestid="'.$arrRequest['ID'].'" date="'.$dteLoopDate.'" class="box180 handcursor '.$style.' borderbottomwhite';          
           if ($intAdmin == 1) {
             if ($arrRequest['Approved'] == 1) {
               echo ' weekly-requests-context-menu-approved';
             } else {
               echo ' weekly-requests-context-menu';
             }
           }
          echo '">';
          if ($arrRequest['Unlikely'] == 0) {
            echo $arrRequest['FullName'];
          } else {
            echo '['.$arrRequest['FullName'].']';
          }
          $i = DayFromDate($dteLoopDate);
          if (isset($arrRequests['Allocations'][$arrRequest['Login']][$i])) {
            echo '<br><font color="#666666">'.$arrRequests['Allocations'][$arrRequest['Login']][$i].'</font>';
          }
          $leaveCommentIconFlag = 0;

          if((strtolower($strUser) == strtolower($arrRequest['Login'])) ||  ($arrUserSettings['LeaveRequests'][$intGroupID]['Admin'] != 0)){
            $leaveCommentIconFlag = 1;
          }
          if ((!empty($arrRequest['OfficeComments']) && $leaveCommentIconFlag == 1) || (!empty($arrRequest['UserComments']) && $leaveCommentIconFlag == 1)) {
            echo '<div requestid="'.$arrRequest['ID'].'" class="DutyCellBottomRight handcursor tipremote">';
            echo '<img src="images/info.png" width="10" height="10" border="0">';
            echo '</div>';
          }
          echo '</div>';
          $intCount++;
        }
      }
        echo "</td>";
        $dteLoopDate = date("Y-m-d", strtotime("+1 day", strtotime($dteLoopDate)));
      }
      echo'</tr>';
      }
      echo'</table>';
      echo '</div>';
      // This is the end of the div with the requests  
} 
