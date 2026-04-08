
    $(document).ready(function () {
    $('.content').css("display",'table');
     $("#facebox").removeClass('customFaceboxClose');

        var selectedTeamId = $('#selteam').val();
        getmastercolors(selectedTeamId);
        if (selectedTeamId) {
            $("#selteam").val(selectedTeamId);
            $("#selteam").prop('disabled', true);
        }

        $('#eDutyname').on('keyup', function(){
            let ControlName = "#eDutyname";
            let Fieldname= 'Duty Name';
            let TextValid = TextTypeValidation(ControlName,Fieldname);
            if(TextValid == 0){
                return;
            }
        });

        function getmastercolors(teamId) {
            var dutyColourID=$("#dutyColourID").val();
            $.ajax({
                url: 'page-includes/master-duties/get-masterdutycolors.php',
                dataType: "json",
                type: 'POST',
                data: {teamId: teamId, dutyColourID : dutyColourID},
                success: function (data) {

                    if (data.status == 'success') {
                        $('select.chosen-select options:contains("Swatch 1")');
                        $('#ddldutycolour').html(data.ColourOptions);
                        $('select.chosen-select').trigger("chosen:updated");
                    }
                }
            });
        }

      
        $(".chosen-select").chosen({no_results_text: "Oops, nothing found!"});
        colorPickerDropdown('#dutybackcolor', '#ffffff');
        colorPickerDropdown('#dutyforecolor', '#000000');
   
        $('#dutybackcolor').val('#ffffff');
        $('#dutyforecolor').val('#000000');
    
        $.validator.addMethod("validDutyName", function (value, element) {
            return this.optional(element) || !/leave|sick/i.test(value);
        }, "<br>Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a diffrent name.");
		$.validator.addMethod("validUDutyName", function (value, element) {
			return this.optional(element) ||  /[^U ]{1}/i.test(value);
		}, "<br>Please enter Duty Name.");

        $('#newunallocatedduty').validate({
            rules: {
                "eDutyname": {
                    required: true,
                    validDutyName:true,
					validUDutyName : true,
                },
                "StartTime": {
                    required: true
                },
                "EndTime": {
                    required: true
                },
                "scheduling_team": {
                    required: true
                },
                "ddldutycolour": {
                    required: true
                },
                "labelId": {
                    required: true
                },
                "BreakTime": {
                    required: true 
                },
            },
            messages: {
                "Duty": {
                    required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter a Duty Name.' />",
                    //minlength: jQuery.validator.format("Please, at least {0} characters are necessary."),
                    validDutyName: "<br>Duty name cannot be 'Leave' OR 'Sick'. Please use the 'Leave OR Sickness Form' to create Leave OR Sick entry OR give the Duty a Different Name.",
					validUDutyName: "Please enter Duty Name",
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
                },
                "ddldutycolour": {
                    required: "Please choose Duty colour"
                   
                },
                "labelId": {
                    required: "Please choose Duty label"
                   
                },
                "BreakTime": {
                    required: "<img id='exclamation' src='images/critical.png' width='16' height='16' title='Please enter the end time.' />"
                   
                }
            },

           
            submitHandler: function (form) {

                var ControlName = "#eDutyname";
                var Fieldname = 'Duty Name';
                var MinLength = 3;
                var MaxLength = 50;
                var Valid = ValidateText(ControlName, MinLength, MaxLength, Fieldname);
                if (Valid == 0) {
                    return;
                }

                let TextValid = TextTypeValidation(ControlName,Fieldname);
                if(TextValid == 0){
                return;
                }
                
               $("#selteam").prop("disabled", false);
                var teamId = $("#selteam").val();
                $.ajax({
                  type: 'POST',
                    url: 'page-includes/allocations/edits/allocation-add-unallocated-duty.php',
                    data: $('#newunallocatedduty').serialize(),
                    success: function (data) {
                        data = JSON.parse(data);
					    if (data.intstatus != null && data.intstatus == 1) {
                            $("#selteam").prop("disabled", false);
                            $.facebox.close();
                        } else {
                            $("#selteam").prop("disabled", true);
                            window.parent.focus();
							$(".messageerror").html('<span class="messageerror error" id="reasonerror">'+data.strstatus+'</span>')
                            return false;
                        }
                        ShowDailyAllocations(teamId, $('#strCurrentDate').val());
                    },
                
                });
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
        $('#BreakTime').timepicker({
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
            if (x.length == '' || x.length == 0) {
                $("#StartTime").val('00:00');
                x.blur();
            }
        }, 100);
    }

    function setEndTime() {
        var x = $("#EndTime").val();
       
        setTimeout(function () {
            if (x.length == '' || x.length == 0) {
                $("#EndTime").val('00:00');
                x.blur();
            }
        }, 100);
    }

    function setBreakTime() {
        var x = $("#BreakTime").val();
       
        setTimeout(function () {
            if (x.length == 0) {
                $("#BreakTime").val('01:00');
            }
        }, 100);
    }

   
