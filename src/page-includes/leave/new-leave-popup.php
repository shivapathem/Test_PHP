<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../../function-includes/init.php';
include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/userfunctions.php';
include_once '../../function-includes/leavefunctions.php';
include_once '../../function-includes/allocationsfunctions.php';
include_once __DIR__ . '/../../function-includes/common/classCommonDBFunctions.php';
$commonDbobj = new classCommonDBFunctions;
$selectedUser = '';
$arrUser = '';
$adminUser = '';
$arrUserSettings = [];
$arrGroupsCanRequest = [];
$adminUser = GetUserLogon();
$selectedUser = $_REQUEST['userNetlogin'];

$LeaveYear = date("Y", strtotime("-3 months"));

$result['DataInput'] = readYearlyFilter();
if (isset($result['DataInput'])) {
    $formdataArr = explode("&", $result['DataInput']);
    if (sizeof($formdataArr)) {
        foreach ($formdataArr as $row) {
            list($keys[], $values[]) = explode("=", $row);
        }
    }
}
if (!empty($keys) && in_array('selectedyear', $keys)) {
    $keyno = array_search('selectedyear', $keys);
    if ($values[$keyno] != '') {
        $LeaveYear = $values[$keyno];
        $selectedYear = $values[$keyno];
    } else {
        $LeaveYear = date("Y", strtotime("-3 months"));
    }
} else {
    $LeaveYear = date("Y", strtotime("-3 months"));
}

$schedulingPersonId = getScheduledPersonIDByNetLoginID($selectedUser);
$schedledPersonInfor = GetScheduledPersonDetailsById($schedulingPersonId);
$arrUserSettings = json_decode($commonDbobj->userLeaveRequestByNetLoginandRequesterID($adminUser, $selectedUser), true);
if (!empty($arrUserSettings) && isset($arrUserSettings['LeaveRequests']) && is_array($arrUserSettings['LeaveRequests'])) {    
    foreach ($arrUserSettings['LeaveRequests'] as $intGroupID => $arrGroup) {
        $arrGroupsCanRequest[$intGroupID] = $arrGroup;
    }
}
?>
<div class="popupHeading">Add New</div>
<div class="popupContent">
<form name="newleaveFrom" id="newleaveFrom" method="post">
<input type="hidden" id="dutyStartTime"/>
<input type="hidden" id="dutyEndTime"/>
<input type="hidden" id="dutyJobs"/>
<input type="hidden" id="validationPassVal"/>
<div class="fields">
		<input type="hidden" name="userNetlogin" id="userNetlogin" value="<?php echo $selectedUser; ?>" readonly>
</div>
<div class="fields">
		<label for="fullname">Full Name</label>
		<input type="text" name="fullname" id="fullname" value="<?php echo $schedledPersonInfor['FullName'] ?? 'No Name' ?>" readonly>
</div>
<div class="fields">
		<label for="leavegruop">Leave Group</label>
		<?php
echo '<select size="1" name="leavegruop" id="leavegruop" onchange="javascript:ChangeLeaveGroup(this.value)";>';
if (!empty($arrGroupsCanRequest)) {
    foreach ($arrGroupsCanRequest as $intGroupID => $arrGroup) {
        echo '<option value="' . $intGroupID . '">' . $arrGroup['Description'] . '</option>';
    }
} else {
    echo '<option value="0">No Group Found</option>';
}
echo '</select>';
?>
</div>
<div class="fields">
		<label for="leavetype">Leave Type</label>
		<span id="typescombo">Select Group First</span>
</div>
<div class="fields">
		<label for="startDate">Start Date</label>
		<input type="text" placeholder="dd-mm-yyyy" autocomplete="off"  name="startDate" id="startDate" class="leaveDateSelect">
</div>
<div class="fields">
		<label for="endDate">End Date</label>
		<input type="text" placeholder="dd-mm-yyyy" autocomplete="off"  name="endDate" id="endDate" class="leaveDateSelect">
</div>
<div class="fields" id="pdlAllowDiv">
  <div style="float:left; width: 14%;">
    <label style="margin-top: 5px;">Part Day Leave</label>
  </div>
  <div style="float:left; width: 86%;">
    <input type="checkbox" name="pdlEnable" id="pdlEnable" onclick="checkPDL();">
  </div>
</div>
<div class="fields" id="dutyDetailsNameDiv" style="display: none;"></div>
<div class="fields" id="dutyDetailsTimeDiv" style="display: none;"></div>
<div class="fields" id="pdlStartTimeDiv" style="display: none;">
        <label>PDL Start Time</label>
        <input type="text" autocomplete="off" name="pdlStartTime" id="starttimemasterduty" style="width:30%;" onchange="checkPDLValidations();">
</div>
<div class="fields" id="pdlEndTimeDiv" style="display: none;">
        <label>PDL End Time</label>
        <input type="text" autocomplete="off" name="pdlEndTime" id="endtimemasterduty" style="width:30%;" onchange="checkPDLValidations();">
</div>
<div class="fields" id="pdlDurationDiv" style="display: none;">
        <label>PDL Duration</label>
        <input type="text" autocomplete="off" id="pdlDurationInp" style="width:30%;" disabled="disabled">
</div>
<div class= "popupButton">
<input type="submit" name="sbt" id="sbt" class="add-btn popupBlock-btn activebtn" value="Save"/>
<input type="text" name="Processing" id="Processing" class="popupBlock-btn"  value="Processing..." readonly/>
<input type="button" name="Finish" id="Finish" class="popupBlock-btn"  value="Cancel" onclick="cancel();"/>
</div>
</form>
<div id="newLeaveFormerrorBox"></div>
<div id="newLeaveFormPDLerrorBox" style="color: #990000; font-weight: bold; line-height: 150%;font-size:10px;"></div>
<div id="dataerr"></div>
</div>
<script src="../../../js/new-leave.js?v=<?php echo time(); ?>"></script>
<script type="text/javascript">
    groupid= $('#leavegruop').val();
    ChangeLeaveGroup(groupid);
    function ChangeLeaveGroup(groupid, firstOpt='') {
        $.ajax({
            url:"page-includes/leave/leave-type-select.php",
            type:'POST',
            data:"leavegroup="+groupid+"&userNetlogin="+"<?php echo $_REQUEST['userNetlogin'] ?? ''; ?>",
            dataType:"text",
            success:function(response,status,http) {
                $('#typescombo').html(response);
            },
            error: function(http,status,error){
                customAlert("Error Found :" + error);
            }
        });
    }

    $("#startDate").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-M-yy",
        firstDay: '6',
        minDate:"01-Apr-<?php echo $LeaveYear; ?>",
        maxDate:"31-Mar-<?php echo $LeaveYear + 1; ?>",
        yearRange: "<?php echo $LeaveYear; ?>:<?php echo $LeaveYear + 1; ?>",
        inline: true
    }).change(function (selected) {
        $('#sbt').attr('disabled',false);
        $('#sbt').addClass('activebtn');
        $('#Finish').removeClass('activebtn');
        var sdate = $('#startDate').val();
        $("#endDate").val(sdate);
        if($('#pdlEnable').prop('checked')){
            $("#endDate").datepicker({
                dateFormat: "dd-M-yy",
                firstDay: '6',
                disabled: true
            });

            if(sdate != ''){
                $('#newLeaveFormPDLerrorBox').hide();
                $('#newLeaveFormPDLerrorBox').html('');
                $.ajax({
                    url:"page-includes/leave/get-allocation-for-leave.php",
                    type:'POST',
                    data:{
                        'userNetLogin': $('#userNetlogin').val(),
                        'pdlStartDate': $('#startDate').val(),
                        'pdlEndDate': $("#endDate").val()
                    },
                    success:function(response,status,http) {
                        let resp = $.parseJSON(response);
                        if((resp.status == true) && (resp.message == '')){
                            $('#dutyStartTime').val(resp.allocDetails.StartTime);
                            $('#dutyEndTime').val(resp.allocDetails.EndTime);
                            $('#dutyJobs').val(resp.allocDutyJobs);
                            let dutyDetailNameCont = '';
                            dutyDetailNameCont +='<label style="margin-right:5px;">Duty Name</label>';
                            dutyDetailNameCont +=resp.allocDetails.DutyName;
                            $('#dutyDetailsNameDiv').html(dutyDetailNameCont);

                            let dutyDetailTimeCont = '';
                            dutyDetailTimeCont +='<label style="margin-right:5px;">Duty Time</label>';
                            dutyDetailTimeCont +=convertSecondsIntoTime(resp.allocDetails.StartTime,':','No')+' - '+convertSecondsIntoTime(resp.allocDetails.EndTime,':','No')+' | '+convertSecondsIntoTime(resp.allocDetails.dutyDuration,'.');
                            $('#dutyDetailsTimeDiv').html(dutyDetailTimeCont);
                            $('#dutyDetailsNameDiv').show();
                            $('#dutyDetailsTimeDiv').show();
                            $('#pdlStartTimeDiv').show();
                            $('#pdlEndTimeDiv').show();
                            $('#pdlDurationDiv').show();
                            if(resp.enableSbmtButton == 1){
                                $('#sbt').attr('disabled',false);
                                $('#sbt').addClass('activebtn');
                            } else {
                                $('#sbt').attr('disabled',true);
                                $('#sbt').removeClass('activebtn');
                            }
                        } else {
                            $('#dutyDetailsNameDiv').hide();
                            $('#dutyDetailsTimeDiv').hide();
                            showPDLErrorDiv(resp.message);
                            $('#pdlStartTimeDiv').hide();
                            $('#pdlEndTimeDiv').hide();
                            $('#pdlDurationDiv').hide();
                            if(resp.enableSbmtButton == 1){
                                $('#sbt').attr('disabled',false);
                                $('#sbt').addClass('activebtn');
                            } else {
                                $('#sbt').attr('disabled',true);
                                $('#sbt').removeClass('activebtn');
                            }
                        }
                    },
                    error: function(http,status,error){
                        $('#dutyDetailsNameDiv').hide();
                        $('#dutyDetailsTimeDiv').hide();
                        showPDLErrorDiv('Some error occurred');
                    }
                });
            }
        } else {
            $("#endDate").datepicker("destroy");
            $("#endDate").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: "dd-M-yy",
                firstDay: '6',
                minDate:sdate,
                maxDate:"31-Mar-<?php echo $LeaveYear + 1; ?>",
                yearRange: "<?php echo $LeaveYear; ?>:<?php echo $LeaveYear + 1; ?>",
                disabled: false,
            });
        }
    });

    function checkPDLValidations(){
        $('#newLeaveFormPDLerrorBox').hide();
        let validationPass = true;
        let pdlStartTime = $('#starttimemasterduty').val();
        pdlStartTime = pdlStartTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
        let pdlEndTime = $('#endtimemasterduty').val();
        pdlEndTime = pdlEndTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
        if(pdlStartTime != '' && pdlEndTime != ''){
            if(pdlStartTime == 0){
                pdlStartTime = '00:00';
            }
            if(pdlEndTime == 0){
                pdlEndTime = '00:00';
            }

            let pdlStartTimeSec = 0;
            let pdlEndTimeSec = 0;
            if(pdlStartTime != 0){
                pdlStartTimeSec = convertTimeIntoSeconds(pdlStartTime);
            }
            if(pdlEndTime != 0){
                pdlEndTimeSec = convertTimeIntoSeconds(pdlEndTime);
            }

            if(pdlStartTimeSec > pdlEndTimeSec){
                pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
            }

            let dutyStartTime = parseInt($('#dutyStartTime').val());
            let dutyEndTime = parseInt($('#dutyEndTime').val());

            if(dutyStartTime > dutyEndTime){
                dutyEndTime = parseInt(86400 + dutyEndTime);
            }

            if(pdlStartTime != '' && pdlEndTime != ''){
                if(dutyEndTime > 86400){
                    if((pdlStartTimeSec < pdlEndTimeSec) && (pdlStartTimeSec < dutyStartTime)){
                        pdlStartTimeSec = parseInt(86400 + pdlStartTimeSec);
                        pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
                    }
                }
                if(pdlStartTimeSec == dutyStartTime && pdlEndTimeSec < dutyEndTime){
                    let dutyEndTime15MinBefore = parseInt(dutyEndTime - 4500);
                    if(pdlEndTimeSec > dutyEndTime15MinBefore){
                        showPDLErrorDiv('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                        validationPass = false;
                    }
                } else if(pdlStartTimeSec > dutyStartTime && pdlEndTimeSec == dutyEndTime){
                    let dutyStartTime15MinAfter = parseInt(dutyStartTime + 4500);
                    if(pdlStartTimeSec < dutyStartTime15MinAfter){
                        showPDLErrorDiv('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                        validationPass = false;
                    }
                } else if(pdlStartTimeSec == dutyStartTime && pdlEndTimeSec == dutyEndTime){
                    showPDLErrorDiv('Duty start and end time cannot be the same as Part Day Leave start and end time. Please change the duty start or end time.');
                    validationPass = false;
                } else if(pdlStartTimeSec < dutyStartTime && pdlEndTimeSec > dutyEndTime){
                    showPDLErrorDiv('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                    validationPass = false;
                } else if(pdlStartTimeSec < dutyStartTime && pdlEndTimeSec <= dutyEndTime){
                    showPDLErrorDiv('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                    validationPass = false;
                } else if(pdlStartTimeSec >= dutyStartTime && pdlEndTimeSec > dutyEndTime){
                    showPDLErrorDiv('The duty start and end times do not cover the start and end times of this leave request. Please change either the duty timings or the leave request timings.');
                    validationPass = false;
                } else if(pdlStartTimeSec > dutyStartTime && pdlEndTimeSec < dutyEndTime){
                    let pdlDuration = parseInt(parseInt(pdlEndTimeSec) - parseInt(pdlStartTimeSec));
                    let dutyDuration = parseInt(parseInt(dutyEndTime) - parseInt(dutyStartTime));
                    if(pdlDuration == 0){
                        showPDLErrorDiv('Part Day Leave start and end time can not be same');
                        validationPass = false;
                    }
                    if(pdlDuration > dutyDuration){
                        showPDLErrorDiv('Part Day Leave timings does not lies between duty timings.');
                        validationPass = false;
                    }
                }
                if(validationPass == false){
                    $('#validationPassVal').val(0);
                    $('#sbt').attr('disabled',true);
                    $('#sbt').removeClass('activebtn');
                } else {
                    $('#validationPassVal').val(1);
                    $('#sbt').attr('disabled',false);
                    $('#sbt').addClass('activebtn');
                }
                calculatePDLDuration('.');
            }
        } else {
            $('#sbt').attr('disabled',true);
            $('#sbt').removeClass('activebtn');
        }
    }

    function convertTimeIntoSeconds(timeDuration){
        let [hours, minutes, seconds] = timeDuration.split(':');
        if(minutes == undefined){
            minutes = 0;
        }
        return Number(hours) * 60 * 60 + Number(minutes) * 60;
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

    function calculatePDLDuration(seperator=':'){
        let pdlStartTime = $('#starttimemasterduty').val();
        pdlStartTime = pdlStartTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
        let pdlEndTime = $('#endtimemasterduty').val();
        pdlEndTime = pdlEndTime.replace(/[\`*|&;\$%@"'#?=<>\(\)\+,a-zA-Z]/g, "");
        if(pdlStartTime == 0){
            pdlStartTime = '00:00';
        }
        if(pdlEndTime == 0){
            pdlEndTime = '00:00';
        }

        if(pdlStartTime != '' && pdlEndTime != ''){
            let pdlStartTimeSec = 0;
            let pdlEndTimeSec = 0;
            let pdlDurationSec = 0;

            if(pdlStartTime != 0){
                pdlStartTimeSec = convertTimeIntoSeconds(pdlStartTime);
            }
            if(pdlEndTime != 0){
                pdlEndTimeSec = convertTimeIntoSeconds(pdlEndTime);
            }

            if(pdlStartTimeSec > pdlEndTimeSec){
                pdlEndTimeSec = parseInt(86400 + pdlEndTimeSec);
            }
            pdlDurationSec = parseInt(pdlEndTimeSec - pdlStartTimeSec);
            $('#pdlDurationInp').val(convertSecondsIntoTime(pdlDurationSec,'.'));

            let dutyJobsData = $('#dutyJobs').val();
            if(dutyJobsData != ''){
                dutyJobsData = $.parseJSON(dutyJobsData);

                for (const [key, value] of Object.entries(dutyJobsData)) {
                    let validPDLStartTime = true;
                    let validPDLEndTime = true;
                    let jobStartTimeInSec = convertTimeIntoSeconds(value.startTime);
                    let jobEndTimeInSec = convertTimeIntoSeconds(value.endTime);

                    if(jobStartTimeInSec > jobEndTimeInSec){
                        jobEndTimeInSec = parseInt(86400 + jobEndTimeInSec);
                    }

                    if((jobStartTimeInSec < pdlStartTimeSec) && (jobEndTimeInSec > pdlStartTimeSec)){
                        validPDLStartTime = false;
                    }
                    if((jobStartTimeInSec < pdlEndTimeSec) && (jobEndTimeInSec > pdlEndTimeSec)){
                        validPDLEndTime = false;
                    }

                    if((validPDLStartTime == false) || (validPDLEndTime == false)){
                        showPDLErrorDiv('There are jobs coinciding with this leave request. If you continue to authorise this request, a job called  "Leave" will appear on the scheduled person\'s duty, and the jobs that coincide will appear in the Unallocated Jobs section of the Daily View.');
                        $('#validationPassVal').val(1);
                        break;
                    } else {
                        $('#validationPassVal').val(1);
                    }
                }
            }
        }
    }

    function showPDLErrorDiv(msg=''){
        let errorPDL = '<label id="pdl-error" class="error"><br>'+msg+'</label>';
        $('#newLeaveFormPDLerrorBox').html(errorPDL);
        $('#newLeaveFormPDLerrorBox').show();
        return false;
    }

    function checkPDL(){
        if($('#pdlEnable').prop('checked')){
            $('#sbt').attr('disabled',true);
            $('#sbt').removeClass('activebtn');
            $('#starttimemasterduty').val('');
            $('#endtimemasterduty').val('');
            $('#pdlDurationDiv').val('');
            $('#dutyDetailsNameDiv').val('');
            $('#dutyDetailsTimeDiv').val('');
            $('#pdlDurationInp').val('');
            $('#dutyDetailsNameDiv').hide();
            $('#dutyDetailsTimeDiv').hide();
            $('#newLeaveFormPDLerrorBox').hide();
            $('#newLeaveFormPDLerrorBox').html('');
            $("#endDate").attr("disabled", true);

            if($('#startDate').val() != '' && $('#endDate').val() != ''){
                $('#newLeaveFormPDLerrorBox').hide();
                $('#newLeaveFormPDLerrorBox').html('');
                $.ajax({
                    url:"page-includes/leave/get-allocation-for-leave.php",
                    type:'POST',
                    data:{
                        'userNetLogin': $('#userNetlogin').val(),
                        'pdlStartDate': $('#startDate').val(),
                        'pdlEndDate': $('#endDate').val()
                    },
                    success:function(response,status,http) {
                        let resp = $.parseJSON(response);
                        if(resp.status == true && resp.message == ''){
                            $('#dutyStartTime').val(resp.allocDetails.StartTime);
                            $('#dutyEndTime').val(resp.allocDetails.EndTime);
                            let dutyDetailNameCont = '';
                            dutyDetailNameCont +='<label style="margin-right:5px;">Duty Name</label>';
                            dutyDetailNameCont +=resp.allocDetails.DutyName;
                            $('#dutyDetailsNameDiv').html(dutyDetailNameCont);

                            let dutyDetailTimeCont = '';
                            dutyDetailTimeCont +='<label style="margin-right:5px;">Duty Time</label>';
                            dutyDetailTimeCont +=convertSecondsIntoTime(resp.allocDetails.StartTime,':','No')+' - '+convertSecondsIntoTime(resp.allocDetails.EndTime,':','No')+' | '+convertSecondsIntoTime(resp.allocDetails.dutyDuration,'.');
                            $('#dutyDetailsTimeDiv').html(dutyDetailTimeCont);
                            $('#dutyDetailsNameDiv').show();
                            $('#dutyDetailsTimeDiv').show();
                            $('#pdlStartTimeDiv').show();
                            $('#pdlEndTimeDiv').show();
                            $('#pdlDurationDiv').show();
                        } else {
                            $('#dutyDetailsNameDiv').hide();
                            $('#dutyDetailsTimeDiv').hide();
                            showPDLErrorDiv(resp.message);
                            $('#pdlStartTimeDiv').hide();
                            $('#pdlEndTimeDiv').hide();
                            $('#pdlDurationDiv').hide();
                            if(resp.enableSbmtButton == 1){
                                $('#sbt').attr('disabled',false);
                                $('#sbt').addClass('activebtn');
                            }
                        }
                    },
                    error: function(http,status,error){
                        $('#dutyDetailsNameDiv').hide();
                        $('#dutyDetailsTimeDiv').hide();
                        showPDLErrorDiv('Some error occurred');
                    }
                });
            }
        } else {
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
            $("#endDate").removeAttr("disabled");
            $('#sbt').attr('disabled',false);
            $('#sbt').addClass('activebtn');
            $('#newLeaveFormPDLerrorBox').hide();
            $('#newLeaveFormPDLerrorBox').html('');
            if($('#startDate').val() != ''){
                $("#endDate").datepicker("destroy");
                $("#endDate").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: "dd-M-yy",
                    firstDay: '6',
                    minDate:$('#startDate').val(),
                    maxDate:"31-Mar-<?php echo $LeaveYear + 1; ?>",
                    yearRange: "<?php echo $LeaveYear; ?>:<?php echo $LeaveYear + 1; ?>",
                    disabled: false,
                });
            }
        }
    }
</script>