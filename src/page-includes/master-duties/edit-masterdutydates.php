<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';


$intDutyID = $_REQUEST["dutyid"];
$intDutyType = $_REQUEST["dutytypeid"];
$intStaffID = $_SESSION['user']['StaffID'];
$intAreaID = $_SESSION['user']['AreaID'];
$intIsRota = $_SESSION['isrota'];
if (!(isset($intIsRota))) {
  $intIsRota = 1;
  $_SESSION['isrota'] = 1;
}
$intType = 0;

//Check User Authentication
if ($intIsRota == 1) {
    $pageid = 6;
    $perms = $_SESSION['areaperms'];
    for($ArraySeq = 0; $ArraySeq < count($perms); $ArraySeq++){
        if($perms[$ArraySeq]["formid"] == $pageid){
            $canview = $perms[$ArraySeq]["isview"];
            $canmodify = 0;
            $canviewextended = $perms[$ArraySeq]["isviewextended"];
            $canmodifyextended = 0;
            $candelete = 0;
        }
    }
}
else {
    $pageid = 4;
    $perms = $_SESSION['areaperms'];
    for($ArraySeq = 0; $ArraySeq < count($perms); $ArraySeq++){
        if($perms[$ArraySeq]["formid"] == $pageid){
            $canview = $perms[$ArraySeq]["isview"];
            $canmodify = $perms[$ArraySeq]["ismodify"];;
            $canviewextended = $perms[$ArraySeq]["isviewextended"];
            $canmodifyextended = $perms[$ArraySeq]["ismodifyextended"];;
            $candelete = $perms[$ArraySeq]["isdelete"];;
        }
    }
}


//check if we show the form
if ($canview == 1) {
  if ($intDutyID > 0) {
    $rsDutyDatesJson = GetDutyDatesByID($intDutyID);
    $rsDutyDates = json_decode($rsDutyDatesJson,true);
    if (!empty($rsDutyDates)) {
      for($row = 0; $row < count($rsDutyDates); $row++) {
        $arrDates[$rsDutyDates[$row]['MasterDutyDateID']]['dutyid'] = $rsDutyDates[$row]['DutyID'];
        $arrDates[$rsDutyDates[$row]['MasterDutyDateID']]['startdate'] = $rsDutyDates[$row]['StartDate'];
        $arrDates[$rsDutyDates[$row]['MasterDutyDateID']]['enddate'] = $rsDutyDates[$row]['EndDate'];
      }
    }
    
    echo '<div style="width: 100%">';
    echo '<form id="neweditdutydates">';
    echo '<table id="dutydatesnewedittable" class="smalltable bluetable" width="50%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="2">Duty Dates...';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    if ($canmodify == 1) {
      echo '<img id="imgNewDutyDate" name="imgNewDutyDate" class="handcursor" border="0" src="images/add.png" width="18px" height="18px" onclick=\'javascript:NewDutyDates('.$intDutyID.','.$intDutyType.')\';> New Duty Date Range';
    }
    echo '</th>';
    echo '</tr>';
    echo '<tr>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th>Start Date</th>';
    echo '<th>End Date</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    
    if (isset($arrDates)) {
      foreach ($arrDates as $intDutyDateID => $arrDate) {
	if (isset($arrDate['dutyid'])) {
	  
	  $Date1 = $arrDate['startdate'];
	  $Date2 = $arrDate['enddate'];
	  
	  if ($Date1 == '') {
	    $Date1 = '01/01/1994';
	  }
	  if ($Date2 == '') {
	    $Date2 = '31/12/2030';
	  }

	  echo '<tr>';
	  
	  echo '<td>';
	  echo $Date1;
	  echo '</td>';
	  echo '<td>';
	  echo $Date2;
	  echo '</td>';
	  
	echo '</tr>';
	}
      }
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</form>';  
    echo '</div>';
  }
}

?>

<script type="text/javascript">

$(document).ready(function(){ 
  if (<?php echo empty($canview) ? 0:$canview ?> == 0) {
    customAlert('You do not have modify privileges for this form.');
  }
  else {
    if (<?php echo empty($canmodify)? 0:$canmodify ?> == 0) {
      $("#neweditdutydates :input").attr("disabled", true); 
    }
  }
});
  
  $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
   $(function() {
    $( "button" )
      .button()
  });
  
</script>

