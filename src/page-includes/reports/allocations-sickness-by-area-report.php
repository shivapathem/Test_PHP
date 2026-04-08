<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once '../../page-includes/admin/divisions/process/classDivisionalAdmin.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$areaObject = new ClassDivisionalAdmin();
$areaList = $areaObject->getDivisionsListBasedOnAreaReportRole();
echo '<link href="styles/reports/sickness-area-report/sickness-area-report.css" rel="stylesheet" type="text/css" />';
echo '<div id="sickness-area-by-report-master-container">';
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h1 style="text-align: center;">Sickness by Area</h1><br><br>';
echo '</div>';
echo '
<div>
Select Area : <select id="area-select" class="chosen-select" onchange="refreshSicknessReport()"><option value="">Select Area</option>';
foreach ($areaList as $area) {
		echo '<option value="'.$area['DivisionID'].'">'.$area['DivisionName'].'</option>';
}
echo '</select>
</div>
';
?>
<style>
<?php if(count($areaList) == 1) {?>
#area_select_chosen .chosen-single div b {
    display: none;
}
#area_select_chosen {
  pointer-events: none;
}
<?php } ?>
</style>
<div>
  <input type="hidden" id="sickness-leave-report-start-date">
  <input type="hidden" id="sickness-leave-report-end-date">
  <input type="hidden" id="sickness-leave-report-quick-link">
  <div id="sickness-area-report-tabs">
    <ul>
      <li data-id="sickness-summary"><a href="#sickness-summary">Sickness Summary</a></li>
      <li data-id="sickness-detail"><a href="#sickness-detail">Sickness Detail</a></li>
      <li data-id="sickness-occurrences"><a href="#sickness-occurrences">Sickness Occurrences</a></li>
      <li data-id="sickness-team-cost"><a href="#sickness-team-cost">Sickness Team Cost</a></li>
    </ul>
    <div id="sickness-summary">
    </div>
    <div id="sickness-detail">
    </div>
    <div id="sickness-occurrences">
    </div>
    <div id="sickness-team-cost">
    </div>
  </div>
</div>
</div>
<script>
$( function() {    
    if (typeof $.cookie('sickness_area_report_select') != 'undefined') { 
      $('#area-select option[value="' + $.cookie('sickness_area_report_select') + '"]').attr("selected", "selected");
    }
    <?php if(count($areaList) == 1) { ?> 
      $('#area-select').find('option:eq(1)').prop('selected', true);
    <?php } ?>
    $('#area-select').chosen({no_results_text: "Oops, nothing found!"});
    $("#sickness-area-report-tabs").tabs({
            activate: function(event, ui) {
              switch(ui.newPanel.attr('id')) {
                case 'sickness-summary':
                  sicknessSummaryReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
                  break;
                case 'sickness-detail':
                  sicknessDetailReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
                  break;
                case 'sickness-occurrences':
                  sicknessOccurrencesReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
                  break;
                case 'sickness-team-cost':
                  sicknessTeamCostReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
                  break;
                default:
              }
            }
    });
    sicknessSummaryReport();
});

function refreshSicknessReport() {
  $('.sickness-by-area-data').remove();
  $.cookie("sickness_area_report_select", $('#area-select').val());
  var activeTab = $('div[id="sickness-area-report-tabs"] ul .ui-tabs-active');
    switch(activeTab.attr('data-id')) {
      case 'sickness-summary':
        sicknessSummaryReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
        break;
      case 'sickness-detail':
        sicknessDetailReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
        break;
      case 'sickness-occurrences':
        sicknessOccurrencesReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
        break;
      case 'sickness-team-cost':
        sicknessTeamCostReport($('#sickness-leave-report-start-date').val(), $('#sickness-leave-report-end-date').val(), $('#sickness-leave-report-quick-link').val());
        break;
      default:
    }
}

function sicknessSummaryReport(start = null, end = null, quick = null) {
  if(new Date(start) > new Date(end) && (quick == null || quick == ''))
  {
    alert('End Date can’t be before Start Date');
    return false;
  }
  $.ajax({
    url: "page-includes/reports/sickness-by-area/sickness-summary-by-area.php", 
    type: 'POST',
    data: {
      areaId: $('#area-select').val(),
      sDate: start,
      eDate: end,
      period: quick,
      data: $('#sickness-by-area-data').val()
    },
    success: function(result){
      $("#sickness-summary").html(result);
    }
  });
}

function sicknessDetailReport(start = null, end = null, quick = null) {
  if(new Date(start) > new Date(end) && (quick == null || quick == ''))
  {
    alert('End Date can’t be before Start Date');
    return false;
  }
  $.ajax({
    url: "page-includes/reports/sickness-by-area/sickness-detail-by-area.php", 
    type: 'POST',
    data: {
      areaId: $('#area-select').val(),
      sDate: start,
      eDate: end,
      period: quick,
      data: $('#sickness-by-area-data').val()
    },
    success: function(result){
      $("#sickness-detail").html(result);
    }
  });
}

function sicknessOccurrencesReport(start = null, end = null, quick = null) {
  if(new Date(start) > new Date(end) && (quick == null || quick == ''))
  {
    alert('End Date can’t be before Start Date');
    return false;
  }
  $.ajax({
    url: "page-includes/reports/sickness-by-area/sickness-breach-occurrences-by-area.php", 
    type: 'POST',
    data: {
      areaId: $('#area-select').val(),
      sDate: start,
      eDate: end,
      period: quick,
      data: $('#sickness-by-area-data').val()
    },
    success: function(result){
      $("#sickness-occurrences").html(result);
    }
  });
}

function sicknessTeamCostReport(start = null, end = null, quick = null) {
  if(new Date(start) > new Date(end) && (quick == null || quick == ''))
  {
    alert('End Date can’t be before Start Date');
    return false;
  }
  $.ajax({
    url: "page-includes/reports/sickness-by-area/sickness-team-cost-by-area.php", 
    type: 'POST',
    data: {
      areaId: $('#area-select').val(),
      sDate: start,
      eDate: end,
      period: quick,
      data: $('#sickness-by-area-data').val()
    },
    success: function(result){
      $("#sickness-team-cost").html(result);
    }
  });
}
</script>


