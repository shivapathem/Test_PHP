function editWeeklyPageLoad(chooseWeekFormData='Yes', formData={}, pageLoadCall = ''){
    if($.cookie('unallocgridheight') != undefined){
        $.removeCookie('unallocgridheight');
    }
    if($.cookie('allocgridheight') != undefined){
        $.removeCookie('allocgridheight');
    }
    if($.cookie('shiftgridheight') != undefined){
        $.removeCookie('shiftgridheight');
    }
    if((pageLoadCall != 'rotaInEditWeekly') && (localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null)){
        localStorage.removeItem('editWeeklyFilter');
    }
    if((pageLoadCall != 'rotaInEditWeekly') && (localStorage.getItem('EditWeeklyNameFilter') != '') && (localStorage.getItem('EditWeeklyNameFilter') != null)){
       localStorage.removeItem('EditWeeklyNameFilter');
    }

    var form_array = {};
    var setWeekNum = '';
    if(chooseWeekFormData == 'Yes'){
        var unindexed_array = $('#chooseWeekForm').serializeArray();
        $.map(unindexed_array, function(n, i){
            form_array[n['name']] = n['value'];
        });
        form_array.teamId = form_array.schedulingTeamId;
        setWeekNum = $('#setWeekNumber').val();
    } else {
        form_array.schedulingTeamId = formData.schedulingTeamId;
        form_array.userId = formData.userId;
        form_array.weekNumber = formData.weekNumber;
        form_array.teamId = formData.teamId;
        form_array.selAutoPageFilterId = formData.selAutoPageFilterId;
        form_array.shiftCountingFilterId = formData.shiftCountingFilterId;
        setWeekNum = formData.weekNumber;
    }

    let reqWeekNum = '';
    let reqTeamId =  form_array.teamId;
    if(setWeekNum.length == 1){
        reqWeekNum = '0'+form_array.weekNumber;
    } else {
        reqWeekNum = form_array.weekNumber;
    }

    var reqcheckweek = {};
    reqcheckweek.actionname = 'checkweekexists';
    reqcheckweek.weeknumber = reqWeekNum;
    reqcheckweek.schedulingTeamId = reqTeamId;
    $.ajax({
        type: "post",
        url: "/page-includes/allocations/weekly/actions/check-week.php",
        data: reqcheckweek,
        success: function (data) {
            var respData = $.parseJSON(data);
            if (respData.success) {
                $('#errorDivWeek').html('');
                $('#errorDivWeek').hide();
                if(respData.errormessage != undefined){
                    if(respData.openWeek == 'Yes'){
                        customConfirm(respData.errormessage,function(){
                                form_array.isMenuCall = 'Yes';
                                $('#errormessage').val('');
                                $('#allocationWeekExists').val('Yes');
                                if (form_array.allocationWeekExists == 'Yes') {
                                    $.cookie('cookieWeekNumber', form_array.weekNumber);
                                    if(parseInt(form_array.selAutoPageFilterId) > 0){
                                        form_array.noSpCall = 'Yes';
                                        $.ajax({
                                            type: "post",
                                            url: "/page-includes/allocations/weekly/period-view.php",
                                            data: form_array,
                                            beforeSend: function (jqXHR, settings) {
                                                if(chooseWeekFormData == 'No'){
                                                    $('#loading').hide();
                                                    $('#loadingWeekly').show();
                                                } else {
                                                    $('#loadingWeekly').hide();
                                                    $('#loading').show();
                                                }
                                            },
                                            success: function (data) { 
                                                $('#content').html(data);
                                                $.facebox.close();
                                                applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                                if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                }
                                            }
                                        });
                                    } else {
                                        if(parseInt(form_array.selAutoPageFilterId) > 0){
                                            form_array.noSpCall = 'Yes';
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/weekly/period-view.php",
                                                data: form_array,
                                                beforeSend: function (jqXHR, settings) {
                                                    if(chooseWeekFormData == 'No'){
                                                        $('#loading').hide();
                                                        $('#loadingWeekly').show();
                                                    } else {
                                                        $('#loadingWeekly').hide();
                                                        $('#loading').show();
                                                    }
                                                },
                                                success: function (data) { 
                                                    $('#content').html(data);
                                                    applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                                    if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                        applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                    }
                                                }
                                            });
                                        } else {
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/weekly/period-view.php",
                                                data: form_array,
                                                beforeSend: function (jqXHR, settings) {
                                                    if(chooseWeekFormData == 'No'){
                                                        $('#loading').hide();
                                                        $('#loadingWeekly').show();
                                                    } else {
                                                        $('#loadingWeekly').hide();
                                                        $('#loading').show();
                                                    }
                                                },
                                                success: function (data) { 
                                                    $('#content').html(data);
                                                    if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                        applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                    }
                                                }
                                            });
                                        }
                                    }
                                } else {
                                    if(parseInt(form_array.selAutoPageFilterId) > 0){
                                        form_array.noSpCall = 'Yes';
                                        $.ajax({
                                            type: "post",
                                            url: "/page-includes/allocations/weekly/period-view.php",
                                            data: form_array,
                                            beforeSend: function (jqXHR, settings) {
                                                if(chooseWeekFormData == 'No'){
                                                    $('#loading').hide();
                                                    $('#loadingWeekly').show();
                                                } else {
                                                    $('#loadingWeekly').hide();
                                                    $('#loading').show();
                                                }
                                            },
                                            success: function (data) { 
                                                $('#content').html(data);
                                                applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                                if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                }
                                            }
                                        });
                                    } else {
                                        $.ajax({
                                            type: "post",
                                            url: "/page-includes/allocations/weekly/period-view.php",
                                            data: form_array,
                                            beforeSend: function (jqXHR, settings) {
                                                if(chooseWeekFormData == 'No'){
                                                    $('#loading').hide();
                                                    $('#loadingWeekly').show();
                                                } else {
                                                    $('#loadingWeekly').hide();
                                                    $('#loading').show();
                                                }
                                            },
                                            success: function (data) { 
                                                $('#content').html(data);
                                                if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                }
                                                if(chooseWeekFormData == 'No'){
                                                    $('#loading').hide();
                                                    $('#loadingWeekly').show();
                                                }
                                            }
                                        });
                                    }
                                }
                            },
                            function() {
                            }
                        );
                        $('#yes').val('OK');
                        $('#no').hide();
                    } else {
                        $('#allocationWeekExists').val('No');
                        customAlert(respData.errormessage);
                    }
                    $('#loading').hide();
                } else {
                    $('#errormessage').val('');
                    $('#allocationWeekExists').val('Yes');
                    if (form_array.allocationWeekExists == 'Yes') {
                        $.cookie('cookieWeekNumber', form_array.weekNumber);
                        if(parseInt(form_array.selAutoPageFilterId) > 0){
                            form_array.noSpCall = 'Yes';
                            $.ajax({
                                type: "post",
                                url: "/page-includes/allocations/weekly/period-view.php",
                                data: form_array,
                                beforeSend: function (jqXHR, settings) {
                                    if(chooseWeekFormData == 'No'){
                                        $('#loading').hide();
                                        $('#loadingWeekly').show();
                                    } else {
                                        $('#loadingWeekly').hide();
                                        $('#loading').show();
                                    }
                                },
                                success: function (data) { 
                                    $('#content').html(data);
                                    $.facebox.close();
                                    applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                    if(parseInt(form_array.shiftCountingFilterId) > 0){
                                        applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                    }
                                }
                            });
                        } else {
                            if(parseInt(form_array.selAutoPageFilterId) > 0){
                                form_array.noSpCall = 'Yes';
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/period-view.php",
                                    data: form_array,
                                    beforeSend: function (jqXHR, settings) {
                                        if(chooseWeekFormData == 'No'){
                                            $('#loading').hide();
                                            $('#loadingWeekly').show();
                                        } else {
                                            $('#loadingWeekly').hide();
                                            $('#loading').show();
                                        }
                                    },
                                    success: function (data) { 
                                        $('#content').html(data);
                                        applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                        if(parseInt(form_array.shiftCountingFilterId) > 0){
                                            applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                        }
                                    }
                                });
                            } else {
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/period-view.php",
                                    data: form_array,
                                    beforeSend: function (jqXHR, settings) {
                                        if(chooseWeekFormData == 'No'){
                                            $('#loading').hide();
                                            $('#loadingWeekly').show();
                                        } else {
                                            $('#loadingWeekly').hide();
                                            $('#loading').show();
                                        }
                                    },
                                    success: function (data) { 
                                        $('#content').html(data);
                                        if(parseInt(form_array.shiftCountingFilterId) > 0){
                                            applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                        }
                                    }
                                });
                            }
                        }
                    } else {
                        if(parseInt(form_array.selAutoPageFilterId) > 0){
                            form_array.noSpCall = 'Yes';
                            $.ajax({
                                type: "post",
                                url: "/page-includes/allocations/weekly/period-view.php",
                                data: form_array,
                                beforeSend: function (jqXHR, settings) {
                                    if(chooseWeekFormData == 'No'){
                                        $('#loading').hide();
                                        $('#loadingWeekly').show();
                                    } else {
                                        $('#loadingWeekly').hide();
                                        $('#loading').show();
                                    }
                                },
                                success: function (data) { 
                                    $('#content').html(data);
                                    applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                    if(parseInt(form_array.shiftCountingFilterId) > 0){
                                        applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                    }
                                }
                            });
                        } else {
                            $.ajax({
                                type: "post",
                                url: "/page-includes/allocations/weekly/period-view.php",
                                data: form_array,
                                beforeSend: function (jqXHR, settings) {
                                    if(chooseWeekFormData == 'No'){
                                        $('#loading').hide();
                                        $('#loadingWeekly').show();
                                    } else {
                                        $('#loadingWeekly').hide();
                                        $('#loading').show();
                                    }
                                },
                                success: function (data) { 
                                    $('#content').html(data);                                    
                                    if(localStorage.getItem('EditWeeklyNameFilter') != '' && localStorage.getItem('EditWeeklyNameFilter') != null){
                                        applyViewFilter('EditWeeklyNameFilter',selFilterId,'Yes');
                                    }
                                    if(((localStorage.getItem('editWeeklyFilter') != '') && (localStorage.getItem('editWeeklyFilter') != null))){  
                                        applyViewFilter('EditWeekly','','Yes');
                                    } 
                                    if(parseInt(form_array.shiftCountingFilterId) > 0){
                                        applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                    }
                                    if(chooseWeekFormData == 'No'){
                                        $('#loading').hide();
                                        $('#loadingWeekly').show();
                                    }
                                }
                            });
                        }
                    }
                }
            } else {
                if(respData.errormessage != undefined){
                    if(respData.openWeek == 'Yes'){
                        customConfirm(respData.errormessage,function(){
                                form_array.isMenuCall = 'Yes';
                                if(respData.validWeek == null){
                                    let splitEntWeekNum = form_array.weekNumber.split('/');
                                    if(splitEntWeekNum[1] != undefined){
                                        if((splitEntWeekNum[1].length == 1) || (splitEntWeekNum[1].length == 3) || (splitEntWeekNum[1].length > 4)){
                                            $('#errorDivWeek').html('Please enter valid week number');
                                            $('#errorDivWeek').show();
                                            $('#loadingWeekly').hide();
                                        } else {
                                            let inpYear = parseInt(splitEntWeekNum[1]);
                                            let formTransitionyear = parseInt(form_array.transitionDateYear);
                                            if(splitEntWeekNum[1].length == 2){
                                                inpYear = '20'+inpYear;
                                            }
                                            if(inpYear < formTransitionyear){
                                                customAlert('Allocate cannot open dates before '+form_array.errTransitionDate+' in the Edit Weekly Allocations view.');
                                            } else {
                                                if(parseInt(form_array.selAutoPageFilterId) > 0){
                                                    form_array.noSpCall = 'Yes';
                                                    $.ajax({
                                                        type: "post",
                                                        url: "/page-includes/allocations/weekly/period-view.php",
                                                        data: form_array,
                                                        beforeSend: function (jqXHR, settings) {
                                                            if(chooseWeekFormData == 'No'){
                                                                $('#loading').hide();
                                                                $('#loadingWeekly').show();
                                                            } else {
                                                                $('#loadingWeekly').hide();
                                                                $('#loading').show();
                                                            }
                                                        },
                                                        success: function (data) { 
                                                            $('#content').html(data);
                                                            applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                                            if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                                applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                            }
                                                        }
                                                    });
                                                } else {
                                                    $.ajax({
                                                        type: "post",
                                                        url: "/page-includes/allocations/weekly/period-view.php",
                                                        data: form_array,
                                                        beforeSend: function (jqXHR, settings) {
                                                            if(chooseWeekFormData == 'No'){
                                                                $('#loading').hide();
                                                                $('#loadingWeekly').show();
                                                            } else {
                                                                $('#loadingWeekly').hide();
                                                                $('#loading').show();
                                                            }
                                                        },
                                                        success: function (data) { 
                                                            $('#content').html(data);
                                                            if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                                applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        }
                                    } else {
                                        $('#errorDivWeek').html('Please enter valid week number');
                                        $('#errorDivWeek').show();
                                        $('#loadingWeekly').hide();
                                    }
                                } else {
                                    if (form_array.allocationWeekExists == 'Yes') {
                                        $.cookie('cookieWeekNumber', form_array.weekNumber);
                                        if(parseInt(form_array.selAutoPageFilterId) > 0){
                                            form_array.noSpCall = 'Yes';
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/weekly/period-view.php",
                                                data: form_array,
                                                beforeSend: function (jqXHR, settings) {
                                                    if(chooseWeekFormData == 'No'){
                                                        $('#loading').hide();
                                                        $('#loadingWeekly').show();
                                                    } else {
                                                        $('#loadingWeekly').hide();
                                                        $('#loading').show();
                                                    }
                                                },
                                                success: function (data) { 
                                                    $('#content').html(data);
                                                    $.facebox.close();
                                                    $('#loadingWeekly').show();
                                                    applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                                    if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                        applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                    }
                                                }
                                            });
                                        } else {
                                            if(parseInt(form_array.selAutoPageFilterId) > 0){
                                                form_array.noSpCall = 'Yes';
                                                $.ajax({
                                                    type: "post",
                                                    url: "/page-includes/allocations/weekly/period-view.php",
                                                    data: form_array,
                                                    beforeSend: function (jqXHR, settings) {
                                                        if(chooseWeekFormData == 'No'){
                                                            $('#loading').hide();
                                                            $('#loadingWeekly').show();
                                                        } else {
                                                            $('#loadingWeekly').hide();
                                                            $('#loading').show();
                                                        }
                                                    },
                                                    success: function (data) { 
                                                        $('#content').html(data);
                                                        applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                                        if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                            applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                        }
                                                    }
                                                });
                                            } else {
                                                $.ajax({
                                                    type: "post",
                                                    url: "/page-includes/allocations/weekly/period-view.php",
                                                    data: form_array,
                                                    beforeSend: function (jqXHR, settings) {
                                                        if(chooseWeekFormData == 'No'){
                                                            $('#loading').hide();
                                                            $('#loadingWeekly').show();
                                                        } else {
                                                            $('#loadingWeekly').hide();
                                                            $('#loading').show();
                                                        }
                                                    },
                                                    success: function (data) { 
                                                        $('#content').html(data);
                                                        if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                            applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    } else {
                                        if(parseInt(form_array.selAutoPageFilterId) > 0){
                                            form_array.noSpCall = 'Yes';
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/weekly/period-view.php",
                                                data: form_array,
                                                beforeSend: function (jqXHR, settings) {
                                                    if(chooseWeekFormData == 'No'){
                                                        $('#loading').hide();
                                                        $('#loadingWeekly').show();
                                                    } else {
                                                        $('#loadingWeekly').hide();
                                                        $('#loading').show();
                                                    }
                                                },
                                                success: function (data) { 
                                                    $('#content').html(data);
                                                    applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                                    if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                        applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                    }
                                                }
                                            });
                                        } else {
                                            $.ajax({
                                                type: "post",
                                                url: "/page-includes/allocations/weekly/period-view.php",
                                                data: form_array,
                                                beforeSend: function (jqXHR, settings) {
                                                    if(chooseWeekFormData == 'No'){
                                                        $('#loading').hide();
                                                        $('#loadingWeekly').show();
                                                    } else {
                                                        $('#loadingWeekly').hide();
                                                        $('#loading').show();
                                                    }
                                                },
                                                success: function (data) { 
                                                    $('#content').html(data);
                                                    if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                        applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                    }
                                                }
                                            });
                                        }
                                    }
                                }
                            },
                            function() {
                            }
                        );
                        $('#yes').val('OK');
                        $('#no').hide();
                    } else {
                        $('#allocationWeekExists').val('No');
                        customAlert(respData.errormessage);
                    }
                    $('#loading').hide();
                } else {
                    if(respData.validWeek == null){
                        let splitEntWeekNum = form_array.weekNumber.split('/');
                        if(splitEntWeekNum[1] != undefined){
                            if((splitEntWeekNum[1].length == 1) || (splitEntWeekNum[1].length == 3) || (splitEntWeekNum[1].length > 4)){
                                $('#errorDivWeek').html('Please enter valid week number');
                                $('#errorDivWeek').show();
                                $('#loadingWeekly').hide();
                            } else {
                                let inpYear = parseInt(splitEntWeekNum[1]);
                                let formTransitionyear = parseInt(form_array.transitionDateYear);
                                if(splitEntWeekNum[1].length == 2){
                                    inpYear = '20'+inpYear;
                                }
                                if(inpYear < formTransitionyear){
                                    customAlert('Allocate cannot open dates before '+form_array.errTransitionDate+' in the Edit Weekly Allocations view.');
                                } else {
                                    if(parseInt(form_array.selAutoPageFilterId) > 0){
                                        form_array.noSpCall = 'Yes';
                                        $.ajax({
                                            type: "post",
                                            url: "/page-includes/allocations/weekly/period-view.php",
                                            data: form_array,
                                            beforeSend: function (jqXHR, settings) {
                                                if(chooseWeekFormData == 'No'){
                                                    $('#loading').hide();
                                                    $('#loadingWeekly').show();
                                                } else {
                                                    $('#loadingWeekly').hide();
                                                    $('#loading').show();
                                                }
                                            },
                                            success: function (data) { 
                                                $('#content').html(data);
                                                applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                                if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                }
                                            }
                                        });
                                    } else {
                                        $.ajax({
                                            type: "post",
                                            url: "/page-includes/allocations/weekly/period-view.php",
                                            data: form_array,
                                            beforeSend: function (jqXHR, settings) {
                                                if(chooseWeekFormData == 'No'){
                                                    $('#loading').hide();
                                                    $('#loadingWeekly').show();
                                                } else {
                                                    $('#loadingWeekly').hide();
                                                    $('#loading').show();
                                                }
                                            },
                                            success: function (data) { 
                                                $('#content').html(data);
                                                if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                    applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        } else {
                            $('#errorDivWeek').html('Please enter valid week number');
                            $('#errorDivWeek').show();
                            $('#loadingWeekly').hide();
                        }
                    } else {
                        if (form_array.allocationWeekExists == 'Yes') {
                            $.cookie('cookieWeekNumber', form_array.weekNumber);
                            if(parseInt(form_array.selAutoPageFilterId) > 0){
                                form_array.noSpCall = 'Yes';
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/period-view.php",
                                    data: form_array,
                                    beforeSend: function (jqXHR, settings) {
                                        if(chooseWeekFormData == 'No'){
                                            $('#loading').hide();
                                            $('#loadingWeekly').show();
                                        } else {
                                            $('#loadingWeekly').hide();
                                            $('#loading').show();
                                        }
                                    },
                                    success: function (data) { 
                                        $('#content').html(data);
                                        $.facebox.close();
                                        $('#loadingWeekly').show();
                                        applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                        if(parseInt(form_array.shiftCountingFilterId) > 0){
                                            applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                        }
                                    }
                                });
                            } else {
                                if(parseInt(form_array.selAutoPageFilterId) > 0){
                                    form_array.noSpCall = 'Yes';
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/weekly/period-view.php",
                                        data: form_array,
                                        beforeSend: function (jqXHR, settings) {
                                            if(chooseWeekFormData == 'No'){
                                                $('#loading').hide();
                                                $('#loadingWeekly').show();
                                            } else {
                                                $('#loadingWeekly').hide();
                                                $('#loading').show();
                                            }
                                        },
                                        success: function (data) { 
                                            $('#content').html(data);
                                            applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                            if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                            }
                                        }
                                    });
                                } else {
                                    $.ajax({
                                        type: "post",
                                        url: "/page-includes/allocations/weekly/period-view.php",
                                        data: form_array,
                                        beforeSend: function (jqXHR, settings) {
                                            if(chooseWeekFormData == 'No'){
                                                $('#loading').hide();
                                                $('#loadingWeekly').show();
                                            } else {
                                                $('#loadingWeekly').hide();
                                                $('#loading').show();
                                            }
                                        },
                                        success: function (data) { 
                                            $('#content').html(data);
                                            if(parseInt(form_array.shiftCountingFilterId) > 0){
                                                applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                            }
                                        }
                                    });
                                }
                            }
                        } else {
                            if(parseInt(form_array.selAutoPageFilterId) > 0){
                                form_array.noSpCall = 'Yes';
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/period-view.php",
                                    data: form_array,
                                    beforeSend: function (jqXHR, settings) {
                                        if(chooseWeekFormData == 'No'){
                                            $('#loading').hide();
                                            $('#loadingWeekly').show();
                                        } else {
                                            $('#loadingWeekly').hide();
                                            $('#loading').show();
                                        }
                                    },
                                    success: function (data) { 
                                        $('#content').html(data);
                                        applyViewFilter('EditWeekly',form_array.selAutoPageFilterId,'Yes');
                                        if(parseInt(form_array.shiftCountingFilterId) > 0){
                                            applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                        }
                                    }
                                });
                            } else {
                                $.ajax({
                                    type: "post",
                                    url: "/page-includes/allocations/weekly/period-view.php",
                                    data: form_array,
                                    beforeSend: function (jqXHR, settings) {
                                        if(chooseWeekFormData == 'No'){
                                            $('#loading').hide();
                                            $('#loadingWeekly').show();
                                        } else {
                                            $('#loadingWeekly').hide();
                                            $('#loading').show();
                                        }
                                    },
                                    success: function (data) { 
                                        $('#content').html(data);
                                        if(parseInt(form_array.shiftCountingFilterId) > 0){
                                            applyDutyShiftCountingFilter(form_array.userId, form_array.schedulingTeamId, form_array.shiftCountingFilterId);
                                        }
                                    }
                                });
                            }
                        }
                    }
                }
            }
        }
    });
}