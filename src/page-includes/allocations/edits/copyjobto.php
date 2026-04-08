<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
date_default_timezone_set('Europe/London');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';
include_once __DIR__ . '/../../../page-includes/allocations/weekly/service/AllocationService.php';
require_once __DIR__ . '/../weekly/service/AllocationRepository.php';
include_once __DIR__. '/../../../function-includes/user-scheduling-team-list.php';
$service = new AllocationService();
$repository = new AllocationRepository();
$pdo = OpenDBLinkA7();
$schedulePersonId = $_POST['scheduledPersonId'];
$getDefaultSchedulePerson = $repository->getDefaultSchedulePerson($schedulePersonId);
$allocationId = $_POST['allocationId']?? 0;
$isEdited = $_POST['isEdited']?? 0;
$parentId = $_POST['parentId']?? 0;
$isShiftleader = $_POST['isShiftleader']?? 0;
$intTeamID= $_POST['teamId'];
$week = $_POST['WeekNumber'];
$day = $_POST['iDay'];
$jobId = $_POST['jobId'];
$role = $_POST['role'];
$jobData = fetchEditedUnAllocationJob($jobId,$intTeamID);
$starttime = $jobData['StartTime'];
if($jobData['EndTime'] > 86400) {
    $jobData['EndTime'] = $jobData['EndTime']-86400;
}
$endtime = $jobData['EndTime'];
if(isset($_POST['date'])) {
    $curdate = $_POST['date'];
} elseif (isset($_SESSION['allocattionsdate'])) {
    $curdate = $_SESSION['allocattionsdate'];
} else {
    $curdate = date("Y-m-d");
}

  echo '<div id="copyjobpage">';
  echo '<form onsubmit="return false;" id="form" novalidate>';
  echo '<div class="hiddenErrorDiv"></div>';
  echo '<table class="redtable" width="600px">';

  echo '<tr>';
  echo '<th colspan="3"><br>Copy Job To<br><br></th>';
  echo '</tr>';
  echo '<tr style="display:none">';
  echo '<td class="lightblue">&nbsp</td>';
  echo '<td>';
  echo '<input type="text">';
  echo '</td>';
  echo '<td>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="lightblue"><label>Select Team <span id="" class="required">*</span></label></td>';
  echo '<td>';
  echo '<select name="selteamId" id="selteamId" class="chosen-select" onchange="getScheduledPersonJob()" required="required">';
  echo '<option value="" selected>Select a Team</option>';
  $teamOptions = getSchedulingTeamList($intTeamID, 'allocation-policy', 'viewEditWeekly');
  echo $teamOptions;
  echo '</select>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="lightblue"><label>Start of date range <span id="" class="required">*</span></label></td>';
  echo '<td>';
  echo '<input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="startDateCopyJobInput"
  name="startDateCopyJobInput" required="required" class="widthpopupcontrols" onblur="callDatePicker();" value="' . (date("d-M-Y", strtotime($_POST['date']))) . '">';
  echo '</td>';
  echo '<td">';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td class="lightblue"><label>End of date range <span id="" class="required">*</span></label></td>';
  echo '<td>';
  echo '<input type="text" placeholder="dd-mm-yyyy"  required="required" autocomplete="off" name="endDateCopyJobInput" id="endDateCopyJobInput" class="widthpopupcontrols" onclick="initEndDateDatepicker();">';
  echo '</td>';
  echo '<td">';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>Assign To</td>';
  echo '<td colspan="2">';
  echo '<select class="chosen-select" id="scheduledPerson" name="scheduledPerson" required>';
  if($schedulePersonId != 0) {
    echo '<option value="' . $getDefaultSchedulePerson['ScheduledPersonID'] . '">' . $getDefaultSchedulePerson['FullName'] . '</option>';
  } else {
    echo '<option value="">-- Select --</option>';
  }
  echo '</select>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<input type="hidden" id="JobId" name="JobId" value="'.$jobId.'">';
  echo '<input type="hidden" id="role" name="role" value="'.$role.'">';
  echo '<input type="hidden" name="jobstart" id="jobstart" value="'.$starttime.'">';
  echo '<input type="hidden" name="job_end" id="job_end" value="'.$endtime.'">';
  echo '<input type="hidden" id="process" name="process" value="CopyJobTo">';
  echo '<input type="hidden" id="submitflag" name="submitflag" value="0">';
  echo '<td>&nbsp;</td>';
  echo '<td colspan="2"><br><input type="submit" id="submit" value="Copy Job"  onclick="submitForm();" name="submit"><input type="button" value="Cancel" onclick="cancel()"></td>';
  echo '</tr>';
  echo '</table>';
  echo '</div>';
  echo '</form>';

?>
<script type="text/javascript">

$(document).ready(function() {
  $(".chosen-select").chosen({
    no_results_text: "Oops, nothing found!",
    width: "450px"
  });
  callDatePicker();
	initEndDateDatepicker();
});

function callDatePicker() {
    $('#startDateCopyJobInput').datepicker({
        changeMonth: true,
        autoOpen: false,
        firstDay:6,
        changeYear: true,
        showButtonPanel: false,//no need to display this btn, removed on 21-07-2021
        dateFormat: "dd-M-yy",
        onSelect: function (dateText, inst) {
          var currentdate = dateText;
            $('.hiddenErrorDiv').attr("style", "display:hidden");
            $("#submitflag").val(0);
            initEndDateDatepicker();
        }
    });
}

function initEndDateDatepicker(){
    $("#endDateCopyJobInput").datepicker("destroy");
    $("#endDateCopyJobInput").val($("#startDateCopyJobInput").val());
    $("#endDateCopyJobInput").datepicker({
        dateFormat: "dd-M-yy",
        firstDay: '6',
        minDate: $("#startDateCopyJobInput").val(),
        defaultDate: $("#startDateCopyJobInput").val(),
        onSelect: function (date, datepicker) {
            if (date != "") {
              getScheduledPersonJob();
            }
        }
    });
    getScheduledPersonJob();
}

function submitForm() {
  var selteamId=$('#selteamId').val();
  var startDateCopyJobInput = $("#startDateCopyJobInput").val();
  var endDateCopyJobInput = $("#endDateCopyJobInput").val();
  var scheduledPerson = $('#scheduledPerson').val();
  if (selteamId==''){
    var msg= "Please Select Team";
    $('.hiddenErrorDiv').attr("style", "display:block");
    $('.hiddenErrorDiv').html(msg);
    return false;
  }
  if (startDateCopyJobInput == ''){
    var msg= "Please Select Start Date";
    $('.hiddenErrorDiv').attr("style", "display:block");
    $('.hiddenErrorDiv').html(msg);
    return false;
  }
  if (endDateCopyJobInput == ''){
    var msg= "Please Select End Date";
    $('.hiddenErrorDiv').attr("style", "display:block");
    $('.hiddenErrorDiv').html(msg);
    return false;
  }
  if (scheduledPerson == ''){
    var msg= "Please Select Assign To";
    $('.hiddenErrorDiv').attr("style", "display:block");
    $('.hiddenErrorDiv').html(msg);
    return false;
  }
  if ($("#submitflag").val()==1){
	  return false;
  }
  var data =  $('#form').serialize();
  $.ajax({
    type:'POST',
    url: 'page-includes/allocations/edits/allocation-move-job.php',
    data:$('#form').serialize(),
    success: function(data) {
        data = JSON.parse(data);
        if (data.intstatus != null && data.intstatus == 0) {
          ShowDailyAllocations(<?php echo $intTeamID; ?>, '<?php echo $curdate; ?>');
          $.facebox.close();
        } else {
            $mesg = data.strstatus;
            customAlert($mesg);
        }
    }
  });
}

function cancel() {
  $.facebox.close();
}

function getScheduledPersonJob(){
    $('#errorDiv').hide();
    $('#successDiv').hide();
    let fromDate = $('#startDateCopyJobInput').val();
    let toDate = $('#endDateCopyJobInput').val();
    let selectedScheduledPerson = '<?php echo $getDefaultSchedulePerson['ScheduledPersonID']?>';
    let teamId = $('#selteamId').val();
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
                repDiv +='<option value="0">Select scheduled person</option>';
                if(response.status == true){
                    $(response.schgeduledPeoplesList).each(function(index, value) {
                        if(value.SchedulingPersonID == selectedScheduledPerson){
                            repDiv +='<option value="'+value.SchedulingPersonID+'" selected="selected">'+value.FullName+'</option>';
                        } else {
                            repDiv +='<option value="'+value.SchedulingPersonID+'">'+value.FullName+'</option>';
                        }
                    });
                }
                $('#scheduledPerson').html(repDiv).trigger("chosen:updated");
            }
        });
    }
}
</script>
<?php
  echo '</div> ';
?>