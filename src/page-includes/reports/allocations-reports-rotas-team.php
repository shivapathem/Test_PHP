<?php

header('Content-Type: text/html; charset=utf-8');
session_start();
date_default_timezone_set('UTC');
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intDepartmentID = $_REQUEST["departmentid"];
$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);

$arrRotas = ReadRotaPatternDepartment($intDepartmentID);  
$intTotalCost = 0;
if (isset($arrRotas)) {
  // Get the total cost
  foreach ($arrRotas as $strStaffNumber => $arrRota) {
    if (isset($arrRota['HoursInRota'])) { 
      $intHoursInRota = $arrRota['HoursInRota'];      
      $intExpectedHours = round($arrRota['WeeksInRota'] * 35 * $arrRota['EFT']);
      $intWeeksInRota = $arrRota['WeeksInRota']; 
      if ($intWeeksInRota != 0) {
        $intCost = (($intExpectedHours - $intHoursInRota) / $intWeeksInRota) * 52 * 25;
      } 
      else {
        $intCost = 0;
      }
      if ($intCost > 0) {
        $intTotalCost = $intTotalCost + abs($intCost);
      }
    }
  }
}

  echo '<table class="tablesmall" width="100%">';
  // ######################################################### The Month across the top ######################################################### 
  echo '<tr height="50px">'; 
  echo '<td width="50%" class="tableheadersmall medtextbold">';
  echo 'Underlying Rota Pattern for '.$strDepartmentName;
  echo '<br>Lines highlighted in blue show a discrepancy between the EFT of the individual and the EFT of the Pattern.';  
  echo '</td>';
  echo '<td class="tableheadersmall medtextbold">';
  echo 'The EFT Loss is <span id="tabletotal1"></span>';
  echo '</td>';
  echo '<td class="tableheadersmall medtextbold">';
  echo 'The total rota pattern scheduling loss for<br>this department is &pound;<span id="tabletotal">'.number_format($intTotalCost).'</span>';
  echo '</td>';
  echo '<td class="tableheadersmall medtextbold">';
  echo 'In the rota patterns below there are <span id="shifttotal"></span> shifts<br>of which <span id="nighttotal"></span> are night shifts.<br>';
  echo 'This means <span id="nightpercenttotal"></span>% of the shifts within the rota patterns are night shifts.';  
  echo '</td>';
  echo '</tr>';   
  echo '</table>';
  echo '<br>';

  if (isset($arrRotas)) {
    echo '<table class="tablesmall compact stripe" id="rotasteams">';   
    echo '<thead>'; 
    echo '<tr>';       
    echo '<th>Name</th>';   
    echo '<th>Sort Code</th>';
    echo '<th>Contract</th>';
    echo '<th>EFT</th>';
    echo '<th>Pattern EFT</th>';
    echo '<th>Appearances</th>';
    echo '<th>Night Shifts</th>';
    echo '<th>Night Percentage</th>';
    echo '<th>Weeks in Pattern</th>';
    echo '<th>Total Hours</th>';
    echo '<th>Expected Hours</th>';
    echo '<th>Cost (&pound;)</th>';
    echo '</tr>';
    echo '</thead>'; 
    
    echo '<tbody>'; 
    foreach ($arrRotas as $strStaffNumber => $arrRota) {
    $arrRota['EFT'] = $arrRota['EFT'] ?? 0;
		$intEft = number_format($arrRota['EFT'], 3, '.', '');
      if (isset($arrRota['HoursInRota'])) { 
        $intHoursInRota = number_format($arrRota['HoursInRota'], 2, '.', '');     
        $intExpectedHours = number_format($arrRota['WeeksInRota'] * 35 * $arrRota['EFT'], 2, '.', '');
        $intEFTPattern = number_format($arrRota['HoursInRota'] / ($arrRota['WeeksInRota'] * 35), 3, '.', ''); 
        $intWeeksInRota = $arrRota['WeeksInRota']; 
      }
      else {
        $intHoursInRota = 0;
        $intEFTPattern = 0;
        $intExpectedHours = 0;
        $intWeeksInRota = 0;
      }
      if (isset($arrRota['MiscDaysInRota'])) {     
        $intMiscDaysInRota = $arrRota['MiscDaysInRota'];
      }
      else {
        $intMiscDaysInRota = 0;
      }
      if (isset($arrRota['DaysInRota'])) {     
        $intDaysInRota = $arrRota['DaysInRota'];
      }
      else {
        $intDaysInRota = 0;
      } 
      if (isset($arrRota['NightsInRota'])) {     
        $intNightsInRota = isset($arrRota['NightsInRota']) ? $arrRota['NightsInRota'] : 0;
      }
      else {
        $intNightsInRota = 0;
      }          
      if ($intEFTPattern == $intEft) {
        echo '<tr class="handcursor" onclick="javascript:ShowRotasIndividual('.$intDepartmentID.', \''.$strStaffNumber.'\', \''.$arrRota['ScheduledPersonID'].'\')">';
      }
      else {
        echo '<tr class="handcursor selected" onclick="javascript:ShowRotasIndividual('.$intDepartmentID.', \''.$strStaffNumber.'\', \''.$arrRota['ScheduledPersonID'].'\')">';      
      }
      
      $totval=$intDaysInRota + $intNightsInRota;
      echo '<td>'.$arrRota['FullName'].'</td>';
      echo '<td>'.$arrRota['SortCode'].'</td>';
      echo '<td>'.$arrRota['ContractType'].'</td>';
      echo '<td align="center">'.$intEft.'</td>'; 
      echo '<td align="center">';
      echo $intEFTPattern;      
      echo '</td>'; 
      
      echo '<td  align="center">'.($intDaysInRota + $intNightsInRota).'</td>';
      echo '<td  align="center">'.$intNightsInRota.'</td>';
      echo '<td  align="center">';
      if ($totval != 0)  {
      echo number_format(($intNightsInRota * 100) / ($totval), 1);
      }
      echo '</td>';      
      
                  
      echo '<td  align="center">'.$intWeeksInRota.'</td>';           
      echo '<td  align="center">'.$intHoursInRota.'</td>'; 
      echo '<td  align="center">'.$intExpectedHours.'</td>';      
      // Get the Cost
      if ($intWeeksInRota != 0) {
        $intCost = (($intExpectedHours - $intHoursInRota) / $intWeeksInRota) * 52 * 25;
      } 
      else {
        $intCost = 0;
      }
      
      echo '<td>';
      if ($intCost > 0) {   
        //echo '&pound;'.number_format($intCost, 2);
        echo number_format($intCost, 0); 
      }
      echo '</td>'; 
      
      
                   
      echo '</tr>';
    } 
    echo '</tbody>';     
    echo '</table>';


  

?>

<script type="text/javascript">
 $(document).ready(function() {
  var table = $("#rotasteams").DataTable({
    paging: false,
    scrollY: 400,
    scrollCollapse: true,
    info:     false,
    stateSave: true,
    deferRender: true,
    order: [[ 2, "desc" ]],
    
    "footerCallback": function ( row, data, start, end, display ) {
      var api = this.api(), data;
      // Remove the formatting to get integer data for summation
      var intVal = function ( i ) {
       return typeof i === 'string' ?
         i.replace(/[\$,]/g, '')*1 :
         typeof i === 'number' ?
         i : 0;
       };
       // Total over this page
       costTotal = api
         .column( 11, { page: 'current'} )
         .data()
         .reduce( function (a, b) {
         return intVal(a) + intVal(b);
       }, 0 );
       // Total shifts over this page
       shiftTotal = api
         .column( 5, { page: 'current'} )
         .data()
         .reduce( function (a, b) {
         return intVal(a) + intVal(b);
       }, 0 );
       // Total night shifts over this page
       nightsTotal = api
         .column( 6, { page: 'current'} )
         .data()
         .reduce( function (a, b) {
         return intVal(a) + intVal(b);
       }, 0 );       
        
       $('#tabletotal').html($.number(costTotal, 0)); 
       $('#tabletotal1').html($.number(costTotal / 25 /1456 , 2)); 
       
       $('#shifttotal').html($.number(shiftTotal, 0)); 
       $('#nighttotal').html($.number(nightsTotal, 0));
       $('#nightpercenttotal').html($.number(nightsTotal * 100 / shiftTotal, 1));
       
       
        
  }
  }); 
  yadcf.init(table, [
    {column_number: 0,
      filter_type: 'text'
    },
    {column_number: 3,
      filter_type: 'text'
    },        

  ]);
  if ( $.cookie("#rotasteams") !== null ) {
    scrollPos = $.cookie("#rotasteams");
    $("#rotasteams").closest('.dataTables_scrollBody').scrollTop(scrollPos);      
  };  
})  

$("#rotasteams").closest('.dataTables_scrollBody').on('scroll', function() { 
  var currpos = $("#rotasteams").closest('.dataTables_scrollBody').scrollTop();
  $.cookie("#rotasteams", currpos);
});



</script>

<?php
}
else {
  echo '<br>';
  echo '<div class="tableheadersmall bigtextboldcentre" style="width:100%">';
  echo '<br><br>';
  echo 'You are attempting to view the Underlying Rota Patterns for Department that has not yet either cretaed any or has not published them.<br><br>';
  echo '<br></br><br>';
  echo '</div>';
}