<?php
session_start();
include_once '../../../function-includes/init.php';
include_once '../../../class-includes/reports/sickness/SicknessAreaReport.php';

$arrPeriods = array( '' => '---',
                     -12 => '1 Year Back',
                     -9  => '9 Months Back',
                     -6  => '6 Months Back',
                     -3  => '3 Months Back',
                     -1 => '1 Month Back',
                      2  => 'Previous Fortnight',
                      1  => 'Previous Week',                           
                   );

if (isset($_REQUEST['period']) && !empty($_REQUEST['period'])) {
  $intPeriod = $_REQUEST['period'];
  if ($intPeriod < 0) {
    // Backwards  in Months
    $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
    $strEndDate = date("Y-m-d");  
  }
  else {
    // Backwards in Weeks
    $strStartDate = date("Y-m-d", strtotime(-$intPeriod." weeks"));
    $strEndDate = date("Y-m-d");  
  
  }
}  
else {
  if (isset($_REQUEST['sDate']) && !empty($_REQUEST['sDate'])) {
    $intPeriod = 0; 
    $strStartDate = $_REQUEST['sDate'];
    $strEndDate = $_REQUEST['eDate'];
  }
  else {
    $intPeriod = -3;  
    $strStartDate = date("Y-m-d", strtotime($intPeriod." months"));
    $strEndDate = date("Y-m-d");
  }  
}

if(empty($_REQUEST['areaId'])) {
  echo '<table class="tablesmall" style="width:100%">';
  echo '<tr>';
  echo '<th colspan="9" style="text-align:center">';
  echo '<br>Please select Area <br><br>';
  echo '</th>';
  echo '</tr>';
  echo '<table>';
  die;
}

$data = !empty($_REQUEST['data']) ? json_decode($_REQUEST['data'], true) : (new SicknessAreaReport)->getSicknessAreaReport($_REQUEST['areaId'], $strStartDate, $strEndDate);

echo '<input type="hidden" class="sickness-by-area-data" id="sickness-by-area-data" value="' . htmlentities(json_encode($data)) . '">';

$detailData = $data [3] ?? [];
$totalSickness = 0;
$totalDutyDuration = 0;
if(count($detailData) > 0) {
  foreach($detailData as $data) {
    $totalSickness = $data['SickDuration'] + $totalSickness;
    $totalDutyDuration = $data['Duration of Duty'] + $totalDutyDuration;
  }
}
echo '<table class="tablesmall" style="width:100%">';
echo '<tr>';
if(count($detailData) > 0) {
echo '<th colspan="9">';
echo '<br>Average Area Sickness Percentage : ' . round((($totalSickness/$totalDutyDuration) * 100), 2) . '%' ;
echo '<br>Approximate Total Area Sickness Cost : £' . number_format($totalSickness * 25);
echo '<button class="sickness-by-area-excel-report-btn" onclick="createSicknessTeamCostByAreaDocs();"><img src="./images/excel.svg" class="exportIcon">Create</button><br><br>';
echo '</th>';
}
echo '</tr>';
echo '<tr>';
echo '<th width="100px" height="30px">Start Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="sDate" id="sickness-team-cost-report-datepicker-start" value="'.$strStartDate.'" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="slrralternate-team-cost" size="30" value="'.date("l, j F, Y", strtotime($strStartDate)).'"></td>';
echo '<th width="100px">End Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="eDate" id="sickness-team-cost-report-datepicker-end" value="'.$strEndDate.'" size="20" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="elrralternate-team-cost" size="30" value="'.date("l, j F, Y", strtotime($strEndDate)).'"></td>';
echo '<th  height="30px">';
echo 'Quick Links';
echo '</th>';
echo '<td class="thBackGround">';
echo '<select id="sickness-team-cost-by-area-quick-link" class="chosen-select" >';
foreach ($arrPeriods as $thisperiod => $text) {
  if ($thisperiod == $intPeriod) {
    echo '<option selected value="'.$thisperiod.'">'.$text.'</option>';
  }
  else {
    echo '<option value="'.$thisperiod.'">'.$text.'</option>';
  }
}
echo '</select>';

echo '</td>';
echo '<td align="left"><button onclick="javascript:sicknessTeamCostReload()">Go</button></td>';
echo '</tr>';
echo '</table>';

?>

<div id="sickness-team-costs-report-data-container">
<?php 
if(count($detailData) == 0) {
  echo '<table class="tablesmall" style="width:100%">';
  echo '<tr>';
  echo '<th colspan="9" style="text-align:center">';
  echo '<br>No records found <br><br>';
  echo '</th>';
  echo '</tr>';
  echo '<table>';
}
?>
<table style="width: 100%;" class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="sickness-team-cost-area-report">
    <thead>
      <tr>
        <th>Area</th>
        <th>Home Scheduling Team</th>
        <th>Sickness Percentage <i title="This figure shows the total hours of sickness for people who have this team as their Home Team for the period selected divided by the sum of the Actual Hours for the same people for that period, expressed as a percentage to two decimal places." style="color: blue;" class="fa fa-info-circle" aria-hidden="true"></i></th>
        <th>Sickness Cost</th>
      </tr>
    </thead>
    <tbody>
        <?php 
        foreach($detailData as $data) {
          echo '<tr>';
          echo '<td>' . $data['Area'] . '</td>';
          echo '<td>' . $data['Home Scheduling Team'] . '</td>';
          echo '<td>' .  round((($data['SickDuration']/$data['Duration of Duty']) * 100), 2) . '% </td>';
          echo '<td>£' . number_format($data['SickDuration'] * 25) . '</td>';
          echo '</tr>';
        }
        ?>
    </tbody>
  </table>
</div>

<form method="post" id="sickness-team-cost-by-area-export-form" action="page-includes/reports/sickness-by-area/sickness-team-cost-by-area-excel.php">
    <input type="hidden" id="exportData-team-cost" name="exportData">
</form>

<script>
  $(function() {
      $( "#sickness-team-cost-report-datepicker-start" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#slrralternate-team-cost",
        altFormat: "DD, d MM, yy",
        onSelect: function() {
          $("#sickness-team-cost-by-area-quick-link").prop("selectedIndex", 0);
        }
      });
      $( "#sickness-team-cost-report-datepicker-end" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#elrralternate-team-cost",
        altFormat: "DD, d MM, yy",
        onSelect: function() {
          $("#sickness-team-cost-by-area-quick-link").prop("selectedIndex", 0);
        }
      });
  });

  
$(document).ready(function () {
  let totalDataCount = <?php echo count($detailData); ?>;
  totalDataCount > 0 ? initializeSicknessTeamCostAreaTable() : $("#sickness-team-cost-area-report").hide();
});
var sicknessTeamCostReportDataTable;
 function initializeSicknessTeamCostAreaTable() {
  sicknessTeamCostReportDataTable = $("#sickness-team-cost-area-report").DataTable({
        lengthChange: false,
        paging: false,
        iDisplayLength: 5,
        info: false,
        stateSave: true,
        deferRender: true,
        scrollCollapse: true,
        scrollX: true,
        "scrollY": "37vh",
        dom: 'Bfrtip'
    });
    yadcf.init(sicknessTeamCostReportDataTable, [
        {
            column_number: 1,
            filter_type: 'text'
        },
        {
            column_number: 2,
            filter_type: 'text'
        },
        {
            column_number: 3,
            filter_type: 'text'
        }
    ]);
  }

  function sicknessTeamCostReload() {
    $('.sickness-by-area-data').remove();
    $('#sickness-leave-report-start-date').val($('#sickness-team-cost-report-datepicker-start').val());
    $('#sickness-leave-report-end-date').val($('#sickness-team-cost-report-datepicker-end').val()); 
    $('#sickness-leave-report-quick-link').val($('#sickness-team-cost-by-area-quick-link').val());
    sicknessTeamCostReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
  }

  function createSicknessTeamCostByAreaDocs() {
    let totalDataCount = <?php echo count($detailData); ?>;
    if(totalDataCount == 0) {
      return false;
    }
    $('#exportData-team-cost').val(JSON.stringify(sicknessTeamCostReportDataTable.rows({search:'applied'}).data().toArray()));
    $('#sickness-team-cost-by-area-export-form').submit();
  }
</script>