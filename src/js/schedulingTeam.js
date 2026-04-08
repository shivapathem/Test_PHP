$(document).ready(function () {
    // Calling of function for scheduling team data.
    getSchedulingTeamDetails();
    $('.schedulepersontab').on('click', function (e) {
        activateTab(e);
    });


    $(document).on("change", "#divisionId", function () {
        $("#schedulingGroups").prop("disabled", true).empty().trigger('chosen:updated');

        const areaId = $(this).val();
        if (areaId && areaId !== "-1") {
            loadSchedulingGroups(areaId).then(effectiveIds => {
                initOrUpdateSchedulingGroupsChosen();
            });
        } else {
            $("#schedulingGroups").prop("disabled", true).trigger('chosen:updated');
        }
    });

    $('.teamdescription').qtip({
        style: { classes: 'qtip-rounded qtip-shadow qtip-light' }
    });

    resetviewhistoryeditbutton();

    let tabname = '';
    let taskcreateedit = '';
    let editButton = 1;

    $('#identitysubmit').on('click', function () {
        if ($('#headerschteam').html() == 'Edit Scheduling Team') {
            taskcreateedit = 'edit';
        } else {
            taskcreateedit = 'create';
        }
        tabname = 'identity';
        saveUpdateSchedulingTeamDetails(tabname, taskcreateedit);
    });

    $('#AllocationsSubmit').on('click', function () {
        if ($('#headerschteam').html() == 'Edit Scheduling Team') {
            taskcreateedit = 'edit';
        } else {
            taskcreateedit = 'create';
        }
        tabname = 'allocations';
        saveUpdateSchedulingTeamDetails(tabname, taskcreateedit);
    });

    $('#miscellaneoussubmit').on('click', function () {
        if ($('#headerschteam').html() == 'Edit Scheduling Team') {
            taskcreateedit = 'edit';
        } else {
            taskcreateedit = 'create';
        }
        tabname = 'miscellaneous';
        saveUpdateSchedulingTeamDetails(tabname, taskcreateedit);
    });

    $('.cancelschteampopup').on('click', function (event) {
        event.preventDefault();
        var r = closeFunction();
    });

    $('#createschteambtn').on('click', function () {
        $('#headerschteam').html('Create Scheduling Team');
        $("#intnewschteamid").val('');
        document.getElementById('identitytab').style.display = "block";
        document.getElementById('allocationtab').style.display = "none";
        document.getElementById('miscellaneoustab').style.display = "none";
        $(".schedulepersontab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#identityTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
        resetIdentityTab(1);
        resetAllocationsTab(1);
        resetMiscellaneousTab(1);
        let modalcancelclose = document.getElementById("myModalSchedTeam");
        modalcancelclose.style.display = "none";
        resetIdentityTabFields();
        openModalPopup();
        $("#loading").hide();
    });
});

function closeFunction() {
    customConfirm('This will discard all your changes. Do you want to continue to close the window?', function () {
        document.getElementById('identitytab').style.display = "block";
        document.getElementById('allocationtab').style.display = "none";
        document.getElementById('miscellaneoustab').style.display = "none";
        $(".schedulepersontab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#identityTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
        resetIdentityTab(1);
        resetAllocationsTab(1);
        resetMiscellaneousTab(1);
        let modalcancelclose = document.getElementById("myModalSchedTeam");
        modalcancelclose.style.display = "none";
        $("#loading").hide();
    },
        function () {
            $("#loading").hide();
            rturnValue = false;
        }
    );
}

function removeSchedulingTeam(team_id) {
    $.ajax({
        type: 'POST',
        url: '../page-includes/admin/process/schedulingTeam.php',
        dataType: "json",
        data: {
            task: 'deleteTeam',
            team_id: team_id
        },
        success: function (Result) {
            $("#loading").hide();
            return true;
        }
    });
}

function resetIdentityTabFields() {
    $('#schedulingTeamName').attr("disabled", false);
    $('#schedulingTeamDescription').attr("disabled", false);
    $('#divisionId').attr("disabled", false);
    $('#schedulingGroups').attr("disabled", false);
    $('#defaultChargeCode').attr("disabled", false);
    $('#isActive').attr("disabled", false);
    $('#divisionId').removeClass('activeclass');
    $('#divisionName').val('');
    $('#divisionName').addClass('activeclass');
    $('#identitysubmit').removeClass('activeclass');
    $("#identityTabDone").val('');
    $("#allocationTabDone").val('');
    $("#miscelTabDone").val('');
}

/*
* @Description : Function to open popup create scheduling team.
* @access : Public
* @global : Not Applicable
* @param  : N/A
* @return : N/A
*/
function openModalPopup() {
    // Modal loading logic start
    // Get the modal
    let modal = document.getElementById("myModalSchedTeam");
    // Get the <span> element that closes the modal
    let span = document.getElementsByClassName("closebox")[0];
    // Function to open modal
    // When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        var r = closeFunction();
    }
    // Open popup
    modal.style.display = "block";

    // Ensure chosen width is computed after display
    setTimeout(() => {
        initOrUpdateSchedulingGroupsChosen();
    }, 0);

    $('#addteamcancel').on('click', function () {
        modal.style.display = "none";
        $("#loading").hide();
    });
}

/*
* @Description : Event listener to reinitialize or update scheduling groups when the browser window is resized.
* @access      : Public
* @global      : Not Applicable
* @param       : N/A
* @return      : N/A
*/
$(window).on('resize', function () {
    initOrUpdateSchedulingGroupsChosen();
});

/*
* @Description : Function to switch various tabs in create scheduling team.
* @access : Public
* @global : Not Applicable
* @param  : N/A
* @return : N/A
*/
function openTab(tabName) {
    if (tabName == 'identitytab') {
        document.getElementById('identitytab').style.display = "block";
        document.getElementById('allocationtab').style.display = "none";
        document.getElementById('miscellaneoustab').style.display = "none";
    } else if (tabName == 'allocationtab') {
        document.getElementById('identitytab').style.display = "none";
        document.getElementById('allocationtab').style.display = "block";
        document.getElementById('miscellaneoustab').style.display = "none";
    } else {
        document.getElementById('identitytab').style.display = "none";
        document.getElementById('allocationtab').style.display = "none";
        document.getElementById('miscellaneoustab').style.display = "block";
    }
}

/*
* @Description : Function to activate a tab in create scheduling team.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function activateTab(evt) {
    $(".schedulepersontab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
    evt.currentTarget.className += " ui-tabs-active ui-state-active ui-state-focus";
}

function cleanupSchedulingTeamFixedHeaderFilters() {
    // FixedColumns clones header cells; remove cloned yadcf widgets to avoid duplicate filters on scroll.
    $('.DTFC_LeftWrapper .yadcf-filter-wrapper, .DTFC_RightWrapper .yadcf-filter-wrapper').remove();
}

function cleanupSchedulingTeamFixedColumnsArtifacts() {
    // Remove stale FixedColumns wrappers before rebuilding the table.
    $('.DTFC_LeftWrapper, .DTFC_RightWrapper').remove();
}

window.onload = function () {
    changePage(1);
};
$(".schedulingTeamContainer").height($(window).height() - 245);
/*
* @Description : Get details of schedulling team according to user permission. This function append data into Datatable.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getSchedulingTeamDetails() {
    cleanupSchedulingTeamFixedColumnsArtifacts();

    let table = $('#schedulingTeamDetails').DataTable({
        lengthChange: false,
        paging: true,
        "pageLength": 50,
        info: false,
        stateSave: true,
        deferRender: true,
        scrollY: parseInt($(".schedulingTeamContainer").height() - 88),
        scrollX: true,
        "fnDrawCallback": function (oSettings) {
            if ($('#schedulingTeamDetails tr').length > 50) {
                $(".dataTables_paginate").css('visibility', 'visible');
                $(".dataTables_length").css("display", "none");
                $("#dataTables_paginate ").css('font-size', '8px');
                $(".dataTables_info").css('display', 'block');
            } else {
                $(".dataTables_info").css('display', 'none');
            }
            cleanupSchedulingTeamFixedHeaderFilters();
        },
        "columnDefs": [
            { "orderable": true, "targets": 6 },
            { "orderable": true, "targets": 7 },
            { "orderable": true, "targets": 8 },
            { "orderable": true, "targets": 9 },
            { "orderable": true, "targets": 10 },
            { "orderable": true, "targets": 11 },
            {
                "orderable": true, "targets": 12,
                render: function (data, type, row) {
                    if (type === 'type' || type === 'sort') {
                        if (data === 'N/A') {
                            return 99999;  // Some arbitrary high number
                        }
                        return data;  // Numeric value
                    }
                    return data;  // return the data for the other orthognal types
                }
            },
            { "orderable": true, "targets": 13 },
            { "orderable": true, "targets": 14 },
            { "orderable": true, "targets": 15 },
            { "orderable": true, "targets": 16 },
            { "orderable": true, "targets": 17 },
            { "orderable": true, "targets": 18 },
            { "orderable": true, "targets": 19 },
            { "orderable": true, "targets": 20 },
            { "orderable": true, "targets": 21 },
            { "orderable": true, "targets": 22 },
            { "orderable": true, "targets": 23 },
            { "orderable": true, "targets": 24 },
            { "orderable": true, "targets": 25 },
            { "orderable": true, "targets": 26 },
            { "orderable": true, "targets": 27 },
            { "orderable": true, "targets": 28 },
            { "orderable": true, "targets": 29 },
            { "orderable": true, "targets": 30 },
            { "orderable": true, "targets": 31 },
            { "orderable": true, "targets": 32 },
            { "orderable": true, "targets": 33 },
            { "orderable": true, "targets": 34 },
            { "orderable": true, "targets": 35 },
            { "orderable": true, "targets": 36 },
            { "orderable": true, "targets": 37 },
            { "orderable": true, "targets": 38 },
            { "orderable": true, "targets": 39 },
            { "orderable": true, "targets": 40 },
            { "orderable": true, "targets": 41 },
            { "orderable": true, "targets": 42 },
            { "orderable": true, "targets": 43 }
        ],
        "ajax": {
            type: 'POST',
            url: '../page-includes/admin/process/schedulingTeam.php',
            "data": {
                task: 'getSchedulingTeamDetails'
            }
        }
    });
    new $.fn.dataTable.FixedColumns(table, {
        leftColumns: 1,
        rightColumns: 1
    });
    yadcf.init(table, [
        {
            column_number: 0,
            filter_type: 'text'
        },
        {
            column_number: 1,
            filter_type: 'text'
        },
        {
            column_number: 2,
            filter_type: 'text'
        },
        {
            column_number: 3,
            filter_type: 'text'
        },
        {
            column_number: 4,
            filter_type: 'text'
        },
        {
            column_number: 5,
            filter_type: 'select'
        },

    ]);
    cleanupSchedulingTeamFixedHeaderFilters();
}

//Set table scrollBody height dynamically 
$('.schedulingTeamContainer .dataTables_scrollBody').css('height', parseInt($(".schedulingTeamContainer").height() - 88));
$(window).resize(function () {
    $('.schedulingTeamContainer .dataTables_scrollBody').css('height', parseInt($(".schedulingTeamContainer").height() - 88));
});

/*
* @Description : Create or Update schedulling team details by authorized users.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : Popup message of success and failure
*/
function saveUpdateSchedulingTeamDetails(tabname, taskcreateedit) {
    let schedulingTeamName = $('#schedulingTeamName').val();
    let divisionId = $('#divisionId').val();
    let defaultChargeCode = $('#defaultChargeCode').val();
    let defaultChargeCodeDesc = $('#defaultChargeCodeDesc').val();
    let defaultActiveCode = $('#defaultActiveCode').val();
    let defaultSicknessHoursAllocation = $('#defaultSicknessHoursAllocation').val();
    let defaultDutyDurationAll = $('#defaultDutyDuration').val().toString().split(/[.:]+/);
    let defaultDutyDurationHour = defaultDutyDurationAll.length > 0 ? defaultDutyDurationAll[0] : 0;
    let defaultDutyDurationMinute = defaultDutyDurationAll.length > 1 ? defaultDutyDurationAll[1] : 0;
    if (defaultDutyDurationMinute == "0" || defaultDutyDurationMinute == "00") {
        defaultDutyDurationMinute = "0";
    } else if (defaultDutyDurationMinute == "25") {
        defaultDutyDurationMinute = "15";
    } else if (defaultDutyDurationMinute == "50" || defaultDutyDurationMinute == "5") {
        defaultDutyDurationMinute = "30";
    } else if (defaultDutyDurationMinute == "75") {
        defaultDutyDurationMinute = "45";
    } else {
        customAlert('WARNING: Only .25, .50 and .75 fractions are allowed for Default Duty Duration.');
        return;
    }
    let defaultDutyDuration = (parseInt(defaultDutyDurationHour * 3600) + parseInt(defaultDutyDurationMinute * 60));
    let defaultNumberweeksRotaPattern = $('#defaultNumberweeksRotaPattern').val();
    let maskAfter = $('#maskAfter').val();
    let dailyViewMaskingDays = $('#dailyViewMaskingDays').val();
    let freelancerMaskingDays = $('#freelancerMaskingDays').val();
    let schEmail = $('#schEmail').val();
    let errormessage = '';
    let RGEXToCheck = /^([a-zA-Z][0-9]{0,500})$/;
    if (tabname == 'identity') {
        if (schedulingTeamName == null || schedulingTeamName == '') {
            errormessage += 'Scheduling team name is a mandatory field.\n';
        }
        if (divisionId == null || divisionId == '-1') {
            errormessage += 'Area Name is a mandatory field. Please select one from dropdown.\n';
        }
    } else if (tabname == 'allocations') {
        if (defaultSicknessHoursAllocation == null || defaultSicknessHoursAllocation == '-1') {
            errormessage += 'Default Sickness Hours Allocation is a mandatory field.\n';
        }
        if (defaultDutyDuration == null || defaultDutyDuration == '') {
            errormessage += 'Default Duty Duration is a mandatory field.\n';
        }

        if (maskAfter == null || maskAfter == '') {
            errormessage += 'Mask After is a mandatory field.\n';
        }

        if ($("#DailyViewMasking").prop("checked") == true) {
            if (dailyViewMaskingDays == null || dailyViewMaskingDays == '') {
                errormessage += 'Daily View Masking Days is a mandatory field.\n';
            }
        }

        if ($("#freelancerMasking").prop("checked") == true) {
            if (freelancerMaskingDays == null || freelancerMaskingDays == '') {
                errormessage += 'Freelancer Masking Days is a mandatory field.\n';
            }
        }

        if ($("#restrictedEditing").prop("checked") == true) {

            if ($('#numberofDaysAllowedEditing').val() == null || $('#numberofDaysAllowedEditing').val() == '') {
                errormessage += 'Number of Days Allowed Editing is a mandatory field.\n';
            }
            let editingStartHour = $('#editingStartHour').val();
            let editingStartMinute = $('#editingStartMinute').val();
            let editingEndHour = $('#editingEndHour').val();
            let editingEndMinute = $('#editingEndMinute').val();
            if ((editingStartHour == null || editingStartHour == '' || editingStartHour == 0) && (editingStartMinute == null || editingStartMinute == '' || editingStartMinute == 0)) {
                editingStartHour = "0";
                editingStartMinute = "0";
            }

            if ((editingEndMinute == null || editingEndMinute == '' || editingEndMinute == 0) && (editingEndHour == null || editingEndHour == '' || editingEndHour == 0)) {
                editingEndHour = "0";
                editingEndMinute = "0";
            }
        }

        if ($("#locks").prop("checked") == true) {

            if ($('#locksStart').val() == null || $('#locksStart').val() == '') {
                errormessage += 'Locks Start is a mandatory field.\n';
            }

            if ($('#locksEnd').val() == null || $('#locksEnd').val() == '') {
                errormessage += 'Locks End is a mandatory field.\n';
            }

        }

        if ($("#signIn").prop("checked") == true) {

            if ($('#signInDays').val() == null || $('#signInDays').val() == '') {
                errormessage += 'Sign In Days is a mandatory field.\n';
            }
        }

        if ($("#autoImportWeeks").prop("checked") == true) {

            if ($('#NoofAutoAutoimportWeeks').val() == null || $('#NoofAutoAutoimportWeeks').val() == '') {
                errormessage += 'No of Auto AutoimportWeeks is a mandatory field.\n';
            }
        }
    } else if (tabname == 'miscellaneous') {
        if (defaultNumberweeksRotaPattern == null || defaultNumberweeksRotaPattern == '') {
            errormessage += 'Default Number of weeks in RotaPattern is a mandatory field.\n';
        }

        if ($('#staffAvailabilityReportStartDate').val() != '') {
            var DateNew = $('#staffAvailabilityReportStartDate').val();
            var SplitDate = DateNew.split("-").reverse().join("-");
            var DayOfWeek = getDayOfWeek(SplitDate);
            if (DayOfWeek != "Saturday") {
                errormessage += 'Please ensure that your Staff Availability Report Start Date is a Saturday.\n';
            }
        } else {
            errormessage += 'Staff Availability Report Start Date is a mandatory field.\n';
        }
        /***** email validation starts here *****/
        let explodeSchEmail, schEmailFlag = 0;
        if ((schEmail != '') && (schEmail !== undefined)) {
            let schEmailRegex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            explodeSchEmail = schEmail.split(';');
            $.each(explodeSchEmail, function (index, value) {
                if (!schEmailRegex.test(value)) {
                    schEmailFlag = 1;
                }
            });

            if (schEmailFlag == 1) {
                errormessage += 'Please enter a valid Email ID.\n';
            }
        } else {
            schEmailFlag = 0;
        }
        /***** email validation ends here *****/
    }

    if (errormessage != '') {
        customAlert('WARNING:' + '\n' + errormessage);
        return;
    }

    if (tabname == 'identity') {
        document.getElementById('identitytab').style.display = "none";
        document.getElementById('allocationtab').style.display = "block";
        document.getElementById('miscellaneoustab').style.display = "none";
        $("#identityTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#allocationTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#identityTabDone").val(1);
    } else if (tabname == 'allocations') {
        document.getElementById('identitytab').style.display = "none";
        document.getElementById('allocationtab').style.display = "none";
        document.getElementById('miscellaneoustab').style.display = "block";
        $("#allocationTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#miscellaneousTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#allocationTabDone").val(1);
    } else if (tabname == 'miscellaneous') {
        let defaultDutyDurationAll = $('#defaultDutyDuration').val().toString().split(/[.:]+/);
        let defaultDutyDurationHour = defaultDutyDurationAll.length > 0 ? defaultDutyDurationAll[0] : 0;
        let defaultDutyDurationMinute = defaultDutyDurationAll.length > 1 ? defaultDutyDurationAll[1] : 0;
        if (defaultDutyDurationMinute == "0" || defaultDutyDurationMinute == "00") {
            defaultDutyDurationMinute = "0";
        } else if (defaultDutyDurationMinute == "25") {
            defaultDutyDurationMinute = "15";
        } else if (defaultDutyDurationMinute == "50" || defaultDutyDurationMinute == "5") {
            defaultDutyDurationMinute = "30";
        } else if (defaultDutyDurationMinute == "75") {
            defaultDutyDurationMinute = "45";
        } else {
            customAlert('WARNING: Only .25, .50 and .75 fractions are allowed for Default Duty Duration.');
            return;
        }
        let defaultDutyDuration = (parseInt(defaultDutyDurationHour * 3600) + parseInt(defaultDutyDurationMinute * 60));
        $.ajax({
            type: 'POST',
            url: '../page-includes/admin/process/schedulingTeam.php',
            dataType: "json",
            data: {
                task: 'createupdateschedulingteam',
                schedulingTeamName: schedulingTeamName,
                schedulingTeamDescription: $('#schedulingTeamDescription').val(),
                divisionId: divisionId,
                defaultChargeCode: defaultChargeCode,
                defaultChargeCodeDescription: defaultChargeCodeDesc,
                defaultActiveCode: defaultActiveCode,
                isActive: $('#isActive').prop('checked') == true ? 1 : 0,
                defaultSicknessHoursAllocation: $('#defaultSicknessHoursAllocation').val(),
                defaultDutyDuration: defaultDutyDuration,
                workTimeDirectiveOptOut: $('#workTimeDirectiveOptOut').prop('checked') == true ? 1 : 0,
                checkOverSixDaysWorked: $('#checkOverSixDaysWorked').prop('checked') == true ? 1 : 0,
                checkOverFiveDaysWorked: $('#checkOverFiveDaysWorked').prop('checked') == true ? 1 : 0,
                signIn: $('#signIn').prop('checked') == true ? 1 : 0,
                signInDays: $('#signInDays').val(),
                allowInBuilding: $('#allowInBuilding').prop('checked') == true ? 1 : 0,
                allowOvertimeRequests: $('#allowOvertimeRequests').prop('checked') == true ? 1 : 0,
                colourWeek: $('#colourWeek').prop('checked') == true ? 1 : 0,
                locks: $('#locks').prop('checked') == true ? 1 : 0,
                locksStart: $('#locksStart').val(),
                locksEnd: $('#locksEnd').val(),
                locksWeekataTime: $('#locksWeekataTime').prop('checked') == true ? 1 : 0,
                maskType: $('#maskType').val(),
                maskAfter: $('#maskAfter').val(),
                DailyViewMasking: $('#DailyViewMasking').prop('checked') == true ? 1 : 0,
                dailyViewMaskingDays: $('#dailyViewMaskingDays').val(),
                freelancerMasking: $('#freelancerMasking').prop('checked') == true ? 1 : 0,
                freelancerMaskingDays: $('#freelancerMaskingDays').val(),
                restrictedEditing: $('#restrictedEditing').prop('checked') == true ? 1 : 0,
                numberofDaysAllowedEditing: $('#numberofDaysAllowedEditing').val(),
                editingStartHour: $('#editingStartHour').val(),
                editingStartMinute: $('#editingStartMinute').val(),
                editingEndHour: $('#editingEndHour').val(),
                editingEndMinute: $('#editingEndMinute').val(),
                WeekendOnly: $('#WeekendOnly').prop('checked') == true ? 1 : 0,
                autoLockTodayTimer: $('#autoLockTodayTimer').prop('checked') == true ? 1 : 0,
                hasGridChecks: $('#hasGridChecks').prop('checked') == true ? 1 : 0,
                showProductionView: $('#showProductionView').prop('checked') == true ? 1 : 0,
                autoImportWeeks: $('#autoImportWeeks').prop('checked') == true ? 1 : 0,
                NoofAutoAutoimportWeeks: $('#NoofAutoAutoimportWeeks').val(),
                hasXmasPoints: $('#hasXmasPoints').prop('checked') == true ? 1 : 0,
                defaultNumberweeksRotaPattern: defaultNumberweeksRotaPattern,
                leaveSelectiveHide: $('#leaveSelectiveHide').prop('checked') == true ? 1 : 0,
                hasHandovers: $('#hasHandovers').prop('checked') == true ? 1 : 0,
                staffAvailabilityReportStartDate: $('#staffAvailabilityReportStartDate').val(),
                intnewschteamid: $('#intnewschteamid').val(),
                taskcreateedit: taskcreateedit,
                tabname: tabname,
                identityTabDone: $("#identityTabDone").val(),
                allocationTabDone: $("#allocationTabDone").val(),
                miscelTabDone: $("#miscelTabDone").val(),
                schEmail: schEmail,
                restrictCopyDuty: $('#restrictCopyDuty').prop('checked') == true ? 1 : 0,
                showJobsInWeeklyView: $('#showJobsInWeeklyView').prop('checked') == true ? 1 : 0,
                createDutyFromRota: $('#createDutyFromRota').prop('checked') == true ? 1 : 0,
                showEditYearly: $('#showEditYearly').prop('checked') == true ? 1 : 0,
                restrictDeleteDuty: $('#restrictDeleteDuty').prop('checked') == true ? 1 : 0,
                restrictApplyRota: $('#restrictApplyRota').prop('checked') == true ? 1 : 0,
                schedulingGroups: getSelectedSchedulingGroups()
            },
            success: function (Result) {
                if (Result.sqlstatus == 1) {
                    if ($("#intnewschteamid").val()) {
                        customAlert('Scheduling Team record modified successfully.');
                    } else {
                        customAlert('Scheduling Team record inserted successfully.');
                    }
                    $("#intnewschteamid").val(Result.intnewidschteam);
                    if (tabname == 'identity') {
                        document.getElementById('identitytab').style.display = "none";
                        document.getElementById('allocationtab').style.display = "block";
                        document.getElementById('miscellaneoustab').style.display = "none";
                        $("#identityTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
                        $("#allocationTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
                        $("#identityTabDone").val(1);
                    } else if (tabname == 'allocations') {
                        document.getElementById('identitytab').style.display = "none";
                        document.getElementById('allocationtab').style.display = "none";
                        document.getElementById('miscellaneoustab').style.display = "block";
                        $("#allocationTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
                        $("#miscellaneousTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
                        $("#allocationTabDone").val(1);
                    } else {
                        document.getElementById('identitytab').style.display = "block";
                        document.getElementById('allocationtab').style.display = "none";
                        document.getElementById('miscellaneoustab').style.display = "none";
                        $("#identityTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
                        $("#miscellaneousTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
                        resetIdentityTab(1);
                        resetAllocationsTab(1);
                        resetMiscellaneousTab(1);
                        let modalsaveclose = document.getElementById("myModalSchedTeam");
                        modalsaveclose.style.display = "none";
                        $('#schedulingTeamDetails').DataTable().clear().destroy();
                        cleanupSchedulingTeamFixedColumnsArtifacts();
                        getSchedulingTeamDetails();
                        $("#miscelTabDone").val(1);
                    }
                    $("#loading").hide();
                    return;
                } else {
                    $("#loading").hide();
                    customAlert(Result.sqlstatusstring);
                    return;
                }
            },
            error: function (x, e) {
                if (x.status == 0) {
                    alert('You are offline!!\n Please Check Your Network.');
                } else if (x.status == 404) {
                    alert('Requested URL not found.');
                } else if (x.status == 500) {
                    alert('Internal Server Error.');
                } else if (e == 'parsererror') {
                    alert('Error.\nParsing JSON Request failed.');
                } else if (e == 'timeout') {
                    alert('Request Time out.');
                } else {
                    alert('Unknown Error.\n' + x.responseText);
                }
            }
        });
    }
}

/*
* @Description : Function to reset identity tab.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function resetIdentityTab(resetfrom) {
    if (resetfrom == 1) {
        getalldivisionsbyuser();
    }
    $('#schedulingTeamName').val('');
    $('#schedulingTeamDescription').val('');
    $('#divisionId').val('-1');
    $('#schedulingGroups').prop('disabled', false);
    $('#schedulingGroups').val([]).trigger('chosen:updated');;
    $('#schedulingGroups').empty();
    $('#selectedGroupTeams').empty();
    $('#defaultChargeCode').val('');
    $('#defaultChargeCodeDesc').val('');
    $('#defaultActiveCode').val('0');
    $('#defaultBreaksGroup').val('-1');
    $("#identityTabDone").val('');
    $("#allocationTabDone").val('');
    $("#miscelTabDone").val('');
    $('#isActive').prop("checked", true);
    $('#identitynext').on('click', function () {
        document.getElementById('identitytab').style.display = "none";
        document.getElementById('allocationtab').style.display = "block";
        document.getElementById('miscellaneoustab').style.display = "none";
        $("#identityTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#allocationTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
    });
}

/*
* @Description : Function to reset Allocations tab.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function resetAllocationsTab(resetfrom) {
    if (resetfrom == 1) {
        PopulateHoursDropDown();
        PopulateMinutesDropDown();
        getallmasktype();
    }
    $('#defaultSicknessHoursAllocation').val('3');
    $('#defaultDutyDuration').val('');
    $('#defaultDutyDuration').on('keyup', function () {
        let ControlName = '#defaultDutyDuration';
        let FieldName = 'Default Duty Duration';
        integerTypeValidationWithTwoDecimal(ControlName, FieldName);
    });

    $('#workTimeDirectiveOptOut').prop("checked", false);
    $('#checkOverSixDaysWorked').prop("checked", false);
    $('#checkOverFiveDaysWorked').prop("checked", false);
    $('#signIn').prop("checked", false);
    $('#signInDays').prop('disabled', true);
    $('#signIn').change(function () {
        if ($("#signIn").prop("checked") == true) {
            $('#signInDays').prop('disabled', false);
            $('#signInDays').val('');
            $('#signInDays').on('keyup', function () {
                let ControlName = '#signInDays';
                let FieldName = 'Sign-In Days';
                integerTypeValidation(ControlName, FieldName);
            });
        } else {
            $('#signInDays').prop('disabled', true);
            $('#signInDays').val('');
        }
    });
    $('#signInDays').val('');
    $('#allowInBuilding').prop("checked", false);
    $('#allowOvertimeRequests').prop("checked", false);
    $('#colourWeek').prop("checked", false);
    $('#locks').prop("checked", false);
    $('#locks').change(function () {
        if ($("#locks").prop("checked") == true) {
            $('#locksStart').prop('disabled', false);
            $('#locksStart').val('');
            $('#locksStart').on('keyup', function () {
                let ControlName = '#locksStart';
                let FieldName = 'Locks Start';
                integerTypeValidation(ControlName, FieldName);
            });
            $('#locksEnd').prop('disabled', false);
            $('#locksEnd').val('');
            $('#locksEnd').on('keyup', function () {
                let ControlName = '#locksEnd';
                let FieldName = 'Locks End';
                integerTypeValidation(ControlName, FieldName);
            });
            $('#locksWeekataTime').prop("disabled", false);
        } else {
            $('#locksStart').prop('disabled', true);
            $('#locksStart').val('');
            $('#locksEnd').prop('disabled', true);
            $('#locksEnd').val('');
            $('#locksWeekataTime').prop("checked", false);
            $('#locksWeekataTime').prop("disabled", true);
        }
    });
    $('#locksStart').prop('disabled', true);
    $('#locksStart').val('');
    $('#locksEnd').prop('disabled', true);
    $('#locksEnd').val('');
    $('#locksWeekataTime').prop("checked", false);
    $('#locksWeekataTime').prop("disabled", true);
    $('#maskType').val('-1');
    $('#maskAfter').val('');
    $('#maskAfter').on('keyup', function () {
        let ControlName = '#maskAfter';
        let FieldName = 'Mask After';
        integerTypeValidation(ControlName, FieldName);
    });
    $('#DailyViewMasking').prop("checked", false);
    $('#dailyViewMaskingDays').prop('disabled', true);
    $('#DailyViewMasking').change(function () {
        if ($("#DailyViewMasking").prop("checked") == true) {
            $('#dailyViewMaskingDays').prop('disabled', false);
            $('#dailyViewMaskingDays').val('');
            $('#dailyViewMaskingDays').on('keyup', function () {
                let ControlName = '#dailyViewMaskingDays';
                let FieldName = 'Daily View Masking Days';
                integerTypeValidation(ControlName, FieldName);
            });

        } else {
            $('#dailyViewMaskingDays').prop('disabled', true);
            $('#dailyViewMaskingDays').val('');
        }
    });
    $('#dailyViewMaskingDays').val('');
    $('#freelancerMasking').prop("checked", false);
    $('#freelancerMasking').change(function () {
        if ($("#freelancerMasking").prop("checked") == true) {
            $('#freelancerMaskingDays').prop('disabled', false);
            $('#freelancerMaskingDays').val('');
            $('#freelancerMaskingDays').on('keyup', function () {
                let ControlName = '#freelancerMaskingDays';
                let FieldName = 'Freelancer Masking Days';
                integerTypeValidation(ControlName, FieldName);
            });
        } else {
            $('#freelancerMaskingDays').prop('disabled', true);
            $('#freelancerMaskingDays').val('');
        }
    });
    $('#freelancerMaskingDays').val('');
    $('#freelancerMaskingDays').prop('disabled', true);
    $('#restrictedEditing').prop("checked", false);
    $('#restrictedEditing').change(function () {
        if ($("#restrictedEditing").prop("checked") == true) {
            $('#numberofDaysAllowedEditing').prop('disabled', false);
            $('#numberofDaysAllowedEditing').val('0');
            $('#numberofDaysAllowedEditing').on('keyup', function () {
                let ControlName = '#numberofDaysAllowedEditing';
                let FieldName = 'Number of Days Allowed Editing';
                integerTypeValidation(ControlName, FieldName);
            });
            $('#editingStartHour').prop('disabled', false);
            $('#editingStartMinute').prop('disabled', false);
            $('#editingEndHour').prop('disabled', false);
            $('#editingEndMinute').prop('disabled', false);
            $('#editingStartHour').val('0');
            $('#editingStartMinute').val('0');
            $('#editingEndHour').val('0');
            $('#editingEndMinute').val('0');
            $('#WeekendOnly').prop("disabled", false);
            $('#autoLockTodayTimer').prop("disabled", false);
            $('#WeekendOnly').prop("checked", false);
            $('#autoLockTodayTimer').prop("checked", false);
        } else {
            $('#numberofDaysAllowedEditing').prop('disabled', true);
            $('#numberofDaysAllowedEditing').val('0');
            $('#editingStartHour').prop('disabled', true);
            $('#editingStartMinute').prop('disabled', true);
            $('#editingEndHour').prop('disabled', true);
            $('#editingEndMinute').prop('disabled', true);
            $('#editingStartHour').val('0');
            $('#editingStartMinute').val('0');
            $('#editingEndHour').val('0');
            $('#editingEndMinute').val('0');
            $('#WeekendOnly').prop("disabled", true);
            $('#autoLockTodayTimer').prop("disabled", true);
            $('#WeekendOnly').prop("checked", false);
            $('#autoLockTodayTimer').prop("checked", false);
        }
    });
    $('#numberofDaysAllowedEditing').prop('disabled', true);
    $('#numberofDaysAllowedEditing').val('0');
    $('#numberofDaysAllowedEditing').prop('disabled', true);
    $('#numberofDaysAllowedEditing').val('');
    $('#editingStartHour').prop('disabled', true);
    $('#editingStartMinute').prop('disabled', true);
    $('#editingEndHour').prop('disabled', true);
    $('#editingEndMinute').prop('disabled', true);
    $('#editingStartHour').val('0');
    $('#editingStartMinute').val('0');
    $('#editingEndHour').val('0');
    $('#editingEndMinute').val('0');
    $('#WeekendOnly').prop("disabled", true);
    $('#autoLockTodayTimer').prop("disabled", true);
    $('#WeekendOnly').prop("checked", false);
    $('#autoLockTodayTimer').prop("checked", false);
    $('#hasGridChecks').prop("checked", false);
    $('#showProductionView').prop("checked", false);
    $('#showJobsInWeeklyView').prop("checked", false);
    $('#createDutyFromRota').prop("checked", false);
    $('#autoImportWeeks').prop("checked", false);
    $('#autoImportWeeks').change(function () {
        if ($("#autoImportWeeks").prop("checked") == true) {
            $('#NoofAutoAutoimportWeeks').prop('disabled', false);
            $('#NoofAutoAutoimportWeeks').val('');
            $('#NoofAutoAutoimportWeeks').on('keyup', function () {
                let ControlName = '#NoofAutoAutoimportWeeks';
                let FieldName = 'Number of Auto Import Weeks';
                integerTypeValidation(ControlName, FieldName);
            });

        } else {
            $('#NoofAutoAutoimportWeeks').prop('disabled', true);
            $('#NoofAutoAutoimportWeeks').val('N/A');
        }
    });
    $('#NoofAutoAutoimportWeeks').prop('disabled', true);
    $('#NoofAutoAutoimportWeeks').val('N/A');
    $('#AllocationsNext').on('click', function () {
        document.getElementById('identitytab').style.display = "none";
        document.getElementById('allocationtab').style.display = "none";
        document.getElementById('miscellaneoustab').style.display = "block";
        $("#allocationTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#miscellaneousTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
    });
    $('#AllocationsBack').on('click', function () {
        document.getElementById('identitytab').style.display = "block";
        document.getElementById('allocationtab').style.display = "none";
        document.getElementById('miscellaneoustab').style.display = "none";
        $("#allocationTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#identityTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
    });

    $('#showEditYearly').prop("checked", false);
    $('#restrictDeleteDuty').prop("checked", false);
    $('#restrictDeleteDuty').prop('disabled', true);
    $('#restrictApplyRota').prop("checked", false);
    $('#restrictApplyRota').prop('disabled', true);
    $('#showEditYearly').change(function () {
        if ($("#showEditYearly").prop("checked") == true) {
            $('#restrictDeleteDuty').prop('disabled', false);
            $('#restrictApplyRota').prop('disabled', false);
        } else {
            $('#restrictDeleteDuty').prop("checked", false);
            $('#restrictDeleteDuty').prop('disabled', true);
            $('#restrictApplyRota').prop("checked", false);
            $('#restrictApplyRota').prop('disabled', true);
        }
    });
}

/*
* @Description : Function to reset Miscellaneous tab.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function resetMiscellaneousTab(resetfrom) {
    let dateControlName = '#defaultRotaStartDate'
    let mindate = '01-01-1995';
    let currdate = new Date();
    let day = currdate.getDate();
    if (day < 10) {
        day = '0' + day;
    }
    let month = currdate.getMonth() + 1;
    if (month < 10) {
        month = '0' + month;
    }
    let year = currdate.getFullYear() + 7;
    let maxdate = day + '-' + month + '-' + year;
    let yearrange = '1995:' + year;
    $('#defaultNumberweeksRotaPattern').val('');
    $('#defaultNumberweeksRotaPattern').on('keyup', function () {
        let ControlName = '#defaultNumberweeksRotaPattern';
        let FieldName = 'Default Number Weeks in Rota Pattern';
        integerTypeValidation(ControlName, FieldName);
    });
    $('#defaultRotaStartDate').val('');
    callDatePicker(dateControlName, mindate, maxdate, yearrange);

    let startyear = currdate.getFullYear() - 7;
    let currleaveyearobj = [];
    let $listcurrleaveyear = '<option value="-1">Select Current Leave Year</option>';
    for (let i = startyear; i <= startyear + 15; i++) {
        currleaveyearobj[i] = i;
    }
    for (let j = startyear; j < startyear + 15; j++) {
        $listcurrleaveyear += '<option value=' + currleaveyearobj[j] + '>' + currleaveyearobj[j] + '</option>';
    }
    $('#leaveSelectiveHide').prop("checked", false);
    $('#hasHandovers').prop("checked", false);
    $('#hasXmasPoints').prop("checked", false);
    dateControlName = '#staffAvailabilityReportStartDate';
    mindate = month + '-' + day + '-' + (year - 14);
    yearrange = (year - 14) + ':' + year;
    $('#staffAvailabilityReportStartDate').val('');
    callDatePicker(dateControlName, mindate, maxdate, yearrange);
    $('#miscellaneousBack').on('click', function () {
        document.getElementById('identitytab').style.display = "none";
        document.getElementById('allocationtab').style.display = "block";
        document.getElementById('miscellaneoustab').style.display = "none";
        $("#miscellaneousTab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
        $("#allocationTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
    });
}

/*
* @Description : Function to get all division by user.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getalldivisionsbyuser() {
    $.ajax({
        type: 'POST',
        url: '../page-includes/admin/process/schedulingTeam.php',
        dataType: "json",
        async: false,
        data: {
            task: 'getalldivisionsbyuser'
        },
        success: function (Result) {
            if (Result.status == 1) {
                let $divisionlist = null;
                $('#divisionId').html('');
                $divisionlist += '<option value="-1">Select Area</option>';
                let is_selected = '';
                if (Result.data.length == 1) {
                    is_selected = 'selected';
                }
                Result.data.forEach((number, index, array) => {
                    $divisionlist += '<option value=' + array[index]['DivisionID'] + ' ' + is_selected + '  >' + array[index]['DivisionName'] + '</option>';
                });
                $('#divisionId').html($divisionlist);
                $("#loading").hide();
                return;
            }
        },
        error: function (x, e) {
            if (x.status == 0) {
                alert('You are offline!!\n Please Check Your Network.');
            } else if (x.status == 404) {
                alert('Requested URL not found.');
            } else if (x.status == 500) {
                alert('Internal Server Error.');
            } else if (e == 'parsererror') {
                alert('Error.\nParsing JSON Request failed.');
            } else if (e == 'timeout') {
                alert('Request Time out.');
            } else {
                alert('Unknown Error.\n' + x.responseText);
            }
        }
    });
}

/*
* @Description : Function to populate hours dropdown.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function PopulateHoursDropDown() {
    let hour_options = [];
    let hrVal;
    for (hrVal = 0; hrVal <= 23; hrVal++) {
        hrVal = String(hrVal).length < 2 ? '0' + hrVal : hrVal;
        hour_options.push(hrVal);
    }
    $('#editingStartHour').html('');
    $('#editingEndHour').html('');
    let $divisionlist = null;
    $.each(hour_options, function (index, value) {
        $divisionlist += '<option value=' + index + '>' + value + '</option>';
    });
    $('#editingStartHour').html($divisionlist);
    $('#editingEndHour').html($divisionlist);
}

/*
* @Description : Function to populate minutes dropdown.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function PopulateMinutesDropDown() {
    let minute_options = [];
    let minVal;
    for (minVal = 0; minVal <= 59; minVal++) {
        minVal = String(minVal).length < 2 ? '0' + minVal : minVal;
        minute_options.push(minVal);
    }

    $('#editingStartMinute').html('');
    $('#editingEndMinute').html('');

    let $divisionlist = null;
    $.each(minute_options, function (index, value) {
        $divisionlist += '<option value=' + index + '>' + value + '</option>';
    });
    $('#editingStartMinute').html($divisionlist);
    $('#editingEndMinute').html($divisionlist);
    return;
}

/*
* @Description : Function to get all masktype.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getallmasktype() {
    $.ajax({
        type: 'POST',
        url: '../page-includes/admin/process/schedulingTeam.php',
        dataType: "json",
        data: {
            task: 'getallmasktype'
        },
        success: function (Result) {
            if (Result.status == 1) {
                let $masktypelist = null;
                $('#maskType').html('');
                // $masktypelist += '<option value="-1">Select Mask Type</option>';
                Result.data.forEach((number, index, array) => {
                    $masktypelist += '<option value=' + array[index]['maskTypeId'] + '>' + array[index]['maskTypeName'] + '</option>';
                });
                $('#maskType').html($masktypelist);
                $("#loading").hide();
                return;
            } else {
                alert(Result.data);
                return;
            }
        },
        error: function (x, e) {
            if (x.status == 0) {
                alert('You are offline!!\n Please Check Your Network.');
            } else if (x.status == 404) {
                alert('Requested URL not found.');
            } else if (x.status == 500) {
                alert('Internal Server Error.');
            } else if (e == 'parsererror') {
                alert('Error.\nParsing JSON Request failed.');
            } else if (e == 'timeout') {
                alert('Request Time out.');
            } else {
                alert('Unknown Error.\n' + x.responseText);
            }
        }
    });
}

/*
* @Description : Function to get all masktype.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getSchedulingTeamDetailsForPopup(schedulingTeamId) {
    $.ajax({
        type: 'POST',
        url: '../page-includes/admin/process/schedulingTeam.php',
        dataType: "json",
        data: {
            task: 'getSchedulingTeamDetailsForPopup',
            schedulingTeamId: schedulingTeamId
        },
        success: function (Result) {
            if (Result.status == 1) {
                // append result to popup from here
                openPopupandAdddataschTeam(Result.data);
            } else {
                alert(Result.data);
                $("#loading").hide();
                return;
            }
        },
        error: function (x, e) {
            if (x.status == 0) {
                alert('You are offline!!\n Please Check Your Network.');
            } else if (x.status == 404) {
                alert('Requested URL not found.');
            } else if (x.status == 500) {
                alert('Internal Server Error.');
            } else if (e == 'parsererror') {
                alert('Error.\nParsing JSON Request failed.');
            } else if (e == 'timeout') {
                alert('Request Time out.');
            } else {
                alert('Unknown Error.\n' + x.responseText);
            }
        }
    });
}

/*
* @Description : Function to open popup and call for function to add result to popup controls.
* @access : Public
* @global : N/A
* @param  : popupresult
* @return : N/A
*/
function openPopupandAdddataschTeam(popupresult) {
    resetIdentityTab(1);
    resetAllocationsTab(1);
    resetMiscellaneousTab(1);
    setTimeout(function () {
        getMappedActiveCodeByTeam(popupresult.EstablishCode, popupresult.defaultActiveCode);
        resetviewhistoryeditbutton();

        const divisionIdForGroups = popupresult.divisionId ?? popupresult.divisionid;
        $('#divisionId').val(String(divisionIdForGroups));

        // If the division is missing in the dropdown, append it
        if ($('#divisionId').val() != String(divisionIdForGroups)) {
            $('#divisionId').append(
                `<option value="${divisionIdForGroups}" selected>${popupresult.DivisionName}</option>`
            );
            $('#divisionId').val(String(divisionIdForGroups));
        }

        // Populate Identity, Allocation, Misc tabs
        addschteamdetailstopopup(popupresult);

        // Normalize mapped group IDs
        const mapped = normalizeMappedGroupIds(popupresult.mappedSchedulingGroups);

        // Load the groups NOW that divisions are ready
        primeSchedulingGroups(divisionIdForGroups, mapped).then(() => {

            $('#headerschteam').html('Edit Scheduling Team');
            $("#identitytab").show();
            $("#allocationtab").hide();
            $("#miscellaneoustab").hide();
            $(".schedulepersontab").removeClass("ui-tabs-active ui-state-active ui-state-focus");
            $("#identityTab").addClass("ui-tabs-active ui-state-active ui-state-focus");
            openModalPopup();
        });
    }, 500);
}

/*
* @Description : Function to add result to popup controls.
* @access : Public
* @global : N/A
* @param  : popupresult
* @return : N/A
*/
function addschteamdetailstopopup(popupresult) {
    $('#intnewschteamid').val(popupresult.schedulingTeamId);
    $('#schedulingTeamName').val(popupresult.schedulingTeamName);
    $('#schedulingTeamDescription').val(popupresult.schedulingTeamDescription);
    $('#divisionId').find('option[value=' + popupresult.divisionId + ']').attr('selected', 'selected');
    $('#defaultChargeCode').val(popupresult.EstablishCode);
    $('#defaultChargeCodeDesc').val(popupresult.EstablishCodeDescription);
    $('#defaultActiveCode').find('option[value=' + popupresult.defaultActiveCode + ']').attr('selected', 'selected');
    if ((popupresult.divisionid > 0) && $('#divisionId option[value="' + popupresult.divisionid + '"]').length === 0) {
        $('#divisionId').val(popupresult.divisionid);
        $('#divisionId').append('<option value="' + popupresult.divisionid + '" selected>' + popupresult.DivisionName + '</option>');
    }
    (popupresult.isActive == 1 ? $('#isActive').prop("checked", true) : $('#isActive').prop("checked", false));

    if (editButton == 0) {
        $('#schedulingTeamName').attr("disabled", true);
        $('#schedulingTeamDescription').attr("disabled", true);
        $('#divisionId').attr("disabled", true);
        $('#defaultChargeCode').attr("disabled", true);
        $('#defaultChargeCodeDesc').attr("disabled", true);
        $('#defaultActiveCode').attr("disabled", true);
        $('#isActive').attr("disabled", true);
        $('#divisionId').addClass('activeclass');
        $('#divisionName').removeClass('activeclass');
        $('#divisionName').val(popupresult.DivisionName);
        $('#divisionId').html('<option value="' + popupresult.divisionId + '" selected>' + popupresult.DivisionName + '</option>');
    } else {
        $('#schedulingTeamName').attr("disabled", false);
        $('#schedulingTeamDescription').attr("disabled", false);
        $('#divisionId').attr("disabled", false);
        $('#defaultChargeCode').attr("disabled", false);
        $('#defaultChargeCodeDesc').attr("disabled", false);
        $('#defaultActiveCode').attr("disabled", false);
        $('#isActive').attr("disabled", false);
        $('#divisionId').removeClass('activeclass');
        $('#divisionName').val('');
        $('#divisionName').addClass('activeclass');
        $('#identitysubmit').removeClass('activeclass');
    }

    // Allocation Tab
    $('#defaultSicknessHoursAllocation').find('option[value=' + popupresult.divisionId + ']').attr('selected', 'selected');
    $('#defaultSicknessHoursAllocation').val(popupresult.defaultSicknessHoursAllocation);
    let thisDurationhours = parseInt(popupresult.defaultDutyDuration / 3600);
    let Durationminutes = ((popupresult.defaultDutyDuration % 3600) / 60);
    if (Durationminutes == 15) {
        Durationminutes = "25";
    } else if (Durationminutes == 30) {
        Durationminutes = "50";
    } else if (Durationminutes == 45) {
        Durationminutes = "75";
    } else {
        Durationminutes = "00";
    }
    let TotalDefaultDutyDuration = ((thisDurationhours).toString() + "." + Durationminutes);
    $('#defaultDutyDuration').val(TotalDefaultDutyDuration);
    (popupresult.workTimeDirectiveOptOut == 1 ? $('#workTimeDirectiveOptOut').prop("checked", true) : $('#workTimeDirectiveOptOut').prop("checked", false));
    (popupresult.checkOverSixDaysWorked == 1 ? $('#checkOverSixDaysWorked').prop("checked", true) : $('#checkOverSixDaysWorked').prop("checked", false));
    (popupresult.checkOverFiveDaysWorked == 1 ? $('#checkOverFiveDaysWorked').prop("checked", true) : $('#checkOverFiveDaysWorked').prop("checked", false));
    if (popupresult.signIn == 1) {
        $('#signInDays').attr("disabled", false);
        $('#signIn').prop("checked", true);
        $('#signInDays').val(popupresult.signInDays);
    } else {
        $('#signIn').prop("checked", false);
        $('#signInDays').val('');
        $('#signInDays').attr("disabled", true);
    }
    (popupresult.allowInBuilding == 1 ? $('#allowInBuilding').prop("checked", true) : $('#allowInBuilding').prop("checked", false));
    if (popupresult.locks == 1) {
        $('#locksEnd').attr('disabled', false);
        $('#locksStart').attr('disabled', false);
        $('#locksWeekataTime').attr("disabled", false);

        $('#locks').prop("checked", true);
        $('#locksStart').val(popupresult.locksStart);
        $('#locksEnd').val(popupresult.locksEnd);
        (popupresult.locksWeekataTime == 1 ? $('#locksWeekataTime').prop("checked", true) : $('#locksWeekataTime').prop("checked", false));
    }
    else {
        $('#locks').prop("checked", false);
        $('#locksStart').val('');
        $('#locksEnd').val('');
        (popupresult.locksWeekataTime == 1 ? $('#locksWeekataTime').prop("checked", true) : $('#locksWeekataTime').prop("checked", false));

        $('#locksEnd').attr('disabled', true);
        $('#locksStart').attr('disabled', true);
        $('#locksWeekataTime').attr("disabled", true);
    }
    $('#maskType').find('option[value=' + popupresult.maskType + ']').attr('selected', 'selected');
    $('#maskType').val(popupresult.maskType);
    $('#maskAfter').val(popupresult.maskAfter);
    if (popupresult.DailyViewMasking == 1) {
        $('#dailyViewMaskingDays').attr("disabled", false);
        $('#DailyViewMasking').prop("checked", true);
        $('#dailyViewMaskingDays').val(popupresult.dailyViewMaskingDays);
    }
    else {
        $('#DailyViewMasking').prop("checked", false);
        $('#dailyViewMaskingDays').val('');
        $('#dailyViewMaskingDays').attr("disabled", true);
    }
    if (popupresult.freelancerMasking == 1) {
        $('#freelancerMaskingDays').prop('disabled', false);
        $('#freelancerMasking').prop("checked", true);
        $('#freelancerMaskingDays').val(popupresult.freelancerMaskingDays);
    } else {
        $('#freelancerMasking').prop("checked", false);
        $('#freelancerMaskingDays').val('');
        $('#freelancerMaskingDays').prop('disabled', true);
    }

    if (popupresult.restrictedEditing == 1) {
        $('#numberofDaysAllowedEditing').prop('disabled', false);
        $('#WeekendOnly').prop("disabled", false);
        $('#editingStartHour').prop('disabled', false);
        $('#editingStartMinute').prop('disabled', false);
        $('#editingEndHour').prop('disabled', false);
        $('#editingEndMinute').prop('disabled', false);
        $('#autoLockTodayTimer').prop("disabled", false);

        $('#restrictedEditing').prop("checked", true);
        let editingStartHour = parseInt(popupresult.editingStart / 3600);
        let editingStartMinute = parseInt((popupresult.editingStart % 3600) / 60);
        let editingEndHour = parseInt(popupresult.editingEnd / 3600);
        let editingEndMinute = parseInt((popupresult.editingEnd % 3600) / 60);
        $('#numberofDaysAllowedEditing').val(popupresult.numberofDaysAllowedEditing);
        (popupresult.WeekendOnly == 1 ? $('#WeekendOnly').prop("checked", true) : $('#WeekendOnly').prop("checked", false));
        $('#editingStartHour').find('option[value=' + editingStartHour + ']').attr('selected', 'selected');
        $('#editingStartHour').val(editingStartHour);
        $('#editingStartMinute').find('option[value=' + editingStartMinute + ']').attr('selected', 'selected');
        $('#editingStartMinute').val(editingStartMinute);
        $('#editingEndHour').find('option[value=' + editingEndHour + ']').attr('selected', 'selected');
        $('#editingEndHour').val(editingEndHour);
        $('#editingEndMinute').find('option[value=' + editingEndMinute + ']').attr('selected', 'selected');
        $('#editingEndMinute').val(editingEndMinute);
        (popupresult.autoLockTodayTimer == 1 ? $('#autoLockTodayTimer').prop("checked", true) : $('#autoLockTodayTimer').prop("checked", false));
    } else {
        $('#numberofDaysAllowedEditing').val('');
        (popupresult.WeekendOnly == 1 ? $('#WeekendOnly').prop("checked", true) : $('#WeekendOnly').prop("checked", false));
        $('#editingStartHour').val(0);
        $('#editingStartMinute').val(0);
        $('#editingEndHour').val(0);
        $('#editingEndMinute').val(0);
        $('#restrictedEditing').prop("checked", false);
        (popupresult.autoLockTodayTimer == 1 ? $('#autoLockTodayTimer').prop("checked", true) : $('#autoLockTodayTimer').prop("checked", false));

        $('#numberofDaysAllowedEditing').prop('disabled', true);
        $('#WeekendOnly').prop("disabled", true);
        $('#editingStartHour').prop('disabled', true);
        $('#editingStartMinute').prop('disabled', true);
        $('#editingEndHour').prop('disabled', true);
        $('#editingEndMinute').prop('disabled', true);
        $('#autoLockTodayTimer').prop("disabled", true);
    }

    (popupresult.hasGridChecks == 1 ? $('#hasGridChecks').prop("checked", true) : $('#hasGridChecks').prop("checked", false));
    if (popupresult.autoImportWeeks == 1) {
        $('#NoofAutoAutoimportWeeks').prop('disabled', false);
        $('#autoImportWeeks').prop("checked", true);
        $('#NoofAutoAutoimportWeeks').val(popupresult.NoofAutoAutoimportWeeks);
    } else {
        $('#autoImportWeeks').prop("checked", false);
        $('#NoofAutoAutoimportWeeks').val('N/A');
        $('#NoofAutoAutoimportWeeks').prop('disabled', true);
    }
    (popupresult.IsRestrictCopyDuty == 1 ? $('#restrictCopyDuty').prop("checked", true) : $('#restrictCopyDuty').prop("checked", false));
    (popupresult.showProductionView == 1 ? $('#showProductionView').prop("checked", true) : $('#showProductionView').prop("checked", false));
    (popupresult.IsCreateDutyFromRota == 1 ? $('#createDutyFromRota').prop("checked", true) : $('#createDutyFromRota').prop("checked", false));
    (popupresult.ShowJobsInWeeklyView == 1 ? $('#showJobsInWeeklyView').prop("checked", true) : $('#showJobsInWeeklyView').prop("checked", false));
    (popupresult.allowOvertimeRequests == 1 ? $('#allowOvertimeRequests').prop("checked", true) : $('#allowOvertimeRequests').prop("checked", false));
    (popupresult.colourWeek == 1 ? $('#colourWeek').prop("checked", true) : $('#colourWeek').prop("checked", false));
    (popupresult.IsShowEditYearly == 1 ? $('#showEditYearly').prop("checked", true) : $('#showEditYearly').prop("checked", false));
    $('#showEditYearly').trigger("change");
    (popupresult.IsRestrictDeleteDuty == 1 ? $('#restrictDeleteDuty').prop("checked", true) : $('#restrictDeleteDuty').prop("checked", false));
    (popupresult.RestrictApplyROTAPattern == 1 ? $('#restrictApplyRota').prop("checked", true) : $('#restrictApplyRota').prop("checked", false));

    // Miscellaneous tab
    $('#defaultNumberweeksRotaPattern').val(popupresult.defaultNumberweeksRotaPattern);
    (popupresult.leaveSelectiveHide == 1 ? $('#leaveSelectiveHide').prop("checked", true) : $('#leaveSelectiveHide').prop("checked", false));
    (popupresult.hasHandovers == 1 ? $('#hasHandovers').prop("checked", true) : $('#hasHandovers').prop("checked", false));
    (popupresult.hasXmasPoints == 1 ? $('#hasXmasPoints').prop("checked", true) : $('#hasXmasPoints').prop("checked", false));
    $('#staffAvailabilityReportStartDate').val(popupresult.staffAvailabilityReportStartDate);
    $('#schEmail').val(popupresult.Email);
}

/*
* @Description : Function to reset binding for edit and history button.
* @access : Public
* @global : Not Applicable
* @param  : N/A
* @return : N/A
*/
function resetviewhistoryeditbutton() {
    $(document).one('click', '.js_editschteam', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        let schedulingteamid = $(this).attr("value");
        editButton = $(this).data("editing");
        getSchedulingTeamDetailsForPopup(schedulingteamid);
    });

    $(document).one('click', '.js_historyschteam', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        schedulingteamid = $(this).attr("value");
        //get history model
        showHistory(schedulingteamid);
    });
}

/*
* @Description : Function to show history in popup
* @access : Public
* @global : Not Applicable
* @param  : schedulingteamid
* @return : N/A
*/
function showHistory(schedulingteamid) {
    $.post("function-includes/common/common.php", {
        divisionid: schedulingteamid,
        action: 'history',
        modulename: 'SchedulingTeams'
    },
        function (data, status) {
            $.facebox(data);
            $("#loading").hide();
            resetviewhistoryeditbutton();
        })
}

//highlight the selected table row
$(document).on('click', '#schedulingTeamDetails td', function () {
    $('tr').removeClass('highlightOrange');
    $(this).parent().addClass('highlightOrange');
});

$('#defaultChargeCode').on('keyup', function () {
    let defaultChargCodeInput = $(this).val();
    getMappedActiveCodeByTeam(defaultChargCodeInput);
});

/*
* @Description : Function to getmapped active code of team.
* @access : Public
* @global : N/A
* @param  : N/A
* @return : N/A
*/
function getMappedActiveCodeByTeam(defaultChargCodeInput, defaultActiveCode = 0) {
    $.ajax({
        type: 'POST',
        url: '../page-includes/admin/process/schedulingTeam.php',
        dataType: "json",
        data: {
            task: 'getmappedactivecodebyteam',
            chargeCode: defaultChargCodeInput
        },
        success: function (Result) {
            if (Result.status == 1) {
                let activecodemapped = '';
                $('#defaultActiveCode').html('');
                activecodemapped += '<option value="0">Select the Activity Code</option>';
                Result.data.forEach((number, index, array) => {
                    if ((array[index]['ActivityCodeId'] == defaultActiveCode)) { var seloption = 'selected'; } else { seloption = ''; }
                    activecodemapped += '<option value=' + array[index]['ActivityCodeId'] + " " + seloption + '>' + array[index]['ActivityCodeName'] + '</option>';
                });
                $('#defaultActiveCode').html(activecodemapped);
                $("#loading").hide();
                return;
            } else {
                $("#loading").hide();
                $('#defaultActiveCode').html('');
            }
        },
        error: function (x, e) {
            if (x.status == 0) {
                alert('You are offline!!\n Please Check Your Network.');
            } else if (x.status == 404) {
                alert('Requested URL not found.');
            } else if (x.status == 500) {
                alert('Internal Server Error.');
            } else if (e == 'parsererror') {
                alert('Error.\nParsing JSON Request failed.');
            } else if (e == 'timeout') {
                alert('Request Time out.');
            } else {
                alert('Unknown Error.\n' + x.responseText);
            }
        }
    });
}

/*
* @Description : Load Scheduling Groups filtered by selected Area (DivisionID). 
*                Populates the multi‑select dropdown and preselects mapped groups if provided.
* @access      : Public
* @global      : N/A
* @param       : areaId (int)        – DivisionID for filtering groups
* @param       : preselected (array|string) – IDs already mapped to the team
* @return      : Promise<Array>      – validSelectedIds selected IDs (after filtering)
*/
function loadSchedulingGroups(areaId, preselected = []) {
    return $.ajax({
        type: "POST",
        url: "../page-includes/admin/process/schedulingTeam.php",
        dataType: "json",
        data: {
            task: "getSchedulingGroupsByArea",
            divisionId: areaId
        }
    }).then(response => {
        if (response.status !== 1 || !Array.isArray(response.data)) {
            $("#schedulingGroups").prop("disabled", true).empty();
            $("#selectedGroupTeams").empty();
            return [];
        }

        $("#schedulingGroups").prop("disabled", false).empty();

        // Preselected to a set of strings
        let preArray = [];
        if (typeof preselected === "string") {
            preArray = preselected.split(",").map(s => s.trim()).filter(Boolean);
        } else if (Array.isArray(preselected)) {
            preArray = preselected.map(x => String(x));
        }
        const preSet = new Set(preArray);

        // Alphabetical order
        response.data.sort((a, b) => a.SchedulingGroupsName.localeCompare(b.SchedulingGroupsName));

        // Render options (REAL tags)
        const optionsHtml = response.data.map(g => {
            const groupId = String(g.SchedulingGroupsID);
            const selectedAttr = preSet.has(groupId) ? "selected" : "";
            return `<option value="${groupId}" ${selectedAttr}>${g.SchedulingGroupsName}</option>`;
        }).join("");

        $("#schedulingGroups").html(optionsHtml);

        // Intersection (handles groups outside this area)
        const allIds = response.data.map(g => String(g.SchedulingGroupsID));
        const validSelectedIds = preArray.filter(id => allIds.includes(id));

        // Apply selection first
        if (validSelectedIds.length) {
            $("#schedulingGroups").val(validSelectedIds);
            initOrUpdateSchedulingGroupsChosen();
        } else {
            $("#schedulingGroups").val([]); // nothing to select
        }

        // Returns the effective selection so caller can render info area
        return validSelectedIds;
    });
}

/*
* @Description : Retrieve currently selected Scheduling Group IDs from the multi-select.
* @access      : Public
* @global      : N/A
* @param       : N/A
* @return      : array - Selected group IDs
*/
function getSelectedSchedulingGroups() {
    return $("#schedulingGroups").val() || [];
}

/*
* @Description : Initialize Scheduling Groups in Edit mode. 
*                Loads groups by Area, preselects mapped ones, and renders info panel.
* @access      : Public
* @global      : N/A
* @param       : areaId (int)            - Team's Area/DivisionID
* @param       : mappedIds (array|string)- IDs mapped to the team
* @return      : Promise                 - resolves after UI is populated
*/
function primeSchedulingGroups(areaId, mappedIds) {
    return loadSchedulingGroups(areaId, mappedIds).then(effectiveIds => {
        initOrUpdateSchedulingGroupsChosen();

        // Also notify the UI listeners if needed
        //$("#schedulingGroups").trigger("change");
    });
}

/*
* @Description : Normalize incoming Scheduling Group IDs into a uniform string array.
*                Supports CSV strings, numeric/string arrays, or null values.
* @access      : Public
* @global      : N/A
* @param       : ids (string|array|null)
* @return      : array<string> - Normalized ID list
*/
function normalizeMappedGroupIds(ids) {
    if (!ids) return [];
    if (typeof ids === 'string') {
        return ids.split(',').map(s => s.trim()).filter(Boolean);
    }
    if (Array.isArray(ids)) {
        return ids.map(x => String(x));
    }
    return [];
}

/*
* @Description : Initializes or refreshes the Chosen UI plugin for the Scheduling Groups <select> element. Ensures proper
*                destruction and reinitialization when required.
* @access      : Public
* @global      : Not Applicable
* @param       : N/A
* @return      : N/A
*/
function initOrUpdateSchedulingGroupsChosen() {
    const $schedulingGroup = $('#schedulingGroups');

    // Always destroy BEFORE re-init
    if ($schedulingGroup.data('chosen')) {
        $schedulingGroup.chosen('destroy');
    }

    // Ensure it's enabled before applying Chosen
    const placeholder = $schedulingGroup.data('placeholder') || 'Select one or more groups';

    $schedulingGroup.chosen({
        placeholder_text_multiple: placeholder,
        search_contains: true,
        display_selected_options: false
    });
}
