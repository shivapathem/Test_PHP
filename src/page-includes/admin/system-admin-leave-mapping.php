<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../users/process/classUserSetup.php';

$setupObj = new classUserSetup();
$intSysAdmin =  $_SESSION['user']['SysAdmin'] ?? 0;
if (($intSysAdmin == 1)) {
$arrAllocLeaveTypes = GetLeaveAllocateTypes();

echo '<div class="tableheadersmall medtextboldcentre" style="position: relative; width:100%">'; 
echo '<br><h2>Leave View</h2><br>';
echo '</div>';
  echo '<table class="tablesmalltidy" id="simplefiltertable" width="100%">';
  echo '<thead>';
  echo '<tr height="30px">';
  echo '<th>';
  echo 'Description';
  echo '</th>';
  echo '<th width="300px">';
  echo 'Allocate DB Name';
  echo '</th>';
  echo '<th width="100px">';
  echo 'ScheduAll ID';
  echo '</th>';
  echo '<th width="100px">';
  echo 'Creditable<br>(In A7)';
  echo '</th>';
  echo '<th width="100px">';
  echo 'Show Zero Values';
  echo '</th>';
  echo '<th width="100px">';
  echo 'Include In Reports';
  echo '</th>';
  echo '<th width="100px">';
  echo 'Include In<br>Days to Take';
  echo '</th>';
  echo '<th width="100px">';
  echo 'Has Credits';
  echo '</th>';
  echo '<th width="100px">';
  echo 'Selective Hide<br>(By Scheduling Team)';
  echo '</th>';  
  echo '<th width="100px">';
  echo 'Order';
  echo '</th>';  
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  
if (isset($arrAllocLeaveTypes)) {   
  $intCounter = 0;
  $intLast = count($arrAllocLeaveTypes) - 1;
  
  foreach ($arrAllocLeaveTypes as $intLeaveTypeID => $arrAllocLeaveType) {
    echo '<tr ondblclick="javascript:EditLeaveMapping('.$intLeaveTypeID.');" class="handcursor">';
    echo '<td>';
    echo $arrAllocLeaveType['Description'];
    echo '</td>';
    echo '<td>';
    echo $arrAllocLeaveType['DisplayAllocName'];
    echo '</td>';
    echo '<td>';
    echo $arrAllocLeaveType['SchedID'];
    echo '</td>'; 
       
    echo '<td>';
    if ($arrAllocLeaveType['isCreditable'] == 1) {
      echo '<img  class="handcursor" border="0" src="../images/green_tick.png" width="12px" height="12px">';     
    }
    else {
      echo '<img  class="handcursor" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
    }
    echo '</td>';     

    echo '<td>';
    if ($arrAllocLeaveType['ShowZero'] == 1) {
      echo '<img  class="handcursor" border="0" src="../images/green_tick.png" width="12px" height="12px">';     
    }
    else {
      echo '<img  class="handcursor" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
    }
    echo '</td>'; 
        
    echo '<td>';
    if ($arrAllocLeaveType['IncludeInReports'] == 1) {
      echo '<img  class="handcursor" border="0" src="../images/green_tick.png" width="12px" height="12px">';     
    }
    else {
      echo '<img  class="handcursor" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
    }
    echo '</td>'; 
    
    echo '<td>';
    if ($arrAllocLeaveType['CalcInReports'] == 1) {
      echo '<img  class="handcursor" border="0" src="../images/green_tick.png" width="12px" height="12px">';     
    }
    else {
      echo '<img  class="handcursor" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
    }
    echo '</td>';

    echo '<td>';
    if ($arrAllocLeaveType['HasCredits'] == 1) {
      echo '<img  class="handcursor" border="0" src="../images/green_tick.png" width="12px" height="12px">';     
    }
    else {
      echo '<img  class="handcursor" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
    }
    echo '</td>';        

    echo '<td>';
    if ($arrAllocLeaveType['SelectiveHide'] == 1) {
      echo '<img  class="handcursor" border="0" src="../images/green_tick.png" width="12px" height="12px">';     
    }
    else {
      echo '<img  class="handcursor" border="0" src="../images/red_cross.png" width="12px" height="12px">';    
    }
    echo '</td>';  
    
    echo '<td align="center">';
    if ($intCounter == 0) {
      echo '<img border="0" src="../images/pixel.png" width="12px" height="12px">';    
    }
    else {
      echo '<img border="0" src="../images/hourpointer-up.png" width="12px" height="12px" onclick="javascript:MoveMappingPosition('.$intLeaveTypeID.', 0);">';   
    } 
    if ($intCounter == $intLast) {
      echo '<br><img border="0" src="../images/pixel.png" width="12px" height="12px">';       
    }
    else {
      echo '<br><img border="0" src="../images/hourpointer.png" width="12px" height="12px" onclick="javascript:MoveMappingPosition('.$intLeaveTypeID.', 1);">';    
    }
    
    echo '</td>';
   
    echo '</tr>';
    $intCounter++;
  }
}   
echo '</tbody>'; 
echo '</table>';   
} else {
  echo 'Access Denied'; die;
 }
?>