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

$occurrencessData = (new SicknessAreaReport)->getSicknessAreaReportOccurencesBreach($_REQUEST['areaId'], $strStartDate, $strEndDate) ?? [];

echo '<table class="tablesmall" style="width:100%">';
echo '<tr style="background-color:#DDDDDD">';
echo '<th colspan="9">';
echo '<br>Sickness Occurrence Record <button class="sickness-by-area-excel-report-btn" onclick="createSicknessOccurrencesByAreaDocs();"><img src="./images/excel.svg" class="exportIcon">Create</button><br>';
echo '3 Occurrences in 12 Weeks<br>';
echo '5 Occurrences in 52 Weeks<br>';
echo '28 Consecutive Days in 52 Weeks<br><br>';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th width="100px" height="30px">Start Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="sDate" id="sickness-occurrences-report-datepicker-start" value="'.$strStartDate.'" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="slrralternate-occurrences" size="30" value="'.date("l, j F, Y", strtotime($strStartDate)).'"></td>';
echo '<th width="100px">End Date</th>';
echo '<th align="right" width="100px"><input type="hidden" name="eDate" id="sickness-occurrences-report-datepicker-end" value="'.$strEndDate.'" size="20" required/></th>';
echo '<td class="thBackGround" width="250px"><input type="text" id="elrralternate-occurrences" size="30" value="'.date("l, j F, Y", strtotime($strEndDate)).'"></td>';
echo '<th height="30px">';
echo 'Quick Links';
echo '</th>';
echo '<td class="thBackGround">';
echo '<select id="sickness-occurrences-by-area-quick-link" class="chosen-select" >';
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
echo '<td align="left"><button onclick="javascript:sicknessOccurrencesReload()">Go</button></td>';
echo '</tr>';
echo '</table>';

?>

<div id="sickness-occurrencess-report-data-container">
<?php
if(count($occurrencessData) == 0) {
  echo '<table class="tablesmall" style="width:100%">';
  echo '<tr>';
  echo '<th colspan="9" style="text-align:center">';
  echo '<br>No records found <br><br>';
  echo '</th>';
  echo '</tr>';
  echo '<table>';
}
?>
<table style="width: 100%;" class="oddevenclass tablesmall stripe bluetable dataTable no-footer" id="sickness-occurrences-area-report">
  <thead>
    <tr>
    <th class="datecell">Name&nbsp;&nbsp;</th>
    <th class="datecell">Days Unavailable/Sick</th>
    <th class="datecell">Weeks Sick</th>
    <th class="datecell">Max Consec Days</th>
    <th class="datecell">Occurrences</th>
    <th class="datecell">Hours</th>
    <th class="datecell">Most Recent</th>
    <th class="datecell">Type of Occurrence&nbsp;&nbsp;</th>
    </tr>
  </thead>
  <?php
  echo '<tbody>';
  function printRowWithOthrOccurenceTyp($getSicknessOccurrencesReport_v, $class, $occType)
	{
		echo '<tr class="handcursor">';
		echo '<td'.$class.'>'.$getSicknessOccurrencesReport_v['DisplayName'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['DaysUnavailable'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['WeeksSick'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['MaxConsecDays'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['Occurrences'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['TotalHoursSick'].'</td>';
		echo '<td'.$class.'>';
		echo date("jS F Y", strtotime($getSicknessOccurrencesReport_v['MaxDutyDate']));
		echo '</td>';
		echo '<td'.$class.'>'.$occType.'</td>';
		echo '</tr>';
	}
  if(count($occurrencessData) > 0) {
    foreach ($occurrencessData as $getSicknessOccurrencesReport_v) {
        $class = '';
        $occType = '';
        if (($getSicknessOccurrencesReport_v['ThreeOccIn12'] + $getSicknessOccurrencesReport_v['FiveOccIn52'] + $getSicknessOccurrencesReport_v['TwentyEightConsecDaysIn52']) > 1) {
          $class = ' class="pinkeven"';
        }
        if($getSicknessOccurrencesReport_v['ThreeOccIn12'])
        {
        $occType = '12 Weeks, 3 Occurrences';
        }elseif($getSicknessOccurrencesReport_v['FiveOccIn52'])
        {
        $occType = '52 Weeks, 5 Occurrences';
        }elseif($getSicknessOccurrencesReport_v['TwentyEightConsecDaysIn52'])
        {
        $occType = '52 Weeks, 28 Consecutive';
        }
        echo '<tr class="handcursor">';
        echo '<td'.$class.'>'.$getSicknessOccurrencesReport_v['DisplayName'].'</td>';
        echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['DaysUnavailable'].'</td>';
        echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['WeeksSick'].'</td>';
        echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['MaxConsecDays'].'</td>';
        echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['Occurrences'].'</td>';
        echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['TotalHoursSick'].'</td>';
        echo '<td'.$class.'>';
        echo date("jS F Y", strtotime($getSicknessOccurrencesReport_v['MaxDutyDate']));
        echo '</td>';
        echo '<td'.$class.'>'.$occType.'</td>';
        echo '</tr>';
        if(($getSicknessOccurrencesReport_v['ThreeOccIn12'] + $getSicknessOccurrencesReport_v['FiveOccIn52'] + $getSicknessOccurrencesReport_v['TwentyEightConsecDaysIn52']) == 2)
        {
          if($getSicknessOccurrencesReport_v['ThreeOccIn12'])
          {
            $occType = '12 Weeks, 3 Occurrences';
          }
          if($getSicknessOccurrencesReport_v['FiveOccIn52'])
          {
            $occType = '52 Weeks, 5 Occurrences';
          }
          if($getSicknessOccurrencesReport_v['TwentyEightConsecDaysIn52'])
          {
            $occType = '52 Weeks, 28 Consecutive';
          }
          printRowWithOthrOccurenceTyp($getSicknessOccurrencesReport_v, $class, $occType);
        }
        elseif(($getSicknessOccurrencesReport_v['ThreeOccIn12'] + $getSicknessOccurrencesReport_v['FiveOccIn52'] + $getSicknessOccurrencesReport_v['TwentyEightConsecDaysIn52']) == 3)
        {
          printRowWithOthrOccurenceTyp($getSicknessOccurrencesReport_v, $class, '52 Weeks, 5 Occurrences');
          printRowWithOthrOccurenceTyp($getSicknessOccurrencesReport_v, $class, '52 Weeks, 28 Consecutive');
        }
    }
  }
  echo '</tbody>';
  ?>
  </table>
</div>
<form method="post" id="sickness-occurrences-by-area-export-form" action="page-includes/reports/sickness-by-area/sickness-breach-occurrences-by-area-excel.php">
    <input type="hidden" id="exportData-occurrences" name="exportData">
    <input type="hidden" id="exportData-occurrences-colour" name="exportDataRowColour">
</form>
<script>
  $(function() {
      $( "#sickness-occurrences-report-datepicker-start" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#slrralternate-occurrences",
        altFormat: "DD, d MM, yy",
        onSelect: function() {
          $("#sickness-occurrences-by-area-quick-link").prop("selectedIndex", 0);
        }
      });
      $( "#sickness-occurrences-report-datepicker-end" ).datepicker({
        changeMonth: true,
        changeYear: true,
        showOn: "button",
        firstDay: '6',
        buttonImage: "images/calendar.gif",
        buttonImageOnly: true,
        dateFormat: 'yy-mm-dd',
        altField: "#elrralternate-occurrences",
        altFormat: "DD, d MM, yy",
        onSelect: function() {
          $("#sickness-occurrences-by-area-quick-link").prop("selectedIndex", 0);
        }
      });
  });


$(document).ready(function () {
  let totalDataCount = <?php echo count($occurrencessData); ?>;
  totalDataCount > 0 ? initializeSicknessOccurrencesAreaTable() : $("#sickness-occurrences-area-report").hide();
});
var sicknessoccurrencesReportDataTable;
 function initializeSicknessOccurrencesAreaTable() {
  sicknessoccurrencesReportDataTable = $("#sickness-occurrences-area-report").DataTable({
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
    yadcf.init(sicknessoccurrencesReportDataTable, [
        {
            column_number: 0,
            filter_type: 'text'
        },
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
            filter_type: 'select'
        }
    ]);
  }

  function sicknessOccurrencesReload() {
    $('.sickness-by-area-data').remove();
    $('#sickness-leave-report-start-date').val($('#sickness-occurrences-report-datepicker-start').val());
    $('#sickness-leave-report-end-date').val($('#sickness-occurrences-report-datepicker-end').val());
    $('#sickness-leave-report-quick-link').val($('#sickness-occurrences-by-area-quick-link').val());
    sicknessOccurrencesReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
  }

  function createSicknessOccurrencesByAreaDocs() {
    let totalDataCount = <?php echo count($occurrencessData); ?>;
    if(totalDataCount == 0) {
      return false;
    }
    $('#exportData-occurrences').val(JSON.stringify(sicknessoccurrencesReportDataTable.rows({search:'applied'}).data().toArray()));
    let colourRow = [];
    $('#sickness-occurrences-area-report > tbody > tr').each(function(i, obj) {
      if($(obj).find('.pinkeven').length > 0) {
        colourRow.push(i);
      }
    });
    $('#exportData-occurrences-colour').val(JSON.stringify(colourRow));
    $('#sickness-occurrences-by-area-export-form').submit()
  }
</script>