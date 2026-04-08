<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); 
}

include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/requestfunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/function-includes/common/classCommonDBFunctions.php';
$intSysAdmin =$isDivisionalAdmin= 0;
$commonObj = new classCommonDBFunctions();
$strUser = isset($_SESSION['user']['user']) && !empty($_SESSION['user']['user']) ?  $_SESSION['user']['user'] : $_COOKIE['editWeeklyUserNetLogin'];
$intGroupID = $_REQUEST["requestgroup"];
$userID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];

if($userID>0){
  $intSysAdmin= $commonObj->UserIsSysAdmin($userID) ?? 0;
  $isDivisionalAdmin = $commonObj->UserIsDivAdmin($userID);
  }
if (($intSysAdmin == 1) || ($isDivisionalAdmin ==1)) {
  $intAdminLevel = 2;
}
else {
  $intAdminLevel = GetAdminLeaveRequestGroupsAdminFromLogin ($strUser, $intGroupID);
}

$date = date("Y-m-d");

$strLeaveGroupDesc = GetLeaveRequestDescFromID ($intGroupID);

$arrRequestTypes = GetRequestTypesfromGroupID ($intGroupID, $date);

echo '<div style="position: relative; width:100%" class="tableheadersmall medtextboldcentre">';
echo '<br><h2>Request types for '.$strLeaveGroupDesc.'</h2><br>';

if ($intAdminLevel == 2) {
  echo '<div class="DutyCellBottomLeft" onclick="javascript:EditRequestType(0, '.$intGroupID.')";>';
  echo '<table>';
  echo '<tr>';
  echo '<td align="right">&nbsp;&nbsp;Add New&nbsp;</td>';
  echo '<td>';
  echo '<img border="0" src="images/button_add.png" width="30px" height="30px"></img>';
  echo '</td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';  
}

echo '</div>';

//echo '<div style="width:600px; height: 900px; position: absolute; left: 10px">';
echo '<table class="tablesmalltidy" id="requesttypeslist" width="100%">'; 
echo '<thead>';
echo '<tr>';  
echo '<th>';  
echo 'Description';
echo '</th>';
echo '<th>';  
echo 'Start Date';
echo '</th>';
echo '<th>';  
echo 'End Date';
echo '</th>';
echo '<th>';  
echo 'Starts (Days)';
echo '</th>';
echo '<th>';  
echo 'Ends (Days)';
echo '</th>';
echo '<th>';  
echo 'Sat';
echo '</th>';
echo '<th>';  
echo 'Sun';
echo '</th>';
echo '<th>';  
echo 'Mon';
echo '</th>';
echo '<th>';  
echo 'Tue';
echo '</th>';
echo '<th>';  
echo 'Wed';
echo '</th>';
echo '<th>';  
echo 'Thu';
echo '</th>';
echo '<th>';  
echo 'Fri';
echo '</th>';
echo '<th>';  
echo 'Always Send eMail';
echo '</th>';
echo '<th>';  
echo 'Restricted';
echo '</th>';
echo '<th>';  
echo 'Count Separately';
echo '</th>';
echo '<th>';  
echo 'Waiting List';
echo '</th>';
echo '<th>';  
echo 'Affects Other Requests';
echo '</th>';
echo '<th>';  
echo 'Affects Locks';
echo '</th>';
echo '<th>';  
echo 'Number Allowed';
echo '</th>';  
echo '</tr>';
echo '</thead>'; 
echo '<tbody>';
if (isset($arrRequestTypes)) {
  foreach ($arrRequestTypes as $intTypeID => $arrRequestType) {
    if ($intAdminLevel == 2) {    
      if ($arrRequestType['isRestricted'] == 1) {  
        echo '<tr id="tr'.$intTypeID.'" class="handcursor" ondblclick="javascript:EditRequestType('.$intTypeID.', '.$intGroupID.')";>';  
      }
      else {
        echo '<tr id="tr'.$intTypeID.'" class="handcursor"  onclick="javascript:HideNames()"; ondblclick="javascript:EditRequestType('.$intTypeID.', '.$intGroupID.')";>';
      }
    }
    else {
        echo '<tr>';    
    }
    echo '<td>';  
    echo $arrRequestType['description'];
    echo '</td>';
    echo '<td>';  
    //echo date("d/m/Y", strtotime($row['startdate']));
    echo $arrRequestType['StartDate'];
    echo '</td>';  
    echo '<td>';  

    echo $arrRequestType['EndDate'];
    echo '</td>';   
    echo '<td align="center">';  
    echo $arrRequestType['Starts'];
    echo '</td>';
    echo '<td align="center">';  
    echo $arrRequestType['Ends'];
    echo '</td>';   

    for ($intDay = 0; $intDay <= 6; $intDay++) {
      echo '<td align="center">';    
      echo $arrRequestType['days'][$intDay];
      echo '</td>';  
    }

    echo '<td align="center">';
    echo '<span class="customdateSort">'.$arrRequestType['SendEmail'].'</span>';
    if ($arrRequestType['SendEmail'] == 1) {   
      echo '<img border="0" src="images/green_tick.png" width="16" height="16">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="16" height="16">';    
    }   

    echo '</td>';

    echo '<td align="center">';
    echo '<span class="customdateSort">'.$arrRequestType['isRestricted'].'</span>';
    if ($arrRequestType['isRestricted'] == 1) {   
      echo '<img onclick="javascript:FillNames('.$intTypeID.')" border="0" src="images/green_tick.png" width="16" height="16">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="16" height="16">';    
    }   

    echo '</td>';
  
    echo '<td align="center">';
    echo '<span class="customdateSort">'.$arrRequestType['UniqueCount'].'</span>';
    if ($arrRequestType['UniqueCount'] == 1) {   
      echo '<img border="0" src="images/green_tick.png" width="16" height="16">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="16" height="16">';    
    }   
    echo '</td>';  

    echo '<td align="center">';
    echo '<span class="customdateSort">'.$arrRequestType['AllowOverLimit'].'</span>';
    if ($arrRequestType['AllowOverLimit'] == 1) {   
      echo '<img border="0" src="images/green_tick.png" width="16" height="16">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="16" height="16">';    
    }   
    echo '</td>';

    echo '<td align="center">';
    echo '<span class="customdateSort">'.$arrRequestType['AffectsOthers'].'</span>';
    if ($arrRequestType['AffectsOthers'] == 1) {   
      echo '<img border="0" src="images/green_tick.png" width="16" height="16">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="16" height="16">';    
    }   
    echo '</td>';
        
    echo '<td align="center">';
    echo '<span class="customdateSort">'.$arrRequestType['AffectLocks'].'</span>';
    if ($arrRequestType['AffectLocks'] == 1) {   
      echo '<img border="0" src="images/green_tick.png" width="16" height="16">';
    }
    else {
      echo '<img border="0" src="images/red_cross.png" width="16" height="16">';    
    }   
    echo '</td>';
         
    echo '<td>';
    if ($arrRequestType['UniqueCount'] == 1) {  
      echo $arrRequestType['RequestsAllowed'];
    }
    else {
      echo "Counts towards Group Default";
    }  
    echo '</td>'; 
    echo '</tr>';
  }
}
echo '</tbody>';
echo '</table>';

echo '<div id="namesholder" class="greycurved" id="names" style="width:400px; height: 400px; position: absolute; left: 940px; top:20px">';
echo '<table border="0" width="100%">';
echo '<tr>';
echo '<td>Assign People</td>';
echo '<td class="handcursor" align="right" onclick="javascript:HideNames()";>';
echo '<img border="0" src="images/closelabel.png" width="8" height="8"></td>';
echo '</tr>';
echo '<tr>';
echo '<td width="100%" colspan="2">';
echo '<div id="names">';
echo '</div>';
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';

?>
<script type="text/javascript">
 $('#adminleavetabs').height($(window).height() - 75);
 $('#admingrouprequesttabs').height($(window).height() - 175);
 $(document).ready(function(){
    var table = $("#requesttypeslist").DataTable({
    paging: false,
    scrollY: parseInt($(window).height() - 322),
    info:     false,
    stateSave: true,
    deferRender: true,
	scrollX: true
    });
 })
function HideNames () {
  $( "#namesholder" ).slideUp( "slow", function() {
    // Animation complete.
  });

}
function FillNames (id) {

  $( "#namesholder" ).slideDown( "slow", function() {
    // Animation complete.
  });


  $.post("page-includes/admin/requesttypefillnames.php", {
  id: id
  },
  function(data,status){
    $('#names').html(data);
   }
  )
}

</script>


