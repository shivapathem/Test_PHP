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

$detailsData = $data[0] ?? [];

echo '<table class="tablesmall" style="width:100%">';
echo '<tr>';
echo '<th colspan="9">';
echo '<br>Sickness Detail Record <button class="sickness-by-area-excel-report-btn" onclick="createSicknessDetailByAreaDocs();"><img src="./images/excel.svg" class="exportIcon">Create</button><br><br>';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px" height="30px">Start Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="sDate" id="sickness-detail-report-datepicker-start" value="'.$strStartDate.'" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="slrralternate-detail" size="30" value="'.date("l, j F, Y", strtotime($strStartDate)).'"></td>';
echo '<th width="100px">End Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="eDate" id="sickness-detail-report-datepicker-end" value="'.$strEndDate.'" size="20" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="elrralternate-detail" size="30" value="'.date("l, j F, Y", strtotime($strEndDate)).'"></td>';
echo '<th  height="30px">';
echo 'Quick Links';
echo '</th>';
echo '<td class="thBackGround">';
echo '<select id="sickness-detail-by-area-quick-link" class="chosen-select" >';
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
echo '<td align="left"><button onclick="javascript:sicknessDetailReload()">Go</button></td>';
echo '</tr>';
echo '</table>';

?>

<div id="sickness-details-report-data-container">
<?php 
if(count($detailsData) == 0) {
  echo '<table class="tablesmall" style="width:100%">';
  echo '<tr>';
  echo '<th colspan="9" style="text-align:center">';
  echo '<br>No records found <br><br>';
  echo '</th>';
  echo '</tr>';
  echo '<table>';
}
?>
<table style="width: 100%;" class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="sickness-detail-area-report">
    <thead>
      <tr>
        <th>Area</th>
        <th>Display Forename</th>
        <th>Display Surname</th>
        <th>Staff Number</th>
        <th>Network Login</th>
        <th>Home Scheduling Team</th>
        <th>Date of Sickness</th>
        <th>Day of the Week</th>
        <th>Name of Sickness</th>
        <th>Duration of Sickness</th>
      </tr>
    </thead>
    <tbody>
        <?php 
        foreach($detailsData as $data) {
          echo '<tr>';
          echo '<td>' . $data['Area'] . '</td>';
          echo '<td>' . $data['Display Forename'] . '</td>';
          echo '<td>' . $data['Display Surname'] . '</td>';
          echo '<td>' . $data['Staff Number'] . '</td>';
          echo '<td>' . $data['Network Login'] . '</td>';
          echo '<td>' . $data['Home Scheduling Team'] . '</td>';
          echo '<td>' . date("d/m/Y", strtotime($data['Date of Sickness'])) . '</td>';
          echo '<td>' . $data['Day of the Week'] . '</td>';
          echo '<td>' . $data['Name of Sickness'] . '</td>';
          echo '<td>' . $data['Duration of Sickness'] . '</td>';
          echo '</tr>';
        }
        ?>
    </tbody>
  </table>
</div>

<form method="post" id="sickness-detail-by-area-export-form" action="page-includes/reports/sickness-by-area/sickness-detail-by-area-excel.php">
    <input type="hidden" id="exportData-detail" name="exportData">
</form>

<script>
  $(function() {
      $( "#sickness-detail-report-datepicker-start" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#slrralternate-detail",
        altFormat: "DD, d MM, yy",
        onSelect: function() {
          $("#sickness-detail-by-area-quick-link").prop("selectedIndex", 0);
        }
      });
      $( "#sickness-detail-report-datepicker-end" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#elrralternate-detail",
        altFormat: "DD, d MM, yy",
        onSelect: function() {
          $("#sickness-detail-by-area-quick-link").prop("selectedIndex", 0);
        }
      });
  });

  
$(document).ready(function () {
  let totalDataCount = <?php echo count($detailsData); ?>;
  totalDataCount > 0 ? initializeSicknessDetailAreaTable() : $("#sickness-detail-area-report").hide() ;
});
var sicknessDetailReportDataTable;
 function initializeSicknessDetailAreaTable() {
  sicknessDetailReportDataTable = $("#sickness-detail-area-report").DataTable({
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
    yadcf.init(sicknessDetailReportDataTable, [
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
        },
        {
            column_number: 4,
            filter_type: 'text'
        },
        {
            column_number: 5,
            filter_type: 'text'
        },
        {
            column_number: 6,
            filter_type: 'text'
        },
        {
            column_number: 7,
            filter_type: 'text'
        },
        {
            column_number: 8,
            filter_type: 'text'
        },
        {
            column_number: 9,
            filter_type: 'text'
        }
    ]);
  }

  function sicknessDetailReload() {
    $('.sickness-by-area-data').remove();
    $('#sickness-leave-report-start-date').val($('#sickness-detail-report-datepicker-start').val());
    $('#sickness-leave-report-end-date').val($('#sickness-detail-report-datepicker-end').val()); 
    $('#sickness-leave-report-quick-link').val($('#sickness-detail-by-area-quick-link').val());
    sicknessDetailReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
  }

  function createSicknessDetailByAreaDocs() {
    let totalDataCount = <?php echo count($detailsData); ?>;
    if(totalDataCount == 0) {
      return false;
    }
    $('#exportData-detail').val(JSON.stringify(sicknessDetailReportDataTable.rows({search:'applied'}).data().toArray()));
    $('#sickness-detail-by-area-export-form').submit();
  }
</script>