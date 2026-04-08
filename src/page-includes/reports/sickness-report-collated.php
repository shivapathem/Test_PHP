<?php
 session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';
$db = OpenDatabase();
$intTeamID = $_REQUEST['teamId'];
$edate = date("Y-m-d");
  $intID = 1;
  $intVal1 = 12;
  $intVal2 = 3;
  $intVal3 = 52;
  $intVal4 = 5;
  $intVal5 = 52;
  $intVal6 = 28;
  $row['Description'] = $row['Description'] ?? null;
  $strDescription = $row['Description'];
  $getSicknessOccurrencesReport	=	getSicknessOccurrencesReport($intTeamID);
  function printRowWithOthrOccurenceTyp($intTeamID, $getSicknessOccurrencesReport_v, $class, $occType)
	{
		echo '<tr class="handcursor" onclick="javascript:ShowIndividualSickness('.$intTeamID.', \''.$getSicknessOccurrencesReport_v['SchedulingPersonID'].'\')">';
		echo '<td'.$class.'>'.$getSicknessOccurrencesReport_v['DisplayName'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['DaysUnavailable'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['WeeksSick'].'</td>';            
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['MaxConsecDays'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['Occurrences'].'</td>';
		echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['TotalHoursSick'].'</td>';
		echo '<td'.$class.'>';
		echo '<span style="display:none">'.date("Ymd", strtotime($getSicknessOccurrencesReport_v['MaxDutyDate'])).'</span>';
		echo date("jS F Y", strtotime($getSicknessOccurrencesReport_v['MaxDutyDate']));
		echo '</td>'; 
		echo '<td'.$class.'>'.$occType.'</td>';
		echo '</tr>';
	}

  echo '<div class="tableheadersmall medtextbold">';
  echo '<br>'.$strDescription.'<br>';
  echo '3 Occurrences in 12 Weeks<br>';
  echo '5 Occurrences in 52 Weeks<br>';
  echo '28 Consecutive Days in 52 Weeks<br>';
  echo '<br><br>';
  echo '</div><br>';
  if(count($getSicknessOccurrencesReport) > 0) {
  echo '<table id="depsickcoltable-'.$intTeamID.'" class="tablesmall compact stripe" style="width:800px">';
  echo '<thead>';
  echo '<tr>';
  echo '<th class="datecell">Name&nbsp;&nbsp;</th>';
  echo '<th class="datecell">Days Unavailable/Sick</th>';
  echo '<th class="datecell">Weeks Sick</th>'; 
  echo '<th class="datecell">Max Consec Days</th>';
  echo '<th class="datecell">Occurrences</th>';
  echo '<th class="datecell">Hours</th>';
  echo '<th class="datecell">Most Recent</th>';
  echo '<th class="datecell">Type of Occurrence&nbsp;&nbsp;</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  if(count($getSicknessOccurrencesReport) > 0) {
    foreach ($getSicknessOccurrencesReport as $getSicknessOccurrencesReport_v) {
      $class = '';//echo "<pre>";print_r($getSicknessOccurrencesReport_v);//die;
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
      echo '<tr class="handcursor" onclick="javascript:ShowIndividualSickness('.$intTeamID.', \''.$getSicknessOccurrencesReport_v['SchedulingPersonID'].'\')">';
      echo '<td'.$class.'>'.$getSicknessOccurrencesReport_v['DisplayName'].'</td>';
      echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['DaysUnavailable'].'</td>';
      echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['WeeksSick'].'</td>';            
      echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['MaxConsecDays'].'</td>';
      echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['Occurrences'].'</td>';
      echo '<td align="center"'.$class.'>'.$getSicknessOccurrencesReport_v['TotalHoursSick'].'</td>';
      echo '<td'.$class.'>';
      echo '<span style="display:none">'.date("Ymd", strtotime($getSicknessOccurrencesReport_v['MaxDutyDate'])).'</span>';
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
		printRowWithOthrOccurenceTyp($intTeamID, $getSicknessOccurrencesReport_v, $class, $occType);
	  }
	  elseif(($getSicknessOccurrencesReport_v['ThreeOccIn12'] + $getSicknessOccurrencesReport_v['FiveOccIn52'] + $getSicknessOccurrencesReport_v['TwentyEightConsecDaysIn52']) == 3)
	  {
		printRowWithOthrOccurenceTyp($intTeamID, $getSicknessOccurrencesReport_v, $class, '52 Weeks, 5 Occurrences');
		printRowWithOthrOccurenceTyp($intTeamID, $getSicknessOccurrencesReport_v, $class, '52 Weeks, 28 Consecutive');
	  }
    }
  }
  echo '</tbody>';
  echo '</table>';

?>

<script type="text/javascript">

$(document).ready(function(){
    var table = $("#depsickcoltable-<?php echo $intTeamID?>").DataTable({
      paging: false,
      scrollY: 400,
      scrollCollapse: true,
      info:     false,
      deferRender:    true,
      aaSorting: [[0, 'asc']],
  columnDefs: [
    { width: 100, targets: 3 },
    { width: 100, targets: 4 },
    { width: 100, targets: 5 },
    { width: 100, targets: 6 },
    { width: 100, targets: 7 },  
  ],       
    });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
    {column_number: 7,
      filter_type: 'select'
    },    
  ]);
  table.columns.adjust().draw();
});
</script>
<?php
}

?>