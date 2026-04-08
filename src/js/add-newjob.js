$(document).ready(function () {
    $("#facebox").removeClass('customFaceboxClose');
    var selectedTeamId = $('#ddlRotaTeams').val();
    if (selectedTeamId) {
        $("#ddldutyteam").val(selectedTeamId);
        $("#ddldutyteam").prop('disabled', true);
    }
    $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
    colorPickerDropdown('#jobbackcolor', '#ffffff');
    colorPickerDropdown('#jobforecolor', '#000000');
    colorPickerDropdown('#jobdefaultcolor', '#000000');
    $('#jobbackcolor').val('#ffffff');
    $('#jobforecolor').val('#000000');
    $('#jobdefaultcolor').val('#000000');
    $.validator.addMethod("validJob", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9 &+!()|\-\\'\[\],.=()*_:{}?]*$/i.test(value);
    }, "Job name must contain only letters, numbers & few special symbol.");
    $.validator.addMethod("validJobName", function (value, element) {
        return this.optional(element) || !/leave|sick/i.test(value);
    }, "Job name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Job a different name.");
    $('#newjob').validate({
        rules: {
            "Job": {
                required: true,
                validJob: true,
                validJobName:true
            },
            "StartTime": {
                required: true,
            },
            "EndTime": {
                required: true,
            },
            "scheduling_team": {
                required: true,
            },
            "contact": {
                digits: false,
                minlength: 0,
                maxlength: 50,
            }
        },
        messages: {
            "Job": {
                required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter a Job Name.' />",
                validJob: '<br>Job name can be only alphanumeric & few special symbol.',
                validJobName: "<br>Job name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Job a Different Name.",
            },
            "StartTime": {
                required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the start time.' />"
            },
            "EndTime": {
                required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the end time.' />"
            },
            "scheduling_team": {
                required: "Please choose Scheduling team"
            }
        },

        submitHandler: function (form) {
            var ControlName = "#Job";
            var Fieldname = 'Job Name';
            var MinLength = 3;
            var MaxLength = 50;
            var Valid = ValidateText(ControlName, MinLength, MaxLength, Fieldname);

            if (Valid == 0) {
                return;
            }

            /** Check the Year Validation */
            var startTime = form.elements["StartTime"].value;
            var endTime = form.elements["EndTime"].value;
            var teamId = form.elements["scheduling_team"].value;
            if (teamId == '') {
                customAlertByModel("Please choose Scheduling team");
                return
            }
            /** End */
            var TimeValidation = TimeValidationCheck(startTime, endTime);
            if (TimeValidation == 1) {
                return;
            }
            $("#ddldutyteam").prop("disabled", false);
            $.ajax({
                type: 'POST',
                url: 'page-includes/master-jobs/add-job.php',
                data: $('#newjob').serialize(),
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.status != null && data.status == "1") {
                        $("#ddldutyteam").prop("disabled", false);
                        ListJobs(0, 0);
                        setTimeout(function(){
                            highlightJobs(parseInt(data.MasterJobID));
                        }, 1000);
                        $.facebox.close();
                    } else {
                        $("#ddldutyteam").prop("disabled", true);
                        customAlertByModel(data.strstatus);
                    }
                }
            });
        }
    })
});

$(function () {
    $('#StartTime').timepicker({
        'step': 15,
        'timeFormat': 'H:i'
    });
});

$(function () {
    $('#EndTime').timepicker({
        'step': 15,
        'timeFormat': 'H:i'
    });
});

$(function () {
    $("#number")
        .selectmenu()
        .selectmenu("menuWidget")
        .addClass("overflow");
});

function jobsTimeSanitization(timeVal, elementId)
{
    let elementResorce	=	document.getElementById(elementId);
    if(timeVal == '0')
    {
        setTimeout(function(){
            elementResorce.value = '00:00';
            elementResorce.blur();
        }, 1000);
        return true;
    }
    let timeValArr	=	timeVal.split(':');
    if(timeValArr[0] < 24 && timeValArr[1] < 60)
    {

    }else
    {
        elementResorce.value	=	'00:00';
    }
}
