$(document).ready(function () {
    document.querySelector("input[auto-focus]").focus();
    colorPickerDropdown('#backcolor', '#fff');
    colorPickerDropdown('#forecolor', '#000');
    colorPickerDropdown('#defaultcolor', '#000');
    $.validator.addMethod("validJob", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9 &+!()|\-\\'\[\],.=()*_:{}?]*$/i.test(value);
    }, "Job name must contain only letters, numbers & few special symbol.");
    $('#newjob').validate({
        rules: {
            "Job": {
                required: true,
                validJob: true,
            },
            "StartTime": {
                required: true,
            },
            "EndTime": {
                required: true,
            },
            "contact": {
                digits: false,
                minlength: 0,
                maxlength: 50,
            },
            "scheduling_team": {
                required: true,
            },
        },
        messages: {
            "Job": {
                required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter a Job Name.' />",
                validJob: 'Job name can be only alphanumeric & few special symbol.',

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
            var startTime = $("#StartTime").val();
            var endTime = $("#EndTime").val();
            var duty_id = $("#duty_id").val();
            var duty_starttime = $("#duty_starttime").val();
            var duty_endtime = $("#duty_endtime").val();
            var job = $("#Job").val();
            var info = $("#info").val();
            var backcolor = $("#backcolor").val();
            var forecolor = $("#forecolor").val();
            var programme_id = $("#programme_id").val();
            var contact = $("#contact").val();
            var location = $("#location").val();
            var schedule_team = $("#schedule_team").val();
            $.ajax({
                type: 'POST',
                dataType: "json",
                async: true,
                url: 'page-includes/master-jobs/check-job-time-before-save.php',
                data: {
                    startTime: startTime,
                    endTime: endTime,
                    duty_id: duty_id,
                    job: job,
                    backcolor: backcolor,
                    forecolor: forecolor,
                    programme_id: programme_id,
                    info: info,
                    contact: contact,
                    location: location,
                    duty_starttime: duty_starttime,
                    duty_endtime: duty_endtime,
                    schedule_team
                },
                success: function (data) {
                    //Before Add
                    if (data.status == 'Before') {
                        var msg = data.msg;
                        var flag = 'Before';
                        //skip confirmbox in case on new job & if blank error msg comes
                        var sure = true;
                        if(msg != '')
                        {
                            customConfirm(msg,function(){
                                    sure = true;
                                    $.ajax({
                                        type: 'POST',
                                        url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                                        async: true,
                                        data: {
                                            startTime: startTime,
                                            endTime: endTime,
                                            duty_id: duty_id,
                                            job: job,
                                            backcolor: backcolor,
                                            forecolor: forecolor,
                                            programme_id: programme_id,
                                            info: info,
                                            contact: contact,
                                            location: location,
                                            flag: flag,
                                            schedule_team
                                        },
                                        success: function (data) {
                                            if (data.status == 0) {
                                                customAlertByModel(data.msg);
                                                return;
                                            }
                                            else {
                                                ListJobs(0, 0);
                                                ListMasterDuties(0, 0);
                                                $.facebox.close();
                                            }
                                        }

                                    });

                                },
                                function(){
                                    sure = false;
                                    return false;
                                }
                            );
                        }else
                        {
                            $.ajax({
                                type: 'POST',
                                url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                                async: true,
                                data: {
                                    startTime: startTime,
                                    endTime: endTime,
                                    duty_id: duty_id,
                                    job: job,
                                    backcolor: backcolor,
                                    forecolor: forecolor,
                                    programme_id: programme_id,
                                    info: info,
                                    contact: contact,
                                    location: location,
                                    flag: flag,
                                    schedule_team
                                },
                                success: function (data) {
                                    if (data.status == 0) {
                                        customAlertByModel(data.msg);
                                        return;
                                    }
                                    else {
                                        ListJobs(0, 0);
                                        ListMasterDuties(0, 0);
                                        $.facebox.close();
                                    }
                                }

                            });
                        }
                    }
                    //End Before
                    // After Add add
                    if (data.status == 'After') {
                        var msg = data.msg;
                        var flag = 'After';
                        //skip confirmbox in case on new job & if blank error msg comes
                        var sure = true;
                        if(msg != '')
                        {
                            customConfirm(msg,function(){
                                    sure = true;
                                    $.ajax({
                                        type: 'POST',
                                        url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                                        async: true,
                                        data: {
                                            startTime: startTime,
                                            endTime: endTime,
                                            duty_id: duty_id,
                                            job: job,
                                            backcolor: backcolor,
                                            forecolor: forecolor,
                                            programme_id: programme_id,
                                            info: info,
                                            contact: contact,
                                            location: location,
                                            flag: flag,
                                            schedule_team
                                        },
                                        success: function (data) {
                                            let Parsedata = JSON.parse(data);
                                            if (Parsedata.status == 0 || Parsedata.status == '0') {
                                                customAlertByModel(Parsedata.msg);
                                                return;
                                            }
                                            else {
                                                ListJobs(0, 0);
                                                ListMasterDuties(0, 0)
                                                $.facebox.close();
                                            }
                                        }
                                    });
                                },
                                function() {
                                    sure = false;
                                    return false;
                                }
                            );
                        }else
                        {
                            $.ajax({
                                type: 'POST',
                                url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                                async: true,
                                data: {
                                    startTime: startTime,
                                    endTime: endTime,
                                    duty_id: duty_id,
                                    job: job,
                                    backcolor: backcolor,
                                    forecolor: forecolor,
                                    programme_id: programme_id,
                                    info: info,
                                    contact: contact,
                                    location: location,
                                    flag: flag,
                                    schedule_team
                                },
                                success: function (data) {
                                    let Parsedata = JSON.parse(data);
                                    if (Parsedata.status == 0 || Parsedata.status == '0') {
                                        customAlertByModel(Parsedata.msg);
                                        return;
                                    }
                                    else {
                                        ListJobs(0, 0);
                                        ListMasterDuties(0, 0)
                                        $.facebox.close();
                                    }
                                }

                            });
                        }
                    }
                    //End After add job

                    if (data.status == 'Conflict') {
                        customAlertByModel(data.msg);
                    }

                    //Case 1 If End time exceed from duties end time amend time in duties and assign to job
                    if (data.status == 'ok') {
                        var flag = 'newrecord';
                        $.ajax({
                            type: 'POST',
                            url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                            async: true,
                            data: {
                                startTime: startTime,
                                endTime: endTime,
                                duty_id: duty_id,
                                job: job,
                                backcolor: backcolor,
                                forecolor: forecolor,
                                programme_id: programme_id,
                                info: info,
                                contact: contact,
                                location: location,
                                flag: flag,
                                schedule_team
                            },
                            success: function (data) {
                                let NewParseData = JSON.parse(data);
                                if (NewParseData.status == 0) {
                                    customAlertByModel(NewParseData.msg);
                                    return;
                                }
                                else {
                                    ListJobs(0, 0);
                                    ListMasterDuties(0, 0)
                                    setTimeout(function(){
                                        highlightJobs(parseInt(NewParseData.masterjobid));
                                    }, 1000);
                                    $.facebox.close();
                                }
                            }

                        });
                    }

                    if (data.status == 'No') {
                        var message = res.msg;
                        var sure = false;
                        customConfirm(message,function(){
                                sure = true;
                                $.ajax({
                                    type: 'POST',
                                    url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                                    async: true,
                                    data: {
                                        startTime: startTime,
                                        endTime: endTime,
                                        duty_id: duty_id,
                                        job: job,
                                        backcolor: backcolor,
                                        forecolor: forecolor,
                                        programme_id: programme_id,
                                        info: info,
                                        contact: contact,
                                        location: location,
                                        schedule_team
                                    },
                                    success: function (data) {
                                        if (data.status == 0) {
                                            customAlertByModel(data.msg);
                                            return;
                                        }
                                        else {
                                            ListJobs(0, 0);
                                            $.facebox.close();
                                        }
                                    }

                                });

                            },
                            function() {
                                sure = false;
                                return false;
                            }
                        );
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    customAlert("Status: " + textStatus);
                    customAlert("Error: " + errorThrown);
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
