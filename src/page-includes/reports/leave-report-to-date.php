<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';

$teamID = $_REQUEST['teamID'] ?? 0;
$arrAllocLeaveTypes = GetLeaveAllocateTypes();
$activeScheduledPeopleVal = '';
$activeScheduledPeopleCheck = '';

if (!empty($_COOKIE["activescheduledpeopled_r"]) && $_COOKIE["activescheduledpeopled_r"] == '1') {
    $activeScheduledPeopleVal = '1';
    $activeScheduledPeopleCheck = 'checked';
}

if (!empty($_REQUEST['year']) && $_REQUEST['year'] != 0) {
    $intLeaveYear = $_REQUEST['year'];
} else {
    $intLeaveYear = $_SESSION['allocations']['leave']['curentleaveyear'] 
        ?? date("Y", strtotime("-3 months"));
}


$intHoursPerLeaveDay = 8.75;
$intCarryOver = 98;

$_SESSION['allocations']["leave"]['curentleaveyear'] = $intLeaveYear;
$intPercentofYear = (GetPercentageOfYear($intLeaveYear));
$intCurrentYear  = GetCurrentLeaveYearGeneric();
$strteamName = GetTeamNameFromID($teamID);
$activeScheduledPeople = (isset($_COOKIE["activescheduledpeopled_r"])) ? $_COOKIE["activescheduledpeopled_r"] : 0;
$additionalflag =  0;
$arrLeaveCredits = GetLeaveCreditsSummary($teamID, $intLeaveYear, $arrAllocLeaveTypes,$activeScheduledPeople,$additionalflag);
$arrLeaveTaken = GetLeaveApprovedTakenSummary($teamID, $intLeaveYear,$arrAllocLeaveTypes);
echo '<table class="tablesmall" width="100%">';
echo '<tr>';
echo '<td  width="200px" class="tableheadersmall handcursor" onclick="javascript:ShowLeaveToDate('.$teamID.','.($intLeaveYear - 1).','.$additionalflag.')";>';
echo '<br>&lt;&lt;&nbsp;'.($intLeaveYear - 1).'<br><br>';
echo '</td>';
echo '<td  width="200px"  class="tableheadersmall handcursor" onclick="javascript:ShowLeaveToDate('.$teamID.','.($intLeaveYear + 1).','.$additionalflag.')";>';
echo '<br>'.($intLeaveYear + 1).'&nbsp;&gt;&gt;<br><br>';
echo '</td>';
echo '<td class="tableheadersmall medtextboldcentre">';
echo '<br>Leave Remaining for \''.$strteamName.'\'<br>Leave Year '.$intLeaveYear.'/'.($intLeaveYear + 1).' - '.$intPercentofYear.'% through the Leave Year<br>';
echo 'The assumption is '.$intHoursPerLeaveDay.' hours leave per day, allowing '.$intCarryOver.' hours carried over.<br><br>';
echo '</td>';
echo '<td align="center" class="tableheadersmall medtextboldcentre handcursor">';
echo '<div class="checkboxdivcredit checkboxdiv"><input type="checkbox" id="activescheduledpeopled_r" name="activescheduledpeopled" value="'.$activeScheduledPeopleVal.'"  '.$activeScheduledPeopleCheck.'>
        <label "for="activescheduledpeopled">Show only current scheduled people</label></div></td>';
echo '<td align="center" class="tableheadersmall medtextboldcentre handcursor" onclick="javascript:CreateLeaveExcel('.$teamID.','.$intLeaveYear.')">Excel Leave Report&nbsp;&nbsp;<br>';
echo '<img width="24px" height="24px" border="0" src="images/excel.png"></img></td>';
echo '</tr>';
echo '</table>';
echo '<br>';
  echo '<table class="tablesmall compact stripe" id="LeaveReportBalancesTable-'.$teamID.'">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Name';
  echo '</th>';
  echo '<th>';
  echo 'EFT';
  echo '</th>';
  $intCountTypes = 0;
  foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
    if ($arrAllocLeaveType['IncludeInReports'] == 1) {
      $intCountTypes++;
      echo '<th>';
      echo $arrAllocLeaveType['Description'];
      if ($arrAllocLeaveType['CalcInReports'] == 0) {
        echo '<br><i>(Not Included in<br>Days To Take)</i>';
      }
      echo '</th>';
    }
  }
  echo '<th>';
  echo 'Days To Take';
  echo '</th>';
   
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  foreach ($arrLeaveCredits as $schdefdulledPerson => $arrCreditsPerson) {
    $strStaffNumber = $arrCreditsPerson['StaffNumber'];
    $intTotalCredit = 0;
    $intTotalTaken = 0;
    echo '<tr class="handcursor">';
    echo '<td onclick="javascript:ShowLeaveBalances(\''.$arrCreditsPerson['Login'].'\',\''.$intLeaveYear.'\','.$teamID.')">';
    echo $arrCreditsPerson['Name'];
    echo '</td>';
    echo '<td>';
    echo $arrCreditsPerson['EFT'];
    echo '</td>';

    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      if ($arrAllocLeaveType['IncludeInReports'] == 1) {
        echo '<td align="center">';
        if (isset($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']])) {
          $intCredit = number_format(($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']]),2, '.', '');
        } else {
          $intCredit = 0;
        }
        if ($arrAllocLeaveType['CalcInReports'] == 1) {
          $intTotalCredit = $intTotalCredit + $intCredit;
        }
        
        if (isset($arrLeaveTaken[$schdefdulledPerson][$arrAllocLeaveType['ID']])) {
          $intTaken = number_format(($arrLeaveTaken[$schdefdulledPerson][$arrAllocLeaveType['ID']]),2, '.', '');
        } else {
          $intTaken = 0;
        }

        if ($arrAllocLeaveType['CalcInReports'] == 1) {
          $intTotalTaken = $intTotalTaken + $intTaken;
        }
        echo $intCredit - $intTaken;
        echo '</td>';
      }
    }
    echo '<td align="center">';
    // Days to take
    $intDaysToTake =  floor(($intTotalCredit - $intTotalTaken - $intCarryOver) / $intHoursPerLeaveDay);
    if ($intDaysToTake > 0) {
      echo $intDaysToTake;
    }
    echo '</td>';
    echo '</tr>';
}
  echo '</tbody>';
  echo '</table>';


?>
<script type="text/javascript">

$(document).ready( function () {
  var tableId = "LeaveReportBalancesTable-<?php echo $teamID?>";
  var a11yHelper = window.LeaveReportDatatableAccessibility;
  var filterSetting = [
    {
      column_number: 0,
      filter_type: 'text',
      filter_reset_button_text: 'x',
      clear_button_label: 'Clear Name filter'
    },
    {
      column_number: 1,
      filter_type: 'multi_select',
      select_type: 'chosen',
      filter_reset_button_text: 'x',
      clear_button_label: 'Clear EFT filter'
    }
  ];

  function applyDatatableAccessibility(dtInstance) {
    if (!a11yHelper) {
      return;
    }

    a11yHelper.applyHeaderAccessibilityFixes(dtInstance);
    a11yHelper.addAriaLabelsToFilterClearButtons(tableId, filterSetting);
    a11yHelper.addAriaLabelledbyToYadcfTextInputs(tableId);
    a11yHelper.hideYadcfSelectValuesUntilFocus(tableId);
  }

  var table = $("#" + tableId).DataTable({
    paging: false,
    destroy: true,
    scrollY: 400,
    info: false,
    stateSave: true,
    deferRender: true,
    initComplete: function () {
      applyDatatableAccessibility(this.api());
    },
    drawCallback: function () {
      applyDatatableAccessibility(this.api());
    }
  });

  yadcf.init(table, filterSetting);
  applyDatatableAccessibility(table);

  setTimeout(function () {
    table.columns.adjust();
  }, 0);

  $(window).off('resize.leaveReportToDate').on('resize.leaveReportToDate', function () {
    table.columns.adjust();
  });

});
function CreateLeaveExcel(teamId, LeaveYear) {
  window.open("page-includes/reports/leave-report-to-date-create-excel.php?teamId="+teamId+"&year="+LeaveYear);
}

$("#activescheduledpeopled_r").on('click',function(){
  if($("#activescheduledpeopled_r").val() == 1){
        $("#activescheduledpeopled_r").removeAttr('checked');
        $.cookie("activescheduledpeopled_r", 0);
  }else{
    $("#activescheduledpeopled_r").attr('checked', 'checked');
    $.cookie("activescheduledpeopled_r", 1);
  }
  $("#activescheduledpeopled_r").val($.cookie("activescheduledpeopled_r"));

  GetTabContent(<?php echo $teamID?>, 0)
     
});
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
