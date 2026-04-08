$(document).ready(function() {
    $("#allocations-container").height($(window).height() - 107);
    showHideCountGrid();
    if($('#teamId').val() != undefined){
        let miscDutyChkBox = $.cookie('showMiscDutyFilter'+$('#teamId').val());
        if((miscDutyChkBox != undefined) && (miscDutyChkBox == 'showFilter')){
            checkMiscDutyFilterBox('showFilter');
        } else {
            checkMiscDutyFilterBox();
        }
    }
    $('#reasonerror').hide();
    $('#durationerror').hide();

    $(".select-content").on("click", function() {
        $(this).select();
    });
	removetip();
    let getDisableDrag = $.cookie('dragdisable'+$('#teamId').val());
    if(getDisableDrag != undefined){
        $('#disableDrag').prop('checked',true);
        disableDragDrop();
    } else {
        $('#disableDrag').prop('checked',false);
    }
    let savedPerson = getHighlightRowScheduledPersonCookie();
    if(savedPerson > 0){
        $('#personRowHighlight').html('');
        highlightRowScheduledPerson(savedPerson);
   }
});



function viewAdhocDutyHeaderSettings() {
    $("#isAdhocAction").val('1');
    $("#publishWeekIconDiv").css('pointer-events', 'none');
    $(".filter-container").css('pointer-events', 'none');
    $('#filterName').html('<span title="Filters"> Filters</span>');
    $("#date, #disableDrag, #showHideCount, #dityCountBtn").prop('disabled', true);
    $("#adhocFilter").attr('disabled', 'disabled');
    $("#allocations-filters, .filterboxposition").attr('disabled', 'disabled');
    $("#showWeeks, #mastMiscFilterId, #dutyShiftCountingFilterId").prop('disabled', true).trigger("chosen:updated");
    $("#contextViewInfoIcons, #removeweek").unbind('click');
}

function initDatePicker() {
    $(".date-picker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        inline: true,
        firstDay: 6,
        beforeShow: function() {
            setTimeout(function(){
                $('.ui-datepicker').css('z-index', 999);
            }, 0);
        }
    }).change(function(selected) {
        $('#loading').show();
        selected.preventDefault();
        selected.stopImmediatePropagation();
        $('#showWeeks').prop('disabled', true).trigger("chosen:updated");
        var postReq = {
            'actionname' : 'getdateweeknumber',
            'date' : $('#date').val(),
			'teamId' : $('#searchTeamId').val()
        };
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/check-week.php",
            data: postReq,
            beforeSend: function(jqXHR, settings){
                $('#loading').hide();
            },
            success: function (response) {
                response = $.parseJSON(response);
			    if (response.success) {
                    $('#getWeekNumber').val(response.dateWeek);
                    $("#publishWeekIconDiv").css('pointer-events', 'all');
					$('#allocSortDate').val("NAME");
					$('#allocSortType').val(0);
                    if($('#showWeeks').val() > 1){
                        $('#showWeeks').val(1).trigger("chosen:updated");
                    }
                    $('#weekNumber').val(response.dateWeek);
                    $('#wprev').attr('data-week',response.prevWeek);
                    $('#wnext').attr('data-week',response.nextWeek);
                    $('#weekStartDate').val(response.weekStartDate);
                    if(response.weekexists == 'No'){
                        $("#editWeeklyAllocations").submit();
                    } else {
                        if(response.openWeek == 'Yes'){
                            customConfirm(response.errormessage,function(){
                                    $('#getWeekNumber').val(response.dateWeek);
                                        reloadeditweeklygrid('','','','','ALL');
                                    },
                                function() {
                                }
                            );
                            $('#yes').val('OK');
                            $('#no').hide();
                        } else {
                            reloadeditweeklygrid('','','','','ALL');
                        }
                    }
                } else {
                    if(response.openWeek == 'Yes'){
                        customConfirm(response.errormessage,function(){
                                $('#getWeekNumber').val(response.dateWeek);
                                $("#publishWeekIconDiv").css('pointer-events', 'all');
                                $('#allocSortDate').val("NAME");
                                $('#allocSortType').val(0);
                                if($('#showWeeks').val() > 1){
                                    $('#showWeeks').val(1).trigger("chosen:updated");
                                }
                                $('#weekNumber').val(response.dateWeek);
                                $('#wprev').attr('data-week',response.prevWeek);
                                $('#wnext').attr('data-week',response.nextWeek);
                                $('#weekStartDate').val(response.weekStartDate);
                                if(response.weekexists == 'No'){
                                    $("#editWeeklyAllocations").submit();
                                } else {
                                    reloadeditweeklygrid('','','','','ALL');
                                }
                            },
                            function() {
                            }
                        );
                        $('#yes').val('OK');
                        $('#no').hide();
                    } else {
                        customAlert(response.errormessage);
                        $('#date').val('');
                    }
                }
            }
        });
    });
}

$(document).ready(function() {
    $('#facebox .close')
        .click($.facebox.close).empty().append('<img src="' +$.facebox.settings.closeImage +'" class="close_image" title="close">');

    initDatePicker();
    $('#loading').hide();
    $('.weekAction').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var weekVal = $(this).attr('data-week');
        let multiweekvalue = $('#showWeeks').val();
        let isAdhocAction = 0;
        $('#Choosedate').val('');
        $('#loading').show();
        let checkNextBtnAction = $(this).hasClass('wnext');
        let checkPrevBtnAction = $(this).hasClass('wprev');
        $('#showWeeks').prop('disabled', false).trigger("chosen:updated");

        isAdhocAction = $("#isAdhocAction").val();

        if(isAdhocAction == 1) {
            $('#weekNumber').val(weekVal);
            $("#isAdhocAction").val('0');
            $.ajax({
                type: "post",
                url: "/components/filters/filter-process.php",
                data: {
                    'action':'checkselectedfilter',
                    'teamId':$("#searchTeamId").val()
                },
                beforeSend: function(jqXHR, settings){
                    $('#loading').hide();
                },
                success: function (response) {
                    let returnData = $.parseJSON(response);
                    let argDataInitialLoad = {};
                    argDataInitialLoad.schedulingTeamId = $("#searchTeamId").val();
                    argDataInitialLoad.userId = $("#userId").val();
                    argDataInitialLoad.weekNumber = $('#weekNumber').val();
                    argDataInitialLoad.teamId = $("#searchTeamId").val();
                    if(returnData.status == true){
                        argDataInitialLoad.selAutoPageFilterId = returnData.selectedFilterId;
                        argDataInitialLoad.shiftCountingFilterId = returnData.selectedShiftCountFilterId;
                    } else {
                        argDataInitialLoad.selAutoPageFilterId = 0;
                        argDataInitialLoad.shiftCountingFilterId = 0;
                    }
                    editWeeklyPageLoad('No',argDataInitialLoad);
                }
            });
        } else {
            var postReq = {
                'actionname' : 'getweeks',
                'weekNumber' : weekVal,
                'teamId' : $('#searchTeamId').val(),
                'checkValidity' : 'Yes'
            };
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/check-week.php",
                data: postReq,
                beforeSend: function(jqXHR, settings){
                    $('#loading').hide();
                },
                success: function (response) {
                    response = $.parseJSON(response);
                    if(response.errormessage != undefined){
                        if(response.openWeek == 'Yes'){
                            customConfirm(response.errormessage,function(){
                                        $("#publishWeekIconDiv").css('pointer-events', 'all');
                                        $('#weekNumber').val(weekVal);
                                        $('#getWeekNumber').val(weekVal);
                                        $(".date-picker").datepicker('setDate', null);
                                        $("#allocations-container").attr('data-is-week-published',response.weekPublish);
                                        if (response.success) {
                                            if($('#showWeeks').val() > 1){
                                                if(response.weekExist == 'Yes'){
                                                    $.cookie('cookieWeekNumber', $('#weekNumber').val());
                                                }
                                                if(checkPrevBtnAction){
                                                    loadMultiweek('PREV');
                                                } else {
                                                    loadMultiweek('NEXT');
                                                }
                                            } else {
                                                var gridLoadData = $.parseJSON($('#ajaxLoadParams').val());
                                                gridLoadData.endDate = response.weekEndDate;
                                                gridLoadData.endWeek = response.nextWeekNumStr;
                                                gridLoadData.endWeekDate = response.weekEndDate;
                                                gridLoadData.startDate = response.weekStartDate;
                                                gridLoadData.startWeek = response.currWeekNumStr;
                                                gridLoadData.startWeekDate = response.weekStartDate;
                                                gridLoadData.weekNumber = weekVal;
                                                var encLoadDate = JSON.stringify(gridLoadData);
                                                $('#ajaxLoadParams').val(encLoadDate);
                                                if(response.weekExist == 'Yes'){
                                                    $.cookie('cookieWeekNumber', $('#weekNumber').val());
                                                }
                                                reloadeditweeklygrid('','','','','ALL');
                                            }
                                            $('#wprev').attr('data-week',response.prevWeekNum);
                                            $('#wnext').attr('data-week',response.nextWeekNum);
                                        } else {
                                            var request = $("#editWeeklyAllocations").serialize();
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/weekly/period-view.php",
                                                data: request,
                                                beforeSend: function(jqXHR, settings){
                                                    $('#loading').hide();
                                                },
                                                success: function (data) {
                                                    $('#content').html(data);
                                                }
                                            });
                                        }
                                        if($('#disableDrag').prop('checked') == true){
                                            disableDragDrop();
                                        }
                                },
                                function() {
                                }
                            );
                            $('#yes').val('OK');
                            $('#no').hide();
                        } else {
                            customAlert(response.errormessage);
                            if($('#date').val() == ''){
                                let ajaxParams = $.parseJSON($('#ajaxLoadParams').val());
                                $('#weekNumber').val($('#weekNumber').val());
                            }
                        }
                    } else {
                        $('#getWeekNumber').val(weekVal);
                        $("#publishWeekIconDiv").css('pointer-events', 'all');
                        $('#weekNumber').val(weekVal);
                        $(".date-picker").datepicker('setDate', null);
                        $("#allocations-container").attr('data-is-week-published',response.weekPublish);
                        if (response.success) {
                            if($('#showWeeks').val() > 1){
                                if(response.weekExist == 'Yes'){
                                    $.cookie('cookieWeekNumber', $('#weekNumber').val());
                                }
                                if(checkPrevBtnAction){
                                    loadMultiweek('PREV');
                                } else {
                                    loadMultiweek('NEXT');
                                }
                            } else {
                                var gridLoadData = $.parseJSON($('#ajaxLoadParams').val());
                                gridLoadData.endDate = response.weekEndDate;
                                gridLoadData.endWeek = response.nextWeekNumStr;
                                gridLoadData.endWeekDate = response.weekEndDate;
                                gridLoadData.startDate = response.weekStartDate;
                                gridLoadData.startWeek = response.currWeekNumStr;
                                gridLoadData.startWeekDate = response.weekStartDate;
                                gridLoadData.weekNumber = weekVal;
                                var encLoadDate = JSON.stringify(gridLoadData);
                                $('#ajaxLoadParams').val(encLoadDate);
                                if(response.weekExist == 'Yes'){
                                    $.cookie('cookieWeekNumber', $('#weekNumber').val());
                                }
                                reloadeditweeklygrid('','','','','ALL');
                            }
                            $('#wprev').attr('data-week',response.prevWeekNum);
                            $('#wnext').attr('data-week',response.nextWeekNum);
                        } else {
                            var request = $("#editWeeklyAllocations").serialize();
                            $.ajax({
                                type: "post",
                                url: "/page-includes/allocations/weekly/period-view.php",
                                data: request,
                                beforeSend: function(jqXHR, settings){
                                    $('#loading').hide();
                                },
                                success: function (data) {
                                    $('#content').html(data);
                                }
                            });
                        }
                        if($('#disableDrag').prop('checked') == true){
                            disableDragDrop();
                        }
                    }
                }
            });
        }
    });

    $('#weekNumber').on('click', function(e) {
        $('#weekNumber').select();
    });

    var dialogForm = $("#dialog-form");

    $('#allocationPublish').on('click', function(e) {
        if($('#editScreen').val() == 1){
            e.preventDefault();
            e.stopImmediatePropagation();
            var screenName = $('#dailyAllocationspublish').find('input[name="screenName"]').val();
            if (screenName=='ViewDaily'){
                var isPublished = $("#allocations-container_daily").attr('data-is-week-published');
            } else {
                var isPublished = $("#allocations-container").attr('data-is-week-published');
            }
            if (isPublished == 1) {
                showPublishWeekModal();
            } else {
                customConfirmModal(
                    'This week has not been published before.',
                    function(){
                        showPublishWeekModal();
                    },
                    function(){},
                    0,
                    'EditWeeklyPublish'
                );
            }
        }
    });

    $(document).on('click', '#createWeek', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('#loading').show();
        var createweekdata = $("#createWeekForm").serializeArray();
        var create_week_array = {};
        $.map(createweekdata, function(n, i){
            create_week_array[n['name']] = n['value'];
        });
	    var request = $("#editWeeklyAllocations").serialize();
	    $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/create-week.php",
            data: request,
            success: function (response) {
                response = $.parseJSON(response);
				if (response.success) {
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/period-view.php",
                        data: request,
                        success: function (data) {
							$('#content').html(data);
                            if(parseInt(create_week_array.selAutoPageFilterId) > 0){
                                applyViewFilter('EditWeekly',create_week_array.selAutoPageFilterId,'Yes');
                            }
                            if(parseInt(create_week_array.shiftCountingFilterId) > 0){
                                applyDutyShiftCountingFilter(create_week_array.userId, create_week_array.schedulingTeamId, create_week_array.shiftCountingFilterId);
                            }
                            if($('#disableDrag').prop('checked') == true){
                                disableDragDrop();
                            }
                        }
                    });
                }
                $('#facebox .close').click();
            }
        });
        delete createweekdata;
        delete create_week_array;
        delete request;
    });

    //view adhoc duties
    $(document).on('click', '#viewAdhocDuties', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        var request = $("#editWeeklyAllocations").serialize();
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/view-adhocduty.php",
            data: request,
            success: function (data) {
                $('#content').html(data);
                viewAdhocDutyHeaderSettings();
            }
        });
        $('#facebox .close').click();
    });


    //on close create week popup
    $(document).on('click', '#closeCreateWeekPopup', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $('#loading').hide();
        var createweekdata = $("#createWeekForm").serializeArray();
        var create_week_array = {};
        $.map(createweekdata, function(n, i){
            create_week_array[n['name']] = n['value'];
        });

        var getCookieWeekNumber = $.cookie("cookieWeekNumber");
        if (getCookieWeekNumber == undefined) {
            var data = {
                "enteredWeekNum": $('#weekNumber').val(),
                "teamId": $('#searchTeamId').val(),
                "actionname": 'getprevweekforcookieweeknum'
            }
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/check-week.php",
                data: data,
                beforeSend: function(jqXHR, settings){
                    $('#loading').hide();
                },
                success: function (data) {
                    var resp = $.parseJSON(data);
                    if (resp.success) {
					    $.cookie('cookieWeekNumber', resp.cookieSetWeek);
                        $('#weekNumber').val(resp.cookieSetWeek);
                        let argDataInitialLoad = {};
                        argDataInitialLoad.schedulingTeamId = $('#searchTeamId').val();
                        argDataInitialLoad.userId = create_week_array.userId;
                        argDataInitialLoad.weekNumber = resp.cookieSetWeek;
                        argDataInitialLoad.teamId = $('#searchTeamId').val();
                        argDataInitialLoad.selAutoPageFilterId = create_week_array.selAutoPageFilterId;
                        argDataInitialLoad.shiftCountingFilterId = create_week_array.shiftCountingFilterId;
                        editWeeklyPageLoad('No',argDataInitialLoad);
                        if($('#disableDrag').prop('checked') == true){
                            disableDragDrop();
                        }
                    } else {
                        if((resp.errormessage != undefined) && ((resp.errormessage != null) || (resp.errormessage != ''))){
                            customAlert(resp.errormessage);
                        } else {
                            $('#facebox .close').click();
                            $('#facebox_overlay').detach();
                        }
                    }
                }
            });
        } else {
            var data = {
                "weeknumber": decodeEntities(getCookieWeekNumber),
                "schedulingTeamId": $('#searchTeamId').val(),
                "actionname": 'checkweekexists'
            }
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/check-week.php",
                data: data,
                beforeSend: function(jqXHR, settings){
                    $('#loading').hide();
					$('#loadingWeekly').hide();
                },
                success: function (data) {
                    var resp = $.parseJSON(data);
                    if (resp.success) {
                        $('#weekNumber').val(decodeEntities(getCookieWeekNumber));
                        $('#newAvailablePerson').val('Yes');

                        var request = $("#editWeeklyAllocations").serialize();
                        if($('#showWeeks').val() > 1){
                            request = request+'&cancelmultiweek=yes';
                        } else {
                            request = request+'&cancelmultiweek=no';
                        }
                        $.ajax({
                            type: "post",
                            url: "/page-includes/allocations/weekly/period-view.php",
                            data: request,
                            beforeSend: function(jqXHR, settings){
                                $('#loading').hide();
                                $('#loadingWeekly').hide();
                            },
                            success: function (data) {
                                if(parseInt(create_week_array.selAutoPageFilterId) > 0){
                                    $('#content').html(data);
                                    applyViewFilter('EditWeekly',create_week_array.selAutoPageFilterId,'Yes');
                                } else {
                                    $('#content').html(data);
                                    if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                                        applyViewFilter('EditWeekly','','No');
                                    } else {
                                        $('#filterName').html('<span title="Filters"> Filters</span>');
                                    }
                                }
                                $('#facebox .close').click();
                                if($('#disableDrag').prop('checked') == true){
                                    disableDragDrop();
                                }
                                if(parseInt(create_week_array.shiftCountingFilterId) > 0){
                                    applyDutyShiftCountingFilter(create_week_array.userId, create_week_array.schedulingTeamId, create_week_array.shiftCountingFilterId);
                                }
                            }
                        });
                    } else {
                        $.cookie('cookieWeekNumber',resp.currentweeknum);
                        $('#weekNumber').val(resp.currentweeknum);
                        $('#newAvailablePerson').val('Yes');
                        if((resp.validWeek.ixWeekInYear != null) && ((resp.errormessage != null) || (resp.errormessage != '')) && (resp.errormessage != undefined)){
                            customAlert(resp.errormessage);
                        } else {
                            var request = $("#editWeeklyAllocations").serialize();
                            if($('#showWeeks').val() > 1){
                                request = request+'&cancelmultiweek=yes';
                            } else {
                                request = request+'&cancelmultiweek=no';
                            }
                            $.ajax({
                                type: "post",
                                url: "/page-includes/allocations/weekly/period-view.php",
                                data: request,
                                success: function (data) {
                                    if(parseInt(create_week_array.selAutoPageFilterId) > 0){
                                        $('#content').html(data);
                                        applyViewFilter('EditWeekly',create_week_array.selAutoPageFilterId,'Yes');
                                    } else {
                                        $('#content').html(data);
                                        if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                                            applyViewFilter('EditWeekly','','No');
                                        } else {
                                            $('#filterName').html('<span title="Filters"> Filters</span>');
                                        }
                                    }
                                    $('#facebox .close').click();
                                    if($('#disableDrag').prop('checked') == true){
                                        disableDragDrop();
                                    }
                                    if(parseInt(create_week_array.shiftCountingFilterId) > 0){
                                        applyDutyShiftCountingFilter(create_week_array.userId, create_week_array.schedulingTeamId, create_week_array.shiftCountingFilterId);
                                    }
                                }
                            });
                        }
                    }
                }
            });
        }
        delete createweekdata;
        delete create_week_array;
    });


    //open the remove week popup
    $('#removeweek').on('click', function(e) {
        if($('#editScreen').val() == 1){
            e.stopImmediatePropagation();
            e.preventDefault();
            var data = {
                "teamId": $("#adhocFilter").val(),
                "weeknumber": $('#weekNumber').val(),
                "action": 'showremoveweekpopup'
            }
            $('#loading').show();
            $('#facebox .close').click();
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/removeWeek.php",
                data: data,
                success: function (data) {
                    $.facebox(data);
                    $('#loading').hide();
                }
            });
        }
    });

    $(document).on('click', '#removeweekApprove', function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        var data = {
            "teamId": $("#adhocFilter").val(),
            "weeknumber": $('#weeknumber').val(),
            "action": 'showremoveweekpopup'
        }
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/modals/removeWeek-approve.php",
            data: data,
            success: function (data) {
                $.facebox(data);
            }
        });
    });

    //Remove the week form
    $(document).on('click', '#js_saveRemoveweek', function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        var formdata = $("#removeweekallocations").serialize();
        var unserailazeData = $("#removeweekallocations").serializeArray();
        var weekColName = unserailazeData[1].name;
        var weekColVal = unserailazeData[1].value;
        if(weekColName == 'weeknumber'){
            let inpWeekNumber = weekColVal;
            var splitWeekNumber = inpWeekNumber.split('/');
            if(splitWeekNumber[1] == ''){
                let currentYear = new Date().getFullYear();
                unserailazeData[1].value = splitWeekNumber[0]+'/'+currentYear;
                newformdata = [];
                unserailazeData.forEach((number, index, rows) => {
                    if(rows[index].name == 'weeknumber'){
                        weekColName = rows[index].name;
                        weekColVal = rows[index].value;
                    }
                    newformdata.push(rows[index].name+'='+rows[index].value);
                });
                formdata = newformdata.join('&');
            }
        }
        $this = $(this);
        $('#loading').hide();
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php",
            data: formdata,
            beforeSend:function(){
                $('#loadingWeekly').hide();
            },
            success: function (data) {
                data = $.parseJSON(data);
                if (data.strstatus == 'error') {
                    $(".messageerror").html(data.strreturnstring);
                    $this.remove();
                } else {
                    customAlert(data.strreturnstring);
                    if(weekColName == 'weeknumber'){
                        if(weekColVal == $('#weekNumber').val()){
                            let unserailazeData = $("#removeweekallocations").serializeArray();
                            $("#publishWeekIconDiv").css('pointer-events', 'none');
                            $('#miscDutyBlock').remove();
                            $('#weeklyAllocation1').remove();
                            $('#weeklyAllocation2').remove();
                            $('#weeklyAllocationTable').remove();
                            $('#showCountsLeft').remove();
                            $('#showCountRight').remove();
                            if($('#showWeeks').val() > 1){
                                $('#showWeeks').val(1).trigger("chosen:updated");
                            }
                        }
                    }
                }
                $('#loading').hide();
            }
        });
    });
    $('.qtip-hover').qtip({
        position: {
            my: 'top center',
            viewport: $(window)
        },
        style: 'qtip-rounded qtip-shadow qtip-light'
    });

    function decodeEntities(encodedString) {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = encodedString;
        return textArea.value;
    }

    $("#editWeeklyAllocations").on('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        if($('#showWeeks').val() > 1){
            $('#showWeeks').val(1).trigger("chosen:updated");
        }

		var createweekflag= true;
		var enteredWeekNumber = $('#weekNumber').val();
        var cookieWeekNumberData = $.cookie('cookieWeekNumber');
        if (cookieWeekNumberData == undefined) {
            let substring = "/";
            if(!enteredWeekNumber.includes(substring)){
                if(parseInt(enteredWeekNumber) < 10){
                    enteredWeekNumber = '0'+parseInt(enteredWeekNumber);
                }
                enteredWeekNumber = enteredWeekNumber+'/'+new Date().getFullYear();
            } else {
                let spltEnteredWeekNum = enteredWeekNumber.split('/');
                if((spltEnteredWeekNum[1] != undefined) && (spltEnteredWeekNum.length > 0)){
                    if(parseInt(spltEnteredWeekNum[0]) < 10){
                        spltEnteredWeekNum[0] = '0'+parseInt(spltEnteredWeekNum[0]);
                    }
                    enteredWeekNumber = spltEnteredWeekNum[0]+'/20'+spltEnteredWeekNum[1];
                }
            }
            var request = {};
            request.actionname = 'getweekforcookieweeknum';
            request.prevweeknum = $('#wprev').attr('data-week');
            request.schedulingTeamId = $('#searchTeamId').val();
            request.enteredWeekNum = enteredWeekNumber;

            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/check-week.php",
                data: request,
                beforeSend: function (jqXHR, settings) {
                    $("#loading").show();
                },
                success: function (data) {
                    var respData = $.parseJSON(data);
					var createweekflag= respData.success;
                    if (respData.success) {
                        if(respData.errormessage != undefined){
                            if(respData.openWeek == 'Yes'){
                                customConfirm(respData.errormessage,function(){
                                        $('#getWeekNumber').val(respData.dateWeek);
                                        $.cookie('cookieWeekNumber', respData.cookieSetWeek);
                                        if ($("[name='weekNumber']").val()) {
                                            $("[name='date']").val("");
                                        }

                                        if (createweekflag==true){
                                            var request = $("#editWeeklyAllocations").serialize();
                                            var formDataArray = $("#editWeeklyAllocations").serializeArray();
                                            var form_array = {};
                                            $.map(formDataArray, function(n, i){
                                                form_array[n['name']] = n['value'];
                                            });
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/weekly/period-view.php",
                                                data: request,
                                                beforeSend: function (jqXHR, settings) {
                                                    $("#loading").show();
                                                },
                                                success: function (data) {
                                                    if (data) {
                                                        $('#content').html(data);
                                                        if((form_array.selFilterId != '') && (form_array.selFilterId != '0')){
                                                            applyViewFilter('EditWeekly',form_array.selFilterId,'No');
                                                            if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                                applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                            }
                                                        } else {
                                                            if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                                                                applyViewFilter('EditWeekly','','No');
                                                            } else {
                                                                $('#filterName').html('<span title="Filters"> Filters</span>');
                                                            }
                                                            if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                                applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                            }
                                                        }
                                                    }
                                                    if($.cookie('unallocgridheight') != undefined){
                                                        $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                                                        $("#allocatedDuty").height($.cookie('allocgridheight'));
                                                        $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                                                        $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                                                        $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                                                        $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                                                        setTimeout(function() {
                                                            $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                                                            $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                                                        }, 1000);
                                                        if($('#showHideCount').prop('checked') == false){
                                                            let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                                            $("#allocatedDuty").height(allocatedHeight);
                                                            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                                                        }
                                                    }
                                                },
                                                complete: function(){
                                                    $("#loading").hide();
                                                }
                                            });
                                        }
                                    },
                                    function() {
                                    }
                                );
                                $('#yes').val('OK');
                                $('#no').hide();
                            } else {
                                customAlert(respData.errormessage);
                                let ajaxParams = $.parseJSON($('#ajaxLoadParams').val());
                                $('#weekNumber').val(respData.cookieSetWeek);
                            }
                        } else {
                            $('#getWeekNumber').val(respData.dateWeek);
                            $.cookie('cookieWeekNumber', respData.cookieSetWeek);
                            if ($("[name='weekNumber']").val()) {
                                $("[name='date']").val("");
                            }

                            if (createweekflag==true){
                                var request = $("#editWeeklyAllocations").serialize();
                                var formDataArray = $("#editWeeklyAllocations").serializeArray();
                                var form_array = {};
                                $.map(formDataArray, function(n, i){
                                    form_array[n['name']] = n['value'];
                                });
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/period-view.php",
                                    data: request,
                                    beforeSend: function (jqXHR, settings) {
                                        $("#loading").show();
                                    },
                                    success: function (data) {
                                        if (data) {
                                            $('#content').html(data);
                                            if((form_array.selFilterId != '') && (form_array.selFilterId != '0')){
                                                applyViewFilter('EditWeekly',form_array.selFilterId,'No');
                                                if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                }
                                            } else {
                                                if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                                                    applyViewFilter('EditWeekly','','No');
                                                } else {
                                                    $('#filterName').html('<span title="Filters"> Filters</span>');
                                                }
                                                if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                }
                                            }
                                        }
                                        if($.cookie('unallocgridheight') != undefined){
                                            $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                                            $("#allocatedDuty").height($.cookie('allocgridheight'));
                                            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                                            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                                            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                                            $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                                            setTimeout(function() {
                                                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                                                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                                            }, 1000);
                                            if($('#showHideCount').prop('checked') == false){
                                                let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                                $("#allocatedDuty").height(allocatedHeight);
                                                $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                                            }
                                        }
                                    },
                                    complete: function(){
                                        $("#loading").hide();
                                    }
                                });
                            }
                        }
                    } else {
                        customAlert(respData.errormessage);
                        let ajaxParams = $.parseJSON($('#ajaxLoadParams').val());
                        $('#weekNumber').val(ajaxParams.weekNumber);
                    }
                },
                complete: function(){
                    $("#loading").hide();
                }
            });
        } else {
	        if (((enteredWeekNumber != decodeEntities(cookieWeekNumberData)) || (enteredWeekNumber == decodeEntities(cookieWeekNumberData))) && (enteredWeekNumber != '') && (enteredWeekNumber != undefined)) {
                let substring = "/";
                if(!enteredWeekNumber.includes(substring)){
                    if(parseInt(enteredWeekNumber) < 10){
                        enteredWeekNumber = '0'+parseInt(enteredWeekNumber);
                    }
                    enteredWeekNumber = enteredWeekNumber+'/'+new Date().getFullYear();
                } else {
                    let spltEnteredWeekNum = enteredWeekNumber.split('/');
                    if((spltEnteredWeekNum[1] != undefined) && (spltEnteredWeekNum.length > 0)){
                        if(parseInt(spltEnteredWeekNum[0]) < 10){
                            spltEnteredWeekNum[0] = '0'+parseInt(spltEnteredWeekNum[0]);
                        }
                        enteredWeekNumber = spltEnteredWeekNum[0]+'/20'+spltEnteredWeekNum[1];
                    }
                }
	            var request = {};
                request.actionname = 'checkweekexists';
                request.weeknumber = enteredWeekNumber;
                request.schedulingTeamId = $('#searchTeamId').val();
                request.oldPrevWeek = $('#wprev').attr('data-week');
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/actions/check-week.php",
                    data: request,
                    beforeSend: function () {
                        $("#loading").show();
                    },
                    success: function (data) {
                        var respData = $.parseJSON(data);
						var createweekflag= respData.success;
                        if (respData.success) {
                            if(respData.errormessage != undefined){
                                if(respData.openWeek == 'Yes'){
                                    customConfirm(respData.errormessage,function(){
                                            $('#getWeekNumber').val(respData.dateWeek);
                                            $.cookie('cookieWeekNumber', enteredWeekNumber);
                                            $('#setCookieWeekNum').val(enteredWeekNumber);

                                            if ($("[name='weekNumber']").val()) {
                                                $("[name='date']").val("");
                                            }

                                            if (createweekflag==true){
                                                var request = $("#editWeeklyAllocations").serialize();
                                                var formDataArray = $("#editWeeklyAllocations").serializeArray();
                                                var form_array = {};
                                                $.map(formDataArray, function(n, i){
                                                    form_array[n['name']] = n['value'];
                                                });
                                                $.ajax({
                                                    type: "post",
                                                    url: "/page-includes/allocations/weekly/period-view.php",
                                                    data: request,
                                                    beforeSend: function (jqXHR, settings) {
                                                        $("#loading").show();
                                                    },
                                                    success: function (data) {
                                                        if (data) {
                                                            $('#content').html(data);
                                                            if((form_array.selFilterId != '') && (form_array.selFilterId != '0')){
                                                                applyViewFilter('EditWeekly',form_array.selFilterId,'No');
                                                                if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                                }
                                                            } else {
                                                                if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                                                                    applyViewFilter('EditWeekly','','No');
                                                                } else {
                                                                    $('#filterName').html('<span title="Filters"> Filters</span>');
                                                                }
                                                                if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                                }
                                                            }
                                                            if($.cookie('unallocgridheight') != undefined){
                                                                $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                                                                $("#allocatedDuty").height($.cookie('allocgridheight'));
                                                                $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                                                                $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                                                                $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                                                                $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                                                                setTimeout(function() {
                                                                    $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                                                                    $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                                                                }, 1000);
                                                                if($('#showHideCount').prop('checked') == false){
                                                                    let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                                                    $("#allocatedDuty").height(allocatedHeight);
                                                                    $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                                                                }
                                                            }
                                                        }
                                                    },
                                                    complete: function(){
                                                        $("#loading").hide();
                                                    }
                                                });
                                            }
                                        },
                                        function() {
                                        }
                                    );
                                    $('#yes').val('OK');
                                    $('#no').hide();
                                } else {
                                    customAlert(respData.errormessage);
                                    let ajaxParams = $.parseJSON($('#ajaxLoadParams').val());
                                    $('#weekNumber').val(respData.cookieSetWeek);
                                }

                            } else {
                                $('#getWeekNumber').val(respData.dateWeek);
                                $.cookie('cookieWeekNumber', enteredWeekNumber);
                                $('#setCookieWeekNum').val(enteredWeekNumber);

                                if ($("[name='weekNumber']").val()) {
                                    $("[name='date']").val("");
                                }

                                if (createweekflag==true){
                                    var request = $("#editWeeklyAllocations").serialize();
                                    var formDataArray = $("#editWeeklyAllocations").serializeArray();
                                    var form_array = {};
                                    $.map(formDataArray, function(n, i){
                                        form_array[n['name']] = n['value'];
                                    });
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/weekly/period-view.php",
                                        data: request,
                                        beforeSend: function (jqXHR, settings) {
                                            $("#loading").show();
                                        },
                                        success: function (data) {
                                            if (data) {
                                                $('#content').html(data);
                                                if((form_array.selFilterId != '') && (form_array.selFilterId != '0')){
                                                    applyViewFilter('EditWeekly',form_array.selFilterId,'No');
                                                    if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                        applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                    }
                                                } else {
                                                    if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                                                        applyViewFilter('EditWeekly','','No');
                                                    } else {
                                                        $('#filterName').html('<span title="Filters"> Filters</span>');
                                                    }
                                                    if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                        applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                    }
                                                }
                                                if($.cookie('unallocgridheight') != undefined){
                                                    $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                                                    $("#allocatedDuty").height($.cookie('allocgridheight'));
                                                    $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                                                    $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                                                    $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                                                    $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                                                    setTimeout(function() {
                                                        $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                                                        $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                                                    }, 1000);
                                                    if($('#showHideCount').prop('checked') == false){
                                                        let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                                        $("#allocatedDuty").height(allocatedHeight);
                                                        $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                                                    }
                                                }
                                            }
                                        },
                                        complete: function(){
                                            $("#loading").hide();
                                        }
                                    });
                                }
                            }
                        } else {
                            if(respData.errormessage != undefined){
                                if(respData.openWeek == 'Yes'){
                                    customConfirm(respData.errormessage,function(){
                                            $('#getWeekNumber').val(respData.dateWeek);
                                            var request = $("#editWeeklyAllocations").serialize();
                                            var formDataArray = $("#editWeeklyAllocations").serializeArray();
                                            var form_array = {};
                                            $.map(formDataArray, function(n, i){
                                                form_array[n['name']] = n['value'];
                                            });
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/weekly/period-view.php",
                                                data: request,
                                                beforeSend: function (jqXHR, settings) {
                                                    $("#loading").show();
                                                },
                                                success: function (data) {
                                                    if (data) {
                                                        $('#content').html(data);
                                                        if((form_array.selFilterId != '') && (form_array.selFilterId != '0')){
                                                            applyViewFilter('EditWeekly',form_array.selFilterId,'No');
                                                            if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                                applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                            }
                                                        } else {
                                                            if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                                                                applyViewFilter('EditWeekly','','No');
                                                            } else {
                                                                $('#filterName').html('<span title="Filters"> Filters</span>');
                                                            }
                                                            if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                                applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                            }
                                                        }
                                                        if($.cookie('unallocgridheight') != undefined){
                                                            $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                                                            $("#allocatedDuty").height($.cookie('allocgridheight'));
                                                            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                                                            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                                                            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                                                            $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                                                            setTimeout(function() {
                                                                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                                                                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                                                            }, 1000);
                                                            if($('#showHideCount').prop('checked') == false){
                                                                let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                                                $("#allocatedDuty").height(allocatedHeight);
                                                                $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                                                            }
                                                        }
                                                    }
                                                },
                                                complete: function(){
                                                    $("#loading").hide();
                                                }
                                            });
                                        },
                                        function() {
                                        }
                                    );
                                    $('#yes').val('OK');
                                    $('#no').hide();
                                } else {
                                    customAlert(respData.errormessage);
                                    let ajaxParams = $.parseJSON($('#ajaxLoadParams').val());
                                    $('#weekNumber').val(respData.cookieSetWeek);
                                }
                            } else {
                                $('#getWeekNumber').val(respData.dateWeek);
                                var request = $("#editWeeklyAllocations").serialize();
                                var formDataArray = $("#editWeeklyAllocations").serializeArray();
                                var form_array = {};
                                $.map(formDataArray, function(n, i){
                                    form_array[n['name']] = n['value'];
                                });
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/period-view.php",
                                    data: request,
                                    beforeSend: function (jqXHR, settings) {
                                        $("#loading").show();
                                    },
                                    success: function (data) {
                                        if (data) {
                                            $('#content').html(data);
                                            if((form_array.selFilterId != '') && (form_array.selFilterId != '0')){
                                                applyViewFilter('EditWeekly',form_array.selFilterId,'No');
                                                if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                }
                                            } else {
                                                if((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
                                                    applyViewFilter('EditWeekly','','No');
                                                } else {
                                                    $('#filterName').html('<span title="Filters"> Filters</span>');
                                                }
                                                if((form_array.dutyShiftCountingFilterId != '') && (form_array.dutyShiftCountingFilterId != '0') && (form_array.dutyShiftCountingFilterId != 'NA')){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.teamId, form_array.dutyShiftCountingFilterId);
                                                }
                                            }
                                            if($.cookie('unallocgridheight') != undefined){
                                                $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                                                $("#allocatedDuty").height($.cookie('allocgridheight'));
                                                $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                                                $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                                                $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                                                $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                                                setTimeout(function() {
                                                    $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                                                    $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                                                }, 1000);
                                                if($('#showHideCount').prop('checked') == false){
                                                    let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                                    $("#allocatedDuty").height(allocatedHeight);
                                                    $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                                                }
                                            }
                                        }
                                    },
                                    complete: function(){
                                        $("#loading").hide();
                                    }
                                });
                            }
                        }
                    },
                    complete: function(){
                        $("#loading").hide();
                    }
                });
            }
        }
    });

    //All three section horizontal scroll
    $('.scrollHorizontal').scroll(function() {
        $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
    });

    // show allocated section vertical scroll
    $('.scrollVertical').scroll(function() {
        $('.scrollVertical').scrollTop($(this).scrollTop());
    });

    var startingHeight;
    var other;
    var minHeightDrag = 0;

    if($('#showWeeks').val() > 1){
        minHeightDrag = 62;
        if ($('#showMastMiscDuty').prop("checked") == true) {
            minHeightDrag = 162;
        }
    } else {
        minHeightDrag = 30;
        if ($('#showMastMiscDuty').prop("checked") == true) {
            minHeightDrag = 155;
        }
    }
    $(".resiz").resizable({
        handles: 's',
        /* try using "n" to see what happens... */
        minHeight: minHeightDrag,
        start: function(e, ui) {
            if ($(this).next('.resiz').length > 0) {
                other = $(this).next('.resiz');
            } else {
                other = $(this).prev('.resiz');
            }
            startingHeight = other.height();
        },
        resize: function(e, ui) {
            var dh = ui.size.height - ui.originalSize.height;
            if (dh > startingHeight) { // can't resize the box more then it's neighbour
                dh = startingHeight;
                ui.size.height = ui.originalSize.height + dh;
            }
            other.height(startingHeight - dh);
            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
            $('.showCountContainer').css('max-height', $("#showCountBlock").height()-15);
            setTimeout(function() {
                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
            }, 1000);
        }
    });

    $('#contextViewInfoIcons').on('click', function(){
        $.ajax({
            type: "POST",
            url: "/page-includes/allocations/weekly/view-info-icons.php",
            success: function (res) {
                $.facebox(res);
                $('#loading').hide();
            }
        });
    });
});

$(document).on('click', '#js_saveOvertTime', function() {
    SaveMarkOvertime();
});

$(document).on('change', '#reasonlist', function(e) {
    $('#reasonerror').hide();
});

/* Over 12 */
$(document).on('change', '#authoriseOver12', function(){
    overTwelveTextboxSetting();
});
$(document).on('click', '#js_saveauthorise', function() {
    saveAuthoriseOver12Popup();
});
$(document).on('click', '#js_cancelauthorise', function(){
    $.facebox.close();
});
/* Over 12 */

/* Under 11 */
$(document).on('click', '#js_saveoverride', function() {
    saveOverrideUnder11Popup();
});
$(document).on('click', '#js_removeoverride', function() {
    $("#RemoveOverride").val('1');
    $("#IsUnderElevenBreakOverride").val('0');
    saveOverrideUnder11Popup();
    $('#saveoverride11_form')[0].reset();
});
$(document).on('click', '#js_canceloverride', function() {
    $.facebox.close();
});
/* Under 11 */

$(document).on('click', '#js_SickSubmit', function(e) {
    e.stopImmediatePropagation();
    e.preventDefault();
    var $confirmboxmessage = '';
    var $confirmboxmessage2 = '';
    let sickDurationHrs = $("#hours").val();
    sickDurationHrsFloat = parseFloat(sickDurationHrs.replace(":", "."));

    let resonListVal = $("#reasonlist").val();
    let resonErr = false;
    if(resonListVal == 0){
        resonErr = true;
    }

    let maxSickHrsErr = false;
    if (sickDurationHrsFloat > parseFloat('23.75')) {
        maxSickHrsErr = true;
    }

    let negativeHrsErr = false;
    if(sickDurationHrs.indexOf('-') != -1){
        negativeHrsErr = true;
    }

    if((resonErr == true) && (maxSickHrsErr == false) && (negativeHrsErr == false)){
        $('#reasonerror').html('Reason is required');
        $('#reasonerror').show();
        $('#durationerror').html('');
        $('#durationerror').hide();
        return false;
    }

    if((resonErr == false) && (maxSickHrsErr == true) && (negativeHrsErr == false)){
        $('#reasonerror').html('');
        $('#reasonerror').hide();
        $('#durationerror').html('Max duration should be 23:75');
        $('#durationerror').show();
        return false;
    }

    if((resonErr == false) && (maxSickHrsErr == false) && (negativeHrsErr == true)){
        $('#reasonerror').html('');
        $('#reasonerror').hide();
        $('#durationerror').html('Duration should not be negative');
        $('#durationerror').show();
        return false;
    }

    if((resonErr == true) && (maxSickHrsErr == true) && (negativeHrsErr == false)){
        $('#reasonerror').html('Reason is required');
        $('#reasonerror').show();
        $('#durationerror').html('Max duration should be 23:75');
        $('#durationerror').show();
        return false;
    }

    if((resonErr == true) && (maxSickHrsErr == false) && (negativeHrsErr == true)){
        $('#reasonerror').html('Reason is required');
        $('#reasonerror').show();
        $('#durationerror').html('Duration should not be negative');
        $('#durationerror').show();
        return false;
    }

    if((resonErr == false) && (maxSickHrsErr == true) && (negativeHrsErr == true)){
        $('#reasonerror').html('');
        $('#reasonerror').hide();
        $('#durationerror').html('Duration should not be negative');
        $('#durationerror').show();
        return false;
    }

    if((resonErr == true) && (maxSickHrsErr == true) && (negativeHrsErr == true)){
        $('#reasonerror').html('Reason is required');
        $('#reasonerror').show();
        $('#durationerror').html('Duration should not be negative');
        $('#durationerror').show();
        return false;
    }

    let getDurMinsVal = sickDurationHrs.split(':');
    if(getDurMinsVal.length > 1){
        if((getDurMinsVal[1] == '0') || (getDurMinsVal[1] == '00') || (getDurMinsVal[1] == '25') || (getDurMinsVal[1] == '50') || (getDurMinsVal[1] == '5') || (getDurMinsVal[1] == '75')){

        } else {
            $('#durationerror').html('00,25,50,75 allowed in mins.');
            $('#durationerror').show();
            return false;
        }
    }

    let getDurMinsValDot = sickDurationHrs.split('.');
    if(getDurMinsValDot.length > 1){
        if((getDurMinsValDot[1] == '0') || (getDurMinsValDot[1] == '00') || (getDurMinsValDot[1] == '25') || (getDurMinsValDot[1] == '50') || (getDurMinsValDot[1] == '5') || (getDurMinsValDot[1] == '75')){

        } else {
            $('#durationerror').html('00,25,50,75 allowed in mins.');
            $('#durationerror').show();
            return false;
        }
    }
    var data = $("#sicknessrecordform").serialize();
    var sFormData = $("#sicknessrecordform").serializeArray();
    var sickFormData = {};
    $.map(sFormData, function(n, i){
        sickFormData[n['name']] = n['value'];
    });

    if ($("#ischeck").prop('checked') == true) {
        data += '&checkbox=1';
    } else {
        data += '&checkbox=0';
    }

    var ischeckVal = false;
    if($("#ischeck").prop('checked') == true){
        ischeckVal = true;
    }

    var dataVars = {
        'formData':data,
        'js_nextPrevDayReasonId':$("#js_nextPrevDayReasonId").val(),
        'reasonlist':$("#reasonlist").val(),
        'ischeck': ischeckVal,
        'comments':$("#comments").val(),
        'js_nextPrevDayComment':$("#js_nextPrevDayComment").val(),
        'js_prevDayReasonId':$("#js_prevDayReasonId").val(),
        'js_nextDayReasonId':$("#js_nextDayReasonId").val(),
        'prevDayMarkedSickness':$("#prevDayMarkedSickness").val(),
        'currDayMarkedSickness':$("#currDayMarkedSickness").val(),
        'js_action':$("#js_action").val(),
        'beforePrevDayReasonId':$("#beforePrevDayReasonId").val()
    };

    if (dataVars.js_action == 'insert') {
	    if ((dataVars.js_prevDayReasonId != dataVars.js_nextDayReasonId) && (dataVars.js_nextDayReasonId != 0) && (dataVars.js_prevDayReasonId != 0)) {


            $confirmboxmessage = 'Note that there are existing sickness records before and after this date <br/>';
            $confirmboxmessage += 'that have different Sickness Reasons. <br/> Would you like to align them ALL together as one sickness period with <br/>';
            $confirmboxmessage += 'the Sickness Reasons selected for this date ?';

            customConfirmFacebox($confirmboxmessage, function() {
                    data += '&synctype=1';
                    modSicknessRecord(data,sickFormData);
                },
                function() {
                    if (dataVars.js_nextPrevDayReasonId != 0) {
                        if ((dataVars.reasonlist != dataVars.js_nextPrevDayReasonId)) {
                            if(dataVars.reasonlist != dataVars.js_prevDayReasonId){
                                $confirmboxmessage2 = 'You have made changes to the following fields :<br/> -Reasons <br/><br/> Preceeding sickness days will also be updated with these changes.';
                            } else {
                                $confirmboxmessage2 = 'You have made changes to the following fields :<br/> -Comment <br/><br/> Related sickness days will be updated with these changes';
                            }
                        }
                        if (dataVars.ischeck == true && (dataVars.comments != dataVars.js_nextPrevDayComment)) {
                            $confirmboxmessage2 = 'You have made changes to the following fields :<br/> -Comment <br/><br/> Related sickness days will be updated with these changes';
                        }
                        if ((dataVars.reasonlist != dataVars.js_nextPrevDayReasonId) && dataVars.ischeck == true && (dataVars.comments != $("#js_nextPrevDayComment").val())) {
                            $confirmboxmessage2 = 'You have made changes to the following fields :<br/> -Reasons <br/>-Comment <br/><br/> Related sickness days will be updated with these changes';
                        }
                        if ($confirmboxmessage2 != '') {
                            data += '&synctype=2';
                            customConfirmFacebox($confirmboxmessage2, function() {
                                    modSicknessRecord(data,sickFormData);
                                },
                                function() {},0,'Yes'
                            );
                        } else {
                            data += '&synctype=0';
                            modSicknessRecord(data,sickFormData);
                        }
                    }
                },0,'No'
            );
        } else if (dataVars.js_nextPrevDayReasonId != 0) {
            if ((dataVars.reasonlist != dataVars.js_nextPrevDayReasonId)) {
                if(dataVars.currDayMarkedSickness == ''){
                    $confirmboxmessage = 'You have made changes to the following fields :<br/> -Reasons <br/><br/> Preceeding sickness days will also be updated with these changes.';
                } else {
                    $confirmboxmessage = 'You have made changes to the following fields :<br/> -Reasons <br/><br/> Related sickness days will be updated with these changes';
                }
            }
            if (dataVars.ischeck == true && (dataVars.comments != dataVars.js_nextPrevDayComment)) {
                $confirmboxmessage = 'You have made changes to the following fields :<br/> -Comment <br/><br/> Related sickness days will be updated with these changes';
            }
            if ((dataVars.reasonlist != dataVars.js_nextPrevDayReasonId) && dataVars.ischeck == true && (dataVars.comments != dataVars.js_nextPrevDayComment)) {
                $confirmboxmessage = 'You have made changes to the following fields :<br/> -Reasons <br/>-Comment <br/><br/> Related sickness days will be updated with these changes';
            }
            if ($confirmboxmessage != '') {

                if ((dataVars.js_prevDayReasonId == dataVars.js_nextDayReasonId)) {
                    data += '&synctype=1';
                } else {
                    data += '&synctype=2';
                }

                customConfirmFacebox($confirmboxmessage, function() {
                        modSicknessRecord(data,sickFormData);
                    },
                    function() {},0,'Yes'
                );
            }else{
                data += '&synctype=2';
                modSicknessRecord(data,sickFormData);
            }
        } else {
            data += '&synctype=0';
            modSicknessRecord(data,sickFormData);
        }

    } else if (dataVars.js_nextPrevDayReasonId != 0) {
		$confirmboxmessage ='';
        if ((dataVars.reasonlist != dataVars.js_nextPrevDayReasonId)) {
            $confirmboxmessage = 'You have made changes to the following fields :<br/> -Reasons <br/><br/> Related sickness days will be updated with these changes';
        }
        if (dataVars.ischeck == true && (dataVars.comments != dataVars.js_nextPrevDayComment)) {
            $confirmboxmessage = 'You have made changes to the following fields :<br/> -Comment <br/><br/> Related sickness days will be updated with these changes';
        }
        if ((dataVars.reasonlist != dataVars.js_nextPrevDayReasonId) && dataVars.ischeck == true && (dataVars.comments != dataVars.js_nextPrevDayComment)) {
            $confirmboxmessage = 'You have made changes to the following fields :<br/> -Reasons <br/>-Comment <br/><br/> Related sickness days will be updated with these changes';
        }
        if ($confirmboxmessage != '') {

            if ((dataVars.js_prevDayReasonId == dataVars.js_nextDayReasonId)) {
                data += '&synctype=1';
            } else {
                if((dataVars.js_prevDayReasonId != dataVars.reasonlist) && (dataVars.prevDayMarkedSickness == 1) && (dataVars.currDayMarkedSickness == 1) && (dataVars.js_nextDayReasonId == 0) && (dataVars.beforePrevDayReasonId != '')){
                    data += '&synctype=0';
                } else {
                    data += '&synctype=2';
                }
            }

            customConfirmFacebox($confirmboxmessage, function() {
                    modSicknessRecord(data,sickFormData);
                },
                function() {},0,'Yes'
            );
        } else {
            data += '&synctype=0';
            modSicknessRecord(data,sickFormData);
        }
    } else {
        data += '&synctype=0';
        modSicknessRecord(data,sickFormData);
    }
});

function overTwelveTextboxSetting() {
    if($('#authoriseOver12').val() == 0) {
        $('#authorised12Hours').val(0).attr('readonly', true);
        $('#overseasDeployment').attr('disabled', 'disabled');
    } else {
        $('#overseasDeployment').removeAttr("disabled");
        $('#authorised12Hours').attr('readonly', false);
    }
}
function modSicknessRecord(data,sickFormData) {
    var dataStrObj = data.split('&');
    var cellId = 0;
	var retscreen='';
	var retteamId='';
    $(dataStrObj).each(function(ind,val){
        var keyName = val.split('=');
	    if(keyName[0] == 'js_allocationid'){
            cellId = keyName[1];
        }
		if(keyName[0] == 'sicknessscreen'){
            retscreen = keyName[1];
	    }
		if(keyName[0] == 'sicknessteamId'){
            retteamId = keyName[1];
        }

    });
    $('#loading').show();
    let mastMiscFilterId = 0;
    if($("#mastMiscFilterId").val() != ''){
        mastMiscFilterId = $("#mastMiscFilterId").val();
    }

    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php",
        data: data+'&mastMiscFilterId='+mastMiscFilterId,
        success: function (data) {
            data = $.parseJSON(data);
            if (data.strstatus == 'error') {
                customAlert(data.strreturnstring, 1000);
            }
            $('#facebox .close').click();

			if (retscreen=='EditDaily'){
				ShowDailyAllocations(retteamId, $('#strCurrentDate').val());
			} else {
				if(cellId != 0){
                    let scrollLeftPos = $('#weeklyUnAllocation-2').scrollLeft();
					let allocationsSpId = (sickFormData.allocationsSpId == 'null') ? sickFormData.scheduledpersonId : sickFormData.allocationsSpId
					let dataInstanceId =  allocationsSpId + '_' + sickFormData.dutyDate;
                    let cellDutyName = $("#colUnqId_"+dataInstanceId).attr("data-duty-name");
                    let cellDutyEndTime = $("#colUnqId_"+dataInstanceId).attr("data-row-end");
                    let cellDutyDuration = $("#colUnqId_"+dataInstanceId).attr("data-duty-duration");
                    let dutyExistsVal = data.dutyExists;
                    let doesntNeedCoveringIconClassExists = $('#rowUnqId_' +dataInstanceId).hasClass('doesntNeedCoveringIcon') ? 'on' : 'off';
                    let doesntNeedCoveringIconClassName = $('#rowUnqId_' +dataInstanceId).hasClass('doesntNeedCoveringIcon') ? 'doesntNeedCoveringIcon' : '';
                    if((cellDutyName.indexOf("Sick") == -1) && (cellDutyName != 'U')){
                        if($('#showWeeks').val() > 1){
                            $('#weeklyUnAllocation-2').scrollLeft(scrollLeftPos);
                            $('#weeklyAllocation-2').scrollLeft(scrollLeftPos);
                            $('#showCountRight').scrollLeft(scrollLeftPos);

                            $('#sickAttr').attr('data-duty-name',sickFormData.oDutyName).attr('date-start-date',sickFormData.fDateStartDate).attr('date-range-days',sickFormData.fDateRangeDays).attr('data-date',sickFormData.dutyDate).attr('data-row-start',sickFormData.fDataRowStart).attr('data-row-end',sickFormData.fDataRowEnd).attr('data-scheduling-team-id',sickFormData.fDataSchedulingTeamId).attr('date-end-date',sickFormData.fDateEndDate).attr('data-duty-duration',sickFormData.fDataDutyDuration).attr('data-masterdutyid',sickFormData.fDataMasterdutyid).attr('data-isactive',sickFormData.fDataIsactive).attr('data-iday',sickFormData.fDataIday).attr('data-new-id',data.newunallocid).attr('data-duty-exists',dutyExistsVal).attr('data-doesnt-need-covering', doesntNeedCoveringIconClassExists).attr('data-is-need-covering',doesntNeedCoveringIconClassName).attr('data-id',data.dataId).attr('data-unique-id', dataInstanceId).attr('data-allocations-duty-id', sickFormData.allocationsDutyId);

                            refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITSICK',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes','No',$('#sickAttr'),'');

                            if($.cookie('unallocgridheight') == undefined){
                                showHideCountGrid();
                            }

                            if((cellDutyName != '-') && (cellDutyName != '--') && (cellDutyDuration != 0) && (cellDutyDuration != null) && (cellDutyName.toLowerCase() != 'absent')){
                                if(((dutyExistsVal == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((dutyExistsVal == '') && ($('#mastMiscFilterId').val() == '')) || ((dutyExistsVal == 'No') && ($('#mastMiscFilterId').val() == ''))){
                                    var finalUpdUnallocDutyData = updateUnAllocDutyJsonArray(sickFormData.oDutyName,sickFormData.dutyDate,'ALLOCTOUNALLOC','','', sickFormData.fDataRowStart, sickFormData.fDataRowEnd);
                                    updateUnallocGrid($('#sickAttr'),'',data.newunallocid,'ALLOCTOUNALLOC',finalUpdUnallocDutyData,'',dutyExistsVal);
                                    setTimeout(function() {
                                        $( '#rowUnqId_'+data.newunallocid ).css('border-bottom','2px solid #bbb');
                                        $('#dragDropId1').html(data.newunallocid);
                                        $('#weeklyAllocation-1').scrollTop(Math.abs($.cookie("teamPeopleBlockPosTop")));
                                        $('#weeklyAllocation-1').scrollLeft(Math.abs($.cookie("teamPeopleBlockPosLeft")) + 2);
                                    }, 1500);
                                }
                            }
                        } else {
                            $('#sickAttr').attr('data-duty-name',sickFormData.oDutyName).attr('date-start-date',sickFormData.fDateStartDate).attr('date-range-days',sickFormData.fDateRangeDays).attr('data-date',sickFormData.dutyDate).attr('data-row-start',sickFormData.fDataRowStart).attr('data-row-end',sickFormData.fDataRowEnd).attr('data-scheduling-team-id',sickFormData.fDataSchedulingTeamId).attr('date-end-date',sickFormData.fDateEndDate).attr('data-duty-duration',sickFormData.fDataDutyDuration).attr('data-masterdutyid',sickFormData.fDataMasterdutyid).attr('data-isactive',sickFormData.fDataIsactive).attr('data-iday',sickFormData.fDataIday).attr('data-new-id',data.newunallocid).attr('data-duty-exists',dutyExistsVal).attr('data-doesnt-need-covering', doesntNeedCoveringIconClassExists).attr('data-is-need-covering',doesntNeedCoveringIconClassName).attr('data-id',data.dataId).attr('data-unique-id', dataInstanceId).attr('data-allocations-duty-id', sickFormData.allocationsDutyId);

                            refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITSICK',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes','No',$('#sickAttr'),'');

                            if($.cookie('unallocgridheight') == undefined){
                                showHideCountGrid();
                            }

                            if((cellDutyName != '-') && (cellDutyName != '--') && (cellDutyDuration != 0) && (cellDutyDuration != null) && (cellDutyName.toLowerCase() != 'absent') && (!$("#colUnqId_"+dataInstanceId).parent().hasClass('doesntNeedCoveringIcon'))){
                                if(((dutyExistsVal == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((dutyExistsVal == '') && ($('#mastMiscFilterId').val() == '')) || ((dutyExistsVal == 'No') && ($('#mastMiscFilterId').val() == ''))){
                                    var finalUpdUnallocDutyData = updateUnAllocDutyJsonArray(sickFormData.oDutyName,sickFormData.dutyDate,'ALLOCTOUNALLOC','','',sickFormData.fDataRowStart,sickFormData.fDataRowEnd);
                                    updateUnallocGrid($('#sickAttr'),'',data.newunallocid,'ALLOCTOUNALLOC',finalUpdUnallocDutyData,'',dutyExistsVal);
                                    setTimeout(function() {
                                        $( '#rowUnqId_'+data.newunallocid ).css('border-bottom','2px solid #bbb');
                                        $('#dragDropId1').html(data.newunallocid);
                                    }, 1500);
                                }
                            }
                        }
                    } else {
                        $('#sickAttr').attr('data-duty-name',sickFormData.oDutyName).attr('date-start-date',sickFormData.fDateStartDate).attr('date-range-days',sickFormData.fDateRangeDays).attr('data-date',sickFormData.dutyDate).attr('data-row-start',sickFormData.fDataRowStart).attr('data-row-end',sickFormData.fDataRowEnd).attr('data-scheduling-team-id',sickFormData.fDataSchedulingTeamId).attr('date-end-date',sickFormData.fDateEndDate).attr('data-duty-duration',sickFormData.fDataDutyDuration).attr('data-masterdutyid',sickFormData.fDataMasterdutyid).attr('data-isactive',sickFormData.fDataIsactive).attr('data-iday',sickFormData.fDataIday).attr('data-new-id',data.newunallocid).attr('data-duty-exists',dutyExistsVal).attr('data-doesnt-need-covering', doesntNeedCoveringIconClassExists).attr('data-is-need-covering',doesntNeedCoveringIconClassName).attr('data-id',data.dataId).attr('data-unique-id', dataInstanceId).attr('data-allocations-duty-id', sickFormData.allocationsDutyId);

                        refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITSICK',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes','No',$('#sickAttr'),'');

                        setTimeout(function() {
                            $('#dragDropId1').html(data.newunallocid);
                        }, 1000);
                    }
				} else {
					$("#editWeeklyAllocations").submit();
				}
			}
            $('#loading').hide();
        }
    });
}
//Script for 2 tabel rows scroll together

function divScrollL() {
    let teamPeopleTop = $('#teamPeoplesBlockPos').position().top;
    let teamPeopleLeft = $('#teamPeoplesBlockPos').position().left;
    $.cookie("teamPeopleBlockPosTop", teamPeopleTop);
    $.cookie("teamPeopleBlockPosLeft", teamPeopleLeft);
}

function allocatePositionleft() {
    const scrollLeft = $('#weeklyAllocation-2').scrollLeft();
    $.cookie('allocationWeekPosLeft', scrollLeft);
}

function divScrollTop() {
    let topUnAllocTop = $('#unallocDutyBlockPos').position().top;
    $.cookie("unallocDutyBlockPosTop", topUnAllocTop);
}

$(function() {
    //context menu
    $.contextMenu({
        selector: '.context-menu',
        zIndex: function($trigger, opt){
            return 999;
        },
        events: {
            activated : function(options) {
                if(options.$menu.css('top') == '0px') {
                    options.$menu.css('top', Math.abs(Math.round(Math.abs(options.$trigger.offset().top - options.$menu.height()) - 4)) + 'px');
                }
            }
       },
        callback: function(key, options) {
            draggable = $(this).find(".item-cell");

            var data = {
                "ID": draggable.attr("data-id"),
                "schedulingPersonId": draggable.attr("data-scheduling-person"),
                "dutyDate": draggable.attr("data-date"),
                "teamId": $("#adhocFilter").val(),
                "action": key,
                "dutyAttention": draggable.attr("data-attention"),
				'allocationsDutyId' : draggable.attr("data-allocations-duty-id"),
				'allocationsSpId' : draggable.attr("data-allocations-sp-id"),
				'allocationsSchPer' : draggable.attr("data-scheduling-person"),
            }

            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/mark-action.php",
                data: data,
                beforeSend: function(jqXHR, settings){
                    $('#loading').hide();
                },
                success: function (response) {
                    response = $.parseJSON(response);
                    if (response.success === true) {
                        if (key == 'attention') {
                            $("#rowUnqId_" + draggable.attr("data-unique-id")).addClass('attentionClass');
                            $("#rowUnqId_" + draggable.attr("data-unique-id")).css('background-color','#FFB6C1');
                            $("#rowUnqId_" + draggable.attr("data-unique-id")).removeAttr("style")
                            $("#colUnqId_" + draggable.attr("data-unique-id")).attr('data-attention', 1);
                        }
                        if (key == 'unattention') {
                            $("#rowUnqId_" + draggable.attr("data-unique-id")).removeClass('attentionClass');
                            $("#rowUnqId_" + draggable.attr("data-unique-id")).removeClass('attentionCopyDutyClass');
                            $("#colUnqId_" + draggable.attr("data-unique-id")).attr('data-attention', 0);
                            setTimeout(function() {
                               if ((draggable.attr("data-row-start")==0) && (draggable.attr("data-row-end")==0)){
									$("#rowUnqId_" + draggable.attr("data-unique-id")).css("background-color", "#" + draggable.attr("data-bgcolor")).css("color", "#" + draggable.attr("data-font-color"));
								} else {
									$("#rowUnqId_" + draggable.attr("data-unique-id")).css("background-color", "#" + draggable.attr("data-bg-color"));
								}
                            }, 500);
                        }

                        if (key == 'wiad') {
                            var icons = draggable.find('.icons');
                            draggable.attr('data-wiad', 1);
                            draggable.attr('data-actual', 0);
                            var wrap = $("<b></b>");
                            wrap.html('W');
                            icons.html(wrap);
                        }
                        if (key == 'actual') {
                            var icons = draggable.find('.icons');
                            draggable.attr('data-actual', 1);
                            draggable.attr('data-wiad', 0);
                            var wrap = $("<b></b>");
                            wrap.html('A');
                            icons.html(wrap);
                        }

                        if (key == 'unmarkactual' || key == 'unmarkwiad') {
                            var icons = draggable.find('.icons');
                            icons.html('');
                            draggable.attr('data-actual', 0);
                            draggable.attr('data-wiad', 0);
                        }

                        if((key == 'wiad') || (key == 'actual') || (key == 'unmarkwiad') || (key == 'unmarkactual') || (key == 'attention') || (key == 'unattention')){
                            refreshAllocatedSectionTr(draggable.attr("data-scheduling-person"),draggable.attr("data-row-id"),draggable.attr("data-date"),draggable.attr("data-duty-name"),draggable.attr("data-row-start"),draggable.attr('data-row-end'),draggable.attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',draggable.attr('data-id'),0,0,'Yes');
                        }
                        if($("#colUnqId_" + draggable.attr("data-unique-id")).attr('data-scheduling-person') == $('#personRowHighlight').html()){
                            $('.person_'+$("#colUnqId_" + draggable.attr("data-unique-id")).attr('data-scheduling-person')).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
                        }
                    } else {
                        if ((key == 'wiad') || (key == 'unmarkwiad') || (key == 'actual') || (key == 'unmarkactual')) {
                            customAlert(response.errMsg);
                        } else {
                            customAlert('Something went wrong. Please try again later.');
                        }
                    }
                }
            });
        },
        items: {
            "delete": {
                name: "Unassign Allocation",
                icon: "delete",
                selected: false,
                callback: function(key, options) {
                    draggable2 = $(this).find(".item-cell");
                    if (draggable2.attr('data-charging-present') == 1 || draggable2.attr('data-charging-present') == "1") {
                        customAlert("This duty cannot be deleted/swapped as it has one or more Charging records associated with it. Please remove the Charging records before trying to delete");
                        return false;
                    } else {
                        if($('#redEuro_'+draggable2.attr('data-unique-id')).hasClass('activemarkovertime')){
                            customAlert('You cannot unallocate or swap a duty that is marked for Overtime. Please delete the Overtime before modifying the duty');
                        } else {
                            refreshAllocatedSectionTr(draggable2.attr('data-scheduling-person'),draggable2.attr('data-row-id'),draggable2.attr('data-date'),draggable2.attr('data-duty-name'),0,0,draggable2.attr('data-unique-id'),'','ALLOCTOUNALLOC',draggable2.attr('data-id'),0,0,'Yes','Yes',draggable2, "'" + draggable2.attr("data-allocations-sp-id") +'_'+draggable2.attr("data-date") + "'");
                        }
                    }
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-edit-duty-flag") == 1) && (draggable2.attr("data-duty-name") != 'U') && (draggable2.attr("data-duty-name").toLowerCase() != 'absent') && (draggable2.attr("data-leave-starttime") == 0) && (draggable2.attr("data-leave-endtime") == 0) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "edit": {
                name: 'Edit Duty',
                visible: function(key, opt, e) {
                    var allocObj = $(this).find(".alloc");
                    if ((allocObj.attr("data-edit-duty-flag") == 1) && (allocObj.attr("data-duty-name") != 'U') && (allocObj.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                },
                icon: "edit",
                selected: false,
                callback: function(key, opt, e) {
                    draggable = $(this).find(".alloc");
                    editAllocateDuty('editduty','edit', draggable2.attr("data-allocations-sp-id") +'_'+draggable2.attr("data-date"));
                }
            },
            "add": {
                name: 'Create Duty',
                visible: function(key, opt, e) {
                    var allocObj = $(this).find(".alloc");
                    if ((allocObj.attr("data-edit-duty-flag") == 1) && (allocObj.attr("data-duty-name") == 'U') && (allocObj.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                },
                icon: "edit",
                selected: false,
                callback: function(key, opt, e) {
                    draggable = $(this).find(".alloc");
					let chkId = (draggable2.attr("data-allocations-sp-id") != "null" && draggable2.attr("data-allocations-sp-id") != '') ? draggable2.attr("data-allocations-sp-id") : draggable2.attr("data-scheduling-person");
                    editAllocateDuty('createduty','edit', chkId +'_'+draggable2.attr("data-date"));
                }
            },
            "copy_duty_rota": {
                name: 'Create Duty from Rota Pattern',
                visible: function(key, opt, e) {
                    var allocObj = $(this).find(".alloc");
                    if ((allocObj.attr("data-edit-duty-flag") == 1) && (allocObj.attr("data-duty-name") == 'U') && (allocObj.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                },
                icon: "copy",
                selected: false,
                callback: function(key, opt, e) {
                    let $draggable = $(this).find(".alloc");
                    createDutyFromRota($draggable);
                }
            },
            "allocatedjobs": {
                name: "View Jobs",
                icon: "jobs",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".alloc");
                    if (draggable2.attr("data-duty-name") != 'U' && draggable2.attr("data-edit-duty-flag") == 1) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".item-cell");
                    dutyAllocatedJobs(selectedDay);
                }
            },
            "comment": {
                name: "Comments",
                icon: "comment",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".alloc");
                    if (draggable2.attr("data-edit-duty-flag") == 1) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".item-cell");
                    addComments(selectedDay);
                }
            },
            "attention": {
                name: "Mark for Attention",
                icon: "exclaim",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    // Disable this item if the menu was triggered on a div
                    if((draggable2.attr("data-attention") == 0)  && (draggable2.attr("data-edit-duty-flag") == 1) && (draggable2.attr("data-edit-screen") == 1)){
                        return true;
                    }
                }
            },
            "unattention": {
                name: "Unmark for Attention",
                icon: "exclaim",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if((draggable2.attr("data-attention") == 1 || draggable2.attr("data-attention") == 2)  && (draggable2.attr("data-edit-duty-flag") == 1) && (draggable2.attr("data-edit-screen") == 1)){
                        return true;
                    }
                }
            },
            "request": {
                name: "Mark as Purple Font",
                icon: 'purple',
                selected: false,
                // superseeds "global" callback
                callback: function(key, options) {
                    var data = {
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date"),
                        "teamId": $("#adhocFilter").val(),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly"),
                        "action": 'request',
                        "type": "markrequest",
                        "dutyRequest": 1,
                        "ID": $(this).find(".item-cell").attr("data-id"),
                        "dutyName": $(this).find(".item-cell").attr("data-duty-name"),
						'allocationsDutyId' : $(this).find(".item-cell").attr("data-allocations-duty-id"),
						'allocationsSchPer' : $(this).find(".item-cell").attr("data-scheduling-person"),
						'allocationsSpId' : $(this).find(".item-cell").attr("data-allocations-sp-id")
                    }
                    MarkRequest(data);
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if (($("#dutytipID_" + draggable2.attr("data-dateonly") + "_" + draggable2.attr("data-scheduling-person")).hasClass('requestClass') == false)  && (draggable2.attr("data-edit-duty-flag") == 1) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "unrequest": {
                name: "Unmark Purple Font",
                icon: "unmark",
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date"),
                        "teamId": $("#adhocFilter").val(),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly"),
                        "action": 'request',
                        "type": "unmarkrequest",
                        "dutyRequest": 0,
                        "dutyTextColor": $(this).find(".item-cell").attr("data-font-color"),
                        "ID": $(this).find(".item-cell").attr("data-id"),
                        "dutyName": $(this).find(".item-cell").attr("data-duty-name"),
						'allocationsDutyId' : $(this).find(".item-cell").attr("data-allocations-duty-id"),
						'allocationsSpId' : $(this).find(".item-cell").attr("data-allocations-sp-id"),
						'allocationsSchPer' : $(this).find(".item-cell").attr("data-scheduling-person")
                    }
                    MarkRequest(data);
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if (($("#dutytipID_" + draggable2.attr("data-dateonly") + "_" + draggable2.attr("data-scheduling-person")).hasClass('requestClass'))  && (draggable2.attr("data-edit-duty-flag") == 1) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "markovertime": {
                name: "Mark Overtime",
                icon: "markovertime",
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "allocationId": $(this).find(".item-cell").attr("data-id"),
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date"),
                        "teamId": $(this).find(".item-cell").attr("data-scheduling-team-id"),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly"),
                        "allocationsDutyId": $(this).find(".item-cell").attr("data-allocations-duty-id"),
                        "allocationsSpId": $(this).find(".item-cell").attr("data-allocations-sp-id"),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly")
                    }
                    MarkOvertimeOpenPopup(data);
                },
                visible: function(key, opt) {
                    let dutyNames = ["U", "SICK", "U-SICK", "-SICK"];
                    draggable2 = $(this).find(".item-cell");
                    let cellDutyName = draggable2.attr("data-duty-name");
                    if ((dutyNames.indexOf( cellDutyName.toUpperCase() ) === -1) && ($("#redEuro" + "_" + $(this).find(".item-cell").attr("data-unique-id")).hasClass('activemarkovertime') == false) && (draggable2.attr("data-edp") == "M") && (draggable2.attr("data-is-home-team") == 1) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "editmarkovertime": {
                name: "Edit Overtime",
                icon: 'markovertime',
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "allocationId": $(this).find(".item-cell").attr("data-id"),
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date"),
                        "teamId": $(this).find(".item-cell").attr("data-scheduling-team-id"),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly"),
                        "allocationsDutyId": $(this).find(".item-cell").attr("data-allocations-duty-id"),
                        "allocationsSpId": $(this).find(".item-cell").attr("data-allocations-sp-id"),
                    }
                    MarkOvertimeOpenPopup(data);
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-duty-name") != 'U') && ($("#redEuro" + "_" + $(this).find(".item-cell").attr("data-unique-id")).hasClass('activemarkovertime') == true) && (draggable2.attr("data-edp") == "M") && (draggable2.attr("data-is-home-team") == 1) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "manageleave": {
                name: "Leave",
                icon: 'manageleave',
                selected: false,
                callback: function(key, options) {
                    openLeavePopup($(this).find(".item-cell"));
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-duty-name").toUpperCase() != 'SICK') && (draggable2.attr("data-duty-name").toUpperCase() != '-SICK') && (draggable2.attr("data-duty-name").toUpperCase() != 'ABSENT') && (draggable2.attr("data-duty-name").toUpperCase() != 'U-SICK') && ($("#redEuro" + "_" + $(this).find(".item-cell").attr("data-unique-id")).hasClass('activemarkovertime') == false) && (draggable2.attr("data-is-home-team") == 1) && ((draggable2.attr("data-charging-present") == 0) || (draggable2.attr("data-charging-present") == "0") || (draggable2.attr("data-charging-present") == undefined)) && (draggable2.attr("data-eleven-icon") == '0') && (draggable2.attr("data-mark-twelve") != '1') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "copyDuty": {
                name: "Copy Duty",
                icon: "copy",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".alloc");
                    let notInDutyNames = ['U','SICK','-SICK','U-SICK','LEAVE','OFF LEAVE','ABSENT'];
                    if(($('#isRestrictCopyDuty').val() == '0') && ($.inArray(draggable2.attr('data-duty-name').toUpperCase(), notInDutyNames) == -1)){
                        return true;
                    } else {
                        if ((draggable2.attr('data-row-start') == '0') && (draggable2.attr('data-row-end') == '0') && (draggable2.attr('data-duty-duration') != '0') && ($("#dutytipID" + "_" + draggable2.attr("data-date")+"_" + draggable2.attr("data-scheduling-person")).hasClass('otherTeamClass') == false) && ($.inArray(draggable2.attr('data-duty-name').toUpperCase(), notInDutyNames) == -1)) {
                            return true;
                        } else if(((draggable2.attr('data-row-start') != '0') || (draggable2.attr('data-row-end') != '0')) && (draggable2.attr('data-duty-duration') != '0') && ((draggable2.attr("data-duty-name").charAt(1) == '7') || (draggable2.attr("data-duty-name").charAt(1) == '8') || (draggable2.attr("data-duty-name").charAt(1) == '9')) && ($("#dutytipID" + "_" + draggable2.attr("data-date")+"_" + draggable2.attr("data-scheduling-person")).hasClass('otherTeamClass') == false) && ($.inArray(draggable2.attr('data-duty-name').toUpperCase(), notInDutyNames) == -1)){
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".alloc");
                    copyDuty(selectedDay);
                }
            },
            "overtimeVolunters": {
                name: 'Show Volunteers',
                icon: "showvolunter",
                selected: false,
                callback: function(key, opt, e) {
                    draggable = $(this).find(".alloc");
                    ShowOvertimeVolunteers(draggable.attr('data-scheduling-team-id'), draggable.attr('data-date'))
                }
            },
            "schedulerslist": {
                name: "Schedulers",
                icon: "user",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".alloc");
                    if ((draggable2.attr("data-duty-name").toUpperCase() != 'U') && (draggable2.attr("data-duty-name").toUpperCase() != 'U-SICK') && (draggable2.attr("data-duty-name").toUpperCase() != '-SICK') && (draggable2.attr("data-duty-name").toUpperCase() != 'SICK') && (draggable2.attr("data-duty-name").toUpperCase() != 'LEAVE') && (draggable2.attr("data-duty-name").toUpperCase() != 'OFF LEAVE') && (draggable2.attr("data-duty-name").toUpperCase() != 'ABSENT')) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".alloc");
                    schedulersList(selectedDay);
                }
            },
            "actual": {
                name: "Mark Actual",
                icon: 'markactual',
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-show-wiad-actual") == 1) && (draggable2.attr("data-actual") == 0) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }

                    if (draggable2.attr("data-is-home-team") == 0) {
                        return false;
                    }
                }
            },
            "unmarkactual": {
                name: "Unmark Actual",
                icon: 'unmarkactual',
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-show-wiad-actual") == 1) && (draggable2.attr("data-actual") == 1) && (draggable2.attr("data-duty-name") == 'U') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "wiad": {
                name: "Mark WIAD",
                icon: 'markwiad',
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-show-wiad-actual") == 1) && (draggable2.attr("data-wiad") == 0) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }

                    if (draggable2.attr("data-show-wiad-actual") == 0) {
                        return false;
                    }
                }
            },
            "unmarkwiad": {
                name: "Unmark WIAD",
                icon: 'unmarkwiad',
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-show-wiad-actual") == 1) && (draggable2.attr("data-wiad") == 1) && (draggable2.attr("data-duty-name") == 'U') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "deleteabsent": {
                name: "Delete Absent",
                icon: "delete",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-duty-name") == 'Absent') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                },
                callback: function(key, options) {
                var allocationId = draggable2.attr("data-unique-id");
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/edits/delete_absent.php",
                        data :{
                            'dataUniqueId' :draggable2.attr("data-allocations-sp-id"),
                            'allocationId' : draggable2.attr("data-id")
                        },
                        success: function (data) {
                            refreshAllocatedSectionTr($("#colUnqId_"+allocationId).attr("data-scheduling-person"),$("#colUnqId_"+allocationId).attr("data-row-id"),$("#colUnqId_"+allocationId).attr("data-date"),$("#colUnqId_"+allocationId).attr("data-duty-name"),$("#colUnqId_"+allocationId).attr("data-row-start"),$("#colUnqId_"+allocationId).attr('data-row-end'),$("#colUnqId_"+allocationId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+allocationId).attr('data-id'),0,0,'Yes');

                        }
                    });
                }
            },
            "Override11" : {
                name: "Override < 11",
                icon: "exclaim",
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "allocationId": $(this).find(".item-cell").attr("data-id"),
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "allocationsSpId": $(this).find(".item-cell").attr("data-allocations-sp-id"),
                        "allocationsDutyId": $(this).find(".item-cell").attr("data-allocations-duty-id"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date")
                    }
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/modals/add-markUnder-Eleven.php",
                        data: data,
                        success: function (response) {
                            $.facebox(response);
                        }
                    });
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-mark-eleven") == '1') && (draggable2.attr("data-eleven-icon") == '0') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "Modify Override11" : {
                name: "Modify < 11",
                icon: "exclaim",
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "allocationId": $(this).find(".item-cell").attr("data-id"),
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "allocationsSpId": $(this).find(".item-cell").attr("data-allocations-sp-id"),
                        "allocationsDutyId": $(this).find(".item-cell").attr("data-allocations-duty-id"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date")
                    }
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/modals/add-markUnder-Eleven.php",
                        data: data,
                        success: function (response) {
                            $.facebox(response);
                        }
                    });
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-mark-eleven") == '1') && (draggable2.attr("data-eleven-icon") == '1') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "Modify Over12" : {
                name: "Modify > 12",
                icon: "exclaim",
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "allocationId": $(this).find(".item-cell").attr("data-id"),
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "allocationsSpId": $(this).find(".item-cell").attr("data-allocations-sp-id"),
                        "allocationsDutyId": $(this).find(".item-cell").attr("data-allocations-duty-id"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date")
                    }
                    AuthoriseOver12Popup(data);
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-mark-twelve") == '1') || (draggable2.attr("data-mark-twelve") == '0') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "Authorise Over12" : {
                name: "Authorise > 12",
                icon: "exclaim",
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "allocationId": $(this).find(".item-cell").attr("data-id"),
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "allocationsSpId": $(this).find(".item-cell").attr("data-allocations-sp-id"),
                        "allocationsDutyId": $(this).find(".item-cell").attr("data-allocations-duty-id"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date")
                    }
                    AuthoriseOver12Popup(data);
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-mark-twelve") == '-1') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "breachapproval": {
                name: 'WTD Breach Approval',
                icon: "mark",
                visible: function(key, opt, e) {
                    var allocObj = $(this).find(".alloc");
                    let markWiadVal = allocObj.attr('data-mark-wiad-option');
                    if((markWiadVal == '') || (markWiadVal == 'false') || (markWiadVal == false) || (allocObj.attr("data-unique-id") == 0)){
                        return false;
                    } else {
                        if(((markWiadVal != '') || (markWiadVal == 'true') || (markWiadVal == true) || (allocObj.attr("data-unique-id") == 0)) && (allocObj.attr("data-edit-duty-flag") == 0)){
                            return false;
                        } else {
                            if(allocObj.attr("data-edit-screen") == 1){
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                },
                callback: function() {
                    dutyCell = $(this).find(".item-cell");
                    var schPersonId = dutyCell.attr('data-scheduling-person');
                    var dutyDate = dutyCell.attr('data-date');
                    var teamId = dutyCell.attr('data-scheduling-team-id');
                    var cellId = dutyCell.attr('data-unique-id');
                    wtdBreachApprovalPopup(schPersonId,dutyDate,teamId,cellId);
                }
            },
            "deleteWTDbreach": {
                name: 'Delete WTD Breach',
                icon: "remove",
                visible: function(key, opt, e) {
                    var allocObj = $(this).find(".alloc");
                    let markWiadVal = allocObj.attr('data-mark-wiad-option');
                    if((markWiadVal == '') || (markWiadVal == 'false') || (markWiadVal == false) || (allocObj.attr("data-unique-id") == 0)  || ($("#staRole").val()==0)){
                        return false;
                    } else {
                        if(((markWiadVal != '') || (markWiadVal == 'true') || (markWiadVal == true) || (allocObj.attr("data-unique-id") == 0)) && (allocObj.attr("data-edit-duty-flag") == 0)){
                            return false;
                        } else {
                            if(allocObj.attr("data-edit-screen") == 1){
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                },
                callback: function() {
                    dutyCell = $(this).find(".item-cell");
                    deleteWTDBreachDetails(dutyCell);
                }
            },
            "approveWtd": {
                name: "Working Time Directive Approval",
                callback: function(key, options) {
                    var data = {
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date")
                    }

                    $this = $(this);

                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/mark-action.php",
                        data: data,
                        beforeSend: function(jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (response) {
                            if (response.success === true) {
                                $this.removeClass('cross-red');
                                $this.addClass('cross-blue');

                            } else {
                                customAlert('Something went wrong. Please try again later.');
                            }
                        }
                    });

                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if((draggable2.attr("data-wtd") == 1) && (draggable2.attr("data-edit-screen") == 1)){
                        return true;
                    }
                }
            },
            "deleteWtd": {
                name: "Delete the WTD Breach",
                callback: function(key, options) {
                    var data = {
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date")
                    }

                    $this = $(this);

                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/mark-action.php",
                        data: data,
                        beforeSend: function(jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (response) {
                            response = $.parseJSON(response);
                            if (response.success === true) {
                                $this.removeClass('cross-red');
                                $this.removeClass('cross-blue');

                            } else {
                                customAlert('Something went wrong. Please try again later.');
                            }
                        }
                    });

                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if(((draggable2.attr("data-wtd") == 1) || (draggable2.attr("data-wtd") == 2)) && (draggable2.attr("data-edit-screen") == 1)){
                        return true;
                    }
                }
            },
            "viewRequest": {
                name: "View Request Details",
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "dutyName": $(this).find(".item-cell").attr("data-duty-name"),
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date"),
                        "teamId": $(this).find(".item-cell").attr("data-scheduling-team-id"),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly")
                    }
                    fetchDutyRequestsPopUp(data);
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if (($("#requestIcon" + "_" + $(this).find(".item-cell").attr("data-unique-id")).hasClass('multirequestvaialble') == true) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "sep3": "---------",
            "sickness": {
                name: "Sickness",
                icon: "sick",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-edit-duty-flag") == 1) && (draggable2.attr("data-is-home-team") == 1) && (draggable2.attr("data-duty-name") !='Sick') && (draggable2.attr("data-duty-name") !='U-Sick') && (draggable2.attr("data-duty-name") !='-Sick') && ($("#redEuro" + "_" + draggable2.attr("data-unique-id")).hasClass('activemarkovertime') == false) && (draggable2.attr("data-leave-starttime") == 0) && (draggable2.attr("data-leave-endtime") == 0) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    let cellStartDate = $(this).find(".item-cell").attr("date-start-date");
                    let cellEndDate = $(this).find(".item-cell").attr("date-end-date");
                    let wEndDate = new Date(cellEndDate.replace(/-/g, '\/'));
                    wEndDate.setDate(wEndDate.getDate() + 1);
                    let offsetwEndDate = wEndDate.getTimezoneOffset();
                    wEndDate = new Date(wEndDate.getTime() - (offsetwEndDate*60*1000));
                    cellEndDate = wEndDate.toISOString().split('T')[0];
                    var data = {
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date"),
                        "teamId": $("#adhocFilter").val(),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly"),
                        "iday": $(this).find(".item-cell").attr("data-iday"),
                        "action": 'showsicknesspopup',
                        "allocid": $(this).find(".item-cell").attr("data-id"),
                        "weekStartDate": cellStartDate,
                        "weekEndDate": cellEndDate,
                        "weeknumber": $('#weekNumber').val(),
                        "date": $('#date').val(),
                        "dutyName": $(this).find(".item-cell").attr("data-duty-name"),
                        "fDateStartDate": $(this).find(".item-cell").attr("date-start-date"),
                        "fDateRangeDays": $(this).find(".item-cell").attr("date-range-days"),
                        "fDataRowStart": $(this).find(".item-cell").attr("data-row-start"),
                        "fDataRowEnd": $(this).find(".item-cell").attr("data-row-end"),
                        "fDataSchedulingTeamId": $(this).find(".item-cell").attr("data-scheduling-team-id"),
                        "fDateEndDate": $(this).find(".item-cell").attr("date-end-date"),
                        "fDataDutyDuration": $(this).find(".item-cell").attr("data-duty-duration"),
                        "fDataMasterdutyid": $(this).find(".item-cell").attr("data-masterdutyid"),
                        "fDataIsactive": $(this).find(".item-cell").attr("data-isactive"),
                        "allocationsSpId": $(this).find(".item-cell").attr("data-allocations-sp-id"),
                        "allocationsDutyId": $(this).find(".item-cell").attr("data-allocations-duty-id"),
                        "isChargingPresent": $(this).find(".item-cell").attr("data-charging-present"),
                    }
                    SicknessOpenPopup(data);
                }
            },
            "editsickness": {
                name: "Edit Sickness",
                icon: "sick",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-edit-duty-flag") == 0) && (draggable2.attr("data-is-home-team") == 1) && ((draggable2.attr("data-duty-name") =='Sick') || (draggable2.attr("data-duty-name") =='U-Sick') || (draggable2.attr("data-duty-name") =='-Sick')) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    let cellStartDate = $(this).find(".item-cell").attr("date-start-date");
                    let cellEndDate = $(this).find(".item-cell").attr("date-end-date");
                    let wEndDate = new Date(cellEndDate.replace(/-/g, '\/'));
                    wEndDate.setDate(wEndDate.getDate() + 1);
                    let offsetwEndDate = wEndDate.getTimezoneOffset();
                    wEndDate = new Date(wEndDate.getTime() - (offsetwEndDate*60*1000));
                    cellEndDate = wEndDate.toISOString().split('T')[0];
                    var data = {
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date"),
                        "teamId": $("#adhocFilter").val(),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly"),
                        "iday": $(this).find(".item-cell").attr("data-iday"),
                        "action": 'showsicknesspopup',
                        "allocid": $(this).find(".item-cell").attr("data-id"),
                        "weekStartDate": cellStartDate,
                        "weekEndDate": cellEndDate,
                        "weeknumber": $('#weekNumber').val(),
                        "date": $('#date').val(),
                        "dutyName": $(this).find(".item-cell").attr("data-duty-name"),
                        "fDateStartDate": $(this).find(".item-cell").attr("date-start-date"),
                        "fDateRangeDays": $(this).find(".item-cell").attr("date-range-days"),
                        "fDataRowStart": $(this).find(".item-cell").attr("data-row-start"),
                        "fDataRowEnd": $(this).find(".item-cell").attr("data-row-end"),
                        "fDataSchedulingTeamId": $(this).find(".item-cell").attr("data-scheduling-team-id"),
                        "fDateEndDate": $(this).find(".item-cell").attr("date-end-date"),
                        "fDataDutyDuration": $(this).find(".item-cell").attr("data-duty-duration"),
                        "fDataMasterdutyid": $(this).find(".item-cell").attr("data-masterdutyid"),
                        "fDataIsactive": $(this).find(".item-cell").attr("data-isactive"),
                        "allocationsSpId": $(this).find(".item-cell").attr("data-allocations-sp-id"),
                        "allocationsDutyId": $(this).find(".item-cell").attr("data-allocations-duty-id"),
                        "isChargingPresent": $(this).find(".item-cell").attr("data-charging-present"),
                    }
                    SicknessOpenPopup(data);
                }
            },
            "deletesickness": {
                name: "Delete Sickness",
                icon: "delete",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-edit-duty-flag") == 0) && (draggable2.attr("data-is-home-team") == 1) && ((draggable2.attr("data-duty-name") =='Sick') || (draggable2.attr("data-duty-name") =='U-Sick') || (draggable2.attr("data-duty-name") =='-Sick') ||  (draggable2.attr("data-duty-name") =='-Sick')) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                },
                callback: function(key, options) {
                var allocationId = $(this).find(".item-cell").attr("data-id");
                var allocationSPId = $(this).find(".item-cell").attr("data-allocations-sp-id");
                var cell = $(this);
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/edits/restoreduty.php",
                        data :{
                            teamId: $("#adhocFilter").val(),
                            isEdited:0,
                            isShiftleader:0,
                            allocationId: allocationId,
                            allocationSPId: allocationSPId,
                            dutyName :draggable2.attr("data-duty-name")
                        },
                        beforeSend: function(jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (data) {
                            refreshAllocatedSectionTr(cell.find(".item-cell").attr("data-scheduling-person"),cell.find(".item-cell").attr("data-row-id"),cell.find(".item-cell").attr("data-date"),cell.find(".item-cell").attr("data-duty-name"),cell.find(".item-cell").attr("data-row-start"),cell.find(".item-cell").attr('data-row-end'),cell.find(".item-cell").attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',cell.find(".item-cell").attr('data-id'),0,0,'Yes');

                        }
                    });
                }
            },
            "charging": {
                name: "Charging",
                icon: "charge",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".alloc");
                    if ((draggable2.attr("data-duty-name") != 'U') && (draggable2.attr("data-unique-id") != 0) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    var schedulingPersonId = $(this).find(".alloc").attr("data-scheduling-person");
                    var allocid = $(this).find(".alloc").attr("data-id");
                    var teamId = $("#searchTeamId").val();
                    var allocationsDutyId = $(this).find(".alloc").attr("data-allocations-duty-id");
                    var allocationsSpId = $(this).find(".alloc").attr("data-allocations-sp-id");
                    ChargingOpenPopup(schedulingPersonId, allocid, teamId, allocationsDutyId, allocationsSpId);
                }
            },
            "sep5": "---------",
            "history": {
                name: "History",
                icon: "history",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".alloc");
                    if (draggable2.attr('data-unique-id') != 0) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".item-cell");
                    getDutyHistory(selectedDay);
                }
            },
            "sep6": "---------",
            "addperson": {
                name: "Add Person",
                icon: "assign",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".alloc");
                    if ($('#showWeeks').val() == 1) {
                        if(($('#date').val() == '') && (draggable2.attr("data-edit-screen") == 1)){
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                callback: function(key, options) {
                    draggable = $(this).find(".alloc");

                    //Additional Person Modal
                    var dialogForm = $("#dialog-form");
                    dialogForm.html('');
                    addPersonDialog = dialogForm.dialog({
                        autoOpen: false,
                        height: 250,
                        width: 350,
                        modal: true,
                        buttons: [
                            {
                                id: "button-add-person",
                                text: "Add Person",
                                click: function() {
                                    var schedulingPerson = addPersonDialog.find($("[name='schedulingPersonId']"));
                                    var data = $("#editWeeklyAllocations").serialize() + "&schedulingPersonId=" + schedulingPerson.chosen().val() + "&dataId=" + draggable.attr("data-id");

                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/weekly/actions/add-person.php",
                                        data: data,
                                        success: function (response) {
                                            response = $.parseJSON(response);
                                            if (response.success) {
                                                addPersonDialog.dialog("close");
                                                $('#loading').show();
                                                addPersonDialog.html('');
                                                addPersonDialog.dialog("close");
                                                addPersonDialog.dialog('destroy');
                                                reloadeditweeklygrid('','','','','ALLOC');
                                            } else {
                                                addPersonDialog.dialog("close");
                                                customAlert(response.errMsg);

                                            }
                                        }
                                    });
                                }
                            },
                            {
                                id: "button-cancel",
                                text: "Cancel",
                                click: function() {
                                    addPersonDialog.html('');
                                    addPersonDialog.dialog("close");
                                    addPersonDialog.dialog('destroy');
                                }
                            }
                        ],
                        open: function(){
                            $(".ui-dialog-buttonpane button:contains('Add Person')").button('disable');
                        },
                        beforeClose: function(event,ui){
                            if ($(event.currentTarget).hasClass('ui-dialog-titlebar-close')){
                                event.preventDefault();
                                addPersonDialog.html('');
                                addPersonDialog.dialog("close");
                                addPersonDialog.dialog('destroy');
                            }
                        }
                    });

                    var data = $("#editWeeklyAllocations").serialize() + "&dutydate=" + draggable.attr("data-date") + "&dataId=" + draggable.attr("data-id");
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/modals/add-person-modal.php",
                        data: data,
                        success: function (response) {
                            $('.ui-dialog-title').html("Add New Person");
                            addPersonDialog.html(response).dialog({
                                modal: true
                            }).dialog('open');
                            $("[name='schedulingPersonId']").chosen();
                            $("#button-add-person").attr("disabled", true);
                        }
                    });
                }
            },
            "removeAdditionalPerson": {
                name: "Remove From Week",
                icon: "unmark",
                selected: false,
                visible: function(key, opt) {
                    dutyCell = $(this).find(".item-cell");
                    // Disable this item if the menu was triggered on a div
                    if ((dutyCell.attr("data-is-home-team") == 0) && ($('#showWeeks').val() == 1)) {
                        if(($('#date').val() == '') && (dutyCell.attr("data-edit-screen") == 1)){
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                callback: function() {
                    dutyCell = $(this).find(".item-cell");
                    var data = {
                        "cellId": dutyCell.attr("data-id"),
                        "schedulingPersonId": dutyCell.attr("data-scheduling-person")
                    }
                    $('#loading').show();
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/remove-addiotional-person.php",
                        data: data,
                        success: function (response) {
                            response = $.parseJSON(response);
                            if (response.success == true) {
                                $('.schRowLeft'+dutyCell.attr("data-scheduling-person")).remove();
                                $('.schRowRight'+dutyCell.attr("data-scheduling-person")).remove();
                                window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
                                if (!window.indexedDB)
                                {
                                    console.log("Your browser doesn't support a stable version of IndexedDB.");
                                }else
                                {
                                    const request = window.indexedDB.open('allocDatabase', 3);
                                    request.onsuccess = function () {
                                        const db = request.result;
                                        if(db.objectStoreNames.length){
                                            const transaction = db.transaction("weeklyData", "readwrite");
                                            const store = transaction.objectStore("weeklyData");
                                            const idQuery = store.get(1);
                                            idQuery.onsuccess = function () {
                                                let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
                                                let dataArr1 = [];
                                                if(dataArr.length > 0){
                                                    dataArr.forEach((number, index, rows) => {
                                                        if(rows[index]['SchedulingPersonID'] != dutyCell.attr("data-scheduling-person")){
                                                            dataArr1.push(rows[index]);
                                                        }
                                                    });
                                                }
                                            saveDataInIndexedDb(dataArr1);
                                            getAllocatedSort(7);
                                            };
                                            transaction.oncomplete = function () {
                                                db.close();
                                            };
                                        } else {
                                            $('#loading').hide();
                                            $('#loadingWeekly').hide();
                                            customAlert("Please close and reopen your browser");
                                        }
                                    };
                                }
                            } else {
                                customAlert(response.errMsg);
                            }
                        }
                    });
                }
            },
            "verifywtdbreach": {
                name: "Check WTD 17 Weeks",
                icon: 'mark',
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-unique-id") != 0) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }else {
                        return false;
                    }
                },
                callback: function() {
                    dutyCell = $(this).find(".item-cell");
                    verifyWtdBreachApproval(dutyCell);
                }
            },
        }
    }); //context menu end

    $.contextMenu({
        selector: '.context-menu-leave',
        zIndex: function($trigger, opt){
            return 999;
        },
        events: {
            activated : function(options) {
                if(options.$menu.css('top') == '0px') {
                    options.$menu.css('top', Math.abs(Math.round(Math.abs(options.$trigger.offset().top - options.$menu.height()) - 4)) + 'px');
                }
            }
        },
        callback: function (key, options) {
            draggable = $(this).find(".item-cell");

            var data = {
                "ID" : draggable.attr("data-id"),
                "schedulingPersonId" : draggable.attr("data-scheduling-person"),
                "dutyDate" : draggable.attr("data-date"),
                "teamId" : $("#adhocFilter").val(),
                "action" : key,
                "dutyAttention" : draggable.attr("data-attention"),
				'dutyDate' : draggable.attr("data-date"),
				'allocationsDutyId' : draggable.attr("data-allocations-duty-id"),
				'allocationsSpId' : draggable.attr("data-allocations-sp-id"),
				'allocationsSchPer' : draggable.attr("data-scheduling-person")
            }

            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/mark-action.php",
                data: data,
                success: function (response) {
                    response = $.parseJSON(response);
                    if(response.success === true){
                        if(key == 'attention'){
                                draggable.parent().addClass('attentionClass');
                                draggable.parent().removeAttr("style")
                                draggable.attr('data-attention',1);
                            }
                        if(key == 'unattention'){
                                draggable.parent().removeClass('attentionClass');
                                draggable.parent().removeClass('attentionCopyDutyClass');
                                draggable.attr('data-attention',0);
                                draggable.parent().css("background-color", "#"+draggable.attr("data-bgcolor"));
                                draggable.parent().css('color','#'+draggable.attr("data-font-color"));
                            }
                    }
                }
            });
        },
        items: {
            "comment": { name: "Comments", icon: "comment", selected: false, callback: function (key, options) {
                selectedDay = $(this).find(".item-cell");
                addComments(selectedDay);
            }},
            "sep1": "---------",
            "manageleave": {
                name: "Leave",
                icon: 'manageleave',
                selected: false,
                callback: function(key, options) {
                    openLeavePopup($(this).find(".item-cell"));
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if ((draggable2.attr("data-duty-name").toUpperCase() != 'SICK') && (draggable2.attr("data-duty-name").toUpperCase() != '-SICK') && (draggable2.attr("data-duty-name").toUpperCase() != 'ABSENT') && (draggable2.attr("data-duty-name").toUpperCase() != 'U-SICK') && ($("#redEuro" + "_" + $(this).find(".item-cell").attr("data-unique-id")).hasClass('activemarkovertime') == false) && (draggable2.attr("data-is-home-team") == 1) && ((draggable2.attr("data-charging-present") == 0) || (draggable2.attr("data-charging-present") == "0") || (draggable2.attr("data-charging-present") == undefined)) && (draggable2.attr("data-eleven-icon") == '0') && (draggable2.attr("data-mark-twelve") != '1') && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "attention": { name: "Mark for Attention" , icon: "exclaim", selected: false,
                        visible: function(key, opt){
                            draggable2 = $(this).find(".item-cell");
                            // Disable this item if the menu was triggered on a div
                            if((draggable2.attr("data-attention") == 0) && (draggable2.attr("data-edit-screen") == 1)){
                                return true;
                            }
                        }
            },
            "unattention": { name: "Unmark for Attention", icon: "exclaim", selected: false,
                visible: function(key, opt){
                    draggable2 = $(this).find(".item-cell");
                    if((draggable2.attr("data-attention") == 1 || draggable2.attr("data-attention") == 2) && (draggable2.attr("data-edit-screen") == 1)){
                        return true;
                    }
                }
            },
            "viewRequest": {
                name: "View Request Details",
                selected: false,
                callback: function(key, options) {
                    var data = {
                        "dutyName": $(this).find(".item-cell").attr("data-duty-name"),
                        "schedulingPersonId": $(this).find(".item-cell").attr("data-scheduling-person"),
                        "dutyDate": $(this).find(".item-cell").attr("data-date"),
                        "teamId": $(this).find(".item-cell").attr("data-scheduling-team-id"),
                        "dateonly": $(this).find(".item-cell").attr("data-dateonly")
                    }
                    fetchDutyRequestsPopUp(data);
                },
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if (($("#requestIcon" + "_" + $(this).find(".item-cell").attr("data-unique-id")).hasClass('multirequestvaialble') == true) && (draggable2.attr("data-edit-screen") == 1)) {
                        return true;
                    }
                }
            },
            "history": { name: "History", icon:"history", selected: false, callback: function (key, options) {
                selectedDay = $(this).find(".item-cell");
                getDutyHistory(selectedDay);
            }}
        }
    });

    $.contextMenu({
        selector: '.context-menu-unallocated',
        zIndex: function($trigger, opt){
            return 999;
        },
        events: {
            activated : function(options) {
                if(options.$menu.css('top') == '0px') {
                    options.$menu.css('top', Math.abs(Math.round(Math.abs(options.$trigger.offset().top - options.$menu.height()) - 4)) + 'px');
                }
            }
        },
        callback: function(key, options) {
        },
        items: {
            "delete": {
                name: "Delete Duty",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    dutyCell = $(this).find(".unallocated");
                    if ((dutyCell.attr("data-edit-screen") == 1) && (dutyCell.hasClass('unallocother-drag-drop') == false)) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".unallocated");
                    var date = new Date(selectedDay.attr("data-date").replace(/-/g, '\/'));
                    var dayName = date.toLocaleDateString('en-GB', {
                        weekday: 'long'
                    });
					let splitWeekNum =$('#weekNumber').val().split('/');
					let weekNum = splitWeekNum[1]+splitWeekNum[0];
                    var dutyName = selectedDay.attr("data-duty-name");
                     var message = " Do you really wish to delete Unallocated Duty '" + dutyName + "' and any jobs attached to the duty from " + dayName + " " + specialFormat(new Date(selectedDay.attr("data-date").replace(/-/g, '\/'))) + "?";

                    customConfirmModal(
                        message,
                        function(){
                            var data = {
                                "ID": selectedDay.attr("data-id"),
								"allocationsDutyId": selectedDay.attr("data-allocations-duty-id")
                            };
                            $.ajax({
                                type: "post",
                                url: "/page-includes/allocations/weekly/actions/delete-allocation-jobs.php",
                                data: data,
                                beforeSend: function(jqXHR, settings){
                                    $('#loading').hide();
                                },
                                success: function (response) {
                                    response = $.parseJSON(response);
                                    if (response.success === true) {
                                        let currentUnAllocDuty = $.parseJSON($('#unAllocDutyData').val());
                                        let dutyInstanceIds = selectedDay.attr("data-duty-instance-ids");
                                        let splitDutyInstanceIds = dutyInstanceIds.split(',');
                                        let oldSelectedDataId = selectedDay.attr("data-allocations-duty-id");
                                        if(splitDutyInstanceIds.length > 1){
                                            let existsIds = selectedDay.attr("data-duty-instance-ids");
                                            existsIds = existsIds.replaceAll(selectedDay.attr("data-allocations-duty-id"),'');
                                            existsIds = existsIds.match(/\d+/g);
                                            valueDutyId = existsIds[0];
                                            remailDutyStr = existsIds.join(',');
											if(remailDutyStr != valueDutyId)
											{
												remailDutyStr = remailDutyStr;
											}
                                            let dutyCount = parseInt(selectedDay.attr('data-duty-instances'))-1;
                                            $('#dutyCountDiv'+selectedDay.attr("data-id")).attr('id','dutyCountDiv'+valueDutyId);
											let uId = valueDutyId + '_' + selectedDay.attr("data-date");
											let uIdOld = oldSelectedDataId + '_' + selectedDay.attr("data-date");
                                            $('#rowUnqId_'+uIdOld).attr('data-id',valueDutyId).attr('data-duty-count',dutyCount).attr('data-unique-id',uId).attr('data-duty-instances',dutyCount).attr('data-duty-instance-ids',remailDutyStr).attr('data-next-instance-id',valueDutyId).attr('ondblclick', "editAllocateDuty('editduty','edit', '" + uId + "')").attr('id','rowUnqId_'+uId);
                                            $('#colUnqId_'+uIdOld).attr('data-id',valueDutyId).attr('data-duty-count',dutyCount).attr('data-unique-id',uId).attr('data-duty-instances',dutyCount).attr('data-duty-instance-ids',remailDutyStr).attr('data-next-instance-id',valueDutyId).attr('id','colUnqId_'+uId).attr('data-allocations-duty-id',valueDutyId);
											$('#dutyCountDiv'+uIdOld).attr('id','dutyCountDiv'+uId)
                                            if(dutyCount > 1){
                                                $('#rowUnqId_'+uId).addClass('cornerIcon-Unalloc cornerIcon-Grey');
                                                $('#dutyCountDiv'+uId).html(dutyCount);
												$('#rowUnqId_'+uIdOld).attr('id','dutyCountDiv'+valueDutyId);
                                            } else {
                                                $('#rowUnqId_'+uId).removeClass('cornerIcon-Unalloc cornerIcon-Grey');
                                                $('#dutyCountDiv'+uId).html('');
												$('#rowUnqId_'+uIdOld).attr('id','dutyCountDiv'+valueDutyId);
                                            }
                                            currentUnAllocDuty[selectedDay.attr("data-duty-name") + selectedDay.attr("data-row-start") + selectedDay.attr("data-row-end")][selectedDay.attr("data-date")].DutyInstances = dutyCount;
                                        } else {
                                            for ([key, value] of Object.entries(currentUnAllocDuty)) {
                                                let cellDutyName = selectedDay.attr("data-duty-name");
                                                let cellDutyDate = selectedDay.attr("data-date");
                                                let cellDutyStartTime = selectedDay.attr("data-row-start");
                                                let cellDutyEndTime = selectedDay.attr("data-row-end");
                                                if(key.toLowerCase() == (cellDutyName + cellDutyStartTime + cellDutyEndTime).toLowerCase()){
                                                    if(Object.keys(value).length > 1){
                                                        cellDeleteUnallocated(selectedDay);
                                                        delete currentUnAllocDuty[key][cellDutyDate];
                                                    } else {
                                                        $('.ulrow_'+selectedDay.attr('data-row-counter')).remove().parent().remove();
                                                        delete currentUnAllocDuty[key];
                                                    }
                                                }
                                            }
                                            valueDutyId = oldSelectedDataId;
                                        }
                                        $('#unAllocDutyData').val(JSON.stringify(currentUnAllocDuty));
                                        if($('#showHideCount').prop('checked') == true){
                                            reloadShiftCountingGrid();
                                        }
										window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
										if (!window.indexedDB)
										{
											console.log("Your browser doesn't support a stable version of IndexedDB.");
										}else
										{
											const request = window.indexedDB.open('unAllocDatabase', 3);
											request.onsuccess = function () {
												const db = request.result;
                                                if(db.objectStoreNames.length){
    												const transaction = db.transaction("weeklyData", "readwrite");
    												const store = transaction.objectStore("weeklyData");
    												const idQuery = store.get(1);
    												idQuery.onsuccess = function () {
    													let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
    													let dataArr1 = [];
                                                        if(dataArr.length > 0){
                                                            dataArr.forEach((number, index, rows) => {
                                                                if(rows[index][15] != oldSelectedDataId){
                                                                    if((rows[index][0] == $('#colUnqId_'+valueDutyId + '_' + selectedDay.attr("data-date")).attr("data-duty-name")) && (rows[index][4] == $('#colUnqId_'+valueDutyId + '_' + selectedDay.attr("data-date")).attr("data-row-start")) && (rows[index][5] == $('#colUnqId_'+valueDutyId + '_' + selectedDay.attr("data-date")).attr("data-row-end")) && (rows[index][9] == $('#colUnqId_'+valueDutyId + '_' + selectedDay.attr("data-date")).attr("data-date"))){
                                                                        let splitRemainInstIds = $('#colUnqId_'+valueDutyId + '_' + selectedDay.attr("data-date")).attr("data-duty-instance-ids").split(',');
                                                                        rows[index][14] = splitRemainInstIds.length - 1;
                                                                        rows[index]['InstanceIds'] = $('#colUnqId_'+valueDutyId + '_' + selectedDay.attr("data-date")).attr("data-duty-instance-ids");
                                                                    }
                                                                    dataArr1.push(rows[index]);
                                                                }
                                                            });
                                                        }
    													saveDataInIndexedDb(dataArr1, 1);
                                                        if(!isNaN($.cookie('unAllocSortDate'))){
                                                            getUnAllocatedSort($.cookie('unAllocSortDate'));
                                                        }
    												};
    												transaction.oncomplete = function () {
    													db.close();
    												};
                                                } else {
                                                    $('#loading').hide();
                                                    $('#loadingWeekly').hide();
                                                    customAlert("Please close and reopen your browser");
                                                }
											};
										}
                                    } else {
                                        customAlert('Error in deleting jobs');
                                    }
                                }
                            });
                        },
                        function(){},
                        0,
                        'EditWeekly'
                    );
                }
            },
            "edit": {
                name: 'Edit Duty',
                visible: function(key, opt, e) {
                    dutyCell = $(this).find(".unallocated");
                    if (dutyCell.hasClass('unallocother-drag-drop') == false) {
                        return true;
                    }
                },
                icon: "edit",
                selected: false,
                callback: function(key, opt, e) {
                    draggable = $(this).find(".unallocated");
                    editAllocateDuty('editduty','edit', draggable.attr("data-allocations-duty-id") +'_'+draggable.attr("data-date"));
                }
            },
            "allocatedjobs": {
                name: "View Jobs",
                icon: "jobs",
                selected: false,
                visible: function(key, options) {
                    dutyCell = $(this).find(".unallocated");
                    if (dutyCell.hasClass('unallocother-drag-drop') == false) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".unallocated");
                    dutyAllocatedJobs(selectedDay);
                }
            },
            "history": {
                name: "History",
                icon: "history",
                selected: false,
                visible: function(key, options) {
                    dutyCell = $(this).find(".unallocated");
                    if (dutyCell.hasClass('unallocother-drag-drop') == false) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".unallocated");
                    getDutyHistory(selectedDay);
                }
            }
        }
    });

    $.contextMenu({
        selector: '.context-menu-additional',
        zIndex: function($trigger, opt){
            return 999;
        },
        events: {
            activated : function(options) {
                if(options.$menu.css('top') == '0px') {
                    options.$menu.css('top', Math.abs(Math.round(Math.abs(options.$trigger.offset().top - options.$menu.height()) - 4)) + 'px');
                }
            }
        },
        callback: function(key, options) {

        },
        items: {
            "schedulerslist": {
                name: "Schedulers",
                icon: "user",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".alloc");
                    if ((draggable2.attr("data-duty-name").toUpperCase() != 'U') && (draggable2.attr("data-duty-name").toUpperCase() != 'U-SICK') && (draggable2.attr("data-duty-name").toUpperCase() != '-SICK') && (draggable2.attr("data-duty-name").toUpperCase() != 'SICK') && (draggable2.attr("data-duty-name").toUpperCase() != 'LEAVE') && (draggable2.attr("data-duty-name").toUpperCase() != 'OFF LEAVE') && (draggable2.attr("data-duty-name").toUpperCase() != 'ABSENT')) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    selectedDay = $(this).find(".alloc");
                    schedulersList(selectedDay);
                }
            },
            "unattention": {
                name: "Unmark for Attention",
                icon: "exclaim",
                selected: false,
                visible: function(key, opt) {
                    draggable2 = $(this).find(".item-cell");
                    if((draggable2.attr("data-attention") == 1 || draggable2.attr("data-attention") == 2)  && (draggable2.attr("data-edit-duty-flag") == 1) && (draggable2.attr("data-edit-screen") == 1)){
                        return true;
                    }
                },
                callback: function(key, options) {
                    draggable = $(this).find(".item-cell");

                    var data = {
                        "ID": draggable.attr("data-id"),
                        "schedulingPersonId": draggable.attr("data-scheduling-person"),
                        "dutyDate": draggable.attr("data-date"),
                        "teamId": $("#adhocFilter").val(),
                        "action": key,
                        "dutyAttention": draggable.attr("data-attention"),
						'allocationsDutyId' : selectedDayInstance.attr("data-scheduling-person"),
						'allocationsSpId' : selectedDayInstance.attr("data-allocations-sp-id"),
						'allocationsSchPer' : selectedDayInstance.attr("data-scheduling-person")
                    }

                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/mark-action.php",
                        data: data,
                        beforeSend: function(jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (response) {
                            response = $.parseJSON(response);
                            if (response.success === true) {
                                if (key == 'unattention') {
                                    $("#rowUnqId_" + draggable.attr("data-unique-id")).removeClass('attentionClass');
                                    $("#rowUnqId_" + draggable.attr("data-unique-id")).removeClass('attentionCopyDutyClass');
                                    $("#colUnqId_" + draggable.attr("data-unique-id")).attr('data-attention', 0);
                                    setTimeout(function() {
                                       if ((draggable.attr("data-row-start")==0) && (draggable.attr("data-row-end")==0)){
                                            $("#rowUnqId_" + draggable.attr("data-unique-id")).css("background-color", "#" + draggable.attr("data-bgcolor")).css("color", "#" + draggable.attr("data-font-color"));
                                        } else {
                                            $("#rowUnqId_" + draggable.attr("data-unique-id")).css("background-color", "#" + draggable.attr("data-bg-color"));
                                        }
                                    }, 500);

                                    $updatedElement = $("#colUnqId_" + draggable.attr("data-unique-id"));
                                    refreshAllocatedSectionTr($updatedElement.attr("data-scheduling-person"),$updatedElement.attr("data-row-id"),$updatedElement.attr("data-date"),$updatedElement.attr("data-duty-name"),$updatedElement.attr("data-row-start"),$updatedElement.attr('data-row-end'),$updatedElement.attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$updatedElement.attr('data-id'),0,0,'Yes');                                }
                            } else {
                                customAlert('Something went wrong. Please try again later.');
                            }
                        }
                    });
                }
            },
            "removeAdditionalPerson": {
                name: "Remove From Week",
                icon: "unmark",
                selected: false,
                visible: function(key, opt) {
                    dutyCell = $(this).find(".item-cell");
                    if ((dutyCell.attr("data-is-home-team") == 0) && ($('#showWeeks').val() == 1) && (dutyCell.attr("data-edit-screen") == 1)) {
                        if($('#date').val() == ''){
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                callback: function() {
                    dutyCell = $(this).find(".item-cell");
                    var data = {
                        "cellId": dutyCell.attr("data-id"),
                        "schedulingPersonId": dutyCell.attr("data-scheduling-person")
                    }
                    $('#loading').show();
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/remove-addiotional-person.php",
                        data: data,
                        success: function (response) {
                            response = $.parseJSON(response);
                            if (response.success == true) {
                                $('.schRowLeft'+dutyCell.attr("data-scheduling-person")).remove();
                                $('.schRowRight'+dutyCell.attr("data-scheduling-person")).remove();
								window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
								if (!window.indexedDB)
								{
									console.log("Your browser doesn't support a stable version of IndexedDB.");
								}else
								{
									const request = window.indexedDB.open('allocDatabase', 3);
									request.onsuccess = function () {
										const db = request.result;
                                        if(db.objectStoreNames.length){
    										const transaction = db.transaction("weeklyData", "readwrite");
    										const store = transaction.objectStore("weeklyData");
    										const idQuery = store.get(1);
    										idQuery.onsuccess = function () {
    											let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
    											let dataArr1 = [];
                                                if(dataArr.length > 0){
                                                    dataArr.forEach((number, index, rows) => {
                                                        if(rows[index]['SchedulingPersonID'] != dutyCell.attr("data-scheduling-person")){
                                                            dataArr1.push(rows[index]);
                                                        }
                                                    });
                                                }
    										saveDataInIndexedDb(dataArr1);
    										getAllocatedSort(7);
    										};
    										transaction.oncomplete = function () {
    											db.close();
    										};
                                        } else {
                                            $('#loading').hide();
                                            $('#loadingWeekly').hide();
                                            customAlert("Please close and reopen your browser");
                                        }
									};
								}
                            } else {
                                customAlert(response.errMsg);
                            }
                        }
                    });
                }
            }
        }
    }); //context menu end
});

function MarkRequest(data) {
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/mark-action.php",
        data: data,
        success: function (response) {
            response = $.parseJSON(response);
            if (response.success === true) {
                if (data.type == 'markrequest') {
                    $("#dutytipID_" + data.dateonly + "_" + data.schedulingPersonId).removeClass('color');
                    $("#dutytipID_" + data.dateonly + "_" + data.schedulingPersonId).addClass('requestClass');
                    if(data.dutyName == 'U'){
                        $("#dutytipID_" + data.dateonly + "_" + data.schedulingPersonId).css('font-weight','bold');
                    }
                }
                if (data.type == 'unmarkrequest') {
                    $("#dutytipID_" + data.dateonly + "_" + data.schedulingPersonId).removeClass('requestClass');
                    if(data.dutyName == 'U'){
                        $("#dutytipID_" + data.dateonly + "_" + data.schedulingPersonId).css('font-weight','');
                    }
                }
                $updatedElement = $('#colUnqId_' +  data.allocationsSpId + '_' + data.dutyDate).length > 0 ? $('#colUnqId_' +  data.allocationsSpId + '_' + data.dutyDate) : $('#colUnqId_' +  data.allocationsSchPer + '_' + data.dutyDate);
                refreshAllocatedSectionTr($updatedElement.attr("data-scheduling-person"),$updatedElement.attr("data-row-id"),$updatedElement.attr("data-date"),$updatedElement.attr("data-duty-name"),$updatedElement.attr("data-row-start"),$updatedElement.attr('data-row-end'),$updatedElement.attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$updatedElement.attr('data-id'),0,0,'Yes');
            } else {
                customAlert('Something went wrong. Please try again later.');
            }
        }
    });
}


function addComments(selectedDayInstance) {
    let id = selectedDayInstance.attr("data-id");
    let scheduledPersonName = $('.peronname_' + selectedDayInstance.attr('data-scheduling-person')).attr('data-order');
	let allocationsDutyId = selectedDayInstance.attr("data-allocations-duty-id");
	let allocationsSpId = selectedDayInstance.attr("data-allocations-sp-id");
    let dataGet = {
        id: id,
		allocationsDutyId: allocationsDutyId,
		allocationsSpId: allocationsSpId
    };
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/get-comments.php",
        data: dataGet,
        success: function (res) {
            res = $.parseJSON(res);
            if (res.status) {
                let teamId = selectedDayInstance.attr("data-scheduling-team-id");
                let weekNum = $('#weekNumber').val();
                let splitWeekNum = weekNum.split('/');
				res.data.DutyName = res.data.DutyName ? res.data.DutyName : 'U';
                let dataCommentModal = {
                    'id': id,
                    'teamId': teamId,
                    'weekNum': splitWeekNum[0],
                    'DutyName': res.data.DutyName,
                    'dataEdit' : selectedDayInstance.attr("data-edit-screen"),
                    'scheduledPersonName': scheduledPersonName,
                    'dutyDate' : selectedDayInstance.attr("data-date"),
                    'allocationsDutyId' : allocationsDutyId,
                    'allocationsSpId' : allocationsSpId,
                    'allocationsSchPer' : selectedDayInstance.attr('data-scheduling-person'),
                    'oldDutyComments' : res.data.DutyComments,
                    'oldPersonComments' : res.data.PersonComments
                }
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/modals/add-comments-modal.php",
                    data: dataCommentModal,
                    success: function (response) {
                        $.facebox(response);
                        let iniDutyComments = res.data.DutyComments ? res.data.DutyComments : '';
                        let iniPersonComments = res.data.PersonComments ? res.data.PersonComments : '';

                        let lineBreaksDutyComments = (iniDutyComments.match(/\n/g) || []).length;
                        let lineBreaksPersonComments = (iniPersonComments.match(/\n/g) || []).length;

                        $("[name='DutyComments']").val(iniDutyComments);
                        $("[name='PersonComments']").val(iniPersonComments);

                        let dutyStrLength = parseInt(iniDutyComments.length);
                        let personStrLength = parseInt(iniPersonComments.length);

                        let dutyRemainStrCharsWithLineBreaks = parseInt(500 - dutyStrLength);
                        let personRemainStrCharsWithLineBreaks = parseInt(500 - personStrLength);

                        let dutyRemainStrChars = parseInt(dutyRemainStrCharsWithLineBreaks + lineBreaksDutyComments);
                        let personRemainStrChars = parseInt(personRemainStrCharsWithLineBreaks + lineBreaksPersonComments);

                        $('#remainDutyComments').html(dutyRemainStrChars);
                        $('#remainPersonComments').html(personRemainStrChars);
                    }
                });
            }
        }
    });
}

function AuthoriseOver12Popup(data) {
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/add-markOver-twelve.php",
        data: data,
        success: function (response) {
            $.facebox(response);
            overTwelveTextboxSetting();
        }
    });
}

/** Save Over 12 data --START*/
function saveAuthoriseOver12Popup() {
    var hrsArr = [0, 15, 30, 45];
    $.validator.addMethod('maxStrict', function(value, el, param) {
        return parseFloat(value) <= parseFloat($("#maxHours").val());
    }, '</br/>You cannot enter Authorise over 12 Hours value greater than the Max Hours of this duty (' + $("#maxHours").val() + ')');

    $.validator.addMethod('validHrs', function(value, el, param) {
        return $.inArray(((value * 3600)%3600)/60, hrsArr) != -1 ? true : false;

    }, "</br/>Only '0.25', '0.50' and '0.75' fractions are allowed for Max Hours");

    $.validator.addMethod('validZeroHrs', function(value, el, param) {
        let notZeroHrs = false;
        if((parseFloat(value) > parseFloat('0.00')) && ($('#authoriseOver12').val() == 1)){
            notZeroHrs = true;
        } else if((parseFloat(value) == parseFloat('0.00')) && ($('#authoriseOver12').val() == 0)){
            notZeroHrs = true;
        }
        return notZeroHrs;
    }, "</br/>Please enter a number of hours");

    $('#saveauthorise_form').validate({
        debug: false,
        errorPlacement: function(error, element) {
            error.appendTo('#over12Error');
        },
        rules: {
            "authorised12Hours": {
                required: true,
                maxStrict: true,
                validHrs: true,
                validZeroHrs: true
            }
        },
        messages: {
            "authorised12Hours": {
                required: jQuery.validator.format("<br/> This Field is required."),
                maxStrict: 'You cannot enter Authorise over 12 hours value greater than the Max Hours of this duty (' + $("#maxHours").val() + ')',
                validHrs: "Only '0.25', '0.50' and '0.75' fractions are allowed for Max Hours",
                validZeroHrs: "Please enter a number of hours"
            }
        },

        submitHandler: function(form) {
            $.ajax({
                type: 'POST',
                url: '/page-includes/allocations/weekly/actions/save-markOverTwelve.php',
                dataType: "json",
                data: $('#saveauthorise_form').serialize(),
                success: function(response) {
                    var maxhrs = $("#maxHours").val();
                    var markTwelve = $("#authoriseOver12").val();
                    var dataInstanceId = $("#allocationsSpId").val() + '_' + $("#dutyDate").val();

                    if (response.success === true) {
                        $('#facebox .close').click();
                        refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
                    } else {

                    }
                }
            });
        }
    });
}
/** Save Over 12 data --END*/

/** Save Override under 11 data --START*/
function saveOverrideUnder11Popup() {
    var hrsArr = [0, 30];
    $.validator.addMethod('maxStrict', function(value, el, param) {
        return parseFloat(value) <= parseFloat($("#underBreakHours").val());
    }, '</br/>You cannot enter Adjusted under 11 Hours value greater than the Rounded Hours of this duty (' + $("#underBreakHours").val() + ')');

    $.validator.addMethod('validHrs', function(value, el, param) {
        return $.inArray(((value * 3600)%3600)/60, hrsArr) != -1 ? true : false;

    }, "</br/>Only '0.50' and '0.00' fractions are allowed for Adjusted Under11 Hours");

    $.validator.addMethod('maxlength', function(value, el, param) {
        return value.length <= param;

    }, "</br/>Comment can not be more than {500} characters");

    $('#saveoverride11_form').validate({
        debug: false,
        errorPlacement: function(error, element) {
            error.appendTo('#under11Error');
        },
        rules: {
            "actualUnder11hrs": {
                required: true,
                maxStrict: true,
                validHrs: true
            },
            "under11Comments": {
                maxlength: 500
            }
        },
        messages: {

            "actualUnder11hrs": {
                required: jQuery.validator.format("<br/> This Field is required."),
                maxStrict: 'You cannot enter Adjusted under 11 Hours value greater than the Rounded Hours of this duty (' + $("#underBreakHours").val() + ')',
                validHrs: "Only '0.50' and '0.00' fractions are allowed for Adjusted Under11 Hours"
            },
            "under11Comments": {
                maxlength: 'Comment can not be more than {500} characters'
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: 'POST',
                url: '/page-includes/allocations/weekly/actions/save-markUnderEleven.php',
                dataType: "json",
                data: $('#saveoverride11_form').serialize(),
                success: function(response) {
                    var dataInstanceId = $("#allocationsSpId").val() + '_' + $("#dutyDate").val();

                    if (response.success === true) {
                        $('#facebox .close').click();
                        refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');

                    } else {

                    }
                }
            });
        }
    });
}
/** Save Override under 11 data --END*/

/** Mark overtime open the popup */
function MarkOvertimeOpenPopup(data) {
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/markOverTimeUI.php",
        data: data,
        success: function (response) {
            $.facebox(response);
        }
    });
}

/* save/update  overtime*/
function SaveMarkOvertime() {
    var hrsArr = [0, 15, 30, 45];
    $.validator.addMethod('compare', function(value, element, param) {
        return this.optional(element) || parseFloat(value) <= parseFloat($("#availhrs").val());
    }, '<br/>You cannot enter Manual OT Hours (exec breaks) value higher than the Available Hours of this duty (' + $("#availhrs").val() + ')');

    $.validator.addMethod('minStrict', function(value, el, param) {
        return value >= param;
    }, "</br/>Maximum Hrs Should Be Equal To Or Greater Than 0");

    $.validator.addMethod('validHrs', function(value, el, param) {
        return $.inArray(((value * 3600)%3600)/60, hrsArr) != -1 ? true : false;
    }, "</br/>Only '0.25', '0.50' and '0.75' fractions are allowed for Overtime entries");

    $.validator.addMethod('otHrsEdit', function(value, el, param) {
        let otHrs = value.split('.');
        let otHrsSec = 0;
        if(otHrs[1] == undefined){
            otHrsSec = (+otHrs[0]) * 60 * 60;
        } else {
            otHrsSec = (+otHrs[0]) * 60 * 60 + (+otHrs[1]) * 60;
        }

        let prevOTHrs = $('#prevOTHrs').val().split('.');
        let prevOTHrsSec = 0;
        if(prevOTHrs[1] == undefined){
            prevOTHrsSec = (+prevOTHrs[0]) * 60 * 60;
        } else {
            prevOTHrsSec = (+prevOTHrs[0]) * 60 * 60 + (+prevOTHrs[1]) * 60;
        }

        if(prevOTHrsSec == otHrsSec){
            return false;
        } else {
            return true;
        }
    }, "</br/>No changes found. Press 'X' to close the window.");
    $('#markovertimeform').validate({
        debug: false,
        errorPlacement: function(error, element) {
            error.appendTo('#overTimeError');
        },
        rules: {
            "mannualothrs": {
                required: true,
                compare: true,
                validHrs: true,
                otHrsEdit: true
            }
        },
        messages: {

            "markovertimeot": {
                required: jQuery.validator.format("<br/> This Field is required."),
                minlength: jQuery.validator.format("<br/> At least {0} characters are necessary."),

            }
        },
        submitHandler: function(form) {

            $.ajax({
                type: 'POST',
                url: '/page-includes/allocations/weekly/actions/mark-action.php',
                dataType: "json",
                data: $('#markovertimeform').serialize(),
                success: function(response) {
                    if (response.success === true) {
                        if (form.elements["mannualothrs"].value > 0 && $("#cellID_" + form.elements["dateonly"].value + "_" + form.elements["schedulingPersonId"].value).hasClass('attentionClass') == true) {
                            $("#cellID_" + form.elements["dateonly"].value + "_" + form.elements["schedulingPersonId"].value).addClass('attentionClass');
                        }
                        if (form.elements["mannualothrs"].value > 0 && $("#cellID_" + form.elements["dateonly"].value + "_" + form.elements["schedulingPersonId"].value).hasClass('attentionCopyDutyClass') == true) {
                            $("#cellID_" + form.elements["dateonly"].value + "_" + form.elements["schedulingPersonId"].value).addClass('attentionCopyDutyClass');
                        }
                        if (form.elements["mannualothrs"].value > 0) {
                        $("#redEuro" + "_" + dataInstanceId).removeClass('inactivemarkovertime').addClass('activemarkovertime');

                        } else {
                            if ($("#cellID_" + form.elements["dateonly"].value + "_" + form.elements["schedulingPersonId"].value).hasClass('attentionClass') == true) {
                                $("#cellID_" + form.elements["dateonly"].value + "_" + form.elements["schedulingPersonId"].value).addClass('attentionClass');
                            }
                            if ($("#cellID_" + form.elements["dateonly"].value + "_" + form.elements["schedulingPersonId"].value).hasClass('attentionCopyDutyClass') == true) {
                                $("#cellID_" + form.elements["dateonly"].value + "_" + form.elements["schedulingPersonId"].value).addClass('attentionCopyDutyClass');
                            }
                            $("#redEuro" + "_" + dataInstanceId).removeClass('inactivemarkovertime').addClass('activemarkovertime');
                        }
                        var dataInstanceId = form.elements["allocationsSpId"].value + '_' + form.elements["dutyDate"].value;
                        refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
                        $('#facebox .close').click();
                    } else {
                        customAlert(data.strreturnstring);
                        $('#facebox .close').click()
                    }
                }
            });
        }
    })
}

$("#mastMiscFilterId").on('change', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    $('#loading').show();
    if($('#showWeeks').val() > 1){
        loadUnallocatedGrid();
    } else {
        reloadeditweeklygrid('','','','','UNALLOC');
    }
});

$(document).on('click', '#publishWeek', function(event) {
    event.stopImmediatePropagation();
    event.preventDefault();
    var formData = $('#publishWeekForm').serialize();
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/week-publish.php",
        data: formData,
        success: function (response) {
            response = $.parseJSON(response);
            if (response.success) {
                var message;
                if (response.send == "1") {
                    message = "The week has been published!";
                    $('#allocations-container').attr('data-is-week-published',1);
                    if($('#allocations-container_daily').length > 0){
                        $('#allocations-container_daily').attr('data-is-week-published',1);
                    }
                } else {
                    message = "The week has been unpublished!";
                    $('#allocations-container').attr('data-is-week-published',0);
                    if($('#allocations-container_daily').length > 0){
                        $('#allocations-container_daily').attr('data-is-week-published',0);
                    }
                }
                customAlert(message);
            } else {
                var message = '';
                if(response.message != ''){
                    message = response.message;
                } else {
                    message = 'Some error occured. Please try again!';
                }
                customAlert(message);
            }
        }
    });
});

function showPublishWeekModal() {
	var screenName = $('#dailyAllocationspublish').find('input[name="screenName"]').val();
	if (screenName=='ViewDaily'){
		var data = $("#dailyAllocationspublish").serialize();
	} else {
		var data = $("#editWeeklyAllocations").serialize();
	}

    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/publish-week.php",
        data: data,
        success: function (response) {
            $.facebox(response);
        }
    });
}

function dutyAllocatedJobs(selectedDayInstance) {
    let id = selectedDayInstance.attr("data-id");
    let iDay = selectedDayInstance.attr("data-iday");
    let schedulingPersonId = selectedDayInstance.attr("data-scheduling-person");
    let AllocationsDutyID = selectedDayInstance.attr("data-allocations-duty-id");
    let AllocationsSPID = selectedDayInstance.attr("data-allocations-sp-id");
    dataReq = {
        id: id,
        iDay: iDay,
        schedulingPersonId: schedulingPersonId,
		AllocationsDutyID: AllocationsDutyID,
		AllocationsSPID: AllocationsSPID
    };
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/duty-allocated-jobs-modal.php",
        data: dataReq,
        success: function (response) {
            $.facebox(response);
        }
    });
}

function getDutyHistory(selectedDayInstance, requestType = 'DH', allocationsSpId = 0, allocationsDutyId = 0, allocationId = 0, scheduledPersonId = 0) {
    let dataSource = '';
    if(selectedDayInstance != null) {
        allocationsSpId = Number.isInteger(selectedDayInstance) ? allocationsSpId : selectedDayInstance.attr("data-allocations-sp-id");
        allocationsDutyId = Number.isInteger(selectedDayInstance) ? allocationsDutyId : selectedDayInstance.attr("data-allocations-duty-id");
        dataSource = Number.isInteger(selectedDayInstance) ? '' : selectedDayInstance.attr("data-source");
    }
    allocationId = Number.isInteger(selectedDayInstance) || selectedDayInstance == null ? allocationId : selectedDayInstance.attr("data-id");
    scheduledPersonId = Number.isInteger(selectedDayInstance) || selectedDayInstance == null ? scheduledPersonId : selectedDayInstance.attr("data-scheduling-person");
    let id = (requestType == 'PH') ? allocationsSpId : allocationsDutyId;
	let disablePersonHistorTab = '';
	let popupTemplate = '';
	if(dataSource == 'unallocated')
	{
		 disablePersonHistorTab = 'pointer-events:none; opacity:0.6;';
	}

	popupTemplate = '<div id="editdutydatetabs" class="ui-tabs ui-corner-all ui-widget ui-widget-content" style="width:750px; max-height: 300px;overflow-y: auto;" ><ul role="tablist" class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header"><li id="tab_a" onclick="javascript:getDutyHistory('+id+', \'DH\', '+allocationsSpId+', '+allocationsDutyId+', ' + allocationId + ', ' + scheduledPersonId + ')";"" role="tab" tabindex="-1" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab" aria-controls="editdutydatelistdiv" aria-labelledby="dutydetails" aria-selected="false" aria-expanded="false"><a href="javascript:void(0);" id="dutydetails" role="presentation" tabindex="-1" class="ui-tabs-anchor">Duty History</a></li><li id="tab_d" onclick="javascript:getDutyHistory('+id+', \'PH\', '+allocationsSpId+', '+allocationsDutyId+', ' + allocationId + ', ' + scheduledPersonId + ')";"" role="tab" tabindex="0" class="ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active" aria-controls="editdutydatelistdiv" aria-labelledby="dutyrotas" aria-selected="true" aria-expanded="true" style="'+disablePersonHistorTab+'"><a href="javascript:void(0);" id="dutyrotas" role="presentation" tabindex="-1" class="ui-tabs-anchor">Person History</a></li></ul><div id="editWeeklyDutyPersonhistory" class="ui-tabs-panel ui-corner-bottom ui-widget-content"><table id="dutyrotasnewedittable1" class="redtable smalltable bluetable" width="100%" style="width: 100%;"><thead><tr role="row"><th style="text-align:center;" >History</th></tr></thead><tbody>DYNAMICTABLEROW</tbody></table></div> </div>';

    dataReq = {
        moduleName: 'AllocationDuty',
        id: id,
		requestType: requestType,
        allocationId: allocationId,
        scheduledPersonId: scheduledPersonId,
		allocationsSPID: allocationsSpId
    };

    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/duty-history-modal.php",
        data: dataReq,
        success: function (response) {
			popupTemplate = popupTemplate.replace('DYNAMICTABLEROW', response);
            $.facebox(popupTemplate);
			if(requestType == 'DH')
			{
				$("#tab_a").addClass('ui-tabs-active ui-state-active');
				$("#tab_d").removeClass('ui-tabs-active ui-state-active');
			}else
			{
				$("#").addClass('ui-tabs-active ui-state-active');
				$("#tab_a").removeClass('ui-tabs-active ui-state-active');
			}
        }
    });
}

function updateComments(id, teamId, weekNum, allocationsDutyId, allocationsSpId, dutyDate, allocationsSchPer) {
    let dataComments = $('#allocationComments').serialize() + '&' + $.param({
        'id': id,
        'dutyDate': dutyDate,
		'allocationsDutyId': allocationsDutyId,
		'allocationsSpId': allocationsSpId,
		'allocationsSchPer': allocationsSchPer
    });
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/add-comments.php",
        data: dataComments,
        success: function (response) {
            response = $.parseJSON(response);
            if (response.success) {
                $('#facebox .close').click();
                var dataInstanceId = allocationsSpId ? allocationsSpId + '_' + dutyDate : allocationsSchPer + '_' + dutyDate;
                refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
            }
        }
    });
}

function showHideCountGrid() {
    if ($('#showHideCount').prop("checked")) {
        reloadShiftCountingGrid();
        $("#shiftCountingCheckBox").val('show');
        if($.cookie('unallocgridheight') != undefined){
            $("#unallocatedDuty").height($.cookie('unallocgridheight'));
            $("#allocatedDuty").height($.cookie('allocgridheight'));
            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
            $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
            setTimeout(function() {
                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
            }, 1000);
        } else {
            $(".allocationSections").height($("#allocations-container").height() / 3);
            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
            $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
            setTimeout(function() {
                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
            }, 1000);
        }
        $('#showCountBlock').show();
        $('#showCountRight').scrollLeft($('#weeklyAllocation-2').scrollLeft());
    } else {
        if(($('#selShiftCountingFilterId').val() != '') && ($('#selShiftCountingFilterId').val() != '0')){
            if ($('#selShiftCountingFilterId').val() != undefined) {
                unsetShiftCountingFilter($('#searchTeamId').val());
            }
        }
        $("#shiftCountingCheckBox").val('hide');
        $('#dutyShiftCountingFilterId').val('NA').trigger("chosen:updated");
        if($.cookie('unallocgridheight') != undefined){
            $("#unallocatedDuty").height($.cookie('unallocgridheight'));
            let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($("#showCountBlock").height());
            $("#allocatedDuty").height(allocatedHeight);
            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
        } else {
            $("#unallocatedDuty").height($("#allocations-container").height() / 2.5);
            $("#allocatedDuty").height($("#allocations-container").height() / 1.7);
            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
            $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
            setTimeout(function() {
                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
            }, 1000);
        }
        $('#showCountBlock').hide();
    }
}
/** Sickness open the popup */

function SicknessOpenPopup(dataval) {
    $('#loading').show();
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/editWeeklyAlloctedDutiesAction.php",
        data: {dataval: dataval},
        success: function (data) {
            $.facebox(data);
            $('#loading').hide();
        }
    });
}

function showDutyShiftCountingFilter(userId, teamId) {
    if($('#editScreen').val() == 1){
        let dutyShiftCountingFilterId = $('#dutyShiftCountingFilterId').val();
        let dataReq = {};
        if (dutyShiftCountingFilterId != '') {
            dataReq = {
                'userId': userId,
                'teamId': teamId,
                'selectedFilterId': dutyShiftCountingFilterId
            };
        } else {
            dataReq = {
                'userId': userId,
                'teamId': teamId,
                'selectedFilterId': ''
            };
        }
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/modals/duty-shiftcounting-filter-modal.php",
            data: dataReq,
            success: function (response) {
                $.facebox(response);
            }
        });
    }
}

function clearDutyShiftCountingFilter(userId, teamId) {
    let dataReq = {
        'userId': userId,
        'teamId': teamId,
        'selectedFilterId': ''
    };
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/duty-shiftcounting-filter-modal.php",
        data: dataReq,
        success: function (response) {
            $.facebox(response);
        }
    });
}

function closeDutyShiftCountingFilter() {
    $('#facebox .close').click()
}

function applyDutyShiftCountingFilter(userId = 0, teamId = 0, filterId = 0) {
    let dutyCountFilterCols = '';
    if(filterId == 0 || filterId == ''){
        var selDutyFilterShiftCounting = $('#modalDutyShiftCountingFilterId').val();
        filterId = selDutyFilterShiftCounting;
    }
    if ($('#saveFilter').prop("checked") == true) {
        let filterName = $('#newFilterName').val();
        if (filterName == '') {
            $('#error-mess').html('Please enter filter name');
            $('#error-mess-div').show();
            $('#success-mess').html('');
            $('#success-mess-div').hide();
        } else {
            let isPublic = 0;
            let isActive = 1;
            let detailsShow = 0;
            let showTotalCount = 0;
            let colA, colB, colC, colD, colE, colF, colG, colH, colI, colJ, colK, colL, colM, colN, colO, colP, colQ, colR, colS, colT, colU, colV, colW, colX, colY, colZ;
            colA = colB = colC = colD = colE = colF = colG = colH = colI = colJ = colK = colL = colM = colN = colO = colP = colQ = colR = colS = colT = colU = colV = colW = colX = colY = colZ = 0;

            if (($('#ColA').prop("checked") == false) && ($('#ColB').prop("checked") == false) && ($('#ColC').prop("checked") == false) && ($('#ColD').prop("checked") == false) && ($('#ColE').prop("checked") == false) && ($('#ColF').prop("checked") == false) && ($('#ColG').prop("checked") == false) && ($('#ColH').prop("checked") == false) && ($('#ColI').prop("checked") == false) && ($('#ColJ').prop("checked") == false) && ($('#ColK').prop("checked") == false) && ($('#ColL').prop("checked") == false) && ($('#ColM').prop("checked") == false) && ($('#ColN').prop("checked") == false) && ($('#ColO').prop("checked") == false) && ($('#ColP').prop("checked") == false) && ($('#ColQ').prop("checked") == false) && ($('#ColR').prop("checked") == false) && ($('#ColS').prop("checked") == false) && ($('#ColT').prop("checked") == false) && ($('#ColU').prop("checked") == false) && ($('#ColV').prop("checked") == false) && ($('#ColW').prop("checked") == false) && ($('#ColX').prop("checked") == false) && ($('#ColY').prop("checked") == false) && ($('#ColZ').prop("checked") == false)) {
                $('#error-mess').html('Please Select Alphabet Checkboxes.');
                $('#error-mess-div').show();
                $('#success-mess').html('');
                $('#success-mess-div').hide();
            } else {
                if ($('#detailsChk').prop("checked") == true) {
                    detailsShow = 1;
                    dutyCountFilterCols += 'd,';
                }
                if ($('#countsTotal').prop("checked") == true) {
                    showTotalCount = 1;
                    dutyCountFilterCols += 't,';
                }
                if ($('#ColA').prop("checked") == true) {
                    colA = 1;
                    dutyCountFilterCols += 'A,';
                }
                if ($('#ColB').prop("checked") == true) {
                    colB = 1;
                    dutyCountFilterCols += 'B,';
                }
                if ($('#ColC').prop("checked") == true) {
                    colC = 1;
                    dutyCountFilterCols += 'C,';
                }
                if ($('#ColD').prop("checked") == true) {
                    colD = 1;
                    dutyCountFilterCols += 'D,';
                }
                if ($('#ColE').prop("checked") == true) {
                    colE = 1;
                    dutyCountFilterCols += 'E,';
                }
                if ($('#ColF').prop("checked") == true) {
                    colF = 1;
                    dutyCountFilterCols += 'F,';
                }
                if ($('#ColG').prop("checked") == true) {
                    colG = 1;
                    dutyCountFilterCols += 'G,';
                }
                if ($('#ColH').prop("checked") == true) {
                    colH = 1;
                    dutyCountFilterCols += 'H,';
                }
                if ($('#ColI').prop("checked") == true) {
                    colI = 1;
                    dutyCountFilterCols += 'I,';
                }
                if ($('#ColJ').prop("checked") == true) {
                    colJ = 1;
                    dutyCountFilterCols += 'J,';
                }
                if ($('#ColK').prop("checked") == true) {
                    colK = 1;
                    dutyCountFilterCols += 'K,';
                }
                if ($('#ColL').prop("checked") == true) {
                    colL = 1;
                    dutyCountFilterCols += 'L,';
                }
                if ($('#ColM').prop("checked") == true) {
                    colM = 1;
                    dutyCountFilterCols += 'M,';
                }
                if ($('#ColN').prop("checked") == true) {
                    colN = 1;
                    dutyCountFilterCols += 'N,';
                }
                if ($('#ColO').prop("checked") == true) {
                    colO = 1;
                    dutyCountFilterCols += 'O,';
                }
                if ($('#ColP').prop("checked") == true) {
                    colP = 1;
                    dutyCountFilterCols += 'P,';
                }
                if ($('#ColQ').prop("checked") == true) {
                    colQ = 1;
                    dutyCountFilterCols += 'Q,';
                }
                if ($('#ColR').prop("checked") == true) {
                    colR = 1;
                    dutyCountFilterCols += 'R,';
                }
                if ($('#ColS').prop("checked") == true) {
                    colS = 1;
                    dutyCountFilterCols += 'S,';
                }
                if ($('#ColT').prop("checked") == true) {
                    colT = 1;
                    dutyCountFilterCols += 'T,';
                }
                if ($('#ColU').prop("checked") == true) {
                    colU = 1;
                    dutyCountFilterCols += 'U,';
                }
                if ($('#ColV').prop("checked") == true) {
                    colV = 1;
                    dutyCountFilterCols += 'V,';
                }
                if ($('#ColW').prop("checked") == true) {
                    colW = 1;
                    dutyCountFilterCols += 'W,';
                }
                if ($('#ColX').prop("checked") == true) {
                    colX = 1;
                    dutyCountFilterCols += 'X,';
                }
                if ($('#ColY').prop("checked") == true) {
                    colY = 1;
                    dutyCountFilterCols += 'Y,';
                }
                if ($('#ColZ').prop("checked") == true) {
                    colZ = 1;
                    dutyCountFilterCols += 'Z,';
                }

                $('#dutyCountFilterCols').val(dutyCountFilterCols);
                let dataReq = {
                    'action': 'Checkduplicate',
                    'filterName': filterName,
                    'userId': userId,
                    'teamId': teamId
                };
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                    data: dataReq,
                    beforeSend: function (jqXHR, settings) {
                        $("#loading").show();
                    },
                    success: function (response) {
                        response = $.parseJSON(response);
                        if (response.success) {
                            let existDataReq = {
                                'type': 'Old',
                                'filterName': filterName,
                                'userId': userId,
                                'SchedulingTeamId': teamId,
                                'isPublic': isPublic,
                                'isActive': response.isActive,
                                'detailsShow': detailsShow,
                                'showTotalCount': showTotalCount,
                                'colA': colA,
                                'colB': colB,
                                'colC': colC,
                                'colD': colD,
                                'colE': colE,
                                'colF': colF,
                                'colG': colG,
                                'colH': colH,
                                'colI': colI,
                                'colJ': colJ,
                                'colK': colK,
                                'colL': colL,
                                'colM': colM,
                                'colN': colN,
                                'colO': colO,
                                'colP': colP,
                                'colQ': colQ,
                                'colR': colR,
                                'colS': colS,
                                'colT': colT,
                                'colU': colU,
                                'colV': colV,
                                'colW': colW,
                                'colX': colX,
                                'colY': colY,
                                'colZ': colZ
                            };
                            $.ajax({
                                type: "post",
                                url: "/page-includes/allocations/weekly/modals/duty-shiftcounting-filter-exist-modal.php",
                                data: existDataReq,
                                success: function (response) {
                                    $.facebox(response);
                                }
                            });
                        } else {
                            saveDutyShiftCountingFilter(userId, teamId, 'New', filterName, isPublic, isActive, detailsShow, showTotalCount, colA, colB, colC, colD, colE, colF, colG, colH, colI, colJ, colK, colL, colM, colN, colO, colP, colQ, colR, colS, colT, colU, colV, colW, colX, colY, colZ);
                        }
                    },
                    complete: function(){
                        $("#loading").hide();
                    }
                });
            }
        }
    } else {
        if (filterId == 0 || filterId == '') {
            if ($('#detailsChk').prop("checked") == true) {
                dutyCountFilterCols += 'd,';
            }
            if ($('#countsTotal').prop("checked") == true) {
                dutyCountFilterCols += 't,';
            }
            for (let i = 0; i < 26; i++) {
                let alphabetStr = (i + 10).toString(36).toUpperCase();
                if ($('#Col' + alphabetStr).prop("checked") == true) {
                    dutyCountFilterCols += alphabetStr + ',';
                }
            }
            $('#dutyCountFilterCols').val(dutyCountFilterCols);
            $('#selShiftCountingFilterId').val(selDutyFilterShiftCounting);
            $("#shiftCountingCheckBox").val('show');
            $('#error-mess').html('');
            $('#error-mess-div').show();
            $('#success-mess').html('');
            $('#success-mess-div').hide();
            $('#facebox .close').click();
            $('#saveFilter').prop("checked", false);
            $('#showHideCount').prop('checked',true);
            reloadShiftCountingGrid();
            showHideCountGrid();
        } else {
            if (filterId == 'NA') {
                $('#dutyCountFilterCols').val('');
                $('#selShiftCountingFilterId').val(0);
                $('#showHideCount').trigger('click');
                $('#showCountBlock').html('');
                showDutyShiftCountingFilter(userId, teamId);
            } else {
                let dataReq = {
                    'action': 'Getdetailswithremember',
                    'userId': userId,
                    'teamId': teamId,
                    'filterId': filterId
                };
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                    data: dataReq,
                    beforeSend: function (jqXHR, settings) {
                        $("#loading").show();
                    },
                    success: function (response) {
                        response = $.parseJSON(response);
                        if (response.success) {
                            if (response.data.ShowDetails == 1) {
                                dutyCountFilterCols += 'd,';
                            }
                            if (response.data.ShowGrandTotal == 1) {
                                dutyCountFilterCols += 't,';
                            }

                            if (response.data.ColA == 1) {
                                dutyCountFilterCols += 'A,';
                            }
                            if (response.data.ColB == 1) {
                                dutyCountFilterCols += 'B,';
                            }
                            if (response.data.ColC == 1) {
                                dutyCountFilterCols += 'C,';
                            }
                            if (response.data.ColD == 1) {
                                dutyCountFilterCols += 'D,';
                            }
                            if (response.data.ColE == 1) {
                                dutyCountFilterCols += 'E,';
                            }
                            if (response.data.ColF == 1) {
                                dutyCountFilterCols += 'F,';
                            }
                            if (response.data.ColG == 1) {
                                dutyCountFilterCols += 'G,';
                            }
                            if (response.data.ColH == 1) {
                                dutyCountFilterCols += 'H,';
                            }
                            if (response.data.ColI == 1) {
                                dutyCountFilterCols += 'I,';
                            }
                            if (response.data.ColJ == 1) {
                                dutyCountFilterCols += 'J,';
                            }
                            if (response.data.ColK == 1) {
                                dutyCountFilterCols += 'K,';
                            }
                            if (response.data.ColL == 1) {
                                dutyCountFilterCols += 'L,';
                            }
                            if (response.data.ColM == 1) {
                                dutyCountFilterCols += 'M,';
                            }
                            if (response.data.ColN == 1) {
                                dutyCountFilterCols += 'N,';
                            }
                            if (response.data.ColO == 1) {
                                dutyCountFilterCols += 'O,';
                            }
                            if (response.data.ColP == 1) {
                                dutyCountFilterCols += 'P,';
                            }
                            if (response.data.ColQ == 1) {
                                dutyCountFilterCols += 'Q,';
                            }
                            if (response.data.ColR == 1) {
                                dutyCountFilterCols += 'R,';
                            }
                            if (response.data.ColS == 1) {
                                dutyCountFilterCols += 'S,';
                            }
                            if (response.data.ColT == 1) {
                                dutyCountFilterCols += 'T,';
                            }
                            if (response.data.ColU == 1) {
                                dutyCountFilterCols += 'U,';
                            }
                            if (response.data.ColV == 1) {
                                dutyCountFilterCols += 'V,';
                            }
                            if (response.data.ColW == 1) {
                                dutyCountFilterCols += 'W,';
                            }
                            if (response.data.ColX == 1) {
                                dutyCountFilterCols += 'X,';
                            }
                            if (response.data.ColY == 1) {
                                dutyCountFilterCols += 'Y,';
                            }
                            if (response.data.ColZ == 1) {
                                dutyCountFilterCols += 'Z,';
                            }

                            $('#dutyCountFilterCols').val(dutyCountFilterCols);
                            $('#selShiftCountingFilterId').val(response.data.ID);
                            $('#dutyShiftCountingFilterId').val(response.data.ID).trigger("chosen:updated");
                            $("#shiftCountingCheckBox").val('show');
                            $('#error-mess').html('');
                            $('#error-mess-div').show();
                            $('#success-mess').html('');
                            $('#success-mess-div').hide();
                            if($('#shiftCountingFilterId').val() == undefined){
                                $('#facebox .close').click();
                            }
                            $('#saveFilter').prop("checked", false);
                            $('#showHideCount').prop('checked',true);
                            reloadShiftCountingGrid();
                            showHideCountGrid();
                        } else {
                            $("#showHideCount").prop( "checked", false );
                            $("#dutyCountFilterCols").val('');
                            $("#selShiftCountingFilterId").val('');
                            $("#shiftCountingCheckBox").val('hide');

                            let dataRequest = {
                                'action': 'Filterlist',
                                'userId': userId,
                                'teamId': teamId,
                                'filterId': 0,
                                'isActive': 1
                            }


                            $.ajax({
                                type: "post",
                                url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                                data: dataRequest,
                                success: function (resp) {
                                    resp = $.parseJSON(resp);
                                    let repDiv = '';
                                    if (resp.success) {
                                        repDiv += '<option value="NA">No Filter/Edit Filter</option>';
                                        $(resp.data).each(function(index, value) {
                                            repDiv += '<option value="' + value.ID + '">' + value.FilterName + '</option>';
                                        });
                                    } else {
                                        repDiv += '<option value="NA">No Filter/Edit Filter</option>';
                                    }
                                    $('#dutyShiftCountingFilterId').html(repDiv).trigger("chosen:updated");

                                    let dataReqst = {
                                        'action': 'Filterlist',
                                        'userId': userId,
                                        'teamId': teamId,
                                        'filterId': 0,
                                        'isActive': 0
                                    }
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                                        data: dataReqst,
                                        success: function (res) {
                                            res = $.parseJSON(res);
                                            let repDivModal = '';
                                            if (res.success) {
                                                repDivModal += '<option value="NA">No Filter/Edit Filter</option>';
                                                $(res.data).each(function(index, value) {
                                                    repDivModal += '<option value="' + value.ID + '">' + value.FilterName + '</option>';
                                                });
                                            } else {
                                                repDivModal += '<option value="NA">No Filter/Edit Filter</option>';
                                            }
                                            $('#modalDutyShiftCountingFilterId').html(repDivModal);
                                            $('#dutyShiftCountingFilterId').html(repDivModal).trigger("chosen:updated");

                                            $('#error-mess').html('Filter deleted by Scheduling Team Admin');
                                            $('#error-mess-div').show();
                                            $('#success-mess').html('');
                                            $('#success-mess-div').hide();

                                            if($('#showHideCount').prop('checked') == true){
                                                $('#showHideCount').trigger('click');
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    },
                    complete: function(){
                        $("#loading").hide();
                    }
                });
            }
        }
    }
}

function saveDutyShiftCountingFilter(userId, teamId, type, filterName, isPublic, isActive, detailsShow, showTotalCount, colA, colB, colC, colD, colE, colF, colG, colH, colI, colJ, colK, colL, colM, colN, colO, colP, colQ, colR, colS, colT, colU, colV, colW, colX, colY, colZ) {
    dataReq = {
        'action': 'Add',
        'type': type,
        'filterName': filterName,
        'userId': userId,
        'SchedulingTeamId': teamId,
        'teamId': teamId,
        'isPublic': isPublic,
        'isActive': isActive,
        'detailsShow': detailsShow,
        'showTotalCount': showTotalCount,
        'colA': colA,
        'colB': colB,
        'colC': colC,
        'colD': colD,
        'colE': colE,
        'colF': colF,
        'colG': colG,
        'colH': colH,
        'colI': colI,
        'colJ': colJ,
        'colK': colK,
        'colL': colL,
        'colM': colM,
        'colN': colN,
        'colO': colO,
        'colP': colP,
        'colQ': colQ,
        'colR': colR,
        'colS': colS,
        'colT': colT,
        'colU': colU,
        'colV': colV,
        'colW': colW,
        'colX': colX,
        'colY': colY,
        'colZ': colZ
    }
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
        data: dataReq,
        beforeSend: function (jqXHR, settings) {
            $("#loading").show();
        },
        success: function (response) {
            response = $.parseJSON(response);
            if (response.success) {
                let dutyFilterDiv = '';
                dutyFilterDiv +='<option value="NA">No Filter/Edit Filter</option>';
                $(response.dutyFiltersList).each(function(index, value) {
                    if(response.savedFilterId == value.ID){
                        dutyFilterDiv +='<option value="'+value.ID+'" selected="selected">'+value.FilterName+'</option>';
                    } else {
                        dutyFilterDiv +='<option value="'+value.ID+'">'+value.FilterName+'</option>';
                    }
                });
                $('#dutyShiftCountingFilterId').html(dutyFilterDiv).trigger("chosen:updated");;
                $('#selShiftCountingFilterId').val(response.savedFilterId);
                $('#facebox .close').click();
                $("#shiftCountingCheckBox").val('show');
                $('#saveFilter').prop("checked", false);
                $('#showHideCount').prop('checked',true);
                reloadShiftCountingGrid();
                showHideCountGrid();
            }
        },
        complete: function(){
            $("#loading").hide();
        }
    });
}

function showFilterDetails(userId, teamId, filterId) {
    if (filterId == '' || filterId == 'NA') {
        clearDutyShiftCountingFilter(userId, teamId);
        $('#dutyShiftCountingFilterId').val('NA').trigger("chosen:updated");
    } else {
        let dataReq = {
            'action': 'Getdetails',
            'userId': userId,
            'teamId': teamId,
            'filterId': filterId
        };
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
            data: dataReq,
            success: function (response) {
                response = $.parseJSON(response);
                if (response.success) {
                    $('#filterIsPublic').html(response.data.ID + '-' + response.data.isPublic);
                    if (response.data.isPublic == 1) {
                        if(response.data.UserID == userId){
                            $('#publicFilter').hide();
                            $('#privateFilter').show();
                        } else {
                            $('#publicFilter').hide();
                            $('#privateFilter').hide();
                        }
                    } else {
                        $('#publicFilter').show();
                        $('#privateFilter').hide();
                    }
                    $('#filterIsActive').html(response.data.ID + '-' + response.data.isActive);
                    // $('#saveFilter').prop('checked', true);
                    // $('#newFilterName').val(response.data.OrgFilterName);
                    if (response.data.ShowDetails == 1) {
                        $("#detailsChk").prop("checked", true);
                    } else {
                        $("#detailsChk").prop("checked", false);
                    }
                    if (response.data.ShowGrandTotal == 1) {
                        $("#countsTotal").prop("checked", true);
                    } else {
                        $("#countsTotal").prop("checked", false);
                    }

                    if (response.data.ColA == 1) {
                        $("#ColA").prop("checked", true);
                    } else {
                        $("#ColA").prop("checked", false);
                    }
                    if (response.data.ColB == 1) {
                        $("#ColB").prop("checked", true);
                    } else {
                        $("#ColB").prop("checked", false);
                    }
                    if (response.data.ColC == 1) {
                        $("#ColC").prop("checked", true);
                    } else {
                        $("#ColC").prop("checked", false);
                    }
                    if (response.data.ColD == 1) {
                        $("#ColD").prop("checked", true);
                    } else {
                        $("#ColD").prop("checked", false);
                    }
                    if (response.data.ColE == 1) {
                        $("#ColE").prop("checked", true);
                    } else {
                        $("#ColE").prop("checked", false);
                    }
                    if (response.data.ColF == 1) {
                        $("#ColF").prop("checked", true);
                    } else {
                        $("#ColF").prop("checked", false);
                    }
                    if (response.data.ColG == 1) {
                        $("#ColG").prop("checked", true);
                    } else {
                        $("#ColG").prop("checked", false);
                    }
                    if (response.data.ColH == 1) {
                        $("#ColH").prop("checked", true);
                    } else {
                        $("#ColH").prop("checked", false);
                    }
                    if (response.data.ColI == 1) {
                        $("#ColI").prop("checked", true);
                    } else {
                        $("#ColI").prop("checked", false);
                    }
                    if (response.data.ColJ == 1) {
                        $("#ColJ").prop("checked", true);
                    } else {
                        $("#ColJ").prop("checked", false);
                    }
                    if (response.data.ColK == 1) {
                        $("#ColK").prop("checked", true);
                    } else {
                        $("#ColK").prop("checked", false);
                    }
                    if (response.data.ColL == 1) {
                        $("#ColL").prop("checked", true);
                    } else {
                        $("#ColL").prop("checked", false);
                    }
                    if (response.data.ColM == 1) {
                        $("#ColM").prop("checked", true);
                    } else {
                        $("#ColM").prop("checked", false);
                    }
                    if (response.data.ColN == 1) {
                        $("#ColN").prop("checked", true);
                    } else {
                        $("#ColN").prop("checked", false);
                    }
                    if (response.data.ColO == 1) {
                        $("#ColO").prop("checked", true);
                    } else {
                        $("#ColO").prop("checked", false);
                    }
                    if (response.data.ColP == 1) {
                        $("#ColP").prop("checked", true);
                    } else {
                        $("#ColP").prop("checked", false);
                    }
                    if (response.data.ColQ == 1) {
                        $("#ColQ").prop("checked", true);
                    } else {
                        $("#ColQ").prop("checked", false);
                    }
                    if (response.data.ColR == 1) {
                        $("#ColR").prop("checked", true);
                    } else {
                        $("#ColR").prop("checked", false);
                    }
                    if (response.data.ColS == 1) {
                        $("#ColS").prop("checked", true);
                    } else {
                        $("#ColS").prop("checked", false);
                    }
                    if (response.data.ColT == 1) {
                        $("#ColT").prop("checked", true);
                    } else {
                        $("#ColT").prop("checked", false);
                    }
                    if (response.data.ColU == 1) {
                        $("#ColU").prop("checked", true);
                    } else {
                        $("#ColU").prop("checked", false);
                    }
                    if (response.data.ColV == 1) {
                        $("#ColV").prop("checked", true);
                    } else {
                        $("#ColV").prop("checked", false);
                    }
                    if (response.data.ColW == 1) {
                        $("#ColW").prop("checked", true);
                    } else {
                        $("#ColW").prop("checked", false);
                    }
                    if (response.data.ColX == 1) {
                        $("#ColX").prop("checked", true);
                    } else {
                        $("#ColX").prop("checked", false);
                    }
                    if (response.data.ColY == 1) {
                        $("#ColY").prop("checked", true);
                    } else {
                        $("#ColY").prop("checked", false);
                    }
                    if (response.data.ColZ == 1) {
                        $("#ColZ").prop("checked", true);
                    } else {
                        $("#ColZ").prop("checked", false);
                    }
                }
            }
        });
    }
}

function deleteFilter(userId, teamId) {
    let filterId = $('#modalDutyShiftCountingFilterId').val();
    let filterIsPublic = $('#filterIsPublic').html();
    let splitFilterIsPublic = filterIsPublic.split('-');
    let staRole = $('#staRole').val();
    let isAllow = 0;
    if ((splitFilterIsPublic[0] == filterId) || (staRole == 1)) {
        isAllow = 1;
    }
    if (filterId != '') {
        if ((splitFilterIsPublic[1] == 0 && isAllow == 1) || (staRole == 1)) {
            let dataReq = {
                'action': 'Delete',
                'userId': userId,
                'teamId': teamId,
                'filterId': filterId,
                'isPublic': splitFilterIsPublic[1],
                'staRole': staRole
            };
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                data: dataReq,
                success: function (response) {
                    response = $.parseJSON(response);
                    if (response.success) {
                        let dataRequest = {
                            'action': 'Filterlist',
                            'userId': userId,
                            'teamId': teamId,
                            'filterId': 0,
                            'isActive': 1
                        }
                        $.ajax({
                            type: "post",
                            url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                            data: dataRequest,
                            success: function (resp) {
                                resp = $.parseJSON(resp);
                                let repDiv = '';
                                if (resp.success) {
                                    repDiv += '<option value="NA">No Filter/Edit Filter</option>';
                                    $(resp.data).each(function(index, value) {
                                        repDiv += '<option value="' + value.ID + '">' + value.FilterName + '</option>';
                                    });
                                } else {
                                    repDiv += '<option value="NA">No Filter/Edit Filter</option>';
                                }
                                $('#dutyShiftCountingFilterId').html(repDiv).trigger("chosen:updated");

                                let dataReqst = {
                                    'action': 'Filterlist',
                                    'userId': userId,
                                    'teamId': teamId,
                                    'filterId': 0,
                                    'isActive': 0
                                }
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                                    data: dataReqst,
                                    success: function (res) {
                                        res = $.parseJSON(res);
                                        let repDivModal = '';
                                        if (res.success) {
                                            repDivModal += '<option value="NA">No Filter/Edit Filter</option>';
                                            $(res.data).each(function(index, value) {
                                                repDivModal += '<option value="' + value.ID + '">' + value.FilterName + '</option>';
                                            });
                                        } else {
                                            repDivModal += '<option value="NA">No Filter/Edit Filter</option>';
                                        }
                                        $('#modalDutyShiftCountingFilterId').html(repDivModal);
                                        $('#dutyShiftCountingFilterId').html(repDivModal).trigger("chosen:updated");

                                        clearDutyShiftCountingFilter(userId, teamId);
                                        $('#error-mess').html('');
                                        $('#error-mess-div').hide();
                                        $('#success-mess').html('Filter deleted successfully');
                                        $('#success-mess-div').hide();

                                        if($('#showHideCount').prop('checked') == true){
                                            $('#showHideCount').trigger('click');
                                        }
                                    }
                                });
                            }
                        });
                    }
                }
            });
        } else {
            $('#error-mess').html('You can only delete your own private Duty Count Filters.');
            $('#error-mess-div').show();
            $('#success-mess').html('');
            $('#success-mess-div').hide();
        }
    } else {
        $('#error-mess').html('Please Select Duty Count Filter');
        $('#error-mess-div').show();
        $('#success-mess').html('');
        $('#success-mess-div').hide();
    }
}

function activateDutyCountFilter(userId, teamId) {
    let filterId = $('#modalDutyShiftCountingFilterId').val();
    let filterIsActive = $('#filterIsActive').html();
    let splitFilterIsActive = filterIsActive.split('-');
    if (filterId != '') {
        let dataReq = {
            'action': 'Activate',
            'userId': userId,
            'teamId': teamId,
            'filterId': filterId
        };
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
            data: dataReq,
            success: function (response) {
                response = $.parseJSON(response);
                if (response.success) {
                    let dataRequest = {
                        'action': 'Filterlist',
                        'userId': userId,
                        'teamId': teamId,
                        'filterId': 0,
                        'isActive': 1
                    }
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                        data: dataRequest,
                        success: function (resp) {
                            resp = $.parseJSON(resp);
                            let repDiv = '';
                            if (resp.success) {
                                $('#error-mess').html('');
                                $('#error-mess-div').hide();
                                $('#success-mess').html('Filter activated successfully');
                                $('#success-mess-div').show();
                            }
                        }
                    });
                }
            }
        });
    } else {
        $('#error-mess').html('Please Select Duty Count Filter');
        $('#error-mess-div').show();
        $('#success-mess').html('');
        $('#success-mess-div').hide();
    }
}

function setPublicFilter(userId, teamId, type) {
    let filterId = $('#modalDutyShiftCountingFilterId').val();
    let filterIsActive = $('#filterIsActive').html();
    let splitFilterIsActive = filterIsActive.split('-');
    let filterIsPublic = $('#filterIsPublic').html();
    let splitFilterIsPublic = filterIsPublic.split('-');
    let isAllow = 0;
    if (splitFilterIsActive[0] == filterId) {
        isAllow = 1;
    }
    if (filterId == '') {
        $('#error-mess').html('Please Select Duty Count Filter');
        $('#error-mess-div').show();
        $('#success-mess').html('');
        $('#success-mess-div').hide();
    } else {
        if (splitFilterIsActive[1] == 1 && isAllow == 1) {
            var setIsPublic = 0;
            if (splitFilterIsPublic[1] == 0) {
                setIsPublic = 1;
            }
            let dataReq = {
                'action': 'Public',
                'userId': userId,
                'teamId': teamId,
                'filterId': filterId,
                'isPublic': setIsPublic,
                'orgIsPublic': splitFilterIsPublic[1]
            };
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                data: dataReq,
                success: function (response) {
                    response = $.parseJSON(response);
                    if (response.success) {
                        let dataRequest = {
                            'action': 'Filterlist',
                            'userId': userId,
                            'teamId': teamId,
                            'filterId': 0,
                            'isActive': 0
                        }
                        $.ajax({
                            type: "post",
                            url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
                            data: dataRequest,
                            success: function (resp) {
                                resp = $.parseJSON(resp);
                                let repDiv = '';
                                if (resp.success) {
                                    repDiv += '<option value="">Select a Filter</option>';
                                    $(resp.data).each(function(index, value) {
                                        repDiv += '<option value="' + value.ID + '">' + value.FilterName + '</option>';
                                    });
                                    $('#modalDutyShiftCountingFilterId').html(repDiv);
                                    $('#modalDutyShiftCountingFilterId').val(filterId);

                                    $('#dutyShiftCountingFilterId').html(repDiv).trigger("chosen:updated");
                                    $('#dutyShiftCountingFilterId').val(filterId).trigger("chosen:updated");

                                    $('#error-mess').html('');
                                    $('#error-mess-div').hide();
                                    $('#success-mess-div').show();

                                    if (type.toLowerCase() == 'private') {
                                        $('#success-mess').html('Filter set to private successfully');
                                        $('#publicFilter').show();
                                        $('#privateFilter').hide();

                                        $('#filterIsPublic').html(filterId+'-0');
                                    } else {
                                        $('#success-mess').html('Filter set to public successfully');
                                        $('#publicFilter').hide();
                                        $('#privateFilter').show();
                                        $('#filterIsPublic').html(filterId+'-1');
                                    }
                                }
                            }
                        });
                    } else {
                        $('#error-mess').html(response.message);
                        $('#error-mess-div').show();
                        $('#success-mess').html('');
                        $('#success-mess-div').hide();
                    }
                }
            });
        } else {
            $('#error-mess').html('Please first activate the filter to make it public');
            $('#error-mess-div').show();
            $('#success-mess').html('');
            $('#success-mess-div').hide();
        }
    }
}

$(function() {
    $.contextMenu({
        selector: '.context-menu-adhoc',
        zIndex: function($trigger, opt){
            return 999;
        },
        events: {
            activated : function(options) {
                if(options.$menu.css('top') == '0px') {
                    options.$menu.css('top', Math.abs(Math.round(Math.abs(options.$trigger.offset().top - options.$menu.height()) - 4)) + 'px');
                }
            }
        },
        icon: "unmark",
        items: {
            "delete": {
                name: "Delete",
                icon: "delete",
                selected: false,
                visible: function(key, options) {
                    dutyCell = $(this).find(".allocated");
                    if (dutyCell.attr("data-edit-screen") == 1) {
                        return true;
                    }
                },
                callback: function(key, options) {
                    draggable = $(this).find(".allocated");
                    let splitCurrWeekNum = $('#weekNumber').val().split('/');
                    var data = {
                        "ID": draggable.attr("data-id"),
                        "teamId": draggable.attr("data-teamId"),
                        "startDate": draggable.attr("data-startDate"),
                        "endDate": draggable.attr("data-endDate"),
                        "days": draggable.attr("data-days"),
                        "weekNumber": $("#weekNumber").val(),
                        "startWeek": splitCurrWeekNum[1]+splitCurrWeekNum[0]
                    }
                    selectedDay = $(this).find(".allocated");
                    deleteAdhocDuty(selectedDay);
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/view-adhocduty.php",
                        data: data,
                        success: function (dataview) {
                            $('#content').html(dataview);
                            viewAdhocDutyHeaderSettings();
                        }
                    });
                }
            },
            "edit": {
                name: "Edit",
                icon: "edit",
                selected: false,
                visible: function(key, opt) {
                    dutyCell = $(this).find(".allocated");
                    if (dutyCell.attr("data-edit-screen") == 1) {
                        return true;
                    }
                },
                callback: function(key, opt, e) {
                    draggable = $(this).find(".allocated");
                    dialogForm = $('#dialog-form-adhoc-duty');
                    let splitCurrWeekNum = $('#weekNumber').val().split('/');
                    var data = {
                        "ID": draggable.attr("data-id"),
                        "teamId": draggable.attr("data-teamId"),
                        "startDate": draggable.attr("data-startDate"),
                        "endDate": draggable.attr("data-endDate"),
                        "days": draggable.attr("data-days"),
                        "colorId": draggable.attr("data-colorId"),
                        "dutyDate": draggable.attr("data-date"),
                        "weekNumber": $('#weekNumber').val(),
                        "startWeekDate": $('#stWeekDate').html(),
                        "startDate": $('#stDate').html(),
                        "startWeek": splitCurrWeekNum[1]+splitCurrWeekNum[0]
                    }
                    editDutyDialog = dialogForm.dialog({
                        autoOpen: false,
                        height: 350,
                        width: 600,
                        modal: true,
                        buttons: {
                            "Update Adhoc Duty": function() {
                                var formData = $(this).find('form').serialize();
                                let adhErr = '';
                                let dutyname = '';

                                dutyname =$(this).find('form input[name="DutyName"]').val();
                                if (typeof dutyname === undefined ||  dutyname.trim() == "") {
                                    adhErr = 'ahDutyNameErr';
                                }
                                if(adhErr == 'ahDutyNameErr') {
                                    $('#errorDivAhdoc').html("Please enter duty name");
                                    $("#DutyName").focus();
                                    $('#errorDivAhdoc').show();
                                } else {
                                    $('#errorDivAhdoc').hide();
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/weekly/actions/edit-adhocDuty.php",
                                        data: formData,
                                        success: function (response) {
                                            response = $.parseJSON(response);
                                            if (response.success) {
                                                $.ajax({
                                                    type: "post",
                                                    url: "/page-includes/allocations/edits/allocation-history.php",
                                                    data: formData,
                                                    success: function (res) {
                                                        res = $.parseJSON(res);
                                                        if (res.intstatus) {
                                                            $.ajax({
                                                                type: "post",
                                                                url: "/page-includes/allocations/weekly/view-adhocduty.php",
                                                                data: data,
                                                                success: function (dataview) {
                                                                    $('#content').html(dataview);
                                                                    viewAdhocDutyHeaderSettings();
                                                                    editDutyDialog.html('');
                                                                    editDutyDialog.dialog("close");
                                                                    editDutyDialog.dialog('destroy');
                                                                }
                                                            });
                                                        }
                                                    }
                                                });
                                            } else if (response.error) {
                                                $("#breaktimeError").html("<p>Breaktime cannot be greater than duration</p>").dialog();

                                            }
                                        }
                                    });
                                }
                            },
                            Cancel: function() {
                                editDutyDialog.html('');
                                editDutyDialog.dialog("close");
                                editDutyDialog.dialog('destroy');
                            }
                        }
                    });
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/modals/edit-adhocDuty-modal.php",
                        data: data,
                        success: function (response) {
                            $('.ui-dialog-title').html("Edit Adhoc Duty");
                            editDutyDialog.html(response).dialog({
                                modal: true
                            }).dialog('open');
                        }
                    });
                }
            },
            "comment": {
                name: "Comment",
                icon: "comment",
                selected: false,
                visible: function(key, opt) {
                    dutyCell = $(this).find(".allocated");
                    if (dutyCell.attr("data-edit-screen") == 1) {
                        return true;
                    }
                },
                callback: function(key, opt, e) {
                    draggable = $(this).find(".allocated");
                    dialogForm = $('#dialog-form-comment');
                    let splitCurrWeekNum = $('#weekNumber').val().split('/');
                    var data = {
                        "ID": draggable.attr("data-id"),
                        "teamId": draggable.attr("data-teamId"),
                        "startDate": draggable.attr("data-startDate"),
                        "endDate": draggable.attr("data-endDate"),
                        "days": draggable.attr("data-days"),
                        "colorId": draggable.attr("data-colorId"),
                        "dutyDate": draggable.attr("data-date"),
                        "weekNumber": $('#weekNumber').val(),
                        "startWeekDate": $('#stWeekDate').html(),
                        "startDate": $('#stDate').html(),
                        "startWeek": splitCurrWeekNum[1]+splitCurrWeekNum[0]
                    }
                    commentDutyDialog = dialogForm.dialog({
                        autoOpen: false,
                        height: 274.667,
                        width: 433.667,
                        buttons: {
                            "Update": function() {
                                var formData = $(this).find('form').serialize();
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/actions/comment-adhocDuty.php",
                                    data: formData,
                                    success: function (response) {
                                        response = $.parseJSON(response);
                                        if (response.success) {
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/edits/allocation-history.php",
                                                data: formData,
                                                success: function (res) {
                                                    res = $.parseJSON(res);
                                                    if (res.intstatus) {
                                                        $.ajax({
                                                            type: "post",
                                                            url: "/page-includes/allocations/weekly/view-adhocduty.php",
                                                            data: data,
                                                            success: function (dataview) {
                                                                $('#content').html(dataview);
                                                                viewAdhocDutyHeaderSettings();
                                                                commentDutyDialog.html('');
                                                                commentDutyDialog.dialog("close");
                                                                commentDutyDialog.dialog('destroy');
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/modals/comment-adhocDuty-modal.php",
                        data: data,
                        success: function (response) {
                            $('.ui-dialog-title').html("Comments");
                            commentDutyDialog.html(response).dialog({
                                modal: true
                            }).dialog('open');
                        }
                    });
                }
            },
            "history": {
                name: "History",
                icon: "history",
                selected: false,
                callback: function(key, opt, e) {
                    draggable = $(this).find(".allocated");
                    var data = {
                        "ID": draggable.attr("data-id")
                    }
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/modals/history-adhocDuty-modal.php",
                        data: data,
                        success: function (response) {
                            $.facebox(response);
                        }
                    });
                }
            },
        }
    });
});


function deleteAdhocDuty(selectedDayInstance) {
    let id = selectedDayInstance.attr("data-id");
    let teamid = selectedDayInstance.attr("data-scheduling-team-id");
    let date = selectedDayInstance.attr("data-date");
    let dataGet = {
        id: id,
		teamid:teamid,
		date:date
    };
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/delete-adhocDuty.php",
        data: dataGet,
        success: function (res) {
            res = $.parseJSON(res);
        }
    });
}

function checkMiscellaneousDuty(teamId) {
    let dutyName = $('#DutyName').val();
    let startTime = $('#starttimemasterduty').val();
    let splitStartTime = startTime.split(':');
    let endTime = $('#endtimemasterduty').val();
    let splitEndTime = endTime.split(':');
    let dutyRowId = $('#allocationsDutyId').val();

    let dataAllocPost = {
        'action': 'getallocationrowdetail',
        'id': dutyRowId
    }
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
        data: dataAllocPost,
        beforeSend: function(jqXHR, settings){
            $('#loading').hide();
        },
        success: function (resp) {
            resp = $.parseJSON(resp);
            if (resp.status) {
                if (resp.data.DutyName != dutyName) {
                    let dataGet = {
                        'action': 'checkDutyName',
                        'teamId': teamId,
                        'dutyName': dutyName
                    };
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                        data: dataGet,
                        beforeSend: function(jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (res) {
                            res = $.parseJSON(res);
                            if (res.status) {
                                let dutyColourId = parseInt(res.dutyColourId);
                                $('#DutyName').val(res.miscDutyName);
                                $('#Duration').val(res.duration);
                                $('#Duration').prop("disabled", false);
                                $('#miscDuty').val('Yes');
                                $('#currDurationVal').val(res.duration);
								let dutyTypehidden = $('#dutyTypehidden').val();
								if(dutyTypehidden == 1)
								{
									$('#Duration').focus();
									$('#dutyTypehidden').val(0);
									$('#ddldutycolour').trigger('chosen:close');
									$('#labelId').trigger('chosen:close');
								}
                                $("#starttimemasterduty").attr("disabled", "disabled").val('--:--');
                                $("#endtimemasterduty").attr("disabled", "disabled").val('--:--');
                                $("#ddldutycolour").val(dutyColourId).trigger("chosen:updated");
                                $("#breakTimeHour").val(res.dutyBreakTimeHr).trigger("chosen:updated");
                                $("#breakTimeMinute").val(res.dutyBreakTimeMin).trigger("chosen:updated");
                                $("#breakTimeHour").prop('disabled',true).trigger("chosen:updated");
                                $("#breakTimeMinute").prop('disabled',true).trigger("chosen:updated");
                                $('#startTimeStar').hide();
                                $('#endTimeStar').hide();
                            } else {
                                if ((splitStartTime[0] == '00' || splitStartTime[0] == '--') && (splitStartTime[1] == '00' || splitStartTime[1] == '--')) {
                                    $('#starttimemasterduty').val('00:00');
                                }
                                if ((splitEndTime[0] == '00' || splitEndTime[0] == '--') && (splitEndTime[1] == '00' || splitEndTime[1] == '--')) {
                                    $('#endtimemasterduty').val('00:00');
                                }
                                $("#starttimemasterduty").prop("disabled", false);
                                $("#endtimemasterduty").prop("disabled", false);
                                $("#breakTimeHour").prop('disabled',false).trigger("chosen:updated");
                                $("#breakTimeMinute").prop('disabled',false).trigger("chosen:updated");
								let dutyTypehidden = $('#dutyTypehidden').val();
								if(dutyTypehidden == 0)
								{
									$('#starttimemasterduty').focus();
									$('#dutyTypehidden').val(1);
									$('#breakTimeHour').trigger('chosen:close');
									$('#breakTimeMinute').trigger('chosen:close');
									$('#ddldutycolour').trigger('chosen:close');
									$('#labelId').trigger('chosen:close');
								}
                                $('#Duration').prop("disabled", true);
                                $('#startTimeStar').show();
                                $('#endTimeStar').show();
                                calculateDuration();
                            }
                        }
                    });
                } else {
                    let dataGet = {
                        'action': 'checkDutyName',
                        'teamId': teamId,
                        'dutyName': dutyName
                    };
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                        data: dataGet,
                        beforeSend: function(jqXHR, settings){
                            $('#loading').hide();
                        },
                        success: function (res) {
                            res = $.parseJSON(res);
                            if (res.status) {
                                let dutyColourId = parseInt(res.dutyColourId);
                                $('#DutyName').val(res.miscDutyName);
                                $('#Duration').prop("disabled", false);
                                $('#miscDuty').val('Yes');
                                if(splitStartTime[0] == '--' && res.duration != $('#Duration').val()){
                                    $('#currDurationVal').val($('#Duration').val());
                                } else {
                                    $('#currDurationVal').val(res.duration);
                                    $('#Duration').val(res.duration);
                                }
								let dutyTypehidden = $('#dutyTypehidden').val();
								if(dutyTypehidden == 1)
								{
									$('#Duration').focus();
									$('#dutyTypehidden').val(0);
									$('#ddldutycolour').trigger('chosen:close');
									$('#labelId').trigger('chosen:close');
								}
                                $("#starttimemasterduty").attr("disabled", "disabled").val('--:--');
                                $("#endtimemasterduty").attr("disabled", "disabled").val('--:--');
                                $("#breakTimeHour").val(res.dutyBreakTimeHr).trigger("chosen:updated");
                                $("#breakTimeMinute").val(res.dutyBreakTimeMin).trigger("chosen:updated");
                                $("#breakTimeHour").prop('disabled',true).trigger("chosen:updated");
                                $("#breakTimeMinute").prop('disabled',true).trigger("chosen:updated");
                                $('#startTimeStar').hide();
                                $('#endTimeStar').hide();
                            } else {
                                $('#starttimemasterduty').prop("disabled", false).val(resp.data.sTTime);
                                $('#endtimemasterduty').prop("disabled", false).val(resp.data.eNTime);
                                if(dutyName != 'U'){
                                    $("#breakTimeHour").val(resp.data.rowBreakTimeHr).prop('disabled',false).trigger("chosen:updated");
                                    $("#breakTimeMinute").val(resp.data.rowBreakTimeMin).prop('disabled',false).trigger("chosen:updated");
                                }
                                $('#Duration').prop("disabled", true);
                                $('#startTimeStar').show();
                                $('#endTimeStar').show();
								let dutyTypehidden = $('#dutyTypehidden').val();
								if(dutyTypehidden == 0)
								{
									$('#starttimemasterduty').focus();
									$('#dutyTypehidden').val(1);
									$('#breakTimeHour').trigger('chosen:close');
									$('#breakTimeMinute').trigger('chosen:close');
									$('#ddldutycolour').trigger('chosen:close');
									$('#labelId').trigger('chosen:close');
								}
                                calculateDuration();
                            }
                        }
                    });
                }
            }
        }
    });
}

function calculateDuration(id = 0) {
    var startTime = $('#starttimemasterduty').val() ? $('#starttimemasterduty').val() : 0;
    var endTime = $('#endtimemasterduty').val() ? $('#endtimemasterduty').val() : 0;
    var breakTimeHr = $('#breakTimeHour').val();
    var breakTimeMin = $('#breakTimeMinute').val();
	if(startTime == '0'){
        setTimeout(function(){
            $('#starttimemasterduty').val('00:00');
        }, 500);
		startTime = '00:00';
    }
	if(endTime == '0'){
        setTimeout(function(){
            $('#endtimemasterduty').val('00:00');
        }, 500);
		endTime = '00:00';
    }
	if((!startTime.match(':')) && (startTime.length == 4)){
		startTime = startTime[0]+startTime[1]+':'+startTime[2]+startTime[3];
	}
	if((!endTime.match(':')) && (endTime.length == 4)){
		endTime = endTime[0]+endTime[1]+':'+endTime[2]+endTime[3];
	}
	if(startTime.length == 3){
		startTime = startTime + '00';
	}
	if(endTime.length == 3){
		endTime = endTime + '00';
	}
	if(startTime.length == 2){
		startTime = startTime + ':00';
	}
	if(endTime.length == 2){
		endTime = endTime + ':00';
	}
	if(startTime.length == 1){
		startTime = startTime + '0:00';
	}
	if(endTime.length == 1){
		endTime = endTime + '0:00';
	}
	$('#starttimemasterduty').val(startTime);
	$('#endtimemasterduty').val(endTime);

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
        pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
    }

    if(startTimeSec > endTimeSec){
        endTimeSec = parseInt(86400 + endTimeSec);
    }

    if(parseInt(startTimeSec) > parseInt(checkPdlStartTime)){
        checkPdlStartTime = parseInt(86400 + checkPdlStartTime);
    }

    if((checkPdlStartTime < parseInt(startTimeSec)) && (checkPdlStartTime != 0)){
        $('#errorDiv').html('Duty start time cannot be later than Part Day Leave start time.');
        $('#errorDiv').show();
        $('.ui-dialog-buttonset').find('button:nth-child(1)').addClass('ui-state-disabled');
    } else if((pdlEndTimeSec > parseInt(endTimeSec)) && (pdlEndTimeSec != 0)){
        $('#errorDiv').html('Duty end time cannot be earlier than Part Day Leave end time.');
        $('#errorDiv').show();
        $('.ui-dialog-buttonset').find('button:nth-child(1)').addClass('ui-state-disabled');
    } else if((checkPdlStartTime == parseInt(startTimeSec)) && (pdlEndTimeSec == parseInt(endTimeSec))){
        $('#errorDiv').html('Duty start and end time cannot be the same as Part Day Leave start and end time. Please change the duty start or end time.');
        $('#errorDiv').show();
        $('.ui-dialog-buttonset').find('button:nth-child(1)').addClass('ui-state-disabled');
    } else {
        let dutyDur = 0;
        let pdlDur = 0;
        let breakDur = 0;
        let durIncBreakTimeAnd15Min = 0;
        let dutyDurExcPdlDur = 0;
        if((pdlStartTimeSec != 0 && Number.isInteger(pdlStartTimeSec)) || (pdlEndTimeSec != 0 && Number.isInteger(pdlEndTimeSec))){
            dutyDur = parseInt(parseInt(endTimeSec) - parseInt(startTimeSec));
            pdlDur = parseInt(parseInt(pdlEndTimeSec) - parseInt(pdlStartTimeSec));
            breakDur = (+breakTimeHr) * 60 * 60 + (+breakTimeMin) * 60;
            durIncBreakTimeAnd15Min = parseInt(breakDur+4500);
            dutyDurExcPdlDur = parseInt(dutyDur - pdlDur);
        }

        if(dutyDurExcPdlDur >= durIncBreakTimeAnd15Min){
            if($('.ui-dialog-buttonset').find('button:nth-child(1)').hasClass('ui-state-disabled')){
                $('.ui-dialog-buttonset').find('button:nth-child(1)').removeClass('ui-state-disabled');
            }
            if(startTime != undefined){
                let StartMinute = startTime.length > 3 ? startTime.substring(3, 5) : startTime;
                let EndMinute = endTime.length > 3 ? endTime.substring(3, 5) : endTime;
                if ((parseInt(StartMinute) % 15) != 0) {
                    $('#errorDiv').html("Only 15 Minute intervals are allowed for the Start time.");
                    $('#errorDiv').show();
                    $("#starttimemasterduty").focus();
                    calculateNormalDutyDuration($('#starttimemasterduty').val(), $('#endtimemasterduty').val(), $('#breakTimeHour').val(), $('#breakTimeMinute').val());
                    return;
                } else {
                    $('#errorDiv').hide();
                }
                if ((parseInt(EndMinute) % 15) != 0) {
                    $('#errorDiv').html("Only 15 Minute intervals are allowed for the End time.");
                    $('#errorDiv').show();
                    $("#endtimemasterduty").focus();
                    calculateNormalDutyDuration($('#starttimemasterduty').val(), $('#endtimemasterduty').val(), $('#breakTimeHour').val(), $('#breakTimeMinute').val());
                    return;
                } else {
                    $('#errorDiv').hide();
                }
            }

            if (id == 0) {
                startTime = $('#starttimemasterduty').val();
                endTime = $('#endtimemasterduty').val();
                calculateNormalDutyDuration(startTime, endTime, breakTimeHr, breakTimeMin);
            } else {
                let dataAllocPost = {
                    'action': 'getallocationrowdetail',
                    'id': id
                }
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                    data: dataAllocPost,
                    beforeSend: function(jqXHR, settings){
                        $('#loading').hide();
                    },
                    success: function (resp) {
                        resp = $.parseJSON(resp);
                        if (resp.status) {
                            calculateMiscellaneoudDutyDuration(breakTimeHr, breakTimeMin, resp.data.Duration);
                        }
                    }
                });
            }
        } else {
            $('#errorDiv').html('Duty duration cannot be the same as PDL duration. Please ensure a duty duration of at least 1.25 hrs earlier than the start time or later than the end time of the PDL.');
            $('#errorDiv').show();
            $('.ui-dialog-buttonset').find('button:nth-child(1)').addClass('ui-state-disabled');
        }
    }
}

function checkMiscDutyFilterBox(param1='') {
    if (($('#showMastMiscDuty').prop("checked") == true) || (param1 != '' && param1 == 'showFilter')) {
        $('#showMiscDutyFilter').val('showFilter');
		$('.dutiesListTR').css("display", "block");
        $(".resiz").resizable({
            minHeight: 150
        });
        $.cookie('showMiscDutyFilter'+$('#teamId').val(),'showFilter');
        if (($('#searchMiscDutyFilter').val() != '') && ($('#searchMiscDutyFilter').val() != undefined)) {
            searchMiscDuty();
        }
    } else {
        $('#showMiscDutyFilter').val('hideFilter');
        $('.dutiesListTR').css("display", "none");
        $(".resiz").resizable({
            minHeight: 48
        });
        $.cookie('showMiscDutyFilter'+$('#teamId').val(),'hideFilter');
    }
    setBlockScrollPositions('miscDuty');
}

var typingTimer;
var doneTypingInterval = 500;
if (($('#searchMiscDutyFilter').val() != '') && ($('#searchMiscDutyFilter').val() != undefined)) {
    $('#searchMiscDuty').val($('#searchMiscDutyFilter').val());
    searchMiscDuty();
}

function searchMisccDutyKu(){
    if($('#showMastMiscDuty').prop('checked') == true){
        clearTimeout(typingTimer);
        typingTimer = setTimeout(searchMiscDuty, doneTypingInterval);
    }
}

function searchMisccDutyKd(){
    clearTimeout(typingTimer);
}

function searchMiscDuty() {
    let searchStr = $('#searchMiscDuty').val();
    let teamId = $('#searchTeamId').val();

    $('#searchMiscDutyFilter').val(searchStr);
    let dataPost = {
        'action': 'searchMiscDuty',
        'teamId': teamId,
        'searchStr': searchStr
    };
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
        data: dataPost,
        success: function (res) {
            res = $.parseJSON(res);
            if (searchStr != '') {
                if (res.status) {
                    $(res.dataHide).each(function(index, value) {
                        $('#miscDutyRow' + value).hide();
                    });
                    $(res.dataShow).each(function(ind, val) {
                        $('#miscDutyRow' + val).show();
                    });
                } else {
                    $('#miscDutyNoRow').show();
                }
            } else {
                if (res.status) {
                    $(res.data).each(function(index, value) {
                        $('#miscDutyRow' + value).show();
                    });
                } else {
                    $('#miscDutyNoRow').show();
                }
            }
        }
    });
}

function setContainerScrollPos() {
    let top = $('#miscDutyBlockPos').position().top;
    let left = $('#miscDutyBlockPos').position().left;
    $.cookie("miscDutyBlockPosTop", top);
}

function setBlockScrollPositions(paramSet = '') {
    if (paramSet == '') {
        $('#miscDutyBlock').scrollTop(Math.abs($.cookie("miscDutyBlockPosTop")));
        $('#weeklyUnAllocation-2').scrollTop(Math.abs($.cookie("unallocDutyBlockPosTop") - 2));
        $('#weeklyAllocation-2').scrollLeft(Math.abs($.cookie("allocationWeekPosLeft")));
        $('#weeklyAllocation-1').scrollTop(Math.abs($.cookie("teamPeopleBlockPosTop")));
        $('#weeklyAllocation-1').scrollLeft(Math.abs($.cookie("teamPeopleBlockPosLeft")));
    } else {
        $('#miscDutyBlock').scrollTop(Math.abs($.cookie("miscDutyBlockPosTop")));
    }
}

function dutyTimeSanitization(timeVal, elementId) {
    let elementResorce = document.getElementById(elementId);
    if (timeVal == '0') {
        setTimeout(function() {
            elementResorce.value = '00:00';
            elementResorce.blur();
        }, 1000);
        return true;
    }
    let timeValArr = timeVal.split(':');
    if (timeValArr[0] < 24 && timeValArr[1] < 60) {

    } else {
        elementResorce.value = '00:00';
    }
}


function SignInToDay(DutyName, action, date, StartTime, EndTime, uIdStr, SigninStatus) {
    $("#dialog-sign-in").dialog(

        {
            width: 600,
            open: function() {
                $(this).siblings('.ui-dialog-buttonpane').find('button:eq(1)').focus();
            },
            buttons: {
                "Sign-In / Un-Sign In": function() {
                    $('*').qtip('hide');
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/allocations-weekly-signin-day.php",
                        data: {
                            sdate: date,
                            DutyName: DutyName,
                            action: action,
                            StartTime: StartTime,
                            EndTime: EndTime,
                            screenType: '0',
							SigninStatus: SigninStatus,
                            allocationsDutyId: $("#colUnqId_"+uIdStr).attr("data-allocations-duty-id"),
                            allocationsSpId: $("#colUnqId_"+uIdStr).attr("data-allocations-sp-id")
                        },
                        success: function (data) {
                            var dataInstanceId = uIdStr;
                            refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
                        }
                    });
                    $(this).dialog("close");
                },
                "Mark As In Building": function() {
                    $('*').qtip('hide');
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/allocations-weekly-signin-day.php",
                        data: {
                            sdate: date,
                            DutyName: DutyName,
                            action: 2,
                            StartTime: StartTime,
                            EndTime: EndTime,
                            screenType: '0',
							SigninStatus: SigninStatus,
                            allocationsDutyId: $("#colUnqId_"+uIdStr).attr("data-allocations-duty-id"),
                            allocationsSpId: $("#colUnqId_"+uIdStr).attr("data-allocations-sp-id")
                        },
                        success: function (data) {
                            if (data == 0) {
                                $("#dialog-noinbuilding").dialog({
                                    title: "Alert!!",
                                    resizable: false,
                                    height: 180,
                                    width: 500,
                                    modal: true,
                                    buttons: {
                                        OK: function() {
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            } else {
                                var dataInstanceId = uIdStr;
                                refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
                            }
                        }
                    });
                    $(this).dialog("close");
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        }
    );
}

function SignInDay(DutyName, action, date, StartTime, EndTime, uIdStr, SigninStatus) {
    $('*').qtip('hide');
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/allocations-weekly-signin-day.php",
        data: {
            sdate: date,
            DutyName: DutyName,
            action: action,
            StartTime: StartTime,
            EndTime: EndTime,
            screenType: '0',
			SigninStatus: SigninStatus,
			allocationsDutyId: $("#colUnqId_"+uIdStr).attr("data-allocations-duty-id"),
			allocationsSpId: $("#colUnqId_"+uIdStr).attr("data-allocations-sp-id")
        },
        success: function (data) {
            var dataInstanceId = uIdStr;
            refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
        }
    });
}

$('.signedtip').each(function() {
    $(this).qtip({
        content: {
            text: function(event, api) {
                $.ajax({
                        url: 'page-includes/allocations/allocations-signedin-info.php',
                        type: 'POST',
                        data: {
                            allocationsSPID: api.elements.target.attr('data-allocations-sp-id'),
                            StartTime: api.elements.target.attr('data-row-start'),
                            EndTime: api.elements.target.attr('data-row-end'),
                            DutyName: api.elements.target.attr('data-duty-name')

                        }
                    })
                    .then(function(content) {
                            // Set the tooltip content upon successful retrieval
                            api.set('content.text', content);
                        },
                        function(xhr, status, error) {
                            // Upon failure... set the tooltip content to error
                            api.set('content.text', status + ': ' + error);
                        });
                return 'Loading...'; // Set some initial text
            }
        },
        position: {
            viewport: $(window)
        },
        style: 'qtip-rounded qtip-shadow qtip-light'
    });
});

function updateCalculatedDuration(id) {
    let breakTimeHr = $('#breakTimeHour').val();
    let breakTimeMin = $('#breakTimeMinute').val();
    var startTime = $('#starttimemasterduty').val();
    if (startTime == '--:--') {
        startTime = '00:00';
    }
    var endTime = $('#endtimemasterduty').val();
    if (endTime == '--:--') {
        endTime = '00:00';
    }
    let dutyDuration = $('#Duration').val();
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
    let pdlEndTimeSec = (($('#pdlEndTime').val() != '') && ($('#pdlEndTime').val() != undefined)) ? parseInt($('#pdlEndTime').val()) : 0;
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
        if($('.ui-dialog-buttonset').find('button:nth-child(1)').hasClass('ui-state-disabled')){
            $('.ui-dialog-buttonset').find('button:nth-child(1)').removeClass('ui-state-disabled');
        }
        if (breakTimeHr != '0' && breakTimeMin == '0') {
            if ((startTime != '0' && endTime == '0') || (startTime == '0' && endTime != '0')) {
                $('#Duration').val('00:00');
                $('#currDurationVal').val('00:00');
            }
            if ((startTime != '0' && endTime != '0') && (startTime != '00:00' && endTime != '00:00')) {
                Object.assign(dataGet, {
                    "action": "calculateduration",
                    "startTime": startTime,
                    "endTime": endTime
                });
                $('#loading').hide();
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                    data: dataGet,
                    beforeSend: function(jqXHR, settings){
                        $('#loading').hide();
                    },
                    success: function (res) {
                        res = $.parseJSON(res);
                        if (res.status) {
                            let updDuration = res.duration;
                            deductBreakTimeFromDuration('updatecalculateddurationbyhr', breakTimeHr, '', updDuration);
                        } else {
                            $('#Duration').val('00:00');
                            $('#currDurationVal').val('00:00');
                            $('#errorDiv').html(res.mess);
                            $('#errorDiv').show();
                        }
                    }
                });
            } else {
                deductBreakTimeFromDuration('updatecalculatedduration', breakTimeHr, '00', dutyDuration, id, dutyName, startTime, endTime);
            }
        }
        if (breakTimeHr == '0' && breakTimeMin != '0') {
            if ((startTime != '0' && endTime == '0') || (startTime == '0' && endTime != '0')) {
                $('#Duration').val('00:00');
                $('#currDurationVal').val('00:00');
            }
            if ((startTime != '0' && endTime != '0') && (startTime != '00:00' && endTime != '00:00')) {
                Object.assign(dataGet, {
                    "action": "calculateduration",
                    "startTime": startTime,
                    "endTime": endTime
                });
                $('#loading').hide();
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                    data: dataGet,
                    beforeSend: function(jqXHR, settings){
                        $('#loading').hide();
                    },
                    success: function (res) {
                        res = $.parseJSON(res);
                        if (res.status) {
                            let updDuration = res.duration;
                            deductBreakTimeFromDuration('updatecalculateddurationbymin', '', breakTimeMin, updDuration);
                        } else {
                            $('#Duration').val('00:00');
                            $('#currDurationVal').val('00:00');
                            $('#errorDiv').html(res.mess);
                            $('#errorDiv').show();
                        }
                    }
                });
            } else {
                deductBreakTimeFromDuration('updatecalculatedduration', '00', breakTimeMin, dutyDuration, id, dutyName, startTime, endTime);
            }
        }
        if (breakTimeHr != '0' && breakTimeMin != '0') {
            if ((startTime != '0' && endTime == '0') || (startTime == '0' && endTime != '0')) {
                $('#Duration').val('00:00');
                $('#currDurationVal').val('00:00');
            }
            if ((startTime != '0' && endTime != '0') && (startTime != '00:00' && endTime != '00:00')) {
                Object.assign(dataGet, {
                    "action": "calculateduration",
                    "startTime": startTime,
                    "endTime": endTime
                });
                $('#loading').hide();
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
                    data: dataGet,
                    beforeSend: function(jqXHR, settings){
                        $('#loading').hide();
                    },
                    success: function (res) {
                        res = $.parseJSON(res);
                        if (res.status) {
                            let updDuration = res.duration;
                            deductBreakTimeFromDuration('updatecalculateddurationbyhrmin', breakTimeHr, breakTimeMin, updDuration);
                        } else {
                            $('#Duration').val('00:00');
                            $('#currDurationVal').val('00:00');
                            $('#errorDiv').html(res.mess);
                            $('#errorDiv').show();
                        }
                    }
                });
            } else {
                deductBreakTimeFromDuration('updatecalculatedduration', breakTimeHr, breakTimeMin, dutyDuration, id, dutyName, startTime, endTime);
            }
        }
        if (breakTimeHr == '0' && breakTimeMin == '0') {
            calculateDuration(0);
        }
    } else {
        $('#errorDiv').html('Duty duration cannot be the same as PDL duration. Please ensure a duty duration of at least 1.25 hrs earlier than the start time or later than the end time of the PDL.');
        $('#errorDiv').show();
        $('.ui-dialog-buttonset').find('button:nth-child(1)').addClass('ui-state-disabled');
    }
}

function deductBreakTimeFromDuration(actionName, breakHr, breakMin, Duration, id, DutyName, startTime='', endTime='') {
    let dataPost = {};
    if (actionName == 'updatecalculateddurationbyhr') {
        Object.assign(dataPost, {
            "action": actionName,
            "breakHr": breakHr,
            'Duration': Duration,
            'startTime': startTime,
            'endTime': endTime
        });
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
            data: dataPost,
            beforeSend: function(jqXHR, settings){
                $('#loading').hide();
            },
            success: function (resPost) {
                resPost = $.parseJSON(resPost);
                if (resPost.status) {
                    if($('#errorDiv').html() != ''){
                        $('#errorDiv').html('');
                        $('#errorDiv').hide();
                    }
                    $('#Duration').val(resPost.duration);
                    $('#currDurationVal').val(resPost.duration);
                } else {
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
            'Duration': Duration,
            'startTime': startTime,
            'endTime': endTime
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
                    $('#Duration').val(resPost.duration);
                    $('#currDurationVal').val(resPost.duration);
                } else {
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
            'Duration': Duration,
            'startTime': startTime,
            'endTime': endTime
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
                    $('#Duration').val(resPost.duration);
                    $('#currDurationVal').val(resPost.duration);
                } else {
                    $('#errorDiv').html(resPost.mess);
                    $('#errorDiv').show();
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
                if (resPost.status) {
                    if($('#errorDiv').html() != ''){
                        $('#errorDiv').html('');
                        $('#errorDiv').hide();
                    }
                    $('#Duration').val(resPost.duration);
                    $('#currDurationVal').val(resPost.duration);
                } else {
                    $('#errorDiv').html(resPost.mess);
                    $('#errorDiv').show();
                }
            }
        });
    }
}

function ChargingOpenPopup(schedulingPersonId, allocid, teamId, allocationsDutyId, allocationsSpId) {
    $.ajax({
        url: "page-includes/admin/charging/index.php",
        type: "POST",
        dataType: "html",
        async: false,
        data: {
            conrollerName: 'popupChargingOpen',
            schedulingPersonId: schedulingPersonId,
            allocid: allocid,
            teamId: teamId,
            allocationsDutyId: allocationsDutyId,
            allocationsSpId: allocationsSpId
        },
        success: function(data) {
            if ($($.parseHTML(data)).filter("#charging-container-Popup").length) {
                $.facebox(data);
            } else {
                customAlert(data);
            }
            return false;
        }
    });
}

function editAllocateDuty(modalTitle,keyObj, dataInstanceId) {
    var dataId = $("#colUnqId_"+dataInstanceId).attr("data-id");
    var dataDate = $("#colUnqId_"+dataInstanceId).attr("data-date");
    var dataIday = $("#colUnqId_"+dataInstanceId).attr("data-iday");
    var dataSchedulingPersonId = $("#colUnqId_"+dataInstanceId).attr("data-scheduling-person");
    var dataPDLStartTime = parseInt($("#colUnqId_"+dataInstanceId).attr("data-leave-starttime"));
    var dataPDLEndTime = parseInt($("#colUnqId_"+dataInstanceId).attr("data-leave-endtime"));
    var dataPDLEndTimeArg = convertSecondsIntoTime(parseInt(dataPDLEndTime),':','No');
    var allocationsDutyId = $("#colUnqId_"+dataInstanceId).attr("data-allocations-duty-id");
    var allocationsSpId = $("#colUnqId_"+dataInstanceId).attr("data-allocations-sp-id");
    var allocationsDate = dataDate;
    var allocationsSchPer = $("#colUnqId_"+dataInstanceId).attr("data-scheduling-person");
	let teamId = $("#adhocFilter").val();
    if(parseInt(dataPDLStartTime) > parseInt(dataPDLEndTime)){
        dataPDLEndTime = parseInt(86400 + parseInt(dataPDLEndTime));
    }
    if(dataPDLEndTime == 86400){
        dataPDLEndTimeArg = '00:00';
    }
    dialogForm = $('#dialog-form-duty');
    if (allocationsSpId == '0') {
        var data = {
            "ID": dataId,
            "dutyDate": dataDate,
            "iDay": dataIday,
            "schedulingPersonId": dataSchedulingPersonId,
            "teamId": teamId,
            "weekNum": $('#getWeekNumber').val(),
            "action": 'add',
            "pdlStartTime": dataPDLStartTime,
            "pdlEndTime": dataPDLEndTime,
            "pdlStartTimeHrMin": dataPDLStartTime ? convertSecondsIntoTime(parseInt(dataPDLStartTime),':','No'):'',
            "pdlEndTimeHrMin": dataPDLEndTimeArg,
            "pdlDurationHrMin": parseInt(dataPDLEndTime-dataPDLStartTime)> 0 ? convertSecondsIntoTime(parseInt(dataPDLEndTime-dataPDLStartTime),'.','Yes') : '',
			"allocationsDutyId": allocationsDutyId,
			"allocationsSpId": allocationsSpId,
			"allocationsDate": allocationsDate,
			"allocationsSchPer": allocationsSchPer,
        }
    } else {
        var data = {
            "ID": dataId,
            "dutyDate": dataDate,
            "teamId": teamId,
            "action": keyObj,
            "pdlStartTime": (dataPDLStartTime > 0) ? dataPDLStartTime : '',
            "pdlEndTime": (dataPDLEndTime > 0) ? dataPDLEndTime : '',
            "pdlStartTimeHrMin": (dataPDLStartTime > 0) ? convertSecondsIntoTime(parseInt(dataPDLStartTime),':','No') : '',
            "pdlEndTimeHrMin": dataPDLEndTimeArg,
            "pdlDurationHrMin": convertSecondsIntoTime(parseInt(dataPDLEndTime-dataPDLStartTime),'.','Yes'),
			"allocationsDutyId": allocationsDutyId,
			"allocationsSpId": allocationsSpId,
			"allocationsDate": allocationsDate,
			"allocationsSchPer": allocationsSchPer
        }
    }
    $("#loading").hide();
    dialogForm.html('');
    editDutyDialog = dialogForm.dialog({
        autoOpen: false,
        width: 700,
        modal: true,
        buttons: {
            "Update Duty": function() {
                $("#loading").hide();
                let formDataArray = $('#editDutyForm').serializeArray();
                let errorMessage = '';
                for (let m = 0; m < formDataArray.length; m++) {
                    if (((formDataArray[m].name).trim() == 'DutyName') && (((formDataArray[m].value).trim() == '') || ((formDataArray[m].value).trim() == 'U'))) {
                        errorMessage += 'Please enter duty name<br>';
                        $("#DutyName").val((formDataArray[m].value).trim());
                        $("#DutyName").focus();
                    } else {
                        if (formDataArray[m].name == 'StartTime' && formDataArray[m].value == '00:00') {
                            if (formDataArray[m].name == 'Duration' && formDataArray[m].value == '00:00') {
                                errorMessage += 'Please enter start time and end time of duty<br>';
                            }
                        } else {
                            if (formDataArray[m].name == 'dutyColorId' && formDataArray[m].value == '') {
                                errorMessage += 'Please select duty colour<br>';
                            }
                        }
                    }
                }

                let miscDuty = 'No';
                let loopdutybreakhr = parseInt($('#breakTimeHour').val());
                let loopdutybreaksec = parseInt($('#breakTimeMinute').val());
                let formStartTime = $('#starttimemasterduty').val();
                let formEndTime = $('#endtimemasterduty').val();
                if(formStartTime != '--:--'){
                    let dutystarttime = formStartTime.split(':');
                    startTimeSec = (+dutystarttime[0]) * 60 * 60 + (+dutystarttime[1]) * 60;
                    let dutyendtime = formEndTime.split(':');
                    endTimeSec = (+dutyendtime[0]) * 60 * 60 + (+dutyendtime[1]) * 60;
                    var rawTime = endTimeSec;
                    if(startTimeSec > endTimeSec){
                        endTimeSec = 86400+endTimeSec;
                        var dutyDurationSec = parseInt(endTimeSec)-parseInt(startTimeSec);
                        let breakTimeMinSec = loopdutybreakhr * 60 * 60;
                        let breakTimeSecSec = loopdutybreaksec * 60;
                        var fnlBreakTimeSec = parseInt(breakTimeMinSec)+parseInt(breakTimeSecSec);
                    } else {
                        var dutyDurationSec = 0;
                        if(startTimeSec == endTimeSec){
                            dutyDurationSec = 86400;
                        } else {
                            dutyDurationSec = parseInt(endTimeSec)-parseInt(startTimeSec);
                        }
                        let breakTimeMinSec = loopdutybreakhr * 60 * 60;
                        let breakTimeSecSec = loopdutybreaksec * 60;
                        var fnlBreakTimeSec = parseInt(breakTimeMinSec)+parseInt(breakTimeSecSec);
                    }
                    if(fnlBreakTimeSec > dutyDurationSec){
                        errorMessage = 'NoSubmitForm';
                    }
                } else {
                    miscDuty = 'Yes';
                    let loopdutyduration = $('#Duration').val();
                    let dutydurtime = loopdutyduration.split(':');
                    durationTimeSec = (+dutydurtime[0]) * 60 * 60 + (+dutydurtime[1]) * 60;
                    let breakTimeMinSec = loopdutybreakhr * 60 * 60;
                    let breakTimeSecSec = loopdutybreaksec * 60;
                    let fnlBreakTimeSec = parseInt(breakTimeMinSec)+parseInt(breakTimeSecSec);
                    if(fnlBreakTimeSec > durationTimeSec){
                        errorMessage = 'NoSubmitForm';
                    }
                }
                $('#miscDuty').val(miscDuty);
               if($('#errorDiv').css('display') == 'block'){
                    errorMessage = 'chkError';
                }
               if(errorMessage != '' && errorMessage != 'NoSubmitForm') {
                    if(errorMessage != '' && errorMessage == 'chkError'){
                        $('#errorDiv').show();
                    } else {
                        $('#errorDiv').html(errorMessage);
                        $('#errorDiv').show();
                    }
                } else if(errorMessage != '' && errorMessage == 'NoSubmitForm'){
                    $('#errorDiv').html('Break Time should not greater than duration.');
                    $('#errorDiv').show();
                } else {
                    let formData = $('#editDutyForm').serialize();
                    $("#loading").hide();
					$('.ui-dialog-buttonset').find('button:nth-child(1)').css('opacity',0.2);
					$('.ui-dialog-buttonset').find('button:nth-child(1)').css('pointer-events','none');
					formData = formData+'&breakTimeHour='+$('#breakTimeHour').val()+'&breakTimeMinute='+$('#breakTimeMinute').val();
                    $.ajax({
                        type: "post",
                        url: "/page-includes/allocations/weekly/actions/mark-action.php",
                        data: formData,
                        beforeSend: function (jqXHR, settings) {
                            $("#loading").hide();
                        },
                        success: function (response) {
                            response = $.parseJSON(response);
                            if (response.success) {
                                if(dataSchedulingPersonId == 0) {
                                    //Update the unallocated cell data
                                    let unallocatedDutyData = $('#editDutyForm').serializeArray();
                                    unallocatedDutyData.push({name: 'breakTimeHour', value: $('#breakTimeHour').val()});
                                    unallocatedDutyData.push({name: 'breakTimeMinute', value: $('#breakTimeMinute').val()});
                                    unallocatedDutyData.push({name: 'startTimeSec', value: typeof startTimeSec != 'undefined' ? startTimeSec : '0'});
                                    unallocatedDutyData.push({name: 'endTimeSec', value: typeof rawTime != 'undefined' ? rawTime : '0'});
                                    unallocatedDutyData.push({name: 'dutyDurationSec', value: dutyDurationSec});
                                    refreshUnAllocatedCell(unallocatedDutyData, editDutyDialog);
                                }  else {
                                    setTimeout(function() {
                                        refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
                                        dutyIdContainer=0;
                                        editDutyDialog.html('');
                                        editDutyDialog.dialog("close");
                                        $('.ui-timepicker-wrapper').css('display','none');
                                    }, 500);
                                }
                            } else {
                                $('#errorDiv').html(response.errMsg);
                                $('#errorDiv').show();
								$('.ui-dialog-buttonset').find('button:nth-child(1)').css('opacity', 1);
								$('.ui-dialog-buttonset').find('button:nth-child(1)').css('pointer-events','auto');
                            }

                        }
                    });
                }
            },
            Cancel: function() {
                $("#loading").hide();
                editDutyDialog.html('');
                editDutyDialog.dialog("close");
                $('.ui-timepicker-wrapper').css('display','none');
            }
        }
    });

    let scheduledPersonNameP = $('.peronname_' + dataSchedulingPersonId).attr('data-order');
    let personDetailString =  scheduledPersonNameP != '' && typeof scheduledPersonNameP != 'undefined' ? ' for ' + scheduledPersonNameP + ' on ' + formatDateForHeader(dataDate) : ' on ' + formatDateForHeader(dataDate) ;

    $('.ui-dialog-title').text("Edit Duty" + personDetailString);

    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/edit-duty-modal.php",
        data: data,
        beforeSend: function(jqXHR, settings){
            $("#loading").hide();
        },
        success: function (response) {
            if(modalTitle == 'createduty'){
                $('.ui-dialog-title').text("Create Duty" + personDetailString);
            } else {
                $('.ui-dialog-title').text("Edit Duty" + personDetailString);
            }
            editDutyDialog.html(response).dialog({
                modal: true,
                open: function(){
                    $('.ui-timepicker-wrapper').css('display','none');
                    durationCalculationforEditDuty();
                    $('#dialog-form-duty').css('max-height', $(editDutyDialog).height() + 50);
                },
                close: function( event, ui ) {
                    editDutyDialog.html('');
                    editDutyDialog.dialog("close");
                    editDutyDialog.dialog('destroy');
                }
            }).dialog('open');
            $(".chosen-select").chosen();
            $("[name='schedulingPersonId']").chosen();
        }
    });
}

function setDurationValue() {
    let getDurationVal = $('#Duration').val();
    if(getDurationVal == 0) {
        $('#Duration').val("00:00");
        $('#currDurationVal').val("00:00");
    }
    let getDurationValMinute = getDurationVal.length > 3 ? getDurationVal.substring(3, 5) : getDurationVal;
    if ((parseInt(getDurationValMinute) % 15) != 0) {
        $('#errorDiv').html("Only 15 Minute intervals are allowed for the Duration.");
        $('#errorDiv').show();
        $("#Duration").val(getDurationVal.substring(0,3)+'00');
        $('#currDurationVal').val(getDurationVal.substring(0,3)+'00');
        $("#Duration").focus();
        return;
    } else {
        $('#errorDiv').hide();
        $('#currDurationVal').val(getDurationVal);
    }
}

function calculateNormalDutyDuration(startTime, endTime, breakTimeHr, breakTimeMin) {
    if (startTime == '--:--') {
        startTime = '00:00';
    }
    if (endTime == '--:--') {
        endTime = '00:00';
    }
    let dataGet = {};
    var dataStartTime = '';
    var dataEndTime = '';
    if (startTime == '0' && endTime != '0') {
        $('#starttimemasterduty').val('00:00');
        dataStartTime = '00:00';
        dataEndTime = endTime;
    } else if (endTime == '0' && startTime != '0') {
        $('#endtimemasterduty').val('00:00');
        dataEndTime = '00:00';
        dataStartTime = startTime;
    } else if ((startTime == '0' && endTime == '0')) {
        $('#Duration').val('00:00');
        dataStartTime = '00:00';
        dataEndTime = '00:00';
    } else if ((startTime != '0' && endTime != '0')) {
        $('#Duration').val('00:00');
        dataStartTime = startTime;
        dataEndTime = endTime;
    }
    Object.assign(dataGet, {
        "action": "calculateduration",
        "startTime": dataStartTime,
        "endTime": dataEndTime,
        "breakTimeHr": breakTimeHr,
        "breakTimeMin": breakTimeMin
    });
    $('#loading').hide();
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
        data: dataGet,
        beforeSend: function(jqXHR, settings){
            $('#loading').hide();
        },
        success: function (res) {
            res = $.parseJSON(res);
            if (res.status) {
                $('#Duration').val(res.duration);
                $('#currDurationVal').val(res.duration);
            } else {
                $('#Duration').val('00:00');
                $('#currDurationVal').val('00:00');
                $('#errorDiv').html(res.mess);
                $('#errorDiv').show();
            }
        }
    });
}

function calculateMiscellaneoudDutyDuration(breakTimeHr, breakTimeMin, dutyDuration) {
    let dataGet = {};
    Object.assign(dataGet, {
        "action": "calculatedurationmisc",
        "breakTimeHr": breakTimeHr,
        "breakTimeMin": breakTimeMin,
        'Duration': dutyDuration
    });
    $('#loading').hide();
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/check-miscellaneous-duty.php",
        data: dataGet,
        beforeSend: function(jqXHR, settings){
            $('#loading').hide();
        },
        success: function (res) {
            res = $.parseJSON(res);
            if (res.status) {
                $('#errorDiv').html('');
                $('#errorDiv').hide();
                $('#Duration').val(res.duration);
                $('#currDurationVal').val(res.duration);
            } else {
                $('#Duration').val('00:00');
                $('#currDurationVal').val('00:00');
                $('#errorDiv').html(res.mess);
                $('#errorDiv').show();
            }
        }
    });
}

function deleteWTDBreachDetails(dutyCell){
    var data = {
        'actionName':'deleteWTDBreachDetails',
        'dutyDate': dutyCell.attr('data-date'),
        'teamId' : dutyCell.attr('data-scheduling-team-id'),
        'schedulingPersonId' : dutyCell.attr('data-scheduling-person')
    }
    customConfirm('This will delete ALL Working Time Directive breaches for this duty. Are you sure you want to do this?',function(){
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/wtd-breach-approval.php",
                data: data,
                success: function (response) {
                    response = $.parseJSON(response);
                    if (response.status == true) {
                        var dataInstanceId = dutyCell.attr("data-unique-id");
                        refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
                    } else {
                        customAlert(response.errMsg);
                    }
                }
            });
        },
        function() {
        }
    );
}

function verifyWtdBreachApproval(dutyCell){
    var data = {
        'actionName':'verifyBreachDetails',
        'startDate': dutyCell.attr('date-start-date'),
        'endDate':  dutyCell.attr('date-end-date'),
        'teamId' :  dutyCell.attr('data-scheduling-team-id')
    }
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/wtd-breach-approval.php",
        data: data,
        success: function (response) {
            response = $.parseJSON(response);
            if (response.status == true) {
                reloadeditweeklygrid('','','','','ALLOC');
            } else {
                customAlert(response.errMsg);
            }
        }
    });
}

function wtdBreachApprovalPopup(scheduledPersonId,dutyDate,teamId,cellId){
    var dataReq = {
        'scheduledPersonId':scheduledPersonId,
        'dutyDate':dutyDate,
        'teamId':teamId,
        'cellId':cellId
    };
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/wtd-approval-modal.php",
        data: dataReq,
		async:false,
        success: function (response) {
            $.facebox(response);
            setTimeout(function() {
                if($('#breachListCount').val() == 1){
                    $('#WTDApproval').trigger('click');
                }
            }, 500);
        }
    });
}

function closeWTDPopup(id) {
    if($('#refreshPageEditWeekly').text() == 'Yes'){
        var dataInstanceId = id;
        refreshAllocatedSectionTr($("#colUnqId_"+dataInstanceId).attr("data-scheduling-person"),$("#colUnqId_"+dataInstanceId).attr("data-row-id"),$("#colUnqId_"+dataInstanceId).attr("data-date"),$("#colUnqId_"+dataInstanceId).attr("data-duty-name"),$("#colUnqId_"+dataInstanceId).attr("data-row-start"),$("#colUnqId_"+dataInstanceId).attr('data-row-end'),$("#colUnqId_"+dataInstanceId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+dataInstanceId).attr('data-id'),0,0,'Yes');
    }
    $('#facebox .close').click();
}

function getBreachDetails(breachId,teamId){
    var dataReq = {
        'actionName':'GetDetails',
        'breachId':breachId,
        'teamId':teamId
    };
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/wtd-breach-approval.php",
        data: dataReq,
        success: function (response) {
            response = $.parseJSON(response);
            if(response.status == true){
                $('#breachType').val(response.breachDetail.Rule);
                $('#breachFrom').val(response.breachDetail.StartDate);
                $('#breachTo').val(response.breachDetail.EndDate);
                $('#breachBy').val(response.breachDetail.BreachedBy);
                $('#breachDate').val(response.breachDetail.BreachedDate);
                $('#breachComments').val($.trim(response.breachDetail.Comments));
                let iniBreachComments = response.breachDetail.Comments;
                let lineBreaksBreachComments = ($.trim(iniBreachComments).match(/\n/g) || []).length;
                let breachCommentsStrLength = parseInt($.trim(iniBreachComments).length);
                let breachRemainStrCharsWithLineBreaks = parseInt(500 - breachCommentsStrLength);
                let breachRemainStrChars = parseInt(breachRemainStrCharsWithLineBreaks + lineBreaksBreachComments);
                $('#remainBreachComments').html(breachRemainStrChars);
                $('#breachHistory').val(response.breachDetail.History);
                $('#WTDApproval').val(response.breachDetail.ID);
                if(response.breachDetail.IsApproved == 1){
                    $('#WTDApproval').prop('checked',true);
                    $('#approvedBy').val(response.aprrovedBy);
                    $('#approvedDate').val(response.approveDate);
                    $('#breachComments').removeClass('disabledField');
                    $('#isBreachApproved').html(1);
                } else {
                    $('#WTDApproval').prop('checked',false);
                    $('#breachComments').addClass('disabledField');
                    $('#approvedBy').val('');
                    $('#approvedDate').val('');
                    $('#isBreachApproved').html(0);
                }

                if(iniBreachComments.length > 20){
                    $('#brchCmts'+breachId).html(iniBreachComments.substr(0,20)+'...');
                } else {
                    $('#brchCmts'+breachId).html(iniBreachComments);
                }
            } else {
                customAlertByModel(response.errMsg);
                resetBreachForm();
            }
        }
    });
}

function approveWTDBreach(breachListCnt=0,breachId=0,teamId=0){
    if($('#WTDApproval').prop('checked') == true){
        if($('#WTDApproval').val() != 'on'){
            $('#breachComments').removeClass('disabledField');
            $('#updateBtn').removeAttr('disabled');
            $('#canceledBtn').removeAttr('disabled');
            var dataReq = {
                'actionName':'SessionUserDetails'
            };
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/wtd-breach-approval.php",
                data: dataReq,
                async: true,
                success: function (response) {
                    response = $.parseJSON(response);
                    if(response.status == true){
                        $('#approvedBy').val(response.aprrovedBy);
                        $('#approvedDate').val(response.approveDate);

                    } else {
                        customAlertByModel(response.errMsg);
                        resetBreachForm();
                    }
                }
            });
        } else {
            if(breachListCnt > 1){
                customAlertByModel('Please select breach from the list.');
            } else {
                if(breachId != 0 && teamId != 0){
                    getBreachDetails(breachId,teamId);
                }
            }
        }
    } else {
        $('.breachrows').removeClass('highlightOrange');
        if(breachListCnt > 1){
            resetBreachForm();
        }
    }
}

function cancelBreachUpdation(scheduledPersonId,dutyDate,teamId){
    if($('#WTDApproval').prop('checked') == true){
        customConfirmModal(
            'Would you like to save the changes you have made to this record?',
            function(){
                var brchcomment = $.trim($('#breachComments').val()).length;
                if(brchcomment < 5){
                    $('#errMsg').html('Please add a meaningful Comment in the Comments field.<br>(i.e - more than 5 characters)');
                    $('#errMsg').css('color','#FF0000');
                    $('#errMsg').show();
                } else {
                    $('#errMsg').html('');
                    $('#errMsg').hide();
                    updateBreachDetails(teamId);
                }
            },
            function(){
                $('#rowNum'+$('#WTDApproval').val()).addClass('WTDNotApproved').removeClass('highlightOrange');
                $('#errMsg').html('');
                $('#errMsg').hide();
                resetBreachForm();
            },
            0,
            'EditWeekly'
        );
    }
}

function updateBreachDetails(teamId){
    var brechcomment = $.trim($('#breachComments').val()).length;
    if(brechcomment < 5){
        $('#errMsg').html('Please add a meaningful Comment in the Comments field.<br>(i.e - more than 5 characters)');
        $('#errMsg').css('color','#FF0000');
        $('#errMsg').show();
    } else {
        $('#errMsg').html('');
        $('#errMsg').hide();
        let editedId = $('#WTDApproval').val();
        if(editedId != 'on'){
            var dataReq = {
                'actionName':'updateBreachDetails',
                'editedId':editedId,
                'breachComments':$('#breachComments').val(),
                'breachApproved':parseInt($('#isBreachApproved').html())
            };
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/wtd-breach-approval.php",
                data: dataReq,
                success: function (response) {
                    response = $.parseJSON(response);
                    if(response.status == true){
                        $('#refreshPageEditWeekly').html('Yes');
                        $('#rowNum'+editedId).addClass('WTDApproved').removeClass('WTDNotApproved');
                        getBreachDetails(editedId,teamId);
                    } else {
                        customAlertByModel(response.errMsg);
                    }
                }
            });
        } else {
            customAlertByModel('Error in updating breach comments and history.');
        }
    }
}

function resetBreachForm(){
    $('#approvedBy').val('');
    $('#approvedDate').val('');
    $('#breachComments').addClass('disabledField');
    $('#breachComments').val('');
    $('#updateBtn').attr('disabled','disabled');
    $('#canceledBtn').attr('disabled','disabled');
    $('#WTDApproval').prop('checked', false);
    $('#WTDApproval').val('on');
    $('#breachType').val('');
    $('#breachFrom').val('');
    $('#breachTo').val('');
    $('#breachBy').val('');
    $('#breachDate').val('');
}

function reloadeditweeklygrid(dutyDataUnalloc='',dutyDataAlloc='',miscDutyData='',dataPickerParam='',showDataType='ALL'){
    $('#loadingWeekly').hide();
    if((dutyDataUnalloc == '') && (dutyDataAlloc == '')){
        var data = $('#ajaxLoadParams').val();
        data = $.parseJSON(data);
        data.queryString = $('#queryString').val();
        data.queryOrder = $('#queryOrder').val();
        data.filterAndSkill = $('#filterAndSkill').val();
        data.filterAndDutyLabel = $('#filterAndDutyLabel').val();
        if($('#date').val() != ''){
            data.weeks = 1;
        } else {
            data.weeks = $('#showWeeks').val();
        }
        data.date = $('#date').val();
        data.mastMiscFilterId = $('#mastMiscFilterId').val();
        data.showDataType = showDataType ? showDataType : 'ALL';
        if(data.date != ''){
            let dataDate = data.date;
            let splitDate = dataDate.split('-');
            data.startDate = splitDate[2]+'-'+splitDate[1]+'-'+splitDate[0];
        }

        $('#ajaxLoadParams').val(JSON.stringify(data));
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/get-edit-weekly-grid-data.php",
            data: data,
            beforeSend: function (jqXHR, settings) {
                if($('#loading').css('display') == 'block'){
                    $("#loading").hide();
                }
                $("#loadingWeekly").show();
				return true;
            },
            success: function (response) {
                let resp = $.parseJSON(response);
                if(showDataType == 'ALL'){
                    let unDutyData = resp.unallocatedduty;
                    if(unDutyData.length > 0){
                        unDutyData = $.parseJSON(resp.unallocatedduty);
                    }
                    let miscData = resp.miscduty;
                    let dtParamData = resp.dateparam;
                    let alDutyData = resp.allocatedduty;
                    if(alDutyData.length > 0){
                        alDutyData = $.parseJSON(resp.allocatedduty);
                    }

					$('#getDayIndicatorData').val(btoa(JSON.stringify(resp.getDayIndicatorData)));
                    let loadUnallocGrid = false;
					if(!isNaN($.cookie('unAllocSortDate'))){
						$('#miscDutyData').val(JSON.stringify(miscData));
						$('#dataPickerData').val(JSON.stringify(dtParamData));
						saveDataInIndexedDb(unDutyData, 1);
						getUnAllocatedSort($.cookie('unAllocSortDate'));
						loadUnallocGrid = true;
					}else
					{
						loadUnallocGrid = loadUnallocatedSingleWeek(unDutyData,miscData,dtParamData,1);
					}
                    if(loadUnallocGrid){
						if($.cookie('allocSortDate') != '')
						{
							saveDataInIndexedDb(IndexToAssociativeArray (alDutyData,'Alloc'));
							getAllocatedSort(7);
						}else
						{
							loadAllocated(alDutyData);
						}
                    }
                } else if(showDataType == 'UNALLOC'){
                    if(resp.shiftCountingFilterId == 0){
                        $('#showHideCount').prop('checked', false);
                        let repShiftCountingDiv = '';
                        if (resp.shiftCountingFiltersList.length > 0) {
                            repShiftCountingDiv += '<option value="NA">No Filter/Edit Filter</option>';
                            $(resp.shiftCountingFiltersList).each(function(index, value) {
                                repShiftCountingDiv += '<option value="' + value.ID + '">' + value.FilterName + '</option>';
                            });
                        } else {
                            repShiftCountingDiv += '<option value="NA">No Filter/Edit Filter</option>';
                        }
                        $('#dutyShiftCountingFilterId').html(repShiftCountingDiv).trigger("chosen:updated");
                    }
                    let unDutyData = resp.unallocatedduty;
                    if(unDutyData.length > 0){
                        unDutyData = $.parseJSON(resp.unallocatedduty);
                    }
                    let miscData = resp.miscduty;
                    let dtParamData = JSON.stringify(resp.dateparam);

                    let loadUnallocGrid = false;
					if(!isNaN($.cookie('unAllocSortDate'))){
						$('#miscDutyData').val(JSON.stringify(miscData));
						$('#dataPickerData').val(JSON.stringify(dtParamData));
						saveDataInIndexedDb(unDutyData, 1);
						getUnAllocatedSort($.cookie('unAllocSortDate'));
						loadUnallocGrid = true;
					}else
					{
						loadUnallocGrid = loadUnallocatedSingleWeek(unDutyData,miscData,dtParamData);
					}
                } else {
                    let alDutyData = resp.allocatedduty;
                    if(alDutyData.length > 0){
                        alDutyData = $.parseJSON(resp.allocatedduty);
                    }
					if($.cookie('allocSortDate') != '')
					{
						saveDataInIndexedDb(IndexToAssociativeArray (alDutyData,'Alloc'));
						getAllocatedSort(7);
					}else
					{
						loadAllocated(alDutyData);
					}
                }
                showHideCountGrid();
                if($.cookie('unallocgridheight') != undefined){
                    $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                    $("#allocatedDuty").height($.cookie('allocgridheight'));
                    $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                    $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                    $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                    $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                    setTimeout(function() {
                        $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                        $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                    }, 1000);
                    if($('#showHideCount').prop('checked') == false){
                        let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                        $("#allocatedDuty").height(allocatedHeight);
                        $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                    }
                }

                $('.dragAlloc').on('dragstart', function (event) {
                    dragSource = $(this);
                    event.stopImmediatePropagation();
                    startDrag(dragSource);
                });
                $('.dropeventscall').on('over', function (event) {
                    event.stopImmediatePropagation();
                    event.preventDefault();
                });
                if($('#showHideCount').prop('checked') == true){
                    reloadShiftCountingGrid();
                }
                if($('#disableDrag').prop('checked') == true){
                    disableDragDrop();
                }
                resizeUnallocatedGrid();
                $('.scrollHorizontal').scroll(function() {
                    $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                });
                $('.scrollVertical').scroll(function() {
                    $('.scrollVertical').scrollTop($(this).scrollTop());
                });
                $("#loading").hide();
            },
            complete: function(){
                $("#loading").hide();
            }
        });
    } else {
        resizeUnallocatedGrid();
        $('#showWeeks').val(1).trigger("chosen:updated");
        let loadUnallocGrid = false;
		if(!isNaN($.cookie('unAllocSortDate'))){
			$('#miscDutyData').val(JSON.stringify(miscDutyData));
			$('#dataPickerData').val(JSON.stringify(dataPickerParam));
			saveDataInIndexedDb(dutyDataUnalloc, 1);
			getUnAllocatedSort('');
			loadUnallocGrid = true;
		}else
		{
			loadUnallocGrid = loadUnallocatedSingleWeek(dutyDataUnalloc,miscDutyData,dataPickerParam,1);
		}
        if(loadUnallocGrid){
			if($.cookie('allocSortDate') != '')
			{
				saveDataInIndexedDb(IndexToAssociativeArray (dutyDataAlloc,'Alloc'));
				getAllocatedSort(7);
			}else
			{
				loadAllocated(dutyDataAlloc);
			}
			$('.scrollHorizontal').scroll(function() {
				$('.scrollHorizontal').scrollLeft($(this).scrollLeft());
			});
			$('.scrollVertical').scroll(function() {
				$('.scrollVertical').scrollTop($(this).scrollTop());
			});

        }
        editWeeklyFilter();
    }
}

function saveDataInIndexedDb(allocProcessData, type=0)
{
	window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
	if (!window.indexedDB)
	{
		console.log("Your browser doesn't support a stable version of sorting functionality.");
	}else
	{
		let dbName = 'allocDatabase';
		if(type)
			dbName = 'unAllocDatabase';
		const request = window.indexedDB.open(dbName, 3);
		request.onupgradeneeded = function () {
		  const db = request.result;
		  const store = db.createObjectStore("weeklyData", { keyPath: "id" });
		}
		request.onsuccess = function () {
		    const db = request.result;
            if(db.objectStoreNames.length){
    		    const transaction = db.transaction("weeklyData", "readwrite");
    		    const store = transaction.objectStore("weeklyData");
	            store.put({ id: 1, mainData:allocProcessData});
    		    const idQuery = store.get(1);
    		    idQuery.onsuccess = function () {
		            //console.log('idQuery', idQuery.result);
    		    };
    		    transaction.oncomplete = function () {
    			    db.close();
    		    };
            } else {
                $('#loading').hide();
                $('#loadingWeekly').hide();
                customAlert("Please close and reopen your browser");
            }
		};
	}
}

function updateDataInIndexedDbForCharging(id, TriangleColour, updateValueFlag = 0)
{
	window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
	if (!window.indexedDB)
	{
		console.log("Your browser doesn't support a stable version of IndexedDB.");
	}else
	{
		let updateValueFlagStr = '';
		switch(updateValueFlag)
		{
			case 0 : updateValueFlagStr = 'TriangleColour'; break;
			case 1 : updateValueFlagStr = 'isAttentionClsName'; break;
			case 2 : updateValueFlagStr = 'isRequest'; break;
		}
		const request = window.indexedDB.open("allocDatabase", 3);
		request.onsuccess = function () {
		  const db = request.result;
          if(db.objectStoreNames.length){
    		  const transaction = db.transaction("weeklyData", "readwrite");
    		  const store = transaction.objectStore("weeklyData");
    		  const idQuery = store.get(1);
    		  idQuery.onsuccess = function () {
    			    let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
                    if(dataArr.length > 0){
                        if(updateValueFlag == 3){
                            dataArr.forEach((number, index, rows) => {
                                if(rows[index]['SchedulingPersonID'] == id){
                                    dataArr[index]['SortCode'] = TriangleColour;
                                }
                            });
                         } else {
                            dataArr.forEach((number, index, rows) => {
                                if(rows[index]['ID'] == id){
                                    dataArr[index][updateValueFlagStr] = TriangleColour;
                                }
                            });
                        }
                        saveDataInIndexedDb(dataArr);
                    }
    		  };
    		  transaction.oncomplete = function () {
    			db.close();
    		  };
            } else {
                $('#loading').hide();
                $('#loadingWeekly').hide();
                customAlert("Please close and reopen your browser");
            }
		};
	}
}

function updateDataInIndexedDb(cellData, loadHtml = 0, newDataOfUnAllocatedCell = {})
{
	//loadHtml values 0, 1. 0 = to not refresh html, 1 = to refresh html for allocated, 2 = to refresh html for unallocated
	window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
	if (!window.indexedDB)
	{
		console.log("Your browser doesn't support a stable version of IndexedDB.");
	}else
	{
		$dbName = 'allocDatabase';
		if(loadHtml > 1)
			$dbName = 'unAllocDatabase';
		const request = window.indexedDB.open($dbName, 3);
		request.onsuccess = function () {
		  const db = request.result;
          if(db.objectStoreNames.length){
    		  const transaction = db.transaction("weeklyData", "readwrite");
    		  const store = transaction.objectStore("weeklyData");
    		  const idQuery = store.get(1);
    		  idQuery.onsuccess = function () {
    			  let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
    		      let dataArr1 = [];
    			  if(loadHtml > 1)
    			  {
    				    let instanceCount = 0;
    				    let instanceIdsStr = '';
    				    let instanceIdsCountStr = 0;
    					if((loadHtml == 2)){
    						let newData = {"0":newDataOfUnAllocatedCell.DutyName, "1":newDataOfUnAllocatedCell.Duration, "2":newDataOfUnAllocatedCell.WeekNumber, "3":newDataOfUnAllocatedCell.iDay, "4":newDataOfUnAllocatedCell.StartTime, "5":newDataOfUnAllocatedCell.EndTime, "6":newDataOfUnAllocatedCell.ID, "7":newDataOfUnAllocatedCell.SchedulingTeamId, "8":newDataOfUnAllocatedCell.SchedulingPersonID, "9":newDataOfUnAllocatedCell.DutyDate, "10":newDataOfUnAllocatedCell.MasterDutyId, "11":newDataOfUnAllocatedCell.isActive, "12":newDataOfUnAllocatedCell.DutyTeamID, "13":newDataOfUnAllocatedCell.DisplayGrid, "14":newDataOfUnAllocatedCell.DutyInstances, "InstanceIds":newDataOfUnAllocatedCell.InstanceIds.toString()};
    						if(newDataOfUnAllocatedCell.DutyInstances > 1)
    						{
    							let updateRemainingIdArr = newDataOfUnAllocatedCell.InstanceIds.split(',');
    							dataArr1.forEach((number1, index1, rows1) => {
    								if(rows1[index1][6] != updateRemainingIdArr[1])
    								{
    									dataArr1[index1]['InstanceIds'] = newDataOfUnAllocatedCell.InstanceIds.toString();
    									dataArr1[index1][14] = newDataOfUnAllocatedCell.DutyInstances;
    								}
    							});
    						}
    						dataArr1.push(newData);
    						setTimeout(function() {
    							$('#rowUnqId_'+newDataOfUnAllocatedCell.ID)[0].scrollIntoView({block: "start"});
    							if($('#showWeeks').val() > 1){
    								$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
    							} else {
    								if($('#date').val() == ''){
    									$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 30);
    								} else {
    									$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
    								}
    							}
    							$( '#rowUnqId_'+newDataOfUnAllocatedCell.ID ).css('border-bottom','2px solid #bbb');
    						}, 1000);
    					}
                        if((loadHtml == 3)){
                            dataArr.forEach((number, index, rows) => {
                                if((rows[index][0] == cellData.DutyName) && (rows[index][4] == cellData.StartTime) && (rows[index][5] == cellData.EndTime) && (rows[index][9] == cellData.DutyDate)){
                                    if(rows[index][14] > 1){
                                        let existsIds = rows[index]['InstanceIds'];
                                        existsIds = existsIds.replace('','');
                                        existsIds = existsIds.match(/\d+/g);
                                        if(rows[index][15] != existsIds[0]){
                                            let existsIds1 = rows[index]['InstanceIds'];
                                            existsIds1 = existsIds1.replace(existsIds[0],'');
                                            existsIds1 = existsIds1.match(/\d+/g);
                                            rows[index][14] = existsIds1.length;
                                            rows[index]['InstanceIds'] = existsIds1.join(',');
                                            dataArr1.push(rows[index]);
                                        }
                                    }
                                    instanceCount++;
                                } else {
                                    dataArr1.push(rows[index]);
                                }
                            });
                        }
                        saveDataInIndexedDb(dataArr1, 1);
                        if(!isNaN($.cookie('unAllocSortDate'))){
                            getUnAllocatedSort($.cookie('unAllocSortDate'));
                        }
    			  }else
    			  {
    				  dataArr.forEach((number, index, rows) => {
    						if((rows[index]['DutyDate'] == cellData.DutyDate) && (rows[index]['SchedulingPersonID'] == cellData.SchedulingPersonID))
    						{
    							dataArr1.push(cellData);
    						}else{
    							if(rows[index]['SchedulingPersonID'] == cellData.SchedulingPersonID)
    							{
    								rows[index]['WeekDuration'] = cellData.WeekDuration;
    							}
                                if(newDataOfUnAllocatedCell.length > 0){
                                    for(let v=0; v<newDataOfUnAllocatedCell.length; v++){
                                        if((rows[index]['SchedulingPersonID'] == newDataOfUnAllocatedCell[v].scheduledPerson && rows[index]['DutyDate'] == newDataOfUnAllocatedCell[v].wtdDate) && (rows[index]['WTDBreachClassName'] != newDataOfUnAllocatedCell[v].WTDBreachClassName)){
                                            rows[index]['WTDBreachClassName'] = newDataOfUnAllocatedCell[v].WTDBreachClassName;
                                        }
                                    }
                                }
                                dataArr1.push(rows[index]);
    						}
    					});
    					saveDataInIndexedDb(dataArr1);
    					if(loadHtml)
    					{
    						getAllocatedSort(7);
    					}
    				}
    		  };
    		  transaction.oncomplete = function () {
    			db.close();
    		  };
            } else {
                $('#loading').hide();
                $('#loadingWeekly').hide();
                customAlert("Please close and reopen your browser");
            }
		};
	}
}
function getAllocatedSort(thisVal,sortType=0)
{
	$("#loadingWeekly").show();
	let currentSortDate		=	'';

    if(($('#SortOrder').val() == '1') && (thisVal == 7)){
        thisVal = 'NAME';
    }
    if(($('#SortOrder').val() == '2') && (thisVal == 7)){
        thisVal = 'SORTCODE';
    }

	switch(thisVal)
	{
		case 7 : currentSortDate = $('#allocSortDate').val(); break;
		case 'NAME' 	: currentSortDate = 'NAME'; break;
		case 'SORTCODE' : currentSortDate = 'SORTCODE'; break;
		case 'HRS' : currentSortDate = 'HRS'; break;
		case 'ACC' : currentSortDate = 'ACC'; break;
		case 'OT' : currentSortDate = 'OT'; break;
		case 'FSV' : currentSortDate = 'FSV'; break;
		case 'EDP' : currentSortDate = 'EDP'; break;
		case 'EFT' : currentSortDate = 'EFT'; break;
		case 'IDAY': currentSortDate = 'IDAY'; break;
		default    : currentSortDate = thisVal;
	}
	let prevSortDate		=	$('#allocSortDate').val();
	let prevSortType		=	$('#allocSortType').val();
	let prevSortTypeNew 	= 	(prevSortType == 0) ? 1 : 0;
    if(($('#SortOrder').val() == '1') && (sortType == 0) && (thisVal == 'NAME')){
        prevSortTypeNew = 0;
    }
	if((currentSortDate == prevSortDate) && (thisVal != 7))
	{
		$('#allocSortType').val(prevSortTypeNew);
	}else if(thisVal != 7)
	{
		$('#allocSortDate').val(currentSortDate);
		$('#allocSortType').val(0);
	}
	$.cookie('allocSortDate', $('#allocSortDate').val());
	$.cookie('allocSortType', $('#allocSortType').val());
	window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
	if (!window.indexedDB)
	{
		console.log("Your browser doesn't support a stable version of IndexedDB.");
	}else
	{
		const request = window.indexedDB.open("allocDatabase", 3);
		request.onsuccess = function () {
		  const db = request.result;
          if(db.objectStoreNames.length){
    		  const transaction = db.transaction("weeklyData", "readwrite");
    		  const store = transaction.objectStore("weeklyData");
    		  const idQuery = store.get(1);
    		  idQuery.onsuccess = function () {
                let scrollLeftPos = $('#weeklyUnAllocation-2').scrollLeft();
    			if($('#allocSortType').val() == 0)
    			{//case of assending
    				let dataArr = idQuery.result.mainData;
    				let sortedArray = [];
    				let dataArr1= dataArr;
    				let sortedArr = [];
    				const dayValArr = [0, 1, 2, 3, 4, 5, 6];
    				let weekNo = $('#weekNumber').val();
    				let weekNoArr = weekNo.split('/');
    				weekNo = weekNoArr[1]+weekNoArr[0];
    				if(dayValArr.includes(parseInt(currentSortDate)))
    				{
    					if(parseInt($('#showWeeks').val()) > 1)
    					{
    						let allocSortDt = $('#allocSortDt').val();
    						allocSortDt = allocSortDt[4]+allocSortDt[5]+allocSortDt[6]+allocSortDt[7]+'-'+allocSortDt[2]+allocSortDt[3]+'-'+allocSortDt[0]+allocSortDt[1];
    						dataArr.forEach((number, index, rows) => {
    							if((rows[index]['iDay'] == currentSortDate) && (rows[index]['DutyDate'] == allocSortDt))
    							{
    								sortedArray.push(rows[index]);
    								delete dataArr1[index];
    							}
    						})
    					}else
    					{
    						dataArr.forEach((number, index, rows) => {
                                if(($('#date').val() != '') && (rows[index]['iDay'] == currentSortDate)){
                                    sortedArray.push(rows[index]);
                                    delete dataArr1[index];
                                } else {
                                    if((rows[index]['iDay'] == currentSortDate) && (parseInt(rows[index]['WeekNumber']) == parseInt(weekNo)))
                                    {
                                        sortedArray.push(rows[index]);
                                        delete dataArr1[index];
                                    }
                                }
    						})
    					}
    					for(let i =0; i < (sortedArray.length - 1); i++)
    					{
    						for(let j = i+1; j < (sortedArray.length); j++)
    						{
    							if(sortedArray[i]['DutyName'].toLowerCase() > sortedArray[j]['DutyName'].toLowerCase())
    							{
    								let temp = sortedArray[i];
    								sortedArray[i] = sortedArray[j];
    								sortedArray[j] = temp;
    							}
    						}
    					}
    					sortedArr = sortedArray.concat(dataArr1);
    				}else
    				{
    					switch(currentSortDate)
    					{
    						case 'NAME' 	: 	for(let i =0; i < (dataArr1.length - 1); i++)
    											{
    												for(let j = i+1; j < (dataArr1.length); j++)
    												{
    													if(dataArr1[i]['DisplayLastName'].toLowerCase()+' '+dataArr1[i]['DisplayFirstName'].toLowerCase() > dataArr1[j]['DisplayLastName'].toLowerCase()+' '+dataArr1[j]['DisplayFirstName'].toLowerCase())
    													{
    														let temp = dataArr1[i];
    														dataArr1[i] = dataArr1[j];
    														dataArr1[j] = temp;
    													}
    												}
    											}
    									break;
    						case 'SORTCODE' : 	for(let i = 0; i < (dataArr1.length - 1); i++)
    											{
                                                    dataArr1[i]['SortCode'] = (dataArr1[i]['SortCode'] == null) ? '' : dataArr1[i]['SortCode'];
    												for(let j = i+1; j < (dataArr1.length); j++)
    												{
                                                        dataArr1[j]['SortCode'] = (dataArr1[j]['SortCode'] == null) ? '' : dataArr1[j]['SortCode'];
    													if(dataArr1[i]['SortCode'].toString()+' '+dataArr1[i]['DisplayLastName'].toLowerCase()+' '+dataArr1[i]['DisplayFirstName'].toLowerCase() > dataArr1[j]['SortCode'].toString()+' '+dataArr1[j]['DisplayLastName'].toLowerCase()+' '+dataArr1[j]['DisplayFirstName'].toLowerCase())
    													{
    														let temp = dataArr1[i];
    														dataArr1[i] = dataArr1[j];
    														dataArr1[j] = temp;
    													}
    												}
    											}
    									break;
    						case 'HRS' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(parseInt(dataArr1[i]['WeekDuration']) > parseInt(dataArr1[j]['WeekDuration']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'ACC' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(eval(dataArr1[i]['AccPeriod']) > eval(dataArr1[j]['AccPeriod']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
							case 'OT' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(eval(dataArr1[i]['OverTimeHrs']) > eval(dataArr1[j]['OverTimeHrs']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'FSV' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(dataArr1[i]['pay'] > dataArr1[j]['pay'])
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'EDP' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												dataArr1[i]['ManualEDP'] = (dataArr1[i]['ManualEDP'] == null ) ? "" : dataArr1[i]['ManualEDP'];
    												dataArr1[j]['ManualEDP'] = (dataArr1[j]['ManualEDP'] == null) ? "" : dataArr1[j]['ManualEDP'];
    												if((dataArr1[i]['ManualEDP']) > (dataArr1[j]['ManualEDP']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'EFT' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(dataArr1[i]['EFT'] > dataArr1[j]['EFT'])
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'IDAY': 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												dataArr1[i]['AccDays'] = (dataArr1[i]['AccDays'] == null) ? 0 : dataArr1[i]['AccDays'];
    												dataArr1[j]['AccDays'] = (dataArr1[j]['AccDays'] == null) ? 0 : dataArr1[j]['AccDays'];
    												if(parseInt(dataArr1[i]['AccDays']) > parseInt(dataArr1[j]['AccDays']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    					}
    					sortedArr = dataArr1;
    				}
    				loadAllocated(sortedArr, 1);
    				if($.cookie('unallocgridheight') != undefined){
                        $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                        $("#allocatedDuty").height($.cookie('allocgridheight'));
                        $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                        $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                        $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                        $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                        setTimeout(function() {
                            $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                            $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                        }, 1000);
                        if($('#showHideCount').prop('checked') == false){
                            let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                            $("#allocatedDuty").height(allocatedHeight);
                            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                        }
                    }

                    $('.dragAlloc').on('dragstart', function (event) {
                        dragSource = $(this);
                        event.stopImmediatePropagation();
                        startDrag(dragSource);
                    });
                    $('.dropeventscall').on('over', function (event) {
                        event.stopImmediatePropagation();
                        event.preventDefault();
                    });
                    if($('#showHideCount').prop('checked') == true){
                        reloadShiftCountingGrid();
                    }
                    if($('#disableDrag').prop('checked') == true){
                        disableDragDrop();
                    }
                    resizeUnallocatedGrid();
                    $('.scrollHorizontal').scroll(function() {
                        $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                    });
                    $('.scrollVertical').scroll(function() {
                        $('.scrollVertical').scrollTop($(this).scrollTop());
                    });
                    setBlockScrollPositions();
    				$("#loading").hide();
    			}else
    			{//case of decending
    				let dataArr = idQuery.result.mainData;
    				let sortedArray = [];
    				let dataArr1= dataArr;
    				let sortedArr = [];
    				const dayValArr = [0, 1, 2, 3, 4, 5, 6];
    				let weekNo = $('#weekNumber').val();
    				let weekNoArr = weekNo.split('/');
    				weekNo = weekNoArr[1]+weekNoArr[0];
    				if(dayValArr.includes(parseInt(currentSortDate)))
    				{
    					if(parseInt($('#showWeeks').val()) > 1)
    					{
    						let allocSortDt = $('#allocSortDt').val();
    						allocSortDt = allocSortDt[4]+allocSortDt[5]+allocSortDt[6]+allocSortDt[7]+'-'+allocSortDt[2]+allocSortDt[3]+'-'+allocSortDt[0]+allocSortDt[1];
    						dataArr.forEach((number, index, rows) => {
    							if((rows[index]['iDay'] == currentSortDate) && (rows[index]['DutyDate'] == allocSortDt))
    							{
    								sortedArray.push(rows[index]);
    								delete dataArr1[index];
    							}
    						})
    					}else
    					{
    						dataArr.forEach((number, index, rows) => {
    							if(($('#date').val() != '') && (rows[index]['iDay'] == currentSortDate)){
                                    sortedArray.push(rows[index]);
                                    delete dataArr1[index];
                                } else {
                                    if((rows[index]['iDay'] == currentSortDate) && (parseInt(rows[index]['WeekNumber']) == parseInt(weekNo)))
                                    {
                                        sortedArray.push(rows[index]);
                                        delete dataArr1[index];
                                    }
                                }
    						})
    					}
    					for(let i =0; i < (sortedArray.length - 1); i++)
    					{
    						for(let j = i+1; j < (sortedArray.length); j++)
    						{
    							if(sortedArray[i]['DutyName'].toLowerCase() < sortedArray[j]['DutyName'].toLowerCase())
    							{
    								let temp = sortedArray[i];
    								sortedArray[i] = sortedArray[j];
    								sortedArray[j] = temp;
    							}
    						}
    					}
    					sortedArr = sortedArray.concat(dataArr1);
    				}else
    				{
    					switch(currentSortDate)
    					{
    						case 'NAME' 	: 	for(let i =0; i < (dataArr1.length - 1); i++)
    											{
    												for(let j = i+1; j < (dataArr1.length); j++)
    												{
    													if(dataArr1[i]['DisplayLastName'].toLowerCase()+' '+dataArr1[i]['DisplayFirstName'].toLowerCase() < dataArr1[j]['DisplayLastName'].toLowerCase()+' '+dataArr1[j]['DisplayFirstName'].toLowerCase())
    													{
    														let temp = dataArr1[i];
    														dataArr1[i] = dataArr1[j];
    														dataArr1[j] = temp;
    													}
    												}
    											}
    									break;
    						case 'SORTCODE' : 	for(let i = 0; i < (dataArr1.length - 1); i++)
    											{
                                                    dataArr1[i]['SortCode'] = (dataArr1[i]['SortCode'] == null) ? '' : dataArr1[i]['SortCode'];
    												for(let j = i+1; j < (dataArr1.length); j++)
    												{
                                                        dataArr1[j]['SortCode'] = (dataArr1[j]['SortCode'] == null) ? '' : dataArr1[j]['SortCode'];
    													if(dataArr1[i]['SortCode'].toString() < dataArr1[j]['SortCode'].toString())
    													{
    														let temp = dataArr1[i];
    														dataArr1[i] = dataArr1[j];
    														dataArr1[j] = temp;
    													}
    												}
    											}
    									break;
    						case 'HRS' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(parseInt(dataArr1[i]['WeekDuration']) < parseInt(dataArr1[j]['WeekDuration']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'ACC' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(eval(dataArr1[i]['AccPeriod']) < eval(dataArr1[j]['AccPeriod']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
							case 'OT' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(eval(dataArr1[i]['OverTimeHrs']) < eval(dataArr1[j]['OverTimeHrs']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'FSV' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(dataArr1[i]['pay'] < dataArr1[j]['pay'])
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'EDP' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												dataArr1[i]['ManualEDP'] = (dataArr1[i]['ManualEDP'] == null ) ? "" : dataArr1[i]['ManualEDP'];
    												dataArr1[j]['ManualEDP'] = (dataArr1[j]['ManualEDP'] == null) ? "" : dataArr1[j]['ManualEDP'];
    												if(dataArr1[i]['ManualEDP'] < dataArr1[j]['ManualEDP'])
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'EFT' : 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												if(dataArr1[i]['EFT'] < dataArr1[j]['EFT'])
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    						case 'IDAY': 	for(let i =0; i < (dataArr1.length - 1); i++)
    										{
    											for(let j = i+1; j < (dataArr1.length); j++)
    											{
    												dataArr1[i]['AccDays'] = (dataArr1[i]['AccDays'] == null) ? 0 : dataArr1[i]['AccDays'];
    												dataArr1[j]['AccDays'] = (dataArr1[j]['AccDays'] == null) ? 0 : dataArr1[j]['AccDays'];
    												if(parseInt(dataArr1[i]['AccDays']) < parseInt(dataArr1[j]['AccDays']))
    												{
    													let temp = dataArr1[i];
    													dataArr1[i] = dataArr1[j];
    													dataArr1[j] = temp;
    												}
    											}
    										}
    									break;
    					}
    					sortedArr = dataArr1;
    				}
    				sortedArr = sortedArray.concat(dataArr1);
    				loadAllocated(sortedArr, 1);
    				if($.cookie('unallocgridheight') != undefined){
                        $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                        $("#allocatedDuty").height($.cookie('allocgridheight'));
                        $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                        $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                        $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                        $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                        setTimeout(function() {
                            $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                            $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                        }, 1000);
                        if($('#showHideCount').prop('checked') == false){
                            let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                            $("#allocatedDuty").height(allocatedHeight);
                            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                        }
                    }

                    $('.dragAlloc').on('dragstart', function (event) {
                        dragSource = $(this);
                        event.stopImmediatePropagation();
                        startDrag(dragSource);
                    });
                    $('.dropeventscall').on('over', function (event) {
                        event.stopImmediatePropagation();
                        event.preventDefault();
                    });
                    if($('#showHideCount').prop('checked') == true){
                        reloadShiftCountingGrid();
                    }
                    if($('#disableDrag').prop('checked') == true){
                        disableDragDrop();
                    }
                    resizeUnallocatedGrid();
                    $('.scrollHorizontal').scroll(function() {
                        $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                    });
                    $('.scrollVertical').scroll(function() {
                        $('.scrollVertical').scrollTop($(this).scrollTop());
                    });
                    setBlockScrollPositions();
    				$("#loading").hide();
    			}
                $('#weeklyAllocation-2').scrollLeft(scrollLeftPos);
                $('#weeklyAllocation-1').scrollLeft(Math.abs($.cookie("teamPeopleBlockPosLeft")) + 2);
                if($('#showHideCount').prop('checked') == true){
                    $('#showCountRight').scrollLeft(scrollLeftPos);
                }
    		  };
    		  transaction.oncomplete = function () {
    			db.close();
    		  };
            } else {
                $('#loading').hide();
                $('#loadingWeekly').hide();
                customAlert("Please close and reopen your browser");
            }
		};
	}

}
function loadAllocated(dutyDataAlloc, isSorted=0)
{
    $("#loadingWeekly").show();
    if(dutyDataAlloc != ''){
       	if(isSorted == 0)
		{
			dutyDataAlloc = IndexToAssociativeArray (dutyDataAlloc,'Alloc');
		}
	    if(typeof dutyDataAlloc == 'string')
        {
            dutyDataAlloc = JSON.parse(dutyDataAlloc);
        }
    }
    var pData = $('#ajaxLoadParams').val();
    pData = $.parseJSON(pData);
    pData.dateInp = $('#date').val();
    if($('#showWeeks').val() > 1){
        var weekDayNum = 7;
        var totalWeekNumDays = parseInt($('#showWeeks').val())*weekDayNum;
        dateRangeDays = totalWeekNumDays;
        var weekStartDate = new Date(pData.startDate.replace(/-/g, '\/'));
        weekStartDate.setDate(weekStartDate.getDate() + totalWeekNumDays);
        let offsetweekStartDate = weekStartDate.getTimezoneOffset();
        pData.endDate = new Date(weekStartDate.getTime() - (offsetweekStartDate*60*1000));
        pData.endDate = weekStartDate.toISOString().split('T')[0];
    }
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/holidaylist.php",
        data: pData,
        success: function (holidayDataResp) {
            holidayDataResp = $.parseJSON(holidayDataResp);
            if(holidayDataResp.shiftCountingFilterId == 0){
                $('#showHideCount').prop('checked', false);
                let repShiftCountingDiv = '';
                if (holidayDataResp.shiftCountingFiltersList.length > 0) {
                    repShiftCountingDiv += '<option value="NA">No Filter/Edit Filter</option>';
                    $(holidayDataResp.shiftCountingFiltersList).each(function(index, value) {
                        repShiftCountingDiv += '<option value="' + value.ID + '">' + value.FilterName + '</option>';
                    });
                } else {
                    repShiftCountingDiv += '<option value="NA">No Filter/Edit Filter</option>';
                }
                $('#dutyShiftCountingFilterId').html(repShiftCountingDiv).trigger("chosen:updated");
            }
            var dayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var dateRangeDays = 7;
            if($('#showWeeks').val() > 1){
                var weekDayNum = 7;
                var totalWeekNumDays = parseInt($('#showWeeks').val())*weekDayNum;
                dateRangeDays = totalWeekNumDays;
                var weekStartDate = new Date(pData.startDate.replace(/-/g, '\/'));
                weekStartDate.setDate(weekStartDate.getDate() + totalWeekNumDays);
                let offsetweekStartDate = weekStartDate.getTimezoneOffset();
                weekStartDate = new Date(weekStartDate.getTime() - (offsetweekStartDate*60*1000));
                weekStartDate = weekStartDate.toISOString().split('T')[0];

                if($('#date').val() != ''){
                    pData.startDate = $('#date').val();
                    let wEndDate = new Date(pData.startDate.replace(/-/g, '\/'));
                    wEndDate.setDate(wEndDate.getDate() + (parseInt(weekDayNum)+1));
                    let offsetwEndDate = wEndDate.getTimezoneOffset();
                    wEndDate = new Date(wEndDate.getTime() - (offsetwEndDate*60*1000));
                    pData.endDate = wEndDate.toISOString().split('T')[0];
                }
            } else {
                let wEndDate = new Date(pData.endDate.replace(/-/g, '\/'));
                wEndDate.setDate(wEndDate.getDate() - 1);
                let offsetwEndDate = wEndDate.getTimezoneOffset();
                wEndDate = new Date(wEndDate.getTime() - (offsetwEndDate*60*1000));
                pData.endDate = wEndDate.toISOString().split('T')[0];
            }

            let allocProcessData = {};
            let dutyDataAlloc1 = [];
            if(dutyDataAlloc != ''){

                dutyDataAlloc.forEach((number, index, rows) => {
                    let SchedulingPersonID = rows[index].SchedulingPersonID + '-1';
                    let dutyDate = rows[index].DutyDate;
                    if(allocProcessData[SchedulingPersonID] == undefined){
                        allocProcessData[SchedulingPersonID] = {};
                        allocProcessData[SchedulingPersonID][dutyDate] = {};
                    }
                    if(allocProcessData[SchedulingPersonID][dutyDate] == undefined){
                        allocProcessData[SchedulingPersonID][dutyDate] = {};
                    }
                    allocProcessData[SchedulingPersonID][dutyDate] = rows[index];
                    rows[index]['ManualEDP'] = (rows[index]['ManualEDP'] == null) ? "-1" : rows[index]['ManualEDP'];
                    dutyDataAlloc1.push(rows[index]);
                });

                if(isSorted == 0)
                {
                    saveDataInIndexedDb(dutyDataAlloc1);

                }
            }

			let showWeeks   =   $('#showWeeks').val();
			if ( ($('#showWeeks').val() == 'undefined') || ($('#showWeeks').val() == undefined)){
				 showWeeks   = 1;
			}

            let allocPersonHtml =   '<div class="block-25 grid-item"><div id="weeklyAllocation-1" class="scrollVertical" onscroll="divScrollL();" onmouseover="removetip();"><div id="teamPeoplesBlockPos" name="teamPeoplesBlockPos"></div><div id="weeklyAllocation1" class="weeklyAllocationTable weekly-table" style="width:100%"><div class="allocated-name-filter-container">' +
            '<div id="quick-filter-name-container"><input autocomplete="off" maxlength="250" class="ui-autocomplete-input" type="text" placeholder="Name filter" id="edit-weekly-name-quick-filter" name="dispName" value="" style="width: 93%;"><em class="fa fa-search" id="edit-weekly-name-quick-filter-search"></em>' +
            '</div></div><div class="weekly-table-header allocLeftTableHead"><div class="filter weekly-table-row">';
            if(($('#allocSortType').val() == 0))
            {
                sortBgImgStr = 'background-image: url(../images/sort_asc_editWeekly.png);';
            }else if(($('#allocSortType').val() == 1))
            {
                sortBgImgStr = 'background-image: url(../images/sort_desc_editWeekly.png);';
            }

            let bothImgStr = 'background-image: url(../images/sort_both_editWeekly.png);';
            let sortBgImgNameStr, sortBgImgCodeStr, sortBgImgHrsStr, sortBgImgAccStr, sortBgImgOtStr, sortBgImgFsvStr, sortBgImgEdpStr, sortBgImgEftStr, sortBgImgIdayStr;
            let allocSorVal = $('#allocSortDate').val();
            switch(allocSorVal)
            {
                case 'NAME'     : sortBgImgNameStr = sortBgImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = bothImgStr; break;
                case 'SORTCODE' : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = sortBgImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = bothImgStr; break;
                case 'HRS'      : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = sortBgImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = bothImgStr; break;
                case 'ACC'      : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = sortBgImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = bothImgStr; break;
				case 'OT'      : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = sortBgImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = bothImgStr; break;
                case 'FSV'      : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = sortBgImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = bothImgStr; break;
                case 'EDP'      : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = sortBgImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = bothImgStr; break;
                case 'EFT'      : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = sortBgImgStr; sortBgImgIdayStr = bothImgStr; break;
                case 'IDAY'     : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = sortBgImgStr; break;
                default         : sortBgImgNameStr = bothImgStr; sortBgImgCodeStr = bothImgStr; sortBgImgHrsStr = bothImgStr; sortBgImgAccStr = bothImgStr; sortBgImgOtStr = bothImgStr; sortBgImgFsvStr = bothImgStr; sortBgImgEdpStr = bothImgStr; sortBgImgEftStr = bothImgStr; sortBgImgIdayStr = bothImgStr; break;
            }
            let topPosHol = '8px';
            let nameHead = 'Name';
            if(holidayDataResp.holidayData.length > 0){
                topPosHol = '15px';
                nameHead = '&nbsp;<br>Name<br>&nbsp;';
            }
            if(showWeeks > 1) {
                allocPersonHtml +=  '<div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="DisplayLastName" onClick="getAllocatedSort(\'NAME\',1);" style="cursor:pointer;">'+nameHead+'<div style="'+sortBgImgNameStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="SortCode" onClick="getAllocatedSort(\'SORTCODE\');" style="cursor:pointer;">Code<div style="'+sortBgImgCodeStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div>';
            } else {
                allocPersonHtml +=  '<div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="DisplayLastName" onClick="getAllocatedSort(\'NAME\',1);" style="cursor:pointer;">'+nameHead+'<div style="'+sortBgImgNameStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="SortCode" onClick="getAllocatedSort(\'SORTCODE\');" style="cursor:pointer;">Code<div style="'+sortBgImgCodeStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="ContractedHours" onClick="getAllocatedSort(\'HRS\');" style="cursor:pointer;">Hrs<div style="'+sortBgImgHrsStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="ACC" onClick="getAllocatedSort(\'ACC\');" style="cursor:pointer; padding-right:14px;">ACC<div style="'+sortBgImgAccStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px; left:32px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="OT" onClick="getAllocatedSort(\'OT\');" style="cursor:pointer; padding-right:14px;">OT<div style="'+sortBgImgOtStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px; left:32px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="pay" onClick="getAllocatedSort(\'FSV\');" style="cursor:pointer; padding-right:14px;">FSV<div style="'+sortBgImgFsvStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px; left:29px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="ManualEDP" onClick="getAllocatedSort(\'EDP\');" style="cursor:pointer;">EDP<div style="'+sortBgImgEdpStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px; left:32px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="AccDays" onClick="getAllocatedSort(\'EFT\');" style="cursor:pointer;">EFT<div style="'+sortBgImgEftStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px; left:27px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div><div class="filterCol weekly-header-cell" data-sort="SORT_ASC" data-id="DisplayLastName" onClick="getAllocatedSort(\'IDAY\');" style="cursor:pointer;">Days<div style="'+sortBgImgIdayStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px; left:35px;" id="sort_img_'+allocSorVal+'" style="cursor:pointer;" ></div></div>';
            }
            allocPersonHtml +=  '</div></div><div class="weekly-table-body allocLeftTableData">';
            if(allocProcessData != '') {
                for([key, value] of Object.entries(allocProcessData)) {
                    key = key.substring(0, key.length - 2);
                    let keyName = Object.keys(value)[0];
                    let userName = value[keyName]['DisplayName'];
                    let eft = (value[keyName]['EFT'] == "1.000") ? 1 : (value[keyName]['EFT'] == null) ? '' : '0'+value[keyName]['EFT'];
                    let accdays = (value[keyName]['AccDays'] != null) ? value[keyName]['AccDays'] : 0;
                    let edp = "";
					let otVar = parseFloat(value[keyName]['OverTimeHrs'] / 3600).toFixed(2);
                    if(value[keyName]['ManualEDP'] == "0"){
                        edp = "C";
                    } else if(value[keyName]['ManualEDP'] == "1"){
                        edp = "M";
                    } else {
                        edp = "";
                    }

                    let bgClass = (value[keyName]['IsHomeTeam'] == 1) ? 'tdGrayBG' : 'tdGrayBGAdditionalPerson';
                    let cellStyle = (value[keyName]['PersonBackgroundColour']) ? 'background-color:'+value[keyName]['PersonBackgroundColour']+' !important;':'';
                    cellStyle += (value[keyName]['PersonFontColour']) ? 'color:'+value[keyName]['PersonFontColour']+' !important;':'';
                    cellStyle +=  'white-space:nowrap';
                    let staffNumberStr = (value[keyName]['StaffNumber']) ? '('+value[keyName]['StaffNumber']+')' : '';
                    let displayLastName =   (value[keyName]['DisplayLastName'].length > 24) ? value[keyName]['DisplayLastName'].substring(0, 12)+'<br>'+value[keyName]['DisplayLastName'].substring(12, 22)+'..' : (value[keyName]['DisplayLastName'].length > 12) ?
                    value[keyName]['DisplayLastName'].substring(0, 12)+'<br>'+value[keyName]['DisplayLastName'].substring(12, 24) : value[keyName]['DisplayLastName'];
                    let displayFirstName =  (value[keyName]['DisplayFirstName'].length > 12) ? value[keyName]['DisplayFirstName'].substring(0, 10) + '..' : value[keyName]['DisplayFirstName'];
                    allocPersonHtml += '<div class="weekly-table-row schRowLeft'+key+'">';
                    let sortCodeStr = getSortCodes(value)[0];
                    if(sortCodeStr != undefined && sortCodeStr != ''){
                        value[keyName]['SortCode'] = (sortCodeStr != '') ? sortCodeStr : '';
                    } else {
                        value[keyName]['SortCode'] = '';
                    }

                    if(value[keyName]['SortCode'].length > 16)
                    {
                        sortCodeStr =   value[keyName]['SortCode'].substring(0, 8) + '<br>' + (value[keyName]['SortCode'].substring(8, 16)) + '<br>' + value[keyName]['SortCode'].substring(16, (value[keyName]['SortCode'].length));
                    }else if(value[keyName]['SortCode'].length > 8)
                    {
                        sortCodeStr =   value[keyName]['SortCode'].substring(0, 8) + '<br>' + value[keyName]['SortCode'].substring(8, value[keyName]['SortCode'].length);
                    }else
                    {
                        sortCodeStr = value[keyName]['SortCode'];
                    }
                    value[keyName]['ACC'] = value[keyName]['ACC'] ? value[keyName]['ACC'] : '';
                    let costCode = value[keyName]['CostCode'] ? value[keyName]['CostCode'] : '';
                    let accTitle = '';
                    if((value[keyName]['AccPeriod'] != '') && (value[keyName]['AccPeriod'] != null) && (value[keyName]['AccPeriod'] != 'null')){
                        accTitle = value[keyName]['ACC'];
                    }
                    let displayName = '';
                    if(displayFirstName.trim() == '<br>')
                    {
                        displayName = displayLastName;
                    }else if(displayLastName.trim() == '<br>')
                    {
                        displayName = displayFirstName;
                    }else
                    {
                        displayName = displayLastName + ',<br>' + displayFirstName;
                    }
                    if(showWeeks > 1)
                    {
                        allocPersonHtml += '<div class="' + bgClass + ' weekly-body-cell person-data-cell peronname_'+key+'" data-id='+key+' data-cost-code="' + costCode + '" data-title="'+userName+' '+ staffNumberStr +'" data-order="'+ userName +'" onClick="highlightRowScheduledPerson('+key+')" style="' + cellStyle +'">';
                        allocPersonHtml += '<span class="person-detail-tooltip">' + displayName + '</span>';
						if (value[keyName]['FWANotesFlag']==1)
						{
							allocPersonHtml += '<div id="'+value[keyName]['SchedulingPersonID']+'_triangle" style="float:right;cursor:pointer;color:grey;top: 25px;right: 0px;position: absolute;font-size: 15px;" onmouseover="getSchedulingNotes('+value[keyName]['SchedulingPersonID']+');" onmouseleave="removetip();" title >&#9698;</div>';
						}
						allocPersonHtml += '</div><div class="'+bgClass+' weekly-body-cell centerText right-pad-4 js_schdsortcode_'+key+'" title="'+value[keyName]['SortCode']+'" data-schedulingpersonid ="'+key+'" style="white-space: nowrap;">'+sortCodeStr+'</div>';
                    } else {
                        allocPersonHtml += '<div class="' + bgClass + ' weekly-body-cell person-data-cell peronname_'+key+'" data-id='+key+' data-cost-code="' + costCode + '" data-title="'+userName+' '+ staffNumberStr +'" data-order="'+ userName +'" onClick="highlightRowScheduledPerson('+key+')" style="' + cellStyle +'">';
                        allocPersonHtml += '<span class="person-detail-tooltip">' + displayName + '</span>';
						value[keyName]['ContractedHours'] = value[keyName]['ContractedHours'] ? value[keyName]['ContractedHours'] : '00:00';
                        let contractedHoursSeconds = value[keyName]['ContractedHours'].replace(":", ".") * 3600;
                        value[keyName]['WeekDuration'] = parseFloat(value[keyName]['WeekDuration']);
                        let weekDurationVal = value[keyName]['WeekDuration'] ? parseFloat(value[keyName]['WeekDuration'] / 3600).toFixed(2) : '00.00';
						value[keyName]['TotalPlannedDuration'] = parseFloat(value[keyName]['TotalPlannedDuration']);
                        let totalPlannedDuration = value[keyName]['TotalPlannedDuration'] ? parseFloat(value[keyName]['TotalPlannedDuration'] / 3600).toFixed(2) : '00.00';
                        if (value[keyName]['FWANotesFlag']==1)
						{
							allocPersonHtml += '<div id="'+value[keyName]['SchedulingPersonID']+'_triangle" style="float:right;cursor:pointer;color:grey;top: 25px;right: 0px;position: absolute;font-size: 15px;" onmouseover="getSchedulingNotes('+value[keyName]['SchedulingPersonID']+');" onmouseleave="removetip();" title >&#9698;</div>';
						}
						allocPersonHtml += '</div><div class="'+bgClass+' weekly-body-cell centerText js_schdsortcode_'+value[keyName]['SchedulingPersonID']+' right-pad-4"  id="js_schdsortcode" title="'+value[keyName]['SortCode']+'" data-schedulingpersonid ="'+key+'" data-id='+value[keyName]['ID']+'  style="white-space: nowrap;">'+sortCodeStr+'</div>';
                        let actualHours = value[keyName]['WeekDuration'] ?? 0;
			            let hrsTextColor = 'black';
                        let actualHoursMinusRotaSeconds = (actualHours ?? 0) - (value[keyName]['TotalPlannedDuration'] ?? 0);
                        let actualHoursMinusRotaHours = parseFloat(actualHoursMinusRotaSeconds / 3600).toFixed(2);
                        let overtimeSeconds = value[keyName]['OverTimeHrs'] ? parseFloat(value[keyName]['OverTimeHrs']) : 0;
                        let manualEDP = value[keyName]['ManualEDP'];
                        if(manualEDP == 1) {
                            if(overtimeSeconds == 0 && actualHoursMinusRotaSeconds < 0) {
                                otVar = '';
                                hrsTextColor = 'red';
                            }
                            if(actualHours < totalPlannedDuration) {
                                hrsTextColor = 'red';
                            }
                        } else {
                            if(actualHours < contractedHoursSeconds) {
                                hrsTextColor = 'red';
                            }
                        }
                        allocPersonHtml += '<div class="' + bgClass + ' weekly-body-cell centerText right-pad-4 hrs_'+key+'" id="hrs_'+key+'" data-contracted-hours="' + contractedHoursSeconds + '" title="Contracted Hours: '+value[keyName]['ContractedHours']+' &#013;Rota Pattern: ' + totalPlannedDuration + ' &#013;Actual Hours minus Planned Rota Pattern Hours = ' + actualHoursMinusRotaHours + '" style="color:' + hrsTextColor + ';">'+weekDurationVal+'</div>';
                        if(value[keyName]['AccPeriod'] != '' && value[keyName]['AccPeriod'] != null){
                            allocPersonHtml += '<div class="'+bgClass+' weekly-body-cell centerText right-pad-14 acc_'+key+'" title="'+accTitle+'">'+value[keyName]['AccPeriod']+'</div>';
                        } else {
                            allocPersonHtml += '<div class="'+bgClass+' weekly-body-cell centerText right-pad-14 acc_'+key+'" title="'+accTitle+'"></div>';
                        }
                        let otTextColor = 'black';
                        if(manualEDP == 1) {
                            if(actualHoursMinusRotaSeconds != overtimeSeconds)
                            {
                                otTextColor = 'red';
                            }
                            if(overtimeSeconds == 0 && actualHoursMinusRotaSeconds == 0)
                            {
                                otTextColor = 'black';
                                otVar = '';
                            }
                            if(overtimeSeconds < 0) {
                                otVar = '0.00';
                            }
                            if(overtimeSeconds == 0 && actualHoursMinusRotaSeconds < 0) {
                                otVar = '';
                            }
                        } else {
                            if(parseInt(overtimeSeconds) < 0)
                            {
                                otTextColor = 'red';
                            }
                            if(overtimeSeconds == 0)
                            {
                                otVar = '';
                            }
                        }
                        allocPersonHtml += '<div id="markOt_'+key+'" data-manual-edp="' + value[keyName]['ManualEDP'] + '" data-actual-seconds="' + (value[keyName]['WeekDuration'] ?? 0) + '" data-ot="' + (value[keyName]['OverTimeHrs'] ?? 0) + '" data-total-planned-hours="' + (value[keyName]['TotalPlannedDuration'] ?? 0) + '" class="'+bgClass+' weekly-body-cell centerText markOt_'+key+'" style="color:'+otTextColor+'; text-align:right;padding-right: 3px">'+otVar+'</div><div class="'+bgClass+' weekly-body-cell centerText right-pad-14 fsv_'+key+'">'+value[keyName]['pay']+'</div><div class="'+bgClass+' weekly-body-cell centerText right-pad-32 edp_'+key+'"> '+edp+'</div><div class="'+bgClass+' weekly-body-cell centerText right-pad-27 eft_'+key+'">'+eft+'</div><div class="'+bgClass+' weekly-body-cell centerText right-pad-26 days_'+key+'" id="days_'+key+'">'+accdays+'</div>';
                    }
                    allocPersonHtml += '</div>';
                }
            } else {
                allocPersonHtml +=  '<div class="noRecordFound">No Record Found</div>';
            }
            allocPersonHtml +=  '</div>';
            allocPersonHtml +=  '</div></div></div>';
            //End of Left grid

            let endDate = new Date(pData.startDate.replace(/-/g, '\/'));
			endDate.setDate(endDate.getDate() + ((7 * showWeeks)-1));
			let offsetendDate = endDate.getTimezoneOffset();
            endDate = new Date(endDate.getTime() - (offsetendDate*60*1000));

            endDate = endDate.toISOString().split('T')[0];
            let period = getDatesInRange(pData.startDate.replace(/-/g, '\/'), endDate.replace(/-/g, '\/'));
            allocPersonHtml +=  '<div class="block-75 grid-item"><div id="weeklyAllocation-2" class="scrollHorizontal scrollVertical allocationSingleWeek" onscroll="allocatePositionleft()"><div id="weeklyAllocation2" data-published="$firstAllocation[isPublished]" class="weeklyAllocationTable weekly-table scrollHorizontal" style="width:100%;"><div class="weekly-table-header"><div class="weekly-table-row allocatedTableHead">';
            period.forEach((number, index, rows) => {
                let dateHeader  =   new Date(rows[index].replace(/-/g, '\/'));
                let sortBgImgStr = '';
                let cdateVal    =   dateHeader.toISOString().split('T')[0];
                let dayVal = '';
                switch(dayName[dateHeader.getDay()].toLowerCase())
                {
                    case 'saturday' : dayVal = 0;
                                    break;
                    case 'sunday'   : dayVal = 1;
                                    break;
                    case 'monday'   : dayVal = 2;
                                    break;
                    case 'tuesday'  : dayVal = 3;
                                    break;
                    case 'wednesday': dayVal = 4;
                                    break;
                    case 'thursday' : dayVal = 5;
                                    break;
                    case 'friday'   : dayVal = 6;
                                    break;

                }
                if(showWeeks > 1)
                {
                    let dt = ("0" + dateHeader.getDate()).slice(-2)+("0"+(dateHeader.getMonth()+1)).slice(-2)+dateHeader.getFullYear();
                    if(($('#allocSortType').val() == 0) && ($('#allocSortDate').val() == dayVal) && (dt == $('#allocSortDt').val()))
                    {
                        sortBgImgStr = 'background-image: url(../images/sort_asc_editWeekly.png);';
                    }else if(($('#allocSortType').val() == 1) && ($('#allocSortDate').val() == dayVal) && (dt == $('#allocSortDt').val()))
                    {
                        sortBgImgStr = 'background-image: url(../images/sort_desc_editWeekly.png);';
                    } else {
                        sortBgImgStr = 'background-image: url(../images/sort_both_editWeekly.png);';
                    }
                }else
                {
                    if(($('#allocSortType').val() == 0) && ($('#allocSortDate').val() == dayVal))
                    {
                        sortBgImgStr = 'background-image: url(../images/sort_asc_editWeekly.png);';
                    }else if(($('#allocSortType').val() == 1) && ($('#allocSortDate').val() == dayVal))
                    {
                        sortBgImgStr = 'background-image: url(../images/sort_desc_editWeekly.png);';
                    } else {
                        sortBgImgStr = 'background-image: url(../images/sort_both_editWeekly.png);';
                    }
                }
                let bankHolidayName = '';
                let titleBankHoliday = '';
                if(holidayDataResp.holidayData.length > 0){
                    let holidayRespData = holidayDataResp.holidayData;
                    holidayRespData.forEach((number, ind, rws) => {
                        if(rws[ind].dDateTime == dateHeader.getFullYear()+'-'+("0"+(dateHeader.getMonth()+1)).slice(-2)+'-'+("0" + dateHeader.getDate()).slice(-2)){
                            if(showWeeks > 1) {
                                titleBankHoliday = rws[ind].sEvent;
                                bankHolidayName = '('+rws[ind].sEvent+')';
                                if(rws[ind].sEvent.length > 13){
                                    bankHolidayName = '('+rws[ind].sEvent.slice(0,12)+'...)';
                                }
                            } else {
                                titleBankHoliday = rws[ind].sEvent;
                                bankHolidayName = '('+rws[ind].sEvent+')';
                                if(rws[ind].sEvent.length > 20){
                                    bankHolidayName = '('+rws[ind].sEvent.slice(0,18)+'...)';
                                }
                            }

                        }
                    });

                }
                let topPosDateComment = holidayDataResp.holidayData.length == 0 ? '2px' : '9px';
                let dateString = dateHeader.getFullYear()+'-'+("0"+(dateHeader.getMonth()+1)).slice(-2)+'-'+("0" + dateHeader.getDate()).slice(-2);
                allocPersonHtml +=  '<div class="min-w120 weekly-header-cell date-comment" data-date-comment-date="' + dateString + '" data-date-comment-team-id="' + $('#searchTeamId').val() + '" data-date="'+dayVal+'"> ';
                allocPersonHtml +=  '<span>'+dayName[dateHeader.getDay()]+'</span><br>';
                allocPersonHtml +='<span style="cursor:pointer;" onclick="openViewDaily(\''+ dateString +'\','+$('#searchTeamId').val()+');">'+("0" + dateHeader.getDate()).slice(-2)+'/'+("0"+(dateHeader.getMonth()+1)).slice(-2)+'/'+dateHeader.getFullYear()+'</span><span class="date-commment-info-icon" style="float: right;cursor: pointer;position: absolute; padding: 5px; top:' + topPosDateComment + '; right: 18px;"></span><br>';
                allocPersonHtml +='<span style="cursor:pointer;" title="'+titleBankHoliday+'">'+bankHolidayName+'</span>';
                allocPersonHtml +='<div onClick="getAllocatedSort('+dayVal+');allocSortDt.value='+"'"+("0" + dateHeader.getDate()).slice(-2)+("0"+(dateHeader.getMonth()+1)).slice(-2)+dateHeader.getFullYear()+"'"+';" style="'+sortBgImgStr+'background-repeat: no-repeat; position:absolute; top:'+topPosHol+'; right: 2px; height: 40px; width: 16px; cursor:pointer;" id="sort_img_'+cdateVal+'">';
                allocPersonHtml +='</div>';
                allocPersonHtml +='</div>';
            });
            allocPersonHtml +=  '</div></div>';
            allocPersonHtml +=  '<div class="weekly-table-body allocatedtablebody">';
            if(allocProcessData != '')
            {
                let rowCnt = 0;
                for([key, value] of Object.entries(allocProcessData))
                {
                    key = key.substring(0, key.length - 2);
                    let keyName = Object.keys(value)[0];
                    let userName = value[keyName]['DisplayName'];
                    let eft = value[keyName]['EFT'];
                    let edp = "";
                    if(value[keyName]['ManualEDP'] == 0){
                        edp = "C";
                    } else if(value[keyName]['ManualEDP'] == 1){
                        edp = "M";
                    } else {
                        edp = "";
                    }
                    let isOccupied = 1;
                    let countLock = 0;
                    let data = value[keyName];
                    allocPersonHtml +=  '<div id="mainUnqId_'+rowCnt+'" class="weekly-table-row allocRow schRowRight'+key+'" data-scheduling-person="'+key+'">';

                    period.forEach((number, index, rows) => {
                        let date = new Date(rows[index].replace(/-/g, '\/'));
                        date = date.getFullYear()+'-'+("0"+(date.getMonth()+1)).slice(-2)+'-'+("0" + date.getDate()).slice(-2);
                        if(typeof value[rows[index]] !== 'undefined') {
                            let imageNameSignInClsName = '';
                            data = value[rows[index]];
                            switch(data['ImageNameSignin']){
                                case "1":
                                    imageNameSignInClsName = 'signin-red-cross';
                                    break;
                                case "2":
                                    imageNameSignInClsName = 'signin-green-tick';
                                    break;
                                case "3":
                                    imageNameSignInClsName = 'signin-blue-tick';
                                    break;
                                case "4":
                                    imageNameSignInClsName = 'msg-box-warning';
                                    break;
                            }
                            //For Images Ends
                            let curDate = new Date();
                            let teamId = $('#adhocFilter').val();
							let uIdStr = data['AllocationsSPID'] ? data['AllocationsSPID']+'_'+data['DutyDate'] : data['SchedulingPersonID']+'_'+data['DutyDate'];
                            let js = '';
                            let allowsignin = 0;
                            curDate = curDate.getFullYear()+'-'+("0"+(curDate.getMonth()+1)).slice(-2)+'-'+("0" + curDate.getDate()).slice(-2);
                            if (rows[index] == curDate) {
                                js = ' onclick=\'javascript:SignInToDay("'+data['DutyName']+'",'+data['ActionNameForSignin']+', "'+curDate+'", "'+data['StartTime']+'", "'+data['EndTime']+'", "'+uIdStr+'", "'+data['signin']+'")\';';

                            } else {
                                js = ' onclick=\'javascript:SignInDay("'+data['DutyName']+'",'+data['ActionNameForSignin']+', "'+date+'", "'+data['StartTime']+'","'+data['EndTime']+'", "'+uIdStr+'", "'+data['signin']+'")\';';
                            }
                            let TodayDate = new Date(curDate.replace(/-/g, '\/'));
                            let curDate2 = new Date();
                            let dataSignInDays = parseInt(data['Signindays']);
                            let TotalSignInDays = new Date(curDate2.setDate(curDate2.getDate() + (dataSignInDays - 1)));
                            let StartTimeSignIn = new Date(data['DutyDate'].replace(/-/g, '\/'));
                            let sTime = data['StartTime'] == null ? 0 : data['StartTime'];
                            let eTime = data['EndTime'] == null ? 0 : data['EndTime'];

                            if ((dataSignInDays > 0)  && (StartTimeSignIn >=  TodayDate) && (StartTimeSignIn <= TotalSignInDays) && ((parseInt(sTime) != 0 || parseInt(eTime) != 0))) {
                                allowsignin = 1;
                            }

                            let cellbgcolour = '';
                            let cellBgColor = data['ColourBackground'];
                            let attentionClsName = 0;
							data['DutyName'] = data['DutyName'] ? data['DutyName'] : '';
                            let DutyNameUpTrim = data['DutyName'].toUpperCase().trim();
					        // Set default mustard yellow colour for Sick and U-Sick
                            if ((DutyNameUpTrim == "SICK") || (DutyNameUpTrim == "U-SICK") || (DutyNameUpTrim == "-SICK")) {
                               cellbgcolour = 'style="background-color:#'+data['ColourBackground']+'; color:#'+data['ColourFont']+';"';
                                cellBgColor = data['ColourBackground'];
                            } else if ((DutyNameUpTrim == "LEAVE") || (DutyNameUpTrim == "OFF LEAVE")) {
                                // Set default green colour for Sick and U-Sick
                                cellbgcolour = 'style="background-color:#'+data['ColourBackground']+'; color:#'+data['ColourFont']+';"';
                                cellBgColor = data['ColourBackground'];
                            } else if (((data['ColourBackground'] != '') && (data['isAttentionClsName'] == 0) && (sTime == 0 && eTime == 0)) || ((data['isAttentionClsName'] == 0) && (data['MarkedSickness'] == 1)) || (data['DutyName'].toLowerCase() == 'absent'))
                            {
                                cellbgcolour = 'style="background-color:#'+data['ColourBackground']+'; color:#'+data['ColourFont']+';"';
                                cellBgColor = data['ColourBackground'];
                            }
                            let contextClassName = '';
                            switch(data['contextMenuClsName']){
                                case "0":
                                    contextClassName = 'context-menu-additional';
                                    break;
                                case "1":
                                    contextClassName = 'context-menu-leave';
                                    break;
                                case "2":
                                    contextClassName = 'context-menu';
                            }
                            let markWiadClass = '';
                            switch(data['WTDBreachClassName']){
                                case "1":
                                    markWiadClass = 'cross-red';
                                    break;
                                case "2":
                                    markWiadClass = 'cross-blue';
                                    break;
                                default:
                                    markWiadClass = '';
                            }
                            let otherTeamClass = '';
                            if( ( data['IsDutyFromOtherTeam'] == 1) && (DutyNameUpTrim != "LEAVE") && (DutyNameUpTrim != "OFF LEAVE") && (DutyNameUpTrim != "U")) {
								 otherTeamClass = 'otherTeamClass';
								 data['isRequest'] = 0;
                            } else {
                                otherTeamClass = '';
                            }

                            if(data['isAttentionClsName'] == 1){
                                attentionClsName = 'attentionClass';
                            } else if (data['isAttentionClsName'] == 2) {
                                attentionClsName = 'attentionCopyDutyClass';
                            }
                            data['ManualEDP'] = data['ManualEDP'] ? data['ManualEDP'] : 0;
                            let dateRangeDays = 7 * parseInt($("#showWeeks").val());
                            let markWiadClassFlag = (((markWiadClass != '') && ((markWiadClass == 'cross-red') || (markWiadClass == 'cross-blue')))) ? true : false;
                            let modalTitle = '';
                            if(data['EditDuty'] == 1)
                            {
                                if(data['DutyName'] == 'U')
                                {
                                    modalTitle = 'createduty';
                                } else {
                                    modalTitle = 'editduty';
                                }
                            }
                            data['TriangleColour'] = ((data['TriangleColour'] == null) || (data['TriangleColour'] == '') || (data['TriangleColour'] == 'NULL')) ? data['TriangleColour'] : 'cornerIcon cornerIcon-'+data['TriangleColour'];
                            let dataChargingPresent = (((data['TriangleColour'] == null) || (data['TriangleColour'] == '')) ? 0 : 1);
							if(data['DutyName'].toLowerCase() == 'sick')
							{
								dataChargingPresent = 0;
								data['TriangleColour'] = '';
							}
                            let dragAllocClass = '';
                            if (((data['IsHomeTeam']) && ((data['DutyName'].trim()!="U") && (data['DutyName']!="") && (data['pdlStartTime']==0) && (data['pdlEndTime']==0))) || (((data['IsHomeTeam']=='')) && ((data['DutyName'].trim()!="U") && (data['DutyName']!="")) && (data['MarkWiad'] || data['MarkActual']) && ((data['pdlStartTime']==0) && (data['pdlEndTime']==0))))
                            {
                                dragAllocClass = 'dragAlloc';
                            }
                            let dropeventscall = '';
                            if(data['EditDuty'] == 1)
                            {
                                dropeventscall = 'dropeventscall-un';
                            }
                            let purpleTriangleIcon = (data['MarkOverTwelve'] == "-1") ? 'purpleTriangleIcon' : '';
                            let doesntNeedCoveringIcon = data['IsNeedCovering'] == 0 ? 'doesntNeedCoveringIcon' : '';
                            let attr1='false';
                            if ((data['EditDuty'] == 1) && (data['DutyName'] != 'U'))
                            {
                                    attr1="true";
                            }
                            let onDropCond = '';
                            if((data['EditDuty'] == 1))
                            {
                                onDropCond='ondrop="dropAlloc(event,this);"';
                            }
                            let absentCond = '';
                            if((data['DutyName'].toLowerCase().trim() == 'off leave') || (data['DutyName'].toLowerCase() == 'leave') || (data['DutyName'].toLowerCase() == 'absent') || (data['DutyName'].toLowerCase() == 'sick') || (data['DutyName'].toLowerCase() == 'u-sick') || (data['DutyName'].toLowerCase() == '-sick') && ($('#editScreen').val() == 1))
                            {
                                onDropCond = 'ondrop="dropSickLeaveAbsent(event,this);"';
                                attr1 = "false";
                            }
                            if(((data['pdlStartTime']!=0) || (data['pdlEndTime']!=0)) && ($('#editScreen').val() == 1)){
                                onDropCond = 'ondrop="dropPDL(event,this);"';
                                attr1 = "false";
                            }
                            data['isAttention'] = data['isAttention'] ? data['isAttention'] : 0;
                            data['StartTime'] = data['StartTime'] ? data['StartTime'] : 0;
                            data['EndTime'] = data['EndTime'] ? data['EndTime'] : 0;
                            let dataWiadOpt = false;
                            if((markWiadClass != '') && (markWiadClass == 'cross-red')){
                                dataWiadOpt = true;
                             }
                             let requestClassStr = '';
                             if(data['isRequest'] == 0  || data['isRequest'] == '')
                             {
                                 requestClassStr = '';
                             }else
                             {
                                 requestClassStr = 'requestClass';
                             }
                             let triangleIconSpan = (data['MarkOverTwelve'] == "-1") ? 'triangleIconSpan' : '';
                             let markWiadClassStr = '';
                             if(markWiadClass != '')
                             {
                                 markWiadClassStr = 'onmouseover="showwtdtip('+"'showtooltip'"+',this, '+data['AllocationsDutyID']+','+dataWiadOpt+','+data['pdlStartTime']+','+data['pdlEndTime']+');" onmouseleave="showwtdtip('+"'hidetooltip'"+');"';
                             }
                             let markWiadClassStr1 = '';
                             let duration = (data['Duration'] - data['dutyBreakTime']) / 3600 ;
                             if(cellbgcolour != ''){
                                duration = (data['Duration']) / 3600 ;
                             }
                             duration = duration.toFixed(2);
                             if(markWiadClass == '')
                             {
                                 markWiadClassStr1 = 'title="'+data['DutyName']+' ('+duration+')'+' - '+data['schedulingTeamName']+'" onmouseover="removetip();"';
                             }
                             let dutyName = 'U';
                            if (data['DutyName'] != '' && data['DutyName'] != "U")
                            {
                                dutyName = '<b>'+data['DutyName']+'</b>';
                            }
                            let uDutyFontWeight = ' ';
                            if((requestClassStr != '') && (requestClassStr == 'requestClass') && (dutyName == 'U')){
                                uDutyFontWeight = ' font-weight:bold;'
                            }
                            let startHrs = Math.floor(data['StartTime'] / 3600);
                            let startMin = Math.floor((data['StartTime'] % 3600) / 60);
                            let endHrs = Math.floor(data['EndTime'] / 3600);
                            let endMin = Math.floor((data['EndTime'] % 3600) / 60);
                            startHrs = (startHrs > 9) ? startHrs : '0'+startHrs;
                            startMin = (startMin > 9) ? startMin : '0'+startMin;
                            endHrs = (endHrs > 9) ? endHrs : '0'+endHrs;
                            endMin = (endMin > 9) ? endMin : '0'+endMin;
                            let timeContainer = '';
                            let dutyTime = startHrs+':'+startMin+'-'+endHrs+':'+endMin;
                            if ((data['DutyName'] != '' && data['DutyName'] != "U") && (dutyName.toLowerCase() != 'sick') && (dutyName.toLowerCase() != 'u-sick') && dutyTime != '00:00-00:00')
                            {
                                if(endHrs+':'+endMin == '24:00'){
                                    timeContainer = '<br/><span style="width: 100px; display:inline-block;">'+startHrs+':'+startMin+' - '+'00:00'+'</span>';
                                } else {
                                    timeContainer = '<br/><span style="width: 100px; display:inline-block;">'+startHrs+':'+startMin+' - '+endHrs+':'+endMin+'</span>';
                                    if((data['pdlStartTime'].toString() != '0') || (data['pdlEndTime'].toString() != '0')){
                                        let pdlSTime = parseInt(data['pdlStartTime']);
                                        let pdlETime = parseInt(data['pdlEndTime']);
                                        if(pdlSTime > pdlETime){
                                            pdlETime = parseInt(parseInt('86400') + parseInt(pdlETime));
                                        }
                                        let pdlTitle = 'PDL: '+convertSecondsIntoTime(data['pdlStartTime'],':','No')+'-'+convertSecondsIntoTime(data['pdlEndTime'],':','No')+'&nbsp;|&nbsp;'+convertSecondsIntoTime(parseInt(pdlETime-data['pdlStartTime']),'.','Yes');
                                        if((markWiadClass != '') && (data['pdlStartTime'].toString() == '0') && (data['pdlEndTime'].toString() == '0')){
                                            pdlTitle = '';
                                        }

                                        timeContainer = '<br/><span style="width: 100px; white-space:nowrap;">'+startHrs+':'+startMin+' - '+endHrs+':'+endMin+'<span style="color:#109146; padding-left:4px;" title="'+pdlTitle+'">[L:'+convertSecondsIntoTime(data['pdlStartTime'],':','No')+'-'+convertSecondsIntoTime(data['pdlEndTime'],':','No')+']</span></span>';
                                    }
                                }
                            }
                            let className = '';
							data['DutyComments'] = (data['DutyComments'] == '') ? "0" : data['DutyComments'];
							data['PersonComments'] = (data['PersonComments'] == '') ? "0" : data['PersonComments'];
                            if(data['DutyComments'] == "0" && data['PersonComments'] != "0")
                            {
                                className = 'golden displayInlineBlock';
                            } else if(data['DutyComments'] != "0" && data['PersonComments'] == "0")
                            {
                                className='blue displayInlineBlock';
                            } else if(data['DutyComments'] != "0" && data['PersonComments'] != "0")
                            {
                                className='pink displayInlineBlock';
                            } else
                            {
                                className='hide';
                            }
                            let mclass = 'inactivemarkovertime';
                            if(data['MarkedOvertime'] == 1){
                                mclass='activemarkovertime';
                            }
                            let bluePoundIcon = (data['ShowEDPIcon'] == 1) ? '<div class="blue-pound-icon"></div>' : '';
                            let MarkWiadStr = data['MarkWiad'] == 1 ? 'W' : '';
                            let MarkActualStr = data['MarkActual'] == 1 ? 'A' : '';
                            if (MarkWiadStr == 'W') {
                                MarkWiadActualStr = 'W';
                            } else if (MarkActualStr == 'A') {
                                MarkWiadActualStr = 'A';
                            } else {
                                MarkWiadActualStr = '';
                            }
                            let IsUnderElevenBreakOverrideStr = (data['IsUnderElevenBreakOverride'] == 1) ? 11 : '';
                            let MarkOverTwelveStr = (data['MarkOverTwelve'] == 1) ? 12 : '';
                            let leaveSpan = '';
                            if(data['LeaveApproved'] == '1' && data['LeaveDeleted'] == '0'){
                                if(data['CountLeave'] == '1')
                                {
                                    leaveSpan = '<span class="leaveOrange"><b>L</b></span>';
                                }else{
                                    leaveSpan = '<span class="leaveHashedOrange"><b>L</b></span>';
                                }
                            }else{
                                if(data['LeaveDeleted'] == '0' && data['IsAgreed'] == '1') {
                                    leaveSpan = '<span class="LeaveAgreed"><b>L</b></span>';
                                } else if((data['LeaveisOK'] == '1') && (data['LeaveDeleted'] == '0'))
                                {
                                    if(data['LeaveShortNotice'] == '1')
                                    {
                                        leaveSpan = '<span class="leavePowderBlue"><b>L</b></span>';
                                    }else if(data['Leaveoversummer'] == '1')
                                    {
                                        leaveSpan = '<span class="leavePurple"><b>L</b></span>';
                                    }else{
                                        leaveSpan = '<span class="leaveYellow"><b>L</b></span>';
                                    }
                                } else if((data['LeaveisOK'] == '0') && (data['LeaveDeleted'] == '0')){
									if(data['LeaveShortNotice'] == '1')
                                    {
                                        leaveSpan = '<span class="leavePowderBlue"><b>L</b></span>';
                                    }else{
										leaveSpan = '<span class="leaveGray"><b>L</b></span>';
									}
                                }
                            }
                            let countLockDiv ='';
                            if ((data['ShowLock'] == 1) && (countLock == 0)) {
                                countLock++;
                                countLockDiv = '<div class="lock-icon-image"></div>';
                            }
                            let RequestClass = (data['ReqCount'] >= 1) ? 'multirequestvaialble' : '';
                            let actingLabelIcon = (data['ActingFlag'] == 1) ? '' : 'visibility: hidden';
                            let RequestImag = '';
                            if (data['ReqCount']>1){
                                RequestImag= '<span class="requestPurple"><b>R</b></span>';
                            }else{
                                if ((data['LockIconColour']=='O') && (data['ReqCount']==1))
                                {
                                    RequestImag= '<span class="requestOrange"><b>R</b></span>';
                                }
                                if((data['LockIconColour']=='B') && (data['ReqCount']==1))
                                {
                                    RequestImag= '<span class="requestPowderBlue"><b>R</b></span>';
                                }
                                 if ((data['LockIconColour']=='Y') && (data['ReqCount']==1))
                                {
                                    RequestImag='<span class="requestYellow"><b>R</b></span>';
                                }
                                if ((data['LockIconColour']=='G') && (data['ReqCount']==1))
                                {
                                    RequestImag= '<span class="requestGray"><b>R</b></span>';
                                }
                            }
                            let ondblclickCond = '';
                            if(data['EditDuty'] == 1)
                            {
                                ondblclickCond=' ondblclick="editAllocateDuty('+"'"+modalTitle+"'"+','+"'"+"edit"+"'"+','+"'"+uIdStr+"'"+')"';
                            }
							data['MasterDutyId'] = data['MasterDutyId'] ? data['MasterDutyId'] : 0;
                            data['MarkWTD'] = (data['MarkWTD']) ? data['MarkWTD'] : '';
                            data['TriangleColour'] = (data['TriangleColour']) ? data['TriangleColour'] : '';
                            allocPersonHtml  += '<div id="rowUnqId_'+uIdStr+'" '+cellbgcolour+' class="NotFixedon weekly-body-cell allocatedDutyCell '+contextClassName+' '+markWiadClass+' '+attentionClsName+' ' + doesntNeedCoveringIcon + ' alloc-cell person_'+data['SchedulingPersonID']+'" data-mannualedp="'+data['ManualEDP']+'" data-duty-name="'+data['DutyName']+'" data-unique-id="'+uIdStr+'" data-id="'+data['ID']+'" data-duty-labels="' + (data['dutyLabels'] ?? []).join(",") + '" date-range-days="'+dateRangeDays+'" date-start-date="'+pData.startDate+'" data-mark-wiad-option="'+markWiadClassFlag+'" date-end-date="'+endDate+'" data-row-uId="'+data['uId']+'" '+ondblclickCond+' onclick="removetip();">';
                     	    allocPersonHtml += '<div id="colUnqId_'+uIdStr+'" class="item-cell dragClass_'+date+'_'+data['SchedulingPersonID']+' alloc '+data['TriangleColour']+' '+ dragAllocClass+' dutydroppable '+dropeventscall+' alloc-drag-drop max-height-duty-cell '+purpleTriangleIcon+'" draggable="'+attr1+'" '+onDropCond+' data-iday ="'+data['iDay']+'" data-dateonly ="'+date+'" data-date="'+data['DutyDate']+'" data-source="allocated" data-id="'+data['ID']+'" data-duty-name="'+data['DutyName']+'" data-scheduling-person="'+data['SchedulingPersonID']+'" data-is-home-team="'+data['IsHomeTeam']+'" data-attention="'+data['isAttentionClsName']+'" data-scheduling-team-id="'+data['SchedulingTeamId']+'" data-wtd="'+data['MarkWTD']+'" data-UnAllocated="'+data['UnAllocated']+'" data-ispublished = "'+data['isPublished']+'" data-edp="'+edp+'" data-wiad="'+data['MarkWiad']+'" data-actual="'+data['MarkActual']+'" data-bgcolor="'+data['ColourBackground']+'" data-font-color="'+data['ColourFont']+'" data-mark-wiad-option="'+markWiadClassFlag+'" data-charging-present = "'+dataChargingPresent+'" data-edit-duty-flag="'+data['EditDuty']+'" data-row-id="'+ rowCnt+'" data-row-start="'+data['StartTime']+'" data-row-end="'+data['EndTime']+'" data-unique-id="'+uIdStr+'" date-range-days="'+dateRangeDays+'" date-start-date="'+pData.startDate+'" date-end-date="'+endDate+'" data-bg-color="ebebeb" data-mark-twelve="'+data['MarkOverTwelve']+'" data-mark-eleven="'+data['IsUnderElevenBreak']+'" data-show-wiad-actual="'+data['ShowWIAD']+'" data-purple-font="'+data['isRequest']+'" data-duty-duration="'+data['Duration']+'" data-isactive="'+data['isActive']+'" data-masterdutyid="'+data['MasterDutyId']+'" data-eleven-icon="'+data['IsUnderElevenBreakOverride']+'" data-leave-starttime="'+data['pdlStartTime']+'" data-leave-endtime="'+data['pdlEndTime']+'" data-scheduled-netlogin="'+data['NetLogin']+'" data-allocations-duty-id="'+data['AllocationsDutyID']+'" data-allocations-sp-id="'+data['AllocationsSPID']+'" data-col-uId="'+data['uId']+'" data-leave-id="'+data['LeaveID']+'" data-is-need-covering="'+doesntNeedCoveringIcon+'">';

                            allocPersonHtml += '<span id="dutytipID_'+date+'_'+data['SchedulingPersonID']+'" class="dutyTip '+requestClassStr+' '+triangleIconSpan+' '+otherTeamClass+'" '+markWiadClassStr+' style="overflow:hidden;'+uDutyFontWeight+'" data-mark-wiad-option="" data-id="'+data['ID']+'" '+markWiadClassStr1+'>';

                            allocPersonHtml += dutyName;
                            allocPersonHtml += timeContainer;
                            allocPersonHtml += '</span>';
                            allocPersonHtml += '<div class="DutyIconPositionLeft">' +
                                    '<div class="IconPositionClass blueEuro displayInlineBlock">'+bluePoundIcon+'</div>' +
                                    '<div class="IconPositionClass redEuro displayInlineBlock '+mclass+'" id="redEuro_'+uIdStr+'">' +
                                        '<div class="red-pound-icon"></div>' +
                                    '</div>' +
                                    '<div class="markWA icons displayInlineBlock IconPositionClass">'+MarkWiadActualStr+'</div>' +
                                    '<div class="displayInlineBlock IconPositionClass elevenIcon">' +
                                        '<span class="padd1FontW600">'+IsUnderElevenBreakOverrideStr+'</span>' +
                                    '</div>' +
                                    '<div class="displayInlineBlock IconPositionClass iconL">'+leaveSpan+'</div>' +
                                    '<div class="displayInlineBlock IconPositionClass iconLock">'+countLockDiv+'</div>' +
                                    '<div class="displayInlineBlock IconPositionClass iconR '+RequestClass+'" id="requestIcon_'+uIdStr+'">'+RequestImag+'</div>' +
                                    '<div class="displayInlineBlock IconPositionClass twelveIcon">' +
                                        '<span class="padd1FontW600">'+ MarkOverTwelveStr +'</span>' +
                                    '</div>' +
                                    '<div style="' + actingLabelIcon + '" class="displayInlineBlock IconPositionClass actingLabelIconContainer">' +
                                        '<span class="actingLabelIcon">A</span>' +
                                    '</div>' +
                                '</div>';
                            allocPersonHtml += '<div class="DutyIconPositionRight">';
                            if ((allowsignin == 1) && ((data['DutyName'].trim()!="U") && (data['DutyName']!="") && (data['DutyName'].toLowerCase()!="leave") && (data['DutyName'].toLowerCase()!="off leave")))
                            {
                                allocPersonHtml += '<div dutyid="'+data['MasterDutyId']+'" date="'+date+'" team="'+teamId+'" SchedulingPersonID="'+data['SchedulingPersonID']+'" dataid="'+data['ID']+'" class="handcursor displayInlineBlock" '+js+' onmouseover="showsignedtip('+"'showtooltip'"+',this, '+"'"+uIdStr+"'"+');" onmouseleave="showsignedtip('+"'hidetooltip'"+');"><div class="'+imageNameSignInClsName+'"></div></div>';
                            }
                            let showdutycommentsStr = '';
                            if(data['DutyComments'] != "0" || data['PersonComments'] != "0"){
                                showdutycommentsStr = 'onmouseover="showdutycomments(\'showtooltip\',this,'+data['AllocationsDutyID']+','+data['ID']+','+data['AllocationsSPID']+');" onmouseleave="showdutycomments(\'hidetooltip\');"';
                                }
                            allocPersonHtml += '<div id="circle" class="'+className+' circleIcon" '+showdutycommentsStr+' datateamid="'+data['SchedulingTeamId']+'" dataschpersonid="'+data['SchedulingPersonID']+'" dataid="'+data['ID']+'" dutydate="'+data['DutyDate']+'" ></div></div></div></div>';
                        }else{
                            allocPersonHtml += '<div class="NotFixedon weekly-body-cell person_'+data['SchedulingPersonID']+'" data-order="U-'+userName+'" data-source="allocated" data-id="'+data['ID']+'"><div class="allocated alloc" data-date="'+date+'" data-occupied=0 data-source="allocated" data-id="0" data-scheduling-person="'+data['SchedulingPersonID']+'" data-scheduling-team-id="'+data['SchedulingTeamId']+'" data-iday ="'+'dotw'+'" data-wiad="'+data['MarkWiad']+'" data-actual="'+data['MarkActual']+'" date-range-days="'+dateRangeDays+'"><span class="dutyTip" title="U"> &nbsp; </span> </div> </div>';
                        }
                    });
                        allocPersonHtml +=  '</div>';
                    rowCnt++;
                }
            }else
            {
                allocPersonHtml +=  '<div class="noRecordFound">No Record Found</div>';
            }
            allocPersonHtml +=  '</div>';
            setTimeout(function() {setBlockScrollPositions()}, 100);

            $('#allocatedDuty').html(allocPersonHtml);
            if ($('#unallocatedDuty').css('display') == 'none') {
                $('#unallocatedDuty').show();
            }
            if ($('#allocatedDuty').css('display') == 'none') {
                $('#allocatedDuty').show();
            }
            $(".chosen-selectWeekly").chosen({
                no_results_text: "No Filter",
                width: "130px",
                disable_search: false
            });
            $(".showWeekChoosen").chosen({
                no_results_text: "week",
                width: "86px",
                disable_search: false
            });
            showHideCountGrid();
            $('.dragAlloc').on('dragstart', function (event) {
                dragSource = $(this);
                event.stopImmediatePropagation();
                startDrag(dragSource);
            });
            $('.dropeventscall').on('over', function (event) {
                event.stopImmediatePropagation();
                event.preventDefault();
            });
            $('#loadingWeekly').hide();
            $('body').css('pointer-events', 'all');
            if($('#disableDrag').prop('checked') == true){
                disableDragDrop();
            }
            $('.scrollHorizontal').scroll(function() {
                $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
            });
            $('.scrollVertical').scroll(function() {
                $('.scrollVertical').scrollTop($(this).scrollTop());
            });
            resizeUnallocatedGrid();
            checkWeekAndRole('ALLOC');
            $('#loading').hide();
            dateCommentOptionsSet(0, 'fa-1x', 'lightblue');
            initializeDateComment();
            initializeNameQuickFilter();
            if($('#personRowHighlight').html() != ''){
                highlightRow($('#personRowHighlight').html());
            }
            $('#unallocatedDuty .block-25').css('max-height', $("#unallocatedDuty").height() - 26);
            initializeSchedulingPersonTooltip();
            //apply filters
            editWeeklyFilter();
        }
    });
}

function getDatesInRange(startDate, endDate) {
    const date = new Date(startDate.replace(/-/g, '\/'));
    const edate = new Date(endDate.replace(/-/g, '\/'));
    const dates = [];
    let totalRangeDays = (parseInt($('#showWeeks').val()) * 7);
    for(var i = 0; i < totalRangeDays; i++){
        let sDate = new Date(startDate.replace(/-/g, '\/'));
        sDate.setDate(sDate.getDate() +  i);
        let offsetsDate = sDate.getTimezoneOffset();
        sDate = new Date(sDate.getTime() - (offsetsDate*60*1000));
        let fDate=sDate.toISOString().split('T')[0];
        dates.push(fDate);
    }
    return dates;
}

function refreshUnAllocatedCell(unallocatedDutyData, editDutyDialog) {
    //Update unAllocDutyData
    var pDutyName, pDutyDate, dutyId, dutyDurationSec, startTimeSec, endTimeSec, allocationsDutyId;
    unallocatedDutyData.forEach(element => {
        switch(element['name']) {
            case 'ID' :
                dutyId = element['value'];
                break;
            case 'DutyName':
                pDutyName = element['value'];
                break;
            case 'DutyDate':
                pDutyDate = element['value'].slice(0,10);
                break;
            case 'dutyDurationSec':
                dutyDurationSec = element['value'];
                break;
            case 'startTimeSec':
                startTimeSec = element['value'];
                break;
            case 'endTimeSec':
                endTimeSec = element['value'];
                break;
            case 'allocationsDutyId':
                allocationsDutyId = element['value'];
                break;
            default:
                break;
        }
    });
    let $editedObj = $('#colUnqId_' + allocationsDutyId + '_' + pDutyDate);
    let unAllocDutyData = JSON.parse($('#unAllocDutyData').val());
    let oldDutyName = $editedObj.attr('data-duty-name');
    let pDutyNameStartEndTime = pDutyName + startTimeSec + endTimeSec, oldDutyNameStartEndTime = oldDutyName + $editedObj.attr('data-row-start') + $editedObj.attr('data-row-end');
    if(oldDutyNameStartEndTime != pDutyNameStartEndTime) {
        if(unAllocDutyData[pDutyNameStartEndTime] !== undefined){
            if(unAllocDutyData[pDutyNameStartEndTime][pDutyDate] !== undefined){
                let nowInstVal = parseInt(unAllocDutyData[pDutyNameStartEndTime][pDutyDate].DutyInstances)+1;
                unAllocDutyData[pDutyNameStartEndTime][pDutyDate].DutyInstances = nowInstVal.toString();
            } else {
                unAllocDutyData[pDutyNameStartEndTime][pDutyDate] = {
                    DutyInstances : '1'
                };
            }
        } else {
            unAllocDutyData[pDutyNameStartEndTime] = {
                [pDutyDate]:{
                    DutyInstances : '1'
                }
            };
        }
        //update old data
        if(unAllocDutyData[oldDutyNameStartEndTime] !== undefined && unAllocDutyData[oldDutyNameStartEndTime][pDutyDate] !== undefined) {
            if(parseInt(unAllocDutyData[oldDutyNameStartEndTime][pDutyDate].DutyInstances) > 1) {
                unAllocDutyData[oldDutyNameStartEndTime][pDutyDate].DutyInstances = parseInt(unAllocDutyData[oldDutyNameStartEndTime][pDutyDate].DutyInstances) - 1;
            } else {
                if(Object.keys(unAllocDutyData[oldDutyNameStartEndTime]).length == 1)
                {
                    delete unAllocDutyData[oldDutyNameStartEndTime];
                } else {
                    delete unAllocDutyData[oldDutyNameStartEndTime][pDutyDate];
                }
            }
        }
    }
    let unAllocDutyDataOrdered = Object.keys(unAllocDutyData).sort((a, b) => {
        return a.toString().localeCompare(b);
    }).reduce(
        (obj, key) => {
          obj[key] = unAllocDutyData[key];
          return obj;
        },
        {}
    );
    $('#unAllocDutyData').val(JSON.stringify(unAllocDutyDataOrdered));

    //Update indexed db
    const request = window.indexedDB.open('unAllocDatabase', 3);
    request.onsuccess = function () {
        const db = request.result;
        if(db.objectStoreNames.length){
            const transaction = db.transaction("weeklyData", "readwrite");
            const store = transaction.objectStore("weeklyData");
            const idQuery = store.get(1);
            idQuery.onsuccess = function () {
                let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
                let dataArr1 = [];
                let dataArrKey = [];
                let $oldCell = $('#colUnqId_' + allocationsDutyId + '_' + pDutyDate);
                let instanceCounter = { 'instanceIds': [allocationsDutyId] };
                dataArr.forEach((item) => {
                    if(oldDutyNameStartEndTime != pDutyNameStartEndTime && item[15] != allocationsDutyId && (item[0] + item[4] + item[5]) == oldDutyNameStartEndTime && item[9] == pDutyDate) {
                        if(parseInt(item[14]) > 1) {
                            let instanceIds = [];
                            item['InstanceIds'].split(',').forEach(element => {
                                if(allocationsDutyId != element) {
                                    instanceIds.push(element);
                                }
                            });
                            item[14] = item[14] - 1;
                            item['InstanceIds'] = instanceIds.join(',');
                            dataArr1.push(item);
                            dataArrKey[item[15]] = item;
                        } else {
                            dataArr1.push(item);
                            dataArrKey[item[15]] = item;
                        }
                    } else if (oldDutyNameStartEndTime != pDutyNameStartEndTime && pDutyNameStartEndTime == (item[0] + item[4] + item[5]) && item[9] == pDutyDate) {
                        item[14] = parseInt(item[14]) + 1;
                        item['InstanceIds'] = allocationsDutyId + ',' + item['InstanceIds'];
                        instanceCounter = {
                            'instanceIds': item['InstanceIds'].split(',')
                        };
                        dataArr1.push(item);
                        dataArrKey[item[15]] = item;
                    } else if(item[15] != allocationsDutyId) {
                        dataArr1.push(item);
                        dataArrKey[item[15]] = item;
                    } else if(item[15] == allocationsDutyId && oldDutyNameStartEndTime == pDutyNameStartEndTime) {
                        instanceCounter.instanceIds = item['InstanceIds'].split(',');
                    }
                });
                itemNew = {
                    "0":pDutyName,
                    "1":dutyDurationSec,
                    "2":1,
                    "3":$oldCell.attr('data-iday'),
                    "4":startTimeSec,
                    "5":endTimeSec,
                    "6":dutyId,
                    "7":$oldCell.attr('data-scheduling-team-id'),
                    "8":0,
                    "9":$oldCell.attr('data-date'),
                    "10":0,
                    "11":1,
                    "12":'',
                    "13":'UL',
                    "14": instanceCounter.instanceIds.length,
                    "InstanceIds":instanceCounter.instanceIds.join(','),
                    "15":allocationsDutyId,
                }
                dataArr1.push(itemNew);
                dataArrKey[allocationsDutyId] = itemNew;
                saveDataInIndexedDb(dataArr1, 1);
                updateUnallocDutyCellAtt(buildElementDataObject(dataArrKey[allocationsDutyId]), dataArrKey, unAllocDutyDataOrdered);
            };
            transaction.oncomplete = function () {
                db.close();
                $("#loading").hide();
                dutyIdContainer=0;
                editDutyDialog.html('');
                editDutyDialog.dialog("close");
                $('.ui-timepicker-wrapper').css('display','none');
            };
        } else {
            $('#loading').hide();
            $('#loadingWeekly').hide();
            customAlert("Please close and reopen your browser");
        }
    };
}

function buildElementDataObject(item) {
    return {
        'id' : item[6],
        'date': item[9],
        'source': item[13] ?? null,
        'dutyName': item[0],
        'dutyCount': item[11],
        'instanceIds': item['InstanceIds'],
        'schedulingTeamId': item[7],
        'rowStart': item[4],
        'rowEnd': item[5],
        'dutyInstances': item[14],
        'dutyDuration': item[1],
        'allocationsDutyId': item[15]
    };
}

function updateUnallocDutyCellAtt(updatedElementNewData, allElementData, orderDate) {
    let $rowObject = $('#rowUnqId_' + updatedElementNewData.allocationsDutyId + '_' + updatedElementNewData.date);
    let $cellDataObject = $('#colUnqId_' + updatedElementNewData.allocationsDutyId + '_' + updatedElementNewData.date);
    let $lastElement;
    if($rowObject.length == 0) {
        return false;
    }
    if(!isNaN($.cookie('unAllocSortDate'))){
        getUnAllocatedSort($.cookie('unAllocSortDate'), false, updatedElementNewData.allocationsDutyId + '_' + updatedElementNewData.date);
        return true;
    }
    if(parseInt($rowObject.attr('data-duty-instances')) > 1) {
        var $newRowObject = $rowObject.clone();
        var $newCellDataObject = $cellDataObject.clone();
        let existingIds = $rowObject.attr('data-duty-instance-ids').split(',');
		let existingIdsKey = 0;
		for(let i = 0; i < existingIds.length; i++)
		{
			if(allElementData[existingIds[i]] != undefined)
			{
				existingIdsKey = i;
			}
		}
        updateAttr($rowObject, $cellDataObject, buildElementDataObject(allElementData[existingIds[existingIdsKey]]));

        //Create new cell
        updateAttr($newRowObject, $newCellDataObject, updatedElementNewData);
    } else {
        var $newRowObject = $rowObject.clone();
        var $newCellDataObject = $cellDataObject.clone();
        let parent = $rowObject.parent();
        $rowObject.replaceWith(buildEmptyCell($rowObject.attr('data-date'), Math.floor(Math.random() *  8008), Math.floor(Math.random() *  8008), $rowObject.attr('date-range-days'), $rowObject.attr('date-start-date'), $rowObject.attr('date-end-days')));
        updateAttr($newRowObject, $newCellDataObject, updatedElementNewData);
        $lastElement = parent.clone();
        removeEmptyRow(parent);
    }

    let rowCounter = 0;
    $.each(orderDate, function(index, item) {
        rowCounter++;
        if((updatedElementNewData.dutyName + updatedElementNewData.rowStart + updatedElementNewData.rowEnd) == index) {
            $spot = $(".unAllocateddTableBody .weekly-table-row:nth-child(" + rowCounter + ")");
            if($spot.find("[data-duty-name='" + updatedElementNewData.dutyName + "']").length && $spot.find("[data-row-start='" + updatedElementNewData.rowStart + "']").length && $spot.find("[data-row-end='" + updatedElementNewData.rowEnd + "']").length) { // Already row exists
                if($spot.find(".unallocate-empty-" + updatedElementNewData.date).length) { //Empty cell
                    $spot.find(".unallocate-empty-" + updatedElementNewData.date).replaceWith($newRowObject);
                } else {//
                    $newRowObject = $spot.find('.weekly-body-cell[data-date="' + updatedElementNewData.date + '"]');
                    $newCellDataObject = $('#colUnqId_' + $newRowObject.attr('data-unique-id'));
                    updateAttr($newRowObject, $newCellDataObject, updatedElementNewData);
                    $newRowObject.replaceWith($newRowObject);
                }
            } else { // Create new row
                let availableDates = [];
                let insertAfter = 0;
                let noRowExists = false;
                if($spot.length == 0) { //If last element
                    $spot = $(".unAllocateddTableBody .weekly-table-row:nth-child(" + (rowCounter -1) + ")");
                    insertAfter = 1;
                }
                if($(".unAllocateddTableBody .weekly-table-row").length == 0) {
                    noRowExists = true;
                    $spot = $lastElement;
                }
                $spot.find('.unallocated').each(function( index ) {
                    let date = $(this).attr('data-date');
                    if ($.inArray(date, availableDates)==-1) availableDates.push(date);
                });
                let $rowDiv = $('<div class="weekly-table-row"></div>');
                let newRowCounterVal = Math.floor(Math.random() *  8008);
                availableDates.forEach(function(item) {
                    if(updatedElementNewData.date == item) {
                        $newRowObject.find('[data-row-counter]').each(function() {
                            $(this).attr('data-row-counter', newRowCounterVal);
                        });
                        $newRowObject.addClass('ulrow_' + newRowCounterVal);
                        $rowDiv.append($newRowObject);
                    } else {
                        let loopDate = item;
                        let m = rowCounter;
                        let daterangedays = availableDates.length, datestartdate = availableDates[0], dateenddate = availableDates[availableDates.length - 1];
                        let repDiv = buildEmptyCell(loopDate, newRowCounterVal, m, daterangedays, datestartdate, dateenddate);
                        $rowDiv.append($(repDiv));
                    }
                });
                if(noRowExists) {
                    $('.unAllocateddTableBody').append($rowDiv);
                } else {
                    insertAfter == 0 ? $rowDiv.insertBefore($spot) : $rowDiv.insertAfter($spot);
                }
            }
            return false;
        }
    });
    dragInitialization();
    scrollToUnallocatedElement($cellDataObject.attr('data-unique-id'));
}

function removeEmptyRow(weeklyRow) {
    if(weeklyRow.find('[data-order="U-"]').length == weeklyRow.find('.unalloc-cell').length) {
        weeklyRow.remove();
    }
}

function findNearestUnallocatedEmptyCell($tr, date) {
    $emptyCell = $tr.next().find('.unallocate-empty-' + date);
    if($emptyCell.length == 0) {
        return findNearestUnallocatedEmptyCell($tr.next(), date)
    }
    return $emptyCell;
}

function updateAttr($rowObject, $cellDataObject, elementData) {
    $rowObject.attr({
        'id': 'rowUnqId_' + elementData.allocationsDutyId + '_' + elementData.date,
        'ondblclick': 'editAllocateDuty(\'editduty\',\'edit\',' + "'" +elementData.allocationsDutyId + '_' + elementData.date  + "'" + ')',
        'title': elementData.dutyName + " (" + parseFloat(elementData.dutyDuration/3600).toFixed(2) + " Hours)",
        'data-date': elementData.date,
        'data-id': elementData.id,
        'data-duty-count': elementData.dutyCount,
        'data-duty-name': elementData.dutyName,
        'data-unique-id': elementData.allocationsDutyId + '_' + elementData.date,
        'data-scheduling-team-id': elementData.schedulingTeamId,
        'data-row-start': elementData.rowStart,
        'data-row-end': elementData.rowEnd,
        'data-duty-instances': elementData.dutyInstances,
        'data-duty-instance-ids': elementData.instanceIds
    });

    $cellDataObject.attr({
        'id': 'colUnqId_' + elementData.allocationsDutyId + '_' + elementData.date,
        'title': elementData.dutyName + " (" + parseFloat(elementData.dutyDuration/3600).toFixed(2) + " Hours)",
        'data-date': elementData.date,
        'data-id': elementData.id,
        'data-duty-count': elementData.dutyCount,
        'data-duty-name': elementData.dutyName,
        'data-unique-id': elementData.allocationsDutyId + '_' + elementData.date,
        'data-scheduling-team-id': elementData.schedulingTeamId,
        'data-row-start': elementData.rowStart,
        'data-row-end': elementData.rowEnd,
        'data-duty-instances': elementData.dutyInstances,
        'data-duty-instance-ids': elementData.instanceIds,
        'data-duty-duration': elementData.dutyDuration,
        'data-allocations-duty-id': elementData.allocationsDutyId
    });

    let st = Number(elementData.rowStart);
    var sth = Math.floor(st / 3600);
    var stm = Math.floor(st % 3600 / 60);
    if (sth < 10){
        sth = "0"+sth;
    }
    if (stm < 10){
        stm = "0"+stm;
    }

    let et = Number(elementData.rowEnd);
    var eth = Math.floor(et / 3600);
    var etm = Math.floor(et % 3600 / 60);
    if (eth < 10){
        eth = "0"+eth;
    }
    if (etm < 10){
        etm = "0"+etm;
    }
    let $title = $('<b></b>').text(elementData.dutyName);
    let $time = $('<span></span>').text(sth+':'+stm+' - '+eth+':'+etm);
    $titleContainer = $('<span class="dutyTip" style="overflow:hidden;"></span>').append([
        $title,
        $('<br>'),
        elementData.rowStart == 0 && elementData.rowEnd == 0 ? '' : $time
    ]);
    $cellDataObject.html($titleContainer);

    $rowObject.find('.DutyCountPositionRightTop').remove();
    $dutyInstanceCount = $('<div class="DutyCountPositionRightTop"></div>');
    if(parseInt(elementData.dutyInstances) == 1)
    {
        $rowObject.removeClass('cornerIcon-Grey');
        $rowObject.removeClass('cornerIcon-Unalloc')
    } else {
        $rowObject.addClass('cornerIcon-Grey');
        $rowObject.addClass('cornerIcon-Unalloc')
    }
    $dutyInstanceCount.attr('id', 'dutyCountDiv' + elementData.allocationsDutyId + '_' + elementData.date);
    $dutyInstanceCount.text(parseInt(elementData.dutyInstances) > 1 ? elementData.dutyInstances : '');
    $rowObject.html($cellDataObject);
    $rowObject.append($dutyInstanceCount);
    reloadDragDrop(elementData.allocationsDutyId + '_' + elementData.date);
}

function refreshAllocatedSectionTr(pSchedulingPersonId, pDtRowId, pDutyDate, pDutyName, pStartTime, pEndTime, pUnqId, pDataSource='',dragType='',dataSourceId=0,dataTargetId=0,dataMasterDutyId=0,loadScriptFile='No',loadUnallocDiv='No',dragSourceInst='',dropAttrInst=''){
    if(dragType.toUpperCase() != 'CONTEXTMENUEDITDUTY'){
        $("#loading").show();
        $("#loadingWeekly").show();
    }
    var dragDutyName = '';
    if(dragSourceInst != '' && dragType != 'MISC'){
        dragDutyName = dragSourceInst.attr('data-duty-name');
    }
    if(dropAttrInst != '' && dragType == 'MISC'){
        dragDutyName = dropAttrInst.getAttribute('data-duty-name');
    }
	let dragAllocationsSpId = 0;
	let dragAllocationsDutyId = 0;
	let dropAllocationsSpId = 0;
	let dropAllocationsDutyId = 0;
    let dragAllocationsId = 0;
	if((dragSourceInst != '') && (dragSourceInst.get(0).hasAttribute('data-allocations-sp-id')))
	{
		dragAllocationsSpId = dragSourceInst.get(0).getAttribute('data-allocations-sp-id');
	}
	if((dragSourceInst != '') && (dragSourceInst.get(0).hasAttribute('data-allocations-duty-id')))
	{
		dragAllocationsDutyId = dragSourceInst.get(0).getAttribute('data-allocations-duty-id');
	}
	if((dragSourceInst != '') && (dragSourceInst.get(0).hasAttribute('data-id')))
	{
		dragAllocationsId = dragSourceInst.get(0).getAttribute('data-id');
	}
	if((dragType.toUpperCase()!='ALLOCTOUNALLOC') && (dropAttrInst != '') && (dropAttrInst.hasAttribute('data-allocations-sp-id')))
	{
		dropAllocationsSpId = dropAttrInst.getAttribute('data-allocations-sp-id');
	}
	if((dragType.toUpperCase()!='ALLOCTOUNALLOC') && (dropAttrInst != '') && (dropAttrInst.hasAttribute('data-allocations-duty-id')))
	{
		dropAllocationsDutyId = dropAttrInst.getAttribute('data-allocations-duty-id');
	}
    let ajaxIniParam = JSON.parse($('#ajaxLoadParams').val());
    let pStartDate = ajaxIniParam.startDate;
    let pEndDate = ajaxIniParam.endDate;

    if($('#showWeeks').val() > 1){
        let medate = new Date(pStartDate.replace(/-/g, '\/'));
        medate.setDate(medate.getDate() + (parseInt($('#showWeeks').val()) * 7));
        let offsetmedate = medate.getTimezoneOffset();
        medate = new Date(medate.getTime() - (offsetmedate*60*1000));
        pEndDate=medate.toISOString().split('T')[0];
		medate = '';
		offsetmedate = '';
    }

    let pDateRangeDays = 7 * $('#showWeeks').val();
    if(dragSourceInst != ''){
        pStartDate = dragSourceInst.attr('date-start-date');
        pEndDate = dragSourceInst.attr('date-end-date');
        pDateRangeDays = dragSourceInst.attr('date-range-days');
        pStartTime = dragSourceInst.attr('data-row-start');
        pEndTime =  dragSourceInst.attr('data-row-end');
    }

    if($('#date').val() != ''){
        let esdate = new Date(pStartDate.replace(/-/g, '\/'));
        esdate.setDate(esdate.getDate() + 7);
        let offsetesdate = esdate.getTimezoneOffset();
        esdate = new Date(esdate.getTime() - (offsetesdate*60*1000));
        pEndDate=esdate.toISOString().split('T')[0];
		esdate = '';
		offsetesdate = '';
    }

    var reqRowData = {
        'scheduledPersonId' : pSchedulingPersonId,
        'dutyDate' : pDutyDate,
        'dutyName' : pDutyName,
        'dutyStart' : pStartTime,
        'dutyEnd' : pEndTime,
        'dutyRowId' : pDtRowId,
        'dataSource' : pDataSource,
        'dataParams' : $('#ajaxLoadParams').val(),
        'dataDragType' : dragType,
        'dataSourceId' : dataSourceId,
        'dataTargetId' : dataTargetId,
        'dataMasterDutyId' : dataMasterDutyId,
        'weekCount': $('#showWeeks').val(),
        'loadUnallocDiv': loadUnallocDiv,
        'unallocDutyData': $('#unAllocDutyData').val(),
        'pStartDate': pStartDate,
        'pEndDate': pEndDate,
        'pDateRangeDays': pDateRangeDays,
        'mastMiscFilterId': $('#mastMiscFilterId').val(),
        'dropExistDutyName': dragDutyName,
        'dragAllocationsId': dragAllocationsId,
        'dragAllocationsSpId': dragAllocationsSpId,
        'dragAllocationsDutyId': dragAllocationsDutyId,
        'dropAllocationsSpId': dropAllocationsSpId,
        'dropAllocationsDutyId': dropAllocationsDutyId
        //'dragUid': dragUid,
        //'dropUid': dropUid
    };
    if(dragType == 'SWAP'){
        reqRowData.scheduledPersonId = pSchedulingPersonId+','+dropAttrInst.getAttribute('data-scheduling-person');
    }

    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/get-allocation-cell-data.php",
        data: reqRowData,
        async: true,
        beforeSend: function(jqXHR, settings){
            $("#loading").hide();
            $("#loadingWeekly").show();
        },
        success: function (resTrData) {
            removetip();
            resTrData = $.parseJSON(resTrData);
            if(resTrData.spStatus){

                if($('#dragDropId1').html() != '' ||  $('#dragDropId2').html() != ''){
                     $('#rowUnqId_'+$('#dragDropId1').html()).css('border','');
                     $('#rowUnqId_'+$('#dragDropId2').html()).css('border','');
                     $('#dragDropId1').html('');
                     $('#dragDropId2').html('');
                }
                if(dragType.toUpperCase() == "SWAP"){
                    if($('#showWeeks').val() == 1) {
                        for ([key, value] of Object.entries(resTrData.dataTd.div25)) {
                            $( '#hrs_'+key ).html( value.dtCellVal );
                            $( '#days_'+key ).html( value.dtDayVal );
                            $( '#markOt_'+key ).attr('data-actual-seconds', value.dtCellValSeconds);
                            refreshPersonOT(key);
                        }
                    }
					let swap1 = '';
					let swap2 = '';
					let swapWtd1 = '';
					let swapWtd2 = '';
					dataSourceId = dragAllocationsSpId + '_' + pDutyDate;
					dataTargetId = (dropAllocationsSpId != 'null') ? dropAllocationsSpId + '_' + pDutyDate : dropAttrInst.getAttribute('data-scheduling-person') + '_' + pDutyDate;
                    for ([key1, value1] of Object.entries(resTrData.dataTd.div75)) {
                        if(key1 == dragSourceInst.attr('data-scheduling-person')){
                            $( '#rowUnqId_'+dataSourceId ).replaceWith( value1.dtCellVal );
                            $('#dragDropId1').html(dataSourceId);
                            reloadDragDrop(dataSourceId);
							swap1 = value1.cellData;
							swapWtd1 = value1.dtCellWTDClassName;
							let OverTimeHrs = swap1.OverTimeHrs;
							OverTimeHrs = parseFloat(OverTimeHrs / 3600).toFixed(2);
							if(!isNaN(OverTimeHrs))
							{
								if(OverTimeHrs == 0)
								{
									OverTimeHrs = '0';
								}
                                if($('#showWeeks').val() == 1) {
                                    $('#markOt_' + key1).attr('data-ot', (swap1.OverTimeHrs ?? 0));
                                    $('#markOt_' + key1).html(OverTimeHrs);
                                    refreshPersonOT(key1);
                                }
							}
                        }
                        if(key1 == dropAttrInst.getAttribute('data-scheduling-person')){
                            $( '#rowUnqId_'+dataTargetId ).replaceWith( value1.dtCellVal );
                            $('#dragDropId2').html(dataTargetId);
                            if($('#colUnqId_'+dataTargetId).attr('data-duty-name') != 'U'){
                                if($('#personRowHighlight').html() != ''){
                                    $( '#rowUnqId_'+dataTargetId ).css('border-bottom','2px solid #bbb');
                                    highlightRow($('#personRowHighlight').html());
                                } else {
                                    $( '#rowUnqId_'+dataTargetId ).css('border-bottom','2px solid #bbb');
                                }
                            }
                            reloadDragDrop(dataTargetId);
							swap2 = value1.cellData;
							swapWtd2 = value1.dtCellWTDClassName;
							let OverTimeHrs = swap2.OverTimeHrs;
							OverTimeHrs = parseFloat(OverTimeHrs / 3600).toFixed(2);
							if(!isNaN(OverTimeHrs))
							{
								if(OverTimeHrs == 0)
								{
									OverTimeHrs = '0';
								}
                                if($('#showWeeks').val() == 1) {
                                    $('#markOt_' + key1).attr('data-ot', (swap2.OverTimeHrs ?? 0));
                                    $('#markOt_' + key1).html(OverTimeHrs);
                                    refreshPersonOT(key1);
                                }
							}
                        }

                        $(value1.dtCellWTDClassName).each(function(indx, val){
                            updateWTDCrossImage(val);
                            updateUnder11Icon(val);
                        });
					}
					window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
					if (!window.indexedDB)
					{
						console.log("Your browser doesn't support a stable version of IndexedDB.");
					}else
					{
						const request = window.indexedDB.open('allocDatabase', 3);
						request.onsuccess = function () {
							const db = request.result;
                            if(db.objectStoreNames.length){
    							const transaction = db.transaction("weeklyData", "readwrite");
    							const store = transaction.objectStore("weeklyData");
    							const idQuery = store.get(1);
    							idQuery.onsuccess = function () {
    								let dataArr = idQuery.result.mainData;
    								let dataArr1 = [];
    								dataArr.forEach((number, index, rows) => {
    									if((rows[index]['AllocationsSPID'] == swap1.AllocationsSPID) || ((rows[index]['SchedulingPersonID'] == swap1.SchedulingPersonID) && (rows[index]['DutyDate'] == swap1.DutyDate)))
                                        {
                                            dataArr1.push(swap1);
                                        }else if((rows[index]['AllocationsSPID'] == swap2.AllocationsSPID) || ((rows[index]['SchedulingPersonID'] == swap2.SchedulingPersonID) && (rows[index]['DutyDate'] == swap2.DutyDate)))
                                        {
                                            dataArr1.push(swap2);
                                        }else
    									{
    										if(swapWtd1.length > 0)
    										{
                                                for(let e=0; e<swapWtd1.length; e++){
                                                    if((rows[index]['AllocationsDutyID'] == swapWtd1[e].AllocationsDutyID) && (rows[index]['WTDBreachClassName'] != swapWtd1[e].WTDBreachClassName)){
                                                        rows[index]['WTDBreachClassName'] = swapWtd1[e].WTDBreachClassName;
                                                    }
                                                }
    										}
    										if(swapWtd2.length > 0)
    										{
                                                for(let h=0; h<swapWtd2.length; h++){
                                                    if((rows[index]['AllocationsDutyID'] == swapWtd2[h].AllocationsDutyID) && (rows[index]['WTDBreachClassName'] != swapWtd2[h].WTDBreachClassName)){
                                                        rows[index]['WTDBreachClassName'] = swapWtd2[h].WTDBreachClassName;
                                                    }
                                                }
    										}
    										dataArr1.push(rows[index]);
    									}

    								});
    								saveDataInIndexedDb(dataArr1);
    								dataArr = '';
    								dataArr1 = '';
    								swap1 = '';
    								swap2 = '';
    								swapWtd1 = '';
    								swapWtd2 = '';
    							};
    							delete idQuery;
    							delete db;
    							delete transaction;
    							delete store;
                            } else {
                                $('#loading').hide();
                                $('#loadingWeekly').hide();
                                customAlert("Please close and reopen your browser");
                            }
						}
						delete request ;
					}
					if($('#personRowHighlight').html() != ''){
                        highlightRow($('#personRowHighlight').html());
                    }
                } else {
                    if((dragType.toUpperCase() == "UNALLOCTOALLOC") || (dragType.toUpperCase() == "ALLOCTOUNALLOC") || (dragType.toUpperCase() == "MISC")){
                        if((dragType.toUpperCase() == "ALLOCTOUNALLOC") && (dragSourceInst.attr('data-duty-duration') > 0) && (!dragSourceInst.parent().hasClass('doesntNeedCoveringIcon'))){
                            if(((resTrData.dataTd.div75.dutyExists == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() == '')) || ((dragType.toUpperCase() == "UNALLOCTOALLOC") && (resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() != ''))){
                                var finalUpdUnallocDutyData = updateUnAllocDutyJsonArray(dragDutyName,pDutyDate,dragType,dropAttrInst,dragSourceInst,pStartTime,pEndTime);
                            }
                        }
                        if(dragType.toUpperCase() != "ALLOCTOUNALLOC"){
							if((dropAttrInst.getAttribute('data-row-start') == 0) && (dropAttrInst.getAttribute('data-row-end') == 0) && (dropAttrInst.getAttribute('data-duty-name') != 'U')){
								var finalUpdUnallocDutyData = updateUnAllocDutyJsonArray(dragDutyName,pDutyDate,dragType,dropAttrInst,dragSourceInst,pStartTime,pEndTime);
							} else {
                                if(((resTrData.dataTd.div75.dutyExists == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() == '')) || ((dragType.toUpperCase() == "UNALLOCTOALLOC") && (resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() != ''))){
                                    var finalUpdUnallocDutyData = updateUnAllocDutyJsonArray(dragDutyName,pDutyDate,dragType,dropAttrInst,dragSourceInst,pStartTime,pEndTime);
                                }
							}
                        }
                    }
                    $('#dutyExists').html(resTrData.dataTd.div75.dutyExists);

                    if(resTrData.dataTd.div25 != undefined){
                        if($('#showWeeks').val() == 1) {
                            $( '#hrs_'+pSchedulingPersonId ).html( resTrData.dataTd.div25.dtCellVal );
                            $( '#days_'+pSchedulingPersonId ).html( resTrData.dataTd.div25.dtDayVal );
                            $( '#markOt_'+pSchedulingPersonId ).attr('data-actual-seconds', resTrData.dataTd.div25.dtCellValSeconds);
                            $( '#rowUnqId_'+pUnqId ).replaceWith( resTrData.dataTd.div75.dtCellVal );
                            refreshPersonOT(pSchedulingPersonId);
                        } else {
                            $( '#rowUnqId_'+pUnqId ).replaceWith( resTrData.dataTd.div75.dtCellVal );
                        }
                        editWeeklyFilter();
                        if($('#personRowHighlight').html() != ''){
                            highlightRow($('#personRowHighlight').html());
                        }

                        $(resTrData.dataTd.div75.dtCellWTDClassName).each(function(indx, val) {
                            updateWTDCrossImage(val);
                            updateUnder11Icon(val);
                        });

                        if(pDataSource == 'misc'){
                            $('#rowUnqId_'+pUnqId).css('background-color',resTrData.dataTd.div75.miscDutyBgColor).css('color',resTrData.dataTd.div75.miscDutyFontColor);
                        }
                        if((dragType == 'UNALLOCTOALLOC') || (dragType == 'ALLOCTOUNALLOC') || (dragType == 'MISC')){
                            if((dragType == 'ALLOCTOUNALLOC') && ((dragSourceInst != '') && (dragSourceInst.attr('data-duty-duration') > 0) && !dragSourceInst.parent().hasClass('doesntNeedCoveringIcon'))){
                                if(((resTrData.dataTd.div75.dutyExists == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() == '')) || ((dragType.toUpperCase() == "UNALLOCTOALLOC") && (resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() != ''))){
                                    updateUnallocGrid(dragSourceInst,dropAttrInst,dragAllocationsDutyId,dragType,finalUpdUnallocDutyData,dragSourceInst.attr('data-row-counter'),resTrData.dataTd.div75.dutyExists,dataSourceId);
                                }
                            }
                            if(dragType != 'ALLOCTOUNALLOC'){
								if((dropAttrInst.getAttribute('data-row-start') == 0) && (dropAttrInst.getAttribute('data-row-end') == 0) && (dropAttrInst.getAttribute('data-duty-name') != 'U')){
									$('#unAllocDutyData').val(JSON.stringify(finalUpdUnallocDutyData));
								} else {
                                    if(((resTrData.dataTd.div75.dutyExists == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() == '')) || ((dragType.toUpperCase() == "UNALLOCTOALLOC") && (resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() != ''))){
                                        updateUnallocGrid(dragSourceInst,dropAttrInst,dropAllocationsDutyId,dragType,finalUpdUnallocDutyData,dragSourceInst.attr('data-row-counter'),resTrData.dataTd.div75.dutyExists, dropAttrInst.getAttribute('data-allocations-duty-id'));
                                    }
								}

                            }
                        }
						if((resTrData.dataTd.div75.cellData.DutyName == 'U-Sick') || (resTrData.dataTd.div75.cellData.DutyName == 'Sick') || (resTrData.dataTd.div75.cellData.DutyName == '-Sick')){
							updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 0, resTrData.dataTd.div75.dtCellWTDClassName);
						}
						if(dragType.toUpperCase() == "UNALLOCTOALLOC"){
                            if((dropAttrInst.getAttribute('data-duty-name') != 'U') && (dropAttrInst.getAttribute('data-row-end') != 0) && ((resTrData.dataTd.div75.dutyExists == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((dropAttrInst.getAttribute('data-row-end') != 0) && (resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() == '')) || ((dropAttrInst.getAttribute('data-row-end') != 0) && (resTrData.dataTd.div75.dutyExists == 'No') && ($('#mastMiscFilterId').val() != '') && ($.inArray(dropAttrInst.getAttribute('data-duty-name'), resTrData.dataTd.div75.cellData.mastMiscFilterDuties) != -1))){

                                window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
                                if (!window.indexedDB)
                                {
                                    console.log("Your browser doesn't support a stable version of IndexedDB.");
                                }else
                                {
                                    const request = window.indexedDB.open('unAllocDatabase', 3);
                                    request.onsuccess = function () {
                                        const db = request.result;
                                        if(db.objectStoreNames.length){
                                            const transaction = db.transaction("weeklyData", "readwrite");
                                            const store = transaction.objectStore("weeklyData");
                                            const idQuery = store.get(1);
                                            idQuery.onsuccess = function () {
                                                let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
                                                let dataArr1 = [];
                                                let dutyExists = 0;
                                                dataArr.forEach((number, index, rows) => {
                                                    if(rows[index][0] == dragSourceInst.attr('data-duty-name') && rows[index][4] == dragSourceInst.attr('data-row-start') && rows[index][5] == dragSourceInst.attr('data-row-end') && (rows[index][9] == dragSourceInst.attr('data-date'))) {
                                                            let instIds =  dragSourceInst.attr('data-duty-instance-ids');
                                                            if(rows[index][0] == dragSourceInst.attr('data-duty-name') && rows[index][4] == dragSourceInst.attr('data-row-start') && rows[index][5] == dragSourceInst.attr('data-row-end') && rows[index][15] != dataSourceId){
                                                                let currInstIds = '';

                                                                instIds = instIds.replace(dataSourceId,'');
                                                                instIds = instIds ? instIds.match(/\d+/g) : '';
                                                                if(instIds.length > 1){
                                                                    currInstIds = instIds.join(',');
                                                                    dataDutyInstances = instIds.length;
                                                                } else {
                                                                    currInstIds = rows[index][15];
                                                                    dataDutyInstances = 1;
                                                                }
                                                                dataDutyInstanceIds = currInstIds;
                                                                rows[index][14] = dataDutyInstances;
                                                                rows[index]['InstanceIds'] = currInstIds;
                                                                dataArr1.push(rows[index]);
                                                            }
    														instIds = '';
    														currInstIds = '';
    														nowInstIds = '';

                                                    } else if(rows[index][0] == dropAttrInst.getAttribute('data-duty-name') && rows[index][4] == dropAttrInst.getAttribute('data-row-start') && rows[index][5] == dropAttrInst.getAttribute('data-row-end') && rows[index][9] == dropAttrInst.getAttribute('data-date') && dropAttrInst.getAttribute('data-is-need-covering') != 'doesntNeedCoveringIcon') {
                                                        $('.uldutycell_'+dragSourceInst.attr('data-date')).each(function(key, val) {
                                                            if($(this).attr('data-duty-name') == dropAttrInst.getAttribute('data-duty-name') && $(this).attr('data-row-start') == dropAttrInst.getAttribute('data-row-start') && $(this).attr('data-row-end') == dropAttrInst.getAttribute('data-row-end')){
                                                                if(dropAttrInst.getAttribute('data-duty-name') != 'U'){
                                                                    let instIds =  $(this).attr('data-duty-instance-ids');
                                                                    let updatedInstances = rows[index]['InstanceIds']+','+dataSourceId;
                                                                    let updatedInstancesCount = parseInt(rows[index][14])+1;
                                                                    rows[index][14] = updatedInstancesCount;
                                                                    rows[index]['InstanceIds'] = updatedInstances;
                                                                    dataArr1.push(rows[index]);
                                                                    if(dutyExists == 0){
                                                                        let newData = {
                                                                            "0":dropAttrInst.getAttribute('data-duty-name'),
                                                                            "1":dropAttrInst.getAttribute('data-duty-duration'),
                                                                            "2":1,
                                                                            "3":dropAttrInst.getAttribute('data-iday'),
                                                                            "4":dropAttrInst.getAttribute('data-row-start'),
                                                                            "5":dropAttrInst.getAttribute('data-row-end'),
                                                                            "6":dataSourceId,
                                                                            "7":dropAttrInst.getAttribute('data-scheduling-team-id'),
                                                                            "8":0,
                                                                            "9":dropAttrInst.getAttribute('data-date'),
                                                                            "10":dropAttrInst.getAttribute('data-masterdutyid'),
                                                                            "11":1,
                                                                            "12":'',
                                                                            "13":'UL',
                                                                            "14":updatedInstancesCount,
																			"15":dropAttrInst.getAttribute('data-allocations-duty-id'),
                                                                            "16":dropAttrInst.getAttribute('data-allocations-sp-id'),
                                                                            "17":dropAttrInst.getAttribute('data-col-uid'),
                                                                            "18":'',
                                                                            "InstanceIds":updatedInstances
                                                                        };
                                                                        dataArr1.push(newData);
                                                                    }
                                                                    dutyExists++;
                                                                    instIds = '';
                                                                }
                                                            }
                                                        });
                                                    } else {
                                                        if(resTrData.dataTd.div75.dtCellWTDClassName.length > 0){
                                                            for(let s=0; s<resTrData.dataTd.div75.dtCellWTDClassName.length; s++){
                                                                if((rows[index]['ID'] == resTrData.dataTd.div75.dtCellWTDClassName.id) && (rows[index]['WTDBreachClassName'] != resTrData.dataTd.div75.dtCellWTDClassName.WTDBreachClassName)){
                                                                    rows[index]['WTDBreachClassName'] = resTrData.dataTd.div75.dtCellWTDClassName.WTDBreachClassName;
                                                                }
                                                            }
                                                            dataArr1.push(rows[index]);
                                                        }
                                                    }
                                                });

                                                if(dutyExists == 0){
                                                    if(dropAttrInst.getAttribute('data-duty-name') != 'U' && dropAttrInst.getAttribute('data-is-need-covering') != 'doesntNeedCoveringIcon'){
                                                        let inpWeekNum = $('#weekNumber').val();
                                                        let splitWeekNum = inpWeekNum.split('/');
                                                        let newData = {
                                                            "0":dropAttrInst.getAttribute('data-duty-name'),
                                                            "1":dropAttrInst.getAttribute('data-duty-duration'),
                                                            "2":splitWeekNum[1]+splitWeekNum[0],
                                                            "3":dropAttrInst.getAttribute('data-iday'),
                                                            "4":dropAttrInst.getAttribute('data-row-start'),
                                                            "5":dropAttrInst.getAttribute('data-row-end'),
                                                            "6":dataSourceId,
                                                            "7":dropAttrInst.getAttribute('data-scheduling-team-id'),
                                                            "8":0,
                                                            "9":dropAttrInst.getAttribute('data-date'),
                                                            "10":dropAttrInst.getAttribute('data-masterdutyid'),
                                                            "11":1,
                                                            "12":'',
                                                            "13":'UL',
                                                            "14":1,
															"15":dropAttrInst.getAttribute('data-allocations-duty-id'),
															"16":dropAttrInst.getAttribute('data-allocations-sp-id'),
															"17":dropAttrInst.getAttribute('data-col-uid'),
															"18":'',
                                                            "InstanceIds":dataSourceId
                                                        };
                                                        dataArr1.push(newData);
                                                        inpWeekNum = '';
                                                        newData = '';
                                                        splitWeekNum = '';
                                                    }
                                                }
    											saveDataInIndexedDb(dataArr1, 1);

                                                if(dutyExists == 0){
                                                    if(!isNaN($.cookie('unAllocSortDate'))){
                                                        getUnAllocatedSort($.cookie('unAllocSortDate'));
                                                        let timerCallSec = 500;
                                                        if($('#showWeeks').val() > 1){
                                                            timerCallSec = 1000;
                                                        }
                                                        setTimeout(function() {
                                                            if(checkUnallocatedSectionHeight()){
                                                                if(dropAttrInst.getAttribute('data-duty-name') != 'U'){
                                                                    var sortedDutyNames = sortKeys(JSON.parse($('#unAllocDutyData').val())).sort();
                                                                    var counterUn = 0;
                                                                    var dutyName = '';
                                                                    $.each(sortedDutyNames, function (key, value) {
                                                                        dutyName = dragSourceInst.attr('data-duty-name') + dragSourceInst.attr('data-row-start') + dragSourceInst.attr('data-row-end');
                                                                        if(dutyName.toLowerCase() == value){
                                                                            counterUn = (key+1);
                                                                            return false;
                                                                        }
                                                                    });
    																sortedDutyNames = '';
    																dutyName =  '';
                                                                    if(sortKeys(JSON.parse($('#unAllocDutyData').val())).sort().length == counterUn){
                                                                        let ele = $('#rowUnqId_'+dropAllocationsDutyId + '_' +dragSourceInst.attr('data-date'))[0];
																		if(ele)
																		{
																			ele.scrollIntoView({block: "end"});
																		}
                                                                    } else {
																	let ele = $('#rowUnqId_'+dropAllocationsDutyId + '_' +dragSourceInst.attr('data-date'))[0];
																	if(ele)
																	{
																		ele.scrollIntoView({block: "start"});
																	}
                                                                        if($('#showWeeks').val() > 1){
                                                                            $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                                                                        } else {
                                                                            if($('#date').val() == ''){
                                                                                $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 30);
                                                                            } else {
                                                                                $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                                                                            }
                                                                        }
                                                                        $( '#rowUnqId_'+dragSourceInst.attr('data-unique-id') ).css('border-bottom','2px solid #bbb');
                                                                    }
                                                                    counterUn = '';
                                                                    if(parseInt($('#setTop').val()) == 1){
                                                                        //For Now Keep this code as it is in commented state
                                                                        //$('#unallocatedDuty').css('top','4px');
                                                                    }
                                                                    $('#setTop').val('1');
                                                                }


                                                            }

                                                        }, timerCallSec);
    													timerCallSec = '';
    												}
                                                }
                                                dutyExists = '';
        										dataArr = '';
        										dataArr1 = '';
    										};
    										delete db;
    										delete transaction;
    										delete store;
                                        } else {
                                            $('#loading').hide();
                                            $('#loadingWeekly').hide();
                                            customAlert("Please close and reopen your browser");
                                        }
                                    }
                                    delete request;
								}
                                updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 0, resTrData.dataTd.div75.dtCellWTDClassName);
                            } else {
                                updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 0, resTrData.dataTd.div75.dtCellWTDClassName);
                                updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 3);
								if(dropAttrInst.getAttribute('data-duty-name') != 'U' && (dropAttrInst.getAttribute('data-row-start') != 0 || dropAttrInst.getAttribute('data-row-end') != 0))
								{
									let scrollLeftPosTemp = $('#weeklyUnAllocation-2').scrollLeft();
									reloadeditweeklygrid('','','','','UNALLOC');
									setTimeout(function() {
										$('#weeklyUnAllocation-2').scrollTop(Math.abs($.cookie("unallocDutyBlockPosTop") - 2));
										$('#weeklyUnAllocation-2').scrollLeft(scrollLeftPosTemp);
									}, 500);
								}
                            }
						}
                        var dtDutyName = resTrData.dataTd.div75.cellData.DutyName;
                        if(dragType.toUpperCase() == "CONTEXTMENUEDITSICK"){
                            dtDutyName = dragSourceInst.attr('data-duty-name');
                        }
						if((dragType.toUpperCase() == "ALLOCTOUNALLOC") || ((dragType.toUpperCase() == "CONTEXTMENUEDITSICK") && (dtDutyName.toLowerCase() != 'u-sick') && (dtDutyName.toLowerCase() != 'sick') && (dtDutyName.toLowerCase() != '-sick'))){
							if((dragSourceInst.attr('data-duty-name') != resTrData.dataTd.div75.cellData.DutyName) && (dragSourceInst.attr('data-duty-duration') != 0)){
                                var chkDutyExist = '';
                                var unAllocatedNewId = 0;
                                if(dragType.toUpperCase() == "CONTEXTMENUEDITSICK"){
                                    chkDutyExist = dragSourceInst.attr('data-duty-exists');
                                    unAllocatedNewId = dragSourceInst.attr('data-new-id');
                                } else {
                                    chkDutyExist = resTrData.dataTd.div75.dutyExists;
                                    unAllocatedNewId = dragAllocationsDutyId;
                                }
                                updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 0, resTrData.dataTd.div75.dtCellWTDClassName);
								let dataDutyInstanceIds = '';
								let dataDutyInstances = 0;
								$('.uldutycell_'+dragSourceInst.attr('data-date')).each(function(key, val) {
									if($(this).attr('data-duty-name') == dragSourceInst.attr('data-duty-name') && $(this).attr('data-row-start') == dragSourceInst.attr('data-row-start') && $(this).attr('data-row-end') == dragSourceInst.attr('data-row-end')){
										let instIds =  $(this).attr('data-duty-instance-ids');
										let splitInsIds = instIds.split(',');
										if(splitInsIds.length > 1)
										{
											dataDutyInstanceIds = $(this).attr('data-duty-instance-ids');
											dataDutyInstances = splitInsIds.length;
										}else
										{
											dataDutyInstanceIds = $(this).attr('data-duty-instance-ids');
											dataDutyInstances =  1;
										}
										instIds = '';
										splitInsIds = '';
									}
								});
                                let isDoesntNeedCoveringEnabled = (dragSourceInst.attr('data-doesnt-need-covering') == 'on' || dragSourceInst.parent().hasClass('doesntNeedCoveringIcon') ? true : false);
                                window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
                                if (!window.indexedDB){
                                    console.log("Your browser doesn't support a stable version of IndexedDB.");
                                } else if(!isDoesntNeedCoveringEnabled) {
                                    const request = window.indexedDB.open('unAllocDatabase', 3);
                                    request.onsuccess = function () {
                                        const db = request.result;
                                        if(db.objectStoreNames.length){
                                            const transaction = db.transaction("weeklyData", "readwrite");
                                            const store = transaction.objectStore("weeklyData");
                                            const idQuery = store.get(1);
                                            idQuery.onsuccess = function () {
                                                let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
                                                let dataArr1 = [];
                                                dataArr.forEach((number, index, rows) => {
                                                    if(rows[index][0] == dragSourceInst.attr('data-duty-name') && rows[index][4] == dragSourceInst.attr('data-row-start') && rows[index][5] == dragSourceInst.attr('data-row-end') && rows[index][9] == dragSourceInst.attr('data-date')){
                                                        rows[index][14] = dataDutyInstances;
                                                        rows[index]['InstanceIds'] = dataDutyInstanceIds.toString();
                                                    }
                                                    dataArr1.push(rows[index]);
                                                });
                                                if(((chkDutyExist == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((chkDutyExist == 'No') && ($('#mastMiscFilterId').val() == '')) || ((dragType.toUpperCase() == "UNALLOCTOALLOC") && (chkDutyExist == 'No') && ($('#mastMiscFilterId').val() != ''))){
                                                    let inpWeekNum = $('#weekNumber').val();
                                                    let splitWeekNum = inpWeekNum.split('/');
                                                    let newData = {
                                                        "0": dragSourceInst.attr('data-duty-name'),
                                                        "1": dragSourceInst.attr('data-duty-duration'),
                                                        "2": splitWeekNum[1]+splitWeekNum[0],
                                                        "3": dragSourceInst.attr('data-iday'),
                                                        "4": dragSourceInst.attr('data-row-start'),
                                                        "5": dragSourceInst.attr('data-row-end'),
                                                        "6": dragAllocationsId,
                                                        "7": dragSourceInst.attr('data-scheduling-team-id'),
                                                        "8": 0,
                                                        "9": dragSourceInst.attr('data-date'),
                                                        "10": dragSourceInst.attr('data-masterdutyid'),
                                                        "11": dragSourceInst.attr('data-isactive'),
                                                        "12": '',
                                                        "13": 'UL',
                                                        "14": (dataDutyInstances == 0) ? 1 : dataDutyInstances,
                                                        //'15': unAllocatedNewId,
														"15":dragSourceInst.attr('data-allocations-duty-id'),
														"16":dragSourceInst.attr('data-allocations-sp-id'),
														"17":dragSourceInst.attr('data-col-uid'),
														"18":'',
                                                        "InstanceIds": (dataDutyInstanceIds == '') ? unAllocatedNewId : dataDutyInstanceIds.toString()
                                                    };
                                                    dataArr1.push(newData);
    												inpWeekNum = '';
    												splitWeekNum = '';
    												newData = '';
                                                }
    											saveDataInIndexedDb(dataArr1, 1);
                                                if(!isNaN($.cookie('unAllocSortDate'))){
                                                    getUnAllocatedSort($.cookie('unAllocSortDate'));
                                                    setTimeout(function() {
                                                        if(checkUnallocatedSectionHeight()){
                                                            var sortedDutyNames = sortKeys(JSON.parse($('#unAllocDutyData').val())).sort();
                                                            var counterUn = 0;
                                                            var dutyName = '';
                                                            $.each(sortedDutyNames, function (key, value) {
                                                                dutyName = dragSourceInst.attr('data-duty-name') + dragSourceInst.attr('data-row-start') + dragSourceInst.attr('data-row-end');
                                                                if(dutyName.toLowerCase() == value){
                                                                    counterUn = (key+1);
                                                                    return false;
                                                                }
                                                            });
    														sortedDutyNames = '';
    														dutyName = '';
															unAllocatedNewId + '_' + dragSourceInst.attr('data-date');
                                                            if(sortKeys(JSON.parse($('#unAllocDutyData').val())).sort().length == counterUn){
                                                                let el = $('#rowUnqId_'+unAllocatedNewId + '_' + dragSourceInst.attr('data-date'))[0];
																if(el)
																{
																	el.scrollIntoView({block: "end"});
																}
                                                            } else {
																let el = $('#rowUnqId_'+unAllocatedNewId + '_' + dragSourceInst.attr('data-date'))[0];
																if(el)
																{
																	el.scrollIntoView({block: "start"});
																}
    															if($('#showWeeks').val() > 1){
    																$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
    															} else {
    																if($('#date').val() == ''){
    																	$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 30);
    																	if((parseInt($('#setTop').val()) == 1) || ($('#setTop').val() == '')){
    																		//$('#unallocatedDuty').css('top','4px');
    																	}
    																	$('#setTop').val('1');
    																} else {
    																	$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
    																}
    															}
    															$( '#rowUnqId_'+unAllocatedNewId ).css('border-bottom','2px solid #bbb');
                                                            }
    														counterUn = '';
                                                            chkDutyExist = '';
                                                            unAllocatedNewId = '';
                                                        }
                                                    }, 2000);
                                                } else {
                                                    if(parseInt($('#setTop').val()) == 1){
                                                        $('#unallocatedDuty').css('top','4px');
                                                    }
                                                    $('#setTop').val('1');
                                                }
                                                if(parseInt($('#setTop').val()) == 1){
                                                    //For Now Keep this code as it is in commented state
                                                    //$('#unallocatedDuty').css('top','4px');
                                                }
                                                $('#setTop').val('1');
    											dataArr = '';
    											dataArr1 = '';
                                            };
    										delete idQuery;
    										delete db;
    										delete transaction;
    										delete store;
                                        } else {
                                            $('#loading').hide();
                                            $('#loadingWeekly').hide();
                                            customAlert("Please close and reopen your browser");
                                        }
                                    }
									delete request;
                                }
						    }else if((dragSourceInst.attr('data-duty-name') != resTrData.dataTd.div75.cellData.DutyName) && (dragSourceInst.attr('data-duty-duration') == 0)){
                                updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 0, resTrData.dataTd.div75.dtCellWTDClassName);
							}
						}
						if(dragType.toUpperCase() == "MISC"){
							if(dropAttrInst.getAttribute('data-duty-name') != resTrData.dataTd.div75.cellData.DutyName){
								updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 0, resTrData.dataTd.div75.dtCellWTDClassName);
								if((dropAttrInst.getAttribute('data-duty-name') != 'U') && (dropAttrInst.getAttribute('data-row-end') > 0))
								{
									let dataDutyInstanceIds = '';
									let dataDutyInstances = 0;
									$('.uldutycell_'+dropAttrInst.getAttribute('data-date')).each(function(key, val) {
										if($(this).attr('data-duty-name') == dropAttrInst.getAttribute('data-duty-name')){
											let instIds =  $(this).attr('data-duty-instance-ids');
											let splitInsIds = instIds.split(',');
											if(splitInsIds.length > 1)
											{
												dataDutyInstanceIds = $(this).attr('data-duty-instance-ids');
												dataDutyInstances = splitInsIds.length;
											}else
											{
												dataDutyInstanceIds = $(this).attr('data-duty-instance-ids');
												dataDutyInstances =  1;
											}
											instIds = '';
											splitInsIds = '';
										}
									});
									let inpWeekNum = $('#weekNumber').val();
									let splitWeekNum = inpWeekNum.split('/');
									 let newData = {
														"0": dropAttrInst.getAttribute('data-duty-name'),
														"1": dropAttrInst.getAttribute('data-duty-duration'),
														"2": splitWeekNum[1]+splitWeekNum[0],
														"3": dropAttrInst.getAttribute('data-iday'),
														"4": dropAttrInst.getAttribute('data-row-start'),
														"5": dropAttrInst.getAttribute('data-row-end'),
														"6": resTrData.dataTd.div75.unAllocNewId,
														"7": dropAttrInst.getAttribute('data-scheduling-team-id'),
														"8": 0,
														"9": dropAttrInst.getAttribute('data-date'),
														"10": dropAttrInst.getAttribute('data-masterdutyid'),
														"11": dropAttrInst.getAttribute('data-isactive'),
														"12": '',
														"13": 'UL',
														"14": (dataDutyInstances == 0) ? 1 : dataDutyInstances,
														"15":dropAttrInst.getAttribute('data-allocations-duty-id'),
														"16":dropAttrInst.getAttribute('data-allocations-sp-id'),
														"17":dropAttrInst.getAttribute('data-col-uid'),
														"18":'',
														"InstanceIds": (dataDutyInstanceIds == '') ? dropAttrInst.getAttribute('data-allocations-duty-id') : dataDutyInstanceIds.toString()
													};
									window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
									if (!window.indexedDB){
										console.log("Your browser doesn't support a stable version of IndexedDB.");
									} else {
										const request = window.indexedDB.open('unAllocDatabase', 3);
										request.onsuccess = function () {
											const db = request.result;
                                            if(db.objectStoreNames.length){
											    const transaction = db.transaction("weeklyData", "readwrite");
    											const store = transaction.objectStore("weeklyData");
    											const idQuery = store.get(1);
    											idQuery.onsuccess = function () {
    												let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
    												dataArr.push(newData);
    												saveDataInIndexedDb(dataArr, 1);

    												if(!isNaN($.cookie('unAllocSortDate'))){
    													getUnAllocatedSort($.cookie('unAllocSortDate'));
    													setTimeout(function() {
    														if(checkUnallocatedSectionHeight()){
    															var sortedDutyNames = sortKeys(JSON.parse($('#unAllocDutyData').val())).sort();
    															var counterUn = 0;
    															var dutyName = '';
    															$.each(sortedDutyNames, function (key, value) {
    																dutyName = dropAttrInst.getAttribute('data-duty-name') + dropAttrInst.getAttribute('data-row-start') + dropAttrInst.getAttribute('data-row-end');
    																if(dutyName.toLowerCase() == value){
    																	counterUn = (key+1);
    																	return false;
    																}
    															});
    															sortedDutyNames = '';
    															dutyName = '';
    															if(sortKeys(JSON.parse($('#unAllocDutyData').val())).sort().length == counterUn){
    																let el = $('#rowUnqId_'+dropAttrInst.getAttribute('data-allocations-duty-id') + '_' + dropAttrInst.getAttribute('data-date'))[0];
																	if(el)
																	{
																		el.scrollIntoView({block: "end"});
																	}
    															} else {
    																let el = $('#rowUnqId_'+dropAttrInst.getAttribute('data-allocations-duty-id') + '_' + dropAttrInst.getAttribute('data-date'))[0];
																	if(el)
																	{
																		el.scrollIntoView({block: "start"});
																	}
    																if($('#showWeeks').val() > 1){
    																	$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
    																} else {
    																	if($('#date').val() == ''){
    																		$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 30);
    																	} else {
    																		$('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
    																	}
    																}
    																$( '#rowUnqId_'+resTrData.dataTd.div75.unAllocNewId ).css('border-bottom','2px solid #bbb');
    															}
    															counterUn = '';
                                                                dataDutyInstanceIds = '';
                                                                dataDutyInstances = '';
                                                                inpWeekNum = '';
                                                                splitWeekNum = '';
                                                                newData = '';
    														}
    													}, 2000);
    												}
    												if(parseInt($('#setTop').val()) == 1){
                                                        //For Now Keep this code as it is in commented state
    													//$('#unallocatedDuty').css('top','4px');
    												}
    												$('#setTop').val('1');
    												dataArr = '';
    											}
    											delete idQuery;
    											delete db;
    											delete transaction;
    											delete store;
                                            } else {
                                                $('#loading').hide();
                                                $('#loadingWeekly').hide();
                                                customAlert("Please close and reopen your browser");
                                            }
										}
										delete request;
									}
								}
							}
						}
						if(dragType.toUpperCase() == "CONTEXTMENUEDITDUTY"){

							if(pDutyName != resTrData.dataTd.div75.cellData.DutyName){
								updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 1, resTrData.dataTd.div75.dtCellWTDClassName);
							}else
							{
								updateDataInIndexedDb(resTrData.dataTd.div75.cellData, 0, resTrData.dataTd.div75.dtCellWTDClassName);
							}

						}
                    }
                    if((dragType.toUpperCase() == "UNALLOCTOALLOC") || (dragType.toUpperCase() == "MISC")){
                        if((dragType.toUpperCase() == "UNALLOCTOALLOC") && (dropAttrInst.getAttribute('data-duty-name') != 'U')){
                            if($('#personRowHighlight').html() != ''){
                                $( '#rowUnqId_'+pUnqId ).css('border-bottom','2px solid #bbb');
                                highlightRow($('#personRowHighlight').html());
                            } else {
                                $( '#rowUnqId_'+pUnqId ).css('border-bottom','2px solid #bbb');
                            }
                            $('#dragDropId1').html(dropAttrInst.getAttribute('data-id'));
                            $('.uldutycell_'+dragSourceInst.attr('data-date')).each(function(key, val) {
                                if($(this).attr('data-duty-name') != undefined){
                                    if($(this).attr('data-duty-name') == dropAttrInst.getAttribute('data-duty-name') && $(this).attr('data-row-start') == dropAttrInst.getAttribute('data-row-start') && $(this).attr('data-row-end') == dropAttrInst.getAttribute('data-row-end')){
                                        $('#dragDropId2').html($(this).attr('data-id'));
                                    }
                                }
                            });
                        } else {
							pUnqId = pSchedulingPersonId + '_' + pDutyDate;
                            if($('#personRowHighlight').html() != ''){
                                $( '#rowUnqId_'+pUnqId ).css('border-bottom','2px solid #bbb');
                                highlightRow($('#personRowHighlight').html());
                            } else {
                                $( '#rowUnqId_'+pUnqId ).css('border-bottom','2px solid #bbb');
                            }
                            $('#dragDropId1').html(dropAttrInst.getAttribute('data-id'));
                        }
                    }
                    if(dragType.toUpperCase() == "ALLOCTOUNALLOC"){
                        $( '#rowUnqId_'+resTrData.dataTd.div75.unAllocNewId ).css('border-bottom','2px solid #bbb');
                        $('#dragDropId1').html(resTrData.dataTd.div75.unAllocNewId);
                    }
					let OverTimeHrs = resTrData.dataTd.div75.cellData.OverTimeHrs;
					OverTimeHrs = parseFloat(OverTimeHrs / 3600).toFixed(2);
					if(!isNaN(OverTimeHrs))
					{
						if(OverTimeHrs == 0)
						{
							OverTimeHrs = '0';
						}
                        if($('#showWeeks').val() == 1) {
                            $('#markOt_' + pSchedulingPersonId).attr('data-ot', (resTrData.dataTd.div75.cellData.OverTimeHrs ?? 0));
                            $('#markOt_' + pSchedulingPersonId).html(OverTimeHrs);
                            refreshPersonOT(pSchedulingPersonId);
                        }
					}
                }
                if($('#showHideCount').prop('checked') == true){
                    reloadShiftCountingGrid();
                }

                $('#sourceDataCell').attr('current-id','0').html('');
                $('#targetDataCell').attr('current-id','0').html('');
                setTimeout(function() {
                    if(!isNaN($.cookie('unAllocSortDate'))){
                        getUnAllocatedSort($.cookie('unAllocSortDate'));
                    }
                    if($('#personRowHighlight').html() != ''){
                        highlightRow($('#personRowHighlight').html());
                    }
                    $("#loadingWeekly").hide();
                }, 300);
            } else {
                if(dragType == 'UNALLOCTOALLOC'){
                    let unallocCellId = $('#sourceDataCell').attr('current-id');
                    $('#rowUnqId_'+unallocCellId).replaceWith($('#sourceDataCell').html());
                    let allocCellId = $('#targetDataCell').attr('current-id');
                    $('#rowUnqId_'+allocCellId).replaceWith($('#targetDataCell').html());
                    $('#rowUnqId_'+allocCellId).css('background-color','#ebebeb');
					unallocCellId = '';
					allocCellId = '';
                }
                if(dragType == 'ALLOCTOUNALLOC' || dragType == 'SWAP'){
                    let allocCellId1 = $('#sourceDataCell').attr('current-id');
                    $('#rowUnqId_'+allocCellId1).replaceWith($('#sourceDataCell').html());
                    $('#rowUnqId_'+allocCellId1).css('background-color','#ebebeb');
                    if($('#rowUnqId_'+allocCellId1).hasClass('cross-red')){
                        $('#rowUnqId_'+allocCellId1).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'red\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'red\' stroke-width=\'2\'/></svg>")');
                        $('#rowUnqId_'+allocCellId1).css('background-repeat','no-repeat');
                        $('#rowUnqId_'+allocCellId1).css('background-position','center');
                        $('#rowUnqId_'+allocCellId1).css('background-size','100% 100%, auto');
                    }
                    if($('#rowUnqId_'+allocCellId1).hasClass('cross-blue')){
                        $("#rowUnqId_"+allocCellId1).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'lightblue\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'lightblue\' stroke-width=\'2\'/></svg>")');
                        $("#rowUnqId_"+allocCellId1).css('background-repeat','no-repeat');
                        $("#rowUnqId_"+allocCellId1).css('background-position','center');
                        $("#rowUnqId_"+allocCellId1).css('background-size','100% 100%, auto');
                    }
                    if($('#rowUnqId_'+allocCellId1).hasClass('attentionClass')){
                        $('#rowUnqId_'+allocCellId1).css('background-color','#FFB6C1');
                    }
                    if($('#rowUnqId_'+allocCellId1).hasClass('attentionCopyDutyClass')){
                        $('#rowUnqId_'+allocCellId1).css('background-color','#9370db');
                    }

                    let allocCellId2 = $('#targetDataCell').attr('current-id');
                    $('#rowUnqId_'+allocCellId2).replaceWith($('#targetDataCell').html());
                    $('#rowUnqId_'+allocCellId2).css('background-color','#ebebeb');
                    if($('#rowUnqId_'+allocCellId2).hasClass('cross-red')){
                        $('#rowUnqId_'+allocCellId2).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'red\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'red\' stroke-width=\'2\'/></svg>")');
                        $('#rowUnqId_'+allocCellId2).css('background-repeat','no-repeat');
                        $('#rowUnqId_'+allocCellId2).css('background-position','center');
                        $('#rowUnqId_'+allocCellId2).css('background-size','100% 100%, auto');
                    }
                    if($('#rowUnqId_'+allocCellId2).hasClass('cross-blue')){
                        $("#rowUnqId_"+allocCellId2).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'lightblue\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'lightblue\' stroke-width=\'2\'/></svg>")');
                        $("#rowUnqId_"+allocCellId2).css('background-repeat','no-repeat');
                        $("#rowUnqId_"+allocCellId2).css('background-position','center');
                        $("#rowUnqId_"+allocCellId2).css('background-size','100% 100%, auto');
                    }
                    if($('#rowUnqId_'+allocCellId2).hasClass('attentionClass')){
                        $('#rowUnqId_'+allocCellId2).css('background-color','#FFB6C1');
                    }
                    if($('#rowUnqId_'+allocCellId2).hasClass('attentionCopyDutyClass')){
                        $('#rowUnqId_'+allocCellId2).css('background-color','#9370db');
                    }
					allocCellId1 = '';
					allocCellId2 = '';
                }
                $('#sourceDataCell').attr('current-id','0').html('');
                $('#targetDataCell').attr('current-id','0').html('');
                if(resTrData.spErrorMessage == null){
                    setTimeout(function() {
                        $("#loadingWeekly").hide();
						let scrollLeftPosTemp = $('#weeklyUnAllocation-2').scrollLeft();
						reloadeditweeklygrid('','','','','UNALLOC');
						setTimeout(function() {
							$('#weeklyUnAllocation-2').scrollTop(Math.abs($.cookie("unallocDutyBlockPosTop") - 2));
							$('#weeklyUnAllocation-2').scrollLeft(scrollLeftPosTemp);
						}, 500);
                        customAlert('There is problem with the data. Underlying data has been modified. Please refresh the page and try again1.');
                    }, 300);
                } else {
					if(resTrData.spErrorMessage.match("Underlying data has been modified"))
					{
						reloadeditweeklygrid('','','','','UNALLOC');
						setTimeout(function() {
							$('#weeklyUnAllocation-2').scrollTop(Math.abs($.cookie("unallocDutyBlockPosTop") - 2));
						}, 500);
					}
                    setTimeout(function() {
                        $("#loadingWeekly").hide();
                        customAlert(resTrData.spErrorMessage);
                    }, 300);
                }
            }
            $('.dragAlloc').on('dragstart', function (event) {
                dragSource = $(this);
                event.stopImmediatePropagation();
                startDrag(dragSource);
            });

            $('.dropeventscall').on('over', function (event) {
                event.stopImmediatePropagation();
                event.preventDefault();
            });

            if($('#disableDrag').prop('checked') == true){
                disableDragDrop();
            }
        },
        error: function(x, e){
            if(dragType == 'UNALLOCTOALLOC'){
                let unallocCellId = $('#sourceDataCell').attr('current-id');
                $('#rowUnqId_'+unallocCellId).replaceWith($('#sourceDataCell').html());

                let allocCellId = $('#targetDataCell').attr('current-id');
                $('#rowUnqId_'+allocCellId).replaceWith($('#targetDataCell').html());
                $('#rowUnqId_'+allocCellId).css('background-color','#ebebeb');
				unallocCellId = '';
				allocCellId = '';
            }
            if(dragType == 'ALLOCTOUNALLOC' || dragType == 'SWAP'){
                let allocCellId1 = $('#sourceDataCell').attr('current-id');
                $('#rowUnqId_'+allocCellId1).replaceWith($('#sourceDataCell').html());
                $('#rowUnqId_'+allocCellId1).css('background-color','#ebebeb');
                if($('#rowUnqId_'+allocCellId1).hasClass('cross-red')){
                    $('#rowUnqId_'+allocCellId1).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'red\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'red\' stroke-width=\'2\'/></svg>")');
                    $('#rowUnqId_'+allocCellId1).css('background-repeat','no-repeat');
                    $('#rowUnqId_'+allocCellId1).css('background-position','center');
                    $('#rowUnqId_'+allocCellId1).css('background-size','100% 100%, auto');
                }
                if($('#rowUnqId_'+allocCellId1).hasClass('cross-blue')){
                    $("#rowUnqId_"+allocCellId1).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'lightblue\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'lightblue\' stroke-width=\'2\'/></svg>")');
                    $("#rowUnqId_"+allocCellId1).css('background-repeat','no-repeat');
                    $("#rowUnqId_"+allocCellId1).css('background-position','center');
                    $("#rowUnqId_"+allocCellId1).css('background-size','100% 100%, auto');
                }
                if($('#rowUnqId_'+allocCellId1).hasClass('attentionClass')){
                    $('#rowUnqId_'+allocCellId1).css('background-color','#FFB6C1');
                }
                if($('#rowUnqId_'+allocCellId1).hasClass('attentionCopyDutyClass')){
                    $('#rowUnqId_'+allocCellId1).css('background-color','#9370db');
                }

                let allocCellId2 = $('#targetDataCell').attr('current-id');
                $('#rowUnqId_'+allocCellId2).replaceWith($('#targetDataCell').html());
                $('#rowUnqId_'+allocCellId2).css('background-color','#ebebeb');
                if($('#rowUnqId_'+allocCellId2).hasClass('cross-red')){
                    $('#rowUnqId_'+allocCellId2).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'red\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'red\' stroke-width=\'2\'/></svg>")');
                    $('#rowUnqId_'+allocCellId2).css('background-repeat','no-repeat');
                    $('#rowUnqId_'+allocCellId2).css('background-position','center');
                    $('#rowUnqId_'+allocCellId2).css('background-size','100% 100%, auto');
                }
                if($('#rowUnqId_'+allocCellId2).hasClass('cross-blue')){
                    $("#rowUnqId_"+allocCellId2).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'lightblue\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'lightblue\' stroke-width=\'2\'/></svg>")');
                    $("#rowUnqId_"+allocCellId2).css('background-repeat','no-repeat');
                    $("#rowUnqId_"+allocCellId2).css('background-position','center');
                    $("#rowUnqId_"+allocCellId2).css('background-size','100% 100%, auto');
                }
                if($('#rowUnqId_'+allocCellId2).hasClass('attentionClass')){
                    $('#rowUnqId_'+allocCellId2).css('background-color','#FFB6C1');
                }
                if($('#rowUnqId_'+allocCellId2).hasClass('attentionCopyDutyClass')){
                    $('#rowUnqId_'+allocCellId2).css('background-color','#9370db');
                }
				allocCellId1 = '';
				allocCellId2 = '';
            }
            $('#sourceDataCell').attr('current-id','0').html('');
            $('#targetDataCell').attr('current-id','0').html('');
            $("#loading").hide();
            // $('body').css('pointer-events', 'all');
            $('.dragAlloc').on('dragstart', function (event) {
                dragSource = $(this);
                event.stopImmediatePropagation();
                startDrag(dragSource);
            });

            $('.dropeventscall').on('over', function (event) {
                event.stopImmediatePropagation();
                event.preventDefault();
            });

            if($('#disableDrag').prop('checked') == true){
                disableDragDrop();
            }
            $("#loadingWeekly").hide();
			let scrollLeftPosTemp = $('#weeklyUnAllocation-2').scrollLeft();
			reloadeditweeklygrid('','','','','UNALLOC');
			setTimeout(function() {
				$('#weeklyUnAllocation-2').scrollTop(Math.abs($.cookie("unallocDutyBlockPosTop") - 2));
				$('#weeklyUnAllocation-2').scrollLeft(scrollLeftPosTemp);
			}, 500);
            customAlert('There is problem with the data. Underlying data has been modified. Please refresh the page and try again.');
        }
    });
}

function getDayName(dateStr, locale='en-GB'){
    let date = new Date(dateStr);
    return date.toLocaleDateString(locale, { weekday: 'long' });
}

function setAllocDuty(dropTargetInst, dragSrcInst){
    $( "#rowUnqId_" + dragSrcInst.attr('data-unique-id') ).clone().appendTo( "#sourceDataCell" );
    $('#sourceDataCell').attr('current-id',dragSrcInst.attr("data-date")+'_'+dragSrcInst.attr("data-row-counter")+'_'+dragSrcInst.attr("data-cell-counter"));
    $( "#rowUnqId_" + dropTargetInst.getAttribute('data-unique-id') ).clone().appendTo( "#targetDataCell" );
    $('#targetDataCell').attr('current-id', dropTargetInst.getAttribute('data-unique-id'));

    var returnHtmlSrc = '';
    if(dragSrcInst.attr('data-duty-instances') == 1){
        returnHtmlSrc +='<div id="colUnqId_'+dragSrcInst.attr("data-date")+'_'+dragSrcInst.attr("data-cell-counter")+'" class="unallocated unallocother-drag-drop uldutycell_'+dragSrcInst.attr("data-date")+'" data-date="'+dragSrcInst.attr("data-date")+'" data-occupied="0" data-source="unallocated" data-scheduling-person="0" data-row-counter="'+dragSrcInst.attr("data-row-counter")+'" data-cell-counter="'+dragSrcInst.attr("data-cell-counter")+'" data-unique-id="'+dragSrcInst.attr("data-date")+'_'+dragSrcInst.attr("data-row-counter")+'_'+dragSrcInst.attr("data-cell-counter")+'" date-range-days="'+dragSrcInst.attr("date-range-days")+'" date-start-date="'+dragSrcInst.attr('date-start-date')+'" date-end-date="'+dragSrcInst.attr('date-end-date')+'">';
            returnHtmlSrc +='<span> <br></span>';
            returnHtmlSrc +='</div>';
        $('#rowUnqId_'+dragSrcInst.attr('data-unique-id')).html(returnHtmlSrc);
        $('#rowUnqId_'+dragSrcInst.attr('data-unique-id')).removeAttr('ondblclick');
        $('#rowUnqId_'+dragSrcInst.attr('data-unique-id')).attr('date-range-days',dragSrcInst.attr("date-range-days")).attr('date-start-date',dragSrcInst.attr("date-start-date")).attr('date-end-date',dragSrcInst.attr("date-end-date")).attr('data-id','0').attr('data-occupied','0').attr('data-source','unallocated').attr('data-scheduling-person','0').attr('data-unique-id',dragSrcInst.attr("data-date")+'_'+dragSrcInst.attr("data-row-counter")+'_'+dragSrcInst.attr("data-cell-counter")).attr('id','rowUnqId_'+dragSrcInst.attr("data-date")+'_'+dragSrcInst.attr("data-row-counter")+'_'+dragSrcInst.attr("data-cell-counter"));
        $('#rowUnqId_'+dragSrcInst.attr("data-date")+'_'+dragSrcInst.attr("data-row-counter")+'_'+dragSrcInst.attr("data-cell-counter")).css('border','');
        $('#rowUnqId_'+dragSrcInst.attr('data-unique-id')).removeClass('cornerIcon-Unalloc cornerIcon-Grey');
        $('#dutyCountDiv'+dragSrcInst.attr('data-unique-id')).html('');
    } else {
        var remainDutyInst = parseInt(dragSrcInst.attr("data-duty-instances") - 1);
        var dragSrcDutyInstanceIds = dragSrcInst.attr("data-duty-instance-ids");
        var lastInstanceId = dragSrcInst.attr('data-allocations-duty-id');
        var splitInstanceIds = dragSrcDutyInstanceIds.split(',');
        var remainInstanceNextId = 0;
        var remainInstancesIds = [];
        for(let i=0; i<splitInstanceIds.length; i++){
            if(lastInstanceId != splitInstanceIds[i] && !remainInstancesIds.includes(splitInstanceIds[i]))
            {
                remainInstancesIds.push(splitInstanceIds[i]);
            }
        }
        remainInstanceNextId = remainInstancesIds[0];
        if(remainInstancesIds.length > 1){
            remainInstancesIds = remainInstancesIds.join(',');
        }
        let nextUniqueId = remainInstanceNextId + '_' + dragSrcInst.attr("data-date");
        $('#dutyCountDiv'+dragSrcInst.attr('data-unique-id')).attr('id','dutyCountDiv'+nextUniqueId);
        $('#rowUnqId_'+dragSrcInst.attr('data-unique-id')).attr('data-order',dragSrcInst.attr("data-duty-name")).attr('data-unique-id',nextUniqueId).attr('data-duty-count',remainDutyInst).attr('data-duty-instances',remainDutyInst).attr('data-duty-instance-ids',remainInstancesIds).attr('data-allocations-duty-id',remainInstanceNextId).attr('data-remain-duty-instances',remainDutyInst).attr('ondblclick', "editAllocateDuty('editduty','edit', '" + nextUniqueId + "')").attr('id','rowUnqId_' + nextUniqueId);
        $('#colUnqId_'+dragSrcInst.attr('data-unique-id')).attr('data-order',dragSrcInst.attr("data-duty-name")).attr('data-unique-id',nextUniqueId).attr('data-duty-count',remainDutyInst).attr('data-duty-instances',remainDutyInst).attr('data-duty-instance-ids',remainInstancesIds).attr('data-allocations-duty-id',remainInstanceNextId).attr('data-remain-duty-instances',remainDutyInst).attr('id','colUnqId_'+nextUniqueId);

        if(remainDutyInst > 1){
            $('#rowUnqId_'+nextUniqueId).addClass('cornerIcon-Unalloc cornerIcon-Grey');
            $('#dutyCountDiv'+nextUniqueId).html(remainDutyInst);
        } else {
            $('#rowUnqId_'+nextUniqueId).removeClass('cornerIcon-Unalloc cornerIcon-Grey');
            $('#dutyCountDiv'+nextUniqueId).html('');
        }
    }
}

function secondsToTime(secs){
    var hours = parseInt(Math.floor(secs / (60 * 60)));
    if(hours < 10){
        hours = '0'+hours;
    }
    let divisor_for_minutes = secs % (60 * 60);
    var minutes = parseInt(Math.floor(divisor_for_minutes / 60));
    if(minutes < 10){
        minutes = '0'+minutes;
    }
    var secToTimeObj = {
        "HOURS": hours,
        "MINUTES": minutes
    };
    return secToTimeObj;
}

function reloadShiftCountingGrid(){
    if($("#showHideCount").prop('checked') == false){
        $("#showHideCount").prop('checked',true);
        if($('#dutyShiftCountingFilterId').val() != 'NA'){
            $('#showHideCount').trigger( "click" );
        }
    }

    var data = $('#ajaxLoadParams').val();
    data = $.parseJSON(data);
    data.shiftCountingCheckBox = $('#shiftCountingCheckBox').val();
    data.dutyCountFilterCols = $('#dutyCountFilterCols').val();
    data.selShiftCountingFilterId = $('#selShiftCountingFilterId').val();
    data.weekCount = $('#showWeeks').val();
    if($('#date').val() != ''){
        let eDdate = new Date(data.startDate.replace(/-/g, '\/'));
        eDdate.setDate(eDdate.getDate() + 6);
        let offseteDdate = eDdate.getTimezoneOffset();
        eDdate = new Date(eDdate.getTime() - (offseteDdate*60*1000));
        data.endDate=eDdate.toISOString().split('T')[0];
    }
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/view-shiftcounting.php",
        data: data,
        beforeSend: function (jqXHR, settings) {
            $("#loading").hide();
            $("#loadingWeekly").show();
        },
        success: function (response) {
            $('#showCountBlock').html(response);
            $('#showCountRight').scrollLeft($('#weeklyUnAllocation-2').scrollLeft());
            setTimeout(function() {
                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
            }, 1000);
        },
        complete: function(){
            $("#loading").hide();
            $("#loadingWeekly").hide();
        }
    });
}

function getMultiWeekData(weekCounts){
    $('#multiweekcount').val();
	if(weekCounts == 1){
        $('#multiweekcount').val('');
        reloadeditweeklygrid('','','','','ALL');
	} else {
        $('body').css('pointer-events', 'none');
        loadUnallocatedGrid();
        if($('#showHideCount').prop('checked')){
            reloadShiftCountingGrid();
        }
	}
}

function createCellsForNonCreatedWeek(scheduledPeopleCount=0){
    let ruturndataNonCreatedWeek = [];
    if(scheduledPeopleCount > 0){
        for(let j=0; j<scheduledPeopleCount; j++){
            ruturndataNonCreatedWeek[j] = blankRowForDates(7);
        }
    }
    return ruturndataNonCreatedWeek;
}

async function createMultiWeekUnallocatedHtml(res, data, weekCounts, callback)
{
    let weekDays = [0, 1, 2, 3, 4, 5, 6];
    let innerHtml = '';
    let startWeek = data.startWeek;
    let cellFoundFlag = false;
    let unAllocatedMultiWeekHtml = '';
    saveDataInIndexedDb(res, 1);
    var dateRangeDays = parseInt(weekCounts)*7;
    let edate = new Date(data.startDate.replace(/-/g, '\/'));
    edate.setDate(edate.getDate() + (dateRangeDays-1));
    let offsetedate = edate.getTimezoneOffset();
    edate = new Date(edate.getTime() - (offsetedate*60*1000));
    endDate=edate.toISOString().split('T')[0];
    let responseDataUnalloc = IndexToAssociativeArray (res[0],'Unalloc');

    var rowCounter = 1;
    res[1].forEach((number, index, rows) => {//unique duty or possible rows
        unAllocatedMultiWeekHtml += '<div class="weekly-table-row">';
        res[2].forEach((number1, index1, days) => {//for each cell or cols

            cellFoundFlag = false;
            let unalloccell = '<div id="rowUnqId_'+days[index1]+'_'+rowCounter+'_'+index1+'" class="NotFixedon unallocate-empty-' + days[index1] + ' weekly-body-cell ulrow_'+rowCounter+'" onmouseover="removetip()" data-order="U-"><div id="colUnqId_'+days[index1]+'_'+index1+'" class="unallocated unallocother-drag-drop uldutycell_'+days[index1]+'" data-occupied=0 data-source="unallocated" data-date="'+days[index1]+'" data-scheduling-person="0" data-row-counter="'+rowCounter+'" data-cell-counter="'+index1+'" data-unique-id="'+days[index1]+'_'+rowCounter+'_'+index1+'" date-range-days="'+dateRangeDays+'" date-start-date="'+days[0]+'" date-end-date="'+endDate+'"><span> &nbsp;<br></span></div></div>';
            let chkInstance = 1;
            for(var i = 1; i < responseDataUnalloc.length; i++)
            {
                if((responseDataUnalloc[i].DutyDate == days[index1]))
                {
                    if(rows[index] == responseDataUnalloc[i].DutyName)
                    {
                        let st = Number(responseDataUnalloc[i].StartTime);
                        var sth = Math.floor(st / 3600);
                        var stm = Math.floor(st % 3600 / 60);
                        if (sth < 10){
                            sth = "0"+sth;
                        }
                        if (stm < 10){
                            stm = "0"+stm;
                        }

                        let et = Number(responseDataUnalloc[i].EndTime);
                        var eth = Math.floor(et / 3600);
                        var etm = Math.floor(et % 3600 / 60);
                        if (eth < 10){
                            eth = "0"+eth;
                        }
                        if (etm < 10){
                            etm = "0"+etm;
                        }

                        let dutyInstanceCount = 0;
                        let dutyInstIds = responseDataUnalloc[i].InstanceIds;
                        let splitDutyInstance = dutyInstIds.split(',');
                        dutyInstanceCount = splitDutyInstance.length;
                        let dutyInstancIds = '';
                        let finalDutyInstanceIds = '';
                        if(dutyInstanceCount == 1){
                            finalDutyInstanceIds = responseDataUnalloc[i].InstanceIds;
                        } else {
                            let nowDutyInstanceIds = '';
                            for(let g=0; g<dutyInstanceCount; g++){
                                if(splitDutyInstance[g] != responseDataUnalloc[i].ID && splitDutyInstance[g] != undefined){
                                    nowDutyInstanceIds +=splitDutyInstance[g]+',';
                                }
                            }
                            finalDutyInstanceIds = responseDataUnalloc[i].ID+','+nowDutyInstanceIds.slice(0, -1);
                        }
                        let ondblclickCondUA = ' ondblclick="editAllocateDuty(\'editduty\',\'edit\', ' + responseDataUnalloc[i].uId + ')" ';
						let draggableFlag = (responseDataUnalloc[i].DutyName == 'U') ? 'false' : 'true';
                        unalloccell = '<div ' + ondblclickCondUA + ' id="rowUnqId_'+responseDataUnalloc[i].ID+'" class="NotFixedon weekly-body-cell context-menu-unallocated ulrow_'+rowCounter+'" title="'+responseDataUnalloc[i].DutyName+'" onmouseover="removetip()" data-date="'+days[index1]+'" data-source="unallocated" data-id="'+responseDataUnalloc[i].ID+'" data-duty-count="'+responseDataUnalloc[i].DutyInstances+'" data-duty-name="'+responseDataUnalloc[i].DutyName+'" data-scheduling-person="'+responseDataUnalloc[i].SchedulingPersonID+'" data-unique-id="'+responseDataUnalloc[i].ID+'" data-scheduling-team-id="'+responseDataUnalloc[i].SchedulingTeamId+'" data-row-start="'+responseDataUnalloc[i].StartTime+'" data-row-end="'+responseDataUnalloc[i].EndTime+'" data-duty-instances="'+responseDataUnalloc[i].DutyInstances+'" data-row-counter="'+rowCounter+'" data-duty-instance-ids="'+finalDutyInstanceIds+'" data-next-instance-id="'+responseDataUnalloc[i].ID+'" data-remain-duty-instances="0" date-range-days="'+dateRangeDays+'" date-start-date="'+days[0]+'" date-end-date="'+endDate+'" >       <div id="colUnqId_'+responseDataUnalloc[i].ID+'" class="unallocated dragAlloc unalloc-drag-drop uldutycell_'+days[index1]+'" draggable="'+draggableFlag+'" data-date="'+days[index1]+'" data-occupied=1 data-source="unallocated" data-id="'+responseDataUnalloc[i].ID+'" data-duty-count="'+responseDataUnalloc[i].DutyInstances+'" data-duty-name="'+responseDataUnalloc[i].DutyName+'" data-scheduling-person="'+responseDataUnalloc[i].SchedulingPersonID+'" data-unique-id="'+responseDataUnalloc[i].ID+'" data-scheduling-team-id="'+responseDataUnalloc[i].SchedulingTeamId+'" data-row-start="'+responseDataUnalloc[i].StartTime+'" data-row-end="'+responseDataUnalloc[i].EndTime+'" data-duty-instances="'+responseDataUnalloc[i].DutyInstances+'" data-row-counter="'+rowCounter+'"  data-cell-counter="'+index1+'"  data-duty-instance-ids="'+finalDutyInstanceIds+'" data-next-instance-id="'+responseDataUnalloc[i].ID+'" data-remain-duty-instances="0" date-range-days="'+dateRangeDays+'" date-start-date="'+days[0]+'" date-end-date="'+endDate+'" ><span class="dutyTip" style="overflow:hidden;"> <b>'+responseDataUnalloc[i].DutyName+ ' </b><br/>';
                        if((sth+':'+stm != '00:00') || (eth+':'+etm != '00:00')){
                            unalloccell +=sth+':'+stm+' - '+eth+':'+etm;
                        }
                        unalloccell +='</span></div></div>';
                        break;
                    }
                }
            }
            unAllocatedMultiWeekHtml += unalloccell;

        });
        unAllocatedMultiWeekHtml += '</div>';
        rowCounter = parseInt(rowCounter+200);
    });
    $('#weeklyUnAllocation-2').css('overflow-x', 'scroll');
    if (typeof callback === 'function') {
        callback([res[3], res[4], unAllocatedMultiWeekHtml, res[5]]);
    }
}

function setSwapAllocDuty(dropTargetInst, dragSrcInst){
    $( "#rowUnqId_"+dragSrcInst.attr('data-unique-id') ).clone().appendTo( "#sourceDataCell" );
    $('#sourceDataCell').attr('current-id',dragSrcInst.attr('data-unique-id'));
    $( "#rowUnqId_"+dropTargetInst.getAttribute('data-unique-id') ).clone().appendTo( "#targetDataCell" );
    $('#targetDataCell').attr('current-id',dropTargetInst.getAttribute('data-unique-id'));

    var returnHtml = '';
    var dragDutyStTime = secondsToTime(dragSrcInst.attr("data-row-start"));
    var dragDutyEdTime = secondsToTime(dragSrcInst.attr("data-row-end"));
    returnHtml +='<div id="colUnqId_'+dragSrcInst.attr("data-unique-id")+'" class="item-cell dragClass_'+dropTargetInst.getAttribute("data-date")+'_'+dropTargetInst.getAttribute("data-scheduling-person")+' alloc  dragAlloc dutydroppable dropeventscall" ';
    returnHtml +='data-date="'+dropTargetInst.getAttribute("data-date")+'" ';
    returnHtml +='data-unique-id="'+dragSrcInst.attr("data-unique-id")+'" ';
    returnHtml +='data-allocations-duty-id="'+dropTargetInst.getAttribute("data-allocations-duty-id")+'" ';
    returnHtml +='data-allocations-sp-id="'+dragSrcInst.attr("data-allocations-sp-id")+'" ';
    returnHtml +='data-id="'+dropTargetInst.getAttribute("data-id")+'" ';
    returnHtml +=(dragSrcInst.attr("data-duty-name") == 'U') ? 'draggable="false" ' : 'draggable="true" ';
    returnHtml +='ondrop="dropAlloc(event,this);">';
    if(dragSrcInst.attr("data-duty-name") == 'U'){
        returnHtml +='<span id="dutytipID_'+dropTargetInst.getAttribute("data-date")+'_'+dropTargetInst.getAttribute("data-scheduling-person")+'" class="dutyTip" style="overflow:hidden;"> '+dragSrcInst.attr("data-duty-name")+'<br><br></span>';
    } else {
        returnHtml +='<span id="dutytipID_'+dropTargetInst.getAttribute("data-date")+'_'+dropTargetInst.getAttribute("data-scheduling-person")+'" class="dutyTip" style="overflow:hidden;"> <b>'+dragSrcInst.attr("data-duty-name")+'</b></span>';
    }
    returnHtml +='<br> ';
    if(dragSrcInst.attr("data-row-start") != 0 && dragSrcInst.attr("data-row-end") != 0){
        returnHtml +=dragDutyStTime.HOURS+':'+dragDutyStTime.MINUTES+' - '+dragDutyEdTime.HOURS+':'+dragDutyEdTime.MINUTES;
    }
    returnHtml +='</div>';
    $('#rowUnqId_'+dragSrcInst.attr("data-unique-id")).html(returnHtml);

    var returnHtml2 = '';
    var dragDutyStTime2 = secondsToTime(dropTargetInst.getAttribute("data-row-start"));
    var dragDutyEdTime2 = secondsToTime(dropTargetInst.getAttribute("data-row-end"));
    if(dropTargetInst.getAttribute("data-duty-name") == 'U'){
        returnHtml2 +='<div id="colUnqId_'+dragSrcInst.attr("data-unique-id")+'" class="item-cell dragClass_'+dragSrcInst.attr("data-date")+'_'+dragSrcInst.attr("data-scheduling-person")+' alloc  dutydroppable dropeventscall" ';
        returnHtml2 +='draggable="false" ';
    } else {
        returnHtml2 +='<div id="colUnqId_'+dropTargetInst.getAttribute("data-unique-id")+'" class="item-cell dragClass_'+dragSrcInst.attr("data-date")+'_'+dragSrcInst.attr("data-scheduling-person")+' alloc  dragAlloc dutydroppable dropeventscall" ';
        returnHtml2 +='draggable="true" ';
    }
	returnHtml2 +='data-date="'+dragSrcInst.attr("data-date")+'" ';
    returnHtml2 +='data-unique-id="'+dropTargetInst.getAttribute("data-unique-id")+'" ';
    returnHtml2 +='data-allocations-duty-id="'+dragSrcInst.attr("data-allocations-duty-id")+'" ';
    returnHtml2 +='data-allocations-sp-id="'+dropTargetInst.getAttribute("data-allocations-sp-id")+'" ';
    returnHtml2 +='data-id="'+dragSrcInst.attr("data-id")+'" ';
    returnHtml2 +='ondrop="dropAlloc(event,this);">';
    if(dropTargetInst.getAttribute("data-duty-name") == 'U'){
        returnHtml2 +='<span id="dutytipID_'+dropTargetInst.getAttribute("data-date")+'_'+dropTargetInst.getAttribute("data-scheduling-person")+'" class="dutyTip" style="overflow:hidden;"> '+dropTargetInst.getAttribute("data-duty-name")+'<br><br></span>';
    } else {
        returnHtml2 +='<span id="dutytipID_'+dropTargetInst.getAttribute("data-date")+'_'+dropTargetInst.getAttribute("data-scheduling-person")+'" class="dutyTip" style="overflow:hidden;"> <b>'+dropTargetInst.getAttribute("data-duty-name")+'</b></span>';
    }
    returnHtml2 +='<br> ';
    if(dropTargetInst.getAttribute("data-row-start") != 0 && dropTargetInst.getAttribute("data-row-end") != 0){
        returnHtml2 +=dragDutyStTime2.HOURS+':'+dragDutyStTime2.MINUTES+' - '+dragDutyEdTime2.HOURS+':'+dragDutyEdTime2.MINUTES;
    }
    returnHtml2 +='</div>';
    $('#rowUnqId_'+dropTargetInst.getAttribute("data-unique-id")).html(returnHtml2);
}

function disableDragDrop(){
    if($('#editScreen').val() == 1){
        if($('#disableDrag').prop('checked') == true){
            $.cookie('dragdisable'+$('#teamId').val(), 'Yes');
            $('.misc-drag-drop').removeClass('dragAlloc');
            $('.misc-drag-drop').attr('draggable','false');

            $('.unalloc-drag-drop').removeClass('dragAlloc').removeClass('dutydroppable').removeClass('dropeventscall');
            $('.unalloc-drag-drop').attr('draggable','false');

            $('.unallocother-drag-drop').removeClass('dutydroppable').removeClass('dropeventscall');

            $('.alloc-drag-drop').removeClass('dragAlloc').removeClass('dutydroppable').removeClass('dropeventscall');
            $('.alloc-drag-drop').attr('draggable','false');
        } else {
            $.removeCookie('dragdisable'+$('#teamId').val());
            $('.misc-drag-drop').addClass('dragAlloc');
            $('.misc-drag-drop').attr('draggable','true');

            $('.unalloc-drag-drop').addClass('dragAlloc').addClass('dutydroppable').addClass('dropeventscall');
            $('.unalloc-drag-drop').attr('draggable','true');

            $('.unallocother-drag-drop').addClass('dutydroppable').addClass('dropeventscall');

            $('.alloc-drag-drop').addClass('dragAlloc').addClass('dutydroppable').addClass('dropeventscall');
            $('.alloc-drag-drop').attr('draggable','true');
        }
    } else {
        $('#disableDrag').prop('checked',false);
    }
}

function teamUpdate(teamId=0, userId=0){
    // $('#loading').show();
    $.ajax({
        type: "post",
        url: "/components/filters/filter-process.php",
        data: {
            'action':'checkselectedfilter',
            'teamId':teamId
        },
        beforeSend: function (jqXHR, settings){
            $('#loading').hide();
        },
        success: function (response) {
            let returnData = $.parseJSON(response);
            let argDataInitialLoad = {};
            argDataInitialLoad.schedulingTeamId = teamId;
            argDataInitialLoad.userId = userId;
            argDataInitialLoad.weekNumber = $('#weekNumber').val();
            argDataInitialLoad.teamId = teamId;
            if(returnData.status == true){
                argDataInitialLoad.selAutoPageFilterId = returnData.selectedFilterId;
                argDataInitialLoad.shiftCountingFilterId = returnData.selectedShiftCountFilterId;
            } else {
                argDataInitialLoad.selAutoPageFilterId = 0;
                argDataInitialLoad.shiftCountingFilterId = 0;
            }
            editWeeklyPageLoad('No',argDataInitialLoad);
        }
    });
}

function updateUnAllocDutyJsonArray(pDutyName='', pDutydate='', pDragType='', dropTargetInst='', dragSrcInst='', pDutyStartTime = '', pDutyEndTime = ''){
    if(pDutyName!='' && pDutydate!='' && pDragType!=''){
        if(getAssociativeArraySize($.parseJSON($('#unAllocDutyData').val())) > 0){
            var unAllocExistAllDuty = $.parseJSON($('#unAllocDutyData').val());
        } else {
            var unAllocExistAllDuty = {};
        }

        switch(pDragType.toUpperCase()){
            case "UNALLOCTOALLOC":
                if(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate] !== undefined){
                    if(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate].DutyInstances == '1'){
                        delete unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate];
                    } else {
                        let nowInstVal = parseInt(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate].DutyInstances)-1;
                        unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate].DutyInstances = nowInstVal.toString();
                    }
                    if((dropTargetInst.getAttribute('data-duty-name') != 'U') && (dropTargetInst.getAttribute('data-row-end') != '0') && (dropTargetInst.getAttribute('data-is-need-covering') == '')){
                        var dropDutyName = dropTargetInst.getAttribute('data-duty-name');
                        var dropDutyDate = dropTargetInst.getAttribute('data-date');
                        var dropDutyStartTime = dropTargetInst.getAttribute('data-row-start');
                        var dropDutyEndTime = dropTargetInst.getAttribute('data-row-end');
                        if(unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime] !== undefined){
                            if(unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate] !== undefined){
                                let nowInstVal1 = parseInt(unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate].DutyInstances)+1;
                                unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate].DutyInstances = nowInstVal1.toString();
                            } else {
                                unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate] = {
                                    DutyInstances : '1'
                                };
                            }
                        } else {
                            unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime] = {
                                [dropDutyDate]:{
                                    DutyInstances : '1'
                                }
                            };
                        }
                    }
                } else {
                    if((dropTargetInst.getAttribute('data-duty-name') != 'U') && (dropTargetInst.getAttribute('data-row-end') != '0') && (dropTargetInst.getAttribute('data-is-need-covering') == '')){
                        var dropDutyName = dropTargetInst.getAttribute('data-duty-name');
                        var dropDutyDate = dropTargetInst.getAttribute('data-date');
                        var dropDutyStartTime = dropTargetInst.getAttribute('data-row-start');
                        var dropDutyEndTime = dropTargetInst.getAttribute('data-row-end');
                        if(unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime] !== undefined){
                            if(unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate] !== undefined){
                                let nowInstVal1 = parseInt(unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate].DutyInstances)+1;
                                unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate].DutyInstances = nowInstVal1.toString();
                            } else {
                                unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate] = {
                                    DutyInstances : '1'
                                };
                            }
                        } else {
                            unAllocExistAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime] = {
                                [dropDutyDate]:{
                                    DutyInstances : '1'
                                }
                            };
                        }
                    }
                }
                break;
            case "ALLOCTOUNALLOC":
                if(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime] !== undefined){
                    if(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate] !== undefined){
                        let nowInstVal = parseInt(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate].DutyInstances)+1;
                        unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate].DutyInstances = nowInstVal.toString();
                    } else {
                        unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate] = {
                            DutyInstances : '1'
                        };
                    }
                } else {
                    unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime] = {
                        [pDutydate]:{
                            DutyInstances : '1'
                        }
                    };
                }
                break;
            case "MISC":
                pDutyName = dropTargetInst.getAttribute('data-duty-name');
                pDutydate = dropTargetInst.getAttribute('data-date');
                pDutyStartTime = dropTargetInst.getAttribute('data-row-start');
                pDutyEndTime = dropTargetInst.getAttribute('data-row-end');
                if(pDutyName != 'U'){
                    if(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime] !== undefined){
                        if(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate] !== undefined){
                            let nowInstVal = parseInt(unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate].DutyInstances)+1;
                            unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate].DutyInstances = nowInstVal.toString();
                        } else {
                            unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime][pDutydate] = {
                                DutyInstances : '1'
                            };
                        }
                    } else {
                        unAllocExistAllDuty[pDutyName + pDutyStartTime + pDutyEndTime] = {
                            [pDutydate]:{
                                DutyInstances : '1'
                            }
                        };
                    }
                }
                break;
        }
        return unAllocExistAllDuty;
    }
}

function updateUnallocGrid(dragSrc, dropTrg, newId, dragType, updUnallocDutyData, pDataRowCounter='', dutyExists='Yes',dataSourceId=0){
    if(getAssociativeArraySize($.parseJSON($('#unAllocDutyData').val())) > 0){
        var unAllocAllDuty = $.parseJSON($('#unAllocDutyData').val());
    } else {
        var unAllocAllDuty = {};
    }
    var dragDutyDate = dragSrc.attr("data-date");
    var dragDutyName = dragSrc.attr("data-duty-name");
    var dragDutyStartTime = dragSrc.attr("data-row-start");
    var dragDutyEndTime = dragSrc.attr("data-row-end");
    switch(dragType.toUpperCase()){
        case "UNALLOCTOALLOC":
            if(unAllocAllDuty[dragDutyName + dragDutyStartTime + dragDutyEndTime][dragDutyDate] !== undefined){
                if(unAllocAllDuty[dragDutyName + dragDutyStartTime + dragDutyEndTime][dragDutyDate].DutyInstances == '1'){
                    var sortedDutyDate = sortKeys(updUnallocDutyData[dragDutyName + dragDutyStartTime + dragDutyEndTime]).length;
                    if(sortedDutyDate == 0){
                        delete updUnallocDutyData[dragDutyName + dragDutyStartTime + dragDutyEndTime];
                        $('.ulrow_'+pDataRowCounter).parent().remove();
                    }
                }
                if(dropTrg.getAttribute('data-duty-name') != 'U'){
                    var dropDutyName = dropTrg.getAttribute('data-duty-name');
                    var dropDutyDate = dropTrg.getAttribute('data-date');
                    var dropDutyStartTime = dropTrg.getAttribute('data-row-start');
                    var dropDutyEndTime = dropTrg.getAttribute('data-row-end');
                    if(dataSourceId != 0){//removed from if condition//newId == undefined && 
                        newId = dataSourceId;
                    } else {
                        newId = dragSrc.attr("data-unique-id").split('_')[0];
                    }
                    if(unAllocAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime] !== undefined){
                        if(unAllocAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate] !== undefined){
                            if((dragSrc.attr("data-duty-name") + dragDutyStartTime + dragDutyEndTime) == (dropDutyName + dropDutyStartTime + dropDutyEndTime)){
                                if((dragSrc.attr('data-row-start') == dropTrg.getAttribute("data-row-start")) && (dragSrc.attr('data-row-end') != dropTrg.getAttribute("data-row-end"))){
                                    updateUnallocDutyCell(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                                } else if((dragSrc.attr('data-row-start') != dropTrg.getAttribute("data-row-start")) && (dragSrc.attr('data-row-end') == dropTrg.getAttribute("data-row-end"))){
                                    updateUnallocDutyCell(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                                } else if((dragSrc.attr('data-row-start') != dropTrg.getAttribute("data-row-start")) && (dragSrc.attr('data-row-end') != dropTrg.getAttribute("data-row-end"))){
                                    updateUnallocDutyCell(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                                }
                            } else {
                                updateUnallocDutyInstance(dragSrc, dropTrg, dragDutyName, dragDutyDate, newId, dragType,dutyExists, dragDutyStartTime, dragDutyEndTime);
                            }
                        } else {
                            updateUnallocDutyCell(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                        }
                    } else {
                        updateUnallocDutyRow(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                    }
                }
            } else {
                if(dropTrg.getAttribute('data-duty-name') != 'U'){
                    var dropDutyName = dropTrg.getAttribute('data-duty-name');
                    var dropDutyDate = dropTrg.getAttribute('data-date');
                    var dropDutyStartTime = dropTrg.getAttribute('data-row-start');
                    var dropDutyEndTime = dropTrg.getAttribute('data-row-end');
                    if(newId == undefined && dataSourceId != 0){
                        newId = dataSourceId;
                    } else {
                        newId = dragSrc.attr("data-unique-id").split('_')[0];
                    }
                    if(unAllocAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime] !== undefined){
                        if(unAllocAllDuty[dropDutyName + dropDutyStartTime + dropDutyEndTime][dropDutyDate] !== undefined){
                            updateUnallocDutyInstance(dragSrc, dropTrg, dragDutyName, dragDutyDate, newId, dragType,dutyExists, dragDutyStartTime, dragDutyEndTime);
                        } else {
                            updateUnallocDutyCell(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                        }
                    } else {
                        updateUnallocDutyRow(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                    }
                }
            }
            break;
        case "ALLOCTOUNALLOC":
            if(unAllocAllDuty[dragDutyName + dragDutyStartTime + dragDutyEndTime] !== undefined){
                if(unAllocAllDuty[dragDutyName + dragDutyStartTime + dragDutyEndTime][dragDutyDate] !== undefined){
                    updateUnallocDutyInstance(dragSrc, dropTrg, dragDutyName, dragDutyDate, newId, dragType,dutyExists, dragDutyStartTime, dragDutyEndTime);
                } else {
                    updateUnallocDutyCell(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                }
            } else {
                updateUnallocDutyRow(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
            }
            break;
        case "MISC":
            dragDutyDate = dropTrg.getAttribute('data-date');
            dragDutyName = dropTrg.getAttribute("data-duty-name");
            dragDutyStartTime = dropTrg.getAttribute("data-row-start");
            dragDutyEndTime = dropTrg.getAttribute("data-row-end");
            if(dragDutyName != 'U'){
                if(unAllocAllDuty[dragDutyName + dragDutyStartTime + dragDutyEndTime] !== undefined){
                    if(unAllocAllDuty[dragDutyName + dragDutyStartTime + dragDutyEndTime][dragDutyDate] !== undefined){
                        updateUnallocDutyInstance(dragSrc, dropTrg, dragDutyName, dragDutyDate, newId, dragType, dutyExists, dragDutyStartTime, dragDutyEndTime);
                    } else {
                        updateUnallocDutyCell(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                    }
                } else {
                    updateUnallocDutyRow(dragSrc, dropTrg, dragType, newId, updUnallocDutyData,dutyExists);
                }
            }
            break;
    }
    $('#unAllocDutyData').val(JSON.stringify(updUnallocDutyData));
}

function sortKeys(obj){
    var keys = [];
    for(var key in obj){
        if(obj.hasOwnProperty(key)){
            let dtName = key.toLowerCase();
            keys.push(dtName);
        }
    }
    return keys;
}

function updateUnallocDutyRow(dragSrcInst, dropTargetInst, dragType, newIdVal, updUnallocDutyData, dutyExists='Yes'){
    if((dragType == 'MISC') || (dragType == 'UNALLOCTOALLOC' && dropTargetInst.getAttribute('data-duty-name') != 'U')){
        var datadutyname = dropTargetInst.getAttribute('data-duty-name');
        var datestartdate = dropTargetInst.getAttribute('date-start-date');
        var daterangedays = dropTargetInst.getAttribute('date-range-days');
        var datadate = dropTargetInst.getAttribute('data-date');
        var datarowstart = dropTargetInst.getAttribute('data-row-start');
        var datarowend = dropTargetInst.getAttribute('data-row-end');
        var dataschedulingteamid = dropTargetInst.getAttribute('data-scheduling-team-id');
        var dateenddate = dropTargetInst.getAttribute('date-end-date');
        var dataduration = dropTargetInst.getAttribute('data-duty-duration');
        var dataiday = dropTargetInst.getAttribute('data-iday');
        var dataId = dropTargetInst.getAttribute('data-id');
        var dataIsNeedCovering = dropTargetInst.getAttribute('data-is-need-covering');
    } else {
        var datadutyname = dragSrcInst.attr('data-duty-name');
        var datestartdate = dragSrcInst.attr('date-start-date');
        var daterangedays = dragSrcInst.attr('date-range-days');
        var datadate = dragSrcInst.attr('data-date');
        var datarowstart = dragSrcInst.attr('data-row-start');
        var datarowend = dragSrcInst.attr('data-row-end');
        var dataschedulingteamid = dragSrcInst.attr('data-scheduling-team-id');
        var dateenddate = dragSrcInst.attr('date-end-date');
        var dataduration = dragSrcInst.attr('data-duty-duration');
        var dataiday = dragSrcInst.attr('data-iday');
        var dataId = dragSrcInst.attr('data-id');
        var dataIsNeedCovering = dragSrcInst.attr('data-is-need-covering');
    }
    var sortedDutyNames = sortKeys(updUnallocDutyData).sort();
    var counter = 0;
    $.each(sortedDutyNames, function (key, value) {
        if((datadutyname + datarowstart + datarowend).toLowerCase() == value){
            counter = (key+1);
            return false;
        }
    });

    let newRowCounterVal = getRowCounterUnallocated(counter);

    let dString = datestartdate;
    let rangedays = parseInt(daterangedays);
    let [year, month, day] = dString.split('-');
    let now = new Date(year, month - 1, day);
    let loopDay = now;
    var repDiv = '';

    let rowPosition = parseInt(counter-1);
	if(dataIsNeedCovering == '')
	{
		repDiv +='<div class="weekly-table-row">';
		for (let i = 0; i < rangedays; i++) {
			let date = new Date(datestartdate.replace(/-/g, '\/'));
			date.setDate(date.getDate() +  i);
			let offsetdate = date.getTimezoneOffset();
			date = new Date(date.getTime() - (offsetdate*60*1000));
			let loopDate = date.toISOString().split('T')[0];
			if(loopDate == datadate){
				var dragDutyStrtTime = secondsToTime(datarowstart);
				var dragDutyEndTime = secondsToTime(datarowend);
				let dutyHrs = parseFloat(dataduration/3600).toFixed(2);

				let dataEditScreen = 0;
				if($('#editScreen').val() == 1){
					dataEditScreen = 1;
				}
				let draggableFlag = (datadutyname == 'U') ? 'false' : 'true';
				let ondblclickCondUA = ' ondblclick="editAllocateDuty(\'editduty\',\'edit\', \'' + newIdVal + '_' + datadate + '\')" ';
				repDiv +='<div ' + ondblclickCondUA + ' id="rowUnqId_'+ newIdVal + '_' + datadate +'" class="NotFixedon weekly-body-cell context-menu-unallocated ulrow_'+newRowCounterVal+' unalloc-cell" title="'+datadutyname+' ('+dutyHrs+' Hours'+')" onmouseover="removetip()" data-date="'+datadate+'" data-source="unallocated" data-id="'+dataId+'" data-duty-count="1" data-duty-name="'+datadutyname+'" data-scheduling-person="0" data-unique-id="'+newIdVal + '_' + datadate +'" data-scheduling-team-id="'+dataschedulingteamid+'" data-row-start="'+datarowstart+'" data-row-end="'+datarowend+'" data-duty-instances="1" data-row-counter="'+newRowCounterVal+'" data-cell-counter="'+i+'" data-duty-instance-ids="'+newIdVal+'" data-next-instance-id="0" data-remain-duty-instances="0" date-range-days="'+daterangedays+'" date-start-date="'+datestartdate+'" date-end-date="'+dateenddate+'">';
					repDiv +='<div id="colUnqId_'+newIdVal + '_' + datadate +'" class="unallocated dragAlloc unalloc-drag-drop uldutycell_'+datadate+' unalloc-cell-inner" draggable="'+draggableFlag+'" data-date="'+datadate+'" data-occupied="1" data-source="unallocated" data-id="'+dataId+'" data-duty-name="'+datadutyname+'" data-duty-count="1" data-scheduling-person="0" data-unique-id="'+newIdVal + '_' + datadate +'" data-scheduling-team-id="'+dataschedulingteamid+'" data-row-start="'+datarowstart+'" data-row-end="'+datarowend+'" data-duty-instances="1" data-row-counter="'+newRowCounterVal+'" data-cell-counter="'+i+'" data-duty-instance-ids="'+newIdVal+'" data-next-instance-id="0" data-remain-duty-instances="0" date-range-days="'+daterangedays+'" date-start-date="'+datestartdate+'" date-end-date="'+dateenddate+'" data-duty-duration="'+dataduration+'" data-iday="'+dataiday+'" data-edit-screen="'+dataEditScreen+'" data-allocations-duty-id="' + newIdVal + '">';
							repDiv +='<span class="dutyTip" style="overflow:hidden;"> <b>'+datadutyname+'</b><br>';
							if(dragDutyStrtTime.HOURS+':'+dragDutyStrtTime.MINUTES+' - '+dragDutyEndTime.HOURS+':'+dragDutyEndTime.MINUTES != '00:00 - 00:00'){
								if(dragDutyEndTime.HOURS+':'+dragDutyEndTime.MINUTES == '24:00'){
									repDiv +=dragDutyStrtTime.HOURS+':'+dragDutyStrtTime.MINUTES+' - 00:00';
								} else {
									repDiv +=dragDutyStrtTime.HOURS+':'+dragDutyStrtTime.MINUTES+' - '+dragDutyEndTime.HOURS+':'+dragDutyEndTime.MINUTES;
								}
							}
							repDiv +='</span>';
					repDiv +='</div>';
					repDiv += '<div class="DutyCountPositionRightTop" id="dutyCountDiv'+newIdVal + '_' + datadate +'"></div>';
				repDiv +='</div>';
			} else {
				let m = i;
				repDiv += buildEmptyCell(loopDate, newRowCounterVal, m, daterangedays, datestartdate, dateenddate);
			}
		}
		repDiv +='</div>';
	}


    if(((dutyExists == 'Yes') && ($('#mastMiscFilterId').val() != '')) || ((dutyExists == 'No') && ($('#mastMiscFilterId').val() == '')) || ((dutyExists == '') && ($('#mastMiscFilterId').val() == ''))){
        if(datadutyname[0] == '-'){
            rowPosition = sortedDutyNames.indexOf(datadutyname + datarowstart + datarowend);
            rowPosition = rowPosition - 1;
            if(rowPosition < 0){
                rowPosition = 0;
                $('#weeklyAllocationTable .weekly-table-body').children(':eq('+(rowPosition)+')').before(repDiv);
            } else {
                $('#weeklyAllocationTable .weekly-table-body').children(':eq('+(rowPosition)+')').after(repDiv);
            }
        } else {
            if(sortedDutyNames.length == 1 && dataIsNeedCovering == ''){
                $('#weeklyAllocationTable .weekly-table-body').html(repDiv);
            } else {
                if(rowPosition == 0){
                    $('#weeklyAllocationTable .weekly-table-body').children(':first').before(repDiv);
                } else {
                    if(counter < sortedDutyNames.length){
                        $('#weeklyAllocationTable .weekly-table-body').children(':eq('+(rowPosition-1)+')').after(repDiv);
                    } else {
                        $('#weeklyAllocationTable .weekly-table-body').children(':last').after(repDiv);
                    }
                }
            }
        }

        if(checkUnallocatedSectionHeight()){
            setTimeout(function() {
                if(!isNaN($.cookie('unAllocSortDate'))){
                    // do nothing
                } else {
                    if(sortKeys(updUnallocDutyData).sort().length == counter){
						setTimeout(() => {
						   let el = $('#rowUnqId_'+newIdVal + '_' + datadate)[0];
						   if(el)
						   {
							   el.scrollIntoView({block: "end"});
						   }
						}, 1000);
                    } else {
						let el = $('#rowUnqId_'+newIdVal + '_' + datadate)[0];
						if(el)
						{
							el.scrollIntoView({block: "start"});
						}
                        if($('#showWeeks').val() > 1){
                            $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                        } else {
                            if($('#date').val() == ''){
                                $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 30);
                            } else {
                                $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                            }
                        }
                        if(parseInt($('#setTop').val()) == 1){
                            $('#unallocatedDuty').css('top','4px');
                        }
                        $('#setTop').val('1');
                    }
                    if(parseInt($('#setTop').val()) == 1){
                        //For Now Keep this code as it is in commented state
                        //$('#unallocatedDuty').css('top','4px');
                    }
                    $('#setTop').val('1');
                }
                $( '#rowUnqId_'+newIdVal ).css('border-bottom','2px solid #bbb');
                $('#dragDropId1').html(newIdVal);
            }, 1000);
        }
    }
}

function buildEmptyCell(loopDate, newRowCounterVal, m, daterangedays, datestartdate, dateenddate) {
    let repDiv = '';
    repDiv +='<div id="rowUnqId_'+loopDate+'_'+newRowCounterVal+'_'+m+'" class="NotFixedon weekly-body-cell unallocate-empty-' + loopDate + ' ulrow_'+newRowCounterVal+' unalloc-cell" data-order="U-">';
        repDiv +='<div id="colUnqId_'+loopDate+'_'+m+'" class="unallocated unallocother-drag-drop uldutycell_'+loopDate+' unalloc-cell-inner" onmouseover="removetip()" data-occupied="0" data-source="unallocated" data-date="'+loopDate+'" data-scheduling-person="0" data-row-counter="'+newRowCounterVal+'" data-cell-counter="'+m+'" data-unique-id="'+loopDate+'_'+newRowCounterVal+'_'+m+'" date-range-days="'+daterangedays+'" date-start-date="'+datestartdate+'" date-end-date="'+dateenddate+'">';
            repDiv +='<span> &nbsp;<br></span> ';
        repDiv +='</div>';
    repDiv +='</div>';
    return repDiv;
}

function updateUnallocDutyInstance(dragSrcInst, dropTargetInst, dragDutyName, dragDutyDate, newId, dragType, dutyExists='Yes', dragDutyStartTime = '', dragDutyEndTime = ''){
    if(dragType == 'UNALLOCTOALLOC'){
        if(dropTargetInst.getAttribute('data-duty-name') != 'U'){
            dragDutyName = dropTargetInst.getAttribute('data-duty-name');
            dragDutyDate = dropTargetInst.getAttribute('data-date');
            dragDutyStartTime = dropTargetInst.getAttribute('data-row-start');
            dragDutyEndTime = dropTargetInst.getAttribute('data-row-end');
        }

        $('.uldutycell_'+dragDutyDate).each(function(key, val) {
            if(($(this).attr('data-duty-name') + $(this).attr('data-row-start') + $(this).attr('data-row-end')) == (dragDutyName + dragDutyStartTime + dragDutyEndTime) && dropTargetInst.getAttribute('data-is-need-covering') == ''){
                let currInstIds = $(this).attr('data-duty-instance-ids');
                $(this).attr('data-duty-instance-ids',currInstIds+','+newId);
                let currDutyInst = $(this).attr('data-duty-instances');
                $(this).attr('data-duty-instances',parseInt(currDutyInst)+1);
                $(this).parent().attr('data-duty-instances',parseInt(currDutyInst)+1);
                $(this).parent().attr('data-duty-instance-ids',currInstIds+','+newId);
                $(this).parent().attr('ondblclick', "editAllocateDuty('editduty','edit', " + $(this).attr('data-unique-id') + ")");
                if((dutyExists == 'Yes' && $('#mastMiscFilterId').val() != '') || (dutyExists == 'No' && $('#mastMiscFilterId').val() == '') || (dutyExists == '' && $('#mastMiscFilterId').val() == '')){
                    if(checkUnallocatedSectionHeight()){
                        $('#rowUnqId_'+$(this).attr('data-unique-id'))[0].scrollIntoView({block: "start"});
                        if($('#showWeeks').val() > 1){
                            $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                        } else {
                            if($('#date').val() == ''){
                                $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 30);
                            } else {
                                $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                            }
                        }
                        $( '#rowUnqId_'+$(this).attr('data-unique-id') ).css('border-bottom','2px solid #bbb');
                        $('#dragDropId1').html($(this).parent().attr('data-unique-id'));
                    }
                }
                let dutyInstances = $(this).attr('data-duty-instance-ids');
                let dutyInstanceCount = dutyInstances.split(',').length;
                if(dutyInstanceCount > 1){
                    $('#rowUnqId_'+$(this).attr('data-unique-id')).addClass('cornerIcon-Unalloc cornerIcon-Grey');
                    $('#dutyCountDiv'+$(this).attr('data-unique-id')).html(dutyInstanceCount);
                } else {
                    $('#rowUnqId_'+$(this).attr('data-unique-id')).removeClass('cornerIcon-Unalloc cornerIcon-Grey');
                    $('#dutyCountDiv'+$(this).attr('data-unique-id')).html('');
                }
                return false;
            }
        });
    } else {
        $('.uldutycell_'+dragDutyDate).each(function(key, val) {
            if(($(this).attr('data-duty-name') + $(this).attr('data-row-start') + $(this).attr('data-row-end')) == (dragDutyName  + dragDutyStartTime + dragDutyEndTime) && dragSrcInst.attr('data-is-need-covering') != 'doesntNeedCoveringIcon'){
                let instancesIds = ($(this).attr('data-duty-instance-ids') + ',' + newId).split(',');
                var currInstIds = [];
                for(let i=0; i<instancesIds.length; i++){ // Remove duplicates
                    if(!currInstIds.includes(instancesIds[i]))
                    {
                        currInstIds.push(instancesIds[i]);
                    }
                }
                currInstIds = currInstIds.join(',');
                $(this).attr('data-duty-instance-ids',currInstIds);
                let currDutyInst = $(this).attr('data-duty-instances');
                $(this).attr('data-duty-instances',parseInt(currDutyInst)+1);
                $(this).parent().attr('data-duty-instances',parseInt(currDutyInst)+1);
                $(this).parent().attr('data-duty-instance-ids',currInstIds);
                $(this).parent().attr('ondblclick', "editAllocateDuty('editduty','edit', '" + $(this).attr('data-allocations-duty-id') + '_' + $(this).attr('data-date') + "')");
                if((dutyExists == 'Yes' && $('#mastMiscFilterId').val() != '') || (dutyExists == 'No' && $('#mastMiscFilterId').val() == '') || (dutyExists == '' && $('#mastMiscFilterId').val() == '')){
                    if(checkUnallocatedSectionHeight()){
                        $('#rowUnqId_'+$(this).attr('data-allocations-duty-id') + '_' + $(this).attr('data-date'))[0].scrollIntoView({block: "start"});
                        if($('#showWeeks').val() > 1){
                            $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                        } else {
                            if($('#date').val() == ''){
                                $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 30);
                            } else {
                                $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                            }
                        }
                        $( '#rowUnqId_'+$(this).attr('data-allocations-duty-id') + '_' + $(this).attr('data-date') ).css('border-bottom','2px solid #bbb');
                        $('#dragDropId2').html($(this).parent().attr('data-unique-id'));
                    }
                }
                let dutyInstances = $(this).attr('data-duty-instance-ids');
                let dutyInstanceCount = dutyInstances.split(',').length;
                if(dutyInstanceCount > 1){
                    $('#rowUnqId_'+$(this).attr('data-allocations-duty-id') + '_' + $(this).attr('data-date')).addClass('cornerIcon-Unalloc cornerIcon-Grey');
                    $('#dutyCountDiv'+$(this).attr('data-allocations-duty-id') + '_' + $(this).attr('data-date')).html(dutyInstanceCount);
                } else {
                    $('#rowUnqId_'+$(this).attr('data-allocations-duty-id') + '_' + $(this).attr('data-date')).removeClass('cornerIcon-Unalloc cornerIcon-Grey');
                    $('#dutyCountDiv'+$(this).attr('data-allocations-duty-id')).html('');
                }
                return false;
            }
        });
    }
    if(parseInt($('#setTop').val()) == 1){
        //For Now Keep this code as it is in commented state
        //$('#unallocatedDuty').css('top','4px');
    }
    $('#setTop').val('1');
}

function updateUnallocDutyCell(dragSrcInst, dropTargetInst, dragType, newIdVal, updUnallocDutyData, dutyExists='Yes'){
    if((dragType == 'MISC') || (dragType == 'UNALLOCTOALLOC' && dropTargetInst.getAttribute('data-duty-name') != 'U')){
        var datadutyname = dropTargetInst.getAttribute('data-duty-name');
        var datestartdate = dropTargetInst.getAttribute('date-start-date');
        var daterangedays = dropTargetInst.getAttribute('date-range-days');
        var datadate = dropTargetInst.getAttribute('data-date');
        var datarowstart = dropTargetInst.getAttribute('data-row-start');
        var datarowend = dropTargetInst.getAttribute('data-row-end');
        var dataschedulingteamid = dropTargetInst.getAttribute('data-scheduling-team-id');
        var dateenddate = dropTargetInst.getAttribute('date-end-date');
        var dataduration = dropTargetInst.getAttribute('data-duty-duration');
        var dataiday = dropTargetInst.getAttribute('data-iday');
        var dataId = dropTargetInst.getAttribute('data-id');
        var dataIsNeedCovering = dropTargetInst.getAttribute('data-is-need-covering');

    } else {
        var datadutyname = dragSrcInst.attr('data-duty-name');
        var datestartdate = dragSrcInst.attr('date-start-date');
        var daterangedays = dragSrcInst.attr('date-range-days');
        var datadate = dragSrcInst.attr('data-date');
        var datarowstart = dragSrcInst.attr('data-row-start');
        var datarowend = dragSrcInst.attr('data-row-end');
        var dataschedulingteamid = dragSrcInst.attr('data-scheduling-team-id');
        var dateenddate = dragSrcInst.attr('date-end-date');
        var dataduration = dragSrcInst.attr('data-duty-duration');
        var dataiday = dragSrcInst.attr('data-iday');
        var dataId = dragSrcInst.attr('data-id');
        var dataIsNeedCovering = dragSrcInst.attr('data-is-need-covering');
    }
    var sortedDutyNames = sortKeys(updUnallocDutyData).sort();
    var counter = 0;
    $.each(sortedDutyNames, function (key, value) {
        if((datadutyname + datarowstart + datarowend).toLowerCase() == value){
            counter = (key+1);
            return false;
        }
    });

    var counter2 = 1;
    $('.uldutycell_'+datadate).each(function(key, val) {
        if((counter == counter2) && (dataIsNeedCovering == '')){
            var repDiv = '';
            var dragDutyStrtTime = secondsToTime(datarowstart);
            var dragDutyEndTime = secondsToTime(datarowend);
            let dutyHrs = parseFloat(dataduration/3600).toFixed(2);
            let dataEditScreen = 0;
            if($('#editScreen').val() == 1){
                dataEditScreen = 1;
            }
			let draggableFlag = (datadutyname == 'U') ? 'false' : 'true';
            let uniqueId = newIdVal+ '_' + $(this).attr('data-date');
            let ondblclickCondUA = ' ondblclick="editAllocateDuty(\'editduty\',\'edit\', \'' + uniqueId + '\')" ';
            repDiv +='<div ' + ondblclickCondUA + ' id="rowUnqId_'+ uniqueId + '" class="NotFixedon weekly-body-cell context-menu-unallocated ulrow_'+$(this).attr('data-row-counter')+' unalloc-cell" title="'+datadutyname+' ('+dutyHrs+' Hours'+')" onmouseover="removetip()" data-date="'+$(this).attr('data-date')+'" data-source="unallocated" data-id="'+dataId+'" data-duty-count="1" data-duty-name="'+datadutyname+'" data-scheduling-person="0" data-unique-id="'+uniqueId+'" data-scheduling-team-id="'+dataschedulingteamid+'" data-row-start="'+datarowstart+'" data-row-end="'+datarowend+'" data-duty-instances="1" data-row-counter="'+$(this).attr('data-row-counter')+'" data-cell-counter="'+$(this).attr('data-cell-counter')+'" data-duty-instance-ids="'+newIdVal+'" data-next-instance-id="0" data-remain-duty-instances="0" date-range-days="'+daterangedays+'" date-start-date="'+datestartdate+'" date-end-date="'+dateenddate+'">';
                repDiv +='<div id="colUnqId_'+ uniqueId + '" class="unallocated dragAlloc unalloc-drag-drop uldutycell_'+$(this).attr('data-date')+' unalloc-cell-inner" draggable="'+draggableFlag+'" data-date="'+$(this).attr('data-date')+'" data-occupied="1" data-source="unallocated" data-id="'+dataId+'" data-duty-name="'+datadutyname+'" data-duty-count="1" data-scheduling-person="0" data-unique-id="'+uniqueId+'" data-scheduling-team-id="'+dataschedulingteamid+'" data-row-start="'+datarowstart+'" data-row-end="'+datarowend+'" data-duty-instances="1" data-row-counter="'+$(this).attr('data-row-counter')+'" data-cell-counter="'+$(this).attr('data-cell-counter')+'" data-duty-instance-ids="'+newIdVal+'" data-next-instance-id="0" data-remain-duty-instances="0" date-range-days="'+daterangedays+'" date-start-date="'+datestartdate+'" date-end-date="'+dateenddate+'" data-duty-duration="'+dataduration+'" data-iday="'+dataiday+'" data-edit-screen="'+dataEditScreen+'" data-allocations-sp-id="" data-allocations-duty-id="' + newIdVal +'">';
                        repDiv +='<span class="dutyTip" style="overflow:hidden;"> <b>'+datadutyname+'</b><br>';
                        if(dragDutyStrtTime.HOURS+':'+dragDutyStrtTime.MINUTES+' - '+dragDutyEndTime.HOURS+':'+dragDutyEndTime.MINUTES != '00:00 - 00:00'){
                            if(dragDutyEndTime.HOURS+':'+dragDutyEndTime.MINUTES == '24:00'){
                                repDiv +=dragDutyStrtTime.HOURS+':'+dragDutyStrtTime.MINUTES+' - 00:00';
                            } else {
                                repDiv +=dragDutyStrtTime.HOURS+':'+dragDutyStrtTime.MINUTES+' - '+dragDutyEndTime.HOURS+':'+dragDutyEndTime.MINUTES;
                            }
                        }
                        repDiv +='</span>';

                repDiv +='</div>';
                repDiv += '<div class="DutyCountPositionRightTop11" id="dutyCountDiv'+uniqueId+'"></div>';
            repDiv +='</div>';
			if(($('#unAllocSortDate').val() == '') || ($('#unAllocSortDate').val() == '0'))
			{
				$('#rowUnqId_'+datadate+'_'+$(this).attr('data-row-counter')+'_'+$(this).attr('data-cell-counter')).replaceWith(repDiv);
			}
        }
        counter2++;
    });

    if(checkUnallocatedSectionHeight()){
        if ($('#rowUnqId_'+newIdVal).length){
            if(sortKeys(updUnallocDutyData).sort().length == counter){
                $('#rowUnqId_'+newIdVal + '_' + datadate)[0].scrollIntoView({block: "end"});
            } else {
                $('#rowUnqId_'+newIdVal + '_' + datadate)[0].scrollIntoView({block: "start"});
                if($('#showWeeks').val() > 1){
                    $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                } else {
                    if($('#date').val() == ''){
                        setTimeout(function() {
                            $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 30);
                        }, 200);
                    } else {
                        $('#weeklyUnAllocation-2').scrollTop($('#weeklyUnAllocation-2').scrollTop() - 40);
                    }
                }
                if((parseInt($('#setTop').val()) == 1) || ($('#setTop').val() == '')){
                    $('#unallocatedDuty').css('top','4px');
                }
                $('#setTop').val('1');
            }
            $( '#rowUnqId_'+newIdVal ).css('border-bottom','2px solid #bbb');
            $('#dragDropId1').html(newIdVal);
        }
    }
}

function updateWTDCrossImage(dataSet){
    if(dataSet.classNameWTD == ''){
        $('#rowUnqId_'+dataSet.id).removeClass('cross-red');
        $('#rowUnqId_'+dataSet.id).removeClass('cross-blue');
        if($('#colUnqId_'+dataSet.id).attr('data-row-end') != 0){
            if($('#colUnqId_'+dataSet.id).attr('data-attention') == 1){
                $('#rowUnqId_'+dataSet.id).css('background','#FFB6C1').addClass('attentionClass');
            } else if($('#colUnqId_'+dataSet.id).attr('data-attention') == 2){
                $('#rowUnqId_'+dataSet.id).css('background','#9370db').addClass('attentionCopyDutyClass');
            } else {
                $('#rowUnqId_'+dataSet.id).css('background','#ebebeb');
            }
        } else {
			dataSet.dutyName = dataSet.dutyName ? dataSet.dutyName : '';
            if((dataSet.bgcolorcode != '') || (dataSet.dutyName.toUpperCase() == 'LEAVE') || (dataSet.dutyName.toUpperCase() == 'SICK') || (dataSet.dutyName.toUpperCase() == 'U-SICK') || (dataSet.dutyName.toUpperCase() == '-SICK')){
                $('#rowUnqId_'+dataSet.id).css('background','');
                $('#rowUnqId_'+dataSet.id).css('background-color','#'+dataSet.bgcolorcode);
            }
        }
        if($('#colUnqId_'+dataSet.id).attr('data-purple-font') == 1){
            $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).addClass('requestClass').css('color','#800080');
        }
        $("#colUnqId_"+dataSet.id).attr('data-mark-wiad-option','false');
        $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).attr('data-mark-wiad-option','0');
        $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).attr('title',dataSet.titleVal).removeAttr('onmouseover').removeAttr('onmouseleave');
    } else {
        if(dataSet.classNameWTD == 'cross-red'){
            if($('#colUnqId_'+dataSet.id).attr('data-attention') != 0){
                $("#rowUnqId_"+dataSet.id).addClass(dataSet.classNameWTD);
                $("#rowUnqId_"+dataSet.id).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'red\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'red\' stroke-width=\'2\'/></svg>")')
                $("#rowUnqId_"+dataSet.id).css('background-repeat','no-repeat');
                $("#rowUnqId_"+dataSet.id).css('background-position','center');
                $("#rowUnqId_"+dataSet.id).css('background-size','100% 100%, auto');
                if($('#colUnqId_'+dataSet.id).attr('data-attention') == 1) {
                    $('#rowUnqId_'+dataSet.id).css('background-color','#FFB6C1').addClass('attentionClass');
                } else if($('#colUnqId_'+dataSet.id).attr('data-attention') == 2) {
                    $('#rowUnqId_'+dataSet.id).css('background-color','#9370db').addClass('attentionCopyDutyClass');
                }
                if(dataSet.bgcolorcode != ''){
                    $("#rowUnqId_"+dataSet.id).css('background-color','#'+dataSet.bgcolorcode);
                }
                $("#colUnqId_"+dataSet.id).attr('data-mark-wiad-option','true');
                $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).attr('data-mark-wiad-option','1').attr('onmouseover','showwtdtip("showtooltip",this,'+$('#colUnqId_'+dataSet.id).attr('data-allocations-duty-id')+',true)').attr('onmouseleave','showwtdtip("hidetooltip")');
            } else {
                $("#rowUnqId_"+dataSet.id).addClass(dataSet.classNameWTD);
                $("#rowUnqId_"+dataSet.id).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'red\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'red\' stroke-width=\'2\'/></svg>")')
                $("#rowUnqId_"+dataSet.id).css('background-repeat','no-repeat');
                $("#rowUnqId_"+dataSet.id).css('background-position','center');
                $("#rowUnqId_"+dataSet.id).css('background-size','100% 100%, auto');
                $('#rowUnqId_'+dataSet.id).css('background-color','#EBEBEB');
                if(dataSet.bgcolorcode != ''){
                    $("#rowUnqId_"+dataSet.id).css('background-color','#'+dataSet.bgcolorcode);
                }
                $("#colUnqId_"+dataSet.id).attr('data-mark-wiad-option','true');
                $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).attr('data-mark-wiad-option','1').attr('onmouseover','showwtdtip("showtooltip",this,'+$('#colUnqId_'+dataSet.id).attr('data-allocations-duty-id')+',true)').attr('onmouseleave','showwtdtip("hidetooltip")');
            }
            $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).removeAttr('title');
        }
        if(dataSet.classNameWTD == 'cross-blue'){
            if($('#colUnqId_'+dataSet.id).attr('data-attention') != 0){
                $("#rowUnqId_"+dataSet.id).addClass(dataSet.classNameWTD);
                $("#rowUnqId_"+dataSet.id).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'lightblue\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'lightblue\' stroke-width=\'2\'/></svg>")');
                $("#rowUnqId_"+dataSet.id).css('background-repeat','no-repeat');
                $("#rowUnqId_"+dataSet.id).css('background-position','center');
                $("#rowUnqId_"+dataSet.id).css('background-size','100% 100%, auto');
                if($('#colUnqId_'+dataSet.id).attr('data-attention') == 1) {
                    $('#rowUnqId_'+dataSet.id).css('background-color','#FFB6C1').addClass('attentionClass');
                } else if($('#colUnqId_'+dataSet.id).attr('data-attention') == 2) {
                    $('#rowUnqId_'+dataSet.id).css('background-color','#9370db').addClass('attentionCopyDutyClass');
                }
                if(dataSet.bgcolorcode != ''){
                    $("#rowUnqId_"+dataSet.id).css('background-color','#'+dataSet.bgcolorcode);
                }
                $("#colUnqId_"+dataSet.id).attr('data-mark-wiad-option','true');
                $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).attr('data-mark-wiad-option','0');
            } else {
                $("#rowUnqId_"+dataSet.id).addClass(dataSet.classNameWTD);
                $("#rowUnqId_"+dataSet.id).css('background','url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' version=\'1.1\' preserveAspectRatio=\'none\' viewBox=\'0 0 100 100\'><path d=\'M100 0 L0 100 \' stroke=\'lightblue\' stroke-width=\'2\'/><path d=\'M0 0 L100 100 \' stroke=\'lightblue\' stroke-width=\'2\'/></svg>")');
                $("#rowUnqId_"+dataSet.id).css('background-repeat','no-repeat');
                $("#rowUnqId_"+dataSet.id).css('background-position','center');
                $("#rowUnqId_"+dataSet.id).css('background-size','100% 100%, auto');
                $('#rowUnqId_'+dataSet.id).css('background-color','#EBEBEB');
                if(dataSet.bgcolorcode != ''){
                    $("#rowUnqId_"+dataSet.id).css('background-color','#'+dataSet.bgcolorcode);
                }
                $("#colUnqId_"+dataSet.id).attr('data-mark-wiad-option','true');
                $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).attr('data-mark-wiad-option','0');
            }
            $('#dutytipID_'+dataSet.wtdDate+'_'+dataSet.scheduledPerson).removeAttr('onmouseover').removeAttr('onmouseleave');
            $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).removeAttr('title');
        }
        if($('#colUnqId_'+dataSet.id).attr('data-purple-font') == 1){
            $('#dutytipID_'+$('#colUnqId_'+dataSet.id).attr('data-date')+'_'+$('#colUnqId_'+dataSet.id).attr('data-scheduling-person')).addClass('requestClass').css('color','#800080');
        }
    }

    $('#colUnqId_'+dataSet.id).attr('data-eleven-icon',dataSet.underElevenIcon);
}

function updateUnder11Icon(dataSet){
    if(dataSet.IsUnderElevenBreakOverride == '1') {
        $('#colUnqId_'+dataSet.id).find('.DutyIconPositionLeft .elevenIcon span').html('11');
    } else {
        $('#colUnqId_'+dataSet.id).find('.DutyIconPositionLeft .elevenIcon span').html('');
    }
    $('#colUnqId_'+dataSet.id).attr('data-mark-eleven', dataSet.IsUnderElevenBreak);
}
/** sort code scheduled person open the popup */
$(document).on('click', '#js_schdsortcode', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/scheduledPersonSortCode.php",
        data: {
            weekNumber : $('#weekNumber').val(),
            teamId : $('#searchTeamId').val(),
            schedulingPersonID:$(this).attr("data-schedulingpersonid"),
            sortCode:$(this).attr('title'),
            id:$(this).attr('data-id'),
            scheduledPersonName:$(".peronname_"+$(this).attr("data-schedulingpersonid")).attr("data-order")
        },
        success: function (response) {
            $.facebox(response);
        }
    });
});

/* save/update  sortcode scheduledperosn*/
$(document).on('click', function(e){
    $('#scheduledpersonsortcode').validate({
        debug: false,
        submitHandler: function(form) {
            $.ajax({
                type: 'POST',
                url: '/page-includes/allocations/weekly/modals/save-scheduledPersonSortCode.php',
                dataType: "json",
                data: $('#scheduledpersonsortcode').serialize(),
                success: function(response) {
						if (response.strstatus === 'success') {
							let sortCodeStr	=	'';
							if(response.sortcode.length > 16)
							{
								sortCodeStr	=	response.sortcode.substring(0, 8) + "<br>" + (response.sortcode.substring(8, 16)) + "<br>" + response.sortcode.substring(16, (response.sortcode.length));
							}else if(response.sortcode.length > 8)
							{
								sortCodeStr	=	response.sortcode.substring(0, 8) + "<br>" + response.sortcode.substring(8, response.sortcode.length);
							}else
							{
								sortCodeStr = response.sortcode;
							}
							$(".js_schdsortcode_"+response.scheduledpersonid).attr('title',response.sortcode);
                         $(".js_schdsortcode_"+response.scheduledpersonid).html(sortCodeStr);
                         $.facebox.close();
						 updateDataInIndexedDbForCharging(response.scheduledpersonid, response.sortcode, 3);
                    } else {
                        customAlert(response.strstatus);
                        $('#facebox .close').click()
                    }
                }
            });
        }
    })
});

function cellDeleteUnallocated(cellInst){
    let returnHtmlSrc = '';
    returnHtmlSrc +='<div id="colUnqId_'+cellInst.attr("data-date")+'_'+cellInst.attr("data-cell-counter")+'" class="unallocated unallocother-drag-drop uldutycell_'+cellInst.attr("data-date")+'" onmouseover="removetip()" data-date="'+cellInst.attr("data-date")+'" data-occupied="0" data-source="unallocated" data-scheduling-person="0" data-row-counter="'+cellInst.attr("data-row-counter")+'" data-cell-counter="'+cellInst.attr("data-cell-counter")+'" data-unique-id="'+cellInst.attr("data-date")+'_'+cellInst.attr("data-row-counter")+'_'+cellInst.attr("data-cell-counter")+'" date-range-days="'+cellInst.attr("date-range-days")+'" date-start-date="'+cellInst.attr('date-start-date')+'" date-end-date="'+cellInst.attr('date-end-date')+'">';
    returnHtmlSrc +='<span> <br></span>';
    returnHtmlSrc +='</div>';
    $('#rowUnqId_'+cellInst.attr("data-unique-id")).removeAttr('ondblclick');
    $('#rowUnqId_'+cellInst.attr("data-unique-id")).html(returnHtmlSrc);
    $('#rowUnqId_'+cellInst.attr("data-unique-id")).attr('date-range-days',cellInst.attr("date-range-days")).attr('date-start-date',cellInst.attr("date-start-date")).attr('date-end-date',cellInst.attr("date-end-date")).attr('data-id','0').attr('data-occupied','0').attr('data-source','unallocated').attr('data-scheduling-person','0').attr('data-unique-id',cellInst.attr("data-date")+'_'+cellInst.attr("data-row-counter")+'_'+cellInst.attr("data-cell-counter")).attr('id','rowUnqId_'+cellInst.attr("data-date")+'_'+cellInst.attr("data-row-counter")+'_'+cellInst.attr("data-cell-counter"));
    reloadDragDrop(cellInst.attr("data-date")+'_'+cellInst.attr("data-cell-counter"));
}

function loadUnallocatedGrid(param=''){
    removetip();
    var weekCounts = $('#showWeeks').val();
    var data = $('#ajaxLoadParams').val();
    data = $.parseJSON(data);
    data.fireQuery = 'Yes';
    data.queryString = $('#queryString').val();
    data.queryOrder = $('#queryOrder').val();
    data.filterAndSkill = $('#filterAndSkill').val();
    data.filterAndDutyLabel = $('#filterAndDutyLabel').val();
    data.weeks = $('#showWeeks').val();
    data.date = $('#date').val();
    data.mastMiscFilterId = $('#mastMiscFilterId').val();
    data.loadFileJs = 'No';
    let edate = new Date(data.startDate.replace(/-/g, '\/'));
    edate.setDate(edate.getDate() + 7);
    let offsetedate = edate.getTimezoneOffset();
    edate = new Date(edate.getTime() - (offsetedate*60*1000));
    data.endDate=edate.toISOString().split('T')[0];
    if(param == ''){
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/view-unallocated-multiweek.php",
            data: data,
            async: true,
            beforeSend: function (jqXHR, settings) {
                $("#loading").show();
            },
            success: function (response) {
                let responseJson = JSON.parse(response);
                $('#weekNumHeader').val(JSON.stringify(responseJson[2]));
				$('#miscDutyData').val(responseJson[5]);
				$('#dataPickerData').val('');
				$('#getDayIndicatorData').val(btoa(JSON.stringify(responseJson[6])));
				saveDataInIndexedDb(responseJson[0], 1);
				getUnAllocatedSort($.cookie('unAllocSortDate'));
				loadUnallocGrid = true;
                if(loadUnallocGrid){
                    $('#unAllocDutyData').val(JSON.stringify(responseJson[3]));
                    let totalRangeDays = (parseInt($('#showWeeks').val()) * 7);
                    let totalEndDate = new Date(data.startDate.replace(/-/g, '\/'));
                    totalEndDate.setDate(totalEndDate.getDate() +  parseInt(totalRangeDays)-1);
                    let offsettotalEndDate = totalEndDate.getTimezoneOffset();
                    totalEndDate = new Date(totalEndDate.getTime() - (offsettotalEndDate*60*1000));
                    let finalEndDate=totalEndDate.toISOString().split('T')[0];
                    let finalStartDate = data.startDate;

                    $('.misc-drag-drop').attr('date-range-days',totalRangeDays);
                    $('.misc-drag-drop').attr('date-start-date',finalStartDate);
                    $('.misc-drag-drop').attr('date-end-date',finalEndDate);

                    $('#unallocdutycount').html(parseInt(responseJson[4]));
					saveDataInIndexedDb(IndexToAssociativeArray (responseJson[1],'Alloc'));
					getAllocatedSort(7);
                    if($.cookie('unallocgridheight') != undefined){
                        $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                        $("#allocatedDuty").height($.cookie('allocgridheight'));
                        $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                        $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                        $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                        $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                        setTimeout(function() {
                            $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                            $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                        }, 1000);
                        if($('#showHideCount').prop('checked') == false){
                            let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                            $("#allocatedDuty").height(allocatedHeight);
                            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                        }
                    }
                    resizeUnallocatedGrid();
                    $('.scrollHorizontal').scroll(function() {
                        $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                    });
                    $('.scrollVertical').scroll(function() {
                        $('.scrollVertical').scrollTop($(this).scrollTop());
                    });
                    $("#publishWeekIconDiv").css('pointer-events', 'all');
                    $('body').css('pointer-events', 'all');
                }
                $('body').css('pointer-events', 'all');
            },
            complete: function(){
                $("#loading").hide();
            }
        });
    } else {
        return new Promise((resolve, reject) => {
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/view-unallocated-multiweek.php",
                data: data,
                async: true,
                beforeSend: function (jqXHR, settings) {
                    $("#loading").show();
                },
                success: function (response) {
                    let responseJson = JSON.parse(response);
                    $('#weekNumHeader').val(JSON.stringify(responseJson[2]));
                    if(unallocatedgridhtml(responseJson[0],JSON.parse(responseJson[5]),'')){
                        $('#unAllocDutyData').val(JSON.stringify(responseJson[3]));
                        let totalRangeDays = (parseInt($('#showWeeks').val()) * 7);
                        let totalEndDate = new Date(data.startDate.replace(/-/g, '\/'));
                        totalEndDate.setDate(totalEndDate.getDate() +  parseInt(totalRangeDays)-1);
                        let offsettotalEndDate = totalEndDate.getTimezoneOffset();
                        totalEndDate = new Date(totalEndDate.getTime() - (offsettotalEndDate*60*1000));
                        let finalEndDate=totalEndDate.toISOString().split('T')[0];
                        let finalStartDate = data.startDate;

                        $('.misc-drag-drop').attr('date-range-days',totalRangeDays);
                        $('.misc-drag-drop').attr('date-start-date',finalStartDate);
                        $('.misc-drag-drop').attr('date-end-date',finalEndDate);

                        $('#unallocdutycount').html(parseInt(responseJson[4]));
                        loadAllocated(responseJson[1]);
                        if($.cookie('unallocgridheight') != undefined){
                            $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                            $("#allocatedDuty").height($.cookie('allocgridheight'));
                            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                            $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                            setTimeout(function() {
                                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                            }, 1000);
                            if($('#showHideCount').prop('checked') == false){
                                let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                $("#allocatedDuty").height(allocatedHeight);
                                $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                            }
                        }
                        resizeUnallocatedGrid();
                        $('.scrollHorizontal').scroll(function() {
                            $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                        });
                        $('.scrollVertical').scroll(function() {
                            $('.scrollVertical').scrollTop($(this).scrollTop());
                        });
                    }
                    $('body').css('pointer-events', 'all');
                    resolve(true);
                },
                error: function (error) {
                    reject(false);
                },
                complete: function(){
                    $("#loading").hide();
                }
            })
        })
    }
}

function loadAllocatedgrid(){
    var weekCounts = $('#showWeeks').val();
    let data2 = $('#ajaxLoadParams').val();
    data2 = $.parseJSON(data2);
    data2.fireQuery = 'Yes';
    data2.queryString = $('#queryString').val();
    data2.queryOrder = $('#queryOrder').val();
    data.filterAndSkill = $('#filterAndSkill').val();
    data.filterAndDutyLabel = $('#filterAndDutyLabel').val();
    data2.weeks = $('#showWeeks').val();
    data2.date = $('#date').val();
    data2.mastMiscFilterId = $('#mastMiscFilterId').val();
    data2.loadFileJs = 'No';
    data2.selTotalWeeks = $('#showWeeks').val();
    data2.orgStartDate = data2.startDate;

    let totalRangeDays = (parseInt($('#showWeeks').val()) * (7 * weekCounts));
    let totalEndDate = new Date(data2.startDate.replace(/-/g, '\/'));
    totalEndDate.setDate(totalEndDate.getDate() +  parseInt(parseInt($('#showWeeks').val())*7)-1);
    let offsettotalEndDate = totalEndDate.getTimezoneOffset();
    totalEndDate = new Date(totalEndDate.getTime() - (offsettotalEndDate*60*1000));
    let finalEndDate=totalEndDate.toISOString().split('T')[0];
    let finalStartDate = data2.startDate;

    data2.reqStartDate = finalStartDate;
    data2.reqEndDate = finalEndDate;

    let responseIteration = [];
    let responseIterationHeader = [];
    let respSignedtipIds = [];
    let respDutyCommentsIds = [];
    let respWtdTipIds = [];
    let scheduledPeopleCount = $('.allocRow').length;
    if($('#multiweekcount').val() == ''){
		let date = new Date(data2.orgStartDate.replace(/-/g, '\/'));
            date.setDate(date.getDate() +  (7 * weekCounts));
            let offsetdate = date.getTimezoneOffset();
            date = new Date(date.getTime() - (offsetdate*60*1000));
            data2.startDate=date.toISOString().split('T')[0];
            let edate = new Date(data2.startDate.replace(/-/g, '\/'));
            edate.setDate(edate.getDate() + (7 * weekCounts));
            let offsetedate = edate.getTimezoneOffset();
            edate = new Date(edate.getTime() - (offsetedate*60*1000));
            data2.endDate=edate.toISOString().split('T')[0];
            data2.weeks = weekCounts;
            data2.weekCount = weekCounts;
		    $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/actions/view-allocated-multiweek.php",
                data: data2,
                async: true,
                beforeSend: function (jqXHR, settings) {
                    $("#loading").show();
                },
                success: function(result){
                    loadAllocated(result);
                    $('.scrollHorizontal').scroll(function() {
                        $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                    });
                    $('.scrollVertical').scroll(function() {
                        $('.scrollVertical').scrollTop($(this).scrollTop());
                    });
				},
                complete: function(){
                    $("#loading").hide();
                }
			});
    } else {
        let prevWeekCount = $('#multiweekcount').val();
        $('#multiweekcount').val(weekCounts);
        if(parseInt(weekCounts) > parseInt(prevWeekCount)){
            let loopStartCount = parseInt(prevWeekCount)+1;
            for(let i=loopStartCount; i<= weekCounts; i++){
                let date = new Date(data2.startDate.replace(/-/g, '\/'));
                if(i==loopStartCount){
                    date.setDate(date.getDate() + (7*(i-1)));
                } else {
                    date.setDate(date.getDate() + 7);
                }
                let offsetdate = date.getTimezoneOffset();
                date = new Date(date.getTime() - (offsetdate*60*1000));
                data2.startDate=date.toISOString().split('T')[0];
                let edate = new Date(data2.startDate.replace(/-/g, '\/'));
                edate.setDate(edate.getDate() + 7);
                let offsetedate = edate.getTimezoneOffset();
                edate = new Date(edate.getTime() - (offsetedate*60*1000));
                data2.endDate=edate.toISOString().split('T')[0];
                data2.weeks = 1;
                data2.weekCount = i;
                $.ajax({
                    type: "post",
                    url: "/page-includes/allocations/weekly/actions/view-allocated-multiweek.php",
                    data: data2,
                    async: true,
                    beforeSend: function (jqXHR, settings) {
                        $("#loading").show();
                    },
                    success: function(result){
                        result = $.parseJSON(result);
                        if(result['bodyStr'] == null){
                            responseIteration[i] = createCellsForNonCreatedWeek(scheduledPeopleCount);
                        } else {
                            responseIteration[i] = result['bodyStr'];
                        }
                        responseIterationHeader[i] = result['subHeader'];
                        respSignedtipIds[i] = result['signedtipids'];
                        respDutyCommentsIds[i] = result['dutycommentsids'];
                        respWtdTipIds[i] = result['wtdids'];
                        if(Object.keys(responseIteration).length == (parseInt(weekCounts)-parseInt(prevWeekCount))){
                            responseIteration.forEach((number2, index2, rows2) => {
                                $('.allocatedTableHead').append(responseIterationHeader[index2]);
                                $('[class*=schRowRight]').each(function(index, currentElement){
                                    if(rows2[index2][$(this).attr('data-scheduling-person')] != undefined){
                                        $(this).append(rows2[index2][$(this).attr('data-scheduling-person')]);
                                    } else {
                                        $(this).append(blankRowForDates(7));
                                    }
                                });

                                $('.dragAlloc').on('dragstart', function (event) {
                                    dragSource = $(this);
                                    event.stopImmediatePropagation();
                                    startDrag(dragSource);
                                });

                                $('.dropeventscall').on('over', function (event) {
                                    event.stopImmediatePropagation();
                                    event.preventDefault();
                                });
                                $('.scrollHorizontal').scroll(function() {
                                    $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                                });
                                $('.scrollVertical').scroll(function() {
                                    $('.scrollVertical').scrollTop($(this).scrollTop());
                                });
                                $('body').css('pointer-events', 'all');
                            });
                        }
                        if((result['leftTableHead'] != '') && (result['leftTableData'] != '')){
                            $('.allocLeftTableHead').html(result['leftTableHead']);
                            $('.allocLeftTableData').html(result['leftTableData']);
                        }
                    },
                    complete: function(){
                        $("#loading").hide();
                    }
                });
            }
        } else {
            let loopEndCount = parseInt(prevWeekCount)+1;
            let selHeadChildren = (7*parseInt(weekCounts)-1);
            $('.allocatedTableHead').children(':eq('+selHeadChildren+')').nextAll().remove();
            $('[class*=schRowRight]').each(function(index, currentElement){
                $(this).children(':eq('+selHeadChildren+')').nextAll().remove();
            });
    		$('.dragAlloc').on('dragstart', function (event) {
                dragSource = $(this);
                event.stopImmediatePropagation();
                startDrag(dragSource);
            });
            $('.dropeventscall').on('over', function (event) {
                event.stopImmediatePropagation();
                event.preventDefault();
            });
            $('body').css('pointer-events', 'all');
        }
    }
    $('#weeklyUnAllocation-2').css('overflow-x', 'scroll');
    $('.item-cell').attr('date-range-days',totalRangeDays);
    $('.item-cell').attr('date-start-date',finalStartDate);
    $('.item-cell').attr('date-end-date',finalEndDate);
}

function refreshShiftCountingCell(paramdata={}){
    let getFilterAlphabets = $('#dutyCountFilterCols').val();
    let splitFilterAlphabets = getFilterAlphabets.split(',');
    let dragType = paramdata.dragType;
    let dutyName = paramdata.dutyName;
    let dutyDate = paramdata.dutyDate;

    let dutyFirstCharacter = dutyName.substr(0,1);
    let dutySecondCharacter = dutyName.substr(1,1);

    let dutyDateWeekDay = getDayName(dutyDate,'en-GB');

    for(let i=0; i<splitFilterAlphabets.length; i++){
        let characterWord = splitFilterAlphabets[i].toUpperCase();
        if((characterWord != 'D') && ((dragType.toUpperCase() == 'UNALLOCTOALLOC') || (dragType.toUpperCase() == 'MISC') || (dragType.toUpperCase() == 'ALLOCTOUNALLOC')) && ((dutySecondCharacter == '-') || (dutySecondCharacter == ' ') || (dutySecondCharacter == '/'))){
            let finalCellVal = 0;
            let cellBgColorCode = '';
            if(characterWord != 'T' && (dutyFirstCharacter == characterWord)){
                let cellVal = $('#cellCountSum_'+dutyDate+'_'+characterWord).html();
                let satSun = 'No';
                if((dutyDateWeekDay == 'Saturday') || (dutyDateWeekDay == 'Sunday')){
                    satSun = 'Yes';
                }
                finalCellVal = parseInt(cellVal)+1;

                if((finalCellVal == 0) && (satSun == 'No')){
                    cellBgColorCode = '#A7B5A9';
                }
                if((finalCellVal == 0) && (satSun == 'Yes')){
                    cellBgColorCode = '#FFFFA7';
                }
                if(finalCellVal < 0){
                    cellBgColorCode = '#ED5F5F';
                }
                if(finalCellVal > 0){
                    cellBgColorCode = '#5FE4ED';
                }
                $('#showCounts .weekly-table-body #cellCountSum_'+dutyDate+'_'+characterWord).css('background-color',cellBgColorCode).html(finalCellVal);

                if((splitFilterAlphabets[0].toUpperCase() == 'T') || (splitFilterAlphabets[1].toUpperCase() == 'T')){
                    let totalCellVal = $('#cellCountSum_'+dutyDate).html();
                    finalCellVal = parseInt(totalCellVal)+1;
                    if(finalCellVal == 0){
                        cellBgColorCode = '#56DB63';
                    }
                    if(finalCellVal < 0){
                        cellBgColorCode = '#ED5F5F';
                    }
                    if(finalCellVal > 0){
                        cellBgColorCode = '#5FE4ED';
                    }
                    $('#showCounts .weekly-table-body #cellCountSum_'+dutyDate).css('background-color',cellBgColorCode).html(finalCellVal);
                }
            }
        }
    }
}

function blankRowForDates(days=7){
    let respBlankRowData = '';
    for(let i=1; i<=days; i++){
        respBlankRowData +='<div id="rowUnqId_0" class="NotFixedon weekly-body-cell allocatedDutyCell"> <div id="colUnqId_0" class="item-cell"> U</span><div class="DutyIconPositionLeft"> <div class="IconPositionClass blueEuro displayInlineBlock"></div><div class="IconPositionClass redEuro displayInlineBlock"></div><div class="markWA icons displayInlineBlock IconPositionClass"></div><div class="displayInlineBlock IconPositionClass emptyIcon1"></div><div class="displayInlineBlock IconPositionClass iconL"></div><div class="displayInlineBlock IconPositionClass iconLock"></div><div class="displayInlineBlock IconPositionClass iconR"></div></div><div class="DutyIconPositionRight"> <div class="handcursor signedtip displayInlineBlock"></div><div id="circle" class="hide circleIcon tipremotecomments"></div></div></div></div>';
    }
    return respBlankRowData;
}

function resizeUnallocatedGrid(){
    var other;
    var minHeightDrag = 0;

    if($('#showWeeks').val() > 1){
        minHeightDrag = 62;
        if ($('#showMastMiscDuty').prop("checked") == true) {
            minHeightDrag = 162;
        }
    } else {
        minHeightDrag = 30;
        if ($('#showMastMiscDuty').prop("checked") == true) {
            minHeightDrag = 155;
        }
    }
    $(".resiz").resizable({
        handles: 's',
        /* try using "n" to see what happens... */
        minHeight: minHeightDrag,
        start: function(e, ui) {
            if ($(this).next('.resiz').length > 0) {
                other = $(this).next('.resiz');
            } else {
                other = $(this).prev('.resiz');
            }
            startingHeight = other.height();
        },
        resize: function(e, ui) {
            var dh = ui.size.height - ui.originalSize.height;
            if (dh > startingHeight) { // can't resize the box more then it's neighbour
                dh = startingHeight;
                ui.size.height = ui.originalSize.height + dh;
            }
            other.height(startingHeight - dh);
            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
            $('.showCountContainer').css('max-height', $("#showCountBlock").height());

            $.cookie('unallocgridheight',$("#unallocatedDuty").height());
            $.cookie('allocgridheight',$("#allocatedDuty").height());
            $.cookie('shiftgridheight',$("#showCountBlock").height());
        }
    });
}

/** Multuple Request Infor Start By Soniya*/
    function fetchDutyRequestsPopUp(data) {
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/modals/duty-request-modal.php",
            data: data,
            success: function (response) {
                $.facebox(response);
            }
        });
    }
/** End Here */

function loadMultiweek(btnClicktype = ''){
    var weekCounts = $('#showWeeks').val();
    var weekDays = parseInt(weekCounts)*7;
    var data = $('#ajaxLoadParams').val();
    data = $.parseJSON(data);
    if(btnClicktype == 'PREV'){
        let sdate = new Date(data.startDate.replace(/-/g, '\/'));
        sdate.setDate(sdate.getDate() -  7);
        let offsetsdate = sdate.getTimezoneOffset();
        sdate = new Date(sdate.getTime() - (offsetsdate*60*1000));
        sdate=sdate.toISOString().split('T')[0];

        let edate = new Date(data.endDate.replace(/-/g, '\/'));
        edate.setDate(edate.getDate() -  7);
        let offsetedate = edate.getTimezoneOffset();
        edate = new Date(edate.getTime() - (offsetedate*60*1000));
        edate=edate.toISOString().split('T')[0];

        data.days = 6;
        data.endDate = edate;
        data.endWeek = data.startWeek;
        data.endWeekDate = edate;
        data.startDate = sdate;
        data.startWeek = $('#weekNumber').val();
        data.startWeekDate = sdate;
        $('#ajaxLoadParams').val(JSON.stringify(data));
        loadUnallocatedGrid();
        reloadeditweeklygrid('','','','','ALLOC');
    } else {
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/check-week.php",
            data: {
                'actionname' : 'getweekdata',
                'weekNumber' : $('#weekNumber').val()
            },
            success: function (response) {
                response = $.parseJSON(response);
                let nextWeekNum = $('#weekNumber').val();
                let splitNextWeekNumber = nextWeekNum.split('/');
                let nexttonextweeknum = response.nexttoNextWeekNumber;
                let splitnexttonextweeknum = nexttonextweeknum.split('/');
                data.days = 6;
                data.endDate = response.weekEndDate;
                data.endWeek = splitnexttonextweeknum[1]+splitnexttonextweeknum[0];
                data.endWeekDate = response.weekEndDate;
                data.schedulingTeamId = $('#teamId').val();
                data.startDate = response.weekStartDate;
                data.startWeek = splitNextWeekNumber[1]+splitNextWeekNumber[0];
                data.startWeekDate = response.weekStartDate;
                data.teamId = $('#teamId').val();
                data.userId = response.currentUserId;
                data.weekNumber = $('#weekNumber').val();
                $('#ajaxLoadParams').val(JSON.stringify(data));
                loadUnallocatedGrid();
                reloadeditweeklygrid('','','','','ALLOC');
            }
        });
    }
}

function reloadDragDrop(refId=0){
    $('#colUnqId_'+refId).on('dragstart', function (event) {
        dragSource = $(this);
        event.stopImmediatePropagation();
        startDrag(dragSource);
    });

    $('.dropeventscall').on('dragover', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
    });
}

function showdutycomments(type='',currObj,allocationsDutyId=0, allocationId = 0,allocationsSPID = 0){
    $('#loading').hide();
    if(type == 'showtooltip'){
        var reqData = {
            'allocationsDutyId':allocationsDutyId,
			'id':allocationId,
			'allocationsSpId':allocationsSPID
        };
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/get-comments-hover.php",
            data: reqData,
            success: function(result){
                let nativeTopPos = $(currObj).offset().top;
                let nativeLeftPos = $(currObj).offset().left;

                let topPos = nativeTopPos-199;
                let leftPos = nativeLeftPos-395;

                $('#tooltipDiv').css('left',leftPos+'px');
                $('#tooltipDiv').css('top',topPos+'px');
                $('#tooltipDiv').html(result);
                $('#tooltipDiv').show();
            }
        });
    } else {
        $('#tooltipDiv').hide();
        $('#tooltipDiv').html('');
    }
}

function showsignedtip(type='',currObj,uIdStr){
    $('#loading').hide();
    if(type == 'showtooltip'){
        var reqData = {
            'allocationsSPID': $("#colUnqId_"+uIdStr).attr('data-allocations-sp-id'),
			'StartTime': $("#colUnqId_"+uIdStr).attr('data-row-start'),
			'EndTime': $("#colUnqId_"+uIdStr).attr('data-row-end'),
			'DutyName': $("#colUnqId_"+uIdStr).attr('data-duty-name')
        };
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/allocations-signedin-info.php",
            data: reqData,
            success: function(result){
                let nativeTopPos = $(currObj).offset().top;
                let nativeLeftPos = $(currObj).offset().left;

                let topPos = nativeTopPos-160;
                let leftPos = nativeLeftPos-405;

                $('#tooltipDiv').css('left',leftPos+'px');
                $('#tooltipDiv').css('top',topPos+'px');
                $('#tooltipDiv').html(result);
                $('#tooltipDiv').show();
            }
        });
    } else {
        $('#tooltipDiv').hide();
        $('#tooltipDiv').html('');
    }
}
var dutyIdContainer=0;
function showwtdtip(type='',currObj,dutyId,wiadOpt,ctpdlStartTime=0,ctpdlEndTime=0){
    $('#loading').hide();
    if(type == 'showtooltip'){
		if((dutyId != dutyIdContainer) || ($('#tooltipDiv').html().length == 214))
		{
			var reqData = {
				'id': dutyId,
				'wiadOption': wiadOpt,
                'pdlStartTime': parseInt(ctpdlStartTime),
                'pdlEndTime': parseInt(ctpdlEndTime)
			};
			dutyIdContainer = dutyId;
			$.ajax({
				type: "post",
				url: "/page-includes/allocations/weekly/actions/get-wtdtypes-hover.php",
				data: reqData,
				success: function(result){
					let nativeTopPos = $(currObj).offset().top;
					let nativeLeftPos = $(currObj).offset().left;

					let topPos = nativeTopPos-121;
					let leftPos = nativeLeftPos-193;

					$('#tooltipDiv').css('left',leftPos+'px');
					$('#tooltipDiv').css('top',topPos+'px');
					$('#tooltipDiv').html(result);
					$('#tooltipDiv').show();
				}
			});
		}else
		{
			$('#tooltipDiv').show();
		}
    } else {
        $('#tooltipDiv').hide();
    }
}

function removetip(){
    $('#loading').hide();
    $('#tooltipDiv').hide();
	$('#schedulingNotesContainer').hide('fast');
}

function loadUnallocatedSingleWeek(dutyDataUnalloc,miscDutyData,dataPickerParam,showDiv=2){
    if(unallocatedgridhtml(dutyDataUnalloc,miscDutyData,dataPickerParam,showDiv)){
        return true;
    } else {
        return false;
    }
}

function formatDateForHeader(dateStr){
    let splitDateStr = dateStr.split('-');
    return splitDateStr[2]+'/'+splitDateStr[1]+'/'+splitDateStr[0];
}

function convertHMS(value) {
    let sec = parseInt(value, 10);
    let hours   = Math.floor(sec / 3600);
    let minutes = Math.floor((sec - (hours * 3600)) / 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    return hours+':'+minutes;
}

function unallocatedgridhtml(dutyData,miscDuty,dataPickerData,showDiv=2)
{
    $("#loadingWeekly").show();
    if(dutyData != ''){
		saveDataInIndexedDb(dutyData, 1);
		$('#miscDutyData').val(JSON.stringify(miscDuty));
		$('#dataPickerData').val(JSON.stringify(dataPickerData));
		dutyData = IndexToAssociativeArray (dutyData,'Unalloc');
    }

    var dutyDataUnalloc = dutyData;
    var miscDutyData = '';
    var miscDutyData = (miscDuty != '') ? miscDuty : (($('#miscDutyData').val() != '') && ($('#miscDutyData').val() != undefined)) ? JSON.parse($('#miscDutyData').val()) : '';
    var dateParam = (dataPickerData != '') ? dataPickerData : (($('#dataPickerData').val() != '') && ($('#dataPickerData').val() != undefined)) ? JSON.parse($('#dataPickerData').val()) : '';

    var pData = $('#ajaxLoadParams').val();
    pData = $.parseJSON(pData);
    var dayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    var dateRangeDays = 7;
    var weekDayNum = 7;
    var totalWeekNumDays = 7;
    if($('#showWeeks').val() > 1){
        totalWeekNumDays = parseInt($('#showWeeks').val())*weekDayNum;
        dateRangeDays = totalWeekNumDays;
        var weekStartDate = new Date(pData.startDate.replace(/-/g, '\/'));
        weekStartDate.setDate(weekStartDate.getDate() +  totalWeekNumDays);
        let offsetweekStartDate = weekStartDate.getTimezoneOffset();
        weekStartDate = new Date(weekStartDate.getTime() - (offsetweekStartDate*60*1000));
        pData.endDate = weekStartDate.toISOString().split('T')[0];
    } else {
        if($('#date').val() != ''){
            if(typeof dateParam == 'string'){
                dateParam = JSON.parse(dateParam);
                pData.startDate = dateParam.startDate;
                pData.endDate = dateParam.endDate;
            } else {
                pData.startDate = dateParam.startDate;
                pData.endDate = dateParam.endDate;
            }

            var satRowCount = 0;
            for(var m = 0; m < 7; m++){
                let date = new Date(pData.startDate.replace(/-/g, '\/'));
                date.setDate(date.getDate() + m);
                let offsetdate = date.getTimezoneOffset();
                date = new Date(date.getTime() - (offsetdate*60*1000));
                if(dayName[date.getDay()] == 'Saturday'){
                    break;
                }
                satRowCount++;
            }
        } else {
            let wEndDate = new Date(pData.endDate.replace(/-/g, '\/'));
            wEndDate.setDate(wEndDate.getDate() - 1);
            let offsetwEndDate = wEndDate.getTimezoneOffset();
            wEndDate = new Date(wEndDate.getTime() - (offsetwEndDate*60*1000));
            pData.endDate = wEndDate.toISOString().split('T')[0];
        }
    }

    let unallocDutyNameArr = {};
    let unallocProcessData = {};
    if(dutyData != ''){
        dutyDataUnalloc.forEach((number, index, rows) => {
			rows[index].StartTime = rows[index].StartTime ? rows[index].StartTime : 0;
			rows[index].EndTime = rows[index].EndTime ? rows[index].EndTime : 0;
            let dutyName = rows[index].DutyName + rows[index].StartTime + rows[index].EndTime;
            let dutyDate = rows[index].DutyDate ? rows[index].DutyDate : 0;
            if(unallocDutyNameArr[dutyName] == undefined){
                unallocDutyNameArr[dutyName] = {};
                unallocDutyNameArr[dutyName][dutyDate] = {};
            }
            if(unallocDutyNameArr[dutyName][dutyDate] == undefined){
                unallocDutyNameArr[dutyName][dutyDate] = {};
            }
            unallocDutyNameArr[dutyName][dutyDate]['DutyInstances'] = rows[index].DutyInstances;

            if(unallocProcessData[dutyName] == undefined){
                unallocProcessData[dutyName] = {};
                unallocProcessData[dutyName][dutyDate] = {};
            }
            if(unallocProcessData[dutyName][dutyDate] == undefined){
                unallocProcessData[dutyName][dutyDate] = {};
            }

            if(rows[index].DutyInstances > 1){
                let existsIds = rows[index].InstanceIds;
                existsIds = existsIds.replace('','');
                existsIds = existsIds.match(/\d+/g);
                //rows[index].ID = existsIds[0];
                unallocProcessData[dutyName][dutyDate] = rows[index];
            } else {
                unallocProcessData[dutyName][dutyDate] = rows[index];
            }
        });
    }
    $('#unAllocDutyData').val(JSON.stringify(unallocDutyNameArr));
    let ungridhtml1week = '';
    ungridhtml1week +='<div class="block-25 grid-item" onmouseover="removetip();">';
        ungridhtml1week +='<div id="miscDutyBlock" onScroll="setContainerScrollPos()">';
            ungridhtml1week +='<div id="miscDutyBlockPos" name="miscDutyBlockPos"></div>';
            ungridhtml1week +='<div class="weeklyAllocationTable weekly-table scrollHorizontal sticky-header" id="miscDutyTopHeader" style="width:99.9%; ';
				if(($('#showWeeks').val() > 1) || ($('#date').val() != '')){
					ungridhtml1week +=' height:41px; ';
				}
				ungridhtml1week +='">';
				ungridhtml1week +='<div class="weekly-table-header">';
				if ($('#showWeeks').val() > 1) {
					ungridhtml1week +='<div class="miscHeader weekly-table-row" style="height:41px">';
				} else {
					ungridhtml1week +='<div class="miscHeader weekly-table-row" style="height:27px">';
				}
				ungridhtml1week +='<div class="left-pad-10 weekly-header-cell">';
					if($.cookie('showMiscDutyFilter'+$('#teamId').val()) == 'showFilter'){
						ungridhtml1week +='<input type="checkbox" checked="checked" name="showMastMiscDuty" id="showMastMiscDuty" onclick="checkMiscDutyFilterBox();">';
					} else {
						ungridhtml1week +='<input type="checkbox" name="showMastMiscDuty" id="showMastMiscDuty" onclick="checkMiscDutyFilterBox();">';
					}
				ungridhtml1week +='</div>';
				ungridhtml1week +='<h3 class="weekly-header-cell font-bold heading">Misc Duties</h3>';
					ungridhtml1week +='<div class="weekly-header-cell">';
						ungridhtml1week +='<input type="text" name="searchMiscDuty" id="searchMiscDuty" onkeyup="searchMisccDutyKu()" onkeydown="searchMisccDutyKd()" placeholder="Misc Duty" autocomplete="off">';
					ungridhtml1week +='</div>';
				ungridhtml1week +='</div>';
			ungridhtml1week +='</div>';
		ungridhtml1week +='</div>';
			if((miscDutyData.length > 0)){
				miscDutyData.forEach((number2, index2, rows2) => {
                    if($.cookie('showMiscDutyFilter'+$('#teamId').val()) == 'showFilter'){
                        ungridhtml1week +='<div class="border-colour td-bg-colour dutiesListTR hide-tb-tr" style="cursor: pointer; display: block;" id="miscDutyRow'+rows2[index2].MasterDutyID+'">';
                    } else {
                        ungridhtml1week +='<div class="border-colour td-bg-colour dutiesListTR hide-tb-tr" style="cursor: pointer;" id="miscDutyRow'+rows2[index2].MasterDutyID+'">';
                    }
						ungridhtml1week +='<div class="border-colour td-bg-colour miscDutyTD">';
                            let dropAllowClass = 'misc-drag-drop';
                            let makeDraggable = (rows2[index2].DutyName == 'U') ? 'false' : 'true';
							ungridhtml1week +='<div id="dragdiv'+rows2[index2].MasterDutyID+'" class="dragAlloc '+dropAllowClass+'" draggable="'+makeDraggable+'" data-source="misc" data-misc-dutyname="'+rows2[index2].DutyName+'" data-misc-dutyduration="'+rows2[index2].Duration+'" data-misc-dutycolour-id="'+rows2[index2].DutyColourID+'" data-misc-id="'+rows2[index2].MasterDutyID+'" data-break-time="'+rows2[index2].BreakTime+'" date-range-days="'+dateRangeDays+'" date-start-date="'+pData.startDate+'" date-end-date="'+pData.endDate+'">';
								ungridhtml1week +=rows2[index2].DutyName;
							ungridhtml1week +='</div>';
						ungridhtml1week +='</div>';
					ungridhtml1week +='</div>';
				});
			}
        ungridhtml1week +='</div>';
    ungridhtml1week +='</div>';
    ungridhtml1week +='<div class="block-75 grid-item">';
        ungridhtml1week +='<div id="weeklyUnAllocation-2"  class="scrollHorizontal allocationSingleWeek" onscroll="divScrollTop();">';
            ungridhtml1week +='<div id="unallocDutyBlockPos" name="unallocDutyBlockPos"></div>';
                ungridhtml1week +='<div id="weeklyAllocationTable" class="weeklyAllocationTable weekly-table scrollHorizontal dropzone"  data-source="unallocated" style="width:100%;">';
                    ungridhtml1week +='<div class="weekly-table-header sticky-header unallocatedDatesHeader" style="z-index:1;">';
                    if ($('#date').val() != ''){
                        let dateWeekNumber = dateParam.dateWeekNumber;
                        let dateWeekYear = dateParam.dateWeekYear;
                        let nextWeekNum = dateParam.nextWeekNum;
                        let nextWeekYear = dateParam.nextWeekYear;
                        if(satRowCount == 0){
                            ungridhtml1week +='<div class="weekly-table-row multiWeekHolder">';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center">Week '+dateWeekNumber+'/'+dateWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                            ungridhtml1week +='</div>';
                        } else if(satRowCount == 1){
                            ungridhtml1week +='<div class="weekly-table-row multiWeekHolder">';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center">Week '+dateWeekNumber+'/'+dateWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-border"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-right">Week</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-left">'+nextWeekNum+'/'+nextWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                            ungridhtml1week +='</div>';
                        } else if(satRowCount == 2){
                            ungridhtml1week +='<div class="weekly-table-row multiWeekHolder">';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-right">Week</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-left">'+dateWeekNumber+'/'+dateWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center date-border"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center">Week '+nextWeekNum+'/'+nextWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                            ungridhtml1week +='</div>';
                        } else if(satRowCount == 3){
                            ungridhtml1week +='<div class="weekly-table-row multiWeekHolder">';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center">Week '+dateWeekNumber+'/'+dateWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center date-border"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-right">Week</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-left">'+nextWeekNum+'/'+nextWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                            ungridhtml1week +='</div>';
                        } else if(satRowCount == 4){
                            ungridhtml1week +='<div class="weekly-table-row multiWeekHolder">';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-right">Week</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-left">'+dateWeekNumber+'/'+dateWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center date-border"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center">Week '+nextWeekNum+'/'+nextWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                            ungridhtml1week +='</div>';
                        } else if(satRowCount == 5){
                            ungridhtml1week +='<div class="weekly-table-row multiWeekHolder">';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center">Week '+dateWeekNumber+'/'+dateWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-border date-text-right">Week</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-left">'+nextWeekNum+'/'+nextWeekYear+'</div>';
                            ungridhtml1week +='</div>';
                        } else {
                            ungridhtml1week +='<div class="weekly-table-row multiWeekHolder">';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-right">Week</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell date-text-left">'+dateWeekNumber+'/'+dateWeekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center date-border">Week '+nextWeekNum+'/'+nextWeekYear+'</div>';
                            ungridhtml1week +='</div>';
                        }
                    } else {
                        if(($('#showWeeks').val() > 1) && ($('#weekNumHeader').val() != '')){
                            let getWeekNumHeaderVal = JSON.parse($('#weekNumHeader').val());
                            ungridhtml1week +='<div class="weekly-table-row multiWeekHolder">';
                            var borderStyle = '';
                            for(let b=0; b<getWeekNumHeaderVal.length; b++){
                                if(b>0){
                                    borderStyle = 'style="border-left: 1px solid #fff;"';
                                }
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center" '+borderStyle+'></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center">Week '+getWeekNumHeaderVal[b].weekNum+'/'+getWeekNumHeaderVal[b].weekYear+'</div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                                ungridhtml1week +='<div class="min-w120 weekly-header-cell text-center"></div>';
                            }
                            ungridhtml1week +='</div>';
                        }
                    }
                    ungridhtml1week +='<div class="weekly-table-row unallocatTable1Head">';
                        for(var i = 0; i < totalWeekNumDays; i++){
								let sortBgImgStr = '';
                                let date = new Date(pData.startDate.replace(/-/g, '\/'));
								let daydate = date;
                                date.setDate(date.getDate() +  i);
                                let offsetdate = date.getTimezoneOffset();
                                date = new Date(date.getTime() - (offsetdate*60*1000));
								let cdateVal = '';
								switch(dayName[daydate.getDay()].toLowerCase())
								{
									case 'saturday' : cdateVal = 0;
													break;
									case 'sunday' 	: cdateVal = 1;
													break;
									case 'monday' 	: cdateVal = 2;
													break;
									case 'tuesday' 	: cdateVal = 3;
													break;
									case 'wednesday': cdateVal = 4;
													break;
									case 'thursday' : cdateVal = 5;
													break;
									case 'friday' 	: cdateVal = 6;
													break;

								}
								const dayValArr = [0, 1, 2, 3, 4, 5, 6];
								let dynamicDate = formatDateForHeader(date.toISOString().split('T')[0]).replaceAll('/', '');
								if(parseInt($('#showWeeks').val()) > 1)
								{
									if(($('#unAllocSortType').val() == 0) && ($.cookie('unAllocSortDate') == cdateVal) && ($('#unAllocSortDt').val() == dynamicDate))
									{
										sortBgImgStr = 'background-image: url(../images/sort_asc_editWeekly.png);';
									}else if(($('#unAllocSortType').val() == 1) && ($.cookie('unAllocSortDate') == cdateVal) && ($('#unAllocSortDt').val() == dynamicDate))
									{
										sortBgImgStr = 'background-image: url(../images/sort_desc_editWeekly.png);';
									} else {
										sortBgImgStr = 'background-image: url(../images/sort_both_editWeekly.png);';
									}
								}else
								{
									if(($('#unAllocSortType').val() == 0) && ($.cookie('unAllocSortDate') == cdateVal))
									{
										sortBgImgStr = 'background-image: url(../images/sort_asc_editWeekly.png);';
									}else if(($('#unAllocSortType').val() == 1) && ($.cookie('unAllocSortDate') == cdateVal))
									{
										sortBgImgStr = 'background-image: url(../images/sort_desc_editWeekly.png);';
									} else {
										sortBgImgStr = 'background-image: url(../images/sort_both_editWeekly.png);';
									}
								}
								let searchTeamId = $('#searchTeamId').val();
								let indicatorClass = 'dayfixed allocations-hideday-menu';
								let getDayIndicatorData = atob($('#getDayIndicatorData').val());
								getDayIndicatorData = JSON.parse(getDayIndicatorData);

								Object.keys(getDayIndicatorData).forEach(key => {

									if(date.toISOString().split('T')[0] == getDayIndicatorData[key]['dDateTime'])
									{
										if(getDayIndicatorData[key]['BandColour'] == 'G')
										{
											indicatorClass = 'dayfixed allocations-hideday-menu';
										}
										if(getDayIndicatorData[key]['BandColour'] == 'Y')
										{
											indicatorClass = 'daynotfixed allocations-hideday-menu';
										}
										if(getDayIndicatorData[key]['BandColour'] == 'B')
										{
											indicatorClass = 'dotw allocations-hideday-menu';
										}
										if(getDayIndicatorData[key]['BandColour'] == 'P')
										{
											indicatorClass = 'dayhidden allocations-showday-menu';
										}
									}
								})

								let dayIndicatorFlag = '<div pageid="4" date="'+date.toISOString().split('T')[0]+'" team="'+searchTeamId+'" style="width: 104%;position: relative;left: -4px;/* top:25px; */height: 9px;" class="handcursor '+indicatorClass+' indicator-cls" id="'+date.toISOString().split('T')[0]+'-dayIndicator"></div>';
                            ungridhtml1week +='<div class="min-w120 weekly-header-cell" data-date="'+cdateVal+'" onclick="getUnAllocatedSort('+cdateVal+', true); unAllocSortDt.value='+"'"+dynamicDate.toString()+"'"+';" style="cursor:pointer;">';
                                ungridhtml1week +=dayName[daydate.getDay()];
                                ungridhtml1week +='<br>';
                                ungridhtml1week +=formatDateForHeader(date.toISOString().split('T')[0]);
                            ungridhtml1week +='<div style="'+sortBgImgStr+' background-repeat: no-repeat; position:absolute; top:8px; right: 2px; height: 40px; width: 16px;" id="sort_img_unalloc"></div>' + dayIndicatorFlag+'</div>';
                        }
                    ungridhtml1week +='</div>';
                ungridhtml1week +='</div>';

                let allowOnDrop = 'ondrop="dropAlloc(event,this)"';
                let dropCls = 'dutydroppable';

                ungridhtml1week +='<div class="weekly-table-body unAllocateddTableBody '+dropCls+' dropeventscall-un" '+allowOnDrop+' data-scheduling-person="0" data-source="unallocated">';
                let rowCounter = 1;
                for ([index1, rows1] of Object.entries(unallocProcessData)) {
                    ungridhtml1week +='<div class="weekly-table-row">';
                        let blankCellCounter = 0;
                        for(var j = 0; j < totalWeekNumDays; j++){
                            let loopdate = new Date(pData.startDate.replace(/-/g, '\/'));
                            loopdate.setDate(loopdate.getDate() + j);
                            let offsetloopdate = loopdate.getTimezoneOffset();
                            loopdate = new Date(loopdate.getTime() - (offsetloopdate*60*1000));
                            let cellDate = loopdate.toISOString().split('T')[0];
                            if(rows1[cellDate] != undefined){
                                let dutyInstanceCount = 0;
                                let dutyData = rows1[cellDate];
                                let dutyCount = rows1[cellDate].length;
                                let dutyInstIds = dutyData.InstanceIds ? dutyData.InstanceIds : '';
                                let splitDutyInstance = dutyInstIds.split(',');
                                dutyInstanceCount = splitDutyInstance.length;
                                let dutyInstancIds = '';
                                let finalDutyInstanceIds = '';
                                if(dutyInstanceCount == 1){
                                    finalDutyInstanceIds = dutyData.InstanceIds;
                                } else {
                                    let nowDutyInstanceIds = [];
                                    for(let g=0; g<dutyInstanceCount; g++){
                                        if(g == 0) {
                                            dutyData.AllocationsDutyID = splitDutyInstance[g];
                                        }
                                        if(!nowDutyInstanceIds.includes(splitDutyInstance[g])){
                                            nowDutyInstanceIds.push(splitDutyInstance[g]);
                                        }
                                    }
                                    finalDutyInstanceIds = nowDutyInstanceIds.join(',');
                                }

								dutyData.StartTime = dutyData.StartTime ? dutyData.StartTime : 0;
								dutyData.EndTime = dutyData.EndTime ? dutyData.EndTime : 0;
                                let showCountCornerCls = '';
                                if(dutyInstanceCount > 1){
                                    showCountCornerCls = 'cornerIcon-Unalloc cornerIcon-Grey';
                                }
                                    let ondblclickCondUA = ' ondblclick="editAllocateDuty(\'editduty\',\'edit\', \'' + dutyData.AllocationsDutyID+'_'+cellDate + '\')" ';
                                    ungridhtml1week +='<div ' + ondblclickCondUA + ' id="rowUnqId_'+dutyData.AllocationsDutyID+'_'+cellDate+'" class="NotFixedon weekly-body-cell context-menu-unallocated ulrow_'+rowCounter+' unalloc-cell '+showCountCornerCls+'" ';
                                        let dutyDur = dutyData.Duration;
                                        let dutyHrs = parseFloat(dutyDur/3600).toFixed(2);
                                        ungridhtml1week +='title="'+dutyData.DutyName+' ('+dutyHrs+' Hours'+')" ';
                                        ungridhtml1week +='onmouseover="removetip()" ';
                                        ungridhtml1week +='data-date="'+cellDate+'" ';
                                        ungridhtml1week +='data-source="unallocated" ';
                                        ungridhtml1week +='data-id="'+dutyData.ID+'" ';
                                        ungridhtml1week +='data-duty-count="'+dutyData.DutyInstances+'" ';
                                        ungridhtml1week +='data-duty-name="'+dutyData.DutyName+'" ';
                                        ungridhtml1week +='data-scheduling-person="0" ';
                                        ungridhtml1week +='data-unique-id="'+dutyData.AllocationsDutyID+'_'+cellDate+'" ';
                                        ungridhtml1week +='data-scheduling-team-id="'+dutyData.SchedulingTeamId+'" ';ungridhtml1week +='data-row-uId="'+dutyData.uId+'" ';
                                        let unDutyStartTime = dutyData.StartTime;
                                        if(unDutyStartTime != 0){
                                            ungridhtml1week +='data-row-start="'+unDutyStartTime+'" ';
                                        } else {
                                            ungridhtml1week +='data-row-start="0" ';
                                        }
                                        let unDutyEndTime = dutyData.EndTime;
                                        if(unDutyEndTime != 0){
                                            ungridhtml1week +='data-row-end="'+unDutyEndTime+'" ';
                                        } else {
                                            ungridhtml1week +='data-row-end="0" ';
                                        }
                                        ungridhtml1week +='data-duty-instances="'+dutyData.DutyInstances+'" ';
                                        ungridhtml1week +='data-row-counter="'+rowCounter+'" ';
                                        ungridhtml1week +='data-cell-counter="'+blankCellCounter+'" ';
                                        ungridhtml1week +='data-duty-instance-ids="'+finalDutyInstanceIds+'" ';
                                        ungridhtml1week +='data-next-instance-id="0" ';
                                        ungridhtml1week +='data-remain-duty-instances="0" ';
                                        ungridhtml1week +='date-range-days="'+dateRangeDays+'" ';
                                        ungridhtml1week +='date-start-date="'+pData.startDate+'" ';
                                        ungridhtml1week +='date-end-date="'+pData.endDate+'">';
                                        ungridhtml1week +='<div id="colUnqId_'+dutyData.AllocationsDutyID+'_'+cellDate+'" class="unallocated dragAlloc unalloc-drag-drop textoverflow uldutycell_'+dutyData.DutyDate+' unalloc-cell-inner" ';
                                        ungridhtml1week +=(dutyData.DutyName == 'U') ? 'draggable="false" ' : 'draggable="true" ';
										dutyData.AllocationsSPID = dutyData.AllocationsSPID > 0 ? dutyData.AllocationsSPID : 0;
                                        ungridhtml1week +='data-date="'+cellDate+'" ';
                                        ungridhtml1week +='data-occupied=1 ';
                                        ungridhtml1week +='data-source="unallocated" ';
                                        ungridhtml1week +='data-id="'+dutyData.ID+'" ';
                                        ungridhtml1week +='data-duty-name="'+dutyData.DutyName+'" ';
                                        ungridhtml1week +='data-duty-count="'+dutyData.DutyInstances+'" ';
                                        ungridhtml1week +='data-scheduling-person="0" ';
                                        ungridhtml1week +='data-unique-id="'+dutyData.AllocationsDutyID+'_'+cellDate+'" ';
                                        ungridhtml1week +='data-scheduling-team-id="'+dutyData.SchedulingTeamId+'" ';
                                        ungridhtml1week +='data-allocations-duty-id="'+dutyData.AllocationsDutyID+'" ';
                                        ungridhtml1week +='data-allocations-sp-id="'+dutyData.AllocationsSPID+'" ';
                                        ungridhtml1week +='data-col-uId="'+dutyData.uId+'" ';
                                        ungridhtml1week +='data-is-need-covering="'+dutyData.IsNeedCovering+'" ';
                                        let unDutyStartTimeIn = dutyData.StartTime;
                                        if(unDutyStartTimeIn != 0){
                                            ungridhtml1week +='data-row-start="'+unDutyStartTimeIn+'" ';
                                        } else {
                                            ungridhtml1week +='data-row-start="0" ';
                                        }
                                        let unDutyEndTimeIn = dutyData.EndTime;
                                        if(unDutyEndTimeIn != 0){
                                            ungridhtml1week +='data-row-end="'+unDutyEndTimeIn+'" ';
                                        } else {
                                            ungridhtml1week +='data-row-end="0" ';
                                        }
                                        ungridhtml1week +='data-unique-id="'+dutyData.AllocationsDutyID+'_'+cellDate+'" ';
                                        ungridhtml1week +='data-duty-instances="'+dutyData.DutyInstances+'" ';
                                        ungridhtml1week +='data-row-counter="'+rowCounter+'" ';
                                        ungridhtml1week +='data-cell-counter="'+blankCellCounter+'" ';
                                        ungridhtml1week +='data-duty-instance-ids="'+finalDutyInstanceIds+'" ';
                                        ungridhtml1week +='data-next-instance-id="0" ';
                                        ungridhtml1week +='data-remain-duty-instances="0" ';
                                        ungridhtml1week +='date-range-days="'+dateRangeDays+'" ';
                                        ungridhtml1week +='date-start-date="'+pData.startDate+'" ';
                                        ungridhtml1week +='date-end-date="'+pData.endDate+'" ';
                                        ungridhtml1week +='data-iday="'+dutyData.iDay+'" ';
                                        ungridhtml1week +='data-duty-duration="'+dutyData.Duration+'">';
                                            ungridhtml1week +='<span class="dutyTip" style="overflow:hidden;">';
                                            let spanDutyName = dutyData.DutyName;
                                            if($('#showWeeks').val() == 1){
                                                ungridhtml1week +='<b>'+spanDutyName+'</b><br>';
                                            } else {
                                                ungridhtml1week +='<b>'+spanDutyName+'</b><br>';
                                            }
                                            if ((dutyData.DutyName != '') || (dutyData.DutyName != null)) {
                                                if((unDutyStartTimeIn != 0 && unDutyStartTimeIn != null) || (unDutyEndTimeIn != 0 && unDutyEndTimeIn != null)){
                                                    if(convertHMS(unDutyEndTimeIn) == '24:00'){
                                                        ungridhtml1week +=convertHMS(unDutyStartTimeIn)+' - 00:00';
                                                    } else {
                                                        ungridhtml1week +=convertHMS(unDutyStartTimeIn)+' - '+convertHMS(unDutyEndTimeIn);
                                                    }
                                                }
                                            }
                                            ungridhtml1week +='</span>';
                                        ungridhtml1week +='</div>';
                                        if(parseInt(dutyInstanceCount) > 1){
                                            ungridhtml1week += '<div class="DutyCountPositionRightTop" id="dutyCountDiv'+ dutyData.AllocationsDutyID + '_' + cellDate + '">'+dutyInstanceCount+'</div>';
                                        } else {
                                            ungridhtml1week += '<div class="DutyCountPositionRightTop" id="dutyCountDiv'+dutyData.AllocationsDutyID + '_' + cellDate + '"></div>';
                                        }
                                    ungridhtml1week +='</div>';
                                } else {
                                    ungridhtml1week +='<div id="rowUnqId_'+cellDate+'_'+rowCounter+'_'+blankCellCounter+'" class="NotFixedon weekly-body-cell unallocate-empty-' + cellDate + ' ulrow_'+rowCounter+' unalloc-cell" data-order="U-">';
                                        ungridhtml1week +='<div id="colUnqId_'+cellDate+'_'+rowCounter+'_'+blankCellCounter+'" class="unallocated textoverflow unallocother-drag-drop uldutycell_'+cellDate+'" data-occupied=0 data-source="unallocated" data-date="'+cellDate+'" data-scheduling-person="0" data-row-counter="'+rowCounter+'" data-cell-counter="'+blankCellCounter+'" data-unique-id="'+cellDate+'_'+rowCounter+'_'+blankCellCounter+'" date-range-days="'+dateRangeDays+'" date-start-date="'+pData.startDate+'" date-end-date="'+pData.endDate+'">';
                                            ungridhtml1week +='<span> &nbsp;<br></span> ';
                                        ungridhtml1week +='</div>';
                                    ungridhtml1week +='</div>';
                                }
                                blankCellCounter+1;
                            }
                        ungridhtml1week +='</div>';
                        rowCounter = rowCounter+200;
                }
                ungridhtml1week +='</div>';
            ungridhtml1week +='</div>';
        ungridhtml1week +='</div>';
    ungridhtml1week +='</div>';
    //setTimeout(function() {setBlockScrollPositions()}, 100);
    if(showDiv == 1){
        $('#unallocatedDuty').hide();
        $('#allocatedDuty').hide();
        $('#unallocatedDuty').html(ungridhtml1week);
        $("#loadingWeekly").show();
    } else {
        $('#unallocatedDuty').html(ungridhtml1week);
        $("#loadingWeekly").hide();
    }
    if(ungridhtml1week != ''){
        checkWeekAndRole('UNALLOC');
        if($('#showWeeks').val() > 1){
            $('#weeklyUnAllocation-2').css('overflow-x', 'scroll');
        }

        if($.cookie('unallocgridheight') != undefined){
            $("#unallocatedDuty").height($.cookie('unallocgridheight'));
            $("#allocatedDuty").height($.cookie('allocgridheight'));
            $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
            $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
            $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
            $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
            setTimeout(function() {
                $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
            }, 1000);
            if($('#showHideCount').prop('checked') == false){
                let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                $("#allocatedDuty").height(allocatedHeight);
                $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
            }
        }
        $('#unallocatedDuty .block-25').css('max-height', $("#unallocatedDuty").height() - 26);
        return true;
    } else {
        return false;
    }
}

function IndexToAssociativeArray (DutyData,Type) {
    let dutyDataAssociative = [];
    if ((Type == 'Unalloc') && (DutyData != '')) {
        DutyData.forEach((number, index, rows) => {
            dutyDataAssociative[index] = {
                DutyName : rows[index][0],
                Duration : rows[index][1],
                WeekNumber : rows[index][2],
                iDay : rows[index][3],
                StartTime : rows[index][4],
                EndTime : rows[index][5],
                ID : rows[index][6],
                SchedulingTeamId : rows[index][7],
                SchedulingPersonID : rows[index][8],
                DutyDate : rows[index][9],
                MasterDutyId : rows[index][10],
                isActive : rows[index][11],
                DutyTeamID : rows[index][12],
                DisplayGrid : rows[index][13],
                DutyInstances : rows[index][14],
                AllocationsDutyID : rows[index][15],
                AllocationsSPID : rows[index][16],
                uId : rows[index][17],
				IsNeedCovering : rows[index][18],
                InstanceIds : rows[index]['InstanceIds']
            };

        });
    } else {
        if (DutyData != '') {
            DutyData.forEach((number, index, rows) => {
                dutyDataAssociative[index] = {
                    StaffNumber : rows[index][0],
                    DutyName : rows[index][1],
                    Duration : rows[index][2],
                    WeekNumber : rows[index][3],
                    iDay : rows[index][4],
                    StartTime : rows[index][5],
                    EndTime : rows[index][6],
                    SortCode : rows[index][7],
                    DutyComments : rows[index][8],
                    PersonComments : rows[index][9],
                    MarkedOvertime : rows[index][10],
                    MarkedSickness : rows[index][11],
                    UnAllocated : rows[index][12],
                    ID : rows[index][13],
                    SchedulingTeamId : rows[index][14],
                    SchedulingPersonID : rows[index][15],
                    DutyDate : rows[index][16],
                    isPublished : rows[index][17],
                    IsHomeTeam : rows[index][18],
                    MarkWiad : rows[index][19],
                    MarkActual : rows[index][20],
                    isAttentionClsName : rows[index][21],
                    isRequest : rows[index][22],
                    dutyBreakTime : rows[index][23],
                    dutyColorId : rows[index][24],
                    MasterDutyId : rows[index][25],
                    isActive : rows[index][26],
                    isEditable : rows[index][27],
                    pay : rows[index][28],
                    DisplayName : rows[index][29],
                    DisplayLastName : rows[index][30],
                    DisplayFirstName : rows[index][31],
                    schedulingTeamName : rows[index][32],
                    IsSigninAllowed : rows[index][33],
                    Signindays : rows[index][34],
                    EFT : rows[index][35],
                    ACC : rows[index][36],
                    ContractedHours : rows[index][37],
                    AccDays : rows[index][38],
                    OverTimeHrs : rows[index][39],
                    ManualEDP : rows[index][40],
                    signin : rows[index][41],
                    AllowInBuilding : rows[index][42],
                    ActionNameForSignin : rows[index][43],
                    ImageNameSignin : rows[index][44],
                    ColourBackground : rows[index][45],
                    ColourFont : rows[index][46],
                    PersonBackgroundColour : rows[index][47],
                    PersonFontColour : rows[index][48],
                    WeekDuration : rows[index][49],
                    AccPeriod : rows[index][50],
                    CostCode : rows[index][51],
                    StaffID : rows[index][52],
                    TriangleColour : rows[index][53],
                    CountLeave : rows[index][54],
                    LeaveApproved : rows[index][55],
                    LeaveDeleted : rows[index][56],
                    LeaveShortNotice : rows[index][57],
                    Leaveoversummer : rows[index][58],
                    LeaveisOK : rows[index][59],
                    EditDuty : rows[index][60],
                    ShowLock : rows[index][61],
                    LockIconColour : rows[index][62],
                    ReqCount : rows[index][63],
                    ShowEDPIcon : rows[index][64],
                    ShowWIAD : rows[index][65],
                    WTDBreachClassName : rows[index][66],
                    contextMenuClsName : rows[index][67],
                    DisplayGrid : rows[index][68],
                    MarkOverTwelve : rows[index][69],
                    IsUnderElevenBreak : rows[index][70],
                    IsUnderElevenBreakOverride : rows[index][71],
                    DutyTeamID : rows[index][72],
                    IsDutyFromOtherTeam : rows[index][73],
                    dutyProgramId : rows[index][74],
                    DutyInstances : rows[index][75],
                    pdlStartTime : (rows[index][76] == '' || rows[index][76] == null) ? 0 : rows[index][76],
                    pdlEndTime : (rows[index][77] == '' || rows[index][77] == null) ? 0 : rows[index][77],
                    leavePDL : (rows[index][78] == '' || rows[index][78] == null) ? 0 : rows[index][78],
					FWANotesFlag : (rows[index][79] == '' || rows[index][79] == null) ? 0 : rows[index][79],
                    NetLogin : (rows[index][80] == '' || rows[index][80] == null) ? 0 : rows[index][80],
                    LeaveID : (rows[index][81] == '' || rows[index][81] == null) ? 0 : rows[index][81],
                    IsAgreed: rows[index][87],
                    IsNeedCovering: rows[index][88],
                    ActingFlag: rows[index][89],
                    TotalPlannedDuration: (rows[index][90] ?? 0),
					AllocationsDutyID : rows[index][92],
					AllocationsSPID : rows[index][93],
					uId : rows[index][94],
                    dutyLabels: [rows[index][74], rows[index][82], rows[index][83], rows[index][84], rows[index][85], rows[index][86]]
                };
            });
        }
    }
    return dutyDataAssociative;
}

function removeDateManually(event){
    if($('#date').val() == ''){
        var getCookieWeekNumber = $.cookie("cookieWeekNumber");
        if (getCookieWeekNumber == undefined) {
            $('#wcurrent').trigger('click');
        } else {
            $('#ui-datepicker-div').hide();
            $('#weekNumber').val(decodeWeekNum(getCookieWeekNumber));
            $('#newAvailablePerson').val('Yes');
            var request = $("#editWeeklyAllocations").serialize();
            $.ajax({
                type: "post",
                url: "/page-includes/allocations/weekly/period-view.php",
                data: request,
                success: function (data) {
                    if(parseInt($('#selFilterId').val()) > 0){
                        $('#content').html(data);
                        applyViewFilter('EditWeekly',$('#selFilterId').val(),'Yes');
                    } else {
                        $('#content').html(data);
                    }
                    $('#facebox .close').click();
                    if($('#disableDrag').prop('checked') == true){
                        disableDragDrop();
                    }
                }
            });
        }
    }
}

function decodeWeekNum(encodedString) {
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
    return textArea.value;
}

function customConfirmFacebox(msg, yesCallback, noCallback, delayInMilliSec=0,faceBoxClose='Yes')
{
    let messageContainer = '<div style="width: 600px"><table id="customConfirm" class="smalltable bluetable" width="100%"><thead><tr><th colspan="5">Please Confirm</th></tr></thead><tbody><tr><td style="padding:10px;">'+msg+'</td></tr><tr style="text-align:right;"><td><input name="yes" id="yes" type="submit" value="Yes" >&nbsp;&nbsp;&nbsp;&nbsp<input name="no" id="no" type="submit" value="No"></td></tr></tbody></table></div><div class="clear"></div>';
    if(delayInMilliSec > 0){
        setTimeout(function() {$.facebox(messageContainer);}, delayInMilliSec);
    } else {
        $.facebox(messageContainer);
    }
    $('#yes').click(function() {
        $.facebox.close();
        yesCallback();
    });
    $('#no').click(function() {
        if(faceBoxClose == 'Yes'){
            $.facebox.close();
        }
        noCallback();
    });
}

function unsetShiftCountingFilter(teamId){
    var dataReq = {
        'action':'Clearshiftcountfilter',
        'teamId':teamId
    };
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
        data: dataReq,
        success: function (data) {
            let respData = $.parseJSON(data);
            if(respData.success == true){
                $('#selShiftCountingFilterId').val(0);
                $('#dutyCountFilterCols').val('');
            }
        }
    });
}

function getAssociativeArraySize(array){
    let size = 0;
    for (let key in array) {
        if (array.hasOwnProperty(key)) {
            size++;
        }
    }
    return size;
}

function getUnAllocatedSort(thisVal, changeSortType = false, scrollToId = null)
{
	$("#loadingWeekly").show();
	let currentSortDate = parseInt(thisVal);
	let prevSortDate = parseInt($('#unAllocSortDate').val());
	let prevSortType = parseInt($('#unAllocSortType').val());
	let prevSortTypeNew = '';
	prevSortTypeNew = (prevSortType == 0) ? 1 : 0;
	if( (parseInt(currentSortDate) == parseInt($.cookie('unAllocSortDate'))) && (changeSortType) )
	{

		$('#unAllocSortType').val(prevSortTypeNew);
	}else if(changeSortType && (prevSortTypeNew != prevSortType) )
	{
        $('#unAllocSortDate').val(currentSortDate);
        $('#unAllocSortType').val(prevSortTypeNew);
	}
	$.cookie('unAllocSortDate', currentSortDate);
	$.cookie('unAllocSortType', prevSortTypeNew);
	window.indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
	if (!window.indexedDB)
	{
		console.log("Your browser doesn't support a stable version of IndexedDB.");
	}else
	{
		const request = window.indexedDB.open("unAllocDatabase", 3);
		request.onsuccess = function () {
		    const db = request.result;
            if(db.objectStoreNames.length){
                const transaction = db.transaction("weeklyData", "readwrite");
                const store = transaction.objectStore("weeklyData");
                const idQuery = store.get(1);
                idQuery.onsuccess = function () {
                    let scrollLeftPos = $('#weeklyAllocation-2').scrollLeft();
                    if($('#unAllocSortType').val() == 0)
                    {//case of assending
                        let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
                        let sortedArray = [];
                        let dataArr1= dataArr;
                        let weekNo = $('#weekNumber').val();
                        let weekNoArr = weekNo.split('/');
                        weekNo = weekNoArr[1]+weekNoArr[0];
                        if(dataArr.length > 0){
                            dataArr.forEach((number, index, rows) => {
                                if(($('#date').val() != '') && (rows[index][3] == currentSortDate)){
                                    sortedArray.push(rows[index]);
                                    delete dataArr1[index];
                                } else {
                                    if((rows[index][3] == currentSortDate) && (parseInt(rows[index][2]) == parseInt(weekNo)))
                                    {
                                        sortedArray.push(rows[index]);
                                        delete dataArr1[index];
                                    }
                                }
                            });

                            for(let i =0; i < (sortedArray.length - 1); i++)
                            {
                                for(let j = i+1; j < (sortedArray.length); j++)
                                {
                                    if((sortedArray[i][0] + sortedArray[i][4] + sortedArray[i][5]).toLowerCase() > (sortedArray[j][0] + sortedArray[j][4] + sortedArray[j][5]).toLowerCase())
                                    {
                                        let temp = sortedArray[i];
                                        sortedArray[i] = sortedArray[j];
                                        sortedArray[j] = temp;
                                    }
                                }
                            }
                            var sortedArr = sortedArray.concat(dataArr1);
                        } else {
                            var sortedArr = '';
                        }


                        let miscDutyData = ($('#miscDutyData').val() == '') ? {} : JSON.parse($('#miscDutyData').val());
                        let dataPickerData = ($('#dataPickerData').val() == '') ? {} : JSON.parse($('#dataPickerData').val());
                        if(unallocatedgridhtml(sortedArr, miscDutyData, dataPickerData)){
                            showHideCountGrid();
                            if($.cookie('unallocgridheight') != undefined){
                                $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                                $("#allocatedDuty").height($.cookie('allocgridheight'));
                                $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                                $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                                $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                                $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 11);
                                if($('#showHideCount').prop('checked') == false){
                                    let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                    $("#allocatedDuty").height(allocatedHeight);
                                    $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                                }
                            }

                            $('.dragAlloc').on('dragstart', function (event) {
                                dragSource = $(this);
                                event.stopImmediatePropagation();
                                startDrag(dragSource);
                            });
                            $('.dropeventscall').on('over', function (event) {
                                event.stopImmediatePropagation();
                                event.preventDefault();
                            });
                            if($('#showHideCount').prop('checked') == true){
                                reloadShiftCountingGrid();
                            }
                            if($('#disableDrag').prop('checked') == true){
                                disableDragDrop();
                            }
                            resizeUnallocatedGrid();
                            $('.scrollHorizontal').scroll(function() {
                                $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                            });
                            $('.scrollVertical').scroll(function() {
                                $('.scrollVertical').scrollTop($(this).scrollTop());
                            });
                            $("#loading").hide();
                        }
                    }else
                    {//case of decending
                        let dataArr = idQuery.result.mainData ? idQuery.result.mainData : [];
                        let sortedArray = [];
                        let dataArr1= dataArr;
                        let weekNo = $('#weekNumber').val();
                        let weekNoArr = weekNo.split('/');
                        weekNo = weekNoArr[1]+weekNoArr[0];
                        if(dataArr.length > 0){
                            dataArr.forEach((number, index, rows) => {
                                if(($('#date').val() != '') && (rows[index][3] == currentSortDate)){
                                    sortedArray.push(rows[index]);
                                    delete dataArr1[index];
                                } else {
                                    if((rows[index][3] == currentSortDate) && (parseInt(rows[index][2]) == parseInt(weekNo)))
                                    {
                                        sortedArray.push(rows[index]);
                                        delete dataArr1[index];
                                    }
                                }
                            })
                            for(let i =0; i < (sortedArray.length - 1); i++)
                            {
                                for(let j = i+1; j < (sortedArray.length); j++)
                                {
                                    if((sortedArray[i][0] + sortedArray[i][4] + sortedArray[i][5]).toLowerCase() < (sortedArray[j][0] + sortedArray[j][4] + sortedArray[j][5]).toLowerCase())
                                    {
                                        let temp = sortedArray[i];
                                        sortedArray[i] = sortedArray[j];
                                        sortedArray[j] = temp;
                                    }
                                }
                            }
                            var sortedArr = sortedArray.concat(dataArr1);
                        } else {
                            var sortedArr = '';
                        }

                        let miscDutyData = ($('#miscDutyData').val() == '') ? {} : JSON.parse($('#miscDutyData').val());
                        let dataPickerData = ($('#dataPickerData').val() == '') ? {} : JSON.parse($('#dataPickerData').val());
                        if(unallocatedgridhtml(sortedArr, miscDutyData, dataPickerData)){
                            showHideCountGrid();
                            if($.cookie('unallocgridheight') != undefined){
                                $("#unallocatedDuty").height($.cookie('unallocgridheight'));
                                $("#allocatedDuty").height($.cookie('allocgridheight'));
                                $('#miscDutyBlock').css('max-height', $("#unallocatedDuty").height() - 26);
                                $('#weeklyUnAllocation-2').css('max-height', $("#unallocatedDuty").height());
                                $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', $("#allocatedDuty").height());
                                $('.showCountContainer').css('max-height', $("#showCountBlock").height() - 15);
                                setTimeout(function() {
                                    $('#showCountRight').css('max-height', $("#showCountBlock").height()-15);
                                    $('#showCountLeft').css('max-height', $("#showCountBlock").height()-15);
                                }, 1000);
                                if($('#showHideCount').prop('checked') == false){
                                    let allocatedHeight = parseInt($.cookie('allocgridheight')) + parseInt($.cookie('shiftgridheight'));
                                    $("#allocatedDuty").height(allocatedHeight);
                                    $('#weeklyAllocation-1, #weeklyAllocation-2').css('max-height', allocatedHeight);
                                }
                            }

                            $('.dragAlloc').on('dragstart', function (event) {
                                dragSource = $(this);
                                event.stopImmediatePropagation();
                                startDrag(dragSource);
                            });
                            $('.dropeventscall').on('over', function (event) {
                                event.stopImmediatePropagation();
                                event.preventDefault();
                            });
                            if($('#showHideCount').prop('checked') == true){
                                reloadShiftCountingGrid();
                            }
                            if($('#disableDrag').prop('checked') == true){
                                disableDragDrop();
                            }
                            resizeUnallocatedGrid();
                            $('.scrollHorizontal').scroll(function() {
                                $('.scrollHorizontal').scrollLeft($(this).scrollLeft());
                            });
                            $('.scrollVertical').scroll(function() {
                                $('.scrollVertical').scrollTop($(this).scrollTop());
                            });
                            $("#loading").hide();
                        }
                    }
                    $('#weeklyUnAllocation-2').scrollLeft(scrollLeftPos);
                    $('#weeklyAllocation-1').scrollLeft(Math.abs($.cookie("teamPeopleBlockPosLeft")) + 2);
                    if($('#showHideCount').prop('checked') == true){
                        $('#showCountRight').scrollLeft(scrollLeftPos);
                    }
                };
                transaction.oncomplete = function () {
                    db.close();
                    if(scrollToId != null) {
                        scrollToUnallocatedElement(scrollToId);
                    }
                };
            } else {
                $('#loading').hide();
                $('#loadingWeekly').hide();
                customAlert("Please close and reopen your browser");
            }
		};
	}
}

function scrollToUnallocatedElement(scrollToId) {
    let updatedElement = document.getElementById('rowUnqId_' + scrollToId);
    let $scrollBody = $('#weeklyUnAllocation-2');
    updatedElement.scrollIntoView();
    if (Math.abs(Math. ceil($scrollBody.outerHeight()) - Math.ceil($scrollBody[0].scrollHeight - $scrollBody.scrollTop())) < 10) { //Scrolled to bottom
        return false;
    }
    $scrollBody.scrollTop(Math.abs($scrollBody.scrollTop() - 40));
}

function durationCalculationforEditDuty(){
    var startTimeEnabledDisabled = $('#startEndDisable').val();
    if(startTimeEnabledDisabled == 'Yes'){
        $("#starttimemasterduty").attr("disabled","disabled").val('--:--');
        $("#endtimemasterduty").attr("disabled","disabled").val('--:--');
        $('#startTimeStar').hide();
        $('#endTimeStar').hide();
        $("#breakTimeHour").prop('disabled',true).trigger("chosen:updated");
        $("#breakTimeMinute").prop('disabled',true).trigger("chosen:updated");
        $("#Duration").attr('disabled',false);
    } else {
        $("#Duration").attr("disabled",true);
        let breakTimeHrSec = parseInt($('#breakTimeHour').val()) * 60 * 60;
        let breakTimeMinSec = parseInt($('#breakTimeMinute').val()) * 60;
        let fnlBreakTimeSec = parseInt(breakTimeHrSec)+parseInt(breakTimeMinSec);
        let finalDurationSec = 0;
        if($("#starttimemasterduty").val() == $("#endtimemasterduty").val()){
            finalDurationSec = 86400 - fnlBreakTimeSec;

            let sec = parseInt(finalDurationSec, 10);
            let hours   = Math.floor(sec / 3600);
            let minutes = Math.floor((sec - (hours * 3600)) / 60);

            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}

            $("#Duration").val(hours+':'+minutes);
        }
        $('#breakTimeHour').prop('disabled', false);
        $('#breakTimeMinute').prop('disabled', false)
    }
}

function getRowCounterUnallocated(counterVal){
    let ulrow = $('.ulrow_'+counterVal).length;
    if(ulrow == 0){
        return counterVal;
    } else {
        return getRowCounterUnallocated(counterVal+1);
    }
}

(function($) {
    $.fn.hasScrollBar = function() {
        if(this.length > 0) {
            return this.get(0).scrollHeight > this.height();
        }
    }
})(jQuery);

function checkUnallocatedSectionHeight(){
    let unGridHeight = parseInt($('#weeklyUnAllocation-2').height());
    let unHeaderHeight = parseInt($('.unallocatedDatesHeader').height());
    let unDutiesHeight = parseInt($('.unAllocateddTableBody').height());
    let unJoinHeight = parseInt(unHeaderHeight + unDutiesHeight);
    if(unGridHeight < unJoinHeight){
        return true;
    } else {
        return false;
    }
}

function showSessionBlankMsg(){
    customAlertByModel('Your session has been interrupted. Please reload the week.');
}

function openViewDaily(clickDate, teamId){
    clickDate = $.trim(clickDate);
    ShowDailyAllocations(teamId,clickDate,'',1);
}

function hideSickHrError(){
    $('#durationerror').hide();
}

function convertSecondsIntoTime(secondsDuration, seperator = ':',displayHours='Yes'){
    let h = Math.floor(secondsDuration / 3600);
    let m = Math.floor(secondsDuration % 3600 / 60);

    let hDisplay = h;
    let mDisplay = m.toString();
    if(seperator == ':'){
        hDisplay = 0;
        if(h <= 9){
            hDisplay = '0'+h;
        } else {
            if(h > 24){
                if((h - 24) <= 9){
                    hDisplay = '0'+h;
                } else {
                    hDisplay = h;
                }
            } else {
                if(h == 24){
                    hDisplay = '00';
                } else {
                    hDisplay = h;
                }
            }
        }
        mDisplay = (m <= 9) ? '0'+m.toString() : m.toString();
    }

    if(seperator == '.'){
        switch(mDisplay){
            case "15":
                mDisplay = '25';
                break;
            case "30":
                mDisplay = '5';
                break;
            case "45":
                mDisplay = '75';
                break;
            default:
                mDisplay = '00';
        }
    }
    if(displayHours == 'Yes'){
        return hDisplay+seperator+mDisplay+' hrs';
    } else {
        return hDisplay+seperator+mDisplay;
    }
}

function openProductionView(){
    let splitWeekNum =$('#weekNumber').val().split('/');
	let weekNum = splitWeekNum[1]+splitWeekNum[0];
	let teamId = $('#searchTeamId').val();
	ShowAllocationsDuties(teamId,weekNum);
}

function getSchedulingNotes(schPersonId)
{
	$.ajax({
		type: "post",
		url: "/page-includes/allocations/weekly/actions/get-scheduling-notes.php",
		data: {schPersonId:schPersonId},
		success: function (data) {
			$('#schedulingNotesContainer').html(data);
			$('#schedulingNotesContainer').show('slow');
			let topVar = 100;
			if(data.length > 400)
			{
				topVar = 250;
			}
			let bottomvar =0;
			if((($(window).height()) - ($('#'+schPersonId+'_triangle').offset().top))<100){
				bottomvar = 150;
			}
			let teamPeopleTop = $('#'+schPersonId+'_triangle').offset().top - topVar - bottomvar;
			let teamPeopleLeft = $('#'+schPersonId+'_triangle').offset().left + 20;
			$('#schedulingNotesContainer').css({ top: teamPeopleTop, left: teamPeopleLeft });
		}
	});
}

function openLeavePopup(dataAttr){
    let user = dataAttr.attr('data-scheduled-netlogin');
    let userLeaveId = (dataAttr.attr('data-leave-id') == 'null' || dataAttr.attr('data-leave-id') == null || dataAttr.attr('data-leave-id') == '') ? 0 : parseInt(dataAttr.attr('data-leave-id'));
    let weekNumYear = $('#weekNumber').val();
    let dutyDate = dataAttr.attr('data-date');
    let dataSchedulingPerson = dataAttr.attr("data-scheduling-person");
    let dataId = dataAttr.attr("data-id");
    let dataDutyName = dataAttr.attr("data-duty-name");
    let dataRowStart = dataAttr.attr("data-row-start");
    let dataRowEnd = dataAttr.attr("data-row-end");
    let dataUniqueId = dataAttr.attr("data-unique-id");

    if((weekNumYear != undefined) && (weekNumYear != '')){
        var splitWeekNum = weekNumYear.split('/');
        var selectedyear = splitWeekNum[1];
    }
    $.ajax({
        url:"page-includes/allocations/weekly/actions/edit-weekly-manage-leave.php",
        type:'POST',
        data:{
            'actionname':'checkgroup',
            'scheduledNetLogin':user,
            'leaveId' : userLeaveId
        },
        success:function(response,status,http) {
            response = $.parseJSON(response);
            if((response.status == false) && (response.message != '')){
                customAlert(response.message);
            } else {
                if((user != undefined) && (user != 0) && (user != '') && (selectedyear != undefined) && (selectedyear != '') && (userLeaveId == 0)) {
                    $.ajax({
                        url:"page-includes/leave/edit-weekly-new-leave-popup.php",
                        type:'POST',
                        data:{
                            'userNetlogin': user,
                            'LeaveYear': selectedyear,
                            'dutyDate': dutyDate,
                            'data-scheduling-person': dataSchedulingPerson,
                            'data-row-id': dataId,
                            'data-duty-name': dataDutyName,
                            'data-row-start': dataRowStart,
                            'data-row-end': dataRowEnd,
                            'data-unique-id': dataUniqueId
                        },
                        success:function(response,status,http) {
                            $.facebox(response);
                        },
                        error: function(http,status,error){
                            customAlert("Error Found :" + error);
                        }
                    });
                } else {
                    if((userLeaveId > 0) && (userLeaveId != undefined)){
                        openManagePopupEditWeekly(userLeaveId,selectedyear,dataSchedulingPerson,dataId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dutyDate);
                    }
                }
            }
        },
        error: function(http,status,error){
            customAlert("Error Found :" + error);
        }
    });
}

function openManagePopupEditWeekly(leaveid,week,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDDate)
{
    if (leaveid!=undefined && week!=undefined && leaveid!='' && week!='') {
        $.ajax({
            type: 'POST',
            url: 'page-includes/leave/edit-weekly-leave-approve-popup.php',
            data: {
                'id': leaveid,
                'week': week,
                'callpage':'yearly',
                'dataSchedulingPerson':dataSchedulingPerson,
                'dataRowId':dataRowId,
                'dataDutyName':dataDutyName,
                'dataRowStart':dataRowStart,
                'dataRowEnd':dataRowEnd,
                'dataUniqueId':dataUniqueId,
                'dataDDate':dataDDate
            },
            success: function (data) {
                $.facebox(data);
            },
            error:function (data) {
                customAlert('some error found in leave approve popup call.');
            }
        });
    }
}

function copyDuty(dataCellInst){
    let copyDutyId = dataCellInst.attr('data-allocations-duty-id');
    let dutyDate = dataCellInst.attr('data-date');
    let schedulingTeamId = dataCellInst.attr('data-scheduling-team-id');
    let dutyName = dataCellInst.attr('data-duty-name');
    let schedulePersonId = dataCellInst.attr('data-scheduling-person');

    dialogForm = $('#dialog-form-copy-duty');
    $("#loading").hide();
    dialogForm.html('');
    copyDutyDialog = dialogForm.dialog({
        autoOpen: false,
        height: 300,
        width: 500,
        modal: true,
        title: "Copy Duty ("+dutyName+" - "+formatDateForHeader(dutyDate)+")",
        buttons: {
            "Copy Duty": function() {
                $('#errorDiv').hide();
                $('#successDiv').hide();
                $("#loading").hide();
                let schedulingTeamId = $('#copyDutyTeamId').val();
                let fromDate = $('#startDateCopyDuty').val();
                let toDate = $('#endDateCopyDuty').val();
                let schedulingPersonId = $('#schedulingPersonId').val();
                if((schedulingTeamId == '') || (fromDate == '') || (toDate == '')){
                    $('#errorDiv').html('Select scheduling team and dates');
                    $('#errorDiv').show();
                } else {
                    $.ajax({
                        type: 'POST',
                        url: 'page-includes/allocations/weekly/actions/copy-duty-actions.php',
                        data: {
                            'actionname': 'checkdutyexists',
                            'schedulingTeamId': schedulingTeamId,
                            'fromDate': fromDate,
                            'toDate': toDate,
                            'schedulingPersonId': schedulingPersonId
                        },
                        success: function (respData) {
                            respData = $.parseJSON(respData);
                            if(respData.status == true){
                                $.ajax({
                                    type: 'POST',
                                    url: 'page-includes/allocations/weekly/actions/copy-duty-actions.php',
                                    data: {
                                        'actionname': 'copyduty',
                                        'schedulingTeamId': schedulingTeamId,
                                        'fromDate': fromDate,
                                        'toDate': toDate,
                                        'schedulingPersonId': schedulingPersonId,
                                        'copyDutyId': $('#copyDutyId').val()
                                    },
                                    success: function (data) {
                                        data = $.parseJSON(data);
                                        if(data.status == "0"){
                                            let screenWeekNum = $('#weekNumber').val();
                                            let splitWeekNum = screenWeekNum.split('/');
                                            let weekNumber = splitWeekNum[1]+splitWeekNum[0];

                                            if(parseInt(data.weekNum) == parseInt(weekNumber)){
                                                if(parseInt(schedulingPersonId) == 0){
                                                    reloadeditweeklygrid('','','','','UNALLOC');
                                                } else {
                                                    reloadeditweeklygrid('','','','','ALL');
                                                }
                                            }
                                            copyDutyDialog.html('');
                                            copyDutyDialog.dialog("close");
                                            copyDutyDialog.dialog('destroy');
                                            customAlert('Duty has been copied');
                                        } else {
                                            let splitMessage = data.message.split('-');
                                            let errMessage = '';
                                            if(splitMessage[1] != undefined){
                                                if($.trim(splitMessage[1]) == 'Cannot assign a Duty to Absent/Sick/Leave Days'){
                                                    errMessage = 'Duties cannot be copied onto days with Charging, or those marked as Absent, Sick, Leave or Overtime.';
                                                } else {
                                                    errMessage = splitMessage[1];
                                                }
                                            } else {
                                                if($.trim(data.message) == 'Cannot assign a Duty to Absent/Sick/Leave Days'){
                                                    errMessage = 'Duties cannot be copied onto days with Charging, or those marked as Absent, Sick, Leave or Overtime.';
                                                } else {
                                                    errMessage = data.message;
                                                }
                                            }
                                            $('#errorDiv').html($.trim(errMessage));
                                            $('#errorDiv').show();
                                        }
                                    }
                                });
                            } else {
                                customConfirmModal('Any scheduled duties in the range will be moved to Unallocated Duties.',
                                    function(){
                                        $.ajax({
                                            type: 'POST',
                                            url: 'page-includes/allocations/weekly/actions/copy-duty-actions.php',
                                            data: {
                                                'actionname': 'copyduty',
                                                'schedulingTeamId': schedulingTeamId,
                                                'fromDate': fromDate,
                                                'toDate': toDate,
                                                'schedulingPersonId': schedulingPersonId,
                                                'copyDutyId': $('#copyDutyId').val()
                                            },
                                            success: function (data) {
                                                data = $.parseJSON(data);
                                                if(data.status == "0"){
                                                    let screenWeekNum = $('#weekNumber').val();
                                                    let splitWeekNum = screenWeekNum.split('/');
                                                    let weekNumber = splitWeekNum[1]+splitWeekNum[0];

                                                    if(parseInt(data.weekNum) == parseInt(weekNumber)){
                                                        if(parseInt(schedulingPersonId) == 0){
                                                            reloadeditweeklygrid('','','','','UNALLOC');
                                                        } else {
                                                            reloadeditweeklygrid('','','','','ALL');
                                                        }
                                                    }
                                                    copyDutyDialog.html('');
                                                    copyDutyDialog.dialog("close");
                                                    copyDutyDialog.dialog('destroy');
                                                    customAlert('Duty has been copied');
                                                } else {
                                                    let splitMessage = data.message.split('-');
                                                    let errMessage = '';
                                                    if(splitMessage[1] != undefined){
                                                        if($.trim(splitMessage[1]) == 'Cannot assign a Duty to Absent/Sick/Leave Days'){
                                                            errMessage = 'Duties cannot be copied onto days with Charging, or those marked as Absent, Sick, Leave or Overtime.';
                                                        } else {
                                                            errMessage = splitMessage[1];
                                                        }
                                                    } else {
                                                        if($.trim(data.message) == 'Cannot assign a Duty to Absent/Sick/Leave Days'){
                                                            errMessage = 'Duties cannot be copied onto days with Charging, or those marked as Absent, Sick, Leave or Overtime.';
                                                        } else {
                                                            errMessage = data.message;
                                                        }
                                                    }
                                                    $('#errorDiv').html($.trim(errMessage));
                                                    $('#errorDiv').show();
                                                }
                                            }
                                        });
                                    },
                                    function() {
                                    },
                                    0,
                                    'EditWeeklyCopyDuty'
                                );
                            }
                        }
                    });
                }
            },
            Cancel: function() {
                $("#loading").hide();
                copyDutyDialog.html('');
                copyDutyDialog.dialog("close");
                copyDutyDialog.dialog('destroy');
            }
        }
    });

    if((dutyDate != '') && (dutyDate != undefined) && (schedulingTeamId != '') && (schedulingTeamId != undefined)){
        removetip();
        $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/weekly/actions/copy-duty-actions.php',
            data: {
                'actionname': 'getcopydutyenddate',
                'schedulingTeamId': schedulingTeamId,
                'dutyDate': dutyDate,
            },
            success: function (respData) {
                respData = $.parseJSON(respData);
                if(respData.status == true){
                    $.ajax({
                        type: 'POST',
                        url: 'page-includes/allocations/weekly/modals/copy-duty-modal.php',
                        data: {
                            'copyDutyId': copyDutyId,
                            'schedulingTeamId': schedulingTeamId,
                            'dutyDate': dutyDate,
                            'maxEndDate': respData.maxEndDate,
                            'schedulePersonId': schedulePersonId
                        },
                        beforeSend: function(jqXHR, settings){
                            $("#loading").hide();
                        },
                        success: function (response) {
                            copyDutyDialog.html(response).dialog({
                                modal: true
                            }).dialog('open');
                            $(".chosen-select").chosen();
                            $(".chosen-selectWeekly").chosen();
                        }
                    });
                }
            }
        });
    }
}

function checkWeekAndRole(type=''){
    let weekNumYear = $('#weekNumber').val();
    if((weekNumYear != undefined) && (weekNumYear != '')){
        var splitWeekNum = weekNumYear.split('/');
        var selectedyear = splitWeekNum[1];
        var selectedweek = splitWeekNum[0];
    }
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/weekly/actions/check-archive-data.php',
        data: {
            'action': 'checkweekandrole',
            'weeknum': $('#getWeekNumber').val(),
            'teamId' : $('#searchTeamId').val()
        },
        success: function (resp) {
            resp = $.parseJSON(resp);
            if(type.toUpperCase() == 'UNALLOC'){
                if((resp.screenView == 1) && (resp.screenEdit == 0)){
                    $('.unalloc-cell-inner').attr('data-edit-screen', '0');
                    $('.indicator-cls').attr('data-edit-screen', '0');
                } else if((resp.screenView == 0) && (resp.screenEdit == 1)){
                    $('.unalloc-cell-inner').attr('data-edit-screen', '1');
                    $('.indicator-cls').attr('data-edit-screen', '1');
                } else {
                    $('.unalloc-cell-inner').attr('data-edit-screen', '0');
                    $('.indicator-cls').attr('data-edit-screen', '0');
                }
            }

            if(type.toUpperCase() == 'ALLOC'){
                if((resp.screenView == 1) && (resp.screenEdit == 0)){
                    $('.alloc-drag-drop').attr('data-edit-screen', '0');
                } else if((resp.screenView == 0) && (resp.screenEdit == 1)){
                    $('.alloc-drag-drop').attr('data-edit-screen', '1');
                } else {
                    $('.alloc-drag-drop').attr('data-edit-screen', '0');
                }
            }

            if((resp.screenView == 1) && (resp.screenEdit == 0)){
                $('#editScreen').val(0);
                $('.misc-drag-drop').removeClass('dragAlloc');
                $('.misc-drag-drop').attr('draggable','false');

                $('.unalloc-drag-drop').removeClass('dragAlloc').removeClass('dutydroppable').removeClass('dropeventscall');
                $('.unalloc-drag-drop').attr('draggable','false');

                $('.unallocother-drag-drop').removeClass('dutydroppable').removeClass('dropeventscall');

                $('.alloc-drag-drop').removeClass('dragAlloc').removeClass('dutydroppable').removeClass('dropeventscall');
                $('.alloc-drag-drop').attr('draggable','false');
            } else if((resp.screenView == 0) && (resp.screenEdit == 1)){
                $('#editScreen').val(1);
                $('.misc-drag-drop').addClass('dragAlloc');
                $('.misc-drag-drop').attr('draggable','true');

                $('.unalloc-drag-drop').addClass('dragAlloc').addClass('dutydroppable').addClass('dropeventscall');
                $('.unalloc-drag-drop').attr('draggable','true');

                $('.unallocother-drag-drop').addClass('dutydroppable').addClass('dropeventscall');

                $('.alloc-drag-drop').addClass('dragAlloc').addClass('dutydroppable').addClass('dropeventscall');
                //$('.alloc-drag-drop').attr('draggable','true');
            } else {
                $('#editScreen').val(0);
                $('.misc-drag-drop').removeClass('dragAlloc');
                $('.misc-drag-drop').attr('draggable','false');

                $('.unalloc-drag-drop').removeClass('dragAlloc').removeClass('dutydroppable').removeClass('dropeventscall');
                $('.unalloc-drag-drop').attr('draggable','false');

                $('.unallocother-drag-drop').removeClass('dutydroppable').removeClass('dropeventscall');

                $('.alloc-drag-drop').removeClass('dragAlloc').removeClass('dutydroppable').removeClass('dropeventscall');
                $('.alloc-drag-drop').attr('draggable','false');
            }
        }
    });
}

function schedulersList(dataCellInst){
    let dutyId = dataCellInst.attr('data-id');
    let allocationsSpId = dataCellInst.attr('data-allocations-sp-id');
    $.ajax({
        type: 'POST',
        url: 'page-includes/allocations/weekly/modals/schedulers-list-modal.php',
        data: {
            'dutyId': dutyId,
			'allocationsSpId': allocationsSpId
        },
        success: function (data) {
            $.facebox(data);
        },
        error:function (data) {
            customAlert('some error found in leave approve popup call.');
        }
    });
}

function refreshPersonOT(personId) {
    let totalPlannedDuration = parseFloat($('#markOt_' + personId).attr('data-total-planned-hours'));
    let contractedHoursSeconds = parseFloat($('#hrs_' + personId).attr('data-contracted-hours'));
    let overtimeSeconds = parseFloat($('#markOt_' + personId).attr('data-ot'));
    let actualHours = parseFloat($('#markOt_' + personId).attr('data-actual-seconds'));
    let manualEDP = $('#markOt_' + personId).attr('data-manual-edp');
    let actualHoursMinusRotaSeconds = (actualHours ?? 0) - (totalPlannedDuration ?? 0);
    let otTextColor = 'black';
    let actualHoursMinusRotaHours = parseFloat(actualHoursMinusRotaSeconds / 3600).toFixed(2);
    if(manualEDP == 1) {
        if(actualHoursMinusRotaSeconds != overtimeSeconds)
        {
            otTextColor = 'red';
        }
        if(overtimeSeconds == 0) {
            $('#markOt_' + personId).html('0.00');
        }
        if(overtimeSeconds == 0 && actualHoursMinusRotaSeconds == 0)
        {
            otTextColor = 'black';
            $('#markOt_' + personId).html('');
        }
        if(overtimeSeconds < 0) {
            $('#markOt_' + personId).html('0.00');
        }

        if(overtimeSeconds == 0 && actualHoursMinusRotaSeconds < 0) {
            $('#markOt_' + personId).html('');
            $('#hrs_' + personId).css('color', 'red');
        } else if(actualHours < totalPlannedDuration) {
            $('#hrs_' + personId).css('color', 'red');
        } else {
            $('#hrs_' + personId).css('color', 'black');
        }
        $('#markOt_' + personId).css('color', otTextColor);
     } else {
        if(parseInt(overtimeSeconds) < 0)
        {
            otTextColor = 'red';
        }
        if(overtimeSeconds == 0)
        {
            $('#markOt_' + personId).html('');
        }
        if(actualHours < contractedHoursSeconds) {
            $('#hrs_' + personId).css('color', 'red');
        } else {
            $('#hrs_' + personId).css('color', 'black');
        }
        $('#markOt_' + personId).css('color', otTextColor);
    }
    if(($('#hrs_' + personId).attr('title')) != undefined){
        let hrTitles = $('#hrs_' + personId).attr('title').split(/\r?\n|\r|\n/g);
        hrTitles[2] = 'Actual Hours minus Planned Rota Pattern Hours = ' + actualHoursMinusRotaHours;
        $('#hrs_' + personId).attr('title', hrTitles.join('\r\n'));
	}
}

function initializeNameQuickFilter() {
    resizeQuickFilter();
    var filter = {'teamId' : ''};
    try {
        filter = JSON.parse(localStorage.getItem('EditWeeklyNameFilter'));
    } catch(e) {
        filter = {'teamId' : ''};
    }
    if(filter != null && filter.teamId == $('#adhocFilter').val()) {
        $('#edit-weekly-name-quick-filter').val(filter.filter);
    } else {
        localStorage.removeItem('EditWeeklyNameFilter');
    }
    $('#edit-weekly-name-quick-filter-search').click(function() {
        callNameFilter();
        clearNameQuickFilter();
    });
    $('#edit-weekly-name-quick-filter').keypress(function(e) {
        if (e.which == 13) {
            callNameFilter();
            clearNameQuickFilter();
        }
    });
    $(window).on('resize', function() {
       resizeQuickFilter();
    });
    clearNameQuickFilter();
}
function callNameFilter() {
    if($('#edit-weekly-name-quick-filter').val() != '') {
        applyViewFilter('EditWeeklyNameFilter', '');
    } else {
        clearNameQuickFilter();
        if($('#clearFilterIcon').is(":visible")) {
            $("#applyBtn").click();
        } else {
            applyViewFilter('EditWeeklyNameFilter', '');
        }
    }
}
function clearNameQuickFilter() {
    let $clearIcon = $('<span class="clearFilterIconName" title="Clear Filter" id="clearFilterIconName"><div class="cross-red-icon"></div></span>');
    if($('#edit-weekly-name-quick-filter').val() != '') {
        $('#clearFilterIconName').remove();
        $('#quick-filter-name-container').append($clearIcon);
        $('#clearFilterIconName').click(function() {
            $('#edit-weekly-name-quick-filter').val('');
            callNameFilter();
        });
    } else {
        $('#clearFilterIconName').remove();
        localStorage.removeItem('EditWeeklyNameFilter');
        $('.filter-hide-row').removeClass('filter-hide-row');
        editWeeklyFilter();
    }
}

function resizeQuickFilter() {
    $("#quick-filter-name-container").css("width", $('.block-25').width() - (($(window).width() % 2) == 0 ? 10 : 9));
}

function createDutyFromRota(selectedDayInstance) {
    let allocationId = selectedDayInstance.attr('data-id');
    let allocationsSPID = selectedDayInstance.attr('data-allocations-sp-id');
    let schedulingPersonId = selectedDayInstance.attr('data-scheduling-person');
    let dutyDate = selectedDayInstance.attr('data-date');
	let uId = '';
	if(allocationsSPID != 'null')
	{
		uId = allocationsSPID + '_' + dutyDate;
	}else
	{
		uId = schedulingPersonId + '_' + dutyDate;
	}
     let dataGet = {
        'allocationId': allocationId,
        'schedulingTeamId': selectedDayInstance.attr('data-scheduling-team-id'),
		'allocationsSPID': allocationsSPID,
		'schedulingPersonId': schedulingPersonId,
		'dutyDate': dutyDate
    };
    let scheduledPersonNameP = $('.peronname_' + selectedDayInstance.attr('data-scheduling-person')).attr('data-order');
    let date = formatDateForHeader(selectedDayInstance.attr('data-date'));
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/modals/create-duty-from-rota-modal.php",
        data: dataGet,
        success: function (response) {
            response = JSON.parse(response);
            if(response.SPStatus == "1" && response.SPMessage.trim() == 'Failed - No ROTA available') {
                let $dutyNotCreated = $('<div><table class="tablesmalltidy" width="100%"><tr height="30px"><th><b>Create Duty from Rota Pattern<b></th></tr><tr><td style="">No duty in rota pattern for ' + scheduledPersonNameP + ' on ' + date + '</td></tr></table><div>');
                $.facebox($dutyNotCreated.html());
            } else if(response.SPStatus == "1") {
                let $dutyNotCreated = $('<div><table class="tablesmalltidy" width="100%"><tr height="30px"><th><b>Create Duty from Rota Pattern<b></th></tr><tr><td style="">Unable to Create Duty please contact Service desk.</td></tr></table><div>');
                $.facebox($dutyNotCreated.html());
            } else if(response.SPStatus == "2") {
                let $dutyNotCreated = $('<div><table class="tablesmalltidy" width="100%"><tr height="30px"><th><b>Create Duty from Rota Pattern<b></th></tr><tr><td style="">The duty on this day is a Master Duty. It cannot be brought into Edit weekly again. If it has been deleted, please use Restore Duty.</td></tr></table><div>');
                $.facebox($dutyNotCreated.html());
            } else {
                refreshAllocatedSectionTr($("#colUnqId_"+uId).attr("data-scheduling-person"),$("#colUnqId_"+uId).attr("data-row-id"),$("#colUnqId_"+uId).attr("data-date"),$("#colUnqId_"+uId).attr("data-duty-name"),$("#colUnqId_"+uId).attr("data-row-start"),$("#colUnqId_"+uId).attr('data-row-end'),$("#colUnqId_"+uId).attr('data-unique-id'),'','CONTEXTMENUEDITDUTY',$("#colUnqId_"+uId).attr('data-id'),0,0,'Yes');
            }
        }
    });
}

function highlightRow(schPerson=0){
    if(schPerson > 0){
        let oldHighlightRowPerson = $('#personRowHighlight').html();
        setHighlightRowScheduledPersonCookie(schPerson);
        if(oldHighlightRowPerson != ''){
            $('#personRowHighlight').html(schPerson);
            $('.peronname_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.js_schdsortcode_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.hrs_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.acc_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.markOt_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.fsv_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.edp_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.eft_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.days_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.person_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.peronname_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.js_schdsortcode_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.hrs_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.acc_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.markOt_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.fsv_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.edp_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.eft_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.days_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.person_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
        } else {
            $('#personRowHighlight').html(schPerson);
            $('.peronname_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.js_schdsortcode_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.hrs_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.acc_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.markOt_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.fsv_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.edp_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.eft_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.days_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.person_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
        }
    }
}

function highlightRowScheduledPerson(schPerson=0){
    if(schPerson > 0){
        let oldHighlightRowPerson = $('#personRowHighlight').html();
        if((oldHighlightRowPerson != '') && (oldHighlightRowPerson != schPerson)){
            $('#personRowHighlight').html(schPerson);
            $('.peronname_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.js_schdsortcode_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.hrs_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.acc_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.markOt_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.fsv_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.edp_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.eft_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.days_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.person_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.peronname_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.js_schdsortcode_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.hrs_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.acc_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.markOt_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.fsv_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.edp_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.eft_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.days_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.person_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            setHighlightRowScheduledPersonCookie(schPerson);
        } else if((oldHighlightRowPerson != '') && (oldHighlightRowPerson == schPerson)){
            $('#personRowHighlight').html('');
            $('.peronname_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.js_schdsortcode_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.hrs_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.acc_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.markOt_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.fsv_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.edp_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.eft_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.days_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            $('.person_'+oldHighlightRowPerson).css('border-top','none').css('border-bottom','0.5px solid #ddd');
            removeHighlightRowScheduledPersonCookie();
        } else if((oldHighlightRowPerson == '') && (oldHighlightRowPerson == schPerson)){
            $('#personRowHighlight').html(schPerson);
            $('.peronname_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.js_schdsortcode_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.hrs_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.acc_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.markOt_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.fsv_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.edp_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.eft_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.days_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.person_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            setHighlightRowScheduledPersonCookie(schPerson);
        } else {
            $('#personRowHighlight').html(schPerson);
            $('.peronname_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.js_schdsortcode_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.hrs_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.acc_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.markOt_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.fsv_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.edp_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.eft_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.days_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            $('.person_'+schPerson).css('border-top','1.5px solid #000000').css('border-bottom','1.5px solid #000000');
            setHighlightRowScheduledPersonCookie(schPerson);
        }
    }
}

function setHighlightRowScheduledPersonCookie(schPerson) {
    var teamId = $('#teamId').val();
    if(teamId > 0 ){
        $.cookie('highlightRowScheduledPerson-'+teamId,schPerson);
    }
}

function getHighlightRowScheduledPersonCookie() {
    var teamId = $('#teamId').val();
    if(teamId > 0 ){
       return $.cookie('highlightRowScheduledPerson-'+teamId);
    }
}

function removeHighlightRowScheduledPersonCookie() {
    var teamId = $('#teamId').val();
    if(teamId > 0 ){
        $.removeCookie('highlightRowScheduledPerson-'+teamId);
    }
}

function initializeSchedulingPersonTooltip() {
    $('.person-detail-tooltip').each(function () {
        $(this).qtip({
            content: {
                text: function (event, api) {
                    var reqData = {
                        'scheduledPersonId': api.elements.target.parent().attr('data-id'),
                        'scheduledPersonTitle': api.elements.target.parent().attr('data-title')
                    };
                    $.ajax({
                        type: "post",
                        data: reqData,
                        url: 'page-includes/allocations/allocations-scheduled-person-info.php'
                    })
                        .then(function (content) {
                            // Set the tooltip content upon successful retrieval
                            api.set('content.text', content);
                        },
                            function (xhr, status, error) {
                                // Upon failure... set the tooltip content to error
                                api.set('content.text', status + ': ' + error);
                            });
                    return 'Loading...'; // Set some initial text
                }
            },
            position: {
                viewport: $(window)
            },
            style: 'qtip-rounded qtip-shadow qtip-light'
        });
    });
}

function applyEditWeeklyFilter() {
    let sortOrder = $('#SortOrder').val();
    if(sortOrder) {
        let currentSort = $('#allocSortDate').val();
        let currentSortOrder = $('#allocSortType').val();
        if(sortOrder == 1 && (currentSort != 'NAME' || (currentSort == 'NAME' && currentSortOrder == '1'))) {

            getAllocatedSort('NAME', 1);

        } else if(sortOrder == 2 && (currentSort != 'SORTCODE' || (currentSort == 'SORTCODE' && currentSortOrder == '1'))) {

            getAllocatedSort('SORTCODE', 1);

        } else {
            editWeeklyFilter();
        }
    } else {
        editWeeklyFilter();
    }
}

/*
    Function used for filter data
*/
function editWeeklyFilter() {
    if((localStorage.getItem('editWeeklyFilter') == '') && (localStorage.getItem('editWeeklyFilter') == null)){
        return false;
    }
    $('#loading').show();
    var quickNameFilter = $.trim($('#edit-weekly-name-quick-filter').val());
    var StaffNameFilter = $('#StaffNameFilter').val();
    var StaffName = $('#StaffName').val();
    var SortCodeFilter = $('#SortCodeFilter').val();
    var SortCode = $('#SortCode').val();
    var CostCodeFilter = $('#CostCodeFilter').val();
    var CostCode = $('#CostCode').val();
    var SkillFilter = $('#SkillFilter').val();
    var Skill = $('#Skill').val();
    var DutyFilter = $('#DutyFilter').val();
    var Duty = $('#Duty').val();
    var DutyLabelFilter = $('#DutyLabelFilter').val();
    var DutyLabel = $('#DutyLabel').val();
    var startDate = $('#startDate').val();
    var endDate = $('#endDate').val();
    var teamId = $('#teamId').val();

    var groupCondition = $('#andFilter').prop('checked') ? false : true;

    let filteredData = [];

    //Get Skills to use in filter
    var scheduledPersonAllSkills = [];
    if(Skill) {
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/filters/filters-get-scheduled-person-skills.php",
            data: {
                startDate: startDate,
                endDate: endDate,
                teamId: teamId
            },
            async: false,
            success: function (response) {
                scheduledPersonAllSkills = (response['status'] == true) ? response['data'] : [];
            }
        });
    }

    //Start filtering
    $('.person-data-cell').each(function() {
        let id = $(this).attr('data-id');
        let matchFound = true;
        let groupOr = false;

        // Staff Name Filter
        if (StaffName) {
            let searchData = $(this).attr('data-order');
            let names = StaffName.split(',').map(s => s.trim());
            let containsMatch = names.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = names.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = names.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = names.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            var staffNameLoopMatch = true;
            if (StaffNameFilter === "*" && !containsMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === "!" && !notContainsMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === ";" && !(exactSomeMatch)) {
                staffNameLoopMatch = false;
                matchFound = false;
            } else if (StaffNameFilter === "__AND__" && !exactMatch) {
                staffNameLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && staffNameLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        // Sort Code Filter
        if (SortCode) {
            let searchData = $('.js_schdsortcode_' + id).attr('title') ?? '';
            let codes = SortCode.split(',').map(s => s.trim());
            let containsMatch = codes.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = codes.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = codes.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = codes.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            let sortCodeLoopMatch = true;
            if (SortCodeFilter === "*" && !containsMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === "!" && !notContainsMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === ";" && !(exactSomeMatch)) {
                sortCodeLoopMatch = false;
                matchFound = false;
            } else if (SortCodeFilter === "__AND__" && !exactMatch) {
                sortCodeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && sortCodeLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Cost code
        if (CostCode) {
            let searchData = $(this).attr('data-cost-code') ?? '';
            let codes = CostCode.split(',').map(s => s.trim());
            let containsMatch = codes.some(name => new RegExp(name, "i").test(searchData));
            let notContainsMatch = codes.every(name => !new RegExp(name, "i").test(searchData));
            let exactMatch = codes.every(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );
            let exactSomeMatch = codes.some(name =>
                searchData.toLowerCase() == name.toLowerCase()
            );

            //filter type
            let costCodeLoopMatch = true;
            if (CostCodeFilter === "*" && !containsMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === "!" && !notContainsMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === ";" && !(exactSomeMatch)) {
                costCodeLoopMatch = false;
                matchFound = false;
            } else if (CostCodeFilter === "__AND__" && !exactMatch) {
                costCodeLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && costCodeLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        // Duty name Filter
        if (Duty) {
            let dutyNames = [];
            $('.schRowRight' + id + ' .allocatedDutyCell').each(function () {
                dutyNames.push($(this).attr('data-duty-name'));
            });

            let duties = Duty.split(',').map(s => s.trim());
            let containsMatch = duties.some(name => new RegExp(name, "i").test(dutyNames));
            let notContainsMatch = duties.every(name => !new RegExp(name, "i").test(dutyNames));
            let exactMatch = duties.every(name =>
                dutyNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = duties.some(name =>
                dutyNames.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let startsWithSomeMatch = duties.some(name =>
                 dutyNames.some(dn => dn.startsWith(name))
            );

            //filter type
            let dutyLoopMatch = true;
            if (DutyFilter === "*" && !containsMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === "!" && !notContainsMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === ";" && !(exactSomeMatch)) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if (DutyFilter === "__AND__" && !exactMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            } else if(DutyFilter === "%" && !startsWithSomeMatch) {
                dutyLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && dutyLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Skill filter
        if(Skill) {
            let scheduledPersonSkillsLabels = [];

            let scheduledPersonId = $(this).attr('data-id');
            let filteredSkills = typeof scheduledPersonAllSkills[scheduledPersonId] !== 'undefined' ? scheduledPersonAllSkills[scheduledPersonId] : [];
            filteredSkills.forEach(function(item, index) {
                scheduledPersonSkillsLabels.push(item);
            });

            let skills = Skill;
            let containsMatch = skills.some(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let notContainsMatch = skills.every(name =>
                !scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactMatch = skills.every(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );
            let exactSomeMatch = skills.some(name =>
                scheduledPersonSkillsLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
            );

            //filter type
            let skillLoopMatch = true;
            if (SkillFilter === "*" && !containsMatch) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === "!" && !notContainsMatch) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === ";" && !(exactSomeMatch)) {
                skillLoopMatch = false;
                matchFound = false;
            } else if (SkillFilter === "__AND__" && !exactMatch) {
                skillLoopMatch = false;
                matchFound = false;
            }

            if(groupCondition && skillLoopMatch) { // for or condition
                groupOr = true;
            }
        }

        //Duty Label filter
        if (DutyLabel) {
            var initialStatus = matchFound;
            $('.schRowRight' + id + ' .allocatedDutyCell').each(function () {
                let dutyLabels = [];
                let filteredLabels = $(this).attr('data-duty-labels').split(',').map(s => s.trim());
                filteredLabels.forEach(function(item, index) {
                    dutyLabels.push(item);
                });
                let labels = DutyLabel;
                let containsMatch = labels.some(name => new RegExp(name, "i").test(dutyLabels));
                let notContainsMatch = labels.every(name => !new RegExp(name, "i").test(dutyLabels));
                let exactMatch = labels.every(name =>
                    dutyLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
                );
                let exactSomeMatch = labels.some(name =>
                    dutyLabels.some(dn => dn.toLowerCase() == name.toLowerCase())
                );

                //filter type
                let dutyLabelLoopMatch = true;
                if (DutyLabelFilter === "*" && !containsMatch) {
                    dutyLabelLoopMatch = false;
                    matchFound = false;
                } else if (DutyLabelFilter === "!" && !notContainsMatch) {
                    dutyLabelLoopMatch = false;
                    matchFound = false;
                } else if (DutyLabelFilter === ";" && !(exactSomeMatch)) {
                    dutyLabelLoopMatch = false;
                    matchFound = false;
                } else if (DutyLabelFilter === "__AND__" && !exactMatch) {
                    dutyLabelLoopMatch = false;
                    matchFound = false;
                }

                if((groupCondition && dutyLabelLoopMatch)) { // for or condition
                    groupOr = true;
                }
                if(initialStatus && dutyLabelLoopMatch) {
                    matchFound = true;
                }
                if(dutyLabelLoopMatch) {
                    return false;
                }
            });
        }

        // Quick Filter
        var quickFilterNameLoopMatch = true;
        if (quickNameFilter != '') {
            let searchData = $(this).attr('data-order');
            let names = quickNameFilter.split(',').map(s => s.trim());
            let containsMatch = names.some(name => new RegExp(name, "i").test(searchData));

            //filter type            
            if (!containsMatch) {
                quickFilterNameLoopMatch = false;
                matchFound = false;
                groupOr = false;
            }
        }

        if (matchFound || groupOr) {
            filteredData.push(id);
        }
    });

    //Show only filtered data and hide remaining
    $('.filter-hide-row').removeClass('filter-hide-row');
    $('.person-data-cell').each(function() {
        if(filteredData.includes($(this).attr('data-id')) == false) {
            $(this).parent().addClass('filter-hide-row');
            $('.schRowRight' + $(this).attr('data-id')).addClass('filter-hide-row');
        }
    });



    let fDataForSC = {};
    filteredData.forEach(function(id) {
        let containerClass = 'schRowRight' + id;
        let personClass = 'person_' + id;
        fDataForSC[id] = [];
        $('.' + containerClass + ' > div.' + personClass).each(function() {
            let nextDiv = $(this).children().first();
            if (nextDiv.length) {
                let uniqueId = nextDiv.attr('data-unique-id');
                let dutyDate = '';
                if (uniqueId && uniqueId.includes('_')) {
                    dutyDate = uniqueId.split('_')[1];
                }
                let dutyName = nextDiv.attr('data-duty-name');
                if (dutyName === 'U') {
                    return;
                }
                fDataForSC[id].push({
                    'dutyDate': dutyDate,
                    'dutyName': dutyName
                });
            }
        });
    });

    if ($('#clearFilterIcon').css('display') === 'none') {
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
            data: {
                'action': 'clearApplyfilter'
            },
            success: function (response) {

            }
        });
    } else {
        $.ajax({
            type: "post",
            url: "/page-includes/allocations/weekly/actions/duty-shiftcounting-actions.php",
            data: {
                'action': 'applyfilter',
                'fdata': JSON.stringify(fDataForSC)
            },
            success: function (response) {
				if($('#showHideCount').prop('checked') == true)
				{
					reloadShiftCountingGrid();
				}
            }
        });
    }


    $('#loading').hide();
}

function getSortCodes(groupData) {
    let currentSortCode = '';
    let currentSortCode1 = '';
    let sortCodes = [];

    $.each(groupData, function(date, data) {
		data.SortCode = (data.SortCode == null) ? '' : data.SortCode;
		if(data.SortCode.trim() !== '')
		{
			currentSortCode = data.SortCode.trim();
			if(data.DutyName != 'U')
			{
				currentSortCode1 = data.SortCode.trim();
			}
		}
    });
	currentSortCode = currentSortCode1 ? currentSortCode1 : currentSortCode;
	sortCodes.push(currentSortCode);
    return [...new Set(sortCodes)];
}
