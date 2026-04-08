    function calculateDuration() {
		$('#errorDiv').html('');
		$('#errorDivADaily').html('');
        if($('#StartTime').length == 0) {
            return false;
        }
	    var startTime = $('#StartTime').val();
        var endTime = $('#EndTime').val();
        var breakTimeHour = $('#breakTimeHour').val();
        var breakTimeMinute = $('#breakTimeMinute').val();
        let StartMinute = startTime.length > 3 ? startTime.substring(3, 5) : startTime;
        let EndMinute = endTime.length > 3 ? endTime.substring(3, 5) : endTime;
	    if ((parseInt(StartMinute) % 15) != 0) {
            $('#errorDivADaily').html("Only 15 Minute intervals are allowed for the Start time.");
            $('#errorDivADaily').show();
            $("#StartTime").val(startTime.substring(0,3)+'00');
            $("#StartTime").focus();
            return;
        } else {
            $('#errorDivADaily').hide();
        }
        if ((parseInt(EndMinute) % 15) != 0) {
            $('#errorDivADaily').html("Only 15 Minute intervals are allowed for the End time.");
            $('#errorDivADaily').show();
            $("#EndTime").val(endTime.substring(0,3)+'00');
            $("#EndTime").focus();
            return;
        } else {
            $('#errorDivADaily').hide();
        }

        let dutystarttime = startTime.split(':');
        let startTimeSec = (+dutystarttime[0]) * 60 * 60 + (+dutystarttime[1]) * 60;
        let dutyendtime = endTime.split(':');
        let endTimeSec = (+dutyendtime[0]) * 60 * 60 + (+dutyendtime[1]) * 60;
        let pdlStartTimeSec = (($('#pdlStartTime').val() != '') && ($('#pdlStartTime').val() != undefined)) ? parseInt($('#pdlStartTime').val()) : 0;
        let pdlEndTimeSec = (($('#pdlEndTime').val() != '') && ($('#pdlEndTime').val() != undefined)) ? parseInt($('#pdlEndTime').val()) : 0;
        let checkPdlStartTime = pdlStartTimeSec;
        if(startTimeSec == endTimeSec){
            endTimeSec = parseInt(parseInt(86400) + parseInt(startTimeSec));
        }

        if(pdlStartTimeSec > pdlEndTimeSec){
            pdlEndTimeSec = parseInt(parseInt(86400) + parseInt(pdlEndTimeSec));
        }

        if(startTimeSec > endTimeSec){
            endTimeSec = parseInt(parseInt(86400) + parseInt(endTimeSec));
        }

        if(parseInt(startTimeSec) > parseInt(checkPdlStartTime)){
            checkPdlStartTime = parseInt(86400 + checkPdlStartTime);
        }

        if((checkPdlStartTime < parseInt(startTimeSec)) && (checkPdlStartTime != 0)){
            $('#errorDivADaily').html('Duty start time cannot be later than Part Day Leave start time.');
            $('#errorDivADaily').show();
            $('#updateduty').attr('disabled', true);
        } else if((pdlEndTimeSec > parseInt(endTimeSec)) && (pdlEndTimeSec != 0)){
            $('#errorDivADaily').html('Duty end time cannot be earlier than Part Day Leave end time.');
            $('#errorDivADaily').show();
            $('#updateduty').attr('disabled', true);
        } else if((checkPdlStartTime == parseInt(startTimeSec)) && (pdlEndTimeSec == parseInt(endTimeSec)) && (checkPdlStartTime != 0) && (pdlEndTimeSec != 0)){
            $('#errorDivADaily').html('Duty start and end time cannot be the same as Part Day Leave start and end time. Please change the duty start or end time.');
            $('#errorDivADaily').show();
            $('#updateduty').attr('disabled', true);
        } else {
            let dutyDur = 0;
            let pdlDur = 0;
            let breakDur = 0;
            let durIncBreakTimeAnd15Min = 0;
            let dutyDurExcPdlDur = 0;
            if((pdlStartTimeSec != 0) || (pdlEndTimeSec != 0)){
                dutyDur = parseInt(parseInt(endTimeSec) - parseInt(startTimeSec));
                pdlDur = parseInt(parseInt(pdlEndTimeSec) - parseInt(pdlStartTimeSec));
                breakDur = (+breakTimeHour) * 60 * 60 + (+breakTimeMinute) * 60;
                durIncBreakTimeAnd15Min = parseInt(breakDur+4500);
                dutyDurExcPdlDur = parseInt(dutyDur - pdlDur);
            }

            if(dutyDurExcPdlDur >= durIncBreakTimeAnd15Min){
                $('#errorDivADaily').hide();
                $('#updateduty').attr('disabled', false);
                dataAllocPost = {
                    "action" : "calculateduration",
                    "startTime":startTime,
                    "endTime":endTime,
                    "breakTimeHr":breakTimeHour,
                    "breakTimeMin":breakTimeMinute
                }
                $.post("page-includes/allocations/weekly/actions/check-miscellaneous-duty.php", dataAllocPost).success(function (resp) {
                    resp = $.parseJSON(resp);
                    let btn = $('#updateduty');
                    if(btn.length > 0){
                        btn.removeAttr('type');
                    }
                    if(resp.status!=false){
                        $("#dutyduration").val(resp.duration);
                        $('#dutyduration').prop("readonly", true);
                        if(btn.length > 0){
                            btn.attr('type', 'Submit');
                        }
                    }else{
                        $('#errorDiv').show();
                        $('#errorDiv').html(resp.mess);
                        if(btn.length > 0){
                            btn.attr('type', 'Button');
                        }
                    }
                });
            } else {
                $('#errorDivADaily').html('Duty duration cannot be the same as PDL duration. Please ensure a duty duration of at least 1.25 hrs earlier than the start time or later than the end time of the PDL.');
                $('#errorDivADaily').show();
                $('#updateduty').attr('disabled', true);
            }
        }
    }

    function updateDailyCalculatedDuration(id) {
        let breakTimeHr = $('#breakTimeHour').val();
        let breakTimeMin = $('#breakTimeMinute').val();
        var startTime = $('#StartTime').val();
        if (startTime == '--:--') {
            startTime = '00:00';
        }
        var endTime = $('#EndTime').val();
        if (endTime == '--:--') {
            endTime = '00:00';
        }
        let dutyDuration = $('#dutyduration').val();
        let dutyName = $('#DutyName').val();
        let dataGet = {};

        let dutyDur = 0;
        let pdlDur = 0;
        let breakDur = 0;
        let durIncBreakTimeAnd15Min = 0;
        let dutyDurExcPdlDur = 0;
        let dutystarttime = startTime.split(':');
        let startTimeSec = (+dutystarttime[0]) * 60 * 60 + (+dutystarttime[1]) * 60;
        let dutyendtime = endTime.split(':');
        let endTimeSec = (+dutyendtime[0]) * 60 * 60 + (+dutyendtime[1]) * 60;
        let pdlStartTimeSec = (($('#pdlStartTime').val() != '') && ($('#pdlStartTime').val() != undefined)) ? parseInt($('#pdlStartTime').val()) : 0;
        let pdlEndTimeSec = (($('#pdlStartTime').val() != '') && ($('#pdlStartTime').val() != undefined)) ? parseInt($('#pdlEndTime').val()) : 0;
        let checkPdlStartTime = pdlStartTimeSec;
        if(startTimeSec == endTimeSec){
            endTimeSec = parseInt(parseInt(86400) + parseInt(startTimeSec));
        }

        if(pdlStartTimeSec > pdlEndTimeSec){
            pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
        }

        if(startTimeSec > endTimeSec){
            endTimeSec = parseInt(86400 + endTimeSec);
        }

        if(parseInt(startTimeSec) > parseInt(checkPdlStartTime)){
            checkPdlStartTime = parseInt(86400 + checkPdlStartTime);
        }

        if((pdlStartTimeSec != 0) || (pdlEndTimeSec != 0)){
            dutyDur = parseInt(parseInt(endTimeSec) - parseInt(startTimeSec));
            pdlDur = parseInt(parseInt(pdlEndTimeSec) - parseInt(pdlStartTimeSec));
            breakDur = (+breakTimeHr) * 60 * 60 + (+breakTimeMin) * 60;
            durIncBreakTimeAnd15Min = parseInt(breakDur+4500);
            dutyDurExcPdlDur = parseInt(dutyDur - pdlDur);
        }

        if(dutyDurExcPdlDur >= durIncBreakTimeAnd15Min){
            $('#updateduty').attr('disabled', false);
            if (breakTimeHr != '0' && breakTimeMin == '0') {
                if ((startTime != '00:00' && endTime == '00:00') || (startTime == '00:00' && endTime != '00:00')) {
                    $('#dutyduration').val('00:00');
                    $('#currDurationVal').val('00:00');
                }
                if ((startTime != '0' && endTime != '0') && (startTime != '00:00' && endTime != '00:00')) {
                    Object.assign(dataGet, {
                        "action": "calculateduration",
                        "startTime": startTime,
                        "endTime": endTime
                    });
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                        data: dataGet,
                        success: function (res) {
                            res = $.parseJSON(res);
                            if (res.status) {
                                let updDuration = res.duration;
                                deductDailyBreakTimeFromDuration('updatecalculateddurationbyhr', breakTimeHr, '', updDuration);
                            } else {
                                $('#dutyduration').val('00:00');
                                $('#currDurationVal').val('00:00');
                                $('#errorDiv').html(res.mess);
                                $('#errorDiv').show();
                            }
                        }
                    });
                } else {
                    deductDailyBreakTimeFromDuration('updatecalculatedduration', breakTimeHr, '00', dutyDuration, id, dutyName,startTime,endTime);
                }
            }
            if (breakTimeHr == '0' && breakTimeMin != '0') {
                if ((startTime != '00:00' && endTime == '00:00') || (startTime == '00:00' && endTime != '00:00')) {
                    $('#dutyduration').val('00:00');
                    $('#currDurationVal').val('00:00');
                }
                if ((startTime != '0' && endTime != '0') && (startTime != '00:00' && endTime != '00:00')) {
                    Object.assign(dataGet, {
                        "action": "calculateduration",
                        "startTime": startTime,
                        "endTime": endTime
                    });
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                        data: dataGet,
                        success: function (res) {
                            res = $.parseJSON(res);
                            if (res.status) {
                                let updDuration = res.duration;
                                deductDailyBreakTimeFromDuration('updatecalculateddurationbymin', '', breakTimeMin, updDuration);
                            } else {
                                $('#dutyduration').val('00:00');
                                $('#currDurationVal').val('00:00');
                                $('#errorDiv').html(res.mess);
                                $('#errorDiv').show();
                            }
                        }
                    });
                } else {
                    deductDailyBreakTimeFromDuration('updatecalculatedduration', '00', breakTimeMin, dutyDuration, id, dutyName,startTime,endTime);
                }
            }
            if (breakTimeHr != '0' && breakTimeMin != '0') {
                if ((startTime != '00:00' && endTime == '00:00') || (startTime == '00:00' && endTime != '00:00')) {
                    $('#dutyduration').val('00:00');
                    $('#currDurationVal').val('00:00');
                }
                if ((startTime != '0' && endTime != '0') && (startTime != '00:00' && endTime != '00:00')) {
                    Object.assign(dataGet, {
                        "action": "calculateduration",
                        "startTime": startTime,
                        "endTime": endTime
                    });
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                        data: dataGet,
                        success: function (res) {
                            res = $.parseJSON(res);
                            if (res.status) {
                                let updDuration = res.duration;
                                deductDailyBreakTimeFromDuration('updatecalculateddurationbyhrmin', breakTimeHr, breakTimeMin, updDuration);
                            } else {
                                $('#dutyduration').val('00:00');
                                $('#currDurationVal').val('00:00');
                                $('#errorDiv').html(res.mess);
                                $('#errorDiv').show();
                            }
                        }
                    });
                } else {
                    deductDailyBreakTimeFromDuration('updatecalculatedduration', breakTimeHr, breakTimeMin, dutyDuration, id, dutyName,startTime,endTime);
                }
            }
            if (breakTimeHr == '0' && breakTimeMin == '0') {
                calculateDuration(0);
            }
        } else {
            $('#errorDivADaily').html('Duty duration cannot be the same as PDL duration. Please ensure a duty duration of at least 1.25 hrs earlier than the start time or later than the end time of the PDL.');
            $('#errorDivADaily').show();
            $('#updateduty').attr('disabled', true);
        }
    }



function deductDailyBreakTimeFromDuration(actionName, breakHr, breakMin, Duration, id, DutyName,startTime='', endTime='') {
    let dataPost = {};
    if (actionName == 'updatecalculateddurationbyhr') {
        Object.assign(dataPost, {
            "action": actionName,
            "breakHr": breakHr,
            'Duration': Duration
        });
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
            data: dataPost,
            success: function (resPost) {
                resPost = $.parseJSON(resPost);
                if (resPost.status) {
                    if($('#errorDiv').html() != ''){
                        $('#errorDiv').html('');
                        $('#errorDiv').hide();
                    }
                    $('#dutyduration').val(resPost.duration);
                    $('#currDurationVal').val(resPost.duration);
                } else {
                    $("#breakTimeHour").val('0').trigger("chosen:updated");
                    $('#dutyduration').val(Duration);
                    $('#currDurationVal').val(Duration);
                    $('#errorDiv').html(resPost.mess);
                    $('#errorDiv').show();
                }
            }
        });
    }

    if (actionName == 'updatecalculateddurationbymin') {
        Object.assign(dataPost, {
            "action": actionName,
            "breakMin": breakMin,
            'Duration': Duration
        });
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
            data: dataPost,
            success: function (resPost) {
                resPost = $.parseJSON(resPost);
                if (resPost.status) {
                    if($('#errorDiv').html() != ''){
                        $('#errorDiv').html('');
                        $('#errorDiv').hide();
                    }
                    $('#dutyduration').val(resPost.duration);
                    $('#currDurationVal').val(resPost.duration);
                } else {
                    $("#breakTimeHour").val('0').trigger("chosen:updated");
                    $('#dutyduration').val(Duration);
                    $('#currDurationVal').val(Duration);
                    $('#errorDiv').html(resPost.mess);
                    $('#errorDiv').show();
                }
            }
        });
    }

    if (actionName == 'updatecalculateddurationbyhrmin') {
        Object.assign(dataPost, {
            "action": actionName,
            "breakHr": breakHr,
            "breakMin": breakMin,
            'Duration': Duration
        });
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
            data: dataPost,
            success: function (resPost) {
                resPost = $.parseJSON(resPost);
				var btn = document.getElementById('updateduty');
				btn.removeAttribute('type');
                if (resPost.status) {
                    if($('#errorDiv').html() != ''){
                        $('#errorDiv').html('');
                        $('#errorDiv').hide();
                    }
                    $('#dutyduration').val(resPost.duration);
                    $('#currDurationVal').val(resPost.duration);
					btn.setAttribute('type', 'Submit');
                } else {
                    $("#breakTimeHour").val('0').trigger("chosen:updated");
                    $('#dutyduration').val(Duration);
                    $('#currDurationVal').val(Duration);
                    $('#errorDiv').html(resPost.mess);
                    $('#errorDiv').show();
					btn.setAttribute('type', 'Button');
                }
            }
        });
    }

    if (actionName == 'updatecalculatedduration') {
        Object.assign(dataPost, {
            "action": actionName,
            "breakHr": breakHr,
            "breakMin": breakMin,
            'Duration': Duration,
            'id': id,
            'DutyName': DutyName,
			'startTime': startTime,
            'endTime': endTime
		});
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
            data: dataPost,
            success: function (resPost) {
                resPost = $.parseJSON(resPost);
				var btn = document.getElementById('updateduty');
				btn.removeAttribute('type');
			    if (resPost.status) {
                    if($('#errorDiv').html() != ''){
                        $('#errorDiv').html('');
                        $('#errorDiv').hide();
                    }
                    $('#dutyduration').val(resPost.duration);
                    $('#currDurationVal').val(resPost.duration);
					btn.setAttribute('type', 'Submit');
                } else {
                    $("#breakTimeHour").val('0').trigger("chosen:updated");
                    $('#dutyduration').val(Duration);
                    $('#currDurationVal').val(Duration);
                    $('#errorDiv').html(resPost.mess);
                    $('#errorDiv').show();
					btn.setAttribute('type', 'Button');
                }
            }
        });
    }
}

function setDuration() {
    let getDurationVal = $('#dutyduration').val();

    if(getDurationVal == 0) {
        $('#dutyduration').val("00:00");
        $('#currDurationVal').val("00:00");
    }
    let getDurationValMinute = getDurationVal.length > 3 ? getDurationVal.substring(3, 5) : getDurationVal;
    if ((parseInt(getDurationValMinute) % 15) != 0) {
        $('#errorDiv').html("Only 15 Minute intervals are allowed for the Duration.");
        $('#errorDiv').show();
        $("#dutyduration").val(getDurationVal.substring(0,3)+'00');
        $('#currDurationVal').val(getDurationVal.substring(0,3)+'00');
        $("#dutyduration").focus();
        return;
    } else {
        $('#errorDiv').hide();
        $('#currDurationVal').val(getDurationVal);
    }
}