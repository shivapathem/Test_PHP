<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';
include_once __DIR__ . '/../../function-includes/user-scheduling-team-list.php';

$teamOptions = getSchedulingTeamList(auth()->user()->defaultTeamId, 'reports-policy', 'viewBasicReports');

$teamDetails = getSchedulingTeamListArray('reports-policy', 'viewBasicReports');
$intEndWeek = bbcweeknumber(date("Y-m-d"));
$intEndWeek = addweeks($intEndWeek, -1);
$intStartWeek = bbcweeknumber(date('Y-m-d', strtotime('-35 days')));
for ($i = 4; $i >= 0; $i--) {
  $intCurrWeek = addweeks($intStartWeek, $i);
  $arrWeeks[$intCurrWeek] = spinweek($intCurrWeek);
}
$arrSick = GetGlobalSick ($intStartWeek, $intEndWeek, array_column($teamDetails, 'id'));
echo '<h1 class="sr-only">Team Summary</h1>';
echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">';
echo '<br><h2>Sickness Reports across All Teams for weeks '.spinweek($intStartWeek).'-'.spinweek($intEndWeek).'</h2>';
echo 'Total Days Sick '.$arrSick['Totals']['Count'];
echo ' Total Hours Sick '.floor($arrSick['Totals']['Hours'] / 3600).'.'.floor((floor(($arrSick['Totals']['Hours'] / 60) % 60) * 100)/60);
echo '<br><br>';
echo '</div>';

    echo '<table id="sickglobal" class="tablesmalltidy">';
    echo '<thead>';
    echo '<tr>';
    echo '<th width="200px"><br>Totals</th>';
    foreach ($arrWeeks as $intLoopWeek => $strSpunWeek) {
      echo '<th>';
      echo $strSpunWeek.'<br>';
      $arrSick['Totals']['Weeks'][$intLoopWeek]['Count'] = $arrSick['Totals']['Weeks'][$intLoopWeek]['Count'] ?? '';
      $arrSick['Totals']['Weeks'][$intLoopWeek]['Hours'] = $arrSick['Totals']['Weeks'][$intLoopWeek]['Hours'] ?? 0;
      echo 'Shifts '.$arrSick['Totals']['Weeks'][$intLoopWeek]['Count'];
      echo ' ('.floor($arrSick['Totals']['Weeks'][$intLoopWeek]['Hours']/ 3600).'.'.floor((floor(($arrSick['Totals']['Weeks'][$intLoopWeek]['Hours']/ 60) % 60) * 100)/60) .' Hours)';
      echo '</th>';
    }
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($teamDetails as  $team) {
			$intDepID = $team['id'];
			$strDepName = $team['name'];
			if ($intDepID > 0) {
				if (isset($arrSick['Weeks'][$intDepID])) {
					echo '<tr>';
					echo '<td>'.$strDepName.'</td>';
					foreach ($arrWeeks as $intLoopWeek => $strSpunWeek) {
						if (isset($arrSick['Weeks'][$intDepID][$intLoopWeek])) {
							echo '<td data-order="'.$arrSick['Weeks'][$intDepID][$intLoopWeek]['Count'].'">';
							echo $arrSick['Weeks'][$intDepID][$intLoopWeek]['Count'];
							echo ' (';
							echo floor($arrSick['Weeks'][$intDepID][$intLoopWeek]['Hours'] / 3600) . '.' . floor((($arrSick['Weeks'][$intDepID][$intLoopWeek]['Hours'] / 60) % 60) * 100 / 60);
							echo ' Hours)';
							echo '</td>';
						} else {
							echo '<td data-order="0"></td>';
						}
					}
					echo '</tr>';
				}
			}
		}
	
    echo '</tbody>';
    echo '</table>';

?>
<script type="text/javascript">
jQuery.extend(jQuery.fn.dataTableExt.oSort, {
  "justNum-pre": a => parseInt(a.split(" ")[0]),
  "justNum-asc": (a, b) => a - b,
  "justNum-desc": (a, b) => b - a
});

var table = $('#sickglobal').DataTable({
  paging: false,
  scrollY: 600,
  scrollCollapse: true,
  info:     false,
  stateSave: true,
  deferRender: true,
  columnDefs: [
    {
      type: 'justNum',
      targets: 1
    },
    {
      type: 'justNum',
      targets: 2
    },
    {
      type: 'justNum',
      targets: 3
    },
    {
      type: 'justNum',
      targets: 4
    },
    {
      type: 'justNum',
      targets: 5
    }
  ]
});
</script>
