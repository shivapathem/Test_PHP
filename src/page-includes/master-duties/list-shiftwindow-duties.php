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

//Check User Authentication
$pageid = 4;
$perms = $_SESSION['areaperms'];
for($ArraySeq = 0; $ArraySeq < count($perms); $ArraySeq++){
    if($perms[$ArraySeq]["formid"] == $pageid){
        $canview = $perms[$ArraySeq]["isview"];
        $canmodify = $perms[$ArraySeq]["ismodify"];
        $canviewextended = $perms[$ArraySeq]["isviewextended"];
        $canmodifyextended = $perms[$ArraySeq]["ismodifyextended"];
        $candelete = $perms[$ArraySeq]["isdelete"];
    }
}

$intHourWidth = 80;
$intDutyHeight = 30;
$currenttop = 50;
$earlieststart = 0;
$lateststart = 24;

$intDutyTypeID = 3;


$intAreaID = $_SESSION['user']['AreaID'];
$rsDutiesJson = ListAllMasterDuties($intAreaID, $intDutyTypeID, $intArchived);
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
else{
    Echo "<h1>No result Found.<h1>";
    die;
}

if (isset($arrDuties)) {

  // The hours in the day....
  echo '<div style="overflow: hidden; position: absolute; width:900px; height:30px; left:250px; top:'.$currenttop.'px" id="top" class="times">';
  for ($i=$earlieststart; $i <= ($lateststart + $earlieststart); $i++){
    echo '<div style="width:'.$intHourWidth.'px;  position:absolute; left:'.(($i - $earlieststart) * $intHourWidth).'px; top:0px">'; 
    echo '<div class="highlighted" style="position: absolute; left: 0px; top:0px; width:'.$intHourWidth.'px; height:15px">'.(gmdate("H:00",  $i * 3600)).'</div>';
    echo '<div style="position: absolute; left:-5px; top:15px; width:20px;"><img border="0" src="images/hourpointer.png" width="11" height="11" /></div>';
    echo '</div>';
  }
  echo '</div>';

  
  $currenttop = $currenttop + 30;
  
  // ################################################################ First a div with the Duty names

 
    $intCounter = 0;
    $prevDutyID = 0;
    echo '<div style="overflow: scroll; position: absolute; width:250px; height:400px; left:0px; top:'.$currenttop.'px" id="names">';
    foreach ($arrDuties as $intDutyID => $arrDuty) {
      if ($canmodify == 1) {
	if ($intArchived==0) { 
	  echo '<div class="listshiftwindow-context-menu" id="'.$intDutyID.'" dutyid="'.$intDutyID.'" prevdutyid="'.$prevDutyID.'" style="background-color: #99bfe6; position: absolute; width:230px; height:'.($intDutyHeight - 1).'px; left:0px; top:'.($intCounter * $intDutyHeight).'px">';
	}
	else {
	  echo '<div class="listshiftwindowarchived-context-menu" id="'.$intDutyID.'" dutyid="'.$intDutyID.'" prevdutyid="'.$prevDutyID.'" style="background-color: #99bfe6; position: absolute; width:230px; height:'.($intDutyHeight - 1).'px; left:0px; top:'.($intCounter * $intDutyHeight).'px">';
	}
      }
      else {
	if ($intArchived==0) { 
	  echo '<div class="listshiftwindowbasic-context-menu" id="'.$intDutyID.'" dutyid="'.$intDutyID.'" prevdutyid="'.$prevDutyID.'" style="background-color: #99bfe6; position: absolute; width:230px; height:'.($intDutyHeight - 1).'px; left:0px; top:'.($intCounter * $intDutyHeight).'px">';
	}
	else {
	  echo '<div class="listshiftwindowarchivedbasic-context-menu" id="'.$intDutyID.'" dutyid="'.$intDutyID.'" prevdutyid="'.$prevDutyID.'" style="background-color: #99bfe6; position: absolute; width:230px; height:'.($intDutyHeight - 1).'px; left:0px; top:'.($intCounter * $intDutyHeight).'px">';
	}
      }
      echo  $arrDuty['DutyName'];
      echo '<br>';
      //echo FormatTime($arrDuty['StartTime']).'-'.FormatTime($arrDuty['EndTime']);
	  
      echo '</div>';
  	 $prevDutyID = $intDutyID;
      $intCounter++;
    }
    echo '</div>';
    // ################################################################ End the Duty Names

    // ################################################################ First a div with the Duties and Jobs
	
    $intCounter = 0;
    $prevDutyID = 0;
    echo '<div style="overflow:scroll; position: absolute; width:1000px; height:400px; left:250px; top:'.$currenttop.'px" id="duties">';
    foreach ($arrDuties as $intDutyID => $arrDuty) {
      // a holder for everything
      if ($canmodify == 1) {
	if ($intArchived==0) {
	  echo '<div class="listshiftwindow-context-menu" id="dutydet'.$intDutyID.'" dutyid="'.$intDutyID.'" prevdutyid="'.$prevDutyID.'" style="height:'.($intDutyHeight).'px; position:absolute; top:'.($intCounter * $intDutyHeight).'px">';
	}
	else {
	  echo '<div class="listshiftwindowarchived-context-menu" id="dutydet'.$intDutyID.'" dutyid="'.$intDutyID.'" prevdutyid="'.$prevDutyID.'" style="height:'.($intDutyHeight).'px; position:absolute; top:'.($intCounter * $intDutyHeight).'px">';
	}
      }
      else {
	if ($intArchived==0) {
	  echo '<div class="listshiftwindow-context-menu" id="dutydet'.$intDutyID.'" dutyid="'.$intDutyID.'" prevdutyid="'.$prevDutyID.'" style="height:'.($intDutyHeight).'px; position:absolute; top:'.($intCounter * $intDutyHeight).'px">';
	}
	else {
	  echo '<div class="listshiftwindowarchived-context-menu" id="dutydet'.$intDutyID.'" dutyid="'.$intDutyID.'" prevdutyid="'.$prevDutyID.'" style="height:'.($intDutyHeight).'px; position:absolute; top:'.($intCounter * $intDutyHeight).'px">';
	}
      }
      // Draw in the Times
      drawtimecells("2015-01-01", $earlieststart, $lateststart, $intHourWidth, $intDutyHeight-1);

      //$intWidth = intval((($arrDuty['EndTime'] - $arrDuty['StartTime']) / 3600) * $intHourWidth);
      //$left = intval((floor(($arrDuty['StartTime'] / 3600))-1) * $intHourWidth);
      $intWidth = (($arrDuty['EndTime'] - $arrDuty['StartTime']) / 3600) * $intHourWidth;
      $left = (($arrDuty['StartTime'] / 3600) - $earlieststart) * $intHourWidth;
      echo '<div style="width:'.$intWidth.'px; height:'.($intDutyHeight - 8).'px; position:absolute; left:'.$left.'px; top:4px; background-color:#'.strval($arrDuty['backcolour']).'; color:#'.strval($arrDuty['forecolour']).';">';
      echo '</div>';  
      echo '<div class="boxed" style="text-align:center; width:'.$intWidth.'px; height:'.($intDutyHeight - 8).'px; position:absolute; left:'.$left.'px; top:4px; background-color:#'.strval($arrDuty['backcolour']).'; color:#'.strval($arrDuty['forecolour']).';">';
      echo ''.$arrDuty['DutyName'].'';
      echo '</div>';   
      echo '</div>';  
	 $prevDutyID = $intDutyID;
      $intCounter++;

    }
    echo '</div>';
}


?>

<script type="text/javascript">

DoResize();
$(document).ready(function(){
  if (<?php echo empty($canview) ? 0:$canview ?> == 0) {
    $( '#content' ).load( 'page-includes/no_access.php', function() { });
  }
  else {
    $("#masterdutieslist").DataTable({
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

function DeleteDuty(dutyid, prevdutyid, lastmoddate, action) {
    //check for Duty in Rotas before deleting
    $.ajax({ 
                url: "page-includes/master-duties/delete-duty.php",
                type: "POST",
                dataType: "json", 
                data: {
                    'dutyid': dutyid,
		    'action': action, 
		    'lastmoddate': lastmoddate
                },
                success: function(data) {
		    if (data.status == 'success') {
		      //all good
		      ListMasterDuties(2, prevdutyid, 0);
		    }
		    else {
		      customAlert(data.status);
		    }
		  
		  //all good
		},
		error:function(x,e) {
		    if (x.status==0) {
			customAlert('You are offline!!<br/>Please Check Your Network.');
		    } else if(x.status==404) {
			customAlert('Requested URL not found.');
		    } else if(x.status==500) {
			customAlert('Internal Server Error.');
		    } else if(e=='parsererror') {
			customAlert('Error.<br/>Parsing JSON Request failed.');
		    } else if(e=='timeout'){
			customAlert('Request Time out.');
		    } else {
			customAlert('Unknow Error.<br/>'+x.responseText);
		    }
		}
            });
}

$( window ).resize(function() {
DoResize;
});


function DoResize () {
  var widowwidth = $(window).width() - 285;
  var widowheight = $(window).height() - <?=$windowheightoffset?>;   
  $("#duties").width(widowwidth).height(widowheight);
  $("#names").height(widowheight);
  $("#top").width(widowwidth);   
}

$(function(){
  $.contextMenu({
    selector: '.listshiftwindowbasic-context-menu', 
    items: { 
    "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
          DutyHistory(dutyid);
        }
      },
   }});
});

$(function(){
  $.contextMenu({
    selector: '.listshiftwindow-context-menu', 
    items: { 
    "edit": {
        name: "Edit",
        icon: "edit",
          // superseeds "global" callback
          callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
          EditDuty(dutyid, 2);
        }
      },     
    "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
          DutyHistory(dutyid);
        }
      },
    "delete": {
        name: "Delete Shift Window",
        icon: "delete",
          // superseeds "global" callback
          callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
	     var prevdutyid =  options.$trigger.attr("prevdutyid");
          var lastmoddate = '';
          DeleteDuty(dutyid, prevdutyid, lastmoddate, 1);
        }
      },
   }});
});

$(function(){
    $.contextMenu({
    selector: '.listshiftwindowarchivedbasic-context-menu', 
    items: { 
    "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
          DutyHistory(dutyid);
        }
      },
   }});
});

$(function(){
    $.contextMenu({
    selector: '.listshiftwindowarchived-context-menu', 
    items: { 
    "history": {
        name: "History",
        icon: "history",
        // superseeds "global" callback
        callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
          DutyHistory(dutyid);
        }
      },
    "delete": {
        name: "UnDelete Shift Window",
        icon: "delete",
          // superseeds "global" callback
          callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
	     var prevdutyid =  options.$trigger.attr("prevdutyid");
          var lastmoddate = '';
          DeleteDuty(dutyid, prevdutyid, lastmoddate, 0);
        }
      },
   }});
});


      
</script>      
