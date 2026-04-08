<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/pageperms.php';

$strSessionAdditionalFields = 'JobsUserFields';
$windowheightoffset = 280;


if (isset($_REQUEST["id"])) {
  $currid = $_REQUEST["id"];
}
else {
  $currid = 0;
}
if (isset($_REQUEST["listtype"])) {
  $intListType = $_REQUEST["listtype"];
}
else {
  $intListType = 0;
}
if (isset($_REQUEST["archived"])) {
  $intArchived = $_REQUEST["archived"];
}
else {
  $intArchived = 0;
}

$intHourWidth = 80;
$intDutyHeight = 30;
$currenttop = 50;
$earlieststart = 0;
$lateststart = 24;
$intDutyTypeID = 5;

//Check User Authentication
$pageid = 6;
$perms = $_SESSION['areaperms'];
for($ArraySeq = 0; $ArraySeq < (count($perms) - 1); $ArraySeq++){
    if($perms[$ArraySeq]["formid"] == $pageid){
        $canview = $perms[$ArraySeq]["isview"];
        $canmodify = $perms[$ArraySeq]["ismodify"];
        $canviewextended = $perms[$ArraySeq]["isviewextended"];
        $canmodifyextended = $perms[$ArraySeq]["ismodifyextended"];
        $candelete = $perms[$ArraySeq]["isdelete"];
    }
}

if ($canmodify == 1) {
  $classname = 'draggable';
}
else {
  $classname = 'notdraggable';
}

$intAreaID = $_SESSION['user']['AreaID'];
$rsDutiesJson = ListAllMasterDuties($intAreaID, $intDutyTypeID, 0);
$rsDuties = json_decode($rsDutiesJson,true);
$earlieststart = 24;
$lateststart = 0;

if (!empty($rsDuties)) {

  for($row = 0; $row < count($rsDuties); $row++) {
    $intThisEnd = ceil($rsDuties[$row]['EndTime'] / 3600);
    if ($intThisEnd > $lateststart) {
      $lateststart = $intThisEnd;    
    }
    $intThisStart = floor($rsDuties[$row]['StartTime'] / 3600);
    if ($intThisStart < $earlieststart) {
      $earlieststart = $intThisStart;    
    }  
    $arrDuties[$rsDuties[$row]['id']]['DutyName'] = $rsDuties[$row]['DutyName'];
    $arrDuties[$rsDuties[$row]['id']]['StartTime'] = $rsDuties[$row]['StartTime'];
    $arrDuties[$rsDuties[$row]['id']]['EndTime'] = $rsDuties[$row]['EndTime'];
    $arrDuties[$rsDuties[$row]['id']]['Duration'] = $rsDuties[$row]['Duration'];
    $arrDuties[$rsDuties[$row]['id']]['WindowDuration'] = $rsDuties[$row]['WindowDuration'];
    $arrDuties[$rsDuties[$row]['id']]['StartDate'] = $rsDuties[$row]['StartDate'];
    $arrDuties[$rsDuties[$row]['id']]['EndDate'] = $rsDuties[$row]['EndDate'];
    $arrDuties[$rsDuties[$row]['id']]['backcolour'] = $rsDuties[$row]['ColourBackground'];
    $arrDuties[$rsDuties[$row]['id']]['forecolour'] = $rsDuties[$row]['ColourFont'];
    $arrDuties[$rsDuties[$row]['id']]['dotw'] = $rsDuties[$row]['dotw'];
    $arrDuties[$rsDuties[$row]['id']]['lastmoddate'] = $rsDuties[$row]['LastModDate'];
    $arrDuties[$rsDuties[$row]['id']]['dutyhasjobs'] = 0;
  }
}
    
  // ################################################################ First a div with the Duty names

  if (isset($arrDuties)) {
    $intCounter = 0;
    echo '<div style="overflow: scroll; position: absolute; width:250px; height:200px; left:0px; top:'.$currenttop.'px" id="names">';
    foreach ($arrDuties as $intDutyID => $arrDuty) {
      echo '<div class="rotaduty-context-menu" dutyid="'.$intDutyID.'" id="'.$intDutyID.'" style="background-color: #99bfe6; position: absolute; width:230px; height:'.($intDutyHeight - 1).'px; left:0px; top:'.($intCounter * $intDutyHeight).'px">';
      echo '<b>'.$arrDuty['DutyName'].'</b>';
      echo '<br>';
      //echo FormatTime($arrDuty['StartTime']).'-'.FormatTime($arrDuty['EndTime']);
      //echo $arrDuty['dotw'];
      //$arrDoTW = ExplodeStringToArray($arrDuty['dotw']);
      //print_r($arrDoTW);
      
	  
      echo '</div>';
      $intCounter++;
    }
    echo '</div>';
    // ################################################################ End the Duty Names

    // ################################################################ First a div with the Duties and Jobs
    
    $currenttop = $currenttop - 10;
    $intCounter = 0;
    echo '<div class="compact stripe bluetable draggable" style="overflow:scroll; position: absolute; width:1000px; height:200px; left:250px; top:'.$currenttop.'px" id="duties">';
    foreach ($arrDuties as $intDutyID => $arrDuty) {
      // a holder for everythnig
      echo '<div class="rotaduty-context-menu" dutyid="'.$intDutyID.'" style="height:'.($intDutyHeight).'px; position:absolute; top:'.($intCounter * $intDutyHeight).'px; background-color:#'.strval($arrDuty['backcolour']).'; color:#'.strval($arrDuty['forecolour']).';">';
      // Draw in the Times
      //drawtimecells("2015-01-01", $earlieststart, $lateststart, $intHourWidth, $intDutyHeight-1);

      $intWidth = 500;
      $left = 0;
      echo '<div class="'.$classname.'" dutyid="'.$intDutyID.'" dutyname="'.$arrDuty['DutyName'].'" dutytypeid="3" style="border: 1px solid #C0C0C0; text-align:center; width: '.$intWidth.'px; height:'.($intDutyHeight - 8).'px; position:absolute; left:'.$left.'px; top:4px; background-color:#'.strval($arrDuty['backcolour']).'; color:#'.strval($arrDuty['forecolour']).';">';
      echo ''.$arrDuty['DutyName'].'';
      echo '</div>';   
      echo '</div>';
      $intCounter++;

    }
    echo '</div>';
  
  }
  
  echo '<div id="drag_helper" style="width:50px;"></div>';


?>

<script type="text/javascript">

DoResize();
$(document).ready(function(){
  if (<?php echo empty($canview) ? 0:$canview ?> == 0) {
    $( '#content' ).load( 'page-includes/no_access.php', function() { });
  }
  else {
    $("#rotadutieslist").DataTable({
      paging: false,
      scrollY: 400, 
      info:     false,
      stateSave: true,
      "initComplete": function( settings, json ) {
	DoMDResize();
      }
    });
  }
});

$(function() {
  $(".draggable" ).draggable({
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

$('#duties').on('scroll', function () {
    $('#names').scrollTop($(this).scrollTop());
    $('#top').scrollLeft($(this).scrollLeft());
    $('#unallocatedduties').scrollLeft($(this).scrollLeft());
    $('#unallocatedjobs').scrollLeft($(this).scrollLeft());
    $(".leaders").scrollLeft($(this).scrollLeft());     
});

$('#names').on('scroll', function () {
    $('#duties').scrollTop($(this).scrollTop());
});

$( window ).resize(function() {
DoResize;
});


function DoResize () {
  var widowwidth = $(window).width() - 285;
  var widowheight = 250;	//$(window).height() - <?=$windowheightoffset?>;   
  $("#duties").width(widowwidth).height(widowheight);
  $("#names").height(widowheight);
  $("#top").width(widowwidth);   
}


$(function() {
  $.contextMenu({
    selector: '.rotaduty-context-menu', 
    items: {    
    "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
          DutyHistory(dutyid);
        }
      }
   }});
});

      
</script>      
