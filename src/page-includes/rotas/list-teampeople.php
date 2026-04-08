<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';
include_once 'setTeamCookieRotas.php';
$pageid = 24;

if (isset($_REQUEST["id"])) {
    $currid = $_REQUEST["id"];
    $StaffFlag = $_REQUEST["StaffFlag"];
}
else {
    $currid = 0;
    $StaffFlag = 0;
}
if($currid == 0)
{
	$permissions = getUserRolePermissions($pageid);
}else
{
	$permissions = getUserRoleByTeam($pageid, $currid);
}
if (isset($_REQUEST["listtype"])) {
    $intListType = $_REQUEST["listtype"];
}
else {
    $intListType = 0;
}

if ($permissions->canmodify == 1 || $permissions->cancreate == 1){
    $TeamDrag = "teampeopledraggable";
}
else{
    $TeamDrag = "Noteampeopledraggable";
}
$intRecordHeight = 30;
$intAreaID = $_SESSION['user']['AreaID'];
$intTeamID = $currid;
$rsPeopleJson = GetUserTeamPeopleList(0, 0, $intTeamID, $StaffFlag);
$rsPeople = json_decode($rsPeopleJson,true);
if (!empty($rsPeople)) {
    $rowcount = count($rsPeople);
  for ($row = 0; $row < $rowcount; $row++) {
    $arrPeople[$rsPeople[$row]['ScheduledPersonID']]['PersonName'] = $rsPeople[$row]['Fullname'];
    $arrPeople[$rsPeople[$row]['ScheduledPersonID']]['EFT'] = $rsPeople[$row]['EFT'];
  }
}

echo '<div style="width:100%; margin-top: -5px">';
echo '<div style="width:100%;">';
echo '<table>';
echo '<tbody>';
echo '<tr class="showStaffRadio">';
echo '<td>';
echo '<input type="radio" id="ShowAll" name="ShowStaff">Show All Staff</input>';
echo '</td>';
echo '<td>';
echo '<input type="radio" id="ShowFutureStaff" name="ShowStaff">Future Staff</input>';
echo '</td>';
echo '<td>';
echo '<input type="radio" id="ShowLimitedStaff" name="ShowStaff">Show Staff with no Rota Pattern</input>';
echo '</td>';
echo '</tr>';

echo '</tbody>';
echo '</table>';
echo '</div>';
echo '<table class="stripe bluetable" style="width:100%; margin-top: -2px">';
echo '<thead>';
echo '<tr>';
echo '<th colspan="2">People in the Team (drag left onto a Rota line)';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th style="border-top:2px solid #000000;" width="100%">Name <input type="text" id="filter"></th>';
echo '</tr>';
echo '</thead>';
echo '</table>';
echo '<div style="overflow:auto; margin-top:-15px; float:right; width:100%; height:100px;">';
echo '<table id="teampeoplelist" class="stripe bluetable" style="width:100%;">';
echo '<tbody>';
if (isset($arrPeople)) {
  foreach ($arrPeople as $intScheduledPersonID => $arrPerson) {
    $strName = $arrPerson['PersonName'];
    $strEFT = $arrPerson['EFT'];
    if($strName != '' || $strName != null) {
        echo '<tr personid="' . $intScheduledPersonID . '" lastmoddate="">';
        echo '<td title="'.$strName.'"  data-size="'.$strName.'"  class="' . $TeamDrag . '" id="' . $intScheduledPersonID . '" ScheduledPersonID="' . $intScheduledPersonID . '" >';
        echo substr($strName,0,50);
        echo '</td>';
        echo '</tr>';
    }
  }
}
echo '</div>';
//echo '</tr>';
echo '</tbody>';
echo '</table>';
echo '</div>';
echo '<div id="drag_helper" style="width:50px;"></div>';

?>

<script type="text/javascript">
    $(document).ready(function(){
    if (<?php echo empty($StaffFlag) ? 0:$StaffFlag ?> == 0) {
        $('input:radio[name=ShowStaff][id=ShowAll]').attr('checked', true);
    }
    else if (<?php echo empty($StaffFlag) ? 0:$StaffFlag ?> == 1) {
        $('input:radio[name=ShowStaff][id=ShowLimitedStaff]').attr('checked', true);
    }
    else {
        $('input:radio[name=ShowStaff][id=ShowFutureStaff]').attr('checked', true);
    }
  $("#ShowAll").click(function(){
      $('input:radio[name=ShowStaff][id=ShowAll]').attr('checked', true);
      ShowRotaPeople(0);
  });
  $("#ShowLimitedStaff").click(function(){
      $('input:radio[name=ShowStaff][id=ShowLimitedStaff]').attr('checked', true);
      ShowRotaPeople(1);
  });
  $("#ShowFutureStaff").click(function(){
      $('input:radio[name=ShowStaff][id=ShowFutureStaff]').attr('checked', true);
      ShowRotaPeople(2);
  });
  $("#rotadutieslist").DataTable({
    paging: false,
    scrollY: 400, 
    info:     false,
    stateSave: true,
    "initComplete": function( settings, json ) {
      // DoMDResize();
    }
  });


});


$("#filter").keyup(function(){
    var selectSize = $(this).val();
    filter(selectSize);
});

function filter(e) {
    var regex = new RegExp('\\b\\w*' + e + '\\w*\\b', "i");
    $('.teampeopledraggable').hide().filter(function () {
        return regex.test($(this).data('size'))
    }).show();
}

$(function() {
  $(".teampeopledraggable" ).draggable({
    cursor: "move",
    appendTo: '#drag_helper',
    revert: "invalid",
    containment: "document",
    helper: "clone",
    zIndex: 100,
    cursorAt: {left:40, top:25}, 
    onStartDrag:function(){
      $(this).draggable('options').cursor = 'not-allowed';
      $(this).draggable('proxy').css('z-index',10);      
    },
    onStopDrag:function(){
        $(this).draggable('options').cursor='move';
    }
  });
});

</script>
