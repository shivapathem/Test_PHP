<?php
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/reports-functions.php';
include_once '../../function-includes/genericfunctions.php';

$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intDepartmentID = $_REQUEST["departmentid"];
if (isset($_REQUEST['debug'])) {
  $intDebug = $_REQUEST['debug'];
}
else {
  $intDebug = 0;
}
if (isset($_REQUEST['date'])) {
  $strStartDate = $_REQUEST['date'];
}
else {
  if (isset($_SESSION['Reports']['StartDate'])) {
    $strStartDate = $_SESSION['Reports']['StartDate'];
  }
  else {
    $strStartDate = date("Y-m-d");  
  }
}
$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);


$_SESSION['Reports']['StartDate'] = $strStartDate;                                          
$currentweek = bbcweeknumber(date("Y-m-d"));
$strStartDate = date('Y-m-01', strtotime($strStartDate));
$currentmonth = date('Y-m-d', strtotime($strStartDate));

$nextmonth = date('Y-m-d', strtotime($strStartDate. ' +1 month'));
$lasttmonth = date('Y-m-d', strtotime($strStartDate. '-1 month'));
$strEndDate = date('Y-m-d', strtotime($nextmonth. '+4 days'));
$tdate = $strStartDate;
while (strtotime($tdate) < strtotime($strEndDate)) {
  $arrweeks[] = bbcweeknumber($tdate);
  $tdate = date ("Y-m-d", strtotime("+1 week", strtotime($tdate)));
}
if (isset($_REQUEST['SortCodeFilter'])) {
  $strSortcodeFilter = $_REQUEST['SortCodeFilter'];
}
else {
  if (isset($_SESSION["reports"]["sortcode$intDepartmentID"])) {
    // Is the session set?
    $strSortcodeFilter = $_SESSION["reports"]["sortcode$intDepartmentID"];    
  }
  else {
    $strSortcodeFilter = '';  
  }
}
$_SESSION["reports"]["sortcode$intDepartmentID"] = $strSortcodeFilter;
$arrSortCodeMapping = GetSortCodeMappingForReport($intDepartmentID);

$arrAllocationsSum = GetAllocationsForExtras ($intDepartmentID, $arrweeks[0], $arrweeks[count($arrweeks) - 1], $strSortcodeFilter);

echo '<table class="tablesmall" width="100%">';
// ######################################################### The Month across the top ######################################################### 

  echo '<tr height="40px">';
  echo '<th colspan="11" style="text-align:center">'; 
  if (isset($arrSortCodeMapping[$strSortcodeFilter])) {
    $strFilter = $arrSortCodeMapping[$strSortcodeFilter];
  }
  else {
    $strFilter = 'All';
  }
  
  
  echo $strDepartmentName; 
  echo '</th>';
  echo '</tr>';
  echo '<tr>';
  echo '<td colspan="11">';
  echo '<table class="tablesmall" width="100%">';
  echo '<tr>';  
  echo '<th width="20%" class="handcursor" onclick="javascript:ShowAllocReportMonthly('.$intDepartmentID.',\''.$lasttmonth.'\')";>';
  echo '<br>&lt;&lt;&nbsp'.date('F', strtotime($lasttmonth)).'<br><br>';
  echo '</td>';
  echo '<th style="text-align:center">';
  echo date('F', strtotime($strStartDate));
  echo '<br>';
  echo date('Y', strtotime($strStartDate));
  echo '</td>';
  echo '<th width="20%" class="handcursor" style="text-align:right" onclick="javascript:ShowAllocReportMonthly('.$intDepartmentID.',\''.$nextmonth.'\')";>';
  echo date('F', strtotime($nextmonth)).'&nbsp;&gt;&gt;';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</td>'; 

echo '</tr>';   
  // ######################################################### END The Month across the top #########################################################  
    
echo '<tr>'; 
echo '<th width="150px"></th>';
echo '<th width="150px">EFT</th>';
echo '<th width="150px">Description</th>';
echo '<th width="150px">Totals</th>';
echo '<th width="150px">Saturday</th>';
echo '<th width="150px">Sunday</th>';
echo '<th width="150px">Monday</th>';
echo '<th width="150px">Tuesday</th>';
echo '<th width="150px">Wednesday</th>';
echo '<th width="150px">Thursday</th>';
echo '<th width="150px">Friday</th>';

echo '</tr>';
// Loop through eac week we are expecing
foreach ($arrweeks as $id => $strCurrWeek) {
  echo '<tr>';
  echo '<td class="tableheadersmall box90x90 handcursor" align="center" onclick=\'javascript:ShowAllocations('.$intDepartmentID.',"'.$strCurrWeek.'")\'><b>';        
  echo 'Week<br>'.spinweek($strCurrWeek);
  echo '</b></td>';
  echo '<td valign="top">'; 
     
  echo '<table width="100%" class="tablesmallnoborder">';
  echo '<tr>';
  echo '<td colspan="2" class="tableheader">';
  echo '&nbsp;';            
  echo '</td>';
  echo '</tr>';
  if (isset($arrAllocationsSum[$strCurrWeek])) {       
    // Core Work
    echo '<tr>';  
    echo '<td>';    
    echo 'Core Work';
    echo '</td>';
    echo '<td align="right">';  
    $intTotalCore = round(($arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] * 52) / 1456);
    echo $intTotalCore.'&nbsp;&nbsp;';  
    echo '</tr>';
    echo '<tr>';  
    echo '<td colspan="2">';
    echo '&nbsp;';  
    echo '</td>';  
    echo '</tr>';

    // Training work  
    echo '<tr>';  
    echo '<td>';    
    echo 'Training';
    echo '</td>';
    echo '<td align="right">';
    $intTotalTraining =  round(($arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration'] * 52) / 1456);  
    echo $intTotalTraining.'&nbsp;';                                                                                                                 
    echo '&nbsp;';  
    echo '</td>';  
    echo '</tr>';
    echo '<tr>';  
    echo '<td colspan="2">';
    echo '</tr>';
    
    // Adhoc work  
    echo '<tr>';  
    echo '<td>';    
    echo 'Adhoc Work';
    echo '</td>';
    echo '<td align="right">';
    $intTotalAdHOC =  round(((($arrAllocationsSum[$strCurrWeek]['Totals']['WorkingDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration']) -$arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration']) * 52) / 1456);  
    echo $intTotalAdHOC.'&nbsp;&nbsp;';    
    echo '</tr>';
    echo '<tr>';  
    echo '<td colspan="2">';
    echo '&nbsp;';  
    echo '</td>';  
    echo '</tr>';
    
    echo '<tr>';  
    echo '<td>';
    echo '<b>Total Work</b>';  
    echo '</td>'; 
    echo '<td align="right">';
    echo '<b>'.($intTotalCore + $intTotalAdHOC).'</b>&nbsp;&nbsp;';  
    echo '</td>';  
    echo '</tr>';    
  }
  echo '</table>';
  echo '</td>';    
  echo '<td nowrap valign="top">';
  echo '<table width="100%" class="tablesmallnoborder">';
  echo '<tr>';
  echo '<td colspan="2" class="tableheader">';
  echo '&nbsp;';            
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>';
  echo 'Core Work Shifts';       
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>';
  echo 'Core Work Duration';       
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>';
  echo 'Training';       
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>';
  echo 'Training Duration';       
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>';
  echo 'Adhoc Work Shifts';       
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>';
  echo 'Adhoc Work Duration';       
  echo '</td>';
  echo '</tr>';                  
  echo '</table>';
  echo '</td>';
    
  echo '<td valign="top">';
 
  echo '<table width="100%" class="tablesmallnoborder">';
  echo '<tr>';
  echo '<td class="tableheader">';
  echo 'Totals';            
  echo '</td>';
  echo '</tr>';

  if (isset($arrAllocationsSum[$strCurrWeek])) {   
    echo '<tr>';
    echo '<td>';            
    echo $arrAllocationsSum[$strCurrWeek]['Totals']['MasterCount'];
    echo '</td>';
    echo '</tr>';  
  
    echo '<tr>';
    echo '<td>';       
	$arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] = $arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] ? $arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] : 0;
    echo number_format(($arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] / 3600), 2, '.', '');
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>';            
    echo $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingCount'];
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>';   
	$arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration'] = $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration'] ? $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration'] : 0;
    echo number_format(($arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration'] / 3600), 2, '.', '');
    echo '</td>';
    echo '</tr>';
     
    echo '<tr>';
    echo '<td>';            
    echo $arrAllocationsSum[$strCurrWeek]['Totals']['WorkingCount'] - $arrAllocationsSum[$strCurrWeek]['Totals']['MasterCount'] - $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingCount'];
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>';            
    echo number_format((($arrAllocationsSum[$strCurrWeek]['Totals']['WorkingDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['MasterDuration'] - $arrAllocationsSum[$strCurrWeek]['Totals']['TrainingDuration']) / 3600), 2, '.', '');
    echo '</td>';
    echo '</tr>';
       
    
  }

  echo '</table>';    
  echo '</td>';   

  for ($i=0; $i <= 6; $i++) {
    $strCurrDate = date("Y-m-d", strtotime(datefromweek($strCurrWeek, $i)));
    echo '<td valign="top">';
    echo '<table width="100%" class="tablesmallnoborder">';
    echo '<tr>';
    echo '<td class="tableheader">';            
    echo date("d", strtotime($strCurrDate));
    echo '</td>';
    echo '</tr>';
    if (isset($arrAllocationsSum[$strCurrWeek]['Days'][$i])) {
      echo '<tr>';
      echo '<td>';    
      echo $arrAllocationsSum[$strCurrWeek]['Days'][$i]['MasterCount'];  
      echo '</td>';            
      echo '</tr>';      
      echo '<td>';            
      echo number_format(($arrAllocationsSum[$strCurrWeek]['Days'][$i]['MasterDuration'] / 3600), 2, '.', '').' Hours';  
      echo '</td>';      
      echo '</tr>';
      
      echo '<tr>';
      echo '<td>';    
      echo $arrAllocationsSum[$strCurrWeek]['Days'][$i]['TrainingCount'];  
      echo '</td>';            
      echo '</tr>';      
      echo '<td>';            
      echo number_format(($arrAllocationsSum[$strCurrWeek]['Days'][$i]['TrainingDuration'] / 3600), 2, '.', '').' Hours';  
      echo '</td>';      
      echo '</tr>';      
      
      
      
      echo '<tr>';
      echo '<td>';            
      echo $arrAllocationsSum[$strCurrWeek]['Days'][$i]['WorkingCount'] - $arrAllocationsSum[$strCurrWeek]['Days'][$i]['MasterCount'] - $arrAllocationsSum[$strCurrWeek]['Days'][$i]['TrainingCount'];  
      echo '</td>';
      echo '<tr>';
      echo '<td>';            
      echo number_format((($arrAllocationsSum[$strCurrWeek]['Days'][$i]['WorkingDuration'] - $arrAllocationsSum[$strCurrWeek]['Days'][$i]['MasterDuration'] - $arrAllocationsSum[$strCurrWeek]['Days'][$i]['TrainingDuration']) / 3600), 2, '.', '').' Hours'; 
      echo '</td>';   
  
      echo '</tr>'; 
    }
    echo '</table>';
    
    echo '</td>';        
  }
  echo '</tr>';
}
echo '</table>';
  
  
    




?>
<script type="text/javascript">
$(document).ready(function() {

})

</script>