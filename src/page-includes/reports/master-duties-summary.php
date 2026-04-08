<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/reports-functions.php';

$intTeamID = $_REQUEST['departmentid'];
if (isset($_REQUEST['sDate'])) {
  $strStartDate = $_REQUEST['sDate'];
} else {
  $strStartDate = date("Y-m-d");
}
if (isset($_REQUEST['Period'])) {
  $thisperiod = $_REQUEST['Period'];
} else {
  $thisperiod = 21;
}
$intStartWeek = bbcweeknumber($strStartDate);
$intEndWeek = addweeks($intStartWeek, $thisperiod);
$strQuery = "exec usp_ReadMasterDuties $intTeamID, $intStartWeek, $intEndWeek";
$pdo  = OpenDBLinkA7();
$stmt = $pdo->prepare($strQuery);
$stmt->execute();
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
$result = [];
foreach ($row as $rowVal) {
  $weekNumber = $rowVal['WeekNumber'];
  if (!isset($result[$weekNumber])) {
    $result[$weekNumber] = [
      'sat' => 0,
      'sun' => 0,
      'mon' => 0,
      'tue' => 0,
      'wed' => 0,
      'thu' => 0,
      'fri' => 0,
      'weekCount' => 0,
      'hours' => 0,
      'eft' => 0
    ];
  }
  $sat = $rowVal[0] ?? 0;
  $sun = $rowVal[1] ?? 0;
  $mon = $rowVal[2] ?? 0;
  $tue = $rowVal[3] ?? 0;
  $wed = $rowVal[4] ?? 0;
  $thu = $rowVal[5] ?? 0;
  $fri = $rowVal[6] ?? 0;
  $dutyDuration = $rowVal['DutyDuration'] ?? 0;
  $breakTime = $rowVal['BreakTime'] ?? 0;
  $netDuration = $dutyDuration - $breakTime;
  $weekTotal = $sat + $sun + $mon + $tue + $wed + $thu + $fri;
  $result[$weekNumber]['sat'] += $sat;
  $result[$weekNumber]['sun'] += $sun;
  $result[$weekNumber]['mon'] += $mon;
  $result[$weekNumber]['tue'] += $tue;
  $result[$weekNumber]['wed'] += $wed;
  $result[$weekNumber]['thu'] += $thu;
  $result[$weekNumber]['fri'] += $fri;
  $result[$weekNumber]['weekCount'] += $weekTotal;
  $result[$weekNumber]['hours'] += $netDuration;
  $result[$weekNumber]['eft'] += $weekTotal * ($netDuration / 3600);
}
echo '<form id="mdsummform' . $intTeamID . '">';
echo '<table class="tablesmall" width="100%">';
echo '<tr>';
echo '<th colspan="7">';
echo '<br>Master Duties for <br><br>';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px" height="30px">Start Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="sDate" id="md-report-datepicker' . $intTeamID . '" value="' . $strStartDate . '" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="slrralternate' . $intTeamID . '" size="30" value="' . date("l, j F, Y", strtotime($strStartDate)) . '"></td>';
echo '<th width="200px">';
echo 'Period';
echo '</th>';
echo '<th width="250px">';
echo '<select class="chosen-select" name="Period">';
for ($i = 0; $i <= 52; $i++) {
  if ($thisperiod == $i) {
    echo '<option selected value="' . $i . '">' . $i . ' Weeks</option>';
  } else {
    echo '<option value="' . $i . '">' . $i . ' Weeks</option>';
  }
}
echo '</select>';
echo '</th>';
echo '<th><input id="submit" type="submit" value=" Go " name="Update"></th>';
echo '</tr>';
echo '</table>';
echo '<input type="hidden" name="departmentid" value="' . $intTeamID . '">';
echo '</form>';
echo '<br>';
echo '<table id="mdsumm-' . $intTeamID . '" class="tablesmall compact stripe">';
echo '<thead>';
echo '<tr>';
echo '<th>Week</th>';
echo '<th>EFT</th>';
echo '<th>Sat</th>';
echo '<th>Sun</th>';
echo '<th>Mon</th>';
echo '<th>Tue</th>';
echo '<th>Wed</th>';
echo '<th>Thu</th>';
echo '<th>Fri</th>';
echo '<th>Week Count</th>';
echo '<th>Week Hours</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
$i = 999;
foreach ($result as $intWeek => $arrMasterDutiesWeek) {
  echo '<tr>';
  echo '<td>';
  $i++;
  echo '<span style="color:transparent;">' . $i . '</span>  ';
  echo spinweek($intWeek);
  echo '</td>';
  echo '<td>';
  echo number_format((($arrMasterDutiesWeek['eft'] * 52) / 1456), 1, '.', '');
  echo '</td>';
  echo '<td>';
  echo $arrMasterDutiesWeek['sat'];
  echo '</td>';
  echo '<td>';
  echo $arrMasterDutiesWeek['sun'];
  echo '</td>';
  echo '<td>';
  echo $arrMasterDutiesWeek['mon'];
  echo '</td>';
  echo '<td>';
  echo $arrMasterDutiesWeek['tue'];
  echo '</td>';
  echo '<td>';
  echo $arrMasterDutiesWeek['wed'];
  echo '</td>';
  echo '<td>';
  echo $arrMasterDutiesWeek['thu'];
  echo '</td>';
  echo '<td>';
  echo $arrMasterDutiesWeek['fri'];
  echo '</td>';
  echo '<td>';
  echo $arrMasterDutiesWeek['weekCount'];
  echo '</td>';
  echo '<td>';
  echo number_format($arrMasterDutiesWeek['hours'] / 3600, 2, '.', '');
  echo '</td>';
  echo '</tr>';
}
echo '</tbody>';
echo '</table>';
?>
<script type="text/javascript">
  $(document).ready(function() {
    $(".chosen-select").chosen({
      no_results_text: "Oops, nothing found!",
      width: "200px"
    });
    var table = $("#mdsumm-<?php echo $intTeamID ?>").DataTable({
      paging: false,
      scrollY: 400,
      scrollCollapse: true,
      info: false,
      stateSave: true,
      deferRender: true,
      order: [
        [0, "asc"]
      ]
    });
    yadcf.init(table, [{
      column_number: 0,
      filter_type: 'text'
    }, ]);
    if ($.cookie("#mdsumm-<?php echo $intTeamID ?>") !== null) {
      scrollPos = $.cookie("#mdsumm-<?php echo $intTeamID ?>");
      $("#mdsumm-<?php echo $intTeamID ?>").closest('.dataTables_scrollBody').scrollTop(scrollPos);
    };
    $(function() {
      $("#md-report-datepicker<?php echo $intTeamID ?>").datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#slrralternate<?php echo $intTeamID ?>",
        altFormat: "DD, d MM, yy"
      });
    });
  })
  $("#mdsumm-<?php echo $intTeamID ?>").closest('.dataTables_scrollBody').on('scroll', function() {
    var currpos = $("#mdsumm-<?php echo $intTeamID ?>").closest('.dataTables_scrollBody').scrollTop();
    $.cookie("#mdsumm-<?php echo $intTeamID ?>", currpos);
  });
  $('#mdsummform<?php echo $intTeamID ?>').validate({
    submitHandler: function(form) {
      $.ajax({
        type: 'POST',
        url: 'page-includes/reports/master-duties-summary.php',
        data: $('#mdsummform<?php echo $intTeamID ?>').serialize(),
        success: function(data) {
          $('#MasterDutiesReportTabsSumm').html(data);
        }
      });
    }
  })
</script>
<style>
  .thBackGround {
    background-color: #DDDDDD;
  }
</style>