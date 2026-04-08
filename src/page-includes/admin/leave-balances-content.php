<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
ini_set("zlib.output_compression", 1);
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leave-admin-functions.php';
include_once '../../function-includes/leavefunctions.php';
$intTeamID = $_REQUEST['teamid'] ?? 0;
$intOption = $_REQUEST['taboption'] ?? 0;
$arrAllocLeaveTypes = GetLeaveAllocateTypes();

if (isset($_REQUEST['year'])) {
  $intLeaveYear = $_REQUEST['year'];
} else {
  if (isset($_SESSION['allocations']["leave"]['curentleaveyear'])) {
    $intLeaveYear = $_SESSION['allocations']["leave"]['curentleaveyear'];
  } else {
    $intLeaveYear = date("Y", strtotime("-3 months"));
  }
}
$_SESSION['allocations']["leave"]['curentleaveyear'] = $intLeaveYear;
$intActiveLeaveYesr = GetCurrentLeaveYearGeneric();
$strTeamName = GetTeamNameFromID($intTeamID);
$arrLeaveCredits = GetLeaveCreditsSummary($intTeamID, $intLeaveYear, $arrAllocLeaveTypes);
$arrLeaveTaken = GetLeaveApprovedTakenSummary($intTeamID, $intLeaveYear, $arrAllocLeaveTypes);
echo '<table class="tablesmallnoborder" width="100%">';
echo '<tr height="40px">';
echo '<th width="150px" align="right" class="medtextbold leaveNav handcursor" onclick="javascript:GetLeaveCreditTab(' . $intTeamID . ',' . $intOption . ',' . ($intLeaveYear - 1) . ')">';
echo '&lt;&lt; ' . ($intLeaveYear - 1);
echo '</th>';

echo '<th width="50px" class="medtextbold leaveNav handcursor">';
echo '|';
echo '</th>';

echo '<th width="150px" align="right" class="medtextbold leaveNav handcursor" onclick="javascript:GetLeaveCreditTab(' . $intTeamID . ',' . $intOption . ',' . ($intLeaveYear + 1) . ')">';
echo ($intLeaveYear + 1) . ' &gt;&gt;';
echo '</th>';

echo '<th style="text-align:center">';
echo '<span><b>Leave Balances for ' . $strTeamName . '</b></span><br>';
echo '<span><b>Showing Year ' . $intLeaveYear . '</b></span><br>';
echo '<span><b>Current Leave Year ' . $intActiveLeaveYesr . '</b></span>';
echo '</th><th width="150px"></th>';
echo '</tr>';
echo '</table>';
echo '<table class="tablesmall compact stripe" id="LeaveBalancesTable-' . $intTeamID . '-' . $intOption . '">';
echo '<thead>';
echo '<tr>';
echo '<th>';
echo 'Name';
echo '</th>';
echo '<th>';
echo 'Sort Code';
echo '</th>';
echo '<th>';
echo 'Staff Number';
echo '</th>';
echo '<th>';
echo 'EFT';
echo '</th>';
foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
  echo '<th>';
  echo $arrAllocLeaveType['Description'];
  echo '</th>';
}
echo '</tr>';
echo '</thead>';
echo '<tbody>';
foreach ($arrLeaveCredits as $schdefdulledPerson => $arrCreditsPerson) {
  if (isset($arrCreditsPerson['Login']) && !empty($arrCreditsPerson['Login'])) {
    $strStaffNumber = $arrCreditsPerson['StaffNumber'];
    echo '<tr class="handcursor">';
    echo '<td onclick="javascript:ShowAllocateLeaveCredit(\'' . $arrCreditsPerson['Login'] . '\',\'' . $intLeaveYear . '\',' . $intTeamID . ')">';
    echo $arrCreditsPerson['Name'];
    echo '</td>';
    echo '<td>';
    echo $arrCreditsPerson['SortCode'];
    echo '</td>';
    echo '<td>';
    echo $strStaffNumber;
    echo '</td>';
    echo '<td>';
    echo $EFTval = $arrCreditsPerson['EFT'] == 0 ? '' : $arrCreditsPerson['EFT'];
    echo '</td>';
    foreach ($arrAllocLeaveTypes as $LTID => $arrAllocLeaveType) {
      echo '<td>';
      if (isset($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']])) {
        $intCredit = number_format(($arrCreditsPerson['Leave'][$arrAllocLeaveType['AllocName']]), 2, '.', '');
      } else {
        $intCredit = 0;
      }
      echo $intCredit;
      echo '<br>';
      if (isset($arrLeaveTaken[$schdefdulledPerson][$arrAllocLeaveType['ID']])) {
        $intTaken = number_format(($arrLeaveTaken[$schdefdulledPerson][$arrAllocLeaveType['ID']]), 2, '.', '');
      } else {
        $intTaken = 0;
      }
      echo $intTaken;
      echo '<br><b>';
      echo $intCredit - $intTaken;
      echo '</b>';
      echo '</td>';
    }
    echo '</tr>';
  }
}
echo '</tbody>';
echo '</table>';
?>
<script type="text/javascript">
  $(document).ready(function() {
    var table = $("#LeaveBalancesTable-<?php echo $intTeamID ?>-<?php echo $intOption ?>").DataTable({
      paging: false,
      destroy: true,
      scrollY: parseInt($(window).height() - 349),
      info: false,
      stateSave: true,
      deferRender: true,
      "initComplete": function(settings, json) {
        $('#loading').hide();
      }
    });

    yadcf.init(table, [{
        column_number: 0,
        filter_type: 'text'
      },
      {
        column_number: 1,
        filter_type: 'text'
      },
      {
        column_number: 3,
        filter_type: 'multi_select',
        select_type: 'chosen'
      },
    ]);

    if ($.cookie("vscrollbal_<?php echo $intTeamID ?>") !== null) {
      $(".dataTables_scrollBody").scrollTop(Math.abs($.cookie("vscrollbal_<?php echo $intTeamID ?>")));
    }

    $(".dataTables_scrollBody").on("scroll", function() {
      // Set a cookie that holds the scroll position.
      $.cookie("vscrollbal_<?php echo $intTeamID ?>", $(".dataTables_scrollBody").scrollTop());
    });
    $(function() {
      $("#accordion-bal<?php echo $intTeamID ?>").accordion({
        active: false,
        collapsible: true

      });
    });

  })
</script>