<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';

$intAreaID = $_SESSION['user']['AreaID'];
$intRotaID = $_REQUEST["id"];


$intWeeksInRota = 0;
$dtRotaStartDate = '';
$intType = 0;

$strEffDate = date("d/m/Y");

echo '<table id="rotadetails" class="stripe bluetable" width="100%">';
echo '<thead>';
echo '<tr>';
echo '<th colspan="4">Showing Rota People ';
echo '</th>';
echo '</tr>';
echo '<tr>';
echo '<th style="border-top:2px solid #000000;" width="5%">Line</th>';
echo '<th style="border-top:2px solid #000000;" width="11%">Name</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($intWeeksInRota > 0) {
  for ($intWeek = 1; $intWeek <= $intWeeksInRota; $intWeek++) {
    echo '<tr>';
    echo '<td class="rotatabledroppable">';
    echo $intWeek;
    echo '</td>';

    for ($intDay = 0; $intDay <= 6; $intDay++) {
      $intDayOfRota = ($intWeek * 7) + $intDay;

      if (isset($arrRota[$intWeek]['dutyid'])) {
	$intRotaPersonID = $arrRota[$intWeek]['rotapeopleid'];
	$intStaffID = $arrRota[$intWeek]['staffid'];
	$strPersonName = $arrRota[$intWeek]['fullname'];
	$strStartDate = $arrRota[$intWeek]['startdate'];
	$strEndDate = $arrRota[$intWeek]['enddate'];
      }
      else {
	$intRotaPeopleID = 0;
	$intStaffID = 0;
	$strPersonName = '';
	$strStartDate = '';
	$strEndDate = '';
      }


      echo '<td class="rotatabledroppable" id="w'.strval($intWeek).'" staffid="'.$intStaffID.'" rotaid="'.$intRotaID.'" weekid="'.$intWeek.'" >';
      echo '<div class="rotaselectedduty-context-menu" rotapeopleid="'.$intRotaPeopleID.'" staffid="'.$intStaffID.'" rotaid="'.$intRotaID.'" lastmoddate="">';


	echo $strPersonName;

      echo '</div>';
      echo '</td>';
    }

  }
}


?>


<script type="text/javascript">

$(document).ready(function(){

    $( "td.rotatabledroppable" ).droppable({
      tolerance: "pointer",
      over: function(event, ui) {
	$(this).addClass('highlighted');
      },
      out: function(event, ui) {
	$(this).removeClass('highlighted');
      },
      drop: function( event, ui ) {
	$(this).removeClass('highlighted');
	var dutyid = ui.draggable.attr("dutyid");
	var dutyname = ui.draggable.attr("dutyname");
	var dutytypeid = ui.draggable.attr("dutytypeid");
	var dutybackcolour = ui.draggable.css("background-color");
	var dutyforecolour = ui.draggable.css("color");
	var dayofrota = $(this).attr("dayofrota");
	var dropdutyid = $(this).attr("dutyid");
	var dropdutytypeid = $(this).attr("dutytypeid");
	var rotaid = $(this).attr("rotaid");
	var weekid = $(this).attr("weekid");
	var blUpdate = 0;
	var blnoreplace = 0;

	if (dropdutyid != 0) {

	  if (confirm ('Do you want to replace the current duty?')) {
	    blUpdate = 1;
	    blreplace = 1;
	  }
	  else {
	    blnoreplace = 1;
	  }
	}
	else {
	  blUpdate = 1;
	}

	var isassigned;
	var istemplate;

	switch (dutytypeid) {
	case "1":
	  isassigned = 1;
	  istemplate = 0;
	  break;

	case "2":
	  isassigned = 0;
	  istemplate = 1;
	  break;

	case "3":
	  isassigned = 0;
	  istemplate = 1;
	  break;

	case "4":
	  isassigned = 1;
	  istemplate = 0;
	  break;

	case "5":
	  isassigned = 1;
	  istemplate = 0;
	  break;
      }
    if (blUpdate != 0) {
      $.ajax({
                url: "page-includes/rotas/dropduty.php",
                type: "POST",
		        dataType: "json",
                data: {
                    'DutyID': dutyid,
		            'DutyTypeID': dutytypeid,
		            'RotaID': rotaid,
                    'DayOfRota': dayofrota,
                    'WeekID': weekid,
                    'IsAssigned': isassigned,
                    'IsTemplate': istemplate
                },
                success: function(data) {
		    if (data.status == 'success') {
		      //all good
		      ShowRotaDetails(rotaid, 1)
		    }
		    else {
		      customAlert(data.status);
		    }
		},
		error:function(x,e) {
		    if (x.status==0) {
			customAlert('You are offline!!<br/> Please Check Your Network.');
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
    else if (blnoreplace == 1) {
      //Person doesn't want to replace Duty
    }
    else {
      customAlert('Please ensure you drop the Duty on a valid Rota position');
    }

  }
    });


  $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    $(function() {
      $("button")
	.button()
    });


  $(function() {
    $( ".selector" ).selectmenu({
      change: function( event, ui ) {
	var value = $(this).val();
	ShowRotaDetails(value, 1);
      }
    });
});



function ShowAll(rotaid, showall) {
  //var showtemplates = $("#ddlViewTemplate option:selected").attr("value");
  var tabCookieName = "rotashowall";
  $.cookie(tabCookieName, showall, { expires: 1 });

  if (showall > 0) {
    $('#effectivedate').hide();
  }
  else {
    $('#effectivedate').show();
  }

  ShowRotaDetails(rotaid, 1);
}

function EditDuty(rotadutyid, rotaid, dutyid) {
  if (rotadutyid > 0) {
      $.ajax({
          type: 'POST',
          url: "page-includes/rotas/edit-duty.php",
          data: {
              "rotadutyid": rotadutyid,
              "rotaid": rotaid,
              "dutyid": dutyid
          },
          success: function (data) {
              $.facebox(data);
          }
      });
  }
}

$(function(){
  $.contextMenu({
    selector: '.rotaselectedduty-context-menu',
    items: {
    "edit": {
        name: "Edit Duty",
        icon: "edit",
          // superseeds "global" callback
          callback: function(key, options) {
          var rotadutyid =  options.$trigger.attr("rotadutyid");
          var rotaid =  options.$trigger.attr("rotaid");
          var dutyid =  options.$trigger.attr("dutyid");
          //var $tabs = $('#masterdutiestabs').tabs();
	  //var selected = $tabs.tabs('option', 'active');
          EditDuty(rotadutyid, rotaid, dutyid);
        }
      },
    "delete": {
        name: "Delete Duty",
        icon: "delete",
          // superseeds "global" callback
          callback: function(key, options) {
          var dutyid =  options.$trigger.attr("dutyid");
          var lastmoddate = options.$trigger.attr("lastmoddate");
          //var $tabs = $('#masterdutiestabs').tabs();
	  //var selected = $tabs.tabs('option', 'active');
          DeleteRotaDuty(dutyid, lastmoddate);
        }
      },
   }})});

</script>

