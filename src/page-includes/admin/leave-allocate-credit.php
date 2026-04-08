<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/userfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
if (isset($_REQUEST['user'])) {
  $strUser = $_REQUEST['user'];
}
else {
  $strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
}

if(isset($_REQUEST['teamid'])) {
  $intTeamID = $_REQUEST['teamid'];
}
else {
  $intTeamID = 0;
}


if(isset( $_REQUEST['leaveyear'])) {
  $intLeaveYear = $_REQUEST['year'];
}
else {
  if (isset($_SESSION['allocations']["leave"]['curentleaveyear'])) {
    $intLeaveYear = $_SESSION['allocations']["leave"]['curentleaveyear'];
  }
  else {
    $intLeaveYear = date("Y", strtotime("-3 months"));
  }
}

$_SESSION['allocations']["leave"]['curentleaveyear'] = $intLeaveYear;
$intActiveLeaveYear = GetCurrentLeaveYearGeneric();
$staffDetailsResult = GetStaffDetailsByLogon($strUser);
$arrAllocLeaveTypes = GetLeaveAllocateTypes();
$arrLeaveCredits = GetLeaveCredits($staffDetailsResult['StaffNumber'],$staffDetailsResult['ScheduledPersonID'], $intLeaveYear);
$arrLeaveTaken = GetLeaveTakenIndivdualReport ($staffDetailsResult['ScheduledPersonID'], $intLeaveYear,$showcomment =1);

// Go through the Allocate Leave Tyoes and unset....
foreach ($arrAllocLeaveTypes as $intArrayID => $arrAllocLeaveType) {
  if ($arrAllocLeaveType['ShowZero'] == 0) {
    $strLeaveType = $arrAllocLeaveType['AllocName'];   
    if ($arrLeaveCredits['Types'][$strLeaveType] == 0 && $arrLeaveTaken['Types'][$intArrayID] == 0) {
      unset($arrAllocLeaveTypes[$intArrayID]);
    }
  }
} 


echo'<table class="tablesmall" width="100%">';
echo'<tr height="30px">';
echo'<th>Leave Record for '.$staffDetailsResult['userDisplayName'].'</th>';
echo'<th>Leave for Year '.$intLeaveYear.'</th>';
echo'</tr>';
echo'<tr>';
echo'</table>';

echo '<br>';


echo '<table width="100%" class="tablesmalltidy">';
  echo '<tr>';
  echo '<th colspan="'.(count($arrAllocLeaveTypes) + 4).'" height="30px">';
  echo 'Leave Credits';
  echo '</th>';
  echo '</tr>';

  echo '<tr>';
  
  echo '<th>';
  if($intActiveLeaveYear==$intLeaveYear){
    echo '&nbsp;&nbsp;<img class="handcursor" onclick="javascript:EditLeaveCredit(0);" border="0" src="../images/menu/new.png" width="12px" height="12px">';
  }
  echo '</th>';

  echo '<th>';
  echo 'Date Credited';
  echo '</th>';
  
  foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    echo '<th>';
    echo $arrAllocLeaveType['Description'];
    echo '</th>';
  }
  echo '<th class="width20Percent">';
  echo 'Comments';
  echo '</th>';
  echo '<th>';
  echo 'Credit Team';
  echo '</th>';
  echo '</tr>';
if (isset($arrLeaveCredits['Leave'])) {
  foreach ($arrLeaveCredits['Leave'] as $intLeaveID => $arrLeaveCredit) {
    echo '<tr>';
    echo '<td>';
    echo '&nbsp;&nbsp;<img class="handcursor" onclick="javascript:HistoryLeaveCredit(\''.$intLeaveID.'\');" border="0" src="../images/history.png" width="12px" height="12px">';
    if ($arrLeaveCredit['CanEdit'] == 1 && $arrLeaveCredit['IsCarryOver']== 0)  {
     echo '&nbsp;&nbsp;<img class="handcursor" onclick="javascript:EditLeaveCredit(\''.$intLeaveID.'\');" border="0" src="../images/edit.gif" width="12px" height="12px">';
     echo '&nbsp;&nbsp;<img class="handcursor" onclick="javascript:DeleteLeaveCredit(\''.$intLeaveID.'\');" border="0" src="../images/delete.gif" width="12px" height="12px">';
    }
   
    echo '</td>';    
    
    echo '<td>';
    echo date("jS F Y", strtotime($arrLeaveCredit['Date']));
    echo '</td>';

    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      echo '<td>';
      if (isset($arrLeaveCredit[$arrAllocLeaveType['AllocName']])) {
        echo number_format($arrLeaveCredit[$arrAllocLeaveType['AllocName']],2, '.', '');
        if (isset($arrTotalCredits[$LTID])) {
          $arrTotalCredits[$LTID] = $arrTotalCredits[$LTID] + $arrLeaveCredit[$arrAllocLeaveType['AllocName']];
        }
        else {
          $arrTotalCredits[$LTID] = $arrLeaveCredit[$arrAllocLeaveType['AllocName']];
        }
      }
      echo '</td>';
    }
    echo '<td>';
    echo $arrLeaveCredit['Comments'];
    echo '</td>';
    echo '<td>';
    echo $arrLeaveCredit['schedulingTeamName'];
    echo '</td>';
    echo '</tr>';
  }
}
// Totals
echo '<tr>';
echo '<th colspan="2">';
echo 'Total Credits';
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

echo '<tr>';
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
        echo '<td title ="<table class=tablesmalltidy><tr><th colspan=4>Breakdown</th></tr>'.$arrLeaveEntry['Summary'].'</table>">';      
      }
      else {
        echo '<td>';
      }
      echo date('l, jS F Y', strtotime($arrLeaveEntry['StartDate']));
      echo '</td>';
      
      echo '<td>';
      echo date('l, jS F Y', strtotime($arrLeaveEntry['EndDate']));
      echo '</td>';
      
      
          
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      echo '<td>';
     
      if (isset($arrLeaveEntry[$LTID])) {
        $intDuration = $arrLeaveEntry[$LTID]['Amount'];
        echo number_format(($intDuration),2, '.', '');
  
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
// Total Taken
echo '<tr>';
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
echo '</tr>';

// Balance
echo '<tr>';
echo '<th colspan="2">';
echo 'Balance';
echo '</th>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  echo '<th>';
  
 if (isset($arrTotalTaken[$LTID])) {
    echo number_format(($arrTotalCredits[$LTID] - $arrTotalTaken[$LTID]),2,'.','');
  } else {
      echo number_format($arrTotalCredits[$LTID],2,'.','');
  }
  echo '</th>';
}
echo '<th colspan="2">';
echo '</th>';
echo '</tr>';
echo '</table>';



?>

<div id="dialog-confirm-delete-credit" title="Information!" style="display:none;">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to mark this credit as Deleted?</p>
</div>


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


function HistoryLeaveCredit (leaveid) {
  $.post("page-includes/admin/leave-credit-history.php", {
    leaveid: leaveid
  },
  function(data,status){
	  $.facebox(data);
  })
}

function DeleteLeaveCredit (leaveid) {
  $( "#dialog-confirm-delete-credit" ).dialog(
    {
      buttons: {
        "Yes": function() {
          $( this ).dialog( "close" );
          $.post("page-includes/admin/leave-credit-delete.php", {
            leaveid: leaveid
          },
          function(data,status){{
            ShowAllocateLeaveCredit('<?php echo $strUser?>', <?php echo $intLeaveYear?>, <?php echo $intTeamID?>)
          }}
          );
        },
        "No": function() {
          $( this ).dialog( "close" );
        },
      }
    }
  );
}


function EditLeaveCredit (leaveid) {
  $.post("page-includes/admin/leave-credit-edit.php", {
    leaveid: leaveid,
    teamid: <?php echo $intTeamID ?>,
    leaveyear: <?php echo $intLeaveYear ?>,
    struser: '<?php echo $strUser?>'
  },
  function(data,status){{
	  $.facebox(data);
  }}
  );
}


</script>