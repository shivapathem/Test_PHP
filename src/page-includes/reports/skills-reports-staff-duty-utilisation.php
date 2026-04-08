<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';

$intDepartmentID = $_REQUEST['departmentid'];
if (!empty($_REQUEST['BST'])) {
  $bst = $_REQUEST['BST'];
}
else {
  $bst = 0;
}
if($bst)
{
	$bstCond = 'skills_duties.BST = 1';
}else
{
	$bstCond = 'skills_duties.GMT = 1';
}
$pdo = OpenDBLinkA7();

$intDutyCount = CountDutiesInDepartment($intDepartmentID, $bst);

// Now get the skills for everyone in this base.....

$query = "SELECT	ud.UD_DisplayName AS FullName,
		UD_StaffNumber AS StaffNumber,
		sp.ID AS progid,
		sp.TeamID 
FROM		 skills_programmes sp(nolock)
INNER JOIN   skills_programmes_staff_link spl (nolock) ON spl.programmes_id = sp.ID
INNER JOIN   UserDetails ud (nolock) ON ud.UD_UserID = spl.UserID
INNER JOIN   ScheduledPersonTeam_LINK sptl (nolock) ON ud.UD_UserID = sptl.ScheduledPersonID
WHERE		 sptl.TeamID = ? AND isNull(sptl.EndDate, 9999-12-31) >= getdate() AND sp.TeamID = ?
Order By FullName";

$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
$stmt->bindParam(2, $intDepartmentID, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
  $arrstaff[$row['StaffNumber']]['name'] = $row['FullName'];
  $arrstaff[$row['StaffNumber']]['progs'][$row['progid']] = $row['TeamID'];
}

//if (isset($arrstaff)) {
    // Now get the duties for this base and link them to the programmes.....
    $query = "SELECT        skills_duties.ID, skills_programmes.ID AS ProgrammeID
              FROM          skills_duties 
              INNER JOIN    skills_duties_programmes_link ON skills_duties.ID = skills_duties_programmes_link.duties_id 
              INNER JOIN    skills_programmes ON skills_duties_programmes_link.programmes_id = skills_programmes.ID
              WHERE         ($bstCond) 
              AND           (skills_duties.TeamID = ?)
              ORDER BY      skills_duties.duty";
$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($result as $row) {
    $id = $row['ID'];
    $arrduties[$id][$row['ProgrammeID']] = $row['ProgrammeID'];
  }
if (isset($arrduties) && isset($arrstaff)) {
  echo '<div class="tableheadersmall medtextbold" style="width:100%">';
  echo '<br>There are '.$intDutyCount.' duties in this Department.';
  echo '<br><br>';
  echo '</div><br>';

  echo '<table id="skillsstaffdutyutilisationtable-'.$intDepartmentID.'-'.$bst.'" class="tablesmall compact stripe" width="100%">';
  echo '<thead>';
  echo '<tr>';
  echo '<th>';
  echo 'Name';
  echo '</th>';
  echo '<th>';
  echo 'Duty Count';
  echo '</th>';
  echo '<th>';
  echo 'Percentage';
  echo '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';


  foreach ($arrstaff as $thisstaffnumber => $arrPerson) {
    $dutycount = 0;
    foreach ($arrduties as $dutyid => $dutyprogs) {
      $cando = 1;
      foreach ($dutyprogs as $dutyprogid => $dutybase) {
        // The duty progid needs to match an enrty in the staff
        if (!isset($arrPerson['progs'][$dutyprogid])) {
          $cando = 0;
        }
      }
      $dutycount = $dutycount + $cando;
    }
    if ($dutycount != 0) {
      $percentage  = number_format((($dutycount / $intDutyCount) * 100), 2).'%';
    }
    else {
      $percentage = '';
    }

    echo '<tr>';

    echo '<td>';
    echo $arrPerson['name'];
    echo '</td>';

    echo '<td>';  

      echo $dutycount;

    echo '</td>';

    echo '<td>';
    echo $percentage;
    echo '</td>';

  echo '</tr>';
  }
  echo '</tbody>';
  echo '</table>';
?>  
<script type="text/javascript">

$(document).ready(function() {
  var table = $("#skillsstaffdutyutilisationtable-<?php echo $intDepartmentID?>-<?php echo $bst?>").DataTable({
    paging: false,
    scrollY: 450,
    info:     false,
    stateSave: false,
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
  echo '<br>There are no Duties defined<br><br>';
  echo '</div>';
}
echo '<br><br>';

?>
