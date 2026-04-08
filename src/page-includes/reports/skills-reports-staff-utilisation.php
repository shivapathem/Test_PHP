<?php

session_start();
include_once '../../function-includes/init.php';
include_once '../../function-includes/reports-functions.php';
include_once '../../function-includes/genericfunctions.php';
$intTotalCount = 0;
$intDepartmentID = $_REQUEST['departmentid'];

// Count of staff
$intStaffCount = CountStaffInDepartmentWithSkill($intDepartmentID);


// Get the count of programmes for this base
$intProgCount = CountProgrammesInDepartment($intDepartmentID);
// Now get the skills for everyone in this base.....

$arrStaff = GetSkillsCountByDepartment($intDepartmentID);



if (isset($arrStaff)) {

  $strTable = '<table id="skillsstaffutilisationtable-'.$intDepartmentID.'" class="tablesmall compact stripe" width="100%">';
  $strTable.= '<thead>';
  $strTable.= '<tr>';
  $strTable.= '<th>';
  $strTable.= 'Name';
  $strTable.= '</th>';
  $strTable.= '<th>';
  $strTable.= 'Skills Count';
  $strTable.= '</th>';
  $strTable.= '<th>';
  $strTable.= 'Percentage';
  $strTable.= '</th>';
  $strTable.= '</tr>';
  $strTable.= '</thead>';
  $strTable.= '<tbody>';

  foreach ($arrStaff as $sn => $arrPerson) {
    if (isset($arrPerson[$intDepartmentID])) {
      $intSkillsCount = $arrPerson[$intDepartmentID];
      $skillspercentage  = number_format((($intSkillsCount / $intProgCount) * 100), 2);
    }
    else {
     $intSkillsCount = 0;
     $skillspercentage  = number_format(0, 2);
    }
    $arrStaff[$sn]['count'] = $intSkillsCount;
    $intTotalCount = $intTotalCount + $intSkillsCount;
    $strTable.= '<tr>';

    $strTable.= '<td>';
    $strTable.= $arrPerson['name'];
    $strTable.= '</td>';

    $strTable.= '<td>';
    $strTable.= $intSkillsCount;
    $strTable.= '</td>';

    $strTable.= '<td>';
    $strTable.= $skillspercentage.'%';
    $strTable.= '</td>';
    $strTable.= '</tr>';
  }
  $strTable.= '</tbody>';
  $strTable.= '</table>';

  $intAverageCount = round($intTotalCount / $intStaffCount);
  $average[0] = 0;
  $average[1] = 0;
  $average[2] = 0;
  foreach ($arrStaff as $sn => $arrPerson) {
    if (isset($arrPerson[$intDepartmentID])) {
      $intSkillsCount = $arrPerson[$intDepartmentID];
    }
    else {
      $intSkillsCount = 0;
    }
    if ($intSkillsCount > $intAverageCount) {
      $average[0] = $average[0] + 1;
    }
    elseif ($intSkillsCount == $intAverageCount) {
      $average[1] = $average[1] + 1;
    }
    else {
      $average[2] = $average[2] + 1;
    }
  }
  if ($intProgCount == 0) {
    $intPercentage = '0.00';
  }
  else {
    $intPercentage = number_format((($intAverageCount / $intProgCount) * 100), 2);
  }

  echo '<div class="tableheadersmall medtextbold" style="width:100%">';
  echo '<br>There are '.$intStaffCount.' people with at least one skill and '.$intProgCount.' skills in this Team.<br>';
  echo 'The average number of skills each person has is '.$intAverageCount.' ('.$intPercentage.'%)<br>';
  echo 'There are '.$average[0].' people above this average, ';
  echo $average[1].' matching this average ';
  echo 'and '.$average[2].' people below the average.<br><br>';
  echo '</div><br>';
  echo $strTable;
?>

<script type="text/javascript">

$(document).ready(function() {
  var table = $("#skillsstaffutilisationtable-<?php echo $intDepartmentID?>").DataTable({
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
  echo '<br>There are no Staff assigned to Programmes.<br><br>';
  echo '</div>';
}

echo '<br><br>';

