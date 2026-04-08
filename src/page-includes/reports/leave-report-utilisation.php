<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';

$teamID = $_POST['teamID'] ?? 0;
$arrAllocLeaveTypes = GetLeaveAllocateTypes();

if (!empty($_POST['year'])) {
    $intLeaveYear = $_POST['year'];
} elseif (!empty($_SESSION['allocations']['leave']['curentleaveyear'])) {
    $intLeaveYear = $_SESSION['allocations']['leave']['curentleaveyear'];
} else {
    $intLeaveYear = date("Y", strtotime("-3 months"));
}

  $intHoursPerLeaveDay = 10;
  $intCarryOver = 72;

  $_SESSION['allocations']["leave"]['curentleaveyear'] = $intLeaveYear;
  $intPercentofYear = (GetPercentageOfYear($intLeaveYear));
  $intCurrentYear  = GetCurrentLeaveYearGeneric();
  $strteamName = GetTeamNameFromID($teamID);
  $arrLeaveCredits = GetLeaveCreditsSummary($teamID, $intLeaveYear, $arrAllocLeaveTypes);
  $arrLeaveTaken = GetLeaveApprovedTakenSummary($teamID, $intLeaveYear, $arrAllocLeaveTypes);
 
  echo '<table class="tablesmall compact stripe" width="100%">';
  echo '<tr>';
  echo '<td  width="200px" class="tableheadersmall handcursor" onclick="javascript:ShowLeaveUtilisation('.$teamID.','.($intLeaveYear - 1).')";>';
  echo '<br>&lt;&lt;&nbsp;'.($intLeaveYear - 1).'<br><br>';
  echo '</td>';
  echo '<td  width="200px" align="right" class="tableheadersmall handcursor" onclick="javascript:ShowLeaveUtilisation('.$teamID.','.($intLeaveYear + 1).')";>';
  echo '<br>'.($intLeaveYear + 1).'&nbsp;&gt;&gt;<br><br>';
  echo '</td>';
  echo '<td class="tableheadersmall medtextboldcentre">';
  echo '<br>Percentage Leave Utilisation for \''.$strteamName.'\'<br>Leave Year '.$intLeaveYear.'/'.($intLeaveYear + 1).' - '.$intPercentofYear.'% through the Leave Year<br><br>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '<br>';
 
  echo '<table class="tablesmall compact stripe" id="LeaveReportUtilisationTable-'.$teamID.'">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Name';
  echo '</th>';
  echo '<th>';
  echo 'EFT';
  echo '</th>';
  echo '<th>';
  echo '</th>';
  // Get a count of the leave types we are showing
  $intCountTypes = 0;
  foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    if ($arrAllocLeaveType['IncludeInReports'] == 1) {
      $intCountTypes++;
      echo '<th>';
      echo $arrAllocLeaveType['Description'];
      echo '</th>';
    }
  }
  
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  foreach ($arrLeaveCredits as $schdefdulledPerson => $arrCreditsPerson) {
    $strStaffNumber = $arrCreditsPerson['StaffNumber'];
    $intTotalCredit = 0;
    $intTotalTaken = 0;
    echo '<tr class="handcursor">';
    echo '<td onclick="javascript:ShowLeaveBalances(\''.$arrCreditsPerson['Login'].'\',\''.$intLeaveYear.'\','.$teamID.')" style="word-wrap: break-word; white-space: normal;">';
    echo $arrCreditsPerson['Name'];
    echo '</td>';
    echo '<td>';
    echo $arrCreditsPerson['EFT'];
    echo '</td>';
    echo '<td style="word-wrap: break-word; white-space: normal;">';
    echo 'Credit<br>Taken/Approved<br>% Taken/Approved';
    echo '</td>';
    
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      if ($arrAllocLeaveType['IncludeInReports'] == 1) {
        echo '<td align="center">';
        if (isset($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']])) {
          $intCredit =number_format(($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']]), 2, '.', '');
        } else {
          $intCredit = 0;
        }
        if ($arrAllocLeaveType['HasCredits'] == 1) {
          echo $intCredit;
        }
        echo '<br>';
        if (isset($arrLeaveTaken[$schdefdulledPerson][$LTID])) {
          $intTaken = number_format(($arrLeaveTaken[$schdefdulledPerson][$arrAllocLeaveType['ID']]), 2, '.', '');
        } else {
          $intTaken = 0;
        }
        echo $intTaken;
        echo '<br>';
        
        if ($intTaken == 0 && $intCredit == 0) {
          $intPercent = '';
        } else {
          if ($intCredit == 0) {
            $intPercent = '100%';
          } else {
            $intPercent = round(($intTaken / $intCredit) * 100, 2).'%';
          }
        }
        echo $intPercent;
        echo '</td>';
      }
    }
    echo '</tr>';
}
  echo '</tbody>';
  echo '</table>';


?>
<script type="text/javascript">

$(document).ready( function () {
  var table = $("#LeaveReportUtilisationTable-<?php echo $teamID?>").DataTable({
  paging: false,
  destroy: true,
  scrollY: 400,
  info: false,
  stateSave: true,
  deferRender: true,

  "initComplete": function( settings, json ) {
    $('#loading').hide();
     this.api().columns.adjust();
  },
   drawCallback: function () {
      this.api().columns.adjust();
    }
  });

  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
	{column_number: 1,
	  filter_type: 'multi_select', 
      select_type: 'chosen'
    },
  ]);

  $(window).on('resize', function () {
    table.columns.adjust();
  });

})
</script>


<style>
.dataTables_scrollHead table,
.dataTables_scrollBody table {
  table-layout: fixed !important;
}

.dataTables_scrollHead th {
  position: relative;
  vertical-align: top;
}

.dataTables_scrollHead th .yadcf-filter {
  width: 92% !important;   
  margin: 4px auto 0;
  box-sizing: border-box;
}

.dataTables_scrollHead th select.yadcf-filter {
  width: 92% !important;
  margin: 4px auto 0;
  box-sizing: border-box;
}

.dataTables_scrollHead th .chosen-container {
  width: 92% !important;
  margin: 4px auto 0;
  box-sizing: border-box;
}

.dataTables_scrollHead th .chosen-single {
  width: 100%;
  box-sizing: border-box;
}
</style>