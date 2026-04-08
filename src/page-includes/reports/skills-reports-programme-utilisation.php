<?php
date_default_timezone_set('UTC');
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/reports-functions.php';
include_once '../../function-includes/DBHelper.php';
$totalcount = 0;
$pdo = OpenDBLinkA7();
$intDepartmentID = $_REQUEST['departmentid'];

// Count of staff
$intStaffCount = CountStaffInDepartmentWithSkill($intDepartmentID);
// Get the count of programmes for this base
$intProgCount = CountProgrammesInDepartment($intDepartmentID);

// Now get the skills for everyone in this base.....
$query = "SELECT skills_programmes.programmename, COUNT(UserDetails.UD_UserID) AS CountStaff, schedulingTeams.schedulingTeamId, skills_programmes.ID, schedulingTeams.schedulingTeamName AS DepartmentName
          FROM          skills_programmes 
          INNER JOIN    skills_programmes_staff_link ON skills_programmes.ID = skills_programmes_staff_link.programmes_id 
          INNER JOIN    UserDetails ON skills_programmes_staff_link.UserID = UserDetails.UD_UserID
          INNER JOIN    schedulingTeams ON skills_programmes.TeamID = schedulingTeams.schedulingTeamId
          WHERE         (skills_programmes.TeamID = ?)
          GROUP BY      skills_programmes.programmename, schedulingTeams.schedulingTeamId, skills_programmes.ID, schedulingTeams.schedulingTeamName
          HAVING        (schedulingTeams.schedulingTeamId = ?)
          ORDER BY      skills_programmes.programmename"; 
$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
$stmt->bindParam(2, $intDepartmentID, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
  $arrprogrammes[$row['ID']]['name'] = $row['programmename'];
  
  if ($row['schedulingTeamId'] == $intDepartmentID) {
    $arrprogrammes[$row['ID']][$row['schedulingTeamId']] = $row['CountStaff'];
  }
  else {
    $arrprogrammes[$row['ID']]['AdditionalDepartments'][$row['schedulingTeamId']] = $row['DepartmentName'].' - '.$row['CountStaff'];  
  }  
}

if (isset($arrprogrammes)) {
  $table = '<table id="skillsprogsutilisationtable-'.$intDepartmentID.'" class="tablesmall compact stripe" width="100%">';
  $table.= '<thead>';
  $table.= '<tr>';
  $table.= '<th>';
  $table.= 'Programme';
  $table.= '</th>';
  $table.= '<th>';
  $table.= 'Staff Count';
  $table.= '</th>';
  $table.= '<th>';
  $table.= 'Percentage';
  $table.= '</th>';
  $table.= '</tr>';
  $table.= '</thead>';
  $table.= '<tbody>';

  foreach ($arrprogrammes as $programme) {
    if (isset($programme[$intDepartmentID])) {
      $staffcount = $programme[$intDepartmentID];
      $staffpercentage  = number_format((($staffcount / $intStaffCount) * 100), 2);
    }
    else {
      $staffcount = 0;
      $staffpercentage  = number_format(0, 2);
    }
  $totalcount = $totalcount + $staffcount;
  $table.= '<tr>';

  $table.= '<td>';
  $table.= $programme['name'];
  $table.= '</td>';

  $table.= '<td>';
  $table.= $staffcount;
  $table.= '</td>';

  $table.= '<td>';
  $table.= $staffpercentage.'%';
  $table.= '</td>';
  $table.= '</td>';

  $table.= '</tr>';
  }
  $table.= '</tbody>';
  $table.= '</table>';

  $averagecount = round($totalcount / $intProgCount);
  $average[0] = 0;
  $average[1] = 0;
  $average[2] = 0;
  foreach ($arrprogrammes as $sn => $programme) {
    $progcountcount = $programme[$intDepartmentID];
    if ($progcountcount > $averagecount) {
      $average[0] = $average[0] + 1;
    }
    elseif ($progcountcount == $averagecount) {
      $average[1] = $average[1] + 1;
    }
    else {
      $average[2] = $average[2] + 1;
    }
  }
  echo '<div class="tableheadersmall medtextbold" style="width:100%">';
  echo '<br>There are '.$intStaffCount.' people with at least one skill and '.$intProgCount.' skills in this Scheduling Group.<br>';
  echo 'The average number of staff able to do a skill is '.$averagecount.' ('.number_format((($averagecount / $intProgCount) * 100), 2).'%)<br>';
  echo 'There are '.$average[0].' skills above this average, ';
  echo $average[1].' matching this average ';
  echo 'and '.$average[2].' skills below the average.<br><br>';
  echo '</div><br>';
  echo $table;
?>
<script type="text/javascript">

$(document).ready(function() {
  var table = $("#skillsprogsutilisationtable-<?php echo $intDepartmentID?>").DataTable({
    paging: false,
    scrollY: 450,
    info:     false,
    stateSave: true,
    deferRender: true,
    "initComplete": function( settings, json ) {
    }
  });
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
  ]);
});

</script>
<?php
}
else {
  echo '<br><div class="tableheadersmall medtextbold" style="width:100%">';
  echo '<br>There are no Programmes defined<br><br>';
  echo '</div>';
}
echo '<br><br>';

?>
