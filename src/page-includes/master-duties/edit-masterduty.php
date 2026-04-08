<?php
session_start();
include_once '../../function-includes/DBHelper.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/masterduty_functions.php';
include_once '../../class-includes/userRolePermissions.php';

$intID = $_REQUEST["id"];
$intDutyType = $_REQUEST["dutytype"];
$intIsRota = $_REQUEST["isrota"];
$ddlRotaTeams = $_REQUEST["ddlRotaTeams"] ?? '';


if (!(isset($intIsRota))) {
  $intIsRota = 1;
  $_SESSION['isrota'] = 1;
}
$intDBDutyType = ($intDutyType + 1);
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'];
$intType = 0;
$intShowForm = 1;
$isEdit = $_REQUEST['isEdit'];


//Check User Authentication
if ($intIsRota == 1) {
    $_SESSION['isrota'] = 1;
    $pageid = 3; // Rota form id
    // Call User Permission function.
    $permissions = getUserRolePermissions($pageid, $ddlRotaTeams);
}
else {
    $_SESSION['isrota'] = 0;
    $pageid = 1; //Master Duties form id.
    // Call User Permission function.
    $permissions = getUserRolePermissions($pageid, $ddlRotaTeams);
}


//check if we show the form
if ($permissions->canview == 1) {
  $monday =  $tuesday =  $wednesday = $thursday = $friday = $saturday = $sunday = $intDutyColourID =0;
  $starttime = $endtime = $duration  = $intTeamID=0;
  $StartYear = date("Y");
  $StartWeek= date("W");
  $EndYear=  '2035';
  $EndWeek =  '52';
  $dutyName =  '';
  $buttonvalue = 'Create Duty';
  if ($intID > 0) {
      $rsDuty = GetDutyDetailsByID($intID);
      $row = json_decode($rsDuty, true);
      $dutyName = $row['DutyName'] ;
      $starttime = $row['StartTime'];
      $endtime = $row['EndTime'];
      $duration = $row['Duration'];
      $monday = $row['Monday'];
      $tuesday = $row['Tuesday'];
      $wednesday = $row['Wednesday'];
      $thursday = $row['Thursday'];
      $friday = $row['Friday'];
      $saturday = $row['Saturday'];
      $sunday = $row['Sunday'];
      $intTeamID = $row['TeamID'];
      $StartYear = substr($row['StartWeek'], 0, 4);
      $EndYear = substr($row['EndWeek'], 0, 4);
      $StartWeek = substr($row['StartWeek'], 4,2);
      $EndWeek = substr($row['EndWeek'], 4, 2);
      $breakTime = $row['BreakTime'];
    $intDutyColourID = $row['DutyColourID'];
    $strForeColour = $row['ForeColour'];
    $strBackColour = $row['BackColour'];
    $buttonvalue = "Update Duty";
  }else{
    $breakTimehourOptions = PopulateHoursDropDown(1);
    $breakTimeminuteOptions = PopulateMinutesDropDown_limited(0);
  }
 
    echo '<div style="width: 600px">';
    echo '<form id="neweditduty">';
    if($permissions->canview == 1 && $isEdit == 0){
        echo '<table id="dutynewedittable" class="redtable smalltable bluetable" width="100%" role="presentation">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="noBorder" colspan="3">Duty Details</th>';
        echo '<th class="noBorder" colspan="1"><span class="spanFaceBoxClose">×</span></th>';
        echo '</tr>';
        echo '</thead>';
        echo '</table>';
    }

    if ((($permissions->canmodify == 1) || ($permissions->cancreate == 1)) && $isEdit == 1) {
        echo '<table id="dutynewedittable" class="redtable smalltable bluetable" width="100%" role="presentation">';
        echo '<thead>';
        echo '<tr>';
        echo '<th colspan="3" class="noBorder">'. $title = $intID > 0 ? "Edit Master Duty": "New Master Duty" .'</th>';
        echo '<th class="noBorder"><span class="spanFaceBoxClose">×</span></th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
      echo '<tr>';
      echo '<td class="lightblue">Duty Name<span class="required">*</span></td>';
      echo '<td colspan="3">';

      echo '<input id="dutyname" name="dutyname" type="text" size="50" value="'.$dutyName.'"  maxlength="50"  minlength="1">';
      echo "</td>";
      echo '</tr>';
      echo '</tbody>';
      echo '</table>';

      echo '<div id="editdutydatetabs">';
      echo '<ul>';
      echo '<li id="tab_a" onclick=\'javascript:ListDutyDetails(0,'.$intID.','.$intDutyType.')\';><a href="#editdutydatelistdiv" id="dutydetails">Duty Details</a></li>';
      if($intID != 0 || $intID != '0') {
          echo '<li id="tab_d" onclick=\'javascript:ListDutyDetails(1,' . $intID . ',' . $intDutyType . ')\';><a href="#editdutydatelistdiv" id="dutyrotas">Duty Rotas</a></li>';
      }
      echo '</ul>';
        echo '<div id="editdutydatelistdiv">';
        echo '</div>';
      echo '</div>';

    }
    else {
        echo '<table id="dutynewedittable" class="redtable smalltable bluetable" width="100%" role="presentation">';
        echo '<tbody>';
        echo '<tr>';
        echo '<td class="lightblue">Duty Name</td>';
        echo '<td colspan="3">';

        echo '<input id="dutyname" name="dutyname" type="text" size="60" value="'.$dutyName.'" disabled>';
        echo "</td>";
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';

        echo '<div id="editdutydatetabs">';
        echo '<ul>';
        echo '<li id="tab_a" onclick=\'javascript:ListDutyDetails(0,'.$intID.','.$intDutyType.')\';><a href="#editdutydatelistdiv" id="dutydetails">Duty Details</a></li>';
        echo '<li id="tab_d" onclick=\'javascript:ListDutyDetails(1,' . $intID . ',' . $intDutyType . ')\';><a href="#editdutydatelistdiv" id="dutyrotas">Duty Rotas</a></li>';
        echo '</ul>';
        echo '<div id="editdutydatelistdiv">';
        echo '</div>';
        echo '</div>';

    }

    echo '</form>';
    echo '</div>';
}
?>

<script type="text/javascript">

$(document).ready(function(){
    var tabCookieName = "editdutydatetabs";
    $("#editdutydatetabs").tabs({
        active : ($.cookie(tabCookieName) || 0),
        activate : function( event, ui ) {
            var newIndex = ui.newTab.parent().children().index(ui.newTab);
            $.cookie(tabCookieName, newIndex, { expires: 1 });
        }
    });
    var $tabs = $('#editdutydatetabs').tabs();
    var selected = $tabs.tabs('option', 'active');
    var thislisttype = selected;
    var thisdutytype = <?php echo $intDutyType; ?>;

    ListDutyDetails(thislisttype, <?php echo $intID;?>, thisdutytype);

    $('#dutyname').on('keyup', function(){
        let ControlName = "#dutyname";
        let Fieldname= 'Duty Name';
        let TextValid = TextTypeValidation(ControlName,Fieldname);
        if(TextValid == 0){
            return;
        }
    });

  if (<?php echo empty($permissions->canview) ? 0:$permissions->canview ?> == 0) {
    customAlert('You do not have modify privileges for this form.');
  }
  else {
    
    $('#neweditduty').validate({
  	submitHandler: function(form) {
      var dutyname = form.elements["dutyname"].value;
	  var dutyid = form.elements["dutyid"].value;
	  var dutytypeid = form.elements["dutytype"].value;
	  var dutyteam = form.elements["ddldutyteam"].value;
	  var olddutyname = form.elements["olddutyname"].value;
      var starttimemasterduty = form.elements["starttimemasterduty"].value;
      var endtimemasterduty = form.elements["endtimemasterduty"].value;
      if(starttimemasterduty == '' || starttimemasterduty == '0')
      {
        starttimemasterduty =   "00:00";
      }
      if(endtimemasterduty == '' || endtimemasterduty == '0')
      {
        endtimemasterduty =   "00:00";
      }
      var startTimeArr  =   starttimemasterduty.split(':');
      var starthour =   startTimeArr[0];
      var startminute =   startTimeArr[1];
      var endTimeArr  =   endtimemasterduty.split(':');
      var endhour =   endTimeArr[0];
      var endminute =   endTimeArr[1];
      var yearavaialblefrom = form.elements["yearavaialblefrom"].value;
	  var weekavaialblefrom = form.elements["weekavaialblefrom"].value;
      var yearavaialbleto = form.elements["yearavaialbleto"].value;
	  var weekavaialbleto = form.elements["weekavaialbleto"].value;
	  var numsat = form.elements["numsaturday"].value;
	  var numsun = form.elements["numsunday"].value;
	  var nummon = form.elements["nummonday"].value;
	  var numtue = form.elements["numtuesday"].value;
	  var numwed = form.elements["numwednesday"].value;
	  var numthu = form.elements["numthursday"].value;
	  var numfri = form.elements["numfriday"].value;
	  var breakTimeHour = form.elements["breakTimeHour"].value;
    var breakTimeMinute = form.elements["breakTimeMinute"].value;
    var labelIds = $(form.elements["labelIds"]).val();      
	  var dutycolour = form.elements["ddldutycolour"].value;
    var isNeedCovering = form.elements["isNeedCovering"].checked ? 0 : 1;
    var isOverrideOver12 = form.elements["isOverrideOver12"].checked ? 0 : 1;
      
    var strHistory = '';
	  if (dutyid > 0 && olddutyname != dutyname) {

	    var strHistory = '-- Duty Name changed from [' + olddutyname + '] to [' + dutyname + ']';

    }
    //validation start
        //check the validation name
        if(dutyname == ''){
            customAlertByModel('Please enter the duty name');
            return;
        }

		/* Duty  Start time and End Time validation*/
		starthour = parseInt(starthour);
		endhour = parseInt(endhour);
		startminute = parseInt(startminute);
		endminute = parseInt(endminute);

        breakTimeHour = breakTimeHour.length == 1 ? "0" + breakTimeHour : breakTimeHour;
        breakTimeMinute = breakTimeMinute.length == 1 ? "0" + breakTimeMinute : breakTimeMinute;
	    /* check the scheduling team dropdown*/
        if( !$('#ddldutyteam').val() ) {
          customAlertByModel("Select the scheduling team");
          return
        }
        // Validation for Duty name should have a value not spaces
        var ControlName = "#dutyname";
        var Fieldname= 'Duty Name';
        var MinLength = 1;
        var MaxLength = 50;

        var Valid = ValidateText(ControlName,MinLength,MaxLength,Fieldname);
        if(Valid == 0){
            return;
        }
      /** Check the Year Validation */
        var startYear = $("#yearavaialblefrom").val();
        var endYear = $("#yearavaialbleto").val();
        var startWeek = $("#weekavaialblefrom").val();
        var endWeek = $("#weekavaialbleto").val();
      var YearValidation=  StartYearEndYear(startYear,endYear,startWeek,endWeek);
        if(YearValidation == 1){
          return;
        }
    /** End */

	  var thisdutytype = <?php echo $intDutyType == '' ? 1 : $intDutyType; ?>;

	    $.ajax({
		      url: "page-includes/master-duties/save-dutydetails.php",
		      type: "POST",
		      dataType: "json",
		      data: {
                  'DutyID': dutyid,
                  'DutyName':dutyname,
                  'DutyTypeID': dutytypeid,
                  'History': strHistory,
                  'dutyteam': dutyteam,
                  'starthour': starthour,
                  'startminute': startminute,
                  'endhour': endhour,
                  'endminute': endminute,
                  'numsaturday': numsat,
                  'numsunday': numsun,
                  'nummonday': nummon,
                  'numtuesday': numtue,
                  'numwednesday': numwed,
                  'numthursday': numthu,
                  'numfriday': numfri,
                  'dutycolour': dutycolour,
                  'yearavaialblefrom':yearavaialblefrom,
                  'yearavaialbleto': yearavaialbleto,
                  'weekavaialbleto':weekavaialbleto,
                  'weekavaialblefrom':weekavaialblefrom,
                  'breakTimeHour': breakTimeHour,
                  'breakTimeMinute': breakTimeMinute,
                  'labelIds' : labelIds,
                  'isNeedCovering' : isNeedCovering,
                  'isOverrideOver12' : isOverrideOver12
		      },
        success: function(data) {
          if (data.status == 'success') {
              ListMasterDuties(dutytypeid, dutyid, 1);
              $.facebox.close();
          }
          else {
            customAlertByModel(data.sqlstatusstring);
		  }
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


    // Update the Week dropdown on the changes of Year value
      $("#yearavaialblefrom").chosen().change(
          function() {
            var yearAvaialbleFrom = $("#yearavaialblefrom").val();
            var selectId = '#weekavaialblefrom';

            updatedWeekAvailable(yearAvaialbleFrom,selectId);
      });

    // Update the Week dropdown on the changes of Year value
      $("#yearavaialbleto").chosen().change(
            function() {
              var yearAvaialbleTo = $("#yearavaialbleto").val();
              var selectId = '#weekavaialbleto';
              updatedWeekAvailable(yearAvaialbleTo,selectId);
      });

    // Update the Week dropdown on the changes of Year value
      function updatedWeekAvailable(year,selectId){
        let dutyid =  $("#dutyid").val();
        $.post("page-includes/master-duties/getweekdropdown.php", {
            year: year,
            dutyid:dutyid
          },
          function(data){
            // update thw week options
            $(selectId).empty();
            $(selectId).append(data.Weeks);
            $(selectId).trigger("chosen:updated");
          }
        );
      }

    //Refresh the master duties list
    function ListMasterDuties (listtype, id, showedit, strInput){
      var CookieName = "masterdutiesshowarchived";
      var strTeams = $('#ddlRotaTeams').val();
        switch (listtype) {
            case 0:
                tabID = 0;
                $("#MiscDutyTab").removeClass("ui-tabs-active ui-state-active");
                $("#MasterDutyTab").addClass("ui-tabs-active ui-state-active");
                var $tabs = $('#masterdutiestabs').tabs();
                break;

            case 1:
                tabID = 1;
                $("#MiscDutyTab").addClass("ui-tabs-active ui-state-active");
                $("#MasterDutyTab").removeClass("ui-tabs-active ui-state-active");
                var $tabs = $('#masterdutiestabs').tabs();
                break;
        }
        if(listtype == 0){
                $("#divShowJobs").show();
                $.post("page-includes/master-jobs/list-masterduties.php", {
                        id: id,
                        archived: 0,
                        teamId: strTeams,
                        strSearchText: strInput
                    },
                    function(data,status){
                        $('#dutieslistdiv0').html(data);
						$('#duties').scrollTop(Math.abs($.cookie("dutyListingPosTop")));
						$('#duties').scrollLeft(Math.abs($.cookie("dutyListingPosLeft")));
                        GoToRow(id);
                    }
                );

            }
            if(listtype == 1){
                $("#divShowJobs").hide();
                $.post("page-includes/master-duties/ListMiscellaneousDuties.php", {
                        id: id,
                        archived: 0
                    },
                    function(data,status){
                        $('#dutieslistdiv0').html(data);
						$('#duties').scrollTop(Math.abs($.cookie("miscDutyListingPosTop")));
						$('#duties').scrollLeft(Math.abs($.cookie("miscDutyListingPosLeft")));
                        GoToRow(id);
                    }
                );
        }
    }
    function checkStartTime(starthour, startminute)
    {
      if(starthour == 0 && startminute == 0)
      {
        customAlertByModel("Please select start time.");
        $('#endhour').val('0').trigger('chosen:updated');
        $('#endminute').val('0').trigger('chosen:updated');
        return false;
      }
    }
    $(function() {
        $('#starttimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
            });
    });

        $(function() {
        $('#endtimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
            });
    });

    function ListDutyDetails(listtype, dutyid, dutytypeid) {
        var tabCookieName = "editdutydatetabs";
        $.cookie(tabCookieName, listtype, { expires: 0 });

        if (listtype == 0) {
            $("#tab_a").addClass('ui-tabs-active ui-state-active');
            $("#tab_d").removeClass('ui-tabs-active ui-state-active');
            $("#dutynewedittabledetail").removeClass('ui-tabs-active ui-state-active');
            $.post("page-includes/master-duties/edit-masterdutydetails.php", {
                    dutyid: dutyid,
                    dutytypeid: dutytypeid,
                    ddlRotaTeams: <?php echo $ddlRotaTeams == '' ? 0 : $ddlRotaTeams; ?>,
                    isEdit: <?php echo $isEdit == '' ? 0 :$isEdit; ?>
                },
                function(data,status){
                    $('#editdutydatelistdiv').html(data);
                }
            );
        }
        else if (listtype == 1) {
            $("#tab_a").removeClass('ui-tabs-active ui-state-active');
            $("#tab_d").addClass('ui-tabs-active ui-state-active');
            $.post("page-includes/master-duties/edit-masterdutyrotas.php", {
                    dutyid: dutyid,
                    dutytypeid: dutytypeid
                },
                function(data,status){
                    $('#editdutydatelistdiv').html(data);
                }
            );
        }

    }

</script>