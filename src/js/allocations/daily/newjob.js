
    $(document).ready(function () {
        $('.content').css("display",'table');
        $("#facebox").removeClass('customFaceboxClose');
        var selectedTeamId = $('#selteam').val();
        if (selectedTeamId) {
            $("#selteam").val(selectedTeamId);
            $("#selteam").prop('disabled', true);
        }
        var allocationEditID = $('#allocationEditID').val();
        if (allocationEditID=='0') {
        $('#unallocated').prop('checked', true);
        $('#unallocated').val('1');
     }

        $("#aftermidnight").click(function() {
        if($('#aftermidnight').prop('checked')){
          $('#aftermidnight').val('1');
        }else{
          $('#aftermidnight').val('0');
        }
      });


      $("#unallocated").click(function() {
        if($('#unallocated').prop('checked')){
          $('#unallocated').val('1');
        }else{
          $('#unallocated').val('0');
        }
      });

        $('#Job').on('keyup', function(){
            let ControlName = "#Job";
            let Fieldname= 'Job Name';
            let TextValid = TextTypeValidationJob(ControlName,Fieldname);
            if(TextValid == 0){
             return;
            }
        });

        $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
        colorPickerDropdown('#jobbackcolor', '#ffffff');
        colorPickerDropdown('#jobforecolor', '#000000');
        colorPickerDropdown('#jobdefaultcolor', '#000000');
        $('#jobbackcolor').val('#ffffff');
        $('#jobforecolor').val('#000000');
        $('#jobdefaultcolor').val('#000000');

        $.validator.addMethod("validJobName", function (value, element) {
            return this.optional(element) || !/leave|sick/i.test(value);
        }, "<br>Job name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Job a different Name.");

        $('#newjob').validate({
            rules: {
                "Job": {
                    required: true,
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
                    //minlength: jQuery.validator.format("Please, at least {0} characters are necessary."),
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

                let TextValid = TextTypeValidationJob(ControlName,Fieldname);
                if(TextValid == 0){
                return;
                }
                /** Check the Year Validation */
                var teamId = form.elements["scheduling_team"].value;
                if (teamId == '') {
                    $.facebox("Please choose Scheduling team");
                    return false;
                }
                /** End */
                $("#selteam").prop("disabled", false);
				var jobsttime = form.elements["StartTime"].value;
				var jobentime = form.elements["EndTime"].value;
				var dutystart = form.elements["dutystart"].value;
				var duty_end = form.elements["duty_end"].value;
				var dutyduration = form.elements["dutyduration"].value;
				var data=  $('#newjob').serialize();
				var flag=0;
				let dutystarttime = dutystart.split(':');
                startTimeSec = (+dutystarttime[0]) * 60 * 60 + (+dutystarttime[1]) * 60;
                let dutyendtime = duty_end.split(':');
                endTimeSec = (+dutyendtime[0]) * 60 * 60 + (+dutyendtime[1]) * 60;
                if((startTimeSec > endTimeSec) || (dutyduration==86400)){
                    endTimeSec = 86400+endTimeSec;
                }
			    let jobstarttime = jobsttime.split(':');
                jobstartTimeSec = (+jobstarttime[0]) * 60 * 60 + (+jobstarttime[1]) * 60;
                let jobendtime = jobentime.split(':');
                jobendTimeSec = (+jobendtime[0]) * 60 * 60 + (+jobendtime[1]) * 60;
                if(jobstarttime > jobendTimeSec){
                    jobendTimeSec = 86400+jobendTimeSec;
                }

				var midnight =0;
				if($("#aftermidnight").prop('checked') == true){
					var midnight =1;
				}
				if (((jobstartTimeSec < startTimeSec) ||  (jobendTimeSec > endTimeSec)) && (dutystart!='')) {
					flag=1;
				}
				if (duty_end<dutystart){
					if ((jobstartTimeSec >= 86400 || jobstartTimeSec <= endTimeSec-86400) ||  (jobendTimeSec >= 86400 || jobendTimeSec <= endTimeSec-86400) ){
						var midnight =1;
					}
					if (jobsttime>jobentime){
						if (jobendTimeSec > endTimeSec-86400) {
							flag=1;
						}
					}
					if ((jobstartTimeSec+86400 > startTimeSec) &&  (jobendTimeSec < endTimeSec-86400)){
						flag=0;
					}
				}
				if($("#unallocated").prop('checked') == true){
					flag =0;
				}
				if (flag ==1 )	{
					$mes = 'The Job start time or end time is outside of the Duty start and end time. Click OK to continue and amend the Duty Start or End Time to that of the Job';
					 customConfirmModal($mes, function() {
						addduty(data,teamId,midnight);
						},
						function() {
							return false;
						},0,'EditWeeklyPublish'
					);
				} else {
					addduty(data,teamId,midnight);
				}
	        }
        })
    });

	function addduty(data,teamId,midnight){
	    $.ajax({
            type: 'POST',
            url: 'page-includes/allocations/edits/allocation-add-job.php',
            data: data+'&aftermidnight='+midnight,
            success: function (data) {
			data = JSON.parse(data);
				if (data.intstatus != null && data.intstatus == 0) {
					$("#selteam").prop("disabled", false);
					$.facebox.close();
				} else {
					$mes = '<table class="smalltable bluetable" width="100%"><tr><td colspan="2">'+data.strstatus+' </td></tr></td></tr></td></tr><tr><td ><input style="float:right;" type="button" name="ok" id="afterAdd" value="OK"></td></tr></table>';
                    $.facebox($mes);
                    $("#afterAdd").click(function (e) {
						$.facebox.close();
					});
					$("#cancelafter").click(function (e) {
						$.facebox.close();
					});
                    return false;
				}
            ShowDailyAllocations(teamId, $('#strCurrentDate').val());
			}
        });
	}

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

    function setStartTime() {
        var x = $("#StartTime").val();
        setTimeout(function () {
            if (x.length =='') {
                $("#StartTime").val('00:00');
            }
        }, 100);
    }

    function setEndTime() {
        var x = $("#EndTime").val();

        setTimeout(function () {
            if (x.length =='') {
                $("#EndTime").val('00:00');
            }
        }, 100);
    }