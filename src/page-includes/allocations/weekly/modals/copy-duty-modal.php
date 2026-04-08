<style type="text/css">
.ui-timepicker-wrapper{ z-index: 999999 !important;}
</style>
<?php

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../../../../function-includes/masterduty_functions.php';
require_once __DIR__ . '/../../../../function-includes/DB_Functions.php';
include_once __DIR__ ."/../../../../function-includes/bootstrap.php";
require_once __DIR__ . '/../service/AllocationService.php';
require_once __DIR__ . '/../service/AllocationRepository.php';
include_once __DIR__. '/../../../../function-includes/user-scheduling-team-list.php';

$request = Request::createFromGlobals();
$service = new AllocationService();
$repository = new AllocationRepository();

$copyDutyId = $request->get('copyDutyId');
$schedulingTeamId = $request->get('schedulingTeamId');
$schedulePersonId = $request->get('schedulePersonId');

$getDefaultSchedulePerson = $repository->getDefaultSchedulePerson($schedulePersonId);
?>
<style type="text/css">
.ui-dialog .ui-dialog-title{
    white-space: normal;
}
</style>
<div>
    <div style="width: 99%; text-align: left; font-size: 11px; color: #222; padding:0px 0px 5px 0px;" id="noteDiv">Please be very careful not to create unnecessary duplicates.</div>
</div>
<div>
    <div style="width: 99%; text-align: left; font-size: 11px; color: green; display: none;" id="successDiv"></div>
    <div style="width: 99%; text-align: left; font-size: 11px; color: #FF0000; display: none;" id="errorDiv"></div>
</div>
<form id="editDutyForm" onsubmit="return false;">
    <table id="dutynewedittabledetail" class="redtable smalltable bluetable" width="100%">
        <tbody>
            <tr>
                <td width="30%" style="vertical-align: middle;">Select Team<span class="required">*</span></td>
                <td>
                    <select name="teamId" id="copyDutyTeamId" class="chosen-selectWeekly" style="width:100%;" onchange="getScheduledPerson(this.value);">
                        <option value=''>Select The Team</option>
                        <?php 
                            $teamOptions = getSchedulingTeamList($intTeamID, 'allocation-policy', 'viewEditWeekly');
                            echo $teamOptions;
                         ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="30%" style="vertical-align: middle;">Start date of range<span class="required">*</span></td>
                <td>
                    <input type="text" placeholder="dd-mm-yyyy" autocomplete="off" name="startDate" id="startDateCopyDuty" class="leaveDateSelect" value="<?php echo (strtotime($request->get('dutyDate')) < strtotime('2024-06-01') ? date('d-M-Y',strtotime('2024-06-01')) : date('d-M-Y',strtotime($request->get('dutyDate'))));?>">
                </td>
            </tr>
            <tr>
                <td width="30%" style="vertical-align: middle;">End date of range<span class="required">*</span></td>
                <td>
                    <input type="text" placeholder="dd-mm-yyyy" autocomplete="off" name="endDate" id="endDateCopyDuty" class="leaveDateSelect" onclick="initEndDateDatepicker();">
                </td>
            </tr>
            <tr>
                <td width="30%" style="vertical-align: middle;">Scheduled Person</td>
                <td>
                    <select name="schedulingPersonId" id="schedulingPersonId" class="chosen-selectWeekly" style="width:100%;">
                        <option value="<?php echo $getDefaultSchedulePerson['ScheduledPersonID']?>"><?php echo $getDefaultSchedulePerson['FullName']?></option>
                    </select>
                </td>
            </tr>
            <input type="hidden" name="copyDutyId" id="copyDutyId" value="<?php echo $request->get('copyDutyId'); ?>">
            <input type="hidden" name="maxEndDate" id="maxEndDate" value="<?php echo $request->get('maxEndDate'); ?>">
        </tbody>
    </table>
    <!-- Allow form submission with keyboard without duplicating the dialog button -->
    <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
</form>
<script type="text/javascript">
function initEndDateDatepicker(){
    $("#endDateCopyDuty").datepicker({
        dateFormat: "dd-M-yy",
        firstDay: '6',
        minDate:$("#startDateCopyDuty").val(),
        maxDate:$('#maxEndDate').val(),
        defaultDate:$("#startDateCopyDuty").val(), 
        onSelect: function (date, datepicker) { 
            if (date != "") { 
                getScheduledPerson($('#copyDutyTeamId').val());
            } 
        }
    });
}

$("#endDateCopyDuty").datepicker({
    dateFormat: "dd-M-yy",
    firstDay: '6',
    minDate:$("#startDateCopyDuty").val(),
    maxDate:$('#maxEndDate').val(),
    defaultDate:$("#startDateCopyDuty").val(), 
    onSelect: function (date, datepicker) { 
        if (date != "") { 
            getScheduledPerson($('#copyDutyTeamId').val());
        } 
    }
});

$("#startDateCopyDuty").datepicker({
    dateFormat: "dd-M-yy",
    firstDay: '6',
    inline: true,
    minDate:'01-Jun-2024',
}).change(function (selected) {
    $('#endDateCopyDuty').datepicker( "destroy" );
    $("#endDateCopyDuty").val('');
    $("#endDateCopyDuty").datepicker({
        dateFormat: "dd-M-yy",
        firstDay: '6',
        minDate:$("#startDateCopyDuty").val(),
        maxDate:$('#maxEndDate').val(),
        defaultDate:$("#startDateCopyDuty").val(), 
        onSelect: function (date, datepicker) { 
            if (date != "") { 
                getScheduledPerson($('#copyDutyTeamId').val());
            } 
        }
    });
});

function getScheduledPerson(teamId=0){
    $('#errorDiv').hide();
    $('#successDiv').hide();
    let fromDate = $('#startDateCopyDuty').val();
    let toDate = $('#endDateCopyDuty').val();
    let selectedScheduledPerson = <?php echo $getDefaultSchedulePerson['ScheduledPersonID']?>;
    if((teamId != 0) && (fromDate != undefined) && (fromDate != '') && (toDate != undefined) && (toDate != '')){
        $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/weekly/actions/copy-duty-actions.php',
            data: {
                'actionname': 'getschedulingpersons',
                'schedulingTeamId': teamId,
                'fromDate': fromDate,
                'toDate': toDate
            },
            success: function (response) { 
                response = $.parseJSON(response);
                let repDiv = '';
                repDiv +='<option value="0">Unallocated</option>';
                if(response.status == true){
                    $(response.schgeduledPeoplesList).each(function(index, value) {
                        if(value.SchedulingPersonID == selectedScheduledPerson){
                            repDiv +='<option value="'+value.SchedulingPersonID+'" selected="selected">'+value.FullName+'</option>';
                        } else {
                            repDiv +='<option value="'+value.SchedulingPersonID+'">'+value.FullName+'</option>';
                        }
                    });
                }
                $('#schedulingPersonId').html(repDiv).trigger("chosen:updated");
            }
        });
    }
}
</script>