<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsstaffing.php';

$intDepartmentID = $_REQUEST['departmentid'];
$intPageID = $_REQUEST["pageid"];
// pageid = 0 ................ Show live period
// pageid = 1 ................ Show historival period

$arrDepartments = GetReportsConfig(0);
if ($intDepartmentID == 0) {
  $strDepartmentName = 'All Departments';
}
else {
  $strDepartmentName = $arrDepartments[$intDepartmentID]['DepartmentName'];
}

// ########################################## A few settings that affect page layout ##########################################
if (isset($_SESSION['screenwidth'])) {
  $intScreenWidth = $_SESSION['screenwidth'];
}
else {
  $intScreenWidth = 1600;
}

$intDateHeight = 35;
$intStatusHeight= 10;
$intRowHeight = 45;
$intStaffRowHeight = 35;
$intNamesRowHeight = 20;
$intNamesMinRowHeight = 30;
$intNamesWidth = 270;
$intDutyWidth = round($intScreenWidth - $intNamesWidth - 80) / 7;
$intTableWidth = round($intNamesWidth + ($intDutyWidth * 7));
$intShowAll = 1;

// ########################################## END ########################################################################

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];

// #################################################### Work Out the week we are to view #################################

if ($intPageID == 0) {
  $intWeekNumber = bbcweeknumber(date("Y-m-d"));  
  $intEndWeekNumber = addweeks($intWeekNumber, 4);
}
else {
  $intEndWeekNumber = addweeks(bbcweeknumber(date("Y-m-d")), -1);  
  $intWeekNumber = addweeks($intEndWeekNumber, -52);
}

$intCountWeekNumber = $intWeekNumber;
while ($intCountWeekNumber <= $intEndWeekNumber) {
  $arrWeeks[$intCountWeekNumber] = spinweek($intCountWeekNumber);
  $intCountWeekNumber = addweeks($intCountWeekNumber, 1);
}

$arrStaffBreaks = array();
$arrAllocations = ReadAllocationsStaffingSummary($intWeekNumber, $intEndWeekNumber, $arrDepartments, $arrStaffBreaks, $intDepartmentID);

$arrFreelancers = ReadAllocationsFreelancersSummary($intWeekNumber, $intEndWeekNumber, $arrDepartments, $arrStaffBreaks, $intDepartmentID);

  $intTotalStaffCost = $arrAllocations['TotalHours'] * $intStaffCostPerHour;
  $intProjectedStaffCost = ($intTotalStaffCost / count($arrWeeks)) * 52;
  $intTotalFreelanceCost = $arrFreelancers['TotalHours'] * $intFreelanceCostPerHour;
  $intProjectedFreelanceCost = ($intTotalFreelanceCost / count($arrWeeks)) * 52;

  echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
  echo '<br>';
  echo '<font size=4"> Projected ';
  if ($intDepartmentID == 0) {
    echo 'Total ';
  }   
  echo 'Yearly Cost &pound'.number_format($intProjectedStaffCost + $intProjectedFreelanceCost, 0, '.', ',').'</font>';
  echo '<br><br>Total Cost for this '.count($arrWeeks).' Week period &pound'.number_format($intTotalStaffCost + $intTotalFreelanceCost, 0, '.', ',');

  echo '<br><br>';
  echo '</div>';
  echo '<br>';
  echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
  echo '<br>Staff Available (Cost Per Hour &pound;'.$intStaffCostPerHour.')<br><br>';
  echo '</div>';
  
  echo '<table class="tablesmall compact stripe" id="useagesummary-'.$intDepartmentID.'" min-width="100%">';   
  echo '<thead>'; 
  echo '<tr>';       
  echo '<th>Week</th>';
  echo '<th>Cost</th>';
  echo '<th>Total Hours</th>';    
  echo '<th>Saturday</th>';
  echo '<th>Sunday</th>';
  echo '<th>Monday</th>';
  echo '<th>Tuesday</th>';  
  echo '<th>Wednesday</th>';
  echo '<th>Thursday</th>';
  echo '<th>Friday</th>';
  echo '</tr>';
  echo '</thead>'; 
    
  echo '<tbody>';
  
  foreach ($arrWeeks as $intCurrWeek => $strSpinnedWeek) {
    if (isset($arrAllocations['Totals'][$intCurrWeek])) {
      $intWeekTotal = $arrAllocations['Totals'][$intCurrWeek];
    }
    else {
      $intWeekTotal = 0;
    }
    echo '<tr>';
    echo '<td>';
    echo '<span style="display:none">'.$intCurrWeek.'</span>'.$strSpinnedWeek;
    echo '</td>';  
    echo '<td>';
    echo '&pound;'.number_format($intWeekTotal * $intStaffCostPerHour);
    echo '</td>';
    echo '<td>';
    echo $intWeekTotal;
    echo '</td>';        
    for ($i=0; $i<=6; $i++) {
      echo '<td>';
      if (isset($arrAllocations['Duties'][$intCurrWeek][$i])) {
        echo $arrAllocations['Duties'][$intCurrWeek][$i];
      }
      echo '</td>';
    }  
    echo '</tr>';   
  }
  echo '</tbody>';
  echo '</table>';
  echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
  echo '<br>Projected Yearly Cost &pound;'.number_format($intProjectedStaffCost, 0, '.', ',').'<br>'; 
  echo 'Cost this period &pound;'.number_format($intTotalStaffCost, 0, '.', ',').'<br><br>';
 
  echo '</div><br>';  
// Do the Freelancers 
  echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';
  echo '<br>Freelancers (Cost Per Hour &pound;'.$intFreelanceCostPerHour.')<br><br>';
  echo '</div>';
  echo '<table class="tablesmall compact stripe" id="useagesummaryfl-'.$intDepartmentID.'" min-width="100%">';   
  echo '<thead>'; 
  echo '<tr>';       
  echo '<th>Week</th>';
  echo '<th>Cost</th>';  
  echo '<th>Total Hours</th>';  
  echo '<th>Saturday</th>';
  echo '<th>Sunday</th>';
  echo '<th>Monday</th>';
  echo '<th>Tuesday</th>';  
  echo '<th>Wednesday</th>';
  echo '<th>Thursday</th>';
  echo '<th>Friday</th>';
  echo '</tr>';
  echo '</thead>'; 
    
  echo '<tbody>';
  
  foreach ($arrWeeks as $intCurrWeek => $strSpinnedWeek) {
    if (isset($arrFreelancers['Totals'][$intCurrWeek])) {
      $intWeekTotal = $arrFreelancers['Totals'][$intCurrWeek];
    }
    else {
      $intWeekTotal = 0;
    }
    echo '<tr>';       
    echo '<td>';
    echo '<span style="display:none">'.$intCurrWeek.'</span>'.$strSpinnedWeek;
    echo '</td>';
    echo '<td>';
    echo '&pound;'.number_format($intWeekTotal * $intFreelanceCostPerHour);
    echo '</td>';    
    echo '<td>';
    echo $intWeekTotal;
    echo '</td>';
    for ($i=0; $i<=6; $i++) {
      echo '<td>';
      if (isset($arrFreelancers['Duties'][$intCurrWeek][$i])) {
        echo $arrFreelancers['Duties'][$intCurrWeek][$i];
      }
      echo '</td>';
    } 
    echo '</tr>';   
  }
  echo '</tbody>';
  echo '</table>';  
  echo '<div class="tableheadersmall medtextboldcentre" style="width:100%">';

  echo '<br>Projected Yearly Cost &pound;'.number_format($intProjectedFreelanceCost, 0, '.', ',').'<br>';   
  echo 'Cost this period &pound;'.number_format($intTotalFreelanceCost, 0, '.', ',').'<br><br>';
  
 
  echo '</div><br>';   

?>
<script type="text/javascript">
 $(document).ready(function() {
  var table = $("#useagesummary-<?php echo $intDepartmentID?>").DataTable({
    paging: false,
    scrollY: 150,
    scrollCollapse: true,
    info:     false,
    deferRender: true,
    order: [[0, 'desc']],
  });
  var table = $("#useagesummaryfl-<?php echo $intDepartmentID?>").DataTable({
    paging: false,
    scrollY: 150,
    scrollCollapse: true,
    info:     false,
    deferRender: true,
    order: [[0, 'desc']],
  });  
})
</script>
