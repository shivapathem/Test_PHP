<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once '../../function-includes/allocationsfunctionsday.php';
include_once '../../function-includes/allocationsfunctionsfiltering.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intTeamID = $_REQUEST['teamId'];
$roleIDPermission = $_REQUEST['roleIDPermission'];
$startendshiftflag = $_REQUEST['startendshiftflag'];
$filterQuery1 = $_REQUEST['filterQuery1'];
$filterQuery2 = $_REQUEST['filterQuery2'];
$filterQuery3 = $_REQUEST['filterQuery3'];
$filterOrderStr = $_REQUEST['filterOrderStr'];
$skillFilterDaily ='';
if (isset($_REQUEST['skillFilterDaily']) && $_REQUEST['skillFilterDaily'] != '') {
  $skillFilterDaily = $_REQUEST['skillFilterDaily'];
}

$dutyFilterDaily ='';
if (isset($_REQUEST['dutyFilterDaily']) && $_REQUEST['dutyFilterDaily'] != '') {
  $dutyFilterDaily = $_REQUEST['dutyFilterDaily'];
}

$jobFilterDaily ='';
if (isset($_REQUEST['jobFilterDaily']) &&  $_REQUEST['jobFilterDaily'] != '') {
  $jobFilterDaily = $_REQUEST['jobFilterDaily'];
}

$jobNameAll ='';
if (isset($_REQUEST['jobNameAll']) &&  $_REQUEST['jobNameAll'] != '') {
  $jobNameAll = $_REQUEST['jobNameAll'];
}

$jobLabelAll ='';
if (isset($_REQUEST['jobLabelAll']) &&  $_REQUEST['jobLabelAll'] != '') {
  $jobLabelAll = $_REQUEST['jobLabelAll'];
}

$selectedDay  = $_REQUEST['selectedDay'];
$arrStaffOptions = GetCurrentFilterData ($strUser,$intTeamID);
$arrFilters = GetDutyFiltersByDepartment($intTeamID);
$filterQuery1 = str_replace("-", "'", $filterQuery1);
$strCurrentDate = date("Y-m-d", strtotime($_REQUEST['date']));
if ($selectedDay > 1) {
    $daysadded = $selectedDay-1;
    $day7date  = date("Y-m-d", strtotime("+$daysadded day", (strtotime($strCurrentDate))));
  } else {
    $day7date = date("Y-m-d",strtotime($strCurrentDate));
  }
$getWeekandDayArr = GetAllocationWeekandDay($strCurrentDate);
$intWeek = $getWeekandDayArr['ixYearWeek'];
$intDay = $getWeekandDayArr['ixDayInWeek'];
if(!isset($intSortOrder)){
  $intSortOrder = 0;
}
$arrAllocations = ReadAllocationsDay($intWeek, $intDay,$intTeamID, $intSortOrder, $roleIDPermission,$filterQuery1,$filterQuery2,$filterQuery3,$filterOrderStr,$strCurrentDate,$day7date,$skillFilterDaily,$dutyFilterDaily,$jobFilterDaily,$jobNameAll,$jobLabelAll);
$arrAllocations = json_decode($arrAllocations,true);
if (isset($arrStaffOptions['CurrentDailyFilter']) && strpos($arrStaffOptions['CurrentDailyFilter'], ',')) {
  $arrCurrentFilter = explode(",", $arrStaffOptions['CurrentDailyFilter']);
  if ($arrCurrentFilter[0] != -1) {
    $arrCurrentFilter = explode(",", $arrStaffOptions['CurrentDailyFilter']);
    // Apply the filter....
    // if $arrCurrentFilter[0]  = 0 it's a preset and we need the $arrFilters
    // if $arrCurrentFilter[0]  = 1 it's a skill and we DO NOT need the $arrFilters" 
    $arrAllocations = ApplyFilterDaily ($arrAllocations, $arrCurrentFilter, $arrFilters, $intTeamID);
  }
}
$strAddresses = '';
$arrduties = array("U", "U-Sick", "Leave", "Sick","OFF Leave","Absent");
$arrduties = array_map( 'strtolower', $arrduties );
foreach($arrAllocations['assigned'] as $intAllocationID => $arrAllocation) {
  if(isset($arrAllocation)) {
    foreach ($arrAllocation as $scid => $allocValue) {
      if($startendshiftflag==1){
        if($allocValue['miscduty']!=1){
          if (!in_array(strtolower(trim($allocValue['duty'])), $arrduties)) {
           $resultBBC = ($allocValue['StaffBBCEmail'] != '') ? $allocValue['StaffBBCEmail'].';' : '';
           $resultNONBBC = ($allocValue['StaffNONBBCEmail'] != '') ? $allocValue['StaffNONBBCEmail'].';' : '';
           $strAddresses.=  $resultBBC.$resultNONBBC;
          }
        }
      }else{
        if (!in_array(strtolower(trim($allocValue['duty'])), $arrduties)) {
            $resultBBC = ($allocValue['StaffBBCEmail'] != '') ? $allocValue['StaffBBCEmail'].';' : '';
            $resultNONBBC = ($allocValue['StaffNONBBCEmail'] != '') ? $allocValue['StaffNONBBCEmail'].';' : '';
            $strAddresses.=  $resultBBC.$resultNONBBC;
        }
      }
    } 
  }
}  

echo '<table class="tablesmall" width="600">';
echo '<tr>';
echo '<th colspan="2"><br>Here\'s a list of people working on this day<br>Right-Click and choose \'copy\' or click on \'Create email\' to create a new Outlook email.<br><br></th>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2">';
echo '<textarea rows="5" id="linktext" cols="80">';
echo $strAddresses;
echo '</textarea>';    
echo '</td>';
echo '</tr>';
echo '<tr>';

echo '<td width="100px">';
echo '<a style="text-decoration:none" href="mailto:'.$strAddresses.'?subject=Allocations for '.date("l jS F Y", strtotime($strCurrentDate)).'">';
echo '<input  type="button" value="Create email">';
echo '</a>';
echo '</td>';

echo '<td>';
echo '<input type="button" value="Close" onclick="cancel()">';
echo'</td>';
echo '</tr>';
echo '</table>';
?>

<script type="text/javascript">
$(document).ready(function () {
        document.getElementById('linktext').focus();
        document.getElementById('linktext').select();
})
</script>