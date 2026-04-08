$(document).ready(function() {
    $('#Processing').hide();
    $('#newleaveFrom').validate({
        errorLabelContainer: "#newLeaveFormerrorBox",
        rules: {
            "userNetlogin":{
                required: true,
            },
            "fullname":{
                required: true,
            },
            "leavegruop":{
                required: true,
            },
            "leavetype":{
                required: true,
            },
            "startDateEditWeekly":{
                required: true,
            },
            "endDateEditWeekly":{
                required: true,
            }
        },
        messages: {   
            "startDateEditWeekly": {
                required:"<br/>Select the Start Date."
            },
            "endDateEditWeekly":{
                required:"<br/>Select the End Date."
            }
        },
        submitHandler: function (form) {
            var userNetlogin = $("#userNetlogin").val();
            var fullname = $('#fullname').val();
            var leavegroup = $('#leavegruop').val();
            var leavetype =$('#leavetype').val();
            var startDate = $('#startDateEditWeekly').val();
            var endDate = $('#endDateEditWeekly').val();
            var pdlStartTime = ($('#starttimemasterduty').val() != '') ? convertTimeIntoSeconds($('#starttimemasterduty').val()) : 0;
            var pdlEndTime = ($('#endtimemasterduty').val() != '') ? convertTimeIntoSeconds($('#endtimemasterduty').val()) : 0;
            var pdlEndTimeDbSave = pdlEndTime;
            var dataSchedulingPerson = $('#dataSchedulingPerson').val();
            var dataRowId = $('#dataRowId').val();
            var dataDutyName = $('#dataDutyName').val();
            var dataRowStart = $('#dataRowStart').val();
            var dataRowEnd = $('#dataRowEnd').val();
            var dataUniqueId = $('#dataUniqueId').val();
            var dataDDate = $('#dataDDate').val();

            if(pdlStartTime > pdlEndTime){
                pdlEndTime = parseInt(86400 + pdlEndTime);
            }
            $('#sbt').attr('disabled',true);
            if($('#pdlEnable').prop('checked')){
                if(pdlStartTime.toString() == '' && pdlEndTime.toString() != ''){
                    let errorPDLStart = '<label id="pdlStartTime-error" class="error"><br>Select Part Day Leave Start Time.</label>';
                    $('#newLeaveFormerrorBox').append(errorPDLStart);
                    $('#newLeaveFormerrorBox').show();
                } else if(pdlStartTime.toString() != '' && pdlEndTime.toString() == ''){
                    let errorPDLEnd = '<label id="pdlEndTime-error" class="error"><br>Select Part Day Leave End Time.</label>';
                    $('#newLeaveFormerrorBox').append(errorPDLEnd);
                    $('#newLeaveFormerrorBox').show();
                } else if(pdlStartTime.toString() == '' && pdlEndTime.toString() == ''){
                    let errorPDLStart = '<label id="pdlStartTime-error" class="error"><br>Select Part Day Leave Start Time.</label>';
                    $('#newLeaveFormerrorBox').append(errorPDLStart);
                    let errorPDLEnd = '<label id="pdlEndTime-error" class="error"><br>Select Part Day Leave End Time.</label>';
                    $('#newLeaveFormerrorBox').append(errorPDLEnd);
                    $('#newLeaveFormerrorBox').show();
                } else {
                    $.ajax({
                        url:"page-includes/leave/save-new-leave.php",
                        type:'POST',
                        data:{
                            "task":"addLeave",
                            "userNetlogin":userNetlogin,
                            "fullname": fullname,
                            "leavegruop": leavegroup,
                            "leavetype": leavetype,
                            "startDate":startDate,
                            "endDate":endDate,
                            "warning":'ok',
                            "pdlStartTime":pdlStartTime,
                            "pdlEndTime":pdlEndTimeDbSave
                        },
                        success:function(data,status) {
                            var pdata=jQuery.parseJSON(data);
                            if (pdata.status==0) {
                                $('#dataerr').html(pdata.msg);
                                $('#sbt').removeClass("activebtn");
                                $('#Finish').addClass("activebtn");
                                return false;
                            } else {
                                if (pdata.status==1) {
                                    $('#dataerr').html();
                                    $('#sbt').removeClass('activebtn');
                                    $('#sbt').attr('disabled',true);
                                    $('#Finish').removeClass('activebtn');
                                    $('#Finish').attr('disabled',true);
                                    $('#Processing').show();
                                    openManagePopupEditWeekly(pdata.LeaveId,pdata.weekno,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDDate);
                                    // refreshAllocatedSectionTr(dataSchedulingPerson,dataRowId,dataDDate,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,'','CONTEXTMENUEDITDUTY',0,0,0,'Yes');
                                    reloadeditweeklygrid('','','','','ALL');
                                }
                                if (pdata.status==2) {
                                    if (confirm(pdata.msg)) {
                                        $.ajax({
                                            url:"page-includes/leave/save-new-leave.php",
                                            type:'POST',
                                            data:{
                                                "task":"addLeave",
                                                "userNetlogin":userNetlogin,
                                                "fullname": fullname,
                                                "leavegruop": leavegroup,
                                                "leavetype": leavetype,
                                                "startDate":startDate,
                                                "endDate":endDate,
                                                "warning":'ok',
                                                "pdlStartTime":pdlStartTime,
                                                "pdlEndTime":pdlEndTimeDbSave
                                            },
                                            success:function(data,stats) {
                                                var fdata=jQuery.parseJSON(data);
                                                if (fdata.status == 0) {
                                                    $('#dataerr').html(fdata.msg);
                                                    $('#sbt').removeClass('activebtn');
                                                    $('#Finish').addClass('activebtn');
                                                    return false;
                                                } else {
                                                    if (fdata.status == 1) {
                                                        $('#dataerr').html();
                                                        $('#sbt').removeClass('activebtn');
                                                        $('#Finish').removeClass('activebtn');
                                                        $('#sbt').attr('disabled',true);
                                                        $('#Finish').attr('disabled',true);
                                                        $('#Processing').show();
                                                        openManagePopupEditWeekly(fdata.LeaveId,fdata.weekno,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDDate);
                                                        // refreshAllocatedSectionTr(dataSchedulingPerson,dataRowId,dataDDate,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,'','CONTEXTMENUEDITDUTY',0,0,0,'Yes');
                                                        reloadeditweeklygrid('','','','','ALL');
                                                    }
                                                }
                                            }
                                        });
                                    }
                                }
                            }
                        }
                    });
                }
            } else {
                $.ajax({
                    url:"page-includes/leave/save-new-leave.php",
                    type:'POST',
                    data:{
                        "task":"addLeave",
                        "userNetlogin":userNetlogin,
                        "fullname": fullname,
                        "leavegruop": leavegroup,
                        "leavetype": leavetype,
                        "startDate":startDate,
                        "endDate":endDate,
                        "warning":'ok',
                        "pdlStartTime":pdlStartTime,
                        "pdlEndTime":pdlEndTimeDbSave
                    },
                    success:function(data,status) {
                        var pdata=jQuery.parseJSON(data);
                        if (pdata.status==0) {
                            $('#dataerr').html(pdata.msg);
                            $('#sbt').removeClass("activebtn");
                            $('#Finish').addClass("activebtn");
                            return false;
                        } else {
                            if (pdata.status==1) {
                                $('#dataerr').html();
                                $('#sbt').removeClass('activebtn');
                                $('#sbt').attr('disabled',true);
                                $('#Finish').removeClass('activebtn');
                                $('#Finish').attr('disabled',true);
                                $('#Processing').show();
                                openManagePopupEditWeekly(pdata.LeaveId,pdata.weekno,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDDate);
                                // refreshAllocatedSectionTr(dataSchedulingPerson,dataRowId,dataDDate,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,'','CONTEXTMENUEDITDUTY',0,0,0,'Yes');
                                reloadeditweeklygrid('','','','','ALL');
                            }
                            if (pdata.status==2) {
                                if (confirm(pdata.msg)) {
                                    $.ajax({
                                        url:"page-includes/leave/save-new-leave.php",
                                        type:'POST',
                                        data:{
                                            "task":"addLeave",
                                            "userNetlogin":userNetlogin,
                                            "fullname": fullname,
                                            "leavegruop": leavegroup,
                                            "leavetype": leavetype,
                                            "startDate":startDate,
                                            "endDate":endDate,
                                            "warning":'ok',
                                            "pdlStartTime":pdlStartTime,
                                            "pdlEndTime":pdlEndTimeDbSave
                                        },
                                        success:function(data,stats) {
                                            var fdata=jQuery.parseJSON(data);
                                            if (fdata.status == 0) {
                                                $('#dataerr').html(fdata.msg);
                                                $('#sbt').removeClass('activebtn');
                                                $('#Finish').addClass('activebtn');
                                                return false;
                                            } else {
                                                if (fdata.status == 1) {
                                                    $('#dataerr').html();
                                                    $('#sbt').removeClass('activebtn');
                                                    $('#Finish').removeClass('activebtn');
                                                    $('#sbt').attr('disabled',true);
                                                    $('#Finish').attr('disabled',true);
                                                    $('#Processing').show();
                                                    openManagePopupEditWeekly(fdata.LeaveId,fdata.weekno,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDDate);
                                                    // refreshAllocatedSectionTr(dataSchedulingPerson,dataRowId,dataDDate,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,'','CONTEXTMENUEDITDUTY',0,0,0,'Yes');
                                                    reloadeditweeklygrid('','','','','ALL');
                                                }
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    }
                });
            }
        }
    });

 });

 function ShowYealyLeaves() {
    $.post("page-includes/leave/leave-yearly-holder.php", {
    },  
    function(data,status){
      GetTabContent(8);
    })
  }

function openManagePopupEditWeekly(leaveid,week,dataSchedulingPerson,dataRowId,dataDutyName,dataRowStart,dataRowEnd,dataUniqueId,dataDDate)
{ 
  if (leaveid!=undefined && week!=undefined && leaveid!='' && week!='') {
     $.post("page-includes/leave/edit-weekly-leave-approve-popup.php", {
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
        function(data,status){
	       $.facebox(data);
        }) 
    }
}

$( document ).ready(function() {
    $('#starttimemasterduty').val('');
    $('#endtimemasterduty').val('');
    $('#pdlDurationDiv').val('');
    $('#dutyDetailsNameDiv').val('');
    $('#dutyDetailsTimeDiv').val('');
    $('#pdlDurationInp').val('');
    $('#pdlStartTimeDiv').hide();
    $('#pdlEndTimeDiv').hide();
    $('#pdlDurationDiv').hide();
    $('#dutyDetailsNameDiv').hide();
    $('#dutyDetailsTimeDiv').hide();
    $(function() {
        $('#starttimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });

        $('#endtimemasterduty').timepicker({
            'step': 15,
            'timeFormat': 'H:i'
        });
    });
});