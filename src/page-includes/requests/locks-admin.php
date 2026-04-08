<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/locksfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$strUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

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
$intWeekNumber = bbcweeknumber($dteStartDate);

$dteStartDate = datefromweek($intWeekNumber);
$dteEndDate = date("Y-m-d", strtotime("+1 week", strtotime($dteStartDate)));
$dtePreviousWeekStarts =  date('Y-m-d', strtotime($dteStartDate. ' - 7 days'));
$dteNextWeekStarts = date('Y-m-d', strtotime($dteStartDate. ' + 8 days'));

$arrLocks = GetWeeklyLocksByTeams($strUserID, $intWeekNumber);
$arrLocksDenied = GetLocksDenied($strUserID, $intWeekNumber);

// The Header
echo '<h1 class="sr-only">Locks Admin</h1>';
echo '<div class="lock-admin-section">';
echo '<div class="main-request-section">';
echo '<div class="left-section" style="width: 60%;">';
echo '<div class="section-box">';
echo '<button class="weeks-btn smalltext handcursor" width="150px" nowrap onclick="javascript:ShowLocksAdmin(\''.$dtePreviousWeekStarts.'\')";>&lt;&lt;Week  '.spinweek(bbcweeknumber($dtePreviousWeekStarts)).'</a></button>';
echo '</div>';
echo '<div class="section-box" style="margin: 0px 60px;">';
echo '<button class="weeks-btn smalltext handcursor" align="right" width="150px" nowrap onclick="javascript:ShowLocksAdmin(\''.$dteNextWeekStarts.'\')";>Week '.spinweek(bbcweeknumber($dteNextWeekStarts)).' &gt;&gt;</a></button>';
echo '</div>';
echo '<div>';
echo '<span class="smalltext" style="font-weight: 600;vertical-align: super;" tabindex="0">Choose Week</span>';
echo '<input type="hidden" id="datepickerlocks">';
echo '</div>';
echo '</div>';
echo '<div class="center-section" style="width: 40%;">';
echo '<div class="section-box">';
echo '<h2 style="font-size: 10px;font-weight: 600;"> Locks for Week '.spinweek($intWeekNumber).'</h2>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '<br>';
echo '<table class="tablesmall" width="100%">';
// The Days across the top
echo'<tr>';
$dteLoopDate  = $dteStartDate;
while (strtotime($dteLoopDate) < strtotime($dteEndDate)) {
  echo'<th width="180px" currdate="'.$dteLoopDate.'" class="handcursor locks-context-menu-header">';
  echo date("jS M Y", strtotime($dteLoopDate));
  echo'<br>'.date("l", strtotime($dteLoopDate));
  echo '</td>';
  $dteLoopDate = date("Y-m-d", strtotime("+1 day", strtotime($dteLoopDate)));
}
echo'</tr>';
if (isset($arrLocks)) {
    echo'<tr>';
    for ($intDayCounter = 0; $intDayCounter <= 6; $intDayCounter++) {
      echo '<td valign="top">';
      if (isset($arrLocks[$intDayCounter])) {
        foreach ($arrLocks[$intDayCounter] as $strCurrLogin => $arrUserLock) {
          // Check to see the times are OK
          $intStillMatches = 1;
          if ((isset($arrUserLock['CurrentDutyDuration'])) && (isset($arrUserLock['LockDuration']))) {
            if ($arrUserLock['CurrentDutyDuration'] != $arrUserLock['LockDuration']) {
              $intStillMatches = 0;
            }
          }
          if (isset($arrUserLock['CurrentDutyStart']) && isset($arrUserLock['LockStartTime'])) {
            if ($arrUserLock['CurrentDutyStart'] != $arrUserLock['LockStartTime'] || $arrUserLock['CurrentDutyEnd'] != $arrUserLock['LockEndTime']) {
              $intStillMatches = 0;
            }
          }
          echo '<table width="100%" class="tablesmallborder">';
          echo '<tr height="35px">';
          if ($arrUserLock['AdminRequest'] == 1) {
            echo '<td class="cellgreyhash locks-context-menu handcursor" LockID="'.$arrUserLock['ID'].'">';
          } else {
            echo '<th class="locks-context-menu handcursor" LockID="'.$arrUserLock['ID'].'">';
          }
          if ($intStillMatches == 0) {
            echo '<font color="#990000">';
          }
          echo $arrUserLock['FullName'];
          if (isset($arrUserLock['LockStartTime']) && !empty($arrUserLock['LockStartTime'])) {
            echo '<br>';
            echo $arrUserLock['LockStartTime'].'-'.$arrUserLock['LockEndTime'];
          }
          if ($intStillMatches == 0) {
            echo '</font>';
          }
          echo '</th>';
          echo '<th width="15px"><img border="0" src="../images/dropdown.png" width="15px" height="15px"  id="Image'.$arrUserLock['ID'].'" onclick="javascript:ToggleDetails('.$arrUserLock['ID'].')">'; 
          echo '</th>';
          echo '</tr>';

          echo '<tr style="display: none" id="'.$arrUserLock['ID'].'">';
          echo '<td colspan="2">';
          echo '<b><font color="#990000">Duty when applied</font></b><br>';
          echo $arrUserLock['AllocationsInfo'];

          if (isset($arrUserLock['CurrentDutyName'])) {
            echo '<br><b><font color="#990000">Current Duty</font></b><br>'.$arrUserLock['CurrentDutyName'].'<br>';
          }
          echo '</td>';
          echo '</tr>';
          echo '</table>';
        }
      }
      echo "</td>";
    }
    echo'</tr>';
  } else {
    echo'<tr height="50px">';
    echo'<td colspan="7">';
    echo 'There are currently no Locks entered for this week';
    echo'</td>';
    echo'</tr>';
  }
// People prevented from applying for a Lock
echo '<tr height="40px">';
echo '<th colspan="6">';
echo 'People prevented from requesting a Lock in this week';
echo '</th>';
echo '<th>';
echo 'New &nbsp;&nbsp;<img border="0" src="images/menu/page_white_add.png" width="17" height="17"  class="handcursor" onclick="javascript:NewEditLockRestriction('.$intWeekNumber.')"><br><br>';
echo '</th>';
echo '</tr>';
if (isset($arrLocksDenied)) {
  echo '<tr>';
  echo '<td colspan="7">';
  echo '<table width="100%" class="stripe">';
  foreach ($arrLocksDenied as $intLDid => $arrLockDenied) {
    echo '<tr>';
    echo '<td width="250px">';
    echo $arrLockDenied['Name'];
    echo '</td>';
    echo '<td>';
    echo '<img border="0" src="images/delete.png" width="17" height="17" class="handcursor" onclick="javascript:DeleteLockRestriction('.$intLDid.', '.$intWeekNumber.')">';
    echo '</td>';
    echo '</tr>';
  }
  echo '</table>';
  echo '</td>';
  echo '</tr>';
}
echo'</table>';
?>
<div id="dialog-confirm-remove" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to remove this restriction?</p>
</div>

<script type="text/javascript">
$(document).ready(function() {
  $(function() {
    $( "#datepickerlocks" ).datepicker({
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
        ShowLocksAdmin(currentdate);
      }
    });
  });
  $("table.stripe tr:odd").addClass("odd");
  $("table.stripe tr:even").addClass("even");
});

function ToggleDetails(ID) {
  $( "#"+ID ).toggle(  );
  if($("#Image"+ID).attr("src") == "images/dropup.png"){
  	$("#Image"+ID).attr("src", "images/dropdown.png");
  } else {
  	$("#Image"+ID).attr("src", "images/dropup.png");
  }
}

function NewEditLockRestriction (weeknumber) {
  $.post("page-includes/requests/lock-restriction.php", {
    weeknumber: weeknumber
  },
  function(data,status){
	  $.facebox(data);
  })
}

function DeleteLockRestriction (id,weekno) {
  $( "#dialog-confirm-remove" ).dialog(
    {
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
          $.post("page-includes/requests/delete-lock-restriction.php", {
            id: id,
            weeknumber:weekno
          },
          function(data,status){
        	  ShowLocksAdmin('<?php echo $dteStartDate?>');
          })
        },
        "No": function() {
          $( this ).dialog( "close" );
        },
      }
    }
  );
}
</script>