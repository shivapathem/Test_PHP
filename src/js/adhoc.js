$(document).ready(function(){
  $(".closeAdhoc").on('click', function() {
    $("#newjob").hide();
  });

  callDatePicker('#addteamstartdate');
  callDatePicker('#addteamenddate');
  
  // on load show day and  week based on date --START
  var startVal = $('#addteamstartdate').val();
  var endVal = $('#addteamenddate').val();
  setDateDayAndWeek(startVal,endVal);
  // on load show day and  week based on date --START

  var selectedTeamId = $('#ddlRotaTeams').val();
  if(selectedTeamId){
    $("#ddldutyteam").val(selectedTeamId);
    $("#ddldutyteam").prop('disabled', true);
  }

  $(".chosen-select").chosen({no_results_text: "Oops, nothing found!", width: "192px"});
  $(".chosen-select-breaktime").chosen({no_results_text: "Oops, nothing found!", width: "75px"});
  colorPickerDropdown('#jobbackcolor', '#ffffff');
  colorPickerDropdown('#jobforecolor', '#000000');  
  colorPickerDropdown('#jobdefaultcolor', '#000000');  
  $('#jobbackcolor').val('#ffffff');
  $('#jobforecolor').val('#000000');  
  $('#jobdefaultcolor').val('#000000');

  $.validator.addMethod("validJob", function(value, element) {
    return this.optional(element) || /^[a-zA-Z0-9 &+!()|\\-\\'\[\]]*$/i.test(value);
  }, "Name must contain only letters, numbers & few special symbol.");

  $.validator.addMethod('validStartTime', function(value, el, param) {
    let ast = value.length > 3 ? value.substring(3, 5) : value;
    return (parseInt(ast) % 15) == 0 ? true :false;
  }, "</br/>Only 15 Minute intervals are allowed for the Start time.");

  $.validator.addMethod('validEndTime', function(value, el, param) {
    let aet = value.length > 3 ? value.substring(3, 5) : value;
    return ((parseInt(aet) % 15) == 0) ? true : false;
  }, "</br/>Only 15 Minute intervals are allowed for the End time.");

  $('#newjob').validate({
    rules:{
      "Job":{
        required:true,
        validJob:true,
      },             
      "StartTime":{
        required: true,
        validStartTime: true
      },
      "EndTime":{
        required:true,
        validEndTime: true
      },
       "scheduling_team":{
        required:true,
      }            
    },
      messages:{       
        "StartTime":{
          required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the start time.' />"
        },          
        "EndTime":{
          required:"<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the end time.' />"
        },
        "scheduling_team":{
          required:"Please choose Scheduling team"
        }
      },
	  
      submitHandler: function(form) {
        var staffId = $("#staffOption").val();
        var startDate = $('#addteamstartdate').val();
        var startDateDay = $('#startDateDay').html();
        var startDateWeek =$('#startDateWeek').html();
        var endDate = $('#addteamenddate').val();
        var endDay = $('#endDateDay').html();
        var endWeek =$('#endDateWeek').html();				
        var startTime = $('#StartTime').val();
        var endTime = $('#EndTime').val();		
        var dutyName = $('#dutyname').val();
        var schedulingTeamId = $('#ddldutyteam').val(); 
        var comment = $('#commentBox').val();
        var dutyColorId = $('#ahdutycolour').val(); 
        var breakTimeHour = $('#breakTimeHour').val(); 
        var breakTimeMinute = $('#breakTimeMinute').val();
        var isNeedCovering = $('#isNeedCovering').is(":checked") ? 0 : 1;
        var intStartTime = 0;
        var intEndTime = 0;

        var startTimeArray = startTime.split(":");
        if (startTimeArray.length == 2) {
            intStartTime = startTimeArray[0] * 3600 + startTimeArray[1] * 60;
        }
        var endTimeArray = endTime.split(":");
        if (endTimeArray.length == 2) {
          intEndTime = endTimeArray[0] * 3600 + endTimeArray[1] * 60;
        }
          
        if(schedulingTeamId == '0')
        {
          customAlert("Please choose Scheduling team");
          return
        }
		
		    if(staffId == '0')
        {
          customAlert("Please choose Staff Name");
          return
        }

      //date validation
      dateTokens = startDate.split('-');
      startdt = new Date(dateTokens[2], parseInt( dateTokens[1], 10 ) - 1, dateTokens[0]);
      dateTokens = endDate.split('-');
      enddt = new Date(dateTokens[2], parseInt( dateTokens[1], 10 ) - 1, dateTokens[0]);
      if (startdt > enddt) {
        customAlert('Please ensure the End Date is greater than the Start Date.');
        return
      }
    /** End */

    $.ajax({
			type:'POST',
			url: 'page-includes/allocations/add-adhoc.php', 
			data:{
				"task":"addAdhocJob",
        "StaffID": staffId,
        "StartDate": startDate,
        "EndDate": endDate,
				"StartTime":intStartTime,
				"EndTime":intEndTime,
				"DutyName":dutyName,
				"SchedulingTeamID":schedulingTeamId,
				"Comment":comment,
        "DutyColorID": dutyColorId,
        "breakTimeHour": breakTimeHour,
        "breakTimeMinute": breakTimeMinute,
        "isNeedCovering": isNeedCovering
      },
        success: function (data) {
          if (data == 'BreakTime-Error') {
            customAlert("Breaktime cannot be greater than duration");
          }
          if (data == 'Allocations-Exist-Error') {
            customAlert("Adhoc Duty can only be created for the weeks which are not created yet.");
          }
          if (data == 'StaffID-DutyName-Error') {
            customAlert("Adhoc Duty with same name cannot be created for a person if that person already has an adhoc duty with same name for a day/days.");
          }
          
          if(data == 'SUCCESS'){
            customAlert('Ad Hoc Duty added successfully.');
            resetForm();
          }		
        }
      });
      }
    })
});

function resetForm(){
  document.getElementById("newjob").reset();	
  $('#startDateDay').html("");
	$('#startDateWeek').html("");
	$('#endDateDay').html("");
	$('#endDateWeek').html("");	
  $('#staffOption').val('');
	$('#ddldutyteam').val('');	
	$('#ahdutycolour').val('');	
  $('select.chosen-select.chosen-select-breaktime').val('').trigger("chosen:updated");
  $('#ddldutyteam').val('0').trigger("chosen:updated");
  $('#breakTimeHour').val('1').trigger("chosen:updated");
  $('#breakTimeMinute').val('15').trigger("chosen:updated");

  var stele = document.getElementById('staffOption');
  stele.innerHTML = '<option value="0">Select Staff</option>';
  $('#staffOption').val('0').trigger("chosen:updated");

  var adele = document.getElementById('ahdutycolour');
  adele.innerHTML = '<option value="0">Select The Colour</option>';
  $('#ahdutycolour').val('0').trigger("chosen:updated");

  callDatePicker('#addteamstartdate');
  callDatePicker('#addteamenddate');
  setStartDateDayAndWeek($('#addteamstartdate').val());
}

$(function() {
  $('#StartTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

$(function() {
  $('#EndTime').timepicker({
    'step': 15,
    'timeFormat': 'H:i'
    });
});

function callDatePicker(dateControlName) {
  $(dateControlName).datepicker({
    changeMonth: true,
    changeYear: true,
    dateFormat: "dd-mm-yy",
    inline: true,
    minDate: new Date('31-12-1995'),
    maxDate: new Date('31-12-9999')
  });
  $(dateControlName).datepicker('setDate', 'today');
  $(dateControlName).datepicker('option', 'firstDay', 6);
}

function setDateDayAndWeek(startVal,endVal){
  datesTokens = startVal.split('-');
  var startFormatVal = datesTokens[2]+'-'+datesTokens[1]+'-'+datesTokens[0];
  getWeekandDay(startFormatVal, 'both');
}

function setStartDateDayAndWeek(val){
  dateTokens = val.split('-');
  var startFormatVal = dateTokens[2]+'-'+dateTokens[1]+'-'+dateTokens[0];
  $('#addteamenddate').val(dateTokens[0]+'-'+dateTokens[1]+'-'+dateTokens[2]);
  getWeekandDay(startFormatVal, 'both');
  populateStaffName();
}

function setEndDateDayAndWeek(val){
  dateTokens = val.split('-');
  var endFormatVal = dateTokens[2]+'-'+dateTokens[1]+'-'+dateTokens[0];
  getWeekandDay(endFormatVal, 'end');
  populateStaffName();
}

function getWeekandDay(seldate, type = 'start') {
  $.ajax({
    type: "POST",
    url: 'page-includes/allocations/add-adhoc.php',
    data: { task: "adGetWeekNumber", seldate: seldate },
    success: function (obj, textstatus) {
      var jsondata = $.parseJSON(obj); 
      if(jsondata) {
        if(type == 'start') {
          $("#startDateWeek").text(jsondata.weekNumber);
          $("#startDateDay").text(jsondata.sDayName);
        } else if(type == 'both') { 
          $("#startDateWeek").text(jsondata.weekNumber);
          $("#startDateDay").text(jsondata.sDayName);
          $("#endDateWeek").text(jsondata.weekNumber);
          $("#endDateDay").text(jsondata.sDayName);
        } else {
          $("#endDateWeek").text(jsondata.weekNumber);
          $("#endDateDay").text(jsondata.sDayName);
        }
      }
    }
  });
}

function getmastercolors(teamId) {
  var dutyColourID = $("#ahdutycolour").val();
  $.ajax({
    url: 'page-includes/master-duties/get-masterdutycolors.php',
    dataType: "json",
    type: 'POST',
    data: { teamId: teamId, dutyColourID : dutyColourID },
    success: function (data) {
      if(data) {
        if (data.status == 'success') {
          $('select.chosen-select options:contains("Swatch 1")');
          $('#ahdutycolour').html(data.ColourOptions);
          $('select.chosen-select').trigger("chosen:updated");
        } else {
            var adele = document.getElementById('ahdutycolour');
            adele.innerHTML = '<option value="0">Select The Colour</option>';
            $('#ahdutycolour').val('0').trigger("chosen:updated");
        }
      } else {
        var adele = document.getElementById('ahdutycolour');
        adele.innerHTML = '<option value="0">Select The Colour</option>';
        $('#ahdutycolour').val('0').trigger("chosen:updated");
      }
      
    }
  });
}

function populateStaffName() {
  var startDate = $('#addteamstartdate').val();
  var endDate = $('#addteamenddate').val();
  var startTime = $('#StartTime').val();
  var endTime = $('#EndTime').val();		
  var schedulingTeamId = $('#ddldutyteam').val();

  if(schedulingTeamId != '0') {
    getmastercolors(schedulingTeamId);
  }
  if((schedulingTeamId == '0') && (startDate == '') && (endDate == '')) {
    return
  }

  var startDateTokens = startDate.split('-');
  var formatStartDate = '';
  formatStartDate = startDateTokens[2]+'-'+startDateTokens[1]+'-'+startDateTokens[0];

  var endDateTokens = endDate.split('-');
  var formatEndDate = '';
  formatEndDate = endDateTokens[2]+'-'+endDateTokens[1]+'-'+endDateTokens[0];
  
  $.ajax({
    type:'POST',
    dataType: "json",
    url: 'page-includes/allocations/add-adhoc.php', 
    data:{
      task:"getStaffTeam",
      startDate:formatStartDate,
      endDate:formatEndDate,
      schedulingTeamId:schedulingTeamId
    }, 
    success: function(data) {
      if(data.status == 'success')
      { 
        $('select.chosen-select options:contains("Swatch 1")');
        var ele = document.getElementById('staffOption');
        ele.innerHTML = '<option value="0">Select Staff</option>';
        for (var i = 0; i < data.result.length; i++) {
          ele.innerHTML = ele.innerHTML +
                  '<option value="' + data.result[i]['ScheduledPersonID'] + '">' + data.result[i]['FullName'] + '</option>';
        }
        $('select.chosen-select').trigger("chosen:updated");
      }	 
    }
  });
}

/* This function is used to validate the duty name it only allow some special characters (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) and restrict some duty names (leave/sick)*/
function dutyNameValidation(ControlName, FieldName, event) {
    var ControlVal = $(ControlName).val();
    var RGEXToCheck = /^([a-zA-Z0-9+&!()|\[\]\- ,.=()*_:{}?]{0,500})$/;
    var key = window.event.keyCode;
    var Flag = RGEXToCheck.test(ControlVal);
    var FlagforDutyName=!/leave|LEAVE|Leave|sick|SICK|Sick/i.test(ControlVal);
    
    if ((FlagforDutyName == false) && (Flag != false)) {
            $(ControlName).val(ControlVal.slice(0, -1));
            customAlert("Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a different name.");
    } 
    if ((FlagforDutyName == false) && (Flag == false) && (key != 8)) {
            $(ControlName).val(ControlVal.slice(0, -1));
            customAlert("Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a different name.Only specified symbols (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) are permitted in " + FieldName + ".");
    }
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val(ControlVal.slice(0, -1));
            customAlert("Only specified symbols (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) are permitted in " + FieldName + ".");
        }
    }
}