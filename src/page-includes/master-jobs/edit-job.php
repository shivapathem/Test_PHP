<?php
session_start();
include_once '../../function-includes/DBHelper.php';

include_once '../../function-includes/genericfunctions.php';
include_once '../../function-includes/DB_Functions.php';
include_once '../../function-includes/master-jobs-functions.php';

$prog = getAllProgrammes();

$allProg = json_decode($prog);
$intStaffID = $_SESSION['user']['StaffID'];
$intUserID = isset($_SESSION['user']['UserID']) && !empty($_SESSION['user']['UserID']) ? $_SESSION['user']['UserID'] : $_COOKIE['editWeeklyUserId'];
$intAreaID = $_SESSION['user']['AreaID'];
$intType = 0;
$rsAreaTeams = GetUserAreaTeamsList($intUserID);

$masterJobId = $_REQUEST['id'];
$requestFrom = $_REQUEST['requestFrom'] ? $_REQUEST['requestFrom'] : 'JOB';
$dutyidContainsJob = $_REQUEST['dutyid'] ? $_REQUEST['dutyid'] : 0;
$prog = getAllProgrammes();
$allProg = json_decode($prog);
$Job = getJobs($masterJobId, 0, 1);
$temp_mJob = json_decode($Job, True);

$mJob = array();

if (is_array($temp_mJob))
    foreach ($temp_mJob as $t => $m) {
        if ($m['MasterJobID'] != $masterJobId) {
            unset($mJob[$t]);
        } else {
            $mJob = $m;
        }
    }

$isused = checkJobAssignToMasterDuty($masterJobId);

$master_duty_id = 0;
$duty_start_time = 0;
$duty_end_time = 0;
if ($isused) {
    $master_duty_id = getMasterDutyId($masterJobId);
    $duty = getMasterDutiesByID($master_duty_id);
    $mduty = json_decode($duty);
    $duty_start_time = $mduty->StartTime;
    $duty_end_time = $mduty->EndTime;
}
$total_isused = totalJobAssignToMasterDuty($masterJobId);
$intTeamID = $mJob['TeamID'];
$TeamOptions = TeamListDropDown($rsAreaTeams, $intTeamID);
if ($mJob['BackColour'] == "") {
    $backgroundcolor = "#fff";
} else {
    $backgroundcolor = $mJob['BackColour'];
}

?>
<div class="popup">
<div class="content">
<?php echo '<div style="width: 600px">'; ?>
  <form id="neweditjob" method="post" >
    <table id="jobnewedittable" class="smalltable bluetable" width="100%" role="presentation">
      <thead><tr><th colspan="5">Edit Master Job</th></tr></thead>
      <tbody>
        <tr><td class="lightblue">Job Name<span class="required">*</span></td><td colspan="3"><input id="Job" name="Job" type="text" size="50" maxlength="50" value="<?php echo $mJob['JobName'];?>"></td></tr>
        <tr>
          <td>Start Time<span class="required">*</span></td><td><input id="StartTime" name="StartTime" type="text" maxlength="5" class="ui-timepicker-input" size="10" value="<?php echo FormatTime($mJob['StartTime']);?>" onChange="jobsTimeSanitization(StartTime.value, 'StartTime');" autocomplete="off"></td>
          <td>End Time<span class="required">*</span></td><td><input id="EndTime" name="EndTime" type="text" maxlength="5" class="ui-timepicker-input" size="10" value="<?php echo FormatTime($mJob['EndTime']);?>" onChange="jobsTimeSanitization(EndTime.value, 'EndTime');" autocomplete="off"></td>
        </tr>
        <tr><td>Scheduling Team<span class="required">*</span></td>
            <td colspan="3">
          <?php  echo '   <select class="chosen-select bg-white chosen-selectMaxWidth" name="scheduling_team" id="ddldutyteam" disabled>';
      echo ''.$TeamOptions.''; ?></td>
            </tr>
        <tr><td>Info</td><td colspan="3">
          <textarea rows="2" maxlength="500" name="info" cols="35"><?php echo $mJob['Details'];?></textarea></td>
        </tr>
        <tr><td class="lightblue">Background Colour</td><td ><input id="meditbackcolor" name="backcolor" type="text" size="10" value="<?php echo $backgroundcolor;?>"></td>
          <td class="lightblue">Font Colour</td><td ><input id="meditforecolor" name="forecolor" type="color" size="10" value="<?php echo $mJob['FontColour'];?>"></td>

        </tr>
        <tr hidden><td class="lightblue">Default Colour</td><td ><input id="meditdefaultcolor" name="defaultcolor" type="color" size="10" value=""></td>
        </tr>
          <tr><td>Label</td><td colspan="3">
            <select name="programme_id" id="JobType">
              <?php
              if($mJob['ProgrammeID'] == 0) {
              ?>
              <option value="0">Select Label</option>
              <?php }
              foreach ($allProg as $key => $value) {
                $selected = '';
                if($value->ID == $mJob['ProgrammeID']){
                  $selected = 'selected';
                }
              ?>
              <option  value="<?php echo $value->ID;?>" <?php echo $selected; ?> ><?php echo $value->Programme;?></option>
            <?php }?>
            </select></td>
          </tr>
          <tr><td>Contact</td>
            <td colspan="3">
            <input id="contact" maxlength="50" name="contact" type="text" size="50" value="<?php echo $mJob['Contact'];?>" class="ui-autocomplete-input" autocomplete="off"></td>
            </tr>
            <tr><td>Location</td>
            <td colspan="3">
            <input id="location" maxlength="50" name="location" type="text" size="50" value="<?php echo $mJob['Location'];?>" class="ui-autocomplete-input" autocomplete="off"></td>
            </tr>
                <tr hidden>
                    <td class="lightblue">Default Colour</td>
                    <td><input id="meditdefaultcolor" name="defaultcolor" type="color" size="10" value=""></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3">
                        <input type="hidden" name="master_job_id" value="<?php echo $mJob['MasterJobID']; ?>">
                        <input name="submit" type="submit" value="Update Job">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
<input type="hidden" id="ajaxResult" value="0">
    <script type="text/javascript">
let requestFrom = '<?php echo $requestFrom; ?>';
        $(document).ready(function () {
            $("#facebox").removeClass('customFaceboxClose');
            colorPickerDropdown('#meditbackcolor', $('#meditbackcolor').val());
            colorPickerDropdown('#meditforecolor', $('#meditforecolor').val());
            colorPickerDropdown('#meditdefaultcolor', "#000");
            $.validator.addMethod("validJob", function (value, element) {
                return this.optional(element) || /^[a-zA-Z0-9 &+!()|\-\\'\[\],.=()*_:{}?]*$/i.test(value);
            }, "Job name must contain only letters, numbers & few special symbol.");
            $.validator.addMethod("validJobName", function (value, element) {
            return this.optional(element) || !/leave|sick/i.test(value);
        }, "<br>Job name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Job a different Name.");

        $('#neweditjob').validate({
                //debug: false,
                rules: {
                    "Job": {
                        required: true,
                        //minlength:3,
                        validJob: true,
                        //maxlength:50
                        validJobName:true
                    },
                    "StartTime": {
                        required: true,
                    },
                    "EndTime": {
                        required: true,
                    },
                    "scheduling_team": {
                        required: true,
                    },
                    "contact": {
                        digits: false,
                        minlength: 0,
                        maxlength: 50,
                    }
                },
                messages: {
                    "Job": {
                        required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter a Job Name.' />",
                        // minlength: jQuery.validator.format("Please, at least {0} characters are necessary."),
                        validJob: 'Job name can be only alphanumeric & few special symbol.',
                        validJobName: "<br>Job name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Job a Different name.",
                        //maxlength:'Job name can not be more than {0} characters'
                    },
                    "StartTime": {
                        required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the start time.' />"
                    },
                    "EndTime": {
                        required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the end time.' />"
                    },
                    "scheduling_team": {
                        required: "Please choose Scheduling team"
                    }
                },
                submitHandler: function (form) {
                    var ControlName = "#Job";
                    var Fieldname = 'Job Name';
                    var MinLength = 3;
                    var MaxLength = 50;

                    var Valid = ValidateText(ControlName, MinLength, MaxLength, Fieldname);
                    if (Valid == 0) {
                        return;
                    }
                    var startTime = form.elements["StartTime"].value;
                    var endTime = form.elements["EndTime"].value;
                    var preStartTime = "<?php echo FormatTime($mJob['StartTime']); ?>";
                    var preEndTime = "<?php echo FormatTime($mJob['EndTime']); ?>";
                    var preTeamId = "<?php echo $intTeamID; ?>";
                    var TeamId = $("select[name='scheduling_team']").val();

                    var TimeValidation = TimeValidationCheck(startTime, endTime);
                    if (TimeValidation == 1) {
                        return;
                    }

                    var exist =<?php echo $isused;?>;
                    if (exist == 1) {

                        var dialog = $('<p>You are editing a Master Job that may be used in one or more duties. Do you want to create a new Master Job or Edit the Job in all the existing Duties?<br><br> Click on YES to create a new Master Job<br> Click No to update the Job in ALL the existing Master Duties<br> Cancel will quit updating the Master Job</p>').dialog({
                            buttons: {
                                "Yes": function () {
									if(requestFrom == 'DUTY')
									{
										$.ajax({
											type: 'POST',
											url: 'page-includes/master-jobs/delete-job-from-duty.php',
											async:true,
											data: {
												jobid: '<?php echo $masterJobId; ?>',
												mduty: '<?php echo $dutyidContainsJob; ?>'
											},
											success: function (data) {
												$("#ddldutyteam").prop("disabled", false);
												let returnVal = addJobAndMapWithDuty(startTime, endTime, '<?php echo $dutyidContainsJob; ?>', $("#Job").val(), form.elements["backcolor"].value, form.elements["forecolor"].value, form.elements["programme_id"].value, form.elements["info"].value, form.elements["contact"].value, form.elements["location"].value, '<?php echo $duty_start_time; ?>', '<?php echo $duty_end_time; ?>', $("select[name='scheduling_team']").val(), "<?php echo $mJob['StartTime']; ?>", "<?php echo $mJob['EndTime']; ?>", '<?php echo $masterJobId; ?>');
												setTimeout(function(){
													if($("#ajaxResult").val() == 1)
													{
														dialog.dialog('close');
														$.facebox.close();
													}
												}, 1500);
											}
										});
									}else
									{
										$("#ddldutyteam").prop("disabled", false);
										$.ajax({
											type: 'POST', url: 'page-includes/master-jobs/add-job.php',
											data: $('#neweditjob').serialize() + '&submit=Submit&copyFlag=Yes',
											success: function (data) {
												data = JSON.parse(data);
												if (data.status != null && data.status == 1) {
													$("#ddldutyteam").prop("disabled", false);
												} else {
													$("#ddldutyteam").prop("disabled", true);
												}
												ListJobs(0, 0);
												ListMasterDuties(0, 0);
												dialog.dialog('close');
												$.facebox.close();
											}
										});
									}

                                },
                                "No": function () {

                                    var warning_message = '';

                                    if (preTeamId != TeamId) {
                                        warning_message += 'Warning : Job has been assigned to one or more duties. Thus, job team can not be edited.'
                                    }

                                    if (warning_message) {
                                        customAlertByModel(warning_message);
                                    } else {

                                        if ((startTime != preStartTime) || (endTime != preEndTime)) {
                                            var startTime = $("#StartTime").val();
                                            var endTime = $("#EndTime").val();
                                            var duty_id = '<?php echo $master_duty_id; ?>';
                                            var duty_starttime = '<?php echo $duty_start_time; ?>';
                                            var duty_endtime = '<?php echo $duty_end_time; ?>';
                                            var job = $("#Job").val();
                                            var job_id = $("input[name='master_job_id']").val();
                                            var info = $("#info").val();
                                            var backcolor = $("#backcolor").val();
                                            var forecolor = $("#forecolor").val();
                                            var programme_id = $("#programme_id").val();
                                            var contact = $("#contact").val();
                                            var location = $("#location").val();
                                            var schedule_team = $("select[name='scheduling_team']").val();

                                            $.ajax({
                                                type: 'POST',
                                                dataType: "json",
                                                url: 'page-includes/master-jobs/check-job-time-before-save.php',
                                                data: {
                                                    startTime: startTime,
                                                    endTime: endTime,
                                                    duty_id: duty_id,
                                                    job: job,
                                                    master_job_id: job_id,
                                                    backcolor: backcolor,
                                                    forecolor: forecolor,
                                                    programme_id: programme_id,
                                                    info: info,
                                                    contact: contact,
                                                    location: location,
                                                    duty_starttime: duty_starttime,
                                                    duty_endtime: duty_endtime,
                                                    schedule_team
                                                },
                                                success: function (data) {
                                                    if (data.status == 'ok') {
                                                        $("#ddldutyteam").attr("disabled",false);

                                                        $.ajax({
                                                            type: 'POST',
                                                            url: 'page-includes/master-jobs/update-master-job-record.php',
                                                            data: $('#neweditjob').serialize() + '&submit=Submit',
                                                            success: function (data) {
                                                                $("#ddldutyteam").attr("disabled",true);
                                                                data = JSON.parse(data);
                                                                if (data.status != null && (data.status == 1 || data.status == '1')) {
                                                                    ListJobs(0, 0);
																	ListMasterDuties(0, 0);
                                                                    dialog.dialog('close');
																	$.facebox.close();
                                                                }
                                                                else if (data.status == 0 || data.status == '0') {
                                                                    customAlertByModel(data.strstatus);
                                                                    dialog.dialog('close');
                                                                    return;
                                                                }
                                                            }
                                                        });

                                                    } else {
                                                        customAlertByModel(data.msg);
                                                    }
                                                }
                                            });

                                        }
                                    }
                                },
                                "Cancel": function () {
                                    dialog.dialog('close');
                                }
                            }
                        });
                    } else {
                        $("#ddldutyteam").attr("disabled",false);
                        $.ajax({
                            type: 'POST',
                            url: 'page-includes/master-jobs/update-master-job-record.php',
                            data: $('#neweditjob').serialize(),
                            success: function (data) {
                                $("#ddldutyteam").attr("disabled",true);
                                data = JSON.parse(data);
                                if (data.status != null && data.status == 1) {
                                    ListJobs(0, 0);
                                    $.facebox.close();
                                }
                                else if (data.status == 0) {
                                    customAlertByModel(data.strstatus);
                                }
                            }
                        });

                    }
                    /* }*/
                }
            })
        });

        $(function () {
            $('#StartTime').timepicker({
                'step': 15,
                'timeFormat': 'H:i'
            });
        });

        $(function () {
            $('#EndTime').timepicker({
                'step': 15,
                'timeFormat': 'H:i'
            });
        });

        $(function () {
            $("#number")
                .selectmenu()
                .selectmenu("menuWidget")
                .addClass("overflow");
        });

function jobsTimeSanitization(timeVal, elementId)
{
	let elementResorce	=	document.getElementById(elementId);
	if(timeVal == '0')
	{
		setTimeout(function(){
			elementResorce.value = '00:00';
			elementResorce.blur();
			}, 1000);
		return true;
	}
	let timeValArr	=	timeVal.split(':');
	if(timeValArr[0] < 24 && timeValArr[1] < 60)
	{

	}else
	{
		elementResorce.value	=	'00:00';
	}
}

function addJobAndMapWithDuty(startTime, endTime, duty_id, job, backcolor, forecolor, programme_id, info, contact, location, duty_starttime, duty_endtime, schedule_team, preStartTime, preEndTime, jobId, dutyId)
{
    var actionVal	=	'EDIT';
	$.ajax({
                    type: 'POST',
                    dataType: "json",
                    async: true,
                    url: 'page-includes/master-jobs/check-job-time-before-save.php',
                    data: {
                        startTime: startTime,
                        endTime: endTime,
                        duty_id: duty_id,
                        job: job,
                        backcolor: backcolor,
                        forecolor: forecolor,
                        programme_id: programme_id,
                        info: info,
                        contact: contact,
                        location: location,
                        duty_starttime: duty_starttime,
                        duty_endtime: duty_endtime,
                        schedule_team
                    },
                    success: function (data) {
                        // var res= $.parseJSON(data);
                        //Before Add
                        if (data.status == 'Before') {
                            var msg = data.msg;
                            var flag = 'Before';
							//skip confirmbox in case on new job & if blank error msg comes
							var sure = true;
							if(msg != '')
							{
								customConfirm(msg,function(){
										sure = true;
										$.ajax({
											type: 'POST',
											url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
											async: true,
											data: {
												startTime: startTime,
												endTime: endTime,
												duty_id: duty_id,
												job: job,
												backcolor: backcolor,
												forecolor: forecolor,
												programme_id: programme_id,
												info: info,
												contact: contact,
												location: location,
												flag: flag,
												schedule_team,
												actionVal:actionVal
											},
											success: function (data) {
												if (data.status == 0) {
													customAlertByModel(data.msg);
													setTimeout(function() {addJobInDuty(preStartTime, preEndTime, duty_id, jobId);}, 1000);
													return;
												}
												else {
													ListJobs(0, 0);
													ListMasterDuties(0, 0);
													$("#ajaxResult").val(1);
												}
											}

										});

									},
									function(){
										sure = false;
										return false;
									}
								);
							}else
							{
                                $.ajax({
                                    type: 'POST',
                                    url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                                    async: true,
                                    data: {
                                        startTime: startTime,
                                        endTime: endTime,
                                        duty_id: duty_id,
                                        job: job,
                                        backcolor: backcolor,
                                        forecolor: forecolor,
                                        programme_id: programme_id,
                                        info: info,
                                        contact: contact,
                                        location: location,
                                        flag: flag,
                                        schedule_team,
										actionVal:actionVal
                                    },
                                    success: function (data) {
                                        if (data.status == 0) {
                                            customAlertByModel(data.msg);
											setTimeout(function() {addJobInDuty(preStartTime, preEndTime, duty_id, jobId);}, 1000);
                                            return;
                                        }
                                        else {
                                            ListJobs(0, 0);
                                            ListMasterDuties(0, 0);
											$("#ajaxResult").val(1);
                                        }
                                    }

                                });
                            }
                        }
                        //End Before
                        // After Add add
                        if (data.status == 'After') {
                            var msg = data.msg;
                            var flag = 'After';
							//skip confirmbox in case on new job & if blank error msg comes
                            var sure = true;
							if(msg != '')
							{
								customConfirm(msg,function(){
										sure = true;
										$.ajax({
											type: 'POST',
											url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
											async: true,
											data: {
												startTime: startTime,
												endTime: endTime,
												duty_id: duty_id,
												job: job,
												backcolor: backcolor,
												forecolor: forecolor,
												programme_id: programme_id,
												info: info,
												contact: contact,
												location: location,
												flag: flag,
												schedule_team,
												actionVal:actionVal
											},
											success: function (data) {
												let Parsedata = JSON.parse(data);
												if (Parsedata.status == 0 || Parsedata.status == '0') {
													customAlertByModel(Parsedata.msg);
													setTimeout(function() {addJobInDuty(preStartTime, preEndTime, duty_id, jobId);}, 1000);
													return;
												}
												else {
													ListJobs(0, 0);
													ListMasterDuties(0, 0);
													$("#ajaxResult").val(1);
												}
											}
										});
									},
									function() {
										sure = false;
										return false;
									}
								);
							}else
							{
                                $.ajax({
                                    type: 'POST',
                                    url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                                    async: true,
                                    data: {
                                        startTime: startTime,
                                        endTime: endTime,
                                        duty_id: duty_id,
                                        job: job,
                                        backcolor: backcolor,
                                        forecolor: forecolor,
                                        programme_id: programme_id,
                                        info: info,
                                        contact: contact,
                                        location: location,
                                        flag: flag,
                                        schedule_team,
										actionVal:actionVal
                                    },
                                    success: function (data) {
                                        let Parsedata = JSON.parse(data);
                                        if (Parsedata.status == 0 || Parsedata.status == '0') {
                                            customAlertByModel(Parsedata.msg);
											setTimeout(function() {addJobInDuty(preStartTime, preEndTime, duty_id, jobId);}, 1000);
                                            return;
                                        }
                                        else {
                                            ListJobs(0, 0);
                                            ListMasterDuties(0, 0);
											$("#ajaxResult").val(1);
                                        }
                                    }

                                });
                            }
                        }
                        //End After add job

                        if (data.status == 'Conflict') {
                            customAlertByModel(data.msg);
							setTimeout(function() {addJobInDuty(preStartTime, preEndTime, duty_id, jobId);}, 1000);
                        }

                        //Case 1 If End time exceed from duties end time amend time in duties and assign to job
                        if (data.status == 'ok') {
                            var flag = 'newrecord';
                            $.ajax({
                                type: 'POST',
                                url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
                                async: true,
                                data: {
                                    startTime: startTime,
                                    endTime: endTime,
                                    duty_id: duty_id,
                                    job: job,
                                    backcolor: backcolor,
                                    forecolor: forecolor,
                                    programme_id: programme_id,
                                    info: info,
                                    contact: contact,
                                    location: location,
                                    flag: flag,
                                    schedule_team,
									actionVal:actionVal
                                },
                                success: function (data) {
                                    let NewParseData = JSON.parse(data);
                                    if (NewParseData.status == 0) {
                                        customAlertByModel(NewParseData.msg);
										setTimeout(function() {addJobInDuty(preStartTime, preEndTime, duty_id, jobId);}, 1000);
                                        return;
                                    }
                                    else {
                                        ListJobs(0, 0);
                                        ListMasterDuties(0, 0);
                                        setTimeout(function(){
                                            highlightJobs(parseInt(NewParseData.masterjobid));
                                        }, 1000);
										$("#ajaxResult").val(1);
                                    }
                                }

                            });
                        }

                        if (data.status == 'No') {
                            var message = res.msg;
                            var sure = false;
							customConfirm(message,function(){
									sure = true;
									$.ajax({
										type: 'POST',
										url: 'page-includes/master-jobs/updade-masterduties-endtime.php',
										async: true,
										data: {
											startTime: startTime,
											endTime: endTime,
											duty_id: duty_id,
											job: job,
											backcolor: backcolor,
											forecolor: forecolor,
											programme_id: programme_id,
											info: info,
											contact: contact,
											location: location,
											schedule_team,
											actionVal:actionVal
										},
										success: function (data) {
											if (data.status == 0) {
												customAlertByModel(data.msg);
												setTimeout(function() {addJobInDuty(preStartTime, preEndTime, duty_id, jobId);}, 1000);
												return;
											}
											else {
												ListJobs(0, 0);
												$("#ajaxResult").val(1);
											}
										}

									});

								},
								function() {
									sure = false;
									return false;
								}
							);
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        customAlert("Status: " + textStatus);
                        customAlert("Error: " + errorThrown);
                    }
                });
}
function addJobInDuty(startTime, endTime, duty_id, jobid)
{
	$.ajax({
		async:false,
		type: 'POST',
		dataType: "json",
		url: 'page-includes/master-jobs/job-within-duties.php',
		data: {
			startTime: startTime,
			endTime: endTime,
			duty_id: duty_id,
			jobid: jobid
		},
		success: function (data) {
			if (data.status == 1 || data.status == "1" ) {
				ListMasterDuties(0, 0);
			}
		}
	});
}
    </script>
