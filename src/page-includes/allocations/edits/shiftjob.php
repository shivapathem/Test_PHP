<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
date_default_timezone_set('Europe/London');
include_once '../../../function-includes/init.php';
include_once '../../../function-includes/genericfunctions.php';
include_once '../../../function-includes/AllocationsFunctionsEditing.php';

$pdo = OpenDBLinkA7();
$allocationId = $_POST['allocationId'] ?? 0;
$isEdited = $_POST['isEdited']?? 0;
$parentId = $_POST['parentId']?? 0;
$isShiftleader = $_POST['isShiftleader']?? 0;
$intTeamID= $_POST['teamId']?? 0;
$week = $_POST['WeekNumber']?? 0;
$day = $_POST['iDay']?? 0;
$jobId = $_POST['jobId']?? 0;
$role = $_POST['role']?? 0;

$jobData = fetchEditedUnAllocationJob($jobId,$intTeamID);
$starttime = $jobData['StartTime'];
if($jobData['EndTime'] > 86400) {
    $jobData['EndTime'] = $jobData['EndTime']-86400;
}
$endtime = $jobData['EndTime'];
if(isset($_POST['currentDate'])) {
    $curdate = $_POST['currentDate'];
} elseif (isset($_SESSION['allocattionsdate'])) {
    $curdate = $_SESSION['allocattionsdate'];
} else {
    $curdate = date("Y-m-d");
}

  echo '<div id="movejobpage">';
  echo '<form onsubmit="return false;" id="form" novalidate>';
  echo '<div class="hiddenErrorDiv"></div>';
  echo '<table class="redtable" width="600px">';
  echo '<tr>';
  echo '<th colspan="3"><br>Move Job To<br><br></th>';
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
  echo '<td class="lightblue"><label>Date <span id="" class="required">*</span></label></td>';
  echo '<td>';
  echo '<input type="text" autocomplete="off" placeholder="dd-mm-yyyy" id="destinationDate"
  name="destinationDate" required="required" class="widthpopupcontrols" onclick="callDatePicker();">';
  echo '</td>';
  echo '<td id="weekno">';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<td>Assign To</td>';
  echo '<td colspan="2">';
  echo '<select class="chosen-select" id="scheduledPerson" name="scheduledPerson">';
	echo '<option value="">-- Select --</option>';
  echo '</select>';
  echo '</td>';
  echo '</tr>';
  echo '<tr>';
  echo '<input type="hidden" id="teamId" name="teamId" value="'.$intTeamID.'">';
  echo '<input type="hidden" id="JobId" name="JobId" value="'.$jobId.'">';
  echo '<input type="hidden" id="role" name="role" value="'.$role.'">';
  echo '<input type="hidden" id="DutyId" name="DutyId" value="">';
  echo '<input type="hidden" name="jobstart" id="jobstart" value="'.$starttime.'">';
  echo '<input type="hidden" name="job_end" id="job_end" value="'.$endtime.'">';
  echo '<input type="hidden" id="process" name="process" value="MoveJob">';
  echo '<input type="hidden" id="submitflag" name="submitflag" value="0">';
  echo '<td>&nbsp;</td>';
  echo '<td colspan="2"><br><input type="submit" id="submit" value="Move Job"  name="submit" onclick="submitForm();" ><input type="button" value="Cancel" onclick="cancel()"></td>';
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
});

function callDatePicker() {
	var currdate = new Date();
	var day = currdate.getDate();
	var month = currdate.getMonth() + 1;
	var year = currdate.getFullYear() + 7;
	var dateControlName = '#destinationDate';
	var mindate = month+ '-' + day  + '-' + (year - 14);
	var yearrange = (year - 14) + ':' + year;
	var maxdate = day + '-' + month + '-' + year;
	$('#destinationDate').val('');
    $('#destinationDate').datepicker({
      changeMonth: true,
	   autoOpen: false,
	  firstDay:6,
      changeYear: true,
      showButtonPanel: false,//no need to display this btn, removed on 21-07-2021
      dateFormat: "dd-mm-yy",
      yearRange: yearrange,
      inline: true,
      minDate: new Date(mindate),
      maxDate: maxdate,//new date fn is returning invalid date that's we removed it.
      onSelect: function (dateText, inst) {
      var currentdate = dateText;
	  $('.hiddenErrorDiv').attr("style", "display:hidden");
	  $("#submitflag").val(0);
      ShowDutiesList(<?php echo $intTeamID?>, currentdate)
      }
    });

}

function ShowDutiesList(teamId,currentdate){
  $.ajax({
    type:'POST',
    url: 'page-includes/allocations/edits/allocation-move-job.php',
    data:{
      process:"getDutiesList",
      currentdate:currentdate,
      teamId:teamId
    },
    success: function(data) {
      data = JSON.parse(data);
      if(data.success == true)
      {
        $('select.chosen-select options:contains("Swatch 1")');
        var ele = document.getElementById('scheduledPerson');
        ele.innerHTML = '<option value="">-- Select --</option>';
        for (var i = 0; i < data.result.length; i++) {
          if(data.result[i]['StartTime']!=null && data.result[i]['SchedulingPersonID']!=0){
          ele.innerHTML = ele.innerHTML +
            '<option value="' + data.result[i]['SchedulingPersonID'] + '" data-starttime="'+data.result[i]['StartTime']+'"  data-endtime="'+data.result[i]['EndTime']+'"  data-alloc="'+data.result[i]['AllocationId']+'" data-duty-id="'+data.result[i]['DutyId']+'">'+data.result[i]['DutyName']+ ' (' + data.result[i]['StartTime'] +' - '+ data.result[i]['EndTime'] +')' +' ['+data.result[i]['FullName']+']'+'</option>';
          }
          else if( data.result[i]['StartTime']!=null && data.result[i]['SchedulingPersonID']==0){
            ele.innerHTML = ele.innerHTML +
            '<option value="' + data.result[i]['SchedulingPersonID'] + '"  data-starttime="'+data.result[i]['StartTime']+'"  data-endtime="'+data.result[i]['EndTime']+'"  data-duty-id="'+data.result[i]['DutyId']+'"   data-alloc="'+data.result[i]['AllocationId']+'">'+data.result[i]['DutyName']+ ' (' + data.result[i]['StartTime'] +' - '+ data.result[i]['EndTime'] +')'+' [Unallocated]'+'</option>';
          }
          else{
            ele.innerHTML = ele.innerHTML +
            '<option value="' + data.result[i]['SchedulingPersonID'] + '" data-alloc="'+data.result[i]['AllocationId']+'"  data-duty-id="'+data.result[i]['DutyId']+'">'+data.result[i]['DutyName']+' ['+data.result[i]['FullName']+']'+'</option>';
          }
        }
        $('select.chosen-select').trigger("chosen:updated");
        $('#weekno').html(data.weekNo);
      }	else{
        var msg= "There are no Allocations for the selected date. Please choose another date."
		$('.hiddenErrorDiv').attr("style", "display:block");
		$("#submitflag").val(1);
		$('.hiddenErrorDiv').html(msg);
      }
    }
  });
}

function submitForm() {
   var destinationDate = $("#destinationDate").val();
  if (destinationDate==''){
	var msg= "Please Select Date";
	$('.hiddenErrorDiv').attr("style", "display:block");
	$('.hiddenErrorDiv').html(msg);
	return false;
  }
  if ($("#submitflag").val()==1){
	  return false;
  }
  var dutyId = $("#scheduledPerson option:selected").attr('data-duty-id');
  $("#DutyId").val(dutyId);
  var data =  $('#form').serialize();
  var Duty = $("#DutyId").val();
  var flag=0;
  if (Duty.length > 0){
      var dutystart = $("#scheduledPerson option:selected").attr('data-starttime');
      var duty_end = $("#scheduledPerson option:selected").attr('data-endtime');
      var jobstarttime = parseInt($('#jobstart').val());
      var jobendTimeSec = parseInt($('#job_end').val());
      var jobmidnightflag = $('#midnightflag').val();
      let dutystarttime = dutystart.split(':');
      startTimeSec = (+dutystarttime[0]) * 60 * 60 + (+dutystarttime[1]) * 60;
      let dutyendtime = duty_end.split(':');
      endTimeSec = (+dutyendtime[0]) * 60 * 60 + (+dutyendtime[1]) * 60;
     var addflag=0;
      if(startTimeSec > endTimeSec){
        endTimeSec = 86400+endTimeSec;
		addflag=1;
      }
	 if ((((jobstarttime < startTimeSec) &&  (addflag==0) )||  (jobendTimeSec > endTimeSec)) && (dutystart!='')) {
			flag=1;
	   }
	  if ((((jobstarttime < startTimeSec) && (jobmidnightflag==0)) ||  (jobendTimeSec > endTimeSec)) && (dutystart!='')) {
			flag=1;
      }
      if (duty_end<dutystart){
		if ((jobstarttime > jobendTimeSec) &&  (jobendTimeSec > endTimeSec-86400)) {
			flag=1;
	    }
      } else {
		if ((jobstarttime > endTimeSec) ||  (jobendTimeSec > endTimeSec)){
			flag=1;
		}
		if (jobstarttime>jobendTimeSec){
			if ((jobstarttime > endTimeSec) ||  (jobendTimeSec+86400 >= endTimeSec)){
				flag=1;
			}
		}
	 }

	 overflowflag =0;
	 if (endTimeSec>86400){
		 var overflowendtime = endTimeSec-86400 ;
		 if (overflowendtime == jobstarttime ){
			overflowflag =1;
		 } else {
			if 	((jobstarttime < jobendTimeSec) && (jobmidnightflag==1)&& (jobstarttime>=overflowendtime) && (jobendTimeSec>=overflowendtime)){
				overflowflag =1;
			}
			if 	((jobmidnightflag==0) && (jobstarttime<=startTimeSec) && (jobendTimeSec<=startTimeSec)){
				overflowflag =1;
			}
		}
	 } else {
		if (endTimeSec == jobstarttime ){
			overflowflag =1;
		}	 else if((jobstarttime<=startTimeSec) && (jobendTimeSec<=startTimeSec)) {
			overflowflag =1;
		}	 else if((jobstarttime>=endTimeSec) && (jobendTimeSec>=endTimeSec)) {
			overflowflag =1;
		}	 else if((jobstarttime>jobendTimeSec) && (jobstarttime>=endTimeSec) ) {
			overflowflag =1;
		}	 else if(jobmidnightflag==1)  {
			overflowflag =1;
		}
	 }


	if (jobendTimeSec==startTimeSec){
		overflowflag =1;
	}

	if (((endTimeSec==86400) && (jobstarttime==0)) || ((jobendTimeSec==86400) && (startTimeSec==0))){
		overflowflag =1;
	}
	if (overflowflag ==1){
		$.facebox.close();
		customAlertByModel("This job sits outside of the start and end time of the duty. It is not possible to move it to this duty.");
		flag=0;
		return;
	}
  }
        if (flag == 1 )	{
			$mes = 'The Job start time or end time is outside of the Duty start and end time. Click OK to continue and amend the Duty Start or End Time to that of the Job';
			customConfirmModal($mes, function() {
            $.ajax({
              type:'POST',
              url: 'page-includes/allocations/edits/allocation-move-job.php',
              data:data,
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
               },
				function() {
					return false;
				},0,'EditWeeklyPublish'
				);
				} else {
            $.ajax({
              type:'POST',
              url: 'page-includes/allocations/edits/allocation-move-job.php',
              data:data,
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
          return false;
        }
}

function cancel() {
  $.facebox.close();
}
</script>
<?php
  echo '</div> ';
?>