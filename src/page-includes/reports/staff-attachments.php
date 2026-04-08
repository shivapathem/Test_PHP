<?php
include_once '../../function-includes/init.php';
include_once '../../function-includes/reports-functions.php';
include_once '../../function-includes/genericfunctions.php';

if (date("d") > 15) {
  $strDate = date("Y-m-01", strtotime("+2 months"));
}
else {
  $strDate = date("Y-m-01", strtotime("+1 month"));
}

$strNextMonth = date('Y-m-d', strtotime($strDate. ' +1 month'));
$strLastMonth = date('Y-m-d', strtotime($strDate. '-1 month'));

$intDepartmentID = $_REQUEST['department'];
$strDepartmentName = GetDepartmentNameFromID($intDepartmentID);
$arrAllowedAttachments = GetStaffAllowedOnAttachment($intDepartmentID);
//print_r($arrAllowedAttachments);
$arrAttachments = GetStaffOnAttachment($intDepartmentID, $strDate);
$arrTypes = array (1 => 'Internal', 2 => 'External');
//echo '<pre>';
//print_r($arrAttachments);

echo '<div class="tableheadersmall medtextboldcentre" width="100%">';
echo '<br>Staff on Attachment for '.$strDepartmentName.'<br><br>';
echo '</div>';

echo '<br><table class="tablesmall">';
foreach ($arrTypes as $iCount => $strTypeDesc) {

  echo '<tr>';
  echo '<td colspan="11" height="30px" class="tableheadersmall medtextboldcentre" >';  
  echo $strTypeDesc;
  echo '</td>';  
  echo '</tr>';

  $strLoopDate = $strDate;
  for ($i = 0; $i <11; $i++) {
    echo '<th width="150px">'.date("F Y", strtotime($strLoopDate)).'</th>';
    $strLoopDate = date("Y-m-d", strtotime("+1 month", strtotime($strLoopDate)));
  }
  echo '</tr>';
  echo '<tr>';
 
  $strLoopDate = $strDate;
  for ($i = 0; $i <11; $i++) {
  $intMonthCount[$i] = 0;
  $uLoopEndDate = strtotime("+ 1 Month", strtotime($strLoopDate));
    echo '<td valign="top" nowrap>';
    if (isset($arrAttachments[$iCount])) {
      foreach ($arrAttachments[$iCount] as $intUserLogon => $arrAttachment) {
        //echo $intUserLogon;
        if ($arrAttachment['StartDate'] <= $uLoopEndDate && $arrAttachment['EndDate'] > strtotime($strLoopDate)) {
          echo $arrAttachment['FullName'];
          if (date("Ym", $arrAttachment['StartDate']) == date("Ym", strtotime($strLoopDate))) {
            echo ' (Starts '.date("jS", $arrAttachment['StartDate']).')';
          }
          if (date("Ym", $arrAttachment['EndDate']) == date("Ym", strtotime($strLoopDate))) {
            echo ' (Ends '.date("jS", $arrAttachment['EndDate']).')';
          }
          echo '<br>';
          $intMonthCount[$i]++;
        }
      }
    }
    else {
      echo '-';
    
    }
    echo '</td>';
    $strLoopDate = date("Y-m-d", strtotime("+1 month", strtotime($strLoopDate)));      
  }
  echo '</tr>';
  
  if ($arrAllowedAttachments[$iCount] < 100) {
    echo '<tr>';
    for ($i = 0; $i <11; $i++) {
      if ($arrAllowedAttachments[$iCount] >= $intMonthCount[$i]) {
        $strClass = 'LightGreen';
      }
      else {
        $strClass = 'highlighted';
      }
      echo '<td class="'.$strClass.'">';
      echo $intMonthCount[$i].'/'.$arrAllowedAttachments[$iCount];
    }
 
  }
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="11" height="40px">';  
    echo '</td>';  
    echo '</tr>'; 


}
  echo '</table>';  


?>