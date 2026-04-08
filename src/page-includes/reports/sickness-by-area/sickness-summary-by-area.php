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

$summaryData = $data[1] ?? [];

echo '<table class="tablesmall" style="width:100%">';
echo '<tr>';
echo '<th colspan="9">';
echo '<br>Sickness Summary Record <button class="sickness-by-area-excel-report-btn" onclick="createSicknessSummaryByAreaDocs();"><img src="./images/excel.svg" class="exportIcon">Create</button><br><br>';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px" height="30px">Start Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="sDate" id="sickness-summary-report-datepicker-start" value="'.$strStartDate.'" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="slrralternate-summary" size="30" value="'.date("l, j F, Y", strtotime($strStartDate)).'"></td>';
echo '<th width="100px">End Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="eDate" id="sickness-summary-report-datepicker-end" value="'.$strEndDate.'" size="20" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="elrralternate-summary" size="30" value="'.date("l, j F, Y", strtotime($strEndDate)).'"></td>';
echo '<th height="30px">';
echo 'Quick Links';
echo '</th>';
echo '<td class="thBackGround" >';
echo '<select class="chosen-select" id="sickness-summary-by-area-quick-link">';
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
echo '<td align="left"><button onclick="javascript:sicknessSummaryReload()">Go</button></td>';
echo '</tr>';
echo '</table>';

$summaryDataList = [
  'Approximate total cost of sickness' => '£ ' . number_format((($summaryData[1][''] ?? 0) * 25) ?? 0),
  'Total number of individuals who have had at least one day Sick with a positive duration' => number_format($summaryData[0][''] ?? 0),
  'Total duration of sickness in hours' => number_format($summaryData[1][''] ?? 0),
  'Total number of sickness days (including zero hours)' => number_format($summaryData[2][''] ?? 0),
  'Total number of sickness days (excluding zero hours)' => number_format($summaryData[3][''] ?? 0),  
  'Total number of sickness days (including zero hours) on Saturdays' => number_format($summaryData[9][''] ?? 0),
  'Total number of sickness days (including zero hours) on Sundays' => number_format($summaryData[10][''] ?? 0),
  'Total number of sickness days (including zero hours) on Mondays' => number_format($summaryData[4][''] ?? 0),
  'Total number of sickness days (including zero hours) on Tuesdays' => number_format($summaryData[5][''] ?? 0),
  'Total number of sickness days (including zero hours) on Wednesdays' => number_format($summaryData[6][''] ?? 0),
  'Total number of sickness days (including zero hours) on Thursdays' => number_format($summaryData[7][''] ?? 0),
  'Total number of sickness days (including zero hours) on Fridays' => number_format($summaryData[8][''] ?? 0)
];

?>

<div id="sickness-summary-report-data-container">
  <ul>
    <?php foreach($summaryDataList as $name => $count) {
        echo '<li>' . $name . ' <strong>' . $count . ' </strong></li>';
    }?>
  </ul>
</div>
<form method="post" id="sickness-summary-by-area-export-form" action="page-includes/reports/sickness-by-area/sickness-summary-by-area-excel.php">
    <input type="hidden" id="exportData-summary" name="exportData" value="<?php echo htmlspecialchars(json_encode( $summaryDataList )); ?>">
</form>
<script>
  $(function() {
      $( "#sickness-summary-report-datepicker-start" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#slrralternate-summary",
        altFormat: "DD, d MM, yy",
        onSelect: function() {
          $("#sickness-summary-by-area-quick-link").prop("selectedIndex", 0);
        }
      });
      $( "#sickness-summary-report-datepicker-end" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#elrralternate-summary",
        altFormat: "DD, d MM, yy",
        onSelect: function() {
          $("#sickness-summary-by-area-quick-link").prop("selectedIndex", 0);
        }
      });
  });

  function sicknessSummaryReload() {
    $('.sickness-by-area-data').remove();
    $('#sickness-leave-report-start-date').val($('#sickness-summary-report-datepicker-start').val());
    $('#sickness-leave-report-end-date').val($('#sickness-summary-report-datepicker-end').val()); 
    $('#sickness-leave-report-quick-link').val($('#sickness-summary-by-area-quick-link').val());
    sicknessSummaryReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
  }

  function createSicknessSummaryByAreaDocs() {
      $('#sickness-summary-by-area-export-form').submit()
  }
</script>