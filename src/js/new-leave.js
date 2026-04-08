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
            "startDate":{
                required: true,
            },
            "endDate":{
                required: true,
            }
        },
        messages: {   
            "startDate": {
                required:"<br/>Select the Start Date."
            },
            "endDate":{
                required:"<br/>Select the End Date."
            }
        },
        submitHandler: function (form) {
            var userNetlogin = $("#userNetlogin").val();
            var fullname = $('#fullname').val();
            var leavegroup = $('#leavegruop').val();
            var leavetype =$('#leavetype').val();
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();
            var pdlStartTime = ($('#starttimemasterduty').val() != '') ? convertTimeIntoSeconds($('#starttimemasterduty').val()) : 0;
            var pdlEndTime = ($('#endtimemasterduty').val() != '') ? convertTimeIntoSeconds($('#endtimemasterduty').val()) : 0;
            var pdlEndTimeDbSave = pdlEndTime;

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
                                    openManagePopup(pdata.LeaveId,pdata.weekno);
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
                                                        openManagePopup(fdata.LeaveId,fdata.weekno);
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
                                openManagePopup(pdata.LeaveId,pdata.weekno);
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
                                                    openManagePopup(fdata.LeaveId,fdata.weekno);
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

function openManagePopup(leaveid,week)
{ 
  if (leaveid!=undefined && week!=undefined && leaveid!='' && week!='') {
     $.post("page-includes/leave/leave-approve-popup.php", {
            id: leaveid,
            week: week,
            callpage:'yearly',
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