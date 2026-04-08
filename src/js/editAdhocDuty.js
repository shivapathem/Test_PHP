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
$(document).ready(function () {
    $(".chosen-select").chosen({no_results_text: "Oops, nothing found!", width: "192px"});
    $(".chosen-select-breaktime").chosen({no_results_text: "Oops, nothing found!", width: "75px"});

    $("#StartTime").on('change', function() {
        if(StartTime.value == 0) {
            setTimeout(function(){ 
                StartTime.value ='00:00'; StartTime.blur(); 
            }, 1000);
        }
    });

    $("#EndTime").on('change', function() {
        if(EndTime.value == 0) {
            setTimeout(function(){ 
                EndTime.value ='00:00'; EndTime.blur(); 
            }, 1000);
        }
    })
});

function calculateDurationAh() {
    var startTime = $('#StartTime').val();
    var endTime = $('#EndTime').val();
    let StartMinute = startTime.length > 3 ? startTime.substring(3, 5) : startTime;
    let EndMinute = endTime.length > 3 ? endTime.substring(3, 5) : endTime;

    if ((parseInt(StartMinute) % 15) != 0) {
        $('#errorDivAhdoc').html("Only 15 Minute intervals are allowed for the Start time.");
        $('#errorDivAhdoc').show();
        $("#StartTime").val(startTime.substring(0,3)+'00');
        $("#StartTime").focus();
        return;
    } else {
        $('#errorDivAhdoc').hide();
    }
    if ((parseInt(EndMinute) % 15) != 0) {
        $('#errorDivAhdoc').html("Only 15 Minute intervals are allowed for the End time.");
        $('#errorDivAhdoc').show();
        $("#EndTime").val(endTime.substring(0,3)+'00');
        $("#EndTime").focus();
        return;
    } else {
        $('#errorDivAhdoc').hide();
    }
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
            $('#errorDivAhdoc').html("Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a different name.");
            $('#errorDivAhdoc').show();
    }
    if ((FlagforDutyName == false) && (Flag == false) && (key != 8)) {
            $(ControlName).val(ControlVal.slice(0, -1));
            $('#errorDivAhdoc').html("Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a different name.Only specified symbols (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) are permitted in " + FieldName + ".");
            $('#errorDivAhdoc').show();
    }
    if (Flag == false) {
        if (key != 8) {
            $(ControlName).val(ControlVal.slice(0, -1));
            $('#errorDivAhdoc').html("Only specified symbols (+ & ! ( ) | [ ] - , . = ( ) * _ : { } ?) are permitted in " + FieldName + ".");
            $('#errorDivAhdoc').show();
        }
    }
}
