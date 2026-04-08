<?php
session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/reports-functions.php';

$pdo = OpenDBLinkA7();
$intDepartmentID = $_REQUEST['departmentid'];
if (isset($_REQUEST['BST'])) {
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
// Count of staff
$intStaffCount = CountStaffInDepartmentWithSkill($intDepartmentID);

// Now get all staff, their base and programmes they can do....
$query = "SELECT        skills_programmes.ID, programmename, UD_StaffNumber StaffNumber, skills_programmes.TeamID AS DepartmentID
          FROM          skills_programmes 
          INNER JOIN    skills_programmes_staff_link ON skills_programmes.ID = skills_programmes_staff_link.programmes_id 
          INNER JOIN    UserDetails ON UD_UserID = UserID
          WHERE         (skills_programmes.TeamID = ?)
          ORDER BY UD_StaffNumber";
$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $row) {
  $sn = $row['StaffNumber'];
  $arrprogs[$sn]['DepartmentID'] = $row['DepartmentID'];
  $arrprogs[$sn]['progs'][$row['ID']] = $row['ID']; 
}
// Now get the duties for this base and link them to the programmes.....
$query = "SELECT        skills_duties.ID, skills_duties.description, skills_duties.duty, skills_duties_programmes_link.programmes_id
		  FROM         	skills_duties INNER JOIN
                        skills_duties_programmes_link ON skills_duties.ID = skills_duties_programmes_link.duties_id INNER JOIN
                        skills_programmes ON skills_duties_programmes_link.programmes_id = skills_programmes.ID
		  WHERE         ($bstCond) AND (skills_duties.TeamId = ?)
		  ORDER BY skills_duties.duty";

$stmt = $pdo->prepare($query);
$stmt->bindParam(1, $intDepartmentID, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
  $id = $row['ID'];
  $arrduties[$id]['dutyname'] = $row['duty'].' '.$row['description'];
  $arrduties[$id]['progs'][$row['programmes_id']] = $row['programmes_id'];  
}




if (isset($arrduties)) {
  echo '<div class="tableheadersmall medtextbold" style="width:100%">';
  echo '<br>There are '.$intStaffCount.' people with at least one skill in this Team.<br><br>';
  echo '</div><br>';

  echo '<table id="skillsdutyutilisationtable-'.$intDepartmentID.'-'.$bst.'" class="tablesmall compact stripe" width="100%">';
  echo '<thead>'; 
  echo '<tr>';  
  echo '<th>';  
  echo 'Duty';
  echo '</th>';
  echo '<th>';  
  echo 'Staff Count'; 
  echo '</th>';
  echo '<th>';  
  echo 'Percentage'; 
  echo '</th>';
  echo '</tr>';
  echo '</thead>'; 
  echo '<tbody>'; 
  foreach ($arrduties as $duty) {
    unset ($staffcount);
    foreach ($arrprogs as $sn => $person) {                        //Loop through all the staff
      $cando = 1;
      $personDepartmentID = $person['DepartmentID'];
 
      foreach ($duty['progs'] as $progid) {                 // match the progids with the person. If 1 doesn't match they can't do it!   
        if (!isset($person['progs'][$progid])) {
          $cando = 0;
        }    
      }
      if (isset($staffcount[$personDepartmentID])) {
         $staffcount[$personDepartmentID] += $cando;
      }
      else {
        $staffcount[$personDepartmentID] = 0;  
      }
    }
    $percentage  = number_format((($staffcount[$intDepartmentID] / $intStaffCount) * 100), 2); 
    
    echo '<tr>';
    echo '<td>'; 
    echo $duty['dutyname'];
    echo '</td>';

  echo '<td>'; 
  echo $staffcount[$intDepartmentID];
  echo '</td>';

  echo '<td>'; 
  echo $percentage.'%';
  echo '</td>';
  echo '</tr>';  
  }
  echo '</tbody>';
  echo '</table>';
?>
<script type="text/javascript">

$(document).ready(function() {
  var table = $("#skillsdutyutilisationtable-<?php echo $intDepartmentID?>-<?php echo $bst?>").DataTable({
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
  echo '<br>There are no Duties defined<br><br>';
  echo '</div>';


}
echo '<br><br>';
  
?>
