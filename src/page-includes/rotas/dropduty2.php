<?php
session_start();

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/init.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/rota_functions.php';
include_once '../../class-includes/pageperms.php';
include_once '../../class-includes/userRolePermissions.php';

$pageid = 3;
// Call User Permission function.
$permissions = getUserRolePermissions($pageid);


$intDutyID = $_REQUEST["DutyID"];
$intDutyTypeID = $_REQUEST["DutyTypeID"];
$intRotaID = $_REQUEST["RotaID"];
$intWeekID = $_REQUEST["WeekID"];
$intDayOfRota = $_REQUEST["DayOfRota"];
$intIsAssigned = $_REQUEST["IsAssigned"];
$intIsTemplate = $_REQUEST["IsTemplate"];


  if ($permissions->canmodify == 1) {
    $Date1 = '';
    $Date2 = '';
    
    if ($Date1 == '') {
      $Date1 = '01/01/1994';
    }
    if ($Date2 == '') {
      $Date2 = '31/12/2030';
    }
    

    echo '<div style="width: 600px">';
    echo '<form id="neweditrotaduty">';
    echo '<table id="rotadutynewedittable" class="smalltable bluetable" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th colspan="4">What date range would you like for this Duty in the Rota...</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

       
    echo '<tr>';
    echo '<td class="lightblue" width="15%">Available From</td>';
    echo '<td width="35%">';
    echo '<input id="startdate" name="startdate" type="text" size="15" value="'.$Date1.'" />';
    echo '</td>';
    echo '<td class="lightblue" width="15%">Available Until </td>';
    echo '<td width="35%">';
    echo '<input id="enddate" name="enddate" type="text" size="15" value="'.$Date2.'" />';
    echo '</td>';
    echo '</tr>';
    
    
    echo '<tr>';  
    echo '<td></td>';
    echo '<td colspan="4"><input name="submit" type="submit" value="Submit"></input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="submit" type="submit" value="Cancel"></input></td>';
    echo '</tr>';
											      
    echo '</tbody>';
    echo '</table>';
    echo '<input type="hidden" id="DutyID" name="DutyID" value="'.$intDutyID.'">';
    echo '<input type="hidden" id="DutyTypeID" name="DutyTypeID" value="'.$intDutyTypeID.'">';
    echo '<input type="hidden" id="RotaID" name="RotaID" value="'.$intRotaID.'">';
    echo '<input type="hidden" id="WeekID" name="WeekID" value="'.$intWeekID.'">';
    echo '<input type="hidden" id="DayOfRota" name="DayOfRota" value="'.$intDayOfRota.'">';
    echo '<input type="hidden" id="IsAssigned" name="IsAssigned" value="'.$intIsAssigned.'">';
    echo '<input type="hidden" id="IsTemplate" name="IsTemplate" value="'.$intIsTemplate.'">';
    
    //echo '<input type="hidden" name="oldNotes" value="'.$oldNotes.'">';
    //echo '<input type="hidden" name="LastModDate" value="'.$strLastMod.'">';
    //echo '<input type="hidden" name="listtype" value="'.$intType.'">';
    echo '</form>';  
    echo '</div>';
  }

?>

<script type="text/javascript">

$(document).ready(function(){ 
  if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
    $( '#content' ).load( 'page-includes/no_access.php', function() { });
  }
  else {
    $( "#startdate" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      dateFormat: "dd/mm/yy"
    });
    
    $( "#enddate" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showButtonPanel: true,
      dateFormat: "dd/mm/yy"
    });
    
    $('#neweditrotaduty').validate({
      debug: true,
      rules:{
	"startdate":{
	  required:true,
	} 
      },
	messages:{
	  "startdate":{
	    required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter a Start Date.' />"
	  }
	},
	submitHandler: function(form) {
	  var rotaid = form.elements["RotaID"].value;
	  var weekid = form.elements["WeekID"].value;
	  var dayofrota = form.elements["DayOfRota"].value;
	  var dutyid = form.elements["DutyID"].value;
	  var dutytypeid = form.elements["DutyTypeID"].value;
	  var isassigned = form.elements["IsAssigned"].value;
	  var istemplate = form.elements["IsTemplate"].value;
	  var startdate = form.elements["startdate"].value;
	  var enddate = form.elements["enddate"].value;
      
	    $.ajax({
		      url: "page-includes/rotas/dropduty.php",
		      type: "POST",
		      dataType: "json", 
		      data: {
			  'RotaDutyID': 0,
			  'RotaID': rotaid,
			  'WeekID': weekid,
			  'DayOfRota': dayofrota,
			  'DutyID': dutyid,
			  'DutyTypeID': dutytypeid,
			  'IsAssigned': isassigned,
			  'IsTemplate': istemplate,
			  'StartDate': startdate,
			  'EndDate': enddate
		      },
		      success: function(data) {
			  if (data.status == 'success') {
			    //all good
			    if (data.sqlstatusstring != '') {
			      customAlert(data.sqlstatusstring);
			    }
			    else {
			        ShowRotaDetails(rotaid, weekid);
			        $.facebox.close();
			    }
			  }
			  else {
			    customAlert(data.sqlstatusstring);
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
			      customAlert('Unknown Error.<br/>'+x.responseText);
			  }
		      }
      
		  });
	}
      });
  }
});
  
$(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
 $(function() {
  $( "button" )
    .button()
});
  
  
</script>



